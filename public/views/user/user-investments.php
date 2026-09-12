<?php
$portal = 'user';
$pageTitle = 'Fixed Deposits & Investments | Finzo Banking';
ob_start();
?>

<!-- Header -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8 ds-animate-fade">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <span class="text-xs font-bold text-text-secondary uppercase tracking-widest">Financial Products</span>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                Coming Soon
            </span>
        </div>
        <h1 class="font-display text-3xl font-extrabold text-text-primary tracking-tight">Fixed Deposits &amp; Investments</h1>
        <p class="text-text-secondary text-sm mt-1">Grow your wealth with guaranteed-return Term Deposits, compounding yield options, and digital certificates.</p>
    </div>
    <div class="flex items-center gap-3">
        <button id="btn-fd-waitlist" class="btn btn-primary shadow-lg hover:shadow-primary/25 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">savings</span>
            <span>Join Wealth Early Access</span>
        </button>
    </div>
</div>

<!-- COMING SOON HERO BANNER -->
<div class="ds-card p-8 border border-border-subtle relative overflow-hidden bg-gradient-to-br from-surface via-surface to-emerald-500/5 mb-8 shadow-sm">
    <div class="absolute -right-20 -bottom-20 w-72 h-72 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -left-20 -top-20 w-64 h-64 bg-primary/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative z-10 max-w-3xl">
        <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-5 border border-emerald-500/20 shadow-sm">
            <span class="material-symbols-outlined text-2xl">trending_up</span>
        </div>

        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-xs font-bold uppercase tracking-wider mb-3">
            <span class="material-symbols-outlined text-[16px]">verified</span>
            High-Yield Guaranteed Returns — Launching Soon
        </div>

        <h2 class="text-2xl sm:text-3xl font-display font-extrabold text-text-primary tracking-tight mb-3">
            Smart Digital Term Deposits
        </h2>
        <p class="text-text-secondary text-sm leading-relaxed mb-6">
            We are engineering a zero-friction digital wealth portal. Lock in industry-leading rates, customize auto-renewal mandates, download digitally signed deposit certificates instantly, and liquidate funds on-demand directly into your savings account with zero branch visits.
        </p>

        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-background/80 border border-border-subtle text-xs font-semibold text-text-secondary">
                <span class="material-symbols-outlined text-emerald-500 text-sm">shield</span>
                <span>DICGC Backed up to ₹5,00,000</span>
            </div>
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-background/80 border border-border-subtle text-xs font-semibold text-text-secondary">
                <span class="material-symbols-outlined text-primary text-sm">percent</span>
                <span>Up to 8.40% Annual Yield</span>
            </div>
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-background/80 border border-border-subtle text-xs font-semibold text-text-secondary">
                <span class="material-symbols-outlined text-amber-500 text-sm">sync</span>
                <span>Instant Auto-Renewal Options</span>
            </div>
        </div>
    </div>
</div>

