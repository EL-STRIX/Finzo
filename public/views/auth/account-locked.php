<?php
$pageTitle = 'Finzo';
$authBgImage = 'customer_bg.png';
$authHeroTitle = 'Your account has been locked.';
$authHeroSubtitle = 'Too many failed login attempts were detected. Your account has been temporarily suspended to prevent unauthorized access.';
$authSecurityIcon = 'gpp_maybe';
$authSecurityTitle = 'Too many failed login attempts';
$authSecurityDesc = 'Accounts are automatically locked after multiple incorrect password attempts to protect against unauthorized access.';
ob_start();
?>
<div class="mb-8 text-center lg:text-left">
                        <h1 class="text-3xl font-bold font-display text-text-main mb-2 tracking-tight">Account Locked</h1>
                        <p class="text-sm text-text-muted leading-relaxed">Access temporarily suspended for security reasons.</p>
                    </div>

                    <!-- Alert Danger Component -->
                    <div class="flex items-start gap-3 p-4 rounded-md mb-6 text-sm bg-danger-surface border border-danger-surface text-danger-text text-left" role="alert">
                        <span class="material-symbols-outlined text-xl flex-shrink-0 mt-0.5" aria-hidden="true">error</span>
                        <div>
                            Too many consecutive failed login attempts. Access to this account has been suspended for <strong>30 minutes</strong>.
                        </div>
                    </div>

                    <!-- Contact Support Block -->
                    <div class="bg-surface-subtle border border-border-subtle rounded-xl p-5 text-left mt-6">
                        <p class="text-xs font-semibold uppercase text-text-muted mb-4 tracking-wider">Need immediate assistance?</p>
                        <div class="flex flex-col gap-4">
                            <a href="tel:+18005550199" class="inline-flex items-center gap-3 text-sm font-medium text-text-main hover:text-primary-base transition-colors group">
                                <span class="flex items-center justify-center w-8 h-8 rounded-md bg-surface-main border border-border-main text-primary-base transition-colors group-hover:bg-primary-base group-hover:text-white group-hover:border-primary-base">
                                    <span class="material-symbols-outlined" aria-hidden="true">phone</span>
                                </span>
                                <span>24/7 Fraud &amp; Security Hotline: +1-800-555-0199</span>
                            </a>
                            <a href="mailto:support@finzo.com" class="inline-flex items-center gap-3 text-sm font-medium text-text-main hover:text-primary-base transition-colors group">
                                <span class="flex items-center justify-center w-8 h-8 rounded-md bg-surface-main border border-border-main text-primary-base transition-colors group-hover:bg-primary-base group-hover:text-white group-hover:border-primary-base">
                                    <span class="material-symbols-outlined" aria-hidden="true">mail</span>
                                </span>
                                <span>Email Support: support@finzo.com</span>
                            </a>
                        </div>
                    </div>

                    <!-- Actions Group Component -->
                    <div class="mt-4">
                        <a href="/Finzo/public/index.html" class="btn btn-primary btn-full">
                            <span class="material-symbols-outlined text-lg" aria-hidden="true">home</span>
                            <span>Return to Homepage</span>
                        </a>
                    </div>

                    <!-- Security Notice Component -->
                    <div class="flex items-start gap-3 bg-surface-subtle border border-border-subtle rounded-xl p-4 mt-8 text-left mt-6">
                        <span class="material-symbols-outlined text-primary-base text-xl flex-shrink-0 mt-0.5" aria-hidden="true">shield</span>
                        <div class="text-xs text-text-muted leading-relaxed">
                            <strong>Security Notice:</strong> Your account will automatically unlock after 30 minutes. If you did not initiate these login attempts, contact our security team immediately.
                        </div>
                    </div>

                    <!-- Trust Badges Component -->
                    <div class="flex flex-wrap items-center justify-center gap-4 mt-8 pt-6 border-t border-border-subtle">
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-text-muted"><span class="material-symbols-outlined" aria-hidden="true">enhanced_encryption</span> 256-bit Encryption</span>
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-text-muted"><span class="material-symbols-outlined" aria-hidden="true">gpp_good</span> Automated Defense</span>
                    </div>
<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/auth-layout.php';
?>