# Accessibility Rules

Target: **WCAG 2.2 Level AA.**

- Semantic HTML first; ARIA only when no native element does the job.
- Every form control has a visible `<label>`; errors are announced (`aria-describedby` + text, never colour alone).
- Skip link to `<main>` on every page.
- Logical heading order; one `<h1>`.
- Everything keyboard-operable; no keyboard traps; visible `:focus-visible` style with ≥ 3:1 contrast.
- Colour contrast AA (4.5:1 text, 3:1 large text and UI).
- Meaningful images have descriptive `alt`; decorative images use `alt=""`.
- Link text makes sense out of context (no "click here"; "Read more" needs a unique accessible name).
- Mobile nav: `<button>` with `aria-expanded` and `aria-controls`; Escape closes it; focus returns to the toggle.
- Tap targets ≥ 44×44px; the sticky CTA bar must never cover content or the focused element.
- Respect `prefers-reduced-motion`.
- `lang` and `dir` set on `<html>` (`en`/`ltr`, `ar`/`rtl`); inline other-language fragments carry their own `lang`; unique, descriptive `<title>` per language.
- Test Arabic pages with NVDA using an Arabic voice; confirm reading order and focus order follow RTL.
- Test with keyboard only, NVDA screen reader, axe/Lighthouse, and 200% zoom.
