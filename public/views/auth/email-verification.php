<?php
$pageTitle = 'Finzo';
$authBgImage = 'customer_bg.png';
$authHeroTitle = 'Verify your email address.';
$authHeroSubtitle = 'We need to confirm your email before activating your account. Check your inbox for the verification link we sent.';
$authSecurityIcon = 'mark_email_read';
$authSecurityTitle = 'Secure verification link';
$authSecurityDesc = 'The link in your email is valid for 30 minutes and can only be used once. After it expires, you can request a new one.';
ob_start();
?>
<div class="mb-8 text-center lg:text-left">
                        <h1 class="text-3xl font-bold font-display text-text-main mb-2 tracking-tight">Check Your Email</h1>
                        <p class="text-sm text-text-muted leading-relaxed">We sent a secure verification link to your registered email address.</p>
                    </div>

                    <!-- Alert Info Component -->
                    <div class="flex items-start gap-3 p-4 rounded-md mb-6 text-sm bg-info-surface border border-info-surface text-info-text text-left" role="status">
                        <span class="material-symbols-outlined text-xl flex-shrink-0 mt-0.5" aria-hidden="true">info</span>
                        <div>
                            Click the secure link inside the email to complete your registration. The link will expire in 30 minutes.
                        </div>
                    </div>

                    <!-- Buttons Group Component -->
                    <div class="space-y-3 mt-6">
                        <a href="login.php" class="btn btn-primary btn-full">
                            <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_back</span>
                            <span>Return to Sign In</span>
                        </a>

                        <button type="button" class="btn btn-secondary btn-full" data-action="resend-email-verification">
                            <span class="material-symbols-outlined text-lg" aria-hidden="true">refresh</span>
                            <span>Resend Verification Email</span>
                        </button>
                    </div>

                    <!-- Security Notice Component -->
                    <div class="flex items-start gap-3 bg-surface-subtle border border-border-subtle rounded-xl p-4 mt-8 text-left mt-8">
                        <span class="material-symbols-outlined text-primary-base text-xl flex-shrink-0 mt-0.5" aria-hidden="true">forward_to_inbox</span>
                        <div class="text-xs text-text-muted leading-relaxed">
                            <strong>Tip:</strong> If you don't see the email within a few minutes, please check your spam or junk folder.
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