<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin — Executive Profile & Governance';
// Admin portal guard — server-side authentication & role check
include __DIR__ . '/_guard.php';
if (!defined('FINZO_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}
\App\Shared\Security\SessionManager::start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
ob_start();
?>

<!-- ========================================== -->
<!-- 1. EXECUTIVE DOSSIER HERO CARD             -->
<!-- ========================================== -->
<div class="ds-card p-6 sm:p-8 mb-6 relative overflow-hidden bg-surface border border-border-subtle rounded-2xl shadow-sm">
    <div class="relative z-10 flex flex-col md:flex-row items-center md:items-start justify-between gap-6 sm:gap-8">
        
        <!-- Left: Avatar & Official Identity -->
        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6 text-center sm:text-left">
            <!-- Official Photo Box with Change Trigger -->
            <div class="relative group">
                <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl bg-surface-subtle border border-border-subtle flex items-center justify-center text-2xl sm:text-3xl font-black text-text-primary shadow-sm overflow-hidden relative" id="avatar-preview-container">
                    <span id="avatar-initials" class="uppercase select-none tracking-wider">--</span>
                    <img loading="lazy" id="avatar-img-preview" src="" class="hidden w-full h-full object-cover" alt="Profile avatar">
                </div>

                <!-- Verified Security Dot -->
                <span class="absolute -top-1 -right-1 flex h-3.5 w-3.5" title="Active Administrative Session">
                    <span class="inline-flex rounded-full h-3.5 w-3.5 bg-emerald-500 border-2 border-surface"></span>
                </span>

                <!-- Photo Upload Trigger -->
                <label for="photo-upload" class="absolute -bottom-1.5 -right-1.5 w-8 h-8 bg-zinc-900 text-white hover:bg-black dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100 rounded-xl flex items-center justify-center cursor-pointer shadow-md transition-transform hover:scale-105 active:scale-95 border-2 border-surface" title="Update Official Photo">
                    <span class="material-symbols-outlined text-[15px]" aria-hidden="true">photo_camera</span>
                </label>
                <input type="file" id="photo-upload" accept="image/jpeg, image/png" class="hidden" onchange="window.AdminProfile.uploadPhoto(this)">
            </div>

            <!-- Identity, Authority Level & Badges -->
            <div class="space-y-2.5">
                <div>
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2.5">
                        <h1 class="text-xl sm:text-2xl font-headline font-bold text-text-primary tracking-tight" id="profile-name-display">Loading Administrative Profile...</h1>
                        <span id="badge-role" class="px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900 shadow-xs">—</span>
                    </div>
                    <p class="text-xs text-text-secondary font-medium mt-0.5">Finzo Central Banking Group &bull; Corporate Governance Division</p>
                </div>

                <!-- Admin ID & Corporate Email Pills -->
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2.5 text-xs">
                    <button type="button" onclick="window.AdminProfile.copyText(document.getElementById('profile-admin-id-input').value, 'Admin ID')" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-surface-subtle hover:bg-surface-hover border border-border-subtle font-mono text-[11px] text-text-primary transition-colors group cursor-pointer" title="Copy Administrator ID">
                        <span class="material-symbols-outlined text-[14px] text-text-secondary" aria-hidden="true">badge</span>
                        <span id="hero-admin-id-text">ID: ---</span>
                        <span class="material-symbols-outlined text-[12px] opacity-40 group-hover:opacity-100" aria-hidden="true">content_copy</span>
                    </button>

                    <button type="button" onclick="window.AdminProfile.copyText(document.getElementById('profile-email-input').value, 'Email')" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-surface-subtle hover:bg-surface-hover border border-border-subtle text-[11px] text-text-secondary hover:text-text-primary transition-colors group cursor-pointer" title="Copy Corporate Email">
                        <span class="material-symbols-outlined text-[14px] text-text-secondary" aria-hidden="true">mail</span>
                        <span id="hero-email-text">admin@finzo.com</span>
                        <span class="material-symbols-outlined text-[12px] opacity-40 group-hover:opacity-100" aria-hidden="true">content_copy</span>
                    </button>
                </div>

                <!-- Institutional Status Badges -->
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 pt-0.5">
                    <span id="badge-status" class="px-2.5 py-0.5 rounded-md bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-[10px] font-bold uppercase tracking-wider flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Active
                    </span>
                    <span id="badge-access" class="px-2.5 py-0.5 rounded-md bg-surface-subtle text-text-primary border border-border-subtle text-[10px] font-bold uppercase tracking-wider">
                        Full Access
                    </span>
                    <span id="badge-sq-status" class="px-2.5 py-0.5 rounded-md bg-surface-subtle text-text-secondary border border-border-subtle text-[10px] font-bold uppercase tracking-wider flex items-center gap-1">
                        <span class="material-symbols-outlined text-[13px]" aria-hidden="true">shield</span>
                        <span id="badge-sq-label">Recovery: Checking...</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Right: Real Institutional Telemetry & Session Metrics -->
        <div class="w-full md:w-auto shrink-0 flex flex-wrap sm:flex-nowrap gap-3">
            <!-- Security Compliance Status -->
            <div class="flex-1 sm:flex-initial p-3.5 sm:p-4 rounded-xl bg-surface-subtle border border-border-subtle text-left min-w-[140px]">
                <span class="text-[10px] font-bold text-text-secondary uppercase tracking-wider block mb-1">Security Standard</span>
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-emerald-600 dark:text-emerald-400 text-lg" aria-hidden="true">verified_user</span>
                    <span class="text-sm sm:text-base font-headline font-bold text-text-primary" id="hero-security-score">100% Verified</span>
                </div>
                <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold block mt-0.5">Policy Compliant</span>
            </div>

            <!-- Session Encryption State -->
            <div class="flex-1 sm:flex-initial p-3.5 sm:p-4 rounded-xl bg-surface-subtle border border-border-subtle text-left min-w-[140px]">
                <span class="text-[10px] font-bold text-text-secondary uppercase tracking-wider block mb-1">Session Security</span>
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-text-primary text-lg" aria-hidden="true">lock</span>
                    <span class="text-xs font-mono font-bold text-text-primary truncate" id="hero-session-status">TLS 1.3 / AES-256</span>
                </div>
                <span class="text-[10px] text-text-secondary font-medium block mt-0.5">Hardware SSL Channel</span>
            </div>
        </div>

    </div>
</div>

<!-- ========================================== -->
<!-- 2. SECTION TABS NAVIGATION                 -->
<!-- ========================================== -->
<div class="flex items-center gap-2 mb-6 border-b border-border-subtle pb-px overflow-x-auto custom-scrollbar" role="tablist">
    <button type="button" role="tab" aria-selected="true" data-tab="tab-overview" onclick="window.AdminProfile.switchTab('tab-overview')" class="profile-tab-btn flex items-center gap-2 px-5 py-3 rounded-t-xl text-xs font-bold uppercase tracking-wider transition-all border-b-2 border-black dark:border-white text-black dark:text-white bg-surface shadow-xs cursor-pointer">
        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">account_box</span>
        <span>Personnel Profile</span>
    </button>

    <button type="button" role="tab" aria-selected="false" data-tab="tab-security" onclick="window.AdminProfile.switchTab('tab-security')" class="profile-tab-btn flex items-center gap-2 px-5 py-3 rounded-t-xl text-xs font-bold uppercase tracking-wider transition-all border-b-2 border-transparent text-text-secondary hover:text-text-primary hover:bg-surface-hover cursor-pointer">
        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">security</span>
        <span>Access &amp; Security</span>
    </button>

    <button type="button" role="tab" aria-selected="false" data-tab="tab-activity" onclick="window.AdminProfile.switchTab('tab-activity')" class="profile-tab-btn flex items-center gap-2 px-5 py-3 rounded-t-xl text-xs font-bold uppercase tracking-wider transition-all border-b-2 border-transparent text-text-secondary hover:text-text-primary hover:bg-surface-hover cursor-pointer">
        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">receipt_long</span>
        <span>Audit Ledger</span>
    </button>

    <button type="button" role="tab" aria-selected="false" id="tab-btn-directory" data-tab="tab-directory" onclick="window.AdminProfile.switchTab('tab-directory')" class="profile-tab-btn hidden items-center gap-2 px-5 py-3 rounded-t-xl text-xs font-bold uppercase tracking-wider transition-all border-b-2 border-transparent text-text-secondary hover:text-text-primary hover:bg-surface-hover cursor-pointer">
        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">admin_panel_settings</span>
        <span>RBAC Directory</span>
    </button>
</div>

<!-- ========================================== -->
<!-- 3. TAB PANELS CONTAINER                    -->
<!-- ========================================== -->

<!-- TAB 1: PERSONNEL PROFILE -->
<div id="tab-overview" class="tab-panel space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Main Form: Official Details (2 cols) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="ds-card p-6 sm:p-8 bg-surface border border-border-subtle rounded-2xl shadow-sm">
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-border-subtle">
                    <div>
                        <h3 class="font-headline font-bold text-base text-text-primary flex items-center gap-2">
                            <span class="material-symbols-outlined text-text-secondary" aria-hidden="true">badge</span>
                            Official Identity Details
                        </h3>
                        <p class="text-xs text-text-secondary mt-0.5">Verified personnel information recorded across corporate banking audit logs.</p>
                    </div>
                    <span class="px-2.5 py-1 bg-surface-subtle text-text-secondary text-[10px] font-mono font-bold rounded-lg border border-border-subtle" id="meta-role-label">System Role</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
                    <div>
                        <label for="profile-admin-id-input" class="fin-label mb-1.5 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">System Identifier</label>
                        <div class="fin-input-wrapper">
                            <span class="material-symbols-outlined input-icon-left text-text-secondary">fingerprint</span>
                            <input type="text" id="profile-admin-id-input" class="fin-input has-icon-left opacity-75 bg-surface-subtle cursor-not-allowed font-mono text-text-primary" readonly>
                        </div>
                    </div>

                    <div>
                        <label for="profile-name-input" class="fin-label mb-1.5 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Full Legal Name *</label>
                        <div class="fin-input-wrapper">
                            <span class="material-symbols-outlined input-icon-left text-text-secondary">person</span>
                            <input type="text" id="profile-name-input" class="fin-input has-icon-left bg-surface text-text-primary border-border-subtle" placeholder="Full Name as per official ID">
                        </div>
                    </div>

                    <div>
                        <label for="profile-dob-input" class="fin-label mb-1.5 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Date of Birth</label>
                        <div class="fin-input-wrapper">
                            <span class="material-symbols-outlined input-icon-left text-text-secondary">calendar_today</span>
                            <input type="date" id="profile-dob-input" class="fin-input has-icon-left bg-surface text-text-primary border-border-subtle">
                        </div>
                    </div>

                    <div>
                        <label class="fin-label mb-1.5 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Gender</label>
                        <!-- Standard Finzo Dropdown Component -->
                        <div class="finzo-dropdown" id="dropdown-gender">
                            <input type="hidden" id="profile-gender-input" value="">
                            <button type="button" class="finzo-dropdown-trigger bg-surface text-text-primary border border-border-subtle rounded-xl px-4 py-2.5 flex items-center justify-between w-full text-xs font-semibold hover:border-text-primary transition-colors focus:ring-2 focus:ring-text-primary focus:outline-none" aria-haspopup="listbox" aria-expanded="false">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-text-secondary text-[16px]" aria-hidden="true">wc</span>
                                    <span class="dropdown-label text-text-primary">Select Gender</span>
                                </div>
                                <span class="material-symbols-outlined text-[18px] chevron transition-transform text-text-secondary" aria-hidden="true">expand_more</span>
                            </button>
                            <div class="finzo-dropdown-menu bg-surface dark:bg-[#18181B] border border-border-subtle rounded-xl shadow-xl py-1.5 z-50" role="listbox">
                                <div class="finzo-dropdown-option px-4 py-2 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer transition-colors" data-value="Male">Male</div>
                                <div class="finzo-dropdown-option px-4 py-2 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer transition-colors" data-value="Female">Female</div>
                                <div class="finzo-dropdown-option px-4 py-2 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer transition-colors" data-value="Other">Other</div>
                                <div class="finzo-dropdown-option px-4 py-2 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer transition-colors" data-value="Prefer not to say">Prefer not to say</div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="profile-phone-input" class="fin-label mb-1.5 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Contact Number</label>
                        <div class="fin-input-wrapper">
                            <span class="material-symbols-outlined input-icon-left text-text-secondary">call</span>
                            <input type="text" id="profile-phone-input" class="fin-input has-icon-left bg-surface text-text-primary border-border-subtle" placeholder="10-digit mobile number">
                        </div>
                    </div>

                    <div>
                        <label for="profile-email-input" class="fin-label mb-1.5 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Corporate Email (Immutable)</label>
                        <div class="fin-input-wrapper">
                            <span class="material-symbols-outlined input-icon-left text-text-secondary">mail</span>
                            <input type="email" id="profile-email-input" class="fin-input has-icon-left opacity-75 bg-surface-subtle cursor-not-allowed text-text-primary" readonly>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-5 border-t border-border-subtle flex flex-col sm:flex-row items-center justify-between gap-4">
                    <span class="text-xs text-text-secondary flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px] text-emerald-600 dark:text-emerald-400" aria-hidden="true">check_circle</span>
                        Modifications are committed directly to core database
                    </span>
                    <!-- Save Profile Button -->
                    <button type="button" onclick="window.AdminProfile.saveProfile()" class="w-full sm:w-auto px-6 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider inline-flex items-center justify-center gap-2 bg-zinc-900 text-white hover:bg-black dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100 shadow-sm transition-all cursor-pointer">
                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">save</span>
                        <span>Save Profile</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Right: Session Telemetry & Governance Overview (1 col) -->
        <div class="space-y-6">
            <!-- Institutional Security Overview Card -->
            <div class="ds-card p-6 bg-surface border border-border-subtle rounded-2xl shadow-sm space-y-4">
                <div class="flex items-center gap-3 pb-3.5 border-b border-border-subtle">
                    <div class="w-9 h-9 rounded-xl bg-surface-subtle text-text-primary flex items-center justify-center border border-border-subtle">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">shield</span>
                    </div>
                    <div>
                        <h3 class="font-headline font-bold text-sm text-text-primary">Access &amp; Audit Metadata</h3>
                        <p class="text-[10px] font-semibold text-text-secondary uppercase tracking-wider">Session Security Parameters</p>
                    </div>
                </div>

                <div class="space-y-3 text-xs">
                    <div class="flex justify-between items-center py-1.5 border-b border-border-subtle">
                        <span class="text-[10px] font-bold text-text-secondary uppercase tracking-wider">Last Authenticated</span>
                        <span class="font-semibold text-text-primary" id="sec-last-login">—</span>
                    </div>
                    <div class="flex justify-between items-center py-1.5 border-b border-border-subtle">
                        <span class="text-[10px] font-bold text-text-secondary uppercase tracking-wider">Client IP Address</span>
                        <span class="font-mono font-bold text-text-primary" id="sec-last-ip">—</span>
                    </div>
                    <div class="flex justify-between items-center py-1.5 border-b border-border-subtle">
                        <span class="text-[10px] font-bold text-text-secondary uppercase tracking-wider">Password Rotated</span>
                        <span class="font-semibold text-text-primary" id="sec-last-pwd">—</span>
                    </div>
                    <div class="flex justify-between items-center py-1.5">
                        <span class="text-[10px] font-bold text-text-secondary uppercase tracking-wider">Authorization Scope</span>
                        <span class="font-bold text-text-primary" id="sec-role-scope">Super Administrator</span>
                    </div>
                </div>
            </div>

            <!-- Two-Factor Authentication Policy Card -->
            <div class="ds-card p-6 bg-surface-subtle border border-border-subtle rounded-2xl shadow-xs space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-text-primary uppercase tracking-wider">Two-Factor Authentication</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="sec-2fa-toggle" class="sr-only peer" onchange="window.AdminProfile.toggle2FA(this.checked)">
                        <div class="w-10 h-5 bg-zinc-300 dark:bg-zinc-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white dark:after:bg-zinc-900 after:border-border-subtle after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-zinc-900 dark:peer-checked:bg-white dark:peer-checked:after:bg-zinc-900"></div>
                    </label>
                </div>
                <p class="text-xs text-text-secondary leading-relaxed">Require secondary cryptographic verification for sensitive core banking transactions and administrative overrides.</p>
                <div class="pt-1">
                    <span class="text-[10px] font-mono font-bold uppercase tracking-wider px-2.5 py-1 rounded bg-surface border border-border-subtle text-text-secondary" id="sec-2fa-label">DISABLED</span>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- TAB 2: ACCESS & SECURITY -->
<div id="tab-security" class="tab-panel hidden space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Rotate Password Card -->
        <div class="ds-card p-6 sm:p-8 bg-surface border border-border-subtle rounded-2xl shadow-sm space-y-5">
            <div class="flex items-center gap-3 pb-3.5 border-b border-border-subtle">
                <div class="w-9 h-9 rounded-xl bg-surface-subtle text-text-primary flex items-center justify-center border border-border-subtle">
                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">lock_reset</span>
                </div>
                <div>
                    <h3 class="font-headline font-bold text-base text-text-primary">Credential Rotation</h3>
                    <p class="text-xs text-text-secondary mt-0.5">Enforce enterprise password compliance</p>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label for="curr-pw" class="fin-label mb-1.5 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Current Password *</label>
                    <div class="fin-input-wrapper relative">
                        <span class="material-symbols-outlined input-icon-left text-text-secondary">lock</span>
                        <input type="password" id="curr-pw" class="fin-input has-icon-left pr-10 bg-surface text-text-primary border-border-subtle" placeholder="Enter current administrative password">
                        <button type="button" onclick="window.AdminProfile.togglePW('curr-pw')" class="absolute right-3 text-text-secondary hover:text-text-primary focus:outline-none cursor-pointer" title="Toggle Visibility">
                            <span class="material-symbols-outlined text-[18px]">visibility</span>
                        </button>
                    </div>
                </div>

                <div>
                    <label for="new-pw" class="fin-label mb-1.5 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">New Password *</label>
                    <div class="fin-input-wrapper relative">
                        <span class="material-symbols-outlined input-icon-left text-text-secondary">key</span>
                        <input type="password" id="new-pw" class="fin-input has-icon-left pr-10 bg-surface text-text-primary border-border-subtle" placeholder="Minimum 8 characters (Uppercase, number, symbol)" oninput="window.AdminProfile.checkStrength(this.value)">
                        <button type="button" onclick="window.AdminProfile.togglePW('new-pw')" class="absolute right-3 text-text-secondary hover:text-text-primary focus:outline-none cursor-pointer" title="Toggle Visibility">
                            <span class="material-symbols-outlined text-[18px]">visibility</span>
                        </button>
                    </div>
                    <!-- Password strength bar -->
                    <div class="mt-2">
                        <div class="password-strength-bar w-full h-1 bg-surface-subtle rounded-full overflow-hidden">
                            <div class="password-strength-fill h-full transition-all duration-300 rounded-full" id="pw-bar" style="width:0%; background:var(--color-primary-base);"></div>
                        </div>
                        <div class="flex justify-between items-center mt-1">
                            <span class="text-[10px] text-text-secondary">Enterprise Compliance Meter</span>
                            <span class="text-[10px] font-bold uppercase tracking-wider" id="pw-text"></span>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="conf-pw" class="fin-label mb-1.5 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Confirm New Password *</label>
                    <div class="fin-input-wrapper relative">
                        <span class="material-symbols-outlined input-icon-left text-text-secondary">check_circle</span>
                        <input type="password" id="conf-pw" class="fin-input has-icon-left pr-10 bg-surface text-text-primary border-border-subtle" placeholder="Confirm new password">
                        <button type="button" onclick="window.AdminProfile.togglePW('conf-pw')" class="absolute right-3 text-text-secondary hover:text-text-primary focus:outline-none cursor-pointer" title="Toggle Visibility">
                            <span class="material-symbols-outlined text-[18px]">visibility</span>
                        </button>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="button" onclick="window.AdminProfile.updatePassword()" class="w-full py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider bg-zinc-900 text-white hover:bg-black dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100 shadow-sm transition-all cursor-pointer">
                        Update Credential
                    </button>
                </div>
            </div>
        </div>

        <!-- Security Recovery Question Card -->
        <div class="ds-card p-6 sm:p-8 bg-surface border border-border-subtle rounded-2xl shadow-sm space-y-5">
            <div class="flex items-center justify-between pb-3.5 border-b border-border-subtle">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-surface-subtle text-text-primary flex items-center justify-center border border-border-subtle">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">quiz</span>
                    </div>
                    <div>
                        <h3 class="font-headline font-bold text-base text-text-primary">Recovery Security Question</h3>
                        <p class="text-xs text-text-secondary mt-0.5">Cryptographic identity challenge verification</p>
                    </div>
                </div>
                <div id="sq-status-container"></div>
            </div>

            <p class="text-xs text-text-secondary leading-relaxed">Configure an internal recovery question to restore administrative access if account lockouts occur due to repeated authentication failures.</p>

            <form id="sq-setup-form" onsubmit="window.AdminProfile.saveSecurityQuestion(event)" class="space-y-4">
                <div>
                    <label for="sq-question" class="fin-label mb-1.5 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Secret Question *</label>
                    <input type="text" id="sq-question" class="fin-input bg-surface text-text-primary border-border-subtle" placeholder="e.g. What was the name of your first elementary school?" minlength="10" required>
                </div>
                <div>
                    <label for="sq-answer" class="fin-label mb-1.5 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Secret Answer *</label>
                    <input type="password" id="sq-answer" class="fin-input bg-surface text-text-primary border-border-subtle" placeholder="Confidential recovery answer" minlength="3" required>
                </div>
                <div class="pt-2 border-t border-border-subtle">
                    <label for="sq-curr-pw" class="fin-label mb-1.5 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Current Password (Authentication Verification) *</label>
                    <input type="password" id="sq-curr-pw" class="fin-input bg-surface text-text-primary border-border-subtle" placeholder="Verify administrative identity" required>
                </div>
                <div class="pt-2 flex justify-end">
                    <button type="submit" id="btn-save-sq" class="px-6 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider bg-zinc-900 text-white hover:bg-black dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100 shadow-sm transition-all cursor-pointer">
                        <span id="sq-btn-text">Save Security Question</span>
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

<!-- TAB 3: AUDIT LEDGER -->
<div id="tab-activity" class="tab-panel hidden space-y-6">
    <div class="ds-card bg-surface border border-border-subtle rounded-2xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-border-subtle flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-surface-subtle">
            <div>
                <h3 class="font-headline font-bold text-base text-text-primary flex items-center gap-2">
                    <span class="material-symbols-outlined text-text-secondary" aria-hidden="true">receipt_long</span>
                    Administrative Audit Ledger
                </h3>
                <p class="text-xs text-text-secondary mt-0.5">Chronological, tamper-evident record of administrative operations and session events.</p>
            </div>
            <div class="flex items-center gap-3">
                <span id="activity-count-badge" class="px-3 py-1 bg-surface text-text-secondary text-[10px] font-mono font-bold rounded-lg border border-border-subtle">0 Records</span>
            </div>
        </div>

        <!-- Activity Date & Search Filter Toolbar -->
        <div class="p-4 sm:p-6 border-b border-border-subtle bg-surface flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3 flex-1">
                <div class="fin-input-wrapper flex-1 min-w-[200px]">
                    <span class="material-symbols-outlined input-icon-left text-text-secondary">search</span>
                    <input type="text" id="activity-search-input" placeholder="Search audit logs by action, IP, or details..." class="fin-input has-icon-left bg-surface text-text-primary border-border-subtle rounded-xl text-xs">
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-bold text-text-secondary uppercase tracking-wider">From:</span>
                    <input type="date" id="activity-from-date" class="fin-input !h-9 bg-surface text-text-primary border-border-subtle rounded-xl text-xs px-3">
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-bold text-text-secondary uppercase tracking-wider">To:</span>
                    <input type="date" id="activity-to-date" class="fin-input !h-9 bg-surface text-text-primary border-border-subtle rounded-xl text-xs px-3">
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2 shrink-0">
                <button type="button" onclick="window.AdminProfile.resetActivityFilter()" class="px-3 py-2 rounded-xl text-xs font-semibold bg-surface text-text-primary hover:bg-surface-hover border border-border-subtle shadow-xs cursor-pointer transition-colors" title="Reset Filters">
                    <span class="material-symbols-outlined text-[16px]" aria-hidden="true">restart_alt</span>
                </button>
                <button type="button" onclick="window.AdminProfile.exportActivityCSV()" class="px-4 py-2 text-xs rounded-xl inline-flex items-center gap-1.5 font-bold uppercase tracking-wider bg-surface text-text-primary hover:bg-surface-hover border border-border-subtle shadow-xs cursor-pointer transition-colors">
                    <span class="material-symbols-outlined text-[16px]" aria-hidden="true">download</span>
                    <span>Export CSV</span>
                </button>
            </div>
        </div>

        <div class="ds-table-wrapper w-full border-0 shadow-none rounded-none bg-transparent overflow-x-auto">
            <table class="ds-table w-full text-left min-w-[700px]">
                <thead class="bg-surface-subtle border-b border-border-subtle">
                    <tr>
                        <th class="py-3 px-6 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Event / Operation</th>
                        <th class="py-3 px-6 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Context &amp; Parameters</th>
                        <th class="py-3 px-6 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Originating IP</th>
                        <th class="py-3 px-6 text-right text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Timestamp</th>
                    </tr>
                </thead>
                <tbody id="activity-table-body" class="divide-y divide-border-subtle">
                    <tr><td colspan="4" class="text-center italic opacity-50 py-12 text-sm">Loading audit records...</td></tr>
                </tbody>
            </table>
        </div>

        <div class="p-4 sm:p-6 border-t border-border-subtle flex flex-col sm:flex-row items-center justify-between gap-4 bg-surface-subtle" id="activity-pagination-container">
            <div class="text-[11px] font-bold text-text-secondary uppercase tracking-wider" id="activity-pagination-info">Showing 0 to 0 of 0</div>
            <div class="flex gap-2">
                <button id="activity-prev-btn" onclick="window.AdminProfile.prevActivityPage()" class="px-3.5 py-1.5 text-xs rounded-xl font-bold bg-surface text-text-primary hover:bg-surface-hover border border-border-subtle shadow-xs cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed transition-colors">Previous</button>
                <button id="activity-next-btn" onclick="window.AdminProfile.nextActivityPage()" class="px-3.5 py-1.5 text-xs rounded-xl font-bold bg-surface text-text-primary hover:bg-surface-hover border border-border-subtle shadow-xs cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed transition-colors">Next</button>
            </div>
        </div>
    </div>
</div>

<!-- TAB 4: RBAC DIRECTORY & ACCESS GOVERNANCE (Super Admin) -->
<div id="tab-directory" class="tab-panel hidden space-y-6">
    
    <!-- LOCKED ACCOUNTS OVERSIGHT -->
    <div id="locked-admins-container" class="ds-card bg-surface border border-danger-border rounded-2xl shadow-sm overflow-hidden hidden">
        <div class="p-5 border-b border-danger-border flex justify-between items-center bg-danger-bg">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-danger/20 text-danger flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">lock_clock</span>
                </div>
                <div>
                    <h3 class="font-headline font-bold text-sm text-danger">Locked Administrative Accounts</h3>
                    <p class="text-[11px] text-danger/80 mt-0.5">Accounts locked due to repeated failed authentication attempts.</p>
                </div>
            </div>
            <span id="locked-count-badge" class="px-2.5 py-1 bg-danger/20 text-danger text-[10px] font-bold rounded-md uppercase tracking-wider border border-danger/30">0 Locked</span>
        </div>
        <div class="ds-table-wrapper w-full border-0 shadow-none rounded-none bg-transparent overflow-x-auto">
            <table class="ds-table w-full text-left min-w-[800px]">
                <thead class="bg-surface-subtle border-b border-border-subtle">
                    <tr>
                        <th class="py-3 px-6 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Administrator</th>
                        <th class="py-3 px-4 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Role</th>
                        <th class="py-3 px-4 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Lock Timestamp</th>
                        <th class="py-3 px-4 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Security Trigger</th>
                        <th class="py-3 px-4 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Failed Challenges</th>
                        <th class="py-3 px-4 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Recovery Setup</th>
                        <th class="py-3 px-4 text-right text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Action</th>
                    </tr>
                </thead>
                <tbody id="locked-admins-body" class="divide-y divide-border-subtle"></tbody>
            </table>
        </div>
    </div>

    <!-- GLOBAL DIRECTORY CARD -->
    <div id="admin-management-container" class="ds-card bg-surface border border-border-subtle rounded-2xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-border-subtle flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-surface-subtle">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-surface text-text-primary flex items-center justify-center border border-border-subtle shadow-xs">
                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">admin_panel_settings</span>
                </div>
                <div>
                    <h3 class="font-headline font-bold text-base text-text-primary">Administrative Directory &amp; RBAC Control</h3>
                    <p class="text-xs text-text-secondary mt-0.5">Manage administrative credentials, security roles, and system authorities.</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span id="management-count-badge" class="px-3 py-1 bg-surface text-text-secondary text-[10px] font-mono font-bold rounded-lg border border-border-subtle">0 Admins</span>
                <!-- Create Admin Button -->
                <button id="header-add-admin-btn" type="button" onclick="window.AdminProfile.showAddModal()" class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider inline-flex items-center gap-1.5 bg-zinc-900 text-white hover:bg-black dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100 shadow-sm transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-[16px]" aria-hidden="true">person_add</span>
                    <span>Add Administrator</span>
                </button>
            </div>
        </div>

        <!-- Filter controls -->
        <div class="p-4 sm:p-6 border-b border-border-subtle bg-surface flex flex-col sm:flex-row gap-4">
            <div class="fin-input-wrapper flex-1">
                <span class="material-symbols-outlined input-icon-left text-text-secondary">search</span>
                <input type="text" id="admin-search-input" placeholder="Search by name, corporate email, or ID..." class="fin-input has-icon-left bg-surface text-text-primary border-border-subtle rounded-xl text-xs">
            </div>
            
            <div class="flex flex-wrap sm:flex-nowrap gap-3">
                <!-- Status Filter Dropdown -->
                <div class="finzo-dropdown min-w-[150px]" id="dropdown-admin-status-filter">
                    <button type="button" class="finzo-dropdown-trigger bg-surface text-text-primary border border-border-subtle rounded-xl px-4 py-2 flex items-center justify-between w-full text-xs font-semibold hover:border-text-primary transition-colors focus:ring-2 focus:ring-text-primary focus:outline-none cursor-pointer" aria-haspopup="listbox" aria-expanded="false">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[15px] text-text-secondary" aria-hidden="true">toggle_on</span>
                            <span class="dropdown-label text-text-primary font-bold">All Statuses</span>
                        </div>
                        <span class="material-symbols-outlined text-[18px] chevron transition-transform text-text-secondary" aria-hidden="true">expand_more</span>
                    </button>
                    <div class="finzo-dropdown-menu bg-surface dark:bg-[#18181B] border border-border-subtle rounded-xl shadow-xl py-1.5 z-50 min-w-[150px]" role="listbox">
                        <div class="finzo-dropdown-option is-selected px-4 py-2 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer transition-colors" data-value="all">All Statuses</div>
                        <div class="finzo-dropdown-option px-4 py-2 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer transition-colors" data-value="active">Active</div>
                        <div class="finzo-dropdown-option px-4 py-2 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer transition-colors" data-value="suspended">Suspended</div>
                    </div>
                    <input type="hidden" id="admin-status-filter" value="all">
                </div>

                <!-- Role Filter Dropdown -->
                <div class="finzo-dropdown min-w-[150px]" id="dropdown-admin-role-filter">
                    <button type="button" class="finzo-dropdown-trigger bg-surface text-text-primary border border-border-subtle rounded-xl px-4 py-2 flex items-center justify-between w-full text-xs font-semibold hover:border-text-primary transition-colors focus:ring-2 focus:ring-text-primary focus:outline-none cursor-pointer" aria-haspopup="listbox" aria-expanded="false">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[15px] text-text-secondary" aria-hidden="true">shield_person</span>
                            <span class="dropdown-label text-text-primary font-bold">All Roles</span>
                        </div>
                        <span class="material-symbols-outlined text-[18px] chevron transition-transform text-text-secondary" aria-hidden="true">expand_more</span>
                    </button>
                    <div class="finzo-dropdown-menu bg-surface dark:bg-[#18181B] border border-border-subtle rounded-xl shadow-xl py-1.5 z-50 min-w-[150px]" role="listbox">
                        <div class="finzo-dropdown-option is-selected px-4 py-2 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer transition-colors" data-value="all">All Roles</div>
                        <div class="finzo-dropdown-option px-4 py-2 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer transition-colors" data-value="admin">Admin</div>
                        <div class="finzo-dropdown-option px-4 py-2 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer transition-colors" data-value="moderator">Moderator</div>
                    </div>
                    <input type="hidden" id="admin-role-filter" value="all">
                </div>
            </div>
        </div>

        <!-- Directory Table -->
        <div class="ds-table-wrapper w-full border-0 shadow-none rounded-none bg-transparent overflow-x-auto">
            <table class="ds-table w-full text-left min-w-[900px]">
                <thead class="bg-surface-subtle border-b border-border-subtle">
                    <tr>
                        <th class="py-3 px-6 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">ID</th>
                        <th class="py-3 px-6 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Administrator Details</th>
                        <th class="py-3 px-6 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Role</th>
                        <th class="py-3 px-6 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Access Scope</th>
                        <th class="py-3 px-6 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Status</th>
                        <th class="py-3 px-6 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Last Active</th>
                        <th class="py-3 px-6 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Provisioned By</th>
                        <th class="py-3 px-6 text-right text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody id="admin-management-body" class="divide-y divide-border-subtle"></tbody>
            </table>
        </div>
    </div>

</div>

<!-- ========================================== -->
<!-- 4. MODALS & DIALOGS                        -->
<!-- ========================================== -->

<!-- CREATE ADMINISTRATOR MODAL (Super Admin) -->
<div id="add-admin-modal" class="fixed inset-0 z-[2000] hidden items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-xs transition-all" onclick="window.AdminProfile.closeAddModal()"></div>
    
    <div class="relative w-full max-w-2xl bg-surface text-text-primary rounded-2xl overflow-hidden shadow-2xl flex flex-col max-h-[90vh] border border-border-subtle animate-modal-enter z-10">
        
        <!-- Modal Header -->
        <div class="p-5 sm:p-6 border-b border-border-subtle flex justify-between items-center sticky top-0 bg-surface z-10 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-surface-subtle text-text-primary flex items-center justify-center border border-border-subtle">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">person_add</span>
                </div>
                <div>
                    <h3 class="font-headline font-bold text-lg text-text-primary tracking-tight">Create Administrator Account</h3>
                    <p class="text-xs text-text-secondary mt-0.5">Assign institutional credentials and access permissions</p>
                </div>
            </div>
            <button type="button" onclick="window.AdminProfile.closeAddModal()" class="p-1.5 hover:bg-surface-hover rounded-lg transition-colors text-text-secondary hover:text-text-primary cursor-pointer">
                <span class="material-symbols-outlined" aria-hidden="true">close</span>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6 sm:p-8 overflow-y-auto flex-1 space-y-6 custom-scrollbar">
            
            <!-- SECTION 1: Personal & Contact Details -->
            <div>
                <div class="text-xs font-bold tracking-wider text-text-primary uppercase mb-3.5 py-1 border-b border-border-subtle flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm" aria-hidden="true">badge</span>
                    1. Official Identity &amp; Contact Information
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="add-admin-id" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Assigned ID</label>
                        <input type="text" id="add-admin-id" class="fin-input opacity-70 bg-surface-subtle font-mono text-text-primary text-xs" readonly>
                    </div>
                    <div>
                        <label for="add-name" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Full Legal Name *</label>
                        <input type="text" id="add-name" class="fin-input bg-surface text-text-primary text-xs" placeholder="e.g. Alexander Vance">
                        <span id="err-add-name" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                    </div>
                    <div>
                        <label for="add-dob" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Date of Birth *</label>
                        <input type="date" id="add-dob" class="fin-input bg-surface text-text-primary text-xs">
                        <span id="err-add-dob" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                    </div>
                    <div>
                        <label class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Gender *</label>
                        <div class="finzo-dropdown" id="dropdown-add-gender">
                            <input type="hidden" id="add-gender" value="">
                            <button type="button" class="finzo-dropdown-trigger bg-surface text-text-primary border border-border-subtle rounded-xl px-4 py-2.5 flex items-center justify-between w-full text-xs font-semibold hover:border-text-primary transition-colors focus:ring-2 focus:ring-text-primary focus:outline-none" aria-haspopup="listbox" aria-expanded="false">
                                <span class="dropdown-label text-text-primary">Select Gender</span>
                                <span class="material-symbols-outlined text-[18px] chevron transition-transform text-text-secondary" aria-hidden="true">expand_more</span>
                            </button>
                            <div class="finzo-dropdown-menu bg-surface dark:bg-[#18181B] border border-border-subtle rounded-xl shadow-xl py-1.5 z-50" role="listbox">
                                <div class="finzo-dropdown-option px-4 py-2 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer" data-value="Male">Male</div>
                                <div class="finzo-dropdown-option px-4 py-2 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer" data-value="Female">Female</div>
                                <div class="finzo-dropdown-option px-4 py-2 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer" data-value="Other">Other</div>
                                <div class="finzo-dropdown-option px-4 py-2 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer" data-value="Prefer not to say">Prefer not to say</div>
                            </div>
                        </div>
                        <span id="err-add-gender" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                    </div>
                    <div>
                        <label for="add-phone" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Contact Number *</label>
                        <input type="text" id="add-phone" class="fin-input bg-surface text-text-primary text-xs" placeholder="10-digit mobile number">
                        <span id="err-add-phone" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                    </div>
                    <div>
                        <label for="add-email" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Corporate Email *</label>
                        <input type="email" id="add-email" class="fin-input bg-surface text-text-primary text-xs" placeholder="official@finzo.com">
                        <span id="err-add-email" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: Initial Credentials -->
            <div>
                <div class="text-xs font-bold tracking-wider text-text-primary uppercase mb-3.5 py-1 border-b border-border-subtle flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm" aria-hidden="true">key</span>
                    2. Authentication Credentials
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="add-pw" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Initial Password *</label>
                        <input type="password" id="add-pw" class="fin-input bg-surface text-text-primary text-xs" placeholder="Minimum 8 characters" oninput="window.AdminProfile.checkAddStrength(this.value)">
                        <div class="password-strength-bar w-full h-1 bg-surface-subtle rounded-full overflow-hidden mt-1.5">
                            <div class="password-strength-fill h-full transition-all duration-300 rounded-full" id="add-pw-bar" style="width:0%; background:var(--color-primary-base);"></div>
                        </div>
                        <span id="err-add-pw" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                    </div>
                    <div>
                        <label for="add-conf-pw" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Confirm Password *</label>
                        <input type="password" id="add-conf-pw" class="fin-input bg-surface text-text-primary text-xs" placeholder="Confirm password">
                        <span id="err-add-conf-pw" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: Recovery Challenge & Role Assignment -->
            <div>
                <div class="text-xs font-bold tracking-wider text-text-primary uppercase mb-3.5 py-1 border-b border-border-subtle flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm" aria-hidden="true">shield</span>
                    3. Recovery Verification &amp; Role Authorization
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="add-seq-q" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Security Question *</label>
                        <input type="text" id="add-seq-q" class="fin-input bg-surface text-text-primary text-xs" placeholder="e.g. What city was your first office located in?">
                        <span id="err-add-seq-q" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                    </div>
                    <div>
                        <label for="add-seq-a" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Secret Answer *</label>
                        <input type="password" id="add-seq-a" class="fin-input bg-surface text-text-primary text-xs" placeholder="Confidential recovery answer">
                        <span id="err-add-seq-a" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                    </div>
                </div>

                <div class="mt-4 space-y-4">
                    <div>
                        <label class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Authorization Role Assignment *</label>
                        <div class="finzo-dropdown" id="dropdown-add-role">
                            <input type="hidden" id="add-role" value="" onchange="window.AdminProfile.updateRolePreview(this.value)">
                            <button type="button" class="finzo-dropdown-trigger bg-surface text-text-primary border border-border-subtle rounded-xl px-4 py-2.5 flex items-center justify-between w-full text-xs font-semibold hover:border-text-primary transition-colors focus:ring-2 focus:ring-text-primary focus:outline-none" aria-haspopup="listbox" aria-expanded="false">
                                <span class="dropdown-label text-text-primary">Select Role</span>
                                <span class="material-symbols-outlined text-[18px] chevron transition-transform text-text-secondary" aria-hidden="true">expand_more</span>
                            </button>
                            <div class="finzo-dropdown-menu bg-surface dark:bg-[#18181B] border border-border-subtle rounded-xl shadow-xl py-1.5 z-50" role="listbox">
                                <div class="finzo-dropdown-option px-4 py-2 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer" data-value="admin">Administrator &mdash; Standard Operational Authority</div>
                                <div class="finzo-dropdown-option px-4 py-2 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer" data-value="moderator">Moderator &mdash; Verification &amp; Approval Oversight</div>
                            </div>
                        </div>
                        <span id="err-add-role" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                    </div>

                    <!-- Role permission summary preview -->
                    <div id="role-preview-box" class="p-4 rounded-xl bg-surface-subtle border border-border-subtle text-xs text-text-secondary hidden leading-relaxed"></div>

                    <div>
                        <label for="new-admin-photo" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Profile Photo (Optional)</label>
                        <input type="file" id="new-admin-photo" accept="image/jpeg, image/png, image/webp" class="fin-input bg-surface text-text-primary text-xs" onchange="window.AdminProfile.previewNewPhoto(this)">
                        <span id="err-add-photo" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                        <div class="mt-3 flex items-center gap-3">
                            <img id="new-avatar-preview" src="" class="w-12 h-12 rounded-xl object-cover hidden border border-border-subtle shadow-xs" alt="Preview">
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Modal Footer -->
        <div class="p-5 border-t border-border-subtle bg-surface-subtle flex justify-end gap-3 sticky bottom-0 z-10 shrink-0">
            <button type="button" onclick="window.AdminProfile.closeAddModal()" class="px-5 py-2 rounded-xl text-xs font-semibold bg-surface text-text-primary hover:bg-surface-hover border border-border-subtle cursor-pointer transition-colors">Cancel</button>
            <button type="button" id="add-admin-submit-btn" onclick="window.AdminProfile.submitAddAdmin()" class="px-6 py-2 rounded-xl text-xs font-bold uppercase tracking-wider flex items-center gap-2 bg-zinc-900 text-white hover:bg-black dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100 shadow-sm cursor-pointer transition-all">
                <span>Create Administrator</span>
                <span class="material-symbols-outlined animate-spin text-[16px] hidden" aria-hidden="true">progress_activity</span>
            </button>
        </div>
    </div>
</div>

<!-- UNLOCK CONFIRMATION DIALOG -->
<div id="unlock-confirm-overlay" class="fixed inset-0 z-[2100] hidden items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-xs" onclick="window.AdminProfile.closeUnlockConfirm()"></div>
    <div class="relative w-full max-w-md bg-surface text-text-primary rounded-2xl p-6 shadow-2xl border border-border-subtle z-10 space-y-4 animate-modal-enter">
        <div class="flex items-center gap-3 text-warning">
            <div class="w-10 h-10 rounded-xl bg-warning-bg text-warning flex items-center justify-center border border-warning-border">
                <span class="material-symbols-outlined text-xl" aria-hidden="true">lock_open</span>
            </div>
            <div>
                <h4 class="font-headline font-bold text-base text-text-primary">Unlock Administrator Account</h4>
                <p class="text-xs text-text-secondary mt-0.5">Reset security lockout flags</p>
            </div>
        </div>
        <p id="unlock-confirm-msg" class="text-xs text-text-secondary leading-relaxed">Are you sure you want to restore access to this administrator account? Access permissions will be restored immediately.</p>
        <div class="flex justify-end gap-3 pt-3 border-t border-border-subtle">
            <button type="button" onclick="window.AdminProfile.closeUnlockConfirm()" class="px-4 py-2 rounded-xl text-xs font-semibold bg-surface text-text-primary hover:bg-surface-hover border border-border-subtle cursor-pointer transition-colors">Cancel</button>
            <button type="button" id="confirm-unlock-btn" onclick="window.AdminProfile.confirmUnlock()" class="px-5 py-2 rounded-xl text-xs font-bold uppercase tracking-wider bg-zinc-900 text-white hover:bg-black dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100 shadow-sm cursor-pointer transition-all">Confirm Unlock</button>
        </div>
    </div>
</div>

<?php
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/profile.js"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>