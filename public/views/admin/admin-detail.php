<?php
$portal = 'admin';
$pageTitle = 'Admin Dossier';
ob_start();
?>
<!-- Top Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 relative z-10">
    <div>
        <div class="flex items-center gap-3">
            <a href="admin-profile.php" class="w-9 h-9 rounded-xl bg-surface border border-border-subtle hover:bg-surface-hover flex items-center justify-center text-text-secondary hover:text-text-primary transition-colors cursor-pointer" title="Back to Directory">
                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">arrow_back</span>
            </a>
            <h1 id="header-admin-name" class="text-2xl font-headline font-black text-text-primary tracking-tight">Admin Dossier</h1>
            <span id="header-status-badge" class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-widest hidden"></span>
        </div>
        <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-1 ml-12">Admin Profile & Access Control</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="admin-profile.php" class="btn btn-secondary px-4 py-2 text-xs font-bold flex items-center gap-1.5 cursor-pointer">
            <span class="material-symbols-outlined text-[16px]" aria-hidden="true">group</span>
            <span>All Admins</span>
        </a>
    </div>
</div>

<div class="flex flex-col lg:grid lg:grid-cols-12 gap-8">

    
    <!-- Left Column: Details -->
    <div class="lg:col-span-8 space-y-8">
        
        <!-- Profile Card -->
        <div class="ds-card p-6 sm:p-8 relative overflow-hidden">
            <div class="absolute -right-20 -top-20 w-64 h-64 bg-primary/5 rounded-full blur-3xl pointer-events-none"></div>
            
            <div class="flex flex-col sm:flex-row items-center gap-6 mb-8 pb-8 border-b border-border-subtle relative z-10">
                <div class="relative group">
                    <div id="admin-photo-container" class="w-24 h-24 sm:w-32 sm:h-32 rounded-3xl overflow-hidden ring-4 ring-primary/10 shadow-xl bg-surface-subtle flex items-center justify-center border border-border-subtle">
                        <span id="photo-initials" class="text-4xl font-black text-text-secondary">?</span>
                    </div>
                </div>
                <div class="text-center sm:text-left flex-1">
                    <h3 id="info-name" class="text-2xl sm:text-3xl font-black tracking-tight text-text-primary">---</h3>
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2.5 mt-3">
                        <span id="info-admin-id" class="px-3 py-1 rounded-xl text-xs font-mono font-bold uppercase tracking-widest bg-surface border border-border-subtle text-text-secondary">---</span>
                        <span id="info-role-badge" class="px-3 py-1 rounded-xl text-xs font-black uppercase tracking-widest">---</span>
                        <span id="info-access-badge" class="px-3 py-1 rounded-xl text-xs font-black uppercase tracking-widest">---</span>
                    </div>
                    <p class="text-xs font-bold text-text-secondary mt-4 flex items-center justify-center sm:justify-start gap-1.5">
                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">calendar_today</span>
                        <span>Member since</span> <span id="info-joined-date" class="text-text-primary font-bold">---</span>
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative z-10">
                <div class="space-y-5">
                    <div class="p-4 rounded-2xl bg-surface-subtle border border-border-subtle">
                        <label class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Email Address</label>
                        <div id="info-email" class="text-xs font-bold text-text-primary flex items-center gap-2">---</div>
                    </div>
                    <div class="p-4 rounded-2xl bg-surface-subtle border border-border-subtle">
                        <label class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Phone Number</label>
                        <div id="info-phone" class="text-xs font-bold text-text-primary">---</div>
                    </div>
                </div>
                <div class="space-y-5">
                    <div class="p-4 rounded-2xl bg-surface-subtle border border-border-subtle">
                        <label class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Gender</label>
                        <div id="info-gender" class="text-xs font-bold text-text-primary">---</div>
                    </div>
                    <div class="p-4 rounded-2xl bg-surface-subtle border border-border-subtle">
                        <label class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Date of Birth</label>
                        <div id="info-dob" class="text-xs font-bold text-text-primary">---</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Information -->
        <div class="ds-card overflow-hidden">
            <div class="p-5 sm:p-6 border-b border-border-subtle bg-surface-subtle flex items-center justify-between">
                <h4 class="font-bold text-xs uppercase tracking-widest flex items-center gap-2 text-text-primary">
                    <span class="material-symbols-outlined text-primary text-[18px]" aria-hidden="true">security</span> System Audit Info
                </h4>
            </div>
            <div class="p-6 sm:p-8 grid grid-cols-2 md:grid-cols-3 gap-6">
                <div>
                    <label class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Last Login</label>
                    <div id="sys-last-login" class="text-xs font-bold text-text-primary">---</div>
                    <div id="sys-last-ip" class="text-[10px] font-mono font-bold text-text-secondary mt-0.5">---</div>
                </div>
                <div>
                    <label class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">PWD Last Changed</label>
                    <div id="sys-pwd-change" class="text-xs font-bold text-text-primary">---</div>
                </div>
                <div>
                    <label class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Failed Attempts</label>
                    <div id="sys-failed-attempts" class="text-xs font-bold text-emerald-500">0</div>
                </div>
                <div>
                    <label class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Added By</label>
                    <div id="sys-created-by" class="text-xs font-bold text-text-primary">---</div>
                </div>
                <div>
                    <label class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Account ID</label>
                    <div id="sys-id" class="text-xs font-mono font-bold text-text-secondary">---</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Actions -->
    <div class="lg:col-span-4 space-y-6">
        
        <!-- Action Panel -->
        <div class="ds-card p-6">
            <h5 class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-4 block">Administrative Control</h5>
            <div class="space-y-3">
                <!-- Suspend/Activate -->
                <button id="action-suspend-btn" onclick="window.AdminDetail.toggleStatus()" class="w-full flex items-center justify-between p-4 bg-surface hover:bg-surface-hover rounded-2xl group transition-all border border-border-subtle hover:border-amber-500/40 cursor-pointer">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center group-hover:scale-105 transition-transform border border-amber-500/20">
                            <span id="suspend-icon" class="material-symbols-outlined text-[20px]" aria-hidden="true">block</span>
                        </div>
                        <div class="text-left">
                            <div id="suspend-label" class="text-xs font-black text-text-primary">Suspend Admin</div>
                            <div class="text-[10px] font-medium text-text-secondary">Temporarily revoke access</div>
                        </div>
                    </div>
                    <span class="material-symbols-outlined text-text-secondary text-[18px]" aria-hidden="true">chevron_right</span>
                </button>

                <!-- Reset Password -->
                <button onclick="window.AdminDetail.showResetModal()" class="w-full flex items-center justify-between p-4 bg-surface hover:bg-surface-hover rounded-2xl group transition-all border border-border-subtle hover:border-primary/40 cursor-pointer">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center group-hover:scale-105 transition-transform border border-primary/20">
                            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">lock_reset</span>
                        </div>
                        <div class="text-left">
                            <div class="text-xs font-black text-text-primary">Reset Password</div>
                            <div class="text-[10px] font-medium text-text-secondary">Force new credentials</div>
                        </div>
                    </div>
                    <span class="material-symbols-outlined text-text-secondary text-[18px]" aria-hidden="true">chevron_right</span>
                </button>

                <!-- Delete -->
                <button onclick="window.AdminDetail.showDeleteModal()" class="w-full flex items-center justify-between p-4 bg-surface hover:bg-rose-500/5 rounded-2xl group transition-all border border-border-subtle hover:border-rose-500/40 cursor-pointer">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-500 flex items-center justify-center group-hover:scale-105 transition-transform border border-rose-500/20">
                            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">person_remove</span>
                        </div>
                        <div class="text-left">
                            <div class="text-xs font-black text-rose-500">Delete Account</div>
                            <div class="text-[10px] font-medium text-text-secondary">Permanent removal</div>
                        </div>
                    </div>
                    <span class="material-symbols-outlined text-text-secondary text-[18px]" aria-hidden="true">chevron_right</span>
                </button>
            </div>
        </div>

        <!-- Role Privileges & Access Control Matrix -->
        <div class="ds-card p-6">
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-border-subtle">
                <div>
                    <h5 class="text-xs font-black uppercase tracking-widest text-text-primary flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[18px]">verified</span>
                        <span>Role Privileges</span>
                    </h5>
                    <p class="text-[10px] font-bold text-text-secondary uppercase tracking-widest mt-0.5" id="privilege-level-sub">Access Control Matrix</p>
                </div>
                <span id="privilege-role-badge" class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest bg-primary/10 text-primary border border-primary/20">Administrator</span>
            </div>

            <!-- Granted Section -->
            <div class="mb-4">
                <div class="text-[10px] font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-400 mb-2.5 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span>Granted Access (Authorized)</span>
                </div>
                <div id="privileges-granted" class="space-y-2">
                    <!-- Dynamic Granted Badges -->
                </div>
            </div>

            <!-- Restricted Section -->
            <div class="pt-3 border-t border-border-subtle">
                <div class="text-[10px] font-black uppercase tracking-widest text-rose-500 mb-2.5 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                    <span>Restricted Access (Forbidden)</span>
                </div>
                <div id="privileges-restricted" class="space-y-2">
                    <!-- Dynamic Restricted Badges -->
                </div>
            </div>
        </div>


        <!-- Security Insights -->
        <div class="p-6 bg-surface-subtle border border-border-subtle rounded-2xl overflow-hidden relative shadow-sm">
            <div class="relative z-10">
                <h5 class="text-[10px] font-black text-primary uppercase tracking-widest mb-2 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">verified_user</span> Security Policy
                </h5>
                <p id="security-note-text" class="text-xs leading-relaxed text-text-secondary font-medium italic">
                    "All administrative actions performed on this account are recorded in the global audit log and attributed to your session."
                </p>
                <div class="mt-4 pt-3 border-t border-border-subtle flex items-center justify-between">
                    <a id="view-audit-link" href="admin-audit-log.php" class="text-xs font-bold text-primary hover:underline flex items-center gap-1">
                        <span>View in Global Audit Log</span>
                        <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Activity Table -->
