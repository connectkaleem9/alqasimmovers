---
name: internal-linking
description: Plan and audit internal links. Use when adding pages and before launch.
---

# Internal Linking

Project: Al Qasim Movers — movers & packers, Dubai (https://alqasimmovers.com). Follow `CLAUDE.md` and the rules in `.claude/rules/`, especially `content-integrity-rules.md`.

## Instructions
1. Structure: Home → Services hub → service pages; Home → Areas hub → area pages; blog → service → area → quote.
2. Each service page links to relevant areas and related services; each area page links to the most relevant services.
3. Anchors descriptive and varied (e.g. 'villa moving in Arabian Ranches', 'our packing team', not the same exact-match phrase everywhere).
4. Audit: orphans, click depth > 3, broken links, links to non-canonical URLs, redirect hops.

## Checklist
- [ ] No orphan pages
- [ ] Click depth ≤ 3
- [ ] No broken/redirecting internal links
- [ ] Anchors natural & varied
- [ ] Link map up to date

## Expected Output
`docs/seo/internal-link-map.md` + audit section.
