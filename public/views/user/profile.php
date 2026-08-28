<?php
$portal = 'user';
$pageTitle = 'My Profile';
ob_start();
?>
<script defer src="/Finzo/public/assets/js/user/user-shared.js"></script>

<div class="mb-8 ds-animate-fade">
    <div class="text-xs font-bold text-text-muted uppercase tracking-wider mb-1">Identity & Customer Profile</div>
    <h1 class="font-display text-3xl font-extrabold text-text-main tracking-tight">Your Profile</h1>
    <p class="text-text-muted mt-1">View and manage your registered personal details, address, identity documentation, and financial profile.</p>
</div>

<!-- KYC Onboarding Banner for Pending/Rejected Users -->
<div id="kyc-notice-banner" class="hidden mb-6 p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 flex flex-col sm:flex-row items-center justify-between gap-4">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-xl shrink-0">
            <span class="material-symbols-outlined">assignment_ind</span>
        </div>
        <div>
            <h4 class="font-display text-sm font-bold text-text-main" id="kyc-banner-title">KYC Onboarding Pending</h4>
            <p class="text-xs text-text-muted mt-0.5" id="kyc-banner-desc">Please complete your identity and address verification to activate full banking features.</p>
        </div>
    </div>
    <a href="complete-profile.php" id="kyc-banner-btn" class="btn btn-primary text-xs shrink-0">
        <span>Complete KYC Onboarding</span>
        <span class="material-symbols-outlined text-sm">arrow_forward</span>
    </a>
</div>
  
