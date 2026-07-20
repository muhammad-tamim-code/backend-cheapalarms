<?php
/**
 * Example secrets configuration file
 *
 * Copy this file to secrets.php and fill in your actual values
 * OR prefer the new portable instance config: config/instance.php
 * (copy from config/instance.example.php).
 * OR set them as environment variables (recommended for production)
 *
 * secrets.php is gitignored - never commit real secrets
 *
 * URLs: edit cheapalarms.instance.json at repo root, run node scripts/sync-instance-config.mjs
 * Production layout:
 *   WordPress + plugin  → https://cheapalarms.com.au
 *   Next.js portal      → https://headless-cheapalarms.vercel.app (until portal subdomain cutover)
 */

return [
    // Branding (portable per business instance)
    'brand_name'             => 'CheapAlarms',
    'brand_tagline'          => 'Your Security Partner',
    'brand_primary_color'    => '#171717',
    'brand_accent_color'     => '#1EA6DF',
    // Optional absolute logo URLs (default: {frontend_url}/brand/logo-horizontal.png)
    // 'brand_logo_horizontal' => 'https://portal.example.com/brand/logo-horizontal.png',
    // 'brand_logo_mark'       => 'https://portal.example.com/brand/logo-mark.png',
    'support_name'           => 'CheapAlarms Support',
    'support_email'          => 'support@cheapalarms.com.au',
    'email_from_name'        => 'CheapAlarms',
    'email_from_address'     => 'quotes@cheapalarms.com.au',

    // GHL Integration Credentials
    'ghl_token'              => '', // or set CA_GHL_TOKEN env var
    'ghl_location_id'        => '', // or set CA_LOCATION_ID env var

    // ServiceM8 Integration
    'servicem8_api_key'      => '', // or set CA_SERVICEM8_API_KEY env var

    // Upload Security
    'upload_shared_secret'   => '', // or set CA_UPLOAD_SHARED_SECRET env var
    'upload_max_mb'          => 10,

    // CORS Configuration - Allowed Origins for Photo Uploads
    'upload_allowed_origins' => [
        'https://cheapalarms.com.au',
        'https://headless-cheapalarms.vercel.app',
        // Local Development
        'http://localhost',
        'http://localhost:3000',
        'http://localhost:5173',
        'http://127.0.0.1:5173',
    ],

    // CORS Configuration - Allowed Origins for API Access
    'api_allowed_origins' => [
        'https://cheapalarms.com.au',
        'https://headless-cheapalarms.vercel.app',
        // Local Development
        'http://localhost:3000',
        'http://localhost:5173',
        'http://127.0.0.1:5173',
    ],

    // JWT Authentication Secret
    'jwt_secret'       => '', // or set CA_JWT_SECRET env var
    'jwt_ttl_seconds'          => 3600, // 1 hour (default session)
    'jwt_remember_ttl_seconds' => 2592000, // 30 days when "Remember me" is checked

    // Next.js portal URL (NOT the WordPress marketing domain)
    'frontend_url'     => 'https://portal.cheapalarms.com.au',

    // GHL User ID (authorized employee/user ID for sending estimates)
    'ghl_user_id'      => '', // or set CA_GHL_USER_ID env var

    // Xero Integration Credentials
    'xero_client_id'     => '', // or set CA_XERO_CLIENT_ID env var
    'xero_client_secret' => '', // or set CA_XERO_CLIENT_SECRET env var
    'xero_redirect_uri'  => 'https://headless-cheapalarms.vercel.app/xero/callback',

    // Stripe Integration Credentials
    'stripe_publishable_key' => '', // or set CA_STRIPE_PUBLISHABLE_KEY env var
    'stripe_secret_key'      => '', // or set CA_STRIPE_SECRET_KEY env var
    'stripe_webhook_secret'  => '', // or set CA_STRIPE_WEBHOOK_SECRET env var

    // DeepSeek AI (website chat assistant, server-side only)
    'deepseek_api_key'       => '', // or set CA_DEEPSEEK_API_KEY env var
    'deepseek_model'         => 'deepseek-chat', // or deepseek-reasoner for harder tasks

    // SMS OTP (quote calculator + chat, Twilio). Leave blank for dummy OTP (any 6 digits) until go-live.
    'twilio_account_sid'     => '', // or CA_TWILIO_ACCOUNT_SID
    'twilio_auth_token'      => '', // or CA_TWILIO_AUTH_TOKEN
    'twilio_from_number'     => '', // E.164 e.g. +61400000000, or CA_TWILIO_FROM_NUMBER
    // Optional: force dummy OTP even when Twilio is set (staging). Default dummy when Twilio missing.
    // 'otp_demo_mode'       => true,
];
