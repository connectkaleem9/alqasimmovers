# Design System — Al Qasim Movers

| Status | Owner | Stage | Date |
|---|---|---|---|
| Complete (v1) — ready for Gate 3 | ui-ux-designer | 3 | 2026-09-20 |

Rules: `.claude/rules/design-rules.md`, `accessibility-rules.md`, `bilingual-rules.md`. Every token here becomes a CSS custom property in `src/css/variables.css` in Stage 4.

---

## 1. Brand direction

**What the brand must say:** professional · reliable · modern · local · trustworthy · fast · easy to contact.

**The problem with the market (from competitor analysis):** Dubai mover sites look alike — orange/blue templates, stock photos of smiling crews, "#1" and "100% satisfaction" badges, and screens full of price stickers. We cannot (and will not) claim reviews, years or awards, so our design has to signal trust a different way.

**Our direction: "Calm, organised, and obviously local."**
- **Calm over loud.** A deep blue base and lots of white space, instead of a shouting orange template.
- **Organised over decorated.** Clear steps, clear inclusions, clear price factors. The design shows order — the thing customers actually want from a mover.
- **Local without clichés.** A warm sand tone borrowed from Dubai light, not skyline photos or camels.
- **Contact always in reach.** Phone and WhatsApp are visible on every screen, with a sticky bar on mobile.
- **Bilingual by design, not by translation.** Arabic is a first-class layout (RTL), not a flipped afterthought.

**Deliberately avoided:** stock handshake photos, gradient hero blobs, fake badges, carousels, counters that animate ("2,500 moves"), and any claim we cannot verify.

---

## 2. Colour tokens

Ratios below are calculated (WCAG 2.x), not estimated. AA needs 4.5:1 for body text, 3:1 for large text (≥24px, or ≥19px bold) and for UI component boundaries.

| Token | Hex | Role |
|---|---|---|
| `--c-ink` | `#0F1C26` | Primary text |
| `--c-ink-soft` | `#42535F` | Secondary text, captions |
| `--c-navy` | `#0C3B5C` | Brand primary: header, headings, primary buttons |
| `--c-navy-hover` | `#0A3149` | Hover/active for navy |
| `--c-teal` | `#0F6B6B` | Secondary brand: links, icon accents |
| `--c-amber` | `#E8A33D` | CTA accent (backgrounds only) |
| `--c-amber-hover` | `#C98A22` | CTA hover (background only) |
| `--c-amber-text` | `#8A5A00` | Amber as *text* on white (accessible variant) |
| `--c-sand` | `#F6F1E8` | Warm section surface |
| `--c-muted` | `#EEF2F5` | Cool section surface, disabled fills |
| `--c-white` | `#FFFFFF` | Page background |
| `--c-border` | `#6E7F8B` | Borders of interactive controls (inputs, outline buttons) |
| `--c-hairline` | `#DFE6EB` | Decorative dividers and card edges (non-interactive) |
| `--c-success` | `#12795A` | Success messages |
| `--c-error` | `#B3261E` | Error messages |

### Verified pairs
| Foreground | Background | Ratio | Use |
|---|---|---|---|
| ink `#0F1C26` | white | **17.29** | Body text |
| ink-soft `#42535F` | white | **7.97** | Secondary text |
| navy `#0C3B5C` | white | **11.69** | Headings, links |
| teal `#0F6B6B` | white | **6.30** | Links, icons |
| white | navy | **11.69** | Header, primary button label |
| navy | amber `#E8A33D` | **5.42** | **Primary CTA: navy label on amber** |
| ink | amber | **8.02** | Alternative CTA label |
| ink | sand | **15.37** | Text on warm sections |
| navy | sand | **10.39** | Headings on warm sections |
| ink-soft | muted | **7.08** | Secondary text on cool sections |
| amber-text `#8A5A00` | white | **5.93** | Amber used as text |
| success | white | **5.37** · error `#B3261E` on white **6.54** | Form messages |
| border `#6E7F8B` | white | **4.14** (needs ≥3) | Input borders |
| border | sand | **3.68** (needs ≥3) | Input borders on sand |
| amber-hover | navy | **3.98** | Large text / UI only |

### Colour rules
- **Never** amber text on white or sand (2.16 and 1.92 — both fail). Use `--c-amber-text`.
- Amber is reserved for the primary CTA and small highlights. If everything is amber, nothing is.
- Never use colour alone to carry meaning (form errors also get an icon and text).
- WhatsApp buttons use our `--c-success` green with the WhatsApp glyph, not WhatsApp's brand green as a background claim.

### Dark mode
Not in v1 (a mover site is overwhelmingly visited in daylight on mobile, and dark mode doubles the QA matrix for launch). The tokens are structured so it can be added later by redefining them under `prefers-color-scheme`.

