<?php
/**
 * Safeguard staging instance — committed for zero-touch Coolify deploys.
 * Production uses config/instance.php (generated, gitignored).
 */
return [
    'frontend_url' => 'https://safeguard-portal.vercel.app',
    'xero_redirect_uri' => 'https://safeguard-portal.vercel.app/xero/callback',
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

    'ghl_token'              => 'pit-195d44e7-6b55-4e86-aa33-1c039d458e5c',
    'ghl_location_id'        => 'aLTXtdwNknfmEFo3WBIX',
    'ghl_user_id'            => '',

    'upload_shared_secret'   => 'RXquQdZurMTanrs1L2f6jwm-TkJjjS4o1Zs__LQa8qBUGaKF_DQpsurQT13JyZXk',
    'jwt_secret'             => 'MZi3PniMBCF7r9SrXUbICLZF8cVz1ou12BOz_1bc8ZQC0-qgicEcL1Agn1dBlFNq',

    'stripe_publishable_key' => 'pk_test_51SgyW7PZnmpzepwm77Y1I1VeheOhybZgTrzmml7pneZ0N821hpGGqKtS3wtGbkAW7ugayllCOiUBmzc5UftAeCPC00nwmDV0Fg',
    'stripe_secret_key'      => '',
    'stripe_webhook_secret'  => '',
];
