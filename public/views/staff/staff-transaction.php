<?php
$portal = 'staff';
$txnType = isset($_GET['type']) && strtolower($_GET['type']) === 'withdraw' ? 'withdraw' : 'deposit';
$isWithdraw = ($txnType === 'withdraw');
$pageTitle = $isWithdraw ? 'Finzo Staff — Process Withdrawal' : 'Finzo Staff — Process Deposit';
ob_start();
?>
<!-- PAGE HEADER -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4 mb-2">
    <div>
        <h2 class="text-2xl sm:text-3xl font-black font-headline text-text-main dark:text-white tracking-tight" id="page-action-title">
            <?= $isWithdraw ? 'Process Withdrawal' : 'Process Deposit' ?>
        </h2>
        <p class="text-text-muted dark:text-text-muted text-sm mt-1 font-medium" id="page-action-sub">
            <?= $isWithdraw ? 'Debit customer account securely with identity verification.' : 'Credit money into customer account securely.' ?>
        </p>
    </div>
    <a href="staff-dashboard.php" class="flex items-center gap-2 px-4 py-2 bg-surface-hover dark:bg-white/5 rounded-xl border border-surface-border dark:border-white/10 text-xs font-bold text-text-muted dark:text-text-muted hover:bg-surface-border dark:hover:bg-white/10 transition-all">
        <span class="material-symbols-outlined text-base" aria-hidden="true">arrow_back</span>
        Back to Dashboard
    </a>
</div>

<!-- STEP INDICATOR (Compact & Styled) -->
<div class="ds-card p-4 flex items-center justify-between px-6 mb-8 overflow-x-auto">
    <div class="flex items-center gap-3">
        <div class="step-indicator w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs transition-all border bg-warning-base text-white border-warning-base shadow-md shadow-amber-500/20" data-step="1">
            1
        </div>
        <span class="text-xs font-black uppercase tracking-widest text-text-main dark:text-white whitespace-nowrap" id="step-label-1">Find Account</span>
    </div>
    <div class="flex-1 h-px bg-surface-border dark:bg-white/10 mx-4 hidden sm:block"></div>
    <div class="flex items-center gap-3">
        <div class="step-indicator w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs transition-all border bg-surface-subtle dark:bg-white/5 text-text-muted border-surface-border dark:border-white/10" data-step="2">
            2
        </div>
        <span class="text-xs font-black uppercase tracking-widest text-text-muted whitespace-nowrap" id="step-label-2">Details</span>
    </div>
    <div class="flex-1 h-px bg-surface-border dark:bg-white/10 mx-4 hidden sm:block"></div>
    <div class="flex items-center gap-3">
        <div class="step-indicator w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs transition-all border bg-surface-subtle dark:bg-white/5 text-text-muted border-surface-border dark:border-white/10" data-step="3">
            3
        </div>
        <span class="text-xs font-black uppercase tracking-widest text-text-muted whitespace-nowrap" id="step-label-3">Confirm</span>
    </div>
</div>

