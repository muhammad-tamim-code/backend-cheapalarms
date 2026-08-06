<?php
/**
 * Safeguard staging instance, committed for zero-touch Coolify deploys.
 * Production uses config/instance.php (generated, gitignored).
 */
return [
    'frontend_url' => 'https://safeguard-portal.vercel.app',
    'upload_allowed_origins' => [
        'https://staging.safeguardsecurity.com.au',
        'https://safeguard-portal.vercel.app',
        'http://localhost',
        'http://localhost:3000',
        'http://localhost:5173',
        'http://127.0.0.1:5173',
    ],
    'api_allowed_origins' => [
        'https://staging.safeguardsecurity.com.au',
        'https://safeguard-portal.vercel.app',
        'http://localhost',
        'http://localhost:3000',
        'http://localhost:5173',
        'http://127.0.0.1:5173',
    ],

    'brand_name'             => 'Safeguard',
    'brand_tagline'          => 'Security Services',
    'brand_primary_color'    => '#2B7FB3',
    'brand_accent_color'     => '#E88324',
    'support_name'           => 'Safeguard Support',
    'support_email'          => 'support@safeguardsecurity.com.au',
    'email_from_name'        => 'Safeguard',
    'email_from_address'     => 'quotes@safeguardsecurity.com.au',

    'estimate_number_prefix' => 'EST-',

    // Invoice-only launch
    'payments_enabled'       => false,
    'xero_enabled'           => false,
    'xero_direct_invoicing'  => false,
    'xero_redirect_uri'      => '',

    'ghl_token'              => 'pit-195d44e7-6b55-4e86-aa33-1c039d458e5c',
    'ghl_location_id'        => 'aLTXtdwNknfmEFo3WBIX',
    'ghl_user_id'            => '',

    'upload_shared_secret'   => 'RXquQdZurMTanrs1L2f6jwm-TkJjjS4o1Zs__LQa8qBUGaKF_DQpsurQT13JyZXk',
    'jwt_secret'             => 'MZi3PniMBCF7r9SrXUbICLZF8cVz1ou12BOz_1bc8ZQC0-qgicEcL1Agn1dBlFNq',

    // Wasabi (also in secrets.php; duplicated so staging works if secrets merge order changes)
    'wasabi_access_key'      => '0PUCC9LAQZME31PSO0A7',
    'wasabi_secret_key'      => 'B2gFu2Bb17tmr6OLEri6U61A7wRXnCUBzZ3nTtHL',
    'wasabi_bucket'          => 'safeguardportal',
    'wasabi_region'          => 'ap-southeast-2',
    'wasabi_endpoint'        => 'https://s3.ap-southeast-2.wasabisys.com',
    'wasabi_prefix'          => 'estimate-photos',
    'wasabi_signed_url_ttl'  => 3600,

    'stripe_publishable_key' => '',
    'stripe_secret_key'      => '',
    'stripe_webhook_secret'  => '',
];
