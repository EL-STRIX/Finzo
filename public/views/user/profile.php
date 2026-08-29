<?php
$portal = 'user';
$pageTitle = 'My Profile';
ob_start();
?>
<script defer src="/Finzo/public/assets/js/user/user-shared.js"></script>

<!-- Profile Header Banner -->
<div class="mb-8 ds-animate-fade flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-border-main pb-6">
    <div>
        <div class="text-xs font-bold text-text-muted uppercase tracking-widest mb-1 flex items-center gap-1.5">
            <span class="material-symbols-outlined text-sm text-primary-base">verified_user</span>
            <span>Customer Record &amp; Security Credentials</span>
        </div>
        <h1 class="font-display text-3xl font-black text-text-main tracking-tight">Customer Profile</h1>
        <p class="text-text-muted mt-1 text-sm">Official account records, personal details, address registry, identity verification, and security controls.</p>
    </div>
</div>

<!-- KYC Onboarding Banner for Pending/Rejected Users -->
<div id="kyc-notice-banner" class="hidden mb-6 p-5 rounded-2xl bg-amber-500/10 border border-amber-500/30 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-sm">
    <div class="flex items-center gap-3.5">
        <div class="w-11 h-11 rounded-xl bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-xl shrink-0">
            <span class="material-symbols-outlined text-2xl">assignment_ind</span>
        </div>
        <div>
            <h4 class="font-display text-sm font-bold text-text-main" id="kyc-banner-title">KYC Verification Pending</h4>
            <p class="text-xs text-text-muted mt-0.5" id="kyc-banner-desc">Please complete your identity and address onboarding to activate full banking services.</p>
        </div>
    </div>
    <a href="complete-profile.php" id="kyc-banner-btn" class="btn btn-primary text-xs shrink-0 font-bold px-4 py-2">
        <span>Complete KYC Onboarding</span>
        <span class="material-symbols-outlined text-sm">arrow_forward</span>
    </a>
</div>

