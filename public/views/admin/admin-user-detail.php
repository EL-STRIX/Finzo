<?php
$portal = 'admin';
$pageTitle = 'User Dossier';
ob_start();
?>

  

  <div class="admin-main">
    <!-- TOP BAR -->
    <div class="admin-topbar">
      <div class="flex items-start sm:items-center gap-3 sm:gap-4 flex-1">
        <button id="mobile-menu-btn" class="lg:hidden p-2 rounded-lg hover:bg-surface-hover dark:hover:bg-white/5 transition-colors -ml-2" aria-label="Toggle Navigation Menu">
            <span class="material-symbols-outlined force-black" aria-hidden="true">menu</span>
        </button>
        <a href="admin-users.php" class="w-10 h-10 flex items-center justify-center rounded-xl bg-surface-hover dark:bg-white/5 hover:bg-surface-border transition-all text-text-muted">
          <span class="material-symbols-outlined force-black" aria-hidden="true">arrow_back</span>
        </a>
        <div>
          <div class="text-xs sm:text-xs font-bold tracking-widest force-black uppercase">USER DETAIL</div>
          <div class="text-xl sm:text-2xl font-bold force-black tracking-tight mt-1">Arjun Malhotra</div>
        </div>
      </div>
      <div class="flex items-center gap-2 sm:gap-4">
        <div id="topbar-status-container" class="hidden sm:flex items-center gap-2 px-4 py-2 bg-success-surface dark:bg-success-surface/10 border border-success-base dark:border-success-base/20 rounded-full">
          <div class="w-2 h-2 bg-success-surface rounded-full animate-pulse"></div>
          <span class="text-xs font-black text-success-base dark:text-success-base uppercase tracking-widest">Active Account</span>
        </div>
        </div>
    </div>
    <div class="p-4 sm:p-6 lg:p-10 space-y-6 sm:space-y-10">
      <!-- PERSONAL & STATS SECTION -->
      <section class="flex flex-col lg:grid lg:grid-cols-12 gap-6 lg:gap-8">
        <!-- Main Info Card -->
        <div class="lg:col-span-8 ds-card p-5 sm:p-8 transition-all duration-300">
          <div class="flex flex-col sm:flex-row justify-between items-start gap-6 sm:mb-8 mb-6">
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4 sm:gap-6 text-center sm:text-left w-full sm:w-auto">
              <div class="h-20 w-20 sm:h-24 sm:w-24 rounded-2xl overflow-hidden ring-4 ring-slate-50 dark:ring-white/5 shadow-2xl shrink-0 mx-auto sm:mx-0">
                <div id="user-avatar-placeholder" class="w-full h-full premium-gradient-primary flex items-center justify-center text-3xl font-black text-white"><div class="skeleton w-full h-full"></div></div>
              </div>
              <div>
                <h3 id="user-name-title" class="font-headline text-2xl sm:text-3xl font-bold tracking-tighter text-black dark:text-white"><div class="skeleton w-48 h-8"></div></h3>
                <div class="flex items-center justify-center sm:justify-start gap-3 mt-1.5">
                  <span id="badge badge-danger" class="text-xs font-black text-success-base px-2 py-0.5 bg-success-surface rounded-md uppercase tracking-widest border border-success-base dark:border-success-base/20"><div class="skeleton w-24 h-4"></div></span>
                </div>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-y-8 gap-x-12 border-t border-surface-border dark:border-white/5 pt-8 text-center sm:text-left">
            <!-- Basic Info -->
            <div>
              <p class="fin-label">CUSTOMER ID</p>
              <div id="info-customer-id" class="info-value text-sm font-mono"><div class="skeleton w-32 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label">FULL NAME</p>
              <div id="info-full-name" class="info-value text-sm"><div class="skeleton w-32 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label">DATE OF BIRTH</p>
              <div id="info-dob" class="info-value text-sm"><div class="skeleton w-24 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label">GENDER</p>
              <div id="info-gender" class="info-value text-sm"><div class="skeleton w-16 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label">MARITAL STATUS</p>
              <div id="info-marital-status" class="info-value text-sm"><div class="skeleton w-16 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label">NATIONALITY</p>
              <div id="info-nationality" class="info-value text-sm"><div class="skeleton w-24 h-4"></div></div>
            </div>
            
            <!-- Family & Employment -->
            <div>
              <p class="fin-label">FATHER'S NAME</p>
              <div id="info-father-name" class="info-value text-sm"><div class="skeleton w-32 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label">MOTHER'S NAME</p>
              <div id="info-mother-name" class="info-value text-sm"><div class="skeleton w-32 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label">OCCUPATION</p>
              <div id="info-occupation" class="info-value text-sm"><div class="skeleton w-24 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label">ANNUAL INCOME</p>
              <div id="info-income" class="info-value text-sm"><div class="skeleton w-20 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label">SOURCE OF INCOME</p>
              <div id="info-source-income" class="info-value text-sm"><div class="skeleton w-24 h-4"></div></div>
            </div>

            <!-- Contact -->
            <div>
              <p class="fin-label">PHONE NUMBER</p>
              <div id="info-phone" class="info-value text-sm"><div class="skeleton w-28 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label">EMAIL ADDRESS</p>
              <div id="info-email" class="info-value text-sm break-all"><div class="skeleton w-40 h-4"></div></div>
            </div>
            
            <!-- Identification -->
            <div>
              <p class="fin-label">AADHAAR (VERIFIED ID)</p>
              <div id="info-aadhaar" class="info-value text-sm font-mono tracking-wider"><div class="skeleton w-32 h-4"></div></div>
            </div>

            <!-- Registration Meta -->
            <div>
              <p class="fin-label">SUBMITTED ON</p>
              <div id="info-submitted" class="info-value text-xs font-bold"><div class="skeleton w-32 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label">REGISTERED ON</p>
              <div id="info-registered" class="info-value text-xs font-bold"><div class="skeleton w-32 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label">LAST UPDATED</p>
              <div id="info-updated-at" class="info-value text-xs font-bold"><div class="skeleton w-32 h-4"></div></div>
            </div>

            <!-- Access Meta -->
            <div>
              <p class="fin-label">LAST LOGIN</p>
              <div id="info-last-login" class="info-value text-xs font-bold"><div class="skeleton w-32 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label">LOGIN IP</p>
              <div id="info-login-ip" class="info-value text-xs font-bold"><div class="skeleton w-32 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label text-danger-base">FAILED ATTEMPTS</p>
              <div id="info-failed-attempts" class="info-value text-xs font-bold text-danger-base dark:text-danger-base"><div class="skeleton w-8 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label text-danger-base">LOCKED UNTIL</p>
              <div id="info-locked-until" class="info-value text-xs font-bold text-danger-base dark:text-danger-base"><div class="skeleton w-32 h-4"></div></div>
            </div>

            <div id="rejection-reason-container" class="hidden sm:col-span-2 md:col-span-3">
              <p class="fin-label text-danger-base">REJECTION REASON</p>
              <p id="info-rejection-reason" class="info-value text-sm leading-tight text-danger-base dark:text-danger-base font-bold bg-danger-surface dark:bg-danger-surface/10 p-3 rounded-lg border border-danger-base dark:border-danger-base/20"></p>
            </div>
            
            <!-- Address (Full Width) -->
            <div class="sm:col-span-2 md:col-span-3">
              <p class="fin-label">RESIDENTIAL ADDRESS</p>
              <p id="info-address" class="info-value text-sm leading-tight"><div class="skeleton w-64 h-4"></div></p>
            </div>
          </div>
        </div>

        <!-- Metric Sidebar -->
        <div class="lg:col-span-4 flex flex-col gap-4 sm:gap-6">


          <!-- Governance Panel -->
          <div class="ds-card p-6">
            <h5 class="text-xs font-black text-text-muted uppercase tracking-widest mb-5 ml-1">ADMIN ACTIONS</h5>
            <div class="space-y-3">
              <button id="suspend-btn" data-action="openGovernanceModal(&#039;block&#039;)" class="w-full flex items-center justify-between p-3.5 bg-surface-subtle dark:bg-white/5 rounded-xl text-sm font-bold hover:bg-warning-surface dark:hover:bg-warning-surface/10 transition-all border border-transparent hover:border-warning-base text-text-main dark:text-text-muted">
                <div class="flex items-center gap-3">
                  <span id="suspend-btn-icon" class="material-symbols-outlined text-warning-base" aria-hidden="true">block</span>
                  <span id="suspend-btn-text">Deactivate Account</span>
                </div>
                <span class="material-symbols-outlined text-text-muted" aria-hidden="true">chevron_right</span>
              </button>
              <button data-action="openGovernanceModal(&#039;delete&#039;)" class="w-full flex items-center justify-between p-3.5 bg-surface-subtle dark:bg-white/5 rounded-xl text-sm font-bold hover:bg-danger-surface dark:hover:bg-danger-surface/10 transition-all border border-transparent hover:border-danger-base text-text-main dark:text-text-muted">
                <div class="flex items-center gap-3">
                  <span class="material-symbols-outlined text-danger-base" aria-hidden="true">delete_forever</span>
                  <span>Soft Delete User</span>
                </div>
                <span class="material-symbols-outlined text-text-muted" aria-hidden="true">chevron_right</span>
              </button>
            </div>
          </div>

          <!-- Bank Accounts Sidebar -->
          <div class="ds-card p-6">
            <h5 class="text-xs font-black text-text-muted uppercase tracking-widest mb-5 ml-1">BANK ACCOUNTS</h5>
            <div id="accounts-sidebar-list" class="space-y-4">
              <div class="skeleton w-full h-24 rounded-2xl"></div>
            </div>
          </div>
        </div>
      </section>

      <!-- TRANSACTION HISTORY SECTION -->
      <section class="ds-card overflow-hidden">
        <div class="p-5 sm:p-8 border-b border-surface-border dark:border-white/5 flex justify-between items-center bg-surface-subtle/20 dark:bg-white/5">
          <div>
            <h3 class="font-headline text-xl font-bold text-black dark:text-white">Transaction History</h3>
            <p class="text-xs font-black text-text-muted uppercase tracking-widest mt-1">Full Ledger Audit for Current Portfolio</p>
          </div>
          <div class="flex items-center gap-3">
              <button data-action="window.open(&#039;../../../api/pdf/statement.php?user_id=&#039; + new URLSearchParams(window.location.search).get(&#039;id&#039;), &#039;_blank&#039;)" class="flex items-center gap-2 py-2.5 px-5 bg-white dark:bg-surface-dark border border-surface-border dark:border-white/10 rounded-xl text-xs font-black uppercase tracking-tighter text-text-muted hover:text-primary-base dark:hover:text-white transition-all shadow-sm">
                <span class="material-symbols-outlined text-lg" aria-hidden="true">print</span>
                Print Statement
              </button>
          </div>
        </div>
        
        <div class="overflow-x-auto">
          <table class="ds-table w-full text-left min-w-[1000px]">
            <thead scope="col">
              <tr class="bg-surface-subtle/50 dark:bg-white/5">
                <th class="py-5 px-8 text-xs font-black text-text-muted uppercase tracking-widest border-b border-surface-border dark:border-white/5 w-16 text-center" scope="col">#</th>
                <th class="py-5 px-6 text-xs font-black text-text-muted uppercase tracking-widest border-b border-surface-border dark:border-white/5" scope="col">TXN ID</th>
                <th class="py-5 px-6 text-xs font-black text-text-muted uppercase tracking-widest border-b border-surface-border dark:border-white/5" scope="col">Type</th>
                <th class="py-5 px-6 text-xs font-black text-text-muted uppercase tracking-widest border-b border-surface-border dark:border-white/5" scope="col">Date & Time</th>
                <th class="py-5 px-6 text-xs font-black text-text-muted uppercase tracking-widest border-b border-surface-border dark:border-white/5 text-right" scope="col">Amount</th>
                <th class="py-5 px-6 text-xs font-black text-text-muted uppercase tracking-widest border-b border-surface-border dark:border-white/5" scope="col">Status</th>
                <th class="py-5 px-8 text-xs font-black text-text-muted uppercase tracking-widest border-b border-surface-border dark:border-white/5 text-right" scope="col">Action</th>
              </tr>
            </thead>
            <tbody id="user-transactions-body" class="divide-y divide-slate-50 dark:divide-white/5">
              <!-- Rendered by JS -->
            </tbody>
          </table>
        </div>
        
        <div class="p-6 bg-surface-subtle/20 dark:bg-white/5 flex flex-col sm:flex-row justify-between items-center gap-4">
          <div class="text-xs font-black uppercase text-text-muted tracking-widest">Showing last 5 account activities</div>
          <div class="flex items-center gap-2">
            <button class="w-9 h-9 flex items-center justify-center rounded-xl bg-surface-hover dark:bg-white/5 text-text-muted hover:text-text-muted dark:hover:text-text-muted transition-all cursor-not-allowed">
              <span class="material-symbols-outlined text-lg" aria-hidden="true">chevron_left</span>
            </button>
            <div class="flex gap-1.5">
              <button class="w-9 h-9 flex items-center justify-center rounded-xl bg-primary-base text-white text-xs font-black shadow-lg shadow-primary-base/20">1</button>
            </div>
            <button class="w-9 h-9 flex items-center justify-center rounded-xl bg-surface-hover dark:bg-white/5 text-text-muted hover:text-text-muted dark:hover:text-text-muted transition-all cursor-not-allowed">
              <span class="material-symbols-outlined text-lg" aria-hidden="true">chevron_right</span>
            </button>
          </div>
        </div>
      </section>

    </div>
  </div>

  <!-- GOVERNANCE SECURITY MODAL -->
  <div id="governance-modal" class="fixed inset-0 z-[2000] hidden" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-surface-dark/60 backdrop-blur-md"></div>
    
    <!-- Modal Container -->
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-lg">
      <div class="ds-card !p-0 overflow-hidden shadow-3xl border-none">
        <!-- Header -->
        <div id="modal-header" class="p-8 pb-4 text-center" role="dialog" aria-modal="true">
          <div id="modal-icon-container" class="w-16 h-16 rounded-full mx-auto flex items-center justify-center mb-4 transition-all duration-300" role="dialog" aria-modal="true">
            <span id="modal-icon" class="material-symbols-outlined text-4xl" aria-hidden="true">warning</span>
          </div>
          <h3 id="modal-title" class="font-headline text-2xl font-black text-black dark:text-white">Authorize Action</h3>
          <p id="modal-subtitle" class="text-sm text-text-muted font-medium mt-1">Secure administrative override required</p>
        </div>

        <div class="px-8 py-4 space-y-6">
          <!-- Selection Grid -->
          <div>
            <label class="block text-xs font-black uppercase text-text-muted mb-3 ml-1">Select Primary Reason</label>
            <div class="grid grid-cols-2 gap-3" id="reason-grid">
              <!-- Reasons will be injected by JS -->
            </div>
          </div>

          <!-- Custom Reason Area -->
          <div id="custom-reason-container" class="hidden">
            <label for="governance-notes" class="block text-xs font-black uppercase text-text-muted mb-3 ml-1">Additional Observations (Optional)</label>
            <textarea id="governance-notes" rows="3" placeholder="Explain the decision for internal auditing..."
              class="w-full bg-surface-hover dark:bg-white/5 border-none rounded-2xl text-sm font-bold p-4 focus:ring-4 focus:ring-primary/5 transition-all"></textarea>
          </div>

          <!-- Auth Step -->
          <div id="auth-step" class="pt-4 border-t border-surface-border dark:border-white/5">
            <label for="admin-password" class="block text-xs font-black uppercase text-text-muted mb-3 ml-1">Confirm Identity (Admin Password)</label>
            <div class="relative">
              <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-text-muted" aria-hidden="true">admin_panel_settings</span>
              <input type="password" id="admin-password" placeholder="••••••••"
                class="w-full pl-12 pr-4 py-3 bg-surface-hover dark:bg-white/5 border-none rounded-2xl text-lg font-black tracking-widest focus:ring-4 focus:ring-primary/5 transition-all">
            </div>
            <p id="auth-error" class="text-xs font-black text-danger-base mt-2 ml-1 hidden">Invalid administrative credentials. Access denied.</p>
          </div>
        </div>

        <!-- Footer Actions -->
        <div class="p-8 pt-4 flex gap-3">
          <button data-action="closeGovernanceModal()" class="flex-1 py-4 bg-surface-hover dark:bg-white/5 hover:bg-surface-border dark:hover:bg-white/10 text-text-muted font-black rounded-2xl text-xs uppercase tracking-widest transition-all">Abort Action</button>
          <button id="confirm-governance-btn" data-action="executeGovernanceAction()" class="flex-1 py-4 text-white font-black rounded-2xl text-xs uppercase tracking-widest shadow-lg hover:scale-[1.02] active:scale-[0.98] transition-all">Confirm Change</button>
        </div>
      </div>
  <script defer src="/Finzo/public/assets/js/admin/user-detail.js"></script>
    <script defer src="/Finzo/public/assets/js/core/component-loader.js"></script>
<?php
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/user-detail.js"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>