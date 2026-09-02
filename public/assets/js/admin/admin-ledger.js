/**
 * Finzo Admin — General Ledger Controller
 * Handles double-entry ledger summaries, chart of accounts, journals,
 * accounting period lifecycle (open/close), drill-down, and CSV exports.
 */
(function() {
    'use strict';

    // State variables
    let activePeriodId = null;
    let activePeriodData = null;
    let allAccounts = [];
    let currentPage = 1;
    const perPage = 20;
    let journalSearch = '';
    let journalType = '';

    // Unified Toast Helper
    function toast(msg, type = 'info') {
        if (window.showToast) {
            window.showToast(msg, type);
        } else if (window.FinzoUtils && window.FinzoUtils.showToast) {
            window.FinzoUtils.showToast(msg, type);
        } else if (window.portal && window.portal.showToast) {
            window.portal.showToast(msg, type);
        } else {
            alert(msg);
        }
    }

    // XSS Prevention Helper
    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Currency Formatter
    function formatINR(amount) {
        const num = parseFloat(amount || 0);
        if (window.FinzoUtils && window.FinzoUtils.formatINR) {
            return window.FinzoUtils.formatINR(num);
        }
        return num.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Date Formatter
    function formatDate(dateStr) {
        if (!dateStr) return '—';
        try {
            const d = new Date(dateStr);
            return isNaN(d.getTime()) ? dateStr : d.toLocaleDateString('en-IN', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        } catch (e) {
            return dateStr;
        }
    }

    function formatDateTime(dateStr) {
        if (!dateStr) return '—';
        try {
            const d = new Date(dateStr);
            return isNaN(d.getTime()) ? dateStr : d.toLocaleString('en-IN', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch (e) {
            return dateStr;
        }
    }

    // =====================================================
    // 1. FINANCIAL SUMMARY & DOUBLE-ENTRY KPIS
    // =====================================================
    async function loadLedgerSummary() {
        try {
            const res = await window.api.get('/admin/ledger/summary');
            if (res.success && res.summary) {
                const s = res.summary;
                
                // Total Assets
                const assetsEl = document.getElementById('kpi-total-assets');
                if (assetsEl) {
                    assetsEl.textContent = `₹${formatINR(s.assets)}`;
                    assetsEl.classList.remove('skeleton');
                }

                // Total Liabilities
                const liabEl = document.getElementById('kpi-total-liabilities');
                if (liabEl) {
                    liabEl.textContent = `₹${formatINR(s.liabilities)}`;
                    liabEl.classList.remove('skeleton');
                }

                // Ledger Double-Entry Health (Debits = Credits)
                const badgeEl = document.getElementById('kpi-balance-badge');
                const varianceEl = document.getElementById('kpi-balance-variance');
                const subEl = document.getElementById('kpi-balance-subtitle');
                const iconBoxEl = document.getElementById('kpi-balance-icon-box');
                const iconEl = document.getElementById('kpi-balance-icon');

                if (s.is_balanced) {
                    if (badgeEl) {
                        badgeEl.className = 'status-badge badge-success text-xs font-bold uppercase';
                        badgeEl.textContent = 'BALANCED';
                    }
                    if (varianceEl) varianceEl.classList.add('hidden');
                    if (iconBoxEl) iconBoxEl.className = 'w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center';
                    if (iconEl) iconEl.textContent = 'check_circle';
                } else {
                    if (badgeEl) {
                        badgeEl.className = 'status-badge badge-error text-xs font-bold uppercase';
                        badgeEl.textContent = 'UNBALANCED';
                    }
                    if (varianceEl) {
                        varianceEl.textContent = `Diff: ₹${formatINR(s.difference)}`;
                        varianceEl.classList.remove('hidden');
                    }
                    if (iconBoxEl) iconBoxEl.className = 'w-9 h-9 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center';
                    if (iconEl) iconEl.textContent = 'error';
                }

                if (subEl) {
                    subEl.textContent = `DR = CR: ₹${formatINR(s.total_debits)}`;
                }

                // Total Journals & Lines
                const journalsEl = document.getElementById('kpi-total-journals');
                if (journalsEl) {
                    journalsEl.textContent = parseInt(s.total_journals || 0).toLocaleString('en-IN');
                    journalsEl.classList.remove('skeleton');
                }
                const linesEl = document.getElementById('kpi-total-lines');
                if (linesEl) {
                    linesEl.textContent = `${parseInt(s.total_lines || 0).toLocaleString('en-IN')} journal lines across all accounts`;
                }
            }
        } catch (error) {
            console.error('Failed to load ledger summary', error);
        }
    }

    // =====================================================
    // 2. ACTIVE ACCOUNTING PERIOD
    // =====================================================
    async function loadActivePeriod() {
        const nameEl = document.getElementById('active-period-name');
        const statusEl = document.getElementById('active-period-status');
        const btnClose = document.getElementById('btn-close-period');
        const btnOpen = document.getElementById('btn-open-period');
        const iconBox = document.getElementById('period-icon-box');

        try {
            const res = await window.api.get('/admin/ledger/periods/active');
            const payload = res.data || res;

            if (res.success && payload.has_active && payload.period) {
                activePeriodId = payload.period.id;
                activePeriodData = payload.period;

                if (nameEl) {
                    nameEl.textContent = `${payload.period.name} (${formatDate(payload.period.start_date)} to ${formatDate(payload.period.end_date)})`;
                    nameEl.classList.remove('skeleton');
                }
                if (statusEl) {
                    statusEl.className = 'status-badge badge-success font-mono font-bold';
                    statusEl.textContent = 'OPEN';
                }
                if (btnClose) btnClose.classList.remove('hidden');
                if (btnOpen) btnOpen.classList.add('hidden');
                if (iconBox) iconBox.className = 'w-11 h-11 rounded-xl bg-primary text-white flex items-center justify-center shrink-0 shadow-sm';
            } else {
                activePeriodId = null;
                activePeriodData = null;

                if (nameEl) {
                    nameEl.textContent = 'No Active Accounting Period';
                    nameEl.classList.remove('skeleton');
                }
                if (statusEl) {
                    statusEl.className = 'status-badge badge-warn font-mono font-bold';
                    statusEl.textContent = 'NO ACTIVE PERIOD';
                }
                if (btnClose) btnClose.classList.add('hidden');
                if (btnOpen) btnOpen.classList.remove('hidden');
                if (iconBox) iconBox.className = 'w-11 h-11 rounded-xl bg-amber-600 text-white flex items-center justify-center shrink-0 shadow-sm';
            }
        } catch (error) {
            console.error('Failed to load active period', error);
            if (nameEl) {
                nameEl.textContent = 'System Default Period';
                nameEl.classList.remove('skeleton');
            }
            if (statusEl) {
                statusEl.className = 'status-badge badge-neutral font-mono font-bold';
                statusEl.textContent = 'SYSTEM';
            }
        }
    }

    // =====================================================
    // 3. CHART OF ACCOUNTS
    // =====================================================
    async function loadChartOfAccounts() {
        const container = document.getElementById('coa-tree');
        try {
            const res = await window.api.get('/admin/ledger/accounts');
            const payload = res.data || res;
            if (res.success && payload.accounts) {
                allAccounts = payload.accounts;
                const badgeEl = document.getElementById('coa-count-badge');
                if (badgeEl) badgeEl.textContent = `${allAccounts.length} Accounts`;
                renderCOA(allAccounts);
            } else {
                if (container) container.innerHTML = '<div class="text-center py-12 text-text-muted text-sm">Chart of Accounts not initialized.</div>';
            }
        } catch (error) {
            console.error('Failed to load COA', error);
            if (container) container.innerHTML = '<div class="text-center py-12 text-error text-sm">Error loading Chart of Accounts.</div>';
        }
    }

    function renderCOA(accounts) {
        const container = document.getElementById('coa-tree');
        if (!container) return;

        const types = [
            { key: 'Asset', icon: 'account_balance', color: 'text-emerald-600 dark:text-emerald-400' },
            { key: 'Liability', icon: 'credit_score', color: 'text-amber-600 dark:text-amber-400' },
            { key: 'Equity', icon: 'pie_chart', color: 'text-blue-600 dark:text-blue-400' },
            { key: 'Revenue', icon: 'trending_up', color: 'text-teal-600 dark:text-teal-400' },
            { key: 'Expense', icon: 'trending_down', color: 'text-rose-600 dark:text-rose-400' }
        ];

        let html = '';

        types.forEach(t => {
            const group = accounts.filter(a => a.type === t.key);
            if (group.length === 0) return;

            // Group balance sum
            const groupTotal = group.reduce((sum, a) => sum + (parseFloat(a.balance) || 0), 0);

            html += `
            <div class="mb-5 bg-surface rounded-xl border border-border-subtle overflow-hidden">
                <div class="bg-surface-subtle px-3.5 py-2.5 flex items-center justify-between border-b border-border-subtle">
                    <div class="text-[11px] font-black uppercase tracking-wider flex items-center gap-1.5 ${t.color}">
                        <span class="material-symbols-outlined text-[15px]">${t.icon}</span>
                        <span>${t.key}s (${group.length})</span>
                    </div>
                    <span class="font-mono text-xs font-bold text-text-primary">₹${formatINR(groupTotal)}</span>
                </div>
                <div class="divide-y divide-border-subtle/50">
            `;

            group.forEach(acc => {
                const bal = parseFloat(acc.balance) || 0;
                const balClass = bal < 0 ? 'text-error' : (bal > 0 ? 'text-text-primary' : 'text-text-muted');

                html += `
                <div class="flex justify-between items-center px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-white/5 transition-colors text-xs">
                    <div>
                        <span class="font-mono font-bold text-primary mr-1.5">${escapeHtml(acc.code)}</span>
                        <span class="font-medium text-text-primary">${escapeHtml(acc.name)}</span>
                    </div>
                    <div class="font-mono font-bold ${balClass}">₹${formatINR(bal)}</div>
                </div>
                `;
            });

            html += `</div></div>`;
        });

        if (html === '') {
            html = '<div class="text-center py-12 text-text-muted text-xs">No accounts match your filter.</div>';
        }

        container.innerHTML = html;
    }

    function filterCOA() {
        const query = (document.getElementById('coa-search')?.value || '').trim().toLowerCase();
        const clearBtn = document.getElementById('coa-search-clear');
        if (clearBtn) {
            clearBtn.classList.toggle('hidden', query.length === 0);
        }

        if (!query) {
            renderCOA(allAccounts);
            return;
        }

        const filtered = allAccounts.filter(a => 
            (a.code && a.code.toLowerCase().includes(query)) ||
            (a.name && a.name.toLowerCase().includes(query)) ||
            (a.type && a.type.toLowerCase().includes(query))
        );
        renderCOA(filtered);
    }

    // =====================================================
    // 4. JOURNAL ENTRIES & PAGINATION
    // =====================================================
    async function loadJournals(page = 1) {
        currentPage = page;
        const tbody = document.getElementById('journals-tbody');
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="6" class="py-16 text-center"><span class="loader"></span></td></tr>';
        }

        try {
            const searchParam = encodeURIComponent(journalSearch);
            const typeParam = encodeURIComponent(journalType);
            const res = await window.api.get(`/admin/ledger/journals?page=${page}&per_page=${perPage}&search=${searchParam}&type=${typeParam}`);

            if (res.success && res.data) {
                renderJournals(res.data);
                renderPagination(res.total, res.pages, res.current_page, res.per_page);
                updateTableTotals(res.data);
            } else {
                if (tbody) tbody.innerHTML = '<tr><td colspan="6" class="py-16 text-center text-text-muted text-xs">No journal entries found.</td></tr>';
                renderPagination(0, 1, 1, perPage);
                updateTableTotals([]);
            }
        } catch (error) {
            console.error('Failed to load journals', error);
            if (tbody) tbody.innerHTML = '<tr><td colspan="6" class="py-16 text-center text-error text-xs">Failed to load journal entries.</td></tr>';
        }
    }

    function renderJournals(entries) {
        const tbody = document.getElementById('journals-tbody');
        if (!tbody) return;

        if (!entries || entries.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="py-16 text-center text-text-muted text-xs">No journal entries found matching criteria.</td></tr>';
            return;
        }

        let html = '';
        entries.forEach(entry => {
            const dateFormatted = formatDateTime(entry.created_at);
            const isDebit = entry.type === 'debit';
            const dr = isDebit ? `₹${formatINR(entry.amount)}` : '—';
            const cr = !isDebit ? `₹${formatINR(entry.amount)}` : '—';

            html += `
            <tr class="hover:bg-slate-50 dark:hover:bg-white/5 transition-colors group">
                <td class="font-mono text-xs font-bold text-primary truncate max-w-[130px]" title="${escapeHtml(entry.transaction_id)}">
                    ${escapeHtml(entry.transaction_id)}
                </td>
                <td class="text-xs text-text-secondary whitespace-nowrap">${dateFormatted}</td>
                <td>
                    <div class="font-bold text-xs text-text-primary">
                        <span class="font-mono text-primary font-bold mr-1">${escapeHtml(entry.account_code)}</span>
                        ${escapeHtml(entry.account_name)}
                    </div>
                    <div class="text-[11px] text-text-muted font-normal mt-0.5 truncate max-w-[240px]" title="${escapeHtml(entry.description || '')}">
                        ${escapeHtml(entry.description || 'System Posting')}
                    </div>
                </td>
                <td class="text-right font-mono text-xs font-bold text-success whitespace-nowrap">${dr}</td>
                <td class="text-right font-mono text-xs font-bold text-error whitespace-nowrap">${cr}</td>
                <td class="text-center whitespace-nowrap">
                    <button type="button" onclick="window.FinzoLedger.viewJournalDetail(${entry.journal_id})" class="p-1.5 hover:bg-background rounded-lg text-text-muted hover:text-primary transition-colors cursor-pointer" title="View journal entry breakdown">
                        <span class="material-symbols-outlined text-base">visibility</span>
                    </button>
                </td>
            </tr>
            `;
        });

        tbody.innerHTML = html;
    }

    function updateTableTotals(entries) {
        const tfoot = document.getElementById('journals-tfoot');
        const drEl = document.getElementById('footer-total-debit');
        const crEl = document.getElementById('footer-total-credit');

        if (!tfoot || !drEl || !crEl) return;

        if (!entries || entries.length === 0) {
            tfoot.classList.add('hidden');
            return;
        }

        let totalDebit = 0;
        let totalCredit = 0;

        entries.forEach(e => {
            if (e.type === 'debit') totalDebit += (parseFloat(e.amount) || 0);
            if (e.type === 'credit') totalCredit += (parseFloat(e.amount) || 0);
        });

        drEl.textContent = `₹${formatINR(totalDebit)}`;
        crEl.textContent = `₹${formatINR(totalCredit)}`;
        tfoot.classList.remove('hidden');
    }

    function renderPagination(total, pages, current, limit) {
        const infoEl = document.getElementById('journals-page-info');
        const controlsEl = document.getElementById('journals-page-controls');
        const countTextEl = document.getElementById('journals-count-text');

        const start = total === 0 ? 0 : (current - 1) * limit + 1;
        const end = Math.min(total, current * limit);

        if (infoEl) infoEl.textContent = `Showing ${start}-${end} of ${total} Entries`;
        if (countTextEl) countTextEl.textContent = `Showing ${end - start + (total > 0 ? 1 : 0)} of ${total} entries`;

        if (!controlsEl) return;
        if (pages <= 1) {
            controlsEl.innerHTML = '';
            return;
        }

        let html = '';

        // Previous button
        html += `
        <button type="button" onclick="window.FinzoLedger.goToPage(${current - 1})" ${current <= 1 ? 'disabled' : ''} class="p-1.5 rounded-lg border border-border-subtle bg-surface text-text-secondary hover:text-text-primary disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-colors" title="Previous Page">
            <span class="material-symbols-outlined text-sm">chevron_left</span>
        </button>
        `;

        // Page buttons (windowed around current)
        const windowSize = 5;
        let startPage = Math.max(1, current - Math.floor(windowSize / 2));
        let endPage = Math.min(pages, startPage + windowSize - 1);
        if (endPage - startPage + 1 < windowSize) {
            startPage = Math.max(1, endPage - windowSize + 1);
        }

        for (let p = startPage; p <= endPage; p++) {
            const isActive = p === current;
            html += `
            <button type="button" onclick="window.FinzoLedger.goToPage(${p})" class="px-2.5 py-1 text-xs font-mono font-bold rounded-lg border ${isActive ? 'bg-primary text-white border-primary shadow-sm' : 'bg-surface text-text-secondary border-border-subtle hover:text-text-primary'} cursor-pointer transition-colors">
                ${p}
            </button>
            `;
        }

        // Next button
        html += `
        <button type="button" onclick="window.FinzoLedger.goToPage(${current + 1})" ${current >= pages ? 'disabled' : ''} class="p-1.5 rounded-lg border border-border-subtle bg-surface text-text-secondary hover:text-text-primary disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-colors" title="Next Page">
            <span class="material-symbols-outlined text-sm">chevron_right</span>
        </button>
        `;

        controlsEl.innerHTML = html;
    }

    // =====================================================
    // 5. JOURNAL ENTRY DRILL-DOWN MODAL
    // =====================================================
    async function viewJournalDetail(journalId) {
        const modal = document.getElementById('modal-journal-detail');
        if (!modal) return;

        // Reset fields
        document.getElementById('modal-journal-number').textContent = 'Loading...';
        document.getElementById('modal-journal-date').textContent = '';
        document.getElementById('modal-journal-ref').textContent = '—';
        document.getElementById('modal-journal-module').textContent = '—';
        document.getElementById('modal-journal-period').textContent = '—';
        document.getElementById('modal-journal-desc').textContent = '—';
        document.getElementById('modal-journal-lines-tbody').innerHTML = '<tr><td colspan="4" class="py-8 text-center"><span class="loader"></span></td></tr>';
        document.getElementById('modal-journal-total-debit').textContent = '₹0.00';
        document.getElementById('modal-journal-total-credit').textContent = '₹0.00';

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        try {
            const res = await window.api.get(`/admin/ledger/journals/${journalId}`);
            if (res.success && res.journal) {
                const j = res.journal;
                document.getElementById('modal-journal-number').textContent = j.journal_number || `JNL-${j.id}`;
                document.getElementById('modal-journal-status').textContent = (j.status || 'POSTED').toUpperCase();
                document.getElementById('modal-journal-date').textContent = `Posted on ${formatDateTime(j.created_at)}`;
                document.getElementById('modal-journal-ref').textContent = j.reference || '—';
                document.getElementById('modal-journal-module').textContent = j.source_module || 'General Ledger';
                document.getElementById('modal-journal-period').textContent = j.period_name || (j.period_id ? `Period #${j.period_id}` : 'General');
                document.getElementById('modal-journal-desc').textContent = j.description || 'System financial transaction';

                // Render balanced legs
                let linesHtml = '';
                let totalDr = 0;
                let totalCr = 0;

                (j.lines || []).forEach(l => {
                    const dr = parseFloat(l.debit_amount || 0);
                    const cr = parseFloat(l.credit_amount || 0);
                    totalDr += dr;
                    totalCr += cr;

                    linesHtml += `
                    <tr class="hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">
                        <td class="py-2 px-3">
                            <div class="font-bold text-xs text-text-primary">
                                <span class="font-mono text-primary font-bold">${escapeHtml(l.account_code)}</span> — ${escapeHtml(l.account_name)}
                            </div>
                            ${l.narration ? `<div class="text-[10px] text-text-muted mt-0.5">${escapeHtml(l.narration)}</div>` : ''}
                        </td>
                        <td class="py-2 px-3 capitalize text-text-secondary text-xs">${escapeHtml(l.category || 'Asset')}</td>
                        <td class="py-2 px-3 text-right font-mono font-bold text-success text-xs">${dr > 0 ? `₹${formatINR(dr)}` : '—'}</td>
                        <td class="py-2 px-3 text-right font-mono font-bold text-error text-xs">${cr > 0 ? `₹${formatINR(cr)}` : '—'}</td>
                    </tr>
                    `;
                });

                document.getElementById('modal-journal-lines-tbody').innerHTML = linesHtml;
                document.getElementById('modal-journal-total-debit').textContent = `₹${formatINR(totalDr)}`;
                document.getElementById('modal-journal-total-credit').textContent = `₹${formatINR(totalCr)}`;
            } else {
                toast('Failed to load journal detail', 'error');
                closeJournalModal();
            }
        } catch (e) {
            console.error('Error fetching journal detail', e);
            toast('Network error loading journal detail', 'error');
            closeJournalModal();
        }
    }

    function closeJournalModal() {
        const modal = document.getElementById('modal-journal-detail');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    // =====================================================
    // 6. PERIOD CLOSE MODAL
    // =====================================================
    function openClosePeriodModal() {
        if (!activePeriodId || !activePeriodData) {
            toast('No active accounting period to close', 'warning');
            return;
        }

        const nameEl = document.getElementById('close-modal-period-name');
        const datesEl = document.getElementById('close-modal-period-dates');
        if (nameEl) nameEl.textContent = activePeriodData.name || activePeriodData.period_name;
        if (datesEl) datesEl.textContent = `${formatDate(activePeriodData.start_date)} to ${formatDate(activePeriodData.end_date)}`;

        const modal = document.getElementById('modal-close-period');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function closeClosePeriodModal() {
        const modal = document.getElementById('modal-close-period');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    async function confirmClosePeriod() {
        if (!activePeriodId) return;

        const btn = document.getElementById('btn-confirm-close-period');
        const origText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="loader border-white border-b-transparent"></span> Closing...';

        try {
            const res = await window.api.post('/admin/ledger/periods/close', { period_id: activePeriodId });
            if (res.success) {
                toast(res.message || 'Accounting period closed successfully', 'success');
                closeClosePeriodModal();
                await Promise.all([
                    loadActivePeriod(),
                    loadLedgerSummary(),
                    loadChartOfAccounts(),
                    loadJournals(1)
                ]);
            } else {
                toast(res.message || 'Failed to close period', 'error');
            }
        } catch (err) {
            console.error('Error closing period', err);
            toast('Error processing period close', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = origText;
        }
    }

    // =====================================================
    // 7. PERIOD OPEN MODAL
    // =====================================================
    function openOpenPeriodModal() {
        // Prepopulate smart defaults (e.g. today to +1 year)
        const today = new Date();
        const nextYear = new Date();
        nextYear.setFullYear(today.getFullYear() + 1);

        const pad = n => String(n).padStart(2, '0');
        const todayStr = `${today.getFullYear()}-${pad(today.getMonth() + 1)}-${pad(today.getDate())}`;
        const nextYearStr = `${nextYear.getFullYear()}-${pad(nextYear.getMonth() + 1)}-${pad(nextYear.getDate())}`;

        const nameInput = document.getElementById('open-period-name');
        const startInput = document.getElementById('open-period-start');
        const endInput = document.getElementById('open-period-end');

        if (nameInput) nameInput.value = `FY ${today.getFullYear()}-${today.getFullYear() + 1}`;
        if (startInput) startInput.value = todayStr;
        if (endInput) endInput.value = nextYearStr;

        const modal = document.getElementById('modal-open-period');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function closeOpenPeriodModal() {
        const modal = document.getElementById('modal-open-period');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    async function handleOpenPeriodSubmit(e) {
        e.preventDefault();
        const name = document.getElementById('open-period-name')?.value.trim();
        const startDate = document.getElementById('open-period-start')?.value;
        const endDate = document.getElementById('open-period-end')?.value;

        if (!name || !startDate || !endDate) {
            toast('All fields are required', 'warning');
            return;
        }

        const btn = document.getElementById('btn-submit-open-period');
        const origText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="loader border-white border-b-transparent"></span> Opening...';

        try {
            const res = await window.api.post('/admin/ledger/periods/open', {
                name,
                start_date: startDate,
                end_date: endDate
            });

            if (res.success) {
                toast(res.message || 'Accounting period opened successfully', 'success');
                closeOpenPeriodModal();
                await Promise.all([
                    loadActivePeriod(),
                    loadLedgerSummary(),
                    loadChartOfAccounts(),
                    loadJournals(1)
                ]);
            } else {
                toast(res.message || 'Failed to open accounting period', 'error');
            }
        } catch (err) {
            console.error('Error opening period', err);
            toast('Network error opening accounting period', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = origText;
        }
    }

    // =====================================================
    // 8. CSV EXPORT
    // =====================================================
    function exportLedger() {
        const searchParam = encodeURIComponent(journalSearch);
        const typeParam = encodeURIComponent(journalType);
        const url = `/Finzo/public/api/v1/admin/ledger/export?search=${searchParam}&type=${typeParam}`;

        toast('Preparing general ledger export...', 'info');
        window.location.href = url;
    }

    // =====================================================
    // 9. EVENT BINDING & INITIALIZATION
    // =====================================================
    function init() {
        // Initial data fetches
        loadActivePeriod();
        loadLedgerSummary();
        loadChartOfAccounts();
        loadJournals(1);

        // Period action buttons
        document.getElementById('btn-close-period')?.addEventListener('click', openClosePeriodModal);
        document.getElementById('btn-open-period')?.addEventListener('click', openOpenPeriodModal);
        document.getElementById('btn-confirm-close-period')?.addEventListener('click', confirmClosePeriod);
        document.getElementById('form-open-period')?.addEventListener('submit', handleOpenPeriodSubmit);

        // Export button
        document.getElementById('btn-export-ledger')?.addEventListener('click', exportLedger);

        // Refresh button
        document.getElementById('btn-refresh-ledger')?.addEventListener('click', () => {
            toast('Refreshing ledger...', 'info');
            loadActivePeriod();
            loadLedgerSummary();
            loadChartOfAccounts();
            loadJournals(currentPage);
        });

        // COA Search
        const coaSearch = document.getElementById('coa-search');
        if (coaSearch) {
            let debounceTimer;
            coaSearch.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(filterCOA, 250);
            });
        }
        document.getElementById('coa-search-clear')?.addEventListener('click', () => {
            if (coaSearch) {
                coaSearch.value = '';
                filterCOA();
            }
        });

        // Journals Search
        const journalsSearch = document.getElementById('journals-search');
        if (journalsSearch) {
            let debounceTimer;
            journalsSearch.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    journalSearch = journalsSearch.value.trim();
                    const clearBtn = document.getElementById('journals-search-clear');
                    if (clearBtn) clearBtn.classList.toggle('hidden', journalSearch.length === 0);
                    loadJournals(1);
                }, 300);
            });
        }
        document.getElementById('journals-search-clear')?.addEventListener('click', () => {
            if (journalsSearch) {
                journalsSearch.value = '';
                journalSearch = '';
                document.getElementById('journals-search-clear')?.classList.add('hidden');
                loadJournals(1);
            }
        });

        // Journals Type Filter
        document.getElementById('journals-type-filter')?.addEventListener('change', (e) => {
            journalType = e.target.value;
            loadJournals(1);
        });
    }

    // Expose global methods for modal onclick callbacks
    window.FinzoLedger = {
        goToPage: (page) => loadJournals(page),
        viewJournalDetail: (id) => viewJournalDetail(id),
        closeJournalModal: () => closeJournalModal(),
        closeClosePeriodModal: () => closeClosePeriodModal(),
        closeOpenPeriodModal: () => closeOpenPeriodModal()
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
