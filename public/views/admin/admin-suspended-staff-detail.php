<?php
$portal = 'admin';
$pageTitle = 'Staff Suspension Detail';
ob_start();
?>



    <!-- BACK BUTTON -->
    <div style="margin-bottom:24px;">
      <a href="admin-suspended-staff.php" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-widest text-text-muted hover:text-text-main hover:bg-surface-hover dark:text-text-muted dark:hover:text-white dark:hover:bg-white/5 transition-colors">
        <span class="material-symbols-outlined text-base" aria-hidden="true">arrow_back</span> Back
      </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-[1fr_360px] gap-6 lg:gap-8 items-start">
      <!-- LEFT -->
      <div class="space-y-6">

        <!-- STAFF PROFILE -->
        <div class="ds-card p-6 sm:p-8">
          <div class="flex flex-col sm:flex-row items-center gap-6 sm:gap-8 border-b border-surface-border dark:border-white/5 pb-8 mb-8 text-center sm:text-left">
            <div id="detail-avatar" class="w-24 h-24 rounded-2xl premium-gradient-primary flex items-center justify-center text-3xl font-black text-white shadow-xl shadow-primary-base/20">
              -
            </div>
            <div class="flex-1">
              <div class="text-xs font-black text-danger-base uppercase tracking-widest mb-2">Suspended Staff</div>
              <h1 id="detail-name" class="text-2xl sm:text-3xl font-black font-headline tracking-tight text-text-main dark:text-white">-</h1>
              <p id="detail-email" class="text-sm font-bold opacity-40 mb-4">-</p>
              <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                <span id="detail-staff-id-badge" class="px-3 py-1 bg-surface-hover dark:bg-white/5 text-xs font-black rounded-lg uppercase tracking-widest">-</span>
                <span class="px-3 py-1 bg-warning-surface/10 text-warning-base text-xs font-black rounded-lg uppercase tracking-widest">Internal Account</span>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-y-6 gap-x-4">
            <div>
              <p class="text-xs font-black text-text-muted uppercase tracking-widest mb-1">Employee ID</p>
              <p id="detail-staff-id" class="font-bold text-sm tracking-widest text-primary-base dark:text-primary-base">-</p>
            </div>
            <div>
              <p class="text-xs font-black text-text-muted uppercase tracking-widest mb-1">Mobile No</p>
              <p id="detail-phone" class="font-bold text-sm">-</p>
            </div>
            <div>
              <p class="text-xs font-black text-text-muted uppercase tracking-widest mb-1">Branch</p>
              <p id="detail-branch" class="font-bold text-sm">-</p>
            </div>
            <div>
              <p class="text-xs font-black text-text-muted uppercase tracking-widest mb-1">Joined Date</p>
              <p id="detail-onboarding" class="font-bold text-sm">-</p>
            </div>
          </div>
        </div>

        <!-- SUSPENSION DETAILS -->
        <div class="ds-card p-6 sm:p-8">
          <div class="text-xs font-black text-text-muted uppercase tracking-widest mb-6 border-b border-surface-border dark:border-white/5 pb-4">Internal Audit Analysis</div>
          <div class="bg-danger-surface dark:bg-danger-surface/5 p-6 rounded-2xl border border-danger-base dark:border-danger-base/10 mb-8">
            <div class="flex items-start gap-4">
              <div class="w-10 h-10 rounded-xl bg-white dark:bg-danger-surface/20 shadow-sm flex items-center justify-center text-danger-base">
                <span class="material-symbols-outlined text-2xl" aria-hidden="true">policy</span>
              </div>
              <div class="flex-1">
                <div class="text-xs font-black text-danger-base dark:text-danger-base uppercase tracking-widest mb-1">Suspension Reason</div>
                <div class="text-sm font-bold mb-2 dark:text-white" id="detail-reason">-</div>
                <div class="text-xs opacity-60 leading-relaxed font-medium" id="detail-reason-detail">-</div>
              </div>
            </div>
          </div>
          <div class="grid grid-cols-3 gap-4">
            <div>
              <p class="text-xs opacity-40 font-bold uppercase mb-1">Authorized By</p>
              <p id="detail-by" class="font-bold text-sm">-</p>
            </div>
            <div>
              <p class="text-xs opacity-40 font-bold uppercase mb-1">Date</p>
              <p id="detail-date" class="font-bold text-sm">-</p>
            </div>
            <div>
              <p class="text-xs opacity-40 font-bold uppercase mb-1">Duration</p>
              <p id="detail-duration" class="font-bold text-sm">-</p>
            </div>
          </div>
        </div>

        <!-- HISTORY -->
        <div class="ds-card overflow-hidden">
          <div class="p-6 border-b border-surface-border dark:border-white/5 flex items-center justify-between">
            <h3 class="text-sm font-bold dark:text-white uppercase tracking-widest">Processing History</h3>
            <a href="admin-transactions.php" class="text-xs font-black text-primary-base dark:text-primary-base uppercase tracking-widest hover:underline">Full Logs</a>
            <a href="admin-audit-log.php" class="admin-nav-item">
                <span class="material-symbols-outlined" aria-hidden="true">manage_search</span> Audit Log
            </a>
          </div>
          <div class="overflow-x-auto w-full">
            <table class="ds-table w-full text-left min-w-[600px]">
              <thead scope="col">
                <tr>
                  <th scope="col">Operation</th>
                  <th scope="col">Timestamp</th>
                  <th class="text-right" scope="col">Amount</th>
                  <th class="text-center" scope="col">Status</th>
                </tr>
              </thead>
              <tbody id="staff-transactions-body">
                <tr><td colspan="4" class="p-10 text-center text-xs opacity-40 font-bold uppercase">Fetching logs...</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- RIGHT -->
      <div class="space-y-6">
        
        <!-- ACTION -->
        <div class="ds-card p-6 sm:p-8 border-2 border-success-base/20 bg-success-surface/5">
           <div class="text-center">
              <div class="w-16 h-16 rounded-full bg-success-surface/10 flex items-center justify-center mx-auto mb-6 text-success-base shadow-inner">
                <span class="material-symbols-outlined text-3xl" aria-hidden="true">verified_user</span>
              </div>
              <h3 class="text-lg font-bold mb-2 dark:text-white">Reinstate Staff</h3>
              <p class="text-xs opacity-60 leading-relaxed mb-8">This will restore system privileges and allow them to process new transactions.</p>
              <button data-action="openReinstateModal()" class="btn btn-primary w-full justify-center !py-4 shadow-xl shadow-emerald-500/20" >
                <span class="material-symbols-outlined" aria-hidden="true">how_to_reg</span> Authorize Access
              </button>
           </div>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- REINSTATE MODAL -->
