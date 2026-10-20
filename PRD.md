# Product Requirements - dckabeten.org

## Goal
A professional, international-standard church website that helps people **visit, watch, read the Bible, give, pray and connect** with Deliverance Church Kabete N Market, and that church staff can **update without a developer**.

## Users
1. **Visitors / first-timers** (Nairobi and diaspora) - where, when, what to expect.
2. **Members** - sermons, live stream, giving, events, prayer.
3. **Global audience** - watch online, Bible in their language, time-zone aware.
4. **Church staff / media team** - edit content, upload sermons, photos, events; read prayer requests and messages.

## Non-functional requirements
- Mobile-first, accessible (keyboard, focus states, contrast, reduced motion), fast (WebP, self-hosted fonts).
- Everything editable in **wp-admin > DCK Church**; blank fields fall back to sensible defaults.
- Secure by default (see SECURITY-AUDIT.md); personal data never public; no secrets in code.
- English primary; Kiswahili support (Google Translate widget, Swahili AI replies, Kiswahili Bibles).
- Times shown in EAT (UTC+3) with visitor-local conversion where useful.

## Pages & key requirements
| Page | Must have |
|---|---|
| Home | Hero slider, welcome, live stats, ministries, Sunday times, CTAs |
| About | Story (dates 9/11/18 June 2017), mission, values, milestones, leadership |
| Our Pastors | Couple hero, profiles with full bios (dialog), focus areas, credentials, foundation, pastoral care |
| Services | Next gathering countdown, Sunday photo cards, weekly calendar, what to expect, map. Managed in **Gatherings** |
| Ministries | 16 departments, search + filters, "I want to join" WhatsApp |
| Events | Next event + countdown, list with add-to-calendar, weekly calendar, notices |
| Watch Live | Live status, countdown, time-zone converter, calendar invites, past messages |
| Sermons | Featured latest, AI search, filters, sort, grid/list, series rail, working images |
| Bible | 17 versions, compare, navigator, verse actions (highlight/save/image/share/ask), AI study assistant |
| Gallery | Mosaic, albums, lightbox with share/download |
| Blog | Search, categories, featured, read time, share |
| Give | Tabs Paybill/Till/Bank/Abroad, copy buttons, safety tips, FAQ |
| Prayer | Topic chips, confidential switch, what happens next, urgent help |
| Contact | Quick contact cards, form with topics, map, local time |
| FAQ | Search, topics, expand all |
| Terms & Privacy | Editable sections, Kenya DPA 2019 aware, photo opt-out with signature |

## Success measures
Visits to Services/Live, form submissions, Bible reader usage, giving page views, staff able to update content without help.

## Out of scope (for now)
User accounts for members, online card payments, multi-site, mobile app.
