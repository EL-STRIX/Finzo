<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin - Executive Profile';
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
<!-- 1. HERO PROFILE OVERVIEW CARD              -->
<!-- ========================================== -->
<div class="ds-card p-6 sm:p-8 mb-6 relative overflow-hidden bg-surface border border-border-subtle shadow-xl rounded-3xl">
    <!-- Ambient glowing backdrop -->
    <div class="absolute -right-16 -top-16 w-80 h-80 bg-primary-base/10 dark:bg-primary-base/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -left-16 -bottom-16 w-72 h-72 bg-accent/5 dark:bg-accent/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative z-10 flex flex-col md:flex-row items-center md:items-start justify-between gap-6 sm:gap-8">
        
        <!-- Left: Avatar & Identity -->
        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6 text-center sm:text-left">
            <!-- Avatar with upload trigger & status badge -->
            <div class="relative group">
                <div class="w-28 h-28 sm:w-32 sm:h-32 rounded-3xl bg-gradient-to-br from-primary-500/20 to-amber-500/10 dark:from-primary-500/30 dark:to-stone-900 border-2 border-primary-500/30 flex items-center justify-center text-3xl sm:text-4xl font-black text-primary shadow-2xl overflow-hidden relative" id="avatar-preview-container">
                    <span id="avatar-initials" class="uppercase select-none">--</span>
                    <img loading="lazy" id="avatar-img-preview" src="" class="hidden w-full h-full object-cover" alt="Profile avatar">
                </div>
                
                <!-- Online Status Dot -->
                <span class="absolute -top-1 -right-1 flex h-4 w-4">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-4 w-4 bg-emerald-500 border-2 border-surface"></span>
                </span>

                <!-- Photo Change Button -->
                <label for="photo-upload" class="absolute -bottom-2 -right-2 w-10 h-10 bg-primary-base hover:bg-primary-hover text-white rounded-2xl flex items-center justify-center cursor-pointer shadow-lg transition-transform hover:scale-110 active:scale-95 border-2 border-surface" title="Change Profile Photo">
                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">photo_camera</span>
                </label>
                <input type="file" id="photo-upload" accept="image/jpeg, image/png" class="hidden" onchange="window.AdminProfile.uploadPhoto(this)">
            </div>

            <!-- Name, ID, Badges -->
            <div class="space-y-3">
                <div class="space-y-1">
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2.5">
                        <h1 class="text-2xl sm:text-3xl font-headline font-black text-text-primary tracking-tight" id="profile-name-display">Loading...</h1>
                        <span id="badge-role" class="px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-widest bg-primary-base text-white shadow-sm">—</span>
                    </div>
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-3 text-xs text-text-secondary font-medium">
                        <!-- Admin ID copy pill -->
                        <button type="button" onclick="window.AdminProfile.copyText(document.getElementById('profile-admin-id-input').value, 'Admin ID')" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-surface-subtle hover:bg-surface-hover border border-border-subtle font-mono text-[11px] text-text-primary transition-colors group cursor-pointer" title="Click to copy Admin ID">
                            <span class="material-symbols-outlined text-[14px] text-primary" aria-hidden="true">badge</span>
                            <span id="hero-admin-id-text">ID: ---</span>
                            <span class="material-symbols-outlined text-[12px] opacity-40 group-hover:opacity-100" aria-hidden="true">content_copy</span>
                        </button>

                        <!-- Email pill -->
                        <button type="button" onclick="window.AdminProfile.copyText(document.getElementById('profile-email-input').value, 'Email')" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-surface-subtle hover:bg-surface-hover border border-border-subtle text-[11px] text-text-secondary hover:text-text-primary transition-colors group cursor-pointer" title="Click to copy Email">
                            <span class="material-symbols-outlined text-[14px] text-info" aria-hidden="true">mail</span>
                            <span id="hero-email-text">admin@finzo.com</span>
                            <span class="material-symbols-outlined text-[12px] opacity-40 group-hover:opacity-100" aria-hidden="true">content_copy</span>
                        </button>
                    </div>
                </div>

                <!-- Status Pills -->
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 pt-1">
                    <span id="badge-status" class="px-3 py-1 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-[10px] font-black uppercase tracking-widest flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Active
                    </span>
                    <span id="badge-access" class="px-3 py-1 rounded-lg bg-info-bg text-info border border-info-border text-[10px] font-black uppercase tracking-widest">
                        Full Access
                    </span>
                    <span id="badge-sq-status" class="px-3 py-1 rounded-lg bg-surface-subtle text-text-secondary border border-border-subtle text-[10px] font-black uppercase tracking-widest flex items-center gap-1">
                        <span class="material-symbols-outlined text-[13px]" aria-hidden="true">shield</span>
                        <span id="badge-sq-label">Recovery: Checking...</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Right: Quick Stat Metrics -->
        <div class="grid grid-cols-2 sm:grid-cols-2 gap-3 w-full md:w-auto shrink-0">
            <div class="p-4 rounded-2xl bg-surface-subtle border border-border-subtle text-center sm:text-left min-w-[130px]">
                <span class="text-[10px] font-black text-text-secondary uppercase tracking-widest block mb-1">Security Score</span>
                <div class="flex items-center justify-center sm:justify-start gap-2">
                    <span class="text-xl font-headline font-black text-emerald-600 dark:text-emerald-400" id="hero-security-score">95%</span>
                    <span class="material-symbols-outlined text-emerald-500 text-lg" aria-hidden="true">verified_user</span>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ========================================== -->
