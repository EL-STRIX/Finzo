/**
 * Finzo Admin — Branch Management Handler
 */

(function() {
    let currentBranches = [];
    let staffList = [];
    let isEditMode = false;
    let currentEditId = null;
    let branchToDelete = null;
    let branchToDeleteName = '';

    /**
     * Resolve resource URLs safely
     */
    function resolveUrl(path, subDir = 'branch-certs') {
        if (!path) return null;
        if (path.startsWith('http://') || path.startsWith('https://')) {
            return path;
        }
        const clean = path.replace(/^\/+/, '');
        if (clean.startsWith('Finzo/')) {
            return '/' + clean;
        }
        if (clean.startsWith('uploads/')) {
            return '/Finzo/' + clean;
        }
        return '/Finzo/uploads/' + subDir + '/' + clean;
    }

    /**
     * Load branches from API
     */
    async function loadBranches() {
        const tbody = document.getElementById('branches-table-body');
        if (tbody) tbody.style.opacity = '0.5';

        const searchInput = document.getElementById('admin-branch-finder');
        const query = searchInput ? searchInput.value.trim() : '';

        const statusInput = document.getElementById('filter-branch-status');
        const status = statusInput ? statusInput.value : 'all';

        const typeInput = document.getElementById('filter-branch-type');
        const type = typeInput ? typeInput.value : 'all';

        try {
            const params = new URLSearchParams();
            if (query) params.append('search', query);
            if (status && status !== 'all') params.append('status', status);
            if (type && type !== 'all') params.append('type', type);

            const url = params.toString() ? `/branches?${params.toString()}` : '/branches';
            const response = await window.api.get(url);
            if (response.success || response.status === 'success') {
                const data = response.data || response;
                currentBranches = data.branches || [];
                renderTable(currentBranches);
                updateStats(data.stats || {});
            } else {
                renderTable([]);
            }
        } catch (error) {
            console.error('Failed to load branches:', error);
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="9" class="py-12 text-center text-red-600 dark:text-red-400 font-bold"><div class="flex flex-col items-center"><span class="material-symbols-outlined text-4xl mb-2">error</span>Failed to load branch records. Please try again.</div></td></tr>';
            }
        } finally {
            if (tbody) tbody.style.opacity = '1';
        }
    }

    /**
     * Render branches table
     */
    function renderTable(branches) {
        const tbody = document.getElementById('branches-table-body');
        const summaryEl = document.getElementById('branch-summary-count');
        if (!tbody) return;

        if (summaryEl) {
            summaryEl.textContent = `Showing ${branches.length} registered ${branches.length === 1 ? 'branch' : 'branches'}`;
        }

        if (!branches.length) {
            tbody.innerHTML = '<tr><td colspan="9" class="py-12 text-center"><div class="flex flex-col items-center justify-center"><span class="material-symbols-outlined text-4xl text-text-secondary/40 mb-3" aria-hidden="true">domain_disabled</span><div class="text-sm font-bold text-text-secondary uppercase tracking-widest">No branches found matching your records.</div></div></td></tr>';
            return;
        }

        tbody.innerHTML = branches.map((b, i) => {
            const isSystem = b.type === 'system';
            const typeBadge = isSystem
                ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded text-[10px] font-mono font-bold bg-blue-100 text-blue-800 border border-blue-300 dark:bg-blue-500/20 dark:text-blue-300 dark:border-blue-500/30">SYSTEM</span>'
                : '<span class="inline-flex items-center px-2.5 py-0.5 rounded text-[10px] font-mono font-bold bg-amber-100 text-amber-800 border border-amber-300 dark:bg-amber-500/20 dark:text-amber-300 dark:border-amber-500/30">CUSTOM</span>';
            
            const countStr = String(i + 1).padStart(2, '0');
            const isActive = b.status === 'active';
            const statusBadge = isActive 
                ? '<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 dark:bg-emerald-500/20 dark:text-emerald-300 dark:border-emerald-500/30 whitespace-nowrap"><span class="w-1.5 h-1.5 rounded-full bg-emerald-600 dark:bg-emerald-500 animate-pulse"></span> ACTIVE</span>'
                : '<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800 border border-red-300 dark:bg-red-500/20 dark:text-red-300 dark:border-red-500/30 whitespace-nowrap"><span class="w-1.5 h-1.5 rounded-full bg-red-600 dark:bg-red-500"></span> INACTIVE</span>';

            const hasManager = b.manager_name && b.manager_name.trim().length > 0;
            const managerAvatarUrl = b.manager_avatar_url || resolveUrl(b.manager_avatar, 'staff-photos');
            const managerInitial = b.manager_initials || (hasManager ? b.manager_name.charAt(0).toUpperCase() : '??');
            
            return `
            <tr data-id="${b.id}" class="hover:bg-slate-50 dark:hover:bg-white/5 transition-colors border-b border-slate-200 dark:border-white/5">
                <td class="font-bold opacity-60 text-xs text-slate-700 dark:text-slate-400 px-4 py-4">${countStr}</td>
                <td class="px-4 py-4">
                    <div class="font-bold text-sm text-slate-900 dark:text-white branch-name-text">${escapeHtml(b.name)}</div>
                    <div class="text-[10px] text-slate-500 dark:text-slate-400 font-semibold">${escapeHtml(b.city || b.state || 'Local Branch')}</div>
                </td>
                <td class="px-4 py-4 text-xs font-bold font-mono text-amber-700 dark:text-amber-400 whitespace-nowrap">${escapeHtml(b.branch_code || 'N/A')}</td>
                <td class="px-4 py-4 text-xs font-semibold text-slate-800 dark:text-slate-300">${escapeHtml(b.state || 'N/A')}</td>
                <td class="px-4 py-4">
                    ${hasManager ? `
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 text-[10px] bg-amber-500/15 text-amber-700 dark:text-amber-400 font-black flex items-center justify-center rounded-full uppercase overflow-hidden shrink-0 border border-amber-500/25">
                            ${managerAvatarUrl ? `<img src="${managerAvatarUrl}?v=${Date.now()}" class="w-full h-full object-cover" onerror="this.parentElement.innerHTML='${managerInitial}'">` : managerInitial}
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-bold text-slate-900 dark:text-white truncate">${escapeHtml(b.manager_name)}</div>
                            ${b.manager_staff_id ? `<div class="text-[10px] text-slate-500 dark:text-slate-400 font-mono font-bold">${escapeHtml(b.manager_staff_id)}</div>` : ''}
                        </div>
                    </div>` : `
                    <div class="flex items-center gap-2 text-slate-500 dark:text-slate-400">
                        <div class="w-7 h-7 rounded-full bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 flex items-center justify-center text-xs shrink-0">
                            <span class="material-symbols-outlined text-[15px] opacity-60">person_off</span>
                        </div>
                        <span class="text-xs font-semibold opacity-75">Unassigned</span>
                    </div>`}
                </td>
                <td class="px-4 py-4 text-center font-bold text-xs text-slate-900 dark:text-slate-200">${b.staff_count || 0}</td>
                <td class="px-4 py-4 text-center">${typeBadge}</td>
                <td class="px-4 py-4 text-center">${statusBadge}</td>
                <td class="px-4 py-4 text-right">
                    <div class="flex justify-end items-center gap-1.5">
                        <button type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-500/15 transition-colors action-view" data-id="${b.id}" title="View Details">
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">visibility</span>
                        </button>
                        <button type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-600 dark:text-slate-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-500/15 transition-colors action-edit" data-id="${b.id}" title="Edit Branch">
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">edit</span>
                        </button>
                        ${!isSystem ? `
                        <button type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-600 dark:text-slate-400 ${isActive ? 'hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-500/15' : 'hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-500/15'} transition-colors action-toggle-status" data-id="${b.id}" data-status="${isActive ? 'inactive' : 'active'}" data-name="${escapeHtml(b.name)}" title="${isActive ? 'Suspend Branch' : 'Reactivate Branch'}">
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">${isActive ? 'pause_circle' : 'play_circle'}</span>
                        </button>
                        <button type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-600 dark:text-slate-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/15 transition-colors action-delete" data-id="${b.id}" data-name="${escapeHtml(b.name)}" data-code="${escapeHtml(b.branch_code)}" title="Delete Branch">
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">delete</span>
                        </button>` : ''}
                    </div>
                </td>
            </tr>`;
        }).join('');
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

    /**
     * Update stats UI
     */
    function updateStats(stats) {
        const totalEl = document.getElementById('stat-total-branches');
        const activeEl = document.getElementById('stat-active-branches');
        const staffEl = document.getElementById('stat-total-staff');
        const txnsEl = document.getElementById('stat-total-txns');

        if (totalEl) {
            totalEl.classList.remove('skeleton');
            totalEl.innerHTML = `${stats.total || 0} <span class="admin-stat-delta neutral bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800/40 px-1.5 py-0.5 rounded text-[10px] ml-1 font-bold">Network</span>`;
        }
        if (activeEl) {
            activeEl.classList.remove('skeleton');
            activeEl.innerHTML = `${stats.active || 0} <span class="admin-stat-delta positive bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/40 px-1.5 py-0.5 rounded text-[10px] ml-1 font-bold">Operational</span>`;
        }
        if (staffEl) {
            staffEl.classList.remove('skeleton');
            staffEl.innerHTML = `${stats.total_staff || 0} <span class="admin-stat-delta bg-purple-100 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800/40 px-1.5 py-0.5 rounded text-[10px] ml-1 font-bold">Assigned</span>`;
        }
        if (txnsEl) {
            txnsEl.classList.remove('skeleton');
            txnsEl.innerHTML = `${stats.total_txns || 0} <span class="admin-stat-delta bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/40 px-1.5 py-0.5 rounded text-[10px] ml-1 font-bold">Processed</span>`;
        }
    }

    /**
     * Load staff members for manager assignment
     */
    async function loadStaffOptions(selectedManagerId = null) {
        const menu = document.getElementById('manager-dropdown-menu');
        if (!menu) return;

        // If we already have staff cached, render immediately
        if (staffList && staffList.length > 0) {
            renderManagerDropdown(staffList, selectedManagerId);
        }

        try {
            const response = await window.api.get('/staff', { per_page: 100 });
            if (response) {
                const payload = response.data || response;
                staffList = payload.staff || response.staff || payload.data || (Array.isArray(payload) ? payload : []);
                renderManagerDropdown(staffList, selectedManagerId);
            }
        } catch (error) {
            console.error('Failed to load staff list:', error);
            if (!staffList || staffList.length === 0) {
                menu.innerHTML = '<div class="p-3 text-center text-xs text-text-secondary font-bold">Failed to load staff members.</div>';
            }
        }
    }

    function renderManagerDropdown(staff, selectedManagerId = null) {
        const menu = document.getElementById('manager-dropdown-menu');
        if (!menu) return;

        if (!staff || !staff.length) {
            menu.innerHTML = `
                <div class="finzo-dropdown-option flex items-center gap-3 p-3 hover:bg-surface-hover dark:hover:bg-white/5 cursor-pointer transition-colors" data-value="" data-name="Select Staff Member (Optional)">
                    <div class="w-7 h-7 rounded-full bg-surface-subtle dark:bg-surface-dark flex items-center justify-center font-bold text-xs text-text-secondary dark:text-slate-400">—</div>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-bold text-text-secondary dark:text-slate-400">None (Unassigned)</div>
                    </div>
                </div>
            `;
            return;
        }

        let html = `
            <div class="finzo-dropdown-option flex items-center gap-3 p-3 hover:bg-surface-hover dark:hover:bg-white/5 cursor-pointer transition-colors border-b border-border-subtle dark:border-white/10 ${!selectedManagerId ? 'is-selected bg-amber-500/10 text-amber-500 font-bold' : ''}" data-value="" data-name="Select Staff Member (Optional)">
                <div class="w-7 h-7 rounded-full bg-surface-subtle dark:bg-white/5 flex items-center justify-center font-bold text-xs text-text-secondary dark:text-slate-400 shrink-0 border border-border-subtle dark:border-white/10">
                    <span class="material-symbols-outlined text-[15px] opacity-60">person_off</span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-bold text-text-primary dark:text-white">None (Unassigned)</div>
                    <div class="text-[10px] text-text-secondary dark:text-slate-400 font-medium">Leave branch head unassigned</div>
                </div>
            </div>
        `;

        html += staff.map(s => {
            const isSelected = selectedManagerId && String(s.id) === String(selectedManagerId);
            const initial = s.initials || (s.full_name ? s.full_name.charAt(0).toUpperCase() : 'ST');
            const photoUrl = s.profile_photo_url || resolveUrl(s.profile_photo, 'staff-photos');
            const designation = s.designation || s.role || 'Staff Member';
            const staffId = s.staff_id || ('STF' + String(s.id).padStart(3, '0'));

            return `
            <div class="finzo-dropdown-option flex items-center gap-3 p-3 hover:bg-surface-hover dark:hover:bg-white/5 cursor-pointer transition-colors ${isSelected ? 'is-selected bg-amber-500/15 text-amber-500 font-bold' : ''}" data-value="${s.id}" data-name="${escapeHtml(s.full_name)} (${escapeHtml(staffId)})" data-id="${escapeHtml(staffId)}">
                <div class="w-7 h-7 rounded-full bg-amber-500/15 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-xs uppercase shrink-0 border border-amber-500/25 dark:border-amber-500/25 overflow-hidden">
                    ${photoUrl ? `<img src="${photoUrl}?v=${Date.now()}" class="w-full h-full object-cover" onerror="this.parentElement.innerHTML='${initial}'">` : initial}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-bold text-text-primary dark:text-white truncate">${escapeHtml(s.full_name)}</div>
                    <div class="text-[10px] text-text-secondary dark:text-slate-400 font-medium truncate">${escapeHtml(staffId)} &bull; ${escapeHtml(designation)}</div>
                </div>
            </div>`;
        }).join('');

        menu.innerHTML = html;
    }

    /**
     * Generate branch code dynamically based on state selection
     */
    async function updateBranchCode(state) {
        const codeInput = document.getElementById('add-branch-code');
        if (!codeInput || !state) return;

        try {
            const response = await window.api.get(`/branches/generate-code?state=${encodeURIComponent(state)}`);
            if (response.success && response.branch_code) {
                codeInput.value = response.branch_code;
            }
        } catch (e) {
            // Fallback code calculation
            const clean = state.replace(/[^a-zA-Z\s]/g, '').trim();
            const words = clean.split(/\s+/);
            const code = words.length >= 2
                ? (words[0][0] + words[1][0]).toUpperCase()
                : clean.substring(0, 2).toUpperCase();
            codeInput.value = `FNZ-${code}-001`;
        }
    }

    /**
     * Setup custom dropdowns interaction
     */
    function setupDropdowns() {
        // Universal helper for finzo dropdowns
        function bindDropdown(dropdownId, onSelect) {
            const dropdown = document.getElementById(dropdownId);
            if (!dropdown) return;
            const trigger = dropdown.querySelector('.finzo-dropdown-trigger');
            const menu = dropdown.querySelector('.finzo-dropdown-menu');
            const label = dropdown.querySelector('.dropdown-label');
            const input = dropdown.querySelector('input[type="hidden"]');

            if (!trigger || !menu) return;

            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                const isOpen = dropdown.classList.contains('is-open') || dropdown.classList.contains('open') || menu.classList.contains('is-open') || menu.classList.contains('open');

                // Close all dropdowns
                document.querySelectorAll('.finzo-dropdown').forEach(d => {
                    d.classList.remove('is-open', 'open');
                    d.querySelector('.finzo-dropdown-trigger')?.classList.remove('is-open', 'open');
                    d.querySelector('.finzo-dropdown-trigger')?.setAttribute('aria-expanded', 'false');
                    d.querySelector('.finzo-dropdown-menu')?.classList.remove('is-open', 'open');
                });

                if (!isOpen) {
                    dropdown.classList.add('is-open', 'open');
                    trigger.classList.add('is-open', 'open');
                    trigger.setAttribute('aria-expanded', 'true');
                    menu.classList.add('is-open', 'open');
                }
            });

            menu.addEventListener('click', (e) => {
                const opt = e.target.closest('.finzo-dropdown-option');
                if (opt) {
                    const val = opt.getAttribute('data-value');
                    const text = opt.getAttribute('data-name') || opt.textContent.trim();
                    if (input) input.value = val !== null ? val : '';
                    if (label) label.textContent = text;

                    menu.querySelectorAll('.finzo-dropdown-option').forEach(o => o.classList.remove('is-selected'));
                    opt.classList.add('is-selected');

                    dropdown.classList.remove('is-open', 'open');
                    trigger.classList.remove('is-open', 'open');
                    trigger.setAttribute('aria-expanded', 'false');
                    menu.classList.remove('is-open', 'open');

                    if (typeof onSelect === 'function') {
                        onSelect(val, text, opt);
                    }
                }
            });
        }

        // Operational Status Filter
        bindDropdown('status-filter-dropdown', () => {
            loadBranches();
        });

        // Branch Type Filter
        bindDropdown('type-filter-dropdown', () => {
            loadBranches();
        });

        // State dropdown (modal)
        bindDropdown('branch-state-dropdown', (val) => {
            if (!isEditMode && val) {
                updateBranchCode(val);
            }
        });

        // Manager dropdown (modal)
        bindDropdown('manager-dropdown');

        // Close dropdowns on outside click
        document.addEventListener('click', () => {
            document.querySelectorAll('.finzo-dropdown').forEach(d => {
                d.classList.remove('is-open', 'open');
                d.querySelector('.finzo-dropdown-trigger')?.classList.remove('is-open', 'open');
                d.querySelector('.finzo-dropdown-trigger')?.setAttribute('aria-expanded', 'false');
                d.querySelector('.finzo-dropdown-menu')?.classList.remove('is-open', 'open');
            });
        });
    }

    /**
     * Setup Modals and Event Listeners
     */
    function bindEvents() {
        // Search filter with debounce
        const searchInput = document.getElementById('admin-branch-finder');
        if (searchInput) {
            let timeout = null;
            searchInput.addEventListener('input', function() {
                clearTimeout(timeout);
                timeout = setTimeout(loadBranches, 400);
            });
        }

        // Refresh button
        const refreshBtn = document.getElementById('btn-refresh-branches');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                loadBranches();
                window.showToast('Branch data refreshed', 'info');
            });
        }

        // Open Add Branch Modal
        const openAddBtn = document.getElementById('btn-open-add-branch');
        if (openAddBtn) {
            openAddBtn.addEventListener('click', () => openAddModal());
        }

        // Password visibility toggle
        const togglePwBtn = document.getElementById('toggle-admin-pw');
        const pwInput = document.getElementById('admin-confirm-pw');
        const pwIcon = document.getElementById('toggle-admin-pw-icon');
        if (togglePwBtn && pwInput) {
            togglePwBtn.addEventListener('click', () => {
                const isPw = pwInput.type === 'password';
                pwInput.type = isPw ? 'text' : 'password';
                if (pwIcon) pwIcon.textContent = isPw ? 'visibility_off' : 'visibility';
            });
        }

        // Character counter
        const descTextarea = document.getElementById('branch-desc');
        const descCounter = document.getElementById('branch-desc-counter');
        if (descTextarea && descCounter) {
            descTextarea.addEventListener('input', function() {
                descCounter.textContent = `${this.value.length} / 500`;
            });
        }

        // File upload trigger & handlers (Add/Edit modal)
        const branchFileInput = document.getElementById('branch-file-input');
        const uploadTrigger = document.getElementById('upload-trigger');
        const filePreview = document.getElementById('file-preview');
        const fileName = document.getElementById('file-name');
        const fileSize = document.getElementById('file-size');
        const removeFileBtn = document.getElementById('remove-file');

        if (uploadTrigger && branchFileInput) {
            uploadTrigger.addEventListener('click', () => branchFileInput.click());

            branchFileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const file = this.files[0];
                    if (file.size > 10 * 1024 * 1024) {
                        window.showToast('File size must be under 10MB', 'error');
                        this.value = '';
                        return;
                    }
                    if (fileName) fileName.textContent = file.name;
                    if (fileSize) fileSize.textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
                    uploadTrigger.classList.add('hidden');
                    filePreview.classList.remove('hidden');
                    filePreview.classList.add('flex');
                }
            });

            if (removeFileBtn) {
                removeFileBtn.addEventListener('click', () => {
                    branchFileInput.value = '';
                    uploadTrigger.classList.remove('hidden');
                    filePreview.classList.add('hidden');
                    filePreview.classList.remove('flex');
                });
            }
        }

        // Form Submit Handler
        const addBranchForm = document.getElementById('addBranchForm');
        if (addBranchForm) {
            addBranchForm.addEventListener('submit', async (e) => {
                e.preventDefault();

                const submitBtn = document.getElementById('submit-branch-btn');
                const branchName = document.getElementById('add-branch-name')?.value.trim();
                const state = document.getElementById('branch-state')?.value.trim();
                const city = document.getElementById('add-branch-city')?.value.trim();
                const address = document.getElementById('add-branch-address')?.value.trim();
                const adminPw = document.getElementById('admin-confirm-pw')?.value;

                if (!branchName) {
                    window.showToast('Please enter a branch name', 'warning');
                    return;
                }
                if (!state) {
                    window.showToast('Please select a state or union territory', 'warning');
                    return;
                }
                if (!city) {
                    window.showToast('Please enter a city or district', 'warning');
                    return;
                }
                if (!address) {
                    window.showToast('Please enter full branch address', 'warning');
                    return;
                }
                if (!isEditMode && !adminPw) {
                    window.showToast('Admin security password is required', 'warning');
                    return;
                }

                window.api.setButtonLoading(submitBtn, true);

                const formData = new FormData(addBranchForm);

                try {
                    const savedEditId = isEditMode ? currentEditId : null;
                    const endpoint = isEditMode ? `/branches/${currentEditId}` : '/branches';
                    if (isEditMode) formData.append('branch_id', currentEditId);

                    const response = await window.api.upload(endpoint, formData);

                    if (response.success || response.status === 'success') {
                        window.showToast(response.message || 'Branch updated successfully!', 'success');
                        closeModal('addBranchModal');
                        addBranchForm.reset();
                        isEditMode = false;
                        currentEditId = null;

                        await loadBranches();

                        // If the view modal is currently open, refresh its content with the updated branch
                        const viewModal = document.getElementById('viewBranchModal');
                        if (viewModal && !viewModal.classList.contains('hidden') && savedEditId) {
                            showViewModal(savedEditId);
                        }
                    } else {
                        window.showToast(response.message || 'Failed to save branch.', 'error');
                        if (response.errors) window.api.showFieldErrors(response.errors);
                    }
                } catch (err) {
                    console.error('Branch submit error:', err);
                    window.showToast(err.message || 'Server error occurred while saving branch', 'error');
                } finally {
                    window.api.setButtonLoading(submitBtn, false, 'Save Branch');
                }
            });
        }

        // Delete Modal Confirmation Form
        const confirmNameInput = document.getElementById('confirm-branch-name-input');
        const confirmPwInput = document.getElementById('confirm-admin-pw');
        const deleteFileInput = document.getElementById('delete-file-input');
        const deleteUploadTrigger = document.getElementById('delete-upload-trigger');
        const deleteFilePreview = document.getElementById('delete-file-preview');
        const deleteFileName = document.getElementById('delete-file-name');
        const removeDeleteFileBtn = document.getElementById('remove-delete-file');
        const confirmBtn = document.getElementById('confirm-btn-action');

        if (confirmNameInput) confirmNameInput.addEventListener('input', checkDeleteForm);
        if (confirmPwInput) confirmPwInput.addEventListener('input', checkDeleteForm);

        if (deleteUploadTrigger && deleteFileInput) {
            deleteUploadTrigger.addEventListener('click', () => deleteFileInput.click());

            deleteFileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    if (deleteFileName) deleteFileName.textContent = this.files[0].name;
                    deleteUploadTrigger.classList.add('hidden');
                    deleteFilePreview.classList.remove('hidden');
                    deleteFilePreview.classList.add('flex');
                    checkDeleteForm();
                }
            });

            if (removeDeleteFileBtn) {
                removeDeleteFileBtn.addEventListener('click', () => {
                    deleteFileInput.value = '';
                    deleteUploadTrigger.classList.remove('hidden');
                    deleteFilePreview.classList.add('hidden');
                    deleteFilePreview.classList.remove('flex');
                    checkDeleteForm();
                });
            }
        }

        if (confirmBtn) {
            confirmBtn.addEventListener('click', async () => {
                const name = confirmNameInput?.value.trim();
                const pw = confirmPwInput?.value;

                if (!branchToDelete) {
                    window.showToast('No branch selected for deletion', 'error');
                    return;
                }
                if (!name) {
                    window.showToast(`Please type the branch name to confirm deletion`, 'warning');
                    confirmNameInput?.focus();
                    return;
                }
                if (!pw) {
                    window.showToast('Please enter your administrator password', 'warning');
                    confirmPwInput?.focus();
                    return;
                }

                const formData = new FormData();
                formData.append('branch_id', branchToDelete);
                formData.append('confirm_name', name);
                formData.append('admin_password', pw);
                if (deleteFileInput && deleteFileInput.files && deleteFileInput.files[0]) {
                    formData.append('closure_document', deleteFileInput.files[0]);
                }

                confirmBtn.innerHTML = '<span class="material-symbols-outlined animate-spin text-sm" aria-hidden="true">refresh</span> Decommissioning...';
                confirmBtn.setAttribute('disabled', 'true');

                try {
                    const res = await window.api.upload(`/branches/${branchToDelete}/delete`, formData);
                    if (res.success || res.status === 'success') {
                        window.showToast(res.message || 'Branch decommissioned and deleted successfully', 'success');
                        closeModal('confirmModal');
                        branchToDelete = null;
                        branchToDeleteName = null;
                        loadBranches();
                    } else {
                        window.showToast(res.message || 'Failed to delete branch', 'error');
                    }
                } catch (e) {
                    console.error('Delete branch error:', e);
                    window.showToast(e.message || 'Server error occurred during deletion', 'error');
                } finally {
                    confirmBtn.innerHTML = 'Permanently Delete Branch';
                    confirmBtn.removeAttribute('disabled');
                    checkDeleteForm();
                }
            });
        }

        // Branch Status Form Submission
        const branchStatusForm = document.getElementById('branchStatusForm');
        if (branchStatusForm) {
            branchStatusForm.addEventListener('submit', async (e) => {
                e.preventDefault();

                const branchId = document.getElementById('status-branch-id')?.value;
                const targetAction = document.getElementById('status-target-action')?.value || 'inactive';
                const adminPw = document.getElementById('status-admin-pw')?.value;
                const reason = document.getElementById('status-reason-input')?.value.trim();
                const submitBtn = document.getElementById('status-submit-btn');

                if (!branchId) {
                    window.showToast('Branch ID is missing', 'error');
                    return;
                }
                if (!adminPw) {
                    window.showToast('Please enter your administrator security password', 'warning');
                    document.getElementById('status-admin-pw')?.focus();
                    return;
                }

                window.api.setButtonLoading(submitBtn, true, targetAction === 'inactive' ? 'Suspending...' : 'Reactivating...');

                const formData = new FormData();
                formData.append('branch_id', branchId);
                formData.append('status', targetAction);
                formData.append('admin_password', adminPw);
                if (reason) formData.append('reason', reason);

                try {
                    const res = await window.api.upload(`/branches/${branchId}/status`, formData);
                    if (res.success || res.status === 'success') {
                        window.showToast(res.message || (targetAction === 'inactive' ? 'Branch suspended successfully' : 'Branch reactivated successfully'), 'success');
                        closeModal('branchStatusModal');
                        branchStatusForm.reset();
                        loadBranches();
                    } else {
                        window.showToast(res.message || 'Failed to update branch status', 'error');
                    }
                } catch (err) {
                    console.error('Status toggle error:', err);
                    window.showToast(err.message || 'Server error occurred while updating status', 'error');
                } finally {
                    window.api.setButtonLoading(submitBtn, false, targetAction === 'inactive' ? 'Confirm Suspension' : 'Confirm Reactivation');
                }
            });
        }

        // Toggle Status Password visibility
        const toggleStatusPwBtn = document.getElementById('toggle-status-pw');
        const statusPwInput = document.getElementById('status-admin-pw');
        const statusPwIcon = document.getElementById('toggle-status-pw-icon');
        if (toggleStatusPwBtn && statusPwInput) {
            toggleStatusPwBtn.addEventListener('click', () => {
                const isPw = statusPwInput.type === 'password';
                statusPwInput.type = isPw ? 'text' : 'password';
                if (statusPwIcon) statusPwIcon.textContent = isPw ? 'visibility_off' : 'visibility';
            });
        }

        // Event Delegation for Table Actions
        document.addEventListener('click', (e) => {
            const viewBtn = e.target.closest('.action-view');
            const editBtn = e.target.closest('.action-edit');
            const toggleStatusBtn = e.target.closest('.action-toggle-status');
            const deleteBtn = e.target.closest('.action-delete');

            if (viewBtn) {
                const id = viewBtn.getAttribute('data-id');
                const branch = currentBranches.find(b => String(b.id) === String(id));
                if (branch) showViewModal(branch);
            } else if (editBtn) {
                const id = editBtn.getAttribute('data-id');
                showEditModal(id);
            } else if (toggleStatusBtn) {
                const id = toggleStatusBtn.getAttribute('data-id');
                const targetStatus = toggleStatusBtn.getAttribute('data-status');
                const name = toggleStatusBtn.getAttribute('data-name');
                showStatusModal(id, targetStatus, name);
            } else if (deleteBtn) {
                const id = deleteBtn.getAttribute('data-id');
                const name = deleteBtn.getAttribute('data-name');
                const code = deleteBtn.getAttribute('data-code');
                showDeleteModal(id, name, code);
            }

            // Fallback for View Modal Status Toggle button
            const modalToggleStatusBtn = e.target.closest('#view-btn-toggle-status');
            if (modalToggleStatusBtn) {
                const id = modalToggleStatusBtn.getAttribute('data-id');
                const targetStatus = modalToggleStatusBtn.getAttribute('data-status') || 'active';
                const name = modalToggleStatusBtn.getAttribute('data-name') || '';
                if (id) {
                    closeModal('viewBranchModal');
                    showStatusModal(id, targetStatus, name);
                }
            }

            // Close modal triggers
            const closeBtn = e.target.closest('[data-close-modal]');
            if (closeBtn) {
                const modalId = closeBtn.getAttribute('data-close-modal');
                closeModal(modalId);
            }
        });
    }

    function checkDeleteForm() {
        const btn = document.getElementById('confirm-btn-action');
        if (!btn) return;
        const name = document.getElementById('confirm-branch-name-input')?.value.trim();
        const pw = document.getElementById('confirm-admin-pw')?.value.trim();

        const nameMatch = name && branchToDeleteName && (
            name.toLowerCase() === branchToDeleteName.toLowerCase() ||
            name.toLowerCase() === 'delete' ||
            name.toLowerCase() === 'confirm'
        );

        if (nameMatch && pw) {
            btn.classList.add('shadow-lg', 'shadow-red-900/30');
            btn.classList.remove('opacity-70');
        } else {
            btn.classList.remove('shadow-lg', 'shadow-red-900/30');
        }
    }

    function openAddModal() {
        isEditMode = false;
        currentEditId = null;

        const form = document.getElementById('addBranchForm');
        if (form) form.reset();

        document.getElementById('modal-branch-heading').textContent = 'Add New Branch';
        document.getElementById('modal-branch-subheading').textContent = 'Fill in all details to register a new Finzo branch';
        document.getElementById('submit-branch-btn').textContent = 'Create Branch';

        // Show admin verification in creation mode
        document.getElementById('section-admin-verification')?.classList.remove('hidden');
        document.getElementById('admin-confirm-pw')?.setAttribute('required', 'true');
        const pwInput = document.getElementById('admin-confirm-pw');
        if (pwInput) pwInput.value = '';

        // Reset state dropdown
        document.getElementById('branch-state').value = '';
        document.getElementById('state-dropdown-label').textContent = 'Select State / UT';

        // Reset manager dropdown
        document.getElementById('manager-id').value = '';
        document.getElementById('manager-dropdown-label').textContent = 'Select Staff Member (Optional)';
        loadStaffOptions();

        // Reset char counter
        const counter = document.getElementById('branch-desc-counter');
        if (counter) counter.textContent = '0 / 500';

        // Reset file preview
        const fileInput = document.getElementById('branch-file-input');
        if (fileInput) fileInput.value = '';
        document.getElementById('upload-trigger')?.classList.remove('hidden');
        document.getElementById('file-preview')?.classList.add('hidden');
        document.getElementById('file-preview')?.classList.remove('flex');

        openModal('addBranchModal');
    }

    async function showEditModal(branchOrId) {
        let branch = typeof branchOrId === 'object' ? branchOrId : currentBranches.find(b => String(b.id) === String(branchOrId));
        if (!branch && branchOrId) {
            try {
                const res = await window.api.get(`/branches/${branchOrId}`);
                if (res && (res.branch || res.data)) branch = res.branch || res.data;
            } catch (e) {
                console.error('Error fetching branch:', e);
            }
        }
        if (!branch) {
            window.showToast('Branch details not found', 'error');
            return;
        }

        isEditMode = true;
        currentEditId = branch.id;

        document.getElementById('modal-branch-heading').textContent = 'Edit Branch: ' + branch.name;
        document.getElementById('modal-branch-subheading').textContent = 'Update branch parameters, manager assignment, and location data';
        document.getElementById('submit-branch-btn').textContent = 'Update Branch';

        // Make admin password optional in edit mode
        document.getElementById('section-admin-verification')?.classList.add('hidden');
        document.getElementById('admin-confirm-pw')?.removeAttribute('required');
        const pwInput = document.getElementById('admin-confirm-pw');
        if (pwInput) pwInput.value = '';

        // Populate fields
        document.getElementById('add-branch-name').value = branch.name || '';
        document.getElementById('add-branch-code').value = branch.branch_code || '';
        document.getElementById('add-branch-city').value = branch.city || '';
        document.getElementById('add-branch-address').value = branch.address || '';
        document.getElementById('branch-desc').value = branch.clean_description || (branch.description || '');

        // Set state dropdown
        document.getElementById('branch-state').value = branch.state || '';
        document.getElementById('state-dropdown-label').textContent = branch.state || 'Select State / UT';

        // Set manager dropdown
        document.getElementById('manager-id').value = branch.manager_id || '';
        document.getElementById('manager-dropdown-label').textContent = branch.manager_name ? `${branch.manager_name} (${branch.manager_staff_id || 'Staff'})` : 'Select Staff Member (Optional)';
        loadStaffOptions(branch.manager_id);

        // Counter
        const descVal = branch.clean_description || branch.description || '';
        const counter = document.getElementById('branch-desc-counter');
        if (counter) counter.textContent = `${descVal.length} / 500`;

        // Certificate preview if already attached
        const uploadTrigger = document.getElementById('upload-trigger');
        const filePreview = document.getElementById('file-preview');
        const fileName = document.getElementById('file-name');
        const fileSize = document.getElementById('file-size');
        const fileInput = document.getElementById('branch-file-input');
        if (fileInput) fileInput.value = '';

        if (branch.cert_path) {
            if (uploadTrigger) uploadTrigger.classList.add('hidden');
            if (filePreview) {
                filePreview.classList.remove('hidden');
                filePreview.classList.add('flex');
            }
            if (fileName) fileName.textContent = branch.cert_path.split('/').pop();
            if (fileSize) fileSize.textContent = 'Existing Certificate Attached';
        } else {
            if (uploadTrigger) uploadTrigger.classList.remove('hidden');
            if (filePreview) {
                filePreview.classList.add('hidden');
                filePreview.classList.remove('flex');
            }
        }

        openModal('addBranchModal');
    }

    async function showViewModal(branchOrId) {
        let branch = typeof branchOrId === 'object' ? branchOrId : currentBranches.find(b => String(b.id) === String(branchOrId));
        if (!branch && branchOrId) {
            try {
                const res = await window.api.get(`/branches/${branchOrId}`);
                if (res && (res.branch || res.data)) branch = res.branch || res.data;
            } catch (e) {
                console.error('Error fetching branch:', e);
            }
        }
        if (!branch) {
            window.showToast('Branch details not found', 'error');
            return;
        }

        document.getElementById('view-branch-title').textContent = branch.name;

        const badgesContainer = document.getElementById('view-branch-badges');
        if (badgesContainer) {
            const isSystem = branch.type === 'system';
            const typeBadge = isSystem
                ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded text-[10px] font-mono font-bold bg-blue-100 text-blue-800 border border-blue-300 dark:bg-blue-500/20 dark:text-blue-300 dark:border-blue-500/30">SYSTEM</span>'
                : '<span class="inline-flex items-center px-2.5 py-0.5 rounded text-[10px] font-mono font-bold bg-amber-100 text-amber-800 border border-amber-300 dark:bg-amber-500/20 dark:text-amber-300 dark:border-amber-500/30">CUSTOM</span>';
            
            const isActive = branch.status === 'active';
            const statusBadge = isActive
                ? '<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 dark:bg-emerald-500/20 dark:text-emerald-300 dark:border-emerald-500/30"><span class="w-1.5 h-1.5 rounded-full bg-emerald-600 dark:bg-emerald-500 animate-pulse"></span> ACTIVE</span>'
                : '<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800 border border-red-300 dark:bg-red-500/20 dark:text-red-300 dark:border-red-500/30"><span class="w-1.5 h-1.5 rounded-full bg-red-600 dark:bg-red-500"></span> INACTIVE</span>';

            badgesContainer.innerHTML = `${typeBadge} ${statusBadge}`;
        }

        document.getElementById('view-branch-code').textContent = branch.branch_code || 'N/A';
        document.getElementById('view-branch-city').textContent = branch.city || branch.state || 'Local';
        document.getElementById('view-branch-state').textContent = branch.state || 'N/A';
        document.getElementById('view-branch-address').textContent = branch.address || `${branch.name}, ${branch.state}`;

        const avatarEl = document.getElementById('view-manager-avatar');
        const nameEl = document.getElementById('view-manager-name');
        const emailEl = document.getElementById('view-manager-email');
        const linkEl = document.getElementById('view-manager-link');

        if (branch.manager_name) {
            const avatarUrl = branch.manager_avatar_url || resolveUrl(branch.manager_avatar, 'staff-photos');
            const initial = branch.manager_initials || branch.manager_name.charAt(0).toUpperCase();

            avatarEl.className = 'w-12 h-12 rounded-full flex items-center justify-center bg-amber-500/15 text-amber-800 dark:text-amber-400 font-black text-lg shrink-0 border-2 border-amber-500/30 overflow-hidden';
            if (avatarUrl) {
                avatarEl.innerHTML = `<img src="${avatarUrl}?v=${Date.now()}" class="w-full h-full object-cover" onerror="this.parentElement.innerHTML='${initial}'">`;
            } else {
                avatarEl.innerHTML = initial;
            }
            nameEl.textContent = branch.manager_name;
            emailEl.textContent = branch.manager_email || (branch.manager_staff_id ? `ID: ${branch.manager_staff_id}` : 'Staff Manager');
            linkEl.classList.remove('hidden');
            linkEl.href = `/Finzo/public/views/admin/admin-staff.php?search=${encodeURIComponent(branch.manager_staff_id || branch.manager_name)}`;
        } else {
            avatarEl.className = 'w-12 h-12 rounded-full flex items-center justify-center bg-slate-100 dark:bg-white/5 text-slate-500 dark:text-slate-400 font-black text-lg shrink-0 border-2 border-slate-200 dark:border-white/10';
            avatarEl.innerHTML = '<span class="material-symbols-outlined text-xl opacity-60">person_off</span>';
            nameEl.textContent = 'Unassigned';
            emailEl.textContent = 'No manager assigned';
            linkEl.classList.add('hidden');
        }

        document.getElementById('view-staff-count').textContent = branch.staff_count || 0;
        document.getElementById('view-txn-count').textContent = branch.txn_count || 0;

        const certContainer = document.getElementById('view-cert-container');
        const certEmpty = document.getElementById('view-cert-empty');
        const certUrl = branch.cert_url || resolveUrl(branch.cert_path, 'branch-certs');

        if (certUrl) {
            certContainer.classList.remove('hidden');
            certContainer.classList.add('flex');
            certEmpty.classList.add('hidden');
            document.getElementById('view-cert-name').textContent = branch.cert_path ? branch.cert_path.split('/').pop() : 'branch_certificate.pdf';
            const dlLink = document.getElementById('view-cert-download');
            dlLink.href = certUrl;
        } else {
            certContainer.classList.add('hidden');
            certContainer.classList.remove('flex');
            certEmpty.classList.remove('hidden');
        }

        document.getElementById('view-branch-desc').textContent = branch.clean_description || branch.description || 'No specialized description provided.';

        // Status Toggle button in View modal
        const statusToggleBtn = document.getElementById('view-btn-toggle-status');
        if (statusToggleBtn) {
            const isSystem = branch.type === 'system';
            const isActive = branch.status === 'active';

            if (isSystem) {
                statusToggleBtn.classList.add('hidden');
            } else {
                statusToggleBtn.classList.remove('hidden');
                statusToggleBtn.setAttribute('data-id', branch.id);
                statusToggleBtn.setAttribute('data-status', isActive ? 'inactive' : 'active');
                statusToggleBtn.setAttribute('data-name', branch.name || '');

                if (isActive) {
                    statusToggleBtn.className = 'btn px-5 py-2.5 text-xs font-bold flex items-center gap-1.5 border border-amber-300 text-amber-900 bg-amber-50 hover:bg-amber-100 dark:border-amber-700/50 dark:text-amber-300 dark:bg-amber-950/40 shadow-sm cursor-pointer';
                    statusToggleBtn.innerHTML = '<span class="material-symbols-outlined text-base" aria-hidden="true">pause_circle</span> Suspend Branch';
                    statusToggleBtn.onclick = (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        closeModal('viewBranchModal');
                        showStatusModal(branch.id, 'inactive', branch.name);
                    };
                } else {
                    statusToggleBtn.className = 'btn px-5 py-2.5 text-xs font-bold flex items-center gap-1.5 border border-emerald-300 text-emerald-900 bg-emerald-50 hover:bg-emerald-100 dark:border-emerald-700/50 dark:text-emerald-300 dark:bg-emerald-950/40 shadow-sm cursor-pointer';
                    statusToggleBtn.innerHTML = '<span class="material-symbols-outlined text-base" aria-hidden="true">play_circle</span> Reactivate Branch';
                    statusToggleBtn.onclick = (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        closeModal('viewBranchModal');
                        showStatusModal(branch.id, 'active', branch.name);
                    };
                }
            }
        }

        // Action buttons
        const editBtn = document.getElementById('view-btn-edit');
        if (editBtn) {
            editBtn.setAttribute('data-id', branch.id);
            editBtn.onclick = (e) => {
                e.preventDefault();
                closeModal('viewBranchModal');
                showEditModal(branch.id);
            };
        }

        const staffBtn = document.getElementById('view-btn-staff');
        if (staffBtn) {
            staffBtn.setAttribute('data-id', branch.id);
            staffBtn.setAttribute('data-name', branch.name || '');
            staffBtn.onclick = (e) => {
                e.preventDefault();
                window.showBranchStaff(branch.id, branch.name);
            };
        }

        openModal('viewBranchModal');
    }

    function showStatusModal(id, targetStatus, name) {
        const branch = currentBranches.find(b => String(b.id) === String(id));
        if (branch && branch.type === 'system' && targetStatus === 'inactive') {
            window.showToast('Root system branches cannot be suspended.', 'error');
            return;
        }

        const branchName = name || (branch ? branch.name : 'Branch');
        const isSuspending = targetStatus === 'inactive';

        const idInput = document.getElementById('status-branch-id');
        const actionInput = document.getElementById('status-target-action');
        const nameTarget = document.getElementById('status-target-branch-name');

        if (idInput) idInput.value = id;
        if (actionInput) actionInput.value = targetStatus;
        if (nameTarget) nameTarget.textContent = branchName;

        const iconBox = document.getElementById('status-modal-icon-box');
        const icon = document.getElementById('status-modal-icon');
        const title = document.getElementById('status-modal-title');
        const desc = document.getElementById('status-modal-desc');
        const alertBox = document.getElementById('status-modal-alert');
        const alertText = document.getElementById('status-modal-alert-text');
        const submitBtn = document.getElementById('status-submit-btn');

        if (isSuspending) {
            if (iconBox) iconBox.className = 'w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border-2 border-amber-300 dark:border-amber-800/40';
            if (icon) icon.textContent = 'pause_circle';
            if (title) title.textContent = 'Suspend Branch';
            if (desc) desc.innerHTML = `You are changing the operational status of <span class="font-black text-slate-950 dark:text-white">${escapeHtml(branchName)}</span>.`;
            if (alertBox) alertBox.className = 'p-3.5 bg-amber-50 dark:bg-amber-950/40 border-2 border-amber-300 dark:border-amber-700/50 rounded-xl flex items-center gap-2.5 text-left mb-5';
            if (alertText) alertText.textContent = 'Suspending this branch will mark it as INACTIVE. Operational transactions and new staff teller assignments will be paused.';
            if (submitBtn) {
                submitBtn.className = 'btn w-full justify-center !py-3.5 !bg-amber-600 hover:!bg-amber-500 !text-white font-black text-sm border-0 shadow-lg shadow-amber-900/20';
                submitBtn.textContent = 'Confirm Suspension';
            }
        } else {
            if (iconBox) iconBox.className = 'w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border-2 border-emerald-300 dark:border-emerald-800/40';
            if (icon) icon.textContent = 'play_circle';
            if (title) title.textContent = 'Reactivate Branch';
            if (desc) desc.innerHTML = `You are restoring the operational status of <span class="font-black text-slate-950 dark:text-white">${escapeHtml(branchName)}</span>.`;
            if (alertBox) alertBox.className = 'p-3.5 bg-emerald-50 dark:bg-emerald-950/40 border-2 border-emerald-300 dark:border-emerald-700/50 rounded-xl flex items-center gap-2.5 text-left mb-5';
            if (alertText) alertText.textContent = 'Reactivating this branch will restore its ACTIVE status in the network. Staff operations and teller transactions will resume normally.';
            if (submitBtn) {
                submitBtn.className = 'btn w-full justify-center !py-3.5 !bg-emerald-600 hover:!bg-emerald-500 !text-white font-black text-sm border-0 shadow-lg shadow-emerald-900/20';
                submitBtn.textContent = 'Confirm Reactivation';
            }
        }

        const pwInput = document.getElementById('status-admin-pw');
        const reasonInput = document.getElementById('status-reason-input');
        if (pwInput) pwInput.value = '';
        if (reasonInput) reasonInput.value = '';

        openModal('branchStatusModal');
    }


    function showDeleteModal(id, name, code) {
        const branch = currentBranches.find(b => String(b.id) === String(id));
        if (branch && branch.type === 'system') {
            window.showToast('System root branches cannot be deleted.', 'error');
            return;
        }

        branchToDelete = id;
        branchToDeleteName = name || (branch ? branch.name : '');
        const branchCode = code || (branch ? branch.branch_code : '');

        const targetNameEl = document.getElementById('target-branch-name');
        if (targetNameEl) targetNameEl.textContent = `${branchToDeleteName} (${branchCode})`;

        const confirmNameInput = document.getElementById('confirm-branch-name-input');
        const confirmPwInput = document.getElementById('confirm-admin-pw');
        if (confirmNameInput) {
            confirmNameInput.value = '';
            confirmNameInput.placeholder = `Type "${branchToDeleteName}" or "delete"`;
        }
        if (confirmPwInput) confirmPwInput.value = '';

        const fileInput = document.getElementById('delete-file-input');
        if (fileInput) fileInput.value = '';
        document.getElementById('delete-upload-trigger')?.classList.remove('hidden');
        document.getElementById('delete-file-preview')?.classList.add('hidden');
        document.getElementById('delete-file-preview')?.classList.remove('flex');

        const btn = document.getElementById('confirm-btn-action');
        if (btn) {
            btn.removeAttribute('disabled');
            btn.classList.remove('opacity-50', 'cursor-not-allowed');
        }

        checkDeleteForm();
        openModal('confirmModal');
    }

    window.showBranchStaff = async function(branchId, branchName) {
        document.getElementById('staff-modal-title').textContent = (branchName || 'Branch') + ' — Staff Roster';
        document.getElementById('staff-modal-subtitle').textContent = 'Loading staff members...';

        closeModal('viewBranchModal');
        openModal('branchStaffModal');

        const tbody = document.getElementById('branch-ds-table-body');
        const noStaff = document.getElementById('no-staff-error');
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="5" class="py-12 text-center text-slate-700 dark:text-slate-400 font-bold"><div class="flex flex-col items-center"><span class="material-symbols-outlined text-4xl animate-spin mb-2">refresh</span>Loading staff roster...</div></td></tr>';
        }
        if (noStaff) noStaff.classList.add('hidden');

        try {
            const response = await window.api.get('/staff', { branch_id: branchId, per_page: 100 });
            if (response) {
                const payload = response.data || response;
                const staff = payload.staff || response.staff || payload.data || (Array.isArray(payload) ? payload : []);
                document.getElementById('staff-modal-subtitle').textContent = `${staff.length} ACTIVE STAFF ${staff.length === 1 ? 'MEMBER' : 'MEMBERS'} ASSIGNED`;

                if (staff.length === 0) {
                    if (tbody) tbody.innerHTML = '';
                    if (noStaff) noStaff.classList.remove('hidden');
                } else {
                    if (noStaff) noStaff.classList.add('hidden');
                    tbody.innerHTML = staff.map(s => {
                        const statusBadge = s.status === 'active' 
                            ? '<span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 dark:bg-emerald-500/20 dark:text-emerald-300 dark:border-emerald-500/30 inline-flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-emerald-600 dark:bg-emerald-500 animate-pulse"></span> ACTIVE</span>'
                            : '<span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800 border border-red-300 dark:bg-red-500/20 dark:text-red-300 dark:border-red-500/30 inline-flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-red-600 dark:bg-red-500"></span> INACTIVE</span>';
                        const photoUrl = s.profile_photo_url || resolveUrl(s.profile_photo, 'staff-photos');
                        const initial = s.initials || (s.full_name ? s.full_name.charAt(0).toUpperCase() : 'ST');

                        return `
                        <tr class="hover:bg-slate-50 dark:hover:bg-white/5 transition-colors border-b border-slate-200 dark:border-white/5">
                            <td class="px-6 py-4 font-mono text-xs font-bold text-amber-700 dark:text-amber-400 whitespace-nowrap">${escapeHtml(s.staff_id)}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-amber-500/15 text-amber-800 dark:text-amber-400 font-bold text-xs flex items-center justify-center overflow-hidden shrink-0 border border-amber-500/25">
                                        ${photoUrl ? `<img src="${photoUrl}?v=${Date.now()}" class="w-full h-full object-cover" onerror="this.parentElement.innerHTML='${initial}'">` : initial}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-xs font-bold text-slate-900 dark:text-white truncate">${escapeHtml(s.full_name)}</div>
                                        <div class="text-[10px] text-slate-500 dark:text-slate-400 font-bold uppercase tracking-widest">${escapeHtml(s.designation || 'Staff')}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-xs font-bold text-slate-900 dark:text-white">${escapeHtml(s.personal_email || s.email || 'N/A')}</div>
                                <div class="text-[10px] text-slate-500 dark:text-slate-400 font-medium">${escapeHtml(s.phone || 'N/A')}</div>
                            </td>
                            <td class="px-6 py-4">
                                ${statusBadge}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="/Finzo/public/views/admin/admin-staff.php?search=${encodeURIComponent(s.staff_id)}" class="btn btn-secondary btn-sm !px-3 !py-1 !text-[10px] font-bold" target="_blank">
                                    View Profile
                                </a>
                            </td>
                        </tr>`;
                    }).join('');
                }
            } else {
                if (tbody) tbody.innerHTML = '<tr><td colspan="5" class="py-12 text-center text-red-600 font-bold">Failed to load staff list.</td></tr>';
            }
        } catch (e) {
            console.error('Error fetching branch staff:', e);
            if (tbody) tbody.innerHTML = '<tr><td colspan="5" class="py-12 text-center text-red-600 font-bold">Error loading staff list.</td></tr>';
        }
    };

    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        }
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        }
    }

    // Expose helpers globally
    window.openModal = openModal;
    window.closeModal = closeModal;
    window.showViewModal = showViewModal;
    window.showEditModal = showEditModal;
    window.showStatusModal = showStatusModal;
    window.showDeleteModal = showDeleteModal;

    // Initialize
    document.addEventListener('DOMContentLoaded', () => {
        loadBranches();
        loadStaffOptions();
        setupDropdowns();
        bindEvents();
    });
})();
