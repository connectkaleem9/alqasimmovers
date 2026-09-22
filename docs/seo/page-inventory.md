# Page Inventory — First Release

| Status | Owner | Stage | Date |
|---|---|---|---|
| Complete (v1) | seo-strategist | 2 | 2026-09-19 |

- **Count:** 42 English content pages, plus a noindex thank-you page and the 404. **40 are indexable at launch**: Projects and Reviews stay noindex until they hold real items. Every page has an Arabic counterpart under `/ar/`, giving about 84 content URLs.
- **Arabic publishing rule:** an Arabic page is published only once its Arabic content is written and reviewed.
- **404:** one bilingual `/404.html` covers both languages.
- **Five-question test:** each page's full answers go into its SEO brief (`briefs/<slug>.md`), written in Stage 5–8 before copy. The "Why it exists" column is the short answer.

## Core (10)
| URL | Type | Primary keyword (EN / AR) | Why it deserves to exist | Index |
|---|---|---|---|---|
| `/` | Home | movers and packers dubai / نقل اثاث دبي | Brand entry point; routes users to services, areas and a quote | ✔ |
| `/about/` | About | Al Qasim Movers | Trust: who we are and how we work (only verified facts) | ✔ |
| `/services/` | Hub | moving services dubai | Overview and routing to all services | ✔ |
| `/areas/` | Hub | movers across dubai | Routes to area pages; states "all of Dubai + every emirate" | ✔ |
| `/projects/` | Proof | — | Real completed jobs only. Launches with an honest "coming soon" state if empty; **noindex until it has real items** | noindex until populated |
| `/reviews/` | Proof | Al Qasim Movers reviews | Real reviews only; same rule as projects | noindex until populated |
| `/faq/` | Support | moving questions dubai | Answers pre-sale objections; supports every service page | ✔ |
| `/get-a-quote/` | Conversion | get a moving quote dubai | Primary conversion page | ✔ |
| `/contact/` | Conversion | contact movers dubai | Phone, WhatsApp, form | ✔ |
| `/blog/` | Hub | moving guides dubai | Hub for the guides | ✔ |

## Services (14 URLs)
| URL | Primary (EN) | Primary (AR) | Why it exists (distinct intent) |
|---|---|---|---|
| `/services/home-movers-dubai/` | home movers dubai | نقل اثاث منازل دبي | Household moves in general (house movers, local moves) |
| `/services/apartment-movers-dubai/` | apartment movers dubai | نقل اثاث شقق دبي | Tower moves: permits, service lifts, loading bays; studios included |
| `/services/villa-movers-dubai/` | villa movers dubai | نقل اثاث فلل دبي | Large-volume moves, gated communities, outdoor/garden items |
| `/services/office-movers-dubai/` | office movers dubai | نقل اثاث مكاتب دبي | Business moves: out-of-hours, IT care, downtime |
| `/services/commercial-movers-dubai/` | commercial movers dubai | نقل محلات ومستودعات دبي | Shops, restaurants, warehouses, stock and fixtures |
| `/services/furniture-movers-dubai/` | furniture movers dubai | نقل قطع اثاث دبي | Furniture-only and single-item moves |
| `/services/packing-services-dubai/` | packing services dubai | تغليف اثاث دبي | Packing & unpacking as a service, materials, fragile items |
| `/services/furniture-assembly-dubai/` | furniture assembly dubai | فك وتركيب اثاث دبي | Dismantling/assembly + handyman (curtains, TV mounting) |
| `/services/storage-services-dubai/` | storage movers dubai | تخزين اثاث دبي | Move-plus-store via **partner** storage |
| `/services/inter-emirate-movers/` | inter emirate movers uae | نقل اثاث بين الامارات | Moves to all emirates (Ajman, RAK, Fujairah, UAQ, Al Ain) |
| `/services/movers-dubai-to-abu-dhabi/` | movers dubai to abu dhabi | نقل اثاث من دبي الى ابوظبي | Route page: distance, timing, both-end permits, utilities |
| `/services/movers-dubai-to-sharjah/` | movers dubai to sharjah | نقل اثاث من دبي الى الشارقة | Route page: busiest cross-emirate corridor, timing, permits |

