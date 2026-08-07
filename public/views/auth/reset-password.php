<?php
$pageTitle = 'Finzo';
$authBgImage = 'customer_bg.png';
$authHeroTitle = 'Set a new password.';
$authHeroSubtitle = 'Choose a strong password to protect your account and keep your banking information secure.';
$authSecurityIcon = 'published_with_changes';
$authSecurityTitle = 'All other sessions will be signed out';
$authSecurityDesc = 'When you change your password, any active sessions on other devices will be ended for your protection.';
ob_start();
?>
<div class="mb-8 text-center lg:text-left">
                        <h1 class="text-3xl font-bold font-display text-text-main mb-2 tracking-tight">Set New Password</h1>
                        <p class="text-sm text-text-muted leading-relaxed">Your new password must meet the minimum security requirements.</p>
                    </div>

                    <!-- Reset Password Form Component -->
                    <form data-action="reset-password" method="POST" novalidate>
                        <input type="hidden" name="csrf_token" value="" />
                        <input type="hidden" name="token" value="" />
                        
                        <!-- New Password Input Component -->
                        <div class="fin-input-container">
                            <label for="new-password" class="fin-label fin-label-required">New Password</label>
                            <div class="fin-input-wrapper">
                                <span class="material-symbols-outlined input-icon-left" aria-hidden="true">lock</span>
                                <input type="password" id="new-password" name="new_password" class="fin-input has-icon-left has-icon-right" placeholder="Enter new password" required autocomplete="new-password" data-strength-meter="true" />
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
                                <span class="material-symbols-outlined" aria-hidden="true">error</span> New password is required.
                            </span>
                        </div>

                        <!-- Confirm Password Input Component -->
                        <div class="fin-input-container">
                            <label for="confirm-password" class="fin-label fin-label-required">Confirm New Password</label>
                            <div class="fin-input-wrapper">
                                <span class="material-symbols-outlined input-icon-left" aria-hidden="true">check_circle</span>
                                <input type="password" id="confirm-password" name="confirm_password" class="fin-input has-icon-left has-icon-right" placeholder="Confirm new password" required autocomplete="new-password" />
                                <button type="button" class="password-toggle-btn" aria-label="Toggle password visibility" tabindex="0">
                                    <span class="material-symbols-outlined" aria-hidden="true">visibility</span>
                                </button>
                            </div>
                            <span class="fin-error-text" role="alert">
                                <span class="material-symbols-outlined" aria-hidden="true">error</span> Passwords must match.
                            </span>
                        </div>

                        <!-- Submit Button Component -->
                        <button type="submit" class="btn btn-primary btn-full mt-2">
                            <span>Update Password</span>
                            <span class="material-symbols-outlined text-lg" aria-hidden="true">verified</span>
                        </button>
                    </form>

                    <!-- Security Notice Component -->
                    <div class="flex items-start gap-3 bg-surface-subtle border border-border-subtle rounded-xl p-4 mt-8">
                        <span class="material-symbols-outlined text-primary-base text-xl flex-shrink-0 mt-0.5" aria-hidden="true">shield</span>
                        <div class="text-xs text-text-muted leading-relaxed">
                            <strong>Tip:</strong> Use a unique password that you don't use on other websites or apps.
                        </div>
                    </div>

                    <!-- Trust Badges Component -->
                    <div class="flex flex-wrap items-center justify-center gap-4 mt-8 pt-6 border-t border-border-subtle">
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-text-muted"><span class="material-symbols-outlined" aria-hidden="true">enhanced_encryption</span> 256-bit SSL</span>
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-text-muted"><span class="material-symbols-outlined" aria-hidden="true">gpp_good</span> Multi-Layer Security</span>
                    </div>
<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/auth-layout.php';
?>