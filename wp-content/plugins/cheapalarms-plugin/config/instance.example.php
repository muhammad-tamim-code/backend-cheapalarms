<?php
/**
 * Portable single-instance configuration.
 *
 * Prefer editing ../../../../../cheapalarms.instance.json at the repo root,
 * then run: node scripts/sync-instance-config.mjs
 * That generates config/instance.php (URLs) and updates local secrets.php.
 *
 * Precedence at runtime:
 * 1) instance.php (generated — URL keys)
 * 2) secrets.php (credentials)
 * 3) environment variables
 * 4) code defaults in Config.php
 *
 * Current production layout (pre-portal-subdomain cutover):
 *   WordPress + plugin  → https://cheapalarms.com.au
 *   Next.js portal      → https://headless-cheapalarms.vercel.app
 *   Planned portal host → https://portal.cheapalarms.com.au (set in cheapalarms.instance.json when ready)
 */
return [
    // Business branding
    'brand_name'             => 'CheapAlarms',
    'brand_tagline'          => 'Your Security Partner',
    'brand_primary_color'    => '#171717',
    'brand_accent_color'     => '#1EA6DF',
    'support_name'       => 'CheapAlarms Support',
    'support_email'      => 'support@cheapalarms.com.au',
    'email_from_name'    => 'CheapAlarms',
    'email_from_address' => 'quotes@cheapalarms.com.au',

    'estimate_number_prefix' => 'EST-',

    // Next.js portal — email links, password reset, Xero callback host
    // Sync from cheapalarms.instance.json; do not hand-edit after sync.
    'frontend_url'       => 'https://headless-cheapalarms.vercel.app',

    // Core integrations (credentials — keep in secrets.php or set here on server)
    'ghl_token'          => '',
    'ghl_location_id'    => '',
    'ghl_user_id'        => '',
    'servicem8_api_key'  => '',

    // Security
    'upload_shared_secret' => '',
    'upload_max_mb'        => 10,
    'jwt_secret'           => '',
    'jwt_ttl_seconds'          => 3600,
    'jwt_remember_ttl_seconds' => 2592000,

    // CORS — auto-synced from cheapalarms.instance.json
    'upload_allowed_origins' => [
        'https://cheapalarms.com.au',
        'https://headless-cheapalarms.vercel.app',
        'http://localhost',
        'http://localhost:3000',
    ],
    'api_allowed_origins' => [
        'https://cheapalarms.com.au',
        'https://headless-cheapalarms.vercel.app',
        'http://localhost:3000',
    ],

    // Xero — derived from frontend_url when omitted
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
