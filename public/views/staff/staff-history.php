<?php
$portal = 'staff';
$pageTitle = 'Finzo Staff — Transaction History';
ob_start();
?>
<!-- PAGE HEADER -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4 mb-6">
    <div>
        <h2 class="text-2xl sm:text-3xl font-black font-headline text-text-main dark:text-white tracking-tight">
            Transaction History
        </h2>
        <p class="text-text-muted dark:text-text-muted text-sm mt-1 font-medium">
            Comprehensive audit log and operational record of all transactions processed by your teller terminal.
        </p>
    </div>
    <div class="flex items-center gap-3">
        <button type="button" id="refresh-history-btn" class="flex items-center gap-2 px-4 py-2 bg-surface-hover dark:bg-white/5 rounded-xl border border-surface-border dark:border-white/10 text-xs font-bold text-text-muted dark:text-text-muted hover:bg-surface-border dark:hover:bg-white/10 transition-all cursor-pointer">
            <span class="material-symbols-outlined text-base" id="refresh-spinner" aria-hidden="true">refresh</span>
            Refresh
        </button>
        <button type="button" id="print-all-btn" class="flex items-center gap-2 px-4 py-2 btn btn-secondary text-xs bg-white dark:bg-white/5 border border-surface-border dark:border-white/10">
            <span class="material-symbols-outlined text-base" aria-hidden="true">print</span>
            Print Report
        </button>
    </div>
</div>

<!-- STATS ROW -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="ds-card p-5 border-l-4 border-l-emerald-500">
        <div class="flex justify-between items-start mb-2">
            <div class="text-xs font-black text-text-muted uppercase tracking-widest">Total Deposits Done</div>
            <span class="material-symbols-outlined text-success-base" aria-hidden="true">add_circle</span>
        </div>
        <div class="text-2xl font-black text-success-base dark:text-success-base" id="stat-deposits">₹0.00</div>
        <div class="mt-2 text-xs font-bold text-success-base dark:text-success-base bg-success-surface dark:bg-success-surface/10 px-2 py-1 rounded inline-block" id="stat-deposits-badge">Total processed</div>
    </div>
    <div class="ds-card p-5 border-l-4 border-l-rose-500">
        <div class="flex justify-between items-start mb-2">
            <div class="text-xs font-black text-text-muted uppercase tracking-widest">Total Withdrawals Done</div>
            <span class="material-symbols-outlined text-danger-base" aria-hidden="true">remove_circle</span>
        </div>
        <div class="text-2xl font-black text-danger-base dark:text-danger-base" id="stat-withdrawals">₹0.00</div>
        <div class="mt-2 text-xs font-bold text-danger-base dark:text-danger-base bg-danger-surface dark:bg-danger-surface/10 px-2 py-1 rounded inline-block" id="stat-withdrawals-badge">Total processed</div>
    </div>
    <div class="ds-card p-5 border-l-4 border-l-amber-500">
        <div class="flex justify-between items-start mb-2">
            <div class="text-xs font-black text-text-muted uppercase tracking-widest">Total Transactions</div>
            <span class="material-symbols-outlined text-warning-base" aria-hidden="true">receipt</span>
        </div>
        <div class="text-2xl font-black text-text-main dark:text-white" id="stat-transactions">0</div>
        <div class="mt-2 text-xs font-bold text-warning-base dark:text-warning-base bg-warning-surface dark:bg-warning-surface/10 px-2 py-1 rounded inline-block uppercase">All Time</div>
    </div>
    <div class="ds-card p-5 border-l-4 border-l-blue-500">
        <div class="flex justify-between items-start mb-2">
            <div class="text-xs font-black text-text-muted uppercase tracking-widest">This Month</div>
            <span class="material-symbols-outlined text-primary-base" aria-hidden="true">calendar_month</span>
        </div>
        <div class="text-2xl font-black text-text-main dark:text-white" id="stat-month-count">0</div>
        <div class="mt-2 text-xs font-bold text-info-base dark:text-primary-base bg-primary-surface dark:bg-primary-surface/10 px-2 py-1 rounded inline-block uppercase" id="stat-month-label">Month</div>
    </div>
</div>