<div class="ds-card overflow-hidden mt-8 relative z-10 shadow-2xl transition-all duration-300">
    <div class="p-5 sm:p-6 border-b border-border-subtle bg-surface-subtle flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h4 class="font-bold text-xs uppercase tracking-widest flex items-center gap-2 text-text-primary">
                <span class="material-symbols-outlined text-primary text-[18px]" aria-hidden="true">history</span> Activity Timeline
            </h4>
            <p class="text-[10px] font-bold text-text-secondary uppercase tracking-widest mt-0.5" id="activity-count-label">0 total entries</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="relative">
                <input type="text" id="activity-search-input" onkeyup="window.AdminDetail.filterActivity()" placeholder="Search activities..." class="bg-surface text-text-primary text-xs font-bold px-3.5 py-2 pl-9 rounded-xl border border-border-subtle focus:border-text-primary focus:ring-2 focus:ring-text-primary focus:outline-none w-48 sm:w-64">
                <span class="material-symbols-outlined absolute left-2.5 top-2.5 text-text-secondary text-[16px]">search</span>
            </div>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="ds-table w-full text-left">
            <thead scope="col">
                <tr class="border-b border-border-subtle bg-surface/50">
                    <th scope="col" class="py-3 px-6 text-[10px] font-black uppercase tracking-widest text-text-secondary">Timestamp</th>
                    <th scope="col" class="py-3 px-6 text-[10px] font-black uppercase tracking-widest text-text-secondary">Action</th>
                    <th scope="col" class="py-3 px-6 text-[10px] font-black uppercase tracking-widest text-text-secondary">Details</th>
                    <th class="py-3 px-6 text-right text-[10px] font-black uppercase tracking-widest text-text-secondary" scope="col">IP Address</th>
                </tr>
            </thead>
            <tbody id="activity-tbody">
                <tr><td colspan="4" class="text-center py-12 text-text-secondary text-xs font-bold">No activity recorded yet.</td></tr>
            </tbody>
        </table>
    </div>
