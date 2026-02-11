<?php

namespace CheapAlarms\Plugin\Config;

/**
 * Centralized freshness-tier constants for the local-read-cache architecture.
 *
 * Each constant defines how long cached data is considered "fresh" (in seconds).
 * After this window, reads will still return the cached data immediately
 * (stale-while-revalidate) but schedule a background re-sync so the NEXT
 * request gets fresh data.
 *
 * See CODEX-PROMPT-local-read-cache.md § 4.2 for the full tier table.
 *
 * ┌─────────────────┬──────────┬───────────────────────────────┐
 * │ Data Type        │ Max Stale│ Rationale                     │
 * ├─────────────────┼──────────┼───────────────────────────────┤
 * │ Estimates        │  3 min   │ Customer-facing, price-sens.  │
 * │ Invoices         │  3 min   │ Payment-sensitive             │
 * │ Contacts (list)  │ 10 min   │ Changes infrequently          │
 * │ Contacts (search)│ 10 min   │ Same tier as contact list     │
 * │ SM8 Jobs         │ 15 min   │ Operational, not cust.-facing │
 * │ SM8 Companies    │ 30 min   │ Rarely changes                │
 * └─────────────────┴──────────┴───────────────────────────────┘
 */
final class CacheConfig
{
    // ── GHL Entities ─────────────────────────────────────────────────

    /** Estimates: customer-facing, price-sensitive. */
    public const ESTIMATE_STALE_SECONDS = 180;   // 3 minutes

    /** Invoices: payment-sensitive. */
    public const INVOICE_STALE_SECONDS = 180;    // 3 minutes

    /** Contacts (full list): changes infrequently. */
    public const CONTACT_LIST_STALE_SECONDS = 600; // 10 minutes

    /** Contacts (single search by email/id): same tier as contact list. */
    public const CONTACT_SEARCH_STALE_SECONDS = 600; // 10 minutes

    // ── ServiceM8 Entities ───────────────────────────────────────────

    /** SM8 Jobs: operational data, not directly customer-facing. */
    public const SM8_JOB_STALE_SECONDS = 900;    // 15 minutes

    /** SM8 Companies: rarely changes. */
    public const SM8_COMPANY_STALE_SECONDS = 1800; // 30 minutes

    // ── Helpers ──────────────────────────────────────────────────────

    /**
     * Check whether a snapshot synced_at timestamp is within the given freshness window.
     *
     * @param string|null $syncedAt  MySQL datetime from the synced_at column.
     * @param int         $maxAge    Maximum acceptable age in seconds.
     * @return bool  true = fresh, false = stale or unknown.
     */
    public static function isFresh(?string $syncedAt, int $maxAge): bool
    {
        if (!$syncedAt) {
            return false;
        }
        $ts = strtotime($syncedAt);
        if ($ts === false) {
            return false;
        }
        return (time() - $ts) < $maxAge;
    }

    /** Prevent instantiation. */
    private function __construct() {}
}
