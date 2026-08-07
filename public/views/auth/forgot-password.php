<?php
$pageTitle = 'Finzo';
$authBgImage = 'customer_bg.png';
$authHeroTitle = 'Recover access to your account.';
$authHeroSubtitle = 'We\'ll send a secure link to your registered email so you can reset your password and regain access.';
$authSecurityIcon = 'lock_reset';
$authSecurityTitle = 'One-time recovery link';
$authSecurityDesc = 'We send a single-use link to your registered email. It expires in 15 minutes to keep your account safe.';
ob_start();
?>
<div class="mb-8 text-center lg:text-left">
                        <h1 class="text-3xl font-bold font-display text-text-main mb-2 tracking-tight">Account Recovery</h1>
                        <p class="text-sm text-text-muted leading-relaxed">Enter your registered email address to receive a secure recovery link.</p>
                    </div>

                    <!-- Recovery Form Component -->
                    <form data-action="forgot-password" method="POST" novalidate>
                        <input type="hidden" name="csrf_token" value="" />
                        
                        <!-- Email Input Component -->
                        <div class="fin-input-container">
                            <label for="recovery-email" class="fin-label fin-label-required">Registered Email Address</label>
                            <div class="fin-input-wrapper">
                                <span class="material-symbols-outlined input-icon-left" aria-hidden="true">mail</span>
                                <input type="email" id="recovery-email" name="email" class="fin-input has-icon-left" placeholder="name@example.com" required autocomplete="email" />
                            </div>
                            <span class="fin-error-text" role="alert">
                                <span class="material-symbols-outlined" aria-hidden="true">error</span> Valid registered email address is required.
                            </span>
                        </div>

                        <!-- Buttons Group Component -->
                        <div class="space-y-3 mt-6">
                            <button type="submit" class="btn btn-primary btn-full">
                                <span>Send Recovery Link</span>
                                <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_forward</span>
                            </button>
                            
                            <a href="login.php" class="btn btn-secondary btn-full">
                                <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_back</span>
                                <span>Return to Sign In</span>
                            </a>
                        </div>
                    </form>

                    <!-- Security Notice Component -->
                    <div class="flex items-start gap-3 bg-surface-subtle border border-border-subtle rounded-xl p-4 mt-8">
                        <span class="material-symbols-outlined text-primary-base text-xl flex-shrink-0 mt-0.5" aria-hidden="true">mark_email_unread</span>
                        <div class="text-xs text-text-muted leading-relaxed">
                            <strong>Please note:</strong> Recovery links expire in 1 hour. If you did not request a password reset, you can safely ignore this.
                        </div>
                    </div>

                    <!-- Trust Badges Component -->
                    <div class="flex flex-wrap items-center justify-center gap-4 mt-8 pt-6 border-t border-border-subtle">
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-text-muted"><span class="material-symbols-outlined" aria-hidden="true">enhanced_encryption</span> 256-bit Encryption</span>
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-text-muted"><span class="material-symbols-outlined" aria-hidden="true">gpp_good</span> Identity Protected</span>
                    </div>
<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/auth-layout.php';
?>