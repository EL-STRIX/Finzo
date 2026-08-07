<?php
$portal = 'admin';
$pageTitle = 'Suspended Users';
ob_start();
?>
<!-- TABLE CARD -->
      <div class="ds-card overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-surface-border dark:border-white/5 flex justify-between items-center">
            <h3 class="font-bold text-xs uppercase tracking-widest text-text-muted">Suspended Users Audit</h3>
            <a href="admin-users.php" class="text-xs font-black text-primary-base hover:underline uppercase tracking-widest">Full Users Audit</a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 items-end gap-4 p-4 sm:p-6 bg-surface-subtle/20 dark:bg-white/[0.02]">
          <div>
            <div class="text-xs font-black tracking-wider text-text-muted uppercase mb-2 ml-1">Search User</div>
            <div class="relative">
              <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-lg" aria-hidden="true">search</span>
              <input type="text" id="search-input" class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-white/5 border border-surface-border dark:border-white/10 rounded-xl text-sm focus:ring-4 focus:ring-primary/5 transition-all font-semibold" placeholder="Name, email or account number...">
            </div>
          </div>
          <div>
            <div class="text-xs font-black tracking-wider text-text-muted uppercase mb-2 ml-1">Start Date</div>
            <input type="date" id="start-date" class="w-full px-4 py-2 bg-white dark:bg-white/5 border border-surface-border dark:border-white/10 rounded-xl text-sm focus:ring-4 focus:ring-primary/5 transition-all font-semibold">
          </div>
          <div>
            <div class="text-xs font-black tracking-wider text-text-muted uppercase mb-2 ml-1">End Date</div>
            <input type="date" id="end-date" class="w-full px-4 py-2 bg-white dark:bg-white/5 border border-surface-border dark:border-white/10 rounded-xl text-sm focus:ring-4 focus:ring-primary/5 transition-all font-semibold">
          </div>
          <div>
            <div class="text-xs font-black tracking-wider text-text-muted uppercase mb-2 ml-1">Reason</div>
            <div class="finzo-dropdown w-full" id="reason-dropdown">
              <input type="hidden" id="reason-filter" value="">
              <div class="finzo-dropdown-trigger cursor-pointer hover:border-primary/40 transition-all !bg-white dark:!bg-white/5 !border-surface-border dark:!border-white/10 !rounded-xl !py-2 !px-4">
                <span class="material-symbols-outlined text-lg opacity-60" aria-hidden="true">filter_alt</span>
                <span class="dropdown-label font-bold text-sm text-text-muted">All Reasons</span>
                <span class="material-symbols-outlined chevron transition-transform duration-300" aria-hidden="true">expand_more</span>
              </div>
              <div class="finzo-dropdown-menu !rounded-xl !border-none !shadow-2xl !z-[2000]">
                <div class="finzo-dropdown-option is-selected" data-value="">All Reasons</div>
                <div class="finzo-dropdown-option" data-value="KYC Documentation Expired/Invalid">KYC Documentation Expired/Invalid</div>
                <div class="finzo-dropdown-option" data-value="Suspicious UPI Transaction Patterns">Suspicious UPI Transaction Patterns</div>
                <div class="finzo-dropdown-option" data-value="Regulatory Compliance Hold (RBI)">Regulatory Compliance Hold (RBI)</div>
                <div class="finzo-dropdown-option" data-value="Multiple Failed Auth Attempts">Multiple Failed Auth Attempts</div>
                <div class="finzo-dropdown-option" data-value="Non-Compliance with IRDAI">Non-Compliance with IRDAI</div>
                <div class="finzo-dropdown-option" data-value="Legal/Account Dispute">Legal/Account Dispute</div>
                <div class="finzo-dropdown-option" data-value="Other">Other</div>
              </div>
            </div>
          </div>
        </div>

        <div class="w-full overflow-x-auto selection:bg-primary-base/10">
          <table class="ds-table w-full text-left min-w-[1300px]">
            <thead scope="col">
              <tr class="bg-surface-subtle/50 dark:bg-white/[0.02]">
                <th class="py-4 px-6 text-left" scope="col">#</th>
                <th class="py-4 px-6 text-left" scope="col">Customer</th>
                <th class="py-4 px-6 text-left" scope="col">Account No</th>
                <th class="py-4 px-6 text-left" scope="col">Account Type</th>
                <th class="py-4 px-6 text-left" scope="col">Suspension Reason</th>
                <th class="py-4 px-6 text-left" scope="col">Suspended On</th>
                <th class="py-4 px-6 text-left" scope="col">Suspended By</th>
                <th class="py-4 px-6 text-left" scope="col">Duration</th>
                <th class="py-4 px-6 text-center min-w-[100px] w-28" scope="col">Actions</th>
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

        <div class="flex flex-col sm:flex-row items-center justify-center sm:justify-between gap-6 p-6 bg-surface-subtle/50 dark:bg-white/[0.02]">
          <span class="text-xs sm:text-xs font-bold tracking-widest text-text-muted uppercase text-center sm:text-left w-full sm:w-auto" id="pag-info">Loading...</span>
          <div class="flex items-center justify-center" id="pag-controls"></div>
        </div>
      </div>
<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>