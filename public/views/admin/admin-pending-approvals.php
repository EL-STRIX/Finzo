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

// Pre-load all pending approvals directly in PHP for instant zero-latency page display!
$initialApps = [];
$initialStats = [
    'pending_review' => 0,
    'under_review' => 0,
    'approved_today' => 0,
    'total_approved' => 0
];
$initialTotal = 0;

try {
    $repo = new \App\Infrastructure\Repositories\AdminOperationsRepository();
    $service = new \App\Application\Services\AdminOperationsService($repo);
    $preData = $service->getPendingApprovals([
        'status'       => 'all',
        'search'       => '',
        'account_type' => 'all',
        'id_type'      => 'all',
        'sort'         => 'newest'
    ], 1, 50);
    $initialApps = $preData['applications'] ?? [];
    $initialStats = $preData['stats'] ?? $initialStats;
    $initialTotal = $preData['total'] ?? count($initialApps);
} catch (\Throwable $e) {
    // Graceful fallback
}

ob_start();
?>

<div class="space-y-6">

    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- 1. BREADCRUMBS & PAGE HEADER                                    -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-border-subtle">
        <div>
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
    <!-- 2. STAT TELEMETRY CARDS (INTERACTIVE FILTERS)                   -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        <!-- Card 1: Pending Review -->
        <div class="stat-filter-card ds-card p-5 bg-surface border border-border-subtle hover:border-amber-500/50 rounded-xl shadow-xs flex items-center gap-4 cursor-pointer transition-all hover:scale-[1.01]" data-status="pending" title="Click to filter by Pending Review">
            <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[24px]" aria-hidden="true">hourglass_empty</span>
            </div>
            <div class="min-w-0">
                <div class="admin-stat-label text-[10px] font-bold uppercase tracking-wider text-text-secondary">PENDING REVIEW</div>
                <div class="text-xl sm:text-2xl font-headline font-bold text-text-primary mt-0.5 tracking-tight" id="stat-pending-review"><?= (int)($initialStats['pending_review'] ?? 0) ?></div>
                <div class="text-[10px] text-text-secondary truncate mt-0.5">Awaiting verification</div>
            </div>
        </div>

        <!-- Card 2: Under Review -->
        <div class="stat-filter-card ds-card p-5 bg-surface border border-border-subtle hover:border-blue-500/50 rounded-xl shadow-xs flex items-center gap-4 cursor-pointer transition-all hover:scale-[1.01]" data-status="under_review" title="Click to filter by Under Review">
            <div class="w-12 h-12 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[24px]" aria-hidden="true">rate_review</span>
            </div>
            <div class="min-w-0">
                <div class="admin-stat-label text-[10px] font-bold uppercase tracking-wider text-text-secondary">UNDER REVIEW</div>
                <div class="text-xl sm:text-2xl font-headline font-bold text-text-primary mt-0.5 tracking-tight" id="stat-under-review"><?= (int)($initialStats['under_review'] ?? 0) ?></div>
                <div class="text-[10px] text-text-secondary truncate mt-0.5">Active inspection</div>
            </div>
        </div>

        <!-- Card 3: Approved Today -->
        <div class="stat-filter-card ds-card p-5 bg-surface border border-border-subtle hover:border-emerald-500/50 rounded-xl shadow-xs flex items-center gap-4 cursor-pointer transition-all hover:scale-[1.01]" data-status="approved" title="Click to filter by Approved">
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[24px]" aria-hidden="true">check_circle</span>
            </div>
            <div class="min-w-0">
                <div class="admin-stat-label text-[10px] font-bold uppercase tracking-wider text-text-secondary">APPROVED TODAY</div>
                <div class="text-xl sm:text-2xl font-headline font-bold text-text-primary mt-0.5 tracking-tight" id="stat-approved-today"><?= (int)($initialStats['approved_today'] ?? 0) ?></div>
                <div class="text-[10px] text-text-secondary truncate mt-0.5">Authorized today</div>
            </div>
        </div>

        <!-- Card 4: Total Approved -->
        <div class="stat-filter-card ds-card p-5 bg-surface border border-border-subtle hover:border-zinc-500/50 rounded-xl shadow-xs flex items-center gap-4 cursor-pointer transition-all hover:scale-[1.01]" data-status="approved" title="Click to filter by Approved">
            <div class="w-12 h-12 rounded-xl bg-zinc-500/10 text-zinc-700 dark:text-zinc-300 border border-border-subtle flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[24px]" aria-hidden="true">verified</span>
            </div>
            <div class="min-w-0">
                <div class="admin-stat-label text-[10px] font-bold uppercase tracking-wider text-text-secondary">TOTAL APPROVED</div>
                <div class="text-xl sm:text-2xl font-headline font-bold text-text-primary mt-0.5 tracking-tight" id="stat-total-approved"><?= (int)($initialStats['total_approved'] ?? 0) ?></div>
                <div class="text-[10px] text-text-secondary truncate mt-0.5">Total active accounts</div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- 3. QUEUE CONTAINER (COMPREHENSIVE FILTERS + DATA TABLE)          -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <div class="ds-card bg-surface border border-border-subtle rounded-xl shadow-xs overflow-hidden">
        
        <!-- Controls Toolbar: Search + Status Tabs -->
        <div class="p-4 sm:p-5 border-b border-border-subtle bg-surface-subtle flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
            <!-- Search -->
            <div class="w-full md:w-96">
                <label for="search-pending" class="sr-only">Search Applications</label>
                <div class="fin-input-wrapper relative">
                    <span class="material-symbols-outlined input-icon-left text-text-secondary">search</span>
                    <input type="text" id="search-pending" placeholder="Search by name, customer ID, email, or phone..." class="fin-input has-icon-left bg-surface text-text-primary border-border-subtle text-xs rounded-lg pr-8">
                    <button type="button" id="btn-clear-search" class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-text-secondary hover:text-text-primary" title="Clear search">
                        <span class="material-symbols-outlined text-[16px]">close</span>
                    </button>
                </div>
            </div>

            <!-- Status Tabs -->
            <div class="flex items-center gap-1.5 overflow-x-auto custom-scrollbar">
                <button type="button" class="tab-btn px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider transition-all cursor-pointer bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 shadow-xs" data-status="all">
                    All Pending
                </button>
                <button type="button" class="tab-btn px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider transition-all cursor-pointer bg-surface text-text-secondary hover:text-text-primary hover:bg-surface-hover border border-border-subtle" data-status="pending">
                    <span class="material-symbols-outlined text-[14px] align-[-2px] mr-1" aria-hidden="true">hourglass_empty</span>
                    Pending Review
                </button>
                <button type="button" class="tab-btn px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider transition-all cursor-pointer bg-surface text-text-secondary hover:text-text-primary hover:bg-surface-hover border border-border-subtle" data-status="under_review">
                    <span class="material-symbols-outlined text-[14px] align-[-2px] mr-1" aria-hidden="true">rate_review</span>
                    Under Review
                </button>
                <button type="button" class="tab-btn px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider transition-all cursor-pointer bg-surface text-text-secondary hover:text-text-primary hover:bg-surface-hover border border-border-subtle" data-status="approved">
                    <span class="material-symbols-outlined text-[14px] align-[-2px] mr-1" aria-hidden="true">check_circle</span>
                    Approved
                </button>
                <button type="button" class="tab-btn px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider transition-all cursor-pointer bg-surface text-text-secondary hover:text-text-primary hover:bg-surface-hover border border-border-subtle" data-status="rejected">
                    <span class="material-symbols-outlined text-[14px] align-[-2px] mr-1" aria-hidden="true">cancel</span>
                    Rejected
                </button>
                <button type="button" class="tab-btn px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider transition-all cursor-pointer bg-surface text-text-secondary hover:text-text-primary hover:bg-surface-hover border border-border-subtle" data-status="all_records">
                    All Records
                </button>
            </div>
        </div>

        <!-- Filter Sub-bar: Account Type + ID Document + Sort Order + Reset -->
        <div class="px-4 sm:px-5 py-3 border-b border-border-subtle bg-surface flex flex-wrap items-center justify-between gap-3 text-xs">
            <div class="flex flex-wrap items-center gap-3">
                <!-- Account Type Filter -->
                <div class="flex items-center gap-1.5">
                    <label for="filter-account-type" class="text-[11px] font-bold text-text-secondary uppercase tracking-wider">Account:</label>
                    <select id="filter-account-type" class="bg-surface-subtle text-text-primary border border-border-subtle rounded-lg px-2.5 py-1.5 text-xs font-medium cursor-pointer focus:outline-none focus:border-primary">
                        <option value="all">All Accounts</option>
                        <option value="savings">Savings Account</option>
                        <option value="current">Current Account</option>
                    </select>
                </div>

                <!-- ID Document Filter -->
                <div class="flex items-center gap-1.5">
                    <label for="filter-id-type" class="text-[11px] font-bold text-text-secondary uppercase tracking-wider">Document:</label>
                    <select id="filter-id-type" class="bg-surface-subtle text-text-primary border border-border-subtle rounded-lg px-2.5 py-1.5 text-xs font-medium cursor-pointer focus:outline-none focus:border-primary">
                        <option value="all">All ID Types</option>
                        <option value="aadhaar">Aadhaar Card</option>
                        <option value="pan">PAN Card</option>
                        <option value="passport">Passport</option>
                        <option value="voter">Voter ID</option>
                        <option value="driving">Driving License</option>
                    </select>
                </div>

                <!-- Sort Order Filter -->
                <div class="flex items-center gap-1.5">
                    <label for="filter-sort" class="text-[11px] font-bold text-text-secondary uppercase tracking-wider">Sort:</label>
                    <select id="filter-sort" class="bg-surface-subtle text-text-primary border border-border-subtle rounded-lg px-2.5 py-1.5 text-xs font-medium cursor-pointer focus:outline-none focus:border-primary">
                        <option value="newest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                        <option value="name_asc">Name (A-Z)</option>
                        <option value="name_desc">Name (Z-A)</option>
                    </select>
                </div>
            </div>

            <!-- Reset Filters -->
            <div>
                <button type="button" id="btn-reset-filters" class="text-xs text-text-secondary hover:text-text-primary font-semibold inline-flex items-center gap-1 transition-colors cursor-pointer" title="Reset all filters">
                    <span class="material-symbols-outlined text-[14px]">restart_alt</span>
                    <span>Reset Filters</span>
                </button>
            </div>
        </div>

        <!-- Empty State Container -->
        <div id="pending-empty" class="<?= empty($initialApps) ? '' : 'hidden ' ?>p-12 flex flex-col items-center justify-center text-center">
            <div class="w-14 h-14 rounded-2xl bg-surface-subtle border border-border-subtle flex items-center justify-center text-text-secondary mb-3 shadow-2xs">
                <span class="material-symbols-outlined text-3xl" aria-hidden="true">inbox</span>
            </div>
            <div class="text-sm font-bold text-text-primary">No Applications Found</div>
            <p class="text-xs text-text-secondary max-w-sm mt-1">No customer applications match your active search and filter criteria. Try adjusting or resetting filters.</p>
            <button type="button" onclick="document.getElementById('btn-reset-filters') && document.getElementById('btn-reset-filters').click()" class="mt-3 px-3.5 py-1.5 rounded-lg text-xs font-bold bg-surface border border-border-subtle text-text-primary hover:bg-surface-hover transition-colors">
                Clear All Filters
            </button>
        </div>

        <!-- Approvals Table -->
        <div class="ds-table-wrapper w-full border-0 shadow-none rounded-none bg-transparent overflow-x-auto">
            <table id="approvals-table" class="<?= empty($initialApps) ? 'hidden ' : '' ?>ds-table w-full text-left min-w-[1100px]">
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
                    <?php if (!empty($initialApps)): ?>
                        <?php foreach ($initialApps as $i => $app): 
                            $rowNum = str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT);
                            $initials = strtoupper(substr($app['full_name'] ?? 'U', 0, 1));
                            $dateRaw = $app['kyc_submitted_at'] ?? $app['created_at'] ?? null;
                            $dateDisplay = $dateRaw ? date('d M Y, h:i A', strtotime($dateRaw)) : '—';
                            $accType = strtolower($app['account_type'] ?? 'savings');
                            $isSavings = str_contains($accType, 'saving');
                            $idType = !empty($app['id_type']) && $app['id_type'] !== 'N/A' ? htmlspecialchars($app['id_type']) : 'Not Uploaded';
                            $status = $app['status'] ?? 'pending';
                            $isUnderReview = ($status === 'under_review');
                            $isActive = ($status === 'active');
                            $isRejected = ($status === 'rejected');
                        ?>
                        <tr class="hover:bg-surface-hover/70 transition-colors">
                            <td class="py-3 px-4 text-center font-mono text-[11px] font-bold text-text-secondary opacity-60"><?= $rowNum ?></td>
                            <td class="py-3 px-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-surface-subtle border border-border-subtle flex items-center justify-center font-bold text-xs text-text-primary uppercase shrink-0 font-mono shadow-2xs"><?= $initials ?></div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="font-headline font-bold text-xs text-text-primary truncate"><?= htmlspecialchars($app['full_name'] ?? '—') ?></span>
                                            <span class="font-mono text-[10px] px-1.5 py-0.5 rounded bg-surface-subtle border border-border-subtle text-text-secondary font-semibold"><?= htmlspecialchars($app['customer_id'] ?? 'FNZ-USR-PENDING') ?></span>
                                        </div>
                                        <div class="text-[11px] text-text-secondary truncate mt-0.5"><?= htmlspecialchars($app['email'] ?? '—') ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-xs font-mono font-medium text-text-primary whitespace-nowrap">
                                <?= !empty($app['phone']) ? '<span class="inline-flex items-center gap-1"><span class="material-symbols-outlined text-[13px] text-text-secondary">call</span>' . htmlspecialchars($app['phone']) . '</span>' : '—' ?>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider <?= $isSavings ? 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20' : 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' ?>">
                                    <?= $isSavings ? 'Savings' : 'Current' ?>
                                </span>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="text-xs font-semibold text-text-primary"><?= $idType ?></span>
                            </td>
                            <td class="py-3 px-4 text-xs text-text-secondary whitespace-nowrap font-medium">
                                <?= $dateDisplay ?>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <?php if ($isActive): ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-[10px] font-bold uppercase tracking-wider">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Approved
                                    </span>
                                <?php elseif ($isRejected): ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20 text-[10px] font-bold uppercase tracking-wider">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Rejected
                                    </span>
                                <?php elseif ($isUnderReview): ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20 text-[10px] font-bold uppercase tracking-wider">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span> Under Review
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 text-[10px] font-bold uppercase tracking-wider">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Pending Review
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-5 text-right whitespace-nowrap">
                                <a href="admin-review-application.php?id=<?= (int)$app['id'] ?>" class="px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider inline-flex items-center gap-1.5 bg-zinc-900 text-white hover:bg-black dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100 shadow-xs transition-all cursor-pointer" title="Review &amp; Authorize Application">
                                    <span class="material-symbols-outlined text-[15px]" aria-hidden="true">rate_review</span>
                                    <span>Review</span>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center italic opacity-60 py-12 text-xs">Loading pending applications queue...</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Bar -->
        <div class="p-4 sm:p-5 bg-surface-subtle border-t border-border-subtle flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-3">
                <span class="text-xs font-bold text-text-primary uppercase tracking-wider">Queue: <span id="pending-count" class="font-mono text-xs px-1.5 py-0.5 rounded bg-surface border border-border-subtle"><?= (int)$initialTotal ?></span></span>
                <span class="text-text-secondary text-xs">•</span>
                <span id="pagination-info" class="text-xs text-text-secondary">Showing <?= $initialTotal > 0 ? 1 : 0 ?> to <?= count($initialApps) ?> of <?= (int)$initialTotal ?></span>
            </div>
            
            <div class="flex items-center gap-2">
                <button id="prev-btn" class="px-3.5 py-1.5 bg-surface border border-border-subtle rounded-lg text-xs font-semibold text-text-primary hover:bg-surface-hover disabled:opacity-40 disabled:cursor-not-allowed transition-colors cursor-pointer shadow-2xs" disabled>
                    Previous
                </button>
                <div id="page-numbers" class="flex gap-1">
                    <!-- Pagination buttons injected by JS -->
                </div>
                <button id="next-btn" class="px-3.5 py-1.5 bg-surface border border-border-subtle rounded-lg text-xs font-semibold text-text-primary hover:bg-surface-hover disabled:opacity-40 disabled:cursor-not-allowed transition-colors cursor-pointer shadow-2xs" <?= $initialTotal <= 50 ? 'disabled' : '' ?>>
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
