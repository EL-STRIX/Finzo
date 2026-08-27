<?php
$portal = 'admin';
$pageTitle = 'Deleted Users';
$pageHeader = '
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-headline font-black text-text-primary tracking-tight">Deleted Users</h1>
        <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-1">View and restore soft-deleted user accounts</p>
    </div>
</div>
';
ob_start();
?>
<!-- FILTERS & TABLE -->
            <div class="ds-card overflow-hidden relative z-10 shadow-2xl transition-all duration-300">
                <div class="absolute -right-20 -top-20 w-64 h-64 bg-primary/5 rounded-full blur-3xl -z-10 pointer-events-none"></div>
                <div class="absolute -left-20 -bottom-20 w-64 h-64 bg-accent/5 rounded-full blur-3xl -z-10 pointer-events-none"></div>
                <div class="p-4 sm:p-6 border-b border-border-subtle bg-background/40 backdrop-blur-xl relative z-10">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                    <div class="md:col-span-2">
                        <label for="filter-search" class="fin-label">Search User</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-text-secondary" aria-hidden="true">search</span>
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
                            <div class="finzo-dropdown-menu z-50">
                                <div class="finzo-dropdown-option is-selected" data-value="">All Types</div>
                                <div class="finzo-dropdown-option" data-value="savings">Savings</div>
                                <div class="finzo-dropdown-option" data-value="current">Current</div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
                <div class="ds-table-wrapper border-0 rounded-none shadow-none bg-transparent">
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
                    <div class="w-20 h-20 rounded-full bg-surface-hover flex items-center justify-center text-text-secondary mb-6">
                        <span class="material-symbols-outlined text-5xl" aria-hidden="true">person_off</span>
                    </div>
                    <h3 class="text-xl font-bold text-text-primary">No deleted users found</h3>
                    <p class="text-sm text-text-secondary mt-1">Try adjusting your filters or search query.</p>
                </div>

                <!-- PAGINATION -->
                <div class="p-4 sm:p-6 bg-background border-t border-border-subtle flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div id="pagination-info" class="text-xs font-bold text-text-secondary uppercase tracking-widest">Showing 0-0 of 0 Deleted Users</div>
                    <div id="pagination-controls" class="flex items-center gap-2">
                        <!-- Pagination injected by deleted-users.js -->
                    </div>
                </div>
            </div>
<?php
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/deleted-users.js"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>