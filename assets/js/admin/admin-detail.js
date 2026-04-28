/* ═══════════════════════════════════════════
   FINZO — Admin Detail Controller
   Handles fetching and managing specific admin data.
   ═══════════════════════════════════════════ */

(function() {
    let currentAdminId = null;
    let currentAdminData = null;

    window.AdminDetail = {
        /**
         * Initialize page
         */
        init: async function() {
            const urlParams = new URLSearchParams(window.location.search);
            currentAdminId = urlParams.get('id');

            if (!currentAdminId) {
                window.location.href = 'admin-profile.html';
                return;
            }

            await this.loadAdminData();
        },

        /**
         * Fetch data from API
         */
        loadAdminData: async function() {
            try {
                const response = await window.api.get(`/admin/get-admin-detail.php?id=${currentAdminId}`);
                if (response.success) {
                    currentAdminData = response.admin;
                    this.renderUI(response.admin);
                    this.renderActivity(response.activity);
                } else {
                    window.portal.showToast(response.message || 'Error loading admin data', 'error');
                    setTimeout(() => window.location.href = 'admin-profile.html', 2000);
                }
            } catch (error) {
                console.error('Failed to load admin detail:', error);
                window.portal.showToast('Network error loading detail', 'error');
            }
        },

        /**
         * Populate UI elements
         */
        renderUI: function(admin) {
            // Header
            document.getElementById('header-admin-name').textContent = admin.full_name;
            
            // Status Badge Topbar
            const statusBox = document.getElementById('header-status-badge');
            const statusDot = statusBox.querySelector('div');
            const statusText = statusBox.querySelector('span');
            
            statusBox.classList.remove('hidden');
            if (admin.status === 'active') {
                statusBox.className = 'flex items-center gap-2 px-3 py-1.5 rounded-full border border-emerald-100 bg-emerald-50 dark:bg-emerald-500/10 dark:border-emerald-500/20';
                statusDot.className = 'w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse';
                statusText.className = 'text-[10px] font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-400';
                statusText.textContent = 'Active Account';
                
                // Update Sidebar Button
                document.getElementById('suspend-label').textContent = 'Suspend Admin';
                document.getElementById('suspend-icon').textContent = 'block';
            } else {
                statusBox.className = 'flex items-center gap-2 px-3 py-1.5 rounded-full border border-rose-100 bg-rose-50 dark:bg-rose-500/10 dark:border-rose-500/20';
                statusDot.className = 'w-1.5 h-1.5 rounded-full bg-rose-500';
                statusText.className = 'text-[10px] font-black uppercase tracking-widest text-rose-600 dark:text-rose-400';
                statusText.textContent = 'Account Suspended';

                // Update Sidebar Button
                document.getElementById('suspend-label').textContent = 'Activate Admin';
                document.getElementById('suspend-icon').textContent = 'check_circle';
            }

            // Photo
            const photoContainer = document.getElementById('admin-photo-container');
            const initialsSpan = document.getElementById('photo-initials');
            if (admin.profile_photo) {
                photoContainer.innerHTML = `<img src="../../${admin.profile_photo}" class="w-full h-full object-cover">`;
            } else {
                initialsSpan.textContent = admin.full_name.split(' ').map(n=>n[0]).join('').substring(0,2).toUpperCase();
            }

            // Info Grid
            document.getElementById('info-name').textContent = admin.full_name;
            document.getElementById('info-admin-id').textContent = admin.admin_id || '---';
            document.getElementById('info-email').innerHTML = `<span class="material-symbols-outlined text-slate-400 text-lg">mail</span> ${admin.email}`;
            document.getElementById('info-phone').textContent = admin.phone || 'Not Provided';
            document.getElementById('info-gender').textContent = admin.gender || 'Not Specified';
            document.getElementById('info-dob').textContent = admin.date_of_birth ? window.FinzoUtils.formatDate(admin.date_of_birth) : 'Not Provided';
            document.getElementById('info-joined-date').textContent = window.FinzoUtils.formatDate(admin.created_at);

            // Badges
            const roleBadge = document.getElementById('info-role-badge');
            roleBadge.textContent = admin.admin_role === 'moderator' ? 'Moderator' : 'Administrator';
            roleBadge.className = `px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest ${admin.admin_role === 'moderator' ? 'bg-amber-500/10 text-amber-600' : 'bg-primary text-white shadow-lg shadow-primary/20'}`;

            const accessBadge = document.getElementById('info-access-badge');
            accessBadge.textContent = admin.access_level === 'full' ? 'Full Access' : 'Restricted';
            accessBadge.className = `px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest ${admin.access_level === 'full' ? 'bg-emerald-500/10 text-emerald-600' : 'bg-slate-100 text-slate-500'}`;

            // System Audit
            document.getElementById('sys-last-login').textContent = admin.last_login_at ? window.FinzoUtils.timeAgo(admin.last_login_at) : 'Never';
            document.getElementById('sys-last-ip').textContent = admin.last_login_ip || 'No Record';
            document.getElementById('sys-pwd-change').textContent = admin.last_password_changed ? window.FinzoUtils.timeAgo(admin.last_password_changed) : 'Unknown';
            const failedEl = document.getElementById('sys-failed-attempts');
            const attempts = admin.failed_attempts || 0;
            failedEl.textContent = attempts;
            failedEl.className = `text-xs font-bold ${attempts > 0 ? 'text-rose-500' : 'text-emerald-500'}`;
            document.getElementById('sys-created-by').textContent = admin.created_by_name || 'System';
            document.getElementById('sys-id').textContent = admin.admin_id;

            // Role-based Security Note
            const noteText = document.getElementById('security-note-text');
            if (noteText) {
                if (admin.admin_role === 'moderator') {
                    noteText.textContent = '"Moderators have restricted access. All KYC approvals and account reviews are logged for quality assurance and compliance monitoring."';
                } else {
                    noteText.textContent = '"Administrators possess elevated privileges. Every system modification and user override is recorded in the global audit log for institutional security."';
                }
            }
        },

        /**
         * Render Activity Table
         */
        renderActivity: function(activities) {
            const tbody = document.getElementById('activity-tbody');
            if (!activities || activities.length === 0) {
                tbody.innerHTML = `<tr><td colspan="4" class="text-center py-12 text-slate-400 text-xs font-bold">No activity recorded for this administrator.</td></tr>`;
                return;
            }

            tbody.innerHTML = activities.map(act => `
                <tr>
                    <td class="py-4 px-6 text-[11px] font-bold text-slate-500">${window.FinzoUtils.formatDateTime(act.created_at)}</td>
                    <td class="py-4 px-6">
                        <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-white/5 text-[9px] font-black uppercase tracking-widest text-slate-600 dark:text-slate-400">${act.action_type.replace(/_/g, ' ')}</span>
                    </td>
                    <td class="py-4 px-6 text-[11px] font-medium dark:text-slate-300">${act.details || '-'}</td>
                    <td class="py-4 px-6 text-right text-[10px] font-mono font-bold text-slate-400">${act.ip_address}</td>
                </tr>
            `).join('');
        },

        /**
         * Administrative Actions
         */
        toggleStatus: async function() {
            const isActive = currentAdminData.status === 'active';
            const confirmMsg = isActive 
                ? `Are you sure you want to SUSPEND ${currentAdminData.full_name}? They will lose all administrative access immediately.`
                : `Are you sure you want to ACTIVATE ${currentAdminData.full_name}?`;

            if (!confirm(confirmMsg)) return;

            try {
                const response = await window.api.post('/admin/admin-action.php', {
                    action: 'toggle_status',
                    admin_id: currentAdminId
                });

                if (response.success) {
                    window.portal.showToast(response.message, 'success');
                    this.loadAdminData();
                } else {
                    window.portal.showToast(response.message, 'error');
                }
            } catch (err) {
                window.portal.showToast('Failed to update status', 'error');
            }
        },

        showResetModal: function() {
            document.getElementById('reset-modal').classList.remove('hidden');
            document.getElementById('reset-modal').classList.add('flex');
            document.getElementById('new-password').value = '';
            document.getElementById('confirm-password').value = '';
            document.getElementById('err-new-pw').classList.add('hidden');
        },

        closeResetModal: function() {
            document.getElementById('reset-modal').classList.add('hidden');
            document.getElementById('reset-modal').classList.remove('flex');
        },

        submitReset: async function() {
            const pw = document.getElementById('new-password').value;
            const conf = document.getElementById('confirm-password').value;
            const err = document.getElementById('err-new-pw');

            if (pw.length < 8) {
                err.textContent = 'Password must be at least 8 characters';
                err.classList.remove('hidden');
                return;
            }

            if (pw !== conf) {
                err.textContent = 'Passwords do not match';
                err.classList.remove('hidden');
                return;
            }

            const btn = document.getElementById('reset-submit-btn');
            window.api.setButtonLoading(btn, true);

            try {
                const response = await window.api.post('/admin/admin-action.php', {
                    action: 'reset_password',
                    admin_id: currentAdminId,
                    new_password: pw
                });

                if (response.success) {
                    window.portal.showToast(response.message, 'success');
                    this.closeResetModal();
                    this.loadAdminData();
                } else {
                    window.portal.showToast(response.message, 'error');
                }
            } catch (error) {
                window.portal.showToast('Error resetting password', 'error');
            } finally {
                window.api.setButtonLoading(btn, false);
            }
        },

        showDeleteModal: function() {
            document.getElementById('delete-admin-name').textContent = currentAdminData.full_name;
            document.getElementById('delete-modal').classList.remove('hidden');
            document.getElementById('delete-modal').classList.add('flex');
        },

        closeDeleteModal: function() {
            document.getElementById('delete-modal').classList.add('hidden');
            document.getElementById('delete-modal').classList.remove('flex');
        },

        submitDelete: async function() {
            const btn = document.getElementById('delete-submit-btn');
            window.api.setButtonLoading(btn, true);

            try {
                const response = await window.api.post('/admin/admin-action.php', {
                    action: 'delete',
                    admin_id: currentAdminId
                });

                if (response.success) {
                    window.portal.showToast(response.message, 'success');
                    setTimeout(() => window.location.href = 'admin-profile.html', 1500);
                } else {
                    window.portal.showToast(response.message, 'error');
                    window.api.setButtonLoading(btn, false);
                }
            } catch (error) {
                window.portal.showToast('Error deleting admin', 'error');
                window.api.setButtonLoading(btn, false);
            }
        }
    };

    document.addEventListener('DOMContentLoaded', () => window.AdminDetail.init());
})();
