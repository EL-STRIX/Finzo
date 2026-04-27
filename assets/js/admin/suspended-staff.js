/**
 * Finzo Admin — Suspended Staff Handler
 * Manages the table of suspended staff, filtering, and reinstatement.
 */

(function() {
    let currentPage = 1;
    let pendingId = null;

    // DOM Elements
    const searchInput = document.getElementById('search-input');
    const tableBody = document.getElementById('table-body');
    const pagInfo = document.getElementById('pag-info');
    const pagControls = document.getElementById('pag-controls');
    const startDateInput = document.getElementById('start-date');
    const endDateInput = document.getElementById('end-date');
    const reasonFilter = document.getElementById('reason-filter');
    const reinstateModal = document.getElementById('reinstate-modal');
    const modalText = document.getElementById('modal-text');
    const confirmBtn = document.getElementById('confirm-btn');

    /**
     * Load suspended staff from API
     */
    async function loadSuspendedStaff() {
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
            const response = await window.api.get('/admin/suspended-staff.php', {
                search: search,
                reason: reason,
                start_date: startDate,
                end_date: endDate,
                page: currentPage,
                per_page: 10
            });

            if (response.status === 'success' || response.success) {
                const data = response.data || response;
                renderTable(data.staff || []);
                updatePagination(data.total || 0, data.pages || 1);
            } else {
                if (tableBody) {
                    tableBody.innerHTML = `<tr><td colspan="9" class="py-20 text-center font-bold opacity-40">${response.message || 'No suspended staff found.'}</td></tr>`;
                }
                if (pagInfo) pagInfo.textContent = 'No results';
                if (pagControls) pagControls.innerHTML = '';
            }
        } catch (error) {
            console.error('Failed to load suspended staff:', error);
            if (tableBody) {
                tableBody.innerHTML = `<tr><td colspan="9" class="py-20 text-center font-bold opacity-40 text-red-500">Failed to load data.</td></tr>`;
            }
        }
    }

    /**
     * Render the staff table
     */
    function renderTable(staff) {
        if (!tableBody) return;

        if (!staff || !staff.length) {
            tableBody.innerHTML = `<tr><td colspan="9"><div class="empty-state"><span class="material-symbols-outlined">work_off</span><div class="empty-title">No suspended staff found</div><div class="empty-sub">Try adjusting your search or filters</div></div></td></tr>`;
            return;
        }

        tableBody.innerHTML = staff.map((s, i) => {
            const initials = s.initials || (s.full_name ? s.full_name.charAt(0) : '?');
            const rowNum = String((currentPage - 1) * 10 + i + 1).padStart(2, '0');
            const gradient = window.FinzoUtils.getAvatarGradient(initials);
            
            return `
            <tr class="hover:bg-slate-50/50 dark:hover:bg-white/5 transition-colors text-[13px]">
                <td style="font-size:11px;font-weight:800;color:var(--text-secondary)">${rowNum}</td>
                <td class="py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-black text-xs shadow-lg uppercase" style="background:${gradient}">
                            ${initials}
                        </div>
                        <div>
                            <div class="font-headline font-bold text-sm text-black dark:text-white">${s.full_name}</div>
                            <div class="text-[11px] font-bold text-slate-400 -mt-0.5">${s.email}</div>
                        </div>
                    </div>
                </td>
                <td><span class="mono font-mono text-xs opacity-70">${s.staff_id}</span></td>
                <td style="font-weight:600 font-bold">${s.branch_name || 'N/A'}</td>
                <td><span class="status-badge ${s.status_badge || 'badge-warn'}">${s.suspension_reason || 'Suspended'}</span></td>
                <td style="font-weight:600">${s.suspended_at || 'N/A'}</td>
                <td style="color:var(--text-secondary)">${s.suspended_by || 'Admin'}</td>
                <td><span class="status-badge badge-secondary">${s.duration || 'Permanent'}</span></td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                    <div class="flex items-center justify-center gap-2">
                        <a href="admin-suspended-staff-detail.html?id=${s.id}" class="p-2 hover:bg-slate-100 dark:hover:bg-white/5 rounded-lg transition-colors text-slate-400 hover:text-primary" title="View Details">
                            <span class="material-symbols-outlined" style="font-size:18px">visibility</span>
                        </a>
                        <button class="p-2 hover:bg-slate-100 dark:hover:bg-white/5 rounded-lg transition-colors text-slate-400 hover:text-emerald-500" 
                                onclick="window.SuspendedStaff.openReinstate('${s.id}', '${s.full_name.replace(/'/g, "\\'")}')"
                                title="Reinstate Staff">
                            <span class="material-symbols-outlined" style="font-size:18px">lock_open</span>
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

        const start = (currentPage - 1) * 10 + 1;
        const end = Math.min(currentPage * 10, total);
        pagInfo.textContent = total ? `Showing ${start}–${end} of ${total} suspended staff` : 'No results';

        let pcHtml = `<button class="admin-btn-secondary px-3 py-1.5 text-[11px] rounded-lg ${currentPage <= 1 ? 'opacity-30 cursor-not-allowed' : ''}" ${currentPage <= 1 ? 'disabled' : ''} onclick="window.SuspendedStaff.goPage(-1)">← Previous</button>`;
        pcHtml += `<div class="flex items-center gap-1.5">`;
        for (let i = 1; i <= pages; i++) {
            if (i > 5 && i < pages - 1) { 
                if (i === 6) pcHtml += '<span class="px-2 opacity-50">...</span>'; 
                continue; 
            }
            pcHtml += `<button class="w-8 h-8 flex items-center justify-center rounded-lg text-[11px] font-bold transition-all ${i === currentPage ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'hover:bg-slate-100 dark:hover:bg-white/5'}" onclick="window.SuspendedStaff.goPageTo(${i})">${i}</button>`;
        }
        pcHtml += `</div>`;
        pcHtml += `<button class="admin-btn-secondary px-3 py-1.5 text-[11px] rounded-lg ${currentPage >= pages ? 'opacity-30 cursor-not-allowed' : ''}" ${currentPage >= pages ? 'disabled' : ''} onclick="window.SuspendedStaff.goPage(1)">Next →</button>`;
        
        pagControls.innerHTML = `<div class="flex items-center gap-2">${pcHtml}</div>`;
    }

    // Exported Object
    window.SuspendedStaff = {
        goPage: (d) => { currentPage += d; loadSuspendedStaff(); },
        goPageTo: (p) => { currentPage = p; loadSuspendedStaff(); },
        
        triggerFilter: () => {
            currentPage = 1;
            loadSuspendedStaff();
        },

        openReinstate: (id, name) => {
            pendingId = id;
            if (modalText) {
                modalText.innerHTML = `You are reinstating <strong>${name}</strong>'s staff account. Their status will immediately return to <strong>Active</strong> and they can resume processing customer deposits and withdrawals.`;
            }
            if (reinstateModal) {
                reinstateModal.classList.remove('hidden');
                reinstateModal.classList.add('flex');
            }
        },

        closeModal: () => {
            if (reinstateModal) {
                reinstateModal.classList.add('hidden');
                reinstateModal.classList.remove('flex');
            }
        },

        confirmReinstate: async () => {
            if (!pendingId) return;
            const btn = confirmBtn;
            if (!btn) return;
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="material-symbols-outlined animate-spin" style="font-size:15px">autorenew</span> Processing...';
            
            try {
                const response = await window.api.post('/admin/staff-action.php', {
                    action: 'reinstate',
                    staff_id: pendingId
                });

                if (response.status === 'success' || response.success) {
                    window.SuspendedStaff.closeModal();
                    window.portal.showToast('Staff account reinstated successfully.', 'success');
                    loadSuspendedStaff();
                } else {
                    window.portal.showToast(response.message || 'Action failed', 'error');
                }
            } catch (error) {
                console.error('Reinstate failed:', error);
                window.portal.showToast('Server error occurred', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        }
    };

    // Initialization
    document.addEventListener('DOMContentLoaded', () => {
        loadSuspendedStaff();

        // Search Listener
        if (searchInput) {
            searchInput.addEventListener('input', window.FinzoUtils.debounce(() => {
                window.SuspendedStaff.triggerFilter();
            }, 300));
        }

        // Date & Reason Listeners
        if (startDateInput) startDateInput.addEventListener('change', window.SuspendedStaff.triggerFilter);
        if (endDateInput) endDateInput.addEventListener('change', window.SuspendedStaff.triggerFilter);
        if (reasonFilter) reasonFilter.addEventListener('change', window.SuspendedStaff.triggerFilter);
    });

})();
