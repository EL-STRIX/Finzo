<?php
$currentPath = basename($_SERVER['PHP_SELF']);
function isActive($key, $current) {
    $active = false;
    switch ($key) {
        case 'dashboard':
            $active = ($current === 'admin-dashboard.php' || $current === 'admin_dashboard.php');
            break;
        case 'approvals':
            $active = (str_contains($current, 'approval') || str_contains($current, 'review-application'));
            break;
        case 'users':
            $active = ($current === 'admin-users.php' || $current === 'admin-user-detail.php' || $current === 'admin_user_detail.php');
            break;
        case 'staff':
            $active = ($current === 'admin-staff.php' || $current === 'admin-staff-detail.php' || $current === 'admin_staff_detail.php');
            break;
        case 'branches':
            $active = str_contains($current, 'branch');
            break;
        case 'transactions':
            $active = str_contains($current, 'transaction');
            break;
        case 'audit':
            $active = str_contains($current, 'audit');
            break;
        case 'support':
            $active = str_contains($current, 'support');
            break;
        case 'loan-products':
            $active = str_contains($current, 'loan-product');
            break;
        case 'loans':
            $active = (str_contains($current, 'loan') && !str_contains($current, 'loan-product'));
            break;
        case 'card-products':
            $active = str_contains($current, 'card-product');
            break;
        case 'cards':
            $active = (str_contains($current, 'card') && !str_contains($current, 'card-product'));
            break;
        case 'ledger':
            $active = str_contains($current, 'ledger');
            break;
        case 'reports':
            $active = str_contains($current, 'report');
            break;
        case 'suspended-users':
            $active = str_contains($current, 'suspended-user');
            break;
        case 'suspended-staff':
            $active = str_contains($current, 'suspended-staff');
            break;
        case 'locked':
            $active = str_contains($current, 'locked');
            break;
        case 'deleted-users':
            $active = (str_contains($current, 'deleted-user') || str_contains($current, 'deleted-users'));
            break;
        case 'interest':
            $active = str_contains($current, 'interest');
            break;
        case 'roles':
            $active = str_contains($current, 'role');
            break;
        case 'settings':
            $active = str_contains($current, 'settings');
            break;
        case 'profile':
            $active = (str_contains($current, 'profile') || str_contains($current, 'admin-detail') || str_contains($current, 'admin_detail'));
            break;
        default:
            $active = ($current === $key);
            break;
    }

    return $active
        ? 'bg-primary/5 dark:bg-primary/10 text-accent border-r-4 border-accent shadow-[inset_0_2px_4px_rgba(0,0,0,0.02)] active-nav-item'
        : 'text-text-secondary hover:bg-background hover:text-text-primary border-r-4 border-transparent';
}

