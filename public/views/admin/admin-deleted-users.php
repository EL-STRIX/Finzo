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
                <div class="absolute inset-0 bg-background/80 backdrop-blur-md" onclick="window.DeletedUsers.closeModal('recoverModal')"></div>
                <div class="ds-card w-full max-w-lg relative z-10 p-6 sm:p-8 ds-animate-scale shadow-2xl border border-border-subtle">
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
                                <label class="flex items-start gap-3 p-3 rounded-xl border border-border-subtle hover:border-emerald-500/40 bg-background cursor-pointer transition-all">
                                    <input type="radio" name="restore-option" value="full" checked class="mt-0.5 w-4 h-4 text-emerald-500 focus:ring-emerald-500/20">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-text-primary">Restore with Full Balance</span>
                                        <span class="text-[10px] text-text-secondary mt-0.5">Retains the user's previous ledger balance exactly as it was.</span>
                                    </div>
                                </label>
                                <label class="flex items-start gap-3 p-3 rounded-xl border border-border-subtle hover:border-emerald-500/40 bg-background cursor-pointer transition-all">
                                    <input type="radio" name="restore-option" value="mandatory" class="mt-0.5 w-4 h-4 text-emerald-500 focus:ring-emerald-500/20">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-text-primary">Restore with Mandatory Balance</span>
                                        <span class="text-[10px] text-text-secondary mt-0.5">Adjusts balance to the minimum requirement (₹500.00).</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <button type="button" onclick="window.DeletedUsers.closeModal('recoverModal')" class="btn btn-secondary flex-1 justify-center py-2.5 text-xs font-bold uppercase tracking-wider">Cancel</button>
                            <button type="button" onclick="window.DeletedUsers.confirmRecovery()" class="btn btn-primary flex-1 justify-center py-2.5 text-xs font-bold uppercase tracking-wider bg-emerald-600 hover:bg-emerald-700 text-white border-none shadow-sm">Confirm Recovery</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DETAILS MODAL -->
            <div id="detailsModal" class="fixed inset-0 z-[2000] hidden items-center justify-center p-4" role="dialog" aria-modal="true">
                <div class="absolute inset-0 bg-background/80 backdrop-blur-md" onclick="window.DeletedUsers.closeModal('detailsModal')"></div>
                <div class="ds-card w-full max-w-2xl relative z-10 overflow-hidden ds-animate-scale shadow-2xl border border-border-subtle">
                    <div class="p-6 border-b border-border-subtle flex justify-between items-center bg-background/40">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-xl" aria-hidden="true">manage_accounts</span>
                            <h3 class="text-lg font-headline font-black text-text-primary">Deleted User Dossier</h3>
                        </div>
                        <button type="button" onclick="window.DeletedUsers.closeModal('detailsModal')" class="w-8 h-8 rounded-lg flex items-center justify-center hover:bg-surface text-text-secondary hover:text-text-primary transition-colors" aria-label="Close modal">
                            <span class="material-symbols-outlined text-lg" aria-hidden="true">close</span>
                        </button>
                    </div>
                    <div class="p-6 sm:p-8 max-h-[72vh] overflow-y-auto space-y-6">
                        <div class="flex flex-col sm:flex-row items-center gap-5 pb-6 border-b border-border-subtle text-center sm:text-left">
                            <div id="details-avatar-container" class="w-18 h-18 rounded-2xl flex items-center justify-center text-3xl font-black text-white shrink-0 shadow-md ring-4 ring-background overflow-hidden">
                                <!-- Injected by JS -->
                            </div>
                            <div class="flex-1">
                                <h4 class="text-2xl font-black font-headline text-text-primary details-name">-</h4>
                                <p class="text-xs font-mono font-bold text-text-secondary mt-1 details-email">-</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <!-- Personal & Account Details -->
                            <div class="space-y-4">
                                <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest">Account Information</div>
                                <div class="bg-surface/50 p-4 rounded-xl border border-border-subtle space-y-3 text-xs">
                                    <div class="flex justify-between">
                                        <span class="text-text-secondary font-bold">Phone:</span>
                                        <span class="font-mono font-bold text-text-primary details-phone">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-text-secondary font-bold">Account No:</span>
                                        <span class="font-mono font-bold text-primary details-acc">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-text-secondary font-bold">Account Type:</span>
                                        <span class="font-bold text-text-primary details-type">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-text-secondary font-bold">Registered On:</span>
                                        <span class="font-bold text-text-primary details-reg">-</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Balance & Deletion Audit -->
                            <div class="space-y-4">
                                <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest">Deletion Record</div>
                                <div class="bg-surface/50 p-4 rounded-xl border border-border-subtle space-y-3 text-xs">
                                    <div class="flex justify-between items-center">
                                        <span class="text-text-secondary font-bold">Recorded Balance:</span>
                                        <span class="font-mono font-black text-lg text-text-primary details-balance">₹0.00</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-text-secondary font-bold">Deleted By:</span>
                                        <span class="font-bold text-text-primary details-deleted-by">Admin</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-text-secondary font-bold">Deleted At:</span>
                                        <span class="font-mono font-bold text-text-primary details-deleted-at">-</span>
                                    </div>
                                    <div class="flex justify-between items-start gap-2 pt-2 border-t border-border-subtle">
                                        <span class="text-text-secondary font-bold shrink-0">Reason:</span>
                                        <span class="font-bold text-error text-right details-reason truncate">Admin Action</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 sm:p-6 bg-background/50 border-t border-border-subtle flex flex-wrap justify-end gap-3">
                        <button type="button" onclick="window.DeletedUsers.deletePermanentlyFromDetails()" class="btn px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider text-rose-500 hover:bg-rose-500/10 border border-rose-500/20 transition-all">Delete Permanently</button>
                        <button type="button" onclick="window.DeletedUsers.recoverFromDetails()" class="btn btn-primary px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider bg-emerald-600 hover:bg-emerald-700 text-white border-none shadow-sm inline-flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-base" aria-hidden="true">restore</span>
                            <span>Recover Account</span>
                        </button>
                        <button type="button" onclick="window.DeletedUsers.closeModal('detailsModal')" class="btn btn-secondary px-5 py-2.5 text-xs font-bold uppercase tracking-wider">Close</button>
                    </div>
                </div>
            </div>
<?php
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/deleted-users.js?v=' . time() . '"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>