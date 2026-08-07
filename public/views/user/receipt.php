<?php
$portal = 'user';
$pageTitle = 'Transaction Receipt';
ob_start();
?>
<script defer src="/Finzo/public/assets/js/user/user-shared.js"></script>

<div class="main print:!ml-0 print:!p-0">


<div class="page-content py-8 print:py-0" style="max-width: 600px; margin: 0 auto;">
  
  <div class="mb-8 ds-animate-fade text-center print:hidden">
    <div class="w-16 h-16 bg-success-surface text-success-base rounded-full flex items-center justify-center mx-auto mb-4">
      <span class="material-symbols-outlined text-3xl" aria-hidden="true">check_circle</span>
    </div>
    <h1 class="font-display text-2xl font-extrabold text-text-main tracking-tight">Transaction Successful</h1>
    <p class="text-text-muted mt-1">Your transaction has been securely processed.</p>
  </div>

  <div class="flex justify-between items-center mb-4 print:hidden px-4 sm:px-0">
    <a href="transactions.php" class="flex items-center gap-1 text-xs font-bold text-text-muted hover:text-primary-base-base transition-colors uppercase tracking-widest">
      <span class="material-symbols-outlined text-base" aria-hidden="true">arrow_back</span> History
    </a>
    <button class="btn btn-secondary !py-1.5 !px-3" data-action="window.print()">
      <span class="material-symbols-outlined text-base" aria-hidden="true">print</span> Print Receipt
    </button>
  </div>

  <!-- RECEIPT CARD -->
  <div class="ds-card ds-animate-slide-up print:shadow-none print:border-none">
    <div class="p-8 print:p-0">
      
      <!-- Letterhead -->
      <div class="flex justify-between items-start pb-6 border-b-2 border-primary-base print:border-black mb-6">
        <div>
          <div class="font-display text-2xl font-extrabold text-primary-base-base print:text-black">Finzo</div>
          <div class="text-xs font-bold text-text-muted print:text-black uppercase tracking-widest mt-1">Finzo Banking System</div>
        </div>
        <div class="text-right">
          <div class="font-display text-base font-bold text-text-main print:text-black uppercase tracking-wider">Transaction Receipt</div>
          <div class="text-xs font-bold text-text-muted print:text-black mt-2 tracking-widest uppercase" id="rcpt-no">RCP...</div>
        </div>
      </div>
      
      <div class="text-center mb-8 print:hidden">
        <span class="ds-badge ds-badge-success !py-1 !px-3">✅ TRANSACTION SUCCESSFUL</span>
      </div>

      <!-- Core Details -->
      <div class="bg-surface-subtle print:bg-transparent rounded-xl p-5 mb-6 border border-border-main print:border-black">
        <div class="text-xs font-bold text-text-muted print:text-black uppercase tracking-widest mb-4">Transaction Details</div>
        
        <div class="flex justify-between items-center py-2.5 border-b border-border-main print:border-black/20">
          <span class="text-xs font-bold text-text-muted print:text-black">Transaction ID</span>
          <span class="font-mono text-xs font-bold text-text-main print:text-black tracking-wider break-all text-right max-w-[60%]" id="rcpt-txn-id"></span>
        </div>
        
        <div class="flex justify-between items-center py-2.5 border-b border-border-main print:border-black/20">
          <span class="text-xs font-bold text-text-muted print:text-black">Date & Time</span>
          <span class="text-xs font-bold text-text-main print:text-black text-right" id="rcpt-datetime"></span>
        </div>
        
        <div class="flex justify-between items-center py-2.5 border-b border-border-main print:border-black/20">
          <span class="text-xs font-bold text-text-muted print:text-black">Transaction Type</span>
          <span id="rcpt-type" class="print:text-black print:font-bold text-xs uppercase text-right"></span>
        </div>
        
        <div class="flex justify-between items-center py-2.5">
          <span class="text-xs font-bold text-text-muted print:text-black">Description</span>
          <span class="text-xs font-bold text-text-main print:text-black text-right" id="rcpt-desc"></span>
        </div>
      </div>

      <!-- Parties -->
      <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="bg-surface-subtle print:bg-transparent rounded-xl p-4 border border-border-main print:border-black">
          <div class="text-xs font-bold text-text-muted print:text-black uppercase tracking-widest mb-2">From</div>
          <div class="text-sm font-bold text-text-main print:text-black break-words" id="rcpt-from-name"></div>
          <div class="font-mono text-xs font-bold text-text-muted print:text-black tracking-widest mt-1 break-all" id="rcpt-from-acc"></div>
        </div>
        <div class="bg-surface-subtle print:bg-transparent rounded-xl p-4 border border-border-main print:border-black">
          <div class="text-xs font-bold text-text-muted print:text-black uppercase tracking-widest mb-2">To</div>
          <div class="text-sm font-bold text-text-main print:text-black break-words" id="rcpt-to-name"></div>
          <div class="font-mono text-xs font-bold text-text-muted print:text-black tracking-widest mt-1 break-all" id="rcpt-to-acc"></div>
        </div>
      </div>

      <!-- Amount -->
      <div class="bg-primary-surface/30 print:bg-transparent rounded-xl p-6 text-center mb-6 border border-primary-base/20 print:border-black">
        <div class="text-xs font-bold text-primary-base-base print:text-black uppercase tracking-widest mb-2">Settlement Value</div>
        <div class="font-display text-4xl font-black text-text-main print:text-black" id="rcpt-amount"></div>
      </div>

      <!-- Breakdown -->
      <div class="bg-surface-subtle print:bg-transparent rounded-xl p-5 mb-8 border border-border-main print:border-black">
        <div class="flex justify-between items-center py-2 border-b border-border-main print:border-black/20">
          <span class="text-xs font-bold text-text-muted print:text-black">Principal Amount</span>
          <span class="text-xs font-bold text-text-main print:text-black" id="rcpt-amount2"></span>
        </div>
        <div class="flex justify-between items-center py-2 border-b border-border-main print:border-black/20">
          <span class="text-xs font-bold text-text-muted print:text-black">Transfer Fee</span>
          <span class="text-xs font-bold text-success-base print:text-black">₹0.00 (Free)</span>
        </div>
        <div class="flex justify-between items-center pt-3 mt-1">
          <span class="text-xs font-black text-text-main print:text-black uppercase tracking-wider">Balance After</span>
          <span class="text-sm font-black text-text-main print:text-black" id="rcpt-balance-after"></span>
        </div>
      </div>

      <!-- Footer -->
      <div class="text-center border-t border-border-main print:border-black pt-6">
        <div class="text-xs font-bold text-text-muted print:text-black tracking-widest uppercase mb-1">Thank you for banking with Finzo.</div>
        <div class="text-xs text-text-disabled print:text-black">This is a computer-generated receipt. No signature required.</div>
      </div>

    </div>
  </div>
  
  <div class="flex flex-col sm:flex-row gap-3 mt-6 print:hidden ds-animate-slide-up" style="animation-delay: 0.2s;">
    <a href="dashboard.php" class="btn btn-secondary flex-1 h-12">Return to Dashboard</a>
    <a href="transfer.php" class="btn btn-primary flex-1 h-12">New Transfer</a>
  </div>
  
</div>
</div>


<script defer src="/Finzo/public/assets/js/core/utils.js"></script>
  <script defer src="/Finzo/public/assets/js/core/api.js"></script>
<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>