<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin — Audit Trail & Compliance Log';
ob_start();
?>

<!-- TOP PAGE HEADER -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 relative z-10">
    <div>
        <h1 class="text-2xl font-headline font-black text-text-primary tracking-tight">Audit Trail & Activity Log</h1>
        <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-1">Immutable regulatory activity history, auth events, security actions, and transaction modifications</p>
    </div>
</div>

<!-- 5 STAT CARDS (Interactive Web Mode) -->
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 sm:gap-6 relative z-10 mb-6 print:hidden">
    <!-- Card 1: Total Events -->
    <div class="ds-card p-5 flex flex-col items-start justify-center group relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl cursor-pointer" onclick="window.setRoleFilter('all')">
        <div class="absolute -right-6 -top-6 w-24 h-24 bg-primary/10 rounded-full blur-2xl group-hover:bg-primary/20 transition-all"></div>
        <div class="relative z-10 w-full">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary/20 to-primary/5 text-primary flex items-center justify-center mb-3 border border-primary/20">
                <span class="material-symbols-outlined text-xl" aria-hidden="true">history</span>
            </div>
            <div class="text-[11px] font-bold text-text-secondary uppercase tracking-widest mb-1">Total Events</div>
            <div class="text-2xl font-black font-display tracking-tight text-text-primary" id="stat-total">0</div>
            <div class="text-[10px] font-bold text-text-secondary uppercase tracking-widest mt-1.5 flex items-center gap-1">
                <span class="material-symbols-outlined text-[12px] text-primary">verified_user</span>
                <span>Immutable Logs</span>
            </div>
        </div>
    </div>

    <!-- Card 2: Admin Actions -->
    <div class="ds-card p-5 flex flex-col items-start justify-center group relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl cursor-pointer" onclick="window.setRoleFilter('admin')">
        <div class="absolute -right-6 -top-6 w-24 h-24 bg-danger/10 rounded-full blur-2xl group-hover:bg-danger/20 transition-all"></div>
        <div class="relative z-10 w-full">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-danger/20 to-danger/5 text-danger flex items-center justify-center mb-3 border border-danger/20">
                <span class="material-symbols-outlined text-xl" aria-hidden="true">admin_panel_settings</span>
            </div>
            <div class="text-[11px] font-bold text-text-secondary uppercase tracking-widest mb-1">Admin Actions</div>
            <div class="text-2xl font-black font-display tracking-tight text-danger" id="stat-admin">0</div>
            <div class="text-[10px] font-bold text-text-secondary uppercase tracking-widest mt-1.5 flex items-center gap-1">
                <span class="material-symbols-outlined text-[12px] text-danger">shield</span>
                <span>Elevated Rights</span>
            </div>
        </div>
    </div>

    <!-- Card 3: Staff Actions -->
    <div class="ds-card p-5 flex flex-col items-start justify-center group relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl cursor-pointer" onclick="window.setRoleFilter('staff')">
        <div class="absolute -right-6 -top-6 w-24 h-24 bg-amber-500/10 rounded-full blur-2xl group-hover:bg-amber-500/20 transition-all"></div>
        <div class="relative z-10 w-full">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500/20 to-amber-500/5 text-amber-500 flex items-center justify-center mb-3 border border-amber-500/20">
                <span class="material-symbols-outlined text-xl" aria-hidden="true">badge</span>
            </div>
            <div class="text-[11px] font-bold text-text-secondary uppercase tracking-widest mb-1">Staff Actions</div>
            <div class="text-2xl font-black font-display tracking-tight text-amber-500" id="stat-staff">0</div>
            <div class="text-[10px] font-bold text-text-secondary uppercase tracking-widest mt-1.5 flex items-center gap-1">
                <span class="material-symbols-outlined text-[12px] text-amber-500">storefront</span>
                <span>Branch Operations</span>
            </div>
        </div>
    </div>

    <!-- Card 4: User Actions -->
    <div class="ds-card p-5 flex flex-col items-start justify-center group relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl cursor-pointer" onclick="window.setRoleFilter('user')">
        <div class="absolute -right-6 -top-6 w-24 h-24 bg-emerald-500/10 rounded-full blur-2xl group-hover:bg-emerald-500/20 transition-all"></div>
        <div class="relative z-10 w-full">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500/20 to-emerald-500/5 text-emerald-500 flex items-center justify-center mb-3 border border-emerald-500/20">
                <span class="material-symbols-outlined text-xl" aria-hidden="true">person</span>
            </div>
            <div class="text-[11px] font-bold text-text-secondary uppercase tracking-widest mb-1">Customer Actions</div>
            <div class="text-2xl font-black font-display tracking-tight text-emerald-500" id="stat-user">0</div>
            <div class="text-[10px] font-bold text-text-secondary uppercase tracking-widest mt-1.5 flex items-center gap-1">
                <span class="material-symbols-outlined text-[12px] text-emerald-500">smartphone</span>
                <span>Portal Self-Service</span>
            </div>
        </div>
    </div>

    <!-- Card 5: System Actions -->
    <div class="ds-card p-5 flex flex-col items-start justify-center group relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl col-span-2 sm:col-span-1 cursor-pointer" onclick="window.setRoleFilter('system')">
        <div class="absolute -right-6 -top-6 w-24 h-24 bg-sky-500/10 rounded-full blur-2xl group-hover:bg-sky-500/20 transition-all"></div>
        <div class="relative z-10 w-full">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-sky-500/20 to-sky-500/5 text-sky-500 flex items-center justify-center mb-3 border border-sky-500/20">
                <span class="material-symbols-outlined text-xl" aria-hidden="true">memory</span>
            </div>
            <div class="text-[11px] font-bold text-text-secondary uppercase tracking-widest mb-1">System Triggers</div>
            <div class="text-2xl font-black font-display tracking-tight text-sky-500" id="stat-system">0</div>
            <div class="text-[10px] font-bold text-text-secondary uppercase tracking-widest mt-1.5 flex items-center gap-1">
                <span class="material-symbols-outlined text-[12px] text-sky-500">sync</span>
                <span>Automated & Cron</span>
            </div>
        </div>
    </div>
