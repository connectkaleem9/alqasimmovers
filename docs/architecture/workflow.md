# Development Workflow

Every unit of work (a stage, a page, a feature) follows this sequence. Skipping a step requires a written reason in `PROJECT_MEMORY.md` → Decisions.

| Step | What happens | Responsible | Output |
|---|---|---|---|
| 1. Research | Gather facts: SERPs, competitors, user needs, owner facts | researcher agents | Notes with sources |
| 2. Document findings | Write findings into the relevant `docs/` file | researcher agents | Updated doc |
| 3. Create specification | SEO brief (`docs/seo/briefs/`) or feature spec; five-question test | seo-strategist / content-strategist / frontend-developer | Approved brief/spec |
| 4. Design | Layout/components against the design system | ui-ux-designer | Wireframe / component spec |
| 5. Review design | Design, accessibility, conversion review | accessibility-auditor, conversion-optimizer | Review notes |
| 6. Develop | Build in `src/`, run build | frontend-developer (+ content-writer for copy) | Working page in `dist/` |
| 7. SEO implementation | Meta, canonical, schema, links, breadcrumbs | technical-seo-auditor, schema-specialist, internal-linking-specialist | SEO checklist pass |
| 8. Security review | Forms, scripts, headers, secrets | security-auditor | Findings |
| 9. Performance review | CWV, weight, images | performance-engineer | Metrics |
| 10. QA | Crawl + manual tests | qa-engineer | Defect log |
| 11. Fix | Resolve defects | owning agent | Fix commits |
| 12. Re-test | Verify fixes, check for regressions | qa-engineer | Re-test log |
| 13. Approve | Gate review | final-reviewer + project-manager | APPROVED / CHANGES REQUIRED |
| 14. Next stage | Update memory + changelog | project-manager | Memory entry |

## Session protocol for Claude
1. Read `CLAUDE.md`, then `PROJECT_MEMORY.md`.
2. Confirm the current stage and its gate.
3. Pick the next task from PROJECT_MEMORY → Next Task.
4. Delegate to the correct agent / use the matching skill.
5. On finishing, update PROJECT_MEMORY and CHANGELOG.

## Branching (once hosting is connected)
- `main` = production. Work on short-lived branches (`stage-2/keyword-map`, `fix/quote-form-validation`), merged after the relevant gate items pass.
