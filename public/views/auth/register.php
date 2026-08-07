<?php
$pageTitle = "Finzo | Create Account";
$pageDescription = "Finzo Enterprise Banking - Establish Account Application";
$extraHead = '<script src="/Finzo/public/assets/js/pages/auth/auth-components.js" defer></script>';
ob_start();
?>

    <main class="auth-page-wrapper">
        <div class="auth-split-grid">
            <!-- Left Side: Hero Illustration & Security Banner -->
            <div class="auth-left-panel">
                <img loading="lazy" src="/Finzo/public/assets/images/register-page-image.png" alt="Registration Illustration" class="auth-hero-img-bg" />
                <img loading="lazy" src="/Finzo/public/assets/images/register-page-image.png" alt="Registration Illustration" class="auth-hero-img-secondary" />
                <div class="auth-hero-overlay"></div>
                
                <div class="auth-hero-content">
                    <div>
                        <a href="/Finzo/public/index.html" class="auth-brand-logo">
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
                    <span class="material-symbols-outlined text-base">language</span>
                    <span>EN</span>
                    <span class="material-symbols-outlined text-base">expand_more</span>
                </div>
                
                <div class="auth-card-container">
                    <!-- Mobile Logo -->
                    <a href="/Finzo/public/index.html" class="auth-mobile-logo" aria-label="Finzo Homepage">
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
                            <span class="material-symbols-outlined text-lg">arrow_forward</span>
                        </button>
                    </form>

                    <!-- Page Footer Link -->
                    <div class="mt-6 text-center text-sm text-gray-600 border-t border-gray-100 pt-6">
                        Already have an account? <a href="login.html" class="font-semibold text-primary hover:underline">Sign In</a>
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

<?php
$slot = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
