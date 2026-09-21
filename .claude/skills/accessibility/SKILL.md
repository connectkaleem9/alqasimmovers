---
name: accessibility
description: Audit and fix accessibility to WCAG 2.2 AA. Use on designs and built pages.
---

# Accessibility

Project: Al Qasim Movers — movers & packers, Dubai (https://alqasimmovers.com). Follow `CLAUDE.md` and the rules in `.claude/rules/`, especially `content-integrity-rules.md`.

## Instructions
1. Automated: axe or Lighthouse on every template.
2. Keyboard: tab through the whole page; check skip link, focus visibility, menu, accordion, form, no traps.
3. Screen reader (NVDA): landmarks, headings list, link list, form labels/errors, button names.
4. Contrast check on every text/background pair and focus ring.
5. Zoom 200% and 320px width: no loss of content or horizontal scroll.

## Checklist
- [ ] No axe critical/serious issues
- [ ] Full keyboard operability
- [ ] Visible focus
- [ ] Labels & error messages
- [ ] Heading order
- [ ] Alt text
- [ ] Tap targets ≥ 44px
- [ ] Reduced motion respected

## Expected Output
`docs/audits/accessibility-audit.md`: Issue | WCAG SC | Severity | Location | Fix | Re-test.
