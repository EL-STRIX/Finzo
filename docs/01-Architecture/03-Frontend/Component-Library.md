# Component Library Architecture

## Overview
The Finzo Frontend encapsulates highly reusable UI elements into standalone **Component Specifications**. 
Instead of redefining the DOM structure and CSS logic repeatedly, developers utilize semantic class names mapped directly to the Finzo Design System.

Component CSS files are located in `public/assets/css/components/`.

## Core Components

### 1. Buttons (`buttons.css`)
Buttons dictate the primary interactive state. We supply variants to manage hierarchy.
- **Base Class:** `.btn` (applies flexbox, padding, border-radius, transition logic)
- **Primary:** `.btn-primary` (solid blue background, white text)
- **Secondary:** `.btn-secondary` (solid gray/surface background)
- **Outline:** `.btn-outline` (transparent background with solid border)
- **Danger:** `.btn-danger` (red semantic coloring for destructive actions)

**Usage Example:**
```html
<button class="btn btn-primary w-full">Submit Transfer</button>
```

### 2. Forms & Inputs (`forms.css`)
Form controls maintain strict visual conformity. 
- **Container:** `.fin-input-container` (wraps labels, inputs, and error text)
- **Inputs:** `.form-control` (applies width, padding, border, focus rings)
- **Labels:** `.form-label` (standardized font size, weight, and margin)
- **Errors:** `.is-invalid` (triggers red borders and reveals `.fin-error-text`)

**Usage Example:**
```html
<div class="fin-input-container">
    <label class="form-label">Account Number</label>
    <input type="text" name="account_no" class="form-control" required>
    <span class="fin-error-text"></span>
</div>
```

### 3. Modals (`modals.css` & `ui-components.js`)
Modals are managed via global event delegation to ensure they trap focus properly.
- **Structure:** Overlay `.modal-overlay` > Dialog `.modal-dialog`
- **Triggers:** Bind via `data-modal-target="[modal_id]"`
- **Closing:** Bind via `data-modal-close` attribute.

**Usage Example:**
```html
<!-- Trigger -->
<button class="btn btn-primary" data-modal-target="transferModal">Open Transfer</button>

<!-- Modal -->
<div id="transferModal" class="modal-overlay" role="dialog" aria-modal="true">
    <div class="modal-dialog">
        <h3>Transfer Funds</h3>
        <!-- Content -->
        <button class="btn" data-modal-close>Cancel</button>
    </div>
</div>
```

### 4. Toast Notifications (`core/utils.js`)
The `FinzoUtils.showToast()` method dynamically injects accessible toast notifications for form successes or network failures.

**Usage Example:**
```javascript
// Success state
window.FinzoUtils.showToast('Transfer completed successfully!', 'success');

// Error state
window.FinzoUtils.showToast('Insufficient funds.', 'error');
```

## HTML Partials (`component-loader.js`)
For structurally massive components like sidebars and headers, Finzo uses a lightweight HTML partial loader. 
Developers inject the `data-include` attribute on a placeholder element, and the JS engine fetches and swaps the DOM cleanly.

**Usage Example:**
```html
<!-- Automatically fetches and renders the unified Sidebar -->
<div data-include="/Finzo/resources/views/admin/components/sidebar.html"></div>
```