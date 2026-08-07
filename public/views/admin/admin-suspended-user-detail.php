<?php
$portal = 'admin';
$pageTitle = 'Suspension Detail';
ob_start();
?>


<div class="admin-main">

  

  <div class="page-content">

    <!-- BACK BUTTON -->
    <div style="margin-bottom:20px;">
      <a href="admin-suspended-users.php" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-widest text-text-muted hover:text-text-main hover:bg-surface-hover dark:text-text-muted dark:hover:text-white dark:hover:bg-white/5 transition-colors">
        <span class="material-symbols-outlined text-base" aria-hidden="true">arrow_back</span> Back
      </a>
    </div>



    <!-- TWO COLUMN LAYOUT: Stacks on Tablet (lg:grid-cols), 1fr 360px on Desktop -->
    <div class="grid grid-cols-1 lg:grid-cols-[1fr_360px] gap-6 sm:gap-8">

      <!-- LEFT -->
      <div class="space-y-6">

        <!-- PROFILE CARD -->
        <div class="ds-card p-6 sm:p-8">
          <div class="flex flex-col sm:flex-row items-center gap-6 sm:gap-8 border-b border-surface-border dark:border-white/5 pb-8 mb-8 text-center sm:text-left">
            <div id="detail-avatar" class="w-24 h-24 rounded-2xl premium-gradient-primary flex items-center justify-center text-3xl font-black text-white shadow-xl shadow-primary-base/20">
              -
            </div>
            <div class="flex-1">
              <div class="text-xs font-black text-danger-base uppercase tracking-widest mb-2">Suspended Account</div>
              <h1 id="detail-name" class="text-2xl sm:text-3xl font-black font-headline tracking-tight text-text-main dark:text-white">-</h1>
              <p id="detail-email" class="text-sm font-bold opacity-40 mb-4">-</p>
              <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                <span id="detail-acc-type-badge" class="px-3 py-1 bg-surface-hover dark:bg-white/5 text-xs font-black rounded-lg uppercase tracking-widest">-</span>
                <span class="px-3 py-1 bg-danger-surface text-white text-xs font-black rounded-lg uppercase tracking-widest shadow-lg shadow-red-500/20">Inactive</span>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-y-6 gap-x-4">
            <div>
              <p class="text-xs font-black text-text-muted uppercase tracking-widest mb-1">Account Number</p>
              <p id="detail-acc-no" class="font-bold text-sm tracking-widest">-</p>
            </div>
            <div>
              <p class="text-xs font-black text-text-muted uppercase tracking-widest mb-1">Phone Number</p>
              <p id="detail-phone" class="font-bold text-sm">-</p>
            </div>
            <div>
              <p class="text-xs font-black text-text-muted uppercase tracking-widest mb-1">Account Type</p>
              <p id="detail-type" class="font-bold text-sm">-</p>
            </div>
            <div>
              <p class="text-xs font-black text-text-muted uppercase tracking-widest mb-1">Date Joined</p>
              <p id="detail-opened" class="font-bold text-sm">-</p>
            </div>
            <div class="col-span-2 pt-4 border-t border-surface-border dark:border-white/5">
              <p class="text-xs font-black text-danger-base uppercase tracking-widest mb-1">Frozen Balance</p>
              <p id="detail-balance" class="text-2xl font-black text-danger-base dark:text-danger-base">-</p>
            </div>
          </div>
        </div>

        <!-- SUSPENSION DETAILS -->
        <div class="ds-card p-6 sm:p-8">
          <div class="text-xs font-black text-text-muted uppercase tracking-widest mb-6 border-b border-surface-border dark:border-white/5 pb-4">Violation Context</div>
          <div class="bg-danger-surface dark:bg-danger-surface/5 p-6 rounded-2xl border border-danger-base dark:border-danger-base/10 mb-8">
            <div class="flex items-start gap-4">
              <div class="w-10 h-10 rounded-xl bg-white dark:bg-danger-surface/20 shadow-sm flex items-center justify-center text-danger-base">
                <span class="material-symbols-outlined" aria-hidden="true">report</span>
              </div>
              <div class="flex-1">
                <div class="text-xs font-black text-danger-base dark:text-danger-base uppercase tracking-widest mb-1">Verified Reason</div>
                <div class="text-sm font-bold mb-2 dark:text-white" id="detail-reason">-</div>
                <div class="text-xs opacity-60 leading-relaxed font-medium" id="detail-reason-detail">-</div>
              </div>
            </div>
          </div>
          <div class="grid grid-cols-3 gap-4">
            <div>
              <p class="text-xs opacity-40 font-bold uppercase mb-1">By Admin</p>
              <p id="detail-by" class="font-bold text-sm">-</p>
            </div>
            <div>
              <p class="text-xs opacity-40 font-bold uppercase mb-1">Date</p>
              <p id="detail-date" class="font-bold text-sm">-</p>
            </div>
            <div>
              <p class="text-xs opacity-40 font-bold uppercase mb-1">Time</p>
              <p id="detail-time" class="font-bold text-sm">-</p>
            </div>
          </div>
        </div>

        <!-- RECENT TXNS -->
        <div class="ds-card overflow-hidden">
          <div class="p-6 border-b border-surface-border dark:border-white/5 flex items-center justify-between">
            <div>
              <h3 class="text-sm font-bold dark:text-white uppercase tracking-widest">Activity History</h3>
              <p class="text-xs opacity-40 font-bold uppercase mt-1">Last 3 Pre-suspension trans</p>
            </div>
            <a href="admin-transactions.php" class="text-xs font-black text-primary-base dark:text-primary-base uppercase tracking-widest hover:underline">View All</a>
            <a href="admin-audit-log.php" class="admin-nav-item">
                <span class="material-symbols-outlined" aria-hidden="true">manage_search</span> Audit Log
            </a>
          </div>
          <div class="overflow-x-auto w-full">
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
                <tr><td colspan="4" class="p-10 text-center text-xs opacity-40 font-bold uppercase">Processing...</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- RIGHT -->
      <div class="space-y-6">
        
        <!-- ADMIN NOTES -->
        <div class="ds-card p-6 sm:p-8">
          <div class="text-xs font-black text-text-muted uppercase tracking-widest mb-4">Official Notes</div>
          <div id="detail-notes" class="p-4 bg-surface-subtle dark:bg-white/5 rounded-xl text-sm font-medium leading-relaxed italic opacity-60 border border-surface-border dark:border-white/5">
            Loading notes...
          </div>
          <div id="detail-notes-by" class="mt-4 text-xs font-black text-primary-base/40 uppercase text-right">-</div>
        </div>

        <!-- ACTIONS -->
        <div class="ds-card p-6 sm:p-8 border-2 border-success-base/20 bg-success-surface/5">
          <div class="text-center">
            <div class="w-16 h-16 rounded-full bg-success-surface/10 flex items-center justify-center mx-auto mb-6 text-success-base shadow-inner">
              <span class="material-symbols-outlined text-3xl" aria-hidden="true">verified_user</span>
            </div>
            <h3 class="text-lg font-bold mb-2 dark:text-white">Account Recovery</h3>
            <p class="text-xs opacity-60 leading-relaxed mb-8">Reinstating will restore original access and notify the user via registered mobile/email.</p>
            <button data-action="openReinstateModal()" class="btn btn-primary w-full justify-center !py-4 shadow-xl shadow-emerald-500/20" style="background: var(--color-success-base) !important;">
              <span class="material-symbols-outlined" aria-hidden="true">power_settings_new</span> Reinstate Account
            </button>
            <button data-action="openDeleteModal()" class="btn btn-secondary w-full justify-center !py-3 mt-4 border-none text-danger-base opacity-60 hover:opacity-100 transition-all font-black text-xs uppercase tracking-widest">
              Permanent Deletion Request
            </button>
          </div>
        </div>

      </div>

    </div>

  </div>
