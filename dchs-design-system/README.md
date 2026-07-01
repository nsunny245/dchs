# Daniyal Group of Colleges — Brand Design System

Version 1.0 · Derived from the official crest logo · Use this as the single source of truth across web, backend/admin, mobile, PDF, and PPT deliverables.

---

## 1. Brand Essence

- **Institution:** Daniyal Group of Colleges
- **Tagline:** "Where Success Is a Tradition"
- **Domain:** Medical / health-sciences education (caduceus, stethoscope, microscope motifs)
- **Symbolism:** The crest opens with the Quranic phrase "اقرأ باسم ربك" ("Read in the name of your Lord"), signaling an Islamic-values-rooted academic institution. The shield is divided into three panels — knowledge (top, calligraphy), clinical care (stethoscope), and science/research (microscope) — unified by a gold caduceus.
- **Personality:** Traditional, trustworthy, academic, medical, disciplined, aspirational. Not playful, not startup-y. Think "established institution," not "modern SaaS."

---

## 2. Logo

### 2.1 Construction
Circular crest (ring + shield + banner), built from 4 layers:
1. Outer white ring carrying the institution name in bold arced type
2. Thin gold ring as a separator
3. Navy shield with gold line-art icons and calligraphy
4. Gold-bordered navy ribbon banner across the bottom carrying the tagline

### 2.2 Clear space & minimum size
- Maintain clear space around the logo equal to at least the width of the gold ring at any scale.
- Minimum digital size: 32px diameter (favicon/app icon crops to shield only below 64px — see 2.4).
- Minimum print size: 15mm diameter.

### 2.3 Backgrounds
- Primary: on white or near-white (`#FFFFFF`, `#F7F8FA`).
- Dark mode / dark backgrounds: logo as-is works on black since it has a built-in white ring — do not add extra background shapes.
- Never place on busy photography without a solid or scrim backing.
- Never recolor the crest itself (navy/gold/white are fixed); only the surrounding UI chrome changes with theme.

### 2.4 Lockups & cropped marks
Full circular crest with ring text = **primary lockup** (letterheads, certificates, ID cards, splash screens).
For small spaces (favicon, app icon, nav bar, watermark), use the **shield-only mark** (crop to just the navy shield with caduceus) — no ring text, since arced text becomes illegible under ~48px.

### 2.5 Don'ts
- Don't stretch, skew, or rotate the crest.
- Don't change the gold-to-navy ratio.
- Don't add drop shadows, gradients, or bevels beyond what's specified in this system.
- Don't translate the Arabic calligraphy or the tagline into a different typeface — treat the crest as a locked asset; typography rules below apply to *surrounding UI*, not the mark itself.

---

## 3. Color System

### 3.1 Core palette (extracted from source logo)

| Token | Hex | RGB | Usage |
|---|---|---|---|
| `navy-900` (Primary/Brand) | `#12223C` | 18, 34, 60 | Shield fill, headers, primary text, nav bars, footers |
| `gold-500` (Secondary/Accent) | `#EBB45A` | 235, 180, 90 | Icons, borders, CTAs, highlights, dividers |
| `white` | `#FFFFFF` | 255, 255, 255 | Ring background, card surfaces, reversed text |
| `black` (crest outline / true black) | `#000000` | 0, 0, 0 | Outlines only — avoid as a large fill in UI; use `navy-900` instead |

### 3.2 Extended navy scale (tints for depth, hierarchy, dark-mode surfaces)

| Token | Hex | Typical use |
|---|---|---|
| `navy-50` | `#EAEDF2` | Subtle backgrounds, table stripes |
| `navy-100` | `#D2D8E3` | Disabled fields, borders on light UI |
| `navy-200` | `#A6B0C4` | Placeholder text, secondary icons |
| `navy-400` | `#4C5C7A` | Muted headings, secondary buttons |
| `navy-700` | `#1A2E4F` | Hover state of navy-900 elements |
| `navy-900` | `#12223C` | **Primary brand color** |
| `navy-950` | `#0A1526` | Deep backgrounds, dark-mode base |

