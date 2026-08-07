<?php
$pageTitle = 'Finzo';
$authBgImage = 'customer_bg.png';
$authHeroTitle = 'Open your account in minutes.';
$authHeroSubtitle = 'Create a Finzo account to manage your finances, make transfers, and access secure banking services from anywhere.';
$authSecurityIcon = 'encrypted';
$authSecurityTitle = 'Your data is protected';
$authSecurityDesc = 'All personal and financial information you submit is encrypted in transit and never shared with unauthorized parties.';
ob_start();
?>
<div class="mb-8 text-center lg:text-left">
                        <h1 class="text-3xl font-bold font-display text-text-main mb-2 tracking-tight">Create Account</h1>
                        <p class="text-sm text-text-muted leading-relaxed">Fill in the details below to open your Finzo account.</p>
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
                                <span class="material-symbols-outlined input-icon-left" aria-hidden="true">person</span>
                                <input type="text" id="full-name" name="full_name" class="fin-input has-icon-left" placeholder="As stated on government ID" required autocomplete="name" />
                            </div>
                            <span class="fin-error-text" role="alert">
                                <span class="material-symbols-outlined" aria-hidden="true">error</span> Full name is required.
                            </span>
                        </div>

                        <!-- Section 2: Contact Details -->
                        <div class="mb-4 border-b border-gray-100 pb-2 mt-6">
                            <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider">2. Contact Details</h3>
                        </div>

                        <div class="fin-input-container">
                            <label for="reg-email" class="fin-label fin-label-required">Email Address</label>
                            <div class="fin-input-wrapper">
                                <span class="material-symbols-outlined input-icon-left" aria-hidden="true">mail</span>
                                <input type="email" id="reg-email" name="email" class="fin-input has-icon-left" placeholder="name@example.com" required autocomplete="email" />
                            </div>
                            <span class="fin-error-text" role="alert">
                                <span class="material-symbols-outlined" aria-hidden="true">error</span> Valid email address is required.
                            </span>
                        </div>

                        <div class="fin-input-container">
                            <label for="mobile-number" class="fin-label fin-label-required">Mobile Number</label>
                            <div class="fin-input-wrapper">
                                <span class="material-symbols-outlined input-icon-left" aria-hidden="true">call</span>
                                <input type="tel" id="mobile-number" name="phone" class="fin-input has-icon-left" placeholder="+91 98765 43210" required autocomplete="tel" />
                            </div>
                            <span class="fin-error-text" role="alert">
                                <span class="material-symbols-outlined" aria-hidden="true">error</span> Mobile number is required.
                            </span>
                        </div>

                        <!-- Section 3: Password & Security -->
                        <div class="mb-4 border-b border-gray-100 pb-2 mt-6">
                            <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider">3. Security Credentials</h3>
                        </div>

                        <div class="fin-input-container">
                            <label for="create-password" class="fin-label fin-label-required">Create Password</label>
                            <div class="fin-input-wrapper">
                                <span class="material-symbols-outlined input-icon-left" aria-hidden="true">lock</span>
                                <input type="password" id="create-password" name="password" class="fin-input has-icon-left has-icon-right" placeholder="Minimum 8 characters" required autocomplete="new-password" data-strength-meter="true" />
                                <button type="button" class="password-toggle-btn" aria-label="Toggle password visibility" tabindex="0">
                                    <span class="material-symbols-outlined" aria-hidden="true">visibility</span>
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
                                    <span class="req-item" data-req="length"><span class="material-symbols-outlined" aria-hidden="true">cancel</span> 8+ Characters</span>
                                    <span class="req-item" data-req="upper"><span class="material-symbols-outlined" aria-hidden="true">cancel</span> Uppercase Letter</span>
                                    <span class="req-item" data-req="number"><span class="material-symbols-outlined" aria-hidden="true">cancel</span> Number (0-9)</span>
                                    <span class="req-item" data-req="special"><span class="material-symbols-outlined" aria-hidden="true">cancel</span> Symbol (!@#$)</span>
                                </div>
                            </div>
                            <span class="fin-error-text" role="alert">
                                <span class="material-symbols-outlined" aria-hidden="true">error</span> Password is required.
                            </span>
                        </div>

                        <div class="fin-input-container">
                            <label for="reg-confirm-password" class="fin-label fin-label-required">Confirm Password</label>
                            <div class="fin-input-wrapper">
                                <span class="material-symbols-outlined input-icon-left" aria-hidden="true">check_circle</span>
                                <input type="password" id="reg-confirm-password" name="confirm_password" class="fin-input has-icon-left has-icon-right" placeholder="Confirm password" required autocomplete="new-password" />
                                <button type="button" class="password-toggle-btn" aria-label="Toggle password visibility" tabindex="0">
                                    <span class="material-symbols-outlined" aria-hidden="true">visibility</span>
                                </button>
                            </div>
                            <span class="fin-error-text" role="alert">
                                <span class="material-symbols-outlined" aria-hidden="true">error</span> Passwords must match.
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
                                <span class="material-symbols-outlined" aria-hidden="true">error</span> You must accept the Terms and Privacy Policy.
                            </span>
                        </div>

                        <!-- Submit Button Component -->
                        <button type="submit" class="btn btn-primary btn-full">
                            <span>Submit Application</span>
                            <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_forward</span>
                        </button>
                    </form>

                    <!-- Page Footer Link -->
                    <div class="mt-6 text-center text-sm text-gray-600 border-t border-gray-100 pt-6">
                        Already have an account? <a href="login.php" class="font-semibold text-primary hover:underline">Sign In</a>
                    </div>

                    <!-- Security Notice Component -->
                    <div class="flex items-start gap-3 bg-surface-subtle border border-border-subtle rounded-xl p-4 mt-8">
                        <span class="material-symbols-outlined text-primary-base text-xl flex-shrink-0 mt-0.5" aria-hidden="true">verified</span>
                        <div class="text-xs text-text-muted leading-relaxed">
                            <strong>Identity Verification:</strong> We will verify your credentials using official records. Your information is never sold or shared with unauthorized third parties.
                        </div>
                    </div>

                    <!-- Trust Badges Component -->
                    <div class="flex flex-wrap items-center justify-center gap-4 mt-8 pt-6 border-t border-border-subtle">
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-text-muted"><span class="material-symbols-outlined" aria-hidden="true">enhanced_encryption</span> 256-bit Encryption</span>
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-text-muted"><span class="material-symbols-outlined" aria-hidden="true">gpp_good</span> Privacy Protected</span>
                    </div>
<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/auth-layout.php';
?>