<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin — Roles & Permissions';
$pageHeader = '
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-headline font-black text-text-primary tracking-tight">Roles & Permissions</h1>
        <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-1">Manage enterprise access control levels & security policies</p>
    </div>
    <div class="flex items-center gap-3">
        <button id="btn-create-role" class="btn btn-primary !px-5 flex items-center gap-2 cursor-pointer shadow-lg shadow-primary/20">
            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">add_moderator</span>
            <span class="font-bold text-sm">Create New Role</span>
        </button>
    </div>
</div>
';
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/admin-roles.js?v=' . time() . '"></script>';
ob_start();
?>

<!-- KPI STATS SUMMARY GRID -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="ds-card p-5 relative overflow-hidden">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1">Defined Roles</p>
                <h3 id="stat-total-roles" class="text-2xl font-black font-headline text-text-primary">--</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-primary/10 border border-primary/20 flex items-center justify-center text-primary">
                <span class="material-symbols-outlined text-2xl" aria-hidden="true">shield_person</span>
            </div>
        </div>
        <div class="mt-3 flex items-center gap-1.5 text-[11px] font-bold text-text-secondary">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span>Enterprise RBAC Active</span>
        </div>
    </div>

    <div class="ds-card p-5 relative overflow-hidden">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1">System Permissions</p>
                <h3 id="stat-total-permissions" class="text-2xl font-black font-headline text-text-primary">--</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-500">
                <span class="material-symbols-outlined text-2xl" aria-hidden="true">verified_user</span>
            </div>
        </div>
        <div class="mt-3 flex items-center gap-1.5 text-[11px] font-bold text-text-secondary">
            <span class="material-symbols-outlined text-sm text-emerald-500" aria-hidden="true">lock_open</span>
            <span>Granular Capability Checks</span>
        </div>
    </div>

    <div class="ds-card p-5 relative overflow-hidden">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1">Protected Roles</p>
                <h3 id="stat-protected-roles" class="text-2xl font-black font-headline text-text-primary">--</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-500">
                <span class="material-symbols-outlined text-2xl" aria-hidden="true">lock</span>
            </div>
        </div>
        <div class="mt-3 flex items-center gap-1.5 text-[11px] font-bold text-text-secondary">
            <span class="material-symbols-outlined text-sm text-amber-500" aria-hidden="true">security</span>
            <span>System Default Guards</span>
        </div>
    </div>
</div>

<!-- TABLE WRAPPER -->
<div class="ds-card relative z-20 overflow-hidden shadow-2xl transition-all duration-300">
    <!-- TABLE HEADER -->
    <div class="p-4 sm:p-6 flex flex-col sm:flex-row items-center justify-between gap-4 border-b border-border-subtle bg-surface/50 backdrop-blur-xl relative z-10">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[22px]" aria-hidden="true">policy</span>
            <span class="text-sm font-black text-text-primary uppercase tracking-widest">Configured Roles</span>
        </div>
        <div class="relative w-full sm:w-72">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-text-muted">
                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">search</span>
            </div>
            <input type="text" id="role-search" class="fin-input !pl-9 !py-2 !h-10 !text-sm w-full bg-surface-subtle" placeholder="Filter by role name or description..." />
        </div>
    </div>

    <!-- TABLE AREA -->
    <div class="ds-table-wrapper bg-surface min-h-[300px]">
        <table class="ds-table w-full text-left">
            <thead>
                <tr>
                    <th class="w-16">ID</th>
                    <th>Role Identity</th>
                    <th>Description</th>
                    <th>Assigned Capabilities</th>
                    <th>Scope</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody id="roles-tbody">
                <tr>
                    <td colspan="6" class="py-16 text-center">
                        <div class="flex flex-col items-center justify-center gap-3 text-text-secondary">
                            <span class="material-symbols-outlined animate-spin text-3xl text-primary" aria-hidden="true">autorenew</span>
                            <span class="text-xs font-bold uppercase tracking-wider">Loading enterprise roles...</span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- CREATE/EDIT ROLE MODAL -->
