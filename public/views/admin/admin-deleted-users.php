<?php
$portal = 'admin';
$pageTitle = 'Deleted Users';
// Admin portal guard — server-side authentication & role check
include __DIR__ . '/_guard.php';
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

            <!-- RECOVERY MODAL -->
            <div id="recoverModal" class="fixed inset-0 z-[2000] hidden items-center justify-center p-4" role="dialog" aria-modal="true">
                <div class="absolute inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-sm" onclick="window.DeletedUsers.closeModal('recoverModal')"></div>
                <div class="ds-card w-full max-w-lg relative z-10 p-6 sm:p-8 ds-animate-scale shadow-2xl border border-border-subtle bg-surface">
                    <div class="text-center">
                        <div class="w-16 h-16 rounded-2xl bg-emerald-500/10 text-emerald-500 mx-auto mb-4 flex items-center justify-center border border-emerald-500/20 shadow-sm">
                            <span class="material-symbols-outlined text-3xl" aria-hidden="true">restore</span>
                        </div>
                        <h3 class="font-headline text-2xl font-black text-text-primary tracking-tight mb-2">Recover User Account</h3>
                        <p class="text-xs font-bold text-text-secondary mb-6 leading-relaxed">
                            You are about to restore access for <span id="recover-user-name" class="text-text-primary font-black">-</span>.
                        </p>

                        <div class="bg-surface/50 p-5 rounded-2xl mb-6 border border-border-subtle text-left">
                            <label class="block text-[10px] font-black uppercase tracking-widest text-text-secondary mb-3">Restore Options</label>
                            <div class="space-y-3">
                                <label class="flex items-start gap-3 p-3.5 rounded-xl border border-border-subtle hover:border-emerald-500/40 bg-background cursor-pointer transition-all">
                                    <input type="radio" name="restore-option" value="full" checked class="mt-0.5 w-4 h-4 text-emerald-500 focus:ring-emerald-500/20">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-text-primary">Restore with Full Balance</span>
                                        <span class="text-[10px] text-text-secondary mt-0.5">Retains the user's previous ledger balance exactly as it was.</span>
                                    </div>
                                </label>
                                <label class="flex items-start gap-3 p-3.5 rounded-xl border border-border-subtle hover:border-emerald-500/40 bg-background cursor-pointer transition-all">
                                    <input type="radio" name="restore-option" value="mandatory" class="mt-0.5 w-4 h-4 text-emerald-500 focus:ring-emerald-500/20">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-text-primary">Restore with Mandatory Balance</span>
                                        <span class="text-[10px] text-text-secondary mt-0.5">Adjusts balance to the minimum requirement (₹500.00).</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <button type="button" onclick="window.DeletedUsers.closeModal('recoverModal')" class="btn btn-secondary flex-1 justify-center py-3 text-xs font-bold uppercase tracking-wider cursor-pointer">Cancel</button>
                            <button type="button" onclick="window.DeletedUsers.confirmRecovery()" class="btn btn-primary flex-1 justify-center py-3 text-xs font-bold uppercase tracking-wider bg-emerald-600 hover:bg-emerald-700 text-white border-none shadow-md shadow-emerald-900/10 cursor-pointer">Confirm Recovery</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DETAILS MODAL -->
            <div id="detailsModal" class="fixed inset-0 z-[2000] hidden items-center justify-center p-4 sm:p-6" role="dialog" aria-modal="true">
                <div class="absolute inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-sm" onclick="window.DeletedUsers.closeModal('detailsModal')"></div>
                <div class="ds-card w-full max-w-2xl relative z-10 overflow-hidden ds-animate-scale shadow-2xl border border-border-subtle bg-surface flex flex-col max-h-[90vh]">
                    <!-- Modal Header -->
                    <div class="p-5 sm:p-6 border-b border-border-subtle flex justify-between items-center bg-background/50 backdrop-blur-sm shrink-0">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center border border-rose-500/20 shadow-sm">
                                <span class="material-symbols-outlined text-xl" aria-hidden="true">person_off</span>
                            </div>
                            <div>
                                <h3 class="text-lg font-headline font-black text-text-primary tracking-tight">Deleted Customer Dossier</h3>
                                <p class="text-[11px] font-bold text-text-secondary uppercase tracking-wider">Archived Account Record & Audit Context</p>
                            </div>
                        </div>
                        <button type="button" onclick="window.DeletedUsers.closeModal('detailsModal')" class="w-9 h-9 rounded-xl flex items-center justify-center hover:bg-surface-hover text-text-secondary hover:text-text-primary transition-colors border border-border-subtle cursor-pointer" aria-label="Close modal">
                            <span class="material-symbols-outlined text-xl" aria-hidden="true">close</span>
                        </button>
                    </div>

                    <!-- Scrollable Modal Body -->
                    <div class="p-6 sm:p-8 overflow-y-auto space-y-6 flex-1">
                        <!-- Profile Hero Card -->
                        <div class="p-6 rounded-2xl bg-surface border border-border-subtle shadow-sm flex flex-col sm:flex-row items-center sm:items-start gap-6 text-center sm:text-left relative overflow-hidden">
                            <div class="absolute -right-10 -top-10 w-36 h-36 bg-rose-500/5 rounded-full blur-2xl pointer-events-none"></div>

                            <!-- Avatar Container -->
                            <div id="details-avatar-container" class="w-20 h-20 min-w-[80px] min-h-[80px] rounded-2xl flex items-center justify-center text-3xl font-black text-white shrink-0 shadow-md ring-4 ring-background overflow-hidden border border-border-subtle">
                                <!-- Injected by JS -->
                            </div>

                            <div class="flex-1 min-w-0 space-y-2">
                                <!-- Badges -->
                                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                                    <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20 inline-flex items-center gap-1.5 shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                        <span>Account Soft-Deleted</span>
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider bg-background text-text-secondary border border-border-subtle details-type-badge font-mono">-</span>
                                    <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-bold text-text-secondary border border-border-subtle bg-background details-customer-id font-mono">FNZ-USR-PENDING</span>
                                </div>

                                <!-- Customer Name -->
                                <h4 class="text-2xl font-black font-headline text-text-primary tracking-tight details-name truncate">-</h4>

                                <!-- Contact Info -->
                                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-4 text-xs font-bold text-text-secondary pt-1">
                                    <span class="inline-flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-sm text-text-muted" aria-hidden="true">mail</span>
                                        <span class="details-email font-mono">-</span>
                                    </span>
                                    <span class="inline-flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-sm text-text-muted" aria-hidden="true">call</span>
                                        <span class="details-phone font-mono">-</span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- 3 Stat Cards Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <!-- Balance Card -->
                            <div class="p-5 rounded-2xl bg-surface border border-border-subtle shadow-sm flex flex-col justify-between">
                                <span class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1">Archived Balance</span>
                                <div>
                                    <span class="text-2xl font-black text-text-primary details-balance">₹0.00</span>
                                    <span class="text-[10px] font-bold text-text-muted block mt-0.5">At time of termination</span>
                                </div>
                            </div>

                            <!-- Account Info Card -->
                            <div class="p-5 rounded-2xl bg-surface border border-border-subtle shadow-sm flex flex-col justify-between">
                                <span class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1">Account Number</span>
                                <div>
                                    <span class="text-base font-black font-mono text-primary details-acc block">-</span>
                                    <span class="text-[10px] font-bold text-text-secondary uppercase block mt-0.5 details-type">-</span>
                                </div>
                            </div>

                            <!-- Registered Card -->
                            <div class="p-5 rounded-2xl bg-surface border border-border-subtle shadow-sm flex flex-col justify-between">
                                <span class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1">Registration Date</span>
                                <div>
                                    <span class="text-base font-black text-text-primary details-reg block">-</span>
                                    <span class="text-[10px] font-bold text-text-muted block mt-0.5">Initial onboarding</span>
                                </div>
                            </div>
                        </div>

                        <!-- Deletion & Audit History Card -->
                        <div class="p-6 rounded-2xl bg-rose-500/[0.03] dark:bg-rose-500/[0.06] border border-rose-500/15 space-y-4">
                            <div class="flex items-center justify-between pb-3 border-b border-rose-500/10">
                                <div class="flex items-center gap-2 text-rose-600 dark:text-rose-400">
                                    <span class="material-symbols-outlined text-lg" aria-hidden="true">assignment_late</span>
                                    <span class="text-xs font-black uppercase tracking-wider">Deletion Audit Record</span>
                                </div>
                                <span class="text-[10px] font-bold text-text-muted uppercase tracking-wider">Audit Log Trace</span>
                            </div>

                            <!-- Reason -->
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-widest text-text-secondary block mb-1.5">Recorded Reason</span>
                                <div class="p-3.5 rounded-xl bg-background/80 border border-border-subtle text-xs font-bold text-text-primary leading-relaxed details-reason shadow-inner">
                                    Admin Action
                                </div>
                            </div>

                            <!-- Audit metadata -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                                <div class="p-3.5 rounded-xl bg-background/60 border border-border-subtle">
                                    <span class="text-[10px] font-black uppercase tracking-wider text-text-secondary block">Authorized By</span>
                                    <span class="text-xs font-bold text-text-primary details-deleted-by mt-0.5 block">Admin</span>
                                </div>
                                <div class="p-3.5 rounded-xl bg-background/60 border border-border-subtle">
                                    <span class="text-[10px] font-black uppercase tracking-wider text-text-secondary block">Deleted Timestamp</span>
                                    <span class="text-xs font-mono font-bold text-text-primary details-deleted-at mt-0.5 block">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="p-4 sm:p-6 bg-background/50 border-t border-border-subtle flex flex-col sm:flex-row items-center justify-between gap-3 shrink-0">
                        <button type="button" onclick="window.DeletedUsers.deletePermanentlyFromDetails()" class="w-full sm:w-auto btn px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400 hover:bg-rose-500/10 border border-rose-500/30 transition-all inline-flex items-center justify-center gap-1.5 cursor-pointer shadow-sm">
                            <span class="material-symbols-outlined text-base" aria-hidden="true">delete_forever</span>
                            <span>Delete Permanently</span>
                        </button>
                        <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                            <button type="button" onclick="window.DeletedUsers.closeModal('detailsModal')" class="flex-1 sm:flex-initial btn btn-secondary px-5 py-2.5 text-xs font-bold uppercase tracking-wider cursor-pointer">Close</button>
                            <button type="button" onclick="window.DeletedUsers.recoverFromDetails()" class="flex-1 sm:flex-initial btn btn-primary px-5 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider bg-emerald-600 hover:bg-emerald-700 text-white border-none shadow-md shadow-emerald-900/10 inline-flex items-center justify-center gap-1.5 cursor-pointer">
                                <span class="material-symbols-outlined text-base" aria-hidden="true">restore</span>
                                <span>Recover Account</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
<?php
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/deleted-users.js?v=' . time() . '"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>