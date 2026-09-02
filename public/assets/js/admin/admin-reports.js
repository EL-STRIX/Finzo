/**
 * Finzo Admin — Advanced Reporting Engine
 * Version 2.1 — Custom Animated Dropdowns, Registered Branches, Financial & Demographic Visualizations, and Instant CSV Streaming
 */

(function() {
    
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
     * Initialize Custom Dropdowns with Smooth Animations
     */
    function initCustomDropdowns() {
        document.querySelectorAll('.finzo-dropdown').forEach(dropdown => {
            const trigger = dropdown.querySelector('.finzo-dropdown-trigger');
            const menu = dropdown.querySelector('.finzo-dropdown-menu');
            const label = dropdown.querySelector('.dropdown-label');
            const hiddenInput = dropdown.querySelector('input[type="hidden"]');
            const chevron = dropdown.querySelector('.chevron');
            const options = dropdown.querySelectorAll('.finzo-dropdown-option');

            if (!trigger || !menu) return;

            // Toggle Open/Close
            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                const isOpen = menu.classList.contains('is-open') || dropdown.classList.contains('is-open');

                // Close all other dropdowns
                document.querySelectorAll('.finzo-dropdown.is-open, .finzo-dropdown-menu.is-open').forEach(el => {
                    if (el !== dropdown && el !== menu) {
                        el.classList.remove('is-open');
                        const otherChevron = el.querySelector?.('.chevron') || el.closest?.('.finzo-dropdown')?.querySelector('.chevron');
                        if (otherChevron) otherChevron.style.transform = 'rotate(0deg)';
                    }
                });

                if (isOpen) {
                    dropdown.classList.remove('is-open');
                    menu.classList.remove('is-open');
                    if (chevron) chevron.style.transform = 'rotate(0deg)';
                } else {
                    dropdown.classList.add('is-open');
                    menu.classList.add('is-open');
                    if (chevron) chevron.style.transform = 'rotate(180deg)';
                }
            });

            // Option selection
            options.forEach(option => {
                option.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const val = option.getAttribute('data-value') || '';
                    const text = option.textContent.trim();

                    if (hiddenInput) {
                        hiddenInput.value = val;
                        hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                    if (label) label.textContent = text;

                    options.forEach(o => o.classList.remove('is-selected'));
                    option.classList.add('is-selected');

                    dropdown.classList.remove('is-open');
                    menu.classList.remove('is-open');
                    if (chevron) chevron.style.transform = 'rotate(0deg)';
                });
            });
        });

        // Click outside listener
        document.addEventListener('click', () => {
            document.querySelectorAll('.finzo-dropdown.is-open, .finzo-dropdown-menu.is-open').forEach(el => {
                el.classList.remove('is-open');
            });
            document.querySelectorAll('.finzo-dropdown .chevron').forEach(ch => {
                ch.style.transform = 'rotate(0deg)';
            });
        });
    }

    /**
     * Handle Synchronous Report Generation
     */
    async function handleGenerate(type) {
        const form = document.getElementById(`form-${type}-report`);
        if (!form) return;

        const dateFromInput = form.querySelector('input[name="date_from"]');
        const dateToInput = form.querySelector('input[name="date_to"]');
        
        if (!dateFromInput.value || !dateToInput.value) {
            toast('Please select both start and end dates', 'warning');
            return;
        }

        const dateFrom = dateFromInput.value;
        const dateTo = dateToInput.value;
        let queryParams = `date_from=${encodeURIComponent(dateFrom)}&date_to=${encodeURIComponent(dateTo)}`;

        if (type === 'financial') {
            const branchVal = document.getElementById('financial-branch-input')?.value || '';
            if (branchVal) queryParams += `&branch_id=${encodeURIComponent(branchVal)}`;
        } else if (type === 'demographics') {
            const statusVal = document.getElementById('demographics-status-input')?.value || '';
            if (statusVal) queryParams += `&status=${encodeURIComponent(statusVal)}`;
        }

        const submitBtn = form.querySelector('.btn-generate');
        const exportBtn = form.querySelector('.btn-export-csv');

        const originalSubmitHtml = submitBtn ? submitBtn.innerHTML : '';
        if (submitBtn) {
            submitBtn.innerHTML = '<span class="material-symbols-outlined text-[18px] animate-spin">refresh</span><span>Analyzing...</span>';
            submitBtn.disabled = true;
        }
        if (exportBtn) exportBtn.disabled = true;

        try {
            const endpoint = `/admin/reports/${type}?${queryParams}`;
            const res = await window.api.get(endpoint);

            if (res && (res.success || res.status === 200 || res.data)) {
                const data = res.data || res;
                if (type === 'financial') {
                    renderFinancialResults(data);
                } else {
                    renderDemographicsResults(data);
                }
                toast(`${type.charAt(0).toUpperCase() + type.slice(1)} report insights generated`, 'success');
            } else {
                toast(res.message || 'Failed to generate report', 'error');
            }
        } catch (error) {
            console.error('Report error:', error);
            toast('Failed to generate report from server', 'error');
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
    function handleExport(type) {
        const form = document.getElementById(`form-${type}-report`);
        if (!form) return;

        const dateFromInput = form.querySelector('input[name="date_from"]');
        const dateToInput = form.querySelector('input[name="date_to"]');
        
        const dateFrom = dateFromInput ? dateFromInput.value : '';
        const dateTo = dateToInput ? dateToInput.value : '';
        let queryParams = `date_from=${encodeURIComponent(dateFrom)}&date_to=${encodeURIComponent(dateTo)}`;

        if (type === 'financial') {
            const branchVal = document.getElementById('financial-branch-input')?.value || '';
            if (branchVal) queryParams += `&branch_id=${encodeURIComponent(branchVal)}`;
        } else if (type === 'demographics') {
            const statusVal = document.getElementById('demographics-status-input')?.value || '';
            if (statusVal) queryParams += `&status=${encodeURIComponent(statusVal)}`;
        }

        const downloadUrl = `/Finzo/api/v1/admin/reports/${type}/export?${queryParams}`;

        toast('Preparing CSV file download...', 'info');
        
        // Trigger browser download
        const iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        iframe.src = downloadUrl;
        document.body.appendChild(iframe);
        setTimeout(() => iframe.remove(), 10000);
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
        if (branchHtml === '') branchHtml = '<div class="text-xs text-text-secondary">No branch distribution records</div>';

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
    }

    /**
     * Initialization on DOM ready
     */
    function init() {
        // Set default dates to current month range
        const today = new Date();
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
        const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0).toISOString().split('T')[0];
        
        document.querySelectorAll('input[type="date"]').forEach(input => {
            if (input.name === 'date_from' && !input.value) input.value = firstDay;
            if (input.name === 'date_to' && !input.value) input.value = lastDay;
        });

        initCustomDropdowns();

        // Bind Generate buttons
        document.querySelectorAll('.btn-generate').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const type = e.currentTarget.dataset.type;
                handleGenerate(type);
            });
        });

        // Bind Export buttons
        document.querySelectorAll('.btn-export-csv').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const type = e.currentTarget.dataset.type;
                handleExport(type);
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
