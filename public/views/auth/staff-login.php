<?php
$pageTitle = "Finzo | Staff Portal";
$pageDescription = "Finzo Enterprise Banking - Staff Portal Sign In";
$extraHead = '<script src="/Finzo/public/assets/js/pages/auth/auth-components.js" defer></script>';
ob_start();
?>

    <main class="auth-page-wrapper">
        <div class="auth-split-grid">
            <!-- Left Side: Hero Illustration & Security Banner -->
            <div class="auth-left-panel">
                <img loading="lazy" src="/Finzo/public/assets/images/staff_bg.png" alt="Staff Portal Illustration" class="auth-hero-img-bg" />
                <img loading="lazy" src="/Finzo/public/assets/images/staff_bg.png" alt="Staff Portal Illustration" class="auth-hero-img-secondary" />
                <div class="auth-hero-overlay"></div>
                
                <div class="auth-hero-content">
                    <div>
                        <a href="/Finzo/public/index.html" class="auth-brand-logo">
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
                    <a href="/Finzo/public/index.html" class="auth-mobile-logo" aria-label="Finzo Homepage">
                        <span class="material-symbols-outlined text-2xl">account_balance</span>
                    </a>
                    
                    <!-- Page Header Component -->
                    <div class="auth-page-header">
                        <h1 class="auth-page-title">Staff Portal</h1>
                        <p class="auth-page-subtitle">Internal portal for authorized bank personnel.</p>
                    </div>

                    <!-- Role Switcher Component -->
                    <div class="auth-role-switcher" role="tablist" aria-label="Select Authentication Role">
                        <a href="login.html" class="auth-role-tab" role="tab" aria-selected="false">Customer</a>
                        <a href="staff-login.html" class="auth-role-tab active" role="tab" aria-selected="true">Staff</a>
                        <a href="admin-login.html" class="auth-role-tab" role="tab" aria-selected="false">Admin</a>
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
                                <a href="forgot-password.html" class="btn-text">Reset Password</a>
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

<?php
$slot = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