<!-- 2. SECTION TABS NAVIGATION                 -->
<!-- ========================================== -->
<div class="flex items-center gap-2 mb-6 border-b border-border-subtle pb-px overflow-x-auto custom-scrollbar" role="tablist">
    <button type="button" role="tab" aria-selected="true" data-tab="tab-overview" onclick="window.AdminProfile.switchTab('tab-overview')" class="profile-tab-btn flex items-center gap-2 px-5 py-3 rounded-t-xl text-xs font-black uppercase tracking-wider transition-all border-b-2 border-primary-base text-primary bg-surface shadow-sm">
        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">person</span>
        <span>Account & Identity</span>
    </button>

    <button type="button" role="tab" aria-selected="false" data-tab="tab-security" onclick="window.AdminProfile.switchTab('tab-security')" class="profile-tab-btn flex items-center gap-2 px-5 py-3 rounded-t-xl text-xs font-black uppercase tracking-wider transition-all border-b-2 border-transparent text-text-secondary hover:text-text-primary hover:bg-surface-hover">
        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">shield_lock</span>
        <span>Security & Password</span>
    </button>

    <button type="button" role="tab" aria-selected="false" data-tab="tab-activity" onclick="window.AdminProfile.switchTab('tab-activity')" class="profile-tab-btn flex items-center gap-2 px-5 py-3 rounded-t-xl text-xs font-black uppercase tracking-wider transition-all border-b-2 border-transparent text-text-secondary hover:text-text-primary hover:bg-surface-hover">
        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">history</span>
        <span>Activity Log</span>
    </button>

    <button type="button" role="tab" aria-selected="false" id="tab-btn-directory" data-tab="tab-directory" onclick="window.AdminProfile.switchTab('tab-directory')" class="profile-tab-btn hidden items-center gap-2 px-5 py-3 rounded-t-xl text-xs font-black uppercase tracking-wider transition-all border-b-2 border-transparent text-text-secondary hover:text-text-primary hover:bg-surface-hover">
        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">manage_accounts</span>
        <span>Admin Directory</span>
    </button>
</div>

<!-- ========================================== -->
<!-- 3. TAB PANELS CONTAINER                    -->
<!-- ========================================== -->

