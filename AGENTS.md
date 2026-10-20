# AGENTS.md - instructions for AI agents and developers

Read this before changing anything. Keep it updated at the end of every task (see "Changelog for agents").

## Project in one paragraph
WordPress site for Deliverance Church Kabete N Market (Nairobi). Classic PHP theme `dck-elite-pro/` (no build step, no npm, vanilla JS) + companion plugin `dck-core/` that provides the **DCK Church** admin screen. All visitor-facing text is editable; defaults live in code so blank settings never break a page.

## Golden rules
1. **Never hardcode content.** Phone, email, address, giving numbers, service times, page text -> read via `dck_home_val( 'key', 'default' )` / helpers in `inc/content.php` and expose the key in `dck-core/includes/settings-schema.php`.
2. **Never hardcode secrets.** API keys only via `wp-config.php` constants (`DCK_AI_KEY`, `DCK_AI_STT_KEY`). See SECURITY-AUDIT.md.
3. **Escape on output, sanitize on input, check capability + nonce on every write.** Public REST endpoints must be rate limited (`dck_sec_guard()` for forms, `dck_ai_rate_ok()` for AI).
4. **Do not remove features.** Church content (sermons, events, prayers, messages, opt-outs) is real data.
5. **Wrap every function in `if ( ! function_exists() )`** (child-theme/plugin overridable) and prefix everything `dck_`.
6. **Bump versions** (`functions.php` + `style.css`) on every theme change.
7. **Lint before commit:** `php -l` on every changed PHP file, `node --check` on JS.
8. Confirmed facts: first prayer meeting 9 June 2017, first Sunday service 11 June 2017, official launch 18 June 2017 (Rev. Ben Luvutse). Do not "correct" these.
9. Do not invent church facts (names, numbers, programmes). Use what exists or ask.

## Where things live
| Need to change | File |
|---|---|
| Global defaults & helpers (phone, giving, tokens, legal text) | `dck-elite-pro/inc/content.php` |
| Homepage data, stats, ministries list | `inc/home.php`, `front-page.php` |
| Inner pages data (pastors, about, services, gatherings, week calendar, photo hero) | `inc/pages.php` |
| Bible reader proxy + versions | `inc/scripture.php`, `assets/js/bible-reader.js`, `template-bible.php` |
| AI (assistant, Bible AI, sermon search, voice, transcription) | `inc/ai*.php` |
| Security (headers, IP, rate limits, form guard) | `inc/security.php`, `inc/hardening.php` |
| Admin settings screen (tabs/fields) | `dck-core/includes/settings-schema.php`, `settings-page.php` |
| Styles | `dck-elite-pro/style.css` (append new blocks at the end with a version comment) |

## Adding an editable field
1. Read it in the template: `dck_home_val( 'dck_my_key', __( 'Default', 'dck-elite' ) )`.
2. Add `$f( 'dck_my_key', label, type, default )` to the right tab in `settings-schema.php`. Types: text, textarea, html, url, email, image, date, services, sections, gatherings.
3. Values are stored as **theme mods** (same as the Customizer).

## Adding a REST endpoint
- Namespace `dck/v1`. Public form -> `dck_sec_guard( $request, $params, 'bucket' )` first. Public AI -> `dck_ai_rate_ok()`. Admin -> `permission_callback` with `current_user_can()`.
- Validate every param (whitelist, regex, length caps). Return generic errors to the public; details only for `manage_options`.

## Testing checklist
`php -l`, `node --check`, then on staging: every page renders, forms submit, DCK Church saves, Bible reader loads chapters, no console errors, mobile width 375px.

## Changelog for agents (newest first)
- 2026-10-20 - Security audit v4.14.0 / DCK Core 1.1.0: spoof-proof client IP, HSTS + CSP baseline + Permissions-Policy, HTTPS redirect, CORS lock for dck/v1, login brute-force limit, shorter staff sessions, AI daily cost cap, STT upload MIME sniffing, scripture rate limit, DISALLOW_FILE_EDIT, docs added.
- 2026-10-20 - v4.15.0 / DCK Core 1.2.0: branded split-screen login page (inc/login.php, assets/css/login.css; text + photo editable in DCK Church > Church & Contact > Staff login page), login + lost-password honeypot, lost-password rate limit (3/hour/IP) and no account enumeration, lockout message kept visible.
- 2026-10-20 - v4.13 gallery mosaic; theme screenshot.png; v4.12 Bible highlights/saved/verse image; v4.11 Gatherings manager; v4.10 Services/Events; v4.9 Pastors; v4.8 Live/Sermons; v4.6-4.7 multilingual Bible reader; v4.3-4.5 DCK Core plugin + redesign of all pages.
