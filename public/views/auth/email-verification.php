<?php
$pageTitle = "Finzo | Check Your Email";
$pageDescription = "Finzo Enterprise Banking - Email Verification Sent";
$extraHead = '<script src="/Finzo/public/assets/js/pages/auth/auth-components.js" defer></script>';
ob_start();
?>

    <main class="auth-page-wrapper">
        <div class="auth-split-grid">
            <!-- Left Side: Hero Illustration & Security Banner -->
            <div class="auth-left-panel">
                <img loading="lazy" src="/Finzo/public/assets/images/customer_bg.png" alt="Identity Confirmation Illustration" class="auth-hero-img-bg" />
                <img loading="lazy" src="/Finzo/public/assets/images/customer_bg.png" alt="Identity Confirmation Illustration" class="auth-hero-img-secondary" />
                <div class="auth-hero-overlay"></div>
                
                <div class="auth-hero-content">
                    <div>
                        <a href="/Finzo/public/index.html" class="auth-brand-logo">
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
                    <a href="/Finzo/public/index.html" class="auth-mobile-logo" aria-label="Finzo Homepage">
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
                        <a href="login.html" class="btn btn-primary btn-full">
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

<?php
$slot = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
