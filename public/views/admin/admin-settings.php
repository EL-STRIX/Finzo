<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin — System Settings';
// Admin portal guard — server-side authentication & role check
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
    <div class="flex items-center gap-3">
        <button id="btn-save-settings" class="btn btn-primary !px-6 flex items-center gap-2 shadow-sm hover:scale-[1.02] active:scale-[0.98] transition-all">
            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">save</span>
            <span class="font-bold">Save Changes</span>
        </button>
    </div>
</div>

<!-- SYSTEM STATUS CARDS -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 relative z-10">
    <div class="ds-card p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-primary-50 text-primary flex items-center justify-center border border-primary/20 shrink-0">
            <span class="material-symbols-outlined text-2xl" aria-hidden="true">tune</span>
        </div>
        <div>
            <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest">Active Parameters</div>
            <div class="text-xl font-black font-display text-text-primary" id="stat-total-settings">11 Configured</div>
        </div>
    </div>
    <div class="ds-card p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center border border-emerald-500/20 shrink-0">
            <span class="material-symbols-outlined text-2xl" aria-hidden="true">verified_user</span>
        </div>
        <div>
            <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest">Platform Status</div>
            <div class="text-xl font-black font-display text-emerald-500" id="stat-platform-status">Operational</div>
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
            <nav class="flex flex-row lg:flex-col gap-1 overflow-x-auto custom-scrollbar" id="settings-tabs">
                <button class="settings-tab active text-left px-4 py-3 rounded-xl flex items-center gap-3 transition-colors text-sm font-bold bg-primary-50 text-primary border border-primary/20" data-target="banking">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">account_balance</span>
                    <span>Banking</span>
                </button>
                <button class="settings-tab text-left px-4 py-3 rounded-xl flex items-center gap-3 transition-colors text-sm font-bold text-text-secondary hover:bg-background hover:text-text-primary" data-target="security">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">security</span>
                    <span>Security</span>
                </button>
                <button class="settings-tab text-left px-4 py-3 rounded-xl flex items-center gap-3 transition-colors text-sm font-bold text-text-secondary hover:bg-background hover:text-text-primary" data-target="application">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">app_settings_alt</span>
                    <span>Application</span>
                </button>
                <button class="settings-tab text-left px-4 py-3 rounded-xl flex items-center gap-3 transition-colors text-sm font-bold text-text-secondary hover:bg-background hover:text-text-primary" data-target="operational">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">manufacturing</span>
                    <span>Operational</span>
                </button>
                <button class="settings-tab text-left px-4 py-3 rounded-xl flex items-center gap-3 transition-colors text-sm font-bold text-text-secondary hover:bg-background hover:text-text-primary" data-target="backup">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">database</span>
                    <span>Data Backup</span>
                </button>
            </nav>
        </div>
    </div>

    <!-- SETTINGS PANELS -->
    <div class="flex-1 min-w-0">
        <form id="settings-form" class="space-y-6" onsubmit="return false;">
            
            <!-- Banking Panel -->
            <div id="settings-banking" class="settings-pane ds-card p-6 lg:p-8 space-y-6">
                <div class="border-b border-border-subtle pb-4 mb-6">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[22px]" aria-hidden="true">account_balance</span>
                        <h2 class="text-lg font-headline font-black text-text-primary">Banking Configuration</h2>
                    </div>
                    <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-1">Configure interest rates, transaction limits, and core banking rules</p>
                </div>
                <div class="settings-grid grid grid-cols-1 md:grid-cols-2 gap-6" id="grid-banking">
                    <div class="col-span-full py-8 text-center text-text-secondary"><span class="loader"></span></div>
                </div>
            </div>

            <!-- Security Panel -->
            <div id="settings-security" class="settings-pane ds-card p-6 lg:p-8 space-y-6 hidden">
                <div class="border-b border-border-subtle pb-4 mb-6">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-warning text-[22px]" aria-hidden="true">security</span>
                        <h2 class="text-lg font-headline font-black text-text-primary">Security Policies</h2>
                    </div>
                    <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-1">Configure authentication thresholds, lockouts, and administrative session timeouts</p>
                </div>
                <div class="settings-grid grid grid-cols-1 md:grid-cols-2 gap-6" id="grid-security"></div>
            </div>

            <!-- Application Panel -->
            <div id="settings-application" class="settings-pane ds-card p-6 lg:p-8 space-y-6 hidden">
                <div class="border-b border-border-subtle pb-4 mb-6">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-info text-[22px]" aria-hidden="true">app_settings_alt</span>
                        <h2 class="text-lg font-headline font-black text-text-primary">Application State</h2>
                    </div>
                    <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-1">Manage platform maintenance mode, default currency, and customer onboarding status</p>
                </div>
                <div class="settings-grid grid grid-cols-1 md:grid-cols-2 gap-6" id="grid-application"></div>
            </div>

            <!-- Operational Panel -->
            <div id="settings-operational" class="settings-pane ds-card p-6 lg:p-8 space-y-6 hidden">
                <div class="border-b border-border-subtle pb-4 mb-6">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-accent text-[22px]" aria-hidden="true">manufacturing</span>
                        <h2 class="text-lg font-headline font-black text-text-primary">Operational Parameters</h2>
                    </div>
                    <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-1">Configure background interest accrual, retention rules, and automated scheduled jobs</p>
                </div>
                <div class="settings-grid grid grid-cols-1 md:grid-cols-2 gap-6" id="grid-operational"></div>
            </div>

            <!-- Backup Panel -->
            <div id="settings-backup" class="settings-pane hidden space-y-6">
                <div class="ds-card p-6 lg:p-8">
                    <div class="border-b border-border-subtle pb-4 mb-6">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-info text-[24px]" aria-hidden="true">cloud_sync</span>
                            <h2 class="text-lg font-headline font-black text-text-primary">Disaster Recovery &amp; Backup</h2>
                        </div>
                        <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-1">Execute manual database snapshots or export structures for disaster recovery</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Full Backup -->
                        <div class="border border-border-subtle rounded-2xl p-5 hover:border-primary transition-all bg-background flex flex-col justify-between">
                            <div>
                                <div class="w-12 h-12 rounded-xl bg-primary-50 text-primary flex items-center justify-center mb-4 border border-primary/20">
                                    <span class="material-symbols-outlined text-2xl" aria-hidden="true">database</span>
                                </div>
                                <h3 class="font-headline font-black text-base text-text-primary">Full Database Snapshot</h3>
                                <p class="text-xs text-text-secondary mt-1 mb-6 leading-relaxed">Generates a complete SQL dump containing table schema, constraints, and all transactional data.</p>
                            </div>
                            <button type="button" class="btn btn-primary !w-full btn-trigger-backup flex items-center justify-center gap-2" data-type="full">
                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">download</span>
                                <span>Run Full Backup</span>
                            </button>
                        </div>
                        
                        <!-- Schema Only -->
                        <div class="border border-border-subtle rounded-2xl p-5 hover:border-info transition-all bg-background flex flex-col justify-between">
                            <div>
                                <div class="w-12 h-12 rounded-xl bg-info-bg text-info flex items-center justify-center mb-4 border border-info/20">
                                    <span class="material-symbols-outlined text-2xl" aria-hidden="true">schema</span>
                                </div>
                                <h3 class="font-headline font-black text-base text-text-primary">Schema Only</h3>
                                <p class="text-xs text-text-secondary mt-1 mb-6 leading-relaxed">Exports DDL table definitions, triggers, views, and foreign key relations without customer rows.</p>
                            </div>
                            <button type="button" class="btn btn-secondary !w-full !border-info !text-info hover:!bg-info hover:!text-white btn-trigger-backup flex items-center justify-center gap-2" data-type="schema">
                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">code</span>
                                <span>Backup Schema</span>
                            </button>
                        </div>

                        <!-- Data Only -->
                        <div class="border border-border-subtle rounded-2xl p-5 hover:border-success transition-all bg-background flex flex-col justify-between">
                            <div>
                                <div class="w-12 h-12 rounded-xl bg-success-bg text-success flex items-center justify-center mb-4 border border-success/20">
                                    <span class="material-symbols-outlined text-2xl" aria-hidden="true">data_object</span>
                                </div>
                                <h3 class="font-headline font-black text-base text-text-primary">Data Rows Only</h3>
                                <p class="text-xs text-text-secondary mt-1 mb-6 leading-relaxed">Exports raw table records via INSERT statements without modifying or re-creating existing schema.</p>
                            </div>
                            <button type="button" class="btn btn-secondary !w-full !border-success !text-success hover:!bg-success hover:!text-white btn-trigger-backup flex items-center justify-center gap-2" data-type="data">
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
                        All backup archives are written to <code>/storage/backups</code> and isolated from public HTTP access. Full database snapshots on high-concurrency environments are executed with consistent read locks.
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>

