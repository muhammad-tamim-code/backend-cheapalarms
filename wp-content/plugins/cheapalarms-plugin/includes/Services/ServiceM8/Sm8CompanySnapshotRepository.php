<?php

namespace CheapAlarms\Plugin\Services\ServiceM8;

use WP_Error;

use function current_time;
use function is_wp_error;
use function wp_json_encode;

class Sm8CompanySnapshotRepository
{
    private string $tableName;

    public function __construct()
    {
        global $wpdb;
        $this->tableName = $wpdb->prefix . 'ca_sm8_companies';
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * Normalize a ServiceM8 company API response into the record shape used by upsert methods.
     *
     * @param array<string, mixed> $companyData Raw ServiceM8 company data.
     * @return array<string, mixed> Normalized record ready for upsertOne/upsertMany.
     */
    public static function normalizeFromApi(array $companyData): array
    {
        return [
            'uuid'      => (string)($companyData['uuid'] ?? ''),
            'name'      => (string)($companyData['name'] ?? ''),
            'email'     => (string)($companyData['email'] ?? ''),
            'phone'     => (string)($companyData['phone'] ?? ''),
            'address'   => (string)($companyData['address'] ?? ''),
            'city'      => (string)($companyData['city'] ?? ''),
            'state'     => (string)($companyData['state'] ?? ''),
            'postcode'  => (string)($companyData['postcode'] ?? ''),
            'country'   => (string)($companyData['country'] ?? ''),
            'createdAt' => $companyData['date'] ?? $companyData['created_at'] ?? null,
            'updatedAt' => $companyData['edit_date'] ?? $companyData['updated_at'] ?? null,
            'rawJson'   => wp_json_encode($companyData),
        ];
    }

    /**
     * Bulk insert/update company snapshots.
     *
     * @param array<int, array<string, mixed>> $records Normalized records.
     * @return true|WP_Error
     */
    public function upsertMany(array $records)
    {
        global $wpdb;

        if (!$records) {
            return true;
        }

        $syncedAt = current_time('mysql');
        $values   = [];
        $params   = [];

        foreach ($records as $r) {
            $uuid = (string)($r['uuid'] ?? '');
            if (!$uuid) {
                continue;
            }

            $values[] = "(%s,NULLIF(%s,''),NULLIF(%s,''),NULLIF(%s,''),NULLIF(%s,''),NULLIF(%s,''),NULLIF(%s,''),NULLIF(%s,''),NULLIF(%s,''),NULLIF(%s,''),NULLIF(%s,''),%s,NULLIF(%s,''))";
            $params[] = $uuid;
            $params[] = (string)($r['name'] ?? '');
            $params[] = (string)($r['email'] ?? '');
            $params[] = (string)($r['phone'] ?? '');
            $params[] = (string)($r['address'] ?? '');
            $params[] = (string)($r['city'] ?? '');
            $params[] = (string)($r['state'] ?? '');
            $params[] = (string)($r['postcode'] ?? '');
            $params[] = (string)($r['country'] ?? '');
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
                (company_uuid, name, email, phone, address, city, state, postcode, country, created_at, updated_at, synced_at, raw_json)
            VALUES " . implode(',', $values) . "
            ON DUPLICATE KEY UPDATE
                name = VALUES(name),
                email = VALUES(email),
                phone = VALUES(phone),
                address = VALUES(address),
                city = VALUES(city),
                state = VALUES(state),
                postcode = VALUES(postcode),
                country = VALUES(country),
                created_at = VALUES(created_at),
                updated_at = VALUES(updated_at),
                synced_at = VALUES(synced_at),
                raw_json = VALUES(raw_json),
                deleted_at = NULL
        ";

        $prepared = $wpdb->prepare($sql, $params);
        $res      = $wpdb->query($prepared);

        if ($res === false) {
            return new WP_Error('db_error', 'Failed to upsert SM8 company snapshots', [
                'status'  => 500,
                'details' => $wpdb->last_error,
            ]);
        }

        return true;
    }

    /**
     * Upsert a single company snapshot (write-through).
     *
     * @param array<string, mixed> $record Normalized record.
     * @return true|WP_Error
     */
    public function upsertOne(array $record)
    {
        return $this->upsertMany([$record]);
    }

    /**
     * List companies from local DB with optional filters.
     *
     * @param array<string, mixed> $filters Optional: uuid, name, email
     * @param int $limit
     * @param int $offset
     * @return array{items: array<int, array<string, mixed>>, total: int}|WP_Error
     */
    public function listAll(array $filters = [], int $limit = 500, int $offset = 0)
    {
        global $wpdb;

        $whereConditions = ['deleted_at IS NULL'];
        $params = [];

        if (!empty($filters['uuid'])) {
            $whereConditions[] = 'company_uuid = %s';
            $params[] = $filters['uuid'];
        }
        if (!empty($filters['name'])) {
            $whereConditions[] = 'name = %s';
            $params[] = $filters['name'];
        }
        if (!empty($filters['email'])) {
            $whereConditions[] = 'email = %s';
            $params[] = strtolower($filters['email']);
        }

        $whereClause = implode(' AND ', $whereConditions);

        // Count
        $countSql = "SELECT COUNT(*) FROM {$this->tableName} WHERE {$whereClause}";
        $total = $params
            ? (int)$wpdb->get_var($wpdb->prepare($countSql, ...$params))
            : (int)$wpdb->get_var($countSql);

        // Fetch
        $params[] = $limit;
        $params[] = $offset;

        $fetchSql = "SELECT company_uuid, name, email, phone, address, city, state, postcode, country, created_at, updated_at, synced_at
                     FROM {$this->tableName}
                     WHERE {$whereClause}
                     ORDER BY name ASC
                     LIMIT %d OFFSET %d";

        $rows = $wpdb->get_results(
            $wpdb->prepare($fetchSql, ...$params),
            ARRAY_A
        );

        if ($rows === null) {
            return new WP_Error('db_error', 'Failed to list SM8 company snapshots', [
                'status'  => 500,
                'details' => $wpdb->last_error,
            ]);
        }

        $out = [];
        foreach ($rows as $row) {
            $out[] = [
                'uuid'              => $row['company_uuid'] ?? '',
                'name'              => $row['name'] ?? '',
                'email'             => $row['email'] ?? '',
                'phone'             => $row['phone'] ?? '',
                'address'           => $row['address'] ?? '',
                'city'              => $row['city'] ?? '',
                'state'             => $row['state'] ?? '',
                'postcode'          => $row['postcode'] ?? '',
                'country'           => $row['country'] ?? '',
                '_snapshotSyncedAt' => $row['synced_at'] ?? null,
            ];
        }

        return [
            'items' => $out,
            'total' => $total,
        ];
    }

    /**
     * Get a single company by UUID from local DB.
     *
     * @return array<string, mixed>|WP_Error  Parsed raw_json with _snapshotSyncedAt, or WP_Error.
     */
    public function getByUuid(string $companyUuid): array|WP_Error
    {
        global $wpdb;

        if (!$companyUuid) {
            return new WP_Error('bad_request', 'companyUuid is required', ['status' => 400]);
        }

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT raw_json, synced_at FROM {$this->tableName}
                 WHERE company_uuid = %s AND deleted_at IS NULL",
                $companyUuid
            ),
            ARRAY_A
        );

        if ($row === null) {
            if ($wpdb->last_error) {
                return new WP_Error('db_error', 'Failed to read SM8 company snapshot', [
                    'status'  => 500,
                    'details' => $wpdb->last_error,
                ]);
            }
            return new WP_Error('not_found', 'Company not found in snapshots', ['status' => 404]);
        }

        if (empty($row['raw_json'])) {
            return new WP_Error('no_data', 'Company snapshot exists but raw_json is empty', ['status' => 404]);
        }

        $decoded = json_decode($row['raw_json'], true);
        if (!is_array($decoded)) {
            return new WP_Error('parse_error', 'Failed to parse company raw_json', [
                'status'  => 500,
                'details' => json_last_error_msg(),
            ]);
        }

        $decoded['_snapshotSyncedAt'] = $row['synced_at'] ?? null;

        return $decoded;
    }

