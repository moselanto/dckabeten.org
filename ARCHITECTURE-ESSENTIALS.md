# Architecture essentials (one page)

```
Browser ──HTTPS──> Cloudflare (optional) ──> WordPress (PHP 7.4+, MySQL)
                                              ├── Theme  dck-elite-pro  (templates, inc/*.php, assets/js, style.css)
                                              └── Plugin dck-core        (DCK Church settings UI, Sermon CPT)
WordPress ──server-side HTTPS──> AI provider (Gemini/OpenAI/Groq) · bible.helloao.org · YouTube/Maps (embeds)
```

- **Storage:** WordPress posts/meta (sermons, events, photos, prayers, messages, opt-outs) + theme mods (all editable settings) + transients (cache, rate limits).
- **Settings flow:** DCK Church screen -> `set_theme_mod()` -> templates read with `dck_home_val()` -> defaults in code if blank.
- **Front-end:** server-rendered PHP templates; small vanilla JS files per page (no frameworks, no build).
- **API:** custom REST namespace `dck/v1` (forms, AI, gallery, scripture proxy). Public endpoints rate-limited; staff endpoints capability-checked.
- **Security layer:** `inc/security.php` + `inc/hardening.php` (headers, HTTPS, CORS, login limits, IP detection, upload checks).
- **Secrets:** `wp-config.php` constants only.
- **Third-party JS:** Google Translate widget, YouTube/Facebook/Maps iframes. No tracking scripts in the theme.
