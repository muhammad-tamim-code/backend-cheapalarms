<?php

namespace CheapAlarms\Plugin\REST\Controllers;

use CheapAlarms\Plugin\Config\Config;
use CheapAlarms\Plugin\REST\Controllers\Base\AdminController;
use CheapAlarms\Plugin\Services\Container;
use CheapAlarms\Plugin\Services\XeroService;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

use function delete_option;
use function get_option;
use function is_wp_error;
use function sanitize_text_field;
use function update_option;
use function wp_remote_get;
use function wp_remote_retrieve_body;
use function wp_remote_retrieve_response_code;

/**
 * Admin integration settings (GHL credentials in wp_options, status aggregation).
 */
class AdminSettingsController extends AdminController
{
    private const GHL_API_VERSION = '2021-07-28';
    private const GHL_BASE = 'https://services.leadconnectorhq.com';
    private const STRIPE_BASE = 'https://api.stripe.com/v1';

    public const OPTION_GHL_API_KEY      = 'ca_ghl_api_key';
    public const OPTION_GHL_LOCATION_ID  = 'ca_ghl_location_id';
    public const OPTION_GHL_LOCATION_NAME = 'ca_ghl_location_name';

    public const OPTION_STRIPE_PUBLISHABLE_KEY = 'ca_stripe_publishable_key';
    public const OPTION_STRIPE_SECRET_KEY      = 'ca_stripe_secret_key';
    public const OPTION_STRIPE_WEBHOOK_SECRET  = 'ca_stripe_webhook_secret';
    public const OPTION_STRIPE_ACCOUNT_NAME    = 'ca_stripe_account_name';

