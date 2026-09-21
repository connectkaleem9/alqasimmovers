# Performance Strategy

| Status | Owner | Stage | Date |
|---|---|---|---|
| Budgets set; measurement in Stage 10 | performance-engineer | 4 / 10 | 2026-09-20 |

## Targets (mobile, field data once live)
LCP < 2.5s · CLS < 0.1 · INP < 200ms

## Budgets per page (compressed)
| Asset | Budget | Now |
|---|---|---|
| HTML | ≤ 30 KB | ~11 KB (home) |
| CSS (one file, all pages) | ≤ 20 KB | **14 KB minified** |
| JS (3 modules) | ≤ 15 KB | ~7 KB |
| Fonts — English page | ≤ 60 KB | 22 KB (400) + 24 KB (600) = 46 KB |
| Fonts — Arabic page | ≤ 100 KB | 42 KB + 45 KB = 87 KB |
| Hero image | ≤ 120 KB | none yet |
| **Total (excl. images), English** | **≤ 120 KB** | ~78 KB |

For comparison, competitors ship 160 KB–1.8 MB of HTML alone with 24–122 scripts (`competitor-analysis.md`).

## What the build already does
- One minified stylesheet; no framework, no jQuery, no third-party script.
- ES modules with `type="module"` (deferred by default).
- Self-hosted fonts, `font-display: swap`, unicode-range split so Latin pages never download the Arabic file.
- Only the current language's 400 weight is preloaded.
- Long-cache headers for `/css`, `/js`, `/fonts`, `/images`; `must-revalidate` for HTML (set in `.htaccess`, with gzip via `mod_deflate`).
- No layout shift by construction: fixed logo dimensions, no injected banners, no web-font-triggered reflow beyond the swap.

## Still to do (Stage 10)
- Images: AVIF/WebP, `srcset`/`sizes`, `width`/`height` on every image, lazy below the fold, `fetchpriority="high"` on the LCP image only.
- Subset the Arabic font further once the copy is final (drop unused Latin glyphs from the Arabic file).
- Consider inlining critical CSS only if measurement shows it helps; 14 KB may not be worth the complexity.
- Re-run PageSpeed Insights with an API key, for our pages and the competitor set (the anonymous quota blocked this in Stage 2).
- Check INP on the mobile menu and the FAQ accordion.
- Shared hosting has no CDN in front of it, so the page-weight budget matters more than it would on a CDN host. Consider putting Cloudflare (free) in front of the domain in Stage 14 if TTFB from the UAE is poor.

## Rules
- Any new dependency, font weight, or third-party script needs a budget entry and an architecture Dependency Register row.
- Do not trade usability for a Lighthouse number.
