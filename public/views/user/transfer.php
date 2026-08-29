<?php
$portal = 'user';
$pageTitle = 'Fund Transfer';
ob_start();
?>
<div class="mb-8 ds-animate-fade text-center max-w-2xl mx-auto">
    <div class="w-16 h-16 bg-primary-surface text-primary-base-base rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm">
      <span class="material-symbols-outlined text-3xl" aria-hidden="true">send</span>
    </div>
    <h1 class="font-display text-3xl font-extrabold text-text-main tracking-tight">Send Money</h1>
    <p class="text-text-muted mt-2">Instant, ACID-protected digital settlement</p>
  </div>

  <div class="w-full max-w-2xl mx-auto flex flex-col gap-6 ds-animate-slide-up">
    
    <!-- DESTINATION CARD -->
    <div class="ds-card">
      <div class="ds-card-header border-b border-border-main">
        <h3 class="font-display text-lg font-bold text-text-main">1. Destination Registry</h3>
      </div>
      <div class="ds-card-body p-6">
        
        <div class="mb-6">
          <label for="to-account" class="block text-xs font-bold text-text-muted uppercase tracking-widest mb-3">Saved Beneficiaries</label>
          <div class="flex flex-wrap gap-2" id="beneficiary-chips">
            <div class="text-xs text-text-muted italic">Loading beneficiaries...</div>
          </div>
        </div>

        <div>
          <label for="to-account" class="block text-xs font-bold text-text-muted uppercase tracking-widest mb-3">Finzo Account Identifier</label>
          <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
              <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted" aria-hidden="true">account_box</span>
              <input type="text" class="form-control pl-10 font-mono font-bold tracking-wider uppercase h-12" id="to-account" placeholder="Enter Finzo Account No." oninput="resetLookup()"/>
            </div>
            <button class="btn btn-secondary h-12 px-6" data-action="lookupAccount()">Verify Identity</button>
          </div>
        </div>

        <!-- Lookup Result -->
        <div id="lookup-result" class="hidden mt-4">
          <div id="lookup-found" class="hidden p-4 rounded-xl bg-success-surface border border-success-base/20 flex items-center gap-3 animate-in fade-in slide-in-from-top-2 duration-300">
            <span class="material-symbols-outlined text-success-base text-2xl" aria-hidden="true">check_circle</span>
            <div>
              <div class="text-sm font-bold text-text-main" id="recipient-name">Name</div>
              <div class="text-xs font-bold text-success-base tracking-widest uppercase mt-0.5"><span id="recipient-type">Savings</span> • Verified</div>
            </div>
          </div>
          <div id="lookup-not-found" class="hidden p-4 rounded-xl bg-danger-surface border border-danger-base/20 flex items-center gap-3 animate-in fade-in slide-in-from-top-2 duration-300">
            <span class="material-symbols-outlined text-danger-base text-2xl" aria-hidden="true">error</span>
            <div class="text-sm font-bold text-danger-base">Unrecognized Account Identifier</div>
          </div>
        </div>
        
      </div>
    </div>

    <!-- AMOUNT & SECURITY CARD -->
    <div class="ds-card ds-animate-slide-up delay-100">
      <div class="ds-card-header border-b border-border-main">
        <h3 class="font-display text-lg font-bold text-text-main">2. Transfer Details</h3>
      </div>
      <div class="ds-card-body p-6">
        
        <!-- Hero Amount Input -->
        <div class="mb-8 text-center relative max-w-xs mx-auto">
          <label for="transfer-amount" class="block text-xs font-bold text-text-muted uppercase tracking-widest mb-4">Amount to send</label>
          <div class="relative flex items-center justify-center">
            <span class="absolute left-4 text-3xl text-text-muted font-bold">₹</span>
            <input type="number" id="transfer-amount" class="w-full bg-transparent border-b-2 border-border-main focus:border-primary-base outline-none text-center font-display text-5xl font-black text-primary-base-600 pb-2 transition-colors placeholder:text-text-disabled" placeholder="0" min="1" oninput="updateSummary()"/>
          </div>
          <div id="amount-words" class="text-xs font-bold text-text-muted mt-3 h-4"></div>
          <div id="amount-error" class="hidden text-xs font-bold text-danger-base mt-2" aria-live="polite"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label for="transfer-desc" class="block text-xs font-bold text-text-muted uppercase tracking-widest mb-2">Reference Note</label>
            <input type="text" class="form-control" id="transfer-desc" placeholder="e.g. Service Payment" maxlength="100"/>
          </div>
          <div>
            <label for="transfer-pin" class="block text-xs font-bold text-text-muted uppercase tracking-widest mb-2">Security PIN</label>
            <input type="password" class="form-control font-mono tracking-widest text-center" id="transfer-pin" placeholder="••••" maxlength="4"/>
          </div>
        </div>
        
        <button class="btn btn-primary w-full h-14 text-lg mt-8 shadow-md hover:shadow-lg transition-all" data-action="confirmTransfer()" id="transfer-btn">
          Review Transfer
        </button>
        
        <div class="flex items-center justify-center gap-2 mt-6 text-xs font-bold text-text-muted uppercase tracking-widest">
           <span class="material-symbols-outlined text-sm" aria-hidden="true">encrypted</span>
           End-to-end encrypted • ISO 20022 Compliant
        </div>
      </div>
    </div>

  </div>
