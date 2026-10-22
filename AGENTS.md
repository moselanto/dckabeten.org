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
> Current status, live-site (wp-admin) changes and the prioritised pending list live in **docs/PROJECT-STATUS.md**. Read it before starting work.

- 2026-10-22 - v4.33.0 / DCK Core 1.10.0: schema opening hours now come from DCK Church > Church & Contact > Opening hours (Google) (`dck_seo_opening_hours`, lines `Day|HH:MM|HH:MM`), default = the Google Business Profile hours confirmed by the church on 22 Oct 2026 (Mon-Wed, Fri-Sat 09:00-18:00; Thu 09:00-19:00; Sun 07:00-18:00). Blank or invalid falls back to the Gatherings-based hours. Service times stay on Services/FAQ.
- 2026-10-22 - v4.33.0: "Plan Your Visit" button in template-parts/join-us.php (shown on About, Ministries, Events, Contact, Terms) now links to /plan-your-visit/ in the same tab instead of the Google Maps share link. "Get Directions" buttons still use the map link. Also on the live site (wp-admin, no code): GA4 G-MMV7163YMC via WPCode header, GA4 click-tracking snippet 653 (phone_call, whatsapp_click, directions_click, plan_visit_click key events; live_click, give_click), GSC + Bing verified via Rank Math, llms.txt + robots.txt (AI training bots allowed) in public_html, Cloudflare www-to-apex redirect rule, FAQ dck_faq_items with 23 items incl. "How do I talk to Pastor Justus Kubai?".
- 2026-10-20 - v4.32.0: mobile menu sheet no longer scrolls sideways (cause: global `.dck-nav a{white-space:nowrap}` stopped the "New here?" banner, Call/WhatsApp and quick-action cards from wrapping). Fix block at end of style.css: overflow-x hidden/clip, minmax(0,1fr) grids, wrapping text, install button kept on one line. style.min.css + min-manifest rebuilt.
- 2026-10-20 - v4.31.0 / DCK Core 1.9.0: editability audit; FAQ page now editable in DCK Church > Page Text > FAQ page (`dck_faq_items`, lines `Group|Question|Answer`, tokens supported, blank = built-in groups; `dck_elite_faq_groups` filter still runs after). Verified locally: override renders + FAQPage schema, revert restores 22 items, no PHP notices.
- 2026-10-20 - Live Rank Math pass (no code change): SEO title + description on all 63 public URLs (wording in docs/seo/seo-meta.json), focus keywords, Local SEO as Organization with NAP + hours, tag/author/date archives cleaned up, image auto-ALT, 8 curated post tags, Sample Page trashed. PR #1 (4.30.0) merged to main. Next: test 4.30.0 with Rank Math installed locally, ship zip, verify live. See docs/PROJECT-STATUS.md.
- 2026-10-20 - Security audit v4.14.0 / DCK Core 1.1.0: spoof-proof client IP, HSTS + CSP baseline + Permissions-Policy, HTTPS redirect, CORS lock for dck/v1, login brute-force limit, shorter staff sessions, AI daily cost cap, STT upload MIME sniffing, scripture rate limit, DISALLOW_FILE_EDIT, docs added.
- 2026-10-20 - v4.30.0 (branch seo-geo-aeo-audit-2026): SEO single source of truth - when Rank Math/Yoast is active (live site) the plugin owns title/description/canonical/robots/OG/sitemap and the theme owns ONE JSON-LD @graph (#church, #website, {url}#webpage, breadcrumb, video/article/event) and strips the plugin graph; hours from Gatherings; no placeholder sameAs; YouTube VideoObject embedUrl only; Events only when dated; SearchAction + meta keywords removed. See docs/seo/SEO-AUDIT-2026.md and docs/seo/robots.txt.
- 2026-10-20 - v4.29.0: full verification on a local WordPress (SQLite): all 24 public pages, manifest/SW/offline, 10 REST forms, 17 admin screens and both Excel downloads return 200 with no PHP errors. Fixed 'translations loaded too early' notice (dck_rec_register now runs on init). I'm New 'How did you hear about us?' is now an inclusive single-choice card list (dck_heard_options filterable; follow-up detail for invited/social/other).
- 2026-10-20 - DCK Core 1.8.0: Member Register Excel template round-trip - Members > Template & upload: blank template or template with current members (Instructions + Members sheets, drop-downs via dataValidations, gold Yes columns per ministry department, text-format phone/date, grey Member ID for updates, 300 pre-formatted rows); upload .xlsx (DCK_Xlsx_Reader: ZipArchive+SimpleXML, LIBXML_NONET, DTD refused, 5 MB / 40 MB unpacked / 5000 rows) or .csv; matching Member ID > phone > email; per-row consent column; skipped-row report; uploads logged. Tested round-trip with openpyxl.
- 2026-10-20 - DCK Core 1.7.0: private Member Register (includes/members.php, CPT dck_member, capability dck_manage_members - admins by default, editors optional; private status forced; no REST/public) with branded Excel export (includes/xlsx-writer.php, pure PHP + ZipArchive: Summary, All Members, one sheet per ministry department, No department yet; logo, navy/gold, frozen header, filters, landscape print, CONFIDENTIAL header) + CSV import + export audit log. NOTE: 1.5.0/1.6.0 shipped an empty dck-core.php by mistake; fixed in 1.6.1. Never write a file with open(p,'w').write(open(p).read()...) - it truncates first.
- 2026-10-20 - v4.28.0: instant M-Pesa giving via Paystack STK push (inc/paystack.php, assets/js/paystack.js): REST dck/v1/pay/start (Paystack /charge mobile_money provider mpesa, KES, ref DCK-xxxx, creates dck_gift channel paystack), /pay/otp (submit_otp), /pay/status (polled every 4s, verifies with Paystack max every 8s), /pay/webhook (x-paystack-signature HMAC-SHA512, re-verifies, amount+currency check, idempotent auto-verify + thank-you + finance email). Key ONLY in wp-config DCK_PAYSTACK_SECRET; card hidden if not set. Overview shows webhook URL + setup check.
- 2026-10-20 - v4.27.0 / DCK Core 1.6.0: Church Tools settings polish - message fields full width with tap-to-insert {placeholder} chips (field keys 'wide','tokens'), Region & formats section (dck_ct_country_code used by dck_ct_wa_number, dck_ct_currency default for bank/abroad, dck_ct_quick_amounts), Overview Setup check + 'Send a test email to each team' (admin-post dck_ov_testmail).
- 2026-10-20 - v4.26.0 / DCK Core 1.5.0: header Call/WhatsApp removed (Call stays in bottom bar); floating WhatsApp chat widget above Ask Hope (inc/whatsapp-widget.php, assets/js/wa-widget.js, .dckw styles; topics, office-hours status from site timezone, teaser once per session; settings in Church Tools); international giving (currency select, From abroad channel + transfer reference, dck_ct_currencies/dck_ct_money, CSV currency column), phone normaliser accepts 00 prefix, +254 placeholders; Church Tools Overview page (inc/tools-overview.php, admin.php?page=dck-overview) + Dashboard widget.
- 2026-10-20 - v4.25.0: branded QR code (assets/images/app/qr-new-here.svg + .png, generated locally, points to https://dckabeten.org/new-here/, error correction H, verified to decode) shown in a 'New here?' band on the homepage (dck_newhere_band, hidden on phones where a button is shown) and on the admin QR page (no external QR service).
- 2026-10-20 - v4.24.0: Give page 'Where your gift goes' cards are tappable (icon, Give to X link) and preselect the purpose in the gift form; #give-<purpose> deep link.
- 2026-10-20 - v4.23.0: bottom bar Sermons tab replaced by a green Call Us tab (tel link); Sermons moved to the menu shortcut tiles.
- 2026-10-20 - v4.22.0 / DCK Core 1.4.0: Church Tools tab prefilled (tab flag 'prefill' renders defaults as values; 'links' quick buttons; section 'desc'), pastoral care requests (inc/pastoral-care.php, template-care.php /pastoral-care/, CPT dck_care, REST dck/v1/care), home fellowships + volunteers (inc/serve.php, template-fellowships.php /fellowships/, template-serve.php /serve/, CPTs dck_fellowship + dck_volunteer, REST dck/v1/serve), shared record framework inc/church-records.php (dck_rec_register), mobile header Call + WhatsApp buttons and Call/WhatsApp tiles in menu, gift form purpose cards + Other + quick amounts.
- 2026-10-20 - v4.21.0 / DCK Core 1.3.0: church tools - giving records (inc/giving-records.php, CPT dck_gift, REST dck/v1/gift, verify + thank-you email/WhatsApp, CSV), I'm New visitor follow-up (inc/visitors.php, template-welcome.php, page /new-here/, CPT dck_visitor, REST dck/v1/visitor, stages, QR page, CSV), event registration (inc/event-rsvp.php, CPT dck_rsvp, REST dck/v1/rsvp, capacity, check-in, hourly cron email reminders day before, WhatsApp reminder links, broadcast number list), installable app (inc/pwa.php, assets/js/sw.js served at /?dck_sw=1, manifest /?dck_manifest=1, offline /?dck_offline=1, icons assets/images/app/), shared helpers inc/church-tools.php + assets/js/church-forms.js; settings in DCK Church > Church Tools. sw.js is excluded from minification (has placeholders).
- 2026-10-20 - v4.20.0: refined mobile app look - top strip hidden on phones (language picker moves into the menu), white header with round cream menu button, calm bottom bar with larger icons/labels and raised gold Live circle.
- 2026-10-20 - v4.19.0: mobile hero polish - no slide arrows on phones, tighter title/text, side-by-side CTA buttons.
- 2026-10-20 - v4.18.0: mobile app-style navigation (inc/mobile-nav.php): slim sticky header with animated burger, full-screen menu sheet with tap-to-expand sub-menus + quick actions (Live, Prayer, Give, Service Times) + call/directions, bottom tab bar (Home, Sermons, Live, Give, Menu; hidden on the Bible reader which has its own toolbar), compact one-line top bar, floating buttons lifted above the tab bar.
- 2026-10-20 - v4.17.0: speed pass - hero images are real <img fetchpriority=high> (LCP), YouTube click-to-play (dck_yt_facade), minified assets via assets/min-manifest.json (run tools/build-assets.sh after editing CSS/JS!), unused WP extras removed (block CSS only when content uses blocks, wp-embed, heartbeat for visitors, oEmbed/RSD/shortlink), ~1.5 MB of images recompressed, services photo now webp, print button is a real <button>.
- 2026-10-20 - v4.16.0: two-factor login (inc/two-factor.php) using the official Two Factor plugin; staff (edit_posts) are sent to their profile until 2FA is on; disable with DCK_REQUIRE_2FA false; styled code screen.
- 2026-10-20 - v4.15.0 / DCK Core 1.2.0: branded split-screen login page (inc/login.php, assets/css/login.css; text + photo editable in DCK Church > Church & Contact > Staff login page), login + lost-password honeypot, lost-password rate limit (3/hour/IP) and no account enumeration, lockout message kept visible.
- 2026-10-20 - v4.13 gallery mosaic; theme screenshot.png; v4.12 Bible highlights/saved/verse image; v4.11 Gatherings manager; v4.10 Services/Events; v4.9 Pastors; v4.8 Live/Sermons; v4.6-4.7 multilingual Bible reader; v4.3-4.5 DCK Core plugin + redesign of all pages.


## Rule: after editing any CSS or JS
Run `bash tools/build-assets.sh` and commit the *.min.* files and assets/min-manifest.json together with the source. If you skip it, the site safely serves the unminified file.
