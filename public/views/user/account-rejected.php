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
$pageTitle = 'Application Action Required';
ob_start();
?>
<div class="mb-8 ds-animate-fade">
    <div class="text-sm font-bold text-text-muted uppercase tracking-wider mb-1">Onboarding Status</div>
    <h1 class="font-display text-3xl font-extrabold text-text-main tracking-tight">Application Action Required</h1>
    <p class="text-text-muted mt-1">We were unable to approve your application based on the information currently available. Please review the details below.</p>
</div>

<div class="ds-card p-8 max-w-3xl mx-auto ds-animate-slide-up">
    <div class="text-center mb-8">
        <div class="w-20 h-20 rounded-full bg-danger-surface border-4 border-white shadow-sm flex items-center justify-center mx-auto mb-4">
            <span class="material-symbols-outlined text-4xl text-danger-base" aria-hidden="true">assignment_late</span>
        </div>
        <h2 class="font-display text-2xl font-bold text-text-main mb-2">Application Requires Attention</h2>
        <p class="text-text-muted">Your application was not approved in its current state. You may need to provide additional documentation.</p>
    </div>

    <!-- Alert Danger -->
    <div class="flex items-start gap-3 p-4 rounded-md mb-8 text-sm bg-danger-surface border border-danger-surface text-danger-text">
        <span class="material-symbols-outlined text-xl flex-shrink-0 mt-0.5" aria-hidden="true">error</span>
        <div>
            <strong>Reason:</strong> Identity Verification Failed. The uploaded ID document was unreadable or expired.
        </div>
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
                    <div class="w-8 h-8 rounded-full bg-danger-base text-white flex items-center justify-center relative z-10 shadow-sm">
                        <span class="material-symbols-outlined text-sm" aria-hidden="true">close</span>
                    </div>
                    <div class="w-0.5 h-full bg-border-main mt-2"></div>
                </div>
                <div class="flex-1 pb-4">
                    <div class="text-sm font-bold text-danger-base">Review Incomplete</div>
                    <div class="text-xs text-danger-base/80 mt-1 font-medium">Verification could not be completed.</div>
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
                    <div class="text-xs text-text-muted mt-1">Pending successful review.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="pt-2 flex flex-col sm:flex-row justify-center gap-3">
        <a href="complete-profile.php" class="btn btn-primary w-full sm:w-auto flex justify-center">
            <span class="material-symbols-outlined text-lg" aria-hidden="true">edit</span>
            <span>Update Information</span>
        </a>
        <button type="button" id="user-logout-btn" class="btn btn-secondary w-full sm:w-auto flex justify-center">
            <span class="material-symbols-outlined text-lg" aria-hidden="true">logout</span>
            <span>Sign Out</span>
        </button>
    </div>
</div>

<script defer src="/Finzo/public/assets/js/user/account-rejected.js"></script>
<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
