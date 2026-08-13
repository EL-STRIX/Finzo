<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin — Staff Management';
$pageHeader = '
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-headline font-black text-text-primary tracking-tight">Staff Management</h1>
        <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-0.5">Manage staff accounts, roles, and access levels</p>
    </div>
</div>
';
ob_start();
?>
<!-- STATS ROW -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <div class="ds-card p-5 relative overflow-hidden group hover:border-info hover:shadow-md transition-all">
          <div class="absolute right-0 top-0 w-16 h-16 bg-info-bg/50 rounded-bl-full -z-0"></div>
          <div class="relative z-10">
            <div class="w-10 h-10 rounded-xl bg-info-bg text-info flex items-center justify-center mb-4 border border-info-bg shadow-sm" >
              <span class="material-symbols-outlined text-xl" aria-hidden="true">group</span>
            </div>
            <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1">TOTAL STAFF</div>
            <div id="stat-total-staff" class="text-xl font-black font-mono tracking-tight text-text-primary skeleton min-w-[40px]">—</div>
          </div>
        </div>
        <div class="ds-card p-5 relative overflow-hidden group hover:border-success hover:shadow-md transition-all">
          <div class="absolute right-0 top-0 w-16 h-16 bg-success-bg/50 rounded-bl-full -z-0"></div>
          <div class="relative z-10">
            <div class="w-10 h-10 rounded-xl bg-success-bg text-success flex items-center justify-center mb-4 border border-success-bg shadow-sm" >
              <span class="material-symbols-outlined text-xl" aria-hidden="true">badge</span>
            </div>
            <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1">ACTIVE STAFF</div>
            <div id="stat-active-staff" class="text-xl font-black font-mono tracking-tight text-text-primary skeleton min-w-[40px]">—</div>
          </div>
        </div>
        <div class="ds-card p-5 relative overflow-hidden group hover:border-error hover:shadow-md transition-all">
          <div class="absolute right-0 top-0 w-16 h-16 bg-error-bg/50 rounded-bl-full -z-0"></div>
          <div class="relative z-10">
            <div class="w-10 h-10 rounded-xl bg-error-bg text-error flex items-center justify-center mb-4 border border-error-bg shadow-sm" >
              <span class="material-symbols-outlined text-xl" aria-hidden="true">person_off</span>
            </div>
            <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1">SUSPEND STAFF</div>
            <div id="stat-suspend-staff" class="text-xl font-black font-mono tracking-tight text-text-primary skeleton min-w-[40px]">—</div>
          </div>
        </div>
        <div class="ds-card p-5 relative overflow-hidden group hover:border-primary hover:shadow-md transition-all">
          <div class="absolute right-0 top-0 w-16 h-16 bg-primary-50/50 rounded-bl-full -z-0"></div>
          <div class="relative z-10">
            <div class="w-10 h-10 rounded-xl bg-primary-50 text-primary flex items-center justify-center mb-4 border border-primary-100 shadow-sm" >
              <span class="material-symbols-outlined text-xl" aria-hidden="true">receipt</span>
            </div>
            <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1">TRANSACTIONS</div>
            <div id="stat-transactions-today" class="text-xl font-black font-mono tracking-tight text-text-primary skeleton min-w-[40px]">—</div>
          </div>
        </div>
      </div>

      <!-- FILTERS + TABLE -->
      <div class="ds-card ds-card-dropdown-fix relative z-20 overflow-hidden">
        <div class="p-4 sm:p-6 grid grid-cols-2 lg:flex lg:flex-row items-end gap-4 border-b border-border-subtle bg-background">
          <div class="col-span-1 lg:flex-1 w-full">
            <label for="staff-search-input" class="fin-label">Search Staff</label>
            <div class="relative group">
              <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-text-secondary transition-colors group-focus-within:text-primary" aria-hidden="true">search</span>
              <input type="text" id="staff-search-input" placeholder="Search by name or staff ID" class="fin-input pl-12">
            </div>
          </div>

          <div class="w-full lg:w-48">
            <label for="filter-status" class="fin-label">Status</label>
            <div class="finzo-dropdown w-full" id="status-filter-dropdown">
              <div class="finzo-dropdown-trigger fin-input !flex items-center justify-between w-full">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg text-text-secondary" aria-hidden="true">filter_list</span>
                    <span class="dropdown-label font-bold text-sm text-text-primary">All Statuses</span>
                </div>
                <span class="material-symbols-outlined chevron transition-transform duration-300" aria-hidden="true">expand_more</span>
              </div>
              <div id="filter-status-menu" class="finzo-dropdown-menu !z-[3000] !w-full">
                <div class="finzo-dropdown-option is-selected" data-value="all">All Statuses</div>
                <div class="finzo-dropdown-option" data-value="active">Active</div>
                <div class="finzo-dropdown-option" data-value="suspended">Suspended</div>
              </div>
              <input type="hidden" id="filter-status" value="all">
            </div>
          </div>

          <div class="w-full lg:w-64">
            <label for="filter-branch" class="fin-label">Branch</label>
            <div class="finzo-dropdown w-full" id="branch-filter-dropdown">
              <div class="finzo-dropdown-trigger fin-input !flex items-center justify-between w-full">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg text-text-secondary" aria-hidden="true">location_on</span>
                    <span class="dropdown-label font-bold text-sm text-text-primary">All Branches</span>
                </div>
                <span class="material-symbols-outlined chevron transition-transform duration-300" aria-hidden="true">expand_more</span>
              </div>
              <div id="filter-branch-menu" class="finzo-dropdown-menu !z-[3000] !w-full">
                <div class="finzo-dropdown-option is-selected" data-value="all">All Branches</div>
                <!-- Dynamically Populated -->
              </div>
              <input type="hidden" id="filter-branch" value="all">
            </div>
          </div>
        </div>
        <div class="ds-table-wrapper border-0 rounded-none shadow-none bg-transparent">
          <table class="ds-table w-full table-fixed min-w-[1000px] text-left">
            <thead scope="col">
            <tr>
              <th class="w-[4%]" scope="col">#</th>
              <th class="w-[25%]" scope="col">Staff</th>
              <th class="w-[10%] text-center" scope="col">ID</th>
              <th class="w-[15%]" scope="col">Branch</th>
              <th class="w-[10%] text-center" scope="col">Trans</th>
              <th class="w-[12%] text-right" scope="col">Amount</th>
              <th class="w-[14%]" scope="col">Last Login</th>
              <th class="w-[10%] text-center" scope="col">Actions</th>
            </tr>
          </thead>
          <tbody id="ds-table-body">
              <!-- Rows will be injected by api.js -->
              <tr><td colspan="8"><div class="skeleton-row"></div></td></tr>
              <tr><td colspan="8"><div class="skeleton-row"></div></td></tr>
              <tr><td colspan="8"><div class="skeleton-row"></div></td></tr>
          </tbody>
        </table>
        </div>

        <!-- PAGINATION -->
        <div class="p-4 sm:p-6 bg-background border-t border-border-subtle flex flex-col sm:flex-row justify-between items-center gap-4 transition-all w-full">
          <div id="pagination-counter" class="text-xs text-text-secondary font-bold uppercase tracking-wider">Showing 1-5 of 8 staff</div>
          <div class="flex items-center gap-2">
            <button id="prev-page" class="w-9 h-9 flex items-center justify-center rounded-xl bg-surface border border-border-subtle text-text-secondary transition-all hover:bg-surface-hover hover:text-text-primary">
              <span class="material-symbols-outlined text-lg" aria-hidden="true">chevron_left</span>
            </button>
            <div id="page-numbers" class="flex gap-1.5">
              <!-- Dynamically Generated -->
            </div>
            <button id="next-page" class="w-9 h-9 flex items-center justify-center rounded-xl bg-surface border border-border-subtle text-text-secondary transition-all hover:bg-surface-hover hover:text-text-primary">
              <span class="material-symbols-outlined text-lg" aria-hidden="true">chevron_right</span>
            </button>
          </div>
        </div>
      </div>
<?php
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/staff.js"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
