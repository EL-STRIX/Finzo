<?php
$portal = $portal ?? 'guest';
$pageTitle = $pageTitle ?? 'Dashboard';
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
        
        <div class="h-6 w-px bg-border-subtle hidden sm:block"></div>

        <!-- User Profile Menu -->
        <div class="relative ml-2 flex items-center gap-3 cursor-pointer group">
            <div class="text-right hidden sm:block">
                <div class="text-sm font-bold text-text-primary leading-tight" id="header-profile-name">Finzo User</div>
                <div class="text-xs text-text-secondary" id="header-profile-id">Loading...</div>
            </div>
            <div class="header-avatar w-10 h-10 rounded-full bg-primary-50 text-primary border border-primary-100 flex items-center justify-center font-bold text-sm shadow-sm transition-transform group-hover:scale-105">
                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">person</span>
            </div>
        </div>
    </div>
</header>
