<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0, viewport-fit=cover" name="viewport" />
    <meta name="description" content="Finzo Enterprise Banking - Identity Verification Success Confirmation" />
    <title>Finzo | Identity Verified</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />

    <script defer src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="/Finzo/public/assets/js/core/tailwind-config.js"></script>
    
    <link rel="stylesheet" href="/Finzo/public/assets/css/base/variables.css" />
    <link rel="stylesheet" href="/Finzo/public/assets/css/components/forms.css" />
    <link rel="stylesheet" href="/Finzo/public/assets/css/components/buttons.css" />
    <link rel="stylesheet" href="/Finzo/public/assets/css/components/cards.css" />
    <link rel="stylesheet" href="/Finzo/public/assets/css/components/badges.css" />
    <link rel="stylesheet" href="/Finzo/public/assets/css/layouts/auth.css" />

    <script src="/Finzo/public/assets/js/core/api.js"></script>
    <script src="/Finzo/public/assets/js/auth/auth-components.js" defer></script>
    <script defer src="/Finzo/public/assets/js/core/ui-components.js"></script>
</head>
<body class="bg-gray-50 font-body text-on-surface">
    <main class="auth-page-wrapper">
        <div class="auth-split-grid">
            <!-- Left Side: Hero Illustration & Security Banner -->
            <div class="auth-left-panel">
                <img loading="lazy" src="/Finzo/public/assets/images/landing-dashboard.jpg" alt="Identity Verified Illustration" class="auth-hero-img-bg" />
                <img loading="lazy" src="/Finzo/public/assets/images/landing-dashboard.jpg" alt="Identity Verified Illustration" class="auth-hero-img-secondary" />
                <div class="auth-hero-overlay"></div>
                
                <div class="auth-hero-content">
                    <div>
                        <a href="/Finzo/public/index.html" class="auth-brand-logo">
                            <div class="auth-brand-icon-box">
                                <span class="material-symbols-outlined text-2xl">account_balance</span>
                            </div>
                            <span class="auth-brand-name">Finzo</span>
                        </a>
                        <h2 class="auth-hero-headline">Your account is ready.</h2>
                        <p class="auth-hero-subtitle">Your email has been verified and your Finzo account is now active. You can sign in and start managing your finances.</p>
                    </div>
                    
                    <div>
                        <div class="auth-hero-security-card">
                            <span class="material-symbols-outlined auth-hero-security-icon">verified</span>
                            <div>
                                <h4 class="auth-hero-security-title">Verification complete</h4>
                                <p class="auth-hero-security-desc">Your email has been confirmed and your account is secured. You can now sign in and manage your account.</p>
                            </div>
                        </div>
                        <div class="auth-hero-footer">
                            <p class="auth-hero-copyright">&copy; 2026 Finzo Banking Group. All rights reserved.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Auth Component Container -->
            <div class="auth-right-area">
                <!-- Language Selector -->
                <div class="auth-lang-selector" aria-label="Select Language">
                    <span class="material-symbols-outlined text-[16px]">language</span>
                    <span>EN</span>
                    <span class="material-symbols-outlined text-[16px]">expand_more</span>
                </div>
                
                <div class="auth-card-container text-center">
                    <!-- Mobile Logo -->
                    <a href="/Finzo/public/index.html" class="auth-mobile-logo" aria-label="Finzo Homepage">
                        <span class="material-symbols-outlined text-2xl">account_balance</span>
                    </a>
                    
                    <!-- Emblem Component -->
                    <div class="w-16 h-16 bg-green-50 border border-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <span class="material-symbols-outlined text-3xl">check_circle</span>
                    </div>

                    <!-- Page Header Component -->
                    <div class="auth-page-header">
                        <h1 class="auth-page-title">Identity Verified</h1>
                        <p class="auth-page-subtitle">Your profile is now active and fully secured.</p>
                    </div>

                    <!-- Alert Success Component -->
                    <div class="alert alert-success text-left" role="status">
                        <span class="material-symbols-outlined alert-icon">task_alt</span>
                        <div>
                            Your Finzo credentials have been verified. You can now log in to access your digital banking workspace.
                        </div>
                    </div>

                    <!-- Button Component -->
                    <div class="mt-6">
                        <a href="login.php" class="btn btn-primary btn-full">
                            <span>Proceed to Login</span>
                            <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                        </a>
                    </div>

                    <!-- Security Notice Component -->
                    <div class="auth-security-notice text-left mt-8">
                        <span class="material-symbols-outlined auth-security-notice-icon">shield</span>
                        <div class="auth-security-notice-text">
                            <strong>Security tip:</strong> We recommend enabling two-step verification after your first login for added account protection.
                        </div>
                    </div>

                    <!-- Trust Badges Component -->
                    <div class="trust-badge-group">
                        <span class="trust-badge"><span class="material-symbols-outlined">enhanced_encryption</span> 256-bit Encryption</span>
                        <span class="trust-badge"><span class="material-symbols-outlined">gpp_good</span> Privacy Protected</span>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
