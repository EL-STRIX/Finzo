/* ═══════════════════════════════════════════
   FINZO — Admin Detail Controller
   Handles fetching and managing specific admin data.
   ═══════════════════════════════════════════ */

(function() {
    let currentAdminId = null;
    let currentAdminData = null;

    function notify(msg, type = 'info') {
        if (window.portal && window.portal.showToast) {
            window.portal.showToast(msg, type);
        } else if (window.utils && window.utils.showToast) {
            window.utils.showToast(msg, type);
        } else {
            console.log(`[Toast ${type}]: ${msg}`);
        }
    }

    function resolvePhotoUrl(path) {
        if (!path) return '';
        if (path.startsWith('http') || path.startsWith('data:')) return path;
        let clean = path.replace(/^\/+/, '');
        if (clean.startsWith('Finzo/public/')) {
            return '/' + clean;
        }
        if (clean.startsWith('uploads/') || clean.startsWith('assets/')) {
            return '/Finzo/public/' + clean;
        }
        return '/Finzo/public/uploads/' + clean;
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    window.AdminDetail = {
        /**
         * Initialize page
         */
        init: async function() {
            const urlParams = new URLSearchParams(window.location.search);
            currentAdminId = urlParams.get('id');

            if (!currentAdminId) {
                window.location.href = 'admin-profile.php';
                return;
            }

            await this.loadAdminData();
        },

        /**
         * Fetch data from API
         */
        loadAdminData: async function() {
            try {
                const response = await window.api.get(`/admins/${currentAdminId}`);
                if (response && (response.success || response.admin)) {
                    const payload = response.data || response;
                    const admin = payload.admin || response.admin;
                    const activity = payload.activity || response.activity || [];
                    currentAdminData = admin;
                    this.renderUI(admin);
                    this.renderActivity(activity);
                } else {
                    notify(response?.message || 'Error loading admin dossier', 'error');
                    setTimeout(() => window.location.href = 'admin-profile.php', 2000);
                }
            } catch (error) {
                console.error('Failed to load admin detail:', error);
                notify('Network error loading detail', 'error');
            }
        },

        /**
         * Populate UI elements
         */
        renderUI: function(admin) {
            if (!admin) return;

            // Header
            const headerName = document.getElementById('header-admin-name');
            if (headerName) headerName.textContent = admin.full_name || 'Admin Dossier';
            
            // Status Badge Topbar
            const statusBox = document.getElementById('header-status-badge');
            if (statusBox) {
                statusBox.classList.remove('hidden');
                const isActive = (admin.status || 'active') === 'active';
                if (isActive) {
                    statusBox.className = 'flex items-center gap-2 px-3 py-1.5 rounded-full border border-emerald-500/20 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400';
                    statusBox.innerHTML = '<div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></div><span class="text-[10px] font-black uppercase tracking-widest">Active Account</span>';
                    
                    const suspendLabel = document.getElementById('suspend-label');
                    const suspendIcon = document.getElementById('suspend-icon');
                    if (suspendLabel) suspendLabel.textContent = 'Suspend Admin';
                    if (suspendIcon) suspendIcon.textContent = 'block';
                } else {
                    statusBox.className = 'flex items-center gap-2 px-3 py-1.5 rounded-full border border-rose-500/20 bg-rose-500/10 text-rose-600 dark:text-rose-400';
                    statusBox.innerHTML = '<div class="w-1.5 h-1.5 rounded-full bg-rose-500"></div><span class="text-[10px] font-black uppercase tracking-widest">Account Suspended</span>';

                    const suspendLabel = document.getElementById('suspend-label');
                    const suspendIcon = document.getElementById('suspend-icon');
                    if (suspendLabel) suspendLabel.textContent = 'Activate Admin';
                    if (suspendIcon) suspendIcon.textContent = 'check_circle';
                }
            }

            // Photo
            const initials = (admin.full_name || 'AD').split(' ').filter(Boolean).map(n => n[0]).join('').toUpperCase().substring(0, 2);
            const photoContainer = document.getElementById('admin-photo-container');
            if (photoContainer) {
                if (admin.profile_photo) {
                    const photoSrc = resolvePhotoUrl(admin.profile_photo);
                    photoContainer.innerHTML = `<img src="${photoSrc}?v=${Date.now()}" class="w-full h-full object-cover" alt="${escapeHtml(admin.full_name)}" onerror="this.outerHTML='<span id=\\\'photo-initials\\\' class=\\\'text-4xl font-black text-text-secondary\\\'>${initials}</span>'">`;
                } else {
                    photoContainer.innerHTML = `<span id="photo-initials" class="text-4xl font-black text-text-secondary">${initials}</span>`;
                }
            }

            // Info Grid
            const setTxt = (id, val) => {
                const el = document.getElementById(id);
                if (el) el.textContent = val || '---';
            };

            setTxt('info-name', admin.full_name);
            setTxt('info-admin-id', admin.admin_id);
            
            const emailEl = document.getElementById('info-email');
            if (emailEl) emailEl.innerHTML = `<span class="material-symbols-outlined text-text-secondary text-lg" aria-hidden="true">mail</span> <span>${escapeHtml(admin.email || '---')}</span>`;
            
            setTxt('info-phone', admin.phone || 'Not Provided');
            setTxt('info-gender', admin.gender || 'Not Specified');
            
            const formatDate = (d) => {
                if (!d) return 'Not Provided';
                const dt = new Date(d);
                return isNaN(dt.getTime()) ? d : dt.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
            };
            const timeAgo = (d) => {
                if (!d) return 'Never';
                if (window.FinzoUtils && window.FinzoUtils.timeAgo) return window.FinzoUtils.timeAgo(d);
                return d;
            };

            setTxt('info-dob', formatDate(admin.date_of_birth));
            setTxt('info-joined-date', formatDate(admin.created_at));

            // Badges
            const roleBadge = document.getElementById('info-role-badge');
            if (roleBadge) {
                const isMod = admin.admin_role === 'moderator';
                roleBadge.textContent = isMod ? 'Moderator' : 'Administrator';
                roleBadge.className = `px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest ${isMod ? 'bg-amber-500/10 text-amber-600 border border-amber-500/20' : 'bg-teal-500/10 text-teal-600 border border-teal-500/20'}`;
            }

            const accessBadge = document.getElementById('info-access-badge');
            if (accessBadge) {
                const isFull = admin.access_level === 'full';
                accessBadge.textContent = isFull ? 'Full Access' : 'Restricted';
                accessBadge.className = `px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest ${isFull ? 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20' : 'bg-surface-subtle text-text-secondary border border-border-subtle'}`;
            }

            // System Audit
            setTxt('sys-last-login', admin.last_login_at ? timeAgo(admin.last_login_at) : 'Never');
            setTxt('sys-last-ip', admin.last_login_ip || 'No Record');
            setTxt('sys-pwd-change', admin.last_password_changed ? timeAgo(admin.last_password_changed) : 'Never');
            
            const failedEl = document.getElementById('sys-failed-attempts');
            if (failedEl) {
                const attempts = admin.failed_attempts || 0;
                failedEl.textContent = attempts;
                failedEl.className = `text-xs font-bold ${attempts > 0 ? 'text-rose-500' : 'text-emerald-500'}`;
            }
            
            setTxt('sys-created-by', admin.created_by_name || 'System');
            setTxt('sys-id', admin.admin_id);

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
            if (!tbody) return;

            if (!activities || activities.length === 0) {
                tbody.innerHTML = `<tr><td colspan="4" class="py-12 text-center"><div class="flex flex-col items-center justify-center"><span class="material-symbols-outlined text-4xl text-text-secondary/30 mb-3" aria-hidden="true">inventory_2</span><div class="text-sm font-bold text-text-secondary">No activity recorded for this administrator.</div></div></td></tr>`;
                return;
            }

            const formatDT = (d) => {
                if (!d) return '-';
                const dt = new Date(d);
                return isNaN(dt.getTime()) ? d : dt.toLocaleString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
            };

            tbody.innerHTML = activities.map(act => `
                <tr class="hover:bg-surface-subtle transition-colors">
                    <td class="py-4 px-6 text-[11px] font-bold text-text-secondary">${formatDT(act.created_at)}</td>
                    <td class="py-4 px-6">
                        <span class="px-2.5 py-1 rounded-lg bg-surface-subtle border border-border-subtle text-[9px] font-black uppercase tracking-widest text-text-primary">${escapeHtml((act.action_type || act.action || '').replace(/_/g, ' '))}</span>
                    </td>
                    <td class="py-4 px-6 text-[11px] font-medium text-text-primary">${escapeHtml(act.details || '-')}</td>
                    <td class="py-4 px-6 text-right text-[10px] font-mono font-bold text-text-secondary">${escapeHtml(act.ip_address || '127.0.0.1')}</td>
                </tr>
            `).join('');
        },

        /**
         * Administrative Actions
         */
        toggleStatus: async function() {
            if (!currentAdminData) return;
            const isActive = currentAdminData.status === 'active';
            const confirmMsg = isActive 
                ? `Are you sure you want to SUSPEND ${currentAdminData.full_name}? They will lose all administrative access immediately.`
                : `Are you sure you want to ACTIVATE ${currentAdminData.full_name}?`;

            if (!confirm(confirmMsg)) return;

            try {
                const response = await window.api.patch('/admins/' + currentAdminData.id, { action: 'toggle_status' });
                const payload = response ? (response.data || response) : {};

                if (response && (response.success || payload.success)) {
                    notify(payload.message || response.message || 'Admin status updated successfully', 'success');
                    this.loadAdminData();
                } else {
                    notify(payload.message || response?.message || 'Failed to update status', 'error');
                }
            } catch (err) {
                notify('Failed to update status', 'error');
            }
        },

        showResetModal: function() {
            const modal = document.getElementById('reset-modal');
            if (!modal) return;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';

            const successState = document.getElementById('reset-success-state');
            if (successState) successState.classList.add('hidden');
            
            const actions = document.getElementById('reset-modal-actions');
            if (actions) actions.classList.remove('hidden');
            
            const existingDone = document.getElementById('reset-done-btn');
            if (existingDone) existingDone.remove();
            
            const display = document.getElementById('generated-password-display');
            if (display) {
                display.onclick = () => {
                    const text = display.textContent;
                    if (!text || text === '--------') return;
                    navigator.clipboard.writeText(text).then(() => {
                        notify('Password copied to clipboard', 'success');
                    });
                };
            }
        },

        closeResetModal: function() {
            const modal = document.getElementById('reset-modal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.style.overflow = '';
            }
        },

        hideResetModal: function() {
            this.closeResetModal();
        },

        submitReset: async function() {
            if (!currentAdminData) return;
            const btn = document.getElementById('reset-submit-btn');
            if (btn) btn.disabled = true;

            try {
                const response = await window.api.patch('/admins/' + currentAdminData.id, { action: 'reset_password' });
                const payload = response ? (response.data || response) : {};

                if (response && (response.success || payload.success)) {
                    const successState = document.getElementById('reset-success-state');
                    if (successState) successState.classList.remove('hidden');
                    
                    const pwDisplay = document.getElementById('generated-password-display');
                    if (pwDisplay) pwDisplay.textContent = payload.generated_password || response.generated_password;
                    
                    const expDisplay = document.getElementById('password-expiry-display');
                    if (expDisplay) expDisplay.textContent = payload.expiry || response.expiry || '24 hours';
                    
                    const actions = document.getElementById('reset-modal-actions');
                    if (actions) actions.classList.add('hidden');
                    
                    const modalContent = document.getElementById('reset-modal-content');
                    if (modalContent) {
                        const doneBtn = document.createElement('button');
                        doneBtn.id = 'reset-done-btn';
                        doneBtn.className = 'w-full py-3 bg-primary text-text-inverse font-black rounded-xl text-xs uppercase tracking-widest mt-6 hover:bg-primary/90 transition-colors shadow-lg cursor-pointer';
                        doneBtn.textContent = 'Done';
                        doneBtn.onclick = () => {
                            this.closeResetModal();
                            doneBtn.remove();
                            this.loadAdminData();
                        };
                        modalContent.querySelector('.p-8')?.appendChild(doneBtn);
                    }

                    notify(payload.message || response.message || 'Password generated successfully', 'success');
                } else {
                    notify(payload.message || response?.message || 'Error generating password', 'error');
                }
            } catch (error) {
                notify('Error generating password', 'error');
            } finally {
                if (btn) btn.disabled = false;
            }
        },

        showDeleteModal: function() {
            if (!currentAdminData) return;
            const nameEl = document.getElementById('delete-admin-name');
            if (nameEl) nameEl.textContent = currentAdminData.full_name;
            
            const modal = document.getElementById('delete-modal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.style.overflow = 'hidden';
            }
        },

        closeDeleteModal: function() {
            const modal = document.getElementById('delete-modal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.style.overflow = '';
            }
        },

        hideDeleteModal: function() {
            this.closeDeleteModal();
        },

        submitDelete: async function() {
            if (!currentAdminData) return;
            const btn = document.getElementById('delete-submit-btn');
            if (btn) btn.disabled = true;

            try {
                const response = await window.api.patch('/admins/' + currentAdminData.id, { action: 'delete' });
                const payload = response ? (response.data || response) : {};

                if (response && (response.success || payload.success)) {
                    notify(payload.message || response.message || 'Admin permanently removed', 'success');
                    setTimeout(() => window.location.href = 'admin-profile.php', 1200);
                } else {
                    notify(payload.message || response?.message || 'Delete failed', 'error');
                    if (btn) btn.disabled = false;
                }
            } catch (error) {
                notify('Error deleting admin', 'error');
                if (btn) btn.disabled = false;
            }
        }
    };

    const initDossier = () => window.AdminDetail.init();
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDossier);
    } else {
        initDossier();
    }
})();

