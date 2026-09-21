# SEO Rules

Applies to all SEO, content, and development agents.

## The five-question test (every indexable page, before it is written)
1. What search intent does this page satisfy?
2. Why does this page deserve to exist (instead of being a section of another page)?
3. What unique information does it provide?
4. What action should the user take?
5. Which internal pages should it connect to?

If any answer is weak, the page is not created. Answers are recorded in the page's SEO brief (`docs/seo/briefs/<slug>.md`, template: `docs/seo/briefs/_template.md`).

## On-page
- One primary keyword per page; no two pages share a primary keyword (prevents cannibalisation — enforced via `docs/seo/keyword-map.md`).
- Exactly one `<h1>`, containing the primary topic naturally. H2/H3 follow a logical outline; no skipped levels.
- `<title>`: unique, ≤ 60 characters where practical, primary keyword near the front, brand at the end (`… | Al Qasim Movers`).
- Meta description: unique, ~140–160 characters, describes the page and gives a reason to click. Never stuffed.
- Never write content to increase word count. Length follows intent.
- No keyword stuffing, hidden text, doorway pages, or city-name-swap pages.

## Technical
- Self-referencing absolute canonical on every indexable page (`https://alqasimmovers.com/<path>/`), unless a deliberate canonical relationship is documented.
- One canonical host, HTTPS only; other variants 301 to it. Default: `https://alqasimmovers.com` (non-www) — owner to confirm.
- Trailing-slash URLs everywhere; internal links match canonical form exactly.
- `sitemap.xml` includes only canonical, indexable, 200-status URLs. Excludes 404, thank-you, and noindex pages.
- `robots.txt` never blocks CSS, JS, images, or indexable pages. Re-validate after every change.
- `noindex` only on thank-you, 404, and utility pages. Every `noindex` is listed in `docs/seo/indexability-register.md`.
- Breadcrumbs on every page below the homepage, with matching `BreadcrumbList` schema.
- Open Graph + Twitter card metadata on every page; `og:image` 1200×630.
- Descriptive image `alt` text and descriptive filenames.

## Multilingual (EN/AR)
- Full rules in `.claude/rules/bilingual-rules.md`. Summary: `/ar/` subfolder, reciprocal `hreflang` (en, ar, x-default→en), self-canonical per language, both languages in the sitemap with alternates.
- The five-question test and keyword mapping apply **per language**. Arabic primary keywords come from Arabic keyword research, not from translating English keywords.
- Titles and meta descriptions are unique within each language.

## Schema
- Structured data describes only content that is visible on the page and true.
- No `AggregateRating` or `Review` markup unless reviews are real, displayed on the page, and the markup complies with Google's current guidelines. Self-serving review markup for our own LocalBusiness is not eligible for review rich results — do not add it for SEO.
- `FAQPage` only when it matches visible content and current Google rules (FAQ rich results are heavily restricted; the markup is optional, never a reason to create a page).
- Validate with Google Rich Results Test and the Schema.org validator before launch.

## Local SEO
- NAP (Name, Address, Phone) identical everywhere: site, schema, citations, and a Google Business Profile if one is created later (none exists as of 2026-09-19; the owner chose to proceed without).
- Service area: all of Dubai (owner-confirmed). Being served is necessary but not sufficient for an area page.
- Area pages only when: the area is actually served (owner-confirmed), there is enough unique local information, and the page is not a near-duplicate of another area page. Minimum bar is defined in `docs/seo/local-seo-strategy.md`.

## Linking
- Descriptive, natural anchor text. Vary anchors; no sitewide exact-match anchor repetition.
- Every indexable page is reachable within 3 clicks of the homepage and linked from at least one other relevant page (no orphans).
- Follow `docs/seo/internal-link-map.md`.
