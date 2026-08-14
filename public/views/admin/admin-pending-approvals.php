<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin - Pending Approvals';
$pageHeader = '
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-headline font-black text-text-primary tracking-tight">Pending Approvals</h1>
        <p class="text-[10px] font-black text-text-secondary uppercase tracking-widest mt-0.5">Review and action account applications awaiting approval</p>
    </div>
</div>
';
ob_start();
?>
<!-- STAT CARDS -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-6 mb-6 sm:mb-10">
                <div class="ds-card p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-warning-bg text-warning flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined" aria-hidden="true">hourglass_empty</span>
                    </div>
                    <div>
                        <div class="admin-stat-label text-[10px] font-black uppercase tracking-widest text-text-secondary">PENDING REVIEW</div>
                        <div class="text-xl font-black mt-1 text-text-primary skeleton min-w-[40px]">—</div>
                    </div>
                </div>
                <div class="ds-card p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-info-bg text-info flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined" aria-hidden="true">rate_review</span>
                    </div>
                    <div>
                        <div class="admin-stat-label text-[10px] font-black uppercase tracking-widest text-text-secondary">UNDER REVIEW</div>
                        <div class="text-xl font-black mt-1 text-text-primary skeleton min-w-[40px]">—</div>
                    </div>
                </div>
                <div class="ds-card p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-success-bg text-success flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined" aria-hidden="true">check_circle</span>
                    </div>
                    <div>
                        <div class="admin-stat-label text-[10px] font-black uppercase tracking-widest text-text-secondary">APPROVED TODAY</div>
                        <div class="text-xl font-black mt-1 text-text-primary skeleton min-w-[40px]">—</div>
                    </div>
                </div>
                <div class="ds-card p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-primary-50 text-primary flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined" aria-hidden="true">verified</span>
                    </div>
                    <div>
                        <div class="admin-stat-label text-[10px] font-black uppercase tracking-widest text-text-secondary">TOTAL APPROVED</div>
                        <div class="text-xl font-black mt-1 text-text-primary skeleton min-w-[40px]">—</div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div id="pending-empty" class="hidden ds-card p-12 flex flex-col items-center justify-center text-center mb-6">
                <span class="material-symbols-outlined text-4xl text-text-muted/30 mb-3" aria-hidden="true">inbox</span>
                <div class="text-sm font-bold text-text-muted uppercase tracking-widest">No pending applications at the moment.</div>
            </div>

            <!-- FILTERS + TABS + TABLE -->
            <div class="ds-card overflow-hidden relative z-10 shadow-2xl transition-all duration-300">
                <div class="absolute -right-20 -top-20 w-64 h-64 bg-primary/5 rounded-full blur-3xl -z-10 pointer-events-none"></div>
                <div class="absolute -left-20 -bottom-20 w-64 h-64 bg-accent/5 rounded-full blur-3xl -z-10 pointer-events-none"></div>
                <div class="p-4 sm:p-6 border-b border-border-subtle bg-background/40 backdrop-blur-xl relative z-10">
                    <div class="flex flex-col md:flex-row gap-4 items-start md:items-center justify-between">
                        <!-- Search -->
                        <div class="w-full md:w-96">
                            <label for="search-pending" class="fin-label mb-2 text-[10px] uppercase text-text-secondary font-black tracking-widest">Search Applications</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-text-secondary" aria-hidden="true">search</span>
                                <input type="text" id="search-pending" placeholder="Search by name or user ID..." class="fin-input pl-12 w-full">
                            </div>
                        </div>

                        <!-- Status Tabs -->
                        <div class="flex flex-wrap gap-2">
                            <button class="tab-btn btn btn-primary px-4 py-2.5 rounded-xl !text-[10px] font-black uppercase tracking-widest transition-all duration-200" data-status="all">
                                All
                            </button>
                            <button class="tab-btn btn btn-secondary px-4 py-2.5 rounded-xl !text-[10px] font-black uppercase tracking-widest transition-all duration-200" data-status="pending">
                                <span class="material-symbols-outlined text-[13px] align-[-2px] mr-1" aria-hidden="true">hourglass_empty</span>
                                Pending
                            </button>
                            <button class="tab-btn btn btn-secondary px-4 py-2.5 rounded-xl !text-[10px] font-black uppercase tracking-widest transition-all duration-200" data-status="under_review">
                                <span class="material-symbols-outlined text-[13px] align-[-2px] mr-1" aria-hidden="true">rate_review</span>
                                Under Review
                            </button>
                        </div>
                    </div>
                </div>
                <div class="ds-table-wrapper w-full border-0 shadow-none rounded-none bg-transparent">
                    <table id="approvals-table" class="ds-table w-full text-left min-w-[1300px]">
                        <thead scope="col">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Applicant</th>
                                <th scope="col">Phone</th>
                                <th scope="col">Account Type</th>
                                <th scope="col">ID Type</th>
                                <th scope="col">Date Applied</th>
                                <th scope="col">Status</th>
                                <th class="text-right" scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody id="pending-tbody">
                            <!-- Data injected by pending-approvals.js -->
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION -->
                <div class="p-4 sm:p-6 bg-surface-subtle border-t border-border-subtle flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="text-[10px] font-bold text-text-secondary uppercase tracking-widest">Total Pending: <span id="pending-count">0</span></div>
                    <div id="pending-pagination" class="flex items-center gap-2">
                        <!-- Pagination injected by JS -->
                    </div>
                </div>
            </div>
<?php
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/pending-approvals.js"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
