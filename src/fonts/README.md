# Fonts

| File | Family | Weight | Size | Licence |
|---|---|---|---|---|
| `ibm-plex-sans-400.woff2` | IBM Plex Sans (Latin) | 400 | 22 KB | SIL OFL 1.1 |
| `ibm-plex-sans-600.woff2` | IBM Plex Sans (Latin) | 600 | 24 KB | SIL OFL 1.1 |
| `ibm-plex-sans-arabic-400.woff2` | IBM Plex Sans Arabic | 400 | 42 KB | SIL OFL 1.1 |
| `ibm-plex-sans-arabic-600.woff2` | IBM Plex Sans Arabic | 600 | 45 KB | SIL OFL 1.1 |

Source: the Fontsource distribution of IBM Plex (`cdn.jsdelivr.net/fontsource`), downloaded 2026-09-20. The fonts are **self-hosted** — no Google Fonts or other third-party font request is made at runtime, which keeps the CSP tight and avoids a third-party connection.

Per-page cost: English pages load 22 KB (400) and, only where a heading needs it, 24 KB (600). Arabic pages load 42 KB + 45 KB. Arabic is heavier by nature; the performance budget accounts for it. Only the 400 weight of the page's own language is preloaded.

IBM Plex is licensed under the SIL Open Font License 1.1, which permits commercial use and self-hosting. Keep this note with the files.
