/**
 * Finzo Admin — Roles & Permissions Engine Handler
 * Manages enterprise roles, permission matrix mapping, and access governance.
 */

(function() {
    let roles = [];
    let permissions = [];

    // Categorization definitions for system permissions
    const CATEGORIES = {
        'User & Customer Management': [
            'manage_users',
            'suspend_user',
            'reinstate_user',
            'soft_delete_user',
            'approve_application',
            'manage_own_account'
        ],
        'Financial & Transactions': [
            'change_interest_rate',
            'process_transactions'
        ],
        'Staff & Branch Administration': [
            'add_branch',
            'manage_admins',
            'add_staff',
            'suspend_staff',
            'delete_staff'
        ],
        'Audit & Governance': [
            'view_audit_logs'
        ]
    };

    function toast(msg, type = 'info') {
        if (window.showToast) window.showToast(msg, type);
        else if (window.portal && window.portal.showToast) window.portal.showToast(msg, type);
        else if (window.FinzoUtils && window.FinzoUtils.showToast) window.FinzoUtils.showToast(msg, type);
        else alert(msg);
    }

    /**
     * Escape untrusted strings before injecting into HTML/attribute context.
     * Role names/descriptions are admin-supplied and must never be rendered raw.
     */
    function escapeHtml(value) {
        if (value === null || value === undefined) return '';
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    async function loadRoles() {
        try {
            const response = await window.api.get('/admin/roles');
            if (response.success || response.status === 'success') {
                roles = response.data || [];
                updateStats();
                renderRoles();
            } else {
                toast(response.message || 'Failed to retrieve roles', 'error');
            }
        } catch (error) {
            console.error('Failed to load roles:', error);
            toast('Network error loading roles', 'error');
        }
    }

    async function loadPermissions() {
        try {
            const response = await window.api.get('/admin/permissions');
            if (response.success || response.status === 'success') {
                permissions = response.data || [];
                renderPermissionsMatrix();
                updateStats();
            }
        } catch (error) {
            console.error('Failed to load permissions:', error);
        }
    }

    function updateStats() {
        const totalRolesEl = document.getElementById('stat-total-roles');
        const totalPermsEl = document.getElementById('stat-total-permissions');
        const protectedRolesEl = document.getElementById('stat-protected-roles');

        if (totalRolesEl) totalRolesEl.textContent = roles.length;
        if (totalPermsEl) totalPermsEl.textContent = permissions.length;
        if (protectedRolesEl) {
            const protectedCount = roles.filter(r => isProtectedRole(r.name)).length;
            protectedRolesEl.textContent = protectedCount;
        }
    }

    function isProtectedRole(roleName) {
        if (!roleName) return false;
        const normalized = roleName.toLowerCase().trim();
        return ['super_admin', 'super admin', 'admin', 'customer'].includes(normalized);
    }

    function formatRoleName(name) {
        if (!name) return '';
        return name
            .split('_')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ');
    }

    function renderRoles() {
        const tbody = document.getElementById('roles-tbody');
        if (!tbody) return;

        if (!roles.length) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="py-16 text-center text-text-secondary">
                        <div class="flex flex-col items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-4xl text-text-secondary/40" aria-hidden="true">policy</span>
                            <span class="text-xs font-bold uppercase tracking-wider">No roles defined in the system.</span>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        const query = (document.getElementById('role-search')?.value || '').toLowerCase().trim();
        const filtered = roles.filter(r => 
            (r.name && r.name.toLowerCase().includes(query)) || 
            (r.description && r.description.toLowerCase().includes(query))
        );

        if (!filtered.length) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="py-12 text-center text-text-secondary">
                        <span class="text-xs font-bold uppercase tracking-wider">No roles matching "${escapeHtml(query)}"</span>
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        filtered.forEach(role => {
            const isProtected = isProtectedRole(role.name);
            const isSuper = (role.name || '').toLowerCase() === 'super_admin';
            const permCount = Array.isArray(role.permissions) ? role.permissions.length : (role.permissions_count || 0);
            const safeFormattedName = escapeHtml(formatRoleName(role.name));
            const safeName = escapeHtml(role.name);
            const safeDescription = escapeHtml(role.description || '');

            let iconName = 'shield';
            let iconClass = 'bg-primary/10 text-primary border-primary/20';
            if (isSuper) {
                iconName = 'workspace_premium';
                iconClass = 'bg-amber-500/10 text-amber-500 border-amber-500/20';
            } else if (isProtected) {
                iconName = 'security';
                iconClass = 'bg-blue-500/10 text-blue-500 border-blue-500/20';
            }

            html += `
            <tr class="hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-colors border-b border-border-subtle last:border-b-0">
                <td class="font-mono text-xs font-bold text-text-muted py-4 px-4">#${role.id}</td>
                <td class="py-4 px-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl ${iconClass} border flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-lg" aria-hidden="true">${iconName}</span>
                        </div>
                        <div>
                            <div class="font-black text-sm text-text-primary flex items-center gap-2">
                                <span>${safeFormattedName}</span>
                                ${isSuper ? '<span class="text-[9px] font-black uppercase bg-amber-500/10 text-amber-500 border border-amber-500/20 px-1.5 py-0.5 rounded">Super</span>' : ''}
                            </div>
                            <span class="font-mono text-[10px] text-text-secondary">${safeName}</span>
                        </div>
                    </div>
                </td>
                <td class="text-xs text-text-secondary py-4 px-4 max-w-[240px] truncate" title="${safeDescription}">
                    ${role.description ? safeDescription : '<span class="italic text-text-muted">No description provided</span>'}
                </td>
                <td class="py-4 px-4">
                    <span class="role-capability-badge inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-black bg-slate-100 dark:bg-white/10 text-slate-800 dark:text-slate-100 border border-slate-200 dark:border-white/15 shadow-2xs">
                        <span class="material-symbols-outlined text-xs" aria-hidden="true">key</span>
                        ${permCount} Permissions
                    </span>
                </td>
                <td class="py-4 px-4">
                    <span class="status-badge ${isProtected ? 'badge-warn' : 'badge-neutral'} inline-flex items-center gap-1 text-[10px] font-black uppercase tracking-wider">
                        ${isProtected ? '<span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> System Protected' : '<span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Custom'}
                    </span>
                </td>
                <td class="text-right py-4 px-4">
                    <div class="flex items-center justify-end gap-1.5">
                        <button type="button" class="btn btn-secondary !px-3 !py-1.5 !text-xs !rounded-xl inline-flex items-center gap-1 cursor-pointer hover:border-primary/40" onclick="window.RoleManager.openModal(${role.id})" title="Edit Role & Permissions">
                            <span class="material-symbols-outlined text-sm" aria-hidden="true">tune</span>
                            <span>Edit</span>
                        </button>
                        ${!isProtected ? `
                        <button type="button" class="btn btn-secondary !px-2.5 !py-1.5 !text-xs !rounded-xl inline-flex items-center gap-1 text-error hover:!bg-error/10 hover:!border-error/40 cursor-pointer" onclick="window.RoleManager.openDeleteModal(${Number(role.id)})" title="Delete Role">
                            <span class="material-symbols-outlined text-sm" aria-hidden="true">delete</span>
                        </button>
                        ` : ''}
                    </div>
                </td>
            </tr>
            `;
        });
        tbody.innerHTML = html;
    }

    function renderPermissionsMatrix() {
        const container = document.getElementById('permissions-container');
        if (!container) return;

        if (!permissions.length) {
            container.innerHTML = '<div class="p-6 text-center text-xs text-text-muted">No permissions available.</div>';
            return;
        }

        // Partition permissions into categories
        const categorized = {};
        const unassigned = [];

        Object.keys(CATEGORIES).forEach(cat => {
            categorized[cat] = [];
        });

        permissions.forEach(p => {
            let placed = false;
            if (p.group_name) {
                if (!categorized[p.group_name]) {
                    categorized[p.group_name] = [];
                }
                categorized[p.group_name].push(p);
                placed = true;
            } else {
                for (const [catName, permNames] of Object.entries(CATEGORIES)) {
                    if (permNames.includes(p.name)) {
                        categorized[catName].push(p);
                        placed = true;
                        break;
                    }
                }
            }
            if (!placed) {
                unassigned.push(p);
            }
        });

        if (unassigned.length > 0) {
            categorized['General & Custom Capabilities'] = unassigned;
        }

        let html = '';
        for (const [catName, permList] of Object.entries(categorized)) {
            if (!permList.length) continue;

            const categoryId = 'cat-' + catName.toLowerCase().replace(/[^a-z0-9]/g, '-');

            html += `
            <div class="border border-border-subtle rounded-2xl p-4 bg-surface-subtle/40">
                <div class="flex items-center justify-between pb-3 mb-3 border-b border-border-subtle/60">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-base" aria-hidden="true">folder_supervised</span>
                        <h4 class="text-xs font-black uppercase tracking-wider text-text-primary">${catName}</h4>
                        <span class="text-[10px] font-bold text-text-secondary bg-surface px-2 py-0.5 rounded-full border border-border-subtle">${permList.length}</span>
                    </div>
                    <button type="button" class="text-[10px] uppercase font-bold text-primary hover:underline cursor-pointer toggle-cat-btn" data-category="${categoryId}">Toggle Section</button>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="${categoryId}">
                    ${permList.map(p => `
                        <label class="flex items-start gap-3 p-3 rounded-xl border border-border-subtle bg-surface hover:border-primary/40 transition-colors cursor-pointer group select-none">
                            <div class="mt-0.5">
                                <input type="checkbox" name="permissions[]" value="${p.id}" class="fin-checkbox perm-checkbox rounded text-primary focus:ring-primary/20">
                            </div>
                            <div class="flex flex-col min-w-0">
                                <span class="text-xs font-black text-text-primary group-hover:text-primary transition-colors truncate">${escapeHtml(formatRoleName(p.name))}</span>
                                <span class="text-[10px] text-text-secondary font-medium leading-tight mt-0.5">${escapeHtml(p.description || p.name)}</span>
                            </div>
                        </label>
                    `).join('')}
                </div>
            </div>
            `;
        }

        container.innerHTML = html;
        bindPermissionToggles();
    }

    function bindPermissionToggles() {
        document.querySelectorAll('.toggle-cat-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const targetId = e.target.getAttribute('data-category');
                const groupContainer = document.getElementById(targetId);
                if (groupContainer) {
                    const checkboxes = groupContainer.querySelectorAll('.perm-checkbox');
                    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                    checkboxes.forEach(cb => cb.checked = !allChecked);
                    updateSelectedPermCount();
                }
            });
        });

        document.querySelectorAll('.perm-checkbox').forEach(cb => {
            cb.addEventListener('change', updateSelectedPermCount);
        });
    }

    function updateSelectedPermCount() {
        const countEl = document.getElementById('perm-selected-count');
        const checked = document.querySelectorAll('.perm-checkbox:checked').length;
        const total = document.querySelectorAll('.perm-checkbox').length;
        if (countEl) {
            countEl.textContent = `${checked} / ${total} selected`;
        }
    }

    // Exported Object for Global Interactions
    window.RoleManager = {
        openModal: (id = null) => {
            const modal = document.getElementById('role-modal');
            const form = document.getElementById('role-form');
            const title = document.getElementById('role-modal-title');
            const nameInput = document.getElementById('role-name');
            const descInput = document.getElementById('role-description');
            
            if (form) form.reset();
            document.getElementById('role-id').value = id || '';
            document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = false);

            if (id) {
                const role = roles.find(r => r.id == id);
                if (role) {
                    title.textContent = `Edit Role: ${formatRoleName(role.name)}`;
                    if (nameInput) {
                        nameInput.value = role.name;
                        // Prevent changing super_admin name
                        nameInput.disabled = (role.name === 'super_admin');
                    }
                    if (descInput) descInput.value = role.description || '';
                    
                    if (Array.isArray(role.permissions)) {
                        const permIds = role.permissions.map(p => typeof p === 'object' ? p.id : p);
                        permIds.forEach(pid => {
                            const cb = document.querySelector(`.perm-checkbox[value="${pid}"]`);
                            if (cb) cb.checked = true;
                        });
                    }
                }
            } else {
                title.textContent = 'Create New Role';
                if (nameInput) {
                    nameInput.disabled = false;
                    nameInput.value = '';
                }
                if (descInput) descInput.value = '';
            }

            updateSelectedPermCount();

            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        },

        closeModal: () => {
            const modal = document.getElementById('role-modal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        },

        openDeleteModal: (id) => {
            const modal = document.getElementById('delete-role-modal');
            const nameDisplay = document.getElementById('delete-role-name-display');
            const idInput = document.getElementById('delete-role-id');

            if (idInput) idInput.value = id;
            // Resolve the display name from the in-memory role list (never trust inline args)
            const role = roles.find(r => Number(r.id) === Number(id));
            if (nameDisplay) nameDisplay.textContent = role ? (formatRoleName(role.name) || role.name) : '';

            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        },

        closeDeleteModal: () => {
            const modal = document.getElementById('delete-role-modal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        },

        confirmDelete: async () => {
            const id = document.getElementById('delete-role-id')?.value;
            const btn = document.getElementById('confirm-delete-role-btn');
            if (!id || !btn || btn.disabled) return;

            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="material-symbols-outlined animate-spin text-sm mr-1" aria-hidden="true">autorenew</span> Deleting...';

            try {
                const res = await window.api.delete(`/admin/roles/${id}`);
                if (res.success || res.status === 'success') {
                    toast(res.message || 'Role deleted successfully', 'success');
                    window.RoleManager.closeDeleteModal();
                    loadRoles();
                } else {
                    toast(res.message || 'Failed to delete role', 'error');
                }
            } catch (err) {
                console.error('Delete role error:', err);
                toast(err.message || 'Error occurred while deleting role', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        },

        saveRole: async () => {
            const id = document.getElementById('role-id')?.value;
            const nameInput = document.getElementById('role-name');
            const descInput = document.getElementById('role-description');
            const btn = document.getElementById('btn-save-role');

            const name = nameInput ? nameInput.value.trim() : '';
            const desc = descInput ? descInput.value.trim() : '';

            if (!name) {
                toast('Please provide a valid role name.', 'error');
                if (nameInput) nameInput.focus();
                return;
            }

            const permInputs = document.querySelectorAll('.perm-checkbox:checked');
            const permIds = Array.from(permInputs).map(cb => parseInt(cb.value, 10));

            const payload = {
                name: name,
                description: desc,
                permissions: permIds
            };

            if (!btn || btn.disabled) return;
            const originalHTML = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="material-symbols-outlined animate-spin text-base mr-1.5" aria-hidden="true">autorenew</span> Saving...';

            try {
                let res;
                if (id) {
                    res = await window.api.put(`/admin/roles/${id}`, payload);
                } else {
                    res = await window.api.post('/admin/roles', payload);
                }

                if (res.success || res.status === 'success') {
                    toast(id ? 'Role updated successfully' : 'Role created successfully', 'success');
                    window.RoleManager.closeModal();
                    loadRoles();
                } else {
                    toast(res.message || 'Failed to save role', 'error');
                }
            } catch (err) {
                console.error('Save role error:', err);
                toast(err.message || 'Error saving role', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalHTML;
            }
        }
    };

    // Event Bindings
    document.addEventListener('DOMContentLoaded', () => {
        loadPermissions();
        loadRoles();

        document.getElementById('role-search')?.addEventListener('input', renderRoles);

        document.getElementById('btn-create-role')?.addEventListener('click', () => {
            window.RoleManager.openModal(null);
        });

        document.getElementById('select-all-perms')?.addEventListener('click', () => {
            document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = true);
            updateSelectedPermCount();
        });

        document.getElementById('clear-all-perms')?.addEventListener('click', () => {
            document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = false);
            updateSelectedPermCount();
        });

        document.getElementById('btn-save-role')?.addEventListener('click', () => {
            window.RoleManager.saveRole();
        });

        // ESC key modal dismissal
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                window.RoleManager.closeModal();
                window.RoleManager.closeDeleteModal();
            }
        });
    });

})();
