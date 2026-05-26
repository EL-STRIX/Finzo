/**
 * Finzo Admin — Suspended Users Handler
 * Manages the table of suspended users, filtering, and reinstatement.
 */

(function() {
    let currentPage = 1;
    let pendingReinstateId = null;

    // DOM Elements
    const searchInput = document.getElementById('search-input');
    const tableBody = document.getElementById('table-body');
    const pagInfo = document.getElementById('pag-info');
    const pagControls = document.getElementById('pag-controls');
    const startDateInput = document.getElementById('start-date');
    const endDateInput = document.getElementById('end-date');
    const reasonFilter = document.getElementById('reason-filter');
    const reinstateModal = document.getElementById('reinstate-modal');
    const reinstateModalText = document.getElementById('reinstate-modal-text');
    const reinstateConfirmBtn = document.getElementById('reinstate-confirm-btn');

    /**
     * Load suspended users from API
     */
    async function loadSuspendedUsers() {
        const search = searchInput ? searchInput.value : '';
        const startDate = startDateInput ? startDateInput.value : '';
        const endDate = endDateInput ? endDateInput.value : '';
        const reason = reasonFilter ? reasonFilter.value : '';

        if (tableBody) {
            tableBody.innerHTML = `
                <tr><td colspan="9"><div class="skeleton-row"></div></td></tr>
                <tr><td colspan="9"><div class="skeleton-row"></div></td></tr>
                <tr><td colspan="9"><div class="skeleton-row"></div></td></tr>
            `;
        }

        try {
            const response = await window.api.get('/admin/suspended-users.php', {
                search: search,
                reason: reason,
                start_date: startDate,
                end_date: endDate,
                page: currentPage,
                per_page: 10
            });

            if (response.status === 'success' || response.success) {
                const data = response.data || response;
                renderTable(data.users || [], data.total || 0, data.pages || 1);
            } else {
                if (tableBody) {
                    tableBody.innerHTML = `<tr><td colspan="9" class="py-20 text-center font-bold opacity-40">${response.message || 'No suspended accounts found.'}</td></tr>`;
                }
                if (pagInfo) pagInfo.textContent = 'No results';
                if (pagControls) pagControls.innerHTML = '';
            }
        } catch (error) {
            console.error('Failed to load suspended users:', error);
            if (tableBody) {
                tableBody.innerHTML = `<tr><td colspan="9" class="py-20 text-center font-bold opacity-40 text-red-500">Failed to load data.</td></tr>`;
            }
        }
    }

    /**
     * Render the users table
     */
    function renderTable(users, total, pages) {
        if (!tableBody) return;

        if (!users || !users.length) {
            tableBody.innerHTML = `<tr><td colspan="9" class="py-20 text-center font-bold opacity-40">No suspended accounts found.</td></tr>`;
            if (pagInfo) pagInfo.textContent = 'Showing 0 of 0 suspended accounts';
            if (pagControls) pagControls.innerHTML = '';
            return;
        }

        tableBody.innerHTML = users.map((u, i) => {
            const initials = u.initials || (u.full_name ? u.full_name.charAt(0) : '?');
            const rowNum = String((currentPage - 1) * 10 + i + 1).padStart(2, '0');
            const gradient = window.FinzoUtils.getAvatarGradient(initials);
            
            return `
            <tr class="hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-colors">
                <td class="py-4 px-6 text-xs font-black opacity-30 tracking-widest">${rowNum}</td>
                <td class="py-4 px-6">
                    <div class="user-cell flex items-center gap-3">
                        <div class="avatar-circle premium-gradient-primary uppercase flex items-center justify-center text-white font-bold w-10 h-10 rounded-full" style="background:${gradient}">${initials}</div>
                        <div style="display:flex; flex-direction:column;">
                            <span class="user-name font-bold text-sm text-slate-800 dark:text-slate-200">${u.full_name}</span>
                            <span class="user-email text-[11px] opacity-60 font-mono">${u.customer_id || 'FNZ-USR-PENDING'}</span>
                        </div>
                    </div>
                </td>
                <td class="py-4 px-6"><span class="mono font-mono text-xs opacity-80">${u.account_no || 'N/A'}</span></td>
                <td class="py-4 px-6"><span class="status-badge ${u.account_type === 'savings' ? 'badge-success' : 'badge-primary'}">${u.account_type || 'N/A'}</span></td>
                <td class="py-4 px-6"><span class="status-badge badge-error">${u.suspension_reason || 'Suspended'}</span></td>
                <td class="py-4 px-6 text-xs font-bold text-slate-500">${u.suspended_at || 'N/A'}</td>
                <td class="py-4 px-6 text-xs font-semibold opacity-60">${u.suspended_by || 'Admin'}</td>
                <td class="py-4 px-6"><span class="status-badge badge-secondary">${u.duration || 'Eternal'}</span></td>
                <td class="py-4 px-6 text-center">
                    <div class="flex items-center justify-center gap-2">
                        <a href="admin-suspended-user-detail.html?id=${u.id}" class="p-2 hover:bg-slate-100 dark:hover:bg-white/5 rounded-lg transition-colors text-slate-400 hover:text-primary" title="View Details">
                            <span class="material-symbols-outlined" style="font-size:18px">visibility</span>
                        </a>
                        <button class="p-2 hover:bg-slate-100 dark:hover:bg-white/5 rounded-lg transition-colors text-slate-400 hover:text-emerald-500" onclick="window.SuspendedUsers.openReinstate('${u.id}','${u.full_name}')" title="Restore Account">
                            <span class="material-symbols-outlined" style="font-size:18px">lock_open</span>
                        </button>
                    </div>
                </td>
            </tr>`;
        }).join('');

        updatePagination(total, pages);
    }

    /**
     * Update pagination UI
     */
    function updatePagination(total, pages) {
        if (!pagInfo || !pagControls) return;

        pagInfo.textContent = `Showing ${Math.min((currentPage - 1) * 10 + 1, total)}–${Math.min(currentPage * 10, total)} of ${total} suspended accounts`;
        
        let html = `<button class="admin-btn-secondary px-3 py-1.5 text-[11px] rounded-lg ${currentPage <= 1 ? 'opacity-30 cursor-not-allowed' : ''}" ${currentPage <= 1 ? 'disabled' : ''} onclick="window.SuspendedUsers.goPage(-1)">← Previous</button>`;
        
        for (let i = 1; i <= pages; i++) {
            if (i > 5 && i < pages - 1) { 
                if (i === 6) html += '<span class="px-2 opacity-50">...</span>'; 
                continue; 
            }
            html += `<button class="w-8 h-8 flex items-center justify-center rounded-lg text-[11px] font-bold transition-all ${i === currentPage ? 'bg-primary text-white' : 'hover:bg-slate-100 dark:hover:bg-white/5'}" onclick="window.SuspendedUsers.goPageTo(${i})">${i}</button>`;
        }
        
        if (pages > 1) {
            html += `<button class="admin-btn-secondary px-3 py-1.5 text-[11px] rounded-lg ${currentPage >= pages ? 'opacity-30 cursor-not-allowed' : ''}" ${currentPage >= pages ? 'disabled' : ''} onclick="window.SuspendedUsers.goPage(1)">Next →</button>`;
        }
        
        pagControls.innerHTML = `<div class="flex items-center gap-1.5">${html}</div>`;
    }

    // Exported Object
    window.SuspendedUsers = {
        goPage: (d) => { currentPage += d; loadSuspendedUsers(); },
        goPageTo: (p) => { currentPage = p; loadSuspendedUsers(); },
        
        resetFilters: () => { 
            if (searchInput) searchInput.value = ''; 
            if (startDateInput) startDateInput.value = '';
            if (endDateInput) endDateInput.value = '';
            if (reasonFilter) {
                reasonFilter.value = '';
                const dropdown = reasonFilter.closest('.finzo-dropdown');
                if (dropdown) {
                    const defaultOpt = dropdown.querySelector('.finzo-dropdown-option[data-value=""]');
                    const label = dropdown.querySelector('.dropdown-label');
                    if (label && defaultOpt) label.textContent = defaultOpt.textContent.trim();
                    dropdown.querySelectorAll('.finzo-dropdown-option').forEach(opt => opt.classList.remove('is-selected'));
                    if (defaultOpt) defaultOpt.classList.add('is-selected');
                }
            }
            currentPage = 1; 
            loadSuspendedUsers(); 
        },

        applyFilters: () => {
            currentPage = 1;
            loadSuspendedUsers();
        },

        openReinstate: (id, name) => {
            pendingReinstateId = id;
            if (reinstateModalText) {
                reinstateModalText.innerHTML = `You are reinstating <strong>${name}</strong>'s account. Operational access will be restored immediately.`;
            }
            if (reinstateModal) {
                reinstateModal.classList.remove('hidden');
                reinstateModal.classList.add('flex');
            }
        },

        closeReinstateModal: () => {
            if (reinstateModal) {
                reinstateModal.classList.add('hidden');
                reinstateModal.classList.remove('flex');
            }
        },

        confirmReinstate: async () => {
            if (!pendingReinstateId) return;
            const btn = reinstateConfirmBtn;
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="material-symbols-outlined animate-spin" style="font-size:15px">autorenew</span> Processing...';
            
            try {
                const response = await window.api.post('/admin/user-action.php', {
                    user_id: pendingReinstateId,
                    action: 'reinstate'
                });

                if (response.status === 'success' || response.success) {
                    window.SuspendedUsers.closeReinstateModal();
                    window.portal.showToast('Account reinstated successfully.', 'success');
                    loadSuspendedUsers();
                } else {
                    alert(response.message || 'Action failed.');
                }
            } catch (error) {
                console.error('Reinstate failed:', error);
                alert('An error occurred during reinstatement.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        }
    };

    // Initialization
    document.addEventListener('DOMContentLoaded', () => {
        loadSuspendedUsers();

        // Bind Search Input for real-time filtering if desired
        if (searchInput) {
            searchInput.addEventListener('input', window.FinzoUtils.debounce(() => {
                currentPage = 1;
                loadSuspendedUsers();
            }, 500));
        }

        // Bind Date & Reason Listeners
        if (startDateInput) startDateInput.addEventListener('change', window.SuspendedUsers.applyFilters);
        if (endDateInput) endDateInput.addEventListener('change', window.SuspendedUsers.applyFilters);
        if (reasonFilter) reasonFilter.addEventListener('change', window.SuspendedUsers.applyFilters);
    });

})();
