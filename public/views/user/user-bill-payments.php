<?php
$portal = 'user';
$pageTitle = 'Bill Payments & Utility Services | Finzo Banking';
ob_start();
?>

<!-- Header -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8 ds-animate-fade">
    <div>
        <div class="text-xs font-bold text-text-secondary uppercase tracking-widest mb-1">Services &amp; Payments</div>
        <h1 class="font-display text-3xl font-extrabold text-text-primary tracking-tight">Bill Payments &amp; Utilities</h1>
        <p class="text-text-secondary text-sm mt-1">Instant digital payment of electricity, water, mobile, broadband, and DTH bills directly from your savings account.</p>
    </div>
</div>

<!-- Biller Category Grid -->
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-8 ds-animate-slide-up">
    <div onclick="selectBillerCategory('Electricity', 'bolt')" class="ds-card p-4 border border-border-subtle bg-surface/80 hover:bg-surface hover:border-primary/50 transition-all cursor-pointer flex flex-col items-center text-center group">
        <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-500 group-hover:scale-110 transition-transform flex items-center justify-center mb-2">
            <span class="material-symbols-outlined text-2xl">bolt</span>
        </div>
        <span class="text-xs font-bold text-text-primary">Electricity</span>
    </div>

    <div onclick="selectBillerCategory('Water', 'water_drop')" class="ds-card p-4 border border-border-subtle bg-surface/80 hover:bg-surface hover:border-primary/50 transition-all cursor-pointer flex flex-col items-center text-center group">
        <div class="w-12 h-12 rounded-xl bg-blue-500/10 text-blue-500 group-hover:scale-110 transition-transform flex items-center justify-center mb-2">
            <span class="material-symbols-outlined text-2xl">water_drop</span>
        </div>
        <span class="text-xs font-bold text-text-primary">Water</span>
    </div>

    <div onclick="selectBillerCategory('Mobile Recharge', 'smartphone')" class="ds-card p-4 border border-border-subtle bg-surface/80 hover:bg-surface hover:border-primary/50 transition-all cursor-pointer flex flex-col items-center text-center group">
        <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-500 group-hover:scale-110 transition-transform flex items-center justify-center mb-2">
            <span class="material-symbols-outlined text-2xl">smartphone</span>
        </div>
        <span class="text-xs font-bold text-text-primary">Mobile</span>
    </div>

    <div onclick="selectBillerCategory('Broadband Internet', 'wifi')" class="ds-card p-4 border border-border-subtle bg-surface/80 hover:bg-surface hover:border-primary/50 transition-all cursor-pointer flex flex-col items-center text-center group">
        <div class="w-12 h-12 rounded-xl bg-indigo-500/10 text-indigo-500 group-hover:scale-110 transition-transform flex items-center justify-center mb-2">
            <span class="material-symbols-outlined text-2xl">wifi</span>
        </div>
        <span class="text-xs font-bold text-text-primary">Broadband</span>
    </div>

    <div onclick="selectBillerCategory('DTH Recharge', 'tv')" class="ds-card p-4 border border-border-subtle bg-surface/80 hover:bg-surface hover:border-primary/50 transition-all cursor-pointer flex flex-col items-center text-center group">
        <div class="w-12 h-12 rounded-xl bg-rose-500/10 text-rose-500 group-hover:scale-110 transition-transform flex items-center justify-center mb-2">
            <span class="material-symbols-outlined text-2xl">tv</span>
        </div>
        <span class="text-xs font-bold text-text-primary">DTH Cable</span>
    </div>

    <div onclick="selectBillerCategory('Piped Gas', 'local_fire_department')" class="ds-card p-4 border border-border-subtle bg-surface/80 hover:bg-surface hover:border-primary/50 transition-all cursor-pointer flex flex-col items-center text-center group">
        <div class="w-12 h-12 rounded-xl bg-orange-500/10 text-orange-500 group-hover:scale-110 transition-transform flex items-center justify-center mb-2">
            <span class="material-symbols-outlined text-2xl">local_fire_department</span>
        </div>
        <span class="text-xs font-bold text-text-primary">Piped Gas</span>
    </div>
</div>

<!-- Pay Bill Form & History Grid -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-8">
    <!-- Form -->
    <div class="lg:col-span-5 ds-card p-6 border border-border-subtle bg-surface/80">
        <div class="flex items-center gap-2 mb-6">
            <div class="w-9 h-9 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                <span class="material-symbols-outlined text-xl" id="form-category-icon">payments</span>
            </div>
            <div>
                <h3 class="font-headline font-bold text-lg text-text-primary" id="form-category-title">Quick Bill Payment</h3>
                <p class="text-xs text-text-secondary">Enter biller details and pay instantly.</p>
            </div>
        </div>

        <form id="bill-pay-form" onsubmit="submitBillPayment(event)" class="space-y-4">
            <input type="hidden" id="bill-category" value="Electricity" />

            <div>
                <label class="fin-label fin-label-required">Select Biller Name</label>
                <input type="text" id="bill-biller-name" class="fin-input w-full" required placeholder="e.g. State Electricity Board, Airtel, Jio" />
            </div>

            <div>
                <label class="fin-label fin-label-required">Consumer / Account / Meter Number</label>
                <input type="text" id="bill-consumer-number" class="fin-input w-full font-mono" required placeholder="Enter Consumer ID / Phone Number" />
            </div>

            <div>
                <label class="fin-label fin-label-required">Payment Amount (₹)</label>
                <input type="number" id="bill-amount" min="1" step="1" class="fin-input w-full font-mono text-lg font-bold text-primary" required placeholder="0.00" />
            </div>

            <div class="pt-2">
                <button type="submit" class="btn btn-primary btn-full shadow-lg">
                    <span class="material-symbols-outlined text-[18px]">verified</span>
                    <span>Pay Bill Instantly</span>
                </button>
            </div>
        </form>
    </div>

    <!-- History -->
    <div class="lg:col-span-7 ds-card p-6 border border-border-subtle">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="font-headline font-bold text-xl text-text-primary">Recent Utility Payments</h3>
                <p class="text-xs text-text-secondary">History of completed bill payments.</p>
            </div>
        </div>

        <div id="bill-history-container" class="space-y-3">
            <div class="py-12 text-center text-text-secondary text-xs font-bold uppercase tracking-wider">
                Loading payment history...
            </div>
        </div>
    </div>
</div>

<script defer src="/Finzo/public/assets/js/user/user-bill-payments.js"></script>

<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
