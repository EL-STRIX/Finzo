<?php
$portal = 'admin';
$pageTitle = 'Admin Dossier';
ob_start();
?>
<div class="flex flex-col lg:grid lg:grid-cols-12 gap-8">
                
                <!-- Left Column: Details -->
                <div class="lg:col-span-8 space-y-8">
                    
                    <!-- Profile Card -->
                    <div class="ds-card p-6 sm:p-8">
                        <div class="flex flex-col sm:flex-row items-center gap-6 mb-8 pb-8 border-b border-surface-border dark:border-white/5">
                            <div class="relative group">
                                <div id="admin-photo-container" class="w-24 h-24 sm:w-32 sm:h-32 rounded-3xl overflow-hidden ring-4 ring-slate-100 dark:ring-white/5 shadow-xl bg-surface-hover dark:bg-white/5 flex items-center justify-center">
                                    <span id="photo-initials" class="text-4xl font-black text-text-muted">?</span>
                                </div>
                            </div>
                            <div class="text-center sm:text-left flex-1">
                                <h3 id="info-name" class="text-2xl sm:text-3xl font-black tracking-tight text-text-main dark:text-white">---</h3>
                                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-3 mt-3">
                                    <span id="info-admin-id" class="px-3 py-1 rounded-lg text-xs font-black uppercase tracking-widest bg-surface-hover text-text-muted dark:bg-white/5 dark:text-text-muted dark:text-text-muted border border-surface-border/50 dark:border-white/10 font-mono">---</span>
                                    <span id="info-role-badge" class="px-3 py-1 rounded-lg text-xs font-black uppercase tracking-widest">---</span>
                                    <span id="info-access-badge" class="px-3 py-1 rounded-lg text-xs font-black uppercase tracking-widest">---</span>
                                </div>
                                <p class="text-sm font-medium text-text-muted dark:text-text-muted dark:text-text-muted mt-4 flex items-center justify-center sm:justify-start gap-2">
                                    <span class="material-symbols-outlined text-lg" aria-hidden="true">calendar_today</span>
                                    Member since <span id="info-joined-date">---</span>
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-6">
                                <div>
                                    <label class="ds-label mb-1.5 block">Email Address</label>
                                    <div id="info-email" class="text-sm font-bold text-text-main dark:text-text-muted flex items-center gap-2">---</div>
                                </div>
                                <div>
                                    <label class="ds-label mb-1.5 block">Phone Number</label>
                                    <div id="info-phone" class="text-sm font-bold text-text-main dark:text-text-muted">---</div>
                                </div>
                            </div>
                            <div class="space-y-6">
                                <div>
                                    <label class="ds-label mb-1.5 block">Gender</label>
                                    <div id="info-gender" class="text-sm font-bold text-text-main dark:text-text-muted">---</div>
                                </div>
                                <div>
                                    <label class="ds-label mb-1.5 block">Date of Birth</label>
                                    <div id="info-dob" class="text-sm font-bold text-text-main dark:text-text-muted">---</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- System Information -->
                    <div class="ds-card">
                        <div class="p-6 border-b border-surface-border dark:border-white/5">
                            <h4 class="font-bold text-sm uppercase tracking-widest flex items-center gap-2 text-text-main dark:text-white">
                                <span class="material-symbols-outlined text-primary-base" aria-hidden="true">security</span> System Audit Info
                            </h4>
                        </div>
                        <div class="p-6 sm:p-8 grid grid-cols-2 md:grid-cols-3 gap-8">
                            <div>
                                <label class="ds-label mb-1.5 block">Last Login</label>
                                <div id="sys-last-login" class="text-xs font-bold text-text-main dark:text-text-muted">---</div>
                                <div id="sys-last-ip" class="text-xs font-black text-text-muted dark:text-text-muted mt-1">---</div>
                            </div>
                            <div>
                                <label class="ds-label mb-1.5 block">PWD Last Changed</label>
                                <div id="sys-pwd-change" class="text-xs font-bold text-text-main dark:text-text-muted">---</div>
                            </div>
                            <div>
                                <label class="ds-label mb-1.5 block">Failed Attempts</label>
                                <div id="sys-failed-attempts" class="text-xs font-bold text-danger-base">0</div>
                            </div>
                            <div>
                                <label class="ds-label mb-1.5 block">Added By</label>
                                <div id="sys-created-by" class="text-xs font-bold text-text-main dark:text-text-muted">---</div>
                            </div>
                            <div>
                                <label class="ds-label mb-1.5 block">Account ID</label>
                                <div id="sys-id" class="text-xs font-mono font-bold text-text-muted dark:text-text-muted dark:text-text-muted">---</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Actions -->
                <div class="lg:col-span-4 space-y-6">
                    
                    <!-- Action Panel -->
                    <div class="ds-card p-6">
                        <h5 class="ds-label mb-6 block">Administrative Control</h5>
                        <div class="space-y-4">
                            <!-- Suspend/Activate -->
                            <button id="action-suspend-btn" data-action="window.AdminDetail.toggleStatus()" class="w-full flex items-center justify-between p-4 bg-surface-subtle dark:bg-white/5 rounded-2xl group transition-all hover:bg-warning-surface dark:hover:bg-warning-surface/10 border border-transparent hover:border-warning-base">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-warning-surface/10 text-warning-base flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <span id="suspend-icon" class="material-symbols-outlined" aria-hidden="true">block</span>
                                    </div>
                                    <div class="text-left">
                                        <div id="suspend-label" class="text-sm font-black dark:text-white">Suspend Admin</div>
                                        <div class="text-xs font-medium text-text-muted dark:text-text-muted">Temporarily revoke access</div>
                                    </div>
                                </div>
                                <span class="material-symbols-outlined text-text-muted" aria-hidden="true">chevron_right</span>
                            </button>

                            <!-- Reset Password -->
                            <button data-action="window.AdminDetail.showResetModal()" class="w-full flex items-center justify-between p-4 bg-surface-subtle dark:bg-white/5 rounded-2xl group transition-all hover:bg-primary-surface dark:hover:bg-primary-surface/10 border border-transparent hover:border-blue-200">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-primary-surface/10 text-info-base flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <span class="material-symbols-outlined" aria-hidden="true">lock_reset</span>
                                    </div>
                                    <div class="text-left">
                                        <div class="text-sm font-black dark:text-white">Reset Password</div>
                                        <div class="text-xs font-medium text-text-muted dark:text-text-muted">Force new credentials</div>
                                    </div>
                                </div>
                                <span class="material-symbols-outlined text-text-muted" aria-hidden="true">chevron_right</span>
                            </button>

                            <!-- Delete -->
                            <button data-action="window.AdminDetail.showDeleteModal()" class="w-full flex items-center justify-between p-4 bg-surface-subtle dark:bg-white/5 rounded-2xl group transition-all hover:bg-danger-surface dark:hover:bg-danger-surface/10 border border-transparent hover:border-danger-base">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-danger-surface/10 text-danger-base flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <span class="material-symbols-outlined" aria-hidden="true">person_remove</span>
                                    </div>
                                    <div class="text-left">
                                        <div class="text-sm font-black text-danger-base">Delete Account</div>
                                        <div class="text-xs font-medium text-text-muted dark:text-text-muted">Permanent removal</div>
                                    </div>
                                </div>
                                <span class="material-symbols-outlined text-text-muted" aria-hidden="true">chevron_right</span>
                            </button>
                        </div>
                    </div>

                    <!-- Security Insights -->
                    <div class="p-6 bg-primary-surface dark:bg-primary-surface/10 border border-indigo-100 dark:border-indigo-500/20 rounded-2xl overflow-hidden relative shadow-sm">
                        <div class="relative z-10">
                            <h5 class="text-xs font-black text-primary-base dark:text-primary-base uppercase tracking-widest mb-4">Security Note</h5>
                            <p id="security-note-text" class="text-xs leading-relaxed text-primary-base dark:text-primary-base font-medium italic">
                                "All administrative actions performed on this account are recorded in the global audit log and attributed to your session."
                            </p>
                        </div>
                        <span class="material-symbols-outlined absolute -right-4 -bottom-4 text-8xl text-primary-base/10" aria-hidden="true">gavel</span>
                    </div>
                </div>
            </div>

            <!-- Activity Table -->
            <div class="ds-card overflow-hidden">
                <div class="p-6 border-b border-surface-border dark:border-white/5 flex justify-between items-center bg-surface-subtle/50 dark:bg-white/5">
                    <h4 class="font-bold text-sm uppercase tracking-widest flex items-center gap-2 dark:text-white">
                        <span class="material-symbols-outlined text-primary-base" aria-hidden="true">history</span> Activity Timeline
                    </h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="ds-table w-full text-left">
                        <thead scope="col">
                            <tr>
                                <th scope="col">Timestamp</th>
                                <th scope="col">Action</th>
                                <th scope="col">Details</th>
                                <th class="text-right" scope="col">IP Address</th>
                            </tr>
                        </thead>
                        <tbody id="activity-tbody">
                            <tr><td colspan="4" class="text-center py-12 text-text-muted dark:text-text-muted text-xs font-bold">No activity recorded yet.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>