<!-- STEP 1: FIND ACCOUNT -->
<div id="section-1" class="space-y-6">
    <div class="ds-card p-6 sm:p-8">
        <h3 class="text-lg font-black font-headline text-text-main dark:text-white mb-6 flex items-center gap-3">
            <span class="w-8 h-8 rounded-lg bg-warning-surface dark:bg-warning-surface/10 flex items-center justify-center text-warning-base dark:text-warning-base font-bold">1</span>
            Customer Account Lookup
        </h3>
        
        <form id="lookup-form" class="flex flex-col sm:flex-row gap-4" onsubmit="return false;">
            <div class="flex-1">
                <label for="account-lookup" class="block text-xs font-black text-text-muted uppercase tracking-widest mb-2">Account Number</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-text-muted" aria-hidden="true">search</span>
                    <input type="text" id="account-lookup" placeholder="Enter account number (e.g. FNZ20260001)" 
                        class="w-full fin-input pl-12 h-12" autocomplete="off">
                </div>
            </div>
            <div class="flex items-end">
                <button type="button" id="find-btn" class="btn btn-primary w-full sm:w-auto h-12 px-8 flex items-center justify-center gap-2">
                    <span id="find-btn-text">Find Account</span>
                    <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_forward</span>
                </button>
            </div>
        </form>
    </div>

    <!-- SUCCESS BANNER -->
    <div id="account-success" class="hidden animate-in fade-in duration-300" aria-live="polite">
        <div class="ds-card border-l-4 <?= $isWithdraw ? 'border-l-rose-500' : 'border-l-emerald-500' ?> p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-6 pb-6 border-b border-surface-border dark:border-white/5">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-full <?= $isWithdraw ? 'bg-rose-500/10 text-rose-600 dark:text-rose-400' : 'bg-success-surface dark:bg-success-surface/10 text-success-base dark:text-success-base' ?> flex items-center justify-center text-xl font-black shadow-inner" id="res-initials">--</div>
                    <div>
                        <h4 class="text-xl font-black font-headline text-text-main dark:text-white leading-tight" id="res-name">Loading...</h4>
                        <div class="text-text-muted font-mono text-sm mt-1" id="res-acc">FNZ---</div>
                    </div>
                </div>
                <span class="px-3 py-1 bg-success-surface dark:bg-success-surface/10 text-success-base dark:text-success-base border border-success-base dark:border-success-base/20 rounded-lg text-xs font-black uppercase tracking-widest">Account Active</span>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-surface-subtle dark:bg-white/5 rounded-xl p-4 border border-surface-border dark:border-white/5">
                    <div class="text-xs font-black text-text-muted uppercase tracking-widest mb-1">Account Type</div>
                    <div class="font-bold text-text-main dark:text-white account-type-val">Loading...</div>
                </div>
                <div class="bg-surface-subtle dark:bg-white/5 rounded-xl p-4 border border-surface-border dark:border-white/5">
                    <div class="text-xs font-black text-text-muted uppercase tracking-widest mb-1">Current Balance</div>
                    <div class="font-black text-success-base dark:text-success-base text-lg current-balance-val">₹0.00</div>
                </div>
                <div class="bg-surface-subtle dark:bg-white/5 rounded-xl p-4 border border-surface-border dark:border-white/5">
                    <div class="text-xs font-black text-text-muted uppercase tracking-widest mb-1">Daily Limit Left</div>
                    <div class="font-bold text-text-main dark:text-white daily-limit-val">₹0.00</div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <button type="button" id="btn-search-again" class="flex-1 py-3 btn btn-secondary text-sm">Search Again</button>
                <button type="button" id="btn-proceed-step2" class="flex-1 py-3 btn btn-primary text-sm flex items-center justify-center gap-2">
                    <span id="proceed-step2-text"><?= $isWithdraw ? 'Proceed with Withdrawal' : 'Proceed with Deposit' ?></span>
                    <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_forward</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ERROR BANNER -->
    <div id="account-error" class="hidden ds-card border-l-4 border-l-red-500 p-6 flex items-center gap-4 animate-in fade-in duration-300" aria-live="polite">
        <div class="w-10 h-10 rounded-full bg-danger-surface dark:bg-danger-surface/10 text-danger-base dark:text-danger-base flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined" aria-hidden="true">error</span>
        </div>
        <div>
            <h4 class="text-text-main dark:text-white font-bold" id="error-title">Account Not Found</h4>
            <p class="text-text-muted text-sm" id="error-desc">Please verify the account number and try again.</p>
        </div>
    </div>
</div>

