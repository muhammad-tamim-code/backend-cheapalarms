<?php

namespace CheapAlarms\Plugin\Commands;

use CheapAlarms\Plugin\Plugin;
use CheapAlarms\Plugin\Services\Invoice\InvoiceEstimateLinkRepository;
use WP_CLI;

/**
 * Re-scan portal meta options and upsert invoice_id → estimate_id rows (repair / drift).
 *
 * ## EXAMPLES
 *
 *     wp cheapalarms rebuild-invoice-links
 */
final class RebuildInvoiceLinksCommand
{
    /**
     * Rebuild links from ca_portal_meta_* options (idempotent).
     *
     * @param array<int, string> $args
     * @param array<string, mixed> $assoc_args
     */
    public function __invoke($args, $assoc_args): void
    {
        /** @var InvoiceEstimateLinkRepository $repo */
        $repo = Plugin::instance()->container()->get(InvoiceEstimateLinkRepository::class);

        WP_CLI::log('Scanning portal meta and upserting wp_ca_invoice_estimate_links…');

        $n = $repo->backfillFromPortalMeta();

        WP_CLI::success(sprintf('Upsert operations completed: %d', $n));
    }
}
