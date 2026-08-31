<?php
$portal = 'admin';
$pageTitle = 'Suspended Users';
// Admin portal guard — server-side authentication & role check
include __DIR__ . '/_guard.php';
$pageHeader = '
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-headline font-black text-text-primary tracking-tight">Suspended Users</h1>
        <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-1">Review and manage suspended customer accounts</p>
    </div>
</div>
';
ob_start();
?>
<!-- TABLE CARD -->
      <div class="ds-card overflow-hidden relative z-10 shadow-2xl transition-all duration-300">
        <div class="absolute -right-20 -top-20 w-64 h-64 bg-primary/5 rounded-full blur-3xl -z-10 pointer-events-none"></div>
        <div class="absolute -left-20 -bottom-20 w-64 h-64 bg-accent/5 rounded-full blur-3xl -z-10 pointer-events-none"></div>
        <div class="p-4 sm:p-6 border-b border-border-subtle flex justify-between items-center bg-background/40 backdrop-blur-xl relative z-10">
            <h3 class="font-bold text-xs uppercase tracking-widest text-text-secondary">Suspended Users Audit</h3>
            <a href="admin-users.php" class="text-xs font-black text-primary hover:underline uppercase tracking-widest">Full Users Audit</a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 items-end gap-4 p-4 sm:p-6 bg-background border-b border-border-subtle">
          <div>
            <div class="text-[10px] font-black tracking-wider text-text-secondary uppercase mb-2 ml-1">Search User</div>
            <div class="relative">
              <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-secondary text-lg" aria-hidden="true">search</span>
              <input type="text" id="search-input" class="fin-input !pl-10" placeholder="Name, email or account number...">
            </div>
          </div>
          <div>
            <div class="text-[10px] font-black tracking-wider text-text-secondary uppercase mb-2 ml-1">Start Date</div>
            <input type="date" id="start-date" class="fin-input">
          </div>
          <div>
            <div class="text-[10px] font-black tracking-wider text-text-secondary uppercase mb-2 ml-1">End Date</div>
            <input type="date" id="end-date" class="fin-input">
          </div>
          <div>
            <div class="text-[10px] font-black tracking-wider text-text-secondary uppercase mb-2 ml-1">Reason</div>
            <div class="finzo-dropdown w-full" id="reason-dropdown">
              <input type="hidden" id="reason-filter" value="">
              <div class="finzo-dropdown-trigger fin-input !flex items-center justify-between cursor-pointer">
                <div class="flex items-center gap-2">
                  <span class="material-symbols-outlined text-lg opacity-60" aria-hidden="true">filter_alt</span>
                  <span class="dropdown-label font-bold text-sm text-text-secondary">All Reasons</span>
                </div>
                <span class="material-symbols-outlined chevron transition-transform duration-300" aria-hidden="true">expand_more</span>
              </div>
              <div class="finzo-dropdown-menu z-50">
                <div class="finzo-dropdown-option is-selected" data-value="">All Reasons</div>
                <div class="finzo-dropdown-option" data-value="KYC Documentation Expired/Invalid">KYC Documentation Expired/Invalid</div>
                <div class="finzo-dropdown-option" data-value="Suspicious UPI Transaction Patterns">Suspicious UPI Transaction Patterns</div>
                <div class="finzo-dropdown-option" data-value="Regulatory Compliance Hold (RBI)">Regulatory Compliance Hold (RBI)</div>
                <div class="finzo-dropdown-option" data-value="Multiple Failed Auth Attempts">Multiple Failed Auth Attempts</div>
                <div class="finzo-dropdown-option" data-value="Non-Compliance with IRDAI">Non-Compliance with IRDAI</div>
                <div class="finzo-dropdown-option" data-value="Legal/Account Dispute">Legal/Account Dispute</div>
                <div class="finzo-dropdown-option" data-value="Other">Other</div>
              </div>
            </div>
          </div>
        </div>

        <div class="ds-table-wrapper border-0 rounded-none shadow-none bg-transparent">
          <table class="ds-table w-full text-left min-w-[1300px]">
            <thead scope="col">
              <tr>
                <th scope="col">#</th>
                <th scope="col">Customer</th>
                <th scope="col">Account No</th>
                <th scope="col">Account Type</th>
                <th scope="col">Suspension Reason</th>
                <th scope="col">Suspended On</th>
                <th scope="col">Suspended By</th>
                <th scope="col">Duration</th>
                <th class="text-center" scope="col">Actions</th>
              </tr>
            </thead>
            <tbody id="table-body">
              <tr><td colspan="9"><div class="skeleton-row"></div></td></tr>
              <tr><td colspan="9"><div class="skeleton-row"></div></td></tr>
              <tr><td colspan="9"><div class="skeleton-row"></div></td></tr>
              <tr><td colspan="9"><div class="skeleton-row"></div></td></tr>
              <tr><td colspan="9"><div class="skeleton-row"></div></td></tr>
            </tbody>
          </table>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-center sm:justify-between gap-6 p-4 sm:p-6 bg-background border-t border-border-subtle">
          <span class="text-xs font-bold tracking-widest text-text-secondary uppercase text-center sm:text-left w-full sm:w-auto" id="pag-info">Loading...</span>
          <div class="flex items-center justify-center" id="pag-controls"></div>
        </div>
      </div>

      <!-- REINSTATE / REMOVE SUSPEND MODAL -->
      <div class="fixed inset-0 z-[2000] hidden items-center justify-center p-4" id="reinstate-modal" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-sm transition-all modal-backdrop" onclick="window.SuspendedUsers.closeReinstateModal()"></div>
        <div class="relative w-full max-w-lg bg-surface dark:bg-[#1C1917] rounded-2xl overflow-hidden shadow-2xl border border-border-subtle dark:border-white/10 animate-modal-enter z-10">
          <div class="p-8 pb-4 text-center">
            <div class="w-16 h-16 rounded-full mx-auto flex items-center justify-center mb-4 bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-300 dark:border-emerald-700/50 shadow-sm">
              <span class="material-symbols-outlined text-4xl" aria-hidden="true">lock_open</span>
            </div>
            <h3 class="font-headline text-2xl font-black text-text-primary dark:text-stone-100">Restore User Account</h3>
            <div class="text-sm text-text-secondary dark:text-stone-400 font-bold mt-4 px-4 leading-relaxed" id="reinstate-modal-text">
              You are removing the suspension from this customer's account. Operational access and banking features will be restored immediately.
            </div>
          </div>
          <div class="p-6 pt-4 border-t border-border-subtle dark:border-white/10 flex gap-3 justify-end bg-background/50 dark:bg-[#141211]">
            <button type="button" onclick="window.SuspendedUsers.closeReinstateModal()" class="flex-1 py-3 bg-surface dark:bg-[#292524] hover:bg-surface-hover dark:hover:bg-[#383330] text-text-secondary dark:text-stone-300 font-black rounded-xl text-xs uppercase tracking-widest transition-all border border-border-subtle dark:border-white/10">Cancel</button>
            <button type="button" id="reinstate-confirm-btn" onclick="window.SuspendedUsers.confirmReinstate()" class="flex-1 py-3 bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white font-black rounded-xl text-xs uppercase tracking-widest shadow-md shadow-emerald-900/20 transition-all flex items-center justify-center gap-1.5 border-0">
              <span class="material-symbols-outlined text-base" aria-hidden="true">lock_open</span>
              Confirm & Reinstate
            </button>
          </div>
        </div>
      </div>
<?php
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/suspended-users.js?v=' . time() . '"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>