---

## 3. Typography

**Latin: IBM Plex Sans. Arabic: IBM Plex Sans Arabic.** (Both SIL OFL, self-hosted.) They are one designed family, so the two languages look like one brand — which is the point of the bilingual site. Alternatives considered: Inter + Tajawal (two unrelated skeletons), and system fonts (free but generic and inconsistent across Arabic systems).

- Weights: **400** (body) and **600** (headings/buttons). No other weights.
- Format: `woff2`, subset (Latin basic + punctuation; Arabic + Arabic-Indic digits), `font-display: swap`.
- Preload only the weight used above the fold, per language: Latin 400 on `/`, Arabic 400 on `/ar/`.
- Fallbacks: `font-family: "IBM Plex Sans", system-ui, -apple-system, Segoe UI, Roboto, sans-serif;` and for `:lang(ar)`: `"IBM Plex Sans Arabic", "Segoe UI", Tahoma, sans-serif;`
- Budget: ≤ 100 KB of fonts per page (see performance strategy).

### Type scale (mobile → desktop, fluid via `clamp()`)
| Token | Mobile | Desktop | Use |
|---|---|---|---|
| `--fs-display` | 30px | 48px | Homepage H1 |
| `--fs-h1` | 27px | 38px | Page H1 |
| `--fs-h2` | 22px | 28px | Section headings |
| `--fs-h3` | 18px | 21px | Sub-headings, card titles |
| `--fs-body` | 17px | 17px | Body text |
| `--fs-small` | 15px | 15px | Captions, labels |
| `--fs-micro` | 13px | 13px | Legal, footnotes |

Line height: 1.25 headings, **1.65 body**. Arabic gets more room: `:lang(ar)` adds `line-height: 1.85` for body and `1.35` for headings, and bumps body size to 18px (Arabic letterforms need it). Measure: 60–75 characters (`max-width: 68ch`).

---

## 4. Spacing, radius, shadow, layout

**Spacing scale (4px base):** `4 · 8 · 12 · 16 · 24 · 32 · 48 · 64 · 96` → `--sp-1 … --sp-9`. Section padding: 48px mobile, 96px desktop. Gutter: 16px mobile, 24px+ desktop.

**Radius:** `--r-sm 6px` (inputs, small buttons) · `--r-md 12px` (cards) · `--r-lg 20px` (hero panels) · `--r-full 999px` (pills).

**Shadow:** `--sh-1 0 1px 2px rgb(15 28 38 / .06), 0 2px 8px rgb(15 28 38 / .06)` (cards) · `--sh-2 0 8px 24px rgb(15 28 38 / .12)` (sticky bar, dropdowns). No shadows on flat sections.

**Breakpoints:** `sm 480` · `md 768` · `lg 1024` · `xl 1280`. Mobile-first `min-width` queries only. Container: `max-width: 1200px`, centred, 16px side gutters (the 360px layout is the design baseline).

**Grid:** 12 columns on desktop, single column below 768px. Cards: 1 column mobile, 2 at 768, 3 at 1024.

---

## 5. Components

### Buttons
| Variant | Style | Use |
|---|---|---|
| Primary | amber background, **navy** label (5.42), radius `--r-sm`, 48px tall, 600 weight | "Get a Free Quote" |
| Secondary | navy background, white label (11.69) | "Call Now", secondary actions |
| WhatsApp | success green background, white label (5.37), WhatsApp glyph | "WhatsApp Us" |
| Outline | transparent, navy label, 1.5px `--c-border` | Tertiary ("See all services") |
| Link-button | teal text, underlined on hover | Inline actions |

Minimum target 48×48px. Icon-only buttons carry an `aria-label`. Disabled state: muted fill, ink-soft label, `aria-disabled` (never colour alone).

### Focus (critical for AA)
`:focus-visible` → 3px solid `--c-navy` outline with 2px offset on light backgrounds; on navy/dark surfaces the outline switches to `--c-amber` (5.42 on navy). Never `outline: none`.

### Cards
White surface, 1px `--c-hairline`, `--r-md`, `--sh-1`, 24px padding. Service card: icon (24px, teal), H3, one-line description, "Learn more" affordance — **the whole card is not a link**; the heading link is, so screen-reader users get a sensible link list.

### Forms (quote form is the money element)
- Labels always visible above the field, 600 weight, `--fs-small`.
- Input: 48px tall, 1.5px `--c-border`, `--r-sm`, 17px text (prevents iOS zoom), white fill.
- Focus: border navy + the focus ring.
- Help text under the field in ink-soft; error text in `--c-error` with a warning icon and `aria-describedby`; success in `--c-success`.
- Required fields marked with the word "required", not only an asterisk.
- Phone field: `inputmode="tel"`, accepts +971 and 05… formats, and Arabic-Indic digits are normalised.
- Date field: native `type="date"`.
- Error summary at the top of the form on submit, focus moved to it, each item linking to its field.

