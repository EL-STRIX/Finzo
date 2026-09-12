<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin — System Settings (Coming Soon)';
include __DIR__ . '/_guard.php';

$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/admin-settings.js?v=' . time() . '"></script>';

ob_start();
?>

<!-- TOP PAGE HEADER -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 relative z-10">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-primary/10 text-primary border border-primary/20">
                <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></span>
                In Development
            </span>
            <span class="text-text-muted text-xs font-mono">Module: Governance 2.0</span>
        </div>
        <h1 class="text-2xl font-headline font-black text-text-primary tracking-tight">System Settings &amp; Configuration</h1>
        <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-1">Global Parameters, Security Policies &amp; Institutional Governance</p>
    </div>
    <div class="flex items-center gap-3">
        <button id="btn-notify-settings" class="btn btn-secondary !px-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">notifications_active</span>
            <span class="font-bold text-xs">Notify on Launch</span>
        </button>
    </div>
</div>

<!-- COMING SOON HERO BANNER -->
<div class="ds-card p-8 relative overflow-hidden shadow-2xl mb-8 border border-border-subtle bg-gradient-to-br from-surface via-surface to-primary/5">
    <div class="absolute -right-24 -top-24 w-80 h-80 bg-primary/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -left-20 -bottom-20 w-72 h-72 bg-accent/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative z-10 max-w-3xl">
        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary/20 to-primary/5 text-primary flex items-center justify-center mb-6 border border-primary/20 shadow-lg">
            <span class="material-symbols-outlined text-3xl">settings_suggest</span>
        </div>

        <h2 class="text-3xl font-display font-black text-text-primary tracking-tight mb-3">
            Centralized Core Banking Configuration Engine
        </h2>
        <p class="text-text-secondary text-sm leading-relaxed mb-6">
            The unified system settings and institutional governance console is undergoing active architectural development. This engine will centralize real-time control over core transaction limits, interest rate schedules, multi-factor security rules, automated database snapshots, and tenant-wide maintenance windows.
        </p>

        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-background/80 border border-border-subtle text-xs font-semibold text-text-secondary">
                <span class="material-symbols-outlined text-emerald-500 text-sm">check_circle</span>
                <span>Database Schemas &amp; Setting Keys Ready</span>
            </div>
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-background/80 border border-border-subtle text-xs font-semibold text-text-secondary">
                <span class="material-symbols-outlined text-primary text-sm">tune</span>
                <span>Dynamic Parameter Engine in Architecture Review</span>
            </div>
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-background/80 border border-border-subtle text-xs font-semibold text-text-secondary">
                <span class="material-symbols-outlined text-amber-500 text-sm">schedule</span>
                <span>Target Release: Q4 2026</span>
            </div>
        </div>
    </div>
</div>

<!-- SYSTEM STATUS KPI CARDS -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8 relative z-10">
    <div class="ds-card p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center border border-primary/20 shrink-0">
            <span class="material-symbols-outlined text-2xl" aria-hidden="true">tune</span>
        </div>
        <div>
            <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest">Active Parameters</div>
            <div class="text-xl font-black font-display text-text-primary">Managed via Config</div>
        </div>
    </div>
    <div class="ds-card p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center border border-emerald-500/20 shrink-0">
            <span class="material-symbols-outlined text-2xl" aria-hidden="true">verified_user</span>
        </div>
        <div>
            <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest">Platform Status</div>
            <div class="text-xl font-black font-display text-emerald-500">All Systems Operational</div>
        </div>
    </div>
    <div class="ds-card p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center border border-blue-500/20 shrink-0">
            <span class="material-symbols-outlined text-2xl" aria-hidden="true">cloud_sync</span>
        </div>
        <div>
            <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest">Disaster Recovery</div>
            <div class="text-xl font-black font-display text-text-primary">Automated Backup Ready</div>
        </div>
    </div>
</div>

