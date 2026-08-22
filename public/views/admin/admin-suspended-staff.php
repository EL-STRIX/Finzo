<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin — Suspended Staff';
$pageHeader = '
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1">Personnel Governance</div>
        <h1 class="text-xl sm:text-2xl font-headline font-black text-text-primary tracking-tight">Suspended Staff</h1>
        <p class="text-xs font-bold text-text-secondary mt-0.5">Review, investigate, and manage deactivated or suspended employee terminals</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="admin-staff.php" class="px-4 py-2.5 rounded-xl bg-surface border border-border-subtle hover:bg-surface-hover transition-all text-xs font-black uppercase tracking-wider text-text-primary shadow-sm flex items-center gap-2">
            <span class="material-symbols-outlined text-base" aria-hidden="true">badge</span>
            <span>Active Staff Directory</span>
        </a>
    </div>
</div>
';
ob_start();
?>
<div class="space-y-6">

  <!-- METRIC STATS OVERVIEW -->
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
    <div class="ds-card p-5 sm:p-6 flex flex-col items-start justify-center group relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
      <div class="absolute -right-6 -top-6 w-24 h-24 bg-rose-500/10 rounded-full blur-2xl group-hover:bg-rose-500/20 transition-all"></div>
      <div class="relative z-10 w-full">
        <div class="w-12 h-12 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center mb-4 border border-rose-500/20 shadow-inner">
          <span class="material-symbols-outlined text-2xl" aria-hidden="true">work_off</span>
        </div>
        <div class="text-[10px] font-black text-text-secondary uppercase tracking-[0.15em] mb-1">TOTAL SUSPENDED STAFF</div>
        <div id="stat-total-suspended" class="text-3xl font-black font-display tracking-tighter leading-none text-rose-600 dark:text-rose-400">—</div>
      </div>
    </div>

    <div class="ds-card p-5 sm:p-6 flex flex-col items-start justify-center group relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
      <div class="absolute -right-6 -top-6 w-24 h-24 bg-amber-500/10 rounded-full blur-2xl group-hover:bg-amber-500/20 transition-all"></div>
      <div class="relative z-10 w-full">
        <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-4 border border-amber-500/20 shadow-inner">
          <span class="material-symbols-outlined text-2xl" aria-hidden="true">store</span>
        </div>
        <div class="text-[10px] font-black text-text-secondary uppercase tracking-[0.15em] mb-1">BRANCHES IMPACTED</div>
        <div id="stat-branches-affected" class="text-3xl font-black font-display tracking-tighter leading-none text-text-primary">—</div>
      </div>
    </div>

    <div class="ds-card p-5 sm:p-6 flex flex-col items-start justify-center group relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
      <div class="absolute -right-6 -top-6 w-24 h-24 bg-primary/10 rounded-full blur-2xl group-hover:bg-primary/20 transition-all"></div>
      <div class="relative z-10 w-full">
        <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center mb-4 border border-primary/20 shadow-inner">
          <span class="material-symbols-outlined text-2xl" aria-hidden="true">gavel</span>
        </div>
        <div class="text-[10px] font-black text-text-secondary uppercase tracking-[0.15em] mb-1">GOVERNANCE PROTOCOL</div>
        <div class="text-sm font-black text-text-primary tracking-tight">STRICT AUDIT ENFORCED</div>
      </div>
    </div>
  </div>

  <!-- TABLE CARD -->
  <div class="ds-card overflow-visible relative z-10 shadow-2xl transition-all duration-300">
    <div class="p-4 sm:p-6 border-b border-border-subtle flex flex-wrap justify-between items-center gap-4 bg-background/50 backdrop-blur-xl relative z-10">
      <div>
        <h3 class="font-black text-xs uppercase tracking-widest text-text-primary">Suspended Staff Personnel Log</h3>
        <p class="text-[11px] font-bold text-text-secondary mt-0.5">Terminal privileges and cash counter processing rights currently revoked</p>
      </div>
      <button type="button" id="reset-filters-btn" class="px-3 py-1.5 rounded-lg bg-surface border border-border-subtle hover:bg-surface-hover text-[10px] font-black uppercase tracking-wider text-text-secondary hover:text-text-primary transition-all cursor-pointer flex items-center gap-1.5">
        <span class="material-symbols-outlined text-sm" aria-hidden="true">refresh</span>
        <span>Reset Filters</span>
      </button>
    </div>

    <!-- Filter Controls Bar -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 items-end gap-4 p-4 sm:p-6 bg-background border-b border-border-subtle">
      <!-- Search Input -->
      <div>
        <label for="search-input" class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-2 ml-1 block">Search Staff</label>
        <div class="relative">
          <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-secondary text-lg" aria-hidden="true">search</span>
          <input type="text" id="search-input" class="fin-input !pl-10" placeholder="Name, email, staff ID...">
        </div>
      </div>

      <!-- Start Date -->
      <div>
        <label for="start-date" class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-2 ml-1 block">Suspended From</label>
        <input type="date" id="start-date" class="fin-input">
      </div>

      <!-- End Date -->
      <div>
        <label for="end-date" class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-2 ml-1 block">Suspended To</label>
        <input type="date" id="end-date" class="fin-input">
      </div>

      <!-- Reason Dropdown -->
      <div>
        <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-2 ml-1 block">Suspension Reason</label>
        <div class="finzo-dropdown w-full" id="reason-dropdown">
          <input type="hidden" id="reason-filter" value="">
          <div class="finzo-dropdown-trigger fin-input !flex items-center justify-between cursor-pointer w-full">
            <div class="flex items-center gap-2 truncate">
              <span class="material-symbols-outlined text-lg text-text-secondary" aria-hidden="true">filter_alt</span>
              <span class="dropdown-label font-bold text-sm text-text-primary truncate">All Reasons</span>
            </div>
            <span class="material-symbols-outlined chevron transition-transform duration-300 text-text-secondary shrink-0" aria-hidden="true">expand_more</span>
          </div>
          <div class="finzo-dropdown-menu !z-[3000] !w-full max-h-60 overflow-y-auto">
            <div class="finzo-dropdown-option is-selected" data-value="">All Reasons</div>
            <div class="finzo-dropdown-option" data-value="Unauthorized Operations">Unauthorized Operations</div>
            <div class="finzo-dropdown-option" data-value="Cash / Financial Discrepancy">Cash / Financial Discrepancy</div>
            <div class="finzo-dropdown-option" data-value="Policy Violation / Misconduct">Policy Violation / Misconduct</div>
            <div class="finzo-dropdown-option" data-value="Performance & Compliance Review">Performance &amp; Compliance Review</div>
            <div class="finzo-dropdown-option" data-value="Voluntary Leave / Resignation">Voluntary Leave / Resignation</div>
            <div class="finzo-dropdown-option" data-value="Security Breach Suspected">Security Breach Suspected</div>
            <div class="finzo-dropdown-option" data-value="Other">Other</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="ds-table-wrapper border-0 rounded-none shadow-none bg-transparent overflow-x-auto">
      <table class="ds-table w-full min-w-[1200px] text-left">
        <thead scope="col">
          <tr>
            <th class="w-12 text-center" scope="col">#</th>
            <th scope="col" style="min-width: 240px;">Staff Member</th>
            <th class="w-28" scope="col">Staff ID</th>
            <th class="w-36" scope="col">Branch</th>
            <th scope="col" style="min-width: 200px;">Reason</th>
            <th class="w-44" scope="col">Suspended On</th>
            <th class="w-36" scope="col">Suspended By</th>
            <th class="w-28" scope="col">Duration</th>
            <th class="text-center w-28 px-4" scope="col">Actions</th>
          </tr>
        </thead>
        <tbody id="table-body">
          <tr><td colspan="9"><div class="skeleton-row"></div></td></tr>
          <tr><td colspan="9"><div class="skeleton-row"></div></td></tr>
          <tr><td colspan="9"><div class="skeleton-row"></div></td></tr>
          <tr><td colspan="9"><div class="skeleton-row"></div></td></tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination Footer -->
    <div class="flex flex-col sm:flex-row items-center justify-center sm:justify-between gap-6 p-4 sm:p-6 bg-background border-t border-border-subtle">
      <span class="text-xs font-bold tracking-widest text-text-secondary uppercase text-center sm:text-left w-full sm:w-auto" id="pag-info">Loading...</span>
      <div class="flex items-center justify-center" id="pag-controls"></div>
    </div>
  </div>

