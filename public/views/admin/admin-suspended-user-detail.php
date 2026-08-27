<?php
$portal = 'admin';
$pageTitle = 'Suspension Detail';
$pageHeader = '
<div class="flex items-start sm:items-center justify-between w-full">
  <div class="flex items-center gap-4">
    <a href="admin-suspended-users.php" class="w-10 h-10 flex items-center justify-center rounded-xl bg-surface border border-border-subtle hover:bg-background transition-all text-text-secondary hover:text-text-primary">
      <span class="material-symbols-outlined" aria-hidden="true">arrow_back</span>
    </a>
    <div>
      <h1 class="text-2xl font-headline font-black tracking-tight text-text-primary admin-topbar-title">Loading...</h1>
      <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-1">Suspended Customer Incident Dossier</p>
    </div>
  </div>
</div>
';
ob_start();
?>
  <div class="admin-main">
    <!-- TWO COLUMN LAYOUT -->
    <div class="grid grid-cols-1 lg:grid-cols-[1fr_360px] gap-6 sm:gap-8 mt-6">

      <!-- LEFT -->
      <div class="space-y-6">

        <!-- PROFILE CARD -->
        <div class="ds-card p-6 sm:p-8">
          <div class="flex flex-col sm:flex-row items-center gap-6 sm:gap-8 border-b border-border-subtle pb-8 mb-8 text-center sm:text-left">
            <div id="detail-avatar" class="w-24 h-24 rounded-2xl bg-primary-50 flex items-center justify-center text-3xl font-black text-primary shadow-sm">
              -
            </div>
            <div class="flex-1">
              <div class="text-[10px] font-black text-error uppercase tracking-widest mb-2">Suspended Account</div>
              <h1 id="detail-name" class="text-2xl sm:text-3xl font-black font-headline tracking-tight text-text-primary">-</h1>
              <p id="detail-email" class="text-sm font-bold text-text-secondary mb-4">-</p>
              <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                <span id="detail-acc-type-badge" class="px-3 py-1 bg-background text-[10px] font-black rounded-lg uppercase tracking-widest text-text-secondary border border-border-subtle">-</span>
                <span class="px-3 py-1 bg-error-bg text-error text-[10px] font-black rounded-lg uppercase tracking-widest border border-error-bg">Inactive</span>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-y-6 gap-x-4">
            <div>
              <p class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1">Account Number</p>
              <p id="detail-acc-no" class="font-bold text-sm tracking-widest text-text-primary">-</p>
            </div>
            <div>
              <p class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1">Phone Number</p>
              <p id="detail-phone" class="font-bold text-sm text-text-primary">-</p>
            </div>
            <div>
              <p class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1">Account Type</p>
              <p id="detail-type" class="font-bold text-sm text-text-primary">-</p>
            </div>
            <div>
              <p class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1">Date Joined</p>
              <p id="detail-opened" class="font-bold text-sm text-text-primary">-</p>
            </div>
            <div class="col-span-2 pt-4 border-t border-border-subtle">
              <p class="text-[10px] font-black text-error uppercase tracking-widest mb-1">Frozen Balance</p>
              <p id="detail-balance" class="text-2xl font-black text-error">-</p>
            </div>
          </div>
        </div>

        <!-- SUSPENSION DETAILS -->
        <div class="ds-card p-6 sm:p-8">
          <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-6 border-b border-border-subtle pb-4">Violation Context</div>
          <div class="bg-error-bg p-6 rounded-2xl border border-error-bg mb-8">
            <div class="flex items-start gap-4">
              <div class="w-10 h-10 rounded-xl bg-surface shadow-sm flex items-center justify-center text-error border border-border-subtle">
                <span class="material-symbols-outlined" aria-hidden="true">report</span>
              </div>
              <div class="flex-1">
                <div class="text-[10px] font-black text-error uppercase tracking-widest mb-1">Verified Reason</div>
                <div class="text-sm font-bold mb-2 text-text-primary" id="detail-reason">-</div>
                <div class="text-xs text-text-secondary leading-relaxed font-bold" id="detail-reason-detail">-</div>
              </div>
            </div>
          </div>
          <div class="grid grid-cols-3 gap-4">
            <div>
              <p class="text-[10px] font-black text-text-secondary uppercase mb-1">By Admin</p>
              <p id="detail-by" class="font-bold text-sm text-text-primary">-</p>
            </div>
            <div>
              <p class="text-[10px] font-black text-text-secondary uppercase mb-1">Date</p>
              <p id="detail-date" class="font-bold text-sm text-text-primary">-</p>
            </div>
            <div>
              <p class="text-[10px] font-black text-text-secondary uppercase mb-1">Time</p>
              <p id="detail-time" class="font-bold text-sm text-text-primary">-</p>
            </div>
          </div>
        </div>

        <!-- RECENT TXNS -->
        <div class="ds-card overflow-hidden mt-6 relative z-10 shadow-2xl transition-all duration-300">
          <div class="absolute -right-20 -top-20 w-64 h-64 bg-primary/5 rounded-full blur-3xl -z-10 pointer-events-none"></div>
          <div class="absolute -left-20 -bottom-20 w-64 h-64 bg-accent/5 rounded-full blur-3xl -z-10 pointer-events-none"></div>
          <div class="p-6 border-b border-border-subtle flex items-center justify-between bg-background/40 backdrop-blur-xl relative z-10">
            <div>
              <h3 class="text-sm font-black text-text-primary uppercase tracking-widest">Activity History</h3>
              <p class="text-[10px] font-black text-text-secondary uppercase mt-1">Last 3 Pre-suspension trans</p>
            </div>
            <a href="admin-transactions.php" class="text-[10px] font-black text-primary uppercase tracking-widest hover:underline">View All</a>
            <a href="admin-audit-log.php" class="inline-flex items-center gap-1 text-[10px] font-black text-text-secondary hover:text-text-primary uppercase tracking-widest transition-colors">
                <span class="material-symbols-outlined text-sm" aria-hidden="true">manage_search</span> Audit Log
            </a>
          </div>
          <div class="ds-table-wrapper border-0 rounded-none shadow-none bg-transparent">
            <table class="ds-table w-full text-left min-w-[500px]">
              <thead scope="col">
                <tr>
                  <th scope="col">Date</th>
                  <th scope="col">Description</th>
                  <th scope="col">Type</th>
                  <th class="text-right" scope="col">Amount</th>
                </tr>
              </thead>
              <tbody id="transactions-body">
                <tr><td colspan="4" class="p-10 text-center text-xs text-text-secondary font-bold uppercase">Processing...</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- RIGHT -->
      <div class="space-y-6">
        
        <!-- ADMIN NOTES -->
        <div class="ds-card p-6 sm:p-8">
          <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-4">Official Notes</div>
          <div id="detail-notes" class="p-4 bg-background rounded-xl text-sm font-bold leading-relaxed text-text-secondary border border-border-subtle">
            Loading notes...
          </div>
          <div id="detail-notes-by" class="mt-4 text-[10px] font-black text-text-secondary uppercase text-right">-</div>
        </div>

        <!-- ACTIONS -->
        <div class="ds-card p-6 sm:p-8 border-2 border-success-bg bg-success-bg/20">
          <div class="text-center">
            <div class="w-16 h-16 rounded-full bg-success-bg flex items-center justify-center mx-auto mb-6 text-success border border-success-bg">
              <span class="material-symbols-outlined text-3xl" aria-hidden="true">verified_user</span>
            </div>
            <h3 class="text-lg font-black mb-2 text-text-primary">Account Recovery</h3>
            <p class="text-xs text-text-secondary font-bold leading-relaxed mb-8">Reinstating will restore original access and notify the user via registered mobile/email.</p>
            <button data-action="openReinstateModal()" class="btn btn-primary w-full justify-center !py-4" >
              <span class="material-symbols-outlined" aria-hidden="true">power_settings_new</span> Reinstate Account
            </button>
            <button data-action="openDeleteModal()" class="btn w-full justify-center !py-3 mt-4 border-none text-error bg-transparent hover:bg-error-bg transition-all font-black text-xs uppercase tracking-widest">
              Permanent Deletion Request
            </button>
          </div>
        </div>

      </div>

    </div>
  </div>

