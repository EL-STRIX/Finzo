<?php
$portal = 'user';
$pageTitle = 'Fund Transfer';
ob_start();
?>
<div class="mb-8 ds-animate-fade text-center max-w-2xl mx-auto">
  <div class="w-14 h-14 bg-primary/10 text-primary rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-sm ring-1 ring-primary/20">
    <span class="material-symbols-outlined text-3xl" aria-hidden="true">send</span>
  </div>
  <h1 class="font-display text-3xl font-extrabold text-text-main tracking-tight">Send Money</h1>
  <p class="text-text-muted mt-1.5 text-sm">Instant, ACID-protected digital settlement between Finzo accounts</p>
</div>

<div class="w-full max-w-2xl mx-auto flex flex-col gap-6 ds-animate-slide-up">
  
  <!-- SOURCE ACCOUNT OVERVIEW (REAL-TIME LIVE DATA) -->
  <div class="ds-card p-6 border-l-4 border-l-primary" id="source-account-card">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-border-main">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center font-bold">
          <span class="material-symbols-outlined text-xl" aria-hidden="true">account_balance_wallet</span>
        </div>
        <div>
          <div class="text-xs font-bold text-text-muted uppercase tracking-widest">Debit Source Account</div>
          <div class="flex items-center gap-2 mt-0.5">
            <span class="font-mono font-black text-text-main text-base tracking-wider" id="src-acc-no">Loading...</span>
            <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded bg-surface-subtle border border-border-main text-text-muted" id="src-acc-type">SAVINGS</span>
          </div>
        </div>
      </div>
      <div id="src-status-badge">
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-xs font-bold border border-emerald-500/20">
          <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
          Active
        </span>
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4">
      <div class="bg-surface-subtle p-3.5 rounded-xl border border-border-main">
        <div class="text-[11px] font-bold text-text-muted uppercase tracking-wider">Available Balance</div>
        <div class="font-display text-2xl font-black text-text-main mt-1" id="src-balance">₹0.00</div>
      </div>
      <div class="bg-surface-subtle p-3.5 rounded-xl border border-border-main">
        <div class="text-[11px] font-bold text-text-muted uppercase tracking-wider">Remaining Limit Today</div>
        <div class="font-display text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1" id="src-remaining-limit">₹0.00</div>
      </div>
    </div>

    <!-- Limit Usage Meter -->
    <div class="mt-4 pt-3 border-t border-border-main/60">
      <div class="w-full bg-surface-subtle h-2 rounded-full overflow-hidden border border-border-main">
        <div id="src-limit-progress" class="bg-primary h-full rounded-full transition-all duration-500" style="width: 0%"></div>
      </div>
      <div class="flex justify-between items-center text-[11px] text-text-muted mt-1.5 font-medium">
        <span>Transferred Today: <strong id="src-limit-used" class="text-text-main font-mono">₹0.00</strong></span>
        <span>Daily Cap: <strong id="src-limit-max" class="text-text-main font-mono">₹0.00</strong></span>
      </div>
    </div>
  </div>

  <!-- DESTINATION REGISTRY CARD -->
  <div class="ds-card">
    <div class="ds-card-header border-b border-border-main flex justify-between items-center px-6 py-4">
      <h3 class="font-display text-base font-bold text-text-main flex items-center gap-2">
        <span class="w-6 h-6 rounded-full bg-primary/10 text-primary text-xs flex items-center justify-center font-bold">1</span>
        Destination Registry
      </h3>
      <a href="beneficiary.php" class="text-xs font-bold text-primary hover:underline flex items-center gap-1">
        <span class="material-symbols-outlined text-sm" aria-hidden="true">group_add</span> Manage Beneficiaries
      </a>
    </div>
    <div class="ds-card-body p-6">
      
      <!-- Saved Beneficiaries Chips -->
      <div class="mb-6">
        <label class="block text-xs font-bold text-text-muted uppercase tracking-widest mb-2.5">Saved Beneficiaries</label>
        <div class="flex flex-wrap gap-2" id="beneficiary-chips">
          <div class="text-xs text-text-muted italic flex items-center gap-1.5">
            <span class="material-symbols-outlined text-sm animate-spin">sync</span> Loading beneficiaries...
          </div>
        </div>
      </div>

      <!-- Account Lookup Input -->
      <div>
        <label for="to-account" class="block text-xs font-bold text-text-muted uppercase tracking-widest mb-2">Finzo Account Identifier</label>
        <div class="flex flex-col sm:flex-row gap-3">
          <div class="relative flex-1">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-lg" aria-hidden="true">account_box</span>
            <input type="text" class="form-control pl-10 font-mono font-bold tracking-wider uppercase h-12 text-sm" id="to-account" placeholder="Enter Finzo Account No. (e.g. FNZ...)" oninput="resetLookup()" autocomplete="off"/>
          </div>
          <button class="btn btn-secondary h-12 px-6 flex items-center justify-center gap-2" id="btn-verify-identity" data-action="lookupAccount()">
            <span class="material-symbols-outlined text-lg" aria-hidden="true">verified</span>
            <span>Verify Identity</span>
          </button>
        </div>
      </div>

      <!-- Lookup Result -->
      <div id="lookup-result" class="hidden mt-4">
        <div id="lookup-found" class="hidden p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-between gap-3 animate-in fade-in slide-in-from-top-2 duration-300">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
              <span class="material-symbols-outlined text-2xl" aria-hidden="true">check_circle</span>
            </div>
            <div>
              <div class="text-sm font-bold text-text-main" id="recipient-name">Recipient Name</div>
              <div class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 tracking-wider uppercase mt-0.5">
                <span id="recipient-type">Savings</span> • Verified Finzo Account
              </div>
            </div>
          </div>
          <span class="px-2.5 py-1 rounded bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 text-[11px] font-bold uppercase tracking-wider">
            Verified
          </span>
        </div>

        <div id="lookup-not-found" class="hidden p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 flex items-center gap-3 animate-in fade-in slide-in-from-top-2 duration-300">
          <div class="w-10 h-10 rounded-full bg-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-center flex-shrink-0">
            <span class="material-symbols-outlined text-2xl" aria-hidden="true">error</span>
          </div>
          <div>
            <div class="text-sm font-bold text-rose-600 dark:text-rose-400" id="lookup-error-msg">Unrecognized Account Identifier</div>
            <div class="text-xs text-text-muted mt-0.5">Please check the account number and try again.</div>
          </div>
        </div>
      </div>
      
    </div>
  </div>

  <!-- AMOUNT & SECURITY CARD -->
  <div class="ds-card ds-animate-slide-up delay-100">
    <div class="ds-card-header border-b border-border-main px-6 py-4">
      <h3 class="font-display text-base font-bold text-text-main flex items-center gap-2">
        <span class="w-6 h-6 rounded-full bg-primary/10 text-primary text-xs flex items-center justify-center font-bold">2</span>
        Transfer Amount & Authorization
      </h3>
    </div>
    <div class="ds-card-body p-6">
      
      <!-- Hero Amount Input -->
      <div class="mb-6 text-center relative max-w-sm mx-auto">
        <label for="transfer-amount" class="block text-xs font-bold text-text-muted uppercase tracking-widest mb-3">Amount to send</label>
        <div class="relative flex items-center justify-center">
          <span class="absolute left-4 text-3xl text-text-muted font-bold select-none">₹</span>
          <input type="number" id="transfer-amount" class="w-full bg-transparent border-b-2 border-border-main focus:border-primary outline-none text-center font-display text-4xl sm:text-5xl font-black text-text-main pb-2 transition-colors placeholder:text-text-disabled" placeholder="0.00" min="1" step="0.01" oninput="updateSummary()"/>
        </div>
        
        <!-- Quick Amount Shortcuts -->
        <div class="flex flex-wrap items-center justify-center gap-1.5 mt-3.5">
          <button type="button" class="px-2.5 py-1 rounded-lg bg-surface-subtle hover:bg-primary/10 hover:text-primary text-[11px] font-bold text-text-muted border border-border-main transition-colors" onclick="quickAmount(500)">+₹500</button>
          <button type="button" class="px-2.5 py-1 rounded-lg bg-surface-subtle hover:bg-primary/10 hover:text-primary text-[11px] font-bold text-text-muted border border-border-main transition-colors" onclick="quickAmount(1000)">+₹1,000</button>
          <button type="button" class="px-2.5 py-1 rounded-lg bg-surface-subtle hover:bg-primary/10 hover:text-primary text-[11px] font-bold text-text-muted border border-border-main transition-colors" onclick="quickAmount(5000)">+₹5,000</button>
          <button type="button" class="px-2.5 py-1 rounded-lg bg-surface-subtle hover:bg-primary/10 hover:text-primary text-[11px] font-bold text-text-muted border border-border-main transition-colors" onclick="quickAmount(10000)">+₹10,000</button>
          <button type="button" class="px-2.5 py-1 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-white text-[11px] font-bold border border-primary/20 transition-colors" onclick="quickAmount('max')">Max Limit</button>
        </div>

        <div id="amount-words" class="text-xs font-semibold text-primary dark:text-primary-400 mt-3 min-h-[1.25rem]"></div>
        <div id="amount-error" class="hidden text-xs font-bold text-rose-600 dark:text-rose-400 mt-2" aria-live="polite"></div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-6 pt-6 border-t border-border-main">
        <div>
          <label for="transfer-desc" class="block text-xs font-bold text-text-muted uppercase tracking-widest mb-2">Reference Note (Optional)</label>
          <input type="text" class="form-control" id="transfer-desc" placeholder="e.g. Rent, Service Payment, Invoice" maxlength="100"/>
        </div>
        <div>
          <label for="transfer-pin" class="block text-xs font-bold text-text-muted uppercase tracking-widest mb-2">4-Digit Security PIN</label>
          <div class="relative">
            <input type="password" class="form-control font-mono tracking-widest text-center pr-10" id="transfer-pin" placeholder="••••" maxlength="4" autocomplete="off"/>
            <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-text-muted hover:text-text-main p-1 transition-colors" onclick="togglePinVisibility()" title="Toggle PIN visibility">
              <span class="material-symbols-outlined text-lg" id="pin-toggle-icon">visibility</span>
            </button>
          </div>
        </div>
      </div>
      
      <button class="btn btn-primary w-full h-14 text-base font-bold mt-8 shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2" data-action="confirmTransfer()" id="transfer-btn">
        <span class="material-symbols-outlined text-xl" aria-hidden="true">lock_open</span>
        <span>Review & Authorize Transfer</span>
      </button>
      
      <div class="flex items-center justify-center gap-2 mt-5 text-[11px] font-bold text-text-muted uppercase tracking-widest">
         <span class="material-symbols-outlined text-sm text-emerald-600" aria-hidden="true">encrypted</span>
         End-to-end encrypted • ISO 20022 Compliant • Instant Settlement
      </div>
    </div>
  </div>

