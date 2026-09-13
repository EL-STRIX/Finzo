/**
 * Finzo Staff — Transaction History Controller
 * Pure Vanilla JavaScript — Clean Architecture, Real-Time Filters & Live Metrics
 */

(function () {
    'use strict';

    // ── State ──
    let currentPage = 1;
    const itemsPerPage = 10;
    let isFetching = false;
    let searchDebounceTimer = null;

    const currentFilters = {
        search: '',
        type: 'all',
        status: 'all',
        range: 'All Time',
        from_date: '',
        to_date: ''
    };

    // ── Safe HTML Escape ──
    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        const div = document.createElement('div');
        div.textContent = String(str);
        return div.innerHTML;
    }

    // ── Date Helpers ──
    function getTodayYmd() {
        const d = new Date();
        const yyyy = d.getFullYear();
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const dd = String(d.getDate()).padStart(2, '0');
        return `${yyyy}-${mm}-${dd}`;
    }

    function getNDaysAgoYmd(n) {
        const d = new Date();
        d.setDate(d.getDate() - n);
        const yyyy = d.getFullYear();
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const dd = String(d.getDate()).padStart(2, '0');
        return `${yyyy}-${mm}-${dd}`;
    }

    function getMonthStartYmd() {
        const d = new Date();
        const yyyy = d.getFullYear();
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        return `${yyyy}-${mm}-01`;
    }

    // ── Init on DOM Ready ──
    function init() {
        if (typeof fetchStaffSession === 'function') {
            fetchStaffSession();
        }

        bindEvents();
        loadHistory();
    }

    function ready(fn) {
        if (document.readyState !== 'loading' && window.api) {
            fn();
        } else {
            const handler = () => {
                if (window.api) {
                    fn();
                } else {
                    setTimeout(fn, 50);
                }
            };
            if (document.readyState !== 'loading') {
                setTimeout(handler, 20);
            } else {
                document.addEventListener('DOMContentLoaded', handler);
            }
        }
    }

    ready(init);

    // ── Event Bindings ──
    function bindEvents() {
        // Search Input with Debounce
        const searchInput = document.getElementById('search-input');
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                clearTimeout(searchDebounceTimer);
                searchDebounceTimer = setTimeout(() => {
                    currentFilters.search = searchInput.value.trim();
                    currentPage = 1;
                    loadHistory();
                }, 300);
            });

            searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(searchDebounceTimer);
                    currentFilters.search = searchInput.value.trim();
                    currentPage = 1;
                    loadHistory();
                }
            });
        }

        // Filter Type Select
        const filterType = document.getElementById('filter-type');
        if (filterType) {
            filterType.addEventListener('change', () => {
                currentFilters.type = filterType.value;
                currentPage = 1;
                loadHistory();
            });
        }

        // Filter Status Select
        const filterStatus = document.getElementById('filter-status');
        if (filterStatus) {
            filterStatus.addEventListener('change', () => {
                currentFilters.status = filterStatus.value;
                currentPage = 1;
                loadHistory();
            });
        }

        // Time Range Tabs
        const timeTabs = document.querySelectorAll('.time-tab');
        timeTabs.forEach((btn) => {
            btn.addEventListener('click', () => {
                timeTabs.forEach(t => {
                    t.className = 'time-tab px-4 py-2 text-text-muted dark:text-text-muted font-bold hover:text-text-main dark:hover:text-white transition-all text-xs uppercase tracking-widest';
                });
                btn.className = 'time-tab px-4 py-2 bg-warning-base text-white font-bold rounded-lg text-xs uppercase tracking-widest shadow shadow-amber-500/20';

                const range = btn.getAttribute('data-range') || btn.textContent.trim();
                currentFilters.range = range;

                const customRow = document.getElementById('custom-date-row');
                if (range === 'Custom') {
                    if (customRow) customRow.classList.remove('hidden');
                } else {
                    if (customRow) customRow.classList.add('hidden');
                    const fromInput = document.getElementById('filter-from-date');
                    const toInput = document.getElementById('filter-to-date');
                    if (fromInput) fromInput.value = '';
                    if (toInput) toInput.value = '';
                    currentFilters.from_date = '';
                    currentFilters.to_date = '';
                }

                currentPage = 1;
                loadHistory();
            });
        });

        // Custom Date Inputs
        const fromDateInput = document.getElementById('filter-from-date');
        const toDateInput = document.getElementById('filter-to-date');
        if (fromDateInput) {
            fromDateInput.addEventListener('change', () => {
                currentFilters.from_date = fromDateInput.value;
                currentPage = 1;
                loadHistory();
            });
        }
        if (toDateInput) {
            toDateInput.addEventListener('change', () => {
                currentFilters.to_date = toDateInput.value;
                currentPage = 1;
                loadHistory();
            });
        }

        // Reset Filters Button
        const resetBtn = document.getElementById('reset-filters-btn');
        if (resetBtn) {
            resetBtn.addEventListener('click', resetAllFilters);
        }

        // Manual Refresh Button
        const refreshBtn = document.getElementById('refresh-history-btn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                const spinner = document.getElementById('refresh-spinner');
                if (spinner) spinner.classList.add('animate-spin');
                loadHistory().finally(() => {
                    setTimeout(() => {
                        if (spinner) spinner.classList.remove('animate-spin');
                    }, 500);
                });
            });
        }

        // Print All Report Button
        const printBtn = document.getElementById('print-all-btn');
        if (printBtn) {
            printBtn.addEventListener('click', printHistoryReport);
        }
    }

    // ── Reset Filters ──
    function resetAllFilters() {
        currentFilters.search = '';
        currentFilters.type = 'all';
        currentFilters.status = 'all';
        currentFilters.range = 'All Time';
        currentFilters.from_date = '';
        currentFilters.to_date = '';
        currentPage = 1;

        const searchInput = document.getElementById('search-input');
        if (searchInput) searchInput.value = '';

        const filterType = document.getElementById('filter-type');
        if (filterType) filterType.value = 'all';

        const filterStatus = document.getElementById('filter-status');
        if (filterStatus) filterStatus.value = 'all';

        const fromInput = document.getElementById('filter-from-date');
        const toInput = document.getElementById('filter-to-date');
        if (fromInput) fromInput.value = '';
        if (toInput) toInput.value = '';

        const customRow = document.getElementById('custom-date-row');
        if (customRow) customRow.classList.add('hidden');

        const timeTabs = document.querySelectorAll('.time-tab');
        timeTabs.forEach((t) => {
            const isAllTime = (t.getAttribute('data-range') || t.textContent.trim()) === 'All Time';
            if (isAllTime) {
                t.className = 'time-tab px-4 py-2 bg-warning-base text-white font-bold rounded-lg text-xs uppercase tracking-widest shadow shadow-amber-500/20';
            } else {
                t.className = 'time-tab px-4 py-2 text-text-muted dark:text-text-muted font-bold hover:text-text-main dark:hover:text-white transition-all text-xs uppercase tracking-widest';
            }
        });

        loadHistory();
    }

    // ── Check Active Filter State ──
    function updateResetButtonVisibility() {
        const hasActiveFilters = Boolean(
            currentFilters.search ||
            currentFilters.type !== 'all' ||
            currentFilters.status !== 'all' ||
            currentFilters.range !== 'All Time' ||
            currentFilters.from_date ||
            currentFilters.to_date
        );

        const resetBtn = document.getElementById('reset-filters-btn');
        if (resetBtn) {
            resetBtn.classList.toggle('hidden', !hasActiveFilters);
        }
    }

    // ── Load History & Stats ──
    async function loadHistory() {
        if (isFetching) return;
        isFetching = true;

        updateResetButtonVisibility();

        const tbody = document.getElementById('transaction-table-body');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-12 text-text-muted text-sm">
                        <div class="inline-flex items-center gap-2">
                            <span class="material-symbols-outlined text-lg animate-spin text-warning-base" aria-hidden="true">sync</span>
                            <span>Loading transactions...</span>
                        </div>
                    </td>
                </tr>
            `;
        }

        // Determine Effective Date Range
        let from_date = currentFilters.from_date;
        let to_date = currentFilters.to_date;
        const today = getTodayYmd();

        if (currentFilters.range === 'Today') {
            from_date = today;
            to_date = today;
        } else if (currentFilters.range === 'This Week') {
            from_date = getNDaysAgoYmd(7);
            to_date = today;
        } else if (currentFilters.range === 'This Month') {
            from_date = getMonthStartYmd();
            to_date = today;
        }

        const queryParams = {
            page: currentPage,
            per_page: itemsPerPage,
            search: currentFilters.search,
            type: currentFilters.type,
            status: currentFilters.status,
            from_date: from_date,
            to_date: to_date
        };

        try {
            let res = null;
            if (window.api && typeof window.api.get === 'function') {
                res = await window.api.get('/staff-portal/history', queryParams);
            } else {
                const qs = new URLSearchParams(queryParams).toString();
                const response = await fetch('/Finzo/api/v1/staff-portal/history?' + qs, {
                    credentials: 'include',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                res = await response.json();
            }

            const payload = (res && res.data && typeof res.data === 'object' && (res.data.transactions || res.data.stats)) ? res.data : (res || {});
            const hasSuccess = res && (res.success === true || payload.transactions !== undefined);

            if (hasSuccess) {
                const stats = payload.stats || res.stats || null;
                if (stats) {
                    updateStatsUI(stats);
                }

                const transactions = payload.transactions || res.transactions || [];
                const total = payload.total !== undefined ? payload.total : (res.total !== undefined ? res.total : transactions.length);
                const page = payload.current_page || res.current_page || currentPage;
                const pages = payload.pages || res.pages || 1;

                renderTable(transactions, total, page, pages);
            } else {
                renderError((res && res.message) ? res.message : 'Failed to retrieve transaction history.');
            }
        } catch (err) {
            console.error('History load error:', err);
            renderError('A network error occurred while loading transaction history.');
        } finally {
            isFetching = false;
        }
    }

    // ── Update Stats Cards ──
    function updateStatsUI(stats) {
        if (!stats || typeof stats !== 'object') return;
        const fmt = (n) => {
            if (typeof formatINR === 'function') return formatINR(n);
            if (window.FinzoUtils && typeof window.FinzoUtils.formatINR === 'function') return window.FinzoUtils.formatINR(n);
            return '₹' + parseFloat(n || 0).toFixed(2);
        };

        const statDeposits = document.getElementById('stat-deposits');
        const statWithdrawals = document.getElementById('stat-withdrawals');
        const statTransactions = document.getElementById('stat-transactions');
        const statMonthCount = document.getElementById('stat-month-count');
        const statMonthLabel = document.getElementById('stat-month-label');
        const depBadge = document.getElementById('stat-deposits-badge');
        const withBadge = document.getElementById('stat-withdrawals-badge');

        if (statDeposits && stats.deposits_total !== undefined) statDeposits.textContent = fmt(stats.deposits_total);
        if (statWithdrawals && stats.withdrawals_total !== undefined) statWithdrawals.textContent = fmt(stats.withdrawals_total);
        if (statTransactions && stats.total_all_time !== undefined) statTransactions.textContent = Number(stats.total_all_time || 0).toLocaleString();
        if (statMonthCount && stats.total_this_month !== undefined) statMonthCount.textContent = Number(stats.total_this_month || 0).toLocaleString();

        if (depBadge && stats.deposits_count !== undefined) {
            depBadge.textContent = `${stats.deposits_count} deposit${stats.deposits_count === 1 ? '' : 's'}`;
        }
        if (withBadge && stats.withdrawals_count !== undefined) {
            withBadge.textContent = `${stats.withdrawals_count} withdrawal${stats.withdrawals_count === 1 ? '' : 's'}`;
        }

        if (statMonthLabel) {
            const now = new Date();
            statMonthLabel.textContent = now.toLocaleDateString('en-IN', { month: 'short', year: 'numeric' });
        }
    }

    // ── Render History Table ──
    function renderTable(transactions, totalItems, page, totalPages) {
        const tbody = document.getElementById('transaction-table-body');
        if (!tbody) return;

        if (!transactions || transactions.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-16">
                        <div class="w-12 h-12 rounded-full bg-surface-subtle dark:bg-white/5 flex items-center justify-center mx-auto mb-3 text-text-muted">
                            <span class="material-symbols-outlined text-2xl" aria-hidden="true">receipt_long</span>
                        </div>
                        <h4 class="text-sm font-bold text-text-main dark:text-white">No transactions found</h4>
                        <p class="text-xs text-text-muted mt-1 max-w-sm mx-auto">There are no operational transactions matching your current filters or date range.</p>
                    </td>
                </tr>
            `;
            renderPagination(1, 1, 0);
            return;
        }

        const fmt = (n) => {
            if (typeof formatINR === 'function') return formatINR(n);
            if (window.FinzoUtils && typeof window.FinzoUtils.formatINR === 'function') return window.FinzoUtils.formatINR(n);
            return '₹' + parseFloat(n || 0).toFixed(2);
        };

        let html = '';
        transactions.forEach((item, idx) => {
            const isDeposit = (item.type || '').toLowerCase() === 'deposit';
            const rowNumber = (page - 1) * itemsPerPage + idx + 1;

            const typeBadge = isDeposit
                ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20'
                : 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20';

            const status = (item.status || 'completed').toLowerCase();
            let statusBadge = 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20';
            if (status === 'pending') {
                statusBadge = 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20';
            } else if (status === 'failed') {
                statusBadge = 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20';
            }

            const amtColor = isDeposit ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400';
            const amtPrefix = isDeposit ? '+' : '-';

            const rawDate = item.created_at || item.date || '';
            const dateObj = new Date(rawDate ? rawDate.replace(' ', 'T') : Date.now());
            const dateStr = !isNaN(dateObj.getTime())
                ? dateObj.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' })
                : (rawDate.split(' ')[0] || '—');
            const timeStr = !isNaN(dateObj.getTime())
                ? dateObj.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit', hour12: true })
                : (rawDate.split(' ')[1] || '');

            const custName = item.customer_name || 'Customer';
            const initials = custName
                .split(' ')
                .filter(w => /^[a-zA-Z]/.test(w))
                .map(n => n[0])
                .join('')
                .substring(0, 2)
                .toUpperCase() || 'CU';

            html += `
                <tr class="hover:bg-surface-subtle dark:hover:bg-white/5 transition-colors">
                    <td class="p-3.5 text-xs font-bold text-text-muted text-center">${rowNumber}</td>
                    <td class="p-3.5 text-xs font-bold text-text-main dark:text-white">
                        <div>${escapeHtml(dateStr)}</div>
                        <div class="text-[10px] text-text-muted font-normal mt-0.5">${escapeHtml(timeStr)}</div>
                    </td>
                    <td class="p-3.5">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-surface-subtle dark:bg-white/10 flex items-center justify-center text-xs font-bold text-text-main dark:text-white shrink-0 shadow-sm">
                                ${escapeHtml(initials)}
                            </div>
                            <div class="min-w-0">
                                <div class="text-sm font-bold text-text-main dark:text-white truncate max-w-[170px]" title="${escapeHtml(custName)}">
                                    ${escapeHtml(custName)}
                                </div>
                                <div class="text-[10px] text-text-muted font-mono truncate">
                                    REF: ${escapeHtml(item.txn_id)}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="p-3.5 font-mono text-xs font-bold text-text-muted">
                        ${escapeHtml(item.account_no || 'N/A')}
                    </td>
                    <td class="p-3.5">
                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest ${typeBadge}">
                            ${escapeHtml(item.type)}
                        </span>
                    </td>
                    <td class="p-3.5 font-mono font-black text-sm ${amtColor}">
                        ${amtPrefix}${fmt(item.amount)}
                    </td>
                    <td class="p-3.5">
                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest ${statusBadge}">
                            ${escapeHtml(item.status)}
                        </span>
                    </td>
                    <td class="p-3.5 text-right">
                        <a href="staff-receipt.php?id=${encodeURIComponent(item.txn_id)}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-text-muted hover:text-warning-base hover:bg-warning-surface dark:hover:bg-warning-surface/10 transition-colors" title="Print/View Receipt" aria-label="View receipt for transaction ${escapeHtml(item.txn_id)}">
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">receipt</span>
                        </a>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
        renderPagination(page, totalPages, totalItems);
    }

    // ── Render Pagination Controls ──
    function renderPagination(current, total, totalItems) {
        const startIdx = totalItems > 0 ? (current - 1) * itemsPerPage + 1 : 0;
        const endIdx = Math.min(startIdx + itemsPerPage - 1, totalItems);

        const showingMeta = document.getElementById('showing-meta');
        if (showingMeta) {
            showingMeta.textContent = `Showing ${startIdx}-${endIdx} of ${totalItems}`;
        }

        const pButtons = document.getElementById('page-buttons');
        if (!pButtons) return;

        pButtons.innerHTML = '';
        if (total <= 1) return;

        // Previous Page Button
        const prevBtn = document.createElement('button');
        prevBtn.type = 'button';
        prevBtn.className = `px-2 py-1 rounded text-text-muted hover:bg-surface-subtle dark:hover:bg-white/10 ${current === 1 ? 'opacity-40 pointer-events-none' : 'cursor-pointer'}`;
        prevBtn.innerHTML = '<span class="material-symbols-outlined text-base">chevron_left</span>';
        prevBtn.setAttribute('aria-label', 'Previous Page');
        prevBtn.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                loadHistory();
            }
        });
        pButtons.appendChild(prevBtn);

        // Page Numbers (Sliding Window)
        let start = Math.max(1, current - 2);
        let end = Math.min(total, start + 4);
        if (end - start < 4) start = Math.max(1, end - 4);

        for (let i = start; i <= end; i++) {
            const pageBtn = document.createElement('button');
            pageBtn.type = 'button';
            const isActive = (i === current);
            pageBtn.className = `w-8 h-8 rounded text-xs font-bold cursor-pointer transition-all ${isActive ? 'bg-warning-base text-white shadow-md shadow-amber-500/20' : 'text-text-muted hover:bg-surface-subtle dark:hover:bg-white/10 hover:text-text-main dark:hover:text-white'}`;
            pageBtn.textContent = i.toString();
            pageBtn.addEventListener('click', () => {
                if (currentPage !== i) {
                    currentPage = i;
                    loadHistory();
                }
            });
            pButtons.appendChild(pageBtn);
        }

        // Next Page Button
        const nextBtn = document.createElement('button');
        nextBtn.type = 'button';
        nextBtn.className = `px-2 py-1 rounded text-text-muted hover:bg-surface-subtle dark:hover:bg-white/10 ${current === total ? 'opacity-40 pointer-events-none' : 'cursor-pointer'}`;
        nextBtn.innerHTML = '<span class="material-symbols-outlined text-base">chevron_right</span>';
        nextBtn.setAttribute('aria-label', 'Next Page');
        nextBtn.addEventListener('click', () => {
            if (currentPage < total) {
                currentPage++;
                loadHistory();
            }
        });
        pButtons.appendChild(nextBtn);
    }

    // ── Render Error State ──
    function renderError(message) {
        const tbody = document.getElementById('transaction-table-body');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-12 text-danger-base font-bold text-sm">
                        <div class="flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined" aria-hidden="true">error</span>
                            <span>${escapeHtml(message)}</span>
                        </div>
                    </td>
                </tr>
            `;
        }
    }

    // ── Print Report Functionality ──
    async function printHistoryReport() {
        const btn = document.getElementById('print-all-btn');
        if (!btn) return;

        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="material-symbols-outlined text-base animate-spin">sync</span><span>Loading...</span>';

        let from_date = currentFilters.from_date;
        let to_date = currentFilters.to_date;
        const today = getTodayYmd();

        if (currentFilters.range === 'Today') {
            from_date = today;
            to_date = today;
        } else if (currentFilters.range === 'This Week') {
            from_date = getNDaysAgoYmd(7);
            to_date = today;
        } else if (currentFilters.range === 'This Month') {
            from_date = getMonthStartYmd();
            to_date = today;
        }

        const queryParams = {
            page: 1,
            per_page: 999,
            search: currentFilters.search,
            type: currentFilters.type,
            status: currentFilters.status,
            from_date: from_date,
            to_date: to_date
        };

        try {
            let res = null;
            if (window.api && typeof window.api.get === 'function') {
                res = await window.api.get('/staff-portal/history', queryParams);
            } else {
                const qs = new URLSearchParams(queryParams).toString();
                const response = await fetch('/Finzo/api/v1/staff-portal/history?' + qs, {
                    credentials: 'include',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                res = await response.json();
            }

            btn.disabled = false;
            btn.innerHTML = originalHtml;

            const payload = (res && res.data && typeof res.data === 'object' && (res.data.transactions || res.data.stats)) ? res.data : (res || {});
            const txns = payload.transactions || (res && res.transactions) || [];

            if (!txns || txns.length === 0) {
                if (typeof showToast === 'function') {
                    showToast('No transactions found to print with current filters.', 'warning');
                } else {
                    alert('No transactions found to print with current filters.');
                }
                return;
            }

            let totalDeposits = 0;
            let totalWithdrawals = 0;

            txns.forEach((t) => {
                const amt = parseFloat(t.amount) || 0;
                if ((t.type || '').toLowerCase() === 'deposit') {
                    totalDeposits += amt;
                } else {
                    totalWithdrawals += amt;
                }
            });

            const fmt = (n) => {
                if (typeof formatINR === 'function') return formatINR(n);
                if (window.FinzoUtils && typeof window.FinzoUtils.formatINR === 'function') return window.FinzoUtils.formatINR(n);
                return '₹' + parseFloat(n || 0).toFixed(2);
            };
            const now = new Date();
            const printDate = now.toLocaleString('en-IN', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            });

            const staffName = (window.StaffState && window.StaffState.name) || 'Finzo Staff';
            const staffId = (window.StaffState && window.StaffState.staff_id) || 'STF001';

            let rowsHtml = '';
            txns.forEach((item, idx) => {
                const rawDate = item.created_at || item.date || '';
                const dateObj = new Date(rawDate ? rawDate.replace(' ', 'T') : Date.now());
                const dateStr = !isNaN(dateObj.getTime())
                    ? dateObj.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' })
                    : (rawDate.split(' ')[0] || '—');
                const isDeposit = (item.type || '').toLowerCase() === 'deposit';
                const amtColor = isDeposit ? '#059669' : '#e11d48';

                rowsHtml += `
                    <tr style="border-bottom: 1px solid #e2e8f0;">
                        <td style="padding: 6px 8px; font-size: 11px; text-align: center; color: #64748b;">${idx + 1}</td>
                        <td style="padding: 6px 8px; font-size: 11px; white-space: nowrap;">${escapeHtml(dateStr)}</td>
                        <td style="padding: 6px 8px; font-size: 11px;">
                            <strong>${escapeHtml(item.customer_name || 'Customer')}</strong><br>
                            <span style="font-family: monospace; font-size: 9px; color: #64748b;">${escapeHtml(item.txn_id)}</span>
                        </td>
                        <td style="padding: 6px 8px; font-size: 11px; font-family: monospace;">${escapeHtml(item.account_no || 'N/A')}</td>
                        <td style="padding: 6px 8px; font-size: 10px; text-transform: uppercase; font-weight: bold;">${escapeHtml(item.type)}</td>
                        <td style="padding: 6px 8px; font-size: 11px; font-family: monospace; font-weight: bold; color: ${amtColor}; text-align: right;">
                            ${isDeposit ? '+' : '-'}${fmt(item.amount)}
                        </td>
                        <td style="padding: 6px 8px; font-size: 10px; text-transform: uppercase; text-align: center;">${escapeHtml(item.status)}</td>
                    </tr>
                `;
            });

            const printHtml = `<!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <title>Finzo — Transaction Report</title>
                <style>
                    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; margin: 24px; color: #0f172a; font-size: 12px; }
                    .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 16px; }
                    .logo { font-size: 24px; font-weight: 900; letter-spacing: -0.5px; }
                    .subtitle { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #d97706; }
                    .stats-box { display: flex; gap: 24px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; margin-bottom: 16px; font-size: 12px; }
                    .stats-box strong { font-size: 14px; }
                    table { width: 100%; border-collapse: collapse; text-align: left; }
                    th { background: #f1f5f9; padding: 8px; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #cbd5e1; }
                    @media print {
                        body { margin: 0; }
                    }
                </style>
            </head>
            <body>
                <div class="header">
                    <div>
                        <div class="logo">Finzo</div>
                        <div class="subtitle">Enterprise Branch Operations</div>
                    </div>
                    <div style="text-align: right;">
                        <h3 style="margin: 0; font-size: 16px;">Teller Transaction Audit Report</h3>
                        <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Teller: ${escapeHtml(staffName)} (${escapeHtml(staffId)})</div>
                        <div style="font-size: 11px; color: #64748b;">Generated on: ${escapeHtml(printDate)}</div>
                    </div>
                </div>

                <div class="stats-box">
                    <div>Total Transactions: <strong>${txns.length}</strong></div>
                    <div>Total Deposits: <strong style="color: #059669;">${fmt(totalDeposits)}</strong></div>
                    <div>Total Withdrawals: <strong style="color: #e11d48;">${fmt(totalWithdrawals)}</strong></div>
                    <div>Net Flow: <strong>${fmt(totalDeposits - totalWithdrawals)}</strong></div>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th style="width: 30px; text-align: center;">#</th>
                            <th>Date</th>
                            <th>Customer & Ref</th>
                            <th>Account No</th>
                            <th>Type</th>
                            <th style="text-align: right;">Amount</th>
                            <th style="text-align: center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rowsHtml}
                    </tbody>
                </table>
            </body>
            </html>`;

            const printWin = window.open('', '_blank');
            if (printWin) {
                printWin.document.write(printHtml);
                printWin.document.close();
                printWin.focus();
                setTimeout(() => {
                    printWin.print();
                    printWin.close();
                }, 400);
            } else {
                window.print();
            }
        } catch (err) {
            console.error('Print error:', err);
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            if (typeof showToast === 'function') {
                showToast('Failed to generate printable report.', 'error');
            } else {
                alert('Failed to generate printable report.');
            }
        }
    }

    // Expose for testing/debugging
    window.FinzoStaffHistory = {
        load: loadHistory,
        reset: resetAllFilters,
        print: printHistoryReport
    };
})();

