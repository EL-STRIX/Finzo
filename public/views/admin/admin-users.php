<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin — User Management';
ob_start();
?>
            <!-- TOP PAGE HEADER & ACTION BAR -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 relative z-10">
                <div>
                    <h1 class="text-2xl font-headline font-black text-text-primary tracking-tight">User Management</h1>
                    <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-1">Manage all registered users, accounts, and verification status</p>
                </div>
                <div class="flex items-center gap-3">
                    <button id="open-export-modal" class="btn btn-secondary px-4 py-2.5 rounded-xl border border-border-subtle bg-surface/80 hover:bg-surface hover:border-primary/40 text-text-primary text-xs font-bold uppercase tracking-wider flex items-center gap-2 shadow-sm transition-all hover:shadow-md active:scale-95 group">
                        <span class="material-symbols-outlined text-[18px] text-primary transition-transform group-hover:-translate-y-0.5" aria-hidden="true">download</span>
                        <span>Export CSV</span>
                    </button>
                </div>
            </div>

            <!-- STAT CARDS -->
            <div class="grid grid-cols-2 xl:grid-cols-5 gap-4 sm:gap-6 relative z-10">
                <div class="ds-card p-5 sm:p-6 flex flex-col items-start justify-center group relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-info/10 rounded-full blur-2xl group-hover:bg-info/20 transition-all"></div>
                    <div class="relative z-10">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-info/20 to-info/5 text-info flex items-center justify-center mb-4 border border-info/20 shadow-[inset_0_1px_1px_rgba(255,255,255,0.4)] dark:shadow-[inset_0_1px_1px_rgba(255,255,255,0.1)]">
                            <span class="material-symbols-outlined text-2xl" aria-hidden="true">group</span>
                        </div>
                        <div class="text-xs font-bold text-text-secondary uppercase tracking-[0.15em] mb-2">Total Users</div>
                        <div class="text-3xl font-black font-display tracking-tighter leading-none text-text-primary skeleton min-w-[40px]" id="stat-total-users">—</div>
                    </div>
                </div>
                <div class="ds-card p-5 sm:p-6 flex flex-col items-start justify-center group relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-success/10 rounded-full blur-2xl group-hover:bg-success/20 transition-all"></div>
                    <div class="relative z-10">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-success/20 to-success/5 text-success flex items-center justify-center mb-4 border border-success/20 shadow-[inset_0_1px_1px_rgba(255,255,255,0.4)] dark:shadow-[inset_0_1px_1px_rgba(255,255,255,0.1)]">
                            <span class="material-symbols-outlined text-2xl" aria-hidden="true">verified_user</span>
                        </div>
                        <div class="text-xs font-bold text-text-secondary uppercase tracking-[0.15em] mb-2">Active Accounts</div>
                        <div class="text-3xl font-black font-display tracking-tighter leading-none text-text-primary skeleton min-w-[40px]" id="stat-active-users">—</div>
                    </div>
                </div>
                <div class="ds-card p-5 sm:p-6 flex flex-col items-start justify-center group relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-error/10 rounded-full blur-2xl group-hover:bg-error/20 transition-all"></div>
                    <div class="relative z-10">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-error/20 to-error/5 text-error flex items-center justify-center mb-4 border border-error/20 shadow-[inset_0_1px_1px_rgba(255,255,255,0.4)] dark:shadow-[inset_0_1px_1px_rgba(255,255,255,0.1)]">
                            <span class="material-symbols-outlined text-2xl" aria-hidden="true">person_off</span>
                        </div>
                        <div class="text-xs font-bold text-text-secondary uppercase tracking-[0.15em] mb-2">Suspended</div>
                        <div class="text-3xl font-black font-display tracking-tighter leading-none text-text-primary skeleton min-w-[40px]" id="stat-suspended-users">—</div>
                    </div>
                </div>
                <div class="ds-card p-5 sm:p-6 flex flex-col items-start justify-center group relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-primary/10 rounded-full blur-2xl group-hover:bg-primary/20 transition-all"></div>
                    <div class="relative z-10">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary/20 to-primary/5 text-primary flex items-center justify-center mb-4 border border-primary/20 shadow-[inset_0_1px_1px_rgba(255,255,255,0.4)] dark:shadow-[inset_0_1px_1px_rgba(255,255,255,0.1)]">
                            <span class="material-symbols-outlined text-2xl" aria-hidden="true">new_releases</span>
                        </div>
                        <div class="text-xs font-bold text-text-secondary uppercase tracking-[0.15em] mb-2">New This Month</div>
                        <div class="text-3xl font-black font-display tracking-tighter leading-none text-text-primary skeleton min-w-[40px]" id="stat-new-month">—</div>
                    </div>
                </div>
                <div class="ds-card p-5 sm:p-6 flex flex-col items-start justify-center group relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-border-strong/10 rounded-full blur-2xl group-hover:bg-border-strong/20 transition-all"></div>
                    <div class="relative z-10">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-border-strong/20 to-border-strong/5 text-text-secondary flex items-center justify-center mb-4 border border-border-strong/20 shadow-[inset_0_1px_1px_rgba(255,255,255,0.4)] dark:shadow-[inset_0_1px_1px_rgba(255,255,255,0.1)]">
                            <span class="material-symbols-outlined text-2xl" aria-hidden="true">delete_sweep</span>
                        </div>
                        <div class="text-xs font-bold text-text-secondary uppercase tracking-[0.15em] mb-2">Soft Deleted</div>
                        <div class="text-3xl font-black font-display tracking-tighter leading-none text-text-primary skeleton min-w-[40px]" id="stat-soft-deleted">—</div>
                    </div>
                </div>
            </div>

            <!-- TABLE WITH INTEGRATED FILTERS -->
            <div class="ds-card overflow-hidden mt-6">
                <!-- Header / Filters -->
                <div class="p-6 border-b border-border-subtle bg-background">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4" id="filtersContainer">
                        <div>
                            <label for="user-search" class="fin-label">Search User</label>
                            <div class="fin-input-wrapper relative">
                                <span class="material-symbols-outlined input-icon-left text-text-secondary pointer-events-none z-10 text-[20px]" aria-hidden="true">search</span>
                                <input type="text" id="user-search" placeholder="Search by name, email or ID..." class="fin-input has-icon-left w-full !pl-11">
                            </div>
                        </div>
                        <div>
                            <label for="filter-sort" class="fin-label">Sort By</label>
                            <div class="finzo-dropdown w-full" id="sort-dropdown">
                                <input type="hidden" id="filter-sort" value="newest">
                                <div class="finzo-dropdown-trigger fin-input !flex items-center justify-between">
                                    <span class="dropdown-label">Newest Registered</span>
                                    <span class="material-symbols-outlined chevron transition-transform duration-300" aria-hidden="true">expand_more</span>
                                </div>
                                <div class="finzo-dropdown-menu z-50">
                                    <div class="finzo-dropdown-option is-selected" data-value="newest">Newest Registered</div>
                                    <div class="finzo-dropdown-option" data-value="balance_high">Highest Balance</div>
                                    <div class="finzo-dropdown-option" data-value="balance_low">Lowest Balance</div>
                                    <div class="finzo-dropdown-option" data-value="recently_active">Recently Active</div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label for="filter-activity" class="fin-label">Activity Level</label>
                            <div class="finzo-dropdown w-full" id="activity-dropdown">
                                <input type="hidden" id="filter-activity" value="all">
                                <div class="finzo-dropdown-trigger fin-input !flex items-center justify-between">
                                    <span class="dropdown-label">All Active Users</span>
                                    <span class="material-symbols-outlined chevron transition-transform duration-300" aria-hidden="true">expand_more</span>
                                </div>
                                <div class="finzo-dropdown-menu z-50">
                                    <div class="finzo-dropdown-option is-selected" data-value="all">All Active Users</div>
                                    <div class="finzo-dropdown-option" data-value="recent">Recently Online (7 Days)</div>
                                    <div class="finzo-dropdown-option" data-value="inactive_30">Inactive (30+ Days)</div>
                                    <div class="finzo-dropdown-option" data-value="dormant">Dormant (90+ Days)</div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label for="filter-balance" class="fin-label">Account Balance</label>
                            <div class="finzo-dropdown w-full" id="balance-dropdown">
                                <input type="hidden" id="filter-balance" value="all">
                                <div class="finzo-dropdown-trigger fin-input !flex items-center justify-between">
                                    <span class="dropdown-label">All Balances</span>
                                    <span class="material-symbols-outlined chevron transition-transform duration-300" aria-hidden="true">expand_more</span>
                                </div>
                                <div class="finzo-dropdown-menu z-50">
                                    <div class="finzo-dropdown-option is-selected" data-value="all">All Balances</div>
                                    <div class="finzo-dropdown-option" data-value="premium">Premium (> ₹1L)</div>
                                    <div class="finzo-dropdown-option" data-value="standard">Standard (> ₹0)</div>
                                    <div class="finzo-dropdown-option" data-value="zero">Zero Balance</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Content -->
                <div class="ds-table-wrapper border-0 rounded-none shadow-none bg-transparent">
                    <table id="users-table" class="ds-table w-full text-left min-w-[800px]">
                        <thead scope="col">
                            <tr>
                                <th scope="col">User Name</th>
                                <th scope="col">Email</th>
                                <th scope="col">Phone</th>
                                <th scope="col">Balance</th>
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
                <div class="p-4 sm:p-6 border-b border-border-subtle bg-background/40 backdrop-blur-xl relative z-10 flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center">
                    <div id="pagination-info" class="text-xs font-bold text-text-secondary uppercase tracking-widest">Showing 0-0 of 0 Users</div>
                    <div class="flex items-center gap-2">
                        <button id="prev-btn" class="px-4 py-2 bg-surface border border-border-subtle rounded-lg text-xs font-bold uppercase tracking-widest text-text-secondary hover:bg-background disabled:opacity-50 disabled:cursor-not-allowed transition-all">Previous</button>
                        <div id="page-numbers" class="flex gap-1.5">
                            <!-- Page numbers injected by users.js -->
                        </div>
                        <button id="next-btn" class="px-4 py-2 bg-surface border border-border-subtle rounded-lg text-xs font-bold uppercase tracking-widest text-text-secondary hover:bg-background disabled:opacity-50 disabled:cursor-not-allowed transition-all">Next</button>
                    </div>
                </div>
            </div>

            <!-- Export Modal -->
            <div id="export-modal" class="fixed inset-0 z-[1000] hidden items-center justify-center p-4 bg-black/70 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="export-modal-title">
                <div class="modal-content bg-surface border border-border-subtle rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden" role="document">
                    <div class="p-6 border-b border-border-subtle flex justify-between items-center bg-background/50">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center border border-primary/20">
                                <span class="material-symbols-outlined text-xl" aria-hidden="true">file_download</span>
                            </div>
                            <div>
                                <h2 id="export-modal-title" class="text-base font-bold font-headline text-text-primary">Export Users (CSV)</h2>
                                <p class="text-[10px] font-bold text-text-secondary uppercase tracking-wider">Configure export filters</p>
                            </div>
                        </div>
                        <button type="button" class="close-export-modal w-8 h-8 flex items-center justify-center rounded-lg hover:bg-background text-text-secondary transition-colors" aria-label="Close modal">
                            <span class="material-symbols-outlined text-lg" aria-hidden="true">close</span>
                        </button>
                    </div>

                    <div class="p-6 space-y-4">
                        <p class="text-xs text-text-secondary">Select export criteria to generate the CSV download:</p>

                        <!-- 3 Filter Dropdowns in Modal -->
                        <div class="space-y-3.5">
                            <div>
                                <label for="export-filter-sort" class="fin-label">Sort Order</label>
                                <div class="finzo-dropdown w-full" id="export-sort-dropdown">
                                    <input type="hidden" id="export-filter-sort" value="newest">
                                    <div class="finzo-dropdown-trigger fin-input !flex items-center justify-between">
                                        <span class="dropdown-label">Newest Registered</span>
                                        <span class="material-symbols-outlined chevron transition-transform duration-300" aria-hidden="true">expand_more</span>
                                    </div>
                                    <div class="finzo-dropdown-menu z-50">
                                        <div class="finzo-dropdown-option is-selected" data-value="newest">Newest Registered</div>
                                        <div class="finzo-dropdown-option" data-value="balance_high">Highest Balance</div>
                                        <div class="finzo-dropdown-option" data-value="balance_low">Lowest Balance</div>
                                        <div class="finzo-dropdown-option" data-value="recently_active">Recently Active</div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label for="export-filter-activity" class="fin-label">Activity Level</label>
                                <div class="finzo-dropdown w-full" id="export-activity-dropdown">
                                    <input type="hidden" id="export-filter-activity" value="all">
                                    <div class="finzo-dropdown-trigger fin-input !flex items-center justify-between">
                                        <span class="dropdown-label">All Active Users</span>
                                        <span class="material-symbols-outlined chevron transition-transform duration-300" aria-hidden="true">expand_more</span>
                                    </div>
                                    <div class="finzo-dropdown-menu z-50">
                                        <div class="finzo-dropdown-option is-selected" data-value="all">All Active Users</div>
                                        <div class="finzo-dropdown-option" data-value="recent">Recently Online (7 Days)</div>
                                        <div class="finzo-dropdown-option" data-value="inactive_30">Inactive (30+ Days)</div>
                                        <div class="finzo-dropdown-option" data-value="dormant">Dormant (90+ Days)</div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label for="export-filter-balance" class="fin-label">Account Balance</label>
                                <div class="finzo-dropdown w-full" id="export-balance-dropdown">
                                    <input type="hidden" id="export-filter-balance" value="all">
                                    <div class="finzo-dropdown-trigger fin-input !flex items-center justify-between">
                                        <span class="dropdown-label">All Balances</span>
                                        <span class="material-symbols-outlined chevron transition-transform duration-300" aria-hidden="true">expand_more</span>
                                    </div>
                                    <div class="finzo-dropdown-menu z-50">
                                        <div class="finzo-dropdown-option is-selected" data-value="all">All Balances</div>
                                        <div class="finzo-dropdown-option" data-value="premium">Premium (> ₹1L)</div>
                                        <div class="finzo-dropdown-option" data-value="standard">Standard (> ₹0)</div>
                                        <div class="finzo-dropdown-option" data-value="zero">Zero Balance</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-border-subtle flex items-center justify-end gap-3">
                            <button type="button" class="close-export-modal btn btn-secondary px-4 py-2 text-xs">Cancel</button>
                            <button type="button" id="start-export" class="btn btn-primary px-5 py-2 text-xs font-bold uppercase tracking-wider inline-flex items-center gap-1.5 shadow-sm">
                                <span class="material-symbols-outlined text-[16px]" aria-hidden="true">download</span>
                                <span>Export CSV</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

<?php
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/users.js"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>