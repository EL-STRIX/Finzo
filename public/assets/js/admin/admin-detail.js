/* ═══════════════════════════════════════════
   FINZO — Admin Detail Controller
   Handles fetching, managing, and rendering admin dossier data.
   ═══════════════════════════════════════════ */

(function() {
    let currentAdminId = null;
    let currentAdminData = null;
    let allActivities = [];

    function notify(msg, type = 'info') {
        if (window.portal && typeof window.portal.showToast === 'function') {
            window.portal.showToast(msg, type);
        } else if (window.utils && typeof window.utils.showToast === 'function') {
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

            // Hydrate SSR data if available
            if (window.__INITIAL_ADMIN__ && window.__INITIAL_ADMIN__.id) {
                currentAdminData = window.__INITIAL_ADMIN__;
                allActivities = window.__INITIAL_ACTIVITIES__ || [];
                this.renderUI(currentAdminData);
                this.renderActivity(allActivities);
            }

            // Fetch latest live data from API
            await this.loadAdminData();
        },

        /**
         * Clipboard utility with toast notification
         */
        copyText: function(text, label = 'Text') {
            if (!text || text === '---' || text === 'Not Provided') return;
            navigator.clipboard.writeText(text).then(() => {
                notify(`${label} copied to clipboard!`, 'success');
            }).catch(() => {
                notify('Failed to copy to clipboard', 'error');
            });
        },

        /**
         * Fetch data from API
         */
        loadAdminData: async function() {
            let attempts = 0;
            while ((!window.api || typeof window.api.get !== 'function') && attempts < 30) {
                await new Promise(r => setTimeout(r, 60));
                attempts++;
            }

            if (!window.api || typeof window.api.get !== 'function') {
                console.error('Finzo API client is unavailable.');
                return;
            }

            try {
                const response = await window.api.get(`/admins/${currentAdminId}`);
                if (!response) {
                    return;
                }

                const payload = response.data || response;
                const admin = payload.admin || response.admin;
                const activity = payload.activity || response.activity || [];

                if (admin) {
                    currentAdminData = admin;
                    this.renderUI(admin);
                    this.renderActivity(activity);
                } else if (!window.__INITIAL_ADMIN__ || !window.__INITIAL_ADMIN__.id) {
                    notify(response?.message || payload?.message || 'Error loading admin dossier', 'error');
                    setTimeout(() => window.location.href = 'admin-profile.php', 2500);
                }
            } catch (error) {
                console.error('Failed to load admin detail:', error);
            }
        },

        /**
         * Populate UI elements
         */
        renderUI: function(admin) {
            if (!admin) return;

            // 1. Header Admin Name
            const headerName = document.getElementById('header-admin-name');
            if (headerName) headerName.textContent = admin.full_name || 'Admin Dossier';

            const resetAdminName = document.getElementById('reset-admin-name');
            if (resetAdminName) resetAdminName.textContent = admin.full_name || 'Admin';

            const deleteAdminName = document.getElementById('delete-admin-name');
            if (deleteAdminName) deleteAdminName.textContent = admin.full_name || 'Admin';
            
            // 2. Status Badge Topbar & Action Button States
            const statusBox = document.getElementById('header-status-badge');
            const isActive = (admin.status || 'active') === 'active';

            if (statusBox) {
                statusBox.className = isActive
                    ? 'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20'
                    : 'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20';
                statusBox.innerHTML = `
                    <span class="w-1.5 h-1.5 rounded-full ${isActive ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500'}"></span>
                    <span>${isActive ? 'Active Account' : 'Account Suspended'}</span>
                `;
            }

            const suspendLabel = document.getElementById('suspend-label');
            const suspendDesc = document.getElementById('suspend-desc');
            const suspendIcon = document.getElementById('suspend-icon');
            const suspendIconContainer = document.getElementById('suspend-icon-container');

            if (suspendLabel) suspendLabel.textContent = isActive ? 'Suspend Administrator' : 'Activate Administrator';
            if (suspendDesc) suspendDesc.textContent = isActive ? 'Temporarily revoke all system access' : 'Restore full operational access';
            if (suspendIcon) suspendIcon.textContent = isActive ? 'block' : 'check_circle';
            if (suspendIconContainer) {
                suspendIconContainer.className = isActive
                    ? 'w-10 h-10 rounded-xl bg-amber-500/10 text-amber-500 border border-amber-500/20 flex items-center justify-center group-hover:scale-105 transition-transform'
                    : 'w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-500 border border-emerald-500/20 flex items-center justify-center group-hover:scale-105 transition-transform';
            }

            // 3. Photo & Monogram
            const initials = (admin.full_name || 'AD').split(' ').filter(Boolean).map(n => n[0]).join('').toUpperCase().substring(0, 2);
            const photoContainer = document.getElementById('admin-photo-container');
            if (photoContainer) {
                if (admin.profile_photo) {
                    const photoSrc = resolvePhotoUrl(admin.profile_photo);
                    photoContainer.innerHTML = `<img src="${photoSrc}?v=${Date.now()}" class="w-full h-full object-cover" alt="${escapeHtml(admin.full_name)}" onerror="this.outerHTML='<span id=\\\'photo-initials\\\' class=\\\'text-3xl sm:text-4xl font-black text-text-secondary\\\'>${initials}</span>'">`;
                } else {
                    photoContainer.innerHTML = `<span id="photo-initials" class="text-3xl sm:text-4xl font-black text-text-secondary">${initials}</span>`;
                }
            }

            // 4. Info Grid Fields Helper
            const setTxt = (id, val) => {
                const el = document.getElementById(id);
                if (el) el.textContent = (val !== null && val !== undefined && val !== '') ? val : '---';
            };

            setTxt('info-name', admin.full_name);
            setTxt('info-admin-id', admin.admin_id);
            setTxt('info-email', admin.email || 'Not Provided');
            setTxt('info-phone', admin.phone || 'Not Provided');
            setTxt('info-gender', admin.gender || 'Not Specified');
            
            const formatDate = (d) => {
                if (!d) return 'Not Provided';
                const dt = new Date(d);
                return isNaN(dt.getTime()) ? d : dt.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
            };

            const calculateAge = (d) => {
                if (!d) return '';
                const dt = new Date(d);
                if (isNaN(dt.getTime())) return '';
                const diff = Date.now() - dt.getTime();
                const ageDt = new Date(diff);
                const age = Math.abs(ageDt.getUTCFullYear() - 1970);
                return age > 0 ? ` (${age} yrs)` : '';
            };

            const timeAgo = (d) => {
                if (!d) return 'Never';
                if (window.FinzoUtils && window.FinzoUtils.timeAgo) return window.FinzoUtils.timeAgo(d);
                return d;
            };

            const dobFormatted = formatDate(admin.date_of_birth) + calculateAge(admin.date_of_birth);
            setTxt('info-dob', dobFormatted);
            setTxt('info-joined-date', formatDate(admin.created_at));
            setTxt('hero-created-by', admin.created_by_name || 'Root System');
            setTxt('sys-created-by', admin.created_by_name || 'Root System');
            setTxt('sys-id', admin.admin_id);

            // 5. Badges
            const isSuper = admin.admin_role === 'super_admin';
            const isAdmin = admin.admin_role === 'admin';
            const isMod = admin.admin_role === 'moderator';

            let roleFormatted = 'Administrator';
            if (isSuper) roleFormatted = 'Super Administrator';
            else if (isMod) roleFormatted = 'System Moderator';

            const infoRoleBadge = document.getElementById('info-role-badge');
            if (infoRoleBadge) {
                infoRoleBadge.textContent = roleFormatted;
                infoRoleBadge.className = isMod 
                    ? 'px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-widest bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20'
                    : (isSuper ? 'px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-widest bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20' : 'px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-widest bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20');
            }

            const isFull = (admin.access_level || 'full') === 'full';
            const infoAccessBadge = document.getElementById('info-access-badge');
            if (infoAccessBadge) {
                infoAccessBadge.textContent = isFull ? 'Full Access' : 'Restricted Access';
                infoAccessBadge.className = isFull 
                    ? 'px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-widest bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' 
                    : 'px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-widest bg-surface-subtle text-text-secondary border border-border-subtle';
            }

            // 6. System Audit Telemetry
            setTxt('hero-last-login', admin.last_login_at ? timeAgo(admin.last_login_at) : 'Never');
            setTxt('hero-last-ip', admin.last_login_ip || 'No Record');
            setTxt('sys-last-login', admin.last_login_at ? formatDate(admin.last_login_at) : 'Never');
            setTxt('sys-last-ip', admin.last_login_ip || 'No Record');
            setTxt('sys-pwd-change', admin.last_password_changed ? timeAgo(admin.last_password_changed) : 'Never');
            
            const is2Fa = intval(admin.two_fa_enabled) === 1;
            const hero2fa = document.getElementById('hero-2fa-status');
            if (hero2fa) {
                hero2fa.textContent = is2Fa ? 'Enabled & Active' : 'Disabled';
                hero2fa.className = `text-xs font-bold ${is2Fa ? 'text-emerald-500' : 'text-text-secondary'}`;
            }

            const sys2fa = document.getElementById('sys-2fa-badge');
            if (sys2fa) {
                sys2fa.textContent = is2Fa ? 'Active (TOTP)' : 'Not Configured';
                sys2fa.className = `text-xs font-bold ${is2Fa ? 'text-emerald-500' : 'text-text-secondary'}`;
            }

            const attempts = admin.failed_attempts || 0;
            const heroFailed = document.getElementById('hero-failed-attempts');
            if (heroFailed) {
                heroFailed.textContent = `${attempts} ${attempts === 1 ? 'attempt' : 'attempts'}`;
                heroFailed.className = `text-xs font-bold ${attempts > 0 ? 'text-rose-500' : 'text-emerald-500'}`;
            }

            const sysFailed = document.getElementById('sys-failed-attempts');
            if (sysFailed) {
                sysFailed.textContent = attempts;
                sysFailed.className = `text-xs font-bold ${attempts > 0 ? 'text-rose-500' : 'text-emerald-500'}`;
            }

            // 7. Security Policy Note
            const noteText = document.getElementById('security-note-text');
            if (noteText) {
                if (isMod) {
                    noteText.textContent = '"Moderators have restricted operational access. All KYC approvals and customer account reviews are permanently recorded for institutional compliance and auditing."';
                } else {
                    noteText.textContent = '"Administrators possess elevated operational privileges. Every system modification and user override is recorded in the global audit log."';
                }
            }

            // 8. Role Privileges Matrix (Granted vs Restricted)
            const grantedContainer = document.getElementById('privileges-granted');
            const restrictedContainer = document.getElementById('privileges-restricted');
            const privilegeRoleBadge = document.getElementById('privilege-role-badge');
            const levelSub = document.getElementById('privilege-level-sub');

            if (privilegeRoleBadge) {
                privilegeRoleBadge.textContent = roleFormatted;
                privilegeRoleBadge.className = isMod 
                    ? 'px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-widest bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20'
                    : (isSuper ? 'px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-widest bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20' : 'px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-widest bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20');
            }

            if (levelSub) {
                levelSub.textContent = isSuper ? 'Root Authority (Unrestricted)' : (isAdmin ? 'Operational Authority (Full)' : 'Compliance Authority (Restricted)');
            }

            let granted = [];
            let restricted = [];

            if (isSuper) {
                granted = [
                    'Root System, Configuration & Global Security Governance',
                    'Create, Provision, Suspend & Purge Administrators',
                    'Financial Core, Global Ledger & Settlement Overrides',
                    'Full Operational Powers Over Users, Staff & Banking Branches',
                    'Immutable Global Audit Trail & System Telemetry Access'
                ];
                restricted = [
                    'None (Unrestricted Root Institutional Scope)'
                ];
            } else if (isAdmin) {
                granted = [
                    'Customer Management (Approve KYC, Search, Review Profiles)',
                    'Staff Operations (Create, Update, Assign Branches & Shifts)',
                    'Branch Management (Create, Edit, Suspend Branches)',
                    'Global Transactions & Core Banking Telemetry Inspection',
                    'Institutional Audit Log & System Activity Oversight'
                ];
                restricted = [
                    'Modify or Suspend Root Super Administrators',
                    'Direct System Database & Infrastructure Reconfigurations',
                    'Execute Manual Financial Core Ledger Adjustments',
                    'Purge or Alter Immutable Audit Trail Records'
                ];
            } else {
                granted = [
                    'KYC Verification & Customer Document Approvals',
                    'Read-Only Access to User Directory & Account Profiles',
                    'Read-Only Staff & Branch Operations Directory',
                    'Inspect System Audit Logs & Operational Activity'
                ];
                restricted = [
                    'Customer Account Suspension or Permanent Deletion',
                    'Create, Modify or Terminate Bank Staff Members',
                    'Create, Edit or Suspend Banking Branches',
                    'Execute Direct Financial Transactions, Debits or Credits',
                    'Create, Modify or Manage Other Administrators',
                    'Access System Environment or Security Credentials'
                ];
            }

            if (grantedContainer) {
                grantedContainer.innerHTML = granted.map(item => `
                    <div class="flex items-start gap-2.5 p-3 rounded-2xl bg-surface-subtle border border-border-subtle hover:border-emerald-500/30 transition-all">
                        <span class="material-symbols-outlined text-emerald-500 text-[18px] shrink-0 mt-0.5" aria-hidden="true">check_circle</span>
                        <div class="text-xs font-bold text-text-primary leading-snug">${escapeHtml(item)}</div>
                    </div>
                `).join('');
            }

            if (restrictedContainer) {
                restrictedContainer.innerHTML = restricted.map(item => `
                    <div class="flex items-start gap-2.5 p-3 rounded-2xl bg-surface-subtle border border-border-subtle opacity-85">
                        <span class="material-symbols-outlined text-rose-500 text-[18px] shrink-0 mt-0.5" aria-hidden="true">${isSuper ? 'lock_open' : 'block'}</span>
                        <div class="text-xs font-bold text-text-secondary leading-snug">${escapeHtml(item)}</div>
                    </div>
                `).join('');
            }

            // 9. Audit Log Links
            const auditLink = document.getElementById('view-audit-link');
            if (auditLink) {
                auditLink.href = `admin-audit-log.php?search=${encodeURIComponent(admin.admin_id || '')}`;
            }
            const topbarAudit = document.getElementById('topbar-audit-link');
            if (topbarAudit) {
                topbarAudit.href = `admin-audit-log.php?search=${encodeURIComponent(admin.admin_id || '')}`;
            }
        },

        /**
         * Render Activity Table
         */
        renderActivity: function(activities) {
            allActivities = activities || [];
            
            const heroCount = document.getElementById('hero-activity-count');
            if (heroCount) heroCount.textContent = `${allActivities.length} Actions Logged`;

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
                countLabel.textContent = `${filtered.length} entries${query ? ' (filtered)' : ''}`;
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

            tbody.innerHTML = filtered.map(act => {
                const actType = act.action_type || act.action || 'system_event';
                let badgeClass = 'bg-primary/10 text-primary border border-primary/20';
                
                if (actType.includes('login')) {
                    badgeClass = 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20';
                } else if (actType.includes('kyc')) {
                    badgeClass = 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20';
                } else if (actType.includes('password') || actType.includes('security')) {
                    badgeClass = 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20';
                } else if (actType.includes('delete') || actType.includes('suspend')) {
                    badgeClass = 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20';
                }

                return `
                    <tr class="hover:bg-surface-subtle transition-colors border-b border-border-subtle last:border-0">
                        <td class="py-4 px-6 text-[11px] font-bold text-text-secondary whitespace-nowrap">${formatDT(act.created_at)}</td>
                        <td class="py-4 px-6 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest ${badgeClass}">${escapeHtml(actType.replace(/_/g, ' '))}</span>
                        </td>
                        <td class="py-4 px-6 text-[11px] font-medium text-text-primary max-w-md">${escapeHtml(act.details || '-')}</td>
                        <td class="py-4 px-6 text-right text-[10px] font-mono font-bold text-text-secondary whitespace-nowrap">${escapeHtml(act.ip_address || '127.0.0.1')}</td>
                    </tr>
                `;
            }).join('');
        },

        /**
         * Administrative Actions: Toggle Status
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
                    await this.loadAdminData();
                } else {
                    notify(payload.message || response?.message || 'Failed to update status', 'error');
                }
            } catch (err) {
                notify('Failed to update status', 'error');
            }
        },

        /**
         * Reset Password Modal Handlers
         */
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
                        notify('Temporary password copied to clipboard!', 'success');
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

        /**
         * Delete Account Modal Handlers
         */
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
