---
name: AgriFlow AI
description: Proactive decision support for perishable agricultural logistics
colors:
  primary: "#2f6b43"
  primary-hover: "#255737"
  primary-soft: "#e4f0e5"
  neutral-ink: "#17241c"
  neutral-muted: "#66736a"
  neutral-bg: "#F8FAF7"
  neutral-surface: "#F4F7F4"
  neutral-line: "#dfe7df"
  warning: "#b66a16"
  warning-soft: "#fff1d8"
  critical: "#b53a32"
  critical-soft: "#fbe8e6"
typography:
  body:
    fontFamily: "Figtree, sans-serif"
  display:
    fontFamily: "Figtree, sans-serif"
    fontWeight: 900
rounded:
  sm: "4px"
  md: "6px"
  lg: "8px"
  xl: "12px"
spacing:
  sm: "8px"
  md: "16px"
  lg: "24px"
components:
  button-primary:
    backgroundColor: "{colors.primary}"
    textColor: "#ffffff"
    padding: "8px 16px"
    rounded: "{rounded.md}"
  card:
    backgroundColor: "#ffffff"
    rounded: "{rounded.lg}"
---

# Design System: AgriFlow AI

## Overview

**Creative North Star: "The Logistics Navigator"**

AgriFlow AI serves as a precise, guided, and structured decision engine for agricultural operations. The visual language is earthy and confident, grounding the operator in the realities of the field while maintaining the clarity of a high-stakes logistics terminal. The interface prioritizes actionable signals—turning complex environmental and routing data into scannable queues and clear recommendations.

**Key Characteristics:**
- **Actionable density**: Information is tightly packed but meticulously structured with strong visual rhythm.
- **Signal over noise**: Color is reserved almost exclusively for status, risk, and primary actions.
- **Tabular precision**: Numeric data uses tabular-nums for scannable comparison.
- **Restrained warmth**: The interface feels precise but not rigid, warm but not soft, premium but not decorative.

## Colors

The palette is earthy and confident, leaning heavily on muted field tones to make operational signals stand out.

### Primary
- **Harvest Green** (#2f6b43): The core brand and action color. Used for primary buttons, active links, and low-risk signals.
- **Harvest Green Soft** (#e4f0e5): Used for low-risk badge backgrounds and subtle active states.

### Secondary
- **Amber Warning** (#b66a16): Used for high-risk signals, pending statuses, and warning badges.
- **Critical Red** (#b53a32): Reserved strictly for critical risk and immediate required action.

### Neutral
- **Ink** (#17241c): Primary text color. Almost black, but rooted in deep green.
- **Muted** (#66736a): Secondary text, metadata, and inactive icons.
- **Sage Surface** (#F4F7F4): Strategic secondary background. Used for grouped information, filters, contextual areas, and supporting data.
- **Field White** (#F8FAF7): The main application background. Pure white (`#ffffff`) is reserved for primary/focused content.
- **Line** (#dfe7df): Borders and dividers.

**The Signal Isolation Rule.** Color is information, not decoration. The interface is largely monochromatic (ink, field white, sage) until a status or risk requires attention. Do not solve visual flatness by adding decorative colors, gradients, or glowing effects.

## Typography

**Display Font:** Figtree (sans-serif)
**Body Font:** Figtree (sans-serif)

**Character:** Modern, legible, and highly functional. Figtree provides a clean, geometric structure that works exceptionally well for dense data tables and dashboards.

### Hierarchy
- **Display** (Black/900, variable size): Used for major page headings and hero metrics.
- **Title** (Bold/700, 1.125rem/1.5rem): Used for section headings and card titles.
- **Body** (Regular/400 or Semibold/600, 0.875rem/1rem): The workhorse for data rows, descriptions, and general text.
- **Label / Eyebrow** (ExtraBold/800, 0.7rem, uppercase, 0.16em tracking): Used for structural signposting above titles (e.g., "Agricultural Operations Intelligence").

**The Tabular Rule.** Any numeric data meant to be compared vertically (metrics, scores, temperatures) must use tabular lining figures (`font-variant-numeric: tabular-nums`).

## Layout

The application uses a centered maximum width (1500px) layout to keep data accessible on ultrawide monitors without stretching. Density is high, but we create strong visual rhythm through intentional spacing, distinct hierarchical surfaces, and typography rather than relying solely on borders.

**The Surface Hierarchy Rule.** Avoid making every content region a white card. Use Sage Surface (`#F4F7F4`) for secondary regions, contexts, or sidebars. Reserve elevated pure white panels for primary, focused content.

## Elevation & Depth

The system is gently lifted and uses elevation purposefully to establish hierarchy.

### Shadow Vocabulary
- **Panel Shadow** (`0 8px 24px rgba(34, 58, 39, .05)`): Used on primary white cards and focused content panels to lift them slightly off the background.
- **Interactive Shadow** (`0 4px 12px rgba(34, 58, 39, .08)`): A tighter shadow used during hover states on interactive rows or actionable cards.

## Shapes

The form language is precise but not rigid. We use restrained, moderate radii to soften the interface without making it feel bubbly or casual.

- **Major Containers:** 8px–12px radius.
- **Cards/Panels:** ~8px radius.
- **Buttons/Inputs:** 6px–8px radius.
- **Badges/Tags:** 4px–6px radius (or fully rounded pills only when semantically appropriate, like status dots).

## Components

### Buttons & Actions
- **Shape:** Moderate radius (6px–8px).
- **Primary:** Harvest Green background, white text, bold font. Tactile and confident.
- **Hover / Focus:** Deepens to `#255737`. Focus rings are highly visible (`3px solid rgba(47, 107, 67, .35)` with an offset).

### Cards & Panels
- **Corner Style:** Restrained radius (~8px).
- **Background:** Pure white (`#ffffff`) for primary content; Sage Surface (`#F4F7F4`) for secondary data groupings.
- **Border:** 1px solid Line (`#dfe7df`).
- **Elevation:** Soft panel shadow (`0 8px 24px rgba(34, 58, 39, .05)`) on primary white cards.
- **Internal Padding:** 20px - 24px typically.

### Priority Queue Rows
- **Behavior:** Interactive rows that transition on hover.
- **Hover State:** Background lightens, and an inset left border (`inset 3px 0 0 var(--agri-green)`) appears to indicate interactivity. Use the Interactive Shadow (`0 4px 12px...`) to slightly lift the row on hover.

### Risk Badges
- **Style:** Colored text on a soft, tinted background of the same hue (e.g., Critical Red text on Red Soft background).
- **Corner Style:** Tight radius (4px–6px) to maintain a crisp, operational feel.

## Do's and Don'ts

### Do:
- **Do** use the established risk colors strictly for their semantic meaning (Green = Low, Amber = High, Red = Critical).
- **Do** wrap primary interactive elements in the standard focus ring for accessibility.
- **Do** use Sage Surface (`#F4F7F4`) to group secondary information and reduce the sea of white cards.
- **Do** use moderate, restrained radii (6px–12px) to make the UI feel premium and approachable.

### Don't:
- **Don't** mandate 0px square corners for structural elements; the interface should not feel overly rigid or boxy.
- **Don't** add excessive gradients, glassmorphism, glow effects, or generic AI SaaS aesthetics.
- **Don't** make every piece of content a white card. Use surface hierarchy to differentiate primary and secondary data.
