<?php

namespace CheapAlarms\Plugin\REST\Controllers;

use CheapAlarms\Plugin\REST\Auth\Authenticator;
use CheapAlarms\Plugin\Config\CacheConfig;
use CheapAlarms\Plugin\Config\Config;
use CheapAlarms\Plugin\Services\Container;
use CheapAlarms\Plugin\Services\GhlClient;
use CheapAlarms\Plugin\Services\Product\ProductSnapshotRepository;
use CheapAlarms\Plugin\Services\Product\ProductSnapshotSyncService;
use CheapAlarms\Plugin\Services\ProductRepository;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

if (!defined('ABSPATH')) {
    exit;
}

class ProductsController implements ControllerInterface
{
    public function __construct(private Container $container)
    {
    }

    public function register(): void
    {
        $auth = $this->container->get(Authenticator::class);
        $repo = $this->container->get(ProductRepository::class);

        // List products
        register_rest_route('ca/v1', '/products', [
            'methods'             => 'GET',
            'permission_callback' => fn () => $this->isDevBypass() ?: $auth->requireCapability('ca_manage_portal'),
            'callback'            => function () use ($repo) {
                $items = array_values($repo->all());
                $response = new WP_REST_Response($items, 200);
                $this->addSecurityHeaders($response);
                return $response;
            },
        ]);

        // POST /ca/v1/products/ghl/sync — background sync catalog + prices into local DB
        register_rest_route('ca/v1', '/products/ghl/sync', [
            'methods'             => 'POST',
            'permission_callback' => fn () => $this->isDevBypass() ?: $auth->requireCapability('ca_manage_portal'),
            'callback'            => function () {
                $config = $this->container->get(Config::class);
                $locationId = $config->getLocationId();
                if ($locationId === '') {
                    $response = new WP_REST_Response(['ok' => false, 'err' => 'GHL location is not configured'], 400);
                } else {
                    $sync = $this->container->get(ProductSnapshotSyncService::class);
                    $result = $sync->startSync($locationId);
                    if ($result instanceof WP_Error) {
                        $response = new WP_REST_Response(['ok' => false, 'err' => $result->get_error_message()], (int) ($result->get_error_data()['status'] ?? 502));
                    } else {
                        $response = new WP_REST_Response(array_merge(['ok' => true], $result), 200);
                    }
                }
                $this->addSecurityHeaders($response);
                return $response;
            },
        ]);

        // GET /ca/v1/products/ghl?search=&refresh=1
        register_rest_route('ca/v1', '/products/ghl', [
            'methods'             => 'GET',
            'permission_callback' => fn () => $this->isDevBypass() ?: $auth->requireCapability('ca_manage_portal'),
            'callback'            => function (WP_REST_Request $request) {
                $search  = sanitize_text_field((string) ($request->get_param('search') ?? ''));
                $refresh = $request->get_param('refresh') === '1' || $request->get_param('refresh') === 'true';
                $result  = $this->fetchGhlProducts($search, $refresh);
                if ($result instanceof WP_Error) {
                    $response = new WP_REST_Response(['ok' => false, 'err' => $result->get_error_message()], 502);
                } else {
                    $response = new WP_REST_Response($result, 200);
                }
                $this->addSecurityHeaders($response);
                return $response;
            },
        ]);

        // GET /ca/v1/products/ghl/{id}/price — fetch a single product's price on demand
        register_rest_route('ca/v1', '/products/ghl/(?P<id>[a-zA-Z0-9_-]+)/price', [
            'methods'             => 'GET',
            'permission_callback' => fn () => $this->isDevBypass() ?: $auth->requireCapability('ca_manage_portal'),
            'callback'            => function (WP_REST_Request $request) {
                $id = sanitize_text_field((string) $request->get_param('id'));
                $result = $this->getGhlProductPrice($id);
                if ($result instanceof WP_Error) {
                    $response = new WP_REST_Response(['ok' => false, 'err' => $result->get_error_message()], 502);
                } else {
                    $response = new WP_REST_Response(['ok' => true, 'prices' => $result], 200);
                }
                $this->addSecurityHeaders($response);
                return $response;
            },
        ]);

        // Type-filtered lists
        register_rest_route('ca/v1', '/products/base', [
            'methods'             => 'GET',
            'permission_callback' => fn () => $this->isDevBypass() ?: $auth->requireCapability('ca_manage_portal'),
            'callback'            => function () use ($repo) {
                $items = array_values(array_filter($repo->all(), fn ($p) => ($p['type'] ?? '') === 'base'));
                $response = new WP_REST_Response($items, 200);
                $this->addSecurityHeaders($response);
                return $response;
            },
        ]);
        register_rest_route('ca/v1', '/products/addons', [
            'methods'             => 'GET',
            'permission_callback' => fn () => $this->isDevBypass() ?: $auth->requireCapability('ca_manage_portal'),
            'callback'            => function () use ($repo) {
                $items = array_values(array_filter($repo->all(), fn ($p) => ($p['type'] ?? '') === 'addon'));
                $response = new WP_REST_Response($items, 200);
                $this->addSecurityHeaders($response);
                return $response;
            },
        ]);
        register_rest_route('ca/v1', '/products/packages', [
            'methods'             => 'GET',
            'permission_callback' => fn () => $this->isDevBypass() ?: $auth->requireCapability('ca_manage_portal'),
            'callback'            => function () use ($repo) {
                $items = array_values(array_filter($repo->all(), fn ($p) => ($p['type'] ?? '') === 'package'));
                $response = new WP_REST_Response($items, 200);
                $this->addSecurityHeaders($response);
                return $response;
            },
        ]);

        // Get by id
        register_rest_route('ca/v1', '/products/(?P<id>[a-zA-Z0-9_\-]+)', [
            'methods'             => 'GET',
            'permission_callback' => fn () => $this->isDevBypass() ?: $auth->requireCapability('ca_manage_portal'),
            'callback'            => function (WP_REST_Request $request) use ($repo) {
                $id = sanitize_text_field($request->get_param('id'));
                $item = $repo->get($id);
                if (!$item) {
                    return new WP_Error('not_found', __('Product not found.', 'cheapalarms'), ['status' => 404]);
                }
                $response = new WP_REST_Response($item, 200);
                $this->addSecurityHeaders($response);
                return $response;
            },
        ]);

        // Create/Update
        register_rest_route('ca/v1', '/products', [
            'methods'             => 'POST',
            'permission_callback' => fn () => $this->isDevBypass() ?: $auth->requireCapability('ca_manage_portal'),
            'callback'            => function (WP_REST_Request $request) use ($repo) {
                $payload = json_decode($request->get_body(), true);
                if (!is_array($payload)) {
                    return new WP_Error('bad_request', __('Invalid JSON body.', 'cheapalarms'), ['status' => 400]);
                }
                $validated = $this->validateProduct($payload);
                if (is_wp_error($validated)) {
                    return $validated;
                }
                $saved = $repo->save($validated);
                $response = new WP_REST_Response($saved, 200);
                $this->addSecurityHeaders($response);
                return $response;
            },
        ]);

        // Delete
        register_rest_route('ca/v1', '/products/(?P<id>[a-zA-Z0-9_\-]+)', [
            'methods'             => 'DELETE',
            'permission_callback' => fn () => $this->isDevBypass() ?: $auth->requireCapability('ca_manage_portal'),
            'callback'            => function (WP_REST_Request $request) use ($repo) {
                $id = sanitize_text_field($request->get_param('id'));
                $ok = $repo->delete($id);
                if (!$ok) {
                    return new WP_Error('not_found', __('Product not found.', 'cheapalarms'), ['status' => 404]);
                }
                $response = new WP_REST_Response(['ok' => true], 200);
                $this->addSecurityHeaders($response);
                return $response;
            },
        ]);
    }

