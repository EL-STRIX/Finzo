/**
 * Finzo Staff — Dashboard Handler
 * Version 1.0 — API Integration
 */

document.addEventListener('DOMContentLoaded', () => {
    loadDashboard();
});

/**
 * Load dashboard stats and recent activity
 */
async function loadDashboard() {
    try {
        const response = await window.api.get('/staff/dashboard');
        if (response.success) {
            const data = response.data || response;
            updateGreeting(data.staff, data.greeting);
            updateStats(data.shift_stats);
            renderRecentActivity(data.recent_activity);
        } else {
            showErrorState();
        }
    } catch (error) {
        console.error('Dashboard load failed:', error);
        showErrorState();
    }
}

/**
 * Update greeting message
 */
function updateGreeting(staff, greeting) {
    const welcomeEl = document.getElementById('welcome-text');
    if (welcomeEl && staff) {
        welcomeEl.textContent = `${greeting || 'Welcome'}, ${staff.name}`;
    }
}

/**
 * Update the stat cards
 */
function updateStats(stats) {
    if (!stats) return;
    
    const depositsEl = document.getElementById('stat-deposits');
    const withdrawalsEl = document.getElementById('stat-withdrawals');
    const transactionsEl = document.getElementById('stat-transactions');
    const netFlowEl = document.getElementById('stat-netflow');

    if (depositsEl) depositsEl.textContent = window.FinzoUtils.formatINR(stats.deposits_total || 0);
    if (withdrawalsEl) withdrawalsEl.textContent = window.FinzoUtils.formatINR(stats.withdrawals_total || 0);
    if (transactionsEl) transactionsEl.textContent = stats.total_transactions || 0;
    
    if (netFlowEl) {
        const netFlow = stats.net_cash_flow || 0;
        netFlowEl.textContent = window.FinzoUtils.formatINR(netFlow);
        if (netFlow < 0) {
            netFlowEl.classList.remove('text-info-base', 'dark:text-primary-base');
            netFlowEl.classList.add('text-danger-base', 'dark:text-danger-base');
        } else {
            netFlowEl.classList.remove('text-danger-base', 'dark:text-danger-base');
            netFlowEl.classList.add('text-info-base', 'dark:text-primary-base');
        }
    }
}

/**
 * Render the recent activity table
 */
function renderRecentActivity(activity) {
    const tbody = document.getElementById('txn-tbody');
    if (!tbody) return;

    if (!activity || !activity.length) {
        tbody.innerHTML = '<tr><td colspan=\"6\" class=\"py-12 text-center\"><div class=\"flex flex-col items-center justify-center\"><span class=\"material-symbols-outlined text-4xl text-text-muted/30 mb-3\">inventory_2</span><div class=\"text-sm font-bold text-text-muted uppercase tracking-widest\">No activity for this shift yet.</div></div></td></tr>';
        return;
    }

    tbody.innerHTML = activity.map(txn => {
        let typeBadge = '';
        if (txn.type === 'deposit') typeBadge = '<span class="status-badge badge-success">DEPOSIT</span>';
        else if (txn.type === 'withdrawal') typeBadge = '<span class="status-badge badge-danger">WITHDRAWAL</span>';
        else if (txn.type === 'transfer') typeBadge = '<span class="status-badge badge-info">TRANSFER</span>';
        else typeBadge = `<span class="status-badge badge-warning">${txn.type.toUpperCase()}</span>`;

        const amountClass = txn.type === 'withdrawal' ? 'text-danger-base' : (txn.type === 'deposit' ? 'text-success-base' : '');
        const amountPrefix = txn.type === 'withdrawal' ? '-' : (txn.type === 'deposit' ? '+' : '');

        return `
        <tr class="hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">
            <td class="text-xs font-bold whitespace-nowrap">${window.FinzoUtils.timeAgo(txn.created_at)}</td>
            <td>
                <div class="font-bold text-xs truncate max-w-[120px]">${txn.customer_name || 'N/A'}</div>
                <div class="text-[10px] opacity-40 truncate">${txn.txn_id}</div>
            </td>
            <td class="text-xs font-mono font-medium">${txn.account_no || 'N/A'}</td>
            <td>${typeBadge}</td>
            <td class="text-xs font-bold ${amountClass}">${amountPrefix}${window.FinzoUtils.formatINR(txn.amount)}</td>
            <td class="text-right">
                <a href="receipt.php?id=${txn.txn_id}" class="text-warning-base hover:underline text-[10px] uppercase font-bold tracking-wider">
                    Receipt
                </a>
            </td>
        </tr>`;
    }).join('');
}

/**
 * Fallback error state
 */
function showErrorState() {
    const tbody = document.getElementById('txn-tbody');
    if (tbody) {
        tbody.innerHTML = '<tr><td colspan=\"6\" class=\"py-12 text-center\"><div class=\"flex flex-col items-center justify-center\"><span class=\"material-symbols-outlined text-4xl text-text-muted/30 mb-3\">inventory_2</span><div class=\"text-sm font-bold text-text-muted uppercase tracking-widest\">Failed to load shift activity. Please refresh.</div></div></td></tr>';
    }
    
    const elements = ['stat-deposits', 'stat-withdrawals', 'stat-transactions', 'stat-netflow', 'welcome-text'];
    elements.forEach(id => {
        const el = document.getElementById(id);
        if (el && id !== 'welcome-text') el.textContent = 'Error';
        else if (el) el.textContent = 'Dashboard Error';
    });
}
