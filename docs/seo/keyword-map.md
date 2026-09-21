# Keyword Map

| Status | Owner | Stage | Date |
|---|---|---|---|
| Complete (v1) | keyword-researcher | 2 | 2026-09-19 |

**Method:**
- **Seeds:** the owner brief §7 plus competitor titles.
- **Expansion:** Google Autocomplete (gl=ae; EN hl=en, AR hl=ar). Raw data is in `data/autocomplete-2026-09-19.json`.
- **Clustering:** by SERP overlap. Google searches were run for each pair in question (see "Consolidation decisions").
- **Volumes:** no volume tool was available, so **no volume numbers are stated**. The "Demand signal" column shows autocomplete evidence only: ●●● = the seed plus several long-tail variants autocomplete; ●● = the seed autocompletes; ● = weak or no autocomplete. Add real volumes from Google Keyword Planner or Search Console later.

Roles: **P** = primary, **S** = secondary, **Sup** = supporting. Intent: **Com** = commercial, **Tra** = transactional, **Inf** = informational, **Loc** = local.

## Consolidation decisions (SERP evidence, 2026-09-19)
| Question | Evidence | Decision |
|---|---|---|
| home vs house movers vs local moving | Autocomplete shows both "home" and "house movers"; same service intent | **One page:** `/services/home-movers-dubai/` |
| packing vs unpacking | The SERP for "unpacking services dubai" is almost entirely combined "Packing & Unpacking" pages | **One page:** `/services/packing-services-dubai/` (H1 "Packing & Unpacking Services in Dubai"). `/services/unpacking-services-dubai/` is **not created**. |
| apartment vs studio | "studio movers dubai" returns no autocomplete and a SERP of small single-purpose pages; apartment SERP differs (aggregators, big brands) | **Merge studio into apartment page** (section "Studio & 1-bedroom moves"). `/services/studio-movers-dubai/` is **not created** for launch; revisit with Search Console data. |
| furniture movers vs furniture assembly | Assembly SERP = handyman/carpentry firms (IKEA assembly); furniture movers SERP = movers and single-item movers | **Two pages**, different intents. The assembly page also carries the confirmed handyman tasks (curtains, TV mounting). |
| loading/unloading | Demand appears as "pickup truck with driver", "labour" | **No standalone page.** Covered by `/services/pickup-truck-with-driver-dubai/` (truck + loading labour) and a section on every service page |
| office vs commercial | Autocomplete for "commercial movers" returns office terms | Office = offices/corporate. Commercial = **shops, retail, restaurants, warehouses**. Distinct H1s and primaries, cross-linked. |
| same-day | "same day movers dubai" autocompletes; the service is conditional | **No page.** Honest section on home/apartment pages + FAQ (owner rule: conditional, never guaranteed) |
| inter-emirate | Strong EN + AR demand: "movers dubai to abu dhabi / sharjah / ajman", "نقل اثاث من دبي الى ابوظبي / الشارقة / العين / عجمان" | **Hub + 2 route pages** (Abu Dhabi, Sharjah). Other emirates are covered on the hub; add route pages later if data supports it. |

## A. English keyword → URL map

