<?php
$currentPath = basename($_SERVER['PHP_SELF']);
function isActive($path, $current) {
    return (strpos($current, $path) !== false) ? 'bg-primary-50 text-primary border-r-4 border-primary' : 'text-text-secondary hover:bg-background hover:text-text-primary border-r-4 border-transparent';
}
?>
<!-- Admin Sidebar -->
<aside class="admin-sidebar fixed top-16 left-0 h-[calc(100vh-4rem)] w-72 bg-surface border-r border-border-subtle z-40 transition-transform duration-300 -translate-x-full lg:translate-x-0 flex flex-col" id="admin-sidebar">
    
    <!-- Brand / Role Indicator -->
    <div class="px-6 py-5 border-b border-border-subtle bg-background">
        <div class="flex items-center gap-3 mb-1">
            <div class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center shadow-sm">
                <span class="material-symbols-outlined text-white text-[18px]" aria-hidden="true">admin_panel_settings</span>
            </div>
            <div class="font-headline font-black text-xl tracking-tight text-text-primary">Finzo</div>
        </div>
        <div class="text-[10px] font-black text-primary uppercase tracking-widest pl-11">Enterprise Admin</div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto py-4 custom-scrollbar" aria-label="Sidebar Navigation">
        <div class="px-6 mb-2">
            <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest">Core Operations</div>
        </div>
        
        <ul class="space-y-1 px-3">
            <li>
                <a href="admin-dashboard.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg font-bold transition-all <?= isActive('dashboard', $currentPath) ?>">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">analytics</span>
                    <span class="text-sm">Executive Overview</span>
                </a>
            </li>
            <li>
                <a href="admin-pending-approvals.php" class="flex items-center justify-between px-4 py-2.5 rounded-lg font-bold transition-all group <?= isActive('approval', $currentPath) ?>">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">rule</span>
                        <span class="text-sm">Approvals</span>
                    </div>
                    <span class="px-2 py-0.5 rounded-full bg-warning-bg text-warning text-[10px] font-black tracking-widest uppercase shadow-sm">...</span>
                </a>
            </li>
            <li>
                <a href="admin-users.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg font-bold transition-all <?= isActive('user', $currentPath) ?>">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">group</span>
                    <span class="text-sm">User Management</span>
                </a>
            </li>
            <li>
                <a href="admin-staff.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg font-bold transition-all <?= isActive('staff', $currentPath) ?>">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">badge</span>
                    <span class="text-sm">Staff Management</span>
                </a>
            </li>
            <li>
                <a href="admin-branches.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg font-bold transition-all <?= isActive('branch', $currentPath) ?>">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">store</span>
                    <span class="text-sm">Branch Operations</span>
                </a>
            </li>
            <li>
                <a href="admin-transactions.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg font-bold transition-all <?= isActive('transaction', $currentPath) ?>">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">swap_horiz</span>
                    <span class="text-sm">Global Transactions</span>
                </a>
            </li>
            <li>
                <a href="admin-audit-log.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg font-bold transition-all <?= isActive('audit', $currentPath) ?>">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">manage_search</span>
                    <span class="text-sm">System Audit Log</span>
                </a>
            </li>
        </ul>

        <div class="px-6 mb-2 mt-6">
            <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest">System & Security</div>
        </div>
        
        <ul class="space-y-1 px-3">
            <li>
                <a href="admin-suspended-users.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg font-bold transition-all <?= isActive('suspended-user', $currentPath) ?>">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">person_off</span>
                    <span class="text-sm">Suspended Users</span>
                </a>
            </li>
            <li>
                <a href="admin-suspended-staff.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg font-bold transition-all <?= isActive('suspended-staff', $currentPath) ?>">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">work_off</span>
                    <span class="text-sm">Suspended Staff</span>
                </a>
            </li>
            <li>
                <a href="admin-locked-accounts.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg font-bold transition-all <?= isActive('locked', $currentPath) ?>">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">lock_person</span>
                    <span class="text-sm">Locked Accounts</span>
                </a>
            </li>
            <li>
                <a href="admin-deleted-users.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg font-bold transition-all <?= isActive('delete', $currentPath) ?>">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">person_remove</span>
                    <span class="text-sm">Deleted Users</span>
                </a>
            </li>
            <li>
                <a href="admin-interest.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg font-bold transition-all <?= isActive('interest', $currentPath) ?>">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">percent</span>
                    <span class="text-sm">Interest Engine</span>
                </a>
            </li>
        </ul>
    </nav>
    
    <!-- Footer Profile Actions -->
    <div class="p-4 border-t border-border-subtle bg-background">
        <ul class="space-y-1">
            <li>
                <a href="admin-profile.php" class="flex items-center gap-3 px-3 py-2 rounded-lg font-bold transition-all <?= isActive('profile', $currentPath) ?>">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">account_circle</span>
                    <span class="text-sm">My Profile</span>
                </a>
            </li>
            <li>
                <button data-toggle-theme class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-text-secondary hover:bg-background hover:text-text-primary font-bold transition-all text-left">
                    <span class="material-symbols-outlined text-[20px]" data-theme-icon aria-hidden="true">light_mode</span>
                    <span class="text-sm" data-theme-label>Toggle Theme</span>
                </button>
            </li>
            <li>
                <a href="../auth/admin-login.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-error hover:bg-error-bg font-bold transition-all mt-2">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">logout</span>
                    <span class="text-sm">Secure Logout</span>
                </a>
            </li>
        </ul>
    </div>
</aside>
