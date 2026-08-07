<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin';
ob_start();
?>
<!-- STAT CARDS -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-6 mb-6 sm:mb-10">
                <div class="ds-card p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-warning-surface dark:bg-warning-surface/10 text-warning-base flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined" aria-hidden="true">hourglass_empty</span>
                    </div>
                    <div>
                        <div class="admin-stat-label text-xs font-bold uppercase tracking-widest text-text-muted">PENDING REVIEW</div>
                        <div class="text-xl font-black mt-1 skeleton min-w-[40px]">—</div>
                    </div>
                </div>
                <div class="ds-card p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-primary-surface dark:bg-primary-surface/10 text-info-base flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined" aria-hidden="true">rate_review</span>
                    </div>
                    <div>
                        <div class="admin-stat-label text-xs font-bold uppercase tracking-widest text-text-muted">UNDER REVIEW</div>
                        <div class="text-xl font-black mt-1 skeleton min-w-[40px]">—</div>
                    </div>
                </div>
                <div class="ds-card p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-success-surface dark:bg-success-surface/10 text-success-base flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined" aria-hidden="true">check_circle</span>
                    </div>
                    <div>
                        <div class="admin-stat-label text-xs font-bold uppercase tracking-widest text-text-muted">APPROVED TODAY</div>
                        <div class="text-xl font-black mt-1 skeleton min-w-[40px]">—</div>
                    </div>
                </div>
                <div class="ds-card p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-primary-surface dark:bg-primary-surface/10 text-primary-base flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined" aria-hidden="true">verified</span>
                    </div>
                    <div>
                        <div class="admin-stat-label text-xs font-bold uppercase tracking-widest text-text-muted">TOTAL APPROVED</div>
                        <div class="text-xl font-black mt-1 skeleton min-w-[40px]">—</div>
                    </div>
                </div>
            </div>

            <!-- FILTERS + TABS + TABLE -->
            <div class="ds-card overflow-hidden">
                <div class="p-4 sm:p-6 border-b border-surface-border dark:border-white/5 bg-surface-subtle/20 dark:bg-white/5">
                    <div class="flex flex-col md:flex-row gap-4 items-start md:items-center justify-between">
                        <!-- Search -->
                        <div class="w-full md:w-96">
                            <label for="approval-search" class="fin-label mb-2">Search Applications</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-text-muted" aria-hidden="true">search</span>
                                <input type="text" id="approval-search" placeholder="Search by name or user ID..." class="fin-input pl-12">
                            </div>
                        </div>

                        <!-- Status Tabs -->
                        <div class="flex flex-wrap gap-2">
                            <button class="tab-btn px-4 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all duration-200 bg-primary-base text-white shadow-lg shadow-primary-base/20 scale-[1.02] dark:bg-sky-500/20 dark:text-sky-300 dark:shadow-none dark:ring-1 dark:ring-sky-500/30" data-status="all">
                                All
                            </button>
                            <button class="tab-btn px-4 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all duration-200 bg-surface-hover text-text-muted hover:bg-surface-border/80 hover:text-text-main hover:scale-[1.01] dark:bg-white/5 dark:text-text-muted dark:hover:bg-white/10 dark:hover:text-text-muted" data-status="pending">
                                <span class="material-symbols-outlined" style="font-size:13px;vertical-align:-2px;" aria-hidden="true">hourglass_empty</span>
                                Pending
                            </button>
                            <button class="tab-btn px-4 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all duration-200 bg-surface-hover text-text-muted hover:bg-surface-border/80 hover:text-text-main hover:scale-[1.01] dark:bg-white/5 dark:text-text-muted dark:hover:bg-white/10 dark:hover:text-text-muted" data-status="under_review">
                                <span class="material-symbols-outlined" style="font-size:13px;vertical-align:-2px;" aria-hidden="true">rate_review</span>
                                Under Review
                            </button>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto w-full">
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
                        <tbody id="applications-table-body">
                            <!-- Data injected by pending-approvals.js -->
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION -->
                <div class="p-4 sm:p-6 bg-surface-subtle/50 dark:bg-white/5 border-t border-surface-border dark:border-white/5 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div id="pagination-info" class="text-xs font-bold opacity-40 uppercase tracking-widest">Showing 0-0 of 0 Items</div>
                    <div class="flex items-center gap-2">
                        <button id="prev-btn" class="btn btn-secondary px-4 !py-2 !text-xs disabled:opacity-50 disabled:cursor-not-allowed">Previous</button>
                        <div id="page-numbers" class="flex gap-1.5">
                            <!-- Page numbers injected by pending-approvals.js -->
                        </div>
                        <button id="next-btn" class="btn btn-secondary px-4 !py-2 !text-xs disabled:opacity-50 disabled:cursor-not-allowed">Next</button>
                    </div>
                </div>
            </div>
<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>