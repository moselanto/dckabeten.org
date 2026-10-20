# Security Audit - dckabeten.org

**Date:** 20 October 2026 · **Scope:** theme `dck-elite-pro` v4.13 -> **v4.14.0**, plugin `dck-core` v1.0 -> **v1.1.0** · **Method:** manual review of every PHP template/include, REST route, form handler, admin handler and JS file; pattern search for injection sinks, unescaped output, superglobals, uploads, secrets.

> WordPress core handles password hashing (bcrypt/phpass via `wp_hash_password`), login cookies and database access. This audit covers our code and how it uses those systems.

## 1. Summary
| # | Area | Finding | Severity | Status |
|---|---|---|---|---|
| 1 | Rate limiting bypass | Client IP taken from `X-Forwarded-For` / `CF-Connecting-IP` without checking the sender -> attackers could spoof IPs and bypass every rate limit | **High** | Fixed |
| 2 | Insecure file upload | `/stt` trusted the browser-supplied MIME type, 12 MB cap, no `is_uploaded_file` check | **High** | Fixed |
| 3 | Cost abuse / DoS | Public AI endpoints only had per-IP limits; distributed abuse could run up the AI bill | Medium | Fixed (daily global cap) |
| 4 | Brute-force login | No login attempt limiting | Medium | Fixed |
| 5 | CORS | WordPress echoes any Origin on REST responses; our `dck/v1` API could be called from other sites' browsers | Medium | Fixed |
| 6 | HTTPS / transport | No HSTS; no HTTP->HTTPS redirect at theme level | Medium | Fixed |
| 7 | Security headers | Missing CSP baseline, Permissions-Policy, COOP; legacy `X-XSS-Protection: 1` | Low | Fixed |
| 8 | Session expiry | Staff sessions used defaults (up to 14 days) | Low | Fixed (12 h / 7 days remember) |
| 9 | Scripture proxy | Unthrottled public proxy to third-party API | Low | Fixed (240 req/10 min/IP + strict validation) |
| 10 | TTS parameter | Voice value from settings not whitelisted | Low | Fixed |
| 11 | Code editor | Theme/plugin file editor enabled in wp-admin | Low | Fixed (`DISALLOW_FILE_EDIT`) |
| 12 | Gallery caption | Server HTML-escaped then JS `textContent` -> visible `&amp;` (not a vuln) | Info | Fixed |
| 13 | Directory listing | Plugin folders lacked `index.php` | Info | Fixed |
| 14 | Secrets | AI keys can still be saved in theme settings (database) | Medium | Mitigated: `wp-config.php` constants supported and preferred; see checklist |

No SQL injection sinks found (no raw `$wpdb` queries; all data access via WP APIs with typed args). No `eval`, `unserialize`, shell execution or dynamic includes found.

## 2. Fixes explained
1. **Spoof-proof client IP** (`inc/security.php`): `CF-Connecting-IP` is used only when `REMOTE_ADDR` is inside Cloudflare's published ranges; `X-Forwarded-For` only when `DCK_TRUSTED_PROXY` is defined and matches. Otherwise `REMOTE_ADDR`. All rate limits now hold.
2. **Upload hardening** (`inc/ai-voice.php`): `is_uploaded_file()`, size 1 B-8 MB, MIME sniffed with `finfo` against an audio allowlist; file never stored on disk beyond PHP's temp file; fixed outbound filename.
3. **AI daily cap** (`inc/hardening.php` `dck_ai_budget_ok()`): 1,500 AI calls/day site-wide by default (`DCK_AI_DAILY_CAP` to change), on top of 25/10 min per IP.
4. **Login protection**: 5 failed logins per IP -> 15-minute lockout; counter cleared on success; generic error messages (no username disclosure).
5. **CORS**: for `/dck/v1/*` responses, `Access-Control-Allow-Origin/Credentials` are removed unless the Origin is this site.
6. **HTTPS everywhere**: 301 redirect to HTTPS when the site URL is HTTPS (Cloudflare/proxy aware -> no loops); `Strict-Transport-Security: max-age=31536000; includeSubDomains` on HTTPS; CSP `upgrade-insecure-requests`.
7. **Headers**: `Content-Security-Policy: frame-ancestors 'self'; base-uri 'self'; object-src 'none'; upgrade-insecure-requests`, `Permissions-Policy` (camera/geo/payment/usb off, mic self-only for voice), `Cross-Origin-Opener-Policy`, `X-Frame-Options`, `nosniff`, `Referrer-Policy`, `X-XSS-Protection: 0`.
8. **Sessions**: users who can edit content get 12-hour sessions (7 days with "Remember me").
9. **Scripture proxy**: rate limited, translation whitelist, `^[1-3A-Z][A-Z0-9]{2}$` book regex, chapter 1-150, cached.

