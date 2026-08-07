<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin';
ob_start();
?>


  <!-- MAIN CONTENT -->
  <div class="admin-main">
    <!-- TOP BAR -->
    <div class="admin-topbar">
      <div class="flex items-center gap-2 sm:gap-4 flex-1">
        <button id="mobile-menu-btn" class="lg:hidden p-2 rounded-lg hover:bg-surface-hover dark:hover:bg-white/5 transition-colors -ml-2" aria-label="Toggle Navigation Menu">
            <span class="material-symbols-outlined force-black" aria-hidden="true">menu</span>
        </button>
        <a href="admin-pending-approvals.php" class="hidden sm:flex w-10 h-10 items-center justify-center rounded-xl bg-surface-hover dark:bg-white/5 hover:bg-surface-border transition-all text-text-muted">
          <span class="material-symbols-outlined force-black" aria-hidden="true">arrow_back</span>
        </a>
        <div class="flex flex-col">
          <div class="flex flex-wrap items-center gap-1 sm:gap-2 text-xs sm:text-xs font-bold tracking-widest force-black uppercase">
             <span class="hidden sm:inline">Admin</span>
             <span class="material-symbols-outlined text-xs hidden sm:inline force-black" aria-hidden="true">chevron_right</span>
             <span class="whitespace-nowrap">Pending Approvals</span>
             <span class="material-symbols-outlined text-xs force-black" aria-hidden="true">chevron_right</span>
             <span class="text-primary-base dark:text-primary-base whitespace-nowrap" id="bc-name"><div class="skeleton w-24 h-3 inline-block"></div></span>
          </div>
          <div class="text-xl sm:text-2xl font-bold force-black tracking-tight mt-1">Review Application</div>
        </div>
      </div>
      <div class="flex items-center gap-4">
        <span class="status-badge px-4 py-2 text-xs" id="top-status-badge">PENDING</span>
        </div>
    </div>

    <div class="p-4 sm:p-6 lg:p-8">
      <div class="flex flex-col lg:flex-row gap-6 lg:gap-10">
        <!-- LEFT COLUMN (60%) -->
        <div class="lg:w-[60%] space-y-6 sm:space-y-8">
          <!-- Personal Info -->
          <div class="ds-card">
            <div class="p-5 sm:p-6 border-b border-surface-border dark:border-white/5 flex flex-col sm:flex-row gap-6 items-start sm:items-center">
              <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-full border-4 border-surface-border dark:border-white/5 overflow-hidden shrink-0 bg-surface-subtle dark:bg-white/5 flex items-center justify-center relative">
                 <img loading="lazy" id="profile-photo" src="" alt="Profile Photo" class="w-full h-full object-cover hidden absolute inset-0 z-10" />
                 <span id="profile-photo-placeholder" class="material-symbols-outlined text-text-muted dark:text-text-muted text-4xl" aria-hidden="true">person</span>
              </div>
              <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                  <span class="material-symbols-outlined text-primary-base dark:text-primary-base text-sm" aria-hidden="true">person</span>
                  <span class="text-xs font-black uppercase tracking-widest text-text-muted">Personal Details</span>
                </div>
                <h2 class="text-xl sm:text-2xl font-bold tracking-tight force-black mb-1" id="header-full-name"><div class="skeleton w-40 h-6"></div></h2>
                <div class="text-xs font-mono font-bold tracking-widest text-primary-base dark:text-primary-base uppercase bg-primary/5 dark:bg-primary-surface/10 px-2 py-1 rounded inline-block" id="header-customer-id"><div class="skeleton w-24 h-3"></div></div>
              </div>
              <div class="hidden sm:block">
                 <span class="material-symbols-outlined text-primary-base dark:text-primary-base opacity-20" style="font-size: 64px;" aria-hidden="true">badge</span>
              </div>
            </div>
            <div class="p-5 sm:p-8 grid grid-cols-2 gap-4 sm:gap-x-10 sm:gap-y-6" id="personal-fields">
              <div>
                <label class="fin-label mb-1">Date of Birth</label>
                <div class="font-bold"><div class="skeleton w-24 h-4"></div></div>
              </div>
              <div>
                <label class="fin-label mb-1">Gender</label>
                <div class="font-bold"><div class="skeleton w-16 h-4"></div></div>
              </div>
              <div>
                <label class="fin-label mb-1">Marital Status</label>
                <div class="font-bold"><div class="skeleton w-20 h-4"></div></div>
              </div>
              <div>
                <label class="fin-label mb-1">Nationality</label>
                <div class="font-bold"><div class="skeleton w-20 h-4"></div></div>
              </div>
              <div>
                <label class="fin-label mb-1">Father's Name</label>
                <div class="font-bold"><div class="skeleton w-32 h-4"></div></div>
              </div>
              <div>
                <label class="fin-label mb-1">Mother's Name</label>
                <div class="font-bold"><div class="skeleton w-32 h-4"></div></div>
              </div>
              <div>
                <label class="fin-label mb-1">Occupation</label>
                <div class="font-bold"><div class="skeleton w-24 h-4"></div></div>
              </div>
              <div>
                <label class="fin-label mb-1">Source of Income</label>
                <div class="font-bold"><div class="skeleton w-24 h-4"></div></div>
              </div>
              <div>
                <label class="fin-label mb-1">Annual Income</label>
                <div class="font-bold"><div class="skeleton w-28 h-4"></div></div>
              </div>
              <div>
                <label class="fin-label mb-1">Phone</label>
                <div class="font-bold"><div class="skeleton w-32 h-4"></div></div>
              </div>
              <div>
                <label class="fin-label mb-1">Email</label>
                <div class="font-bold"><div class="skeleton w-40 h-4"></div></div>
              </div>
              <div>
                <label class="fin-label mb-1">SUBMITTED ON</label>
                <div class="font-bold"><div class="skeleton w-36 h-4"></div></div>
              </div>
              <div>
                <label class="fin-label mb-1">REGISTERED ON</label>
                <div class="font-bold"><div class="skeleton w-36 h-4"></div></div>
              </div>
            </div>
          </div>

          <!-- Address Info -->
          <div class="ds-card">
            <div class="p-5 sm:p-6 border-b border-surface-border dark:border-white/5 flex items-center gap-3">
              <span class="material-symbols-outlined text-primary-base dark:text-primary-base" aria-hidden="true">home</span>
              <h3 class="admin-section-title" style="font-size:16px;">Address Information</h3>
            </div>
            <div class="p-5 sm:p-8 space-y-6" id="address-fields">
              <div>
                <label class="fin-label mb-1">Street Address</label>
                <div class="font-bold"><div class="skeleton w-full max-w-sm h-4"></div></div>
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="fin-label mb-1">City</label>
                  <div class="font-bold"><div class="skeleton w-24 h-4"></div></div>
                </div>
                <div>
                  <label class="fin-label mb-1">State</label>
                  <div class="font-bold"><div class="skeleton w-32 h-4"></div></div>
                </div>
                <div>
                  <label class="fin-label mb-1">PIN Code</label>
                  <div class="font-bold"><div class="skeleton w-20 h-4"></div></div>
                </div>
                <div>
                  <label class="fin-label mb-1">Country</label>
                  <div class="font-bold"><div class="skeleton w-24 h-4"></div></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Identity Verification -->
          <div class="ds-card">
            <div class="p-5 sm:p-6 border-b border-surface-border dark:border-white/5 flex items-center gap-3">
              <span class="material-symbols-outlined text-primary-base dark:text-primary-base" aria-hidden="true">verified_user</span>
              <h3 class="admin-section-title" style="font-size:16px;">Identity Verification</h3>
            </div>
            <div class="p-5 sm:p-8">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 sm:gap-10 mb-6 sm:mb-8" id="identity-fields">
                <div>
                  <label class="fin-label mb-1">ID Type</label>
                  <div class="font-bold"><div class="skeleton w-28 h-4"></div></div>
                </div>
                <div>
                  <label class="fin-label mb-1">ID Number</label>
                  <div class="font-bold tracking-widest"><div class="skeleton w-36 h-4"></div></div>
                </div>
              </div>
              <div class="flex flex-col sm:flex-row gap-6">
                <div class="flex-1">
                  <label class="fin-label mb-2">Front Side</label>
                  <div class="aspect-video bg-surface-subtle dark:bg-white/5 rounded-2xl border border-dashed border-surface-border dark:border-white/10 flex flex-col items-center justify-center p-4 text-center cursor-pointer hover:border-primary transition-all">
                    <span class="material-symbols-outlined text-3xl opacity-20 mb-2" aria-hidden="true">image</span>
                    <span class="text-xs font-bold opacity-40">Click to view full image</span>
                  </div>
                </div>
                <div class="flex-1">
                  <label class="fin-label mb-2">Back Side</label>
                  <div class="aspect-video bg-surface-subtle dark:bg-white/5 rounded-2xl border border-dashed border-surface-border dark:border-white/10 flex flex-col items-center justify-center p-4 text-center cursor-pointer hover:border-primary transition-all">
                    <span class="material-symbols-outlined text-3xl opacity-20 mb-2" aria-hidden="true">image</span>
                    <span class="text-xs font-bold opacity-40">Click to view full image</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Account Preference -->
          <div class="ds-card">
            <div class="p-5 sm:p-6 border-b border-surface-border dark:border-white/5 flex items-center gap-3">
              <span class="material-symbols-outlined text-primary-base dark:text-primary-base" aria-hidden="true">account_balance_wallet</span>
              <h3 class="admin-section-title" style="font-size:16px;">Account Details</h3>
            </div>
            <div class="p-5 sm:p-8 grid grid-cols-2 gap-4" id="account-fields">
              <div>
                <label class="fin-label mb-2">Account Type</label>
                <div class="skeleton w-24 h-6 rounded-full"></div>
              </div>
              <div>
                <label class="fin-label mb-2">Initial Deposit</label>
                <div class="font-bold text-lg"><div class="skeleton w-20 h-6"></div></div>
              </div>
              <div>
                <label for="reject-reason-input" class="fin-label mb-2">Transaction PIN</label>
                <div class="skeleton w-16 h-6"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- RIGHT COLUMN (40%) -->
        <div class="lg:w-[40%] space-y-6 sm:space-y-8">
          <!-- Timeline -->
          <div class="ds-card">
            <div class="p-5 sm:p-6 border-b border-surface-border dark:border-white/5 flex items-center gap-3">
              <span class="material-symbols-outlined text-primary-base dark:text-primary-base" aria-hidden="true">timeline</span>
              <h3 class="admin-section-title" style="font-size:16px;">Application Status</h3>
            </div>
            <div class="p-5 sm:p-8 overflow-y-auto max-h-[400px]" style="scrollbar-width: thin; scrollbar-color: #cbd5e1 transparent;">
              <div class="space-y-8 relative" id="timeline-body">
                <div class="skeleton w-full h-8 mb-4"></div>
                <div class="skeleton w-full h-8 mb-4"></div>
                <div class="skeleton w-full h-8"></div>
              </div>
            </div>
          </div>

          <!-- Decision Card -->
          <div class="ds-card overflow-visible">
            <div class="p-5 sm:p-6 border-b border-surface-border dark:border-white/5 flex items-center gap-3">
              <span class="material-symbols-outlined text-primary-base dark:text-primary-base" aria-hidden="true">gavel</span>
              <h3 class="admin-section-title" style="font-size:16px;">Make Decision</h3>
            </div>
            <div class="p-5 sm:p-8 space-y-6 sm:space-y-8">
              <!-- Approve Section -->
              <div class="p-6 rounded-2xl border border-green-100 dark:border-green-900/40 bg-green-50/30 dark:bg-green-900/5">
                <div class="flex items-start gap-4 mb-6">
                  <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/30 text-green-600 flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined" aria-hidden="true">check_circle</span>
                  </div>
                  <div>
                    <div class="font-bold text-green-900 dark:text-green-300">Approve this application</div>
                    <div class="text-xs text-green-800/60 dark:text-green-400/60">Account number will be auto-generated</div>
                  </div>
                </div>
                <!-- Account Preview -->
                <div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-green-100 dark:border-green-900/40 text-center mb-6 shadow-sm">
                   <div class="text-xs font-black uppercase tracking-widest text-green-600 mb-1">Expected Account Number</div>
                   <div class="font-code text-xl font-black text-green-700 dark:text-green-400 tracking-wider">FNZ2026<span class="opacity-30">XXXX</span></div>
                </div>
                <button data-action="openApproveModal()" class="w-full py-4 text-white font-black rounded-2xl text-xs uppercase tracking-widest shadow-lg transition-all bg-success-surface shadow-emerald-500/20 hover:bg-success-surface hover:scale-[1.02] active:scale-[0.98]">Approve & Activate Account</button>
              </div>

              <div class="flex items-center gap-4 px-4">
                <div class="flex-1 h-px bg-surface-hover dark:bg-white/5"></div>
                <div class="text-xs font-black opacity-30 tracking-widest">OR</div>
                <div class="flex-1 h-px bg-surface-hover dark:bg-white/5"></div>
              </div>

              <!-- Reject Section -->
              <div class="p-6 rounded-2xl border border-danger-base dark:border-danger-base/40">
                <div class="flex items-start gap-4 mb-6">
                  <div class="w-10 h-10 rounded-full bg-danger-surface dark:bg-danger-surface/30 text-danger-base flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined" aria-hidden="true">cancel</span>
                  </div>
                  <div>
                    <div class="font-bold text-danger-base dark:text-danger-base">Reject this application</div>
                  </div>
                </div>
                <textarea id="reject-reason-input" placeholder="Enter reason for rejection. This will be shown to the applicant." class="w-full bg-surface-subtle dark:bg-white/5 border border-danger-base dark:border-danger-base/40 rounded-xl p-4 text-xs min-h-[100px] mb-6 focus:ring-red-500/20"></textarea>
                <button data-action="openRejectModal()" class="w-full py-4 bg-transparent border-2 border-danger-base text-danger-base hover:bg-danger-surface hover:border-danger-base hover:text-danger-base dark:hover:bg-danger-surface/10 font-black rounded-2xl text-xs uppercase tracking-widest transition-all">Reject Application</button>
              </div>


            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- APPROVE MODAL -->
  <div id="approveModal" class="fixed inset-0 z-[2000] hidden items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-primary/40 backdrop-blur-sm" data-action="closeApproveModal()"></div>
    <div class="ds-card w-full max-w-lg relative z-10 animate-modal-enter text-center p-8">
      <div class="w-20 h-20 rounded-full bg-green-50 dark:bg-green-900/20 text-green-500 mx-auto mb-6 flex items-center justify-center">
        <span class="material-symbols-outlined text-4xl" aria-hidden="true">check_circle</span>
      </div>
      <h3 class="admin-section-title text-2xl mb-4">Confirm Account Approval</h3>
      <p class="text-text-muted dark:text-text-muted mb-8 leading-relaxed">
        You are approving the account application of <span class="font-bold text-text-main dark:text-white" id="modal-approve-name">--</span>.
        A <span id="modal-acc-type">Savings</span> Account with number <span class="font-code font-bold text-green-600" id="modal-approve-acc">FNZ---</span> will be generated and activated. An email will be sent to the applicant immediately.
      </p>
      <div class="flex gap-3">
        <button data-action="closeApproveModal()" class="flex-1 btn btn-secondary py-4">Cancel</button>
        <button id="confirmApprove" class="flex-1 btn btn-primary py-4" >Yes, Approve Now</button>
      </div>
    </div>
  </div>

  <!-- REJECT MODAL -->
  <div id="rejectModal" class="fixed inset-0 z-[2000] hidden items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-primary/40 backdrop-blur-sm" data-action="closeRejectModal()"></div>
    <div class="ds-card w-full max-w-lg relative z-10 animate-modal-enter text-center p-8">
      <div class="w-20 h-20 rounded-full bg-danger-surface dark:bg-danger-surface/20 text-danger-base mx-auto mb-6 flex items-center justify-center">
        <span class="material-symbols-outlined text-4xl" aria-hidden="true">cancel</span>
      </div>
      <h3 class="admin-section-title text-2xl mb-4">Confirm Rejection</h3>
      <p class="text-text-muted dark:text-text-muted mb-6 leading-relaxed">
        You are rejecting the application of <span class="font-bold text-text-main dark:text-white" id="modal-reject-name">--</span>. The rejection reason will be sent to their email address.
      </p>
      
      <!-- Rejection Reason Preview -->
      <div class="bg-surface-subtle dark:bg-white/5 border border-surface-border dark:border-white/10 rounded-xl p-4 mb-8 text-left">
        <div class="text-xs font-black uppercase tracking-widest text-text-muted mb-1">REJECTION REASON:</div>
        <div id="rejection-reason-preview" class="text-xs font-semibold text-text-muted dark:text-text-muted italic">No reason provided</div>
      </div>

      <div class="flex gap-3">
        <button data-action="closeRejectModal()" class="flex-1 btn btn-secondary py-4">Cancel</button>
        <button id="confirmReject" class="flex-1 btn btn-primary py-4" >Yes, Reject Application</button>
      </div>
    </div>
  </div>

  

  <script defer src="/Finzo/public/assets/js/admin/review-application.js?v=5"></script>
    <script defer src="/Finzo/public/assets/js/core/component-loader.js"></script>
<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>