</div>

<!-- CONFIRM MODAL -->
<div id="confirm-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4" role="dialog" aria-modal="true">
  <div class="ds-card max-w-md w-full p-6 sm:p-8 animate-in zoom-in-95 duration-200 shadow-2xl">
    <div class="w-14 h-14 bg-primary/10 text-primary rounded-full flex items-center justify-center mx-auto mb-4">
      <span class="material-symbols-outlined text-3xl" aria-hidden="true">verified_user</span>
    </div>
    <h3 class="font-display text-xl font-bold text-center text-text-main mb-1">Authorize Settlement</h3>
    <p class="text-xs text-text-muted text-center mb-5">Please verify the transfer details below before confirming</p>
    
    <div class="bg-surface-subtle rounded-xl border border-border-main overflow-hidden mb-6">
      <div class="p-3.5 border-b border-border-main flex justify-between items-center">
        <span class="text-xs font-bold text-text-muted uppercase tracking-wider">Debit Source</span>
        <span class="font-mono text-xs font-bold text-text-main" id="cm-from">Loading...</span>
      </div>
      <div class="p-3.5 border-b border-border-main flex justify-between items-center">
        <span class="text-xs font-bold text-text-muted uppercase tracking-wider">Credit Target</span>
        <span class="font-mono text-xs font-bold text-text-main" id="cm-to">-</span>
      </div>
      <div class="p-3.5 border-b border-border-main flex justify-between items-center">
        <span class="text-xs font-bold text-text-muted uppercase tracking-wider">Recipient Name</span>
        <span class="text-xs font-bold text-text-main text-right" id="cm-recipient">-</span>
      </div>
      <div class="p-3.5 border-b border-border-main flex justify-between items-center" id="cm-desc-row">
        <span class="text-xs font-bold text-text-muted uppercase tracking-wider">Reference Note</span>
        <span class="text-xs text-text-main text-right truncate max-w-[180px]" id="cm-desc">-</span>
      </div>
      <div class="p-3.5 border-b border-border-main flex justify-between items-center">
        <span class="text-xs font-bold text-text-muted uppercase tracking-wider">Transfer Fee</span>
        <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">₹0.00 (Free)</span>
      </div>
      <div class="p-5 flex flex-col items-center justify-center bg-primary/5 dark:bg-primary/10">
        <span class="text-[11px] font-bold text-text-muted uppercase tracking-widest mb-1">Settlement Value</span>
        <span class="font-display text-3xl font-black text-text-main font-mono" id="cm-amount">₹0.00</span>
      </div>
    </div>
    
    <div class="p-3 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-800 dark:text-amber-300 text-xs flex items-start gap-2 mb-6">
      <span class="material-symbols-outlined text-base flex-shrink-0 mt-0.5">warning</span>
      <span>Transfers are processed immediately in real-time. Once authorized, transactions cannot be reversed.</span>
    </div>
    
    <div class="flex gap-3">
      <button class="btn btn-secondary flex-1 h-12" data-action="closeModal(&#039;confirm-modal&#039;)">Cancel</button>
      <button class="btn btn-primary flex-[2] h-12 flex items-center justify-center gap-2" id="final-transfer-btn" data-action="processTransfer()">
        <span class="material-symbols-outlined text-lg" aria-hidden="true">done_all</span>
        <span>Authorize & Send</span>
      </button>
    </div>
  </div>
</div>

<script defer src="/Finzo/public/assets/js/user/transfer.js"></script>
<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
