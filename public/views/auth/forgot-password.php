<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0, viewport-fit=cover" name="viewport" />
    <meta name="description" content="Finzo Enterprise Banking - Password Recovery" />
    <title>Finzo | Password Recovery</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="/Finzo/public/assets/js/core/tailwind-config.js"></script>
    
    <link rel="stylesheet" href="/Finzo/public/assets/css/base/variables.css" />
    <link rel="stylesheet" href="/Finzo/public/assets/css/components/forms.css" />
    <link rel="stylesheet" href="/Finzo/public/assets/css/components/buttons.css" />
    <link rel="stylesheet" href="/Finzo/public/assets/css/components/cards.css" />
    <link rel="stylesheet" href="/Finzo/public/assets/css/components/badges.css" />
    <link rel="stylesheet" href="/Finzo/public/assets/css/layouts/auth.css" />
</head>
<body class="bg-gray-50 font-body text-on-surface">
    <main class="auth-page-wrapper">
        <div class="auth-split-grid">
            <!-- Left Side: Hero Illustration & Security Banner -->
            <div class="auth-left-panel">
                <img loading="lazy" src="/Finzo/public/assets/images/forget-password-page-image.png" alt="Recovery Illustration" class="auth-hero-img-bg" />
                <img loading="lazy" src="/Finzo/public/assets/images/forget-password-page-image.png" alt="Recovery Illustration" class="auth-hero-img-secondary" />
                <div class="auth-hero-overlay"></div>
                
                <div class="auth-hero-content">
                    <div>
                        <a href="/Finzo/public/index.html" class="auth-brand-logo">
                            <div class="auth-brand-icon-box">
                                <span class="material-symbols-outlined text-2xl">account_balance</span>
                            </div>
                            <span class="auth-brand-name">Finzo</span>
                        </a>
                        <h2 class="auth-hero-headline">Account access &amp; recovery.</h2>
                        <p class="auth-hero-subtitle">Email and Phone OTP verification are coming soon. Account password resets are handled directly by your administrator.</p>
                    </div>
                    
                    <div>
                        <div class="auth-hero-security-card">
                            <span class="material-symbols-outlined auth-hero-security-icon">admin_panel_settings</span>
                            <div>
                                <h4 class="auth-hero-security-title">Administrator-Managed Recovery</h4>
                                <p class="auth-hero-security-desc">To protect account security, user passwords are generated and reset manually by branch administrators. No automated email links are sent.</p>
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
                <div class="auth-card-container text-center">
                    <!-- Mobile Logo -->
                    <a href="/Finzo/public/index.html" class="auth-mobile-logo" aria-label="Finzo Homepage">
                        <span class="material-symbols-outlined text-2xl">account_balance</span>
                    </a>

                    <!-- Icon -->
                    <div class="w-16 h-16 bg-amber-50 border border-amber-100 text-amber-500 rounded-full flex items-center justify-center mx-auto mb-6">
                        <span class="material-symbols-outlined text-3xl">mark_email_unread</span>
                    </div>

                    <!-- Page Header -->
                    <div class="auth-page-header">
                        <h1 class="auth-page-title">Password Recovery</h1>
                        <div class="flex items-center justify-center gap-2 mt-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700 border border-amber-200">
                                <span class="material-symbols-outlined" style="font-size:14px">schedule</span>
                                Email Recovery &mdash; Coming Soon
                            </span>
                        </div>
                    </div>

                    <!-- Alert Notice -->
                    <div class="alert alert-info text-left mt-6" role="status">
                        <span class="material-symbols-outlined alert-icon">info</span>
                        <div>
                            Automated email and phone OTP recovery are currently under development. Online password reset via email link is not active.
                        </div>
                    </div>

                    <!-- Information Box -->
                    <div class="text-left mt-5 p-4 rounded-lg border border-gray-100 bg-gray-50">
                        <p class="text-xs font-semibold text-gray-700 mb-2 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px] text-primary">support_agent</span>
                            How to reset your password
                        </p>
                        <p class="text-sm text-gray-600 leading-relaxed mb-2">
                            User accounts do not use security questions. If you forgot your password or your account is locked, contact your branch administrator.
                        </p>
                        <p class="text-xs text-gray-500 leading-relaxed">
                            The administrator will verify your identity and generate a new secure password for your account.
                        </p>
                    </div>

                    <!-- Buttons Group -->
                    <div class="space-y-3 mt-6">
                        <a href="login.php" class="btn btn-primary btn-full">
                            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                            <span>Return to Sign In</span>
                        </a>
                    </div>

                    <!-- Trust Badges -->
                    <div class="trust-badge-group">
                        <span class="trust-badge"><span class="material-symbols-outlined">enhanced_encryption</span> 256-bit Encryption</span>
                        <span class="trust-badge"><span class="material-symbols-outlined">gpp_good</span> Identity Protected</span>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
