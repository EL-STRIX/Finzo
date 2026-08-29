(function() {
    let activePeriodId = null;

    async function loadActivePeriod() {
        try {
            const res = await window.api.get('/admin/ledger/periods/active');
            const payload = res.data || res;
            if (res.success && payload.has_active) {
                activePeriodId = payload.period.id;
                document.getElementById('active-period-name').textContent = `${payload.period.name} (${payload.period.start_date} to ${payload.period.end_date})`;
                document.getElementById('active-period-name').classList.remove('skeleton');
            } else {
                document.getElementById('active-period-name').textContent = 'No Active Period';
                document.getElementById('active-period-name').classList.remove('skeleton');
                document.getElementById('btn-close-period').classList.add('hidden');
            }
        } catch (error) {
            console.error('Failed to load active period', error);
            document.getElementById('active-period-name').textContent = 'System Default';
            document.getElementById('active-period-name').classList.remove('skeleton');
        }
    }

    async function loadChartOfAccounts() {
        try {
            const res = await window.api.get('/admin/ledger/accounts');
            const payload = res.data || res;
            const container = document.getElementById('coa-tree');
            if (res.success && payload.accounts) {
                renderCOA(payload.accounts, container);
            } else {
                container.innerHTML = '<div class="text-center py-8 text-text-muted text-sm">Chart of Accounts not initialized.</div>';
            }
        } catch (error) {
            console.error('Failed to load COA', error);
            document.getElementById('coa-tree').innerHTML = '<div class="text-center py-8 text-text-muted text-sm">Error loading COA.</div>';
        }
    }

    function renderCOA(accounts, container) {
        // Assuming accounts is grouped by type (Asset, Liability, Equity, Revenue, Expense)
        let html = '';
        const types = ['Asset', 'Liability', 'Equity', 'Revenue', 'Expense'];
        
        types.forEach(type => {
            const typeAccounts = accounts.filter(a => a.type === type);
            if(typeAccounts.length === 0) return;

            let typeIcon = 'account_balance';
            if(type === 'Liability') typeIcon = 'credit_score';
            if(type === 'Revenue') typeIcon = 'trending_up';
            if(type === 'Expense') typeIcon = 'trending_down';
            
            html += `
            <div class="mb-4">
                <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-2 flex items-center gap-1 border-b border-border-subtle pb-1">
                    <span class="material-symbols-outlined text-[14px]">${typeIcon}</span> ${type}s
                </div>
                <div class="space-y-1">
            `;
            
            typeAccounts.forEach(acc => {
                html += `
                <div class="flex justify-between items-center p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-white/5 transition-colors text-sm">
                    <div class="font-bold">${acc.code} - ${acc.name}</div>
                    <div class="font-mono text-text-muted opacity-80">₹${window.FinzoUtils.formatINR(acc.balance || 0)}</div>
                </div>
                `;
            });
            
            html += `</div></div>`;
        });
        
        if (html === '') {
            html = '<div class="text-center py-8 text-text-muted text-sm">No accounts found.</div>';
        }
        container.innerHTML = html;
    }

    async function loadJournals() {
        try {
            const res = await window.api.get('/admin/ledger/journals?per_page=50');
            const tbody = document.getElementById('journals-tbody');
            if (res.success && res.data) {
                renderJournals(res.data, tbody);
            } else {
                tbody.innerHTML = '<tr><td colspan="5" class="py-12 text-center text-text-muted">No journal entries found.</td></tr>';
            }
        } catch (error) {
            console.error('Failed to load journals', error);
            document.getElementById('journals-tbody').innerHTML = '<tr><td colspan="5" class="py-12 text-center text-error">Error loading journals.</td></tr>';
        }
    }

    function renderJournals(entries, tbody) {
        if (!entries.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="py-12 text-center text-text-muted">No journal entries found.</td></tr>';
            return;
        }

        let html = '';
        entries.forEach(entry => {
            const date = new Date(entry.created_at).toLocaleString();
            const dr = entry.type === 'debit' ? `₹${window.FinzoUtils.formatINR(entry.amount)}` : '-';
            const cr = entry.type === 'credit' ? `₹${window.FinzoUtils.formatINR(entry.amount)}` : '-';
            
            html += `
            <tr class="hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">
                <td class="font-mono text-xs opacity-60 truncate max-w-[120px]" title="${entry.transaction_id}">${entry.transaction_id}</td>
                <td class="text-sm opacity-80">${date}</td>
                <td class="font-bold text-sm">
                    ${entry.account_code} - ${entry.account_name}
                    <div class="text-[10px] text-text-muted font-normal mt-0.5 truncate max-w-[200px]">${entry.description || ''}</div>
                </td>
                <td class="text-right font-mono text-sm font-bold text-success">${dr}</td>
                <td class="text-right font-mono text-sm font-bold text-error">${cr}</td>
            </tr>
            `;
        });
        tbody.innerHTML = html;
    }

    function init() {
        loadActivePeriod();
        loadChartOfAccounts();
        loadJournals();

        document.getElementById('btn-close-period')?.addEventListener('click', async () => {
            if (!activePeriodId) {
                window.showToast('No active period to close', 'warning');
                return;
            }

            if (!confirm('WARNING: Closing the accounting period is irreversible. All balances will be rolled forward. Are you sure?')) {
                return;
            }

            const btn = document.getElementById('btn-close-period');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="loader border-warning border-b-transparent"></span> Processing...';
            btn.disabled = true;

            try {
                const res = await window.api.post('/admin/ledger/periods/close', { period_id: activePeriodId });
                if (res.success) {
                    window.showToast(res.message, 'success');
                    loadActivePeriod();
                } else {
                    window.showToast(res.message || 'Failed to close period', 'error');
                }
            } catch (err) {
                window.showToast('Error closing accounting period', 'error');
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
