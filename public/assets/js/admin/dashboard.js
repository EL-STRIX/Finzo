/**
 * Finzo Admin — Dashboard Handler
 * Version 1.3 — Dynamic KPI Sync, Real-time Charts, Responsive Theme Styling, and Streamlined Rendering
 */

(function() {
    let charts = {};

    function fmtINR(val) {
        const num = parseFloat(val) || 0;
        return '₹' + num.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    /**
     * Load dashboard high-level stats, charts and recent users
     */
    async function loadDashboard() {
        try {
            const response = await window.api.get('/admin/dashboard');
            if (response && (response.success || response.status === 200 || response.data)) {
                const data = response.data || response;
                updateStats(data.stats || {});
                renderRecentUsers(data.recent_users || []);
                updateCharts(data);
            }
        } catch (error) {
            console.error('Dashboard load failed:', error);
        }
    }

    /**
     * Update the stat cards with real data
     */
    function updateStats(stats) {
        if (!stats) return;

        // ID based mapping
        const statMappings = {
            'stat-total-assets': { val: stats.total_balance ?? 0, isCurrency: true },
            'stat-deposits-today': { val: stats.deposits_today ?? 0, isCurrency: true },
            'stat-withdrawals-today': { val: stats.withdrawals_today ?? 0, isCurrency: true },
            'stat-active-accounts': { val: stats.total_accounts ?? 0, isCurrency: false },
            'stat-pending-kyc': { val: stats.pending_approvals ?? stats.pending_users ?? 0, isCurrency: false },
            'stat-locked-accounts': { val: stats.locked_accounts ?? 0, isCurrency: false },
            'stat-today-txns': { val: stats.transactions_today ?? 0, isCurrency: false },
            'stat-total-users': { val: stats.total_users ?? 0, isCurrency: false },
            'stat-monthly-txns': { val: stats.transactions_month ?? 0, isCurrency: false },
            'stat-interest-credited': { val: stats.interest_credited ?? 0, isCurrency: true },
            'stat-suspended-users': { val: stats.suspended_users ?? 0, isCurrency: false },
            'stat-total-branches': { val: stats.total_branches ?? 0, isCurrency: false }
        };

        for (const [id, item] of Object.entries(statMappings)) {
            const el = document.getElementById(id);
            if (el) {
                el.classList.remove('skeleton');
                el.textContent = item.isCurrency ? fmtINR(item.val) : Number(item.val).toLocaleString();
            }
        }

        // Fallback for querySelector on cards if IDs differ
        const cards = document.querySelectorAll('.admin-stat-card');
        cards.forEach(card => {
            const labelEl = card.querySelector('.admin-stat-label');
            const valEl = card.querySelector('.admin-stat-value');
            if (!labelEl || !valEl || valEl.id) return; // already updated by ID

            const label = labelEl.textContent.trim().toUpperCase();
            let value = 0;
            let isPrice = false;

            if (label.includes('WITHDRAWALS')) { value = stats.withdrawals_today || 0; isPrice = true; }
            else if (label.includes('DEPOSITS')) { value = stats.deposits_today || 0; isPrice = true; }
            else if (label.includes('TOTAL ASSETS')) { value = stats.total_balance || 0; isPrice = true; }
            else if (label.includes('TOTAL USERS')) { value = stats.total_users || 0; }
            else if (label.includes('LOCKED ACCOUNTS')) { value = stats.locked_accounts || 0; }
            else if (label.includes('ACTIVE ACCOUNTS')) { value = stats.total_accounts || 0; }
            else if (label.includes('PENDING KYC')) { value = stats.pending_approvals || 0; }
            else if (label.includes('TODAY\'S TRANSACTIONS')) { value = stats.transactions_today || 0; }
            else if (label.includes('MONTHLY TRANSACTIONS')) { value = stats.transactions_month || 0; }
            else if (label.includes('INTEREST CREDITED')) { value = stats.interest_credited || 0; isPrice = true; }
            else if (label.includes('SUSPENDED USERS')) { value = stats.suspended_users || 0; }
            else if (label.includes('TOTAL BRANCHES')) { value = stats.total_branches || 0; }

            valEl.classList.remove('skeleton');
            valEl.textContent = isPrice ? fmtINR(value) : Number(value).toLocaleString();
        });
    }

    /**
     * Render the recent user activity table
     */
    function renderRecentUsers(users) {
        const tbody = document.getElementById('dash-user-tbody');
        if (!tbody) return;

        if (!users || !users.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="py-12 text-center text-text-secondary text-xs font-bold uppercase tracking-wider">No recent users registered.</td></tr>';
            return;
        }

        tbody.innerHTML = users.map(u => {
            const fallbackName = u.full_name || 'Unknown User';
            const initials = fallbackName.split(' ').map(n=>n[0]).join('').substring(0, 2).toUpperCase();
            
            const isActive = (u.status === 'active');
            const isSuspended = (u.status === 'suspended');
            const statusBadge = isActive 
                ? '<span class="badge-tag bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>Active</span>'
                : (isSuspended 
                    ? '<span class="badge-tag bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20 text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>Suspended</span>'
                    : '<span class="badge-tag bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Under Review</span>');

            const isVerified = (u.kyc_status === 'approved' || u.kyc_status === 'verified' || u.status === 'active');
            const kycBadge = isVerified
                ? '<span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 rounded-md text-[10px] font-bold uppercase">VERIFIED</span>'
                : '<span class="px-2 py-0.5 bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 rounded-md text-[10px] font-bold uppercase">PENDING</span>';

            const avatarHtml = u.profile_picture_url 
                ? `<img src="${u.profile_picture_url}" class="w-10 h-10 rounded-xl object-cover border border-border-subtle" alt="${fallbackName}">` 
                : `<div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary/20 to-primary/5 text-primary border border-primary/20 font-black text-xs flex items-center justify-center uppercase shrink-0">${initials}</div>`;

            const timeDisplay = u.created_at ? new Date(u.created_at).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' }) : '—';

            return `
            <tr class="hover:bg-surface-hover transition-colors border-b border-border-subtle last:border-0 group">
                <td class="py-3">
                    <div class="flex items-center gap-3">
                        ${avatarHtml}
                        <div class="min-w-0">
                            <div class="font-bold text-sm text-text-primary group-hover:text-primary transition-colors truncate">${fallbackName}</div>
                            <div class="text-[10px] text-text-secondary truncate font-mono">${u.email || 'No Email'}</div>
                        </div>
                    </div>
                </td>
                <td>${statusBadge}</td>
                <td>${kycBadge}</td>
                <td class="text-xs font-bold text-text-secondary capitalize">${u.account_type || 'Savings'}</td>
                <td class="text-[11px] text-text-secondary font-bold whitespace-nowrap">${timeDisplay}</td>
                <td class="text-right">
                    <a href="/Finzo/public/views/admin/admin-user-detail.php?id=${u.id}" class="btn btn-secondary px-3 py-1.5 text-xs font-bold uppercase tracking-wider inline-flex items-center gap-1 shadow-sm transition-all group/btn">
                        <span>View</span>
                        <span class="material-symbols-outlined text-[14px] transition-transform group-hover/btn:translate-x-0.5" aria-hidden="true">arrow_forward</span>
                    </a>
                </td>
            </tr>`;
        }).join('');
    }

    /**
     * Update Global Charts with real data
     */
    function updateCharts(data) {
        const hasChartData = Array.isArray(data.monthly_chart_data) && data.monthly_chart_data.length > 0;

        // 1. Transaction Volume (Line Chart)
        const txCanvas = document.getElementById('chart-transactions');
        if (txCanvas && charts.transactions && hasChartData) {
            const labels = data.monthly_chart_data.map(d => d.month);
            const deposits = data.monthly_chart_data.map(d => Number(d.deposits || 0));
            const withdrawals = data.monthly_chart_data.map(d => Number(d.withdrawals || 0));
            const transfers = data.monthly_chart_data.map(d => Number(d.transfers || 0));

            charts.transactions.data.labels = labels;
            charts.transactions.data.datasets[0].data = deposits;
            charts.transactions.data.datasets[1].data = withdrawals;
            if (charts.transactions.data.datasets[2]) {
                charts.transactions.data.datasets[2].data = transfers;
            }
            charts.transactions.update();
        }

        // 2. Account Distribution (Donut)
        const acCanvas = document.getElementById('chart-accounts');
        if (acCanvas && data.stats) {
            const stats = data.stats;
            const activeVal = Number(stats.active_accounts ?? stats.active_users ?? 0);
            const pendingVal = Number(stats.pending_accounts ?? stats.pending_approvals ?? stats.pending_users ?? 0);
            const suspendedVal = Number(stats.suspended_accounts ?? stats.suspended_users ?? 0);
            const lockedVal = Number(stats.locked_accounts ?? 0);

            const values = [activeVal, pendingVal, suspendedVal, lockedVal];
            
            // Update Legend numbers
            const activeEl = document.getElementById('legend-active');
            const pendingEl = document.getElementById('legend-pending');
            const suspendedEl = document.getElementById('legend-suspended');
            const lockedEl = document.getElementById('legend-locked');

            if (activeEl) activeEl.textContent = String(activeVal);
            if (pendingEl) pendingEl.textContent = String(pendingVal);
            if (suspendedEl) suspendedEl.textContent = String(suspendedVal);
            if (lockedEl) lockedEl.textContent = String(lockedVal);
            
            document.querySelectorAll('#donut-legend .skeleton').forEach(el => el.classList.remove('skeleton'));

            if (charts.accounts) {
                charts.accounts.data.datasets[0].data = values;
                charts.accounts.update();
            }
        }

        // 3. Monthly Revenue (Bar Chart)
        const rvCanvas = document.getElementById('chart-revenue');
        if (rvCanvas && charts.revenue && hasChartData) {
            const labels = data.monthly_chart_data.map(d => d.month);
            const turnover = data.monthly_chart_data.map(d => Number(d.total_turnover ?? (Number(d.deposits || 0) + Number(d.withdrawals || 0) + Number(d.transfers || 0))));
            const netFlow = data.monthly_chart_data.map(d => Number(d.net_flow !== undefined ? d.net_flow : (Number(d.deposits || 0) - Number(d.withdrawals || 0))));

            charts.revenue.data.labels = labels;
            charts.revenue.data.datasets[0].data = turnover;
            charts.revenue.data.datasets[1].data = netFlow;
            charts.revenue.update();
        }
    }

    /**
     * Tooltip formatter for currency
     */
    const currencyTooltipCallback = {
        label: function(context) {
            let label = context.dataset.label || '';
            if (label) {
                label += ': ';
            }
            if (context.parsed.y !== null) {
                label += fmtINR(context.parsed.y);
            }
            return label;
        }
    };

    /**
     * Initialize Chart instances
     */
    function initCharts() {
        const getCssVar = (name) => getComputedStyle(document.documentElement).getPropertyValue(name).trim() || name;
        const gridColor = getCssVar('--color-border-subtle') || 'rgba(255,255,255,0.05)';
        const textColor = getCssVar('--color-text-secondary') || '#8b949e';

        // Transactions Line Chart
        const txCtx = document.getElementById('chart-transactions');
        if (txCtx) {
            charts.transactions = new Chart(txCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [
                        { label: 'Deposits', data: [], borderColor: '#10b981', backgroundColor: 'rgba(16, 185, 129, 0.1)', fill: true, tension: 0.4, pointRadius: 4, pointHoverRadius: 7, borderWidth: 2.5 },
                        { label: 'Withdrawals', data: [], borderColor: '#3b82f6', backgroundColor: 'rgba(59, 130, 246, 0.1)', fill: true, tension: 0.4, pointRadius: 4, pointHoverRadius: 7, borderWidth: 2.5 },
                        { label: 'Transfers', data: [], borderColor: '#f59e0b', backgroundColor: 'rgba(245, 158, 11, 0.1)', fill: true, tension: 0.4, pointRadius: 4, pointHoverRadius: 7, borderWidth: 2.5 }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { 
                        legend: { display: false },
                        tooltip: { callbacks: currencyTooltipCallback }
                    },
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
                        data: [0, 0, 0, 0],
                        backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#64748b'],
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
                        { label: 'Gross Turnover', data: [], backgroundColor: '#6366f1', borderRadius: 8, barPercentage: 0.6 },
                        { label: 'Net Inflow', data: [], backgroundColor: '#10b981', borderRadius: 8, barPercentage: 0.6 }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { 
                        legend: { 
                            position: 'top', 
                            labels: { usePointStyle: true, pointStyle: 'circle', padding: 20, font: { size: 11, weight: 600 }, color: textColor } 
                        },
                        tooltip: { callbacks: currencyTooltipCallback }
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
    function initClock() {
        const clockElement = document.getElementById("header-live-clock");
        if (clockElement) {
            const updateTime = () => {
                const d = new Date();
                clockElement.textContent = d.toLocaleTimeString("en-IN", {
                    hour: "2-digit",
                    minute: "2-digit",
                    second: "2-digit",
                    hour12: true
                });
            };
            setInterval(updateTime, 1000);
            updateTime();
        }
    }

    // Initialize Dashboard on DOM ready
    document.addEventListener('DOMContentLoaded', () => {
        initClock();
        initCharts();

        // 1. Instant First-Paint Hydration from Preloaded Server Data
        if (window.initialDashboardData && typeof window.initialDashboardData === 'object') {
            const initial = window.initialDashboardData;
            if (initial.stats) updateStats(initial.stats);
            if (initial.recent_users) renderRecentUsers(initial.recent_users);
            if (initial.monthly_chart_data || initial.stats) updateCharts(initial);
        }

        // 2. Silent Background Sync for real-time consistency
        loadDashboard();

        // 3. Periodic Real-Time Polling & Focus-Regain Sync (Every 15s)
        setInterval(loadDashboard, 15000);
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                loadDashboard();
            }
        });
    });
})();