<!-- TAB 1: ACCOUNT & IDENTITY -->
<div id="tab-overview" class="tab-panel space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Main Form (2 cols) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="ds-card p-6 sm:p-8 bg-surface border border-border-subtle rounded-3xl shadow-lg">
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-border-subtle">
                    <div>
                        <h3 class="font-headline font-bold text-lg text-text-primary flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary" aria-hidden="true">badge</span>
                            Administrator Identity
                        </h3>
                        <p class="text-xs text-text-secondary mt-1">Official profile details recorded across all audit logs</p>
                    </div>
                    <span class="px-2.5 py-1 bg-surface-subtle text-text-secondary text-[10px] font-mono font-bold rounded-lg border border-border-subtle" id="meta-role-label">System Role</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
                    <div>
                        <label for="profile-admin-id-input" class="fin-label mb-1.5 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Admin ID (System Generated)</label>
                        <div class="fin-input-wrapper">
                            <span class="material-symbols-outlined input-icon-left text-text-secondary">fingerprint</span>
                            <input type="text" id="profile-admin-id-input" class="fin-input has-icon-left opacity-75 bg-surface-subtle cursor-not-allowed font-mono text-text-primary" readonly>
                        </div>
                    </div>

                    <div>
                        <label for="profile-name-input" class="fin-label mb-1.5 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Full Name *</label>
                        <div class="fin-input-wrapper">
                            <span class="material-symbols-outlined input-icon-left text-text-secondary">person</span>
                            <input type="text" id="profile-name-input" class="fin-input has-icon-left bg-surface text-text-primary border-border-subtle" placeholder="e.g. Sujay Paul">
                        </div>
                    </div>

                    <div>
                        <label for="profile-dob-input" class="fin-label mb-1.5 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Date of Birth</label>
                        <div class="fin-input-wrapper">
                            <span class="material-symbols-outlined input-icon-left text-text-secondary">calendar_today</span>
                            <input type="date" id="profile-dob-input" class="fin-input has-icon-left bg-surface text-text-primary border-border-subtle">
                        </div>
                    </div>

                    <div>
                        <label class="fin-label mb-1.5 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Gender</label>
                        <!-- Standard Finzo Dropdown Component -->
                        <div class="finzo-dropdown" id="dropdown-gender">
                            <input type="hidden" id="profile-gender-input" value="">
                            <button type="button" class="finzo-dropdown-trigger bg-surface text-text-primary border border-border-subtle rounded-xl px-4 py-3 flex items-center justify-between w-full text-sm font-medium hover:border-primary transition-colors focus:ring-2 focus:ring-primary focus:outline-none" aria-haspopup="listbox" aria-expanded="false">
                                <div class="flex items-center gap-2.5">
                                    <span class="material-symbols-outlined text-text-secondary text-[18px]" aria-hidden="true">wc</span>
                                    <span class="dropdown-label text-text-primary">Select Gender</span>
                                </div>
                                <span class="material-symbols-outlined text-[18px] chevron transition-transform text-text-secondary" aria-hidden="true">expand_more</span>
                            </button>
                            <div class="finzo-dropdown-menu bg-surface dark:bg-[#18181B] border border-border-subtle rounded-2xl shadow-2xl py-2 z-50" role="listbox">
                                <div class="finzo-dropdown-option px-4 py-2.5 text-xs font-bold text-text-primary hover:bg-surface-hover cursor-pointer transition-colors" data-value="Male">Male</div>
                                <div class="finzo-dropdown-option px-4 py-2.5 text-xs font-bold text-text-primary hover:bg-surface-hover cursor-pointer transition-colors" data-value="Female">Female</div>
                                <div class="finzo-dropdown-option px-4 py-2.5 text-xs font-bold text-text-primary hover:bg-surface-hover cursor-pointer transition-colors" data-value="Other">Other</div>
                                <div class="finzo-dropdown-option px-4 py-2.5 text-xs font-bold text-text-primary hover:bg-surface-hover cursor-pointer transition-colors" data-value="Prefer not to say">Prefer not to say</div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="profile-phone-input" class="fin-label mb-1.5 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Phone Number</label>
                        <div class="fin-input-wrapper">
                            <span class="material-symbols-outlined input-icon-left text-text-secondary">call</span>
                            <input type="text" id="profile-phone-input" class="fin-input has-icon-left bg-surface text-text-primary border-border-subtle" placeholder="10-digit phone number">
                        </div>
                    </div>

                    <div>
                        <label for="profile-email-input" class="fin-label mb-1.5 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Email Address (Read-only)</label>
                        <div class="fin-input-wrapper">
                            <span class="material-symbols-outlined input-icon-left text-text-secondary">mail</span>
                            <input type="email" id="profile-email-input" class="fin-input has-icon-left opacity-75 bg-surface-subtle cursor-not-allowed text-text-primary" readonly>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-border-subtle flex items-center justify-between">
                    <span class="text-xs text-text-secondary flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px] text-emerald-500" aria-hidden="true">check_circle</span>
                        Changes are saved instantly to database
                    </span>
                    <button type="button" onclick="window.AdminProfile.saveProfile()" class="btn btn-primary px-8 py-3 rounded-xl text-xs font-black uppercase tracking-wider inline-flex items-center gap-2 shadow-lg shadow-amber-900/20 hover:scale-[1.02] active:scale-[0.98] transition-all">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">save</span>
                        <span>Save Profile</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Right: Security & Meta Overview (1 col) -->
        <div class="space-y-6">
            <div class="ds-card p-6 sm:p-8 bg-surface border border-border-subtle rounded-3xl shadow-lg space-y-5">
                <div class="flex items-center gap-3 pb-4 border-b border-border-subtle">
                    <div class="w-10 h-10 rounded-2xl bg-primary/10 text-primary flex items-center justify-center shadow-inner">
                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">security</span>
                    </div>
                    <div>
                        <h3 class="font-headline font-bold text-base text-text-primary">System Telemetry</h3>
                        <p class="text-[10px] font-bold text-text-secondary uppercase tracking-wider">Access & Session Auditing</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex justify-between items-center py-2 border-b border-border-subtle">
                        <span class="text-[10px] font-black text-text-secondary uppercase tracking-widest">Last Login Time</span>
                        <span class="text-xs font-bold text-text-primary" id="sec-last-login">—</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-border-subtle">
                        <span class="text-[10px] font-black text-text-secondary uppercase tracking-widest">Client IP Address</span>
                        <span class="text-xs font-mono font-bold text-text-primary" id="sec-last-ip">—</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-border-subtle">
                        <span class="text-[10px] font-black text-text-secondary uppercase tracking-widest">Password Rotated</span>
                        <span class="text-xs font-bold text-text-primary" id="sec-last-pwd">—</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-[10px] font-black text-text-secondary uppercase tracking-widest">Role Scope</span>
                        <span class="text-xs font-bold text-primary" id="sec-role-scope">Super Administrator</span>
                    </div>
                </div>
            </div>

            <!-- Quick Security Status Card -->
            <div class="ds-card p-6 bg-surface-subtle border border-border-subtle rounded-3xl shadow-md space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-black text-text-primary uppercase tracking-wider">Two-Factor Authentication</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="sec-2fa-toggle" class="sr-only peer" onchange="window.AdminProfile.toggle2FA(this.checked)">
                        <div class="w-11 h-6 bg-surface-hover peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-border-subtle after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                    </label>
                </div>
                <p class="text-[11px] text-text-secondary leading-relaxed">Require secondary authentication challenge on all sensitive operations and new device logins.</p>
                <div class="pt-2">
                    <span class="text-[10px] font-mono font-black uppercase tracking-widest px-2.5 py-1 rounded bg-surface border border-border-subtle text-text-secondary" id="sec-2fa-label">DISABLED</span>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- TAB 2: SECURITY & PASSWORD -->
