<?php
$portal = 'user';
$pageTitle = 'My Profile';
ob_start();
?>
<script defer src="/Finzo/public/assets/js/user/user-shared.js"></script>

<!-- KYC Action Banner -->
<div id="kyc-notice-banner" class="hidden mb-5 p-4 rounded-lg bg-amber-50 dark:bg-amber-500/10 border border-amber-300 dark:border-amber-500/30 flex flex-col sm:flex-row items-center justify-between gap-3">
    <div class="flex items-center gap-3">
        <span class="material-symbols-outlined text-amber-600 dark:text-amber-400 shrink-0">warning</span>
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
<div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-2">
    <div>
        <p class="text-[11px] font-semibold text-text-muted uppercase tracking-widest mb-0.5">Account Management</p>
        <h1 class="font-display text-2xl font-bold text-text-main">My Profile</h1>
    </div>
    <div class="flex items-center gap-1.5 text-[11px] text-text-muted">
        <span class="material-symbols-outlined text-sm text-emerald-500">lock</span>
        <span>Session Encrypted · TLS 1.3</span>
    </div>
</div>

<div class="flex flex-col xl:flex-row gap-5">

    <!-- ═══ LEFT PANEL ═══ -->
    <div class="w-full xl:w-72 shrink-0 flex flex-col gap-4">

        <!-- Identity Card -->
        <div class="ds-card overflow-hidden">
            <!-- Banner strip -->
            <div class="h-14 bg-gradient-to-r from-primary-700 via-primary-600 to-indigo-600 relative">
                <div class="absolute inset-0" style="background:repeating-linear-gradient(-45deg,rgba(255,255,255,.04),rgba(255,255,255,.04) 1px,transparent 1px,transparent 8px)"></div>
            </div>
            <!-- Profile info -->
            <div class="px-5 pt-0 pb-5 -mt-8">
                <div class="w-16 h-16 rounded-full border-[3px] border-surface bg-primary-surface flex items-center justify-center font-bold text-xl overflow-hidden shadow mb-3" id="avatar-circle">
                    <span class="text-primary-base">—</span>
                </div>
                <h2 class="text-base font-bold text-text-main leading-snug mb-0.5" id="profile-name">—</h2>
                <p class="font-mono text-[11px] text-text-muted mb-2" id="info-customer-id">—</p>
                <div id="info-status" class="mb-4"></div>

                <table class="w-full text-xs border-collapse">
                    <tbody>
                        <tr class="border-b border-border-main">
                            <td class="py-2 text-text-muted font-medium pr-3">Member Since</td>
                            <td class="py-2 font-semibold text-text-main text-right font-mono" id="info-member-since">—</td>
                        </tr>
                        <tr class="border-b border-border-main">
                            <td class="py-2 text-text-muted font-medium pr-3">Account Type</td>
                            <td class="py-2 font-semibold text-text-main text-right capitalize" id="info-acc-type">—</td>
                        </tr>
                        <tr class="border-b border-border-main">
                            <td class="py-2 text-text-muted font-medium pr-3">Interest Rate</td>
                            <td class="py-2 font-bold text-emerald-600 dark:text-emerald-400 text-right" id="info-interest-rate">—</td>
                        </tr>
                        <tr>
                            <td class="py-2 text-text-muted font-medium pr-3">Daily Limit</td>
                            <td class="py-2 font-semibold text-text-main text-right" id="info-daily-limit">—</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Account Card (dark) -->
        <div class="rounded-xl p-5 bg-slate-900 dark:bg-slate-950 border border-slate-700">
            <p class="text-[9px] font-black uppercase tracking-widest text-slate-500 mb-3">Bank Account</p>
            <div class="flex items-center justify-between mb-1">
                <span class="font-mono text-sm font-bold text-white tracking-widest" id="info-acc-no">——————</span>
                <button type="button" onclick="copyText('info-acc-no','Account Number')" title="Copy" class="w-7 h-7 rounded bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors">
                    <span class="material-symbols-outlined text-[14px] text-slate-400">content_copy</span>
                </button>
            </div>
            <p class="text-[9px] font-bold uppercase tracking-widest text-slate-500 mb-1 mt-4">Available Balance</p>
            <p class="font-display text-xl font-black text-white" id="info-balance">₹0.00</p>
        </div>

    </div>

    <!-- ═══ RIGHT PANEL ═══ -->
    <div class="flex-1 min-w-0">

        <!-- Tab nav -->
        <div class="flex border-b border-border-main mb-5 overflow-x-auto no-scrollbar">
            <button class="profile-tab active" data-tab="personal" onclick="switchTab('personal')">
                <span class="material-symbols-outlined text-[15px]">person</span>Personal
            </button>
            <button class="profile-tab" data-tab="contact" onclick="switchTab('contact')">
                <span class="material-symbols-outlined text-[15px]">contact_mail</span>Contact
            </button>
            <button class="profile-tab" data-tab="identity" onclick="switchTab('identity')">
                <span class="material-symbols-outlined text-[15px]">badge</span>Identity
            </button>
            <button class="profile-tab" data-tab="financial" onclick="switchTab('financial')">
                <span class="material-symbols-outlined text-[15px]">work</span>Financial
            </button>
            <button class="profile-tab" data-tab="security" onclick="switchTab('security')">
                <span class="material-symbols-outlined text-[15px]">lock</span>Security
            </button>
        </div>

        <!-- ── Tab: Personal ── -->
        <div id="tab-personal" class="profile-tab-panel ds-card">
            <div class="px-5 py-3 border-b border-border-main">
                <h3 class="text-[10px] font-black uppercase tracking-widest text-text-muted">Personal &amp; Demographic Information</h3>
            </div>
            <table class="w-full text-xs">
                <tbody>
                    <?php
                    $rows = [
                        ['view-full-name',      'Full Legal Name'],
                        ['view-dob',            'Date of Birth'],
                        ['view-gender',         'Gender'],
                        ['view-nationality',    'Nationality'],
                        ['view-father-name',    "Father's Name"],
                        ['view-mother-name',    "Mother's Name"],
                        ['view-marital-status', 'Marital Status'],
                    ];
                    foreach ($rows as $i => [$id, $label]):
                        $bg = ($i % 2 === 0) ? '' : 'bg-surface-subtle/50';
                    ?>
                    <tr class="border-b border-border-main <?= $bg ?>">
                        <td class="px-5 py-3 text-text-muted font-semibold w-44 text-[11px] uppercase tracking-wide"><?= htmlspecialchars($label) ?></td>
                        <td class="px-5 py-3 font-semibold text-text-main" id="<?= htmlspecialchars($id) ?>">—</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- ── Tab: Contact ── -->
        <div id="tab-contact" class="profile-tab-panel hidden">
            <!-- Editable contact -->
            <div class="ds-card p-5 mb-4">
                <div class="text-[10px] font-black uppercase tracking-widest text-text-muted mb-4">Contact Information</div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="edit-email" class="text-[10px] font-bold text-text-muted uppercase tracking-wide block mb-1.5">Primary Email</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-[17px] pointer-events-none">mail</span>
                            <input type="email" id="edit-email" class="fin-input w-full pl-9 text-sm" />
                        </div>
                    </div>
                    <div>
                        <label for="edit-phone" class="text-[10px] font-bold text-text-muted uppercase tracking-wide block mb-1.5">Mobile Number</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-[17px] pointer-events-none">call</span>
                            <input type="tel" id="edit-phone" class="fin-input w-full pl-9 text-sm" />
                        </div>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="button" id="btn-save-profile" class="btn btn-primary text-xs" onclick="saveProfile()">
                        <span class="material-symbols-outlined text-sm">save</span>
                        <span>Save Changes</span>
                    </button>
                </div>
            </div>

            <!-- Address (read-only) -->
            <div class="ds-card">
                <div class="px-5 py-3 border-b border-border-main">
                    <h3 class="text-[10px] font-black uppercase tracking-widest text-text-muted">Permanent / Residential Address</h3>
                </div>
                <table class="w-full text-xs">
                    <tbody>
                        <?php
                        $addrRows = [
                            ['view-street',  'Street / House No'],
                            ['view-city',    'City'],
                            ['view-state',   'State / Region'],
                            ['view-pincode', 'Postal Code'],
                            ['view-country', 'Country'],
                        ];
                        foreach ($addrRows as $i => [$id, $label]):
                            $bg = ($i % 2 === 0) ? '' : 'bg-surface-subtle/50';
                        ?>
                        <tr class="border-b border-border-main <?= $bg ?>">
                            <td class="px-5 py-3 text-text-muted font-semibold w-44 text-[11px] uppercase tracking-wide"><?= htmlspecialchars($label) ?></td>
                            <td class="px-5 py-3 font-semibold text-text-main" id="<?= htmlspecialchars($id) ?>">—</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ── Tab: Identity ── -->
        <div id="tab-identity" class="profile-tab-panel hidden ds-card">
            <div class="px-5 py-3 border-b border-border-main flex items-center justify-between">
                <h3 class="text-[10px] font-black uppercase tracking-widest text-text-muted">Government Identity Verification</h3>
                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 border border-emerald-500/30 px-2 py-0.5 rounded-full">
                    <span class="material-symbols-outlined text-sm">encrypted</span> AES-256
                </span>
            </div>
            <table class="w-full text-xs">
                <tbody>
                    <tr class="border-b border-border-main">
                        <td class="px-5 py-3 text-text-muted font-semibold w-44 text-[11px] uppercase tracking-wide">ID Document Type</td>
                        <td class="px-5 py-3 font-semibold text-text-main" id="view-id-type">—</td>
                    </tr>
                    <tr>
                        <td class="px-5 py-3 text-text-muted font-semibold w-44 text-[11px] uppercase tracking-wide">ID Number</td>
                        <td class="px-5 py-3 font-mono font-bold text-text-main">
                            <div class="flex items-center gap-3">
                                <span id="view-id-number">•••• •••• ——</span>
                                <button type="button" id="btn-toggle-id" onclick="toggleIdMask()" class="text-[10px] font-bold text-primary-base hover:underline">Show</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- ── Tab: Financial ── -->
        <div id="tab-financial" class="profile-tab-panel hidden ds-card">
            <div class="px-5 py-3 border-b border-border-main">
                <h3 class="text-[10px] font-black uppercase tracking-widest text-text-muted">Employment &amp; Financial Profile</h3>
            </div>
            <table class="w-full text-xs">
                <tbody>
                    <?php
                    $finRows = [
                        ['view-occupation',    'Occupation'],
                        ['view-source-income', 'Source of Income'],
                        ['view-annual-income', 'Annual Income'],
                    ];
                    foreach ($finRows as $i => [$id, $label]):
                        $bg = ($i % 2 === 0) ? '' : 'bg-surface-subtle/50';
                    ?>
                    <tr class="border-b border-border-main <?= $bg ?>">
                        <td class="px-5 py-3 text-text-muted font-semibold w-44 text-[11px] uppercase tracking-wide"><?= htmlspecialchars($label) ?></td>
                        <td class="px-5 py-3 font-semibold text-text-main capitalize" id="<?= htmlspecialchars($id) ?>">—</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- ── Tab: Security ── -->
        <div id="tab-security" class="profile-tab-panel hidden flex flex-col gap-4">

            <!-- Change Password -->
            <div class="ds-card p-5">
                <div class="text-[10px] font-black uppercase tracking-widest text-text-muted mb-4">Change Login Password</div>
                <form id="change-password-form" onsubmit="event.preventDefault(); updatePassword();" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label for="current-pass" class="text-[10px] font-bold text-text-muted uppercase tracking-wide block mb-1.5">Current Password</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-[17px] pointer-events-none">key</span>
                            <input type="password" id="current-pass" class="fin-input w-full pl-9 pr-10 text-sm" placeholder="Enter current password" required />
                            <button type="button" onclick="togglePassVisibility('current-pass','icon-toggle-current')" class="absolute right-3 top-1/2 -translate-y-1/2 text-text-muted hover:text-text-main">
                                <span class="material-symbols-outlined text-[17px]" id="icon-toggle-current">visibility</span>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label for="new-pass" class="text-[10px] font-bold text-text-muted uppercase tracking-wide block mb-1.5">New Password</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-[17px] pointer-events-none">lock</span>
                            <input type="password" id="new-pass" class="fin-input w-full pl-9 pr-10 text-sm" placeholder="Min. 8 characters" required minlength="8" />
                            <button type="button" onclick="togglePassVisibility('new-pass','icon-toggle-new')" class="absolute right-3 top-1/2 -translate-y-1/2 text-text-muted hover:text-text-main">
                                <span class="material-symbols-outlined text-[17px]" id="icon-toggle-new">visibility</span>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label for="confirm-pass" class="text-[10px] font-bold text-text-muted uppercase tracking-wide block mb-1.5">Confirm New Password</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-[17px] pointer-events-none">lock_reset</span>
                            <input type="password" id="confirm-pass" class="fin-input w-full pl-9 pr-10 text-sm" placeholder="Re-enter new password" required minlength="8" />
                            <button type="button" onclick="togglePassVisibility('confirm-pass','icon-toggle-confirm')" class="absolute right-3 top-1/2 -translate-y-1/2 text-text-muted hover:text-text-main">
                                <span class="material-symbols-outlined text-[17px]" id="icon-toggle-confirm">visibility</span>
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

            <!-- 2FA -->
            <div class="ds-card p-5">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-primary-surface text-primary-base flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-lg">shield_lock</span>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-text-main">Two-Factor Authentication</p>
                            <p class="text-[11px] text-text-muted">TOTP &amp; hardware key authentication</p>
                        </div>
                    </div>
                    <span class="badge badge-warning text-[10px] uppercase font-bold tracking-widest shrink-0">Coming Soon</span>
                </div>
                <div class="flex items-center justify-between gap-3 p-3 rounded-lg bg-surface-subtle border border-border-main text-xs">
                    <div class="flex items-center gap-2 text-text-muted">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse shrink-0"></span>
                        <span>Authenticator app integration is under security audit.</span>
                    </div>
                    <button type="button" class="btn btn-secondary text-xs opacity-50 cursor-not-allowed shrink-0" disabled>Configure</button>
                </div>
            </div>

        </div>

    </div>
</div>

<style>
.profile-tab {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 10px 16px; font-size: 12px; font-weight: 600;
    color: var(--tw-text-opacity, #6b7280);
    border-bottom: 2px solid transparent;
    white-space: nowrap; cursor: pointer;
    background: transparent;
    border-top: 0; border-left: 0; border-right: 0;
    transition: color .15s, border-color .15s;
}
.profile-tab:hover { color: var(--color-text-main, #111); }
.profile-tab.active { color: var(--color-primary-600, #2563eb); border-bottom-color: var(--color-primary-600, #2563eb); font-weight: 700; }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>

<script>
function switchTab(name) {
    document.querySelectorAll('.profile-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.profile-tab-panel').forEach(p => p.classList.add('hidden'));
    document.querySelector('[data-tab="' + name + '"]').classList.add('active');
    const panel = document.getElementById('tab-' + name);
    if (panel) panel.classList.remove('hidden');
}
</script>

<script defer src="/Finzo/public/assets/js/user/profile.js"></script>

<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
