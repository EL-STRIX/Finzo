<?php
$portal = 'admin';
include __DIR__ . '/_guard.php';

$pageTitle = 'Finzo Admin — General Ledger';
$pageHeader = '
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-headline font-black text-text-primary tracking-tight">General Ledger</h1>
        <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-1">Double-entry bookkeeping, chart of accounts & journals</p>
    </div>
    <div class="flex items-center flex-wrap gap-2">
        <button id="btn-export-ledger" class="btn btn-secondary !px-4 text-xs font-bold uppercase tracking-wider flex items-center gap-1.5 cursor-pointer" title="Export journals to CSV">
            <span class="material-symbols-outlined text-base" aria-hidden="true">download</span>
            <span>Export CSV</span>
        </button>
        <button id="btn-open-period" class="btn btn-primary !px-4 text-xs font-bold uppercase tracking-wider hidden flex items-center gap-1.5 cursor-pointer">
            <span class="material-symbols-outlined text-base" aria-hidden="true">add_circle</span>
            <span>Open Period</span>
        </button>
        <button id="btn-close-period" class="btn btn-secondary !px-4 !text-warning !border-warning/40 hover:!bg-warning hover:!text-white text-xs font-bold uppercase tracking-wider flex items-center gap-1.5 cursor-pointer">
            <span class="material-symbols-outlined text-base" aria-hidden="true">event_available</span>
            <span>Close Period</span>
        </button>
        <button id="btn-refresh-ledger" class="p-2 hover:bg-surface rounded-xl text-text-secondary hover:text-text-primary border border-border-subtle transition-colors cursor-pointer" title="Refresh ledger data">
            <span class="material-symbols-outlined text-lg" aria-hidden="true">refresh</span>
        </button>
    </div>
</div>
';
ob_start();
?>

<!-- FINANCIAL SUMMARY / DOUBLE-ENTRY KPI CARDS -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <!-- TOTAL ASSETS -->
    <div class="ds-card p-5 border border-border-subtle bg-surface">
        <div class="flex items-center justify-between mb-3">
            <span class="text-[10px] font-black uppercase tracking-widest text-text-secondary">Total Assets</span>
            <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                <span class="material-symbols-outlined text-lg" aria-hidden="true">account_balance</span>
            </div>
        </div>
        <div id="kpi-total-assets" class="text-2xl font-black font-headline text-text-primary skeleton min-w-[100px]">₹0.00</div>
        <div class="text-[11px] font-medium text-text-muted mt-1">Normal debit balance</div>
    </div>

    <!-- TOTAL LIABILITIES -->
    <div class="ds-card p-5 border border-border-subtle bg-surface">
        <div class="flex items-center justify-between mb-3">
            <span class="text-[10px] font-black uppercase tracking-widest text-text-secondary">Total Liabilities</span>
            <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                <span class="material-symbols-outlined text-lg" aria-hidden="true">credit_score</span>
            </div>
        </div>
        <div id="kpi-total-liabilities" class="text-2xl font-black font-headline text-text-primary skeleton min-w-[100px]">₹0.00</div>
        <div class="text-[11px] font-medium text-text-muted mt-1">Customer deposits & obligations</div>
    </div>

    <!-- DOUBLE-ENTRY HEALTH (DEBIT = CREDIT) -->
    <div class="ds-card p-5 border border-border-subtle bg-surface">
        <div class="flex items-center justify-between mb-3">
            <span class="text-[10px] font-black uppercase tracking-widest text-text-secondary">Ledger Balance</span>
            <div id="kpi-balance-icon-box" class="w-9 h-9 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                <span id="kpi-balance-icon" class="material-symbols-outlined text-lg" aria-hidden="true">balance</span>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span id="kpi-balance-badge" class="status-badge badge-success text-xs font-bold uppercase">BALANCED</span>
            <span id="kpi-balance-variance" class="text-xs font-mono text-text-muted hidden">Diff: ₹0.00</span>
        </div>
        <div id="kpi-balance-subtitle" class="text-[11px] font-medium text-text-muted mt-2 font-mono">DR = CR: ₹0.00</div>
    </div>

    <!-- TOTAL POSTINGS / VOLUME -->
    <div class="ds-card p-5 border border-border-subtle bg-surface">
        <div class="flex items-center justify-between mb-3">
            <span class="text-[10px] font-black uppercase tracking-widest text-text-secondary">Journal Volume</span>
            <div class="w-9 h-9 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                <span class="material-symbols-outlined text-lg" aria-hidden="true">receipt_long</span>
            </div>
        </div>
        <div id="kpi-total-journals" class="text-2xl font-black font-headline text-text-primary skeleton min-w-[80px]">0</div>
        <div id="kpi-total-lines" class="text-[11px] font-medium text-text-muted mt-1">0 journal lines across all accounts</div>
    </div>
