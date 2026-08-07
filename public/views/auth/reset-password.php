<?php
$portal = 'auth';
$pageTitle = 'Finzo';
ob_start();
?>
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
                    <span class="material-symbols-outlined text-base">language</span>
                    <span>EN</span>
                    <span class="material-symbols-outlined text-base">expand_more</span>
                </div>
                
                <div class="ds-card-container">
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
                            <span class="material-symbols-outlined text-lg">verified</span>
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
<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>