<!-- BACKUP CONFIRMATION MODAL -->
<div id="backup-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 hidden animate-fade-in">
    <div class="ds-card max-w-md w-full p-6 sm:p-8 space-y-6 shadow-2xl border border-border-subtle bg-surface animate-scale-up">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-info-bg text-info flex items-center justify-center border border-info/20 shrink-0">
                <span class="material-symbols-outlined text-2xl" aria-hidden="true">database</span>
            </div>
            <div>
                <h3 class="font-headline font-black text-lg text-text-primary">Trigger Database Snapshot</h3>
                <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-0.5">BCDR Snapshot Confirmation</p>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-background border border-border-subtle text-xs space-y-2">
            <div class="flex justify-between items-center text-text-secondary">
                <span>Backup Type:</span>
                <span id="backup-modal-type" class="font-mono font-black text-text-primary uppercase tracking-wider">FULL</span>
            </div>
            <div class="flex justify-between items-center text-text-secondary">
                <span>Destination:</span>
                <span class="font-mono font-bold text-text-primary">/storage/backups</span>
            </div>
        </div>

        <p class="text-xs text-text-secondary leading-relaxed">
            Are you sure you want to execute this database backup operation? The system will create an atomic timestamped SQL dump.
        </p>

        <div class="flex gap-3">
            <button type="button" id="backup-modal-cancel" class="btn btn-secondary flex-1">Abort</button>
            <button type="button" id="backup-modal-confirm" class="btn btn-primary flex-1 flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">cloud_sync</span>
                <span>Confirm Backup</span>
            </button>
        </div>
    </div>
</div>

<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
