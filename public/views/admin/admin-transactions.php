<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin — Global Transactions';
ob_start();
?>

<!-- PRINT-ONLY HEADER (Matches Statement / Passbook Style) -->
<div class="hidden print:block mb-6">
    <div class="flex justify-between items-start pb-4 border-b-2 border-black">
        <div>
            <div class="font-display text-3xl font-extrabold text-black">Finzo</div>
            <div class="text-xs font-bold text-black tracking-widest uppercase mt-0.5">Enterprise Banking System</div>
            <div class="text-xs text-black">Global Transaction Ledger & Audit Statement</div>
        </div>
        <div class="text-right">
            <div class="text-sm font-bold text-black uppercase tracking-wider">Official Audit Report</div>
            <div class="text-xs font-bold text-black mt-1 uppercase tracking-widest" id="print-period">Period: All Transactions</div>
            <div class="text-xs text-black uppercase tracking-widest mt-0.5">Generated: <span id="print-generated"><?= date('d M Y, h:i A') ?></span></div>
        </div>
    </div>
</div>

<!-- TOP PAGE HEADER & ACTION BAR -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 relative z-10 print:hidden">
    <div>
        <h1 class="text-2xl font-headline font-black text-text-primary tracking-tight">Global Transactions</h1>
        <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-1">Real-time ledger audit, multi-channel flow monitor, and transaction reversal control</p>
    </div>
    
    <div class="flex items-center gap-3">
        <!-- Print Statement / Passbook Button -->
        <button id="print-statement-btn" class="btn btn-secondary px-4 py-2.5 rounded-xl border border-border-subtle bg-surface/80 hover:bg-surface hover:border-primary/40 text-text-primary text-xs font-bold uppercase tracking-wider flex items-center gap-2 shadow-sm transition-all hover:shadow-md active:scale-95 group" title="Print Ledger Statement / Passbook">
            <span class="material-symbols-outlined text-[18px] text-primary transition-transform group-hover:scale-110" aria-hidden="true">print</span>
            <span>Print Statement</span>
        </button>

        <!-- Export CSV Button -->
        <button id="open-export-modal" class="btn btn-primary px-4 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider flex items-center gap-2 shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all group" title="Export Transactions as CSV">
            <span class="material-symbols-outlined text-[18px] transition-transform group-hover:-translate-y-0.5" aria-hidden="true">download</span>
            <span>Export CSV</span>
        </button>
    </div>
</div>

