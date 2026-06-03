<?php
/**
 * Portable single-instance configuration.
 *
 * Copy to `instance.php` and edit ONLY this file per business deployment
 * (CheapAlarms, Safeguard, etc.).
 *
 * Precedence at runtime:
 * 1) instance.php
 * 2) secrets.php (legacy)
 * 3) environment variables
 * 4) code defaults
 */
return [
    // Business branding
    'brand_name'         => 'CheapAlarms',
    'support_name'       => 'CheapAlarms Support',
    'support_email'      => 'support@cheapalarms.com.au',
    'email_from_name'    => 'CheapAlarms',
    'email_from_address' => 'quotes@cheapalarms.com.au',

    // Frontend/domain
    'frontend_url'       => 'https://headless-cheapalarms.vercel.app',

    // Core integrations
    'ghl_token'          => '',
    'ghl_location_id'    => '',
    'ghl_user_id'        => '',
    'servicem8_api_key'  => '',

    // Security
    'upload_shared_secret' => '',
    'upload_max_mb'        => 10,
    'jwt_secret'           => '',
    'jwt_ttl_seconds'      => 3600,

    // CORS
    'upload_allowed_origins' => [
        'https://cheapalarms.com.au',
        'https://headless-cheapalarms.vercel.app',
    ],
    'api_allowed_origins' => [
        'https://cheapalarms.com.au',
        'https://headless-cheapalarms.vercel.app',
    ],

    // Xero
    'xero_client_id'          => '',
    'xero_client_secret'      => '',
    'xero_redirect_uri'       => 'https://headless-cheapalarms.vercel.app/xero/callback',
    'xero_sales_account_code' => '200',
    'xero_bank_account_code'  => '090',
    'xero_direct_invoicing'   => false,

    // Stripe
    'stripe_publishable_key' => '',
    'stripe_secret_key'      => '',
    'stripe_webhook_secret'  => '',

    // Data-source policy
    'ghl_fetch_allowed' => false,
];

