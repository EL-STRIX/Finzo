<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0, viewport-fit=cover" name="viewport" />
    <meta name="description" content="Finzo Enterprise Banking - Security Account Lockout Notice" />
    <title>Finzo | Account Locked</title>
    
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
                <img loading="lazy" src="/Finzo/public/assets/images/landing-dashboard.jpg" alt="Automated Security Illustration" class="auth-hero-img-bg" />
                <img loading="lazy" src="/Finzo/public/assets/images/landing-dashboard.jpg" alt="Automated Security Illustration" class="auth-hero-img-secondary" />
                <div class="auth-hero-overlay"></div>
                
                <div class="auth-hero-content">
                    <div>
                        <a href="/Finzo/public/index.php" class="auth-brand-logo">
                            <div class="auth-brand-icon-box">
                                <span class="material-symbols-outlined text-2xl">account_balance</span>
                            </div>
                            <span class="auth-brand-name">Finzo</span>
                        </a>
                        <h2 class="auth-hero-headline">Your account has been locked.</h2>
                        <p class="auth-hero-subtitle">Too many failed login attempts were detected. Your account has been temporarily suspended to prevent unauthorized access.</p>
                    </div>
                    
                    <div>
                        <div class="auth-hero-security-card">
                            <span class="material-symbols-outlined auth-hero-security-icon">gpp_maybe</span>
                            <div>
                                <h4 class="auth-hero-security-title">Too many failed login attempts</h4>
                                <p class="auth-hero-security-desc">Accounts are automatically locked after multiple incorrect password attempts to protect against unauthorized access.</p>
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
                    <a href="/Finzo/public/index.php" class="auth-mobile-logo" aria-label="Finzo Homepage">
                        <span class="material-symbols-outlined text-2xl">account_balance</span>
                    </a>
                    
                    <!-- Emblem Component -->
                    <div class="w-16 h-16 bg-red-50 border border-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <span class="material-symbols-outlined text-3xl">lock_person</span>
                    </div>

                    <!-- Page Header Component -->
                    <div class="auth-page-header">
                        <h1 class="auth-page-title">Account Locked</h1>
                        <p class="auth-page-subtitle">Access temporarily suspended for security reasons.</p>
                    </div>

                    <!-- Alert Danger Component -->
                    <div class="alert alert-danger text-left" role="alert">
                        <span class="material-symbols-outlined alert-icon">error</span>
                        <div>
                            Too many consecutive failed login attempts. Access to this account has been suspended for <strong>30 minutes</strong>.
                        </div>
                    </div>

                    <!-- Contact Support Block -->
                    <div class="auth-contact-widget">
                        <p class="auth-contact-title">Need immediate assistance?</p>
                        <div class="auth-contact-list">
                            <a href="tel:+18005550199" class="auth-contact-item">
                                <span class="auth-contact-icon">
                                    <span class="material-symbols-outlined">phone</span>
                                </span>
                                <span>24/7 Fraud &amp; Security Hotline: +1-800-555-0199</span>
                            </a>
                            <a href="mailto:support@finzo.com" class="auth-contact-item">
                                <span class="auth-contact-icon">
                                    <span class="material-symbols-outlined">mail</span>
                                </span>
                                <span>Email Support: support@finzo.com</span>
                            </a>
                        </div>
                    </div>

                    <!-- Actions Group Component -->
                    <div class="mt-4">
                        <a href="/Finzo/public/index.php" class="btn btn-primary btn-full">
                            <span class="material-symbols-outlined text-[18px]">home</span>
                            <span>Return to Homepage</span>
                        </a>
                    </div>

                    <!-- Security Notice Component -->
                    <div class="auth-security-notice text-left mt-6">
                        <span class="material-symbols-outlined auth-security-notice-icon">shield</span>
                        <div class="auth-security-notice-text">
                            <strong>Security Notice:</strong> Your account will automatically unlock after 30 minutes. If you did not initiate these login attempts, contact our security team immediately.
                        </div>
                    </div>

                    <!-- Trust Badges Component -->
                    <div class="trust-badge-group">
                        <span class="trust-badge"><span class="material-symbols-outlined">enhanced_encryption</span> 256-bit Encryption</span>
                        <span class="trust-badge"><span class="material-symbols-outlined">gpp_good</span> Automated Defense</span>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0, viewport-fit=cover" name="viewport" />
    <meta name="description" content="Finzo Enterprise Banking - Administrator Terminal Authentication" />
    <title>Finzo | Admin Authentication</title>
    
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
                <img loading="lazy" src="/Finzo/public/assets/images/admin_bg.png" alt="Admin Terminal Illustration" class="auth-hero-img-bg" />
                <img loading="lazy" src="/Finzo/public/assets/images/admin_bg.png" alt="Admin Terminal Illustration" class="auth-hero-img-secondary" />
                <div class="auth-hero-overlay"></div>
                
                <div class="auth-hero-content">
                    <div>
                        <a href="/Finzo/public/index.php" class="auth-brand-logo">
                            <div class="auth-brand-icon-box">
                                <span class="material-symbols-outlined text-2xl">account_balance</span>
                            </div>
                            <span class="auth-brand-name">Finzo</span>
                        </a>
                        <h2 class="auth-hero-headline">Enterprise System Management</h2>
                        <p class="auth-hero-subtitle">Centralized core system configuration, security audit trail monitoring, interest yield management, and global access administration.</p>
                    </div>
                    
                    <div>
                        <div class="auth-hero-security-card">
                            <span class="material-symbols-outlined auth-hero-security-icon">security</span>
                            <div>
                                <h4 class="auth-hero-security-title">Zero-Trust Terminal Protection</h4>
                                <p class="auth-hero-security-desc">Hardware security token validation combined with IP whitelist checks protect administrative system privileges.</p>
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
                    <a href="/Finzo/public/index.php" class="auth-mobile-logo" aria-label="Finzo Homepage">
                        <span class="material-symbols-outlined text-2xl">account_balance</span>
                    </a>
                    
                    <!-- Page Header Component -->
                    <div class="auth-page-header">
                        <h1 class="auth-page-title">Admin Authentication</h1>
                        <p class="auth-page-subtitle">Strictly authorized administrative personnel only.</p>
                    </div>

                    <!-- Role Switcher Component -->
                    <div class="auth-role-switcher" role="tablist" aria-label="Select Authentication Role">
                        <a href="login.php" class="auth-role-tab" role="tab" aria-selected="false">Customer</a>
                        <a href="staff-login.php" class="auth-role-tab" role="tab" aria-selected="false">Staff</a>
                        <a href="admin-login.php" class="auth-role-tab active" role="tab" aria-selected="true">Admin</a>
                    </div>

                    <!-- Alert Warning Component -->
                    <div class="alert alert-danger" role="alert">
                        <span class="material-symbols-outlined alert-icon">warning</span>
                        <div>
                            <strong>Mandatory Audit Notice:</strong> Access to this terminal is subject to real-time activity recording and strict forensic logging.
                        </div>
                    </div>

                    <!-- Admin Form Component -->
                    <form data-action="admin-login" method="POST" novalidate>
                        <input type="hidden" name="csrf_token" value="" />
                        
                        <!-- Administrator Email Input Component -->
                        <div class="fin-input-container">
                            <label for="admin-id" class="fin-label fin-label-required">Administrator Email</label>
                            <div class="fin-input-wrapper">
                                <span class="material-symbols-outlined input-icon-left">shield_person</span>
                                <input type="email" id="admin-id" name="identity" class="fin-input has-icon-left" placeholder="admin@finzo.com" required autocomplete="username" />
                            </div>
                            <span class="fin-error-text" role="alert">
                                <span class="material-symbols-outlined">error</span> Administrator email is required.
                            </span>
                        </div>

                        <!-- Security Token / Password Input Component -->
                        <div class="fin-input-container">
                            <label for="admin-token" class="fin-label fin-label-required">Security Token / Password</label>
                            <div class="fin-input-wrapper">
                                <span class="material-symbols-outlined input-icon-left">vpn_key</span>
                                <input type="password" id="admin-token" name="password" class="fin-input has-icon-left has-icon-right" placeholder="Enter security token or password" required autocomplete="current-password" />
                                <button type="button" class="password-toggle-btn" aria-label="Toggle password visibility" tabindex="0">
                                    <span class="material-symbols-outlined">visibility</span>
                                </button>
                            </div>
                            <span class="fin-error-text" role="alert">
                                <span class="material-symbols-outlined">error</span> Security Token is required.
                            </span>
                        </div>

                        <!-- Submit Button Component -->
                        <button type="submit" class="btn btn-primary btn-full mt-2">
                            <span>Access Terminal</span>
                            <span class="material-symbols-outlined text-[18px]">terminal</span>
                        </button>
                    </form>

                    <!-- Security Notice Component -->
                    <div class="auth-security-notice">
                        <span class="material-symbols-outlined auth-security-notice-icon">policy</span>
                        <div class="auth-security-notice-text">
                            <strong>Restricted Terminal:</strong> All administrative actions require hardware security key authentication and supervisory sign-off.
                        </div>
                    </div>

                    <!-- Trust Badges Component -->
                    <div class="trust-badge-group">
                        <span class="trust-badge"><span class="material-symbols-outlined">admin_panel_settings</span> Zero-Trust Control</span>
                        <span class="trust-badge"><span class="material-symbols-outlined">fingerprint</span> Hardware Token SSL</span>
                    </div>
                </div>
            </div>
        </div>
    </main>
