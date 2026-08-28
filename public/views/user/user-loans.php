<?php
$portal = 'user';
$pageTitle = 'Loans & Credit Services | Finzo Banking';
ob_start();
?>

<!-- Header -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8 ds-animate-fade">
    <div>
        <div class="text-xs font-bold text-text-secondary uppercase tracking-widest mb-1">Financial Products</div>
        <h1 class="font-display text-3xl font-extrabold text-text-primary tracking-tight">Loans &amp; Credit Services</h1>
        <p class="text-text-secondary text-sm mt-1">Manage active credit facilities, calculate monthly EMIs, and apply for flexible personal or business loans.</p>
    </div>
    <div class="flex items-center gap-3">
        <button onclick="openApplyLoanModal()" class="btn btn-primary shadow-lg hover:shadow-primary/25 transition-all">
            <span class="material-symbols-outlined text-[20px]">add_card</span>
            <span>Apply for New Loan</span>
        </button>
    </div>
</div>

<!-- Interactive EMI Calculator Section -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8 ds-animate-slide-up">
    <!-- Calculator Form Controls -->
    <div class="lg:col-span-2 ds-card p-6 border border-border-subtle bg-surface/80 backdrop-blur-md">
        <div class="flex items-center gap-2 mb-6">
            <div class="w-9 h-9 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                <span class="material-symbols-outlined text-xl">calculate</span>
            </div>
            <div>
                <h3 class="font-headline font-bold text-lg text-text-primary">Interactive EMI Calculator</h3>
                <p class="text-xs text-text-secondary">Estimate your monthly installment based on principal amount and tenure.</p>
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

        <button onclick="applyWithCalcValues()" class="mt-6 btn btn-secondary btn-full">
            <span>Apply With These Terms</span>
            <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
        </button>
    </div>
</div>

<!-- Active Loans & Applications -->
<div class="ds-card p-6 border border-border-subtle mb-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h3 class="font-headline font-bold text-xl text-text-primary">My Loan Facilities</h3>
            <p class="text-xs text-text-secondary mt-0.5">List of all current, pending, and completed credit facilities associated with your profile.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs text-text-secondary font-bold">Filter:</span>
            <select id="loan-filter-status" onchange="filterLoans()" class="fin-select py-1.5 text-xs">
                <option value="all">All Loans</option>
                <option value="approved">Approved &amp; Active</option>
                <option value="pending">Pending Approval</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
    </div>

    <!-- Loan List Grid / Table -->
    <div id="loans-container" class="space-y-4">
        <div class="py-12 text-center text-text-secondary text-xs font-bold uppercase tracking-wider">
            Loading loan facilities...
        </div>
    </div>
</div>

<!-- Repayment Schedule Modal -->
<div id="schedule-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="ds-card max-w-3xl w-full p-6 bg-surface border border-border-subtle max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between border-b border-border-subtle pb-4 mb-4">
            <div>
                <h3 class="font-headline font-bold text-lg text-text-primary" id="schedule-modal-title">Loan Repayment Schedule</h3>
                <p class="text-xs text-text-secondary">View detailed breakdown of installment dues and principal components.</p>
            </div>
            <button onclick="closeScheduleModal()" class="w-8 h-8 rounded-full hover:bg-surface-hover flex items-center justify-center text-text-secondary">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>
        
        <div class="flex-1 overflow-y-auto custom-scrollbar">
            <table class="w-full text-left text-xs">
                <thead class="border-b border-border-subtle uppercase text-[10px] font-bold text-text-secondary tracking-wider sticky top-0 bg-surface">
                    <tr>
                        <th class="py-3 px-2">#</th>
                        <th class="py-3 px-2">Due Date</th>
                        <th class="py-3 px-2">Amount</th>
                        <th class="py-3 px-2">Principal</th>
                        <th class="py-3 px-2">Interest</th>
                        <th class="py-3 px-2">Status</th>
                        <th class="py-3 px-2 text-right">Action</th>
                    </tr>
                </thead>
                <tbody id="schedule-tbody" class="divide-y divide-border-subtle">
                    <!-- Schedule Rows -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Apply Loan Modal -->
<div id="apply-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="ds-card max-w-lg w-full p-6 bg-surface border border-border-subtle">
        <div class="flex items-center justify-between border-b border-border-subtle pb-4 mb-4">
            <h3 class="font-headline font-bold text-lg text-text-primary">Apply for Credit Facility</h3>
            <button onclick="closeApplyModal()" class="w-8 h-8 rounded-full hover:bg-surface-hover flex items-center justify-center text-text-secondary">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>

        <form id="loan-apply-form" onsubmit="submitLoanApplication(event)" class="space-y-4">
            <div>
                <label class="fin-label fin-label-required">Loan Product</label>
                <select id="apply-product" class="fin-select w-full" required>
                    <option value="">Select a loan product...</option>
                </select>
            </div>

            <div>
                <label class="fin-label fin-label-required">Loan Amount (₹)</label>
                <input type="number" id="apply-amount" class="fin-input w-full" min="5000" max="5000000" step="5000" required placeholder="Enter amount" />
            </div>

            <div>
                <label class="fin-label fin-label-required">Tenure (Months)</label>
                <input type="number" id="apply-tenure" class="fin-input w-full" min="3" max="120" step="1" required placeholder="Enter tenure in months" />
            </div>

            <div>
                <label class="fin-label fin-label-required">Purpose of Loan</label>
                <textarea id="apply-purpose" class="fin-input w-full" rows="3" required placeholder="Provide purpose for credit request (e.g. Education, Business Expansion, Personal)"></textarea>
            </div>

            <div class="pt-4 flex justify-end gap-3 border-t border-border-subtle">
                <button type="button" onclick="closeApplyModal()" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Submit Application</button>
            </div>
        </form>
    </div>
</div>

<script defer src="/Finzo/public/assets/js/user/user-loans.js"></script>

<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
