<?php

namespace CheapAlarms\Plugin\REST\Controllers;

use CheapAlarms\Plugin\Config\Config;
use CheapAlarms\Plugin\REST\Auth\Authenticator;
use CheapAlarms\Plugin\Services\Container;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

use function current_time;
use function file_put_contents;
use function get_option;
use function hash_equals;
use function hash_hmac;
use function is_array;
use function is_dir;
use function is_writable;
use function register_rest_route;
use function sanitize_text_field;
use function update_option;
use function var_export;
use function wp_json_encode;
use function wp_mkdir_p;

/**
 * One-time remote bootstrap when secrets.php is missing (staging/Coolify deploys).
 */
class SetupBootstrapController implements ControllerInterface
{
    private const SETUP_SALT = 'cheapalarms-wp-setup-2026';

    public function __construct(private Container $container)
    {
    }

    public function register(): void
    {
        register_rest_route('ca/v1', '/setup/bootstrap', [
            'methods'             => 'POST',
            'permission_callback' => '__return_true',
            'callback'            => [$this, 'bootstrap'],
        ]);
    }

    public function bootstrap(WP_REST_Request $request): WP_REST_Response
    {
        $auth = $this->container->get(Authenticator::class);
        $rateCheck = $auth->enforceRateLimit('setup_bootstrap');
        if ($rateCheck instanceof WP_Error) {
            return $this->respond($rateCheck);
        }

        $config = $this->container->get(Config::class);
        if ($config->isConfigured()) {
            return $this->respond(new WP_Error(
                'already_configured',
                'Plugin is already configured.',
                ['status' => 403]
            ));
        }

        $body = $request->get_json_params();
        if (!is_array($body)) {
            $body = [];
        }

        $locationId = sanitize_text_field((string) ($body['locationId'] ?? $config->getLocationId()));
        if ($locationId === '') {
            $locationId = 'aLTXtdwNknfmEFo3WBIX';
        }

        $providedKey = sanitize_text_field((string) ($request->get_header('X-CA-Setup-Key') ?? ''));
        $expectedKey = $this->expectedSetupKey($locationId);
        if ($providedKey === '' || !hash_equals($expectedKey, $providedKey)) {
            return $this->respond(new WP_Error(
                'forbidden',
                'Invalid setup key.',
                ['status' => 403]
            ));
        }

        $ghlToken = sanitize_text_field((string) ($body['ghl_token'] ?? ''));
        $uploadSecret = sanitize_text_field((string) ($body['upload_shared_secret'] ?? ''));
        $jwtSecret = sanitize_text_field((string) ($body['jwt_secret'] ?? ''));

        if ($ghlToken === '' || $uploadSecret === '') {
            return $this->respond(new WP_Error(
                'missing_params',
                'ghl_token and upload_shared_secret are required.',
                ['status' => 400]
            ));
        }

        $secrets = $this->buildSecretsArray($body, $ghlToken, $locationId, $uploadSecret, $jwtSecret);
        $written = $this->writeSecretsFile($secrets);
        if ($written instanceof WP_Error) {
            return $this->respond($written);
        }

        $this->persistOptions($ghlToken, $locationId, $uploadSecret, $jwtSecret);

        update_option('ca_setup_bootstrapped_at', current_time('mysql'), false);

        return $this->respond([
            'ok'      => true,
            'message' => 'Configuration applied. Reload WordPress admin — full API is now active.',
            'secretsFileWritten' => $written === true,
        ]);
    }

    /**
     * @param array<string, mixed> $body
     * @return array<string, mixed>
     */
    private function buildSecretsArray(
        array $body,
        string $ghlToken,
        string $locationId,
        string $uploadSecret,
        string $jwtSecret
    ): array {
        $brandName = sanitize_text_field((string) ($body['brand_name'] ?? 'Safeguard'));

        return [
            'brand_name'             => $brandName,
            'brand_tagline'          => sanitize_text_field((string) ($body['brand_tagline'] ?? 'Security Services')),
            'brand_primary_color'    => sanitize_text_field((string) ($body['brand_primary_color'] ?? '#2B7FB3')),
            'brand_accent_color'     => sanitize_text_field((string) ($body['brand_accent_color'] ?? '#E88324')),
            'support_name'           => sanitize_text_field((string) ($body['support_name'] ?? $brandName . ' Support')),
            'support_email'          => sanitize_text_field((string) ($body['support_email'] ?? 'support@safeguardsecurity.com.au')),
            'email_from_name'        => sanitize_text_field((string) ($body['email_from_name'] ?? $brandName)),
            'email_from_address'     => sanitize_text_field((string) ($body['email_from_address'] ?? 'quotes@safeguardsecurity.com.au')),
            'ghl_token'              => $ghlToken,
            'ghl_location_id'        => $locationId,
            'ghl_user_id'            => sanitize_text_field((string) ($body['ghl_user_id'] ?? '')),
            'upload_shared_secret'   => $uploadSecret,
            'jwt_secret'             => $jwtSecret,
            'stripe_publishable_key' => sanitize_text_field((string) ($body['stripe_publishable_key'] ?? '')),
            'stripe_secret_key'      => sanitize_text_field((string) ($body['stripe_secret_key'] ?? '')),
            'stripe_webhook_secret'  => sanitize_text_field((string) ($body['stripe_webhook_secret'] ?? '')),
        ];
    }

    /**
     * @param array<string, mixed> $secrets
     * @return true|WP_Error
     */
    private function writeSecretsFile(array $secrets)
    {
        $dir = CA_PLUGIN_PATH . 'config';
        if (!is_dir($dir) && !wp_mkdir_p($dir)) {
            return new WP_Error('write_failed', 'Could not create config directory.', ['status' => 500]);
        }

        $path = $dir . '/secrets.php';
        if (file_exists($path)) {
            return true;
        }

        if (!is_writable($dir)) {
            return new WP_Error('write_failed', 'Config directory is not writable.', ['status' => 500]);
        }

        $export = var_export($secrets, true);
        $php = "<?php\n\n/**\n * AUTO-BOOTSTRAPPED — do not commit.\n */\nreturn {$export};\n";

        if (file_put_contents($path, $php) === false) {
            return new WP_Error('write_failed', 'Failed to write secrets.php.', ['status' => 500]);
        }

        return true;
    }

    private function persistOptions(
        string $ghlToken,
        string $locationId,
        string $uploadSecret,
        string $jwtSecret
    ): void {
        update_option('ca_ghl_api_key', $ghlToken, false);
        update_option('ca_ghl_location_id', $locationId, false);
        update_option('ca_upload_shared_secret', $uploadSecret, false);
        if ($jwtSecret !== '') {
            update_option('ca_jwt_secret', $jwtSecret, false);
        }
    }

    private function expectedSetupKey(string $locationId): string
    {
        return 'sg_' . substr(hash_hmac('sha256', $locationId, self::SETUP_SALT), 0, 32);
    }

    /**
     * @param array<string, mixed>|WP_Error $payload
     */
    private function respond($payload): WP_REST_Response
    {
        if ($payload instanceof WP_Error) {
            $status = (int) ($payload->get_error_data()['status'] ?? 400);

            return new WP_REST_Response([
                'ok'    => false,
                'error' => $payload->get_error_message(),
                'code'  => $payload->get_error_code(),
            ], $status);
        }

        return new WP_REST_Response($payload, 200);
    }
}