<script defer src="/Finzo/public/assets/js/auth/admin-login.js"></script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0, viewport-fit=cover" name="viewport" />
    <meta name="description" content="Finzo Enterprise Banking - Email Verification Sent" />
    <title>Finzo | Check Your Email</title>
    
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
                <img loading="lazy" src="/Finzo/public/assets/images/customer_bg.png" alt="Identity Confirmation Illustration" class="auth-hero-img-bg" />
                <img loading="lazy" src="/Finzo/public/assets/images/customer_bg.png" alt="Identity Confirmation Illustration" class="auth-hero-img-secondary" />
                <div class="auth-hero-overlay"></div>
                
                <div class="auth-hero-content">
                    <div>
                        <a href="/Finzo/public/index.php" class="auth-brand-logo">
                            <div class="auth-brand-icon-box">
                                <span class="material-symbols-outlined text-2xl">account_balance</span>
                            </div>
                            <span class="auth-brand-name">Finzo</span>
                        </a>
                        <h2 class="auth-hero-headline">Verify your email address.</h2>
                        <p class="auth-hero-subtitle">We need to confirm your email before activating your account. Check your inbox for the verification link we sent.</p>
                    </div>
                    
                    <div>
                        <div class="auth-hero-security-card">
                            <span class="material-symbols-outlined auth-hero-security-icon">mark_email_read</span>
                            <div>
                                <h4 class="auth-hero-security-title">Secure verification link</h4>
                                <p class="auth-hero-security-desc">The link in your email is valid for 30 minutes and can only be used once. After it expires, you can request a new one.</p>
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
                    <a href="/Finzo/public/index.php" class="auth-mobile-logo" aria-label="Finzo Homepage">
                        <span class="material-symbols-outlined text-2xl">account_balance</span>
                    </a>
                    
                    <!-- Emblem Component -->
                    <div class="w-16 h-16 bg-blue-50 border border-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <span class="material-symbols-outlined text-3xl">mark_email_unread</span>
                    </div>

                    <!-- Page Header Component -->
                    <div class="auth-page-header">
                        <h1 class="auth-page-title">Check Your Email</h1>
                        <p class="auth-page-subtitle">We sent a secure verification link to your registered email address.</p>
                    </div>

                    <!-- Alert Info Component -->
                    <div class="alert alert-info text-left" role="status">
                        <span class="material-symbols-outlined alert-icon">info</span>
                        <div>
                            Click the secure link inside the email to complete your registration. The link will expire in 30 minutes.
                        </div>
                    </div>

                    <!-- Buttons Group Component -->
                    <div class="space-y-3 mt-6">
                        <a href="login.php" class="btn btn-primary btn-full">
                            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                            <span>Return to Sign In</span>
                        </a>

                        <button type="button" class="btn btn-secondary btn-full" data-action="resend-email-verification">
                            <span class="material-symbols-outlined text-[18px]">refresh</span>
                            <span>Resend Verification Email</span>
                        </button>
                    </div>

                    <!-- Security Notice Component -->
                    <div class="auth-security-notice text-left mt-8">
                        <span class="material-symbols-outlined auth-security-notice-icon">forward_to_inbox</span>
                        <div class="auth-security-notice-text">
                            <strong>Tip:</strong> If you don't see the email within a few minutes, please check your spam or junk folder.
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0, viewport-fit=cover" name="viewport" />
    <meta name="description" content="Finzo Enterprise Banking - Secure Account Recovery" />
    <title>Finzo | Account Recovery</title>
    
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
                <img loading="lazy" src="/Finzo/public/assets/images/forget-password-page-image.png" alt="Recovery Illustration" class="auth-hero-img-bg" />
                <img loading="lazy" src="/Finzo/public/assets/images/forget-password-page-image.png" alt="Recovery Illustration" class="auth-hero-img-secondary" />
                <div class="auth-hero-overlay"></div>
                
                <div class="auth-hero-content">
                    <div>
                        <a href="/Finzo/public/index.php" class="auth-brand-logo">
                            <div class="auth-brand-icon-box">
                                <span class="material-symbols-outlined text-2xl">account_balance</span>
                            </div>
                            <span class="auth-brand-name">Finzo</span>
                        </a>
                        <h2 class="auth-hero-headline">Recover access to your account.</h2>
                        <p class="auth-hero-subtitle">We'll send a secure link to your registered email so you can reset your password and regain access.</p>
                    </div>
                    
                    <div>
                        <div class="auth-hero-security-card">
                            <span class="material-symbols-outlined auth-hero-security-icon">lock_reset</span>
                            <div>
                                <h4 class="auth-hero-security-title">One-time recovery link</h4>
                                <p class="auth-hero-security-desc">We send a single-use link to your registered email. It expires in 15 minutes to keep your account safe.</p>
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
                    <a href="/Finzo/public/index.php" class="auth-mobile-logo" aria-label="Finzo Homepage">
                        <span class="material-symbols-outlined text-2xl">account_balance</span>
                    </a>
                    
                    <!-- Page Header Component -->
                    <div class="auth-page-header">
                        <h1 class="auth-page-title">Account Recovery</h1>
                        <p class="auth-page-subtitle">Enter your registered email address to receive a secure recovery link.</p>
                    </div>

                    <!-- Recovery Form Component -->
                    <form data-action="forgot-password" method="POST" novalidate>
                        <input type="hidden" name="csrf_token" value="" />
                        
                        <!-- Email Input Component -->
                        <div class="fin-input-container">
                            <label for="recovery-email" class="fin-label fin-label-required">Registered Email Address</label>
                            <div class="fin-input-wrapper">
                                <span class="material-symbols-outlined input-icon-left">mail</span>
                                <input type="email" id="recovery-email" name="email" class="fin-input has-icon-left" placeholder="name@example.com" required autocomplete="email" />
                            </div>
                            <span class="fin-error-text" role="alert">
                                <span class="material-symbols-outlined">error</span> Valid registered email address is required.
                            </span>
                        </div>

                        <!-- Buttons Group Component -->
                        <div class="space-y-3 mt-6">
                            <button type="submit" class="btn btn-primary btn-full">
                                <span>Send Recovery Link</span>
                                <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                            </button>
                            
                            <a href="login.php" class="btn btn-secondary btn-full">
                                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                                <span>Return to Sign In</span>
                            </a>
                        </div>
                    </form>

                    <!-- Security Notice Component -->
                    <div class="auth-security-notice">
                        <span class="material-symbols-outlined auth-security-notice-icon">mark_email_unread</span>
                        <div class="auth-security-notice-text">
                            <strong>Please note:</strong> Recovery links expire in 1 hour. If you did not request a password reset, you can safely ignore this.
                        </div>
                    </div>

                    <!-- Trust Badges Component -->
                    <div class="trust-badge-group">
                        <span class="trust-badge"><span class="material-symbols-outlined">enhanced_encryption</span> 256-bit Encryption</span>
                        <span class="trust-badge"><span class="material-symbols-outlined">gpp_good</span> Identity Protected</span>
                    </div>
                </div>
            </div>
        </div>
    </main>
