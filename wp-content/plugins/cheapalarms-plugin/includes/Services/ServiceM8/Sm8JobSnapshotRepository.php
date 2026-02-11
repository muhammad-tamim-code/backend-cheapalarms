<?php

namespace CheapAlarms\Plugin\Services\ServiceM8;

use WP_Error;

use function current_time;
use function is_wp_error;
use function wp_json_encode;

class Sm8JobSnapshotRepository
{
    private string $tableName;

    public function __construct()
    {
        global $wpdb;
        $this->tableName = $wpdb->prefix . 'ca_sm8_jobs';
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * Normalize a ServiceM8 job API response into the record shape used by upsert methods.
     *
     * @param array<string, mixed> $jobData Raw ServiceM8 job data.
     * @return array<string, mixed> Normalized record ready for upsertOne/upsertMany.
     */
    public static function normalizeFromApi(array $jobData): array
    {
        return [
            'uuid'                  => (string)($jobData['uuid'] ?? ''),
            'companyUuid'           => (string)($jobData['company_uuid'] ?? ''),
            'status'                => (string)($jobData['status'] ?? ''),
            'jobDescription'        => (string)($jobData['job_description'] ?? ''),
            'jobAddress'            => (string)($jobData['job_address'] ?? ''),
            'generatedJobId'        => (string)($jobData['generated_job_id'] ?? ''),
            'assignedToStaffUuid'   => (string)($jobData['assigned_to_staff_uuid'] ?? ''),
            'createdAt'             => $jobData['date'] ?? $jobData['created_at'] ?? null,
            'updatedAt'             => $jobData['edit_date'] ?? $jobData['updated_at'] ?? null,
            'rawJson'               => wp_json_encode($jobData),
        ];
    }

    /**
     * Bulk insert/update job snapshots.
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

            $values[] = "(%s,NULLIF(%s,''),NULLIF(%s,''),NULLIF(%s,''),NULLIF(%s,''),NULLIF(%s,''),NULLIF(%s,''),NULLIF(%s,''),NULLIF(%s,''),%s,NULLIF(%s,''))";
            $params[] = $uuid;
            $params[] = (string)($r['companyUuid'] ?? '');
            $params[] = (string)($r['status'] ?? '');
            $params[] = (string)($r['jobDescription'] ?? '');
            $params[] = (string)($r['jobAddress'] ?? '');
            $params[] = (string)($r['generatedJobId'] ?? '');
            $params[] = (string)($r['assignedToStaffUuid'] ?? '');
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
                (job_uuid, company_uuid, status, job_description, job_address, generated_job_id, assigned_to_staff_uuid, created_at, updated_at, synced_at, raw_json)
            VALUES " . implode(',', $values) . "
            ON DUPLICATE KEY UPDATE
                company_uuid = VALUES(company_uuid),
                status = VALUES(status),
                job_description = VALUES(job_description),
                job_address = VALUES(job_address),
                generated_job_id = VALUES(generated_job_id),
                assigned_to_staff_uuid = VALUES(assigned_to_staff_uuid),
                created_at = VALUES(created_at),
                updated_at = VALUES(updated_at),
                synced_at = VALUES(synced_at),
                raw_json = VALUES(raw_json),
                deleted_at = NULL
        ";

        $prepared = $wpdb->prepare($sql, $params);
        $res      = $wpdb->query($prepared);

        if ($res === false) {
            return new WP_Error('db_error', 'Failed to upsert SM8 job snapshots', [
                'status'  => 500,
                'details' => $wpdb->last_error,
            ]);
        }

        return true;
    }

    /**
     * Upsert a single job snapshot (write-through).
     *
     * @param array<string, mixed> $record Normalized record.
     * @return true|WP_Error
     */
    public function upsertOne(array $record)
    {
        return $this->upsertMany([$record]);
    }

    /**
     * List jobs from local DB with optional filters.
     *
     * @param array<string, mixed> $filters Optional: uuid, company_uuid, status
     * @param int $limit
     * @param int $offset
     * @return array{items: array<int, array<string, mixed>>, total: int}|WP_Error
     */
    public function listAll(array $filters = [], int $limit = 200, int $offset = 0)
    {
        global $wpdb;

        $whereConditions = ['deleted_at IS NULL'];
        $params = [];

        if (!empty($filters['uuid'])) {
            $whereConditions[] = 'job_uuid = %s';
            $params[] = $filters['uuid'];
        }
        if (!empty($filters['company_uuid'])) {
            $whereConditions[] = 'company_uuid = %s';
            $params[] = $filters['company_uuid'];
        }
        if (!empty($filters['status'])) {
            $whereConditions[] = 'status = %s';
            $params[] = $filters['status'];
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

        $fetchSql = "SELECT job_uuid, company_uuid, status, job_description, job_address,
                            generated_job_id, assigned_to_staff_uuid, created_at, updated_at, synced_at
                     FROM {$this->tableName}
                     WHERE {$whereClause}
                     ORDER BY COALESCE(updated_at, created_at) DESC
                     LIMIT %d OFFSET %d";

        $rows = $wpdb->get_results(
            $wpdb->prepare($fetchSql, ...$params),
            ARRAY_A
        );

        if ($rows === null) {
            return new WP_Error('db_error', 'Failed to list SM8 job snapshots', [
                'status'  => 500,
                'details' => $wpdb->last_error,
            ]);
        }

        $out = [];
        foreach ($rows as $row) {
            $out[] = [
                'uuid'                   => $row['job_uuid'] ?? '',
                'company_uuid'           => $row['company_uuid'] ?? '',
                'status'                 => $row['status'] ?? '',
                'job_description'        => $row['job_description'] ?? '',
                'job_address'            => $row['job_address'] ?? '',
                'generated_job_id'       => $row['generated_job_id'] ?? '',
                'assigned_to_staff_uuid' => $row['assigned_to_staff_uuid'] ?? '',
                '_snapshotSyncedAt'      => $row['synced_at'] ?? null,
            ];
        }

        return [
            'items' => $out,
            'total' => $total,
        ];
    }

    /**
     * Get a single job by UUID from local DB.
     *
     * @return array<string, mixed>|WP_Error  Parsed raw_json with _snapshotSyncedAt, or WP_Error.
     */
    public function getByUuid(string $jobUuid): array|WP_Error
    {
        global $wpdb;

        if (!$jobUuid) {
            return new WP_Error('bad_request', 'jobUuid is required', ['status' => 400]);
        }

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT raw_json, synced_at FROM {$this->tableName}
                 WHERE job_uuid = %s AND deleted_at IS NULL",
                $jobUuid
            ),
            ARRAY_A
        );

        if ($row === null) {
            if ($wpdb->last_error) {
                return new WP_Error('db_error', 'Failed to read SM8 job snapshot', [
                    'status'  => 500,
                    'details' => $wpdb->last_error,
                ]);
            }
            return new WP_Error('not_found', 'Job not found in snapshots', ['status' => 404]);
        }

        if (empty($row['raw_json'])) {
            return new WP_Error('no_data', 'Job snapshot exists but raw_json is empty', ['status' => 404]);
        }

        $decoded = json_decode($row['raw_json'], true);
        if (!is_array($decoded)) {
            return new WP_Error('parse_error', 'Failed to parse job raw_json', [
                'status'  => 500,
                'details' => json_last_error_msg(),
            ]);
        }

        $decoded['_snapshotSyncedAt'] = $row['synced_at'] ?? null;

        return $decoded;
    }

    /**
     * Soft delete a job snapshot.
     *
     * @return bool|WP_Error
     */
    public function softDelete(string $jobUuid): bool|WP_Error
    {
        global $wpdb;

        if (!$jobUuid) {
            return new WP_Error('bad_request', 'jobUuid is required', ['status' => 400]);
        }

        $updated = $wpdb->update(
            $this->tableName,
            ['deleted_at' => current_time('mysql')],
            ['job_uuid' => $jobUuid],
            ['%s'],
            ['%s']
        );

        if ($updated === false) {
            return new WP_Error('db_error', 'Failed to soft delete SM8 job snapshot', [
                'status'  => 500,
                'details' => $wpdb->last_error,
            ]);
        }

        return true;
    }

    /**
     * Get MAX(synced_at) across all jobs.
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
            return new WP_Error('db_error', 'Failed to check SM8 job snapshot data', [
                'status'  => 500,
                'details' => $wpdb->last_error,
            ]);
        }

        return $val !== null;
    }
}