</div>

<!-- REINSTATE MODAL -->
<div class="modal-overlay hidden" id="reinstate-modal" role="dialog" aria-modal="true">
  <div class="modal-card">
    <div class="modal-icon" style="background: var(--color-success-surface)">
      <span class="material-symbols-outlined" style="color: var(--color-success-base);font-size:30px" aria-hidden="true">lock_open</span>
    </div>
    <div class="modal-title">Confirm Reinstatement</div>
    <div class="modal-text">You are removing the suspension from <strong id="modal-name">--</strong>'s account <strong id="modal-acc">FNZ---</strong>. Their account status will immediately return to <strong>Active</strong> and they will regain complete access to all Finzo banking features.</div>

    <div class="modal-actions">
      <button class="btn btn-ghost" data-action="closeModal()">Cancel</button>
      <button class="btn btn-success" id="confirm-btn" data-action="processReinstate()">
        <span class="material-symbols-outlined" style="font-size:16px" aria-hidden="true">check</span>
        Confirm & Reinstate
      </button>
    </div>
  </div>
</div>

<!-- PERMANENT DELETION MODAL -->
<div class="modal-overlay hidden" id="delete-modal" role="dialog" aria-modal="true">
  <div class="modal-card">
    <div class="modal-icon" style="background:#ffeef0">
      <span class="material-symbols-outlined" style="color:#ff3b30;font-size:30px" aria-hidden="true">delete_forever</span>
    </div>
    <div class="modal-title">Confirm Permanent Deletion</div>
    <div class="modal-text">You are permanently deleting <strong id="delete-modal-name">--</strong>'s account. This action will close all bank accounts and archive their dossier permanently.</div>
    
    <div class="mt-6 w-full text-left">
      <label class="block text-xs font-black uppercase tracking-widest text-text-muted mb-3 ml-1">Confirm Identity (Admin Password)</label>
      <div class="relative">
        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-text-muted" aria-hidden="true">lock</span>
        <input type="password" id="delete-admin-password" placeholder="••••••••" 
          class="w-full pl-12 pr-4 py-3 bg-surface-hover dark:bg-white/5 border-none rounded-xl text-lg font-black tracking-widest focus:ring-4 focus:ring-primary/5 transition-all text-black dark:text-white">
      </div>
    </div>

    <div class="modal-actions mt-8">
      <button class="btn btn-ghost" data-action="closeDeleteModal()">Cancel</button>
      <button class="btn btn-danger" id="delete-confirm-btn" data-action="processDelete()" style="background:#ff3b30 !important; color:#fff !important;">
        <span class="material-symbols-outlined" style="font-size:16px" aria-hidden="true">check</span>
        Confirm & Delete
      </button>
    </div>
  </div>
</div>

<div class="toast-wrap" id="toast-wrap"></div>

<script defer src="/Finzo/public/assets/js/admin/suspended-user-detail.js"></script>
    <script defer src="/Finzo/public/assets/js/core/component-loader.js"></script>
<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>