    /**
     * Validate and normalise the product payload according to agreed schema.
     *
     * @param array $data
     * @return array|WP_Error
     */
    private function validateProduct(array $data): array|WP_Error
    {
        $type = isset($data['type']) ? (string) $data['type'] : '';
        if (!in_array($type, ['base', 'addon', 'package'], true)) {
            return new WP_Error('bad_request', __('type must be one of base|addon|package', 'cheapalarms'), ['status' => 400]);
        }
        $name = isset($data['name']) ? trim((string) $data['name']) : '';
        if ($name === '') {
            return new WP_Error('bad_request', __('name is required', 'cheapalarms'), ['status' => 400]);
        }
        $brand = isset($data['brand']) ? (string) $data['brand'] : null;
        $status = isset($data['status']) && in_array($data['status'], ['active', 'inactive'], true) ? $data['status'] : 'active';

        $price = is_array($data['price'] ?? null) ? $data['price'] : [];
        $oneOffExGst = isset($price['oneOffExGst']) ? (float) $price['oneOffExGst'] : 0.0;
        $installExGst = isset($price['installExGst']) ? (float) $price['installExGst'] : 0.0;
        $recurring = isset($price['recurring']) && is_array($price['recurring']) ? $price['recurring'] : null;
        if ($recurring) {
            $recurring['amountExGst'] = isset($recurring['amountExGst']) ? (float) $recurring['amountExGst'] : 0.0;
            $recurring['interval'] = in_array($recurring['interval'] ?? '', ['month', 'year'], true) ? $recurring['interval'] : 'year';
            $recurring['termMonths'] = isset($recurring['termMonths']) ? (int) $recurring['termMonths'] : 12;
        }

        $gstRate = isset($data['gstRate']) ? (float) $data['gstRate'] : 0.1;
        $installMinutes = isset($data['installMinutes']) ? (int) $data['installMinutes'] : 0;

        $tags = array_values(array_filter(array_map('strval', $data['tags'] ?? [])));
        $attributes = is_array($data['attributes'] ?? null) ? $data['attributes'] : [];
        $id = isset($data['id']) ? (string) $data['id'] : null;

        $out = [
            'id'             => $id,
            'type'           => $type,
            'name'           => $name,
            'brand'          => $brand,
            'status'         => $status,
            'price'          => [
                'oneOffExGst'  => $oneOffExGst,
                'installExGst' => $installExGst,
            ],
            'gstRate'        => $gstRate,
            'installMinutes' => $installMinutes,
            'tags'           => $tags,
            'attributes'     => $attributes,
        ];
        if ($recurring) {
            $out['price']['recurring'] = $recurring;
        }

        if ($type === 'package') {
            $baseId = isset($data['baseId']) ? (string) $data['baseId'] : '';
            if ($baseId === '') {
                return new WP_Error('bad_request', __('package.baseId is required', 'cheapalarms'), ['status' => 400]);
            }
            $components = is_array($data['components'] ?? null) ? $data['components'] : [];
            $normComponents = [];
            foreach ($components as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $addonId = isset($row['addonId']) ? (string) $row['addonId'] : '';
                if ($addonId === '') {
                    continue;
                }
                $normComponents[] = [
                    'addonId'     => $addonId,
                    'minQty'      => isset($row['minQty']) ? (int) $row['minQty'] : 0,
                    'maxQty'      => isset($row['maxQty']) ? (int) $row['maxQty'] : 0,
                    'defaultQty'  => isset($row['defaultQty']) ? (int) $row['defaultQty'] : 0,
                    'powerDrawmA' => isset($row['powerDrawmA']) ? (int) $row['powerDrawmA'] : null,
                    'notes'       => isset($row['notes']) ? (string) $row['notes'] : null,
                ];
            }
            $out['baseId'] = $baseId;
            $out['components'] = $normComponents;
        } elseif ($type === 'base') {
            $addons = is_array($data['addons'] ?? null) ? $data['addons'] : [];
            $normAddons = [];
            foreach ($addons as $row) {
                if (!is_array($row) || empty($row['addonId'])) {
                    continue;
                }
                $normAddons[] = [
                    'addonId'    => (string) $row['addonId'],
                    'minQty'     => isset($row['minQty']) ? (int) $row['minQty'] : 0,
                    'maxQty'     => isset($row['maxQty']) ? (int) $row['maxQty'] : 0,
                    'defaultQty' => isset($row['defaultQty']) ? (int) $row['defaultQty'] : 0,
                ];
            }
            $out['addons'] = $normAddons;
        } else { // addon
            if (isset($data['maxQtyPerSite'])) {
                $out['maxQtyPerSite'] = (int) $data['maxQtyPerSite'];
            }
        }

        return $out;
    }

