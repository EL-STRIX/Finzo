<?php
$portal = 'admin';
$pageTitle = 'Staff Dossier';
$pageHeader = '
<div class="flex items-start sm:items-center justify-between w-full">
  <div class="flex items-center gap-4">
    <a href="admin-staff.php" class="w-10 h-10 flex items-center justify-center rounded-xl bg-surface border border-border-subtle hover:bg-background transition-all text-text-secondary hover:text-text-primary">
      <span class="material-symbols-outlined" aria-hidden="true">arrow_back</span>
    </a>
    <div>
      <div class="text-[10px] font-black tracking-widest uppercase text-text-secondary">STAFF DETAIL</div>
      <div class="text-xl sm:text-2xl font-black tracking-tight mt-0.5 text-text-primary force-black" id="header-staff-name">Loading...</div>
    </div>
  </div>
  <div class="flex items-center gap-2 sm:gap-4">
    <div id="header-status-badge" class="hidden sm:flex items-center gap-2 px-4 py-1.5 bg-success-bg border border-success-bg rounded-full">
      <div class="status-dot w-2 h-2 bg-success rounded-full animate-pulse"></div>
      <span class="status-label text-[11px] font-black text-success uppercase tracking-widest">Loading</span>
    </div>
  </div>
</div>
';
ob_start();
?>
  <div class="admin-main">
    <!-- ALERT BANNER FOR SUSPENSION OR LOCK -->
    <div id="security-alert-banner" class="hidden rounded-2xl p-4 sm:p-5 flex items-start gap-4 mt-6">
        <span id="security-alert-icon" class="material-symbols-outlined text-3xl shrink-0 mt-1" aria-hidden="true">warning</span>
        <div>
            <h4 id="security-alert-title" class="text-sm sm:text-base font-black tracking-tight mb-1 uppercase text-error">Alert</h4>
            <p id="security-alert-message" class="text-xs sm:text-sm font-bold leading-relaxed text-error"></p>
        </div>
    </div>

    <div class="flex flex-col lg:grid lg:grid-cols-12 gap-6 lg:gap-8 mt-6">
        
        <!-- LEFT COLUMN (8 cols) -->
        <div class="lg:col-span-8 space-y-6">
            
            <!-- PROFILE CARD -->
            <div class="ds-card p-6 sm:p-8">
                <div class="flex flex-col sm:flex-row items-center gap-6 mb-8 pb-8 border-b border-border-subtle text-center sm:text-left">
                    <div class="relative">
                        <div id="staff-photo-container" class="w-24 h-24 sm:w-28 sm:h-28 rounded-3xl overflow-hidden shadow-sm bg-primary-50 flex items-center justify-center border border-border-subtle">
                            <span id="photo-initials" class="text-4xl font-black text-primary">?</span>
                        </div>
                    </div>
                    <div class="flex-1">
                        <h3 id="info-name" class="text-2xl sm:text-3xl font-black tracking-tight text-text-primary mb-3">Loading...</h3>
                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mb-4">
                            <span id="info-staff-id-badge" class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest bg-background border border-border-subtle text-text-secondary font-mono">---</span>
                            <span id="info-designation-badge" class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest bg-primary text-white">---</span>
                            <span id="info-department-badge" class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest bg-success-bg text-success border border-success-bg">---</span>
                        </div>
                        <p class="text-sm font-bold text-text-secondary flex items-center justify-center sm:justify-start gap-2">
                            <span class="material-symbols-outlined text-lg" aria-hidden="true">calendar_today</span>
                            Member since <span id="info-joined-date" class="font-black text-text-primary">---</span>
                        </p>
                    </div>
                </div>

                <!-- Personal Info Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-6">
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Email Address</label>
                        <div id="info-email" class="text-sm font-bold text-text-primary">---</div>
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Gender</label>
                        <div id="info-gender" class="text-sm font-bold text-text-primary">---</div>
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Phone Number</label>
                        <div id="info-phone" class="text-sm font-bold text-text-primary">---</div>
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Date of Birth</label>
                        <div id="info-dob" class="text-sm font-bold text-text-primary">---</div>
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Emergency Contact</label>
                        <div id="info-emergency" class="text-sm font-bold text-text-primary">---</div>
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Branch Assignment</label>
                        <div id="info-branch" class="text-sm font-bold text-text-primary">---</div>
                    </div>
                </div>
            </div>

            <!-- ADDRESS -->
            <div class="ds-card">
                <div class="p-5 border-b border-border-subtle bg-background">
                    <h4 class="font-black text-[10px] uppercase tracking-widest flex items-center gap-2 text-text-secondary">
                        <span class="material-symbols-outlined text-text-secondary text-lg" aria-hidden="true">location_on</span> Residential Address
                    </h4>
                </div>
                <div class="p-5 grid grid-cols-2 sm:grid-cols-5 gap-5">
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Street</label>
                        <div id="addr-street" class="text-sm font-bold text-text-primary">---</div>
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">City</label>
                        <div id="addr-city" class="text-sm font-bold text-text-primary">---</div>
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">State</label>
                        <div id="addr-state" class="text-sm font-bold text-text-primary">---</div>
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Pincode</label>
                        <div id="addr-pincode" class="text-sm font-bold text-text-primary">---</div>
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Country</label>
                        <div id="addr-country" class="text-sm font-bold text-text-primary">---</div>
                    </div>
                </div>
            </div>

            <!-- SYSTEM AUDIT INFO -->
            <div class="ds-card">
                <div class="p-5 border-b border-border-subtle bg-background">
                    <h4 class="font-black text-[10px] uppercase tracking-widest flex items-center gap-2 text-text-secondary">
                        <span class="material-symbols-outlined text-text-secondary text-lg" aria-hidden="true">security</span> System Audit Info
                    </h4>
                </div>
                <div class="p-5 grid grid-cols-2 sm:grid-cols-4 gap-5">
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Last Login</label>
                        <div id="sys-last-login" class="text-xs font-bold text-text-primary">---</div>
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Login IP</label>
                        <div id="sys-last-ip" class="text-xs font-mono font-bold text-text-secondary">---</div>
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Date of Joining</label>
                        <div id="sys-joining-date" class="text-xs font-bold text-text-primary">---</div>
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Created By</label>
                        <div id="sys-created-by" class="text-xs font-bold text-text-primary">---</div>
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Staff ID</label>
                        <div id="sys-staff-id" class="text-xs font-mono font-bold text-text-secondary">---</div>
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Total Shifts</label>
                        <div id="sys-total-shifts" class="text-xs font-bold text-text-primary">---</div>
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Failed Logins</label>
                        <div id="sys-failed-logins" class="text-xs font-bold text-text-primary">---</div>
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Lock Status</label>
                        <div id="sys-lock-status" class="text-xs font-bold text-text-primary">---</div>
                    </div>
                </div>
            </div>

            <!-- IDENTITY DOCUMENTS -->
            <div class="ds-card">
                <div class="p-5 border-b border-border-subtle bg-background">
                    <h4 class="font-black text-[10px] uppercase tracking-widest flex items-center gap-2 text-text-secondary">
                        <span class="material-symbols-outlined text-text-secondary text-lg" aria-hidden="true">badge</span> Identity Documents
                    </h4>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-2 gap-5 mb-5">
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">ID Type</label>
                            <div id="doc-id-type" class="text-sm font-bold text-text-primary">---</div>
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">ID Number</label>
                            <div id="doc-id-number" class="text-sm font-bold font-mono text-text-primary">---</div>
                        </div>
                    </div>
                    <div id="doc-images-container" class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <!-- Front -->
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-2 block">Front of Document</label>
                            <div id="doc-front-wrapper" class="relative group rounded-2xl overflow-hidden bg-background border border-border-subtle flex items-center justify-center aspect-[16/10] cursor-pointer transition-all hover:border-primary hover:shadow-sm">
                                <div id="doc-front-placeholder" class="text-center">
                                    <span class="material-symbols-outlined text-4xl text-text-secondary" aria-hidden="true">image</span>
                                    <p class="text-[10px] font-black uppercase tracking-widest text-text-secondary mt-1">No document uploaded</p>
                                </div>
                                <img loading="lazy" id="doc-front-img" class="hidden absolute inset-0 w-full h-full object-cover transition-transform group-hover:scale-105" alt="ID Front" />
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-all flex items-center justify-center opacity-0 group-hover:opacity-100">
                                    <span class="material-symbols-outlined text-white text-3xl drop-shadow-lg" aria-hidden="true">zoom_in</span>
                                </div>
                            </div>
                        </div>
                        <!-- Back -->
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-2 block">Back of Document</label>
                            <div id="doc-back-wrapper" class="relative group rounded-2xl overflow-hidden bg-background border border-border-subtle flex items-center justify-center aspect-[16/10] cursor-pointer transition-all hover:border-primary hover:shadow-sm">
                                <div id="doc-back-placeholder" class="text-center">
                                    <span class="material-symbols-outlined text-4xl text-text-secondary" aria-hidden="true">image</span>
                                    <p class="text-[10px] font-black uppercase tracking-widest text-text-secondary mt-1">No document uploaded</p>
                                </div>
                                <img loading="lazy" id="doc-back-img" class="hidden absolute inset-0 w-full h-full object-cover transition-transform group-hover:scale-105" alt="ID Back" />
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-all flex items-center justify-center opacity-0 group-hover:opacity-100">
                                    <span class="material-symbols-outlined text-white text-3xl drop-shadow-lg" aria-hidden="true">zoom_in</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- RIGHT COLUMN (4 cols) -->
        <div class="lg:col-span-4 space-y-6">
            
            <!-- STAFF CONTROL -->
            <div class="ds-card p-5">
                <h5 class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-5 ml-1">Staff Control</h5>
                <div class="space-y-3">
                    <button id="deactivate-staff-btn" data-action="openGovernanceModal('deactivate')" class="w-full flex items-center justify-between p-3.5 bg-background rounded-xl border border-border-subtle hover:border-warning hover:bg-warning-bg transition-all group">
                        <div class="flex items-center gap-3">
                            <span id="suspend-icon" class="material-symbols-outlined text-warning" aria-hidden="true">block</span>
                            <div class="text-left">
                                <div id="suspend-label" class="text-xs font-black text-text-primary">Deactivate Staff</div>
                                <div class="text-[10px] font-bold text-text-secondary uppercase tracking-widest mt-0.5">Temporarily revoke access</div>
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-text-secondary text-lg" aria-hidden="true">chevron_right</span>
                    </button>

                    <button id="reset-password-btn" data-action="openGovernanceModal('reset')" class="w-full flex items-center justify-between p-3.5 bg-background rounded-xl border border-border-subtle hover:border-info hover:bg-info-bg transition-all group">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-info" aria-hidden="true">lock_reset</span>
                            <div class="text-left">
                                <div class="text-xs font-black text-text-primary">Reset Password</div>
                                <div class="text-[10px] font-bold text-text-secondary uppercase tracking-widest mt-0.5">Force new credentials</div>
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-text-secondary text-lg" aria-hidden="true">chevron_right</span>
                    </button>

                    <button id="delete-staff-btn" data-action="openGovernanceModal('delete')" class="w-full flex items-center justify-between p-3.5 bg-background rounded-xl border border-border-subtle hover:border-error hover:bg-error-bg transition-all group">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-error" aria-hidden="true">person_remove</span>
                            <div class="text-left">
                                <div class="text-xs font-black text-error">Delete Account</div>
                                <div class="text-[10px] font-bold text-text-secondary uppercase tracking-widest mt-0.5">Permanent removal</div>
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-text-secondary text-lg" aria-hidden="true">chevron_right</span>
                    </button>
                </div>
            </div>

            <!-- PERFORMANCE STATS -->
            <div class="ds-card p-5">
                <h5 class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-5 ml-1">Performance Overview</h5>
                <div class="grid grid-cols-2 gap-3">
                    <div class="text-center p-3 bg-background rounded-xl border border-border-subtle">
                        <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1">Deposits</div>
                        <div id="stat-total-deposits" class="text-sm font-black text-success">₹0</div>
                    </div>
                    <div class="text-center p-3 bg-background rounded-xl border border-border-subtle">
                        <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1">Withdrawals</div>
                        <div id="stat-total-withdrawals" class="text-sm font-black text-error">₹0</div>
                    </div>
                    <div class="text-center p-3 bg-background rounded-xl border border-border-subtle">
                        <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1">Transactions</div>
                        <div id="stat-total-txns" class="text-sm font-black text-text-primary">0</div>
                    </div>
                    <div class="text-center p-3 bg-background rounded-xl border border-border-subtle">
                        <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1">Last Active</div>
                        <div id="stat-last-active" class="text-xs font-black text-text-primary mt-0.5">Never</div>
                    </div>
                </div>
            </div>

            <!-- TODAY'S SHIFT -->
            <div class="ds-card p-5">
                <h5 class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-5 ml-1">Today\'s Shift Summary</h5>
                <div class="space-y-3">
                    <div class="flex justify-between items-center pb-2.5 border-b border-border-subtle">
                        <span class="text-[10px] font-black text-text-secondary uppercase tracking-widest">Deposits Done</span>
                        <span id="summary-deposits" class="text-sm font-bold text-text-primary">0</span>
                    </div>
                    <div class="flex justify-between items-center pb-2.5 border-b border-border-subtle">
                        <span class="text-[10px] font-black text-text-secondary uppercase tracking-widest">Withdrawals Done</span>
                        <span id="summary-withdrawals" class="text-sm font-bold text-text-primary">0</span>
                    </div>
                    <div class="flex justify-between items-center pb-2.5 border-b border-border-subtle">
                        <span class="text-[10px] font-black text-text-secondary uppercase tracking-widest">Total Transactions</span>
                        <span id="summary-total-txns" class="text-sm font-bold text-text-primary">0</span>
                    </div>
                    <div class="flex justify-between items-center pt-1">
                        <span class="text-[10px] font-black text-primary uppercase tracking-widest">Net Amount</span>
                        <span id="summary-total-amount" class="text-lg font-black text-primary">₹0.00</span>
                    </div>
                </div>
            </div>

            <!-- SHIFT HISTORY -->
            <div class="ds-card p-5">
                <h5 class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-5 flex justify-between items-center ml-1">
                    <span>Recent Shifts</span>
                    <span class="material-symbols-outlined text-sm" aria-hidden="true">schedule</span>
                </h5>
                <div id="staff-shift-history" class="space-y-3">
                    <div class="text-[10px] font-black text-text-secondary text-center py-4 uppercase tracking-widest">Loading shift history...</div>
                </div>
            </div>

            <!-- SECURITY NOTE -->
            <div class="p-5 bg-background border border-border-subtle rounded-2xl overflow-hidden relative">
                <div class="relative z-10">
                    <h5 class="text-[10px] font-black text-primary uppercase tracking-widest mb-3">Security Note</h5>
                    <p class="text-[11px] leading-relaxed text-text-secondary font-bold italic">
                        "All staff-related actions are recorded in the global audit log. Every modification is attributed to the performing administrator\'s session."
                    </p>
                </div>
                <span class="material-symbols-outlined absolute -right-4 -bottom-4 text-8xl text-border-subtle" aria-hidden="true">gavel</span>
            </div>
        </div>
    </div>

    <!-- TRANSACTION HISTORY -->
    <div class="ds-card overflow-hidden mt-6">
        <div class="p-5 sm:p-8 border-b border-border-subtle flex justify-between items-center bg-background">
            <div>
                <h4 class="font-black text-lg text-text-primary tracking-tight">Transaction History</h4>
                <p class="text-[10px] font-black text-text-secondary uppercase tracking-widest mt-1">Full Ledger Audit for Current Portfolio</p>
            </div>
            <button data-action="window.open(\'../../../api/pdf/staff-statement.php?staff_id=\' + new URLSearchParams(window.location.search).get(\'id\'), \'_blank\')" class="btn btn-secondary btn-sm flex items-center gap-2">
                <span class="material-symbols-outlined text-lg" aria-hidden="true">print</span>
                Print Statement
            </button>
        </div>
        <div class="ds-table-wrapper border-0 rounded-none shadow-none bg-transparent">
            <table class="ds-table w-full text-left">
                <thead scope="col">
                    <tr>
                        <th scope="col">Receipt No</th>
                        <th scope="col">Date & Time</th>
                        <th scope="col">Customer</th>
                        <th scope="col">Account</th>
                        <th scope="col">Type</th>
                        <th scope="col">Amount</th>
                        <th class="text-right" scope="col">Action</th>
                    </tr>
                </thead>
                <tbody id="staff-transactions-body">
                    <tr><td colspan="7" class="text-center py-12 text-text-secondary text-xs font-bold">Loading transactions...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- RECENT ACTIVITY -->
    <div class="ds-card overflow-hidden mt-6">
        <div class="p-5 sm:p-8 border-b border-border-subtle flex justify-between items-center bg-background">
            <div>
                <h4 class="font-black text-lg text-text-primary tracking-tight">Recent Activity</h4>
                <p class="text-[10px] font-black text-text-secondary uppercase tracking-widest mt-1">System activity logs</p>
            </div>
        </div>
        <div class="ds-table-wrapper border-0 rounded-none shadow-none bg-transparent">
            <table class="ds-table w-full text-left">
                <thead scope="col">
                    <tr>
                        <th scope="col">Date & Time</th>
                        <th scope="col">Action Type</th>
                        <th scope="col">Details</th>
                        <th class="text-right" scope="col">IP Address</th>
                    </tr>
                </thead>
                <tbody id="staff-activities-body">
                    <tr><td colspan="4" class="text-center py-12 text-text-secondary text-xs font-bold">Loading activity...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- GOVERNANCE SECURITY MODAL -->
