# Conversion Plan

| Status | Owner | Stage | Date |
|---|---|---|---|
| Complete (v1) | conversion-optimizer | 3 | 2026-09-20 |

## Conversion goals, in priority order
1. **WhatsApp message** — the lowest-friction action in this market, and the owner's fastest reply channel.
2. **Phone call** — high intent, especially on mobile and for urgent/same-day jobs.
3. **Quote form submit** — best for detailed jobs (villa, office) and for capturing leads out of hours.

## CTA system (site-wide)
| Slot | Action | Wording (EN / AR) |
|---|---|---|
| Header (desktop) | Primary | Get a Free Quote / احصل على عرض سعر مجاني |
| Header (mobile) | Call icon + menu | ☏ / — |
| Hero (every page) | Primary + secondary | Get a Free Quote · WhatsApp Us / واتساب |
| Mid-page CTA card | Contextual | e.g. "Not sure what a villa move costs? WhatsApp us your floor plan." |
| CTA band before footer | Primary + call | Get a Free Quote · Call +971 55 686 9224 |
| Sticky mobile bar | Call + WhatsApp | Call Now / اتصل الآن · WhatsApp / واتساب |
| Quote page submit | Primary | Request a Free Moving Quote / اطلب عرض سعر مجاني |

"Free quote" wording is confirmed by the owner brief (§16, §19).

## Page-level placement rules
- A contact action is visible **within the first screen** on every page, in both languages.
- Long pages (service, area, guide) carry a mid-page CTA card after the first or second H2 — never more than two body CTAs, so the page does not feel like a sales funnel.
- Every FAQ answer that ends in "it depends" ends with a WhatsApp link.
- The cost guide carries a CTA after the price-factor table, where intent peaks.

## Quote form specification
- **Required: name + phone only.** Everything else optional. Each extra required field loses leads, and the phone number alone is enough to quote in this market.
- Fields: name, phone, moving from, moving to, property type, moving date, services needed (checkboxes from the confirmed service list), number of rooms, message.
- One page, no multi-step wizard. Native inputs, no date-picker library.
- Spam: hidden honeypot + minimum time-to-submit. No CAPTCHA unless spam proves it necessary.
- Validation: inline, on blur and on submit; error summary at the top with focus moved to it.
- Success: `/get-a-quote/thank-you/` (noindex) stating what happens next, plus a WhatsApp button for anyone in a hurry. No invented response-time promise until the owner confirms one.
- The form states which language it was submitted in, so replies come in the right language.

## Trust elements (only what is true)
| Shown at launch | Not shown (until verified) |
|---|---|
| All of Dubai + every emirate | Years in business, number of moves |
| Dismantling and reassembly available | Star ratings, review counts |
| Packing and unpacking available | Insurance cover, guarantees |
| Storage through a partner facility | Trade licence number |
| Quote over WhatsApp | Awards, certifications |
| Site in English and Arabic | Team/fleet size |
| Clear inclusions and exclusions | "Same-day guaranteed" (only "subject to availability") |

As real reviews, projects and photos arrive, they slot into the existing review-card and project-card components, and the Reviews/Projects pages switch from noindex to index.

## Objection handling (mapped to page sections)
| Objection | Where it is answered |
|---|---|
| "Will the price change on the day?" | Price-factor section + FAQ: what changes a quote |
| "What if something breaks?" | FAQ, written with the owner at Stage 8 — no invented insurance claim |
| "Do I need a permit?" | Area pages + the permit guide |
| "Can you come today?" | Honest same-day wording + WhatsApp |
| "Do you dismantle and reassemble?" | Assembly service page + FAQ |
| "Do you cover my area/emirate?" | Area pages, route pages, areas hub |

## Analytics events (consent-aware, Stage 4/14)
| Event | Trigger | Parameters |
|---|---|---|
| `quote_submit` | Successful form submit | `lang`, `page_type`, `services[]` |
| `tel_click` | Any `tel:` link | `lang`, `placement` (header/sticky/band) |
| `whatsapp_click` | Any wa.me link | `lang`, `placement` |
| `form_error` | Validation blocked submit | `field` |

Review monthly: which pages and which placements produce contacts, and which form fields cause drop-off.

## Measurement caveat
Until Analytics is live, the only lead signal is the owner's phone and WhatsApp. Ask the owner to note "how did you find us" where practical.