</div>

<!-- ACTIVE ACCOUNTING PERIOD BANNER -->
<div id="period-banner" class="ds-card p-5 mb-6 border border-border-subtle bg-surface flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 transition-all duration-300">
    <div class="flex items-center gap-3">
        <div id="period-icon-box" class="w-11 h-11 rounded-xl bg-primary text-white flex items-center justify-center shrink-0 shadow-sm">
            <span class="material-symbols-outlined text-xl" aria-hidden="true">calendar_month</span>
        </div>
        <div>
            <div class="text-[10px] font-black text-primary uppercase tracking-widest mb-0.5">Accounting Period Status</div>
            <div id="active-period-name" class="font-bold text-text-primary text-base skeleton min-w-[180px]">Loading...</div>
        </div>
    </div>
    <div class="flex items-center gap-4 self-end sm:self-auto">
        <div class="text-right">
            <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1">State</div>
            <span id="active-period-status" class="status-badge badge-success font-mono font-bold">OPEN</span>
        </div>
    </div>
</div>

<!-- MAIN TWO-COLUMN CONTENT (COA Left, Journals Right) -->
<div class="flex flex-col lg:flex-row gap-6">
    
    <!-- CHART OF ACCOUNTS (Left Column) -->
    <div class="w-full lg:w-1/3 shrink-0">
        <div class="ds-card h-[680px] flex flex-col border border-border-subtle bg-surface">
            <div class="p-4 border-b border-border-subtle bg-surface-subtle flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <h3 class="font-headline font-black tracking-tight flex items-center gap-2 text-text-primary">
                        <span class="material-symbols-outlined text-primary text-[20px]" aria-hidden="true">account_tree</span>
                        Chart of Accounts
                    </h3>
                    <span id="coa-count-badge" class="status-badge badge-neutral text-[10px] font-mono font-bold">0 Accounts</span>
                </div>
                <!-- Search COA Input -->
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-secondary text-sm" aria-hidden="true">search</span>
                    <input type="text" id="coa-search" placeholder="Filter accounts by code or name..." class="w-full pl-9 pr-8 py-2 bg-background border border-border-subtle rounded-xl text-xs font-medium text-text-primary placeholder:text-text-muted focus:outline-none focus:border-primary transition-colors" />
                    <button type="button" id="coa-search-clear" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-text-muted hover:text-text-primary hidden cursor-pointer" title="Clear filter">
                        <span class="material-symbols-outlined text-xs">close</span>
                    </button>
                </div>
            </div>
            <div class="p-4 flex-1 overflow-y-auto custom-scrollbar">
                <div id="coa-tree" class="space-y-4">
                    <div class="text-center py-12 text-text-muted"><span class="loader"></span></div>
                </div>
            </div>
        </div>
    </div>

    <!-- JOURNAL ENTRIES (Right Column) -->
    <div class="w-full lg:w-2/3">
        <div class="ds-card h-[680px] flex flex-col border border-border-subtle bg-surface">
            <!-- Filter & Search Toolbar -->
            <div class="p-4 border-b border-border-subtle bg-surface-subtle flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                <div>
                    <h3 class="font-headline font-black tracking-tight flex items-center gap-2 text-text-primary">
                        <span class="material-symbols-outlined text-info text-[20px]" aria-hidden="true">receipt_long</span>
                        Journal Entries
                    </h3>
                    <div id="journals-count-text" class="text-[11px] text-text-muted mt-0.5">Showing latest entries</div>
                </div>

                <div class="flex items-center flex-wrap gap-2 w-full sm:w-auto">
                    <!-- Search Journals Input -->
                    <div class="relative flex-1 sm:w-56">
                        <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-text-secondary text-sm" aria-hidden="true">search</span>
                        <input type="text" id="journals-search" placeholder="Search txn, account, memo..." class="w-full pl-8 pr-7 py-1.5 bg-background border border-border-subtle rounded-xl text-xs font-medium text-text-primary placeholder:text-text-muted focus:outline-none focus:border-primary transition-colors" />
                        <button type="button" id="journals-search-clear" class="absolute right-2 top-1/2 -translate-y-1/2 text-text-muted hover:text-text-primary hidden cursor-pointer" title="Clear search">
                            <span class="material-symbols-outlined text-xs">close</span>
                        </button>
                    </div>
                    <!-- Type Filter Dropdown -->
                    <div class="finzo-dropdown min-w-[145px] relative" id="journals-type-dropdown">
                        <input type="hidden" id="journals-type-filter" value="">
                        <div class="finzo-dropdown-trigger fin-input !h-[34px] !py-1 !px-3 !flex items-center justify-between cursor-pointer w-full text-xs font-bold bg-background border border-border-subtle rounded-xl text-text-primary hover:border-primary transition-colors">
                            <span class="dropdown-label truncate">All Lines</span>
                            <span class="material-symbols-outlined chevron text-sm transition-transform duration-300 text-text-secondary" aria-hidden="true">expand_more</span>
                        </div>
                        <div class="finzo-dropdown-menu w-full z-50">
                            <div class="finzo-dropdown-option is-selected text-xs py-2 px-3" data-value="">All Lines</div>
                            <div class="finzo-dropdown-option text-xs py-2 px-3" data-value="debit">Debit Only (DR)</div>
                            <div class="finzo-dropdown-option text-xs py-2 px-3" data-value="credit">Credit Only (CR)</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Table Container -->
            <div class="flex-1 overflow-y-auto custom-scrollbar">
                <div class="ds-table-wrapper border-0 rounded-none h-full">
                    <table class="ds-table">
                        <thead class="sticky top-0 z-10 bg-white dark:bg-surface">
                            <tr>
                                <th class="w-32">Transaction ID</th>
                                <th class="w-36">Date & Time</th>
                                <th>Account & Description</th>
                                <th class="text-right text-success w-28">Debit (DR)</th>
                                <th class="text-right text-error w-28">Credit (CR)</th>
                                <th class="text-center w-16">Action</th>
                            </tr>
                        </thead>
                        <tbody id="journals-tbody">
                            <tr><td colspan="6" class="py-16 text-center"><span class="loader"></span></td></tr>
                        </tbody>
                        <!-- Table Footer with dynamic Debits and Credits sum -->
                        <tfoot id="journals-tfoot" class="sticky bottom-0 bg-surface-subtle border-t-2 border-border-subtle font-mono text-xs hidden">
                            <tr>
                                <td colspan="3" class="text-right font-black uppercase tracking-wider text-text-secondary py-3">Page Total:</td>
                                <td id="footer-total-debit" class="text-right font-bold text-success py-3">₹0.00</td>
                                <td id="footer-total-credit" class="text-right font-bold text-error py-3">₹0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Pagination Toolbar -->
            <div id="journals-pagination" class="p-3 sm:px-6 bg-surface-subtle border-t border-border-subtle flex flex-col sm:flex-row justify-between items-center gap-3">
                <div id="journals-page-info" class="text-[10px] font-bold text-text-secondary uppercase tracking-widest">Showing 0-0 of 0 Entries</div>
                <div id="journals-page-controls" class="flex items-center gap-1">
                    <!-- Pagination injected by admin-ledger.js -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===================================================== -->