<!-- Interactive FD Yield Calculator -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-8 ds-animate-slide-up">
    
    <!-- Calculator Form Controls -->
    <div class="lg:col-span-7 ds-card p-6 border border-border-subtle bg-surface/80 backdrop-blur-md flex flex-col justify-between">
        <div>
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                    <span class="material-symbols-outlined text-xl">calculate</span>
                </div>
                <div>
                    <h3 class="font-headline font-bold text-lg text-text-primary">Interactive FD Yield Calculator</h3>
                    <p class="text-xs text-text-secondary">Simulate interest earnings with quarterly compounding actuarial math.</p>
                </div>
            </div>

            <div class="space-y-6">
                <!-- Deposit Amount Slider -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-text-secondary">Investment Amount</label>
                        <span id="fd-calc-amount-label" class="font-display font-bold text-lg text-primary">₹ 1,00,000</span>
                    </div>
                    <input type="range" id="fd-calc-amount-slider" min="10000" max="2500000" step="10000" value="100000" class="w-full h-2 bg-border-subtle rounded-lg appearance-none cursor-pointer accent-primary" oninput="updateFDCalculator()" />
                    <div class="flex justify-between text-[10px] text-text-secondary font-bold uppercase tracking-wider mt-1">
                        <span>₹10,000</span>
                        <span>₹25,00,000</span>
                    </div>
                </div>

                <!-- Tenure Selector -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-text-secondary">Tenure Duration</label>
                        <span id="fd-calc-rate-badge" class="px-2 py-0.5 rounded text-xs font-mono font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">7.25% p.a.</span>
                    </div>
                    <select id="fd-calc-tenure-select" class="fin-select w-full" onchange="updateFDCalculator()">
                        <option value="6" data-rate="6.50">6 Months @ 6.50% p.a.</option>
                        <option value="12" data-rate="7.25" selected>12 Months @ 7.25% p.a. (Most Popular)</option>
                        <option value="24" data-rate="7.75">24 Months @ 7.75% p.a.</option>
                        <option value="36" data-rate="8.10">36 Months @ 8.10% p.a.</option>
                        <option value="60" data-rate="8.40">60 Months @ 8.40% p.a. (Special Rate)</option>
                    </select>
                </div>

                <!-- Payout Scheme -->
                <div>
                    <label class="text-xs font-bold uppercase tracking-wider text-text-secondary block mb-2">Interest Payout Frequency</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="p-3 rounded-xl border border-primary/30 bg-primary/5 cursor-pointer flex items-center gap-2.5">
                            <input type="radio" name="payout-type" value="cumulative" checked class="accent-primary" onchange="updateFDCalculator()" />
                            <div>
                                <div class="text-xs font-bold text-text-primary">Cumulative (At Maturity)</div>
                                <div class="text-[10px] text-text-secondary">Quarterly Compounding</div>
                            </div>
                        </label>
                        <label class="p-3 rounded-xl border border-border-subtle bg-surface/50 cursor-pointer flex items-center gap-2.5">
                            <input type="radio" name="payout-type" value="monthly" class="accent-primary" onchange="updateFDCalculator()" />
                            <div>
                                <div class="text-xs font-bold text-text-primary">Regular Payout</div>
                                <div class="text-[10px] text-text-secondary">Monthly Direct Credit</div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-6 border-t border-border-subtle mt-6 text-xs text-text-secondary flex items-center gap-2">
            <span class="material-symbols-outlined text-sm text-primary">info</span>
            <span>Senior citizens (age 60+) will receive an additional 0.50% interest premium across all tenures upon rollout.</span>
        </div>
    </div>

    <!-- Summary Box -->
    <div class="lg:col-span-5 ds-card p-6 border border-border-subtle bg-gradient-to-br from-emerald-500/5 via-surface to-surface flex flex-col justify-between shadow-sm">
        <div>
            <div class="text-xs font-black text-text-secondary uppercase tracking-widest mb-1">Maturity Projection</div>
            <div class="text-2xl font-display font-extrabold text-text-primary mb-6">Yield Summary</div>

            <div class="space-y-4">
                <div class="p-5 rounded-2xl bg-surface border border-border-subtle shadow-sm">
                    <div class="text-[10px] font-bold text-text-secondary uppercase tracking-wider mb-1">Total Maturity Value</div>
                    <div id="fd-calc-maturity-result" class="text-3xl font-black font-display text-emerald-600 dark:text-emerald-400">₹ 1,07,450</div>
                    <div class="text-[11px] font-medium text-text-secondary mt-1">Principal + Total Accumulated Interest</div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="p-4 rounded-xl bg-surface/60 border border-border-subtle">
                        <div class="text-[10px] font-bold text-text-secondary uppercase tracking-wider mb-1">Principal Amount</div>
                        <div id="fd-calc-principal-output" class="text-base font-bold font-display text-text-primary">₹ 1,00,000</div>
                    </div>
                    <div class="p-4 rounded-xl bg-surface/60 border border-border-subtle">
                        <div class="text-[10px] font-bold text-text-secondary uppercase tracking-wider mb-1">Estimated Interest</div>
                        <div id="fd-calc-interest-output" class="text-base font-bold font-display text-emerald-600 dark:text-emerald-400">₹ 7,450</div>
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-surface/60 border border-border-subtle">
                    <div class="flex justify-between items-center text-xs">
                        <span class="font-bold text-text-secondary">Effective Annual Yield:</span>
                        <span id="fd-calc-yield-output" class="font-mono font-bold text-primary">7.45%</span>
                    </div>
                </div>
            </div>
        </div>

        <button id="btn-fd-pre-register" class="mt-6 btn btn-secondary btn-full flex items-center justify-center gap-2">
            <span>Pre-Register For This Yield</span>
            <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
        </button>
    </div>