<div id="tab-security" class="tab-panel hidden space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Change Password Card -->
        <div class="ds-card p-6 sm:p-8 bg-surface border border-border-subtle rounded-3xl shadow-lg space-y-6">
            <div class="flex items-center gap-3 pb-4 border-b border-border-subtle">
                <div class="w-10 h-10 rounded-2xl bg-amber-500/10 text-amber-500 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[22px]" aria-hidden="true">password</span>
                </div>
                <div>
                    <h3 class="font-headline font-bold text-lg text-text-primary">Update Admin Password</h3>
                    <p class="text-xs text-text-secondary mt-0.5">Enforce strong entropy for account protection</p>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label for="curr-pw" class="fin-label mb-1.5 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Current Password *</label>
                    <div class="fin-input-wrapper relative">
                        <span class="material-symbols-outlined input-icon-left text-text-secondary">lock</span>
                        <input type="password" id="curr-pw" class="fin-input has-icon-left pr-10 bg-surface text-text-primary border-border-subtle" placeholder="Enter current password">
                        <button type="button" onclick="window.AdminProfile.togglePW('curr-pw')" class="absolute right-3 text-text-secondary hover:text-text-primary focus:outline-none" title="Toggle Visibility">
                            <span class="material-symbols-outlined text-[18px]">visibility</span>
                        </button>
                    </div>
                </div>

                <div>
                    <label for="new-pw" class="fin-label mb-1.5 block text-[10px] font-black text-text-secondary uppercase tracking-widest">New Password *</label>
                    <div class="fin-input-wrapper relative">
                        <span class="material-symbols-outlined input-icon-left text-text-secondary">lock_open</span>
                        <input type="password" id="new-pw" class="fin-input has-icon-left pr-10 bg-surface text-text-primary border-border-subtle" placeholder="Minimum 8 characters" oninput="window.AdminProfile.checkStrength(this.value)">
                        <button type="button" onclick="window.AdminProfile.togglePW('new-pw')" class="absolute right-3 text-text-secondary hover:text-text-primary focus:outline-none" title="Toggle Visibility">
                            <span class="material-symbols-outlined text-[18px]">visibility</span>
                        </button>
                    </div>
                    <!-- Password strength bar -->
                    <div class="mt-2.5">
                        <div class="password-strength-bar w-full h-1.5 bg-surface-hover rounded-full overflow-hidden">
                            <div class="password-strength-fill h-full transition-all duration-300 rounded-full" id="pw-bar" style="width:0%; background:var(--primary-base);"></div>
                        </div>
                        <div class="flex justify-between items-center mt-1">
                            <span class="text-[10px] text-text-secondary font-medium">Strength Indicator</span>
                            <span class="text-[10px] font-black uppercase tracking-widest" id="pw-text"></span>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="conf-pw" class="fin-label mb-1.5 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Confirm New Password *</label>
                    <div class="fin-input-wrapper relative">
                        <span class="material-symbols-outlined input-icon-left text-text-secondary">check_circle</span>
                        <input type="password" id="conf-pw" class="fin-input has-icon-left pr-10 bg-surface text-text-primary border-border-subtle" placeholder="Confirm new password">
                        <button type="button" onclick="window.AdminProfile.togglePW('conf-pw')" class="absolute right-3 text-text-secondary hover:text-text-primary focus:outline-none" title="Toggle Visibility">
                            <span class="material-symbols-outlined text-[18px]">visibility</span>
                        </button>
                    </div>
                </div>

                <div class="pt-3">
                    <button type="button" onclick="window.AdminProfile.updatePassword()" class="w-full btn btn-primary py-3 rounded-xl text-xs font-black uppercase tracking-wider shadow-lg shadow-amber-900/20 hover:scale-[1.01] active:scale-[0.99] transition-all">
                        Update Password
                    </button>
                </div>
            </div>
        </div>

        <!-- Master Security Question Card -->
        <div class="ds-card p-6 sm:p-8 bg-surface border border-border-subtle rounded-3xl shadow-lg space-y-6">
            <div class="flex items-center justify-between pb-4 border-b border-border-subtle">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-info/10 text-info flex items-center justify-center">
                        <span class="material-symbols-outlined text-[22px]" aria-hidden="true">quiz</span>
                    </div>
                    <div>
                        <h3 class="font-headline font-bold text-lg text-text-primary">Master Security Question</h3>
                        <p class="text-xs text-text-secondary mt-0.5">Emergency self-recovery protection</p>
                    </div>
                </div>
                <div id="sq-status-container"></div>
            </div>

            <p class="text-xs text-text-secondary leading-relaxed font-medium">Configure a secret recovery question to restore administrative access if account lockouts occur due to multiple failed login attempts.</p>

            <form id="sq-setup-form" onsubmit="window.AdminProfile.saveSecurityQuestion(event)" class="space-y-4">
                <div>
                    <label for="sq-question" class="fin-label mb-1.5 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Secret Question *</label>
                    <input type="text" id="sq-question" class="fin-input bg-surface text-text-primary border-border-subtle" placeholder="e.g. In what city were you born?" minlength="10" required>
                </div>
                <div>
                    <label for="sq-answer" class="fin-label mb-1.5 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Secret Answer *</label>
                    <input type="password" id="sq-answer" class="fin-input bg-surface text-text-primary border-border-subtle" placeholder="Enter your confidential answer" minlength="3" required>
                </div>
                <div class="pt-2 border-t border-border-subtle">
                    <label for="sq-curr-pw" class="fin-label mb-1.5 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Current Password (Authentication Check) *</label>
                    <input type="password" id="sq-curr-pw" class="fin-input bg-surface text-text-primary border-border-subtle" placeholder="Authenticate changes" required>
                </div>
                <div class="pt-2 flex justify-end">
                    <button type="submit" id="btn-save-sq" class="btn btn-secondary px-8 py-3 rounded-xl text-xs font-black uppercase tracking-wider shadow-sm transition-all">
                        <span id="sq-btn-text">Set Security Question</span>
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