</div>

<!-- FILTERS & DATA TABLE CONTAINER -->
<div class="ds-card overflow-hidden relative z-10 shadow-2xl transition-all duration-300 print:border-none print:shadow-none">
    <div class="absolute -right-20 -top-20 w-64 h-64 bg-primary/5 rounded-full blur-3xl -z-10 pointer-events-none print:hidden"></div>
    <div class="absolute -left-20 -bottom-20 w-64 h-64 bg-accent/5 rounded-full blur-3xl -z-10 pointer-events-none print:hidden"></div>
    
    <!-- ROLE SEGMENTED PILLS BAR -->
    <div class="px-5 py-3.5 border-b border-border-subtle bg-surface/50 backdrop-blur-md flex flex-wrap items-center justify-between gap-3 print:hidden">
        <div class="flex flex-wrap items-center gap-2" id="role-pills-container">
            <button class="role-pill px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-200 bg-primary text-text-inverse shadow-md shadow-primary/20" data-role="all">
                <span class="flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[15px]" aria-hidden="true">all_inclusive</span>
                    <span>All Roles</span>
                </span>
            </button>
            <button class="role-pill px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-200 bg-surface border border-border-subtle text-text-secondary hover:text-text-primary hover:bg-surface-hover" data-role="admin">
                <span class="flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[15px] text-danger" aria-hidden="true">admin_panel_settings</span>
                    <span>Admin</span>
                </span>
            </button>
            <button class="role-pill px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-200 bg-surface border border-border-subtle text-text-secondary hover:text-text-primary hover:bg-surface-hover" data-role="staff">
                <span class="flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[15px] text-amber-500" aria-hidden="true">badge</span>
                    <span>Staff</span>
                </span>
            </button>
            <button class="role-pill px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-200 bg-surface border border-border-subtle text-text-secondary hover:text-text-primary hover:bg-surface-hover" data-role="user">
                <span class="flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[15px] text-emerald-500" aria-hidden="true">person</span>
                    <span>Customer</span>
                </span>
            </button>
            <button class="role-pill px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-200 bg-surface border border-border-subtle text-text-secondary hover:text-text-primary hover:bg-surface-hover" data-role="system">
                <span class="flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[15px] text-sky-500" aria-hidden="true">memory</span>
                    <span>System</span>
                </span>
            </button>
        </div>

        <div class="flex items-center gap-2">
            <!-- Active Filter Reset Button -->
            <button id="reset-filters-btn" class="btn btn-secondary px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider rounded-lg text-text-secondary hover:text-text-primary flex items-center gap-1 transition-all">
                <span class="material-symbols-outlined text-[14px]" aria-hidden="true">filter_alt_off</span>
                <span>Reset Filters</span>
            </button>
        </div>
    </div>

    <!-- ADVANCED FILTER BAR -->
    <div class="p-5 sm:p-6 border-b border-border-subtle bg-background/60 backdrop-blur-xl relative z-10">
        <input type="hidden" id="filter-actor-role" value="all">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 items-end">
            <!-- Search -->
            <div class="sm:col-span-2 lg:col-span-6">
                <label for="filter-search" class="fin-label mb-2 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Search Events</label>
                <div class="relative group">
                    <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-text-secondary group-focus-within:text-primary transition-colors text-[18px]" aria-hidden="true">search</span>
                    <input type="text" id="filter-search" placeholder="Search by Actor ID, Name, Entity ID, Event ID, IP, or details..." class="fin-input pl-10 pr-9 w-full text-xs transition-all focus:ring-2 focus:ring-primary/20">
                    <button id="clear-search-btn" class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-text-secondary hover:text-text-primary transition-colors">
                        <span class="material-symbols-outlined text-[16px]">close</span>
                    </button>
                </div>
            </div>

            <!-- Action Type Dropdown -->
            <div class="sm:col-span-1 lg:col-span-3">
                <label for="filter-action-type" class="fin-label mb-2 block text-[10px] font-black text-text-secondary uppercase tracking-widest">Action Type</label>
                <div class="finzo-dropdown w-full" id="action-type-dropdown">
                    <div class="finzo-dropdown-trigger fin-input !flex items-center justify-between w-full !py-2 cursor-pointer">
                        <div class="flex items-center gap-2 truncate">
                            <span class="material-symbols-outlined text-[16px] text-text-secondary" aria-hidden="true">bolt</span>
                            <span class="dropdown-label font-bold text-xs text-text-primary truncate">All Actions</span>
                        </div>
                        <span class="material-symbols-outlined chevron transition-transform duration-300 text-[18px]" aria-hidden="true">expand_more</span>
                    </div>
                    <div class="finzo-dropdown-menu !z-[3000] !w-full max-h-60 overflow-y-auto custom-scrollbar" id="action-type-menu">
                        <div class="finzo-dropdown-option is-selected text-xs" data-value="all">All Actions</div>
                        <!-- Dynamic options injected by audit-log.js -->
                    </div>
                    <input type="hidden" id="filter-action-type" value="all">
                </div>
            </div>

            <!-- Date Range: From & To -->
            <div class="sm:col-span-1 lg:col-span-3 grid grid-cols-2 gap-3">
                <div>
                    <label for="date-from" class="fin-label mb-2 block text-[10px] font-black text-text-secondary uppercase tracking-widest">From Date</label>
                    <input type="date" id="date-from" class="fin-input w-full text-xs !py-2">
                </div>
                <div>
                    <label for="date-to" class="fin-label mb-2 block text-[10px] font-black text-text-secondary uppercase tracking-widest">To Date</label>
                    <input type="date" id="date-to" class="fin-input w-full text-xs !py-2">
                </div>
            </div>
        </div>
    </div>
    
    <!-- DATA TABLE -->
    <div class="ds-table-wrapper w-full border-0 shadow-none rounded-none bg-transparent overflow-x-auto custom-scrollbar print:overflow-visible">
        <table id="audit-log-table" class="ds-table w-full text-left min-w-[1250px] print:min-w-full">
            <thead scope="col">
                <tr class="border-b border-border-subtle bg-surface-subtle/50 text-[10px] font-black uppercase tracking-wider text-text-secondary">
                    <th scope="col" class="py-3.5 px-4 w-12 text-center">#</th>
                    <th scope="col" class="py-3.5 px-4 w-36">Event ID</th>
                    <th scope="col" class="py-3.5 px-4">Actor</th>
                    <th scope="col" class="py-3.5 px-4 w-40">Action</th>
                    <th scope="col" class="py-3.5 px-4 w-44">Entity & Module</th>
                    <th scope="col" class="py-3.5 px-4 w-32">IP Address</th>
                    <th scope="col" class="py-3.5 px-4 w-24 text-center">Status</th>
                    <th scope="col" class="py-3.5 px-4">Details</th>
                    <th scope="col" class="py-3.5 px-4 w-36">Timestamp</th>
                    <th scope="col" class="py-3.5 px-6 text-right w-24 print:hidden">Action</th>
                </tr>
            </thead>
            <tbody id="audit-log-tbody" class="divide-y divide-border-subtle">
                <!-- Injected dynamically by audit-log.js -->
            </tbody>
        </table>
    </div>

    <!-- PRINT-ONLY FOOTER -->
    <div class="hidden print:block mt-8 pt-4 border-t border-black text-center text-xs text-black uppercase tracking-widest font-bold">
        Finzo Enterprise Regulatory System • Computer-generated audit log statement • Mathematically immutable record trail.
    </div>

    <!-- PAGINATION (Web Mode) -->
    <div class="p-4 sm:p-6 bg-surface-subtle border-t border-border-subtle flex flex-col sm:flex-row justify-between items-center gap-4 print:hidden">
        <div class="flex items-center gap-3">
            <div id="pagination-info" class="text-[10px] font-bold text-text-secondary uppercase tracking-widest">Showing 0-0 of 0 Records</div>
            <div class="flex items-center gap-1.5 text-xs text-text-secondary">
                <span class="text-[10px] uppercase font-bold tracking-wider">Per page:</span>
                <select id="rows-per-page" class="fin-input !py-1 !px-2 text-xs font-bold rounded-lg border border-border-subtle bg-surface cursor-pointer">
                    <option value="10">10</option>
                    <option value="25" selected>25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button id="prev-btn" class="btn btn-secondary px-4 !py-2 !text-xs font-bold disabled:opacity-50 disabled:cursor-not-allowed">Previous</button>
            <div id="page-numbers" class="flex items-center gap-1.5">
                <!-- Page numbers injected by audit-log.js -->
            </div>
            <button id="next-btn" class="btn btn-secondary px-4 !py-2 !text-xs font-bold disabled:opacity-50 disabled:cursor-not-allowed">Next</button>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- AUDIT DETAIL INSPECTION MODAL                                             -->
