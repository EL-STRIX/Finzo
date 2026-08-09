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
            <div class="ds-card overflow-hidden">
                <div class="p-4 sm:p-6 border-b border-border-subtle bg-surface-subtle">
                    <div class="flex flex-col xl:flex-row gap-4 items-start xl:items-center justify-between">
                        <!-- Search -->
                        <div class="w-full xl:w-96 shrink-0">
                            <label for="transaction-search" class="fin-label mb-2 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Search Transactions</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-text-secondary" aria-hidden="true">search</span>
                                <input type="text" id="transaction-search" placeholder="Search by TXN ID, Name or Note..." class="fin-input pl-12 w-full">
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
