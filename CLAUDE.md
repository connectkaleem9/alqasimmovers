# CLAUDE.md — Al Qasim Movers

This file is the entry point for every Claude session on this project. Read it fully, then read
`PROJECT_MEMORY.md` before doing any work. The original owner brief lives at
`Al_Qasim_Movers_Complete_Project_Plan (1).txt` and is the source of truth for scope.

## Project Identity

| Field | Value |
|---|---|
| Project Name | Al Qasim Movers (Arabic: القاسم لنقل الأثاث) |
| Domain | https://alqasimmovers.com |
| Business | Movers & Packers |
| Location | Dubai, UAE |
| Phone (local) | 0556869224 |
| Phone (international) | +971 55 686 9224 |
| Phone link | `tel:+971556869224` |
| WhatsApp link | `https://wa.me/971556869224` |
| Languages | **English (default, `/`) + Arabic (`/ar/`, RTL)** — see `.claude/rules/bilingual-rules.md` |
| Service area | All of Dubai; moves to/from every UAE emirate. Storage via partner. Same-day moves conditional. No international moves. |
| Google Business Profile | None (owner decision: proceed without; keep site GBP-ready) |
| Technology | HTML5, CSS3, vanilla JavaScript |
| Primary Objective | Local SEO + Organic Traffic + Lead Generation |

All business facts come from `config/business.json`. Never hard-code a fact that is not in that
file, and never add a fact to that file without owner confirmation (see `docs/owner-questions.md`).

## Current Stage

See `PROJECT_MEMORY.md` → "Current Stage" and `docs/roadmap.md`. Do not start a stage until the
previous stage's quality gate in `docs/architecture/quality-gates.md` has passed.

## Non-Negotiable Rules

1. **Do not invent business information.** No fabricated reviews, projects, prices, ratings,
   certifications, awards, guarantees, years in business, team sizes, fleet sizes, insurance
   claims, addresses, or opening hours. If a fact is not in `config/business.json` with
   `verified: true`, it does not appear on the site. Use a clearly marked placeholder
   (`[[OWNER-INPUT: ...]]`) in drafts instead — and never ship a placeholder.
2. **Do not create thin SEO pages.** Every indexable page must pass the five-question test in
   `.claude/rules/seo-rules.md`. Area pages require evidence the area is served plus genuinely
   unique local content.
3. **Do not modify completed work** unless required by a documented bug, security, SEO,
   accessibility, or performance issue, or an explicit requirement. Inspect before changing.
4. **Do not rewrite working systems** unnecessarily.
5. **Research → document → specify → design → review → build → SEO → security → performance →
   QA → fix → re-test → approve.** No skipping steps (see "Workflow" below).
6. **No production website code before Stage 4/5.** Stages 1–3 produce documentation and designs.

Full rule sets:

- `.claude/rules/development-rules.md`
- `.claude/rules/seo-rules.md`
- `.claude/rules/security-rules.md`
- `.claude/rules/design-rules.md`
- `.claude/rules/content-integrity-rules.md`
- `.claude/rules/accessibility-rules.md`
- `.claude/rules/bilingual-rules.md`

## Development Rules (summary)

- Bilingual EN/AR: `/ar/` subfolder, same slugs, reciprocal hreflang, `dir="rtl"`, CSS logical properties, Arabic written for Arabic searchers (never raw machine translation).

- Semantic HTML5; one `<h1>` per page; logical H2/H3 hierarchy.
- Mobile-first CSS; design tokens in `src/css/variables.css`.
- Vanilla JavaScript; add a library only when genuinely required and documented in
  `docs/architecture/architecture.md` → "Dependency Register".
- No frameworks, no bloated dependencies, no unnecessary third-party scripts.
- Accessible markup (WCAG 2.2 AA target); ARIA only when native HTML cannot do the job.
- SEO-friendly, lowercase, hyphenated, trailing-slash URLs (e.g. `/services/villa-movers-dubai/`).
- Reusable partials for header, footer, breadcrumbs, CTA, review card.
- Consistent design system (`docs/design/design-system.md`).
- Fast page loading (LCP < 2.5s, CLS < 0.1, INP < 200ms).
- Secure forms: client + server validation, honeypot, no secrets in the repo.
- No inline secrets, API keys, or credentials anywhere in the repository.

## Architecture (summary)

Full detail and reasoning: `docs/architecture/architecture.md`.

```
/                      project root (docs, tooling — NOT deployed)
├── CLAUDE.md, PROJECT_MEMORY.md, CHANGELOG.md, README.md
├── .claude/           agents/, skills/, rules/
├── config/            business.json (single source of business truth)
├── docs/              research/, seo/, design/, architecture/, audits/, changelog/
├── src/               website source (Stage 4+)
│   ├── partials/      header, footer, breadcrumbs, cta, review-card
│   ├── pages/         one folder per URL → becomes /<path>/index.html
│   ├── css/  js/  images/  fonts/  static/ (robots.txt, favicon, etc.)
├── scripts/           zero-dependency Node build + QA scripts (Stage 4+)
└── dist/              build output = deployable web root (git-ignored)
```

Key decision: the plan's suggested `pages/` folder would produce `/pages/services/...` URLs, which
conflicts with the required `/services/...` URLs. We therefore use `src/pages/` as source and a
small zero-dependency Node build step that assembles partials and business config into `dist/`
with the exact public URL structure. Headers/footers are rendered at build time (not via
client-side JS includes) so crawlers see full HTML. See ADR-001 in the architecture doc.

## Agents

Specialised agents live in `.claude/agents/`. Use the right agent for the job; the
`project-manager` agent coordinates stages and gates.

project-manager · business-researcher · competitor-researcher · keyword-researcher ·
seo-strategist · local-seo-specialist · content-strategist · content-writer · ui-ux-designer ·
frontend-developer · technical-seo-auditor · schema-specialist · internal-linking-specialist ·
performance-engineer · accessibility-auditor · security-auditor · conversion-optimizer ·
qa-engineer · deployment-engineer · final-reviewer

## Skills

Reusable procedures live in `.claude/skills/<name>/SKILL.md`, each with instructions, a
checklist, and an expected output:

seo-research · keyword-research · competitor-research · local-seo · content-strategy ·
technical-seo · ui-ux-design · frontend-development · accessibility · performance ·
security-audit · schema-markup · internal-linking · image-optimization ·
conversion-optimization · qa-testing · deployment

## Workflow

```
Research → Document findings → Create specification → Design → Review design → Develop →
SEO implementation → Security review → Performance review → QA → Fix → Re-test → Approve →
Next stage
```

Never generate the whole website in one pass and assume it is correct.

## Conversion Rules (every important page)

- Primary CTA: **Get a Free Quote** → `/get-a-quote/`
- Secondary CTA: **WhatsApp Us** → `https://wa.me/971556869224`
- Mobile sticky bar: **Call Now | WhatsApp** (`tel:+971556869224`)
- Quote page primary button text: **Request a Free Moving Quote**

## Documentation Duties

After every major stage, update:

1. `PROJECT_MEMORY.md` — Completed, Pending, Decisions, Changes, Known Issues, Next Task.
2. `CHANGELOG.md` — dated entry.
3. Any doc in `docs/` affected by the work.

If a session is interrupted, the next session must read `PROJECT_MEMORY.md` first.

## Owner Inputs

The owner has asked **not to be asked repeated questions**. Decide from the plan file and use the
conservative defaults in `config/business.json` → `defaults`; document each decision. Facts that
cannot be defaulted without inventing them (reviews, projects, hours, address, email) are simply
left off the site until the owner shares them — see `docs/owner-questions.md`. Never send the owner
question lists.
