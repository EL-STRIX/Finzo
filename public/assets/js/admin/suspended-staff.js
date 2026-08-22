/**
 * Finzo Admin — Suspended Staff Handler
 * Manages the table of suspended staff, filtering (search, dates, reason), metrics, and reinstatement.
 */

(function() {
    let currentPage = 1;
    let pendingId = null;
    let debounceTimer = null;

    // DOM Elements
    const searchInput = document.getElementById('search-input');
    const tableBody = document.getElementById('table-body');
    const pagInfo = document.getElementById('pag-info');
    const pagControls = document.getElementById('pag-controls');
    const startDateInput = document.getElementById('start-date');
    const endDateInput = document.getElementById('end-date');
    const reasonFilter = document.getElementById('reason-filter');
    const reasonDropdown = document.getElementById('reason-dropdown');
    const resetFiltersBtn = document.getElementById('reset-filters-btn');
    const reinstateModal = document.getElementById('reinstate-modal');
    const modalText = document.getElementById('modal-text');
    const modalError = document.getElementById('modal-error');
    const confirmBtn = document.getElementById('confirm-btn');
    const closeModalBtn = document.getElementById('close-modal-btn');
    const adminPasswordInput = document.getElementById('admin-password');

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function getReasonBadgeClass(reason) {
        const r = (reason || '').toLowerCase();
        if (r.includes('financial') || r.includes('cash') || r.includes('theft')) {
            return 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20';
        }
        if (r.includes('security') || r.includes('breach') || r.includes('unauthorized')) {
            return 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20';
        }
        if (r.includes('resignation') || r.includes('leave') || r.includes('voluntary')) {
            return 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20';
        }
        if (r.includes('performance') || r.includes('compliance')) {
            return 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20';
        }
        return 'bg-slate-500/10 text-slate-600 dark:text-slate-400 border border-slate-500/20';
    }

    function updateStats(staffList, stats) {
        const totalEl = document.getElementById('stat-total-suspended');
        const branchesEl = document.getElementById('stat-branches-affected');

        if (totalEl) {
            const total = stats?.suspended_staff ?? (stats?.total ?? staffList.length);
            totalEl.textContent = total;
        }

        if (branchesEl) {
            const uniqueBranches = new Set();
            (staffList || []).forEach(s => {
                if (s.branch_name && s.branch_name !== 'Unassigned') {
                    uniqueBranches.add(s.branch_name);
                }
            });
            branchesEl.textContent = uniqueBranches.size > 0 ? uniqueBranches.size : (staffList.length > 0 ? '1' : '0');
        }
    }

    /**
     * Load suspended staff from API
     */
    async function loadSuspendedStaff() {
        const search = searchInput ? searchInput.value.trim() : '';
        const startDate = startDateInput ? startDateInput.value : '';
        const endDate = endDateInput ? endDateInput.value : '';
        const reason = reasonFilter ? reasonFilter.value.trim() : '';

        if (tableBody) {
            tableBody.innerHTML = `
                <tr><td colspan="9"><div class="skeleton-row"></div></td></tr>
                <tr><td colspan="9"><div class="skeleton-row"></div></td></tr>
                <tr><td colspan="9"><div class="skeleton-row"></div></td></tr>
                <tr><td colspan="9"><div class="skeleton-row"></div></td></tr>
            `;
        }

        try {
            const response = await window.api.get('/staff/suspended', {
                search: search,
                reason: reason,
                start_date: startDate,
                end_date: endDate,
                page: currentPage,
                per_page: 10
            });

            if (response && (response.status === 'success' || response.success || response.data)) {
                const data = response.data || response;
                const staffList = data.staff || [];
                const total = data.total ?? staffList.length;
                const pages = data.pages ?? Math.max(1, Math.ceil(total / 10));

                renderTable(staffList);
                updatePagination(total, pages);
                updateStats(staffList, data.stats);
            } else {
                if (tableBody) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="9" class="py-16 text-center">
                                <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                    <div class="w-12 h-12 rounded-2xl bg-surface border border-border-subtle flex items-center justify-center text-text-secondary mb-3 shadow-sm">
                                        <span class="material-symbols-outlined text-2xl" aria-hidden="true">check_circle</span>
                                    </div>
                                    <div class="text-sm font-black text-text-primary">No Suspended Staff Found</div>
                                    <p class="text-xs font-medium text-text-secondary mt-1">All staff terminals are currently active and compliant.</p>
                                </div>
                            </td>
                        </tr>
                    `;
                }
                if (pagInfo) pagInfo.textContent = 'No records found';
                if (pagControls) pagControls.innerHTML = '';
                updateStats([], null);
            }
        } catch (error) {
            console.error('Failed to load suspended staff:', error);
            if (tableBody) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="9" class="py-16 text-center">
                            <div class="flex flex-col items-center justify-center text-error">
                                <span class="material-symbols-outlined text-3xl mb-2" aria-hidden="true">error</span>
                                <div class="text-xs font-black uppercase tracking-wider">Failed to load suspended staff directory</div>
                            </div>
                        </td>
                    </tr>
                `;
            }
        }
    }

    /**
     * Render the staff table rows
     */
    function renderTable(staff) {
        if (!tableBody) return;

        if (!staff || !staff.length) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="9" class="py-16 text-center">
                        <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                            <div class="w-12 h-12 rounded-2xl bg-surface border border-border-subtle flex items-center justify-center text-text-secondary mb-3 shadow-sm">
                                <span class="material-symbols-outlined text-2xl" aria-hidden="true">search_off</span>
                            </div>
                            <div class="text-sm font-black text-text-primary">No Matching Records</div>
                            <p class="text-xs font-medium text-text-secondary mt-1">No suspended staff match the chosen search or filter criteria.</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        tableBody.innerHTML = staff.map((s, i) => {
            const initials = s.initials || (s.full_name ? s.full_name.split(' ').map(n => n[0]).join('').slice(0, 2).toUpperCase() : 'ST');
            const rowNum = String((currentPage - 1) * 10 + i + 1).padStart(2, '0');
            const gradient = (window.FinzoUtils && window.FinzoUtils.getAvatarGradient)
                ? window.FinzoUtils.getAvatarGradient(initials)
                : 'linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%)';
            
            const reasonBadgeClass = getReasonBadgeClass(s.suspension_reason);
            const photoUrl = s.profile_photo_url || (s.profile_photo ? (s.profile_photo.startsWith('/') ? s.profile_photo : '/Finzo/' + s.profile_photo) : null);

            return `
            <tr class="hover:bg-slate-50/50 dark:hover:bg-white/5 transition-colors text-xs">
                <td class="text-center font-mono font-bold text-text-secondary text-[11px]">${rowNum}</td>
                <td class="py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-black text-xs shadow-sm uppercase shrink-0 overflow-hidden" style="background: ${gradient}">
                            ${photoUrl ? `<img src="${photoUrl}" class="w-full h-full object-cover" alt="${escapeHtml(s.full_name)}" onerror="this.parentElement.innerHTML='<span class=\\'font-black text-xs text-white\\'>${initials}</span>'">` : initials}
                        </div>
                        <div class="min-w-0">
                            <div class="font-headline font-bold text-xs sm:text-sm text-text-primary truncate">${escapeHtml(s.full_name)}</div>
                            <div class="text-[11px] font-medium text-text-secondary truncate mt-0.5">${escapeHtml(s.personal_email || s.email || '—')}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="px-2.5 py-1 rounded-lg bg-surface border border-border-subtle font-mono text-[11px] font-bold text-text-primary shadow-sm">${escapeHtml(s.staff_id || 'STF---')}</span>
                </td>
                <td>
                    <div class="font-bold text-text-primary text-xs">${escapeHtml(s.branch_name || 'Unassigned')}</div>
                    ${s.branch_code ? `<div class="text-[10px] font-mono text-text-secondary">${escapeHtml(s.branch_code)}</div>` : ''}
                </td>
                <td>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-[11px] font-bold ${reasonBadgeClass} shadow-sm">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                        <span class="truncate max-w-[180px]">${escapeHtml(s.suspension_reason || 'Administrative Review')}</span>
                    </span>
                </td>
                <td>
                    <div class="font-bold text-text-primary text-xs">${escapeHtml(s.suspended_at || '—')}</div>
                </td>
                <td>
                    <span class="text-xs font-bold text-text-secondary">${escapeHtml(s.suspended_by || 'Administrator')}</span>
                </td>
                <td>
                    <span class="px-2.5 py-1 rounded-lg bg-surface border border-border-subtle text-[10px] font-black uppercase tracking-wider text-text-secondary">${escapeHtml(s.duration || 'Indefinite')}</span>
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-center text-sm font-medium">
                    <div class="flex items-center justify-center gap-2">
                        <a href="admin-staff-detail.php?id=${s.id}" class="w-8 h-8 rounded-lg bg-surface border border-border-subtle hover:bg-surface-hover hover:border-primary/50 transition-all flex items-center justify-center text-text-secondary hover:text-primary shadow-sm" title="View Full Dossier">
                            <span class="material-symbols-outlined text-base" aria-hidden="true">visibility</span>
                        </a>
                        <button type="button" class="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-500/20 hover:bg-emerald-500/20 transition-all flex items-center justify-center text-emerald-600 dark:text-emerald-400 cursor-pointer shadow-sm" 
                                onclick="window.SuspendedStaff.openReinstate('${s.id}', '${escapeHtml(s.full_name).replace(/'/g, "\\'")}', '${escapeHtml(s.staff_id || '').replace(/'/g, "\\'")}')"
                                title="Reinstate Staff Terminal">
                            <span class="material-symbols-outlined text-base" aria-hidden="true">lock_open</span>
                        </button>
                    </div>
                </td>
            </tr>`;
        }).join('');
    }

    /**
     * Update pagination UI
     */
    function updatePagination(total, pages) {
        if (!pagInfo || !pagControls) return;

        if (total === 0) {
            pagInfo.textContent = '0 records';
            pagControls.innerHTML = '';
            return;
        }

        const start = (currentPage - 1) * 10 + 1;
        const end = Math.min(currentPage * 10, total);
        pagInfo.textContent = `Showing ${start}–${end} of ${total} suspended personnel`;

        let pcHtml = `<button class="px-3 py-1.5 rounded-lg bg-surface border border-border-subtle font-bold text-xs text-text-secondary hover:bg-surface-hover hover:text-text-primary transition-all cursor-pointer ${currentPage <= 1 ? 'opacity-30 cursor-not-allowed pointer-events-none' : ''}" onclick="window.SuspendedStaff.goPage(-1)">← Previous</button>`;
        pcHtml += `<div class="flex items-center gap-1.5">`;
        for (let i = 1; i <= pages; i++) {
            if (i > 5 && i < pages - 1) { 
                if (i === 6) pcHtml += '<span class="px-2 opacity-50 text-xs">...</span>'; 
                continue; 
            }
            pcHtml += `<button class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-black transition-all ${i === currentPage ? 'bg-primary text-white shadow-sm' : 'bg-surface border border-border-subtle text-text-primary hover:bg-surface-hover cursor-pointer'}" onclick="window.SuspendedStaff.goPageTo(${i})">${i}</button>`;
        }
        pcHtml += `</div>`;
        pcHtml += `<button class="px-3 py-1.5 rounded-lg bg-surface border border-border-subtle font-bold text-xs text-text-secondary hover:bg-surface-hover hover:text-text-primary transition-all cursor-pointer ${currentPage >= pages ? 'opacity-30 cursor-not-allowed pointer-events-none' : ''}" onclick="window.SuspendedStaff.goPage(1)">Next →</button>`;
        
        pagControls.innerHTML = `<div class="flex items-center gap-2">${pcHtml}</div>`;
    }

    function triggerFilterDebounced() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            currentPage = 1;
            loadSuspendedStaff();
        }, 250);
    }

    // Exported Window Object
    window.SuspendedStaff = {
        goPage: (d) => { currentPage += d; loadSuspendedStaff(); },
        goPageTo: (p) => { currentPage = p; loadSuspendedStaff(); },
        
        triggerFilter: () => {
            currentPage = 1;
            loadSuspendedStaff();
        },

        openReinstate: (id, name, staffCode) => {
            pendingId = id;
            if (modalText) {
                modalText.innerHTML = `You are removing administrative suspension from <strong class="text-text-primary font-black">${name}</strong> (${staffCode || 'STF---'}). Their branch terminal privileges and processing rights will be restored immediately.`;
            }
            if (adminPasswordInput) adminPasswordInput.value = '';
            if (modalError) {
                modalError.textContent = '';
                modalError.classList.add('hidden');
            }
            if (reinstateModal) {
                reinstateModal.classList.remove('hidden');
                reinstateModal.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            }
            if (adminPasswordInput) adminPasswordInput.focus();
        },

        closeModal: () => {
            if (reinstateModal) {
                reinstateModal.classList.add('hidden');
                reinstateModal.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
            }
            if (adminPasswordInput) adminPasswordInput.value = '';
            if (modalError) modalError.classList.add('hidden');
            pendingId = null;
        },

        confirmReinstate: async () => {
            if (!pendingId) return;
            const btn = confirmBtn;
            if (!btn) return;

            const password = adminPasswordInput?.value || '';
            if (!password) {
                if (modalError) {
                    modalError.textContent = 'Administrator password is required to authorize reinstatement';
                    modalError.classList.remove('hidden');
                }
                return;
            }

            const originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Authorizing...';
            if (modalError) modalError.classList.add('hidden');
            
            try {
                const response = await window.api.patch('/staff/' + pendingId, {
                    staff_id: pendingId,
                    action: 'reinstate',
                    password: password
                });

                const payload = response.data || response;

                if (response.status === 'success' || response.success || payload.success) {
                    window.SuspendedStaff.closeModal();
                    if (window.showToast) {
                        window.showToast('Staff member reinstated successfully', 'success');
                    } else if (window.portal && window.portal.showToast) {
                        window.portal.showToast('Staff member reinstated successfully', 'success');
                    }
                    loadSuspendedStaff();
                } else {
                    if (modalError) {
                        modalError.textContent = payload.message || response.message || 'Authorization failed. Please check credentials.';
                        modalError.classList.remove('hidden');
                    }
                }
            } catch (error) {
                console.error('Reinstate failed:', error);
                if (modalError) {
                    modalError.textContent = error?.message || 'Server authorization error occurred.';
                    modalError.classList.remove('hidden');
                }
            } finally {
                btn.disabled = false;
                btn.textContent = originalText;
            }
        }
    };

    // Event Listeners Initialization
    function init() {
        loadSuspendedStaff();

        // Search Input (Real-time debounced)
        if (searchInput) {
            searchInput.addEventListener('input', triggerFilterDebounced);
        }

        // Date Inputs
        if (startDateInput) {
            startDateInput.addEventListener('change', window.SuspendedStaff.triggerFilter);
            startDateInput.addEventListener('input', window.SuspendedStaff.triggerFilter);
        }
        if (endDateInput) {
            endDateInput.addEventListener('change', window.SuspendedStaff.triggerFilter);
            endDateInput.addEventListener('input', window.SuspendedStaff.triggerFilter);
        }

        // Reason Dropdown Change
        if (reasonFilter) {
            reasonFilter.addEventListener('change', window.SuspendedStaff.triggerFilter);
        }
        if (reasonDropdown) {
            reasonDropdown.addEventListener('change', window.SuspendedStaff.triggerFilter);
        }

        // Reset Filters Button
        if (resetFiltersBtn) {
            resetFiltersBtn.addEventListener('click', () => {
                if (searchInput) searchInput.value = '';
                if (startDateInput) startDateInput.value = '';
                if (endDateInput) endDateInput.value = '';
                if (reasonFilter) reasonFilter.value = '';
                
                const label = reasonDropdown?.querySelector('.dropdown-label');
                if (label) label.textContent = 'All Reasons';
                reasonDropdown?.querySelectorAll('.finzo-dropdown-option').forEach((opt, idx) => {
                    if (idx === 0) opt.classList.add('is-selected');
                    else opt.classList.remove('is-selected');
                });

                currentPage = 1;
                loadSuspendedStaff();
            });
        }

        // Modal Action Bindings
        if (closeModalBtn) {
            closeModalBtn.addEventListener('click', window.SuspendedStaff.closeModal);
        }
        if (confirmBtn) {
            confirmBtn.addEventListener('click', window.SuspendedStaff.confirmReinstate);
        }
        if (reinstateModal) {
            reinstateModal.addEventListener('click', (e) => {
                if (e.target === reinstateModal) window.SuspendedStaff.closeModal();
            });
        }

        // Admin Password Enter key submission
        if (adminPasswordInput) {
            adminPasswordInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    window.SuspendedStaff.confirmReinstate();
                }
            });
        }

        // Escape Key Modal Dismissal
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                window.SuspendedStaff.closeModal();
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
