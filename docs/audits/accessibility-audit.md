# Accessibility Audit

Target: WCAG 2.2 AA. Findings are logged per round. Round 1 reviews the **designs** (Stage 3). Round 2 will review the **built pages** (Stage 12) with axe, keyboard and NVDA.

---

## Round 1 — Design review (Stage 3, 2026-09-20)
Reviewer: accessibility-auditor. Scope: `docs/design/design-system.md` + all wireframes.

### Verified as compliant by design
| Item | Evidence |
|---|---|
| Text contrast | All text pairs calculated: lowest is amber-hover on navy at 3.98 (restricted to large text/UI) and success green on white at 5.37. Body pairs are 7.97–17.29. |
| UI component contrast | Interactive border `#6E7F8B` = 4.14 on white, 3.68 on sand (needs ≥ 3). |
| Focus visibility | 3px navy outline, 2px offset; amber outline on dark surfaces (5.42 on navy). `outline: none` is banned. |
| Target size (2.5.8) | Buttons and inputs 48px tall; sticky bar 56px; menu items 48px. |
| Forms (1.3.1, 3.3.1–3.3.3) | Visible labels, "required" in words, inline errors + error summary with focus move, `aria-describedby`. |
| Keyboard (2.1.1–2.1.2) | Mobile menu uses a real button with `aria-expanded`/`aria-controls`, Escape to close, focus return, background `inert`. FAQ uses native `<details>`. |
| No colour-only meaning (1.4.1) | Errors carry icon + text; required state is a word. |
| Motion (2.3.3) | 150–200ms transitions only, all disabled under `prefers-reduced-motion`. No carousels or auto-animation. |
| Language (3.1.1–3.1.2) | `lang`/`dir` per page; other-language fragments carry their own `lang`; the switcher is a link with `hreflang`. |
| Reflow / zoom (1.4.10, 1.4.4) | 360px baseline, single-column, fluid type via `clamp()`, 68ch measure. |
| Headings (1.3.1, 2.4.6) | One H1 per template; wireframes show a strict H2/H3 outline. |
| Link purpose (2.4.4) | Card headings are the links; no whole-card link, no bare "Read more". |

### Findings to fix in build (Stage 4–5)
| # | Severity | Issue | WCAG | Fix |
|---|---|---|---|---|
| A11Y-D1 | Medium | The sticky mobile CTA bar can cover content, including a focused element near the page bottom | 2.4.11 Focus Not Obscured | Reserve `padding-block-end` equal to bar height on `body`; hide the bar while a text input has focus (already specified — must be verified in build) |
| A11Y-D2 | Medium | The desktop service page has a sticky quote card that could overlap content at 200% zoom | 1.4.10 | Disable stickiness below 1024px and when `prefers-reduced-motion` or zoom reduces height; the card must fall back to a normal block |
| A11Y-D3 | Low | Amber is the CTA colour; amber text on white fails (2.16) | 1.4.3 | Enforced token split: `--c-amber` (background only) vs `--c-amber-text` — add a CSS lint check in QA |
| A11Y-D4 | Low | Arabic line-height/size differ from English; risk of clipped buttons with long Arabic labels | 1.4.12 | Buttons size by content, min-height 48px, no fixed widths; test the longest Arabic CTA string |
| A11Y-D5 | Low | `<details>` accordion has no animation but jumps focus on open in some browsers | 2.4.3 | Verify in Stage 12 across Chrome, Edge, Firefox, iOS Safari |
| A11Y-D6 | Info | Icon sprite must not become the only meaning carrier | 1.1.1 | Meaningful icons get `<title>`; decorative get `aria-hidden` — verify in build |

### Not applicable at this stage
Media captions (no video/audio planned), session timeouts (no login), data tables (only simple content tables with headers).

**Verdict: design PASSES Gate 3 accessibility review**, with the six findings carried into the build checklist.
