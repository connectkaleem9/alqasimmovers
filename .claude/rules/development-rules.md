# Development Rules

Applies to: frontend-developer, performance-engineer, deployment-engineer, and anyone editing `src/` or `scripts/`.

## Master rules (owner brief §40)
1. Do not modify completed work unless required by a documented bug, security, SEO, accessibility, or performance issue, or an explicit project requirement.
2. Before modifying an existing feature, inspect its current implementation and dependencies.
3. Do not rewrite working systems unnecessarily.
4. Do not invent business information, reviews, projects, services, prices, certifications, awards, guarantees, or claims.
5. Do not create SEO pages solely for keyword manipulation.
6. Do not duplicate service or area-page content.
7. Every indexable page must have a clear purpose.
8. All SEO recommendations must be based on research and documented reasoning.

## Code standards
- **HTML:** semantic HTML5 landmarks (`header`, `nav`, `main`, `footer`, `section`, `article`, `aside`). Exactly one `<h1>`. `lang="en"` on `<html>`. W3C-validator clean.
- **CSS:** mobile-first (`min-width` media queries). Tokens only from `src/css/variables.css` — no raw colour, spacing, radius, or type-scale values in component CSS. File order: `reset → variables → base → layout → components → utilities`. BEM-style class names (`block__element--modifier`). No `!important` except in utilities.
- **JavaScript:** vanilla ES2020+, ES modules, `defer`. Progressive enhancement: every page must be usable with JS disabled (nav reachable, forms submit, phone/WhatsApp links work). No `innerHTML` with non-constant data. No globals.
- **Dependencies:** none by default. Any addition is recorded in `docs/architecture/architecture.md` → Dependency Register with reason, size, licence, and the alternative considered.
- **Build:** zero-dependency Node script (`scripts/build.mjs`, Stage 4). Output to `dist/`. `dist/` is never edited by hand and never committed.
- **URLs:** lowercase, hyphenated, trailing slash, no query-string IDs, no `.html` in public links (except `/404.html`, `/sitemap.xml`, `/robots.txt`).
- **Business data:** read from `config/business.json` at build time. Never hard-code phone, domain, name, or address in templates.
- **Images:** follow `.claude/skills/image-optimization/SKILL.md`. Always set `width` and `height`.
- **Commits:** small, one concern per commit, imperative message. Never commit secrets, `dist/`, or `.env`.

## Definition of done (any code change)
- [ ] Rules above respected
- [ ] No console errors
- [ ] Works at 360px, 768px, 1280px
- [ ] Keyboard usable
- [ ] Relevant docs updated
- [ ] `CHANGELOG.md` entry added
