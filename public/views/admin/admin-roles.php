<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin — Roles & Permissions';
$pageHeader = '
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-headline font-black text-text-primary tracking-tight">Roles & Permissions</h1>
        <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-0.5">Manage enterprise access control levels</p>
    </div>
    <div class="flex items-center gap-3">
        <button id="btn-create-role" class="btn btn-primary !px-5 flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">add</span>
            <span class="font-bold">Create Role</span>
        </button>
    </div>
</div>
';
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/admin-roles.js"></script>';
ob_start();
?>

<!-- TABLE WRAPPER -->
<div class="ds-card ds-card-dropdown-fix relative z-20 overflow-hidden">
    <!-- TABLE HEADER -->
    <div class="p-4 sm:p-6 flex flex-col sm:flex-row items-center justify-between gap-4 border-b border-border-subtle bg-background">
        <div class="text-sm font-bold text-text-primary uppercase tracking-widest flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[20px]">policy</span> Active Roles
        </div>
        <div class="relative w-full sm:w-64">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="material-symbols-outlined text-text-muted text-[18px]">search</span>
            </div>
            <input type="text" id="role-search" class="fin-input !pl-9 !py-2 !h-9 !text-sm w-full bg-white dark:bg-surface" placeholder="Search roles..." />
        </div>
    </div>

    <!-- TABLE AREA -->
    <div class="ds-table-wrapper bg-white dark:bg-surface min-h-[300px]">
        <table class="ds-table">
            <thead>
                <tr>
                    <th class="w-16">ID</th>
                    <th>Role Name</th>
                    <th>Description</th>
                    <th>Permissions</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody id="roles-tbody">
                <tr><td colspan="6" class="py-12 text-center"><span class="loader"></span></td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- CREATE/EDIT ROLE MODAL -->
<div id="role-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm modal-backdrop" onclick="document.getElementById('role-modal').classList.add('hidden')"></div>
    <div class="bg-surface border border-border-subtle rounded-2xl shadow-2xl w-full max-w-2xl relative z-10 max-h-[90vh] flex flex-col">
        <div class="p-6 border-b border-border-subtle flex justify-between items-center bg-background rounded-t-2xl">
            <h3 class="font-headline font-black tracking-tight text-lg" id="role-modal-title">Create New Role</h3>
            <button class="w-8 h-8 flex items-center justify-center rounded-lg bg-surface-subtle text-text-muted hover:text-text-primary hover:bg-border-subtle transition-colors" onclick="document.getElementById('role-modal').classList.add('hidden')">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
        <div class="p-6 overflow-y-auto custom-scrollbar flex-1">
            <form id="role-form" class="space-y-6">
                <input type="hidden" id="role-id" name="id" />
                
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="fin-label">Role Name</label>
                        <input type="text" id="role-name" name="name" class="fin-input" placeholder="e.g. Branch Manager" required />
                    </div>
                    <div>
                        <label class="fin-label">Description</label>
                        <textarea id="role-description" name="description" class="fin-input" placeholder="Briefly describe the purpose of this role..." rows="2"></textarea>
                    </div>
                    
                    <div class="pt-4 border-t border-border-subtle">
                        <label class="fin-label mb-4 flex justify-between items-center">
                            <span>Assign Permissions</span>
                            <button type="button" class="text-[10px] uppercase font-bold text-primary tracking-widest hover:underline" id="select-all-perms">Select All</button>
                        </label>
                        <div id="permissions-grid" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- Permissions loaded dynamically via JS -->
                            <div class="p-4 text-center text-text-muted text-sm col-span-full border border-dashed border-border-subtle rounded-xl"><span class="loader align-middle mr-2"></span> Loading permissions...</div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="p-6 border-t border-border-subtle bg-background rounded-b-2xl flex justify-end gap-3">
            <button type="button" class="btn btn-secondary !px-5" onclick="document.getElementById('role-modal').classList.add('hidden')">Cancel</button>
            <button type="button" class="btn btn-primary !px-5" id="btn-save-role">Save Role</button>
        </div>
    </div>
</div>

<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
