/* ═══════════════════════════════════════════
   FINZO — Core API Module
   Centralised fetch wrapper and global helpers
   ═══════════════════════════════════════════ */

(function() {
    // Dynamically resolve project base so it works in any subdirectory (e.g. ../../../../)
    const _scriptEl = document.currentScript || (() => {
        const scripts = document.getElementsByTagName('script');
        return scripts[scripts.length - 1];
    })();
    const _scriptSrc = _scriptEl ? _scriptEl.src : '';
    const _baseMatch = _scriptSrc.match(/^(https?:\/\/[^/]+)(\/.*?)\/assets\/js\/.*/);
    const API_BASE = (_baseMatch ? _baseMatch[2] : '') + '/api/v1';

    /**
     * Core API caller. Handles JSON and FormData.
     */
    async function apiCall(endpoint, options = {}) {
        const { method = 'GET', body = null, isForm = false } = options;

        const fetchOpts = {
            method,
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
        };

        if (method !== 'GET') {
            const csrf = window.csrfToken || (document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '');
            
            if (csrf) {
                fetchOpts.headers['X-CSRF-Token'] = csrf;
            }

            if (body) {
                if (isForm || body instanceof FormData) {
                    fetchOpts.body = body instanceof FormData ? body : objectToFormData(body);
                    if (csrf && !fetchOpts.body.has('csrf_token')) {
                        fetchOpts.body.append('csrf_token', csrf);
                    }
                } else {
                    if (csrf && typeof body === 'object' && body !== null) {
                        body.csrf_token = csrf;
                    }
                    fetchOpts.headers['Content-Type'] = 'application/json';
                    fetchOpts.body = JSON.stringify(body);
                }
            }
        }

        try {
            const res = await fetch(API_BASE + endpoint, fetchOpts);

            // Handle downloads
            const contentType = res.headers.get('content-type') || '';
            if (contentType.includes('application/pdf') || contentType.includes('text/csv') || contentType.includes('application/vnd.ms-excel')) {
                const blob = await res.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                const disposition = res.headers.get('content-disposition') || '';
                const match = disposition.match(/filename="?(.+?)"?$/);
                a.download = match ? match[1] : 'download';
                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(url);
                return { success: true, message: 'File downloaded' };
            }

            const data = await res.json();
            
            if (!res.ok) {
                if (res.status === 401 && !endpoint.includes('/auth/')) {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                        return data;
                    }
                    
                    // Fallback to manual redirect based on path
                    const path = window.location.pathname;
                    if (path.includes('admin')) {
                        window.location.href = '/Finzo/public/views/auth/admin-login.php';
                    } else if (path.includes('staff')) {
                        window.location.href = '/Finzo/public/views/auth/staff-login.php';
                    } else {
                        window.location.href = '/Finzo/public/views/auth/login.php';
                    }
                    return data;
                }
                // Return full server response so field-level errors (data.errors) reach the caller
                return { success: false, message: data.message || `Error ${res.status}: ${res.statusText}`, ...data };
            }

            return data;

        } catch (err) {
            console.error(`API Error [${endpoint}]:`, err);
            return { success: false, message: 'Network error. Please check your connection.' };
        }
    }

    // Export to window
    window.api = {
        get: (endpoint, params = null) => {
            let url = endpoint;
            if (params) {
                const qs = objectToURLParams(params);
                url += (url.includes('?') ? '&' : '?') + qs;
            }
            return apiCall(url, { method: 'GET' });
        },
        post: (endpoint, body, isForm = false) => apiCall(endpoint, { method: 'POST', body, isForm }),
        put: (endpoint, body, isForm = false) => apiCall(endpoint, { method: 'PUT', body, isForm }),
        patch: (endpoint, body, isForm = false) => apiCall(endpoint, { method: 'PATCH', body, isForm }),
        delete: (endpoint, body = null) => apiCall(endpoint, { method: 'DELETE', body }),
        upload: (endpoint, formData) => apiCall(endpoint, { method: 'POST', body: formData, isForm: true }),
        
        // Utils
        getCSRFToken: () => {
            const meta = document.querySelector('meta[name="csrf-token"]');
            return meta ? meta.getAttribute('content') : '';
        },
        
        setButtonLoading: (btn, loading = true, text = 'Processing...') => {
            if (!btn) return;
            if (loading) {
                btn.disabled = true;
                btn.dataset.originalText = btn.innerHTML;
                btn.innerHTML = `<span class="spinner"></span> ${text}`;
            } else {
                btn.disabled = false;
                btn.innerHTML = btn.dataset.originalText || btn.innerHTML;
            }
        },

        showFieldErrors: (errors) => {
            document.querySelectorAll('.field-error').forEach(el => el.remove());
            document.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));
            if (!errors || typeof errors !== 'object') return;
            Object.keys(errors).forEach(field => {
                const input = document.querySelector(`[name="${field}"]`) || document.getElementById(field);
                if (input) {
                    input.classList.add('input-error');
                    const errorEl = document.createElement('div');
                    errorEl.className = 'field-error';
                    errorEl.textContent = errors[field];
                    input.parentNode.appendChild(errorEl);
                }
            });
        },

        buildUrl: (endpoint, params = null) => {
            let url = API_BASE + endpoint;
            if (params) {
                const qs = objectToURLParams(params);
                url += (url.includes('?') ? '&' : '?') + qs;
            }
            return url;
        }
    };

    // Internal Helpers
    function objectToURLParams(obj) {
        return Object.keys(obj)
            .map(key => encodeURIComponent(key) + '=' + encodeURIComponent(obj[key] ?? ''))
            .join('&');
    }

    function objectToFormData(obj) {
        const fd = new FormData();
        Object.keys(obj).forEach(key => {
            if (obj[key] !== null && obj[key] !== undefined) fd.append(key, obj[key]);
        });
        return fd;
    }

    // Global Form Validation Integration
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (form.hasAttribute('data-no-validate')) return;
        
        let valid = true;
        form.querySelectorAll('[required]').forEach(input => {
            if (!input.value.trim()) {
                input.classList.add('input-error');
                valid = false;
            } else {
                input.classList.remove('input-error');
            }
        });
        
        if (!valid) {
            e.preventDefault();
            if (window.showToast) window.showToast('Please fill in all required fields.', 'error');
        }
    });

})();
