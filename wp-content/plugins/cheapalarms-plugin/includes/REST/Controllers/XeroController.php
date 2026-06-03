<?php

namespace CheapAlarms\Plugin\REST\Controllers;

use CheapAlarms\Plugin\Config\Config;
use CheapAlarms\Plugin\REST\Auth\Authenticator;
use CheapAlarms\Plugin\REST\Controllers\Base\AdminController;
use CheapAlarms\Plugin\Services\Container;
use CheapAlarms\Plugin\Services\Invoice\InvoiceSnapshotRepository;
use CheapAlarms\Plugin\Services\XeroService;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

use function sanitize_text_field;
use function current_time;
use function is_wp_error;

class XeroController extends AdminController
{
    private XeroService $xeroService;
    private Authenticator $auth;
    private \CheapAlarms\Plugin\Services\Logger $logger;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->xeroService = $this->container->get(XeroService::class);
        $this->auth = $this->container->get(Authenticator::class);
        $this->logger = $this->container->get(\CheapAlarms\Plugin\Services\Logger::class);
    }

    public function register(): void
    {
        // Get authorization URL
        register_rest_route('ca/v1', '/xero/authorize', [
            'methods' => 'GET',
            'permission_callback' => fn () => true,
            'callback' => function (WP_REST_Request $request) {
                $this->ensureUserLoaded();
                $authCheck = $this->auth->requireCapability('ca_manage_portal');
                if (is_wp_error($authCheck)) {
                    return $this->respond($authCheck);
                }
                return $this->getAuthorizationUrl($request);
            },
        ]);

        // OAuth callback (handled by Next.js API route, but we provide the exchange endpoint)
        // Note: This endpoint is called server-to-server from Next.js, so we don't require user auth
        // The state parameter provides CSRF protection
        // Additional security: Only allow from Next.js frontend URL
        register_rest_route('ca/v1', '/xero/callback', [
            'methods' => 'POST',
            'permission_callback' => function () {
                // SECURITY: State token validation in handleCallback is the primary security mechanism
                // Referer/Origin headers are spoofable and not used for security decisions
                // This endpoint is called server-to-server from Next.js during OAuth flow
                return true;
            },
            'callback' => function (WP_REST_Request $request) {
                return $this->handleCallback($request);
            },
        ]);

        // Check connection status
        register_rest_route('ca/v1', '/xero/status', [
            'methods' => 'GET',
            'permission_callback' => fn () => true,
            'callback' => function (WP_REST_Request $request) {
                $this->ensureUserLoaded();
                $authCheck = $this->auth->requireCapability('ca_manage_portal');
                if (is_wp_error($authCheck)) {
                    return $this->respond($authCheck);
                }
                return $this->getStatus($request);
            },
        ]);

        // Disconnect Xero
        register_rest_route('ca/v1', '/xero/disconnect', [
            'methods' => 'POST',
            'permission_callback' => fn () => true,
            'callback' => function (WP_REST_Request $request) {
                $this->ensureUserLoaded();
                $authCheck = $this->auth->requireCapability('ca_manage_portal');
                if (is_wp_error($authCheck)) {
                    return $this->respond($authCheck);
                }
                return $this->disconnect($request);
            },
        ]);

        // Sync invoice to Xero
        register_rest_route('ca/v1', '/xero/sync-invoice', [
            'methods' => 'POST',
            'permission_callback' => fn () => true,
            'callback' => function (WP_REST_Request $request) {
                $this->ensureUserLoaded();
                $authCheck = $this->auth->requireCapability('ca_manage_portal');
                if (is_wp_error($authCheck)) {
                    return $this->respond($authCheck);
                }
                return $this->syncInvoice($request);
            },
        ]);

        // Sync payment to Xero (record payment against existing invoice)
        register_rest_route('ca/v1', '/xero/sync-payment', [
            'methods' => 'POST',
            'permission_callback' => fn () => true,
            'callback' => function (WP_REST_Request $request) {
                $this->ensureUserLoaded();
                $authCheck = $this->auth->requireCapability('ca_manage_portal');
                if (is_wp_error($authCheck)) {
                    return $this->respond($authCheck);
                }
                return $this->syncPayment($request);
            },
        ]);
    }

    private function getAuthorizationUrl(WP_REST_Request $request): WP_REST_Response
    {
        $result = $this->xeroService->getAuthorizationUrl();
        
        if (is_wp_error($result)) {
            return $this->respond($result);
        }

        return $this->respond([
            'ok' => true,
            'authUrl' => $result['authUrl'],
            'state' => $result['state'],
        ]);
    }

    private function handleCallback(WP_REST_Request $request): WP_REST_Response
    {
        // SECURITY: State token validation is the primary security mechanism
        // Referer/Origin headers are spoofable and should not be relied upon for security
        // We log them for monitoring but don't block based on them
        
        // Get parameters from JSON body (POST request)
        $body = $request->get_json_params();
        $code = sanitize_text_field($body['code'] ?? '');
        $state = sanitize_text_field($body['state'] ?? '');
        
        if (empty($code) || empty($state)) {
            return $this->respond(new WP_Error('missing_params', __('Code and state are required.', 'cheapalarms'), ['status' => 400]));
        }
        
        // Optional: Log referer/origin for monitoring (but don't use for security decisions)
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $referer = sanitize_text_field($_SERVER['HTTP_REFERER'] ?? '');
            $origin = sanitize_text_field($_SERVER['HTTP_ORIGIN'] ?? '');
            if (!empty($referer) || !empty($origin)) {
                $this->container->get(\CheapAlarms\Plugin\Services\Logger::class)->info('Xero OAuth callback', [
                    'has_referer' => !empty($referer),
                    'has_origin' => !empty($origin),
                ]);
            }
        }

        $result = $this->xeroService->exchangeCodeForToken($code, $state);
        
        if (is_wp_error($result)) {
            return $this->respond($result);
        }

        return $this->respond([
            'ok' => true,
            'tenantId' => $result['tenantId'],
        ]);
    }

    private function getStatus(WP_REST_Request $request): WP_REST_Response
    {
        $isConnected = $this->xeroService->isConnected();
        $tenantId = $isConnected ? get_option('ca_xero_tenant_id') : null;

        return $this->respond([
            'ok' => true,
            'connected' => $isConnected,
            'tenantId' => $tenantId ?: null,
        ]);
    }

    private function disconnect(WP_REST_Request $request): WP_REST_Response
    {
        $this->xeroService->disconnect();

        return $this->respond([
            'ok' => true,
            'message' => __('Xero disconnected successfully.', 'cheapalarms'),
        ]);
    }

    private function syncInvoice(WP_REST_Request $request): WP_REST_Response
    {
        // Get parameters from JSON body (POST request)
        $body = $request->get_json_params();
        $invoiceId = sanitize_text_field($body['invoiceId'] ?? '');
        $locationId = sanitize_text_field($body['locationId'] ?? '');

        if (empty($invoiceId)) {
            return $this->respond(new WP_Error('missing_invoice_id', __('Invoice ID is required.', 'cheapalarms'), ['status' => 400]));
        }

        $config = $this->container->get(Config::class);
        $locationId = $locationId ?: $config->getLocationId();
        if ($locationId === '') {
            return $this->respond(new WP_Error('missing_location', __('Location ID is required.', 'cheapalarms'), ['status' => 400]));
        }

        $invoiceService = $this->container->get(\CheapAlarms\Plugin\Services\InvoiceService::class);

        if (!$config->isGhlFetchAllowed()) {
            $repo = $this->container->get(InvoiceSnapshotRepository::class);
            $raw = $repo->getByInvoiceId($invoiceId, $locationId);
            if (is_wp_error($raw)) {
                return $this->respond($raw);
            }
            $ghlInvoice = $this->invoiceFromSnapshotForXero($raw, $invoiceId);
        } else {
            $ghlInvoice = $invoiceService->getInvoice($invoiceId, $locationId);
            if (is_wp_error($ghlInvoice)) {
                return $this->respond($ghlInvoice);
            }
        }

        // Extract contact data
        $contact = $ghlInvoice['contact'] ?? [];
        $contactName = trim($contact['name'] ?? '');
        
        // Split name into first and last name, handling edge cases
        $firstName = '';
        $lastName = '';
        if (!empty($contactName)) {
            $nameParts = preg_split('/\s+/', $contactName, 2);
            $firstName = $nameParts[0] ?? '';
            $lastName = $nameParts[1] ?? '';
        }
        
        $contactData = [
            'name' => $contactName ?: 'Unknown',
            'email' => $contact['email'] ?? '',
            'firstName' => $firstName,
            'lastName' => $lastName,
            'phone' => $contact['phone'] ?? '',
        ];

        // Create invoice in Xero
        $result = $this->xeroService->createInvoice($ghlInvoice, $contactData);

        if (is_wp_error($result)) {
            return $this->respond($result);
        }

        // Store Xero invoice ID in portal meta (find estimate ID from invoice ID)
        $portalMetaRepo = $this->container->get(\CheapAlarms\Plugin\Services\Shared\PortalMetaRepository::class);
        $estimateId = $portalMetaRepo->findEstimateIdByInvoiceId($invoiceId);
        
        if ($estimateId) {
            $meta = $portalMetaRepo->get($estimateId);
            $invoiceMeta = $meta['invoice'] ?? [];
            $invoiceMeta['xeroInvoiceId'] = $result['invoiceId'];
            $invoiceMeta['xeroInvoiceNumber'] = $result['invoiceNumber'];
            // Update sync status for manual sync
            $invoiceMeta['xeroSync'] = [
                'status' => 'success',
                'attemptedAt' => current_time('mysql'),
                'error' => null,
                'retryCount' => ($invoiceMeta['xeroSync']['retryCount'] ?? 0),
                'note' => 'Manually synced via admin',
            ];
            $portalMetaRepo->merge($estimateId, ['invoice' => $invoiceMeta]);
            
            $this->logger->info('Stored Xero invoice ID in portal meta (manual sync)', [
                'estimateId' => $estimateId,
                'invoiceId' => $invoiceId,
                'xeroInvoiceId' => $result['invoiceId'],
            ]);
        } else {
            $this->logger->warning('Could not find estimate ID for invoice, Xero invoice ID not stored', [
                'invoiceId' => $invoiceId,
                'xeroInvoiceId' => $result['invoiceId'],
            ]);
        }

        return $this->respond([
            'ok' => true,
            'xeroInvoiceId' => $result['invoiceId'],
            'xeroInvoiceNumber' => $result['invoiceNumber'],
            'message' => __('Invoice synced to Xero successfully.', 'cheapalarms'),
        ]);
    }

    private function syncPayment(WP_REST_Request $request): WP_REST_Response
    {
        $body = $request->get_json_params();
        $invoiceId = sanitize_text_field($body['invoiceId'] ?? '');
        $transactionId = sanitize_text_field($body['transactionId'] ?? '');

        if (empty($invoiceId)) {
            return $this->respond(new WP_Error('missing_invoice_id', __('Invoice ID is required.', 'cheapalarms'), ['status' => 400]));
        }

        $lockKey = 'ca_xero_payment_sync_lock_' . $invoiceId;
        $lockValue = get_transient($lockKey);
        if ($lockValue !== false) {
            return $this->respond(new WP_Error('sync_in_progress', __('Xero payment sync is already in progress.', 'cheapalarms'), ['status' => 409]));
        }
        set_transient($lockKey, time(), 30);

        try {
        $portalMetaRepo = $this->container->get(\CheapAlarms\Plugin\Services\Shared\PortalMetaRepository::class);
        $estimateId = $portalMetaRepo->findEstimateIdByInvoiceId($invoiceId);
        if (!$estimateId) {
            return $this->respond(new WP_Error('estimate_not_found', __('Estimate not found for this invoice.', 'cheapalarms'), ['status' => 404]));
        }

        $meta = $portalMetaRepo->get($estimateId);
        $invoiceMeta = $meta['invoice'] ?? [];
        $paymentMeta = $meta['payment'] ?? [];
        $payments = $paymentMeta['payments'] ?? [];

        if (!is_array($payments) || empty($payments)) {
            return $this->respond(new WP_Error('no_payments', __('No payments found for this invoice.', 'cheapalarms'), ['status' => 400]));
        }

        $xeroInvoiceId = $invoiceMeta['xeroInvoiceId'] ?? null;
        if (empty($xeroInvoiceId)) {
            $invoiceNumber = $invoiceMeta['invoiceNumber'] ?? $invoiceMeta['number'] ?? null;
            if ($invoiceNumber) {
                $findResult = $this->xeroService->findInvoiceByNumber($invoiceNumber);
                if (!is_wp_error($findResult) && !empty($findResult['invoiceId'])) {
                    $xeroInvoiceId = $findResult['invoiceId'];
                    $invoiceMeta['xeroInvoiceId'] = $xeroInvoiceId;
                    $invoiceMeta['xeroInvoiceNumber'] = $findResult['invoiceNumber'] ?? $invoiceNumber;
                }
            }
        }

        if (empty($xeroInvoiceId)) {
            return $this->respond(new WP_Error('xero_invoice_missing', __('Xero invoice ID not found. Sync invoice first.', 'cheapalarms'), ['status' => 400]));
        }

        $eligiblePayments = [];
        foreach ($payments as $index => $payment) {
            $isSuccessful = (($payment['status'] ?? null) === 'succeeded');
            $isRefunded = ($payment['refunded'] ?? false) === true;
            $alreadySynced = !empty($payment['xeroPaymentId']) || (!empty($payment['xeroSynced']) && $payment['xeroSynced'] === true);

            if (!$isSuccessful || $isRefunded || $alreadySynced) {
                continue;
            }

            if (!empty($transactionId)) {
                $matchesTransactionId = !empty($payment['transactionId']) && $payment['transactionId'] === $transactionId;
                $matchesPaymentIntentId = !empty($payment['paymentIntentId']) && $payment['paymentIntentId'] === $transactionId;
                if (!$matchesTransactionId && !$matchesPaymentIntentId) {
                    continue;
                }
            }

            $paidAt = $payment['paidAt'] ?? null;
            $timestamp = $paidAt ? strtotime($paidAt) : null;
            $eligiblePayments[] = [
                'index' => $index,
                'payment' => $payment,
                'timestamp' => $timestamp !== false ? $timestamp : null,
            ];
        }

        if (empty($eligiblePayments)) {
            return $this->respond(new WP_Error('no_payment_to_sync', __('No eligible payment found to sync.', 'cheapalarms'), ['status' => 404]));
        }

        if (!empty($transactionId) && count($eligiblePayments) > 1) {
            return $this->respond(new WP_Error(
                'multiple_payment_matches',
                __('Multiple payment records matched this transaction ID. Refusing to sync to prevent duplicates.', 'cheapalarms'),
                ['status' => 409]
            ));
        }

        usort($eligiblePayments, function ($a, $b) {
            $aTime = $a['timestamp'] ?? 0;
            $bTime = $b['timestamp'] ?? 0;
            if ($aTime === $bTime) {
                return ($a['index'] ?? 0) <=> ($b['index'] ?? 0);
            }
            return $aTime <=> $bTime;
        });

        $syncedCount = 0;
        $errors = [];

        foreach ($eligiblePayments as $entry) {
            $paymentIndex = $entry['index'];
            $paymentRecord = $entry['payment'];
            $amount = (float) ($paymentRecord['amount'] ?? 0);
            if ($amount <= 0) {
                $errors[] = [
                    'transactionId' => $paymentRecord['transactionId'] ?? $paymentRecord['paymentIntentId'] ?? null,
                    'error' => 'invalid_payment_amount',
                ];
                continue;
            }

            $paymentMethod = ($paymentRecord['provider'] ?? '') === 'stripe' ? 'Stripe' : ($paymentRecord['provider'] ?? 'Manual');
            $paymentTransactionId = $paymentRecord['transactionId'] ?? $paymentRecord['paymentIntentId'] ?? '';

            $result = $this->xeroService->recordPayment($xeroInvoiceId, $amount, $paymentMethod, $paymentTransactionId);
            if (is_wp_error($result)) {
                $errors[] = [
                    'transactionId' => $paymentTransactionId,
                    'error' => $result->get_error_message(),
                    'errorCode' => $result->get_error_code(),
                ];
                continue;
            }

            $payments[$paymentIndex]['xeroPaymentId'] = $result['paymentId'] ?? null;
            $payments[$paymentIndex]['xeroSynced'] = true;
            $syncedCount += 1;
        }

        if ($syncedCount === 0) {
            return $this->respond(new WP_Error('xero_payment_sync_failed', __('No payments were synced to Xero.', 'cheapalarms'), ['status' => 500, 'errors' => $errors]));
        }

        // Recalculate totals to keep portal meta consistent
        $invoiceTotal = (is_array($invoiceMeta['ghl'] ?? null) ? ($invoiceMeta['ghl']['total'] ?? null) : null)
            ?? ($invoiceMeta['total'] ?? 0);
        $totalPaid = 0;
        foreach ($payments as $payment) {
            $isSuccessful = (($payment['status'] ?? null) === 'succeeded');
            $isRefunded = ($payment['refunded'] ?? false) === true;
            if ($isSuccessful && !$isRefunded && !empty($payment['amount'])) {
                $totalPaid += (float) $payment['amount'];
            }
        }
        $remainingBalance = max(0, ((float) $invoiceTotal) - $totalPaid);
        $isFullyPaid = ((float) $invoiceTotal) > 0 && abs($totalPaid - (float) $invoiceTotal) < 0.01;

        $paymentMeta['payments'] = $payments;
        $paymentMeta['amount'] = $totalPaid;
        $paymentMeta['remainingBalance'] = $remainingBalance;
        $paymentMeta['status'] = $isFullyPaid ? 'paid' : ($totalPaid > 0 ? 'partial' : 'pending');

        $invoiceMeta['amountDue'] = $remainingBalance;
        $invoiceMeta['status'] = $isFullyPaid ? 'paid' : ($totalPaid > 0 ? 'partial' : ($invoiceMeta['status'] ?? 'draft'));
        $invoiceMeta['xeroInvoiceId'] = $xeroInvoiceId;

        $workflow = $meta['workflow'] ?? [];
        if ($isFullyPaid) {
            $workflow['status'] = 'paid';
            $workflow['currentStep'] = 5;
            if (empty($workflow['paidAt'])) {
                $workflow['paidAt'] = current_time('mysql');
            }
        }

        $portalMetaRepo->merge($estimateId, [
            'payment' => $paymentMeta,
            'invoice' => $invoiceMeta,
            'workflow' => $workflow,
        ]);

        $this->logger->info('Payment synced to Xero', [
            'estimateId' => $estimateId,
            'invoiceId' => $invoiceId,
            'xeroInvoiceId' => $xeroInvoiceId,
            'xeroPaymentId' => $result['paymentId'] ?? null,
        ]);

        return $this->respond([
            'ok' => true,
            'xeroInvoiceId' => $xeroInvoiceId,
            'syncedCount' => $syncedCount,
            'errors' => $errors,
            'message' => __('Payment synced to Xero successfully.', 'cheapalarms'),
        ]);
        } finally {
            delete_transient($lockKey);
        }
    }

    /**
     * Normalize snapshot raw_json into the flat shape XeroService::createInvoice() expects
     * (aligned with InvoiceService::getInvoice output).
     *
     * @param array<string, mixed> $raw
     * @return array<string, mixed>
     */
    private function invoiceFromSnapshotForXero(array $raw, string $invoiceId): array
    {
        unset($raw['_snapshotSyncedAt']);
        $invoice = $raw['invoice'] ?? $raw;
        $contact = $invoice['contact'] ?? $invoice['contactDetails'] ?? [];
        $items   = $invoice['items'] ?? $invoice['lineItems'] ?? $invoice['invoiceItems'] ?? [];
        if (!is_array($items)) {
            $items = [];
        }

        return [
            'id'            => $invoice['id'] ?? $invoice['_id'] ?? $invoiceId,
            'invoiceNumber' => $invoice['invoiceNumber'] ?? $invoice['number'] ?? null,
            'status'        => $invoice['status'] ?? 'draft',
            'contact'      => [
                'id'    => $contact['id'] ?? $contact['contactId'] ?? null,
                'name'  => trim($contact['name'] ?? (($contact['firstName'] ?? '') . ' ' . ($contact['lastName'] ?? ''))),
                'email' => $contact['email'] ?? '',
                'phone' => $contact['phone'] ?? '',
            ],
            'items'         => $items,
            'subtotal'      => (float)($invoice['subtotal'] ?? 0),
            'tax'           => (float)($invoice['tax'] ?? 0),
            'discount'      => (float)($invoice['discount'] ?? 0),
            'total'         => (float)($invoice['total'] ?? 0),
            'amountDue'     => (float)($invoice['amountDue'] ?? $invoice['total'] ?? 0),
            'currency'      => $invoice['currency'] ?? 'AUD',
            'issueDate'     => $invoice['issueDate'] ?? null,
            'dueDate'       => $invoice['dueDate'] ?? null,
        ];
    }
}

