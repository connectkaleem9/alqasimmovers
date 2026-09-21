# PROJECT MEMORY — Al Qasim Movers

> Claude: read this file (after `CLAUDE.md`) before continuing any work, especially after an interruption. Update it at the end of every major stage.

## Current Stage
**Stage 5 — Core Website: NEXT.** Stages 1–4 passed (Gate 4 on 2026-09-20).

---

## Analytics + Search Console (2026-09-21)
- GSC HTML-tag verification and the GA4 ID are in `config/business.json` → `integrations` (public IDs, not secrets).
- GA4 is consent-gated (ADR in the Dependency Register). The owner still needs to: click Verify in Search Console, submit `sitemap.xml`, and mark `generate_lead`, `tel_click` and `whatsapp_click` as key events in GA4.
- Still missing: the `/get-a-quote/thank-you/` page, so `generate_lead` can't fire yet. The quote form redirects there.


- /reviews/, /projects/ and /admin/ are live. See ADR-007. SQLite is at `~/domains/alqasimmovers.com/private/alqasim.sqlite`, and the config is next to it (600).
- The owner creates the admin account at /admin/ with the one-time setup key, which is in the server config and was given to the owner in chat. After an account exists, the key is no longer accepted.
- Still waiting on: Facebook and Instagram URLs (top-bar icons stay hidden until `socialProfiles` is verified).

---

## Reviews policy decision (2026-09-20)

Owner asked twice for "dummy reviews" in the testimonials section. Resolution that satisfies the design without breaking the integrity rule:
- `content/reviews.json` holds **real** reviews and is what every normal build renders. Empty → honest note.
- `content/reviews.sample.json` holds placeholders, read **only** when `PREVIEW=1`. That build stamps a dashed "SAMPLE LAYOUT" banner, outlines the cards and prints a build warning.
- So the owner can see the finished card design locally (`PREVIEW=1 node scripts/build.mjs`), and fake testimonials can never reach the live site.
- When the owner supplies real reviews, they go straight into `reviews.json` and the section switches over with no code change.

---

## Design + assets from owner (2026-09-20, during Stage 5)

Owner supplied a full homepage mockup (`design-reference/alqasimhompage.png`), 12 photos and the real logo.
- **Stage 3 design system replaced (v2):** colours sampled from the mockup — orange `#FB7320`, navy `#0B233D`, deep navy `#091731`, WhatsApp green darkened to `#0E7C43` for contrast. Fonts changed to Poppins (Latin) + Tajawal (Arabic), self-hosted, 7–9 KB each.
- **Accessibility deviation, documented:** the mockup's white-on-orange buttons are 2.77:1 (fails AA). We keep the exact orange and use navy labels (5.73:1). If the owner insists on white text, darken the orange to `#C94E06`.
- **Homepage EN + AR rebuilt** to the mockup's section order, with real photos in the 8 service cards, hero, about background and CTA band.
- **Images:** processed with Pillow to AVIF + WebP, multiple widths; 13 MB → 1.5 MB. Naming is SEO-descriptive. Register: `docs/design/image-register.md`.
- **New verified facts from the owner's own files:** email, opening hours, tagline, legal name **Al Qasim Movers L.L.C**.
- **Still outstanding:** the mockup's three testimonials are template samples — not published; asked the owner whether they are real customers.
- Bugs fixed while viewing in a real browser: CSS minifier stripping spaces inside `clamp()` (headings rendered at body size), RTL phone number reversed, Arabic nav wrapping, logo squashed by flexbox, stale `btn--secondary` class.

---

## Hosting change (2026-09-20, after Gate 4)

Owner has a **Hostinger unlimited plan with MySQL**, so the Netlify decision was replaced the same day.
- ADR-004 → Hostinger shared hosting (Apache + PHP + MySQL). ADR-005 → own PHP handler + MySQL `leads` table.
- `.htaccess` replaces the Netlify `_headers`/`_redirects`; build now ships it plus `form/quote.php`, `form/lib.php`.
- Credentials live in `/home/<user>/private/alqasim-config.php` **above public_html** — never in the repo (`config.sample.php` documents the shape).
- Leads are stored in MySQL *and* emailed: email can fail silently, the database is the record of truth. Owner reads them in phpMyAdmin (`leads_this_month` view). No admin UI on purpose (auth surface).
- Anti-abuse: honeypot, JS timestamp (<3s = bot), 5 submissions per IP per 10 min, salted IP hashes.
- `tests/lib-test.php`: 26 tests (phone normalisation incl. Arabic-Indic digits, UAE formats, input cleaning, CR/LF stripping for mail-header injection) — pass with and without mbstring.
- Portable PHP 8.2 kept in the scratchpad for linting and tests; Hostinger needs PHP 8.1+.
- **Next deploy prerequisites:** create the DB and import `db/schema.sql`, place the config file, create `website@alqasimmovers.com`.

---

## Stage 4 — Technical Architecture (2026-09-20) — COMPLETE

