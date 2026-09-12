<?php
$portal = 'user';
$pageTitle = 'Loans & Credit Services | Finzo Banking';
ob_start();
?>

<!-- Header -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8 ds-animate-fade">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <span class="text-xs font-bold text-text-secondary uppercase tracking-widest">Financial Products</span>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-primary/10 text-primary border border-primary/20">
                <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></span>
                Coming Soon
            </span>
        </div>
        <h1 class="font-display text-3xl font-extrabold text-text-primary tracking-tight">Loans &amp; Credit Services</h1>
        <p class="text-text-secondary text-sm mt-1">Estimate your monthly installments with our actuarial EMI calculator and explore upcoming flexible credit facilities.</p>
    </div>
    <div class="flex items-center gap-3">
        <button id="btn-user-loan-notify" class="btn btn-primary shadow-lg hover:shadow-primary/25 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">notifications_active</span>
            <span>Join Early Access Waitlist</span>
        </button>
    </div>
</div>

<!-- Interactive EMI Calculator Section (Fully Functional Client-Side) -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8 ds-animate-slide-up">
    <!-- Calculator Form Controls -->
    <div class="lg:col-span-2 ds-card p-6 border border-border-subtle bg-surface/80 backdrop-blur-md">
        <div class="flex items-center gap-2 mb-6">
            <div class="w-9 h-9 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                <span class="material-symbols-outlined text-xl">calculate</span>
            </div>
            <div>
                <h3 class="font-headline font-bold text-lg text-text-primary">Interactive EMI Calculator</h3>
                <p class="text-xs text-text-secondary">Estimate your monthly installment based on principal amount, tenure, and interest rate.</p>
            </div>
        </div>

        <div class="space-y-6">
            <!-- Amount Slider -->
            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="text-xs font-bold uppercase tracking-wider text-text-secondary">Desired Loan Amount</label>
                    <span id="calc-amount-label" class="font-display font-bold text-lg text-primary">₹ 1,00,000</span>
                </div>
                <input type="range" id="calc-amount-slider" min="10000" max="5000000" step="10000" value="100000" class="w-full h-2 bg-border-subtle rounded-lg appearance-none cursor-pointer accent-primary" oninput="updateCalculator()" />
                <div class="flex justify-between text-[10px] text-text-secondary font-bold uppercase tracking-wider mt-1">
                    <span>₹10,000</span>
                    <span>₹50,00,000</span>
                </div>
            </div>

            <!-- Tenure Slider -->
            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="text-xs font-bold uppercase tracking-wider text-text-secondary">Tenure (Months)</label>
                    <span id="calc-tenure-label" class="font-display font-bold text-lg text-text-primary">12 Months</span>
                </div>
                <input type="range" id="calc-tenure-slider" min="6" max="120" step="6" value="12" class="w-full h-2 bg-border-subtle rounded-lg appearance-none cursor-pointer accent-primary" oninput="updateCalculator()" />
                <div class="flex justify-between text-[10px] text-text-secondary font-bold uppercase tracking-wider mt-1">
                    <span>6 Months</span>
                    <span>120 Months</span>
                </div>
            </div>

            <!-- Interest Rate Slider -->
            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="text-xs font-bold uppercase tracking-wider text-text-secondary">Interest Rate (% p.a.)</label>
                    <span id="calc-rate-label" class="font-display font-bold text-lg text-text-primary">10.5 %</span>
                </div>
                <input type="range" id="calc-rate-slider" min="5" max="24" step="0.5" value="10.5" class="w-full h-2 bg-border-subtle rounded-lg appearance-none cursor-pointer accent-primary" oninput="updateCalculator()" />
                <div class="flex justify-between text-[10px] text-text-secondary font-bold uppercase tracking-wider mt-1">
                    <span>5.0%</span>
                    <span>24.0%</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Box -->
    <div class="ds-card p-6 border border-border-subtle bg-gradient-to-br from-primary/5 via-surface to-surface flex flex-col justify-between">
        <div>
            <div class="text-xs font-black text-text-secondary uppercase tracking-widest mb-1">Estimated Output</div>
            <div class="text-2xl font-display font-extrabold text-text-primary mb-6">EMI Summary</div>

            <div class="space-y-4">
                <div class="p-4 rounded-xl bg-surface border border-border-subtle shadow-sm">
                    <div class="text-[10px] font-bold text-text-secondary uppercase tracking-wider mb-1">Monthly Installment (EMI)</div>
                    <div id="calc-emi-result" class="text-2xl font-black font-display text-primary">₹ 8,815</div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="p-3 rounded-lg bg-surface/60 border border-border-subtle">
                        <div class="text-[10px] font-bold text-text-secondary uppercase tracking-wider mb-1">Total Payable</div>
                        <div id="calc-total-payable" class="text-sm font-bold font-display text-text-primary">₹ 1,05,780</div>
                    </div>
                    <div class="p-3 rounded-lg bg-surface/60 border border-border-subtle">
                        <div class="text-[10px] font-bold text-text-secondary uppercase tracking-wider mb-1">Total Interest</div>
                        <div id="calc-total-interest" class="text-sm font-bold font-display text-warning">₹ 5,780</div>
                    </div>
                </div>
            </div>
        </div>

        <button id="btn-calc-waitlist" class="mt-6 btn btn-secondary btn-full flex items-center justify-center gap-2">
            <span>Pre-Register For These Terms</span>
            <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
        </button>
    </div>
