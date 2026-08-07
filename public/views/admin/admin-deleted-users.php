<?php
$portal = 'admin';
$pageTitle = 'Deleted Users';
ob_start();
?>
<!-- FILTERS & TABLE -->
            <div class="ds-card overflow-hidden">
                <div class="p-4 sm:p-6 border-b border-surface-border dark:border-white/5 bg-surface-subtle/20 dark:bg-white/5">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                    <div class="md:col-span-2">
                        <label for="filter-search" class="fin-label">Search User</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-text-muted" aria-hidden="true">search</span>
                            <input type="text" id="filter-search" placeholder="ID, name, email or account number..." class="fin-input pl-11">
                        </div>
                    </div>
                    <div>
                        <label for="filter-date-from" class="fin-label">From Date</label>
                        <input type="date" id="filter-date-from" class="fin-input">
                    </div>
                    <div>
                        <label for="filter-date-to" class="fin-label">To Date</label>
                        <input type="date" id="filter-date-to" class="fin-input">
                    </div>
                    <div>
                        <label for="filter-type" class="fin-label">Account Type</label>
                        <div class="finzo-dropdown w-full" id="type-dropdown">
                            <input type="hidden" id="filter-type" value="">
                            <div class="finzo-dropdown-trigger fin-input !flex items-center justify-between">
                                <span class="dropdown-label">All Types</span>
                                <span class="material-symbols-outlined chevron transition-transform duration-300" aria-hidden="true">expand_more</span>
                            </div>
                            <div class="finzo-dropdown-menu !rounded-xl !border-none !shadow-2xl z-50">
                                <div class="finzo-dropdown-option is-selected" data-value="">All Types</div>
                                <div class="finzo-dropdown-option" data-value="savings">Savings</div>
                                <div class="finzo-dropdown-option" data-value="current">Current</div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
                <div class="overflow-x-auto w-full">
                    <table class="ds-table w-full text-left min-w-[1000px]">
                        <thead scope="col">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">User</th>
                                <th scope="col">Phone</th>
                                <th scope="col">Account No</th>
                                <th scope="col">Account Type</th>
                                <th scope="col">Deleted On</th>
                                <th scope="col">Reason</th>
                                <th class="text-right px-6" scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="deleted-table-body">
                            <!-- Injected by deleted-users.js -->
                        </tbody>
                    </table>
                </div>

                <!-- EMPTY STATE -->
                <div id="empty-state" class="hidden flex-col items-center justify-center py-24 animate-fade-in">
                    <div class="w-20 h-20 rounded-full bg-surface-hover dark:bg-white/5 flex items-center justify-center text-text-muted mb-6">
                        <span class="material-symbols-outlined text-5xl" aria-hidden="true">person_off</span>
                    </div>
                    <h3 class="text-xl font-bold dark:text-white">No deleted users found</h3>
                    <p class="text-sm opacity-50 mt-1">Try adjusting your filters or search query.</p>
                </div>

                <!-- PAGINATION -->
                <div class="p-4 sm:p-6 bg-surface-subtle/50 dark:bg-white/5 border-t border-surface-border dark:border-white/5 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div id="pagination-info" class="text-xs font-bold opacity-40 uppercase tracking-widest">Showing 0-0 of 0 Deleted Users</div>
                    <div id="pagination-controls" class="flex items-center gap-2">
                        <!-- Pagination injected by deleted-users.js -->
                    </div>
                </div>
            </div>
<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>