<div class="fixed inset-0 z-[2000] hidden items-center justify-center p-4 bg-surface-dark/40 backdrop-blur-sm transition-all" id="reinstate-modal" role="dialog" aria-modal="true">
  <div class="relative w-full max-w-lg">
    <div class="bg-white dark:bg-surface-dark rounded-2xl overflow-hidden shadow-2xl flex flex-col max-h-full border border-surface-border dark:border-white/5">
      <div class="p-8 pb-4 text-center shrink-0">
        <div class="w-16 h-16 rounded-full mx-auto flex items-center justify-center mb-4 text-success-base bg-success-surface dark:bg-success-surface/10">
          <span class="material-symbols-outlined text-4xl" aria-hidden="true">verified_user</span>
        </div>
        <h3 class="font-headline text-2xl font-black text-text-main dark:text-white">Confirm Reinstatement</h3>
        <p class="text-sm text-text-muted font-medium mt-1">Administrative security clearance required</p>
      </div>
      <div class="px-8 py-4 space-y-6 overflow-y-auto">
        <div class="text-sm text-center text-text-muted dark:text-text-muted font-medium leading-relaxed px-4">
          You are removing the suspension from <strong id="modal-name" class="text-text-main dark:text-white font-bold">...</strong>'s staff account <strong id="modal-id" class="font-mono text-success-base dark:text-success-base">...</strong>. Their system privileges will be restored immediately.
        </div>
        
        <div class="pt-6 border-t border-surface-border dark:border-white/5">
          <label for="admin-password" class="block text-xs font-black uppercase tracking-widest text-text-muted mb-3 ml-1 text-left">Authorize Action (Admin Password)</label>
          <div class="relative">
            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-text-muted" aria-hidden="true">lock</span>
            <input type="password" id="admin-password" placeholder="••••••••" 
              class="w-full pl-12 pr-4 py-4 bg-surface-subtle dark:bg-white/5 border border-surface-border dark:border-white/10 rounded-2xl text-lg font-black tracking-widest focus:ring-4 focus:ring-emerald-500/20 transition-all text-black dark:text-white outline-none shadow-inner">
          </div>
        </div>
      </div>
      <div class="p-8 pt-4 flex gap-3 shrink-0 border-t border-surface-border dark:border-white/5 mt-2">
        <button data-action="closeModal()" class="flex-1 py-4 bg-surface-hover dark:bg-white/5 hover:bg-surface-border dark:hover:bg-white/10 text-text-muted dark:text-text-muted font-black rounded-2xl text-xs uppercase tracking-widest transition-all">Cancel</button>
        <button id="confirm-btn" data-action="processReinstate()" class="flex-1 py-4 text-white font-black rounded-2xl text-xs uppercase tracking-widest shadow-lg transition-all bg-success-surface shadow-emerald-500/20 hover:bg-success-surface hover:scale-[1.02] active:scale-[0.98]">Confirm & Authorize</button>
      </div>
    </div>
  <script defer src="/Finzo/public/assets/js/admin/suspended-staff-detail.js"></script>
    <script defer src="/Finzo/public/assets/js/core/component-loader.js"></script>
<?php
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/suspended-staff-detail.js"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>