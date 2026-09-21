# Service Catalogue Research — What Dubai Movers & Packers Offer

| Status | Owner | Stage | Date |
|---|---|---|---|
| Complete (v1) | business-researcher | 2 | 2026-09-19 |

**Why this exists:** On 2026-09-19 the owner asked us to identify the services a movers & packers company in Dubai offers, and to base Al Qasim's service list on the standard market offering. This document records what the market offers (with sources), then sorts those services into tiers that decide what Al Qasim publishes.

## 1. Market findings (English-language competitors)

Services that show up on most Dubai mover websites and marketplaces:

| Category | Services seen in market | Seen on |
|---|---|---|
| Residential | Home / house moving, apartment moving (studio, 1–3+ BR), villa moving, furniture-only moves, single-item moves | Super Movers, ServiceMarket, Crown, Mr Packer, Movers E Dubai, Sana Movers |
| Commercial | Office relocation, corporate moves, warehouse moving, equipment transport | Super Movers, Crown, E Home Movers |
| Packing | Packing with materials (boxes, bubble wrap, blankets), unpacking, fragile/specialty packing | ServiceMarket, Crown, Super Movers |
| Furniture | Disassembly & reassembly, furniture installation | All major results |
| Transport | Loading / unloading, local transport, pickup-truck rental | Super Movers, ServiceMarket |
| Storage | Short/long-term storage, warehousing, climate-controlled, store-by-the-box | Crown, Super Movers, Mr Packer, BBC Movers |
| Handyman add-ons | Curtain/blind installation, TV mounting, picture/mirror hanging, light fixtures, appliance installation | Crown, Super Movers, Tamam Movers, Al Mas Movers |
| Specialty items | Pianos, heavy items, artwork, safes | Sana Movers, Handyman Dubai, ServiceMarket |
| Scope | Local (within Dubai), inter-emirate (long-distance), international | Super Movers, BBC Movers |
| Urgency | Same-day / emergency moves | Super Movers |
| Other add-ons (usually partner-delivered) | Deep cleaning, painting, pest control, lady packers | Crown, ServiceMarket |

Market-level context (not Al Qasim prices, never to be published as ours): ServiceMarket lists marketplace ranges of roughly AED 1,000–1,200 for a studio, AED 1,200–1,500 for 1 BR, AED 1,800–2,500 for 2 BR, AED 4,000–5,000 for a 3 BR villa. The usual payment terms are 50% on confirmation and the balance on moving day. Recorded only to understand customer price expectations.

## 2. Market findings (Arabic-language competitors)

Arabic-language Dubai mover sites describe the offer as **نقل اثاث / نقل عفش** (furniture/household moving) with the fixed sequence **فك – تغليف – نقل – تركيب** (dismantle, pack, move, reassemble), for **شقق، فلل، مكاتب** (apartments, villas, offices), often adding **تركيب ستائر** (curtain installation). Arabic searchers describe the job this way rather than translating "movers and packers". This matters for Arabic keyword research and page naming (see `docs/seo/keyword-map.md` → Arabic).

## 3. Tiering for Al Qasim Movers

- **Tier 1 — standard services (owner instruction: offer the standard market set).** Every mover needs only a crew, a truck and packing materials for these. Marked `offered: true` in `config/business.json`.
- **Tier 2 — needs a capability the owner must confirm.** These need a warehouse, special equipment, licensing, or a policy commitment. They stay `offered: null` until the owner confirms.
- **Tier 3 — excluded for launch.** These are usually delivered by partners, are outside the core business, or would suggest capabilities we can't verify.