<!-- 5 STAT CARDS -->
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 sm:gap-6 relative z-10 mb-6 print:grid-cols-5 print:gap-2">
    <!-- Card 1: Total Volume -->
    <div class="ds-card p-5 sm:p-6 flex flex-col items-start justify-center group relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl print:border print:border-black print:shadow-none print:p-3">
        <div class="absolute -right-6 -top-6 w-24 h-24 bg-primary/10 rounded-full blur-2xl group-hover:bg-primary/20 transition-all print:hidden"></div>
        <div class="relative z-10 w-full">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-primary/20 to-primary/5 text-primary flex items-center justify-center mb-3 border border-primary/20 shadow-[inset_0_1px_1px_rgba(255,255,255,0.4)] dark:shadow-[inset_0_1px_1px_rgba(255,255,255,0.1)] print:hidden">
                <span class="material-symbols-outlined text-2xl" aria-hidden="true">account_balance</span>
            </div>
            <div class="text-[11px] font-bold text-text-secondary uppercase tracking-[0.15em] mb-1 print:text-black">Total Volume</div>
            <div class="text-2xl sm:text-3xl font-black font-display tracking-tight leading-none text-text-primary skeleton min-w-[50px] print:text-black print:text-xl" id="stat-total-volume">₹0</div>
            <div class="text-[10px] font-bold text-text-secondary uppercase tracking-widest mt-2 flex items-center gap-1 print:hidden">
                <span class="material-symbols-outlined text-[12px] text-primary" aria-hidden="true">all_inclusive</span>
                <span>Cumulative Ledger Flow</span>
            </div>
        </div>
    </div>

    <!-- Card 2: Total Transactions -->
    <div class="ds-card p-5 sm:p-6 flex flex-col items-start justify-center group relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl print:border print:border-black print:shadow-none print:p-3">
        <div class="absolute -right-6 -top-6 w-24 h-24 bg-info/10 rounded-full blur-2xl group-hover:bg-info/20 transition-all print:hidden"></div>
        <div class="relative z-10 w-full">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-info/20 to-info/5 text-info flex items-center justify-center mb-3 border border-info/20 shadow-[inset_0_1px_1px_rgba(255,255,255,0.4)] dark:shadow-[inset_0_1px_1px_rgba(255,255,255,0.1)] print:hidden">
                <span class="material-symbols-outlined text-2xl" aria-hidden="true">swap_horiz</span>
            </div>
            <div class="text-[11px] font-bold text-text-secondary uppercase tracking-[0.15em] mb-1 print:text-black">Total Records</div>
            <div class="text-2xl sm:text-3xl font-black font-display tracking-tight leading-none text-text-primary skeleton min-w-[40px] print:text-black print:text-xl" id="stat-total-count">0</div>
            <div class="text-[10px] font-bold text-text-secondary uppercase tracking-widest mt-2 flex items-center gap-1 print:hidden">
                <span class="material-symbols-outlined text-[12px] text-info" aria-hidden="true">receipt_long</span>
                <span>All System Txns</span>
            </div>
        </div>
    </div>

    <!-- Card 3: Total Deposits -->
    <div class="ds-card p-5 sm:p-6 flex flex-col items-start justify-center group relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl print:border print:border-black print:shadow-none print:p-3">
        <div class="absolute -right-6 -top-6 w-24 h-24 bg-emerald-500/10 rounded-full blur-2xl group-hover:bg-emerald-500/20 transition-all print:hidden"></div>
        <div class="relative z-10 w-full">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-emerald-500/20 to-emerald-500/5 text-emerald-500 flex items-center justify-center mb-3 border border-emerald-500/20 shadow-[inset_0_1px_1px_rgba(255,255,255,0.4)] dark:shadow-[inset_0_1px_1px_rgba(255,255,255,0.1)] print:hidden">
                <span class="material-symbols-outlined text-2xl" aria-hidden="true">south_west</span>
            </div>
            <div class="text-[11px] font-bold text-text-secondary uppercase tracking-[0.15em] mb-1 print:text-black">Total Deposits</div>
            <div class="text-2xl sm:text-3xl font-black font-display tracking-tight leading-none text-emerald-500 skeleton min-w-[50px] print:text-black print:text-xl" id="stat-total-deposits">₹0</div>
            <div class="text-[10px] font-bold text-text-secondary uppercase tracking-widest mt-2 flex items-center gap-1 print:hidden">
                <span class="material-symbols-outlined text-[12px] text-emerald-500" aria-hidden="true">arrow_downward</span>
                <span>Inflows & Funding</span>
            </div>
        </div>
    </div>

    <!-- Card 4: Total Withdrawals -->
    <div class="ds-card p-5 sm:p-6 flex flex-col items-start justify-center group relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl print:border print:border-black print:shadow-none print:p-3">
        <div class="absolute -right-6 -top-6 w-24 h-24 bg-rose-500/10 rounded-full blur-2xl group-hover:bg-rose-500/20 transition-all print:hidden"></div>
        <div class="relative z-10 w-full">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-rose-500/20 to-rose-500/5 text-rose-500 flex items-center justify-center mb-3 border border-rose-500/20 shadow-[inset_0_1px_1px_rgba(255,255,255,0.4)] dark:shadow-[inset_0_1px_1px_rgba(255,255,255,0.1)] print:hidden">
                <span class="material-symbols-outlined text-2xl" aria-hidden="true">north_east</span>
            </div>
            <div class="text-[11px] font-bold text-text-secondary uppercase tracking-[0.15em] mb-1 print:text-black">Total Withdrawals</div>
            <div class="text-2xl sm:text-3xl font-black font-display tracking-tight leading-none text-rose-500 skeleton min-w-[50px] print:text-black print:text-xl" id="stat-total-withdrawals">₹0</div>
            <div class="text-[10px] font-bold text-text-secondary uppercase tracking-widest mt-2 flex items-center gap-1 print:hidden">
                <span class="material-symbols-outlined text-[12px] text-rose-500" aria-hidden="true">arrow_upward</span>
                <span>Outflows & OTC Cash</span>
            </div>
        </div>
    </div>

    <!-- Card 5: Transfers & Interest -->
    <div class="ds-card p-5 sm:p-6 flex flex-col items-start justify-center group relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl col-span-2 lg:col-span-1 print:border print:border-black print:shadow-none print:p-3">
        <div class="absolute -right-6 -top-6 w-24 h-24 bg-amber-500/10 rounded-full blur-2xl group-hover:bg-amber-500/20 transition-all print:hidden"></div>
        <div class="relative z-10 w-full">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-amber-500/20 to-amber-500/5 text-amber-500 flex items-center justify-center mb-3 border border-amber-500/20 shadow-[inset_0_1px_1px_rgba(255,255,255,0.4)] dark:shadow-[inset_0_1px_1px_rgba(255,255,255,0.1)] print:hidden">
                <span class="material-symbols-outlined text-2xl" aria-hidden="true">sync_alt</span>
            </div>
            <div class="text-[11px] font-bold text-text-secondary uppercase tracking-[0.15em] mb-1 print:text-black">Transfers & Interest</div>
            <div class="text-2xl sm:text-3xl font-black font-display tracking-tight leading-none text-amber-500 skeleton min-w-[50px] print:text-black print:text-xl" id="stat-total-transfers">₹0</div>
            <div class="text-[10px] font-bold text-text-secondary uppercase tracking-widest mt-2 flex items-center gap-1 print:hidden">
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
        <table id="transactions-table" class="ds-table w-full text-left min-w-[1300px] print:min-w-full print:border print:border-black">
            <thead scope="col">
                <tr class="border-b border-border-subtle bg-surface-subtle/50 text-[10px] font-black uppercase tracking-wider text-text-secondary print:text-black print:border-b-2 print:border-black">
                    <th scope="col" class="py-3.5 px-4 w-12 text-center print:border-r print:border-black">#</th>
                    <th scope="col" class="py-3.5 px-4 w-36 print:border-r print:border-black">TXN ID</th>
                    <th scope="col" class="py-3.5 px-4 print:border-r print:border-black">From Account (Source)</th>
                    <th scope="col" class="py-3.5 px-4 print:border-r print:border-black">To Account (Destination)</th>
                    <th scope="col" class="py-3.5 px-4 w-28 print:border-r print:border-black">Type</th>
                    <th scope="col" class="py-3.5 px-4 w-36 print:border-r print:border-black">Amount</th>
                    <th scope="col" class="py-3.5 px-4 w-40 print:border-r print:border-black">Date & Time</th>
                    <th scope="col" class="py-3.5 px-4 w-28 text-center print:border-r print:border-black">Status</th>
                    <th scope="col" class="py-3.5 px-6 text-right w-44 print:hidden">Actions</th>
                </tr>
            </thead>
            <tbody id="txn-table-body" class="divide-y divide-border-subtle print:divide-black">
                <!-- Data dynamically injected by transactions.js -->
            </tbody>
        </table>
    </div>

    <!-- PRINT-ONLY FOOTER -->
    <div class="hidden print:block mt-8 pt-4 border-t border-black text-center text-xs text-black uppercase tracking-widest font-bold">
        This is an official computer-generated audit statement from Finzo Enterprise Banking System. No signature required.
    </div>

    <!-- PAGINATION -->
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

            <!-- Sender & Beneficiary Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Source / Sender Card -->
                <div class="p-4 rounded-xl bg-surface border border-border-subtle">
                    <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-3 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm text-primary">upload</span>
                        <span>Source / Sender</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div id="modal-from-avatar" class="admin-avatar sm uppercase">U</div>
                        <div class="overflow-hidden">
                            <div id="modal-from-name" class="font-bold text-sm text-text-primary truncate">Customer Name</div>
                            <div id="modal-from-acc" class="text-xs font-mono font-semibold text-text-secondary mt-0.5">FNZ000000000</div>
                            <div id="modal-from-email" class="text-[11px] text-text-muted truncate mt-0.5">email@example.com</div>
                        </div>
                    </div>
                </div>

                <!-- Destination / Beneficiary Card -->
                <div class="p-4 rounded-xl bg-surface border border-border-subtle">
                    <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-3 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm text-emerald-500">download</span>
                        <span>Destination / Beneficiary</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div id="modal-to-avatar" class="admin-avatar sm uppercase">U</div>
                        <div class="overflow-hidden">
                            <div id="modal-to-name" class="font-bold text-sm text-text-primary truncate">Customer Name</div>
                            <div id="modal-to-acc" class="text-xs font-mono font-semibold text-text-secondary mt-0.5">FNZ000000000</div>
                            <div id="modal-to-email" class="text-[11px] text-text-muted truncate mt-0.5">email@example.com</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Processing & Ledger Audit Details -->
            <div class="p-4 rounded-xl bg-surface border border-border-subtle space-y-3">
                <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-2 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm text-info">history</span>
                    <span>Audit & Channel Information</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                    <div>
                        <span class="text-[10px] font-bold text-text-secondary uppercase tracking-wider block">Channel / Method</span>
                        <span id="modal-channel" class="font-semibold text-text-primary">Online Banking</span>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-text-secondary uppercase tracking-wider block">Processed By</span>
                        <span id="modal-processed-by" class="font-semibold text-text-primary">Automated System</span>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-text-secondary uppercase tracking-wider block">Description</span>
                        <span id="modal-description" class="font-semibold text-text-primary truncate block">---</span>
                    </div>
                </div>

                <div id="modal-reference-container" class="pt-2 border-t border-border-subtle hidden">
                    <span class="text-[10px] font-bold text-text-secondary uppercase tracking-wider block">Reference / Notes</span>
                    <p id="modal-reference" class="text-xs text-text-primary mt-0.5 italic">---</p>
                </div>
            </div>
        </div>

        <!-- Modal Footer Actions -->
        <div class="p-5 border-t border-border-subtle bg-surface-subtle/50 flex flex-wrap items-center justify-between gap-3">
            <div id="modal-reversal-action">
                <!-- Dynamic Reverse Button if eligible -->
            </div>
            <div class="flex items-center gap-2 ml-auto">
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
</div>