<div id="role-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-sm modal-backdrop" onclick="window.RoleManager.closeModal()"></div>
    <div class="bg-surface border border-border-subtle rounded-2xl shadow-2xl w-full max-w-3xl relative z-10 max-h-[90vh] flex flex-col overflow-hidden">
        <div class="p-6 border-b border-border-subtle flex justify-between items-center bg-surface-subtle">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center border border-primary/20">
                    <span class="material-symbols-outlined text-xl" aria-hidden="true">admin_panel_settings</span>
                </div>
                <div>
                    <h3 class="font-headline font-black tracking-tight text-lg text-text-primary" id="role-modal-title">Create New Role</h3>
                    <p class="text-[11px] font-bold text-text-secondary uppercase tracking-widest mt-0.5">Configure access level and permission matrix</p>
                </div>
            </div>
            <button type="button" class="w-8 h-8 flex items-center justify-center rounded-lg bg-surface text-text-muted hover:text-text-primary hover:bg-border-subtle transition-colors cursor-pointer" onclick="window.RoleManager.closeModal()">
                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">close</span>
            </button>
        </div>
        
        <div class="p-6 overflow-y-auto custom-scrollbar flex-1 space-y-6">
            <form id="role-form" class="space-y-6" onsubmit="return false;">
                <input type="hidden" id="role-id" name="id" />
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label for="role-name" class="fin-label text-[11px] font-black uppercase text-text-secondary tracking-wider block mb-2">Role Name <span class="text-error">*</span></label>
                        <input type="text" id="role-name" name="name" class="fin-input w-full" placeholder="e.g. Compliance Officer, Auditor" required />
                    </div>
                    <div class="sm:col-span-2">
                        <label for="role-description" class="fin-label text-[11px] font-black uppercase text-text-secondary tracking-wider block mb-2">Description</label>
                        <textarea id="role-description" name="description" class="fin-input w-full" placeholder="Briefly describe the operational responsibilities of this role..." rows="2"></textarea>
                    </div>
                </div>
                
                <div class="pt-4 border-t border-border-subtle">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <span class="text-xs font-black uppercase tracking-wider text-text-primary">Assign Permissions Matrix</span>
                            <span id="perm-selected-count" class="ml-2 text-[10px] font-black bg-primary/10 text-primary border border-primary/20 px-2 py-0.5 rounded-full">0 selected</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" class="text-[11px] uppercase font-bold text-primary tracking-wider hover:underline cursor-pointer" id="select-all-perms">Select All</button>
                            <span class="text-text-muted text-xs">|</span>
                            <button type="button" class="text-[11px] uppercase font-bold text-text-secondary tracking-wider hover:underline cursor-pointer" id="clear-all-perms">Clear All</button>
                        </div>
                    </div>

                    <div id="permissions-container" class="space-y-6">
                        <!-- Categorized permissions loaded dynamically via JS -->
                        <div class="p-8 text-center text-text-muted text-xs border border-dashed border-border-subtle rounded-xl flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined animate-spin text-base" aria-hidden="true">autorenew</span>
                            Loading system permissions...
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="p-4 sm:p-6 border-t border-border-subtle bg-surface-subtle flex justify-end gap-3">
            <button type="button" class="btn btn-secondary !px-5 cursor-pointer font-bold text-xs uppercase tracking-wider" onclick="window.RoleManager.closeModal()">Cancel</button>
            <button type="button" class="btn btn-primary !px-6 cursor-pointer font-bold text-xs uppercase tracking-wider" id="btn-save-role">
                <span class="material-symbols-outlined text-base mr-1.5" aria-hidden="true">save</span>
                Save Role
            </button>
        </div>
    </div>
</div>

<!-- DELETE ROLE CONFIRMATION MODAL -->
<div id="delete-role-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-sm" onclick="window.RoleManager.closeDeleteModal()"></div>
    <div class="ds-card w-full max-w-md shadow-2xl overflow-hidden relative z-10 bg-surface border border-border-subtle">
        <div class="p-8 text-center text-text-primary">
            <div class="w-16 h-16 rounded-full bg-error-50 text-error flex items-center justify-center mx-auto mb-4 border border-error/20">
                <span class="material-symbols-outlined text-3xl" aria-hidden="true">delete_forever</span>
            </div>
            <h3 class="text-xl font-black font-headline mb-2 text-text-primary">Delete Custom Role</h3>
            <p class="text-sm text-text-secondary leading-relaxed mb-6">
                Are you sure you want to permanently delete the role <strong id="delete-role-name-display" class="text-text-primary font-black"></strong>? This action cannot be undone.
            </p>
            <input type="hidden" id="delete-role-id" />
            <div class="flex gap-3">
                <button type="button" onclick="window.RoleManager.closeDeleteModal()" class="flex-1 btn btn-secondary py-3 justify-center text-xs font-bold uppercase tracking-wider cursor-pointer">
                    Cancel
                </button>
                <button type="button" id="confirm-delete-role-btn" onclick="window.RoleManager.confirmDelete()" class="flex-1 btn btn-danger py-3 justify-center text-xs font-bold uppercase tracking-wider cursor-pointer !bg-error hover:!bg-error-hover text-white">
                    Confirm Delete
                </button>
            </div>
        </div>
    </div>
</div>

<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
