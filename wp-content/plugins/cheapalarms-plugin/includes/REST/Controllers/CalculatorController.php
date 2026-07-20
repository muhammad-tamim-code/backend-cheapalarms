<?php

namespace CheapAlarms\Plugin\REST\Controllers;

use CheapAlarms\Plugin\Calculators\AjaxProductSeedService;
use CheapAlarms\Plugin\Calculators\CalculatorResolverRegistry;
use CheapAlarms\Plugin\Calculators\Config\AjaxProducts;
use CheapAlarms\Plugin\Calculators\ResolveTokenStore;
use CheapAlarms\Plugin\Calculators\Resolvers\AjaxResolver;
use CheapAlarms\Plugin\Config\Config;
use CheapAlarms\Plugin\REST\Auth\Authenticator;
use CheapAlarms\Plugin\Services\Container;
use CheapAlarms\Plugin\Services\Product\ProductSnapshotRepository;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

use function home_url;
use function is_wp_error;
use function register_rest_route;

class CalculatorController implements ControllerInterface
{
    public function __construct(private Container $container)
    {
    }

    public function register(): void
    {
        $auth = $this->container->get(Authenticator::class);

        register_rest_route('ca/v1', '/calculators/(?P<brand>[a-z0-9_-]+)/catalog', [
            'methods'             => 'GET',
            'permission_callback' => '__return_true',
            'callback'            => [$this, 'getCatalog'],
        ]);

        register_rest_route('ca/v1', '/calculators/(?P<brand>[a-z0-9_-]+)/resolve', [
            'methods'             => 'POST',
            'permission_callback' => '__return_true',
            'callback'            => [$this, 'resolve'],
        ]);

        register_rest_route('ca/v1', '/calculators/(?P<brand>[a-z0-9_-]+)/seed', [
            'methods'             => 'POST',
            'permission_callback' => fn () => $auth->requireCapability('ca_manage_portal'),
            'callback'            => [$this, 'seed'],
        ]);
    }

    public function getCatalog(WP_REST_Request $request): WP_REST_Response
    {
        $brand = strtolower((string) $request->get_param('brand'));
        if ($brand !== AjaxProducts::BRAND) {
            return $this->respond(new WP_Error('unknown_brand', 'Unknown calculator brand', ['status' => 404]));
        }

        $config = $this->container->get(Config::class);
        $mediaBase = $this->mediaBaseFromRequest($request);
        $products = $this->buildCatalogFromConfig($mediaBase);
        $source = 'config';

        $locationId = $this->effectiveLocationId($config);
        if ($locationId !== '') {
            $repo = $this->container->get(ProductSnapshotRepository::class);
            $rows = $repo->listCalculatorProducts($locationId, $brand);
            if (!is_wp_error($rows) && $rows !== []) {
                foreach ($rows as $row) {
                    $item = $repo->rowToCalculatorCatalog($row);
                    if (empty($item['key'])) {
                        continue;
                    }
                    $key = (string) $item['key'];
                    $products[$key] = array_merge($products[$key] ?? [], $item);
                }
                $source = 'snapshots';
            }
        }

        return $this->respond([
            'ok'       => true,
            'brand'    => $brand,
            'products' => $products,
            'source'   => $source,
        ]);
    }

    public function resolve(WP_REST_Request $request): WP_REST_Response
    {
        $auth = $this->container->get(Authenticator::class);
        $rateCheck = $auth->enforceRateLimit('quote_request_public');
        if (is_wp_error($rateCheck)) {
            return $this->respond($rateCheck);
        }

        $brand = strtolower((string) $request->get_param('brand'));
        $registry = $this->container->get(CalculatorResolverRegistry::class);
        $resolver = $registry->get($brand);
        if (is_wp_error($resolver)) {
            return $this->respond($resolver);
        }

        $config = $this->container->get(Config::class);
        $locationId = $this->effectiveLocationId($config);

        $body = $request->get_json_params();
        if (!is_array($body)) {
            $body = [];
        }

        $selections = $this->normalizeSelections($body);
        $valid = $resolver->validate($selections);
        if (is_wp_error($valid)) {
            return $this->respond($valid);
        }

        $lineItems = $resolver->toLineItems($selections, $locationId);
        if ($lineItems === []) {
            return $this->respond(new WP_Error('resolve_failed', 'Could not price kit, products may not be seeded', ['status' => 502]));
        }

        $install = $resolver->installEstimate($selections, $lineItems);
        $hardwareSubtotal = $resolver instanceof AjaxResolver
            ? $resolver->hardwareSubtotal($lineItems)
            : round(array_sum(array_map(
                static fn (array $i): float => (float) ($i['amount'] ?? 0) * (int) ($i['qty'] ?? 1),
                array_filter($lineItems, static fn (array $i): bool => strpos((string) ($i['name'] ?? ''), 'installation') === false)
            )), 2);

        $tokenStore = $this->container->get(ResolveTokenStore::class);
        $token = $tokenStore->create($brand, $selections);

        return $this->respond([
            'ok'               => true,
            'hardwareSubtotal' => $hardwareSubtotal,
            'installEstimate'  => round($install, 2),
            'total'            => round($hardwareSubtotal + $install, 2),
            'currency'         => 'AUD',
            'summary'          => $resolver->toSummary($selections, $locationId),
            'resolveToken'     => $token,
        ]);
    }

