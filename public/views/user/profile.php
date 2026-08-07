<?php
$portal = 'user';
$pageTitle = 'My Profile';
ob_start();
?>
<script defer src="/Finzo/public/assets/js/user/user-shared.js"></script>

<div class="main">


<div class="page-content ds-container py-8">
  <div class="mb-8 ds-animate-fade">
    <div class="text-sm font-bold text-text-muted uppercase tracking-wider mb-1">Identity & Security</div>
    <h1 class="font-display text-3xl font-extrabold text-text-main tracking-tight">Your Profile</h1>
    <p class="text-text-muted mt-1">Manage your personal information and Finzo account settings.</p>
  </div>
  
  <div class="flex flex-col lg:flex-row gap-6">
    <!-- LEFT PANE: Identity Profile (Wise Inspired) -->
    <div class="w-full lg:w-1/3 flex flex-col gap-6 ds-animate-slide-up">
      <div class="ds-card p-6 flex flex-col items-center text-center">
        <div class="relative mb-4">
          <div class="w-24 h-24 rounded-full bg-primary-surface text-primary-base-base flex items-center justify-center font-display text-3xl font-extrabold border-4 border-white shadow-sm" id="avatar-circle">
            <span class="skeleton w-full h-full rounded-full"></span>
          </div>
          <div class="absolute bottom-0 right-0 w-6 h-6 rounded-full bg-success-base border-2 border-white flex items-center justify-center text-white" title="Identity Verified">
            <span class="material-symbols-outlined text-sm" aria-hidden="true">verified</span>
          </div>
        </div>
        <h2 class="font-display text-xl font-bold text-text-main mb-1" id="profile-name"><span class="skeleton w-32 h-6"></span></h2>
        <div class="font-mono text-sm font-bold text-text-muted mb-4" id="info-customer-id">—</div>
        
        <div class="flex flex-wrap justify-center gap-2 mb-2">
          <span class="badge badge badge-success text-xs">Verified</span>
          <span class="badge badge badge-warning text-xs">Premium Client</span>
        </div>
      </div>
      
      <div class="ds-card p-6">
        <div class="flex items-center gap-3 mb-4">
          <span class="material-symbols-outlined text-primary-base-base" aria-hidden="true">account_balance_wallet</span>
          <h3 class="font-display font-bold">Account Ledger</h3>
        </div>
        
        <div class="space-y-3">
          <div class="flex justify-between items-center p-3 rounded-lg bg-surface-subtle border border-border-main">
            <span class="text-xs font-bold text-text-muted uppercase">Account No</span>
            <span class="font-mono text-sm font-bold text-text-main" id="info-acc-no">—</span>
          </div>
          
          <div class="flex justify-between items-center p-3 rounded-lg bg-surface-subtle border border-border-main">
            <span class="text-xs font-bold text-text-muted uppercase">Status</span>
            <div id="info-status"><span class="skeleton w-16 h-5"></span></div>
          </div>
        </div>
        
        <div class="mt-4 p-5 rounded-xl bg-primary-600 text-white relative overflow-hidden shadow-md">
          <div class="absolute -right-6 -bottom-6 opacity-10">
            <span class="material-symbols-outlined text-8xl" aria-hidden="true">account_balance</span>
          </div>
          <div class="relative z-10">
            <div class="text-xs font-extrabold text-white/70 uppercase tracking-widest mb-1">Portfolio Balance</div>
            <div class="font-display text-2xl font-black" id="info-balance">₹0.00</div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- RIGHT PANE: Settings Forms -->
    <div class="w-full lg:w-2/3 flex flex-col gap-6 ds-animate-slide-up" style="animation-delay: 0.1s;">
      
      <!-- Personal Information -->
      <div class="ds-card">
        <div class="ds-card-header border-b border-border-main">
          <div>
            <h3 class="font-display text-lg font-bold">Personal Information</h3>
            <div class="text-sm text-text-muted">Update your contact details and view identity info.</div>
          </div>
        </div>
        <div class="ds-card-body p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="form-group mb-0">
              <label for="edit-name" class="form-label text-xs uppercase tracking-wider mb-2 block text-text-muted">Full Legal Name</label>
              <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-disabled" aria-hidden="true">person</span>
                <input type="text" class="form-control pl-10 bg-surface-subtle text-text-disabled border-dashed cursor-not-allowed" id="edit-name" readonly tabindex="-1" placeholder="Legal full name"/>
                <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-success-base text-lg" title="Identity Verified" aria-hidden="true">verified</span>
              </div>
              <div class="text-xs text-text-muted mt-2 font-medium">Verified legal name cannot be changed.</div>
            </div>
            
            <div class="form-group mb-0">
              <label for="edit-phone" class="form-label text-xs uppercase tracking-wider mb-2 block text-text-muted">Phone Number</label>
              <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted" aria-hidden="true">call</span>
                <input type="text" class="form-control pl-10" id="edit-phone" placeholder="+91 00000 00000"/>
              </div>
            </div>
          </div>
          
          <div class="form-group mb-6">
            <label for="edit-email" class="form-label text-xs uppercase tracking-wider mb-2 block text-text-muted">Primary Email Address</label>
            <div class="relative">
              <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted" aria-hidden="true">mail</span>
              <input type="email" class="form-control pl-10" id="edit-email" placeholder="example@finzo.com"/>
            </div>
            <div class="flex items-center gap-1.5 mt-2 text-xs font-medium text-text-muted">
              <span class="material-symbols-outlined text-success-base text-sm" aria-hidden="true">check_circle</span>
              Email is verified and linked to your ACID protection.
            </div>
          </div>
          
          <div class="flex justify-end pt-4 border-t border-border-main">
            <button class="btn btn-primary" data-action="saveProfile()">Save Changes</button>
          </div>
        </div>
      </div>
      
      <!-- Security Registry -->
      <div class="ds-card border-dashed border-primary-base/40 bg-primary-surface/30">
        <div class="ds-card-body p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-primary-surface flex items-center justify-center shrink-0">
              <span class="material-symbols-outlined text-primary-base-base text-2xl" aria-hidden="true">lock_open</span>
            </div>
            <div>
              <h3 class="font-display font-bold text-text-main">Credentials Registry</h3>
              <div class="text-sm text-text-muted">Last changed: 4 days ago</div>
            </div>
          </div>
          <a href="change-password.php" class="btn btn-secondary shrink-0">Manage Access</a>
        </div>
      </div>
      
    </div>
  </div>
</div>


</div>
<script defer src="/Finzo/public/assets/js/core/utils.js"></script>
  <script defer src="/Finzo/public/assets/js/core/api.js"></script>
<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>