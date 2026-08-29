<?php
$portal = 'user';
$pageTitle = 'Transactions';
ob_start();
?>
<div class="mb-8 ds-animate-fade">
    <div class="text-sm font-bold text-text-muted uppercase tracking-wider mb-1">Banking</div>
    <h1 class="font-display text-3xl font-extrabold text-text-main tracking-tight">All Transactions</h1>
    <p class="text-text-muted mt-1">Complete history of all your account activity.</p>
  </div>

  <!-- FILTER BAR -->
  <div class="ds-card mb-6 ds-animate-slide-up">
    <!-- Tabs -->
    <div class="border-b border-border-main flex overflow-x-auto hide-scrollbar bg-surface-subtle">
      <button class="filter-tab active px-6 py-4 text-sm font-bold border-b-2 transition-colors border-primary-base text-primary-base-base" data-action="filterTxns(&#039;all&#039;,this)">All</button>
      <button class="filter-tab px-6 py-4 text-sm font-bold border-b-2 border-transparent text-text-muted hover:text-text-main transition-colors" data-action="filterTxns(&#039;credit&#039;,this)">Credits</button>
      <button class="filter-tab px-6 py-4 text-sm font-bold border-b-2 border-transparent text-text-muted hover:text-text-main transition-colors" data-action="filterTxns(&#039;debit&#039;,this)">Debits</button>
      <button class="filter-tab px-6 py-4 text-sm font-bold border-b-2 border-transparent text-text-muted hover:text-text-main transition-colors" data-action="filterTxns(&#039;transfer&#039;,this)">Transfers</button>
    </div>
    
    <!-- Controls -->
    <div class="p-4 sm:p-6 flex flex-col sm:flex-row gap-4 items-end">
      <div class="w-full sm:flex-1">
        <label for="search" class="block text-xs font-bold text-text-muted uppercase tracking-widest mb-2">Search</label>
        <div class="relative">
          <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted" aria-hidden="true">search</span>
          <input type="text" id="search" class="form-control pl-10 h-11" placeholder="Search transactions..." oninput="renderTable()"/>
        </div>
      </div>
      <div class="w-full sm:w-40">
        <label for="from-date" class="block text-xs font-bold text-text-muted uppercase tracking-widest mb-2">From Date</label>
        <input type="date" id="from-date" class="form-control h-11" onchange="loadTransactions()"/>
      </div>
      <div class="w-full sm:w-40">
        <label for="to-date" class="block text-xs font-bold text-text-muted uppercase tracking-widest mb-2">To Date</label>
        <input type="date" id="to-date" class="form-control h-11" onchange="loadTransactions()"/>
      </div>
      <div class="flex gap-2 w-full sm:w-auto">
        <button class="btn btn-secondary h-11 flex-1 sm:flex-none" data-action="resetFilters()">Reset</button>
        <button class="btn btn-secondary h-11 flex-1 sm:flex-none" data-action="printStatement()" title="Download Statement">
          <span class="material-symbols-outlined text-lg" aria-hidden="true">download</span>
        </button>
      </div>
    </div>
  </div>

  <!-- TABLE -->
  <div class="ds-card ds-animate-slide-up delay-100">
    <div class="ds-card-header border-b border-border-main flex justify-between items-center bg-surface-subtle">
      <h3 class="font-display text-sm font-bold text-text-main uppercase tracking-wider">Transaction History</h3>
      <span id="txn-count" class="text-xs font-bold text-text-muted bg-surface-subtle px-2 py-1 rounded-full border border-border-main">Loading...</span>
    </div>
    
    <div class="overflow-x-auto">
      <table class="ds-table">
        <thead scope="col">
          <tr>
            <th scope="col">Date & Time</th>
            <th scope="col">Transaction Details</th>
            <th class="text-right" scope="col">Amount</th>
            <th class="text-right" scope="col">Balance</th>
            <th class="text-center" scope="col">Action</th>
          </tr>
        </thead>
        <tbody id="txn-body">
          <!-- Skeletons -->
          <tr>
            <td><div class="skeleton w-24 h-4 mb-1"></div><div class="skeleton w-16 h-3"></div></td>
            <td><div class="skeleton w-32 h-4 mb-1"></div><div class="skeleton w-24 h-3"></div></td>
            <td class="text-right"><div class="skeleton w-16 h-4 ml-auto"></div></td>
            <td class="text-right"><div class="skeleton w-20 h-4 ml-auto"></div></td>
            <td class="text-center"><div class="skeleton w-8 h-8 rounded-full mx-auto"></div></td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <div class="p-4 sm:p-6 border-t border-border-main flex flex-col sm:flex-row gap-4 justify-between items-center bg-surface-subtle">
      <span id="pag-info" class="text-xs font-bold text-text-muted uppercase tracking-wider"></span>
      <div class="flex items-center gap-1" id="pag-nums"></div>
    </div>
  </div>
</div>


<script defer src="/Finzo/public/assets/js/core/utils.js"></script>
  <script defer src="/Finzo/public/assets/js/core/api.js"></script>
<script defer src="/Finzo/public/assets/js/user/transactions.js"></script>
<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>

