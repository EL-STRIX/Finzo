<?php
$portal = 'user';
$pageTitle = 'Fixed Deposits & Savings Investments | Finzo Banking';
ob_start();
?>

<!-- Header -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8 ds-animate-fade">
    <div>
        <div class="text-xs font-bold text-text-secondary uppercase tracking-widest mb-1">Financial Products</div>
        <h1 class="font-display text-3xl font-extrabold text-text-primary tracking-tight">Fixed Deposits &amp; Investments</h1>
        <p class="text-text-secondary text-sm mt-1">Grow your wealth with high-yield Term Deposits (FDs), auto-renewal options, and digital certificates.</p>
    </div>
    <div class="flex items-center gap-3">
        <button onclick="openCreateFDModal()" class="btn btn-primary shadow-lg hover:shadow-primary/25 transition-all">
            <span class="material-symbols-outlined text-[20px]">savings</span>
            <span>Open New Fixed Deposit</span>
        </button>
    </div>
</div>

<!-- Portfolio KPI Summary -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8 ds-animate-slide-up">
    <div class="ds-card p-5 border border-border-subtle bg-surface/80 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-2xl">account_balance</span>
        </div>
        <div>
            <div class="text-[10px] font-bold text-text-secondary uppercase tracking-wider">Total FD Portfolio</div>
            <div id="stat-total-fd-amount" class="text-2xl font-black font-display text-text-primary">₹ 0.00</div>
        </div>
    </div>

    <div class="ds-card p-5 border border-border-subtle bg-surface/80 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-2xl">trending_up</span>
        </div>
        <div>
            <div class="text-[10px] font-bold text-text-secondary uppercase tracking-wider">Est. Interest Earned</div>
            <div id="stat-total-fd-interest" class="text-2xl font-black font-display text-emerald-500">₹ 0.00</div>
        </div>
    </div>

    <div class="ds-card p-5 border border-border-subtle bg-surface/80 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-2xl">workspace_premium</span>
        </div>
        <div>
            <div class="text-[10px] font-bold text-text-secondary uppercase tracking-wider">Active Term Deposits</div>
            <div id="stat-active-fd-count" class="text-2xl font-black font-display text-text-primary">0</div>
        </div>
    </div>

    <div class="ds-card p-5 border border-border-subtle bg-surface/80 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-indigo-500/10 text-indigo-500 flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-2xl">event_upcoming</span>
        </div>
        <div>
            <div class="text-[10px] font-bold text-text-secondary uppercase tracking-wider">Next Maturity Date</div>
            <div id="stat-next-fd-maturity" class="text-sm font-bold font-display text-text-primary mt-1">—</div>
        </div>
    </div>
</div>