<div class="flex flex-col lg:flex-row gap-6">
    <!-- LEFT PANE: Identity Summary & Account Ledger -->
    <div class="w-full lg:w-1/3 flex flex-col gap-6 ds-animate-slide-up">
        <!-- Identity Summary Card -->
        <div class="ds-card p-6 flex flex-col items-center text-center">
            <div class="relative mb-4">
                <div class="w-24 h-24 rounded-full bg-primary-surface text-primary-base flex items-center justify-center font-display text-3xl font-extrabold border-4 border-surface shadow-sm overflow-hidden" id="avatar-circle">
                    <span class="skeleton w-full h-full rounded-full"></span>
                </div>
                <div class="absolute bottom-0 right-0 w-7 h-7 rounded-full bg-emerald-500 border-2 border-surface flex items-center justify-center text-white" title="Verified Customer">
                    <span class="material-symbols-outlined text-sm" aria-hidden="true">verified</span>
                </div>
            </div>
            
            <h2 class="font-display text-xl font-bold text-text-main mb-1" id="profile-name"><span class="skeleton w-36 h-6"></span></h2>
            <div class="font-mono text-xs font-bold text-text-muted mb-3" id="info-customer-id">Customer ID: —</div>
            
            <div class="flex flex-wrap justify-center gap-2 mb-2" id="profile-badges">
                <div id="info-status"><span class="skeleton w-20 h-5"></span></div>
                <span class="badge badge-success text-[10px] uppercase font-bold tracking-wider">Verified Identity</span>
            </div>
            <div class="text-xs text-text-muted mt-1 font-medium" id="info-member-since">Member since: —</div>
        </div>
      
        <!-- Account Ledger Card -->
        <div class="ds-card p-6">
            <div class="flex items-center gap-3 mb-4">
                <span class="material-symbols-outlined text-primary-base" aria-hidden="true">account_balance_wallet</span>
                <h3 class="font-display font-bold text-text-main">Account Ledger</h3>
            </div>
        
            <div class="space-y-2.5 text-xs">
                <div class="flex justify-between items-center p-2.5 rounded-lg bg-surface-subtle border border-border-main">
                    <span class="font-bold text-text-muted uppercase">Account No</span>
                    <span class="font-mono font-bold text-text-main" id="info-acc-no">—</span>
                </div>
                
                <div class="flex justify-between items-center p-2.5 rounded-lg bg-surface-subtle border border-border-main">
                    <span class="font-bold text-text-muted uppercase">Account Type</span>
                    <span class="font-bold text-text-main capitalize" id="info-acc-type">—</span>
                </div>

                <div class="flex justify-between items-center p-2.5 rounded-lg bg-surface-subtle border border-border-main">
                    <span class="font-bold text-text-muted uppercase">Interest Rate</span>
                    <span class="font-bold text-emerald-600 dark:text-emerald-400" id="info-interest-rate">—</span>
                </div>

                <div class="flex justify-between items-center p-2.5 rounded-lg bg-surface-subtle border border-border-main">
                    <span class="font-bold text-text-muted uppercase">Daily Limit</span>
                    <span class="font-bold text-text-main" id="info-daily-limit">—</span>
                </div>
            </div>
        
            <div class="mt-4 p-5 rounded-xl bg-gradient-to-br from-primary-600 to-indigo-700 text-white relative overflow-hidden shadow-md">
                <div class="absolute -right-6 -bottom-6 opacity-10">
                    <span class="material-symbols-outlined text-8xl" aria-hidden="true">account_balance</span>
                </div>
                <div class="relative z-10">
                    <div class="text-[10px] font-black text-white/70 uppercase tracking-widest mb-1">Total Account Balance</div>
                    <div class="font-display text-2xl font-black" id="info-balance">₹0.00</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- RIGHT PANE: Full Customer Information Cards -->
    <div class="w-full lg:w-2/3 flex flex-col gap-6 ds-animate-slide-up delay-100">
      
        <!-- 1. Personal & Demographic Details -->
        <div class="ds-card p-6">
            <div class="flex items-center gap-2 mb-4 border-b border-border-main pb-3">
                <span class="material-symbols-outlined text-primary-base">person</span>
                <h3 class="font-display text-base font-bold text-text-main">Personal & Demographic Information</h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-text-muted font-semibold uppercase tracking-wider block mb-1">Full Legal Name</span>
                    <div class="p-2.5 rounded-lg bg-surface-subtle border border-border-main font-bold text-text-main" id="view-full-name">—</div>
                </div>
                <div>
                    <span class="text-text-muted font-semibold uppercase tracking-wider block mb-1">Date of Birth</span>
                    <div class="p-2.5 rounded-lg bg-surface-subtle border border-border-main font-medium text-text-main" id="view-dob">—</div>
                </div>
                <div>
                    <span class="text-text-muted font-semibold uppercase tracking-wider block mb-1">Gender</span>
                    <div class="p-2.5 rounded-lg bg-surface-subtle border border-border-main font-medium text-text-main capitalize" id="view-gender">—</div>
                </div>
                <div>
                    <span class="text-text-muted font-semibold uppercase tracking-wider block mb-1">Nationality</span>
                    <div class="p-2.5 rounded-lg bg-surface-subtle border border-border-main font-medium text-text-main" id="view-nationality">—</div>
                </div>
                <div>
                    <span class="text-text-muted font-semibold uppercase tracking-wider block mb-1">Father's Name</span>
                    <div class="p-2.5 rounded-lg bg-surface-subtle border border-border-main font-medium text-text-main" id="view-father-name">—</div>
                </div>
                <div>
                    <span class="text-text-muted font-semibold uppercase tracking-wider block mb-1">Mother's Name</span>
                    <div class="p-2.5 rounded-lg bg-surface-subtle border border-border-main font-medium text-text-main" id="view-mother-name">—</div>
                </div>
                <div>
                    <span class="text-text-muted font-semibold uppercase tracking-wider block mb-1">Marital Status</span>
                    <div class="p-2.5 rounded-lg bg-surface-subtle border border-border-main font-medium text-text-main capitalize" id="view-marital-status">—</div>
                </div>
            </div>
        </div>

        <!-- 2. Contact Details & Residential Address -->
        <div class="ds-card p-6">
            <div class="flex items-center justify-between mb-4 border-b border-border-main pb-3">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary-base">home_pin</span>
                    <h3 class="font-display text-base font-bold text-text-main">Contact & Residential Address</h3>
                </div>
            </div>
            
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group mb-0">
                        <label for="edit-email" class="form-label text-xs uppercase tracking-wider mb-1 block text-text-muted">Primary Email Address</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-sm">mail</span>
                            <input type="email" class="form-control pl-9 text-xs" id="edit-email" placeholder="name@example.com"/>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label for="edit-phone" class="form-label text-xs uppercase tracking-wider mb-1 block text-text-muted">Mobile Phone Number</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-sm">call</span>
                            <input type="text" class="form-control pl-9 text-xs" id="edit-phone" placeholder="+91 98765 43210"/>
                        </div>
                    </div>
                </div>

                <div>
                    <span class="text-xs text-text-muted font-semibold uppercase tracking-wider block mb-1">Residential Street Address</span>
                    <div class="p-2.5 rounded-lg bg-surface-subtle border border-border-main text-xs font-medium text-text-main" id="view-street">—</div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs">
                    <div>
                        <span class="text-text-muted font-semibold uppercase tracking-wider block mb-1">City</span>
                        <div class="p-2 rounded-lg bg-surface-subtle border border-border-main font-medium text-text-main" id="view-city">—</div>
                    </div>
                    <div>
                        <span class="text-text-muted font-semibold uppercase tracking-wider block mb-1">State</span>
                        <div class="p-2 rounded-lg bg-surface-subtle border border-border-main font-medium text-text-main" id="view-state">—</div>
                    </div>
                    <div>
                        <span class="text-text-muted font-semibold uppercase tracking-wider block mb-1">Pincode</span>
                        <div class="p-2 rounded-lg bg-surface-subtle border border-border-main font-mono font-medium text-text-main" id="view-pincode">—</div>
                    </div>
                    <div>
                        <span class="text-text-muted font-semibold uppercase tracking-wider block mb-1">Country</span>
                        <div class="p-2 rounded-lg bg-surface-subtle border border-border-main font-medium text-text-main" id="view-country">—</div>
                    </div>
                </div>

                <div class="flex justify-end pt-3 border-t border-border-main">
                    <button type="button" class="btn btn-primary text-xs" onclick="saveProfile()">
                        <span class="material-symbols-outlined text-sm">save</span>
                        <span>Save Contact Details</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- 3. Identity Verification & Document Registry -->
        <div class="ds-card p-6">
            <div class="flex items-center gap-2 mb-4 border-b border-border-main pb-3">
                <span class="material-symbols-outlined text-primary-base">badge</span>
                <h3 class="font-display text-base font-bold text-text-main">Identity Verification & Documents</h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-text-muted font-semibold uppercase tracking-wider block mb-1">Government ID Type</span>
                    <div class="p-2.5 rounded-lg bg-surface-subtle border border-border-main font-bold text-text-main uppercase" id="view-id-type">—</div>
                </div>

                <div>
                    <span class="text-text-muted font-semibold uppercase tracking-wider block mb-1">Identification Number</span>
                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-surface-subtle border border-border-main">
                        <span class="font-mono font-bold text-text-main" id="view-id-number">•••• •••• ••••</span>
                        <button type="button" class="text-primary-base hover:underline text-xs font-bold" id="btn-toggle-id" onclick="toggleIdMask()">Show</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Employment & Financial Details -->
        <div class="ds-card p-6">
            <div class="flex items-center gap-2 mb-4 border-b border-border-main pb-3">
                <span class="material-symbols-outlined text-primary-base">work</span>
                <h3 class="font-display text-base font-bold text-text-main">Employment & Financial Profile</h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                <div>
                    <span class="text-text-muted font-semibold uppercase tracking-wider block mb-1">Occupation</span>
                    <div class="p-2.5 rounded-lg bg-surface-subtle border border-border-main font-medium text-text-main capitalize" id="view-occupation">—</div>
                </div>
                <div>
                    <span class="text-text-muted font-semibold uppercase tracking-wider block mb-1">Source of Income</span>
                    <div class="p-2.5 rounded-lg bg-surface-subtle border border-border-main font-medium text-text-main capitalize" id="view-source-income">—</div>
                </div>
                <div>
                    <span class="text-text-muted font-semibold uppercase tracking-wider block mb-1">Annual Income</span>
                    <div class="p-2.5 rounded-lg bg-surface-subtle border border-border-main font-bold text-emerald-600 dark:text-emerald-400" id="view-annual-income">—</div>
                </div>
            </div>
        </div>
      
        <!-- 5. Credentials & Security Shortcuts -->
        <div class="ds-card border-dashed border-primary-base/40 bg-primary-surface/30 p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-primary-surface flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-primary-base text-2xl" aria-hidden="true">lock_reset</span>
                </div>
                <div>
                    <h3 class="font-display font-bold text-text-main">Security & Access Controls</h3>
                    <div class="text-xs text-text-muted">Manage 2FA authenticators, update transaction PIN, or change password.</div>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="user-security.php" class="btn btn-secondary text-xs">Security Center</a>
                <a href="change-password.php" class="btn btn-primary text-xs">Change Password</a>
            </div>
        </div>
      
    </div>
</div>

<script defer src="/Finzo/public/assets/js/core/utils.js"></script>
<script defer src="/Finzo/public/assets/js/core/api.js"></script>
<script defer src="/Finzo/public/assets/js/user/profile.js"></script>
<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
