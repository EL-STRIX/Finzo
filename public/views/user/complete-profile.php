<?php
$portal = 'user';
$pageTitle = 'Complete Your Profile';
ob_start();
?>
<script defer src="/Finzo/public/assets/js/user/user-shared.js?v=3"></script>

<div class="mb-8 ds-animate-fade">
    <div class="text-sm font-bold text-text-muted uppercase tracking-wider mb-1">Onboarding</div>
    <h1 class="font-display text-3xl font-extrabold text-text-main tracking-tight">Complete Your Profile</h1>
    <p class="text-text-muted mt-1">Complete the remaining required information to activate your Finzo Banking account.</p>
</div>

<div class="ds-card p-6 lg:p-8 max-w-4xl mx-auto ds-animate-slide-up">
    
</div>

<script defer src="/Finzo/public/assets/js/user/complete-profile.js"></script>

<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>