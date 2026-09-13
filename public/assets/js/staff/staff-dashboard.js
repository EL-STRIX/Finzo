/**
 * Finzo Staff — Dashboard Controller
 * Pure Vanilla JavaScript — Clean Architecture & Real-Time Shift Metrics
 */

(function () {
    'use strict';

    // ── Local State ──
    let currentShiftData = null;
    let currentStatsData = null;
    let isRefreshing = false;

    // ── Utility: Safe HTML Escape ──
    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        const div = document.createElement('div');
        div.textContent = String(str);
        return div.innerHTML;
    }

    // ── Utility: Format Time ──
    function formatShiftTime(dateStr) {
        if (!dateStr) return '—';
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        return d.toLocaleTimeString('en-IN', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
    }

    // ── Init on DOM Ready ──
    function initDashboard() {
        bindEvents();
        loadDashboardData();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDashboard);
    } else {
        initDashboard();
    }

    // ── Event Bindings ──
    function bindEvents() {
        const btnRefresh = document.getElementById('btn-refresh-dashboard');
        if (btnRefresh) {
            btnRefresh.addEventListener('click', () => {
                loadDashboardData(true);
            });
        }

        const btnClockIn = document.getElementById('btn-clock-in');
        if (btnClockIn) {
            btnClockIn.addEventListener('click', handleClockIn);
        }

        const btnClockOut = document.getElementById('btn-clock-out');
        if (btnClockOut) {
            btnClockOut.addEventListener('click', openClockOutModal);
        }

        const btnCloseModal = document.getElementById('btn-close-clockout');
        if (btnCloseModal) {
            btnCloseModal.addEventListener('click', closeClockOutModal);
        }

        const btnCancelModal = document.getElementById('btn-cancel-clockout');
        if (btnCancelModal) {
            btnCancelModal.addEventListener('click', closeClockOutModal);
        }

        const clockoutForm = document.getElementById('clockout-form');
        if (clockoutForm) {
            clockoutForm.addEventListener('submit', handleClockOutSubmit);
        }

        // Close modal on backdrop click
        const modal = document.getElementById('clockout-modal');
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    closeClockOutModal();
                }
            });
        }

        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeClockOutModal();
            }
        });
    }

    // ── Core Data Loader ──
    async function loadDashboardData(isManual = false) {
        if (isRefreshing) return;
        isRefreshing = true;

        const refreshIcon = document.getElementById('refresh-icon');
        const refreshBtn = document.getElementById('btn-refresh-dashboard');

        if (refreshIcon) refreshIcon.classList.add('animate-spin');
        if (refreshBtn) refreshBtn.disabled = true;

        try {
            // Ensure session & StaffState are synchronized
            if (typeof fetchStaffSession === 'function') {
                await fetchStaffSession();
            }

            const res = await window.api.get('/staff-portal/dashboard');

            if (!res || !res.success) {
                renderErrorState(res ? res.message : 'Failed to retrieve dashboard data');
                if (isManual && typeof showToast === 'function') {
                    showToast(res ? res.message : 'Unable to refresh dashboard.', 'error');
                }
                return;
            }

            const payload = (res.data && typeof res.data === 'object' && res.data.staff) ? res.data : res;
            const staff = payload.staff || res.staff || {};
            const shift = payload.current_shift || res.current_shift || null;
            const stats = payload.shift_stats || res.shift_stats || {};
            const recentActivity = payload.recent_activity || res.recent_activity || [];

            currentShiftData = shift;
            currentStatsData = stats;

            renderWelcome(staff);
            renderShiftStatus(shift);
            renderStats(stats);
            renderRecentActivity(recentActivity);

            if (isManual && typeof showToast === 'function') {
                showToast('Dashboard metrics refreshed.', 'success');
            }
        } catch (error) {
            console.error('Error fetching staff dashboard data:', error);
            renderErrorState('Network connection error. Please refresh.');
            if (isManual && typeof showToast === 'function') {
                showToast('Network error while refreshing.', 'error');
            }
        } finally {
            isRefreshing = false;
            if (refreshIcon) refreshIcon.classList.remove('animate-spin');
            if (refreshBtn) refreshBtn.disabled = false;
        }
    }

    // ── Render: Header & Welcome ──
    function renderWelcome(staff) {
        const welcomeEl = document.getElementById('welcome-text');
        if (welcomeEl) {
            const hour = new Date().getHours();
            let greeting = 'Good morning';
            if (hour >= 12 && hour < 17) greeting = 'Good afternoon';
            else if (hour >= 17) greeting = 'Good evening';

            const staffName = staff.name || staff.full_name || StaffState.name || 'Staff Member';
            welcomeEl.textContent = `${greeting}, ${staffName}`;
        }

        const branchBadge = document.getElementById('branch-badge');
        if (branchBadge) {
            const branch = staff.branch_name || StaffState.branch_name;
            if (branch && branch !== 'Unassigned') {
                branchBadge.textContent = branch;
                branchBadge.classList.remove('hidden');
            } else {
                branchBadge.classList.add('hidden');
            }
        }
    }

    // ── Render: Shift Operational Status ──
    function renderShiftStatus(shift) {
        const isActive = shift && shift.status === 'active';
        
        // Header Status Pill
        const pillText = document.getElementById('shift-status-text');
        const pillDot = document.getElementById('shift-status-dot');
        const pill = document.getElementById('shift-status-pill');

        if (pillText && pillDot && pill) {
            if (isActive) {
                pillText.textContent = 'Shift Active';
                pillDot.className = 'w-2 h-2 rounded-full bg-emerald-500 animate-pulse';
                pill.className = 'flex items-center gap-2 px-3 py-2 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-xs font-bold text-emerald-600 dark:text-emerald-400 shadow-sm';
            } else {
                pillText.textContent = 'Off Duty';
                pillDot.className = 'w-2 h-2 rounded-full bg-slate-400';
                pill.className = 'flex items-center gap-2 px-3 py-2 bg-white dark:bg-white/5 rounded-xl border border-surface-border dark:border-white/10 text-xs font-bold text-text-muted shadow-sm';
            }
        }

        // Shift Control Banner
        const shiftTitle = document.getElementById('shift-title');
        const shiftBadge = document.getElementById('shift-badge');
        const shiftDetail = document.getElementById('shift-detail');
        const shiftIconBox = document.getElementById('shift-icon-box');
        const shiftIcon = document.getElementById('shift-icon');
        const btnClockIn = document.getElementById('btn-clock-in');
        const btnClockOut = document.getElementById('btn-clock-out');

        if (isActive) {
            const clockInTime = formatShiftTime(shift.clock_in);
            if (shiftTitle) shiftTitle.textContent = 'Active Shift in Progress';
            if (shiftBadge) {
                shiftBadge.textContent = 'On Duty';
                shiftBadge.className = 'px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20';
            }
            if (shiftDetail) shiftDetail.textContent = `Shift commenced at ${clockInTime}. Counter operations are actively being tracked.`;
            if (shiftIconBox) shiftIconBox.className = 'w-11 h-11 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0';
            if (shiftIcon) shiftIcon.textContent = 'timelapse';

            if (btnClockIn) btnClockIn.classList.add('hidden');
            if (btnClockOut) btnClockOut.classList.remove('hidden');
        } else {
            if (shiftTitle) shiftTitle.textContent = 'Shift Operations';
            if (shiftBadge) {
                shiftBadge.textContent = 'Off Duty';
                shiftBadge.className = 'px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20';
            }
            if (shiftDetail) shiftDetail.textContent = 'You are currently off-duty. Clock in to begin recording today\'s customer deposits and cash transactions.';
            if (shiftIconBox) shiftIconBox.className = 'w-11 h-11 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0';
            if (shiftIcon) shiftIcon.textContent = 'schedule';

            if (btnClockIn) btnClockIn.classList.remove('hidden');
            if (btnClockOut) btnClockOut.classList.add('hidden');
        }
    }

    // ── Render: Summary Grid ──
    function renderStats(stats) {
        const depositsTotal = parseFloat(stats.deposits_total) || 0;
        const withdrawalsTotal = parseFloat(stats.withdrawals_total) || 0;
        const totalTxns = parseInt(stats.total_transactions, 10) || 0;
        const netFlow = parseFloat(stats.net_cash_flow) || (depositsTotal - withdrawalsTotal);

        const depEl = document.getElementById('stat-deposits');
        const withEl = document.getElementById('stat-withdrawals');
        const txnEl = document.getElementById('stat-transactions');
        const netEl = document.getElementById('stat-netflow');
        const netSub = document.getElementById('stat-netflow-sub');

        if (depEl) depEl.textContent = formatINR(depositsTotal);
        if (withEl) withEl.textContent = formatINR(withdrawalsTotal);
        if (txnEl) txnEl.textContent = totalTxns.toString();

        const depCountEl = document.getElementById('stat-deposits-count');
        if (depCountEl) {
            const count = parseInt(stats.deposits_count, 10) || 0;
            depCountEl.textContent = `${count} deposit${count === 1 ? '' : 's'} today`;
        }

        const withCountEl = document.getElementById('stat-withdrawals-count');
        if (withCountEl) {
            const count = parseInt(stats.withdrawals_count, 10) || 0;
            withCountEl.textContent = `${count} withdrawal${count === 1 ? '' : 's'} today`;
        }

        if (netEl) {
            const sign = netFlow > 0 ? '+' : '';
            netEl.textContent = sign + formatINR(netFlow);

            netEl.classList.remove('text-success-base', 'text-danger-base', 'text-info-base', 'dark:text-primary-base');
            if (netFlow > 0) {
                netEl.classList.add('text-success-base');
                if (netSub) netSub.textContent = 'Net Cash Surplus';
            } else if (netFlow < 0) {
                netEl.classList.add('text-danger-base');
                if (netSub) netSub.textContent = 'Net Cash Deficit';
            } else {
                netEl.classList.add('text-text-main', 'dark:text-white');
                if (netSub) netSub.textContent = 'In-hand balance neutral';
            }
        }
    }

    // ── Render: Recent Activity Table ──
    function renderRecentActivity(txns) {
        const tbody = document.getElementById('txn-tbody');
        const recentCount = document.getElementById('recent-count');

        const totalCount = Array.isArray(txns) ? txns.length : 0;
        if (recentCount) {
            recentCount.textContent = `${totalCount} today`;
        }

        if (!tbody) return;

        if (!Array.isArray(txns) || txns.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-14">
                        <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                            <div class="w-12 h-12 rounded-2xl bg-surface-subtle dark:bg-white/5 flex items-center justify-center text-text-muted mb-3 border border-surface-border dark:border-white/10">
                                <span class="material-symbols-outlined text-2xl" aria-hidden="true">receipt_long</span>
                            </div>
                            <div class="text-sm font-bold text-text-main dark:text-white mb-1">No Shift Activity Today</div>
                            <div class="text-xs text-text-muted text-center mb-4 font-medium">No counter deposits or withdrawals have been processed during today's shift yet.</div>
                            <div class="flex items-center gap-2">
                                <a href="staff-transaction.php?type=deposit" class="px-3.5 py-1.5 rounded-xl bg-success-surface dark:bg-success-surface/10 text-success-base text-xs font-bold hover:bg-success-surface/80 border border-success-base/20 transition-all flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm">add</span>
                                    <span>Process Deposit</span>
                                </a>
                                <a href="staff-transaction.php?type=withdraw" class="px-3.5 py-1.5 rounded-xl bg-danger-surface dark:bg-danger-surface/10 text-danger-base text-xs font-bold hover:bg-danger-surface/80 border border-danger-base/20 transition-all flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm">remove</span>
                                    <span>Process Withdrawal</span>
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        txns.forEach((txn) => {
            const rawType = (txn.type || '').toLowerCase();
            const isDeposit = rawType === 'deposit';
            const isWithdrawal = rawType === 'withdrawal' || rawType === 'withdraw';
            const timeStr = formatShiftTime(txn.created_at);
            const amountVal = parseFloat(txn.amount) || 0;
            const amountFormatted = formatINR(amountVal);

            // Customer Name & Initials
            const custName = txn.customer_name || 'Customer';
            const initials = custName
                .split(' ')
                .filter(w => /^[a-zA-Z]/.test(w))
                .map(n => n[0])
                .join('')
                .substring(0, 2)
                .toUpperCase() || 'CU';

            // Ref ID & Masked Account
            const refId = txn.txn_id || txn.reference || '—';
            const accNo = txn.account_no ? escapeHtml(txn.account_no) : '—';

            // Type Badge & Amount Styling
            let typeBadge = '';
            let amountHtml = '';

            if (isDeposit) {
                typeBadge = `
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wider bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        DEPOSIT
                    </span>
                `;
                amountHtml = `<span class="font-mono font-bold text-sm text-success-base dark:text-success-base">+${amountFormatted}</span>`;
            } else if (isWithdrawal) {
                typeBadge = `
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wider bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-500/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                        WITHDRAWAL
                    </span>
                `;
                amountHtml = `<span class="font-mono font-bold text-sm text-danger-base dark:text-danger-base">-${amountFormatted}</span>`;
            } else {
                typeBadge = `
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wider bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20">
                        ${escapeHtml(rawType.toUpperCase())}
                    </span>
                `;
                amountHtml = `<span class="font-mono font-bold text-sm text-text-main dark:text-white">${amountFormatted}</span>`;
            }

            html += `
                <tr class="hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-colors">
                    <td class="p-3 text-xs font-bold text-text-main dark:text-slate-300 whitespace-nowrap">
                        ${timeStr}
                    </td>
                    <td class="p-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-surface-subtle dark:bg-white/10 flex items-center justify-center text-xs font-bold text-text-main dark:text-white shrink-0 border border-surface-border dark:border-white/5">
                                ${escapeHtml(initials)}
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs font-bold text-text-main dark:text-white truncate max-w-[160px] sm:max-w-[200px]">
                                    ${escapeHtml(custName)}
                                </div>
                                <div class="text-[10px] font-mono text-text-muted truncate">
                                    REF: ${escapeHtml(refId)}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="p-3 font-mono text-xs font-medium text-text-muted dark:text-slate-400">
                        ${accNo}
                    </td>
                    <td class="p-3">
                        ${typeBadge}
                    </td>
                    <td class="p-3">
                        ${amountHtml}
                    </td>
                    <td class="p-3 text-right">
                        <a href="staff-receipt.php?id=${encodeURIComponent(refId)}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-text-muted hover:text-warning-base hover:bg-warning-surface dark:hover:bg-warning-surface/10 transition-colors" title="Print/View Receipt" aria-label="View receipt for transaction ${escapeHtml(refId)}">
                            <span class="material-symbols-outlined text-[18px]">print</span>
                        </a>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
    }

    // ── Render: Error State ──
    function renderErrorState(message) {
        const tbody = document.getElementById('txn-tbody');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-10">
                        <div class="flex flex-col items-center justify-center">
                            <span class="material-symbols-outlined text-3xl text-danger-base mb-2">error</span>
                            <div class="text-xs font-bold text-danger-base uppercase tracking-wider">${escapeHtml(message)}</div>
                            <button type="button" onclick="window.location.reload()" class="mt-3 px-3 py-1.5 rounded-lg border border-surface-border text-xs font-bold text-text-main hover:bg-surface-subtle transition-colors">
                                Retry
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }

        const elements = ['stat-deposits', 'stat-withdrawals', 'stat-transactions', 'stat-netflow'];
        elements.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.textContent = '—';
        });
    }

    // ── Actions: Clock In ──
    async function handleClockIn() {
        const btnClockIn = document.getElementById('btn-clock-in');
        if (btnClockIn) {
            btnClockIn.disabled = true;
            btnClockIn.innerHTML = `
                <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                <span>Clocking In...</span>
            `;
        }

        try {
            const res = await window.api.post('/staff-portal/shift/clock-in');
            if (res && res.success) {
                if (typeof showToast === 'function') {
                    showToast('Shift started successfully. Have a productive shift!', 'success');
                }
                await loadDashboardData();
            } else {
                const errMsg = res ? res.message : 'Unable to clock in.';
                if (typeof showToast === 'function') {
                    showToast(errMsg, 'error');
                }
            }
        } catch (error) {
            console.error('Clock in error:', error);
            if (typeof showToast === 'function') {
                showToast('Network error while clocking in.', 'error');
            }
        } finally {
            if (btnClockIn) {
                btnClockIn.disabled = false;
                btnClockIn.innerHTML = `
                    <span class="material-symbols-outlined text-base" aria-hidden="true">login</span>
                    <span>Clock In Shift</span>
                `;
            }
        }
    }

    // ── Actions: Clock Out Modal ──
    function openClockOutModal() {
        const modal = document.getElementById('clockout-modal');
        const modalClockIn = document.getElementById('modal-clockin-time');
        const modalTxns = document.getElementById('modal-shift-txns');
        const modalNet = document.getElementById('modal-shift-net');
        const notes = document.getElementById('shift-notes');
        const errEl = document.getElementById('clockout-error');

        if (errEl) {
            errEl.textContent = '';
            errEl.classList.add('hidden');
        }
        if (notes) notes.value = '';

        if (modalClockIn) {
            modalClockIn.textContent = currentShiftData && currentShiftData.clock_in ? formatShiftTime(currentShiftData.clock_in) : 'Today';
        }
        if (modalTxns) {
            modalTxns.textContent = currentStatsData ? (currentStatsData.total_transactions || 0) : '0';
        }
        if (modalNet) {
            const net = currentStatsData ? (parseFloat(currentStatsData.net_cash_flow) || 0) : 0;
            const sign = net > 0 ? '+' : '';
            modalNet.textContent = sign + formatINR(net);
        }

        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }
    window.openClockOutModal = openClockOutModal;

    function closeClockOutModal() {
        const modal = document.getElementById('clockout-modal');
        if (modal) {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }
    }

    async function handleClockOutSubmit(event) {
        event.preventDefault();

        const submitBtn = document.getElementById('btn-submit-clockout');
        const cancelBtn = document.getElementById('btn-cancel-clockout');
        const notesInput = document.getElementById('shift-notes');
        const errEl = document.getElementById('clockout-error');

        if (errEl) {
            errEl.textContent = '';
            errEl.classList.add('hidden');
        }

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                <span>Ending Shift...</span>
            `;
        }
        if (cancelBtn) cancelBtn.disabled = true;

        const notes = notesInput ? notesInput.value.trim() : '';

        try {
            const res = await window.api.post('/staff-portal/shift/clock-out', { notes });

            if (res && res.success) {
                closeClockOutModal();
                if (typeof showToast === 'function') {
                    showToast('Shift concluded successfully. Great work today!', 'success');
                }
                await loadDashboardData();
            } else {
                const msg = res ? res.message : 'Unable to clock out.';
                if (errEl) {
                    errEl.textContent = msg;
                    errEl.classList.remove('hidden');
                } else if (typeof showToast === 'function') {
                    showToast(msg, 'error');
                }
            }
        } catch (error) {
            console.error('Clock out error:', error);
            if (errEl) {
                errEl.textContent = 'A network error occurred while clocking out.';
                errEl.classList.remove('hidden');
            }
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = `
                    <span class="material-symbols-outlined text-base" aria-hidden="true">check_circle</span>
                    <span>Confirm Clock Out</span>
                `;
            }
            if (cancelBtn) cancelBtn.disabled = false;
        }
    }

    // Expose for debugging if needed
    window.FinzoStaffDashboard = {
        reload: loadDashboardData,
        clockIn: handleClockIn,
        openClockOut: openClockOutModal
    };
})();
