<?php

namespace CheapAlarms\Plugin\Services\Product;

use WP_Error;

use function current_time;
use function wp_json_encode;

class ProductSnapshotRepository
{
    private string $tableName;

    public function __construct()
    {
        global $wpdb;
        $this->tableName = $wpdb->prefix . 'ca_product_snapshots';
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @return bool|WP_Error
     */
    public function hasData(string $locationId)
    {
        global $wpdb;

        if ($locationId === '') {
            return new WP_Error('bad_request', 'locationId is required', ['status' => 400]);
        }

        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->tableName} WHERE location_id = %s",
                $locationId
            )
        );

        if ($count === null && $wpdb->last_error) {
            return new WP_Error('db_error', 'Failed to check product snapshots', [
                'status'  => 500,
                'details' => $wpdb->last_error,
            ]);
        }

        return ((int) $count) > 0;
    }

    /**
     * @return string|null|WP_Error
     */
    public function lastSyncedAt(string $locationId)
    {
        global $wpdb;

        if ($locationId === '') {
            return new WP_Error('bad_request', 'locationId is required', ['status' => 400]);
        }

        $val = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT MAX(synced_at) FROM {$this->tableName} WHERE location_id = %s",
                $locationId
            )
        );

        if ($wpdb->last_error) {
            return new WP_Error('db_error', 'Failed to read product sync time', [
                'status'  => 500,
                'details' => $wpdb->last_error,
            ]);
        }

        return is_string($val) && $val !== '' ? $val : null;
    }

    /**
     * @param array<int, array<string, mixed>> $records
     * @return true|WP_Error
     */
    public function upsertCatalogMany(string $locationId, array $records)
    {
        global $wpdb;

        if ($locationId === '') {
            return new WP_Error('bad_request', 'locationId is required', ['status' => 400]);
        }

        if ($records === []) {
            return true;
        }

        $syncedAt = current_time('mysql');
        $values = [];
        $params = [];

        foreach ($records as $r) {
            $productId = (string) ($r['id'] ?? $r['productId'] ?? '');
            if ($productId === '') {
                continue;
            }

            $values[] = '(%s,%s,%s,%s,%s,%s,%s,%s,%s)';
            $params[] = $locationId;
            $params[] = $productId;
            $params[] = (string) ($r['name'] ?? '');
            $params[] = (string) ($r['sku'] ?? '');
            $params[] = (string) ($r['description'] ?? '');
            $params[] = (string) ($r['image'] ?? '');
            $params[] = (string) ($r['productType'] ?? '');
            $params[] = $syncedAt;
            $params[] = (string) ($r['rawJson'] ?? wp_json_encode($r));
        }

        if ($values === []) {
            return true;
        }

        $sql = "
            INSERT INTO {$this->tableName}
                (location_id, product_id, name, sku, description, image, product_type, synced_at, raw_json)
            VALUES " . implode(',', $values) . "
            ON DUPLICATE KEY UPDATE
                name = VALUES(name),
                sku = VALUES(sku),
                description = VALUES(description),
                image = VALUES(image),
                product_type = VALUES(product_type),
                synced_at = VALUES(synced_at),
                raw_json = VALUES(raw_json)
        ";

        $res = $wpdb->query($wpdb->prepare($sql, $params));

        if ($res === false) {
            return new WP_Error('db_error', 'Failed to upsert product snapshots', [
                'status'  => 500,
                'details' => $wpdb->last_error,
            ]);
        }

        return true;
    }

    /**
     * @return true|WP_Error
     */
    public function updatePrice(
        string $locationId,
        string $productId,
        ?float $amount,
        string $currency = 'AUD',
        ?string $priceId = null
    ) {
        global $wpdb;

        if ($locationId === '' || $productId === '') {
            return new WP_Error('bad_request', 'locationId and productId are required', ['status' => 400]);
        }

        $res = $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$this->tableName}
                 SET price_amount = %f, price_currency = %s, price_id = %s, synced_at = %s
                 WHERE location_id = %s AND product_id = %s",
                $amount ?? 0.0,
                $currency,
                $priceId,
                current_time('mysql'),
                $locationId,
                $productId
            )
        );

        if ($res === false) {
            return new WP_Error('db_error', 'Failed to update product price', [
                'status'  => 500,
                'details' => $wpdb->last_error,
            ]);
        }

        return true;
    }

    /**
     * @return array{items: array<int, array<string, mixed>>, total: int}|WP_Error
     */
    public function listByLocation(string $locationId, ?string $search = null, int $limit = 500, int $offset = 0)
    {
        global $wpdb;

        if ($locationId === '') {
            return new WP_Error('bad_request', 'locationId is required', ['status' => 400]);
        }

        $where = ['location_id = %s'];
        $params = [$locationId];

        if ($search !== null && $search !== '') {
            $like = '%' . $wpdb->esc_like($search) . '%';
            $where[] = '(name LIKE %s OR sku LIKE %s OR description LIKE %s)';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $whereClause = implode(' AND ', $where);

        $total = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->tableName} WHERE {$whereClause}",
                ...$params
            )
        );

        $queryParams = $params;
        $queryParams[] = max(1, min(500, $limit));
        $queryParams[] = max(0, $offset);

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT product_id, name, sku, description, image, product_type,
                        price_amount, price_currency, price_id, synced_at
                 FROM {$this->tableName}
                 WHERE {$whereClause}
                 ORDER BY name ASC
                 LIMIT %d OFFSET %d",
                ...$queryParams
            ),
            ARRAY_A
        );

        if ($rows === null) {
            return new WP_Error('db_error', 'Failed to read product snapshots', [
                'status'  => 500,
                'details' => $wpdb->last_error,
            ]);
        }

        $items = [];
        foreach ($rows as $row) {
            $items[] = $this->rowToFrontend($row);
        }

        return ['items' => $items, 'total' => $total];
    }

    /**
     * @return array<string, mixed>|null|WP_Error
     */
    public function getPrice(string $locationId, string $productId)
    {
        global $wpdb;

        if ($locationId === '' || $productId === '') {
            return new WP_Error('bad_request', 'locationId and productId are required', ['status' => 400]);
        }

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT price_amount, price_currency, price_id, sku
                 FROM {$this->tableName}
                 WHERE location_id = %s AND product_id = %s
                 LIMIT 1",
                $locationId,
                $productId
            ),
            ARRAY_A
        );

        if ($wpdb->last_error) {
            return new WP_Error('db_error', 'Failed to read product price', [
                'status'  => 500,
                'details' => $wpdb->last_error,
            ]);
        }

        if (!$row) {
            return null;
        }

        if ($row['price_amount'] === null || $row['price_amount'] === '') {
            return null;
        }

        return [
            'id'       => $row['price_id'] ?? null,
            'name'     => '',
            'amount'   => $row['price_amount'] !== null ? (float) $row['price_amount'] : 0.0,
            'currency' => $row['price_currency'] ?? 'AUD',
            'sku'      => $row['sku'] ?? '',
        ];
    }

    /**
     * @return string[]
     */
    public function listProductIds(string $locationId, int $limit = 1000, int $offset = 0): array
    {
        global $wpdb;

        if ($locationId === '') {
            return [];
        }

        $rows = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT product_id FROM {$this->tableName}
                 WHERE location_id = %s
                 ORDER BY product_id ASC
                 LIMIT %d OFFSET %d",
                $locationId,
                max(1, $limit),
                max(0, $offset)
            )
        );

        return is_array($rows) ? array_map('strval', $rows) : [];
    }

    public function countProducts(string $locationId): int
    {
        global $wpdb;

        if ($locationId === '') {
            return 0;
        }

        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->tableName} WHERE location_id = %s",
                $locationId
            )
        );
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    public function rowToFrontend(array $row): array
    {
        $amount = $row['price_amount'] ?? null;
        $hasAmount = $amount !== null && $amount !== '';

        return [
            'id'          => $row['product_id'] ?? null,
            'name'        => (string) ($row['name'] ?? ''),
            'sku'         => (string) ($row['sku'] ?? ''),
            'description' => (string) ($row['description'] ?? ''),
            'image'       => (string) ($row['image'] ?? ''),
            'productType' => (string) ($row['product_type'] ?? ''),
            'amount'      => $hasAmount ? (float) $amount : null,
            'currency'    => (string) ($row['price_currency'] ?? 'AUD'),
            'hasPrices'   => $hasAmount,
        ];
    }

    /**
     * Upsert a calculator product with price (used by Ajax seed).
     *
     * @param array<string, mixed> $record
     * @return true|WP_Error
     */
    public function upsertCalculatorProduct(string $locationId, array $record)
    {
        global $wpdb;

        if ($locationId === '') {
            return new WP_Error('bad_request', 'locationId is required', ['status' => 400]);
        }

        $productId = (string) ($record['productId'] ?? '');
        if ($productId === '') {
            return new WP_Error('bad_request', 'productId is required', ['status' => 400]);
        }

        $syncedAt = current_time('mysql');
        $amount = isset($record['amount']) ? (float) $record['amount'] : null;

        $res = $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$this->tableName}
                    (location_id, product_id, name, sku, description, image, product_type,
                     price_amount, price_currency, synced_at, raw_json)
                 VALUES (%s, %s, %s, %s, %s, %s, %s, %f, %s, %s, %s)
                 ON DUPLICATE KEY UPDATE
                    name = VALUES(name),
                    sku = VALUES(sku),
                    description = VALUES(description),
                    image = VALUES(image),
                    product_type = VALUES(product_type),
                    price_amount = VALUES(price_amount),
                    price_currency = VALUES(price_currency),
                    synced_at = VALUES(synced_at),
                    raw_json = VALUES(raw_json)",
                $locationId,
                $productId,
                (string) ($record['name'] ?? ''),
                (string) ($record['sku'] ?? ''),
                (string) ($record['description'] ?? ''),
                (string) ($record['image'] ?? ''),
                (string) ($record['productType'] ?? 'physical'),
                $amount ?? 0.0,
                (string) ($record['currency'] ?? 'AUD'),
                $syncedAt,
                (string) ($record['rawJson'] ?? wp_json_encode($record))
            )
        );

        if ($res === false) {
            return new WP_Error('db_error', 'Failed to upsert calculator product', [
                'status'  => 500,
                'details' => $wpdb->last_error,
            ]);
        }

        return true;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getCalculatorProductByKey(string $locationId, string $brand, string $key): ?array
    {
        global $wpdb;

        if ($locationId === '' || $key === '') {
            return null;
        }

        $productId = 'calc:' . strtolower($brand) . ':' . $key;
        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT product_id, name, sku, description, image, product_type,
                        price_amount, price_currency, synced_at, raw_json
                 FROM {$this->tableName}
                 WHERE location_id = %s AND product_id = %s
                 LIMIT 1",
                $locationId,
                $productId
            ),
            ARRAY_A
        );

        return is_array($row) ? $row : null;
    }

    /**
     * @return array<int, array<string, mixed>>|WP_Error
     */
    public function listCalculatorProducts(string $locationId, string $brand)
    {
        global $wpdb;

        if ($locationId === '') {
            return new WP_Error('bad_request', 'locationId is required', ['status' => 400]);
        }

        $prefix = 'calc:' . strtolower($brand) . ':%';
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT product_id, name, sku, description, image, product_type,
                        price_amount, price_currency, synced_at, raw_json
                 FROM {$this->tableName}
                 WHERE location_id = %s AND product_id LIKE %s
                 ORDER BY name ASC",
                $locationId,
                $prefix
            ),
            ARRAY_A
        );

        if ($rows === null) {
            return new WP_Error('db_error', 'Failed to list calculator products', [
                'status'  => 500,
                'details' => $wpdb->last_error,
            ]);
        }

        return $rows;
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    public function rowToCalculatorCatalog(array $row): array
    {
        $raw = [];
        if (!empty($row['raw_json']) && is_string($row['raw_json'])) {
            $decoded = json_decode($row['raw_json'], true);
            if (is_array($decoded)) {
                $raw = $decoded;
            }
        }

        $productId = (string) ($row['product_id'] ?? '');
        $key = (string) ($raw['calculatorKey'] ?? '');
        if ($key === '' && strpos($productId, ':') !== false) {
            $parts = explode(':', $productId);
            $key = (string) end($parts);
        }

        return [
            'key'     => $key,
            'name'    => (string) ($row['name'] ?? ''),
            'desc'    => (string) ($row['description'] ?? ''),
            'cat'     => (string) ($raw['cat'] ?? ''),
            'icon'    => (string) ($raw['icon'] ?? ''),
            'colours' => $raw['colours'] ?? ['white'],
            'alts'    => $raw['alts'] ?? [],
            'thumb'   => (string) ($row['image'] ?? ''),
            'gallery' => is_array($raw['gallery'] ?? null) ? $raw['gallery'] : [],
        ];
    }
}
