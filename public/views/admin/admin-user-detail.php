<?php
$portal = 'admin';
$pageTitle = 'User Dossier';
$pageHeader = '
<div class="flex items-start sm:items-center justify-between w-full">
  <div class="flex items-center gap-4">
    <a href="admin-users.php" class="w-10 h-10 flex items-center justify-center rounded-xl bg-surface border border-border-subtle hover:bg-background transition-all text-text-secondary hover:text-text-primary">
      <span class="material-symbols-outlined" aria-hidden="true">arrow_back</span>
    </a>
    <div>
      <div class="text-[10px] font-black tracking-widest uppercase text-text-secondary">USER DETAIL</div>
      <div class="text-xl sm:text-2xl font-black tracking-tight mt-0.5 text-text-primary force-black">Loading...</div>
    </div>
  </div>
  <div class="flex items-center gap-2 sm:gap-4">
    <div id="topbar-status-container" class="hidden sm:flex items-center gap-2 px-4 py-1.5 bg-success-bg border border-success-bg rounded-full">
      <div class="w-2 h-2 bg-success rounded-full animate-pulse"></div>
      <span class="text-[11px] font-black text-success uppercase tracking-widest">Loading</span>
    </div>
  </div>
</div>
';
ob_start();
?>
  <div class="admin-main">
    <div class="space-y-6 sm:space-y-8">
      <!-- PERSONAL & STATS SECTION -->
      <section class="flex flex-col lg:grid lg:grid-cols-12 gap-6 lg:gap-8">
        <!-- Main Info Card -->
        <div class="lg:col-span-8 ds-card p-5 sm:p-8 transition-all duration-300">
          <div class="flex flex-col sm:flex-row justify-between items-start gap-6 mb-8">
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4 sm:gap-6 text-center sm:text-left w-full sm:w-auto">
              <div class="h-20 w-20 sm:h-24 sm:w-24 rounded-2xl overflow-hidden ring-4 ring-background shadow-lg shrink-0 mx-auto sm:mx-0">
                <div id="user-avatar-placeholder" class="w-full h-full bg-primary-50 flex items-center justify-center text-3xl font-black text-primary"><div class="skeleton w-full h-full"></div></div>
              </div>
              <div>
                <h3 id="user-name-title" class="font-headline text-2xl sm:text-3xl font-black tracking-tighter text-text-primary"><div class="skeleton w-48 h-8"></div></h3>
                <div class="flex items-center justify-center sm:justify-start gap-3 mt-2">
                  <span id="badge badge-danger" class="text-[10px] font-black text-success px-2 py-0.5 bg-success-bg rounded-md uppercase tracking-widest border border-success-bg"><div class="skeleton w-24 h-4"></div></span>
                </div>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-y-8 gap-x-8 border-t border-border-subtle pt-8 text-center sm:text-left">
            <!-- Basic Info -->
            <div>
              <p class="fin-label">CUSTOMER ID</p>
              <div id="info-customer-id" class="text-sm font-mono font-bold text-text-primary"><div class="skeleton w-32 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label">FULL NAME</p>
              <div id="info-full-name" class="text-sm font-bold text-text-primary"><div class="skeleton w-32 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label">DATE OF BIRTH</p>
              <div id="info-dob" class="text-sm font-bold text-text-primary"><div class="skeleton w-24 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label">GENDER</p>
              <div id="info-gender" class="text-sm font-bold text-text-primary"><div class="skeleton w-16 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label">MARITAL STATUS</p>
              <div id="info-marital-status" class="text-sm font-bold text-text-primary"><div class="skeleton w-16 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label">NATIONALITY</p>
              <div id="info-nationality" class="text-sm font-bold text-text-primary"><div class="skeleton w-24 h-4"></div></div>
            </div>
            
            <!-- Family & Employment -->
            <div>
              <p class="fin-label">FATHER\'S NAME</p>
              <div id="info-father-name" class="text-sm font-bold text-text-primary"><div class="skeleton w-32 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label">MOTHER\'S NAME</p>
              <div id="info-mother-name" class="text-sm font-bold text-text-primary"><div class="skeleton w-32 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label">OCCUPATION</p>
              <div id="info-occupation" class="text-sm font-bold text-text-primary"><div class="skeleton w-24 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label">ANNUAL INCOME</p>
              <div id="info-income" class="text-sm font-bold text-text-primary"><div class="skeleton w-20 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label">SOURCE OF INCOME</p>
              <div id="info-source-income" class="text-sm font-bold text-text-primary"><div class="skeleton w-24 h-4"></div></div>
            </div>

            <!-- Contact -->
            <div>
              <p class="fin-label">PHONE NUMBER</p>
              <div id="info-phone" class="text-sm font-bold text-text-primary"><div class="skeleton w-28 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label">EMAIL ADDRESS</p>
              <div id="info-email" class="text-sm font-bold text-text-primary break-all"><div class="skeleton w-40 h-4"></div></div>
            </div>
            
            <!-- Identification -->
            <div>
              <p class="fin-label">AADHAAR (VERIFIED ID)</p>
              <div id="info-aadhaar" class="text-sm font-mono font-bold tracking-wider text-text-primary"><div class="skeleton w-32 h-4"></div></div>
            </div>

            <!-- Registration Meta -->
            <div>
              <p class="fin-label">SUBMITTED ON</p>
              <div id="info-submitted" class="text-xs font-bold text-text-secondary"><div class="skeleton w-32 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label">REGISTERED ON</p>
              <div id="info-registered" class="text-xs font-bold text-text-secondary"><div class="skeleton w-32 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label">LAST UPDATED</p>
              <div id="info-updated-at" class="text-xs font-bold text-text-secondary"><div class="skeleton w-32 h-4"></div></div>
            </div>

            <!-- Access Meta -->
            <div>
              <p class="fin-label">LAST LOGIN</p>
              <div id="info-last-login" class="text-xs font-bold text-text-secondary"><div class="skeleton w-32 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label">LOGIN IP</p>
              <div id="info-login-ip" class="text-xs font-bold text-text-secondary"><div class="skeleton w-32 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label text-error">FAILED ATTEMPTS</p>
              <div id="info-failed-attempts" class="text-xs font-bold text-error"><div class="skeleton w-8 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label text-error">LOCKED UNTIL</p>
              <div id="info-locked-until" class="text-xs font-bold text-error"><div class="skeleton w-32 h-4"></div></div>
            </div>

            <div id="rejection-reason-container" class="hidden sm:col-span-2 md:col-span-3">
              <p class="fin-label text-error">REJECTION REASON</p>
              <p id="info-rejection-reason" class="text-sm leading-tight text-error font-bold bg-error-bg p-3 rounded-lg border border-error-bg"></p>
            </div>
            
            <!-- Address (Full Width) -->
            <div class="sm:col-span-2 md:col-span-3">
              <p class="fin-label">RESIDENTIAL ADDRESS</p>
              <p id="info-address" class="text-sm font-bold text-text-primary leading-tight"><div class="skeleton w-64 h-4"></div></p>
            </div>
          </div>
        </div>

        <!-- Metric Sidebar -->
        <div class="lg:col-span-4 flex flex-col gap-6">

          <!-- Governance Panel -->
          <div class="ds-card p-6">
            <h5 class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-5 ml-1">ADMIN ACTIONS</h5>
            <div class="space-y-3">
              <button id="suspend-btn" data-action="openGovernanceModal('block')" class="w-full flex items-center justify-between p-3.5 bg-background rounded-xl text-sm font-bold hover:bg-warning-bg transition-all border border-border-subtle hover:border-warning text-text-primary">
                <div class="flex items-center gap-3">
                  <span id="suspend-btn-icon" class="material-symbols-outlined text-warning" aria-hidden="true">block</span>
                  <span id="suspend-btn-text">Deactivate Account</span>
                </div>
                <span class="material-symbols-outlined text-text-secondary" aria-hidden="true">chevron_right</span>
              </button>
              <button data-action="openGovernanceModal('delete')" class="w-full flex items-center justify-between p-3.5 bg-background rounded-xl text-sm font-bold hover:bg-error-bg transition-all border border-border-subtle hover:border-error text-text-primary">
                <div class="flex items-center gap-3">
                  <span class="material-symbols-outlined text-error" aria-hidden="true">delete_forever</span>
                  <span>Soft Delete User</span>
                </div>
                <span class="material-symbols-outlined text-text-secondary" aria-hidden="true">chevron_right</span>
              </button>
            </div>
          </div>

          <!-- Bank Accounts Sidebar -->
          <div class="ds-card p-6">
            <h5 class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-5 ml-1">BANK ACCOUNTS</h5>
            <div id="accounts-sidebar-list" class="space-y-4">
              <div class="skeleton w-full h-24 rounded-2xl"></div>
            </div>
          </div>
        </div>
      </section>

      <!-- TRANSACTION HISTORY SECTION -->
      <section class="ds-card overflow-hidden mt-6 relative z-10 shadow-2xl transition-all duration-300">
        <div class="absolute -right-20 -top-20 w-64 h-64 bg-primary/5 rounded-full blur-3xl -z-10 pointer-events-none"></div>
        <div class="absolute -left-20 -bottom-20 w-64 h-64 bg-accent/5 rounded-full blur-3xl -z-10 pointer-events-none"></div>
        <div class="p-6 border-b border-border-subtle flex justify-between items-center bg-background/40 backdrop-blur-xl relative z-10">
          <div>
            <h3 class="font-headline text-lg font-black text-text-primary">Transaction History</h3>
            <p class="text-[10px] font-black text-text-secondary uppercase tracking-widest mt-1">Full Ledger Audit for Current Portfolio</p>
          </div>
          <div class="flex items-center gap-3">
              <button data-action="window.open('../../../api/pdf/statement.php?user_id=' + new URLSearchParams(window.location.search).get('id'), '_blank')" class="btn btn-secondary btn-sm inline-flex items-center gap-2">
                <span class="material-symbols-outlined text-lg" aria-hidden="true">print</span>
                Print Statement
              </button>
          </div>
        </div>
        
        <div class="ds-table-wrapper border-0 rounded-none shadow-none bg-transparent">
          <table class="ds-table w-full text-left min-w-[1000px]">
            <thead scope="col">
              <tr>
                <th class="w-16 text-center" scope="col">#</th>
                <th scope="col">TXN ID</th>
                <th scope="col">Type</th>
                <th scope="col">Date & Time</th>
                <th class="text-right" scope="col">Amount</th>
                <th scope="col">Status</th>
                <th class="text-right" scope="col">Action</th>
              </tr>
            </thead>
            <tbody id="user-transactions-body">
              <!-- Rendered by JS -->
            </tbody>
          </table>
        </div>
        
        <div class="p-6 bg-background border-t border-border-subtle flex flex-col sm:flex-row justify-between items-center gap-4">
          <div class="text-[10px] font-black uppercase text-text-secondary tracking-widest">Showing last 5 account activities</div>
          <div class="flex items-center gap-2">
            <button class="w-9 h-9 flex items-center justify-center rounded-xl bg-surface border border-border-subtle text-text-secondary transition-all cursor-not-allowed opacity-50">
              <span class="material-symbols-outlined text-lg" aria-hidden="true">chevron_left</span>
            </button>
            <div class="flex gap-1.5">
              <button class="w-9 h-9 flex items-center justify-center rounded-xl bg-primary text-white text-xs font-black shadow-sm">1</button>
            </div>
            <button class="w-9 h-9 flex items-center justify-center rounded-xl bg-surface border border-border-subtle text-text-secondary transition-all cursor-not-allowed opacity-50">
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
    <div class="absolute inset-0 bg-surface-dark/80 backdrop-blur-sm"></div>
    
    <!-- Modal Container -->
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-lg">
      <div class="ds-card !p-0 overflow-hidden shadow-2xl border-none">
        <!-- Header -->
        <div id="modal-header" class="p-8 pb-4 text-center" role="dialog" aria-modal="true">
          <div id="modal-icon-container" class="w-16 h-16 rounded-full mx-auto flex items-center justify-center mb-4 transition-all duration-300" role="dialog" aria-modal="true">
            <span id="modal-icon" class="material-symbols-outlined text-4xl" aria-hidden="true">warning</span>
          </div>
          <h3 id="modal-title" class="font-headline text-2xl font-black text-text-primary">Authorize Action</h3>
          <p id="modal-subtitle" class="text-xs text-text-secondary font-bold mt-1 uppercase tracking-widest">Secure administrative override required</p>
        </div>

        <div class="px-8 py-4 space-y-6">
          <!-- Selection Grid -->
          <div>
            <label class="block text-[10px] font-black uppercase text-text-secondary mb-3 ml-1">Select Primary Reason</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="reason-grid">
              <!-- Reasons will be injected by JS -->
            </div>
          </div>

          <!-- Custom Reason Area -->
          <div id="custom-reason-container" class="hidden">
            <label for="governance-notes" class="block text-[10px] font-black uppercase text-text-secondary mb-3 ml-1">Additional Observations (Optional)</label>
            <textarea id="governance-notes" rows="3" placeholder="Explain the decision for internal auditing..."
              class="fin-input !rounded-2xl"></textarea>
          </div>

          <!-- Auth Step -->
          <div id="auth-step" class="pt-4 border-t border-border-subtle">
            <label for="admin-password" class="block text-[10px] font-black uppercase text-text-secondary mb-3 ml-1">Confirm Identity (Admin Password)</label>
            <div class="relative">
              <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-text-secondary" aria-hidden="true">admin_panel_settings</span>
              <input type="password" id="admin-password" placeholder="••••••••"
                class="fin-input !pl-12 !py-3 !rounded-2xl !text-lg !tracking-widest">
            </div>
            <p id="auth-error" class="text-[10px] font-black text-error uppercase tracking-widest mt-2 ml-1 hidden">Invalid administrative credentials. Access denied.</p>
          </div>
        </div>

        <!-- Footer Actions -->
        <div class="p-8 pt-4 flex gap-3">
          <button data-action="closeGovernanceModal()" class="flex-1 py-4 bg-background hover:bg-surface-hover text-text-secondary font-black rounded-2xl text-[10px] uppercase tracking-widest transition-all border border-border-subtle">Abort Action</button>
          <button id="confirm-governance-btn" data-action="executeGovernanceAction()" class="flex-1 py-4 bg-primary text-white font-black rounded-2xl text-[10px] uppercase tracking-widest shadow-sm hover:scale-[1.02] active:scale-[0.98] transition-all">Confirm Change</button>
        </div>
      </div>
    </div>
  </div>
<?php
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/user-detail.js"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>