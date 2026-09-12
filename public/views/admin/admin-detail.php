<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin — Admin Dossier';

if (!defined('FINZO_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}
\App\Shared\Security\SessionManager::start();

// Authentication Guard
$currentUser = \App\Shared\Security\Auth::user();
if (empty($currentUser)) {
    header('Location: /Finzo/public/views/auth/admin-login.php');
    exit;
}

$adminId = intval($_GET['id'] ?? 0);
$adminData = null;
$activities = [];
$totalActivities = 0;

if ($adminId > 0) {
    try {
        $container = \App\Shared\Core\Container::getInstance();
        $repo = $container->get(\App\Domain\Contracts\Repositories\AdminManagementRepositoryInterface::class);
        $adminData = $repo->getAdminDetail($adminId);
        if ($adminData) {
            $activities = $repo->getAdminActivity($adminData['admin_id'], 50);
            $totalActivities = $repo->getActivityCount($adminData['admin_id']);
        }
    } catch (\Throwable $e) {
        $adminData = null;
    }
}

// Prepare SSR display variables
$fullName     = htmlspecialchars($adminData['full_name'] ?? 'Admin Dossier', ENT_QUOTES, 'UTF-8');
$adminCode    = htmlspecialchars($adminData['admin_id'] ?? 'ADM---', ENT_QUOTES, 'UTF-8');
$email        = htmlspecialchars($adminData['email'] ?? 'Not Provided', ENT_QUOTES, 'UTF-8');
$phone        = htmlspecialchars($adminData['phone'] ?? 'Not Provided', ENT_QUOTES, 'UTF-8');
$gender       = htmlspecialchars($adminData['gender'] ?? 'Not Specified', ENT_QUOTES, 'UTF-8');
$dobRaw       = $adminData['date_of_birth'] ?? null;
$dobFormatted = !empty($dobRaw) ? date('d M Y', strtotime($dobRaw)) : 'Not Provided';
$ageStr       = '';
if (!empty($dobRaw)) {
    try {
        $ageVal = (new \DateTime($dobRaw))->diff(new \DateTime('today'))->y;
        if ($ageVal > 0) {
            $ageStr = " ({$ageVal} yrs)";
        }
    } catch (\Throwable $t) {}
}

$role         = $adminData['admin_role'] ?? 'admin';
$isSuper      = ($role === 'super_admin');
$isAdmin      = ($role === 'admin');
$isModerator  = ($role === 'moderator');

$roleLabel = match($role) {
    'super_admin' => 'Super Admin',
    'moderator'   => 'Moderator',
    default       => 'Admin'
};

$roleDescription = match($role) {
    'super_admin' => 'Platform Root Administrator',
    'moderator'   => 'Compliance & KYC Reviewer',
    default       => 'Operations & User Administrator'
};

$accessLevel  = $adminData['access_level'] ?? ($isModerator ? 'restricted' : 'full');
$isFullAccess = ($accessLevel === 'full');

$status       = $adminData['status'] ?? 'active';
$isActive     = ($status === 'active');

$profilePhoto = !empty($adminData['profile_photo']) ? $adminData['profile_photo'] : null;
$initials     = !empty($adminData['full_name'])
    ? strtoupper(substr(implode('', array_map(fn($n) => $n[0] ?? '', explode(' ', $adminData['full_name']))), 0, 2))
    : 'AD';

$lastLoginAt    = !empty($adminData['last_login_at']) ? date('d M Y, h:i A', strtotime($adminData['last_login_at'])) : 'Never';
$lastLoginIp    = htmlspecialchars($adminData['last_login_ip'] ?? 'No Record', ENT_QUOTES, 'UTF-8');
$pwdChangedAt   = !empty($adminData['last_password_changed']) ? date('d M Y, h:i A', strtotime($adminData['last_password_changed'])) : 'Never';
$failedAttempts = intval($adminData['failed_attempts'] ?? 0);
$twoFaEnabled   = intval($adminData['two_fa_enabled'] ?? 0) === 1;
$createdByName  = htmlspecialchars($adminData['created_by_name'] ?? 'System', ENT_QUOTES, 'UTF-8');
$joinedDate     = !empty($adminData['created_at']) ? date('d M Y', strtotime($adminData['created_at'])) : '—';

ob_start();
?>

<script>
  window.__INITIAL_ADMIN__ = <?= json_encode($adminData ?? new \stdClass(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  window.__INITIAL_ACTIVITIES__ = <?= json_encode($activities ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>

<div class="space-y-6">
    
    <!-- ═══════════════════════════════════════════ -->
    <!-- 1. BREADCRUMBS & TOP BAR                    -->
    <!-- ═══════════════════════════════════════════ -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 relative z-10">
        <div class="flex items-center gap-4">
            <a href="admin-profile.php" class="w-10 h-10 rounded-xl bg-surface border border-border-subtle hover:bg-surface-hover flex items-center justify-center text-text-secondary hover:text-text-primary transition-colors shadow-sm cursor-pointer shrink-0" title="Back to Directory">
                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_back</span>
            </a>
            <div>
                <h1 class="text-2xl font-headline font-black text-text-primary tracking-tight">Admin Dossier</h1>
                <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-1">Official platform administrator record and governance profile</p>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════ -->
    <!-- 2. EXECUTIVE PROFILE HEADER CARD            -->
    <!-- ═══════════════════════════════════════════ -->
    <div class="ds-card p-6 bg-surface border border-border-subtle rounded-3xl shadow-sm relative overflow-hidden">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            
            <!-- Left: Avatar & Identity Details -->
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5 text-center sm:text-left">
                <!-- Avatar with status indicator -->
                <div class="relative shrink-0">
                    <div id="admin-photo-container" class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl overflow-hidden ring-4 ring-primary/10 shadow-md bg-surface-subtle flex items-center justify-center border border-border-subtle">
                        <?php if ($profilePhoto): ?>
                            <img src="<?= htmlspecialchars($profilePhoto) ?>?v=<?= time() ?>" class="w-full h-full object-cover" alt="<?= $fullName ?>" onerror="this.outerHTML='<span id=\'photo-initials\' class=\'text-2xl sm:text-3xl font-black text-text-secondary\'><?= $initials ?></span>'">
                        <?php else: ?>
                            <span id="photo-initials" class="text-2xl sm:text-3xl font-black text-text-secondary"><?= $initials ?></span>
                        <?php endif; ?>
                    </div>
                    <!-- Live status dot -->
                    <span class="absolute -top-1 -right-1 flex h-3.5 w-3.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full <?= $isActive ? 'bg-emerald-400 opacity-75' : 'bg-rose-400 opacity-75' ?>"></span>
                        <span class="relative inline-flex rounded-full h-3.5 w-3.5 <?= $isActive ? 'bg-emerald-500' : 'bg-rose-500' ?> border-2 border-surface"></span>
                    </span>
                </div>

                <!-- Identity info -->
                <div class="space-y-2">
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2.5">
                        <h2 id="info-name" class="text-xl sm:text-2xl font-black tracking-tight text-text-primary"><?= $fullName ?></h2>
                        
                        <!-- Role Badge -->
                        <span id="info-role-badge" class="px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-widest <?= $isModerator ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20' : ($isSuper ? 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20' : 'bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20') ?>">
                            <?= $roleLabel ?>
                        </span>

                        <!-- Status Badge -->
                        <span id="header-status-badge" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-widest <?= $isActive ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20' ?>">
                            <span class="w-1.5 h-1.5 rounded-full <?= $isActive ? 'bg-emerald-500' : 'bg-rose-500' ?>"></span>
                            <span><?= $isActive ? 'Active' : 'Suspended' ?></span>
                        </span>
                    </div>

                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-y-1 gap-x-4 text-xs text-text-secondary font-medium">
                        <!-- Admin ID Chip with copy button -->
                        <button onclick="window.AdminDetail.copyText('<?= $adminCode ?>', 'Admin ID')" class="inline-flex items-center gap-1 font-mono font-bold text-text-primary hover:text-primary transition-colors cursor-pointer" title="Click to copy Admin ID">
                            <span>ID:</span>
                            <span id="info-admin-id" class="font-bold"><?= $adminCode ?></span>
                            <span class="material-symbols-outlined text-[13px] text-text-secondary">content_copy</span>
                        </button>
                        <span>&bull;</span>
                        <span>Joined: <strong id="info-joined-date" class="text-text-primary font-bold"><?= $joinedDate ?></strong></span>
                        <span>&bull;</span>
                        <span>Added by: <strong id="hero-created-by" class="text-text-primary font-bold"><?= $createdByName ?></strong></span>
                    </div>
                </div>
            </div>

            <!-- Right: Action Buttons Group -->
            <div class="flex flex-wrap items-center justify-center sm:justify-end gap-2.5 shrink-0 pt-2 lg:pt-0 border-t lg:border-t-0 border-border-subtle">
                <!-- Suspend / Activate Button -->
                <button id="action-suspend-btn" onclick="window.AdminDetail.toggleStatus()" class="px-3.5 py-2 rounded-xl text-xs font-black uppercase tracking-wider flex items-center gap-1.5 transition-all cursor-pointer shadow-sm <?= $isActive ? 'bg-amber-500/10 hover:bg-amber-500 text-amber-600 hover:text-white border border-amber-500/20' : 'bg-emerald-500/10 hover:bg-emerald-600 text-emerald-600 hover:text-white border border-emerald-500/20' ?>">
                    <span id="suspend-icon" class="material-symbols-outlined text-[16px]"><?= $isActive ? 'block' : 'check_circle' ?></span>
                    <span id="suspend-label"><?= $isActive ? 'Suspend' : 'Activate' ?></span>
                </button>

                <!-- Reset Password Button -->
                <button onclick="window.AdminDetail.showResetModal()" class="btn btn-secondary px-3.5 py-2 text-xs font-black uppercase tracking-wider flex items-center gap-1.5 cursor-pointer shadow-sm">
                    <span class="material-symbols-outlined text-[16px]">lock_reset</span>
                    <span>Reset Password</span>
                </button>

                <!-- Delete Button -->
                <button onclick="window.AdminDetail.showDeleteModal()" class="px-3.5 py-2 rounded-xl text-xs font-black uppercase tracking-wider flex items-center gap-1.5 transition-all cursor-pointer shadow-sm bg-rose-500/10 hover:bg-rose-600 text-rose-600 hover:text-white border border-rose-500/20">
                    <span class="material-symbols-outlined text-[16px]">delete</span>
                    <span>Delete</span>
                </button>
            </div>

        </div>
    </div>

    <!-- ═══════════════════════════════════════════ -->
    <!-- 3. ESSENTIAL DETAILS (3 CLEAN TILES)        -->
    <!-- ═══════════════════════════════════════════ -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        
        <!-- Tile 1: Contact & Personal -->
        <div class="ds-card p-5 bg-surface border border-border-subtle rounded-2xl shadow-sm space-y-3.5">
            <div class="flex items-center justify-between pb-2 border-b border-border-subtle">
                <h3 class="text-xs font-black uppercase tracking-widest text-text-primary flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-primary text-[16px]">contact_mail</span>
                    <span>Contact Details</span>
                </h3>
            </div>
            
            <div class="space-y-2.5 text-xs">
                <div>
                    <label class="text-[10px] font-black text-text-secondary uppercase tracking-widest block mb-0.5">Email Address</label>
                    <div class="flex items-center justify-between gap-1">
                        <span id="info-email" class="font-bold text-text-primary truncate" title="<?= $email ?>"><?= $email ?></span>
                        <button onclick="window.AdminDetail.copyText('<?= $email ?>', 'Email')" class="text-text-secondary hover:text-text-primary transition-colors cursor-pointer shrink-0" title="Copy email">
                            <span class="material-symbols-outlined text-[14px]">content_copy</span>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="text-[10px] font-black text-text-secondary uppercase tracking-widest block mb-0.5">Phone Number</label>
                    <div class="flex items-center justify-between gap-1">
                        <span id="info-phone" class="font-bold text-text-primary"><?= $phone ?></span>
                        <button onclick="window.AdminDetail.copyText('<?= $phone ?>', 'Phone')" class="text-text-secondary hover:text-text-primary transition-colors cursor-pointer shrink-0" title="Copy phone">
                            <span class="material-symbols-outlined text-[14px]">content_copy</span>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 pt-1 border-t border-border-subtle">
                    <div>
                        <label class="text-[10px] font-black text-text-secondary uppercase tracking-widest block mb-0.5">Date of Birth</label>
                        <span id="info-dob" class="font-bold text-text-primary"><?= $dobFormatted ?><?= $ageStr ?></span>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-text-secondary uppercase tracking-widest block mb-0.5">Gender</label>
                        <span id="info-gender" class="font-bold text-text-primary"><?= $gender ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tile 2: Security & Session Telemetry -->
        <div class="ds-card p-5 bg-surface border border-border-subtle rounded-2xl shadow-sm space-y-3.5">
            <div class="flex items-center justify-between pb-2 border-b border-border-subtle">
                <h3 class="text-xs font-black uppercase tracking-widest text-text-primary flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-primary text-[16px]">shield</span>
                    <span>Security & Session</span>
                </h3>
                <span class="text-[10px] font-bold text-text-secondary">UID #<?= $adminId ?></span>
            </div>

            <div class="space-y-2.5 text-xs">
                <div>
                    <label class="text-[10px] font-black text-text-secondary uppercase tracking-widest block mb-0.5">Last Active Session</label>
                    <div class="font-bold text-text-primary" id="sys-last-login"><?= $lastLoginAt ?></div>
                    <div class="text-[10px] font-mono text-text-secondary" id="sys-last-ip">IP: <?= $lastLoginIp ?></div>
                </div>

                <div>
                    <label class="text-[10px] font-black text-text-secondary uppercase tracking-widest block mb-0.5">Password Last Changed</label>
                    <div class="font-bold text-text-primary" id="sys-pwd-change"><?= $pwdChangedAt ?></div>
                </div>

                <div class="grid grid-cols-2 gap-2 pt-1 border-t border-border-subtle">
                    <div>
                        <label class="text-[10px] font-black text-text-secondary uppercase tracking-widest block mb-0.5">Failed Logins</label>
                        <span id="sys-failed-attempts" class="font-bold <?= $failedAttempts > 0 ? 'text-rose-500' : 'text-emerald-500' ?>">
                            <?= $failedAttempts ?> <?= $failedAttempts === 1 ? 'attempt' : 'attempts' ?>
                        </span>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-text-secondary uppercase tracking-widest block mb-0.5">2FA Auth</label>
                        <span id="sys-2fa-badge" class="font-bold <?= $twoFaEnabled ? 'text-emerald-500' : 'text-text-secondary' ?>">
                            <?= $twoFaEnabled ? 'Enabled' : 'Disabled' ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tile 3: Role Scope & Authorization -->
        <div class="ds-card p-5 bg-surface border border-border-subtle rounded-2xl shadow-sm space-y-3.5">
            <div class="flex items-center justify-between pb-2 border-b border-border-subtle">
                <h3 class="text-xs font-black uppercase tracking-widest text-text-primary flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-primary text-[16px]">verified</span>
                    <span>Role &amp; Permissions</span>
                </h3>
                <span id="info-access-badge" class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-widest <?= $isModerator ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20' : 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' ?>">
                    <?= $isModerator ? 'Restricted Access' : 'Full Access' ?>
                </span>
            </div>

            <div class="space-y-2 text-xs">
                <div>
                    <div class="font-bold text-text-primary text-xs" id="role-title-desc"><?= $roleDescription ?></div>
                    <div class="text-[10px] text-text-secondary mt-0.5" id="role-scope-level"><?= $isModerator ? 'Compliance & verification tier' : ($isSuper ? 'Root system tier' : 'Operational management tier') ?></div>
                </div>

                <div class="pt-2 border-t border-border-subtle">
                    <div id="role-scope-list" class="space-y-1.5">
                        <?php if ($isModerator): ?>
                            <div class="flex items-center gap-1.5 text-[11px] text-text-primary font-medium">
                                <span class="material-symbols-outlined text-emerald-500 text-[14px]">check_circle</span>
                                <span>KYC & Document Verification</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-[11px] text-text-primary font-medium">
                                <span class="material-symbols-outlined text-emerald-500 text-[14px]">check_circle</span>
                                <span>Customer Application Reviews</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-[11px] text-text-secondary font-medium">
                                <span class="material-symbols-outlined text-rose-500 text-[14px]">block</span>
                                <span>No Account Deletion or Fund Transfers</span>
                            </div>
                        <?php elseif ($isSuper): ?>
                            <div class="flex items-center gap-1.5 text-[11px] text-text-primary font-medium">
                                <span class="material-symbols-outlined text-emerald-500 text-[14px]">check_circle</span>
                                <span>Root System & Security Oversight</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-[11px] text-text-primary font-medium">
                                <span class="material-symbols-outlined text-emerald-500 text-[14px]">check_circle</span>
                                <span>Manage All Administrators & Roles</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-[11px] text-text-primary font-medium">
                                <span class="material-symbols-outlined text-emerald-500 text-[14px]">check_circle</span>
                                <span>Global Financial Core & Ledger Control</span>
                            </div>
                        <?php else: ?>
                            <div class="flex items-center gap-1.5 text-[11px] text-text-primary font-medium">
                                <span class="material-symbols-outlined text-emerald-500 text-[14px]">check_circle</span>
                                <span>Full Customer & KYC Management</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-[11px] text-text-primary font-medium">
                                <span class="material-symbols-outlined text-emerald-500 text-[14px]">check_circle</span>
                                <span>Staff & Banking Branch Operations</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-[11px] text-text-primary font-medium">
                                <span class="material-symbols-outlined text-emerald-500 text-[14px]">check_circle</span>
                                <span>Global Transactions & System Logs</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ═══════════════════════════════════════════ -->
    <!-- 4. RECENT ACTIVITY TIMELINE TABLE           -->
    <!-- ═══════════════════════════════════════════ -->
    <div class="ds-card overflow-hidden bg-surface border border-border-subtle rounded-3xl shadow-sm">
        <div class="p-5 border-b border-border-subtle bg-surface-subtle flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-2.5">
                <h3 class="font-bold text-xs uppercase tracking-widest flex items-center gap-1.5 text-text-primary">
                    <span class="material-symbols-outlined text-primary text-[18px]" aria-hidden="true">history</span>
                    <span>Recent Activity</span>
                </h3>
                <span id="activity-count-label" class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-surface border border-border-subtle text-text-secondary">
                    <?= $totalActivities ?> entries
                </span>
            </div>
            
            <div class="relative">
                <input type="text" id="activity-search-input" onkeyup="window.AdminDetail.filterActivity()" placeholder="Search activities..." class="bg-surface text-text-primary text-xs font-bold px-3 py-1.5 pl-8 rounded-xl border border-border-subtle focus:border-text-primary focus:ring-1 focus:ring-text-primary focus:outline-none w-full sm:w-56 transition-all">
                <span class="material-symbols-outlined absolute left-2.5 top-2 text-text-secondary text-[15px]">search</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="ds-table w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-border-subtle bg-surface/50 text-[10px] font-black uppercase tracking-widest text-text-secondary">
                        <th scope="col" class="py-3 px-5">Timestamp</th>
                        <th scope="col" class="py-3 px-5">Action</th>
                        <th scope="col" class="py-3 px-5">Details</th>
                        <th scope="col" class="py-3 px-5 text-right">IP Address</th>
                    </tr>
                </thead>
                <tbody id="activity-tbody">
                    <tr>
                        <td colspan="4" class="py-12 text-center">
                            <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                <div class="w-10 h-10 rounded-xl bg-surface-subtle border border-border-subtle flex items-center justify-center mb-2.5 text-text-secondary">
                                    <span class="material-symbols-outlined text-xl">history_toggle_off</span>
                                </div>
                                <div class="text-xs font-black text-text-primary uppercase tracking-wider mb-0.5">No Activity Recorded</div>
                                <div class="text-[11px] font-medium text-text-secondary">No operational actions have been initiated by this account yet.</div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ═══════════════════════════════════════════ -->
<!-- 5. RESET PASSWORD MODAL                     -->
<!-- ═══════════════════════════════════════════ -->
<div id="reset-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-sm px-4">
    <div id="reset-modal-content" class="bg-surface border border-border-subtle rounded-3xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all relative">
        <div class="p-6 sm:p-8">
            <div class="w-12 h-12 rounded-2xl bg-primary/10 text-primary border border-primary/20 flex items-center justify-center mb-4">
                <span class="material-symbols-outlined text-2xl" aria-hidden="true">lock_reset</span>
            </div>
            <h3 class="text-lg font-black text-text-primary mb-1.5">Reset Password</h3>
            <p class="text-xs font-medium text-text-secondary mb-5 leading-relaxed">
                Generate a secure temporary password for <strong class="text-text-primary" id="reset-admin-name"><?= $fullName ?></strong>.
            </p>
            
            <div id="reset-success-state" class="hidden mt-3 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl">
                <div class="text-[10px] font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-400 mb-1.5">Temporary Password (Click to Copy)</div>
                <div id="generated-password-display" class="font-mono text-base font-black text-text-primary tracking-widest select-all mb-2 cursor-pointer p-2.5 bg-surface rounded-xl border border-border-subtle text-center hover:border-emerald-500/50 transition-colors" title="Click to copy"></div>
                <div class="text-[10px] text-text-secondary font-bold flex items-center justify-between">
                    <span>Expiry:</span>
                    <span id="password-expiry-display" class="text-text-primary font-mono font-bold">24 hours</span>
                </div>
            </div>

            <div id="reset-modal-actions" class="flex items-center gap-3 mt-6">
                <button onclick="window.AdminDetail.closeResetModal()" class="btn btn-secondary flex-1 py-2.5 !rounded-xl !font-bold !text-xs cursor-pointer">Cancel</button>
                <button id="reset-submit-btn" onclick="window.AdminDetail.submitReset()" class="btn btn-primary flex-1 py-2.5 !rounded-xl !font-black !text-xs flex items-center justify-center gap-1.5 cursor-pointer">
                    <span class="material-symbols-outlined text-[16px]">autorenew</span> Generate
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════ -->
<!-- 6. DELETE ADMIN MODAL                       -->
<!-- ═══════════════════════════════════════════ -->
<div id="delete-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 backdrop-blur-md px-4">
    <div class="bg-surface border border-rose-500/30 rounded-3xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all relative">
        <div class="p-6 sm:p-8 text-center flex flex-col items-center">
            <div class="w-14 h-14 rounded-full bg-rose-500/10 text-rose-500 border border-rose-500/20 flex items-center justify-center mb-4">
                <span class="material-symbols-outlined text-2xl" aria-hidden="true">warning</span>
            </div>
            <h3 class="text-lg font-black text-text-primary mb-1.5">Delete <span id="delete-admin-name" class="text-rose-500"><?= $fullName ?></span>?</h3>
            <p class="text-xs font-medium text-text-secondary mb-5 leading-relaxed">
                This action is permanent and cannot be undone. All access will be revoked immediately.
            </p>
            
            <div class="flex items-center gap-3 w-full">
                <button onclick="window.AdminDetail.closeDeleteModal()" class="btn btn-secondary flex-1 py-2.5 !rounded-xl !font-bold !text-xs cursor-pointer">Cancel</button>
                <button id="delete-submit-btn" onclick="window.AdminDetail.submitDelete()" class="btn flex-1 py-2.5 !rounded-xl !font-black !text-xs bg-rose-600 text-white hover:bg-rose-700 transition-colors shadow-lg cursor-pointer flex items-center justify-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">delete_forever</span> Confirm Delete
                </button>
            </div>
        </div>
    </div>
</div>

<?php
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/admin-detail.js?v=' . time() . '"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
