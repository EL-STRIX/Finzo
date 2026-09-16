/* ═══════════════════════════════════════════════════════════════
   FINZO — Staff Portal Shared JavaScript
   Enterprise clean architecture, Vanilla JS, Dark/Light Mode
   ═══════════════════════════════════════════════════════════════ */

// ── 1. UNIFIED THEME ENGINE ──
(function () {
    const STORAGE_KEY = 'finzo-theme';
    const html = document.documentElement;

    function applyTheme(theme) {
        const activeTheme = theme || localStorage.getItem(STORAGE_KEY) || localStorage.getItem('finzo-staff-theme') || 'light';

        html.setAttribute('data-theme', activeTheme);
        html.classList.toggle('dark', activeTheme === 'dark');

        localStorage.setItem(STORAGE_KEY, activeTheme);
        localStorage.setItem('finzo-staff-theme', activeTheme);

        // Update all icons across DOM (header, sidebar, etc.)
        document.querySelectorAll('[data-theme-icon], #theme-icon').forEach(icon => {
            icon.textContent = activeTheme === 'dark' ? 'light_mode' : 'dark_mode';
        });

        // Update all labels across DOM
        document.querySelectorAll('[data-theme-label], #theme-label').forEach(label => {
            label.textContent = activeTheme === 'dark' ? 'Light Mode' : 'Dark Mode';
        });

        // Update theme badges if present
        document.querySelectorAll('#theme-badge').forEach(badge => {
            badge.textContent = activeTheme === 'dark' ? 'Dark' : 'Light';
        });
    }

    function toggleTheme() {
        const current = localStorage.getItem(STORAGE_KEY) || localStorage.getItem('finzo-staff-theme') || 'light';
        const target = current === 'dark' ? 'light' : 'dark';
        applyTheme(target);
    }

    // Immediate sync to match theme-init.js
    const saved = localStorage.getItem(STORAGE_KEY) || localStorage.getItem('finzo-staff-theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    applyTheme(saved);

    window.FinzoTheme = {
        toggle: toggleTheme,
        apply: applyTheme,
        current: () => localStorage.getItem(STORAGE_KEY) || 'light'
    };

    // Global click listener for any theme toggle button anywhere in the DOM
    document.addEventListener('click', function (e) {
        const toggleBtn = e.target.closest('#toggle-theme-btn, [data-toggle-theme]');
        if (toggleBtn) {
            e.preventDefault();
            toggleTheme();
        }
    });

    // Re-sync after DOM finishes loading
    document.addEventListener('DOMContentLoaded', () => applyTheme());
})();

// ── 2. UI HELPERS ──
const el = (selector) => document.querySelector(selector);
const els = (selector) => document.querySelectorAll(selector);

function formatINR(amount) {
    const num = parseFloat(amount) || 0;
    return '₹' + num.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// Live Clock
function updateClock() {
    const now = new Date();
    const timeStr = now.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
    const dateStr = now.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });

    if (el('#live-clock')) el('#live-clock').textContent = timeStr;
    els('#live-date, #header-date').forEach(e => e.textContent = dateStr);
}
setInterval(updateClock, 1000);
document.addEventListener('DOMContentLoaded', updateClock);