<!-- MODAL 1: CLOSE ACCOUNTING PERIOD CONFIRMATION -->
<!-- ===================================================== -->
<div id="modal-close-period" class="fixed inset-0 z-[2000] hidden items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-sm" onclick="window.FinzoLedger.closeClosePeriodModal()"></div>
    <div class="ds-card w-full max-w-md relative z-10 p-6 sm:p-8 ds-animate-scale shadow-2xl border border-border-subtle bg-surface">
        <div class="text-center">
            <div class="w-16 h-16 rounded-2xl bg-amber-500/10 text-amber-500 mx-auto mb-4 flex items-center justify-center border border-amber-500/20 shadow-sm">
                <span class="material-symbols-outlined text-3xl" aria-hidden="true">warning</span>
            </div>
            <h3 class="font-headline text-2xl font-black text-text-primary tracking-tight mb-2">Close Accounting Period</h3>
            <p class="text-xs font-medium text-text-secondary mb-4 leading-relaxed">
                You are about to close the active accounting period:
            </p>
            <div class="bg-background p-4 rounded-xl border border-border-subtle mb-6 text-left">
                <div class="text-[10px] font-black uppercase tracking-wider text-text-muted mb-1">Period Details</div>
                <div id="close-modal-period-name" class="font-bold text-sm text-text-primary mb-1">--</div>
                <div id="close-modal-period-dates" class="text-xs font-mono text-text-secondary">--</div>
                <div class="mt-3 pt-3 border-t border-border-subtle text-[11px] text-amber-600 dark:text-amber-400 font-medium">
                    ⚠️ This action is irreversible. All general ledger balances will be finalized for this accounting period.
                </div>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="window.FinzoLedger.closeClosePeriodModal()" class="btn btn-secondary flex-1 justify-center py-3 text-xs font-bold uppercase tracking-wider cursor-pointer">Cancel</button>
                <button type="button" id="btn-confirm-close-period" class="btn btn-primary flex-1 justify-center py-3 text-xs font-bold uppercase tracking-wider bg-amber-600 hover:bg-amber-700 text-white border-none shadow-md shadow-amber-900/10 cursor-pointer inline-flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-base" aria-hidden="true">lock</span>
                    <span>Confirm Close</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ===================================================== -->
