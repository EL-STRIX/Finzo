/* ═══════════════════════════════════════════
   FINZO — Admin Detail Controller (Streamlined)
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

            // Fetch live data from API
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
                if (!response) return;

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

            // 1. Names
            const infoName = document.getElementById('info-name');
            if (infoName) infoName.textContent = admin.full_name || 'Admin Dossier';

            const resetAdminName = document.getElementById('reset-admin-name');
            if (resetAdminName) resetAdminName.textContent = admin.full_name || 'Admin';

            const deleteAdminName = document.getElementById('delete-admin-name');
            if (deleteAdminName) deleteAdminName.textContent = admin.full_name || 'Admin';
            
            // 2. Status Badge & Action Button
            const statusBox = document.getElementById('header-status-badge');
            const isActive = (admin.status || 'active') === 'active';

            if (statusBox) {
                statusBox.className = isActive
                    ? 'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-widest bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20'
                    : 'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-widest bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20';
                statusBox.innerHTML = `
                    <span class="w-1.5 h-1.5 rounded-full ${isActive ? 'bg-emerald-500' : 'bg-rose-500'}"></span>
                    <span>${isActive ? 'Active' : 'Suspended'}</span>
                `;
            }

            const suspendBtn = document.getElementById('action-suspend-btn');
            const suspendLabel = document.getElementById('suspend-label');
            const suspendIcon = document.getElementById('suspend-icon');

            if (suspendLabel) suspendLabel.textContent = isActive ? 'Suspend' : 'Activate';
            if (suspendIcon) suspendIcon.textContent = isActive ? 'block' : 'check_circle';
            if (suspendBtn) {
                suspendBtn.className = isActive
                    ? 'px-3.5 py-2 rounded-xl text-xs font-black uppercase tracking-wider flex items-center gap-1.5 transition-all cursor-pointer shadow-sm bg-amber-500/10 hover:bg-amber-500 text-amber-600 hover:text-white border border-amber-500/20'
                    : 'px-3.5 py-2 rounded-xl text-xs font-black uppercase tracking-wider flex items-center gap-1.5 transition-all cursor-pointer shadow-sm bg-emerald-500/10 hover:bg-emerald-600 text-emerald-600 hover:text-white border border-emerald-500/20';
            }

            // 3. Photo & Monogram
            const initials = (admin.full_name || 'AD').split(' ').filter(Boolean).map(n => n[0]).join('').toUpperCase().substring(0, 2);
            const photoContainer = document.getElementById('admin-photo-container');
            if (photoContainer) {
                if (admin.profile_photo) {
                    const photoSrc = resolvePhotoUrl(admin.profile_photo);
                    photoContainer.innerHTML = `<img src="${photoSrc}?v=${Date.now()}" class="w-full h-full object-cover" alt="${escapeHtml(admin.full_name)}" onerror="this.outerHTML='<span id=\\\'photo-initials\\\' class=\\\'text-2xl sm:text-3xl font-black text-text-secondary\\\'>${initials}</span>'">`;
                } else {
                    photoContainer.innerHTML = `<span id="photo-initials" class="text-2xl sm:text-3xl font-black text-text-secondary">${initials}</span>`;
                }
            }

            // 4. Info Fields
            const setTxt = (id, val) => {
                const el = document.getElementById(id);
                if (el) el.textContent = (val !== null && val !== undefined && val !== '') ? val : '---';
            };

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
            setTxt('hero-created-by', admin.created_by_name || 'System');

            // 5. Role & Badges
            const isSuper = admin.admin_role === 'super_admin';
            const isAdmin = admin.admin_role === 'admin';
            const isMod = admin.admin_role === 'moderator';

            let roleFormatted = 'Admin';
            if (isSuper) roleFormatted = 'Super Admin';
            else if (isMod) roleFormatted = 'Moderator';

            const infoRoleBadge = document.getElementById('info-role-badge');
            if (infoRoleBadge) {
                infoRoleBadge.textContent = roleFormatted;
                infoRoleBadge.className = isMod 
                    ? 'px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-widest bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20'
                    : (isSuper ? 'px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-widest bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20' : 'px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-widest bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20');
            }

            const isFull = (admin.access_level || 'full') === 'full';
            const infoAccessBadge = document.getElementById('info-access-badge');
            if (infoAccessBadge) {
                infoAccessBadge.textContent = isFull ? 'Full Access' : 'Restricted';
                infoAccessBadge.className = isFull 
                    ? 'px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-widest bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' 
                    : 'px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-widest bg-surface-subtle text-text-secondary border border-border-subtle';
            }

            // 6. Security Telemetry
            setTxt('sys-last-login', admin.last_login_at ? `${formatDate(admin.last_login_at)} (${timeAgo(admin.last_login_at)})` : 'Never');
            setTxt('sys-last-ip', `IP: ${admin.last_login_ip || 'No Record'}`);
            setTxt('sys-pwd-change', admin.last_password_changed ? timeAgo(admin.last_password_changed) : 'Never');
            
            const is2Fa = parseInt(admin.two_fa_enabled) === 1;
            const sys2fa = document.getElementById('sys-2fa-badge');
            if (sys2fa) {
                sys2fa.textContent = is2Fa ? 'Enabled' : 'Disabled';
                sys2fa.className = `font-bold ${is2Fa ? 'text-emerald-500' : 'text-text-secondary'}`;
            }

            const attempts = admin.failed_attempts || 0;
            const sysFailed = document.getElementById('sys-failed-attempts');
            if (sysFailed) {
                sysFailed.textContent = `${attempts} ${attempts === 1 ? 'attempt' : 'attempts'}`;
                sysFailed.className = `font-bold ${attempts > 0 ? 'text-rose-500' : 'text-emerald-500'}`;
            }

            // 7. Role Scope Tags
            const scopeContainer = document.getElementById('role-scope-tags');
            if (scopeContainer) {
                if (isMod) {
                    scopeContainer.innerHTML = `
                        <span class="px-2 py-0.5 rounded-md bg-surface-subtle border border-border-subtle text-[10px] font-bold text-text-primary">KYC Approvals</span>
                        <span class="px-2 py-0.5 rounded-md bg-surface-subtle border border-border-subtle text-[10px] font-bold text-text-primary">User Reviews</span>
                        <span class="px-2 py-0.5 rounded-md bg-surface-subtle border border-border-subtle text-[10px] font-bold text-text-primary">Read-Only Operations</span>
                        <span class="px-2 py-0.5 rounded-md bg-surface-subtle border border-border-subtle text-[10px] font-bold text-text-primary">Audit Log Inspection</span>
                    `;
                } else {
                    scopeContainer.innerHTML = `
                        <span class="px-2 py-0.5 rounded-md bg-surface-subtle border border-border-subtle text-[10px] font-bold text-text-primary">User Management</span>
                        <span class="px-2 py-0.5 rounded-md bg-surface-subtle border border-border-subtle text-[10px] font-bold text-text-primary">Staff Operations</span>
                        <span class="px-2 py-0.5 rounded-md bg-surface-subtle border border-border-subtle text-[10px] font-bold text-text-primary">Branch Controls</span>
                        <span class="px-2 py-0.5 rounded-md bg-surface-subtle border border-border-subtle text-[10px] font-bold text-text-primary">Global Transactions</span>
                    `;
                }
            }

            // 8. Audit Log Links
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
                                    <div class="w-10 h-10 rounded-xl bg-surface-subtle border border-border-subtle flex items-center justify-center mb-2.5 text-text-secondary">
                                        <span class="material-symbols-outlined text-xl">history_toggle_off</span>
                                    </div>
                                    <div class="text-xs font-black text-text-primary uppercase tracking-wider mb-0.5">No Activity Recorded</div>
                                    <div class="text-[11px] font-medium text-text-secondary">No operational actions have been initiated by this account yet.</div>
                                </div>
                            </td>
                        </tr>
                    `;
                } else {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="4" class="py-8 text-center text-xs font-bold text-text-secondary">
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
                    <tr class="hover:bg-surface-subtle transition-colors border-b border-border-subtle last:border-0 text-xs">
                        <td class="py-3.5 px-5 font-bold text-text-secondary whitespace-nowrap">${formatDT(act.created_at)}</td>
                        <td class="py-3.5 px-5 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-widest ${badgeClass}">${escapeHtml(actType.replace(/_/g, ' '))}</span>
                        </td>
                        <td class="py-3.5 px-5 font-medium text-text-primary max-w-md">${escapeHtml(act.details || '-')}</td>
                        <td class="py-3.5 px-5 text-right font-mono font-bold text-text-secondary whitespace-nowrap">${escapeHtml(act.ip_address || '127.0.0.1')}</td>
                    </tr>
                `;
            }).join('');
        },

        /**
         * Toggle Status
         */
        toggleStatus: async function() {
            if (!currentAdminData) return;
            const isActive = currentAdminData.status === 'active';
            const confirmMsg = isActive 
                ? `Are you sure you want to SUSPEND ${currentAdminData.full_name}? They will lose access immediately.`
                : `Are you sure you want to ACTIVATE ${currentAdminData.full_name}?`;

            if (!confirm(confirmMsg)) return;

            try {
                const response = await window.api.patch('/admins/' + currentAdminData.id, { action: 'toggle_status' });
                const payload = response ? (response.data || response) : {};

                if (response && (response.success || payload.success)) {
                    notify(payload.message || response.message || 'Status updated successfully', 'success');
                    await this.loadAdminData();
                } else {
                    notify(payload.message || response?.message || 'Failed to update status', 'error');
                }
            } catch (err) {
                notify('Failed to update status', 'error');
            }
        },

        /**
         * Reset Password Modal
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
                        doneBtn.className = 'w-full py-2.5 bg-primary text-text-inverse font-black rounded-xl text-xs uppercase tracking-widest mt-5 hover:bg-primary/90 transition-colors shadow-sm cursor-pointer';
                        doneBtn.textContent = 'Done';
                        doneBtn.onclick = () => {
                            this.closeResetModal();
                            doneBtn.remove();
                            this.loadAdminData();
                        };
                        modalContent.querySelector('.p-6, .p-8')?.appendChild(doneBtn);
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
         * Delete Account Modal
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