### Completed
- `scripts/build.mjs` (zero deps): EN at root + AR under `/ar/`, layout + partials, i18n, business-fact injection with verification guard, head meta, canonical, reciprocal hreflang + x-default, Open Graph, JSON-LD (MovingCompany/WebSite/Service/BreadcrumbList), sitemap with `xhtml:link` alternates, robots.txt, CSS concat+minify, asset copy.
- Partials: layout, header, footer, sticky-cta, cta-band, quote-form. i18n: `src/i18n/en.json`, `ar.json`.
- CSS: reset, variables (design tokens), fonts, base, layout, components, utilities → 14 KB minified. JS: main, navigation (focus-trapped menu), forms (validation, error summary, Arabic-digit normalisation), analytics (dataLayer events).
- Fonts self-hosted: IBM Plex Sans 400/600 + IBM Plex Sans Arabic 400/600 (SIL OFL, from Fontsource), unicode-range split so English pages never load the Arabic file.
- `qa-crawl.mjs`, `secret-scan.mjs`, `serve.mjs`, `package.json` (task runner only, zero dependencies).
- Host config: `_headers` (HSTS, nosniff, Referrer-Policy, Permissions-Policy, frame-ancestors, CSP Report-Only, cache rules) and `_redirects` (www → non-www).
- Docs filled: deployment-guide (Netlify), performance-strategy (budgets vs actuals), security-strategy, schema-strategy, architecture §8 build contract.
- Scaffold home pages EN/AR built to exercise the pipeline (real copy comes in Stage 5).

### Decisions
- **ADR-004 hosting = Netlify** (built-in Forms decided it; no API key needed anywhere).
- **ADR-005 forms = Netlify Forms + honeypot**, no CAPTCHA at launch, hidden `lang` field so replies match the language.
- Build fails on unverified business facts — the no-fabrication rule is now enforced by code, not just policy.
- QA `--allow-missing` flag while pages are unwritten; Gate 12 runs strict.

### Known Issues / verified fixes
- Fixed during the stage: empty canonical/og:url; `.gitkeep` files copied into `dist/`; unverified facts rendering as empty instead of failing; **partials inside page bodies were not expanded** (the homepage CTA band was silently missing).
- Current page weight: home 9.6 KB HTML, CSS 14 KB, JS 7 KB — well inside budget. Arabic pages carry 87 KB of font.
- `og-default.png` (1200×630) not yet created — needed before launch.
- Wordmark SVGs still reference the font by name; convert to outlines before launch.

### Next Task
**Stage 5 — Core Website:** real copy and pages for Home, About, Services hub, Areas hub, Contact, Get a Quote (+ thank-you, noindex) and 404, in both languages, from SEO briefs → Gate 5.

---

## Stage 3 — UX/UI (2026-09-20) — COMPLETE

### Completed
- `docs/design/design-system.md`: brand direction ("calm, organised, obviously local"), colour tokens with **calculated** contrast ratios, IBM Plex Sans + IBM Plex Sans Arabic typography (Arabic gets 18px/1.85), spacing/radius/shadow/breakpoints, components (buttons, focus, cards, forms, header, mobile nav, sticky CTA bar, breadcrumbs, review/project cards, FAQ accordion, price-factor list, language switch, icon sprite), RTL rules, motion, imagery policy.
- Wireframes (mobile + desktop, LTR + RTL): home, service, area, blog post, quote + contact, mobile nav/sticky bar/RTL mirror check.
- Wordmark drawn in-house (no owner logo): `src/images/logo/wordmark-en.svg`, `wordmark-ar.svg`, `mark-square.svg`; logged in `image-register.md`.
- `docs/seo/conversion-plan.md`: CTA system EN/AR, placement rules, quote-form spec (name + phone the only required fields), truthful trust list, objection map, analytics events.
- `docs/audits/accessibility-audit.md` Round 1 (design): PASS with 6 findings carried into the build.

### Decisions
- Palette: navy `#0C3B5C` + amber `#E8A33D` CTA + warm sand `#F6F1E8`; amber is background-only (`--c-amber-text` for text).
- One type family across both scripts (IBM Plex) so EN and AR look like one brand.
- No dark mode in v1 (tokens allow adding it later). No map embeds, no carousels, no stock "our team" photos.
- Reviews/projects sections are absent, not placeholders, until real content exists.

### Known Issues
- Wordmark SVGs reference the font by name; convert text to outlines in Stage 4.
- A11Y-D1/D2: sticky elements must be verified against WCAG 2.4.11 in the build.
- Arabic font subset adds weight; the performance budget must account for it.

### Next Task
**Stage 4 — Technical Architecture:** `scripts/build.mjs` (zero dependencies, EN + `/ar/` output, hreflang, sitemap), partials, token CSS, JS modules, schema generator, form system + hosting decision (ADR-004/005), placeholder and hreflang build checks → Gate 4.

---

## Stage 2 — Research (2026-09-19) — COMPLETE

