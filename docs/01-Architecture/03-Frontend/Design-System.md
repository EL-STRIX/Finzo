# Design System & Styling Architecture

## Overview
The Finzo Banking Management System relies on a **Utility-First, CSS-Variable Driven Design System**. 
We maintain a strict boundary between UI design tokens (colors, spacing, fonts) and structural layouts. 

## Design Tokens (`variables.css`)
All design tokens are stored as CSS Custom Properties in `public/assets/css/variables.css`. This acts as our Single Source of Truth, entirely bypassing the need for a preprocessor like SCSS or a build tool like PostCSS.

```css
:root {
    /* Primary Brand Colors */
    --color-primary-50: #eff6ff;
    --color-primary-500: #3b82f6;
    --color-primary-900: #1e3a8a;
    
    /* Functional Semantic Colors */
    --color-success: #10b981;
    --color-danger: #ef4444;
    --color-warning: #f59e0b;
    --color-info: #3b82f6;

    /* Typography Constraints */
    --font-family-sans: 'Inter', system-ui, sans-serif;
    --font-size-base: 16px;
}
```

## Utility Classes (`utilities.css`)
To keep our HTML DRY and prevent inline styles, we use a utility-first approach inspired by Tailwind CSS, written natively in `public/assets/css/utilities.css`.

### Common Utilities
- **Layout:** `.flex`, `.grid`, `.hidden`, `.block`
- **Flexbox:** `.items-center`, `.justify-between`, `.gap-4`
- **Spacing:** `.p-4` (1rem padding), `.m-0`, `.mt-2`
- **Typography:** `.text-sm`, `.font-bold`, `.text-center`
- **Colors:** `.bg-primary`, `.text-danger`

## Component Styling Strategy
While we use utility classes for generic layout structures (e.g. grids and flex wrappers), we extract highly repeated, complex UI elements into **Component Classes**.

Instead of writing:
```html
<button class="bg-primary text-white font-bold py-2 px-4 rounded hover:bg-primary-dark transition-colors focus:ring focus:ring-primary-light">
  Submit Transfer
</button>
```

We write:
```html
<button class="btn btn-primary">
  Submit Transfer
</button>
```

The definition for `.btn.btn-primary` is strictly maintained inside `public/assets/css/components/buttons.css`, which internally references our CSS variables from `variables.css`.

## Responsive Grid System
We do not use a 12-column heavy framework (like Bootstrap). Instead, we rely on modern CSS Grid and Flexbox primitives coupled with media queries.

```css
.finzo-dashboard-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
}

@media (min-width: 1024px) {
    .finzo-dashboard-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}
```

## Dark Mode Theme System
Dark mode is implemented using the `.dark` class toggled on the `<html>` element. 
In `variables.css`, we simply redefine the CSS variables when the `.dark` class is present.

```css
html.dark {
    --bg-surface: #1e293b;
    --text-main: #f8fafc;
    --border-color: #334155;
}
```
All utility classes (like `.bg-surface`) automatically adapt to these token changes seamlessly.