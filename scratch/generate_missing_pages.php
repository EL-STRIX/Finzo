<?php
function generateAuthPage($title, $headerTitle, $headerSubtitle, $headerTag, $icon, $iconClass, $cardTitle, $cardDesc, $primaryBtnText, $primaryBtnIcon, $primaryBtnLink, $secondaryBtnText, $secondaryBtnLink, $filename) {
    $html = <<<HTML
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Finzo | {$title}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet"/>
    <script src="/Finzo/public/assets/js/tailwind-config.js"></script>
    <link href="/Finzo/public/assets/css/main.css" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
</head>
<body class="bg-background font-body text-[var(--color-text-main)] antialiased">
<script src="/Finzo/public/assets/js/user/user-shared.js?v=2"></script>
<main class="min-h-screen flex items-stretch">
    <!-- Left Column: Visual Branding -->
    <section class="hidden lg:flex lg:w-1/2 relative flex-col justify-between p-10 xl:p-16 overflow-hidden" style="background: linear-gradient(135deg, rgba(10,20,40,0.88) 0%, rgba(26,60,94,0.75) 100%);">
        <div class="relative z-10">
            <div class="flex items-center gap-2 mb-16 xl:mb-20">
                <span class="text-2xl xl:text-3xl font-black tracking-tighter text-white font-headline cursor-pointer" data-href="/Finzo/public/index.html">Finzo</span>
            </div>
            <div class="max-w-md text-white">
                <p class="font-headline text-4xl xl:text-5xl font-bold leading-tight mb-6">{$headerTitle}</p>
                <p class="text-white/80 text-base xl:text-lg leading-relaxed">{$headerSubtitle}</p>
            </div>
        </div>
        <div class="relative z-10 flex gap-4 text-white/40 text-xs font-headline tracking-widest uppercase">
            <span>{$headerTag}</span>
        </div>
        <div class="absolute inset-0 z-0 pointer-events-none">
            <img alt="Modern Architecture" class="w-full h-full object-cover brightness-[0.35] contrast-[1.1]" src="/Finzo/public/assets/images/forget-password-page-image.png" loading="lazy">
            <div class="absolute inset-0 bg-primary-600/30 mix-blend-multiply"></div>
        </div>
    </section>

    <!-- Right Column: Status Card -->
    <section class="w-full lg:w-1/2 flex flex-col justify-center items-center px-5 py-10 sm:p-8 md:p-16 lg:p-20 xl:p-24 bg-background">
        <div class="w-full max-w-md">

            <!-- Mobile Branding -->
            <div class="md:hidden flex items-center justify-center gap-2 mb-6">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-primary-600/10">
                    <span class="material-symbols-outlined text-primary-600 text-lg">account_balance</span>
                </div>
                <span class="text-xl font-black tracking-tighter text-primary-600 font-headline">Finzo</span>
            </div>

            <div class="mb-8 sm:mb-10 lg:mb-12 text-center">
                <div class="w-20 h-20 rounded-full {$iconClass} border-2 flex items-center justify-center mx-auto mb-6 relative">
                    <span class="material-symbols-outlined text-[40px] relative z-10">{$icon}</span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-headline font-bold text-[var(--color-text-main)] tracking-tight mb-2">{$cardTitle}</h2>
                <p class="text-[var(--color-text-muted)] font-medium text-sm sm:text-base">{$cardDesc}</p>
            </div>

            <form class="ds-card p-6 md:p-8 space-y-6">
HTML;
    
    if ($filename === 'otp-verification.html') {
        $html .= <<<HTML
                <div class="fin-input-container">
                    <label class="field-label ml-1">Secure OTP Code</label>
                    <div class="relative">
                        <span class="material-symbols-outlined input-icon">dialpad</span>
                        <input class="fin-input has-icon-left text-center tracking-[0.5em] text-lg font-bold" placeholder="• • • • • •" type="text" maxlength="6" autocomplete="one-time-code" required/>
                    </div>
                </div>
HTML;
    }

    $html .= <<<HTML
                <div class="pt-3 sm:pt-4 flex flex-col gap-3">
                    <a href="{$primaryBtnLink}" class="btn-primary flex justify-center items-center w-full">
                        <span>{$primaryBtnText}</span>
                        <span class="material-symbols-outlined text-xl ml-2">{$primaryBtnIcon}</span>
                    </a>
HTML;

    if ($secondaryBtnText) {
        $html .= <<<HTML
                    <a href="{$secondaryBtnLink}" class="btn-secondary flex justify-center items-center w-full">
                        <span>{$secondaryBtnText}</span>
                    </a>
HTML;
    }

    $html .= <<<HTML
                </div>
            </form>

            <div class="mt-8 text-center text-xs font-bold text-[var(--color-text-muted)] tracking-wide">
                Need help? <a href="mailto:support@finzobank.com" class="text-primary-600 hover:underline ml-1">Contact Support</a>
            </div>

        </div>
    </section>
</main>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="/Finzo/public/assets/js/core/utils.js"></script>
<script src="/Finzo/public/assets/js/core/api.js"></script>
</body>
</html>
HTML;
    file_put_contents('resources/views/auth/' . $filename, $html);
}

