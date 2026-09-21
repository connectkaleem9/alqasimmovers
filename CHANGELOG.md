# Changelog

All notable changes to the Al Qasim Movers project. Newest first. Format: date — stage — summary.

## 2026-09-21 — Live reviews, projects gallery, admin dashboard, real mobile sliders

### Added
- **Reviews page** (`/reviews/`, `/ar/reviews/`). Customers submit a review with name, phone (kept private), area, service, stars and text, and it appears on the page immediately.
- **Projects page** (`/projects/`, `/ar/projects/`). A gallery of photos and videos uploaded by the admin.
- **Admin dashboard** (`/admin/`). Create, edit and delete projects; upload photos and videos; hide or delete reviews; view quote leads.
- The quote form now stores every lead in the same database.

### Changed
- The homepage review button now reads **Leave a Review** and opens the review form.
- On phones, the trust bar and "Why choose us" slide continuously left→right and the process steps slide right→left. On desktop all three are static.

## 2026-09-21 — About section measured against the reference and rebuilt

Instead of eyeballing it, the reference (`about.PNG`) was measured in pixels and the rendered
page measured the same way, at 1440px, until the numbers matched.

| | Reference | Before | After |
|---|---|---|---|
| Section shape | 2.41 : 1 | 1.60 : 1 | 2.26 : 1 |
| Card position | 66%–96% | 56%–90% | 66%–95% |
| Form fields | white, icon + placeholder | dark translucent, label above | white, icon + placeholder |
| Photo | visible between text and card | not visible at all | visible |

### Fixed
- **The photo never actually showed.** Three separate causes: `<picture>` is `display: inline`, so `height: 100%` on the image silently failed; the inner-edge fade covered 45% of the panel; and the left third of the owner's photo is a blank white wall, so the visible strip showed nothing. Fixed with `picture { display: block }`, a 20% fade, and `object-position: 100%` so the boxes and plants land in the visible strip.
- Labels above each field made the card — and the whole section — ~170px too tall. Fields now use icon + placeholder with visually-hidden labels, matching the reference (labels still exist for screen readers).
- Section padding reduced and container widened so the card reaches the reference position.

## 2026-09-20 — Second round of layout corrections

### Changed
- **About:** the photo and the quote card now share one grid row, so the card overlaps the photo instead of overflowing the section. Photo 66% of the visual column, card pulled 18% over it — matching `about.PNG`.
- **Areas band:** white wash reduced from 90–94% to 62–74% so the owner's photo is clearly visible; the sub-heading uses the darker ink at semibold so contrast stays above 9:1 even over the photo's mid-tones.
- **Reviews:** built the card design from the owner's reference (avatar initials, stars, quote, name, area).

### Added
- `content/reviews.json` — real reviews, read by every build (currently empty).
- `content/reviews.sample.json` — placeholder text used **only** when `PREVIEW=1`, and then the section carries a dashed "SAMPLE LAYOUT" banner, the cards are outlined, and the build prints a warning. A normal build cannot emit sample reviews.

### Not done
- Publishing invented testimonials as real customer reviews. Rejected under `.claude/rules/content-integrity-rules.md`: fake reviews breach UAE consumer-protection law and Google's policies. The design is shown with clearly-marked samples instead.

## 2026-09-20 — Layout corrections from the owner's four reference screenshots

### Changed
- **Hero:** now a full-bleed photo with a navy scrim and the text over it (was a boxed image beside the text). WhatsApp button is white, as in the reference.
- **About:** text column + photo with the quote card overlapping it, at the reference proportions (the photo was previously a full-section background and far too large). Quote-card fields tightened.
- **Areas:** replaced the tile grid with the reference band — light photo background, chip eyebrow, heading with orange "Dubai", pill chips (4+4) and a centred dark "View All Areas" button.
- **Footer:** five columns — brand + company description, Quick Links, Services, **Legal (moved between Services and Contact)**, Contact. Services list now populated.

### Fixed
- `[object Object]` printed under the footer logo (the tagline token returned an object; now a proper per-language string).
- Restored component styles after a bad block replacement in `components.css` deleted forms, FAQ, breadcrumbs, cards and tiles; the file was rewritten in full and verified.
- About section had no base grid rule, so its two columns stacked.

## 2026-09-20 — Owner's design and real images applied

### Changed
- Design system rebuilt to the owner's homepage mockup: orange `#FB7320`, navy `#0B233D`/`#091731`, Poppins + Tajawal fonts (replacing IBM Plex). Buttons keep the exact orange with navy labels, because white-on-orange measured 2.77:1 and fails AA.
- Homepage (EN + AR) rebuilt to the design's section order: hero, 8 service cards, about + dark quote card, why-choose, 4 process steps, area tiles, reviews, CTA band, contact strip, 4-column footer.
- Header/footer now use the owner's real logo; placeholder SVG wordmarks deleted.

### Added
- Owner's 12 photos + logo processed into AVIF/WebP at multiple widths (13 MB → 1.5 MB); `og-default.jpg`; favicon and apple-touch icons from the real mark.
- Facts taken from the owner's own design and logo: email `info@alqasimmovers.com`, hours Mon–Sun 08:00–22:00, tagline, legal name **Al Qasim Movers L.L.C**.

