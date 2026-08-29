<?php
$portal = 'user';
$pageTitle = 'Security & 2FA Center | Finzo Banking';
ob_start();
?>

<!-- Header -->
<div class="mb-8 ds-animate-fade">
    <div class="text-xs font-bold text-text-muted uppercase tracking-wider mb-1">Account &amp; Security</div>
    <h1 class="font-display text-3xl font-extrabold text-text-main tracking-tight">Security &amp; 2FA Control Center</h1>
    <p class="text-text-muted mt-1">Manage multi-factor authentication, security controls, and active sessions for your Finzo account.</p>
</div>

<!-- Two-Factor Authentication (2FA) Coming Soon Card -->
<div class="ds-card p-8 lg:p-12 text-center max-w-3xl mx-auto border border-border-main bg-surface ds-animate-slide-up">
    <div class="w-20 h-20 rounded-2xl bg-primary-surface text-primary-base flex items-center justify-center mx-auto mb-6 shadow-sm border border-border-main">
        <span class="material-symbols-outlined text-4xl">shield_lock</span>
    </div>
    
    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-600 dark:text-amber-400 text-xs font-bold uppercase tracking-wider mb-4">
        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
        <span>Coming Soon</span>
    </div>

    <h2 class="font-display text-2xl font-bold text-text-main mb-3">Two-Factor Authentication (2FA)</h2>
    <p class="text-text-muted text-sm max-w-lg mx-auto leading-relaxed mb-6">
        Hardware security key and Time-based One-Time Password (TOTP) authenticator app integration are currently undergoing security audits and will be enabled in an upcoming release.
    </p>

    <div class="p-4 rounded-xl bg-surface-subtle border border-border-main text-left max-w-md mx-auto space-y-3">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-primary-base text-lg">check_circle</span>
            <span class="text-xs font-semibold text-text-main">Automated IP & Rate Limit Protection Active</span>
        </div>
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-primary-base text-lg">check_circle</span>
            <span class="text-xs font-semibold text-text-main">Encrypted Session Tokens Enabled</span>
        </div>
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-amber-500 text-lg">hourglass_top</span>
            <span class="text-xs font-semibold text-text-muted">TOTP Authenticator & SMS 2FA (In Development)</span>
        </div>
    </div>
</div>

<script defer src="/Finzo/public/assets/js/user/user-security.js"></script>

<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
