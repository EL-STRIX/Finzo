<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin — Loan Applications';
$pageHeader = '
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-headline font-black text-text-primary tracking-tight">Loan Applications</h1>
        <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-0.5">Review, approve, and manage user loans</p>
    </div>
</div>
';
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/admin-loans.js"></script>';
ob_start();
?>

<!-- STATS ROW -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 mb-6">
    <div class="ds-card p-5 relative overflow-hidden">
        <div class="absolute right-0 top-0 w-16 h-16 bg-warning-bg/50 rounded-bl-full -z-0"></div>
        <div class="relative z-10">
            <div class="w-10 h-10 rounded-xl bg-warning-bg text-warning flex items-center justify-center mb-4">
                <span class="material-symbols-outlined text-xl">pending_actions</span>
            </div>
            <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1">Pending Review</div>
            <div id="stat-pending" class="text-xl font-black font-mono tracking-tight skeleton min-w-[40px]">—</div>
        </div>
    </div>
    <div class="ds-card p-5 relative overflow-hidden">
        <div class="absolute right-0 top-0 w-16 h-16 bg-success-bg/50 rounded-bl-full -z-0"></div>
        <div class="relative z-10">
            <div class="w-10 h-10 rounded-xl bg-success-bg text-success flex items-center justify-center mb-4">
                <span class="material-symbols-outlined text-xl">verified</span>
            </div>
            <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1">Active Loans</div>
            <div id="stat-active" class="text-xl font-black font-mono tracking-tight skeleton min-w-[40px]">—</div>
        </div>
    </div>
    <div class="ds-card p-5 relative overflow-hidden">
        <div class="absolute right-0 top-0 w-16 h-16 bg-error-bg/50 rounded-bl-full -z-0"></div>
        <div class="relative z-10">
            <div class="w-10 h-10 rounded-xl bg-error-bg text-error flex items-center justify-center mb-4">
                <span class="material-symbols-outlined text-xl">gavel</span>
            </div>
            <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1">Defaulted / Foreclosed</div>
            <div id="stat-defaulted" class="text-xl font-black font-mono tracking-tight skeleton min-w-[40px]">—</div>
        </div>
    </div>
</div>

<!-- TABLE WRAPPER -->
<div class="ds-card ds-card-dropdown-fix relative z-20 overflow-hidden">
    <div class="p-4 sm:p-6 flex flex-col sm:flex-row items-center justify-between gap-4 border-b border-border-subtle bg-background">
        <div class="flex items-center gap-2">
            <select id="filter-status" class="fin-input !py-1.5 !h-9 !text-sm !w-40 cursor-pointer">
                <option value="all">All Applications</option>
                <option value="pending" selected>Pending Review</option>
                <option value="approved">Approved & Active</option>
                <option value="rejected">Rejected</option>
                <option value="foreclosed">Foreclosed</option>
                <option value="closed">Closed / Paid</option>
            </select>
        </div>
        <div class="relative w-full sm:w-64">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="material-symbols-outlined text-text-muted text-[18px]">search</span>
            </div>
            <input type="text" id="loan-search" class="fin-input !pl-9 !py-2 !h-9 !text-sm w-full bg-white dark:bg-surface" placeholder="Search loans..." />
        </div>
    </div>

    <!-- TABLE AREA -->
    <div class="ds-table-wrapper bg-white dark:bg-surface min-h-[300px]">
        <table class="ds-table">
            <thead>
                <tr>
                    <th class="w-16">ID</th>
                    <th>User / Account</th>
                    <th>Product</th>
                    <th>Principal Amount</th>
                    <th>Tenure</th>
                    <th>Date Applied</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody id="loans-tbody">
                <tr><td colspan="8" class="py-12 text-center"><span class="loader"></span></td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- REVIEW MODAL -->
<div id="review-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm modal-backdrop" onclick="document.getElementById('review-modal').classList.add('hidden')"></div>
    <div class="bg-surface border border-border-subtle rounded-2xl shadow-2xl w-full max-w-lg relative z-10 max-h-[90vh] flex flex-col">
        <div class="p-6 border-b border-border-subtle flex justify-between items-center bg-background rounded-t-2xl">
            <h3 class="font-headline font-black tracking-tight text-lg" id="review-modal-title">Review Application</h3>
            <button class="w-8 h-8 flex items-center justify-center rounded-lg bg-surface-subtle text-text-muted hover:text-text-primary hover:bg-border-subtle transition-colors" onclick="document.getElementById('review-modal').classList.add('hidden')">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
        <div class="p-6 overflow-y-auto custom-scrollbar flex-1 space-y-6">
            <input type="hidden" id="review-loan-id" />
            
            <div class="p-4 rounded-xl bg-background border border-border-subtle">
                <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-3">Application Details</div>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div><span class="text-text-muted">User:</span> <span id="review-user" class="font-bold"></span></div>
                    <div><span class="text-text-muted">Product:</span> <span id="review-product" class="font-bold"></span></div>
                    <div><span class="text-text-muted">Amount:</span> <span id="review-amount" class="font-bold text-primary"></span></div>
                    <div><span class="text-text-muted">Tenure:</span> <span id="review-tenure" class="font-bold"></span></div>
                </div>
            </div>

            <div>
                <label class="fin-label">Review Notes (Optional)</label>
                <textarea id="review-notes" class="fin-input" placeholder="Provide reason for approval/rejection..." rows="3"></textarea>
            </div>
            
        </div>
        <div class="p-6 border-t border-border-subtle bg-background rounded-b-2xl flex justify-between items-center gap-3">
            <button type="button" class="btn btn-secondary !px-5 !text-error !border-error hover:!bg-error hover:!text-white" id="btn-reject-loan">Reject</button>
            <button type="button" class="btn btn-primary !px-5 !bg-success !border-success hover:!bg-success/90" id="btn-approve-loan">Approve & Disburse</button>
        </div>
    </div>
</div>

<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
