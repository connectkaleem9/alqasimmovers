# Changelog

All notable changes to the Al Qasim Movers project. Newest first. Format: date — stage — summary.

## 2026-09-22 — Mobile audit of every page

Checked all 84 pages at 390x844 (phone) for sideways scrolling, elements wider than the screen, tap-target size, tiny text, oversized images and console errors. 81 were clean; the findings below were fixed.

### Fixed
- **Mobile menu covered the logo.** The panel opened at a fixed offset; it now starts exactly under the header, which is taller while the top bar is on screen.
- **Contact page printed the opening hours twice** in the same card.
- **404 page mixed English and Arabic in one line.** They are now two lines, each with its own `lang`/`dir`.
- **Projects page** skips media whose file is missing on the server instead of rendering a broken image.

### Checked and correct
Both dropdowns work inside the mobile panel (57–60px tap targets, scrollable, no sideways scroll), in LTR and RTL. The homepage sliders are wider than the screen by design, inside a clipped wrapper — the page itself does not scroll sideways.

## 2026-09-22 — The rest of the site: About, Contact, Quote, FAQ, guides, legal, 404

### Added
- **Core pages (EN + AR):** `/about/`, `/contact/`, `/get-a-quote/` with the quote form, `/get-a-quote/thank-you/` (noindex — this is what Analytics counts as a lead), `/faq/`, `/privacy-policy/`, `/terms-and-conditions/`.
- **Guides:** `/blog/` hub plus five English guides — move-in/move-out permits, what a move costs, a Dubai moving checklist, how to choose a mover, and an office relocation checklist. Three of them are also published in Arabic (permits, cost, checklist); the other two ship in Arabic once reviewed.
- **`/404.html`**, bilingual, noindex, with routes back into the site. New build support: a page can declare `"output"` to be written as a fixed file, and the error document ships without canonical, hreflang or og:url.
- Briefs for every new page in `docs/seo/briefs/`.

### Changed
- `/reviews/` and `/projects/` are now `noindex` until they hold real content, as the indexability register requires. Flip `"index"` in their page meta once there are reviews and projects to show.

### Integrity
The cost guide explains what drives a price and refuses to publish a price list. The terms page states only what we can stand behind: payment terms are confirmed with the quote, and nothing is claimed about insurance.

## 2026-09-22 — Area pages published (EN + AR), Areas dropdown

### Added
- `/areas/` hub plus 10 area pages in both languages: Dubai Marina, JLT, Business Bay, Downtown Dubai, Palm Jumeirah, JVC, Al Barsha, Arabian Ranches, Dubai Hills and Dubai Silicon Oasis.
- Each page passes the area-page minimum bar in `docs/seo/local-seo-strategy.md`: real local constraints (permit route, lift slots or gate registration, loading and parking, property mix, typical moves), unique FAQs, and links to the services that matter in that community. Briefs in `docs/seo/briefs/area-*.md`.
- **Areas dropdown** in the header alongside the Services dropdown.
- `config/business.json`: JLT added to `areasServed` (inside the owner-confirmed "all of Dubai").

### Integrity
Permit and access details are written generally, because building processes vary and change. No claims about jobs completed in any area, and no prices.

## 2026-09-22 — All 14 service pages published (EN + AR), Services dropdown

### Added
- **14 service URLs in both languages (28 pages):** home, apartment, villa, office and commercial movers; packing & unpacking; furniture moving; dismantling & assembly; storage; piano & heavy items; pickup truck with driver; the inter-emirate hub; and the Dubai→Abu Dhabi and Dubai→Sharjah route pages. Plus the `/services/` hub.
- Targeting follows `docs/seo/keyword-map.md` (Stage 2 research): one primary keyword per page, no two pages sharing one. Arabic pages are written for Arabic searchers (نقل اثاث / فك وتركيب / تغليف), not translated.
- Every page has unique content: what is included, how it works, the local reality (permits, lift slots, gated communities, mall hours, traffic), honest pricing factors, 5 FAQs and related-service links.
- `Service` + `BreadcrumbList` schema from the build; SEO briefs for all 14 pages in `docs/seo/briefs/`.
- **Services dropdown** in the header (hover, keyboard and touch), listing all 12 services plus "All services".

### Integrity
No prices, no invented capabilities. Storage is stated as a partner facility, same-day moves stay conditional, international moving is never mentioned as offered.

## 2026-09-22 — Projects page as cards, Projects in the navigation

### Changed
- `/projects/` and `/ar/projects/` now show a card per project: cover photo on top, then the title, the location and the description, with the remaining photos and videos as small thumbnails under the text. Three cards per row on desktop, two on tablets, one on phones.
- "Projects" added to the header navigation (both languages).

## 2026-09-22 — Admin uploads: on the create form, every photo/video format, hosting-size limits

### Fixed
- **"New project" had no upload box.** Photos and videos can now be chosen while creating the project; they are saved with it in one step (button: "Save project and upload"). The upload box also stays on the edit screen.

### Changed
- Every common photo format is accepted, including iPhone **HEIC/HEIF**, plus AVIF, TIFF, GIF and BMP. Formats GD cannot read are converted with Imagick, so they still display on the website.
- Every common video format is accepted: MP4, MOV, WebM, AVI, MKV, WMV, 3GP, MPEG, FLV, M4V, TS.
- The old 20 MB photo / 500 MB video caps are gone. The only limit is the hosting's own per-file limit (2 GB), shown in the upload box, with 20 files per upload and no total storage cap.
- Clear messages when a file is refused (too large, stopped early, not a photo or video).

### Security (unchanged)
- Only files that really are images or videos are stored; scripts, HTML and programs are refused even when renamed, and `/uploads/` can never execute code.

## 2026-09-21 — Review form: centred layout, optional email instead of phone

### Changed
- The heading and intro are centred, with one wide form card below. Name, email, area and service sit in a single row on desktop, two per row on tablets, and are stacked on phones. The submit button is centred.
- Phone number removed. There is an optional **email** field instead (validated if filled in, private, shown only in the admin). Owner's request.
- The database gains a `reviews.email` column automatically on the next request (`migrate()` adds missing columns).

## 2026-09-21 — Google Search Console + Google Analytics 4

### Added
- Search Console verification meta tag on every page (from `config/business.json` → `integrations`).
- GA4 (`G-K39C33QF13`) loads only after the visitor accepts the cookie notice. Decline, or withdrawing consent later, removes the `_ga` cookies. The notice can be reopened from "Cookie settings" in the footer. Analytics never loads in `/admin/`.
- Events: `tel_click`, `whatsapp_click` (with placement), `quote_form_send`, `review_form_send`, `review_submit` (reviews thank-you), `generate_lead` (quote thank-you page, once it exists).
- `/cookie-policy/` and `/ar/cookie-policy/`: a factual description of exactly what the site stores.
- CSP (report-only) allows the Google Analytics origins.

, admin inside the site layout

### Fixed
- Review form: the name box no longer stretches to match the phone box (whose hint made it taller); the stars now start at the reading edge instead of floating right, empty stars are visible, and each star is a 44px tap target. The Arabic review field got its own label ("اكتب تجربتك") instead of repeating "تقييمك".

### Changed
- Admin login and dashboard now show the site's top bar, header and footer. The build renders them into `admin/shell.html`, which is blocked from direct access. The admin styles are scoped to `.admin-app`.

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
