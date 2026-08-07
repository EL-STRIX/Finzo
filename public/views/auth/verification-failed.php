<?php
$pageTitle = 'Finzo';
$authBgImage = 'customer_bg.png';
$authHeroTitle = 'This link has expired.';
$authHeroSubtitle = 'Verification links are valid for a limited time to keep your account secure. Request a new one to continue.';
$authSecurityIcon = 'hourglass_disabled';
$authSecurityTitle = 'Links can only be used once';
$authSecurityDesc = 'Each verification link expires after use or after 30 minutes, whichever comes first. You can always request a fresh link.';
ob_start();
?>
<div class="mb-8 text-center lg:text-left">
                        <h1 class="text-3xl font-bold font-display text-text-main mb-2 tracking-tight">Link Expired</h1>
                        <p class="text-sm text-text-muted leading-relaxed">Security token has timed out or is invalid.</p>
                    </div>

                    <!-- Alert Danger Component -->
                    <div class="flex items-start gap-3 p-4 rounded-md mb-6 text-sm bg-danger-surface border border-danger-surface text-danger-text text-left" role="alert">
                        <span class="material-symbols-outlined text-xl flex-shrink-0 mt-0.5" aria-hidden="true">error</span>
                        <div>
                            For safety, verification links expire 30 minutes after issuance. Please request a fresh verification link.
                        </div>
                    </div>

                    <!-- Buttons Group Component -->
                    <div class="space-y-3 mt-6">
                        <a href="forgot-password.php" class="btn btn-primary btn-full">
                            <span class="material-symbols-outlined text-lg" aria-hidden="true">refresh</span>
                            <span>Request New Link</span>
                        </a>

                        <a href="login.php" class="btn btn-secondary btn-full">
                            <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_back</span>
                            <span>Return to Login</span>
                        </a>
                    </div>

                    <!-- Security Notice Component -->
                    <div class="flex items-start gap-3 bg-surface-subtle border border-border-subtle rounded-xl p-4 mt-8 text-left mt-8">
                        <span class="material-symbols-outlined text-primary-base text-xl flex-shrink-0 mt-0.5" aria-hidden="true">shield</span>
                        <div class="text-xs text-text-muted leading-relaxed">
                            <strong>Need help?</strong> If you keep experiencing issues, please contact our support team for assistance.
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