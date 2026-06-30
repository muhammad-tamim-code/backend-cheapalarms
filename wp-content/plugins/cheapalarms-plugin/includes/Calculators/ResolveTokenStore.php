<?php

namespace CheapAlarms\Plugin\Calculators;

use function delete_transient;
use function get_transient;
use function set_transient;
use function wp_generate_password;

class ResolveTokenStore
{
    private const PREFIX = 'ca_calc_rt_';
    private const TTL = 900;

    /**
     * @param array<string, mixed> $selections
     */
    public function create(string $brand, array $selections): string
    {
        $token = 'rt_' . wp_generate_password(32, false, false);
        set_transient(self::PREFIX . $token, [
            'brand'       => $brand,
            'selections'  => $selections,
            'created'     => time(),
        ], self::TTL);

        return $token;
    }

    /**
     * @return array{brand:string,selections:array<string,mixed>,created:int}|null
     */
    public function get(string $token): ?array
    {
        if ($token === '' || strpos($token, 'rt_') !== 0) {
            return null;
        }

        $data = get_transient(self::PREFIX . $token);
        if (!is_array($data) || empty($data['selections']) || !is_array($data['selections'])) {
            return null;
        }

        return [
            'brand'      => (string) ($data['brand'] ?? ''),
            'selections' => $data['selections'],
            'created'    => (int) ($data['created'] ?? 0),
        ];
    }

    public function delete(string $token): void
    {
        if ($token !== '') {
            delete_transient(self::PREFIX . $token);
        }
    }
}
