/**
 * Finzo Admin — Deleted Users Handler
 * Manages the soft-deleted users record, filtering, and recovery.
 */

(function() {
    let currentPage = 1;
    let pendingUserId = null;

    // DOM Elements
    const tableBody = document.getElementById('deleted-table-body');
    const tableContainer = tableBody ? tableBody.closest('.overflow-x-auto') : null;
    const emptyState = document.getElementById('empty-state');
    const filterSearch = document.getElementById('filter-search');
    const filterDateFrom = document.getElementById('filter-date-from');
    const filterDateTo = document.getElementById('filter-date-to');
    const filterType = document.getElementById('filter-type');
    const applyFiltersBtn = document.getElementById('apply-filters');
    const resetFiltersBtn = document.getElementById('reset-filters');
    const pagInfo = document.getElementById('pagination-info');
    const pagControls = document.getElementById('pagination-controls');

    /**
     * Load deleted users from API
     */
    async function loadDeletedUsers() {
        if (tableBody) {
            tableBody.innerHTML = `
                <tr><td colspan="8"><div class="skeleton-row"></div></td></tr>
                <tr><td colspan="8"><div class="skeleton-row"></div></td></tr>
                <tr><td colspan="8"><div class="skeleton-row"></div></td></tr>
            `;
        }

        try {
            const response = await window.api.get('/users/deleted', {
                search: filterSearch ? filterSearch.value : '',
                date_from: filterDateFrom ? filterDateFrom.value : '',
                date_to: filterDateTo ? filterDateTo.value : '',
                account_type: filterType ? filterType.value : '',
                page: currentPage,
                per_page: 10
            });

            if (response.status === 'success' || response.success) {
                const data = response.data || response;
                renderTable(data.users || [], data.total || 0, data.pages || 1);
            } else {
                if (tableContainer) tableContainer.style.display = 'none';
                if (emptyState) {
                    emptyState.classList.remove('hidden');
                    emptyState.classList.add('flex');
                }
            }
        } catch (error) {
            console.error('Failed to load deleted users:', error);
            if (tableBody) {
                tableBody.innerHTML = `<tr><td colspan="8" class="py-12 text-center"><div class="flex flex-col items-center justify-center"><span class="material-symbols-outlined text-4xl text-text-muted/30 mb-3" aria-hidden="true">inventory_2</span><div class="text-sm font-bold text-text-muted">Failed to load data.</div></div></td></tr>`;
            }
        }
    }

    /**
     * Render the deleted users table
     */
    function renderTable(users, total, pages) {
        if (!tableBody) return;

        if (!users || !users.length) {
            if (tableContainer) tableContainer.style.display = 'none';
            if (emptyState) {
                emptyState.classList.remove('hidden');
                emptyState.classList.add('flex');
            }
            if (pagInfo) pagInfo.textContent = 'Showing 0-0 of 0 deleted users';
            if (pagControls) pagControls.innerHTML = '';
            return;
        }

        if (tableContainer) tableContainer.style.display = 'block';
        if (emptyState) {
            emptyState.classList.add('hidden');
            emptyState.classList.remove('flex');
        }

        window.DeletedUsers.usersList = users;

        tableBody.innerHTML = users.map((u, i) => {
            const initials = u.initials || u.full_name.charAt(0);
            const isSavings = (u.account_type || '').toLowerCase() === 'savings';
            const gradient = window.FinzoUtils.getAvatarGradient(initials);
            const rowNum = String((currentPage - 1) * 10 + i + 1).padStart(2, '0');
            
            const avatarHtml = u.avatar_url 
                ? `<img src="${u.avatar_url}" class="w-10 h-10 rounded-xl object-cover border border-slate-200 dark:border-white/10" alt="DP">`
                : `<div class="flex items-center justify-center w-10 h-10 rounded-xl text-white font-black text-xs uppercase" >${initials}</div>`;

            return `
            <tr class="hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-colors">
                <td class="du-num tracking-widest opacity-30 text-[11px] font-bold">${rowNum}</td>
                <td>
                    <div class="flex items-center gap-3">
                        ${avatarHtml}
                        <div style="display:flex; flex-direction:column;">
                            <span class="user-name font-bold text-sm text-slate-800 dark:text-slate-200">${u.full_name}</span>
                            <span class="user-email text-[11px] opacity-60 font-mono">${u.customer_id || 'FNZ-USR-PENDING'}</span>
                        </div>
                    </div>
                </td>
                <td><span class="font-mono text-xs opacity-80">${u.phone || 'N/A'}</span></td>
                <td><span class="px-2 py-1 bg-slate-100 dark:bg-white/5 rounded-lg text-[11px] font-mono opacity-80 flex items-center gap-1.5 w-fit border border-slate-200 dark:border-white/5"><span class="material-symbols-outlined" style="font-size:14px;color:#94a3b8;" aria-hidden="true">account_balance</span>${u.account_no || 'N/A'}</span></td>
                <td><span class="status-badge ${isSavings ? 'badge-success' : 'badge-primary'} flex items-center gap-1.5 w-fit text-[10px]"><span class="material-symbols-outlined" style="font-size:12px;" aria-hidden="true">${isSavings ? 'savings' : 'account_balance_wallet'}</span>${(u.account_type || 'N/A').toUpperCase()}</span></td>
                <td><span class="text-[11px] opacity-60 font-semibold">${u.deleted_at || 'N/A'}</span></td>
                <td>
                    <div class="bg-amber-500/5 dark:bg-amber-500/10 p-2 rounded-xl border border-amber-500/10 max-w-[150px]">
                        <span class="text-[11px] opacity-80 font-bold block truncate" title="${u.deletion_reason || 'Admin Action'}">${u.deletion_reason || 'Admin Action'}</span>
                    </div>
                </td>
                <td class="text-right">
                    <div class="flex justify-end gap-2">
                        <button onclick="window.DeletedUsers.openRecoverModal(${u.id})" class="p-2 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 text-emerald-500 rounded-lg transition-colors" title="Recover Account">
                            <span class="material-symbols-outlined" style="font-size:18px;" aria-hidden="true">restore</span>
                        </button>
                        <button onclick="window.DeletedUsers.openDetailsModal(${u.id})" class="p-2 hover:bg-slate-100 dark:hover:bg-white/5 text-slate-400 rounded-lg transition-colors" title="View Details">
                            <span class="material-symbols-outlined" style="font-size:18px;" aria-hidden="true">visibility</span>
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

        const start = (currentPage - 1) * 10 + 1;
        const end = Math.min(currentPage * 10, total);
        pagInfo.textContent = total ? `Showing ${start}-${end} of ${total} deleted users` : 'No results found';

        let pagHtml = `<button class="btn btn-secondary px-3 py-1.5 text-[11px] rounded-lg ${currentPage <= 1 ? 'opacity-30 cursor-not-allowed' : ''}" ${currentPage <= 1 ? 'disabled' : ''} onclick="window.DeletedUsers.goPage(-1)">← Previous</button>`;
        
        for (let i = 1; i <= pages; i++) {
            if (pages > 5 && i > 3 && i < pages) {
                if (i === 4) pagHtml += `<span class="px-2 opacity-30">...</span>`;
                continue;
            }
            pagHtml += `<button onclick="window.DeletedUsers.goToPage(${i})" class="w-8 h-8 flex items-center justify-center rounded-lg text-[11px] font-bold transition-all ${i === currentPage ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'hover:bg-slate-100 dark:hover:bg-white/5'}" ${i === currentPage ? '' : ''}>${i}</button>`;
        }
        
        pagHtml += `<button class="btn btn-secondary px-3 py-1.5 text-[11px] rounded-lg ${currentPage >= pages ? 'opacity-30 cursor-not-allowed' : ''}" ${currentPage >= pages ? 'disabled' : ''} onclick="window.DeletedUsers.goPage(1)">Next →</button>`;
        
        pagControls.innerHTML = pagHtml;
    }

    // Exported Object
    window.DeletedUsers = {
        goPage: (d) => { currentPage += d; loadDeletedUsers(); },
        goToPage: (p) => { currentPage = p; loadDeletedUsers(); },
        
        applyFilters: () => {
            currentPage = 1;
            loadDeletedUsers();
        },

        resetFilters: () => {
            if (filterSearch) filterSearch.value = '';
            if (filterDateFrom) filterDateFrom.value = '';
            if (filterDateTo) filterDateTo.value = '';
            if (filterType) filterType.value = '';
            currentPage = 1;
            loadDeletedUsers();
        },

        openRecoverModal: (id) => {
            pendingUserId = id;
            const u = (window.DeletedUsers.usersList || []).find(x => x.id == id);
            const nameEl = document.getElementById('recover-user-name');
            const accEl = document.getElementById('recover-user-acc');
            const modal = document.getElementById('recoverModal');

            if (nameEl && u) nameEl.textContent = u.full_name;
            if (accEl && u) accEl.textContent = u.account_no || 'N/A';
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        },

        confirmRecovery: async () => {
            if (!pendingUserId) return;
            const option = document.querySelector('input[name="restore-option"]:checked')?.value || 'full';
            try {
                const response = await window.api.post('/users/' + pendingUserId + '/recover', { balance_option: option });
                if (response.status === 'success' || response.success) {
                    window.portal.showToast('User account recovered successfully', 'success');
                    window.DeletedUsers.closeModal('recoverModal');
                    loadDeletedUsers();
                } else {
                    window.portal.showToast(response.message || 'Failed to recover account', 'error');
                }
            } catch (error) {
                console.error('Recovery failed:', error);
                window.portal.showToast('Server error during recovery', 'error');
            }
        },

        openDetailsModal: (id) => {
            const u = (window.DeletedUsers.usersList || []).find(x => x.id == id);
            if (!u) return;
            pendingUserId = u.id; // for recovery

            const nameEls = document.querySelectorAll('.details-name');
            const accEls = document.querySelectorAll('.details-acc');
            const emailEls = document.querySelectorAll('.details-email');
            const phoneEls = document.querySelectorAll('.details-phone');
            const typeEls = document.querySelectorAll('.details-type');
            const regEls = document.querySelectorAll('.details-reg');
            const balanceEls = document.querySelectorAll('.details-balance');
            
            const avatarContainer = document.getElementById('details-avatar-container');
            if (avatarContainer) {
                if (u.avatar_url) {
                    avatarContainer.innerHTML = `<img src="${u.avatar_url}" class="w-full h-full object-cover rounded-2xl">`;
                    avatarContainer.style.background = 'none';
                } else {
                    const initials = u.initials || u.full_name.charAt(0);
                    avatarContainer.innerHTML = initials;
                    avatarContainer.style.background = window.FinzoUtils.getAvatarGradient(initials);
                }
            }

            nameEls.forEach(el => el.textContent = u.full_name);
            accEls.forEach(el => el.textContent = u.account_no || 'N/A');
            emailEls.forEach(el => el.textContent = u.email || 'N/A');
            phoneEls.forEach(el => el.textContent = u.phone || 'N/A');
            typeEls.forEach(el => el.textContent = u.account_type ? u.account_type.charAt(0).toUpperCase() + u.account_type.slice(1) : 'N/A');
            regEls.forEach(el => el.textContent = u.created_at ? new Date(u.created_at).toLocaleDateString() : 'N/A');
            balanceEls.forEach(el => el.textContent = window.FinzoUtils.formatINR(u.balance || 0));
            
            const modal = document.getElementById('detailsModal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        },

        closeModal: (id) => {
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        },

        recoverFromDetails: () => {
            window.DeletedUsers.closeModal('detailsModal');
            window.DeletedUsers.openRecoverModal(pendingUserId);
        },

        deletePermanentlyFromDetails: async () => {
            if (!pendingUserId) return;
            if (!confirm('Are you sure you want to PERMANENTLY delete this user? This action cannot be undone and all data will be lost forever.')) return;
            
            try {
                const response = await window.api.patch('/users/' + pendingUserId, { action: 'delete_permanent' });
                if (response.status === 'success' || response.success) {
                    window.portal.showToast('User account permanently deleted', 'success');
                    window.DeletedUsers.closeModal('detailsModal');
                    loadDeletedUsers();
                } else {
                    window.portal.showToast(response.message || 'Failed to delete account', 'error');
                }
            } catch (error) {
                console.error('Delete failed:', error);
                window.portal.showToast('Server error during deletion', 'error');
            }
        },

        deleteAccountOnly: async (id) => {
            if (!confirm('Are you sure you want to PERMANENTLY delete this account (but keep the user)? This action cannot be undone.')) return;
            
            try {
                const response = await window.api.patch('/users/' + id, { action: 'delete_account_only' });
                if (response.status === 'success' || response.success) {
                    window.portal.showToast('Account permanently deleted', 'success');
                    loadDeletedUsers();
                } else {
                    window.portal.showToast(response.message || 'Failed to delete account', 'error');
                }
            } catch (error) {
                console.error('Delete failed:', error);
                window.portal.showToast('Server error during deletion', 'error');
            }
        }
    };

    // Initialization
    document.addEventListener('DOMContentLoaded', () => {
        loadDeletedUsers();

        // Search Input Listener (Debounced)
        if (filterSearch) {
            filterSearch.addEventListener('input', window.FinzoUtils.debounce(() => {
                currentPage = 1;
                loadDeletedUsers();
            }, 500));
        }

        // Date Filters change listeners
        if (filterDateFrom) {
            filterDateFrom.addEventListener('change', () => {
                currentPage = 1;
                loadDeletedUsers();
            });
        }
        if (filterDateTo) {
            filterDateTo.addEventListener('change', () => {
                currentPage = 1;
                loadDeletedUsers();
            });
        }
        if (filterType) {
            filterType.addEventListener('change', () => {
                currentPage = 1;
                loadDeletedUsers();
            });
        }


        
        // Expose helper to global for detail modal button
        window.recoverFromDetails = window.DeletedUsers.recoverFromDetails;
    });

})();