</div>

<!-- COMING SOON CREDIT SUITE SHOWCASE -->
<div class="ds-card p-8 border border-border-subtle relative overflow-hidden bg-gradient-to-br from-surface via-surface to-primary/5 mb-8">
    <div class="absolute -right-20 -bottom-20 w-64 h-64 bg-primary/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-2xl mb-8">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary/10 text-primary border border-primary/20 text-xs font-bold uppercase tracking-wider mb-3">
            <span class="material-symbols-outlined text-[16px]">rocket_launch</span>
            Launching Soon
        </div>
        <h2 class="text-2xl font-display font-extrabold text-text-primary tracking-tight mb-2">
            Finzo Digital Credit Facilities
        </h2>
        <p class="text-text-secondary text-sm leading-relaxed">
            We are working on bringing seamless, transparent borrowing directly into your Finzo account. Zero branch visits, paperless automated KYC, instant underwriting, and funds disbursed to your savings account in minutes.
        </p>
    </div>

    <!-- UPCOMING PRODUCT TIERS -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="p-5 rounded-2xl bg-background/60 border border-border-subtle hover:border-primary/40 transition-all">
            <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center mb-3">
                <span class="material-symbols-outlined text-xl">person</span>
            </div>
            <h4 class="font-headline font-bold text-sm text-text-primary mb-1">Instant Personal Loan</h4>
            <p class="text-xs text-text-secondary mb-3 leading-relaxed">Pre-approved credit limits up to ₹5,00,000 for medical, travel, and personal expenses with 0 prepayment charges.</p>
            <span class="text-[11px] font-mono font-bold text-primary">From 10.5% p.a. • 3-36 Months</span>
        </div>

        <div class="p-5 rounded-2xl bg-background/60 border border-border-subtle hover:border-primary/40 transition-all">
            <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-500 flex items-center justify-center mb-3">
                <span class="material-symbols-outlined text-xl">storefront</span>
            </div>
            <h4 class="font-headline font-bold text-sm text-text-primary mb-1">Business Credit Line</h4>
            <p class="text-xs text-text-secondary mb-3 leading-relaxed">Revolving working capital line for entrepreneurs and small businesses with interest charged only on utilized funds.</p>
            <span class="text-[11px] font-mono font-bold text-purple-500">From 11.2% p.a. • Flexible Drawdowns</span>
        </div>

        <div class="p-5 rounded-2xl bg-background/60 border border-border-subtle hover:border-primary/40 transition-all">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center mb-3">
                <span class="material-symbols-outlined text-xl">home</span>
            </div>
            <h4 class="font-headline font-bold text-sm text-text-primary mb-1">Secured Micro-Mortgages</h4>
            <p class="text-xs text-text-secondary mb-3 leading-relaxed">Longer-tenure affordable home renovation and property loans with customized reducing-balance amortization.</p>
            <span class="text-[11px] font-mono font-bold text-emerald-500">From 8.75% p.a. • Up to 120 Months</span>
        </div>
    </div>
</div>

<script defer src="/Finzo/public/assets/js/user/user-loans.js"></script>

<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
