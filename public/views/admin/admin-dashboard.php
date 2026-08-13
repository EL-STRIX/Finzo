<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin — Dashboard';
$useCharts = true;
$pageHeader = '
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-headline font-black text-text-primary tracking-tight">Dashboard</h1>
        <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-0.5">System Overview &amp; Real-Time Analytics</p>
    </div>
    <div class="flex items-center gap-3 text-xs font-bold text-text-secondary uppercase tracking-widest">
        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">schedule</span>
        <span id="header-live-clock"></span>
    </div>
</div>
';
ob_start();
?><!-- KEY PERFORMANCE INDICATORS GRID -->
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 sm:gap-6">
                
                <div class="ds-card admin-stat-card p-4 sm:p-5 flex flex-col items-start justify-center group hover:border-warning hover:shadow-md transition-all">
                    <div class="w-10 h-10 rounded-md bg-warning-bg text-warning border border-warning-bg flex items-center justify-center mb-3">
                        <span class="material-symbols-outlined text-xl" aria-hidden="true">account_balance_wallet</span>
                    </div>
                    <div class="admin-stat-label text-[11px] font-bold text-text-secondary uppercase tracking-widest mb-1">Total Assets</div>
                    <div class="admin-stat-value text-2xl font-black font-display text-text-primary leading-tight tracking-tight skeleton" id="stat-total-assets">&#8377;0</div>
                </div>
                
                <div class="ds-card admin-stat-card p-4 sm:p-5 flex flex-col items-start justify-center group hover:border-success hover:shadow-md transition-all">
                    <div class="w-10 h-10 rounded-md bg-success-bg text-success border border-success-bg flex items-center justify-center mb-3">
                        <span class="material-symbols-outlined text-xl" aria-hidden="true">download_for_offline</span>
                    </div>
                    <div class="admin-stat-label text-[11px] font-bold text-text-secondary uppercase tracking-widest mb-1">Today's Deposits</div>
                    <div class="admin-stat-value text-2xl font-black font-display text-text-primary leading-tight tracking-tight skeleton">0</div>
                </div>
                
                <div class="ds-card admin-stat-card p-4 sm:p-5 flex flex-col items-start justify-center group hover:border-info hover:shadow-md transition-all">
                    <div class="w-10 h-10 rounded-md bg-info-bg text-info border border-info-bg flex items-center justify-center mb-3">
                        <span class="material-symbols-outlined text-xl" aria-hidden="true">upload_file</span>
                    </div>
                    <div class="admin-stat-label text-[11px] font-bold text-text-secondary uppercase tracking-widest mb-1">Today's Withdrawals</div>
                    <div class="admin-stat-value text-2xl font-black font-display text-text-primary leading-tight tracking-tight skeleton">0</div>
                </div>

                <div class="ds-card admin-stat-card p-4 sm:p-5 flex flex-col items-start justify-center group hover:border-primary hover:shadow-md transition-all">
                    <div class="w-10 h-10 rounded-md bg-primary-50 text-primary border border-primary-100 flex items-center justify-center mb-3">
                        <span class="material-symbols-outlined text-xl" aria-hidden="true">group</span>
                    </div>
                    <div class="admin-stat-label text-[11px] font-bold text-text-secondary uppercase tracking-widest mb-1">Total Users</div>
                    <div class="admin-stat-value text-2xl font-black font-display text-text-primary leading-tight tracking-tight skeleton">0</div>
                </div>

                <div class="ds-card admin-stat-card p-4 sm:p-5 flex flex-col items-start justify-center group hover:border-primary-hover hover:shadow-md transition-all">
                    <div class="w-10 h-10 rounded-md bg-primary-100 text-primary-700 border border-primary-200 flex items-center justify-center mb-3">
                        <span class="material-symbols-outlined text-xl" aria-hidden="true">badge</span>
                    </div>
                    <div class="admin-stat-label text-[11px] font-bold text-text-secondary uppercase tracking-widest mb-1">Active Staff</div>
                    <div class="admin-stat-value text-2xl font-black font-display text-text-primary leading-tight tracking-tight skeleton">0</div>
                </div>

                <div class="ds-card admin-stat-card p-4 sm:p-5 flex flex-col items-start justify-center group hover:border-error hover:shadow-md transition-all">
                    <div class="w-10 h-10 rounded-md bg-error-bg text-error border border-error-bg flex items-center justify-center mb-3">
                        <span class="material-symbols-outlined text-xl" aria-hidden="true">lock</span>
                    </div>
                    <div class="admin-stat-label text-[11px] font-bold text-text-secondary uppercase tracking-widest mb-1">Locked Accounts</div>
                    <div class="admin-stat-value text-2xl font-black font-display text-text-primary leading-tight tracking-tight skeleton">0</div>
                </div>
            </div>

            <!-- CHARTS ROW -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Transaction Volume Chart -->
                <div class="lg:col-span-2 ds-card p-6 sm:p-8">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4 border-b border-border-subtle pb-6">
                        <div>
                            <h3 class="font-headline font-black text-lg text-text-primary">Transaction Volume</h3>
                            <p class="text-xs font-bold text-text-secondary mt-1 uppercase tracking-widest">Real-time volume analysis (Last 6 Months)</p>
                        </div>
                        <div class="flex items-center gap-4 text-xs font-black uppercase tracking-widest">
                            <span class="flex items-center gap-2 text-success"><span class="w-2 h-2 rounded-full bg-success shadow-sm"></span> Deposits</span>
                            <span class="flex items-center gap-2 text-info"><span class="w-2 h-2 rounded-full bg-info shadow-sm"></span> Withdrawals</span>
                        </div>
                    </div>
                    <div class="relative h-[320px] w-full">
                        <canvas id="chart-transactions"></canvas>
                    </div>
                </div>

                <!-- Account Spread Donut -->
                <div class="ds-card p-6 sm:p-8">
                    <div class="border-b border-border-subtle pb-6 mb-8">
                        <h3 class="font-headline font-black text-lg text-text-primary">Account Distribution</h3>
                        <p class="text-xs font-bold text-text-secondary mt-1 uppercase tracking-widest">Current System State</p>
                    </div>
                    <div class="relative flex items-center justify-center mb-8 h-[200px]">
                        <canvas id="chart-accounts"></canvas>
                    </div>
                    <div class="space-y-4" id="donut-legend">
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-xs font-bold text-text-secondary uppercase tracking-widest"><span class="w-2 h-2 rounded-full bg-success"></span> Active</span>
                            <span class="font-black font-mono text-text-primary skeleton" id="legend-active">0</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-xs font-bold text-text-secondary uppercase tracking-widest"><span class="w-2 h-2 rounded-full bg-warning"></span> Pending</span>
                            <span class="font-black font-mono text-text-primary skeleton" id="legend-pending">0</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-xs font-bold text-text-secondary uppercase tracking-widest"><span class="w-2 h-2 rounded-full bg-error"></span> Suspended</span>
                            <span class="font-black font-mono text-text-primary skeleton" id="legend-suspended">0</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-xs font-bold text-text-secondary uppercase tracking-widest"><span class="w-2 h-2 rounded-full bg-border-strong"></span> Locked</span>
                            <span class="font-black font-mono text-text-primary skeleton" id="legend-locked">0</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- REVENUE CHART -->
            <div class="ds-card p-6 sm:p-8">
                <div class="border-b border-border-subtle pb-6 mb-8 flex justify-between items-center">
                    <div>
                        <h3 class="font-headline font-black text-lg text-text-primary">Revenue Performance</h3>
                        <p class="text-xs font-bold text-text-secondary mt-1 uppercase tracking-widest">Monthly Growth Analysis</p>
                    </div>
                </div>
                <div class="relative h-[280px] w-full">
                    <canvas id="chart-revenue"></canvas>
                </div>
            </div>

            <!-- RECENT ACTIVITY TABLE -->
            <div class="ds-card overflow-hidden">
                <div class="p-6 border-b border-border-subtle flex justify-between items-center bg-background">
                    <h3 class="font-headline font-bold text-lg flex items-center gap-2 text-text-primary">
                        <span class="material-symbols-outlined text-primary" aria-hidden="true">history</span> Recent Onboarding
                    </h3>
                    <a href="admin-users.php" class="text-xs font-black text-primary hover:underline uppercase tracking-widest">View All Users</a>
                </div>
                <div class="ds-table-wrapper border-0 rounded-none shadow-none bg-transparent">
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
