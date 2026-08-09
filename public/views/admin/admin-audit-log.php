<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin - Audit Log';
$pageHeader = '
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-headline font-black text-text-main dark:text-white tracking-tight">Audit Log</h1>
        <p class="text-xs font-bold text-text-muted uppercase tracking-widest mt-0.5">Complete system activity audit trail</p>
    </div>
</div>
';
ob_start();
?>
<!-- STATS ROW -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-6 mb-6 sm:mb-10">
                <div class="ds-card p-5 flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-xl bg-primary-surface dark:bg-primary-surface/10 text-info-base flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined" aria-hidden="true">history</span>
                    </div>
                    <div>
                        <div class="text-xs font-bold uppercase tracking-widest text-text-muted">Total Events</div>
                        <div id="stat-total" class="text-xl font-black mt-1">0</div>
                    </div>
                </div>
                <div class="ds-card p-5 flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-xl bg-primary-surface dark:bg-primary-surface/10 text-primary-base flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined" aria-hidden="true">person</span>
                    </div>
                    <div>
                        <div class="text-xs font-bold uppercase tracking-widest text-text-muted">Admin Actions</div>
                        <div id="stat-admin" class="text-xl font-black mt-1">0</div>
                    </div>
                </div>
                <div class="ds-card p-5 flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-xl bg-violet-50 dark:bg-violet-500/10 text-violet-600 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined" aria-hidden="true">badge</span>
                    </div>
                    <div>
                        <div class="text-xs font-bold uppercase tracking-widest text-text-muted">Staff Actions</div>
                        <div id="stat-staff" class="text-xl font-black mt-1">0</div>
                    </div>
                </div>
                <div class="ds-card p-5 flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-xl bg-teal-50 dark:bg-teal-500/10 text-teal-600 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined" aria-hidden="true">account_circle</span>
                    </div>
                    <div>
                        <div class="text-xs font-bold uppercase tracking-widest text-text-muted">User Actions</div>
                        <div id="stat-user" class="text-xl font-black mt-1">0</div>
                    </div>
                </div>
            </div>

            <!-- FILTERS & TABLE -->
            <div class="ds-card overflow-hidden">
                <div class="p-4 sm:p-6 border-b border-surface-border dark:border-white/5 bg-surface-subtle/20 dark:bg-white/5 relative z-20">
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 items-end">
                    <div class="col-span-2">
                        <label for="filter-search" class="fin-label mb-2">Search
                            Events</label>
                        <div class="relative">
                            <span
                                class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-text-muted" aria-hidden="true">search</span>
                            <input type="text" id="filter-search" placeholder="Search by Staff ID (e.g. STF001)…"
                                class="fin-input pl-12">
                        </div>
                    </div>
                    <div class="col-span-1">
                        <label for="filter-action-type" class="fin-label mb-2">Action
                            Type</label>
                        <div class="finzo-dropdown" id="action-type-dropdown">
                            <input type="hidden" id="filter-action-type" value="all">
                            <div
                                class="finzo-dropdown-trigger !bg-surface-hover dark:!bg-white/5 !border-none !py-3 !rounded-xl">
                                <span class="dropdown-label font-bold text-sm">All Actions</span>
                                <span
                                    class="material-symbols-outlined chevron transition-transform duration-300" aria-hidden="true">expand_more</span>
                            </div>
                            <div class="finzo-dropdown-menu !rounded-xl !border-none !shadow-2xl">
                                <div class="finzo-dropdown-option is-selected" data-value="all">All Actions</div>
                                <div class="finzo-dropdown-option" data-value="login">Login</div>
                                <div class="finzo-dropdown-option" data-value="create">Create</div>
                                <div class="finzo-dropdown-option" data-value="update">Update</div>
                                <div class="finzo-dropdown-option" data-value="delete">Delete</div>
                                <div class="finzo-dropdown-option" data-value="approve">Approve</div>
                                <div class="finzo-dropdown-option" data-value="reject">Reject</div>
                                <div class="finzo-dropdown-option" data-value="suspend">Suspend</div>
                                <div class="finzo-dropdown-option" data-value="unlock">Unlock</div>
                                <div class="finzo-dropdown-option" data-value="reset_password">Reset Password</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-1">
                        <label for="filter-actor-role" class="fin-label mb-2">Actor
                            Role</label>
                        <div class="finzo-dropdown" id="actor-role-dropdown">
                            <input type="hidden" id="filter-actor-role" value="all">
                            <div
                                class="finzo-dropdown-trigger !bg-surface-hover dark:!bg-white/5 !border-none !py-3 !rounded-xl">
                                <span class="dropdown-label font-bold text-sm">All Roles</span>
                                <span
                                    class="material-symbols-outlined chevron transition-transform duration-300" aria-hidden="true">expand_more</span>
                            </div>
                            <div class="finzo-dropdown-menu !rounded-xl !border-none !shadow-2xl">
                                <div class="finzo-dropdown-option is-selected" data-value="all">All Roles</div>
                                <div class="finzo-dropdown-option" data-value="admin">Admin</div>
                                <div class="finzo-dropdown-option" data-value="staff">Staff</div>
                                <div class="finzo-dropdown-option" data-value="user">User</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-1">
                        <label for="date-from" class="fin-label mb-2">From
                            Date</label>
                        <input type="date" id="date-from"
                            class="fin-input">
                    </div>
                    <div class="col-span-1">
                        <label for="date-to" class="fin-label mb-2">To
                            Date</label>
                        <input type="date" id="date-to"
                            class="fin-input">
                    </div>
                </div>
                </div>
                <div class="overflow-x-auto w-full">
                    <table id="audit-log-table" class="ds-table w-full text-left min-w-[1000px]">
                        <thead scope="col">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Actor</th>
                                <th scope="col">Action</th>
                                <th scope="col">Entity</th>
                                <th scope="col">IP Address</th>
                                <th scope="col">Details</th>
                                <th scope="col">Timestamp</th>
                            </tr>
                        </thead>
                        <tbody id="audit-log-tbody">
                            <!-- injected by audit-log.js -->
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION -->
                <div
                    class="p-4 sm:p-6 bg-surface-subtle/50 dark:bg-white/5 border-t border-surface-border dark:border-white/5 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div id="pagination-info" class="text-xs font-bold opacity-40 uppercase tracking-widest">Showing
                        0-0 of 0 Records</div>
                    <div class="flex items-center gap-2">
                        <button id="prev-btn"
                            class="btn btn-secondary px-4 !py-2 !text-xs disabled:opacity-50 disabled:cursor-not-allowed">Previous</button>
                        <div id="page-numbers" class="flex gap-1.5">
                            <!-- Page numbers injected by audit-log.js -->
                        </div>
                        <button id="next-btn"
                            class="btn btn-secondary px-4 !py-2 !text-xs disabled:opacity-50 disabled:cursor-not-allowed">Next</button>
                    </div>
                </div>
            </div>
<?php
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/audit-log.js"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>