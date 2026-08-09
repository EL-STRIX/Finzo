<?php
$portal = $portal ?? 'guest'; // admin, staff, user, guest
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0, viewport-fit=cover" name="viewport" />
    <meta name="csrf-token" content="" />
    <title><?= $pageTitle ?? 'Finzo Banking' ?></title>
    
    <!-- Anti-FOUC: Apply saved theme before first paint -->
    <script>
    (function(){
      var t = localStorage.getItem('finzo-theme') || 'light';
      document.documentElement.setAttribute('data-theme', t);
      if(t === 'dark') document.documentElement.classList.add('dark');
    })();
    </script>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet" />

    <!-- CSS -->
    <link href="/Finzo/public/assets/css/main.css" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              primary: { 50: 'var(--color-primary-50)', 100: 'var(--color-primary-100)', 200: 'var(--color-primary-200)', 300: 'var(--color-primary-300)', 400: 'var(--color-primary-400)', 500: 'var(--color-primary-500)', 600: 'var(--color-primary-600)', 700: 'var(--color-primary-700)', 800: 'var(--color-primary-800)', 900: 'var(--color-primary-900)', base: 'var(--color-primary-base)', hover: 'var(--color-primary-hover)', surface: 'var(--color-primary-surface)' },
              surface: { main: 'var(--color-surface-main)', subtle: 'var(--color-surface-subtle)', hover: 'var(--color-surface-hover)', dark: 'var(--color-surface-dark)', border: 'var(--color-surface-border)' },
              text: { main: 'var(--color-text-main)', muted: 'var(--color-text-muted)', disabled: 'var(--color-text-disabled)', inverse: 'var(--color-text-inverse)' },
              border: { main: 'var(--color-border-main)', subtle: 'var(--color-border-subtle)', focus: 'var(--color-border-focus)' },
              success: { base: 'var(--color-success-base)', hover: 'var(--color-success-hover)', surface: 'var(--color-success-surface)', text: 'var(--color-success-text)' },
              warning: { base: 'var(--color-warning-base)', hover: 'var(--color-warning-hover)', surface: 'var(--color-warning-surface)', text: 'var(--color-warning-text)' },
              danger: { base: 'var(--color-danger-base)', hover: 'var(--color-danger-hover)', surface: 'var(--color-danger-surface)', text: 'var(--color-danger-text)' },
              info: { base: 'var(--color-info-base)', hover: 'var(--color-info-hover)', surface: 'var(--color-info-surface)', text: 'var(--color-info-text)' }
            },
            fontFamily: { display: ['Plus Jakarta Sans', 'sans-serif'], headline: ['Plus Jakarta Sans', 'sans-serif'], body: ['Inter', 'sans-serif'], mono: ['JetBrains Mono', 'monospace'] },
            boxShadow: { 'xs': 'var(--shadow-xs)', 'sm': 'var(--shadow-sm)', 'md': 'var(--shadow-md)', 'lg': 'var(--shadow-lg)', 'dropdown': 'var(--shadow-dropdown)', 'focus': 'var(--shadow-focus)', 'focus-danger': 'var(--shadow-focus-danger)', 'focus-success': 'var(--shadow-focus-success)' },
            borderRadius: { 'sm': 'var(--radius-sm)', 'md': 'var(--radius-md)', 'lg': 'var(--radius-lg)', 'xl': 'var(--radius-xl)', '2xl': 'var(--radius-2xl)', 'full': 'var(--radius-full)' }
          }
        }
      }
    </script>
    <!-- Core JS: order matters — utils must load before api and ui-components -->
    <script defer src="/Finzo/public/assets/js/core/utils.js"></script>
    <script defer src="/Finzo/public/assets/js/core/api.js"></script>
    <script defer src="/Finzo/public/assets/js/core/ui-components.js"></script>
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
<body class="<?= $bodyClass ?? 'bg-surface-subtle font-body text-text-main antialiased' ?>">
    
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
                <?php if (!empty($pageHeader)): ?>
                    <header class="bg-white dark:bg-surface-dark border-b border-border-subtle sticky top-0 z-10">
                        <div class="px-6 py-5 lg:px-8">
                            <?= $pageHeader ?>
                        </div>
                    </header>
                <?php endif; ?>

                <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-[1600px] mx-auto w-full space-y-6">
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

