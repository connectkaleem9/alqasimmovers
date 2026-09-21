---
name: keyword-research
description: Build keyword clusters and map each cluster to one URL with role and intent labels. Use in Stage 2 or when adding a new page.
---

# Keyword Research

Project: Al Qasim Movers — movers & packers, Dubai (https://alqasimmovers.com). Follow `CLAUDE.md` and the rules in `.claude/rules/`, especially `content-integrity-rules.md`.

## Instructions
1. Collect seeds from the owner brief §7 and competitor pages.
2. Expand via autocomplete, PAA, related searches, and any keyword tool available; record the tool.
3. Cluster by SERP overlap: keywords that return mostly the same top results belong to one page.
4. Label each keyword: Primary / Secondary / Supporting; Commercial / Transactional / Informational / Local.
5. Map each cluster to exactly one URL; check no URL shares a primary keyword with another.
6. Park clusters for unconfirmed services/areas under 'Pending owner confirmation'.

## Checklist
- [ ] Every keyword has role + intent
- [ ] Every cluster maps to one URL
- [ ] No cannibalisation between URLs
- [ ] Volumes only with source
- [ ] Pending list for unconfirmed services/areas

## Expected Output
`docs/seo/keyword-map.md` table: Cluster | Primary | Secondary | Supporting | Intent | Target URL | Status | Notes.
