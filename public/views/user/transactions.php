<?php
$portal = 'user';
$pageTitle = 'Transactions';
ob_start();
?>
<div class="mb-8 ds-animate-fade flex flex-col md:flex-row md:items-center md:justify-between gap-4">
  <div>
    <div class="text-sm font-bold text-text-muted uppercase tracking-wider mb-1">Banking</div>
    <h1 class="font-display text-3xl font-extrabold text-text-main tracking-tight">All Transactions</h1>
    <p class="text-text-muted mt-1 text-sm">Real-time ledger of all deposits, transfers, and digital account settlements.</p>
  </div>
  <div class="flex items-center gap-3">
    <button class="btn btn-secondary h-11 px-4 flex items-center gap-2" onclick="exportTransactionsCsv()" title="Export filtered transactions as CSV">
      <span class="material-symbols-outlined text-lg text-primary" aria-hidden="true">csv</span>
      <span class="text-xs font-bold">Export CSV</span>
    </button>
  </div>
</div>

<!-- REAL-TIME SUMMARY METRICS -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6 ds-animate-slide-up">
  <div class="ds-card p-5 border-l-4 border-l-emerald-500">
    <div class="flex items-center justify-between">
      <span class="text-xs font-bold text-text-muted uppercase tracking-wider">Total Inflow</span>
      <span class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
        <span class="material-symbols-outlined text-lg">arrow_downward</span>
      </span>
    </div>
    <div class="font-display text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-2 font-mono" id="overview-credits">₹0.00</div>
    <div class="text-[11px] text-text-muted mt-1">Deposits & incoming transfers</div>
  </div>

  <div class="ds-card p-5 border-l-4 border-l-rose-500">
    <div class="flex items-center justify-between">
      <span class="text-xs font-bold text-text-muted uppercase tracking-wider">Total Outflow</span>
      <span class="w-8 h-8 rounded-lg bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center font-bold">
        <span class="material-symbols-outlined text-lg">arrow_upward</span>
      </span>
    </div>
    <div class="font-display text-2xl font-black text-text-main mt-2 font-mono" id="overview-debits">₹0.00</div>
    <div class="text-[11px] text-text-muted mt-1">Debits & outgoing payments</div>
  </div>

  <div class="ds-card p-5 border-l-4 border-l-primary">
    <div class="flex items-center justify-between">
      <span class="text-xs font-bold text-text-muted uppercase tracking-wider">Ledger Balance</span>
      <span class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center font-bold">
        <span class="material-symbols-outlined text-lg">account_balance_wallet</span>
      </span>
    </div>
    <div class="font-display text-2xl font-black text-text-main mt-2 font-mono" id="overview-balance">₹0.00</div>
    <div class="text-[11px] text-text-muted mt-1" id="overview-acc-no">Loading account...</div>
  </div>

  <div class="ds-card p-5 border-l-4 border-l-indigo-500">
    <div class="flex items-center justify-between">
      <span class="text-xs font-bold text-text-muted uppercase tracking-wider">Records Found</span>
      <span class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">
        <span class="material-symbols-outlined text-lg">receipt_long</span>
      </span>
    </div>
    <div class="font-display text-2xl font-black text-text-main mt-2" id="overview-count">0</div>
    <div class="text-[11px] text-text-muted mt-1">Matching current filter criteria</div>
  </div>
</div>

