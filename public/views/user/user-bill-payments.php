<?php
$portal = 'user';
$pageTitle = 'Bill Payments & Utilities | Finzo Banking';
ob_start();
?>

<!-- Header -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8 ds-animate-fade">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <span class="text-xs font-bold text-text-secondary uppercase tracking-widest">Services &amp; Payments</span>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                Coming Soon
            </span>
        </div>
        <h1 class="font-display text-3xl font-extrabold text-text-primary tracking-tight">Bill Payments &amp; Utilities</h1>
        <p class="text-text-secondary text-sm mt-1">Unified Bharat BillPay (BBPS) integration for electricity, water, mobile recharge, broadband, DTH, gas &amp; FASTag.</p>
    </div>
    <div class="flex items-center gap-3">
        <button id="btn-bill-waitlist" class="btn btn-primary shadow-lg hover:shadow-primary/25 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">notifications_active</span>
            <span>Join BBPS Early Access</span>
        </button>
    </div>
</div>

<!-- COMING SOON HERO BANNER -->
<div class="ds-card p-8 border border-border-subtle relative overflow-hidden bg-gradient-to-br from-surface via-surface to-amber-500/5 mb-8 shadow-sm">
    <div class="absolute -right-20 -bottom-20 w-72 h-72 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -left-20 -top-20 w-64 h-64 bg-primary/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative z-10 max-w-3xl">
        <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-5 border border-amber-500/20 shadow-sm">
            <span class="material-symbols-outlined text-2xl">receipt_long</span>
        </div>

        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 text-xs font-bold uppercase tracking-wider mb-3">
            <span class="material-symbols-outlined text-[16px]">bolt</span>
            Bharat BillPay (BBPS) Centralized Gateway — In Development
        </div>

        <h2 class="text-2xl sm:text-3xl font-display font-extrabold text-text-primary tracking-tight mb-3">
            Seamless Utility &amp; Biller Payments
        </h2>
        <p class="text-text-secondary text-sm leading-relaxed mb-6">
            We are integrating Finzo directly with NPCI's Bharat BillPay System (BBPS). Settle daily utility dues, configure automated recurring debits, fetch live meter bills, and receive instant digital receipt statements without convenience fees or third-party redirection.
        </p>

        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-background/80 border border-border-subtle text-xs font-semibold text-text-secondary">
                <span class="material-symbols-outlined text-emerald-500 text-sm">hub</span>
                <span>20,000+ National Billers Supported</span>
            </div>
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-background/80 border border-border-subtle text-xs font-semibold text-text-secondary">
                <span class="material-symbols-outlined text-amber-500 text-sm">sync_saved_loc</span>
                <span>One-Click Auto-Debit Schedules</span>
            </div>
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-background/80 border border-border-subtle text-xs font-semibold text-text-secondary">
                <span class="material-symbols-outlined text-primary text-sm">currency_rupee</span>
                <span>Zero Platform Surcharges</span>
            </div>
        </div>
    </div>
</div>