<!-- TAB 3: ACTIVITY LOG & AUDIT -->
<div id="tab-activity" class="tab-panel hidden space-y-6">
    <div class="ds-card bg-surface border border-border-subtle rounded-3xl shadow-lg overflow-hidden">
        <div class="p-6 border-b border-border-subtle flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-surface-subtle">
            <div>
                <h3 class="font-headline font-bold text-lg text-text-primary flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary" aria-hidden="true">history</span>
                    Personal Audit Trail
                </h3>
                <p class="text-xs text-text-secondary mt-0.5">Chronological record of administrative operations and session history</p>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" onclick="window.AdminProfile.exportActivityCSV()" class="btn btn-secondary !px-4 !py-2 !text-xs rounded-xl inline-flex items-center gap-2 shadow-sm">
                    <span class="material-symbols-outlined text-[16px]" aria-hidden="true">download</span>
                    <span>Export CSV</span>
                </button>
            </div>
        </div>

        <div class="ds-table-wrapper w-full border-0 shadow-none rounded-none bg-transparent overflow-x-auto">
            <table class="ds-table w-full text-left min-w-[700px]">
                <thead class="bg-surface-subtle border-b border-border-subtle">
                    <tr>
                        <th class="py-3.5 px-6 text-[10px] font-black text-text-secondary uppercase tracking-widest" scope="col">Operation / Action</th>
                        <th class="py-3.5 px-6 text-[10px] font-black text-text-secondary uppercase tracking-widest" scope="col">Details & Context</th>
                        <th class="py-3.5 px-6 text-[10px] font-black text-text-secondary uppercase tracking-widest" scope="col">IP Address</th>
                        <th class="py-3.5 px-6 text-right text-[10px] font-black text-text-secondary uppercase tracking-widest" scope="col">Date & Time</th>
                    </tr>
                </thead>
                <tbody id="activity-table-body" class="divide-y divide-border-subtle">
                    <tr><td colspan="4" class="text-center italic opacity-50 py-12 text-sm">Loading activity logs...</td></tr>
                </tbody>
            </table>
        </div>

        <div class="p-4 sm:p-6 border-t border-border-subtle flex flex-col sm:flex-row items-center justify-between gap-4 bg-surface-subtle" id="activity-pagination-container">
            <div class="text-[11px] font-black text-text-secondary uppercase tracking-widest" id="activity-pagination-info">Showing 0 to 0 of 0</div>
            <div class="flex gap-2">
                <button id="activity-prev-btn" onclick="window.AdminProfile.prevActivityPage()" class="btn btn-secondary !px-4 !py-1.5 !text-xs rounded-xl">Prev</button>
                <button id="activity-next-btn" onclick="window.AdminProfile.nextActivityPage()" class="btn btn-secondary !px-4 !py-1.5 !text-xs rounded-xl">Next</button>
            </div>
        </div>
    </div>
</div>

