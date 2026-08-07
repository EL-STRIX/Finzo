<?php
$currentPath = basename($_SERVER['PHP_SELF']);
function isActiveStaff($path, $current) {
    return (strpos($current, $path) !== false) ? 'bg-warning-surface/10 text-warning-base border-r-4 border-warning-base' : 'text-text-muted hover:bg-surface-subtle dark:hover:bg-white/5 hover:text-text-main dark:hover:text-white border-r-4 border-transparent';
}
?>
<!-- Staff Sidebar -->
<aside class="fixed top-16 left-0 h-[calc(100vh-4rem)] w-72 bg-white dark:bg-surface-dark border-r border-border-subtle z-40 transition-transform duration-300 -translate-x-full lg:translate-x-0 flex flex-col" id="sidebar">
    
    <!-- Staff Identity -->
    <div class="px-6 py-5 border-b border-border-subtle bg-surface-subtle/30 dark:bg-black/10">
        <div class="flex items-center gap-4 mb-3">
            <div class="w-10 h-10 rounded-lg bg-warning-surface flex items-center justify-center font-bold text-white shadow-sm staff-initials-display">
                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">badge</span>
            </div>
            <div>
                <div class="text-sm font-bold text-text-main dark:text-white staff-name-display truncate max-w-[150px]">Loading...</div>
                <div class="text-xs text-text-muted staff-id-display">STF---</div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <div class="inline-flex items-center gap-1.5 px-2 py-1 rounded bg-warning-surface/10 border border-warning-base/20">
                <div class="w-1.5 h-1.5 rounded-full bg-warning-base animate-pulse"></div>
                <span class="text-[10px] font-black text-warning-base uppercase tracking-widest">Active Shift</span>
            </div>
            <div class="text-[10px] font-bold text-text-muted uppercase tracking-widest badge badge-neutral">Loading...</div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto py-4 custom-scrollbar" aria-label="Sidebar Navigation">
        <div class="px-6 mb-2">
            <div class="text-[10px] font-black text-text-muted uppercase tracking-widest">Core Operations</div>
        </div>
        
        <ul class="space-y-1 px-3">
            <li>
                <a href="staff-dashboard.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg font-bold transition-all <?= isActiveStaff('dashboard', $currentPath) ?>">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">analytics</span>
                    <span class="text-sm">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="staff-transaction.php?type=deposit" class="flex items-center gap-3 px-4 py-2.5 rounded-lg font-bold transition-all <?= isActiveStaff('transaction', $currentPath) ?>">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">add_circle</span>
                    <span class="text-sm">New Deposit</span>
                </a>
            </li>
            <li>
                <a href="staff-transaction.php?type=withdraw" class="flex items-center gap-3 px-4 py-2.5 rounded-lg font-bold transition-all text-text-muted hover:bg-surface-subtle dark:hover:bg-white/5 hover:text-text-main dark:hover:text-white border-r-4 border-transparent">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">remove_circle</span>
                    <span class="text-sm">New Withdrawal</span>
                </a>
            </li>
            <li>
                <a href="staff-history.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg font-bold transition-all <?= isActiveStaff('history', $currentPath) ?>">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">receipt_long</span>
                    <span class="text-sm">History</span>
                </a>
            </li>
        </ul>
    </nav>
    
    <!-- Footer Profile Actions -->
    <div class="p-4 border-t border-border-subtle bg-surface-subtle/30 dark:bg-black/10">
        <ul class="space-y-1">
            <li>
                <a href="staff-profile.php" class="flex items-center gap-3 px-3 py-2 rounded-lg font-bold transition-all <?= isActiveStaff('profile', $currentPath) ?>">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">person</span>
                    <span class="text-sm">My Profile</span>
                </a>
            </li>
            <li>
                <button id="toggle-theme-btn" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-text-muted hover:bg-surface-subtle dark:hover:bg-white/5 hover:text-text-main dark:hover:text-white font-bold transition-all text-left">
                    <span class="material-symbols-outlined text-[20px]" id="theme-icon" aria-hidden="true">dark_mode</span>
                    <span class="text-sm" id="theme-label">Toggle Theme</span>
                </button>
            </li>
            <li>
                <button class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-danger-base hover:bg-danger-surface font-bold transition-all text-left mt-2 end-shift-trigger">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">logout</span>
                    <span class="text-sm">End Shift</span>
                </button>
            </li>
        </ul>
    </div>
</aside>
