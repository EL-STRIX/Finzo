<?php
$pageTitle = 'Finzo';
$authBgImage = 'customer_bg.png';
$authHeroTitle = 'Secure online banking, built for you.';
$authHeroSubtitle = 'Sign in to access your accounts, manage transactions, and stay in control of your finances — securely and reliably.';
$authSecurityIcon = 'verified_user';
$authSecurityTitle = 'Your session is protected';
$authSecurityDesc = 'Every login is secured with 256-bit encryption and monitored for unauthorized access attempts.';
ob_start();
?>
<div class="mb-8 text-center lg:text-left">
                        <h1 class="text-3xl font-bold font-display text-text-main mb-2 tracking-tight">Welcome Back</h1>
                        <p class="text-sm text-text-muted leading-relaxed">Sign in to manage your enterprise accounts securely.</p>
                    </div>

                    <!-- Role Switcher Component -->
                    <div class="flex bg-surface-subtle p-1 rounded-lg mb-8 border border-border-main" role="tablist" aria-label="Select Authentication Role">
                        <a href="login.php" class="flex-1 text-center py-2 text-sm font-semibold rounded-md bg-surface-main text-primary-base shadow-sm border border-border-subtle" role="tab" aria-selected="true">Customer</a>
                        <a href="staff-login.php" class="flex-1 text-center py-2 text-sm font-medium text-text-muted hover:text-text-main transition-colors" role="tab" aria-selected="false">Staff</a>
                        <a href="admin-login.php" class="flex-1 text-center py-2 text-sm font-medium text-text-muted hover:text-text-main transition-colors" role="tab" aria-selected="false">Admin</a>
                    </div>

                    <!-- Login Form Component -->
                    <form data-action="customer-login" method="POST" novalidate>
                        <input type="hidden" name="csrf_token" value="" />
                        
                        <!-- Customer ID / Identity Input Component -->
                        <div class="fin-input-container">
                            <label for="customer-id" class="fin-label fin-label-required">Customer ID or Email</label>
                            <div class="fin-input-wrapper">
                                <span class="material-symbols-outlined input-icon-left" aria-hidden="true">person</span>
                                <input type="text" id="customer-id" name="identity" class="fin-input has-icon-left" placeholder="Enter Customer ID or email" required autocomplete="username" />
                            </div>
                            <span class="fin-error-text" role="alert">
                                <span class="material-symbols-outlined" aria-hidden="true">error</span> Customer ID or email is required.
                            </span>
                        </div>

                        <!-- Password Input Component -->
                        <div class="fin-input-container">
                            <div class="flex justify-between items-center mb-1">
                                <label for="login-password" class="fin-label fin-label-required">Password</label>
                                <a href="forgot-password.php" class="btn-text">Forgot password?</a>
                            </div>
                            <div class="fin-input-wrapper">
                                <span class="material-symbols-outlined input-icon-left" aria-hidden="true">lock</span>
                                <input type="password" id="login-password" name="password" class="fin-input has-icon-left has-icon-right" placeholder="Enter password" required autocomplete="current-password" />
                                <button type="button" class="password-toggle-btn" aria-label="Toggle password visibility" tabindex="0">
                                    <span class="material-symbols-outlined" aria-hidden="true">visibility</span>
                                </button>
                            </div>
                            <span class="fin-error-text" role="alert">
                                <span class="material-symbols-outlined" aria-hidden="true">error</span> Password is required.
                            </span>
                        </div>

                        <!-- Remember Checkbox Component -->
                        <div class="mb-6">
                            <label class="fin-checkbox-wrapper">
                                <input type="checkbox" id="remember-me" name="remember" class="fin-checkbox-input" />
                                <span class="fin-checkbox-label">Remember my ID on this device</span>
                            </label>
                        </div>

                        <!-- Submit Button Component -->
                        <button type="submit" class="btn btn-primary btn-full">
                            <span>Secure Login</span>
                            <span class="material-symbols-outlined text-lg" aria-hidden="true">login</span>
                        </button>
                    </form>

                    <!-- Page Footer Link -->
                    <div class="mt-8 text-center text-sm text-gray-600 border-t border-gray-100 pt-6">
                        Don't have an account? <a href="register.php" class="font-semibold text-primary hover:underline">Open an Account</a>
                    </div>

                    <!-- Security Notice Component -->
                    <div class="flex items-start gap-3 bg-surface-subtle border border-border-subtle rounded-xl p-4 mt-8">
                        <span class="material-symbols-outlined text-primary-base text-xl flex-shrink-0 mt-0.5" aria-hidden="true">shield</span>
                        <div class="text-xs text-text-muted leading-relaxed">
                            <strong>Protected Session:</strong> Never share your online banking credentials or OTP with anyone. Finzo staff will never request your password.
                        </div>
                    </div>

                    <!-- Trust Badges Component -->
                    <div class="flex flex-wrap items-center justify-center gap-4 mt-8 pt-6 border-t border-border-subtle">
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-text-muted"><span class="material-symbols-outlined" aria-hidden="true">enhanced_encryption</span> 256-bit Encryption</span>
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-text-muted"><span class="material-symbols-outlined" aria-hidden="true">gpp_good</span> Privacy Protected</span>
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-text-muted"><span class="material-symbols-outlined" aria-hidden="true">verified</span> Verified Platform</span>
                    </div>
<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/auth-layout.php';
?>