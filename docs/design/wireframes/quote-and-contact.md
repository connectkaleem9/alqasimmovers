# Get a Quote + Contact

## /get-a-quote/ (mobile)
```
┌──────────────────────────────┐
│ H1 Get a Free Moving Quote   │
│ Reply by WhatsApp or call.   │
│ [ WhatsApp instead ] ← for   │  many users prefer chat;
│   people who hate forms      │  give them the exit first
├──────────────────────────────┤
│ Step 1 of 2 — About the move │  progress text, not a bar
│ Name (required)   ___        │
│ Phone (required)  ___        │  inputmode=tel
│ Moving from       ___        │  area (datalist of 10 areas)
│ Moving to         ___        │
│ Property type     [select]   │  studio…villa, office, shop
│ Moving date       [date]     │
├──────────────────────────────┤
│ Step 2 — What you need       │
│ ☐ Packing  ☐ Unpacking       │  checkboxes = the real
│ ☐ Dismantle/assemble         │  service list from config
│ ☐ Storage  ☐ Handyman        │
│ ☐ Heavy items (piano/safe)   │
│ Rooms  [select]              │
│ Message ___________          │
│ (honeypot field, hidden)     │
│ [ Request a Free Moving Quote]│
│ Privacy line + ▸ policy      │
└──────────────────────────────┘
```
Both "steps" are on **one page** (no multi-page form). Required fields: name + phone only — everything else optional, because every extra required field costs leads. Errors: summary at top + inline messages. Success → `/get-a-quote/thank-you/` (noindex) with what happens next and the WhatsApp button.

## /contact/
```
H1 Contact Al Qasim Movers
[ Call +971 55 686 9224 ] [ WhatsApp Us ]
Short form (name, phone, message)
NAP block: Al Qasim Movers · Dubai, United Arab Emirates
Hours: omitted until confirmed
No map embed (privacy/performance); "we serve all of Dubai and every emirate"
```
