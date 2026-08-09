<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin — Dashboard';
$useCharts = true;
$pageHeader = '
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-headline font-black text-text-main dark:text-white tracking-tight">Dashboard</h1>
        <p class="text-xs font-bold text-text-muted uppercase tracking-widest mt-0.5">System Overview &amp; Real-Time Analytics</p>
    </div>
    <div class="flex items-center gap-3 text-xs font-bold text-text-muted uppercase tracking-widest">
        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">schedule</span>
        <span id="header-live-clock"></span>
    </div>
</div>
<script defer src="/Finzo/public/assets/js/admin/admin-dashboard.js"></script>
';
ob_start();
?><!-- KEY PERFORMANCE INDICATORS GRID -->
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 sm:gap-6">
                
                <div class="admin-stat-card group hover:border-warning-base/40 dark:hover:border-warning-base/30">
                    <div class="admin-stat-icon bg-warning-surface dark:bg-warning-surface/10 text-warning-base border border-warning-base/20">
                        <span class="material-symbols-outlined text-xl" aria-hidden="true">account_balance_wallet</span>
                    </div>
                    <div class="admin-stat-label">Total Assets</div>
                    <div class="admin-stat-value skeleton" id="stat-total-assets">&#8377;0</div>
                </div>
                
                <div class="admin-stat-card group hover:border-success-base/40 dark:hover:border-success-base/30">
                    <div class="admin-stat-icon bg-success-surface dark:bg-success-surface/10 text-success-base border border-success-base/20">
                        <span class="material-symbols-outlined text-xl" aria-hidden="true">download_for_offline</span>
                    </div>
                    <div class="admin-stat-label">Today's Deposits</div>
                    <div class="admin-stat-value skeleton">0</div>
                </div>
                
                <div class="admin-stat-card group hover:border-blue-200 dark:hover:border-blue-500/30">
                    <div class="admin-stat-icon bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-500/20">
                        <span class="material-symbols-outlined text-xl" aria-hidden="true">upload_file</span>
                    </div>
                    <div class="admin-stat-label">Today's Withdrawals</div>
                    <div class="admin-stat-value skeleton">0</div>
                </div>

                <div class="admin-stat-card group hover:border-purple-200 dark:hover:border-purple-500/30">
                    <div class="admin-stat-icon bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-100 dark:border-purple-500/20">
                        <span class="material-symbols-outlined text-xl" aria-hidden="true">group</span>
                    </div>
                    <div class="admin-stat-label">Total Users</div>
                    <div class="admin-stat-value skeleton">0</div>
                </div>

                <div class="admin-stat-card group hover:border-primary-base/20 dark:hover:border-primary-base/50">
                    <div class="admin-stat-icon bg-primary-surface dark:bg-primary-surface/10 text-primary-base border border-primary-base/10">
                        <span class="material-symbols-outlined text-xl" aria-hidden="true">badge</span>
                    </div>
                    <div class="admin-stat-label">Active Staff</div>
                    <div class="admin-stat-value skeleton">0</div>
                </div>

                <div class="admin-stat-card group hover:border-danger-base/40 dark:hover:border-danger-base/30">
                    <div class="admin-stat-icon bg-danger-surface dark:bg-danger-surface/10 text-danger-base border border-danger-base/20">
                        <span class="material-symbols-outlined text-xl" aria-hidden="true">lock</span>
                    </div>
                    <div class="admin-stat-label">Locked Accounts</div>
                    <div class="admin-stat-value skeleton">0</div>
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
                <div class="overflow-x-auto w-full touch-pan-x">
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
$extraScripts = '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script><script defer src="/Finzo/public/assets/js/admin/dashboard.js"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