### Fixed
- Logo squashed to 16px by flexbox; sticky Call button using a removed class; last raw colour tokenised.

### Not done on purpose
- The three testimonials in the mockup look like template samples, so they are not published. The reviews section says real ones are coming.

## 2026-09-20 — Hosting changed to Hostinger (owner's existing plan)

### Changed
- ADR-004: Hostinger shared hosting replaces Netlify. ADR-005: PHP + MySQL form handling replaces Netlify Forms.
- `src/static/.htaccess` replaces `_headers` / `_redirects` (security headers, HTTPS + non-www, trailing slash, 404, gzip, dotfile blocking).
- Quote form now posts to `/form/quote.php`; added `source_page` and JS timestamp fields.
- Build ships `.htaccess` and `form/*.php`, and excludes the sample config.
- Security, performance and deployment docs rewritten for Apache/PHP/MySQL.

### Added
- `src/php/quote.php`, `src/php/lib.php`, `src/php/config.sample.php` (no real credentials).
- `db/schema.sql` — `leads` table + `leads_this_month` view (utf8mb4).
- `tests/lib-test.php` — 26 tests for phone normalisation/validation and input cleaning, passing with and without mbstring.

## 2026-09-20 — Stage 4: Technical architecture complete

### Added
- `scripts/build.mjs` — zero-dependency bilingual static build (EN root + `/ar/`, hreflang, schema, sitemap, robots).
- `scripts/qa-crawl.mjs`, `scripts/secret-scan.mjs`, `scripts/serve.mjs`, `package.json` (task runner, no dependencies).
- Partials (layout, header, footer, sticky CTA, CTA band, quote form), i18n files, full CSS token system, JS modules.
- Self-hosted IBM Plex Sans + IBM Plex Sans Arabic; `_headers`, `_redirects`, web manifest, icon sprite, apple-touch icon.
- ADR-004 (Netlify hosting) and ADR-005 (Netlify Forms); architecture §8 build contract.

### Fixed
- Empty canonical/og:url; `.gitkeep` leaking into `dist/`; unverified business facts rendering as blank instead of failing the build; partials not expanding inside page bodies.

## 2026-09-20 — Stage 3: UX/UI complete

### Added
- Design system (brand direction, verified colour tokens, bilingual typography, components, RTL rules).
- Wireframes for all templates, mobile + desktop, LTR + RTL.
- In-house wordmark: `wordmark-en.svg`, `wordmark-ar.svg`, `mark-square.svg`.
- Conversion plan (CTA system, quote-form spec, analytics events).
- Accessibility audit round 1 (design): PASS with 6 build findings.

### Changed
- Gate 3 passed; roadmap Stage 3 complete.

## 2026-09-19 — Stage 2: Research complete

### Added
- Business research, competitor analysis, keyword map (EN + AR), topical map, page inventory, local SEO strategy with area validation, content strategy, content calendar, internal link map, SEO strategy, indexability register, autocomplete evidence data.
- Owner answers round 3: Arabic name, every emirate, partner storage, conditional same-day; `defaults` block in business.json; free quote confirmed from the brief.

### Changed
- Gate 2 passed; roadmap Stage 2 complete; owner-questions reduced to a short, non-blocking list (owner asked not to be asked repeatedly).

## 2026-09-19 — Stage 2: Research (owner answers round 1)

### Added
- `docs/research/service-catalog.md` — market service research (EN + AR competitors) with Tier 1/2/3 classification.
- `.claude/rules/bilingual-rules.md` and ADR-006 (English + Arabic, `/ar/` subfolder, hreflang, RTL).
- Arabic seed keywords in `docs/seo/keyword-map.md`; owner questions section G (bilingual).

### Changed
- `config/business.json`: Tier 1 + confirmed Tier 2 services offered (international = not offered); all Dubai areas served; GBP = none; languages = en, ar; Arabic name field.
- CLAUDE.md, SEO/design/accessibility rules, quality gates (Gate 1 marked passed; bilingual items in Gates 2–4, 9, 12; GBP N/A in Gate 14), roadmap, local SEO strategy, local-seo skill, all agents (bilingual rule).

## 2026-09-19 — Stage 1: Foundation

### Added
- `CLAUDE.md` project operating instructions.
- 20 specialised agents in `.claude/agents/`.
- 17 skills in `.claude/skills/`.
- Rule sets: development, SEO, security, design, content-integrity, accessibility (`.claude/rules/`).
- `config/business.json` — central business configuration with verification flags.
- Architecture (`docs/architecture/architecture.md`) with ADR-001…005.
- Workflow, quality gates, and master roadmap.
- Documentation skeleton for all Stage 2–14 deliverables, SEO brief template, owner question list.
- `PROJECT_MEMORY.md`, `README.md`, `.gitignore`, `.gitattributes`; git repository initialised.
- Folder structure for `src/`, `scripts/`, and `docs/`.

### Notes
- No production website code or visual design created (per Stage 1 scope).
