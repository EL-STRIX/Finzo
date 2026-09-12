<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin — Card Products (Coming Soon)';
$pageHeader = '
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-accent/10 text-accent border border-accent/20">
                <span class="w-1.5 h-1.5 rounded-full bg-accent animate-pulse"></span>
                In Development
            </span>
            <span class="text-text-muted text-xs font-mono">Module: Card Engine 2.0</span>
        </div>
        <h1 class="text-2xl font-headline font-black text-text-primary tracking-tight">Card Products &amp; Schemes</h1>
        <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-1">Configure credit and debit card tiers, BIN routing, and daily transaction bounds</p>
    </div>
    <div class="flex items-center gap-3">
        <button id="btn-notify-card-products" class="btn btn-secondary !px-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">notifications_active</span>
            <span class="font-bold text-xs">Notify on Launch</span>
        </button>
    </div>
</div>
';
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/admin-card-products.js"></script>';
ob_start();
?>

<!-- COMING SOON HERO BANNER -->
<div class="ds-card p-8 relative overflow-hidden shadow-2xl mb-8 border border-border-subtle bg-gradient-to-br from-surface via-surface to-accent/5">
    <div class="absolute -right-24 -top-24 w-80 h-80 bg-accent/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -left-20 -bottom-20 w-72 h-72 bg-primary/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative z-10 max-w-3xl">
        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-accent/20 to-accent/5 text-accent flex items-center justify-center mb-6 border border-accent/20 shadow-lg">
            <span class="material-symbols-outlined text-3xl">featured_play_list</span>
        </div>

        <h2 class="text-3xl font-display font-black text-text-primary tracking-tight mb-3">
            Card Portfolio & Tier Configuration
        </h2>
        <p class="text-text-secondary text-sm leading-relaxed mb-6">
            The next-generation Card Products management console is undergoing active architectural development. Finzo administrators will be able to define debit and credit card tiers, assign BIN ranges across global networks (Visa, Mastercard, RuPay), configure daily ATM and POS limits, and establish interchange fees.
        </p>

        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-background/80 border border-border-subtle text-xs font-semibold text-text-secondary">
                <span class="material-symbols-outlined text-emerald-500 text-sm">check_circle</span>
                <span>Database Schemas & Tables Ready</span>
            </div>
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-background/80 border border-border-subtle text-xs font-semibold text-text-secondary">
                <span class="material-symbols-outlined text-accent text-sm">credit_card</span>
                <span>BIN Range & Network Integration in Architecture Review</span>
            </div>
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-background/80 border border-border-subtle text-xs font-semibold text-text-secondary">
                <span class="material-symbols-outlined text-amber-500 text-sm">schedule</span>
                <span>Target Release: Q4 2026</span>
            </div>
        </div>
    </div>
</div>

<!-- PLANNED CAPABILITIES GRID -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="ds-card p-6 border border-border-subtle hover:border-accent/40 transition-all group">
        <div class="w-12 h-12 rounded-xl bg-accent/10 text-accent flex items-center justify-center mb-4 group-hover:scale-105 transition-transform">
            <span class="material-symbols-outlined text-2xl">credit_card_gear</span>
        </div>
        <h3 class="font-headline font-bold text-base text-text-primary mb-2">Multi-Network & BIN Tiers</h3>
        <p class="text-xs text-text-secondary leading-relaxed">
            Support for Visa Infinite, Mastercard World Elite, and RuPay Select with automated Luhn algorithm validation and Bank Identification Number (BIN) mapping.
        </p>
    </div>

    <div class="ds-card p-6 border border-border-subtle hover:border-accent/40 transition-all group">
        <div class="w-12 h-12 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center mb-4 group-hover:scale-105 transition-transform">
            <span class="material-symbols-outlined text-2xl">speed</span>
        </div>
        <h3 class="font-headline font-bold text-base text-text-primary mb-2">Dynamic Velocity Caps</h3>
        <p class="text-xs text-text-secondary leading-relaxed">
            Configurable daily and single-transaction ceilings across ATM withdrawals, physical Point-of-Sale (POS), contactless NFC, and international e-commerce.
        </p>
    </div>

    <div class="ds-card p-6 border border-border-subtle hover:border-accent/40 transition-all group">
        <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center mb-4 group-hover:scale-105 transition-transform">
            <span class="material-symbols-outlined text-2xl">loyalty</span>
        </div>
        <h3 class="font-headline font-bold text-base text-text-primary mb-2">Fee & Rewards Matrix</h3>
        <p class="text-xs text-text-secondary leading-relaxed">
            Automated billing of annual maintenance fees, joining bonuses, airport lounge eligibility thresholds, and merchant category code (MCC) cashback policies.
        </p>
    </div>
</div>

<!-- ARCHITECTURE BLUEPRINT FOR FUTURE IMPLEMENTATION -->
<div class="ds-card p-6 border border-border-subtle bg-surface/90">
    <div class="flex items-center gap-3 mb-4 pb-4 border-b border-border-subtle">
        <div class="w-10 h-10 rounded-xl bg-accent/10 text-accent flex items-center justify-center">
            <span class="material-symbols-outlined text-xl">developer_board</span>
        </div>
        <div>
            <h3 class="font-headline font-bold text-base text-text-primary">Implementation Specification</h3>
            <p class="text-xs text-text-secondary">Clean architecture blueprint for implementing this feature backend</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 text-xs">
        <div class="space-y-3">
            <div class="font-bold text-text-primary uppercase tracking-wider text-[11px]">Target API Contracts</div>
            <div class="p-3 rounded-lg bg-background border border-border-subtle font-mono text-[11px] space-y-1.5">
                <div class="text-emerald-500"><span class="font-bold">GET</span> /api/v1/admin/card-products <span class="text-text-muted">(List all card products)</span></div>
                <div class="text-accent"><span class="font-bold">POST</span> /api/v1/admin/card-products <span class="text-text-muted">(Create new card tier)</span></div>
                <div class="text-amber-500"><span class="font-bold">PATCH</span> /api/v1/admin/card-products/{id} <span class="text-text-muted">(Modify limits & status)</span></div>
            </div>
        </div>

        <div class="space-y-3">
            <div class="font-bold text-text-primary uppercase tracking-wider text-[11px]">Decoupled Components</div>
            <ul class="space-y-1.5 text-text-secondary">
                <li class="flex items-center gap-2"><span class="material-symbols-outlined text-text-muted text-[14px]">chevron_right</span> <strong>Controller:</strong> <code class="text-text-primary">AdminCardController</code></li>
                <li class="flex items-center gap-2"><span class="material-symbols-outlined text-text-muted text-[14px]">chevron_right</span> <strong>Service:</strong> <code class="text-text-primary">CardService</code> implements <code class="text-text-primary">CardServiceInterface</code></li>
                <li class="flex items-center gap-2"><span class="material-symbols-outlined text-text-muted text-[14px]">chevron_right</span> <strong>Repository:</strong> <code class="text-text-primary">CardRepository</code> (mapped to DB table: <code class="text-text-primary">card_products</code>)</li>
            </ul>
        </div>
    </div>
</div>

<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
