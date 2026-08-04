/* ═══════════════════════════════════════════
   FINZO — Staff Portal Shared JavaScript
   ═══════════════════════════════════════════ */

// ── THEME ENGINE ──
(function () {
    const STORAGE_KEY = 'finzo-staff-theme';
    const html = document.documentElement;

    function applyTheme(theme) {
        html.setAttribute('data-theme', theme);
        html.classList.toggle('dark', theme === 'dark');
        localStorage.setItem(STORAGE_KEY, theme);

        const icon = document.getElementById('theme-icon');
        const label = document.getElementById('theme-label');
        if (icon) icon.textContent = theme === 'dark' ? 'light_mode' : 'dark_mode';
        if (label) label.textContent = theme === 'dark' ? 'Light Mode' : 'Dark Mode';
    }

    function toggleTheme() {
        const current = localStorage.getItem(STORAGE_KEY) || 'light';
        applyTheme(current === 'dark' ? 'light' : 'dark');
    }

    // Initial load
    const saved = localStorage.getItem(STORAGE_KEY) || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    applyTheme(saved);

    document.addEventListener('DOMContentLoaded', function () {
        const toggleBtn = document.querySelector('#toggle-theme-btn');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', toggleTheme);
        }
    });
})();

// ── UI HELPERS ──
function formatINR(amount) {
    const num = parseFloat(amount) || 0;
    return '₹' + num.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// Live Clock
function updateClock() {
    const now = new Date();
    const timeStr = now.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
    const dateStr = now.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });

    const el = (selector) => document.querySelector(selector);
    const els = (selector) => document.querySelectorAll(selector);

    if (el('#live-clock')) el('#live-clock').textContent = timeStr;
    els('#live-date, #header-date').forEach(e => e.textContent = dateStr);
}
setInterval(updateClock, 1000);
document.addEventListener('DOMContentLoaded', updateClock);

// Global Events (Sidebar, Modals)
document.addEventListener('DOMContentLoaded', function () {
    // Mobile Sidebar Toggle
    const mobileBtn = el('#mobile-menu-btn');
    if (mobileBtn) {
        mobileBtn.addEventListener('click', function () {
            el('#admin-sidebar').classList.add('mobile-open');
            el('#sidebar-overlay').classList.add('active');
        });
    }

    const sidebarOverlay = el('#sidebar-overlay');
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function () {
            el('#admin-sidebar').classList.remove('mobile-open');
            el('#sidebar-overlay').classList.remove('active');
        });
    }

    // End Shift Modal
    els('.end-shift-trigger').forEach(trigger => {
        trigger.addEventListener('click', function () {
            el('#modal-overlay').style.display = 'flex';
            el('#modal-overlay').classList.remove('hidden');
            el('#modal-overlay').classList.add('opacity-100');
            el('#logout-modal').classList.remove('hidden');
            el('#logout-modal').style.display = 'block';
            el('#logout-modal').classList.add('scale-100');
        });
    });

    els('.modal-close').forEach(closeBtn => {
        closeBtn.addEventListener('click', function () {
            el('#modal-overlay').classList.remove('opacity-100');
            el('#modal-overlay').classList.add('hidden');
            el('#modal-overlay').style.display = 'none';
            el('#logout-modal').classList.remove('scale-100');
            el('#logout-modal').classList.add('hidden');
        });
    });

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function(e) {
            if (e.target === this) {
                el('#modal-overlay').classList.remove('opacity-100');
                el('#modal-overlay').classList.add('hidden');
                el('#modal-overlay').style.display = 'none';
                if (el('#logout-modal')) {
                    el('#logout-modal').classList.remove('scale-100');
                    el('#logout-modal').classList.add('hidden');
                }
            }
        });
    }

    // Component Loaded Event (for active nav highlighting)
    document.addEventListener('componentsLoaded', function() {
        const currentPath = window.location.pathname.split('/').pop();
        const sidebar = document.getElementById('admin-sidebar');
        if (sidebar) {
            const links = sidebar.querySelectorAll('a.staff-nav-item');
            links.forEach(link => {
                const href = link.getAttribute('href');
                if (href && href === currentPath) {
                    link.classList.remove('text-slate-600', 'dark:text-slate-400', 'hover:bg-slate-50', 'dark:hover:bg-white/5', 'hover:text-slate-900', 'dark:hover:text-white');
                    link.classList.add('bg-amber-50', 'dark:bg-amber-500/10', 'text-amber-600', 'dark:text-amber-500', 'border', 'border-amber-200', 'dark:border-amber-500/20');
                }
            });
        }

        // Set Navbar Title based on page title
        const navbarTitle = document.getElementById('page-title');
        if (navbarTitle) {
            const title = document.title.split('—')[1] || document.title;
            if (title) {
                navbarTitle.textContent = title.trim();
            }
        }
        
        // Re-bind sidebar toggle
        // Deprecated mobile button bindings left in legacy staff-shared
        // These are now handled in the global listener above, leaving empty block for legacy compat
        const themeBtn2 = document.querySelector('#toggle-theme-btn');
        if (themeBtn2) {
            themeBtn2.addEventListener('click', function() {
                // Handled globally
            });
        }

        // Populate staff info if loaded
        if (StaffState && StaffState.loaded) {
            populateStaffUI(StaffState);
        }
    });
});

// ── STAFF SESSION STATE ──
const StaffState = {
    staff_id: '',
    name: 'Loading...',
    role: '',
    branch_name: '',
    loaded: false
};

async function fetchStaffSession() {
    try {
        const data = await window.api.get('/staff-portal/dashboard');
        if (data.status === 401 || data.status === 403) {
            window.location.href = '/Finzo/public/views/auth/staff-login.html';
            return null;
        }
        if (data.success && data.staff) {
            StaffState.staff_id = data.staff.staff_id;
            StaffState.name = data.staff.name;
            StaffState.role = data.staff.role;
            StaffState.branch_name = data.staff.branch_name;
            StaffState.loaded = true;

            // Store CSRF token globally so api.js POST requests can attach it
            if (data.csrf_token) {
                window.csrfToken = data.csrf_token;
                const meta = document.querySelector('meta[name="csrf-token"]');
                if (meta) meta.setAttribute('content', data.csrf_token);
            }
            
            // Populate basic DOM if present
            document.querySelectorAll('.staff-name-display').forEach(el => el.textContent = StaffState.name);
            document.querySelectorAll('.staff-id-display').forEach(el => el.textContent = StaffState.staff_id);

            const src = StaffState.profile_picture ? (StaffState.profile_picture.startsWith('/') ? '/Finzo' + StaffState.profile_picture : '/Finzo/' + StaffState.profile_picture) : null;
            if (src) {
                document.querySelectorAll('.staff-initials-display').forEach(el => {
                    el.innerHTML = `<img src="${src}?v=${Date.now()}" class="w-full h-full object-cover rounded-full" alt="DP">`;
                });
            } else {
                document.querySelectorAll('.staff-initials-display').forEach(el => {
                    el.textContent = StaffState.name.split(' ').map(n=>n[0]).join('').substring(0,2).toUpperCase();
                });
            }
            document.querySelectorAll('.staff-branch-display').forEach(el => el.textContent = StaffState.branch_name);
            
            return data;
        }

    } catch(e) {
        console.warn('Failed to load staff session:', e);
    }
    return null;
}

