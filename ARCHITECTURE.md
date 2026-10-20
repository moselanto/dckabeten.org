# Architecture

## 1. Components
### Theme `dck-elite-pro/`
| Path | Responsibility |
|---|---|
| `functions.php` | Setup, sermon CPT fallback, page/menu one-time setup (`DCK_ELITE_SETUP_VERSION`), includes |
| `inc/content.php` | Editable defaults & helpers: phone/email/address, service times, giving, `%TOKENS%`, legal sections |
| `inc/home.php` | Homepage model, ministries, stats, Customizer panel |
| `inc/pages.php` | About/Pastors/Services data, `dck_lines()`, gatherings, photo hero, week calendar, editor shortcut |
| `inc/scripture.php` | Bible versions catalogue + cached proxy to bible.helloao.org |
| `inc/ai.php` | AI core: key/model, chat call, knowledge (RAG-lite), assistant + prayer endpoints, rate limit |
| `inc/ai-bible.php`, `ai-sermons.php`, `ai-voice.php`, `ai-transcribe.php`, `ai-content.php`, `ai-sermon-tools.php`, `ai-i18n.php` | AI Bible study, sermon search, voice (STT/TTS), transcription, content studio, backfill, language notes |
| `inc/contact.php`, `inc/terms.php` | Contact form + Messages CPT; Terms page + photo opt-out CPT |
| `inc/events.php`, `inc/gallery.php`, `inc/blog.php`, `inc/faq.php`, `inc/sermons.php` | Events CPT, Gallery CPT + REST, blog seeds, FAQ + schema, sermon helpers |
| `inc/seo*.php`, `inc/sitemap.php` | Meta tags, schema.org, sitemap |
| `inc/security.php`, `inc/hardening.php` | Security headers, IP detection, rate limiting, form guard, HTTPS, CORS, login limiter, sessions |
| `inc/media.php`, `inc/perf.php` | Image helpers, preloads |
| `template-*.php`, `archive-dck_sermon.php`, `single*.php`, `index.php`, `front-page.php` | Page templates |
| `assets/js/*.js` | One small script per feature (see list below) |

### Plugin `dck-core/`
`dck-core.php` (bootstrap), `includes/settings-schema.php` (all tabs/fields), `includes/settings-page.php` (render, sanitize, save via `admin-post.php`, nonce + `edit_theme_options`), `includes/post-types.php` (Sermons + taxonomies), `assets/admin.{css,js}` (repeaters, media picker, search).

## 2. Data model
| Type | Key | Notes |
|---|---|---|
| CPT | `dck_sermon` (+ `dck_sermon_series/speaker/topic`) | meta `_dck_video_url,_dck_audio_url,_dck_scripture,_dck_download_url,_dck_sermon_image,_dck_summary,_dck_study_guide` |
| CPT | `dck_event` | meta `_dck_event_date,_start,_end,_location,_daylabel,_recurring,_notice` |
| CPT | `dck_photo` (+ `dck_gallery_cat`) | featured image = photo |
| CPT (private) | `dck_prayer`, `dck_message`, `dck_photo_optout` | personal data - admin only, `show_ui`, not public |
| Theme mods | `dck_*` | every editable setting (see settings-schema.php) |
| Transients | `dck_rl_*`, `dck_ai_rl_*`, `dck_ai_budget_*`, `dck_login_fail_*`, `dck_bbl_*`, `dck_bible_*` | rate limits, AI budget, caches |

## 3. REST API (`/wp-json/dck/v1/...`)
| Route | Method | Access | Protection |
|---|---|---|---|
| `contact`, `prayer`, `photo-optout` | POST | public | nonce + honeypot + time trap + IP rate limit (`dck_sec_guard`) + sanitization + spam checks |
| `assistant`, `bible`, `sermon-search`, `tts` | POST | public | per-IP rate limit (25/10 min) + global daily AI cap + length caps |
| `stt` | POST (file) | public | rate limits + 8 MB cap + MIME sniffing allowlist + is_uploaded_file |
| `gallery` | GET | public | read-only, bounded per_page |
| `scripture/books`, `scripture/chapter` | GET | public | version whitelist, strict regex, 240 req/10 min/IP, cached |
| `generate`, `transcribe`, `transcribe-status`, `sermon-backfill` | POST/GET | `edit_posts` | capability + nonce |
CORS: only the site's own origin is allowed for `dck/v1`.

## 4. Front-end scripts
main (nav, counters, local time, copy), home (slider), a11y, translate/i18n, assistant/voice, bible + bible-reader, sermon-search, sermons-ui, live, week, give, contact, prayer, terms, pages (ministries/FAQ), pastors, gallery, read-aloud, transcript, sermon-print, admin scripts.

## 5. Caching & performance
Self-hosted fonts, WebP images, transients for AI/Bible responses, next-chapter prefetch, lazy images, versioned assets (`?ver=DCK_ELITE_VERSION`). Works behind Cloudflare; purge after deploy.

## 6. Deploy
Upload theme + plugin folders, purge cache, open DCK Church. Secrets in wp-config.php. See SECURITY-AUDIT.md production checklist.
