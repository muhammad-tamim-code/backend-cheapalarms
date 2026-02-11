<?php

namespace CheapAlarms\Plugin\Services\Contact;

use WP_Error;

use function current_time;
use function is_wp_error;
use function wp_json_encode;

class ContactSnapshotRepository
{
    /**
     * Normalize a GHL contact API response into the record shape used by upsertMany/upsertOne.
     *
     * This is intentionally a static helper so any controller/service can call it
     * without needing a full repository instance for the normalization step.
     *
     * @param array<string, mixed> $contactData Raw GHL contact data (from API response or local payload).
     * @return array<string, mixed> Normalized record ready for upsertOne/upsertMany.
     */
    public static function normalizeFromGhl(array $contactData): array
    {
        $contactId = (string)($contactData['id'] ?? $contactData['_id'] ?? $contactData['contactId'] ?? '');

        return [
            'id'           => $contactId,
            'email'        => (string)($contactData['email'] ?? ''),
            'firstName'    => (string)($contactData['firstName'] ?? $contactData['first_name'] ?? ''),
            'lastName'     => (string)($contactData['lastName'] ?? $contactData['last_name'] ?? ''),
            'phone'        => (string)($contactData['phone'] ?? ''),
            'companyName'  => (string)($contactData['companyName'] ?? $contactData['company'] ?? ''),
            'addressLine1' => (string)($contactData['address1'] ?? $contactData['addressLine1'] ?? ''),
            'city'         => (string)($contactData['city'] ?? ''),
            'state'        => (string)($contactData['state'] ?? ''),
            'postalCode'   => (string)($contactData['postalCode'] ?? $contactData['postal_code'] ?? ''),
            'tags'         => is_array($contactData['tags'] ?? null) ? wp_json_encode($contactData['tags']) : (string)($contactData['tags'] ?? ''),
            'dateAdded'    => $contactData['dateAdded'] ?? $contactData['createdAt'] ?? null,
            'createdAt'    => $contactData['createdAt'] ?? $contactData['dateAdded'] ?? null,
            'updatedAt'    => $contactData['updatedAt'] ?? $contactData['dateUpdated'] ?? null,
            'rawJson'      => wp_json_encode($contactData),
        ];
    }

    private string $tableName;

