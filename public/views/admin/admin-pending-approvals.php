<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin — Pending Account Approvals';
// Admin portal guard — server-side authentication & role check
include __DIR__ . '/_guard.php';
if (!defined('FINZO_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}
\App\Shared\Security\SessionManager::start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
ob_start();
?>

<div class="space-y-6">

    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- 1. BREADCRUMBS & PAGE HEADER                                    -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-border-subtle">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-bold text-text-secondary uppercase tracking-wider mb-1">
                <span>Administration</span>
                <span>/</span>
                <span>Operations</span>
                <span>/</span>
                <span class="text-text-primary font-bold">Pending Approvals</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-headline font-bold text-text-primary tracking-tight">Pending Account Approvals</h1>
            <p class="text-xs text-text-secondary mt-0.5">Review, verify submitted KYC documentation, and authorize new customer banking accounts.</p>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            <button type="button" onclick="window.PendingApprovals && window.PendingApprovals.refresh()" class="px-3.5 py-2 text-xs font-semibold rounded-lg bg-surface text-text-primary hover:bg-surface-hover border border-border-subtle shadow-xs inline-flex items-center gap-1.5 transition-colors cursor-pointer" title="Refresh Application Queue">
                <span class="material-symbols-outlined text-[16px]" aria-hidden="true">refresh</span>
                <span>Refresh</span>
            </button>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- 2. STAT TELEMETRY CARDS                                         -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        <!-- Card 1: Pending Review -->
        <div class="ds-card p-5 bg-surface border border-border-subtle rounded-xl shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[24px]" aria-hidden="true">hourglass_empty</span>
            </div>
            <div class="min-w-0">
                <div class="admin-stat-label text-[10px] font-bold uppercase tracking-wider text-text-secondary">PENDING REVIEW</div>
                <div class="text-xl sm:text-2xl font-headline font-bold text-text-primary mt-0.5 tracking-tight" id="stat-pending-review">—</div>
                <div class="text-[10px] text-text-secondary truncate mt-0.5">Awaiting verification</div>
            </div>
        </div>

        <!-- Card 2: Under Review -->
        <div class="ds-card p-5 bg-surface border border-border-subtle rounded-xl shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[24px]" aria-hidden="true">rate_review</span>
            </div>
            <div class="min-w-0">
                <div class="admin-stat-label text-[10px] font-bold uppercase tracking-wider text-text-secondary">UNDER REVIEW</div>
                <div class="text-xl sm:text-2xl font-headline font-bold text-text-primary mt-0.5 tracking-tight" id="stat-under-review">—</div>
                <div class="text-[10px] text-text-secondary truncate mt-0.5">Active inspection</div>
            </div>
        </div>

        <!-- Card 3: Approved Today -->
        <div class="ds-card p-5 bg-surface border border-border-subtle rounded-xl shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[24px]" aria-hidden="true">check_circle</span>
            </div>
            <div class="min-w-0">
                <div class="admin-stat-label text-[10px] font-bold uppercase tracking-wider text-text-secondary">APPROVED TODAY</div>
                <div class="text-xl sm:text-2xl font-headline font-bold text-text-primary mt-0.5 tracking-tight" id="stat-approved-today">—</div>
                <div class="text-[10px] text-text-secondary truncate mt-0.5">Authorized today</div>
            </div>
        </div>

        <!-- Card 4: Total Approved -->
        <div class="ds-card p-5 bg-surface border border-border-subtle rounded-xl shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-zinc-500/10 text-zinc-700 dark:text-zinc-300 border border-border-subtle flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[24px]" aria-hidden="true">verified</span>
            </div>
            <div class="min-w-0">
                <div class="admin-stat-label text-[10px] font-bold uppercase tracking-wider text-text-secondary">TOTAL APPROVED</div>
                <div class="text-xl sm:text-2xl font-headline font-bold text-text-primary mt-0.5 tracking-tight" id="stat-total-approved">—</div>
                <div class="text-[10px] text-text-secondary truncate mt-0.5">Total active accounts</div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- 3. QUEUE CONTAINER (SEARCH + TABS + DATA TABLE)                 -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <div class="ds-card bg-surface border border-border-subtle rounded-xl shadow-xs overflow-hidden">
        
        <!-- Controls Toolbar -->
        <div class="p-4 sm:p-5 border-b border-border-subtle bg-surface-subtle flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
            <!-- Search -->
            <div class="w-full md:w-96">
                <label for="search-pending" class="sr-only">Search Applications</label>
                <div class="fin-input-wrapper">
                    <span class="material-symbols-outlined input-icon-left text-text-secondary">search</span>
                    <input type="text" id="search-pending" placeholder="Search by name, customer ID, email, or phone..." class="fin-input has-icon-left bg-surface text-text-primary border-border-subtle text-xs rounded-lg">
                </div>
            </div>

            <!-- Status Tabs -->
            <div class="flex items-center gap-1.5 overflow-x-auto custom-scrollbar">
                <button type="button" class="tab-btn px-3.5 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider transition-all cursor-pointer bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 shadow-xs" data-status="all">
                    All Applications
                </button>
                <button type="button" class="tab-btn px-3.5 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider transition-all cursor-pointer bg-surface text-text-secondary hover:text-text-primary hover:bg-surface-hover border border-border-subtle" data-status="pending">
                    <span class="material-symbols-outlined text-[14px] align-[-2px] mr-1" aria-hidden="true">hourglass_empty</span>
                    Pending Review
                </button>
                <button type="button" class="tab-btn px-3.5 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider transition-all cursor-pointer bg-surface text-text-secondary hover:text-text-primary hover:bg-surface-hover border border-border-subtle" data-status="under_review">
                    <span class="material-symbols-outlined text-[14px] align-[-2px] mr-1" aria-hidden="true">rate_review</span>
                    Under Review
                </button>
            </div>
        </div>

        <!-- Empty State Container -->
        <div id="pending-empty" class="hidden p-12 flex flex-col items-center justify-center text-center">
            <div class="w-14 h-14 rounded-2xl bg-surface-subtle border border-border-subtle flex items-center justify-center text-text-secondary mb-3 shadow-2xs">
                <span class="material-symbols-outlined text-3xl" aria-hidden="true">inbox</span>
            </div>
            <div class="text-sm font-bold text-text-primary">No Pending Applications Found</div>
            <p class="text-xs text-text-secondary max-w-sm mt-1">All account applications have been processed or no applicants match your search filters.</p>
        </div>

        <!-- Approvals Table -->
        <div class="ds-table-wrapper w-full border-0 shadow-none rounded-none bg-transparent overflow-x-auto">
            <table id="approvals-table" class="ds-table w-full text-left min-w-[1100px]">
                <thead class="bg-surface-subtle border-b border-border-subtle">
                    <tr>
                        <th class="py-2.5 px-4 text-[10px] font-bold text-text-secondary uppercase tracking-wider w-12 text-center" scope="col">#</th>
                        <th class="py-2.5 px-5 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Applicant</th>
                        <th class="py-2.5 px-4 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Phone</th>
                        <th class="py-2.5 px-4 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Account Type</th>
                        <th class="py-2.5 px-4 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">ID Document</th>
                        <th class="py-2.5 px-4 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Date Applied</th>
                        <th class="py-2.5 px-4 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Status</th>
                        <th class="py-2.5 px-5 text-right text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Action</th>
                    </tr>
                </thead>
                <tbody id="pending-tbody" class="divide-y divide-border-subtle text-xs">
                    <tr>
                        <td colspan="8" class="text-center italic opacity-60 py-12 text-xs">Loading pending applications queue...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination Bar -->
        <div class="p-4 sm:p-5 bg-surface-subtle border-t border-border-subtle flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-3">
                <span class="text-xs font-bold text-text-primary uppercase tracking-wider">Queue: <span id="pending-count" class="font-mono text-xs px-1.5 py-0.5 rounded bg-surface border border-border-subtle">0</span></span>
                <span class="text-text-secondary text-xs">•</span>
                <span id="pagination-info" class="text-xs text-text-secondary">Showing 0 to 0 of 0</span>
            </div>
            
            <div class="flex items-center gap-2">
                <button id="prev-btn" class="px-3.5 py-1.5 bg-surface border border-border-subtle rounded-lg text-xs font-semibold text-text-primary hover:bg-surface-hover disabled:opacity-40 disabled:cursor-not-allowed transition-colors cursor-pointer shadow-2xs">
                    Previous
                </button>
                <div id="page-numbers" class="flex gap-1">
                    <!-- Pagination buttons injected by JS -->
                </div>
                <button id="next-btn" class="px-3.5 py-1.5 bg-surface border border-border-subtle rounded-lg text-xs font-semibold text-text-primary hover:bg-surface-hover disabled:opacity-40 disabled:cursor-not-allowed transition-colors cursor-pointer shadow-2xs">
                    Next
                </button>
            </div>
        </div>

    </div>

</div>

<?php
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/pending-approvals.js"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
