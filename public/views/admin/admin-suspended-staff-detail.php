<?php
$portal = 'admin';
$pageTitle = 'Suspended Staff Detail';
$pageHeader = '
<div class="flex items-start sm:items-center justify-between w-full">
  <div class="flex items-center gap-4">
    <a href="admin-suspended-staff.php" class="w-10 h-10 flex items-center justify-center rounded-xl bg-surface border border-border-subtle hover:bg-background transition-all text-text-secondary hover:text-text-primary">
      <span class="material-symbols-outlined" aria-hidden="true">arrow_back</span>
    </a>
    <div>
      <div class="text-[10px] font-black tracking-widest uppercase text-text-secondary">SUSPENDED STAFF</div>
      <div class="text-xl sm:text-2xl font-black tracking-tight mt-0.5 text-text-primary force-black" id="header-staff-name">Loading...</div>
    </div>
  </div>
  <div class="flex items-center gap-2 sm:gap-4">
    <div id="header-status-badge" class="hidden sm:flex items-center gap-2 px-4 py-1.5 bg-error-bg border border-error-bg rounded-full">
      <div class="status-dot w-2 h-2 bg-error rounded-full animate-pulse"></div>
      <span class="status-label text-[11px] font-black text-error uppercase tracking-widest">SUSPENDED</span>
    </div>
  </div>
</div>
';
ob_start();
?>
  <div class="admin-main">
    <div class="flex flex-col lg:grid lg:grid-cols-12 gap-6 lg:gap-8 mt-6">
      
      <!-- LEFT COLUMN (8 cols) -->
      <div class="lg:col-span-8 space-y-6">

        <!-- STAFF PROFILE -->
        <div class="ds-card p-6 sm:p-8">
          <div class="flex flex-col sm:flex-row items-center gap-6 sm:gap-8 border-b border-border-subtle pb-8 mb-8 text-center sm:text-left">
            <div id="detail-avatar" class="w-24 h-24 sm:w-28 sm:h-28 rounded-3xl overflow-hidden shadow-sm bg-error-bg flex items-center justify-center border border-error-bg text-4xl font-black text-error">
              -
            </div>
            <div class="flex-1">
              <div class="text-[10px] font-black text-error uppercase tracking-widest mb-2">Suspended Staff</div>
              <h1 id="detail-name" class="text-2xl sm:text-3xl font-black tracking-tight text-text-primary mb-3">-</h1>
              <p id="detail-email" class="text-sm font-bold text-text-secondary mb-4">-</p>
              <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                <span id="detail-staff-id-badge" class="px-3 py-1 bg-background border border-border-subtle text-[10px] font-black rounded-lg uppercase tracking-widest text-text-secondary font-mono">-</span>
                <span class="px-3 py-1 bg-error-bg text-error border border-error-bg text-[10px] font-black rounded-lg uppercase tracking-widest">Internal Account</span>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-y-6 gap-x-4">
            <div>
              <p class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1">Employee ID</p>
              <p id="detail-staff-id" class="font-bold text-sm tracking-widest text-primary font-mono">-</p>
            </div>
            <div>
              <p class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1">Mobile No</p>
              <p id="detail-phone" class="font-bold text-sm text-text-primary">-</p>
            </div>
            <div>
              <p class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1">Branch</p>
              <p id="detail-branch" class="font-bold text-sm text-text-primary">-</p>
            </div>
            <div>
              <p class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1">Joined Date</p>
              <p id="detail-onboarding" class="font-bold text-sm text-text-primary">-</p>
            </div>
          </div>
        </div>

        <!-- SUSPENSION DETAILS -->
        <div class="ds-card p-6 sm:p-8">
          <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-6 border-b border-border-subtle pb-4">Internal Audit Analysis</div>
          <div class="bg-error-bg p-6 rounded-2xl border border-error-bg mb-8">
            <div class="flex items-start gap-4">
              <div class="w-10 h-10 rounded-xl bg-white/50 shadow-sm flex items-center justify-center text-error border border-error-bg">
                <span class="material-symbols-outlined text-2xl" aria-hidden="true">policy</span>
              </div>
              <div class="flex-1">
                <div class="text-[10px] font-black text-error uppercase tracking-widest mb-1">Suspension Reason</div>
                <div class="text-sm font-bold mb-2 text-text-primary" id="detail-reason">-</div>
                <div class="text-xs text-text-secondary leading-relaxed font-medium" id="detail-reason-detail">-</div>
              </div>
            </div>
          </div>
          <div class="grid grid-cols-3 gap-4">
            <div>
              <p class="text-[10px] text-text-secondary font-black uppercase mb-1 tracking-widest">Authorized By</p>
              <p id="detail-by" class="font-bold text-sm text-text-primary">-</p>
            </div>
            <div>
              <p class="text-[10px] text-text-secondary font-black uppercase mb-1 tracking-widest">Date</p>
              <p id="detail-date" class="font-bold text-sm text-text-primary">-</p>
            </div>
            <div>
              <p class="text-[10px] text-text-secondary font-black uppercase mb-1 tracking-widest">Duration</p>
              <p id="detail-duration" class="font-bold text-sm text-text-primary">-</p>
            </div>
          </div>
        </div>

        <!-- HISTORY -->
        <div class="ds-card overflow-hidden">
          <div class="p-6 border-b border-border-subtle flex items-center justify-between bg-background">
            <h3 class="text-[10px] font-black text-text-secondary uppercase tracking-widest">Processing History</h3>
            <div class="flex gap-4 items-center">
                <a href="admin-transactions.php" class="text-[10px] font-black text-primary uppercase tracking-widest hover:underline">Full Logs</a>
                <a href="admin-audit-log.php" class="text-[10px] font-black flex items-center gap-1 uppercase tracking-widest text-text-secondary hover:text-text-primary transition-colors">
                    <span class="material-symbols-outlined text-sm" aria-hidden="true">manage_search</span> Audit Log
                </a>
            </div>
          </div>
          <div class="ds-table-wrapper border-0 rounded-none shadow-none bg-transparent">
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
                <tr><td colspan="4" class="p-10 text-center text-xs text-text-secondary font-bold uppercase">Fetching logs...</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- RIGHT COLUMN (4 cols) -->
      <div class="lg:col-span-4 space-y-6">
        
        <!-- ACTION -->
        <div class="ds-card p-6 sm:p-8 border-2 border-success-bg bg-success-bg">
           <div class="text-center">
              <div class="w-16 h-16 rounded-full bg-success-bg border border-success-bg flex items-center justify-center mx-auto mb-6 text-success shadow-sm">
                <span class="material-symbols-outlined text-3xl" aria-hidden="true">verified_user</span>
              </div>
              <h3 class="text-lg font-black tracking-tight text-text-primary mb-2">Reinstate Staff</h3>
              <p class="text-xs text-text-secondary font-bold leading-relaxed mb-8">This will restore system privileges and allow them to process new transactions.</p>
              <button data-action="openReinstateModal()" class="w-full py-3.5 bg-success hover:bg-success-hover text-white font-black rounded-xl text-[10px] uppercase tracking-widest transition-all shadow-sm flex items-center justify-center gap-2" >
                <span class="material-symbols-outlined text-lg" aria-hidden="true">how_to_reg</span> Authorize Access
              </button>
           </div>
        </div>
      </div>

    </div>
  </div>

