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

    // ── Dedicated Pure Black & White Vintage Bank Statement Printing via Isolated Frame ──
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

            const totalVolume = totalDeposits + totalWithdrawals;
            const now = new Date();
            const printDate = now.toLocaleString('en-IN', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            });

            const staff = window.StaffState || window.StaffSession || {};
            const staffName = (staff.name || staff.full_name || 'Finzo Staff').toUpperCase();
            const staffId = staff.staff_id || 'STF001';
            const branchName = (staff.branch_name || 'Finzo Main Branch').toUpperCase();
            const designation = (staff.role || staff.designation || 'TELLER / CASHIER').toUpperCase();

            let periodFormatted = 'ALL RECORDED TRANSACTIONS';
            if (currentFilters.range === 'Today') periodFormatted = 'TODAY (' + today + ')';
            else if (currentFilters.range === 'This Week') periodFormatted = 'PAST 7 DAYS (' + getNDaysAgoYmd(7) + ' TO ' + today + ')';
            else if (currentFilters.range === 'This Month') periodFormatted = 'THIS MONTH (' + getMonthStartYmd() + ' TO ' + today + ')';
            else if (from_date && to_date) periodFormatted = from_date + ' TO ' + to_date;

            const filterTypeFormatted = currentFilters.type === 'all' ? 'ALL TYPES' : currentFilters.type.toUpperCase();
            const filterStatusFormatted = currentFilters.status === 'all' ? 'ALL STATUSES' : currentFilters.status.toUpperCase();

            let rowsHtml = '';
            txns.forEach((item, idx) => {
                const rawDate = item.created_at || item.date || '';
                const dateObj = new Date(rawDate ? rawDate.replace(' ', 'T') : Date.now());
                const dateStr = !isNaN(dateObj.getTime())
                    ? dateObj.toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' })
                    : (rawDate.split(' ')[0] || 'N/A');
                const isDeposit = (item.type || '').toLowerCase() === 'deposit';
                const amt = parseFloat(item.amount) || 0;

                const debitDisplay = !isDeposit ? amt.toLocaleString('en-IN', { minimumFractionDigits: 2 }) : '-';
                const creditDisplay = isDeposit ? amt.toLocaleString('en-IN', { minimumFractionDigits: 2 }) : '-';
                const particulars = (escapeHtml(item.customer_name || 'Customer').toUpperCase()) + ' / ' + escapeHtml(item.txn_id || '');

                rowsHtml += `
                    <tr>
                        <td style="text-align: center;">${idx + 1}</td>
                        <td style="text-align: center; white-space: nowrap;">${dateStr}</td>
                        <td style="text-transform: uppercase;">${particulars}</td>
                        <td style="text-align: center; font-family: monospace;">${escapeHtml(item.account_no || 'N/A')}</td>
                        <td style="text-align: right;">${debitDisplay}</td>
                        <td style="text-align: right;">${creditDisplay}</td>
                        <td style="text-align: center; text-transform: uppercase; font-size: 9.5px; font-weight: bold;">${escapeHtml(item.status || 'COMPLETED')}</td>
                        <td style="text-align: center;">TLR</td>
                    </tr>
                `;
            });

            const printHtml = `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Statement - ${escapeHtml(staffName)} - ${escapeHtml(staffId)}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: "Courier New", Courier, "Lucida Console", Monaco, monospace;
            color: #000000;
            background: #ffffff;
            padding: 0;
            font-size: 11px;
            line-height: 1.3;
        }
        .passbook-container {
            width: 100%;
            border: 2px solid #000000;
            padding: 14px 16px;
        }
        .bank-header {
            text-align: center;
            border-bottom: 2px solid #000000;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .bank-title {
            font-size: 20px;
            font-weight: 900;
            letter-spacing: 2px;
            text-transform: uppercase;
            font-family: Georgia, serif;
        }
        .bank-sub {
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-top: 2px;
        }
        .bank-meta {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            font-weight: bold;
            border-top: 1px solid #000000;
            padding-top: 6px;
            margin-top: 6px;
        }
        .table-grid {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000000;
            margin-bottom: 10px;
            font-size: 10.5px;
        }
        .table-grid td, .table-grid th {
            border: 1px solid #000000;
            padding: 4px 6px;
        }
        .table-grid th {
            background-color: #f2f2f2;
            font-weight: 900;
            text-transform: uppercase;
            font-size: 10px;
        }
        .lbl {
            font-weight: bold;
            background-color: #f8f8f8;
            width: 25%;
        }
        .val {
            font-weight: bold;
            width: 25%;
        }
        .summary-box {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000000;
            margin-bottom: 10px;
            font-size: 10.5px;
            text-align: center;
        }
        .summary-box td {
            border: 1px solid #000000;
            padding: 6px 8px;
        }
        .summary-title {
            font-size: 9.5px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .summary-amt {
            font-size: 13px;
            font-weight: 900;
            margin-top: 2px;
        }
        .footer-notice {
            border-top: 1px solid #000000;
            padding-top: 6px;
            font-size: 9.5px;
            line-height: 1.35;
        }
    </style>
</head>
<body>
    <div class="passbook-container">
        <div class="bank-header">
            <div class="bank-title">FINZO BANK</div>
            <div class="bank-sub">TELLER OPERATION &amp; TRANSACTION AUDIT STATEMENT</div>
            <div class="bank-meta">
                <div>BRANCH: ${escapeHtml(branchName)}</div>
                <div>IFSC: FINZ0002026</div>
                <div>MICR: 400026002</div>
            </div>
        </div>

        <table class="table-grid">
            <tr>
                <td class="lbl">STAFF MEMBER</td>
                <td class="val" style="font-size: 12px; font-weight: 900;">${escapeHtml(staffName)}</td>
                <td class="lbl">STAFF ID</td>
                <td class="val" style="font-size: 12px;">${escapeHtml(staffId)}</td>
            </tr>
            <tr>
                <td class="lbl">DESIGNATION</td>
                <td class="val">${escapeHtml(designation)}</td>
                <td class="lbl">BRANCH</td>
                <td class="val">${escapeHtml(branchName)}</td>
            </tr>
            <tr>
                <td class="lbl">STATEMENT PERIOD</td>
                <td class="val">${escapeHtml(periodFormatted)}</td>
                <td class="lbl">GENERATED ON</td>
                <td class="val">${escapeHtml(printDate)}</td>
            </tr>
            <tr>
                <td class="lbl">FILTER APPLIED</td>
                <td class="val" colspan="3">TYPE: ${escapeHtml(filterTypeFormatted)} | STATUS: ${escapeHtml(filterStatusFormatted)}</td>
            </tr>
        </table>

        <table class="table-grid">
            <thead>
                <tr>
                    <th style="width: 32px; text-align: center;">LINE</th>
                    <th style="width: 75px; text-align: center;">DATE</th>
                    <th style="text-align: left;">PARTICULARS / DETAILS</th>
                    <th style="width: 105px; text-align: center;">ACCOUNT NO</th>
                    <th style="width: 95px; text-align: right;">WITHDRAWAL (DR) ₹</th>
                    <th style="width: 95px; text-align: right;">DEPOSIT (CR) ₹</th>
                    <th style="width: 70px; text-align: center;">STATUS</th>
                    <th style="width: 35px; text-align: center;">INIT</th>
                </tr>
            </thead>
            <tbody>
                ${rowsHtml}
            </tbody>
        </table>

        <table class="summary-box">
            <tr>
                <td style="width: 25%;">
                    <div class="summary-title">TOTAL WITHDRAWALS (DR)</div>
                    <div class="summary-amt">₹ ${totalWithdrawals.toLocaleString('en-IN', { minimumFractionDigits: 2 })}</div>
                </td>
                <td style="width: 25%;">
                    <div class="summary-title">TOTAL DEPOSITS (CR)</div>
                    <div class="summary-amt">₹ ${totalDeposits.toLocaleString('en-IN', { minimumFractionDigits: 2 })}</div>
                </td>
                <td style="width: 25%; background-color: #f2f2f2;">
                    <div class="summary-title">TOTAL PROCESSED VOLUME</div>
                    <div class="summary-amt">₹ ${totalVolume.toLocaleString('en-IN', { minimumFractionDigits: 2 })}</div>
                </td>
                <td style="width: 25%;">
                    <div class="summary-title">TOTAL TRANSACTIONS</div>
                    <div class="summary-amt">${txns.length}</div>
                </td>
            </tr>
        </table>

        <div style="margin-top: 24px; margin-bottom: 10px; display: flex; justify-content: space-between; text-align: center; font-size: 10px; font-weight: bold; text-transform: uppercase;">
            <div style="width: 40%;">
                <div style="border-bottom: 1px solid #000000; margin-bottom: 4px; height: 25px;"></div>
                <div>TELLER / CASHIER SIGNATURE</div>
            </div>
            <div style="width: 40%;">
                <div style="border-bottom: 1px solid #000000; margin-bottom: 4px; height: 25px;"></div>
                <div>BRANCH MANAGER / AUDITOR</div>
            </div>
        </div>

        <div class="footer-notice">
            <div>* Please examine this statement immediately. Discrepancies, if any, should be reported within 15 days.</div>
            <div style="display: flex; justify-content: space-between; margin-top: 4px; font-weight: bold;">
                <span>* COMPUTER GENERATED STATEMENT — NO PHYSICAL SIGNATURE REQUIRED | OFFICIAL BANK AUDIT RECORD</span>
                <span>PAGE 1 OF 1</span>
            </div>
        </div>
    </div>
</body>
</html>`;

            let printFrame = document.getElementById('finzo-staff-print-iframe');
            if (!printFrame) {
                printFrame = document.createElement('iframe');
                printFrame.id = 'finzo-staff-print-iframe';
                printFrame.style.position = 'fixed';
                printFrame.style.right = '0';
                printFrame.style.bottom = '0';
                printFrame.style.width = '0';
                printFrame.style.height = '0';
                printFrame.style.border = '0';
                document.body.appendChild(printFrame);
            }

            const frameDoc = printFrame.contentWindow || printFrame.contentDocument.document || printFrame.contentDocument;
            const doc = frameDoc.document || frameDoc;
            doc.open();
            doc.write(printHtml);
            doc.close();

            setTimeout(() => {
                printFrame.contentWindow.focus();
                printFrame.contentWindow.print();
            }, 300);
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

