<?php
$portal = 'staff';
$pageTitle = 'Finzo Staff';
ob_start();
?>
<!-- STATS ROW -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="ds-card p-5 border-l-4 border-l-emerald-500">
                    <div class="flex justify-between items-start mb-2">
                        <div class="text-xs font-black text-text-muted uppercase tracking-widest">Total Deposits Done</div>
                        <span class="material-symbols-outlined text-success-base" aria-hidden="true">add_circle</span>
                    </div>
                    <div class="text-2xl font-black text-success-base dark:text-success-base" id="stat-deposits">₹0</div>
                    <div class="mt-2 text-xs font-bold text-success-base dark:text-success-base bg-success-surface dark:bg-success-surface/10 px-2 py-1 rounded inline-block">Total processed</div>
                </div>
                <div class="ds-card p-5 border-l-4 border-l-red-500">
                    <div class="flex justify-between items-start mb-2">
                        <div class="text-xs font-black text-text-muted uppercase tracking-widest">Total Withdrawals Done</div>
                        <span class="material-symbols-outlined text-danger-base" aria-hidden="true">remove_circle</span>
                    </div>
                    <div class="text-2xl font-black text-danger-base dark:text-danger-base" id="stat-withdrawals">₹0</div>
                    <div class="mt-2 text-xs font-bold text-danger-base dark:text-danger-base bg-danger-surface dark:bg-danger-surface/10 px-2 py-1 rounded inline-block">Total processed</div>
                </div>
                <div class="ds-card p-5 border-l-4 border-l-amber-500">
                    <div class="flex justify-between items-start mb-2">
                        <div class="text-xs font-black text-text-muted uppercase tracking-widest">Total Transactions</div>
                        <span class="material-symbols-outlined text-warning-base" aria-hidden="true">receipt</span>
                    </div>
                    <div class="text-2xl font-black text-text-main dark:text-white" id="stat-transactions">0</div>
                    <div class="mt-2 text-xs font-bold text-warning-base dark:text-warning-base bg-warning-surface dark:bg-warning-surface/10 px-2 py-1 rounded inline-block uppercase">All Time</div>
                </div>
                <div class="ds-card p-5 border-l-4 border-l-blue-500">
                    <div class="flex justify-between items-start mb-2">
                        <div class="text-xs font-black text-text-muted uppercase tracking-widest">This Month</div>
                        <span class="material-symbols-outlined text-primary-base" aria-hidden="true">calendar_month</span>
                    </div>
                    <div class="text-2xl font-black text-text-main dark:text-white" id="stat-month-count">0</div>
                    <div class="mt-2 text-xs font-bold text-info-base dark:text-primary-base bg-primary-surface dark:bg-primary-surface/10 px-2 py-1 rounded inline-block uppercase" id="stat-month-label">Month</div>
                </div>
            </div>

            <!-- FILTER BAR -->
            <div class="ds-card p-4 sm:p-6 space-y-4 relative z-20">
                <!-- Tabs and Print Row -->
                <div class="flex items-center justify-between border-b border-surface-border dark:border-white/5 pb-4">
                    <div class="flex gap-2 overflow-x-auto whitespace-nowrap scrollbar-hide pb-2 sm:pb-0">
                        <button class="time-tab px-4 py-2 bg-warning-surface text-white font-bold rounded-lg text-xs uppercase tracking-widest shadow shadow-amber-500/20">All Time</button>
                        <button class="time-tab px-4 py-2 text-text-muted dark:text-text-muted font-bold hover:text-text-main dark:hover:text-white transition-all text-xs uppercase tracking-widest">Today</button>
                        <button class="time-tab px-4 py-2 text-text-muted dark:text-text-muted font-bold hover:text-text-main dark:hover:text-white transition-all text-xs uppercase tracking-widest">This Week</button>
                        <button class="time-tab px-4 py-2 text-text-muted dark:text-text-muted font-bold hover:text-text-main dark:hover:text-white transition-all text-xs uppercase tracking-widest">This Month</button>
                        <button class="time-tab px-4 py-2 text-text-muted dark:text-text-muted font-bold hover:text-text-main dark:hover:text-white transition-all text-xs uppercase tracking-widest" id="tab-custom">Custom</button>
                    </div>
                    
                    <button id="print-all-btn" data-action="printAllTransactions()" class="flex items-center gap-2 px-4 py-2 ds-btn-secondary text-xs bg-white dark:bg-white/5">
                        <span class="material-symbols-outlined text-lg" aria-hidden="true">print</span>
                        <span class="hidden sm:inline">Print Report</span>
                    </button>
                </div>
                
                <!-- Inputs Row -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2 relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-lg" aria-hidden="true">search</span>
                        <input type="text" id="search-input" placeholder="Search customer name, account no, or ref..." class="w-full ds-input pl-10 h-10 text-sm">
                    </div>
                    
                    <div>
                        <select id="filter-type" class="w-full ds-input h-10 text-sm appearance-none">
                            <option value="All Types">All Types</option>
                            <option value="Deposits">Deposits</option>
                            <option value="Withdrawals">Withdrawals</option>
                        </select>
                    </div>
                    
                    <div>
                        <select id="filter-status" class="w-full ds-input h-10 text-sm appearance-none">
                            <option value="All Status">All Status</option>
                            <option value="Completed">Completed</option>
                            <option value="Pending">Pending</option>
                            <option value="Failed">Failed</option>
                        </select>
                    </div>
                </div>

                <!-- Custom Date Row (Hidden) -->
                <div id="custom-date-row" class="hidden grid grid-cols-1 md:grid-cols-4 gap-4 mt-4 pt-4 border-t border-surface-border dark:border-white/5">
                    <div class="md:col-span-2"></div>
                    <div>
                        <label class="block text-xs font-black text-text-muted uppercase tracking-widest mb-1">From</label>
                        <input type="date" id="filter-from-date" class="w-full ds-input h-10 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-text-muted uppercase tracking-widest mb-1">To</label>
                        <input type="date" id="filter-to-date" class="w-full ds-input h-10 text-sm">
                    </div>
                </div>
            </div>

            <!-- HISTORY TABLE (High Density) -->
            <div class="ds-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="ds-table whitespace-nowrap">
                        <thead scope="col">
                            <tr class="bg-surface-subtle dark:bg-white/5 border-b border-surface-border dark:border-white/5">
                                <th class="p-3 text-xs font-black text-text-muted uppercase tracking-widest w-12 text-center" scope="col">#</th>
                                <th class="p-3 text-xs font-black text-text-muted uppercase tracking-widest" scope="col">Time</th>
                                <th class="p-3 text-xs font-black text-text-muted uppercase tracking-widest" scope="col">Customer / Ref</th>
                                <th class="p-3 text-xs font-black text-text-muted uppercase tracking-widest" scope="col">Account</th>
                                <th class="p-3 text-xs font-black text-text-muted uppercase tracking-widest" scope="col">Type</th>
                                <th class="p-3 text-xs font-black text-text-muted uppercase tracking-widest" scope="col">Amount</th>
                                <th class="p-3 text-xs font-black text-text-muted uppercase tracking-widest" scope="col">Status</th>
                                <th class="p-3 text-xs font-black text-text-muted uppercase tracking-widest text-right" scope="col">Receipt</th>
                            </tr>
                        </thead>
                        <tbody id="transaction-table-body" class="divide-y divide-slate-100 dark:divide-white/5">
                            <tr><td colspan="8" class="text-center py-8 font-bold text-text-muted text-sm">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION -->
                <div class="p-4 bg-surface-subtle/50 dark:bg-white/2 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <span id="showing-meta" class="text-xs font-bold text-text-muted uppercase tracking-widest">Showing 0-0 of 0</span>
                    <div class="flex items-center gap-1" id="page-buttons">
                        <!-- Populated by JS -->
                    </div>
                </div>
            </div>
<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>