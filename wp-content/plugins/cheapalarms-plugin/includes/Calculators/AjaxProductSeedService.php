<?php

namespace CheapAlarms\Plugin\Calculators;

use CheapAlarms\Plugin\Calculators\Config\AjaxProducts;
use CheapAlarms\Plugin\Services\Product\ProductSnapshotRepository;
use WP_Error;

use function current_time;
use function home_url;
use function is_wp_error;
use function wp_json_encode;

class AjaxProductSeedService
{
    public function __construct(private ProductSnapshotRepository $repository)
    {
    }

    /**
     * Upsert all Ajax calculator products into ca_product_snapshots.
     *
     * @param array<string, mixed> $options mediaBase (optional), mediaOverrides (optional keyed by calculator key)
     * @return array{ok:bool,seeded:int,mediaBase:string}|WP_Error
     */
    public function seed(string $locationId, array $options = []): array|WP_Error
    {
        if ($locationId === '') {
            return new WP_Error('bad_request', 'locationId is required', ['status' => 400]);
        }

        $mediaBase = (string) ($options['mediaBase'] ?? home_url('/wp-content/uploads/2026/06'));
        /** @var array<string, array<string, mixed>> $overrides */
        $overrides = is_array($options['mediaOverrides'] ?? null) ? $options['mediaOverrides'] : [];

        $seeded = 0;
        foreach (AjaxProducts::all() as $key => $config) {
            $media = AjaxProducts::buildMediaUrls($mediaBase, $key, $config['gallery'] ?? []);
            if (isset($overrides[$key]) && is_array($overrides[$key])) {
                $ov = $overrides[$key];
                if (!empty($ov['thumb'])) {
                    $media['thumb'] = (string) $ov['thumb'];
                }
                if (!empty($ov['gallery']) && is_array($ov['gallery'])) {
                    $media['gallery'] = array_values(array_map('strval', $ov['gallery']));
                }
            }

            $raw = [
                'calculatorKey' => $key,
                'brand'         => AjaxProducts::BRAND,
                'cat'           => $config['cat'] ?? '',
                'icon'          => $config['icon'] ?? '',
                'colours'       => $config['colours'] ?? ['white'],
                'alts'          => $config['alts'] ?? [],
                'gallery'       => $media['gallery'],
            ];

            $result = $this->repository->upsertCalculatorProduct($locationId, [
                'productId'   => AjaxProducts::productId($key),
                'name'        => (string) ($config['name'] ?? $key),
                'sku'         => (string) ($config['sku'] ?? ''),
                'description' => (string) ($config['description'] ?? ''),
                'image'       => $media['thumb'],
                'productType' => 'physical',
                'amount'      => AjaxProducts::priceIncGst((float) ($config['rrpExGst'] ?? 0)),
                'currency'    => 'AUD',
                'rawJson'     => wp_json_encode($raw),
            ]);

            if ($result instanceof WP_Error) {
                return $result;
            }

            $seeded++;
        }

        return [
            'ok'        => true,
            'seeded'    => $seeded,
            'mediaBase' => $mediaBase,
            'syncedAt'  => current_time('mysql'),
        ];
    }
}