14 service URLs: 11 service pages + the inter-emirate hub + 2 route pages. Within the Stage 6 target of 12–15.

**Withdrawn 2026-09-22 (owner decision):** `/services/piano-movers-dubai/` and `/services/pickup-truck-with-driver-dubai/` were published, then removed at the owner's request. Both URLs 301 to `/services/`. Heavy items and truck-with-driver work are no longer offered as their own pages; do not re-create them without a new owner decision.

**Not created (merged; documented in keyword-map):** studio-movers-dubai, unpacking-services-dubai, loading-unloading-dubai, local-moving-dubai. No same-day page.

## Areas (10) — `/areas/{slug}/`
Chosen for demand (autocomplete) × distinct moving realities. Validation detail is in `local-seo-strategy.md`.

| URL | Primary (EN) | Property mix / why unique |
|---|---|---|
| `/areas/dubai-marina/` | movers in dubai marina | High-rise towers; service-lift slots; tight loading and parking along the Marina walk |
| `/areas/jvc/` | movers in jvc | Mixed low/mid-rise apartments + townhouses; many separate building managements |
| `/areas/business-bay/` | movers in business bay | Residential and office towers side by side; office moves + apartment moves |
| `/areas/downtown-dubai/` | movers in downtown dubai | Emaar-managed towers; Emaar permit route |
| `/areas/palm-jumeirah/` | movers in palm jumeirah | Villas on fronds + shoreline apartments; Nakheel permit route; single access road |
| `/areas/jlt/` | movers in jumeirah lakes towers | DMCC-managed towers in clusters; DMCC process |
| `/areas/al-barsha/` | movers in al barsha | Mixed villas and apartments; high family demand |
| `/areas/arabian-ranches/` | villa movers in arabian ranches | Gated villa community; Emaar-managed |
| `/areas/dubai-hills/` | movers in dubai hills | Villas + apartments; Emaar permit ("emaar move in permit dubai hills" autocompletes) |
| `/areas/silicon-oasis/` | movers in dubai silicon oasis | Separate free-zone-style authority area; villas + apartments |

Deferred (not rejected; add when real jobs or Search Console data give unique content): Jumeirah 1–3, Deira, Bur Dubai, Mirdif, Al Quoz, Al Nahda, International City, Discovery Gardens.

## Blog — launch set (5)
| URL | Primary (EN) | AR at launch? | Supports |
|---|---|---|---|
| `/blog/move-in-move-out-permit-dubai/` | move in permit dubai | Yes | Apartment, villa, all area pages |
| `/blog/moving-cost-dubai/` | moving cost dubai | Yes (اسعار نقل اثاث دبي) | All services, quote page |
| `/blog/moving-checklist-dubai/` | moving house in dubai checklist | Yes | Home, apartment, villa |
| `/blog/how-to-choose-movers-dubai/` | how to choose a moving company in dubai | Later | Home, about, reviews |
| `/blog/office-relocation-checklist-dubai/` | office relocation checklist dubai | Later | Office, commercial |

Later (content calendar): villa move preparation, how to pack furniture. The brief's separate "apartment checklist" is merged into the main checklist to avoid cannibalisation.

## Legal & utility
| URL | Index |
|---|---|
| `/privacy-policy/`, `/terms-and-conditions/`, `/cookie-policy/` | ✔ (low priority in sitemap) |
| `/get-a-quote/thank-you/` | noindex |
| `/404.html` | noindex, HTTP 404 |

## Totals (English)
| Group | Pages |
|---|---|
| Core | 10 |
| Services | 14 |
| Areas | 10 |
| Blog posts | 5 |
| Legal | 3 |
| **Total** | **42** (+ thank-you, + 404). Indexable at launch: 40 |

The plan targets 25–40. We are slightly over because the inter-emirate routes carry proven demand in both languages. The 404 page is extra, and the Projects and Reviews pages stay noindex until they have real content.
