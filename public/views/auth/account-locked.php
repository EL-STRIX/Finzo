<?php
$pageTitle = "Finzo | Account Locked";
$pageDescription = "Finzo Enterprise Banking - Security Account Lockout Notice";
$extraHead = '<script src="/Finzo/public/assets/js/pages/auth/auth-components.js" defer></script>';
ob_start();
?>

    <main class="auth-page-wrapper">
        <div class="auth-split-grid">
            <!-- Left Side: Hero Illustration & Security Banner -->
            <div class="auth-left-panel">
                <img loading="lazy" src="/Finzo/public/assets/images/landing-dashboard.jpg" alt="Automated Security Illustration" class="auth-hero-img-bg" />
                <img loading="lazy" src="/Finzo/public/assets/images/landing-dashboard.jpg" alt="Automated Security Illustration" class="auth-hero-img-secondary" />
                <div class="auth-hero-overlay"></div>
                
                <div class="auth-hero-content">
                    <div>
                        <a href="/Finzo/public/index.html" class="auth-brand-logo">
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
                    <a href="/Finzo/public/index.html" class="auth-mobile-logo" aria-label="Finzo Homepage">
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
                        <a href="/Finzo/public/index.html" class="btn btn-primary btn-full">
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

<?php
$slot = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
