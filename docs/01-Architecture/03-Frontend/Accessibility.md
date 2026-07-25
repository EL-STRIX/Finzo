# Accessibility Standards (WCAG 2.2 AA)

## Overview
The Finzo Frontend Architecture is strictly built to comply with **WCAG 2.2 AA** accessibility guidelines. Accessibility is not an afterthought; it is integrated directly into the `ui-components.js` core and the semantic HTML partials.

## 1. Semantic HTML
We rely on native semantic landmarks to provide structural context to screen readers (e.g., NVDA, VoiceOver).
- Use `<header>` for navigation and top-bars.
- Use `<nav>` for sidebar link groups.
- Use `<main>` for the primary dashboard view context.
- Use `<section>` with `aria-labelledby` for grouped content widgets.

## 2. ARIA and Dynamic State
All custom interactive elements strictly manage their ARIA states via JavaScript (`ui-components.js`).

### Dropdowns
Dropdown menus dynamically toggle `aria-expanded="true"` and `aria-expanded="false"` upon click. 

### Modals
When a modal is opened:
- `aria-hidden="true"` is applied to the `<main>` background wrapper to prevent screen readers from reading background content.
- `aria-modal="true"` and `role="dialog"` are enforced on the `.modal-overlay`.

## 3. Keyboard Navigation and Focus Management
Finzo ensures 100% functionality via keyboard-only navigation.

### Focus Indicators
We do not use `outline: none;` without providing a distinct visual fallback. Global focus states are managed in `utilities.css`:
```css
*:focus-visible {
    outline: 2px solid var(--color-primary-500);
    outline-offset: 2px;
}
```

### Focus Trapping
When a modal opens, `ui-components.js` captures the currently focused element (`document.activeElement`) and stores it via `data-return-focus`.
Focus is forcibly shifted into the first focusable input inside the modal.
If the modal closes (via ESC or submit), focus is seamlessly returned to the original triggering button.

## 4. Color Contrast
Finzo's `variables.css` design tokens are mathematically calculated to ensure compliance.
- Text against a background surface exceeds **4.5:1** contrast ratio.
- Large text (headings) and interface borders exceed **3.0:1** contrast ratio.

## 5. Error Validation and ARIA
When a form submits and backend validation fails, the Javascript automatically flags invalid inputs with `aria-invalid="true"`.

```html
<input type="email" name="email" class="form-control is-invalid" aria-invalid="true">
<span class="fin-error-text">
    <span class="material-symbols-outlined">error</span> Invalid format.
</span>
```