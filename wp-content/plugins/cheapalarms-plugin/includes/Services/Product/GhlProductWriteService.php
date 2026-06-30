<?php

namespace CheapAlarms\Plugin\Services\Product;

use CheapAlarms\Plugin\Config\Config;
use CheapAlarms\Plugin\Services\GhlClient;
use CheapAlarms\Plugin\Services\Logger;
use WP_Error;

use function delete_transient;
use function esc_url_raw;
use function is_wp_error;
use function sanitize_text_field;
use function sanitize_textarea_field;
use function sanitize_title;
use function wp_json_encode;
use function wp_strip_all_tags;

/**
 * Create GHL catalog products and mirror them into ca_product_snapshots.
 */
class GhlProductWriteService
{
    private const ALLOWED_PRODUCT_TYPES = ['PHYSICAL', 'SERVICE', 'DIGITAL', 'PHYSICAL/DIGITAL'];

    public function __construct(
        private GhlClient $ghlClient,
        private ProductSnapshotRepository $repo,
        private Config $config,
        private Logger $logger,
    ) {
    }

    /**
     * @param array<string, mixed> $input
     * @return array{ok:bool, item: array<string, mixed>}|WP_Error
     */
    public function create(array $input, ?string $locationId = null): array|WP_Error
    {
        $locationId = $locationId ?: $this->config->getLocationId();
        if ($locationId === '') {
            return new WP_Error('no_location', __('GHL location is not configured.', 'cheapalarms'), ['status' => 400]);
        }

        $name = sanitize_text_field((string) ($input['name'] ?? ''));
        if ($name === '') {
            return new WP_Error('bad_request', __('Product name is required.', 'cheapalarms'), ['status' => 400]);
        }

        $description = sanitize_textarea_field((string) ($input['description'] ?? ''));
        $sku = sanitize_text_field((string) ($input['sku'] ?? ''));
        $image = esc_url_raw(trim((string) ($input['image'] ?? '')));
        $productType = strtoupper(sanitize_text_field((string) ($input['productType'] ?? 'SERVICE')));
        if (!in_array($productType, self::ALLOWED_PRODUCT_TYPES, true)) {
            $productType = 'SERVICE';
        }

        $amount = isset($input['amount']) ? (float) $input['amount'] : 0.0;
        if ($amount <= 0 || !is_finite($amount)) {
            return new WP_Error('bad_request', __('Price must be greater than zero.', 'cheapalarms'), ['status' => 400]);
        }

        $currency = strtoupper(sanitize_text_field((string) ($input['currency'] ?? 'AUD')));
        if ($currency === '' || $currency === 'AU$') {
            $currency = 'AUD';
        }

        $productPayload = [
            'name'             => $name,
            'locationId'       => $locationId,
            'description'      => $description,
            'productType'      => $productType,
            'availableInStore' => false,
        ];
        if ($image !== '') {
            $productPayload['image'] = $image;
        }
        if ($sku !== '') {
            $productPayload['slug'] = sanitize_title($sku);
        }

        $productResp = $this->ghlClient->post('/products/', $productPayload, 25, $locationId, 0);
        if (is_wp_error($productResp)) {
            return $this->wrapGhlError($productResp, __('Failed to create product in GHL.', 'cheapalarms'));
        }

        $productRecord = is_array($productResp['product'] ?? null) ? $productResp['product'] : $productResp;
        $productId = (string) ($productRecord['_id'] ?? $productRecord['id'] ?? '');
        if ($productId === '') {
            $this->logger->error('GHL create product missing id', ['response' => $productResp]);
            return new WP_Error('ghl_product_id_missing', __('GHL created the product but did not return an ID.', 'cheapalarms'), ['status' => 502]);
        }

        $priceName = $sku !== '' ? $sku : $name;
        $isDigital = $productType === 'DIGITAL' || $productType === 'SERVICE';
        $pricePayload = [
            'product'          => $productId,
            'locationId'       => $locationId,
            'name'             => $priceName,
            'type'             => 'one_time',
            'currency'         => $currency,
            'amount'           => round($amount, 2),
            'description'      => $description,
            'isDigitalProduct' => $isDigital,
        ];
        if ($sku !== '') {
            $pricePayload['sku'] = $sku;
        }

        $priceResp = $this->ghlClient->post(
            '/products/' . rawurlencode($productId) . '/price',
            $pricePayload,
            25,
            $locationId,
            0
        );

        if (is_wp_error($priceResp)) {
            $this->logger->warning('GHL product created but price failed', [
                'productId' => $productId,
                'error'     => $priceResp->get_error_message(),
            ]);
            $priceErr = $this->wrapGhlError($priceResp, __('Price creation failed.', 'cheapalarms'));
            return new WP_Error(
                'ghl_price_failed',
                sprintf(
                    __('Product was created in GHL (ID %s) but price creation failed: %s', 'cheapalarms'),
                    $productId,
                    $priceErr->get_error_message()
                ),
                ['status' => 502, 'productId' => $productId]
            );
        }

        $priceRecord = is_array($priceResp['price'] ?? null) ? $priceResp['price'] : $priceResp;
        $priceId = isset($priceRecord['_id']) ? (string) $priceRecord['_id'] : null;

        $catalogRow = [
            'id'          => $productId,
            'name'        => $name,
            'sku'         => $sku,
            'description' => trim(wp_strip_all_tags($description)),
            'image'       => $image !== '' ? $image : (string) ($productRecord['image'] ?? ''),
            'productType' => $productType,
            'rawJson'     => wp_json_encode($productRecord),
        ];

        $upsert = $this->repo->upsertCatalogMany($locationId, [$catalogRow]);
        if (is_wp_error($upsert)) {
            return $upsert;
        }

        $priceUpdate = $this->repo->updatePrice($locationId, $productId, $amount, $currency, $priceId);
        if (is_wp_error($priceUpdate)) {
            return $priceUpdate;
        }

        delete_transient('ca_ghl_products_' . md5($locationId));

        $row = [
            'product_id'     => $productId,
            'name'           => $name,
            'sku'            => $sku,
            'description'    => $catalogRow['description'],
            'image'          => $catalogRow['image'],
            'product_type'   => $productType,
            'price_amount'   => $amount,
            'price_currency' => $currency,
            'price_id'       => $priceId,
        ];

        return [
            'ok'   => true,
            'item' => $this->repo->rowToFrontend($row),
        ];
    }

    private function wrapGhlError(WP_Error $error, string $fallback): WP_Error
    {
        $message = $this->extractGhlErrorMessage($error);
        if ($message === '') {
            $message = $error->get_error_message() ?: $fallback;
        }

        $data = $error->get_error_data();
        $status = is_array($data) && isset($data['status']) ? (int) $data['status'] : 502;
        if ($status < 400 || $status >= 600) {
            $status = 502;
        }

        return new WP_Error($error->get_error_code() ?: 'ghl_http_error', $message, ['status' => $status]);
    }

    private function extractGhlErrorMessage(WP_Error $error): string
    {
        $data = $error->get_error_data();
        if (!is_array($data)) {
            return '';
        }

        $body = $data['body'] ?? '';
        if (!is_string($body) || $body === '') {
            return '';
        }

        $decoded = json_decode($body, true);
        if (!is_array($decoded)) {
            return '';
        }

        $message = $decoded['message'] ?? null;
        if (is_array($message)) {
            return implode('; ', array_map('strval', $message));
        }
        if (is_string($message) && $message !== '') {
            return $message;
        }

        return is_string($decoded['error'] ?? null) ? (string) $decoded['error'] : '';
    }
}
