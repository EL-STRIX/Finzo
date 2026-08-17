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
                ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded text-[10px] font-mono font-bold bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-500/20 dark:text-blue-300 dark:border-blue-500/30">SYSTEM</span>'
                : '<span class="inline-flex items-center px-2.5 py-0.5 rounded text-[10px] font-mono font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-500/20 dark:text-amber-300 dark:border-amber-500/30">CUSTOM</span>';
            
            const countStr = String(i + 1).padStart(2, '0');
            const isActive = b.status === 'active';
            const statusBadge = isActive 
                ? '<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/20 dark:text-emerald-300 dark:border-emerald-500/30 whitespace-nowrap"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> ACTIVE</span>'
                : '<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-50 text-red-700 border border-red-200 dark:bg-red-500/20 dark:text-red-300 dark:border-red-500/30 whitespace-nowrap"><span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> INACTIVE</span>';

            const hasManager = b.manager_name && b.manager_name.trim().length > 0;
            const managerAvatarUrl = b.manager_avatar_url || resolveUrl(b.manager_avatar, 'staff-photos');
            const managerInitial = b.manager_initials || (hasManager ? b.manager_name.charAt(0).toUpperCase() : '??');
            
            return `
            <tr data-id="${b.id}" class="hover:bg-surface-hover/60 dark:hover:bg-white/5 transition-colors border-b border-border-subtle dark:border-white/5">
                <td class="font-bold opacity-40 text-xs text-text-primary dark:text-slate-400 px-4 py-4">${countStr}</td>
                <td class="px-4 py-4">
                    <div class="font-bold text-sm text-text-primary dark:text-white branch-name-text">${escapeHtml(b.name)}</div>
                    <div class="text-[10px] text-text-secondary dark:text-slate-400 font-medium">${escapeHtml(b.city || b.state || 'Local Branch')}</div>
                </td>
                <td class="px-4 py-4 text-xs font-bold font-mono text-primary dark:text-amber-400 whitespace-nowrap">${escapeHtml(b.branch_code || 'N/A')}</td>
                <td class="px-4 py-4 text-xs font-medium text-text-primary dark:text-slate-300">${escapeHtml(b.state || 'N/A')}</td>
                <td class="px-4 py-4">
                    ${hasManager ? `
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 text-[10px] bg-primary-50 dark:bg-primary/20 text-primary dark:text-amber-400 font-black flex items-center justify-center rounded-full uppercase overflow-hidden shrink-0 border border-primary-100 dark:border-primary/30">
                            ${managerAvatarUrl ? `<img src="${managerAvatarUrl}?v=${Date.now()}" class="w-full h-full object-cover" onerror="this.parentElement.innerHTML='${managerInitial}'">` : managerInitial}
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-bold text-text-primary dark:text-white truncate">${escapeHtml(b.manager_name)}</div>
                            ${b.manager_staff_id ? `<div class="text-[10px] text-text-secondary dark:text-slate-400 font-mono font-medium">${escapeHtml(b.manager_staff_id)}</div>` : ''}
                        </div>
                    </div>` : `
                    <div class="flex items-center gap-2 text-text-secondary dark:text-slate-400">
                        <div class="w-7 h-7 rounded-full bg-surface-subtle dark:bg-white/5 border border-border-subtle dark:border-white/10 flex items-center justify-center text-xs shrink-0">
                            <span class="material-symbols-outlined text-[15px] opacity-60">person_off</span>
                        </div>
                        <span class="text-xs font-semibold opacity-75">Unassigned</span>
                    </div>`}
                </td>
                <td class="px-4 py-4 text-center font-bold text-xs text-text-primary dark:text-slate-200">${b.staff_count || 0}</td>
                <td class="px-4 py-4 text-center">${typeBadge}</td>
                <td class="px-4 py-4 text-center">${statusBadge}</td>
                <td class="px-4 py-4 text-right">
                    <div class="flex justify-end items-center gap-1.5">
                        <button type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-text-secondary dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-500/15 transition-colors action-view" data-id="${b.id}" title="View Details">
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">visibility</span>
                        </button>
                        <button type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-text-secondary dark:text-slate-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-500/15 transition-colors action-edit" data-id="${b.id}" title="Edit Branch">
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">edit</span>
                        </button>
                        ${!isSystem ? `
                        <button type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-text-secondary dark:text-slate-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/15 transition-colors action-delete" data-id="${b.id}" data-name="${escapeHtml(b.name)}" data-code="${escapeHtml(b.branch_code)}" title="Delete Branch">
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

        try {
            const response = await window.api.get('/staff', { per_page: 100 });
            if (response.success || response.staff) {
                staffList = response.staff || [];
                renderManagerDropdown(staffList, selectedManagerId);
            }
        } catch (error) {
            console.error('Failed to load staff list:', error);
            menu.innerHTML = '<div class="p-3 text-center text-xs text-text-secondary font-bold">Failed to load staff members.</div>';
        }
    }

    function renderManagerDropdown(staff, selectedManagerId = null) {
        const menu = document.getElementById('manager-dropdown-menu');
        if (!menu) return;

        if (!staff.length) {
            menu.innerHTML = '<div class="p-4 text-center text-xs opacity-50 font-bold">No available staff members found.</div>';
            return;
        }

        let html = `
            <div class="finzo-dropdown-option flex items-center gap-3 p-3 hover:bg-surface-hover dark:hover:bg-white/5 cursor-pointer transition-colors border-b border-border-subtle dark:border-white/10" data-value="" data-name="Select Staff Member (Optional)">
                <div class="w-7 h-7 rounded-full bg-surface-subtle dark:bg-surface-dark flex items-center justify-center font-bold text-xs text-text-secondary dark:text-slate-400">—</div>
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-bold text-text-secondary dark:text-slate-400">None (Unassigned)</div>
                </div>
            </div>
        `;

        html += staff.map(s => {
            const isSelected = selectedManagerId && String(s.id) === String(selectedManagerId);
            const initial = s.initials || (s.full_name ? s.full_name.charAt(0).toUpperCase() : 'ST');
            return `
            <div class="finzo-dropdown-option flex items-center gap-3 p-3 hover:bg-surface-hover dark:hover:bg-white/5 cursor-pointer transition-colors ${isSelected ? 'bg-primary-50/50 dark:bg-primary/20' : ''}" data-value="${s.id}" data-name="${escapeHtml(s.full_name)}" data-id="${escapeHtml(s.staff_id)}">
                <div class="w-7 h-7 rounded-full bg-primary-50 dark:bg-primary/20 text-primary dark:text-primary-300 flex items-center justify-center font-bold text-xs uppercase shrink-0 border border-primary-100 dark:border-primary/30">${initial}</div>
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-bold text-text-primary dark:text-white truncate">${escapeHtml(s.full_name)}</div>
                    <div class="text-[10px] text-text-secondary dark:text-slate-400 font-medium truncate">${escapeHtml(s.staff_id)} &bull; ${escapeHtml(s.designation || 'Staff')}</div>
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
                    const endpoint = isEditMode ? `/branches/${currentEditId}` : '/branches';
                    if (isEditMode) formData.append('branch_id', currentEditId);

                    const response = await window.api.upload(endpoint, formData);

                    if (response.success || response.status === 'success') {
                        window.showToast(response.message || 'Branch saved successfully!', 'success');
                        closeModal('addBranchModal');
                        addBranchForm.reset();
                        loadBranches();
                    } else {
                        window.showToast(response.message || 'Failed to save branch.', 'error');
                        if (response.errors) window.api.showFieldErrors(response.errors);
                    }
                } catch (err) {
                    console.error('Branch submit error:', err);
                    window.showToast(err.message || 'Server error occurred while saving branch', 'error');
                } finally {
                    window.api.setButtonLoading(submitBtn, false, isEditMode ? 'Update Branch' : 'Create Branch');
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

                if (!name || !pw || !branchToDelete) return;

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
                        window.showToast('Branch decommissioned and deleted successfully', 'success');
                        closeModal('confirmModal');
                        loadBranches();
                    } else {
                        window.showToast(res.message || 'Failed to delete branch', 'error');
                    }
                } catch (e) {
                    window.showToast(e.message || 'Server error occurred during deletion', 'error');
                } finally {
                    confirmBtn.innerHTML = 'Permanently Delete Branch';
                    checkDeleteForm();
                }
            });
        }

        // Event Delegation for Table Actions
        document.addEventListener('click', (e) => {
            const viewBtn = e.target.closest('.action-view');
            const editBtn = e.target.closest('.action-edit');
            const deleteBtn = e.target.closest('.action-delete');

            if (viewBtn) {
                const id = viewBtn.getAttribute('data-id');
                const branch = currentBranches.find(b => String(b.id) === String(id));
                if (branch) showViewModal(branch);
            } else if (editBtn) {
                const id = editBtn.getAttribute('data-id');
                showEditModal(id);
            } else if (deleteBtn) {
                const id = deleteBtn.getAttribute('data-id');
                const name = deleteBtn.getAttribute('data-name');
                const code = deleteBtn.getAttribute('data-code');
                showDeleteModal(id, name, code);
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

        const nameMatch = name && branchToDeleteName && name.toLowerCase() === branchToDeleteName.toLowerCase();

        if (nameMatch && pw) {
            btn.removeAttribute('disabled');
            btn.classList.remove('opacity-50', 'cursor-not-allowed');
        } else {
            btn.setAttribute('disabled', 'true');
            btn.classList.add('opacity-50', 'cursor-not-allowed');
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

    function showEditModal(id) {
        const branch = currentBranches.find(b => String(b.id) === String(id));
        if (!branch) return;

        isEditMode = true;
        currentEditId = id;

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

    function showViewModal(branch) {
        document.getElementById('view-branch-title').textContent = branch.name;

        const badgesContainer = document.getElementById('view-branch-badges');
        if (badgesContainer) {
            const isSystem = branch.type === 'system';
            const typeBadge = isSystem
                ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded text-[10px] font-mono font-bold bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-500/20 dark:text-blue-300 dark:border-blue-500/30">SYSTEM</span>'
                : '<span class="inline-flex items-center px-2.5 py-0.5 rounded text-[10px] font-mono font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-500/20 dark:text-amber-300 dark:border-amber-500/30">CUSTOM</span>';
            
            const isActive = branch.status === 'active';
            const statusBadge = isActive
                ? '<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/20 dark:text-emerald-300 dark:border-emerald-500/30"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> ACTIVE</span>'
                : '<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-50 text-red-700 border border-red-200 dark:bg-red-500/20 dark:text-red-300 dark:border-red-500/30"><span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> INACTIVE</span>';

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

            if (avatarUrl) {
                avatarEl.innerHTML = `<img src="${avatarUrl}?v=${Date.now()}" class="w-full h-full rounded-full object-cover" onerror="this.parentElement.innerHTML='${initial}'">`;
            } else {
                avatarEl.innerHTML = initial;
            }
            nameEl.textContent = branch.manager_name;
            emailEl.textContent = branch.manager_email || (branch.manager_staff_id ? `ID: ${branch.manager_staff_id}` : 'Staff Manager');
            linkEl.classList.remove('hidden');
            linkEl.href = `/Finzo/public/views/admin/admin-staff.php?search=${encodeURIComponent(branch.manager_staff_id || branch.manager_name)}`;
        } else {
            avatarEl.innerHTML = '??';
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

        // Action buttons
        document.getElementById('view-btn-edit').onclick = () => {
            closeModal('viewBranchModal');
            showEditModal(branch.id);
        };

        document.getElementById('view-btn-staff').onclick = () => {
            window.showBranchStaff(branch.id, branch.name);
        };

        openModal('viewBranchModal');
    }

    function showDeleteModal(id, name, code) {
        const branch = currentBranches.find(b => String(b.id) === String(id));
        if (branch && branch.type === 'system') {
            window.showToast('System root branches cannot be deleted.', 'error');
            return;
        }

        branchToDelete = id;
        branchToDeleteName = name;

        document.getElementById('target-branch-name').textContent = `${name} (${code})`;
        document.getElementById('confirm-branch-name-input').value = '';
        document.getElementById('confirm-admin-pw').value = '';

        const fileInput = document.getElementById('delete-file-input');
        if (fileInput) fileInput.value = '';
        document.getElementById('delete-upload-trigger')?.classList.remove('hidden');
        document.getElementById('delete-file-preview')?.classList.add('hidden');
        document.getElementById('delete-file-preview')?.classList.remove('flex');

        checkDeleteForm();
        openModal('confirmModal');
    }

    window.showBranchStaff = async function(branchId, branchName) {
        document.getElementById('staff-modal-title').textContent = branchName + ' — Staff Roster';
        document.getElementById('staff-modal-subtitle').textContent = 'Loading staff members...';

        closeModal('viewBranchModal');
        openModal('branchStaffModal');

        const tbody = document.getElementById('branch-ds-table-body');
        const noStaff = document.getElementById('no-staff-error');
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="5" class="py-12 text-center text-text-secondary"><div class="flex flex-col items-center"><span class="material-symbols-outlined text-4xl animate-spin mb-2">refresh</span>Loading staff roster...</div></td></tr>';
        }
        if (noStaff) noStaff.classList.add('hidden');

        try {
            const response = await window.api.get(`/staff?branch_id=${branchId}&per_page=50`);
            if (response.success || response.staff) {
                const staff = response.staff || [];
                document.getElementById('staff-modal-subtitle').textContent = `${staff.length} ACTIVE STAFF ${staff.length === 1 ? 'MEMBER' : 'MEMBERS'} ASSIGNED`;

                if (staff.length === 0) {
                    if (tbody) tbody.innerHTML = '';
                    if (noStaff) noStaff.classList.remove('hidden');
                } else {
                    if (noStaff) noStaff.classList.add('hidden');
                    tbody.innerHTML = staff.map(s => {
                        const statusBadge = s.status === 'active' 
                            ? '<span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800/40 inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> ACTIVE</span>'
                            : '<span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-red-50 text-red-700 border border-red-200 dark:bg-red-950/60 dark:text-red-300 dark:border-red-800/40 inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> INACTIVE</span>';
                        const photoUrl = s.profile_photo_url || resolveUrl(s.profile_photo, 'staff-photos');
                        const initial = s.initials || (s.full_name ? s.full_name.charAt(0).toUpperCase() : 'ST');

                        return `
                        <tr class="hover:bg-surface-hover/60 dark:hover:bg-white/5 transition-colors border-b border-border-subtle dark:border-white/5">
                            <td class="px-6 py-4 font-mono text-xs font-bold text-primary dark:text-amber-400">${escapeHtml(s.staff_id)}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-primary-50 dark:bg-primary/20 text-primary dark:text-primary-300 font-bold text-xs flex items-center justify-center overflow-hidden shrink-0 border border-primary-100 dark:border-primary/30">
                                        ${photoUrl ? `<img src="${photoUrl}?v=${Date.now()}" class="w-full h-full object-cover" onerror="this.parentElement.innerHTML='${initial}'">` : initial}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-xs font-bold text-text-primary dark:text-white truncate">${escapeHtml(s.full_name)}</div>
                                        <div class="text-[10px] text-text-secondary dark:text-slate-400 font-bold uppercase tracking-widest">${escapeHtml(s.designation || 'Staff')}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-xs font-bold text-text-primary dark:text-white">${escapeHtml(s.personal_email || s.email || 'N/A')}</div>
                                <div class="text-[10px] text-text-secondary dark:text-slate-400 font-medium">${escapeHtml(s.phone || 'N/A')}</div>
                            </td>
                            <td class="px-6 py-4">
                                ${statusBadge}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="/Finzo/public/views/admin/admin-staff.php?search=${encodeURIComponent(s.staff_id)}" class="btn btn-secondary btn-sm !px-3 !py-1 !text-[10px]" target="_blank">
                                    View Staff
                                </a>
                            </td>
                        </tr>`;
                    }).join('');
                }
            } else {
                if (tbody) tbody.innerHTML = '<tr><td colspan="5" class="py-12 text-center text-error font-bold">Failed to load staff list.</td></tr>';
            }
        } catch (e) {
            console.error('Error fetching branch staff:', e);
            if (tbody) tbody.innerHTML = '<tr><td colspan="5" class="py-12 text-center text-error font-bold">Error loading staff list.</td></tr>';
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

    // Initialize
    document.addEventListener('DOMContentLoaded', () => {
        loadBranches();
        setupDropdowns();
        bindEvents();
    });
})();
