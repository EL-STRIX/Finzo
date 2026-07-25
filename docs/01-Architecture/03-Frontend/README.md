# Frontend Architecture

## Overview
The Finzo Banking Management System utilizes a deeply decoupled, highly modular **Vanilla Frontend Architecture**. 
We completely bypass heavy JavaScript frameworks (React, Vue, Angular) and heavy build tools (Webpack, Vite) in favor of native browser APIs (ES6 Modules, Fetch) and a centralized CSS Utility System (Tailwind-inspired).

This architecture ensures:
1. **Ultra-fast Page Loads:** Minimal JavaScript payload and no virtual DOM overhead.
2. **High Security:** Total compatibility with strict Content Security Policies (CSP) and automatic CSRF mitigation via `window.api`.
3. **Enterprise Maintainability:** A strictly enforced separation of concerns where logic lives in JS, layout lives in CSS, and content lives in HTML.

## Folder Structure

The frontend layer is entirely contained within `public/assets/` and `resources/views/`.

```text
public/assets/
├── css/
│   ├── components/      # UI components (buttons, cards, inputs)
│   ├── layouts/         # Structural grids (sidebar, header, grids)
│   ├── pages/           # Page-specific styling (rarely used, prefer components)
│   ├── base.css         # CSS Reset and basic typography rules
│   ├── utilities.css    # Tailwind-inspired utility classes
│   └── variables.css    # Centralized Design Tokens
├── js/
│   ├── admin/           # Admin-specific logic
│   ├── core/            # Global modules (api.js, component-loader.js, ui-components.js)
│   ├── staff/           # Staff-specific logic
│   └── user/            # Customer portal logic
├── images/              # Optimized static SVGs, WebP, PNGs
└── fonts/               # Local WOFF2 font files
```

## Coding Standards

### 1. Vanilla JavaScript First
- Do not introduce jQuery, React, or Vue.
- Use `document.querySelector` and `document.addEventListener`.
- Always use `async/await` for asynchronous logic. Avoid `.then()` chaining when possible.
- Use the global `window.api.get()` or `window.api.post()` wrappers for all network communication (see `API-Integration.md`).

### 2. Vanilla CSS and Utility Classes
- Styling is predominantly achieved through utility classes mapping to `variables.css`.
- Avoid deeply nested SCSS-style nesting to prevent specificity wars.
- Never use `!important` to override layouts. Instead, build a specific modifier class.
- Always use CSS Custom Properties (`var(--color-primary-base)`) instead of hardcoded hex values.

### 3. Progressive Enhancement
- Ensure the application does not completely break if a specific JS module fails to load.
- Ensure HTML remains semantically accurate and WCAG 2.2 AA compliant.

## Related Documents
- [Design System](Design-System.md) - CSS Variables, Colors, and Typography
- [Component Library](Component-Library.md) - Reusable UI elements (Modals, Toasts)
- [JavaScript Architecture](JavaScript-Architecture.md) - Global state and event delegation
- [API Integration](API-Integration.md) - Fetch wrappers and CSRF protection
- [Accessibility](Accessibility.md) - WCAG 2.2 AA and ARIA specs
- [Performance](Performance.md) - Asset optimization and lazy-loading