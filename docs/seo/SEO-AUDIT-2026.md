# SEO / GEO / AEO audit - Deliverance Church Kabete N Market (20 Oct 2026)

Branch `seo-geo-aeo-audit-2026`. No production deployment. Evidence: live fetches of https://dckabeten.org on 20 Oct 2026, plus a local WordPress (SQLite) running this branch.

## 1. Findings (live site, verified)
| Sev | Finding | Evidence | Fix in this PR / action |
|---|---|---|---|
| Critical | Physical `public_html/robots.txt` points at a **/test/** install: `Sitemap: https://dckabeten.org/test/wp-sitemap.xml`, `Disallow: /test/wp-admin/` | live /robots.txt | Corrected file in `docs/seo/robots.txt` - **upload manually** (a physical file overrides WordPress) |
| High | Two SEO systems on every page: Rank Math + theme -> **2 canonicals, 2 meta descriptions, 2 JSON-LD graphs** (Rank Math labels the church `Person, Organization` with a title-style name) | live homepage HTML | Theme now detects Rank Math/Yoast: plugin owns title/description/canonical/robots/OG/sitemap; theme owns the **single** JSON-LD graph and removes the plugin graph (`rank_math/json_ld`) |
| High | Two sitemap systems (core `wp-sitemap.xml` forced on -> 403 behind Cloudflare; Rank Math `sitemap_index.xml` live) | live fetch | Core sitemap only when no SEO plugin; robots points to `sitemap_index.xml` |
| High | Church schema had a **hard-coded Sunday 08:00-13:00** only | inc/seo.php | Hours now built from DCK Church > Gatherings (Sunday + weekly; monthly Keshas skipped) |
| Medium | `sameAs` could emit placeholder `facebook.com/` / `youtube.com/` | inc/home.php defaults | Generic root URLs are dropped; IG/TikTok/X included when real |
| Medium | VideoObject used the YouTube **watch** URL as both `embedUrl` and `contentUrl` | inc/seo-schema.php | YouTube ID -> `embedUrl` `/embed/ID`, real thumbnail, no third-party `contentUrl`; no video -> `Article` |
| Medium | Event schema emitted for undated/recurring events | inc/seo-schema.php | Only dated one-off events; organizer linked to church `@id` |
| Medium | `SearchAction` (sitelinks search box retired by Google) while `/?s=` is disallowed | inc/seo.php | Removed |
| Low | `meta keywords` output (ignored by Google/Bing) | inc/seo.php | Removed |
| Low | Separate unlinked JSON-LD scripts (Church, WebSite, Breadcrumb, Article...) | multiple | One `@graph`: `#church`, `#website`, `{url}#webpage` (+ `#breadcrumb`, `#video`/`#article`/`#event`), all cross-referenced |
| OK | City doorway pages (Nairobi/Nakuru/Mombasa/Eldoret) | `/church-in-nairobi/` -> 404 | Already retired; nothing re-creates them. `/looking-for-a-church-in-nairobi/` blog post is genuine local content - keep |
| Decision | Cloudflare-managed robots + the physical file block AI **training** bots (GPTBot, ClaudeBot, Google-Extended...). AI **search** bots (OAI-SearchBot, PerplexityBot, Claude-SearchBot) are allowed | live robots | Kept training blocked; search bots explicitly allowed in new file |

## 2. Rendered checks (local WordPress, this branch)
Home, About, Services, Contact, FAQ, Give, Sermons, a sermon, an event, a blog post: 1 canonical, 1 description, 1 JSON-LD graph (FAQ page +1 FAQPage from the visible Q&As), valid JSON, no duplicate `@id`, no PHP errors. All PHP files lint clean.

Titles (used when no SEO plugin; mirror them in Rank Math): Home "Deliverance Church Kabete N Market | Church in Uthiru, Kabete, Nairobi"; Services "Sunday Service Times in Uthiru, Kabete | ..."; Contact "Contact & Directions | ..., Behind N Market Uthiru".

## 3. Intent map (hypotheses until Search Console confirms)
Brand (DCK Kabete, Cistern of Hope Center) -> Home | Service times / Sunday service Kabete/Uthiru -> Services | Directions / church near N Market -> Contact | First visit -> Plan Your Visit + I'm New | Ministries -> Ministries | Sermons online -> Sermon archive + sermons | Live service -> Watch Live | Prayer -> Prayer Requests | Giving -> Give | National reach -> sermons, live, Bible, blog (no city pages).

## 4. Manual actions (need church access)
1. Replace `public_html/robots.txt` with `docs/seo/robots.txt`.
2. Rank Math: set the titles/descriptions above; Titles & Meta > Local SEO: type Church, name "Deliverance Church Kabete N Market", logo; Sitemap on; Schema can stay on (theme removes its output).
3. Google Business Profile: claim/verify, category Church, address "Behind N Market, Uthiru", phone, website, real hours, current photos, weekly posts; ask members for honest reviews (no incentives).
4. Search Console + Bing Webmaster: verify, submit `sitemap_index.xml`, inspect Home/Services/Contact.
5. DCK Church settings: real Facebook/YouTube channel URLs (not homepages); optional latitude/longitude (`dck_seo_lat`/`dck_seo_lng`) only from the verified GBP pin.
6. Sermons: add YouTube link, scripture, 150-300 word summary per sermon (transcripts where approved).
7. Confirm with leadership: service times, history dates (2017 / 2020), leader titles.

## 5. 30/60/90
30: robots fix, GSC/Bing/GBP verified, Rank Math aligned, 10 sermons with summaries. 60: GBP weekly posts, ministry pages expanded, first reviews, events with real dates. 90: compare GSC clusters (brand/local/service-time/sermon), monthly AI-answer spot checks (save date, prompt, sources). No ranking guarantees.

## 6. Rollback
Revert this PR (theme only). Settings and content are untouched. To keep plugin schema instead: `add_filter( 'dck_keep_plugin_schema', '__return_true' );`.
