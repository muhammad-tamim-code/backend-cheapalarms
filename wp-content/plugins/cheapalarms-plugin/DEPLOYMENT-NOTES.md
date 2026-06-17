# Deployment Notes - CheapAlarms Plugin

## Domain layout

| Host | Role |
|------|------|
| `https://cheapalarms.com.au` | WordPress + this plugin |
| `https://portal.cheapalarms.com.au` | Next.js portal (set `frontend_url` + CORS) |

See **`PRODUCTION-CONFIG-CHECKLIST.md`** for the full cutover checklist.

---

## Plugin config on server

**File:** `config/instance.php` (preferred) or `config/secrets.php`

```php
'frontend_url' => 'https://portal.cheapalarms.com.au',
'upload_allowed_origins' => [
    'https://cheapalarms.com.au',
    'https://portal.cheapalarms.com.au',
],
'api_allowed_origins' => [
    'https://cheapalarms.com.au',
    'https://portal.cheapalarms.com.au',
],
'xero_redirect_uri' => 'https://portal.cheapalarms.com.au/xero/callback',
```

Copy from `config/instance.example.php` if starting fresh.

---

## WordPress deployment checklist

- [ ] WordPress on `cheapalarms.com.au` — Settings → General URLs correct
- [ ] `composer install --no-dev` in plugin folder (Stripe)
- [ ] Upload / deploy plugin
- [ ] Activate plugin
- [ ] Permalinks → Post name
- [ ] `config/instance.php` or `secrets.php` with production secrets
- [ ] Health: `https://cheapalarms.com.au/wp-json/ca/v1/health`

---

## Next.js portal (Vercel)

Set on the portal project:

```
NEXT_PUBLIC_WP_URL=https://cheapalarms.com.au/wp-json
```

Custom domain: `portal.cheapalarms.com.au`

See `next-app/VERCEL-DEPLOYMENT.md`.

---

## Health check

```
GET https://cheapalarms.com.au/wp-json/ca/v1/health
```

Expected: `{ "ok": true, ... }`

---

## Paths on server

```
/httpdocs/wp-content/plugins/cheapalarms-plugin/
/httpdocs/wp-content/plugins/cheapalarms-plugin/config/instance.php
/httpdocs/wp-content/plugins/cheapalarms-plugin/logs/cheapalarms.log
```

---

**Status:** Production URLs configured in repo templates — server `instance.php` / Vercel env must match before go-live.
