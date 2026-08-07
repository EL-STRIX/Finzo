<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin';
ob_start();
?>
<!-- PROFILE HEADER -->
            <div class="ds-card p-6 sm:p-10 flex flex-col sm:flex-row items-center sm:items-start gap-8 bg-gradient-to-br from-white to-slate-50 dark:from-surface-main dark:to-surface-subtle border-t-4 border-t-primary">
                <div class="profile-avatar-wrapper flex-shrink-0">
                    <div class="profile-avatar" id="avatar-preview-container">
                        <span id="avatar-initials" class="uppercase">--</span>
                        <img loading="lazy" id="avatar-img-preview" src="" class="hidden w-full h-full object-cover" alt="">
                    </div>
                    <label for="photo-upload" class="avatar-upload-btn">
                        <span class="material-symbols-outlined text-base" aria-hidden="true">add_a_photo</span>
                    </label>
                    <input type="file" id="photo-upload" accept="image/jpeg, image/png" class="hidden" onchange="window.AdminProfile.uploadPhoto(this)">
                </div>
                <div class="text-center sm:text-left flex-1">
                    <h1 class="text-2xl sm:text-3xl font-black font-headline text-text-main dark:text-white tracking-tight mb-2" id="profile-name-display">Loading...</h1>
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-3 mt-4">
                        <span id="badge-role" class="px-3 py-1 rounded bg-primary-surface dark:bg-primary-surface/10 text-primary-base dark:text-primary-base text-xs font-black uppercase tracking-widest border border-blue-200 dark:border-blue-500/20 shadow-sm">—</span>
                        <span id="badge-access" class="px-3 py-1 rounded bg-purple-50 dark:bg-purple-500/10 text-purple-700 dark:text-purple-500 text-xs font-black uppercase tracking-widest border border-purple-200 dark:border-purple-500/20 shadow-sm">—</span>
                        <span id="badge-status" class="px-3 py-1 rounded bg-success-surface dark:bg-success-surface/10 text-success-base dark:text-success-base text-xs font-black uppercase tracking-widest border border-success-base dark:border-success-base/20 shadow-sm">—</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- LEFT COLUMN -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- PERSONAL INFO -->
                    <div class="ds-card p-6 sm:p-8">
                        <div class="flex items-center justify-between mb-6 pb-4 border-b border-surface-border dark:border-white/5">
                            <h3 class="font-headline font-bold text-lg text-text-main dark:text-white flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary-base dark:text-primary-base" aria-hidden="true">badge</span>
                                Administrator Identity
                            </h3>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-6">
                            <div>
                                <label class="fin-label mb-1">Admin ID (System Generated)</label>
                                <input type="text" id="profile-admin-id-input" class="fin-input opacity-70 bg-surface-hover dark:bg-white/5 cursor-not-allowed font-mono" readonly>
                            </div>
                            <div>
                                <label class="fin-label mb-1">Full Name *</label>
                                <input type="text" id="profile-name-input" class="fin-input">
                            </div>
                            <div>
                                <label class="fin-label mb-1">Date of Birth</label>
                                <input type="date" id="profile-dob-input" class="fin-input">
                            </div>
                            <div>
                                <label class="fin-label mb-1">Gender</label>
                                <select id="profile-gender-input" class="fin-input">
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                    <option value="Prefer not to say">Prefer not to say</option>
                                </select>
                            </div>
                            <div>
                                <label class="fin-label mb-1">Phone Number</label>
                                <input type="text" id="profile-phone-input" class="fin-input">
                            </div>
                            <div>
                                <label class="fin-label mb-1">Email Address (Read-only)</label>
                                <input type="email" id="profile-email-input" class="fin-input opacity-70 bg-surface-hover dark:bg-white/5 cursor-not-allowed" readonly>
                            </div>
                        </div>
                        <div class="mt-8 pt-6 border-t border-surface-border dark:border-white/5 flex justify-end">
                            <button data-action="window.AdminProfile.saveProfile()" class="btn btn-primary px-8">Save Profile</button>
                        </div>
                    </div>

                    <!-- SECURITY QUESTION -->
                    <div class="ds-card p-6 sm:p-8">
                        <div class="flex items-center justify-between mb-6 pb-4 border-b border-surface-border dark:border-white/5">
                            <h3 class="font-headline font-bold text-lg text-text-main dark:text-white flex items-center gap-2">
                                <span class="material-symbols-outlined text-teal-500" aria-hidden="true">quiz</span>
                                Master Security Question
                            </h3>
                            <div id="sq-status-container"></div>
                        </div>
                        <p class="text-sm text-text-muted dark:text-text-muted mb-6 font-medium">Configure a security question to recover this admin account if locked out. The answer is case-insensitive.</p>
                        <form id="sq-setup-form" onsubmit="window.AdminProfile.saveSecurityQuestion(event)" class="space-y-6">
                            <div>
                                <label class="fin-label mb-1">Security Question *</label>
                                <input type="text" id="sq-question" class="fin-input" placeholder="e.g. In what city were you born?" minlength="10" required>
                            </div>
                            <div>
                                <label class="fin-label mb-1">Answer *</label>
                                <input type="password" id="sq-answer" class="fin-input" placeholder="Your secret answer" minlength="3" required>
                            </div>
                            <div class="pt-4 border-t border-surface-border dark:border-white/5">
                                <label class="fin-label mb-1">Current Password (to verify) *</label>
                                <input type="password" id="sq-curr-pw" class="fin-input" placeholder="Authenticate changes" required>
                            </div>
                            <div class="flex justify-end pt-4">
                                <button type="submit" id="btn-save-sq" class="btn btn-secondary px-8"><span id="sq-btn-text">Set Security Question</span></button>
                            </div>
                        </form>
                    </div>

                    <!-- ACTIVITY TABLE -->
                    <div class="ds-card overflow-hidden">
                        <div class="p-6 border-b border-surface-border dark:border-white/5 flex justify-between items-center bg-surface-subtle/50 dark:bg-white/2">
                            <h3 class="font-headline font-bold text-lg flex items-center gap-2 text-text-main dark:text-white">
                                <span class="material-symbols-outlined text-text-muted" aria-hidden="true">history</span>
                                Admin Activity Log
                            </h3>
                        </div>
                        <div class="overflow-x-auto w-full">
                            <table class="ds-table w-full text-left">
                                <thead scope="col">
                                    <tr>
                                        <th scope="col">Action</th>
                                        <th scope="col">Details</th>
                                        <th scope="col">IP Address</th>
                                        <th scope="col">Date & Time</th>
                                    </tr>
                                </thead>
                                <tbody id="activity-table-body">
                                    <tr><td colspan="4" class="text-center italic opacity-50 py-8 text-sm">Loading activity...</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="p-4 border-t border-surface-border dark:border-white/5 flex items-center justify-between bg-surface-subtle/50 dark:bg-white/2 hidden" id="activity-pagination-container">
                            <div class="text-xs font-black text-text-muted uppercase tracking-widest" id="activity-pagination-info">Showing 0 to 0 of 0</div>
                            <div class="flex gap-2">
                                <button id="activity-prev-btn" data-action="window.AdminProfile.prevActivityPage()" class="btn btn-secondary !px-3 !py-1.5 !text-xs">Prev</button>
                                <button id="activity-next-btn" data-action="window.AdminProfile.nextActivityPage()" class="btn btn-secondary !px-3 !py-1.5 !text-xs">Next</button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- ADMIN MANAGEMENT (Super Admin) -->
                    <div id="admin-management-container" class="ds-card overflow-hidden hidden">
                        <div class="p-6 border-b border-surface-border dark:border-white/5 flex justify-between items-center bg-warning-surface/30 dark:bg-warning-surface/5">
                            <h3 class="font-headline font-bold text-lg flex items-center gap-2 text-warning-base dark:text-warning-base">
                                <span class="material-symbols-outlined text-warning-base" aria-hidden="true">manage_accounts</span>
                                Global Admin Directory
                            </h3>
                            <div class="flex gap-3">
                                <span id="management-count-badge" class="px-3 py-1 bg-warning-surface text-warning-base dark:bg-warning-surface/20 dark:text-warning-base text-xs font-black rounded uppercase tracking-widest border border-warning-base dark:border-warning-base/30">0 Admins</span>
                                <button id="header-add-admin-btn" data-action="window.AdminProfile.showAddModal()" class="btn btn-primary !px-4 !py-1 !text-xs">Add Admin</button>
                            </div>
                        </div>
                        <!-- Filters -->
                        <div id="admin-filter-bar" class="p-4 border-b border-surface-border dark:border-white/5 bg-surface-subtle dark:bg-white/5 flex gap-4">
                            <input type="text" id="admin-search-input" placeholder="Search admins..." class="fin-input flex-1">
                            <select id="admin-status-filter" class="fin-input w-40">
                                <option value="all">All Status</option>
                                <option value="active">Active</option>
                                <option value="suspended">Suspended</option>
                            </select>
                            <select id="admin-role-filter" class="fin-input w-40">
                                <option value="all">All Roles</option>
                                <option value="admin">Admin</option>
                                <option value="moderator">Moderator</option>
                            </select>
                        </div>
                        <div class="overflow-x-auto w-full">
                            <table class="ds-table w-full text-left min-w-[800px]">
                                <thead scope="col">
                                    <tr>
                                        <th scope="col">ID</th>
                                        <th scope="col">Admin Name & Email</th>
                                        <th scope="col">Role</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Added On</th>
                                        <th class="text-right" scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="admin-management-body">
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- LOCKED ADMINS (Super Admin) -->
                    <div id="locked-admins-container" class="ds-card overflow-hidden hidden">
                        <div class="p-6 border-b border-surface-border dark:border-white/5 flex justify-between items-center bg-danger-surface/50 dark:bg-danger-surface/5">
                            <h3 class="font-headline font-bold text-lg flex items-center gap-2 text-danger-base dark:text-danger-base">
                                <span class="material-symbols-outlined text-danger-base" aria-hidden="true">lock</span>
                                Locked Admin Accounts
                            </h3>
                            <span id="locked-count-badge" class="px-3 py-1 bg-danger-surface text-danger-base dark:bg-danger-surface/20 dark:text-danger-base text-xs font-black rounded uppercase tracking-widest border border-danger-base dark:border-danger-base/30">0 Locked</span>
                        </div>
                        <div class="overflow-x-auto w-full">
                            <table class="ds-table w-full text-left min-w-[800px]">
                                <thead scope="col">
                                    <tr>
                                        <th scope="col">Admin Profile</th>
                                        <th scope="col">Locked At</th>
                                        <th scope="col">Reason</th>
                                        <th scope="col">Attempts</th>
                                        <th scope="col">Security Q</th>
                                        <th class="text-right" scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="locked-admins-body">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN -->
                <div class="space-y-6">
                    <!-- SECURITY / PASSWORD -->
                    <div class="ds-card p-6 sm:p-8">
                        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-surface-border dark:border-white/5">
                            <div class="w-10 h-10 rounded-xl bg-surface-hover dark:bg-white/10 text-text-muted dark:text-text-muted flex items-center justify-center shadow-sm">
                                <span class="material-symbols-outlined" aria-hidden="true">password</span>
                            </div>
                            <h3 class="font-headline font-bold text-lg text-text-main dark:text-white">Update Password</h3>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="fin-label mb-1">Current Password</label>
                                <input type="password" id="curr-pw" class="fin-input">
                            </div>
                            <div>
                                <label class="fin-label mb-1">New Password</label>
                                <input type="password" id="new-pw" class="fin-input" oninput="window.AdminProfile.checkStrength(this.value)">
                                <div class="mt-2 pl-1">
                                    <div class="password-strength-bar">
                                        <div class="password-strength-fill" id="pw-bar" style="width:0%"></div>
                                    </div>
                                    <div class="text-xs font-bold mt-1 text-text-muted uppercase tracking-widest" id="pw-text"></div>
                                </div>
                            </div>
                            <div>
                                <label class="fin-label mb-1">Confirm New Password</label>
                                <input type="password" id="conf-pw" class="fin-input">
                            </div>
                            <button data-action="window.AdminProfile.updatePassword()" class="w-full btn btn-primary mt-4">Change Password</button>
                        </div>
                    </div>

                    <!-- SECURITY STATUS -->
                    <div class="ds-card p-6 sm:p-8 space-y-4">
                        <h3 class="font-headline font-bold text-lg text-text-main dark:text-white mb-4">Security Audit</h3>
                        
                        <div class="flex justify-between items-center py-2 border-b border-surface-border dark:border-white/5">
                            <span class="text-xs font-black text-text-muted uppercase tracking-widest">Last Login</span>
                            <div class="text-right">
                                <div id="sec-last-login" class="text-xs font-bold text-text-main dark:text-white">—</div>
                            </div>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-surface-border dark:border-white/5">
                            <span class="text-xs font-black text-text-muted uppercase tracking-widest">IP Address</span>
                            <span class="text-xs font-mono font-bold text-text-main dark:text-white" id="sec-last-ip">—</span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-xs font-black text-text-muted uppercase tracking-widest">Pwd Updated</span>
                            <span class="text-xs font-bold text-text-main dark:text-white" id="sec-last-pwd">—</span>
                        </div>
                    </div>
                </div>

            </div>
<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>