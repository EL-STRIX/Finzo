<?php
$pageTitle = 'Finzo';
$authBgImage = 'staff_bg.png';
$authHeroTitle = 'Staff portal access.';
$authHeroSubtitle = 'Sign in to manage customer accounts, process transactions, and access the tools you need to serve customers effectively.';
$authSecurityIcon = 'badge';
$authSecurityTitle = 'All actions are logged';
$authSecurityDesc = 'Staff activity is recorded and audited to ensure compliance with bank policies and regulatory requirements.';
ob_start();
?>
<div class="mb-8 text-center lg:text-left">
                        <h1 class="text-3xl font-bold font-display text-text-main mb-2 tracking-tight">Staff Portal</h1>
                        <p class="text-sm text-text-muted leading-relaxed">Internal portal for authorized bank personnel.</p>
                    </div>

                    <!-- Role Switcher Component -->
                    <div class="flex bg-surface-subtle p-1 rounded-lg mb-8 border border-border-main" role="tablist" aria-label="Select Authentication Role">
                        <a href="login.php" class="flex-1 text-center py-2 text-sm font-medium text-text-muted hover:text-text-main transition-colors" role="tab" aria-selected="false">Customer</a>
                        <a href="staff-login.php" class="flex-1 text-center py-2 text-sm font-semibold rounded-md bg-surface-main text-primary-base shadow-sm border border-border-subtle" role="tab" aria-selected="true">Staff</a>
                        <a href="admin-login.php" class="flex-1 text-center py-2 text-sm font-medium text-text-muted hover:text-text-main transition-colors" role="tab" aria-selected="false">Admin</a>
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
                                <a href="forgot-password.php" class="btn-text">Reset Password</a>
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
                            <span class="material-symbols-outlined text-lg">login</span>
                        </button>
                    </form>

                    <!-- Security Notice Component -->
                    <div class="flex items-start gap-3 bg-surface-subtle border border-border-subtle rounded-xl p-4 mt-8">
                        <span class="material-symbols-outlined text-primary-base text-xl flex-shrink-0 mt-0.5">lock</span>
                        <div class="text-xs text-text-muted leading-relaxed">
                            <strong>Internal Operations:</strong> Access restricted to authorized Finzo personnel. Unauthorized access attempts are monitored and reported.
                        </div>
                    </div>

                    <!-- Trust Badges Component -->
                    <div class="flex flex-wrap items-center justify-center gap-4 mt-8 pt-6 border-t border-border-subtle">
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-text-muted"><span class="material-symbols-outlined">enhanced_encryption</span> Encrypted Connection</span>
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-text-muted"><span class="material-symbols-outlined">verified_user</span> Dual-Factor MFA</span>
                    </div>
<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/auth-layout.php';
?>