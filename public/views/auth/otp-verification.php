<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0, viewport-fit=cover" name="viewport" />
    <meta name="description" content="Finzo Enterprise Banking - Phone OTP Verification Coming Soon" />
    <title>Finzo | Phone OTP Verification</title>
    
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
            <!-- Left Side -->
            <div class="auth-left-panel">
                <img loading="lazy" src="/Finzo/public/assets/images/customer_bg.png" alt="Phone Verification Illustration" class="auth-hero-img-bg" />
                <img loading="lazy" src="/Finzo/public/assets/images/customer_bg.png" alt="Phone Verification Illustration" class="auth-hero-img-secondary" />
                <div class="auth-hero-overlay"></div>
                
                <div class="auth-hero-content">
                    <div>
                        <a href="/Finzo/public/index.html" class="auth-brand-logo">
                            <div class="auth-brand-icon-box">
                                <span class="material-symbols-outlined text-2xl">account_balance</span>
                            </div>
                            <span class="auth-brand-name">Finzo</span>
                        </a>
                        <h2 class="auth-hero-headline">SMS verification is coming.</h2>
                        <p class="auth-hero-subtitle">Phone-based two-factor authentication is planned for an upcoming release. Your account is currently protected through security question-based verification.</p>
                    </div>
                    
                    <div>
                        <div class="auth-hero-security-card">
                            <span class="material-symbols-outlined auth-hero-security-icon">smartphone</span>
                            <div>
                                <h4 class="auth-hero-security-title">SMS OTP — Planned Feature</h4>
                                <p class="auth-hero-security-desc">When enabled, a 6-digit one-time code will be sent to your registered phone number each time you sign in from a new device.</p>
                            </div>
                        </div>
                        <div class="auth-hero-footer">
                            <p class="auth-hero-copyright">&copy; 2026 Finzo Banking Group. All rights reserved.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side -->
            <div class="auth-right-area">
                <div class="auth-card-container text-center">
                    <!-- Mobile Logo -->
                    <a href="/Finzo/public/index.html" class="auth-mobile-logo" aria-label="Finzo Homepage">
                        <span class="material-symbols-outlined text-2xl">account_balance</span>
                    </a>
                    
                    <!-- Icon -->
                    <div class="w-16 h-16 bg-amber-50 border border-amber-100 text-amber-500 rounded-full flex items-center justify-center mx-auto mb-6">
                        <span class="material-symbols-outlined text-3xl">phonelink_lock</span>
                    </div>

                    <!-- Page Header -->
                    <div class="auth-page-header">
                        <h1 class="auth-page-title">Phone OTP Verification</h1>
                        <div class="flex items-center justify-center gap-2 mt-1">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700 border border-amber-200">
                                <span class="material-symbols-outlined" style="font-size:14px">schedule</span>
                                Coming Soon
                            </span>
                        </div>
                    </div>

                    <!-- Info Alert -->
                    <div class="alert alert-info text-left" role="status">
                        <span class="material-symbols-outlined alert-icon">info</span>
                        <div>
                            SMS-based two-factor authentication is currently under development and will be available in a future release. Your account remains secure through existing verification methods.
                        </div>
                    </div>

                    <!-- Feature Preview -->
                    <div class="text-left mt-5 p-4 rounded-lg border border-gray-100 bg-gray-50">
                        <p class="text-xs font-semibold text-gray-700 mb-3 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px] text-primary">upcoming</span>
                            What's planned
                        </p>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li class="flex items-start gap-2">
                                <span class="material-symbols-outlined text-[16px] text-gray-400 mt-0.5">circle</span>
                                <span>6-digit SMS code sent to your registered phone number.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="material-symbols-outlined text-[16px] text-gray-400 mt-0.5">circle</span>
                                <span>Triggered automatically on new device sign-ins.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="material-symbols-outlined text-[16px] text-gray-400 mt-0.5">circle</span>
                                <span>Code expires in 5 minutes for maximum security.</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Action Buttons -->
                    <div class="space-y-3 mt-6">
                        <a href="login.php" class="btn btn-primary btn-full">
                            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                            <span>Return to Sign In</span>
                        </a>
                    </div>

                    <!-- Trust Badges -->
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
