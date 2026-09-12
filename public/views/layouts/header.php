<?php
$portal = $portal ?? 'guest';
$pageTitle = $pageTitle ?? 'Dashboard';

// Ensure session is active
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    \App\Shared\Security\SessionManager::start();
}

// ── Resolve Profile Details Server-Side (Instant 0ms SSR) ──
$headerProfileName = 'Finzo User';
$headerProfileId   = '';
$headerPhotoUrl    = null;
$headerProfileLink = '#';

$resolveHeaderPhoto = function(?string $path): ?string {
    if (empty($path)) return null;
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'data:')) {
        return $path;
    }
    $clean = ltrim($path, '/');
    if (str_starts_with($clean, 'Finzo/public/')) {
        return '/' . $clean;
    }
    if (str_starts_with($clean, 'uploads/') || str_starts_with($clean, 'assets/')) {
        return '/Finzo/public/' . $clean;
    }
    return '/Finzo/public/uploads/' . $clean;
};

if ($portal === 'admin' || (!empty($_SESSION['admin_id']) && $portal !== 'user' && $portal !== 'staff')) {
    $headerProfileName = $_SESSION['admin_name'] ?? 'Administrator';
    $rawAdminId        = $_SESSION['admin_display_id'] ?? (isset($_SESSION['admin_id']) ? 'ADM' . str_pad((string)$_SESSION['admin_id'], 3, '0', STR_PAD_LEFT) : 'ADM---');
    $headerProfileId   = 'ID: ' . $rawAdminId;
    $headerPhotoUrl    = $resolveHeaderPhoto($_SESSION['admin_profile_photo'] ?? null);
    $headerProfileLink = '/Finzo/public/views/admin/admin-profile.php';
} elseif ($portal === 'staff' || (!empty($_SESSION['staff_id']) && $portal !== 'user')) {
    $headerProfileName = $_SESSION['staff_name'] ?? 'Staff Member';
    $rawStaffId        = $_SESSION['staff_display_id'] ?? (isset($_SESSION['staff_id']) ? 'STF' . str_pad((string)$_SESSION['staff_id'], 3, '0', STR_PAD_LEFT) : 'Staff');
    $headerProfileId   = 'ID: ' . $rawStaffId;
    $headerPhotoUrl    = $resolveHeaderPhoto($_SESSION['staff_profile_photo'] ?? null);
    $headerProfileLink = '/Finzo/public/views/staff/staff-profile.php';
} elseif ($portal === 'user' || !empty($_SESSION['user_id'])) {
    $headerProfileName = $_SESSION['user_name'] ?? 'Customer';
    $rawCustId         = $_SESSION['user_customer_id'] ?? (isset($_SESSION['user_id']) ? 'CUST-' . $_SESSION['user_id'] : 'Customer');
    $headerProfileId   = 'ID: ' . $rawCustId;
    $headerPhotoUrl    = $resolveHeaderPhoto($_SESSION['user_avatar'] ?? null);
    $headerProfileLink = '/Finzo/public/views/user/profile.php';
}

$headerWords    = array_filter(explode(' ', trim($headerProfileName)));
$headerInitials = '';
foreach ($headerWords as $w) {
    if (!empty($w)) $headerInitials .= mb_substr($w, 0, 1);
}
$headerInitials = strtoupper(substr($headerInitials ?: 'FZ', 0, 2));
?>
<!-- Global Header -->
<header class="admin-topbar h-16 bg-surface/80 backdrop-blur-md border-b border-border-subtle fixed top-0 left-0 right-0 z-50 px-4 sm:px-6 lg:px-8 flex items-center justify-between transition-colors duration-300 shadow-sm">
    <div class="flex items-center gap-4 lg:gap-6">
        <!-- Mobile Menu Toggle -->
        <button id="mobile-menu-btn" class="lg:hidden p-2 -ml-2 rounded-xl text-text-secondary hover:bg-background transition-all focus:outline-none focus:ring-2 focus:ring-primary" aria-label="Toggle Navigation Menu">
            <span class="material-symbols-outlined" aria-hidden="true">menu</span>
        </button>
        
        <!-- Brand Area (Visible on mobile if sidebar is hidden, or on desktop if no sidebar) -->
        <div class="flex items-center gap-3 <?= $portal === 'guest' ? '' : 'lg:hidden' ?>">
            <div class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center">
                <span class="material-symbols-outlined text-white text-sm" aria-hidden="true">account_balance</span>
            </div>
            <div class="font-headline font-black text-xl tracking-tight text-text-primary">Finzo</div>
        </div>

        <!-- Breadcrumbs / Page Title (Desktop) -->
        <div class="hidden lg:flex flex-col justify-center">
            <nav aria-label="Breadcrumb" class="flex items-center space-x-2 text-xs font-bold text-text-secondary uppercase tracking-widest mb-0.5">
                <a href="#" class="hover:text-primary transition-colors"><?= ucfirst($portal) ?> Portal</a>
                <span class="material-symbols-outlined text-[14px]" aria-hidden="true">chevron_right</span>
                <span aria-current="page" class="text-text-primary"><?= htmlspecialchars($pageTitle) ?></span>
            </nav>
        </div>
    </div>

    <!-- Right Side Actions -->
    <div class="flex items-center gap-2 sm:gap-4">
        
        <!-- Global Theme Toggle Button -->
        <button type="button" data-toggle-theme class="p-2 rounded-xl text-text-secondary hover:text-text-primary hover:bg-surface transition-all border border-border-subtle shadow-sm flex items-center justify-center cursor-pointer" title="Toggle Dark/Light Mode" aria-label="Toggle Theme">
            <span class="material-symbols-outlined text-[20px]" data-theme-icon aria-hidden="true">dark_mode</span>
        </button>

        <div class="h-6 w-px bg-border-subtle hidden sm:block"></div>

        <!-- User Profile Menu -->
        <a href="<?= htmlspecialchars($headerProfileLink) ?>" class="relative ml-2 flex items-center gap-3 cursor-pointer group text-inherit no-underline" title="View Profile">
            <div class="text-right hidden sm:block">
                <div class="text-sm font-bold text-text-primary leading-tight" id="header-profile-name"><?= htmlspecialchars($headerProfileName) ?></div>
                <div class="text-xs text-text-secondary font-mono" id="header-profile-id"><?= htmlspecialchars($headerProfileId) ?></div>
            </div>
            <div class="header-avatar w-10 h-10 rounded-full bg-primary/10 text-primary border border-primary/20 flex items-center justify-center font-black text-xs shadow-sm transition-transform group-hover:scale-105 overflow-hidden">
                <?php if ($headerPhotoUrl): ?>
                    <img src="<?= htmlspecialchars($headerPhotoUrl) ?>?v=<?= time() ?>" alt="<?= htmlspecialchars($headerProfileName) ?>" class="w-full h-full object-cover" onerror="this.outerHTML='<span><?= htmlspecialchars($headerInitials) ?></span>'">
                <?php else: ?>
                    <span><?= htmlspecialchars($headerInitials) ?></span>
                <?php endif; ?>
            </div>
        </a>
    </div>
</header>