<!-- Interactive Category Explorer -->
<div class="mb-8">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="font-headline font-bold text-lg text-text-primary">Supported Biller Categories</h3>
            <p class="text-xs text-text-secondary">Click any utility category to explore upcoming supported billers and run a demo simulation.</p>
        </div>
        <span id="active-category-pill" class="text-xs font-mono font-bold px-2.5 py-1 rounded-lg bg-primary/10 text-primary border border-primary/20">Electricity Selected</span>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3" id="category-grid">

        <div data-cat-id="electricity" class="category-card active-category ds-card p-4 border border-primary/40 bg-primary/5 hover:border-primary transition-all cursor-pointer flex flex-col items-center text-center group">
            <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-500 group-hover:scale-110 transition-transform flex items-center justify-center mb-2">
                <span class="material-symbols-outlined text-2xl">bolt</span>
            </div>
            <span class="text-xs font-bold text-text-primary">Electricity</span>
            <span class="text-[10px] text-text-secondary mt-0.5">Power Utilities</span>
        </div>

        <div data-cat-id="water" class="category-card ds-card p-4 border border-border-subtle bg-surface/80 hover:border-primary/50 transition-all cursor-pointer flex flex-col items-center text-center group">
            <div class="w-12 h-12 rounded-xl bg-blue-500/10 text-blue-500 group-hover:scale-110 transition-transform flex items-center justify-center mb-2">
                <span class="material-symbols-outlined text-2xl">water_drop</span>
            </div>
            <span class="text-xs font-bold text-text-primary">Water</span>
            <span class="text-[10px] text-text-secondary mt-0.5">Municipal Supply</span>
        </div>

        <div data-cat-id="mobile" class="category-card ds-card p-4 border border-border-subtle bg-surface/80 hover:border-primary/50 transition-all cursor-pointer flex flex-col items-center text-center group">
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-500 group-hover:scale-110 transition-transform flex items-center justify-center mb-2">
                <span class="material-symbols-outlined text-2xl">smartphone</span>
            </div>
            <span class="text-xs font-bold text-text-primary">Mobile</span>
            <span class="text-[10px] text-text-secondary mt-0.5">Recharge &amp; Postpaid</span>
        </div>

        <div data-cat-id="broadband" class="category-card ds-card p-4 border border-border-subtle bg-surface/80 hover:border-primary/50 transition-all cursor-pointer flex flex-col items-center text-center group">
            <div class="w-12 h-12 rounded-xl bg-indigo-500/10 text-indigo-500 group-hover:scale-110 transition-transform flex items-center justify-center mb-2">
                <span class="material-symbols-outlined text-2xl">wifi</span>
            </div>
            <span class="text-xs font-bold text-text-primary">Broadband</span>
            <span class="text-[10px] text-text-secondary mt-0.5">Fiber &amp; Cable</span>
        </div>

        <div data-cat-id="dth" class="category-card ds-card p-4 border border-border-subtle bg-surface/80 hover:border-primary/50 transition-all cursor-pointer flex flex-col items-center text-center group">
            <div class="w-12 h-12 rounded-xl bg-rose-500/10 text-rose-500 group-hover:scale-110 transition-transform flex items-center justify-center mb-2">
                <span class="material-symbols-outlined text-2xl">tv</span>
            </div>
            <span class="text-xs font-bold text-text-primary">DTH / Cable</span>
            <span class="text-[10px] text-text-secondary mt-0.5">Satellite TV</span>
        </div>

        <div data-cat-id="gas" class="category-card ds-card p-4 border border-border-subtle bg-surface/80 hover:border-primary/50 transition-all cursor-pointer flex flex-col items-center text-center group">
            <div class="w-12 h-12 rounded-xl bg-orange-500/10 text-orange-500 group-hover:scale-110 transition-transform flex items-center justify-center mb-2">
                <span class="material-symbols-outlined text-2xl">local_fire_department</span>
            </div>
            <span class="text-xs font-bold text-text-primary">Piped Gas</span>
            <span class="text-[10px] text-text-secondary mt-0.5">PNG &amp; Cylinders</span>
        </div>

        <div data-cat-id="fastag" class="category-card ds-card p-4 border border-border-subtle bg-surface/80 hover:border-primary/50 transition-all cursor-pointer flex flex-col items-center text-center group">
            <div class="w-12 h-12 rounded-xl bg-teal-500/10 text-teal-500 group-hover:scale-110 transition-transform flex items-center justify-center mb-2">
                <span class="material-symbols-outlined text-2xl">toll</span>
            </div>
            <span class="text-xs font-bold text-text-primary">FASTag</span>
            <span class="text-[10px] text-text-secondary mt-0.5">NHAI Toll Recharge</span>
        </div>

    </div>
</div>

