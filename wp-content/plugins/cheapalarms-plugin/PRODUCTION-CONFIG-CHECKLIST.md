# Production Configuration Checklist

## Domain layout

| Host | Role |
|------|------|
| `https://cheapalarms.com.au` | WordPress + `cheapalarms-plugin` (REST API, marketing, calculators) |
| `https://portal.cheapalarms.com.au` | Next.js customer + admin portal (Vercel or host) |

## ⚠️ CRITICAL: Before Deploying to Production

### WordPress (`cheapalarms.com.au`)

**Settings → General**

- WordPress Address (URL): `https://cheapalarms.com.au`
- Site Address (URL): `https://cheapalarms.com.au`

**`wp-config.php`**

```php
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);
define('WP_DEBUG_DISPLAY', false);
define('CA_DEV_BYPASS', false);
// Or: define('CA_DEV_BYPASS', defined('WP_DEBUG') && WP_DEBUG);
```

### Plugin config (`config/instance.php` or `config/secrets.php` on server)

| Key | Production value |
|-----|------------------|
| `frontend_url` | `https://portal.cheapalarms.com.au` |
| `api_allowed_origins` | `https://portal.cheapalarms.com.au`, `https://cheapalarms.com.au` |
| `upload_allowed_origins` | same as above |
| `xero_redirect_uri` | `https://portal.cheapalarms.com.au/xero/callback` |
| `ghl_token`, `ghl_location_id`, `upload_shared_secret`, `jwt_secret` | Required — plugin won't start without GHL + upload secret |

Copy from `config/instance.example.php` if starting fresh.

### Next.js portal (`portal.cheapalarms.com.au`)

**Vercel → Environment Variables**

| Name | Value |
|------|-------|
| `NEXT_PUBLIC_WP_URL` | `https://cheapalarms.com.au/wp-json` |
| `NEXT_PUBLIC_GHL_LOCATION_ID` | Your GHL location ID |
| `NODE_ENV` | `production` |

Redeploy after changing env vars.

### Third-party dashboards

- **Stripe** — webhook URL on `https://portal.cheapalarms.com.au/api/...`
- **Xero** — redirect URI `https://portal.cheapalarms.com.au/xero/callback`

### CORS

- Localhost origins are stripped in production when `WP_DEBUG = false`
- To allow localhost in production (not recommended): `CA_ALLOW_LOCALHOST_CORS=true`

### Verification

1. `curl https://cheapalarms.com.au/wp-json/ca/v1/health`
2. Login at `https://portal.cheapalarms.com.au/admin`
3. Submit test quote — email links point to `portal.cheapalarms.com.au`
4. Photo upload from portal — no CORS errors in browser console
5. Xero connect from admin settings

---

**Last Updated:** 2026-06-03
