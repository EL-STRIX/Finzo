<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin — Administrator Profile & Governance';
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

<div class="space-y-6">

    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- 1. BREADCRUMBS & PAGE HEADER                                    -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-border-subtle">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-bold text-text-secondary uppercase tracking-wider mb-1">
                <span>Administration</span>
                <span>/</span>
                <span>System Access</span>
                <span>/</span>
                <span class="text-text-primary font-bold">Officer Profile</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-headline font-bold text-text-primary tracking-tight">Administrator Profile</h1>
            <p class="text-xs text-text-secondary mt-0.5">Account identity, delegated system authorities, security credentials, and personal audit log.</p>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            <button type="button" onclick="window.AdminProfile.exportActivityCSV()" class="px-3.5 py-2 text-xs font-semibold rounded-lg bg-surface text-text-primary hover:bg-surface-hover border border-border-subtle shadow-xs inline-flex items-center gap-1.5 transition-colors cursor-pointer" title="Export Activity Log to CSV">
                <span class="material-symbols-outlined text-[16px]" aria-hidden="true">download</span>
                <span>Export Activity Log</span>
            </button>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- 2. OFFICER SUMMARY DOSSIER CARD                                 -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <div class="ds-card p-5 sm:p-6 bg-surface border border-border-subtle rounded-xl shadow-xs">
        <div class="flex flex-col md:flex-row items-center md:items-start justify-between gap-6 text-center md:text-left">
            
            <!-- Left: Portrait & Core Identity -->
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4">
                <!-- Officer Portrait -->
                <div class="relative shrink-0">
                    <div class="w-18 h-18 sm:w-20 sm:h-20 rounded-xl bg-surface-subtle border border-border-subtle flex items-center justify-center text-xl font-bold text-text-primary shadow-xs overflow-hidden relative" id="avatar-preview-container">
                        <span id="avatar-initials" class="uppercase select-none font-mono font-bold">--</span>
                        <img loading="lazy" id="avatar-img-preview" src="" class="hidden w-full h-full object-cover" alt="Profile photo">
                    </div>

                    <!-- Photo Upload Trigger -->
                    <label for="photo-upload" class="absolute -bottom-1 -right-1 w-6 h-6 bg-zinc-900 text-white hover:bg-black dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100 rounded-md flex items-center justify-center cursor-pointer shadow-xs transition-transform hover:scale-105 active:scale-95 border border-surface" title="Update Profile Photo">
                        <span class="material-symbols-outlined text-[13px]" aria-hidden="true">photo_camera</span>
                    </label>
                    <input type="file" id="photo-upload" accept="image/jpeg, image/png" class="hidden" onchange="window.AdminProfile.uploadPhoto(this)">
                </div>

                <!-- Name, Roles, Badges -->
                <div class="space-y-1.5">
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                        <h2 class="text-lg font-headline font-bold text-text-primary tracking-tight" id="profile-name-display">Loading...</h2>
                        <span id="badge-role" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900 shadow-xs">—</span>
                        <span id="badge-status" class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-[10px] font-bold uppercase tracking-wider flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Active
                        </span>
                        <span id="badge-access" class="px-2 py-0.5 rounded bg-surface-subtle text-text-primary border border-border-subtle text-[10px] font-bold uppercase tracking-wider">
                            Full Access
                        </span>
                    </div>

                    <!-- Copyable ID & Email -->
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 text-xs text-text-secondary">
                        <button type="button" onclick="window.AdminProfile.copyText(document.getElementById('profile-admin-id-input').value, 'Admin ID')" class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-surface-subtle hover:bg-surface-hover border border-border-subtle font-mono text-[11px] font-medium text-text-primary transition-colors cursor-pointer" title="Copy Admin ID">
                            <span class="material-symbols-outlined text-[13px] text-text-secondary" aria-hidden="true">badge</span>
                            <span id="hero-admin-id-text">ID: ---</span>
                            <span class="material-symbols-outlined text-[11px] opacity-40 hover:opacity-100" aria-hidden="true">content_copy</span>
                        </button>

                        <button type="button" onclick="window.AdminProfile.copyText(document.getElementById('profile-email-input').value, 'Email')" class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-surface-subtle hover:bg-surface-hover border border-border-subtle text-[11px] text-text-secondary hover:text-text-primary transition-colors cursor-pointer" title="Copy Email">
                            <span class="material-symbols-outlined text-[13px] text-text-secondary" aria-hidden="true">mail</span>
                            <span id="hero-email-text">admin@finzo.com</span>
                            <span class="material-symbols-outlined text-[11px] opacity-40 hover:opacity-100" aria-hidden="true">content_copy</span>
                        </button>

                        <span class="text-text-secondary hidden sm:inline">&bull;</span>
                        <span id="badge-sq-status" class="inline-flex items-center gap-1 text-[11px]">
                            <span class="material-symbols-outlined text-[13px]" aria-hidden="true">shield</span>
                            <span id="badge-sq-label">Recovery: Checking...</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Right: Account Induction Metadata -->
            <div class="flex flex-wrap md:flex-col items-center md:items-end justify-center gap-1.5 text-xs text-text-secondary pt-2 md:pt-0 border-t md:border-t-0 border-border-subtle">
                <div class="flex items-center gap-1.5">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-text-secondary">Inducted:</span>
                    <span class="font-semibold text-text-primary" id="hero-joined-date">—</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-text-secondary">Provisioned By:</span>
                    <span class="font-semibold text-text-primary" id="hero-added-by">System</span>
                </div>
                <!-- Hidden anchors to satisfy JS parity -->
                <span id="hero-security-score" class="hidden"></span>
                <span id="hero-session-status" class="hidden"></span>
            </div>

        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- 3. NAVIGATION TABS                                              -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <div class="flex items-center gap-1 border-b border-border-subtle pb-px overflow-x-auto custom-scrollbar" role="tablist">
        <button type="button" role="tab" aria-selected="true" data-tab="tab-overview" onclick="window.AdminProfile.switchTab('tab-overview')" class="profile-tab-btn flex items-center gap-2 px-4 py-2.5 rounded-t-lg text-xs font-bold uppercase tracking-wider transition-all border-b-2 border-zinc-900 dark:border-white text-zinc-900 dark:text-white bg-surface shadow-2xs cursor-pointer">
            <span class="material-symbols-outlined text-[16px]" aria-hidden="true">person</span>
            <span>Profile Details</span>
        </button>

        <button type="button" role="tab" aria-selected="false" data-tab="tab-security" onclick="window.AdminProfile.switchTab('tab-security')" class="profile-tab-btn flex items-center gap-2 px-4 py-2.5 rounded-t-lg text-xs font-bold uppercase tracking-wider transition-all border-b-2 border-transparent text-text-secondary hover:text-text-primary hover:bg-surface-hover cursor-pointer">
            <span class="material-symbols-outlined text-[16px]" aria-hidden="true">lock</span>
            <span>Security &amp; Credentials</span>
        </button>

        <button type="button" role="tab" aria-selected="false" data-tab="tab-permissions" onclick="window.AdminProfile.switchTab('tab-permissions')" class="profile-tab-btn flex items-center gap-2 px-4 py-2.5 rounded-t-lg text-xs font-bold uppercase tracking-wider transition-all border-b-2 border-transparent text-text-secondary hover:text-text-primary hover:bg-surface-hover cursor-pointer">
            <span class="material-symbols-outlined text-[16px]" aria-hidden="true">policy</span>
            <span>Roles &amp; Permissions</span>
        </button>

        <button type="button" role="tab" aria-selected="false" data-tab="tab-activity" onclick="window.AdminProfile.switchTab('tab-activity')" class="profile-tab-btn flex items-center gap-2 px-4 py-2.5 rounded-t-lg text-xs font-bold uppercase tracking-wider transition-all border-b-2 border-transparent text-text-secondary hover:text-text-primary hover:bg-surface-hover cursor-pointer">
            <span class="material-symbols-outlined text-[16px]" aria-hidden="true">history</span>
            <span>Activity Log</span>
        </button>

        <button type="button" role="tab" aria-selected="false" id="tab-btn-directory" data-tab="tab-directory" onclick="window.AdminProfile.switchTab('tab-directory')" class="profile-tab-btn hidden items-center gap-2 px-4 py-2.5 rounded-t-lg text-xs font-bold uppercase tracking-wider transition-all border-b-2 border-transparent text-text-secondary hover:text-text-primary hover:bg-surface-hover cursor-pointer">
            <span class="material-symbols-outlined text-[16px]" aria-hidden="true">group</span>
            <span>Admin Management</span>
        </button>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- 4. TAB 1: PROFILE DETAILS                                       -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <div id="tab-overview" class="tab-panel space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left: Personal Information Form (2 cols) -->
            <div class="lg:col-span-2 space-y-6">
                <div class="ds-card p-5 sm:p-6 bg-surface border border-border-subtle rounded-xl shadow-xs">
                    <div class="flex items-center justify-between pb-3.5 mb-4 border-b border-border-subtle">
                        <div>
                            <h3 class="font-headline font-bold text-sm text-text-primary flex items-center gap-2">
                                <span class="material-symbols-outlined text-text-secondary text-[18px]" aria-hidden="true">badge</span>
                                Personal Information
                            </h3>
                            <p class="text-xs text-text-secondary mt-0.5">Your official administrator profile details.</p>
                        </div>
                        <span class="px-2 py-0.5 bg-surface-subtle text-text-secondary text-[10px] font-mono font-bold rounded border border-border-subtle" id="meta-role-label">System Role</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="profile-admin-id-input" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Admin ID</label>
                            <div class="fin-input-wrapper">
                                <span class="material-symbols-outlined input-icon-left text-text-secondary">fingerprint</span>
                                <input type="text" id="profile-admin-id-input" class="fin-input has-icon-left opacity-75 bg-surface-subtle cursor-not-allowed font-mono text-text-primary text-xs" readonly>
                            </div>
                        </div>

                        <div>
                            <label for="profile-name-input" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Full Name *</label>
                            <div class="fin-input-wrapper">
                                <span class="material-symbols-outlined input-icon-left text-text-secondary">person</span>
                                <input type="text" id="profile-name-input" class="fin-input has-icon-left bg-surface text-text-primary border-border-subtle text-xs" placeholder="Full Legal Name">
                            </div>
                        </div>

                        <div>
                            <label for="profile-dob-input" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Date of Birth</label>
                            <div class="fin-input-wrapper">
                                <span class="material-symbols-outlined input-icon-left text-text-secondary">calendar_today</span>
                                <input type="date" id="profile-dob-input" class="fin-input has-icon-left bg-surface text-text-primary border-border-subtle text-xs">
                            </div>
                        </div>

                        <div>
                            <label class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Gender</label>
                            <div class="finzo-dropdown" id="dropdown-gender">
                                <input type="hidden" id="profile-gender-input" value="">
                                <button type="button" class="finzo-dropdown-trigger bg-surface text-text-primary border border-border-subtle rounded-lg px-3.5 py-2 flex items-center justify-between w-full text-xs font-semibold hover:border-text-primary transition-colors focus:ring-2 focus:ring-text-primary focus:outline-none" aria-haspopup="listbox" aria-expanded="false">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-text-secondary text-[16px]" aria-hidden="true">wc</span>
                                        <span class="dropdown-label text-text-primary">Select Gender</span>
                                    </div>
                                    <span class="material-symbols-outlined text-[16px] chevron transition-transform text-text-secondary" aria-hidden="true">expand_more</span>
                                </button>
                                <div class="finzo-dropdown-menu bg-surface dark:bg-[#18181B] border border-border-subtle rounded-lg shadow-xl py-1 z-50" role="listbox">
                                    <div class="finzo-dropdown-option px-3.5 py-1.5 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer transition-colors" data-value="Male">Male</div>
                                    <div class="finzo-dropdown-option px-3.5 py-1.5 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer transition-colors" data-value="Female">Female</div>
                                    <div class="finzo-dropdown-option px-3.5 py-1.5 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer transition-colors" data-value="Other">Other</div>
                                    <div class="finzo-dropdown-option px-3.5 py-1.5 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer transition-colors" data-value="Prefer not to say">Prefer not to say</div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="profile-phone-input" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Phone Number</label>
                            <div class="fin-input-wrapper">
                                <span class="material-symbols-outlined input-icon-left text-text-secondary">call</span>
                                <input type="text" id="profile-phone-input" class="fin-input has-icon-left bg-surface text-text-primary border-border-subtle text-xs" placeholder="10-digit mobile number">
                            </div>
                        </div>

                        <div>
                            <label for="profile-email-input" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Email Address (Read-only)</label>
                            <div class="fin-input-wrapper">
                                <span class="material-symbols-outlined input-icon-left text-text-secondary">mail</span>
                                <input type="email" id="profile-email-input" class="fin-input has-icon-left opacity-75 bg-surface-subtle cursor-not-allowed text-text-primary text-xs" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-border-subtle flex items-center justify-between">
                        <span class="text-xs text-text-secondary">Changes are saved directly to database</span>
                        <button type="button" onclick="window.AdminProfile.saveProfile()" class="px-5 py-2 rounded-lg text-xs font-bold uppercase tracking-wider inline-flex items-center gap-1.5 bg-zinc-900 text-white hover:bg-black dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100 shadow-xs transition-all cursor-pointer">
                            <span class="material-symbols-outlined text-[16px]" aria-hidden="true">save</span>
                            <span>Save Changes</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right: Account Overview & Session Telemetry (1 col) -->
            <div class="space-y-6">
                <!-- Account Security Parameters -->
                <div class="ds-card p-5 bg-surface border border-border-subtle rounded-xl shadow-xs space-y-3">
                    <div class="flex items-center gap-2.5 pb-3 border-b border-border-subtle">
                        <div class="w-8 h-8 rounded-lg bg-surface-subtle text-text-primary flex items-center justify-center border border-border-subtle">
                            <span class="material-symbols-outlined text-[17px]" aria-hidden="true">security</span>
                        </div>
                        <div>
                            <h3 class="font-headline font-bold text-sm text-text-primary">Account Overview</h3>
                            <p class="text-[10px] text-text-secondary">Role scope and session details</p>
                        </div>
                    </div>

                    <div class="space-y-2.5 text-xs">
                        <div class="flex justify-between items-center py-1 border-b border-border-subtle">
                            <span class="text-text-secondary">Role</span>
                            <span class="font-bold text-text-primary" id="sec-role-scope">Super Administrator</span>
                        </div>
                        <div class="flex justify-between items-center py-1 border-b border-border-subtle">
                            <span class="text-text-secondary">Last Login</span>
                            <span class="font-semibold text-text-primary" id="sec-last-login">—</span>
                        </div>
                        <div class="flex justify-between items-center py-1 border-b border-border-subtle">
                            <span class="text-text-secondary">Last Login IP</span>
                            <span class="font-mono font-bold text-text-primary" id="sec-last-ip">—</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-text-secondary">Password Last Changed</span>
                            <span class="font-semibold text-text-primary" id="sec-last-pwd">—</span>
                        </div>
                    </div>
                </div>

                <!-- Two-Factor Authentication -->
                <div class="ds-card p-5 bg-surface border border-border-subtle rounded-xl shadow-xs space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-text-primary">Two-Factor Authentication</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="sec-2fa-toggle" class="sr-only peer" onchange="window.AdminProfile.toggle2FA(this.checked)">
                            <div class="w-10 h-5 bg-zinc-300 dark:bg-zinc-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white dark:after:bg-zinc-900 after:border-border-subtle after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-zinc-900 dark:peer-checked:bg-white dark:peer-checked:after:bg-zinc-900"></div>
                        </label>
                    </div>
                    <p class="text-xs text-text-secondary leading-relaxed">Require two-factor verification on logins and sensitive administrative actions.</p>
                    <div class="pt-1">
                        <span class="text-[10px] font-mono font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-surface-subtle border border-border-subtle text-text-secondary" id="sec-2fa-label">DISABLED</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- 5. TAB 2: SECURITY & CREDENTIALS                                -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <div id="tab-security" class="tab-panel hidden space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Change Password Card -->
            <div class="ds-card p-5 sm:p-6 bg-surface border border-border-subtle rounded-xl shadow-xs space-y-4">
                <div class="flex items-center gap-2.5 pb-3 border-b border-border-subtle">
                    <div class="w-8 h-8 rounded-lg bg-surface-subtle text-text-primary flex items-center justify-center border border-border-subtle">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">password</span>
                    </div>
                    <div>
                        <h3 class="font-headline font-bold text-sm text-text-primary">Change Password</h3>
                        <p class="text-xs text-text-secondary mt-0.5">Regular password updates protect against credential compromise.</p>
                    </div>
                </div>

                <div class="space-y-3.5">
                    <div>
                        <label for="curr-pw" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Current Password *</label>
                        <div class="fin-input-wrapper relative">
                            <span class="material-symbols-outlined input-icon-left text-text-secondary">lock</span>
                            <input type="password" id="curr-pw" class="fin-input has-icon-left pr-10 bg-surface text-text-primary border-border-subtle text-xs" placeholder="Enter current password">
                            <button type="button" onclick="window.AdminProfile.togglePW('curr-pw')" class="absolute right-3 text-text-secondary hover:text-text-primary focus:outline-none cursor-pointer" title="Toggle Visibility">
                                <span class="material-symbols-outlined text-[17px]">visibility</span>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label for="new-pw" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">New Password *</label>
                        <div class="fin-input-wrapper relative">
                            <span class="material-symbols-outlined input-icon-left text-text-secondary">key</span>
                            <input type="password" id="new-pw" class="fin-input has-icon-left pr-10 bg-surface text-text-primary border-border-subtle text-xs" placeholder="Minimum 8 characters" oninput="window.AdminProfile.checkStrength(this.value)">
                            <button type="button" onclick="window.AdminProfile.togglePW('new-pw')" class="absolute right-3 text-text-secondary hover:text-text-primary focus:outline-none cursor-pointer" title="Toggle Visibility">
                                <span class="material-symbols-outlined text-[17px]">visibility</span>
                            </button>
                        </div>
                        <div class="mt-1.5">
                            <div class="password-strength-bar w-full h-1 bg-surface-subtle rounded-full overflow-hidden">
                                <div class="password-strength-fill h-full transition-all duration-300 rounded-full" id="pw-bar" style="width:0%; background:var(--color-primary-base);"></div>
                            </div>
                            <div class="flex justify-between items-center mt-1">
                                <span class="text-[10px] text-text-secondary">Password Strength</span>
                                <span class="text-[10px] font-bold uppercase tracking-wider" id="pw-text"></span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="conf-pw" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Confirm New Password *</label>
                        <div class="fin-input-wrapper relative">
                            <span class="material-symbols-outlined input-icon-left text-text-secondary">check_circle</span>
                            <input type="password" id="conf-pw" class="fin-input has-icon-left pr-10 bg-surface text-text-primary border-border-subtle text-xs" placeholder="Confirm new password">
                            <button type="button" onclick="window.AdminProfile.togglePW('conf-pw')" class="absolute right-3 text-text-secondary hover:text-text-primary focus:outline-none cursor-pointer" title="Toggle Visibility">
                                <span class="material-symbols-outlined text-[17px]">visibility</span>
                            </button>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="button" onclick="window.AdminProfile.updatePassword()" class="w-full py-2.5 rounded-lg text-xs font-bold uppercase tracking-wider bg-zinc-900 text-white hover:bg-black dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100 shadow-xs transition-all cursor-pointer">
                            Update Password
                        </button>
                    </div>
                </div>
            </div>

            <!-- Security Question Card -->
            <div class="ds-card p-5 sm:p-6 bg-surface border border-border-subtle rounded-xl shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-border-subtle">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-surface-subtle text-text-primary flex items-center justify-center border border-border-subtle">
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">quiz</span>
                        </div>
                        <div>
                            <h3 class="font-headline font-bold text-sm text-text-primary">Security Question</h3>
                            <p class="text-xs text-text-secondary mt-0.5">Used for identity challenge if your account is locked.</p>
                        </div>
                    </div>
                    <div id="sq-status-container"></div>
                </div>

                <p class="text-xs text-text-secondary leading-relaxed">Configure a secret question and answer to verify your identity and unlock your account after failed login attempts.</p>

                <form id="sq-setup-form" onsubmit="window.AdminProfile.saveSecurityQuestion(event)" class="space-y-3.5">
                    <div>
                        <label for="sq-question" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Secret Question *</label>
                        <input type="text" id="sq-question" class="fin-input bg-surface text-text-primary border-border-subtle text-xs" placeholder="e.g. In what city were you born?" minlength="10" required>
                    </div>
                    <div>
                        <label for="sq-answer" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Secret Answer *</label>
                        <input type="password" id="sq-answer" class="fin-input bg-surface text-text-primary border-border-subtle text-xs" placeholder="Enter confidential answer" minlength="3" required>
                    </div>
                    <div class="pt-2 border-t border-border-subtle">
                        <label for="sq-curr-pw" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Current Password (to verify changes) *</label>
                        <input type="password" id="sq-curr-pw" class="fin-input bg-surface text-text-primary border-border-subtle text-xs" placeholder="Enter current password" required>
                    </div>
                    <div class="pt-2 flex justify-end">
                        <button type="submit" id="btn-save-sq" class="px-5 py-2 rounded-lg text-xs font-bold uppercase tracking-wider bg-zinc-900 text-white hover:bg-black dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100 shadow-xs transition-all cursor-pointer">
                            <span id="sq-btn-text">Save Security Question</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- 6. TAB 3: ROLES & PERMISSIONS MATRIX (REAL BANKING FEATURE)     -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <div id="tab-permissions" class="tab-panel hidden space-y-6">
        <div class="ds-card p-5 sm:p-6 bg-surface border border-border-subtle rounded-xl shadow-xs space-y-5">
            <div class="flex items-center justify-between pb-3.5 border-b border-border-subtle">
                <div>
                    <h3 class="font-headline font-bold text-sm text-text-primary flex items-center gap-2">
                        <span class="material-symbols-outlined text-text-secondary text-[18px]" aria-hidden="true">policy</span>
                        Assigned Operational Permissions
                    </h3>
                    <p class="text-xs text-text-secondary mt-0.5">Overview of active system access rights and operational boundaries granted to your role.</p>
                </div>
                <span class="px-2.5 py-1 rounded bg-surface-subtle border border-border-subtle text-[11px] font-bold text-text-primary">
                    Finzo RBAC Policy
                </span>
            </div>

            <!-- Permissions Category Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                
                <!-- Category 1: Customer Account Operations -->
                <div class="p-4 rounded-xl bg-surface-subtle border border-border-subtle space-y-3">
                    <div class="flex items-center gap-2 text-text-primary">
                        <span class="material-symbols-outlined text-[18px]">account_balance</span>
                        <span class="text-xs font-bold uppercase tracking-wider">Customer Accounts</span>
                    </div>
                    <div class="space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-text-secondary">View Customer Accounts</span>
                            <span class="text-emerald-600 dark:text-emerald-400 font-bold text-[11px]">Granted</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-text-secondary">Review Pending KYC</span>
                            <span class="text-emerald-600 dark:text-emerald-400 font-bold text-[11px]">Granted</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-text-secondary">Suspend / Lock Accounts</span>
                            <span class="text-emerald-600 dark:text-emerald-400 font-bold text-[11px]">Granted</span>
                        </div>
                    </div>
                </div>

                <!-- Category 2: Staff & Branch Operations -->
                <div class="p-4 rounded-xl bg-surface-subtle border border-border-subtle space-y-3">
                    <div class="flex items-center gap-2 text-text-primary">
                        <span class="material-symbols-outlined text-[18px]">store</span>
                        <span class="text-xs font-bold uppercase tracking-wider">Staff &amp; Branches</span>
                    </div>
                    <div class="space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-text-secondary">View Branch Records</span>
                            <span class="text-emerald-600 dark:text-emerald-400 font-bold text-[11px]">Granted</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-text-secondary">Staff Oversight &amp; Roster</span>
                            <span class="text-emerald-600 dark:text-emerald-400 font-bold text-[11px]">Granted</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-text-secondary">Create / Close Branches</span>
                            <span class="text-text-secondary font-medium text-[11px]">Admin / Super Admin</span>
                        </div>
                    </div>
                </div>

                <!-- Category 3: General Ledger & Finance -->
                <div class="p-4 rounded-xl bg-surface-subtle border border-border-subtle space-y-3">
                    <div class="flex items-center gap-2 text-text-primary">
                        <span class="material-symbols-outlined text-[18px]">receipt_long</span>
                        <span class="text-xs font-bold uppercase tracking-wider">General Ledger</span>
                    </div>
                    <div class="space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-text-secondary">Inspect Transactions</span>
                            <span class="text-emerald-600 dark:text-emerald-400 font-bold text-[11px]">Granted</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-text-secondary">General Ledger Auditing</span>
                            <span class="text-emerald-600 dark:text-emerald-400 font-bold text-[11px]">Granted</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-text-secondary">Financial Reporting</span>
                            <span class="text-emerald-600 dark:text-emerald-400 font-bold text-[11px]">Granted</span>
                        </div>
                    </div>
                </div>

                <!-- Category 4: Products & Services -->
                <div class="p-4 rounded-xl bg-surface-subtle border border-border-subtle space-y-3">
                    <div class="flex items-center gap-2 text-text-primary">
                        <span class="material-symbols-outlined text-[18px]">credit_card</span>
                        <span class="text-xs font-bold uppercase tracking-wider">Products &amp; Cards</span>
                    </div>
                    <div class="space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-text-secondary">Card Issuance Auditing</span>
                            <span class="text-emerald-600 dark:text-emerald-400 font-bold text-[11px]">Granted</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-text-secondary">Loan Applications Review</span>
                            <span class="text-emerald-600 dark:text-emerald-400 font-bold text-[11px]">Granted</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-text-secondary">Configure Interest Rates</span>
                            <span class="text-text-secondary font-medium text-[11px]">Admin / Super Admin</span>
                        </div>
                    </div>
                </div>

                <!-- Category 5: Audit & Security -->
                <div class="p-4 rounded-xl bg-surface-subtle border border-border-subtle space-y-3">
                    <div class="flex items-center gap-2 text-text-primary">
                        <span class="material-symbols-outlined text-[18px]">shield</span>
                        <span class="text-xs font-bold uppercase tracking-wider">Audit &amp; Security</span>
                    </div>
                    <div class="space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-text-secondary">System Audit Trail Access</span>
                            <span class="text-emerald-600 dark:text-emerald-400 font-bold text-[11px]">Granted</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-text-secondary">Security Question Self-Reset</span>
                            <span class="text-emerald-600 dark:text-emerald-400 font-bold text-[11px]">Granted</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-text-secondary">Export Audit CSV</span>
                            <span class="text-emerald-600 dark:text-emerald-400 font-bold text-[11px]">Granted</span>
                        </div>
                    </div>
                </div>

                <!-- Category 6: Governance & Administration -->
                <div class="p-4 rounded-xl bg-surface-subtle border border-border-subtle space-y-3">
                    <div class="flex items-center gap-2 text-text-primary">
                        <span class="material-symbols-outlined text-[18px]">admin_panel_settings</span>
                        <span class="text-xs font-bold uppercase tracking-wider">Admin Governance</span>
                    </div>
                    <div class="space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-text-secondary">Unlock Admin Accounts</span>
                            <span class="text-purple-600 dark:text-purple-400 font-bold text-[11px]">Super Admin</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-text-secondary">Provision New Admin</span>
                            <span class="text-purple-600 dark:text-purple-400 font-bold text-[11px]">Super Admin</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-text-secondary">Manage System Roles</span>
                            <span class="text-purple-600 dark:text-purple-400 font-bold text-[11px]">Super Admin</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- 7. TAB 4: ACTIVITY LOG                                          -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <div id="tab-activity" class="tab-panel hidden space-y-6">
        <div class="ds-card bg-surface border border-border-subtle rounded-xl shadow-xs overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-border-subtle flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-surface-subtle">
                <div>
                    <h3 class="font-headline font-bold text-sm text-text-primary flex items-center gap-2">
                        <span class="material-symbols-outlined text-text-secondary text-[18px]" aria-hidden="true">receipt_long</span>
                        Personal Activity Log
                    </h3>
                    <p class="text-xs text-text-secondary mt-0.5">Chronological record of your administrative operations and logins.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span id="activity-count-badge" class="px-2.5 py-1 bg-surface text-text-secondary text-[10px] font-mono font-bold rounded border border-border-subtle">0 Logs</span>
                </div>
            </div>

            <!-- Search & Filters -->
            <div class="p-4 border-b border-border-subtle bg-surface flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-3 flex-1">
                    <div class="fin-input-wrapper flex-1 min-w-[200px]">
                        <span class="material-symbols-outlined input-icon-left text-text-secondary">search</span>
                        <input type="text" id="activity-search-input" placeholder="Search logs by action, IP, or details..." class="fin-input has-icon-left bg-surface text-text-primary border-border-subtle rounded-lg text-xs">
                    </div>

                    <div class="flex items-center gap-1.5">
                        <span class="text-[10px] font-bold text-text-secondary uppercase tracking-wider">From:</span>
                        <input type="date" id="activity-from-date" class="fin-input !h-9 bg-surface text-text-primary border-border-subtle rounded-lg text-xs px-2.5">
                    </div>

                    <div class="flex items-center gap-1.5">
                        <span class="text-[10px] font-bold text-text-secondary uppercase tracking-wider">To:</span>
                        <input type="date" id="activity-to-date" class="fin-input !h-9 bg-surface text-text-primary border-border-subtle rounded-lg text-xs px-2.5">
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" onclick="window.AdminProfile.resetActivityFilter()" class="px-3 py-2 rounded-lg text-xs font-semibold bg-surface text-text-primary hover:bg-surface-hover border border-border-subtle shadow-2xs cursor-pointer transition-colors" title="Reset Filters">
                        <span class="material-symbols-outlined text-[15px]" aria-hidden="true">restart_alt</span>
                    </button>
                    <button type="button" onclick="window.AdminProfile.exportActivityCSV()" class="px-3.5 py-2 text-xs rounded-lg inline-flex items-center gap-1.5 font-bold uppercase tracking-wider bg-surface text-text-primary hover:bg-surface-hover border border-border-subtle shadow-2xs cursor-pointer transition-colors">
                        <span class="material-symbols-outlined text-[15px]" aria-hidden="true">download</span>
                        <span>Export CSV</span>
                    </button>
                </div>
            </div>

            <!-- Activity Table -->
            <div class="ds-table-wrapper w-full border-0 shadow-none rounded-none bg-transparent overflow-x-auto">
                <table class="ds-table w-full text-left min-w-[700px]">
                    <thead class="bg-surface-subtle border-b border-border-subtle">
                        <tr>
                            <th class="py-2.5 px-5 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Action / Operation</th>
                            <th class="py-2.5 px-5 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Details</th>
                            <th class="py-2.5 px-5 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">IP Address</th>
                            <th class="py-2.5 px-5 text-right text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Date &amp; Time</th>
                        </tr>
                    </thead>
                    <tbody id="activity-table-body" class="divide-y divide-border-subtle text-xs">
                        <tr><td colspan="4" class="text-center italic opacity-50 py-10 text-xs">Loading activity logs...</td></tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-4 border-t border-border-subtle flex flex-col sm:flex-row items-center justify-between gap-3 bg-surface-subtle" id="activity-pagination-container">
                <div class="text-[11px] font-bold text-text-secondary uppercase tracking-wider" id="activity-pagination-info">Showing 0 to 0 of 0</div>
                <div class="flex gap-1.5">
                    <button id="activity-prev-btn" onclick="window.AdminProfile.prevActivityPage()" class="px-3 py-1 text-xs rounded-md font-bold bg-surface text-text-primary hover:bg-surface-hover border border-border-subtle shadow-2xs cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed transition-colors">Previous</button>
                    <button id="activity-next-btn" onclick="window.AdminProfile.nextActivityPage()" class="px-3 py-1 text-xs rounded-md font-bold bg-surface text-text-primary hover:bg-surface-hover border border-border-subtle shadow-2xs cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed transition-colors">Next</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- 8. TAB 5: ADMIN MANAGEMENT (Super Admin Only)                    -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <div id="tab-directory" class="tab-panel hidden space-y-6">
        
        <!-- LOCKED ADMIN ACCOUNTS -->
        <div id="locked-admins-container" class="ds-card bg-surface border border-danger-border rounded-xl shadow-xs overflow-hidden hidden">
            <div class="p-4 border-b border-danger-border flex justify-between items-center bg-danger-bg">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-danger/20 text-danger flex items-center justify-center">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">lock_clock</span>
                    </div>
                    <div>
                        <h3 class="font-headline font-bold text-sm text-danger">Locked Administrator Accounts</h3>
                        <p class="text-[11px] text-danger/80 mt-0.5">Accounts locked due to repeated failed login attempts.</p>
                    </div>
                </div>
                <span id="locked-count-badge" class="px-2 py-0.5 bg-danger/20 text-danger text-[10px] font-bold rounded uppercase tracking-wider border border-danger/30">0 Locked</span>
            </div>
            <div class="ds-table-wrapper w-full border-0 shadow-none rounded-none bg-transparent overflow-x-auto">
                <table class="ds-table w-full text-left min-w-[800px]">
                    <thead class="bg-surface-subtle border-b border-border-subtle">
                        <tr>
                            <th class="py-2.5 px-5 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Administrator</th>
                            <th class="py-2.5 px-4 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Role</th>
                            <th class="py-2.5 px-4 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Locked At</th>
                            <th class="py-2.5 px-4 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Lock Reason</th>
                            <th class="py-2.5 px-4 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Failed Attempts</th>
                            <th class="py-2.5 px-4 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Security Question</th>
                            <th class="py-2.5 px-4 text-right text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody id="locked-admins-body" class="divide-y divide-border-subtle text-xs"></tbody>
                </table>
            </div>
        </div>

        <!-- GLOBAL ADMIN DIRECTORY -->
        <div id="admin-management-container" class="ds-card bg-surface border border-border-subtle rounded-xl shadow-xs overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-border-subtle flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-surface-subtle">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-surface text-text-primary flex items-center justify-center border border-border-subtle shadow-2xs">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">group</span>
                    </div>
                    <div>
                        <h3 class="font-headline font-bold text-sm text-text-primary">Admin Directory</h3>
                        <p class="text-xs text-text-secondary mt-0.5">Manage administrator accounts and permissions.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2.5">
                    <span id="management-count-badge" class="px-2.5 py-1 bg-surface text-text-secondary text-[10px] font-mono font-bold rounded border border-border-subtle">0 Admins</span>
                    <button id="header-add-admin-btn" type="button" onclick="window.AdminProfile.showAddModal()" class="px-3.5 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider inline-flex items-center gap-1.5 bg-zinc-900 text-white hover:bg-black dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100 shadow-xs transition-all cursor-pointer">
                        <span class="material-symbols-outlined text-[15px]" aria-hidden="true">person_add</span>
                        <span>Add Admin</span>
                    </button>
                </div>
            </div>

            <!-- Directory Filters -->
            <div class="p-4 border-b border-border-subtle bg-surface flex flex-col sm:flex-row gap-3">
                <div class="fin-input-wrapper flex-1">
                    <span class="material-symbols-outlined input-icon-left text-text-secondary">search</span>
                    <input type="text" id="admin-search-input" placeholder="Search by name, email, or admin ID..." class="fin-input has-icon-left bg-surface text-text-primary border-border-subtle rounded-lg text-xs">
                </div>
                
                <div class="flex flex-wrap sm:flex-nowrap gap-2.5">
                    <div class="finzo-dropdown min-w-[140px]" id="dropdown-admin-status-filter">
                        <button type="button" class="finzo-dropdown-trigger bg-surface text-text-primary border border-border-subtle rounded-lg px-3 py-2 flex items-center justify-between w-full text-xs font-semibold hover:border-text-primary transition-colors focus:ring-2 focus:ring-text-primary focus:outline-none cursor-pointer" aria-haspopup="listbox" aria-expanded="false">
                            <div class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[15px] text-text-secondary" aria-hidden="true">toggle_on</span>
                                <span class="dropdown-label text-text-primary font-bold">All Statuses</span>
                            </div>
                            <span class="material-symbols-outlined text-[16px] chevron transition-transform text-text-secondary" aria-hidden="true">expand_more</span>
                        </button>
                        <div class="finzo-dropdown-menu bg-surface dark:bg-[#18181B] border border-border-subtle rounded-lg shadow-xl py-1 z-50 min-w-[140px]" role="listbox">
                            <div class="finzo-dropdown-option is-selected px-3.5 py-1.5 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer transition-colors" data-value="all">All Statuses</div>
                            <div class="finzo-dropdown-option px-3.5 py-1.5 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer transition-colors" data-value="active">Active</div>
                            <div class="finzo-dropdown-option px-3.5 py-1.5 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer transition-colors" data-value="suspended">Suspended</div>
                        </div>
                        <input type="hidden" id="admin-status-filter" value="all">
                    </div>

                    <div class="finzo-dropdown min-w-[140px]" id="dropdown-admin-role-filter">
                        <button type="button" class="finzo-dropdown-trigger bg-surface text-text-primary border border-border-subtle rounded-lg px-3 py-2 flex items-center justify-between w-full text-xs font-semibold hover:border-text-primary transition-colors focus:ring-2 focus:ring-text-primary focus:outline-none cursor-pointer" aria-haspopup="listbox" aria-expanded="false">
                            <div class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[15px] text-text-secondary" aria-hidden="true">shield_person</span>
                                <span class="dropdown-label text-text-primary font-bold">All Roles</span>
                            </div>
                            <span class="material-symbols-outlined text-[16px] chevron transition-transform text-text-secondary" aria-hidden="true">expand_more</span>
                        </button>
                        <div class="finzo-dropdown-menu bg-surface dark:bg-[#18181B] border border-border-subtle rounded-lg shadow-xl py-1 z-50 min-w-[140px]" role="listbox">
                            <div class="finzo-dropdown-option is-selected px-3.5 py-1.5 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer transition-colors" data-value="all">All Roles</div>
                            <div class="finzo-dropdown-option px-3.5 py-1.5 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer transition-colors" data-value="admin">Admin</div>
                            <div class="finzo-dropdown-option px-3.5 py-1.5 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer transition-colors" data-value="moderator">Moderator</div>
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
                            <th class="py-2.5 px-5 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">ID</th>
                            <th class="py-2.5 px-5 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Administrator</th>
                            <th class="py-2.5 px-5 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Role</th>
                            <th class="py-2.5 px-5 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Access Level</th>
                            <th class="py-2.5 px-5 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Status</th>
                            <th class="py-2.5 px-5 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Last Active</th>
                            <th class="py-2.5 px-5 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Created By</th>
                            <th class="py-2.5 px-5 text-right text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="admin-management-body" class="divide-y divide-border-subtle text-xs"></tbody>
                </table>
            </div>
        </div>

    </div>

