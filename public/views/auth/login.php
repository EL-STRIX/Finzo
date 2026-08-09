<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0, viewport-fit=cover" name="viewport" />
    <meta name="description" content="Finzo Enterprise Banking - Secure Customer Sign In" />
    <title>Finzo | Welcome Back</title>
    
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
    <script src="/Finzo/public/assets/js/pages/auth/auth-components.js" defer></script>
    <script defer src="/Finzo/public/assets/js/core/ui-components.js"></script>
<script defer src="/Finzo/public/assets/js/auth/login.js"></script>
</head>
<body class="bg-gray-50 font-body text-on-surface">
    <main class="auth-page-wrapper">
        <div class="auth-split-grid">
            <!-- Left Side: Hero Illustration & Security Banner -->
            <div class="auth-left-panel">
                <img loading="lazy" src="/Finzo/public/assets/images/customer_bg.png" alt="Enterprise Banking Illustration" class="auth-hero-img-bg" />
                <img loading="lazy" src="/Finzo/public/assets/images/customer_bg.png" alt="Enterprise Banking Illustration" class="auth-hero-img-secondary" />
                <div class="auth-hero-overlay"></div>
                
                <div class="auth-hero-content">
                    <div>
                        <a href="/Finzo/public/index.html" class="auth-brand-logo">
                            <div class="auth-brand-icon-box">
                                <span class="material-symbols-outlined text-2xl">account_balance</span>
                            </div>
                            <span class="auth-brand-name">Finzo</span>
                        </a>
                        <h2 class="auth-hero-headline">Secure online banking, built for you.</h2>
                        <p class="auth-hero-subtitle">Sign in to access your accounts, manage transactions, and stay in control of your finances — securely and reliably.</p>
                    </div>
                    
                    <div>
                        <div class="auth-hero-security-card">
                            <span class="material-symbols-outlined auth-hero-security-icon">verified_user</span>
                            <div>
                                <h4 class="auth-hero-security-title">Your session is protected</h4>
                                <p class="auth-hero-security-desc">Every login is secured with 256-bit encryption and monitored for unauthorized access attempts.</p>
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
                
                <div class="auth-card-container">
                    <!-- Mobile Logo -->
                    <a href="/Finzo/public/index.html" class="auth-mobile-logo" aria-label="Finzo Homepage">
                        <span class="material-symbols-outlined text-2xl">account_balance</span>
                    </a>
                    
                    <!-- Page Header Component -->
                    <div class="auth-page-header">
                        <h1 class="auth-page-title">Welcome Back</h1>
                        <p class="auth-page-subtitle">Sign in to manage your enterprise accounts securely.</p>
                    </div>

                    <!-- Role Switcher Component -->
                    <div class="auth-role-switcher" role="tablist" aria-label="Select Authentication Role">
                        <a href="login.php" class="auth-role-tab active" role="tab" aria-selected="true">Customer</a>
                        <a href="staff-login.php" class="auth-role-tab" role="tab" aria-selected="false">Staff</a>
                        <a href="admin-login.php" class="auth-role-tab" role="tab" aria-selected="false">Admin</a>
                    </div>

                    <!-- Login Form Component -->
                    <form data-action="customer-login" method="POST" novalidate>
                        <input type="hidden" name="csrf_token" value="" />
                        
                        <!-- Customer ID / Identity Input Component -->
                        <div class="fin-input-container">
                            <label for="customer-id" class="fin-label fin-label-required">Customer ID or Email</label>
                            <div class="fin-input-wrapper">
                                <span class="material-symbols-outlined input-icon-left">person</span>
                                <input type="text" id="customer-id" name="identity" class="fin-input has-icon-left" placeholder="Enter Customer ID or email" required autocomplete="username" />
                            </div>
                            <span class="fin-error-text" role="alert">
                                <span class="material-symbols-outlined">error</span> Customer ID or email is required.
                            </span>
                        </div>

                        <!-- Password Input Component -->
                        <div class="fin-input-container">
                            <div class="flex justify-between items-center mb-1">
                                <label for="login-password" class="fin-label fin-label-required">Password</label>
                                <a href="forgot-password.php" class="btn-text">Forgot password?</a>
                            </div>
                            <div class="fin-input-wrapper">
                                <span class="material-symbols-outlined input-icon-left">lock</span>
                                <input type="password" id="login-password" name="password" class="fin-input has-icon-left has-icon-right" placeholder="Enter password" required autocomplete="current-password" />
                                <button type="button" class="password-toggle-btn" aria-label="Toggle password visibility" tabindex="0">
                                    <span class="material-symbols-outlined">visibility</span>
                                </button>
                            </div>
                            <span class="fin-error-text" role="alert">
                                <span class="material-symbols-outlined">error</span> Password is required.
                            </span>
                        </div>

                        <!-- Remember Checkbox Component -->
                        <div class="mb-6">
                            <label class="fin-checkbox-wrapper">
                                <input type="checkbox" id="remember-me" name="remember" class="fin-checkbox-input" />
                                <span class="fin-checkbox-label">Remember my ID on this device</span>
                            </label>
                        </div>

                        <!-- Submit Button Component -->
                        <button type="submit" class="btn btn-primary btn-full">
                            <span>Secure Login</span>
                            <span class="material-symbols-outlined text-[18px]">login</span>
                        </button>
                    </form>

                    <!-- Page Footer Link -->
                    <div class="mt-8 text-center text-sm text-gray-600 border-t border-gray-100 pt-6">
                        Don't have an account? <a href="register.php" class="font-semibold text-primary hover:underline">Open an Account</a>
                    </div>

                    <!-- Security Notice Component -->
                    <div class="auth-security-notice">
                        <span class="material-symbols-outlined auth-security-notice-icon">shield</span>
                        <div class="auth-security-notice-text">
                            <strong>Protected Session:</strong> Never share your online banking credentials or OTP with anyone. Finzo staff will never request your password.
                        </div>
                    </div>

                    <!-- Trust Badges Component -->
                    <div class="trust-badge-group">
                        <span class="trust-badge"><span class="material-symbols-outlined">enhanced_encryption</span> 256-bit Encryption</span>
                        <span class="trust-badge"><span class="material-symbols-outlined">gpp_good</span> Privacy Protected</span>
                        <span class="trust-badge"><span class="material-symbols-outlined">verified</span> Verified Platform</span>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
