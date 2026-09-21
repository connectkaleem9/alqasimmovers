---
name: performance
description: Measure and improve Core Web Vitals and page weight. Use in Stage 10 and when adding heavy assets.
---

# Performance

Project: Al Qasim Movers — movers & packers, Dubai (https://alqasimmovers.com). Follow `CLAUDE.md` and the rules in `.claude/rules/`, especially `content-integrity-rules.md`.

## Instructions
1. Measure with Lighthouse (mobile, throttled) and PageSpeed Insights; later use CrUX/Search Console field data.
2. Images: correct dimensions, AVIF/WebP with fallback, `srcset`/`sizes`, lazy-load below the fold, `fetchpriority=high` on the LCP image only.
3. CSS: inline critical CSS for above-the-fold if it measurably helps; minify; remove unused.
4. JS: minimal, `defer`/module, no unused code, no third-party unless justified.
5. Fonts: self-host, subset, preload only the critical weight, `font-display: swap`, size-adjusted fallback to limit CLS.
6. Host: Brotli/gzip, long cache for hashed assets, short cache for HTML.

## Checklist
- [ ] LCP < 2.5s
- [ ] CLS < 0.1
- [ ] INP < 200ms
- [ ] Page-weight budget met
- [ ] No render-blocking third parties
- [ ] Images sized & modern format

## Expected Output
`docs/audits/performance-report.md` with before/after metrics per template.
