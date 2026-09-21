---
name: frontend-development
description: Build pages and components in the static-site architecture. Use from Stage 4.
---

# Frontend Development

Project: Al Qasim Movers — movers & packers, Dubai (https://alqasimmovers.com). Follow `CLAUDE.md` and the rules in `.claude/rules/`, especially `content-integrity-rules.md`.

## Instructions
1. Read the architecture doc and the page's SEO brief and design first.
2. Compose pages from partials in `src/partials/`; page source in `src/pages/<path>/index.html`.
3. Styles via tokens only; mobile-first; BEM class names.
4. JS as progressive enhancement in ES modules, `defer`.
5. Pull business data from `config/business.json` via build placeholders — never hard-code.
6. Run the build, open `dist/` locally, test at 360/768/1280px and keyboard only.

## Checklist
- [ ] Semantic HTML, 1× H1
- [ ] Tokens only
- [ ] Works without JS
- [ ] No console errors
- [ ] Business data from config
- [ ] Responsive at 3 widths
- [ ] Keyboard OK
- [ ] Changelog updated

## Expected Output
Working source in `src/`, clean build into `dist/`.