// ── 3. MOBILE SIDEBAR & RESPONSIVE BEHAVIOR ──
function setupMobileSidebar() {
    const mobileBtn = document.getElementById('mobile-menu-btn');
    const sidebar = document.getElementById('sidebar') || document.getElementById('admin-sidebar');
    const overlay = document.getElementById('sidebar-overlay');

    function openSidebar() {
        if (sidebar) {
            sidebar.classList.remove('-translate-x-full');
            sidebar.classList.add('translate-x-0');
        }
        if (overlay) {
            overlay.classList.remove('hidden', 'opacity-0');
            overlay.classList.add('opacity-100');
        }
    }

    function closeSidebar() {
        if (sidebar) {
            sidebar.classList.remove('translate-x-0');
            sidebar.classList.add('-translate-x-full');
        }
        if (overlay) {
            overlay.classList.add('hidden', 'opacity-0');
            overlay.classList.remove('opacity-100');
        }
    }

    if (mobileBtn) {
        mobileBtn.addEventListener('click', function (e) {
            e.preventDefault();
            if (sidebar && sidebar.classList.contains('translate-x-0')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
    }

    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeSidebar();
    });
}
document.addEventListener('DOMContentLoaded', setupMobileSidebar);

// ── 4. SHIFT PILL & STATUS SYNC ──
function updateSidebarShiftPill(shift) {
    const pill = document.getElementById('sidebar-shift-pill');
    const dot = document.getElementById('sidebar-shift-dot');
    const text = document.getElementById('sidebar-shift-text');
    const endShiftBtn = document.getElementById('sidebar-end-shift-btn');

    const isActive = shift && shift.status === 'active';

    if (pill && dot && text) {
        if (isActive) {
            pill.className = 'inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-500/10 dark:bg-emerald-500/15 border border-emerald-500/20';
            dot.className = 'w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse';
            text.className = 'text-[10px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest';
            text.textContent = 'Active Shift';
        } else {
            pill.className = 'inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-slate-500/10 dark:bg-slate-500/15 border border-slate-500/20';
            dot.className = 'w-1.5 h-1.5 rounded-full bg-slate-400';
            text.className = 'text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest';
            text.textContent = 'Off Duty';
        }
    }

    // Show/hide "End Shift" button in sidebar footer based on shift state
    if (endShiftBtn) {
        if (isActive) {
            endShiftBtn.classList.remove('hidden');
        } else {
            endShiftBtn.classList.add('hidden');
        }
    }
}
window.updateSidebarShiftPill = updateSidebarShiftPill;

// ── 5. STAFF SESSION STATE ──
const StaffState = {
    staff_id: '',
    name: 'Loading...',
    role: '',
    branch_name: '',
    profile_photo: '',
    current_shift: null,
    loaded: false
};

async function fetchStaffSession() {
    try {
        const res = await window.api.get('/staff-portal/dashboard');
        if (res && (res.status === 401 || res.status === 403)) {
            window.location.href = '/Finzo/public/views/auth/staff-login.php';
            return null;
        }
        const data = res || {};
        const payload = (data.data && typeof data.data === 'object' && data.data.staff) ? data.data : data;
        const staff = payload.staff || data.staff;
        const shift = payload.current_shift || data.current_shift;

        if (data.success && staff) {
            StaffState.staff_id = staff.staff_id || staff.id || '';
            StaffState.name = staff.name || staff.full_name || 'Staff Member';
            StaffState.role = staff.role || staff.designation || 'Teller';
            StaffState.branch_name = staff.branch_name || 'Unassigned';
            StaffState.profile_photo = staff.profile_photo_url || staff.profile_photo || staff.profile_picture || '';
            StaffState.current_shift = shift || null;
            StaffState.loaded = true;

            // Store CSRF token globally
            const csrf = data.csrf_token || payload.csrf_token;
            if (csrf) {
                window.csrfToken = csrf;
                const meta = document.querySelector('meta[name="csrf-token"]');
                if (meta) meta.setAttribute('content', csrf);
            }

            // Populate DOM displays
            document.querySelectorAll('.staff-name-display').forEach(el => el.textContent = StaffState.name);
            document.querySelectorAll('.staff-id-display').forEach(el => el.textContent = StaffState.staff_id);
            document.querySelectorAll('.staff-branch-display, .badge.badge-neutral').forEach(el => el.textContent = StaffState.branch_name);

            // Update shift indicator in sidebar — always update, even when off-duty
            updateSidebarShiftPill(shift || { status: 'not_clocked_in' });

            // Avatar / initials
            const rawPhoto = StaffState.profile_photo;
            let src = null;
            if (rawPhoto) {
                src = (rawPhoto.startsWith('http://') || rawPhoto.startsWith('https://') || rawPhoto.startsWith('/'))
                    ? rawPhoto
                    : '/Finzo/' + rawPhoto.replace(/^\/+/, '');
            }

            if (src) {
                document.querySelectorAll('.staff-initials-display').forEach(el => {
                    el.innerHTML = `<img src="${src}?v=${Date.now()}" class="w-full h-full object-cover rounded-xl" alt="DP">`;
                });
            } else {
                document.querySelectorAll('.staff-initials-display').forEach(el => {
                    const initials = StaffState.name.split(' ').filter(w => /^[a-zA-Z]/.test(w)).map(n => n[0]).join('').substring(0, 2).toUpperCase() || 'ST';
                    el.textContent = initials;
                });
            }

            return data;
        }

    } catch (e) {
        console.warn('Failed to load staff session:', e);
    }
    return null;
}
window.fetchStaffSession = fetchStaffSession;

// ── 6. END SHIFT & LOGOUT ACTIONS ──
document.addEventListener('click', function (e) {
    // End Shift Trigger
    const endShiftTrigger = e.target.closest('.end-shift-trigger, #sidebar-end-shift-btn');
    if (endShiftTrigger) {
        e.preventDefault();

        // 1. If dashboard custom modal exists and window.openClockOutModal is exposed, use it
        const dashboardModal = document.getElementById('clockout-modal');
        if (dashboardModal && typeof window.openClockOutModal === 'function') {
            window.openClockOutModal();
            return;
        }

        // 2. Otherwise, open universal sidebar clockout modal
        const sidebarModal = document.getElementById('sidebar-clockout-modal');
        if (sidebarModal) {
            sidebarModal.classList.remove('hidden');
            sidebarModal.classList.add('flex');
            return;
        }

        // 3. Fallback prompt
        if (confirm('Are you sure you want to end your active shift?')) {
            window.api.post('/staff-portal/shift/clock-out', { notes: '' })
                .then(res => {
                    showToast('Shift ended successfully.', 'success');
                    updateSidebarShiftPill({ status: 'not_clocked_in' });
                    setTimeout(() => window.location.reload(), 600);
                })
                .catch(err => {
                    showToast(err.message || 'Failed to end shift', 'error');
                });
        }
    }

    // Sign Out Trigger
    const logoutTrigger = e.target.closest('#sidebar-logout-btn, .staff-logout-trigger');
    if (logoutTrigger) {
        e.preventDefault();
        if (confirm('Are you sure you want to sign out of Finzo Staff Portal?')) {
            window.api.post('/auth/logout')
                .catch(() => {})
                .finally(() => {
                    window.location.href = '/Finzo/public/views/auth/staff-login.php';
                });
        }
    }
});

// Setup sidebar clockout form
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('sidebar-clockout-form');
    const closeBtns = document.querySelectorAll('#btn-close-sidebar-clockout, #btn-cancel-sidebar-clockout');
    const modal = document.getElementById('sidebar-clockout-modal');

    closeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            if (modal) {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
            }
        });
    });

    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === this) {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
            }
        });
    }

    if (form) {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            const submitBtn = document.getElementById('btn-submit-sidebar-clockout');
            const notesInput = document.getElementById('sidebar-shift-notes');
            const errEl = document.getElementById('sidebar-clockout-error');

            if (errEl) {
                errEl.textContent = '';
                errEl.classList.add('hidden');
            }
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = `
                    <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                    <span>Ending Shift...</span>
                `;
            }

            try {
                const notes = notesInput ? notesInput.value.trim() : '';
                const res = await window.api.post('/staff-portal/shift/clock-out', { notes });
                if (res && res.success) {
                    showToast('Shift ended successfully.', 'success');
                    if (modal) {
                        modal.classList.remove('flex');
                        modal.classList.add('hidden');
                    }
                    updateSidebarShiftPill({ status: 'not_clocked_in' });
                    setTimeout(() => window.location.reload(), 600);
                } else {
                    const msg = (res && res.message) ? res.message : 'Failed to end shift';
                    if (errEl) {
                        errEl.textContent = msg;
                        errEl.classList.remove('hidden');
                    } else {
                        showToast(msg, 'error');
                    }
                }
            } catch (err) {
                const msg = err.message || 'Error communicating with server';
                if (errEl) {
                    errEl.textContent = msg;
                    errEl.classList.remove('hidden');
                } else {
                    showToast(msg, 'error');
                }
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = `
                        <span class="material-symbols-outlined text-base" aria-hidden="true">check_circle</span>
                        <span>Confirm Clock Out</span>
                    `;
                }
            }
        });
    }

    // Auto-fetch staff session on initial load
    fetchStaffSession();
});

