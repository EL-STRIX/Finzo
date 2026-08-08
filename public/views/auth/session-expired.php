<?php
$pageTitle = 'Finzo';
$authBgImage = 'customer_bg.png';
$authHeroTitle = 'Your session has ended.';
$authHeroSubtitle = 'For your security, sessions are automatically ended after a period of inactivity. Please sign in again to continue.';
$authSecurityIcon = 'timer';
$authSecurityTitle = 'Automatic sign-out after inactivity';
$authSecurityDesc = 'Finzo signs you out automatically after 24 hours of inactivity to protect your account from unauthorized access.';
ob_start();
?>
<div class="mb-8 text-center lg:text-left">
                        <h1 class="text-3xl font-bold font-display text-text-main mb-2 tracking-tight">Session Terminated</h1>
                        <p class="text-sm text-text-muted leading-relaxed">Your banking session expired due to inactivity.</p>
                    </div>

                    <!-- Alert Warning Component -->
                    <div class="flex items-start gap-3 p-4 rounded-md mb-6 text-sm bg-warning-surface border border-warning-surface text-warning-text text-left" role="alert">
                        <span class="material-symbols-outlined text-xl flex-shrink-0 mt-0.5" aria-hidden="true">warning</span>
                        <div>
                            To protect your financial data, Finzo automatically signs out inactive sessions after 24 hours. Please sign in again to continue.
                        </div>
                    </div>

                    <!-- Buttons Group Component -->
                    <div class="space-y-3 mt-6">
                        <a href="login.php" class="btn btn-primary btn-full">
                            <span class="material-symbols-outlined text-lg" aria-hidden="true">login</span>
                            <span>Re-Authenticate Now</span>
                        </a>

                        <a href="/Finzo/public/index.html" class="btn btn-secondary btn-full">
                            <span class="material-symbols-outlined text-lg" aria-hidden="true">home</span>
                            <span>Return to Home</span>
                        </a>
                    </div>

                    <!-- Security Notice Component -->
                    <div class="flex items-start gap-3 bg-surface-subtle border border-border-subtle rounded-xl p-4 mt-8 text-left mt-8">
                        <span class="material-symbols-outlined text-primary-base text-xl flex-shrink-0 mt-0.5" aria-hidden="true">lock</span>
                        <div class="text-xs text-text-muted leading-relaxed">
                            <strong>Session Security:</strong> Any unsaved form data has been cleared to prevent unauthorized viewing.
                        </div>
                    </div>

                    <!-- Trust Badges Component -->
                    <div class="flex flex-wrap items-center justify-center gap-4 mt-8 pt-6 border-t border-border-subtle">
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-text-muted"><span class="material-symbols-outlined" aria-hidden="true">enhanced_encryption</span> 256-bit Encryption</span>
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-text-muted"><span class="material-symbols-outlined" aria-hidden="true">gpp_good</span> Automatic Logout</span>
                    </div>
<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/auth-layout.php';
?>