<?php
$portal = 'staff';
$pageTitle = 'Finzo Staff — My Profile';
ob_start();
?>
<div class="space-y-6">
    <!-- PROFILE HEADER / HERO DOSSIER -->
    <div class="ds-card p-6 sm:p-8 flex flex-col sm:flex-row items-center sm:items-start gap-6 sm:gap-8 bg-white dark:bg-[#181513] border border-border-subtle dark:border-white/10 border-t-4 border-t-amber-500 shadow-sm relative overflow-visible">
        <!-- Avatar Column -->
        <div class="relative shrink-0">
            <div id="profile-avatar" class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl bg-amber-500/10 dark:bg-amber-500/15 border-2 border-amber-500/30 flex items-center justify-center text-2xl font-black text-amber-600 dark:text-amber-400 overflow-hidden shadow-md relative">
                <span id="profile-avatar-text" class="uppercase select-none font-mono">--</span>
            </div>
            <label for="photo-upload" class="absolute -bottom-2 -right-2 w-9 h-9 rounded-xl bg-amber-500 hover:bg-amber-600 active:scale-95 text-white shadow-lg shadow-amber-500/30 flex items-center justify-center cursor-pointer transition-all border-2 border-white dark:border-[#181513]" title="Update Profile Photo">
                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">photo_camera</span>
            </label>
            <input type="file" id="photo-upload" accept="image/jpeg, image/png, image/webp" class="hidden">
        </div>

        <!-- Identity & Badges -->
        <div class="text-center sm:text-left flex-1 min-w-0">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black font-headline text-slate-900 dark:text-white tracking-tight" id="profile-name">Loading...</h1>
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2.5 mt-2">
                        <span class="px-3 py-1 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 text-xs font-black uppercase tracking-wider border border-amber-500/20" id="profile-designation">—</span>
                        <span class="px-3 py-1 rounded-lg bg-slate-100 dark:bg-white/5 text-slate-700 dark:text-slate-300 text-xs font-black uppercase tracking-wider border border-slate-200 dark:border-white/10" id="profile-department">—</span>
                        <span class="px-3 py-1 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-xs font-black uppercase tracking-wider border border-emerald-500/20 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Operational Teller
                        </span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-6 mt-5 pt-4 border-t border-slate-100 dark:border-white/5 text-xs font-bold text-slate-500 dark:text-slate-400">
                <div class="flex items-center gap-2 justify-center sm:justify-start">
                    <span class="material-symbols-outlined text-lg text-amber-500" aria-hidden="true">badge</span>
                    <span>Staff ID: <strong id="profile-staff-id" class="text-slate-900 dark:text-white font-mono">—</strong></span>
                </div>
                <div class="flex items-center gap-2 justify-center sm:justify-start">
                    <span class="material-symbols-outlined text-lg text-amber-500" aria-hidden="true">apartment</span>
                    <span id="profile-branch" class="text-slate-900 dark:text-white">—</span>
                </div>
                <div class="flex items-center gap-2 justify-center sm:justify-start">
                    <span class="material-symbols-outlined text-lg text-amber-500" aria-hidden="true">calendar_today</span>
                    <span>Joined <strong id="profile-joined" class="text-slate-900 dark:text-white font-mono">—</strong></span>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN TWO-COLUMN CONTENT GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- LEFT COLUMN (2 COLS): PERSONAL, EMERGENCY & ADDRESS -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- PERSONAL DETAILS -->
            <div class="ds-card bg-white dark:bg-[#181513] border border-border-subtle dark:border-white/10 shadow-xs">
                <div class="p-5 sm:p-6 border-b border-slate-100 dark:border-white/5 flex items-center justify-between">
                    <h3 class="font-headline font-bold text-lg text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-amber-500" aria-hidden="true">person</span>
                        Personal Information
                    </h3>
                    <button type="button" onclick="openEditModal('phone')" class="px-3 py-1.5 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 text-amber-600 dark:text-amber-400 text-xs font-bold uppercase tracking-wider border border-amber-500/20 transition-all flex items-center gap-1.5 cursor-pointer">
                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">edit</span>
                        <span>Edit Phone</span>
                    </button>
                </div>
                <div class="p-5 sm:p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-6">
                        <div>
                            <div class="text-[11px] font-black text-slate-400 dark:text-slate-400 uppercase tracking-widest mb-1">Full Legal Name</div>
                            <div class="text-sm font-bold text-slate-900 dark:text-white" id="info-name">—</div>
                        </div>
                        <div>
                            <div class="text-[11px] font-black text-slate-400 dark:text-slate-400 uppercase tracking-widest mb-1">Date of Birth</div>
                            <div class="text-sm font-bold text-slate-900 dark:text-white font-mono" id="info-dob">—</div>
                        </div>
                        <div>
                            <div class="text-[11px] font-black text-slate-400 dark:text-slate-400 uppercase tracking-widest mb-1">Gender</div>
                            <div class="text-sm font-bold text-slate-900 dark:text-white capitalize" id="info-gender">—</div>
                        </div>
                        <div>
                            <div class="text-[11px] font-black text-slate-400 dark:text-slate-400 uppercase tracking-widest mb-1">Mobile Phone Number</div>
                            <div class="text-sm font-mono font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <span id="info-phone">—</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">Primary</span>
                            </div>
                        </div>
                        <div class="sm:col-span-2">
                            <div class="text-[11px] font-black text-slate-400 dark:text-slate-400 uppercase tracking-widest mb-1">Personal Email Address</div>
                            <div class="text-sm font-bold text-slate-900 dark:text-white" id="info-email">—</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- EMERGENCY CONTACT -->
            <div class="ds-card bg-white dark:bg-[#181513] border border-border-subtle dark:border-white/10 shadow-xs">
                <div class="p-5 sm:p-6 border-b border-slate-100 dark:border-white/5 flex items-center justify-between">
                    <h3 class="font-headline font-bold text-lg text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-rose-500" aria-hidden="true">emergency</span>
                        Emergency Contact
                    </h3>
                    <button type="button" onclick="openEditModal('emergency')" class="px-3 py-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 text-xs font-bold uppercase tracking-wider border border-rose-500/20 transition-all flex items-center gap-1.5 cursor-pointer">
                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">edit</span>
                        <span>Edit Contact</span>
                    </button>
                </div>
                <div class="p-5 sm:p-6 bg-rose-500/5 dark:bg-rose-500/[0.03]">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="flex items-center gap-3.5 bg-white dark:bg-white/5 p-4 rounded-xl border border-slate-200/80 dark:border-white/10 shadow-2xs">
                            <div class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-xl" aria-hidden="true">person</span>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[10px] font-black text-slate-400 dark:text-slate-400 uppercase tracking-widest">Contact Name</div>
                                <div class="text-sm font-bold text-slate-900 dark:text-white mt-0.5 truncate" id="info-emergency-name">—</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3.5 bg-white dark:bg-white/5 p-4 rounded-xl border border-slate-200/80 dark:border-white/10 shadow-2xs">
                            <div class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-xl" aria-hidden="true">call</span>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[10px] font-black text-slate-400 dark:text-slate-400 uppercase tracking-widest">Contact Phone</div>
                                <div class="text-sm font-mono font-bold text-slate-900 dark:text-white mt-0.5 truncate" id="info-emergency-phone">—</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RESIDENTIAL ADDRESS -->
            <div class="ds-card bg-white dark:bg-[#181513] border border-border-subtle dark:border-white/10 shadow-xs">
                <div class="p-5 sm:p-6 border-b border-slate-100 dark:border-white/5 flex items-center justify-between">
                    <h3 class="font-headline font-bold text-lg text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-500" aria-hidden="true">location_on</span>
                        Residential Address
                    </h3>
                    <button type="button" onclick="openEditModal('address')" class="px-3 py-1.5 rounded-xl bg-blue-500/10 hover:bg-blue-500/20 text-blue-600 dark:text-blue-400 text-xs font-bold uppercase tracking-wider border border-blue-500/20 transition-all flex items-center gap-1.5 cursor-pointer">
                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">edit</span>
                        <span>Edit Address</span>
                    </button>
                </div>
                <div class="p-5 sm:p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="sm:col-span-2 lg:col-span-4">
                            <div class="text-[11px] font-black text-slate-400 dark:text-slate-400 uppercase tracking-widest mb-1">Street Address</div>
                            <div class="text-sm font-semibold text-slate-900 dark:text-white" id="info-street">—</div>
                        </div>
                        <div>
                            <div class="text-[11px] font-black text-slate-400 dark:text-slate-400 uppercase tracking-widest mb-1">City</div>
                            <div class="text-sm font-bold text-slate-900 dark:text-white" id="info-city">—</div>
                        </div>
                        <div>
                            <div class="text-[11px] font-black text-slate-400 dark:text-slate-400 uppercase tracking-widest mb-1">State</div>
                            <div class="text-sm font-bold text-slate-900 dark:text-white" id="info-state">—</div>
                        </div>
                        <div>
                            <div class="text-[11px] font-black text-slate-400 dark:text-slate-400 uppercase tracking-widest mb-1">Postal Code (PIN)</div>
                            <div class="text-sm font-mono font-bold text-slate-900 dark:text-white" id="info-pincode">—</div>
                        </div>
                        <div>
                            <div class="text-[11px] font-black text-slate-400 dark:text-slate-400 uppercase tracking-widest mb-1">Country</div>
                            <div class="text-sm font-bold text-slate-900 dark:text-white" id="info-country">—</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN (1 COL): SECURITY, ID DOCUMENTS & SESSION -->
        <div class="space-y-6">
            
            <!-- SECURITY & PASSWORD -->
            <div class="ds-card bg-white dark:bg-[#181513] border border-border-subtle dark:border-white/10 p-5 sm:p-6 shadow-xs">
                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100 dark:border-white/5">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center border border-amber-500/20">
                        <span class="material-symbols-outlined text-xl" aria-hidden="true">lock_reset</span>
                    </div>
                    <div>
                        <h3 class="font-headline font-bold text-lg text-slate-900 dark:text-white">Security &amp; Password</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Keep your teller terminal account secure</p>
                    </div>
                </div>
                
                <form id="password-form" class="space-y-4">
                    <div>
                        <label for="pw-current" class="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1.5">Current Password *</label>
                        <div class="fin-input-wrapper relative">
                            <span class="material-symbols-outlined input-icon-left text-slate-400">lock</span>
                            <input type="password" id="pw-current" required class="w-full fin-input has-icon-left text-sm bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20" placeholder="Enter current password">
                        </div>
                    </div>
                    <div>
                        <label for="pw-new" class="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1.5">New Password *</label>
                        <div class="fin-input-wrapper relative">
                            <span class="material-symbols-outlined input-icon-left text-slate-400">lock_open</span>
                            <input type="password" id="pw-new" required minlength="8" class="w-full fin-input has-icon-left text-sm bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20" placeholder="Min 8 characters" oninput="checkPasswordStrength()">
                        </div>
                        <div class="mt-2.5">
                            <div class="password-strength-bar w-full h-1.5 bg-slate-200 dark:bg-white/10 rounded-full overflow-hidden">
                                <div class="password-strength-fill h-full transition-all duration-300 rounded-full" id="pw-strength-bar" style="width:0%"></div>
                            </div>
                            <div class="text-[11px] font-bold mt-1 text-slate-500 dark:text-slate-400 uppercase tracking-wider" id="pw-strength-text"></div>
                        </div>
                    </div>
                    <div>
                        <label for="pw-confirm" class="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1.5">Confirm New Password *</label>
                        <div class="fin-input-wrapper relative">
                            <span class="material-symbols-outlined input-icon-left text-slate-400">check_circle</span>
                            <input type="password" id="pw-confirm" required class="w-full fin-input has-icon-left text-sm bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20" placeholder="Repeat new password">
                        </div>
                    </div>
                    <div id="pw-error" class="hidden text-xs font-bold text-rose-600 dark:text-rose-400 bg-rose-500/10 p-3 rounded-xl border border-rose-500/20" aria-live="polite"></div>
                    <button type="submit" id="pw-submit-btn" class="w-full h-11 bg-amber-500 hover:bg-amber-600 active:scale-[0.99] text-white font-bold rounded-xl shadow-md shadow-amber-500/20 transition-all text-xs uppercase tracking-wider mt-2 flex items-center justify-center gap-2 cursor-pointer">
                        <span class="material-symbols-outlined text-base">vpn_key</span>
                        <span>Update Password</span>
                    </button>
                </form>
            </div>

            <!-- ID DOCUMENTS -->
            <div class="ds-card bg-white dark:bg-[#181513] border border-border-subtle dark:border-white/10 shadow-xs">
                <div class="p-5 sm:p-6 border-b border-slate-100 dark:border-white/5 flex items-center gap-2">
                    <span class="material-symbols-outlined text-purple-500" aria-hidden="true">verified_user</span>
                    <h3 class="font-headline font-bold text-lg text-slate-900 dark:text-white">Identity Documents</h3>
                </div>
                <div class="p-5 sm:p-6 bg-slate-50/50 dark:bg-white/[0.02]" id="documents-container">
                    <div class="text-center py-6 text-slate-400 font-bold text-xs uppercase tracking-widest">Loading documents...</div>
                </div>
            </div>

            <!-- SESSION & ACCOUNT STATUS -->
            <div class="ds-card bg-white dark:bg-[#181513] border border-border-subtle dark:border-white/10 p-5 sm:p-6 space-y-4 shadow-xs">
                <h3 class="font-headline font-bold text-lg text-slate-900 dark:text-white mb-2">Account Status</h3>
                
                <div class="flex justify-between items-center py-2.5 border-b border-slate-100 dark:border-white/5">
                    <span class="text-xs font-black text-slate-400 dark:text-slate-400 uppercase tracking-widest">Portal Access</span>
                    <div id="info-status">—</div>
                </div>
                <div class="flex justify-between items-center py-2.5 border-b border-slate-100 dark:border-white/5">
                    <span class="text-xs font-black text-slate-400 dark:text-slate-400 uppercase tracking-widest">Last Session</span>
                    <span class="text-xs font-bold text-slate-900 dark:text-white text-right font-mono" id="info-last-login">—</span>
                </div>
                <div class="flex justify-between items-center py-2.5">
                    <span class="text-xs font-black text-slate-400 dark:text-slate-400 uppercase tracking-widest">Terminal IP</span>
                    <span class="text-xs font-mono font-bold text-slate-900 dark:text-white" id="info-last-ip">—</span>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- EDIT DETAILS MODAL -->