<!-- Interactive BBPS Sandbox & Benefits Preview -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-8 ds-animate-slide-up">

    <!-- Sandbox Form -->
    <div class="lg:col-span-6 ds-card p-6 border border-border-subtle bg-surface/80 flex flex-col justify-between">
        <div>
            <div class="flex items-center gap-2 mb-6">
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                    <span class="material-symbols-outlined text-xl" id="sandbox-category-icon">bolt</span>
                </div>
                <div>
                    <h3 class="font-headline font-bold text-lg text-text-primary" id="sandbox-title">Electricity Bill Sandbox</h3>
                    <p class="text-xs text-text-secondary">Simulate live bill discovery via NPCI Bharat BillPay API.</p>
                </div>
            </div>

            <form id="sandbox-bill-form" class="space-y-4">
                <!-- Provider Dropdown — uses the global Finzo animated dropdown system -->
                <div>
                    <label class="fin-label fin-label-required">Selected Provider / Biller</label>
                    <div class="finzo-dropdown relative w-full" id="biller-dropdown">
                        <!-- trigger: ui-components.js sets aria-* automatically, do not duplicate -->
                        <div class="finzo-dropdown-trigger fin-input !flex items-center justify-between cursor-pointer w-full !h-[44px] text-sm">
                            <span class="dropdown-label" id="biller-selected-label">Tata Power Delhi</span>
                            <span class="chevron material-symbols-outlined text-text-secondary text-sm">expand_more</span>
                        </div>
                        <!-- menu: options injected by JS per category -->
                        <div class="finzo-dropdown-menu !z-[1500] !w-full" id="biller-dropdown-menu">
                            <!-- populated by buildBillerOptions() in user-bill-payments.js -->
                        </div>
                        <!-- hidden input MUST be inside .finzo-dropdown so ui-components.js updates it on selection -->
                        <input type="hidden" id="sandbox-biller-value" value="Tata Power Delhi" />
                    </div>
                </div>

                <!-- Consumer Number field (label changes per category) -->
                <div>
                    <label class="fin-label fin-label-required" id="consumer-no-label">Consumer / CA Number</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-secondary text-sm" id="consumer-icon">badge</span>
                        <input type="text" id="sandbox-consumer-no" class="fin-input w-full pl-9 font-mono" placeholder="e.g. 1029384756" value="1029384756" />
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" id="btn-fetch-bill" class="btn btn-primary btn-full shadow-lg flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">search</span>
                        <span>Fetch Live Bill (Demo Simulation)</span>
                    </button>
                </div>
            </form>

            <!-- Simulation Result Card -->
            <div id="sandbox-result" class="mt-5 p-4 rounded-xl border border-emerald-500/30 bg-emerald-500/5 hidden">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-1.5 text-xs font-bold text-emerald-600 dark:text-emerald-400">
                        <span class="material-symbols-outlined text-sm">verified</span>
                        <span>Bill Discovered via BBPS</span>
                    </div>
                    <span id="demo-due-badge" class="text-[10px] font-mono font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">DUE IN 8 DAYS</span>
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs border-t border-emerald-500/10 pt-2 mt-2">
                    <div>
                        <span class="text-text-secondary text-[10px] uppercase font-bold" id="demo-name-label">Consumer Name</span>
                        <div class="font-bold text-text-primary" id="demo-consumer-name">Arjun Sharma</div>
                    </div>
                    <div>
                        <span class="text-text-secondary text-[10px] uppercase font-bold">Amount Due</span>
                        <div class="font-black text-base text-primary font-mono" id="demo-bill-amount">₹ 1,420.00</div>
                    </div>
                    <div class="col-span-2 mt-1">
                        <span class="text-text-secondary text-[10px] uppercase font-bold">Provider</span>
                        <div class="font-bold text-text-primary text-xs" id="demo-provider-name">Tata Power Delhi</div>
                    </div>
                </div>
                <div class="text-[11px] text-text-secondary mt-3">
                    Direct one-click settlement will be live upon full BBPS production rollout. Join the waitlist for priority auto-debit!
                </div>
            </div>
        </div>

        <div class="pt-4 border-t border-border-subtle mt-4 text-xs text-text-secondary flex items-center gap-2">
            <span class="material-symbols-outlined text-sm text-primary">security</span>
            <span>Payments settled securely under the regulatory guidelines of the Reserve Bank of India (RBI).</span>
        </div>
    </div>

    <!-- Benefits Showcase -->
    <div class="lg:col-span-6 ds-card p-6 border border-border-subtle flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-headline font-bold text-xl text-text-primary">Network Highlights</h3>
                    <p class="text-xs text-text-secondary">What you can expect with Finzo Bharat BillPay.</p>
                </div>
            </div>

            <div class="space-y-4">
                <div class="p-4 rounded-xl border border-border-subtle bg-surface/50 flex items-start gap-3.5">
                    <div class="w-9 h-9 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">
                        <span class="material-symbols-outlined text-lg">calendar_clock</span>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-text-primary">Automated Due-Date Auto-Debit</h4>
                        <p class="text-xs text-text-secondary mt-0.5 leading-relaxed">Set monthly payment ceilings and let Finzo automatically pay recurring utility bills on time, avoiding late payment fines.</p>
                    </div>
                </div>

                <div class="p-4 rounded-xl border border-border-subtle bg-surface/50 flex items-start gap-3.5">
                    <div class="w-9 h-9 rounded-lg bg-blue-500/10 text-blue-500 flex items-center justify-center shrink-0 mt-0.5">
                        <span class="material-symbols-outlined text-lg">receipt</span>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-text-primary">Centralized Expense Statements</h4>
                        <p class="text-xs text-text-secondary mt-0.5 leading-relaxed">All electricity, gas, and broadband invoices aggregated in one verifiable PDF statement for simple accounting and tax filing.</p>
                    </div>
                </div>

                <div class="p-4 rounded-xl border border-border-subtle bg-surface/50 flex items-start gap-3.5">
                    <div class="w-9 h-9 rounded-lg bg-purple-500/10 text-purple-500 flex items-center justify-center shrink-0 mt-0.5">
                        <span class="material-symbols-outlined text-lg">notifications</span>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-text-primary">Instant WhatsApp &amp; SMS Alerts</h4>
                        <p class="text-xs text-text-secondary mt-0.5 leading-relaxed">Receive instant transaction reference numbers (BBPS-UTR) with real-time delivery confirmation straight to your mobile.</p>
                    </div>
                </div>
            </div>
        </div>

        <button id="btn-pre-register-biller" class="mt-6 btn btn-secondary btn-full flex items-center justify-center gap-2">
            <span>Register for Zero Convenience Fees</span>
            <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
        </button>
    </div>
</div>

<script defer src="/Finzo/public/assets/js/user/user-bill-payments.js"></script>

<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
