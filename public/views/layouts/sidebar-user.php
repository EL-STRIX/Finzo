<?php
$currentPath = basename($_SERVER['PHP_SELF']);
$userStatus = $_SESSION['user_status'] ?? 'pending';
$isActive = $userStatus === 'active';

function isActiveUserNav($path, $current) {
    if ($path === 'profile' && strpos($current, 'complete-profile') !== false) {
        return 'text-text-secondary hover:bg-surface hover:text-text-primary border-l-4 border-transparent';
    }
    if ($path === 'account' && (strpos($current, 'account-pending') !== false || strpos($current, 'account-rejected') !== false)) {
        return 'text-text-secondary hover:bg-surface hover:text-text-primary border-l-4 border-transparent';
    }
    return (strpos($current, $path) !== false) 
        ? 'bg-primary/10 text-primary border-l-4 border-primary font-bold active-nav-item' 
        : 'text-text-secondary hover:bg-surface hover:text-text-primary border-l-4 border-transparent';
}
?>
<!-- Customer Sidebar -->
<aside class="fixed top-16 left-0 h-[calc(100vh-4rem)] w-64 bg-surface/90 dark:bg-surface-dark border-r border-border-subtle z-40 transition-transform duration-300 -translate-x-full lg:translate-x-0 flex flex-col backdrop-blur-md" id="sidebar">
    
    <!-- User Profile Summary Card -->
    <div class="px-5 py-4 border-b border-border-subtle bg-surface-subtle/30 dark:bg-black/10">
        <div class="flex items-center gap-3 mb-2">
            <div class="header-avatar w-10 h-10 rounded-full bg-primary/10 text-primary border border-primary/20 flex items-center justify-center font-bold text-xs shadow-sm shrink-0 overflow-hidden user-initials">
                <?php if (!empty($_SESSION['user_avatar'])): ?>
                    <img src="<?= htmlspecialchars($_SESSION['user_avatar']) ?>" alt="Avatar" class="w-full h-full object-cover" onerror="this.outerHTML='<span><?= htmlspecialchars(strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 2)), ENT_QUOTES, 'UTF-8') ?></span>'">
                <?php else: ?>
                    <?= htmlspecialchars(strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 2)), ENT_QUOTES, 'UTF-8') ?>
                <?php endif; ?>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-sm font-bold text-text-primary dark:text-white truncate user-name"><?= htmlspecialchars($_SESSION['user_name'] ?? 'User', ENT_QUOTES, 'UTF-8') ?></div>
                <div class="text-xs text-text-secondary font-mono user-id truncate"><?= !empty($_SESSION['user_customer_id']) ? 'ID: ' . htmlspecialchars($_SESSION['user_customer_id'], ENT_QUOTES, 'UTF-8') : '' ?></div>
            </div>
        </div>

        <div id="sidebar-status-badge">
            <?php if ($userStatus === 'active'): ?>
                <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded bg-emerald-500/10 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 text-[10px] font-black uppercase tracking-wider">
                    <div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div>
                    Account Active
                </div>
            <?php elseif ($userStatus === 'under_review'): ?>
                <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded bg-indigo-500/10 border border-indigo-500/30 text-indigo-600 dark:text-indigo-400 text-[10px] font-black uppercase tracking-wider">
                    <div class="w-1.5 h-1.5 rounded-full bg-indigo-500"></div>
                    Under Review
                </div>
            <?php elseif ($userStatus === 'rejected'): ?>
                <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded bg-rose-500/10 border border-rose-500/30 text-rose-600 dark:text-rose-400 text-[10px] font-black uppercase tracking-wider">
                    <div class="w-1.5 h-1.5 rounded-full bg-rose-500"></div>
                    Action Required
                </div>
            <?php else: ?>
                <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded bg-amber-500/10 border border-amber-500/30 text-amber-600 dark:text-amber-400 text-[10px] font-black uppercase tracking-wider">
                    <div class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></div>
                    Pending KYC
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Navigation List -->
    <nav class="flex-1 overflow-y-auto py-3 custom-scrollbar space-y-4" aria-label="Sidebar Navigation">
        
        <!-- Section 1: Overview & Accounts -->
        <div>
            <div class="px-5 mb-1.5 text-[10px] font-black text-text-secondary uppercase tracking-widest">Main Banking</div>
                <?php if (!$isActive): ?>
                    <?php 
                        $kycPath = $userStatus === 'under_review' ? 'account-pending' : ($userStatus === 'rejected' ? 'account-rejected' : 'complete-profile');
                    ?>
                    <li>
                        <a href="<?= $kycPath . '.php' ?>" class="flex items-center gap-3 px-5 py-2 text-xs font-semibold transition-all <?= isActiveUserNav($kycPath, $currentPath) ?>">
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">assignment_ind</span>
                            <span><?= $userStatus === 'under_review' ? 'KYC Status' : ($userStatus === 'rejected' ? 'Resubmit KYC' : 'Complete KYC') ?></span>
                        </a>
                    </li>
                <?php endif; ?>

                <li>
                    <a href="<?= $isActive ? 'dashboard.php' : '#' ?>" class="flex items-center justify-between px-5 py-2 text-xs font-semibold transition-all <?= isActiveUserNav('dashboard', $currentPath) ?> <?= !$isActive ? 'opacity-50 cursor-not-allowed' : '' ?>" <?= !$isActive ? 'title="Available after account activation"' : '' ?>>
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">dashboard</span>
                            <span>Dashboard</span>
                        </div>
                        <?php if (!$isActive): ?><span class="material-symbols-outlined text-[14px] text-text-secondary" aria-hidden="true">lock</span><?php endif; ?>
                    </a>
                </li>

                <li>
                    <a href="<?= $isActive ? 'accounts.php' : '#' ?>" class="flex items-center justify-between px-5 py-2 text-xs font-semibold transition-all <?= isActiveUserNav('account', $currentPath) ?> <?= !$isActive ? 'opacity-50 cursor-not-allowed' : '' ?>" <?= !$isActive ? 'title="Available after account activation"' : '' ?>>
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">account_balance</span>
                            <span>My Accounts</span>
                        </div>
                        <?php if (!$isActive): ?><span class="material-symbols-outlined text-[14px] text-text-secondary" aria-hidden="true">lock</span><?php endif; ?>
                    </a>
                </li>

                <li>
                    <a href="<?= $isActive ? 'transfer.php' : '#' ?>" class="flex items-center justify-between px-5 py-2 text-xs font-semibold transition-all <?= isActiveUserNav('transfer', $currentPath) ?> <?= !$isActive ? 'opacity-50 cursor-not-allowed' : '' ?>" <?= !$isActive ? 'title="Available after account activation"' : '' ?>>
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">swap_horiz</span>
                            <span>Fund Transfer</span>
                        </div>
                        <?php if (!$isActive): ?><span class="material-symbols-outlined text-[14px] text-text-secondary" aria-hidden="true">lock</span><?php endif; ?>
                    </a>
                </li>

                <li>
                    <a href="<?= $isActive ? 'transactions.php' : '#' ?>" class="flex items-center justify-between px-5 py-2 text-xs font-semibold transition-all <?= isActiveUserNav('transaction', $currentPath) ?> <?= !$isActive ? 'opacity-50 cursor-not-allowed' : '' ?>" <?= !$isActive ? 'title="Available after account activation"' : '' ?>>
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">receipt_long</span>
                            <span>Transactions</span>
                        </div>
                        <?php if (!$isActive): ?><span class="material-symbols-outlined text-[14px] text-text-secondary" aria-hidden="true">lock</span><?php endif; ?>
                    </a>
                </li>

                <li>
                    <a href="<?= $isActive ? 'statement.php' : '#' ?>" class="flex items-center justify-between px-5 py-2 text-xs font-semibold transition-all <?= isActiveUserNav('statement', $currentPath) ?> <?= !$isActive ? 'opacity-50 cursor-not-allowed' : '' ?>" <?= !$isActive ? 'title="Available after account activation"' : '' ?>>
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">description</span>
                            <span>Statements</span>
                        </div>
                        <?php if (!$isActive): ?><span class="material-symbols-outlined text-[14px] text-text-secondary" aria-hidden="true">lock</span><?php endif; ?>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Section 2: Financial Products & Services -->
        <div>
            <div class="px-5 mb-1.5 text-[10px] font-black text-text-secondary uppercase tracking-widest">Financial Products</div>
            <ul class="space-y-0.5">
                <li>
                    <a href="<?= $isActive ? 'user-loans.php' : '#' ?>" class="flex items-center justify-between px-5 py-2 text-xs font-semibold transition-all <?= isActiveUserNav('loans', $currentPath) ?> <?= !$isActive ? 'opacity-50 cursor-not-allowed' : '' ?>" <?= !$isActive ? 'title="Available after account activation"' : '' ?>>
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">account_balance_wallet</span>
                            <span>Loans &amp; Credit</span>
                        </div>
                        <?php if ($isActive): ?>
                            <span class="text-[9px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded bg-primary/10 text-primary border border-primary/20">Soon</span>
                        <?php else: ?>
                            <span class="material-symbols-outlined text-[14px] text-text-secondary" aria-hidden="true">lock</span>
                        <?php endif; ?>
                    </a>
                </li>

                <li>
                    <a href="<?= $isActive ? 'user-cards.php' : '#' ?>" class="flex items-center justify-between px-5 py-2 text-xs font-semibold transition-all <?= isActiveUserNav('cards', $currentPath) ?> <?= !$isActive ? 'opacity-50 cursor-not-allowed' : '' ?>" <?= !$isActive ? 'title="Available after account activation"' : '' ?>>
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">credit_card</span>
                            <span>Cards &amp; Controls</span>
                        </div>
                        <?php if ($isActive): ?>
                            <span class="text-[9px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded bg-accent/10 text-accent border border-accent/20">Soon</span>
                        <?php else: ?>
                            <span class="material-symbols-outlined text-[14px] text-text-secondary" aria-hidden="true">lock</span>
                        <?php endif; ?>
                    </a>
                </li>

                <li>
                    <a href="<?= $isActive ? 'user-investments.php' : '#' ?>" class="flex items-center justify-between px-5 py-2 text-xs font-semibold transition-all <?= isActiveUserNav('investments', $currentPath) ?> <?= !$isActive ? 'opacity-50 cursor-not-allowed' : '' ?>" <?= !$isActive ? 'title="Available after account activation"' : '' ?>>
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">savings</span>
                            <span>Investments &amp; FDs</span>
                        </div>
                        <?php if (!$isActive): ?><span class="material-symbols-outlined text-[14px] text-text-secondary" aria-hidden="true">lock</span><?php endif; ?>
                    </a>
                </li>

                <li>
                    <a href="<?= $isActive ? 'user-bill-payments.php' : '#' ?>" class="flex items-center justify-between px-5 py-2 text-xs font-semibold transition-all <?= isActiveUserNav('bill-payments', $currentPath) ?> <?= !$isActive ? 'opacity-50 cursor-not-allowed' : '' ?>" <?= !$isActive ? 'title="Available after account activation"' : '' ?>>
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">payments</span>
                            <span>Bill Payments</span>
                        </div>
                        <?php if (!$isActive): ?><span class="material-symbols-outlined text-[14px] text-text-secondary" aria-hidden="true">lock</span><?php endif; ?>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Section 3: Settings & Support -->
        <div>
            <div class="px-5 mb-1.5 text-[10px] font-black text-text-secondary uppercase tracking-widest">Account &amp; Help</div>
            <ul class="space-y-0.5">
                <li>
                    <a href="<?= $isActive ? 'beneficiary.php' : '#' ?>" class="flex items-center justify-between px-5 py-2 text-xs font-semibold transition-all <?= isActiveUserNav('beneficiary', $currentPath) ?> <?= !$isActive ? 'opacity-50 cursor-not-allowed' : '' ?>" <?= !$isActive ? 'title="Available after account activation"' : '' ?>>
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">group</span>
                            <span>Beneficiaries</span>
                        </div>
                        <?php if (!$isActive): ?><span class="material-symbols-outlined text-[14px] text-text-secondary" aria-hidden="true">lock</span><?php endif; ?>
                    </a>
                </li>

                <li>
                    <a href="user-support.php" class="flex items-center gap-3 px-5 py-2 text-xs font-semibold transition-all <?= isActiveUserNav('support', $currentPath) ?>">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">help</span>
                        <span>Help &amp; Support</span>
                    </a>
                </li>

                <li>
                    <a href="notifications.php" class="flex items-center gap-3 px-5 py-2 text-xs font-semibold transition-all <?= isActiveUserNav('notification', $currentPath) ?>">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">notifications</span>
                        <span>Notifications</span>
                    </a>
                </li>

                <li>
                    <a href="profile.php" class="flex items-center gap-3 px-5 py-2 text-xs font-semibold transition-all <?= isActiveUserNav('profile', $currentPath) ?>">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">person</span>
                        <span>My Profile</span>
                    </a>
                </li>
            </ul>
        </div>

    </nav>
    
    <!-- Sidebar Footer Controls -->
    <div class="p-4 border-t border-border-subtle bg-surface-subtle/30 dark:bg-black/10 space-y-2">


        <button id="user-logout-btn" class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-rose-500 hover:bg-rose-500/10 font-bold transition-all text-xs text-left cursor-pointer">
            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">logout</span>
            <span>Secure Logout</span>
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
