<?php

namespace CheapAlarms\Plugin\Config;

use function sanitize_text_field;
use function wp_salt;
use function get_option;

class Config
{
    private array $defaults = [
        'ghl_token'              => '',
        'ghl_location_id'        => '',
        'servicem8_api_key'      => '',
        'upload_shared_secret'   => '',
        'upload_max_mb'          => 10,
        'upload_allowed_origins' => [],
        'api_allowed_origins'    => [],
        'jwt_secret'             => '',
        'jwt_ttl_seconds'          => 3600,
        'jwt_remember_ttl_seconds' => 2592000, // 30 days when "Remember me" is checked
        'xero_client_id'         => '',
        'xero_client_secret'     => '',
        'xero_redirect_uri'      => '',
        'xero_sales_account_code' => '200',
        'xero_bank_account_code' => '090',
        'stripe_publishable_key' => '',
        'stripe_secret_key'      => '',
        'stripe_webhook_secret'  => '',
        'brand_name'             => 'CheapAlarms',
        'brand_tagline'          => 'Your Security Partner',
        'brand_primary_color'    => '#171717',
        'brand_accent_color'     => '#1EA6DF',
        'brand_logo_horizontal'  => '',
        'brand_logo_mark'        => '',
        'support_name'           => '',
        'support_email'          => '',
        'email_from_name'        => '',
        'email_from_address'     => '',
    ];

    private array $overrides = [];

    public function __construct()
    {
        $secretsFile = CA_PLUGIN_PATH . 'config/secrets.php';
        if (file_exists($secretsFile)) {
            $data = include $secretsFile;
            if (is_array($data)) {
                $this->overrides = $data;
            }
        }

        $instanceFile = CA_PLUGIN_PATH . 'config/instance.php';
        if (file_exists($instanceFile)) {
            $data = include $instanceFile;
            if (is_array($data)) {
                // instance.php is the per-business source of truth and wins over secrets.php
                $this->overrides = array_merge($this->overrides, $data);
            }
        }
    }

    public function getBrandName(): string
    {
        $override = $this->fromOverrides('brand_name');
        if (is_string($override) && trim($override) !== '') {
            return trim($override);
        }

        $env = (string) $this->getEnv('CA_BRAND_NAME', '');
        if (trim($env) !== '') {
            return trim($env);
        }

        return (string) $this->defaults['brand_name'];
    }

    public function getBrandTagline(): string
    {
        $override = $this->fromOverrides('brand_tagline');
        if (is_string($override) && trim($override) !== '') {
            return trim($override);
        }

        $env = (string) $this->getEnv('CA_BRAND_TAGLINE', '');
        if (trim($env) !== '') {
            return trim($env);
        }

        return (string) $this->defaults['brand_tagline'];
    }

    public function getBrandPrimaryColor(): string
    {
        $override = $this->fromOverrides('brand_primary_color');
        if (is_string($override) && trim($override) !== '') {
            return trim($override);
        }

        $env = (string) $this->getEnv('CA_BRAND_PRIMARY_COLOR', '');
        if (trim($env) !== '') {
            return trim($env);
        }

        return (string) $this->defaults['brand_primary_color'];
    }

    public function getBrandAccentColor(): string
    {
        $override = $this->fromOverrides('brand_accent_color');
        if (is_string($override) && trim($override) !== '') {
            return trim($override);
        }

        $env = (string) $this->getEnv('CA_BRAND_ACCENT_COLOR', '');
        if (trim($env) !== '') {
            return trim($env);
        }

        return (string) $this->defaults['brand_accent_color'];
    }

    public function getBrandLogoHorizontalUrl(): string
    {
        $override = $this->fromOverrides('brand_logo_horizontal');
        if (is_string($override) && trim($override) !== '') {
            return trim($override);
        }

        $env = (string) $this->getEnv('CA_BRAND_LOGO_HORIZONTAL', '');
        if (trim($env) !== '') {
            return trim($env);
        }

        return rtrim($this->getFrontendUrl(), '/') . '/brand/logo-horizontal.png';
    }

    public function getBrandLogoMarkUrl(): string
    {
        $override = $this->fromOverrides('brand_logo_mark');
        if (is_string($override) && trim($override) !== '') {
            return trim($override);
        }

        $env = (string) $this->getEnv('CA_BRAND_LOGO_MARK', '');
        if (trim($env) !== '') {
            return trim($env);
        }

        return rtrim($this->getFrontendUrl(), '/') . '/brand/logo-mark.png';
    }

