<?php
/**
 * Safeguard staging, copy to config/secrets.php on the WordPress server.
 * Fill in real values (copy from old Hostinger deploy or your password manager).
 * NEVER commit secrets.php to git.
 */
return [
    'brand_name'             => 'Safeguard',
    'brand_tagline'          => 'Security Services',
    'support_name'           => 'Safeguard Support',
    'support_email'          => 'support@safeguardsecurity.com.au',
    'email_from_name'        => 'Safeguard',
    'email_from_address'     => 'quotes@safeguardsecurity.com.au',

    // Required, plugin blocks portal/login until these are set
    'ghl_token'              => 'PASTE_GHL_PRIVATE_INTEGRATION_TOKEN',
    'ghl_location_id'        => 'aLTXtdwNknfmEFo3WBIX',
    'ghl_user_id'            => 'PASTE_GHL_USER_ID',
    'upload_shared_secret'   => 'PASTE_RANDOM_32_CHAR_SECRET',

    'jwt_secret'             => 'PASTE_RANDOM_JWT_SECRET',

    // Stripe (test keys for staging)
    'stripe_publishable_key' => 'pk_test_51SgyW7PZnmpzepwm77Y1I1VeheOhybZgTrzmml7pneZ0N821hpGGqKtS3wtGbkAW7ugayllCOiUBmzc5UftAeCPC00nwmDV0Fg',
    'stripe_secret_key'      => 'PASTE_STRIPE_SECRET',
    'stripe_webhook_secret'  => 'PASTE_STRIPE_WHSEC',

    // URLs, instance.php overrides these; keep in sync via sync-instance-config.mjs
    'frontend_url'           => 'https://safeguard-portal.vercel.app',
    'xero_redirect_uri'      => 'https://safeguard-portal.vercel.app/xero/callback',

    // DeepSeek AI chat (optional, website assistant)
    'deepseek_api_key'       => 'PASTE_DEEPSEEK_API_KEY',
    'deepseek_model'         => 'deepseek-chat',
];