<!-- ========================================================================= -->
<!-- TRANSACTION REVERSAL MODAL                                                -->
<!-- ========================================================================= -->
<div id="txn-reversal-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm hidden p-4 transition-opacity opacity-0 print:hidden">
    <div class="ds-card w-full max-w-lg bg-surface border border-border-subtle rounded-2xl shadow-2xl overflow-hidden transform scale-95 transition-all duration-300">
        <div class="p-6 border-b border-border-subtle bg-surface-subtle/50 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-500 flex items-center justify-center border border-rose-500/20">
                    <span class="material-symbols-outlined text-xl" aria-hidden="true">undo</span>
                </div>
                <div>
                    <h3 class="text-lg font-headline font-black text-text-primary tracking-tight">Reverse Transaction</h3>
                    <p class="text-[10px] font-bold text-rose-500 uppercase tracking-widest">Irreversible Admin Action</p>
                </div>
            </div>
            <button id="close-reversal-modal" class="p-2 rounded-xl text-text-secondary hover:bg-surface-hover hover:text-text-primary transition-all">
                <span class="material-symbols-outlined text-xl">close</span>
            </button>
        </div>

        <div class="p-6 space-y-4">
            <!-- Warning Banner -->
            <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 text-xs font-semibold space-y-1">
                <div class="flex items-center gap-2 font-bold text-sm">
                    <span class="material-symbols-outlined text-base">warning</span>
                    <span>Confirm Ledger Balance Reversal</span>
                </div>
                <p class="text-[11px] opacity-90">This action will debit the recipient account, restore funds to the source account, mark the transaction status as <strong>REVERSED</strong>, and record a permanent audit entry.</p>
            </div>

            <!-- Transaction Summary -->
            <div class="p-3.5 rounded-xl bg-surface-subtle border border-border-subtle flex items-center justify-between text-xs">
                <div>
                    <div class="text-[10px] font-bold text-text-secondary uppercase">Transaction Ref</div>
                    <div id="reversal-txn-id" class="font-mono font-black text-text-primary text-sm mt-0.5">TXN---</div>
                </div>
                <div class="text-right">
                    <div class="text-[10px] font-bold text-text-secondary uppercase">Amount to Reverse</div>
                    <div id="reversal-txn-amount" class="font-black text-rose-500 text-base mt-0.5">₹0.00</div>
                </div>
            </div>

            <!-- Reason Input Field -->
            <div>
                <label for="reversal-reason" class="fin-label mb-1.5 block text-[10px] font-black text-text-secondary uppercase tracking-widest">
                    Reversal Reason <span class="text-rose-500">*</span>
                </label>
                <textarea id="reversal-reason" rows="3" placeholder="Enter reason for audit record (e.g., Customer dispute, Duplicate transfer, Fraudulent request)..." class="fin-input w-full text-xs p-3 focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500" required></textarea>
                <div class="flex justify-between items-center text-[10px] text-text-secondary mt-1">
                    <span>Required for compliance audit</span>
                    <span id="reversal-char-count">0 / 255</span>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="p-5 border-t border-border-subtle bg-surface-subtle/50 flex items-center justify-end gap-3">
            <button id="cancel-reversal-btn" class="btn btn-secondary px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-xl">
                Cancel
            </button>
            <button id="confirm-reversal-btn" class="btn btn-primary !bg-rose-600 hover:!bg-rose-700 text-white px-5 py-2 text-xs font-black uppercase tracking-wider rounded-xl shadow-lg shadow-rose-600/20 transition-all flex items-center gap-2">
                <span class="material-symbols-outlined text-[16px]">undo</span>
                <span>Confirm & Reverse</span>
            </button>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- EXPORT CSV MODAL                                                          -->
