---
name: technical-seo
description: Implement and check technical SEO basics on every page. Use when building templates (Stage 4/9) and auditing (Stage 13).
---

# Technical Seo

Project: Al Qasim Movers — movers & packers, Dubai (https://alqasimmovers.com). Follow `CLAUDE.md` and the rules in `.claude/rules/`, especially `content-integrity-rules.md`.

## Instructions
1. Per page: unique `<title>`, unique meta description, one `<h1>`, logical H2/H3, self-canonical absolute URL, `meta viewport`, `lang`, OG + Twitter tags, favicon links.
2. Breadcrumb nav + BreadcrumbList JSON-LD below the homepage.
3. Generate `sitemap.xml` from the page inventory at build time — indexable 200 canonical URLs only, with `lastmod`.
4. `robots.txt`: allow all, reference sitemap; confirm CSS/JS/images are not blocked.
5. Verify 404 returns HTTP 404 (not 200) on the host; `404.html` is noindex.
6. Trailing-slash consistency and canonical-host redirects.

## Checklist
- [ ] Titles/metas unique
- [ ] 1× H1 per page
- [ ] Canonicals self-referencing & absolute
- [ ] Sitemap valid & clean
- [ ] robots.txt blocks nothing important
- [ ] No accidental noindex
- [ ] Breadcrumbs + schema
- [ ] OG tags

## Expected Output
Pass/fail table per URL in `docs/audits/technical-seo-audit.md`.
