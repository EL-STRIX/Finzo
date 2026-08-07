<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin';
ob_start();
?>
<!-- STATS ROW -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6 mb-6 sm:mb-10">
        <div class="admin-stat-card !p-4 flex flex-row items-center gap-4">
          <div class="admin-stat-icon shrink-0 !mb-0 !w-12 !h-12 flex items-center justify-center rounded-xl" style="background:#e0f2fe;">
            <span class="material-symbols-outlined" style="color:#0369a1;" aria-hidden="true">group</span>
          </div>
          <div>
            <div class="admin-stat-label !mb-1 text-xs">TOTAL STAFF</div>
            <div id="stat-total-staff" class="admin-stat-value text-xl font-black h-7 skeleton min-w-[40px]"></div>
          </div>
        </div>
        <div class="admin-stat-card !p-4 flex flex-row items-center gap-4">
          <div class="admin-stat-icon shrink-0 !mb-0 !w-12 !h-12 flex items-center justify-center rounded-xl" style="background:#e8f8f2;">
            <span class="material-symbols-outlined" style="color:#00b894;" aria-hidden="true">badge</span>
          </div>
          <div>
            <div class="admin-stat-label !mb-1 text-xs">ACTIVE STAFF</div>
            <div id="stat-active-staff" class="admin-stat-value text-xl font-black h-7 skeleton min-w-[40px]"></div>
          </div>
        </div>
        <div class="admin-stat-card !p-4 flex flex-row items-center gap-4">
          <div class="admin-stat-icon shrink-0 !mb-0 !w-12 !h-12 flex items-center justify-center rounded-xl" style="background:#f1f3f5;">
            <span class="material-symbols-outlined" style="color:#6c757d;" aria-hidden="true">person_off</span>
          </div>
          <div>
            <div class="admin-stat-label !mb-1 text-xs">SUSPEND STAFF</div>
            <div id="stat-suspend-staff" class="admin-stat-value text-xl font-black h-7 skeleton min-w-[40px]"></div>
          </div>
        </div>
        <div class="admin-stat-card !p-4 flex flex-row items-center gap-4">
          <div class="admin-stat-icon shrink-0 !mb-0 !w-12 !h-12 flex items-center justify-center rounded-xl" style="background:#fff8e1;">
            <span class="material-symbols-outlined" style="color:#f39c12;" aria-hidden="true">receipt</span>
          </div>
          <div>
            <div class="admin-stat-label !mb-1 text-xs">TRANSACTIONS</div>
            <div id="stat-transactions-today" class="admin-stat-value text-xl font-black h-7 skeleton min-w-[40px]"></div>
          </div>
        </div>
      </div>

      <!-- FILTERS + TABLE -->
      <div class="ds-card ds-card-dropdown-fix relative z-20 overflow-hidden">
        <div class="p-4 sm:p-6 grid grid-cols-2 lg:flex lg:flex-row items-end gap-3 sm:gap-4 border-b border-surface-border dark:border-white/5 bg-surface-subtle/20 dark:bg-white/5">
          <div class="col-span-1 lg:flex-1 w-full">
            <label class="fin-label">Search Staff</label>
            <div class="relative group">
              <span
                class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-text-muted transition-colors group-focus-within:text-primary-base" aria-hidden="true">search</span>
              <input type="text" id="staff-search-input" placeholder="Search by name or staff ID"
                class="fin-input pl-12">
            </div>
          </div>

          <div class="hidden">
            <input type="hidden" id="filter-status" value="active">
          </div>

          <div class="w-full lg:w-64">
            <label class="fin-label">Branch</label>
            <div class="finzo-dropdown w-full" id="branch-filter-dropdown">
              <div
                class="finzo-dropdown-trigger !bg-surface-hover dark:!bg-surface-container-high !border-none !py-[11px] !rounded-xl w-full">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg opacity-60" aria-hidden="true">location_on</span>
                    <span class="dropdown-label font-bold text-xs">All Branches</span>
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
        <div class="overflow-x-auto w-full">
          <table class="ds-table w-full table-fixed min-w-[1000px] text-left">
            <thead scope="col">
            <tr>
              <th style="width: 4%;" scope="col">#</th>
              <th style="width: 25%;" scope="col">Staff</th>
              <th style="width: 10%;" class="text-center" scope="col">ID</th>
              <th style="width: 15%;" scope="col">Branch</th>
              <th style="width: 10%;" class="text-center" scope="col">Trans</th>
              <th style="width: 12%;" class="text-right" scope="col">Amount</th>
              <th style="width: 14%;" scope="col">Last Login</th>
              <th style="width: 10%;" class="text-right" scope="col">ACTIONS</th>
            </tr>
          </thead>
          <tbody id="ds-table-body">
              <!-- Rows will be injected by api.js -->
              <tr><td colspan="8" style="padding:0;"><div class="skeleton-row"></div></td></tr>
              <tr><td colspan="8" style="padding:0;"><div class="skeleton-row"></div></td></tr>
              <tr><td colspan="8" style="padding:0;"><div class="skeleton-row"></div></td></tr>
          </tbody>
        </table>
        </div>

        <!-- PAGINATION -->
        <div
          class="p-4 bg-surface-subtle/50 dark:bg-white/5 border-t border-surface-border dark:border-white/5 flex flex-col sm:flex-row justify-between items-center gap-4 transition-all mt-4 w-full">
          <div id="pagination-counter" class="text-xs text-text-muted font-bold uppercase tracking-wider">Showing 1-5
            of 8 staff</div>
          <div class="flex items-center gap-2">
            <button id="prev-page" class="btn btn-secondary"
              style="padding: 7px 14px !important; border-radius: 8px !important; font-size: 11px !important;">&larr;
              Previous</button>
            <div id="page-numbers" class="flex gap-1.5">
              <!-- Dynamically Generated -->
            </div>
            <button id="next-page" class="btn btn-secondary"
              style="padding: 7px 14px !important; border-radius: 8px !important; font-size: 11px !important;">Next
              &rarr;</button>
          </div>
        </div>
      </div>
<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>