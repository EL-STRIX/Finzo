<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin — General Ledger';
$pageHeader = '
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-headline font-black text-text-primary tracking-tight">General Ledger</h1>
        <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-1">Chart of accounts & journal entries</p>
    </div>
    <div class="flex items-center gap-3">
        <button id="btn-close-period" class="btn btn-secondary !px-5 !text-warning !border-warning hover:!bg-warning hover:!text-white flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">event_available</span>
            <span class="font-bold">Close Accounting Period</span>
        </button>
    </div>
</div>
';
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/admin-ledger.js"></script>';
ob_start();
?>

<!-- ACTIVE PERIOD BANNER -->
<div class="ds-card p-5 mb-6 bg-primary-50 border-primary-100 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-primary text-white flex items-center justify-center">
            <span class="material-symbols-outlined text-xl">calendar_month</span>
        </div>
        <div>
            <div class="text-[10px] font-black text-primary uppercase tracking-widest mb-1">Active Accounting Period</div>
            <div id="active-period-name" class="font-bold text-text-primary skeleton min-w-[150px]">Loading...</div>
        </div>
    </div>
    <div class="text-right">
        <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1">Status</div>
        <span class="status-badge badge-success">OPEN</span>
    </div>
</div>

<div class="flex flex-col lg:flex-row gap-6">
    
    <!-- CHART OF ACCOUNTS (Left Sidebar) -->
    <div class="w-full lg:w-1/3 shrink-0">
        <div class="ds-card h-[600px] flex flex-col">
            <div class="p-4 border-b border-border-subtle bg-background">
                <h3 class="font-headline font-black tracking-tight flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[20px]">account_tree</span>
                    Chart of Accounts
                </h3>
            </div>
            <div class="p-4 flex-1 overflow-y-auto custom-scrollbar">
                <div id="coa-tree" class="space-y-4">
                    <div class="text-center py-8 text-text-muted"><span class="loader"></span></div>
                </div>
            </div>
        </div>
    </div>

    <!-- JOURNAL ENTRIES (Main Content) -->
    <div class="w-full lg:w-2/3">
        <div class="ds-card h-[600px] flex flex-col">
            <div class="p-4 border-b border-border-subtle bg-background flex justify-between items-center">
                <h3 class="font-headline font-black tracking-tight flex items-center gap-2">
                    <span class="material-symbols-outlined text-info text-[20px]">receipt_long</span>
                    Recent Journal Entries
                </h3>
                <div class="text-xs text-text-muted">Showing latest 50 entries</div>
            </div>
            
            <div class="flex-1 overflow-y-auto custom-scrollbar">
                <div class="ds-table-wrapper border-0 rounded-none h-full">
                    <table class="ds-table">
                        <thead class="sticky top-0 z-10 bg-white dark:bg-surface">
                            <tr>
                                <th>Transaction ID</th>
                                <th>Date</th>
                                <th>Account</th>
                                <th class="text-right text-success">Debit (DR)</th>
                                <th class="text-right text-error">Credit (CR)</th>
                            </tr>
                        </thead>
                        <tbody id="journals-tbody">
                            <tr><td colspan="5" class="py-12 text-center"><span class="loader"></span></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
