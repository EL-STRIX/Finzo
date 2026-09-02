<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin — Advanced Reporting';
// Admin portal guard — server-side authentication & role check
include __DIR__ . '/_guard.php';

$branchesList = [];
try {
    $branchStmt = \App\Shared\Core\Database::getInstance()->query("SELECT id, name, branch_code FROM branches WHERE status = 'active' ORDER BY name ASC");
    $branchesList = $branchStmt ? $branchStmt->fetchAll(PDO::FETCH_ASSOC) : [];
} catch (\Throwable $e) {
    $branchesList = [];
}

$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/admin-reports.js?v=' . time() . '"></script>';
ob_start();
?>

<!-- TOP PAGE HEADER -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 relative z-10">
    <div>
        <h1 class="text-2xl font-headline font-black text-text-primary tracking-tight">Advanced Reports</h1>
        <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-1">Financial Reconciliation, Demographics &amp; Regulatory Data Exports</p>
    </div>
    <div class="flex items-center gap-2 px-3.5 py-1.5 rounded-full border border-border-subtle bg-surface/80 shadow-sm text-xs font-bold text-text-secondary uppercase tracking-widest">
        <span class="material-symbols-outlined text-[16px] text-primary" aria-hidden="true">analytics</span>
        <span>Real-Time Telemetry</span>
    </div>
</div>

