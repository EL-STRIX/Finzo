<?php
$pageTitle = 'Finzo';
$authBgImage = 'customer_bg.png';
$authHeroTitle = 'Your account is ready.';
$authHeroSubtitle = 'Your email has been verified and your Finzo account is now active. You can sign in and start managing your finances.';
$authSecurityIcon = 'verified';
$authSecurityTitle = 'Verification complete';
$authSecurityDesc = 'Your email has been confirmed and your account is secured. You can now sign in and manage your account.';
ob_start();
?>
<div class="mb-8 text-center lg:text-left">
                        <h1 class="text-3xl font-bold font-display text-text-main mb-2 tracking-tight">Identity Verified</h1>
                        <p class="text-sm text-text-muted leading-relaxed">Your profile is now active and fully secured.</p>
                    </div>

                    <!-- Alert Success Component -->
                    <div class="flex items-start gap-3 p-4 rounded-md mb-6 text-sm bg-success-surface border border-success-surface text-success-text text-left" role="status">
                        <span class="material-symbols-outlined text-xl flex-shrink-0 mt-0.5" aria-hidden="true">task_alt</span>
                        <div>
                            Your Finzo credentials have been verified. You can now log in to access your digital banking workspace.
                        </div>
                    </div>

                    <!-- Button Component -->
                    <div class="mt-6">
                        <a href="login.php" class="btn btn-primary btn-full">
                            <span>Proceed to Login</span>
                            <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_forward</span>
                        </a>
                    </div>

                    <!-- Security Notice Component -->
                    <div class="flex items-start gap-3 bg-surface-subtle border border-border-subtle rounded-xl p-4 mt-8 text-left mt-8">
                        <span class="material-symbols-outlined text-primary-base text-xl flex-shrink-0 mt-0.5" aria-hidden="true">shield</span>
                        <div class="text-xs text-text-muted leading-relaxed">
                            <strong>Security tip:</strong> We recommend enabling two-step verification after your first login for added account protection.
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