## 3. Controls already in place (verified)
- **CSRF:** every admin save uses nonces (`wp_nonce_field` / `check_admin_referer`); public forms require the `wp_rest` nonce.
- **Authorization:** staff REST routes use `current_user_can( 'edit_posts' )`; DCK Church uses `edit_theme_options`; private CPTs (`dck_prayer`, `dck_message`, `dck_photo_optout`) are not public or queryable.
- **Input sanitization:** `sanitize_text_field`, `sanitize_textarea_field`, `sanitize_email`, `esc_url_raw`, `wp_kses_post`, length caps (`dck_sec_cap`), signature data-URL validation.
- **Output escaping (XSS):** `esc_html`, `esc_attr`, `esc_url`, `wp_kses_post` across templates; JS renders API data with `textContent` / escaping helper.
- **Spam:** honeypot, time trap, keyword and disposable-email checks.
- **Info leakage:** WP version hidden, user enumeration blocked (`?author=`, `/wp/v2/users`), generic login errors, XML-RPC pingbacks off, comments disabled, PHP execution blocked in uploads (`.htaccess`).
- **Errors:** technical AI errors shown only to administrators.

## 4. Production checklist (do these on the server)
- [ ] Move keys to `wp-config.php`: `DCK_AI_KEY`, `DCK_AI_STT_KEY`; then clear the key fields in Customizer.
- [ ] `define( 'FORCE_SSL_ADMIN', true );` and confirm the site URL is `https://`.
- [ ] Cloudflare: SSL mode **Full (strict)**, "Always Use HTTPS" on.
- [ ] Unique salts in `wp-config.php` (regenerate at https://api.wordpress.org/secret-key/1.1/salt/).
- [ ] Admin accounts: strong unique passwords + 2FA plugin (e.g. Two-Factor), remove unused admins.
- [ ] Keep WordPress core, PHP (8.1+) and all plugins updated monthly ("regular dependencies"); remove unused plugins/themes.
- [ ] Daily off-site backups (files + database) and a tested restore.
- [ ] File permissions: 644 files, 755 folders, `wp-config.php` 600/640.
- [ ] Review Prayer Requests / Messages / Photo Preferences and delete old personal data per the Privacy Notice.

## 5. Not applicable / notes
- **Password hashing:** handled by WordPress core; the theme/plugin never stores passwords.
- **NoSQL injection:** no NoSQL database used.
- **Environment variables:** WordPress uses `wp-config.php` constants (can read `getenv()` there if your host provides env vars).
- **Dependencies:** no npm/composer packages; only WordPress core and self-hosted fonts.

## Follow-up (v4.15.0, 2026-10-20) - login screen
- Honeypot field on login and lost-password forms; bots that fill it are rejected with the generic error.
- Lost-password requests limited to 3 per hour per IP.
- Lost-password no longer reveals whether an account exists (unknown user/email shows the same "check your email" screen).
- Generic login errors kept; only the lockout message ("Too many...") is shown as-is.

## Follow-up (v4.16.0) - two-factor authentication
- Staff accounts (edit_posts and above) must enable two-factor (authenticator app + recovery codes) via the official Two Factor plugin before using wp-admin. Enforcement can be disabled with DCK_REQUIRE_2FA.
