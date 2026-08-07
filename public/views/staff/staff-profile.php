<?php
$portal = 'staff';
$pageTitle = 'Finzo Staff';
ob_start();
?>
<!-- PROFILE HEADER -->
            <div class="ds-card p-6 sm:p-10 flex flex-col sm:flex-row items-center sm:items-start gap-8 bg-gradient-to-br from-white to-slate-50 dark:from-surface-main dark:to-surface-subtle border-t-4 border-t-amber-500">
                <div class="profile-avatar-wrapper flex-shrink-0">
                    <div class="profile-avatar" id="profile-avatar">
                        <span id="profile-avatar-text">--</span>
                    </div>
                    <label for="photo-upload" class="avatar-upload-btn">
                        <span class="material-symbols-outlined text-base" aria-hidden="true">add_a_photo</span>
                    </label>
                    <input type="file" id="photo-upload" accept="image/*" class="hidden">
                </div>
                <div class="text-center sm:text-left flex-1">
                    <h1 class="text-2xl sm:text-3xl font-black font-headline text-text-main dark:text-white tracking-tight mb-2" id="profile-name">Loading...</h1>
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-3 mb-4">
                        <span class="px-3 py-1 rounded bg-warning-surface dark:bg-warning-surface/10 text-warning-base dark:text-warning-base text-xs font-black uppercase tracking-widest border border-warning-base dark:border-warning-base/20" id="profile-designation">—</span>
                        <span class="px-3 py-1 rounded bg-surface-hover dark:bg-white/5 text-text-muted dark:text-text-muted text-xs font-black uppercase tracking-widest border border-surface-border dark:border-white/10" id="profile-department">—</span>
                    </div>
                    <div class="flex flex-wrap justify-center sm:justify-start gap-6 text-xs font-bold text-text-muted">
                        <div class="flex items-center gap-2"><span class="material-symbols-outlined text-base text-text-muted" aria-hidden="true">badge</span> ID: <span id="profile-staff-id" class="text-text-main dark:text-white font-mono">—</span></div>
                        <div class="flex items-center gap-2"><span class="material-symbols-outlined text-base text-text-muted" aria-hidden="true">apartment</span> <span id="profile-branch" class="text-text-main dark:text-white">—</span></div>
                        <div class="flex items-center gap-2"><span class="material-symbols-outlined text-base text-text-muted" aria-hidden="true">calendar_today</span> Joined <span id="profile-joined" class="text-text-main dark:text-white">—</span></div>
                    </div>
                </div>
                <!-- Action -> might add quick edit button if needed -->
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- LEFT COLUMN -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- PERSONAL INFO -->
                    <div class="ds-card">
                        <div class="p-6 border-b border-surface-border dark:border-white/5 flex items-center justify-between">
                            <h3 class="font-headline font-bold text-lg text-text-main dark:text-white flex items-center gap-2">
                                <span class="material-symbols-outlined text-warning-base" aria-hidden="true">person</span>
                                Personal Details
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-6">
                                <div>
                                    <div class="text-xs font-black text-text-muted uppercase tracking-widest mb-1">Full Name</div>
                                    <div class="text-sm font-bold text-text-main dark:text-white" id="info-name">—</div>
                                </div>
                                <div>
                                    <div class="text-xs font-black text-text-muted uppercase tracking-widest mb-1">Date of Birth</div>
                                    <div class="text-sm font-bold text-text-main dark:text-white" id="info-dob">—</div>
                                </div>
                                <div>
                                    <div class="text-xs font-black text-text-muted uppercase tracking-widest mb-1">Gender</div>
                                    <div class="text-sm font-bold text-text-main dark:text-white" id="info-gender">—</div>
                                </div>
                                <div class="relative group">
                                    <div class="text-xs font-black text-text-muted uppercase tracking-widest mb-1">Phone Number</div>
                                    <div class="text-sm font-mono font-bold text-text-main dark:text-white" id="info-phone">—</div>
                                    <button data-action="openEditModal(&#039;phone&#039;)" class="absolute right-0 top-1/2 -translate-y-1/2 p-1.5 rounded-lg text-warning-base hover:bg-warning-surface dark:hover:bg-warning-surface/10 opacity-0 group-hover:opacity-100 transition-all">
                                        <span class="material-symbols-outlined text-base" aria-hidden="true">edit</span>
                                    </button>
                                </div>
                                <div class="sm:col-span-2">
                                    <div class="text-xs font-black text-text-muted uppercase tracking-widest mb-1">Personal Email</div>
                                    <div class="text-sm font-bold text-text-main dark:text-white" id="info-email">—</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- EMERGENCY CONTACT -->
                    <div class="ds-card">
                        <div class="p-6 border-b border-surface-border dark:border-white/5 flex items-center justify-between">
                            <h3 class="font-headline font-bold text-lg text-text-main dark:text-white flex items-center gap-2">
                                <span class="material-symbols-outlined text-danger-base" aria-hidden="true">emergency</span>
                                Emergency Contact
                            </h3>
                            <button data-action="openEditModal(&#039;emergency&#039;)" class="px-3 py-1.5 rounded-lg bg-danger-surface dark:bg-danger-surface/10 text-danger-base dark:text-danger-base text-xs font-black uppercase tracking-widest border border-danger-base dark:border-danger-base/20 hover:bg-danger-surface transition-all flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm" aria-hidden="true">edit</span> Edit
                            </button>
                        </div>
                        <div class="p-6 bg-danger-surface/30 dark:bg-danger-surface/5">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div class="flex items-center gap-4 bg-white dark:bg-surface-subtle dark:bg-surface-main p-4 rounded-xl border border-danger-base dark:border-danger-base/20">
                                    <div class="w-10 h-10 rounded-full bg-danger-surface dark:bg-danger-surface/20 text-danger-base flex items-center justify-center">
                                        <span class="material-symbols-outlined" aria-hidden="true">person</span>
                                    </div>
                                    <div>
                                        <div class="text-xs font-black text-text-muted uppercase tracking-widest">Contact Name</div>
                                        <div class="text-sm font-bold text-text-main dark:text-white mt-1" id="info-emergency-name">—</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4 bg-white dark:bg-surface-subtle dark:bg-surface-main p-4 rounded-xl border border-danger-base dark:border-danger-base/20">
                                    <div class="w-10 h-10 rounded-full bg-danger-surface dark:bg-danger-surface/20 text-danger-base flex items-center justify-center">
                                        <span class="material-symbols-outlined" aria-hidden="true">call</span>
                                    </div>
                                    <div>
                                        <div class="text-xs font-black text-text-muted uppercase tracking-widest">Contact Phone</div>
                                        <div class="text-sm font-mono font-bold text-text-main dark:text-white mt-1" id="info-emergency-phone">—</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ADDRESS -->
                    <div class="ds-card">
                        <div class="p-6 border-b border-surface-border dark:border-white/5 flex items-center justify-between">
                            <h3 class="font-headline font-bold text-lg text-text-main dark:text-white flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary-base" aria-hidden="true">location_on</span>
                                Address Information
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                                <div class="sm:col-span-2 lg:col-span-4">
                                    <div class="text-xs font-black text-text-muted uppercase tracking-widest mb-1">Street Address</div>
                                    <div class="text-sm font-medium text-text-main dark:text-white" id="info-street">—</div>
                                </div>
                                <div>
                                    <div class="text-xs font-black text-text-muted uppercase tracking-widest mb-1">City</div>
                                    <div class="text-sm font-bold text-text-main dark:text-white" id="info-city">—</div>
                                </div>
                                <div>
                                    <div class="text-xs font-black text-text-muted uppercase tracking-widest mb-1">State</div>
                                    <div class="text-sm font-bold text-text-main dark:text-white" id="info-state">—</div>
                                </div>
                                <div>
                                    <div class="text-xs font-black text-text-muted uppercase tracking-widest mb-1">Pincode</div>
                                    <div class="text-sm font-mono font-bold text-text-main dark:text-white" id="info-pincode">—</div>
                                </div>
                                <div>
                                    <div class="text-xs font-black text-text-muted uppercase tracking-widest mb-1">Country</div>
                                    <div class="text-sm font-bold text-text-main dark:text-white" id="info-country">—</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN -->
                <div class="space-y-6">
                    <!-- SECURITY -->
                    <div class="ds-card p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-xl bg-warning-surface dark:bg-warning-surface/10 text-warning-base dark:text-warning-base flex items-center justify-center border border-warning-base dark:border-warning-base/20">
                                <span class="material-symbols-outlined" aria-hidden="true">security</span>
                            </div>
                            <h3 class="font-headline font-bold text-lg text-text-main dark:text-white">Security</h3>
                        </div>
                        
                        <form id="password-form" class="space-y-4">
                            <div>
                                <label class="block text-xs font-black text-text-muted uppercase tracking-widest mb-1">Current Password *</label>
                                <input type="password" id="pw-current" required class="w-full fin-input">
                            </div>
                            <div>
                                <label class="block text-xs font-black text-text-muted uppercase tracking-widest mb-1">New Password *</label>
                                <input type="password" id="pw-new" required minlength="8" class="w-full fin-input" oninput="checkPasswordStrength()">
                                <div class="mt-2 pl-1">
                                    <div class="password-strength-bar">
                                        <div class="password-strength-fill" id="pw-strength-bar" style="width:0%"></div>
                                    </div>
                                    <div class="text-xs font-bold mt-1 text-text-muted uppercase tracking-widest" id="pw-strength-text"></div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-black text-text-muted uppercase tracking-widest mb-1">Confirm Password *</label>
                                <input type="password" id="pw-confirm" required class="w-full fin-input">
                            </div>
                            <div id="pw-error" class="hidden text-xs font-bold text-danger-base bg-danger-surface dark:bg-danger-surface/10 p-2 rounded" aria-live="polite"></div>
                            <button type="submit" id="pw-submit-btn" class="w-full h-10 bg-warning-surface text-white font-bold rounded-lg shadow-md shadow-amber-500/20 hover:bg-warning-surface transition-all text-xs uppercase tracking-widest mt-2">
                                Update Password
                            </button>
                        </form>
                    </div>

                    <!-- ID DOCUMENTS -->
                    <div class="ds-card">
                        <div class="p-6 border-b border-surface-border dark:border-white/5 flex items-center gap-2">
                            <span class="material-symbols-outlined text-purple-500" aria-hidden="true">verified_user</span>
                            <h3 class="font-headline font-bold text-lg text-text-main dark:text-white">ID Documents</h3>
                        </div>
                        <div class="p-6 bg-surface-subtle/50 dark:bg-white/2" id="documents-container">
                            <div class="text-center py-6 text-text-muted font-bold text-xs uppercase tracking-widest">Loading...</div>
                        </div>
                    </div>

                    <!-- SESSION INFO -->
                    <div class="ds-card p-6 space-y-4">
                        <h3 class="font-headline font-bold text-lg text-text-main dark:text-white mb-4">Account Status</h3>
                        
                        <div class="flex justify-between items-center py-2 border-b border-surface-border dark:border-white/5">
                            <span class="text-xs font-black text-text-muted uppercase tracking-widest">Status</span>
                            <div id="info-status">—</div>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-surface-border dark:border-white/5">
                            <span class="text-xs font-black text-text-muted uppercase tracking-widest">Last Login</span>
                            <span class="text-xs font-bold text-text-main dark:text-white text-right" id="info-last-login">—</span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-xs font-black text-text-muted uppercase tracking-widest">IP Address</span>
                            <span class="text-xs font-mono font-bold text-text-main dark:text-white" id="info-last-ip">—</span>
                        </div>
                    </div>
                </div>

            </div>
<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>