?>
<!-- Admin Sidebar -->
<aside class="admin-sidebar fixed top-16 left-0 h-[calc(100vh-4rem)] w-72 bg-surface border-r border-border-subtle z-40 transition-transform duration-300 -translate-x-full lg:translate-x-0 flex flex-col" id="admin-sidebar">
    
    <!-- Brand / Role Indicator -->
    <div class="px-6 py-6 border-b border-border-subtle bg-background/80 backdrop-blur-md relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-accent/5 to-transparent opacity-50"></div>
        <div class="flex items-center gap-3 mb-1 relative z-10">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary to-accent flex items-center justify-center shadow-lg border border-white/20">
                <span class="material-symbols-outlined text-white text-[20px]" aria-hidden="true">account_balance</span>
            </div>
            <div class="font-display font-bold text-2xl tracking-tight text-text-primary">Finzo</div>
        </div>
        <div class="text-[10px] font-bold text-accent uppercase tracking-[0.2em] pl-[48px] relative z-10">Enterprise Admin</div>
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
                <a href="admin-pending-approvals.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg font-bold transition-all <?= isActive('approvals', $currentPath) ?>">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">rule</span>
                    <span class="text-sm">Approvals</span>
                </a>
            </li>
            <li>
                <a href="admin-users.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg font-bold transition-all <?= isActive('users', $currentPath) ?>">
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
                <a href="admin-branches.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg font-bold transition-all <?= isActive('branches', $currentPath) ?>">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">store</span>
                    <span class="text-sm">Branch Operations</span>
                </a>
            </li>
            <li>
                <a href="admin-transactions.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg font-bold transition-all <?= isActive('transactions', $currentPath) ?>">
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
            <li>
                <a href="admin-support.php" class="flex items-center justify-between px-4 py-2.5 rounded-lg font-bold transition-all <?= isActive('support', $currentPath) ?>">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">support_agent</span>
                        <span class="text-sm">Helpdesk &amp; Support</span>
                    </div>
                    <span class="text-[9px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded bg-primary/10 text-primary border border-primary/20">Soon</span>
                </a>
            </li>
        </ul>

        <div class="px-6 mb-2 mt-6">
            <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest">Core Banking</div>
        </div>
        
        <ul class="space-y-1 px-3">
            <li>
                <a href="admin-loan-products.php" class="flex items-center justify-between px-4 py-2.5 rounded-lg font-bold transition-all <?= isActive('loan-products', $currentPath) ?>">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">real_estate_agent</span>
                        <span class="text-sm">Loan Products</span>
                    </div>
                    <span class="text-[9px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded bg-primary/10 text-primary border border-primary/20">Soon</span>
                </a>
            </li>
            <li>
                <a href="admin-loans.php" class="flex items-center justify-between px-4 py-2.5 rounded-lg font-bold transition-all <?= isActive('loans', $currentPath) ?>">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">payments</span>
                        <span class="text-sm">Loan Applications</span>
                    </div>
                    <span class="text-[9px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded bg-warning/10 text-warning border border-warning/20">Soon</span>
                </a>
            </li>
            <li>
                <a href="admin-card-products.php" class="flex items-center justify-between px-4 py-2.5 rounded-lg font-bold transition-all <?= isActive('card-products', $currentPath) ?>">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">featured_play_list</span>
                        <span class="text-sm">Card Products</span>
                    </div>
                    <span class="text-[9px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded bg-accent/10 text-accent border border-accent/20">Soon</span>
                </a>
            </li>
            <li>
                <a href="admin-cards.php" class="flex items-center justify-between px-4 py-2.5 rounded-lg font-bold transition-all <?= isActive('cards', $currentPath) ?>">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">credit_card</span>
                        <span class="text-sm">Card Issuance</span>
                    </div>
                    <span class="text-[9px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded bg-accent/10 text-accent border border-accent/20">Soon</span>
                </a>
            </li>
        </ul>

        <div class="px-6 mb-2 mt-6">
            <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest">Finance & Accounting</div>
        </div>
        
        <ul class="space-y-1 px-3">
            <li>
                <a href="admin-ledger.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg font-bold transition-all <?= isActive('ledger', $currentPath) ?>">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">account_balance_wallet</span>
                    <span class="text-sm">General Ledger</span>
                </a>
            </li>
        </ul>

        <div class="px-6 mb-2 mt-6">
            <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest">Reporting & Analytics</div>
        </div>
        
        <ul class="space-y-1 px-3">
            <li>
                <a href="admin-reports.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg font-bold transition-all <?= isActive('reports', $currentPath) ?>">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">query_stats</span>
                    <span class="text-sm">Advanced Reports</span>
                </a>
            </li>
        </ul>

        <div class="px-6 mb-2 mt-6">
            <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest">System & Security</div>
        </div>
        
        <ul class="space-y-1 px-3">
            <li>
                <a href="admin-suspended-users.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg font-bold transition-all <?= isActive('suspended-users', $currentPath) ?>">
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
                <a href="admin-deleted-users.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg font-bold transition-all <?= isActive('deleted-users', $currentPath) ?>">
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
            <li>
                <a href="admin-roles.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg font-bold transition-all <?= isActive('roles', $currentPath) ?>">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">policy</span>
                    <span class="text-sm">Roles & Permissions</span>
                </a>
            </li>
            <li>
                <a href="admin-settings.php" class="flex items-center justify-between px-4 py-2.5 rounded-lg font-bold transition-all <?= isActive('settings', $currentPath) ?>">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">settings</span>
                        <span class="text-sm">System Settings</span>
                    </div>
                    <span class="text-[9px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded bg-primary/10 text-primary border border-primary/20">Soon</span>
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
                <a href="../auth/admin-login.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-error hover:bg-error-bg font-bold transition-all mt-2">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">logout</span>
                    <span class="text-sm">Secure Logout</span>
                </a>
            </li>
        </ul>
    </div>
    <script>
        (function() {
            try {
                var sb = document.getElementById('admin-sidebar');
                if (!sb) return;
                var nav = sb.querySelector('nav');
                if (!nav) return;
                var saved = sessionStorage.getItem('finzo_sidebar_pos_admin');
                if (saved !== null) {
                    nav.scrollTop = parseInt(saved, 10);
                }
                var active = nav.querySelector('.active-nav-item');
                if (active) {
                    var nRect = nav.getBoundingClientRect();
                    var aRect = active.getBoundingClientRect();
                    if (aRect.top < nRect.top || aRect.bottom > nRect.bottom || saved === null) {
                        active.scrollIntoView({ block: 'center', inline: 'nearest', behavior: 'instant' });
                    }
                }
            } catch (e) {}
        })();
    </script>
</aside>
