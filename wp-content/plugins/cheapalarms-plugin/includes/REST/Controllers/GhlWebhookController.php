<?php

namespace CheapAlarms\Plugin\REST\Controllers;

use CheapAlarms\Plugin\Services\Container;
use CheapAlarms\Plugin\Services\Ghl\GhlWebhookEventRepository;
use CheapAlarms\Plugin\Services\Logger;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

use function is_wp_error;
use function register_rest_route;
use function sanitize_text_field;
use function wp_schedule_single_event;

/**
 * GHL Webhook Controller
 *
 * Receives webhook events from GoHighLevel, verifies the RSA-SHA256 signature,
 * stores the event, and schedules async processing via WP-Cron.
 *
 * Endpoint: POST /ca/v1/ghl/webhook
 * Permission: Public (verified by signature)
 */
class GhlWebhookController implements ControllerInterface
{
    private Logger $logger;

    /**
     * GHL public key used to verify webhook signatures (x-wh-signature header).
     * Published by GoHighLevel in their marketplace documentation.
     * @see https://marketplace.gohighlevel.com/docs/webhook/WebhookIntegrationGuide
     */
    private const GHL_PUBLIC_KEY = <<<'PEM'
-----BEGIN PUBLIC KEY-----
MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEAokvo/r9tVgcfZ5DysOSC
Frm602qYV0MaAiNnX9O8KxMbiyRKWeL9JpCpVpt4XHIcBOK4u3cLSqJGOLaPuXw6
dO0t6Q/ZVdAV5Phz+ZtzPL16iCGeK9po6D6JHBpbi989mmzMryUnQJezlYJ3DVfB
csedpinheNnyYeFXolrJvcsjDtfAeRx5ByHQmTnSdFUzuAnC9/GepgLT9SM4nCpv
uxmZMxrJt5Rw+VUaQ9B8JSvbMPpez4peKaJPZHBbU3OdeCVx5klVXXZQGNHOs8gF
3kvoV5rTnXV0IknLBXlcKKAQLZcY/Q9rG6Ifi9c+5vqlvHPCUJFT5XUGG5RKgOKU
J062fRtN+rLYZUV+BjafxQauvC8wSWeYja63VSUruvmNj8xkx2zE/Juc+yjLjTXp
IocmaiFeAO6fUtNjDeFVkhf5LNb59vECyrHD2SQIrhgXpO4Q3dVNA5rw576PwTzN
h/AMfHKIjE4xQA1SZuYJmNnmVZLIZBlQAF9Ntd03rfadZ+yDiOXCCs9FkHibELhC
HULgCsnuDJHcrGNd5/Ddm5hxGQ0ASitgHeMZ0kcIOwKDOzOU53lDza6/Y09T7sYJ
PQe7z0cvj7aE4B+Ax1ZoZGPzpJlZtGXCsu9aTEGEnKzmsFqwcSsnw3JB31IGKAyk
T1hhTiaCeIY/OwwwNUY2yvcCAwEAAQ==
-----END PUBLIC KEY-----
PEM;

    public function __construct(private Container $container)
    {
        $this->logger = $this->container->get(Logger::class);
    }

    public function register(): void
    {
        // Receive GHL webhook events
        register_rest_route('ca/v1', '/ghl/webhook', [
            'methods'             => 'POST',
            'permission_callback' => '__return_true', // Public – verified by RSA signature
            'callback'            => [$this, 'handleWebhook'],
        ]);
    }

