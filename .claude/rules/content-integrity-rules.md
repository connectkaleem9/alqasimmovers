# Content Integrity Rules

The most important rule set on this project. Applies to every agent that writes anything a visitor or search engine will see.

## Never fabricate
Do not invent or imply any of the following unless the owner has supplied it and it is recorded as verified in `config/business.json` or `docs/research/owner-facts.md`:

- Reviews, testimonials, ratings, star counts, review counts
- Projects, case studies, before/after photos, client names or logos
- Prices, price ranges, "starting from" figures, discounts, or "free" offers (even "free quote" needs owner confirmation)
- Years in business, number of moves completed, team size, fleet size
- Licences, trade licence numbers, certifications, memberships, awards
- Insurance cover, guarantees, damage-free or on-time promises
- Business hours, physical address, same-day or 24/7 availability
- Services or areas the business does not actually provide

## Placeholders
In drafts, mark unknown facts as `[[OWNER-INPUT: description]]`. A QA check fails the build if any `[[OWNER-INPUT` string reaches `dist/`.

## Allowed without owner confirmation
- General, verifiable information about moving in Dubai (e.g. that many residential towers require a move permit from building management), phrased generally and, where specific, cited in the research docs.
- Final descriptions of the company's own process, equipment, and policies still require owner approval before publishing.

## Competitor content
Use competitors to understand intent and gaps. Never copy or closely paraphrase their text, images, or page structure.

## AI-assisted content
Every page is reviewed by content-strategist and final-reviewer for accuracy, usefulness, and originality before publishing. No bulk-generated articles.
