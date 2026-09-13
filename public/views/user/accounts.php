<?php
$portal = 'user';
$pageTitle = 'My Accounts';
ob_start();
?>
<!-- Standard Page Header -->
  <div class="mb-8 ds-animate-fade">
    <div class="text-sm font-bold text-text-muted uppercase tracking-wider mb-1">Banking</div>
    <h1 class="font-display text-3xl font-extrabold text-text-main tracking-tight">My Accounts</h1>
    <p class="text-text-muted mt-1">Unified portfolio and active wealth containers.</p>
  </div>

  <div class="w-full max-w-4xl mx-auto flex flex-col gap-8 ds-animate-slide-up">
    
    <!-- PENDING / NOTICE BANNER -->
    <div id="acc-pending-banner" class="ds-card p-4 border border-amber-500/30 bg-amber-500/10 hidden">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
          <span class="material-symbols-outlined text-amber-500" aria-hidden="true">info</span>
          <div class="text-sm font-semibold text-text-main" id="acc-pending-msg">Your account is currently awaiting verification.</div>
        </div>
        <a href="complete-profile.php" id="acc-pending-cta" class="btn btn-primary btn-sm shrink-0">Complete Profile</a>
      </div>
    </div>

    <!-- SPECIFIC ACCOUNTS -->
    <div class="ds-card border-none bg-transparent shadow-none p-0 overflow-visible">
      <div class="ds-card p-0 overflow-hidden relative shadow-md group">
        
        <!-- Premium Header -->
        <div class="p-5 sm:p-6 bg-surface-subtle border-b border-border-main flex flex-wrap items-center justify-between gap-4">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0 shadow-sm">
              <span class="material-symbols-outlined text-2xl" aria-hidden="true">account_balance</span>
            </div>
            <div>
              <div class="font-display font-bold text-text-main" id="acc-type-name">Finzo Platinum Savings</div>
              <div class="text-xs font-bold text-text-muted tracking-wide">Primary Wealth Container</div>
            </div>
          </div>
          <span id="acc-status-badge" class="badge badge-success text-xs tracking-widest uppercase">ACTIVE</span>
        </div>
        
        <!-- Card Body -->
        <div class="p-6 sm:p-8 bg-surface relative">
          <!-- Revolut-style absolute decoration -->
          <div class="absolute -top-12 -right-12 w-32 h-32 bg-surface-subtle rounded-full blur-2xl opacity-50 pointer-events-none"></div>

          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-6 mb-8 relative z-10">
            <div>
              <div class="text-xs font-bold text-text-muted uppercase tracking-widest mb-2">Ledger Balance</div>
              <div class="flex items-center gap-3">
                <div class="font-display text-4xl sm:text-5xl font-black text-text-primary dark:text-white tracking-tighter" id="acc-balance">₹0.00</div>
                <button id="toggle-balance-btn" type="button" class="text-text-muted hover:text-text-primary dark:hover:text-white transition-colors p-1.5 rounded-lg hover:bg-surface-subtle" title="Toggle balance visibility" aria-label="Toggle balance visibility">
                  <span class="material-symbols-outlined text-2xl" id="balance-eye-icon">visibility</span>
                </button>
              </div>
            </div>
            <div class="sm:text-right w-full sm:w-auto p-4 sm:p-0 rounded-lg sm:rounded-none bg-surface-subtle sm:bg-transparent border sm:border-none border-border-main">
              <div class="text-xs font-bold text-text-muted uppercase tracking-widest mb-1">Reference</div>
              <div class="flex items-center sm:justify-end gap-2">
                <div class="font-mono text-base font-bold text-text-main tracking-wider" id="acc-no">FNZ — — —</div>
                <button id="copy-account-btn" type="button" class="text-text-muted hover:text-text-primary transition-colors p-1 rounded hover:bg-surface-subtle" title="Copy Account Number" aria-label="Copy Account Number">
                  <span class="material-symbols-outlined text-base" id="copy-account-icon">content_copy</span>
                </button>
              </div>
            </div>
          </div>

          <!-- Meta Grid -->
          <div class="grid grid-cols-2 md:grid-cols-3 gap-4 pt-6 border-t border-border-main relative z-10">
            <div class="p-3 rounded-lg hover:bg-surface-subtle transition-colors">
              <div class="text-xs font-bold text-text-muted uppercase tracking-widest mb-1">Opened date</div>
              <div class="font-bold text-text-main" id="acc-opened">—</div>
            </div>
            <div class="p-3 rounded-lg hover:bg-surface-subtle transition-colors">
              <div class="text-xs font-bold text-text-muted uppercase tracking-widest mb-1">Interest APY</div>
              <div class="font-bold text-text-main" id="acc-interest">—</div>
            </div>
            <div class="p-3 rounded-lg hover:bg-surface-subtle transition-colors col-span-2 md:col-span-1">
              <div class="text-xs font-bold text-text-muted uppercase tracking-widest mb-1">Daily Limit</div>
              <div class="font-bold text-text-main" id="acc-limit">—</div>
            </div>
          </div>

          <!-- Actions -->
          <div class="mt-8 pt-6 border-t border-border-main flex flex-col sm:flex-row gap-4 relative z-10">
            <a href="account-detail.php" id="btn-acc-detail" class="btn btn-secondary flex-1 group">
              <span class="material-symbols-outlined text-text-muted group-hover:text-text-main transition-colors" aria-hidden="true">analytics</span> 
              Analytics & Details
            </a>
            <a href="transfer.php" id="btn-acc-transfer" class="btn btn-primary flex-1">
              <span class="material-symbols-outlined" aria-hidden="true">send</span> 
              Send Money
            </a>
          </div>
        </div>
      </div>
      
    </div>
  </div>


<script defer src="/Finzo/public/assets/js/user/accounts.js"></script>
<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