<!-- TAB 4: ADMIN DIRECTORY & ACCESS CONTROL (Super Admin) -->
<div id="tab-directory" class="tab-panel hidden space-y-6">
    
    <!-- LOCKED ACCOUNTS BANNER / TABLE -->
    <div id="locked-admins-container" class="ds-card bg-surface border border-danger-border rounded-3xl shadow-xl overflow-hidden hidden">
        <div class="p-6 border-b border-danger-border flex justify-between items-center bg-danger-bg">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-danger/20 text-danger flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">lock_clock</span>
                </div>
                <div>
                    <h3 class="font-headline font-bold text-base text-danger">Locked Administrator Accounts</h3>
                    <p class="text-xs text-danger/80 mt-0.5">Accounts locked due to repeated authentication failures</p>
                </div>
            </div>
            <span id="locked-count-badge" class="px-3 py-1 bg-danger/20 text-danger text-[10px] font-black rounded-lg uppercase tracking-widest border border-danger/30">0 Locked</span>
        </div>
        <div class="ds-table-wrapper w-full border-0 shadow-none rounded-none bg-transparent overflow-x-auto">
            <table class="ds-table w-full text-left min-w-[800px]">
                <thead class="bg-surface-subtle border-b border-border-subtle">
                    <tr>
                        <th class="py-3 px-6 text-[10px] font-black text-text-secondary uppercase tracking-widest" scope="col">Administrator</th>
                        <th class="py-3 px-4 text-[10px] font-black text-text-secondary uppercase tracking-widest" scope="col">Role</th>
                        <th class="py-3 px-4 text-[10px] font-black text-text-secondary uppercase tracking-widest" scope="col">Locked At</th>
                        <th class="py-3 px-4 text-[10px] font-black text-text-secondary uppercase tracking-widest" scope="col">Lock Reason</th>
                        <th class="py-3 px-4 text-[10px] font-black text-text-secondary uppercase tracking-widest" scope="col">Failed Attempts</th>
                        <th class="py-3 px-4 text-[10px] font-black text-text-secondary uppercase tracking-widest" scope="col">Recovery Setup</th>
                        <th class="py-3 px-4 text-right text-[10px] font-black text-text-secondary uppercase tracking-widest" scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody id="locked-admins-body" class="divide-y divide-border-subtle"></tbody>
            </table>
        </div>
    </div>

    <!-- GLOBAL DIRECTORY CARD -->
    <div id="admin-management-container" class="ds-card bg-surface border border-border-subtle rounded-3xl shadow-xl overflow-hidden">
        <div class="p-6 border-b border-border-subtle flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-surface-subtle">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-primary/10 text-primary flex items-center justify-center shadow-inner">
                    <span class="material-symbols-outlined text-[22px]" aria-hidden="true">manage_accounts</span>
                </div>
                <div>
                    <h3 class="font-headline font-bold text-lg text-text-primary">Global Administrator Directory</h3>
                    <p class="text-xs text-text-secondary mt-0.5">Provision, inspect, and configure enterprise administrator privileges</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span id="management-count-badge" class="px-3 py-1.5 bg-surface text-text-secondary text-[10px] font-mono font-bold rounded-xl border border-border-subtle">0 Admins</span>
                <button id="header-add-admin-btn" type="button" onclick="window.AdminProfile.showAddModal()" class="btn btn-primary px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider inline-flex items-center gap-2 shadow-lg shadow-amber-900/20 hover:scale-[1.02] active:scale-[0.98] transition-all">
                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">person_add</span>
                    <span>Provision Admin</span>
                </button>
            </div>
        </div>

        <!-- Filter controls -->
        <div class="p-4 sm:p-6 border-b border-border-subtle bg-surface flex flex-col sm:flex-row gap-4">
            <div class="fin-input-wrapper flex-1">
                <span class="material-symbols-outlined input-icon-left text-text-secondary">search</span>
                <input type="text" id="admin-search-input" placeholder="Search by name, email, or admin ID..." class="fin-input has-icon-left bg-surface text-text-primary border-border-subtle rounded-xl">
            </div>
            
            <div class="flex gap-3">
                <select id="admin-status-filter" class="fin-input w-40 bg-surface text-text-primary border border-border-subtle rounded-xl px-4 text-xs font-bold">
                    <option value="all">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="suspended">Suspended</option>
                </select>

                <select id="admin-role-filter" class="fin-input w-40 bg-surface text-text-primary border border-border-subtle rounded-xl px-4 text-xs font-bold">
                    <option value="all">All Roles</option>
                    <option value="admin">Admin</option>
                    <option value="moderator">Moderator</option>
                </select>
            </div>
        </div>

        <!-- Directory Table -->
        <div class="ds-table-wrapper w-full border-0 shadow-none rounded-none bg-transparent overflow-x-auto">
            <table class="ds-table w-full text-left min-w-[900px]">
                <thead class="bg-surface-subtle border-b border-border-subtle">
                    <tr>
                        <th class="py-3.5 px-6 text-[10px] font-black text-text-secondary uppercase tracking-widest" scope="col">ID</th>
                        <th class="py-3.5 px-6 text-[10px] font-black text-text-secondary uppercase tracking-widest" scope="col">Administrator Details</th>
                        <th class="py-3.5 px-6 text-[10px] font-black text-text-secondary uppercase tracking-widest" scope="col">Role</th>
                        <th class="py-3.5 px-6 text-[10px] font-black text-text-secondary uppercase tracking-widest" scope="col">Access Scope</th>
                        <th class="py-3.5 px-6 text-[10px] font-black text-text-secondary uppercase tracking-widest" scope="col">Status</th>
                        <th class="py-3.5 px-6 text-[10px] font-black text-text-secondary uppercase tracking-widest" scope="col">Last Active</th>
                        <th class="py-3.5 px-6 text-[10px] font-black text-text-secondary uppercase tracking-widest" scope="col">Created By</th>
                        <th class="py-3.5 px-6 text-right text-[10px] font-black text-text-secondary uppercase tracking-widest" scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody id="admin-management-body" class="divide-y divide-border-subtle"></tbody>
            </table>
        </div>
    </div>

</div>

<!-- ========================================== -->
<!-- 4. MODALS & OVERLAYS                       -->
<!-- ========================================== -->