    public function __construct()
    {
        global $wpdb;
        $this->tableName = $wpdb->prefix . 'ca_contact_snapshots';
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * Bulk insert/update contact snapshots.
     *
     * @param array<int, array<string, mixed>> $records Normalized records.
     * @return true|WP_Error
     */
    public function upsertMany(string $locationId, array $records)
    {
        global $wpdb;

        if (!$locationId) {
            return new WP_Error('bad_request', 'locationId is required', ['status' => 400]);
        }

        if (!$records) {
            return true;
        }

        // Build one multi-row insert with ON DUPLICATE KEY UPDATE.
        $syncedAt = current_time('mysql');

        $values = [];
        $params = [];

        foreach ($records as $r) {
            $contactId = (string)($r['id'] ?? '');
            if (!$contactId) {
                continue;
            }

            $values[] = "(%s,%s,NULLIF(%s,''),NULLIF(%s,''),NULLIF(%s,''),NULLIF(%s,''),NULLIF(%s,''),NULLIF(%s,''),NULLIF(%s,''),NULLIF(%s,''),NULLIF(%s,''),NULLIF(%s,''),NULLIF(%s,''),NULLIF(%s,''),NULLIF(%s,''),%s,NULLIF(%s,''))";
            $params[] = $locationId;
            $params[] = $contactId;
            $params[] = (string)($r['email'] ?? '');
            $params[] = (string)($r['firstName'] ?? '');
            $params[] = (string)($r['lastName'] ?? '');
            $params[] = (string)($r['phone'] ?? '');
            $params[] = (string)($r['companyName'] ?? '');
            $params[] = (string)($r['addressLine1'] ?? '');
            $params[] = (string)($r['city'] ?? '');
            $params[] = (string)($r['state'] ?? '');
            $params[] = (string)($r['postalCode'] ?? '');
            $params[] = (string)($r['tags'] ?? '');
            $params[] = (string)($r['dateAdded'] ?? '');
            $params[] = (string)($r['createdAt'] ?? '');
            $params[] = (string)($r['updatedAt'] ?? '');
            $params[] = $syncedAt;
            $params[] = (string)($r['rawJson'] ?? '');
        }

        if (!$values) {
            return true;
        }

        $sql = "
            INSERT INTO {$this->tableName}
                (location_id, contact_id, email, first_name, last_name, phone, company_name, address_line1, city, state, postal_code, tags, ghl_date_added, created_at, updated_at, synced_at, raw_json)
            VALUES " . implode(',', $values) . "
            ON DUPLICATE KEY UPDATE
                email = VALUES(email),
                first_name = VALUES(first_name),
                last_name = VALUES(last_name),
                phone = VALUES(phone),
                company_name = VALUES(company_name),
                address_line1 = VALUES(address_line1),
                city = VALUES(city),
                state = VALUES(state),
                postal_code = VALUES(postal_code),
                tags = VALUES(tags),
                ghl_date_added = VALUES(ghl_date_added),
                created_at = VALUES(created_at),
                updated_at = VALUES(updated_at),
                synced_at = VALUES(synced_at),
                raw_json = VALUES(raw_json),
                deleted_at = NULL
        ";

        $prepared = $wpdb->prepare($sql, $params);
        $res      = $wpdb->query($prepared);

        if ($res === false) {
            return new WP_Error('db_error', 'Failed to upsert contact snapshots', [
                'status'  => 500,
                'details' => $wpdb->last_error,
            ]);
        }

        return true;
    }

    /**
     * List contacts for a location from local DB.
     *
     * @param string|null $search Optional search term (name, email, phone, company)
     * @param int $limit Max results
     * @param int $offset Pagination offset
     * @return array{items: array<int, array<string, mixed>>, total: int}|WP_Error
     */
    public function listByLocation(
        string $locationId,
        ?string $search = null,
        int $limit = 100,
        int $offset = 0
    ) {
        global $wpdb;

        if (!$locationId) {
            return new WP_Error('bad_request', 'locationId is required', ['status' => 400]);
        }

        // Build WHERE clause
        $whereConditions = ['location_id = %s', 'deleted_at IS NULL'];
        $params = [$locationId];

        if ($search) {
            $whereConditions[] = '(email LIKE %s OR first_name LIKE %s OR last_name LIKE %s OR phone LIKE %s OR company_name LIKE %s)';
            $searchLike = '%' . $wpdb->esc_like($search) . '%';
            $params[] = $searchLike;
            $params[] = $searchLike;
            $params[] = $searchLike;
            $params[] = $searchLike;
            $params[] = $searchLike;
        }

        $whereClause = implode(' AND ', $whereConditions);

        // Count total matching rows (for pagination)
        $countParams = $params;
        $total = (int)$wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->tableName} WHERE {$whereClause}",
                ...$countParams
            )
        );

        // Fetch paginated results
        $params[] = $limit;
        $params[] = $offset;

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT contact_id, email, first_name, last_name, phone, company_name,
                        address_line1, city, state, postal_code, tags, ghl_date_added,
                        created_at, updated_at
                 FROM {$this->tableName}
                 WHERE {$whereClause}
                 ORDER BY COALESCE(updated_at, created_at, ghl_date_added) DESC
                 LIMIT %d OFFSET %d",
                ...$params
            ),
            ARRAY_A
        );

        if ($rows === null) {
            return new WP_Error('db_error', 'Failed to read contact snapshots', [
                'status'  => 500,
                'details' => $wpdb->last_error,
            ]);
        }

        $out = [];
        foreach ($rows as $row) {
            $out[] = [
                'id'          => $row['contact_id'] ?? null,
                'email'       => $row['email'] ?? '',
                'firstName'   => $row['first_name'] ?? '',
                'lastName'    => $row['last_name'] ?? '',
                'name'        => trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')),
                'phone'       => $row['phone'] ?? '',
                'companyName' => $row['company_name'] ?? '',
                'address1'    => $row['address_line1'] ?? '',
                'city'        => $row['city'] ?? '',
                'state'       => $row['state'] ?? '',
                'postalCode'  => $row['postal_code'] ?? '',
                'tags'        => ($row['tags'] ? json_decode($row['tags'], true) : null) ?? [],
                'dateAdded'   => $row['ghl_date_added'] ?? '',
                'createdAt'   => $row['created_at'] ?? '',
                'updatedAt'   => $row['updated_at'] ?? '',
            ];
        }

        return [
            'items' => $out,
            'total' => $total,
        ];
    }

    /**
     * Find a contact by email address in local snapshots.
     * Returns the first non-deleted match for this location.
     *
     * @param string $email
     * @param string $locationId
     * @return array<string, mixed>|null|WP_Error  Full raw_json data, null if not found, or WP_Error.
     */
    public function findByEmail(string $email, string $locationId)
    {
        global $wpdb;

        if (!$email) {
            return new WP_Error('bad_request', 'email is required', ['status' => 400]);
        }

        if (!$locationId) {
            return new WP_Error('bad_request', 'locationId is required', ['status' => 400]);
        }

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT contact_id, raw_json, synced_at
                 FROM {$this->tableName}
                 WHERE location_id = %s AND email = %s AND deleted_at IS NULL
                 LIMIT 1",
                $locationId,
                $email
            ),
            ARRAY_A
        );

        if ($row === null) {
            if ($wpdb->last_error) {
                return new WP_Error('db_error', 'Failed to search contact by email', [
                    'status'  => 500,
                    'details' => $wpdb->last_error,
                ]);
            }
            return null; // Not found
        }

        $result = [
            'contactId' => $row['contact_id'],
            'syncedAt'  => $row['synced_at'],
        ];

        // Attach parsed raw_json if available
        if (!empty($row['raw_json'])) {
            $decoded = json_decode($row['raw_json'], true);
            if (is_array($decoded)) {
                $result['data'] = $decoded;
            }
        }

        return $result;
    }

    /**
     * Get full contact data from snapshot by contactId and locationId.
     * Returns the parsed raw_json (same structure as GHL API response).
     *
     * @param string $contactId
     * @param string $locationId
     * @return array<string, mixed>|WP_Error
     */
    public function getByContactId(string $contactId, string $locationId): array|WP_Error
    {
        global $wpdb;

        if (!$contactId) {
            return new WP_Error('bad_request', 'contactId is required', ['status' => 400]);
        }

        if (!$locationId) {
            return new WP_Error('bad_request', 'locationId is required', ['status' => 400]);
        }

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT raw_json, synced_at FROM {$this->tableName}
                 WHERE location_id = %s AND contact_id = %s AND deleted_at IS NULL",
                $locationId,
                $contactId
            ),
            ARRAY_A
        );

        if ($row === null) {
            if ($wpdb->last_error) {
                return new WP_Error('db_error', 'Failed to read contact snapshot', [
                    'status'  => 500,
                    'details' => $wpdb->last_error,
                ]);
            }
            return new WP_Error('not_found', 'Contact not found in snapshots', ['status' => 404]);
        }

        if (empty($row['raw_json'])) {
            return new WP_Error('no_data', 'Contact snapshot exists but raw_json is empty', ['status' => 404]);
        }

        $decoded = json_decode($row['raw_json'], true);
        if (!is_array($decoded)) {
            return new WP_Error('parse_error', 'Failed to parse contact raw_json', [
                'status'  => 500,
                'details' => json_last_error_msg(),
            ]);
        }

        // Attach synced_at so callers can check freshness
        $decoded['_snapshotSyncedAt'] = $row['synced_at'] ?? null;

        return $decoded;
    }

    /**
     * Get the MAX(synced_at) for a location.
     *
     * @return string|null|WP_Error
     */
    public function lastSyncedAt(string $locationId)
    {
        global $wpdb;

        if (!$locationId) {
            return new WP_Error('bad_request', 'locationId is required', ['status' => 400]);
        }

        $val = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT MAX(synced_at) FROM {$this->tableName} WHERE location_id = %s",
                $locationId
            )
        );

        if ($val === null && $wpdb->last_error) {
            return new WP_Error('db_error', 'Failed to read last synced_at', [
                'status'  => 500,
                'details' => $wpdb->last_error,
            ]);
        }

        return is_string($val) ? $val : null;
    }

    /**
     * Upsert a single contact snapshot (used for write-through after GHL mutations).
     *
     * @param string $locationId
     * @param array<string, mixed> $record Normalized record (same shape as upsertMany items)
     * @return true|WP_Error
     */
    public function upsertOne(string $locationId, array $record)
    {
        return $this->upsertMany($locationId, [$record]);
    }

    /**
     * Soft delete a contact snapshot.
     *
     * @param string $contactId
     * @param string $locationId
     * @param int $userId WordPress user ID who deleted
     * @param string|null $reason Optional deletion reason
     * @return bool|WP_Error
     */
    public function softDelete(string $contactId, string $locationId, int $userId, ?string $reason = null): bool|WP_Error
    {
        global $wpdb;

        if (!$contactId) {
            return new WP_Error('bad_request', 'contactId is required', ['status' => 400]);
        }

        if (!$locationId) {
            return new WP_Error('bad_request', 'locationId is required', ['status' => 400]);
        }

        // Check if already soft-deleted
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT deleted_at FROM {$this->tableName}
             WHERE location_id = %s AND contact_id = %s",
            $locationId,
            $contactId
        ));

        if ($existing !== null && $existing !== '') {
            return new WP_Error('already_deleted', 'Contact is already soft-deleted', ['status' => 409]);
        }

        $updated = $wpdb->update(
            $this->tableName,
            [
                'deleted_at'       => current_time('mysql'),
                'deleted_by'       => $userId,
                'deletion_reason'  => $reason,
            ],
            [
                'location_id' => $locationId,
                'contact_id'  => $contactId,
            ],
            ['%s', '%d', '%s'],
            ['%s', '%s']
        );

        if ($updated === false) {
            return new WP_Error('db_error', 'Failed to soft delete contact snapshot', [
                'status'  => 500,
                'details' => $wpdb->last_error,
            ]);
        }

        if ($updated === 0) {
            return new WP_Error('not_found', 'Contact not found for this location', ['status' => 404]);
        }

        return true;
    }

    /**
     * Check if the snapshot table has any non-deleted rows for a given location.
     *
     * @return bool|WP_Error
     */
    public function hasData(string $locationId)
    {
        global $wpdb;

        if (!$locationId) {
            return new WP_Error('bad_request', 'locationId is required', ['status' => 400]);
        }

        $val = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT 1 FROM {$this->tableName} WHERE location_id = %s AND deleted_at IS NULL LIMIT 1",
                $locationId
            )
        );

        if ($wpdb->last_error) {
            return new WP_Error('db_error', 'Failed to check contact snapshot data', [
                'status'  => 500,
                'details' => $wpdb->last_error,
            ]);
        }

        return $val !== null;
    }
}
