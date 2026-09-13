<?php
$portal = 'user';
$pageTitle = 'Account Statement';
ob_start();
?>

<!-- PRINT MEDIA STYLES (Strict Black & White Bank Statement Layout Matching Admin Portal) -->
<style>
@media print {
    @page {
        size: A4 portrait;
        margin: 10mm 12mm 12mm 12mm;
    }
    body, html {
        background: #ffffff !important;
        color: #000000 !important;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .print-hidden, .print\:hidden,
    #sidebar, #sidebar-overlay, header, nav,
    .fin-portal-topbar, .app-sidebar, .app-navbar,
    button, input, select, .no-print,
    .filter-section {
        display: none !important;
    }
    main, .app-content, .page-content, .main-content {
        padding: 0 !important;
        margin: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
    }
    .ds-card {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    #statement-preview {
        box-shadow: none !important;
        border: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    #statement-print-area {
        box-shadow: none !important;
        border: 2px solid #000000 !important;
        max-width: 100% !important;
        padding: 14px 16px !important;
        margin: 0 !important;
        background: #ffffff !important;
        color: #000000 !important;
    }
    table {
        page-break-inside: auto !important;
        width: 100% !important;
        border-collapse: collapse !important;
    }
    tr {
        page-break-inside: avoid !important;
        page-break-after: auto !important;
    }
    thead {
        display: table-header-group !important;
    }
}
</style>

<div class="mb-8 ds-animate-fade print:hidden">
  <div class="text-sm font-bold text-text-muted uppercase tracking-wider mb-1">Banking</div>
  <h1 class="font-display text-3xl font-extrabold text-text-main tracking-tight">Account Statement</h1>
  <p class="text-text-muted mt-1 text-sm">Generate, view, and print your official certified transaction ledger and passbook statement.</p>
</div>

<!-- FILTER & ACTIONS CARD -->
<div class="ds-card p-6 mb-8 ds-animate-slide-up print:hidden filter-section">
  <!-- Date Range Inputs and Action Buttons -->
  <div class="flex flex-col lg:flex-row gap-4 items-end justify-between">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 w-full lg:max-w-xl">
      <div>
        <label for="stmt-from" class="block text-xs font-bold text-text-muted uppercase tracking-widest mb-2">From Date</label>
        <input type="date" id="stmt-from" class="form-control h-11 w-full text-sm font-mono cursor-pointer" />
      </div>
      <div>
        <label for="stmt-to" class="block text-xs font-bold text-text-muted uppercase tracking-widest mb-2">To Date</label>
        <input type="date" id="stmt-to" class="form-control h-11 w-full text-sm font-mono cursor-pointer" />
      </div>
    </div>
    
    <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto">
      <button type="button" id="btn-generate-statement" class="btn btn-primary h-11 px-6 flex-1 sm:flex-none flex items-center justify-center gap-1.5 text-xs font-bold rounded-xl shadow-sm cursor-pointer" onclick="generateStatement()">
        <span class="material-symbols-outlined text-lg" aria-hidden="true">filter_alt</span>
        <span>Generate</span>
      </button>
      <button type="button" id="btn-print-statement" class="btn btn-secondary h-11 px-5 flex-1 sm:flex-none flex items-center justify-center gap-1.5 text-xs font-bold rounded-xl shadow-sm cursor-pointer" onclick="printStatement()" title="Print Statement using official bank layout">
        <span class="material-symbols-outlined text-lg text-primary" aria-hidden="true">print</span>
        <span>Print Statement</span>
      </button>
      <button type="button" id="btn-official-pdf" class="btn btn-secondary h-11 px-5 flex-1 sm:flex-none flex items-center justify-center gap-1.5 text-xs font-bold rounded-xl shadow-sm cursor-pointer" onclick="openOfficialStatementPdf()" title="Open official standalone PDF view in new tab">
        <span class="material-symbols-outlined text-lg text-rose-500" aria-hidden="true">picture_as_pdf</span>
        <span>Official PDF</span>
      </button>
    </div>
  </div>

  <!-- Quick Presets -->
  <div class="mt-4 pt-4 border-t border-border-main flex flex-wrap items-center justify-between gap-2">
    <span class="text-xs font-bold text-text-muted uppercase tracking-wider">Statement Period Presets:</span>
    <div class="flex flex-wrap items-center gap-1.5">
      <button type="button" class="stmt-preset-btn px-3 py-1 rounded-lg bg-primary text-white border border-primary font-bold text-[11px] uppercase transition-all cursor-pointer" onclick="setPeriodPreset('30', this)">Last 30 Days</button>
      <button type="button" class="stmt-preset-btn px-3 py-1 rounded-lg bg-surface border border-border-main text-text-muted hover:text-text-main font-bold text-[11px] uppercase transition-all cursor-pointer" onclick="setPeriodPreset('month', this)">This Month</button>
      <button type="button" class="stmt-preset-btn px-3 py-1 rounded-lg bg-surface border border-border-main text-text-muted hover:text-text-main font-bold text-[11px] uppercase transition-all cursor-pointer" onclick="setPeriodPreset('90', this)">Last 90 Days</button>
      <button type="button" class="stmt-preset-btn px-3 py-1 rounded-lg bg-surface border border-border-main text-text-muted hover:text-text-main font-bold text-[11px] uppercase transition-all cursor-pointer" onclick="setPeriodPreset('all', this)">All Time</button>
    </div>
  </div>
</div>

<!-- OFFICIAL BANK STATEMENT & PASSBOOK PREVIEW (Strict Bank Layout Matching Admin Portal) -->
<div class="ds-card ds-animate-slide-up delay-100 overflow-hidden" id="statement-preview">
  <div class="p-3 sm:p-8 bg-slate-100 dark:bg-black/40 print:p-0 print:bg-transparent">
    <div id="statement-print-area" class="bg-white text-black p-5 sm:p-8 max-w-4xl mx-auto border-2 border-black font-mono shadow-sm print:border-2 print:border-black print:p-3 print:shadow-none print:max-w-none">
      
      <!-- Bank Header -->
      <div class="border-b-2 border-black pb-3 mb-4 text-center">
        <h2 class="text-2xl sm:text-3xl font-black tracking-wider uppercase text-black font-serif">FINZO BANK</h2>
        <div class="text-xs font-bold uppercase tracking-widest text-black mt-0.5">SAVINGS / CURRENT ACCOUNT PASSBOOK & STATEMENT</div>
        <div class="flex justify-between items-center text-[11px] font-bold text-black border-t border-black pt-2 mt-2.5">
          <div>BRANCH: MAIN BRANCH, MUMBAI</div>
          <div>IFSC: FINZ0002026</div>
          <div>MICR: 400026002</div>
        </div>
      </div>

      <!-- Account Particulars Box -->
      <table class="w-full border-collapse border border-black text-xs mb-4 text-black font-mono">
        <tbody>
          <tr class="border-b border-black">
            <td class="p-2 border-r border-black font-bold w-1/4 bg-slate-100 text-black">A/C HOLDER NAME</td>
            <td class="p-2 border-r border-black font-black w-1/4 text-black text-sm uppercase" id="pb-full-name">Loading...</td>
            <td class="p-2 border-r border-black font-bold w-1/4 bg-slate-100 text-black">ACCOUNT NO.</td>
            <td class="p-2 font-black w-1/4 text-black text-sm" id="pb-account-no">Loading...</td>
          </tr>
          <tr class="border-b border-black">
            <td class="p-2 border-r border-black font-bold bg-slate-100 text-black">CUSTOMER ID (CIF)</td>
            <td class="p-2 border-r border-black font-bold text-black" id="pb-customer-id">Loading...</td>
            <td class="p-2 border-r border-black font-bold bg-slate-100 text-black">ACCOUNT TYPE</td>
            <td class="p-2 font-bold text-black uppercase" id="pb-account-type">SAVINGS ACCOUNT</td>
          </tr>
          <tr class="border-b border-black">
            <td class="p-2 border-r border-black font-bold bg-slate-100 text-black">MOBILE / EMAIL</td>
            <td class="p-2 border-r border-black font-bold text-black" id="pb-phone">Loading...</td>
            <td class="p-2 border-r border-black font-bold bg-slate-100 text-black">STATEMENT PERIOD</td>
            <td class="p-2 font-bold text-black" id="pb-generated-date">Loading...</td>
          </tr>
          <tr>
            <td class="p-2 border-r border-black font-bold bg-slate-100 text-black">BRANCH / ADDRESS</td>
            <td class="p-2 text-black font-bold" colspan="3" id="pb-address">MAIN BRANCH, MUMBAI • FINZ0002026</td>
          </tr>
        </tbody>
      </table>

      <!-- Summary Metrics Box -->
      <table class="w-full border-collapse border-2 border-black text-xs mb-4 text-black font-mono">
        <tbody>
          <tr class="font-bold">
            <td class="p-2.5 border-r border-black w-1/3 text-center">
              <div class="text-[10px] uppercase">TOTAL WITHDRAWALS (DR)</div>
              <div class="text-sm sm:text-base font-black mt-0.5" id="pb-total-debits">₹ 0.00</div>
            </td>
            <td class="p-2.5 border-r border-black w-1/3 text-center">
              <div class="text-[10px] uppercase">TOTAL DEPOSITS (CR)</div>
              <div class="text-sm sm:text-base font-black mt-0.5" id="pb-total-credits">₹ 0.00</div>
            </td>
            <td class="p-2.5 w-1/3 text-center bg-slate-100 font-black">
              <div class="text-[10px] uppercase">CLOSING AVAILABLE BALANCE</div>
              <div class="text-base sm:text-lg font-black mt-0.5" id="pb-net-balance">₹ 0.00</div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Official Passbook Ledger Table -->
      <div class="mb-4 overflow-x-auto print:overflow-visible">
        <table class="w-full text-left text-xs border-collapse border border-black font-mono text-black">
          <thead>
            <tr class="border-b-2 border-black bg-slate-100 text-black font-black text-[11px] uppercase">
              <th class="p-2 border-r border-black w-10 text-center">LINE</th>
              <th class="p-2 border-r border-black w-24 text-center">DATE</th>
              <th class="p-2 border-r border-black">PARTICULARS / TRANSACTION ID</th>
              <th class="p-2 border-r border-black w-28 text-right">WITHDRAWAL (DR) ₹</th>
              <th class="p-2 border-r border-black w-28 text-right">DEPOSIT (CR) ₹</th>
              <th class="p-2 border-r border-black w-28 text-right">BALANCE ₹</th>
              <th class="p-2 w-14 text-center">INIT</th>
            </tr>
          </thead>
          <tbody id="pb-transactions-body" class="divide-y divide-black text-[11px] text-black">
            <tr><td colspan="7" class="p-8 text-center text-black font-bold">Generating statement ledger...</td></tr>
          </tbody>
        </table>
      </div>

      <!-- Footer Rules & Notice -->
      <div class="text-[10px] text-black leading-tight border-t border-black pt-3 font-mono">
        <p class="font-bold">* Please examine this passbook/statement immediately. Discrepancies, if any, should be reported within 15 days.</p>
        <div class="flex justify-between items-center mt-2 text-[9px] font-bold">
          <span>* OFFICIAL COMPUTER GENERATED BANK STATEMENT — NO PHYSICAL SIGNATURE REQUIRED • FINZO CORE BANKING</span>
          <span>PAGE 1 OF 1</span>
        </div>
      </div>

    </div>
  </div>
</div>

<script defer src="/Finzo/public/assets/js/user/statement.js?v=<?= time() ?>"></script>
<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
