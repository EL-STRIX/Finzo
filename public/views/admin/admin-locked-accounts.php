<?php
$portal = 'admin';
$pageTitle = 'Locked Accounts';
ob_start();
?>
<!-- INFO BANNER -->
            <div class="p-4 mb-6 bg-warning-surface dark:bg-warning-surface/10 border-l-4 border-warning-base rounded-lg flex gap-4">
                <span class="material-symbols-outlined text-warning-base" aria-hidden="true">info</span>
                <div>
                    <h4 class="text-sm font-bold text-warning-base dark:text-warning-base">How account locking works</h4>
                    <p class="text-xs text-warning-base dark:text-warning-base mt-1">If a user enters the wrong password 5 times within 24 hours, their account is automatically locked. Administrators can generate a 24-hour temporary password to restore access.</p>
                </div>
            </div>

            <!-- FILTERS & TABLE -->
            <div class="ds-card overflow-hidden">
                <div class="p-4 sm:p-6 border-b border-surface-border dark:border-white/5 bg-surface-subtle/20 dark:bg-white/5">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 items-end">
                    <div class="col-span-2">
                        <label for="search-input" class="block text-xs font-black text-text-muted uppercase tracking-widest mb-2">Search Account</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-text-muted" aria-hidden="true">search</span>
                            <input type="text" id="search-input" placeholder="Name, email or account number..." class="w-full pl-12 pr-4 py-3 bg-surface-hover dark:bg-white/5 border-none rounded-xl focus:ring-2 focus:ring-primary/10 font-bold text-sm">
                        </div>
                    </div>
                    <div>
                        <label for="type-filter" class="block text-xs font-black text-text-muted uppercase tracking-widest mb-2">Account Type</label>
                        <div class="finzo-dropdown" id="type-dropdown">
                            <input type="hidden" id="type-filter" value="">
                            <div class="finzo-dropdown-trigger !bg-surface-hover dark:!bg-white/5 !border-none !py-3 !rounded-xl">
                                <span class="dropdown-label font-bold text-sm">All Types</span>
                                <span class="material-symbols-outlined chevron transition-transform duration-300" aria-hidden="true">expand_more</span>
                            </div>
                            <div class="finzo-dropdown-menu !rounded-xl !border-none !shadow-2xl">
                                <div class="finzo-dropdown-option is-selected" data-value="">All Types</div>
                                <div class="finzo-dropdown-option" data-value="Savings">Savings</div>
                                <div class="finzo-dropdown-option" data-value="Current">Current</div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label for="status-filter" class="block text-xs font-black text-text-muted uppercase tracking-widest mb-2">Status</label>
                        <div class="finzo-dropdown" id="status-dropdown">
                            <input type="hidden" id="status-filter" value="">
                            <div class="finzo-dropdown-trigger !bg-surface-hover dark:!bg-white/5 !border-none !py-3 !rounded-xl">
                                <span class="dropdown-label font-bold text-sm">All Statuses</span>
                                <span class="material-symbols-outlined chevron transition-transform duration-300" aria-hidden="true">expand_more</span>
                            </div>
                            <div class="finzo-dropdown-menu !rounded-xl !border-none !shadow-2xl">
                                <div class="finzo-dropdown-option is-selected" data-value="">All Statuses</div>
                                <div class="finzo-dropdown-option" data-value="pending">Pending</div>
                                <div class="finzo-dropdown-option" data-value="done">Done</div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label for="date-filter" class="block text-xs font-black text-text-muted uppercase tracking-widest mb-2">Locked On</label>
                        <input type="date" id="date-filter" class="w-full px-4 py-3 bg-surface-hover dark:bg-white/5 border-none rounded-xl focus:ring-2 focus:ring-primary/10 font-bold text-sm">
                    </div>
                </div>

                </div>
                <div class="overflow-x-auto w-full">
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
                <div class="p-4 sm:p-6 bg-surface-subtle/50 dark:bg-white/5 border-t border-surface-border dark:border-white/5 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div id="pag-info" class="text-xs font-bold opacity-40 uppercase tracking-widest">Showing 0-0 of 0 Accounts</div>
                    <div class="flex items-center gap-2" id="pag-controls">
                        <!-- Pagination injected by locked-accounts.js -->
                    </div>
                </div>
            </div>
<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>