# dckabeten.org

Website code for **Deliverance Church Kabete N Market (Cistern of Hope Center)**, Uthiru, Nairobi, Kenya - https://dckabeten.org

The site runs on WordPress and consists of two parts, both in this repository:

| Folder | What it is | Install to |
|---|---|---|
| `dck-elite-pro/` | **DCK Elite Pro** theme (v4.14.0) - all page design, templates, AI features, security hardening | `wp-content/themes/dck-elite-pro/` |
| `dck-core/` | **DCK Core** plugin (v1.1.0) - "DCK Church" settings screen (edit all content), Sermons content type | `wp-content/plugins/dck-core/` |

## Requirements
- WordPress 6.0+ (tested to 6.7), PHP 7.4+ (8.1+ recommended), HTTPS certificate
- Outbound HTTPS from the server to: your AI provider (default Google Gemini), `api.groq.com` (optional transcription), `bible.helloao.org` (Bible reader)
- Optional: Cloudflare in front of the site (supported and detected automatically)

## Install / update
1. Download this repository (Code > Download ZIP).
2. Upload `dck-elite-pro` to `wp-content/themes/` (replace the old folder) and activate it.
3. Upload `dck-core` to `wp-content/plugins/` and activate **DCK Core**.
4. Purge any page cache / Cloudflare cache.
5. Go to **DCK Church** in wp-admin and review each tab; press **Save** on the **Gatherings** tab once.

## Configuration (no keys in code)
All secrets live in `wp-config.php`, never in the repository or database:

```php
define( 'DCK_AI_KEY', 'your-ai-provider-key' );        // AI assistant / Bible assistant (Gemini, OpenAI, Groq...)
define( 'DCK_AI_STT_KEY', 'your-groq-key' );           // Optional: speech-to-text / sermon transcription
define( 'DCK_AI_DAILY_CAP', 1500 );                    // Optional: max AI requests per day (cost ceiling)
define( 'DCK_TRUSTED_PROXY', '10.0.0.5' );             // Optional: only if behind a non-Cloudflare reverse proxy
define( 'FORCE_SSL_ADMIN', true );                     // Recommended
```

Everything else (contact details, service times, giving numbers, page text, photos) is edited in **wp-admin > DCK Church**. Nothing needs code changes.

## Main features
- Pages: Home, About, Our Pastors, Services, Ministries, Events, Watch Live, Sermons, Bible, Gallery, Blog, Give, Prayer Request, Contact, FAQ, Terms & Privacy
- Multilingual Bible reader (17 versions: English, Kiswahili, Gikuyu, Dholuo, Sabaot, Suba, Borana, Somali) with compare, highlights, saved verses, verse images
- AI church assistant "Hope", AI Bible study assistant, AI sermon search, sermon transcription (staff)
- Live stream page with live status, time-zone converter, calendar invites
- Online giving (M-Pesa Paybill/Till, bank, international)
- Forms: contact, prayer requests (confidential option), photography opt-out with signature
- SEO: schema.org (Church, Person, Sermon, FAQ), sitemap, per-page meta

## Documentation
- [`AGENTS.md`](AGENTS.md) - rules and context for AI coding agents and developers (read first)
- [`ARCHITECTURE-ESSENTIALS.md`](ARCHITECTURE-ESSENTIALS.md) - one-page architecture summary
- [`ARCHITECTURE.md`](ARCHITECTURE.md) - full architecture, data model, endpoints
- [`PRD.md`](PRD.md) - product requirements and page-by-page scope
- [`SECURITY-AUDIT.md`](SECURITY-AUDIT.md) - security review, fixes and production checklist

## Versioning
Bump `DCK_ELITE_VERSION` in `dck-elite-pro/functions.php` **and** `Version:` in `dck-elite-pro/style.css` together (this also busts browser caches). Plugin: `DCK_CORE_VERSION` + header in `dck-core/dck-core.php`.


## Two-factor login (required for staff)
1. Plugins > Add New > search "Two Factor" (by WordPress.org contributors) > Install > Activate.
2. Every user who can edit content is taken to Users > Profile until they enable it: tick **Authenticator App** and scan the QR code, generate **Recovery Codes** and store them safely, set Primary Method to Authenticator App, Update Profile.
3. To switch enforcement off temporarily: `define( 'DCK_REQUIRE_2FA', false );` in wp-config.php.
4. Locked out (lost phone and recovery codes)? Another admin can disable two-factor on that user's profile, or deactivate the plugin by renaming its folder over FTP.