<script defer src="/Finzo/public/assets/js/auth/forgot-password.js"></script>
</body>
</html>

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
    <script src="/Finzo/public/assets/js/auth/auth-components.js" defer></script>
    <script defer src="/Finzo/public/assets/js/core/ui-components.js"></script>
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
                        <a href="/Finzo/public/index.php" class="auth-brand-logo">
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
                    <a href="/Finzo/public/index.php" class="auth-mobile-logo" aria-label="Finzo Homepage">
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
<script defer src="/Finzo/public/assets/js/auth/login.js"></script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0, viewport-fit=cover" name="viewport" />
    <meta name="description" content="Finzo Enterprise Banking - Device Verification Multi-Factor Authentication" />
    <title>Finzo | Device Verification</title>
    
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
                <img loading="lazy" src="/Finzo/public/assets/images/customer_bg.png" alt="Multi Factor Authentication Illustration" class="auth-hero-img-bg" />
                <img loading="lazy" src="/Finzo/public/assets/images/customer_bg.png" alt="Multi Factor Authentication Illustration" class="auth-hero-img-secondary" />
                <div class="auth-hero-overlay"></div>
                
                <div class="auth-hero-content">
                    <div>
                        <a href="/Finzo/public/index.php" class="auth-brand-logo">
                            <div class="auth-brand-icon-box">
                                <span class="material-symbols-outlined text-2xl">account_balance</span>
                            </div>
                            <span class="auth-brand-name">Finzo</span>
                        </a>
                        <h2 class="auth-hero-headline">Two-step verification.</h2>
                        <p class="auth-hero-subtitle">We sent a one-time code to your registered phone number. Enter it to confirm your identity and sign in.</p>
                    </div>
                    
                    <div>
                        <div class="auth-hero-security-card">
                            <span class="material-symbols-outlined auth-hero-security-icon">smartphone</span>
                            <div>
                                <h4 class="auth-hero-security-title">New device detected</h4>
                                <p class="auth-hero-security-desc">When you sign in from a new or unrecognized device, we require a verification code to confirm it's you.</p>
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
                    <a href="/Finzo/public/index.php" class="auth-mobile-logo" aria-label="Finzo Homepage">
                        <span class="material-symbols-outlined text-2xl">account_balance</span>
                    </a>
                    
                    <!-- Page Header Component -->
                    <div class="auth-page-header">
                        <h1 class="auth-page-title">Device Verification</h1>
                        <p class="auth-page-subtitle">A 6-digit security code was sent to your phone ending in <strong>••45</strong>.</p>
                    </div>

                    <!-- OTP Form Component -->
                    <form action="#" method="POST" class="text-center" novalidate>
                        <!-- 6-Digit OTP Grid Component -->
                        <div class="fin-otp-container mb-8">
                            <div class="fin-otp-grid">
                                <input type="text" maxlength="1" class="fin-otp-input" aria-label="OTP Digit 1" required autofocus />
                                <input type="text" maxlength="1" class="fin-otp-input" aria-label="OTP Digit 2" required />
                                <input type="text" maxlength="1" class="fin-otp-input" aria-label="OTP Digit 3" required />
                                <input type="text" maxlength="1" class="fin-otp-input" aria-label="OTP Digit 4" required />
                                <input type="text" maxlength="1" class="fin-otp-input" aria-label="OTP Digit 5" required />
                                <input type="text" maxlength="1" class="fin-otp-input" aria-label="OTP Digit 6" required />
                            </div>
                        </div>

                        <!-- Primary Submit Button -->
                        <button type="submit" class="btn btn-primary btn-full mb-4">
                            <span>Verify Identity</span>
                            <span class="material-symbols-outlined text-[18px]">verified_user</span>
                        </button>

                        <!-- Resend Component with Countdown -->
                        <p class="text-xs text-gray-500 font-medium">
                            Didn't receive the code? 
                            <button type="button" class="btn-text" data-action="resend-otp">Resend Code in 00:45</button>
                        </p>
                    </form>

                    <!-- Security Notice Component -->
                    <div class="auth-security-notice text-left mt-8">
                        <span class="material-symbols-outlined auth-security-notice-icon">phonelink_lock</span>
                        <div class="auth-security-notice-text">
                            <strong>Important:</strong> OTP codes expire in 5 minutes. Never share this code with anyone, including Finzo staff.
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0, viewport-fit=cover" name="viewport" />
    <meta name="description" content="Finzo Enterprise Banking - Establish Account Application" />
    <title>Finzo | Create Account</title>
    
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
                <img loading="lazy" src="/Finzo/public/assets/images/register-page-image.png" alt="Registration Illustration" class="auth-hero-img-bg" />
                <img loading="lazy" src="/Finzo/public/assets/images/register-page-image.png" alt="Registration Illustration" class="auth-hero-img-secondary" />
                <div class="auth-hero-overlay"></div>
                
                <div class="auth-hero-content">
                    <div>
                        <a href="/Finzo/public/index.php" class="auth-brand-logo">
                            <div class="auth-brand-icon-box">
                                <span class="material-symbols-outlined text-2xl">account_balance</span>
                            </div>
                            <span class="auth-brand-name">Finzo</span>
                        </a>
                        <h2 class="auth-hero-headline">Open your account in minutes.</h2>
                        <p class="auth-hero-subtitle">Create a Finzo account to manage your finances, make transfers, and access secure banking services from anywhere.</p>
                    </div>
                    
                    <div>
                        <div class="auth-hero-security-card">
                            <span class="material-symbols-outlined auth-hero-security-icon">encrypted</span>
                            <div>
                                <h4 class="auth-hero-security-title">Your data is protected</h4>
                                <p class="auth-hero-security-desc">All personal and financial information you submit is encrypted in transit and never shared with unauthorized parties.</p>
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
                    <a href="/Finzo/public/index.php" class="auth-mobile-logo" aria-label="Finzo Homepage">
                        <span class="material-symbols-outlined text-2xl">account_balance</span>
                    </a>
                    
                    <!-- Page Header Component -->
                    <div class="auth-page-header">
                        <h1 class="auth-page-title">Create Account</h1>
                        <p class="auth-page-subtitle">Fill in the details below to open your Finzo account.</p>
                    </div>

                    <!-- Registration Form Component -->
                    <form data-action="register" method="POST" novalidate>
                        <input type="hidden" name="csrf_token" value="" />
                        
                        <!-- Section 1: Personal Details -->
                        <div class="mb-4 border-b border-gray-100 pb-2">
                            <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider">1. Personal Information</h3>
                        </div>
                        
                        <div class="fin-input-container">
                            <label for="full-name" class="fin-label fin-label-required">Full Legal Name</label>
                            <div class="fin-input-wrapper">
                                <span class="material-symbols-outlined input-icon-left">person</span>
                                <input type="text" id="full-name" name="full_name" class="fin-input has-icon-left" placeholder="As stated on government ID" required autocomplete="name" />
                            </div>
                            <span class="fin-error-text" role="alert">
                                <span class="material-symbols-outlined">error</span> Full name is required.
                            </span>
                        </div>

                        <!-- Section 2: Contact Details -->
                        <div class="mb-4 border-b border-gray-100 pb-2 mt-6">
                            <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider">2. Contact Details</h3>
                        </div>

                        <div class="fin-input-container">
                            <label for="reg-email" class="fin-label fin-label-required">Email Address</label>
                            <div class="fin-input-wrapper">
                                <span class="material-symbols-outlined input-icon-left">mail</span>
                                <input type="email" id="reg-email" name="email" class="fin-input has-icon-left" placeholder="name@example.com" required autocomplete="email" />
                            </div>
                            <span class="fin-error-text" role="alert">
                                <span class="material-symbols-outlined">error</span> Valid email address is required.
                            </span>
                        </div>

                        <div class="fin-input-container">
                            <label for="mobile-number" class="fin-label fin-label-required">Mobile Number</label>
                            <div class="fin-input-wrapper">
                                <span class="material-symbols-outlined input-icon-left">call</span>
                                <input type="tel" id="mobile-number" name="phone" class="fin-input has-icon-left" placeholder="+91 98765 43210" required autocomplete="tel" />
                            </div>
                            <span class="fin-error-text" role="alert">
                                <span class="material-symbols-outlined">error</span> Mobile number is required.
                            </span>
                        </div>

                        <!-- Section 3: Password & Security -->
                        <div class="mb-4 border-b border-gray-100 pb-2 mt-6">
                            <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider">3. Security Credentials</h3>
                        </div>

                        <div class="fin-input-container">
                            <label for="create-password" class="fin-label fin-label-required">Create Password</label>
                            <div class="fin-input-wrapper">
                                <span class="material-symbols-outlined input-icon-left">lock</span>
                                <input type="password" id="create-password" name="password" class="fin-input has-icon-left has-icon-right" placeholder="Minimum 8 characters" required autocomplete="new-password" data-strength-meter="true" />
                                <button type="button" class="password-toggle-btn" aria-label="Toggle password visibility" tabindex="0">
                                    <span class="material-symbols-outlined">visibility</span>
                                </button>
                            </div>
                            
                            <!-- Password Strength Component -->
                            <div class="fin-password-strength">
                                <div class="strength-bar-track">
                                    <div class="strength-bar-fill"></div>
                                </div>
                                <div class="strength-info">
                                    <span class="strength-label">Password Strength</span>
                                    <span>Enterprise Standard</span>
                                </div>
                                <div class="requirement-checklist">
                                    <span class="req-item" data-req="length"><span class="material-symbols-outlined">cancel</span> 8+ Characters</span>
                                    <span class="req-item" data-req="upper"><span class="material-symbols-outlined">cancel</span> Uppercase Letter</span>
                                    <span class="req-item" data-req="number"><span class="material-symbols-outlined">cancel</span> Number (0-9)</span>
                                    <span class="req-item" data-req="special"><span class="material-symbols-outlined">cancel</span> Symbol (!@#$)</span>
                                </div>
                            </div>
                            <span class="fin-error-text" role="alert">
                                <span class="material-symbols-outlined">error</span> Password is required.
                            </span>
                        </div>

                        <div class="fin-input-container">
                            <label for="reg-confirm-password" class="fin-label fin-label-required">Confirm Password</label>
                            <div class="fin-input-wrapper">
                                <span class="material-symbols-outlined input-icon-left">check_circle</span>
                                <input type="password" id="reg-confirm-password" name="confirm_password" class="fin-input has-icon-left has-icon-right" placeholder="Confirm password" required autocomplete="new-password" />
                                <button type="button" class="password-toggle-btn" aria-label="Toggle password visibility" tabindex="0">
                                    <span class="material-symbols-outlined">visibility</span>
                                </button>
                            </div>
                            <span class="fin-error-text" role="alert">
                                <span class="material-symbols-outlined">error</span> Passwords must match.
                            </span>
                        </div>

                        <!-- Terms & Conditions Checkbox -->
                        <div class="my-6">
                            <label class="fin-checkbox-wrapper">
                                <input type="checkbox" id="agree-terms" name="terms" class="fin-checkbox-input" required />
                                <span class="fin-checkbox-label">
                                    I agree to Finzo Banking's <a href="#" class="font-semibold text-primary hover:underline">Terms of Service</a> and <a href="#" class="font-semibold text-primary hover:underline">Privacy Policy</a>.
                                </span>
                            </label>
                            <span class="fin-error-text" role="alert">
                                <span class="material-symbols-outlined">error</span> You must accept the Terms and Privacy Policy.
                            </span>
                        </div>

                        <!-- Submit Button Component -->
                        <button type="submit" class="btn btn-primary btn-full">
                            <span>Submit Application</span>
                            <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                        </button>
                    </form>

                    <!-- Page Footer Link -->
                    <div class="mt-6 text-center text-sm text-gray-600 border-t border-gray-100 pt-6">
                        Already have an account? <a href="login.php" class="font-semibold text-primary hover:underline">Sign In</a>
                    </div>

                    <!-- Security Notice Component -->
                    <div class="auth-security-notice">
                        <span class="material-symbols-outlined auth-security-notice-icon">verified</span>
                        <div class="auth-security-notice-text">
                            <strong>Identity Verification:</strong> We will verify your credentials using official records. Your information is never sold or shared with unauthorized third parties.
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
<script defer src="/Finzo/public/assets/js/auth/register.js"></script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0, viewport-fit=cover" name="viewport" />
    <meta name="description" content="Finzo Enterprise Banking - Set New Password" />
    <title>Finzo | Set New Password</title>
    
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
                <img loading="lazy" src="/Finzo/public/assets/images/reset-password-page-image.png" alt="Reset Password Illustration" class="auth-hero-img-bg" />
                <img loading="lazy" src="/Finzo/public/assets/images/reset-password-page-image.png" alt="Reset Password Illustration" class="auth-hero-img-secondary" />
                <div class="auth-hero-overlay"></div>
                
                <div class="auth-hero-content">
                    <div>
                        <a href="/Finzo/public/index.php" class="auth-brand-logo">
                            <div class="auth-brand-icon-box">
                                <span class="material-symbols-outlined text-2xl">account_balance</span>
                            </div>
                            <span class="auth-brand-name">Finzo</span>
                        </a>
                        <h2 class="auth-hero-headline">Set a new password.</h2>
                        <p class="auth-hero-subtitle">Choose a strong password to protect your account and keep your banking information secure.</p>
                    </div>
                    
                    <div>
                        <div class="auth-hero-security-card">
                            <span class="material-symbols-outlined auth-hero-security-icon">published_with_changes</span>
                            <div>
                                <h4 class="auth-hero-security-title">All other sessions will be signed out</h4>
                                <p class="auth-hero-security-desc">When you change your password, any active sessions on other devices will be ended for your protection.</p>
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
                    <a href="/Finzo/public/index.php" class="auth-mobile-logo" aria-label="Finzo Homepage">
                        <span class="material-symbols-outlined text-2xl">account_balance</span>
                    </a>
                    
                    <!-- Page Header Component -->
                    <div class="auth-page-header">
                        <h1 class="auth-page-title">Set New Password</h1>
                        <p class="auth-page-subtitle">Your new password must meet the minimum security requirements.</p>
                    </div>

                    <!-- Reset Password Form Component -->
                    <form data-action="reset-password" method="POST" novalidate>
                        <input type="hidden" name="csrf_token" value="" />
                        <input type="hidden" name="token" value="" />
                        
                        <!-- New Password Input Component -->
                        <div class="fin-input-container">
                            <label for="new-password" class="fin-label fin-label-required">New Password</label>
                            <div class="fin-input-wrapper">
                                <span class="material-symbols-outlined input-icon-left">lock</span>
                                <input type="password" id="new-password" name="new_password" class="fin-input has-icon-left has-icon-right" placeholder="Enter new password" required autocomplete="new-password" data-strength-meter="true" />
                                <button type="button" class="password-toggle-btn" aria-label="Toggle password visibility" tabindex="0">
                                    <span class="material-symbols-outlined">visibility</span>
                                </button>
                            </div>
                            
                            <!-- Password Strength Component -->
                            <div class="fin-password-strength">
                                <div class="strength-bar-track">
                                    <div class="strength-bar-fill"></div>
                                </div>
                                <div class="strength-info">
                                    <span class="strength-label">Password Strength</span>
                                    <span>Enterprise Standard</span>
                                </div>
                                <div class="requirement-checklist">
                                    <span class="req-item" data-req="length"><span class="material-symbols-outlined">cancel</span> 8+ Characters</span>
                                    <span class="req-item" data-req="upper"><span class="material-symbols-outlined">cancel</span> Uppercase Letter</span>
                                    <span class="req-item" data-req="number"><span class="material-symbols-outlined">cancel</span> Number (0-9)</span>
                                    <span class="req-item" data-req="special"><span class="material-symbols-outlined">cancel</span> Symbol (!@#$)</span>
                                </div>
                            </div>
                            <span class="fin-error-text" role="alert">
                                <span class="material-symbols-outlined">error</span> New password is required.
                            </span>
                        </div>

                        <!-- Confirm Password Input Component -->
                        <div class="fin-input-container">
                            <label for="confirm-password" class="fin-label fin-label-required">Confirm New Password</label>
                            <div class="fin-input-wrapper">
                                <span class="material-symbols-outlined input-icon-left">check_circle</span>
                                <input type="password" id="confirm-password" name="confirm_password" class="fin-input has-icon-left has-icon-right" placeholder="Confirm new password" required autocomplete="new-password" />
                                <button type="button" class="password-toggle-btn" aria-label="Toggle password visibility" tabindex="0">
                                    <span class="material-symbols-outlined">visibility</span>
                                </button>
                            </div>
                            <span class="fin-error-text" role="alert">
                                <span class="material-symbols-outlined">error</span> Passwords must match.
                            </span>
                        </div>

                        <!-- Submit Button Component -->
                        <button type="submit" class="btn btn-primary btn-full mt-2">
                            <span>Update Password</span>
                            <span class="material-symbols-outlined text-[18px]">verified</span>
                        </button>
                    </form>

                    <!-- Security Notice Component -->
                    <div class="auth-security-notice">
                        <span class="material-symbols-outlined auth-security-notice-icon">shield</span>
                        <div class="auth-security-notice-text">
                            <strong>Tip:</strong> Use a unique password that you don't use on other websites or apps.
                        </div>
                    </div>

                    <!-- Trust Badges Component -->
                    <div class="trust-badge-group">
                        <span class="trust-badge"><span class="material-symbols-outlined">enhanced_encryption</span> 256-bit SSL</span>
                        <span class="trust-badge"><span class="material-symbols-outlined">gpp_good</span> Multi-Layer Security</span>
                    </div>
                </div>
            </div>
        </div>
    </main>
<script defer src="/Finzo/public/assets/js/auth/reset-password.js"></script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0, viewport-fit=cover" name="viewport" />
    <meta name="description" content="Finzo Enterprise Banking - Inactivity Session Timeout Notice" />
    <title>Finzo | Session Terminated</title>
    
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
                <img loading="lazy" src="/Finzo/public/assets/images/landing-dashboard.jpg" alt="Session Protection Illustration" class="auth-hero-img-bg" />
                <img loading="lazy" src="/Finzo/public/assets/images/landing-dashboard.jpg" alt="Session Protection Illustration" class="auth-hero-img-secondary" />
                <div class="auth-hero-overlay"></div>
                
                <div class="auth-hero-content">
                    <div>
                        <a href="/Finzo/public/index.php" class="auth-brand-logo">
                            <div class="auth-brand-icon-box">
                                <span class="material-symbols-outlined text-2xl">account_balance</span>
                            </div>
                            <span class="auth-brand-name">Finzo</span>
                        </a>
                        <h2 class="auth-hero-headline">Your session has ended.</h2>
                        <p class="auth-hero-subtitle">For your security, sessions are automatically ended after a period of inactivity. Please sign in again to continue.</p>
                    </div>
                    
                    <div>
                        <div class="auth-hero-security-card">
                            <span class="material-symbols-outlined auth-hero-security-icon">timer</span>
                            <div>
                                <h4 class="auth-hero-security-title">Automatic sign-out after inactivity</h4>
                                <p class="auth-hero-security-desc">Finzo signs you out automatically after 24 hours of inactivity to protect your account from unauthorized access.</p>
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
                    <a href="/Finzo/public/index.php" class="auth-mobile-logo" aria-label="Finzo Homepage">
                        <span class="material-symbols-outlined text-2xl">account_balance</span>
                    </a>
                    
                    <!-- Emblem Component -->
                    <div class="w-16 h-16 bg-amber-50 border border-amber-100 text-amber-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <span class="material-symbols-outlined text-3xl">timer</span>
                    </div>

                    <!-- Page Header Component -->
                    <div class="auth-page-header">
                        <h1 class="auth-page-title">Session Terminated</h1>
                        <p class="auth-page-subtitle">Your banking session expired due to inactivity.</p>
                    </div>

                    <!-- Alert Warning Component -->
                    <div class="alert alert-warning text-left" role="alert">
                        <span class="material-symbols-outlined alert-icon">warning</span>
                        <div>
                            To protect your financial data, Finzo automatically signs out inactive sessions after 24 hours. Please sign in again to continue.
                        </div>
                    </div>

                    <!-- Buttons Group Component -->
                    <div class="space-y-3 mt-6">
                        <a href="login.php" class="btn btn-primary btn-full">
                            <span class="material-symbols-outlined text-[18px]">login</span>
                            <span>Re-Authenticate Now</span>
                        </a>

                        <a href="/Finzo/public/index.php" class="btn btn-secondary btn-full">
                            <span class="material-symbols-outlined text-[18px]">home</span>
                            <span>Return to Home</span>
                        </a>
                    </div>

                    <!-- Security Notice Component -->
                    <div class="auth-security-notice text-left mt-8">
                        <span class="material-symbols-outlined auth-security-notice-icon">lock</span>
                        <div class="auth-security-notice-text">
                            <strong>Session Security:</strong> Any unsaved form data has been cleared to prevent unauthorized viewing.
                        </div>
                    </div>

                    <!-- Trust Badges Component -->
                    <div class="trust-badge-group">
                        <span class="trust-badge"><span class="material-symbols-outlined">enhanced_encryption</span> 256-bit Encryption</span>
                        <span class="trust-badge"><span class="material-symbols-outlined">gpp_good</span> Automatic Logout</span>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0, viewport-fit=cover" name="viewport" />
    <meta name="description" content="Finzo Enterprise Banking - Staff Portal Sign In" />
    <title>Finzo | Staff Portal</title>
    
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
                <img loading="lazy" src="/Finzo/public/assets/images/staff_bg.png" alt="Staff Portal Illustration" class="auth-hero-img-bg" />
                <img loading="lazy" src="/Finzo/public/assets/images/staff_bg.png" alt="Staff Portal Illustration" class="auth-hero-img-secondary" />
                <div class="auth-hero-overlay"></div>
                
                <div class="auth-hero-content">
                    <div>
                        <a href="/Finzo/public/index.php" class="auth-brand-logo">
                            <div class="auth-brand-icon-box">
                                <span class="material-symbols-outlined text-2xl">account_balance</span>
                            </div>
                            <span class="auth-brand-name">Finzo</span>
                        </a>
                        <h2 class="auth-hero-headline">Staff portal access.</h2>
                        <p class="auth-hero-subtitle">Sign in to manage customer accounts, process transactions, and access the tools you need to serve customers effectively.</p>
                    </div>
                    
                    <div>
                        <div class="auth-hero-security-card">
                            <span class="material-symbols-outlined auth-hero-security-icon">badge</span>
                            <div>
                                <h4 class="auth-hero-security-title">All actions are logged</h4>
                                <p class="auth-hero-security-desc">Staff activity is recorded and audited to ensure compliance with bank policies and regulatory requirements.</p>
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
                    <a href="/Finzo/public/index.php" class="auth-mobile-logo" aria-label="Finzo Homepage">
                        <span class="material-symbols-outlined text-2xl">account_balance</span>
                    </a>
                    
                    <!-- Page Header Component -->
                    <div class="auth-page-header">
                        <h1 class="auth-page-title">Staff Portal</h1>
                        <p class="auth-page-subtitle">Internal portal for authorized bank personnel.</p>
                    </div>

                    <!-- Role Switcher Component -->
                    <div class="auth-role-switcher" role="tablist" aria-label="Select Authentication Role">
                        <a href="login.php" class="auth-role-tab" role="tab" aria-selected="false">Customer</a>
                        <a href="staff-login.php" class="auth-role-tab active" role="tab" aria-selected="true">Staff</a>
                        <a href="admin-login.php" class="auth-role-tab" role="tab" aria-selected="false">Admin</a>
                    </div>

                    <!-- Staff Form Component -->
                    <form data-action="staff-login" method="POST" novalidate>
                        <input type="hidden" name="csrf_token" value="" />
                        
                        <!-- Staff ID Input Component -->
                        <div class="fin-input-container">
                            <label for="staff-id" class="fin-label fin-label-required">Staff ID or Email</label>
                            <div class="fin-input-wrapper">
                                <span class="material-symbols-outlined input-icon-left">badge</span>
                                <input type="text" id="staff-id" name="identity" class="fin-input has-icon-left" placeholder="e.g. STF-84920 or email" required autocomplete="username" />
                            </div>
                            <span class="fin-error-text" role="alert">
                                <span class="material-symbols-outlined">error</span> Staff ID or email is required.
                            </span>
                        </div>

                        <!-- Password Input Component -->
                        <div class="fin-input-container">
                            <div class="flex justify-between items-center mb-1">
                                <label for="staff-password" class="fin-label fin-label-required">Password</label>
                                <a href="forgot-password.php" class="btn-text">Reset Password</a>
                            </div>
                            <div class="fin-input-wrapper">
                                <span class="material-symbols-outlined input-icon-left">lock</span>
                                <input type="password" id="staff-password" name="password" class="fin-input has-icon-left has-icon-right" placeholder="Enter password" required autocomplete="current-password" />
                                <button type="button" class="password-toggle-btn" aria-label="Toggle password visibility" tabindex="0">
                                    <span class="material-symbols-outlined">visibility</span>
                                </button>
                            </div>
                            <span class="fin-error-text" role="alert">
                                <span class="material-symbols-outlined">error</span> Password is required.
                            </span>
                        </div>

                        <!-- Submit Button Component -->
                        <button type="submit" class="btn btn-primary btn-full mt-2">
                            <span>Access Dashboard</span>
                            <span class="material-symbols-outlined text-[18px]">login</span>
                        </button>
                    </form>

                    <!-- Security Notice Component -->
                    <div class="auth-security-notice">
                        <span class="material-symbols-outlined auth-security-notice-icon">lock</span>
                        <div class="auth-security-notice-text">
                            <strong>Internal Operations:</strong> Access restricted to authorized Finzo personnel. Unauthorized access attempts are monitored and reported.
                        </div>
                    </div>

                    <!-- Trust Badges Component -->
                    <div class="trust-badge-group">
                        <span class="trust-badge"><span class="material-symbols-outlined">enhanced_encryption</span> Encrypted Connection</span>
                        <span class="trust-badge"><span class="material-symbols-outlined">verified_user</span> Dual-Factor MFA</span>
                    </div>
                </div>
            </div>
        </div>
    </main>
<script defer src="/Finzo/public/assets/js/auth/staff-login.js"></script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0, viewport-fit=cover" name="viewport" />
    <meta name="description" content="Finzo Enterprise Banking - Verification Link Expired" />
    <title>Finzo | Link Expired</title>
    
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
                <img loading="lazy" src="/Finzo/public/assets/images/landing-dashboard.jpg" alt="Link Expiration Illustration" class="auth-hero-img-bg" />
                <img loading="lazy" src="/Finzo/public/assets/images/landing-dashboard.jpg" alt="Link Expiration Illustration" class="auth-hero-img-secondary" />
                <div class="auth-hero-overlay"></div>
                
                <div class="auth-hero-content">
                    <div>
                        <a href="/Finzo/public/index.php" class="auth-brand-logo">
                            <div class="auth-brand-icon-box">
                                <span class="material-symbols-outlined text-2xl">account_balance</span>
                            </div>
                            <span class="auth-brand-name">Finzo</span>
                        </a>
                        <h2 class="auth-hero-headline">This link has expired.</h2>
                        <p class="auth-hero-subtitle">Verification links are valid for a limited time to keep your account secure. Request a new one to continue.</p>
                    </div>
                    
                    <div>
                        <div class="auth-hero-security-card">
                            <span class="material-symbols-outlined auth-hero-security-icon">hourglass_disabled</span>
                            <div>
                                <h4 class="auth-hero-security-title">Links can only be used once</h4>
                                <p class="auth-hero-security-desc">Each verification link expires after use or after 30 minutes, whichever comes first. You can always request a fresh link.</p>
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
                    <a href="/Finzo/public/index.php" class="auth-mobile-logo" aria-label="Finzo Homepage">
                        <span class="material-symbols-outlined text-2xl">account_balance</span>
                    </a>
                    
                    <!-- Emblem Component -->
                    <div class="w-16 h-16 bg-red-50 border border-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <span class="material-symbols-outlined text-3xl">error</span>
                    </div>

                    <!-- Page Header Component -->
                    <div class="auth-page-header">
                        <h1 class="auth-page-title">Link Expired</h1>
                        <p class="auth-page-subtitle">Security token has timed out or is invalid.</p>
                    </div>

                    <!-- Alert Danger Component -->
                    <div class="alert alert-danger text-left" role="alert">
                        <span class="material-symbols-outlined alert-icon">error</span>
                        <div>
                            For safety, verification links expire 30 minutes after issuance. Please request a fresh verification link.
                        </div>
                    </div>

                    <!-- Buttons Group Component -->
                    <div class="space-y-3 mt-6">
                        <a href="forgot-password.php" class="btn btn-primary btn-full">
                            <span class="material-symbols-outlined text-[18px]">refresh</span>
                            <span>Request New Link</span>
                        </a>

                        <a href="login.php" class="btn btn-secondary btn-full">
                            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                            <span>Return to Login</span>
                        </a>
                    </div>

                    <!-- Security Notice Component -->
                    <div class="auth-security-notice text-left mt-8">
                        <span class="material-symbols-outlined auth-security-notice-icon">shield</span>
                        <div class="auth-security-notice-text">
                            <strong>Need help?</strong> If you keep experiencing issues, please contact our support team for assistance.
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
                        <a href="/Finzo/public/index.php" class="auth-brand-logo">
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
                    <a href="/Finzo/public/index.php" class="auth-mobile-logo" aria-label="Finzo Homepage">
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