### 3.3 Extended gold scale

| Token | Hex | Typical use |
|---|---|---|
| `gold-50` | `#FDF4E4` | Tinted backgrounds, badges |
| `gold-100` | `#FBE7C4` | Hover backgrounds on light UI |
| `gold-300` | `#F3CD8B` | Borders, dividers |
| `gold-500` | `#EBB45A` | **Primary accent / CTA fill** |
| `gold-600` | `#D89A34` | CTA hover/pressed state |
| `gold-700` | `#B37B22` | Text-on-light needing gold at AA contrast |

### 3.4 Semantic / functional colors
Institutional systems (LMS, ERP, student portal) need status colors that don't fight the brand. These are calibrated to sit comfortably next to navy/gold:

| Token | Hex | Usage |
|---|---|---|
| `success` | `#1E8A5F` | Fee paid, approved, pass |
| `warning` | `#EBB45A` (= gold-500) | Pending, due soon — reuses brand gold |
| `danger` | `#C0392B` | Errors, overdue, failed |
| `info` | `#2C6FAD` | Notices, announcements |
| `surface` | `#F7F8FA` | App background |
| `surface-dark` | `#0A1526` | Dark-mode app background |
| `border` | `#E1E4EA` | Default light-mode borders |
| `text-primary` | `#12223C` | Body text on light backgrounds |
| `text-secondary` | `#4C5C7A` | Secondary/meta text |
| `text-inverse` | `#FFFFFF` | Text on navy/dark fills |

### 3.5 Contrast & accessibility
- `navy-900` on `white`: **15.9:1** — passes AAA for all text sizes.
- `white` on `navy-900`: same ratio, safe for buttons/nav.
- `gold-500` on `navy-900`: **~5.2:1** — passes AA for large/bold text and icons; for small body text on navy, use `white` instead and reserve gold for headings, icons, and accents ≥18px or bold ≥14px.
- `gold-500` on `white`: **~1.6:1** — fails as text color on white. Use `gold-700` for any gold text on white/light backgrounds; keep `gold-500` for fills, icons, borders, and large decorative type only.

---

## 4. Typography

The logo's ring type is a bold, slightly condensed geometric sans (display-style), and the banner uses a bold condensed slab/sans for the tagline. Recommended production font pairing (free, widely available, similar character):

| Role | Font | Fallback stack |
|---|---|---|
| **Display / Headings** | Montserrat (700/800) | `"Montserrat", "Poppins", "Segoe UI", Arial, sans-serif` |
| **Body / UI text** | Inter (400/500/600) | `"Inter", "Segoe UI", Roboto, Arial, sans-serif` |
| **Tagline / Banner-style callouts** | Oswald (600/700, condensed) | `"Oswald", "Bebas Neue", "Arial Narrow", sans-serif` |
| **Arabic/Urdu content** (if any UI needs it) | Noto Naskh Arabic | `"Noto Naskh Arabic", "Scheherazade New", serif` |

### 4.1 Type scale (base 16px, 1.25 ratio)

| Token | Size | Line-height | Weight | Use |
|---|---|---|---|---|
| `display-xl` | 48px | 56px | 800 | Hero / landing headline |
| `display-lg` | 36px | 44px | 800 | Page titles |
| `heading-1` | 28px | 36px | 700 | Section headers |
| `heading-2` | 22px | 30px | 700 | Card / panel titles |
| `heading-3` | 18px | 26px | 600 | Sub-headers |
| `body-lg` | 16px | 24px | 400 | Default body |
| `body-sm` | 14px | 20px | 400 | Secondary text, captions |
| `label` | 12px | 16px | 600 (uppercase, +0.04em tracking) | Form labels, badges, tags |

