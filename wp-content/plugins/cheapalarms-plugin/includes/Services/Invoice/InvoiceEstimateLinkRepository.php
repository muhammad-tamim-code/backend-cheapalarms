<?php

namespace CheapAlarms\Plugin\Services\Invoice;

use WP_Error;

use function current_time;

/**
 * Indexed reverse lookup: GHL invoice_id → WP estimate_id.
 *
 * Replaces the old O(N) "SELECT … FROM wp_options WHERE option_name LIKE 'ca_portal_meta_%'"
 * scan that ran on every invoice page load. The mapping is now a tiny indexed table populated
 * via the `ca_portal_meta_updated` action whenever an estimate's portal meta gains an invoice.id.
 *
 * Backfill: see backfillFromPortalMeta(); runs once on first schema migration that ships this table.
 */
class InvoiceEstimateLinkRepository
{
    private string $tableName;

    public function __construct()
    {
        global $wpdb;
        $this->tableName = $wpdb->prefix . 'ca_invoice_estimate_links';
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * Upsert a link. Idempotent — safe to call repeatedly with the same args.
     *
     * @return bool true on success, false on failure
     */
    public function link(string $invoiceId, string $estimateId, ?string $locationId = null): bool
    {
        if ($invoiceId === '' || $estimateId === '') {
            return false;
        }

        global $wpdb;

        $now = current_time('mysql');

        $sql = "INSERT INTO {$this->tableName} (invoice_id, estimate_id, location_id, created_at, updated_at)
                VALUES (%s, %s, %s, %s, %s)
                ON DUPLICATE KEY UPDATE
                    estimate_id = VALUES(estimate_id),
                    location_id = COALESCE(VALUES(location_id), location_id),
                    updated_at  = VALUES(updated_at)";

        $prepared = $wpdb->prepare($sql, $invoiceId, $estimateId, $locationId ?? '', $now, $now);
        $result   = $wpdb->query($prepared);

        if ($result === false) {
            if (function_exists('error_log')) {
                error_log(sprintf(
                    '[CA] Failed to upsert invoice→estimate link: %s',
                    $wpdb->last_error
                ));
            }
            return false;
        }

        return true;
    }

    /**
     * Indexed reverse lookup. O(1) on a B-tree primary key.
     */
    public function findEstimateIdByInvoiceId(string $invoiceId): ?string
    {
        if ($invoiceId === '') {
            return null;
        }

        global $wpdb;

        $val = $wpdb->get_var($wpdb->prepare(
            "SELECT estimate_id FROM {$this->tableName} WHERE invoice_id = %s LIMIT 1",
            $invoiceId
        ));

        return is_string($val) && $val !== '' ? $val : null;
    }

    /**
     * Batch reverse lookup using a single indexed IN-clause query.
     *
     * @param string[] $invoiceIds
     * @return array<string, string> Map of invoiceId => estimateId (only links that exist)
     */
    public function batchFindEstimateIdsByInvoiceIds(array $invoiceIds): array
    {
        if (empty($invoiceIds)) {
            return [];
        }

        $normalized = array_values(array_unique(array_filter(
            array_map('strval', $invoiceIds),
            static fn ($v) => $v !== ''
        )));

        if (empty($normalized)) {
            return [];
        }

        global $wpdb;

        $placeholders = implode(',', array_fill(0, count($normalized), '%s'));
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT invoice_id, estimate_id FROM {$this->tableName}
             WHERE invoice_id IN ($placeholders)",
            ...$normalized
        ), ARRAY_A);

        if (!is_array($rows)) {
            return [];
        }

        $map = [];
        foreach ($rows as $row) {
            $map[(string)$row['invoice_id']] = (string)$row['estimate_id'];
        }

        return $map;
    }

    /**
     * Remove a link. Used when an invoice is hard-deleted.
     */
    public function unlink(string $invoiceId): bool
    {
        if ($invoiceId === '') {
            return false;
        }

        global $wpdb;

        $result = $wpdb->delete(
            $this->tableName,
            ['invoice_id' => $invoiceId],
            ['%s']
        );

        return $result !== false;
    }

    /**
     * Bulk unlink (for bulk deletes).
     *
     * @param string[] $invoiceIds
     */
    public function unlinkMany(array $invoiceIds): int
    {
        if (empty($invoiceIds)) {
            return 0;
        }

        global $wpdb;

        $normalized = array_values(array_unique(array_filter(
            array_map('strval', $invoiceIds),
            static fn ($v) => $v !== ''
        )));

        if (empty($normalized)) {
            return 0;
        }

        $placeholders = implode(',', array_fill(0, count($normalized), '%s'));
        $result = $wpdb->query($wpdb->prepare(
            "DELETE FROM {$this->tableName} WHERE invoice_id IN ($placeholders)",
            ...$normalized
        ));

        return $result === false ? 0 : (int)$result;
    }

    /**
     * One-time backfill: walk every `ca_portal_meta_*` row, extract any embedded invoice.id,
     * and populate the link table. Idempotent — safe to run multiple times.
     *
     * Returns the number of links created/updated.
     */
    public function backfillFromPortalMeta(): int
    {
        global $wpdb;

        // Single scan of wp_options. This is the *last* time this scan runs in the codebase —
        // after this backfill all reverse lookups go through the indexed find* methods.
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s",
            'ca_portal_meta_%'
        ));

        if (!is_array($rows) || empty($rows)) {
            return 0;
        }

        $count = 0;
        foreach ($rows as $row) {
            $estimateId = substr($row->option_name, strlen('ca_portal_meta_'));
            if ($estimateId === '' || $estimateId === false) {
                continue;
            }

            $meta = json_decode($row->option_value, true);
            if (!is_array($meta)) {
                continue;
            }

            $invoiceId  = $meta['invoice']['id']   ?? null;
            $locationId = $meta['locationId']      ?? null;

            if (!is_string($invoiceId) || $invoiceId === '') {
                continue;
            }

            if ($this->link($invoiceId, (string)$estimateId, is_string($locationId) ? $locationId : null)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Has this table been populated yet? Used to decide whether to run the backfill on migration.
     */
    public function hasAnyRows(): bool
    {
        global $wpdb;
        $val = $wpdb->get_var("SELECT 1 FROM {$this->tableName} LIMIT 1");
        return $val !== null;
    }
}