</div>

<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- 9. MODALS                                                       -->
<!-- ═══════════════════════════════════════════════════════════════ -->

<!-- ADD ADMIN MODAL -->
<div id="add-admin-modal" class="fixed inset-0 z-[2000] hidden items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-xs transition-all" onclick="window.AdminProfile.closeAddModal()"></div>
    
    <div class="relative w-full max-w-2xl bg-surface text-text-primary rounded-xl overflow-hidden shadow-2xl flex flex-col max-h-[90vh] border border-border-subtle animate-modal-enter z-10">
        
        <!-- Header -->
        <div class="p-4 sm:p-5 border-b border-border-subtle flex justify-between items-center sticky top-0 bg-surface z-10 shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-surface-subtle text-text-primary flex items-center justify-center border border-border-subtle">
                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">person_add</span>
                </div>
                <div>
                    <h3 class="font-headline font-bold text-base text-text-primary tracking-tight">Create Admin Account</h3>
                    <p class="text-xs text-text-secondary mt-0.5">Add a new administrator to the system</p>
                </div>
            </div>
            <button type="button" onclick="window.AdminProfile.closeAddModal()" class="p-1 hover:bg-surface-hover rounded-md transition-colors text-text-secondary hover:text-text-primary cursor-pointer">
                <span class="material-symbols-outlined" aria-hidden="true">close</span>
            </button>
        </div>

        <!-- Body -->
        <div class="p-5 sm:p-6 overflow-y-auto flex-1 space-y-5 custom-scrollbar">
            
            <!-- 1: Personal Details -->
            <div>
                <div class="text-xs font-bold tracking-wider text-text-primary uppercase mb-3 py-1 border-b border-border-subtle flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm" aria-hidden="true">person</span>
                    1. Personal Details
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label for="add-admin-id" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Admin ID</label>
                        <input type="text" id="add-admin-id" class="fin-input opacity-70 bg-surface-subtle font-mono text-text-primary text-xs" readonly>
                    </div>
                    <div>
                        <label for="add-name" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Full Name *</label>
                        <input type="text" id="add-name" class="fin-input bg-surface text-text-primary text-xs" placeholder="e.g. John Doe">
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
                            <button type="button" class="finzo-dropdown-trigger bg-surface text-text-primary border border-border-subtle rounded-lg px-3 py-2 flex items-center justify-between w-full text-xs font-semibold hover:border-text-primary transition-colors focus:ring-2 focus:ring-text-primary focus:outline-none" aria-haspopup="listbox" aria-expanded="false">
                                <span class="dropdown-label text-text-primary">Select Gender</span>
                                <span class="material-symbols-outlined text-[16px] chevron transition-transform text-text-secondary" aria-hidden="true">expand_more</span>
                            </button>
                            <div class="finzo-dropdown-menu bg-surface dark:bg-[#18181B] border border-border-subtle rounded-lg shadow-xl py-1 z-50" role="listbox">
                                <div class="finzo-dropdown-option px-3.5 py-1.5 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer" data-value="Male">Male</div>
                                <div class="finzo-dropdown-option px-3.5 py-1.5 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer" data-value="Female">Female</div>
                                <div class="finzo-dropdown-option px-3.5 py-1.5 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer" data-value="Other">Other</div>
                                <div class="finzo-dropdown-option px-3.5 py-1.5 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer" data-value="Prefer not to say">Prefer not to say</div>
                            </div>
                        </div>
                        <span id="err-add-gender" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                    </div>
                    <div>
                        <label for="add-phone" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Phone Number *</label>
                        <input type="text" id="add-phone" class="fin-input bg-surface text-text-primary text-xs" placeholder="10-digit mobile number">
                        <span id="err-add-phone" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                    </div>
                    <div>
                        <label for="add-email" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Email Address *</label>
                        <input type="email" id="add-email" class="fin-input bg-surface text-text-primary text-xs" placeholder="admin@finzo.com">
                        <span id="err-add-email" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                    </div>
                </div>
            </div>

            <!-- 2: Credentials -->
            <div>
                <div class="text-xs font-bold tracking-wider text-text-primary uppercase mb-3 py-1 border-b border-border-subtle flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm" aria-hidden="true">key</span>
                    2. Login Credentials
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label for="add-pw" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Password *</label>
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

            <!-- 3: Security Question & Role -->
            <div>
                <div class="text-xs font-bold tracking-wider text-text-primary uppercase mb-3 py-1 border-b border-border-subtle flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm" aria-hidden="true">shield</span>
                    3. Security Question &amp; Role
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label for="add-seq-q" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Security Question *</label>
                        <input type="text" id="add-seq-q" class="fin-input bg-surface text-text-primary text-xs" placeholder="e.g. In what city were you born?">
                        <span id="err-add-seq-q" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                    </div>
                    <div>
                        <label for="add-seq-a" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Secret Answer *</label>
                        <input type="password" id="add-seq-a" class="fin-input bg-surface text-text-primary text-xs" placeholder="Answer">
                        <span id="err-add-seq-a" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                    </div>
                </div>

                <div class="mt-3.5 space-y-3.5">
                    <div>
                        <label class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Role *</label>
                        <div class="finzo-dropdown" id="dropdown-add-role">
                            <input type="hidden" id="add-role" value="" onchange="window.AdminProfile.updateRolePreview(this.value)">
                            <button type="button" class="finzo-dropdown-trigger bg-surface text-text-primary border border-border-subtle rounded-lg px-3 py-2 flex items-center justify-between w-full text-xs font-semibold hover:border-text-primary transition-colors focus:ring-2 focus:ring-text-primary focus:outline-none" aria-haspopup="listbox" aria-expanded="false">
                                <span class="dropdown-label text-text-primary">Select Role</span>
                                <span class="material-symbols-outlined text-[16px] chevron transition-transform text-text-secondary" aria-hidden="true">expand_more</span>
                            </button>
                            <div class="finzo-dropdown-menu bg-surface dark:bg-[#18181B] border border-border-subtle rounded-lg shadow-xl py-1 z-50" role="listbox">
                                <div class="finzo-dropdown-option px-3.5 py-1.5 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer" data-value="admin">Administrator &mdash; Full access</div>
                                <div class="finzo-dropdown-option px-3.5 py-1.5 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer" data-value="moderator">Moderator &mdash; Approvals &amp; KYC review</div>
                            </div>
                        </div>
                        <span id="err-add-role" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                    </div>

                    <div id="role-preview-box" class="p-3 rounded-lg bg-surface-subtle border border-border-subtle text-xs text-text-secondary hidden leading-relaxed"></div>

                    <div>
                        <label for="new-admin-photo" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Profile Photo (Optional)</label>
                        <input type="file" id="new-admin-photo" accept="image/jpeg, image/png, image/webp" class="fin-input bg-surface text-text-primary text-xs" onchange="window.AdminProfile.previewNewPhoto(this)">
                        <span id="err-add-photo" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                        <div class="mt-2.5 flex items-center gap-2.5">
                            <img id="new-avatar-preview" src="" class="w-10 h-10 rounded-lg object-cover hidden border border-border-subtle shadow-2xs" alt="Preview">
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Footer -->
        <div class="p-4 border-t border-border-subtle bg-surface-subtle flex justify-end gap-2.5 sticky bottom-0 z-10 shrink-0">
            <button type="button" onclick="window.AdminProfile.closeAddModal()" class="px-4 py-2 rounded-lg text-xs font-semibold bg-surface text-text-primary hover:bg-surface-hover border border-border-subtle cursor-pointer transition-colors">Cancel</button>
            <button type="button" id="add-admin-submit-btn" onclick="window.AdminProfile.submitAddAdmin()" class="px-5 py-2 rounded-lg text-xs font-bold uppercase tracking-wider flex items-center gap-1.5 bg-zinc-900 text-white hover:bg-black dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100 shadow-xs cursor-pointer transition-all">
                <span>Create Admin</span>
                <span class="material-symbols-outlined animate-spin text-[15px] hidden" aria-hidden="true">progress_activity</span>
            </button>
        </div>
    </div>