<!-- MODAL 2: OPEN NEW ACCOUNTING PERIOD -->
<!-- ===================================================== -->
<div id="modal-open-period" class="fixed inset-0 z-[2000] hidden items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-sm" onclick="window.FinzoLedger.closeOpenPeriodModal()"></div>
    <div class="ds-card w-full max-w-md relative z-10 p-6 sm:p-8 ds-animate-scale shadow-2xl border border-border-subtle bg-surface">
        <div>
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                        <span class="material-symbols-outlined text-xl" aria-hidden="true">date_range</span>
                    </div>
                    <div>
                        <h3 class="font-headline text-xl font-black text-text-primary tracking-tight">Open Accounting Period</h3>
                        <p class="text-xs text-text-secondary">Start a new financial period</p>
                    </div>
                </div>
                <button type="button" onclick="window.FinzoLedger.closeOpenPeriodModal()" class="p-1 hover:bg-background rounded-lg text-text-muted hover:text-text-primary transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-lg">close</span>
                </button>
            </div>

            <form id="form-open-period" class="space-y-4">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-wider text-text-secondary mb-1">Period Name <span class="text-error">*</span></label>
                    <input type="text" id="open-period-name" placeholder="e.g. FY 2026-2027 or Q3 FY26" required class="w-full px-3.5 py-2.5 bg-background border border-border-subtle rounded-xl text-xs font-bold text-text-primary focus:outline-none focus:border-primary" />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-wider text-text-secondary mb-1">Start Date <span class="text-error">*</span></label>
                        <input type="date" id="open-period-start" required class="w-full px-3.5 py-2.5 bg-background border border-border-subtle rounded-xl text-xs font-bold text-text-primary focus:outline-none focus:border-primary" />
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-wider text-text-secondary mb-1">End Date <span class="text-error">*</span></label>
                        <input type="date" id="open-period-end" required class="w-full px-3.5 py-2.5 bg-background border border-border-subtle rounded-xl text-xs font-bold text-text-primary focus:outline-none focus:border-primary" />
                    </div>
                </div>
                <div class="bg-primary/5 p-3 rounded-xl border border-primary/10 text-[11px] text-text-muted flex items-start gap-2">
                    <span class="material-symbols-outlined text-primary text-sm shrink-0 mt-0.5">info</span>
                    <span>Transactions will be recorded and aggregated under this period. Only one period may remain active at any time.</span>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="window.FinzoLedger.closeOpenPeriodModal()" class="btn btn-secondary flex-1 justify-center py-2.5 text-xs font-bold uppercase tracking-wider cursor-pointer">Cancel</button>
                    <button type="submit" id="btn-submit-open-period" class="btn btn-primary flex-1 justify-center py-2.5 text-xs font-bold uppercase tracking-wider cursor-pointer inline-flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-base" aria-hidden="true">check_circle</span>
                        <span>Open Period</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===================================================== -->