<!-- REINSTATE MODAL -->
<div class="fixed inset-0 z-[2000] hidden items-center justify-center p-4 bg-surface-dark/80 backdrop-blur-sm transition-all" id="reinstate-modal" role="dialog" aria-modal="true">
  <div class="relative w-full max-w-lg">
    <div class="ds-card !p-0 overflow-hidden shadow-2xl border-none">
      <div class="p-8 pb-4 text-center shrink-0">
        <div class="w-16 h-16 rounded-full mx-auto flex items-center justify-center mb-4 text-success bg-success-bg border border-success-bg">
          <span class="material-symbols-outlined text-4xl" aria-hidden="true">verified_user</span>
        </div>
        <h3 class="font-headline text-2xl font-black text-text-primary">Confirm Reinstatement</h3>
        <p class="text-[10px] uppercase tracking-widest text-text-secondary font-black mt-1">Administrative security clearance required</p>
      </div>
      <div class="px-8 py-4 space-y-6 overflow-y-auto">
        <div class="text-sm text-center text-text-secondary font-bold leading-relaxed px-4">
          You are removing the suspension from <strong id="modal-name" class="text-text-primary font-black">...</strong>\'s staff account <strong id="modal-id" class="font-mono text-success">...</strong>. Their system privileges will be restored immediately.
        </div>
        
        <div class="pt-6 border-t border-border-subtle">
          <label for="admin-password" class="block text-[10px] font-black uppercase tracking-widest text-text-secondary mb-3 ml-1 text-left">Authorize Action (Admin Password)</label>
          <div class="relative">
            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-text-secondary" aria-hidden="true">lock</span>
            <input type="password" id="admin-password" placeholder="••••••••" 
              class="fin-input !pl-12 !py-3 !rounded-2xl !text-lg !tracking-widest !w-full">
          </div>
        </div>
      </div>
      <div class="p-8 pt-4 flex gap-3 shrink-0 mt-2">
        <button data-action="closeModal()" class="flex-1 py-4 bg-background hover:bg-surface-hover border border-border-subtle text-text-secondary font-black rounded-2xl text-[10px] uppercase tracking-widest transition-all">Cancel</button>
        <button id="confirm-btn" data-action="processReinstate()" class="flex-1 py-4 text-white font-black rounded-2xl text-[10px] uppercase tracking-widest shadow-sm transition-all bg-success hover:scale-[1.02] active:scale-[0.98]">Confirm & Authorize</button>
      </div>
    </div>
  </div>
</div>
<?php
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/suspended-staff-detail.js"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