</div>

<!-- CONFIRM MODAL -->
<div id="confirm-modal" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-sm flex items-center justify-center p-4" role="dialog" aria-modal="true">
  <div class="ds-card max-w-md w-full p-6 sm:p-8 animate-in zoom-in-95 duration-200">
    <div class="w-16 h-16 bg-info-surface text-info-base rounded-full flex items-center justify-center mx-auto mb-4">
      <span class="material-symbols-outlined text-3xl" aria-hidden="true">verified_user</span>
    </div>
    <h3 class="font-display text-xl font-bold text-center text-text-main mb-6">Authorize Settlement</h3>
    
    <div class="bg-surface-subtle rounded-xl border border-border-main overflow-hidden mb-6">
      <div class="p-4 border-b border-border-main flex justify-between items-center bg-white">
        <span class="text-xs font-bold text-text-muted uppercase tracking-widest">Source</span>
        <span class="font-mono text-sm font-bold text-text-main" id="cm-from"></span>
      </div>
      <div class="p-4 border-b border-border-main flex justify-between items-center bg-white">
        <span class="text-xs font-bold text-text-muted uppercase tracking-widest">Target</span>
        <span class="font-mono text-sm font-bold text-text-main" id="cm-to"></span>
      </div>
      <div class="p-4 border-b border-border-main flex justify-between items-center bg-white">
        <span class="text-xs font-bold text-text-muted uppercase tracking-widest">Entity</span>
        <span class="text-sm font-bold text-text-main" id="cm-recipient"></span>
      </div>
      <div class="p-5 flex flex-col items-center justify-center bg-primary-surface/30">
        <span class="text-xs font-bold text-text-muted uppercase tracking-widest mb-1">Settlement Value</span>
        <span class="font-display text-3xl font-black text-danger-base" id="cm-amount"></span>
      </div>
    </div>
    
    <p class="text-xs font-bold text-text-muted text-center mb-6 px-4">Please review the details carefully. Once confirmed, transfers cannot be reversed.</p>
    
    <div class="flex gap-4">
      <button class="btn btn-secondary flex-1" data-action="closeModal(&#039;confirm-modal&#039;)">Cancel</button>
      <button class="btn btn-primary flex-[2]" id="final-transfer-btn" data-action="processTransfer()">
        <span class="material-symbols-outlined" aria-hidden="true">done_all</span> Authorize & Send
      </button>
    </div>
  </div>
</div>


<script defer src="/Finzo/public/assets/js/core/utils.js"></script>
  <script defer src="/Finzo/public/assets/js/core/api.js"></script>
<script defer src="/Finzo/public/assets/js/user/transfer.js"></script>
<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>

