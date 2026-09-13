<?php
$portal = 'user';
$pageTitle = 'Account Details';
ob_start();
?>
<div class="flex items-center gap-4 mb-8 ds-animate-fade">
    <a href="accounts.php" class="w-10 h-10 rounded-full hover:bg-surface-subtle flex items-center justify-center transition-colors text-text-main" aria-label="Back to Accounts">
      <span class="material-symbols-outlined" aria-hidden="true">arrow_back</span>
    </a>
    <div>
      <div class="text-sm font-bold text-text-muted uppercase tracking-wider mb-1">Banking</div>
      <h1 class="font-display text-3xl font-extrabold text-text-main tracking-tight">Account Details</h1>
    </div>
  </div>

  <div class="flex flex-col lg:flex-row gap-6 lg:gap-8">
    
    <!-- ASSET IDENTITY (LEFT COLUMN) -->
    <div class="w-full lg:w-1/3 ds-animate-slide-up">
      <div class="ds-card bg-primary-600 text-white p-6 relative overflow-hidden shadow-lg border-none min-h-[400px]">
        <!-- Decorative Grid -->
        <div class="absolute inset-0 opacity-[0.03] bg-radial-dots pointer-events-none"></div>
        <div class="absolute -top-24 -right-24 w-64 h-64 bg-white opacity-5 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-16 -left-16 w-48 h-48 bg-black opacity-10 rounded-full blur-2xl pointer-events-none"></div>
        
        <div class="relative z-10 flex flex-col h-full justify-between">
          <div>
            <div class="flex justify-between items-start mb-8">
              <div>
                <div class="text-xs font-extrabold text-white/70 uppercase tracking-widest mb-1" id="acc-type-label">Savings Account</div>
                <div class="flex items-center gap-2">
                  <div class="font-display text-4xl font-black tracking-tighter" id="acc-balance-big">₹0.00</div>
                  <button id="toggle-detail-balance-btn" type="button" class="text-white/60 hover:text-white transition-colors p-1" title="Toggle balance visibility" aria-label="Toggle balance visibility">
                    <span class="material-symbols-outlined text-2xl" id="detail-balance-eye-icon">visibility</span>
                  </button>
                </div>
                <div class="text-xs font-bold text-white/50 uppercase mt-1 tracking-wider">AVAILABLE BALANCE</div>
              </div>
              <span id="acc-detail-status-badge" class="badge badge-success !bg-white/20 !border-white/30 text-white text-xs tracking-widest py-1">ACTIVE</span>
            </div>
            
            <div class="mt-8 p-4 rounded-xl bg-black/10 border border-white/10 backdrop-blur-sm">
              <div class="flex items-center justify-between">
                <div>
                  <div class="font-mono text-lg font-bold text-white/90 tracking-widest" id="acc-no-big">FNZ — — —</div>
                  <div class="text-xs font-bold text-white/50 uppercase tracking-widest mt-1">ACCOUNT NUMBER</div>
                </div>
                <button id="copy-detail-account-btn" type="button" class="text-white/60 hover:text-white transition-colors p-1.5 rounded-lg hover:bg-white/10" title="Copy Account Number" aria-label="Copy Account Number">
                  <span class="material-symbols-outlined text-base" id="copy-detail-account-icon">content_copy</span>
                </button>
              </div>
            </div>
          </div>
          
          <div class="grid grid-cols-3 gap-2 mt-8 pt-6 border-t border-white/10">
            <div class="p-2 rounded hover:bg-white/5 transition-colors">
              <div class="text-xs font-bold text-white/50 uppercase tracking-widest mb-1">Opened</div>
              <div class="font-bold text-sm" id="acc-opened">—</div>
            </div>
            <div class="p-2 rounded hover:bg-white/5 transition-colors">
              <div class="text-xs font-bold text-white/50 uppercase tracking-widest mb-1">APY</div>
              <div class="font-bold text-sm" id="acc-interest">—</div>
            </div>
            <div class="p-2 rounded hover:bg-white/5 transition-colors">
              <div class="text-xs font-bold text-white/50 uppercase tracking-widest mb-1">Routing</div>
              <div class="font-mono text-sm font-bold">FINZ01</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ANALYTICS & LEDGER (RIGHT COLUMN) -->
    <div class="w-full lg:w-2/3 flex flex-col gap-6 lg:gap-8 ds-animate-slide-up delay-100">
      
      <!-- Daily Limit Management -->
      <div class="ds-card">
        <div class="ds-card-header border-b border-border-main flex flex-col sm:flex-row justify-between sm:items-center gap-4">
          <div>
            <h3 class="font-display text-lg font-bold text-text-main">Daily Transfer Limit</h3>
            <div class="text-sm text-text-muted">Manage your daily transaction exposure</div>
          </div>
          <div class="sm:text-right">
            <div class="text-xs font-bold text-text-muted uppercase tracking-widest mb-1">Used Today</div>
            <div class="font-display text-xl font-black text-text-primary dark:text-accent font-mono" id="used-amount">₹0</div>
          </div>
        </div>
        
        <div class="ds-card-body p-6">
          <div class="w-full h-3 bg-surface-subtle rounded-full overflow-hidden mb-3 border border-border-main relative">
            <div id="limit-bar" class="h-full bg-gradient-to-r from-primary to-accent transition-all duration-1000 ease-out" style="width:0%"></div>
          </div>
          
          <div class="flex justify-between items-center mb-8">
            <div class="text-xs font-bold text-emerald-600 dark:text-emerald-400 tracking-wide" id="limit-remaining">₹0.00 Remaining</div>
            <div class="text-xs font-bold text-text-main tracking-wide">Total Limit: <span id="limit-amount" class="font-black">₹0</span></div>
          </div>

          <div class="pt-6 border-t border-dashed border-border-main">
            <label for="new-limit" class="block text-xs font-bold text-text-muted uppercase tracking-widest mb-3">Configure New Limit</label>
            <div class="flex flex-col sm:flex-row gap-3">
              <div class="relative flex-1">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted" aria-hidden="true">currency_rupee</span>
                <input type="number" class="form-control pl-10 h-12 text-lg font-bold text-text-main" id="new-limit" placeholder="Adjust exposure limit" min="1000" max="100000" step="1000"/>
              </div>
              <button class="btn btn-primary h-12 px-8" id="btn-update-limit" onclick="updateLimit()">Apply Change</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Professional Transaction Ledger -->
      <div class="ds-card overflow-hidden">
        <div class="ds-card-header border-b border-border-main flex justify-between items-center bg-surface-subtle">
          <h3 class="font-display text-base font-bold text-text-main uppercase tracking-wider">Recent Transactions</h3>
          <a href="transactions.php" class="flex items-center gap-1 text-xs font-black text-primary hover:underline uppercase tracking-widest transition-colors">
            View Full Ledger <span class="material-symbols-outlined text-sm" aria-hidden="true">open_in_new</span>
          </a>
        </div>
        
        <div id="mini-txns" class="flex flex-col divide-y divide-border-subtle">
          <!-- Skeletons loaded initially -->
          <div class="p-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="skeleton w-10 h-10 rounded-xl"></div>
              <div><div class="skeleton w-32 h-4 mb-2"></div><div class="skeleton w-20 h-3"></div></div>
            </div>
            <div class="text-right"><div class="skeleton w-16 h-4 mb-2 ml-auto"></div><div class="skeleton w-10 h-3 ml-auto"></div></div>
          </div>
        </div>
      </div>
      
    </div>
  </div>


<script defer src="/Finzo/public/assets/js/user/account-detail.js"></script>
<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
