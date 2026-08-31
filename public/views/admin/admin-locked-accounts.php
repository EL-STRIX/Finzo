<?php
$portal = 'admin';
$pageTitle = 'Locked Accounts';
$pageHeader = '
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-headline font-black text-text-primary tracking-tight">Locked Accounts</h1>
        <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-1">Review and manage locked customer accounts</p>
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

            <!-- UNLOCK & TEMPORARY PASSWORD MODAL -->
            <div id="unlock-modal" class="fixed inset-0 z-[2000] hidden items-center justify-center p-4" role="dialog" aria-modal="true">
                <div class="absolute inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-sm" onclick="window.LockedAccounts.closeUnlockModal()"></div>
                <div class="ds-card w-full max-w-md relative z-10 p-6 sm:p-8 ds-animate-scale shadow-2xl border border-border-subtle bg-surface">
                    <div class="text-center">
                        <div id="unlock-modal-icon-box" class="w-16 h-16 rounded-2xl bg-amber-500/10 text-amber-500 mx-auto mb-4 flex items-center justify-center border border-amber-500/20 shadow-sm transition-all duration-300">
                            <span id="unlock-modal-icon" class="material-symbols-outlined text-3xl" aria-hidden="true">key</span>
                        </div>
                        <h3 id="unlock-modal-title" class="font-headline text-2xl font-black text-text-primary tracking-tight mb-2">Generate Temporary Password</h3>
                        <p id="unlock-modal-text" class="text-xs font-bold text-text-secondary mb-6 leading-relaxed"></p>

                        <!-- Generated password display -->
                        <div id="gen-pass-section" class="hidden mb-6 text-left">
                            <label class="block text-[10px] font-black uppercase tracking-widest text-text-secondary mb-2">Temporary One-Time Password</label>
                            <div class="flex items-center gap-3 bg-background p-3.5 rounded-xl border-2 border-amber-500/30 shadow-inner">
                                <code id="gen-pass-value" class="flex-1 font-mono font-black text-xl tracking-wider text-primary text-center select-all"></code>
                                <button type="button" id="copy-btn" onclick="window.LockedAccounts.copyPassword()" class="p-2 hover:bg-surface rounded-lg transition-colors text-amber-600 dark:text-amber-400 border border-border-subtle shrink-0 cursor-pointer" title="Copy to clipboard">
                                    <span class="material-symbols-outlined text-lg" aria-hidden="true">content_copy</span>
                                </button>
                            </div>
                            <div class="grid grid-cols-2 gap-3 mt-3">
                                <div class="bg-amber-500/10 p-2.5 rounded-xl flex items-center gap-2 border border-amber-500/20">
                                    <span class="material-symbols-outlined text-amber-600 dark:text-amber-400 text-sm" aria-hidden="true">schedule</span>
                                    <span class="text-[10px] font-black text-amber-600 dark:text-amber-400 uppercase">24H Validity</span>
                                </div>
                                <div class="bg-primary/10 p-2.5 rounded-xl flex items-center gap-2 border border-primary/20">
                                    <span class="material-symbols-outlined text-primary text-sm" aria-hidden="true">verified_user</span>
                                    <span class="text-[10px] font-black text-primary uppercase">Single Use</span>
                                </div>
                            </div>
                        </div>

                        <div id="unlock-modal-actions" class="flex gap-3">
                            <button type="button" onclick="window.LockedAccounts.closeUnlockModal()" class="btn btn-secondary flex-1 justify-center py-3 text-xs font-bold uppercase tracking-wider cursor-pointer">Cancel</button>
                            <button type="button" id="generate-btn" onclick="window.LockedAccounts.generatePassword()" class="btn btn-primary flex-1 justify-center py-3 text-xs font-bold uppercase tracking-wider bg-amber-600 hover:bg-amber-700 text-white border-none shadow-md shadow-amber-900/10 cursor-pointer inline-flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-base" aria-hidden="true">key</span>
                                <span>Generate & Unlock</span>
                            </button>
                        </div>
                        <div id="unlock-done-actions" class="hidden flex gap-3">
                            <button type="button" onclick="window.LockedAccounts.closeUnlockModal()" class="btn btn-primary w-full justify-center py-3 text-xs font-bold uppercase tracking-wider bg-emerald-600 hover:bg-emerald-700 text-white border-none shadow-md shadow-emerald-900/10 cursor-pointer">Done</button>
                        </div>
                    </div>
                </div>
            </div>
<?php
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/locked-accounts.js?v=' . time() . '"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>