</div>

<!-- UPCOMING INVESTMENT TIERS -->
<div class="ds-card p-8 border border-border-subtle mb-8">
    <div class="max-w-2xl mb-6">
        <h3 class="text-xl font-display font-bold text-text-primary tracking-tight mb-2">Upcoming Term Deposit Tiers</h3>
        <p class="text-xs text-text-secondary leading-relaxed">
            Tailored deposit solutions engineered for tax efficiency, regular liquidity, and maximum yield compounding.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl bg-background/60 border border-border-subtle hover:border-emerald-500/40 transition-all">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-3">
                <span class="material-symbols-outlined text-xl">savings</span>
            </div>
            <h4 class="font-headline font-bold text-sm text-text-primary mb-1">Smart Flexi-FD</h4>
            <p class="text-xs text-text-secondary mb-3 leading-relaxed">Linked to your savings account. Auto-sweeps idle funds and allows premature withdrawal without break penalty.</p>
            <span class="text-[11px] font-mono font-bold text-emerald-600 dark:text-emerald-400">7.25% p.a. • Instant Liquidity</span>
        </div>

        <div class="p-5 rounded-2xl bg-background/60 border border-border-subtle hover:border-emerald-500/40 transition-all">
            <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center mb-3">
                <span class="material-symbols-outlined text-xl">account_balance</span>
            </div>
            <h4 class="font-headline font-bold text-sm text-text-primary mb-1">Tax-Saver 80C Deposit</h4>
            <p class="text-xs text-text-secondary mb-3 leading-relaxed">Qualifies for tax deduction under Section 80C up to ₹1,50,000 annually with a 5-year lock-in.</p>
            <span class="text-[11px] font-mono font-bold text-blue-500">8.10% p.a. • 5-Year Term</span>
        </div>

        <div class="p-5 rounded-2xl bg-background/60 border border-border-subtle hover:border-emerald-500/40 transition-all">
            <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-500 flex items-center justify-center mb-3">
                <span class="material-symbols-outlined text-xl">calendar_month</span>
            </div>
            <h4 class="font-headline font-bold text-sm text-text-primary mb-1">Monthly Income Scheme</h4>
            <p class="text-xs text-text-secondary mb-3 leading-relaxed">Ideal for retirees and steady income needs. Direct monthly interest payouts credited to your primary account.</p>
            <span class="text-[11px] font-mono font-bold text-purple-500">7.50% p.a. • Monthly Payout</span>
        </div>

        <div class="p-5 rounded-2xl bg-background/60 border border-border-subtle hover:border-emerald-500/40 transition-all">
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center mb-3">
                <span class="material-symbols-outlined text-xl">workspace_premium</span>
            </div>
            <h4 class="font-headline font-bold text-sm text-text-primary mb-1">Senior Privilege FD</h4>
            <p class="text-xs text-text-secondary mb-3 leading-relaxed">Exclusive 0.50% interest bonus across all tenures for individuals aged 60 and above with doorstep branch concierge.</p>
            <span class="text-[11px] font-mono font-bold text-amber-500">Up to 8.90% p.a. Yield</span>
        </div>
    </div>
</div>

<script defer src="/Finzo/public/assets/js/user/user-investments.js"></script>

<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
