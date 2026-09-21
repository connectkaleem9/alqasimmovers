# Indexability Register

Every page that is **not** indexable, and why. Anything not listed here must be indexable, canonical, and in the sitemap. The rules apply to both languages (`/` and `/ar/`).

| URL | Directive | Reason |
|---|---|---|
| /404.html | noindex | Error page (bilingual) |
| /get-a-quote/thank-you/ (+ /ar/) | noindex | Post-conversion page |
| /projects/ (+ /ar/) | noindex **until it lists real projects** | Avoid an empty/thin indexable page; switch to index once ≥ 3 real projects are published |
| /reviews/ (+ /ar/) | noindex **until it lists real reviews** | Same reason; switch to index once ≥ 5 real reviews are published |
| Any Arabic page without reviewed Arabic content | Not published at all | Never publish untranslated or unreviewed pages under /ar/ |
