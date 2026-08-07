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

<script>
    // Adjusting progress bar dynamically if required
    function updateProgress(step) {
        const steps = ['25% Complete', '50% Complete', '75% Complete', '100% Complete'];
        const widths = ['25%', '50%', '75%', '100%'];
        document.getElementById('progress-text').innerText = steps[step-1];
        document.getElementById('progress-bar').style.width = widths[step-1];
    }
    // Add event listeners or keep the logic in user-shared.js intact if it targets IDs
</script>

<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>