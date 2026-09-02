/**
 * Finzo Admin — Advanced Reporting Engine
 * Version 2.3 — Reliable Event Delegation, Flexible Date Filtering, Timezone-Safe Formatting, and Instant CSV Streaming
 */

(function() {
    'use strict';

    /**
     * Unified Toast Dispatcher
     */
    function toast(msg, type = 'info') {
        if (typeof window.showToast === 'function') {
            window.showToast(msg, type);
        } else if (window.FinzoUtils && typeof window.FinzoUtils.showToast === 'function') {
            window.FinzoUtils.showToast(msg, type);
        } else if (window.portal && typeof window.portal.showToast === 'function') {
            window.portal.showToast(msg, type);
        } else {
            alert(msg);
        }
    }

    /**
     * Format INR Currency
     */
    function fmtINR(val) {
        const num = parseFloat(val) || 0;
        return '₹' + num.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    /**
     * Timezone-safe local date string (YYYY-MM-DD)
     */
    function formatLocalDate(d) {
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    /**
     * Collect form parameters for a given report type
     */
    function getReportParams(type) {
        const form = document.getElementById(`form-${type}-report`);
        if (!form) return null;

        const dateFromInput = form.querySelector('input[name="date_from"]');
        const dateToInput = form.querySelector('input[name="date_to"]');

        const dateFrom = dateFromInput ? dateFromInput.value.trim() : '';
        const dateTo = dateToInput ? dateToInput.value.trim() : '';

        if (dateFrom && dateTo && dateFrom > dateTo) {
            toast('Date From cannot be after Date To', 'warning');
            return null;
        }

        const params = {};
        if (dateFrom) params.date_from = dateFrom;
        if (dateTo) params.date_to = dateTo;

        if (type === 'financial') {
            const branchVal = document.getElementById('financial-branch-input')?.value || '';
            if (branchVal) params.branch_id = branchVal;
        } else if (type === 'demographics') {
            const statusVal = document.getElementById('demographics-status-input')?.value || '';
            if (statusVal) params.status = statusVal;
        }

        return params;
    }

    /**
     * Handle Synchronous Report Generation
     */
    async function handleGenerate(type) {
        if (!type) return;

        const form = document.getElementById(`form-${type}-report`);
        if (!form) return;

        if (!window.api || typeof window.api.get !== 'function') {
            toast('API module not loaded. Please refresh the page.', 'error');
            return;
        }

        const params = getReportParams(type);
        if (params === null) return; // Validation failed

        const queryParams = new URLSearchParams(params).toString();
        const submitBtn = form.querySelector('.btn-generate');
        const exportBtn = form.querySelector('.btn-export-csv');

        const originalSubmitHtml = submitBtn ? submitBtn.innerHTML : '';
        if (submitBtn) {
            submitBtn.innerHTML = '<span class="material-symbols-outlined text-[18px] animate-spin">refresh</span><span>Analyzing...</span>';
            submitBtn.disabled = true;
        }
        if (exportBtn) exportBtn.disabled = true;

        try {
            const res = await window.api.get(`/admin/reports/${type}?${queryParams}`);

            // Unwrap: service returns {success, report, data} at top level
            const data = (res && res.data) ? res.data : res;

            if (res && res.success === true && data) {
                if (type === 'financial') {
                    renderFinancialResults(data);
                } else {
                    renderDemographicsResults(data);
                }
                const label = type === 'financial' ? 'Financial' : 'Demographics';
                toast(`${label} report generated successfully`, 'success');
            } else {
                toast(res?.message || 'Failed to generate report. Please try again.', 'error');
            }
        } catch (error) {
            console.error('Report generation error:', error);
            toast('Server error while generating report', 'error');
        } finally {
            if (submitBtn) {
                submitBtn.innerHTML = originalSubmitHtml;
                submitBtn.disabled = false;
            }
            if (exportBtn) exportBtn.disabled = false;
        }
    }

    /**
     * Handle Direct CSV Export Download
     */
    async function handleExport(type) {
        if (!type) return;

        const form = document.getElementById(`form-${type}-report`);
        if (!form) return;

        if (!window.api || typeof window.api.get !== 'function') {
            toast('API module not loaded. Please refresh the page.', 'error');
            return;
        }

        const params = getReportParams(type);
        if (params === null) return;

        const queryParams = new URLSearchParams(params).toString();
        const exportBtn = form.querySelector('.btn-export-csv');
        const originalHtml = exportBtn ? exportBtn.innerHTML : '';

        if (exportBtn) {
            exportBtn.innerHTML = '<span class="material-symbols-outlined text-[18px] animate-spin">refresh</span><span>Preparing...</span>';
            exportBtn.disabled = true;
        }

        toast('Preparing CSV export — downloading shortly...', 'info');

        try {
            // api.js blob handler fires automatically for text/csv content-type
            await window.api.get(`/admin/reports/${type}/export?${queryParams}`);
        } catch (err) {
            console.error('Export error:', err);
            toast('Failed to export CSV. Please try again.', 'error');
        } finally {
            if (exportBtn) {
                exportBtn.innerHTML = originalHtml;
                exportBtn.disabled = false;
            }
        }
    }

    /**
     * Render Financial Report Metrics
     */
    function renderFinancialResults(data) {
        const container = document.getElementById('financial-result');
        const metricsGrid = document.getElementById('financial-metrics');
        if (!container || !metricsGrid) return;

        container.classList.remove('hidden');

        const netFlow = parseFloat(data.net_cash_flow || 0);
        const netColor = netFlow >= 0 ? 'text-emerald-500' : 'text-error';

        metricsGrid.innerHTML = `
            <div class="p-4 rounded-xl border border-border-subtle bg-surface flex flex-col justify-between">
                <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1 flex items-center justify-between">
                    <span>Deposits</span>
                    <span class="px-1.5 py-0.5 rounded bg-emerald-500/10 text-emerald-500 text-[9px] font-bold">${data.deposit_count || 0} Txns</span>
                </div>
                <div class="font-headline text-lg font-black text-emerald-500">${fmtINR(data.total_deposits || 0)}</div>
            </div>

            <div class="p-4 rounded-xl border border-border-subtle bg-surface flex flex-col justify-between">
                <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1 flex items-center justify-between">
                    <span>Withdrawals</span>
                    <span class="px-1.5 py-0.5 rounded bg-error/10 text-error text-[9px] font-bold">${data.withdrawal_count || 0} Txns</span>
                </div>
                <div class="font-headline text-lg font-black text-error">${fmtINR(data.total_withdrawals || 0)}</div>
            </div>

            <div class="p-4 rounded-xl border border-border-subtle bg-surface flex flex-col justify-between">
                <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1 flex items-center justify-between">
                    <span>Net Cash Flow</span>
                    <span class="material-symbols-outlined text-[14px] ${netColor}">${netFlow >= 0 ? 'trending_up' : 'trending_down'}</span>
                </div>
                <div class="font-headline text-lg font-black ${netColor}">${fmtINR(netFlow)}</div>
            </div>

            <div class="p-4 rounded-xl border border-border-subtle bg-surface flex flex-col justify-between">
                <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1 flex items-center justify-between">
                    <span>Transfers</span>
                    <span class="px-1.5 py-0.5 rounded bg-info/10 text-info text-[9px] font-bold">${data.transfer_count || 0} Txns</span>
                </div>
                <div class="font-headline text-lg font-black text-text-primary">${fmtINR(data.total_transfers || 0)}</div>
            </div>

            <div class="p-4 rounded-xl border border-border-subtle bg-surface flex flex-col justify-between">
                <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1 flex items-center justify-between">
                    <span>Interest Paid</span>
                    <span class="px-1.5 py-0.5 rounded bg-warning/10 text-warning text-[9px] font-bold">${data.interest_count || 0} Txns</span>
                </div>
                <div class="font-headline text-lg font-black text-warning">${fmtINR(data.total_interest_paid || 0)}</div>
            </div>

            <div class="p-4 rounded-xl border border-border-subtle bg-surface flex flex-col justify-between">
                <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1">Total Activity</div>
                <div class="font-headline text-lg font-black text-primary font-mono">${data.total_transactions || 0} Txns</div>
            </div>
        `;

        // Smooth scroll to results for visual confirmation
        container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    /**
     * Render Demographics & KYC Breakdown
     */
    function renderDemographicsResults(data) {
        const container = document.getElementById('demographics-result');
        const metricsContainer = document.getElementById('demographics-metrics');
        if (!container || !metricsContainer) return;

        container.classList.remove('hidden');

        const userStatuses = data.user_statuses || {};
        const accountTypes = data.account_types || {};
        const kycStatuses = data.kyc_statuses || {};
        const branchDist = data.branch_distribution || {};

        let branchHtml = '';
        Object.keys(branchDist).forEach(branch => {
            branchHtml += `
                <div class="flex items-center justify-between text-xs py-1 border-b border-border-subtle last:border-0">
                    <span class="font-bold text-text-secondary">${branch}</span>
                    <span class="font-mono font-black text-text-primary">${branchDist[branch]} Customers</span>
                </div>
            `;
        });
        if (branchHtml === '') {
            branchHtml = '<div class="text-xs text-text-secondary py-1 italic">No branch distribution records found for this period</div>';
        }

        metricsContainer.innerHTML = `
            <!-- Top Summary Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="p-3 rounded-xl border border-border-subtle bg-surface text-center">
                    <div class="text-[9px] font-black text-text-secondary uppercase tracking-wider mb-1">Total Users</div>
                    <div class="font-headline text-xl font-black text-text-primary font-mono">${data.total_users || 0}</div>
                </div>
                <div class="p-3 rounded-xl border border-border-subtle bg-surface text-center">
                    <div class="text-[9px] font-black text-text-secondary uppercase tracking-wider mb-1">Active Accounts</div>
                    <div class="font-headline text-xl font-black text-emerald-500 font-mono">${userStatuses.active || 0}</div>
                </div>
                <div class="p-3 rounded-xl border border-border-subtle bg-surface text-center">
                    <div class="text-[9px] font-black text-text-secondary uppercase tracking-wider mb-1">Savings Accounts</div>
                    <div class="font-headline text-xl font-black text-primary font-mono">${accountTypes.savings || 0}</div>
                </div>
                <div class="p-3 rounded-xl border border-border-subtle bg-surface text-center">
                    <div class="text-[9px] font-black text-text-secondary uppercase tracking-wider mb-1">Current Accounts</div>
                    <div class="font-headline text-xl font-black text-info font-mono">${accountTypes.current || 0}</div>
                </div>
            </div>

            <!-- KYC Verification Breakdown -->
            <div class="p-4 rounded-xl border border-border-subtle bg-surface">
                <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-3">KYC Verification Funnel</div>
                <div class="grid grid-cols-3 sm:grid-cols-5 gap-2 text-center text-xs">
                    <div class="p-2 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                        <div class="text-[9px] font-bold text-emerald-600 dark:text-emerald-400 uppercase">Approved</div>
                        <div class="font-black text-emerald-600 dark:text-emerald-400 font-mono text-base">${kycStatuses.approved || 0}</div>
                    </div>
                    <div class="p-2 rounded-lg bg-amber-500/10 border border-amber-500/20">
                        <div class="text-[9px] font-bold text-amber-600 dark:text-amber-400 uppercase">Under Review</div>
                        <div class="font-black text-amber-600 dark:text-amber-400 font-mono text-base">${kycStatuses.under_review || 0}</div>
                    </div>
                    <div class="p-2 rounded-lg bg-blue-500/10 border border-blue-500/20">
                        <div class="text-[9px] font-bold text-blue-600 dark:text-blue-400 uppercase">Submitted</div>
                        <div class="font-black text-blue-600 dark:text-blue-400 font-mono text-base">${kycStatuses.submitted || 0}</div>
                    </div>
                    <div class="p-2 rounded-lg bg-purple-500/10 border border-purple-500/20">
                        <div class="text-[9px] font-bold text-purple-600 dark:text-purple-400 uppercase">Draft</div>
                        <div class="font-black text-purple-600 dark:text-purple-400 font-mono text-base">${kycStatuses.draft || 0}</div>
                    </div>
                    <div class="p-2 rounded-lg bg-rose-500/10 border border-rose-500/20">
                        <div class="text-[9px] font-bold text-rose-600 dark:text-rose-400 uppercase">Rejected</div>
                        <div class="font-black text-rose-600 dark:text-rose-400 font-mono text-base">${kycStatuses.rejected || 0}</div>
                    </div>
                </div>
            </div>

            <!-- Branch Allocation Table -->
            <div class="p-4 rounded-xl border border-border-subtle bg-surface">
                <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-2">Customer Branch Allocation (Top Active)</div>
                <div class="space-y-1">
                    ${branchHtml}
                </div>
            </div>
        `;

        // Smooth scroll to results for visual confirmation
        container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    /**
     * Initialization on DOM ready
     */
    function init() {
        const today = new Date();

        // Financial defaults: Start of current month to end of current month
        const financialFrom = formatLocalDate(new Date(today.getFullYear(), today.getMonth(), 1));
        const financialTo = formatLocalDate(new Date(today.getFullYear(), today.getMonth() + 1, 0));

        const formFinancial = document.getElementById('form-financial-report');
        if (formFinancial) {
            const fromInput = formFinancial.querySelector('input[name="date_from"]');
            const toInput = formFinancial.querySelector('input[name="date_to"]');
            if (fromInput && !fromInput.value) fromInput.value = financialFrom;
            if (toInput && !toInput.value) toInput.value = financialTo;
        }

        // Demographics defaults: Start of current year to end of current month (covers existing customer census)
        const demoFrom = formatLocalDate(new Date(today.getFullYear(), 0, 1));
        const demoTo = formatLocalDate(new Date(today.getFullYear(), today.getMonth() + 1, 0));

        const formDemo = document.getElementById('form-demographics-report');
        if (formDemo) {
            const fromInput = formDemo.querySelector('input[name="date_from"]');
            const toInput = formDemo.querySelector('input[name="date_to"]');
            if (fromInput && !fromInput.value) fromInput.value = demoFrom;
            if (toInput && !toInput.value) toInput.value = demoTo;
        }

        // Direct ID Event Listeners
        const btnGenFinancial = document.getElementById('btn-generate-financial');
        if (btnGenFinancial) {
            btnGenFinancial.addEventListener('click', () => handleGenerate('financial'));
        }

        const btnExportFinancial = document.getElementById('btn-export-financial');
        if (btnExportFinancial) {
            btnExportFinancial.addEventListener('click', () => handleExport('financial'));
        }

        const btnGenDemo = document.getElementById('btn-generate-demographics');
        if (btnGenDemo) {
            btnGenDemo.addEventListener('click', () => handleGenerate('demographics'));
        }

        const btnExportDemo = document.getElementById('btn-export-demographics');
        if (btnExportDemo) {
            btnExportDemo.addEventListener('click', () => handleExport('demographics'));
        }

        // Delegated Click Listener as universal fallback
        document.addEventListener('click', (e) => {
            const genBtn = e.target.closest('.btn-generate');
            if (genBtn && genBtn.dataset.type && !genBtn.disabled) {
                if (!genBtn.id) {
                    handleGenerate(genBtn.dataset.type);
                }
            }

            const exportBtn = e.target.closest('.btn-export-csv');
            if (exportBtn && exportBtn.dataset.type && !exportBtn.disabled) {
                if (!exportBtn.id) {
                    handleExport(exportBtn.dataset.type);
                }
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