    public function seed(WP_REST_Request $request): WP_REST_Response
    {
        $brand = strtolower((string) $request->get_param('brand'));
        if ($brand !== AjaxProducts::BRAND) {
            return $this->respond(new WP_Error('unknown_brand', 'Unknown calculator brand', ['status' => 404]));
        }

        $config = $this->container->get(Config::class);
        $locationId = $this->effectiveLocationId($config);
        if ($locationId === '') {
            return $this->respond(new WP_Error('no_location', 'GHL location is not configured, set ghl_location_id in config/instance.php', ['status' => 400]));
        }

        $body = $request->get_json_params();
        if (!is_array($body)) {
            $body = [];
        }

        $options = [];
        if (!empty($body['mediaBase'])) {
            $options['mediaBase'] = (string) $body['mediaBase'];
        }
        if (!empty($body['mediaOverrides']) && is_array($body['mediaOverrides'])) {
            $options['mediaOverrides'] = $body['mediaOverrides'];
        }

        $seed = $this->container->get(AjaxProductSeedService::class);
        $result = $seed->seed($locationId, $options);

        return $this->respond($result);
    }

    private function effectiveLocationId(Config $config): string
    {
        $locationId = $config->getLocationId();
        if ($locationId !== '') {
            return $locationId;
        }

        if (defined('WP_DEBUG') && WP_DEBUG) {
            return 'local-dev';
        }

        return '';
    }

    private function mediaBaseFromRequest(WP_REST_Request $request): string
    {
        $param = trim((string) ($request->get_param('mediaBase') ?? ''));
        if ($param !== '') {
            return rtrim($param, '/');
        }

        if (defined('WP_DEBUG') && WP_DEBUG) {
            return 'https://staging.safeguardsecurity.com.au/wp-content/uploads/2026/06';
        }

        return rtrim(home_url('/wp-content/uploads/2026/06'), '/');
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function buildCatalogFromConfig(string $mediaBase): array
    {
        $products = [];
        foreach (AjaxProducts::all() as $key => $cfg) {
            $galleryFiles = is_array($cfg['gallery'] ?? null) ? $cfg['gallery'] : [];
            $media = AjaxProducts::buildMediaUrls($mediaBase, $key, $galleryFiles);

            $products[$key] = [
                'key'     => $key,
                'name'    => (string) ($cfg['name'] ?? $key),
                'desc'    => (string) ($cfg['description'] ?? ''),
                'cat'     => (string) ($cfg['cat'] ?? ''),
                'icon'    => (string) ($cfg['icon'] ?? ''),
                'colours' => $cfg['colours'] ?? ['white'],
                'alts'    => $cfg['alts'] ?? [],
                'thumb'   => $media['thumb'],
                'gallery' => $media['gallery'],
            ];
        }

        return $products;
    }

    /**
     * @param array<string, mixed> $body
     * @return array<string, mixed>
     */
    private function normalizeSelections(array $body): array
    {
        $kit = $body['kit'] ?? [];
        if (!is_array($kit)) {
            $kit = [];
        }

        return [
            'mode'       => (string) ($body['mode'] ?? 'build'),
            'property'   => $body['property'] ?? null,
            'monitoring' => (string) ($body['monitoring'] ?? 'none'),
            'kit'        => $kit,
        ];
    }

    /**
     * @param array<string, mixed>|WP_Error $payload
     */
    private function respond($payload): WP_REST_Response
    {
        if (is_wp_error($payload)) {
            $status = (int) ($payload->get_error_data()['status'] ?? 400);
            $response = new WP_REST_Response([
                'ok'  => false,
                'err' => $payload->get_error_message(),
                'code' => $payload->get_error_code(),
            ], $status);
        } else {
            $response = new WP_REST_Response($payload, 200);
        }

        $response->header('X-Content-Type-Options', 'nosniff');
        return $response;
    }
}
