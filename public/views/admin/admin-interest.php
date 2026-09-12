<?php
$portal = 'admin';
$pageTitle = 'Interest Rates';
$pageHeader = '
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-headline font-black text-text-primary tracking-tight">Interest Rates</h1>
        <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-1">Configure system-wide interest rates and policies</p>
    </div>
</div>
';
ob_start();
?>
<div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                <!-- LEFT: LIVE RATE & UPDATE -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Live Rate Card -->
                    <div class="ds-card p-8 relative overflow-hidden">
                        <div class="flex justify-between items-center mb-6">
                            <span class="text-[10px] font-black text-text-secondary tracking-widest uppercase">Live Savings Rate</span>
                            <div class="flex items-center gap-2 bg-success-bg px-3 py-1 rounded-full border border-success-bg">
                                <div class="w-1.5 h-1.5 rounded-full bg-success animate-pulse"></div>
                                <span class="text-[10px] font-black text-success uppercase">Active</span>
                            </div>
                        </div>
                        <div class="flex items-baseline gap-3 mb-8">
                            <span id="live-rate" class="text-7xl font-black font-headline tracking-tighter text-text-primary">0.00</span>
                            <span class="text-3xl font-bold text-text-primary">%</span>
                            <span class="text-xs font-bold text-text-secondary uppercase ml-2">p.a.</span>
                        </div>
                        <div class="pt-6 border-t border-border-subtle flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-surface-hover flex items-center justify-center text-text-secondary">
                                <span class="material-symbols-outlined" aria-hidden="true">history</span>
                            </div>
                            <div>
                                <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-0.5">Last Rate Change</div>
                                <div id="last-change-date" class="text-sm font-bold text-text-primary">-</div>
                            </div>
                        </div>
                        <div class="absolute -top-12 -right-12 w-40 h-40 bg-primary/5 rounded-full blur-3xl"></div>
                    </div>

                    <!-- Update Form Card -->
                    <div class="ds-card p-8">
                        <h3 class="text-lg font-black text-text-primary mb-6 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary" aria-hidden="true">edit_square</span>
                            Update Rates
                        </h3>
                        <div class="space-y-6">
                            <div>
                                <label for="new-rate" class="fin-label mb-3 block text-[10px] font-black text-text-secondary uppercase">New Interest Rate (%)</label>
                                <div class="relative">
                                    <input type="number" id="new-rate" step="0.01" class="w-full px-6 py-4 bg-surface-hover border-none rounded-2xl focus:ring-2 focus:ring-primary/10 font-black text-2xl font-headline tracking-widest text-text-primary [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-inner-spin-button]:m-0 [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-outer-spin-button]:m-0 [appearance:textfield]">
                                    <span class="absolute right-6 top-1/2 -translate-y-1/2 text-xl font-bold text-text-secondary pointer-events-none">%</span>
                                </div>
                            </div>
                            <div>
                                <label for="lock_period_input" class="fin-label mb-3 block text-[10px] font-black text-text-secondary uppercase">Apply From</label>
                                <div class="finzo-dropdown w-full" id="apply-from-dropdown">
                                    <input type="hidden" id="lock_period_input" value="Immediately">
                                    <div class="finzo-dropdown-trigger fin-input !flex items-center justify-between cursor-pointer">
                                        <span class="dropdown-label font-bold text-sm text-text-primary">Immediately</span>
                                        <span class="material-symbols-outlined chevron transition-transform duration-300 text-text-secondary" aria-hidden="true">expand_more</span>
                                    </div>
                                    <div class="finzo-dropdown-menu w-full z-50">
                                        <div class="finzo-dropdown-option is-selected" data-value="Immediately">Immediately</div>
                                        <div class="finzo-dropdown-option" data-value="Next Month">Next Month (1st Date)</div>
                                        <div class="finzo-dropdown-option" data-value="Custom Date">Custom Date</div>
                                    </div>
                                </div>

                                <div id="custom-date-container" class="hidden mt-4 animate-fade-in">
                                    <label for="custom-apply-date" class="fin-label mb-2 block text-[10px] font-black text-text-secondary uppercase">Effective Date</label>
                                    <input type="date" id="custom-apply-date" class="fin-input">
                                </div>
                            </div>
                            <button type="button" id="apply-rate-btn" onclick="window.InterestManagement.applyRate()" class="btn btn-primary w-full justify-center !py-4 rounded-xl cursor-pointer">
                                <span class="material-symbols-outlined text-lg mr-2" aria-hidden="true">check_circle</span>
                                Apply Rate Update
                            </button>
                            <p class="text-[10px] text-center text-text-secondary font-bold uppercase tracking-widest leading-relaxed">Updating rates will trigger notifications to all affected savings account holders.</p>
                        </div>
                    </div>
                </div>

                <!-- RIGHT: HISTORY & ANALYTICS -->
                <div class="lg:col-span-3 space-y-6">
                    <!-- Stats Grid -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="ds-card p-6">
                            <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-4">Total Interest Credited</div>
                            <div id="stat-total-interest" class="text-3xl font-black font-headline text-text-primary mb-2">₹0.00</div>
                            <div id="stat-interest-delta" class="text-[10px] font-black text-success flex items-center gap-1.5 uppercase tracking-widest">
                                <span class="material-symbols-outlined text-sm" aria-hidden="true">trending_up</span>
                                0% from last month
                            </div>
                        </div>
                        <div class="ds-card p-6">
                            <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-4">Earning Accounts</div>
                            <div class="flex items-baseline gap-2 mb-2">
                                <span id="stat-earning-accounts" class="text-3xl font-black font-headline text-text-primary">0</span>
                                <span class="text-[10px] font-black text-primary bg-primary-50 border border-primary-100 px-2 py-0.5 rounded uppercase">Savings</span>
                            </div>
                            <div class="text-[10px] font-bold text-text-secondary uppercase tracking-widest">Auto-credited every 30 days</div>
                        </div>
                    </div>

                    <!-- Interest Chart -->
                    <div class="ds-card p-8">
                        <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-8">Credited Interest (12 Mo)</div>
                        <div id="interest-chart-container" class="h-44 flex items-stretch gap-2 px-2 pt-4">
                            <!-- Bars injected by JS -->
                        </div>
                        <div class="flex justify-between mt-4 text-[10px] font-black text-text-secondary uppercase tracking-widest">
                            <span id="chart-date-1">JAN</span>
                            <span id="chart-date-2">JUN</span>
                            <span id="chart-date-3">DEC</span>
                        </div>
                    </div>

                    <!-- History Table -->
                    <div class="ds-card overflow-hidden">
                        <div class="p-6 border-b border-border-subtle">
                            <h3 class="text-sm font-black text-text-primary uppercase tracking-widest">Rate Change History</h3>
                        </div>
                        <div class="ds-table-wrapper border-0 rounded-none shadow-none bg-transparent">
                            <table class="ds-table w-full text-left min-w-auto">
                                <thead scope="col">
                                    <tr>
                                        <th scope="col">Date</th>
                                        <th scope="col">Rate</th>
                                        <th scope="col">Updated By</th>
                                        <th class="text-right" scope="col">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="interest-history-body">
                                    <!-- History items injected by JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CONFIRMATION MODAL -->
            <div id="confirm-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden" role="dialog" aria-modal="true">
                <div class="absolute inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-sm" onclick="window.InterestManagement.closeModal()"></div>
                <div class="ds-card w-full max-w-md shadow-2xl overflow-hidden scale-95 transition-all duration-300 border border-border-subtle relative z-10 bg-surface">
                    <div class="p-8 text-center text-text-primary">
                        <div class="w-20 h-20 rounded-full bg-primary-50 flex items-center justify-center mx-auto mb-6 text-primary border border-primary/20">
                            <span class="material-symbols-outlined text-4xl" aria-hidden="true">percent</span>
                        </div>
                        <h3 class="text-xl font-black font-headline mb-3 text-text-primary">Confirm Rate Adjustment</h3>
                        <div id="modal-msg" class="text-sm text-text-secondary leading-relaxed mb-6">
                            Are you sure you want to adjust the global savings interest rate?
                        </div>
                        
                        <div class="p-3 bg-primary-50/50 dark:bg-primary-900/10 rounded-xl border border-primary/20 mb-6 text-left">
                            <div class="flex items-start gap-2.5">
                                <span class="material-symbols-outlined text-primary text-base mt-0.5" aria-hidden="true">info</span>
                                <p class="text-[11px] text-text-secondary font-medium leading-normal">
                                    Adjusting this policy affects all active savings accounts and is recorded in audit logs.
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <button type="button" onclick="window.InterestManagement.closeModal()" class="flex-1 btn btn-secondary py-3 justify-center text-xs font-bold uppercase tracking-wider cursor-pointer">
                                Cancel
                            </button>
                            <button type="button" id="confirm-rate-btn" onclick="window.InterestManagement.confirmRate()" class="flex-1 btn btn-primary py-3 justify-center text-xs font-bold uppercase tracking-wider cursor-pointer">
                                Confirm &amp; Apply
                            </button>
                        </div>
                    </div>
                </div>
            </div>
<?php
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/interest.js?v=' . time() . '"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
