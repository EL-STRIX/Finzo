<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin â€” User Management';
ob_start();
?>
<!-- STAT CARDS -->
            <div class="grid grid-cols-2 xl:grid-cols-5 gap-4 sm:gap-6">
                <div class="ds-card p-5 relative overflow-hidden group hover:border-blue-200 dark:hover:border-blue-500/30 transition-colors">
                    <div class="absolute right-0 top-0 w-16 h-16 bg-primary-surface/5 rounded-bl-full -z-0"></div>
                    <div class="relative z-10">
                        <div class="w-10 h-10 rounded-xl bg-primary-surface dark:bg-primary-surface/10 text-info-base flex items-center justify-center mb-4 border border-blue-100 dark:border-blue-500/20 shadow-sm">
                            <span class="material-symbols-outlined text-xl" aria-hidden="true">group</span>
                        </div>
                        <div class="text-xs font-black text-text-muted uppercase tracking-widest mb-1">Total Users</div>
                        <div class="text-xl font-black font-mono tracking-tight text-text-main dark:text-white skeleton min-w-[40px]" id="stat-total-users">—</div>
                    </div>
                </div>
                <div class="ds-card p-5 relative overflow-hidden group hover:border-success-base dark:hover:border-success-base/30 transition-colors">
                    <div class="absolute right-0 top-0 w-16 h-16 bg-success-surface/5 rounded-bl-full -z-0"></div>
                    <div class="relative z-10">
                        <div class="w-10 h-10 rounded-xl bg-success-surface dark:bg-success-surface/10 text-success-base flex items-center justify-center mb-4 border border-success-base dark:border-success-base/20 shadow-sm">
                            <span class="material-symbols-outlined text-xl" aria-hidden="true">verified_user</span>
                        </div>
                        <div class="text-xs font-black text-text-muted uppercase tracking-widest mb-1">Active Accounts</div>
                        <div class="text-xl font-black font-mono tracking-tight text-text-main dark:text-white skeleton min-w-[40px]" id="stat-active-users">—</div>
                    </div>
                </div>
                <div class="ds-card p-5 relative overflow-hidden group hover:border-danger-base dark:hover:border-danger-base/30 transition-colors">
                    <div class="absolute right-0 top-0 w-16 h-16 bg-danger-surface/5 rounded-bl-full -z-0"></div>
                    <div class="relative z-10">
                        <div class="w-10 h-10 rounded-xl bg-danger-surface dark:bg-danger-surface/10 text-danger-base flex items-center justify-center mb-4 border border-danger-base dark:border-danger-base/20 shadow-sm">
                            <span class="material-symbols-outlined text-xl" aria-hidden="true">person_off</span>
                        </div>
                        <div class="text-xs font-black text-text-muted uppercase tracking-widest mb-1">Suspended</div>
                        <div class="text-xl font-black font-mono tracking-tight text-text-main dark:text-white skeleton min-w-[40px]" id="stat-suspended-users">—</div>
                    </div>
                </div>
                <div class="ds-card p-5 relative overflow-hidden group hover:border-indigo-200 dark:hover:border-indigo-500/30 transition-colors">
                    <div class="absolute right-0 top-0 w-16 h-16 bg-primary-surface/5 rounded-bl-full -z-0"></div>
                    <div class="relative z-10">
                        <div class="w-10 h-10 rounded-xl bg-primary-surface dark:bg-primary-surface/10 text-primary-base flex items-center justify-center mb-4 border border-indigo-100 dark:border-indigo-500/20 shadow-sm">
                            <span class="material-symbols-outlined text-xl" aria-hidden="true">new_releases</span>
                        </div>
                        <div class="text-xs font-black text-text-muted uppercase tracking-widest mb-1">New This Month</div>
                        <div class="text-xl font-black font-mono tracking-tight text-text-main dark:text-white skeleton min-w-[40px]" id="stat-new-month">—</div>
                    </div>
                </div>
                <div class="ds-card p-5 relative overflow-hidden group hover:border-border-main dark:hover:border-white/20 transition-colors">
                    <div class="absolute right-0 top-0 w-16 h-16 bg-surface-subtle0/5 rounded-bl-full -z-0"></div>
                    <div class="relative z-10">
                        <div class="w-10 h-10 rounded-xl bg-surface-hover dark:bg-white/5 text-text-muted dark:text-text-muted flex items-center justify-center mb-4 border border-surface-border dark:border-white/10 shadow-sm">
                            <span class="material-symbols-outlined text-xl" aria-hidden="true">delete_sweep</span>
                        </div>
                        <div class="text-xs font-black text-text-muted uppercase tracking-widest mb-1">Soft Deleted</div>
                        <div class="text-xl font-black font-mono tracking-tight text-text-main dark:text-white skeleton min-w-[40px]" id="stat-soft-deleted">—</div>
                    </div>
                </div>
            </div>

            <!-- TABLE WITH INTEGRATED FILTERS -->
            <div class="ds-card overflow-hidden">
                <!-- Header / Filters -->
                <div class="p-6 border-b border-surface-border dark:border-white/5 bg-surface-subtle/50 dark:bg-white/[0.02]">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="ds-label">Search User</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-text-muted" aria-hidden="true">search</span>
                                <input type="text" id="user-search" placeholder="Search by name, email or customer ID..." class="ds-input pl-11">
                            </div>
                        </div>
                        <div>
                            <label class="ds-label">Account Status</label>
                            <div class="finzo-dropdown w-full" id="status-dropdown">
                                <input type="hidden" id="filter-status" value="all">
                                <div class="finzo-dropdown-trigger ds-input !flex items-center justify-between">
                                    <span class="dropdown-label">All Status</span>
                                    <span class="material-symbols-outlined chevron transition-transform duration-300" aria-hidden="true">expand_more</span>
                                </div>
                                <div class="finzo-dropdown-menu !rounded-xl !border-none !shadow-2xl z-50">
                                    <div class="finzo-dropdown-option is-selected" data-value="all">All Status</div>
                                    <div class="finzo-dropdown-option" data-value="active">Active</div>
                                    <div class="finzo-dropdown-option" data-value="pending">Pending</div>
                                    <div class="finzo-dropdown-option" data-value="under_review">Under Review</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Content -->
                <div class="overflow-x-auto">
                    <table id="users-table" class="ds-table w-full text-left">
                        <thead scope="col">
                            <tr>
                                <th scope="col">User Name</th>
                                <th scope="col">Email</th>
                                <th scope="col">Phone</th>
                                <th scope="col">Status</th>
                                <th scope="col">Last Active</th>
                                <th class="text-right" scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data injected by users.js -->
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION -->
                <div class="p-4 sm:p-6 bg-surface-subtle/50 dark:bg-white/[0.02] border-t border-surface-border dark:border-white/5 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div id="pagination-info" class="text-xs font-bold opacity-40 uppercase tracking-widest">Showing 0-0 of 0 Users</div>
                    <div class="flex items-center gap-2">
                        <button id="prev-btn" class="px-4 py-2 bg-white dark:bg-white/5 border border-surface-border dark:border-white/10 rounded-lg text-xs font-bold uppercase tracking-widest text-text-muted dark:text-text-muted hover:bg-surface-subtle dark:hover:bg-white/10 disabled:opacity-50 disabled:cursor-not-allowed transition-all">Previous</button>
                        <div id="page-numbers" class="flex gap-1.5">
                            <!-- Page numbers injected by users.js -->
                        </div>
                        <button id="next-btn" class="px-4 py-2 bg-white dark:bg-white/5 border border-surface-border dark:border-white/10 rounded-lg text-xs font-bold uppercase tracking-widest text-text-muted dark:text-text-muted hover:bg-surface-subtle dark:hover:bg-white/10 disabled:opacity-50 disabled:cursor-not-allowed transition-all">Next</button>
                    </div>
                </div>
            </div>
<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>