<!-- ========================================================================= -->
<div id="audit-detail-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm hidden p-4 transition-opacity opacity-0 print:hidden">
    <div class="ds-card w-full max-w-2xl bg-surface border border-border-subtle rounded-2xl shadow-2xl overflow-hidden transform scale-95 transition-all duration-300 max-h-[90vh] flex flex-col">
        <!-- Modal Header -->
        <div class="p-6 border-b border-border-subtle bg-surface-subtle/50 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center border border-primary/20">
                    <span class="material-symbols-outlined text-xl" aria-hidden="true">receipt_long</span>
                </div>
                <div>
                    <h3 class="text-lg font-headline font-black text-text-primary tracking-tight">Audit Event Record</h3>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span id="detail-event-id" class="text-xs font-mono font-bold text-primary">EVT-000000</span>
                        <button id="copy-event-id-btn" class="text-text-secondary hover:text-text-primary transition-colors" title="Copy Event ID">
                            <span class="material-symbols-outlined text-[14px]">content_copy</span>
                        </button>
                    </div>
                </div>
            </div>
            <button id="close-detail-modal-btn" class="detail-close p-2 rounded-xl text-text-secondary hover:bg-surface-hover hover:text-text-primary transition-all">
                <span class="material-symbols-outlined text-xl">close</span>
            </button>
        </div>

        <!-- Modal Body (Scrollable) -->
        <div class="p-6 overflow-y-auto space-y-5 flex-1 custom-scrollbar">
            <!-- Event Overview Banner -->
            <div class="p-5 rounded-2xl bg-gradient-to-br from-surface to-surface-subtle border border-border-subtle flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1">Action & Module</div>
                    <div id="detail-action-badge" class="flex items-center gap-2">
                        <!-- Action Badge -->
                    </div>
                    <div class="text-xs font-bold text-text-secondary mt-2 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[14px] text-text-secondary">schedule</span>
                        <span id="detail-timestamp">--</span>
                    </div>
                </div>
                <div class="flex flex-col sm:items-end gap-2">
                    <div id="detail-status-badge"><span class="badge badge-deposit">SUCCESS</span></div>
                    <div class="text-[11px] font-mono text-text-secondary" id="detail-log-id">Log #0</div>
                </div>
            </div>

            <!-- Actor & Entity Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Actor Card -->
                <div class="p-4 rounded-xl bg-surface border border-border-subtle">
                    <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-3 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm text-primary">person</span>
                        <span>Actor Information</span>
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-text-secondary">Role:</span>
                            <span id="detail-actor-role" class="font-bold text-text-primary">--</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-text-secondary">Actor ID:</span>
                            <span id="detail-actor-id" class="font-mono font-bold text-text-primary">--</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-text-secondary">Actor Name:</span>
                            <span id="detail-actor-name" class="font-bold text-text-primary">--</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-text-secondary">IP Address:</span>
                            <span id="detail-ip" class="font-mono text-text-primary">--</span>
                        </div>
                    </div>
                </div>

                <!-- Entity & Scope Card -->
                <div class="p-4 rounded-xl bg-surface border border-border-subtle">
                    <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-3 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm text-info">category</span>
                        <span>Target Entity & Scope</span>
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-text-secondary">Module:</span>
                            <span id="detail-module" class="font-bold text-text-primary uppercase">--</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-text-secondary">Entity Type:</span>
                            <span id="detail-entity-type" class="font-bold text-text-primary uppercase">--</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-text-secondary">Entity ID:</span>
                            <span id="detail-entity-id" class="font-mono font-bold text-text-primary">--</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-text-secondary">Correlation / Req:</span>
                            <span id="detail-request-id" class="font-mono text-xs text-text-secondary truncate max-w-[150px]">--</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Details Description Card -->
            <div class="p-4 rounded-xl bg-surface border border-border-subtle">
                <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-2 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm text-warning">description</span>
                    <span>Operation Details</span>
                </div>
                <div id="detail-details" class="text-xs text-text-primary leading-relaxed bg-surface-subtle p-3 rounded-lg border border-border-subtle font-sans break-words whitespace-pre-wrap">
                    --
                </div>
            </div>

            <!-- Payload Diff / Values Section (if JSON or values exist) -->
            <div id="detail-payload-section" class="hidden space-y-3">
                <div class="flex items-center justify-between">
                    <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm text-primary">data_object</span>
                        <span>State Changes & Payload</span>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="detail-payload-container">
                    <div>
                        <div class="text-[10px] font-bold text-text-secondary uppercase tracking-wider mb-1">Previous Values</div>
                        <pre id="detail-previous-values" class="bg-surface-subtle text-[11px] p-3 rounded-lg border border-border-subtle font-mono overflow-x-auto max-h-40 text-text-secondary custom-scrollbar">None</pre>
                    </div>
                    <div>
                        <div class="text-[10px] font-bold text-text-secondary uppercase tracking-wider mb-1">New Values</div>
                        <pre id="detail-new-values" class="bg-surface-subtle text-[11px] p-3 rounded-lg border border-border-subtle font-mono overflow-x-auto max-h-40 text-primary custom-scrollbar">None</pre>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="p-4 sm:p-5 border-t border-border-subtle bg-surface-subtle/50 flex justify-end items-center gap-3">
            <button class="detail-close btn btn-secondary px-5 py-2 text-xs font-bold">Close</button>
        </div>
    </div>
</div>

<?php
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/audit-log.js?v=' . time() . '"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>