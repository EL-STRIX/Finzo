<?php
$portal = 'auth';
$pageTitle = 'Finzo';
ob_start();
?>
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
                    <span class="material-symbols-outlined text-base">language</span>
                    <span>EN</span>
                    <span class="material-symbols-outlined text-base">expand_more</span>
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
                            <span class="material-symbols-outlined text-lg">verified_user</span>
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
<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>