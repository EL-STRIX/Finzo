<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin — Loan Applications (Coming Soon)';
$pageHeader = '
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-warning/10 text-warning border border-warning/20">
                <span class="w-1.5 h-1.5 rounded-full bg-warning animate-pulse"></span>
                In Development
            </span>
            <span class="text-text-muted text-xs font-mono">Module: Underwriting 2.0</span>
        </div>
        <h1 class="text-2xl font-headline font-black text-text-primary tracking-tight">Loan Applications & Underwriting</h1>
        <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-1">Multi-stage credit appraisal, risk analysis, and fund disbursement workflow</p>
    </div>
    <div class="flex items-center gap-3">
        <button id="btn-notify-underwriting" class="btn btn-secondary !px-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">notifications_active</span>
            <span class="font-bold text-xs">Notify on Launch</span>
        </button>
    </div>
</div>
';
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/admin-loans.js"></script>';
ob_start();
?>

<!-- COMING SOON HERO BANNER -->
<div class="ds-card p-8 relative overflow-hidden shadow-2xl mb-8 border border-border-subtle bg-gradient-to-br from-surface via-surface to-warning/5">
    <div class="absolute -right-24 -top-24 w-80 h-80 bg-warning/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -left-20 -bottom-20 w-72 h-72 bg-primary/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative z-10 max-w-3xl">
        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-warning/20 to-warning/5 text-warning flex items-center justify-center mb-6 border border-warning/20 shadow-lg">
            <span class="material-symbols-outlined text-3xl">payments</span>
        </div>

        <h2 class="text-3xl font-display font-black text-text-primary tracking-tight mb-3">
            Digital Credit Underwriting Suite
        </h2>
        <p class="text-text-secondary text-sm leading-relaxed mb-6">
            The automated credit assessment and loan application processing dashboard is currently in active development. This pipeline will provide end-to-end loan adjudication, bureau verification scorecards, dual-control maker-checker approval controls, and instant disbursement to customer savings accounts.
        </p>

        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-background/80 border border-border-subtle text-xs font-semibold text-text-secondary">
                <span class="material-symbols-outlined text-emerald-500 text-sm">check_circle</span>
                <span>Database Schemas & Tables Ready</span>
            </div>
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-background/80 border border-border-subtle text-xs font-semibold text-text-secondary">
                <span class="material-symbols-outlined text-primary text-sm">architecture</span>
                <span>Maker-Checker Approval Engine in Architecture Review</span>
            </div>
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-background/80 border border-border-subtle text-xs font-semibold text-text-secondary">
                <span class="material-symbols-outlined text-amber-500 text-sm">schedule</span>
                <span>Target Release: Q4 2026</span>
            </div>
        </div>
    </div>
</div>

<!-- PLANNED CAPABILITIES GRID -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="ds-card p-6 border border-border-subtle hover:border-warning/40 transition-all group">
        <div class="w-12 h-12 rounded-xl bg-warning/10 text-warning flex items-center justify-center mb-4 group-hover:scale-105 transition-transform">
            <span class="material-symbols-outlined text-2xl">rule</span>
        </div>
        <h3 class="font-headline font-bold text-base text-text-primary mb-2">Automated Underwriting</h3>
        <p class="text-xs text-text-secondary leading-relaxed">
            Algorithmic debt-to-income (DTI) evaluation, existing repayment history analysis, and risk scoring to streamline instant low-value retail loan approvals.
        </p>
    </div>

    <div class="ds-card p-6 border border-border-subtle hover:border-warning/40 transition-all group">
        <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center mb-4 group-hover:scale-105 transition-transform">
            <span class="material-symbols-outlined text-2xl">account_balance_wallet</span>
        </div>
        <h3 class="font-headline font-bold text-base text-text-primary mb-2">Instant Fund Disbursement</h3>
        <p class="text-xs text-text-secondary leading-relaxed">
            Direct ledger posting of approved principal amounts directly into the borrower's savings account with automatic schedule amortization generation.
        </p>
    </div>

    <div class="ds-card p-6 border border-border-subtle hover:border-warning/40 transition-all group">
        <div class="w-12 h-12 rounded-xl bg-rose-500/10 text-rose-500 flex items-center justify-center mb-4 group-hover:scale-105 transition-transform">
            <span class="material-symbols-outlined text-2xl">gavel</span>
        </div>
        <h3 class="font-headline font-bold text-base text-text-primary mb-2">Delinquency & Foreclosure</h3>
        <p class="text-xs text-text-secondary leading-relaxed">
            Real-time tracking of overdue installments, grace periods, penalty interest accrual, and administrative foreclosure with dual-approval audit logs.
        </p>
    </div>
</div>

<!-- ARCHITECTURE BLUEPRINT FOR FUTURE IMPLEMENTATION -->
<div class="ds-card p-6 border border-border-subtle bg-surface/90">
    <div class="flex items-center gap-3 mb-4 pb-4 border-b border-border-subtle">
        <div class="w-10 h-10 rounded-xl bg-warning/10 text-warning flex items-center justify-center">
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
                <div class="text-emerald-500"><span class="font-bold">GET</span> /api/v1/admin/loan-applications <span class="text-text-muted">(Queue by status)</span></div>
                <div class="text-primary"><span class="font-bold">POST</span> /api/v1/admin/loan-applications/approve <span class="text-text-muted">(Maker-checker approve & disburse)</span></div>
                <div class="text-rose-500"><span class="font-bold">POST</span> /api/v1/admin/loan-applications/reject <span class="text-text-muted">(Decline with audit reason)</span></div>
                <div class="text-amber-500"><span class="font-bold">GET</span> /api/v1/admin/loans/{id}/schedules <span class="text-text-muted">(View installment records)</span></div>
                <div class="text-purple-500"><span class="font-bold">POST</span> /api/v1/admin/loans/{id}/foreclose <span class="text-text-muted">(Foreclosure execution)</span></div>
            </div>
        </div>

        <div class="space-y-3">
            <div class="font-bold text-text-primary uppercase tracking-wider text-[11px]">Decoupled Components</div>
            <ul class="space-y-1.5 text-text-secondary">
                <li class="flex items-center gap-2"><span class="material-symbols-outlined text-text-muted text-[14px]">chevron_right</span> <strong>Controller:</strong> <code class="text-text-primary">AdminLoanController</code></li>
                <li class="flex items-center gap-2"><span class="material-symbols-outlined text-text-muted text-[14px]">chevron_right</span> <strong>Service:</strong> <code class="text-text-primary">LoanService</code> implements <code class="text-text-primary">LoanServiceInterface</code></li>
                <li class="flex items-center gap-2"><span class="material-symbols-outlined text-text-muted text-[14px]">chevron_right</span> <strong>Repository:</strong> <code class="text-text-primary">LoanRepository</code> (tables: <code class="text-text-primary">loan_applications</code>, <code class="text-text-primary">loans</code>, <code class="text-text-primary">loan_schedules</code>)</li>
            </ul>
        </div>
    </div>
</div>

<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