### Completed
- Owner answers: standard services (researched) + storage (partner), inter-emirate (every emirate), same-day (conditional), handyman, heavy items, truck hire; no international. All Dubai. No GBP. Bilingual EN/AR. Arabic name القاسم لنقل الأثاث. Free quotes (from brief).
- **Owner instruction: do not keep asking questions** — defaults in `config/business.json` → `defaults`.
- Docs: service-catalog, business-research, competitor-analysis (10 competitors + marketplaces), keyword-map (EN + AR, SERP-overlap consolidation), topical-map, page-inventory (42 EN pages, 40 indexable at launch, AR mirror), local-seo-strategy (10 areas to build, 8 deferred), content-strategy, content-calendar, internal-link-map, seo-strategy, indexability-register. Raw autocomplete data: `docs/seo/data/`.

### Decisions
- Merged: studio → apartment; unpacking → packing; loading/unloading → pickup-truck page + sections; local moving → home. No same-day page (conditional).
- Inter-emirate: hub + Abu Dhabi and Sharjah route pages.
- Areas built: Dubai Marina, JVC, Business Bay, Downtown, Palm Jumeirah, JLT, Al Barsha, Arabian Ranches, Dubai Hills, Silicon Oasis.
- Projects and Reviews pages: noindex until real items exist.
- Blog launch: permits, cost, checklist, choosing a mover, office checklist.
- Area URL pattern `/areas/<slug>/` confirmed.

### Known Issues
- No keyword volume tool: demand is based on autocomplete evidence only. Validate with Keyword Planner or Search Console later.
- PageSpeed API quota: competitor CWV not measured; re-run in Stage 10 with an API key.
- Emaar-type permits reportedly need the mover's public liability insurance (AED 1M). We make no insurance claim; flag to owner once, if relevant.
- No GBP, reviews or projects: trust comes from clarity.

### Next Task
**Stage 3 — UX/UI:** brand direction, design system (LTR + RTL, Arabic typeface), wireframes (home, service, area, blog, contact/quote, mobile), English + Arabic text wordmark (no logo supplied), conversion elements, accessibility design review → Gate 3.

---

## Stage 1 — Foundation (2026-09-19)

### Completed
- `CLAUDE.md` (project identity, rules summary, architecture summary, workflow, conversion rules)
- 20 agents in `.claude/agents/`
- 17 skills in `.claude/skills/*/SKILL.md` (instructions, checklist, expected output)
- 6 rule files in `.claude/rules/` (development, SEO, security, design, content-integrity, accessibility)
- Architecture doc with ADRs 001–005, URL architecture, Dependency Register
- Workflow, quality gates (Gates 1–14), master roadmap
- `config/business.json` with verification flags
- Documentation structure with templates/owners for every Stage 2+ doc
- Owner question list (`docs/owner-questions.md`)
- `CHANGELOG.md`, `README.md`, git initialised with `.gitignore` / `.gitattributes`
- Folder structure: `src/` (partials, pages, css, js, images/*, fonts, static), `scripts/`, `docs/*`

### Pending
- ~~Owner answers for Stage 2 blockers~~ — received 2026-09-19 (see Stage 2)
- First git commit (repository initialised; nothing committed yet — awaiting owner go-ahead)

### Decisions
- **ADR-001:** Source in `src/`, zero-dependency Node build → `dist/` as web root. Reason: the brief's `pages/` folder would create `/pages/...` URLs and plain HTML would duplicate header/footer across ~40 pages or require client-side includes (bad for SEO/CWV).
- **ADR-002:** All business facts in `config/business.json` with `verified` flags; unverified facts never render.
- Brief's `public/` → `src/static/`; `components/` → `src/partials/`; `responsive.css` folded into mobile-first component files (documented in architecture §5).
- Default canonical host: non-www `https://alqasimmovers.com` (pending owner confirmation A9).
- Proposed area URL pattern `/areas/<area>/` (to be confirmed by seo-strategist in Stage 2).
- Docs listed flat in brief §38 are organised into `docs/` subfolders (index in `docs/README.md`).
- Single changelog at root `CHANGELOG.md`; `docs/changelog/` points to it (avoids two diverging logs).
- CTA wording "Get a Free Quote" is used only once the owner confirms quotes are free (D1).

### Changes
- None to prior work (first stage).

### Known Issues
- Almost all business facts beyond name/phone/city/domain are unknown. Pages cannot be written until the owner answers.
- Service list and area list in `business.json` are candidates only (`offered/served: null`).

### Next Task (superseded — see Stage 2)
1. Owner reviews Stage 1 and answers the BLOCKING Stage 2 questions (at minimum A6, B1, B2, C1, C2, F2).
2. Then start Stage 2 in this order: business-researcher → competitor-researcher → keyword-researcher → seo-strategist (topical map, page inventory) → local-seo-specialist (area validation) → content-strategist (strategy + calendar) → internal-linking-specialist (draft link map) → Gate 2 review.