    public function getSupportName(): string
    {
        $fromOption = get_option('ca_support_name', '');
        if (is_string($fromOption) && trim($fromOption) !== '') {
            return sanitize_text_field($fromOption);
        }

        $override = $this->fromOverrides('support_name');
        if (is_string($override) && trim($override) !== '') {
            return trim($override);
        }

        $env = (string) $this->getEnv('CA_SUPPORT_NAME', '');
        if (trim($env) !== '') {
            return trim($env);
        }

        return $this->getBrandName();
    }

    public function getSupportEmail(): string
    {
        $override = $this->fromOverrides('support_email');
        if (is_string($override) && trim($override) !== '') {
            return sanitize_text_field($override);
        }

        $env = (string) $this->getEnv('CA_SUPPORT_EMAIL', '');
        if (trim($env) !== '') {
            return sanitize_text_field($env);
        }

        return '';
    }

    public function getEmailFromAddress(): string
    {
        $override = $this->fromOverrides('email_from_address');
        if (is_string($override) && trim($override) !== '') {
            return sanitize_text_field($override);
        }

        $env = (string) $this->getEnv('CA_EMAIL_FROM_ADDRESS', '');
        if (trim($env) !== '') {
            return sanitize_text_field($env);
        }

        $fromOption = get_option('ghl_from_email', '');
        if (is_string($fromOption) && trim($fromOption) !== '') {
            return sanitize_text_field($fromOption);
        }

        $support = $this->getSupportEmail();
        if ($support !== '') {
            return $support;
        }

        return 'noreply@example.com';
    }

    public function getEmailFromName(): string
    {
        $override = $this->fromOverrides('email_from_name');
        if (is_string($override) && trim($override) !== '') {
            return trim($override);
        }

        $env = (string) $this->getEnv('CA_EMAIL_FROM_NAME', '');
        if (trim($env) !== '') {
            return trim($env);
        }

        return $this->getBrandName();
    }

    public function getEmailFromHeader(): string
    {
        return $this->getEmailFromName() . ' <' . $this->getEmailFromAddress() . '>';
    }

    public function getGhlToken(): string
    {
        $fromDb = get_option('ca_ghl_api_key', '');
        if (is_string($fromDb) && $fromDb !== '') {
            return sanitize_text_field($fromDb);
        }

        return $this->fromOverrides('ghl_token') ?: $this->getEnv('CA_GHL_TOKEN', $this->defaults['ghl_token']);
    }

    public function getLocationId(): string
    {
        $fromDb = get_option('ca_ghl_location_id', '');
        if (is_string($fromDb) && $fromDb !== '') {
            return sanitize_text_field($fromDb);
        }

        return $this->fromOverrides('ghl_location_id') ?: $this->getEnv('CA_LOCATION_ID', $this->defaults['ghl_location_id']);
    }

    /**
     * Where the active GHL API token was loaded from (for admin diagnostics).
     */
    public function getGhlTokenSource(): string
    {
        $fromDb = get_option('ca_ghl_api_key', '');
        if (is_string($fromDb) && $fromDb !== '') {
            return 'database';
        }
        $file = $this->fromOverrides('ghl_token');
        if (is_string($file) && $file !== '') {
            return 'file';
        }
        $env = (string) $this->getEnv('CA_GHL_TOKEN', '');

        return $env !== '' ? 'env' : 'none';
    }

    /**
     * Where the active GHL location id was loaded from (for admin diagnostics).
     */
    public function getGhlLocationSource(): string
    {
        $fromDb = get_option('ca_ghl_location_id', '');
        if (is_string($fromDb) && $fromDb !== '') {
            return 'database';
        }
        $file = $this->fromOverrides('ghl_location_id');
        if (is_string($file) && $file !== '') {
            return 'file';
        }
        $env = (string) $this->getEnv('CA_LOCATION_ID', '');

        return $env !== '' ? 'env' : 'none';
    }

    public function getUploadSharedSecret(): string
    {
        $fromDb = get_option('ca_upload_shared_secret', '');
        if (is_string($fromDb) && $fromDb !== '') {
            return sanitize_text_field($fromDb);
        }

        return $this->fromOverrides('upload_shared_secret') ?: $this->getEnv('CA_UPLOAD_SHARED_SECRET', $this->defaults['upload_shared_secret']);
    }

    public function getUploadMaxBytes(): int
    {
        $value = $this->fromOverrides('upload_max_mb');
        if ($value !== null) {
            $mb = (int) $value;
        } else {
            $mb = (int) $this->getEnv('CA_UPLOAD_MAX_MB', $this->defaults['upload_max_mb']);
        }
        return $mb * 1024 * 1024;
    }