<!-- ========================================================================= -->
<div id="export-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm hidden p-4 transition-opacity opacity-0 print:hidden">
    <div class="ds-card w-full max-w-md bg-surface border border-border-subtle rounded-2xl shadow-2xl overflow-hidden transform scale-95 transition-all duration-300">
        <div class="p-6 border-b border-border-subtle bg-surface-subtle/50 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center border border-primary/20">
                    <span class="material-symbols-outlined text-xl" aria-hidden="true">download</span>
                </div>
                <div>
                    <h3 class="text-lg font-headline font-black text-text-primary tracking-tight">Export Transactions</h3>
                    <p class="text-[10px] font-bold text-text-secondary uppercase tracking-widest">Download CSV Ledger</p>
                </div>
            </div>
            <button id="close-export-modal" class="p-2 rounded-xl text-text-secondary hover:bg-surface-hover hover:text-text-primary transition-all">
                <span class="material-symbols-outlined text-xl">close</span>
            </button>
        </div>

        <div class="p-6 space-y-4 text-xs">
            <p class="text-text-secondary font-medium">Export the global transactions dataset matching your active filters. The generated CSV will include full audit columns, account numbers, amounts, timestamps, and staff handlers.</p>
            <div class="p-3.5 rounded-xl bg-surface-subtle border border-border-subtle space-y-1.5">
                <div class="flex justify-between">
                    <span class="text-text-secondary font-bold uppercase text-[10px]">Filter Scope:</span>
                    <span id="export-scope-text" class="font-black text-text-primary">Current Active Filters</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-text-secondary font-bold uppercase text-[10px]">Format:</span>
                    <span class="font-bold text-text-primary">CSV (Comma Separated)</span>
                </div>
            </div>
        </div>

        <div class="p-5 border-t border-border-subtle bg-surface-subtle/50 flex items-center justify-end gap-3">
            <button id="cancel-export-btn" class="btn btn-secondary px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-xl">
                Cancel
            </button>
            <button id="start-export-btn" class="btn btn-primary px-5 py-2 text-xs font-black uppercase tracking-wider rounded-xl shadow-lg shadow-primary/20 flex items-center gap-2">
                <span class="material-symbols-outlined text-[16px]">file_download</span>
                <span>Download CSV</span>
            </button>
        </div>
    </div>
</div>

<?php
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/transactions.js?v=' . time() . '"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
