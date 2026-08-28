<?php
$portal = 'user';
$pageTitle = 'Security & 2FA Center | Finzo Banking';
ob_start();
?>

<!-- Header -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8 ds-animate-fade">
    <div>
        <div class="text-xs font-bold text-text-secondary uppercase tracking-widest mb-1">Account &amp; Privacy</div>
        <h1 class="font-display text-3xl font-extrabold text-text-primary tracking-tight">Security &amp; 2FA Control Center</h1>
        <p class="text-text-secondary text-sm mt-1">Configure multi-factor authentication, manage 4-digit transaction PIN, and track active device sessions.</p>
    </div>
</div>

<!-- Security Grid -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-8 ds-animate-slide-up">
    
    <!-- 2FA & Transaction PIN Settings -->
    <div class="lg:col-span-6 space-y-6">
        <!-- 2FA Card -->
        <div class="ds-card p-6 border border-border-subtle bg-surface/80">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl">verified_user</span>
                </div>
                <div>
                    <h3 class="font-headline font-bold text-lg text-text-primary">Two-Factor Authentication (2FA)</h3>
                    <p class="text-xs text-text-secondary">Protect your login with an extra security layer.</p>
                </div>
            </div>

            <div class="flex items-center justify-between p-4 rounded-xl bg-surface-elevated border border-border-subtle mb-4">
                <div>
                    <div class="font-bold text-sm text-text-primary">Authenticator App (TOTP)</div>
                    <div class="text-xs text-text-secondary">Require a 6-digit verification code during sign-in.</div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="2fa-toggle" onchange="toggle2FA(this)" class="sr-only peer" />
                    <div class="w-11 h-6 bg-border-subtle peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                </label>
            </div>
        </div>

        <!-- Transaction PIN Card -->
        <div class="ds-card p-6 border border-border-subtle bg-surface/80">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl">pin</span>
                </div>
                <div>
                    <h3 class="font-headline font-bold text-lg text-text-primary">4-Digit Transaction PIN</h3>
                    <p class="text-xs text-text-secondary">Authorise high-value transfers, balance reveals, and card requests.</p>
                </div>
            </div>

            <form id="set-pin-form" onsubmit="submitSetPin(event)" class="space-y-4">
                <div>
                    <label class="fin-label fin-label-required">Enter New 4-Digit PIN</label>
                    <input type="password" id="sec-pin" maxlength="4" pattern="\d{4}" class="fin-input w-full font-mono text-center tracking-widest text-lg" required placeholder="••••" />
                </div>
                <button type="submit" class="btn btn-primary btn-full">
                    <span>Set / Update Transaction PIN</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Active Device Sessions -->
    <div class="lg:col-span-6 ds-card p-6 border border-border-subtle bg-surface/80">
        <div class="flex items-center justify-between border-b border-border-subtle pb-4 mb-6">
            <div>
                <h3 class="font-headline font-bold text-lg text-text-primary">Active Device Sessions</h3>
                <p class="text-xs text-text-secondary">Devices currently logged into your Finzo account.</p>
            </div>
            <span class="px-2.5 py-1 rounded-full bg-primary/10 text-primary border border-primary/20 text-[10px] font-black uppercase tracking-wider">ACTIVE SESSIONS</span>
        </div>

        <div id="sessions-container" class="space-y-3">
            <div class="py-8 text-center text-text-secondary text-xs font-bold uppercase tracking-wider">
                Loading active sessions...
            </div>
        </div>
    </div>
</div>

<script defer src="/Finzo/public/assets/js/user/user-security.js"></script>

<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
