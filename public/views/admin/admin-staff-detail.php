<?php
$portal = 'admin';
$pageTitle = 'Staff Dossier';
ob_start();
?>
<!-- ALERT BANNER FOR SUSPENSION OR LOCK -->
            <div id="security-alert-banner" class="hidden rounded-2xl p-4 sm:p-5 flex items-start gap-4">
                <span id="security-alert-icon" class="material-symbols-outlined text-3xl shrink-0 mt-1" aria-hidden="true">warning</span>
                <div>
                    <h4 id="security-alert-title" class="text-sm sm:text-base font-black tracking-tight mb-1 uppercase">Alert</h4>
                    <p id="security-alert-message" class="text-xs sm:text-sm font-medium leading-relaxed opacity-90"></p>
                </div>
            </div>
            <div class="flex flex-col lg:grid lg:grid-cols-12 gap-6 lg:gap-8">
                
                <!-- ════════════════════════════════════════ -->
                <!-- LEFT COLUMN (8 cols) -->
                <!-- ════════════════════════════════════════ -->
                <div class="lg:col-span-8 space-y-6">
                    
                    <!-- ═══ PROFILE CARD ═══ -->
                    <div class="ds-card p-6 sm:p-8">
                        <div class="flex flex-col sm:flex-row items-center gap-6 mb-8 pb-8 border-b border-surface-border dark:border-white/5">
                            <div class="relative">
                                <div id="staff-photo-container" class="w-24 h-24 sm:w-28 sm:h-28 rounded-3xl overflow-hidden ring-4 ring-slate-100 dark:ring-white/5 shadow-xl bg-surface-border dark:bg-white/5 flex items-center justify-center">
                                    <span id="photo-initials" class="text-4xl font-black text-text-muted">?</span>
                                </div>
                            </div>
                            <div class="text-center sm:text-left flex-1">
                                <h3 id="info-name" class="text-2xl sm:text-3xl font-black tracking-tight text-text-main dark:text-white mb-3">Loading...</h3>
                                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mb-4">
                                    <span id="info-staff-id-badge" class="px-3 py-1 rounded-lg text-xs font-black uppercase tracking-widest bg-surface-hover text-text-muted dark:bg-white/5 dark:text-text-muted border border-surface-border/50 dark:border-white/10 font-mono">---</span>
                                    <span id="info-designation-badge" class="px-3 py-1 rounded-lg text-xs font-black uppercase tracking-widest bg-primary-surface text-white">---</span>
                                    <span id="info-department-badge" class="px-3 py-1 rounded-lg text-xs font-black uppercase tracking-widest bg-success-surface text-success-base dark:bg-success-surface/20 dark:text-success-base">---</span>
                                </div>
                                <p class="text-sm font-medium text-text-muted dark:text-text-muted flex items-center justify-center sm:justify-start gap-2">
                                    <span class="material-symbols-outlined text-lg" aria-hidden="true">calendar_today</span>
                                    Member since <span id="info-joined-date" class="font-bold">---</span>
                                </p>
                            </div>
                        </div>

                        <!-- Personal Info Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-5">
                            <div>
                                <label class="fin-label mb-1">Email Address</label>
                                <div id="info-email" class="text-sm font-bold text-text-main dark:text-text-muted">---</div>
                            </div>
                            <div>
                                <label class="fin-label mb-1">Gender</label>
                                <div id="info-gender" class="text-sm font-bold text-text-main dark:text-text-muted">---</div>
                            </div>
                            <div>
                                <label class="fin-label mb-1">Phone Number</label>
                                <div id="info-phone" class="text-sm font-bold text-text-main dark:text-text-muted">---</div>
                            </div>
                            <div>
                                <label class="fin-label mb-1">Date of Birth</label>
                                <div id="info-dob" class="text-sm font-bold text-text-main dark:text-text-muted">---</div>
                            </div>
                            <div>
                                <label class="fin-label mb-1">Emergency Contact</label>
                                <div id="info-emergency" class="text-sm font-bold text-text-main dark:text-text-muted">---</div>
                            </div>
                            <div>
                                <label class="fin-label mb-1">Branch Assignment</label>
                                <div id="info-branch" class="text-sm font-bold text-text-main dark:text-text-muted">---</div>
                            </div>
                        </div>
                    </div>

                    <!-- ═══ ADDRESS ═══ -->
                    <div class="ds-card">
                        <div class="p-5 border-b border-surface-border dark:border-white/5">
                            <h4 class="font-bold text-xs uppercase tracking-widest flex items-center gap-2 text-text-main dark:text-white">
                                <span class="material-symbols-outlined text-primary-base text-lg" aria-hidden="true">location_on</span> Residential Address
                            </h4>
                        </div>
                        <div class="p-5 grid grid-cols-2 sm:grid-cols-5 gap-5">
                            <div>
                                <label class="fin-label mb-1">Street</label>
                                <div id="addr-street" class="text-sm font-bold text-text-main dark:text-text-muted">---</div>
                            </div>
                            <div>
                                <label class="fin-label mb-1">City</label>
                                <div id="addr-city" class="text-sm font-bold text-text-main dark:text-text-muted">---</div>
                            </div>
                            <div>
                                <label class="fin-label mb-1">State</label>
                                <div id="addr-state" class="text-sm font-bold text-text-main dark:text-text-muted">---</div>
                            </div>
                            <div>
                                <label class="fin-label mb-1">Pincode</label>
                                <div id="addr-pincode" class="text-sm font-bold text-text-main dark:text-text-muted">---</div>
                            </div>
                            <div>
                                <label class="fin-label mb-1">Country</label>
                                <div id="addr-country" class="text-sm font-bold text-text-main dark:text-text-muted">---</div>
                            </div>
                        </div>
                    </div>

                    <!-- ═══ SYSTEM AUDIT INFO ═══ -->
                    <div class="ds-card">
                        <div class="p-5 border-b border-surface-border dark:border-white/5">
                            <h4 class="font-bold text-xs uppercase tracking-widest flex items-center gap-2 text-text-main dark:text-white">
                                <span class="material-symbols-outlined text-primary-base text-lg" aria-hidden="true">security</span> System Audit Info
                            </h4>
                        </div>
                        <div class="p-5 grid grid-cols-2 sm:grid-cols-4 gap-5">
                            <div>
                                <label class="fin-label mb-1">Last Login</label>
                                <div id="sys-last-login" class="text-xs font-bold text-text-main dark:text-text-muted">---</div>
                            </div>
                            <div>
                                <label class="fin-label mb-1">Login IP</label>
                                <div id="sys-last-ip" class="text-xs font-mono font-bold text-text-muted dark:text-text-muted">---</div>
                            </div>
                            <div>
                                <label class="fin-label mb-1">Date of Joining</label>
                                <div id="sys-joining-date" class="text-xs font-bold text-text-main dark:text-text-muted">---</div>
                            </div>
                            <div>
                                <label class="fin-label mb-1">Created By</label>
                                <div id="sys-created-by" class="text-xs font-bold text-text-main dark:text-text-muted">---</div>
                            </div>
                            <div>
                                <label class="fin-label mb-1">Staff ID</label>
                                <div id="sys-staff-id" class="text-xs font-mono font-bold text-text-muted dark:text-text-muted">---</div>
                            </div>
                            <div>
                                <label class="fin-label mb-1">Total Shifts</label>
                                <div id="sys-total-shifts" class="text-xs font-bold text-text-main dark:text-text-muted">---</div>
                            </div>
                            <div>
                                <label class="fin-label mb-1">Failed Logins</label>
                                <div id="sys-failed-logins" class="text-xs font-bold text-text-main dark:text-text-muted">---</div>
                            </div>
                            <div>
                                <label class="fin-label mb-1">Lock Status</label>
                                <div id="sys-lock-status" class="text-xs font-bold text-text-main dark:text-text-muted">---</div>
                            </div>
                        </div>
                    </div>

                    <!-- ═══ IDENTITY DOCUMENTS ═══ -->
                    <div class="ds-card">
                        <div class="p-5 border-b border-surface-border dark:border-white/5">
                            <h4 class="font-bold text-xs uppercase tracking-widest flex items-center gap-2 text-text-main dark:text-white">
                                <span class="material-symbols-outlined text-primary-base text-lg" aria-hidden="true">badge</span> Identity Documents
                            </h4>
                        </div>
                        <div class="p-5">
                            <div class="grid grid-cols-2 gap-5 mb-5">
                                <div>
                                    <label class="fin-label mb-1">ID Type</label>
                                    <div id="doc-id-type" class="text-sm font-bold text-text-main dark:text-text-muted">---</div>
                                </div>
                                <div>
                                    <label class="fin-label mb-1">ID Number</label>
                                    <div id="doc-id-number" class="text-sm font-bold font-mono text-text-main dark:text-text-muted">---</div>
                                </div>
                            </div>
                            <div id="doc-images-container" class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <!-- Front -->
                                <div>
                                    <label class="fin-label mb-2">Front of Document</label>
                                    <div id="doc-front-wrapper" class="relative group rounded-2xl overflow-hidden bg-surface-subtle dark:bg-white/5 border-2 border-dashed border-surface-border dark:border-white/10 flex items-center justify-center aspect-[16/10] cursor-pointer transition-all hover:border-primary/30 hover:shadow-lg">
                                        <div id="doc-front-placeholder" class="text-center">
                                            <span class="material-symbols-outlined text-4xl text-text-muted dark:text-text-muted" aria-hidden="true">image</span>
                                            <p class="text-xs font-bold text-text-muted mt-1">No document uploaded</p>
                                        </div>
                                        <img loading="lazy" id="doc-front-img" class="hidden absolute inset-0 w-full h-full object-cover transition-transform group-hover:scale-105" alt="ID Front" />
                                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-all flex items-center justify-center opacity-0 group-hover:opacity-100">
                                            <span class="material-symbols-outlined text-white text-3xl drop-shadow-lg" aria-hidden="true">zoom_in</span>
                                        </div>
                                    </div>
                                </div>
                                <!-- Back -->
                                <div>
                                    <label class="fin-label mb-2">Back of Document</label>
                                    <div id="doc-back-wrapper" class="relative group rounded-2xl overflow-hidden bg-surface-subtle dark:bg-white/5 border-2 border-dashed border-surface-border dark:border-white/10 flex items-center justify-center aspect-[16/10] cursor-pointer transition-all hover:border-primary/30 hover:shadow-lg">
                                        <div id="doc-back-placeholder" class="text-center">
                                            <span class="material-symbols-outlined text-4xl text-text-muted dark:text-text-muted" aria-hidden="true">image</span>
                                            <p class="text-xs font-bold text-text-muted mt-1">No document uploaded</p>
                                        </div>
                                        <img loading="lazy" id="doc-back-img" class="hidden absolute inset-0 w-full h-full object-cover transition-transform group-hover:scale-105" alt="ID Back" />
                                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-all flex items-center justify-center opacity-0 group-hover:opacity-100">
                                            <span class="material-symbols-outlined text-white text-3xl drop-shadow-lg" aria-hidden="true">zoom_in</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- ════════════════════════════════════════ -->
                <!-- RIGHT COLUMN (4 cols) -->
                <!-- ════════════════════════════════════════ -->
                <div class="lg:col-span-4 space-y-6">
                    
                    <!-- ═══ STAFF CONTROL ═══ -->
                    <div class="ds-card p-5">
                        <h5 class="text-xs font-black text-text-muted uppercase tracking-widest mb-5">Staff Control</h5>
                        <div class="space-y-3">
                            <button id="deactivate-staff-btn" class="w-full flex items-center justify-between p-3.5 bg-surface-subtle dark:bg-white/5 rounded-2xl group transition-all hover:bg-warning-surface dark:hover:bg-warning-surface/10 border border-transparent hover:border-warning-base">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-warning-surface/10 text-warning-base flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <span id="suspend-icon" class="material-symbols-outlined text-xl" aria-hidden="true">block</span>
                                    </div>
                                    <div class="text-left">
                                        <div id="suspend-label" class="text-xs font-black dark:text-white">Deactivate Staff</div>
                                        <div class="text-xs font-medium text-text-muted">Temporarily revoke access</div>
                                    </div>
                                </div>
                                <span class="material-symbols-outlined text-text-muted text-lg" aria-hidden="true">chevron_right</span>
                            </button>

                            <button id="reset-password-btn" class="w-full flex items-center justify-between p-3.5 bg-surface-subtle dark:bg-white/5 rounded-2xl group transition-all hover:bg-primary-surface dark:hover:bg-primary-surface/10 border border-transparent hover:border-blue-200">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-primary-surface/10 text-info-base flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <span class="material-symbols-outlined text-xl" aria-hidden="true">lock_reset</span>
                                    </div>
                                    <div class="text-left">
                                        <div class="text-xs font-black dark:text-white">Reset Password</div>
                                        <div class="text-xs font-medium text-text-muted">Force new credentials</div>
                                    </div>
                                </div>
                                <span class="material-symbols-outlined text-text-muted text-lg" aria-hidden="true">chevron_right</span>
                            </button>

                            <button id="delete-staff-btn" class="w-full flex items-center justify-between p-3.5 bg-surface-subtle dark:bg-white/5 rounded-2xl group transition-all hover:bg-danger-surface dark:hover:bg-danger-surface/10 border border-transparent hover:border-danger-base">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-danger-surface/10 text-danger-base flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <span class="material-symbols-outlined text-xl" aria-hidden="true">person_remove</span>
                                    </div>
                                    <div class="text-left">
                                        <div class="text-xs font-black text-danger-base">Delete Account</div>
                                        <div class="text-xs font-medium text-text-muted">Permanent removal</div>
                                    </div>
                                </div>
                                <span class="material-symbols-outlined text-text-muted text-lg" aria-hidden="true">chevron_right</span>
                            </button>
                        </div>
                    </div>

                    <!-- ═══ PERFORMANCE STATS ═══ -->
                    <div class="ds-card p-5">
                        <h5 class="text-xs font-black text-text-muted uppercase tracking-widest mb-5">Performance Overview</h5>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="text-center p-3 bg-surface-subtle dark:bg-white/5 rounded-xl">
                                <div class="text-xs font-black text-text-muted uppercase mb-1">Deposits</div>
                                <div id="stat-total-deposits" class="text-sm font-black text-primary-base dark:text-primary-base">₹0</div>
                            </div>
                            <div class="text-center p-3 bg-surface-subtle dark:bg-white/5 rounded-xl">
                                <div class="text-xs font-black text-text-muted uppercase mb-1">Withdrawals</div>
                                <div id="stat-total-withdrawals" class="text-sm font-black text-danger-base">₹0</div>
                            </div>
                            <div class="text-center p-3 bg-surface-subtle dark:bg-white/5 rounded-xl">
                                <div class="text-xs font-black text-text-muted uppercase mb-1">Transactions</div>
                                <div id="stat-total-txns" class="text-sm font-black text-text-main dark:text-white">0</div>
                            </div>
                            <div class="text-center p-3 bg-surface-subtle dark:bg-white/5 rounded-xl">
                                <div class="text-xs font-black text-text-muted uppercase mb-1">Last Active</div>
                                <div id="stat-last-active" class="text-xs font-black text-text-main dark:text-white">Never</div>
                            </div>
                        </div>
                    </div>

                    <!-- ═══ TODAY'S SHIFT ═══ -->
                    <div class="ds-card p-5">
                        <h5 class="text-xs font-black text-text-muted uppercase tracking-widest mb-5">Today's Shift Summary</h5>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center pb-2.5 border-b border-surface-border dark:border-white/5">
                                <span class="text-xs font-black text-text-muted uppercase">Deposits Done</span>
                                <span id="summary-deposits" class="text-xs font-bold text-text-main dark:text-text-muted">0</span>
                            </div>
                            <div class="flex justify-between items-center pb-2.5 border-b border-surface-border dark:border-white/5">
                                <span class="text-xs font-black text-text-muted uppercase">Withdrawals Done</span>
                                <span id="summary-withdrawals" class="text-xs font-bold text-text-main dark:text-text-muted">0</span>
                            </div>
                            <div class="flex justify-between items-center pb-2.5 border-b border-surface-border dark:border-white/5">
                                <span class="text-xs font-black text-text-muted uppercase">Total Transactions</span>
                                <span id="summary-total-txns" class="text-xs font-bold text-text-main dark:text-text-muted">0</span>
                            </div>
                            <div class="flex justify-between items-center pt-1">
                                <span class="text-xs font-black text-primary-base dark:text-primary-base uppercase">Net Amount</span>
                                <span id="summary-total-amount" class="text-lg font-black text-primary-base dark:text-primary-base">₹0.00</span>
                            </div>
                        </div>
                    </div>

                    <!-- ═══ SHIFT HISTORY ═══ -->
                    <div class="ds-card p-5">
                        <h5 class="text-xs font-black text-text-muted uppercase tracking-widest mb-5 flex justify-between items-center">
                            <span>Recent Shifts</span>
                            <span class="material-symbols-outlined text-sm" aria-hidden="true">schedule</span>
                        </h5>
                        <div id="staff-shift-history" class="space-y-3">
                            <div class="text-xs text-text-muted text-center py-4">Loading shift history...</div>
                        </div>
                    </div>

                    <!-- ═══ SECURITY NOTE ═══ -->
                    <div class="p-5 bg-primary-surface dark:bg-primary-surface/10 border border-indigo-100 dark:border-indigo-500/20 rounded-2xl overflow-hidden relative">
                        <div class="relative z-10">
                            <h5 class="text-xs font-black text-primary-base dark:text-primary-base uppercase tracking-widest mb-3">Security Note</h5>
                            <p class="text-xs leading-relaxed text-primary-base dark:text-primary-base font-medium italic">
                                "All staff-related actions are recorded in the global audit log. Every modification is attributed to the performing administrator's session."
                            </p>
                        </div>
                        <span class="material-symbols-outlined absolute -right-4 -bottom-4 text-8xl text-primary-base/10" aria-hidden="true">gavel</span>
                    </div>
                </div>
            </div>

            <!-- ═══ TRANSACTION HISTORY (Full Width) ═══ -->
            <div class="ds-card overflow-hidden">
                <div class="p-5 border-b border-surface-border dark:border-white/5 flex justify-between items-center bg-surface-subtle/50 dark:bg-white/5">
                    <h4 class="font-bold text-xs uppercase tracking-widest flex items-center gap-2 dark:text-white">
                        <span class="material-symbols-outlined text-primary-base text-lg" aria-hidden="true">history</span> Transaction History
                    </h4>
                    <button data-action="window.open(&#039;../../../api/pdf/staff-statement.php?staff_id=&#039; + new URLSearchParams(window.location.search).get(&#039;id&#039;), &#039;_blank&#039;)" class="flex items-center gap-2 py-2 px-4 bg-white dark:bg-surface-dark border border-surface-border dark:border-white/10 rounded-xl text-xs font-black uppercase tracking-tighter text-text-muted hover:text-primary-base dark:hover:text-white transition-all shadow-sm">
                        <span class="material-symbols-outlined text-lg" aria-hidden="true">print</span>
                        Print Statement
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="ds-table w-full text-left">
                        <thead scope="col">
                            <tr>
                                <th scope="col">Receipt No</th>
                                <th scope="col">Date & Time</th>
                                <th scope="col">Customer</th>
                                <th scope="col">Account</th>
                                <th scope="col">Type</th>
                                <th scope="col">Amount</th>
                                <th class="text-right" scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody id="staff-transactions-body">
                            <tr><td colspan="7" class="text-center py-12 text-text-muted text-xs font-bold">Loading transactions...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ═══ RECENT ACTIVITY (Full Width) ═══ -->
            <div class="ds-card overflow-hidden mt-6">
                <div class="p-5 border-b border-surface-border dark:border-white/5 flex justify-between items-center bg-surface-subtle/50 dark:bg-white/5">
                    <h4 class="font-bold text-xs uppercase tracking-widest flex items-center gap-2 dark:text-white">
                        <span class="material-symbols-outlined text-primary-base text-lg" aria-hidden="true">local_activity</span> Recent Activity
                    </h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="ds-table w-full text-left">
                        <thead scope="col">
                            <tr>
                                <th scope="col">Date & Time</th>
                                <th scope="col">Action Type</th>

                                <th scope="col">Details</th>
                                <th class="text-right" scope="col">IP Address</th>
                            </tr>
                        </thead>
                        <tbody id="staff-activities-body">
                            <tr><td colspan="5" class="text-center py-12 text-text-muted text-xs font-bold">Loading activity...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
<?php
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/staff-detail.js"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>