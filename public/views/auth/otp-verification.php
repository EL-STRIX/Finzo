<?php
$pageTitle = 'Finzo';
$authBgImage = 'customer_bg.png';
$authHeroTitle = 'Two-step verification.';
$authHeroSubtitle = 'We sent a one-time code to your registered phone number. Enter it to confirm your identity and sign in.';
$authSecurityIcon = 'smartphone';
$authSecurityTitle = 'New device detected';
$authSecurityDesc = 'When you sign in from a new or unrecognized device, we require a verification code to confirm it\'s you.';
ob_start();
?>
<div class="mb-8 text-center lg:text-left">
                        <h1 class="text-3xl font-bold font-display text-text-main mb-2 tracking-tight">Device Verification</h1>
                        <p class="text-sm text-text-muted leading-relaxed">A 6-digit security code was sent to your phone ending in <strong>••45</strong>.</p>
                    </div>

                    <!-- OTP Form Component -->
                    <form action="#" method="POST" class="text-center" novalidate>
                        <!-- 6-Digit OTP Grid Component -->
                        <div class="fin-otp-container mb-8">
                            <div class="fin-otp-grid">
                                <input type="text" maxlength="1" class="fin-otp-input" aria-label="OTP Digit 1" required autofocus />
                                <input type="text" maxlength="1" class="fin-otp-input" aria-label="OTP Digit 2" required />
                                <input type="text" maxlength="1" class="fin-otp-input" aria-label="OTP Digit 3" required />
                                <input type="text" maxlength="1" class="fin-otp-input" aria-label="OTP Digit 4" required />
                                <input type="text" maxlength="1" class="fin-otp-input" aria-label="OTP Digit 5" required />
                                <input type="text" maxlength="1" class="fin-otp-input" aria-label="OTP Digit 6" required />
                            </div>
                        </div>

                        <!-- Primary Submit Button -->
                        <button type="submit" class="btn btn-primary btn-full mb-4">
                            <span>Verify Identity</span>
                            <span class="material-symbols-outlined text-lg" aria-hidden="true">verified_user</span>
                        </button>

                        <!-- Resend Component with Countdown -->
                        <p class="text-xs text-gray-500 font-medium">
                            Didn't receive the code? 
                            <button type="button" class="btn-text" data-action="resend-otp">Resend Code in 00:45</button>
                        </p>
                    </form>

                    <!-- Security Notice Component -->
                    <div class="flex items-start gap-3 bg-surface-subtle border border-border-subtle rounded-xl p-4 mt-8 text-left mt-8">
                        <span class="material-symbols-outlined text-primary-base text-xl flex-shrink-0 mt-0.5" aria-hidden="true">phonelink_lock</span>
                        <div class="text-xs text-text-muted leading-relaxed">
                            <strong>Important:</strong> OTP codes expire in 5 minutes. Never share this code with anyone, including Finzo staff.
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