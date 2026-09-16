<?php
$portal = 'staff';
$pageTitle = 'Finzo Staff';
ob_start();
?>
<!-- Welcome Header -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4">
    <div>
        <h2 class="text-2xl sm:text-3xl font-black font-headline text-text-main dark:text-white flex items-center gap-2" id="welcome-text">
            Loading Dashboard...
        </h2>
        <p class="text-text-muted dark:text-text-muted text-sm mt-1 font-medium flex items-center gap-2">
            <span>Shift Summary &amp; Daily Operations Overview</span>
            <span id="branch-badge" class="hidden px-2 py-0.5 rounded-md bg-surface-subtle dark:bg-white/10 text-text-main dark:text-white text-xs font-bold border border-surface-border dark:border-white/10"></span>
        </p>
    </div>
    <div class="flex items-center gap-2">
        <div id="shift-status-pill" class="flex items-center gap-2 px-3 py-2 bg-white dark:bg-white/5 rounded-xl border border-surface-border dark:border-white/10 text-xs font-bold text-text-muted dark:text-text-muted shadow-sm">
            <span id="shift-status-dot" class="w-2 h-2 rounded-full bg-slate-400"></span>
            <span id="shift-status-text">Checking Shift...</span>
        </div>
        <div class="flex items-center gap-2 px-3.5 py-2 bg-white dark:bg-white/5 rounded-xl border border-surface-border dark:border-white/10 text-xs font-bold text-text-muted dark:text-text-muted shadow-sm">
            <span class="material-symbols-outlined text-base text-warning-base" aria-hidden="true">calendar_today</span>
            <span id="header-date">-- --- ----</span>
        </div>
        <button id="btn-refresh-dashboard" type="button" class="p-2 rounded-xl border border-surface-border dark:border-white/10 bg-white dark:bg-white/5 text-text-muted hover:text-text-main dark:hover:text-white hover:bg-surface-subtle transition-all shadow-sm" title="Refresh Dashboard">
            <span id="refresh-icon" class="material-symbols-outlined text-lg block">refresh</span>
        </button>
    </div>
</div>

<!-- Shift Control Banner -->
<div class="ds-card p-5 border border-surface-border dark:border-white/10 bg-white dark:bg-surface-card flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
    <div class="flex items-center gap-3.5">
        <div id="shift-icon-box" class="w-11 h-11 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
            <span id="shift-icon" class="material-symbols-outlined text-2xl" aria-hidden="true">schedule</span>
        </div>
        <div>
            <div class="flex items-center gap-2">
                <span class="font-headline font-bold text-base text-text-main dark:text-white" id="shift-title">Shift Operations</span>
                <span id="shift-badge" class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-slate-300">Checking...</span>
            </div>
            <p class="text-xs text-text-muted font-medium mt-0.5" id="shift-detail">Loading shift status...</p>
        </div>
    </div>
    <div class="flex items-center gap-2 w-full md:w-auto">
        <button id="btn-clock-in" type="button" class="w-full md:w-auto px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs uppercase tracking-wider shadow-md shadow-emerald-600/20 transition-all flex items-center justify-center gap-1.5 hidden">
            <span class="material-symbols-outlined text-base" aria-hidden="true">login</span>
            <span>Clock In Shift</span>
        </button>
        <button id="btn-clock-out" type="button" class="w-full md:w-auto px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs uppercase tracking-wider shadow-md shadow-rose-600/20 transition-all flex items-center justify-center gap-1.5 hidden">
            <span class="material-symbols-outlined text-base" aria-hidden="true">logout</span>
            <span>End Shift</span>
        </button>
    </div>
</div>