    /**
     * Handle incoming GHL webhook.
     *
     * Flow: verify signature → parse → idempotency check → store → schedule async → return 200
     */
    public function handleWebhook(WP_REST_Request $request): WP_REST_Response
    {
        // ── 1. Read raw body (need raw for signature verification) ───
        $payload   = $request->get_body();
        $signature = $request->get_header('x-wh-signature') ?? '';

        if (empty($payload)) {
            return $this->respond(new WP_Error(
                'bad_request',
                'Missing webhook payload.',
                ['status' => 400]
            ));
        }

        // ── 2. Verify RSA-SHA256 signature ───────────────────────────
        if (!$this->verifySignature($payload, $signature)) {
            $this->logger->warning('Invalid GHL webhook signature', [
                'signaturePresent' => !empty($signature),
            ]);
            return $this->respond(new WP_Error(
                'invalid_signature',
                'Invalid webhook signature.',
                ['status' => 401]
            ));
        }

        // ── 3. Parse payload ─────────────────────────────────────────
        $event = json_decode($payload, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->respond(new WP_Error(
                'parse_error',
                'Failed to parse webhook payload.',
                ['status' => 400]
            ));
        }

        $webhookId  = sanitize_text_field($event['webhookId'] ?? '');
        $eventType  = sanitize_text_field($event['type'] ?? '');
        $data       = $event['data'] ?? $event;

        if (empty($webhookId) || empty($eventType)) {
            return $this->respond(new WP_Error(
                'invalid_event',
                'Webhook event missing webhookId or type.',
                ['status' => 400]
            ));
        }

        // ── 4. Extract entity ID + location ID from data ─────────────
        $entityId   = $this->extractEntityId($eventType, $data);
        $locationId = (string)($data['locationId'] ?? $data['altId'] ?? '');

        // ── 5. Idempotency check ─────────────────────────────────────
        /** @var GhlWebhookEventRepository $eventRepo */
        $eventRepo = $this->container->get(GhlWebhookEventRepository::class);

        if ($eventRepo->isProcessed($webhookId)) {
            $this->logger->info('GHL webhook already processed (idempotency)', [
                'webhookId' => $webhookId,
            ]);
            return $this->respondOk([
                'webhookId'        => $webhookId,
                'alreadyProcessed' => true,
            ]);
        }

        // ── 6. Store event (INSERT IGNORE) ───────────────────────────
        $eventRepo->storeEvent($webhookId, $eventType, $entityId, $locationId, $payload);

        // ── 7. Schedule async processing via WP-Cron ─────────────────
        wp_schedule_single_event(time() + 1, 'ca_process_ghl_webhook', [$webhookId]);

        // ── 8. Return 200 immediately ────────────────────────────────
        return $this->respondOk([
            'webhookId' => $webhookId,
            'eventType' => $eventType,
            'queued'    => true,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // Signature verification
    // ─────────────────────────────────────────────────────────────

    /**
     * Verify the GHL webhook signature using the GHL public RSA key.
     *
     * GHL signs the raw JSON payload with its private key and includes the
     * base64-encoded signature in the `x-wh-signature` header.
     */
    private function verifySignature(string $payload, string $signature): bool
    {
        if (empty($signature)) {
            // During initial integration / testing, GHL may not always send
            // signatures. Log a warning but allow in dev mode only.
            if (defined('WP_DEBUG') && WP_DEBUG) {
                $this->logger->warning('GHL webhook missing signature – allowed in dev mode');
                return true;
            }
            return false;
        }

        $publicKey = openssl_pkey_get_public(self::GHL_PUBLIC_KEY);
        if ($publicKey === false) {
            $this->logger->error('Failed to parse GHL public key');
            return false;
        }

        $result = openssl_verify(
            $payload,
            base64_decode($signature, true) ?: '',
            $publicKey,
            OPENSSL_ALGO_SHA256
        );

        return $result === 1;
    }

    // ─────────────────────────────────────────────────────────────
    // Entity ID extraction
    // ─────────────────────────────────────────────────────────────

    /**
     * Extract the primary entity ID from the webhook data based on event type.
     */
    private function extractEntityId(string $eventType, array $data): string
    {
        // Contact events
        if (str_starts_with($eventType, 'Contact')) {
            return (string)($data['id'] ?? $data['contactId'] ?? $data['_id'] ?? '');
        }

        // Invoice events
        if (str_starts_with($eventType, 'Invoice')) {
            return (string)($data['id'] ?? $data['invoiceId'] ?? $data['_id'] ?? '');
        }

        // Estimate events
        if (str_starts_with($eventType, 'Estimate')) {
            return (string)($data['estimateId'] ?? $data['id'] ?? $data['_id'] ?? '');
        }

        // Generic fallback
        return (string)($data['id'] ?? $data['_id'] ?? '');
    }

    // ─────────────────────────────────────────────────────────────
    // Response helpers
    // ─────────────────────────────────────────────────────────────

    private function respondOk(array $data): WP_REST_Response
    {
        $data['ok'] = true;
        return new WP_REST_Response($data, 200);
    }

    /**
     * @param array|WP_Error $result
     */
    private function respond($result): WP_REST_Response
    {
        if (is_wp_error($result)) {
            $data   = $result->get_error_data();
            $status = is_array($data) ? ($data['status'] ?? 400) : 400;
            return new WP_REST_Response([
                'ok'      => false,
                'code'    => $result->get_error_code(),
                'message' => $result->get_error_message(),
            ], (int)$status);
        }

        if (!isset($result['ok'])) {
            $result['ok'] = true;
        }

        return new WP_REST_Response($result, 200);
    }
}