<!-- MODAL 3: JOURNAL ENTRY DETAILS (DOUBLE-ENTRY VIEW) -->
<!-- ===================================================== -->
<div id="modal-journal-detail" class="fixed inset-0 z-[2000] hidden items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-sm" onclick="window.FinzoLedger.closeJournalModal()"></div>
    <div class="ds-card w-full max-w-2xl relative z-10 p-6 sm:p-8 ds-animate-scale shadow-2xl border border-border-subtle bg-surface">
        <div class="flex items-center justify-between pb-4 border-b border-border-subtle mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-info/10 text-info flex items-center justify-center">
                    <span class="material-symbols-outlined text-xl" aria-hidden="true">receipt</span>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 id="modal-journal-number" class="font-mono text-base font-black text-text-primary tracking-wide">JNL-XXXXXX</h3>
                        <span id="modal-journal-status" class="status-badge badge-success text-[10px] font-bold uppercase">POSTED</span>
                    </div>
                    <div id="modal-journal-date" class="text-xs text-text-secondary">--</div>
                </div>
            </div>
            <button type="button" onclick="window.FinzoLedger.closeJournalModal()" class="p-1 hover:bg-background rounded-lg text-text-muted hover:text-text-primary transition-colors cursor-pointer">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 p-3 bg-background rounded-xl border border-border-subtle mb-4 text-xs">
            <div>
                <div class="text-[10px] font-black uppercase tracking-wider text-text-muted">Reference</div>
                <div id="modal-journal-ref" class="font-mono font-bold text-text-primary truncate">--</div>
            </div>
            <div>
                <div class="text-[10px] font-black uppercase tracking-wider text-text-muted">Source Module</div>
                <div id="modal-journal-module" class="font-bold text-text-primary">--</div>
            </div>
            <div>
                <div class="text-[10px] font-black uppercase tracking-wider text-text-muted">Period</div>
                <div id="modal-journal-period" class="font-bold text-text-primary truncate">--</div>
            </div>
            <div class="col-span-2 sm:col-span-3">
                <div class="text-[10px] font-black uppercase tracking-wider text-text-muted">Description / Memo</div>
                <div id="modal-journal-desc" class="font-medium text-text-secondary mt-0.5">--</div>
            </div>
        </div>

        <div class="mb-4">
            <div class="text-[10px] font-black uppercase tracking-wider text-text-secondary mb-2 flex items-center justify-between">
                <span>Balanced Journal Lines</span>
                <span class="text-text-muted font-normal">Double-entry ledger</span>
            </div>
            <div class="border border-border-subtle rounded-xl overflow-hidden">
                <table class="w-full text-xs text-left">
                    <thead class="bg-surface-subtle border-b border-border-subtle text-[10px] uppercase tracking-wider text-text-secondary">
                        <tr>
                            <th class="py-2.5 px-3">Account</th>
                            <th class="py-2.5 px-3">Category</th>
                            <th class="py-2.5 px-3 text-right text-success">Debit (DR)</th>
                            <th class="py-2.5 px-3 text-right text-error">Credit (CR)</th>
                        </tr>
                    </thead>
                    <tbody id="modal-journal-lines-tbody" class="divide-y divide-border-subtle">
                        <!-- Injected via admin-ledger.js -->
                    </tbody>
                    <tfoot class="bg-surface-subtle font-mono text-xs font-bold border-t border-border-subtle">
                        <tr>
                            <td colspan="2" class="py-2.5 px-3 text-right uppercase tracking-wider text-text-secondary">Total:</td>
                            <td id="modal-journal-total-debit" class="py-2.5 px-3 text-right text-success font-bold">₹0.00</td>
                            <td id="modal-journal-total-credit" class="py-2.5 px-3 text-right text-error font-bold">₹0.00</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="flex justify-end pt-2">
            <button type="button" onclick="window.FinzoLedger.closeJournalModal()" class="btn btn-secondary !px-6 text-xs font-bold uppercase tracking-wider cursor-pointer">Close</button>
        </div>
    </div>
</div>

<?php
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/admin-ledger.js?v=' . time() . '"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