    /**
     * @return string[]
     */
    public function getAllowedOrigins(): array
    {
        $override = $this->fromOverrides('upload_allowed_origins');
        if (is_array($override) && !empty($override)) {
            return $this->sanitizeOrigins($this->mergeCoreOrigins($override));
        }

        $value = $this->getEnv('CA_UPLOAD_ALLOWED_ORIGINS', '');
        if (is_array($value)) {
            return $this->sanitizeOrigins($this->mergeCoreOrigins($value));
        }
        if (is_string($value) && $value !== '') {
            return $this->sanitizeOrigins($this->mergeCoreOrigins(array_map('trim', explode(',', $value))));
        }
        return $this->sanitizeOrigins($this->getCoreOrigins());
    }

    /**
     * @return string[]
     */
    public function getApiAllowedOrigins(): array
    {
        $override = $this->fromOverrides('api_allowed_origins');
        if (is_array($override) && !empty($override)) {
            return $this->sanitizeOrigins($this->mergeCoreOrigins($override));
        }

        $value = $this->getEnv('CA_API_ALLOWED_ORIGINS', '');
        if (is_array($value)) {
            return $this->sanitizeOrigins($this->mergeCoreOrigins($value));
        }
        if (is_string($value) && $value !== '') {
            return $this->sanitizeOrigins($this->mergeCoreOrigins(array_map('trim', explode(',', $value))));
        }
        return $this->sanitizeOrigins($this->getCoreOrigins());
    }

    public function getJwtSecret(): string
    {
        $override = $this->fromOverrides('jwt_secret');
        if (is_string($override) && $override !== '') {
            return $override;
        }

        $fromDb = get_option('ca_jwt_secret', '');
        if (is_string($fromDb) && $fromDb !== '') {
            return sanitize_text_field($fromDb);
        }

        $env = (string) $this->getEnv('CA_JWT_SECRET', '');
        if ($env !== '') {
            return $env;
        }

        if (defined('AUTH_KEY') && AUTH_KEY !== '') {
            return AUTH_KEY;
        }

        if (defined('SECURE_AUTH_KEY') && SECURE_AUTH_KEY !== '') {
            return SECURE_AUTH_KEY;
        }

        return hash('sha256', wp_salt('auth'));
    }

    public function getJwtTtlSeconds(): int
    {
        $override = $this->fromOverrides('jwt_ttl_seconds');
        if ($override !== null) {
            return max(60, (int) $override);
        }

        $env = (int) $this->getEnv('CA_JWT_TTL_SECONDS', $this->defaults['jwt_ttl_seconds']);
        return max(60, $env);
    }

    public function getJwtRememberTtlSeconds(): int
    {
        $override = $this->fromOverrides('jwt_remember_ttl_seconds');
        if ($override !== null) {
            return max(3600, (int) $override);
        }

        $env = (int) $this->getEnv(
            'CA_JWT_REMEMBER_TTL_SECONDS',
            $this->defaults['jwt_remember_ttl_seconds']
        );
        return max(3600, $env);
    }

    /**
     * @return string[]
     */
    public function getUploadAllowedOrigins(): array
    {
        return $this->getAllowedOrigins();
    }

    public function getServiceM8ApiKey(): string
    {
        return $this->fromOverrides('servicem8_api_key') ?: $this->getEnv('CA_SERVICEM8_API_KEY', $this->defaults['servicem8_api_key']);
    }

    public function getFrontendUrl(): string
    {
        // Canonical resolution order: instance.php / secrets.php → env → default.
        // Edit cheapalarms.instance.json at repo root, then run scripts/sync-instance-config.mjs.
        $url = $this->fromOverrides('frontend_url')
            ?: $this->getEnv('CA_FRONTEND_URL', 'https://headless-cheapalarms.vercel.app');

        return rtrim((string) $url, '/');
    }

    public function isConfigured(): bool
    {
        return $this->getGhlToken() !== '' && $this->getLocationId() !== '' && $this->getUploadSharedSecret() !== '';
    }

    public function getUserId(): ?string
    {
        // Check secrets.php first, then environment variable, then WordPress option
        $secrets = $this->fromOverrides('ghl_user_id');
        if ($secrets !== null && $secrets !== '') {
            return sanitize_text_field($secrets);
        }
        
        if (defined('CA_GHL_USER_ID')) {
            $userId = constant('CA_GHL_USER_ID');
            if ($userId) {
                return sanitize_text_field((string) $userId);
            }
        }
        
        $option = get_option('ca_ghl_user_id', null);
        if ($option) {
            return sanitize_text_field($option);
        }
        
        return null;
    }