Rule of thumb: **Montserrat/gold-500 or navy-900 for anything that should feel "institutional/ceremonial"** (certificates, hero banners, letterheads); **Inter for anything functional** (forms, tables, dashboards, app UI).

---

## 5. Spacing, Radius, Elevation

### 5.1 Spacing scale (4px base unit)
`4, 8, 12, 16, 24, 32, 48, 64, 96` px → tokens `space-1` … `space-9`

### 5.2 Radius
| Token | Value | Use |
|---|---|---|
| `radius-sm` | 4px | Inputs, small buttons, tags |
| `radius-md` | 8px | Cards, modals |
| `radius-lg` | 16px | Hero panels, feature cards |
| `radius-full` | 999px | Pills, avatars, badges |

### 5.3 Elevation (shadows) — use sparingly, this brand favors flat/bordered over floaty
| Token | Value |
|---|---|
| `shadow-sm` | `0 1px 2px rgba(18,34,60,0.06)` |
| `shadow-md` | `0 4px 12px rgba(18,34,60,0.10)` |
| `shadow-lg` | `0 12px 32px rgba(18,34,60,0.16)` |

---

## 6. Iconography & Imagery

- Icon style matching the crest: **duotone line icons**, navy stroke + gold fill accents, 1.5–2px stroke weight, rounded joins. (Lucide / Phosphor icon sets restyled with these two colors work well.)
- Medical iconography (stethoscope, microscope, caduceus, cross, book/graduation cap) should stay gold-on-navy or navy-on-white, matching the crest — never introduce a third icon color.
- Photography: prefer warm-toned, well-lit campus/clinical photography; apply a subtle navy overlay (10–20% `navy-900`) on hero images that carry white text, for consistent contrast.

---

## 7. Component Guidelines (Web & Backend/Admin)

### Buttons
| Variant | Background | Text | Border | Hover |
|---|---|---|---|---|
| Primary | `navy-900` | `white` | none | `navy-700` |
| Secondary (accent) | `gold-500` | `navy-900` | none | `gold-600` |
| Outline | transparent | `navy-900` | `1px navy-900` | fill `navy-50` |
| Destructive | `danger` | `white` | none | darken 10% |
| Ghost/Text | transparent | `navy-900` | none | `navy-50` bg |

### Cards / Panels
- White surface, `1px solid border` (`#E1E4EA`), `radius-md`, `shadow-sm`.
- Optional top accent bar in `gold-500` (4px) for featured/highlighted cards (e.g., "Featured Program").

### Navigation
- Top nav: `navy-900` background, `white` text/logo, `gold-500` active-state underline or pill.
- Sidebar (admin/backend): `navy-950` background, `navy-200` inactive text, `white` active text with `gold-500` left-border indicator (3–4px) on the active item.

### Forms
- Inputs: `white` bg, `1px solid navy-100` border, `radius-sm`, focus ring `gold-500` (2px outline, offset 1px) — never a generic blue focus ring, it clashes.
- Labels: `label` type token, `navy-700`.

### Tables (admin/backend heavy use)
- Header row: `navy-50` background, `navy-900` bold text.
- Row hover: `gold-50`.
- Zebra striping optional: alternate `white` / `navy-50`.

### Badges/Status tags
- Rounded-full, `label` type, colored per semantic tokens (§3.4) at 15% opacity background + full-opacity text/border.

---

## 8. Platform-Specific Application

### 8.1 Website
- Use `tokens.css` (provided) as CSS custom properties; wire directly into Tailwind via the `tailwind.config` snippet in §9.
- Header/footer in `navy-900`; hero sections can use a full-bleed navy background with gold accent underlines on headings, echoing the crest.
- Favicon = shield-only crop (see §2.4) exported as 32×32/16×16 PNG/ICO.

