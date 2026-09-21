# Bilingual Rules (English + Arabic)

The site is published in **English (default)** and **Arabic** (owner decision 2026-09-19). The architecture is in ADR-006 (`docs/architecture/architecture.md`).

## URLs
- English at the root: `/services/villa-movers-dubai/`
- Arabic under `/ar/` with the **same slug**: `/ar/services/villa-movers-dubai/`
- Every page has exactly one counterpart in the other language, or none if the Arabic version is not ready. Never publish an English page under `/ar/`.

## hreflang & canonicals
- Every page with a counterpart declares `hreflang="en"`, `hreflang="ar"`, and `hreflang="x-default"` (pointing to English). The annotations are reciprocal and use absolute URLs.
- Each language version has a self-referencing canonical. Never canonicalise Arabic to English.
- `sitemap.xml` lists both versions with `xhtml:link` alternates.

## Markup
- English: `<html lang="en" dir="ltr">`. Arabic: `<html lang="ar" dir="rtl">`.
- A text fragment in the other language gets its own `lang` (and `dir` if needed), e.g. the English brand name inside Arabic text.
- The language switcher links to the **equivalent page**, not to the home page. It uses `hreflang` and `lang` attributes and shows each language's name in that language ("English" / "العربية").

## CSS
- Use CSS logical properties everywhere (`margin-inline-start`, `padding-inline-end`, `inset-inline-start`, `text-align: start`). No `left`/`right` in component CSS.
- Mirror directional icons (arrows, chevrons) in RTL. Leave non-directional icons (phone, WhatsApp) alone.
- One stylesheet serves both directions. Language-specific rules are limited to font stacks and line-height, using `:lang(ar)`.

## Typography
- A dedicated, self-hosted Arabic typeface, subset to Arabic + Latin punctuation (chosen in Stage 3; weight/performance budget applies).
- Arabic needs a larger line-height and often a slightly larger size. Tokens are defined per language.

## Content
- Arabic content is **written for Arabic searchers**, not machine-translated word for word. Arabic users search differently (e.g. "نقل اثاث دبي", "فك وتركيب اثاث"), so Arabic pages get Arabic keyword research and their own titles and meta descriptions.
- Machine translation may be a starting draft only. Every Arabic page is reviewed by a fluent Arabic reviewer before publishing (reviewer named in `docs/owner-questions.md`).
- Business facts are the same in both languages and come from `config/business.json`. The Arabic business name is published only once `nameArabic` is verified.
- Numbers and phone: display with Western digits (0–9) in both languages for consistency with `tel:` links. Forms accept both Western and Arabic-Indic digits (٠–٩) and normalise them.

## Forms
- Labels, placeholders, validation messages, success and error pages in both languages.
- Submissions record the language they came from.

## Schema
- JSON-LD on each page uses that page's language for text values and `inLanguage`. The business `@id` is the same across languages.

## QA
- Every template is checked in both directions (LTR and RTL): layout, icons, form, mobile menu, sticky CTA bar.
- hreflang reciprocity is checked automatically at build time. The build fails if a pair doesn't match.
