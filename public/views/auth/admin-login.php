<?php
$pageTitle = "Finzo | Admin Authentication";
$pageDescription = "Finzo Enterprise Banking - Administrator Terminal Authentication";
$extraHead = '<script src="/Finzo/public/assets/js/pages/auth/auth-components.js" defer></script>';
ob_start();
?>

    <main class="auth-page-wrapper">
        <div class="auth-split-grid">
            <!-- Left Side: Hero Illustration & Security Banner -->
            <div class="auth-left-panel">
                <img loading="lazy" src="/Finzo/public/assets/images/admin_bg.png" alt="Admin Terminal Illustration" class="auth-hero-img-bg" />
                <img loading="lazy" src="/Finzo/public/assets/images/admin_bg.png" alt="Admin Terminal Illustration" class="auth-hero-img-secondary" />
                <div class="auth-hero-overlay"></div>
                
                <div class="auth-hero-content">
                    <div>
                        <a href="/Finzo/public/index.html" class="auth-brand-logo">
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
                    <a href="/Finzo/public/index.html" class="auth-mobile-logo" aria-label="Finzo Homepage">
                        <span class="material-symbols-outlined text-2xl">account_balance</span>
                    </a>
                    
                    <!-- Page Header Component -->
                    <div class="auth-page-header">
                        <h1 class="auth-page-title">Admin Authentication</h1>
                        <p class="auth-page-subtitle">Strictly authorized administrative personnel only.</p>
                    </div>

                    <!-- Role Switcher Component -->
                    <div class="auth-role-switcher" role="tablist" aria-label="Select Authentication Role">
                        <a href="login.html" class="auth-role-tab" role="tab" aria-selected="false">Customer</a>
                        <a href="staff-login.html" class="auth-role-tab" role="tab" aria-selected="false">Staff</a>
                        <a href="admin-login.html" class="auth-role-tab active" role="tab" aria-selected="true">Admin</a>
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
                                <input type="email" id="admin-id" name="email" class="fin-input has-icon-left" placeholder="admin@finzo.com" required autocomplete="username" />
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

<?php
$slot = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
