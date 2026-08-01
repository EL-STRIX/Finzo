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
            const data = await window.api.get('/admin/dashboard.php');
            if (data.success) {
                this.state.admin = data.admin;
                window.csrfToken = data.csrf_token;
                const meta = document.querySelector('meta[name="csrf-token"]');
                if (meta) meta.content = data.csrf_token;
                this.state.isLoaded = true;
                this.syncHeader(data.admin, data.stats);
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
        
        const name = admin.full_name;
        const initials = name.split(' ').filter(word => /^[a-zA-Z]/.test(word)).map(n => n[0]).join('').toUpperCase();

        // Update only the topbar admin avatar to prevent overwriting avatars in data tables
        document.querySelectorAll('header.admin-topbar .admin-avatar').forEach(el => {
            if (admin.profile_photo) {
                const cacheBuster = '?v=' + new Date().getTime();
                const src = (admin.profile_photo.startsWith('http') ? admin.profile_photo : `/Finzo/public/assets/${admin.profile_photo}`) + cacheBuster;
                el.innerHTML = `<img src="${src}" alt="${name}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">`;
                el.style.background = 'none';
            } else {
                el.textContent = initials;
                el.style.background = window.FinzoUtils.getAvatarGradient(initials);
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
                        await window.api.post('/auth/logout.php');
                        window.location.href = '/Finzo/public/views/auth/admin-login.html';
                    } catch (error) {
                        window.location.href = '/Finzo/public/views/auth/admin-login.html';
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
            <span class="material-symbols-outlined">${icons[type]}</span>
            <div style="flex:1">${message}</div>
            <button onclick="this.parentElement.remove()" style="opacity:0.5;transition:opacity 0.2s;background:none;border:none;cursor:pointer;">
                <span class="material-symbols-outlined" style="font-size:16px">close</span>
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
window.portal = window.AdminPortal;
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
                    await window.api.post('/auth/logout.php');
                    window.location.href = '/Finzo/public/views/auth/admin-login.html';
                } catch (error) {
                    window.location.href = '/Finzo/public/views/auth/admin-login.html';
                }
            });
        }
        
        // Apply theme to newly loaded components
        applyTheme();
        
        // Re-bind sidebar toggle
        const menuBtn = document.getElementById('mobile-menu-btn');
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
    });
})();