| Service | Tier | Reason | Candidate page |
|---|---|---|---|
| Home / house moving | 1 | Core | `/services/home-movers-dubai/` |
| Apartment moving | 1 | Core | `/services/apartment-movers-dubai/` |
| Villa moving | 1 | Core | `/services/villa-movers-dubai/` |
| Studio moving | 1 | Core (may merge into apartment page; keyword research decides) | `/services/studio-movers-dubai/` |
| Office moving | 1 | Core | `/services/office-movers-dubai/` |
| Commercial moving (shops, retail, warehouses) | 1 | Core | `/services/commercial-movers-dubai/` |
| Furniture moving (furniture-only / single items) | 1 | Core | `/services/furniture-movers-dubai/` |
| Packing services | 1 | Core | `/services/packing-services-dubai/` |
| Unpacking services | 1 | Core (strong merge candidate with packing; keyword research decides) | `/services/unpacking-services-dubai/` |
| Furniture disassembly & assembly | 1 | Core (فك وتركيب is a major Arabic query) | `/services/furniture-assembly-dubai/` |
| Loading & unloading | 1 | Core (may become a section instead of a page) | `/services/loading-unloading-dubai/` |
| Local moving within Dubai | 1 | Core; overlaps home movers, so probably not its own page | — (covered by home/areas) |
| Storage & warehousing | 2 | Needs a warehouse (own or partner) | `/services/storage-services-dubai/` |
| Inter-emirate moves (Dubai ↔ Sharjah / Abu Dhabi / etc.) | 2 | Owner said areas = all of Dubai; moves outside Dubai not confirmed | — |
| International moving | 2 | Needs freight forwarding/customs capability | — |
| Same-day / urgent moves | 2 | Policy commitment | FAQ / section |
| Handyman: curtains, TV mounting, fixtures | 2 | Needs skilled staff; common Arabic add-on (تركيب ستائر) | Section on assembly page |
| Heavy / specialty items (piano, safe, gym equipment) | 2 | Needs equipment and experience | Section |
| Pickup / truck rental with driver | 2 | Different service model | — |
| Deep cleaning, painting, pest control | 3 | Partner-delivered; not a mover's core | — |
| Lady packers | 3 | Staffing claim needs verification | — |

## 4. Consolidation notes for keyword research
The keyword researcher must decide, using SERP overlap, whether these pairs are one page or two:
- home movers ↔ house movers ↔ local moving (almost certainly one page)
- apartment ↔ studio
- packing ↔ unpacking
- furniture movers ↔ furniture assembly
- loading/unloading: a page of its own, or a section on every service page

Rule: no thin pages (`.claude/rules/seo-rules.md`). The target stays at 12–15 strong service pages at most.

## Sources (accessed 2026-09-19)
- Super Movers — https://www.supermovers.ae/
- ServiceMarket, Dubai local movers — https://servicemarket.com/en/dubai/local-movers
- Crown Relocations, Dubai local moves — https://www.crownrelo.com/uae/en-ae/local-moves/dubai
- Mr Packer — https://mrpacker.ae/
- Movers E Dubai — https://moversedubai.com/
- Sana Movers — https://sanamovers.com/
- BBC Movers — https://bbcmover.com/
- Tamam Movers, handyman — https://tamammovers.com/service/handyman-services-dubai/
- Al Mas Movers, handyman — https://almasmovers.com/services/handyman/
- Arabic: Liza Movers — https://lizamovers.com/ ; Al Rahma Movers — https://alrahmamovers.com/dubai.html ; Dar Al Fayha — https://www.dmoversae.com/ ; Arkan — https://arkanuae.com/furniture-moving-company-in-dubai/

## 5. Owner confirmation of Tier 2 (2026-09-19)
| Service | Decision |
|---|---|
| Storage & warehousing | **Offered** — details open: own vs partner facility, short/long term, climate control |
| Inter-emirate moves | **Offered** — details open: which emirates |
| Same-day / urgent moves | **Offered** — availability conditions open |
| Handyman (curtains, TV mounting, fixtures) | **Offered** |
| Heavy / specialty items (piano, safe, gym equipment) | **Offered** |
| Truck / pickup with driver | **Offered** |
| International moving | **Not offered** — never mention on the site |

Round 3 (same day): **every emirate** served; storage is **partner** storage (never call it our own warehouse); same-day moves are **conditional** (subject to availability — never guaranteed). Handyman tasks and heavy-item types use the conservative defaults in `business.json` → `defaults`.

