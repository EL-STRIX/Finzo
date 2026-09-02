<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin — System Settings';
include __DIR__ . '/_guard.php';

$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/admin-settings.js?v=' . time() . '"></script>';

ob_start();
?>

<!-- TOP PAGE HEADER -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 relative z-10">
    <div>
        <h1 class="text-2xl font-headline font-black text-text-primary tracking-tight">System Settings</h1>
        <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-1">Global Parameters, Security Policies &amp; Governance</p>
    </div>
    <div class="flex items-center gap-2 px-3.5 py-1.5 rounded-full border border-border-subtle bg-surface/80 shadow-sm text-xs font-bold text-text-secondary uppercase tracking-widest">
        <span class="material-symbols-outlined text-[16px] text-warning" aria-hidden="true">engineering</span>
        <span>Under Development</span>
    </div>
</div>

<!-- SYSTEM STATUS CARDS -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 relative z-10">
    <div class="ds-card p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center border border-primary/20 shrink-0">
            <span class="material-symbols-outlined text-2xl" aria-hidden="true">tune</span>
        </div>
        <div>
            <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest">Active Parameters</div>
            <div class="text-xl font-black font-display text-text-primary">Coming Soon</div>
        </div>
    </div>
    <div class="ds-card p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center border border-emerald-500/20 shrink-0">
            <span class="material-symbols-outlined text-2xl" aria-hidden="true">verified_user</span>
        </div>
        <div>
            <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest">Platform Status</div>
            <div class="text-xl font-black font-display text-emerald-500">Operational</div>
        </div>
    </div>
    <div class="ds-card p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center border border-blue-500/20 shrink-0">
            <span class="material-symbols-outlined text-2xl" aria-hidden="true">cloud_sync</span>
        </div>
        <div>
            <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest">Disaster Recovery</div>
            <div class="text-xl font-black font-display text-text-primary">BCDR Ready</div>
        </div>
    </div>
</div>

