<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin — Staff Dossier';

// SSR data loading — only when bootstrapped via the router (FINZO_ROOT is defined).
// When accessed directly the page will load data client-side via JavaScript.
$staffData = null;
if (defined('FINZO_ROOT')) {
    $staffId = intval($_GET['id'] ?? 0);
    if ($staffId > 0) {
        try {
            $container = \App\Shared\Core\Container::getInstance();
            $service = $container->get(\App\Domain\Contracts\Services\AdminStaffBranchServiceInterface::class);
            $staffData = $service->getStaffDetailComprehensive($staffId);
        } catch (\Throwable $e) {
            $staffData = null;
        }
    }
}

$staff        = $staffData['staff']            ?? [];
$address      = $staffData['address']          ?? [];
$performance  = $staffData['performance']      ?? [];
$today        = $staffData['today_performance'] ?? [];
$shifts       = $staffData['shifts']           ?? [];
$transactions = $staffData['transactions']     ?? [];
$activities   = $staffData['activities']       ?? [];
$documents    = $staffData['documents']        ?? [];

$fullName    = htmlspecialchars($staff['full_name']   ?? 'Loading Profile...', ENT_QUOTES, 'UTF-8');
$staffCode   = htmlspecialchars($staff['staff_id']    ?? 'STF---',             ENT_QUOTES, 'UTF-8');
$designation = htmlspecialchars($staff['designation'] ?? 'Staff',              ENT_QUOTES, 'UTF-8');
$status      = $staff['status'] ?? 'active';
$isActive    = ($status === 'active');
$initials    = !empty($staff['full_name'])
    ? strtoupper(substr(implode('', array_map(fn($n) => $n[0] ?? '', explode(' ', $staff['full_name']))), 0, 2))
    : 'ST';
$profilePhoto = !empty($staff['profile_photo_url']) ? $staff['profile_photo_url'] : null;

// Age calculation
$dobStr    = !empty($staff['date_of_birth']) ? date('d M Y', strtotime($staff['date_of_birth'])) : '—';
$genderStr = htmlspecialchars($staff['gender'] ?? 'Not specified', ENT_QUOTES, 'UTF-8');
if (!empty($staff['date_of_birth'])) {
    $age = (new \DateTime($staff['date_of_birth']))->diff(new \DateTime('today'))->y;
    if ($age > 0) {
        $genderStr .= " ({$age} yrs)";
    }
}
$joinedDate = !empty($staff['date_of_joining'])
    ? date('d M Y', strtotime($staff['date_of_joining']))
    : (!empty($staff['created_at']) ? date('d M Y', strtotime($staff['created_at'])) : '—');
$branchName = !empty($staff['branch_name'])
    ? htmlspecialchars($staff['branch_name'] . (!empty($staff['branch_code']) ? " ({$staff['branch_code']})" : ''), ENT_QUOTES, 'UTF-8')
    : 'Unassigned';

