<?php
$portal = 'user';
$pageTitle = 'My Profile';
ob_start();
?>
<script defer src="/Finzo/public/assets/js/user/user-shared.js"></script>

<!-- KYC Action Banner -->
<div id="kyc-notice-banner" class="hidden mb-5 p-4 rounded-lg bg-amber-50 dark:bg-amber-500/10 border border-amber-300 dark:border-amber-500/40 flex flex-col sm:flex-row items-center justify-between gap-3">
    <div class="flex items-center gap-3">
        <span class="material-symbols-outlined text-amber-600 dark:text-amber-400 text-xl shrink-0">warning</span>
        <div>
            <p class="text-sm font-bold text-text-main" id="kyc-banner-title">KYC Verification Required</p>
            <p class="text-xs text-text-muted" id="kyc-banner-desc">Complete identity verification to unlock all banking services.</p>
        </div>
    </div>
    <a href="complete-profile.php" id="kyc-banner-btn" class="btn btn-primary text-xs shrink-0">
        <span>Complete KYC</span>
        <span class="material-symbols-outlined text-sm">chevron_right</span>
    </a>
</div>

<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-3">
    <div>
        <p class="text-xs font-semibold text-text-muted uppercase tracking-widest mb-0.5">Account Management</p>
        <h1 class="font-display text-2xl font-bold text-text-main">My Profile</h1>
    </div>
    <div class="flex items-center gap-2 text-xs text-text-muted">
        <span class="material-symbols-outlined text-sm text-emerald-500">lock</span>
        <span>Session Encrypted &bull; TLS 1.3</span>
    </div>
</div>

