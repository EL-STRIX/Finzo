<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin - Transaction Monitor';
$pageHeader = '
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-headline font-black text-text-primary tracking-tight">Transaction Monitor</h1>
        <p class="text-[10px] font-black text-text-secondary uppercase tracking-widest mt-0.5">View and audit all system-wide transactions</p>
    </div>
</div>
';
ob_start();
?>
<!-- FILTERS & TABLE -->
            <div class="ds-card overflow-hidden relative z-10 shadow-2xl transition-all duration-300">
                <div class="absolute -right-20 -top-20 w-64 h-64 bg-primary/5 rounded-full blur-3xl -z-10 pointer-events-none"></div>
                <div class="absolute -left-20 -bottom-20 w-64 h-64 bg-accent/5 rounded-full blur-3xl -z-10 pointer-events-none"></div>
                
                <div class="p-5 sm:p-6 border-b border-border-subtle bg-background/40 backdrop-blur-xl relative z-10">
                    <div class="flex flex-col xl:flex-row gap-5 items-start xl:items-center justify-between">
                        <!-- Search -->
                        <div class="w-full xl:w-80 shrink-0">
                            <label for="transaction-search" class="fin-label mb-2 block text-xs font-bold text-text-secondary uppercase tracking-[0.15em]">Search</label>
                            <div class="relative group">
                                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-text-secondary group-focus-within:text-primary transition-colors" aria-hidden="true">search</span>
                                <input type="text" id="transaction-search" placeholder="Search ID, Name..." class="fin-input pl-12 w-full transition-all focus:ring-2 focus:ring-primary/20">
                            </div>
                        </div>

                        <!-- Date From -->
                        <div class="w-full xl:w-40 shrink-0">
                            <label for="filter-date-from" class="fin-label mb-2 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Date From</label>
                            <input type="date" id="filter-date-from" class="fin-input w-full">
                        </div>

                        <!-- Date To -->
                        <div class="w-full xl:w-40 shrink-0">
                            <label for="filter-date-to" class="fin-label mb-2 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Date To</label>
                            <input type="date" id="filter-date-to" class="fin-input w-full">
                        </div>

                        <!-- Status Filter -->
                        <div class="w-full xl:w-48 shrink-0">
                            <label for="filter-status" class="fin-label mb-2 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Status</label>
                            <div class="finzo-dropdown w-full" id="status-dropdown">
                                <div class="finzo-dropdown-trigger fin-input !flex items-center justify-between w-full">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-lg text-text-secondary" aria-hidden="true">filter_list</span>
                                        <span class="dropdown-label font-bold text-sm text-text-primary">All Status</span>
                                    </div>
                                    <span class="material-symbols-outlined chevron transition-transform duration-300" aria-hidden="true">expand_more</span>
                                </div>
                                <div class="finzo-dropdown-menu !z-[3000] !w-full">
                                    <div class="finzo-dropdown-option is-selected" data-value="all">All Status</div>
                                    <div class="finzo-dropdown-option" data-value="pending">Pending</div>
                                    <div class="finzo-dropdown-option" data-value="completed">Completed</div>
                                    <div class="finzo-dropdown-option" data-value="failed">Failed</div>
                                    <div class="finzo-dropdown-option" data-value="reversed">Reversed</div>
                                </div>
                                <input type="hidden" id="filter-status" value="all">
                            </div>
                        </div>

                        <!-- Type Tabs -->
                        <div class="flex flex-wrap gap-2 w-full xl:justify-end">
                            <button class="tab-btn btn btn-primary px-4 py-2.5 rounded-xl !text-[10px] font-black uppercase tracking-widest transition-all duration-200" data-type="all">
                                All
                            </button>
                            <button class="tab-btn btn btn-secondary px-4 py-2.5 rounded-xl !text-[10px] font-black uppercase tracking-widest transition-all duration-200" data-type="deposit">
                                <span class="material-symbols-outlined text-[13px] align-[-2px] mr-1" aria-hidden="true">south_west</span>
                                Deposits
                            </button>
                            <button class="tab-btn btn btn-secondary px-4 py-2.5 rounded-xl !text-[10px] font-black uppercase tracking-widest transition-all duration-200" data-type="withdrawal">
                                <span class="material-symbols-outlined text-[13px] align-[-2px] mr-1" aria-hidden="true">north_east</span>
                                Withdrawals
                            </button>
                            <button class="tab-btn btn btn-secondary px-4 py-2.5 rounded-xl !text-[10px] font-black uppercase tracking-widest transition-all duration-200" data-type="transfer">
                                <span class="material-symbols-outlined text-[13px] align-[-2px] mr-1" aria-hidden="true">sync_alt</span>
                                Transfers
                            </button>
                            <button class="tab-btn btn btn-secondary px-4 py-2.5 rounded-xl !text-[10px] font-black uppercase tracking-widest transition-all duration-200" data-type="interest_credit">
                                <span class="material-symbols-outlined text-[13px] align-[-2px] mr-1" aria-hidden="true">percent</span>
                                Interest
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="ds-table-wrapper w-full border-0 shadow-none rounded-none bg-transparent">
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
                                <th class="text-right px-6" scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody id="txn-table-body">
                            <!-- Data injected by transactions.js -->
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION -->
                <div class="p-4 sm:p-6 bg-surface-subtle border-t border-border-subtle flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div id="pagination-info" class="text-[10px] font-bold text-text-secondary uppercase tracking-widest">Showing 0-0 of 0 Records</div>
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
