<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin — Global Transactions';
// Admin portal guard — server-side authentication & role check
include __DIR__ . '/_guard.php';
ob_start();
?>

<!-- PRINT MEDIA STYLES (Strict Black & White Bank Statement Layout) -->
<style>
@media print {
    @page {
        size: auto;
        margin: 10mm 12mm 12mm 12mm;
    }
    body, html {
        background: #ffffff !important;
        color: #000000 !important;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .print-hidden, .print\:hidden,
    #sidebar, #sidebar-overlay, header, nav,
    .fin-portal-topbar, .type-pill, #reset-filters-btn,
    .finzo-dropdown, #txn-details-modal, #txn-reversal-modal,
    button {
        display: none !important;
    }
    .print-block, .print\:block {
        display: block !important;
    }
    .print-flex, .print\:flex {
        display: flex !important;
    }
    .print-grid, .print\:grid {
        display: grid !important;
    }
    .ds-card {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    .ds-table-wrapper {
        overflow: visible !important;
    }
    table, .ds-table {
        width: 100% !important;
        min-width: 100% !important;
        border-collapse: collapse !important;
        border: 1px solid #000000 !important;
        color: #000000 !important;
        font-size: 9.5pt !important;
        page-break-inside: auto !important;
    }
    tr {
        page-break-inside: avoid !important;
        page-break-after: auto !important;
    }
    thead {
        display: table-header-group !important;
    }
    tfoot {
        display: table-footer-group !important;
    }
    th, td {
        border: 1px solid #000000 !important;
        color: #000000 !important;
        padding: 6px 7px !important;
        background: transparent !important;
        text-align: left !important;
        vertical-align: middle !important;
    }
    th {
        background-color: #f3f4f6 !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        font-size: 8.5pt !important;
        letter-spacing: 0.05em !important;
    }
    td * {
        color: #000000 !important;
    }
    .badge, .ds-badge {
        border: 1px solid #000000 !important;
        background: transparent !important;
        color: #000000 !important;
        font-weight: 800 !important;
        padding: 1px 5px !important;
        border-radius: 3px !important;
        font-size: 8pt !important;
        display: inline-block !important;
        text-transform: uppercase !important;
    }
    /* Hide Actions column in print */
    table th:last-child, table td:last-child {
        display: none !important;
    }
}
</style>

<!-- PRINT-ONLY HEADER (Matches Statement / Passbook Style) -->
<div class="hidden print:block mb-6">
    <div class="flex justify-between items-start pb-4 border-b-2 border-black mb-4">
        <div>
            <div class="text-3xl font-black text-black tracking-tight">Finzo</div>
            <div class="text-[11px] font-bold text-black tracking-widest uppercase mt-0.5">Enterprise Banking System</div>
            <div class="text-xs text-black mt-1">support@finzobank.com • +91 1800-FINZO-BANK</div>
        </div>
        <div class="text-right">
            <div class="text-base font-black text-black uppercase tracking-wider">Global Transaction Statement</div>
            <div class="text-xs font-bold text-black mt-1 uppercase tracking-widest" id="print-period">Period: All Transactions</div>
            <div class="text-xs text-black uppercase tracking-widest mt-0.5">Generated: <span id="print-generated"><?= date('d M Y, h:i A') ?></span></div>
        </div>
    </div>

    <!-- Print-only Statement Summary Metrics Grid -->
    <div class="grid grid-cols-4 gap-3 mb-6">
        <div class="border border-black p-3 text-center">
            <div class="text-[10px] font-bold uppercase tracking-widest text-black mb-1">Total Credits (Deposits)</div>
            <div class="text-base font-black text-black" id="print-credits">₹0.00</div>
        </div>
        <div class="border border-black p-3 text-center">
            <div class="text-[10px] font-bold uppercase tracking-widest text-black mb-1">Total Debits (Withdrawals)</div>
            <div class="text-base font-black text-black" id="print-debits">₹0.00</div>
        </div>
        <div class="border border-black p-3 text-center">
            <div class="text-[10px] font-bold uppercase tracking-widest text-black mb-1">Total Ledger Volume</div>
            <div class="text-base font-black text-black" id="print-volume">₹0.00</div>
        </div>
        <div class="border border-black p-3 text-center">
            <div class="text-[10px] font-bold uppercase tracking-widest text-black mb-1">Total Transactions</div>
            <div class="text-base font-black text-black" id="print-count">0</div>
        </div>
    </div>
</div>

<!-- TOP PAGE HEADER -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 relative z-10">
    <div>
        <h1 class="text-2xl font-headline font-black text-text-primary tracking-tight">Global Transactions</h1>
        <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-1">Real-time ledger audit, multi-channel flow monitor, and transaction reversal control</p>
    </div>
</div>

<!-- 5 STAT CARDS (Interactive Web Mode) -->
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 sm:gap-6 relative z-10 mb-6 print:hidden">
    <!-- Card 1: Total Volume -->
    <div class="ds-card p-5 sm:p-6 flex flex-col items-start justify-center group relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
        <div class="absolute -right-6 -top-6 w-24 h-24 bg-primary/10 rounded-full blur-2xl group-hover:bg-primary/20 transition-all"></div>
        <div class="relative z-10 w-full">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-primary/20 to-primary/5 text-primary flex items-center justify-center mb-3 border border-primary/20 shadow-[inset_0_1px_1px_rgba(255,255,255,0.4)] dark:shadow-[inset_0_1px_1px_rgba(255,255,255,0.1)]">
                <span class="material-symbols-outlined text-2xl" aria-hidden="true">account_balance</span>
            </div>
            <div class="text-[11px] font-bold text-text-secondary uppercase tracking-[0.15em] mb-1">Total Volume</div>
            <div class="text-2xl sm:text-3xl font-black font-display tracking-tight leading-none text-text-primary skeleton min-w-[50px]" id="stat-total-volume">₹0</div>
            <div class="text-[10px] font-bold text-text-secondary uppercase tracking-widest mt-2 flex items-center gap-1">
                <span class="material-symbols-outlined text-[12px] text-primary" aria-hidden="true">all_inclusive</span>
                <span>Cumulative Ledger Flow</span>
            </div>
        </div>
    </div>

    <!-- Card 2: Total Transactions -->
    <div class="ds-card p-5 sm:p-6 flex flex-col items-start justify-center group relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
        <div class="absolute -right-6 -top-6 w-24 h-24 bg-info/10 rounded-full blur-2xl group-hover:bg-info/20 transition-all"></div>
        <div class="relative z-10 w-full">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-info/20 to-info/5 text-info flex items-center justify-center mb-3 border border-info/20 shadow-[inset_0_1px_1px_rgba(255,255,255,0.4)] dark:shadow-[inset_0_1px_1px_rgba(255,255,255,0.1)]">
                <span class="material-symbols-outlined text-2xl" aria-hidden="true">swap_horiz</span>
            </div>
            <div class="text-[11px] font-bold text-text-secondary uppercase tracking-[0.15em] mb-1">Total Records</div>
            <div class="text-2xl sm:text-3xl font-black font-display tracking-tight leading-none text-text-primary skeleton min-w-[40px]" id="stat-total-count">0</div>
            <div class="text-[10px] font-bold text-text-secondary uppercase tracking-widest mt-2 flex items-center gap-1">
                <span class="material-symbols-outlined text-[12px] text-info" aria-hidden="true">receipt_long</span>
                <span>All System Txns</span>
            </div>
        </div>
    </div>

    <!-- Card 3: Total Deposits -->
    <div class="ds-card p-5 sm:p-6 flex flex-col items-start justify-center group relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
        <div class="absolute -right-6 -top-6 w-24 h-24 bg-emerald-500/10 rounded-full blur-2xl group-hover:bg-emerald-500/20 transition-all"></div>
        <div class="relative z-10 w-full">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-emerald-500/20 to-emerald-500/5 text-emerald-500 flex items-center justify-center mb-3 border border-emerald-500/20 shadow-[inset_0_1px_1px_rgba(255,255,255,0.4)] dark:shadow-[inset_0_1px_1px_rgba(255,255,255,0.1)]">
                <span class="material-symbols-outlined text-2xl" aria-hidden="true">south_west</span>
            </div>
            <div class="text-[11px] font-bold text-text-secondary uppercase tracking-[0.15em] mb-1">Total Deposits</div>
            <div class="text-2xl sm:text-3xl font-black font-display tracking-tight leading-none text-emerald-500 skeleton min-w-[50px]" id="stat-total-deposits">₹0</div>
            <div class="text-[10px] font-bold text-text-secondary uppercase tracking-widest mt-2 flex items-center gap-1">
                <span class="material-symbols-outlined text-[12px] text-emerald-500" aria-hidden="true">arrow_downward</span>
                <span>Inflows & Funding</span>
            </div>
        </div>
    </div>

    <!-- Card 4: Total Withdrawals -->
    <div class="ds-card p-5 sm:p-6 flex flex-col items-start justify-center group relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
        <div class="absolute -right-6 -top-6 w-24 h-24 bg-rose-500/10 rounded-full blur-2xl group-hover:bg-rose-500/20 transition-all"></div>
        <div class="relative z-10 w-full">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-rose-500/20 to-rose-500/5 text-rose-500 flex items-center justify-center mb-3 border border-rose-500/20 shadow-[inset_0_1px_1px_rgba(255,255,255,0.4)] dark:shadow-[inset_0_1px_1px_rgba(255,255,255,0.1)]">
                <span class="material-symbols-outlined text-2xl" aria-hidden="true">north_east</span>
            </div>
            <div class="text-[11px] font-bold text-text-secondary uppercase tracking-[0.15em] mb-1">Total Withdrawals</div>
            <div class="text-2xl sm:text-3xl font-black font-display tracking-tight leading-none text-rose-500 skeleton min-w-[50px]" id="stat-total-withdrawals">₹0</div>
            <div class="text-[10px] font-bold text-text-secondary uppercase tracking-widest mt-2 flex items-center gap-1">
                <span class="material-symbols-outlined text-[12px] text-rose-500" aria-hidden="true">arrow_upward</span>
                <span>Outflows & OTC Cash</span>
            </div>
        </div>
    </div>

    <!-- Card 5: Transfers & Interest -->
    <div class="ds-card p-5 sm:p-6 flex flex-col items-start justify-center group relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl col-span-2 lg:col-span-1">
        <div class="absolute -right-6 -top-6 w-24 h-24 bg-amber-500/10 rounded-full blur-2xl group-hover:bg-amber-500/20 transition-all"></div>
        <div class="relative z-10 w-full">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-amber-500/20 to-amber-500/5 text-amber-500 flex items-center justify-center mb-3 border border-amber-500/20 shadow-[inset_0_1px_1px_rgba(255,255,255,0.4)] dark:shadow-[inset_0_1px_1px_rgba(255,255,255,0.1)]">
                <span class="material-symbols-outlined text-2xl" aria-hidden="true">sync_alt</span>
            </div>
            <div class="text-[11px] font-bold text-text-secondary uppercase tracking-[0.15em] mb-1">Transfers & Interest</div>
            <div class="text-2xl sm:text-3xl font-black font-display tracking-tight leading-none text-amber-500 skeleton min-w-[50px]" id="stat-total-transfers">₹0</div>
            <div class="text-[10px] font-bold text-text-secondary uppercase tracking-widest mt-2 flex items-center gap-1">
                <span class="material-symbols-outlined text-[12px] text-amber-500" aria-hidden="true">percent</span>
                <span>Internal Movement</span>
            </div>
        </div>
    </div>
</div>

<!-- FILTERS & DATA TABLE CONTAINER -->
<div class="ds-card overflow-hidden relative z-10 shadow-2xl transition-all duration-300 print:border-none print:shadow-none">
    <div class="absolute -right-20 -top-20 w-64 h-64 bg-primary/5 rounded-full blur-3xl -z-10 pointer-events-none print:hidden"></div>
    <div class="absolute -left-20 -bottom-20 w-64 h-64 bg-accent/5 rounded-full blur-3xl -z-10 pointer-events-none print:hidden"></div>
    
    <!-- TYPE SEGMENTED TABS -->
    <div class="px-5 py-3.5 border-b border-border-subtle bg-surface/50 backdrop-blur-md flex flex-wrap items-center justify-between gap-3 print:hidden">
        <div class="flex flex-wrap items-center gap-2">
            <button class="type-pill px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-200 bg-primary text-text-inverse shadow-md shadow-primary/20" data-type="all">
                <span class="flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[15px]" aria-hidden="true">all_inclusive</span>
                    <span>All Types</span>
                </span>
            </button>
            <button class="type-pill px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-200 bg-surface border border-border-subtle text-text-secondary hover:text-text-primary hover:bg-surface-hover" data-type="deposit">
                <span class="flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[15px] text-emerald-500" aria-hidden="true">south_west</span>
                    <span>Deposits</span>
                </span>
            </button>
            <button class="type-pill px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-200 bg-surface border border-border-subtle text-text-secondary hover:text-text-primary hover:bg-surface-hover" data-type="withdrawal">
                <span class="flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[15px] text-rose-500" aria-hidden="true">north_east</span>
                    <span>Withdrawals</span>
                </span>
            </button>
            <button class="type-pill px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-200 bg-surface border border-border-subtle text-text-secondary hover:text-text-primary hover:bg-surface-hover" data-type="transfer">
                <span class="flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[15px] text-sky-500" aria-hidden="true">sync_alt</span>
                    <span>Transfers</span>
                </span>
            </button>
            <button class="type-pill px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-200 bg-surface border border-border-subtle text-text-secondary hover:text-text-primary hover:bg-surface-hover" data-type="interest_credit">
                <span class="flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[15px] text-amber-500" aria-hidden="true">percent</span>
                    <span>Interest Credits</span>
                </span>
            </button>
        </div>

        <div class="flex items-center gap-2">
            <!-- Active Filter Reset Button -->
            <button id="reset-filters-btn" class="btn btn-secondary px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider rounded-lg text-text-secondary hover:text-text-primary flex items-center gap-1 transition-all">
                <span class="material-symbols-outlined text-[14px]" aria-hidden="true">filter_alt_off</span>
                <span>Reset Filters</span>
            </button>
        </div>
    </div>

    <!-- ADVANCED FILTER BAR -->
    <div class="p-5 sm:p-6 border-b border-border-subtle bg-background/60 backdrop-blur-xl relative z-10 print:hidden">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 items-end">
            <!-- Search -->
            <div class="lg:col-span-4">
                <label for="transaction-search" class="fin-label mb-2 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Search Transactions</label>
                <div class="relative group">
                    <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-text-secondary group-focus-within:text-primary transition-colors text-[18px]" aria-hidden="true">search</span>
                    <input type="text" id="transaction-search" placeholder="Search Txn ID, Name, Account, Staff ID..." class="fin-input pl-10 pr-9 w-full text-xs transition-all focus:ring-2 focus:ring-primary/20">
                    <button id="clear-search-btn" class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-text-secondary hover:text-text-primary transition-colors">
                        <span class="material-symbols-outlined text-[16px]">close</span>
                    </button>
                </div>
            </div>

            <!-- Status Dropdown -->
            <div class="lg:col-span-2">
                <label for="filter-status" class="fin-label mb-2 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Status</label>
                <div class="finzo-dropdown w-full" id="status-dropdown">
                    <div class="finzo-dropdown-trigger fin-input !flex items-center justify-between w-full !py-2 cursor-pointer">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[16px] text-text-secondary" aria-hidden="true">tune</span>
                            <span class="dropdown-label font-bold text-xs text-text-primary">All Status</span>
                        </div>
                        <span class="material-symbols-outlined chevron transition-transform duration-300 text-[18px]" aria-hidden="true">expand_more</span>
                    </div>
                    <div class="finzo-dropdown-menu !z-[3000] !w-full">
                        <div class="finzo-dropdown-option is-selected text-xs" data-value="all">All Status</div>
                        <div class="finzo-dropdown-option text-xs" data-value="completed">Completed</div>
                        <div class="finzo-dropdown-option text-xs" data-value="pending">Pending</div>
                        <div class="finzo-dropdown-option text-xs" data-value="failed">Failed</div>
                        <div class="finzo-dropdown-option text-xs" data-value="reversed">Reversed</div>
                    </div>
                    <input type="hidden" id="filter-status" value="all">
                </div>
            </div>

            <!-- Date Range: From -->
            <div class="lg:col-span-2">
                <label for="filter-date-from" class="fin-label mb-2 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Date From</label>
                <input type="date" id="filter-date-from" class="fin-input w-full text-xs !py-2">
            </div>

            <!-- Date Range: To -->
            <div class="lg:col-span-2">
                <label for="filter-date-to" class="fin-label mb-2 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Date To</label>
                <input type="date" id="filter-date-to" class="fin-input w-full text-xs !py-2">
            </div>

            <!-- Sort By Dropdown -->
            <div class="lg:col-span-2">
                <label for="filter-sort" class="fin-label mb-2 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Sort By</label>
                <div class="finzo-dropdown w-full" id="sort-dropdown">
                    <div class="finzo-dropdown-trigger fin-input !flex items-center justify-between w-full !py-2 cursor-pointer">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[16px] text-text-secondary" aria-hidden="true">sort</span>
                            <span class="dropdown-label font-bold text-xs text-text-primary">Newest First</span>
                        </div>
                        <span class="material-symbols-outlined chevron transition-transform duration-300 text-[18px]" aria-hidden="true">expand_more</span>
                    </div>
                    <div class="finzo-dropdown-menu !z-[3000] !w-full">
                        <div class="finzo-dropdown-option is-selected text-xs" data-value="newest">Newest First</div>
                        <div class="finzo-dropdown-option text-xs" data-value="oldest">Oldest First</div>
                        <div class="finzo-dropdown-option text-xs" data-value="amount_high">Highest Amount</div>
                        <div class="finzo-dropdown-option text-xs" data-value="amount_low">Lowest Amount</div>
                    </div>
                    <input type="hidden" id="filter-sort" value="newest">
                </div>
            </div>
        </div>
    </div>
    
    <!-- DATA TABLE -->
    <div class="ds-table-wrapper w-full border-0 shadow-none rounded-none bg-transparent overflow-x-auto custom-scrollbar print:overflow-visible">
        <table id="transactions-table" class="ds-table w-full text-left min-w-[1300px] print:min-w-full">
            <thead scope="col">
                <tr class="border-b border-border-subtle bg-surface-subtle/50 text-[10px] font-black uppercase tracking-wider text-text-secondary">
                    <th scope="col" class="py-3.5 px-4 w-12 text-center">#</th>
                    <th scope="col" class="py-3.5 px-4 w-36">TXN ID</th>
                    <th scope="col" class="py-3.5 px-4">From Account (Source)</th>
                    <th scope="col" class="py-3.5 px-4">To Account (Destination)</th>
                    <th scope="col" class="py-3.5 px-4 w-28">Type</th>
                    <th scope="col" class="py-3.5 px-4 w-36">Amount</th>
                    <th scope="col" class="py-3.5 px-4 w-40">Date & Time</th>
                    <th scope="col" class="py-3.5 px-4 w-28 text-center">Status</th>
                    <th scope="col" class="py-3.5 px-6 text-right w-44 print:hidden">Actions</th>
                </tr>
            </thead>
            <tbody id="txn-table-body" class="divide-y divide-border-subtle">
                <!-- Data dynamically injected by transactions.js -->
            </tbody>
        </table>
    </div>

    <!-- PRINT-ONLY FOOTER -->
    <div class="hidden print:block mt-8 pt-4 border-t border-black text-center text-xs text-black uppercase tracking-widest font-bold">
        This is an official computer-generated statement from Finzo Enterprise Banking System. No signature required.
    </div>

    <!-- PAGINATION (Web Mode) -->
    <div class="p-4 sm:p-6 bg-surface-subtle border-t border-border-subtle flex flex-col sm:flex-row justify-between items-center gap-4 print:hidden">
        <div id="pagination-info" class="text-[10px] font-bold text-text-secondary uppercase tracking-widest">Showing 0-0 of 0 Records</div>
        <div class="flex items-center gap-2">
            <button id="prev-btn" class="btn btn-secondary px-4 !py-2 !text-xs font-bold disabled:opacity-50 disabled:cursor-not-allowed">Previous</button>
            <div id="page-numbers" class="flex items-center gap-1.5">
                <!-- Page numbers injected by transactions.js -->
            </div>
            <button id="next-btn" class="btn btn-secondary px-4 !py-2 !text-xs font-bold disabled:opacity-50 disabled:cursor-not-allowed">Next</button>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- TRANSACTION DETAILS MODAL                                                 -->
<!-- ========================================================================= -->
<div id="txn-details-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm hidden p-4 transition-opacity opacity-0 print:hidden">
    <div class="ds-card w-full max-w-2xl bg-surface border border-border-subtle rounded-2xl shadow-2xl overflow-hidden transform scale-95 transition-all duration-300 max-h-[90vh] flex flex-col">
        <!-- Modal Header -->
        <div class="p-6 border-b border-border-subtle bg-surface-subtle/50 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center border border-primary/20">
                    <span class="material-symbols-outlined text-xl" aria-hidden="true">receipt_long</span>
                </div>
                <div>
                    <h3 class="text-lg font-headline font-black text-text-primary tracking-tight">Transaction Details</h3>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span id="modal-txn-id" class="text-xs font-mono font-bold text-primary">TXN---</span>
                        <button onclick="window.copyTxnId()" class="text-text-secondary hover:text-text-primary transition-colors" title="Copy Txn ID">
                            <span class="material-symbols-outlined text-[14px]">content_copy</span>
                        </button>
                    </div>
                </div>
            </div>
            <button id="close-details-modal" class="p-2 rounded-xl text-text-secondary hover:bg-surface-hover hover:text-text-primary transition-all">
                <span class="material-symbols-outlined text-xl">close</span>
            </button>
        </div>

        <!-- Modal Body (Scrollable) -->
        <div class="p-6 overflow-y-auto space-y-6 flex-1 custom-scrollbar">
            <!-- Amount & Status Overview Banner -->
            <div class="p-5 rounded-2xl bg-gradient-to-br from-surface to-surface-subtle border border-border-subtle flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1">Transaction Value</div>
                    <div id="modal-amount" class="text-3xl font-black font-display tracking-tight text-text-primary">₹0.00</div>
                    <div id="modal-date" class="text-xs font-bold text-text-secondary mt-1">---</div>
                </div>
                <div class="flex flex-col sm:items-end gap-2">
                    <div id="modal-type-badge"><span class="badge badge-info">TRANSFER</span></div>
                    <div id="modal-status-badge"><span class="badge badge-success">COMPLETED</span></div>
                </div>
            </div>

            <!-- Transfer Account-to-Account Flow Banner -->
            <div id="modal-flow-banner" class="hidden"></div>

            <!-- Sender & Beneficiary Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Source / Sender / Processing Staff Card -->
                <div class="p-4 rounded-xl bg-surface border border-border-subtle">
                    <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-3 flex items-center gap-1.5">
                        <span id="modal-from-icon" class="material-symbols-outlined text-sm text-primary">upload</span>
                        <span id="modal-from-title">Source / Sender</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div id="modal-from-avatar" class="admin-avatar sm !w-9 !h-9 !min-w-[36px] !min-h-[36px] rounded-full overflow-hidden uppercase shrink-0">U</div>
                        <div class="overflow-hidden w-full">
                            <div id="modal-from-name" class="font-bold text-sm text-text-primary truncate">Customer Name</div>
                            <div id="modal-from-acc" class="text-xs font-mono font-semibold text-text-secondary mt-0.5 truncate">FNZ000000000</div>
                            <div id="modal-from-email" class="text-[11px] text-text-muted truncate mt-0.5">email@example.com</div>
                        </div>
                    </div>
                </div>

                <!-- Destination / Beneficiary / Payout Staff Card -->
                <div class="p-4 rounded-xl bg-surface border border-border-subtle">
                    <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-3 flex items-center gap-1.5">
                        <span id="modal-to-icon" class="material-symbols-outlined text-sm text-emerald-500">download</span>
                        <span id="modal-to-title">Destination / Beneficiary</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div id="modal-to-avatar" class="admin-avatar sm !w-9 !h-9 !min-w-[36px] !min-h-[36px] rounded-full overflow-hidden uppercase shrink-0">U</div>
                        <div class="overflow-hidden w-full">
                            <div id="modal-to-name" class="font-bold text-sm text-text-primary truncate">Customer Name</div>
                            <div id="modal-to-acc" class="text-xs font-mono font-semibold text-text-secondary mt-0.5 truncate">FNZ000000000</div>
                            <div id="modal-to-email" class="text-[11px] text-text-muted truncate mt-0.5">email@example.com</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes & Description (if available) -->
            <div id="modal-reference-container" class="p-4 rounded-xl bg-surface-subtle border border-border-subtle text-xs space-y-1 hidden">
                <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest">Transaction Note / Description</div>
                <div id="modal-description" class="font-semibold text-text-primary">---</div>
                <div id="modal-reference" class="font-mono text-text-secondary text-[11px] mt-0.5 italic"></div>
            </div>
        </div>

        <!-- Modal Footer Actions -->
        <div class="p-5 border-t border-border-subtle bg-surface-subtle/50 flex items-center justify-end gap-3">
            <button id="modal-reverse-btn" class="hidden px-4 py-2 text-xs font-black uppercase tracking-wider rounded-xl flex items-center gap-1.5 bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/30 hover:bg-rose-500 hover:text-white hover:border-rose-500 transition-all active:scale-95">
                <span class="material-symbols-outlined text-[16px]">undo</span>
                <span>Reverse Transaction</span>
            </button>
            <button id="modal-receipt-btn" class="btn btn-secondary px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-xl flex items-center gap-1.5 hover:bg-primary hover:text-white transition-all">
                <span class="material-symbols-outlined text-[16px]">receipt_long</span>
                <span>Official Receipt PDF</span>
            </button>
            <button id="close-details-btn" class="btn btn-outline px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-xl">
                <span>Close</span>
            </button>
        </div>
    </div>
</div>

<?php
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/transactions.js?v=' . time() . '"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
