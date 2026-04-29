/**
 * Finzo Admin — Profile & RBAC Management Handler
 * Manages administrative account settings, avatar, security, and super_admin privileges.
 */

(function () {
    let currentAdminRole = 'admin';

    // Pagination State for Activity
    let activityData = [];
    let currentActivityPage = 1;
    const ACTIVITY_PER_PAGE = 10;

    // Helper: Formats relative time
    const timeAgo = (dateStr) => {
        if (!dateStr) return 'Never';
        const ts = new Date(dateStr).getTime();
        const seconds = Math.floor((new Date().getTime() - ts) / 1000);

        let interval = seconds / 31536000;
        if (interval > 1) return Math.floor(interval) + " years ago";
        interval = seconds / 2592000;
        if (interval > 1) return Math.floor(interval) + " months ago";
        interval = seconds / 86400;
        if (interval > 1) {
            const days = Math.floor(interval);
            return days === 1 ? "Yesterday" : days + " days ago";
        }
        interval = seconds / 3600;
        if (interval > 1) return Math.floor(interval) + " hours ago";
        interval = seconds / 60;
        if (interval > 1) return Math.floor(interval) + " minutes ago";
        return Math.floor(seconds) + " seconds ago";
    };

    // Helper: Formats absolute date and time
    const formatDateTime = (dateStr) => {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) + ', ' +
            d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    };

    function renderActivityPage() {
        const tbody = document.getElementById('activity-table-body');
        const info = document.getElementById('activity-pagination-info');
        const prevBtn = document.getElementById('activity-prev-btn');
        const nextBtn = document.getElementById('activity-next-btn');

        if (activityData.length === 0) {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center italic opacity-40 py-8">No recent activity found.</td></tr>`;
            document.getElementById('activity-pagination-container').classList.add('hidden');
            return;
        }

        document.getElementById('activity-pagination-container').classList.remove('hidden');

        const totalPages = Math.ceil(activityData.length / ACTIVITY_PER_PAGE);
        if (currentActivityPage > totalPages) currentActivityPage = totalPages;
        if (currentActivityPage < 1) currentActivityPage = 1;

        const startIdx = (currentActivityPage - 1) * ACTIVITY_PER_PAGE;
        const endIdx = Math.min(startIdx + ACTIVITY_PER_PAGE, activityData.length);

        const pageData = activityData.slice(startIdx, endIdx);

        tbody.innerHTML = pageData.map(a => {
            // Strip redundant IP from details
            let cleanDetails = a.details ? a.details.replace(/\sfrom IP:\s.*$/, '') : '-';

            // Professional overrides
            switch (a.action_type) {
                case 'admin_login':
                    cleanDetails = 'Successfully authenticated into the administrative console.';
                    break;
                case 'admin_profile_updated':
                    cleanDetails = 'Account preferences and profile parameters were modified.';
                    break;
                case 'admin_password_updated':
                    cleanDetails = 'Security credentials (password) were successfully reset.';
                    break;
                case 'admin_added':
                    cleanDetails = 'A new administrative account was provisioned in the system.';
                    break;
                case 'admin_deleted':
                    cleanDetails = 'An administrative account was permanently revoked and deleted.';
                    break;
                case 'admin_suspended':
                    cleanDetails = 'Administrative system access was temporarily suspended.';
                    break;
                case 'admin_activated':
                    cleanDetails = 'Administrative system access was restored and activated.';
                    break;
            }

            // Determine badge style
            let badgeClass = 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700';
            if (a.action_type.includes('login')) badgeClass = 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400 border border-blue-100 dark:border-blue-500/20';
            else if (a.action_type.includes('update')) badgeClass = 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-500/20';
            else if (a.action_type.includes('delete') || a.action_type.includes('suspend')) badgeClass = 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400 border border-red-100 dark:border-red-500/20';
            else if (a.action_type.includes('add') || a.action_type.includes('activate')) badgeClass = 'bg-purple-50 text-purple-600 dark:bg-purple-500/10 dark:text-purple-400 border border-purple-100 dark:border-purple-500/20';

            return `
            <tr class="hover:bg-slate-50 dark:hover:bg-white/5 transition-colors border-b border-slate-50 dark:border-white/5 last:border-0">
                <td class="py-4"><span class="px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-widest ${badgeClass}">${a.action_type.replace(/_/g, ' ')}</span></td>
                <td class="py-4 text-[13px] font-medium text-slate-600 dark:text-slate-300">${cleanDetails}</td>
                <td class="py-4 text-xs font-mono text-slate-500 dark:text-slate-400">${a.ip_address}</td>
                <td class="py-4 text-[11px] font-bold text-slate-500 dark:text-slate-400 whitespace-nowrap uppercase tracking-wide">${formatDateTime(a.created_at)}</td>
            </tr>
        `}).join('');

        info.textContent = `Showing ${startIdx + 1} to ${endIdx} of ${activityData.length} entries`;
        prevBtn.disabled = currentActivityPage === 1;
        nextBtn.disabled = currentActivityPage === totalPages;
    }

    /**
     * Load Admin profile data from API
     */
    async function loadAdminProfile() {
        try {
            const response = await window.api.get('/admin/get-admin-profile.php');
            if (response.success) {
                const admin = response.profile;
                currentAdminRole = admin.admin_role;

                // Topbar & Subtitle handling
                if (!admin.profile_photo) {
                    document.getElementById('topbar-avatar').textContent = admin.full_name.charAt(0);
                }

                // Profile Card bindings
                document.getElementById('profile-name-display').textContent = admin.full_name;
                document.getElementById('profile-admin-id-input').value = admin.admin_id || '---';
                document.getElementById('profile-name-input').value = admin.full_name;
                document.getElementById('profile-dob-input').value = admin.date_of_birth || '';

                // Gender Dropdown sync
                const genderVal = admin.gender || '';
                const genderInput = document.getElementById('profile-gender-input');
                genderInput.value = genderVal;
                const genderDropdown = genderInput.closest('.finzo-dropdown');
                if (genderDropdown && genderVal) {
                    const label = genderDropdown.querySelector('.dropdown-label');
                    if (label) label.textContent = genderVal;
                }
                document.getElementById('profile-phone-input').value = admin.phone || '';
                document.getElementById('profile-email-input').value = admin.email;

                const qField = document.getElementById('profile-seq-q-input');
                if (qField) qField.value = admin.security_question || '';

                // Badges
                const baseBadgeClass = 'px-3.5 py-1.5 text-[10px] font-black rounded-xl uppercase tracking-widest transition-colors';

                const roleBadge = document.getElementById('badge-role');
                if (admin.admin_role === 'super_admin') {
                    roleBadge.textContent = 'Super Admin';
                    roleBadge.className = `${baseBadgeClass} bg-[#002645] text-white shadow-sm`;
                } else if (admin.admin_role === 'admin') {
                    roleBadge.textContent = 'Admin';
                    roleBadge.className = `${baseBadgeClass} bg-teal-50 text-teal-600 dark:bg-teal-500/10 dark:text-teal-400`;
                } else {
                    roleBadge.textContent = 'Moderator';
                    roleBadge.className = `${baseBadgeClass} bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400`;
                }

                const accessBadge = document.getElementById('badge-access');
                accessBadge.textContent = admin.access_level;
                accessBadge.className = admin.access_level === 'full'
                    ? `${baseBadgeClass} bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400`
                    : `${baseBadgeClass} bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400`;

                const statusBadge = document.getElementById('badge-status');
                statusBadge.textContent = admin.status;
                if (admin.status === 'active') {
                    statusBadge.className = `${baseBadgeClass} bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400`;
                } else if (admin.status === 'suspended') {
                    statusBadge.className = `${baseBadgeClass} bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-400`;
                } else {
                    statusBadge.className = `${baseBadgeClass} bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400`;
                }

                // Avatar handling
                const initials = admin.full_name.split(' ').map(n => n[0]).join('').substring(0, 2);
                if (admin.profile_photo) {
                    document.getElementById('avatar-img-preview').src = '../../' + admin.profile_photo;
                    document.getElementById('avatar-img-preview').classList.remove('hidden');
                    document.getElementById('avatar-initials').classList.add('hidden');
                } else {
                    document.getElementById('avatar-img-preview').classList.add('hidden');
                    document.getElementById('avatar-initials').textContent = initials;
                    document.getElementById('avatar-initials').classList.remove('hidden');
                }

                // Security Card bindings
                document.getElementById('sec-last-login').textContent = timeAgo(admin.last_login_at);
                document.getElementById('sec-last-ip').textContent = admin.last_login_ip || '-';
                document.getElementById('sec-last-pwd').textContent = timeAgo(admin.last_password_changed);

                // Recovery Status handling
                const recoveryCard = document.getElementById('sec-recovery-card');
                const recoveryIcon = document.getElementById('sec-recovery-icon');
                const recoveryText = document.getElementById('sec-recovery-text');
                if (recoveryCard && recoveryIcon && recoveryText) {
                    const recoveryLabel = recoveryCard.querySelector('label');
                    if (admin.security_question_set == 1) {
                        recoveryCard.className = "bg-emerald-50 dark:bg-emerald-500/5 rounded-2xl p-5 border border-emerald-100 dark:border-emerald-500/10 relative overflow-hidden group hover:border-emerald-300 transition-colors";
                        recoveryIcon.textContent = "verified_user";
                        recoveryIcon.className = "material-symbols-outlined text-emerald-500 opacity-80 mb-3 relative z-10 text-[28px]";
                        if (recoveryLabel) recoveryLabel.className = "block text-[10px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest mb-1 relative z-10";
                        recoveryText.textContent = "✓ Configured";
                        recoveryText.className = "font-bold text-[13px] tracking-wide text-emerald-700 dark:text-emerald-400 relative z-10 truncate";
                    } else {
                        recoveryCard.className = "bg-rose-50 dark:bg-rose-500/5 rounded-2xl p-5 border border-rose-100 dark:border-rose-500/10 relative overflow-hidden group hover:border-rose-300 transition-colors";
                        recoveryIcon.textContent = "gpp_maybe";
                        recoveryIcon.className = "material-symbols-outlined text-rose-500 opacity-80 mb-3 relative z-10 text-[28px]";
                        if (recoveryLabel) recoveryLabel.className = "block text-[10px] font-black text-rose-600 dark:text-rose-400 uppercase tracking-widest mb-1 relative z-10";
                        recoveryText.textContent = "⚠ Action Required";
                        recoveryText.className = "font-bold text-[13px] tracking-wide text-rose-700 dark:text-rose-400 relative z-10 truncate";
                    }
                }

                // 2FA is currently a placeholder
                // document.getElementById('sec-2fa-toggle').checked = (admin.two_fa_enabled == 1);

                // Recent Activity
                if (response.activity && response.activity.length > 0) {
                    activityData = response.activity;
                    currentActivityPage = 1;
                    renderActivityPage();
                } else {
                    activityData = [];
                    renderActivityPage();
                }

                // Super Admin Privileges Check
                if (admin.admin_role === 'super_admin') {
                    // Show Add Admin button
                    const addBtn = document.getElementById('header-add-admin-btn');
                    if (addBtn) addBtn.classList.remove('hidden');

                    // Show Management Card
                    document.getElementById('admin-management-container').classList.remove('hidden');
                    loadAdminList();
                    loadLockedAdmins();
                }

                // Security Question Status Badge
                const sqContainer = document.getElementById('sq-status-container');
                const sqSet = admin.security_question_set == 1;
                if (sqContainer) {
                    if (sqSet) {
                        sqContainer.innerHTML = `
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-50 border border-emerald-200 dark:bg-emerald-500/10 dark:border-emerald-500/20">
                                <span class="material-symbols-outlined text-emerald-500 text-[14px]">check_circle</span>
                                <span class="text-[10px] font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-400">Security Question Set ✓</span>
                            </div>`;
                        const sqQField = document.getElementById('sq-question');
                        if (sqQField && admin.security_question) sqQField.value = admin.security_question;
                        const sqBtnText = document.getElementById('sq-btn-text');
                        if (sqBtnText) sqBtnText.textContent = 'Update Security Question';
                    } else {
                        const warningMsg = admin.admin_role === 'super_admin'
                            ? '⚠️ As a Super Admin, your account is critical. If you get locked, you will need another Super Admin to unlock you, or you must have a security question set.'
                            : '⚠️ You have not set a security question yet. If your account gets locked you will need a Super Admin to unlock it manually.';
                        sqContainer.innerHTML = `
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-50 border border-amber-200 dark:bg-amber-500/10 dark:border-amber-500/20 mb-3">
                                <span class="material-symbols-outlined text-amber-500 text-[14px]">warning</span>
                                <span class="text-[10px] font-black uppercase tracking-widest text-amber-600 dark:text-amber-400">Not Set — Recommended</span>
                            </div>
                            <p class="text-[11px] font-semibold text-amber-700 dark:text-amber-400 leading-relaxed bg-amber-50 dark:bg-amber-500/10 border border-amber-100 dark:border-amber-500/20 rounded-xl p-3">
                                ${warningMsg}
                            </p>`;
                    }
                }

            } else {
                window.portal.showToast(response.message || 'Failed to load profile', 'error');
            }
        } catch (error) {
            console.error('Failed to load profile:', error);
            window.portal.showToast('Network error loading profile', 'error');
        }
    }

    let pendingUnlockAdminId = null;
    let pendingUnlockName = '';

    async function loadLockedAdmins() {
        try {
            const res = await window.api.get('/admin/get-locked-admins.php');
            if (!res.success || res.total === 0) {
                document.getElementById('locked-admins-container').classList.add('hidden');
                return;
            }

            document.getElementById('locked-admins-container').classList.remove('hidden');
            document.getElementById('locked-count-badge').textContent = `${res.total} Locked`;

            const formatDT = (d) => {
                if (!d) return '-';
                return new Date(d).toLocaleString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
            };

            const tbody = document.getElementById('locked-admins-body');
            tbody.innerHTML = res.locked_admins.map(a => {
                const lockReasonLabel = a.lock_reason === 'failed_password'
                    ? `<span class="px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-widest bg-rose-50 text-rose-600 border border-rose-100">Wrong Password (3x)</span>`
                    : `<span class="px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-widest bg-amber-50 text-amber-600 border border-amber-100">Wrong Answer (5x)</span>`;

                const attemptsColor = a.answer_attempts >= 3 ? 'text-rose-600 font-bold' : 'text-slate-600 dark:text-slate-300';
                const sqHtml = a.security_question_set
                    ? `<span class="text-emerald-600 text-xs font-bold">Yes</span>`
                    : `<span class="text-rose-500 text-xs font-bold">No</span>`;

                return `<tr class="hover:bg-rose-50/30 dark:hover:bg-rose-500/5 transition-colors border-b border-slate-50 dark:border-white/5 last:border-0">
                    <td class="py-4 px-6">
                        <div class="text-xs font-black dark:text-white tracking-tight">${a.full_name}</div>
                        <div class="text-[9px] font-bold text-slate-400 mt-0.5">${a.email}</div>
                    </td>
                    <td class="py-4 px-4"><span class="px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-widest bg-teal-500/10 text-teal-600 border border-teal-500/20">${a.admin_role}</span></td>
                    <td class="py-4 px-4 text-[11px] text-slate-500 dark:text-slate-400 font-medium">${formatDT(a.locked_at)}</td>
                    <td class="py-4 px-4">${lockReasonLabel}</td>
                    <td class="py-4 px-4 text-[11px] font-bold ${attemptsColor}">${a.answer_attempts} / 5</td>
                    <td class="py-4 px-4">${sqHtml}</td>
                    <td class="py-4 px-4">
                        <button onclick="window.AdminProfile.showUnlockConfirm(${a.id}, '${a.full_name.replace(/'/g, "\\'")}')"
                            class="px-3 py-1.5 bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white border border-emerald-200 rounded-lg text-[10px] font-black uppercase tracking-widest transition-colors">
                            Unlock
                        </button>
                    </td>
                </tr>`;
            }).join('');
        } catch (err) {
            console.error('Error loading locked admins:', err);
        }
    }

    async function loadAdminList() {

        try {
            const res = await window.api.get('/admin/get-admins.php');
            if (res.success) {
                document.getElementById('management-count-badge').textContent = `${res.total} Admins`;
                const tbody = document.getElementById('admin-management-body');
                if (res.total > 0) {
                    tbody.innerHTML = res.admins.map((a, i) => {

                        let roleHtml = `<span class="px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-widest bg-teal-500/10 text-teal-600 border border-teal-500/20">Admin</span>`;
                        if (a.admin_role === 'moderator') {
                            roleHtml = `<span class="px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-widest bg-amber-500/10 text-amber-600 border border-amber-500/20">Moderator</span>`;
                        }

                        let statHtml = `<span class="px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-widest bg-emerald-500/10 text-emerald-500">• Active</span>`;
                        if (a.status === 'suspended') statHtml = `<span class="px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-widest bg-red-500/10 text-red-500">• Suspended</span>`;

                        const photo = a.profile_photo
                            ? `<img src="../../${a.profile_photo}" class="w-9 h-9 rounded-xl object-cover shadow-sm ring-2 ring-slate-100 dark:ring-white/5">`
                            : `<div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-white/5 flex items-center justify-center text-[11px] font-black text-slate-400 border border-slate-200/50 dark:border-white/10">${a.full_name.charAt(0).toUpperCase()}</div>`;

                        const formatDateLocal = (d) => {
                            if (!d) return '-';
                            const dt = new Date(d);
                            return dt.toLocaleDateString('en-GB', { day: '2-digit', month: 'short' });
                        };

                        return `
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-white/5 transition-colors group">
                            <td class="py-4 px-6 text-[10px] font-black text-slate-400 w-20 text-left font-mono">${a.admin_id}</td>
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    ${photo}
                                    <div>
                                        <div class="text-xs font-black dark:text-white tracking-tight">${a.full_name}</div>
                                        <div class="text-[9px] font-bold text-slate-400 mt-0.5">${a.email}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6">${roleHtml}</td>
                            <td class="py-4 px-6">
                                <span class="text-[10px] font-bold uppercase tracking-tight text-slate-500 dark:text-slate-400">${a.access_level}</span>
                            </td>
                            <td class="py-4 px-6">${statHtml}</td>
                            <td class="py-4 px-6">
                                <div class="text-[10px] font-bold dark:text-slate-300">${a.last_login_at ? timeAgo(a.last_login_at) : 'Never'}</div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="text-[10px] font-bold dark:text-slate-300">${a.created_by_name || 'System'}</div>
                                <div class="text-[9px] font-bold text-slate-400 mt-0.5">${formatDateLocal(a.created_at)}</div>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <a href="admin-detail.html?id=${a.id}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 dark:bg-white/5 hover:bg-primary hover:text-white dark:hover:bg-primary transition-all rounded-lg text-[9px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 shadow-sm border border-transparent hover:border-primary/20">
                                    View Profile
                                    <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                                </a>
                            </td>
                        </tr>
                        `;
                    }).join('');
                } else {
                    tbody.innerHTML = `<tr><td colspan="8" class="text-center py-12 text-slate-400 text-xs font-bold">No other administrators found.</td></tr>`;
                }
            }
        } catch (e) {
            console.error(e);
        }
    }

    // Exported Object
    window.AdminProfile = {
        uploadPhoto: async (input) => {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const formData = new FormData();
                formData.append('profile_photo', file);

                try {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        document.getElementById('avatar-img-preview').src = e.target.result;
                        document.getElementById('avatar-img-preview').classList.remove('hidden');
                        document.getElementById('avatar-initials').classList.add('hidden');
                    };
                    reader.readAsDataURL(file);

                    const response = await window.api.upload('/admin/upload-admin-photo.php', formData);
                    if (response.success) {
                        window.portal.showToast('Profile photo updated', 'success');
                        if (window.AdminPortal) window.AdminPortal.init();
                    } else {
                        window.portal.showToast(response.message || 'Upload failed', 'error');
                    }
                } catch (error) {
                    window.portal.showToast('System error uploading photo', 'error');
                }
            }
        },

        previewNewPhoto: (input) => {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const r = new FileReader();
                r.onload = (e) => {
                    document.getElementById('new-avatar-preview').src = e.target.result;
                    document.getElementById('new-avatar-preview').classList.remove('hidden');
                };
                r.readAsDataURL(file);
            }
        },

        togglePW: (id) => {
            const input = document.getElementById(id);
            if (!input) return;
            const icon = input.nextElementSibling;
            if (input.type === 'password') {
                input.type = 'text';
                if (icon) icon.textContent = 'visibility_off';
            } else {
                input.type = 'password';
                if (icon) icon.textContent = 'visibility';
            }
        },

        checkStrength: (pw) => {
            const pwBar = document.getElementById('pw-bar');
            const pwText = document.getElementById('pw-text');
            if (!pwBar) return;
            let strength = 0;
            if (pw.length >= 8) strength++;
            if (/[A-Z]/.test(pw)) strength++;
            if (/[0-9]/.test(pw)) strength++;
            if (/[^A-Za-z0-9]/.test(pw)) strength++;

            if (strength === 0) {
                pwBar.style.width = '0%'; pwText.textContent = '';
            } else if (strength <= 2) {
                pwBar.style.width = '33%'; pwBar.style.background = '#ef4444'; pwText.textContent = 'Weak'; pwText.style.color = '#ef4444';
            } else if (strength === 3) {
                pwBar.style.width = '66%'; pwBar.style.background = '#f39c12'; pwText.textContent = 'Fair'; pwText.style.color = '#f39c12';
            } else {
                pwBar.style.width = '100%'; pwBar.style.background = '#10b981'; pwText.textContent = 'Strong'; pwText.style.color = '#10b981';
            }
        },

        checkAddStrength: (pw) => {
            const pwBar = document.getElementById('add-pw-bar');
            if (!pwBar) return;
            let strength = 0;
            if (pw.length >= 8) strength++;
            if (/[A-Z]/.test(pw)) strength++;
            if (/[0-9]/.test(pw)) strength++;
            if (/[^A-Za-z0-9]/.test(pw)) strength++;

            if (strength === 0) {
                pwBar.style.width = '0%';
            } else if (strength <= 2) {
                pwBar.style.width = '33%'; pwBar.style.background = '#ef4444';
            } else if (strength === 3) {
                pwBar.style.width = '66%'; pwBar.style.background = '#f39c12';
            } else {
                pwBar.style.width = '100%'; pwBar.style.background = '#10b981';
            }
        },

        updatePassword: async () => {
            const curr = document.getElementById('curr-pw').value;
            const nw = document.getElementById('new-pw').value;
            const conf = document.getElementById('conf-pw').value;

            if (!curr || !nw || !conf) {
                window.portal.showToast('Please fill all password fields', 'error');
                return;
            }

            try {
                const btn = document.activeElement;
                window.api.setButtonLoading(btn, true, 'Updating...');
                const res = await window.api.post('/admin/update-admin-profile.php', {
                    current_password: curr, new_password: nw, confirm_new_password: conf
                });
                if (res.success) {
                    window.portal.showToast(res.message, 'success');
                    document.getElementById('curr-pw').value = '';
                    document.getElementById('new-pw').value = '';
                    document.getElementById('conf-pw').value = '';
                    document.getElementById('pw-bar').style.width = '0%';
                    document.getElementById('pw-text').textContent = '';
                    loadAdminProfile();
                } else {
                    window.portal.showToast(res.message, 'error');
                }
            } catch (e) {
                window.portal.showToast('System error updating password', 'error');
            } finally {
                window.api.setButtonLoading(document.activeElement, false);
            }
        },

        saveProfile: async () => {
            const payload = {
                full_name: document.getElementById('profile-name-input').value,
                date_of_birth: document.getElementById('profile-dob-input').value,
                gender: document.getElementById('profile-gender-input').value,
                phone: document.getElementById('profile-phone-input').value
            };

            const qField = document.getElementById('profile-seq-q-input');
            const aField = document.getElementById('profile-seq-a-input');
            if (qField) payload.security_question = qField.value;
            if (aField) payload.security_answer = aField.value;

            if (!payload.full_name) {
                window.portal.showToast('Full Name is required', 'error');
                return;
            }

            try {
                const btn = document.activeElement;
                window.api.setButtonLoading(btn, true, 'Saving...');
                const res = await window.api.post('/admin/update-admin-profile.php', payload);
                if (res.success) {
                    window.portal.showToast(res.message, 'success');
                    const aField = document.getElementById('profile-seq-a-input');
                    if (aField) aField.value = '';
                    loadAdminProfile();
                } else {
                    window.portal.showToast(res.message, 'error');
                }
            } catch (e) {
                window.portal.showToast('System error updating profile', 'error');
            } finally {
                window.api.setButtonLoading(document.activeElement, false);
            }
        },

        toggle2FA: async (enabled) => {
            try {
                const res = await window.api.post('/admin/update-admin-profile.php', {
                    two_fa_enabled: enabled ? 1 : 0
                });
                if (res.success) {
                    document.getElementById('sec-2fa-label').textContent = enabled ? 'ENABLED' : 'DISABLED';
                    window.portal.showToast('2FA settings updated', 'success');
                } else {
                    document.getElementById('sec-2fa-toggle').checked = !enabled; // revert
                    window.portal.showToast(res.message, 'error');
                }
            } catch (e) {
                document.getElementById('sec-2fa-toggle').checked = !enabled; // revert
                window.portal.showToast('System error updating 2FA', 'error');
            }
        },

        updateRolePreview: (role) => {
            const box = document.getElementById('role-preview-box');
            if (!role) {
                box.classList.add('hidden');
                return;
            }
            box.classList.remove('hidden');
            if (role === 'admin') {
                box.innerHTML = `
                    <div class="font-bold mb-2">Admin Permissions:</div>
                    <ul class="space-y-1">
                        <li>✅ Dashboard Access</li>
                        <li>✅ User Management</li>
                        <li>✅ Staff Management</li>
                        <li>✅ Transaction Overview</li>
                        <li>✅ Branch View</li>
                        <li>✅ Pending Approvals & KYC Review</li>
                        <li class="opacity-50 line-through">❌ Suspend or Delete Users</li>
                        <li class="opacity-50 line-through">❌ Suspend Staff</li>
                        <li class="opacity-50 line-through">❌ Change Interest Rate</li>
                        <li class="opacity-50 line-through">❌ Add New Branch</li>
                        <li class="opacity-50 line-through">❌ Manage Other Admins</li>
                    </ul>
                `;
            } else if (role === 'moderator') {
                box.innerHTML = `
                    <div class="font-bold mb-2">Moderator Permissions:</div>
                    <ul class="space-y-1">
                        <li>✅ Dashboard Access</li>
                        <li>✅ Pending Approvals</li>
                        <li>✅ Review, Approve and Reject KYC</li>
                        <li class="opacity-50 line-through">❌ User Management</li>
                        <li class="opacity-50 line-through">❌ Staff Management</li>
                        <li class="opacity-50 line-through">❌ Transaction Overview</li>
                        <li class="opacity-50 line-through">❌ Branch Management</li>
                        <li class="opacity-50 line-through">❌ Interest Rate Settings</li>
                        <li class="opacity-50 line-through">❌ Admin Management</li>
                        <li class="opacity-50 line-through">❌ All other features</li>
                    </ul>
                `;
            }
        },

        showAddModal: async () => {
            const modal = document.getElementById('add-admin-modal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.style.overflow = 'hidden';

                // Fetch prospective Admin ID
                try {
                    const res = await window.api.get('/admin/get-next-admin-id.php');
                    if (res.success) {
                        document.getElementById('add-admin-id').value = res.next_id;
                    }
                } catch (e) {
                    console.error('Failed to fetch next admin ID');
                }
            }
        },
        closeAddModal: () => {
            const modal = document.getElementById('add-admin-modal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.style.overflow = '';
            }
        },

        submitAddAdmin: async () => {
            const btn = document.getElementById('add-admin-submit-btn');

            // Clear errors
            document.querySelectorAll('[id^=err-add-]').forEach(el => el.classList.add('hidden'));

            const fields = ['name', 'dob', 'gender', 'phone', 'email', 'pw', 'conf-pw', 'seq-q', 'seq-a', 'role'];
            const vals = {};
            let hasErr = false;

            fields.forEach(f => {
                vals[f] = document.getElementById('add-' + f).value;
                if (!vals[f]) {
                    const e = document.getElementById('err-add-' + f);
                    e.textContent = 'This field is required';
                    e.classList.remove('hidden');
                    hasErr = true;
                }
            });

            if (vals.pw !== vals['conf-pw']) {
                const e = document.getElementById('err-add-conf-pw');
                e.textContent = 'Passwords do not match';
                e.classList.remove('hidden');
                hasErr = true;
            }

            if (hasErr) return;

            btn.disabled = true;
            btn.querySelector('span:last-child').classList.remove('hidden');

            const formData = new FormData();
            formData.append('full_name', vals.name);
            formData.append('date_of_birth', vals.dob);
            formData.append('gender', vals.gender);
            formData.append('phone', vals.phone);
            formData.append('email', vals.email);
            formData.append('password', vals.pw);
            formData.append('confirm_password', vals['conf-pw']);
            formData.append('security_question', vals['seq-q']);
            formData.append('security_answer', vals['seq-a']);
            formData.append('admin_role', vals.role);

            const fileInput = document.getElementById('new-admin-photo');
            if (fileInput.files.length > 0) {
                formData.append('profile_photo', fileInput.files[0]);
            }

            try {
                const response = await window.api.upload('/admin/add-admin.php', formData);
                if (response.success) {
                    window.AdminProfile.closeAddModal();

                    // Clear form
                    fields.forEach(f => {
                        const input = document.getElementById('add-' + f);
                        if (input) {
                            input.value = '';
                            // Reset custom dropdown labels if applicable
                            const dropdown = input.closest('.finzo-dropdown');
                            if (dropdown) {
                                const label = dropdown.querySelector('.dropdown-label');
                                if (label) {
                                    if (f === 'gender') label.textContent = 'Select Gender';
                                    else if (f === 'role') label.textContent = 'Select Role';
                                    else label.textContent = 'Select Option';
                                }
                                dropdown.querySelectorAll('.finzo-dropdown-option').forEach(opt => opt.classList.remove('is-selected'));
                            }
                        }
                    });
                    document.getElementById('new-avatar-preview').src = '';
                    document.getElementById('new-avatar-preview').classList.add('hidden');
                    document.getElementById('role-preview-box').classList.add('hidden');
                    document.getElementById('add-pw-bar').style.width = '0%';

                    window.portal.showToast(`Admin ${response.admin_name} has been added successfully`, 'success');
                    loadAdminList(); // Refresh list automatically
                } else {
                    if (response.field) {
                        const fieldMap = {
                            'full_name': 'name',
                            'date_of_birth': 'dob',
                            'gender': 'gender',
                            'phone': 'phone',
                            'email': 'email',
                            'password': 'pw',
                            'confirm_password': 'conf-pw',
                            'security_question': 'seq-q',
                            'security_answer': 'seq-a',
                            'admin_role': 'role'
                        };
                        const targetId = 'err-add-' + (fieldMap[response.field] || response.field);
                        const e = document.getElementById(targetId);
                        if (e) {
                            e.textContent = response.message;
                            e.classList.remove('hidden');
                        } else {
                            window.portal.showToast(response.message, 'error');
                        }
                    } else {
                        window.portal.showToast(response.message || 'Operation failed', 'error');
                    }
                }
            } catch (err) {
                window.portal.showToast('System error adding admin', 'error');
            } finally {
                btn.disabled = false;
                btn.querySelector('span:last-child').classList.add('hidden');
            }
        },

        // Activity Pagination
        prevActivityPage: () => {
            if (currentActivityPage > 1) {
                currentActivityPage--;
                renderActivityPage();
            }
        },
        nextActivityPage: () => {
            const totalPages = Math.ceil(activityData.length / ACTIVITY_PER_PAGE);
            if (currentActivityPage < totalPages) {
                currentActivityPage++;
                renderActivityPage();
            }
        },

        saveSecurityQuestion: async (e) => {
            if (e) e.preventDefault();
            const question = document.getElementById('sq-question').value.trim();
            const answer = document.getElementById('sq-answer').value;
            const currPw = document.getElementById('sq-curr-pw').value;

            if (!question || question.length < 10) {
                window.portal.showToast('Security question must be at least 10 characters', 'error');
                return;
            }
            if (!answer || answer.length < 3) {
                window.portal.showToast('Security answer must be at least 3 characters', 'error');
                return;
            }
            if (!currPw) {
                window.portal.showToast('Current password is required', 'error');
                return;
            }

            const btn = document.getElementById('btn-save-sq');
            const origHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span><span>Saving...</span>';

            try {
                const res = await window.api.post('/admin/save-security-question.php', {
                    security_question: question,
                    security_answer: answer,
                    current_password: currPw
                });
                if (res.success) {
                    window.portal.showToast('Security question saved successfully', 'success');
                    document.getElementById('sq-answer').value = '';
                    document.getElementById('sq-curr-pw').value = '';
                    // Update badge to Set
                    const sqContainer = document.getElementById('sq-status-container');
                    if (sqContainer) {
                        sqContainer.innerHTML = `
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-50 border border-emerald-200 dark:bg-emerald-500/10 dark:border-emerald-500/20">
                                <span class="material-symbols-outlined text-emerald-500 text-[14px]">check_circle</span>
                                <span class="text-[10px] font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-400">Security Question Set ✓</span>
                            </div>`;
                    }
                    const sqBtnText = document.getElementById('sq-btn-text');
                    if (sqBtnText) sqBtnText.textContent = 'Update Security Question';
                } else {
                    window.portal.showToast(res.message || 'Failed to save security question', 'error');
                }
            } catch (err) {
                window.portal.showToast('System error saving security question', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = origHtml;
            }
        },

        showUnlockConfirm: (adminId, adminName) => {
            pendingUnlockAdminId = adminId;
            pendingUnlockName = adminName;
            const overlay = document.getElementById('unlock-confirm-overlay');
            const msg = document.getElementById('unlock-confirm-msg');
            if (msg) msg.textContent = `Unlock ${adminName}'s account? They will be able to login with their existing password immediately.`;
            if (overlay) {
                overlay.classList.remove('hidden');
                overlay.classList.add('flex');
            }
        },

        closeUnlockConfirm: () => {
            pendingUnlockAdminId = null;
            pendingUnlockName = '';
            const overlay = document.getElementById('unlock-confirm-overlay');
            if (overlay) {
                overlay.classList.add('hidden');
                overlay.classList.remove('flex');
            }
        },

        confirmUnlock: async () => {
            if (!pendingUnlockAdminId) return;
            const btn = document.getElementById('confirm-unlock-btn');
            const origHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="material-symbols-outlined animate-spin text-[16px]">progress_activity</span> Unlocking...';

            try {
                const res = await window.api.post('/admin/unlock-admin-account.php', {
                    target_admin_id: pendingUnlockAdminId
                });
                window.AdminProfile.closeUnlockConfirm();
                if (res.success) {
                    window.portal.showToast(res.message, 'success');
                    // Reload locked list
                    loadLockedAdmins();
                } else {
                    window.portal.showToast(res.message || 'Failed to unlock account', 'error');
                }
            } catch (err) {
                window.portal.showToast('System error unlocking account', 'error');
                window.AdminProfile.closeUnlockConfirm();
            } finally {
                btn.disabled = false;
                btn.innerHTML = origHtml;
            }
        }
    };


    // Initialization
    document.addEventListener('DOMContentLoaded', () => {
        loadAdminProfile();

        // Escape keys
        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                window.AdminProfile.closeAddModal();
                window.AdminProfile.closeDeleteModal();
            }
        });
    });

})();
