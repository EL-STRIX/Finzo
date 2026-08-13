<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin - Review Application';
$pageHeader = '
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <div class="flex items-center gap-2 mb-2">
            <a href="admin-pending-approvals.php" class="w-8 h-8 flex items-center justify-center rounded-xl bg-surface-hover hover:bg-border-subtle transition-colors text-text-secondary">
                <span class="material-symbols-outlined text-sm" aria-hidden="true">arrow_back</span>
            </a>
            <div class="flex items-center gap-1.5 text-[10px] font-black tracking-widest uppercase text-text-secondary">
                <span>Pending Approvals</span>
                <span class="material-symbols-outlined text-xs" aria-hidden="true">chevron_right</span>
                <span class="text-primary whitespace-nowrap" id="bc-name"><div class="skeleton w-24 h-3 inline-block"></div></span>
            </div>
        </div>
        <h1 class="text-xl font-headline font-black text-text-primary tracking-tight">Review Application</h1>
    </div>
    <div class="flex items-center">
        <span class="status-badge px-4 py-2 text-[10px] font-black uppercase tracking-widest bg-warning-bg text-warning rounded-full border border-warning" id="top-status-badge">PENDING</span>
    </div>
</div>
';
ob_start();
?>

    <div class="flex flex-col lg:flex-row gap-6 lg:gap-10">
        <!-- LEFT COLUMN (60%) -->
        <div class="lg:w-[60%] space-y-6 sm:space-y-8">
          <!-- Personal Info -->
          <div class="ds-card">
            <div class="p-5 sm:p-6 border-b border-border-subtle flex flex-col sm:flex-row gap-6 items-start sm:items-center">
              <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-full border-4 border-border-subtle overflow-hidden shrink-0 bg-surface-subtle flex items-center justify-center relative">
                 <img loading="lazy" id="profile-photo" src="" alt="Profile Photo" class="w-full h-full object-cover hidden absolute inset-0 z-10" />
                 <span id="profile-photo-placeholder" class="material-symbols-outlined text-text-secondary text-4xl" aria-hidden="true">person</span>
              </div>
              <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                  <span class="material-symbols-outlined text-primary text-sm" aria-hidden="true">person</span>
                  <span class="text-[10px] font-black uppercase tracking-widest text-text-secondary">Personal Details</span>
                </div>
                <h2 class="text-xl sm:text-2xl font-black tracking-tight text-text-primary mb-1" id="header-full-name"><div class="skeleton w-40 h-6"></div></h2>
                <div class="text-[10px] font-mono font-black tracking-widest text-primary uppercase bg-primary-50 border border-primary-100 px-2 py-1 rounded inline-block" id="header-customer-id"><div class="skeleton w-24 h-3"></div></div>
              </div>
              <div class="hidden sm:block">
                 <span class="material-symbols-outlined text-border-subtle text-[64px]" aria-hidden="true">badge</span>
              </div>
            </div>
            <div class="p-5 sm:p-8 grid grid-cols-2 gap-4 sm:gap-x-10 sm:gap-y-6 text-text-primary" id="personal-fields">
              <div>
                <label class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase">Date of Birth</label>
                <div class="font-bold" id="val-dob"><div class="skeleton w-24 h-4"></div></div>
              </div>
              <div>
                <label class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase">Gender</label>
                <div class="font-bold" id="val-gender"><div class="skeleton w-16 h-4"></div></div>
              </div>
              <div>
                <label class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase">Marital Status</label>
                <div class="font-bold" id="val-marital-status"><div class="skeleton w-20 h-4"></div></div>
              </div>
              <div>
                <label class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase">Nationality</label>
                <div class="font-bold" id="val-nationality"><div class="skeleton w-20 h-4"></div></div>
              </div>
              <div>
                <label class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase">Father's Name</label>
                <div class="font-bold" id="val-father-name"><div class="skeleton w-32 h-4"></div></div>
              </div>
              <div>
                <label class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase">Mother's Name</label>
                <div class="font-bold" id="val-mother-name"><div class="skeleton w-32 h-4"></div></div>
              </div>
              <div>
                <label class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase">Occupation</label>
                <div class="font-bold" id="val-occupation"><div class="skeleton w-24 h-4"></div></div>
              </div>
              <div>
                <label class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase">Source of Income</label>
                <div class="font-bold" id="val-source-of-income"><div class="skeleton w-24 h-4"></div></div>
              </div>
              <div>
                <label class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase">Annual Income</label>
                <div class="font-bold" id="val-annual-income"><div class="skeleton w-28 h-4"></div></div>
              </div>
              <div>
                <label class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase">Phone</label>
                <div class="font-bold" id="val-phone"><div class="skeleton w-32 h-4"></div></div>
              </div>
              <div>
                <label class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase">Email</label>
                <div class="font-bold" id="val-email"><div class="skeleton w-40 h-4"></div></div>
              </div>
              <div>
                <label class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase">SUBMITTED ON</label>
                <div class="font-bold" id="val-kyc-submitted-at"><div class="skeleton w-36 h-4"></div></div>
              </div>
              <div>
                <label class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase">REGISTERED ON</label>
                <div class="font-bold" id="val-created-at"><div class="skeleton w-36 h-4"></div></div>
              </div>
            </div>
          </div>

          <!-- Address Info -->
          <div class="ds-card">
            <div class="p-5 sm:p-6 border-b border-border-subtle flex items-center gap-3">
              <span class="material-symbols-outlined text-primary" aria-hidden="true">home</span>
              <h3 class="text-sm font-black uppercase tracking-widest text-text-primary">Address Information</h3>
            </div>
            <div class="p-5 sm:p-8 space-y-6 text-text-primary" id="address-fields">
              <div>
                <label class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase">Street Address</label>
                <div class="font-bold" id="val-street"><div class="skeleton w-full max-w-sm h-4"></div></div>
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase">City</label>
                  <div class="font-bold" id="val-city"><div class="skeleton w-24 h-4"></div></div>
                </div>
                <div>
                  <label class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase">State</label>
                  <div class="font-bold" id="val-state"><div class="skeleton w-32 h-4"></div></div>
                </div>
                <div>
                  <label class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase">PIN Code</label>
                  <div class="font-bold" id="val-pincode"><div class="skeleton w-20 h-4"></div></div>
                </div>
                <div>
                  <label class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase">Country</label>
                  <div class="font-bold" id="val-country"><div class="skeleton w-24 h-4"></div></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Identity Verification -->
          <div class="ds-card">
            <div class="p-5 sm:p-6 border-b border-border-subtle flex items-center gap-3">
              <span class="material-symbols-outlined text-primary" aria-hidden="true">verified_user</span>
              <h3 class="text-sm font-black uppercase tracking-widest text-text-primary">Identity Verification</h3>
            </div>
            <div class="p-5 sm:p-8">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 sm:gap-10 mb-6 sm:mb-8 text-text-primary" id="identity-fields">
                <div>
                  <label class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase">ID Type</label>
                  <div class="font-bold" id="val-id-type"><div class="skeleton w-28 h-4"></div></div>
                </div>
                <div>
                  <label class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase">ID Number</label>
                  <div class="font-bold tracking-widest" id="val-id-number"><div class="skeleton w-36 h-4"></div></div>
                </div>
              </div>
              <div class="flex flex-col sm:flex-row gap-6">
                <div class="flex-1">
                  <label class="fin-label mb-2 block text-[10px] font-black text-text-secondary uppercase">Front Side</label>
                  <div class="aspect-video bg-surface-subtle rounded-2xl border border-dashed border-border-subtle flex flex-col items-center justify-center p-4 text-center cursor-pointer hover:border-primary transition-all">
                    <span class="material-symbols-outlined text-3xl opacity-20 mb-2 text-text-primary" aria-hidden="true">image</span>
                    <span class="text-[10px] font-bold text-text-secondary uppercase">Click to view full image</span>
                  </div>
                </div>
                <div class="flex-1">
                  <label class="fin-label mb-2 block text-[10px] font-black text-text-secondary uppercase">Back Side</label>
                  <div class="aspect-video bg-surface-subtle rounded-2xl border border-dashed border-border-subtle flex flex-col items-center justify-center p-4 text-center cursor-pointer hover:border-primary transition-all">
                    <span class="material-symbols-outlined text-3xl opacity-20 mb-2 text-text-primary" aria-hidden="true">image</span>
                    <span class="text-[10px] font-bold text-text-secondary uppercase">Click to view full image</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Account Preference -->
          <div class="ds-card">
            <div class="p-5 sm:p-6 border-b border-border-subtle flex items-center gap-3">
              <span class="material-symbols-outlined text-primary" aria-hidden="true">account_balance_wallet</span>
              <h3 class="text-sm font-black uppercase tracking-widest text-text-primary">Account Details</h3>
            </div>
            <div class="p-5 sm:p-8 grid grid-cols-2 gap-4 text-text-primary" id="account-fields">
              <div>
                <label class="fin-label mb-2 block text-[10px] font-black text-text-secondary uppercase">Account Type</label>
                <div id="val-account-type"><div class="skeleton w-24 h-6 rounded-full"></div></div>
              </div>
              <div>
                <label class="fin-label mb-2 block text-[10px] font-black text-text-secondary uppercase">Initial Deposit</label>
                <div class="font-bold text-lg" id="val-initial-deposit"><div class="skeleton w-20 h-6"></div></div>
              </div>
              <div>
                <label class="fin-label mb-2 block text-[10px] font-black text-text-secondary uppercase">Transaction PIN</label>
                <div id="val-transaction-pin"><div class="skeleton w-16 h-6"></div></div>
              </div>
            </div>
          </div>
        </div>

        <!-- RIGHT COLUMN (40%) -->
        <div class="lg:w-[40%] space-y-6 sm:space-y-8">
          <!-- Timeline -->
          <div class="ds-card">
            <div class="p-5 sm:p-6 border-b border-border-subtle flex items-center gap-3">
              <span class="material-symbols-outlined text-primary" aria-hidden="true">timeline</span>
              <h3 class="text-sm font-black uppercase tracking-widest text-text-primary">Application Status</h3>
            </div>
            <div class="p-5 sm:p-8 overflow-y-auto max-h-[400px] custom-scrollbar">
              <div class="space-y-8 relative" id="timeline-body">
                <div class="skeleton w-full h-8 mb-4"></div>
                <div class="skeleton w-full h-8 mb-4"></div>
                <div class="skeleton w-full h-8"></div>
              </div>
            </div>
          </div>

          <!-- Decision Card -->
          <div class="ds-card overflow-visible">
            <div class="p-5 sm:p-6 border-b border-border-subtle flex items-center gap-3">
              <span class="material-symbols-outlined text-primary" aria-hidden="true">gavel</span>
              <h3 class="text-sm font-black uppercase tracking-widest text-text-primary">Make Decision</h3>
            </div>
            <div class="p-5 sm:p-8 space-y-6 sm:space-y-8">
              <!-- Approve Section -->
              <div class="p-6 rounded-2xl border border-success-bg bg-success-bg/30">
                <div class="flex items-start gap-4 mb-6">
                  <div class="w-10 h-10 rounded-full bg-success-bg text-success flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined" aria-hidden="true">check_circle</span>
                  </div>
                  <div>
                    <div class="font-bold text-success">Approve this application</div>
                    <div class="text-[10px] font-bold text-success opacity-80 uppercase tracking-widest">Account number will be auto-generated</div>
                  </div>
                </div>
                <!-- Account Preview -->
                <div class="bg-surface p-4 rounded-xl border border-success-bg text-center mb-6 shadow-sm">
                   <div class="text-[10px] font-black uppercase tracking-widest text-success mb-1">Expected Account Number</div>
                   <div class="font-code text-xl font-black text-success tracking-wider">FNZ2026<span class="opacity-30">XXXX</span></div>
                </div>
                <button data-action="openApproveModal()" class="w-full btn btn-primary !bg-success !text-white !py-4 rounded-2xl text-xs shadow-lg shadow-success/20 justify-center">Approve & Activate Account</button>
              </div>

              <div class="flex items-center gap-4 px-4">
                <div class="flex-1 h-px bg-border-subtle"></div>
                <div class="text-[10px] font-black opacity-30 tracking-widest text-text-primary">OR</div>
                <div class="flex-1 h-px bg-border-subtle"></div>
              </div>

              <!-- Reject Section -->
              <div class="p-6 rounded-2xl border border-danger">
                <div class="flex items-start gap-4 mb-6">
                  <div class="w-10 h-10 rounded-full bg-danger-bg text-danger flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined" aria-hidden="true">cancel</span>
                  </div>
                  <div>
                    <div class="font-bold text-danger">Reject this application</div>
                  </div>
                </div>
                <textarea id="reject-reason-input" placeholder="Enter reason for rejection. This will be shown to the applicant." class="fin-input w-full min-h-[100px] mb-6"></textarea>
                <button data-action="openRejectModal()" class="w-full btn btn-secondary !border-danger !text-danger hover:!bg-danger hover:!text-white !py-4 rounded-2xl text-xs justify-center">Reject Application</button>
              </div>
            </div>
          </div>
        </div>
    </div>

  <!-- APPROVE MODAL -->
  <div id="approveModal" class="fixed inset-0 z-[2000] hidden items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-background/80 backdrop-blur-sm" data-action="closeApproveModal()"></div>
    <div class="ds-card w-full max-w-lg relative z-10 animate-modal-enter text-center p-8 border border-border-subtle">
      <div class="w-20 h-20 rounded-full bg-success-bg text-success mx-auto mb-6 flex items-center justify-center">
        <span class="material-symbols-outlined text-4xl" aria-hidden="true">check_circle</span>
      </div>
      <h3 class="text-xl font-headline font-black text-text-primary mb-4">Confirm Account Approval</h3>
      <p class="text-sm text-text-secondary mb-8 leading-relaxed">
        You are approving the account application of <span class="font-bold text-text-primary" id="modal-approve-name">--</span>.
        A <span id="modal-acc-type">Savings</span> Account with number <span class="font-code font-bold text-success" id="modal-approve-acc">FNZ---</span> will be generated and activated. An email will be sent to the applicant immediately.
      </p>
      <div class="flex gap-3">
        <button data-action="closeApproveModal()" class="flex-1 btn btn-secondary py-4 justify-center">Cancel</button>
        <button id="confirmApprove" class="flex-1 btn btn-primary !bg-success !text-white py-4 justify-center">Yes, Approve Now</button>
      </div>
    </div>
  </div>

  <!-- REJECT MODAL -->
  <div id="rejectModal" class="fixed inset-0 z-[2000] hidden items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-background/80 backdrop-blur-sm" data-action="closeRejectModal()"></div>
    <div class="ds-card w-full max-w-lg relative z-10 animate-modal-enter text-center p-8 border border-border-subtle">
      <div class="w-20 h-20 rounded-full bg-danger-bg text-danger mx-auto mb-6 flex items-center justify-center">
        <span class="material-symbols-outlined text-4xl" aria-hidden="true">cancel</span>
      </div>
      <h3 class="text-xl font-headline font-black text-text-primary mb-4">Confirm Rejection</h3>
      <p class="text-sm text-text-secondary mb-6 leading-relaxed">
        You are rejecting the application of <span class="font-bold text-text-primary" id="modal-reject-name">--</span>. The rejection reason will be sent to their email address.
      </p>
      
      <!-- Rejection Reason Preview -->
      <div class="bg-surface-subtle border border-border-subtle rounded-xl p-4 mb-8 text-left">
        <div class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1">REJECTION REASON:</div>
        <div id="rejection-reason-preview" class="text-xs font-semibold text-text-primary italic">No reason provided</div>
      </div>

      <div class="flex gap-3">
        <button data-action="closeRejectModal()" class="flex-1 btn btn-secondary py-4 justify-center">Cancel</button>
        <button id="confirmReject" class="flex-1 btn btn-primary !bg-danger !text-white py-4 justify-center">Yes, Reject Application</button>
      </div>
    </div>
  </div>

<?php
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/review-application.js?v=6"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
