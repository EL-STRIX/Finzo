# API Integration & Network Architecture

## Overview
All frontend communication with the Finzo backend (PHP) strictly traverses our centralized wrapper: `public/assets/js/core/api.js`.

**Do NOT use raw `fetch()` or `XMLHttpRequest` anywhere in the frontend.** 

## Why a Centralized Wrapper?
Relying strictly on `window.api.get()` and `window.api.post()` guarantees:
1. **CSRF Mitigation:** `api.js` automatically maps the session's Anti-CSRF token to outgoing POST requests headers/FormData.
2. **Path Resolution:** It resolves the `API_BASE` endpoint relatively, so the code works in nested subfolders cleanly without hardcoding paths.
3. **Structured Error Handling:** It aggressively attempts to parse JSON payloads. If the backend Fatals (HTTP 500) and returns raw HTML, `api.js` intercepts it and shows a localized error rather than crashing the UI thread.
4. **Toast Integration:** It binds network failures directly to `window.FinzoUtils.showToast()`.

## Usage Examples

### 1. Simple GET Request
Used for retrieving data (e.g. populating a table).
```javascript
async function loadUsers() {
    try {
        const response = await window.api.get('/admin/users.php?page=1');
        
        if (response.success) {
            renderTable(response.data);
        }
    } catch (error) {
        console.error("Critical failure during GET:", error);
    }
}
```

### 2. Standard POST Request
Used for submitting simple JSON objects (e.g. settings, status changes).
```javascript
async function lockAccount(userId) {
    const payload = { user_id: userId, reason: 'Suspicious Activity' };
    
    // api.js handles JSON.stringify() and Content-Type automatically
    const response = await window.api.post('/admin/lock-account.php', payload);
    
    if (response.success) {
        window.FinzoUtils.showToast('Account locked.', 'success');
    }
}
```

### 3. FormData Submission (File Uploads / Forms)
Used when capturing a `<form>` node directly from the DOM, especially if it contains `type="file"`.
```javascript
document.addEventListener('submit', async (e) => {
    if (e.target.id === 'profileForm') {
        e.preventDefault();
        
        const form = e.target;
        const formData = new FormData(form);
        
        // Pass 'true' as the third argument to signal FormData mode
        const response = await window.api.post('/user/update-profile.php', formData, true);
        
        if (response.success) {
            document.dispatchEvent(new CustomEvent('formSuccess'));
        } else if (response.errors) {
            // Handle field-level validation errors securely
            displayFieldErrors(form, response.errors);
        }
    }
});
```

## Form Validation Handling
Finzo's backend validation strictly maps HTTP 422 errors into a `data.errors` array structure:
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": "Invalid email address format.",
        "phone": "Phone number must be numeric."
    }
}
```
The frontend parses this JSON, locates the corresponding `[name="email"]` inputs in the DOM, attaches `.is-invalid` CSS classes, and reveals `.fin-error-text` spans with the specific messages.
