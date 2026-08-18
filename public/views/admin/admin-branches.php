<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin - Branch Management';
$pageHeader = '
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-headline font-black text-text-primary tracking-tight">Branch Management</h1>
        <p class="text-[10px] font-black text-text-secondary uppercase tracking-widest mt-0.5">Manage bank branches, locations, managers, and operational configurations</p>
    </div>
    <div class="flex items-center gap-3">
        <button id="btn-open-add-branch" class="btn btn-primary flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">add_business</span> Add New Branch
        </button>
    </div>
</div>
';
ob_start();
?>
    <!-- STATS ROW -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8 relative z-10">
        <div class="ds-card p-5 sm:p-6 flex flex-col items-start justify-center group relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-blue-500/10 rounded-full blur-2xl group-hover:bg-blue-500/20 transition-all"></div>
            <div class="relative z-10 w-full">
                <div class="w-12 h-12 rounded-xl bg-blue-500/10 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 flex items-center justify-center mb-4 border border-blue-500/20 shadow-[inset_0_1px_1px_rgba(255,255,255,0.4)] dark:shadow-[inset_0_1px_1px_rgba(255,255,255,0.1)]">
                    <span class="material-symbols-outlined text-2xl" aria-hidden="true">store</span>
                </div>
                <div class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.15em] mb-2">TOTAL BRANCHES</div>
                <div id="stat-total-branches" class="text-3xl font-black font-display tracking-tighter leading-none text-slate-900 dark:text-white skeleton min-w-[40px]">—</div>
            </div>
        </div>
        <div class="ds-card p-5 sm:p-6 flex flex-col items-start justify-center group relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-emerald-500/10 rounded-full blur-2xl group-hover:bg-emerald-500/20 transition-all"></div>
            <div class="relative z-10 w-full">
                <div class="w-12 h-12 rounded-xl bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-4 border border-emerald-500/20 shadow-[inset_0_1px_1px_rgba(255,255,255,0.4)] dark:shadow-[inset_0_1px_1px_rgba(255,255,255,0.1)]">
                    <span class="material-symbols-outlined text-2xl" aria-hidden="true">check_circle</span>
                </div>
                <div class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.15em] mb-2">ACTIVE BRANCHES</div>
                <div id="stat-active-branches" class="text-3xl font-black font-display tracking-tighter leading-none text-slate-900 dark:text-white skeleton min-w-[40px]">—</div>
            </div>
        </div>
        <div class="ds-card p-5 sm:p-6 flex flex-col items-start justify-center group relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-purple-500/10 rounded-full blur-2xl group-hover:bg-purple-500/20 transition-all"></div>
            <div class="relative z-10 w-full">
                <div class="w-12 h-12 rounded-xl bg-purple-500/10 dark:bg-purple-500/20 text-purple-600 dark:text-purple-400 flex items-center justify-center mb-4 border border-purple-500/20 shadow-[inset_0_1px_1px_rgba(255,255,255,0.4)] dark:shadow-[inset_0_1px_1px_rgba(255,255,255,0.1)]">
                    <span class="material-symbols-outlined text-2xl" aria-hidden="true">badge</span>
                </div>
                <div class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.15em] mb-2">TOTAL STAFF ASSIGNED</div>
                <div id="stat-total-staff" class="text-3xl font-black font-display tracking-tighter leading-none text-slate-900 dark:text-white skeleton min-w-[40px]">—</div>
            </div>
        </div>
        <div class="ds-card p-5 sm:p-6 flex flex-col items-start justify-center group relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-amber-500/10 rounded-full blur-2xl group-hover:bg-amber-500/20 transition-all"></div>
            <div class="relative z-10 w-full">
                <div class="w-12 h-12 rounded-xl bg-amber-500/10 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-4 border border-amber-500/20 shadow-[inset_0_1px_1px_rgba(255,255,255,0.4)] dark:shadow-[inset_0_1px_1px_rgba(255,255,255,0.1)]">
                    <span class="material-symbols-outlined text-2xl" aria-hidden="true">receipt_long</span>
                </div>
                <div class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.15em] mb-2">PROCESSED TRANSACTIONS</div>
                <div id="stat-total-txns" class="text-3xl font-black font-display tracking-tighter leading-none text-slate-900 dark:text-white skeleton min-w-[40px]">—</div>
            </div>
        </div>
    </div>

    <!-- INFO BANNER -->
    <div class="p-4 bg-blue-500/10 dark:bg-blue-950/30 border border-blue-500/20 dark:border-blue-800/40 rounded-2xl flex gap-3 mb-6 items-start">
        <div class="w-10 h-10 rounded-xl bg-blue-500/15 dark:bg-blue-900/40 text-blue-700 dark:text-blue-400 flex items-center justify-center shrink-0 border border-blue-500/25 dark:border-blue-800/50">
            <span class="material-symbols-outlined text-2xl" aria-hidden="true">account_balance</span>
        </div>
        <div>
            <div class="text-sm font-black text-blue-950 dark:text-blue-200 flex items-center gap-2">
                <span>Central Headquarters (System Hub)</span>
                <span class="px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/60 text-blue-800 dark:text-blue-300 text-[9px] font-mono font-bold uppercase border border-blue-200 dark:border-blue-700/50">System Protected</span>
            </div>
            <div class="text-xs font-medium text-slate-700 dark:text-blue-300/90 mt-1 leading-relaxed">
                The primary bank headquarters (Type: <code class="px-1.5 py-0.5 rounded bg-blue-100 dark:bg-blue-900/60 text-blue-900 dark:text-blue-200 font-mono text-[11px] font-bold">system</code>) is the core operational hub and is protected from deletion. Auxiliary branches across Indian states can be registered, edited, and monitored below.
            </div>
        </div>
    </div>

    <!-- FILTERS & TABLE CARD -->
    <div class="ds-card ds-card-dropdown-fix relative z-20 overflow-visible">
        <div class="p-4 sm:p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 items-end border-b border-slate-200 dark:border-white/10 bg-slate-50/70 dark:bg-surface rounded-t-2xl">
            
            <!-- Search Input (Span 6 cols on lg) -->
            <div class="lg:col-span-6 w-full">
                <label for="admin-branch-finder" class="fin-label mb-1.5 block text-[10px] font-black uppercase text-slate-600 dark:text-slate-300">Search Branches</label>
                <div class="fin-input-wrapper">
                    <span class="material-symbols-outlined input-icon-left text-slate-400 dark:text-slate-400 text-lg" aria-hidden="true">search</span>
                    <input type="text" id="admin-branch-finder" name="branch_query" placeholder="Search by branch name, code, city, state..." 
                        autocomplete="off" class="fin-input has-icon-left bg-white dark:bg-surface-elevated text-slate-900 dark:text-white border-slate-200 dark:border-white/10 w-full shadow-sm">
                </div>
            </div>

            <!-- Filter 1: Status Dropdown (Span 3 cols on lg) -->
            <div class="lg:col-span-3 w-full relative z-30">
                <label class="fin-label mb-1.5 block text-[10px] font-black uppercase text-slate-600 dark:text-slate-300">Operational Status</label>
                <div class="finzo-dropdown w-full" id="status-filter-dropdown">
                    <input type="hidden" id="filter-branch-status" value="all">
                    <div class="finzo-dropdown-trigger fin-input !flex items-center justify-between w-full cursor-pointer bg-white dark:bg-surface-elevated text-slate-900 dark:text-white border-slate-200 dark:border-white/10 shadow-sm" id="status-filter-trigger">
                        <div class="flex items-center gap-2 truncate">
                            <span class="material-symbols-outlined text-lg text-slate-500 dark:text-slate-400" aria-hidden="true">toggle_on</span>
                            <span class="dropdown-label font-bold text-sm text-slate-900 dark:text-white truncate" id="status-filter-label">All Statuses</span>
                        </div>
                        <span class="material-symbols-outlined chevron transition-transform duration-300 text-slate-500 dark:text-slate-400 shrink-0" aria-hidden="true">expand_more</span>
                    </div>
                    <div class="finzo-dropdown-menu !z-[3000] !w-full bg-white dark:bg-surface-elevated border border-slate-200 dark:border-white/10 text-slate-900 dark:text-white shadow-2xl" id="status-filter-menu">
                        <div class="finzo-dropdown-option is-selected hover:bg-slate-100 dark:hover:bg-white/5 cursor-pointer py-2.5 px-3.5 text-xs font-bold" data-value="all">All Statuses</div>
                        <div class="finzo-dropdown-option hover:bg-slate-100 dark:hover:bg-white/5 cursor-pointer py-2.5 px-3.5 text-xs font-bold flex items-center gap-2" data-value="active">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Active Branches
                        </div>
                        <div class="finzo-dropdown-option hover:bg-slate-100 dark:hover:bg-white/5 cursor-pointer py-2.5 px-3.5 text-xs font-bold flex items-center gap-2" data-value="inactive">
                            <span class="w-2 h-2 rounded-full bg-red-500"></span> Inactive Branches
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter 2: Branch Type Dropdown (Span 3 cols on lg) -->
            <div class="lg:col-span-3 w-full relative z-20">
                <label class="fin-label mb-1.5 block text-[10px] font-black uppercase text-slate-600 dark:text-slate-300">Branch Type</label>
                <div class="finzo-dropdown w-full" id="type-filter-dropdown">
                    <input type="hidden" id="filter-branch-type" value="all">
                    <div class="finzo-dropdown-trigger fin-input !flex items-center justify-between w-full cursor-pointer bg-white dark:bg-surface-elevated text-slate-900 dark:text-white border-slate-200 dark:border-white/10 shadow-sm" id="type-filter-trigger">
                        <div class="flex items-center gap-2 truncate">
                            <span class="material-symbols-outlined text-lg text-slate-500 dark:text-slate-400" aria-hidden="true">category</span>
                            <span class="dropdown-label font-bold text-sm text-slate-900 dark:text-white truncate" id="type-filter-label">All Branch Types</span>
                        </div>
                        <span class="material-symbols-outlined chevron transition-transform duration-300 text-slate-500 dark:text-slate-400 shrink-0" aria-hidden="true">expand_more</span>
                    </div>
                    <div class="finzo-dropdown-menu !z-[3000] !w-full bg-white dark:bg-surface-elevated border border-slate-200 dark:border-white/10 text-slate-900 dark:text-white shadow-2xl" id="type-filter-menu">
                        <div class="finzo-dropdown-option is-selected hover:bg-slate-100 dark:hover:bg-white/5 cursor-pointer py-2.5 px-3.5 text-xs font-bold" data-value="all">All Branch Types</div>
                        <div class="finzo-dropdown-option hover:bg-slate-100 dark:hover:bg-white/5 cursor-pointer py-2.5 px-3.5 text-xs font-bold flex items-center gap-2" data-value="system">
                            <span class="px-1.5 py-0.5 rounded bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300 font-mono text-[10px] font-bold">SYSTEM</span> Head Office / System Hub
                        </div>
                        <div class="finzo-dropdown-option hover:bg-slate-100 dark:hover:bg-white/5 cursor-pointer py-2.5 px-3.5 text-xs font-bold flex items-center gap-2" data-value="custom">
                            <span class="px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 font-mono text-[10px] font-bold">CUSTOM</span> Regional / Custom Branch
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="ds-table-wrapper border-0 rounded-none shadow-none bg-transparent">
            <table class="ds-table w-full min-w-[1000px] text-left">
                <thead scope="col" class="bg-slate-50 dark:bg-surface-elevated/70 border-b border-slate-200 dark:border-white/10">
                    <tr>
                        <th class="w-[4%] py-3.5 px-4 text-[10px] font-black text-slate-600 dark:text-slate-400 uppercase tracking-widest" scope="col">#</th>
                        <th class="w-[22%] py-3.5 px-4 text-[10px] font-black text-slate-600 dark:text-slate-400 uppercase tracking-widest" scope="col">BRANCH</th>
                        <th class="w-[14%] whitespace-nowrap py-3.5 px-4 text-[10px] font-black text-slate-600 dark:text-slate-400 uppercase tracking-widest" scope="col">BRANCH ID</th>
                        <th class="w-[14%] py-3.5 px-4 text-[10px] font-black text-slate-600 dark:text-slate-400 uppercase tracking-widest" scope="col">STATE</th>
                        <th class="w-[18%] py-3.5 px-4 text-[10px] font-black text-slate-600 dark:text-slate-400 uppercase tracking-widest" scope="col">MANAGER</th>
                        <th class="w-[7%] py-3.5 px-4 text-center text-[10px] font-black text-slate-600 dark:text-slate-400 uppercase tracking-widest" scope="col">STAFF</th>
                        <th class="w-[9%] py-3.5 px-4 text-center text-[10px] font-black text-slate-600 dark:text-slate-400 uppercase tracking-widest" scope="col">TYPE</th>
                        <th class="w-[9%] py-3.5 px-4 text-center text-[10px] font-black text-slate-600 dark:text-slate-400 uppercase tracking-widest" scope="col">STATUS</th>
                        <th class="w-[8%] py-3.5 px-4 text-right text-[10px] font-black text-slate-600 dark:text-slate-400 uppercase tracking-widest" scope="col">ACTIONS</th>
                    </tr>
                </thead>
                <tbody id="branches-table-body">
                    <tr class="skeleton-row">
                        <td class="font-bold opacity-40"><div class="skeleton w-4 h-4"></div></td>
                        <td><div class="skeleton w-32 h-4"></div></td>
                        <td><div class="skeleton w-24 h-4"></div></td>
                        <td><div class="skeleton w-20 h-4"></div></td>
                        <td><div class="skeleton w-48 h-6"></div></td>
                        <td><div class="skeleton w-8 h-4 mx-auto"></div></td>
                        <td><div class="skeleton w-20 h-6 rounded-lg"></div></td>
                        <td><div class="skeleton w-24 h-6 rounded-full"></div></td>
                        <td class="text-right"><div class="skeleton w-24 h-8 ml-auto"></div></td>
                    </tr>
                    <tr class="skeleton-row">
                        <td class="font-bold opacity-40"><div class="skeleton w-4 h-4"></div></td>
                        <td><div class="skeleton w-32 h-4"></div></td>
                        <td><div class="skeleton w-24 h-4"></div></td>
                        <td><div class="skeleton w-20 h-4"></div></td>
                        <td><div class="skeleton w-48 h-6"></div></td>
                        <td><div class="skeleton w-8 h-4 mx-auto"></div></td>
                        <td><div class="skeleton w-20 h-6 rounded-lg"></div></td>
                        <td><div class="skeleton w-24 h-6 rounded-full"></div></td>
                        <td class="text-right"><div class="skeleton w-24 h-8 ml-auto"></div></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- PAGINATION / SUMMARY FOOTER -->
        <div class="p-4 sm:p-6 bg-slate-50/70 dark:bg-surface border-t border-slate-200 dark:border-white/10 flex flex-col sm:flex-row justify-between items-center gap-4 transition-all w-full">
            <div id="branch-summary-count" class="text-[10px] text-slate-600 dark:text-slate-400 font-black uppercase tracking-widest">Loading branches...</div>
        </div>
    </div>

    <!-- ADD/EDIT BRANCH MODAL -->
    <div id="addBranchModal" class="fixed inset-0 z-[2000] hidden items-center justify-center p-4" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-sm transition-all modal-backdrop" data-close-modal="addBranchModal"></div>
        <div class="relative w-full max-w-2xl bg-white dark:bg-surface-elevated text-slate-900 dark:text-white rounded-2xl overflow-hidden shadow-2xl flex flex-col max-h-[90vh] border border-slate-200 dark:border-white/10 animate-modal-enter">
            <div class="p-6 border-b border-slate-200 dark:border-white/10 flex justify-between items-center sticky top-0 bg-white dark:bg-surface-elevated z-10 shrink-0">
                <div>
                    <h3 id="modal-branch-heading" class="font-black text-xl text-slate-900 dark:text-white tracking-tight">Add New Branch</h3>
                    <p id="modal-branch-subheading" class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mt-1">Fill in all details to register a new Finzo branch</p>
                </div>
                <button type="button" class="p-2 hover:bg-slate-100 dark:hover:bg-white/10 rounded-full transition-colors text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white modal-close-btn" data-close-modal="addBranchModal">
                    <span class="material-symbols-outlined" aria-hidden="true">close</span>
                </button>
            </div>
            
            <div class="p-6 sm:p-8 overflow-y-auto flex-1">
                <form class="space-y-6 sm:space-y-8" id="addBranchForm" novalidate>
                    <!-- SECTION 1 -->
                    <div>
                        <div class="text-[10px] font-black tracking-widest text-amber-700 dark:text-amber-400 uppercase mb-4 py-1 border-b border-slate-200 dark:border-white/10 flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm" aria-hidden="true">domain</span>
                            SECTION 1: Branch Information
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div class="col-span-1">
                                <label for="add-branch-name" class="fin-label mb-1.5 block text-[10px] font-black uppercase text-slate-600 dark:text-slate-300">Branch Name *</label>
                                <input type="text" id="add-branch-name" name="branch_name" placeholder="e.g. Mumbai West Hub" required class="fin-input bg-white dark:bg-surface-subtle text-slate-900 dark:text-white border-slate-200 dark:border-white/10 shadow-sm">
                            </div>
                            <div class="col-span-1">
                                <label for="add-branch-code" class="fin-label mb-1.5 block text-[10px] font-black uppercase text-slate-600 dark:text-slate-300">Branch Code *</label>
                                <input type="text" id="add-branch-code" name="branch_code" placeholder="FNZ-XX-000" readonly class="fin-input bg-slate-100 dark:bg-surface-dark cursor-not-allowed font-mono text-amber-700 dark:text-amber-400 font-bold border-slate-200 dark:border-white/10 shadow-sm">
                                <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1 font-bold">Auto-generated from state selection.</p>
                            </div>
                            <div class="col-span-1">
                                <label for="add-branch-city" class="fin-label mb-1.5 block text-[10px] font-black uppercase text-slate-600 dark:text-slate-300">City / District *</label>
                                <input type="text" id="add-branch-city" name="city" placeholder="e.g. Mumbai" required class="fin-input bg-white dark:bg-surface-subtle text-slate-900 dark:text-white border-slate-200 dark:border-white/10 shadow-sm">
                            </div>
                            <div class="col-span-1">
                                <label for="branch-state" class="fin-label mb-1.5 block text-[10px] font-black uppercase text-slate-600 dark:text-slate-300">State / UT *</label>
                                <div class="finzo-dropdown w-full" id="branch-state-dropdown">
                                    <div class="finzo-dropdown-trigger fin-input !flex items-center justify-between cursor-pointer bg-white dark:bg-surface-subtle text-slate-900 dark:text-white border-slate-200 dark:border-white/10 shadow-sm" id="state-dropdown-trigger">
                                        <span class="dropdown-label font-bold text-sm text-slate-900 dark:text-white truncate" id="state-dropdown-label">Select State / UT</span>
                                        <span class="material-symbols-outlined chevron text-slate-400 dark:text-slate-400 shrink-0" aria-hidden="true">expand_more</span>
                                    </div>
                                    <div class="finzo-dropdown-menu w-full !max-h-[260px] overflow-y-auto z-50 bg-white dark:bg-surface-elevated border border-slate-200 dark:border-white/10 text-slate-900 dark:text-white shadow-2xl">
                                        <div class="px-4 py-2 text-[10px] font-black uppercase text-slate-500 dark:text-slate-400 tracking-widest bg-slate-50 dark:bg-surface-dark sticky top-0 z-10 border-b border-slate-200 dark:border-white/10">Indian States</div>
                                        <div class="finzo-dropdown-option" data-value="Andhra Pradesh">Andhra Pradesh</div>
                                        <div class="finzo-dropdown-option" data-value="Arunachal Pradesh">Arunachal Pradesh</div>
                                        <div class="finzo-dropdown-option" data-value="Assam">Assam</div>
                                        <div class="finzo-dropdown-option" data-value="Bihar">Bihar</div>
                                        <div class="finzo-dropdown-option" data-value="Chhattisgarh">Chhattisgarh</div>
                                        <div class="finzo-dropdown-option" data-value="Goa">Goa</div>
                                        <div class="finzo-dropdown-option" data-value="Gujarat">Gujarat</div>
                                        <div class="finzo-dropdown-option" data-value="Haryana">Haryana</div>
                                        <div class="finzo-dropdown-option" data-value="Himachal Pradesh">Himachal Pradesh</div>
                                        <div class="finzo-dropdown-option" data-value="Jharkhand">Jharkhand</div>
                                        <div class="finzo-dropdown-option" data-value="Karnataka">Karnataka</div>
                                        <div class="finzo-dropdown-option" data-value="Kerala">Kerala</div>
                                        <div class="finzo-dropdown-option" data-value="Madhya Pradesh">Madhya Pradesh</div>
                                        <div class="finzo-dropdown-option" data-value="Maharashtra">Maharashtra</div>
                                        <div class="finzo-dropdown-option" data-value="Manipur">Manipur</div>
                                        <div class="finzo-dropdown-option" data-value="Meghalaya">Meghalaya</div>
                                        <div class="finzo-dropdown-option" data-value="Mizoram">Mizoram</div>
                                        <div class="finzo-dropdown-option" data-value="Nagaland">Nagaland</div>
                                        <div class="finzo-dropdown-option" data-value="Odisha">Odisha</div>
                                        <div class="finzo-dropdown-option" data-value="Punjab">Punjab</div>
                                        <div class="finzo-dropdown-option" data-value="Rajasthan">Rajasthan</div>
                                        <div class="finzo-dropdown-option" data-value="Sikkim">Sikkim</div>
                                        <div class="finzo-dropdown-option" data-value="Tamil Nadu">Tamil Nadu</div>
                                        <div class="finzo-dropdown-option" data-value="Telangana">Telangana</div>
                                        <div class="finzo-dropdown-option" data-value="Tripura">Tripura</div>
                                        <div class="finzo-dropdown-option" data-value="Uttar Pradesh">Uttar Pradesh</div>
                                        <div class="finzo-dropdown-option" data-value="Uttarakhand">Uttarakhand</div>
                                        <div class="finzo-dropdown-option" data-value="West Bengal">West Bengal</div>
                                        <div class="px-4 py-2 mt-2 text-[10px] font-black uppercase text-slate-500 dark:text-slate-400 tracking-widest bg-slate-50 dark:bg-surface-dark sticky top-0 z-10 border-b border-slate-200 dark:border-white/10">Union Territories</div>
                                        <div class="finzo-dropdown-option" data-value="Andaman and Nicobar Islands">Andaman and Nicobar Islands</div>
                                        <div class="finzo-dropdown-option" data-value="Chandigarh">Chandigarh</div>
                                        <div class="finzo-dropdown-option" data-value="Dadra and Nagar Haveli and Daman and Diu">Dadra and Nagar Haveli and Daman and Diu</div>
                                        <div class="finzo-dropdown-option" data-value="Delhi">Delhi</div>
                                        <div class="finzo-dropdown-option" data-value="Jammu and Kashmir">Jammu and Kashmir</div>
                                        <div class="finzo-dropdown-option" data-value="Ladakh">Ladakh</div>
                                        <div class="finzo-dropdown-option" data-value="Lakshadweep">Lakshadweep</div>
                                        <div class="finzo-dropdown-option" data-value="Puducherry">Puducherry</div>
                                    </div>
                                    <input type="hidden" id="branch-state" name="state" required value="">
                                </div>
                            </div>
                            <div class="col-span-1 sm:col-span-2">
                                <label for="add-branch-address" class="fin-label mb-1.5 block text-[10px] font-black uppercase text-slate-600 dark:text-slate-300">Full Address *</label>
                                <textarea id="add-branch-address" name="address" rows="2" placeholder="Building no., street name, locality, pincode" required class="fin-input bg-white dark:bg-surface-subtle text-slate-900 dark:text-white border-slate-200 dark:border-white/10 shadow-sm"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2 -->
                    <div>
                        <div class="text-[10px] font-black tracking-widest text-amber-700 dark:text-amber-400 uppercase mb-4 py-1 border-b border-slate-200 dark:border-white/10 flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm" aria-hidden="true">badge</span>
                            SECTION 2: Branch Manager Assignment
                        </div>
                        <div>
                            <label for="manager-id" class="fin-label mb-1.5 block text-[10px] font-black uppercase text-slate-600 dark:text-slate-300">Assign Branch Manager</label>
                            <div class="finzo-dropdown w-full" id="branch-manager-dropdown">
                                <div class="finzo-dropdown-trigger fin-input !flex items-center justify-between cursor-pointer bg-white dark:bg-surface-subtle text-slate-900 dark:text-white border-slate-200 dark:border-white/10 shadow-sm" id="manager-dropdown-trigger">
                                    <span class="dropdown-label font-bold text-sm text-slate-900 dark:text-white truncate" id="manager-dropdown-label">No Manager Assigned (Unassigned)</span>
                                    <span class="material-symbols-outlined chevron text-slate-400 dark:text-slate-400 shrink-0" aria-hidden="true">expand_more</span>
                                </div>
                                <div class="finzo-dropdown-menu w-full !max-h-[220px] overflow-y-auto z-50 bg-white dark:bg-surface-elevated border border-slate-200 dark:border-white/10 text-slate-900 dark:text-white shadow-2xl" id="manager-dropdown-menu">
                                    <div class="finzo-dropdown-option is-selected font-bold" data-value="">No Manager Assigned (Unassigned)</div>
                                    <!-- Dynamic manager options injected via JS -->
                                </div>
                                <input type="hidden" id="manager-id" name="manager_id" value="">
                            </div>
                            <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1.5 font-bold">Only active staff members can be assigned as Branch Manager.</p>
                        </div>
                    </div>

                    <!-- SECTION 3 -->
                    <div id="section-admin-security">
                        <div class="text-[10px] font-black tracking-widest text-amber-700 dark:text-amber-400 uppercase mb-4 py-1 border-b border-slate-200 dark:border-white/10 flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm" aria-hidden="true">lock</span>
                            SECTION 3: Administrator Security Authentication
                        </div>
                        <div>
                            <label for="admin-security-password" class="fin-label mb-1.5 block text-[10px] font-black uppercase text-slate-600 dark:text-slate-300">Admin Security Password *</label>
                            <div class="fin-input-wrapper">
                                <span class="material-symbols-outlined input-icon-left text-slate-400 dark:text-slate-400 text-lg" aria-hidden="true">admin_panel_settings</span>
                                <input type="password" id="admin-security-password" name="admin_password" placeholder="Enter your admin security password" required class="fin-input has-icon-left has-icon-right bg-white dark:bg-surface-subtle text-slate-900 dark:text-white border-slate-200 dark:border-white/10 shadow-sm">
                                <button type="button" id="toggle-admin-pw" class="password-toggle-btn text-slate-400 dark:text-slate-400 hover:text-slate-700 dark:hover:text-white transition-colors" aria-label="Toggle password visibility">
                                    <span class="material-symbols-outlined text-lg" id="toggle-admin-pw-icon" aria-hidden="true">visibility</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 4 -->
                    <div>
                        <div class="text-[10px] font-black tracking-widest text-amber-700 dark:text-amber-400 uppercase mb-4 py-1 border-b border-slate-200 dark:border-white/10 flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm" aria-hidden="true">notes</span>
                            SECTION 4: Description & Details
                        </div>
                        <div>
                            <label for="branch-desc" class="fin-label mb-1.5 block text-[10px] font-black uppercase text-slate-600 dark:text-slate-300">Branch Description / Notes</label>
                            <textarea id="branch-desc" name="description" rows="3" maxlength="500" placeholder="Operational timings, specialized banking services, lockers, etc." class="fin-input bg-white dark:bg-surface-subtle text-slate-900 dark:text-white border-slate-200 dark:border-white/10 shadow-sm"></textarea>
                            <div class="char-counter text-[10px] text-slate-500 dark:text-slate-400 font-black uppercase tracking-widest text-right mt-1" id="branch-desc-counter">0 / 500</div>
                        </div>
                    </div>

                    <!-- SECTION 5 -->
                    <div>
                        <div class="text-[10px] font-black tracking-widest text-amber-700 dark:text-amber-400 uppercase mb-4 py-1 border-b border-slate-200 dark:border-white/10 flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm" aria-hidden="true">verified_user</span>
                            SECTION 5: Registration Certificate
                        </div>
                        <label class="fin-label mb-1 block text-[10px] font-black uppercase text-slate-600 dark:text-slate-300">Official Branch Registration Certificate</label>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400 mb-3 font-black uppercase tracking-widest">PDF, JPG, or PNG (Max 10MB)</p>
                        
                        <input type="file" id="branch-file-input" name="certificate" class="hidden" accept=".pdf,.jpg,.png,.jpeg">
                        <div class="cursor-pointer p-6 border-2 border-dashed border-slate-300 dark:border-white/15 rounded-2xl bg-slate-50/70 dark:bg-white/2 hover:bg-slate-100 dark:hover:bg-white/5 hover:border-amber-500 dark:hover:border-amber-400 transition-all text-center" id="upload-trigger">
                            <span class="material-symbols-outlined text-4xl text-slate-400 dark:text-slate-400 mb-2" id="upload-icon" aria-hidden="true">upload_file</span>
                            <div>
                                <div class="text-sm font-black text-slate-900 dark:text-white">Click to upload document</div>
                                <div class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mt-1">Select official RBI / Banking license or branch permit</div>
                            </div>
                            <button type="button" class="btn btn-secondary btn-sm mt-4 pointer-events-none">Browse Files</button>
                        </div>
                        <div id="file-preview" class="hidden mt-3 p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/40 rounded-xl flex items-center justify-between">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="material-symbols-outlined text-emerald-600 dark:text-emerald-400 text-2xl shrink-0" aria-hidden="true">check_circle</span>
                                <div class="min-w-0">
                                    <div id="file-name" class="text-xs font-black text-emerald-800 dark:text-emerald-300 truncate max-w-[250px]">branch_cert.pdf</div>
                                    <div id="file-size" class="text-[10px] text-emerald-600 dark:text-emerald-400 font-black uppercase tracking-widest">2.4 MB</div>
                                </div>
                            </div>
                            <button type="button" id="remove-file" class="text-[10px] font-black text-red-600 dark:text-red-400 uppercase tracking-widest hover:underline px-2 py-1 shrink-0">Remove</button>
                        </div>
                    </div>

                    <!-- FOOTER -->
                    <div class="pt-6 border-t border-slate-200 dark:border-white/10 flex flex-col-reverse sm:flex-row justify-end gap-3 mt-6">
                        <button type="button" class="btn btn-secondary py-3 px-8 text-center justify-center modal-close-btn" data-close-modal="addBranchModal">Cancel</button>
                        <button type="submit" id="submit-branch-btn" class="btn btn-primary py-3 px-12 text-center justify-center flex-1 sm:flex-none">Create Branch</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- VIEW BRANCH MODAL -->
    <div id="viewBranchModal" class="fixed inset-0 z-[2000] hidden items-center justify-center p-4" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-sm transition-all modal-backdrop" data-close-modal="viewBranchModal"></div>
        <div class="relative w-full max-w-2xl bg-white dark:bg-surface-elevated text-slate-900 dark:text-white rounded-2xl overflow-hidden shadow-2xl flex flex-col max-h-[90vh] border border-slate-200 dark:border-white/10 animate-modal-enter">
            <div id="view-modal-header" class="p-6 border-b border-slate-200 dark:border-white/10 flex justify-between items-center sticky top-0 bg-white dark:bg-surface-elevated z-10 shrink-0">
                <div>
                    <h3 id="view-branch-title" class="font-black text-xl text-slate-900 dark:text-white tracking-tight">Branch Details</h3>
                    <div class="flex gap-2 mt-2" id="view-branch-badges">
                        <span class="px-2 py-0.5 bg-blue-100 dark:bg-blue-950/40 text-blue-800 dark:text-blue-300 border border-blue-300 dark:border-blue-800/40 text-[9px] font-black rounded uppercase tracking-widest font-mono">SYSTEM</span>
                        <span class="px-2 py-0.5 bg-emerald-100 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800/40 text-[9px] font-black rounded uppercase tracking-widest">ACTIVE</span>
                    </div>
                </div>
                <button type="button" class="p-2 hover:bg-slate-100 dark:hover:bg-white/10 rounded-full transition-colors text-slate-400 hover:text-slate-900 dark:hover:text-white modal-close-btn" data-close-modal="viewBranchModal">
                    <span class="material-symbols-outlined" aria-hidden="true">close</span>
                </button>
            </div>

            <div class="p-6 sm:p-8 grid grid-cols-1 sm:grid-cols-2 gap-6 overflow-y-auto flex-1">
                <div class="space-y-6">
                    <div class="p-5 bg-slate-50 dark:bg-surface-dark border border-slate-200 dark:border-white/5 rounded-2xl">
                        <label class="block text-[10px] font-black uppercase text-slate-500 dark:text-slate-400 mb-3">Branch Location & ID</label>
                        <div class="space-y-4">
                            <div>
                                <div class="text-[10px] text-slate-500 dark:text-slate-400 uppercase tracking-widest font-black">Branch Code</div>
                                <div id="view-branch-code" class="font-bold text-sm tracking-tight text-amber-700 dark:text-amber-400 font-mono mt-0.5">—</div>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <div class="text-[10px] text-slate-500 dark:text-slate-400 uppercase tracking-widest font-black">City / District</div>
                                    <div id="view-branch-city" class="font-bold text-sm text-slate-900 dark:text-white mt-0.5">—</div>
                                </div>
                                <div>
                                    <div class="text-[10px] text-slate-500 dark:text-slate-400 uppercase tracking-widest font-black">State / UT</div>
                                    <div id="view-branch-state" class="font-bold text-sm text-slate-900 dark:text-white mt-0.5">—</div>
                                </div>
                            </div>
                            <div>
                                <div class="text-[10px] text-slate-500 dark:text-slate-400 uppercase tracking-widest font-black">Full Address</div>
                                <div id="view-branch-address" class="text-xs font-bold leading-relaxed text-slate-800 dark:text-slate-300 mt-0.5">—</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-5 bg-slate-50 dark:bg-surface-dark border border-slate-200 dark:border-white/5 rounded-2xl">
                        <label class="block text-[10px] font-black uppercase text-slate-500 dark:text-slate-400 mb-3">Branch Manager</label>
                        <div class="flex items-center gap-4">
                            <div id="view-manager-avatar" class="w-12 h-12 rounded-full flex items-center justify-center bg-amber-500/15 text-amber-700 dark:text-amber-400 font-black text-lg shrink-0 border border-amber-500/25">??</div>
                            <div class="min-w-0">
                                <div id="view-manager-name" class="font-black text-sm text-slate-900 dark:text-white truncate">Unassigned</div>
                                <div id="view-manager-email" class="text-[10px] text-slate-500 dark:text-slate-400 font-bold tracking-widest uppercase truncate">No Manager Assigned</div>
                                <a href="#" id="view-manager-link" class="text-[10px] text-amber-700 dark:text-amber-400 font-black uppercase tracking-widest mt-1 inline-block hover:underline">View Staff Profile &rarr;</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-amber-500/10 dark:bg-amber-500/10 p-4 rounded-xl text-center border border-amber-500/20 dark:border-amber-500/20">
                            <div id="view-staff-count" class="text-2xl font-black text-amber-700 dark:text-amber-400 tracking-tight font-display">0</div>
                            <div class="text-[10px] text-slate-600 dark:text-slate-400 font-black uppercase tracking-widest mt-1">Assigned Staff</div>
                        </div>
                        <div class="bg-emerald-500/10 dark:bg-emerald-500/10 p-4 rounded-xl text-center border border-emerald-500/20 dark:border-emerald-500/20">
                            <div id="view-txn-count" class="text-2xl font-black text-emerald-700 dark:text-emerald-400 tracking-tight font-display">0</div>
                            <div class="text-[10px] text-slate-600 dark:text-slate-400 font-black uppercase tracking-widest mt-1">Total Transactions</div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-500 dark:text-slate-400 mb-2">Registration Certificate</label>
                        <div id="view-cert-container" class="hidden items-center gap-3 p-3 bg-slate-50 dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-white/10">
                            <span class="material-symbols-outlined text-red-600 dark:text-red-400 text-2xl shrink-0" aria-hidden="true">picture_as_pdf</span>
                            <div class="flex-1 min-w-0">
                                <div id="view-cert-name" class="text-xs font-bold text-slate-900 dark:text-white truncate">certificate.pdf</div>
                            </div>
                            <a id="view-cert-download" href="#" target="_blank" class="p-2 text-amber-700 dark:text-amber-400 hover:bg-slate-100 dark:hover:bg-white/10 rounded-lg transition-colors shrink-0" title="Download Document">
                                <span class="material-symbols-outlined text-base" aria-hidden="true">download</span>
                            </a>
                        </div>
                        <div id="view-cert-empty" class="text-[10px] p-4 bg-slate-50 dark:bg-surface-dark rounded-xl text-slate-500 dark:text-slate-400 font-black uppercase tracking-widest text-center border border-slate-200 dark:border-white/5">No certificate uploaded</div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-500 dark:text-slate-400 mb-2">Branch Description</label>
                        <div id="view-branch-desc" class="text-xs p-4 bg-slate-50 dark:bg-surface-dark rounded-xl text-slate-700 dark:text-slate-300 font-medium leading-relaxed italic border border-slate-200 dark:border-white/5">
                            No description provided.
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-6 border-t border-slate-200 dark:border-white/10 flex flex-wrap gap-3 justify-end items-center bg-white dark:bg-surface-elevated shrink-0">
                <button type="button" id="view-btn-toggle-status" class="btn btn-secondary px-5 text-xs font-bold flex items-center gap-1.5"></button>
                <button type="button" id="view-btn-edit" class="btn btn-secondary px-6">Edit Branch Info</button>
                <button type="button" id="view-btn-staff" class="btn !bg-amber-600 hover:!bg-amber-500 active:!bg-amber-700 !text-white font-bold px-6 shadow-md shadow-amber-900/20 border-0">View Staff List</button>
            </div>
        </div>
    </div>

    <!-- BRANCH STATUS TOGGLE MODAL (SUSPEND / REACTIVATE) -->
    <div id="branchStatusModal" class="fixed inset-0 z-[3000] hidden items-center justify-center p-4" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-sm transition-all modal-backdrop" data-close-modal="branchStatusModal"></div>
        <div class="relative w-full max-w-md bg-white dark:bg-surface-elevated text-slate-900 dark:text-white rounded-2xl overflow-hidden shadow-2xl flex flex-col max-h-[90vh] border border-slate-200 dark:border-white/10 p-6 sm:p-8 text-center animate-modal-enter">
            <div id="status-modal-icon-box" class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800/40">
                <span class="material-symbols-outlined text-3xl" id="status-modal-icon" aria-hidden="true">pause_circle</span>
            </div>
            
            <h3 id="status-modal-title" class="font-headline text-xl font-black text-slate-900 dark:text-white tracking-tight mb-1.5">Suspend Branch</h3>
            <p id="status-modal-desc" class="text-xs text-slate-600 dark:text-slate-300 font-medium leading-relaxed mb-4">
                You are changing the operational status of <span class="font-black text-slate-900 dark:text-white" id="status-target-branch-name">—</span>.
            </p>

            <div id="status-modal-alert" class="p-3 bg-amber-500/10 dark:bg-amber-500/10 border border-amber-500/20 dark:border-amber-500/20 rounded-xl flex items-center gap-2.5 text-left mb-5">
                <span class="material-symbols-outlined text-amber-600 dark:text-amber-400 text-lg shrink-0" aria-hidden="true">info</span>
                <p id="status-modal-alert-text" class="text-[11px] font-bold text-amber-700 dark:text-amber-300 leading-tight">
                    Suspending this branch will mark it as INACTIVE. Operational transactions and new staff teller assignments will be paused.
                </p>
            </div>

            <form id="branchStatusForm" class="space-y-4 text-left mb-4" novalidate>
                <input type="hidden" id="status-branch-id" value="">
                <input type="hidden" id="status-target-action" value="inactive">

                <div>
                    <label for="status-admin-pw" class="fin-label mb-1.5 block text-[10px] font-black uppercase text-slate-600 dark:text-slate-300">Admin Security Password *</label>
                    <div class="fin-input-wrapper">
                        <span class="material-symbols-outlined input-icon-left text-slate-400 dark:text-slate-400 text-lg" aria-hidden="true">admin_panel_settings</span>
                        <input type="password" id="status-admin-pw" name="admin_password" placeholder="Enter your administrator password" required class="fin-input has-icon-left has-icon-right bg-white dark:bg-surface-subtle text-slate-900 dark:text-white border-slate-200 dark:border-white/10 shadow-sm">
                        <button type="button" id="toggle-status-pw" class="password-toggle-btn text-slate-400 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors" aria-label="Toggle password visibility">
                            <span class="material-symbols-outlined text-lg" id="toggle-status-pw-icon" aria-hidden="true">visibility</span>
                        </button>
                    </div>
                </div>

                <div>
                    <label for="status-reason-input" class="fin-label mb-1.5 block text-[10px] font-black uppercase text-slate-600 dark:text-slate-300">Reason / Operational Note (Optional)</label>
                    <textarea id="status-reason-input" name="reason" rows="2" placeholder="e.g. Scheduled annual renovation, regulatory compliance review..." class="fin-input bg-white dark:bg-surface-subtle text-slate-900 dark:text-white border-slate-200 dark:border-white/10 shadow-sm"></textarea>
                </div>

                <div class="flex flex-col gap-2.5 pt-2">
                    <button type="submit" id="status-submit-btn" class="btn w-full justify-center !py-3 !bg-amber-600 hover:!bg-amber-500 !text-white font-bold border-0 shadow-lg">Confirm Suspension</button>
                    <button type="button" class="btn btn-secondary w-full justify-center !py-2.5 modal-close-btn" data-close-modal="branchStatusModal">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- BRANCH STAFF MODAL -->
    <div id="branchStaffModal" class="fixed inset-0 z-[2500] hidden items-center justify-center p-4" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-sm transition-all modal-backdrop" data-close-modal="branchStaffModal"></div>
        <div class="relative w-full max-w-4xl bg-white dark:bg-surface-elevated text-slate-900 dark:text-white rounded-2xl overflow-hidden shadow-2xl flex flex-col max-h-[90vh] border border-slate-200 dark:border-white/10 animate-modal-enter">
            <div class="p-6 border-b border-slate-200 dark:border-white/10 flex justify-between items-center sticky top-0 bg-white dark:bg-surface-elevated z-10 shrink-0">
                <div>
                    <h3 id="staff-modal-title" class="font-black text-xl text-slate-900 dark:text-white tracking-tight">Branch Staff List</h3>
                    <p id="staff-modal-subtitle" class="text-[10px] text-slate-500 dark:text-slate-400 uppercase font-black tracking-widest mt-1">Loading staff members...</p>
                </div>
                <button type="button" class="p-2 hover:bg-slate-100 dark:hover:bg-white/10 rounded-full transition-colors text-slate-400 hover:text-slate-900 dark:hover:text-white modal-close-btn" data-close-modal="branchStaffModal">
                    <span class="material-symbols-outlined" aria-hidden="true">close</span>
                </button>
            </div>
            
            <div class="p-0 overflow-y-auto flex-1">
                <div class="ds-table-wrapper border-0 rounded-none shadow-none bg-transparent">
                    <table class="ds-table w-full text-left">
                        <thead scope="col" class="bg-slate-50 dark:bg-surface-dark border-b border-slate-200 dark:border-white/10">
                            <tr>
                                <th class="px-6 py-4 text-[10px] font-black tracking-widest text-slate-600 dark:text-slate-400 uppercase" scope="col">Staff ID</th>
                                <th class="px-6 py-4 text-[10px] font-black tracking-widest text-slate-600 dark:text-slate-400 uppercase" scope="col">Name & Role</th>
                                <th class="px-6 py-4 text-[10px] font-black tracking-widest text-slate-600 dark:text-slate-400 uppercase" scope="col">Contact</th>
                                <th class="px-6 py-4 text-[10px] font-black tracking-widest text-slate-600 dark:text-slate-400 uppercase" scope="col">Status</th>
                                <th class="px-6 py-4 text-[10px] font-black tracking-widest text-slate-600 dark:text-slate-400 uppercase text-right" scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody id="branch-ds-table-body">
                            <!-- Staff rows injected here -->
                        </tbody>
                    </table>
                </div>
                <div id="no-staff-error" class="hidden p-12 text-center" aria-live="polite">
                    <span class="material-symbols-outlined text-4xl text-slate-400 dark:text-slate-400 mb-2" aria-hidden="true">person_off</span>
                    <div class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">No staff members assigned to this branch yet.</div>
                </div>
            </div>
            
            <div class="p-6 border-t border-slate-200 dark:border-white/10 flex justify-end bg-white dark:bg-surface-elevated shrink-0">
                <button type="button" class="btn btn-secondary px-8 modal-close-btn" data-close-modal="branchStaffModal">Close List</button>
            </div>
        </div>
    </div>

    <!-- DELETE CONFIRMATION MODAL -->
    <div id="confirmModal" class="fixed inset-0 z-[3000] hidden items-center justify-center p-4" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-sm transition-all modal-backdrop" data-close-modal="confirmModal"></div>
        <div class="relative w-full max-w-md bg-white dark:bg-surface-elevated text-slate-900 dark:text-white rounded-2xl overflow-hidden shadow-2xl flex flex-col max-h-[90vh] border border-slate-200 dark:border-white/10 p-6 sm:p-8 text-center animate-modal-enter">
            <div id="confirm-icon-box" class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-5 bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800/40">
                <span class="material-symbols-outlined text-3xl" aria-hidden="true">delete_forever</span>
            </div>
            <h3 id="confirm-title" class="font-headline text-xl font-black text-slate-900 dark:text-white tracking-tight mb-2">Delete Branch</h3>
            <p id="confirm-text" class="text-xs text-slate-600 dark:text-slate-300 font-medium leading-relaxed mb-6">You are permanently decommissioning <span class="font-black text-slate-900 dark:text-white" id="target-branch-name">—</span>. All assigned staff will be unlinked.</p>
            
            <div id="confirm-input-area" class="space-y-4 text-left mb-6 overflow-y-auto">
                <div>
                    <label for="confirm-branch-name-input" class="fin-label mb-1.5 block text-[10px] font-black uppercase text-slate-600 dark:text-slate-300">Type Branch Name to Confirm *</label>
                    <input type="text" id="confirm-branch-name-input" placeholder="Type exact branch name" class="fin-input bg-white dark:bg-surface-subtle text-slate-900 dark:text-white border-slate-200 dark:border-white/10 shadow-sm">
                </div>
                
                <div>
                    <label for="confirm-admin-pw" class="fin-label mb-1.5 block text-[10px] font-black uppercase text-slate-600 dark:text-slate-300">Admin Password *</label>
                    <div class="fin-input-wrapper">
                        <span class="material-symbols-outlined input-icon-left text-slate-400 dark:text-slate-400 text-lg" aria-hidden="true">lock</span>
                        <input type="password" id="confirm-admin-pw" placeholder="Enter administrator password" class="fin-input has-icon-left bg-white dark:bg-surface-subtle text-slate-900 dark:text-white border-slate-200 dark:border-white/10 shadow-sm">
                    </div>
                </div>

                <div>
                    <label class="fin-label mb-1.5 block text-[10px] font-black uppercase text-slate-600 dark:text-slate-300">Official Decommission Document (Optional)</label>
                    <div id="delete-upload-trigger" class="cursor-pointer p-4 border-2 border-dashed border-slate-300 dark:border-white/15 rounded-xl bg-slate-50/70 dark:bg-white/2 hover:bg-slate-100 dark:hover:bg-white/5 hover:border-amber-500 dark:hover:border-amber-400 transition-all text-center">
                        <span class="material-symbols-outlined text-slate-400 dark:text-slate-400 mb-1 text-2xl" aria-hidden="true">upload_file</span>
                        <div class="text-[10px] font-black text-slate-900 dark:text-white uppercase tracking-widest">Upload closure authorization</div>
                        <div class="text-[10px] text-slate-500 dark:text-slate-400 font-bold uppercase tracking-widest mt-0.5">PDF, JPG, or PNG (Max 5MB)</div>
                    </div>
                    <input type="file" id="delete-file-input" class="hidden" accept=".pdf,.jpg,.jpeg,.png">
                    <div id="delete-file-preview" class="hidden items-center gap-3 p-3 bg-primary-50 dark:bg-primary/10 rounded-xl border border-primary-100 dark:border-primary/20 mt-2">
                        <span class="material-symbols-outlined text-primary dark:text-primary-400 text-xl shrink-0" aria-hidden="true">description</span>
                        <div class="flex-1 min-w-0">
                            <div id="delete-file-name" class="text-xs font-black text-slate-900 dark:text-white truncate">document.pdf</div>
                        </div>
                        <button type="button" id="remove-delete-file" class="p-1 hover:bg-white dark:hover:bg-white/10 rounded-lg transition-colors text-red-600 dark:text-red-400 shrink-0">
                            <span class="material-symbols-outlined text-sm" aria-hidden="true">close</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-3">
                <button type="button" id="confirm-btn-action" class="btn btn-primary w-full justify-center !py-3 !bg-red-600 hover:!bg-red-700 !text-white border-0 opacity-50 cursor-not-allowed" disabled>Permanently Delete Branch</button>
                <button type="button" class="btn btn-secondary w-full justify-center !py-3 modal-close-btn" data-close-modal="confirmModal">Cancel Action</button>
            </div>
        </div>
    </div>
<?php
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/branches.js"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
