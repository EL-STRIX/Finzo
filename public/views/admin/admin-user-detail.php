<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin — User Dossier';
ob_start();
?>
  <style>
    @media print {
      body * {
        visibility: hidden !important;
      }
      #passbook-print-area, #passbook-print-area * {
        visibility: visible !important;
      }
      #passbook-print-area {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 20px !important;
        background: white !important;
        color: black !important;
        box-shadow: none !important;
        border: none !important;
      }
      .no-print {
        display: none !important;
      }
    }
  </style>

  <div class="admin-main">
    <div class="space-y-6 sm:space-y-8">
      
      <!-- TOP NAVIGATION -->
      <div class="flex items-center justify-between gap-4 relative z-10">
        <div class="flex items-center gap-4">
          <a href="admin-users.php" class="w-10 h-10 flex items-center justify-center rounded-xl bg-surface border border-border-subtle hover:bg-background transition-all text-text-secondary hover:text-text-primary shadow-sm" aria-label="Back to Users">
            <span class="material-symbols-outlined" aria-hidden="true">arrow_back</span>
          </a>
          <div>
            <div class="text-[10px] font-black tracking-widest uppercase text-text-secondary">USER DOSSIER & PROFILE</div>
            <div id="topbar-user-name" class="text-xl sm:text-2xl font-headline font-black tracking-tight text-text-primary">Loading User Profile...</div>
          </div>
        </div>
      </div>

      <!-- PERSONAL & STATS SECTION -->
      <section class="flex flex-col lg:grid lg:grid-cols-12 gap-6 lg:gap-8">
        <!-- Main Info Card -->
        <div class="lg:col-span-8 ds-card p-5 sm:p-8 transition-all duration-300">
          <div class="flex flex-col sm:flex-row justify-between items-start gap-6 mb-8">
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4 sm:gap-6 text-center sm:text-left w-full sm:w-auto">
              <div class="h-20 w-20 sm:h-24 sm:w-24 rounded-2xl overflow-hidden ring-4 ring-background shadow-lg shrink-0 mx-auto sm:mx-0 bg-surface flex items-center justify-center">
                <div id="user-avatar-placeholder" class="w-full h-full flex items-center justify-center text-3xl font-black text-white">
                  <div class="skeleton w-full h-full"></div>
                </div>
              </div>
              <div>
                <h3 id="user-name-title" class="font-headline text-2xl sm:text-3xl font-black tracking-tight text-text-primary">
                  <div class="skeleton w-48 h-8"></div>
                </h3>
                <div class="flex items-center justify-center sm:justify-start gap-3 mt-2">
                  <span id="user-status-badge" class="text-[10px] font-black px-2.5 py-1 rounded-md uppercase tracking-widest bg-surface border border-border-subtle text-text-secondary">
                    <div class="skeleton w-24 h-4"></div>
                  </span>
                </div>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-y-8 gap-x-8 border-t border-border-subtle pt-8 text-center sm:text-left">
            <!-- Basic Info -->
            <div>
              <p class="fin-label">CUSTOMER ID</p>
              <div id="info-customer-id" class="text-sm font-mono font-bold text-text-primary"><div class="skeleton w-32 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label">FULL NAME</p>
              <div id="info-full-name" class="text-sm font-bold text-text-primary"><div class="skeleton w-32 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label">DATE OF BIRTH</p>
              <div id="info-dob" class="text-sm font-bold text-text-primary"><div class="skeleton w-24 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label">GENDER</p>
              <div id="info-gender" class="text-sm font-bold text-text-primary"><div class="skeleton w-16 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label">MARITAL STATUS</p>
              <div id="info-marital-status" class="text-sm font-bold text-text-primary"><div class="skeleton w-16 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label">NATIONALITY</p>
              <div id="info-nationality" class="text-sm font-bold text-text-primary"><div class="skeleton w-24 h-4"></div></div>
            </div>
            
            <!-- Family & Employment -->
            <div>
              <p class="fin-label">FATHER'S NAME</p>
              <div id="info-father-name" class="text-sm font-bold text-text-primary"><div class="skeleton w-32 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label">MOTHER'S NAME</p>
              <div id="info-mother-name" class="text-sm font-bold text-text-primary"><div class="skeleton w-32 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label">OCCUPATION</p>
              <div id="info-occupation" class="text-sm font-bold text-text-primary"><div class="skeleton w-24 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label">ANNUAL INCOME</p>
              <div id="info-income" class="text-sm font-bold text-text-primary"><div class="skeleton w-20 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label">SOURCE OF INCOME</p>
              <div id="info-source-income" class="text-sm font-bold text-text-primary"><div class="skeleton w-24 h-4"></div></div>
            </div>

            <!-- Contact -->
            <div>
              <p class="fin-label">PHONE NUMBER</p>
              <div id="info-phone" class="text-sm font-bold text-text-primary"><div class="skeleton w-28 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label">EMAIL ADDRESS</p>
              <div id="info-email" class="text-sm font-bold text-text-primary break-all"><div class="skeleton w-40 h-4"></div></div>
            </div>
            
            <!-- Identification -->
            <div>
              <p class="fin-label">IDENTIFICATION ID</p>
              <div id="info-aadhaar" class="text-sm font-mono font-bold tracking-wider text-text-primary"><div class="skeleton w-32 h-4"></div></div>
            </div>

            <!-- Registration Meta -->
            <div>
              <p class="fin-label">SUBMITTED ON</p>
              <div id="info-submitted" class="text-xs font-bold text-text-secondary"><div class="skeleton w-32 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label">REGISTERED ON</p>
              <div id="info-registered" class="text-xs font-bold text-text-secondary"><div class="skeleton w-32 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label">LAST UPDATED</p>
              <div id="info-updated-at" class="text-xs font-bold text-text-secondary"><div class="skeleton w-32 h-4"></div></div>
            </div>

            <!-- Access Meta -->
            <div>
              <p class="fin-label">LAST LOGIN</p>
              <div id="info-last-login" class="text-xs font-bold text-text-secondary"><div class="skeleton w-32 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label">LOGIN IP</p>
              <div id="info-login-ip" class="text-xs font-bold text-text-secondary"><div class="skeleton w-32 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label text-error">FAILED ATTEMPTS</p>
              <div id="info-failed-attempts" class="text-xs font-bold text-error"><div class="skeleton w-8 h-4"></div></div>
            </div>
            <div>
              <p class="fin-label text-error">LOCKED UNTIL</p>
              <div id="info-locked-until" class="text-xs font-bold text-error"><div class="skeleton w-32 h-4"></div></div>
            </div>

            <div id="rejection-reason-container" class="hidden sm:col-span-2 md:col-span-3">
              <p class="fin-label text-error">REJECTION REASON</p>
              <p id="info-rejection-reason" class="text-sm leading-tight text-error font-bold bg-error-bg p-3 rounded-lg border border-error-bg"></p>
            </div>
            
            <!-- Address (Full Width) -->
            <div class="sm:col-span-2 md:col-span-3">
              <p class="fin-label">RESIDENTIAL ADDRESS</p>
              <div id="info-address" class="text-sm font-bold text-text-primary leading-tight"><div class="skeleton w-64 h-4"></div></div>
            </div>
          </div>
        </div>

        <!-- Metric Sidebar -->
        <div class="lg:col-span-4 flex flex-col gap-6">

          <!-- Governance Panel -->
          <div class="ds-card p-6">
            <h5 class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-5 ml-1">ADMIN ACTIONS</h5>
            <div class="space-y-3">
              <button id="suspend-btn" class="w-full flex items-center justify-between p-3.5 bg-background rounded-xl text-sm font-bold hover:bg-warning-bg transition-all border border-border-subtle hover:border-warning text-text-primary group">
                <div class="flex items-center gap-3">
                  <span id="suspend-btn-icon" class="material-symbols-outlined text-warning" aria-hidden="true">block</span>
                  <span id="suspend-btn-text">Deactivate Account</span>
                </div>
                <span class="material-symbols-outlined text-text-secondary group-hover:translate-x-0.5 transition-transform" aria-hidden="true">chevron_right</span>
              </button>
              <button id="delete-btn" class="w-full flex items-center justify-between p-3.5 bg-background rounded-xl text-sm font-bold hover:bg-error-bg transition-all border border-border-subtle hover:border-error text-text-primary group">
                <div class="flex items-center gap-3">
                  <span class="material-symbols-outlined text-error" aria-hidden="true">delete_forever</span>
                  <span>Soft Delete User</span>
                </div>
                <span class="material-symbols-outlined text-text-secondary group-hover:translate-x-0.5 transition-transform" aria-hidden="true">chevron_right</span>
              </button>
            </div>
          </div>

          <!-- Bank Accounts Sidebar -->
          <div class="ds-card p-6">
            <div class="flex items-center justify-between mb-5">
              <h5 class="text-[10px] font-black text-text-secondary uppercase tracking-widest ml-1">BANK ACCOUNTS</h5>
              <span id="accounts-count-badge" class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-surface border border-border-subtle text-text-secondary">0 Accounts</span>
            </div>
            <div id="accounts-sidebar-list" class="space-y-4">
              <div class="skeleton w-full h-24 rounded-2xl"></div>
            </div>
          </div>
        </div>
      </section>

      <!-- KYC & IDENTITY DOCUMENTS SECTION -->
      <section class="ds-card p-5 sm:p-8 relative z-10 shadow-2xl transition-all duration-300">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-border-subtle">
          <div>
            <div class="flex items-center gap-3">
              <h3 class="font-headline text-xl font-black text-text-primary">KYC & Identity Documents</h3>
              <span id="doc-verified-badge" class="px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider rounded-md bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">Verified</span>
            </div>
            <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-1">Official government identification records uploaded during onboarding</p>
          </div>
          <div id="doc-meta-info" class="text-xs font-mono text-text-secondary font-bold">
            <!-- ID info injected by JS -->
          </div>
        </div>

        <div id="documents-grid" class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6">
          <!-- Front Document Card -->
          <div class="bg-background rounded-2xl border border-border-subtle p-5 flex flex-col justify-between">
            <div>
              <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                  <span class="material-symbols-outlined text-primary text-lg" aria-hidden="true">badge</span>
                  <span class="text-xs font-black uppercase tracking-wider text-text-primary">Front Side Document</span>
                </div>
                <span id="front-doc-status" class="text-[10px] font-bold px-2 py-0.5 rounded bg-surface border border-border-subtle text-text-secondary">ID Front</span>
              </div>
              <div id="front-doc-preview" class="aspect-[16/10] w-full rounded-xl overflow-hidden bg-surface border border-border-subtle relative flex items-center justify-center group cursor-pointer">
                <div class="skeleton w-full h-full"></div>
              </div>
            </div>
            <div class="flex items-center justify-between mt-4 pt-3 border-t border-border-subtle/60">
              <span class="text-[11px] font-bold text-text-secondary" id="front-doc-filename">Front Document</span>
              <div class="flex items-center gap-2" id="front-doc-actions">
                <!-- Action buttons injected by JS -->
              </div>
            </div>
          </div>

          <!-- Back Document Card -->
          <div class="bg-background rounded-2xl border border-border-subtle p-5 flex flex-col justify-between">
            <div>
              <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                  <span class="material-symbols-outlined text-primary text-lg" aria-hidden="true">badge</span>
                  <span class="text-xs font-black uppercase tracking-wider text-text-primary">Back Side Document</span>
                </div>
                <span id="back-doc-status" class="text-[10px] font-bold px-2 py-0.5 rounded bg-surface border border-border-subtle text-text-secondary">ID Back</span>
              </div>
              <div id="back-doc-preview" class="aspect-[16/10] w-full rounded-xl overflow-hidden bg-surface border border-border-subtle relative flex items-center justify-center group cursor-pointer">
                <div class="skeleton w-full h-full"></div>
              </div>
            </div>
            <div class="flex items-center justify-between mt-4 pt-3 border-t border-border-subtle/60">
              <span class="text-[11px] font-bold text-text-secondary" id="back-doc-filename">Back Document</span>
              <div class="flex items-center gap-2" id="back-doc-actions">
                <!-- Action buttons injected by JS -->
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- TRANSACTION HISTORY SECTION -->
      <section class="ds-card overflow-hidden mt-6 relative z-10 shadow-2xl transition-all duration-300">
        <div class="p-6 border-b border-border-subtle flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-background/40 backdrop-blur-xl relative z-10">
          <div>
            <h3 class="font-headline text-lg font-black text-text-primary">Transaction History</h3>
            <p class="text-[10px] font-black text-text-secondary uppercase tracking-widest mt-1">Full Ledger Audit for Current User</p>
          </div>
          <div class="flex items-center gap-3">
            <button id="open-passbook-modal-btn" type="button" class="btn btn-primary px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider inline-flex items-center gap-2 shadow-sm hover:scale-[1.02] active:scale-[0.98] transition-transform">
              <span class="material-symbols-outlined text-[18px]" aria-hidden="true">menu_book</span>
              <span>Passbook & Statement</span>
            </button>
          </div>
        </div>
        
        <div class="ds-table-wrapper border-0 rounded-none shadow-none bg-transparent">
          <table class="ds-table w-full text-left min-w-[900px]">
            <thead scope="col">
              <tr>
                <th class="w-16 text-center" scope="col">#</th>
                <th scope="col">TXN ID</th>
                <th scope="col">Type</th>
                <th scope="col">Date & Time</th>
                <th class="text-right" scope="col">Amount</th>
                <th scope="col">Status</th>
              </tr>
            </thead>
            <tbody id="user-transactions-body">
              <tr><td colspan="6" class="py-8 text-center text-xs font-bold text-text-secondary uppercase tracking-widest">Loading transactions...</td></tr>
            </tbody>
          </table>
        </div>
      </section>

    </div>
  </div>

  <!-- PROFESSIONAL BANK PASSBOOK & STATEMENT MODAL -->
  <div id="passbook-modal" class="fixed inset-0 z-[2500] hidden items-center justify-center p-2 sm:p-6 bg-black/80 backdrop-blur-md overflow-y-auto" role="dialog" aria-modal="true">
    <div class="relative w-full max-w-4xl bg-surface rounded-3xl border border-border-subtle shadow-2xl overflow-hidden flex flex-col my-auto max-h-[92vh]">
      
      <!-- Top Modal Bar (No Print) -->
      <div class="p-4 sm:p-5 border-b border-border-subtle flex items-center justify-between bg-background/80 backdrop-blur sticky top-0 z-20 no-print">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl bg-primary/10 text-primary flex items-center justify-center border border-primary/20">
            <span class="material-symbols-outlined text-xl" aria-hidden="true">receipt_long</span>
          </div>
          <div>
            <h3 class="text-base font-black font-headline text-text-primary">Official Account Statement & Passbook</h3>
            <p class="text-[10px] font-bold text-text-secondary uppercase tracking-wider">Authorized Banking Record</p>
          </div>
        </div>
        
        <div class="flex items-center gap-2">
          <button id="print-passbook-action" type="button" class="btn btn-primary px-3.5 py-2 rounded-xl text-xs font-bold uppercase tracking-wider inline-flex items-center gap-1.5 shadow-sm">
            <span class="material-symbols-outlined text-[16px]" aria-hidden="true">print</span>
            <span>Print Passbook</span>
          </button>
          <button id="close-passbook-btn" type="button" class="w-9 h-9 rounded-xl bg-surface border border-border-subtle flex items-center justify-center text-text-secondary hover:text-text-primary transition-colors">
            <span class="material-symbols-outlined text-lg" aria-hidden="true">close</span>
          </button>
        </div>
      </div>

      <!-- Printable Passbook Paper Area -->
      <div class="p-4 sm:p-8 overflow-y-auto bg-slate-100 dark:bg-black/40">
        <div id="passbook-print-area" class="bg-white text-black p-6 sm:p-8 max-w-4xl mx-auto border-2 border-black font-mono shadow-sm">
          
          <!-- Bank Header -->
          <div class="border-b-2 border-black pb-3 mb-4 text-center">
            <h1 class="text-2xl font-black tracking-wider uppercase text-black font-serif">FINZO BANK</h1>
            <div class="text-xs font-bold uppercase tracking-widest text-black mt-0.5">SAVINGS / CURRENT ACCOUNT PASSBOOK</div>
            <div class="flex justify-between items-center text-[11px] font-bold text-black border-t border-black pt-2 mt-2.5">
              <div>BRANCH: MAIN BRANCH, MUMBAI</div>
              <div>IFSC: FINZ0002026</div>
              <div>MICR: 400026002</div>
            </div>
          </div>

          <!-- Account Particulars Box -->
          <table class="w-full border-collapse border border-black text-xs mb-4">
            <tbody>
              <tr class="border-b border-black">
                <td class="p-2 border-r border-black font-bold w-1/4 bg-slate-100 text-black">A/C HOLDER NAME</td>
                <td class="p-2 border-r border-black font-black w-1/4 text-black text-sm uppercase" id="pb-full-name">Account Holder</td>
                <td class="p-2 border-r border-black font-bold w-1/4 bg-slate-100 text-black">ACCOUNT NO.</td>
                <td class="p-2 font-black w-1/4 text-black text-sm" id="pb-account-no">FNZ0000000000</td>
              </tr>
              <tr class="border-b border-black">
                <td class="p-2 border-r border-black font-bold bg-slate-100 text-black">CUSTOMER ID (CIF)</td>
                <td class="p-2 border-r border-black font-bold text-black" id="pb-customer-id">FNZ-USR</td>
                <td class="p-2 border-r border-black font-bold bg-slate-100 text-black">ACCOUNT TYPE</td>
                <td class="p-2 font-bold text-black uppercase" id="pb-account-type">SAVINGS ACCOUNT</td>
              </tr>
              <tr class="border-b border-black">
                <td class="p-2 border-r border-black font-bold bg-slate-100 text-black">MOBILE NUMBER</td>
                <td class="p-2 border-r border-black font-bold text-black" id="pb-phone">N/A</td>
                <td class="p-2 border-r border-black font-bold bg-slate-100 text-black">DATE ISSUED</td>
                <td class="p-2 font-bold text-black" id="pb-generated-date"><?= date('d-m-Y') ?></td>
              </tr>
              <tr>
                <td class="p-2 border-r border-black font-bold bg-slate-100 text-black">ADDRESS</td>
                <td class="p-2 text-black font-bold" colspan="3" id="pb-address">N/A</td>
              </tr>
            </tbody>
          </table>

          <!-- Official Passbook Ledger -->
          <div class="mb-4 overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse border border-black font-mono">
              <thead>
                <tr class="border-b-2 border-black bg-slate-100 text-black font-black text-[11px] uppercase">
                  <th class="p-2 border-r border-black w-10 text-center">LINE</th>
                  <th class="p-2 border-r border-black w-24 text-center">DATE</th>
                  <th class="p-2 border-r border-black">PARTICULARS / DETAILS</th>
                  <th class="p-2 border-r border-black w-28 text-right">WITHDRAWAL (DR) ₹</th>
                  <th class="p-2 border-r border-black w-28 text-right">DEPOSIT (CR) ₹</th>
                  <th class="p-2 border-r border-black w-28 text-right">BALANCE ₹</th>
                  <th class="p-2 w-14 text-center">INIT</th>
                </tr>
              </thead>
              <tbody id="pb-transactions-body" class="divide-y divide-black text-[11px] text-black">
                <tr><td colspan="7" class="p-4 text-center text-black">No transaction entries found.</td></tr>
              </tbody>
            </table>
          </div>

          <!-- Bottom Totals & Closing Balance Summary Box -->
          <table class="w-full border-collapse border-2 border-black text-xs mb-4 text-black font-mono">
            <tbody>
              <tr class="font-bold">
                <td class="p-2.5 border-r border-black w-1/3 text-center">
                  <div class="text-[10px] uppercase">TOTAL WITHDRAWALS (DR)</div>
                  <div class="text-sm font-black mt-0.5" id="pb-total-debits">₹ 0.00</div>
                </td>
                <td class="p-2.5 border-r border-black w-1/3 text-center">
                  <div class="text-[10px] uppercase">TOTAL DEPOSITS (CR)</div>
                  <div class="text-sm font-black mt-0.5" id="pb-total-credits">₹ 0.00</div>
                </td>
                <td class="p-2.5 w-1/3 text-center bg-slate-100 font-black">
                  <div class="text-[10px] uppercase">CLOSING AVAILABLE BALANCE</div>
                  <div class="text-base font-black mt-0.5" id="pb-net-balance">₹ 0.00</div>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Footer Rules & Notice -->
          <div class="text-[10px] text-black leading-tight border-t border-black pt-3 font-mono">
            <p class="font-bold">* Please examine this passbook immediately. Discrepancies, if any, should be reported within 15 days.</p>
            <div class="flex justify-between items-center mt-2 text-[9px] font-bold">
              <span>* COMPUTER GENERATED PASSBOOK — NO PHYSICAL SIGNATURE REQUIRED</span>
              <span>PAGE 1 OF 1</span>
            </div>
          </div>

        </div>
      </div>

    </div>
  </div>

  <!-- IMAGE LIGHTBOX MODAL -->
  <div id="image-lightbox-modal" class="fixed inset-0 z-[3000] hidden items-center justify-center p-4 bg-black/80 backdrop-blur-md">
    <div class="relative max-w-4xl max-h-[90vh] bg-surface rounded-2xl border border-border-subtle overflow-hidden flex flex-col shadow-2xl">
      <div class="p-4 border-b border-border-subtle flex items-center justify-between bg-background">
        <h4 id="lightbox-title" class="text-sm font-bold text-text-primary font-headline">Document Preview</h4>
        <button id="close-lightbox" class="w-8 h-8 rounded-lg bg-surface flex items-center justify-center text-text-secondary hover:text-text-primary transition-colors">
          <span class="material-symbols-outlined text-lg" aria-hidden="true">close</span>
        </button>
      </div>
      <div class="p-4 overflow-auto flex items-center justify-center bg-black/40 min-h-[300px]">
        <img id="lightbox-img" src="" alt="Full Preview" class="max-w-full max-h-[75vh] object-contain rounded-lg shadow-lg">
      </div>
    </div>
  </div>

  <!-- GOVERNANCE SECURITY MODAL -->
  <div id="governance-modal" class="fixed inset-0 z-[2000] hidden items-center justify-center p-4 bg-black/70 backdrop-blur-sm" role="dialog" aria-modal="true">
    <div class="relative w-full max-w-lg">
      <div class="ds-card !p-0 overflow-hidden shadow-2xl border border-border-subtle bg-surface">
        <!-- Header -->
        <div id="modal-header" class="p-8 pb-4 text-center">
          <div id="modal-icon-container" class="w-16 h-16 rounded-full mx-auto flex items-center justify-center mb-4 transition-all duration-300">
            <span id="modal-icon" class="material-symbols-outlined text-4xl" aria-hidden="true">warning</span>
          </div>
          <h3 id="modal-title" class="font-headline text-2xl font-black text-text-primary">Authorize Action</h3>
          <p id="modal-subtitle" class="text-xs text-text-secondary font-bold mt-1 uppercase tracking-widest">Secure administrative override required</p>
        </div>

        <div class="px-8 py-4 space-y-6">
          <!-- Selection Grid -->
          <div>
            <label class="block text-[10px] font-black uppercase text-text-secondary mb-3 ml-1">Select Primary Reason</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="reason-grid">
              <!-- Reasons will be injected by JS -->
            </div>
          </div>

          <!-- Custom Reason Area -->
          <div id="custom-reason-container" class="hidden">
            <label for="governance-notes" class="block text-[10px] font-black uppercase text-text-secondary mb-3 ml-1">Additional Observations (Optional)</label>
            <textarea id="governance-notes" rows="3" placeholder="Explain the decision for internal auditing..."
              class="fin-input !rounded-2xl"></textarea>
          </div>

          <!-- Auth Step -->
          <div id="auth-step" class="pt-4 border-t border-border-subtle">
            <label for="admin-password" class="block text-[10px] font-black uppercase text-text-secondary mb-3 ml-1">Confirm Identity (Admin Password)</label>
            <div class="relative">
              <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-text-secondary" aria-hidden="true">admin_panel_settings</span>
              <input type="password" id="admin-password" placeholder="••••••••"
                class="fin-input !pl-12 !py-3 !rounded-2xl !text-lg !tracking-widest">
            </div>
            <p id="auth-error" class="text-[10px] font-black text-error uppercase tracking-widest mt-2 ml-1 hidden">Invalid administrative credentials. Access denied.</p>
          </div>
        </div>

        <!-- Footer Actions -->
        <div class="p-8 pt-4 flex gap-3">
          <button id="cancel-governance-btn" class="flex-1 py-3.5 bg-background hover:bg-surface text-text-secondary font-black rounded-2xl text-[10px] uppercase tracking-widest transition-all border border-border-subtle">Abort Action</button>
          <button id="confirm-governance-btn" class="flex-1 py-3.5 bg-primary text-white font-black rounded-2xl text-[10px] uppercase tracking-widest shadow-sm hover:scale-[1.02] active:scale-[0.98] transition-all">Confirm Change</button>
        </div>
      </div>
    </div>
  </div>
<?php
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/user-detail.js"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>