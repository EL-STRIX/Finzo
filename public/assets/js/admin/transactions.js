/**
 * Finzo Admin — Global Transactions Controller
 * Manages table rendering, filters, statistics, reversal workflows, and official statement/passbook printing.
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
    let pendingReversalTxnId = null;

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
     * Load transactions from backend API
     */
    async function loadTransactions() {
        const tbody = document.getElementById('txn-table-body');
        if (tbody) {
            renderSkeletonTable();
        }

        try {
            const searchInput = document.getElementById('transaction-search');
            const statusFilter = document.getElementById('filter-status');
            const dateFrom = document.getElementById('filter-date-from');
            const dateTo = document.getElementById('filter-date-to');
            const sortFilter = document.getElementById('filter-sort');

            const params = {
                type: currentType,
                status: statusFilter ? statusFilter.value : 'all',
                search: searchInput ? searchInput.value.trim() : '',
                from_date: dateFrom ? dateFrom.value : '',
                to_date: dateTo ? dateTo.value : '',
                sort: sortFilter ? sortFilter.value : 'newest',
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

        tbody.innerHTML = transactions.map((r, index) => {
            const rowNumber = (currentPage - 1) * rowsPerPage + index + 1;
            const rowNumStr = String(rowNumber).padStart(2, '0');
            const typeClass = (r.type || 'transfer').toLowerCase();
            
            // Amount formatting
            const rawAmount = parseFloat(r.amount) || 0;
            const isNegative = typeClass === 'withdrawal' || (typeClass === 'transfer' && r.from_user_name !== 'N/A');
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
                    typeBadgeHtml = `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-500 border border-emerald-500/20"><span class="material-symbols-outlined text-[13px] print:hidden">south_west</span> Deposit</span>`;
                    break;
                case 'withdrawal':
                    typeBadgeHtml = `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-rose-500/10 text-rose-500 border border-rose-500/20"><span class="material-symbols-outlined text-[13px] print:hidden">north_east</span> Withdrawal</span>`;
                    break;
                case 'transfer':
                    typeBadgeHtml = `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-sky-500/10 text-sky-500 border border-sky-500/20"><span class="material-symbols-outlined text-[13px] print:hidden">sync_alt</span> Transfer</span>`;
                    break;
                case 'interest_credit':
                    typeBadgeHtml = `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-amber-500/10 text-amber-500 border border-amber-500/20"><span class="material-symbols-outlined text-[13px] print:hidden">percent</span> Interest</span>`;
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
                case 'reversed':
                    statusBadgeHtml = `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-rose-500/10 text-rose-500 border border-rose-500/20">REVERSED</span>`;
                    break;
                default:
                    statusBadgeHtml = `<span class="badge badge-neutral !text-[10px]">${status.toUpperCase()}</span>`;
            }

            // Determine From Account (Source) HTML
            let fromAccountHtml = '';
            if (typeClass === 'deposit') {
                if (r.description && (r.description.includes('Initial Deposit') || r.description.includes('Account Opening'))) {
                    fromAccountHtml = `
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center border border-primary/20 shrink-0 print:hidden"><span class="material-symbols-outlined text-sm" aria-hidden="true">rocket_launch</span></div>
                        <div class="overflow-hidden">
                            <div class="font-bold text-xs text-text-primary truncate">Account Opening</div>
                            <div class="text-[10px] font-bold text-text-secondary uppercase tracking-wider">Initial Funding</div>
                        </div>
                    </div>`;
                } else if (r.processed_by) {
                    fromAccountHtml = `
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center border border-primary/20 shrink-0 print:hidden"><span class="material-symbols-outlined text-sm" aria-hidden="true">badge</span></div>
                        <div class="overflow-hidden">
                            <div class="font-bold text-xs text-text-primary truncate">OTC Branch Deposit</div>
                            <div class="text-[10px] font-bold text-text-secondary uppercase tracking-wider">Staff: ${r.processed_by}</div>
                        </div>
                    </div>`;
                } else {
                    fromAccountHtml = `
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center border border-primary/20 shrink-0 print:hidden"><span class="material-symbols-outlined text-sm" aria-hidden="true">account_balance_wallet</span></div>
                        <div class="overflow-hidden">
                            <div class="font-bold text-xs text-text-primary truncate">Direct Deposit</div>
                            <div class="text-[10px] font-bold text-text-secondary uppercase tracking-wider">Electronic Inflow</div>
                        </div>
                    </div>`;
                }
            } else if (typeClass === 'interest_credit') {
                fromAccountHtml = `
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-amber-500/10 text-amber-500 flex items-center justify-center border border-amber-500/20 shrink-0 print:hidden"><span class="material-symbols-outlined text-sm" aria-hidden="true">account_balance</span></div>
                    <div class="overflow-hidden">
                        <div class="font-bold text-xs text-text-primary truncate">Finzo Treasury</div>
                        <div class="text-[10px] font-bold text-text-secondary uppercase tracking-wider">Automated Interest</div>
                    </div>
                </div>`;
            } else {
                const senderName = r.from_user_name && r.from_user_name !== 'N/A' ? r.from_user_name : 'Unknown';
                const senderInitials = senderName.split(' ').filter(w => /^[a-zA-Z]/.test(w)).map(w => w[0]).join('').toUpperCase().substring(0, 2) || 'U';
                let avatarContent = senderInitials;
                if (r.from_user_avatar) {
                    const src = (r.from_user_avatar.startsWith('http') ? r.from_user_avatar : `/Finzo/public/assets/${r.from_user_avatar}`) + '?v=' + new Date().getTime();
                    avatarContent = `<img src="${src}" alt="${senderName}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">`;
                }
                
                fromAccountHtml = `
                <div class="flex items-center gap-3">
                    <div class="admin-avatar sm uppercase shrink-0 print:hidden">${avatarContent}</div>
                    <div class="overflow-hidden">
                        <div class="font-bold text-xs text-text-primary truncate" title="${senderName}">${senderName}</div>
                        <div class="text-[11px] font-mono font-semibold text-text-secondary flex items-center gap-1">
                            <span>${r.from_account_no}</span>
                            <button onclick="window.copyText('${r.from_account_no}')" class="opacity-40 hover:opacity-100 transition-opacity print:hidden" title="Copy Account No">
                                <span class="material-symbols-outlined text-[12px]">content_copy</span>
                            </button>
                        </div>
                    </div>
                </div>`;
            }

            // Determine To Account (Destination) HTML
            let toAccountHtml = '';
            if (typeClass === 'withdrawal') {
                if (r.processed_by) {
                    toAccountHtml = `
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-rose-500/10 text-rose-500 flex items-center justify-center border border-rose-500/20 shrink-0 print:hidden"><span class="material-symbols-outlined text-sm" aria-hidden="true">badge</span></div>
                        <div class="overflow-hidden">
                            <div class="font-bold text-xs text-text-primary truncate">OTC Cash Payout</div>
                            <div class="text-[10px] font-bold text-text-secondary uppercase tracking-wider">Staff: ${r.processed_by}</div>
                        </div>
                    </div>`;
                } else if (r.description && r.description.includes('Restore Charge')) {
                    toAccountHtml = `
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-rose-500/10 text-rose-500 flex items-center justify-center border border-rose-500/20 shrink-0 print:hidden"><span class="material-symbols-outlined text-sm" aria-hidden="true">settings_backup_restore</span></div>
                        <div class="overflow-hidden">
                            <div class="font-bold text-xs text-text-primary truncate">Admin Fee</div>
                            <div class="text-[10px] font-bold text-text-secondary uppercase tracking-wider">Account Charge</div>
                        </div>
                    </div>`;
                } else {
                    toAccountHtml = `
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-rose-500/10 text-rose-500 flex items-center justify-center border border-rose-500/20 shrink-0 print:hidden"><span class="material-symbols-outlined text-sm" aria-hidden="true">atm</span></div>
                        <div class="overflow-hidden">
                            <div class="font-bold text-xs text-text-primary truncate">ATM / Cash Outflow</div>
                            <div class="text-[10px] font-bold text-text-secondary uppercase tracking-wider">Direct Withdrawal</div>
                        </div>
                    </div>`;
                }
            } else {
                const receiverName = r.to_user_name && r.to_user_name !== 'N/A' ? r.to_user_name : 'Beneficiary';
                const receiverInitials = receiverName.split(' ').filter(w => /^[a-zA-Z]/.test(w)).map(w => w[0]).join('').toUpperCase().substring(0, 2) || 'U';
                let receiverAvatarContent = receiverInitials;
                if (r.to_user_avatar) {
                    const src = (r.to_user_avatar.startsWith('http') ? r.to_user_avatar : `/Finzo/public/assets/${r.to_user_avatar}`) + '?v=' + new Date().getTime();
                    receiverAvatarContent = `<img src="${src}" alt="${receiverName}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">`;
                }
                
                toAccountHtml = `
                <div class="flex items-center gap-3">
                    <div class="admin-avatar sm uppercase shrink-0 print:hidden">${receiverAvatarContent}</div>
                    <div class="overflow-hidden">
                        <div class="font-bold text-xs text-text-primary truncate" title="${receiverName}">${receiverName}</div>
                        <div class="text-[11px] font-mono font-semibold text-text-secondary flex items-center gap-1">
                            <span>${r.to_account_no}</span>
                            <button onclick="window.copyText('${r.to_account_no}')" class="opacity-40 hover:opacity-100 transition-opacity print:hidden" title="Copy Account No">
                                <span class="material-symbols-outlined text-[12px]">content_copy</span>
                            </button>
                        </div>
                    </div>
                </div>`;
            }

            return `
            <tr class="hover:bg-surface-hover/60 transition-colors group cursor-pointer" onclick="window.openTransactionDetails('${r.txn_id}')">
                <td class="py-3.5 px-4 font-bold text-text-secondary text-[11px] text-center">${rowNumStr}</td>
                <td class="py-3.5 px-4">
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs font-mono font-bold text-primary">${r.txn_id}</span>
                        <button onclick="event.stopPropagation(); window.copyText('${r.txn_id}')" class="opacity-0 group-hover:opacity-100 text-text-secondary hover:text-text-primary transition-all p-0.5 print:hidden" title="Copy ID">
                            <span class="material-symbols-outlined text-[13px]">content_copy</span>
                        </button>
                    </div>
                </td>
                <td class="py-3.5 px-4">${fromAccountHtml}</td>
                <td class="py-3.5 px-4">${toAccountHtml}</td>
                <td class="py-3.5 px-4">${typeBadgeHtml}</td>
                <td class="py-3.5 px-4 text-sm font-black ${amountColorClass}">${amountFormatted}</td>
                <td class="py-3.5 px-4 text-xs font-semibold text-text-secondary whitespace-nowrap">${r.created_at}</td>
                <td class="py-3.5 px-4 text-center">${statusBadgeHtml}</td>
                <td class="py-3.5 px-6 text-right print:hidden" onclick="event.stopPropagation()">
                    <div class="flex items-center justify-end gap-1.5">
                        <button onclick="window.openTransactionDetails('${r.txn_id}')" class="btn btn-secondary !p-1.5 rounded-lg text-text-secondary hover:text-primary hover:bg-surface-hover" title="View Breakdown">
                            <span class="material-symbols-outlined text-[16px]">visibility</span>
                        </button>
                        <button onclick="window.openReceipt('${r.txn_id}')" class="btn btn-secondary !p-1.5 rounded-lg text-text-secondary hover:text-primary hover:bg-surface-hover" title="Official Receipt PDF">
                            <span class="material-symbols-outlined text-[16px]">receipt_long</span>
                        </button>
                        ${status === 'completed' ? `
                        <button onclick="window.openReversalModal('${r.txn_id}')" class="btn btn-secondary !p-1.5 rounded-lg text-rose-500 hover:bg-rose-500 hover:text-white" title="Reverse Transaction">
                            <span class="material-symbols-outlined text-[16px]">undo</span>
                        </button>` : ''}
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

        // Update Print Header Summary Metrics
        const printCreditsEl = document.getElementById('print-credits');
        const printDebitsEl = document.getElementById('print-debits');
        const printVolumeEl = document.getElementById('print-volume');
        const printCountEl = document.getElementById('print-count');

        if (printCreditsEl) {
            printCreditsEl.textContent = `₹${(parseFloat(summary.total_deposits) || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 })}`;
        }
        if (printDebitsEl) {
            printDebitsEl.textContent = `₹${(parseFloat(summary.total_withdrawals) || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 })}`;
        }
        if (printVolumeEl) {
            printVolumeEl.textContent = `₹${(parseFloat(summary.total_amount) || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 })}`;
        }
        if (printCountEl) {
            printCountEl.textContent = (summary.total_count || 0).toLocaleString();
        }
    }

    /**
     * Print Ledger Statement / Passbook view (Identical UX to User & Staff Statement)
     */
    function printStatement() {
        const periodEl = document.getElementById('print-period');
        const dateFrom = document.getElementById('filter-date-from')?.value;
        const dateTo = document.getElementById('filter-date-to')?.value;
        
        let periodText = 'All Transactions';
        if (dateFrom && dateTo) {
            periodText = `${dateFrom} to ${dateTo}`;
        } else if (dateFrom) {
            periodText = `From ${dateFrom}`;
        } else if (dateTo) {
            periodText = `Up to ${dateTo}`;
        }

        if (currentType !== 'all') {
            periodText += ` (${currentType.toUpperCase()})`;
        }

        if (periodEl) periodEl.textContent = `Period: ${periodText}`;
        
        const genEl = document.getElementById('print-generated');
        if (genEl) genEl.textContent = new Date().toLocaleString('en-IN', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });

        // Trigger browser print
        window.print();
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

        // Set IDs and Values
        document.getElementById('modal-txn-id').textContent = txn.txn_id;
        const amountNum = parseFloat(txn.amount) || 0;
        document.getElementById('modal-amount').textContent = `₹${amountNum.toLocaleString('en-IN', { minimumFractionDigits: 2 })}`;
        document.getElementById('modal-date').textContent = `Recorded on ${txn.created_at}`;

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
        if (status === 'reversed') statusBadge = `<span class="badge badge-error">REVERSED</span>`;
        if (status === 'pending') statusBadge = `<span class="badge badge-warning">PENDING</span>`;
        document.getElementById('modal-status-badge').innerHTML = statusBadge;

        // Sender Info
        document.getElementById('modal-from-name').textContent = txn.from_user_name || 'N/A';
        document.getElementById('modal-from-acc').textContent = txn.from_account_no || 'N/A';
        document.getElementById('modal-from-email').textContent = txn.from_user_email || (txn.from_customer_id ? `ID: ${txn.from_customer_id}` : 'Direct Inflow');
        const fromAvatarEl = document.getElementById('modal-from-avatar');
        if (fromAvatarEl) {
            const initials = txn.from_user_name && txn.from_user_name !== 'N/A' ? txn.from_user_name.charAt(0).toUpperCase() : 'U';
            if (txn.from_user_avatar) {
                fromAvatarEl.innerHTML = `<img src="${txn.from_user_avatar.startsWith('http') ? txn.from_user_avatar : `/Finzo/public/assets/${txn.from_user_avatar}`}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">`;
            } else {
                fromAvatarEl.textContent = initials;
            }
        }

        // Beneficiary Info
        document.getElementById('modal-to-name').textContent = txn.to_user_name || 'N/A';
        document.getElementById('modal-to-acc').textContent = txn.to_account_no || 'N/A';
        document.getElementById('modal-to-email').textContent = txn.to_user_email || (txn.to_customer_id ? `ID: ${txn.to_customer_id}` : 'Direct Outflow');
        const toAvatarEl = document.getElementById('modal-to-avatar');
        if (toAvatarEl) {
            const initials = txn.to_user_name && txn.to_user_name !== 'N/A' ? txn.to_user_name.charAt(0).toUpperCase() : 'U';
            if (txn.to_user_avatar) {
                toAvatarEl.innerHTML = `<img src="${txn.to_user_avatar.startsWith('http') ? txn.to_user_avatar : `/Finzo/public/assets/${txn.to_user_avatar}`}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">`;
            } else {
                toAvatarEl.textContent = initials;
            }
        }

        // Channel & Processing
        let channel = 'Web / Online Banking';
        if (txn.processed_by) channel = `OTC Branch Desk (${txn.processed_by})`;
        else if (typeClass === 'interest_credit') channel = 'Automated Core Banking System';
        document.getElementById('modal-channel').textContent = channel;
        document.getElementById('modal-processed-by').textContent = txn.processed_by ? `Staff ID: ${txn.processed_by}` : 'System Engine';
        document.getElementById('modal-description').textContent = txn.description || 'Standard transaction processing';

        const refContainer = document.getElementById('modal-reference-container');
        if (refContainer) {
            if (txn.reference_note) {
                refContainer.classList.remove('hidden');
                document.getElementById('modal-reference').textContent = txn.reference_note;
            } else {
                refContainer.classList.add('hidden');
            }
        }

        // Actions
        const receiptBtn = document.getElementById('modal-receipt-btn');
        if (receiptBtn) {
            receiptBtn.onclick = () => window.openReceipt(txn.txn_id);
        }

        const reversalContainer = document.getElementById('modal-reversal-action');
        if (reversalContainer) {
            if (status === 'completed') {
                reversalContainer.innerHTML = `
                <button onclick="window.closeTransactionDetails(); window.openReversalModal('${txn.txn_id}')" class="btn btn-secondary text-rose-500 hover:bg-rose-500 hover:text-white px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-xl flex items-center gap-1.5 transition-all">
                    <span class="material-symbols-outlined text-[16px]">undo</span>
                    <span>Reverse Transaction</span>
                </button>`;
            } else {
                reversalContainer.innerHTML = '';
            }
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
     * Open Reversal Modal
     */
    window.openReversalModal = function(txnId) {
        pendingReversalTxnId = txnId;
        const txn = activeTransactionsData.find(t => t.txn_id === txnId);
        
        const modal = document.getElementById('txn-reversal-modal');
        if (!modal) return;

        isModalOpen = true;
        document.getElementById('reversal-txn-id').textContent = txnId;
        const amount = txn ? parseFloat(txn.amount) : 0;
        document.getElementById('reversal-txn-amount').textContent = `₹${amount.toLocaleString('en-IN', { minimumFractionDigits: 2 })}`;
        
        const reasonInput = document.getElementById('reversal-reason');
        if (reasonInput) reasonInput.value = '';
        const charCount = document.getElementById('reversal-char-count');
        if (charCount) charCount.textContent = '0 / 255';

        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.querySelector('.ds-card').classList.remove('scale-95');
            modal.querySelector('.ds-card').classList.add('scale-100');
            if (reasonInput) reasonInput.focus();
        }, 10);
    };

    /**
     * Close Reversal Modal
     */
    window.closeReversalModal = function() {
        const modal = document.getElementById('txn-reversal-modal');
        if (!modal) return;

        modal.classList.add('opacity-0');
        modal.querySelector('.ds-card').classList.remove('scale-100');
        modal.querySelector('.ds-card').classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
            pendingReversalTxnId = null;
            isModalOpen = false;
        }, 200);
    };

    /**
     * Submit Reversal Action
     */
    async function submitReversal() {
        if (!pendingReversalTxnId) return;

        const reasonInput = document.getElementById('reversal-reason');
        const reason = reasonInput ? reasonInput.value.trim() : '';

        if (!reason) {
            if (window.showToast) window.showToast('Please provide a mandatory reversal reason', 'error');
            if (reasonInput) reasonInput.focus();
            return;
        }

        const confirmBtn = document.getElementById('confirm-reversal-btn');
        if (window.api && window.api.setButtonLoading) {
            window.api.setButtonLoading(confirmBtn, true, 'Reversing...');
        }

        try {
            const response = await window.api.post(`/transactions/${pendingReversalTxnId}/reverse`, {
                transaction_id: pendingReversalTxnId,
                reason: reason
            });

            if (response && response.success) {
                if (window.showToast) window.showToast('Transaction successfully reversed and ledger updated.', 'success');
                window.closeReversalModal();
                loadTransactions();
            } else {
                if (window.showToast) window.showToast((response && response.message) || 'Failed to reverse transaction', 'error');
            }
        } catch (error) {
            console.error('Reversal error:', error);
            if (window.showToast) window.showToast('Server error processing reversal', 'error');
        } finally {
            if (window.api && window.api.setButtonLoading) {
                window.api.setButtonLoading(confirmBtn, false);
            }
        }
    }

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

        // Print Statement Button
        document.getElementById('print-statement-btn')?.addEventListener('click', printStatement);

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

        // Reversal Modal Buttons
        document.getElementById('close-reversal-modal')?.addEventListener('click', window.closeReversalModal);
        document.getElementById('cancel-reversal-btn')?.addEventListener('click', window.closeReversalModal);
        document.getElementById('confirm-reversal-btn')?.addEventListener('click', submitReversal);

        // Reversal textarea character counter
        const reasonInput = document.getElementById('reversal-reason');
        if (reasonInput) {
            reasonInput.addEventListener('input', function() {
                const count = document.getElementById('reversal-char-count');
                if (count) count.textContent = `${this.value.length} / 255`;
            });
        }

        // Keyboard escape to close modals
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && isModalOpen) {
                window.closeTransactionDetails();
                window.closeReversalModal();
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
