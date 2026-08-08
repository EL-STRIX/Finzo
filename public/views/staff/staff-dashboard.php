<?php
$portal = 'staff';
$pageTitle = 'Finzo Staff';
ob_start();
?>
<!-- Welcome Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-black font-headline text-text-main dark:text-white" id="welcome-text">Loading...</h2>
                    <p class="text-text-muted dark:text-text-muted text-sm mt-1 font-medium">Shift Summary & Daily Operations Overview.</p>
                </div>
                <div class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-white/5 rounded-xl border border-surface-border dark:border-white/10 text-xs font-bold text-text-muted dark:text-text-muted">
                    <span class="material-symbols-outlined text-base" aria-hidden="true">calendar_today</span>
                    <span id="header-date">12 Apr 2026</span>
                </div>
            </div>

            <!-- Quick Actions (High Density) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <a href="staff-transaction.php?type=deposit" class="ds-card p-6 flex items-center justify-between group hover:border-success-base/50 transition-all">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-success-surface dark:bg-success-surface/10 text-success-base dark:text-success-base flex items-center justify-center">
                            <span class="material-symbols-outlined" aria-hidden="true">add_circle</span>
                        </div>
                        <div>
                            <div class="font-headline font-bold text-lg text-text-main dark:text-white group-hover:text-success-base dark:group-hover:text-success-base transition-colors">Process Deposit</div>
                            <div class="text-xs text-text-muted font-medium">Credit accounts via cash/cheque</div>
                        </div>
                    </div>
                    <span class="material-symbols-outlined text-text-muted dark:text-text-muted group-hover:text-success-base group-hover:translate-x-1 transition-all" aria-hidden="true">arrow_forward</span>
                </a>
                
                <a href="staff-transaction.php?type=withdraw" class="ds-card p-6 flex items-center justify-between group hover:border-danger-base/50 transition-all">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-danger-surface dark:bg-danger-surface/10 text-danger-base dark:text-danger-base flex items-center justify-center">
                            <span class="material-symbols-outlined" aria-hidden="true">remove_circle</span>
                        </div>
                        <div>
                            <div class="font-headline font-bold text-lg text-text-main dark:text-white group-hover:text-danger-base dark:group-hover:text-danger-base transition-colors">Process Withdrawal</div>
                            <div class="text-xs text-text-muted font-medium">Debit accounts securely</div>
                        </div>
                    </div>
                    <span class="material-symbols-outlined text-text-muted dark:text-text-muted group-hover:text-danger-base group-hover:translate-x-1 transition-all" aria-hidden="true">arrow_forward</span>
                </a>
            </div>

            <!-- Shift Summary Grid -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="ds-card p-5 border-l-4 border-l-emerald-500">
                    <div class="text-xs font-black text-text-muted uppercase tracking-widest mb-2">Total Deposits</div>
                    <div class="text-2xl font-black text-success-base dark:text-success-base" id="stat-deposits">₹0.00</div>
                </div>
                <div class="ds-card p-5 border-l-4 border-l-red-500">
                    <div class="text-xs font-black text-text-muted uppercase tracking-widest mb-2">Total Withdrawals</div>
                    <div class="text-2xl font-black text-danger-base dark:text-danger-base" id="stat-withdrawals">₹0.00</div>
                </div>
                <div class="ds-card p-5 border-l-4 border-l-amber-500">
                    <div class="text-xs font-black text-text-muted uppercase tracking-widest mb-2">Transactions</div>
                    <div class="text-2xl font-black text-text-main dark:text-white" id="stat-transactions">0</div>
                </div>
                <div class="ds-card p-5 border-l-4 border-l-blue-500">
                    <div class="text-xs font-black text-text-muted uppercase tracking-widest mb-2">Net Flow</div>
                    <div class="text-2xl font-black text-info-base dark:text-primary-base" id="stat-netflow">₹0.00</div>
                </div>
            </div>

            <!-- Recent Activity Table (High Density Data Grid) -->
            <div class="ds-card overflow-hidden">
                <div class="p-5 border-b border-surface-border dark:border-white/5 flex justify-between items-center bg-surface-subtle/50 dark:bg-white/[0.02]">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-warning-base" aria-hidden="true">receipt_long</span>
                        <h3 class="font-headline font-bold text-text-main dark:text-white">Recent Shift Activity</h3>
                    </div>
                    <a href="staff-history.php" class="text-xs font-bold text-warning-base dark:text-warning-base hover:underline uppercase tracking-widest">View All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="ds-table">
                        <thead scope="col">
                            <tr class="bg-surface-subtle dark:bg-white/5 border-b border-surface-border dark:border-white/5">
                                <th class="p-3 text-xs font-black text-text-muted uppercase tracking-widest" scope="col">Time</th>
                                <th class="p-3 text-xs font-black text-text-muted uppercase tracking-widest" scope="col">Customer / Ref</th>
                                <th class="p-3 text-xs font-black text-text-muted uppercase tracking-widest" scope="col">Account</th>
                                <th class="p-3 text-xs font-black text-text-muted uppercase tracking-widest" scope="col">Type</th>
                                <th class="p-3 text-xs font-black text-text-muted uppercase tracking-widest" scope="col">Amount</th>
                                <th class="p-3 text-xs font-black text-text-muted uppercase tracking-widest text-right" scope="col">Receipt</th>
                            </tr>
                        </thead>
                        <tbody id="txn-tbody" class="divide-y divide-slate-100 dark:divide-white/5">
                            <tr>
                                <td colspan="6" class="text-center py-10 font-bold text-text-muted">Loading today's transactions...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>