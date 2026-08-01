/**
 * Finzo Admin — Transactions Handler
 */

(function() {
    let currentType = 'all';
    let currentPage = 1;
    const rowsPerPage = 10;

    /**
     * Load transactions with current filters
     */
    async function loadTransactions() {
        const tbody = document.getElementById('txn-table-body');
        if (tbody) tbody.style.opacity = '0.5';

        try {
            const searchInput = document.getElementById('transaction-search');

            const response = await window.api.get('/transactions', {
                type: currentType,
                status: 'all',
                account_type: 'all',
                search: searchInput ? searchInput.value : '',
                date_from: '',
                date_to: '',
                page: currentPage,
                per_page: rowsPerPage
            });

            if (response.success) {
                renderTable(response.transactions, response.total, response.pages);
                updateStats(response.summary || {});
            }
        } catch (error) {
            console.error('Failed to load transactions:', error);
        } finally {
            if (tbody) tbody.style.opacity = '1';
        }
    }

    /**
     * Render transactions table
     */
    function renderTable(transactions, total, pages) {
        const tbody = document.getElementById('txn-table-body');
        if (!tbody) return;

        if (!transactions || !transactions.length) {
            tbody.innerHTML = '<tr><td colspan="9" class="py-20 text-center font-bold opacity-40">No transactions found.</td></tr>';
            return;
        }

        tbody.innerHTML = transactions.map((r, i) => {
            const countStr = String(r.id).padStart(2, '0');
            const typeClass = r.type.toLowerCase();
            
            // Format amount nicely with Rupee symbol
            const rawAmount = parseFloat(r.amount);
            const isNegative = ['withdrawal', 'transfer'].includes(typeClass) && r.from_user_name !== 'N/A';
            const amountClass = isNegative ? 'text-rose-500' : 'text-emerald-500';
            const prefix = isNegative ? '-' : '+';
            const amountFormatted = `${prefix}₹${rawAmount.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

            // Determine From Account (Source) HTML
            let fromAccountHtml = '';
            if (typeClass === 'deposit') {
                if (r.description && (r.description.includes('Initial Deposit') || r.description.includes('Account Opening'))) {
                    fromAccountHtml = `
                    <div class="flex items-center gap-3">
                        <div class="admin-avatar sm premium-gradient-primary uppercase" style="color:#fff;"><span class="material-symbols-outlined text-sm">rocket_launch</span></div>
                        <div>
                            <div class="font-bold text-slate-800 dark:text-slate-100">Opening Bal</div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">System Init</div>
                        </div>
                    </div>`;
                } else if (r.processed_by) {
                    fromAccountHtml = `
                    <div class="flex items-center gap-3">
                        <div class="admin-avatar sm premium-gradient-primary uppercase" style="color:#fff;"><span class="material-symbols-outlined text-sm">badge</span></div>
                        <div>
                            <div class="font-bold text-slate-800 dark:text-slate-100">OTC Cash</div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Staff: ${r.processed_by}</div>
                        </div>
                    </div>`;
                } else {
                    fromAccountHtml = `
                    <div class="flex items-center gap-3">
                        <div class="admin-avatar sm premium-gradient-primary uppercase" style="color:#fff;"><span class="material-symbols-outlined text-sm">account_balance_wallet</span></div>
                        <div>
                            <div class="font-bold text-slate-800 dark:text-slate-100">Direct Deposit</div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">System Direct</div>
                        </div>
                    </div>`;
                }
            } else if (typeClass === 'interest_credit') {
                fromAccountHtml = `
                <div class="flex items-center gap-3">
                    <div class="admin-avatar sm premium-gradient-primary uppercase" style="color:#fff;"><span class="material-symbols-outlined text-sm">percent</span></div>
                    <div>
                        <div class="font-bold text-slate-800 dark:text-slate-100">Interest Credit</div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">System Auto</div>
                    </div>
                </div>`;
            } else {
                // For withdrawals and transfers, From Account is the source customer
                const senderInitials = r.from_user_name && r.from_user_name !== 'N/A' ? r.from_user_name.charAt(0).toUpperCase() : 'U';
                let avatarContent = senderInitials;
                if (r.from_user_avatar) {
                    const src = (r.from_user_avatar.startsWith('http') ? r.from_user_avatar : `/Finzo/public/assets/${r.from_user_avatar}`) + '?v=' + new Date().getTime();
                    avatarContent = `<img src="${src}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">`;
                }
                
                fromAccountHtml = `
                <div class="flex items-center gap-3">
                    <div class="admin-avatar sm uppercase" style="color:#fff; ${r.from_user_avatar ? 'background:none;' : window.FinzoUtils.getAvatarGradient(senderInitials)}">${avatarContent}</div>
                    <div>
                        <div class="font-bold text-slate-800 dark:text-slate-100">${r.from_user_name}</div>
                        <div class="text-[11px] opacity-40 font-mono font-semibold">${r.from_account_no}</div>
                    </div>
                </div>`;
            }

            // Determine To Account (Destination) HTML
            let toAccountHtml = '';
            if (typeClass === 'withdrawal') {
                if (r.processed_by) {
                    toAccountHtml = `
                    <div class="flex items-center gap-3">
                        <div class="admin-avatar sm premium-gradient-secondary uppercase" style="color:#fff;"><span class="material-symbols-outlined text-sm">badge</span></div>
                        <div>
                            <div class="font-bold text-slate-800 dark:text-slate-100">OTC Cash</div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Staff: ${r.processed_by}</div>
                        </div>
                    </div>`;
                } else if (r.description === 'Account Restore Charge') {
                    toAccountHtml = `
                    <div class="flex items-center gap-3">
                        <div class="admin-avatar sm bg-rose-500 uppercase" style="color:#fff;"><span class="material-symbols-outlined text-sm">settings_backup_restore</span></div>
                        <div>
                            <div class="font-bold text-slate-800 dark:text-slate-100">Restore Charge</div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Admin Action</div>
                        </div>
                    </div>`;
                } else {
                    toAccountHtml = `
                    <div class="flex items-center gap-3">
                        <div class="admin-avatar sm premium-gradient-secondary uppercase" style="color:#fff;"><span class="material-symbols-outlined text-sm">atm</span></div>
                        <div>
                            <div class="font-bold text-slate-800 dark:text-slate-100">Direct Withdrawal</div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">ATM / Direct</div>
                        </div>
                    </div>`;
                }
            } else {
                // For deposits, transfers, and interest, To Account is the recipient customer
                const receiverInitials = r.to_user_name && r.to_user_name !== 'N/A' ? r.to_user_name.charAt(0).toUpperCase() : 'U';
                let receiverAvatarContent = receiverInitials;
                if (r.to_user_avatar) {
                    const src = (r.to_user_avatar.startsWith('http') ? r.to_user_avatar : `/Finzo/public/assets/${r.to_user_avatar}`) + '?v=' + new Date().getTime();
                    receiverAvatarContent = `<img src="${src}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">`;
                }
                
                toAccountHtml = `
                <div class="flex items-center gap-3">
                    <div class="admin-avatar sm uppercase" style="color:#fff; ${r.to_user_avatar ? 'background:none;' : window.FinzoUtils.getAvatarGradient(receiverInitials)}">${receiverAvatarContent}</div>
                    <div>
                        <div class="font-bold text-slate-800 dark:text-slate-100">${r.to_user_name}</div>
                        <div class="text-[11px] opacity-40 font-mono font-semibold">${r.to_account_no}</div>
                    </div>
                </div>`;
            }

            return `
            <tr class="hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-colors">
                <td class="font-bold opacity-30 text-[11px] tracking-widest">${countStr}</td>
                <td class="text-[11px] font-bold opacity-50 font-mono tracking-tighter">${r.txn_id}</td>
                <td>${fromAccountHtml}</td>
                <td>${toAccountHtml}</td>
                <td><span class="badge badge-${typeClass}">${r.type.toUpperCase()}</span></td>
                <td class="text-sm font-black ${amountClass}">${amountFormatted}</td>
                <td class="text-[11px] font-bold opacity-60">${r.created_at}</td>
                <td><span class="badge ${r.status === 'completed' ? 'badge-completed' : 'badge-pending'}">${r.status.toUpperCase()}</span></td>
                <td class="text-right">
                    <button onclick="window.open('../../../../api/v1/documents/receipt?id=${r.txn_id}', '_blank')" class="admin-btn-secondary px-3 py-1.5 text-[10px] uppercase tracking-widest font-bold rounded-lg inline-flex items-center gap-1.5 hover:bg-primary hover:text-white dark:hover:bg-blue-600 transition-all border-slate-200 dark:border-white/10">
                        <span class="material-symbols-outlined !text-[14px]">receipt_long</span> Receipt
                    </button>
                </td>
            </tr>`;
        }).join('');

        updatePagination(total, pages);
    }

    /**
     * Update pagination UI
     */
    function updatePagination(total, pages) {
        const info = document.getElementById('pagination-info');
        if (info) {
            info.textContent = `Showing ${Math.min((currentPage - 1) * rowsPerPage + 1, total)}-${Math.min(currentPage * rowsPerPage, total)} of ${total}`;
        }

        const prevBtn = document.getElementById('prev-btn');
        const nextBtn = document.getElementById('next-btn');
        const pagNumbers = document.getElementById('page-numbers');

        if (prevBtn) {
            prevBtn.disabled = (currentPage === 1);
            prevBtn.classList.toggle('opacity-50', currentPage === 1);
            prevBtn.classList.toggle('cursor-not-allowed', currentPage === 1);
        }
        if (nextBtn) {
            nextBtn.disabled = (currentPage === pages || pages === 0);
            nextBtn.classList.toggle('opacity-50', currentPage === pages || pages === 0);
            nextBtn.classList.toggle('cursor-not-allowed', currentPage === pages || pages === 0);
        }

        if (pagNumbers) {
            let html = '';
            for (let i = 1; i <= pages; i++) {
                if (pages > 5 && i > 3 && i < pages) {
                    if (i === 4) html += '<span class="px-2 opacity-50">...</span>';
                    continue;
                }
                const activeClass = i === currentPage ? 'bg-primary text-white' : 'bg-slate-100 dark:bg-white/5 text-slate-400 hover:text-primary cursor-pointer';
                html += `<span data-page="${i}" class="w-8 h-8 flex items-center justify-center ${activeClass} rounded-lg text-xs font-bold transition-all">${i}</span>`;
            }
            pagNumbers.innerHTML = html;
        }
    }

    /**
     * Update stat cards (safely, if present)
     */
    function updateStats(stats) {
        const cards = document.querySelectorAll('.stat-card');
        if (cards.length >= 1 && stats.total_amount !== undefined) {
            const val = cards[0].querySelector('.text-2xl');
            if (val) { val.textContent = '₹' + parseFloat(stats.total_amount).toLocaleString('en-IN', { maximumFractionDigits: 0 }); }
        }
    }

    /**
     * Bind events
     */
    function bindEvents() {
        // Auto filter on Type Tab Click
        const tabBtns = document.querySelectorAll('.tab-btn');
        tabBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove active classes from all tabs
                tabBtns.forEach(b => {
                    b.classList.remove('bg-primary', 'text-white', 'shadow-lg', 'shadow-primary/20', 'scale-[1.02]', 'dark:bg-sky-500/20', 'dark:text-sky-300', 'dark:shadow-none', 'dark:ring-1', 'dark:ring-sky-500/30');
                    b.classList.add('bg-slate-100', 'text-slate-600', 'hover:bg-slate-200/80', 'hover:text-slate-800', 'hover:scale-[1.01]', 'dark:bg-white/5', 'dark:text-slate-400', 'dark:hover:bg-white/10', 'dark:hover:text-slate-200');
                });
                
                // Add active class to clicked tab
                this.classList.remove('bg-slate-100', 'text-slate-600', 'hover:bg-slate-200/80', 'hover:text-slate-800', 'hover:scale-[1.01]', 'dark:bg-white/5', 'dark:text-slate-400', 'dark:hover:bg-white/10', 'dark:hover:text-slate-200');
                this.classList.add('bg-primary', 'text-white', 'shadow-lg', 'shadow-primary/20', 'scale-[1.02]', 'dark:bg-sky-500/20', 'dark:text-sky-300', 'dark:shadow-none', 'dark:ring-1', 'dark:ring-sky-500/30');

                currentType = this.getAttribute('data-type');
                currentPage = 1;
                loadTransactions();
            });
        });

        // Auto filter on Search Input (Debounced)
        const searchInput = document.getElementById('transaction-search');
        if (searchInput) {
            let debounceTimer;
            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    currentPage = 1;
                    loadTransactions();
                }, 300);
            });
        }

        // Pagination Click Events
        document.addEventListener('click', (e) => {
            const span = e.target.closest('span[data-page]');
            if (span) {
                currentPage = parseInt(span.getAttribute('data-page'));
                loadTransactions();
            }
        });

        const prevBtn = document.getElementById('prev-btn');
        if (prevBtn) prevBtn.addEventListener('click', () => { if (currentPage > 1) { currentPage--; loadTransactions(); } });

        const nextBtn = document.getElementById('next-btn');
        if (nextBtn) nextBtn.addEventListener('click', () => { currentPage++; loadTransactions(); });
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', () => {
        loadTransactions();
        bindEvents();
    });
})();
