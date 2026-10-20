# Project status and handoff (read after AGENTS.md)

Last updated: 2026-10-20 (Tue), by Ray (Town assistant). Update this file at the end of every task.

## 1. Where things stand

| Item | Version / state |
|---|---|
| Theme `dck-elite-pro/` on `main` | **4.30.0** (PR #1 merged 2026-10-20, merge commit d20f9df) |
| Plugin `dck-core/` on `main` | **1.8.0** |
| Theme installed on the live site | **older than 4.29.0** (live I'm New page still shows the old "How did you hear about us?" dropdown). 4.29.0 and 4.30.0 are NOT live yet |
| Live SEO plugin | Rank Math (free), fully configured 2026-10-20 (see section 3) |
| Live robots.txt | Replaced by Agnes with `docs/seo/robots.txt`; **not yet re-verified** from outside |
| Hosting | cPanel, behind Cloudflare. WordPress zip uploader fails on this server |

## 2. Code work done (summary, newest first; details in AGENTS.md changelog)
- **4.30.0** SEO single source of truth: with Rank Math/Yoast active the plugin owns title, description, canonical, robots, OG and sitemap; the theme outputs ONE JSON-LD `@graph` (#church, #website, {url}#webpage, breadcrumb, video/article/event, blog posts) and strips the plugin graph via `rank_math/json_ld` and `wpseo_json_ld_output`. Hours from Gatherings, no placeholder sameAs, YouTube VideoObject embedUrl, Event only for dated events, SearchAction and meta keywords removed, AI search crawlers allowed in robots. Report: `docs/seo/SEO-AUDIT-2026.md`.
- **4.29.0** full local verification (24 pages, 10 REST forms, 17 admin screens, 2 Excel exports); translations-too-early notice fixed; I'm New "How did you hear" = 12 radio cards + follow-up detail.
- **4.17-4.28 / Core 1.1-1.8**: speed pass, mobile app navigation, PWA, church tools (giving, visitors, RSVP, pastoral care, fellowships, volunteers), Paystack M-Pesa STK, Member Register with Excel round trip, security audit.

## 3. Live-site work done in WordPress admin (not in the repo)
Done through the live wp-admin (user `moses`) with Rank Math, 2026-10-20:
- **Rank Math SEO title + meta description** set on all 63 public URLs: 19 core pages, `/blog/`, 22 posts, `/sermons/` archive, 6 sermons, 8 events, 6 categories. Posts, sermons, events and categories also have a focus keyword (the sermon archive has no focus-keyword field). Source of truth for the wording: `docs/seo/seo-meta.json` (path, title, description). Rules used: titles <= 60 chars, descriptions 110-160 chars, every core page names Uthiru / Kabete / N Market.
- **Local SEO / Knowledge Graph**: Organization (free Rank Math has no Church type), name "Deliverance Church Kabete N Market", alternate "Cistern of Hope Center", logo `cropped-finallogo.png`, URL, info@dckabeten.org, +254110304521, address Behind N Market, Uthiru, Kabete, Nairobi 00605 KE, hours Sun 08:00-13:00, Tue 18:00-19:00, Thu 18:00-19:00. Local SEO, Image SEO and Instant Indexing modules on.
- Pages default schema = None (theme provides the graph). OG fallback image = church logo. Sitemap includes featured images (`/sitemap_index.xml` is the only sitemap; core `wp-sitemap.xml` is 403).
- Tag archives noindex,follow; author archives and date archives disabled; search results noindex.
- Auto ALT/TITLE for images: format `%title% - Deliverance Church Kabete`.
- **Post tags** (8 curated, each on 3-6 posts): Prayer Life, Bible Study, Marriage & Family, Christian Living, Hope & Encouragement, Youth, New Believers, Church in Nairobi. Do not create one-off tags.
- "Sample Page" moved to Trash.
- Earlier: a dedicated Editor account `ray-assistant` was created for the assistant (never store its password in the repo or chat).

## 4. Pending - next agent picks up here (in priority order)
1. **Test 4.30.0 with Rank Math installed** on the local test WordPress (`wp plugin install seo-by-rank-math --activate`), configure it like section 3, and confirm each page outputs exactly 1 canonical, 1 description, 1 JSON-LD graph, and no Rank Math `@graph`. This path was never exercised. Fix and bump to 4.30.1 if needed.
2. **Build and hand over the theme zip** (4.30.x) to `content://collections/DCK Website/`. Install steps for the church: cPanel File Manager, rename `wp-content/themes/dck-elite-pro` to `dck-elite-pro-old`, upload + extract zip, purge WordPress cache and Cloudflare cache. Also upload `dck-core` 1.8.0 the same way if the live plugin is older.
3. **After install, verify live** (view-source or curl): one canonical/description/JSON-LD per page on Home, Services, Contact, a sermon, an event, a post; Rich Results Test on Home, a sermon and an event; `https://dckabeten.org/robots.txt` contains `Sitemap: https://dckabeten.org/sitemap_index.xml` and no `/test/`.
4. **Search engines**: verify the site in Google Search Console and Bing Webmaster Tools (Bing sign-up was started and stalled), submit `sitemap_index.xml`, request indexing for Home, Services, Contact, Plan Your Visit.
5. **Google Business Profile**: claim/verify "Deliverance Church Kabete N Market", category Church, same name/address/phone/hours as section 3, add photos, start collecting reviews. Biggest lever for "church near me / church in Uthiru" searches.
6. **Content gaps (need church input, do not invent)**: real social URLs in DCK Church settings (enables sameAs); YouTube link + 2-3 sentence summary + scripture reference on every sermon; leadership confirmation of service times and of 2020 history dates (2017 dates are confirmed, see AGENTS.md rule 8).
7. **Content growth for ranking**: one new sermon page and one helpful blog post per week; add internal links from posts to Services, Plan Your Visit and Prayer Requests; Kiswahili versions of key pages later.
8. Smaller open items: Terms wording update (awaiting church confirmation); Gatherings must be saved once in DCK Church settings so hours in schema match.

## 5. Working method that has been used
- Local test site: WordPress + SQLite, `php -S localhost:8099`, WP-CLI, debug.log checked after every page run. Admin login on the local test site only.
- Lint every changed PHP file (`php -l`) and JS (`node --check`); bump `style.css` + `functions.php` versions on every theme change; add an AGENTS.md changelog line.
- Never put secrets or passwords in the repo. Live wp-admin changes are recorded in section 3 of this file.
