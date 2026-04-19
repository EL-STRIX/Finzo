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
        const res = await fetch('/api/staff/dashboard.php', { credentials: 'same-origin' });
        if (res.status === 401 || res.status === 403) {
            window.location.href = '../auth/staff-login.html';
            return null;
        }
        const data = await res.json();
        if (data.success && data.staff) {
            StaffState.staff_id = data.staff.staff_id;
            StaffState.name = data.staff.name;
            StaffState.role = data.staff.role;
            StaffState.branch_name = data.staff.branch_name;
            StaffState.loaded = true;
            
            // Populate basic DOM if present
            $('.staff-name-display').text(StaffState.name);
            $('.staff-id-display').text(StaffState.staff_id);
            $('.staff-initials-display').text(StaffState.name.split(' ').map(n=>n[0]).join('').substring(0,2).toUpperCase());
            $('.staff-branch-display').text(StaffState.branch_name);
            
            return data;
        }
    } catch(e) {
        console.warn('Failed to load staff session:', e);
    }
    return null;
}