| Cluster | Primary (P) | Secondary (S) | Supporting (Sup) | Intent | Demand | Target URL |
|---|---|---|---|---|---|---|
| Brand / core | movers and packers dubai | movers dubai, packers and movers dubai, moving company dubai, moving services dubai | movers and packers dubai near me, movers and packers dubai reviews | Com/Tra/Loc | ●●● (marketplace-dominated) | `/` |
| Home moving | home movers dubai | house movers dubai, house moving dubai, house shifting dubai, local movers dubai | house removals dubai, best house movers dubai, same day movers dubai | Com/Tra | ●●● | `/services/home-movers-dubai/` |
| Apartment | apartment movers dubai | apartment movers and packers dubai, studio movers dubai, 1 bedroom / 2 bedroom movers dubai | studio apartment moving dubai | Com/Tra | ●● | `/services/apartment-movers-dubai/` |
| Villa | villa movers dubai | villa movers and packers dubai, villa moving dubai, best villa movers dubai | villa relocation dubai, cheap villa movers dubai | Com/Tra | ●●● | `/services/villa-movers-dubai/` |
| Office | office movers dubai | office relocation dubai, office moving company dubai, office movers and packers dubai | office furniture movers dubai, professional office movers dubai | Com/Tra | ●●● | `/services/office-movers-dubai/` |
| Commercial | commercial movers dubai | shop movers dubai, warehouse movers dubai, retail relocation dubai | restaurant moving dubai | Com | ●● | `/services/commercial-movers-dubai/` |
| Furniture moving | furniture movers dubai | furniture moving dubai, furniture removal dubai, single item movers dubai | furniture movers price dubai, cheapest furniture movers dubai | Com/Tra | ●●● | `/services/furniture-movers-dubai/` |
| Packing | packing services dubai | packing and unpacking services dubai, unpacking services dubai, packing company dubai | wrapping services dubai, packing materials | Com | ●●● | `/services/packing-services-dubai/` |
| Assembly & handyman | furniture assembly dubai | furniture dismantling and assembly dubai, furniture installation dubai | curtain installation dubai, TV mounting dubai | Com/Tra | ●● | `/services/furniture-assembly-dubai/` |
| Storage | storage movers dubai | furniture storage dubai, moving and storage dubai | storage unit cost dubai | Com | ●● | `/services/storage-services-dubai/` |
| Heavy items | piano movers dubai | piano moving dubai, heavy item movers dubai | safe movers, gym equipment moving dubai | Com | ●● | `/services/piano-movers-dubai/` |
| Truck + labour | pickup truck with driver dubai | pickup rental dubai, 1 ton pickup dubai, truck rental for moving dubai | loading unloading labour dubai | Tra | ●● (SERP: many rental sites) | `/services/pickup-truck-with-driver-dubai/` |
| Inter-emirate hub | inter emirate movers uae | movers dubai to ajman, movers dubai to ras al khaimah, movers dubai to fujairah | long distance movers uae | Com | ●● | `/services/inter-emirate-movers/` |
| Route: Abu Dhabi | movers dubai to abu dhabi | moving company dubai to abu dhabi, moving from dubai to abu dhabi | movers dubai to abu dhabi price | Com/Tra | ●●● | `/services/movers-dubai-to-abu-dhabi/` |
| Route: Sharjah | movers dubai to sharjah | moving from dubai to sharjah, moving company dubai to sharjah | movers dubai to sharjah price | Com/Tra | ●●● | `/services/movers-dubai-to-sharjah/` |
| Areas (pattern) | movers in {area} | movers and packers in {area}, home/villa/furniture movers in {area} | best movers in {area} | Loc/Com | ●●● for Marina, JVC, Business Bay, Al Barsha, Jumeirah/Palm, JLT, Silicon Oasis | `/areas/{area}/` (see page inventory) |
| Cost | moving cost dubai | movers cost dubai, moving company dubai prices, movers and packers dubai cost | how much do movers cost in dubai | Inf/Com | ●●● | `/blog/moving-cost-dubai/` |
| Permits | move in permit dubai | move out permit dubai, how to get move in permit dubai | emaar move in permit, nakheel move permit, dubai properties move in permit | Inf | ●●● | `/blog/move-in-move-out-permit-dubai/` |
| Checklist | moving house in dubai checklist | apartment moving checklist dubai, moving checklist dubai | moving out of dubai checklist | Inf | ●● | `/blog/moving-checklist-dubai/` |
| Choosing a mover | how to choose a moving company in dubai | best movers dubai reddit, movers dubai reviews | — | Inf/Com | ●● | `/blog/how-to-choose-movers-dubai/` |
| Office checklist | office relocation checklist dubai | office move checklist | — | Inf | ● | `/blog/office-relocation-checklist-dubai/` |

**Excluded (not offered, or wrong intent):** anything about international moving ("movers dubai to india / uk / riyadh", "moving company dubai to uk"). International moving is not offered and must never be targeted. Job queries ("packing company dubai job") are also excluded.

## B. Arabic keyword → URL map (`/ar/` counterparts)

Arabic clusters come from Arabic autocomplete and Arabic competitor titles, not from translating the English keywords.

