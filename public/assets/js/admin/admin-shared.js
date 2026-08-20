/**
 * Finzo Portals — Admin Shared Logic
 * Manages admin session, sidebar transitions, and global admin states.
 */

window.AdminPortal = {
    state: {
        admin: null,
        isLoaded: false
    },

    /**
     * Initial session check
     */
    init: async function() {
        try {
            const data = await window.api.get('/admin/auth/session');
            if (data.success) {
                const payload = data.data || data;
                const admin = payload.admin || data.admin;
                const stats = payload.stats || data.stats;
                const csrf = payload.csrf_token || data.csrf_token;
                this.state.admin = admin;
                window.csrfToken = csrf;
                const meta = document.querySelector('meta[name="csrf-token"]');
                if (meta) meta.content = csrf;
                this.state.isLoaded = true;
                this.syncHeader(admin, stats);
            }
        } catch (error) {
            console.error('Admin initialization failed:', error);
        }
        
        this.bindGlobalEvents();
    },

    /**
     * Update topbar/sidebar with admin info
     */
    syncHeader: function(admin, stats) {
        if (!admin) return;
        
        const name = admin.full_name || 'Admin';
        const initials = name.split(' ').filter(word => /^[a-zA-Z]/.test(word)).map(n => n[0]).join('').toUpperCase().substring(0, 2);

        // Update header name and ID spans
        const nameEl = document.getElementById('header-profile-name');
        const idEl = document.getElementById('header-profile-id');
        if (nameEl) nameEl.textContent = name;
        if (idEl) idEl.textContent = 'ID: ' + (admin.admin_id || '#' + (admin.id || '---'));


        // Update topbar admin avatar
        document.querySelectorAll('header .header-avatar').forEach(el => {
            if (admin.profile_photo) {
                let photoSrc = admin.profile_photo;
                if (!photoSrc.startsWith('http')) {
                    if (photoSrc.startsWith('/Finzo/public/')) {
                        // already full
                    } else if (photoSrc.startsWith('/uploads/') || photoSrc.startsWith('uploads/')) {
                        photoSrc = '/Finzo/public/' + photoSrc.replace(/^\/+/, '');
                    } else if (photoSrc.startsWith('/assets/') || photoSrc.startsWith('assets/')) {
                        photoSrc = '/Finzo/public/' + photoSrc.replace(/^\/+/, '');
                    } else {
                        photoSrc = '/Finzo/public/uploads/' + photoSrc.replace(/^\/+/, '');
                    }
                }
                const cacheBuster = '?v=' + new Date().getTime();
                const src = photoSrc + cacheBuster;
                el.innerHTML = `<img src="${src}" alt="${name}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">`;
                el.style.background = 'none';
            } else {
                el.textContent = initials;
                if (window.FinzoUtils) el.style.background = window.FinzoUtils.getAvatarGradient(initials);
            }
        });

    },

    /**
     * Bind UI events that exist on every admin page
     */
    bindGlobalEvents: function() {
        // Mobile Sidebar Toggle
        const menuBtn = document.getElementById('mobile-menu-btn');
        const sidebar = document.getElementById('admin-sidebar');
        const overlay = document.getElementById('sidebar-overlay');

        if (menuBtn && sidebar && overlay) {
            menuBtn.addEventListener('click', () => {
                sidebar.classList.add('mobile-open', 'active');
                overlay.classList.add('active');
            });

            overlay.addEventListener('click', () => {
                sidebar.classList.remove('mobile-open', 'active');
                overlay.classList.remove('active');
            });
        }

        // Standardized Logout
        document.querySelectorAll('.admin-nav-item').forEach(link => {
            if (link.textContent.toLowerCase().includes('logout')) {
                link.addEventListener('click', async (e) => {
                    e.preventDefault();
                    try {
                        await window.api.post('/auth/logout');
                        window.location.href = '/Finzo/public/views/auth/admin-login.php';
                    } catch (error) {
                        window.location.href = '/Finzo/public/views/auth/admin-login.php';
                    }
                });
            }
        });
    },

    /**
     * Show a standardized toast notification
     */
    showToast: function(message, type = 'success', duration = 4000) {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        const icons = { success: 'check_circle', error: 'error', warning: 'warning', info: 'info' };
        
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <span class="material-symbols-outlined" aria-hidden="true">${icons[type]}</span>
            <div style="flex:1">${message}</div>
            <button onclick="this.parentElement.remove()" style="opacity:0.5;transition:opacity 0.2s;background:none;border:none;cursor:pointer;">
                <span class="material-symbols-outlined" style="font-size:16px" aria-hidden="true">close</span>
            </button>
        `;

        container.appendChild(toast);

        // Animate in
        setTimeout(() => toast.classList.add('active'), 10);

        // Auto remove
        setTimeout(() => {
            toast.classList.remove('active');
            setTimeout(() => toast.remove(), 400);
        }, duration);
    }
};

// Aliases for broader compatibility across admin scripts
window.showToast = window.AdminPortal.showToast;

// Auto-init on load
document.addEventListener('DOMContentLoaded', () => window.AdminPortal.init());

// ==========================================
// Theme (Dark/Light Mode) Logic
// ==========================================
(function() {
    const STORAGE_KEY = 'finzo-theme';

    function applyTheme(theme) {
        const activeTheme = theme || localStorage.getItem(STORAGE_KEY) || 'light';
        
        document.documentElement.setAttribute('data-theme', activeTheme);
        document.documentElement.classList.toggle('dark', activeTheme === 'dark');
        
        localStorage.setItem(STORAGE_KEY, activeTheme);

        // Sync all icons and labels currently in the DOM
        document.querySelectorAll('[data-theme-icon]').forEach(el => {
            el.textContent = activeTheme === 'dark' ? 'light_mode' : 'dark_mode';
        });
        document.querySelectorAll('[data-theme-label]').forEach(el => {
            el.textContent = activeTheme === 'dark' ? 'Light Mode' : 'Dark Mode';
        });
    }

    function toggleTheme() {
        const current = localStorage.getItem(STORAGE_KEY) || 'light';
        const target = current === 'dark' ? 'light' : 'dark';
        applyTheme(target);
    }

    // Immediate execution to prevent FOUC
    const saved = localStorage.getItem(STORAGE_KEY) || 'light';
    document.documentElement.setAttribute('data-theme', saved);
    document.documentElement.classList.toggle('dark', saved === 'dark');

    window.FinzoTheme = { 
        toggle: toggleTheme, 
        apply: applyTheme,
        current: () => localStorage.getItem(STORAGE_KEY) || 'light'
    };

    // Global toggle listener
    document.addEventListener('click', function(e) {
        const toggleBtn = e.target.closest('[data-toggle-theme]');
        if (toggleBtn) {
            e.preventDefault();
            toggleTheme();
        }
    });

    // Final sync on load
    document.addEventListener('DOMContentLoaded', () => applyTheme());

    // Component Loaded Event (for active nav highlighting)
    document.addEventListener('componentsLoaded', function() {
        // Highlight active sidebar link
        const currentPath = window.location.pathname.split('/').pop();
        const sidebar = document.getElementById('admin-sidebar');
        if (sidebar) {
            const links = sidebar.querySelectorAll('a.admin-nav-item');
            links.forEach(link => {
                const href = link.getAttribute('href');
                if (href && href === currentPath) {
                    link.classList.remove('text-slate-400', 'hover:bg-white/5');
                    link.classList.add('bg-primary/20', 'text-primary', 'border', 'border-primary/30');
                }
            });
        }

        // Set Navbar Title based on page title
        const navbarTitle = document.getElementById('navbar-title');
        if (navbarTitle) {
            const title = document.title.split('—')[1];
            if (title) {
                navbarTitle.textContent = title.trim();
            }
        }
        
        // Rebind logout since component is newly loaded
        const logoutBtn = document.querySelector('.admin-logout-btn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', async (e) => {
                e.preventDefault();
                try {
                    await window.api.post('/auth/logout');
                    window.location.href = '/Finzo/public/views/auth/admin-login.php';
                } catch (error) {
                    window.location.href = '/Finzo/public/views/auth/admin-login.php';
                }
            });
        }
        
        // Apply theme to newly loaded components
        applyTheme();
        
    });
})();