// Generate the 6 missing pages
generateAuthPage(
    'OTP Verification', 'Identity<br/>Verification.', 'Please enter the 6-digit one-time password sent to your registered device.', 'Multi-Factor Authentication', 
    'lock_clock', 'bg-info-surface/50 border-info-base/20 text-info-base',
    'Verify Identity', 'Enter the security code to access your account.',
    'Verify Securely', 'verified', 'dashboard.html',
    'Resend Code', '#',
    'otp-verification.html'
);

generateAuthPage(
    'Verify Email', 'Email<br/>Verification.', 'We need to verify your email address to secure your institutional profile.', 'Security Requirement',
    'mark_email_unread', 'bg-info-surface/50 border-info-base/20 text-info-base',
    'Check Your Inbox', 'We have sent a verification link to your email. Click the link to verify your identity.',
    'Resend Verification Email', 'send', '#',
    'Return to Login', 'login.html',
    'email-verification.html'
);

generateAuthPage(
    'Account Locked', 'Account<br/>Locked.', 'Access to your institutional portal has been restricted due to multiple failed authentication attempts.', 'Security Protocol Enforced',
    'lock', 'bg-danger-surface/50 border-danger-base/20 text-danger-base',
    'Account Locked', 'For your security, we have temporarily locked your account. Please reset your password to regain access.',
    'Reset Password', 'key', 'forgot-password.html',
    '', '',
    'account-locked.html'
);

generateAuthPage(
    'Session Expired', 'Session<br/>Expired.', 'Your secure session has expired due to inactivity. Please authenticate again to continue.', 'Security Protocol Enforced',
    'timer', 'bg-warning-surface/50 border-warning-base/20 text-warning-base',
    'Session Timeout', 'You have been securely logged out due to inactivity.',
    'Sign In Again', 'login', 'login.html',
    '', '',
    'session-expired.html'
);

generateAuthPage(
    'Verification Success', 'Identity<br/>Confirmed.', 'Your identity has been successfully verified. You now have full access to your account.', 'Verification Complete',
    'verified', 'bg-success-surface/50 border-success-base/20 text-success-base',
    'Verification Successful', 'Your account is fully verified and secure.',
    'Proceed to Dashboard', 'arrow_forward', '/Finzo/views/user/dashboard.html',
    '', '',
    'verification-success.html'
);

generateAuthPage(
    'Verification Failed', 'Verification<br/>Failed.', 'We were unable to securely verify your identity. Please ensure the link is valid and try again.', 'Identity Verification Failed',
    'cancel', 'bg-danger-surface/50 border-danger-base/20 text-danger-base',
    'Verification Unsuccessful', 'The verification token is invalid or has expired.',
    'Request New Link', 'refresh', 'email-verification.html',
    'Return to Login', 'login.html',
    'verification-failed.html'
);

echo "Missing pages generated.\n";
