# JavaScript Architecture

## Overview
The JavaScript logic in Finzo is split strictly into **Global Modules** (reusable logic loaded on every page) and **Domain Scripts** (loaded only when required for a specific page route). 

All JS is written in modern **Vanilla ES6**. We do not use React, Vue, jQuery, or heavy state management libraries like Redux.

## Directory Structure

```text
public/assets/js/
├── core/
│   ├── api.js                # Global network fetch wrapper
│   ├── component-loader.js   # Injects HTML partials recursively
│   ├── ui-components.js      # Global event delegation for Modals/Dropdowns
│   └── utils.js              # Formatting functions and Toast UI
├── admin/                    # Admin-specific logic (e.g. users.js, staff.js)
├── user/                     # Customer-specific logic (e.g. transactions.js)
├── staff/                    # Staff-specific logic
└── pages/                    # Generic page logic (e.g. auth-components.js)
```

## State Management
We do not use complex client-side stores. 
Instead, data is retrieved lazily from the backend via REST API calls and rendered directly to the DOM.

Where session states (e.g., Logged-in user information) must persist across page loads to prevent visual flickering, we cache minimal structured data into `localStorage` (e.g. `UserState` object) and strictly validate it via API calls in the background.

## Event Delegation
To support highly dynamic interfaces (such as components fetched via `component-loader.js` after the initial DOM completes), we rely heavily on **Event Delegation** bound to the `document`.

### Anti-Pattern (DO NOT USE)
```javascript
// Will fail if .btn-submit is injected via AJAX later
document.querySelectorAll('.btn-submit').forEach(btn => {
    btn.addEventListener('click', () => { /* ... */ });
});
```

### Best Practice (USE THIS)
```javascript
// Will securely bind to current and future elements
document.addEventListener('click', (e) => {
    const trigger = e.target.closest('.btn-submit');
    if (trigger) {
        e.preventDefault();
        // Handle logic
    }
});
```

## Custom Events
To allow loosely coupled components to communicate, we fire custom DOM events:
- **`componentsLoaded`**: Fired by `component-loader.js` when all HTML partials are fully injected. Wait for this event before attaching heavy DOM-dependent initializations.
- **`formSuccess`**: Fired manually when an API Form resolves successfully, natively triggering `ui-components.js` to automatically close any active Modals.

## Execution Flow
1. **Load CSS / Render HTML Frame**.
2. **`component-loader.js`**: Fetches sidebars/headers synchronously and patches the DOM.
3. **`ui-components.js`**: Attaches global event listeners for Dropdowns and Modals to `document`.
4. **Domain Script (e.g. `dashboard.js`)**: Fetches API data via `window.api.get()`, iterates JSON results, and populates the table rows.