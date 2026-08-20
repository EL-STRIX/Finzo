<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin - Admin Profile';
$pageHeader = '
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-headline font-black text-text-primary tracking-tight">Admin Profile</h1>
        <p class="text-[10px] font-black text-text-secondary uppercase tracking-widest mt-0.5">Manage your administrator account and security settings</p>
    </div>
</div>
';
ob_start();
?>
<!-- PROFILE HEADER -->
            <div class="ds-card p-6 sm:p-10 flex flex-col sm:flex-row items-center sm:items-start gap-8 bg-surface-subtle">
                <div class="profile-avatar-wrapper flex-shrink-0 relative">
                    <div class="profile-avatar w-32 h-32 rounded-3xl bg-primary-50 text-primary flex items-center justify-center text-4xl font-black shadow-lg relative overflow-hidden" id="avatar-preview-container">
                        <span id="avatar-initials" class="uppercase">--</span>
                        <img loading="lazy" id="avatar-img-preview" src="" class="hidden w-full h-full object-cover" alt="">
                    </div>
                    <label for="photo-upload" class="avatar-upload-btn absolute -bottom-3 -right-3 w-12 h-12 bg-primary-base text-white rounded-2xl flex items-center justify-center cursor-pointer shadow-xl hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined text-base" aria-hidden="true">add_a_photo</span>
                    </label>
                    <input type="file" id="photo-upload" accept="image/jpeg, image/png" class="hidden" onchange="window.AdminProfile.uploadPhoto(this)">
                </div>
                <div class="text-center sm:text-left flex-1 mt-4 sm:mt-0">
                    <h1 class="text-2xl sm:text-3xl font-black font-headline text-text-primary tracking-tight mb-2" id="profile-name-display">Loading...</h1>
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-3 mt-4">
                        <span id="badge-role" class="px-3 py-1 rounded bg-primary-50 text-primary text-[10px] font-black uppercase tracking-widest border border-primary-200/50 shadow-sm">—</span>
                        <span id="badge-access" class="px-3 py-1 rounded bg-info-bg text-info text-[10px] font-black uppercase tracking-widest border border-info-border shadow-sm">—</span>
                        <span id="badge-status" class="px-3 py-1 rounded bg-success-bg text-success text-[10px] font-black uppercase tracking-widest border border-success-border shadow-sm">—</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- LEFT COLUMN -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- PERSONAL INFO -->
                    <div class="ds-card p-6 sm:p-8">
                        <div class="flex items-center justify-between mb-6 pb-4 border-b border-border-subtle">
                            <h3 class="font-headline font-bold text-lg text-text-primary flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary-base" aria-hidden="true">badge</span>
                                Administrator Identity
                            </h3>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-6">
                            <div>
                                <label for="profile-admin-id-input" class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Admin ID (System Generated)</label>
                                <input type="text" id="profile-admin-id-input" class="fin-input opacity-70 bg-surface-subtle cursor-not-allowed font-mono" readonly>
                            </div>
                            <div>
                                <label for="profile-name-input" class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Full Name *</label>
                                <input type="text" id="profile-name-input" class="fin-input">
                            </div>
                            <div>
                                <label for="profile-dob-input" class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Date of Birth</label>
                                <input type="date" id="profile-dob-input" class="fin-input">
                            </div>
                            <div>
                                <label for="profile-gender-input" class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Gender</label>
                                <select id="profile-gender-input" class="fin-input">
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                    <option value="Prefer not to say">Prefer not to say</option>
                                </select>
                            </div>
                            <div>
                                <label for="profile-phone-input" class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Phone Number</label>
                                <input type="text" id="profile-phone-input" class="fin-input">
                            </div>
                            <div>
                                <label for="profile-email-input" class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Email Address (Read-only)</label>
                                <input type="email" id="profile-email-input" class="fin-input opacity-70 bg-surface-subtle cursor-not-allowed" readonly>
                            </div>
                        </div>
                        <div class="mt-8 pt-6 border-t border-border-subtle flex justify-end">
                            <button onclick="window.AdminProfile.saveProfile()" data-action="window.AdminProfile.saveProfile()" class="btn btn-primary px-8">Save Profile</button>
                        </div>
                    </div>

                    <!-- SECURITY QUESTION -->
                    <div class="ds-card p-6 sm:p-8">
                        <div class="flex items-center justify-between mb-6 pb-4 border-b border-border-subtle">
                            <h3 class="font-headline font-bold text-lg text-text-primary flex items-center gap-2">
                                <span class="material-symbols-outlined text-info" aria-hidden="true">quiz</span>
                                Master Security Question
                            </h3>
                            <div id="sq-status-container"></div>
                        </div>
                        <p class="text-sm text-text-secondary mb-6 font-medium">Configure a security question to recover this admin account if locked out. The answer is case-insensitive.</p>
                        <form id="sq-setup-form" onsubmit="window.AdminProfile.saveSecurityQuestion(event)" class="space-y-6">
                            <div>
                                <label for="sq-question" class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Security Question *</label>
                                <input type="text" id="sq-question" class="fin-input" placeholder="e.g. In what city were you born?" minlength="10" required>
                            </div>
                            <div>
                                <label for="sq-answer" class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Answer *</label>
                                <input type="password" id="sq-answer" class="fin-input" placeholder="Your secret answer" minlength="3" required>
                            </div>
                            <div class="pt-4 border-t border-border-subtle">
                                <label for="sq-curr-pw" class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Current Password (to verify) *</label>
                                <input type="password" id="sq-curr-pw" class="fin-input" placeholder="Authenticate changes" required>
                            </div>
                            <div class="flex justify-end pt-4">
                                <button type="submit" id="btn-save-sq" class="btn btn-secondary px-8"><span id="sq-btn-text">Set Security Question</span></button>
                            </div>
                        </form>
                    </div>

                    <!-- ACTIVITY TABLE -->
                    <div class="ds-card overflow-hidden">
                        <div class="p-6 border-b border-border-subtle flex justify-between items-center bg-surface-subtle">
                            <h3 class="font-headline font-bold text-lg flex items-center gap-2 text-text-primary">
                                <span class="material-symbols-outlined text-text-secondary" aria-hidden="true">history</span>
                                Admin Activity Log
                            </h3>
                        </div>
                        <div class="ds-table-wrapper w-full border-0 shadow-none rounded-none bg-transparent">
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
                        <div class="p-4 border-t border-border-subtle flex items-center justify-between bg-surface-subtle hidden" id="activity-pagination-container">
                            <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest" id="activity-pagination-info">Showing 0 to 0 of 0</div>
                            <div class="flex gap-2">
                                <button id="activity-prev-btn" onclick="window.AdminProfile.prevActivityPage()" data-action="window.AdminProfile.prevActivityPage()" class="btn btn-secondary !px-3 !py-1.5 !text-xs">Prev</button>
                                <button id="activity-next-btn" onclick="window.AdminProfile.nextActivityPage()" data-action="window.AdminProfile.nextActivityPage()" class="btn btn-secondary !px-3 !py-1.5 !text-xs">Next</button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- ADMIN MANAGEMENT (Super Admin) -->
                    <div id="admin-management-container" class="ds-card overflow-hidden hidden">
                        <div class="p-6 border-b border-border-subtle flex justify-between items-center bg-warning-bg">
                            <h3 class="font-headline font-bold text-lg flex items-center gap-2 text-warning">
                                <span class="material-symbols-outlined text-warning" aria-hidden="true">manage_accounts</span>
                                Global Admin Directory
                            </h3>
                            <div class="flex gap-3">
                                <span id="management-count-badge" class="px-3 py-1 bg-white/20 text-warning text-[10px] font-black rounded uppercase tracking-widest border border-warning-border">0 Admins</span>
                                <button id="header-add-admin-btn" onclick="window.AdminProfile.showAddModal()" data-action="window.AdminProfile.showAddModal()" class="btn btn-primary !px-4 !py-1 !text-xs">Add Admin</button>
                            </div>
                        </div>
                        <!-- Filters -->
                        <div id="admin-filter-bar" class="p-4 border-b border-border-subtle bg-surface-subtle flex gap-4">
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
                        <div class="ds-table-wrapper w-full border-0 shadow-none rounded-none bg-transparent">
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
                        <div class="p-6 border-b border-border-subtle flex justify-between items-center bg-danger-bg">
                            <h3 class="font-headline font-bold text-lg flex items-center gap-2 text-danger">
                                <span class="material-symbols-outlined text-danger" aria-hidden="true">lock</span>
                                Locked Admin Accounts
                            </h3>
                            <span id="locked-count-badge" class="px-3 py-1 bg-white/20 text-danger text-[10px] font-black rounded uppercase tracking-widest border border-danger-border">0 Locked</span>
                        </div>
                        <div class="ds-table-wrapper w-full border-0 shadow-none rounded-none bg-transparent">
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
                        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-border-subtle">
                            <div class="w-10 h-10 rounded-xl bg-surface-subtle text-text-secondary flex items-center justify-center shadow-sm">
                                <span class="material-symbols-outlined" aria-hidden="true">password</span>
                            </div>
                            <h3 class="font-headline font-bold text-lg text-text-primary">Update Password</h3>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="curr-pw" class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Current Password</label>
                                <div class="fin-input-wrapper">
                                    <span class="material-symbols-outlined input-icon-left">lock</span>
                                    <input type="password" id="curr-pw" class="fin-input has-icon-left" placeholder="Enter current password">
                                </div>
                            </div>
                            <div>
                                <label for="new-pw" class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">New Password</label>
                                <div class="fin-input-wrapper">
                                    <span class="material-symbols-outlined input-icon-left">lock_open</span>
                                    <input type="password" id="new-pw" class="fin-input has-icon-left" placeholder="Min 8 characters" oninput="window.AdminProfile.checkStrength(this.value)">
                                </div>
                                <div class="mt-2 pl-1">
                                    <div class="password-strength-bar w-full h-1 bg-surface-hover rounded overflow-hidden">
                                        <div class="password-strength-fill h-full transition-all duration-300" id="pw-bar" style="width:0%; background:var(--primary-base);"></div>
                                    </div>
                                    <div class="text-[10px] font-black mt-1 text-text-secondary uppercase tracking-widest" id="pw-text"></div>
                                </div>
                            </div>
                            <div>
                                <label for="conf-pw" class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Confirm New Password</label>
                                <div class="fin-input-wrapper">
                                    <span class="material-symbols-outlined input-icon-left">check_circle</span>
                                    <input type="password" id="conf-pw" class="fin-input has-icon-left" placeholder="Confirm new password">
                                </div>
                            </div>
                            <button onclick="window.AdminProfile.updatePassword()" data-action="window.AdminProfile.updatePassword()" class="w-full btn btn-primary mt-4">Change Password</button>
                        </div>
                    </div>

                    <!-- SECURITY STATUS -->
                    <div class="ds-card p-6 sm:p-8 space-y-4">
                        <h3 class="font-headline font-bold text-lg text-text-primary mb-4">Security Audit</h3>
                        
                        <div class="flex justify-between items-center py-2 border-b border-border-subtle">
                            <span class="text-[10px] font-black text-text-secondary uppercase tracking-widest">Last Login</span>
                            <div class="text-right">
                                <div id="sec-last-login" class="text-xs font-bold text-text-primary">—</div>
                            </div>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-border-subtle">
                            <span class="text-[10px] font-black text-text-secondary uppercase tracking-widest">IP Address</span>
                            <span class="text-xs font-mono font-bold text-text-primary" id="sec-last-ip">—</span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-[10px] font-black text-text-secondary uppercase tracking-widest">Pwd Updated</span>
                            <span class="text-xs font-bold text-text-primary" id="sec-last-pwd">—</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ADD ADMIN MODAL (Super Admin) -->
            <div id="add-admin-modal" class="fixed inset-0 z-[2000] hidden items-center justify-center p-4" role="dialog" aria-modal="true">
                <div class="absolute inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-sm transition-all" onclick="window.AdminProfile.closeAddModal()"></div>
                <div class="relative w-full max-w-2xl bg-surface text-text-primary rounded-2xl overflow-hidden shadow-2xl flex flex-col max-h-[90vh] border border-border-subtle animate-modal-enter z-10">
                    <div class="p-6 border-b border-border-subtle flex justify-between items-center sticky top-0 bg-surface z-10 shrink-0">
                        <div>
                            <h3 class="font-black text-xl text-text-primary tracking-tight">Provision New Administrator</h3>
                            <p class="text-xs font-bold uppercase tracking-wider text-text-secondary mt-1">Create an administrative account with RBAC roles</p>
                        </div>
                        <button type="button" onclick="window.AdminProfile.closeAddModal()" class="p-2 hover:bg-surface-hover rounded-full transition-colors text-text-secondary hover:text-text-primary">
                            <span class="material-symbols-outlined" aria-hidden="true">close</span>
                        </button>
                    </div>

                    <div class="p-6 sm:p-8 overflow-y-auto flex-1 space-y-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="add-admin-id" class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Admin ID</label>
                                <input type="text" id="add-admin-id" class="fin-input opacity-70 bg-surface-subtle font-mono" readonly>
                            </div>
                            <div>
                                <label for="add-name" class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Full Name *</label>
                                <input type="text" id="add-name" class="fin-input" placeholder="e.g. John Doe">
                                <span id="err-add-name" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                            </div>
                            <div>
                                <label for="add-dob" class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Date of Birth *</label>
                                <input type="date" id="add-dob" class="fin-input">
                                <span id="err-add-dob" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                            </div>
                            <div>
                                <label for="add-gender" class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Gender *</label>
                                <select id="add-gender" class="fin-input">
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                    <option value="Prefer not to say">Prefer not to say</option>
                                </select>
                                <span id="err-add-gender" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                            </div>
                            <div>
                                <label for="add-phone" class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Phone Number *</label>
                                <input type="text" id="add-phone" class="fin-input" placeholder="10 digit phone">
                                <span id="err-add-phone" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                            </div>
                            <div>
                                <label for="add-email" class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Email Address *</label>
                                <input type="email" id="add-email" class="fin-input" placeholder="admin@finzo.com">
                                <span id="err-add-email" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-border-subtle">
                            <div>
                                <label for="add-pw" class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Initial Password *</label>
                                <input type="password" id="add-pw" class="fin-input" placeholder="Min 8 characters" oninput="window.AdminProfile.checkAddStrength(this.value)">
                                <div class="password-strength-bar w-full h-1 bg-surface-hover rounded overflow-hidden mt-1.5">
                                    <div class="password-strength-fill h-full transition-all duration-300" id="add-pw-bar" style="width:0%; background:var(--primary-base);"></div>
                                </div>
                                <span id="err-add-pw" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                            </div>
                            <div>
                                <label for="add-conf-pw" class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Confirm Password *</label>
                                <input type="password" id="add-conf-pw" class="fin-input" placeholder="Confirm password">
                                <span id="err-add-conf-pw" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-border-subtle">
                            <div>
                                <label for="add-seq-q" class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Security Question *</label>
                                <input type="text" id="add-seq-q" class="fin-input" placeholder="e.g. What was the name of your first school?">
                                <span id="err-add-seq-q" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                            </div>
                            <div>
                                <label for="add-seq-a" class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Security Answer *</label>
                                <input type="password" id="add-seq-a" class="fin-input" placeholder="Secret recovery answer">
                                <span id="err-add-seq-a" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-border-subtle space-y-4">
                            <div>
                                <label for="add-role" class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Role Assignment *</label>
                                <select id="add-role" class="fin-input" onchange="window.AdminProfile.updateRolePreview(this.value)">
                                    <option value="">Select Role</option>
                                    <option value="admin">Administrator (Full operational access)</option>
                                    <option value="moderator">Moderator (KYC & Pending approvals only)</option>
                                </select>
                                <span id="err-add-role" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                            </div>
                            <div id="role-preview-box" class="p-4 rounded-xl bg-surface-subtle border border-border-subtle text-xs text-text-secondary hidden"></div>
                            <div>
                                <label for="new-admin-photo" class="fin-label mb-1 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Profile Photo (Optional)</label>
                                <input type="file" id="new-admin-photo" accept="image/jpeg, image/png" class="fin-input" onchange="window.AdminProfile.previewNewPhoto(this)">
                                <div class="mt-2 flex items-center gap-3">
                                    <img id="new-avatar-preview" src="" class="w-12 h-12 rounded-xl object-cover hidden border border-border-subtle" alt="Preview">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 border-t border-border-subtle bg-surface-subtle flex justify-end gap-3 sticky bottom-0 z-10 shrink-0">
                        <button type="button" onclick="window.AdminProfile.closeAddModal()" class="btn btn-secondary px-6">Cancel</button>
                        <button type="button" id="add-admin-submit-btn" onclick="window.AdminProfile.submitAddAdmin()" class="btn btn-primary px-8 flex items-center gap-2">
                            <span>Create Admin</span>
                            <span class="material-symbols-outlined animate-spin text-[16px] hidden" aria-hidden="true">progress_activity</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- UNLOCK CONFIRM OVERLAY -->
            <div id="unlock-confirm-overlay" class="fixed inset-0 z-[2100] hidden items-center justify-center p-4" role="dialog" aria-modal="true">
                <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="window.AdminProfile.closeUnlockConfirm()"></div>
                <div class="relative w-full max-w-md bg-surface text-text-primary rounded-2xl p-6 shadow-2xl border border-border-subtle z-10 space-y-4">
                    <div class="flex items-center gap-3 text-warning">
                        <span class="material-symbols-outlined text-2xl" aria-hidden="true">lock_open</span>
                        <h4 class="font-bold text-lg text-text-primary">Unlock Admin Account</h4>
                    </div>
                    <p id="unlock-confirm-msg" class="text-sm text-text-secondary">Are you sure you want to unlock this administrator account?</p>
                    <div class="flex justify-end gap-3 pt-4 border-t border-border-subtle">
                        <button type="button" onclick="window.AdminProfile.closeUnlockConfirm()" class="btn btn-secondary px-5">Cancel</button>
                        <button type="button" id="confirm-unlock-btn" onclick="window.AdminProfile.confirmUnlock()" class="btn btn-primary px-6">Confirm Unlock</button>
                    </div>
                </div>
            </div>
<?php
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/profile.js"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>