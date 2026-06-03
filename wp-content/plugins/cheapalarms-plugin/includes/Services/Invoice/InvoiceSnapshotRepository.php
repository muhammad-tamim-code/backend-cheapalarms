<?php

namespace CheapAlarms\Plugin\Services\Invoice;

use WP_Error;

use function current_time;
use function is_wp_error;

class InvoiceSnapshotRepository
{
    private string $tableName;

    public function __construct()
    {
        global $wpdb;
        $this->tableName = $wpdb->prefix . 'ca_invoice_snapshots';
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * Bulk insert/update invoice snapshots.
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
            $invoiceId = (string)($r['id'] ?? '');
            if (!$invoiceId) {
                continue;
            }

            $values[] = "(%s,%s,NULLIF(%s,''),NULLIF(%s,''),NULLIF(%s,''),NULLIF(%s,''),NULLIF(%s,''),%f,%f,NULLIF(%s,''),NULLIF(%s,''),NULLIF(%s,''),NULLIF(%s,''),%s,NULLIF(%s,''))";
            $params[] = $locationId;
            $params[] = $invoiceId;
            $params[] = (string)($r['invoiceNumber'] ?? '');
            $params[] = (string)($r['contactId'] ?? '');
            $params[] = (string)($r['email'] ?? '');
            $params[] = (string)($r['name'] ?? '');
            $params[] = (string)($r['status'] ?? ''); // GHL status
            $params[] = (float)($r['total'] ?? 0);
            $params[] = (float)($r['amountPaid'] ?? 0);
            $params[] = (string)($r['currency'] ?? '');
            $params[] = (string)($r['dueDate'] ?? '');
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
                (location_id, invoice_id, invoice_number, contact_id, email, name, ghl_status, total, amount_paid, currency, due_date, created_at, updated_at, synced_at, raw_json)
            VALUES " . implode(',', $values) . "
            ON DUPLICATE KEY UPDATE
                invoice_number = VALUES(invoice_number),
                contact_id = VALUES(contact_id),
                email = VALUES(email),
                name = VALUES(name),
                ghl_status = VALUES(ghl_status),
                total = VALUES(total),
                amount_paid = VALUES(amount_paid),
                currency = VALUES(currency),
                due_date = VALUES(due_date),
                created_at = VALUES(created_at),
                updated_at = VALUES(updated_at),
                synced_at = VALUES(synced_at),
                raw_json = VALUES(raw_json)
        ";

        $prepared = $wpdb->prepare($sql, $params);
        $res      = $wpdb->query($prepared);

        if ($res === false) {
            return new WP_Error('db_error', 'Failed to upsert invoice snapshots', [
                'status'  => 500,
                'details' => $wpdb->last_error,
            ]);
        }

        return true;
    }

