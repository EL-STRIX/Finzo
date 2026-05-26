/**
 * Finzo Admin — Deleted Users Handler
 * Manages the soft-deleted users record, filtering, and recovery.
 */

(function() {
    let currentPage = 1;
    let pendingUserId = null;

    // DOM Elements
    const tableBody = document.getElementById('deleted-table-body');
    const tableContainer = document.querySelector('.du-table')?.parentElement;
    const emptyState = document.getElementById('empty-state');
    const filterSearch = document.getElementById('filter-search');
    const filterDateFrom = document.getElementById('filter-date-from');
    const filterDateTo = document.getElementById('filter-date-to');
    const applyFiltersBtn = document.getElementById('apply-filters');
    const resetFiltersBtn = document.getElementById('reset-filters');
    const pagInfo = document.querySelector('.text-xs.font-bold.opacity-40');
    const pagControls = document.querySelector('.flex.items-center.gap-2:not(#status-tabs)');

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
            const response = await window.api.get('/admin/deleted-users.php', {
                search: filterSearch ? filterSearch.value : '',
                date_from: filterDateFrom ? filterDateFrom.value : '',
                date_to: filterDateTo ? filterDateTo.value : '',
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
                tableBody.innerHTML = `<tr><td colspan="8" class="py-20 text-center font-bold opacity-40 text-red-500">Failed to load data.</td></tr>`;
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

        tableBody.innerHTML = users.map((u, i) => {
            const initials = u.initials || u.full_name.charAt(0);
            const isSavings = (u.account_type || '').toLowerCase() === 'savings';
            const gradient = window.FinzoUtils.getAvatarGradient(initials);
            const rowNum = String((currentPage - 1) * 10 + i + 1).padStart(2, '0');
            
            return `
            <tr class="hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-colors">
                <td class="du-num tracking-widest opacity-30 text-[11px] font-bold">${rowNum}</td>
                <td>
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-10 h-10 rounded-xl text-white font-black text-xs uppercase" style="background:${gradient}">${initials}</div>
                        <div style="display:flex; flex-direction:column;">
                            <span class="user-name font-bold text-sm text-slate-800 dark:text-slate-200">${u.full_name}</span>
                            <span class="user-email text-[11px] opacity-60 font-mono">${u.customer_id || 'FNZ-USR-PENDING'}</span>
                        </div>
                    </div>
                </td>
                <td><span class="font-mono text-xs opacity-80">${u.phone || 'N/A'}</span></td>
                <td><span class="px-2 py-1 bg-slate-100 dark:bg-white/5 rounded-lg text-[11px] font-mono opacity-80 flex items-center gap-1.5 w-fit border border-slate-200 dark:border-white/5"><span class="material-symbols-outlined" style="font-size:14px;color:#94a3b8;">account_balance</span>${u.account_no || 'N/A'}</span></td>
                <td><span class="status-badge ${isSavings ? 'badge-success' : 'badge-primary'} flex items-center gap-1.5 w-fit text-[10px]"><span class="material-symbols-outlined" style="font-size:12px;">${isSavings ? 'savings' : 'account_balance_wallet'}</span>${(u.account_type || 'N/A').toUpperCase()}</span></td>
                <td><span class="text-[11px] opacity-60 font-semibold">${u.deleted_at || 'N/A'}</span></td>
                <td>
                    <div class="bg-amber-500/5 dark:bg-amber-500/10 p-2 rounded-xl border border-amber-500/10 max-w-[150px]">
                        <span class="text-[11px] opacity-80 font-bold block truncate" title="${u.deletion_reason || 'Admin Action'}">${u.deletion_reason || 'Admin Action'}</span>
                    </div>
                </td>
                <td class="text-right">
                    <div class="flex justify-end gap-2">
                        <button onclick="window.DeletedUsers.openRecoverModal('${u.full_name.replace(/'/g, "\\'")}', '${u.account_no}', ${u.id})" class="p-2 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 text-emerald-500 rounded-lg transition-colors" title="Recover Account">
                            <span class="material-symbols-outlined" style="font-size:18px;">restore</span>
                        </button>
                        <button onclick="window.DeletedUsers.openDetailsModal('${u.full_name.replace(/'/g, "\\'")}', '${u.account_no}', '${u.email}', '${u.phone || ''}', '${u.account_type}', '${u.created_at}', '${u.balance}', '${u.deleted_at}')" class="p-2 hover:bg-slate-100 dark:hover:bg-white/5 text-slate-400 rounded-lg transition-colors" title="View Details">
                            <span class="material-symbols-outlined" style="font-size:18px;">visibility</span>
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

        let pagHtml = `<button class="admin-btn-secondary px-3 py-1.5 text-[11px] rounded-lg ${currentPage <= 1 ? 'opacity-30 cursor-not-allowed' : ''}" ${currentPage <= 1 ? 'disabled' : ''} onclick="window.DeletedUsers.goPage(-1)">← Previous</button>`;
        
        for (let i = 1; i <= pages; i++) {
            if (pages > 5 && i > 3 && i < pages) {
                if (i === 4) pagHtml += `<span class="px-2 opacity-30">...</span>`;
                continue;
            }
            pagHtml += `<button onclick="window.DeletedUsers.goToPage(${i})" class="w-8 h-8 flex items-center justify-center rounded-lg text-[11px] font-bold transition-all ${i === currentPage ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'hover:bg-slate-100 dark:hover:bg-white/5'}" ${i === currentPage ? 'style="background:#002645;"' : ''}>${i}</button>`;
        }
        
        pagHtml += `<button class="admin-btn-secondary px-3 py-1.5 text-[11px] rounded-lg ${currentPage >= pages ? 'opacity-30 cursor-not-allowed' : ''}" ${currentPage >= pages ? 'disabled' : ''} onclick="window.DeletedUsers.goPage(1)">Next →</button>`;
        
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
            currentPage = 1;
            loadDeletedUsers();
        },

        openRecoverModal: (name, acc, id) => {
            pendingUserId = id;
            const nameEl = document.getElementById('recover-user-name');
            const accEl = document.getElementById('recover-user-acc');
            const modal = document.getElementById('recoverModal');

            if (nameEl) nameEl.textContent = name;
            if (accEl) accEl.textContent = acc;
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        },

        confirmRecovery: async () => {
            if (!pendingUserId) return;
            try {
                const response = await window.api.post('/admin/user-action.php', {
                    user_id: pendingUserId,
                    action: 'recover'
                });
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

        openDetailsModal: (name, acc, email, phone, type, reg, balance, deleted) => {
            const nameEls = document.querySelectorAll('.details-name');
            const accEls = document.querySelectorAll('.details-acc');
            const emailEls = document.querySelectorAll('.details-email');
            const phoneEls = document.querySelectorAll('.details-phone');
            const typeEls = document.querySelectorAll('.details-type');
            const regEls = document.querySelectorAll('.details-reg');
            const balanceEls = document.querySelectorAll('.details-balance');
            
            nameEls.forEach(el => el.textContent = name);
            accEls.forEach(el => el.textContent = acc);
            emailEls.forEach(el => el.textContent = email);
            phoneEls.forEach(el => el.textContent = phone || 'N/A');
            typeEls.forEach(el => el.textContent = type ? type.charAt(0).toUpperCase() + type.slice(1) : 'N/A');
            regEls.forEach(el => el.textContent = new Date(reg).toLocaleDateString());
            balanceEls.forEach(el => el.textContent = window.FinzoUtils.formatCurrency(balance || 0));
            
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
            // Placeholder if needed, for now just call openRecoverModal or similar
            const name = document.querySelector('.details-name')?.textContent;
            const acc = document.querySelector('.details-acc')?.textContent;
            window.DeletedUsers.closeModal('detailsModal');
            window.DeletedUsers.openRecoverModal(name, acc, pendingUserId);
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

        // Tab Filtering
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.tab-btn').forEach(b => {
                    b.classList.remove('bg-primary', 'text-white', 'shadow-md');
                    b.classList.add('text-slate-500');
                });
                btn.classList.add('bg-primary', 'text-white', 'shadow-md');
                btn.classList.remove('text-slate-500');
                
                // Logic for specific status filtering can be added here
                // For now it just reloads with general filters
                currentPage = 1;
                loadDeletedUsers();
            });
        });
        
        // Expose helper to global for detail modal button
        window.recoverFromDetails = window.DeletedUsers.recoverFromDetails;
    });

})();