<!-- Quick Actions (High Density) -->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <a href="staff-transaction.php?type=deposit" class="ds-card p-6 flex items-center justify-between group hover:border-success-base/50 transition-all">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-success-surface dark:bg-success-surface/10 text-success-base dark:text-success-base flex items-center justify-center">
                <span class="material-symbols-outlined" aria-hidden="true">add_circle</span>
            </div>
            <div>
                <div class="font-headline font-bold text-lg text-text-main dark:text-white group-hover:text-success-base dark:group-hover:text-success-base transition-colors">Process Deposit</div>
                <div class="text-xs text-text-muted font-medium">Credit customer accounts via cash/cheque</div>
            </div>
        </div>
        <span class="material-symbols-outlined text-text-muted dark:text-text-muted group-hover:text-success-base group-hover:translate-x-1 transition-all" aria-hidden="true">arrow_forward</span>
    </a>
    
    <a href="staff-transaction.php?type=withdraw" class="ds-card p-6 flex items-center justify-between group hover:border-danger-base/50 transition-all">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-danger-surface dark:bg-danger-surface/10 text-danger-base dark:text-danger-base flex items-center justify-center">
                <span class="material-symbols-outlined" aria-hidden="true">remove_circle</span>
            </div>
            <div>
                <div class="font-headline font-bold text-lg text-text-main dark:text-white group-hover:text-danger-base dark:group-hover:text-danger-base transition-colors">Process Withdrawal</div>
                <div class="text-xs text-text-muted font-medium">Debit customer accounts securely</div>
            </div>
        </div>
        <span class="material-symbols-outlined text-text-muted dark:text-text-muted group-hover:text-danger-base group-hover:translate-x-1 transition-all" aria-hidden="true">arrow_forward</span>
    </a>
</div>

<!-- Shift Summary Grid -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="ds-card p-5 border-l-4 border-l-emerald-500">
        <div class="flex items-center justify-between mb-2">
            <div class="text-xs font-black text-text-muted uppercase tracking-widest">Total Deposits</div>
            <span class="material-symbols-outlined text-emerald-500 text-base" aria-hidden="true">arrow_downward</span>
        </div>
        <div class="text-2xl font-black text-success-base dark:text-success-base" id="stat-deposits">₹0.00</div>
        <div class="text-[11px] font-semibold text-text-muted mt-1" id="stat-deposits-count">0 deposits today</div>
    </div>
    <div class="ds-card p-5 border-l-4 border-l-rose-500">
        <div class="flex items-center justify-between mb-2">
            <div class="text-xs font-black text-text-muted uppercase tracking-widest">Total Withdrawals</div>
            <span class="material-symbols-outlined text-rose-500 text-base" aria-hidden="true">arrow_upward</span>
        </div>
        <div class="text-2xl font-black text-danger-base dark:text-danger-base" id="stat-withdrawals">₹0.00</div>
        <div class="text-[11px] font-semibold text-text-muted mt-1" id="stat-withdrawals-count">0 withdrawals today</div>
    </div>
    <div class="ds-card p-5 border-l-4 border-l-amber-500">
        <div class="flex items-center justify-between mb-2">
            <div class="text-xs font-black text-text-muted uppercase tracking-widest">Transactions</div>
            <span class="material-symbols-outlined text-amber-500 text-base" aria-hidden="true">swap_horiz</span>
        </div>
        <div class="text-2xl font-black text-text-main dark:text-white" id="stat-transactions">0</div>
        <div class="text-[11px] font-semibold text-text-muted mt-1">Total ops logged</div>
    </div>
    <div class="ds-card p-5 border-l-4 border-l-blue-500">
        <div class="flex items-center justify-between mb-2">
            <div class="text-xs font-black text-text-muted uppercase tracking-widest">Net Flow</div>
            <span class="material-symbols-outlined text-blue-500 text-base" aria-hidden="true">account_balance_wallet</span>
        </div>
        <div class="text-2xl font-black text-info-base dark:text-primary-base" id="stat-netflow">₹0.00</div>
        <div class="text-[11px] font-semibold text-text-muted mt-1" id="stat-netflow-sub">In-hand variance</div>
    </div>
</div>

