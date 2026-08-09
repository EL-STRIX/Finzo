<?php
$portal = $portal ?? 'guest';
$pageTitle = $pageTitle ?? 'Dashboard';
?>
<!-- Global Header -->
<header class="admin-topbar h-16 bg-white dark:bg-surface-dark border-b border-border-subtle fixed top-0 left-0 right-0 z-50 px-4 sm:px-6 lg:px-8 flex items-center justify-between transition-colors duration-300">
    <div class="flex items-center gap-4 lg:gap-6">
        <!-- Mobile Menu Toggle -->
        <button id="mobile-menu-btn" class="lg:hidden p-2 -ml-2 rounded-xl text-text-muted hover:bg-surface-subtle dark:hover:bg-white/5 transition-all focus:outline-none focus:ring-2 focus:ring-primary-base" aria-label="Toggle Navigation Menu">
            <span class="material-symbols-outlined" aria-hidden="true">menu</span>
        </button>
        
        <!-- Brand Area (Visible on mobile if sidebar is hidden, or on desktop if no sidebar) -->
        <div class="flex items-center gap-3 <?= $portal === 'guest' ? '' : 'lg:hidden' ?>">
            <div class="w-8 h-8 rounded-lg bg-primary-base flex items-center justify-center">
                <span class="material-symbols-outlined text-white text-sm" aria-hidden="true">account_balance</span>
            </div>
            <div class="font-headline font-black text-xl tracking-tight text-text-main dark:text-white">Finzo</div>
        </div>

        <!-- Breadcrumbs / Page Title (Desktop) -->
        <div class="hidden lg:flex flex-col justify-center">
            <nav aria-label="Breadcrumb" class="flex items-center space-x-2 text-xs font-bold text-text-muted uppercase tracking-widest mb-0.5">
                <a href="#" class="hover:text-primary-base transition-colors"><?= ucfirst($portal) ?> Portal</a>
                <span class="material-symbols-outlined text-[14px]" aria-hidden="true">chevron_right</span>
                <span aria-current="page" class="text-text-main dark:text-white"><?= htmlspecialchars($pageTitle) ?></span>
            </nav>
        </div>
    </div>

    <!-- Right Side Actions -->
    <div class="flex items-center gap-2 sm:gap-4">
        <!-- Search -->
        <div class="hidden md:flex relative group">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="material-symbols-outlined text-text-muted text-[18px]" aria-hidden="true">search</span>
            </div>
            <input type="text" placeholder="Search..." class="w-64 pl-10 pr-4 py-2 border border-border-subtle rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-primary-base bg-surface-subtle dark:bg-surface-dark text-text-main dark:text-white transition-all group-hover:border-border-focus placeholder:text-text-muted">
        </div>

        <!-- Notifications -->
        <button class="relative p-2 rounded-full text-text-muted hover:bg-surface-subtle dark:hover:bg-white/5 transition-all focus:outline-none focus:ring-2 focus:ring-primary-base" aria-label="Notifications">
            <span class="material-symbols-outlined" aria-hidden="true">notifications</span>
            <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-danger-base ring-2 ring-white dark:ring-surface-dark"></span>
        </button>

        <div class="h-6 w-px bg-border-subtle hidden sm:block"></div>

        <!-- User Profile Menu -->
        <div class="relative ml-2 flex items-center gap-3 cursor-pointer group">
            <div class="text-right hidden sm:block">
                <div class="text-sm font-bold text-text-main dark:text-white leading-tight" id="header-admin-name">Admin</div>
                <div class="text-xs text-text-muted" id="header-admin-id">Loading...</div>
            </div>
            <div class="admin-avatar w-10 h-10 rounded-full bg-primary-surface text-primary-base border border-primary-base/20 flex items-center justify-center font-bold text-sm shadow-sm transition-transform group-hover:scale-105">
                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">person</span>
            </div>
        </div>
    </div>
</header>
