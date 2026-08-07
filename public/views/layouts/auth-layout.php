<?php
$pageTitle = $pageTitle ?? 'Finzo Authentication';
$authBgImage = $authBgImage ?? 'customer_bg.png';
$authHeroTitle = $authHeroTitle ?? 'Secure online banking, built for you.';
$authHeroSubtitle = $authHeroSubtitle ?? 'Sign in to access your accounts, manage transactions, and stay in control of your finances — securely and reliably.';
$authSecurityIcon = $authSecurityIcon ?? 'verified_user';
$authSecurityTitle = $authSecurityTitle ?? 'Your session is protected';
$authSecurityDesc = $authSecurityDesc ?? 'Every login is secured with 256-bit encryption and monitored for unauthorized access attempts.';
$portal = 'auth';

ob_start();
?>
<div class="min-h-screen flex flex-col lg:flex-row bg-surface-main w-full">
    <!-- Left panel (Information Panel & Branding) -->
    <div class="hidden lg:flex flex-col justify-between w-full lg:w-5/12 xl:w-5/12 bg-primary-900 text-white relative p-12 overflow-hidden shadow-2xl z-10">
        <!-- background images -->
        <div class="absolute inset-0 bg-gradient-to-br from-primary-900 to-primary-800 z-10"></div>
        <img src="/Finzo/public/assets/images/<?= htmlspecialchars($authBgImage) ?>" class="absolute inset-0 w-full h-full object-cover z-0 opacity-20 mix-blend-overlay" alt="" />
        
        <div class="relative z-20">
            <a href="/Finzo/public/index.php" class="flex items-center gap-3 text-white mb-16 hover:opacity-90 transition-opacity w-max">
                <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center backdrop-blur-sm border border-white/20 shadow-inner">
                    <span class="material-symbols-outlined text-2xl">account_balance</span>
                </div>
                <span class="text-2xl font-bold font-display tracking-tight">Finzo</span>
            </a>
            
            <h2 class="text-3xl xl:text-4xl font-bold font-display mb-5 leading-tight tracking-tight"><?= htmlspecialchars($authHeroTitle) ?></h2>
            <p class="text-primary-100 text-base leading-relaxed max-w-md opacity-90"><?= htmlspecialchars($authHeroSubtitle) ?></p>
        </div>
        
        <div class="relative z-20 mt-12">
            <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-5 mb-8 shadow-lg transition-transform hover:-translate-y-1 duration-300">
                <div class="flex items-start gap-4">
                    <span class="material-symbols-outlined text-success-400 text-3xl mt-1"><?= htmlspecialchars($authSecurityIcon) ?></span>
                    <div>
                        <h4 class="font-bold text-white mb-1 tracking-wide"><?= htmlspecialchars($authSecurityTitle) ?></h4>
                        <p class="text-sm text-primary-200/80 leading-relaxed"><?= htmlspecialchars($authSecurityDesc) ?></p>
                    </div>
                </div>
            </div>
            <p class="text-xs text-primary-300/60 font-semibold tracking-widest uppercase">&copy; <?= date('Y') ?> Finzo Banking Group. All rights reserved.</p>
        </div>
    </div>
    
    <!-- Right panel (Form Container) -->
    <div class="w-full lg:w-7/12 xl:w-7/12 flex items-center justify-center p-6 sm:p-12 lg:p-16 xl:p-24 relative overflow-y-auto min-h-screen">
        
        <!-- Language Selector -->
        <div class="absolute top-6 right-6 hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-full bg-surface-subtle border border-border-main text-xs font-semibold text-text-main cursor-pointer shadow-sm hover:border-border-focus transition-all" aria-label="Select Language">
            <span class="material-symbols-outlined text-base">language</span>
            <span>EN</span>
            <span class="material-symbols-outlined text-base">expand_more</span>
        </div>
        
        <div class="w-full max-w-[440px] auth-form-container">
            <!-- Mobile Logo -->
            <div class="lg:hidden mb-10 flex justify-center">
                 <a href="/Finzo/public/index.php" class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-primary-surface text-primary-base border border-primary-200 shadow-sm" aria-label="Finzo Homepage">
                     <span class="material-symbols-outlined text-3xl">account_balance</span>
                 </a>
            </div>
            
            <?= $slot ?? '' ?>
            
        </div>
    </div>
</div>

<!-- Enterprise Toast Feedback Notification System -->
<div class="fixed top-6 right-6 z-[9999] flex flex-col gap-3 pointer-events-none" id="toast-container"></div>
<?php
$slot = ob_get_clean();
include __DIR__ . '/app.php';
?>