</div>


<!-- Reset Password Modal -->
<div id="reset-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-sm px-4">
    <div id="reset-modal-content" class="bg-surface border border-border-subtle rounded-3xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all relative">
        <div class="p-8">
            <div class="w-14 h-14 rounded-2xl bg-primary/10 text-primary border border-primary/20 flex items-center justify-center mb-5">
                <span class="material-symbols-outlined text-2xl" aria-hidden="true">lock_reset</span>
            </div>
            <h3 class="text-xl font-black text-text-primary mb-2">Reset Password</h3>
            <p class="text-xs font-medium text-text-secondary mb-6 leading-relaxed">
                A secure temporary password will be generated for this administrator.
            </p>
            
            <div id="reset-success-state" class="hidden mt-4 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl">
                <div class="text-[10px] font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-400 mb-2">Temporary Password (Click to Copy)</div>
                <div id="generated-password-display" class="font-mono text-lg font-black text-text-primary tracking-widest select-all mb-2 cursor-pointer p-2 bg-surface rounded-xl border border-border-subtle text-center"></div>
                <div class="text-[10px] text-text-secondary font-bold">Expires: <span id="password-expiry-display" class="text-text-primary"></span></div>
            </div>

            <div id="reset-modal-actions" class="flex items-center gap-3 mt-6">
                <button onclick="window.AdminDetail.closeResetModal()" class="btn btn-secondary flex-1 py-2.5 !rounded-xl !font-bold !text-xs cursor-pointer">Cancel</button>
                <button id="reset-submit-btn" onclick="window.AdminDetail.submitReset()" class="btn btn-primary flex-1 py-2.5 !rounded-xl !font-black !text-xs flex items-center justify-center gap-1.5 cursor-pointer">
                    <span class="material-symbols-outlined text-[16px]">autorenew</span> Generate
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Admin Modal -->
<div id="delete-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 backdrop-blur-md px-4">
    <div class="bg-surface border border-rose-500/30 rounded-3xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all relative">
        <div class="p-8 text-center flex flex-col items-center">
            <div class="w-16 h-16 rounded-full bg-rose-500/10 text-rose-500 border border-rose-500/20 flex items-center justify-center mb-5 animate-pulse">
                <span class="material-symbols-outlined text-3xl" aria-hidden="true">warning</span>
            </div>
            <h3 class="text-xl font-black text-text-primary mb-2">Delete <span id="delete-admin-name" class="text-rose-500">Admin</span>?</h3>
            <p class="text-xs font-medium text-text-secondary mb-6 leading-relaxed">
                This action is <strong>irreversible</strong>. The account will be permanently removed and access revoked immediately.
            </p>
            
            <div class="flex items-center gap-3 w-full">
                <button onclick="window.AdminDetail.closeDeleteModal()" class="btn btn-secondary flex-1 py-2.5 !rounded-xl !font-bold !text-xs cursor-pointer">Cancel</button>
                <button id="delete-submit-btn" onclick="window.AdminDetail.submitDelete()" class="btn flex-1 py-2.5 !rounded-xl !font-black !text-xs bg-rose-600 text-white hover:bg-rose-700 transition-colors shadow-lg cursor-pointer flex items-center justify-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">delete_forever</span> Confirm Delete
                </button>
            </div>
        </div>
    </div>
</div>

<?php
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/admin-detail.js?v=' . time() . '"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>

