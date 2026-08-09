<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin - Branch Management';
$pageHeader = '
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-headline font-black text-text-primary tracking-tight">Branch Management</h1>
        <p class="text-[10px] font-black text-text-secondary uppercase tracking-widest mt-0.5">Manage bank branches and their configurations</p>
    </div>
    <div class="flex items-center gap-3">
        <button data-action="openAddModal()" class="btn btn-primary flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">add</span> Add New Branch
        </button>
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
            <span class="material-symbols-outlined text-xl" aria-hidden="true">store</span>
        </div>
        <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1">TOTAL BRANCHES</div>
        <div id="stat-total-branches" class="text-xl font-black font-mono tracking-tight text-text-primary skeleton min-w-[40px]">—</div>
        </div>
    </div>
    <div class="ds-card p-5 relative overflow-hidden group hover:border-success hover:shadow-md transition-all">
        <div class="absolute right-0 top-0 w-16 h-16 bg-success-bg/50 rounded-bl-full -z-0"></div>
        <div class="relative z-10">
        <div class="w-10 h-10 rounded-xl bg-success-bg text-success flex items-center justify-center mb-4 border border-success-bg shadow-sm" >
            <span class="material-symbols-outlined text-xl" aria-hidden="true">check_circle</span>
        </div>
        <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1">ACTIVE BRANCHES</div>
        <div id="stat-active-branches" class="text-xl font-black font-mono tracking-tight text-text-primary skeleton min-w-[40px]">—</div>
        </div>
    </div>
    <div class="ds-card p-5 relative overflow-hidden group hover:border-primary hover:shadow-md transition-all">
        <div class="absolute right-0 top-0 w-16 h-16 bg-primary-50/50 rounded-bl-full -z-0"></div>
        <div class="relative z-10">
        <div class="w-10 h-10 rounded-xl bg-primary-50 text-primary flex items-center justify-center mb-4 border border-primary-100 shadow-sm" >
            <span class="material-symbols-outlined text-xl" aria-hidden="true">badge</span>
        </div>
        <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1">TOTAL STAFF ASSIGNED</div>
        <div id="stat-total-staff" class="text-xl font-black font-mono tracking-tight text-text-primary skeleton min-w-[40px]">—</div>
        </div>
    </div>
    </div>

    <!-- INFO BANNER -->
    <div class="p-4 bg-info-bg border border-info-bg rounded-xl flex gap-3 mb-6">
    <span class="material-symbols-outlined text-info text-2xl" aria-hidden="true">info</span>
    <div>
        <div class="text-sm font-black text-info">Main Branch Note</div>
        <div class="text-xs font-bold text-info/80 mt-0.5">Finzo\'s main branch is <b>Barasat</b>. This is the primary hub and cannot be deleted. All other branches can be managed or removed as needed.</div>
    </div>
    </div>

    <!-- SEARCH & TABLE CARD -->
    <div class="ds-card relative z-20">
    <div class="p-4 sm:p-6 border-b border-border-subtle bg-background">
        <div class="relative">
        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-text-secondary transition-colors" aria-hidden="true">search</span>
        <input type="text" id="admin-branch-finder" name="branch_query_ignore" placeholder="Search by branch name, location or manager..." 
            autocomplete="one-time-code" data-lpignore="true" 
            class="fin-input pl-12">
        </div>
    </div>

    <div class="ds-table-wrapper border-0 rounded-none shadow-none bg-transparent">
        <table class="ds-table w-full min-w-[1000px] text-left">
        <thead scope="col">
            <tr>
            <th class="w-[5%]" scope="col">#</th>
            <th class="w-[20%]" scope="col">BRANCH</th>
            <th class="w-[12%]" scope="col">BRANCH ID</th>
            <th class="w-[12%]" scope="col">STATE</th>
            <th class="w-[16%]" scope="col">MANAGER</th>
            <th class="w-[8%] text-center" scope="col">STAFF</th>
            <th class="w-[10%]" scope="col">TYPE</th>
            <th class="w-[8%]" scope="col">STATUS</th>
            <th class="w-[9%] text-right" scope="col">ACTIONS</th>
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
            <td><div class="skeleton w-40 h-4"></div></td>
            <td><div class="skeleton w-48 h-6"></div></td>
            <td><div class="skeleton w-8 h-4 mx-auto"></div></td>
            <td><div class="skeleton w-20 h-6 rounded-lg"></div></td>
            <td><div class="skeleton w-24 h-6 rounded-full"></div></td>
            <td class="text-right"><div class="skeleton w-24 h-8 ml-auto"></div></td>
            </tr>
        </tbody>
        </table>
    </div>

    <!-- PAGINATION -->
    <div class="p-4 sm:p-6 bg-background border-t border-border-subtle flex flex-col sm:flex-row justify-between items-center gap-4 transition-all w-full">
        <div class="text-[10px] text-text-secondary font-black uppercase tracking-widest">Showing 1-5 of 5 branches</div>
        <div class="flex items-center gap-2">
        <button class="btn btn-secondary btn-sm" >&larr; Previous</button>
        <div class="flex gap-1.5">
            <button class="btn btn-primary btn-sm" >1</button>
        </div>
        <button class="btn btn-secondary btn-sm" >Next &rarr;</button>
        </div>
    </div>
    </div>

  <!-- ADD/EDIT BRANCH MODAL -->
  <div id="addBranchModal" class="fixed inset-0 z-[2000] hidden items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-surface-dark/80 backdrop-blur-sm transition-all" data-action="$(&#039;#addBranchModal&#039;).addClass(&#039;hidden&#039;).removeClass(&#039;flex&#039;)"></div>
    <div class="relative w-full max-w-2xl bg-background rounded-2xl overflow-hidden shadow-2xl flex flex-col max-h-full border border-border-subtle animate-modal-enter">
      <div class="p-6 border-b border-border-subtle flex justify-between items-center sticky top-0 bg-background z-10">
        <div>
          <h3 class="font-black text-xl text-text-primary tracking-tight">Add New Branch</h3>
          <p class="text-[10px] font-black uppercase tracking-widest text-text-secondary mt-1">Fill in all details to register a new Finzo branch</p>
        </div>
        <button data-action="$(&#039;#addBranchModal&#039;).addClass(&#039;hidden&#039;).removeClass(&#039;flex&#039;)" class="p-2 hover:bg-surface-hover rounded-full transition-colors text-text-secondary hover:text-text-primary">
          <span class="material-symbols-outlined" aria-hidden="true">close</span>
        </button>
      </div>
      <div class="p-8 overflow-y-auto">
        <form class="space-y-8" id="addBranchForm">
          <!-- SECTION 1 -->
          <div>
            <div class="text-[10px] font-black tracking-widest text-primary uppercase mb-4 py-1 border-b border-border-subtle">SECTION 1: Branch Information</div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
              <div class="col-span-1">
                <label for="add-branch-name" class="fin-label mb-2 block text-[10px] font-black uppercase text-text-secondary">Branch Name *</label>
                <input type="text" id="add-branch-name" name="branch_name" placeholder="e.g. West Branch" required class="fin-input">
              </div>
              <div class="col-span-1">
                <label for="add-branch-code" class="fin-label mb-2 block text-[10px] font-black uppercase text-text-secondary">Branch Code *</label>
                <input type="text" id="add-branch-code" name="branch_code" placeholder="FNZ-XX-000" readonly class="fin-input bg-surface-hover cursor-not-allowed">
                <p class="text-[10px] text-text-secondary mt-1 font-bold">System Generated — Cannot be manualy edited.</p>
              </div>
              <div class="col-span-1">
                <label class="fin-label mb-2 block text-[10px] font-black uppercase text-text-secondary">City *</label>
                <input type="text" name="city" placeholder="e.g. Kolkata" required class="fin-input">
              </div>
              <div class="col-span-1">
                <label for="branch-state" class="fin-label mb-2 block text-[10px] font-black uppercase text-text-secondary">State *</label>
                <div class="finzo-dropdown w-full" id="branch-state-dropdown">
                  <div class="finzo-dropdown-trigger fin-input !flex items-center justify-between cursor-pointer">
                    <span class="dropdown-label font-bold text-sm text-text-primary">Select State / UT</span>
                    <span class="material-symbols-outlined chevron text-text-secondary" aria-hidden="true">expand_more</span>
                  </div>
                  <div class="finzo-dropdown-menu w-full !max-h-[240px] z-50">
                    <div class="px-4 py-2 text-[10px] font-black uppercase text-text-secondary tracking-widest bg-background sticky top-0 z-10">States</div>
                    <div class="finzo-dropdown-option" data-value="Andhra Pradesh">Andhra Pradesh</div>
                    <div class="finzo-dropdown-option" data-value="Assam">Assam</div>
                    <div class="finzo-dropdown-option" data-value="West Bengal">West Bengal</div>
                    <div class="px-4 py-2 mt-2 text-[10px] font-black uppercase text-text-secondary tracking-widest bg-background sticky top-0 z-10">Union Territories</div>
                    <div class="finzo-dropdown-option" data-value="Delhi">Delhi</div>
                  </div>
                  <input type="hidden" id="branch-state" name="state" required value="">
                </div>
              </div>
              <div class="col-span-1 sm:col-span-2">
                <label class="fin-label mb-2 block text-[10px] font-black uppercase text-text-secondary">Full Address *</label>
                <textarea name="address" rows="2" placeholder="Building no., street name, locality, city, PIN code" required class="fin-input"></textarea>
              </div>
            </div>
          </div>

          <!-- SECTION 2 -->
          <div>
            <div class="text-[10px] font-black tracking-widest text-primary uppercase mb-4 py-1 border-b border-border-subtle">SECTION 2: Branch Manager</div>
            <div class="mb-4">
              <label for="manager-id" class="fin-label mb-2 block text-[10px] font-black uppercase text-text-secondary">Assign Branch Manager *</label>
              
              <div class="finzo-dropdown w-full" id="manager-dropdown">
                <div class="finzo-dropdown-trigger fin-input !flex items-center justify-between cursor-pointer">
                  <span class="dropdown-label font-bold text-sm text-text-primary">Select Staff Member</span>
                  <span class="material-symbols-outlined chevron text-text-secondary" aria-hidden="true">expand_more</span>
                </div>
                <div class="finzo-dropdown-menu w-full z-50" id="manager-dropdown-menu">
                   <div class="p-4 text-center text-xs text-text-secondary font-bold">Loading staff...</div>
                </div>
                <input type="hidden" id="manager-id" name="manager_id" value="">
              </div>

              <div class="p-3 bg-warning-bg border border-warning-bg rounded-xl flex gap-3 mt-4" >
                <span class="material-symbols-outlined text-warning text-lg" aria-hidden="true">info</span>
                <p class="text-xs text-warning font-bold">Assigning a manager will update their branch in Staff Management.</p>
              </div>
            </div>
          </div>

          <!-- SECTION 3 -->
          <div>
            <div class="text-[10px] font-black tracking-widest text-primary uppercase mb-4 py-1 border-b border-border-subtle">SECTION 3: Admin Verification</div>
            <div class="mb-2">
              <label for="admin-confirm-pw" class="fin-label mb-2 block text-[10px] font-black uppercase text-text-secondary">Admin Password *</label>
              <div class="relative">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-text-secondary text-lg" aria-hidden="true">admin_panel_settings</span>
                <input type="password" id="admin-confirm-pw" name="admin_password" placeholder="Enter your admin password" required class="fin-input pl-12 pr-12">
                <button type="button" class="absolute right-4 top-1/2 -translate-y-1/2 text-text-secondary hover:text-text-primary transition-colors">
                  <span class="material-symbols-outlined text-lg" aria-hidden="true">visibility</span>
                </button>
              </div>
              <div class="p-3 bg-error-bg border border-error-bg rounded-xl flex gap-3 mt-4" >
                <span class="material-symbols-outlined text-error text-lg" aria-hidden="true">shield</span>
                <p class="text-xs text-error font-bold">This verification ensures branch creation is authorized and logged.</p>
              </div>
            </div>
          </div>

          <!-- SECTION 4 -->
          <div>
             <label for="branch-desc" class="fin-label mb-2 block text-[10px] font-black uppercase text-text-secondary">Branch Description / Notes</label>
             <textarea id="branch-desc" name="description" rows="4" maxlength="500" placeholder="Describe this branch — services offered, operating hours, etc." class="fin-input"></textarea>
             <div class="char-counter text-xs text-text-secondary font-bold text-right mt-1" id="branch-desc-counter">0 / 500</div>
          </div>

          <!-- SECTION 5 -->
          <div>
            <label for="branch-file-input" class="fin-label mb-1 block text-[10px] font-black uppercase text-text-secondary">Branch Registration Certificate</label>
            <p class="text-[10px] text-text-secondary mb-3 font-black uppercase tracking-widest">Upload the official documents</p>
            
            <input type="file" id="branch-file-input" name="certificate" class="hidden" accept=".pdf,.jpg,.png,.jpeg">
            <div class="cursor-pointer p-6 border-2 border-dashed border-border-subtle rounded-2xl hover:bg-surface-hover hover:border-primary transition-all text-center" id="upload-trigger">
              <span class="material-symbols-outlined text-4xl text-text-secondary mb-2" id="upload-icon" aria-hidden="true">upload_file</span>
              <div>
                <div class="text-sm font-black text-text-primary">Upload Registration Certificate</div>
                <div class="text-[10px] font-black uppercase tracking-widest text-text-secondary mt-1">PDF, JPG or PNG — Max 10MB</div>
              </div>
              <button type="button" class="btn btn-secondary btn-sm mt-4">Browse Files</button>
            </div>
            <div id="file-preview" class="hidden mt-3 p-4 bg-success-bg border border-success-bg rounded-xl flex items-center justify-between">
               <div class="flex items-center gap-3">
                  <span class="material-symbols-outlined text-success" aria-hidden="true">check_circle</span>
                  <div>
                    <div id="file-name" class="text-xs font-black text-success truncate max-w-[200px]">branch_cert.pdf</div>
                    <div id="file-size" class="text-[10px] text-success/80 font-black uppercase tracking-widest">2.4 MB</div>
                  </div>
               </div>
               <button type="button" id="remove-file" class="text-[10px] font-black text-error uppercase tracking-widest hover:underline px-2 py-1">Remove</button>
            </div>
          </div>

          <!-- FOOTER -->
          <div class="pt-6 border-t border-border-subtle flex flex-col-reverse sm:flex-row justify-end gap-3 mt-8">
              <button type="button" data-action="$(&#039;#addBranchModal&#039;).addClass(&#039;hidden&#039;).removeClass(&#039;flex&#039;)" class="btn btn-secondary py-3 px-8 text-center justify-center">Cancel</button>
              <button type="submit" id="submit-branch-btn" class="btn btn-primary py-3 px-12 text-center justify-center flex-1 sm:flex-none" >Create Branch</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- VIEW BRANCH MODAL -->
  <div id="viewBranchModal" class="fixed inset-0 z-[2000] hidden items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-surface-dark/80 backdrop-blur-sm transition-all" data-action="$(&#039;#viewBranchModal&#039;).addClass(&#039;hidden&#039;).removeClass(&#039;flex&#039;)"></div>
    <div class="relative w-full max-w-2xl bg-background rounded-2xl overflow-hidden shadow-2xl flex flex-col max-h-full border border-border-subtle animate-modal-enter">
      <div id="view-modal-header" class="p-6 border-b border-border-subtle flex justify-between items-center sticky top-0 bg-background z-10" role="dialog" aria-modal="true">
        <div>
          <h3 id="view-branch-title" class="font-black text-xl text-text-primary tracking-tight">Barasat</h3>
          <div class="flex gap-2 mt-2" id="view-branch-badges">
            <span class="px-2 py-0.5 bg-background border border-border-subtle text-[9px] font-black rounded uppercase tracking-widest text-text-secondary font-mono" >SYSTEM</span>
            <span class="px-2 py-0.5 bg-success-bg text-success border border-success-bg text-[9px] font-black rounded uppercase tracking-widest">ACTIVE</span>
          </div>
        </div>
        <button data-action="$(&#039;#viewBranchModal&#039;).addClass(&#039;hidden&#039;).removeClass(&#039;flex&#039;)" class="p-2 hover:bg-surface-hover rounded-full transition-colors text-text-secondary hover:text-text-primary">
          <span class="material-symbols-outlined" aria-hidden="true">close</span>
        </button>
      </div>
      <div class="p-8 grid grid-cols-1 sm:grid-cols-2 gap-8 overflow-y-auto">
        <div class="space-y-6">
          <div class="ds-card !bg-surface-hover !p-5 !border-none">
            <label class="block text-[10px] font-black uppercase text-text-secondary mb-3">Branch Details</label>
            <div class="space-y-4">
               <div>
                 <div class="text-[10px] text-text-secondary uppercase tracking-widest font-black">Branch Code</div>
                 <div id="view-branch-code" class="font-bold text-sm tracking-tight text-primary font-mono mt-0.5">FNZ-MB-101</div>
               </div>
               <div class="grid grid-cols-2">
                 <div>
                    <div class="text-[10px] text-text-secondary uppercase tracking-widest font-black">City</div>
                    <div id="view-branch-city" class="font-bold text-sm text-text-primary mt-0.5">Kolkata</div>
                 </div>
                 <div>
                    <div class="text-[10px] text-text-secondary uppercase tracking-widest font-black">State</div>
                    <div id="view-branch-state" class="font-bold text-sm text-text-primary mt-0.5">West Bengal</div>
                 </div>
               </div>
               <div>
                  <div class="text-[10px] text-text-secondary uppercase tracking-widest font-black">Full Address</div>
                  <div id="view-branch-address" class="text-xs font-bold leading-relaxed text-text-primary mt-0.5">12/B Park Street, Near Metro Gate 4, Kolkata 700016</div>
               </div>
            </div>
          </div>
          
          <div class="ds-card !bg-surface-hover !p-5 !border-none">
            <label class="block text-[10px] font-black uppercase text-text-secondary mb-3">Manager Info</label>
            <div class="flex items-center gap-4">
               <div id="view-manager-avatar" class="w-12 h-12 rounded-full flex items-center justify-center bg-primary-50 text-primary font-black text-lg">AS</div>
               <div>
                  <div id="view-manager-name" class="font-black text-sm text-text-primary">Amit Sharma</div>
                  <div id="view-manager-email" class="text-[10px] text-text-secondary font-black tracking-widest uppercase">amit.sharma@finzobank.com</div>
                  <a href="#" id="view-manager-link" class="text-[10px] text-primary font-black uppercase tracking-widest mt-1 block hover:underline">View Staff Profile</a>
               </div>
            </div>
          </div>
        </div>

        <div class="space-y-6">
           <div class="grid grid-cols-2 gap-4">
              <div class="bg-primary-50 p-4 rounded-xl text-center border border-primary-100">
                 <div id="view-staff-count" class="text-xl font-black text-primary tracking-tight">3</div>
                 <div class="text-[10px] text-text-secondary font-black uppercase tracking-widest mt-1">Total Staff</div>
              </div>
              <div class="bg-success-bg p-4 rounded-xl text-center border border-success-bg">
                 <div id="view-txn-count" class="text-xl font-black text-success tracking-tight">342</div>
                 <div class="text-[10px] text-text-secondary font-black uppercase tracking-widest mt-1">Total Trans</div>
              </div>
           </div>

           <div>
              <label class="block text-[10px] font-black uppercase text-text-secondary mb-2">Registration Certificate</label>
              <div id="view-cert-container" class="flex items-center gap-3 p-3 bg-surface-hover rounded-xl border border-border-subtle">
                 <span class="material-symbols-outlined text-error" aria-hidden="true">picture_as_pdf</span>
                 <div class="flex-1 min-w-0">
                    <div id="view-cert-name" class="text-xs font-bold text-text-primary truncate">main_branch_reg.pdf</div>
                 </div>
                 <a id="view-cert-download" href="#" target="_blank" class="p-2 text-primary hover:bg-background rounded-lg transition-colors"><span class="material-symbols-outlined text-sm" aria-hidden="true">download</span></a>
              </div>
              <div id="view-cert-empty" class="hidden text-[10px] p-4 bg-surface-hover rounded-xl text-text-secondary font-black uppercase tracking-widest text-center">No certificate uploaded</div>
           </div>

           <div>
              <label class="block text-[10px] font-black uppercase text-text-secondary mb-2">Branch Description</label>
              <div id="view-branch-desc" class="text-xs p-4 bg-surface-hover rounded-xl text-text-secondary font-bold leading-relaxed italic">
                 "Main central hub responsible for high-value corporate accounts and treasury operations. Operates 24/7 with specialized support for international transfers."
              </div>
           </div>
        </div>
      </div>
      <div class="p-6 border-t border-border-subtle flex gap-3 justify-end bg-background shrink-0">
         <button id="view-btn-edit" class="btn btn-secondary px-6">Edit Branch Info</button>
         <button id="view-btn-staff" class="btn btn-primary px-6" >View Staff List</button>
      </div>
    </div>
  </div>

  <!-- BRANCH STAFF MODAL -->
  <div id="branchStaffModal" class="fixed inset-0 z-[2500] hidden items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-surface-dark/80 backdrop-blur-sm transition-all" data-action="$(&#039;#branchStaffModal&#039;).addClass(&#039;hidden&#039;).removeClass(&#039;flex&#039;)"></div>
    <div class="relative w-full max-w-4xl bg-background rounded-2xl overflow-hidden shadow-2xl flex flex-col max-h-full border border-border-subtle animate-modal-enter">
      <div class="p-6 border-b border-border-subtle flex justify-between items-center sticky top-0 bg-background z-10">
        <div>
          <h3 id="staff-modal-title" class="font-black text-xl text-text-primary tracking-tight">Branch Staff List</h3>
          <p id="staff-modal-subtitle" class="text-[10px] text-text-secondary uppercase font-black tracking-widest mt-1">Loading staff members...</p>
        </div>
        <button data-action="$(&#039;#branchStaffModal&#039;).addClass(&#039;hidden&#039;).removeClass(&#039;flex&#039;)" class="p-2 hover:bg-surface-hover rounded-full transition-colors text-text-secondary hover:text-text-primary">
          <span class="material-symbols-outlined" aria-hidden="true">close</span>
        </button>
      </div>
      <div class="p-0 overflow-y-auto">
        <div class="ds-table-wrapper border-0 rounded-none shadow-none bg-transparent">
          <table class="ds-table w-full text-left">
            <thead scope="col">
              <tr class="bg-surface-hover">
                <th class="px-6 py-4 text-[10px] font-black tracking-widest text-text-secondary uppercase" scope="col">Staff ID</th>
                <th class="px-6 py-4 text-[10px] font-black tracking-widest text-text-secondary uppercase" scope="col">Name & Role</th>
                <th class="px-6 py-4 text-[10px] font-black tracking-widest text-text-secondary uppercase" scope="col">Contact</th>
                <th class="px-6 py-4 text-[10px] font-black tracking-widest text-text-secondary uppercase" scope="col">Status</th>
                <th class="px-6 py-4 text-[10px] font-black tracking-widest text-text-secondary uppercase text-right" scope="col">Action</th>
              </tr>
            </thead>
            <tbody id="branch-ds-table-body">
              <!-- Staff rows injected here -->
            </tbody>
          </table>
        </div>
        <div id="no-staff-error" class="hidden p-12 text-center" aria-live="polite">
           <span class="material-symbols-outlined text-4xl text-text-secondary mb-2" aria-hidden="true">person_off</span>
           <div class="text-[10px] font-black uppercase tracking-widest text-text-secondary">No staff members assigned to this branch yet.</div>
        </div>
      </div>
      <div class="p-6 border-t border-border-subtle flex justify-end bg-background shrink-0">
         <button data-action="$(&#039;#branchStaffModal&#039;).addClass(&#039;hidden&#039;).removeClass(&#039;flex&#039;)" class="btn btn-secondary px-8">Close List</button>
      </div>
    </div>
  </div>

  <!-- TOGGLE/DELETE CONFIRM MODALS -->
  <div id="confirmModal" class="fixed inset-0 z-[3000] hidden items-center justify-center p-4" role="dialog" aria-modal="true">
     <div class="absolute inset-0 bg-surface-dark/80 backdrop-blur-sm transition-all"></div>
     <div class="relative w-full max-w-md bg-background rounded-2xl overflow-hidden shadow-2xl flex flex-col max-h-full border border-border-subtle p-8 text-center animate-modal-enter">
        <div id="confirm-icon-box" class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6 bg-error-bg text-error">
           <span class="material-symbols-outlined text-4xl" aria-hidden="true">close</span>
        </div>
        <h3 id="confirm-title" class="font-headline text-xl font-black text-text-primary tracking-tight mb-2">Delete Branch</h3>
        <p id="confirm-text" class="text-xs text-text-secondary font-bold leading-relaxed mb-6">You are permanently deleting <span class="font-black text-text-primary" id="target-branch-name">Barasat</span>. This action cannot be undone.</p>
        
        <div id="confirm-input-area" class="hidden space-y-5 text-left mb-8">
           <div>
              <label for="confirm-branch-name-input" class="fin-label mb-2 block text-[10px] font-black uppercase text-text-secondary">Confirm Branch Name *</label>
              <input type="text" id="confirm-branch-name-input" placeholder="Type name here" class="fin-input">
           </div>
           
           <div>
              <label for="confirm-admin-pw" class="fin-label mb-2 block text-[10px] font-black uppercase text-text-secondary">Admin Security Password *</label>
              <input type="password" id="confirm-admin-pw" placeholder="Enter admin password" class="fin-input">
           </div>

           <div>
              <label for="delete-file-input" class="fin-label mb-2 block text-[10px] font-black uppercase text-text-secondary">Official Closure Document *</label>
              <div id="delete-upload-trigger" class="cursor-pointer p-4 border-2 border-dashed border-border-subtle rounded-xl hover:bg-surface-hover hover:border-primary transition-all text-center">
                 <span class="material-symbols-outlined text-text-secondary mb-1" aria-hidden="true">upload_file</span>
                 <div class="text-[10px] font-black text-text-primary uppercase tracking-widest">Click to upload document</div>
                 <div class="text-[10px] text-text-secondary font-bold uppercase tracking-widest mt-1">PDF, JPG or PNG (Max 5MB)</div>
              </div>
              <input type="file" id="delete-file-input" class="hidden" accept=".pdf,.jpg,.jpeg,.png">
              <div id="delete-file-preview" class="hidden flex items-center gap-3 p-3 bg-primary-50 rounded-xl border border-primary-100 mt-2">
                 <span class="material-symbols-outlined text-primary" aria-hidden="true">description</span>
                 <div class="flex-1 min-w-0">
                    <div id="delete-file-name" class="text-xs font-black text-text-primary truncate">document.pdf</div>
                 </div>
                 <button type="button" id="remove-delete-file" class="p-1.5 hover:bg-white rounded-lg transition-colors text-error">
                    <span class="material-symbols-outlined text-sm" aria-hidden="true">close</span>
                 </button>
              </div>
           </div>
        </div>

        <div class="flex flex-col gap-3">
           <button id="confirm-btn-action" class="btn btn-primary w-full justify-center !py-3 !bg-error-bg !text-error border !border-error-bg !shadow-none opacity-50 cursor-not-allowed" disabled>Permanently Delete Branch</button>
           <button data-action="$(&#039;#confirmModal&#039;).addClass(&#039;hidden&#039;).removeClass(&#039;flex&#039;)" class="btn btn-secondary w-full justify-center !py-3">Cancel Action</button>
        </div>
     </div>
  </div>
  <script defer src="/Finzo/public/assets/js/core/utils.js"></script>
  <script defer src="/Finzo/public/assets/js/core/api.js"></script>
  <script defer src="/Finzo/public/assets/js/admin/admin-shared.js"></script>
  <script defer src="/Finzo/public/assets/js/admin/branches.js?v=13"></script>
  <script defer src="/Finzo/public/assets/js/core/component-loader.js"></script>
<?php
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/branches.js"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