<!-- PROVISION ADMIN MODAL (Super Admin) -->
<div id="add-admin-modal" class="fixed inset-0 z-[2000] hidden items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-md transition-all" onclick="window.AdminProfile.closeAddModal()"></div>
    
    <div class="relative w-full max-w-2xl bg-surface text-text-primary rounded-3xl overflow-hidden shadow-2xl flex flex-col max-h-[90vh] border border-border-subtle animate-modal-enter z-10">
        
        <!-- Header -->
        <div class="p-6 border-b border-border-subtle flex justify-between items-center sticky top-0 bg-surface z-10 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-primary/10 text-primary flex items-center justify-center">
                    <span class="material-symbols-outlined text-[22px]" aria-hidden="true">person_add</span>
                </div>
                <div>
                    <h3 class="font-headline font-black text-xl text-text-primary tracking-tight">Provision Administrator</h3>
                    <p class="text-xs font-bold text-text-secondary uppercase tracking-wider mt-0.5">Create a credentialed enterprise admin account</p>
                </div>
            </div>
            <button type="button" onclick="window.AdminProfile.closeAddModal()" class="p-2 hover:bg-surface-hover rounded-xl transition-colors text-text-secondary hover:text-text-primary">
                <span class="material-symbols-outlined" aria-hidden="true">close</span>
            </button>
        </div>

        <!-- Body -->
        <div class="p-6 sm:p-8 overflow-y-auto flex-1 space-y-6 custom-scrollbar">
            
            <!-- SECTION 1: Personal & Contact Details -->
            <div>
                <div class="text-xs font-black tracking-wider text-primary uppercase mb-4 py-1.5 border-b border-border-subtle flex items-center gap-2">
                    <span class="material-symbols-outlined text-base" aria-hidden="true">badge</span>
                    1. Personal & Contact Details
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="add-admin-id" class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Admin ID (System Assigned)</label>
                        <input type="text" id="add-admin-id" class="fin-input opacity-70 bg-surface-subtle font-mono text-text-primary" readonly>
                    </div>
                    <div>
                        <label for="add-name" class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Full Legal Name *</label>
                        <input type="text" id="add-name" class="fin-input bg-surface text-text-primary" placeholder="e.g. John Doe">
                        <span id="err-add-name" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                    </div>
                    <div>
                        <label for="add-dob" class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Date of Birth *</label>
                        <input type="date" id="add-dob" class="fin-input bg-surface text-text-primary">
                        <span id="err-add-dob" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                    </div>
                    <div>
                        <label class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Gender *</label>
                        <div class="finzo-dropdown" id="dropdown-add-gender">
                            <input type="hidden" id="add-gender" value="">
                            <button type="button" class="finzo-dropdown-trigger bg-surface text-text-primary border border-border-subtle rounded-xl px-4 py-3 flex items-center justify-between w-full text-sm font-medium hover:border-primary transition-colors focus:ring-2 focus:ring-primary focus:outline-none" aria-haspopup="listbox" aria-expanded="false">
                                <span class="dropdown-label text-text-primary">Select Gender</span>
                                <span class="material-symbols-outlined text-[18px] chevron transition-transform text-text-secondary" aria-hidden="true">expand_more</span>
                            </button>
                            <div class="finzo-dropdown-menu bg-surface dark:bg-[#18181B] border border-border-subtle rounded-2xl shadow-2xl py-2 z-50" role="listbox">
                                <div class="finzo-dropdown-option px-4 py-2.5 text-xs font-bold text-text-primary hover:bg-surface-hover cursor-pointer" data-value="Male">Male</div>
                                <div class="finzo-dropdown-option px-4 py-2.5 text-xs font-bold text-text-primary hover:bg-surface-hover cursor-pointer" data-value="Female">Female</div>
                                <div class="finzo-dropdown-option px-4 py-2.5 text-xs font-bold text-text-primary hover:bg-surface-hover cursor-pointer" data-value="Other">Other</div>
                                <div class="finzo-dropdown-option px-4 py-2.5 text-xs font-bold text-text-primary hover:bg-surface-hover cursor-pointer" data-value="Prefer not to say">Prefer not to say</div>
                            </div>
                        </div>
                        <span id="err-add-gender" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                    </div>
                    <div>
                        <label for="add-phone" class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Phone Number *</label>
                        <input type="text" id="add-phone" class="fin-input bg-surface text-text-primary" placeholder="10-digit mobile number">
                        <span id="err-add-phone" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                    </div>
                    <div>
                        <label for="add-email" class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Email Address *</label>
                        <input type="email" id="add-email" class="fin-input bg-surface text-text-primary" placeholder="admin@finzo.com">
                        <span id="err-add-email" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: Initial Credentials -->
            <div>
                <div class="text-xs font-black tracking-wider text-primary uppercase mb-4 py-1.5 border-b border-border-subtle flex items-center gap-2">
                    <span class="material-symbols-outlined text-base" aria-hidden="true">key</span>
                    2. Authentication Credentials
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="add-pw" class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Initial Password *</label>
                        <input type="password" id="add-pw" class="fin-input bg-surface text-text-primary" placeholder="Min 8 characters" oninput="window.AdminProfile.checkAddStrength(this.value)">
                        <div class="password-strength-bar w-full h-1.5 bg-surface-hover rounded-full overflow-hidden mt-2">
                            <div class="password-strength-fill h-full transition-all duration-300 rounded-full" id="add-pw-bar" style="width:0%; background:var(--primary-base);"></div>
                        </div>
                        <span id="err-add-pw" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                    </div>
                    <div>
                        <label for="add-conf-pw" class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Confirm Password *</label>
                        <input type="password" id="add-conf-pw" class="fin-input bg-surface text-text-primary" placeholder="Confirm password">
                        <span id="err-add-conf-pw" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: Account Recovery & RBAC Role -->
            <div>
                <div class="text-xs font-black tracking-wider text-primary uppercase mb-4 py-1.5 border-b border-border-subtle flex items-center gap-2">
                    <span class="material-symbols-outlined text-base" aria-hidden="true">policy</span>
                    3. Recovery & Permissions Role
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="add-seq-q" class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Security Question *</label>
                        <input type="text" id="add-seq-q" class="fin-input bg-surface text-text-primary" placeholder="e.g. What was your first vehicle?">
                        <span id="err-add-seq-q" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                    </div>
                    <div>
                        <label for="add-seq-a" class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Secret Answer *</label>
                        <input type="password" id="add-seq-a" class="fin-input bg-surface text-text-primary" placeholder="Confidential recovery answer">
                        <span id="err-add-seq-a" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                    </div>
                </div>

                <div class="mt-4 space-y-4">
                    <div>
                        <label class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Role Assignment *</label>
                        <div class="finzo-dropdown" id="dropdown-add-role">
                            <input type="hidden" id="add-role" value="" onchange="window.AdminProfile.updateRolePreview(this.value)">
                            <button type="button" class="finzo-dropdown-trigger bg-surface text-text-primary border border-border-subtle rounded-xl px-4 py-3 flex items-center justify-between w-full text-sm font-medium hover:border-primary transition-colors focus:ring-2 focus:ring-primary focus:outline-none" aria-haspopup="listbox" aria-expanded="false">
                                <span class="dropdown-label text-text-primary">Select Role</span>
                                <span class="material-symbols-outlined text-[18px] chevron transition-transform text-text-secondary" aria-hidden="true">expand_more</span>
                            </button>
                            <div class="finzo-dropdown-menu bg-surface dark:bg-[#18181B] border border-border-subtle rounded-2xl shadow-2xl py-2 z-50" role="listbox">
                                <div class="finzo-dropdown-option px-4 py-2.5 text-xs font-bold text-text-primary hover:bg-surface-hover cursor-pointer" data-value="admin">Administrator (Full operational access)</div>
                                <div class="finzo-dropdown-option px-4 py-2.5 text-xs font-bold text-text-primary hover:bg-surface-hover cursor-pointer" data-value="moderator">Moderator (KYC & Pending approvals only)</div>
                            </div>
                        </div>
                        <span id="err-add-role" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                    </div>

                    <!-- Role permission summary preview -->
                    <div id="role-preview-box" class="p-4 rounded-2xl bg-surface-subtle border border-border-subtle text-xs text-text-secondary hidden leading-relaxed"></div>

                    <div>
                        <label for="new-admin-photo" class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Profile Photo (Optional)</label>
                        <input type="file" id="new-admin-photo" accept="image/jpeg, image/png" class="fin-input bg-surface text-text-primary" onchange="window.AdminProfile.previewNewPhoto(this)">
                        <div class="mt-3 flex items-center gap-3">
                            <img id="new-avatar-preview" src="" class="w-14 h-14 rounded-2xl object-cover hidden border-2 border-primary/30 shadow-md" alt="Preview">
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Footer -->
        <div class="p-6 border-t border-border-subtle bg-surface-subtle flex justify-end gap-3 sticky bottom-0 z-10 shrink-0">
            <button type="button" onclick="window.AdminProfile.closeAddModal()" class="btn btn-secondary px-6 py-2.5 rounded-xl text-xs font-bold">Cancel</button>
            <button type="button" id="add-admin-submit-btn" onclick="window.AdminProfile.submitAddAdmin()" class="btn btn-primary px-8 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider flex items-center gap-2 shadow-lg shadow-amber-900/20">
                <span>Create Admin</span>
                <span class="material-symbols-outlined animate-spin text-[16px] hidden" aria-hidden="true">progress_activity</span>
            </button>
        </div>
    </div>
