<?php
if (!defined('FINZO_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}
\App\Shared\Security\SessionManager::start();

$userStatus = $_SESSION['user_status'] ?? 'pending';
if ($userStatus === \App\Domain\Enums\AccountStatusEnum::ACTIVE->value) {
    header('Location: dashboard.php');
    exit;
}

$portal = 'user';
$pageTitle = 'KYC Application Under Review';
ob_start();
?>
<div class="mb-8 ds-animate-fade">
    <div class="text-sm font-bold text-text-muted uppercase tracking-wider mb-1">Onboarding Status</div>
    <h1 class="font-display text-3xl font-extrabold text-text-main tracking-tight">Application Under Review</h1>
    <p class="text-text-muted mt-1">Thank you for registering. Your application has been successfully submitted and is currently being reviewed.</p>
</div>

<div class="ds-card p-8 max-w-3xl mx-auto ds-animate-slide-up">
    <div class="text-center mb-8">
        <div class="w-20 h-20 rounded-full bg-info-surface border-4 border-white shadow-sm flex items-center justify-center mx-auto mb-4">
            <span class="material-symbols-outlined text-4xl text-info-base" aria-hidden="true">hourglass_top</span>
        </div>
        <h2 class="font-display text-2xl font-bold text-text-main mb-2">Pending Review</h2>
        <p class="text-text-muted">Every application is reviewed to maintain banking security and ensure institutional standards are met.</p>
    </div>

    <!-- Timeline -->
    <div class="mb-8 p-6 border border-border-main rounded-xl bg-surface-subtle">
        <div class="flex flex-col gap-6">
            <!-- Step 1 -->
            <div class="flex gap-4">
                <div class="flex flex-col items-center">
                    <div class="w-8 h-8 rounded-full bg-success-surface text-success-base flex items-center justify-center shrink-0 border border-success-base/20">
                        <span class="material-symbols-outlined text-sm" aria-hidden="true">check</span>
                    </div>
                    <div class="w-0.5 h-full bg-success-base/20 mt-2"></div>
                </div>
                <div class="flex-1 pb-4">
                    <div class="text-sm font-bold text-text-main">Registration Completed</div>
                    <div class="text-xs text-text-muted mt-1">Account details submitted successfully.</div>
                </div>
            </div>

            <!-- Step 2 -->
            <div class="flex gap-4">
                <div class="flex flex-col items-center">
                    <div class="w-8 h-8 rounded-full bg-success-surface text-success-base flex items-center justify-center shrink-0 border border-success-base/20">
                        <span class="material-symbols-outlined text-sm" aria-hidden="true">check</span>
                    </div>
                    <div class="w-0.5 h-full bg-border-main mt-2"></div>
                </div>
                <div class="flex-1 pb-4">
                    <div class="text-sm font-bold text-text-main">Profile Completed</div>
                    <div class="text-xs text-text-muted mt-1">Identity information and documents provided.</div>
                </div>
            </div>

            <!-- Step 3 (Current) -->
            <div class="flex gap-4">
                <div class="flex flex-col items-center">
                    <div class="relative flex h-8 w-8 items-center justify-center shrink-0">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-info-base opacity-20"></span>
                        <div class="w-8 h-8 rounded-full bg-info-base text-white flex items-center justify-center relative z-10 shadow-sm">
                            <span class="material-symbols-outlined text-sm" aria-hidden="true">hourglass_top</span>
                        </div>
                    </div>
                    <div class="w-0.5 h-full bg-border-main mt-2"></div>
                </div>
                <div class="flex-1 pb-4">
                    <div class="text-sm font-bold text-info-base">Under Review</div>
                    <div class="text-xs text-info-base/80 mt-1 font-medium">Our banking team is currently verifying your details.</div>
                </div>
            </div>

            <!-- Step 4 -->
            <div class="flex gap-4">
                <div class="flex flex-col items-center">
                    <div class="w-8 h-8 rounded-full bg-surface-main text-text-disabled flex items-center justify-center shrink-0 border border-border-main">
                        <span class="material-symbols-outlined text-sm" aria-hidden="true">lock_open</span>
                    </div>
                </div>
                <div class="flex-1">
                    <div class="text-sm font-bold text-text-disabled">Account Activated</div>
                    <div class="text-xs text-text-muted mt-1">Waiting for approval.</div>
                </div>
            </div>
        </div>
    </div>

    <!-- What Happens Next -->
    <div class="p-5 bg-primary-surface/40 border border-primary-base/20 rounded-xl flex items-start gap-3 mb-8">
        <span class="material-symbols-outlined text-primary-base text-xl shrink-0 mt-0.5" aria-hidden="true">info</span>
        <div>
            <div class="text-sm font-bold text-primary-base mb-2">What happens next?</div>
            <ul class="text-xs text-text-main leading-relaxed space-y-2 list-none">
                <li class="flex items-start gap-2"><span class="text-primary-base font-bold">•</span> Your submitted information will be reviewed.</li>
                <li class="flex items-start gap-2"><span class="text-primary-base font-bold">•</span> Identity verification will be completed.</li>
                <li class="flex items-start gap-2"><span class="text-primary-base font-bold">•</span> You will receive a notification at <strong id="user-email-show" class="font-bold text-text-main break-all">your registered email</strong> once the review is finished.</li>
                <li class="flex items-start gap-2"><span class="text-primary-base font-bold">•</span> After approval, you can securely access all Finzo Banking features.</li>
            </ul>
        </div>
    </div>
    
    <div class="text-center">
        <button type="button" id="user-logout-btn" class="btn btn-secondary w-full sm:w-auto">
            <span class="material-symbols-outlined text-lg" aria-hidden="true">logout</span>
            <span>Sign Out Temporarily</span>
        </button>
    </div>
</div>

<script defer src="/Finzo/public/assets/js/user/account-pending.js"></script>
<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
