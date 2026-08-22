<?php
$portal = 'admin';
$pageTitle = 'Locked Accounts';
$pageHeader = '
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-headline font-black text-text-primary tracking-tight">Locked Accounts</h1>
        <p class="text-[10px] font-black text-text-secondary uppercase tracking-widest mt-0.5">Review and manage locked customer accounts</p>
    </div>
</div>
';
ob_start();
?>
<!-- INFO BANNER -->
            <div class="p-4 mb-6 bg-warning-bg/30 border border-warning rounded-xl flex gap-4">
                <span class="material-symbols-outlined text-warning" aria-hidden="true">info</span>
                <div>
                    <h4 class="text-sm font-bold text-warning">How account locking works</h4>
                    <p class="text-xs text-warning/80 mt-1">If a user enters the wrong password 5 times within 24 hours, their account is automatically locked. Administrators can generate a 24-hour temporary password to restore access.</p>
                </div>
            </div>

            <!-- FILTERS & TABLE -->
            <div class="ds-card overflow-hidden relative z-10 shadow-2xl transition-all duration-300">
                <div class="absolute -right-20 -top-20 w-64 h-64 bg-primary/5 rounded-full blur-3xl -z-10 pointer-events-none"></div>
                <div class="absolute -left-20 -bottom-20 w-64 h-64 bg-accent/5 rounded-full blur-3xl -z-10 pointer-events-none"></div>
                <div class="p-4 sm:p-6 border-b border-border-subtle bg-background/40 backdrop-blur-xl relative z-10">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 items-end">
                    <div class="col-span-2">
                        <label for="search-input" class="fin-label mb-2 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Search Account</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-text-secondary" aria-hidden="true">search</span>
                            <input type="text" id="search-input" placeholder="Name, email or account number..." class="fin-input pl-12 w-full">
                        </div>
                    </div>
                    <div>
                        <label for="type-filter" class="fin-label mb-2 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Account Type</label>
                        <div class="finzo-dropdown" id="type-dropdown">
                            <input type="hidden" id="type-filter" value="">
                            <div class="finzo-dropdown-trigger fin-input !flex items-center justify-between cursor-pointer w-full">
                                <span class="dropdown-label font-bold text-sm text-text-primary">All Types</span>
                                <span class="material-symbols-outlined chevron transition-transform duration-300 text-text-secondary" aria-hidden="true">expand_more</span>
                            </div>
                            <div class="finzo-dropdown-menu w-full z-50">
                                <div class="finzo-dropdown-option is-selected" data-value="">All Types</div>
                                <div class="finzo-dropdown-option" data-value="Savings">Savings</div>
                                <div class="finzo-dropdown-option" data-value="Current">Current</div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label for="status-filter" class="fin-label mb-2 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Status</label>
                        <div class="finzo-dropdown" id="status-dropdown">
                            <input type="hidden" id="status-filter" value="">
                            <div class="finzo-dropdown-trigger fin-input !flex items-center justify-between cursor-pointer w-full">
                                <span class="dropdown-label font-bold text-sm text-text-primary">All Statuses</span>
                                <span class="material-symbols-outlined chevron transition-transform duration-300 text-text-secondary" aria-hidden="true">expand_more</span>
                            </div>
                            <div class="finzo-dropdown-menu w-full z-50">
                                <div class="finzo-dropdown-option is-selected" data-value="">All Statuses</div>
                                <div class="finzo-dropdown-option" data-value="pending">Pending</div>
                                <div class="finzo-dropdown-option" data-value="done">Done</div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label for="date-filter" class="fin-label mb-2 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Locked On</label>
                        <input type="date" id="date-filter" class="fin-input w-full">
                    </div>
                </div>

                </div>
                <div class="ds-table-wrapper w-full border-0 shadow-none rounded-none bg-transparent">
                    <table class="ds-table w-full text-left min-w-[1000px]">
                        <thead scope="col">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Customer</th>
                                <th scope="col">Account No</th>
                                <th scope="col">Account Type</th>
                                <th scope="col">Status</th>
                                <th scope="col">Failed Attempts</th>
                                <th scope="col">Locked On</th>
                                <th scope="col">Lock Time</th>
                                <th class="text-right px-6" scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="table-body">
                            <!-- Data injected by locked-accounts.js -->
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION -->
                <div class="p-4 sm:p-6 bg-surface-subtle border-t border-border-subtle flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div id="pag-info" class="text-[10px] font-bold text-text-secondary uppercase tracking-widest">Showing 0-0 of 0 Accounts</div>
                    <div class="flex items-center gap-2" id="pag-controls">
                        <!-- Pagination injected by locked-accounts.js -->
                    </div>
                </div>
            </div>
<?php
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/locked-accounts.js"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>