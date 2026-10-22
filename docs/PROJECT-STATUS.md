# Project status and handoff (read after AGENTS.md)

Last updated: 2026-10-22 (Thu), by Ray (Town assistant). Update this file at the end of every task.

## 1. Where things stand

| Item | Version / state |
|---|---|
| Theme `dck-elite-pro/` on `main` | **4.33.0** (PR #2 merged 2026-10-22: Plan Your Visit link + Google opening hours in schema) |
| Plugin `dck-core/` on `main` | **1.10.0** (adds `dck_seo_opening_hours` field) |
| Theme / plugin installed on the live site | **4.33.0 / 1.10.0**, installed by the church via cPanel on 2026-10-22 and verified live |
| Live SEO plugin | Rank Math (free), configured 2026-10-20 (see section 3) |
| Live robots.txt | `docs/seo/robots.txt` (identical to live, verified 2026-10-22). Allows search AND AI training crawlers (church decision 2026-10-21) |
| Live llms.txt | `docs/seo/llms.txt` (identical to live, verified 2026-10-22) |
| Hosting | cPanel, behind Cloudflare (free plan). WordPress zip uploader fails on this server: install themes/plugins via cPanel File Manager |
| Google Business Profile | "Deliverance church Kabete N Market at Waiyaki way, Nairobi", managed by deliverancechurchn24@gmail.com. Hours and pin updated by the church 2026-10-22; **GBP is the source of truth for opening hours and location** |
| Analytics | GA4 `G-MMV7163YMC` (property 559955222). Loaded once per page |

## 2. Code work done (newest first; details in AGENTS.md changelog)
- **4.33.0 / Core 1.10.0 (2026-10-22)**
  - `template-parts/join-us.php`: "Plan Your Visit" button now uses `dck_elite_page_url( 'plan-your-visit' )`, same tab (it used to open the map). Used on About, Ministries, Events, Contact, Terms. Verified live.
  - `inc/seo.php` `dck_seo_hours()`: `openingHoursSpecification` now comes from DCK Church > Church & Contact > **Opening hours (Google)** (`dck_seo_opening_hours`, one line per day `Day|HH:MM|HH:MM`, 24-hour). Default = GBP hours. Blank or invalid falls back to the old Gatherings-based hours. Verified live on the homepage graph.
  - Opening hours (must match GBP): Mon-Wed 09:00-18:00, Thu 09:00-19:00, Fri-Sat 09:00-18:00, Sun 07:00-18:00. **Service times are separate** and stay on Services/FAQ/Gatherings.
  - `php -l` was NOT run (no PHP in the assistant sandbox); the live site renders without errors.
- **4.31.0 / Core 1.9.0** FAQ editable in admin (`dck_faq_items`, `Group|Question|Answer`, tokens %PHONE% %EMAIL% %SERVICES% %HOME%).
- **4.30.0** SEO single source of truth: Rank Math owns title/description/canonical/robots/OG/sitemap; theme outputs ONE JSON-LD `@graph` and strips Rank Math's graph. Report: `docs/seo/SEO-AUDIT-2026.md`.
- **4.29.0 and earlier**: see AGENTS.md.

## 3. Live-site work done outside the repo (wp-admin, Cloudflare, Google)
### 2026-10-22
- **5 new SEO blog posts published** (by the assistant via wp-admin; content source `DCK SEO Growth Plan and Ready Posts.docx` in the Town library). Verified live: 1 title, 1 description, 1 canonical, 1 JSON-LD graph with BlogPosting, 1 H1, featured image, in `post-sitemap.xml`:
  - /sunday-service-in-uthiru-what-to-expect/ (focus "Sunday service in Uthiru")
  - /church-near-upper-kabete-campus-students/ (focus "church near Upper Kabete campus")
  - /what-is-a-kesha-overnight-prayer/ (focus "Kesha overnight prayer")
  - /directions-to-deliverance-church-kabete-n-market/ (matatu stops and parking left generic until the church confirms details)
  - /ibada-ya-jumapili-uthiru-kabete/ (Kiswahili, focus "kanisa Uthiru"; a fluent speaker should proofread)
  - Posts 1 and 5 use the website's Sunday times; update them if leadership confirms different times. Next topics list is in the growth-plan doc.
  - The church temporarily disabled the wp-login CAPTCHA for this; it must be re-enabled.
- **Opening hours** set to the GBP hours in the theme schema (code default) and in `llms.txt` (new "Opening hours" section, service times kept separate).
- Theme 4.33.0 + DCK Core 1.10.0 uploaded by the church; old folders kept as `dck-elite-pro-old` / `dck-core-old` in cPanel (can be deleted once stable).
- Church guide for remaining content tasks: `DCK Remaining Tasks Guide.docx` (Town library, DCK Website), also in `docs/handover/`.

### 2026-10-21
- **Full live audit of the 63 sitemap URLs**: titles, descriptions, one H1, canonical, OG/Twitter, one JSON-LD graph per page, GA tag once, GSC + Bing verification tags on home, no noindex, no images without alt, redirects and 404s OK.
- **Search engines**: site verified in Google Search Console and Bing Webmaster Tools; Rank Math account connected (Sign in with Google).
- **GA4 click tracking**: WPCode snippet ID **653** "GA4 click tracking" (Site Wide Footer). Events: `phone_call`, `whatsapp_click`, `directions_click` (regex includes maps links and `share.google`), `plan_visit_click`, `live_click`, `give_click`. Key events: phone_call, whatsapp_click, directions_click, plan_visit_click (verified in Realtime).
- **robots.txt / llms.txt** uploaded to `public_html`. Cloudflare: AI "Training" set to Allow and Bot Preference Sync turned off, so Cloudflare no longer injects disallow lines (verified).
- **Cloudflare redirect rule**: Hostname equals `www.dckabeten.org` -> 301 to `https://dckabeten.org` preserving path and query (verified single hop).
- **Contact page**: added "To speak with Pastor Justus Kubai, call or WhatsApp +254 110 304521. He has access to this number."
- **FAQ** (DCK Church FAQ field): 23 questions incl. "How do I talk to Pastor Justus Kubai?"; FAQPage schema has 23 items (verified). Note: the assistant's browser tool refuses to paste text containing Paybill/Till/bank numbers; the church pasted it manually.
- **Google Business Profile**: management request accepted; deliverancechurchn24@gmail.com is a manager.

### 2026-10-20
- Rank Math titles + descriptions on all 63 URLs (source: `docs/seo/seo-meta.json`), Local SEO / Knowledge Graph (Organization, alternate name "Cistern of Hope Center"), sitemap `/sitemap_index.xml`, tag archives noindex, author/date archives off, 8 curated post tags, Auto ALT format `%title% - Deliverance Church Kabete`. Editor account `ray-assistant` exists (never store passwords in the repo).
- Note: Rank Math Local SEO hours still show the old service-style hours (Sun 08:00-13:00, Tue/Thu 18:00-19:00). They are not output (the theme strips Rank Math schema), so this is cosmetic; update to the GBP hours if anyone touches that screen.

## 4. Pending - next agent picks up here (in priority order)
1. **Confirm Sunday service times with leadership.** Website/FAQ: Prayers 08:00-09:00, Bible Study 09:30-10:30, Glorious Service 10:40-13:00. The old GBP description says 10:00 and 11:00. Do not change either until the church confirms; then make Gatherings, Services page, FAQ, llms.txt and GBP all match.
2. **Rewrite the GBP description** (still mentions Wednesday prayers, typos "vistation", "Aunthentic", no name/location). A 618-character suggested text is in the Remaining Tasks Guide; the church edits it themselves (needs their approval).
3. **Social profile URLs** (Facebook, YouTube, Instagram, optional TikTok/X) into DCK Church > Social Media, YouTube channel ID (UC...) in the Live tab. These feed `sameAs`. Also add the same profiles to GBP. Waiting on the church.
4. **Sermons**: YouTube link, scripture reference and 150-300 word summary on each sermon (Sermons > Add New > Sermon Details). Waiting on the church for links/content. Do not invent sermon content.
5. **Content confirmations**: 2020 church-history dates, exact leaders' titles (Lead: Justus & Clare Kubai; Assistant: Mr. & Mrs. Henry Onzere), Terms page wording. Waiting on the church.
6. **GBP pin / geo**: GBP pin is now about -1.2680913, 36.7298952 (address on GBP still shows "Waiyaki Wy, Nairobi" while the edit is reviewed). Optional: expose `dck_seo_lat` / `dck_seo_lng` in DCK Core settings and set them from the GBP pin (currently not output).
7. **Verification**: Google Rich Results Test on Home, FAQ, a sermon and an event; fix any errors.
8. **Not started**: Kenya keyword/competitor research (church in Uthiru / Kabete / Kangemi / Waiyaki Way), 30/60/90-day SEO plan and reporting (GSC clusters + GA4 key events), weekly GBP posts and review collection.
9. Housekeeping: delete `dck-elite-pro-old` / `dck-core-old` from cPanel after a week without issues; branch `fix/plan-your-visit-link` can be deleted.

## 5. Working method
- Theme changes: bump `style.css` + `functions.php` version, add an AGENTS.md changelog line, lint PHP (`php -l`) and JS (`node --check`) where PHP is available.
- Ship: build zips of `dck-elite-pro/` and `dck-core/`, save to the Town library (DCK Website), church installs via cPanel File Manager (rename old folder to `-old`, upload, extract), then purge WordPress cache (Performance > Purge All Caches) and Cloudflare cache. Verify live with curl / view-source.
- wp-admin sessions log out often and need a human CAPTCHA.
- Never put secrets or passwords in the repo. Record live wp-admin / Cloudflare / Google changes in section 3 of this file.