### Header
Height 64px mobile, 80px desktop. Left (LTR): wordmark. Right: nav, phone link, language switch, primary CTA. Sticky on scroll with `--sh-1`. On mobile: wordmark + call icon + menu button.

### Mobile navigation
Full-screen panel (not a cramped dropdown). `<button aria-expanded aria-controls>`, Escape closes, focus trapped while open and returned to the toggle on close, `inert`/`aria-hidden` on the page behind. Items: Services (expandable group), Areas, Guides, About, Contact, then Get a Free Quote, Call, WhatsApp, and the language switch.

### Sticky mobile CTA bar
Fixed at the bottom, 56px, two equal buttons: **Call Now** (navy) and **WhatsApp** (green). `padding-bottom: env(safe-area-inset-bottom)`. The page reserves the same height so nothing is hidden behind it, and it hides while the mobile menu or an open form field is in use. Not shown on desktop.

### Other components
- **Breadcrumbs:** small, ink-soft, `nav[aria-label="Breadcrumb"]`, current page not a link, RTL-mirrored separators.
- **Review card:** quote, name/initials, area, service, date, source. Never rendered without real data.
- **Project card:** image (4:3), title, location, property type, services used.
- **FAQ accordion:** native `<details>/<summary>` (works without JS), 48px targets, chevron rotates, `prefers-reduced-motion` respected.
- **Price-factor list:** icon + factor + one line — used instead of fake "from AED" prices.
- **Steps/process:** numbered ordered list, not a decorative timeline that breaks in RTL.
- **Language switch:** a link (not a select) to the same page in the other language, labelled "العربية" on English pages and "English" on Arabic ones, with `hreflang` and `lang`.

### Icons
A single inline SVG sprite, 24px grid, 1.5px strokes, `currentColor`. About 16 icons: phone, whatsapp, quote, box, sofa, building, villa, office, truck, storage, piano, tools, shield, clock, check, chevron. Decorative icons get `aria-hidden="true"`; meaningful ones get a `<title>`. No icon fonts, no third-party icon CDNs.

---

## 6. RTL / bilingual rules
- Layout uses logical properties only (`margin-inline`, `padding-inline`, `inset-inline`, `text-align: start`). No `left`/`right` in components.
- Mirrored in RTL: chevrons, arrows, breadcrumb separators, card "learn more" arrows, the header's logo/nav order (handled automatically by `dir`).
- Not mirrored: phone, WhatsApp and truck glyphs; numbers and `tel:` links stay Western digits.
- Arabic pages get the Arabic type tokens (18px body, 1.85 line height) and slightly larger buttons, since Arabic words run longer.
- The wordmark has an Arabic lockup (below) — it is not a flipped English mark.
- Every layout is checked at 360px in both `dir` values before development (Gate 3 item).

---

## 7. Logo / wordmark (no logo supplied)
The owner has not supplied a logo, so the default applies: a clean text wordmark, drawn as SVG, in both scripts.
- **English:** "AL QASIM" in IBM Plex Sans 600, navy, with "MOVERS" in letter-spaced 400 beneath, and a small amber "corner" glyph suggesting a box/route.
- **Arabic:** "القاسم" in IBM Plex Sans Arabic 600 with "لنقل الأثاث" beneath, the same amber glyph mirrored.
- Files: `src/images/logo/wordmark-en.svg`, `wordmark-ar.svg`, plus a square mark for the favicon and og fallback.
- Minimum size 120px wide; clear space = the height of the "A"; mono versions (all navy / all white) for dark backgrounds.
- If the owner later supplies a real logo, it replaces these files and the tokens stay unchanged.

---

## 8. Motion
Transitions 150–200ms `ease-out`, limited to colour, opacity and small transforms. No parallax, no counters, no carousels. Everything inside `@media (prefers-reduced-motion: reduce)` drops to `none`.

---

## 9. Imagery
- Real photos of the owner's crew, trucks and jobs are first choice; the register is `image-register.md`.
- Until real photos exist: **no fake "our team" imagery**. Pages use the illustration/icon system, colour panels and typography. Neutral licensed photos of Dubai buildings or boxes may be used only where they cannot be read as a claim about our own jobs, and each is logged with its licence.
- Aspect ratios: hero 16:9 (desktop) / 4:3 (mobile); cards 4:3; project 4:3. Always `width`/`height` set, AVIF/WebP, lazy below the fold.

---

## 10. Implementation notes (Stage 4)
Token file order: `reset → variables → base → layout → components → utilities`. Component CSS may not contain raw hex, px type sizes, or `left`/`right`. A QA check greps for those.
