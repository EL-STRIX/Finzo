/**
 * Finzo Admin — Profile & RBAC Management Handler
 * Manages administrative account settings, avatar, security, and super_admin privileges.
 */

(function() {
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
                document.getElementById('topbar-avatar').textContent = admin.full_name.charAt(0);
                
                // Profile Card bindings
                document.getElementById('profile-name-display').textContent = admin.full_name;
                document.getElementById('profile-name-input').value = admin.full_name;
                document.getElementById('profile-dob-input').value = admin.date_of_birth || '';
                document.getElementById('profile-gender-input').value = admin.gender || '';
                document.getElementById('profile-phone-input').value = admin.phone || '';
                document.getElementById('profile-email-input').value = admin.email;
                document.getElementById('profile-seq-q-input').value = admin.security_question || '';
                
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
                const initials = admin.full_name.split(' ').map(n=>n[0]).join('').substring(0, 2);
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
                document.getElementById('sec-failed').textContent = admin.failed_attempts || '0';
                
                const m2fa = document.getElementById('sec-2fa-toggle');
                const p2fa = document.getElementById('sec-2fa-label');
                if (admin.two_fa_enabled == 1) {
                    m2fa.checked = true;
                    p2fa.textContent = 'ENABLED';
                } else {
                    m2fa.checked = false;
                    p2fa.textContent = 'DISABLED';
                }

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
                    // Inject Add Admin button
                    const c = document.getElementById('header-actions-container');
                    if (!document.getElementById('inject-add-admin')) {
                        const btn = document.createElement('button');
                        btn.id = 'inject-add-admin';
                        btn.className = 'admin-btn-primary flex items-center gap-2 !py-2';
                        btn.innerHTML = `<span class="material-symbols-outlined text-lg">add</span> <span class="hidden sm:inline">Add Admin</span>`;
                        btn.onclick = () => window.AdminProfile.showAddModal();
                        c.prepend(btn);
                    }
                    
                    // Show Management Card
                    document.getElementById('admin-management-container').classList.remove('hidden');
                    loadAdminList();
                }

            } else {
                window.portal.showToast(response.message || 'Failed to load profile', 'error');
            }
        } catch (error) {
            console.error('Failed to load profile:', error);
            window.portal.showToast('Network error loading profile', 'error');
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
                        
                        let roleHtml = `<span class="px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-widest bg-teal-500/10 text-teal-600">Admin</span>`;
                        if (a.admin_role === 'moderator') {
                            roleHtml = `<span class="px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-widest bg-amber-500/10 text-amber-600">Moderator</span>`;
                        }

                        let statHtml = `<span class="px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-widest bg-emerald-500/10 text-emerald-500">• Active</span>`;
                        if(a.status === 'suspended') statHtml = `<span class="px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-widest bg-red-500/10 text-red-500">• Suspended</span>`;
                        
                        let actHtml = ``;
                        if(a.status === 'active') {
                            actHtml += `<button onclick="window.AdminProfile.manageAdmin(${a.id}, 'suspend')" class="p-1 rounded text-amber-500 hover:bg-amber-50 dark:hover:bg-white/5"><span class="material-symbols-outlined text-[18px]">block</span></button>`;
                        } else if (a.status === 'suspended') {
                            actHtml += `<button onclick="window.AdminProfile.manageAdmin(${a.id}, 'activate')" class="p-1 rounded text-emerald-500 hover:bg-emerald-50 dark:hover:bg-white/5"><span class="material-symbols-outlined text-[18px]">check_circle</span></button>`;
                        }
                        actHtml += `<button onclick="window.AdminProfile.showDeleteModal(${a.id}, '${a.full_name}')" class="p-1 rounded text-red-500 hover:bg-red-50 dark:hover:bg-white/5 ml-1"><span class="material-symbols-outlined text-[18px]">delete</span></button>`;

                        const photo = a.profile_photo 
                            ? `<img src="../../${a.profile_photo}" class="w-8 h-8 rounded-full object-cover">`
                            : `<div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-[10px] font-bold">${a.full_name.charAt(0).toUpperCase()}</div>`;

                        return `
                        <tr id="admin-row-${a.id}" class="hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">
                            <td class="text-xs font-bold opacity-50">${i+1}</td>
                            <td>${photo}</td>
                            <td>
                                <div class="font-bold text-sm">${a.full_name}</div>
                                <div class="text-[10px] opacity-60">${a.email}</div>
                            </td>
                            <td>${roleHtml}</td>
                            <td><span class="text-[10px] uppercase font-bold opacity-60">${a.access_level}</span></td>
                            <td>${statHtml}</td>
                            <td class="text-[10px] font-bold opacity-60">${timeAgo(a.last_login_at)}</td>
                            <td>
                                <div class="text-[10px] font-bold">${a.created_by_name || 'System'}</div>
                                <div class="text-[9px] opacity-50">${formatDate(a.created_at)}</div>
                            </td>
                            <td class="text-right whitespace-nowrap">${actHtml}</td>
                        </tr>
                        `;
                    }).join('');
                } else {
                    tbody.innerHTML = `<tr><td colspan="9" class="text-center py-10">
                        <span class="material-symbols-outlined text-4xl opacity-20 mb-2">person_add</span>
                        <div class="font-bold">No admins added yet</div>
                        <div class="text-xs opacity-50 mt-1">Click + Add Admin to create your first admin account</div>
                    </td></tr>`;
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

            if(!curr || !nw || !conf){
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
                 phone: document.getElementById('profile-phone-input').value,
                 security_question: document.getElementById('profile-seq-q-input').value,
                 security_answer: document.getElementById('profile-seq-a-input').value
             };

             if(!payload.full_name || !payload.security_question){
                 window.portal.showToast('Full Name and Security Question are required', 'error');
                 return;
             }

             try {
                 const btn = document.activeElement;
                 window.api.setButtonLoading(btn, true, 'Saving...');
                 const res = await window.api.post('/admin/update-admin-profile.php', payload);
                 if (res.success) {
                     window.portal.showToast(res.message, 'success');
                     document.getElementById('profile-seq-a-input').value = '';
                     loadAdminProfile();
                 } else {
                     window.portal.showToast(res.message, 'error');
                 }
             } catch(e) {
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
                if(res.success){
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

        showAddModal: () => {
            document.getElementById('add-admin-modal').style.display = 'flex';
        },
        closeAddModal: () => {
            document.getElementById('add-admin-modal').style.display = 'none';
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
                    fields.forEach(f => document.getElementById('add-' + f).value = '');
                    document.getElementById('new-avatar-preview').src = '';
                    document.getElementById('new-avatar-preview').classList.add('hidden');
                    document.getElementById('role-preview-box').classList.add('hidden');
                    document.getElementById('add-pw-bar').style.width = '0%';

                    window.portal.showToast(`Admin ${response.admin_name} has been added successfully`, 'success');
                    loadAdminList(); // Refresh list automatically
                } else {
                    if (response.field) {
                        const e = document.getElementById('err-add-' + response.field.replace(/_/g, '-').replace('full-name', 'name').replace('date-of-birth', 'dob').replace('security', 'seq').replace('-answer', '-a').replace('-question', '-q').replace('admin-role', 'role').replace('password', 'pw'));
                        if(e){
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

        manageAdmin: async (id, action) => {
            try {
                const res = await window.api.post('/admin/admin-action.php', { target_admin_id: id, action: action });
                if(res.success){
                    window.portal.showToast(res.message, 'success');
                    loadAdminList();
                } else {
                    window.portal.showToast(res.message, 'error');
                }
            } catch (e) {
                window.portal.showToast('System error performing action', 'error');
            }
        },

        showDeleteModal: (id, name) => {
            window.targetDeleteAdminId = id;
            document.getElementById('delete-admin-text').textContent = `Are you sure you want to delete ${name}? This action cannot be undone.`;
            document.getElementById('delete-admin-modal').style.display = 'flex';
        },
        closeDeleteModal: () => {
            window.targetDeleteAdminId = null;
            document.getElementById('delete-admin-modal').style.display = 'none';
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
        }
    };

    // Attach to confirm delete statically
    document.getElementById('confirm-delete-btn').addEventListener('click', async () => {
        if (!window.targetDeleteAdminId) return;
        try {
            const res = await window.api.post('/admin/admin-action.php', { target_admin_id: window.targetDeleteAdminId, action: 'delete' });
            if(res.success){
                window.portal.showToast(res.message, 'success');
                window.AdminProfile.closeDeleteModal();
                loadAdminList();
            } else {
                window.portal.showToast(res.message, 'error');
            }
        } catch (e) {
            window.portal.showToast('System error performing action', 'error');
        }
    });

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
