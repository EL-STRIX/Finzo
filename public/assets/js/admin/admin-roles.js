(function() {
    let roles = [];
    let permissions = [];

    async function loadRoles() {
        try {
            const response = await window.api.get('/admin/roles');
            if (response.success) {
                roles = response.data || [];
                renderRoles();
            }
        } catch (error) {
            console.error('Failed to load roles:', error);
            window.showToast('Failed to load roles', 'error');
        }
    }

    async function loadPermissions() {
        try {
            const response = await window.api.get('/admin/permissions');
            if (response.success) {
                permissions = response.data || [];
                renderPermissionsGrid();
            }
        } catch (error) {
            console.error('Failed to load permissions:', error);
        }
    }

    function renderRoles() {
        const tbody = document.getElementById('roles-tbody');
        if (!tbody) return;

        if (!roles.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="py-12 text-center text-text-muted">No roles found.</td></tr>';
            return;
        }

        const query = document.getElementById('role-search')?.value.toLowerCase() || '';
        const filtered = roles.filter(r => r.name.toLowerCase().includes(query) || (r.description && r.description.toLowerCase().includes(query)));

        let html = '';
        filtered.forEach(role => {
            const statusClass = role.is_active ? 'badge-success' : 'badge-danger';
            const statusText = role.is_active ? 'ACTIVE' : 'INACTIVE';
            
            html += `
            <tr class="hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">
                <td class="font-mono text-xs opacity-60">#${role.id}</td>
                <td class="font-bold">${role.name}</td>
                <td class="text-sm opacity-80 truncate max-w-[200px]">${role.description || '-'}</td>
                <td class="text-xs font-bold text-primary">${role.permissions ? role.permissions.length : 0} Perms</td>
                <td><span class="status-badge ${statusClass}">${statusText}</span></td>
                <td class="text-right">
                    <button class="btn btn-secondary !px-3 !py-1.5 !text-[10px] !rounded-lg inline-flex items-center gap-1 btn-edit-role" data-id="${role.id}">
                        <span class="material-symbols-outlined !text-[14px]">edit</span> Edit
                    </button>
                    ${role.name.toLowerCase() !== 'super admin' ? `
                    <button class="btn btn-secondary !px-3 !py-1.5 !text-[10px] !rounded-lg inline-flex items-center gap-1 text-error hover:bg-error-bg hover:border-error btn-delete-role" data-id="${role.id}">
                        <span class="material-symbols-outlined !text-[14px]">delete</span> Delete
                    </button>
                    ` : ''}
                </td>
            </tr>
            `;
        });
        tbody.innerHTML = html;
        bindRowEvents();
    }

    function renderPermissionsGrid() {
        const grid = document.getElementById('permissions-grid');
        if (!grid) return;
        
        let html = '';
        permissions.forEach(p => {
            html += `
            <label class="flex items-start gap-3 p-3 rounded-xl border border-border-subtle hover:bg-background transition-colors cursor-pointer group">
                <div class="mt-0.5">
                    <input type="checkbox" name="permissions[]" value="${p.id}" class="fin-checkbox perm-checkbox">
                </div>
                <div class="flex flex-col">
                    <span class="text-sm font-bold group-hover:text-primary transition-colors">${p.name}</span>
                    <span class="text-[10px] text-text-muted mt-0.5">${p.description || ''}</span>
                </div>
            </label>
            `;
        });
        grid.innerHTML = html;
    }

    function bindRowEvents() {
        document.querySelectorAll('.btn-edit-role').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.currentTarget.dataset.id;
                openModal(id);
            });
        });

        document.querySelectorAll('.btn-delete-role').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const id = e.currentTarget.dataset.id;
                if(confirm('Are you sure you want to delete this role?')) {
                    try {
                        const res = await window.api.delete(`/admin/roles/${id}`);
                        if(res.success) {
                            window.showToast('Role deleted successfully');
                            loadRoles();
                        } else {
                            window.showToast(res.message || 'Failed to delete', 'error');
                        }
                    } catch(err) {
                        window.showToast('Failed to delete role', 'error');
                    }
                }
            });
        });
    }

    function openModal(id = null) {
        const modal = document.getElementById('role-modal');
        const form = document.getElementById('role-form');
        const title = document.getElementById('role-modal-title');
        
        form.reset();
        document.getElementById('role-id').value = id || '';
        document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = false);

        if (id) {
            title.textContent = 'Edit Role';
            const role = roles.find(r => r.id == id);
            if (role) {
                document.getElementById('role-name').value = role.name;
                document.getElementById('role-description').value = role.description || '';
                
                if (role.permissions) {
                    role.permissions.forEach(p => {
                        const cb = document.querySelector(`.perm-checkbox[value="${p.id}"]`);
                        if (cb) cb.checked = true;
                    });
                }
            }
        } else {
            title.textContent = 'Create New Role';
        }

        modal.classList.remove('hidden');
    }

    function init() {
        loadPermissions();
        loadRoles();

        document.getElementById('role-search')?.addEventListener('input', renderRoles);

        document.getElementById('btn-create-role')?.addEventListener('click', () => {
            openModal(null);
        });

        document.getElementById('select-all-perms')?.addEventListener('click', () => {
            const checkboxes = document.querySelectorAll('.perm-checkbox');
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            checkboxes.forEach(cb => cb.checked = !allChecked);
        });

        document.getElementById('btn-save-role')?.addEventListener('click', async () => {
            const form = document.getElementById('role-form');
            if(!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const id = document.getElementById('role-id').value;
            const name = document.getElementById('role-name').value;
            const desc = document.getElementById('role-description').value;
            const permInputs = document.querySelectorAll('.perm-checkbox:checked');
            const permIds = Array.from(permInputs).map(cb => parseInt(cb.value));

            const payload = {
                name: name,
                description: desc,
                permissions: permIds
            };

            const btn = document.getElementById('btn-save-role');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="loader"></span> Saving...';
            btn.disabled = true;

            try {
                let res;
                if (id) {
                    res = await window.api.put(`/admin/roles/${id}`, payload);
                } else {
                    res = await window.api.post('/admin/roles', payload);
                }

                if (res.success) {
                    window.showToast(id ? 'Role updated' : 'Role created', 'success');
                    document.getElementById('role-modal').classList.add('hidden');
                    loadRoles();
                } else {
                    window.showToast(res.message || 'Validation failed', 'error');
                }
            } catch (err) {
                console.error(err);
                window.showToast('Error saving role', 'error');
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
