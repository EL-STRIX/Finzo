<?php
$portal = 'user';
$pageTitle = 'My Profile';
ob_start();
?>
<script defer src="/Finzo/public/assets/js/user/user-shared.js"></script>

<!-- Profile Header -->
<div class="mb-8 ds-animate-fade">
    <div class="text-xs font-bold text-text-muted uppercase tracking-wider mb-1">Banking Identity &amp; Profile</div>
    <h1 class="font-display text-3xl font-extrabold text-text-main tracking-tight">Customer Account Profile</h1>
    <p class="text-text-muted mt-1 text-sm">Official customer record, registered personal details, address, identity documentation, and security controls.</p>
</div>

<!-- KYC Onboarding Banner for Pending/Rejected Users -->
<div id="kyc-notice-banner" class="hidden mb-6 p-5 rounded-xl bg-amber-500/10 border border-amber-500/30 flex flex-col sm:flex-row items-center justify-between gap-4">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-xl shrink-0">
            <span class="material-symbols-outlined">assignment_ind</span>
        </div>
        <div>
            <h4 class="font-display text-sm font-bold text-text-main" id="kyc-banner-title">KYC Verification Pending</h4>
            <p class="text-xs text-text-muted mt-0.5" id="kyc-banner-desc">Please complete your identity and address onboarding to activate full banking services.</p>
        </div>
    </div>
    <a href="complete-profile.php" id="kyc-banner-btn" class="btn btn-primary text-xs shrink-0">
        <span>Complete KYC Onboarding</span>
        <span class="material-symbols-outlined text-sm">arrow_forward</span>
    </a>
</div>

