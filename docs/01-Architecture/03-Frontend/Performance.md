# Performance Optimization Standards

## Overview
The Finzo Frontend Architecture is designed to achieve near-instant First Contentful Paint (FCP) and Time to Interactive (TTI). By entirely avoiding heavy JavaScript frameworks (React/Vue/Angular), the browser spends zero milliseconds parsing Virtual DOM trees or executing client-side hydration.

## 1. CSS Delivery Optimization
All CSS layout architecture has been converted to an atomic-inspired utility system.

- **Zero Duplication:** Layout classes (e.g., flexbox wrappers, paddings, colors) are defined exactly once in `utilities.css`.
- **Render Blocking Mitigation:** Because the CSS payload is so incredibly tiny, it does not significantly block rendering. The browser instantly paints the layout based on classes present in the HTML.

## 2. JavaScript Execution Strategy
All JavaScript files are decoupled from the render pipeline to ensure the DOM paints immediately.

### The `defer` Attribute
Every `<script>` tag included in the DOM must carry the `defer` attribute.
```html
<script src="/assets/js/core/api.js" defer></script>
```
**Why?** `defer` guarantees that the HTML parser is never blocked while the browser downloads the script. The script only executes strictly after the DOM has been fully parsed.

### Component Loading Pipeline
The `component-loader.js` script fetches HTML partials dynamically without reloading the page. It fires `componentsLoaded` when finished, ensuring that downstream domain scripts only initialize heavily after the exact DOM fragments they need are fully painted.

## 3. Asset and Image Optimization

### Native Lazy Loading
All image assets outside the immediate initial viewport (below the fold) or inside Modals must include the native `loading="lazy"` attribute.
```html
<img src="/assets/images/promotions/banking-banner.webp" loading="lazy" alt="Promotion">
```
**Why?** This prevents the browser from utilizing network bandwidth or decoding CPU cycles on images the user cannot even see yet.

### Format Standards
- Use **SVG** exclusively for logos, icons, and vector graphics to ensure crisp scaling and zero-loss compression.
- Use **WebP** for all raster graphics and profile pictures. Never use uncompressed PNGs or heavy JPEGs.

## 4. DOM Manipulation Restrictions
- **No Layout Thrashing:** Javascript should never repeatedly read a DOM property (like `offsetHeight`) and then write a style in a loop.
- **Batch Updates:** When rendering dynamic tables (e.g., Transaction Lists), concatenate the HTML string fully in memory before injecting it into `.innerHTML` once. Do not append nodes individually in a loop.