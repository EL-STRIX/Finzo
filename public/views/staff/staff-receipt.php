<?php
$portal = 'staff';
$pageTitle = 'Finzo Staff — Official Transaction Receipt';
ob_start();
?>

<!-- PRINT MEDIA STYLES (Strict Black & White Bank Counter Receipt) -->
<style>
@media print {
    @page {
        size: A4 portrait;
        margin: 10mm 15mm 15mm 15mm;
    }
    body, html {
        background: #ffffff !important;
        color: #000000 !important;
        font-family: "Courier New", Courier, "Lucida Console", Monaco, monospace !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .no-print, .print-hidden,
    #sidebar, #sidebar-overlay, header, nav,
    .fin-portal-topbar, button, a {
        display: none !important;
    }
    .receipt-container-card {
        border: 2px solid #000000 !important;
        box-shadow: none !important;
        background: #ffffff !important;
        color: #000000 !important;
        padding: 16px !important;
        margin: 0 auto !important;
        max-width: 100% !important;
        width: 100% !important;
    }
    * {
        color: #000000 !important;
        border-color: #000000 !important;
        background-color: transparent !important;
    }
    .print-table-bg {
        background-color: #f2f2f2 !important;
    }
}
</style>

<!-- TOP ACTION & NAVIGATION BAR (no-print) -->
<div class="no-print mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-[#181513] border border-slate-200/80 dark:border-white/10 p-4 rounded-2xl shadow-sm">
    <div class="flex items-center gap-3">
        <a href="staff-dashboard.php" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 dark:text-white/70 hover:text-slate-900 dark:hover:text-white px-3 py-2 rounded-xl bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 transition-colors">
            <span class="material-symbols-outlined text-base" aria-hidden="true">arrow_back</span>
            <span>Dashboard</span>
        </a>
        <div class="h-4 w-px bg-slate-200 dark:bg-white/10 hidden sm:block"></div>
        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 dark:bg-white/5 text-slate-700 dark:text-white/80 border border-slate-300 dark:border-white/15">
            <span class="w-2 h-2 rounded-full bg-black dark:bg-white"></span>
            Official Voucher
        </span>
    </div>

    <div class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
        <button type="button" id="btn-print-receipt" class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-black hover:bg-slate-800 text-white dark:bg-white dark:hover:bg-slate-200 dark:text-black font-black text-xs uppercase tracking-wider rounded-xl shadow-md transition-all cursor-pointer">
            <span class="material-symbols-outlined text-base" aria-hidden="true">print</span>
            <span>Print Receipt</span>
            <span class="text-[10px] opacity-75 font-normal ml-0.5">(Ctrl+P)</span>
        </button>
        <a href="staff-transaction.php?type=deposit" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-white/5 dark:hover:bg-white/10 text-slate-800 dark:text-white font-bold text-xs uppercase tracking-wider rounded-xl border border-slate-200 dark:border-white/10 transition-colors">
            <span class="material-symbols-outlined text-base text-slate-600 dark:text-slate-400" aria-hidden="true">add_circle</span>
            <span>Deposit</span>
        </a>
        <a href="staff-transaction.php?type=withdraw" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-white/5 dark:hover:bg-white/10 text-slate-800 dark:text-white font-bold text-xs uppercase tracking-wider rounded-xl border border-slate-200 dark:border-white/10 transition-colors">
            <span class="material-symbols-outlined text-base text-slate-600 dark:text-slate-400" aria-hidden="true">remove_circle</span>
            <span>Withdrawal</span>
        </a>
    </div>
</div>

<!-- OFFICIAL BANKING TRANSACTION VOUCHER (Clean Black & White Design) -->
<div class="max-w-2xl mx-auto">
    <div id="receipt-voucher" class="receipt-container-card bg-white dark:bg-[#151311] border-2 border-black dark:border-white/30 p-6 sm:p-8 font-mono text-black dark:text-white shadow-lg transition-colors">
        
        <!-- BANK HEADER -->
        <div class="border-b-2 border-black dark:border-white/30 pb-4 mb-4 text-center">
            <div class="text-2xl sm:text-3xl font-black uppercase tracking-wider font-serif">Finzo Bank</div>
            <div class="text-xs font-bold uppercase tracking-widest mt-0.5">Official Cash Transaction Voucher</div>
            <div class="text-[11px] font-mono mt-1.5 flex flex-wrap justify-center gap-x-4 gap-y-1 text-slate-700 dark:text-white/80">
                <span>BRANCH: <strong id="r-branch" class="text-black dark:text-white">Finzo Main Branch</strong></span>
                <span>CODE: <strong id="r-branch-code" class="text-black dark:text-white">FNZ-HQ</strong></span>
                <span>IFSC: <strong class="text-black dark:text-white">FINZ0002026</strong></span>
                <span>MICR: <strong class="text-black dark:text-white">400026002</strong></span>
            </div>
        </div>

        <!-- VOUCHER METADATA GRID -->
        <div class="border border-black dark:border-white/20 mb-4 divide-y divide-black dark:divide-white/20 text-xs">
            <div class="grid grid-cols-2 sm:grid-cols-4 divide-x divide-black dark:divide-white/20 bg-slate-50 dark:bg-white/5 font-mono">
                <div class="p-2.5">
                    <div class="text-[9px] uppercase font-bold text-slate-500 dark:text-white/60">Voucher / Txn ID</div>
                    <div class="font-black text-xs sm:text-sm tracking-tight mt-0.5 truncate" id="receipt-ref-id">TXN---</div>
                </div>
                <div class="p-2.5">
                    <div class="text-[9px] uppercase font-bold text-slate-500 dark:text-white/60">Date &amp; Time</div>
                    <div class="font-bold text-xs mt-0.5" id="r-date">---</div>
                </div>
                <div class="p-2.5">
                    <div class="text-[9px] uppercase font-bold text-slate-500 dark:text-white/60">Transaction Nature</div>
                    <div class="font-black uppercase text-xs sm:text-sm mt-0.5" id="r-txn-type">---</div>
                </div>
                <div class="p-2.5">
                    <div class="text-[9px] uppercase font-bold text-slate-500 dark:text-white/60">Channel / Desk</div>
                    <div class="font-bold text-xs mt-0.5">TELLER COUNTER</div>
                </div>
            </div>
        </div>

        <!-- CUSTOMER & ACCOUNT PARTICULARS -->
        <div class="border border-black dark:border-white/20 mb-4 text-xs font-mono">
            <div class="bg-slate-100 dark:bg-white/10 px-3 py-1.5 font-bold uppercase text-[10px] tracking-wider border-b border-black dark:border-white/20">
                Account Holder Particulars
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-black dark:divide-white/20 p-0">
                <div class="p-3">
                    <div class="text-[9px] uppercase font-bold text-slate-500 dark:text-white/60">Customer Name</div>
                    <div class="font-bold text-xs uppercase mt-0.5" id="r-cust-name">---</div>
                </div>
                <div class="p-3">
                    <div class="text-[9px] uppercase font-bold text-slate-500 dark:text-white/60">Account Number</div>
                    <div class="font-black text-xs tracking-wider mt-0.5" id="r-cust-account">---</div>
                </div>
                <div class="p-3">
                    <div class="text-[9px] uppercase font-bold text-slate-500 dark:text-white/60">Account Type</div>
                    <div class="font-bold uppercase text-xs mt-0.5" id="r-cust-account-type">Savings Account</div>
                </div>
            </div>
        </div>

        <!-- FINANCIAL FIGURES & ACCOUNTING BREAKDOWN -->
        <div class="border-2 border-black dark:border-white/30 mb-4 text-xs font-mono">
            <div class="p-4 bg-slate-50 dark:bg-white/5 border-b border-black dark:border-white/20 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                <div>
                    <div class="text-[9px] uppercase font-bold text-slate-500 dark:text-white/60 tracking-wider">Transaction Amount</div>
                    <div class="text-2xl sm:text-3xl font-black tracking-tight mt-0.5" id="r-amount">₹0.00</div>
                </div>
                <div class="text-left sm:text-right">
                    <div class="text-[9px] uppercase font-bold text-slate-500 dark:text-white/60 tracking-wider">Core Status</div>
                    <div class="font-black text-xs uppercase tracking-wider mt-0.5" id="r-status">SUCCESS / POSTED</div>
                </div>
            </div>
            <div class="p-3 border-b border-black dark:border-white/20 bg-white dark:bg-transparent">
                <div class="text-[9px] uppercase font-bold text-slate-500 dark:text-white/60 tracking-wider">Amount in Words</div>
                <div class="font-bold text-xs uppercase tracking-wide mt-0.5" id="r-amount-words">---</div>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 divide-x divide-black dark:divide-white/20 p-0 text-xs">
                <div class="p-2.5">
                    <div class="text-[9px] uppercase font-bold text-slate-500 dark:text-white/60">Previous Balance</div>
                    <div class="font-bold mt-0.5" id="r-prev-balance">₹0.00</div>
                </div>
                <div class="p-2.5">
                    <div class="text-[9px] uppercase font-bold text-slate-500 dark:text-white/60">Closing Available Balance</div>
                    <div class="font-black text-xs mt-0.5" id="r-avail-balance">₹0.00</div>
                </div>
                <div class="col-span-2 sm:col-span-1 p-2.5 border-t sm:border-t-0 border-black dark:border-white/20">
                    <div class="text-[9px] uppercase font-bold text-slate-500 dark:text-white/60">Narration / Ref</div>
                    <div class="font-bold mt-0.5 truncate" id="r-narration">Counter Cash</div>
                </div>
            </div>
        </div>

        <!-- TELLER & OPERATIONAL AUDIT INFO -->
        <div class="border border-black dark:border-white/20 mb-4 p-3 text-xs grid grid-cols-2 sm:grid-cols-3 gap-2 bg-slate-50 dark:bg-white/5 font-mono">
            <div>
                <span class="text-[9px] uppercase font-bold text-slate-500 dark:text-white/60 block">Cashier / Teller:</span>
                <strong id="r-staff" class="text-xs">---</strong>
            </div>
            <div>
                <span class="text-[9px] uppercase font-bold text-slate-500 dark:text-white/60 block">Staff ID:</span>
                <strong id="r-staff-id" class="text-xs">---</strong>
            </div>
            <div>
                <span class="text-[9px] uppercase font-bold text-slate-500 dark:text-white/60 block">Core Validation:</span>
                <strong class="text-xs">FINZO-CORE-AUTH-OK</strong>
            </div>
        </div>

        <!-- DUAL SIGNATURE LINES -->
        <div class="pt-8 pb-3 grid grid-cols-2 gap-8 text-center text-xs font-mono">
            <div>
                <div class="border-b-2 border-black dark:border-white/30 mb-1.5 w-4/5 mx-auto"></div>
                <div class="font-bold uppercase text-[9px] tracking-wider">Customer Signature</div>
            </div>
            <div>
                <div class="border-b-2 border-black dark:border-white/30 mb-1.5 w-4/5 mx-auto"></div>
                <div class="font-bold uppercase text-[9px] tracking-wider">Cashier / Teller Signature</div>
            </div>
        </div>

        <!-- OFFICIAL BANKING NOTICES -->
        <div class="mt-4 pt-3 border-t border-black dark:border-white/20 text-[10px] font-mono text-center text-slate-600 dark:text-white/70 space-y-1 leading-tight">
            <div>* Please verify cash and details before leaving the teller counter. Discrepancies must be notified immediately.</div>
            <div>* This is a computer-generated official banking counter voucher. Official Bank Audit Record.</div>
            <div class="font-bold uppercase tracking-widest text-[9px] pt-1">Finzo Enterprise Core Banking • 24x7 Customer Support: 1800-FINZO-BANK</div>
        </div>
    </div>
</div>

<?php
$slot = ob_get_clean();
$extraScripts = '<script defer src="/Finzo/public/assets/js/staff/staff-receipt.js?v=' . time() . '"></script>';
include __DIR__ . '/../layouts/app.php';
?>
