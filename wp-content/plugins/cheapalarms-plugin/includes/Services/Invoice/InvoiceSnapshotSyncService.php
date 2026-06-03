<?php

namespace CheapAlarms\Plugin\Services\Invoice;

use CheapAlarms\Plugin\Config\Config;
use CheapAlarms\Plugin\Services\InvoiceService;
use CheapAlarms\Plugin\Services\Logger;
use WP_Error;

use function is_wp_error;
use function wp_json_encode;

class InvoiceSnapshotSyncService
{
    public function __construct(
        private InvoiceService $invoiceService,
        private InvoiceSnapshotRepository $repo,
        private Logger $logger,
        private Config $config,
    ) {
    }

    /**
     * Sync ALL invoices for a location into the snapshots table.
     *
     * Uses a distributed lock (transient) to prevent concurrent syncs.
     *
     * @return array{ok:bool, locationId:string, pages:int, count:int, durationMs:float, skipped?:string}|WP_Error
     */
    public function syncLocation(string $locationId, int $pageSize = 50, int $maxPages = 200)
    {
        if (!$this->config->isGhlFetchAllowed()) {
            $this->logger->info('[INVOICE_SNAPSHOTS] sync skipped — GHL fetch disabled', ['locationId' => $locationId]);

            return [
                'ok'           => true,
                'locationId'   => $locationId,
                'pages'        => 0,
                'count'        => 0,
                'durationMs'   => 0.0,
                'skipped'      => 'ghl_fetch_disabled',
            ];
        }

        // Distributed lock to prevent concurrent syncs
        $lockKey = 'ca_sync_invoice_lock_' . $locationId;
        if (get_transient($lockKey)) {
            return new WP_Error('sync_locked', 'Invoice sync already in progress for this location', ['status' => 409]);
        }
        set_transient($lockKey, true, 120); // 2-minute lock

        $pageSize = max(1, min(100, $pageSize));
        $offset   = '0';
        $pages    = 0;
        $count    = 0;
        $start    = microtime(true);

        try {
            while ($pages < $maxPages) {
                $pages++;

                $page = $this->invoiceService->fetchInvoiceListPage($locationId, $pageSize, $offset);
                if (is_wp_error($page)) {
                    return $page;
                }

                $items = $page['items'] ?? [];

                $normalized = [];
                foreach ($items as $record) {
                    $invoiceId = $record['id'] ?? $record['_id'] ?? $record['invoiceId'] ?? null;
                    if (!$invoiceId) {
                        continue;
                    }

                    $contact = $record['contact'] ?? $record['contactDetails'] ?? [];
                    $contactName = $contact['name']
                        ?? (trim(($contact['firstName'] ?? '') . ' ' . ($contact['lastName'] ?? '')))
                        ?: '';
                    $contactEmail = $contact['email'] ?? '';
                    $contactId = $contact['id'] ?? $contact['contactId'] ?? '';

                    $normalized[] = [
                        'id'            => (string)$invoiceId,
                        'invoiceNumber' => $record['invoiceNumber'] ?? $record['number'] ?? null,
                        'contactId'     => $contactId,
                        'email'         => $contactEmail,
                        'name'          => $contactName,
                        'status'        => $record['status'] ?? '',
                        'total'         => (float)($record['total'] ?? 0),
                        'amountPaid'    => (float)($record['amountPaid'] ?? 0),
                        'currency'      => $record['currency'] ?? 'AUD',
                        'dueDate'       => $record['dueDate'] ?? null,
                        'createdAt'     => $record['createdAt'] ?? null,
                        'updatedAt'     => $record['updatedAt'] ?? null,
                        'rawJson'       => wp_json_encode($record),
                    ];
                }

                $res = $this->repo->upsertMany($locationId, $normalized);
                if (is_wp_error($res)) {
                    return $res;
                }

                $count += count($normalized);

                $next = $page['nextOffset'] ?? null;
                if (!$next) {
                    break;
                }
                $offset = (string)$next;
            }
        } finally {
            // Always release the lock
            delete_transient($lockKey);
        }

        $durationMs = round((microtime(true) - $start) * 1000, 2);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            $this->logger->debug('[INVOICE_SNAPSHOTS] synced location', [
                'locationId' => $locationId,
                'pages'      => $pages,
                'count'      => $count,
                'durationMs' => $durationMs,
            ]);
        }

        return [
            'ok'         => true,
            'locationId' => $locationId,
            'pages'      => $pages,
            'count'      => $count,
            'durationMs' => $durationMs,
        ];
    }
}
