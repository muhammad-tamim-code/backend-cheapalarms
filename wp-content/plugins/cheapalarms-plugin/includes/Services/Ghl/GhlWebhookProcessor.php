<?php

namespace CheapAlarms\Plugin\Services\Ghl;

use CheapAlarms\Plugin\Services\Contact\ContactSnapshotRepository;
use CheapAlarms\Plugin\Services\Estimate\EstimateSnapshotRepository;
use CheapAlarms\Plugin\Services\Invoice\InvoiceSnapshotRepository;
use CheapAlarms\Plugin\Services\Logger;
use WP_Error;

use function is_wp_error;
use function json_decode;
use function json_last_error;
use function json_last_error_msg;
use function wp_json_encode;

/**
 * Processes GHL webhook events asynchronously (via WP-Cron).
 *
 * Mirrors the Stripe webhook processor pattern:
 *   load event → parse payload → route by event type → upsert/soft-delete → mark processed
 */
class GhlWebhookProcessor
{
    public function __construct(
        private GhlWebhookEventRepository $eventRepo,
        private ContactSnapshotRepository $contactRepo,
        private InvoiceSnapshotRepository $invoiceRepo,
        private EstimateSnapshotRepository $estimateRepo,
        private Logger $logger
    ) {
    }

    /**
     * Process a single queued GHL webhook event.
     */
    public function processEvent(string $webhookId): bool|WP_Error
    {
        $event = $this->eventRepo->getEvent($webhookId);

        if (!$event) {
            return new WP_Error('not_found', 'GHL webhook event not found.', ['status' => 404]);
        }

        // Already processed – idempotency guard
        if (!empty($event['processed_at'])) {
            $this->logger->info('GHL webhook event already processed', ['webhookId' => $webhookId]);
            return true;
        }

        $this->eventRepo->markProcessingStarted($webhookId);

        try {
            $payload = json_decode($event['payload'] ?? '{}', true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Failed to parse GHL webhook payload: ' . json_last_error_msg());
            }

            $eventType  = $event['event_type'] ?? '';
            $entityId   = $event['entity_id'] ?? '';
            $locationId = $event['location_id'] ?? '';
            $data       = $payload['data'] ?? $payload;

            $result = $this->processEventByType($eventType, $entityId, $locationId, $data);

            if (is_wp_error($result)) {
                $this->eventRepo->markFailed($webhookId, $result->get_error_message());
                return $result;
            }

            $this->eventRepo->markProcessed($webhookId);

            $this->logger->info('GHL webhook event processed', [
                'webhookId' => $webhookId,
                'eventType' => $eventType,
                'entityId'  => $entityId,
            ]);

            return true;
        } catch (\Throwable $e) {
            $this->eventRepo->markFailed($webhookId, $e->getMessage());

            $this->logger->error('GHL webhook processing exception', [
                'webhookId' => $webhookId,
                'error'     => $e->getMessage(),
            ]);

            return new WP_Error('processing_error', $e->getMessage(), ['status' => 500]);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Event routing
    // ─────────────────────────────────────────────────────────────

    private function processEventByType(string $eventType, string $entityId, string $locationId, array $data): bool|WP_Error
    {
        return match (true) {
            // Contact events
            str_starts_with($eventType, 'Contact') && $eventType !== 'ContactDelete'
                => $this->handleContactUpsert($locationId, $data),
            $eventType === 'ContactDelete'
                => $this->handleContactDelete($locationId, $entityId),

            // Invoice events
            str_starts_with($eventType, 'Invoice') && $eventType !== 'InvoiceDelete'
                => $this->handleInvoiceUpsert($locationId, $data),
            $eventType === 'InvoiceDelete'
                => $this->handleInvoiceDelete($locationId, $entityId),

            // Estimate events
            str_starts_with($eventType, 'Estimate')
                => $this->handleEstimateUpsert($locationId, $data),

            // Unknown – log and skip (don't error so event is marked processed)
            default => $this->handleUnknown($eventType),
        };
    }

    // ─────────────────────────────────────────────────────────────
    // Contact handlers
    // ─────────────────────────────────────────────────────────────

    private function handleContactUpsert(string $locationId, array $data): bool|WP_Error
    {
        if (!$locationId) {
            $locationId = $data['locationId'] ?? '';
        }
        if (!$locationId) {
            return new WP_Error('missing_location', 'Cannot upsert contact: locationId missing');
        }

        $record = ContactSnapshotRepository::normalizeFromGhl($data);
        if (empty($record['id'])) {
            return new WP_Error('missing_entity_id', 'Contact webhook data has no id');
        }

        return $this->contactRepo->upsertOne($locationId, $record);
    }

    private function handleContactDelete(string $locationId, string $entityId): bool|WP_Error
    {
        if (!$locationId || !$entityId) {
            return true; // Nothing to soft-delete
        }

        $res = $this->contactRepo->softDelete($entityId, $locationId, 0, 'Deleted via GHL webhook');
        // Treat 'not_found' and 'already_deleted' as success
        if (is_wp_error($res)) {
            $code = $res->get_error_code();
            if ($code === 'not_found' || $code === 'already_deleted') {
                return true;
            }
        }
        return $res;
    }

    // ─────────────────────────────────────────────────────────────
    // Invoice handlers
    // ─────────────────────────────────────────────────────────────

    private function handleInvoiceUpsert(string $locationId, array $data): bool|WP_Error
    {
        if (!$locationId) {
            $locationId = $data['altId'] ?? $data['locationId'] ?? '';
        }
        if (!$locationId) {
            return new WP_Error('missing_location', 'Cannot upsert invoice: locationId missing');
        }

        $invoiceId = $data['id'] ?? $data['_id'] ?? $data['invoiceId'] ?? null;
        if (!$invoiceId) {
            return new WP_Error('missing_entity_id', 'Invoice webhook data has no id');
        }

        // Normalize (same shape as InvoiceSnapshotSyncService)
        $contact = $data['contact'] ?? $data['contactDetails'] ?? [];
        $contactName = $contact['name']
            ?? (trim(($contact['firstName'] ?? '') . ' ' . ($contact['lastName'] ?? '')))
            ?: '';

        $record = [
            'id'            => (string)$invoiceId,
            'invoiceNumber' => $data['invoiceNumber'] ?? $data['number'] ?? null,
            'contactId'     => $contact['id'] ?? $contact['contactId'] ?? '',
            'email'         => $contact['email'] ?? '',
            'name'          => $contactName,
            'status'        => $data['status'] ?? '',
            'total'         => (float)($data['total'] ?? 0),
            'amountPaid'    => (float)($data['amountPaid'] ?? 0),
            'currency'      => $data['currency'] ?? 'AUD',
            'dueDate'       => $data['dueDate'] ?? null,
            'createdAt'     => $data['createdAt'] ?? null,
            'updatedAt'     => $data['updatedAt'] ?? null,
            'rawJson'       => wp_json_encode($data),
        ];

        return $this->invoiceRepo->upsertOne($locationId, $record);
    }

    private function handleInvoiceDelete(string $locationId, string $entityId): bool|WP_Error
    {
        if (!$locationId || !$entityId) {
            return true;
        }

        $res = $this->invoiceRepo->softDelete($entityId, $locationId, 0, 'Deleted via GHL webhook');
        if (is_wp_error($res)) {
            $code = $res->get_error_code();
            if ($code === 'not_found' || $code === 'already_deleted') {
                return true;
            }
        }
        return $res;
    }

    // ─────────────────────────────────────────────────────────────
    // Estimate handlers
    // ─────────────────────────────────────────────────────────────

    private function handleEstimateUpsert(string $locationId, array $data): bool|WP_Error
    {
        if (!$locationId) {
            $locationId = $data['altId'] ?? $data['locationId'] ?? '';
        }
        if (!$locationId) {
            return new WP_Error('missing_location', 'Cannot upsert estimate: locationId missing');
        }

        $estimateId = $data['estimateId'] ?? $data['id'] ?? $data['_id'] ?? null;
        if (!$estimateId) {
            return new WP_Error('missing_entity_id', 'Estimate webhook data has no id');
        }

        // Normalize (same shape as EstimateSnapshotSyncService)
        $email = $data['contact']['email']
            ?? $data['contactDetails']['email']
            ?? ($data['sentTo']['email'][0] ?? '')
            ?? '';

        $record = [
            'id'             => (string)$estimateId,
            'estimateNumber' => $data['estimateNumber'] ?? null,
            'email'          => $email,
            'status'         => $data['estimateStatus'] ?? $data['status'] ?? '',
            'total'          => (float)($data['total'] ?? 0),
            'currency'       => $data['currency'] ?? 'AUD',
            'createdAt'      => $data['createdAt'] ?? null,
            'updatedAt'      => $data['updatedAt'] ?? null,
            'rawJson'        => wp_json_encode($data),
        ];

        return $this->estimateRepo->upsertMany($locationId, [$record]);
    }

    // ─────────────────────────────────────────────────────────────

    private function handleUnknown(string $eventType): bool
    {
        $this->logger->info('Unhandled GHL webhook event type', ['eventType' => $eventType]);
        return true; // Don't error on unknown types – mark processed
    }
}