<!-- TOP KPI OVERVIEW CARDS -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 sm:mb-8 relative z-10">
    <div class="ds-card p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center border border-primary/20 shrink-0">
            <span class="material-symbols-outlined text-2xl" aria-hidden="true">account_balance</span>
        </div>
        <div>
            <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest">Reporting Scope</div>
            <div class="text-xl font-black font-display text-text-primary">Multi-Branch Ledger</div>
        </div>
    </div>
    <div class="ds-card p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center border border-emerald-500/20 shrink-0">
            <span class="material-symbols-outlined text-2xl" aria-hidden="true">receipt_long</span>
        </div>
        <div>
            <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest">Export Formats</div>
            <div class="text-xl font-black font-display text-emerald-500">CSV &amp; PDF Direct</div>
        </div>
    </div>
    <div class="ds-card p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center border border-blue-500/20 shrink-0">
            <span class="material-symbols-outlined text-2xl" aria-hidden="true">verified_user</span>
        </div>
        <div>
            <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest">Audit Compliance</div>
            <div class="text-xl font-black font-display text-text-primary">RBI Statutory Grade</div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 relative z-10">
    
    <!-- FINANCIAL SUMMARY REPORT CARD -->
    <div class="ds-card p-6 lg:p-8 flex flex-col justify-between">
        <div>
            <div class="border-b border-border-subtle pb-4 mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center border border-primary/20">
                        <span class="material-symbols-outlined text-2xl" aria-hidden="true">monetization_on</span>
                    </div>
                    <div>
                        <h2 class="text-lg font-headline font-black text-text-primary">Financial Summary Report</h2>
                        <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-0.5">Deposits, Withdrawals, Cash Flow &amp; Interest</p>
                    </div>
                </div>
            </div>
            
            <form id="form-financial-report" class="space-y-4" onsubmit="return false;">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="fin-label text-xs font-bold text-text-secondary uppercase tracking-wider block mb-1">Date From</label>
                        <input type="date" name="date_from" class="fin-input !h-10 !text-sm font-medium" required />
                    </div>
                    <div>
                        <label class="fin-label text-xs font-bold text-text-secondary uppercase tracking-wider block mb-1">Date To</label>
                        <input type="date" name="date_to" class="fin-input !h-10 !text-sm font-medium" required />
                    </div>
                </div>

                <!-- Custom Animated Dropdown: Branch Filter -->
                <div>
                    <label class="fin-label text-xs font-bold text-text-secondary uppercase tracking-wider block mb-1">Registered Branch</label>
                    <div class="finzo-dropdown relative w-full" id="financial-branch-dropdown">
                        <input type="hidden" name="branch_id" id="financial-branch-input" value="">
                        <div class="finzo-dropdown-trigger fin-input !flex items-center justify-between cursor-pointer !h-10 !py-2" id="financial-branch-trigger">
                            <span class="dropdown-label truncate text-sm" id="financial-branch-label">All Registered Branches (Consolidated)</span>
                            <span class="material-symbols-outlined chevron transition-transform duration-300 shrink-0" aria-hidden="true">expand_more</span>
                        </div>
                        <div class="finzo-dropdown-menu z-50 !max-h-60 overflow-y-auto" id="financial-branch-menu">
                            <div class="finzo-dropdown-option is-selected text-xs font-bold" data-value="">All Registered Branches (Consolidated)</div>
                            <?php foreach ($branchesList as $b): ?>
                                <div class="finzo-dropdown-option text-xs font-bold" data-value="<?= htmlspecialchars((string)$b['id']) ?>"><?= htmlspecialchars($b['name']) ?> (<?= htmlspecialchars($b['branch_code'] ?? 'BR') ?>)</div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <div class="pt-2 flex flex-col sm:flex-row gap-3">
                    <button type="button" class="btn btn-primary flex-1 btn-generate flex items-center justify-center gap-2 shadow-sm" data-type="financial">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">analytics</span>
                        <span>Generate Insights</span>
                    </button>
                    <button type="button" class="btn btn-secondary flex-1 btn-export-csv flex items-center justify-center gap-2" data-type="financial">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">download</span>
                        <span>Export CSV</span>
                    </button>
                </div>
            </form>
        </div>

        <div id="financial-result" class="mt-6 p-5 rounded-2xl bg-background border border-border-subtle hidden animate-fade-in">
            <div class="flex items-center justify-between mb-4 border-b border-border-subtle pb-3">
                <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Financial Metrics Output</span>
                </div>
                <button type="button" class="text-xs font-bold text-primary hover:underline flex items-center gap-1" onclick="window.print()">
                    <span class="material-symbols-outlined text-[14px]">print</span> Print Summary
                </button>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-sm" id="financial-metrics">
                <!-- Metrics populated dynamically -->
            </div>
        </div>
    </div>

    <!-- DEMOGRAPHICS & KYC REPORT CARD -->
    <div class="ds-card p-6 lg:p-8 flex flex-col justify-between">
        <div>
            <div class="border-b border-border-subtle pb-4 mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-info-bg text-info flex items-center justify-center border border-info/20">
                        <span class="material-symbols-outlined text-2xl" aria-hidden="true">public</span>
                    </div>
                    <div>
                        <h2 class="text-lg font-headline font-black text-text-primary">User Demographics &amp; KYC</h2>
                        <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-0.5">Account Types, Onboarding, Verification &amp; Branches</p>
                    </div>
                </div>
            </div>
            
            <form id="form-demographics-report" class="space-y-4" onsubmit="return false;">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="fin-label text-xs font-bold text-text-secondary uppercase tracking-wider block mb-1">Date From</label>
                        <input type="date" name="date_from" class="fin-input !h-10 !text-sm font-medium" required />
                    </div>
                    <div>
                        <label class="fin-label text-xs font-bold text-text-secondary uppercase tracking-wider block mb-1">Date To</label>
                        <input type="date" name="date_to" class="fin-input !h-10 !text-sm font-medium" required />
                    </div>
                </div>

                <!-- Custom Animated Dropdown: Status Filter -->
                <div>
                    <label class="fin-label text-xs font-bold text-text-secondary uppercase tracking-wider block mb-1">Customer Status</label>
                    <div class="finzo-dropdown relative w-full" id="demographics-status-dropdown">
                        <input type="hidden" name="status" id="demographics-status-input" value="">
                        <div class="finzo-dropdown-trigger fin-input !flex items-center justify-between cursor-pointer !h-10 !py-2" id="demographics-status-trigger">
                            <span class="dropdown-label truncate text-sm" id="demographics-status-label">All Statuses (Active &amp; Suspended)</span>
                            <span class="material-symbols-outlined chevron transition-transform duration-300 shrink-0" aria-hidden="true">expand_more</span>
                        </div>
                        <div class="finzo-dropdown-menu z-50 !max-h-60 overflow-y-auto" id="demographics-status-menu">
                            <div class="finzo-dropdown-option is-selected text-xs font-bold" data-value="">All Statuses (Active &amp; Suspended)</div>
                            <div class="finzo-dropdown-option text-xs font-bold" data-value="active">Active Only</div>
                            <div class="finzo-dropdown-option text-xs font-bold" data-value="suspended">Suspended Only</div>
                        </div>
                    </div>
                </div>
                
                <div class="pt-2 flex flex-col sm:flex-row gap-3">
                    <button type="button" class="btn btn-primary flex-1 !bg-info !border-info hover:!bg-info/90 btn-generate flex items-center justify-center gap-2 shadow-sm" data-type="demographics">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">insights</span>
                        <span>Generate Insights</span>
                    </button>
                    <button type="button" class="btn btn-secondary flex-1 !text-info !border-info hover:!bg-info hover:!text-white btn-export-csv flex items-center justify-center gap-2" data-type="demographics">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">download</span>
                        <span>Export CSV</span>
                    </button>
                </div>
            </form>
        </div>

        <div id="demographics-result" class="mt-6 p-5 rounded-2xl bg-background border border-border-subtle hidden animate-fade-in">
            <div class="flex items-center justify-between mb-4 border-b border-border-subtle pb-3">
                <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-info"></span>
                    <span>Demographics Breakdown</span>
                </div>
                <button type="button" class="text-xs font-bold text-info hover:underline flex items-center gap-1" onclick="window.print()">
                    <span class="material-symbols-outlined text-[14px]">print</span> Print Summary
                </button>
            </div>
            <div class="space-y-4" id="demographics-metrics">
                <!-- Metrics populated dynamically -->
            </div>
        </div>
    </div>

</div>

<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
