# Quality Gates

A stage is **complete** only when every item in its gate is checked, evidence is recorded, and final-reviewer returns APPROVED. project-manager records the result in `PROJECT_MEMORY.md`. A gate with any unchecked item = **FAIL** → do not start the next stage.

Universal items (apply to every gate):
- [ ] No fabricated business information anywhere in the deliverables
- [ ] `PROJECT_MEMORY.md` and `CHANGELOG.md` updated
- [ ] Missing facts handled by a documented default (no question lists to the owner)
- [ ] final-reviewer verdict recorded

---

## Gate 1 — Foundation — PASSED 2026-09-19
- [x] `CLAUDE.md` with identity, rules, workflow, architecture summary
- [x] 20 agents in `.claude/agents/`, each with name, description, tools, responsibilities, outputs
- [x] 17 skills in `.claude/skills/`, each with instructions, checklist, expected output
- [x] Rule files: development, SEO, security, design, content-integrity, accessibility (+ bilingual, added 2026-09-19)
- [x] Architecture doc with ADRs; folder structure created
- [x] Documentation structure with owners and statuses
- [x] `config/business.json` with only verified facts marked verified
- [x] `PROJECT_MEMORY.md`, `CHANGELOG.md`, roadmap, workflow, quality gates
- [x] Git initialised with `.gitignore` / `.gitattributes`
- [x] Owner question list produced

## Gate 2 — Research — PASSED 2026-09-19 (self-review; owner review welcome)
- [x] Business research complete with cited sources
- [x] 10 competitors + marketplaces analysed (page speed via PSI deferred to Stage 10 — API quota; HTML weight/scripts used as proxy)
- [x] Keyword map: every keyword has role + intent; each cluster maps to one URL; no cannibalisation
- [x] Topical map complete
- [x] Local SEO strategy with Area Validation table (served / unique info / value / not duplicate)
- [x] Content strategy + initial content calendar
- [x] Internal link map (draft)
- [x] **Owner answers received (2026-09-19):** services = standard market set (Tier 1, see `docs/research/service-catalog.md`); areas = all of Dubai; no GBP; bilingual EN/AR
- [x] Tier 2 services confirmed or excluded by owner (2026-09-19)
- [x] Arabic keyword research complete (separate from English; bilingual-rules)
- [x] Page inventory for first release: 42 EN pages (40 indexable at launch) + AR mirror, each with a 'why it exists' justification; full five-question answers go in per-page briefs (Stages 5–8)

## Gate 3 — UX/UI — PASSED 2026-09-20
- [x] Brand direction and design system with tokens
- [x] All colour pairs AA-contrast verified by calculation (ratios recorded in design-system §2)
- [x] Wireframes: home, service, area, blog, contact/quote — mobile and desktop, **LTR and RTL**
- [x] Arabic typeface chosen; Arabic type tokens defined
- [x] Conversion elements designed (CTAs, sticky bar, quote form)
- [x] accessibility-auditor design review passed
- [x] Design direction shared with the owner (owner asked not to be sent question lists; changes welcome any time — nothing here is irreversible)
- [x] Logo: owner's logo if supplied, otherwise an English + Arabic text wordmark (default)
- [x] Photo plan: real photos when supplied; otherwise neutral imagery that never claims to show our own jobs

## Gate 4 — Technical Architecture — PASSED 2026-09-20
- [x] `scripts/build.mjs` builds `dist/` with correct URL tree, zero npm dependencies
- [x] Partials, token CSS, JS modules scaffolded
- [x] Business config injection works; unverified fields are not rendered
- [x] SEO utilities: title/meta/canonical/OG per page, sitemap generator, robots
- [x] Schema generator produces valid JSON-LD from config
- [x] Form system: PHP handler + MySQL on Hostinger (ADR-005) — server-side validation, prepared statements, honeypot, speed check, per-IP rate limit, 26 passing unit tests
- [x] Hosting decided: Hostinger shared hosting (ADR-004), owner's existing plan
- [x] Placeholder check (`[[OWNER-INPUT`) fails the build
- [x] i18n: `/ar/` output, `lang`/`dir`, UI strings from `src/i18n/`, reciprocal hreflang, bilingual sitemap; build fails on a broken hreflang pair

