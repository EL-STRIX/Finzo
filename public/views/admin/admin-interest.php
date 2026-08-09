<?php
$portal = 'admin';
$pageTitle = 'Interest Rates';
$pageHeader = '
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-headline font-black text-text-main dark:text-white tracking-tight">Interest Rates</h1>
        <p class="text-xs font-bold text-text-muted uppercase tracking-widest mt-0.5">Configure system-wide interest rates and policies</p>
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
                            <span class="text-xs font-black text-text-muted tracking-widest uppercase">Live Savings Rate</span>
                            <div class="flex items-center gap-2 bg-success-surface/10 px-3 py-1 rounded-full border border-success-base/20">
                                <div class="w-1.5 h-1.5 rounded-full bg-success-surface animate-pulse"></div>
                                <span class="text-xs font-black text-success-base uppercase">Active</span>
                            </div>
                        </div>
                        <div class="flex items-baseline gap-3 mb-8">
                            <span id="live-rate" class="text-7xl font-black font-headline tracking-tighter text-primary-base dark:text-text-muted">0.00</span>
                            <span class="text-3xl font-bold text-primary-base dark:text-text-muted">%</span>
                            <span class="text-xs font-bold opacity-30 uppercase ml-2">p.a.</span>
                        </div>
                        <div class="pt-6 border-t border-surface-border dark:border-white/5 flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-surface-hover dark:bg-white/5 flex items-center justify-center text-text-muted">
                                <span class="material-symbols-outlined" aria-hidden="true">history</span>
                            </div>
                            <div>
                                <div class="text-xs font-black text-text-muted uppercase tracking-widest mb-0.5">Last Rate Change</div>
                                <div id="last-change-date" class="text-sm font-bold dark:text-white">-</div>
                            </div>
                        </div>
                        <div class="absolute -top-12 -right-12 w-40 h-40 bg-primary/5 rounded-full blur-3xl"></div>
                    </div>

                    <!-- Update Form Card -->
                    <div class="ds-card p-8">
                        <h3 class="text-lg font-bold mb-6 flex items-center gap-2 dark:text-white">
                            <span class="material-symbols-outlined text-primary-base dark:text-primary-base" aria-hidden="true">edit_square</span>
                            Update Rates
                        </h3>
                        <div class="space-y-6">
                            <div>
                                <label for="new-rate" class="fin-label mb-3 block">New Interest Rate (%)</label>
                                <div class="relative">
                                    <input type="number" id="new-rate" step="0.01" class="w-full px-6 py-4 bg-surface-hover dark:bg-white/5 border-none rounded-2xl focus:ring-2 focus:ring-primary/10 font-black text-2xl font-headline tracking-widest text-primary-base dark:text-text-muted [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-inner-spin-button]:m-0 [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-outer-spin-button]:m-0 [appearance:textfield]">
                                    <span class="absolute right-6 top-1/2 -translate-y-1/2 text-xl font-bold opacity-30 pointer-events-none">%</span>
                                </div>
                            </div>
                            <div>
                                <label for="lock_period_input" class="fin-label mb-3 block">Apply From</label>
                                <div class="finzo-dropdown" id="apply-from-dropdown">
                                    <input type="hidden" id="lock_period_input" value="Immediately">
                                    <div class="finzo-dropdown-trigger !bg-surface-hover dark:!bg-white/5 !border-none !py-4 !rounded-2xl">
                                        <span class="dropdown-label font-bold text-sm">Immediately</span>
                                        <span class="material-symbols-outlined chevron transition-transform duration-300" aria-hidden="true">expand_more</span>
                                    </div>
                                    <div class="finzo-dropdown-menu !rounded-2xl !border-none !shadow-2xl">
                                        <div class="finzo-dropdown-option is-selected" data-value="Immediately">Immediately</div>
                                        <div class="finzo-dropdown-option" data-value="Next Month">Next Month (1st Date)</div>
                                        <div class="finzo-dropdown-option" data-value="Custom Date">Custom Date</div>
                                    </div>
                                </div>

                                <div id="custom-date-container" class="hidden mt-4 animate-fade-in">
                                    <label for="custom-apply-date" class="fin-label mb-2 block">Effective Date</label>
                                    <input type="date" id="custom-apply-date" class="w-full px-4 py-3 bg-surface-hover dark:bg-white/5 border-none rounded-xl focus:ring-2 focus:ring-primary/10 font-bold text-sm">
                                </div>
                            </div>
                            <button data-action="window.InterestManagement.applyRate()" class="btn btn-primary w-full justify-center !py-4 rounded-2xl shadow-xl shadow-primary-base/20">Apply Rate Update</button>
                            <p class="text-xs text-center opacity-40 leading-relaxed font-medium">Updating rates will trigger notifications to all affected savings account holders.</p>
                        </div>
                    </div>
                </div>

                <!-- RIGHT: HISTORY & ANALYTICS -->
                <div class="lg:col-span-3 space-y-6">
                    <!-- Stats Grid -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="ds-card p-6">
                            <div class="text-xs font-black text-text-muted uppercase tracking-widest mb-4">Total Interest Credited</div>
                            <div id="stat-total-interest" class="text-3xl font-black font-headline text-primary-base dark:text-text-muted mb-2">?0.00</div>
                            <div id="stat-interest-delta" class="text-xs font-bold text-success-base flex items-center gap-1.5 uppercase">
                                <span class="material-symbols-outlined text-sm" aria-hidden="true">trending_up</span>
                                0% from last month
                            </div>
                        </div>
                        <div class="ds-card p-6">
                            <div class="text-xs font-black text-text-muted uppercase tracking-widest mb-4">Earning Accounts</div>
                            <div class="flex items-baseline gap-2 mb-2">
                                <span id="stat-earning-accounts" class="text-3xl font-black font-headline text-primary-base dark:text-text-muted">0</span>
                                <span class="text-xs font-black text-primary-base bg-primary-surface/10 px-2 py-0.5 rounded uppercase">Savings</span>
                            </div>
                            <div class="text-xs font-bold opacity-40 uppercase tracking-widest">Auto-credited every 30 days</div>
                        </div>
                    </div>

                    <!-- Interest Chart -->
                    <div class="ds-card p-8">
                        <div class="text-xs font-black text-text-muted uppercase tracking-widest mb-8">Credited Interest (12 Mo)</div>
                        <div id="interest-chart-container" class="h-40 flex items-end gap-2 px-2">
                            <!-- Bars injected by JS -->
                        </div>
                        <div class="flex justify-between mt-4 text-xs font-black text-text-muted uppercase tracking-widest">
                            <span id="chart-date-1">JAN</span>
                            <span id="chart-date-2">JUN</span>
                            <span id="chart-date-3">DEC</span>
                        </div>
                    </div>

                    <!-- History Table -->
                    <div class="ds-card overflow-hidden">
                        <div class="p-6 border-b border-surface-border dark:border-white/5">
                            <h3 class="text-sm font-bold dark:text-white uppercase tracking-widest">Rate Change History</h3>
                        </div>
                        <div class="overflow-x-auto">
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
<?php
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/interest.js"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
