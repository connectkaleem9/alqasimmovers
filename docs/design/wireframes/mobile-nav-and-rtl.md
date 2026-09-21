# Mobile navigation, sticky bar, RTL check

## Mobile menu (open state)
```
┌──────────────────────────────┐
│ ◻AL QASIM               ✕    │  ✕ = same button, aria-expanded=true
├──────────────────────────────┤
│ Services                  ▾  │  expandable group (14 links)
│ Areas                     ▾  │  (10 links)
│ Guides                       │
│ About                        │
│ Contact                      │
├──────────────────────────────┤
│ [ Get a Free Quote ]         │
│ [ ☏ Call Now ] [ ⌾ WhatsApp ]│
├──────────────────────────────┤
│ العربية                      │  language switch → same page
└──────────────────────────────┘
```
Behaviour: focus moves into the panel, Escape closes, focus returns to ≡, background `inert`, body scroll locked, sticky CTA bar hidden while open.

## Sticky CTA bar
56px, two equal buttons, bottom-fixed, `env(safe-area-inset-bottom)`. Body gets matching bottom padding so the footer is never covered. Hidden on desktop, while the menu is open, and while a text field has focus (so it never covers the keyboard area).

## RTL mirror check (Arabic, 360px)
```
┌──────────────────────────────┐
│ ≡ العربية   ☏      القاسم ◻ │  header order mirrors
├──────────────────────────────┤
│        نقل اثاث في دبي H1    │  text-align: start → right
│     سطر واحد: ماذا ننقل…     │
│         [ احصل على عرض سعر ] │  buttons full width
│              [ واتساب ]      │
│   الإمارات وكل دبي أنحاء ✓   │  check icons flip side
├──────────────────────────────┤
│ الفلل ▸ … ◂ breadcrumb flips │  chevrons mirrored
└──────────────────────────────┘
```
Checked for each template: header order, breadcrumb separators, card arrows, form label alignment, accordion chevrons, sticky-bar button order, and the numbered process list (numbers sit on the start side). Phone numbers, `tel:` links and Western digits are **not** mirrored.