ob_start();
?>
  <style>
    @media print {
      body * {
        visibility: hidden !important;
      }
      #passbook-print-area, #passbook-print-area * {
        visibility: visible !important;
      }
      #passbook-print-area {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 20px !important;
        background: white !important;
        color: black !important;
        box-shadow: none !important;
        border: none !important;
      }
      .no-print {
        display: none !important;
      }
    }
  </style>

  <div class="admin-main">
    <div class="space-y-6 sm:space-y-8">
      
      <!-- TOP NAVIGATION & HEADER -->
      <div class="flex items-center justify-between gap-4 relative z-10">
        <div class="flex items-center gap-4">
          <a href="/Finzo/admin/staff" class="w-10 h-10 flex items-center justify-center rounded-xl bg-surface border border-border-subtle hover:bg-surface-hover transition-all text-text-secondary hover:text-text-primary shadow-sm" aria-label="Back to Staff List">
            <span class="material-symbols-outlined" aria-hidden="true">arrow_back</span>
          </a>
          <div>
            <div class="text-[10px] font-black tracking-widest uppercase text-text-secondary">STAFF PROFILE &amp; DOSSIER</div>
            <div class="text-xl sm:text-2xl font-black tracking-tight mt-0.5 text-text-primary" id="header-staff-name"><?= $fullName ?></div>
          </div>
        </div>
      </div>

      <!-- ALERT BANNER FOR SUSPENSION OR LOCK -->
      <div id="security-alert-banner" class="<?= !$isActive ? '' : 'hidden' ?> rounded-2xl p-4 sm:p-5 flex items-start gap-4 border shadow-sm <?= !$isActive ? 'border-rose-500/20 bg-rose-500/10 text-rose-700 dark:text-rose-300' : '' ?>">
          <span id="security-alert-icon" class="material-symbols-outlined text-3xl shrink-0 mt-0.5" aria-hidden="true">gavel</span>
          <div>
              <h4 id="security-alert-title" class="text-sm sm:text-base font-black tracking-tight mb-1 uppercase"><?= !$isActive ? 'Account Suspended' : 'Security Notice' ?></h4>
              <p id="security-alert-message" class="text-xs sm:text-sm font-bold leading-relaxed"><?= !$isActive ? 'Staff privileges have been revoked under administrative review.' : '' ?></p>
          </div>
      </div>

      <!-- MAIN CONTENT GRID -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8">
          
          <!-- LEFT COLUMN (8 cols) -->
          <div class="lg:col-span-8 space-y-6">
              
              <!-- PROFILE CARD -->
              <div class="ds-card p-6 sm:p-8">
                  <div class="flex flex-col sm:flex-row items-center gap-6 mb-8 pb-8 border-b border-border-subtle text-center sm:text-left">
                      <div class="relative shrink-0">
                          <div id="staff-photo-container" class="w-24 h-24 sm:w-28 sm:h-28 rounded-3xl overflow-hidden shadow-md bg-surface border border-border-subtle flex items-center justify-center">
                              <?php if ($profilePhoto): ?>
                                  <img src="<?= htmlspecialchars($profilePhoto, ENT_QUOTES, 'UTF-8') ?>" class="w-full h-full object-cover" alt="<?= $fullName ?>" onerror="this.parentElement.innerHTML='<span class=\'text-3xl sm:text-4xl font-black text-primary font-headline\'><?= $initials ?></span>'">
                              <?php else: ?>
                                  <span id="photo-initials" class="text-3xl sm:text-4xl font-black text-primary font-headline"><?= $initials ?></span>
                              <?php endif; ?>
                          </div>
                      </div>
                      <div class="flex-1 min-w-0">
                          <h3 id="info-name" class="text-2xl sm:text-3xl font-black tracking-tight text-text-primary mb-3 truncate"><?= $fullName ?></h3>
                          <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2.5 mb-4">
                              <span id="info-staff-id-badge" class="px-3 py-1 rounded-xl text-xs font-mono font-bold uppercase tracking-wider bg-surface border border-border-subtle text-text-secondary shadow-sm"><?= $staffCode ?></span>
                              <?php
                                  $roleLower = strtolower($designation);
                                  $roleBadgeClass = 'bg-blue-500/15 text-blue-600 dark:text-blue-400 border border-blue-500/30';
                                  if (strpos($roleLower, 'manager') !== false) {
                                      $roleBadgeClass = 'bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/30';
                                  } elseif (strpos($roleLower, 'supervisor') !== false) {
                                      $roleBadgeClass = 'bg-purple-500/15 text-purple-600 dark:text-purple-400 border border-purple-500/30';
                                  }
                              ?>
                              <span id="info-role-badge" class="px-3.5 py-1 rounded-xl text-xs font-black uppercase tracking-wider <?= $roleBadgeClass ?> shadow-sm"><?= $designation ?></span>
                              <span id="info-status-badge" class="px-3 py-1 rounded-xl text-xs font-bold uppercase tracking-wider inline-flex items-center gap-1.5 <?= $isActive ? 'bg-success-bg text-success border border-success/20' : 'bg-error-bg text-error border border-error/20' ?> shadow-sm">
                                  <span class="w-2 h-2 rounded-full <?= $isActive ? 'bg-success animate-pulse' : 'bg-error' ?>"></span>
                                  <span id="info-status-text"><?= $isActive ? 'Active Account' : 'Suspended' ?></span>
                              </span>
                          </div>
                          <p class="text-xs font-bold text-text-secondary flex items-center justify-center sm:justify-start gap-2">
                              <span class="material-symbols-outlined text-base" aria-hidden="true">calendar_today</span>
                              <span>Staff Member Since <strong id="info-joined-date" class="text-text-primary font-black"><?= $joinedDate ?></strong></span>
                          </p>
                      </div>
                  </div>

                  <!-- Personal Info Grid -->
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-6">
                      <div>
                          <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Email Address</label>
                          <div id="info-email" class="text-sm font-bold text-text-primary break-all"><?= htmlspecialchars($staff['email'] ?? '—', ENT_QUOTES, 'UTF-8') ?></div>
                      </div>
                      <div>
                          <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Gender &amp; Age</label>
                          <div id="info-gender" class="text-sm font-bold text-text-primary"><?= $genderStr ?></div>
                      </div>
                      <div>
                          <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Phone Number</label>
                          <div id="info-phone" class="text-sm font-bold text-text-primary font-mono"><?= htmlspecialchars($staff['phone'] ?? '—', ENT_QUOTES, 'UTF-8') ?></div>
                      </div>
                      <div>
                          <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Date of Birth</label>
                          <div id="info-dob" class="text-sm font-bold text-text-primary"><?= $dobStr ?></div>
                      </div>
                      <div>
                          <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Emergency Contact</label>
                          <div id="info-emergency" class="text-sm font-bold text-text-primary">
                              <?php
                                  $emStr = 'Not provided';
                                  if (!empty($staff['emergency_contact_name'])) {
                                      $emStr = htmlspecialchars($staff['emergency_contact_name'] . (!empty($staff['emergency_contact_phone']) ? " ({$staff['emergency_contact_phone']})" : ''), ENT_QUOTES, 'UTF-8');
                                  }
                                  echo $emStr;
                              ?>
                          </div>
                      </div>
                      <div>
                          <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Branch Assignment</label>
                          <div id="info-branch" class="text-sm font-bold text-text-primary"><?= $branchName ?></div>
                      </div>
                  </div>
              </div>

              <!-- RESIDENTIAL ADDRESS -->
              <div class="ds-card">
                  <div class="p-5 border-b border-border-subtle bg-background/50">
                      <h4 class="font-black text-[10px] uppercase tracking-widest flex items-center gap-2 text-text-secondary">
                          <span class="material-symbols-outlined text-text-secondary text-lg" aria-hidden="true">location_on</span>
                          <span>Residential Address</span>
                      </h4>
                  </div>
                  <div class="p-5 grid grid-cols-2 sm:grid-cols-5 gap-5">
                      <div>
                          <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Street</label>
                          <div id="addr-street" class="text-sm font-bold text-text-primary"><?= htmlspecialchars($address['street'] ?? $staff['street'] ?? '—', ENT_QUOTES, 'UTF-8') ?></div>
                      </div>
                      <div>
                          <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">City</label>
                          <div id="addr-city" class="text-sm font-bold text-text-primary"><?= htmlspecialchars($address['city'] ?? $staff['city'] ?? '—', ENT_QUOTES, 'UTF-8') ?></div>
                      </div>
                      <div>
                          <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">State</label>
                          <div id="addr-state" class="text-sm font-bold text-text-primary"><?= htmlspecialchars($address['state'] ?? $staff['state'] ?? '—', ENT_QUOTES, 'UTF-8') ?></div>
                      </div>
                      <div>
                          <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Pincode</label>
                          <div id="addr-pincode" class="text-sm font-bold text-text-primary font-mono"><?= htmlspecialchars($address['pincode'] ?? $staff['pincode'] ?? '—', ENT_QUOTES, 'UTF-8') ?></div>
                      </div>
                      <div>
                          <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Country</label>
                          <div id="addr-country" class="text-sm font-bold text-text-primary"><?= htmlspecialchars($address['country'] ?? $staff['country'] ?? 'India', ENT_QUOTES, 'UTF-8') ?></div>
                      </div>
                  </div>
              </div>

              <!-- SYSTEM AUDIT INFO -->
              <div class="ds-card">
                  <div class="p-5 border-b border-border-subtle bg-background/50">
                      <h4 class="font-black text-[10px] uppercase tracking-widest flex items-center gap-2 text-text-secondary">
                          <span class="material-symbols-outlined text-text-secondary text-lg" aria-hidden="true">security</span>
                          <span>System &amp; Security Audit</span>
                      </h4>
                  </div>
                  <div class="p-5 grid grid-cols-2 sm:grid-cols-4 gap-5">
                      <div>
                          <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Last Login</label>
                          <div id="sys-last-login" class="text-xs font-bold text-text-primary"><?= !empty($staff['last_login_at']) ? date('d M Y, h:i A', strtotime($staff['last_login_at'])) : 'Never' ?></div>
                      </div>
                      <div>
                          <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Login IP</label>
                          <div id="sys-last-ip" class="text-xs font-mono font-bold text-text-secondary"><?= htmlspecialchars($staff['last_login_ip'] ?? '—', ENT_QUOTES, 'UTF-8') ?></div>
                      </div>
                      <div>
                          <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Date of Joining</label>
                          <div id="sys-joining-date" class="text-xs font-bold text-text-primary"><?= $joinedDate ?></div>
                      </div>
                      <div>
                          <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Created By</label>
                          <div id="sys-created-by" class="text-xs font-bold text-text-primary"><?= htmlspecialchars($staff['created_by_name'] ?? 'System', ENT_QUOTES, 'UTF-8') ?></div>
                      </div>
                      <div>
                          <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Staff ID</label>
                          <div id="sys-staff-id" class="text-xs font-mono font-bold text-text-secondary"><?= $staffCode ?></div>
                      </div>
                      <div>
                          <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Total Shifts</label>
                          <div id="sys-total-shifts" class="text-xs font-bold text-text-primary"><?= (int)($staffData['total_shifts'] ?? count($shifts)) ?></div>
                      </div>
                      <div>
                          <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Failed Logins</label>
                          <div id="sys-failed-logins" class="text-xs font-bold text-text-primary"><?= (int)($staff['failed_attempts'] ?? 0) ?></div>
                      </div>
                      <div>
                          <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Lock Status</label>
                          <div id="sys-lock-status" class="text-xs font-bold <?= !empty($staff['locked_until']) ? 'text-error' : 'text-success' ?>"><?= !empty($staff['locked_until']) ? 'Locked' : 'Unlocked' ?></div>
                      </div>
                  </div>
              </div>

              <!-- IDENTITY DOCUMENTS -->
              <div class="ds-card">
                  <div class="p-5 border-b border-border-subtle bg-background/50">
                      <h4 class="font-black text-[10px] uppercase tracking-widest flex items-center gap-2 text-text-secondary">
                          <span class="material-symbols-outlined text-text-secondary text-lg" aria-hidden="true">badge</span>
                          <span>Identity Verification Documents</span>
                      </h4>
                  </div>
                  <div class="p-5">
                      <div class="grid grid-cols-2 gap-5 mb-5">
                          <div>
                              <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Document Type</label>
                              <div id="doc-id-type" class="text-sm font-bold text-text-primary"><?= htmlspecialchars($staff['id_type'] ?? 'Identity Proof', ENT_QUOTES, 'UTF-8') ?></div>
                          </div>
                          <div>
                              <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Document Number</label>
                              <div id="doc-id-number" class="text-sm font-bold font-mono text-text-primary"><?= htmlspecialchars($staff['id_number'] ?? '—', ENT_QUOTES, 'UTF-8') ?></div>
                          </div>
                      </div>
                      <div id="doc-images-container" class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                          <!-- Front Document -->
                          <div>
                              <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-2 block">Front of Document</label>
                              <div id="doc-front-wrapper" class="relative group rounded-2xl overflow-hidden bg-background border border-border-subtle flex items-center justify-center aspect-[16/10] cursor-pointer transition-all hover:border-primary hover:shadow-sm" <?= $frontUrl ? "onclick=\"openLightbox('" . htmlspecialchars($frontUrl, ENT_QUOTES, 'UTF-8') . "')\"" : '' ?>>
                                  <?php $frontUrl = $staff['front_image_url'] ?? null; ?>
                                  <div id="doc-front-placeholder" class="text-center <?= $frontUrl ? 'hidden' : '' ?>">
                                      <span class="material-symbols-outlined text-4xl text-text-secondary" aria-hidden="true">image</span>
                                      <p class="text-[10px] font-black uppercase tracking-widest text-text-secondary mt-1">No document uploaded</p>
                                  </div>
                                  <img loading="lazy" id="doc-front-img" src="<?= htmlspecialchars($frontUrl ?? '', ENT_QUOTES, 'UTF-8') ?>" class="<?= $frontUrl ? '' : 'hidden' ?> absolute inset-0 w-full h-full object-cover transition-transform group-hover:scale-105" alt="ID Front" onerror="this.classList.add('hidden'); document.getElementById('doc-front-placeholder').classList.remove('hidden');" />
                                  <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-all flex items-center justify-center opacity-0 group-hover:opacity-100">
                                      <span class="material-symbols-outlined text-white text-3xl drop-shadow-lg" aria-hidden="true">zoom_in</span>
                                  </div>
                              </div>
                          </div>
                          <!-- Back Document -->
                          <div>
                              <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-2 block">Back of Document</label>
                              <div id="doc-back-wrapper" class="relative group rounded-2xl overflow-hidden bg-background border border-border-subtle flex items-center justify-center aspect-[16/10] cursor-pointer transition-all hover:border-primary hover:shadow-sm" <?= $backUrl ? "onclick=\"openLightbox('" . htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8') . "')\"" : '' ?>>
                                  <?php $backUrl = $staff['back_image_url'] ?? null; ?>
                                  <div id="doc-back-placeholder" class="text-center <?= $backUrl ? 'hidden' : '' ?>">
                                      <span class="material-symbols-outlined text-4xl text-text-secondary" aria-hidden="true">image</span>
                                      <p class="text-[10px] font-black uppercase tracking-widest text-text-secondary mt-1">No document uploaded</p>
                                  </div>
                                  <img loading="lazy" id="doc-back-img" src="<?= htmlspecialchars($backUrl ?? '', ENT_QUOTES, 'UTF-8') ?>" class="<?= $backUrl ? '' : 'hidden' ?> absolute inset-0 w-full h-full object-cover transition-transform group-hover:scale-105" alt="ID Back" onerror="this.classList.add('hidden'); document.getElementById('doc-back-placeholder').classList.remove('hidden');" />
                                  <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-all flex items-center justify-center opacity-0 group-hover:opacity-100">
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
              
              <!-- STAFF GOVERNANCE CONTROL -->
              <div class="ds-card p-5">
                  <h5 class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-4 ml-1">Staff Governance Actions</h5>
                  <div class="space-y-3">
                      <button id="deactivate-staff-btn" type="button" class="w-full flex items-center justify-between p-3.5 bg-background rounded-xl border border-border-subtle hover:border-warning hover:bg-warning-bg transition-all group">
                          <div class="flex items-center gap-3">
                              <span id="suspend-icon" class="material-symbols-outlined text-warning" aria-hidden="true">block</span>
                              <div class="text-left">
                                  <div id="suspend-label" class="text-xs font-black text-text-primary">Suspend Staff</div>
                                  <div class="text-[10px] font-bold text-text-secondary uppercase tracking-widest mt-0.5">Revoke system access</div>
                              </div>
                          </div>
                          <span class="material-symbols-outlined text-text-secondary text-lg" aria-hidden="true">chevron_right</span>
                      </button>

                      <button id="reset-password-btn" type="button" class="w-full flex items-center justify-between p-3.5 bg-background rounded-xl border border-border-subtle hover:border-info hover:bg-info-bg transition-all group">
                          <div class="flex items-center gap-3">
                              <span class="material-symbols-outlined text-info" aria-hidden="true">lock_reset</span>
                              <div class="text-left">
                                  <div class="text-xs font-black text-text-primary">Reset Password</div>
                                  <div class="text-[10px] font-bold text-text-secondary uppercase tracking-widest mt-0.5">Issue temporary password</div>
                              </div>
                          </div>
                          <span class="material-symbols-outlined text-text-secondary text-lg" aria-hidden="true">chevron_right</span>
                      </button>

                      <button id="delete-staff-btn" type="button" class="w-full flex items-center justify-between p-3.5 bg-background rounded-xl border border-border-subtle hover:border-error hover:bg-error-bg transition-all group">
                          <div class="flex items-center gap-3">
                              <span class="material-symbols-outlined text-error" aria-hidden="true">person_remove</span>
                              <div class="text-left">
                                  <div class="text-xs font-black text-error">Delete Account</div>
                                  <div class="text-[10px] font-bold text-text-secondary uppercase tracking-widest mt-0.5">Soft-delete permanently</div>
                              </div>
                          </div>
                          <span class="material-symbols-outlined text-text-secondary text-lg" aria-hidden="true">chevron_right</span>
                      </button>
                  </div>
              </div>

              <!-- PERFORMANCE OVERVIEW -->
              <div class="ds-card p-5">
                  <h5 class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-4 ml-1">Performance Overview</h5>
                  <div class="grid grid-cols-2 gap-3">
                      <div class="text-center p-3 bg-background rounded-xl border border-border-subtle">
                          <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1">Deposits</div>
                          <div id="stat-total-deposits" class="text-sm font-black text-success">₹<?= number_format((float)($performance['total_deposits'] ?? 0), 2) ?></div>
                      </div>
                      <div class="text-center p-3 bg-background rounded-xl border border-border-subtle">
                          <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1">Withdrawals</div>
                          <div id="stat-total-withdrawals" class="text-sm font-black text-error">₹<?= number_format((float)($performance['total_withdrawals'] ?? 0), 2) ?></div>
                      </div>
                      <div class="text-center p-3 bg-background rounded-xl border border-border-subtle">
                          <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1">Transactions</div>
                          <div id="stat-total-txns" class="text-sm font-black text-text-primary"><?= number_format((int)($performance['total_transactions'] ?? 0)) ?></div>
                      </div>
                      <div class="text-center p-3 bg-background rounded-xl border border-border-subtle">
                          <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1">Last Active</div>
                          <div id="stat-last-active" class="text-xs font-black text-text-primary mt-0.5"><?= !empty($staff['last_login_at']) ? date('d M Y', strtotime($staff['last_login_at'])) : 'Never' ?></div>
                      </div>
                  </div>
              </div>

              <!-- TODAY'S SHIFT SUMMARY -->
              <div class="ds-card p-5">
                  <h5 class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-4 ml-1">Today's Shift Summary</h5>
                  <div class="space-y-3">
                      <div class="flex justify-between items-center pb-2.5 border-b border-border-subtle">
                          <span class="text-[10px] font-black text-text-secondary uppercase tracking-widest">Deposits Processed</span>
                          <span id="summary-deposits" class="text-sm font-bold text-text-primary"><?= (int)($today['deposit_count'] ?? 0) ?> (₹<?= number_format((float)($today['total_deposits'] ?? 0), 2) ?>)</span>
                      </div>
                      <div class="flex justify-between items-center pb-2.5 border-b border-border-subtle">
                          <span class="text-[10px] font-black text-text-secondary uppercase tracking-widest">Withdrawals Processed</span>
                          <span id="summary-withdrawals" class="text-sm font-bold text-text-primary"><?= (int)($today['withdrawal_count'] ?? 0) ?> (₹<?= number_format((float)($today['total_withdrawals'] ?? 0), 2) ?>)</span>
                      </div>
                      <div class="flex justify-between items-center pb-2.5 border-b border-border-subtle">
                          <span class="text-[10px] font-black text-text-secondary uppercase tracking-widest">Total Txn Count</span>
                          <span id="summary-total-txns" class="text-sm font-bold text-text-primary"><?= (int)($today['total_transactions'] ?? 0) ?></span>
                      </div>
                      <div class="flex justify-between items-center pt-1">
                          <span class="text-[10px] font-black text-primary uppercase tracking-widest">Net Volume Today</span>
                          <?php $todayNet = (float)($today['total_deposits'] ?? 0) + (float)($today['total_withdrawals'] ?? 0); ?>
                          <span id="summary-total-amount" class="text-base font-black text-primary">₹<?= number_format($todayNet, 2) ?></span>
                      </div>
                  </div>
              </div>

              <!-- RECENT SHIFTS -->
              <div class="ds-card p-5">
                  <h5 class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-4 flex justify-between items-center ml-1">
                      <span>Recent Shifts</span>
                      <span class="material-symbols-outlined text-sm" aria-hidden="true">schedule</span>
                  </h5>
                  <div id="staff-shift-history" class="space-y-2.5">
                      <?php if (empty($shifts)): ?>
                          <div class="text-xs text-text-secondary text-center py-4">No recent shift logs recorded.</div>
                      <?php else: ?>
                          <?php foreach ($shifts as $s): ?>
                              <div class="flex items-center justify-between p-3 bg-surface rounded-xl border border-border-subtle">
                                  <div>
                                      <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest"><?= date('d M Y', strtotime($s['shift_date'])) ?></div>
                                      <div class="text-xs font-bold text-text-primary mt-0.5"><?= !empty($s['login_time']) ? date('h:i A', strtotime($s['login_time'])) : '—' ?> — <?= !empty($s['logout_time']) ? date('h:i A', strtotime($s['logout_time'])) : 'Active' ?></div>
                                  </div>
                                  <div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                      <span class="material-symbols-outlined text-base" aria-hidden="true">check</span>
                                  </div>
                              </div>
                          <?php endforeach; ?>
                      <?php endif; ?>
                  </div>
              </div>

              <!-- SECURITY NOTICE -->
              <div class="p-5 bg-background border border-border-subtle rounded-2xl overflow-hidden relative">
                  <div class="relative z-10">
                      <h5 class="text-[10px] font-black text-primary uppercase tracking-widest mb-2">Audit Compliance</h5>
                      <p class="text-[11px] leading-relaxed text-text-secondary font-bold">
                          All staff actions, transactions, and credential changes are recorded in the central audit ledger and cryptographically tied to the active administrator session.
                      </p>
                  </div>
                  <span class="material-symbols-outlined absolute -right-3 -bottom-3 text-7xl text-border-subtle" aria-hidden="true">verified_user</span>
              </div>
          </div>
      </div>

      <!-- TRANSACTION HISTORY TABLE -->
      <div class="ds-card overflow-hidden mt-6 relative z-10 shadow-lg">
          <div class="p-5 sm:p-6 border-b border-border-subtle flex flex-col sm:flex-row justify-between sm:items-center gap-4 bg-background/50">
              <div>
                  <h4 class="font-black text-lg text-text-primary tracking-tight">Staff Transaction Ledger</h4>
                  <p class="text-[10px] font-black text-text-secondary uppercase tracking-widest mt-0.5">Transactions processed across customer accounts</p>
              </div>
              <button id="open-passbook-modal-table-btn" type="button" class="btn btn-secondary px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider inline-flex items-center gap-1.5 shadow-sm transition-all">
                  <span class="material-symbols-outlined text-[16px]" aria-hidden="true">receipt_long</span>
                  <span>Passbook & Statement</span>
              </button>
          </div>
          <div class="ds-table-wrapper border-0 rounded-none shadow-none bg-transparent overflow-x-auto">
              <table class="ds-table w-full text-left min-w-[900px]">
                  <thead scope="col">
                      <tr>
                          <th scope="col" class="w-[15%]">Receipt / Txn ID</th>
                          <th scope="col" class="w-[18%]">Date & Time</th>
                          <th scope="col" class="w-[20%]">Customer</th>
                          <th scope="col" class="w-[17%]">Account No</th>
                          <th scope="col" class="w-[12%] text-center">Type</th>
                          <th scope="col" class="w-[18%] text-right">Amount</th>
                      </tr>
                  </thead>
                  <tbody id="staff-transactions-body">
                      <?php if (empty($transactions)): ?>
                          <tr><td colspan="6" class="text-center py-12 text-text-secondary text-xs font-bold uppercase tracking-wider">No transactions processed yet</td></tr>
                      <?php else: ?>
                          <?php foreach ($transactions as $t): ?>
                              <?php
                                  $isDep = ($t['type'] === 'deposit');
                                  $badgeHtml = $isDep
                                      ? '<span class="badge-tag bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full inline-flex items-center gap-1">DEPOSIT</span>'
                                      : '<span class="badge-tag bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20 text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full inline-flex items-center gap-1">WITHDRAWAL</span>';
                                  $amtCls = $isDep ? 'text-success font-black' : 'text-error font-black';
                              ?>
                              <tr class="hover:bg-surface-hover transition-colors border-b border-border-subtle last:border-0">
                                  <td class="font-mono text-xs font-bold text-text-secondary"><?= htmlspecialchars($t['txn_id'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                  <td class="text-xs font-bold text-text-primary whitespace-nowrap"><?= !empty($t['created_at']) ? date('d M Y, h:i A', strtotime($t['created_at'])) : '—' ?></td>
                                  <td class="text-xs font-bold text-text-primary truncate max-w-[180px]"><?= htmlspecialchars($t['customer_name'] ?? 'Customer', ENT_QUOTES, 'UTF-8') ?></td>
                                  <td class="font-mono text-xs text-text-secondary"><?= htmlspecialchars($t['account_no'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                                  <td class="text-center"><?= $badgeHtml ?></td>
                                  <td class="text-right text-xs font-mono <?= $amtCls ?>">₹<?= number_format((float)($t['amount'] ?? 0), 2) ?></td>
                              </tr>
                          <?php endforeach; ?>
                      <?php endif; ?>
                  </tbody>
              </table>
          </div>
      </div>

      <!-- RECENT AUDIT ACTIVITIES -->
      <div class="ds-card overflow-hidden mt-6 relative z-10 shadow-lg">
          <div class="p-5 sm:p-6 border-b border-border-subtle flex justify-between items-center bg-background/50">
              <div>
                  <h4 class="font-black text-lg text-text-primary tracking-tight">Staff Security & Activity Log</h4>
                  <p class="text-[10px] font-black text-text-secondary uppercase tracking-widest mt-0.5">Logged events performed by this staff account</p>
              </div>
          </div>
          <div class="ds-table-wrapper border-0 rounded-none shadow-none bg-transparent overflow-x-auto">
              <table class="ds-table w-full text-left min-w-[800px]">
                  <thead scope="col">
                      <tr>
                          <th scope="col" class="w-[20%]">Date & Time</th>
                          <th scope="col" class="w-[20%]">Action Type</th>
                          <th scope="col" class="w-[42%]">Details</th>
                          <th scope="col" class="w-[18%] text-right">IP Address</th>
                      </tr>
                  </thead>
                  <tbody id="staff-activities-body">
                      <?php if (empty($activities)): ?>
                          <tr><td colspan="4" class="text-center py-12 text-text-secondary text-xs font-bold uppercase tracking-wider">No audit activities logged</td></tr>
                      <?php else: ?>
                          <?php foreach ($activities as $a): ?>
                              <tr class="hover:bg-surface-hover transition-colors border-b border-border-subtle last:border-0">
                                  <td class="text-xs font-bold text-text-primary whitespace-nowrap"><?= !empty($a['created_at']) ? date('d M Y, h:i A', strtotime($a['created_at'])) : '—' ?></td>
                                  <td>
                                      <span class="px-2.5 py-1 rounded-lg text-[10px] font-mono font-bold uppercase tracking-wider bg-surface border border-border-subtle text-text-secondary">
                                          <?= htmlspecialchars($a['action'] ?? 'ACTIVITY', ENT_QUOTES, 'UTF-8') ?>
                                      </span>
                                  </td>
                                  <td class="text-xs text-text-secondary font-medium"><?= htmlspecialchars($a['details'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                  <td class="text-right text-xs font-mono font-bold text-text-secondary"><?= htmlspecialchars($a['ip_address'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                              </tr>
                          <?php endforeach; ?>
                      <?php endif; ?>
                  </tbody>
              </table>
          </div>
      </div>

    </div>
  </div>

  <!-- PROFESSIONAL BANK PASSBOOK & STATEMENT MODAL -->
  <div id="passbook-modal" class="fixed inset-0 z-[2500] hidden items-center justify-center p-2 sm:p-6 bg-black/80 backdrop-blur-md overflow-y-auto" role="dialog" aria-modal="true">
    <div class="relative w-full max-w-4xl bg-surface rounded-3xl border border-border-subtle shadow-2xl overflow-hidden flex flex-col my-auto max-h-[92vh]">
      
      <!-- Top Modal Bar (No Print) -->
      <div class="p-4 sm:p-5 border-b border-border-subtle flex items-center justify-between bg-background/80 backdrop-blur sticky top-0 z-20 no-print">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl bg-primary/10 text-primary flex items-center justify-center border border-primary/20">
            <span class="material-symbols-outlined text-xl" aria-hidden="true">receipt_long</span>
          </div>
          <div>
            <h3 class="text-base font-black font-headline text-text-primary">Staff Transaction Statement & Passbook</h3>
            <p class="text-[10px] font-bold text-text-secondary uppercase tracking-wider">Authorized Banking Record</p>
          </div>
        </div>
        
        <div class="flex items-center gap-2">
          <button id="print-passbook-action" type="button" class="btn btn-primary px-3.5 py-2 rounded-xl text-xs font-bold uppercase tracking-wider inline-flex items-center gap-1.5 shadow-sm">
            <span class="material-symbols-outlined text-[16px]" aria-hidden="true">print</span>
            <span>Print Passbook</span>
          </button>
          <button id="close-passbook-btn" type="button" class="w-9 h-9 rounded-xl bg-surface border border-border-subtle flex items-center justify-center text-text-secondary hover:text-text-primary transition-colors">
            <span class="material-symbols-outlined text-lg" aria-hidden="true">close</span>
          </button>
        </div>
      </div>

      <!-- Date Range Filter Bar (No Print) -->
      <div class="px-4 py-3 sm:px-6 bg-surface-subtle border-b border-border-subtle flex flex-wrap items-center justify-between gap-3 no-print">
        <div class="flex flex-wrap items-center gap-2 text-xs">
          <span class="font-bold text-text-secondary uppercase tracking-wider text-[10px] mr-1">Statement Period:</span>
          
          <div class="flex items-center gap-1.5 bg-surface px-2.5 py-1 rounded-lg border border-border-subtle">
            <span class="text-[10px] font-bold text-text-secondary uppercase">From:</span>
            <input type="date" id="pb-filter-from" class="bg-transparent text-xs text-text-primary focus:outline-none cursor-pointer">
          </div>

          <div class="flex items-center gap-1.5 bg-surface px-2.5 py-1 rounded-lg border border-border-subtle">
            <span class="text-[10px] font-bold text-text-secondary uppercase">To:</span>
            <input type="date" id="pb-filter-to" class="bg-transparent text-xs text-text-primary focus:outline-none cursor-pointer">
          </div>

          <button id="pb-apply-filter" type="button" class="btn btn-secondary px-3 py-1 text-xs font-bold rounded-lg inline-flex items-center gap-1 shadow-sm transition-all">
            <span class="material-symbols-outlined text-[14px]" aria-hidden="true">filter_alt</span>
            <span>Filter</span>
          </button>
        </div>

        <div class="flex items-center gap-1.5 text-[11px]">
          <button id="pb-preset-all" type="button" class="px-2.5 py-1 rounded-md bg-surface border border-border-subtle hover:border-primary text-text-secondary hover:text-text-primary font-bold text-[10px] uppercase transition-all">All Time</button>
          <button id="pb-preset-month" type="button" class="px-2.5 py-1 rounded-md bg-surface border border-border-subtle hover:border-primary text-text-secondary hover:text-text-primary font-bold text-[10px] uppercase transition-all">This Month</button>
          <button id="pb-preset-30" type="button" class="px-2.5 py-1 rounded-md bg-surface border border-border-subtle hover:border-primary text-text-secondary hover:text-text-primary font-bold text-[10px] uppercase transition-all">Last 30 Days</button>
          <button id="pb-preset-90" type="button" class="px-2.5 py-1 rounded-md bg-surface border border-border-subtle hover:border-primary text-text-secondary hover:text-text-primary font-bold text-[10px] uppercase transition-all">Last 90 Days</button>
        </div>
      </div>

      <!-- Printable Passbook Paper Area -->
      <div class="p-4 sm:p-8 overflow-y-auto bg-slate-100 dark:bg-black/40">
        <div id="passbook-print-area" class="bg-white text-black p-6 sm:p-8 max-w-4xl mx-auto border-2 border-black font-mono shadow-sm">
          
          <!-- Bank Header -->
          <div class="border-b-2 border-black pb-3 mb-4 text-center">
            <h1 class="text-2xl font-black tracking-wider uppercase text-black font-serif">FINZO FINANCIAL BANKING CORP</h1>
            <div class="text-xs font-bold uppercase tracking-widest text-black mt-0.5">STAFF TRANSACTION LEDGER & OPERATIONAL PASSBOOK</div>
            <div class="flex justify-between items-center text-[11px] font-bold text-black border-t border-black pt-2 mt-2.5">
              <div id="pb-branch-header">BRANCH: MAIN HEADQUARTERS</div>
              <div>IFSC: FINZ0002026</div>
              <div>MICR: 400026002</div>
            </div>
          </div>

          <!-- Staff Particulars Box -->
          <table class="w-full border-collapse border border-black text-xs mb-4">
            <tbody>
              <tr class="border-b border-black">
                <td class="p-2 border-r border-black font-bold w-1/4 bg-slate-100 text-black">STAFF FULL NAME</td>
                <td class="p-2 border-r border-black font-black w-1/4 text-black text-sm uppercase" id="pb-full-name">Staff Member</td>
                <td class="p-2 border-r border-black font-bold w-1/4 bg-slate-100 text-black">STAFF ID</td>
                <td class="p-2 font-black w-1/4 text-black text-sm" id="pb-staff-id">STF000</td>
              </tr>
              <tr class="border-b border-black">
                <td class="p-2 border-r border-black font-bold bg-slate-100 text-black">DESIGNATION / ROLE</td>
                <td class="p-2 border-r border-black font-bold text-black uppercase" id="pb-designation">Teller / Operations</td>
                <td class="p-2 border-r border-black font-bold bg-slate-100 text-black">BRANCH LOCATION</td>
                <td class="p-2 font-bold text-black uppercase" id="pb-branch">Main Branch</td>
              </tr>
              <tr class="border-b border-black">
                <td class="p-2 border-r border-black font-bold bg-slate-100 text-black">MOBILE NUMBER</td>
                <td class="p-2 border-r border-black font-bold text-black" id="pb-phone">N/A</td>
                <td class="p-2 border-r border-black font-bold bg-slate-100 text-black">STATEMENT PERIOD</td>
                <td class="p-2 font-bold text-black" id="pb-generated-date"><?= date('d-m-Y') ?></td>
              </tr>
              <tr>
                <td class="p-2 border-r border-black font-bold bg-slate-100 text-black">OFFICIAL ADDRESS</td>
                <td class="p-2 text-black font-bold" colspan="3" id="pb-address">N/A</td>
              </tr>
            </tbody>
          </table>

          <!-- Official Passbook Ledger -->
          <div class="mb-4 overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse border border-black font-mono">
              <thead>
                <tr class="border-b-2 border-black bg-slate-100 text-black font-black text-[11px] uppercase">
                  <th class="p-2 border-r border-black w-10 text-center">LINE</th>
                  <th class="p-2 border-r border-black w-24 text-center">DATE</th>
                  <th class="p-2 border-r border-black w-28">RECEIPT / TXN ID</th>
                  <th class="p-2 border-r border-black">CUSTOMER / PARTICULARS</th>
                  <th class="p-2 border-r border-black w-28">ACCOUNT NO</th>
                  <th class="p-2 border-r border-black w-24 text-center">TYPE</th>
                  <th class="p-2 w-28 text-right">AMOUNT ₹</th>
                </tr>
              </thead>
              <tbody id="pb-transactions-body" class="divide-y divide-black text-[11px] text-black">
                <tr><td colspan="7" class="p-4 text-center text-black">No transaction entries found for the selected period.</td></tr>
              </tbody>
            </table>
          </div>

          <!-- Bottom Totals Summary Box -->
          <table class="w-full border-collapse border-2 border-black text-xs mb-4 text-black font-mono">
            <tbody>
              <tr class="font-bold">
                <td class="p-2.5 border-r border-black w-1/4 text-center">
                  <div class="text-[10px] uppercase">TOTAL DEPOSITS (CR)</div>
                  <div class="text-sm font-black mt-0.5 text-black" id="pb-total-credits">₹ 0.00</div>
                </td>
                <td class="p-2.5 border-r border-black w-1/4 text-center">
                  <div class="text-[10px] uppercase">TOTAL WITHDRAWALS (DR)</div>
                  <div class="text-sm font-black mt-0.5 text-black" id="pb-total-debits">₹ 0.00</div>
                </td>
                <td class="p-2.5 border-r border-black w-1/4 text-center">
                  <div class="text-[10px] uppercase">TOTAL TRANSACTIONS</div>
                  <div class="text-sm font-black mt-0.5 text-black" id="pb-total-txns-count">0</div>
                </td>
                <td class="p-2.5 w-1/4 text-center bg-slate-100 font-black">
                  <div class="text-[10px] uppercase">TOTAL VOLUME PROCESSED</div>
                  <div class="text-sm font-black mt-0.5 text-black" id="pb-net-balance">₹ 0.00</div>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Footer Rules & Notice -->
          <div class="text-[10px] text-black leading-tight border-t border-black pt-3 font-mono">
            <p class="font-bold">* Please examine this passbook & ledger statement immediately. Discrepancies, if any, should be reported to the Branch Operations Supervisor within 15 days.</p>
            <div class="flex justify-between items-center mt-2 text-[9px] font-bold">
              <span>* COMPUTER GENERATED PASSBOOK & STATEMENT — NO PHYSICAL SIGNATURE REQUIRED</span>
              <span>PAGE 1 OF 1</span>
            </div>
          </div>

        </div>
      </div>

    </div>
  </div>

  <!-- GOVERNANCE SECURITY MODAL -->
  <div id="governance-modal" class="fixed inset-0 z-[2600] hidden items-center justify-center p-4 bg-black/80 backdrop-blur-sm" role="dialog" aria-modal="true">
      <div class="bg-surface rounded-3xl border border-border-subtle shadow-2xl w-full max-w-lg overflow-hidden flex flex-col">
          <div id="modal-header" class="p-6 text-center border-b border-border-subtle bg-background/50">
              <div id="modal-icon-container" class="w-14 h-14 rounded-2xl mx-auto flex items-center justify-center mb-3 transition-all duration-300">
                  <span id="modal-icon" class="material-symbols-outlined text-3xl" aria-hidden="true">warning</span>
              </div>
              <h3 id="modal-title" class="font-headline text-xl font-black text-text-primary">Authorize Governance Action</h3>
              <p id="modal-subtitle" class="text-[10px] text-text-secondary font-bold mt-0.5 uppercase tracking-wider">Administrative Verification Required</p>
          </div>
          
          <div class="p-6 space-y-4">
              <div id="modal-body-text" class="text-xs font-bold text-text-secondary text-center leading-relaxed"></div>

              <!-- Reason Selection Step -->
              <div id="reason-step" class="hidden space-y-2">
                  <label class="block text-[10px] font-black uppercase tracking-wider text-text-secondary">Select Primary Reason:</label>
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-2" id="reason-grid">
                      <!-- Injected by JS -->
                  </div>
              </div>

              <!-- Custom Notes -->
              <div id="custom-reason-container" class="hidden">
                  <label for="governance-notes" class="block text-[10px] font-black uppercase tracking-wider text-text-secondary mb-1">Additional Observations (Optional):</label>
                  <textarea id="governance-notes" rows="2" placeholder="Explain the governance decision for audit trail..." class="fin-input !rounded-xl text-xs"></textarea>
              </div>

              <!-- Admin Password Auth Step -->
              <div id="auth-step" class="pt-3 border-t border-border-subtle">
                  <label for="admin-password" class="block text-[10px] font-black uppercase tracking-wider text-text-secondary mb-1.5">Confirm Identity (Admin Password):</label>
                  <div class="relative">
                      <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-text-secondary text-lg" aria-hidden="true">lock</span>
                      <input type="password" id="admin-password" placeholder="••••••••" class="fin-input !pl-11 !py-2.5 !rounded-xl text-sm">
                  </div>
                  <p id="auth-error" class="text-[10px] font-bold text-error uppercase tracking-wider mt-1.5 hidden"></p>
              </div>
          </div>

          <!-- Footer Actions -->
          <div class="p-6 pt-0 flex gap-3">
              <button type="button" id="abort-governance-btn" class="flex-1 py-3 bg-surface hover:bg-surface-hover text-text-secondary font-bold rounded-xl text-xs uppercase tracking-wider transition-all border border-border-subtle">Cancel</button>
              <button type="button" id="confirm-governance-btn" class="flex-1 py-3 bg-primary text-white font-bold rounded-xl text-xs uppercase tracking-wider shadow-sm hover:scale-[1.02] active:scale-[0.98] transition-all">Confirm</button>
          </div>
      </div>
  </div>

  <!-- LIGHTBOX MODAL FOR DOCUMENTS -->
  <div id="lightbox-modal" class="fixed inset-0 z-[3000] hidden items-center justify-center p-4 bg-black/90 backdrop-blur-md" role="dialog" aria-modal="true">
      <button type="button" id="close-lightbox-btn" class="absolute top-6 right-6 w-10 h-10 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white transition-all">
          <span class="material-symbols-outlined text-2xl" aria-hidden="true">close</span>
      </button>
      <img id="lightbox-img" src="" class="max-w-[90vw] max-h-[85vh] object-contain rounded-xl shadow-2xl border border-white/10" alt="Document Zoom">
  </div>
<?php
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/staff-detail.js"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>