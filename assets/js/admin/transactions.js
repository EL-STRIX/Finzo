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
            const statusInput = document.getElementById('filter-status');
            const accountInput = document.getElementById('filter-account');
            const searchInput = document.getElementById('filter-search');
            const dateFrom = document.getElementById('date-from');
            const dateTo = document.getElementById('date-to');

            const response = await window.api.get('/admin/transactions.php', {
                type: currentType,
                status: statusInput ? statusInput.value : 'all',
                account_type: accountInput ? accountInput.value : 'all',
                search: searchInput ? searchInput.value : '',
                date_from: dateFrom ? dateFrom.value : '',
                date_to: dateTo ? dateTo.value : '',
                page: currentPage,
                per_page: rowsPerPage
            });

            if (response.success) {
                renderTable(response.data.transactions, response.data.total, response.data.pages);
                updateStats(response.data.stats || {});
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
            const initials = r.initials || r.full_name.charAt(0);
            const typeClass = r.type.toLowerCase();
            const amountClass = r.amount_formatted.startsWith('+') ? 'text-emerald-500' : 'text-rose-500';
            const countStr = String((currentPage - 1) * rowsPerPage + i + 1).padStart(2, '0');

            return `
            <tr class="hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-colors">
                <td class="font-bold opacity-30 text-[11px] tracking-widest">${countStr}</td>
                <td>
                    <div class="flex items-center gap-3">
                        <div class="admin-avatar sm premium-gradient-primary uppercase" style="color:#fff;">${initials}</div>
                        <div>
                            <div class="font-bold text-slate-800 dark:text-slate-100">${r.full_name}</div>
                            <div class="text-[11px] opacity-40 font-semibold">${r.email}</div>
                        </div>
                    </div>
                </td>
                <td class="text-xs font-bold font-mono text-slate-600 dark:text-slate-400 opacity-80">${r.account_no}</td>
                <td class="text-[11px] font-bold opacity-50 font-mono tracking-tighter">${r.transaction_id}</td>
                <td><span class="badge ${getBadgeClass(typeClass)}">${r.type.toUpperCase()}</span></td>
                <td class="text-[11px] font-bold opacity-60">${r.created_at}</td>
                <td class="text-sm font-black ${amountClass}">${r.amount_formatted}</td>
                <td><span class="badge ${r.status === 'completed' ? 'badge-completed' : 'badge-pending'}">${r.status.toUpperCase()}</span></td>
                <td class="text-right">
                    <button class="p-2 opacity-30 hover:opacity-100 transition-all text-slate-500 font-bold hover:text-primary">
                        <span class="material-symbols-outlined text-lg">print</span>
                    </button>
                </td>
            </tr>`;
        }).join('');

        updatePagination(total, pages);
    }

    /**
     * Get badge class based on transaction type
     */
    function getBadgeClass(type) {
        if (type === 'deposit') return 'badge-deposit';
        if (type === 'withdrawal') return 'badge-withdrawal';
        return 'badge-transfer';
    }

    /**
     * Update pagination UI
     */
    function updatePagination(total, pages) {
        const info = document.getElementById('pag-info');
        if (info) {
            info.textContent = `Showing ${Math.min((currentPage - 1) * rowsPerPage + 1, total)}-${Math.min(currentPage * rowsPerPage, total)} of ${total}`;
        }

        const prevBtn = document.getElementById('prev-page');
        const nextBtn = document.getElementById('next-page');
        const pagNumbers = document.getElementById('pag-numbers');

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
     * Update stat cards
     */
    function updateStats(stats) {
        const cards = document.querySelectorAll('.stat-card');
        if (cards.length >= 1 && stats.volume_today) {
            const val = cards[0].querySelector('.text-2xl');
            if (val) { val.textContent = stats.volume_today; val.classList.remove('skeleton'); }
        }
        if (cards.length >= 2 && stats.deposits_today !== undefined) {
            const val = cards[1].querySelector('.text-2xl');
            if (val) { val.textContent = stats.deposits_today; val.classList.remove('skeleton'); }
        }
        if (cards.length >= 3 && stats.withdrawals_today !== undefined) {
            const val = cards[2].querySelector('.text-2xl');
            if (val) { val.textContent = stats.withdrawals_today; val.classList.remove('skeleton'); }
        }
        if (cards.length >= 4 && stats.transfers_today !== undefined) {
            const val = cards[3].querySelector('.text-2xl');
            if (val) { val.textContent = stats.transfers_today; val.classList.remove('skeleton'); }
        }
    }

    /**
     * Bind events
     */
    function bindEvents() {
        // Tabs
        const tabs = document.querySelectorAll('.tab-btn');
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                tabs.forEach(t => {
                    t.classList.remove('active', 'bg-primary', 'text-white', 'shadow-lg');
                    t.classList.add('text-slate-500');
                });
                this.classList.add('active', 'bg-primary', 'text-white', 'shadow-lg');
                this.classList.remove('text-slate-500');
                
                currentType = this.getAttribute('data-type');
                currentPage = 1;
                loadTransactions();
            });
        });

        // Apply Filters
        const applyBtn = document.getElementById('apply-filters');
        if (applyBtn) applyBtn.addEventListener('click', () => { currentPage = 1; loadTransactions(); });

        // Reset Filters
        const resetBtn = document.getElementById('reset-filters');
        if (resetBtn) {
            resetBtn.addEventListener('click', () => {
                const searchInput = document.getElementById('filter-search');
                const dateFrom = document.getElementById('date-from');
                const dateTo = document.getElementById('date-to');
                const statusInput = document.getElementById('filter-status');
                const accountInput = document.getElementById('filter-account');

                if (searchInput) searchInput.value = '';
                if (dateFrom) dateFrom.value = '';
                if (dateTo) dateTo.value = '';
                
                // Reset dropdowns manually if needed
                if (statusInput) statusInput.value = 'all';
                if (accountInput) accountInput.value = 'all';

                const statusLabel = document.querySelector('#status-dropdown .dropdown-label');
                if (statusLabel) statusLabel.textContent = 'All Status';
                const accountLabel = document.querySelector('#account-dropdown .dropdown-label');
                if (accountLabel) accountLabel.textContent = 'All Accounts';

                const firstTab = document.querySelector('.filter-tab-btn');
                if (firstTab) firstTab.click(); // This will trigger loadTransactions
            });
        }

        // Pagination
        document.addEventListener('click', (e) => {
            const span = e.target.closest('span[data-page]');
            if (span) {
                currentPage = parseInt(span.getAttribute('data-page'));
                loadTransactions();
            }
        });

        const prevBtn = document.getElementById('prev-page');
        if (prevBtn) prevBtn.addEventListener('click', () => { if (currentPage > 1) { currentPage--; loadTransactions(); } });

        const nextBtn = document.getElementById('next-page');
        if (nextBtn) nextBtn.addEventListener('click', () => { currentPage++; loadTransactions(); });
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', () => {
        loadTransactions();
        bindEvents();
    });
})();
