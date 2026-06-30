<?php

namespace CheapAlarms\Plugin\Calculators\Resolvers;

use CheapAlarms\Plugin\Calculators\Config\AjaxProducts;
use CheapAlarms\Plugin\Services\Product\ProductSnapshotRepository;
use WP_Error;

class AjaxResolver implements CalculatorResolverInterface
{
    private const INSTALL_BASE = 450.0;
    private const INSTALL_PER_EXTRA_DEVICE = 35.0;

    public function __construct(private ProductSnapshotRepository $repository)
    {
    }

    public function getBrand(): string
    {
        return AjaxProducts::BRAND;
    }

    /**
     * @param array<string, mixed> $selections
     */
    public function validate(array $selections)
    {
        $kit = $this->normalizeKit($selections);
        if ($kit === []) {
            return new WP_Error('invalid_selections', 'Kit is empty', ['status' => 400]);
        }

        $hasHub = false;
        foreach ($kit as $line) {
            $key = (string) ($line['key'] ?? '');
            if ($key === '') {
                return new WP_Error('invalid_selections', 'Kit line missing key', ['status' => 400]);
            }
            if (!AjaxProducts::get($key)) {
                return new WP_Error('invalid_selections', 'Unknown product key: ' . $key, ['status' => 400]);
            }
            $qty = (int) ($line['qty'] ?? 0);
            if ($qty < 1) {
                return new WP_Error('invalid_selections', 'Invalid quantity for ' . $key, ['status' => 400]);
            }
            if (in_array($key, ['hub_plus', 'upgrade'], true)) {
                $hasHub = true;
            }
        }

        if (!$hasHub) {
            return new WP_Error('invalid_selections', 'Kit must include a hub or upgrade kit', ['status' => 400]);
        }

        return true;
    }

    /**
     * @param array<string, mixed> $selections
     * @return array<int, array<string, mixed>>
     */
    public function toLineItems(array $selections, string $locationId): array
    {
        $items = [];
        $kit = $this->normalizeKit($selections);

        foreach ($kit as $line) {
            $key = (string) $line['key'];
            $qty = (int) $line['qty'];
            $tag = isset($line['tag']) && $line['tag'] !== '' ? (string) $line['tag'] : null;

            $row = $this->repository->getCalculatorProductByKey($locationId, self::getBrand(), $key);
            $config = AjaxProducts::get($key);
            $name = (string) ($row['name'] ?? $config['name'] ?? $key);
            if ($tag !== null) {
                $name .= ' (' . $tag . ')';
            }

            $amount = null;
            if (is_array($row) && isset($row['price_amount']) && $row['price_amount'] !== null && $row['price_amount'] !== '') {
                $amount = (float) $row['price_amount'];
            } elseif ($config !== null) {
                $amount = AjaxProducts::priceIncGst((float) ($config['rrpExGst'] ?? 0));
            }

            if ($amount === null || $amount <= 0) {
                continue;
            }

            $items[] = [
                'name'        => $name,
                'description' => (string) ($row['description'] ?? $config['description'] ?? ''),
                'currency'    => 'AUD',
                'amount'      => $amount,
                'qty'         => $qty,
                'type'        => 'one_time',
                'photoRequired' => true,
            ];
        }

        $install = $this->installEstimate($selections, $items);
        if ($install > 0) {
            $items[] = [
                'name'        => 'Professional installation (estimate)',
                'description' => 'Labour to install and commission your Ajax system',
                'currency'    => 'AUD',
                'amount'      => $install,
                'qty'         => 1,
                'type'        => 'one_time',
                'photoRequired' => false,
            ];
        }

        return $items;
    }

    /**
     * @param array<string, mixed> $selections
     * @return array<int, array{name:string,qty:int}>
     */
    public function toSummary(array $selections, string $locationId): array
    {
        $summary = [];
        foreach ($this->normalizeKit($selections) as $line) {
            $key = (string) $line['key'];
            $qty = (int) $line['qty'];
            $tag = isset($line['tag']) && $line['tag'] !== '' ? (string) $line['tag'] : null;

            $row = $this->repository->getCalculatorProductByKey($locationId, self::getBrand(), $key);
            $config = AjaxProducts::get($key);
            $name = (string) ($row['name'] ?? $config['name'] ?? $key);
            if ($tag !== null) {
                $name .= ' (' . $tag . ')';
            }

            $summary[] = ['name' => $name, 'qty' => $qty];
        }

        return $summary;
    }

    /**
     * @param array<string, mixed> $selections
     * @param array<int, array<string, mixed>> $lineItems
     */
    public function installEstimate(array $selections, array $lineItems): float
    {
        $deviceCount = 0;
        foreach ($this->normalizeKit($selections) as $line) {
            $deviceCount += (int) ($line['qty'] ?? 0);
        }

        if ($deviceCount < 1) {
            return 0.0;
        }

        return self::INSTALL_BASE + max(0, $deviceCount - 1) * self::INSTALL_PER_EXTRA_DEVICE;
    }

    /**
     * @param array<string, mixed> $selections
     * @return array<int, array<string, mixed>>
     */
    private function normalizeKit(array $selections): array
    {
        $kit = $selections['kit'] ?? [];
        if (!is_array($kit)) {
            return [];
        }

        $normalized = [];
        foreach ($kit as $line) {
            if (!is_array($line)) {
                continue;
            }
            $normalized[] = [
                'key'    => (string) ($line['key'] ?? ''),
                'qty'    => (int) ($line['qty'] ?? 0),
                'tag'    => $line['tag'] ?? null,
                'colour' => $line['colour'] ?? null,
            ];
        }

        return $normalized;
    }

    /**
     * Hardware subtotal only (excludes install line).
     *
     * @param array<int, array<string, mixed>> $lineItems
     */
    public function hardwareSubtotal(array $lineItems): float
    {
        $total = 0.0;
        foreach ($lineItems as $item) {
            if (strpos((string) ($item['name'] ?? ''), 'installation') !== false) {
                continue;
            }
            $total += (float) ($item['amount'] ?? 0) * (int) ($item['qty'] ?? 1);
        }

        return round($total, 2);
    }
}