<!-- FILTER BAR -->
<div class="ds-card p-4 sm:p-6 space-y-4 relative z-20 mb-6">
    <!-- Tabs and Action Row -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b border-surface-border dark:border-white/5 pb-4">
        <div class="flex gap-2 overflow-x-auto whitespace-nowrap scrollbar-hide pb-2 sm:pb-0" id="time-tabs-container">
            <button type="button" class="time-tab px-4 py-2 bg-warning-base text-white font-bold rounded-lg text-xs uppercase tracking-widest shadow shadow-amber-500/20" data-range="All Time">All Time</button>
            <button type="button" class="time-tab px-4 py-2 text-text-muted dark:text-text-muted font-bold hover:text-text-main dark:hover:text-white transition-all text-xs uppercase tracking-widest" data-range="Today">Today</button>
            <button type="button" class="time-tab px-4 py-2 text-text-muted dark:text-text-muted font-bold hover:text-text-main dark:hover:text-white transition-all text-xs uppercase tracking-widest" data-range="This Week">This Week</button>
            <button type="button" class="time-tab px-4 py-2 text-text-muted dark:text-text-muted font-bold hover:text-text-main dark:hover:text-white transition-all text-xs uppercase tracking-widest" data-range="This Month">This Month</button>
            <button type="button" class="time-tab px-4 py-2 text-text-muted dark:text-text-muted font-bold hover:text-text-main dark:hover:text-white transition-all text-xs uppercase tracking-widest" data-range="Custom" id="tab-custom">Custom</button>
        </div>
        
        <button type="button" id="reset-filters-btn" class="hidden text-xs font-bold text-warning-base hover:underline flex items-center gap-1">
            <span class="material-symbols-outlined text-sm" aria-hidden="true">restart_alt</span>
            Reset Filters
        </button>
    </div>
    
    <!-- Inputs Row -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="md:col-span-2 relative">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-lg" aria-hidden="true">search</span>
            <input type="text" id="search-input" placeholder="Search customer name, account no, or ref..." class="w-full fin-input pl-10 h-10 text-sm" autocomplete="off">
        </div>
        
        <div>
            <select id="filter-type" class="w-full fin-input h-10 text-sm">
                <option value="all">All Types</option>
                <option value="deposit">Deposits</option>
                <option value="withdrawal">Withdrawals</option>
            </select>
        </div>
        
        <div>
            <select id="filter-status" class="w-full fin-input h-10 text-sm">
                <option value="all">All Status</option>
                <option value="completed">Completed</option>
                <option value="pending">Pending</option>
                <option value="failed">Failed</option>
            </select>
        </div>
    </div>

    <!-- Custom Date Row (Hidden) -->
    <div id="custom-date-row" class="hidden grid grid-cols-1 md:grid-cols-4 gap-4 mt-4 pt-4 border-t border-surface-border dark:border-white/5">
        <div class="md:col-span-2"></div>
        <div>
            <label for="filter-from-date" class="block text-xs font-black text-text-muted uppercase tracking-widest mb-1">From Date</label>
            <input type="date" id="filter-from-date" class="w-full fin-input h-10 text-sm">
        </div>
        <div>
            <label for="filter-to-date" class="block text-xs font-black text-text-muted uppercase tracking-widest mb-1">To Date</label>
            <input type="date" id="filter-to-date" class="w-full fin-input h-10 text-sm">
        </div>
    </div>
</div>

<!-- HISTORY TABLE (High Density) -->
<div class="ds-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="ds-table whitespace-nowrap w-full">
            <thead>
                <tr class="bg-surface-subtle dark:bg-white/5 border-b border-surface-border dark:border-white/5 text-left">
                    <th class="p-3.5 text-xs font-black text-text-muted uppercase tracking-widest w-12 text-center" scope="col">#</th>
                    <th class="p-3.5 text-xs font-black text-text-muted uppercase tracking-widest" scope="col">Date &amp; Time</th>
                    <th class="p-3.5 text-xs font-black text-text-muted uppercase tracking-widest" scope="col">Customer / Ref</th>
                    <th class="p-3.5 text-xs font-black text-text-muted uppercase tracking-widest" scope="col">Account</th>
                    <th class="p-3.5 text-xs font-black text-text-muted uppercase tracking-widest" scope="col">Type</th>
                    <th class="p-3.5 text-xs font-black text-text-muted uppercase tracking-widest" scope="col">Amount</th>
                    <th class="p-3.5 text-xs font-black text-text-muted uppercase tracking-widest" scope="col">Status</th>
                    <th class="p-3.5 text-xs font-black text-text-muted uppercase tracking-widest text-right" scope="col">Receipt</th>
                </tr>
            </thead>
            <tbody id="transaction-table-body" class="divide-y divide-surface-border dark:divide-white/5">
                <tr><td colspan="8" class="text-center py-12 font-bold text-text-muted text-sm">Loading transactions...</td></tr>
            </tbody>
        </table>
    </div>

    <!-- PAGINATION -->
    <div class="p-4 bg-surface-subtle/50 dark:bg-white/2 border-t border-surface-border dark:border-white/5 flex flex-col sm:flex-row justify-between items-center gap-4">
        <span id="showing-meta" class="text-xs font-bold text-text-muted uppercase tracking-widest">Showing 0-0 of 0</span>
        <div class="flex items-center gap-1" id="page-buttons">
            <!-- Populated by JS -->
        </div>
    </div>
</div>

<?php
$slot = ob_get_clean();
$extraScripts = '<script defer src="/Finzo/public/assets/js/staff/staff-history.js?v=' . time() . '"></script>';
include __DIR__ . '/../layouts/app.php';
?>