<!-- REINSTATE MODAL -->
<div class="fixed inset-0 z-[2000] hidden" id="reinstate-modal" role="dialog" aria-modal="true">
  <div class="absolute inset-0 bg-surface-dark/80 backdrop-blur-sm"></div>
  <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-lg">
    <div class="ds-card !p-0 overflow-hidden shadow-2xl border-none">
      <div class="p-8 pb-4 text-center">
        <div class="w-16 h-16 rounded-full mx-auto flex items-center justify-center mb-4 bg-success-bg text-success border border-success-bg shadow-sm">
          <span class="material-symbols-outlined text-4xl" aria-hidden="true">lock_open</span>
        </div>
        <h3 class="font-headline text-2xl font-black text-text-primary">Confirm Reinstatement</h3>
        <div class="text-sm text-text-secondary font-bold mt-4 px-4">You are removing the suspension from <strong id="modal-name" class="text-text-primary">--</strong>\'s account <strong id="modal-acc" class="text-text-primary">FNZ---</strong>. Their account status will immediately return to <strong class="text-success">Active</strong> and they will regain complete access to all Finzo banking features.</div>
      </div>
      <div class="p-8 pt-6 flex gap-3">
        <button data-action="closeModal()" class="flex-1 py-4 bg-background hover:bg-surface-hover text-text-secondary font-black rounded-2xl text-[10px] uppercase tracking-widest transition-all border border-border-subtle">Cancel</button>
        <button id="confirm-btn" data-action="processReinstate()" class="flex-1 py-4 bg-success text-white font-black rounded-2xl text-[10px] uppercase tracking-widest shadow-sm hover:scale-[1.02] active:scale-[0.98] transition-all">
          Confirm & Reinstate
        </button>
      </div>
    </div>
  </div>
