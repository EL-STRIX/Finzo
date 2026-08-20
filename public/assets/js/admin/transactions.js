/**
 * Finzo Admin — Global Transactions Controller
 * Manages table rendering, filters, statistics, and transaction audit details.
 */

(function() {
    'use strict';

    // State
    let currentType = 'all';
    let currentPage = 1;
    const rowsPerPage = 10;
    let isModalOpen = false;
    let activeTransactionsData = [];
    let activeSummaryData = {};

    /**
     * Format currency in Indian Rupees format (₹)
     */
    function formatCurrency(amount, withSign = true, type = '') {
        const num = parseFloat(amount) || 0;
        const formatted = num.toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        if (!withSign) return `₹${formatted}`;

        const isNegative = ['withdrawal'].includes(type.toLowerCase());
        const isPositive = ['deposit', 'interest_credit'].includes(type.toLowerCase());
        
        if (isNegative) return `-₹${formatted}`;
        if (isPositive) return `+₹${formatted}`;
        return `₹${formatted}`;
    }

    /**
     * Format compact currency (e.g. ₹12.5 K or ₹67.4 L)
     */
    function formatCompactCurrency(amount) {
        const num = parseFloat(amount) || 0;
        if (num >= 10000000) {
            return `₹${(num / 10000000).toFixed(2)} Cr`;
        }
        if (num >= 100000) {
            return `₹${(num / 100000).toFixed(2)} L`;
        }
        if (num >= 1000) {
            return `₹${(num / 1000).toFixed(1)} K`;
        }
        return `₹${num.toLocaleString('en-IN', { maximumFractionDigits: 0 })}`;
    }

    /**
     * Format date & time nicely (e.g., 19 Aug 2026, 08:25 PM)
     */
    function formatDateTime(dateStr) {
        if (!dateStr) return '---';
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        return d.toLocaleString('en-IN', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
    }

    /**
     * Get current active filter object
     */
    function getActiveFilters() {
        const searchInput = document.getElementById('transaction-search');
        const statusFilter = document.getElementById('filter-status');
        const dateFrom = document.getElementById('filter-date-from');
        const dateTo = document.getElementById('filter-date-to');
        const sortFilter = document.getElementById('filter-sort');

        return {
            type: currentType,
            status: statusFilter ? statusFilter.value : 'all',
            search: searchInput ? searchInput.value.trim() : '',
            from_date: dateFrom ? dateFrom.value : '',
            to_date: dateTo ? dateTo.value : '',
            sort: sortFilter ? sortFilter.value : 'newest'
        };
    }

    /**
     * Load transactions from backend API
     */
    async function loadTransactions() {
        const tbody = document.getElementById('txn-table-body');
        if (tbody) {
            renderSkeletonTable();
        }

        try {
            const params = {
                ...getActiveFilters(),
                page: currentPage,
                per_page: rowsPerPage
            };

            const response = await window.api.get('/transactions', params);

            if (response && response.success) {
                const data = response.data || response;
                activeTransactionsData = data.transactions || [];
                activeSummaryData = data.summary || response.summary || {};
                const total = data.total !== undefined ? data.total : (response.total || 0);
                const pages = data.pages !== undefined ? data.pages : (response.pages || 1);

                renderTable(activeTransactionsData, total, pages);
                updateStats(activeSummaryData);
            } else {
                if (tbody) {
                    tbody.innerHTML = `<tr><td colspan="9" class="py-12 text-center text-rose-500 font-bold text-xs uppercase tracking-widest">${(response && response.message) || 'No transactions returned'}</td></tr>`;
                }
            }
        } catch (error) {
            console.error('Failed to load transactions:', error);
            if (tbody) {
                tbody.innerHTML = `<tr><td colspan="9" class="py-12 text-center text-rose-500 font-bold text-xs uppercase tracking-widest">Network or server error loading transactions</td></tr>`;
            }
        }
    }

    /**
     * Render Skeleton Loading rows
     */
    function renderSkeletonTable() {
        const tbody = document.getElementById('txn-table-body');
        if (!tbody) return;

        let skeletonHtml = '';
        for (let i = 0; i < 5; i++) {
            skeletonHtml += `
            <tr class="animate-pulse">
                <td class="py-4 px-4 text-center"><div class="h-3 w-4 bg-border-subtle rounded mx-auto"></div></td>
                <td class="py-4 px-4"><div class="h-3.5 w-24 bg-border-subtle rounded"></div></td>
                <td class="py-4 px-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-border-subtle shrink-0"></div>
                        <div class="space-y-1.5 w-full">
                            <div class="h-3 w-28 bg-border-subtle rounded"></div>
                            <div class="h-2 w-16 bg-border-subtle rounded"></div>
                        </div>
                    </div>
                </td>
                <td class="py-4 px-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-border-subtle shrink-0"></div>
                        <div class="space-y-1.5 w-full">
                            <div class="h-3 w-28 bg-border-subtle rounded"></div>
                            <div class="h-2 w-16 bg-border-subtle rounded"></div>
                        </div>
                    </div>
                </td>
                <td class="py-4 px-4"><div class="h-5 w-20 bg-border-subtle rounded-md"></div></td>
                <td class="py-4 px-4"><div class="h-4 w-20 bg-border-subtle rounded"></div></td>
                <td class="py-4 px-4"><div class="h-3 w-24 bg-border-subtle rounded"></div></td>
                <td class="py-4 px-4 text-center"><div class="h-5 w-16 bg-border-subtle rounded-md mx-auto"></div></td>
                <td class="py-4 px-6 text-right"><div class="h-7 w-20 bg-border-subtle rounded-lg ml-auto"></div></td>
            </tr>`;
        }
        tbody.innerHTML = skeletonHtml;
    }

    /**
     * Render transactions table rows
     */
    function renderTable(transactions, total, pages) {
        const tbody = document.getElementById('txn-table-body');
        if (!tbody) return;

        if (!transactions || !transactions.length) {
            tbody.innerHTML = `
            <tr>
                <td colspan="9" class="py-16 text-center">
                    <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                        <div class="w-14 h-14 rounded-2xl bg-surface-subtle border border-border-subtle flex items-center justify-center mb-3 text-text-secondary">
                            <span class="material-symbols-outlined text-3xl">receipt_long</span>
                        </div>
                        <div class="text-sm font-black text-text-primary uppercase tracking-wider mb-1">No Transactions Found</div>
                        <p class="text-xs text-text-secondary mb-4">No records match the current filter or search criteria.</p>
                        <button onclick="window.resetTransactionFilters()" class="btn btn-secondary px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-xl">
                            Reset All Filters
                        </button>
                    </div>
                </td>
            </tr>`;
            updatePagination(0, 0);
            return;
        }

        const sortVal = document.getElementById('filter-sort')?.value || 'newest';
        const isOldestSort = sortVal === 'oldest';

        tbody.innerHTML = transactions.map((r, index) => {
            const rowNumber = isOldestSort
                ? (((currentPage - 1) * rowsPerPage) + index + 1)
                : (total ? (total - (((currentPage - 1) * rowsPerPage) + index)) : (index + 1));
            const rowNumStr = String(rowNumber).padStart(2, '0');
            const typeClass = (r.type || 'transfer').toLowerCase();
            
            // Amount formatting
            const rawAmount = parseFloat(r.amount) || 0;
            const isNegative = typeClass === 'withdrawal';
            const isPositive = typeClass === 'deposit' || typeClass === 'interest_credit';
            
            let amountColorClass = 'text-text-primary';
            let amountPrefix = '';
            if (isNegative) {
                amountColorClass = 'text-rose-500';
                amountPrefix = '-';
            } else if (isPositive) {
                amountColorClass = 'text-emerald-500';
                amountPrefix = '+';
            }
            const amountFormatted = `${amountPrefix}₹${rawAmount.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

            // Type badge icon & label
            let typeBadgeHtml = '';
            switch (typeClass) {
                case 'deposit':
                    typeBadgeHtml = `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-500 border border-emerald-500/20"><span class="material-symbols-outlined text-[13px]">south_west</span> Deposit</span>`;
                    break;
                case 'withdrawal':
                    typeBadgeHtml = `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-rose-500/10 text-rose-500 border border-rose-500/20"><span class="material-symbols-outlined text-[13px]">north_east</span> Withdrawal</span>`;
                    break;
                case 'transfer':
                    typeBadgeHtml = `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-sky-500/10 text-sky-500 border border-sky-500/20"><span class="material-symbols-outlined text-[13px]">sync_alt</span> Transfer</span>`;
                    break;
                case 'interest_credit':
                    typeBadgeHtml = `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-amber-500/10 text-amber-500 border border-amber-500/20"><span class="material-symbols-outlined text-[13px]">percent</span> Interest</span>`;
                    break;
                default:
                    typeBadgeHtml = `<span class="badge badge-info">${typeClass.toUpperCase()}</span>`;
            }

            // Status Badge
            let statusBadgeHtml = '';
            const status = (r.status || 'pending').toLowerCase();
            switch (status) {
                case 'completed':
                    statusBadgeHtml = `<span class="badge badge-success !text-[10px]">COMPLETED</span>`;
                    break;
                case 'pending':
                    statusBadgeHtml = `<span class="badge badge-warning !text-[10px]">PENDING</span>`;
                    break;
                case 'failed':
                    statusBadgeHtml = `<span class="badge badge-error !text-[10px]">FAILED</span>`;
                    break;
                default:
                    statusBadgeHtml = `<span class="badge badge-neutral !text-[10px]">${status.toUpperCase()}</span>`;
            }

            // -------------------------------------------------------------
            // 1. FROM Column (Sender: Staff if Deposit/Withdrawal, Customer if Transfer)
            // -------------------------------------------------------------
            let fromAccountHtml = '';

            if (typeClass === 'deposit' || typeClass === 'withdrawal') {
                // In Finzo, all deposits and withdrawals are processed by Branch Staff
                const staffName = r.processed_by_name || (r.processed_by ? `Staff ${r.processed_by}` : 'Branch Staff');
                const staffCode = r.processed_by || 'STF';
                const staffInitials = staffName.split(' ').filter(w => /^[a-zA-Z]/.test(w)).map(w => w[0]).join('').toUpperCase().substring(0, 2) || 'ST';
                const branchText = r.branch_name ? ` • ${r.branch_name}` : '';

                fromAccountHtml = `
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-black text-xs flex items-center justify-center border border-emerald-500/20 shrink-0 uppercase" title="Processed by Staff: ${staffName}">
                        ${staffInitials}
                    </div>
                    <div class="overflow-hidden">
                        <div class="font-bold text-xs text-text-primary truncate" title="${staffName}">${staffName}</div>
                        <div class="text-[11px] font-mono font-semibold text-text-secondary flex items-center gap-1">
                            <span>${staffCode}${branchText}</span>
                        </div>
                    </div>
                </div>`;
            } else if (typeClass === 'interest_credit') {
                fromAccountHtml = `
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-amber-500/10 text-amber-500 flex items-center justify-center border border-amber-500/20 shrink-0">
                        <span class="material-symbols-outlined text-sm">account_balance</span>
                    </div>
                    <div class="overflow-hidden">
                        <div class="font-bold text-xs text-text-primary truncate">Finzo Treasury</div>
                        <div class="text-[11px] text-amber-500 font-semibold truncate">Interest Pool</div>
                    </div>
                </div>`;
            } else {
                // Customer-to-Customer Transfer Sender
                const senderName = r.from_user_name && r.from_user_name !== 'N/A' ? r.from_user_name : 'Sender Customer';
                const senderInitials = senderName.split(' ').filter(w => /^[a-zA-Z]/.test(w)).map(w => w[0]).join('').toUpperCase().substring(0, 2) || 'U';
                let avatarContent = senderInitials;
                if (r.from_user_avatar) {
                    const src = (r.from_user_avatar.startsWith('http') ? r.from_user_avatar : (r.from_user_avatar.startsWith('/') ? r.from_user_avatar : `/Finzo/${r.from_user_avatar}`)) + '?v=' + new Date().getTime();
                    avatarContent = `<img src="${src}" alt="${senderName}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;" onerror="this.parentElement.textContent='${senderInitials}'">`;
                }
                
                fromAccountHtml = `
                <div class="flex items-center gap-3">
                    <div class="admin-avatar sm uppercase shrink-0">${avatarContent}</div>
                    <div class="overflow-hidden">
                        <div class="font-bold text-xs text-text-primary truncate" title="${senderName}">${senderName}</div>
                        <div class="text-[11px] font-mono font-semibold text-text-secondary flex items-center gap-1">
                            <span>${r.from_account_no}</span>
                            <button onclick="event.stopPropagation(); window.copyText('${r.from_account_no}')" class="opacity-40 hover:opacity-100 transition-opacity" title="Copy Account No">
                                <span class="material-symbols-outlined text-[12px]">content_copy</span>
                            </button>
                        </div>
                    </div>
                </div>`;
            }

            // -------------------------------------------------------------
            // 2. TO Column (Receiver: Customer Account)
            // -------------------------------------------------------------
            let toAccountHtml = '';

            if (typeClass === 'withdrawal') {
                // For withdrawal, the customer account being debited
                const custName = (r.from_user_name && r.from_user_name !== 'N/A') ? r.from_user_name : (r.to_user_name !== 'N/A' ? r.to_user_name : 'Customer');
                const custAcc = (r.from_account_no && r.from_account_no !== 'N/A') ? r.from_account_no : (r.to_account_no !== 'N/A' ? r.to_account_no : 'N/A');
                const custInitials = custName.split(' ').filter(w => /^[a-zA-Z]/.test(w)).map(w => w[0]).join('').toUpperCase().substring(0, 2) || 'U';
                let avatarContent = custInitials;
                if (r.from_user_avatar || r.to_user_avatar) {
                    const avatarUrl = r.from_user_avatar || r.to_user_avatar;
                    const src = (avatarUrl.startsWith('http') ? avatarUrl : (avatarUrl.startsWith('/') ? avatarUrl : `/Finzo/${avatarUrl}`)) + '?v=' + new Date().getTime();
                    avatarContent = `<img src="${src}" alt="${custName}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;" onerror="this.parentElement.textContent='${custInitials}'">`;
                }

                toAccountHtml = `
                <div class="flex items-center gap-3">
                    <div class="admin-avatar sm uppercase shrink-0">${avatarContent}</div>
                    <div class="overflow-hidden">
                        <div class="font-bold text-xs text-text-primary truncate" title="${custName}">${custName}</div>
                        <div class="text-[11px] font-mono font-semibold text-text-secondary flex items-center gap-1">
                            <span>${custAcc}</span>
                            <button onclick="event.stopPropagation(); window.copyText('${custAcc}')" class="opacity-40 hover:opacity-100 transition-opacity" title="Copy Account No">
                                <span class="material-symbols-outlined text-[12px]">content_copy</span>
                            </button>
                        </div>
                    </div>
                </div>`;
            } else {
                // Deposit, Interest, and Transfer destination customer account
                const receiverName = r.to_user_name && r.to_user_name !== 'N/A' ? r.to_user_name : (r.from_user_name !== 'N/A' ? r.from_user_name : 'Customer');
                const receiverAcc = r.to_account_no && r.to_account_no !== 'N/A' ? r.to_account_no : (r.from_account_no !== 'N/A' ? r.from_account_no : 'N/A');
                const receiverInitials = receiverName.split(' ').filter(w => /^[a-zA-Z]/.test(w)).map(w => w[0]).join('').toUpperCase().substring(0, 2) || 'U';
                let receiverAvatarContent = receiverInitials;
                if (r.to_user_avatar) {
                    const src = (r.to_user_avatar.startsWith('http') ? r.to_user_avatar : (r.to_user_avatar.startsWith('/') ? r.to_user_avatar : `/Finzo/${r.to_user_avatar}`)) + '?v=' + new Date().getTime();
                    receiverAvatarContent = `<img src="${src}" alt="${receiverName}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;" onerror="this.parentElement.textContent='${receiverInitials}'">`;
                }
                
                toAccountHtml = `
                <div class="flex items-center gap-3">
                    <div class="admin-avatar sm uppercase shrink-0">${receiverAvatarContent}</div>
                    <div class="overflow-hidden">
                        <div class="font-bold text-xs text-text-primary truncate" title="${receiverName}">${receiverName}</div>
                        <div class="text-[11px] font-mono font-semibold text-text-secondary flex items-center gap-1">
                            <span>${receiverAcc}</span>
                            <button onclick="event.stopPropagation(); window.copyText('${receiverAcc}')" class="opacity-40 hover:opacity-100 transition-opacity" title="Copy Account No">
                                <span class="material-symbols-outlined text-[12px]">content_copy</span>
                            </button>
                        </div>
                    </div>
                </div>`;
            }

            const formattedDate = formatDateTime(r.created_at);

            return `
            <tr class="hover:bg-surface-hover/60 transition-colors group cursor-pointer" onclick="window.openTransactionDetails('${r.txn_id}')">
                <td class="py-3.5 px-4 font-bold text-text-secondary text-[11px] text-center">${rowNumStr}</td>
                <td class="py-3.5 px-4">
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs font-mono font-bold text-primary">${r.txn_id}</span>
                        <button onclick="event.stopPropagation(); window.copyText('${r.txn_id}')" class="opacity-0 group-hover:opacity-100 text-text-secondary hover:text-text-primary transition-all p-0.5" title="Copy ID">
                            <span class="material-symbols-outlined text-[13px]">content_copy</span>
                        </button>
                    </div>
                </td>
                <td class="py-3.5 px-4">${fromAccountHtml}</td>
                <td class="py-3.5 px-4">${toAccountHtml}</td>
                <td class="py-3.5 px-4">${typeBadgeHtml}</td>
                <td class="py-3.5 px-4 text-sm font-black ${amountColorClass}">${amountFormatted}</td>
                <td class="py-3.5 px-4 text-xs font-semibold text-text-secondary whitespace-nowrap">${formattedDate}</td>
                <td class="py-3.5 px-4 text-center">${statusBadgeHtml}</td>
                <td class="py-3.5 px-6 text-right" onclick="event.stopPropagation()">
                    <div class="flex items-center justify-end gap-1.5">
                        <button onclick="window.openTransactionDetails('${r.txn_id}')" class="btn btn-secondary !p-1.5 rounded-lg text-text-secondary hover:text-primary hover:bg-surface-hover" title="View Details">
                            <span class="material-symbols-outlined text-[16px]">visibility</span>
                        </button>
                        <button onclick="window.openReceipt('${r.txn_id}')" class="btn btn-secondary !p-1.5 rounded-lg text-text-secondary hover:text-primary hover:bg-surface-hover" title="Official Receipt PDF">
                            <span class="material-symbols-outlined text-[16px]">receipt_long</span>
                        </button>
                    </div>
                </td>
            </tr>`;
        }).join('');

        updatePagination(total, pages);
    }

    /**
     * Update pagination controls
     */
    function updatePagination(total, pages) {
        const info = document.getElementById('pagination-info');
        if (info) {
            const start = total > 0 ? (currentPage - 1) * rowsPerPage + 1 : 0;
            const end = Math.min(currentPage * rowsPerPage, total);
            info.textContent = `Showing ${start}-${end} of ${total.toLocaleString()} Records`;
        }

        const prevBtn = document.getElementById('prev-btn');
        const nextBtn = document.getElementById('next-btn');
        const pagNumbers = document.getElementById('page-numbers');

        if (prevBtn) {
            prevBtn.disabled = (currentPage === 1);
        }
        if (nextBtn) {
            nextBtn.disabled = (currentPage === pages || pages === 0);
        }

        if (pagNumbers) {
            let html = '';
            for (let i = 1; i <= pages; i++) {
                if (pages > 6 && i > 3 && i < pages - 1) {
                    if (i === 4) html += '<span class="px-2 text-text-secondary text-xs">...</span>';
                    continue;
                }
                const activeClass = i === currentPage 
                    ? 'bg-primary text-text-inverse font-black shadow-sm' 
                    : 'bg-surface border border-border-subtle text-text-primary hover:bg-surface-hover hover:border-border-strong cursor-pointer';
                html += `<span data-page="${i}" class="w-8 h-8 flex items-center justify-center ${activeClass} rounded-lg text-xs font-bold transition-all">${i}</span>`;
            }
            pagNumbers.innerHTML = html;
        }
    }

    /**
     * Update all 5 stat cards dynamically
     */
    function updateStats(summary) {
        const totalVolumeEl = document.getElementById('stat-total-volume');
        const totalCountEl = document.getElementById('stat-total-count');
        const totalDepositsEl = document.getElementById('stat-total-deposits');
        const totalWithdrawalsEl = document.getElementById('stat-total-withdrawals');
        const totalTransfersEl = document.getElementById('stat-total-transfers');

        if (totalVolumeEl) {
            totalVolumeEl.textContent = formatCompactCurrency(summary.total_amount || 0);
            totalVolumeEl.classList.remove('skeleton');
        }
        if (totalCountEl) {
            totalCountEl.textContent = (summary.total_count || 0).toLocaleString();
            totalCountEl.classList.remove('skeleton');
        }
        if (totalDepositsEl) {
            totalDepositsEl.textContent = formatCompactCurrency(summary.total_deposits || 0);
            totalDepositsEl.classList.remove('skeleton');
        }
        if (totalWithdrawalsEl) {
            totalWithdrawalsEl.textContent = formatCompactCurrency(summary.total_withdrawals || 0);
            totalWithdrawalsEl.classList.remove('skeleton');
        }
        if (totalTransfersEl) {
            const combinedInternal = (parseFloat(summary.total_transfers) || 0) + (parseFloat(summary.total_interest) || 0);
            totalTransfersEl.textContent = formatCompactCurrency(combinedInternal);
            totalTransfersEl.classList.remove('skeleton');
        }
    }

    /**
     * Open Transaction Details Modal
     */
    window.openTransactionDetails = function(txnId) {
        const txn = activeTransactionsData.find(t => t.txn_id === txnId);
        if (!txn) return;

        isModalOpen = true;
        const modal = document.getElementById('txn-details-modal');
        if (!modal) return;

        // Set Header Data
        document.getElementById('modal-txn-id').textContent = txn.txn_id;
        const amountNum = parseFloat(txn.amount) || 0;
        document.getElementById('modal-amount').textContent = `₹${amountNum.toLocaleString('en-IN', { minimumFractionDigits: 2 })}`;
        document.getElementById('modal-date').textContent = `Recorded on ${formatDateTime(txn.created_at)}`;

        // Badges
        const typeClass = (txn.type || '').toLowerCase();
        let typeBadge = `<span class="badge badge-info uppercase">${txn.type}</span>`;
        if (typeClass === 'deposit') typeBadge = `<span class="badge badge-success">DEPOSIT</span>`;
        if (typeClass === 'withdrawal') typeBadge = `<span class="badge badge-error">WITHDRAWAL</span>`;
        if (typeClass === 'transfer') typeBadge = `<span class="badge badge-info">TRANSFER</span>`;
        if (typeClass === 'interest_credit') typeBadge = `<span class="badge badge-warning">INTEREST CREDIT</span>`;
        document.getElementById('modal-type-badge').innerHTML = typeBadge;

        const status = (txn.status || '').toLowerCase();
        let statusBadge = `<span class="badge badge-success uppercase">${status}</span>`;
        if (status === 'pending') statusBadge = `<span class="badge badge-warning">PENDING</span>`;
        if (status === 'failed') statusBadge = `<span class="badge badge-error">FAILED</span>`;
        document.getElementById('modal-status-badge').innerHTML = statusBadge;

        // Transfer / Flow Direction Banner
        const flowBanner = document.getElementById('modal-flow-banner');
        if (flowBanner) {
            if (typeClass === 'transfer') {
                const fromAcc = txn.from_account_no && txn.from_account_no !== 'N/A' ? txn.from_account_no : 'Sender Account';
                const toAcc = txn.to_account_no && txn.to_account_no !== 'N/A' ? txn.to_account_no : 'Receiver Account';
                flowBanner.classList.remove('hidden');
                flowBanner.innerHTML = `
                    <div class="flex items-center justify-between gap-2 text-xs font-bold text-sky-600 dark:text-sky-400 bg-sky-500/10 border border-sky-500/20 px-3.5 py-2.5 rounded-xl">
                        <div class="flex items-center gap-1.5 truncate">
                            <span class="text-[10px] font-black uppercase tracking-wider text-text-secondary">From:</span>
                            <span class="font-mono text-text-primary">${fromAcc}</span>
                        </div>
                        <span class="material-symbols-outlined text-base shrink-0">arrow_forward</span>
                        <div class="flex items-center gap-1.5 truncate">
                            <span class="text-[10px] font-black uppercase tracking-wider text-text-secondary">To:</span>
                            <span class="font-mono text-text-primary">${toAcc}</span>
                        </div>
                    </div>`;
            } else {
                flowBanner.classList.add('hidden');
                flowBanner.innerHTML = '';
            }
        }

        // Element references
        const fromTitleEl = document.getElementById('modal-from-title');
        const fromIconEl = document.getElementById('modal-from-icon');
        const fromAvatarEl = document.getElementById('modal-from-avatar');
        const toTitleEl = document.getElementById('modal-to-title');
        const toIconEl = document.getElementById('modal-to-icon');
        const toAvatarEl = document.getElementById('modal-to-avatar');

        // -------------------------------------------------------------
        // Modal Left Card (Source / Staff / Sender)
        // -------------------------------------------------------------
        if (typeClass === 'deposit' || typeClass === 'withdrawal') {
            // Processed by Staff
            if (fromTitleEl) fromTitleEl.textContent = `Processing Staff (${typeClass === 'deposit' ? 'Deposit' : 'Payout'})`;
            if (fromIconEl) fromIconEl.textContent = 'badge';

            const staffName = txn.processed_by_name || (txn.processed_by ? `Staff ${txn.processed_by}` : 'Branch Staff');
            const staffCode = txn.processed_by || 'STF';
            const staffInitials = staffName.split(' ').filter(w => /^[a-zA-Z]/.test(w)).map(w => w[0]).join('').toUpperCase().substring(0, 2) || 'ST';
            const branch = txn.branch_name ? `${txn.branch_name} Branch` : 'Branch Counter';

            document.getElementById('modal-from-name').textContent = staffName;
            document.getElementById('modal-from-acc').textContent = `Staff ID: ${staffCode}`;
            document.getElementById('modal-from-email').textContent = branch;
            if (fromAvatarEl) fromAvatarEl.textContent = staffInitials;
        } else if (typeClass === 'interest_credit') {
            if (fromTitleEl) fromTitleEl.textContent = 'Finzo Treasury Source';
            if (fromIconEl) fromIconEl.textContent = 'account_balance';

            document.getElementById('modal-from-name').textContent = 'Finzo Core Treasury';
            document.getElementById('modal-from-acc').textContent = 'Automated Interest Pool';
            document.getElementById('modal-from-email').textContent = 'System Interest Calculation';
            if (fromAvatarEl) fromAvatarEl.innerHTML = '<span class="material-symbols-outlined text-amber-500">account_balance</span>';
        } else {
            // Customer-to-Customer Transfer Sender
            if (fromTitleEl) fromTitleEl.textContent = 'Sender Customer Account';
            if (fromIconEl) fromIconEl.textContent = 'person';

            const senderName = txn.from_user_name && txn.from_user_name !== 'N/A' ? txn.from_user_name : 'Sender Customer';
            document.getElementById('modal-from-name').textContent = senderName;
            document.getElementById('modal-from-acc').textContent = txn.from_account_no || 'N/A';
            document.getElementById('modal-from-email').textContent = txn.from_user_email || (txn.from_customer_id ? `ID: ${txn.from_customer_id}` : 'Sender Account');
            if (fromAvatarEl) {
                const initials = senderName.split(' ').filter(w => /^[a-zA-Z]/.test(w)).map(w => w[0]).join('').toUpperCase().substring(0, 2) || 'U';
                if (txn.from_user_avatar) {
                    const src = txn.from_user_avatar.startsWith('http') ? txn.from_user_avatar : (txn.from_user_avatar.startsWith('/') ? txn.from_user_avatar : `/Finzo/${txn.from_user_avatar}`);
                    fromAvatarEl.innerHTML = `<img src="${src}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;" onerror="this.parentElement.textContent='${initials}'">`;
                } else {
                    fromAvatarEl.textContent = initials;
                }
            }
        }

        // -------------------------------------------------------------
        // Modal Right Card (Beneficiary / Debited Customer Account)
        // -------------------------------------------------------------
        if (typeClass === 'withdrawal') {
            if (toTitleEl) toTitleEl.textContent = 'Debited Customer Account';
            if (toIconEl) toIconEl.textContent = 'person';

            const custName = (txn.from_user_name && txn.from_user_name !== 'N/A') ? txn.from_user_name : (txn.to_user_name !== 'N/A' ? txn.to_user_name : 'Customer');
            const custAcc = (txn.from_account_no && txn.from_account_no !== 'N/A') ? txn.from_account_no : (txn.to_account_no !== 'N/A' ? txn.to_account_no : 'N/A');
            const custEmail = txn.from_user_email || txn.to_user_email || (txn.from_customer_id ? `ID: ${txn.from_customer_id}` : 'Debited Account');

            document.getElementById('modal-to-name').textContent = custName;
            document.getElementById('modal-to-acc').textContent = custAcc;
            document.getElementById('modal-to-email').textContent = custEmail;
            if (toAvatarEl) {
                const initials = custName.split(' ').filter(w => /^[a-zA-Z]/.test(w)).map(w => w[0]).join('').toUpperCase().substring(0, 2) || 'U';
                const avatarUrl = txn.from_user_avatar || txn.to_user_avatar;
                if (avatarUrl) {
                    const src = avatarUrl.startsWith('http') ? avatarUrl : (avatarUrl.startsWith('/') ? avatarUrl : `/Finzo/${avatarUrl}`);
                    toAvatarEl.innerHTML = `<img src="${src}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;" onerror="this.parentElement.textContent='${initials}'">`;
                } else {
                    toAvatarEl.textContent = initials;
                }
            }
        } else {
            // Deposit, Interest, and Transfer Beneficiary Account
            if (toTitleEl) toTitleEl.textContent = 'Beneficiary Customer Account';
            if (toIconEl) toIconEl.textContent = 'person';

            const receiverName = txn.to_user_name && txn.to_user_name !== 'N/A' ? txn.to_user_name : (txn.from_user_name !== 'N/A' ? txn.from_user_name : 'Customer');
            const receiverAcc = txn.to_account_no && txn.to_account_no !== 'N/A' ? txn.to_account_no : (txn.from_account_no !== 'N/A' ? txn.from_account_no : 'N/A');
            const receiverEmail = txn.to_user_email || txn.from_user_email || (txn.to_customer_id ? `ID: ${txn.to_customer_id}` : 'Credited Account');

            document.getElementById('modal-to-name').textContent = receiverName;
            document.getElementById('modal-to-acc').textContent = receiverAcc;
            document.getElementById('modal-to-email').textContent = receiverEmail;
            if (toAvatarEl) {
                const initials = receiverName.split(' ').filter(w => /^[a-zA-Z]/.test(w)).map(w => w[0]).join('').toUpperCase().substring(0, 2) || 'U';
                if (txn.to_user_avatar) {
                    const src = txn.to_user_avatar.startsWith('http') ? txn.to_user_avatar : (txn.to_user_avatar.startsWith('/') ? txn.to_user_avatar : `/Finzo/${txn.to_user_avatar}`);
                    toAvatarEl.innerHTML = `<img src="${src}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;" onerror="this.parentElement.textContent='${initials}'">`;
                } else {
                    toAvatarEl.textContent = initials;
                }
            }
        }

        // Description & Reference Notes (Single clean card)
        const refContainer = document.getElementById('modal-reference-container');
        const descEl = document.getElementById('modal-description');
        const refEl = document.getElementById('modal-reference');
        if (refContainer) {
            if (txn.description || txn.reference_note) {
                refContainer.classList.remove('hidden');
                if (descEl) descEl.textContent = txn.description || 'Standard transaction processing';
                if (refEl) refEl.textContent = txn.reference_note ? `Note: ${txn.reference_note}` : '';
            } else {
                refContainer.classList.add('hidden');
            }
        }

        // Actions
        const receiptBtn = document.getElementById('modal-receipt-btn');
        if (receiptBtn) {
            receiptBtn.onclick = () => window.openReceipt(txn.txn_id);
        }

        // Show Modal
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.querySelector('.ds-card').classList.remove('scale-95');
            modal.querySelector('.ds-card').classList.add('scale-100');
        }, 10);
    };

    /**
     * Close Transaction Details Modal
     */
    window.closeTransactionDetails = function() {
        const modal = document.getElementById('txn-details-modal');
        if (!modal) return;

        modal.classList.add('opacity-0');
        modal.querySelector('.ds-card').classList.remove('scale-100');
        modal.querySelector('.ds-card').classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
            isModalOpen = false;
        }, 200);
    };

    /**
     * Open official Receipt PDF
     */
    window.openReceipt = function(txnId) {
        window.open(`/Finzo/api/v1/documents/receipt?id=${txnId}`, '_blank');
    };

    /**
     * Copy text with toast feedback
     */
    window.copyText = function(text) {
        if (!text || text === 'N/A') return;
        navigator.clipboard.writeText(text).then(() => {
            if (window.showToast) window.showToast(`Copied: ${text}`, 'success');
        }).catch(() => {
            if (window.showToast) window.showToast('Failed to copy', 'error');
        });
    };

    window.copyTxnId = function() {
        const txnId = document.getElementById('modal-txn-id')?.textContent;
        if (txnId) window.copyText(txnId);
    };

    /**
     * Reset All Filters
     */
    window.resetTransactionFilters = function() {
        currentType = 'all';
        currentPage = 1;

        // Reset Type Pills UI
        document.querySelectorAll('.type-pill').forEach(btn => {
            if (btn.getAttribute('data-type') === 'all') {
                btn.className = 'type-pill px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-200 bg-primary text-text-inverse shadow-md shadow-primary/20';
            } else {
                btn.className = 'type-pill px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-200 bg-surface border border-border-subtle text-text-secondary hover:text-text-primary hover:bg-surface-hover';
            }
        });

        // Clear Search
        const searchInput = document.getElementById('transaction-search');
        if (searchInput) searchInput.value = '';
        const clearBtn = document.getElementById('clear-search-btn');
        if (clearBtn) clearBtn.classList.add('hidden');

        // Clear Dates
        const dateFrom = document.getElementById('filter-date-from');
        if (dateFrom) dateFrom.value = '';
        const dateTo = document.getElementById('filter-date-to');
        if (dateTo) dateTo.value = '';

        // Reset Status Dropdown
        const statusInput = document.getElementById('filter-status');
        if (statusInput) statusInput.value = 'all';
        const statusDropdown = document.getElementById('status-dropdown');
        if (statusDropdown) {
            const label = statusDropdown.querySelector('.dropdown-label');
            if (label) label.textContent = 'All Status';
            statusDropdown.querySelectorAll('.finzo-dropdown-option').forEach(opt => {
                opt.classList.toggle('is-selected', opt.getAttribute('data-value') === 'all');
            });
        }

        // Reset Sort Dropdown
        const sortInput = document.getElementById('filter-sort');
        if (sortInput) sortInput.value = 'newest';
        const sortDropdown = document.getElementById('sort-dropdown');
        if (sortDropdown) {
            const label = sortDropdown.querySelector('.dropdown-label');
            if (label) label.textContent = 'Newest First';
            sortDropdown.querySelectorAll('.finzo-dropdown-option').forEach(opt => {
                opt.classList.toggle('is-selected', opt.getAttribute('data-value') === 'newest');
            });
        }

        loadTransactions();
    };

    /**
     * Bind all DOM event listeners
     */
    function bindEvents() {
        // Type Pills
        const typePills = document.querySelectorAll('.type-pill');
        typePills.forEach(pill => {
            pill.addEventListener('click', function() {
                typePills.forEach(p => {
                    p.className = 'type-pill px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-200 bg-surface border border-border-subtle text-text-secondary hover:text-text-primary hover:bg-surface-hover';
                });
                this.className = 'type-pill px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-200 bg-primary text-text-inverse shadow-md shadow-primary/20';

                currentType = this.getAttribute('data-type');
                currentPage = 1;
                loadTransactions();
            });
        });

        // Search Input (Debounced)
        const searchInput = document.getElementById('transaction-search');
        const clearSearchBtn = document.getElementById('clear-search-btn');
        if (searchInput) {
            let debounceTimer;
            searchInput.addEventListener('input', function() {
                if (clearSearchBtn) {
                    clearSearchBtn.classList.toggle('hidden', !this.value);
                }
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    currentPage = 1;
                    loadTransactions();
                }, 300);
            });
        }
        if (clearSearchBtn && searchInput) {
            clearSearchBtn.addEventListener('click', () => {
                searchInput.value = '';
                clearSearchBtn.classList.add('hidden');
                currentPage = 1;
                loadTransactions();
            });
        }

        // Date Pickers
        const dateFrom = document.getElementById('filter-date-from');
        if (dateFrom) dateFrom.addEventListener('change', () => { currentPage = 1; loadTransactions(); });

        const dateTo = document.getElementById('filter-date-to');
        if (dateTo) dateTo.addEventListener('change', () => { currentPage = 1; loadTransactions(); });

        // Status Dropdown
        const statusInput = document.getElementById('filter-status');
        if (statusInput) {
            statusInput.addEventListener('change', () => {
                currentPage = 1;
                loadTransactions();
            });
        }
        const statusDropdown = document.getElementById('status-dropdown');
        if (statusDropdown) {
            statusDropdown.addEventListener('click', (e) => {
                const opt = e.target.closest('.finzo-dropdown-option');
                if (opt) {
                    setTimeout(() => { currentPage = 1; loadTransactions(); }, 50);
                }
            });
        }

        // Sort Dropdown
        const sortInput = document.getElementById('filter-sort');
        if (sortInput) {
            sortInput.addEventListener('change', () => {
                currentPage = 1;
                loadTransactions();
            });
        }
        const sortDropdown = document.getElementById('sort-dropdown');
        if (sortDropdown) {
            sortDropdown.addEventListener('click', (e) => {
                const opt = e.target.closest('.finzo-dropdown-option');
                if (opt) {
                    setTimeout(() => { currentPage = 1; loadTransactions(); }, 50);
                }
            });
        }

        // Reset Filters Button
        const resetBtn = document.getElementById('reset-filters-btn');
        if (resetBtn) resetBtn.addEventListener('click', window.resetTransactionFilters);

        // Pagination Clicks
        document.addEventListener('click', (e) => {
            const pageSpan = e.target.closest('#page-numbers span[data-page]');
            if (pageSpan) {
                currentPage = parseInt(pageSpan.getAttribute('data-page'));
                loadTransactions();
            }
        });

        const prevBtn = document.getElementById('prev-btn');
        if (prevBtn) prevBtn.addEventListener('click', () => { if (currentPage > 1) { currentPage--; loadTransactions(); } });

        const nextBtn = document.getElementById('next-btn');
        if (nextBtn) nextBtn.addEventListener('click', () => { currentPage++; loadTransactions(); });

        // Details Modal Close buttons
        document.getElementById('close-details-modal')?.addEventListener('click', window.closeTransactionDetails);
        document.getElementById('close-details-btn')?.addEventListener('click', window.closeTransactionDetails);

        // Keyboard escape to close modals
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && isModalOpen) {
                window.closeTransactionDetails();
            }
        });
    }

    // Initialize function
    function init() {
        loadTransactions();
        bindEvents();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