<div class="flex flex-col lg:flex-row gap-6">
    <!-- LEFT COLUMN: Identity & Account Overview -->
    <div class="w-full lg:w-1/3 flex flex-col gap-6 ds-animate-slide-up">
        
        <!-- Identity Summary Card -->
        <div class="ds-card p-6 flex flex-col items-center text-center">
            <div class="relative mb-4">
                <div class="w-24 h-24 rounded-full bg-primary-surface text-primary-base flex items-center justify-center font-display text-3xl font-extrabold border-4 border-surface shadow-sm overflow-hidden" id="avatar-circle">
                    <span class="skeleton w-full h-full rounded-full"></span>
                </div>
                <div class="absolute bottom-0 right-0 w-7 h-7 rounded-full bg-emerald-500 border-2 border-surface flex items-center justify-center text-white" title="Customer Identity">
                    <span class="material-symbols-outlined text-sm" aria-hidden="true">verified</span>
                </div>
            </div>
            
            <h2 class="font-display text-xl font-bold text-text-main mb-1" id="profile-name"><span class="skeleton w-36 h-6"></span></h2>
            <div class="font-mono text-xs font-bold text-text-muted mb-3" id="info-customer-id">Customer ID: —</div>
            
            <div class="flex flex-wrap justify-center gap-2 mb-3" id="profile-badges">
                <div id="info-status"><span class="skeleton w-20 h-5"></span></div>
            </div>
            <div class="text-xs text-text-muted font-medium" id="info-member-since">Member since: —</div>
        </div>

        <!-- Account Ledger Card -->
        <div class="ds-card p-6">
            <div class="flex items-center gap-3 mb-4">
                <span class="material-symbols-outlined text-primary-base">account_balance_wallet</span>
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
                <div class="relative z-10">
                    <div class="text-[10px] font-black text-white/70 uppercase tracking-widest mb-1">Available Account Balance</div>
                    <div class="font-display text-2xl font-black" id="info-balance">₹0.00</div>
                </div>
            </div>
        </div>

    </div>

    <!-- RIGHT COLUMN: Details & Security Cards -->
    <div class="w-full lg:w-2/3 flex flex-col gap-6 ds-animate-slide-up delay-100">
        
        <!-- 1. Personal & Demographics -->
        <div class="ds-card p-6">
            <div class="flex items-center gap-2 mb-4 border-b border-border-main pb-3">
                <span class="material-symbols-outlined text-primary-base">person</span>
                <h3 class="font-display text-base font-bold text-text-main">Personal &amp; Demographic Information</h3>
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

        <!-- 2. Contact & Address Records -->
        <div class="ds-card p-6">
            <div class="flex items-center gap-2 mb-4 border-b border-border-main pb-3">
                <span class="material-symbols-outlined text-primary-base">contact_mail</span>
                <h3 class="font-display text-base font-bold text-text-main">Contact &amp; Residential Address</h3>
            </div>
            
            <div class="space-y-4 text-xs">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="edit-email" class="text-text-muted font-semibold uppercase tracking-wider block mb-1">Primary Email Address</label>
                        <input type="email" id="edit-email" class="fin-input w-full" required />
                    </div>
                    <div>
                        <label for="edit-phone" class="text-text-muted font-semibold uppercase tracking-wider block mb-1">Mobile Phone Number</label>
                        <input type="tel" id="edit-phone" class="fin-input w-full" required />
                    </div>
                </div>

                <div class="border-t border-border-main pt-3">
                    <span class="text-text-muted font-semibold uppercase tracking-wider block mb-2">Residential Address</span>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <span class="text-text-muted block mb-0.5 text-[11px]">Street / House</span>
                            <div class="p-2.5 rounded-lg bg-surface-subtle border border-border-main font-medium text-text-main" id="view-street">—</div>
                        </div>
                        <div>
                            <span class="text-text-muted block mb-0.5 text-[11px]">City</span>
                            <div class="p-2.5 rounded-lg bg-surface-subtle border border-border-main font-medium text-text-main" id="view-city">—</div>
                        </div>
                        <div>
                            <span class="text-text-muted block mb-0.5 text-[11px]">State / Region</span>
                            <div class="p-2.5 rounded-lg bg-surface-subtle border border-border-main font-medium text-text-main" id="view-state">—</div>
                        </div>
                        <div>
                            <span class="text-text-muted block mb-0.5 text-[11px]">Postal / Pincode</span>
                            <div class="p-2.5 rounded-lg bg-surface-subtle border border-border-main font-mono font-medium text-text-main" id="view-pincode">—</div>
                        </div>
                        <div>
                            <span class="text-text-muted block mb-0.5 text-[11px]">Country</span>
                            <div class="p-2.5 rounded-lg bg-surface-subtle border border-border-main font-medium text-text-main" id="view-country">—</div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="button" class="btn btn-primary text-xs" onclick="saveProfile()">
                        <span class="material-symbols-outlined text-sm">save</span>
                        <span>Save Contact Details</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- 3. Government Identity Verification -->
        <div class="ds-card p-6">
            <div class="flex items-center justify-between border-b border-border-main pb-3 mb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary-base">badge</span>
                    <h3 class="font-display text-base font-bold text-text-main">Government Identity Verification</h3>
                </div>
                <span class="badge badge-success text-[10px] uppercase font-bold tracking-wider">Encrypted Record</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-text-muted font-semibold uppercase tracking-wider block mb-1">ID Document Type</span>
                    <div class="p-2.5 rounded-lg bg-surface-subtle border border-border-main font-bold text-text-main" id="view-id-type">—</div>
                </div>
                <div>
                    <span class="text-text-muted font-semibold uppercase tracking-wider block mb-1">ID Document Number</span>
                    <div class="p-2.5 rounded-lg bg-surface-subtle border border-border-main font-mono font-bold text-text-main flex items-center justify-between">
                        <span id="view-id-number">• • • •  • • • •  —</span>
                        <button type="button" id="btn-toggle-id" onclick="toggleIdMask()" class="text-primary-base hover:underline text-[11px] font-bold">Show</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Employment & Financial Profile -->
        <div class="ds-card p-6">
            <div class="flex items-center gap-2 mb-4 border-b border-border-main pb-3">
                <span class="material-symbols-outlined text-primary-base">work</span>
                <h3 class="font-display text-base font-bold text-text-main">Employment &amp; Financial Profile</h3>
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
                    <div class="p-2.5 rounded-lg bg-surface-subtle border border-border-main font-mono font-bold text-text-main" id="view-annual-income">—</div>
                </div>
            </div>
        </div>

        <!-- 5. Two-Factor Authentication (2FA) Security Card (Coming Soon) -->
        <div class="ds-card p-6 border border-border-main bg-surface">
            <div class="flex items-center justify-between border-b border-border-main pb-3 mb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-primary-surface text-primary-base flex items-center justify-center font-bold">
                        <span class="material-symbols-outlined text-lg">shield_lock</span>
                    </div>
                    <div>
                        <h3 class="font-display text-base font-bold text-text-main">Two-Factor Authentication (2FA)</h3>
                        <p class="text-[11px] text-text-muted">Hardware key &amp; TOTP authenticator protection</p>
                    </div>
                </div>
                <span class="badge badge-warning text-[10px] uppercase font-bold tracking-wider">Coming Soon</span>
            </div>

            <div class="p-4 rounded-xl bg-surface-subtle border border-border-main flex flex-col sm:flex-row items-center justify-between gap-4 text-xs">
                <div class="space-y-1">
                    <div class="font-bold text-text-main flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        <span>Multi-Factor Authentication Setup</span>
                    </div>
                    <p class="text-text-muted text-[11px]">TOTP authenticator app and SMS verification modules are under security review.</p>
                </div>
                <button type="button" class="btn btn-secondary text-xs opacity-60 cursor-not-allowed shrink-0" disabled>
                    <span>Configure 2FA</span>
                    <span class="material-symbols-outlined text-sm">lock</span>
                </button>
            </div>
        </div>

    </div>
</div>

<script defer src="/Finzo/public/assets/js/user/profile.js"></script>

<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
