<?php
$currentPath = basename($_SERVER['PHP_SELF']);

if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    \App\Shared\Security\SessionManager::start();
}

$staffName = $_SESSION['staff_name'] ?? 'Staff Member';
$rawStaffId = $_SESSION['staff_display_id'] ?? (isset($_SESSION['staff_id']) ? 'STF' . str_pad((string)$_SESSION['staff_id'], 3, '0', STR_PAD_LEFT) : 'STF---');
$staffBranch = $_SESSION['staff_branch'] ?? $_SESSION['branch_name'] ?? 'Finzo Branch';
$staffPhoto = $_SESSION['staff_profile_photo'] ?? null;

$resolveStaffPhoto = function(?string $path): ?string {
    if (empty($path)) return null;
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'data:')) {
        return $path;
    }
    $clean = ltrim($path, '/');
    if (str_starts_with($clean, 'Finzo/public/')) return '/' . $clean;
    if (str_starts_with($clean, 'uploads/') || str_starts_with($clean, 'assets/')) return '/Finzo/public/' . $clean;
    return '/Finzo/public/uploads/' . $clean;
};
$resolvedPhotoUrl = $resolveStaffPhoto($staffPhoto);

$staffInitials = '';
$words = array_filter(explode(' ', trim($staffName)));
foreach ($words as $w) {
    if (!empty($w)) $staffInitials .= mb_substr($w, 0, 1);
}
$staffInitials = strtoupper(substr($staffInitials ?: 'ST', 0, 2));

