<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin — Dashboard';
$useCharts = true;
ob_start();
?>
            <!-- TOP PAGE HEADER -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 relative z-10">
                <div>
                    <h1 class="text-2xl font-headline font-black text-text-primary tracking-tight">Dashboard</h1>
                    <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-1">System Overview &amp; Real-Time Analytics</p>
                </div>
                <div class="flex items-center gap-2 px-3.5 py-1.5 rounded-full border border-border-subtle bg-surface/80 shadow-sm text-xs font-bold text-text-secondary uppercase tracking-widest">
                    <span class="material-symbols-outlined text-[16px] text-primary" aria-hidden="true">schedule</span>
                    <span id="header-live-clock">--:--:--</span>
                </div>
            </div>

            <!-- KEY PERFORMANCE INDICATORS GRID -->
            <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-4 gap-4 sm:gap-6 relative z-10 mb-6 sm:mb-8">
                
                <!-- Card 1: Total Assets -->
                <div class="ds-card admin-stat-card p-5 sm:p-6 flex flex-col items-start justify-center group relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-warning/10 rounded-full blur-2xl group-hover:bg-warning/20 transition-all"></div>
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-warning/20 to-warning/5 text-warning border border-warning/20 flex items-center justify-center mb-4 shadow-[inset_0_1px_1px_rgba(255,255,255,0.4)] dark:shadow-[inset_0_1px_1px_rgba(255,255,255,0.1)]">
                        <span class="material-symbols-outlined text-2xl" aria-hidden="true">account_balance_wallet</span>
                    </div>
                    <div class="admin-stat-label text-[10px] font-black text-text-secondary uppercase tracking-[0.15em] mb-2">Total Assets</div>
                    <div class="admin-stat-value text-3xl font-black font-display text-text-primary leading-none tracking-tighter skeleton" id="stat-total-assets">&#8377;0.00</div>
                </div>
                
                <!-- Card 2: Today's Deposits -->
                <div class="ds-card admin-stat-card p-5 sm:p-6 flex flex-col items-start justify-center group relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-success/10 rounded-full blur-2xl group-hover:bg-success/20 transition-all"></div>
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-success/20 to-success/5 text-success border border-success/20 flex items-center justify-center mb-4 shadow-[inset_0_1px_1px_rgba(255,255,255,0.4)] dark:shadow-[inset_0_1px_1px_rgba(255,255,255,0.1)]">
                        <span class="material-symbols-outlined text-2xl" aria-hidden="true">download_for_offline</span>
                    </div>
                    <div class="admin-stat-label text-[10px] font-black text-text-secondary uppercase tracking-[0.15em] mb-2">Today's Deposits</div>
                    <div class="admin-stat-value text-3xl font-black font-display text-text-primary leading-none tracking-tighter skeleton" id="stat-deposits-today">&#8377;0.00</div>
                </div>
                
                <!-- Card 3: Today's Withdrawals -->
                <div class="ds-card admin-stat-card p-5 sm:p-6 flex flex-col items-start justify-center group relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-info/10 rounded-full blur-2xl group-hover:bg-info/20 transition-all"></div>
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-info/20 to-info/5 text-info border border-info/20 flex items-center justify-center mb-4 shadow-[inset_0_1px_1px_rgba(255,255,255,0.4)] dark:shadow-[inset_0_1px_1px_rgba(255,255,255,0.1)]">
                        <span class="material-symbols-outlined text-2xl" aria-hidden="true">upload_file</span>
                    </div>
                    <div class="admin-stat-label text-[10px] font-black text-text-secondary uppercase tracking-[0.15em] mb-2">Today's Withdrawals</div>
                    <div class="admin-stat-value text-3xl font-black font-display text-text-primary leading-none tracking-tighter skeleton" id="stat-withdrawals-today">&#8377;0.00</div>
                </div>

                <!-- Card 4: Active Accounts -->
                <div class="ds-card admin-stat-card p-5 sm:p-6 flex flex-col items-start justify-center group relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-primary/10 rounded-full blur-2xl group-hover:bg-primary/20 transition-all"></div>
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary/20 to-primary/5 text-primary border border-primary/20 flex items-center justify-center mb-4 shadow-[inset_0_1px_1px_rgba(255,255,255,0.4)] dark:shadow-[inset_0_1px_1px_rgba(255,255,255,0.1)]">
                        <span class="material-symbols-outlined text-2xl" aria-hidden="true">account_balance</span>
                    </div>
                    <div class="admin-stat-label text-[10px] font-black text-text-secondary uppercase tracking-[0.15em] mb-2">Active Accounts</div>
                    <div class="admin-stat-value text-3xl font-black font-display text-text-primary leading-none tracking-tighter skeleton" id="stat-active-accounts">0</div>
                </div>

                <!-- Card 5: Pending KYC -->
                <div class="ds-card admin-stat-card p-5 sm:p-6 flex flex-col items-start justify-center group relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-accent/10 rounded-full blur-2xl group-hover:bg-accent/20 transition-all"></div>
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-accent/20 to-accent/5 text-accent border border-accent/20 flex items-center justify-center mb-4 shadow-[inset_0_1px_1px_rgba(255,255,255,0.4)] dark:shadow-[inset_0_1px_1px_rgba(255,255,255,0.1)]">
                        <span class="material-symbols-outlined text-2xl" aria-hidden="true">rule</span>
                    </div>
                    <div class="admin-stat-label text-[10px] font-black text-text-secondary uppercase tracking-[0.15em] mb-2">Pending KYC</div>
                    <div class="admin-stat-value text-3xl font-black font-display text-text-primary leading-none tracking-tighter skeleton" id="stat-pending-kyc">0</div>
                </div>

                <!-- Card 6: Locked Accounts -->
                <div class="ds-card admin-stat-card p-5 sm:p-6 flex flex-col items-start justify-center group relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-error/10 rounded-full blur-2xl group-hover:bg-error/20 transition-all"></div>
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-error/20 to-error/5 text-error border border-error/20 flex items-center justify-center mb-4 shadow-[inset_0_1px_1px_rgba(255,255,255,0.4)] dark:shadow-[inset_0_1px_1px_rgba(255,255,255,0.1)]">
                        <span class="material-symbols-outlined text-2xl" aria-hidden="true">lock</span>
                    </div>
                    <div class="admin-stat-label text-[10px] font-black text-text-secondary uppercase tracking-[0.15em] mb-2">Locked Accounts</div>
                    <div class="admin-stat-value text-3xl font-black font-display text-text-primary leading-none tracking-tighter skeleton" id="stat-locked-accounts">0</div>
                </div>

                <!-- Card 7: Today's Transactions -->
                <div class="ds-card admin-stat-card p-5 sm:p-6 flex flex-col items-start justify-center group relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-secondary/10 rounded-full blur-2xl group-hover:bg-secondary/20 transition-all"></div>
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-secondary/20 to-secondary/5 text-secondary border border-secondary/20 flex items-center justify-center mb-4 shadow-[inset_0_1px_1px_rgba(255,255,255,0.4)] dark:shadow-[inset_0_1px_1px_rgba(255,255,255,0.1)]">
                        <span class="material-symbols-outlined text-2xl" aria-hidden="true">receipt_long</span>
                    </div>
                    <div class="admin-stat-label text-[10px] font-black text-text-secondary uppercase tracking-[0.15em] mb-2">Today's Transactions</div>
                    <div class="admin-stat-value text-3xl font-black font-display text-text-primary leading-none tracking-tighter skeleton" id="stat-today-txns">0</div>
                </div>

                <!-- Card 8: Total Users -->
                <div class="ds-card admin-stat-card p-5 sm:p-6 flex flex-col items-start justify-center group relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-text-muted/10 rounded-full blur-2xl group-hover:bg-text-muted/20 transition-all"></div>
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-text-muted/20 to-text-muted/5 text-text-muted border border-text-muted/20 flex items-center justify-center mb-4 shadow-[inset_0_1px_1px_rgba(255,255,255,0.4)] dark:shadow-[inset_0_1px_1px_rgba(255,255,255,0.1)]">
                        <span class="material-symbols-outlined text-2xl" aria-hidden="true">group</span>
                    </div>
                    <div class="admin-stat-label text-[10px] font-black text-text-secondary uppercase tracking-[0.15em] mb-2">Total Users</div>
                    <div class="admin-stat-value text-3xl font-black font-display text-text-primary leading-none tracking-tighter skeleton" id="stat-total-users">0</div>
                </div>

                <!-- Card 9: Monthly Transactions -->
                <div class="ds-card admin-stat-card p-5 sm:p-6 flex flex-col items-start justify-center group relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-info/10 rounded-full blur-2xl group-hover:bg-info/20 transition-all"></div>
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-info/20 to-info/5 text-info border border-info/20 flex items-center justify-center mb-4 shadow-[inset_0_1px_1px_rgba(255,255,255,0.4)] dark:shadow-[inset_0_1px_1px_rgba(255,255,255,0.1)]">
                        <span class="material-symbols-outlined text-2xl" aria-hidden="true">query_stats</span>
                    </div>
                    <div class="admin-stat-label text-[10px] font-black text-text-secondary uppercase tracking-[0.15em] mb-2">Monthly Transactions</div>
                    <div class="admin-stat-value text-3xl font-black font-display text-text-primary leading-none tracking-tighter skeleton" id="stat-monthly-txns">0</div>
                </div>

                <!-- Card 10: Interest Credited -->
                <div class="ds-card admin-stat-card p-5 sm:p-6 flex flex-col items-start justify-center group relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-success/10 rounded-full blur-2xl group-hover:bg-success/20 transition-all"></div>
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-success/20 to-success/5 text-success border border-success/20 flex items-center justify-center mb-4 shadow-[inset_0_1px_1px_rgba(255,255,255,0.4)] dark:shadow-[inset_0_1px_1px_rgba(255,255,255,0.1)]">
                        <span class="material-symbols-outlined text-2xl" aria-hidden="true">price_check</span>
                    </div>
                    <div class="admin-stat-label text-[10px] font-black text-text-secondary uppercase tracking-[0.15em] mb-2">Interest Credited</div>
                    <div class="admin-stat-value text-3xl font-black font-display text-text-primary leading-none tracking-tighter skeleton" id="stat-interest-credited">&#8377;0.00</div>
                </div>

                <!-- Card 11: Suspended Users -->
                <div class="ds-card admin-stat-card p-5 sm:p-6 flex flex-col items-start justify-center group relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-warning/10 rounded-full blur-2xl group-hover:bg-warning/20 transition-all"></div>
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-warning/20 to-warning/5 text-warning border border-warning/20 flex items-center justify-center mb-4 shadow-[inset_0_1px_1px_rgba(255,255,255,0.4)] dark:shadow-[inset_0_1px_1px_rgba(255,255,255,0.1)]">
                        <span class="material-symbols-outlined text-2xl" aria-hidden="true">person_off</span>
                    </div>
                    <div class="admin-stat-label text-[10px] font-black text-text-secondary uppercase tracking-[0.15em] mb-2">Suspended Users</div>
                    <div class="admin-stat-value text-3xl font-black font-display text-text-primary leading-none tracking-tighter skeleton" id="stat-suspended-users">0</div>
                </div>

                <!-- Card 12: Total Branches -->
                <div class="ds-card admin-stat-card p-5 sm:p-6 flex flex-col items-start justify-center group relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-primary/10 rounded-full blur-2xl group-hover:bg-primary/20 transition-all"></div>
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary/20 to-primary/5 text-primary border border-primary/20 flex items-center justify-center mb-4 shadow-[inset_0_1px_1px_rgba(255,255,255,0.4)] dark:shadow-[inset_0_1px_1px_rgba(255,255,255,0.1)]">
                        <span class="material-symbols-outlined text-2xl" aria-hidden="true">storefront</span>
                    </div>
                    <div class="admin-stat-label text-[10px] font-black text-text-secondary uppercase tracking-[0.15em] mb-2">Total Branches</div>
                    <div class="admin-stat-value text-3xl font-black font-display text-text-primary leading-none tracking-tighter skeleton" id="stat-total-branches">0</div>
                </div>

            </div>

            <!-- CHARTS ROW -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6 sm:mb-8">
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
            <div class="ds-card p-6 sm:p-8 mb-6 sm:mb-8">
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
                    <a href="/Finzo/admin/users" class="text-xs font-black text-primary hover:underline uppercase tracking-widest">View All Users</a>
                </div>
                <div class="ds-table-wrapper border-0 rounded-none shadow-none bg-transparent overflow-x-auto">
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
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/dashboard.js"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
