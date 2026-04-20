/**
 * Finzo — Shared Admin Logic
 * Handles session validation, UI updates and common admin actions.
 */

const AdminState = {
    admin: null,
    isLoaded: false
};

/**
 * Fetch Admin Session and update UI
 */
async function fetchAdminSession() {
    try {
        const response = await api.get('/admin/dashboard.php'); // Uses dashboard as a soft session check
        if (response.success) {
            AdminState.admin = response.admin;
            AdminState.isLoaded = true;
            updateAdminUI(response.stats);
        } else {
            // Redirect to login if unauthorized
            if (response.message && response.message.includes('authorized')) {
                window.location.href = '../../views/auth/login.html';
            }
        }
    } catch (error) {
        console.error('Failed to fetch admin session:', error);
    }
}

/**
 * Update common UI elements (Sidebar, Topbar)
 */
function updateAdminUI(stats = null) {
    if (!AdminState.admin) return;

    const name = AdminState.admin.full_name;
    const initials = name.split(' ')
        .filter(word => /^[a-zA-Z]/.test(word)) 
        .map(n => n[0])
        .join('')
        .toUpperCase();

    // Update Avatar
    const avatars = document.querySelectorAll('.admin-avatar');
    const avatarUrl = AdminState.admin.avatar;
    
    avatars.forEach(el => {
        if (avatarUrl) {
            // If avatar starts with http, use as is, else prepend relative path
            // Add cache breaker to ensure fresh image load
            const src = (avatarUrl.startsWith('http') ? avatarUrl : `../../${avatarUrl}`) + '?v=' + new Date().getTime();
            
            el.innerHTML = `<img src="${src}" alt="${name}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;display:block;border:none;">`;
            el.style.padding = '0'; 
            el.style.background = 'none'; // Force remove any gradient
            el.style.backgroundColor = 'transparent';
        } else {
            el.textContent = initials;
        }
    });

    // Update Sidebar Badges if stats provided
    /* 
    if (stats) {
        updateSidebarBadge('admin-locked-accounts.html', stats.locked_accounts);
        updateSidebarBadge('admin-pending-approvals.html', stats.pending_approvals);
    }
    */
}

/**
 * Helper to update a sidebar link badge
 */
function updateSidebarBadge(href, count) {
    const link = document.querySelector(`.admin-nav-item[href="${href}"], .sidebar-nav-item[href="${href}"], a[href="${href}"]`);
    if (!link) return;

    let badge = link.querySelector('.nav-badge, .badge, span[style*="background"]');
    
    if (count > 0) {
        if (!badge) {
            // Create badge if missing
            badge = document.createElement('span');
            badge.style.cssText = "margin-left:auto;background:rgba(244,63,94,0.15);color:#f43f5e;font-size:10px;font-weight:800;padding:2px 7px;border-radius:6px;border:1px solid rgba(244,63,94,0.2);";
            link.appendChild(badge);
        }
        badge.textContent = count.toString().padStart(2, '0');
        badge.style.display = '';
    } else if (badge) {
        badge.style.display = 'none';
    }
}

/**
 * Global Admin Logout
 */
async function adminLogout() {
    try {
        const response = await api.post('/auth/logout.php');
        if (response.success || response.redirect) {
            window.location.href = response.redirect || '../../views/auth/login.html';
        }
    } catch (error) {
        console.error('Logout failed:', error);
        window.location.href = '../../views/auth/login.html';
    }
}

// Global Helpers
function formatINR(amount) {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        maximumFractionDigits: 0
    }).format(amount);
}

function formatDate(dateStr) {
    if (!dateStr) return 'N/A';
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-IN', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
}

// Initialize on load
document.addEventListener('DOMContentLoaded', () => {
    // Intercept logout links
    const logoutLinks = document.querySelectorAll('a[href*="logout.html"], a[href*="logout.php"]');
    logoutLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            adminLogout();
        });
    });

    // Initial session fetch
    fetchAdminSession();
});