<div class="flex flex-col lg:flex-row gap-6 lg:gap-8 relative z-10">

    <!-- SETTINGS NAVIGATION SIDEBAR -->
    <div class="w-full lg:w-64 shrink-0">
        <div class="ds-card sticky top-24 p-3">
            <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest px-3 py-2">Categories</div>
            <nav class="flex flex-row lg:flex-col gap-1 overflow-x-auto custom-scrollbar">
                <button class="settings-tab-ui active text-left px-4 py-3 rounded-xl flex items-center gap-3 transition-colors text-sm font-bold bg-primary/10 text-primary border border-primary/20" data-tab="banking">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">account_balance</span>
                    <span>Banking</span>
                </button>
                <button class="settings-tab-ui text-left px-4 py-3 rounded-xl flex items-center gap-3 transition-colors text-sm font-bold text-text-secondary hover:bg-background hover:text-text-primary" data-tab="security">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">security</span>
                    <span>Security</span>
                </button>
                <button class="settings-tab-ui text-left px-4 py-3 rounded-xl flex items-center gap-3 transition-colors text-sm font-bold text-text-secondary hover:bg-background hover:text-text-primary" data-tab="application">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">app_settings_alt</span>
                    <span>Application</span>
                </button>
                <button class="settings-tab-ui text-left px-4 py-3 rounded-xl flex items-center gap-3 transition-colors text-sm font-bold text-text-secondary hover:bg-background hover:text-text-primary" data-tab="operational">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">manufacturing</span>
                    <span>Operational</span>
                </button>
                <button class="settings-tab-ui text-left px-4 py-3 rounded-xl flex items-center gap-3 transition-colors text-sm font-bold text-text-secondary hover:bg-background hover:text-text-primary" data-tab="backup">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">database</span>
                    <span>Data Backup</span>
                </button>
            </nav>
        </div>
    </div>

    <!-- SETTINGS CONTENT PANELS -->
    <div class="flex-1 min-w-0 space-y-0">

        <!-- BANKING Panel -->
        <div id="tab-banking" class="settings-panel ds-card p-6 lg:p-8 relative overflow-hidden">
            <div class="border-b border-border-subtle pb-4 mb-6">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[22px]" aria-hidden="true">account_balance</span>
                    <h2 class="text-lg font-headline font-black text-text-primary">Banking Configuration</h2>
                </div>
                <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-1">Configure interest rates, transaction limits, and core banking rules</p>
            </div>
            <?= renderComingSoonPanel([
                ['icon' => 'percent', 'label' => 'Savings Interest Rate', 'desc' => 'Annual interest rate applied to savings accounts'],
                ['icon' => 'swap_horiz', 'label' => 'Default Daily Transfer Limit', 'desc' => 'Maximum amount a user can transfer in 24 hours'],
                ['icon' => 'account_balance_wallet', 'label' => 'Minimum Account Balance', 'desc' => 'Minimum required balance to keep an account active'],
                ['icon' => 'payments', 'label' => 'Withdrawal Limit per Transaction', 'desc' => 'Maximum single withdrawal amount allowed'],
            ]) ?>
            <?= renderPanelOverlay() ?>
        </div>

        <!-- SECURITY Panel -->
        <div id="tab-security" class="settings-panel ds-card p-6 lg:p-8 hidden relative overflow-hidden">
            <div class="border-b border-border-subtle pb-4 mb-6">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-warning text-[22px]" aria-hidden="true">security</span>
                    <h2 class="text-lg font-headline font-black text-text-primary">Security Policies</h2>
                </div>
                <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-1">Configure authentication thresholds, lockouts, and administrative session timeouts</p>
            </div>
            <?= renderComingSoonPanel([
                ['icon' => 'lock_clock', 'label' => 'Max Failed Login Attempts', 'desc' => 'Account lockout threshold before temporary ban'],
                ['icon' => 'timer', 'label' => 'Session Timeout (Minutes)', 'desc' => 'Admin dashboard auto-logout inactivity period'],
                ['icon' => 'phonelink_lock', 'label' => 'Two-Factor Authentication', 'desc' => 'Enforce 2FA for all admin and staff accounts'],
                ['icon' => 'password', 'label' => 'Password Rotation Policy', 'desc' => 'Force password reset interval in days'],
            ]) ?>
            <?= renderPanelOverlay() ?>
        </div>

        <!-- APPLICATION Panel -->
        <div id="tab-application" class="settings-panel ds-card p-6 lg:p-8 hidden relative overflow-hidden">
            <div class="border-b border-border-subtle pb-4 mb-6">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-info text-[22px]" aria-hidden="true">app_settings_alt</span>
                    <h2 class="text-lg font-headline font-black text-text-primary">Application State</h2>
                </div>
                <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-1">Manage platform maintenance mode, default currency, and customer onboarding status</p>
            </div>
            <?= renderComingSoonPanel([
                ['icon' => 'construction', 'label' => 'Maintenance Mode', 'desc' => 'Temporarily suspend all user access to the portal'],
                ['icon' => 'currency_rupee', 'label' => 'Default Currency', 'desc' => 'Base currency used across all financial transactions'],
                ['icon' => 'how_to_reg', 'label' => 'Allow User Registration', 'desc' => 'Enable or disable new customer onboarding'],
                ['icon' => 'language', 'label' => 'Platform Locale', 'desc' => 'Default language and regional formatting settings'],
            ]) ?>
            <?= renderPanelOverlay() ?>
        </div>

        <!-- OPERATIONAL Panel -->
        <div id="tab-operational" class="settings-panel ds-card p-6 lg:p-8 hidden relative overflow-hidden">
            <div class="border-b border-border-subtle pb-4 mb-6">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-accent text-[22px]" aria-hidden="true">manufacturing</span>
                    <h2 class="text-lg font-headline font-black text-text-primary">Operational Parameters</h2>
                </div>
                <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-1">Configure background interest accrual, retention rules, and automated scheduled jobs</p>
            </div>
            <?= renderComingSoonPanel([
                ['icon' => 'schedule', 'label' => 'Auto Interest Calculation', 'desc' => 'Enable automated nightly interest credit jobs'],
                ['icon' => 'history', 'label' => 'Audit Log Retention (Days)', 'desc' => 'Duration to retain security and access logs'],
                ['icon' => 'savings', 'label' => 'Interest Accrual Cycle', 'desc' => 'Frequency of interest computation (daily/monthly)'],
                ['icon' => 'mail', 'label' => 'Notification Dispatch Mode', 'desc' => 'Control transactional email and SMS delivery'],
            ]) ?>
            <?= renderPanelOverlay() ?>
        </div>

        <!-- BACKUP Panel -->
        <div id="tab-backup" class="settings-panel hidden space-y-6">
            <div class="ds-card p-6 lg:p-8">
                <div class="border-b border-border-subtle pb-4 mb-6">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-info text-[24px]" aria-hidden="true">cloud_sync</span>
                        <h2 class="text-lg font-headline font-black text-text-primary">Disaster Recovery &amp; Backup</h2>
                    </div>
                    <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-1">Execute manual database snapshots or export structures for disaster recovery</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Full Backup Card -->
                    <div class="border border-border-subtle rounded-2xl p-5 bg-background flex flex-col justify-between relative overflow-hidden opacity-60">
                        <div class="absolute inset-0 flex flex-col items-center justify-center bg-surface/80 backdrop-blur-[2px] z-10 gap-2">
                            <span class="material-symbols-outlined text-4xl text-text-secondary">schedule</span>
                            <span class="text-xs font-black text-text-secondary uppercase tracking-widest">Coming Soon</span>
                        </div>
                        <div>
                            <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center mb-4 border border-primary/20">
                                <span class="material-symbols-outlined text-2xl" aria-hidden="true">database</span>
                            </div>
                            <h3 class="font-headline font-black text-base text-text-primary">Full Database Snapshot</h3>
                            <p class="text-xs text-text-secondary mt-1 mb-6 leading-relaxed">Generates a complete SQL dump containing table schema, constraints, and all transactional data.</p>
                        </div>
                        <button type="button" class="btn btn-primary !w-full flex items-center justify-center gap-2" disabled>
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">download</span>
                            <span>Run Full Backup</span>
                        </button>
                    </div>

                    <!-- Schema Only Card -->
                    <div class="border border-border-subtle rounded-2xl p-5 bg-background flex flex-col justify-between relative overflow-hidden opacity-60">
                        <div class="absolute inset-0 flex flex-col items-center justify-center bg-surface/80 backdrop-blur-[2px] z-10 gap-2">
                            <span class="material-symbols-outlined text-4xl text-text-secondary">schedule</span>
                            <span class="text-xs font-black text-text-secondary uppercase tracking-widest">Coming Soon</span>
                        </div>
                        <div>
                            <div class="w-12 h-12 rounded-xl bg-info-bg text-info flex items-center justify-center mb-4 border border-info/20">
                                <span class="material-symbols-outlined text-2xl" aria-hidden="true">schema</span>
                            </div>
                            <h3 class="font-headline font-black text-base text-text-primary">Schema Only</h3>
                            <p class="text-xs text-text-secondary mt-1 mb-6 leading-relaxed">Exports DDL table definitions, triggers, views, and foreign key relations without customer rows.</p>
                        </div>
                        <button type="button" class="btn btn-secondary !w-full !border-info !text-info flex items-center justify-center gap-2" disabled>
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">code</span>
                            <span>Backup Schema</span>
                        </button>
                    </div>

                    <!-- Data Only Card -->
                    <div class="border border-border-subtle rounded-2xl p-5 bg-background flex flex-col justify-between relative overflow-hidden opacity-60">
                        <div class="absolute inset-0 flex flex-col items-center justify-center bg-surface/80 backdrop-blur-[2px] z-10 gap-2">
                            <span class="material-symbols-outlined text-4xl text-text-secondary">schedule</span>
                            <span class="text-xs font-black text-text-secondary uppercase tracking-widest">Coming Soon</span>
                        </div>
                        <div>
                            <div class="w-12 h-12 rounded-xl bg-success-bg text-success flex items-center justify-center mb-4 border border-success/20">
                                <span class="material-symbols-outlined text-2xl" aria-hidden="true">data_object</span>
                            </div>
                            <h3 class="font-headline font-black text-base text-text-primary">Data Rows Only</h3>
                            <p class="text-xs text-text-secondary mt-1 mb-6 leading-relaxed">Exports raw table records via INSERT statements without modifying or re-creating existing schema.</p>
                        </div>
                        <button type="button" class="btn btn-secondary !w-full !border-success !text-success flex items-center justify-center gap-2" disabled>
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">table_rows</span>
                            <span>Backup Data</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="p-4 rounded-xl bg-warning-bg border border-warning/30 flex gap-3 text-warning">
                <span class="material-symbols-outlined shrink-0 text-xl" aria-hidden="true">info</span>
                <div class="text-xs leading-relaxed">
                    <strong class="font-black block mb-1 uppercase tracking-wider">Storage &amp; Encryption Policy</strong>
                    All backup archives will be written to <code>/storage/backups</code> and isolated from public HTTP access. This feature is being prepared for production release.
                </div>
            </div>
        </div>

    </div>
