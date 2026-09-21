---
name: qa-testing
description: Crawl and test the built site before launch and after every major change. Use in Stages 12–13.
---

# Qa Testing

Project: Al Qasim Movers — movers & packers, Dubai (https://alqasimmovers.com). Follow `CLAUDE.md` and the rules in `.claude/rules/`, especially `content-integrity-rules.md`.

## Instructions
1. Links: broken links, broken images, wrong URLs, redirects, 404s.
2. SEO: missing/duplicate titles and descriptions, missing/multiple H1, missing/duplicate canonical, noindex mistakes, robots mistakes, sitemap problems, schema errors.
3. Content: no `[[OWNER-INPUT` placeholders, no lorem ipsum, no TODOs in `dist/`.
4. Technical: console errors, JS/CSS errors, form submit/validation/success/error, mobile menu, buttons, WhatsApp link, phone link.
5. Browsers/devices: Chrome, Edge, Firefox, Android mobile, iOS Safari, tablet, desktop.

## Checklist
- [ ] Crawl clean
- [ ] SEO checks pass
- [ ] No placeholders
- [ ] No console errors
- [ ] Forms work end-to-end
- [ ] Tel/WhatsApp open correctly
- [ ] Cross-browser pass

## Expected Output
`docs/audits/qa-report.md` with defect log and re-test status.