</div>

<!-- UNLOCK CONFIRM OVERLAY -->
<div id="unlock-confirm-overlay" class="fixed inset-0 z-[2100] hidden items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-md" onclick="window.AdminProfile.closeUnlockConfirm()"></div>
    <div class="relative w-full max-w-md bg-surface text-text-primary rounded-3xl p-6 sm:p-8 shadow-2xl border border-border-subtle z-10 space-y-5 animate-modal-enter">
        <div class="flex items-center gap-3 text-warning">
            <div class="w-12 h-12 rounded-2xl bg-warning-bg text-warning flex items-center justify-center border border-warning-border">
                <span class="material-symbols-outlined text-2xl" aria-hidden="true">lock_open</span>
            </div>
            <div>
                <h4 class="font-headline font-black text-lg text-text-primary">Unlock Administrator</h4>
                <p class="text-xs text-text-secondary mt-0.5">Clear lockout security flags</p>
            </div>
        </div>
        <p id="unlock-confirm-msg" class="text-sm text-text-secondary leading-relaxed">Are you sure you want to restore access to this administrator account?</p>
        <div class="flex justify-end gap-3 pt-4 border-t border-border-subtle">
            <button type="button" onclick="window.AdminProfile.closeUnlockConfirm()" class="btn btn-secondary px-5 py-2.5 rounded-xl text-xs font-bold">Cancel</button>
            <button type="button" id="confirm-unlock-btn" onclick="window.AdminProfile.confirmUnlock()" class="btn btn-primary px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider shadow-lg shadow-amber-900/20">Confirm Unlock</button>
        </div>
    </div>
</div>

<?php
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/profile.js"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>