<?php
$portal = 'user';
$pageTitle = 'Application Under Review';
ob_start();
?>
<div class="mb-8 ds-animate-fade">
    <div class="text-sm font-bold text-text-muted uppercase tracking-wider mb-1">Onboarding Status</div>
    <h1 class="font-display text-3xl font-extrabold text-text-main tracking-tight">Application Under Review</h1>
    <p class="text-text-muted mt-1">Your application has been submitted and is under review by our compliance team.</p>
</div>

<div class="max-w-3xl mx-auto space-y-6 ds-animate-slide-up">

    <!-- Status Banner -->
    <div class="ds-card p-6 flex items-center gap-5">
        <div class="relative flex-shrink-0">
            <div class="w-16 h-16 rounded-full bg-indigo-500/10 border-2 border-indigo-500/30 flex items-center justify-center">
                <span class="material-symbols-outlined text-3xl text-indigo-500" aria-hidden="true">hourglass_top</span>
            </div>
            <span class="animate-ping absolute top-0 right-0 w-4 h-4 rounded-full bg-indigo-400 opacity-40"></span>
            <span class="absolute top-0 right-0 w-4 h-4 rounded-full bg-indigo-500 border-2 border-white"></span>
        </div>
        <div class="flex-1 min-w-0">
            <div class="text-lg font-bold text-text-main">Under Review</div>
            <div class="text-sm text-text-muted mt-0.5">Our compliance team is verifying your submitted documents. Typical review time is 24–48 hours.</div>
            <div class="mt-2 flex items-center gap-2 text-xs font-semibold text-indigo-500">
                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-pulse inline-block"></span>
                Review in progress
            </div>
        </div>
        <div class="hidden sm:flex flex-col items-end gap-1 shrink-0">
            <div class="text-[10px] font-bold text-text-disabled uppercase tracking-widest">Submitted</div>
            <div id="kyc-submitted-display" class="text-xs font-bold text-text-main">—</div>
        </div>
    </div>

    <!-- Full Timeline -->
    <div class="ds-card p-6">
        <div class="text-sm font-bold text-text-main mb-5 flex items-center gap-2">
            <span class="material-symbols-outlined text-base text-primary-base">timeline</span>
            Application History
        </div>

        <div id="full-timeline" class="relative">
            <!-- Vertical line -->
            <div class="absolute left-[17px] top-2 bottom-2 w-0.5 bg-border-main rounded-full" aria-hidden="true"></div>

            <div class="space-y-0" id="timeline-entries">

                <!-- Step 1: Registration -->
                <div class="tl-entry flex gap-4 relative pb-6">
                    <div class="w-9 h-9 rounded-full bg-emerald-500/10 border-2 border-emerald-500/30 flex items-center justify-center shrink-0 z-10">
                        <span class="material-symbols-outlined text-emerald-500 text-base">how_to_reg</span>
                    </div>
                    <div class="flex-1 pt-1">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="text-sm font-bold text-text-main">Account Registered</div>
                                <div class="text-xs text-text-muted mt-0.5">Email, phone, and password set up successfully.</div>
                            </div>
                            <div id="ts-registered" class="text-[10px] font-bold text-text-disabled uppercase tracking-widest shrink-0 text-right mt-0.5">—</div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: KYC Submitted -->
                <div class="tl-entry flex gap-4 relative pb-6">
                    <div class="w-9 h-9 rounded-full bg-emerald-500/10 border-2 border-emerald-500/30 flex items-center justify-center shrink-0 z-10">
                        <span class="material-symbols-outlined text-emerald-500 text-base">assignment_turned_in</span>
                    </div>
                    <div class="flex-1 pt-1">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="text-sm font-bold text-text-main">KYC Profile Submitted</div>
                                <div class="text-xs text-text-muted mt-0.5">Personal details, address and identity documents uploaded.</div>
                            </div>
                            <div id="ts-kyc-submitted" class="text-[10px] font-bold text-text-disabled uppercase tracking-widest shrink-0 text-right mt-0.5">—</div>
                        </div>
                    </div>
                </div>

                <!-- Past rejections injected here by JS -->
                <div id="rejection-history"></div>

                <!-- Step 3: Under Review (current) -->
                <div class="tl-entry flex gap-4 relative pb-6">
                    <div class="relative w-9 h-9 shrink-0 z-10">
                        <span class="animate-ping absolute inset-0 rounded-full bg-indigo-400/30"></span>
                        <div class="w-9 h-9 rounded-full bg-indigo-500/20 border-2 border-indigo-500/50 flex items-center justify-center relative">
                            <span class="material-symbols-outlined text-indigo-500 text-base">manage_search</span>
                        </div>
                    </div>
                    <div class="flex-1 pt-1">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="text-sm font-bold text-indigo-500">Under Compliance Review</div>
                                <div class="text-xs text-indigo-400/80 mt-0.5 font-medium">Our team is currently verifying your identity and documents.</div>
                            </div>
                            <div class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest shrink-0 text-right mt-0.5">Now</div>
                        </div>
                        <div class="mt-2 inline-flex items-center gap-1.5 px-2 py-0.5 rounded bg-indigo-500/10 border border-indigo-500/20 text-[10px] font-black text-indigo-500 uppercase tracking-wider">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-pulse"></span>
                            In Progress
                        </div>
                    </div>
                </div>

                <!-- Step 4: Account Activation (pending) -->
                <div class="tl-entry flex gap-4 relative">
                    <div class="w-9 h-9 rounded-full bg-surface-main border-2 border-border-main flex items-center justify-center shrink-0 z-10">
                        <span class="material-symbols-outlined text-text-disabled text-base">lock_open</span>
                    </div>
                    <div class="flex-1 pt-1">
                        <div class="text-sm font-bold text-text-disabled">Account Activated</div>
                        <div class="text-xs text-text-muted mt-0.5">Full access granted upon successful approval.</div>
                        <div class="mt-1.5 text-[10px] font-bold text-text-disabled uppercase tracking-widest">Awaiting approval</div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- What Happens Next -->
    <div class="ds-card p-5">
        <div class="flex items-start gap-3">
            <span class="material-symbols-outlined text-primary-base text-xl shrink-0 mt-0.5" aria-hidden="true">info</span>
            <div>
                <div class="text-sm font-bold text-primary-base mb-2">What happens next?</div>
                <ul class="text-xs text-text-main leading-relaxed space-y-2">
                    <li class="flex items-start gap-2"><span class="text-primary-base font-bold mt-0.5">•</span>Your submitted information and documents will be reviewed by our compliance team.</li>
                    <li class="flex items-start gap-2"><span class="text-primary-base font-bold mt-0.5">•</span>Identity verification and document authenticity checks will be completed.</li>
                    <li class="flex items-start gap-2"><span class="text-primary-base font-bold mt-0.5">•</span>You will receive a notification at <strong id="user-email-show" class="font-bold text-text-main break-all">your registered email</strong> once the review is finished.</li>
                    <li class="flex items-start gap-2"><span class="text-primary-base font-bold mt-0.5">•</span>After approval, you can securely access all Finzo Banking features.</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex justify-center">
        <a href="/Finzo/public/views/auth/login.php" class="btn btn-secondary w-full sm:w-auto flex justify-center">
            <span class="material-symbols-outlined text-lg" aria-hidden="true">logout</span>
            <span>Sign Out Temporarily</span>
        </a>
    </div>

</div>

<script defer src="/Finzo/public/assets/js/user/account-pending.js"></script>
<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
