# Security Rules

Applies to all agents; enforced by security-auditor.

## Secrets
- No API keys, tokens, passwords, SMTP credentials, or private endpoints in the repository — ever.
- Secrets live in the hosting provider's environment variables. `.env*` files are git-ignored.
- Run a secret scan before every deploy (Stage 11 QA script).

## Forms
- Validate on the client (UX) **and** in the server/form handler (security). Client validation is never trusted.
- Allow-list validation: UAE phone formats, reasonable email format, every text field length-capped.
- Spam protection: honeypot field + minimum time-to-submit. Add a CAPTCHA only if spam proves it necessary (it hurts conversion and adds a third-party script).
- Never reflect submitted values into HTML without escaping.
- The form-handler choice is documented in Stage 4, including data retention and privacy implications, and reflected in the Privacy Policy.

## Front-end
- No `innerHTML`, `document.write`, or `eval` with non-constant data.
- No open redirects: never redirect to a URL taken from a query parameter.
- External links in new tabs use `rel="noopener noreferrer"`.
- Third-party scripts: none by default. Each requires a documented reason and must be reflected in the CSP and the Cookie Policy.

## HTTP headers (target — test before enforcing; never break functionality)
- `Strict-Transport-Security: max-age=31536000; includeSubDomains` (add `preload` only once all subdomains are confirmed HTTPS)
- `X-Content-Type-Options: nosniff`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()`
- `Content-Security-Policy`: start from `default-src 'self'`; add only origins actually used (analytics, form handler, map). Ship as `Content-Security-Policy-Report-Only` first, then enforce.
- Clickjacking: `frame-ancestors 'none'` in CSP plus `X-Frame-Options: DENY` for older browsers.

## Process
- Security review is a gate before performance review and QA (see `docs/architecture/quality-gates.md`).
- Findings go in `docs/audits/security-audit.md` with severity, evidence, fix, and re-test result.
