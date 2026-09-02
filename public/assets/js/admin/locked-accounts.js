/**
 * Finzo Admin — Locked Accounts Handler
 * Manages the queue of locked accounts, filtering, and temporary password generation.
 */

(function() {
    let allData = [];
    let currentPage = 1;
    let pendingId = null;
    let generatedPass = '';
    const ROWS_PP = 10;

    // DOM Elements
    const searchInput = document.getElementById('search-input');
    const tableBody = document.getElementById('table-body');
    const pagInfo = document.getElementById('pag-info');
    const pagControls = document.getElementById('pag-controls');
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

    function toast(msg, type = 'info') {
        if (window.showToast) window.showToast(msg, type);
        else if (window.portal && window.portal.showToast) window.portal.showToast(msg, type);
        else if (window.FinzoUtils && window.FinzoUtils.showToast) window.FinzoUtils.showToast(msg, type);
        else alert(msg);
    }

    /**
     * Load locked accounts from API
     */
    async function loadData() {
        if (tableBody) {
            tableBody.innerHTML = `<tr><td colspan="10" class="py-12 text-center"><div class="flex flex-col items-center justify-center"><span class="material-symbols-outlined text-4xl text-text-muted/30 mb-3" aria-hidden="true">inventory_2</span><div class="text-sm font-bold text-text-muted">Loading locked accounts...</div></div></td></tr>`;
        }
        
        try {
            const search = document.getElementById('search-input')?.value || '';
            const typeFilter = document.getElementById('type-filter')?.value || '';
            const statusFilter = document.getElementById('status-filter')?.value || '';

            let url = `/users/locked?page=${currentPage}&per_page=${ROWS_PP}`;
            if (search) url += `&search=${encodeURIComponent(search)}`;
            if (typeFilter) url += `&type=${encodeURIComponent(typeFilter)}`;
            if (statusFilter) url += `&status=${encodeURIComponent(statusFilter)}`;

            const response = await window.api.get(url);
            
            if (response.success) {
                const data = response.data || response;
                allData = (data.accounts || []).map(acc => ({
                    id: acc.id,
                    name: acc.full_name,
                    email: acc.email,
                    initials: acc.initials || (acc.full_name ? acc.full_name.charAt(0).toUpperCase() : 'U'),
                    avatar_url: acc.avatar_url || (acc.profile_picture ? (acc.profile_picture.startsWith('http') ? acc.profile_picture : `/Finzo/${acc.profile_picture}`) : null),
                    accNo: acc.account_no || 'N/A',
                    accType: (acc.account_type || '').toLowerCase() === 'savings' ? 'Savings' : 'Current',
                    attempts: acc.failed_attempts || 0,
                    isLocked: acc.is_locked === true || acc.is_locked === 1,
                    lockedDate: acc.locked_at || 'N/A',
                    lockTime: acc.lock_time || 'N/A',
                    tempSent: acc.temp_pass_status === 'pending' || acc.status_text === 'Done', 
                    statusBadge: acc.status_badge || 'badge-warn',
                    statusText: acc.status_text || 'Pending'
                }));
                
                renderTable(data.total || 0, data.pages || 1);
            } else {
                if (tableBody) {
                    tableBody.innerHTML = `<tr><td colspan="10" class="py-12 text-center"><div class="flex flex-col items-center justify-center"><span class="material-symbols-outlined text-4xl text-text-muted/30 mb-3" aria-hidden="true">inventory_2</span><div class="text-sm font-bold text-text-muted">No locked accounts found</div></div></td></tr>`;
                }
                if (pagInfo) pagInfo.textContent = 'No results found';
                if (pagControls) pagControls.innerHTML = '';
            }
        } catch (error) {
            console.error('Failed to load locked accounts:', error);
            if (tableBody) {
                tableBody.innerHTML = `<tr><td colspan="10" class="py-12 text-center"><div class="flex flex-col items-center justify-center"><span class="material-symbols-outlined text-4xl text-text-muted/30 mb-3" aria-hidden="true">inventory_2</span><div class="text-sm font-bold text-text-muted">Failed to load data.</div></div></td></tr>`;
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
            tableBody.innerHTML = `<tr><td colspan="10" class="py-12 text-center"><div class="flex flex-col items-center justify-center"><span class="material-symbols-outlined text-4xl text-text-muted/30 mb-3" aria-hidden="true">inventory_2</span><div class="text-sm font-bold text-text-muted">No locked accounts found</div></div></td></tr>`;
            if (pagInfo) pagInfo.textContent = 'No results found';
            if (pagControls) pagControls.innerHTML = '';
            return;
        }

        tableBody.innerHTML = allData.map((r, i) => {
            const initials = r.initials || (r.name ? r.name.charAt(0).toUpperCase() : 'U');
            const gradient = (window.FinzoUtils && window.FinzoUtils.getAvatarGradient) ? window.FinzoUtils.getAvatarGradient(initials) : 'linear-gradient(135deg, #10b981, #059669)';
            const rowNum = String((currentPage - 1) * ROWS_PP + i + 1).padStart(2, '0');
            
            const avatarHtml = r.avatar_url 
                ? `<img src="${r.avatar_url}" class="w-10 h-10 rounded-full object-cover shadow-sm border border-border-subtle shrink-0" alt="${r.name}" onerror="this.outerHTML='<div class=\\'avatar uppercase flex items-center justify-center text-white font-bold w-10 h-10 rounded-full shadow-sm shrink-0\\' style=\\'background:${gradient}\\'>${initials}</div>'">` 
                : `<div class="avatar uppercase flex items-center justify-center text-white font-bold w-10 h-10 rounded-full shadow-sm shrink-0" style="background:${gradient}">${initials}</div>`;

            return `
            <tr class="hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-colors" style="${r.tempSent ? 'opacity:0.8; ' : ''}">
                <td style="font-size:11px;font-weight:800;color:var(--text-secondary);">${rowNum}</td>
                <td>
                    <div class="user-cell flex items-center gap-3">
                        ${avatarHtml}
                        <div>
                            <div class="user-name font-bold text-sm text-text-primary">${r.name}</div>
                            <div class="user-email text-xs opacity-60 font-mono">${r.email}</div>
                        </div>
                    </div>
                </td>
                <td><span class="mono font-mono text-xs opacity-80">${r.accNo}</span></td>
                <td><span class="status-badge ${r.accType === 'Savings' ? 'badge-success' : 'badge-primary'}">${r.accType}</span></td>
                <td>
                    <span class="status-badge ${r.statusBadge}">
                        <span class="material-symbols-outlined" style="font-size:12px; vertical-align:middle;" aria-hidden="true">${r.tempSent ? 'check_circle' : 'pending'}</span>
                        ${r.statusText}
                    </span>
                </td>
                <td>${attemptsDisplay(r.attempts)}</td>
                <td style="font-size:12px;font-weight:600;">${r.lockedDate}</td>
                <td style="font-size:12px;color:var(--text-secondary);">${r.lockTime}</td>
                <td class="text-right px-6">
                    <div class="flex items-center justify-end gap-2">
                        <a href="admin-locked-account-detail.php?id=${r.id}" class="p-2 hover:bg-slate-100 dark:hover:bg-white/5 rounded-lg transition-colors text-slate-400 hover:text-primary" title="View Incident Investigation">
                            <span class="material-symbols-outlined" style="font-size:18px;" aria-hidden="true">visibility</span>
                        </a>
                        ${(r.isLocked || !r.tempSent) ? `
                        <button class="p-2 hover:bg-amber-500/10 rounded-lg transition-colors text-amber-500 cursor-pointer" onclick="window.LockedAccounts.openUnlock('${r.id}','${r.name.replace(/'/g, "\\'")}','${r.accNo}')" title="Generate Temporary Password">
                            <span class="material-symbols-outlined" style="font-size:18px;" aria-hidden="true">key</span>
                        </button>
                        ` : `
                        <div class="p-2 text-emerald-500" title="Password Generated"><span class="material-symbols-outlined" style="font-size:18px;" aria-hidden="true">check_circle</span></div>
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
                pcHtml += `<button class="w-8 h-8 flex items-center justify-center rounded-lg text-[11px] font-black transition-all ${i === currentPage ? 'bg-primary text-text-inverse shadow-sm' : 'bg-surface border border-border-subtle text-text-primary hover:bg-surface-hover hover:border-border-strong cursor-pointer'}" onclick="window.LockedAccounts.goPageTo(${i})">${i}</button>`;
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

            const sd = document.getElementById('status-dropdown');
            if (sd) {
                const hiddenInput = sd.querySelector('input[type="hidden"]');
                if (hiddenInput) hiddenInput.value = '';
                const label = sd.querySelector('.dropdown-label');
                if (label) label.textContent = 'All Statuses';
                sd.querySelectorAll('.finzo-dropdown-option').forEach(o => o.classList.remove('is-selected'));
                const firstOpt = sd.querySelector('.finzo-dropdown-option');
                if (firstOpt) firstOpt.classList.add('is-selected');
            }

            currentPage = 1;
            loadData();
            toast('Filters reset to default', 'info');
        },

        applyFilters: () => {
            currentPage = 1;
            loadData();
        },

        openUnlock: (id, name, accNo) => {
            pendingId = id;
            
            const modalIconBox = document.getElementById('unlock-modal-icon-box') || unlockModalIconBox;
            const modalIcon = document.getElementById('unlock-modal-icon') || unlockModalIcon;
            const modalTitle = document.getElementById('unlock-modal-title') || unlockModalTitle;
            const modalText = document.getElementById('unlock-modal-text') || unlockModalText;
            const passSec = document.getElementById('gen-pass-section') || genPassSection;
            const modalActions = document.getElementById('unlock-modal-actions') || unlockModalActions;
            const doneActions = document.getElementById('unlock-done-actions') || unlockDoneActions;
            const genBtn = document.getElementById('generate-btn') || generateBtn;
            const modal = document.getElementById('unlock-modal') || unlockModal;

            if (modalIconBox) {
                modalIconBox.className = 'w-16 h-16 rounded-2xl bg-amber-500/10 text-amber-500 mx-auto mb-4 flex items-center justify-center border border-amber-500/20 shadow-sm transition-all duration-300';
            }
            if (modalIcon) {
                modalIcon.textContent = 'key';
            }
            if (modalTitle) {
                modalTitle.textContent = 'Generate Temporary Password';
            }
            if (modalText) {
                modalText.innerHTML = `A secure 12-character temporary password will be generated for <strong>${name}</strong> (${accNo}). It will be valid for <strong>24 hours only</strong>. The customer must log in with this password and will be required to set a new password immediately.`;
            }

            if (passSec) passSec.classList.add('hidden');
            if (modalActions) modalActions.classList.remove('hidden');
            if (doneActions) doneActions.classList.add('hidden');
            
            if (genBtn) {
                genBtn.disabled = false;
                genBtn.innerHTML = '<span class="material-symbols-outlined text-base" aria-hidden="true">key</span> <span>Generate & Unlock</span>';
            }
            
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        },

        closeUnlockModal: () => {
            const modal = document.getElementById('unlock-modal') || unlockModal;
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        },

        generatePassword: async () => {
            const btn = document.getElementById('generate-btn') || generateBtn;
            if (!btn) return;
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="material-symbols-outlined animate-spin text-base" aria-hidden="true">autorenew</span> <span>Generating...</span>';

            try {
                const response = await window.api.post(`/users/locked/${pendingId}/unlock`, { user_id: pendingId });

                if (response.status === 'success' || response.success) {
                    generatedPass = response.temp_password || (response.data && response.data.temp_password) || 'N/A';

                    const modalIconBox = document.getElementById('unlock-modal-icon-box') || unlockModalIconBox;
                    const modalIcon = document.getElementById('unlock-modal-icon') || unlockModalIcon;
                    const modalTitle = document.getElementById('unlock-modal-title') || unlockModalTitle;
                    const modalText = document.getElementById('unlock-modal-text') || unlockModalText;
                    const passVal = document.getElementById('gen-pass-value') || genPassValue;
                    const passSec = document.getElementById('gen-pass-section') || genPassSection;
                    const modalActions = document.getElementById('unlock-modal-actions') || unlockModalActions;
                    const doneActions = document.getElementById('unlock-done-actions') || unlockDoneActions;

                    if (modalIconBox) {
                        modalIconBox.className = 'w-16 h-16 rounded-2xl bg-emerald-500/10 text-emerald-500 mx-auto mb-4 flex items-center justify-center border border-emerald-500/20 shadow-sm transition-all duration-300';
                    }
                    if (modalIcon) {
                        modalIcon.textContent = 'task_alt';
                    }
                    if (modalTitle) {
                        modalTitle.textContent = 'Account Unlocked';
                    }
                    if (modalText) {
                        modalText.innerHTML = `Access has been restored. A temporary password was generated and logged in the system.`;
                    }

                    if (passVal) passVal.textContent = generatedPass;
                    if (passSec) passSec.classList.remove('hidden');
                    
                    if (modalActions) modalActions.classList.add('hidden');
                    if (doneActions) doneActions.classList.remove('hidden');

                    loadData();
                    toast('Account unlocked. Temporary password generated.', 'success');
                } else {
                    toast(response.message || 'Failed to unlock account', 'error');
                }
            } catch (error) {
                console.error('Unlock error:', error);
                toast('An error occurred during unlock.', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        },

        copyPassword: () => {
            if (!generatedPass) return;
            navigator.clipboard.writeText(generatedPass).then(() => {
                const btn = document.getElementById('copy-btn') || copyBtn;
                if (btn) {
                    const originalIcon = btn.innerHTML;
                    btn.innerHTML = '<span class="material-symbols-outlined text-lg text-emerald-500" aria-hidden="true">check</span>';
                    setTimeout(() => btn.innerHTML = originalIcon, 2000);
                }
                toast('Password copied to clipboard', 'success');
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