    public function getXeroClientId(): string
    {
        return $this->fromOverrides('xero_client_id') ?: $this->getEnv('CA_XERO_CLIENT_ID', $this->defaults['xero_client_id']);
    }

    public function getXeroClientSecret(): string
    {
        return $this->fromOverrides('xero_client_secret') ?: $this->getEnv('CA_XERO_CLIENT_SECRET', $this->defaults['xero_client_secret']);
    }

    public function getXeroRedirectUri(): string
    {
        $override = $this->fromOverrides('xero_redirect_uri');
        if (is_string($override) && trim($override) !== '') {
            return trim($override);
        }

        $env = (string) $this->getEnv('CA_XERO_REDIRECT_URI', '');
        if (trim($env) !== '') {
            return trim($env);
        }

        return $this->getFrontendUrl() . '/xero/callback';
    }

    public function getXeroSalesAccountCode(): string
    {
        return $this->fromOverrides('xero_sales_account_code') ?: $this->getEnv('CA_XERO_SALES_ACCOUNT_CODE', $this->defaults['xero_sales_account_code']);
    }

    public function getXeroBankAccountCode(): string
    {
        return $this->fromOverrides('xero_bank_account_code') ?: $this->getEnv('CA_XERO_BANK_ACCOUNT_CODE', $this->defaults['xero_bank_account_code']);
    }

    public function getStripePublishableKey(): string
    {
        $fromDb = get_option('ca_stripe_publishable_key', '');
        if (is_string($fromDb) && $fromDb !== '') {
            return sanitize_text_field($fromDb);
        }

        return $this->fromOverrides('stripe_publishable_key') ?: $this->getEnv('CA_STRIPE_PUBLISHABLE_KEY', $this->defaults['stripe_publishable_key']);
    }

    public function getStripeSecretKey(): string
    {
        $fromDb = get_option('ca_stripe_secret_key', '');
        if (is_string($fromDb) && $fromDb !== '') {
            return sanitize_text_field($fromDb);
        }

        return $this->fromOverrides('stripe_secret_key') ?: $this->getEnv('CA_STRIPE_SECRET_KEY', $this->defaults['stripe_secret_key']);
    }

    public function getStripeWebhookSecret(): string
    {
        $fromDb = get_option('ca_stripe_webhook_secret', '');
        if (is_string($fromDb) && $fromDb !== '') {
            return sanitize_text_field($fromDb);
        }

        return $this->fromOverrides('stripe_webhook_secret') ?: $this->getEnv('CA_STRIPE_WEBHOOK_SECRET', $this->defaults['stripe_webhook_secret']);
    }

    /**
     * Where the active Stripe secret key was loaded from (for admin diagnostics).
     */
    public function getStripeSecretKeySource(): string
    {
        $fromDb = get_option('ca_stripe_secret_key', '');
        if (is_string($fromDb) && $fromDb !== '') {
            return 'database';
        }
        $file = $this->fromOverrides('stripe_secret_key');
        if (is_string($file) && $file !== '') {
            return 'file';
        }
        $env = (string) $this->getEnv('CA_STRIPE_SECRET_KEY', '');

        return $env !== '' ? 'env' : 'none';
    }

    /**
     * Where the active Stripe publishable key was loaded from (for admin diagnostics).
     */
    public function getStripePublishableKeySource(): string
    {
        $fromDb = get_option('ca_stripe_publishable_key', '');
        if (is_string($fromDb) && $fromDb !== '') {
            return 'database';
        }
        $file = $this->fromOverrides('stripe_publishable_key');
        if (is_string($file) && $file !== '') {
            return 'file';
        }
        $env = (string) $this->getEnv('CA_STRIPE_PUBLISHABLE_KEY', '');

        return $env !== '' ? 'env' : 'none';
    }

    /**
     * Where the active Stripe webhook secret was loaded from (for admin diagnostics).
     */
    public function getStripeWebhookSecretSource(): string
    {
        $fromDb = get_option('ca_stripe_webhook_secret', '');
        if (is_string($fromDb) && $fromDb !== '') {
            return 'database';
        }
        $file = $this->fromOverrides('stripe_webhook_secret');
        if (is_string($file) && $file !== '') {
            return 'file';
        }
        $env = (string) $this->getEnv('CA_STRIPE_WEBHOOK_SECRET', '');

        return $env !== '' ? 'env' : 'none';
    }

    private function getEnv(string $key, $default = '')
    {
        if (defined($key)) {
            return constant($key);
        }

        $mapped = getenv($key);
        if ($mapped !== false) {
            return $mapped;
        }

        return $default;
    }

    private function fromOverrides(string $key)
    {
        return $this->overrides[$key] ?? null;
    }

