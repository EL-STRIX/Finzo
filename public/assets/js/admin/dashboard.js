/**
 * Finzo Admin — Dashboard Handler
 * Version 1.1 — Integrated Chart.js updates and Premium UX
 */

(function() {
    let charts = {};

    /**
     * Load dashboard high-level stats, charts and recent users
     */
    async function loadDashboard() {
        try {
            const response = await window.api.get('/admin/dashboard');
            if (response.success) {
                const data = response.data || response;
                updateStats(data.stats);
                renderRecentUsers(data.recent_users);
                updateCharts(data);
            }
        } catch (error) {
            console.error('Dashboard load failed:', error);
            // Handle error UI if needed
        }
    }

    /**
     * Update the stat cards with real data
     */
    function updateStats(stats) {
        if (!stats) return;
        const cards = document.querySelectorAll('.admin-stat-card');
        
        // Define mapping based on card labels (flexible matching)
        cards.forEach(card => {
            const labelEl = card.querySelector('.admin-stat-label');
            const valEl = card.querySelector('.admin-stat-value');
            if (!labelEl || !valEl) return;

            const label = labelEl.textContent.trim().toUpperCase();
            let value = 0;
            let isPrice = false;

            if (label.includes('WITHDRAWALS')) { value = stats.withdrawals_today || 0; isPrice = true; }
            else if (label.includes('DEPOSITS')) { value = stats.deposits_today || 0; isPrice = true; }
            else if (label.includes('TRANSFERS')) { value = stats.transfers_today || 0; isPrice = true; }
            else if (label.includes('TOTAL ACCOUNTS')) { value = stats.total_accounts || 0; }
            else if (label.includes('TOTAL USERS')) { value = stats.total_users || 0; }
            else if (label.includes('INTEREST')) { value = stats.interest_credited || 0; isPrice = true; }
            else if (label.includes('PENDING APPROVALS')) { value = stats.pending_approvals || 0; }
            else if (label.includes('LOCKED ACCOUNTS')) { value = stats.locked_accounts || 0; }
            else if (label.includes('SUSPENDED USERS')) { value = stats.suspended_users || 0; }
            else if (label.includes('SOFT DELETED')) { value = stats.soft_deleted_users || 0; }
            else if (label.includes('ACTIVE STAFF')) { value = stats.total_staff || 0; }
            else if (label.includes('ACTIVE BRANCHES')) { value = stats.total_branches || 0; }

            valEl.classList.remove('skeleton');
            valEl.textContent = isPrice ? window.FinzoUtils.formatINR(value) : value.toLocaleString();
        });
    }

    /**
     * Render the recent user activity table
     */
    function renderRecentUsers(users) {
        const tbody = document.getElementById('dash-user-tbody');
        if (!tbody) return;

        if (!users || !users.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="py-12 text-center opacity-40 font-bold">No recent activity.</td></tr>';
            return;
        }

        tbody.innerHTML = users.map(u => {
            const initials = u.full_name.split(' ').map(n=>n[0]).join('').toUpperCase();
            const statusClass = u.status === 'active' ? 'badge-success' : (u.status === 'suspended' ? 'badge-error' : 'badge-warn');
            const gradient = window.FinzoUtils.getAvatarGradient(initials);
            
            const avatarHtml = u.profile_picture_url 
                ? `<img src="${u.profile_picture_url}" class="admin-avatar !w-10 !h-10 !rounded-xl object-cover" onerror="this.outerHTML='<div class=\\'admin-avatar !w-10 !h-10 !rounded-xl text-[12px]\\' style=\\'background:${gradient};color:#fff;\\'>${initials}</div>'">` 
                : `<div class="admin-avatar !w-10 !h-10 !rounded-xl text-[12px]" style="background:${gradient};color:#fff;">${initials}</div>`;

            return `
            <tr class="hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">
                <td>
                    <div class="flex items-center gap-3">
                        ${avatarHtml}
                        <div class="min-w-0">
                            <div class="font-bold text-sm truncate">${u.full_name}</div>
                            <div class="text-[10px] opacity-40 truncate">${u.email}</div>
                        </div>
                    </div>
                </td>
                <td><span class="status-badge ${statusClass}">${u.status.toUpperCase()}</span></td>
                <td><span class="status-badge ${u.status === 'active' ? 'badge-success' : 'badge-warn'}" style="font-size: 10px; padding: 4px 10px;">${u.status === 'active' ? 'APPROVED' : 'PENDING'}</span></td>
                <td class="text-xs font-semibold">Savings</td>
                <td class="text-[11px] opacity-40 font-bold">${window.FinzoUtils.timeAgo(u.created_at)}</td>
                <td class="text-right">
                    <a href="admin-user-detail.html?id=${u.id}" class="admin-btn-secondary !px-4 !py-2 !text-[10px] !rounded-xl inline-flex items-center gap-2">
                        <span class="material-symbols-outlined !text-xs">visibility</span> View
                    </a>
                </td>
            </tr>`;
        }).join('');
    }

    /**
     * Update Global Charts with real data
     */
    function updateCharts(data) {
        const isDark = document.documentElement.classList.contains('dark') || document.documentElement.getAttribute('data-theme') === 'dark';
        const textColor = isDark ? '#8b949e' : '#64748b';

        // 1. Transaction Volume (Line Chart)
        const txCanvas = document.getElementById('chart-transactions');
        if (txCanvas && data.monthly_chart_data) {
            const labels = data.monthly_chart_data.map(d => d.month);
            const deposits = data.monthly_chart_data.map(d => d.deposits);
            const withdrawals = data.monthly_chart_data.map(d => d.withdrawals);

            if (charts.transactions) {
                charts.transactions.data.labels = labels;
                charts.transactions.data.datasets[0].data = deposits;
                charts.transactions.data.datasets[1].data = withdrawals;
                charts.transactions.update();
            }
        }

        // 2. Account Distribution (Donut)
        const acCanvas = document.getElementById('chart-accounts');
        if (acCanvas && data.stats) {
            const stats = data.stats;
            const values = [stats.active_users, stats.pending_users, stats.suspended_users, stats.locked_accounts];
            
            // Update Legend numbers
            const activeEl = document.getElementById('legend-active');
            const pendingEl = document.getElementById('legend-pending');
            const suspendedEl = document.getElementById('legend-suspended');
            const lockedEl = document.getElementById('legend-locked');

            if (activeEl) activeEl.textContent = stats.active_users || 0;
            if (pendingEl) pendingEl.textContent = stats.pending_users || 0;
            if (suspendedEl) suspendedEl.textContent = stats.suspended_users || 0;
            if (lockedEl) lockedEl.textContent = stats.locked_accounts || 0;
            
            // Remove skeleton from legend
            document.querySelectorAll('#donut-legend .skeleton').forEach(el => el.classList.remove('skeleton'));

            if (charts.accounts) {
                charts.accounts.data.datasets[0].data = values;
                charts.accounts.update();
            }
        }

        // 3. Monthly Revenue (Bar Chart)
        const rvCanvas = document.getElementById('chart-revenue');
        if (rvCanvas && data.monthly_chart_data) {
            if (charts.revenue) {
                charts.revenue.data.labels = data.monthly_chart_data.map(d => d.month);
                charts.revenue.data.datasets[0].data = data.monthly_chart_data.map(d => d.deposits);
                charts.revenue.data.datasets[1].data = data.monthly_chart_data.map(d => d.withdrawals);
                charts.revenue.update();
            }
        }
    }

    /**
     * Initialize Chart instances with minimal placeholder/null config
     */
    function initCharts() {
        const isDark = document.documentElement.classList.contains('dark') || document.documentElement.getAttribute('data-theme') === 'dark';
        const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.05)';
        const textColor = isDark ? '#8b949e' : '#64748b';

        // Transactions Line Chart
        const txCtx = document.getElementById('chart-transactions');
        if (txCtx) {
            charts.transactions = new Chart(txCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [
                        { label: 'Deposits', data: [], borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.08)', fill: true, tension: 0.4, pointRadius: 4, pointHoverRadius: 7, borderWidth: 2.5 },
                        { label: 'Withdrawals', data: [], borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.08)', fill: true, tension: 0.4, pointRadius: 4, pointHoverRadius: 7, borderWidth: 2.5 }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { grid: { color: gridColor, drawBorder: false }, ticks: { color: textColor, font: { size: 10, weight: 600 } } },
                        x: { grid: { display: false }, ticks: { color: textColor, font: { size: 10, weight: 600 } } }
                    },
                    interaction: { mode: 'index', intersect: false }
                }
            });
        }

        // Accounts Donut Chart
        const acCtx = document.getElementById('chart-accounts');
        if (acCtx) {
            charts.accounts = new Chart(acCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Active', 'Pending', 'Suspended', 'Locked'],
                    datasets: [{
                        data: [],
                        backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#94a3b8'],
                        borderWidth: 0, hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: { legend: { display: false } }
                }
            });
        }

        // Revenue Bar Chart
        const rvCtx = document.getElementById('chart-revenue');
        if (rvCtx) {
            charts.revenue = new Chart(rvCtx, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [
                        { label: 'Deposits', data: [], backgroundColor: 'rgba(16,185,129,0.7)', borderRadius: 8, barPercentage: 0.6 },
                        { label: 'Withdrawals', data: [], backgroundColor: 'rgba(59,130,246,0.7)', borderRadius: 8, barPercentage: 0.6 }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { 
                        legend: { 
                            position: 'top', 
                            labels: { usePointStyle: true, pointStyle: 'circle', padding: 20, font: { size: 11, weight: 600 }, color: textColor } 
                        } 
                    },
                    scales: {
                        y: { grid: { color: gridColor, drawBorder: false }, ticks: { color: textColor, font: { size: 10, weight: 600 } } },
                        x: { grid: { display: false }, ticks: { color: textColor, font: { size: 10, weight: 600 } } }
                    }
                }
            });
        }
    }

    // Initialization
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            initCharts();
            loadDashboard();
        });
    } else {
        initCharts();
        loadDashboard();
    }
})();