<div id="edit-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/70 backdrop-blur-sm p-4 transition-all">
    <div class="bg-white dark:bg-[#1c1917] border border-slate-200 dark:border-white/10 rounded-2xl w-full max-w-md shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <div class="p-5 border-b border-slate-100 dark:border-white/5 flex items-center justify-between">
            <h3 class="font-headline font-bold text-lg text-slate-900 dark:text-white flex items-center gap-2" id="edit-modal-title">
                Edit Profile
            </h3>
            <button type="button" onclick="closeEditModal()" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/10 transition-colors cursor-pointer" aria-label="Close modal">
                <span class="material-symbols-outlined text-xl leading-none">close</span>
            </button>
        </div>
        <form id="edit-form" class="p-6 space-y-4">
            <div id="edit-fields" class="space-y-4"></div>
            <div id="edit-error" class="hidden text-xs font-bold text-rose-600 dark:text-rose-400 bg-rose-500/10 p-3 rounded-xl border border-rose-500/20" aria-live="polite"></div>
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-white/5">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-white/10 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5 text-xs font-bold uppercase tracking-wider transition-colors cursor-pointer">
                    Cancel
                </button>
                <button type="submit" id="edit-submit-btn" class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-black uppercase tracking-wider shadow-md shadow-amber-500/20 transition-all flex items-center gap-1.5 cursor-pointer">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$slot = ob_get_clean();
$extraScripts = '<script defer src="/Finzo/public/assets/js/staff/staff-profile.js?v=' . time() . '"></script>';
include __DIR__ . '/../layouts/app.php';
?>