<!-- Recent Activity Table (High Density Data Grid) -->
<div class="ds-card overflow-hidden">
    <div class="p-5 border-b border-surface-border dark:border-white/5 flex justify-between items-center bg-surface-subtle/50 dark:bg-white/[0.02]">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-warning-base" aria-hidden="true">receipt_long</span>
            <h3 class="font-headline font-bold text-text-main dark:text-white">Recent Shift Activity</h3>
            <span id="recent-count" class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-warning-surface dark:bg-warning-surface/10 text-warning-base">0 today</span>
        </div>
        <a href="staff-history.php" class="text-xs font-bold text-warning-base dark:text-warning-base hover:underline uppercase tracking-widest flex items-center gap-1">
            <span>View All</span>
            <span class="material-symbols-outlined text-sm" aria-hidden="true">arrow_forward</span>
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="ds-table w-full text-left">
            <thead>
                <tr class="bg-surface-subtle dark:bg-white/5 border-b border-surface-border dark:border-white/5">
                    <th class="p-3 text-xs font-black text-text-muted uppercase tracking-widest" scope="col">Time</th>
                    <th class="p-3 text-xs font-black text-text-muted uppercase tracking-widest" scope="col">Customer / Ref</th>
                    <th class="p-3 text-xs font-black text-text-muted uppercase tracking-widest" scope="col">Account</th>
                    <th class="p-3 text-xs font-black text-text-muted uppercase tracking-widest" scope="col">Type</th>
                    <th class="p-3 text-xs font-black text-text-muted uppercase tracking-widest" scope="col">Amount</th>
                    <th class="p-3 text-xs font-black text-text-muted uppercase tracking-widest text-right" scope="col">Receipt</th>
                </tr>
            </thead>
            <tbody id="txn-tbody" class="divide-y divide-slate-100 dark:divide-white/5">
                <tr>
                    <td colspan="6" class="text-center py-12">
                        <div class="flex flex-col items-center justify-center gap-2">
                            <div class="w-6 h-6 border-2 border-amber-500 border-t-transparent rounded-full animate-spin"></div>
                            <span class="text-xs font-bold text-text-muted">Loading today's shift activity...</span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- END SHIFT / CLOCK OUT MODAL -->
<div id="clockout-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 transition-all">
    <div class="bg-white dark:bg-surface-main border border-surface-border dark:border-white/10 rounded-2xl w-full max-w-md shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <div class="p-5 border-b border-surface-border dark:border-white/5 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                    <span class="material-symbols-outlined text-lg" aria-hidden="true">logout</span>
                </div>
                <h3 class="font-headline font-bold text-lg text-text-main dark:text-white">End Shift Confirmation</h3>
            </div>
            <button type="button" id="btn-close-clockout" class="p-1.5 rounded-lg text-text-muted hover:text-text-main hover:bg-surface-hover transition-colors" aria-label="Close modal">
                <span class="material-symbols-outlined text-xl leading-none">close</span>
            </button>
        </div>
        <form id="clockout-form" class="p-6 space-y-4">
            <div class="bg-surface-subtle/70 dark:bg-white/[0.03] p-4 rounded-xl border border-surface-border dark:border-white/5 space-y-2">
                <div class="flex justify-between items-center text-xs">
                    <span class="text-text-muted font-medium">Clock-in Time:</span>
                    <span id="modal-clockin-time" class="font-bold text-text-main dark:text-white font-mono">—</span>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-text-muted font-medium">Today's Transactions:</span>
                    <span id="modal-shift-txns" class="font-bold text-text-main dark:text-white font-mono">0</span>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-text-muted font-medium">Net Shift Cash:</span>
                    <span id="modal-shift-net" class="font-bold text-text-main dark:text-white font-mono">₹0.00</span>
                </div>
            </div>
            <div>
                <label for="shift-notes" class="block text-xs font-black uppercase tracking-wider text-text-muted mb-1.5">Shift Handover Remarks (Optional)</label>
                <textarea id="shift-notes" name="notes" rows="3" class="w-full px-3.5 py-2.5 rounded-xl border border-surface-border dark:border-white/10 bg-white dark:bg-surface-card text-text-main dark:text-white text-xs placeholder:text-text-muted/60 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all resize-none" placeholder="e.g., Cash drawer balanced with ₹25,000 physical count."></textarea>
            </div>
            <div id="clockout-error" class="hidden text-xs font-bold text-danger-base bg-danger-surface dark:bg-danger-surface/10 p-3 rounded-xl border border-danger-base/20" aria-live="polite"></div>
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-surface-border dark:border-white/5">
                <button type="button" id="btn-cancel-clockout" class="px-4 py-2.5 rounded-xl border border-surface-border text-text-muted hover:text-text-main text-xs font-bold uppercase tracking-wider transition-colors">
                    Cancel
                </button>
                <button type="submit" id="btn-submit-clockout" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-black uppercase tracking-wider shadow-md shadow-rose-600/20 transition-all flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-base" aria-hidden="true">check_circle</span>
                    <span>Confirm Clock Out</span>
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$slot = ob_get_clean();
$extraScripts = '<script defer src="/Finzo/public/assets/js/staff/staff-dashboard.js?v=' . time() . '"></script>';
include __DIR__ . '/../layouts/app.php';
?>
