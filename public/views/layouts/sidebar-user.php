<?php
$currentPath = basename($_SERVER['PHP_SELF']);
function isActiveUser($path, $current) {
    return (strpos($current, $path) !== false) ? 'bg-primary-surface/10 text-primary-base border-l-4 border-primary-base active-nav-item' : 'text-text-muted hover:bg-surface-subtle dark:hover:bg-white/5 hover:text-text-main dark:hover:text-white border-l-4 border-transparent';
}
?>
<!-- Customer Sidebar -->
<aside class="fixed top-16 left-0 h-[calc(100vh-4rem)] w-64 bg-white dark:bg-surface-dark border-r border-border-subtle z-40 transition-transform duration-300 -translate-x-full lg:translate-x-0 flex flex-col" id="sidebar">
    
    <!-- User Profile Summary -->
    <div class="px-6 py-5 border-b border-border-subtle bg-surface-subtle/30 dark:bg-black/10">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-full bg-primary-base flex items-center justify-center font-bold text-white shadow-sm user-initials">
                --
            </div>
            <div>
                <div class="text-sm font-bold text-text-main dark:text-white user-name truncate max-w-[140px]">Loading...</div>
                <div class="text-xs text-text-muted user-id">---</div>
            </div>
        </div>
        <div class="badge badge-danger">
            <div class="inline-flex items-center gap-1.5 px-2 py-1 rounded bg-success-surface/10 border border-success-base/20 hidden" id="status-active">
                <div class="w-1.5 h-1.5 rounded-full bg-success-base"></div>
                <span class="text-[10px] font-black text-success-base uppercase tracking-widest">Active</span>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto py-4 custom-scrollbar" aria-label="Sidebar Navigation">
        <ul class="space-y-1">
            <li>
                <a href="dashboard.php" class="flex items-center gap-3 px-6 py-2.5 font-bold transition-all <?= isActiveUser('dashboard', $currentPath) ?>">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">dashboard</span>
                    <span class="text-sm">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="accounts.php" class="flex items-center gap-3 px-6 py-2.5 font-bold transition-all <?= isActiveUser('account', $currentPath) ?>">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">account_balance_wallet</span>
                    <span class="text-sm">Accounts</span>
                </a>
            </li>
            <li>
                <a href="transfer.php" class="flex items-center gap-3 px-6 py-2.5 font-bold transition-all <?= isActiveUser('transfer', $currentPath) ?>">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">swap_horiz</span>
                    <span class="text-sm">Transfers</span>
                </a>
            </li>
            <li>
                <a href="beneficiary.php" class="flex items-center gap-3 px-6 py-2.5 font-bold transition-all <?= isActiveUser('beneficiary', $currentPath) ?>">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">group</span>
                    <span class="text-sm">Beneficiaries</span>
                </a>
            </li>
            <li>
                <a href="transactions.php" class="flex items-center gap-3 px-6 py-2.5 font-bold transition-all <?= isActiveUser('transaction', $currentPath) ?>">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">receipt_long</span>
                    <span class="text-sm">Transactions</span>
                </a>
            </li>
            <li>
                <a href="statement.php" class="flex items-center gap-3 px-6 py-2.5 font-bold transition-all <?= isActiveUser('statement', $currentPath) ?>">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">description</span>
                    <span class="text-sm">Statements</span>
                </a>
            </li>
        </ul>

        <div class="px-6 mb-2 mt-6">
            <div class="text-[10px] font-black text-text-muted uppercase tracking-widest">Account</div>
        </div>
        
        <ul class="space-y-1">
            <li>
                <a href="profile.php" class="flex items-center gap-3 px-6 py-2.5 font-bold transition-all <?= isActiveUser('profile', $currentPath) ?>">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">person</span>
                    <span class="text-sm">Profile Settings</span>
                </a>
            </li>
            <li>
                <a href="notifications.php" class="flex items-center gap-3 px-6 py-2.5 font-bold transition-all <?= isActiveUser('notification', $currentPath) ?>">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">notifications</span>
                    <span class="text-sm">Notifications</span>
                </a>
            </li>
            <li>
                <a href="change-password.php" class="flex items-center gap-3 px-6 py-2.5 font-bold transition-all <?= isActiveUser('password', $currentPath) ?>">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">lock</span>
                    <span class="text-sm">Security</span>
                </a>
            </li>
        </ul>
    </nav>
    
    <!-- Footer Logout -->
    <div class="p-4 border-t border-border-subtle bg-surface-subtle/30 dark:bg-black/10">
        <button id="user-logout-btn" class="w-full flex items-center gap-3 px-4 py-2 rounded-lg text-danger-base hover:bg-danger-surface font-bold transition-all text-left">
            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">logout</span>
            <span class="text-sm">Secure Logout</span>
        </button>
    </div>
    <script>
        (function() {
            try {
                var sb = document.getElementById('sidebar');
                if (!sb) return;
                var nav = sb.querySelector('nav');
                if (!nav) return;
                var saved = sessionStorage.getItem('finzo_sidebar_pos_user');
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