function isActiveStaff($path, $current, $type = null) {
    if ($path === 'history' && (strpos($current, 'receipt') !== false || strpos($current, 'history') !== false)) {
        return 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-r-4 border-amber-500 font-bold active-nav-item';
    }
    if ($type !== null) {
        $currentType = $_GET['type'] ?? '';
        if (empty($currentType) && strpos($current, 'transaction') !== false) {
            $currentType = 'deposit';
        }
        $isMatch = ((strpos($current, $path) !== false) || (strpos($current, $type) !== false)) && ($currentType === $type || strpos($current, $type) !== false);
    } else {
        $isMatch = (strpos($current, $path) !== false);
    }
    return $isMatch 
        ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-r-4 border-amber-500 font-bold active-nav-item' 
        : 'text-text-muted hover:bg-surface-subtle dark:hover:bg-white/5 hover:text-text-main dark:hover:text-white border-r-4 border-transparent font-medium';
}
?>
<!-- Staff Sidebar -->
<aside class="fixed top-16 left-0 h-[calc(100vh-4rem)] w-72 bg-white dark:bg-surface-dark border-r border-border-subtle z-40 transition-transform duration-300 -translate-x-full lg:translate-x-0 flex flex-col" id="sidebar">
    
    <!-- Staff Identity (SSR Instant 0ms Render) -->
    <div class="px-6 py-5 border-b border-border-subtle bg-surface-subtle/30 dark:bg-black/10">
        <div class="flex items-center gap-4 mb-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500/15 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 border border-amber-500/20 flex items-center justify-center font-bold text-sm shadow-sm overflow-hidden staff-initials-display">
                <?php if ($resolvedPhotoUrl): ?>
                    <img src="<?= htmlspecialchars($resolvedPhotoUrl) ?>" class="w-full h-full object-cover" alt="DP" onerror="this.outerHTML='<span><?= htmlspecialchars($staffInitials) ?></span>'">
                <?php else: ?>
                    <span><?= htmlspecialchars($staffInitials) ?></span>
                <?php endif; ?>
            </div>
            <div class="min-w-0">
                <div class="text-sm font-bold text-text-main dark:text-white staff-name-display truncate max-w-[150px]" title="<?= htmlspecialchars($staffName) ?>"><?= htmlspecialchars($staffName) ?></div>
                <div class="text-xs text-text-muted font-mono staff-id-display"><?= htmlspecialchars($rawStaffId) ?></div>
            </div>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <div id="sidebar-shift-pill" class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-amber-500/10 dark:bg-amber-500/15 border border-amber-500/20">
                <div id="sidebar-shift-dot" class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></div>
                <span id="sidebar-shift-text" class="text-[10px] font-black text-amber-600 dark:text-amber-400 uppercase tracking-widest">Active Shift</span>
            </div>
            <div class="text-[10px] font-bold text-text-muted uppercase tracking-wider px-2 py-0.5 rounded bg-surface-subtle dark:bg-white/5 border border-border-subtle staff-branch-display"><?= htmlspecialchars($staffBranch) ?></div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto py-4 custom-scrollbar" aria-label="Sidebar Navigation">
        <div class="px-6 mb-2">
            <div class="text-[10px] font-black text-text-muted uppercase tracking-widest">Core Operations</div>
        </div>
        
        <ul class="space-y-1 px-3">
            <li>
                <a href="staff-dashboard.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-all <?= isActiveStaff('dashboard', $currentPath) ?>">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">analytics</span>
                    <span class="text-sm">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="staff-transaction.php?type=deposit" class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-all <?= isActiveStaff('transaction', $currentPath, 'deposit') ?>">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">add_circle</span>
                    <span class="text-sm">New Deposit</span>
                </a>
            </li>
            <li>
                <a href="staff-transaction.php?type=withdraw" class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-all <?= isActiveStaff('transaction', $currentPath, 'withdraw') ?>">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">remove_circle</span>
                    <span class="text-sm">New Withdrawal</span>
                </a>
            </li>
            <li>
                <a href="staff-history.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-all <?= isActiveStaff('history', $currentPath) ?>">
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
                <a href="staff-profile.php" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-all <?= isActiveStaff('profile', $currentPath) ?>">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">person</span>
                    <span class="text-sm">My Profile</span>
                </a>
            </li>
            <li>
                <button type="button" id="toggle-theme-btn" data-toggle-theme class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-text-muted hover:bg-surface-subtle dark:hover:bg-white/5 hover:text-text-main dark:hover:text-white font-bold transition-all text-left cursor-pointer group" title="Toggle Dark/Light Mode" aria-label="Toggle Theme">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[20px] transition-transform group-hover:rotate-12" id="theme-icon" data-theme-icon aria-hidden="true">dark_mode</span>
                        <span class="text-sm" id="theme-label" data-theme-label>Toggle Theme</span>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded bg-surface-subtle dark:bg-white/10 text-text-muted border border-border-subtle" id="theme-badge">Theme</span>
                </button>
            </li>
        </ul>
        <div class="pt-2 mt-2 border-t border-border-subtle flex flex-col gap-1">
            <button type="button" id="sidebar-end-shift-btn" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-amber-600 dark:text-amber-400 hover:bg-amber-500/10 font-bold transition-all text-left end-shift-trigger cursor-pointer" title="End Current Shift">
                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">timer_off</span>
                <span class="text-sm">End Shift</span>
            </button>
            <button type="button" id="sidebar-logout-btn" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-danger-base hover:bg-danger-surface/20 font-bold transition-all text-left cursor-pointer" title="Sign out of Finzo Staff Portal">
                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">logout</span>
                <span class="text-sm">Sign Out</span>
            </button>
        </div>
    </div>

    <!-- Universal Sidebar End Shift / Clock Out Modal -->
    <div id="sidebar-clockout-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 transition-all">
        <div class="bg-white dark:bg-surface-elevated border border-border-subtle rounded-2xl w-full max-w-md shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
            <div class="p-5 border-b border-border-subtle flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                        <span class="material-symbols-outlined text-lg" aria-hidden="true">timer_off</span>
                    </div>
                    <h3 class="font-headline font-bold text-lg text-text-main dark:text-white">End Shift Confirmation</h3>
                </div>
                <button type="button" id="btn-close-sidebar-clockout" class="p-1.5 rounded-lg text-text-muted hover:text-text-main hover:bg-surface-subtle transition-colors" aria-label="Close modal">
                    <span class="material-symbols-outlined text-xl leading-none">close</span>
                </button>
            </div>
            <form id="sidebar-clockout-form" class="p-6 space-y-4">
                <p class="text-xs text-text-muted leading-relaxed">
                    You are about to end your active teller shift. Any pending counter activities should be completed before proceeding.
                </p>
                <div>
                    <label for="sidebar-shift-notes" class="block text-xs font-black uppercase tracking-wider text-text-muted mb-1.5">Shift Handover Remarks (Optional)</label>
                    <textarea id="sidebar-shift-notes" name="notes" rows="3" class="w-full px-3.5 py-2.5 rounded-xl border border-border-subtle bg-surface-subtle/50 dark:bg-black/20 text-text-main dark:text-white text-xs placeholder:text-text-muted/60 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all resize-none" placeholder="e.g., Cash drawer verified, physical count completed."></textarea>
                </div>
                <div id="sidebar-clockout-error" class="hidden text-xs font-bold text-danger-base bg-danger-surface/10 p-3 rounded-xl border border-danger-base/20" aria-live="polite"></div>
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-border-subtle">
                    <button type="button" id="btn-cancel-sidebar-clockout" class="px-4 py-2.5 rounded-xl border border-border-subtle text-text-muted hover:text-text-main text-xs font-bold uppercase tracking-wider transition-colors cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" id="btn-submit-sidebar-clockout" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-black uppercase tracking-wider shadow-md shadow-rose-600/20 transition-all flex items-center gap-1.5 cursor-pointer">
                        <span class="material-symbols-outlined text-base" aria-hidden="true">check_circle</span>
                        <span>Confirm Clock Out</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function() {
            try {
                var sb = document.getElementById('sidebar');
                if (!sb) return;
                var nav = sb.querySelector('nav');
                if (!nav) return;
                var saved = sessionStorage.getItem('finzo_sidebar_pos_staff');
                if (saved !== null) {
                    nav.scrollTop = parseInt(saved, 10);
                }
                nav.addEventListener('scroll', function() {
                    sessionStorage.setItem('finzo_sidebar_pos_staff', nav.scrollTop);
                }, { passive: true });
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
