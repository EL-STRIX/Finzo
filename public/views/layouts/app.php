<?php
$portal = $portal ?? 'guest'; // admin, staff, user, guest
if (!defined('FINZO_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}
\App\Shared\Security\SessionManager::start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0, viewport-fit=cover" name="viewport" />
    <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>" />
    <script>window.csrfToken = "<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>";</script>
    <title><?= $pageTitle ?? 'Finzo Banking' ?></title>
    
    <!-- Anti-FOUC: Apply saved theme before first paint -->
    <script src="/Finzo/public/assets/js/core/theme-init.js"></script>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cormorant:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet" />

    <!-- CSS -->
    <link href="/Finzo/public/assets/css/main.css?v=<?= time() ?>" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="/Finzo/public/assets/js/core/tailwind-config.js?v=<?= time() ?>"></script>
    <!-- Core JS: order matters — utils must load before api and ui-components -->
    <script defer src="/Finzo/public/assets/js/core/utils.js"></script>
    <script defer src="/Finzo/public/assets/js/core/api.js"></script>
    <script defer src="/Finzo/public/assets/js/core/ui-components.js"></script>
    <?php if ($portal === 'user' || !empty($_SESSION['user_id'])): ?>
    <script>
        window.__FINZO_USER__ = <?= json_encode([
            'id'          => (int)($_SESSION['user_id'] ?? 0),
            'customer_id' => $_SESSION['user_customer_id'] ?? '',
            'name'        => $_SESSION['user_name'] ?? '',
            'email'       => $_SESSION['user_email'] ?? '',
            'phone'       => $_SESSION['user_phone'] ?? '',
            'status'      => $_SESSION['user_status'] ?? 'pending',
        ], JSON_UNESCAPED_UNICODE) ?>;
    </script>
    <?php endif; ?>
    <?php if ($portal === 'admin'): ?>
        <script defer src="/Finzo/public/assets/js/admin/admin-shared.js"></script>
    <?php elseif ($portal === 'user'): ?>
        <script defer src="/Finzo/public/assets/js/user/user-shared.js"></script>
    <?php endif; ?>
    <?php if (!empty($useCharts)): ?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <?php endif; ?>
    <?= $extraHead ?? '' ?>
</head>
<body class="<?= $bodyClass ?? 'bg-background font-body text-text-primary antialiased relative' ?>">
    <!-- Liquid Glass Background Element -->
    <div class="fixed inset-0 z-[-1] pointer-events-none bg-background">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-accent/10 via-background to-background"></div>
        <div class="absolute top-0 right-0 -mt-20 -mr-20 w-96 h-96 bg-primary/5 rounded-full blur-3xl opacity-50 dark:opacity-20 animate-pulse delay-1000"></div>
        <div class="absolute bottom-0 left-0 -mb-20 -ml-20 w-[500px] h-[500px] bg-accent/5 rounded-full blur-3xl opacity-50 dark:opacity-20 animate-pulse"></div>
    </div>
    
    <?php if ($portal !== 'guest' && $portal !== 'auth'): ?>
        <!-- Global Header -->
        <?php include __DIR__ . '/header.php'; ?>

        <div class="flex h-screen overflow-hidden pt-16">
            
            <!-- Mobile Sidebar Overlay -->
            <div class="fixed inset-0 bg-black/60 z-30 hidden lg:hidden transition-opacity opacity-0" id="sidebar-overlay"></div>

            <!-- Sidebar Navigation -->
            <?php 
                if ($portal === 'admin') include __DIR__ . '/sidebar-admin.php';
                if ($portal === 'staff') include __DIR__ . '/sidebar-staff.php';
                if ($portal === 'user') include __DIR__ . '/sidebar-user.php';
            ?>
            
            <!-- Main Content Area -->
            <div class="relative flex flex-col flex-1 overflow-y-auto overflow-x-hidden transition-all duration-300 <?= $portal === 'user' ? 'lg:ml-64' : 'lg:ml-72' ?>">
                
                <!-- Page Header -->
                <?php if ($portal !== 'admin' && !empty($pageHeader)): ?>
                    <header class="bg-white dark:bg-surface-dark border-b border-border-subtle sticky top-0 z-10">
                        <div class="px-6 py-5 lg:px-8">
                            <?= $pageHeader ?>
                        </div>
                    </header>
                <?php endif; ?>

                <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-[1600px] mx-auto w-full space-y-6">
                    <?php if ($portal === 'admin' && !empty($pageHeader)): ?>
                        <div class="mb-6 relative z-10">
                            <?= $pageHeader ?>
                        </div>
                    <?php endif; ?>
                    <?= $slot ?? '' ?>
                </main>
                
            </div>
        </div>
        
    <?php else: ?>
        <!-- Guest/Auth Layout -->
        <main class="flex-1 w-full min-h-screen">
            <?= $slot ?? '' ?>
        </main>
        
        <?php if ($portal === 'guest'): ?>
            <?php 
                $footerFile = __DIR__ . '/footer.php';
                if (file_exists($footerFile)) include $footerFile;
            ?>
        <?php endif; ?>
    <?php endif; ?>

    <?= $extraScripts ?? '' ?>
</body>
</html>