<!-- Main Profile Grid -->
<div class="flex flex-col xl:flex-row gap-5">

    <!-- LEFT PANEL -->
    <div class="w-full xl:w-80 shrink-0 flex flex-col gap-4">

        <!-- Customer Identity -->
        <div class="ds-card overflow-hidden">
            <!-- Header strip -->
            <div class="h-16 bg-gradient-to-r from-primary-700 to-indigo-700 relative">
                <div class="absolute inset-0 opacity-10 bg-[repeating-linear-gradient(45deg,transparent,transparent_10px,rgba(255,255,255,.15)_10px,rgba(255,255,255,.15)_11px)]"></div>
            </div>
            <!-- Avatar overlapping strip -->
            <div class="px-5 pb-5 -mt-10">
                <div class="w-20 h-20 rounded-full border-4 border-surface bg-primary-surface text-primary-base flex items-center justify-center font-bold text-2xl shadow overflow-hidden mb-3" id="avatar-circle">
                    <span class="text-primary-base font-bold text-2xl">—</span>
                </div>
                <h2 class="font-display text-lg font-bold text-text-main leading-tight mb-0.5" id="profile-name">—</h2>
                <p class="font-mono text-[11px] text-text-muted mb-3" id="info-customer-id">Customer ID: —</p>
                <div id="info-status" class="mb-4"></div>

                <div class="space-y-1.5 text-xs">
                    <div class="flex items-center justify-between py-1.5 border-b border-border-main">
                        <span class="text-text-muted font-medium">Member Since</span>
                        <span class="font-semibold text-text-main font-mono" id="info-member-since">—</span>
                    </div>
                    <div class="flex items-center justify-between py-1.5 border-b border-border-main">
                        <span class="text-text-muted font-medium">Account Type</span>
                        <span class="font-semibold text-text-main capitalize" id="info-acc-type">—</span>
                    </div>
                    <div class="flex items-center justify-between py-1.5 border-b border-border-main">
                        <span class="text-text-muted font-medium">Interest Rate</span>
                        <span class="font-semibold text-emerald-600 dark:text-emerald-400" id="info-interest-rate">—</span>
                    </div>
                    <div class="flex items-center justify-between py-1.5">
                        <span class="text-text-muted font-medium">Daily Limit</span>
                        <span class="font-semibold text-text-main" id="info-daily-limit">—</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Number & Balance -->
        <div class="ds-card p-5 bg-gradient-to-br from-slate-900 to-slate-800 dark:from-slate-950 dark:to-slate-900 border-slate-700 text-white">
            <div class="text-[10px] font-black uppercase tracking-widest text-white/50 mb-3">Account Details</div>
            <div class="flex items-center justify-between gap-2 mb-4">
                <span class="font-mono text-sm font-bold tracking-widest" id="info-acc-no">——————</span>
                <button type="button" onclick="copyText('info-acc-no', 'Account Number')" title="Copy" class="w-7 h-7 rounded-md bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors">
                    <span class="material-symbols-outlined text-sm text-white/70">content_copy</span>
                </button>
            </div>
            <div class="text-[10px] font-bold uppercase tracking-widest text-white/50 mb-1">Available Balance</div>
            <div class="font-display text-2xl font-black text-white" id="info-balance">₹0.00</div>
        </div>

    </div>

    <!-- RIGHT PANEL: Tabbed Detail Sections -->
    <div class="flex-1 min-w-0">

        <!-- Tab Navigation -->
        <div class="flex border-b border-border-main mb-5 overflow-x-auto gap-0 no-scrollbar">
            <button type="button" class="profile-tab active" data-tab="personal" onclick="switchTab('personal')">
                <span class="material-symbols-outlined text-[16px]">person</span>
                <span>Personal</span>
            </button>
            <button type="button" class="profile-tab" data-tab="contact" onclick="switchTab('contact')">
                <span class="material-symbols-outlined text-[16px]">contact_mail</span>
                <span>Contact</span>
            </button>
            <button type="button" class="profile-tab" data-tab="identity" onclick="switchTab('identity')">
                <span class="material-symbols-outlined text-[16px]">badge</span>
                <span>Identity</span>
            </button>
            <button type="button" class="profile-tab" data-tab="financial" onclick="switchTab('financial')">
                <span class="material-symbols-outlined text-[16px]">work</span>
                <span>Financial</span>
            </button>
            <button type="button" class="profile-tab" data-tab="security" onclick="switchTab('security')">
                <span class="material-symbols-outlined text-[16px]">lock</span>
                <span>Security</span>
            </button>
        </div>

        <!-- Tab: Personal -->
        <div id="tab-personal" class="profile-tab-panel ds-card p-6">
            <h3 class="text-xs font-black uppercase tracking-widest text-text-muted mb-4">Personal &amp; Demographic Information</h3>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-0">
                <?php
                $rows = [
                    ['view-full-name',       'Full Legal Name'],
                    ['view-dob',             'Date of Birth'],
                    ['view-gender',          'Gender'],
                    ['view-nationality',     'Nationality'],
                    ['view-father-name',     'Father\'s Name'],
                    ['view-mother-name',     'Mother\'s Name'],
                    ['view-marital-status',  'Marital Status'],
                ];
                foreach ($rows as [$id, $label]): ?>
                <div class="flex flex-col py-3 border-b border-border-main">
                    <dt class="text-[10px] font-bold text-text-muted uppercase tracking-wider mb-0.5"><?= htmlspecialchars($label) ?></dt>
                    <dd class="text-sm font-semibold text-text-main" id="<?= htmlspecialchars($id) ?>">—</dd>
                </div>
                <?php endforeach; ?>
            </dl>
        </div>

        <!-- Tab: Contact -->
        <div id="tab-contact" class="profile-tab-panel ds-card p-6 hidden">
            <h3 class="text-xs font-black uppercase tracking-widest text-text-muted mb-4">Contact &amp; Address Records</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                <div>
                    <label for="edit-email" class="text-[10px] font-bold text-text-muted uppercase tracking-wider block mb-1.5">Primary Email</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-[18px] pointer-events-none">mail</span>
                        <input type="email" id="edit-email" class="fin-input w-full pl-9 text-sm" required />
                    </div>
                </div>
                <div>
                    <label for="edit-phone" class="text-[10px] font-bold text-text-muted uppercase tracking-wider block mb-1.5">Mobile Number</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-[18px] pointer-events-none">call</span>
                        <input type="tel" id="edit-phone" class="fin-input w-full pl-9 text-sm" required />
                    </div>
                </div>
            </div>

            <div class="flex justify-end mb-6">
                <button type="button" id="btn-save-profile" class="btn btn-primary text-xs" onclick="saveProfile()">
                    <span class="material-symbols-outlined text-sm">save</span>
                    <span>Save Changes</span>
                </button>
            </div>

            <div class="border-t border-border-main pt-4">
                <h4 class="text-[10px] font-black uppercase tracking-widest text-text-muted mb-3">Permanent / Residential Address</h4>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-0">
                    <?php
                    $addrRows = [
                        ['view-street',  'Street / House No'],
                        ['view-city',    'City'],
                        ['view-state',   'State / Region'],
                        ['view-pincode', 'Postal / Pincode'],
                        ['view-country', 'Country'],
                    ];
                    foreach ($addrRows as [$id, $label]): ?>
                    <div class="flex flex-col py-3 border-b border-border-main">
                        <dt class="text-[10px] font-bold text-text-muted uppercase tracking-wider mb-0.5"><?= htmlspecialchars($label) ?></dt>
                        <dd class="text-sm font-semibold text-text-main" id="<?= htmlspecialchars($id) ?>">—</dd>
                    </div>
                    <?php endforeach; ?>
                </dl>
            </div>
        </div>

        <!-- Tab: Identity -->
        <div id="tab-identity" class="profile-tab-panel ds-card p-6 hidden">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xs font-black uppercase tracking-widest text-text-muted">Government Identity Verification</h3>
                <span class="inline-flex items-center gap-1.5 text-[10px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 border border-emerald-500/30 px-2.5 py-1 rounded-full">
                    <span class="material-symbols-outlined text-sm">encrypted</span>
                    <span>AES-256 Encrypted</span>
                </span>
            </div>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-0">
                <div class="flex flex-col py-3 border-b border-border-main">
                    <dt class="text-[10px] font-bold text-text-muted uppercase tracking-wider mb-0.5">ID Document Type</dt>
                    <dd class="text-sm font-semibold text-text-main" id="view-id-type">—</dd>
                </div>
                <div class="flex flex-col py-3 border-b border-border-main">
                    <dt class="text-[10px] font-bold text-text-muted uppercase tracking-wider mb-0.5">ID Document Number</dt>
                    <dd class="flex items-center justify-between text-sm font-semibold text-text-main font-mono">
                        <span id="view-id-number">•••• •••• ——</span>
                        <button type="button" id="btn-toggle-id" onclick="toggleIdMask()" class="text-[10px] font-bold text-primary-base hover:underline ml-2">Show</button>
                    </dd>
                </div>
            </dl>
        </div>

        <!-- Tab: Financial -->
        <div id="tab-financial" class="profile-tab-panel ds-card p-6 hidden">
            <h3 class="text-xs font-black uppercase tracking-widest text-text-muted mb-4">Employment &amp; Financial Profile</h3>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-0">
                <?php
                $finRows = [
                    ['view-occupation',    'Occupation'],
                    ['view-source-income', 'Source of Income'],
                    ['view-annual-income', 'Annual Income'],
                ];
                foreach ($finRows as [$id, $label]): ?>
                <div class="flex flex-col py-3 border-b border-border-main">
                    <dt class="text-[10px] font-bold text-text-muted uppercase tracking-wider mb-0.5"><?= htmlspecialchars($label) ?></dt>
                    <dd class="text-sm font-semibold text-text-main capitalize" id="<?= htmlspecialchars($id) ?>">—</dd>
                </div>
                <?php endforeach; ?>
            </dl>
        </div>

        <!-- Tab: Security -->
        <div id="tab-security" class="profile-tab-panel hidden flex flex-col gap-4">

            <!-- Change Password -->
            <div class="ds-card p-6">
                <h3 class="text-xs font-black uppercase tracking-widest text-text-muted mb-4">Change Login Password</h3>
                <form id="change-password-form" onsubmit="event.preventDefault(); updatePassword();" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label for="current-pass" class="text-[10px] font-bold text-text-muted uppercase tracking-wider block mb-1.5">Current Password</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-[18px] pointer-events-none">key</span>
                            <input type="password" id="current-pass" class="fin-input w-full pl-9 pr-10 text-sm" placeholder="Enter current password" required />
                            <button type="button" onclick="togglePassVisibility('current-pass','icon-toggle-current')" class="absolute right-3 top-1/2 -translate-y-1/2 text-text-muted hover:text-text-main">
                                <span class="material-symbols-outlined text-[18px]" id="icon-toggle-current">visibility</span>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label for="new-pass" class="text-[10px] font-bold text-text-muted uppercase tracking-wider block mb-1.5">New Password</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-[18px] pointer-events-none">lock</span>
                            <input type="password" id="new-pass" class="fin-input w-full pl-9 pr-10 text-sm" placeholder="Min. 8 characters" required minlength="8" />
                            <button type="button" onclick="togglePassVisibility('new-pass','icon-toggle-new')" class="absolute right-3 top-1/2 -translate-y-1/2 text-text-muted hover:text-text-main">
                                <span class="material-symbols-outlined text-[18px]" id="icon-toggle-new">visibility</span>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label for="confirm-pass" class="text-[10px] font-bold text-text-muted uppercase tracking-wider block mb-1.5">Confirm New Password</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-[18px] pointer-events-none">lock_reset</span>
                            <input type="password" id="confirm-pass" class="fin-input w-full pl-9 pr-10 text-sm" placeholder="Re-enter new password" required minlength="8" />
                            <button type="button" onclick="togglePassVisibility('confirm-pass','icon-toggle-confirm')" class="absolute right-3 top-1/2 -translate-y-1/2 text-text-muted hover:text-text-main">
                                <span class="material-symbols-outlined text-[18px]" id="icon-toggle-confirm">visibility</span>
                            </button>
                        </div>
                    </div>
                    <div class="sm:col-span-2 flex justify-end">
                        <button type="submit" id="btn-update-password" class="btn btn-primary text-xs">
                            <span class="material-symbols-outlined text-sm">lock_reset</span>
                            <span>Update Password</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- 2FA Coming Soon -->
            <div class="ds-card p-6">
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-primary-surface text-primary-base flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-xl">shield_lock</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-text-main">Two-Factor Authentication</h3>
                            <p class="text-[11px] text-text-muted">TOTP &amp; hardware key authentication</p>
                        </div>
                    </div>
                    <span class="badge badge-warning text-[10px] uppercase font-bold tracking-widest shrink-0">Coming Soon</span>
                </div>
                <div class="p-3 rounded-lg bg-surface-subtle border border-border-main text-xs text-text-muted flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse shrink-0"></span>
                    <span>Authenticator app integration is under security audit.</span>
                    <button type="button" class="ml-auto btn btn-secondary text-xs opacity-50 cursor-not-allowed" disabled>Configure</button>
                </div>
            </div>

        </div>

    </div>

</div>

<style>
.profile-tab {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.625rem 1.125rem;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--color-text-muted, #6b7280);
    border-bottom: 2px solid transparent;
    white-space: nowrap;
    transition: color 0.15s, border-color 0.15s;
    cursor: pointer;
    background: transparent;
    border-top: none; border-left: none; border-right: none;
}
.profile-tab:hover { color: var(--color-text-main, #111); }
.profile-tab.active { color: var(--color-primary-base, #3b82f6); border-bottom-color: var(--color-primary-base, #3b82f6); font-weight: 700; }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>

<script>
function switchTab(name) {
    document.querySelectorAll('.profile-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.profile-tab-panel').forEach(p => p.classList.add('hidden'));
    document.querySelector(`[data-tab="${name}"]`).classList.add('active');
    const panel = document.getElementById('tab-' + name);
    if (panel) panel.classList.remove('hidden');
}
</script>

<script defer src="/Finzo/public/assets/js/user/profile.js"></script>

<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
