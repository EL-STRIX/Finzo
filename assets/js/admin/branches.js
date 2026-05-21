/**
 * Finzo Admin — Branch Management Handler
 */

(function() {
    let currentBranches = [];
    let unassignedStaff = [];
    let isEditMode = false;
    let currentEditId = null;

    /**
     * Load branches from API
     */
    async function loadBranches() {
        const tbody = document.getElementById('branches-table-body');
        if (tbody) tbody.style.opacity = '0.5';

        try {
            const response = await window.api.get('/admin/branches.php');
            if (response.success || response.status === 'success') {
                const data = response.data || response;
                currentBranches = data.branches || [];
                renderTable(currentBranches);
                updateStats(data.stats || {});
            }
        } catch (error) {
            console.error('Failed to load branches:', error);
        } finally {
            if (tbody) tbody.style.opacity = '1';
        }
    }

    /**
     * Render branches table
     */
    function renderTable(branches) {
        const tbody = document.getElementById('branches-table-body');
        if (!tbody) return;

        if (!branches.length) {
            tbody.innerHTML = '<tr><td colspan="8" class="py-20 text-center font-bold opacity-30">No branches found matching your records.</td></tr>';
            return;
        }

        tbody.innerHTML = branches.map((b, i) => {
            const typeStyle = b.type === 'MAIN' ? 'background:#eff6ff;color:#1d4ed8;border-color:#dbeafe;' : 'background:#fffbeb;color:#b45309;border-color:#fef3c7;';
            const countStr = String(i + 1).padStart(2, '0');
            
            return `
            <tr data-id="${b.id}">
                <td class="font-bold opacity-40">${countStr}</td>
                <td><div class="font-bold branch-name-text">${b.name}</div></td>
                <td class="text-sm font-medium branch-location-text">${b.branch_code || 'N/A'} — ${b.state || 'Local'}</td>
                <td>
                    <div class="flex items-center gap-2 manager-info-text">
                        <div class="admin-avatar sm w-6 h-6 text-[9px] bg-blue-100 text-blue-700 flex items-center justify-center rounded-full uppercase">${b.manager_initials || '??'}</div>
                        <div class="text-[12px] font-bold"><span class="manager-name-text">${b.manager_name || 'Unassigned'}</span> <span class="opacity-40 font-mono">(${b.manager_staff_id || 'N/A'})</span></div>
                    </div>
                </td>
                <td class="text-center font-bold">${b.staff_count || 0}</td>
                <td><span class="status-badge" style="${typeStyle}">${b.type || 'BRANCH'}</span></td>
                <td><span class="status-badge badge-success">ACTIVE</span></td>
                <td class="text-right">
                    <div class="flex justify-end gap-2">
                        <button type="button" class="p-2 hover:bg-slate-100 dark:hover:bg-white/5 rounded-lg text-slate-400 hover:text-primary action-view" data-id="${b.id}" data-name="${b.name}" title="View">
                            <span class="material-symbols-outlined !text-[18px]">visibility</span>
                        </button>
                        <button type="button" class="p-2 hover:bg-slate-100 dark:hover:bg-white/5 rounded-lg text-slate-400 hover:text-primary action-edit" data-id="${b.id}" data-name="${b.name}" data-code="${b.branch_code}" title="Edit">
                            <span class="material-symbols-outlined !text-[18px]">edit</span>
                        </button>
                        ${b.type !== 'MAIN' ? `
                        <button type="button" class="p-2 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-lg text-slate-400 hover:text-red-500 action-delete" data-id="${b.id}" data-name="${b.name}" data-code="${b.branch_code}" title="Delete">
                            <span class="material-symbols-outlined !text-[18px]">delete</span>
                        </button>` : ''}
                    </div>
                </td>
            </tr>`;
        }).join('');
    }

    /**
     * Update stats UI
     */
    function updateStats(stats) {
        if (document.getElementById('stat-total-branches')) {
            document.getElementById('stat-total-branches').innerHTML = `${stats.total || 0} <span class="admin-stat-delta neutral bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded text-[10px]">Registered</span>`;
        }
        if (document.getElementById('stat-active-branches')) {
            document.getElementById('stat-active-branches').innerHTML = `${stats.active || 0} <span class="admin-stat-delta positive">Operational</span>`;
        }
        if (document.getElementById('stat-total-staff')) {
            document.getElementById('stat-total-staff').innerHTML = `${stats.total_staff || 0} <span class="admin-stat-delta bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded text-[10px]">Across all branches</span>`;
        }
    }

    /**
     * Load staff for manager assignment
     */
    async function loadUnassignedStaff() {
        try {
            const response = await window.api.get('/admin/staff.php', { filter: 'unassigned' });
            if (response.success) {
                unassignedStaff = response.staff || [];
                renderManagerDropdown(unassignedStaff);
            }
        } catch (error) {
            console.error('Failed to load unassigned staff:', error);
        }
    }

    function renderManagerDropdown(staff) {
        const menu = document.getElementById('manager-dropdown-menu');
        if (!menu) return;

        if (!staff.length) {
            menu.innerHTML = '<div class="p-4 text-center text-xs opacity-50 font-bold">No available staff members to assign as manager.</div>';
            return;
        }

        menu.innerHTML = staff.map(s => `
            <div class="finzo-dropdown-option flex items-center gap-3 p-3 hover:bg-slate-50 dark:hover:bg-white/5 cursor-pointer transition-colors" data-value="${s.id}">
                <div class="admin-avatar sm w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center font-bold text-xs uppercase">${s.initials || '??'}</div>
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-bold truncate">${s.full_name}</div>
                    <div class="text-[10px] opacity-50 font-medium">${s.staff_id} — ${s.personal_email}</div>
                </div>
            </div>
        `).join('');
    }

    /**
     * Bind events
     */
    function bindEvents() {
        // Search
        const searchInput = document.getElementById('admin-branch-finder');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                const rows = document.querySelectorAll('#branches-table-body tr');
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(query) ? '' : 'none';
                });
            });
        }

        // Action Buttons Delegation
        document.addEventListener('click', (e) => {
            const viewBtn = e.target.closest('.action-view');
            const editBtn = e.target.closest('.action-edit');
            const deleteBtn = e.target.closest('.action-delete');

            if (viewBtn) {
                const id = viewBtn.getAttribute('data-id');
                const name = viewBtn.getAttribute('data-name');
                const branch = currentBranches.find(b => b.id == id);
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
        });

        // Add Branch Modal
        const addBtn = document.querySelector('button[onclick="openAddModal()"]');
        if (addBtn) {
            addBtn.removeAttribute('onclick');
            addBtn.addEventListener('click', () => {
                isEditMode = false;
                currentEditId = null;
                const form = document.getElementById('addBranchForm');
                if (form) form.reset();
                document.getElementById('addBranchModal').classList.remove('hidden');
                document.getElementById('addBranchModal').classList.add('flex');
            });
        }

        // Form Submit Handler
        const addBranchForm = document.getElementById('addBranchForm');
        if (addBranchForm) {
            addBranchForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                // Form is validated by core API script before this fires if invalid
                const submitBtn = document.getElementById('submit-branch-btn');
                window.api.setButtonLoading(submitBtn, true);
                
                const formData = new FormData(addBranchForm);
                
                try {
                    const endpoint = isEditMode ? '/admin/edit-branch.php' : '/admin/add-branch.php';
                    if (isEditMode) formData.append('branch_id', currentEditId);

                    const response = await window.api.upload(endpoint, formData);
                    
                    if (response.success) {
                        window.showToast(response.message || 'Branch saved successfully', 'success');
                        document.getElementById('addBranchModal').classList.add('hidden');
                        document.getElementById('addBranchModal').classList.remove('flex');
                        addBranchForm.reset();
                        // Also clear the file preview
                        const fileInput = document.getElementById('branch-file-input');
                        if (fileInput) fileInput.value = '';
                        const trigger = document.getElementById('upload-trigger');
                        const preview = document.getElementById('file-preview');
                        if (trigger) trigger.classList.remove('hidden');
                        if (preview) preview.classList.add('hidden');

                        loadBranches();
                    } else {
                        window.showToast(response.message || 'Failed to save branch', 'error');
                        if (response.errors) window.api.showFieldErrors(response.errors);
                    }
                } catch (err) {
                    console.error('Submit error:', err);
                } finally {
                    window.api.setButtonLoading(submitBtn, false, isEditMode ? 'Update Branch' : 'Create Branch');
                }
            });
        }

        // File Upload Handlers (Add Branch)
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
                    fileName.textContent = file.name;
                    fileSize.textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
                    uploadTrigger.classList.add('hidden');
                    filePreview.classList.remove('hidden');
                }
            });

            if (removeFileBtn) {
                removeFileBtn.addEventListener('click', () => {
                    branchFileInput.value = '';
                    uploadTrigger.classList.remove('hidden');
                    filePreview.classList.add('hidden');
                });
            }
        }

        // File Upload Handlers (Delete Branch)
        const deleteFileInput = document.getElementById('delete-file-input');
        const deleteUploadTrigger = document.getElementById('delete-upload-trigger');
        const deleteFilePreview = document.getElementById('delete-file-preview');
        const deleteFileName = document.getElementById('delete-file-name');
        const removeDeleteFileBtn = document.getElementById('remove-delete-file');
        
        const confirmNameInput = document.getElementById('confirm-branch-name-input');
        const confirmPwInput = document.getElementById('confirm-admin-pw');
        
        if (confirmNameInput) confirmNameInput.addEventListener('input', checkDeleteForm);
        if (confirmPwInput) confirmPwInput.addEventListener('input', checkDeleteForm);

        if (deleteUploadTrigger && deleteFileInput) {
            deleteUploadTrigger.addEventListener('click', () => deleteFileInput.click());
            
            deleteFileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    deleteFileName.textContent = this.files[0].name;
                    deleteUploadTrigger.classList.add('hidden');
                    deleteFilePreview.classList.remove('hidden');
                    deleteFilePreview.classList.add('flex');
                    checkDeleteForm(); // If there is a validation func
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

        // Close Modals
        document.querySelectorAll('[onclick*="addClass(\'hidden\')"]').forEach(btn => {
            btn.removeAttribute('onclick');
            btn.addEventListener('click', () => {
                const modal = btn.closest('.fixed');
                if (modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }
            });
        });
    }

    function checkDeleteForm() {
        const btn = document.getElementById('confirm-btn-action');
        if (!btn) return;
        const name = document.getElementById('confirm-branch-name-input')?.value;
        const pw = document.getElementById('confirm-admin-pw')?.value;
        const file = document.getElementById('delete-file-input')?.files?.length > 0;
        
        if (name && pw && file) {
            btn.removeAttribute('disabled');
            btn.classList.remove('opacity-50', 'cursor-not-allowed');
        } else {
            btn.setAttribute('disabled', 'true');
            btn.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }

    function showViewModal(branch) {
        document.getElementById('view-branch-title').textContent = branch.name;
        
        const badgesContainer = document.getElementById('view-branch-badges');
        if (badgesContainer) {
            const typeStyle = branch.type === 'MAIN' ? 'background:#eff6ff;color:#1d4ed8;border-color:#dbeafe;' : 'background:#fffbeb;color:#b45309;border-color:#fef3c7;';
            const statusStyle = branch.status === 'active' ? 'badge-success' : 'badge-error';
            badgesContainer.innerHTML = `
                <span class="status-badge" style="${typeStyle}font-size:9px;padding:2px 6px;">${branch.type || 'BRANCH'}</span>
                <span class="status-badge ${statusStyle}" style="font-size:9px;padding:2px 6px;">${(branch.status || 'ACTIVE').toUpperCase()}</span>
            `;
        }

        document.getElementById('view-branch-code').textContent = branch.branch_code || 'N/A';
        document.getElementById('view-branch-city').textContent = branch.state || 'N/A'; // DB does not store city
        document.getElementById('view-branch-state').textContent = branch.state || 'N/A';
        document.getElementById('view-branch-address').textContent = 'N/A'; // DB does not store address

        const avatarEl = document.getElementById('view-manager-avatar');
        const nameEl = document.getElementById('view-manager-name');
        const emailEl = document.getElementById('view-manager-email');
        const linkEl = document.getElementById('view-manager-link');

        if (branch.manager_name) {
            avatarEl.textContent = branch.manager_initials || '??';
            avatarEl.className = 'admin-avatar premium-gradient-primary';
            nameEl.textContent = branch.manager_name;
            emailEl.textContent = branch.manager_email || 'N/A';
            linkEl.classList.remove('hidden');
        } else {
            avatarEl.textContent = '??';
            avatarEl.className = 'admin-avatar bg-slate-200 text-slate-500';
            nameEl.textContent = 'Unassigned';
            emailEl.textContent = 'No manager assigned';
            linkEl.classList.add('hidden');
        }

        document.getElementById('view-staff-count').textContent = branch.staff_count || 0;
        document.getElementById('view-txn-count').textContent = branch.txn_count || 0;

        const certContainer = document.getElementById('view-cert-container');
        const certEmpty = document.getElementById('view-cert-empty');
        if (branch.cert_path) {
            certContainer.classList.remove('hidden');
            certContainer.classList.add('flex');
            certEmpty.classList.add('hidden');
            document.getElementById('view-cert-name').textContent = branch.cert_path.split('/').pop();
            const dlLink = document.getElementById('view-cert-download');
            dlLink.href = '../../' + branch.cert_path;
        } else {
            certContainer.classList.add('hidden');
            certContainer.classList.remove('flex');
            certEmpty.classList.remove('hidden');
        }

        document.getElementById('view-branch-desc').textContent = branch.description || 'No description provided.';
        
        window.lastViewedBranchId = branch.id;
        window.lastViewedBranchName = branch.name;

        document.getElementById('viewBranchModal').classList.remove('hidden');
        document.getElementById('viewBranchModal').classList.add('flex');
    }

    function showEditModal(id) {
        const branch = currentBranches.find(b => b.id == id);
        if (!branch) return;
        isEditMode = true;
        currentEditId = id;
        
        const form = document.getElementById('addBranchForm');
        if (form) {
            document.getElementById('add-branch-name').value = branch.name;
            document.getElementById('add-branch-code').value = branch.branch_code;
        }

        document.getElementById('addBranchModal').classList.remove('hidden');
        document.getElementById('addBranchModal').classList.add('flex');
    }

    function showDeleteModal(id, name, code) {
        if (name.toLowerCase() === 'barasat') {
            alert("The main branch 'Barasat' cannot be deleted.");
            return;
        }
        document.getElementById('target-branch-name').textContent = `${name} (${code})`;
        document.getElementById('confirmModal').classList.remove('hidden');
        document.getElementById('confirmModal').classList.add('flex');
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', () => {
        loadBranches();
        loadUnassignedStaff();
        bindEvents();
    });
})();
