<?php

namespace CheapAlarms\Plugin\Services\Contact;

use CheapAlarms\Plugin\Config\Config;
use CheapAlarms\Plugin\Services\GhlClient;
use CheapAlarms\Plugin\Services\Logger;
use WP_Error;

use function is_wp_error;
use function wp_json_encode;

class ContactSnapshotSyncService
{
    public function __construct(
        private GhlClient $ghlClient,
        private ContactSnapshotRepository $repo,
        private Logger $logger,
        private Config $config,
    ) {
    }

    /**
     * Sync ALL contacts for a location into the snapshots table.
     *
     * Uses a distributed lock (transient) to prevent concurrent syncs.
     * GHL contacts API uses cursor-based pagination (startAfterId), not numeric offset.
     *
     * @return array{ok:bool, locationId:string, pages:int, count:int, durationMs:float, skipped?:string}|WP_Error
     */
    public function syncLocation(string $locationId, int $pageSize = 100, int $maxPages = 100)
    {
        if (!$this->config->isGhlFetchAllowed()) {
            $this->logger->info('[CONTACT_SNAPSHOTS] sync skipped, GHL fetch disabled', ['locationId' => $locationId]);

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
        $lockKey = 'ca_sync_contact_lock_' . $locationId;
        if (get_transient($lockKey)) {
            return new WP_Error('sync_locked', 'Contact sync already in progress for this location', ['status' => 409]);
        }
        set_transient($lockKey, true, 180); // 3-minute lock (contacts can be a larger dataset)

        $pageSize = max(1, min(100, $pageSize)); // GHL caps at 100 for contacts
        $pages    = 0;
        $count    = 0;
        $start    = microtime(true);
        $startAfterId = null;

        try {
            while ($pages < $maxPages) {
                $pages++;

                $page = $this->fetchContactListPage($locationId, $pageSize, $startAfterId);
                if (is_wp_error($page)) {
                    return $page;
                }

                $items = $page['items'] ?? [];

                if (empty($items)) {
                    break;
                }

                $normalized = [];
                foreach ($items as $record) {
                    $contactId = $record['id'] ?? $record['_id'] ?? $record['contactId'] ?? null;
                    if (!$contactId) {
                        continue;
                    }

                    $normalized[] = [
                        'id'           => (string)$contactId,
                        'email'        => $record['email'] ?? '',
                        'firstName'    => $record['firstName'] ?? $record['first_name'] ?? '',
                        'lastName'     => $record['lastName'] ?? $record['last_name'] ?? '',
                        'phone'        => $record['phone'] ?? '',
                        'companyName'  => $record['companyName'] ?? $record['company'] ?? '',
                        'addressLine1' => $record['address1'] ?? $record['addressLine1'] ?? '',
                        'city'         => $record['city'] ?? '',
                        'state'        => $record['state'] ?? '',
                        'postalCode'   => $record['postalCode'] ?? $record['postal_code'] ?? '',
                        'tags'         => is_array($record['tags'] ?? null) ? wp_json_encode($record['tags']) : ($record['tags'] ?? ''),
                        'dateAdded'    => $record['dateAdded'] ?? $record['createdAt'] ?? null,
                        'createdAt'    => $record['createdAt'] ?? $record['dateAdded'] ?? null,
                        'updatedAt'    => $record['updatedAt'] ?? $record['dateUpdated'] ?? null,
                        'rawJson'      => wp_json_encode($record),
                    ];
                }

                $res = $this->repo->upsertMany($locationId, $normalized);
                if (is_wp_error($res)) {
                    return $res;
                }

                $count += count($normalized);

                // GHL cursor-based pagination: use startAfterId from meta or last contact ID
                $nextStartAfterId = $page['startAfterId'] ?? null;
                if (!$nextStartAfterId) {
                    break; // No more pages
                }
                $startAfterId = $nextStartAfterId;
            }
        } finally {
            // Always release the lock
            delete_transient($lockKey);
        }

        $durationMs = round((microtime(true) - $start) * 1000, 2);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            $this->logger->debug('[CONTACT_SNAPSHOTS] synced location', [
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

    /**
     * Fetch a single page of contacts from GHL API.
     *
     * GHL /contacts/ endpoint uses cursor-based pagination:
     * - `limit` (max 100)
     * - `startAfterId` (last contact ID from previous page)
     *
     * @param string $locationId
     * @param int $limit
     * @param string|null $startAfterId Cursor from previous page
     * @return array{items: array, startAfterId: string|null}|WP_Error
     */
    private function fetchContactListPage(string $locationId, int $limit = 100, ?string $startAfterId = null)
    {
        $query = [
            'locationId' => $locationId,
            'limit'      => $limit,
        ];

        if ($startAfterId) {
            $query['startAfterId'] = $startAfterId;
        }

        $result = $this->ghlClient->get('/contacts/', $query, 30, $locationId);

        if (is_wp_error($result)) {
            return $result;
        }

        // Parse GHL response structure
        $contacts = [];
        if (isset($result['contacts']) && is_array($result['contacts'])) {
            $contacts = $result['contacts'];
        } elseif (is_array($result) && isset($result[0]) && isset($result[0]['id'])) {
            $contacts = $result;
        }

        // Extract pagination cursor
        $nextStartAfterId = null;
        if (isset($result['meta']['startAfterId'])) {
            $nextStartAfterId = (string)$result['meta']['startAfterId'];
        } elseif (isset($result['meta']['nextPageUrl'])) {
            // Some GHL versions use nextPageUrl, extract startAfterId from it
            parse_str(parse_url($result['meta']['nextPageUrl'], PHP_URL_QUERY) ?? '', $urlParams);
            $nextStartAfterId = $urlParams['startAfterId'] ?? null;
        }

        // If we got a full page but no cursor, use last contact ID as fallback
        if (!$nextStartAfterId && count($contacts) >= $limit) {
            $lastContact = end($contacts);
            $nextStartAfterId = $lastContact['id'] ?? $lastContact['_id'] ?? null;
        }

        return [
            'items'        => $contacts,
            'startAfterId' => $nextStartAfterId,
        ];
    }
}
