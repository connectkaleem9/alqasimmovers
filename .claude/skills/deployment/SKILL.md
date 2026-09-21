---
name: deployment
description: Deploy the static site and complete post-launch setup. Use in Stage 14.
---

# Deployment

Project: Al Qasim Movers — movers & packers, Dubai (https://alqasimmovers.com). Follow `CLAUDE.md` and the rules in `.claude/rules/`, especially `content-integrity-rules.md`.

## Instructions
1. Pre-deploy: all gates up to Stage 13 PASS; build clean; secret scan clean.
2. Hosting config: publish `dist/`; canonical host + HTTPS redirects; trailing-slash handling; custom 404 returning 404; security headers; cache rules; compression.
3. DNS: point domain; verify HTTPS certificate.
4. Post-launch: Google Search Console (domain property), submit sitemap, URL-inspect key pages; Google Analytics (consent-aware); update GBP website URL; verify indexing over following weeks.
5. Rollback plan: previous deploy can be restored in one step.

## Checklist
- [ ] Gates passed
- [ ] HTTPS + redirects
- [ ] Headers live
- [ ] 404 correct
- [ ] GSC verified + sitemap submitted
- [ ] Analytics firing
- [ ] GBP linked
- [ ] Rollback tested

## Expected Output
`docs/architecture/deployment-guide.md` + launch log in `CHANGELOG.md`.
