<?php
$portal = 'auth';
$pageTitle = 'Finzo';
ob_start();
?>
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
                    <span class="material-symbols-outlined text-base">language</span>
                    <span>EN</span>
                    <span class="material-symbols-outlined text-base">expand_more</span>
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
                            <span class="material-symbols-outlined text-lg">refresh</span>
                            <span>Request New Link</span>
                        </a>

                        <a href="login.php" class="btn btn-secondary btn-full">
                            <span class="material-symbols-outlined text-lg">arrow_back</span>
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
<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>