<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin — Staff Management';
ob_start();
?>
      <!-- TOP PAGE HEADER & ACTION BAR -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 relative z-10">
          <div>
              <h1 class="text-2xl font-headline font-black text-text-primary tracking-tight">Staff Management</h1>
              <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-1">Manage staff accounts, roles, assignments, and permissions</p>
          </div>
          <div class="flex items-center gap-3">
              <button id="open-export-modal" type="button" class="btn btn-secondary px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider inline-flex items-center gap-2 shadow-sm hover:scale-[1.02] active:scale-[0.98] transition-transform">
                  <span class="material-symbols-outlined text-base text-primary" aria-hidden="true">file_download</span>
                  <span>Export CSV</span>
              </button>
              <a id="add-staff-btn" href="/Finzo/admin/staff/add" class="btn btn-primary px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider inline-flex items-center gap-2 shadow-sm hover:scale-[1.02] active:scale-[0.98] transition-transform">
                  <span class="material-symbols-outlined text-base" aria-hidden="true">person_add</span>
                  <span>Add Staff</span>
              </a>
          </div>
      </div>

      <!-- STATS ROW -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8 relative z-10">
        <div class="ds-card p-5 sm:p-6 flex flex-col items-start justify-center group relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
          <div class="absolute -right-6 -top-6 w-24 h-24 bg-info/10 rounded-full blur-2xl group-hover:bg-info/20 transition-all"></div>
          <div class="relative z-10">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-info/20 to-info/5 text-info flex items-center justify-center mb-4 border border-info/20 shadow-[inset_0_1px_1px_rgba(255,255,255,0.4)] dark:shadow-[inset_0_1px_1px_rgba(255,255,255,0.1)]">
              <span class="material-symbols-outlined text-2xl" aria-hidden="true">group</span>
            </div>
            <div class="text-[10px] font-black text-text-secondary uppercase tracking-[0.15em] mb-2">TOTAL STAFF</div>
            <div id="stat-total-staff" class="text-3xl font-black font-display tracking-tighter leading-none text-text-primary skeleton min-w-[40px]">—</div>
          </div>
        </div>
        <div class="ds-card p-5 sm:p-6 flex flex-col items-start justify-center group relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
          <div class="absolute -right-6 -top-6 w-24 h-24 bg-success/10 rounded-full blur-2xl group-hover:bg-success/20 transition-all"></div>
          <div class="relative z-10">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-success/20 to-success/5 text-success flex items-center justify-center mb-4 border border-success/20 shadow-[inset_0_1px_1px_rgba(255,255,255,0.4)] dark:shadow-[inset_0_1px_1px_rgba(255,255,255,0.1)]">
              <span class="material-symbols-outlined text-2xl" aria-hidden="true">badge</span>
            </div>
            <div class="text-[10px] font-black text-text-secondary uppercase tracking-[0.15em] mb-2">ACTIVE STAFF</div>
            <div id="stat-active-staff" class="text-3xl font-black font-display tracking-tighter leading-none text-text-primary skeleton min-w-[40px]">—</div>
          </div>
        </div>
        <div class="ds-card p-5 sm:p-6 flex flex-col items-start justify-center group relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
          <div class="absolute -right-6 -top-6 w-24 h-24 bg-error/10 rounded-full blur-2xl group-hover:bg-error/20 transition-all"></div>
          <div class="relative z-10">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-error/20 to-error/5 text-error flex items-center justify-center mb-4 border border-error/20 shadow-[inset_0_1px_1px_rgba(255,255,255,0.4)] dark:shadow-[inset_0_1px_1px_rgba(255,255,255,0.1)]">
              <span class="material-symbols-outlined text-2xl" aria-hidden="true">person_off</span>
            </div>
            <div class="text-[10px] font-black text-text-secondary uppercase tracking-[0.15em] mb-2">SUSPENDED STAFF</div>
            <div id="stat-suspend-staff" class="text-3xl font-black font-display tracking-tighter leading-none text-text-primary skeleton min-w-[40px]">—</div>
          </div>
        </div>
        <div class="ds-card p-5 sm:p-6 flex flex-col items-start justify-center group relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
          <div class="absolute -right-6 -top-6 w-24 h-24 bg-primary/10 rounded-full blur-2xl group-hover:bg-primary/20 transition-all"></div>
          <div class="relative z-10">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary/20 to-primary/5 text-primary flex items-center justify-center mb-4 border border-primary/20 shadow-[inset_0_1px_1px_rgba(255,255,255,0.4)] dark:shadow-[inset_0_1px_1px_rgba(255,255,255,0.1)]">
              <span class="material-symbols-outlined text-2xl" aria-hidden="true">receipt</span>
            </div>
            <div class="text-[10px] font-black text-text-secondary uppercase tracking-[0.15em] mb-2">TRANSACTIONS TODAY</div>
            <div id="stat-transactions-today" class="text-3xl font-black font-display tracking-tighter leading-none text-text-primary skeleton min-w-[40px]">—</div>
          </div>
        </div>
      </div>

      <!-- FILTERS + TABLE -->
      <div class="ds-card ds-card-dropdown-fix relative z-20 overflow-visible">
        <div class="p-4 sm:p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end border-b border-border-subtle bg-background/50 rounded-t-2xl">
          
          <!-- Search Input -->
          <div class="w-full">
            <label for="staff-search-input" class="fin-label">Search Staff</label>
            <div class="relative group">
              <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-text-secondary transition-colors group-focus-within:text-primary" aria-hidden="true">search</span>
              <input type="text" id="staff-search-input" placeholder="Search by name, email, staff ID, phone..." class="fin-input pl-12">
            </div>
          </div>

          <!-- Filter 1: Status Dropdown -->
          <div class="w-full relative z-30">
            <label for="filter-status" class="fin-label">Account Status</label>
            <div class="finzo-dropdown w-full" id="status-filter-dropdown">
              <input type="hidden" id="filter-status" value="all">
              <div class="finzo-dropdown-trigger fin-input !flex items-center justify-between w-full cursor-pointer">
                <div class="flex items-center gap-2 truncate">
                    <span class="material-symbols-outlined text-lg text-text-secondary" aria-hidden="true">verified_user</span>
                    <span class="dropdown-label font-bold text-sm text-text-primary">All Statuses</span>
                </div>
                <span class="material-symbols-outlined chevron transition-transform duration-300 text-text-secondary" aria-hidden="true">expand_more</span>
              </div>
              <div id="filter-status-menu" class="finzo-dropdown-menu !z-[3000] !w-full">
                <div class="finzo-dropdown-option is-selected" data-value="all">All Statuses</div>
                <div class="finzo-dropdown-option" data-value="active">Active Staff</div>
                <div class="finzo-dropdown-option" data-value="suspended">Suspended Staff</div>
              </div>
            </div>
          </div>

          <!-- Filter 2: Role / Designation Dropdown -->
          <div class="w-full relative z-20">
            <label for="filter-role" class="fin-label">Staff Role / Designation</label>
            <div class="finzo-dropdown w-full" id="role-filter-dropdown">
              <input type="hidden" id="filter-role" value="all">
              <div class="finzo-dropdown-trigger fin-input !flex items-center justify-between w-full cursor-pointer">
                <div class="flex items-center gap-2 truncate">
                    <span class="material-symbols-outlined text-lg text-text-secondary" aria-hidden="true">badge</span>
                    <span class="dropdown-label font-bold text-sm text-text-primary">All Roles</span>
                </div>
                <span class="material-symbols-outlined chevron transition-transform duration-300 text-text-secondary" aria-hidden="true">expand_more</span>
              </div>
              <div id="filter-role-menu" class="finzo-dropdown-menu !z-[3000] !w-full">
                <div class="finzo-dropdown-option is-selected" data-value="all">All Roles</div>
                <div class="finzo-dropdown-option" data-value="Manager">Branch Manager</div>
                <div class="finzo-dropdown-option" data-value="Supervisor">Supervisor</div>
                <div class="finzo-dropdown-option" data-value="Teller">Teller / Cashier</div>
              </div>
            </div>
          </div>

          <!-- Filter 3: Branch Dropdown -->
          <div class="w-full relative z-10">
            <label for="filter-branch" class="fin-label">Assigned Branch</label>
            <div class="finzo-dropdown w-full" id="branch-filter-dropdown">
              <input type="hidden" id="filter-branch" value="all">
              <div class="finzo-dropdown-trigger fin-input !flex items-center justify-between w-full cursor-pointer">
                <div class="flex items-center gap-2 truncate">
                    <span class="material-symbols-outlined text-lg text-text-secondary" aria-hidden="true">location_on</span>
                    <span class="dropdown-label font-bold text-sm text-text-primary">All Branches</span>
                </div>
                <span class="material-symbols-outlined chevron transition-transform duration-300 text-text-secondary" aria-hidden="true">expand_more</span>
              </div>
              <div id="filter-branch-menu" class="finzo-dropdown-menu !z-[3000] !w-full">
                <div class="finzo-dropdown-option is-selected" data-value="all">All Branches</div>
                <!-- Dynamically Populated -->
              </div>
            </div>
          </div>

        </div>

        <div class="ds-table-wrapper border-0 rounded-none shadow-none bg-transparent overflow-x-auto">
          <table class="ds-table w-full table-fixed min-w-[1050px] text-left">
            <thead scope="col">
              <tr>
                <th class="w-[4%]" scope="col">#</th>
                <th class="w-[24%]" scope="col">Staff Member</th>
                <th class="w-[10%] text-center" scope="col">Staff ID</th>
                <th class="w-[14%]" scope="col">Branch</th>
                <th class="w-[10%] text-center" scope="col">Status</th>
                <th class="w-[8%] text-center" scope="col">Trans</th>
                <th class="w-[12%] text-right" scope="col">Volume (₹)</th>
                <th class="w-[10%] text-center" scope="col">Joined</th>
                <th class="w-[8%] text-right" scope="col">Actions</th>
              </tr>
            </thead>
            <tbody id="ds-table-body">
                <tr><td colspan="9"><div class="skeleton-row"></div></td></tr>
                <tr><td colspan="9"><div class="skeleton-row"></div></td></tr>
                <tr><td colspan="9"><div class="skeleton-row"></div></td></tr>
            </tbody>
          </table>
        </div>

        <!-- PAGINATION -->
        <div class="p-4 sm:p-6 bg-background/50 border-t border-border-subtle flex flex-col sm:flex-row justify-between items-center gap-4 transition-all w-full rounded-b-2xl">
          <div id="pagination-counter" class="text-xs text-text-secondary font-bold uppercase tracking-wider">Showing 0-0 of 0 staff</div>
          <div class="flex items-center gap-2">
            <button id="prev-page" class="w-9 h-9 flex items-center justify-center rounded-xl bg-surface border border-border-subtle text-text-secondary transition-all hover:bg-surface-hover hover:text-text-primary disabled:opacity-40 disabled:cursor-not-allowed">
              <span class="material-symbols-outlined text-lg" aria-hidden="true">chevron_left</span>
            </button>
            <div id="page-numbers" class="flex gap-1.5">
              <!-- Dynamically Generated -->
            </div>
            <button id="next-page" class="w-9 h-9 flex items-center justify-center rounded-xl bg-surface border border-border-subtle text-text-secondary transition-all hover:bg-surface-hover hover:text-text-primary disabled:opacity-40 disabled:cursor-not-allowed">
              <span class="material-symbols-outlined text-lg" aria-hidden="true">chevron_right</span>
            </button>
          </div>
        </div>
      </div>

      <!-- EXPORT STAFF CSV MODAL -->
      <div id="export-modal" class="fixed inset-0 z-[2000] hidden items-center justify-center p-4 bg-black/70 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="export-modal-title">
          <div class="modal-content bg-surface border border-border-subtle rounded-2xl shadow-2xl w-full max-w-lg overflow-visible" role="document">
              <div class="p-6 border-b border-border-subtle flex justify-between items-center bg-background/50 rounded-t-2xl">
                  <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center border border-primary/20">
                          <span class="material-symbols-outlined text-xl" aria-hidden="true">file_download</span>
                      </div>
                      <div>
                          <h2 id="export-modal-title" class="text-base font-bold font-headline text-text-primary">Export Staff (CSV)</h2>
                          <p class="text-[10px] font-bold text-text-secondary uppercase tracking-wider">Configure export filters & full columns</p>
                      </div>
                  </div>
                  <button type="button" class="close-export-modal w-8 h-8 flex items-center justify-center rounded-lg hover:bg-background text-text-secondary transition-colors" aria-label="Close modal">
                      <span class="material-symbols-outlined text-lg" aria-hidden="true">close</span>
                  </button>
              </div>

              <div class="p-6 space-y-4">
                  <p class="text-xs text-text-secondary">Export complete staff dossiers (30 data fields including IDs, branches, performance metrics, and audit timestamps):</p>

                  <div class="space-y-3.5">
                      <!-- Modal Status Filter -->
                      <div class="relative z-30">
                          <label for="export-filter-status" class="fin-label">Account Status</label>
                          <div class="finzo-dropdown w-full" id="export-status-dropdown">
                              <input type="hidden" id="export-filter-status" value="all">
                              <div class="finzo-dropdown-trigger fin-input !flex items-center justify-between">
                                  <span class="dropdown-label">All Statuses</span>
                                  <span class="material-symbols-outlined chevron transition-transform duration-300" aria-hidden="true">expand_more</span>
                              </div>
                              <div class="finzo-dropdown-menu z-50">
                                  <div class="finzo-dropdown-option is-selected" data-value="all">All Statuses</div>
                                  <div class="finzo-dropdown-option" data-value="active">Active Staff</div>
                                  <div class="finzo-dropdown-option" data-value="suspended">Suspended Staff</div>
                              </div>
                          </div>
                      </div>

                      <!-- Modal Role Filter -->
                      <div class="relative z-20">
                          <label for="export-filter-role" class="fin-label">Staff Role / Designation</label>
                          <div class="finzo-dropdown w-full" id="export-role-dropdown">
                              <input type="hidden" id="export-filter-role" value="all">
                              <div class="finzo-dropdown-trigger fin-input !flex items-center justify-between">
                                  <span class="dropdown-label">All Roles</span>
                                  <span class="material-symbols-outlined chevron transition-transform duration-300" aria-hidden="true">expand_more</span>
                              </div>
                              <div class="finzo-dropdown-menu z-50">
                                  <div class="finzo-dropdown-option is-selected" data-value="all">All Roles</div>
                                  <div class="finzo-dropdown-option" data-value="Manager">Branch Manager</div>
                                  <div class="finzo-dropdown-option" data-value="Supervisor">Supervisor</div>
                                  <div class="finzo-dropdown-option" data-value="Teller">Teller / Cashier</div>
                              </div>
                          </div>
                      </div>

                      <!-- Modal Branch Filter -->
                      <div class="relative z-10">
                          <label for="export-filter-branch" class="fin-label">Assigned Branch</label>
                          <div class="finzo-dropdown w-full" id="export-branch-dropdown">
                              <input type="hidden" id="export-filter-branch" value="all">
                              <div class="finzo-dropdown-trigger fin-input !flex items-center justify-between">
                                  <span class="dropdown-label">All Branches</span>
                                  <span class="material-symbols-outlined chevron transition-transform duration-300" aria-hidden="true">expand_more</span>
                              </div>
                              <div id="export-branch-menu" class="finzo-dropdown-menu z-50">
                                  <div class="finzo-dropdown-option is-selected" data-value="all">All Branches</div>
                                  <!-- Dynamically Populated -->
                              </div>
                          </div>
                      </div>
                  </div>

                  <div class="pt-4 border-t border-border-subtle flex items-center justify-end gap-3 relative z-0">
                      <button type="button" class="close-export-modal btn btn-secondary px-4 py-2 text-xs font-bold uppercase tracking-wider">Cancel</button>
                      <button type="button" id="start-export" class="btn btn-primary px-5 py-2 text-xs font-bold uppercase tracking-wider inline-flex items-center gap-1.5 shadow-sm">
                          <span class="material-symbols-outlined text-[16px]" aria-hidden="true">download</span>
                          <span>Download CSV</span>
                      </button>
                  </div>
              </div>
          </div>
      </div>
<?php
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/staff.js"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