</div>

<!-- REINSTATE STAFF MODAL -->
<div class="fixed inset-0 z-[2600] hidden items-center justify-center p-4 bg-black/80 backdrop-blur-md transition-all" id="reinstate-modal" role="dialog" aria-modal="true">
  <div class="relative w-full max-w-md bg-surface rounded-3xl border border-border-subtle shadow-2xl overflow-hidden flex flex-col my-auto">
    <!-- Header -->
    <div class="p-6 border-b border-border-subtle flex items-start gap-4">
      <div class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 shadow-inner">
        <span class="material-symbols-outlined text-2xl" aria-hidden="true">check_circle</span>
      </div>
      <div class="flex-1">
        <h3 class="text-base font-black font-headline text-text-primary">Reinstate Staff Account</h3>
        <p class="text-xs font-bold text-text-secondary mt-0.5">Authorization &amp; Clearance</p>
      </div>
    </div>

    <!-- Body -->
    <div class="p-6 space-y-4">
      <p id="modal-text" class="text-xs font-medium text-text-secondary leading-relaxed">
        You are about to reinstate this staff member. This will immediately restore terminal operations, cash desk rights, and branch system access.
      </p>

      <!-- Admin Password Field -->
      <div class="space-y-2">
        <label for="admin-password" class="text-[10px] font-black uppercase tracking-widest text-text-secondary block">Admin Password Confirmation</label>
        <div class="relative">
          <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-text-secondary text-lg" aria-hidden="true">lock</span>
          <input type="password" id="admin-password" placeholder="Enter your administrative password" class="fin-input !pl-10" />
        </div>
        <div id="modal-error" class="text-xs font-bold text-error hidden"></div>
      </div>
    </div>

    <!-- Actions -->
    <div class="p-6 pt-0 flex gap-3">
      <button type="button" id="close-modal-btn" class="flex-1 py-3 bg-surface hover:bg-surface-hover text-text-primary font-bold rounded-xl text-xs uppercase tracking-wider transition-all border border-border-subtle cursor-pointer">Cancel</button>
      <button type="button" id="confirm-btn" class="flex-1 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-black rounded-xl text-xs uppercase tracking-wider shadow-sm hover:scale-[1.02] active:scale-[0.98] transition-all cursor-pointer">Confirm Reinstatement</button>
    </div>
  </div>
</div>

<?php
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/suspended-staff.js?v=' . time() . '"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>