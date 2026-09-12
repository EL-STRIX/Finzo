<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin — Staff Management';
// Admin portal guard — server-side authentication & role check
include __DIR__ . '/_guard.php';
if (!defined('FINZO_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}
\App\Shared\Security\SessionManager::start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
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
              <button id="open-add-staff-modal" type="button" class="btn btn-primary px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider inline-flex items-center gap-2 shadow-sm hover:scale-[1.02] active:scale-[0.98] transition-transform">
                  <span class="material-symbols-outlined text-base" aria-hidden="true">person_add</span>
                  <span>Add Staff</span>
              </button>
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
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary/20 to-primary/5 text-primary flex items-center justify-center border border-primary/20 shadow-[inset_0_1px_1px_rgba(255,255,255,0.4)] dark:shadow-[inset_0_1px_1px_rgba(255,255,255,0.1)]">
              <span class="material-symbols-outlined text-2xl" aria-hidden="true">receipt</span>
            </div>
            <div class="text-[10px] font-black text-text-secondary uppercase tracking-[0.15em] mb-2 pt-4 ">TRANSACTIONS TODAY</div>
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

          <!-- Filter 2: Role Dropdown -->
          <div class="w-full relative z-20">
            <label for="filter-role" class="fin-label">Role / Designation</label>
            <div class="finzo-dropdown w-full" id="role-filter-dropdown">
              <input type="hidden" id="filter-role" value="all">
              <div class="finzo-dropdown-trigger fin-input !flex items-center justify-between w-full cursor-pointer">
                <div class="flex items-center gap-2 truncate">
                    <span class="material-symbols-outlined text-lg text-text-secondary" aria-hidden="true">work</span>
                    <span class="dropdown-label font-bold text-sm text-text-primary">All Roles</span>
                </div>
                <span class="material-symbols-outlined chevron transition-transform duration-300 text-text-secondary" aria-hidden="true">expand_more</span>
              </div>
              <div id="filter-role-menu" class="finzo-dropdown-menu !z-[3000] !w-full">
                <div class="finzo-dropdown-option is-selected" data-value="all">All Roles</div>
                <div class="finzo-dropdown-option" data-value="Manager">Manager</div>
                <div class="finzo-dropdown-option" data-value="Supervisor">Supervisor</div>
                <div class="finzo-dropdown-option" data-value="Teller">Teller</div>
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

        <div class="ds-table-wrapper border-0 rounded-none shadow-none bg-transparent !overflow-x-hidden w-full">
          <table class="ds-table w-full text-left table-auto">
            <thead scope="col">
              <tr>
                <th class="w-8 text-center !px-2" scope="col">#</th>
                <th class="!px-3" scope="col">Staff Member</th>
                <th class="text-center !px-2" scope="col">Staff ID</th>
                <th class="!px-3" scope="col">Branch</th>
                <th class="text-center !px-2" scope="col">Status</th>
                <th class="text-center !px-2" scope="col">Trans</th>
                <th class="text-right !px-3" scope="col">Volume (₹)</th>
                <th class="text-center !px-2" scope="col">Joined</th>
                <th class="text-right !px-3" scope="col">Action</th>
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
            <button id="prev-page" class="w-9 h-9 flex items-center justify-center rounded-xl bg-surface border border-border-subtle text-text-primary transition-all hover:bg-surface-hover hover:border-border-strong hover:text-text-primary disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer shadow-sm" aria-label="Previous Page">
              <span class="material-symbols-outlined text-lg pointer-events-none" aria-hidden="true">chevron_left</span>
            </button>
            <div id="page-numbers" class="flex items-center gap-1.5">
              <!-- Dynamically Generated -->
            </div>
            <button id="next-page" class="w-9 h-9 flex items-center justify-center rounded-xl bg-surface border border-border-subtle text-text-primary transition-all hover:bg-surface-hover hover:border-border-strong hover:text-text-primary disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer shadow-sm" aria-label="Next Page">
              <span class="material-symbols-outlined text-lg pointer-events-none" aria-hidden="true">chevron_right</span>
            </button>
          </div>
        </div>
      </div>

      <!-- ADD NEW STAFF MODAL -->
      <div id="add-staff-modal" class="fixed inset-0 z-[2000] hidden items-center justify-center p-4 bg-black/75 backdrop-blur-md overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="add-staff-modal-title">
          <div class="modal-content bg-surface border border-border-subtle rounded-2xl shadow-2xl w-full max-w-3xl my-8 overflow-visible animate-in fade-in zoom-in duration-200" role="document">
              <!-- Modal Header -->
              <div class="p-6 border-b border-border-subtle flex justify-between items-center bg-background/60 backdrop-blur-md rounded-t-2xl">
                  <div class="flex items-center gap-3">
                      <div class="w-11 h-11 rounded-xl bg-primary/10 text-primary flex items-center justify-center border border-primary/20 shadow-sm">
                          <span class="material-symbols-outlined text-2xl" aria-hidden="true">person_add</span>
                      </div>
                      <div>
                          <h2 id="add-staff-modal-title" class="text-lg font-black font-headline text-text-primary">Add New Staff Member</h2>
                          <p class="text-xs font-bold text-text-secondary uppercase tracking-wider">Create employee profile, credentials &amp; branch assignment</p>
                      </div>
                  </div>
                  <button type="button" class="close-add-staff-modal w-9 h-9 flex items-center justify-center rounded-xl hover:bg-surface-hover text-text-secondary transition-colors" aria-label="Close modal">
                      <span class="material-symbols-outlined text-xl" aria-hidden="true">close</span>
                  </button>
              </div>

              <!-- Modal Form -->
              <form id="add-staff-form" class="p-6 sm:p-8 space-y-6 max-h-[75vh] overflow-y-auto" enctype="multipart/form-data">
                  <input type="hidden" name="csrf_token" id="add-staff-csrf-token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                  <div id="add-staff-alert" class="hidden p-4 rounded-xl border text-xs font-bold leading-relaxed"></div>

                  <!-- SECTION 1: Personal Profile -->
                  <div>
                      <div class="flex items-center gap-2 mb-4 pb-2 border-b border-border-subtle">
                          <span class="material-symbols-outlined text-primary text-lg" aria-hidden="true">badge</span>
                          <h3 class="text-xs font-black uppercase tracking-widest text-text-primary">Personal Details</h3>
                      </div>
                      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                          <div class="sm:col-span-2">
                              <label for="new-staff-name" class="fin-label">Full Legal Name <span class="text-error">*</span></label>
                              <input type="text" id="new-staff-name" name="full_name" required placeholder="e.g. Aditi Sharma" class="fin-input">
                          </div>
                          <div>
                              <label for="new-staff-dob" class="fin-label">Date of Birth <span class="text-error">*</span></label>
                              <input type="date" id="new-staff-dob" name="date_of_birth" required class="fin-input">
                          </div>
                          <div class="relative z-50">
                              <label for="new-staff-gender" class="fin-label">Gender <span class="text-error">*</span></label>
                              <div class="finzo-dropdown w-full" id="staff-gender-dropdown">
                                  <input type="hidden" id="new-staff-gender" name="gender" value="Female">
                                  <div class="finzo-dropdown-trigger fin-input !flex items-center justify-between cursor-pointer">
                                      <span class="dropdown-label font-bold text-sm text-text-primary">Female</span>
                                      <span class="material-symbols-outlined chevron transition-transform duration-300 text-text-secondary" aria-hidden="true">expand_more</span>
                                  </div>
                                  <div class="finzo-dropdown-menu !z-[3500] !w-full">
                                      <div class="finzo-dropdown-option" data-value="Male">Male</div>
                                      <div class="finzo-dropdown-option is-selected" data-value="Female">Female</div>
                                      <div class="finzo-dropdown-option" data-value="Other">Other</div>
                                      <div class="finzo-dropdown-option" data-value="Prefer not to say">Prefer not to say</div>
                                  </div>
                              </div>
                          </div>
                          <div>
                              <label for="new-staff-email" class="fin-label">Personal / Work Email <span class="text-error">*</span></label>
                              <input type="email" id="new-staff-email" name="personal_email" required placeholder="staff@finzobank.com" class="fin-input">
                          </div>
                          <div>
                              <label for="new-staff-phone" class="fin-label">Mobile Number (10 Digits) <span class="text-error">*</span></label>
                              <input type="tel" id="new-staff-phone" name="phone" required placeholder="9876543210" maxlength="10" class="fin-input">
                          </div>
                          <div>
                              <label for="new-staff-em-name" class="fin-label">Emergency Contact Name</label>
                              <input type="text" id="new-staff-em-name" name="emergency_contact_name" placeholder="Contact person name" class="fin-input">
                          </div>
                          <div>
                              <label for="new-staff-em-phone" class="fin-label">Emergency Contact Phone</label>
                              <input type="tel" id="new-staff-em-phone" name="emergency_contact_phone" placeholder="Emergency phone" maxlength="10" class="fin-input">
                          </div>
                      </div>
                  </div>

                  <!-- SECTION 2: Role & Branch Assignment -->
                  <div>
                      <div class="flex items-center gap-2 mb-4 pb-2 border-b border-border-subtle">
                          <span class="material-symbols-outlined text-primary text-lg" aria-hidden="true">corporate_fare</span>
                          <h3 class="text-xs font-black uppercase tracking-widest text-text-primary">Branch &amp; Role Assignment</h3>
                      </div>
                      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                          <div class="relative z-40">
                              <label for="new-staff-designation" class="fin-label">Role / Designation <span class="text-error">*</span></label>
                              <div class="finzo-dropdown w-full" id="staff-designation-dropdown">
                                  <input type="hidden" id="new-staff-designation" name="designation" value="Teller">
                                  <div class="finzo-dropdown-trigger fin-input !flex items-center justify-between cursor-pointer">
                                      <span class="dropdown-label font-bold text-sm text-text-primary">Teller / Operations</span>
                                      <span class="material-symbols-outlined chevron transition-transform duration-300 text-text-secondary" aria-hidden="true">expand_more</span>
                                  </div>
                                  <div class="finzo-dropdown-menu !z-[3500] !w-full">
                                      <div class="finzo-dropdown-option is-selected" data-value="Teller">Teller / Operations</div>
                                      <div class="finzo-dropdown-option" data-value="Supervisor">Supervisor</div>
                                      <div class="finzo-dropdown-option" data-value="Manager">Branch Manager</div>
                                  </div>
                              </div>
                          </div>
                          <div class="relative z-40">
                              <label for="new-staff-branch" class="fin-label">Assigned Branch</label>
                              <div class="finzo-dropdown w-full" id="staff-branch-dropdown">
                                  <input type="hidden" id="new-staff-branch" name="branch_id" value="">
                                  <div class="finzo-dropdown-trigger fin-input !flex items-center justify-between cursor-pointer">
                                      <span class="dropdown-label font-bold text-sm text-text-primary">Head Office / Unassigned</span>
                                      <span class="material-symbols-outlined chevron transition-transform duration-300 text-text-secondary" aria-hidden="true">expand_more</span>
                                  </div>
                                  <div id="new-staff-branch-menu" class="finzo-dropdown-menu !z-[3500] !w-full">
                                      <div class="finzo-dropdown-option is-selected" data-value="">Head Office / Unassigned</div>
                                      <!-- Dynamically populated -->
                                  </div>
                              </div>
                          </div>
                          <div>
                              <label for="new-staff-joining" class="fin-label">Date of Joining</label>
                              <input type="date" id="new-staff-joining" name="date_of_joining" value="<?= date('Y-m-d') ?>" class="fin-input">
                          </div>
                      </div>
                  </div>

                  <!-- SECTION 3: Residential Address -->
                  <div>
                      <div class="flex items-center gap-2 mb-4 pb-2 border-b border-border-subtle">
                          <span class="material-symbols-outlined text-primary text-lg" aria-hidden="true">home_pin</span>
                          <h3 class="text-xs font-black uppercase tracking-widest text-text-primary">Residential Address</h3>
                      </div>
                      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                          <div class="sm:col-span-2 lg:col-span-4">
                              <label for="new-staff-street" class="fin-label">Street Address</label>
                              <input type="text" id="new-staff-street" name="street" placeholder="Flat / House No, Street name" class="fin-input">
                          </div>
                          <div>
                              <label for="new-staff-city" class="fin-label">City <span class="text-error">*</span></label>
                              <input type="text" id="new-staff-city" name="city" required placeholder="City" class="fin-input">
                          </div>
                          <div>
                              <label for="new-staff-state" class="fin-label">State <span class="text-error">*</span></label>
                              <input type="text" id="new-staff-state" name="state" required placeholder="State" class="fin-input">
                          </div>
                          <div class="sm:col-span-2 lg:col-span-2">
                              <label for="new-staff-pincode" class="fin-label">Postal Pincode <span class="text-error">*</span></label>
                              <input type="text" id="new-staff-pincode" name="pincode" required placeholder="6-digit Pincode" maxlength="6" class="fin-input">
                          </div>
                      </div>
                  </div>

                  <!-- SECTION 4: KYC & Identification -->
                  <div>
                      <div class="flex items-center gap-2 mb-4 pb-2 border-b border-border-subtle">
                          <span class="material-symbols-outlined text-primary text-lg" aria-hidden="true">verified</span>
                          <h3 class="text-xs font-black uppercase tracking-widest text-text-primary">KYC Documents &amp; ID Proof</h3>
                      </div>
                      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                          <div class="relative z-30">
                              <label for="new-staff-id-type" class="fin-label">ID Document Type <span class="text-error">*</span></label>
                              <div class="finzo-dropdown w-full" id="staff-id-type-dropdown">
                                  <input type="hidden" id="new-staff-id-type" name="id_type" value="Aadhaar">
                                  <div class="finzo-dropdown-trigger fin-input !flex items-center justify-between cursor-pointer">
                                      <span class="dropdown-label font-bold text-sm text-text-primary">Aadhaar Card</span>
                                      <span class="material-symbols-outlined chevron transition-transform duration-300 text-text-secondary" aria-hidden="true">expand_more</span>
                                  </div>
                                  <div class="finzo-dropdown-menu !z-[3500] !w-full">
                                      <div class="finzo-dropdown-option is-selected" data-value="Aadhaar">Aadhaar Card</div>
                                      <div class="finzo-dropdown-option" data-value="PAN">PAN Card</div>
                                      <div class="finzo-dropdown-option" data-value="Passport">Passport</div>
                                  </div>
                              </div>
                          </div>
                          <div>
                              <label for="new-staff-id-num" class="fin-label">ID Document Number <span class="text-error">*</span></label>
                              <input type="text" id="new-staff-id-num" name="id_number" required placeholder="Enter ID number" class="fin-input">
                          </div>
                          <div>
                              <label for="new-staff-id-front" class="fin-label">Front ID Document</label>
                              <input type="file" id="new-staff-id-front" name="id_front" accept="image/*,application/pdf" class="fin-input file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                          </div>
                          <div>
                              <label for="new-staff-id-back" class="fin-label">Back ID Document</label>
                              <input type="file" id="new-staff-id-back" name="id_back" accept="image/*,application/pdf" class="fin-input file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                          </div>
                          <div class="sm:col-span-2">
                              <label for="new-staff-photo" class="fin-label">Staff Profile Photo</label>
                              <input type="file" id="new-staff-photo" name="profile_photo" accept="image/*" class="fin-input file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                          </div>
                      </div>
                  </div>

                  <!-- SECTION 5: Initial Password & Access -->
                  <div>
                      <div class="flex items-center gap-2 mb-4 pb-2 border-b border-border-subtle">
                          <span class="material-symbols-outlined text-primary text-lg" aria-hidden="true">lock</span>
                          <h3 class="text-xs font-black uppercase tracking-widest text-text-primary">Account Security &amp; Password</h3>
                      </div>
                      <div>
                          <label for="new-staff-password" class="fin-label">Initial Password <span class="text-error">*</span></label>
                          <div class="flex gap-2">
                              <input type="text" id="new-staff-password" name="password" required minlength="8" placeholder="Enter or generate a strong password" class="fin-input font-mono flex-1">
                              <button type="button" id="generate-staff-pwd-btn" class="btn btn-secondary px-4 py-2 text-xs font-bold uppercase tracking-wider inline-flex items-center gap-1 shrink-0">
                                  <span class="material-symbols-outlined text-sm" aria-hidden="true">autorenew</span>
                                  <span>Generate</span>
                              </button>
                          </div>
                          <p class="text-[11px] text-text-secondary mt-1">Minimum 8 characters. Provide this initial credential to the staff member upon onboarding.</p>
                      </div>
                  </div>

                  <!-- Modal Footer Buttons -->
                  <div class="pt-6 border-t border-border-subtle flex items-center justify-end gap-3 sticky bottom-0 bg-surface">
                      <button type="button" class="close-add-staff-modal btn btn-secondary px-5 py-2.5 text-xs font-bold uppercase tracking-wider">Cancel</button>
                      <button type="submit" id="submit-add-staff-btn" class="btn btn-primary px-6 py-2.5 text-xs font-bold uppercase tracking-wider inline-flex items-center gap-2 shadow-sm">
                          <span class="material-symbols-outlined text-base" aria-hidden="true">person_add</span>
                          <span id="submit-btn-text">Create Staff Member</span>
                      </button>
                  </div>
              </form>
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
                          <p class="text-[10px] font-bold text-text-secondary uppercase tracking-wider">Configure export filters &amp; full columns</p>
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
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/staff.js?v=' . time() . '"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