    /**
     * Allow localhost testing without WP capabilities when explicitly requested.
     * When header X-CA-Dev: 1 or query __dev=1 is present from localhost, bypass auth.
     * You can also set CA_DEV_BYPASS=true in wp-config.php to always bypass (from localhost).
     */
    private function isDevBypass(): bool
    {
        $header = isset($_SERVER['HTTP_X_CA_DEV']) ? trim((string) $_SERVER['HTTP_X_CA_DEV']) : '';
        $query  = isset($_GET['__dev']) ? trim((string) $_GET['__dev']) : '';
        $addr = $_SERVER['REMOTE_ADDR'] ?? '';
        // SECURITY: Never trust Host headers for "local" detection (can be spoofed behind proxies).
        // Local bypass is ONLY allowed from loopback addresses and ONLY in WP_DEBUG.
        $isLocal = in_array($addr, ['127.0.0.1', '::1'], true);
        $isDebug = defined('WP_DEBUG') && WP_DEBUG;

        // Global switch for dev convenience (only from localhost + debug)
        if ($isLocal && $isDebug && defined('CA_DEV_BYPASS') && CA_DEV_BYPASS) {
            return true;
        }

        if ($isLocal && $isDebug && $header === '1') {
            return true;
        }
        if ($isLocal && $isDebug && $query === '1') {
            return true;
        }
        return false;
    }

