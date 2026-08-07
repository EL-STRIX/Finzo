/**
 * Finzo Admin — Locked Accounts Handler
 * Manages the queue of locked accounts, filtering, and temporary password generation.
 */

(function() {
    let allData = [];
    let currentPage = 1;
    let pendingId = null;
    let generatedPass = '';
    const ROWS_PP = 6;

    // DOM Elements
    const searchInput = document.getElementById('search-input');
    const tableBody = document.getElementById('table-body');
    const pagInfo = document.getElementById('pag-info');
    const pagControls = document.getElementById('pag-controls');
    const dateFilter = document.getElementById('date-filter');
    const typeFilter = document.getElementById('type-filter');
    
    // Modal Elements
    const unlockModal = document.getElementById('unlock-modal');
    const unlockModalIconBox = document.getElementById('unlock-modal-icon-box');
    const unlockModalIcon = document.getElementById('unlock-modal-icon');
    const unlockModalTitle = document.getElementById('unlock-modal-title');
    const unlockModalText = document.getElementById('unlock-modal-text');
    const genPassSection = document.getElementById('gen-pass-section');
    const genPassValue = document.getElementById('gen-pass-value');
    const generateBtn = document.getElementById('generate-btn');
    const unlockModalActions = document.getElementById('unlock-modal-actions');
    const unlockDoneActions = document.getElementById('unlock-done-actions');
    const copyBtn = document.getElementById('copy-btn');

    /**
     * Load locked accounts from API
     */
    async function loadData() {
        if (tableBody) {
            tableBody.innerHTML = `<tr><td colspan="10" class="py-10"><div class="finzo-spinner mx-auto border-primary"></div></td></tr>`;
        }
        
        try {
            const search = document.getElementById('search-input')?.value || '';
            const typeFilter = document.getElementById('type-filter')?.value || '';
            const statusFilter = document.getElementById('status-filter')?.value || '';
            const dateFilter = document.getElementById('date-filter')?.value || '';

            let url = `/users/locked?page=${currentPage}&per_page=${ROWS_PP}`;
            if (search) url += `&search=${encodeURIComponent(search)}`;
            if (typeFilter) url += `&type=${encodeURIComponent(typeFilter)}`;
            if (statusFilter) url += `&status=${encodeURIComponent(statusFilter)}`;
            if (dateFilter) url += `&date=${encodeURIComponent(dateFilter)}`;

            const response = await window.api.get(url);
            
            if (response.success) {
                const data = response.data || response;
                allData = (data.accounts || []).map(acc => ({
                    id: acc.id,
                    name: acc.full_name,
                    email: acc.email,
                    initials: acc.initials || acc.full_name.charAt(0),
                    profile_photo: acc.profile_picture || null,
                    accNo: acc.account_no || 'N/A',
                    accType: acc.account_type === 'savings' ? 'Savings' : 'Current',
                    attempts: acc.failed_attempts,
                    lockedDate: acc.locked_at || 'N/A',
                    lockTime: acc.lock_time || 'N/A',
                    tempSent: acc.status === 'unlocked' || acc.status === 'done', 
                    statusBadge: acc.status_badge || 'badge-warn',
                    statusText: acc.status_text || 'Pending'
                }));
                
                renderTable(data.total || 0, data.pages || 1);
            } else {
                if (tableBody) {
                    tableBody.innerHTML = `<tr><td colspan="10"><div class="empty-state"><span class="material-symbols-outlined">lock_open</span><div class="empty-title">No locked accounts found</div><div class="empty-sub">${response.message || 'All accounts are currently accessible.'}</div></div></td></tr>`;
                }
                if (pagInfo) pagInfo.textContent = 'No results found';
                if (pagControls) pagControls.innerHTML = '';
            }
        } catch (error) {
            console.error('Failed to load locked accounts:', error);
            if (tableBody) {
                tableBody.innerHTML = `<tr><td colspan="10" class="py-20 text-center font-bold opacity-40 text-red-500">Failed to load data.</td></tr>`;
            }
        }
    }

    /**
     * Render the visual display for failed attempts
     */
    function attemptsDisplay(count) {
        const dots = Array(5).fill(0).map((_, i) => `<div class="attempt-dot ${i < count ? '' : 'empty'}"></div>`).join('');
        return `<div class="attempt-dots">${dots}<span style="font-size:11px;font-weight:800;color:var(--error);margin-left:6px;">${count}/5</span></div>`;
    }

    /**
     * Render the accounts table
     */
    function renderTable(total, totalPages) {
        if (!tableBody) return;

        if (!allData || !allData.length) {
            tableBody.innerHTML = `<tr><td colspan="10"><div class="empty-state"><span class="material-symbols-outlined">lock_open</span><div class="empty-title">No locked accounts found</div><div class="empty-sub">All accounts are currently accessible.</div></div></td></tr>`;
            if (pagInfo) pagInfo.textContent = 'No results found';
            if (pagControls) pagControls.innerHTML = '';
            return;
        }

        tableBody.innerHTML = allData.map((r, i) => {
            const gradient = window.FinzoUtils.getAvatarGradient(r.initials);
            const rowNum = String((currentPage - 1) * ROWS_PP + i + 1).padStart(2, '0');
            
            return `
            <tr class="hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-colors" style="${r.tempSent ? 'opacity:0.8; ' : ''}">
                <td style="font-size:11px;font-weight:800;color:var(--text-secondary);">${rowNum}</td>
                <td>
                    <div class="user-cell flex items-center gap-3">
                        ${r.profile_photo 
                            ? `<img src="/Finzo/public/assets/${r.profile_photo}" class="w-10 h-10 rounded-full object-cover shadow-sm">` 
                            : `<div class="avatar uppercase flex items-center justify-center text-white font-bold w-10 h-10 rounded-full shadow-sm" style="background:${gradient}">${r.initials}</div>`
                        }
                        <div>
                            <div class="user-name font-bold text-sm dark:text-slate-200">${r.name}</div>
                            <div class="user-email text-xs opacity-60">${r.email}</div>
                        </div>
                    </div>
                </td>
                <td><span class="mono font-mono text-xs opacity-80">${r.accNo}</span></td>
                <td><span class="status-badge ${r.accType === 'Savings' ? 'badge-success' : 'badge-primary'}">${r.accType}</span></td>
                <td>
                    <span class="status-badge ${r.statusBadge}">
                        <span class="material-symbols-outlined" style="font-size:12px; vertical-align:middle;">${r.tempSent ? 'check_circle' : 'pending'}</span>
                        ${r.statusText}
                    </span>
                </td>
                <td>${attemptsDisplay(r.attempts)}</td>
                <td style="font-size:12px;font-weight:600;">${r.lockedDate}</td>
                <td style="font-size:12px;color:var(--text-secondary);">${r.lockTime}</td>
                <td>
                    <div class="flex items-center justify-center gap-2">
                        <a href="admin-locked-account-detail.html?id=${r.id}" class="p-2 hover:bg-slate-100 dark:hover:bg-white/5 rounded-lg transition-colors text-slate-400 hover:text-primary">
                            <span class="material-symbols-outlined" style="font-size:18px;">visibility</span>
                        </a>
                        ${!r.tempSent ? `
                        <button class="p-2 hover:bg-slate-100 dark:hover:bg-white/5 rounded-lg transition-colors text-slate-400 hover:text-amber-500" onclick="window.LockedAccounts.openUnlock('${r.id}','${r.name}','${r.accNo}')">
                            <span class="material-symbols-outlined" style="font-size:18px;">key</span>
                        </button>
                        ` : `
                        <div class="p-2 text-emerald-500"><span class="material-symbols-outlined" style="font-size:18px;">check_circle</span></div>
                        `}
                    </div>
                </td>
            </tr>`;
        }).join('');

        updatePagination(total, totalPages);
    }

    /**
     * Update pagination UI
     */
    function updatePagination(total, totalPages) {
        if (!pagInfo || !pagControls) return;

        const s = (currentPage - 1) * ROWS_PP + 1, e = Math.min(currentPage * ROWS_PP, total);
        pagInfo.textContent = total ? `Showing ${s}–${e} of ${total} locked account${total !== 1 ? 's' : ''}` : 'No results found';
        
        let pcHtml = `<button class="btn btn-secondary px-3 py-1.5 text-[11px] rounded-lg ${currentPage <= 1 ? 'opacity-30 cursor-not-allowed' : ''}" ${currentPage <= 1 ? 'disabled' : ''} onclick="window.LockedAccounts.goPage(-1)">← Prev</button>`;
        
        pcHtml += `<div class="flex items-center gap-1.5">`;
        for (let i = 1; i <= totalPages; i++) {
            if (i <= 3 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                pcHtml += `<button class="w-8 h-8 flex items-center justify-center rounded-lg text-[11px] font-bold transition-all ${i === currentPage ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'hover:bg-slate-100 dark:hover:bg-white/5'}" onclick="window.LockedAccounts.goPageTo(${i})">${i}</button>`;
            } else if (i === 4 || i === totalPages - 1) {
                pcHtml += `<span class="px-2 opacity-50">...</span>`;
            }
        }
        pcHtml += `</div>`;
        
        pcHtml += `<button class="btn btn-secondary px-3 py-1.5 text-[11px] rounded-lg ${currentPage >= totalPages ? 'opacity-30 cursor-not-allowed' : ''}" ${currentPage >= totalPages ? 'disabled' : ''} onclick="window.LockedAccounts.goPage(1)">Next →</button>`;
        
        pagControls.innerHTML = `<div class="flex items-center gap-2 font-bold">${pcHtml}</div>`;
    }

    // Exported Object
    window.LockedAccounts = {
        goPage: (d) => { currentPage += d; loadData(); },
        goPageTo: (p) => { currentPage = p; loadData(); },
        
        resetFilters: () => {
            if (searchInput) searchInput.value = '';
            if (dateFilter) dateFilter.value = '';
            
            const td = document.getElementById('type-dropdown');
            if (td) {
                const hiddenInput = td.querySelector('input[type="hidden"]');
                if (hiddenInput) hiddenInput.value = '';
                const label = td.querySelector('.dropdown-label');
                if (label) label.textContent = 'All Types';
                td.querySelectorAll('.finzo-dropdown-option').forEach(o => o.classList.remove('is-selected'));
                const firstOpt = td.querySelector('.finzo-dropdown-option');
                if (firstOpt) firstOpt.classList.add('is-selected');
            }



            currentPage = 1;
            loadData();
            window.portal.showToast('Filters reset to default', 'info');
        },

        applyFilters: () => {
            currentPage = 1;
            loadData();
        },

        openUnlock: (id, name, accNo) => {
            pendingId = id;
            
            if (unlockModalIconBox) {
                unlockModalIconBox.style.background = 'var(--amber-soft)';
                unlockModalIconBox.classList.remove('success-pop');
            }
            if (unlockModalIcon) {
                unlockModalIcon.textContent = 'key';
                unlockModalIcon.style.color = 'var(--amber)';
            }
            if (unlockModalTitle) {
                unlockModalTitle.textContent = 'Generate Temporary Password';
                unlockModalTitle.style.color = '';
            }
            if (unlockModalText) {
                unlockModalText.innerHTML = `A secure 12-character temporary password will be generated for <strong>${name}</strong> (${accNo}). It will be valid for <strong>24 hours only</strong>. The customer must log in with this password and will be required to set a new password immediately.`;
            }

            if (genPassSection) genPassSection.classList.add('hidden');
            if (unlockModalActions) unlockModalActions.classList.remove('hidden');
            if (unlockDoneActions) unlockDoneActions.classList.add('hidden');
            
            if (generateBtn) {
                generateBtn.disabled = false;
                generateBtn.innerHTML = '<span class="material-symbols-outlined" style="font-size:15px; vertical-align:middle;">key</span> Generate & Unlock';
            }
            
            if (unlockModal) {
                unlockModal.classList.remove('hidden');
                unlockModal.classList.add('flex');
            }
        },

        closeUnlockModal: () => {
            if (unlockModal) {
                unlockModal.classList.add('hidden');
                unlockModal.classList.remove('flex');
            }
        },

        generatePassword: async () => {
            const btn = generateBtn;
            if (!btn) return;
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="material-symbols-outlined animate-spin" style="font-size:15px; vertical-align:middle;">autorenew</span> Generating...';

            try {
                const response = await window.api.post(/users/locked/{}/unlock, );

                if (response.status === 'success' || response.success) {
                    generatedPass = response.temp_password || (response.data && response.data.temp_password) || 'N/A';

                    if (unlockModalIconBox) {
                        unlockModalIconBox.style.background = 'var(--green-soft)';
                        unlockModalIconBox.classList.add('success-pop');
                    }
                    if (unlockModalIcon) {
                        unlockModalIcon.textContent = 'task_alt';
                        unlockModalIcon.style.color = 'var(--green)';
                    }
                    if (unlockModalTitle) {
                        unlockModalTitle.textContent = 'Account Unlocked';
                        unlockModalTitle.style.color = 'var(--green)';
                    }
                    if (unlockModalText) {
                        unlockModalText.innerHTML = `Access has been restored. A temporary password was generated and logged in the system.`;
                    }

                    if (genPassValue) genPassValue.textContent = generatedPass;
                    if (genPassSection) genPassSection.classList.remove('hidden');
                    
                    if (unlockModalActions) unlockModalActions.classList.add('hidden');
                    if (unlockDoneActions) unlockDoneActions.classList.remove('hidden');

                    loadData();
                    window.portal.showToast('Account unlocked. Temporary password generated.', 'success');
                } else {
                    window.portal.showToast(response.message || 'Failed to unlock account', 'error');
                }
            } catch (error) {
                console.error('Unlock error:', error);
                window.portal.showToast('An error occurred during unlock.', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        },

        copyPassword: () => {
            if (!generatedPass) return;
            navigator.clipboard.writeText(generatedPass).then(() => {
                const btn = copyBtn;
                if (btn) {
                    const originalIcon = btn.innerHTML;
                    btn.innerHTML = '<span class="material-symbols-outlined" style="font-size:20px;color:var(--green);">check</span>';
                    setTimeout(() => btn.innerHTML = originalIcon, 2000);
                }
                window.portal.showToast('Password copied to clipboard', 'success');
            });
        }
    };

    // Initialization
    document.addEventListener('DOMContentLoaded', () => {
        loadData();

        // Search Input Listener
        if (searchInput) {
            searchInput.addEventListener('input', window.FinzoUtils.debounce(() => {
                currentPage = 1;
                loadData();
            }, 500));
        }

        // Date Filter Listener
        if (dateFilter) {
            dateFilter.addEventListener('change', () => {
                currentPage = 1;
                loadData();
            });
        }

        // Dropdown Filters change listeners
        if (typeFilter) {
            typeFilter.addEventListener('change', () => {
                currentPage = 1;
                loadData();
            });
        }
        
        const statusFilterInput = document.getElementById('status-filter');
        if (statusFilterInput) {
            statusFilterInput.addEventListener('change', () => {
                currentPage = 1;
                loadData();
            });
        }
    });

})();