<!-- FILTER BAR -->
<div class="ds-card mb-6 ds-animate-slide-up">
  <!-- Tabs -->
  <div class="border-b border-border-main flex overflow-x-auto hide-scrollbar bg-surface-subtle px-2 pt-2">
    <button class="filter-tab active px-5 py-3 text-xs font-bold border-b-2 transition-colors border-primary text-primary flex items-center gap-1.5" data-tab="all" onclick="setFilterTab('all', this)">
      <span class="material-symbols-outlined text-base">apps</span> All Activity
    </button>
    <button class="filter-tab px-5 py-3 text-xs font-bold border-b-2 border-transparent text-text-muted hover:text-text-main transition-colors flex items-center gap-1.5" data-tab="credit" onclick="setFilterTab('credit', this)">
      <span class="material-symbols-outlined text-base text-emerald-500">arrow_downward</span> Credits & Deposits
    </button>
    <button class="filter-tab px-5 py-3 text-xs font-bold border-b-2 border-transparent text-text-muted hover:text-text-main transition-colors flex items-center gap-1.5" data-tab="debit" onclick="setFilterTab('debit', this)">
      <span class="material-symbols-outlined text-base text-rose-500">arrow_upward</span> Debits & Withdrawals
    </button>
    <button class="filter-tab px-5 py-3 text-xs font-bold border-b-2 border-transparent text-text-muted hover:text-text-main transition-colors flex items-center gap-1.5" data-tab="transfer" onclick="setFilterTab('transfer', this)">
      <span class="material-symbols-outlined text-base text-indigo-500">sync_alt</span> Fund Transfers
    </button>
  </div>
  
  <!-- Controls -->
  <div class="p-4 sm:p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 items-end">
    <div class="lg:col-span-6">
      <label for="search" class="block text-xs font-bold text-text-muted uppercase tracking-widest mb-1.5">Search Transactions</label>
      <div class="relative">
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-lg" aria-hidden="true">search</span>
        <input type="text" id="search" class="form-control pl-10 h-11 text-sm" placeholder="Search by description, ID, or recipient..." oninput="onSearchInput()"/>
        <button type="button" id="search-clear-btn" class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-text-muted hover:text-text-main" onclick="clearSearch()">
          <span class="material-symbols-outlined text-base">close</span>
        </button>
      </div>
    </div>
    
    <div class="lg:col-span-2">
      <label for="from-date" class="block text-xs font-bold text-text-muted uppercase tracking-widest mb-1.5">From Date</label>
      <input type="date" id="from-date" class="form-control h-11 text-sm" onchange="onDateChange()"/>
    </div>
    
    <div class="lg:col-span-2">
      <label for="to-date" class="block text-xs font-bold text-text-muted uppercase tracking-widest mb-1.5">To Date</label>
      <input type="date" id="to-date" class="form-control h-11 text-sm" onchange="onDateChange()"/>
    </div>

    <div class="lg:col-span-2">
      <button class="btn btn-secondary w-full h-11 flex items-center justify-center gap-1.5 text-xs font-bold" onclick="resetFilters()">
        <span class="material-symbols-outlined text-base">restart_alt</span>
        <span>Reset</span>
      </button>
    </div>
  </div>
</div>

<!-- TRANSACTIONS TABLE CARD -->
<div class="ds-card ds-animate-slide-up delay-100">
  <div class="ds-card-header border-b border-border-main flex justify-between items-center bg-surface-subtle px-6 py-4">
    <div class="flex items-center gap-2.5">
      <h3 class="font-display text-sm font-bold text-text-main uppercase tracking-wider">Transaction Ledger</h3>
      <button class="p-1 rounded-lg text-text-muted hover:text-primary transition-colors" onclick="loadTransactions()" title="Refresh records">
        <span class="material-symbols-outlined text-base" id="refresh-icon">sync</span>
      </button>
    </div>
    <span id="txn-count" class="text-xs font-bold text-text-muted bg-surface px-2.5 py-1 rounded-full border border-border-main">Loading...</span>
  </div>
  
  <div class="overflow-x-auto">
    <table class="ds-table w-full">
      <thead>
        <tr class="text-left text-xs font-bold text-text-muted uppercase tracking-wider border-b border-border-main bg-surface-subtle">
          <th class="p-4" scope="col">Date & Time</th>
          <th class="p-4" scope="col">Details & Counterparty</th>
          <th class="p-4" scope="col">Type</th>
          <th class="p-4" scope="col">Reference ID</th>
          <th class="p-4 text-right" scope="col">Amount</th>
          <th class="p-4 text-right" scope="col">Balance</th>
          <th class="p-4 text-center" scope="col">Status</th>
          <th class="p-4 text-center" scope="col">Receipt</th>
        </tr>
      </thead>
      <tbody id="txn-body" class="divide-y divide-border-main">
        <!-- Skeleton Loading Rows -->
        <tr>
          <td class="p-4"><div class="skeleton w-24 h-4 mb-1"></div><div class="skeleton w-16 h-3"></div></td>
          <td class="p-4"><div class="skeleton w-36 h-4 mb-1"></div><div class="skeleton w-24 h-3"></div></td>
          <td class="p-4"><div class="skeleton w-16 h-5 rounded-full"></div></td>
          <td class="p-4"><div class="skeleton w-28 h-4 font-mono"></div></td>
          <td class="p-4 text-right"><div class="skeleton w-20 h-4 ml-auto"></div></td>
          <td class="p-4 text-right"><div class="skeleton w-20 h-4 ml-auto"></div></td>
          <td class="p-4 text-center"><div class="skeleton w-16 h-5 rounded-full mx-auto"></div></td>
          <td class="p-4 text-center"><div class="skeleton w-8 h-8 rounded-full mx-auto"></div></td>
        </tr>
      </tbody>
    </table>
  </div>
  
  <!-- Pagination Controls -->
  <div class="p-4 sm:p-6 border-t border-border-main flex flex-col sm:flex-row gap-4 justify-between items-center bg-surface-subtle">
    <span id="pag-info" class="text-xs font-bold text-text-muted uppercase tracking-wider">Showing 0 of 0</span>
    <div class="flex items-center gap-1.5" id="pag-nums"></div>
  </div>
</div>

<script defer src="/Finzo/public/assets/js/user/transactions.js?v=<?= time() ?>"></script>
<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