</div>

<?php
/**
 * Renders a grid of blurred mocked setting rows.
 * The content is visible but blurred underneath the panel-level overlay.
 */
function renderComingSoonPanel(array $items): string
{
    $html = '<div class="grid grid-cols-1 md:grid-cols-2 gap-5">';
    foreach ($items as $item) {
        $html .= '
        <div class="relative p-5 rounded-2xl border border-border-subtle bg-background overflow-hidden">
            <div class="flex items-start gap-4 blur-[2px] select-none pointer-events-none">
                <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center border border-primary/20 shrink-0 mt-0.5">
                    <span class="material-symbols-outlined text-xl" aria-hidden="true">' . htmlspecialchars($item['icon']) . '</span>
                </div>
                <div class="flex-1">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-sm font-black text-text-primary">' . htmlspecialchars($item['label']) . '</span>
                    </div>
                    <p class="text-xs text-text-secondary mt-1 leading-relaxed">' . htmlspecialchars($item['desc']) . '</p>
                    <div class="mt-3 h-8 rounded-lg bg-border-subtle/50"></div>
                </div>
            </div>
        </div>';
    }
    $html .= '</div>';
    return $html;
}

/**
 * Renders a full-panel persistent "Coming Soon" frosted-glass overlay.
 */
function renderPanelOverlay(): string
{
    return '
    <div class="absolute inset-0 z-20 flex flex-col items-center justify-center gap-4"
         style="background: rgba(var(--color-surface-rgb, 15 15 20), 0.55); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);">
        <div class="flex flex-col items-center gap-3 text-center px-6">
            <div class="w-16 h-16 rounded-2xl bg-primary/10 border border-primary/20 flex items-center justify-center">
                <span class="material-symbols-outlined text-4xl text-primary">rocket_launch</span>
            </div>
            <div>
                <div class="text-lg font-headline font-black text-text-primary tracking-tight">Coming Soon</div>
                <div class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-1">This feature is under active development</div>
            </div>
            <div class="px-4 py-2 rounded-full bg-primary/10 border border-primary/20 text-xs font-black text-primary uppercase tracking-widest">
                🚀 &nbsp;Launching Soon
            </div>
        </div>
    </div>';
}

$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
