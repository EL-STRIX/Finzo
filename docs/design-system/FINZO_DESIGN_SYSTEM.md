# FINZO ENTERPRISE DESIGN SYSTEM

## 1. Design Philosophy
The Finzo Design System defines the foundational UI components and tokens for the entire banking platform. It establishes a professional, trustworthy, accessible, and highly consistent environment. The system strictly governs *how* components look and behave, rather than dictating *what* page layouts must explicitly contain. 

## 2. Token System
Our design system is built heavily on semantic CSS custom properties.
- **Primary:** `var(--color-primary)`
- **Secondary:** `var(--color-secondary)`
- **Surface:** `var(--color-surface)`
- **Border:** `var(--color-border)`
- **Text:** `var(--color-text-primary)`
- **Status:** `var(--color-success)`, `var(--color-warning)`, `var(--color-error)`, `var(--color-info)`

These are natively integrated into the `tailwind-config.js` extensions.

## 3. Light Theme
Light mode serves as the default enterprise theme. It uses `#f4f7f9` for the application background, pure `#ffffff` for elevated card surfaces, and dark `#111820` for maximum text contrast. The Light Theme is defined under the `:root` scope in `variables.css`.

## 4. Dark Theme
Dark mode is treated as a first-class citizen, fully supported at the token level without requiring separate override stylesheets. It activates when `data-theme="dark"` or the `.dark` class is present on the `<html>` element. It utilizes a layered surface approach (`#000000` base, `#161b22` cards, `#1c2128` modals).

## 5. Typography
- **Display:** `Plus Jakarta Sans` - Used for page titles and large marketing copy.
- **Body:** `Inter` - Used for all tables, inputs, data displays, and paragraph text.
- **Mono:** `JetBrains Mono` - Reserved for numeric banking IDs, hashes, and code.

## 6. Spacing
An exact spacing scale from `--space-4xs` (4px) to `--space-3xl` (64px).

## 7. Borders
- `--color-border-subtle`: Dividers, subtle rows.
- `--color-border`: Standard inputs, standard cards.
- `--color-border-strong`: Focused or interactive un-elevated elements.

## 8. Radius
The radius scale ranges from `--radius-sm` (4px) for small inputs up to `--radius-full` for badges. The standard container radius is `--radius-md` or `--radius-lg`.

## 9. Shadows
Subtle elevation is preferred over heavy depth. Focus rings (`--shadow-focus`) replace the default browser outline to maintain visual brand identity without sacrificing accessibility.

## 10. Focus States
Focus is mandatory. Any interactive element (button, input, link, dropdown) receives a `--shadow-focus` ring `rgba(10, 37, 64, 0.2)` in Light Mode and `rgba(88, 166, 255, 0.3)` in Dark Mode. 

## 11. Buttons
Classes available: `.btn`, `.btn-primary`, `.btn-secondary`, `.btn-tertiary`, `.btn-outline`, `.btn-ghost`, `.btn-error`, `.btn-success`. Support for `.is-disabled` and `.is-loading`.

## 12. Forms
Standard `.fin-input` / `.form-control` classes manage all text inputs, selects, and textareas. `.fin-label` for standard labels. Required asterisks handled securely via `.fin-label-required`. Supports `.is-invalid` and `.is-valid`.

## 13. Cards
Built with `.ds-card`, `.ds-card-header`, `.ds-card-body`, `.ds-card-footer`. Use `.ds-card-elevated` for modals/popovers. Add `.is-interactive` for hover elevation.

## 14. Tables
`.ds-table-wrapper` and `.ds-table` provide an enterprise-grade table interface. Features clean dividers, subtle row hovering, and appropriate data contrast.

## 15. Badges
`.badge` or `.fin-badge` with `.badge-primary`, `.badge-success`, `.badge-warning`, `.badge-error`, `.badge-info`, and `.badge-neutral`. Used for status tagging.

## 16. Alerts
`.alert` with `.alert-success`, `.alert-warning`, `.alert-error`, `.alert-info`. Features prominent icons and bold titles for inline notifications.

## 17. Modals
Modals inherit styles from `.ds-card-elevated` overlaid on a `var(--color-overlay)` backdrop.

## 18. Dropdowns
Dropdowns inherit `--shadow-dropdown` and `.ds-card` border standards.

## 19. Pagination
Uses `.finzo-pagination` wrapper with `.pagination-link` and `.active` state modifiers.

## 20. Responsive Rules
Standard Tailwind breakpoints (sm, md, lg, xl, 2xl). Forms and tables stack appropriately. 

## 21. Accessibility
- All inputs are enforced to be at least `16px` to avoid iOS zoom.
- Minimum touch target is `44px` (WCAG 2.2 AA).
- Global `@media (prefers-reduced-motion: reduce)` disabling all transitions.

## 22. Animation Rules
Minimal animation. Only `--transition-fast` (150ms) and `--transition-normal` (250ms) are permitted for hovers, dropdowns, and states.

## 23. Component Usage Rules
Components must use CSS variables. Hardcoded `#hex` values in component files are strictly forbidden unless it's a structural necessity.

## 24. Forbidden Practices
- Do NOT create `dark-mode.css` global override files.
- Do NOT remove focus rings.
- Do NOT use floating or infinite decorative animations.
- Do NOT design pages with mock data logic.
