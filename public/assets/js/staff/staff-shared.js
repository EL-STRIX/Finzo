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

    $(function () {
        $('#toggle-theme-btn').on('click', toggleTheme);
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

    $('#live-clock').text(timeStr);
    $('#live-date, #header-date').text(dateStr);
}
setInterval(updateClock, 1000);
$(function() { updateClock(); });

// Global Events (Sidebar, Modals)
$(function () {
    // Mobile Sidebar Toggle
    $('#mobile-menu-btn').on('click', function () {
        $('#admin-sidebar').addClass('mobile-open');
        $('#sidebar-overlay').addClass('active');
    });
    $('#sidebar-overlay').on('click', function () {
        $('#admin-sidebar').removeClass('mobile-open');
        $('#sidebar-overlay').removeClass('active');
    });

    // End Shift Modal
    $('.end-shift-trigger').on('click', function () {
        $('#modal-overlay').css('display', 'flex').removeClass('hidden').addClass('opacity-100');
        $('#logout-modal').removeClass('hidden').css('display', 'block').addClass('scale-100');
    });

    $('.modal-close, #modal-overlay').on('click', function (e) {
        if (e.target === this || $(this).hasClass('modal-close')) {
            $('#modal-overlay').removeClass('opacity-100').addClass('hidden').css('display', 'none');
            $('#logout-modal').removeClass('scale-100').addClass('hidden');
        }
    });

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
        $('#mobile-menu-btn').on('click', function () {
            $('#admin-sidebar').addClass('mobile-open');
            $('#sidebar-overlay').addClass('active');
        });

        // Re-bind theme toggle
        $('#toggle-theme-btn').on('click', function() {
            const current = localStorage.getItem('finzo-staff-theme') || 'light';
            const html = document.documentElement;
            const theme = current === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', theme);
            html.classList.toggle('dark', theme === 'dark');
            localStorage.setItem('finzo-staff-theme', theme);
    
            const icon = document.getElementById('theme-icon');
            const label = document.getElementById('theme-label');
            if (icon) icon.textContent = theme === 'dark' ? 'light_mode' : 'dark_mode';
            if (label) label.textContent = theme === 'dark' ? 'Light Mode' : 'Dark Mode';
        });

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
            $('.staff-name-display').text(StaffState.name);
            $('.staff-id-display').text(StaffState.staff_id);
            if (data.staff.profile_photo) {
                const src = data.staff.profile_photo.startsWith('http') ? data.staff.profile_photo : '/Finzo/public/assets/' + data.staff.profile_photo;
                $('.staff-initials-display').html(`<img src="${src}?v=${Date.now()}" class="w-full h-full object-cover rounded-full" alt="DP">`);
            } else {
                $('.staff-initials-display').text(StaffState.name.split(' ').map(n=>n[0]).join('').substring(0,2).toUpperCase());
            }
            $('.staff-branch-display').text(StaffState.branch_name);
            
            return data;
        }

    } catch(e) {
        console.warn('Failed to load staff session:', e);
    }
    return null;
}