<!-- Interactive FD Yield Calculator & Active Deposits Grid -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-8">
    
    <!-- Calculator -->
    <div class="lg:col-span-4 ds-card p-6 border border-border-subtle bg-surface/80 backdrop-blur-md h-fit">
        <div class="flex items-center gap-2 mb-6">
            <div class="w-9 h-9 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                <span class="material-symbols-outlined text-xl">calculate</span>
            </div>
            <div>
                <h3 class="font-headline font-bold text-lg text-text-primary">FD Return Calculator</h3>
                <p class="text-xs text-text-secondary">Quarterly compounding return estimation.</p>
            </div>
        </div>

        <div class="space-y-4">
            <div>
                <label class="text-xs font-bold uppercase tracking-wider text-text-secondary block mb-1">Deposit Amount (₹)</label>
                <input type="number" id="calc-fd-amount" value="50000" min="5000" step="5000" class="fin-input w-full" oninput="calculateFDReturns()" />
            </div>

            <div>
                <label class="text-xs font-bold uppercase tracking-wider text-text-secondary block mb-1">Tenure (Months)</label>
                <select id="calc-fd-tenure" class="fin-select w-full" onchange="calculateFDReturns()">
                    <option value="6">6 Months (6.50% p.a.)</option>
                    <option value="12" selected>12 Months (7.25% p.a.)</option>
                    <option value="24">24 Months (7.75% p.a.)</option>
                    <option value="36">36 Months (8.10% p.a.)</option>
                    <option value="60">60 Months (8.10% p.a.)</option>
                </select>
            </div>

            <div class="p-4 rounded-xl bg-surface-elevated border border-border-subtle space-y-2">
                <div class="flex justify-between text-xs font-bold">
                    <span class="text-text-secondary">Applicable Rate:</span>
                    <span id="calc-fd-rate" class="text-primary font-mono">7.25%</span>
                </div>
                <div class="flex justify-between text-xs font-bold">
                    <span class="text-text-secondary">Est. Interest:</span>
                    <span id="calc-fd-interest" class="text-emerald-500 font-mono">₹ 3,724</span>
                </div>
                <div class="flex justify-between text-sm font-black border-t border-border-subtle pt-2">
                    <span class="text-text-primary">Maturity Value:</span>
                    <span id="calc-fd-maturity" class="text-primary font-mono">₹ 53,724</span>
                </div>
            </div>

            <button onclick="openCreateFDWithCalc()" class="btn btn-primary btn-full mt-2">
                <span>Open Deposit Now</span>
                <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
            </button>
        </div>
    </div>

    <!-- Active FDs List -->
    <div class="lg:col-span-8 ds-card p-6 border border-border-subtle">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="font-headline font-bold text-xl text-text-primary">Active Fixed Deposits</h3>
                <p class="text-xs text-text-secondary">Your current high-yield term deposit portfolio.</p>
            </div>
        </div>

        <div id="fd-container" class="space-y-4">
            <div class="py-12 text-center text-text-secondary text-xs font-bold uppercase tracking-wider">
                Loading investments...
            </div>
        </div>
    </div>
</div>

<!-- Open New FD Modal -->
<div id="create-fd-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="ds-card max-w-lg w-full p-6 bg-surface border border-border-subtle">
        <div class="flex items-center justify-between border-b border-border-subtle pb-4 mb-4">
            <h3 class="font-headline font-bold text-lg text-text-primary">Open New Fixed Deposit</h3>
            <button onclick="closeCreateFDModal()" class="w-8 h-8 rounded-full hover:bg-surface-hover flex items-center justify-center text-text-secondary">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>

        <form id="create-fd-form" onsubmit="submitCreateFD(event)" class="space-y-4">
            <div>
                <label class="fin-label fin-label-required">Deposit Amount (Min ₹5,000)</label>
                <input type="number" id="create-fd-amount" class="fin-input w-full" min="5000" step="1000" required placeholder="Enter deposit amount" />
            </div>

            <div>
                <label class="fin-label fin-label-required">Tenure (Months)</label>
                <select id="create-fd-tenure" class="fin-select w-full" required>
                    <option value="6">6 Months @ 6.50% p.a.</option>
                    <option value="12" selected>12 Months @ 7.25% p.a.</option>
                    <option value="24">24 Months @ 7.75% p.a.</option>
                    <option value="36">36 Months @ 8.10% p.a.</option>
                    <option value="60">60 Months @ 8.10% p.a.</option>
                </select>
            </div>

            <div class="p-4 rounded-xl bg-surface-elevated border border-border-subtle">
                <label class="flex items-center justify-between cursor-pointer">
                    <div>
                        <div class="font-bold text-sm text-text-primary">Auto-Renewal on Maturity</div>
                        <div class="text-xs text-text-secondary">Automatically renew principal and interest for another term.</div>
                    </div>
                    <input type="checkbox" id="create-fd-autorenew" class="fin-checkbox-input" checked />
                </label>
            </div>

            <div class="pt-4 flex justify-end gap-3 border-t border-border-subtle">
                <button type="button" onclick="closeCreateFDModal()" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Confirm &amp; Deposit</button>
            </div>
        </form>
    </div>
</div>

<script defer src="/Finzo/public/assets/js/user/user-investments.js"></script>

<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