    /**
     * Local-first GHL catalog with optional live fallback when snapshots are empty.
     *
     * @return array{ok:bool,items:array,total:int,cached:bool,source?:string}|WP_Error
     */
    private function fetchGhlProducts(string $search = '', bool $refresh = false): array|WP_Error
    {
        $config     = $this->container->get(Config::class);
        $locationId = $config->getLocationId();
        if ($locationId === '') {
            return new WP_Error('no_location', 'GHL location is not configured');
        }

        $repo = $this->container->get(ProductSnapshotRepository::class);
        $sync = $this->container->get(ProductSnapshotSyncService::class);

        if ($refresh) {
            $started = $sync->startSync($locationId);
            if ($started instanceof WP_Error && $started->get_error_code() !== 'sync_locked') {
                return $started;
            }
        }

        $hasLocal = $repo->hasData($locationId);
        if (!is_wp_error($hasLocal) && $hasLocal) {
            $local = $repo->listByLocation($locationId, $search !== '' ? $search : null, 500, 0);
            if (!is_wp_error($local)) {
                $lastSynced = $repo->lastSyncedAt($locationId);
                $isStale = is_wp_error($lastSynced) || !CacheConfig::isFresh($lastSynced, CacheConfig::PRODUCT_LIST_STALE_SECONDS);

                if ($isStale && !wp_next_scheduled('ca_sync_product_snapshots', [$locationId])) {
                    wp_schedule_single_event(time() + 1, 'ca_sync_product_snapshots', [$locationId]);
                }

                return [
                    'ok'     => true,
                    'items'  => $local['items'],
                    'total'  => $local['total'],
                    'cached' => !$isStale,
                    'source' => 'local',
                ];
            }
        }

        // Empty snapshots — kick off first sync and fall back to legacy transient/live catalog (no prices).
        if (!is_wp_error($hasLocal) && !$hasLocal && !wp_next_scheduled('ca_sync_product_snapshots', [$locationId])) {
            wp_schedule_single_event(time() + 1, 'ca_sync_product_snapshots', [$locationId]);
        }

        return $this->fetchGhlProductsLive($search, $refresh, $locationId);
    }