<div class="flex flex-col lg:flex-row gap-6">
    <!-- LEFT COLUMN: Identity & Account Summary -->
    <div class="w-full lg:w-1/3 flex flex-col gap-6 ds-animate-slide-up">
        
        <!-- Identity Summary Card -->
        <div class="ds-card p-6 flex flex-col items-center text-center relative overflow-hidden">
            <div class="relative mb-4">
                <div class="w-24 h-24 rounded-full bg-primary-surface text-primary-base flex items-center justify-center font-display text-3xl font-extrabold border-4 border-surface shadow-sm overflow-hidden" id="avatar-circle">
                    <span class="skeleton w-full h-full rounded-full"></span>
                </div>
                <div class="absolute bottom-0 right-0 w-7 h-7 rounded-full bg-emerald-500 border-2 border-surface flex items-center justify-center text-white shadow-sm" title="Verified Account">
                    <span class="material-symbols-outlined text-sm" aria-hidden="true">check_circle</span>
                </div>
            </div>
            
            <h2 class="font-display text-xl font-bold text-text-main mb-1" id="profile-name"><span class="skeleton w-36 h-6"></span></h2>
            
            <div class="flex items-center gap-1.5 font-mono text-xs font-bold text-text-muted mb-3 bg-surface-subtle px-3 py-1 rounded-full border border-border-main">
                <span id="info-customer-id">Customer ID: —</span>
                <button type="button" onclick="copyText('info-customer-id', 'Customer ID')" class="text-text-muted hover:text-primary-base text-xs ml-1" title="Copy Customer ID">
                    <span class="material-symbols-outlined text-sm">content_copy</span>
                </button>
            </div>
            
            <div class="flex flex-wrap justify-center gap-2 mb-3" id="profile-badges">
                <div id="info-status"><span class="skeleton w-20 h-5"></span></div>
            </div>
            
            <div class="text-xs text-text-muted font-medium pt-2 border-t border-border-main w-full" id="info-member-since">Member since: —</div>
        </div>

        <!-- Account Ledger Card -->
        <div class="ds-card p-6">
            <div class="flex items-center gap-2.5 mb-4 pb-3 border-b border-border-main">
                <span class="material-symbols-outlined text-primary-base">account_balance_wallet</span>
                <h3 class="font-display font-bold text-text-main">Account Portfolio</h3>
            </div>

            <div class="space-y-2.5 text-xs">
                <div class="flex justify-between items-center p-3 rounded-xl bg-surface-subtle border border-border-main">
                    <span class="font-bold text-text-muted uppercase tracking-wider text-[10px]">Account No</span>
                    <div class="flex items-center gap-1 font-mono font-bold text-text-main">
                        <span id="info-acc-no">—</span>
                        <button type="button" onclick="copyText('info-acc-no', 'Account Number')" class="text-text-muted hover:text-primary-base ml-1" title="Copy Account Number">
                            <span class="material-symbols-outlined text-xs">content_copy</span>
                        </button>
                    </div>
                </div>
                
                <div class="flex justify-between items-center p-3 rounded-xl bg-surface-subtle border border-border-main">
                    <span class="font-bold text-text-muted uppercase tracking-wider text-[10px]">Account Type</span>
                    <span class="font-bold text-text-main capitalize" id="info-acc-type">—</span>
                </div>

                <div class="flex justify-between items-center p-3 rounded-xl bg-surface-subtle border border-border-main">
                    <span class="font-bold text-text-muted uppercase tracking-wider text-[10px]">Interest Rate</span>
                    <span class="font-bold text-emerald-600 dark:text-emerald-400" id="info-interest-rate">—</span>
                </div>

                <div class="flex justify-between items-center p-3 rounded-xl bg-surface-subtle border border-border-main">
                    <span class="font-bold text-text-muted uppercase tracking-wider text-[10px]">Daily Transfer Limit</span>
                    <span class="font-bold text-text-main" id="info-daily-limit">—</span>
                </div>
            </div>

            <div class="mt-4 p-5 rounded-2xl bg-gradient-to-br from-primary-600 to-indigo-700 text-white relative overflow-hidden shadow-md">
                <div class="relative z-10">
                    <div class="text-[10px] font-black text-white/70 uppercase tracking-widest mb-1">Available Account Balance</div>
                    <div class="font-display text-2xl font-black" id="info-balance">₹0.00</div>
                </div>
            </div>
        </div>

    </div>

    <!-- RIGHT COLUMN: Customer Details Cards -->
    <div class="w-full lg:w-2/3 flex flex-col gap-6 ds-animate-slide-up delay-100">
        
        <!-- 1. Personal & Demographics -->
        <div class="ds-card p-6">
            <div class="flex items-center gap-2 mb-4 border-b border-border-main pb-3">
                <span class="material-symbols-outlined text-primary-base">person</span>
                <h3 class="font-display text-base font-bold text-text-main">Personal &amp; Demographic Records</h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-text-muted font-semibold uppercase tracking-wider block mb-1 text-[10px]">Full Legal Name</span>
                    <div class="p-3 rounded-xl bg-surface-subtle border border-border-main font-bold text-text-main" id="view-full-name">—</div>
                </div>
                <div>
                    <span class="text-text-muted font-semibold uppercase tracking-wider block mb-1 text-[10px]">Date of Birth</span>
                    <div class="p-3 rounded-xl bg-surface-subtle border border-border-main font-medium text-text-main" id="view-dob">—</div>
                </div>
                <div>
                    <span class="text-text-muted font-semibold uppercase tracking-wider block mb-1 text-[10px]">Gender</span>
                    <div class="p-3 rounded-xl bg-surface-subtle border border-border-main font-medium text-text-main capitalize" id="view-gender">—</div>
                </div>
                <div>
                    <span class="text-text-muted font-semibold uppercase tracking-wider block mb-1 text-[10px]">Nationality</span>
                    <div class="p-3 rounded-xl bg-surface-subtle border border-border-main font-medium text-text-main" id="view-nationality">—</div>
                </div>
                <div>
                    <span class="text-text-muted font-semibold uppercase tracking-wider block mb-1 text-[10px]">Father's Name</span>
                    <div class="p-3 rounded-xl bg-surface-subtle border border-border-main font-medium text-text-main" id="view-father-name">—</div>
                </div>
                <div>
                    <span class="text-text-muted font-semibold uppercase tracking-wider block mb-1 text-[10px]">Mother's Name</span>
                    <div class="p-3 rounded-xl bg-surface-subtle border border-border-main font-medium text-text-main" id="view-mother-name">—</div>
                </div>
                <div>
                    <span class="text-text-muted font-semibold uppercase tracking-wider block mb-1 text-[10px]">Marital Status</span>
                    <div class="p-3 rounded-xl bg-surface-subtle border border-border-main font-medium text-text-main capitalize" id="view-marital-status">—</div>
                </div>
            </div>
        </div>

        <!-- 2. Contact & Residential Address -->
        <div class="ds-card p-6">
            <div class="flex items-center gap-2 mb-4 border-b border-border-main pb-3">
                <span class="material-symbols-outlined text-primary-base">contact_mail</span>
                <h3 class="font-display text-base font-bold text-text-main">Contact &amp; Residential Address</h3>
            </div>
            
            <div class="space-y-4 text-xs">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="edit-email" class="text-text-muted font-semibold uppercase tracking-wider block mb-1 text-[10px]">Primary Email Address</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-2.5 text-text-muted text-lg">mail</span>
                            <input type="email" id="edit-email" class="fin-input w-full pl-10" required />
                        </div>
                    </div>
                    <div>
                        <label for="edit-phone" class="text-text-muted font-semibold uppercase tracking-wider block mb-1 text-[10px]">Mobile Phone Number</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-2.5 text-text-muted text-lg">call</span>
                            <input type="tel" id="edit-phone" class="fin-input w-full pl-10" required />
                        </div>
                    </div>
                </div>

                <div class="border-t border-border-main pt-4">
                    <span class="text-text-muted font-semibold uppercase tracking-wider block mb-3 text-[10px]">Permanent Address</span>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <span class="text-text-muted block mb-0.5 text-[11px]">Street / House</span>
                            <div class="p-3 rounded-xl bg-surface-subtle border border-border-main font-medium text-text-main" id="view-street">—</div>
                        </div>
                        <div>
                            <span class="text-text-muted block mb-0.5 text-[11px]">City</span>
                            <div class="p-3 rounded-xl bg-surface-subtle border border-border-main font-medium text-text-main" id="view-city">—</div>
                        </div>
                        <div>
                            <span class="text-text-muted block mb-0.5 text-[11px]">State / Region</span>
                            <div class="p-3 rounded-xl bg-surface-subtle border border-border-main font-medium text-text-main" id="view-state">—</div>
                        </div>
                        <div>
                            <span class="text-text-muted block mb-0.5 text-[11px]">Postal / Pincode</span>
                            <div class="p-3 rounded-xl bg-surface-subtle border border-border-main font-mono font-medium text-text-main" id="view-pincode">—</div>
                        </div>
                        <div>
                            <span class="text-text-muted block mb-0.5 text-[11px]">Country</span>
                            <div class="p-3 rounded-xl bg-surface-subtle border border-border-main font-medium text-text-main" id="view-country">—</div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="button" id="btn-save-profile" class="btn btn-primary text-xs font-bold px-4 py-2" onclick="saveProfile()">
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
                    <span class="text-text-muted font-semibold uppercase tracking-wider block mb-1 text-[10px]">ID Document Type</span>
                    <div class="p-3 rounded-xl bg-surface-subtle border border-border-main font-bold text-text-main" id="view-id-type">—</div>
                </div>
                <div>
                    <span class="text-text-muted font-semibold uppercase tracking-wider block mb-1 text-[10px]">ID Document Number</span>
                    <div class="p-3 rounded-xl bg-surface-subtle border border-border-main font-mono font-bold text-text-main flex items-center justify-between">
                        <span id="view-id-number">• • • •  • • • •  —</span>
                        <button type="button" id="btn-toggle-id" onclick="toggleIdMask()" class="text-primary-base hover:underline text-[11px] font-bold">Show</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Employment & Financial Records -->
        <div class="ds-card p-6">
            <div class="flex items-center gap-2 mb-4 border-b border-border-main pb-3">
                <span class="material-symbols-outlined text-primary-base">work</span>
                <h3 class="font-display text-base font-bold text-text-main">Employment &amp; Financial Profile</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                <div>
                    <span class="text-text-muted font-semibold uppercase tracking-wider block mb-1 text-[10px]">Occupation</span>
                    <div class="p-3 rounded-xl bg-surface-subtle border border-border-main font-medium text-text-main capitalize" id="view-occupation">—</div>
                </div>
                <div>
                    <span class="text-text-muted font-semibold uppercase tracking-wider block mb-1 text-[10px]">Source of Income</span>
                    <div class="p-3 rounded-xl bg-surface-subtle border border-border-main font-medium text-text-main capitalize" id="view-source-income">—</div>
                </div>
                <div>
                    <span class="text-text-muted font-semibold uppercase tracking-wider block mb-1 text-[10px]">Annual Income</span>
                    <div class="p-3 rounded-xl bg-surface-subtle border border-border-main font-mono font-bold text-text-main" id="view-annual-income">—</div>
                </div>
            </div>
        </div>

        <!-- 5. Security & Password Management Card -->
        <div class="ds-card p-6">
            <div class="flex items-center gap-2 mb-4 border-b border-border-main pb-3">
                <span class="material-symbols-outlined text-primary-base">lock</span>
                <h3 class="font-display text-base font-bold text-text-main">Security &amp; Password Management</h3>
            </div>

            <form id="change-password-form" onsubmit="event.preventDefault(); updatePassword();" class="space-y-4 text-xs">
                <div>
                    <label for="current-pass" class="text-text-muted font-semibold uppercase tracking-wider block mb-1 text-[10px]">Current Password</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-2.5 text-text-muted text-lg">key</span>
                        <input type="password" id="current-pass" class="fin-input w-full pl-10 pr-10" placeholder="••••••••" required />
                        <button type="button" onclick="togglePassVisibility('current-pass', 'icon-toggle-current')" class="absolute right-3 top-2.5 text-text-muted hover:text-text-main">
                            <span class="material-symbols-outlined text-lg" id="icon-toggle-current">visibility</span>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="new-pass" class="text-text-muted font-semibold uppercase tracking-wider block mb-1 text-[10px]">New Password</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-2.5 text-text-muted text-lg">lock</span>
                            <input type="password" id="new-pass" class="fin-input w-full pl-10 pr-10" placeholder="••••••••" required minlength="8" />
                            <button type="button" onclick="togglePassVisibility('new-pass', 'icon-toggle-new')" class="absolute right-3 top-2.5 text-text-muted hover:text-text-main">
                                <span class="material-symbols-outlined text-lg" id="icon-toggle-new">visibility</span>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label for="confirm-pass" class="text-text-muted font-semibold uppercase tracking-wider block mb-1 text-[10px]">Confirm New Password</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-2.5 text-text-muted text-lg">lock_reset</span>
                            <input type="password" id="confirm-pass" class="fin-input w-full pl-10 pr-10" placeholder="••••••••" required minlength="8" />
                            <button type="button" onclick="togglePassVisibility('confirm-pass', 'icon-toggle-confirm')" class="absolute right-3 top-2.5 text-text-muted hover:text-text-main">
                                <span class="material-symbols-outlined text-lg" id="icon-toggle-confirm">visibility</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" id="btn-update-password" class="btn btn-primary text-xs font-bold px-4 py-2">
                        <span class="material-symbols-outlined text-sm">lock_reset</span>
                        <span>Update Password</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- 6. Two-Factor Authentication (2FA) Security Card (Coming Soon) -->
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
