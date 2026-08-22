<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin — Advanced Reporting';
$pageHeader = '
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-headline font-black text-text-primary tracking-tight">Advanced Reports</h1>
        <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-0.5">Generate financial and demographic insights</p>
    </div>
</div>
';
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/admin-reports.js"></script>';
ob_start();
?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    
    <!-- FINANCIAL SUMMARY REPORT -->
    <div class="ds-card p-6 lg:p-8 flex flex-col">
        <div class="border-b border-border-subtle pb-4 mb-6">
            <h2 class="text-lg font-headline font-black text-text-primary flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[24px]">monetization_on</span>
                Financial Summary Report
            </h2>
            <p class="text-sm text-text-muted mt-1">Generate a comprehensive summary of deposits, withdrawals, and interest accrued over a specific period.</p>
        </div>
        
        <form id="form-financial-report" class="space-y-5 flex-1">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="fin-label">Date From</label>
                    <input type="date" name="date_from" class="fin-input" required />
                </div>
                <div>
                    <label class="fin-label">Date To</label>
                    <input type="date" name="date_to" class="fin-input" required />
                </div>
            </div>
            <div>
                <label class="fin-label">Branch ID (Optional)</label>
                <input type="number" name="branch_id" class="fin-input" placeholder="Leave empty for all branches" />
            </div>
            
            <div class="pt-4 flex gap-3">
                <button type="submit" class="btn btn-primary flex-1 btn-generate" data-type="financial">Generate Report</button>
                <button type="button" class="btn btn-secondary flex-1 btn-generate-async" data-type="financial">Export CSV (Async)</button>
            </div>
        </form>

        <div id="financial-result" class="mt-6 p-4 rounded-xl bg-background border border-border-subtle hidden">
            <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-3">Report Results</div>
            <div class="grid grid-cols-2 gap-4 text-sm" id="financial-metrics">
                <!-- Metrics populated via JS -->
            </div>
        </div>
    </div>

    <!-- DEMOGRAPHICS REPORT -->
    <div class="ds-card p-6 lg:p-8 flex flex-col">
        <div class="border-b border-border-subtle pb-4 mb-6">
            <h2 class="text-lg font-headline font-black text-text-primary flex items-center gap-2">
                <span class="material-symbols-outlined text-info text-[24px]">public</span>
                User Demographics Report
            </h2>
            <p class="text-sm text-text-muted mt-1">Analyze user acquisition, KYC statuses, and demographic breakdowns.</p>
        </div>
        
        <form id="form-demographics-report" class="space-y-5 flex-1">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="fin-label">Date From</label>
                    <input type="date" name="date_from" class="fin-input" required />
                </div>
                <div>
                    <label class="fin-label">Date To</label>
                    <input type="date" name="date_to" class="fin-input" required />
                </div>
            </div>
            <div>
                <label class="fin-label">Account Status</label>
                <select name="status" class="fin-input">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="suspended">Suspended</option>
                    <option value="locked">Locked</option>
                </select>
            </div>
            
            <div class="pt-4 flex gap-3">
                <button type="submit" class="btn btn-primary flex-1 !bg-info !border-info hover:!bg-info/90 btn-generate" data-type="demographics">Generate Report</button>
                <button type="button" class="btn btn-secondary flex-1 !text-info !border-info hover:!bg-info hover:!text-white btn-generate-async" data-type="demographics">Export CSV (Async)</button>
            </div>
        </form>

        <div id="demographics-result" class="mt-6 p-4 rounded-xl bg-background border border-border-subtle hidden">
            <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-3">Report Results</div>
            <div class="grid grid-cols-2 gap-4 text-sm" id="demographics-metrics">
                <!-- Metrics populated via JS -->
            </div>
        </div>
    </div>

</div>

<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