</div>

<!-- UNLOCK CONFIRMATION DIALOG -->
<div id="unlock-confirm-overlay" class="fixed inset-0 z-[2100] hidden items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-xs" onclick="window.AdminProfile.closeUnlockConfirm()"></div>
    <div class="relative w-full max-w-md bg-surface text-text-primary rounded-xl p-5 shadow-2xl border border-border-subtle z-10 space-y-3.5 animate-modal-enter">
        <div class="flex items-center gap-2.5 text-warning">
            <div class="w-9 h-9 rounded-lg bg-warning-bg text-warning flex items-center justify-center border border-warning-border">
                <span class="material-symbols-outlined text-lg" aria-hidden="true">lock_open</span>
            </div>
            <div>
                <h4 class="font-headline font-bold text-sm text-text-primary">Unlock Administrator Account</h4>
                <p class="text-xs text-text-secondary mt-0.5">Reset failed attempt counter and restore account access</p>
            </div>
        </div>
        <p id="unlock-confirm-msg" class="text-xs text-text-secondary leading-relaxed">Are you sure you want to restore access to this administrator account?</p>
        <div class="flex justify-end gap-2 pt-2.5 border-t border-border-subtle">
            <button type="button" onclick="window.AdminProfile.closeUnlockConfirm()" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-surface text-text-primary hover:bg-surface-hover border border-border-subtle cursor-pointer transition-colors">Cancel</button>
            <button type="button" id="confirm-unlock-btn" onclick="window.AdminProfile.confirmUnlock()" class="px-4 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider bg-zinc-900 text-white hover:bg-black dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100 shadow-xs cursor-pointer transition-all">Confirm Unlock</button>
        </div>
    </div>
</div>

<?php
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/profile.js"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>