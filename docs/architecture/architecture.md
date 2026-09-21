# Architecture — Al Qasim Movers

Status: **Approved for Stage 1** (implementation details finalised in Stage 4)
Owner: project-manager, frontend-developer

## 1. Goals that drive the architecture

| Goal | Architectural consequence |
|---|---|
| Local SEO + organic traffic | Fully server-rendered static HTML; clean trailing-slash URLs; build-time metadata, canonicals, schema, sitemap |
| Lead generation | Header/sticky CTAs in shared partials; a form system with a real backend handler |
| Fast loading (LCP < 2.5s, CLS < 0.1, INP < 200ms) | No framework, minimal JS, optimised images, self-hosted fonts, static hosting on a CDN |
| Consistency & accuracy | One business config (`config/business.json`) injected at build time |
| Maintainability | Reusable partials, design tokens, documented decisions |

## 2. Repository structure

```
alqasim-movers/                      (repo root — NOT deployed)
├── CLAUDE.md                        Claude entry point
├── PROJECT_MEMORY.md                Stage-by-stage memory
├── CHANGELOG.md                     Dated change log
├── README.md
├── .claude/
│   ├── agents/                      20 specialised agents
│   ├── skills/                      17 skills (SKILL.md each)
│   └── rules/                       development, seo, security, design, content-integrity, accessibility
├── config/
│   └── business.json                Single source of business truth (verified flags)
├── docs/
│   ├── project-brief.md
│   ├── roadmap.md
│   ├── owner-questions.md
│   ├── research/                    business, competitor, owner facts
│   ├── seo/                         strategy, keywords, topical map, links, local, content, schema, briefs/
│   ├── design/                      design system, wireframes, image register
│   ├── content/                     approved page copy drafts
│   ├── architecture/                this file, workflow, quality gates, security, performance, QA, deployment
│   ├── audits/                      audit reports
│   └── changelog/                   pointer to root CHANGELOG.md
├── src/                             Website source (Stage 4+)
│   ├── partials/                    header, footer, breadcrumbs, cta, review-card, head-meta, sticky-cta
│   ├── pages/en/, pages/ar/         Folder per public URL (see §4, ADR-006)
│   ├── i18n/                        en.json, ar.json — UI strings for partials
│   ├── css/                         reset, variables, base, layout, components, utilities
│   ├── js/                          main, navigation, forms, analytics
│   ├── images/                      logo, hero, services, projects, team, areas, icons
│   ├── fonts/
│   └── static/                      robots.txt template, favicon set, site.webmanifest, _headers/_redirects
│   ├── php/                         quote.php, lib.php, config.sample.php → dist/form/
├── db/                              schema.sql (MySQL leads table)
├── tests/                           lib-test.php (PHP helper tests)
├── scripts/                         build.mjs, qa-crawl.mjs, secret-scan.mjs (zero dependencies)
└── dist/                            Build output = web root (git-ignored)
```

## 3. Architecture Decision Records

### ADR-001 — Static source + zero-dependency build step (deviation from brief §37)
- **Context:** The brief suggests `pages/`, `components/`, and `public/` folders and plain HTML. Two problems:
  1. A `pages/` folder served as-is yields URLs like `/pages/services/...`, contradicting the required `/services/villa-movers-dubai/` URLs.
  2. Plain HTML with 30–40 pages means copying the header/footer into every file (drift risk), or including them with client-side JS (crawlers and no-JS users get incomplete pages; hurts LCP/CLS).
