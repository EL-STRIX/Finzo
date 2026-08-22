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
    'super_admin' => 'Super Administrator',
    'moderator'   => 'System Moderator',
    default       => 'Administrator'
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
$createdByName  = htmlspecialchars($adminData['created_by_name'] ?? 'Root System', ENT_QUOTES, 'UTF-8');
$joinedDate     = !empty($adminData['created_at']) ? date('d M Y', strtotime($adminData['created_at'])) : '—';

ob_start();
?>

<script>
  window.__INITIAL_ADMIN__ = <?= json_encode($adminData ?? new \stdClass(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  window.__INITIAL_ACTIVITIES__ = <?= json_encode($activities ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>

<div class="space-y-6 sm:space-y-8">
    
    <!-- ═══════════════════════════════════════════ -->
    <!-- 1. TOP PAGE HEADER & NAVIGATION             -->
    <!-- ═══════════════════════════════════════════ -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 relative z-10">
        <div class="flex items-center gap-4">
            <a href="admin-profile.php" class="w-10 h-10 rounded-2xl bg-surface border border-border-subtle hover:bg-surface-hover flex items-center justify-center text-text-secondary hover:text-text-primary transition-all shadow-sm shrink-0 cursor-pointer" title="Back to Admin Directory">
                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">arrow_back</span>
            </a>
            <div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <span class="text-[10px] font-black uppercase tracking-widest text-text-secondary">ADMIN DIRECTORY &bull; DOSSIER</span>
                    <span id="header-status-badge" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest <?= $isActive ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20' ?>">
                        <span class="w-1.5 h-1.5 rounded-full <?= $isActive ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' ?>"></span>
                        <span><?= $isActive ? 'Active Account' : 'Account Suspended' ?></span>
                    </span>
                </div>
                <h1 id="header-admin-name" class="text-xl sm:text-2xl font-black tracking-tight text-text-primary mt-0.5"><?= $fullName ?></h1>
            </div>
        </div>
        <div class="flex items-center gap-2.5 self-start sm:self-auto">
            <a href="admin-profile.php" class="btn btn-secondary px-4 py-2 text-xs font-bold flex items-center gap-1.5 cursor-pointer shadow-sm">
                <span class="material-symbols-outlined text-[16px]" aria-hidden="true">group</span>
                <span>All Administrators</span>
            </a>
            <a id="topbar-audit-link" href="admin-audit-log.php?search=<?= urlencode($adminCode) ?>" class="btn btn-secondary px-4 py-2 text-xs font-bold flex items-center gap-1.5 cursor-pointer shadow-sm">
                <span class="material-symbols-outlined text-[16px]" aria-hidden="true">manage_search</span>
                <span>Audit Logs</span>
            </a>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════ -->
    <!-- 2. EXECUTIVE DOSSIER HERO BANNER            -->
    <!-- ═══════════════════════════════════════════ -->
    <div class="ds-card p-6 sm:p-8 relative overflow-hidden bg-surface border border-border-subtle shadow-xl rounded-3xl">
        <!-- Ambient glowing backdrop elements -->
        <div class="absolute -right-20 -top-20 w-80 h-80 bg-primary/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-20 -bottom-20 w-72 h-72 bg-accent/5 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row items-center lg:items-stretch justify-between gap-8">
            
            <!-- Left: Avatar & Identity Details -->
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6 text-center sm:text-left w-full lg:w-auto">
                <!-- Avatar with status indicator -->
                <div class="relative shrink-0">
                    <div id="admin-photo-container" class="w-24 h-24 sm:w-28 sm:h-28 rounded-3xl overflow-hidden ring-4 ring-primary/10 shadow-2xl bg-surface-subtle flex items-center justify-center border-2 border-border-subtle">
                        <?php if ($profilePhoto): ?>
                            <img src="<?= htmlspecialchars($profilePhoto) ?>?v=<?= time() ?>" class="w-full h-full object-cover" alt="<?= $fullName ?>" onerror="this.outerHTML='<span id=\'photo-initials\' class=\'text-3xl sm:text-4xl font-black text-text-secondary\'><?= $initials ?></span>'">
                        <?php else: ?>
                            <span id="photo-initials" class="text-3xl sm:text-4xl font-black text-text-secondary"><?= $initials ?></span>
                        <?php endif; ?>
                    </div>
                    <!-- Live status indicator dot -->
                    <span class="absolute -top-1.5 -right-1.5 flex h-4 w-4">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full <?= $isActive ? 'bg-emerald-400 opacity-75' : 'bg-rose-400 opacity-75' ?>"></span>
                        <span class="relative inline-flex rounded-full h-4 w-4 <?= $isActive ? 'bg-emerald-500' : 'bg-rose-500' ?> border-2 border-surface"></span>
                    </span>
                </div>

                <!-- Identity Header & Badges -->
                <div class="space-y-3 flex-1 min-w-0">
                    <div>
                        <h2 id="info-name" class="text-2xl sm:text-3xl font-black tracking-tight text-text-primary truncate"><?= $fullName ?></h2>
                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mt-2">
                            <!-- Monospace Admin ID Chip with copy button -->
                            <button onclick="window.AdminDetail.copyText('<?= $adminCode ?>', 'Admin ID')" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-mono font-black uppercase tracking-wider bg-surface-subtle hover:bg-surface-hover border border-border-subtle text-text-secondary hover:text-text-primary transition-all cursor-pointer" title="Click to copy Admin ID">
                                <span id="info-admin-id"><?= $adminCode ?></span>
                                <span class="material-symbols-outlined text-[14px]" aria-hidden="true">content_copy</span>
                            </button>

                            <!-- Role Badge -->
                            <span id="info-role-badge" class="px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-widest <?= $isModerator ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20' : ($isSuper ? 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20' : 'bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20') ?>">
                                <?= $roleLabel ?>
                            </span>

                            <!-- Access Level Badge -->
                            <span id="info-access-badge" class="px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-widest <?= $isFullAccess ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-surface-subtle text-text-secondary border border-border-subtle' ?>">
                                <?= $isFullAccess ? 'Full Access' : 'Restricted Access' ?>
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-y-1 gap-x-4 text-xs font-medium text-text-secondary pt-1">
                        <div class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px] text-text-secondary" aria-hidden="true">calendar_today</span>
                            <span>Member since <strong id="info-joined-date" class="text-text-primary font-bold"><?= $joinedDate ?></strong></span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px] text-text-secondary" aria-hidden="true">person_add</span>
                            <span>Added by <strong id="hero-created-by" class="text-text-primary font-bold"><?= $createdByName ?></strong></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Quick Telemetry & Health Metrics Strip -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-2 gap-3 w-full lg:w-80 shrink-0">
                <!-- Metric 1: Session Status -->
                <div class="p-3.5 rounded-2xl bg-surface-subtle border border-border-subtle">
                    <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1 flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">login</span> Last Login
                    </div>
                    <div id="hero-last-login" class="text-xs font-bold text-text-primary truncate"><?= $lastLoginAt ?></div>
                    <div id="hero-last-ip" class="text-[10px] font-mono font-bold text-text-secondary mt-0.5 truncate"><?= $lastLoginIp ?></div>
                </div>

                <!-- Metric 2: Audit Logs -->
                <div class="p-3.5 rounded-2xl bg-surface-subtle border border-border-subtle">
                    <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1 flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">history</span> Activity Count
                    </div>
                    <div id="hero-activity-count" class="text-xs font-bold text-primary"><?= $totalActivities ?> Actions Logged</div>
                    <div class="text-[10px] font-medium text-text-secondary mt-0.5">Recorded in audit trail</div>
                </div>

                <!-- Metric 3: Security 2FA -->
                <div class="p-3.5 rounded-2xl bg-surface-subtle border border-border-subtle">
                    <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1 flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">lock</span> 2FA Security
                    </div>
                    <div id="hero-2fa-status" class="text-xs font-bold <?= $twoFaEnabled ? 'text-emerald-500' : 'text-text-secondary' ?>">
                        <?= $twoFaEnabled ? 'Enabled & Active' : 'Disabled' ?>
                    </div>
                    <div class="text-[10px] font-medium text-text-secondary mt-0.5">Two-factor auth</div>
                </div>

                <!-- Metric 4: Failed Logins -->
                <div class="p-3.5 rounded-2xl bg-surface-subtle border border-border-subtle">
                    <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1 flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">gpp_maybe</span> Failed Logins
                    </div>
                    <div id="hero-failed-attempts" class="text-xs font-bold <?= $failedAttempts > 0 ? 'text-rose-500' : 'text-emerald-500' ?>">
                        <?= $failedAttempts ?> <?= $failedAttempts === 1 ? 'attempt' : 'attempts' ?>
                    </div>
                    <div class="text-[10px] font-medium text-text-secondary mt-0.5"><?= $failedAttempts > 0 ? 'Review security' : 'Account secure' ?></div>
                </div>
            </div>

        </div>
    </div>

    <!-- ═══════════════════════════════════════════ -->
    <!-- 3. TWO-COLUMN MAIN DOSSIER LAYOUT           -->
    <!-- ═══════════════════════════════════════════ -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">

        <!-- ───────────────────────────────────────── -->
        <!-- LEFT COLUMN: Identity, Telemetry & Matrix -->
        <!-- ───────────────────────────────────────── -->
        <div class="lg:col-span-7 space-y-6">

            <!-- Card 1: Identity & Personal Information -->
            <div class="ds-card p-6 bg-surface border border-border-subtle rounded-3xl shadow-sm">
                <div class="flex items-center justify-between pb-4 mb-5 border-b border-border-subtle">
                    <h3 class="text-xs font-black uppercase tracking-widest text-text-primary flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[18px]">badge</span>
                        <span>Identity & Contact Details</span>
                    </h3>
                    <span class="text-[10px] font-bold text-text-secondary uppercase tracking-widest">Verified Record</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Email Address -->
                    <div class="p-4 rounded-2xl bg-surface-subtle border border-border-subtle flex flex-col justify-between">
                        <label class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1.5 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[14px]">mail</span> Email Address
                        </label>
                        <div class="flex items-center justify-between gap-2">
                            <div id="info-email" class="text-xs font-bold text-text-primary truncate" title="<?= $email ?>"><?= $email ?></div>
                            <button onclick="window.AdminDetail.copyText('<?= $email ?>', 'Email')" class="p-1 rounded-lg hover:bg-surface text-text-secondary hover:text-text-primary transition-colors cursor-pointer shrink-0" title="Copy email">
                                <span class="material-symbols-outlined text-[14px]">content_copy</span>
                            </button>
                        </div>
                    </div>

                    <!-- Phone Number -->
                    <div class="p-4 rounded-2xl bg-surface-subtle border border-border-subtle flex flex-col justify-between">
                        <label class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1.5 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[14px]">call</span> Phone Number
                        </label>
                        <div class="flex items-center justify-between gap-2">
                            <div id="info-phone" class="text-xs font-bold text-text-primary"><?= $phone ?></div>
                            <button onclick="window.AdminDetail.copyText('<?= $phone ?>', 'Phone')" class="p-1 rounded-lg hover:bg-surface text-text-secondary hover:text-text-primary transition-colors cursor-pointer shrink-0" title="Copy phone">
                                <span class="material-symbols-outlined text-[14px]">content_copy</span>
                            </button>
                        </div>
                    </div>

                    <!-- Date of Birth & Age -->
                    <div class="p-4 rounded-2xl bg-surface-subtle border border-border-subtle flex flex-col justify-between">
                        <label class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1.5 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[14px]">cake</span> Date of Birth
                        </label>
                        <div id="info-dob" class="text-xs font-bold text-text-primary"><?= $dobFormatted ?><?= $ageStr ?></div>
                    </div>

                    <!-- Gender Identity -->
                    <div class="p-4 rounded-2xl bg-surface-subtle border border-border-subtle flex flex-col justify-between">
                        <label class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1.5 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[14px]">person</span> Gender
                        </label>
                        <div id="info-gender" class="text-xs font-bold text-text-primary"><?= $gender ?></div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Security & Session Telemetry -->
            <div class="ds-card p-6 bg-surface border border-border-subtle rounded-3xl shadow-sm">
                <div class="flex items-center justify-between pb-4 mb-5 border-b border-border-subtle">
                    <h3 class="text-xs font-black uppercase tracking-widest text-text-primary flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[18px]">security</span>
                        <span>Security & Session Telemetry</span>
                    </h3>
                    <span class="text-[10px] font-bold text-text-secondary uppercase tracking-widest">Audit Trail</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <div class="p-3.5 rounded-2xl bg-surface-subtle border border-border-subtle">
                        <label class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1 block">Last Active Session</label>
                        <div id="sys-last-login" class="text-xs font-bold text-text-primary truncate"><?= $lastLoginAt ?></div>
                        <div id="sys-last-ip" class="text-[10px] font-mono font-bold text-text-secondary mt-0.5 truncate"><?= $lastLoginIp ?></div>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-surface-subtle border border-border-subtle">
                        <label class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1 block">Password Last Changed</label>
                        <div id="sys-pwd-change" class="text-xs font-bold text-text-primary truncate"><?= $pwdChangedAt ?></div>
                        <div class="text-[10px] font-medium text-text-secondary mt-0.5">Credential age</div>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-surface-subtle border border-border-subtle">
                        <label class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1 block">Failed Login Attempts</label>
                        <div id="sys-failed-attempts" class="text-xs font-bold <?= $failedAttempts > 0 ? 'text-rose-500' : 'text-emerald-500' ?>">
                            <?= $failedAttempts ?>
                        </div>
                        <div class="text-[10px] font-medium text-text-secondary mt-0.5"><?= $failedAttempts > 0 ? 'Failed checks' : 'Zero lockouts' ?></div>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-surface-subtle border border-border-subtle">
                        <label class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1 block">Provisioned By</label>
                        <div id="sys-created-by" class="text-xs font-bold text-text-primary truncate"><?= $createdByName ?></div>
                        <div class="text-[10px] font-medium text-text-secondary mt-0.5">Account creator</div>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-surface-subtle border border-border-subtle">
                        <label class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1 block">System Account ID</label>
                        <div id="sys-id" class="text-xs font-mono font-bold text-text-secondary"><?= $adminCode ?></div>
                        <div class="text-[10px] font-medium text-text-secondary mt-0.5">UID #<?= $adminId ?></div>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-surface-subtle border border-border-subtle">
                        <label class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1 block">2FA Enforcement</label>
                        <div id="sys-2fa-badge" class="text-xs font-bold <?= $twoFaEnabled ? 'text-emerald-500' : 'text-text-secondary' ?>">
                            <?= $twoFaEnabled ? 'Active (TOTP)' : 'Not Configured' ?>
                        </div>
                        <div class="text-[10px] font-medium text-text-secondary mt-0.5">Secondary auth</div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Role Scope & Authorization Matrix -->
            <div class="ds-card p-6 bg-surface border border-border-subtle rounded-3xl shadow-sm">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-border-subtle">
                    <div>
                        <h3 class="text-xs font-black uppercase tracking-widest text-text-primary flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-[18px]">verified_user</span>
                            <span>Role Privileges & Access Control Matrix</span>
                        </h3>
                        <p class="text-[10px] font-bold text-text-secondary uppercase tracking-widest mt-0.5" id="privilege-level-sub">
                            <?= $isModerator ? 'Compliance Authority (Restricted)' : ($isSuper ? 'Root Authority (Unrestricted)' : 'Operational Authority (Full)') ?>
                        </p>
                    </div>
                    <span id="privilege-role-badge" class="px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-widest <?= $isModerator ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20' : ($isSuper ? 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20' : 'bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20') ?>">
                        <?= $roleLabel ?>
                    </span>
                </div>

                <!-- Granted Section -->
                <div class="mb-5">
                    <div class="text-[10px] font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-400 mb-2.5 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        <span>Granted Access (Authorized)</span>
                    </div>
                    <div id="privileges-granted" class="space-y-2">
                        <!-- Populated dynamically via JS / SSR Fallback -->
                    </div>
                </div>

                <!-- Restricted Section -->
                <div class="pt-4 border-t border-border-subtle">
                    <div class="text-[10px] font-black uppercase tracking-widest text-rose-500 mb-2.5 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                        <span>Restricted Access (Forbidden)</span>
                    </div>
                    <div id="privileges-restricted" class="space-y-2">
                        <!-- Populated dynamically via JS / SSR Fallback -->
                    </div>
                </div>

                <!-- Compliance Quote Box -->
                <div class="mt-5 p-4 rounded-2xl bg-surface-subtle border border-border-subtle">
                    <p id="security-note-text" class="text-xs leading-relaxed text-text-secondary font-medium italic">
                        <?= $isModerator 
                            ? '"Moderators have restricted operational access. All KYC approvals and customer account reviews are permanently recorded for institutional compliance and auditing."' 
                            : '"Administrators possess elevated operational privileges. Every system modification and user override is recorded in the global audit log."' ?>
                    </p>
                </div>
            </div>

        </div>

        <!-- ───────────────────────────────────────── -->
        <!-- RIGHT COLUMN: Actions, Governance & Shortcuts -->
        <!-- ───────────────────────────────────────── -->
        <div class="lg:col-span-5 space-y-6">

            <!-- Card 1: Administrative Control Center -->
            <div class="ds-card p-6 bg-surface border border-border-subtle rounded-3xl shadow-sm">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-border-subtle">
                    <h3 class="text-xs font-black uppercase tracking-widest text-text-primary flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[18px]">admin_panel_settings</span>
                        <span>Administrative Controls</span>
                    </h3>
                    <span class="text-[10px] font-bold text-text-secondary uppercase tracking-widest">Executive</span>
                </div>

                <div class="space-y-3">
                    <!-- Suspend / Activate Account Button -->
                    <button id="action-suspend-btn" onclick="window.AdminDetail.toggleStatus()" class="w-full flex items-center justify-between p-4 bg-surface hover:bg-surface-hover rounded-2xl group transition-all border border-border-subtle hover:border-amber-500/40 cursor-pointer shadow-sm">
                        <div class="flex items-center gap-3.5">
                            <div id="suspend-icon-container" class="w-10 h-10 rounded-xl <?= $isActive ? 'bg-amber-500/10 text-amber-500 border border-amber-500/20' : 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20' ?> flex items-center justify-center group-hover:scale-105 transition-transform">
                                <span id="suspend-icon" class="material-symbols-outlined text-[20px]" aria-hidden="true"><?= $isActive ? 'block' : 'check_circle' ?></span>
                            </div>
                            <div class="text-left">
                                <div id="suspend-label" class="text-xs font-black text-text-primary"><?= $isActive ? 'Suspend Administrator' : 'Activate Administrator' ?></div>
                                <div id="suspend-desc" class="text-[10px] font-medium text-text-secondary"><?= $isActive ? 'Temporarily revoke all system access' : 'Restore full operational access' ?></div>
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-text-secondary text-[18px] group-hover:translate-x-0.5 transition-transform" aria-hidden="true">chevron_right</span>
                    </button>

                    <!-- Reset Password Button -->
                    <button onclick="window.AdminDetail.showResetModal()" class="w-full flex items-center justify-between p-4 bg-surface hover:bg-surface-hover rounded-2xl group transition-all border border-border-subtle hover:border-primary/40 cursor-pointer shadow-sm">
                        <div class="flex items-center gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center group-hover:scale-105 transition-transform border border-primary/20">
                                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">lock_reset</span>
                            </div>
                            <div class="text-left">
                                <div class="text-xs font-black text-text-primary">Reset Password</div>
                                <div class="text-[10px] font-medium text-text-secondary">Generate secure temporary credentials</div>
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-text-secondary text-[18px] group-hover:translate-x-0.5 transition-transform" aria-hidden="true">chevron_right</span>
                    </button>

                    <!-- Delete Account Button -->
                    <button onclick="window.AdminDetail.showDeleteModal()" class="w-full flex items-center justify-between p-4 bg-surface hover:bg-rose-500/5 rounded-2xl group transition-all border border-border-subtle hover:border-rose-500/40 cursor-pointer shadow-sm">
                        <div class="flex items-center gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-500 flex items-center justify-center group-hover:scale-105 transition-transform border border-rose-500/20">
                                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">person_remove</span>
                            </div>
                            <div class="text-left">
                                <div class="text-xs font-black text-rose-500">Delete Account</div>
                                <div class="text-[10px] font-medium text-text-secondary">Irreversible permanent removal</div>
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-text-secondary text-[18px] group-hover:translate-x-0.5 transition-transform" aria-hidden="true">chevron_right</span>
                    </button>
                </div>
            </div>

            <!-- Card 2: Security Policy & Audit Link Card -->
            <div class="ds-card p-6 bg-surface border border-border-subtle rounded-3xl shadow-sm">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-border-subtle">
                    <h3 class="text-xs font-black uppercase tracking-widest text-text-primary flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[18px]">policy</span>
                        <span>Institutional Audit Trail</span>
                    </h3>
                    <span class="text-[10px] font-bold text-emerald-500 uppercase tracking-widest">Active Logging</span>
                </div>

                <div class="space-y-3.5 text-xs text-text-secondary leading-relaxed">
                    <div class="p-3.5 rounded-2xl bg-surface-subtle border border-border-subtle flex items-start gap-3">
                        <span class="material-symbols-outlined text-primary text-[18px] shrink-0 mt-0.5">fingerprint</span>
                        <div>
                            <div class="font-bold text-text-primary text-xs">Immutable Audit Traceability</div>
                            <div class="text-[11px] text-text-secondary mt-0.5">All administrative actions are cryptographically logged with IP, session, and timestamp telemetry.</div>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-surface-subtle border border-border-subtle flex items-start gap-3">
                        <span class="material-symbols-outlined text-teal-500 text-[18px] shrink-0 mt-0.5">encrypted</span>
                        <div>
                            <div class="font-bold text-text-primary text-xs">Role-Based Access Enforcement</div>
                            <div class="text-[11px] text-text-secondary mt-0.5">Granular RBAC boundaries restrict unauthorized financial overrides and cross-account mutations.</div>
                        </div>
                    </div>
                </div>

                <div class="mt-5 pt-4 border-t border-border-subtle">
                    <a id="view-audit-link" href="admin-audit-log.php?search=<?= urlencode($adminCode) ?>" class="btn btn-secondary w-full py-2.5 text-xs font-bold flex items-center justify-center gap-2 cursor-pointer shadow-sm">
                        <span class="material-symbols-outlined text-[16px]">manage_search</span>
                        <span>Inspect in Global Audit Log</span>
                        <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                    </a>
                </div>
            </div>

        </div>

    </div>

    <!-- ═══════════════════════════════════════════ -->
    <!-- 4. ACTIVITY TIMELINE & AUDIT TRAIL TABLE    -->
    <!-- ═══════════════════════════════════════════ -->
    <div class="ds-card overflow-hidden bg-surface border border-border-subtle rounded-3xl shadow-xl transition-all duration-300">
        <div class="p-5 sm:p-6 border-b border-border-subtle bg-surface-subtle flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2.5">
                    <h3 class="font-bold text-xs uppercase tracking-widest flex items-center gap-2 text-text-primary">
                        <span class="material-symbols-outlined text-primary text-[18px]" aria-hidden="true">history</span>
                        <span>Activity Timeline &amp; Audit Trail</span>
                    </h3>
                    <span id="activity-count-label" class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-surface border border-border-subtle text-text-secondary">
                        <?= $totalActivities ?> entries
                    </span>
                </div>
                <p class="text-[10px] font-bold text-text-secondary uppercase tracking-widest mt-0.5">Actions initiated by this account</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="relative">
                    <input type="text" id="activity-search-input" onkeyup="window.AdminDetail.filterActivity()" placeholder="Search activities..." class="bg-surface text-text-primary text-xs font-bold px-3.5 py-2 pl-9 rounded-xl border border-border-subtle focus:border-text-primary focus:ring-2 focus:ring-text-primary focus:outline-none w-48 sm:w-64 transition-all">
                    <span class="material-symbols-outlined absolute left-2.5 top-2.5 text-text-secondary text-[16px]">search</span>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="ds-table w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-border-subtle bg-surface/50">
                        <th scope="col" class="py-3.5 px-6 text-[10px] font-black uppercase tracking-widest text-text-secondary">Timestamp</th>
                        <th scope="col" class="py-3.5 px-6 text-[10px] font-black uppercase tracking-widest text-text-secondary">Operation / Action</th>
                        <th scope="col" class="py-3.5 px-6 text-[10px] font-black uppercase tracking-widest text-text-secondary">Context &amp; Details</th>
                        <th scope="col" class="py-3.5 px-6 text-right text-[10px] font-black uppercase tracking-widest text-text-secondary">IP Address</th>
                    </tr>
                </thead>
                <tbody id="activity-tbody">
                    <!-- Populated dynamically via JS / SSR Fallback -->
                    <tr>
                        <td colspan="4" class="py-12 text-center">
                            <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                <div class="w-12 h-12 rounded-2xl bg-surface-subtle border border-border-subtle flex items-center justify-center mb-3 text-text-secondary">
                                    <span class="material-symbols-outlined text-2xl">history_toggle_off</span>
                                </div>
                                <div class="text-xs font-black text-text-primary uppercase tracking-wider mb-1">No Activity Logged Yet</div>
                                <div class="text-[11px] font-medium text-text-secondary">This administrator has not initiated any recorded actions or operations yet.</div>
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
        <div class="p-8">
            <div class="w-14 h-14 rounded-2xl bg-primary/10 text-primary border border-primary/20 flex items-center justify-center mb-5">
                <span class="material-symbols-outlined text-2xl" aria-hidden="true">lock_reset</span>
            </div>
            <h3 class="text-xl font-black text-text-primary mb-2">Reset Password</h3>
            <p class="text-xs font-medium text-text-secondary mb-6 leading-relaxed">
                A secure temporary password will be generated for <strong class="text-text-primary" id="reset-admin-name"><?= $fullName ?></strong>.
            </p>
            
            <div id="reset-success-state" class="hidden mt-4 p-5 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl">
                <div class="text-[10px] font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-400 mb-2">Temporary Password (Click to Copy)</div>
                <div id="generated-password-display" class="font-mono text-lg font-black text-text-primary tracking-widest select-all mb-2 cursor-pointer p-3 bg-surface rounded-xl border border-border-subtle text-center hover:border-emerald-500/50 transition-colors" title="Click to copy password"></div>
                <div class="text-[10px] text-text-secondary font-bold flex items-center justify-between">
                    <span>Valid For:</span>
                    <span id="password-expiry-display" class="text-text-primary font-mono font-bold">24 hours</span>
                </div>
            </div>

            <div id="reset-modal-actions" class="flex items-center gap-3 mt-6">
                <button onclick="window.AdminDetail.closeResetModal()" class="btn btn-secondary flex-1 py-2.5 !rounded-xl !font-bold !text-xs cursor-pointer">Cancel</button>
                <button id="reset-submit-btn" onclick="window.AdminDetail.submitReset()" class="btn btn-primary flex-1 py-2.5 !rounded-xl !font-black !text-xs flex items-center justify-center gap-1.5 cursor-pointer">
                    <span class="material-symbols-outlined text-[16px]">autorenew</span> Generate Password
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
        <div class="p-8 text-center flex flex-col items-center">
            <div class="w-16 h-16 rounded-full bg-rose-500/10 text-rose-500 border border-rose-500/20 flex items-center justify-center mb-5 animate-pulse">
                <span class="material-symbols-outlined text-3xl" aria-hidden="true">warning</span>
            </div>
            <h3 class="text-xl font-black text-text-primary mb-2">Delete <span id="delete-admin-name" class="text-rose-500"><?= $fullName ?></span>?</h3>
            <p class="text-xs font-medium text-text-secondary mb-6 leading-relaxed">
                This action is <strong>irreversible</strong>. The administrator account will be permanently purged and all credentials revoked immediately.
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