</div>

<!-- PERMANENT DELETION MODAL -->
<div class="fixed inset-0 z-[2000] hidden" id="delete-modal" role="dialog" aria-modal="true">
  <div class="absolute inset-0 bg-surface-dark/80 backdrop-blur-sm"></div>
  <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-lg">
    <div class="ds-card !p-0 overflow-hidden shadow-2xl border-none">
      <div class="p-8 pb-4 text-center">
        <div class="w-16 h-16 rounded-full mx-auto flex items-center justify-center mb-4 bg-error-bg text-error border border-error-bg shadow-sm">
          <span class="material-symbols-outlined text-4xl" aria-hidden="true">delete_forever</span>
        </div>
        <h3 class="font-headline text-2xl font-black text-text-primary">Confirm Permanent Deletion</h3>
        <div class="text-sm text-text-secondary font-bold mt-4 px-4">You are permanently deleting <strong id="delete-modal-name" class="text-text-primary">--</strong>\'s account. This action will close all bank accounts and archive their dossier permanently.</div>
      </div>
      
      <div class="px-8 py-4 border-t border-border-subtle mt-4">
        <label for="delete-admin-password" class="block text-[10px] font-black uppercase tracking-widest text-text-secondary mb-3 ml-1">Confirm Identity (Admin Password)</label>
        <div class="relative">
          <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-text-secondary" aria-hidden="true">lock</span>
          <input type="password" id="delete-admin-password" placeholder="••••••••" 
            class="fin-input !pl-12 !py-3 !rounded-2xl !text-lg !tracking-widest">
        </div>
      </div>

      <div class="p-8 pt-4 flex gap-3">
        <button data-action="closeDeleteModal()" class="flex-1 py-4 bg-background hover:bg-surface-hover text-text-secondary font-black rounded-2xl text-[10px] uppercase tracking-widest transition-all border border-border-subtle">Cancel</button>
        <button id="delete-confirm-btn" data-action="processDelete()" class="flex-1 py-4 bg-error text-white font-black rounded-2xl text-[10px] uppercase tracking-widest shadow-sm hover:scale-[1.02] active:scale-[0.98] transition-all">
          Confirm & Delete
        </button>
      </div>
    </div>
  </div>
</div>

<div class="toast-wrap" id="toast-wrap"></div>
<?php
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/suspended-user-detail.js"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
