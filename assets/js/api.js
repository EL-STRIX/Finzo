/* ═══════════════════════════════════════════
   FINZO — API Helper Module
   Centralised fetch wrapper for all portals
   ═══════════════════════════════════════════ */

const API_BASE = '/api';

/**
 * Core API caller. Handles JSON and FormData.
 * @param {string} endpoint  - Relative path like '/auth/login'
 * @param {object} options   - { method, body, isForm }
 * @returns {Promise<object>} Parsed JSON response
 */
async function apiCall(endpoint, options = {}) {
  const { method = 'GET', body = null, isForm = false } = options;

  const fetchOpts = {
    method,
    credentials: 'same-origin', // send session cookies
    headers: {},
  };

  if (body && method !== 'GET') {
    if (isForm || body instanceof FormData) {
      // Let browser set multipart boundary
      fetchOpts.body = body instanceof FormData ? body : objectToFormData(body);
    } else {
      fetchOpts.headers['Content-Type'] = 'application/x-www-form-urlencoded';
      fetchOpts.body = objectToURLParams(body);
    }
  }

  try {
    const res = await fetch(API_BASE + endpoint, fetchOpts);

    // Handle non-JSON responses (PDF downloads, CSV)
    const contentType = res.headers.get('content-type') || '';
    if (contentType.includes('application/pdf') || contentType.includes('text/csv')) {
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
      // Handle auth redirect
      if (res.status === 401 && data.redirect) {
        window.location.href = data.redirect;
        return data;
      }
      return { success: false, message: data.message || `Error ${res.status}: ${res.statusText}` };
    }

    return data;

  } catch (err) {
    console.error(`API Error [${endpoint}]:`, err);
    return { success: false, message: 'Network error. Please check your connection.' };
  }
}

// ── Convenience methods ──
const api = {
  get:  (endpoint) => apiCall(endpoint, { method: 'GET' }),
  post: (endpoint, body, isForm = false) => apiCall(endpoint, { method: 'POST', body, isForm }),
  upload: (endpoint, formData) => apiCall(endpoint, { method: 'POST', body: formData, isForm: true }),
};

// ── CSRF token management ──
function getCSRFToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.getAttribute('content') : '';
}

function addCSRFToBody(body = {}) {
  body.csrf_token = getCSRFToken();
  return body;
}

// ── Helper: object → URL encoded params ──
function objectToURLParams(obj) {
  return Object.keys(obj)
    .map(key => encodeURIComponent(key) + '=' + encodeURIComponent(obj[key] ?? ''))
    .join('&');
}

// ── Helper: object → FormData ──
function objectToFormData(obj) {
  const fd = new FormData();
  Object.keys(obj).forEach(key => {
    if (obj[key] !== null && obj[key] !== undefined) {
      fd.append(key, obj[key]);
    }
  });
  return fd;
}

// ── Helper: Build query string from filters ──
function buildQueryString(params) {
  const cleaned = {};
  Object.keys(params).forEach(key => {
    if (params[key] !== '' && params[key] !== null && params[key] !== undefined && params[key] !== 'all') {
      cleaned[key] = params[key];
    }
  });
  const str = new URLSearchParams(cleaned).toString();
  return str ? '?' + str : '';
}

// ── Loading state helpers ──
function setButtonLoading(btn, loading = true) {
  if (!btn) return;
  if (loading) {
    btn.disabled = true;
    btn.dataset.originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner" style="width:16px;height:16px;border:2px solid rgba(255,255,255,0.3);border-top-color:#fff;border-radius:50%;animation:spin 0.6s linear infinite;display:inline-block;vertical-align:middle;margin-right:8px"></span> Processing...';
  } else {
    btn.disabled = false;
    btn.innerHTML = btn.dataset.originalText || btn.innerHTML;
  }
}

// ── Error display helper ──
function showFieldErrors(errors) {
  // Clear previous errors
  document.querySelectorAll('.field-error').forEach(el => el.remove());
  document.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));

  if (!errors || typeof errors !== 'object') return;

  Object.keys(errors).forEach(field => {
    const input = document.querySelector(`[name="${field}"]`) || document.getElementById(field);
    if (input) {
      input.classList.add('input-error');
      const errorEl = document.createElement('div');
      errorEl.className = 'field-error';
      errorEl.style.cssText = 'color:#f43f5e;font-size:12px;margin-top:4px;';
      errorEl.textContent = errors[field];
      input.parentNode.appendChild(errorEl);
    }
  });
}

// ── Pagination renderer ──
function renderPagination(containerId, currentPage, totalPages, onPageClick) {
  const container = document.getElementById(containerId);
  if (!container || totalPages <= 1) {
    if (container) container.innerHTML = '';
    return;
  }

  let html = '<div class="pagination">';

  // Previous
  html += `<button class="pagination-btn" ${currentPage <= 1 ? 'disabled' : ''} onclick="(${onPageClick.name || 'goToPage'})(${currentPage - 1})">
    <span class="material-symbols-outlined" style="font-size:18px">chevron_left</span>
  </button>`;

  // Page numbers (show max 5)
  let start = Math.max(1, currentPage - 2);
  let end = Math.min(totalPages, start + 4);
  if (end - start < 4) start = Math.max(1, end - 4);

  for (let i = start; i <= end; i++) {
    html += `<button class="pagination-btn ${i === currentPage ? 'active' : ''}" onclick="(${onPageClick.name || 'goToPage'})(${i})">${i}</button>`;
  }

  // Next
  html += `<button class="pagination-btn" ${currentPage >= totalPages ? 'disabled' : ''} onclick="(${onPageClick.name || 'goToPage'})(${currentPage + 1})">
    <span class="material-symbols-outlined" style="font-size:18px">chevron_right</span>
  </button>`;

  html += '</div>';
  container.innerHTML = html;
}

// Add spinner animation if not present
if (!document.getElementById('api-spinner-style')) {
  const style = document.createElement('style');
  style.id = 'api-spinner-style';
  style.innerHTML = '@keyframes spin { to { transform: rotate(360deg); } }';
  document.head.appendChild(style);
}
