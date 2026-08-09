<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin - Transaction Monitor';
$pageHeader = '
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-headline font-black text-text-main dark:text-white tracking-tight">Transaction Monitor</h1>
        <p class="text-xs font-bold text-text-muted uppercase tracking-widest mt-0.5">View and audit all system-wide transactions</p>
    </div>
</div>
';
ob_start();
?>
<!-- FILTERS & TABLE -->
            <div class="ds-card overflow-hidden">
                <div class="p-4 sm:p-6 border-b border-surface-border dark:border-white/5 bg-surface-subtle/20 dark:bg-white/5 relative z-20">
                    <div class="flex flex-col md:flex-row gap-4 items-start md:items-center justify-between">
                        <!-- Search -->
                        <div class="w-full md:w-96">
                            <label for="transaction-search" class="fin-label mb-2">Search Transactions</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-text-muted" aria-hidden="true">search</span>
                                <input type="text" id="transaction-search" placeholder="Search by TXN ID, Name or Note..." class="fin-input pl-12">
                            </div>
                        </div>

                        <!-- Type Tabs -->
                        <div class="flex flex-wrap gap-2 mt-4 md:mt-0">
                            <button class="tab-btn px-4 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all duration-200 bg-primary-base text-white shadow-lg shadow-primary-base/20 scale-[1.02] dark:bg-sky-500/20 dark:text-sky-300 dark:shadow-none dark:ring-1 dark:ring-sky-500/30" data-type="all">
                                All
                            </button>
                            <button class="tab-btn px-4 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all duration-200 bg-surface-hover text-text-muted hover:bg-surface-border/80 hover:text-text-main hover:scale-[1.01] dark:bg-white/5 dark:text-text-muted dark:hover:bg-white/10 dark:hover:text-text-muted" data-type="deposit">
                                <span class="material-symbols-outlined" style="font-size:13px;vertical-align:-2px;" aria-hidden="true">south_west</span>
                                Deposits
                            </button>
                            <button class="tab-btn px-4 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all duration-200 bg-surface-hover text-text-muted hover:bg-surface-border/80 hover:text-text-main hover:scale-[1.01] dark:bg-white/5 dark:text-text-muted dark:hover:bg-white/10 dark:hover:text-text-muted" data-type="withdrawal">
                                <span class="material-symbols-outlined" style="font-size:13px;vertical-align:-2px;" aria-hidden="true">north_east</span>
                                Withdrawals
                            </button>
                            <button class="tab-btn px-4 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all duration-200 bg-surface-hover text-text-muted hover:bg-surface-border/80 hover:text-text-main hover:scale-[1.01] dark:bg-white/5 dark:text-text-muted dark:hover:bg-white/10 dark:hover:text-text-muted" data-type="transfer">
                                <span class="material-symbols-outlined" style="font-size:13px;vertical-align:-2px;" aria-hidden="true">sync_alt</span>
                                Transfers
                            </button>
                            <button class="tab-btn px-4 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all duration-200 bg-surface-hover text-text-muted hover:bg-surface-border/80 hover:text-text-main hover:scale-[1.01] dark:bg-white/5 dark:text-text-muted dark:hover:bg-white/10 dark:hover:text-text-muted" data-type="interest_credit">
                                <span class="material-symbols-outlined" style="font-size:13px;vertical-align:-2px;" aria-hidden="true">percent</span>
                                Interest
                            </button>
                        </div>
                    </div>

                </div>
                <div class="overflow-x-auto w-full">
                    <table id="transactions-table" class="ds-table w-full text-left min-w-[1300px]">
                        <thead scope="col">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">TXN ID</th>
                                <th scope="col">From Account</th>
                                <th scope="col">To Account</th>
                                <th scope="col">Type</th>
                                <th scope="col">Amount</th>
                                <th scope="col">Date</th>
                                <th scope="col">Status</th>
                                <th class="text-right" scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody id="txn-table-body">
                            <!-- Data injected by transactions.js -->
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION -->
                <div class="p-4 sm:p-6 bg-surface-subtle/50 dark:bg-white/5 border-t border-surface-border dark:border-white/5 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div id="pagination-info" class="text-xs font-bold opacity-40 uppercase tracking-widest">Showing 0-0 of 0 Records</div>
                    <div class="flex items-center gap-2">
                        <button id="prev-btn" class="btn btn-secondary px-4 !py-2 !text-xs disabled:opacity-50 disabled:cursor-not-allowed">Previous</button>
                        <div id="page-numbers" class="flex gap-1.5">
                            <!-- Page numbers injected by transactions.js -->
                        </div>
                        <button id="next-btn" class="btn btn-secondary px-4 !py-2 !text-xs disabled:opacity-50 disabled:cursor-not-allowed">Next</button>
                    </div>
                </div>
            </div>
<?php
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/transactions.js"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>