### 8.2 Backend / Admin Software (dashboards, ERP, LMS)
- Sidebar navy-950, content area `surface` (`#F7F8FA`), cards white.
- Keep gold strictly as an accent (active states, primary CTA, key metrics) — a dashboard that's mostly gold will feel alarm-toned; navy + gold accents + generous white space is the correct ratio (roughly 70% neutral/white, 20% navy, 10% gold).
- Data-viz palette (charts): `navy-900, gold-500, navy-400, gold-300, success, info` in that order, so the first two series always read as "on-brand."

### 8.3 Mobile (iOS/Android/React Native)
- Use `tokens.json`/`tokens.js` as your theme object.
- Status bar: light content on `navy-900` app bar; dark content over `surface` screens.
- Tab bar: `white` bg, active icon/label `navy-900` + small `gold-500` dot/indicator, inactive `navy-200`.
- App icon: shield-only crest, navy background fill (not transparent — many launchers add their own white backing otherwise, which is fine too, but test both).
- Respect Human Interface / Material minimum touch targets (44pt / 48dp) — the brand doesn't override platform ergonomics.

### 8.4 PDF Documents (reports, certificates, transcripts, letterheads)
- **Letterhead:** crest (shield-only or full, depending on page size) top-left or centered, `navy-900` thin rule (1pt) below header, footer in `navy-400` small text with contact info.
- **Headings:** Montserrat Bold, `navy-900`.
- **Body:** Inter/Arial-equivalent (PDF-safe fallback: Helvetica/Arial if Inter isn't embedded), `text-primary`.
- **Certificates:** full circular crest centered top, gold double-rule border frame (`gold-500` outer, thin navy inner), tagline set in Oswald/condensed caps.
- **Tables in reports:** header row `navy-900` fill + white text; body rows white/`navy-50` zebra.
- Always embed fonts or use PDF-safe fallbacks (Helvetica for Inter, Arial Narrow/Oswald substitute for Oswald) since not all PDF renderers embed custom fonts reliably.

### 8.5 PowerPoint / Slide Decks
- **Master slide 1 (Title):** full navy-900 background, crest centered or top, title in white Montserrat 800, gold-500 rule under subtitle.
- **Master slide 2 (Content):** white background, thin navy header bar (or just navy title text + gold underline), navy-900 body text, gold-500 used for bullet accents/icons/highlighted numbers only.
- **Master slide 3 (Section divider):** navy-900 bg, large gold-500 section number/heading.
- Chart colors: same data-viz order as §8.2.
- Footer every slide: small shield-only mark + page number, `navy-400`.

---

## 9. Developer Tokens

Three machine-readable token files accompany this doc:
- `tokens.css` — CSS custom properties for any web stack
- `tokens.json` — platform-agnostic (backend, mobile, design tools)
- `tokens.js` — JS/TS export (React, React Native, Tailwind theme extension)

### Tailwind quick-start
```js
// tailwind.config.js
const tokens = require('./tokens.json');
module.exports = {
  theme: {
    extend: {
      colors: {
        navy: tokens.color.navy,
        gold: tokens.color.gold,
        success: tokens.color.semantic.success,
        warning: tokens.color.semantic.warning,
        danger: tokens.color.semantic.danger,
        info: tokens.color.semantic.info,
      },
      fontFamily: {
        display: ['Montserrat', 'sans-serif'],
        body: ['Inter', 'sans-serif'],
        tagline: ['Oswald', 'sans-serif'],
      },
      borderRadius: tokens.radius,
      boxShadow: tokens.shadow,
    },
  },
};
```

---

## 10. Quick Reference Cheat Sheet

```
Primary (Navy):   #12223C
Accent (Gold):    #EBB45A
Neutral White:    #FFFFFF
Success:          #1E8A5F
Danger:           #C0392B
Info:             #2C6FAD

Headings font:    Montserrat (700/800)
Body font:        Inter (400/600)
Tagline font:     Oswald (600/700)

Rule of thumb:    Navy dominant (~70%), Gold accent only (~10%), White/neutral space (~20%)
```
