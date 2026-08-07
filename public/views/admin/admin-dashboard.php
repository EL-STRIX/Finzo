<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin';
ob_start();
?>
<!-- KEY PERFORMANCE INDICATORS GRID -->
            <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-6 gap-4 sm:gap-6">
                
                <div class="ds-card p-5 relative overflow-hidden group lg:hover:border-warning-base dark:lg:hover:border-warning-base/30 transition-colors">
                    <div class="absolute right-0 top-0 w-16 h-16 bg-warning-surface/5 rounded-bl-full -z-0"></div>
                    <div class="relative z-10">
                        <div class="w-10 h-10 rounded-xl bg-warning-surface dark:bg-warning-surface/10 text-warning-base dark:text-warning-base flex items-center justify-center mb-4 border border-warning-base dark:border-warning-base/20 shadow-sm">
                            <span class="material-symbols-outlined text-xl" aria-hidden="true">account_balance_wallet</span>
                        </div>
                        <div class="text-xs font-black text-text-muted uppercase tracking-widest mb-1">Total Assets</div>
                        <div class="text-xl font-black font-mono tracking-tight text-text-main dark:text-white skeleton" id="stat-total-assets">₹0.00</div>
                    </div>
                </div>
                
                <div class="ds-card p-5 relative overflow-hidden group lg:hover:border-success-base dark:lg:hover:border-success-base/30 transition-colors">
                    <div class="absolute right-0 top-0 w-16 h-16 bg-success-surface/5 rounded-bl-full -z-0"></div>
                    <div class="relative z-10">
                        <div class="w-10 h-10 rounded-xl bg-success-surface dark:bg-success-surface/10 text-success-base dark:text-success-base flex items-center justify-center mb-4 border border-success-base dark:border-success-base/20 shadow-sm">
                            <span class="material-symbols-outlined text-xl" aria-hidden="true">download_for_offline</span>
                        </div>
                        <div class="text-xs font-black text-text-muted uppercase tracking-widest mb-1">Today's Deposits</div>
                        <div class="text-xl font-black font-mono tracking-tight text-text-main dark:text-white skeleton">0</div>
                    </div>
                </div>
                
                <div class="ds-card p-5 relative overflow-hidden group lg:hover:border-blue-200 dark:lg:hover:border-blue-500/30 transition-colors">
                    <div class="absolute right-0 top-0 w-16 h-16 bg-primary-surface/5 rounded-bl-full -z-0"></div>
                    <div class="relative z-10">
                        <div class="w-10 h-10 rounded-xl bg-primary-surface dark:bg-primary-surface/10 text-info-base dark:text-primary-base flex items-center justify-center mb-4 border border-blue-100 dark:border-blue-500/20 shadow-sm">
                            <span class="material-symbols-outlined text-xl" aria-hidden="true">upload_file</span>
                        </div>
                        <div class="text-xs font-black text-text-muted uppercase tracking-widest mb-1">Today's Withdrawals</div>
                        <div class="text-xl font-black font-mono tracking-tight text-text-main dark:text-white skeleton">0</div>
                    </div>
                </div>

                <div class="ds-card p-5 relative overflow-hidden group lg:hover:border-purple-200 dark:lg:hover:border-purple-500/30 transition-colors">
                    <div class="absolute right-0 top-0 w-16 h-16 bg-purple-500/5 rounded-bl-full -z-0"></div>
                    <div class="relative z-10">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-500 flex items-center justify-center mb-4 border border-purple-100 dark:border-purple-500/20 shadow-sm">
                            <span class="material-symbols-outlined text-xl" aria-hidden="true">group</span>
                        </div>
                        <div class="text-xs font-black text-text-muted uppercase tracking-widest mb-1">Total Users</div>
                        <div class="text-xl font-black tracking-tight text-text-main dark:text-white skeleton">0</div>
                    </div>
                </div>

                <div class="ds-card p-5 relative overflow-hidden group lg:hover:border-primary/20 dark:lg:hover:border-primary/50 transition-colors">
                    <div class="absolute right-0 top-0 w-16 h-16 bg-primary/5 rounded-bl-full -z-0"></div>
                    <div class="relative z-10">
                        <div class="w-10 h-10 rounded-xl bg-primary/5 text-primary-base flex items-center justify-center mb-4 border border-primary/10 shadow-sm">
                            <span class="material-symbols-outlined text-xl" aria-hidden="true">badge</span>
                        </div>
                        <div class="text-xs font-black text-text-muted uppercase tracking-widest mb-1">Active Staff</div>
                        <div class="text-xl font-black tracking-tight text-text-main dark:text-white skeleton">0</div>
                    </div>
                </div>

                <div class="ds-card p-5 relative overflow-hidden group lg:hover:border-danger-base dark:lg:hover:border-danger-base/30 transition-colors">
                    <div class="absolute right-0 top-0 w-16 h-16 bg-danger-surface/5 rounded-bl-full -z-0"></div>
                    <div class="relative z-10">
                        <div class="w-10 h-10 rounded-xl bg-danger-surface dark:bg-danger-surface/10 text-danger-base dark:text-danger-base flex items-center justify-center mb-4 border border-danger-base dark:border-danger-base/20 shadow-sm">
                            <span class="material-symbols-outlined text-xl" aria-hidden="true">lock</span>
                        </div>
                        <div class="text-xs font-black text-text-muted uppercase tracking-widest mb-1">Locked Accounts</div>
                        <div class="text-xl font-black tracking-tight text-text-main dark:text-white skeleton">0</div>
                    </div>
                </div>
            </div>

            <!-- CHARTS ROW -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Transaction Volume Chart -->
                <div class="lg:col-span-2 ds-card p-6 sm:p-8">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4 border-b border-surface-border dark:border-white/5 pb-6">
                        <div>
                            <h3 class="font-headline font-black text-lg text-text-main dark:text-white">Transaction Volume</h3>
                            <p class="text-xs font-bold text-text-muted mt-1 uppercase tracking-widest">Real-time volume analysis (Last 6 Months)</p>
                        </div>
                        <div class="flex items-center gap-4 text-xs font-black uppercase tracking-widest">
                            <span class="flex items-center gap-2 text-success-base dark:text-success-base"><span class="w-2 h-2 rounded-full bg-success-surface shadow-sm"></span> Deposits</span>
                            <span class="flex items-center gap-2 text-info-base dark:text-primary-base"><span class="w-2 h-2 rounded-full bg-primary-surface shadow-sm"></span> Withdrawals</span>
                        </div>
                    </div>
                    <div class="relative h-[320px] w-full">
                        <canvas id="chart-transactions"></canvas>
                    </div>
                </div>

                <!-- Account Spread Donut -->
                <div class="ds-card p-6 sm:p-8">
                    <div class="border-b border-surface-border dark:border-white/5 pb-6 mb-8">
                        <h3 class="font-headline font-black text-lg text-text-main dark:text-white">Account Distribution</h3>
                        <p class="text-xs font-bold text-text-muted mt-1 uppercase tracking-widest">Current System State</p>
                    </div>
                    <div class="relative flex items-center justify-center mb-8 h-[200px]">
                        <canvas id="chart-accounts"></canvas>
                    </div>
                    <div class="space-y-4" id="donut-legend">
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-xs font-bold text-text-muted dark:text-text-muted uppercase tracking-widest"><span class="w-2 h-2 rounded-full bg-success-surface"></span> Active</span>
                            <span class="font-black font-mono text-text-main dark:text-white skeleton" id="legend-active">0</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-xs font-bold text-text-muted dark:text-text-muted uppercase tracking-widest"><span class="w-2 h-2 rounded-full bg-warning-surface"></span> Pending</span>
                            <span class="font-black font-mono text-text-main dark:text-white skeleton" id="legend-pending">0</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-xs font-bold text-text-muted dark:text-text-muted uppercase tracking-widest"><span class="w-2 h-2 rounded-full bg-danger-surface"></span> Suspended</span>
                            <span class="font-black font-mono text-text-main dark:text-white skeleton" id="legend-suspended">0</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-xs font-bold text-text-muted dark:text-text-muted uppercase tracking-widest"><span class="w-2 h-2 rounded-full bg-border-main"></span> Locked</span>
                            <span class="font-black font-mono text-text-main dark:text-white skeleton" id="legend-locked">0</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- REVENUE CHART -->
            <div class="ds-card p-6 sm:p-8">
                <div class="border-b border-surface-border dark:border-white/5 pb-6 mb-8 flex justify-between items-center">
                    <div>
                        <h3 class="font-headline font-black text-lg text-text-main dark:text-white">Revenue Performance</h3>
                        <p class="text-xs font-bold text-text-muted mt-1 uppercase tracking-widest">Monthly Growth Analysis</p>
                    </div>
                </div>
                <div class="relative h-[280px] w-full">
                    <canvas id="chart-revenue"></canvas>
                </div>
            </div>

            <!-- RECENT ACTIVITY TABLE -->
            <div class="ds-card overflow-hidden">
                <div class="p-6 border-b border-surface-border dark:border-white/5 flex justify-between items-center bg-surface-subtle/50 dark:bg-white/2">
                    <h3 class="font-headline font-bold text-lg flex items-center gap-2 text-text-main dark:text-white">
                        <span class="material-symbols-outlined text-primary-base" aria-hidden="true">history</span> Recent Onboarding
                    </h3>
                    <a href="admin-users.php" class="text-xs font-black text-primary-base hover:underline uppercase tracking-widest">View All Users</a>
                </div>
                <div class="overflow-x-auto w-full" style="-webkit-overflow-scrolling: touch;">
                    <table class="ds-table w-full text-left min-w-[800px]">
                        <thead scope="col">
                            <tr>
                                <th scope="col">User Profile</th>
                                <th scope="col">Account Status</th>
                                <th scope="col">Verification</th>
                                <th scope="col">Tier</th>
                                <th scope="col">Joined Date</th>
                                <th class="text-right" scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody id="dash-user-tbody">
                            <tr class="skeleton-row"><td><div class="skeleton h-12 lg:h-10 w-full rounded-xl"></div></td><td><div class="skeleton h-6 w-20 rounded-lg"></div></td><td><div class="skeleton h-6 w-20 rounded-lg"></div></td><td><div class="skeleton h-4 w-16"></div></td><td><div class="skeleton h-4 w-12"></div></td><td><div class="skeleton h-10 lg:h-8 w-16 float-right"></div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>