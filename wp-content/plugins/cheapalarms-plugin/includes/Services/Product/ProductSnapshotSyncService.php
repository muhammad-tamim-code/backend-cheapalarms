<?php

namespace CheapAlarms\Plugin\Services\Product;

use CheapAlarms\Plugin\Config\Config;
use CheapAlarms\Plugin\Services\GhlClient;
use CheapAlarms\Plugin\Services\Logger;
use WP_Error;

use function delete_transient;
use function get_transient;
use function is_wp_error;
use function set_transient;
use function wp_json_encode;
use function wp_schedule_single_event;
use function wp_strip_all_tags;
use function html_entity_decode;
use function wp_next_scheduled;

class ProductSnapshotSyncService
{
    private const LOCK_TTL = 600;
    private const PRICE_BATCH_SIZE = 40;

    public function __construct(
        private GhlClient $ghlClient,
        private ProductSnapshotRepository $repo,
        private Logger $logger,
        private Config $config,
    ) {
    }

    /**
     * Start a full catalog + chunked price sync (catalog now, prices via cron batches).
     *
     * @return array<string, mixed>|WP_Error
     */
    public function startSync(string $locationId)
    {
        if ($locationId === '') {
            return new WP_Error('bad_request', 'locationId is required', ['status' => 400]);
        }

        $lockKey = 'ca_sync_product_lock_' . $locationId;
        if (get_transient($lockKey)) {
            return new WP_Error('sync_locked', 'Product sync already in progress', ['status' => 409]);
        }

        set_transient($lockKey, true, self::LOCK_TTL);

        $catalog = $this->syncCatalog($locationId);
        if (is_wp_error($catalog)) {
            delete_transient($lockKey);
            return $catalog;
        }

        $total = $this->repo->countProducts($locationId);
        if ($total > 0 && !wp_next_scheduled('ca_sync_product_price_batch', [$locationId, 0, self::PRICE_BATCH_SIZE])) {
            wp_schedule_single_event(time() + 2, 'ca_sync_product_price_batch', [$locationId, 0, self::PRICE_BATCH_SIZE]);
        } else {
            delete_transient($lockKey);
        }

        return [
            'ok'         => true,
            'locationId' => $locationId,
            'catalog'    => $catalog,
            'total'      => $total,
            'priceSync'  => $total > 0 ? 'scheduled' : 'none',
        ];
    }

    /**
     * Sync product metadata pages from GHL into local DB.
     *
     * @return array{pages:int, count:int, durationMs:float}|WP_Error
     */
    public function syncCatalog(string $locationId)
    {
        $start = microtime(true);
        $items = [];
        $limit = 500;
        $offset = 0;
        $guard = 0;
        $pages = 0;

        do {
            $pages++;
            $guard++;
            if ($guard > 20) {
                break;
            }

            $resp = $this->ghlClient->get('/products/', [
                'locationId' => $locationId,
                'limit'      => $limit,
                'offset'     => $offset,
            ], 25, $locationId, 1, true);

            if (is_wp_error($resp)) {
                if ($items !== []) {
                    break;
                }
                return $resp;
            }

            $batch = $resp['products'] ?? [];
            if (!is_array($batch) || $batch === []) {
                break;
            }

            $normalized = [];
            foreach ($batch as $p) {
                if (!is_array($p)) {
                    continue;
                }
                $normalized[] = $this->normalizeCatalogRecord($p);
            }

            $res = $this->repo->upsertCatalogMany($locationId, $normalized);
            if (is_wp_error($res)) {
                return $res;
            }

            $items = array_merge($items, $normalized);
            $offset += $limit;
        } while (count($batch) === $limit);

        return [
            'pages'      => $pages,
            'count'      => count($items),
            'durationMs' => round((microtime(true) - $start) * 1000, 2),
        ];
    }

    /**
     * Fetch prices for a batch of products; reschedule next batch if needed.
     *
     * @return array<string, mixed>|WP_Error
     */
    public function syncPriceBatch(string $locationId, int $offset = 0, int $batchSize = self::PRICE_BATCH_SIZE)
    {
        $lockKey = 'ca_sync_product_lock_' . $locationId;
        $batchSize = max(1, min(50, $batchSize));
        $ids = $this->repo->listProductIds($locationId, $batchSize, $offset);

        $updated = 0;
        foreach ($ids as $productId) {
            $price = $this->fetchPriceFromGhl($locationId, $productId);
            if (is_wp_error($price)) {
                continue;
            }
            $amount = $price['amount'] ?? 0.0;
            $res = $this->repo->updatePrice(
                $locationId,
                $productId,
                (float) $amount,
                (string) ($price['currency'] ?? 'AUD'),
                isset($price['id']) ? (string) $price['id'] : null
            );
            if (!is_wp_error($res)) {
                $updated++;
            }
        }

        $nextOffset = $offset + $batchSize;
        $total = $this->repo->countProducts($locationId);

        if ($nextOffset < $total) {
            if (!wp_next_scheduled('ca_sync_product_price_batch', [$locationId, $nextOffset, $batchSize])) {
                wp_schedule_single_event(time() + 5, 'ca_sync_product_price_batch', [$locationId, $nextOffset, $batchSize]);
            }
        } else {
            delete_transient($lockKey);
            delete_transient('ca_ghl_products_' . md5($locationId));
        }

        return [
            'ok'       => true,
            'offset'   => $offset,
            'updated'  => $updated,
            'batch'    => count($ids),
            'total'    => $total,
            'complete' => $nextOffset >= $total,
        ];
    }

    /**
     * @return array{id:?string, amount:float, currency:string, sku:string}|WP_Error
     */
    private function fetchPriceFromGhl(string $locationId, string $productId)
    {
        $resp = $this->ghlClient->get(
            '/products/' . rawurlencode($productId) . '/price',
            ['locationId' => $locationId],
            15,
            $locationId,
            1,
            true
        );

        if (is_wp_error($resp)) {
            return $resp;
        }

        $prices = $resp['prices'] ?? [];
        $first = is_array($prices) && isset($prices[0]) && is_array($prices[0]) ? $prices[0] : null;

        if (!$first) {
            return [
                'id'       => null,
                'amount'   => 0.0,
                'currency' => 'AUD',
                'sku'      => '',
            ];
        }

        return [
            'id'       => $first['_id'] ?? null,
            'amount'   => isset($first['amount']) ? (float) $first['amount'] : 0.0,
            'currency' => (string) ($first['currency'] ?? 'AUD'),
            'sku'      => (string) ($first['sku'] ?? ''),
        ];
    }

    /**
     * @param array<string, mixed> $p
     * @return array<string, mixed>
     */
    private function normalizeCatalogRecord(array $p): array
    {
        return [
            'id'          => $p['_id'] ?? null,
            'name'        => (string) ($p['name'] ?? ''),
            'sku'         => (string) ($p['slug'] ?? ''),
            'description' => trim(html_entity_decode(wp_strip_all_tags((string) ($p['description'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8')),
            'image'       => (string) ($p['image'] ?? ''),
            'productType' => (string) ($p['productType'] ?? ''),
            'rawJson'     => wp_json_encode($p),
        ];
    }
}