    private Config $config;
    private XeroService $xeroService;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->config      = $this->container->get(Config::class);
        $this->xeroService = $this->container->get(XeroService::class);
    }

    public function register(): void
    {
        register_rest_route('ca/v1', '/admin/settings/integrations', [
            'methods'             => 'GET',
            'permission_callback' => fn () => true,
            'callback'            => function (WP_REST_Request $request) {
                $err = $this->requireAdmin();
                if ($err) {
                    return $err;
                }

                return $this->getIntegrations($request);
            },
        ]);

        register_rest_route('ca/v1', '/admin/settings/integrations/ghl', [
            [
                'methods'             => 'POST',
                'permission_callback' => fn () => true,
                'callback'            => function (WP_REST_Request $request) {
                    $err = $this->requireAdmin();
                    if ($err) {
                        return $err;
                    }

                    return $this->saveGhl($request);
                },
            ],
            [
                'methods'             => 'DELETE',
                'permission_callback' => fn () => true,
                'callback'            => function (WP_REST_Request $request) {
                    $err = $this->requireAdmin();
                    if ($err) {
                        return $err;
                    }

                    return $this->deleteGhl($request);
                },
            ],
        ]);

        register_rest_route('ca/v1', '/admin/settings/integrations/ghl/test', [
            'methods'             => 'POST',
            'permission_callback' => fn () => true,
            'callback'            => function (WP_REST_Request $request) {
                $err = $this->requireAdmin();
                if ($err) {
                    return $err;
                }

                return $this->testGhl($request);
            },
        ]);

        register_rest_route('ca/v1', '/admin/settings/integrations/stripe', [
            [
                'methods'             => 'POST',
                'permission_callback' => fn () => true,
                'callback'            => function (WP_REST_Request $request) {
                    $err = $this->requireAdmin();
                    if ($err) {
                        return $err;
                    }

                    return $this->saveStripe($request);
                },
            ],
            [
                'methods'             => 'DELETE',
                'permission_callback' => fn () => true,
                'callback'            => function (WP_REST_Request $request) {
                    $err = $this->requireAdmin();
                    if ($err) {
                        return $err;
                    }

                    return $this->deleteStripe($request);
                },
            ],
        ]);

        register_rest_route('ca/v1', '/admin/settings/integrations/stripe/test', [
            'methods'             => 'POST',
            'permission_callback' => fn () => true,
            'callback'            => function (WP_REST_Request $request) {
                $err = $this->requireAdmin();
                if ($err) {
                    return $err;
                }

                return $this->testStripe($request);
            },
        ]);
    }

    private function getIntegrations(WP_REST_Request $request): WP_REST_Response
    {
        $token   = $this->config->getGhlToken();
        $locId   = $this->config->getLocationId();
        $ghlOk   = $token !== '' && $locId !== '';

        $xeroConnected = $this->xeroService->isConnected();
        $xeroTenantId  = $xeroConnected ? get_option('ca_xero_tenant_id') : null;

        $stripeSecret      = $this->config->getStripeSecretKey();
        $stripePublishable = $this->config->getStripePublishableKey();
        $stripeWebhook     = $this->config->getStripeWebhookSecret();
        $stripeOk          = $stripeSecret !== '';
        $sm8Ok             = $this->config->getServiceM8ApiKey() !== '';

        return $this->respond([
            'ok' => true,
            'ghl' => [
                'connected'      => $ghlOk,
                'token_source'   => $this->config->getGhlTokenSource(),
                'location_source' => $this->config->getGhlLocationSource(),
                'location_id'    => $locId !== '' ? $locId : null,
                'location_name'  => (string) get_option(self::OPTION_GHL_LOCATION_NAME, ''),
            ],
            'xero' => [
                'connected'  => $xeroConnected,
                'tenant_id'  => $xeroTenantId ?: null,
            ],
            'stripe' => [
                'connected'                  => $stripeOk,
                'mode'                       => $this->detectStripeMode($stripeSecret),
                'secret_key_source'          => $this->config->getStripeSecretKeySource(),
                'publishable_key_source'     => $this->config->getStripePublishableKeySource(),
                'webhook_secret_source'      => $this->config->getStripeWebhookSecretSource(),
                'publishable_key'            => $stripePublishable !== '' ? $stripePublishable : null,
                'has_webhook_secret'         => $stripeWebhook !== '',
                'account_name'               => (string) get_option(self::OPTION_STRIPE_ACCOUNT_NAME, ''),
            ],
            'servicem8' => [
                'connected' => $sm8Ok,
                'source'    => $sm8Ok ? 'env_or_file' : 'none',
            ],
        ], $request);
    }

    private function detectStripeMode(string $secretKey): ?string
    {
        if ($secretKey === '') {
            return null;
        }
        if (str_starts_with($secretKey, 'sk_live_') || str_starts_with($secretKey, 'rk_live_')) {
            return 'live';
        }
        if (str_starts_with($secretKey, 'sk_test_') || str_starts_with($secretKey, 'rk_test_')) {
            return 'test';
        }

        return 'unknown';
    }

    private function testGhl(WP_REST_Request $request): WP_REST_Response
    {
        $body   = $request->get_json_params();
        $apiKey = sanitize_text_field($body['api_key'] ?? '');
        $locId  = sanitize_text_field($body['location_id'] ?? '');

        if ($apiKey === '' || $locId === '') {
            return $this->respond(new WP_Error(
                'missing_params',
                __('API key and Location ID are required.', 'cheapalarms'),
                ['status' => 400]
            ));
        }

        $test = $this->callGhlLocation($apiKey, $locId);
        if (is_wp_error($test)) {
            return $this->respond($test);
        }

        return $this->respond([
            'ok'            => true,
            'valid'         => true,
            'location_name' => $test['location_name'],
        ], $request);
    }

    private function saveGhl(WP_REST_Request $request): WP_REST_Response
    {
        $body   = $request->get_json_params();
        $apiKey = sanitize_text_field($body['api_key'] ?? '');
        $locId  = sanitize_text_field($body['location_id'] ?? '');

        if ($apiKey === '' || $locId === '') {
            return $this->respond(new WP_Error(
                'missing_params',
                __('API key and Location ID are required.', 'cheapalarms'),
                ['status' => 400]
            ));
        }

        $test = $this->callGhlLocation($apiKey, $locId);
        if (is_wp_error($test)) {
            return $this->respond($test);
        }

        update_option(self::OPTION_GHL_API_KEY, $apiKey, false);
        update_option(self::OPTION_GHL_LOCATION_ID, $locId, false);
        update_option(self::OPTION_GHL_LOCATION_NAME, $test['location_name'], false);

        return $this->respond([
            'ok'            => true,
            'status'        => 'connected',
            'location_name' => $test['location_name'],
        ], $request);
    }

    private function deleteGhl(WP_REST_Request $request): WP_REST_Response
    {
        delete_option(self::OPTION_GHL_API_KEY);
        delete_option(self::OPTION_GHL_LOCATION_ID);
        delete_option(self::OPTION_GHL_LOCATION_NAME);

        return $this->respond([
            'ok'      => true,
            'message' => __('GoHighLevel credentials cleared from WordPress.', 'cheapalarms'),
        ], $request);
    }

    /**
     * @return array{location_name: string}|WP_Error
     */
    private function callGhlLocation(string $apiKey, string $locationId): array|WP_Error
    {
        $url = self::GHL_BASE . '/locations/' . rawurlencode($locationId);
        $res = wp_remote_get($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Accept'        => 'application/json',
                'Version'       => self::GHL_API_VERSION,
                'LocationId'    => $locationId,
            ],
            'timeout' => 15,
            'sslverify' => true,
        ]);

        if (is_wp_error($res)) {
            return new WP_Error(
                'ghl_request_failed',
                $res->get_error_message(),
                ['status' => 502]
            );
        }

        $code = wp_remote_retrieve_response_code($res);
        $raw  = wp_remote_retrieve_body($res);
        if ($code < 200 || $code >= 300) {
            return new WP_Error(
                'ghl_http_error',
                sprintf(__('GoHighLevel returned HTTP %d', 'cheapalarms'), $code),
                ['status' => 400, 'body' => $raw]
            );
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return new WP_Error('ghl_invalid_json', __('Invalid response from GoHighLevel.', 'cheapalarms'), ['status' => 502]);
        }

        $loc = $decoded['location'] ?? $decoded;
        $name = '';
        if (is_array($loc)) {
            $name = sanitize_text_field($loc['name'] ?? $loc['companyName'] ?? '');
        }

        return ['location_name' => $name];
    }

    private function testStripe(WP_REST_Request $request): WP_REST_Response
    {
        $body            = $request->get_json_params();
        $secretKey       = isset($body['secret_key']) ? trim((string) $body['secret_key']) : '';
        $publishableKey  = isset($body['publishable_key']) ? trim((string) $body['publishable_key']) : '';

        if ($secretKey === '') {
            return $this->respond(new WP_Error(
                'missing_params',
                __('Stripe secret key is required.', 'cheapalarms'),
                ['status' => 400]
            ));
        }

        if (!$this->looksLikeStripeSecret($secretKey)) {
            return $this->respond(new WP_Error(
                'invalid_stripe_key',
                __('Secret key should start with sk_live_, sk_test_, rk_live_, or rk_test_.', 'cheapalarms'),
                ['status' => 400]
            ));
        }

        if ($publishableKey !== '' && !$this->looksLikeStripePublishable($publishableKey)) {
            return $this->respond(new WP_Error(
                'invalid_stripe_key',
                __('Publishable key should start with pk_live_ or pk_test_.', 'cheapalarms'),
                ['status' => 400]
            ));
        }

        $info = $this->callStripeAccount($secretKey);
        if (is_wp_error($info)) {
            return $this->respond($info);
        }

        return $this->respond([
            'ok'           => true,
            'valid'        => true,
            'account_name' => $info['account_name'],
            'account_id'   => $info['account_id'],
            'mode'         => $this->detectStripeMode($secretKey),
        ], $request);
    }

    private function saveStripe(WP_REST_Request $request): WP_REST_Response
    {
        $body            = $request->get_json_params();
        $secretKey       = isset($body['secret_key']) ? trim((string) $body['secret_key']) : '';
        $publishableKey  = isset($body['publishable_key']) ? trim((string) $body['publishable_key']) : '';
        $webhookSecret   = isset($body['webhook_secret']) ? trim((string) $body['webhook_secret']) : '';

        if ($secretKey === '') {
            return $this->respond(new WP_Error(
                'missing_params',
                __('Stripe secret key is required.', 'cheapalarms'),
                ['status' => 400]
            ));
        }

        if (!$this->looksLikeStripeSecret($secretKey)) {
            return $this->respond(new WP_Error(
                'invalid_stripe_key',
                __('Secret key should start with sk_live_, sk_test_, rk_live_, or rk_test_.', 'cheapalarms'),
                ['status' => 400]
            ));
        }

        if ($publishableKey !== '' && !$this->looksLikeStripePublishable($publishableKey)) {
            return $this->respond(new WP_Error(
                'invalid_stripe_key',
                __('Publishable key should start with pk_live_ or pk_test_.', 'cheapalarms'),
                ['status' => 400]
            ));
        }

        if ($webhookSecret !== '' && !str_starts_with($webhookSecret, 'whsec_')) {
            return $this->respond(new WP_Error(
                'invalid_stripe_key',
                __('Webhook secret should start with whsec_.', 'cheapalarms'),
                ['status' => 400]
            ));
        }

        $info = $this->callStripeAccount($secretKey);
        if (is_wp_error($info)) {
            return $this->respond($info);
        }

        update_option(self::OPTION_STRIPE_SECRET_KEY, $secretKey, false);
        update_option(self::OPTION_STRIPE_PUBLISHABLE_KEY, $publishableKey, false);
        if ($webhookSecret !== '') {
            update_option(self::OPTION_STRIPE_WEBHOOK_SECRET, $webhookSecret, false);
        }
        update_option(self::OPTION_STRIPE_ACCOUNT_NAME, $info['account_name'], false);

        return $this->respond([
            'ok'           => true,
            'status'       => 'connected',
            'account_name' => $info['account_name'],
            'account_id'   => $info['account_id'],
            'mode'         => $this->detectStripeMode($secretKey),
        ], $request);
    }

    private function deleteStripe(WP_REST_Request $request): WP_REST_Response
    {
        delete_option(self::OPTION_STRIPE_SECRET_KEY);
        delete_option(self::OPTION_STRIPE_PUBLISHABLE_KEY);
        delete_option(self::OPTION_STRIPE_WEBHOOK_SECRET);
        delete_option(self::OPTION_STRIPE_ACCOUNT_NAME);

        return $this->respond([
            'ok'      => true,
            'message' => __('Stripe credentials cleared from WordPress.', 'cheapalarms'),
        ], $request);
    }

    private function looksLikeStripeSecret(string $key): bool
    {
        return str_starts_with($key, 'sk_live_')
            || str_starts_with($key, 'sk_test_')
            || str_starts_with($key, 'rk_live_')
            || str_starts_with($key, 'rk_test_');
    }

    private function looksLikeStripePublishable(string $key): bool
    {
        return str_starts_with($key, 'pk_live_') || str_starts_with($key, 'pk_test_');
    }

    /**
     * Validate the Stripe secret key by calling /v1/account.
     *
     * @return array{account_id: string, account_name: string}|WP_Error
     */
    private function callStripeAccount(string $secretKey): array|WP_Error
    {
        $res = wp_remote_get(self::STRIPE_BASE . '/account', [
            'headers' => [
                'Authorization' => 'Bearer ' . $secretKey,
                'Accept'        => 'application/json',
            ],
            'timeout'   => 15,
            'sslverify' => true,
        ]);

        if (is_wp_error($res)) {
            return new WP_Error(
                'stripe_request_failed',
                $res->get_error_message(),
                ['status' => 502]
            );
        }

        $code = wp_remote_retrieve_response_code($res);
        $raw  = wp_remote_retrieve_body($res);
        if ($code < 200 || $code >= 300) {
            $decoded = json_decode($raw, true);
            $stripeMessage = '';
            if (is_array($decoded)) {
                $stripeMessage = (string) ($decoded['error']['message'] ?? '');
            }

            return new WP_Error(
                'stripe_http_error',
                $stripeMessage !== ''
                    ? sprintf(__('Stripe rejected the key: %s', 'cheapalarms'), $stripeMessage)
                    : sprintf(__('Stripe returned HTTP %d', 'cheapalarms'), $code),
                ['status' => 400, 'body' => $raw]
            );
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return new WP_Error('stripe_invalid_json', __('Invalid response from Stripe.', 'cheapalarms'), ['status' => 502]);
        }

        $accountId   = sanitize_text_field((string) ($decoded['id'] ?? ''));
        $accountName = sanitize_text_field((string) (
            $decoded['business_profile']['name']
                ?? $decoded['settings']['dashboard']['display_name']
                ?? $decoded['email']
                ?? ''
        ));

        return [
            'account_id'   => $accountId,
            'account_name' => $accountName,
        ];
    }
}