// ── 7. TOAST NOTIFICATION ──
function showToast(message, type = 'success', duration = 3000) {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'fixed bottom-5 right-5 z-50 flex flex-col gap-2 pointer-events-none';
        document.body.appendChild(container);
    }
    const toast = document.createElement('div');
    const typeClasses = {
        success: 'bg-emerald-600 text-white shadow-emerald-500/20',
        error: 'bg-rose-600 text-white shadow-rose-500/20',
        danger: 'bg-rose-600 text-white shadow-rose-500/20',
        warning: 'bg-amber-600 text-white shadow-amber-500/20',
        info: 'bg-blue-600 text-white shadow-blue-500/20'
    };
    const icons = {
        success: 'check_circle',
        error: 'cancel',
        danger: 'cancel',
        warning: 'warning',
        info: 'info'
    };
    const bg = typeClasses[type] || typeClasses.info;
    const icon = icons[type] || 'info';

    toast.className = `pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl shadow-xl font-bold text-sm transition-all transform duration-300 translate-y-2 opacity-0 ${bg}`;
    toast.innerHTML = `
        <span class="material-symbols-outlined text-[20px] shrink-0" aria-hidden="true">${icon}</span>
        <span class="flex-1">${message}</span>
    `;
    container.appendChild(toast);
    requestAnimationFrame(() => {
        toast.classList.remove('translate-y-2', 'opacity-0');
        toast.classList.add('translate-y-0', 'opacity-100');
    });
    setTimeout(() => {
        toast.classList.add('opacity-0', 'translate-y-2');
        setTimeout(() => toast.remove(), 350);
    }, duration);
}
window.showToast = showToast;
