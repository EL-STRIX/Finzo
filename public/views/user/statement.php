<?php
$portal = 'user';
$pageTitle = 'Account Statement';
ob_start();
?>
<div class="mb-8 ds-animate-fade print:hidden">
    <div class="text-sm font-bold text-text-muted uppercase tracking-wider mb-1">Banking</div>
    <h1 class="font-display text-3xl font-extrabold text-text-main tracking-tight">Account Statement</h1>
    <p class="text-text-muted mt-1">Generate, view, and print your transaction history.</p>
  </div>

  <!-- FILTER -->
  <div class="ds-card p-6 mb-8 flex flex-col sm:flex-row gap-4 items-end ds-animate-slide-up print:hidden">
    <div class="w-full sm:flex-1">
      <label for="stmt-from" class="block text-xs font-bold text-text-muted uppercase tracking-widest mb-2">From Date</label>
      <input type="date" id="stmt-from" class="form-control h-11 w-full" />
    </div>
    <div class="w-full sm:flex-1">
      <label for="stmt-to" class="block text-xs font-bold text-text-muted uppercase tracking-widest mb-2">To Date</label>
      <input type="date" id="stmt-to" class="form-control h-11 w-full" />
    </div>
    <div class="flex gap-2 w-full sm:w-auto">
      <button class="btn btn-primary h-11 px-8 flex-1 sm:flex-none" data-action="generateStatement()">Generate</button>
      <button class="btn btn-secondary h-11 flex-1 sm:flex-none" data-action="window.print()" title="Print Statement">
        <span class="material-symbols-outlined text-lg" aria-hidden="true">print</span>
      </button>
    </div>
  </div>

  <!-- STATEMENT PREVIEW -->
  <div class="ds-card ds-animate-slide-up print:shadow-none print:border-none delay-100" id="statement-preview">
    <div class="p-8 print:p-0">
      
      <!-- Print Header -->
      <div class="flex justify-between items-start pb-6 border-b-2 border-primary-base print:border-black mb-8">
        <div>
          <div class="font-display text-3xl font-extrabold text-primary-base-base print:text-black">Finzo</div>
          <div class="text-xs font-bold text-text-muted tracking-widest uppercase mt-1 print:text-black">Finzo Banking System</div>
          <div class="text-xs text-text-muted print:text-black">support@finzobank.com</div>
        </div>
        <div class="text-right">
          <div class="font-display text-lg font-bold text-text-main print:text-black uppercase tracking-wider">Account Statement</div>
          <div class="text-xs font-bold text-text-muted print:text-black mt-2 uppercase tracking-widest" id="stmt-period">Period: All Transactions</div>
          <div class="text-xs text-text-muted print:text-black uppercase tracking-widest mt-1">Generated: <span id="stmt-generated"></span></div>
        </div>
      </div>

      <!-- Account Info -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-surface-subtle print:bg-transparent p-6 rounded-xl border border-border-main print:border-black print:rounded-none mb-8">
        <div>
          <div class="text-xs font-bold text-text-muted print:text-black uppercase tracking-widest mb-2">Account Holder</div>
          <div class="font-display text-lg font-bold text-text-main print:text-black" id="stmt-name"><div class="skeleton w-32 h-5"></div></div>
          <div class="text-xs font-bold text-text-muted print:text-black mt-1" id="stmt-email"><div class="skeleton w-40 h-4"></div></div>
        </div>
        <div>
          <div class="text-xs font-bold text-text-muted print:text-black uppercase tracking-widest mb-2">Account Details</div>
          <div class="font-mono text-base font-bold text-text-main print:text-black uppercase tracking-widest" id="stmt-acc-no"><div class="skeleton w-24 h-5"></div></div>
          <div class="text-xs font-bold text-text-muted print:text-black mt-1" id="stmt-acc-type"><div class="skeleton w-20 h-4"></div></div>
        </div>
      </div>

      <!-- Summary Metrics -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="p-5 text-center bg-success-surface/40 print:bg-transparent border border-success-base/20 print:border-black rounded-xl print:rounded-none">
          <div class="text-xs font-bold text-success-base print:text-black uppercase tracking-widest mb-2">Total Credits</div>
          <div class="font-display text-2xl font-black text-success-base print:text-black" id="stmt-credits"><div class="skeleton w-20 h-6 mx-auto"></div></div>
        </div>
        <div class="p-5 text-center bg-danger-surface/40 print:bg-transparent border border-danger-base/20 print:border-black rounded-xl print:rounded-none">
          <div class="text-xs font-bold text-danger-base print:text-black uppercase tracking-widest mb-2">Total Debits</div>
          <div class="font-display text-2xl font-black text-danger-base print:text-black" id="stmt-debits"><div class="skeleton w-20 h-6 mx-auto"></div></div>
        </div>
        <div class="p-5 text-center bg-primary-surface/40 print:bg-transparent border border-primary-base/20 print:border-black rounded-xl print:rounded-none">
          <div class="text-xs font-bold text-primary-base-base print:text-black uppercase tracking-widest mb-2">Closing Balance</div>
          <div class="font-display text-2xl font-black text-primary-base-base print:text-black" id="stmt-closing"><div class="skeleton w-20 h-6 mx-auto"></div></div>
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto print:overflow-visible">
        <table class="ds-table w-full">
          <thead scope="col">
            <tr>
              <th scope="col">Date</th>
              <th scope="col">Transaction ID</th>
              <th scope="col">Description</th>
              <th scope="col">Type</th>
              <th class="text-right" scope="col">Amount</th>
              <th class="text-right" scope="col">Balance</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td colspan="6" class="p-8">
                <div class="skeleton w-full h-8 mb-2"></div>
                <div class="skeleton w-full h-8 mb-2"></div>
                <div class="skeleton w-full h-8"></div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Footer -->
      <div class="mt-12 pt-6 border-t border-border-main print:border-black text-center">
        <div class="text-xs font-bold text-text-muted print:text-black tracking-widest uppercase">This is a computer-generated statement. No signature required.</div>
      </div>
      
    <script defer src="/Finzo/public/assets/js/core/utils.js"></script>
  <script defer src="/Finzo/public/assets/js/core/api.js"></script>
<script defer src="/Finzo/public/assets/js/user/statement.js"></script>
<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>

