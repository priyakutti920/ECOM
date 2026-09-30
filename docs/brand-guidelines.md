# Nool & Crop — Brand Guidelines

> The single source of truth for the Nool & Crop brand.
> Sync to design tokens: `node .claude/skills/brand/scripts/sync-brand-to-tokens.cjs --force`

---

## Brand Snapshot

| Attribute        | Value                                                              |
|------------------|--------------------------------------------------------------------|
| **Brand name**   | Nool & Crop                                                        |
| **Tagline**      | Modern essentials, delivered with care.                            |
| **Industry**     | E-commerce / Direct-to-consumer storefront                         |
| **Personality**  | Trustworthy, modern, approachable, clean                           |
| **Voice**        | Confident, friendly, plain-spoken, never over-promising             |

---

## Color Palette

### Quick Reference

| Role                | Value      |
|---------------------|------------|
| **Primary Color**   | `#0068E1`  |
| **Secondary Color** | `#292D32`  |
| **Accent Color**    | `#10B981`  |

### Primary Colors

| Name             | Hex       | Usage                                |
|------------------|-----------|--------------------------------------|
| Brand Blue       | `#0068E1` | Default CTA / brand mark             |
| Dark Blue        | `#0053B8` | Hover / pressed states               |
| Light Blue       | `#E8F2FF` | Tinted backgrounds, info callouts    |
| Subtle Blue      | `#F0F6FF` | Page-tinted backgrounds              |
| Blue Glow        | `rgba(0,104,225,0.2)` | Focus rings, soft shadows |

### Secondary Colors

| Name             | Hex       | Usage                                |
|------------------|-----------|--------------------------------------|
| Brand Dark       | `#292D32` | Headlines, body text                 |
| Dark Muted       | `#1E2229` | Footer, deep surfaces                |
| Text Main        | `#292D32` | Default body copy                    |
| Text Muted       | `#626F84` | Secondary copy, helper text          |
| Text Light       | `#8C9BA5` | Disabled / placeholder               |
| Text Inverse     | `#FFFFFF` | Text on dark surfaces                |

### Accent Colors

| Name             | Hex       | Usage                                |
|------------------|-----------|--------------------------------------|
| Accent Green     | `#10B981` | Success, "in stock", confirmations   |
| Warning Orange   | `#F59E0B` | Warnings, pending states             |
| Danger Red       | `#EF4444` | Errors, destructive actions          |
| Brand Purple     | `#4B0C4E` | Featured / highlight categories      |
| Brand Orange     | `#B82804` | Sale badges, urgent banners          |
| Brand Navy       | `#04195A` | Footer accent, special promos        |

### Surface & Border

| Token               | Value     |
|---------------------|-----------|
| Page background     | `#F8F9FA` |
| Surface (cards)     | `#FFFFFF` |
| Muted background    | `#F3F5F8` |
| Default border      | `#E2E8F0` |
| Light border        | `#EDF2F7` |

---

## Typography

| Token       | Value                                                  |
|-------------|--------------------------------------------------------|
| **Family**  | `Rubik` (Google Fonts), fallback `sans-serif`          |
| **Weights** | 300 (light), 400 (regular), 500 (medium), 600 (semibold), 700 (bold) |
| **Style**   | Single sans family — no secondary fonts                |

### Type Scale (recommended)

| Use           | Size  | Weight | Line-height |
|---------------|-------|--------|-------------|
| Display       | 36px  | 700    | 1.15        |
| H1            | 28px  | 700    | 1.2         |
| H2            | 22px  | 600    | 1.25        |
| H3            | 18px  | 600    | 1.3         |
| Body          | 14px  | 400    | 1.5         |
| Caption       | 12px  | 400    | 1.4         |
| Button label  | 14px  | 600    | 1           |

---

## Logo

- **File**: `public/assets/images/logo.png`
- **Favicon**: `public/favicon.ico`
- **Min clear space**: Equal to the height of the wordmark cap on every side
- **Min width**: 96px on screen, 24mm on print
- **Do not** stretch, recolor, or place on busy backgrounds without a solid plate

---

## Spacing, Radius, Shadows

| Token            | Value                                |
|------------------|--------------------------------------|
| Container width  | `1260px`                             |
| Header height    | `82px`                               |
| Radius scale     | xs 4 / sm 6 / md 10 / lg 14 / xl 20 / full 9999 (px) |
| Shadow scale     | sm / md / lg / card / card-hover / drawer |
| Transition       | `all 0.25s cubic-bezier(0.16, 1, 0.3, 1)` |

---

## Iconography

- **Primary set**: `line-awesome` (`las la-*`) — loaded from CDN
- **Secondary set**: `font-awesome` (`fas fa-*`) — already in admin
- **Style**: Outline / line icons preferred; filled only for primary CTAs and active states

---

## Voice & Tone

| Do                                              | Don't                                         |
|-------------------------------------------------|-----------------------------------------------|
| Speak like a helpful store associate            | Sound corporate or robotic                    |
| Use plain language                              | Use jargon or buzzwords                       |
| Lead with the customer benefit                  | Lead with features                            |
| Be confident about quality                      | Over-promise or exaggerate                    |
| Show empathy when things go wrong               | Blame the customer                            |

**Tone by surface:**
- **Marketing**: warm, aspirational, clear
- **Product**: factual, scannable, helpful
- **Support**: empathetic, accountable, action-oriented
- **Errors**: human, specific, next-step forward

---

## Messaging Framework

**Headline pattern:** `[Benefit] + [proof or scope]`
- "Free shipping on orders over ₹499"
- "100% secure checkout — UPI, cards, netbanking"

**CTA pattern:** Verb-led, one or two words
- `Add to cart`, `Buy now`, `Track order`, `Save changes`

---

## Asset Organization

```
public/
├── assets/
│   ├── css/        → shared styles (style.css = brand stylesheet)
│   ├── images/     → product photos, banners, logo.png
│   └── js/         → shared scripts
└── favicon.ico
```

**Naming convention:** `kebab-case.ext`, descriptive but short.
Examples: `hero-banner.webp`, `product-card-placeholder.png`.

---

## Consistency Checklist

Before shipping a new surface, confirm:

- [ ] Primary `#0068E1` used only for primary CTAs and links
- [ ] Body copy uses `#292D32`, helper text uses `#626F84`
- [ ] Fonts load from the same Google Fonts URL
- [ ] Spacing follows the 4 / 6 / 10 / 14 / 20 scale
- [ ] Icon class prefix matches the surface (`las` in shop, `fas` in admin)
- [ ] Status colors used semantically (green = success, red = error)
- [ ] No hardcoded brand hex outside `public/assets/css/style.css`

---

## AI Image Generation Prompts

Use these seeds for marketing visuals — match the brand mood.

> "Modern e-commerce hero, soft daylight, clean white surface, one product centered, subtle blue (#0068E1) accent, ultra-clean, minimalist, photo-real."

> "Flat-lay product photography, soft shadow, light grey (#F8F9FA) backdrop, no text, no logos, lifestyle props in muted neutrals."

---

_Last updated: 2025-10-01_