<!-- STEP 2: TRANSACTION DETAILS -->
<div id="section-2" class="hidden space-y-6">
    <div class="ds-card p-6 sm:p-8">
        <h3 class="text-lg font-black font-headline text-text-main dark:text-white mb-6 flex items-center gap-3">
            <span class="w-8 h-8 rounded-lg bg-warning-surface dark:bg-warning-surface/10 flex items-center justify-center text-warning-base dark:text-warning-base font-bold">2</span>
            <span id="step-2-heading"><?= $isWithdraw ? 'Enter Withdrawal Details' : 'Enter Deposit Details' ?></span>
        </h3>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
            <div class="lg:col-span-3 space-y-6">
                <div>
                    <label for="dep-amount" class="block text-xs font-black text-text-muted uppercase tracking-widest mb-2" id="amount-input-label">
                        <?= $isWithdraw ? 'Withdrawal Amount (₹)' : 'Deposit Amount (₹)' ?>
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 font-black text-text-muted text-xl">₹</span>
                        <input type="number" id="dep-amount" placeholder="0.00" min="100" step="any"
                            class="w-full fin-input pl-10 h-16 text-2xl font-black font-mono tracking-tight">
                    </div>
                    <p id="amt-words" class="mt-2 text-xs font-bold text-warning-base dark:text-warning-base uppercase tracking-widest px-1 italic">Waiting for input...</p>
                    <p id="amt-validation-msg" class="hidden mt-1 text-xs font-bold text-danger-base"></p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="dep-type" class="block text-xs font-black text-text-muted uppercase tracking-widest mb-2" id="type-select-label">
                            <?= $isWithdraw ? 'Withdrawal Method' : 'Deposit Method' ?>
                        </label>
                        <select id="dep-type" class="w-full fin-input h-[46px]">
                            <?php if ($isWithdraw): ?>
                                <option value="Cash Withdrawal">Cash Withdrawal</option>
                                <option value="Cheque Encashment">Cheque Encashment</option>
                            <?php else: ?>
                                <option value="Cash Deposit">Cash Deposit</option>
                                <option value="Cheque Deposit">Cheque Deposit</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div>
                        <label for="dep-ref" class="block text-xs font-black text-text-muted uppercase tracking-widest mb-2" id="ref-input-label">
                            <?= $isWithdraw ? 'Identity Verification Note' : 'Reference / Remarks' ?>
                        </label>
                        <input type="text" id="dep-ref" placeholder="<?= $isWithdraw ? 'e.g. ID verified in branch' : 'Optional reference' ?>" class="w-full fin-input h-[46px]">
                    </div>
                </div>
            </div>

            <!-- Dense Live Summary Sidebar -->
            <div class="lg:col-span-2">
                <div class="bg-surface-subtle dark:bg-white/5 rounded-2xl p-6 border border-surface-border dark:border-white/5 sticky top-24">
                    <h4 class="text-text-main dark:text-white font-black uppercase text-xs tracking-widest mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-base text-warning-base" aria-hidden="true">receipt_long</span>
                        Live Summary
                    </h4>
                    <div class="space-y-4 font-mono text-sm">
                        <div class="flex justify-between items-center">
                            <span class="text-text-muted font-sans text-xs">Customer</span>
                            <span class="text-text-main dark:text-white font-bold truncate max-w-[140px]" id="sum-name">--</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-text-muted font-sans text-xs">Account</span>
                            <span class="text-text-main dark:text-white font-bold" id="sum-acc">--</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-text-muted font-sans text-xs">Method</span>
                            <span class="text-text-main dark:text-white font-bold font-sans text-xs" id="sum-type">Cash</span>
                        </div>
                        <div class="h-px bg-surface-border dark:bg-white/10"></div>
                        <div class="flex justify-between items-center">
                            <span class="text-text-muted font-sans text-xs" id="sum-amt-label"><?= $isWithdraw ? 'Debit Amount' : 'Credit Amount' ?></span>
                            <span class="text-lg font-black <?= $isWithdraw ? 'text-danger-base dark:text-danger-base' : 'text-success-base dark:text-success-base' ?>" id="sum-amt">₹0.00</span>
                        </div>
                        <div class="flex justify-between items-center opacity-70">
                            <span class="text-text-muted font-sans text-xs">Bal Before</span>
                            <span class="text-text-main dark:text-white font-bold text-xs" id="sum-before">₹0.00</span>
                        </div>
                        <div class="flex justify-between items-center bg-surface-main dark:bg-white/5 p-2 rounded-lg border border-surface-border dark:border-white/10" id="sum-after-box">
                            <span class="font-sans text-xs font-bold text-text-muted">Bal After</span>
                            <span class="font-black text-sm text-text-main dark:text-white" id="sum-after">₹0.00</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-8 pt-6 border-t border-surface-border dark:border-white/5 flex justify-end gap-3">
            <button type="button" id="btn-back-step1" class="px-6 py-2.5 btn btn-secondary text-sm">Back</button>
            <button type="button" id="btn-proceed-step3" class="px-8 py-2.5 btn btn-primary text-sm flex items-center gap-2">
                Review &amp; Confirm <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_forward</span>
            </button>
        </div>
    </div>
</div>