| Cluster | Primary (P) | Secondary (S) | Supporting (Sup) | Intent | Demand | Target URL |
|---|---|---|---|---|---|---|
| Core | نقل اثاث دبي | شركة نقل اثاث دبي، شركات نقل الاثاث في دبي، نقل عفش دبي | نقل اثاث دبي مفتوح الآن | Com/Tra/Loc | ●●● | `/ar/` |
| Home | نقل اثاث منازل دبي | نقل عفش المنزل دبي، نقل اثاث داخل دبي | — | Com | ●● | `/ar/services/home-movers-dubai/` |
| Apartment | نقل اثاث شقق دبي | نقل شقة في دبي، نقل استوديو | — | Com | ● (seen in competitor H2s) | `/ar/services/apartment-movers-dubai/` |
| Villa | نقل اثاث فلل دبي | نقل أثاث الفلل في دبي | — | Com | ● (competitor H2s) | `/ar/services/villa-movers-dubai/` |
| Office | نقل اثاث مكاتب دبي | نقل مكاتب دبي، نقل الشركات | — | Com | ● | `/ar/services/office-movers-dubai/` |
| Commercial | نقل محلات ومستودعات دبي | نقل اثاث تجاري | — | Com | ● | `/ar/services/commercial-movers-dubai/` |
| Furniture moving | نقل قطع اثاث دبي | نقل غرفة نوم، نقل كنب | — | Com | ● | `/ar/services/furniture-movers-dubai/` |
| Packing | تغليف اثاث دبي | تغليف اثاث المنزل، تغليف وفك | تغليف اثاث نايلون | Com | ●● | `/ar/services/packing-services-dubai/` |
| Assembly & handyman | فك وتركيب اثاث دبي | تركيب ستائر دبي، عامل تركيب ستائر دبي، تركيب تلفزيون على الحائط | — | Com/Tra | ●● | `/ar/services/furniture-assembly-dubai/` |
| Storage | تخزين اثاث دبي | شركة تخزين اثاث دبي | — | Com | ●● | `/ar/services/storage-services-dubai/` |
| Heavy items | نقل بيانو دبي | نقل خزنة، نقل اجهزة رياضية | — | Com | ● | `/ar/services/piano-movers-dubai/` |
| Truck | بيك اب نقل اثاث دبي | تأجير بيك اب مع سائق دبي | — | Tra | ● | `/ar/services/pickup-truck-with-driver-dubai/` |
| Inter-emirate hub | نقل اثاث بين الامارات | نقل اثاث من دبي الى عجمان، الى العين، الى رأس الخيمة | — | Com | ●●● | `/ar/services/inter-emirate-movers/` |
| Route: Abu Dhabi | نقل اثاث من دبي الى ابوظبي | نقل اثاث دبي ابوظبي | — | Com/Tra | ●●● | `/ar/services/movers-dubai-to-abu-dhabi/` |
| Route: Sharjah | نقل اثاث من دبي الى الشارقة | نقل أثاث الشارقة دبي | — | Com/Tra | ●●● | `/ar/services/movers-dubai-to-sharjah/` |
| Areas | نقل اثاث {المنطقة} دبي | — | — | Loc | ● (to validate in Search Console) | `/ar/areas/{area}/` |
| Cost | اسعار نقل اثاث دبي | نقل اثاث دبي رخيص، تكلفة نقل الاثاث | — | Inf/Com | ●● | `/ar/blog/moving-cost-dubai/` |
| Permits | تصريح نقل اثاث دبي | تصريح الانتقال إعمار، نخيل | — | Inf | ● (EN-led; AR to validate) | `/ar/blog/move-in-move-out-permit-dubai/` |

Notes:
- "رخيص" (cheap) is a real Arabic modifier. We never claim to be the cheapest. It is covered honestly in the cost guide ("how to keep moving costs down").
- Several AR clusters show only ● because Arabic autocomplete returns little for long phrases. Arabic Search Console data after launch is the real test. The pages are justified by the service existing and by Arabic competitors targeting the same headings.

## Cannibalisation watch-list
- `/` vs `/services/home-movers-dubai/`: home = brand + "movers and packers"; home-movers = household/house moving. Keep the H1s distinct.
- `/services/office-movers-dubai/` vs `/services/commercial-movers-dubai/`: offices vs shops/warehouses.
- `/services/furniture-movers-dubai/` vs `/services/furniture-assembly-dubai/`: moving items vs assembling them.
- `/services/inter-emirate-movers/` vs route pages: the hub never targets "dubai to abu dhabi/sharjah" as its primary.
- Area pages vs service pages: an area page targets "movers in {area}" only.
- `/blog/moving-cost-dubai/` vs service pages: the blog answers "how much", and service pages link to it rather than duplicating it.
