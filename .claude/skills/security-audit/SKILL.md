---
name: security-audit
description: Audit site and repo security. Use in Stage 11 and before each deploy.
---

# Security Audit

Project: Al Qasim Movers — movers & packers, Dubai (https://alqasimmovers.com). Follow `CLAUDE.md` and the rules in `.claude/rules/`, especially `content-integrity-rules.md`.

## Instructions
1. Secret scan the repo and `dist/` (keys, tokens, passwords, private URLs).
2. Review JS for XSS sinks (`innerHTML`, `eval`, `document.write`, unsafe URL handling) and open redirects.
3. Test forms with malicious input (script tags, SQL-like strings, very long input, header injection in email fields); confirm server-side validation and escaping.
4. List all third-party origins; justify each; ensure CSP covers exactly those.
5. Check response headers on staging (securityheaders.com or curl -I): HSTS, nosniff, Referrer-Policy, Permissions-Policy, CSP, frame-ancestors.
6. Confirm HTTPS everywhere, no mixed content.

## Checklist
- [ ] No secrets
- [ ] No XSS sinks with dynamic data
- [ ] Forms validated server-side
- [ ] Spam protection
- [ ] Headers present & tested
- [ ] CSP enforced without breakage
- [ ] HTTPS/no mixed content
- [ ] Third-party inventory

## Expected Output
`docs/audits/security-audit.md` with severity, evidence, fix, re-test.