<div id="governance-modal" class="fixed inset-0 z-[2000] hidden" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-surface-dark/80 backdrop-blur-sm"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-lg">
        <div class="ds-card !p-0 overflow-hidden shadow-2xl border-none">
            <div id="modal-header" class="p-8 pb-4 text-center">
                <div id="modal-icon-container" class="w-16 h-16 rounded-full mx-auto flex items-center justify-center mb-4 transition-all duration-300">
                    <span id="modal-icon" class="material-symbols-outlined text-4xl" aria-hidden="true">warning</span>
                </div>
                <h3 id="modal-title" class="font-headline text-2xl font-black text-text-primary">Authorize Action</h3>
                <p id="modal-subtitle" class="text-[10px] text-text-secondary font-black mt-1 uppercase tracking-widest">Secure administrative override required</p>
            </div>
            
            <div id="modal-body-text" class="px-8 text-sm font-bold text-text-secondary text-center mb-4"></div>

            <div class="px-8 py-4 space-y-6">
                <!-- Selection Grid -->
                <div id="reason-step" class="hidden">
                    <label class="block text-[10px] font-black uppercase text-text-secondary mb-3 ml-1">Select Primary Reason</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="reason-grid">
                        <!-- Reasons will be injected by JS -->
                    </div>
                </div>

                <!-- Custom Reason Area -->
                <div id="custom-reason-container" class="hidden">
                    <label for="governance-notes" class="block text-[10px] font-black uppercase text-text-secondary mb-3 ml-1">Additional Observations (Optional)</label>
                    <textarea id="governance-notes" rows="3" placeholder="Explain the decision for internal auditing..."
                    class="fin-input !rounded-2xl"></textarea>
                </div>

                <!-- Auth Step -->
                <div id="auth-step" class="pt-4 border-t border-border-subtle">
                    <label for="admin-password" class="block text-[10px] font-black uppercase text-text-secondary mb-3 ml-1">Confirm Identity (Admin Password)</label>
                    <div class="relative">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-text-secondary" aria-hidden="true">admin_panel_settings</span>
                    <input type="password" id="admin-password" placeholder="••••••••"
                        class="fin-input !pl-12 !py-3 !rounded-2xl !text-lg !tracking-widest">
                    </div>
                    <p id="auth-error" class="text-[10px] font-black text-error uppercase tracking-widest mt-2 ml-1 hidden">Invalid administrative credentials. Access denied.</p>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="p-8 pt-4 flex gap-3">
                <button data-action="closeGovernanceModal()" class="flex-1 py-4 bg-background hover:bg-surface-hover text-text-secondary font-black rounded-2xl text-[10px] uppercase tracking-widest transition-all border border-border-subtle">Abort Action</button>
                <button id="confirm-governance-btn" data-action="executeGovernanceAction()" class="flex-1 py-4 bg-primary text-white font-black rounded-2xl text-[10px] uppercase tracking-widest shadow-sm hover:scale-[1.02] active:scale-[0.98] transition-all">Confirm Change</button>
            </div>
        </div>
    </div>
</div>

<!-- LIGHTBOX MODAL -->
<div id="lightbox-modal" class="fixed inset-0 z-[3000] hidden items-center justify-center bg-surface-dark/95 backdrop-blur-md transition-all" role="dialog" aria-modal="true">
    <button data-action="closeLightbox()" class="absolute top-6 right-6 w-12 h-12 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white transition-all">
        <span class="material-symbols-outlined text-2xl" aria-hidden="true">close</span>
    </button>
    <img id="lightbox-img" src="" class="max-w-[90vw] max-h-[90vh] object-contain rounded-lg shadow-2xl" alt="Document View">
</div>

<script defer src="/Finzo/public/assets/js/admin/staff-detail.js"></script>
<script defer src="/Finzo/public/assets/js/core/component-loader.js"></script>
<?php
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/staff-detail.js"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>