- **Decision:** Author in `src/`; a small Node script (`scripts/build.mjs`, built-in modules only, no npm dependencies) assembles partials, injects `config/business.json` values, generates per-page meta/canonical/JSON-LD, builds `sitemap.xml`, and writes `dist/` with the exact public URL tree (`dist/services/villa-movers-dubai/index.html`).
- **Consequences:** Output is pure static HTML/CSS/JS (brief's technology requirement met). Node (v24 available locally) is a dev-time tool only. The brief's `public/`, `components/` map to `src/static/`, `src/partials/`.
- **Status:** Accepted in principle; build script specified and implemented in Stage 4.

### ADR-002 — Business facts in one config with verification flags
- **Decision:** `config/business.json` stores each fact with `verified` and `source`. Build refuses to render unverified facts; templates use tokens like `{{business.phone.international}}`.
- **Why:** Enforces NAP consistency and the no-fabrication rule mechanically, not just by policy.

### ADR-003 — No CSS/JS framework
- **Decision:** Hand-written CSS with design tokens and vanilla ES modules.
- **Why:** Brief requirement; smallest payload; nothing to keep patched.

### ADR-004 — Hosting: Hostinger shared hosting (decided 2026-09-20; supersedes the Netlify decision made earlier the same day)
- **Context:** The owner already has a Hostinger unlimited plan with PHP and MySQL. Paying for or managing a second host would be waste, and Hostinger covers everything this site needs.
- **Decision: deploy to Hostinger.** The build still produces plain static files; Apache serves them, `.htaccess` provides headers, redirects and caching, PHP handles the forms and MySQL stores the leads.
- **Consequences:**
  - `_headers` / `_redirects` (Netlify formats) are replaced by `src/static/.htaccess`.
  - There is no build step on the server: `node scripts/build.mjs` runs locally and `dist/` is uploaded.
  - PHP is available, so form handling no longer depends on a third-party service.
  - Free SSL and a CDN-less but adequate shared-hosting stack; performance work therefore matters more (see performance-strategy).
- **Rejected:** Netlify (an unnecessary second host), Cloudflare Pages (same), keeping forms on a third-party service while hosting elsewhere (needless split).

### ADR-005 — Forms: PHP handler + MySQL on Hostinger (decided 2026-09-20)
- **Decision:** `src/php/quote.php` (deployed to `/form/quote.php`) validates the submission, stores it in a MySQL `leads` table via **prepared statements**, emails the owner, and redirects to the language-correct thank-you page.
- **Why store in a database as well as email:** email can silently fail or land in spam, and a lost lead is lost money. The database is the record of truth; email is the notification. The owner can read leads in phpMyAdmin, and `leads_this_month` is a ready-made view.
- **Credentials:** kept in a config file **above `public_html`** (`/home/<user>/private/alqasim-config.php`), never in the repository. `src/php/config.sample.php` documents the shape with no real values. The handler also accepts a `QASIM_CONFIG` environment path.
- **Anti-abuse:** honeypot field, a JavaScript-set timestamp (submissions faster than 3s are treated as bots), and a per-IP rate limit of 5 submissions per 10 minutes. IPs are stored only as a salted SHA-256 hash.
- **Validation:** name and phone required; UAE phone formats including Arabic-Indic digits; allow-lists for property type and services; length caps on every field; control characters (including CR/LF) stripped, which also closes mail-header injection. 26 unit tests cover this (`tests/lib-test.php`).
- **Privacy:** the Privacy Policy (Stage 8) must state that enquiries are stored in the company's own database on Hostinger and emailed to the owner.
- **Rejected:** PHPMailer/SMTP (adds a dependency and a password in config for no launch-day gain — revisit if `mail()` deliverability disappoints), a web admin panel for leads (an auth surface we do not need yet; phpMyAdmin is enough).

### ADR-006 — Bilingual site: English (default) + Arabic under `/ar/`
- **Context:** Owner decision 2026-09-19: the site is dual-language, Arabic and English.
- **Options considered:** (a) subfolder `/ar/`; (b) subdomain `ar.alqasimmovers.com`; (c) separate ccTLD/domain; (d) client-side language toggle on one URL.
- **Decision:** (a) subfolder. It keeps all authority on one host, is simple to host, and is fully supported by Google. Option (d) is rejected: search engines need a separate URL per language.
- **Slugs:** Arabic pages reuse the English slug (`/ar/services/villa-movers-dubai/`). *Reason:* Arabic-script slugs become long percent-encoded strings when shared, complicate hreflang mapping and QA, and give negligible ranking benefit. Titles, H1s and content carry the Arabic relevance.
- **Build impact:** Shared partials read UI strings from `src/i18n/en.json` / `src/i18n/ar.json`. Page sources live in `src/pages/en/...` and `src/pages/ar/...`; the build maps `en` to the root and `ar` to `/ar/`, emits `lang`/`dir`, hreflang pairs and a bilingual sitemap, and **fails if an hreflang pair is broken**.
- **Consequences:** Page count roughly doubles (about 25–40 unique pages × 2). An Arabic page ships only when its Arabic content is written and reviewed. English may launch first for a page, but no untranslated page appears under `/ar/`. CSS uses logical properties (RTL-safe). An Arabic web font adds page weight, so the performance budget accounts for it.
- **Rules:** `.claude/rules/bilingual-rules.md`.

## 4. URL architecture

Arabic mirrors every row below under `/ar/` (e.g. `/ar/`, `/ar/services/villa-movers-dubai/`). `/404.html` serves both languages (bilingual content).


| Page | URL | Source |
|---|---|---|
| Home | `/` | `src/pages/index.html` |
| About | `/about/` | `src/pages/about/index.html` |
| Services hub | `/services/` | `src/pages/services/index.html` |
| Service page | `/services/<service>-dubai/` | `src/pages/services/<slug>/index.html` |
| Areas hub | `/areas/` | `src/pages/areas/index.html` |
| Area page | `/areas/<area>/` (confirmed Stage 2) | `src/pages/areas/<slug>/index.html` |
| Projects | `/projects/` | |
| Reviews | `/reviews/` | |
| FAQ | `/faq/` | |
| Blog | `/blog/`, `/blog/<post-slug>/` | |
| Get a Quote | `/get-a-quote/` | |
| Quote thank-you | `/get-a-quote/thank-you/` (noindex) | |
| Contact | `/contact/` | |
| Legal | `/privacy-policy/`, `/terms-and-conditions/`, `/cookie-policy/` | |
| 404 | `/404.html` (noindex, HTTP 404) | |

Rules: lowercase, hyphenated, trailing slash, no IDs/query strings, no `.html` in links.

## 5. CSS architecture

`reset.css → variables.css → base.css → layout.css → components.css → utilities.css` (plus `responsive` concerns handled mobile-first inside each file rather than a separate `responsive.css`, so a component's styles live in one place — deviation from brief §37, documented here). Concatenated and minified into one stylesheet at build; critical CSS inlining evaluated in Stage 10.

## 6. JS architecture

ES modules, loaded with `type="module"` (implicitly deferred): `main.js` (bootstraps), `navigation.js` (mobile menu), `forms.js` (validation/enhancement), `analytics.js` (consent-aware events). Every feature is progressive enhancement.

## 7. Dependency Register

| Dependency | Purpose | Size | Licence | Alternatives considered | Approved |
|---|---|---|---|---|---|
| *(none)* | | | | | |

Add a row before introducing any library, font service, or third-party script.

## 8. Build contract (implemented 2026-09-20)

`node scripts/build.mjs` — zero dependencies, Node built-ins only.

**Input:** `src/pages/<lang>/<route>/index.html`, each starting with a page-meta block:
```html
<!--page
{ "title": "…", "description": "…", "type": "home|service|area|page|post",
  "navTitle": "…", "serviceName": "…", "index": true,
  "breadcrumbs": [{ "name": "Services", "path": "services" }] }
-->
```
**Output:** `dist/<route>/index.html` (English at the root, Arabic under `/ar/`), plus `sitemap.xml`, `robots.txt`, one minified `css/site.css`, JS modules, images, fonts and the host files `_headers` / `_redirects`.

**Tokens available in pages and partials**
| Token | Meaning |
|---|---|
| `{{business.*}}` | a fact from `config/business.json` — **build fails if it is not `verified: true`** |
| `{{t.*}}` | UI string from `src/i18n/<lang>.json` |
| `{{url:quote}}`, `{{url:services}}`, … | language-correct internal URL |
| `{{page.*}}` | a value from the page-meta block |
| `{{altUrl}}`, `{{altLang}}`, `{{lang}}`, `{{dir}}`, `{{year}}` | page scalars |
| `{{nav:services}}`, `{{nav:areas}}` | footer lists, generated from the pages that actually exist |
| `{{> header}}`, `{{> quote-form}}`, … | partial from `src/partials/` (works in layout **and** page bodies) |

**The build refuses to finish when:** a business fact is unverified or empty, a page has no meta block, a title or description is missing, a page has ≠ 1 `<h1>`, two pages in one language share a title, `[[OWNER-INPUT` or lorem ipsum reaches the output, or an Arabic route has no English counterpart. (Each of these was tested by deliberately breaking a page on 2026-09-20.)

**Also shipped into `dist/`:** `.htaccess` (headers, HTTPS and non-www redirects, trailing slash, `ErrorDocument 404`, compression, dotfile blocking) and `form/quote.php` + `form/lib.php`. The sample config is deliberately excluded from the output.

**Companion scripts:** `qa-crawl.mjs` (links, assets, metadata, hreflang reciprocity, sitemap/robots sanity, CSS token discipline, page weight; `--allow-missing` while pages are still being written) · `secret-scan.mjs` · `serve.mjs` (local preview with real 404s).

### ADR-007 — Live content: PHP pages + SQLite + admin dashboard (decided 2026-09-21)
**Context:** the owner wants customers to post reviews that appear immediately, a projects
gallery, and an admin dashboard for uploading project photos and videos.
**Decision:** pages that show live data (`/reviews/`, `/projects/` and their Arabic twins) are
built as `index.php` (page meta `"php": true`) and include server-side views from `/form/views/`.
Data lives in SQLite at `~/domains/alqasimmovers.com/private/alqasim.sqlite` (above public_html;
MySQL is still supported via the config's `db.driver`). `bootstrap.php` migrates the tables
automatically. The admin is a single PHP file at `/admin/` using a one-time setup key, password_hash,
login throttling, CSRF, a strict session cookie, and a 2h idle timeout. Images are re-encoded to WebP
with GD; videos are checked against a MIME allowlist; `/uploads/` can never run code; and uploads are
git-ignored on the deploy branch so deploys never delete them.
**Reviews:** they publish instantly, as the owner asked. There is a honeypot, a timing check,
3 reviews per IP per 24h, and links are rejected. The phone number is required but never shown.
The admin can hide or delete reviews.
**Tests:** `tests/e2e-local.sh` (29 checks).
