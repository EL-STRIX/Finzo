<?php
$portal = 'admin';
$pageTitle = 'Suspended Staff';
$pageHeader = '
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-headline font-black text-text-primary tracking-tight">Suspended Staff</h1>
        <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-0.5">Review and manage suspended staff accounts</p>
    </div>
</div>
';
ob_start();
?>
<!-- TABLE CARD -->
      <div class="ds-card overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-border-subtle flex justify-between items-center bg-background">
            <h3 class="font-bold text-[10px] uppercase tracking-widest text-text-secondary">Suspended Personnel Audit</h3>
            <a href="admin-staff.php" class="text-[10px] font-black text-primary hover:underline uppercase tracking-widest">Full Staff Audit</a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 items-end gap-4 p-4 sm:p-6 bg-background border-b border-border-subtle">
          <div>
            <div class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-2 ml-1">Search Staff</div>
            <div class="relative">
              <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-secondary text-lg" aria-hidden="true">search</span>
              <input type="text" id="search-input" class="fin-input pl-10" placeholder="Name, staff ID...">
            </div>
          </div>
          <div>
            <div class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-2 ml-1">Start Date</div>
            <input type="date" id="start-date" class="fin-input">
          </div>
          <div>
            <div class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-2 ml-1">End Date</div>
            <input type="date" id="end-date" class="fin-input">
          </div>
          <div>
            <div class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-2 ml-1">Reason</div>
            <div class="finzo-dropdown w-full" id="reason-dropdown">
              <input type="hidden" id="reason-filter" value="">
              <div class="finzo-dropdown-trigger fin-input !flex items-center justify-between cursor-pointer w-full">
                <div class="flex items-center gap-2">
                  <span class="material-symbols-outlined text-lg text-text-secondary" aria-hidden="true">filter_alt</span>
                  <span class="dropdown-label font-bold text-sm text-text-primary">All Reasons</span>
                </div>
                <span class="material-symbols-outlined chevron transition-transform duration-300" aria-hidden="true">expand_more</span>
              </div>
              <div class="finzo-dropdown-menu z-50">
                <div class="finzo-dropdown-option is-selected" data-value="">All Reasons</div>
                <div class="finzo-dropdown-option" data-value="Unauthorized Data Access">Unauthorized Data Access</div>
                <div class="finzo-dropdown-option" data-value="Financial Discrepancy Found">Financial Discrepancy Found</div>
                <div class="finzo-dropdown-option" data-value="Policy Violation / Misconduct">Policy Violation / Misconduct</div>
                <div class="finzo-dropdown-option" data-value="Performance Related Termination">Performance Related Termination</div>
                <div class="finzo-dropdown-option" data-value="Voluntary Resignation">Voluntary Resignation</div>
                <div class="finzo-dropdown-option" data-value="Security Breach Suspected">Security Breach Suspected</div>
                <div class="finzo-dropdown-option" data-value="Other">Other</div>
              </div>
            </div>
          </div>
        </div>
        <div class="ds-table-wrapper border-0 rounded-none shadow-none bg-transparent">
          <table class="ds-table w-full min-w-[1300px] text-left">
            <thead scope="col">
              <tr>
                <th class="w-12" scope="col">#</th>
                <th scope="col">Staff</th>
                <th class="w-24" scope="col">ID</th>
                <th class="w-32" scope="col">Branch</th>
                <th scope="col">Reason</th>
                <th class="w-40" scope="col">Date</th>
                <th class="w-32" scope="col">By</th>
                <th class="w-24" scope="col">Duration</th>
                <th class="text-center px-6" scope="col">Actions</th>
              </tr>
            </thead>
            <tbody id="table-body">
              <tr><td colspan="9"><div class="skeleton-row"></div></td></tr>
              <tr><td colspan="9"><div class="skeleton-row"></div></td></tr>
              <tr><td colspan="9"><div class="skeleton-row"></div></td></tr>
              <tr><td colspan="9"><div class="skeleton-row"></div></td></tr>
              <tr><td colspan="9"><div class="skeleton-row"></div></td></tr>
            </tbody>
          </table>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-center sm:justify-between gap-6 p-4 sm:p-6 bg-background border-t border-border-subtle">
          <span class="text-xs font-bold tracking-widest text-text-secondary uppercase text-center sm:text-left w-full sm:w-auto" id="pag-info">Loading...</span>
          <div class="flex items-center justify-center" id="pag-controls"></div>
        </div>
      </div>
<?php
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/suspended-staff.js"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>