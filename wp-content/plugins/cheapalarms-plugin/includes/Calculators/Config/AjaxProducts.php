<?php

namespace CheapAlarms\Plugin\Calculators\Config;

/**
 * Ajax calculator product catalog (metadata + distributor RRP ex GST).
 * Prices are stored inc GST in ca_product_snapshots when seeded.
 */
final class AjaxProducts
{
    public const BRAND = 'ajax';

    /** @var array<string, array<string, mixed>> */
    private static array $products = [
        'hub_plus' => [
            'sku' => '30639.40.WH3',
            'name' => 'Hub 2 Plus',
            'description' => 'Wi-Fi, Ethernet + 4G control panel',
            'rrpExGst' => 540.0,
            'cat' => 'hub',
            'icon' => 'hub',
            'colours' => ['white'],
            'alts' => ['hub_plus'],
            'gallery' => ['hub_plus-extra.webp', 'hub_plus-03.webp', 'hub_plus-02.webp', 'hub_plus-01.webp'],
        ],
        'door' => [
            'sku' => '30616.03.WH3',
            'name' => 'DoorProtect',
            'description' => 'Opening detector',
            'rrpExGst' => 250.0,
            'cat' => 'open',
            'icon' => 'door',
            'colours' => ['white'],
            'alts' => ['door', 'door_plus', 'glass'],
            'gallery' => ['door-01.webp'],
        ],
        'door_plus' => [
            'sku' => '30622.13.WH3',
            'name' => 'DoorProtect Plus',
            'description' => 'Opening + shock + tilt',
            'rrpExGst' => 350.0,
            'cat' => 'open',
            'icon' => 'door',
            'colours' => ['white'],
            'alts' => ['door', 'door_plus', 'glass'],
            'gallery' => ['door_plus-02.webp', 'door_plus-01.webp'],
        ],
        'glass' => [
            'sku' => '30627.05.WH3',
            'name' => 'GlassProtect',
            'description' => 'Glass-break detector',
            'rrpExGst' => 330.0,
            'cat' => 'open',
            'icon' => 'window',
            'colours' => ['white'],
            'alts' => ['door', 'door_plus', 'glass'],
            'gallery' => ['glass-02.webp', 'glass-01.webp'],
        ],
        'motion' => [
            'sku' => '30655.09.WH3',
            'name' => 'MotionProtect',
            'description' => 'Indoor PIR',
            'rrpExGst' => 243.0,
            'cat' => 'motion',
            'icon' => 'motion',
            'colours' => ['white'],
            'alts' => ['motion', 'motion_plus', 'motioncam', 'combi'],
            'gallery' => ['motion-extra.webp', 'motion-02.webp', 'motion-01.webp'],
        ],
        'motion_plus' => [
            'sku' => '30660.02.WH3',
            'name' => 'MotionProtect Plus',
            'description' => 'Dual-tech, pet-immune',
            'rrpExGst' => 374.0,
            'cat' => 'motion',
            'icon' => 'motion',
            'colours' => ['white'],
            'alts' => ['motion', 'motion_plus', 'motioncam', 'combi'],
            'gallery' => ['motion_plus-02.webp', 'motion_plus-01.webp'],
        ],
        'motioncam' => [
            'sku' => '117754.307.WH3',
            'name' => 'MotionCam',
            'description' => 'Motion + photo on alarm',
            'rrpExGst' => 450.0,
            'cat' => 'motion',
            'icon' => 'motion',
            'colours' => ['white'],
            'alts' => ['motion', 'motion_plus', 'motioncam', 'combi'],
            'gallery' => [],
        ],
        'combi' => [
            'sku' => '30614.06.WH3',
            'name' => 'CombiProtect',
            'description' => 'Motion + glass-break',
            'rrpExGst' => 450.0,
            'cat' => 'motion',
            'icon' => 'motion',
            'colours' => ['white'],
            'alts' => ['motion', 'motion_plus', 'motioncam', 'combi'],
            'gallery' => ['combi-03.webp', 'combi-02.webp', 'combi-01.webp'],
        ],
        'siren_in' => [
            'sku' => '30630.11.WH3',
            'name' => 'HomeSiren',
            'description' => 'Indoor siren',
            'rrpExGst' => 162.0,
            'cat' => 'alarm',
            'icon' => 'siren',
            'colours' => ['white'],
            'alts' => ['siren_in'],
            'gallery' => [],
        ],
        'siren_out' => [
            'sku' => '30672.07.WH3',
            'name' => 'StreetSiren',
            'description' => 'Outdoor siren',
            'rrpExGst' => 326.0,
            'cat' => 'alarm',
            'icon' => 'siren',
            'colours' => ['white'],
            'alts' => ['siren_out', 'siren_dd'],
            'gallery' => ['siren_out-02.webp', 'siren_out-01.webp'],
        ],
        'siren_dd' => [
            'sku' => '30674.61.WH3',
            'name' => 'StreetSiren DoubleDeck',
            'description' => 'Outdoor + branding plate',
            'rrpExGst' => 748.0,
            'cat' => 'alarm',
            'icon' => 'siren',
            'colours' => ['white'],
            'alts' => ['siren_out', 'siren_dd'],
            'gallery' => ['siren_dd-03.webp', 'siren_dd-02.webp', 'siren_dd-01.webp'],
        ],
        'keypad_basic' => [
            'sku' => '30644.12.WH3',
            'name' => 'KeyPad',
            'description' => 'Touch keypad',
            'rrpExGst' => 260.0,
            'cat' => 'alarm',
            'icon' => 'keypad',
            'colours' => ['white'],
            'alts' => ['keypad', 'keypad_ts', 'keypad_basic'],
            'gallery' => ['keypad_basic-03.webp', 'keypad_basic-02.webp', 'keypad_basic-01.webp'],
        ],
        'keypad' => [
            'sku' => '30646.83.WH3',
            'name' => 'KeyPad Plus',
            'description' => 'Touch keypad',
            'rrpExGst' => 390.0,
            'cat' => 'alarm',
            'icon' => 'keypad',
            'colours' => ['white'],
            'alts' => ['keypad', 'keypad_ts', 'keypad_basic'],
            'gallery' => ['keypad-extra.webp', 'keypad-06.webp', 'keypad-05.webp', 'keypad-04.webp', 'keypad-03.webp', 'keypad-02.webp', 'keypad-01.webp'],
        ],
        'keypad_ts' => [
            'sku' => '58469.148.WH3',
            'name' => 'KeyPad TouchScreen',
            'description' => 'Touchscreen keypad',
            'rrpExGst' => 890.0,
            'cat' => 'alarm',
            'icon' => 'keypad',
            'colours' => ['white'],
            'alts' => ['keypad', 'keypad_ts', 'keypad_basic'],
            'gallery' => [],
        ],
        'fob' => [
            'sku' => '30670.04.WH3',
            'name' => 'SpaceControl',
            'description' => 'Key fob',
            'rrpExGst' => 150.0,
            'cat' => 'alarm',
            'icon' => 'fob',
            'colours' => ['white'],
            'alts' => ['fob', 'button'],
            'gallery' => ['fob-01.webp'],
        ],
        'button' => [
            'sku' => '30612.26.WH3',
            'name' => 'Button',
            'description' => 'Panic button',
            'rrpExGst' => 130.0,
            'cat' => 'alarm',
            'icon' => 'fob',
            'colours' => ['white'],
            'alts' => ['fob', 'button'],
            'gallery' => ['button-extra.webp', 'button-03.webp', 'button-02.webp', 'button-01.webp'],
        ],
        'multitx' => [
            'sku' => '30662.62.WH3',
            'name' => 'MultiTransmitter',
            'description' => 'Connects your wired zones to Ajax',
            'rrpExGst' => 660.0,
            'cat' => 'hub',
            'icon' => 'hub',
            'colours' => ['white'],
            'alts' => ['multitx'],
            'gallery' => ['multitx-01.webp'],
        ],
        'doorbell' => [
            'sku' => '116897.125.WH3',
            'name' => 'Ajax Doorbell',
            'description' => 'Video intercom at your door',
            'rrpExGst' => 560.0,
            'cat' => 'intercom',
            'icon' => 'bell',
            'colours' => ['white'],
            'alts' => ['doorbell'],
            'gallery' => ['doorbell-01.webp'],
        ],
        'upgrade' => [
            'sku' => 'AX-WU-K1',
            'name' => 'Wired-to-Wireless Kit (AX-WU-K1)',
            'description' => 'Hub + transmitter + battery — reuses your wiring',
            'rrpExGst' => 1398.0,
            'cat' => 'hub',
            'icon' => 'hub',
            'colours' => ['white'],
            'alts' => ['upgrade'],
            'gallery' => ['upgrade-01.webp'],
        ],
        'cam_5mp' => [
            'sku' => 'CALC-CAM-5MP',
            'name' => '5MP Dome Camera',
            'description' => 'Crisp Full-HD+ — ideal for most homes',
            'rrpExGst' => 360.0,
            'cat' => 'camera',
            'icon' => 'cam',
            'colours' => ['white'],
            'alts' => ['cam_5mp', 'cam_8mp'],
            'gallery' => [],
        ],
        'cam_8mp' => [
            'sku' => 'CALC-CAM-8MP',
            'name' => '8MP Dome Camera (4K)',
            'description' => 'Sharper detail, sees further',
            'rrpExGst' => 430.0,
            'cat' => 'camera',
            'icon' => 'cam',
            'colours' => ['white'],
            'alts' => ['cam_5mp', 'cam_8mp'],
            'gallery' => [],
        ],
        'nvr' => [
            'sku' => 'CALC-NVR',
            'name' => 'NVR (recorder)',
            'description' => 'Records & stores your footage',
            'rrpExGst' => 500.0,
            'cat' => 'camera',
            'icon' => 'nvr',
            'colours' => ['white'],
            'alts' => ['nvr'],
            'gallery' => [],
        ],
    ];

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        return self::$products;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function get(string $key): ?array
    {
        return self::$products[$key] ?? null;
    }

    public static function productId(string $key): string
    {
        return 'calc:' . self::BRAND . ':' . $key;
    }

    public static function priceIncGst(float $rrpExGst): float
    {
        return round($rrpExGst * 1.1, 2);
    }

    /**
     * @param array<int, string> $galleryFiles
     * @return array{thumb: string, gallery: array<int, string>}
     */
    public static function buildMediaUrls(string $mediaBase, string $key, array $galleryFiles): array
    {
        $base = rtrim($mediaBase, '/');
        $gallery = [];
        foreach ($galleryFiles as $file) {
            $gallery[] = $base . '/' . ltrim($file, '/');
        }

        return [
            'thumb'   => $base . '/' . $key . '-thumb.webp',
            'gallery' => $gallery,
        ];
    }
}