    /**
     * WordPress site + Next.js portal — always allowed for CORS when lists are built.
     *
     * @return string[]
     */
    private function getCoreOrigins(): array
    {
        $origins = [];
        $site = rtrim((string) site_url(), '/');
        if ($site !== '') {
            $origins[] = $site;
        }

        $frontend = $this->getFrontendUrl();
        if ($frontend !== '') {
            $origins[] = $frontend;
        }

        if (defined('WP_DEBUG') && WP_DEBUG) {
            $origins = array_merge($origins, [
                'http://localhost',
                'http://localhost:3000',
                'http://localhost:5173',
                'http://127.0.0.1:5173',
            ]);
        }

        return $origins;
    }

    /**
     * @param string[] $origins
     * @return string[]
     */
    private function mergeCoreOrigins(array $origins): array
    {
        return array_values(array_unique(array_merge($origins, $this->getCoreOrigins())));
    }

    /**
     * Sanitize and normalize allowed origins:
     * - Trim entries
     * - Remove empties and duplicates
     * - Drop localhost/loopback in production unless explicitly allowed
     */
    private function sanitizeOrigins(array $origins): array
    {
        $clean = [];
        foreach ($origins as $origin) {
            if (!is_string($origin)) {
                continue;
            }
            $o = trim($origin);
            if ($o === '') {
                continue;
            }
            $clean[] = $o;
        }

        $clean = array_values(array_unique($clean));

        // SECURITY: Only allow localhost in development (WP_DEBUG mode)
        // This prevents localhost from being allowed in production
        $isDebug = defined('WP_DEBUG') && WP_DEBUG;
        $allowLocalhost = $this->getEnv('CA_ALLOW_LOCALHOST_CORS', false);
        
        // In production (WP_DEBUG = false), remove localhost unless explicitly allowed
        if (!$isDebug && !$allowLocalhost) {
            $clean = array_values(array_filter($clean, function ($o) {
                $oLower = strtolower($o);
                return !str_contains($oLower, 'localhost') 
                    && !str_contains($oLower, '127.0.0.1')
                    && !str_contains($oLower, '::1');
            }));
        }

        return $clean;
    }

    /**
     * When true, {@see \CheapAlarms\Plugin\Services\PortalService::createInvoiceForEstimate}
     * creates the receivable in Xero from estimate data and skips GHL invoice creation.
     *
     * Precedence: secrets.php `xero_direct_invoicing`, env CA_XERO_DIRECT_INVOICING, then option ca_xero_direct_invoicing.
     */
    public function isXeroDirectInvoicingEnabled(): bool
    {
        $override = $this->fromOverrides('xero_direct_invoicing');
        if ($override !== null && $override !== '') {
            if (is_bool($override)) {
                return $override;
            }
            $s = strtolower(trim((string) $override));

            return in_array($s, ['1', 'true', 'yes', 'on'], true);
        }

        $env = strtolower(trim((string) $this->getEnv('CA_XERO_DIRECT_INVOICING', '')));
        if ($env !== '' && !in_array($env, ['0', 'false', 'no', 'off'], true)) {
            return in_array($env, ['1', 'true', 'yes', 'on'], true);
        }

        $opt = get_option('ca_xero_direct_invoicing', '0');

        return $opt === '1' || $opt === 1 || $opt === true;
    }

    /**
     * When false (default), {@see \CheapAlarms\Plugin\Services\GhlClient::get} does not call GoHighLevel.
     * WordPress remains the source of truth; snapshots update from outbound writes only.
     *
     * Enable reads via: option `ca_ghl_fetch_allowed` = 1, env `CA_GHL_FETCH_ALLOWED`=true,
     * secrets.php `ghl_fetch_allowed`, or filter `cheapalarms_ghl_fetch_allowed`.
     */
    public function isGhlFetchAllowed(): bool
    {
        $override = $this->fromOverrides('ghl_fetch_allowed');
        if ($override !== null && $override !== '') {
            if (is_bool($override)) {
                return $override;
            }
            $s = strtolower(trim((string) $override));

            return in_array($s, ['1', 'true', 'yes', 'on'], true);
        }

        $env = strtolower(trim((string) $this->getEnv('CA_GHL_FETCH_ALLOWED', '')));
        if ($env !== '') {
            return in_array($env, ['1', 'true', 'yes', 'on'], true);
        }

        $opt = get_option('ca_ghl_fetch_allowed', null);
        if ($opt !== null && $opt !== '') {
            return $opt === '1' || $opt === 1 || $opt === true;
        }

        return (bool) apply_filters('cheapalarms_ghl_fetch_allowed', false);
    }
}

