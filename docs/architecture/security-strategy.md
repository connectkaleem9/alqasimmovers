# Security Strategy

| Status | Owner | Stage | Date |
|---|---|---|---|
| Implemented at build level; audit in Stage 11 | security-auditor | 4 / 11 | 2026-09-20 |

## Threat model
A static marketing site on Hostinger shared hosting with two public forms, a PHP handler and a MySQL table of leads. The realistic risks are: SQL injection, mail-header injection, form spam, leaked database credentials, exposure of stored personal data, and clickjacking or sniffing through missing headers. There is no login and no user-generated content on the site itself.

## Controls in place
| Risk | Control |
|---|---|
| Database credentials | Stored in `/home/<user>/private/alqasim-config.php`, **above `public_html`**, so the web server cannot serve it even if PHP stops executing. Only `config.sample.php` (no real values) is in the repo. `scripts/secret-scan.mjs` (8 credential patterns + `.env` check) runs in `npm run check`. |
| SQL injection | PDO with `ATTR_EMULATE_PREPARES => false`; every value is bound, never concatenated. Allow-lists for property type and services. |
| Mail-header injection | All control characters, including CR and LF, are stripped from every field before use; mail headers are built from configured constants only, never from visitor input. Covered by unit tests. |
| Stored personal data | Only what the customer typed plus a **salted hash** of their IP — never the raw address. `utf8mb4` so Arabic is stored intact. Retention policy to be set with the owner in Stage 8. |
| PHP file exposure | `.htaccess` denies `*.sql`, `*.md`, `*.json` (except the web manifest), `*.log`, `*.ini`, `*.sample.php` and every dotfile; directory listing is off. |
| XSS | No `innerHTML` with dynamic data anywhere; `forms.js` builds the error summary with `createElement`/`textContent`. The build escapes every business value it injects. |
| Form spam | Honeypot field, JS timestamp (sub-3s submissions rejected), and a per-IP rate limit of 5 per 10 minutes enforced in the database. No CAPTCHA at launch. |
| Untrusted redirects | The handler only ever redirects to two fixed, language-derived paths; no user value reaches `Location`. `.htaccess` holds static 301s only. |
| Third-party code | None. Zero external scripts, fonts, or styles — everything is self-hosted, which also keeps the CSP tight. |
| Clickjacking | `X-Frame-Options: DENY` + CSP `frame-ancestors 'none'`. |
| MIME sniffing | `X-Content-Type-Options: nosniff`. |
| Transport | HTTPS forced, HSTS one year with `includeSubDomains`. `preload` only after all subdomains are confirmed HTTPS. |
| Tab-nabbing | Every `target="_blank"` carries `rel="noopener noreferrer"`; the QA crawl fails the build if one does not. |

## CSP rollout
Shipping as **Report-Only** first (in `.htaccess`):
```
default-src 'self'; img-src 'self' data:; style-src 'self'; script-src 'self';
font-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'self'; object-src 'none'
```
No `unsafe-inline` is needed because there are no inline styles or scripts (the JSON-LD block is `application/ld+json`, which CSP does not treat as a script source). Plan: deploy Report-Only → watch reports for a week → switch to enforcing. If analytics is added later, its origin is added deliberately, never with a wildcard.

## Stage 11 audit plan
1. `npm run scan` and a manual grep for keys; confirm the live config file is outside `public_html` with permissions 600.
2. Submit malicious input to both forms (script tags, very long strings, header-injection attempts in the email field) and confirm nothing is reflected and the submission is captured safely.
3. Check live headers with `curl -I` and securityheaders.com.
4. Confirm no mixed content and no unexpected outbound requests (DevTools network panel, "third-party" filter).
5. Try to fetch `/form/config.sample.php`, `/db/schema.sql` and `/.htaccess` over the web — all must return 403/404.
6. Confirm the rate limit works (six rapid submissions from one address) and that the honeypot and speed checks store nothing.
7. Re-test after switching CSP to enforcing.