<!-- STEP 3: CONFIRMATION -->
<div id="section-3" class="hidden space-y-6 animate-in fade-in duration-300">
    <div class="ds-card overflow-hidden">
        <div class="<?= $isWithdraw ? 'bg-gradient-to-r from-rose-600 to-rose-700' : 'bg-gradient-to-r from-emerald-600 to-teal-700' ?> p-8 text-center text-white relative">
            <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4xIi8+PC9zdmc+')] opacity-30"></div>
            <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center mx-auto mb-4 relative z-10 shadow-inner">
                <span class="material-symbols-outlined text-3xl" aria-hidden="true">fact_check</span>
            </div>
            <h3 class="text-2xl font-black font-headline relative z-10" id="confirm-card-title">
                <?= $isWithdraw ? 'Verify Withdrawal Transaction' : 'Verify Deposit Transaction' ?>
            </h3>
            <p class="text-white/80 font-bold text-xs mt-2 uppercase tracking-widest relative z-10">Final Operational Review</p>
        </div>

        <div class="p-6 sm:p-8 space-y-6">
            <div id="deposit-error" class="hidden text-xs font-bold text-danger-base bg-danger-surface dark:bg-danger-surface/10 p-4 rounded-xl border border-danger-base/20" aria-live="polite"></div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-4">
                    <h4 class="text-xs font-black text-text-muted uppercase tracking-widest border-b border-surface-border dark:border-white/5 pb-2">Transaction Details</h4>
                    <div class="flex justify-between items-center">
                        <span class="text-text-muted text-sm font-medium">Account Name</span>
                        <span class="text-text-main dark:text-white font-bold" id="confirm-name">--</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-text-muted text-sm font-medium">Account Number</span>
                        <span class="text-text-main dark:text-white font-mono font-bold" id="confirm-acc">--</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-text-muted text-sm font-medium">Method</span>
                        <span class="text-text-main dark:text-white font-bold" id="confirm-type">--</span>
                    </div>
                    <div class="flex justify-between items-center bg-surface-subtle dark:bg-white/5 p-3 rounded-xl border border-surface-border dark:border-white/5 mt-2">
                        <span class="text-text-main dark:text-text-muted font-bold" id="confirm-amt-label">
                            <?= $isWithdraw ? 'Total Withdrawal' : 'Total Deposit' ?>
                        </span>
                        <span class="text-2xl font-black <?= $isWithdraw ? 'text-danger-base' : 'text-success-base' ?> font-mono tracking-tight" id="confirm-amt">₹0.00</span>
                    </div>
                </div>

                <div class="space-y-4">
                    <h4 class="text-xs font-black text-text-muted uppercase tracking-widest border-b border-surface-border dark:border-white/5 pb-2">Audit Trail</h4>
                    <div class="grid grid-cols-2 gap-4 bg-surface-subtle dark:bg-white/5 p-4 rounded-xl border border-surface-border dark:border-white/5">
                        <div>
                            <div class="text-[10px] text-text-muted uppercase font-black">Processed By</div>
                            <div class="font-bold text-text-main dark:text-white text-xs staff-name-display truncate mt-0.5">--</div>
                        </div>
                        <div>
                            <div class="text-[10px] text-text-muted uppercase font-black">Staff ID</div>
                            <div class="font-bold text-text-main dark:text-white text-xs font-mono staff-id-display mt-0.5">--</div>
                        </div>
                        <div>
                            <div class="text-[10px] text-text-muted uppercase font-black">Branch</div>
                            <div class="font-bold text-text-main dark:text-white text-xs badge badge-neutral truncate mt-0.5">--</div>
                        </div>
                        <div>
                            <div class="text-[10px] text-text-muted uppercase font-black">Date</div>
                            <div class="font-bold text-text-main dark:text-white text-xs mt-0.5" id="current-date-display">--</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="pt-6 border-t border-surface-border dark:border-white/5 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-2 text-xs font-bold text-text-muted">
                    <span class="material-symbols-outlined text-base" aria-hidden="true">info</span>
                    <span><?= $isWithdraw ? 'Ensure customer identity has been verified before payout.' : 'Double check physical cash before confirming deposit.' ?></span>
                </div>
                <div class="flex gap-3 w-full sm:w-auto">
                    <button type="button" id="btn-back-step2" class="flex-1 sm:flex-none px-6 py-2.5 btn btn-secondary text-sm">Edit</button>
                    <button type="button" id="final-deposit-btn" class="flex-1 sm:flex-none px-8 py-2.5 <?= $isWithdraw ? 'bg-rose-600 hover:bg-rose-700 shadow-rose-600/20' : 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/20' ?> text-white font-bold rounded-xl shadow-lg transition-all flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-lg" aria-hidden="true">done_all</span>
                        <span id="final-btn-text"><?= $isWithdraw ? 'Execute Withdrawal' : 'Execute Deposit' ?></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script defer src="/Finzo/public/assets/js/staff/staff-deposit.js"></script>
<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>