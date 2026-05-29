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
                const src = (admin.profile_photo.startsWith('http') ? admin.profile_photo : `../../${admin.profile_photo}`) + cacheBuster;
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
                        window.location.href = '../../views/auth/admin-login.html';
                    } catch (error) {
                        window.location.href = '../../views/auth/admin-login.html';
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