    /**
     * Find a company by email in local snapshots.
     *
     * @return array<string, mixed>|null|WP_Error  Parsed raw_json, null if not found, or WP_Error.
     */
    public function findByEmail(string $email)
    {
        global $wpdb;

        if (!$email) {
            return new WP_Error('bad_request', 'email is required', ['status' => 400]);
        }

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT company_uuid, raw_json, synced_at
                 FROM {$this->tableName}
                 WHERE email = %s AND deleted_at IS NULL
                 LIMIT 1",
                strtolower($email)
            ),
            ARRAY_A
        );

        if ($row === null) {
            if ($wpdb->last_error) {
                return new WP_Error('db_error', 'Failed to search company by email', [
                    'status'  => 500,
                    'details' => $wpdb->last_error,
                ]);
            }
            return null; // Not found
        }

        $result = [
            'companyUuid' => $row['company_uuid'],
            'syncedAt'    => $row['synced_at'],
        ];

        if (!empty($row['raw_json'])) {
            $decoded = json_decode($row['raw_json'], true);
            if (is_array($decoded)) {
                $result['data'] = $decoded;
            }
        }

        return $result;
    }

    /**
     * Find a company by name in local snapshots.
     *
     * @return array<string, mixed>|null|WP_Error  Parsed raw_json, null if not found, or WP_Error.
     */
    public function findByName(string $name)
    {
        global $wpdb;

        if (!$name) {
            return new WP_Error('bad_request', 'name is required', ['status' => 400]);
        }

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT company_uuid, raw_json, synced_at
                 FROM {$this->tableName}
                 WHERE name = %s AND deleted_at IS NULL
                 LIMIT 1",
                $name
            ),
            ARRAY_A
        );

        if ($row === null) {
            if ($wpdb->last_error) {
                return new WP_Error('db_error', 'Failed to search company by name', [
                    'status'  => 500,
                    'details' => $wpdb->last_error,
                ]);
            }
            return null; // Not found
        }

        $result = [
            'companyUuid' => $row['company_uuid'],
            'syncedAt'    => $row['synced_at'],
        ];

        if (!empty($row['raw_json'])) {
            $decoded = json_decode($row['raw_json'], true);
            if (is_array($decoded)) {
                $result['data'] = $decoded;
            }
        }

        return $result;
    }

    /**
     * Soft delete a company snapshot.
     *
     * @return bool|WP_Error
     */
    public function softDelete(string $companyUuid): bool|WP_Error
    {
        global $wpdb;

        if (!$companyUuid) {
            return new WP_Error('bad_request', 'companyUuid is required', ['status' => 400]);
        }

        $updated = $wpdb->update(
            $this->tableName,
            ['deleted_at' => current_time('mysql')],
            ['company_uuid' => $companyUuid],
            ['%s'],
            ['%s']
        );

        if ($updated === false) {
            return new WP_Error('db_error', 'Failed to soft delete SM8 company snapshot', [
                'status'  => 500,
                'details' => $wpdb->last_error,
            ]);
        }

        return true;
    }

    /**
     * Get MAX(synced_at) across all companies.
     *
     * @return string|null|WP_Error
     */
    public function lastSyncedAt()
    {
        global $wpdb;

        $val = $wpdb->get_var(
            "SELECT MAX(synced_at) FROM {$this->tableName}"
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
     * Check if the snapshot table has any non-deleted rows.
     *
     * @return bool|WP_Error
     */
    public function hasData()
    {
        global $wpdb;

        $val = $wpdb->get_var(
            "SELECT 1 FROM {$this->tableName} WHERE deleted_at IS NULL LIMIT 1"
        );

        if ($wpdb->last_error) {
            return new WP_Error('db_error', 'Failed to check SM8 company snapshot data', [
                'status'  => 500,
                'details' => $wpdb->last_error,
            ]);
        }

        return $val !== null;
    }
}
