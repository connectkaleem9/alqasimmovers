# Local SEO Strategy

| Status | Owner | Stage | Date |
|---|---|---|---|
| Complete (v1) | local-seo-specialist | 2 | 2026-09-19 |

## Owner decisions (2026-09-19)
- **Service area:** all of Dubai, plus moves to and from every UAE emirate.
- **No Google Business Profile.** The business cannot appear in the Local Pack or Maps, so local visibility comes from organic results only. Reviews are collected on-site (with permission) or on other platforms. **Recommendation, recorded once:** create a GBP as a service-area business (the address can stay hidden) whenever the owner is ready. The site stays GBP-ready.
- **Bilingual:** area pages in English and Arabic. Arabic area names follow common usage: دبي مارينا، قرية جميرا الدائرية، الخليج التجاري، وسط مدينة دبي، نخلة جميرا، أبراج بحيرات جميرا، البرشاء، المرابع العربية، دبي هيلز، واحة دبي للسيليكون.

## NAP standard
| Element | Format (use exactly) |
|---|---|
| Name (EN) | Al Qasim Movers |
| Name (AR) | القاسم لنقل الأثاث |
| Address | Dubai, United Arab Emirates (service-area business; no street address published unless the owner shares one) |
| Phone (display) | +971 55 686 9224 |
| Phone (link) | tel:+971556869224 |
| WhatsApp | https://wa.me/971556869224 |

## LocalBusiness / MovingCompany data
`MovingCompany` with `areaServed`: Dubai (City) + the other emirates (AdministrativeArea). No `address.streetAddress`, `geo`, `openingHours`, or ratings until verified. Details in `schema-strategy.md` (Stage 4).

## Service ↔ area relationships (for linking and area content)
| Area type | Areas | Most relevant services |
|---|---|---|
| Towers | Dubai Marina, JLT, Business Bay, Downtown | Apartment, packing, furniture movers; office (Business Bay, JLT) |
| Mixed | JVC, Al Barsha, Dubai Hills, Silicon Oasis | Apartment, villa, home, furniture assembly |
| Villa communities | Palm Jumeirah, Arabian Ranches | Villa, packing, piano/heavy items, storage |

## Area page minimum bar
An area page is created only if **all** are true:
1. The area is served (all of Dubai is confirmed).
2. It has at least four genuinely area-specific, **cited** points (property types, tower/villa access, loading/parking, permit authority, typical move types).
3. It has unique FAQs that would not make sense on another area page.
4. It is not a near-duplicate of another area page.

## Area Validation table
Demand = autocomplete evidence (`data/autocomplete-2026-09-19.json`). "Unique info" = what we can document with citations in Stage 7. Area facts below are planning notes; each must be verified and cited before copy is written.

| Area | Served | Demand | Unique info available | Duplicate risk | Decision |
|---|---|---|---|---|---|
| Dubai Marina | Yes | ●●● (movers / villa / home / furniture / cheap movers in dubai marina) | High-rise density, service-lift booking, loading and parking constraints, many building managements | Low vs JLT if content stays specific | **Build** |
| JVC | Yes | ●●● (movers / villa / house / furniture movers in jvc) | Many mid-rise buildings with separate managers, townhouses, internal road layout | Low | **Build** |
| Business Bay | Yes | ●●● (incl. office movers in business bay) | Residential + commercial towers; office-move angle | Low (office angle) | **Build** |
| Downtown Dubai | Yes | ●● | Emaar-managed towers, Emaar permit process, event-day traffic | Medium vs Dubai Hills (both Emaar) → differentiate by tower vs villa | **Build** |
| Palm Jumeirah | Yes | ●● (movers in palm jumeirah) | Frond villas vs shoreline apartments, Nakheel permits, access/security | Low | **Build** |
| JLT | Yes | ●● (movers in jumeirah lakes tower) | DMCC-managed clusters, DMCC procedures, towers | Medium vs Marina → focus on DMCC process and clusters | **Build** |
| Al Barsha | Yes | ●●● (house / villa / furniture / office movers in al barsha) | Mix of villas (Barsha 1–3) and apartments (Barsha Heights), family moves | Low | **Build** |
| Arabian Ranches | Yes | ● (seen in competitor villa pages) | Gated villa community, Emaar community management, villa volumes | Medium vs Dubai Hills → pure villa focus | **Build** |
| Dubai Hills | Yes | ●● ("emaar move in permit dubai hills") | Villas + apartments, Emaar permit | Medium → mixed-type angle | **Build** |
| Silicon Oasis | Yes | ●● (house / villa movers in dubai silicon oasis) | Separate authority-managed area; villas + apartments | Low | **Build** |
| Jumeirah (1–3) | Yes | ●● (autocomplete mixes Jumeirah Park / Island / JVC / JLT) | Villas; ambiguous name clusters | High ambiguity | Defer |
| Deira | Yes | — | Older buildings, dense streets | — | Defer (needs real jobs) |
| Bur Dubai | Yes | — | Older buildings | — | Defer |
| Mirdif | Yes | — | Villas | — | Defer |
| Al Quoz | Yes | — | Warehouses/industrial → commercial angle | — | Defer |
| Al Nahda | Yes | — | Apartments near Sharjah border | — | Defer |
| International City | Yes | — | Low-rise clusters | — | Defer |
| Discovery Gardens | Yes | — | Low-rise clusters | — | Defer |

## Local citations (legitimate, Stage 14+)
dubizzle services, Yellow Pages UAE, Connect.ae, 2GIS Dubai, ServiceMarket and MoveAdvisor listings. The NAP must match the table above exactly.

## Reviews process (real customers only)
After each completed job, the crew or office sends a WhatsApp message asking for a short review and permission to publish it (name/initials, area, service, date). Published reviews go on `/reviews/` with their source. No incentives that breach platform policies, and no invented reviews. If a GBP is created later, send customers there as well.
