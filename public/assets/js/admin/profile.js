/**
 * Finzo Admin — Executive Profile & Access Management Handler
 * Version 2.0 — Executive Dashboard, Responsive Tab System, Real-time 2FA, Dynamic Audit & Directory Controls
 */

(function() {
    let currentAdminRole = null;
    let allActivityLogs = [];
    let activityData = [];
    let currentActivityPage = 1;
    const ACTIVITY_PER_PAGE = 8;
    let allAdmins = [];
    let pendingUnlockAdminId = null;
    let pendingUnlockName = '';


    /**
     * Load Admin profile data from API
     */
    async function loadAdminProfile() {
        try {
            const response = await window.api.get('/admins/profile');
            if (response && (response.success || response.status === 200 || response.data)) {
                const payload = response.data || response;
                const admin = payload.profile || response.profile || payload;
                if (!admin || !admin.full_name) {
                    console.error('Invalid profile data payload:', response);
                    return;
                }
                currentAdminRole = admin.admin_role;

                // Sync Header & Hero Profile
                const nameDisplay = document.getElementById('profile-name-display');
                if (nameDisplay) nameDisplay.textContent = admin.full_name;

                const heroAdminId = document.getElementById('hero-admin-id-text');
                if (heroAdminId) heroAdminId.textContent = 'ID: ' + (admin.admin_id || '#' + admin.id);

                const heroEmail = document.getElementById('hero-email-text');
                if (heroEmail) heroEmail.textContent = admin.email || 'admin@finzo.com';

                const headerName = document.getElementById('header-profile-name');
                if (headerName) headerName.textContent = admin.full_name;
                const headerId = document.getElementById('header-profile-id');
                if (headerId) headerId.textContent = 'ID: ' + (admin.admin_id || '#' + admin.id);

                // Form bindings
                const adminIdInput = document.getElementById('profile-admin-id-input');
                if (adminIdInput) adminIdInput.value = admin.admin_id || '---';

                const nameInput = document.getElementById('profile-name-input');
                if (nameInput) nameInput.value = admin.full_name;

                const dobInput = document.getElementById('profile-dob-input');
                if (dobInput) dobInput.value = admin.date_of_birth || '';

                // Gender Dropdown sync
                const genderVal = admin.gender || '';
                const genderInput = document.getElementById('profile-gender-input');
                if (genderInput) {
                    genderInput.value = genderVal;
                    const genderDropdown = genderInput.closest('.finzo-dropdown');
                    if (genderDropdown && genderVal) {
                        const label = genderDropdown.querySelector('.dropdown-label');
                        if (label) label.textContent = genderVal;
                        genderDropdown.querySelectorAll('.finzo-dropdown-option').forEach(opt => {
                            opt.classList.toggle('is-selected', opt.dataset.value === genderVal);
                        });
                    }
                }

                const phoneInput = document.getElementById('profile-phone-input');
                if (phoneInput) phoneInput.value = admin.phone || '';

                const emailInput = document.getElementById('profile-email-input');
                if (emailInput) emailInput.value = admin.email;

                // Role Meta Labels & Badges
                const roleBadge = document.getElementById('badge-role');
                const metaRoleLabel = document.getElementById('meta-role-label');
                const secRoleScope = document.getElementById('sec-role-scope');
                
                let roleFormatted = 'Administrator';
                if (admin.admin_role === 'super_admin') {
                    roleFormatted = 'Super Administrator';
                    if (roleBadge) {
                        roleBadge.textContent = 'Super Admin';
                        roleBadge.className = 'px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-widest bg-primary-base text-white shadow-sm';
                    }
                } else if (admin.admin_role === 'admin') {
                    roleFormatted = 'System Administrator';
                    if (roleBadge) {
                        roleBadge.textContent = 'Admin';
                        roleBadge.className = 'px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-widest bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20';
                    }
                } else {
                    roleFormatted = 'System Moderator';
                    if (roleBadge) {
                        roleBadge.textContent = 'Moderator';
                        roleBadge.className = 'px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-widest bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20';
                    }
                }
                if (metaRoleLabel) metaRoleLabel.textContent = roleFormatted;
                if (secRoleScope) secRoleScope.textContent = roleFormatted;

                // Access Level Badge
                const accessBadge = document.getElementById('badge-access');
                if (accessBadge) {
                    accessBadge.textContent = (admin.access_level || 'full').toUpperCase() + ' ACCESS';
                    accessBadge.className = admin.access_level === 'full'
                        ? 'px-3 py-1 rounded-lg bg-info-bg text-info border border-info-border text-[10px] font-black uppercase tracking-widest'
                        : 'px-3 py-1 rounded-lg bg-surface-subtle text-text-secondary border border-border-subtle text-[10px] font-black uppercase tracking-widest';
                }

                // Account Status Badge
                const statusBadge = document.getElementById('badge-status');
                if (statusBadge) {
                    const isActive = (admin.status || 'active') === 'active';
                    statusBadge.innerHTML = `<span class="w-1.5 h-1.5 rounded-full ${isActive ? 'bg-emerald-500' : 'bg-rose-500'}"></span> ${(admin.status || 'active').toUpperCase()}`;
                    statusBadge.className = isActive
                        ? 'px-3 py-1 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-[10px] font-black uppercase tracking-widest flex items-center gap-1.5'
                        : 'px-3 py-1 rounded-lg bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20 text-[10px] font-black uppercase tracking-widest flex items-center gap-1.5';
                }

                // Avatar / Photo handling
                const initials = (admin.full_name || 'AD').split(' ').filter(Boolean).map(n => n[0]).join('').toUpperCase().substring(0, 2);
                const avatarImg = document.getElementById('avatar-img-preview');
                const avatarInitials = document.getElementById('avatar-initials');
                if (admin.profile_photo) {
                    let photoSrc = admin.profile_photo;
                    if (!photoSrc.startsWith('http')) {
                        if (photoSrc.startsWith('/Finzo/public/')) {
                            // full
                        } else if (photoSrc.startsWith('/uploads/') || photoSrc.startsWith('uploads/')) {
                            photoSrc = '/Finzo/public/' + photoSrc.replace(/^\/+/, '');
                        } else if (photoSrc.startsWith('/assets/') || photoSrc.startsWith('assets/')) {
                            photoSrc = '/Finzo/public/' + photoSrc.replace(/^\/+/, '');
                        } else {
                            photoSrc = '/Finzo/public/uploads/' + photoSrc.replace(/^\/+/, '');
                        }
                    }
                    if (avatarImg) {
                        avatarImg.src = photoSrc + '?v=' + Date.now();
                        avatarImg.classList.remove('hidden');
                    }
                    if (avatarInitials) avatarInitials.classList.add('hidden');
                } else {
                    if (avatarImg) avatarImg.classList.add('hidden');
                    if (avatarInitials) {
                        avatarInitials.textContent = initials;
                        avatarInitials.classList.remove('hidden');
                    }
                }


                // Security Card Telemetry
                const secLogin = document.getElementById('sec-last-login');
                if (secLogin) secLogin.textContent = admin.last_login_at ? (window.FinzoUtils ? window.FinzoUtils.timeAgo(admin.last_login_at) : admin.last_login_at) : 'Active Now';
                const secIp = document.getElementById('sec-last-ip');
                if (secIp) secIp.textContent = admin.last_login_ip || '127.0.0.1';
                const secPwd = document.getElementById('sec-last-pwd');
                if (secPwd) secPwd.textContent = admin.last_password_changed ? (window.FinzoUtils ? window.FinzoUtils.timeAgo(admin.last_password_changed) : admin.last_password_changed) : 'Recently';

                // 2FA Toggle Sync
                const twoFaToggle = document.getElementById('sec-2fa-toggle');
                const twoFaLabel = document.getElementById('sec-2fa-label');
                const is2FaEnabled = admin.two_fa_enabled == 1;
                if (twoFaToggle) twoFaToggle.checked = is2FaEnabled;
                if (twoFaLabel) {
                    twoFaLabel.textContent = is2FaEnabled ? 'ENABLED (ACTIVE)' : 'DISABLED';
                    twoFaLabel.className = is2FaEnabled
                        ? 'text-[10px] font-mono font-black uppercase tracking-widest px-2.5 py-1 rounded bg-emerald-500/10 text-emerald-600 border border-emerald-500/20'
                        : 'text-[10px] font-mono font-black uppercase tracking-widest px-2.5 py-1 rounded bg-surface border border-border-subtle text-text-secondary';
                }

                // Security Question Status
                const sqContainer = document.getElementById('sq-status-container');
                const badgeSqLabel = document.getElementById('badge-sq-label');
                const heroSecScore = document.getElementById('hero-security-score');
                const sqSet = admin.security_question_set == 1;

                if (badgeSqLabel) {
                    badgeSqLabel.textContent = sqSet ? 'Recovery: Active ✓' : 'Recovery: Action Required ⚠️';
                }

                let secScore = 80;
                if (sqSet) secScore += 10;
                if (is2FaEnabled) secScore += 10;
                if (heroSecScore) heroSecScore.textContent = `${secScore}%`;

                if (sqContainer) {
                    if (sqSet) {
                        sqContainer.innerHTML = `
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400">
                                <span class="material-symbols-outlined text-[14px]" aria-hidden="true">check_circle</span>
                                <span class="text-[10px] font-black uppercase tracking-widest">Configured ✓</span>
                            </div>`;
                        const sqQField = document.getElementById('sq-question');
                        if (sqQField && admin.security_question) sqQField.value = admin.security_question;
                        const sqBtnText = document.getElementById('sq-btn-text');
                        if (sqBtnText) sqBtnText.textContent = 'Update Security Question';
                    } else {
                        sqContainer.innerHTML = `
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400">
                                <span class="material-symbols-outlined text-[14px]" aria-hidden="true">warning</span>
                                <span class="text-[10px] font-black uppercase tracking-widest">Action Recommended</span>
                            </div>`;
                    }
                }

                // Activity Trail
                const activityList = payload.activity || response.activity || [];
                allActivityLogs = Array.isArray(activityList) ? activityList : [];
                activityData = [...allActivityLogs];
                currentActivityPage = 1;
                
                const countBadge = document.getElementById('activity-count-badge');
                if (countBadge) countBadge.textContent = `${allActivityLogs.length} Logs`;
                
                renderActivityPage();


                // Super Admin Exclusive Privileges
                if (admin.admin_role === 'super_admin') {
                    const tabBtnDir = document.getElementById('tab-btn-directory');
                    if (tabBtnDir) {
                        tabBtnDir.classList.remove('hidden');
                        tabBtnDir.classList.add('flex');
                    }
                    loadAdminList();
                    loadLockedAdmins();
                }

            } else {
                if (window.portal) window.portal.showToast(response?.message || 'Failed to load profile', 'error');
            }
        } catch (error) {
            console.error('Failed to load profile:', error);
            if (window.portal) window.portal.showToast('Network error loading profile', 'error');
        }
    }

    /**
     * Render paginated Activity Log
     */
    function renderActivityPage() {
        const tbody = document.getElementById('activity-table-body');
        const paginationContainer = document.getElementById('activity-pagination-container');
        const paginationInfo = document.getElementById('activity-pagination-info');
        const prevBtn = document.getElementById('activity-prev-btn');
        const nextBtn = document.getElementById('activity-next-btn');

        if (!tbody) return;

        if (activityData.length === 0) {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center italic text-text-secondary py-12 text-sm">No activity logs recorded yet.</td></tr>`;
            if (paginationContainer) paginationContainer.classList.add('hidden');
            return;
        }

        if (paginationContainer) paginationContainer.classList.remove('hidden');

        const totalPages = Math.ceil(activityData.length / ACTIVITY_PER_PAGE);
        const startIndex = (currentActivityPage - 1) * ACTIVITY_PER_PAGE;
        const endIndex = Math.min(startIndex + ACTIVITY_PER_PAGE, activityData.length);
        const currentSlice = activityData.slice(startIndex, endIndex);

        if (paginationInfo) {
            paginationInfo.textContent = `Showing ${startIndex + 1} to ${endIndex} of ${activityData.length} entries`;
        }

        if (prevBtn) prevBtn.disabled = currentActivityPage === 1;
        if (nextBtn) nextBtn.disabled = currentActivityPage >= totalPages;

        const formatDT = (d) => {
            if (!d) return '-';
            return new Date(d).toLocaleString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
        };

        tbody.innerHTML = currentSlice.map(a => {
            const actionText = a.action || 'system_event';
            let icon = 'history';
            let badgeStyle = 'bg-primary/10 text-primary border border-primary/20';

            if (actionText.includes('login')) {
                icon = 'login';
                badgeStyle = 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20';
            } else if (actionText.includes('password') || actionText.includes('security')) {
                icon = 'key';
                badgeStyle = 'bg-amber-500/10 text-amber-600 border border-amber-500/20';
            } else if (actionText.includes('provision') || actionText.includes('admin')) {
                icon = 'manage_accounts';
                badgeStyle = 'bg-info/10 text-info border border-info-border';
            }

            return `
                <tr class="hover:bg-surface-subtle transition-colors">
                    <td class="py-4 px-6">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider ${badgeStyle}">
                            <span class="material-symbols-outlined text-[14px]" aria-hidden="true">${icon}</span>
                            ${actionText.replace(/_/g, ' ')}
                        </span>
                    </td>
                    <td class="py-4 px-6 text-xs text-text-primary font-medium max-w-md truncate">${a.details || 'Administrative system operation'}</td>
                    <td class="py-4 px-6 text-xs font-mono font-bold text-text-secondary">${a.ip_address || '127.0.0.1'}</td>
                    <td class="py-4 px-6 text-right text-xs font-medium text-text-secondary">${formatDT(a.created_at)}</td>
                </tr>
            `;
        }).join('');
    }

    /**
     * Load Locked Admin Accounts
     */
    async function loadLockedAdmins() {
        try {
            const res = await window.api.get('/admins/locked');
            if (!res || (!res.success && !res.data)) {
                document.getElementById('locked-admins-container')?.classList.add('hidden');
                return;
            }
            const payload = res.data || res;
            const lockedList = payload.locked_admins || payload.locked || (Array.isArray(payload) ? payload : []);
            const total = payload.total ?? lockedList.length;

            if (total === 0 || lockedList.length === 0) {
                document.getElementById('locked-admins-container')?.classList.add('hidden');
                return;
            }

            document.getElementById('locked-admins-container')?.classList.remove('hidden');
            const badge = document.getElementById('locked-count-badge');
            if (badge) badge.textContent = `${total} Locked`;

            const formatDT = (d) => {
                if (!d) return '-';
                return new Date(d).toLocaleString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
            };

            const tbody = document.getElementById('locked-admins-body');
            if (tbody) {
                tbody.innerHTML = lockedList.map(a => {
                    const lockReasonLabel = a.lock_reason === 'failed_password'
                        ? `<span class="px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-widest bg-rose-500/10 text-rose-600 border border-rose-500/20">Wrong Password (3x)</span>`
                        : `<span class="px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-widest bg-amber-500/10 text-amber-600 border border-amber-500/20">Wrong Answer (5x)</span>`;

                    const attemptsColor = a.answer_attempts >= 3 ? 'text-rose-600 font-bold' : 'text-text-secondary';
                    const sqHtml = a.security_question_set
                        ? `<span class="text-emerald-600 text-xs font-bold">Yes ✓</span>`
                        : `<span class="text-rose-500 text-xs font-bold">No ✕</span>`;

                    return `<tr class="hover:bg-rose-500/5 transition-colors border-b border-border-subtle last:border-0">
                        <td class="py-4 px-6">
                            <div class="text-xs font-black text-text-primary tracking-tight">${a.full_name}</div>
                            <div class="text-[10px] font-bold text-text-secondary mt-0.5">${a.email}</div>
                        </td>
                        <td class="py-4 px-4"><span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest bg-teal-500/10 text-teal-600 border border-teal-500/20">${a.admin_role}</span></td>
                        <td class="py-4 px-4 text-xs text-text-secondary font-medium">${formatDT(a.locked_at)}</td>
                        <td class="py-4 px-4">${lockReasonLabel}</td>
                        <td class="py-4 px-4 text-xs font-mono font-bold ${attemptsColor}">${a.answer_attempts} / 5</td>
                        <td class="py-4 px-4">${sqHtml}</td>
                        <td class="py-4 px-4 text-right">
                            <button onclick="window.AdminProfile.showUnlockConfirm(${a.id}, '${(a.full_name || '').replace(/'/g, "\\'")}')"
                                class="px-3.5 py-1.5 bg-emerald-500/10 hover:bg-emerald-600 text-emerald-600 hover:text-white border border-emerald-500/20 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                                Unlock
                            </button>
                        </td>
                    </tr>`;
                }).join('');
            }
        } catch (err) {
            console.error('Error loading locked admins:', err);
        }
    }

    /**
     * Render Global Admin Table
     */
    function renderAdminTable(admins) {
        const tbody = document.getElementById('admin-management-body');
        if (!tbody) return;

        if (admins.length > 0) {
            tbody.innerHTML = admins.map(a => {
                let roleHtml = `<span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest bg-teal-500/10 text-teal-600 border border-teal-500/20">Admin</span>`;
                if (a.admin_role === 'moderator') {
                    roleHtml = `<span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest bg-amber-500/10 text-amber-600 border border-amber-500/20">Moderator</span>`;
                }

                let statHtml = `<span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">• Active</span>`;
                if (a.status === 'suspended') {
                    statHtml = `<span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest bg-rose-500/10 text-rose-600 border border-rose-500/20">• Suspended</span>`;
                }

                const photo = a.profile_photo
                    ? `<img src="/Finzo/public/assets/${a.profile_photo}" class="w-9 h-9 rounded-xl object-cover shadow-sm ring-2 ring-primary/20">`
                    : `<div class="w-9 h-9 rounded-xl bg-surface-subtle flex items-center justify-center text-xs font-black text-text-secondary border border-border-subtle">${(a.full_name || 'A').charAt(0).toUpperCase()}</div>`;

                const formatDateLocal = (d) => {
                    if (!d) return '-';
                    const dt = new Date(d);
                    return dt.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
                };

                return `
                    <tr class="hover:bg-surface-subtle transition-colors">
                        <td class="py-4 px-6 text-xs font-mono font-bold text-text-secondary">${a.admin_id}</td>
                        <td class="py-4 px-6">
                            <div class="flex items-center gap-3">
                                ${photo}
                                <div>
                                    <div class="text-xs font-black text-text-primary tracking-tight">${a.full_name}</div>
                                    <div class="text-[10px] font-bold text-text-secondary mt-0.5">${a.email}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-6">${roleHtml}</td>
                        <td class="py-4 px-6">
                            <span class="text-xs font-bold uppercase tracking-tight text-text-secondary">${a.access_level || 'standard'}</span>
                        </td>
                        <td class="py-4 px-6">${statHtml}</td>
                        <td class="py-4 px-6">
                            <div class="text-xs font-bold text-text-primary">${a.last_login_at ? window.FinzoUtils.timeAgo(a.last_login_at) : 'Never'}</div>
                        </td>
                        <td class="py-4 px-6">
                            <div class="text-xs font-bold text-text-primary">${a.created_by_name || 'System'}</div>
                            <div class="text-[10px] font-medium text-text-secondary mt-0.5">${formatDateLocal(a.created_at)}</div>
                        </td>
                        <td class="py-4 px-6 text-right">
                            <a href="admin-detail.php?id=${a.id}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-white text-zinc-900 hover:bg-black hover:text-white dark:bg-zinc-900 dark:text-white dark:hover:bg-white dark:hover:text-black rounded-xl text-[10px] font-black uppercase tracking-widest transition-all border border-border-subtle shadow-sm cursor-pointer">
                                <span>Inspect</span>
                                <span class="material-symbols-outlined text-[14px]" aria-hidden="true">chevron_right</span>
                            </a>
                        </td>
                    </tr>
                `;
            }).join('');
        } else {
            tbody.innerHTML = `<tr><td colspan="8" class="py-12 text-center text-text-secondary text-sm">No administrators match your search or filter criteria.</td></tr>`;
        }
    }

    function filterAdminList() {
        const searchVal = (document.getElementById('admin-search-input')?.value || '').toLowerCase().trim();
        const statusVal = (document.getElementById('admin-status-filter')?.value || 'all').toLowerCase().trim();
        const roleVal = (document.getElementById('admin-role-filter')?.value || 'all').toLowerCase().trim();

        const filtered = allAdmins.filter(a => {
            if (searchVal) {
                const haystack = `${a.full_name || ''} ${a.email || ''} ${a.admin_id || ''} ${a.phone || ''} ${a.admin_role || ''} ${a.status || ''}`.toLowerCase();
                if (!haystack.includes(searchVal)) return false;
            }
            if (statusVal !== 'all' && (a.status || '').toLowerCase() !== statusVal) return false;
            if (roleVal !== 'all' && (a.admin_role || '').toLowerCase() !== roleVal) return false;
            return true;
        });

        const badge = document.getElementById('management-count-badge');
        if (badge) {
            const isFiltered = searchVal || statusVal !== 'all' || roleVal !== 'all';
            badge.textContent = isFiltered
                ? `${filtered.length} of ${allAdmins.length} Admins`
                : `${allAdmins.length} Admins`;
        }

        renderAdminTable(filtered);
    }


    async function loadAdminList() {
        try {
            const res = await window.api.get('/admins');
            if (res && (res.success || res.status === 200 || res.data)) {
                const payload = res.data || res;
                allAdmins = payload.admins || (Array.isArray(payload) ? payload : []);
                const total = payload.total ?? allAdmins.length;
                const countBadge = document.getElementById('management-count-badge');
                if (countBadge) countBadge.textContent = `${total} Admins`;
                renderAdminTable(allAdmins);
            }
        } catch (e) {
            console.error('Error loading admin list:', e);
        }
    }

    // Attach search and filter listeners for directory
    const searchInput = document.getElementById('admin-search-input');
    if (searchInput) searchInput.addEventListener('input', filterAdminList);

    const statusFilter = document.getElementById('admin-status-filter');
    if (statusFilter) statusFilter.addEventListener('change', filterAdminList);

    const roleFilter = document.getElementById('admin-role-filter');
    if (roleFilter) roleFilter.addEventListener('change', filterAdminList);

    // Attach real-time listeners for activity log
    const activitySearch = document.getElementById('activity-search-input');
    if (activitySearch) activitySearch.addEventListener('input', () => window.AdminProfile.applyActivityFilter());

    const fromDateInput = document.getElementById('activity-from-date');
    if (fromDateInput) fromDateInput.addEventListener('change', () => window.AdminProfile.applyActivityFilter());

    const toDateInput = document.getElementById('activity-to-date');
    if (toDateInput) toDateInput.addEventListener('change', () => window.AdminProfile.applyActivityFilter());


    // ==========================================
    // EXPORTED OBJECT TO GLOBAL SCOPE
    // ==========================================
    window.AdminProfile = {
        
        switchTab: (tabId) => {
            // Update Tab Buttons
            document.querySelectorAll('.profile-tab-btn').forEach(btn => {
                const isCurrent = btn.dataset.tab === tabId;
                btn.setAttribute('aria-selected', isCurrent ? 'true' : 'false');
                if (isCurrent) {
                    btn.className = 'profile-tab-btn flex items-center gap-2 px-5 py-3 rounded-t-xl text-xs font-black uppercase tracking-wider transition-all border-b-2 border-black dark:border-white text-black dark:text-white bg-surface shadow-sm cursor-pointer';
                } else {
                    btn.className = 'profile-tab-btn flex items-center gap-2 px-5 py-3 rounded-t-xl text-xs font-black uppercase tracking-wider transition-all border-b-2 border-transparent text-text-secondary hover:text-text-primary hover:bg-surface-hover cursor-pointer';
                }
            });

            // Update Tab Panels
            document.querySelectorAll('.tab-panel').forEach(panel => {
                panel.classList.toggle('hidden', panel.id !== tabId);
            });
        },


        copyText: (text, label = 'Copied') => {
            if (!text || text === '---') return;
            navigator.clipboard.writeText(text).then(() => {
                if (window.portal) window.portal.showToast(`${label} copied to clipboard!`, 'success');
            }).catch(() => {
                if (window.portal) window.portal.showToast('Could not copy to clipboard', 'error');
            });
        },

        applyActivityFilter: () => {
            const searchVal = (document.getElementById('activity-search-input')?.value || '').toLowerCase().trim();
            const fromDateVal = document.getElementById('activity-from-date')?.value || '';
            const toDateVal = document.getElementById('activity-to-date')?.value || '';

            activityData = allActivityLogs.filter(a => {
                if (searchVal) {
                    const haystack = `${a.action || ''} ${a.action_type || ''} ${a.details || ''} ${a.ip_address || ''} ${a.entity_type || ''} ${a.entity_id || ''}`.toLowerCase();
                    if (!haystack.includes(searchVal)) return false;
                }
                if (fromDateVal && a.created_at) {
                    const logDate = a.created_at.substring(0, 10);
                    if (logDate < fromDateVal) return false;
                }
                if (toDateVal && a.created_at) {
                    const logDate = a.created_at.substring(0, 10);
                    if (logDate > toDateVal) return false;
                }
                return true;
            });

            currentActivityPage = 1;
            const isFiltered = Boolean(searchVal || fromDateVal || toDateVal);
            const countBadge = document.getElementById('activity-count-badge');
            if (countBadge) {
                countBadge.textContent = isFiltered
                    ? `${activityData.length} of ${allActivityLogs.length} Logs`
                    : `${allActivityLogs.length} Logs`;
            }
            renderActivityPage();
        },

        resetActivityFilter: () => {
            const searchInput = document.getElementById('activity-search-input');
            const fromInput = document.getElementById('activity-from-date');
            const toInput = document.getElementById('activity-to-date');

            if (searchInput) searchInput.value = '';
            if (fromInput) fromInput.value = '';
            if (toInput) toInput.value = '';

            activityData = [...allActivityLogs];
            currentActivityPage = 1;
            const countBadge = document.getElementById('activity-count-badge');
            if (countBadge) countBadge.textContent = `${allActivityLogs.length} Logs`;
            renderActivityPage();
            if (window.portal) window.portal.showToast('Activity filters reset', 'info');
        },

        exportActivityCSV: () => {
            const dataToExport = (activityData && activityData.length > 0) ? activityData : allActivityLogs;
            if (!dataToExport || dataToExport.length === 0) {
                if (window.portal) window.portal.showToast('No activity logs found to export', 'error');
                return;
            }

            const fromDateVal = document.getElementById('activity-from-date')?.value;
            const toDateVal = document.getElementById('activity-to-date')?.value;

            const headers = ['Log ID', 'Operation / Action', 'Details & Context', 'IP Address', 'Entity Type', 'Entity ID', 'Date & Time'];
            const rows = dataToExport.map((a, idx) => [
                idx + 1,
                `"${(a.action || a.action_type || 'System Event').replace(/"/g, '""')}"`,
                `"${(a.details || '').replace(/"/g, '""')}"`,
                `"${a.ip_address || '127.0.0.1'}"`,
                `"${a.entity_type || 'admin'}"`,
                `"${a.entity_id || 'ADM001'}"`,
                `"${a.created_at || ''}"`
            ]);

            const csvString = [headers.join(','), ...rows.map(r => r.join(','))].join('\r\n');
            const blob = new Blob(['\uFEFF' + csvString], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);

            let dateLabel = new Date().toISOString().slice(0, 10);
            if (fromDateVal && toDateVal) {
                dateLabel = `${fromDateVal}_to_${toDateVal}`;
            } else if (fromDateVal) {
                dateLabel = `from_${fromDateVal}`;
            } else if (toDateVal) {
                dateLabel = `upto_${toDateVal}`;
            }

            const link = document.createElement('a');
            link.href = url;
            link.download = `Finzo_Admin_Audit_Trail_${dateLabel}.csv`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            setTimeout(() => URL.revokeObjectURL(url), 1000);

            if (window.portal) window.portal.showToast(`Exported ${dataToExport.length} activity records as CSV`, 'success');
        },


        uploadPhoto: async (input) => {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const formData = new FormData();
                formData.append('profile_photo', file);

                try {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const preview = document.getElementById('avatar-img-preview');
                        const initials = document.getElementById('avatar-initials');
                        if (preview) { preview.src = e.target.result; preview.classList.remove('hidden'); }
                        if (initials) initials.classList.add('hidden');
                    };
                    reader.readAsDataURL(file);

                    const response = await window.api.upload('/admins/profile/photo', formData);
                    const payload = response ? (response.data || response) : {};
                    if (response && (response.success || response.status === 200 || payload.success)) {
                        if (window.portal) window.portal.showToast(payload.message || 'Profile photo updated successfully', 'success');
                        await loadAdminProfile();
                        if (window.AdminPortal) window.AdminPortal.init();
                    } else {
                        if (window.portal) window.portal.showToast(payload.message || 'Upload failed', 'error');
                    }
                } catch (error) {
                    if (window.portal) window.portal.showToast('System error uploading photo', 'error');
                }

            }
        },

        previewNewPhoto: (input) => {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const r = new FileReader();
                r.onload = (e) => {
                    const preview = document.getElementById('new-avatar-preview');
                    if (preview) { preview.src = e.target.result; preview.classList.remove('hidden'); }
                };
                r.readAsDataURL(file);
            }
        },

        togglePW: (id) => {
            const input = document.getElementById(id);
            if (!input) return;
            const btn = input.nextElementSibling;
            const icon = btn ? btn.querySelector('.material-symbols-outlined') : null;
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
                pwBar.style.width = '0%'; if (pwText) pwText.textContent = '';
            } else if (strength <= 2) {
                pwBar.style.width = '33%'; pwBar.style.background = '#ef4444'; if (pwText) { pwText.textContent = 'Weak'; pwText.style.color = '#ef4444'; }
            } else if (strength === 3) {
                pwBar.style.width = '66%'; pwBar.style.background = '#f39c12'; if (pwText) { pwText.textContent = 'Fair'; pwText.style.color = '#f39c12'; }
            } else {
                pwBar.style.width = '100%'; pwBar.style.background = '#10b981'; if (pwText) { pwText.textContent = 'Strong'; pwText.style.color = '#10b981'; }
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
                if (window.portal) window.portal.showToast('Please fill all password fields', 'error');
                return;
            }
            if (nw !== conf) {
                if (window.portal) window.portal.showToast('New passwords do not match', 'error');
                return;
            }

            const btn = document.querySelector('button[onclick*="updatePassword"]') || document.activeElement;

            try {
                if (btn) window.api.setButtonLoading(btn, true, 'Updating...');
                const res = await window.api.post('/admins/profile', {
                    current_password: curr, new_password: nw, confirm_new_password: conf
                });
                const payload = res ? (res.data || res) : {};
                if (res && (res.success || res.status === 200 || payload.success)) {
                    if (window.portal) window.portal.showToast(payload.message || 'Password changed successfully', 'success');
                    document.getElementById('curr-pw').value = '';
                    document.getElementById('new-pw').value = '';
                    document.getElementById('conf-pw').value = '';
                    const pwBar = document.getElementById('pw-bar');
                    if (pwBar) pwBar.style.width = '0%';
                    const pwText = document.getElementById('pw-text');
                    if (pwText) pwText.textContent = '';
                    loadAdminProfile();
                } else {
                    if (window.portal) window.portal.showToast(payload.message || res?.message || 'Password update failed', 'error');
                }
            } catch (e) {
                if (window.portal) window.portal.showToast('System error updating password', 'error');
            } finally {
                if (btn) window.api.setButtonLoading(btn, false);
            }
        },

        saveProfile: async () => {
            const nameInput = document.getElementById('profile-name-input');
            const dobInput = document.getElementById('profile-dob-input');
            const genderInput = document.getElementById('profile-gender-input');
            const phoneInput = document.getElementById('profile-phone-input');

            const payload = {
                full_name: nameInput ? nameInput.value.trim() : '',
                date_of_birth: dobInput ? dobInput.value : '',
                gender: genderInput ? genderInput.value : '',
                phone: phoneInput ? phoneInput.value.trim() : ''
            };

            if (!payload.full_name) {
                if (window.portal) window.portal.showToast('Full Name is required', 'error');
                return;
            }

            const btn = document.querySelector('button[onclick*="saveProfile"]') || document.activeElement;

            try {
                if (btn) window.api.setButtonLoading(btn, true, 'Saving...');
                const res = await window.api.post('/admins/profile', payload);
                const data = res ? (res.data || res) : {};
                if (res && (res.success || res.status === 200 || data.success)) {
                    if (window.portal) window.portal.showToast(data.message || 'Profile saved successfully', 'success');
                    loadAdminProfile();
                } else {
                    if (window.portal) window.portal.showToast(data.message || res?.message || 'Save failed', 'error');
                }
            } catch (e) {
                if (window.portal) window.portal.showToast('System error updating profile', 'error');
            } finally {
                if (btn) window.api.setButtonLoading(btn, false);
            }
        },

        toggle2FA: async (enabled) => {
            try {
                const res = await window.api.post('/admins/profile', {
                    two_fa_enabled: enabled ? 1 : 0
                });
                const payload = res ? (res.data || res) : {};
                if (res && (res.success || res.status === 200 || payload.success)) {
                    const label = document.getElementById('sec-2fa-label');
                    if (label) {
                        label.textContent = enabled ? 'ENABLED (ACTIVE)' : 'DISABLED';
                        label.className = enabled
                            ? 'text-[10px] font-mono font-black uppercase tracking-widest px-2.5 py-1 rounded bg-emerald-500/10 text-emerald-600 border border-emerald-500/20'
                            : 'text-[10px] font-mono font-black uppercase tracking-widest px-2.5 py-1 rounded bg-surface border border-border-subtle text-text-secondary';
                    }
                    if (window.portal) window.portal.showToast('Two-Factor Authentication settings updated', 'success');
                    loadAdminProfile();
                } else {
                    document.getElementById('sec-2fa-toggle').checked = !enabled;
                    if (window.portal) window.portal.showToast(payload.message || 'Error updating 2FA', 'error');
                }
            } catch (e) {
                document.getElementById('sec-2fa-toggle').checked = !enabled;
                if (window.portal) window.portal.showToast('System error updating 2FA', 'error');
            }
        },

        updateRolePreview: (role) => {
            const box = document.getElementById('role-preview-box');
            if (!box) return;
            if (!role) {
                box.classList.add('hidden');
                return;
            }
            box.classList.remove('hidden');
            if (role === 'admin') {
                box.innerHTML = `
                    <div class="font-bold text-text-primary mb-2">Administrator Permissions Summary:</div>
                    <ul class="space-y-1">
                        <li>✅ Executive Dashboard Access</li>
                        <li>✅ Customer Accounts & User Management</li>
                        <li>✅ Staff Management & Shift Oversight</li>
                        <li>✅ Global Transactions Overview</li>
                        <li>✅ Branch Operations View</li>
                        <li>✅ Pending Approvals & KYC Verification</li>
                        <li class="opacity-50 line-through">❌ Delete or Suspend Other Administrators</li>
                        <li class="opacity-50 line-through">❌ Alter Global Interest Engine</li>
                        <li class="opacity-50 line-through">❌ Provision Root Level Super Admins</li>
                    </ul>
                `;
            } else if (role === 'moderator') {
                box.innerHTML = `
                    <div class="font-bold text-text-primary mb-2">Moderator Permissions Summary:</div>
                    <ul class="space-y-1">
                        <li>✅ Executive Dashboard Overview</li>
                        <li>✅ Pending KYC Verifications & Review</li>
                        <li>✅ Support Document Auditing</li>
                        <li class="opacity-50 line-through">❌ User Management & Status Modification</li>
                        <li class="opacity-50 line-through">❌ Staff Operations</li>
                        <li class="opacity-50 line-through">❌ Financial Accounting & Transactions</li>
                        <li class="opacity-50 line-through">❌ Branch Configuration</li>
                    </ul>
                `;
            }
        },

        checkAddStrength: (val) => {
            const bar = document.getElementById('add-pw-bar');
            if (!bar) return;
            if (!val) {
                bar.style.width = '0%';
                return;
            }
            let score = 0;
            if (val.length >= 8) score += 25;
            if (/[A-Z]/.test(val)) score += 25;
            if (/[0-9]/.test(val)) score += 25;
            if (/[^A-Za-z0-9]/.test(val)) score += 25;

            bar.style.width = score + '%';
            if (score <= 25) {
                bar.style.background = '#EF4444';
            } else if (score <= 50) {
                bar.style.background = '#F59E0B';
            } else if (score <= 75) {
                bar.style.background = '#3B82F6';
            } else {
                bar.style.background = '#10B981';
            }
        },

        previewNewPhoto: (input) => {
            const preview = document.getElementById('new-avatar-preview');
            const errEl = document.getElementById('err-add-photo');
            if (errEl) errEl.classList.add('hidden');
            if (input.files && input.files[0]) {
                const file = input.files[0];
                if (file.size > 10 * 1024 * 1024) {
                    if (errEl) {
                        errEl.textContent = 'File size exceeds 10MB limit';
                        errEl.classList.remove('hidden');
                    }
                    input.value = '';
                    if (preview) preview.classList.add('hidden');
                    return;
                }
                const reader = new FileReader();
                reader.onload = (e) => {
                    if (preview) {
                        preview.src = e.target.result;
                        preview.classList.remove('hidden');
                    }
                };
                reader.readAsDataURL(file);
            } else {
                if (preview) {
                    preview.src = '';
                    preview.classList.add('hidden');
                }
            }
        },

        showAddModal: async () => {
            const modal = document.getElementById('add-admin-modal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.style.overflow = 'hidden';

                // Reset error messages
                document.querySelectorAll('[id^=err-add-]').forEach(el => el.classList.add('hidden'));

                try {
                    const res = await window.api.get('/admins/next-id');
                    if (res && (res.success || res.data)) {
                        const payload = res.data || res;
                        const nextId = payload.next_id || res.next_id;
                        if (nextId) {
                            const idInput = document.getElementById('add-admin-id');
                            if (idInput) idInput.value = nextId;
                        }
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
            document.querySelectorAll('[id^=err-add-]').forEach(el => el.classList.add('hidden'));

            const fields = ['name', 'dob', 'gender', 'phone', 'email', 'pw', 'conf-pw', 'seq-q', 'seq-a', 'role'];
            const vals = {};
            let hasErr = false;

            fields.forEach(f => {
                const el = document.getElementById('add-' + f);
                vals[f] = el ? el.value.trim() : '';
                if (!vals[f]) {
                    const e = document.getElementById('err-add-' + f);
                    if (e) {
                        e.textContent = 'This field is required';
                        e.classList.remove('hidden');
                    }
                    hasErr = true;
                }
            });

            if (vals.pw && vals['conf-pw'] && vals.pw !== vals['conf-pw']) {
                const e = document.getElementById('err-add-conf-pw');
                if (e) {
                    e.textContent = 'Passwords do not match';
                    e.classList.remove('hidden');
                }
                hasErr = true;
            }

            if (hasErr) {
                const firstErr = document.querySelector('[id^=err-add-]:not(.hidden)');
                if (firstErr) {
                    firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                if (window.portal) window.portal.showToast('Please fill all required fields', 'warning');
                return;
            }


            if (btn) {
                btn.disabled = true;
                const spinner = btn.querySelector('span:last-child');
                if (spinner) spinner.classList.remove('hidden');
            }

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
            if (fileInput && fileInput.files.length > 0) {
                formData.append('profile_photo', fileInput.files[0]);
            }

            try {
                const response = await window.api.upload('/admins', formData);
                const payload = response ? (response.data || response) : {};
                if (response && (response.success || response.status === 200 || response.status === 201 || payload.success)) {
                    window.AdminProfile.closeAddModal();

                    fields.forEach(f => {
                        const input = document.getElementById('add-' + f);
                        if (input) input.value = '';
                    });
                    const photoPrev = document.getElementById('new-avatar-preview');
                    if (photoPrev) { photoPrev.src = ''; photoPrev.classList.add('hidden'); }
                    const roleBox = document.getElementById('role-preview-box');
                    if (roleBox) roleBox.classList.add('hidden');
                    const pwBar = document.getElementById('add-pw-bar');
                    if (pwBar) pwBar.style.width = '0%';

                    const createdName = payload.admin_name || vals.name;
                    if (window.portal) window.portal.showToast(payload.message || `Admin ${createdName} provisioned successfully`, 'success');
                    loadAdminList();
                } else {
                    const errMsg = payload.message || response?.message || 'Provisioning failed';
                    const errors = payload.errors || response?.errors || {};
                    const fieldKey = payload.field || response?.field || (Object.keys(errors).length > 0 ? Object.keys(errors)[0] : null);

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
                        'admin_role': 'role',
                        'profile_photo': 'photo'
                    };

                    if (fieldKey) {
                        const targetId = 'err-add-' + (fieldMap[fieldKey] || fieldKey);
                        const e = document.getElementById(targetId);
                        if (e) {
                            e.textContent = errors[fieldKey] || errMsg;
                            e.classList.remove('hidden');
                        }
                    }
                    if (window.portal) window.portal.showToast(errMsg, 'error');
                }
            } catch (err) {
                if (window.portal) window.portal.showToast('System error provisioning admin', 'error');
            } finally {
                if (btn) {
                    btn.disabled = false;
                    const spinner = btn.querySelector('span:last-child');
                    if (spinner) spinner.classList.add('hidden');
                }
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
                if (window.portal) window.portal.showToast('Security question must be at least 10 characters', 'error');
                return;
            }
            if (!answer || answer.length < 3) {
                if (window.portal) window.portal.showToast('Security answer must be at least 3 characters', 'error');
                return;
            }
            if (!currPw) {
                if (window.portal) window.portal.showToast('Current password is required to verify changes', 'error');
                return;
            }

            const btn = document.getElementById('btn-save-sq');
            if (btn) window.api.setButtonLoading(btn, true, 'Saving...');

            try {
                const res = await window.api.post('/admins/profile/security', {
                    security_question: question,
                    security_answer: answer,
                    current_password: currPw
                });
                const payload = res ? (res.data || res) : {};
                if (res && (res.success || res.status === 200 || payload.success)) {
                    if (window.portal) window.portal.showToast(payload.message || 'Security question saved successfully', 'success');
                    document.getElementById('sq-answer').value = '';
                    document.getElementById('sq-curr-pw').value = '';
                    loadAdminProfile();
                } else {
                    if (window.portal) window.portal.showToast(payload.message || res?.message || 'Failed to save security question', 'error');
                }
            } catch (err) {
                if (window.portal) window.portal.showToast('System error saving security question', 'error');
            } finally {
                if (btn) window.api.setButtonLoading(btn, false);
            }
        },

        showUnlockConfirm: (adminId, adminName) => {
            pendingUnlockAdminId = adminId;
            pendingUnlockName = adminName;
            const overlay = document.getElementById('unlock-confirm-overlay');
            const msg = document.getElementById('unlock-confirm-msg');
            if (msg) msg.textContent = `Unlock ${adminName}'s account? They will be able to login with their credentials immediately.`;
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
            const origHtml = btn ? btn.innerHTML : '';
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="material-symbols-outlined animate-spin text-[16px]" aria-hidden="true">progress_activity</span> Unlocking...';
            }

            try {
                const res = await window.api.post('/admins/unlock', {
                    target_admin_id: pendingUnlockAdminId
                });
                const payload = res ? (res.data || res) : {};
                window.AdminProfile.closeUnlockConfirm();
                if (res && (res.success || res.status === 200 || payload.success)) {
                    if (window.portal) window.portal.showToast(payload.message || 'Account unlocked successfully', 'success');
                    loadLockedAdmins();
                    loadAdminList();
                } else {
                    if (window.portal) window.portal.showToast(payload.message || res?.message || 'Unlock failed', 'error');
                }
            } catch (err) {
                if (window.portal) window.portal.showToast('System error unlocking account', 'error');
            } finally {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = origHtml;
                }
            }
        }
    };

    // DOM Ready initialization
    document.addEventListener('DOMContentLoaded', () => {
        loadAdminProfile();

        // Listen for Esc key to dismiss modals
        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                if (window.AdminProfile.closeAddModal) window.AdminProfile.closeAddModal();
                if (window.AdminProfile.closeUnlockConfirm) window.AdminProfile.closeUnlockConfirm();
            }
        });
    });

})();
