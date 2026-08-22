<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin — System Settings';
$pageHeader = '
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-headline font-black text-text-primary tracking-tight">System Settings</h1>
        <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-0.5">Configure global enterprise banking parameters</p>
    </div>
    <div class="flex items-center gap-3">
        <button id="btn-save-settings" class="btn btn-primary !px-6 flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">save</span>
            <span class="font-bold">Save Changes</span>
        </button>
    </div>
</div>
';
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/admin-settings.js"></script>';
ob_start();
?>

<div class="flex flex-col lg:flex-row gap-6 lg:gap-8">
    
    <!-- SETTINGS NAVIGATION -->
    <div class="w-full lg:w-64 shrink-0">
        <div class="ds-card sticky top-24">
            <nav class="flex flex-row lg:flex-col p-2 gap-1 overflow-x-auto custom-scrollbar" id="settings-tabs">
                <button class="settings-tab active text-left px-4 py-3 rounded-xl flex items-center gap-3 transition-colors text-sm font-bold bg-primary-50 text-primary" data-target="banking">
                    <span class="material-symbols-outlined text-[20px]">account_balance</span>
                    Banking
                </button>
                <button class="settings-tab text-left px-4 py-3 rounded-xl flex items-center gap-3 transition-colors text-sm font-bold text-text-secondary hover:bg-background hover:text-text-primary" data-target="security">
                    <span class="material-symbols-outlined text-[20px]">security</span>
                    Security
                </button>
                <button class="settings-tab text-left px-4 py-3 rounded-xl flex items-center gap-3 transition-colors text-sm font-bold text-text-secondary hover:bg-background hover:text-text-primary" data-target="application">
                    <span class="material-symbols-outlined text-[20px]">app_settings_alt</span>
                    Application
                </button>
                <button class="settings-tab text-left px-4 py-3 rounded-xl flex items-center gap-3 transition-colors text-sm font-bold text-text-secondary hover:bg-background hover:text-text-primary" data-target="operational">
                    <span class="material-symbols-outlined text-[20px]">manufacturing</span>
                    Operational
                </button>
                <button class="settings-tab text-left px-4 py-3 rounded-xl flex items-center gap-3 transition-colors text-sm font-bold text-text-secondary hover:bg-background hover:text-text-primary" data-target="backup">
                    <span class="material-symbols-outlined text-[20px]">database</span>
                    Data Backup
                </button>
            </nav>
        </div>
    </div>

    <!-- SETTINGS CONTENT -->
    <div class="flex-1 min-w-0">
        <form id="settings-form" class="space-y-6">
            
            <!-- Dynamic Category Containers -->
            <div id="settings-banking" class="settings-pane ds-card p-6 lg:p-8 space-y-6">
                <div class="border-b border-border-subtle pb-4 mb-6">
                    <h2 class="text-lg font-headline font-black text-text-primary">Banking Configuration</h2>
                    <p class="text-sm text-text-muted mt-1">Configure interest rates, transaction limits, and core banking rules.</p>
                </div>
                <div class="settings-grid grid grid-cols-1 md:grid-cols-2 gap-6" id="grid-banking">
                    <div class="col-span-full py-8 text-center text-text-muted"><span class="loader"></span></div>
                </div>
            </div>

            <div id="settings-security" class="settings-pane ds-card p-6 lg:p-8 space-y-6 hidden">
                <div class="border-b border-border-subtle pb-4 mb-6">
                    <h2 class="text-lg font-headline font-black text-text-primary">Security Policies</h2>
                    <p class="text-sm text-text-muted mt-1">Configure password rules, session timeouts, and KYC strictness.</p>
                </div>
                <div class="settings-grid grid grid-cols-1 md:grid-cols-2 gap-6" id="grid-security"></div>
            </div>

            <div id="settings-application" class="settings-pane ds-card p-6 lg:p-8 space-y-6 hidden">
                <div class="border-b border-border-subtle pb-4 mb-6">
                    <h2 class="text-lg font-headline font-black text-text-primary">Application State</h2>
                    <p class="text-sm text-text-muted mt-1">Manage system maintenance mode and general platform UI toggles.</p>
                </div>
                <div class="settings-grid grid grid-cols-1 md:grid-cols-2 gap-6" id="grid-application"></div>
            </div>

            <div id="settings-operational" class="settings-pane ds-card p-6 lg:p-8 space-y-6 hidden">
                <div class="border-b border-border-subtle pb-4 mb-6">
                    <h2 class="text-lg font-headline font-black text-text-primary">Operational Parameters</h2>
                    <p class="text-sm text-text-muted mt-1">Configure cron job timings and automated operational tasks.</p>
                </div>
                <div class="settings-grid grid grid-cols-1 md:grid-cols-2 gap-6" id="grid-operational"></div>
            </div>

            <!-- BACKUP WIDGET (Not part of the dynamic form) -->
            <div id="settings-backup" class="settings-pane hidden space-y-6">
                <div class="ds-card p-6 lg:p-8">
                    <div class="border-b border-border-subtle pb-4 mb-6">
                        <h2 class="text-lg font-headline font-black text-text-primary flex items-center gap-2">
                            <span class="material-symbols-outlined text-info text-[24px]">cloud_sync</span>
                            Disaster Recovery & Backup
                        </h2>
                        <p class="text-sm text-text-muted mt-1">Manually trigger database snapshots or download schema structures.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Full Backup -->
                        <div class="border border-border-subtle rounded-2xl p-5 hover:border-primary transition-colors bg-background">
                            <div class="w-12 h-12 rounded-xl bg-primary-50 text-primary flex items-center justify-center mb-4">
                                <span class="material-symbols-outlined">database</span>
                            </div>
                            <h3 class="font-bold text-text-primary">Full Snapshot</h3>
                            <p class="text-xs text-text-muted mt-1 mb-4 h-8">Creates a complete SQL dump of schema and all production data.</p>
                            <button type="button" class="btn btn-primary !w-full btn-trigger-backup" data-type="full">Run Full Backup</button>
                        </div>
                        
                        <!-- Schema Only -->
                        <div class="border border-border-subtle rounded-2xl p-5 hover:border-info transition-colors bg-background">
                            <div class="w-12 h-12 rounded-xl bg-info-bg text-info flex items-center justify-center mb-4">
                                <span class="material-symbols-outlined">schema</span>
                            </div>
                            <h3 class="font-bold text-text-primary">Schema Only</h3>
                            <p class="text-xs text-text-muted mt-1 mb-4 h-8">Exports only table structures, triggers, and foreign keys.</p>
                            <button type="button" class="btn btn-secondary !w-full !border-info !text-info hover:!bg-info hover:!text-white btn-trigger-backup" data-type="schema">Backup Schema</button>
                        </div>

                        <!-- Data Only -->
                        <div class="border border-border-subtle rounded-2xl p-5 hover:border-success transition-colors bg-background">
                            <div class="w-12 h-12 rounded-xl bg-success-bg text-success flex items-center justify-center mb-4">
                                <span class="material-symbols-outlined">data_object</span>
                            </div>
                            <h3 class="font-bold text-text-primary">Data Only</h3>
                            <p class="text-xs text-text-muted mt-1 mb-4 h-8">Exports raw row data via INSERT statements without schema creation.</p>
                            <button type="button" class="btn btn-secondary !w-full !border-success !text-success hover:!bg-success hover:!text-white btn-trigger-backup" data-type="data">Backup Data</button>
                        </div>
                    </div>
                </div>
                
                <div class="p-4 rounded-xl bg-warning-bg border border-warning/30 flex gap-3 text-warning">
                    <span class="material-symbols-outlined shrink-0">info</span>
                    <div class="text-sm">
                        <strong class="font-bold block mb-1">Backup Storage Location</strong>
                        Backups are written securely to the <code>/storage/backups</code> directory and are restricted from public web access. A full snapshot can take up to 2 minutes on large datasets.
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>

<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
