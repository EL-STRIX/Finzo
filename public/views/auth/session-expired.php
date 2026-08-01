<?php
$pageTitle = "Finzo | Session Terminated";
$pageDescription = "Finzo Enterprise Banking - Inactivity Session Timeout Notice";
$extraHead = '<script src="/Finzo/public/assets/js/pages/auth/auth-components.js" defer></script>';
ob_start();
?>

    <main class="auth-page-wrapper">
        <div class="auth-split-grid">
            <!-- Left Side: Hero Illustration & Security Banner -->
            <div class="auth-left-panel">
                <img loading="lazy" src="/Finzo/public/assets/images/landing-dashboard.jpg" alt="Session Protection Illustration" class="auth-hero-img-bg" />
                <img loading="lazy" src="/Finzo/public/assets/images/landing-dashboard.jpg" alt="Session Protection Illustration" class="auth-hero-img-secondary" />
                <div class="auth-hero-overlay"></div>
                
                <div class="auth-hero-content">
                    <div>
                        <a href="/Finzo/public/index.html" class="auth-brand-logo">
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
                    <a href="/Finzo/public/index.html" class="auth-mobile-logo" aria-label="Finzo Homepage">
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
                        <a href="login.html" class="btn btn-primary btn-full">
                            <span class="material-symbols-outlined text-[18px]">login</span>
                            <span>Re-Authenticate Now</span>
                        </a>

                        <a href="/Finzo/public/index.html" class="btn btn-secondary btn-full">
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

<?php
$slot = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
