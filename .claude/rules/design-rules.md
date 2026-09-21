# Design Rules

Applies to: ui-ux-designer, frontend-developer, conversion-optimizer.

## Brand attributes
The design must communicate: **Professional · Reliable · Modern · Local (Dubai) · Trustworthy · Fast · Easy to contact.**

## Principles
- Avoid generic template-looking design: no stock "handshake" imagery, no default framework look, no gratuitous gradients or animation.
- Real photography of Al Qasim Movers' own team, vehicles, and jobs is strongly preferred. Stock images, if used, must never imply they show the company's own work.
- Contact is never more than one tap away: header phone/CTA, mobile sticky bar (Call Now | WhatsApp), CTA sections.
- One design system: all colours, type sizes, spacing, radii, and shadows come from tokens in `docs/design/design-system.md`, implemented in `src/css/variables.css`.
- Mobile-first: design the 360px layout first, then scale up.
- Contrast: WCAG AA minimum (4.5:1 body text; 3:1 large text and UI components).
- Tap targets ≥ 44×44px. Visible focus state on every interactive element.
- Motion is optional and respects `prefers-reduced-motion`.
- Icons: inline SVG sprite, not an icon font. Meaningful icons have an accessible name; decorative icons are `aria-hidden="true"`.
- Bilingual: every component must work in LTR (English) and RTL (Arabic). Use CSS logical properties only, mirror directional icons, and design Arabic type tokens separately (see `bilingual-rules.md`). Test both directions at design review.
- Typography: one Latin family + one Arabic family (or one family covering both), no more; self-hosted, `font-display: swap`, subset.

## Required components (design-system deliverable)
Brand colours, typography, spacing, buttons, cards, forms, icons, header, footer, CTA system, mobile navigation, hero layout, section layouts, breadcrumbs, review card, project card, FAQ accordion, sticky mobile CTA bar.

## Review
Every design is reviewed against this file and `accessibility-rules.md` before development starts (Stage 3 gate).
