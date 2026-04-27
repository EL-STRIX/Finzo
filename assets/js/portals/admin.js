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

        // Update all admin avatars
        document.querySelectorAll('.admin-avatar').forEach(el => {
            if (admin.avatar) {
                const cacheBuster = '?v=' + new Date().getTime();
                const src = (admin.avatar.startsWith('http') ? admin.avatar : `../../${admin.avatar}`) + cacheBuster;
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
        document.querySelectorAll('a[href*="logout"]').forEach(link => {
            link.addEventListener('click', async (e) => {
                e.preventDefault();
                const res = await window.api.post('/auth/logout.php');
                window.location.href = res.redirect || '../../views/auth/login.html';
            });
        });
    }
};

// Auto-init on load
document.addEventListener('DOMContentLoaded', () => window.AdminPortal.init());