- [x] QA crawler (`qa-crawl.mjs`) and secret scanner (`secret-scan.mjs`) written; `npm run check` green
- [x] Fonts self-hosted (IBM Plex Sans + Arabic, SIL OFL); no third-party runtime requests

## Gate 5 — Core Website
- [ ] Header, navigation (mobile + desktop), footer, sticky CTA
- [ ] Home, About, Services hub, Contact, Get a Quote built from approved briefs
- [ ] Form works end-to-end on staging
- [ ] Owner reviewed copy for accuracy

## Gate 6 — Service Pages
- [ ] Only owner-confirmed services built
- [ ] Every page follows the service template (brief §11) and its SEO brief
- [ ] No duplicated passages between service pages (similarity check)

## Gate 7 — Local SEO Pages
- [ ] Only areas that passed validation built
- [ ] Each has unique local considerations, FAQs, relevant services, CTA
- [ ] No near-duplicate area pages (similarity check)

## Gate 8 — Supporting Pages
- [ ] Reviews (real only, sourced), Projects (real only), FAQ (owner-approved answers), Blog (strategic launch articles), Privacy, Terms, Cookie, 404
- [ ] Legal pages reflect actual data handling (form handler, analytics, cookies)

## Gate 9 — SEO Implementation
- [ ] Unique titles/metas; 1× H1; heading hierarchy
- [ ] Canonicals, sitemap, robots validated
- [ ] Breadcrumbs + BreadcrumbList
- [ ] Internal link audit clean (no orphans, depth ≤ 3)
- [ ] Open Graph + image SEO
- [ ] Schema validated (0 errors)
- [ ] hreflang reciprocity + self-canonicals verified for every EN/AR pair
- [ ] Arabic titles/metas unique within Arabic

## Gate 10 — Performance
- [ ] Lab: LCP < 2.5s, CLS < 0.1, TBT low (INP proxy) on mobile for every template
- [ ] Page-weight budgets met
- [ ] Images, fonts, CSS, JS optimised; caching/compression configured

## Gate 11 — Security
- [ ] Secret scan clean; dependency audit (should be none)
- [ ] Live config file confirmed above `public_html`, permissions 600
- [ ] `/db/schema.sql`, `/.htaccess`, `*.sample.php` unreachable over the web
- [ ] Rate limit, honeypot and speed check verified on the live form
- [ ] Forms tested with malicious input
- [ ] Headers tested on staging; CSP enforced without breakage
- [ ] No XSS sinks / open redirects

## Gate 12 — QA
- [ ] Chrome, Edge, Firefox, Android, iOS Safari, tablet, desktop
- [ ] Links, images, forms, menu, tel, WhatsApp all pass
- [ ] Accessibility audit: no critical/serious issues
- [ ] No console errors
- [ ] Every template passes in RTL (Arabic): layout, icons, form, menu, sticky bar
- [ ] Arabic content reviewed (Claude native-quality review + owner review at the gate)

## Gate 13 — Pre-launch SEO Audit
- [ ] Full crawl; `docs/audits/pre-launch-seo-audit.md` complete
- [ ] All Critical and High issues fixed and re-tested
- [ ] Final launch checklist (brief §43) fully checked

## Gate 14 — Deployment
- [ ] Live on https://alqasimmovers.com with canonical-host redirects
- [ ] Headers, 404 status, HTTPS verified live
- [ ] Search Console verified, sitemap submitted, key URLs inspected
- [ ] Analytics working (consent-aware)
- [ ] Google Business Profile website link aligned — *N/A at launch (owner has no GBP); recommendation to create one recorded*
- [ ] Rollback path documented