    /**
     * Invoice IDs in local snapshots whose email column matches (case-insensitive).
     *
     * @return list<string>
     */
    public function listInvoiceIdsByEmail(string $locationId, string $email): array
    {
        global $wpdb;

        if ($locationId === '' || $email === '') {
            return [];
        }

        $norm = strtolower(trim($email));
        $rows = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT invoice_id FROM {$this->tableName}
                 WHERE location_id = %s AND deleted_at IS NULL AND LOWER(TRIM(email)) = %s",
                $locationId,
                $norm
            )
        );

        if (!is_array($rows)) {
            return [];
        }

        return array_values(array_filter(array_map('strval', $rows)));
    }

    /**
     * List invoices for a location from local DB.
     *
     * @param string|null $status Optional GHL status filter
     * @param string|null $search Optional search term (name, email, invoice number)
     * @param int $limit Max results
     * @param int $offset Pagination offset
     * @return array{items: array<int, array<string, mixed>>, total: int}|WP_Error
     */
    public function listByLocation(
        string $locationId,
        ?string $status = null,
        ?string $search = null,
        int $limit = 20,
        int $offset = 0
    ) {
        global $wpdb;

        if (!$locationId) {
            return new WP_Error('bad_request', 'locationId is required', ['status' => 400]);
        }

        // Build WHERE clause
        $whereConditions = ['location_id = %s', 'deleted_at IS NULL'];
        $params = [$locationId];

        if ($status) {
            $whereConditions[] = 'ghl_status = %s';
            $params[] = $status;
        }

        if ($search) {
            $whereConditions[] = '(invoice_number LIKE %s OR email LIKE %s OR name LIKE %s)';
            $searchLike = '%' . $wpdb->esc_like($search) . '%';
            $params[] = $searchLike;
            $params[] = $searchLike;
            $params[] = $searchLike;
        }

        $whereClause = implode(' AND ', $whereConditions);

        // Count total matching rows (for pagination)
        $countParams = $params; // same params for count query
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
                "SELECT invoice_id, invoice_number, contact_id, email, name, ghl_status, total, amount_paid, currency, due_date, created_at, updated_at
                 FROM {$this->tableName}
                 WHERE {$whereClause}
                 ORDER BY COALESCE(updated_at, created_at) DESC
                 LIMIT %d OFFSET %d",
                ...$params
            ),
            ARRAY_A
        );

        if ($rows === null) {
            return new WP_Error('db_error', 'Failed to read invoice snapshots', [
                'status'  => 500,
                'details' => $wpdb->last_error,
            ]);
        }

        $out = [];
        foreach ($rows as $row) {
            $out[] = [
                'id'            => $row['invoice_id'] ?? null,
                'invoiceNumber' => $row['invoice_number'] ?? null,
                'contactId'     => $row['contact_id'] ?? null,
                'contactName'   => $row['name'] ?? '',
                'contactEmail'  => $row['email'] ?? '',
                'status'        => $row['ghl_status'] ?? '',
                'total'         => (float)($row['total'] ?? 0),
                'amountDue'     => (float)($row['total'] ?? 0) - (float)($row['amount_paid'] ?? 0),
                'currency'      => $row['currency'] ?? 'AUD',
                'dueDate'       => $row['due_date'] ?? null,
                'createdAt'     => $row['created_at'] ?? '',
                'updatedAt'     => $row['updated_at'] ?? '',
            ];
        }

        return [
            'items' => $out,
            'total' => $total,
        ];
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
     * Get full invoice data from snapshot by invoiceId and locationId.
     * Returns the parsed raw_json (same structure as GHL API response).
     *
     * @param string $invoiceId
     * @param string $locationId
     * @return array<string, mixed>|WP_Error
     */
    public function getByInvoiceId(string $invoiceId, string $locationId): array|WP_Error
    {
        global $wpdb;

        if (!$invoiceId) {
            return new WP_Error('bad_request', 'invoiceId is required', ['status' => 400]);
        }

        if (!$locationId) {
            return new WP_Error('bad_request', 'locationId is required', ['status' => 400]);
        }

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT raw_json, synced_at FROM {$this->tableName}
                 WHERE location_id = %s AND invoice_id = %s AND deleted_at IS NULL",
                $locationId,
                $invoiceId
            ),
            ARRAY_A
        );

        if ($row === null) {
            if ($wpdb->last_error) {
                return new WP_Error('db_error', 'Failed to read invoice snapshot', [
                    'status'  => 500,
                    'details' => $wpdb->last_error,
                ]);
            }
            return new WP_Error('not_found', 'Invoice not found in snapshots', ['status' => 404]);
        }

        if (empty($row['raw_json'])) {
            return new WP_Error('no_data', 'Invoice snapshot exists but raw_json is empty', ['status' => 404]);
        }

        $decoded = json_decode($row['raw_json'], true);
        if (!is_array($decoded)) {
            return new WP_Error('parse_error', 'Failed to parse invoice raw_json', [
                'status'  => 500,
                'details' => json_last_error_msg(),
            ]);
        }

        // Attach synced_at so callers can check freshness
        $decoded['_snapshotSyncedAt'] = $row['synced_at'] ?? null;

        return $decoded;
    }

    /**
     * Upsert a single invoice snapshot (used for write-through after GHL mutations).
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
     * Soft delete an invoice snapshot.
     *
     * @param string $invoiceId
     * @param string $locationId
     * @param int $userId WordPress user ID who deleted
     * @param string|null $reason Optional deletion reason
     * @return bool|WP_Error
     */
    public function softDelete(string $invoiceId, string $locationId, int $userId, ?string $reason = null): bool|WP_Error
    {
        global $wpdb;

        if (!$invoiceId) {
            return new WP_Error('bad_request', 'invoiceId is required', ['status' => 400]);
        }

        if (!$locationId) {
            return new WP_Error('bad_request', 'locationId is required', ['status' => 400]);
        }

        // Check if already soft-deleted
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT deleted_at FROM {$this->tableName}
             WHERE location_id = %s AND invoice_id = %s",
            $locationId,
            $invoiceId
        ));

        if ($existing !== null && $existing !== '') {
            return new WP_Error('already_deleted', 'Invoice is already soft-deleted', ['status' => 409]);
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
                'invoice_id'  => $invoiceId,
            ],
            ['%s', '%d', '%s'],
            ['%s', '%s']
        );

        if ($updated === false) {
            return new WP_Error('db_error', 'Failed to soft delete invoice snapshot', [
                'status'  => 500,
                'details' => $wpdb->last_error,
            ]);
        }

        if ($updated === 0) {
            return new WP_Error('not_found', 'Invoice not found for this location', ['status' => 404]);
        }

        return true;
    }

    /**
     * Get time-series data aggregated by date for chart visualization.
     *
     * @param string $locationId
     * @param string $startDate Start date (Y-m-d format)
     * @param string $endDate End date (Y-m-d format)
     * @return array<int, array{date: string, count: int, total: float}>|WP_Error
     */
    public function getTimeSeriesData(string $locationId, string $startDate, string $endDate)
    {
        global $wpdb;

        if (!$locationId) {
            return new WP_Error('bad_request', 'locationId is required', ['status' => 400]);
        }

        $startTimestamp = strtotime($startDate);
        $endTimestamp   = strtotime($endDate);
        if ($startTimestamp === false || $endTimestamp === false) {
            return new WP_Error('bad_request', 'Invalid date format. Use Y-m-d', ['status' => 400]);
        }

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT
                    DATE(COALESCE(created_at, updated_at)) as date,
                    COUNT(*) as count,
                    SUM(COALESCE(total, 0)) as total
                 FROM {$this->tableName}
                 WHERE location_id = %s
                 AND deleted_at IS NULL
                 AND DATE(COALESCE(created_at, updated_at)) >= %s
                 AND DATE(COALESCE(created_at, updated_at)) <= %s
                 GROUP BY DATE(COALESCE(created_at, updated_at))
                 ORDER BY date ASC",
                $locationId,
                $startDate,
                $endDate
            ),
            ARRAY_A
        );

        if ($rows === null) {
            return new WP_Error('db_error', 'Failed to read time-series data', [
                'status'  => 500,
                'details' => $wpdb->last_error,
            ]);
        }

        // Fill in missing dates with zeros
        $result     = [];
        $currentDate     = $startTimestamp;
        $endDateTimestamp = $endTimestamp;
        $dataByDate      = [];

        foreach ($rows as $row) {
            $dataByDate[$row['date']] = [
                'count' => (int)$row['count'],
                'total' => (float)$row['total'],
            ];
        }

        while ($currentDate <= $endDateTimestamp) {
            $dateStr  = date('Y-m-d', $currentDate);
            $result[] = [
                'date'  => $dateStr,
                'count' => $dataByDate[$dateStr]['count'] ?? 0,
                'total' => $dataByDate[$dateStr]['total'] ?? 0.0,
            ];
            $currentDate = strtotime('+1 day', $currentDate);
        }

        return $result;
    }

    /**
     * Check if the snapshot table has any rows for a given location.
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
            return new WP_Error('db_error', 'Failed to check snapshot data', [
                'status'  => 500,
                'details' => $wpdb->last_error,
            ]);
        }

        return $val !== null;
    }
}
