/* ═══════════════════════════════════════════
   FINZO — Admin Detail Controller
   Handles fetching and managing specific admin data.
   ═══════════════════════════════════════════ */

(function() {
    let currentAdminId = null;
    let currentAdminData = null;
    let allActivities = [];

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
            // Guarantee window.api is initialized
            let attempts = 0;
            while ((!window.api || typeof window.api.get !== 'function') && attempts < 25) {
                await new Promise(r => setTimeout(r, 80));
                attempts++;
            }

            if (!window.api || typeof window.api.get !== 'function') {
                console.error('API module unavailable');
                return;
            }

            try {
                const response = await window.api.get(`/admins/${currentAdminId}`);
                if (!response) {
                    notify('No response from server', 'error');
                    return;
                }

                const payload = response.data || response;
                const admin = payload.admin || response.admin;
                const activity = payload.activity || response.activity || [];

                if (admin) {
                    currentAdminData = admin;
                    this.renderUI(admin);
                    this.renderActivity(activity);
                } else {
                    notify(response?.message || payload?.message || 'Error loading admin dossier', 'error');
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

            // Role Privileges Matrix (Granted vs Restricted)
            const grantedContainer = document.getElementById('privileges-granted');
            const restrictedContainer = document.getElementById('privileges-restricted');
            const roleBadge = document.getElementById('privilege-role-badge');
            const levelSub = document.getElementById('privilege-level-sub');

            const isSuper = admin.admin_role === 'super_admin';
            const isAdmin = admin.admin_role === 'admin';
            const isMod = admin.admin_role === 'moderator';

            if (roleBadge) {
                if (isSuper) {
                    roleBadge.textContent = 'Super Admin';
                    roleBadge.className = 'px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest bg-purple-500/10 text-purple-600 border border-purple-500/20';
                } else if (isAdmin) {
                    roleBadge.textContent = 'Administrator';
                    roleBadge.className = 'px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest bg-teal-500/10 text-teal-600 border border-teal-500/20';
                } else {
                    roleBadge.textContent = 'Moderator';
                    roleBadge.className = 'px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest bg-amber-500/10 text-amber-600 border border-amber-500/20';
                }
            }

            if (levelSub) {
                levelSub.textContent = isSuper ? 'Full Root Authority' : (isAdmin ? 'Operational Authority (Full)' : 'Compliance Authority (Restricted)');
            }

            let granted = [];
            let restricted = [];

            if (isSuper) {
                granted = [
                    'Root System, Configuration & Security Oversight',
                    'Create, Suspend & Manage All Administrators',
                    'Financial Core, Global Ledger & Settlement Overrides',
                    'Full User, Staff & Branch Administrative Powers',
                    'Immutable Global Audit Trail & Compliance Access'
                ];
                restricted = [
                    'None (Unrestricted Root Administrative Scope)'
                ];
            } else if (isAdmin) {
                granted = [
                    'User Management (Approve, Search, Review Profiles)',
                    'Staff Operations (Create, Update, Assign Branches)',
                    'Branch Management (Create, Edit, Suspend Branches)',
                    'Global Transactions & Core Banking Telemetry',
                    'Full System Audit Log Inspection'
                ];
                restricted = [
                    'Modify or Suspend Root Super Administrators',
                    'Direct System Database & Environment Reconfigurations',
                    'Execute Manual Financial Core Ledger Adjustments',
                    'Delete or Purge Immutable Audit Logs'
                ];
            } else {
                // Moderator
                granted = [
                    'KYC Verification & Customer Document Approvals',
                    'Read-Only Access to User Directory & Profiles',
                    'Read-Only Staff & Branch Operations Directory',
                    'Inspect System Audit Logs & Operational Activity'
                ];
                restricted = [
                    'Customer Account Suspension or Permanent Deletion',
                    'Create, Modify or Terminate Bank Staff Members',
                    'Create, Edit or Suspend Banking Branches',
                    'Execute Financial Transactions, Debits or Credits',
                    'Create, Modify or Manage Other Administrators',
                    'Access System Environment or Security Credentials'
                ];
            }

            if (grantedContainer) {
                grantedContainer.innerHTML = granted.map(item => `
                    <div class="flex items-start gap-2.5 p-2.5 rounded-xl bg-surface-subtle border border-border-subtle">
                        <span class="material-symbols-outlined text-emerald-500 text-[16px] shrink-0 mt-0.5" aria-hidden="true">check_circle</span>
                        <div class="text-[11px] font-bold text-text-primary leading-snug">${escapeHtml(item)}</div>
                    </div>
                `).join('');
            }

            if (restrictedContainer) {
                restrictedContainer.innerHTML = restricted.map(item => `
                    <div class="flex items-start gap-2.5 p-2.5 rounded-xl bg-surface-subtle border border-border-subtle opacity-75">
                        <span class="material-symbols-outlined text-rose-500 text-[16px] shrink-0 mt-0.5" aria-hidden="true">${isSuper ? 'lock_open' : 'block'}</span>
                        <div class="text-[11px] font-bold text-text-secondary leading-snug">${escapeHtml(item)}</div>
                    </div>
                `).join('');
            }

            // View in Global Audit Log link
            const auditLink = document.getElementById('view-audit-link');
            if (auditLink) {
                auditLink.href = `admin-audit-log.php?search=${encodeURIComponent(admin.admin_id)}`;
            }
        },


        /**
         * Render Activity Table
         */
        renderActivity: function(activities) {
            allActivities = activities || [];
            this.filterActivity();
        },

        filterActivity: function() {
            const query = (document.getElementById('activity-search-input')?.value || '').toLowerCase().trim();
            const tbody = document.getElementById('activity-tbody');
            const countLabel = document.getElementById('activity-count-label');
            if (!tbody) return;

            const filtered = allActivities.filter(act => {
                if (!query) return true;
                const actType = (act.action_type || act.action || '').toLowerCase();
                const details = (act.details || '').toLowerCase();
                const ip = (act.ip_address || '').toLowerCase();
                return actType.includes(query) || details.includes(query) || ip.includes(query);
            });

            if (countLabel) {
                countLabel.textContent = `${filtered.length} total entries${query ? ' (filtered)' : ''}`;
            }

            if (filtered.length === 0) {
                if (allActivities.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="4" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                    <div class="w-12 h-12 rounded-2xl bg-surface-subtle border border-border-subtle flex items-center justify-center mb-3 text-text-secondary">
                                        <span class="material-symbols-outlined text-2xl">history_toggle_off</span>
                                    </div>
                                    <div class="text-xs font-black text-text-primary uppercase tracking-wider mb-1">No Activity Logged Yet</div>
                                    <div class="text-[11px] font-medium text-text-secondary">This administrator has not initiated any recorded actions or operations yet.</div>
                                </div>
                            </td>
                        </tr>
                    `;
                } else {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="4" class="py-10 text-center text-xs font-bold text-text-secondary">
                                No activity records match "${escapeHtml(query)}"
                            </td>
                        </tr>
                    `;
                }
                return;
            }

            const formatDT = (d) => {
                if (!d) return '-';
                const dt = new Date(d);
                return isNaN(dt.getTime()) ? d : dt.toLocaleString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
            };

            tbody.innerHTML = filtered.map(act => `
                <tr class="hover:bg-surface-subtle transition-colors border-b border-border-subtle last:border-0">
                    <td class="py-4 px-6 text-[11px] font-bold text-text-secondary whitespace-nowrap">${formatDT(act.created_at)}</td>
                    <td class="py-4 px-6 whitespace-nowrap">
                        <span class="px-2.5 py-1 rounded-lg bg-surface border border-border-subtle text-[9px] font-black uppercase tracking-widest text-text-primary">${escapeHtml((act.action_type || act.action || '').replace(/_/g, ' '))}</span>
                    </td>
                    <td class="py-4 px-6 text-[11px] font-medium text-text-primary">${escapeHtml(act.details || '-')}</td>
                    <td class="py-4 px-6 text-right text-[10px] font-mono font-bold text-text-secondary whitespace-nowrap">${escapeHtml(act.ip_address || '127.0.0.1')}</td>
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