    /**
     * Legacy live GHL catalog fetch (metadata only — prices come from snapshots).
     *
     * @return array{ok:bool,items:array,total:int,cached:bool,source?:string}|WP_Error
     */
    private function fetchGhlProductsLive(string $search, bool $refresh, string $locationId): array|WP_Error
    {
        $cacheKey = 'ca_ghl_products_' . md5($locationId);
        $items    = $refresh ? false : get_transient($cacheKey);
        $cached   = is_array($items);

        if (!$cached) {
            $ghl    = $this->container->get(GhlClient::class);
            $items  = [];
            $limit  = 500;
            $offset = 0;
            $guard  = 0;

            do {
                $resp = $ghl->get('/products/', [
                    'locationId' => $locationId,
                    'limit'      => $limit,
                    'offset'     => $offset,
                ], 25, $locationId, 1, true);

                if ($resp instanceof WP_Error) {
                    if ($items !== []) {
                        break;
                    }
                    return $resp;
                }

                $batch = $resp['products'] ?? [];
                if (!is_array($batch) || $batch === []) {
                    break;
                }
                foreach ($batch as $p) {
                    $items[] = $this->normalizeGhlProduct($p);
                }
                $offset += $limit;
                $guard++;
            } while (count($batch) === $limit && $guard < 20);

            set_transient($cacheKey, $items, 30 * MINUTE_IN_SECONDS);
        }

        if ($search !== '') {
            $needle = strtolower($search);
            $items  = array_values(array_filter($items, function ($p) use ($needle) {
                return strpos(strtolower((string) ($p['name'] ?? '')), $needle) !== false
                    || strpos(strtolower((string) ($p['sku'] ?? '')), $needle) !== false
                    || strpos(strtolower((string) ($p['description'] ?? '')), $needle) !== false;
            }));
        }

        return ['ok' => true, 'items' => $items, 'total' => count($items), 'cached' => $cached, 'source' => 'live'];
    }

    /**
     * Read price from local snapshot; live GHL only when missing.
     *
     * @return array|WP_Error
     */
    private function getGhlProductPrice(string $productId): array|WP_Error
    {
        $config     = $this->container->get(Config::class);
        $locationId = $config->getLocationId();
        if ($locationId === '' || $productId === '') {
            return new WP_Error('bad_request', 'Missing location or product id');
        }

        $repo = $this->container->get(ProductSnapshotRepository::class);
        $local = $repo->getPrice($locationId, $productId);
        if (!is_wp_error($local) && is_array($local)) {
            return [$local];
        }

        $cacheKey = 'ca_ghl_price_' . md5($locationId . '|' . $productId);
        $cached   = get_transient($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        $ghl  = $this->container->get(GhlClient::class);
        $resp = $ghl->get('/products/' . rawurlencode($productId) . '/price', [
            'locationId' => $locationId,
        ], 15, $locationId, 1, true);

        if ($resp instanceof WP_Error) {
            return $resp;
        }

        $prices = $resp['prices'] ?? [];
        $out = [];
        foreach ((is_array($prices) ? $prices : []) as $price) {
            $out[] = [
                'id'       => $price['_id'] ?? null,
                'name'     => $price['name'] ?? '',
                'amount'   => isset($price['amount']) ? (float) $price['amount'] : 0.0,
                'currency' => $price['currency'] ?? 'AUD',
                'sku'      => $price['sku'] ?? '',
            ];
        }

        set_transient($cacheKey, $out, 30 * MINUTE_IN_SECONDS);

        $first = $out[0] ?? null;
        if ($first) {
            $repo->updatePrice(
                $locationId,
                $productId,
                (float) ($first['amount'] ?? 0),
                (string) ($first['currency'] ?? 'AUD'),
                isset($first['id']) ? (string) $first['id'] : null
            );
        }

        return $out;
    }

    /**
     * Normalise a raw GHL product into the shape the frontend consumes.
     *
     * @param array<string,mixed> $p
     * @return array<string,mixed>
     */
    private function normalizeGhlProduct(array $p): array
    {
        return [
            'id'          => $p['_id'] ?? null,
            'name'        => (string) ($p['name'] ?? ''),
            'sku'         => (string) ($p['slug'] ?? ''),
            'description' => trim(html_entity_decode(wp_strip_all_tags((string) ($p['description'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8')),
            'image'       => (string) ($p['image'] ?? ''),
            'productType' => (string) ($p['productType'] ?? ''),
            'hasPrices'   => false,
            'amount'      => null,
            'currency'    => 'AUD',
        ];
    }

    /**
     * Add security headers to response
     *
     * @param WP_REST_Response $response
     * @return void
     */
    private function addSecurityHeaders(WP_REST_Response $response): void
    {
        // Prevent MIME type sniffing
        $response->header('X-Content-Type-Options', 'nosniff');
        
        // XSS protection (legacy but still useful)
        $response->header('X-XSS-Protection', '1; mode=block');
        
        // Prevent clickjacking
        $response->header('X-Frame-Options', 'DENY');
        
        // Referrer policy
        $response->header('Referrer-Policy', 'strict-origin-when-cross-origin');
    }
}


