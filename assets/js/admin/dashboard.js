/**
 * Finzo Admin — Dashboard Handler
 */

(function() {
    /**
     * Load dashboard high-level stats and recent users
     */
    async function loadDashboard() {
        try {
            const response = await window.api.get('/admin/dashboard.php');
            if (response.success) {
                updateStats(response.data.stats);
                renderRecentUsers(response.data.recent_users);
            }
        } catch (error) {
            console.error('Dashboard load failed:', error);
        }
    }

    /**
     * Update the stat cards with real data
     */
    function updateStats(stats) {
        const cards = document.querySelectorAll('.admin-stat-card');
        
        const mapping = [
            { label: 'Total Withdrawals', value: stats.withdrawals_today, isPrice: true },
            { label: 'Total Deposits', value: stats.deposits_today, isPrice: true },
            { label: 'Total Transfers', value: stats.transfers_today, isPrice: true },
            { label: 'Total Accounts', value: stats.total_users, isPrice: false }, 
            { label: 'TOTAL USERS', value: stats.total_users, isPrice: false },
            { label: 'Interest Credited', value: 0, isPrice: true },
            { label: 'PENDING APPROVALS', value: stats.pending_approvals, isPrice: false },
            { label: 'Locked Accounts', value: stats.locked_accounts, isPrice: false },
            { label: 'Suspend Users', value: stats.suspended_users, isPrice: false },
            { label: 'SOFT DELETED USERS', value: 0, isPrice: false },
            { label: 'ACTIVE STAFF', value: stats.total_staff, isPrice: false },
            { label: 'ACTIVE BRANCHES', value: stats.total_branches, isPrice: false }
        ];

        cards.forEach((card, i) => {
            const map = mapping[i];
            if (!map) return;
            const valEl = card.querySelector('.admin-stat-value');
            if (valEl) {
                valEl.classList.remove('skeleton');
                valEl.textContent = map.isPrice ? window.FinzoUtils.formatINR(map.value) : map.value.toLocaleString();
            }
        });
    }

    /**
     * Render the recent user activity table
     */
    function renderRecentUsers(users) {
        const tbody = document.getElementById('dash-user-tbody');
        if (!tbody) return;

        if (!users.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="py-12 text-center opacity-40 font-bold">No recent activity.</td></tr>';
            return;
        }

        tbody.innerHTML = users.map(u => {
            const initials = u.full_name.split(' ').map(n=>n[0]).join('').toUpperCase();
            const statusClass = u.status === 'active' ? 'badge-success' : (u.status === 'suspended' ? 'badge-error' : 'badge-warn');
            const gradient = window.FinzoUtils.getAvatarGradient(initials);
            
            return `
            <tr>
                <td>
                    <div class="flex items-center gap-3">
                        <div class="admin-avatar" style="background:${gradient};color:#fff;">${initials}</div>
                        <div>
                            <div class="font-bold text-sm">${u.full_name}</div>
                            <div class="text-[10px] opacity-60">${u.email}</div>
                        </div>
                    </div>
                </td>
                <td><span class="status-badge ${statusClass}">${u.status.toUpperCase()}</span></td>
                <td><span class="status-badge ${u.status === 'active' ? 'badge-success' : 'badge-warn'}" style="font-size: 10px; padding: 4px 10px;">${u.status === 'active' ? 'APPROVED' : 'PENDING'}</span></td>
                <td class="text-xs font-semibold">Savings</td>
                <td class="text-[11px] opacity-70">${window.FinzoUtils.timeAgo(u.created_at)}</td>
                <td class="text-right">
                    <a href="admin-users.html?id=${u.id}" class="admin-btn-secondary px-3 py-1.5 text-[10px] rounded-lg inline-flex items-center gap-2">
                        <span class="material-symbols-outlined !text-xs">visibility</span> View
                    </a>
                </td>
            </tr>`;
        }).join('');
    }

    // Initialization
    document.addEventListener('DOMContentLoaded', () => {
        loadDashboard();
    });
})();
