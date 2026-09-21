---
name: schema-markup
description: Create and validate JSON-LD structured data from verified business data. Use in Stage 4 (templates) and Stage 9.
---

# Schema Markup

Project: Al Qasim Movers — movers & packers, Dubai (https://alqasimmovers.com). Follow `CLAUDE.md` and the rules in `.claude/rules/`, especially `content-integrity-rules.md`.

## Instructions
1. Homepage: `MovingCompany` (LocalBusiness subtype) + `Organization` + `WebSite`, linked by `@id`.
2. Service pages: `Service` with `provider` → the business `@id`, `areaServed` only confirmed areas; plus `BreadcrumbList`.
3. FAQ: `FAQPage` only where it matches visible Q&A and current Google rules allow; it's optional.
4. Reviews: only legitimate, visible reviews; no self-serving aggregate rating on our own business.
5. Build JSON-LD from `config/business.json`; drop any property whose value is null/unverified (never output empty or guessed values).
6. Validate every template in Rich Results Test + validator.schema.org.

## Checklist
- [ ] Only verified properties
- [ ] Consistent `@id` graph
- [ ] Matches visible content
- [ ] No fake ratings
- [ ] Validator: 0 errors

## Expected Output
`docs/seo/schema-strategy.md` + validation log with screenshots/links.