<div class="flex flex-col lg:flex-row gap-6 lg:gap-8 relative z-10 mb-8">

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
            <div class="ds-card p-6 lg:p-8 relative overflow-hidden">
                <div class="border-b border-border-subtle pb-4 mb-6">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-info text-[24px]" aria-hidden="true">cloud_sync</span>
                        <h2 class="text-lg font-headline font-black text-text-primary">Disaster Recovery &amp; Backup</h2>
                    </div>
                    <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-1">Automated database snapshots and export mechanisms for business continuity</p>
                </div>
                <?= renderComingSoonPanel([
                    ['icon' => 'database', 'label' => 'Full Database Snapshot', 'desc' => 'Complete dump of all relational tables and ledger journals'],
                    ['icon' => 'schedule_send', 'label' => 'Automated Backup Schedule', 'desc' => 'Cron frequency for continuous off-site replication'],
                    ['icon' => 'security', 'label' => 'AES-256 Backup Encryption', 'desc' => 'Cryptographic key rotation for backup archive storage'],
                    ['icon' => 'history_toggle_drop_down', 'label' => 'Point-in-Time Recovery', 'desc' => 'WAL archiving for zero-data-loss rollback capability'],
                ]) ?>
                <?= renderPanelOverlay() ?>
            </div>
        </div>
    </div>
</div>

<!-- ARCHITECTURE BLUEPRINT FOR FUTURE IMPLEMENTATION -->
<div class="ds-card p-6 border border-border-subtle bg-surface/90">
    <div class="flex items-center gap-3 mb-4 pb-4 border-b border-border-subtle">
        <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
            <span class="material-symbols-outlined text-xl">developer_board</span>
        </div>
        <div>
            <h3 class="font-headline font-bold text-base text-text-primary">Implementation Specification</h3>
            <p class="text-xs text-text-secondary">Clean architecture blueprint for implementing this feature backend</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 text-xs">
        <div class="space-y-3">
            <div class="font-bold text-text-primary uppercase tracking-wider text-[11px]">Target API Contracts</div>
            <div class="p-3 rounded-lg bg-background border border-border-subtle font-mono text-[11px] space-y-1.5">
                <div class="text-emerald-500"><span class="font-bold">GET</span> /api/v1/admin/settings <span class="text-text-muted">(Fetch grouped system settings)</span></div>
                <div class="text-primary"><span class="font-bold">PUT/PATCH</span> /api/v1/admin/settings <span class="text-text-muted">(Bulk update parameters with audit log)</span></div>
                <div class="text-blue-500"><span class="font-bold">POST</span> /api/v1/admin/settings/backup <span class="text-text-muted">(Trigger manual disaster recovery snapshot)</span></div>
            </div>
        </div>

        <div class="space-y-3">
            <div class="font-bold text-text-primary uppercase tracking-wider text-[11px]">Decoupled Components</div>
            <ul class="space-y-1.5 text-text-secondary">
                <li class="flex items-center gap-2"><span class="material-symbols-outlined text-text-muted text-[14px]">chevron_right</span> <strong>Controller:</strong> <code class="text-text-primary">AdminSettingsController</code></li>
                <li class="flex items-center gap-2"><span class="material-symbols-outlined text-text-muted text-[14px]">chevron_right</span> <strong>Service:</strong> <code class="text-text-primary">SettingsService</code> implements <code class="text-text-primary">SettingsServiceInterface</code></li>
                <li class="flex items-center gap-2"><span class="material-symbols-outlined text-text-muted text-[14px]">chevron_right</span> <strong>Repository:</strong> <code class="text-text-primary">SettingsRepository</code> (mapped to DB table: <code class="text-text-primary">system_settings</code>)</li>
            </ul>
        </div>
    </div>
</div>

<?php
/**
 * Renders a grid of blurred mocked setting rows.
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
                <div class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-1">This configuration module is under active development</div>
            </div>
            <div class="px-4 py-2 rounded-full bg-primary/10 border border-primary/20 text-xs font-black text-primary uppercase tracking-widest">
                🚀 &nbsp;Launching Q4 2026
            </div>
        </div>
    </div>';
}

$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
