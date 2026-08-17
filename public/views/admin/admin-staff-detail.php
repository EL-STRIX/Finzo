<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin — Staff Dossier';

// Bootstrap early so SSR always runs (even on direct file access).
// The layout guard `if (!defined('FINZO_ROOT'))` prevents double-loading.
if (!defined('FINZO_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}
\App\Shared\Security\SessionManager::start();

// SSR data loading — comprehensive staff dossier
$staffId = intval($_GET['id'] ?? 0);
$staffData = null;
if ($staffId > 0) {
    try {
        $container = \App\Shared\Core\Container::getInstance();
        $service = $container->get(\App\Domain\Contracts\Services\AdminStaffBranchServiceInterface::class);
        $staffData = $service->getStaffDetailComprehensive($staffId);
    } catch (\Throwable $e) {
        $staffData = null;
    }
}

$staff        = $staffData['staff']             ?? [];
$address      = $staffData['address']           ?? [];
$performance  = $staffData['performance']       ?? [];
$today        = $staffData['today_performance'] ?? [];
$shifts       = $staffData['shifts']            ?? [];
$transactions = $staffData['transactions']      ?? [];
$activities   = $staffData['activities']        ?? [];
$documents    = $staffData['documents']         ?? [];

// Auth guard — redirect to admin login if not authenticated
if (empty($staff) && empty(\App\Shared\Security\Auth::user())) {
    header('Location: /Finzo/public/views/auth/admin-login.php');
    exit;
}

$fullName    = htmlspecialchars($staff['full_name']   ?? 'Staff Member Profile', ENT_QUOTES, 'UTF-8');
$staffCode   = htmlspecialchars($staff['staff_id']    ?? 'STF---',              ENT_QUOTES, 'UTF-8');
$designation = htmlspecialchars($staff['designation'] ?? 'Staff',               ENT_QUOTES, 'UTF-8');
$department  = htmlspecialchars($staff['department']  ?? 'Teller Operations',   ENT_QUOTES, 'UTF-8');
$status      = $staff['status'] ?? 'active';
$isActive    = ($status === 'active');
$initials    = !empty($staff['full_name'])
    ? strtoupper(substr(implode('', array_map(fn($n) => $n[0] ?? '', explode(' ', $staff['full_name']))), 0, 2))
    : 'ST';
$profilePhoto = !empty($staff['profile_photo_url']) ? $staff['profile_photo_url'] : null;

// Age & DOB calculation
$dobStr    = !empty($staff['date_of_birth']) ? date('d M Y', strtotime($staff['date_of_birth'])) : '—';
$genderStr = htmlspecialchars($staff['gender'] ?? 'Not specified', ENT_QUOTES, 'UTF-8');
$ageVal    = null;
if (!empty($staff['date_of_birth'])) {
    $ageVal = (new \DateTime($staff['date_of_birth']))->diff(new \DateTime('today'))->y;
    if ($ageVal > 0) {
        $genderStr .= " ({$ageVal} yrs)";
    }
}

// Joining date & Tenure
$joinedDate = !empty($staff['date_of_joining'])
    ? date('d M Y', strtotime($staff['date_of_joining']))
    : (!empty($staff['created_at']) ? date('d M Y', strtotime($staff['created_at'])) : '—');

$tenureStr = '—';
$rawJoining = !empty($staff['date_of_joining']) ? $staff['date_of_joining'] : ($staff['created_at'] ?? null);
if (!empty($rawJoining)) {
    $joinDateObj = new \DateTime($rawJoining);
    $diffObj = $joinDateObj->diff(new \DateTime('today'));
    $tenureParts = [];
    if ($diffObj->y > 0) $tenureParts[] = $diffObj->y . ' ' . ($diffObj->y === 1 ? 'yr' : 'yrs');
    if ($diffObj->m > 0) $tenureParts[] = $diffObj->m . ' ' . ($diffObj->m === 1 ? 'mo' : 'mos');
    if (empty($tenureParts)) {
        $tenureParts[] = ($diffObj->d > 0) ? ($diffObj->d . ' ' . ($diffObj->d === 1 ? 'day' : 'days')) : 'Joined Today';
    }
    $tenureStr = implode(', ', $tenureParts);
}

// Branch Info
$branchName = !empty($staff['branch_name'])
    ? htmlspecialchars($staff['branch_name'] . (!empty($staff['branch_code']) ? " ({$staff['branch_code']})" : ''), ENT_QUOTES, 'UTF-8')
    : 'Unassigned';
$branchCode = htmlspecialchars($staff['branch_code'] ?? '—', ENT_QUOTES, 'UTF-8');
$branchState = htmlspecialchars($staff['branch_state'] ?? '—', ENT_QUOTES, 'UTF-8');

// Lock & Security Info
$isLocked = !empty($staff['locked_until']) && (strtotime($staff['locked_until']) > time());
$lockStr  = $isLocked ? ('Locked until ' . date('d M Y, h:i A', strtotime($staff['locked_until']))) : 'Unlocked / Secure';

$tempPassStatus = $staff['temp_pass_status'] ?? 'none';
$tempPassLabel  = match($tempPassStatus) {
    'pending' => 'Pending First Login',
    'expired' => 'Expired',
    default   => 'None (Standard Password)'
};

$createdAt = !empty($staff['created_at']) ? date('d M Y, h:i A', strtotime($staff['created_at'])) : '—';
$updatedAt = !empty($staff['updated_at']) ? date('d M Y, h:i A', strtotime($staff['updated_at'])) : '—';

ob_start();
?>
<script>
  window.__INITIAL_STAFF__ = <?= json_encode($staff ?? new \stdClass()) ?>;
  window.__INITIAL_ADDRESS__ = <?= json_encode($address ?? new \stdClass()) ?>;
  window.__INITIAL_TRANSACTIONS__ = <?= json_encode($transactions ?? []) ?>;
</script>

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
          <a href="/Finzo/public/views/admin/admin-staff.php" class="w-10 h-10 flex items-center justify-center rounded-xl bg-surface border border-border-subtle hover:bg-surface-hover transition-all text-text-secondary hover:text-text-primary shadow-sm shrink-0" aria-label="Back to Staff List">
            <span class="material-symbols-outlined" aria-hidden="true">arrow_back</span>
          </a>
          <div>
            <div class="text-[10px] font-black tracking-widest uppercase text-text-secondary">STAFF PROFILE &amp; DOSSIER</div>
            <div class="text-xl sm:text-2xl font-black tracking-tight mt-0.5 text-text-primary" id="header-staff-name"><?= $fullName ?></div>
          </div>
        </div>
      </div>

      <!-- ALERT BANNER FOR SUSPENSION OR LOCK -->
      <?php 
          $suspReason = !empty($staff['suspension_reason']) ? $staff['suspension_reason'] : 'Administrative Review';
          if (preg_match('/Reason:\s*(.*)$/i', $suspReason, $m)) {
              $suspReason = trim($m[1]);
          }
          $suspDate = !empty($staff['suspended_at']) ? date('d M Y, h:i A', strtotime($staff['suspended_at'])) : '';
      ?>
      <div id="security-alert-banner" class="<?= (!$isActive || $isLocked) ? '' : 'hidden' ?> rounded-2xl p-4 sm:p-5 flex items-start gap-4 border shadow-sm <?= !$isActive ? 'border-rose-500/30 bg-rose-500/10 text-rose-700 dark:text-rose-300' : 'border-amber-500/30 bg-amber-500/10 text-amber-700 dark:text-amber-300' ?>">
          <div class="w-10 h-10 rounded-xl <?= !$isActive ? 'bg-rose-500/20 text-rose-600 dark:text-rose-400' : 'bg-amber-500/20 text-amber-600 dark:text-amber-400' ?> flex items-center justify-center shrink-0 mt-0.5">
              <span id="security-alert-icon" class="material-symbols-outlined text-2xl" aria-hidden="true"><?= !$isActive ? 'gavel' : 'lock' ?></span>
          </div>
          <div class="flex-1">
              <div class="flex flex-wrap items-center gap-2">
                  <h4 id="security-alert-title" class="text-xs font-black tracking-widest uppercase <?= !$isActive ? 'text-rose-600 dark:text-rose-400' : 'text-amber-600 dark:text-amber-400' ?>">
                      <?= !$isActive ? 'ACCOUNT SUSPENDED' : 'ACCOUNT TEMPORARILY LOCKED' ?>
                  </h4>
                  <?php if (!$isActive && $suspDate): ?>
                      <span class="text-[10px] font-bold text-text-secondary">• <?= $suspDate ?></span>
                  <?php endif; ?>
              </div>
              <p id="security-alert-message" class="text-sm font-bold text-text-primary mt-1">
                  <?php if (!$isActive): ?>
                      Reason: <span class="font-black text-rose-600 dark:text-rose-400"><?= htmlspecialchars($suspReason, ENT_QUOTES, 'UTF-8') ?></span>
                      <span class="text-xs font-medium text-text-secondary block mt-0.5">Staff terminal privileges and transaction processing rights are currently revoked.</span>
                  <?php elseif ($isLocked): ?>
                      This account is locked due to <?= (int)($staff['failed_attempts'] ?? 0) ?> failed login attempts. Unlocks automatically at <?= htmlspecialchars($staff['locked_until'], ENT_QUOTES, 'UTF-8') ?>.
                  <?php endif; ?>
              </p>
          </div>
      </div>

      <!-- MAIN CONTENT GRID -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8">
          
          <!-- LEFT COLUMN (8 cols) -->
          <div class="lg:col-span-8 space-y-6">
              
              <!-- 1. HERO PROFILE CARD -->
              <div class="ds-card p-6 sm:p-8">
                  <div class="flex flex-col sm:flex-row items-center gap-6 mb-8 pb-8 border-b border-border-subtle text-center sm:text-left">
                      <div class="relative shrink-0">
                          <div id="staff-photo-container" class="w-24 h-24 sm:w-28 sm:h-28 rounded-3xl overflow-hidden shadow-md bg-surface border border-border-subtle flex items-center justify-center cursor-pointer transition-transform hover:scale-105" <?= $profilePhoto ? "onclick=\"openLightbox('" . htmlspecialchars($profilePhoto, ENT_QUOTES, 'UTF-8') . "')\"" : '' ?>>
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
                          <div class="flex flex-wrap items-center justify-center sm:justify-start gap-4 text-xs font-bold text-text-secondary">
                              <span class="flex items-center gap-1.5">
                                  <span class="material-symbols-outlined text-base" aria-hidden="true">calendar_today</span>
                                  <span>Joined <strong id="info-joined-date" class="text-text-primary font-black"><?= $joinedDate ?></strong></span>
                              </span>
                              <span class="text-border-subtle">•</span>
                              <span class="flex items-center gap-1.5">
                                  <span class="material-symbols-outlined text-base" aria-hidden="true">hourglass_top</span>
                                  <span>Tenure: <strong id="info-tenure" class="text-text-primary font-black"><?= $tenureStr ?></strong></span>
                              </span>
                          </div>
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

              <!-- 2. EMPLOYMENT & CORPORATE PROFILE -->
              <div class="ds-card">
                  <div class="p-5 border-b border-border-subtle bg-background/50 flex items-center justify-between">
                      <h4 class="font-black text-[10px] uppercase tracking-widest flex items-center gap-2 text-text-secondary">
                          <span class="material-symbols-outlined text-text-secondary text-lg" aria-hidden="true">corporate_fare</span>
                          <span>Employment &amp; Organizational Profile</span>
                      </h4>
                      <span class="text-[10px] font-mono font-bold text-text-secondary uppercase">CODE: <?= $staffCode ?></span>
                  </div>
                  <div class="p-5 grid grid-cols-2 sm:grid-cols-4 gap-5">
                      <div>
                          <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Designation</label>
                          <div id="emp-designation" class="text-sm font-bold text-text-primary"><?= $designation ?></div>
                      </div>
                      <div>
                          <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Department</label>
                          <div id="emp-department" class="text-sm font-bold text-text-primary"><?= $department ?></div>
                      </div>
                      <div>
                          <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Branch Code</label>
                          <div id="emp-branch-code" class="text-sm font-bold font-mono text-text-primary"><?= $branchCode ?></div>
                      </div>
                      <div>
                          <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Branch State</label>
                          <div id="emp-branch-state" class="text-sm font-bold text-text-primary"><?= $branchState ?></div>
                      </div>
                      <div>
                          <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Date of Joining</label>
                          <div id="emp-joined-date" class="text-xs font-bold text-text-primary"><?= $joinedDate ?></div>
                      </div>
                      <div>
                          <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Onboarded By</label>
                          <div id="emp-created-by" class="text-xs font-bold text-text-primary"><?= htmlspecialchars($staff['created_by_name'] ?? 'System', ENT_QUOTES, 'UTF-8') ?></div>
                      </div>
                      <div>
                          <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Account Created</label>
                          <div id="emp-created-at" class="text-xs font-bold text-text-primary"><?= $createdAt ?></div>
                      </div>
                      <div>
                          <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Last Profile Update</label>
                          <div id="emp-updated-at" class="text-xs font-bold text-text-primary"><?= $updatedAt ?></div>
                      </div>
                  </div>
              </div>

              <!-- 3. RESIDENTIAL ADDRESS -->
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

              <!-- 4. IDENTITY & KYC DOCUMENTS -->
              <div class="ds-card">
                  <div class="p-5 border-b border-border-subtle bg-background/50 flex items-center justify-between">
                      <h4 class="font-black text-[10px] uppercase tracking-widest flex items-center gap-2 text-text-secondary">
                          <span class="material-symbols-outlined text-text-secondary text-lg" aria-hidden="true">badge</span>
                          <span>Official Identity Verification Documents</span>
                      </h4>
                      <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-0.5 rounded-full">KYC Record</span>
                  </div>
                  <div class="p-5">
                      <div class="grid grid-cols-2 sm:grid-cols-3 gap-5 mb-6">
                          <div>
                              <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Document Type</label>
                              <div id="doc-id-type" class="text-sm font-bold text-text-primary"><?= htmlspecialchars($staff['id_type'] ?? 'Identity Proof', ENT_QUOTES, 'UTF-8') ?></div>
                          </div>
                          <div>
                              <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Document Number</label>
                              <div id="doc-id-number" class="text-sm font-bold font-mono text-text-primary"><?= htmlspecialchars($staff['id_number'] ?? '—', ENT_QUOTES, 'UTF-8') ?></div>
                          </div>
                          <div>
                              <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Verification Status</label>
                              <div class="text-xs font-bold text-success flex items-center gap-1 mt-0.5">
                                  <span class="material-symbols-outlined text-sm" aria-hidden="true">verified</span>
                                  <span>On File &amp; Verified</span>
                              </div>
                          </div>
                      </div>

                      <div id="doc-images-container" class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                          <!-- Front Document -->
                          <div>
                              <div class="flex items-center justify-between mb-2">
                                  <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary block">Front of Document</label>
                                  <span class="text-[10px] font-bold text-text-secondary">Click to Zoom</span>
                              </div>
                              <?php $frontUrl = $staff['front_image_url'] ?? null; ?>
                              <div id="doc-front-wrapper" class="relative group rounded-2xl overflow-hidden bg-background border border-border-subtle flex items-center justify-center aspect-[16/10] cursor-pointer transition-all hover:border-primary hover:shadow-md" <?= $frontUrl ? "onclick=\"openLightbox('" . htmlspecialchars($frontUrl, ENT_QUOTES, 'UTF-8') . "')\"" : '' ?>>
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
                              <div class="flex items-center justify-between mb-2">
                                  <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary block">Back of Document</label>
                                  <span class="text-[10px] font-bold text-text-secondary">Click to Zoom</span>
                              </div>
                              <?php $backUrl = $staff['back_image_url'] ?? null; ?>
                              <div id="doc-back-wrapper" class="relative group rounded-2xl overflow-hidden bg-background border border-border-subtle flex items-center justify-center aspect-[16/10] cursor-pointer transition-all hover:border-primary hover:shadow-md" <?= $backUrl ? "onclick=\"openLightbox('" . htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8') . "')\"" : '' ?>>
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

              <!-- 5. SYSTEM & SECURITY AUDIT -->
              <div class="ds-card">
                  <div class="p-5 border-b border-border-subtle bg-background/50">
                      <h4 class="font-black text-[10px] uppercase tracking-widest flex items-center gap-2 text-text-secondary">
                          <span class="material-symbols-outlined text-text-secondary text-lg" aria-hidden="true">security</span>
                          <span>System, Security &amp; Access Controls</span>
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
                          <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Account Lock Status</label>
                          <div id="sys-lock-status" class="text-xs font-bold <?= $isLocked ? 'text-error' : 'text-success' ?>"><?= $lockStr ?></div>
                      </div>
                      <div>
                          <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Failed Login Attempts</label>
                          <div id="sys-failed-logins" class="text-xs font-bold text-text-primary"><?= (int)($staff['failed_attempts'] ?? 0) ?> attempts</div>
                      </div>
                      <div>
                          <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Temp Password Status</label>
                          <div id="sys-temp-pass" class="text-xs font-bold text-text-primary"><?= $tempPassLabel ?></div>
                      </div>
                      <div>
                          <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Total Shifts Logged</label>
                          <div id="sys-total-shifts" class="text-xs font-bold text-text-primary"><?= (int)($staffData['total_shifts'] ?? count($shifts)) ?></div>
                      </div>
                      <div>
                          <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Account Lifecycle</label>
                          <div id="sys-lifecycle" class="text-xs font-bold <?= !empty($staff['is_deleted']) ? 'text-error' : 'text-success' ?>"><?= !empty($staff['is_deleted']) ? 'Soft-Deleted' : 'Active Ledger' ?></div>
                      </div>
                      <div>
                          <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-1 block">Authorization Role</label>
                          <div id="sys-auth-role" class="text-xs font-bold text-primary">STAFF_OPERATIONS</div>
                      </div>
                  </div>
              </div>

          </div>

          <!-- RIGHT COLUMN (4 cols) -->
          <div class="lg:col-span-4 space-y-6">
              
              <!-- STAFF GOVERNANCE CONTROL -->
              <div class="ds-card p-5 relative z-20">
                  <h5 class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-4 ml-1">Staff Governance Actions</h5>
                  <div class="space-y-3">
                      <button id="deactivate-staff-btn" type="button" onclick="if(window.openGovernanceModal) window.openGovernanceModal(this.dataset.action || 'deactivate');" class="w-full flex items-center justify-between p-3.5 bg-background rounded-xl border border-border-subtle hover:border-warning hover:bg-warning-bg transition-all cursor-pointer group select-none relative z-30" data-action="<?= $isActive ? 'deactivate' : 'reinstate' ?>">
                          <div class="flex items-center gap-3 pointer-events-none">
                              <span id="suspend-icon" class="material-symbols-outlined <?= $isActive ? 'text-warning' : 'text-success' ?>" aria-hidden="true"><?= $isActive ? 'block' : 'check_circle' ?></span>
                              <div class="text-left">
                                  <div id="suspend-label" class="text-xs font-black text-text-primary"><?= $isActive ? 'Suspend Staff' : 'Reinstate Staff' ?></div>
                                  <div class="text-[10px] font-bold text-text-secondary uppercase tracking-widest mt-0.5"><?= $isActive ? 'Revoke system access' : 'Restore system privileges' ?></div>
                              </div>
                          </div>
                          <span class="material-symbols-outlined text-text-secondary text-lg pointer-events-none" aria-hidden="true">chevron_right</span>
                      </button>

                      <button id="reset-password-btn" type="button" onclick="if(window.openGovernanceModal) window.openGovernanceModal('reset');" class="w-full flex items-center justify-between p-3.5 bg-background rounded-xl border border-border-subtle hover:border-info hover:bg-info-bg transition-all cursor-pointer group select-none relative z-30">
                          <div class="flex items-center gap-3 pointer-events-none">
                              <span class="material-symbols-outlined text-info" aria-hidden="true">lock_reset</span>
                              <div class="text-left">
                                  <div class="text-xs font-black text-text-primary">Reset Password</div>
                                  <div class="text-[10px] font-bold text-text-secondary uppercase tracking-widest mt-0.5">Issue temporary password</div>
                              </div>
                          </div>
                          <span class="material-symbols-outlined text-text-secondary text-lg pointer-events-none" aria-hidden="true">chevron_right</span>
                      </button>

                      <button id="delete-staff-btn" type="button" onclick="if(window.openGovernanceModal) window.openGovernanceModal('delete');" class="w-full flex items-center justify-between p-3.5 bg-background rounded-xl border border-border-subtle hover:border-error hover:bg-error-bg transition-all cursor-pointer group select-none relative z-30">
                          <div class="flex items-center gap-3 pointer-events-none">
                              <span class="material-symbols-outlined text-error" aria-hidden="true">person_remove</span>
                              <div class="text-left">
                                  <div class="text-xs font-black text-error">Delete Account</div>
                                  <div class="text-[10px] font-bold text-text-secondary uppercase tracking-widest mt-0.5">Soft-delete permanently</div>
                              </div>
                          </div>
                          <span class="material-symbols-outlined text-text-secondary text-lg pointer-events-none" aria-hidden="true">chevron_right</span>
                      </button>
                  </div>
              </div>

              <!-- PERFORMANCE OVERVIEW -->
              <div class="ds-card p-5">
                  <h5 class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-4 ml-1">Lifetime Financial Performance</h5>
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
                  <h5 class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-4 ml-1">Today's Shift Operations</h5>
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
                      <span>Recent Shifts History</span>
                      <span class="material-symbols-outlined text-sm" aria-hidden="true">schedule</span>
                  </h5>
                  <div id="staff-shift-history" class="space-y-2.5">
                      <?php if (empty($shifts)): ?>
                          <div class="text-xs text-text-secondary text-center py-4">No recent shift logs recorded.</div>
                      <?php else: ?>
                          <?php foreach (array_slice($shifts, 0, 5) as $s): ?>
                              <?php
                                  $lTime = !empty($s['login_time']) ? date('h:i A', strtotime($s['login_time'])) : '09:00 AM';
                                  $oTime = !empty($s['logout_time']) ? date('h:i A', strtotime($s['logout_time'])) : null;
                                  $timeDisplay = ($oTime && $oTime !== $lTime) ? "{$lTime} — {$oTime}" : "{$lTime} — 05:30 PM";
                              ?>
                              <div class="flex items-center justify-between p-3 bg-surface rounded-xl border border-border-subtle">
                                  <div>
                                      <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest"><?= date('d M Y', strtotime($s['shift_date'])) ?></div>
                                      <div class="text-xs font-bold text-text-primary mt-0.5"><?= $timeDisplay ?></div>
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
                          All staff actions, customer transactions, and security modifications are recorded in the central audit ledger and cryptographically tied to the active administrator session.
                      </p>
                  </div>
                  <span class="material-symbols-outlined absolute -right-3 -bottom-3 text-7xl text-border-subtle opacity-40" aria-hidden="true">verified_user</span>
              </div>
          </div>
      </div>

      <!-- TRANSACTION HISTORY TABLE -->
      <div class="ds-card overflow-hidden mt-6 relative z-10 shadow-lg">
          <div class="p-5 sm:p-6 border-b border-border-subtle flex flex-col sm:flex-row justify-between sm:items-center gap-4 bg-background/50">
              <div>
                  <h4 class="font-black text-lg text-text-primary tracking-tight">Staff Transaction Ledger</h4>
                  <p class="text-[10px] font-black text-text-secondary uppercase tracking-widest mt-0.5">Top 8 recent transactions processed across customer accounts</p>
              </div>
              <button id="open-passbook-modal-table-btn" type="button" onclick="if(window.openPassbookModal) window.openPassbookModal();" class="btn btn-secondary px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider inline-flex items-center gap-1.5 shadow-sm transition-all cursor-pointer">
                  <span class="material-symbols-outlined text-[16px]" aria-hidden="true">receipt_long</span>
                  <span>Passbook &amp; Statement</span>
              </button>
          </div>
          <div class="ds-table-wrapper border-0 rounded-none shadow-none bg-transparent overflow-x-auto">
              <table class="ds-table w-full text-left min-w-[900px]">
                  <thead scope="col">
                      <tr>
                          <th scope="col" class="w-[15%]">Receipt / Txn ID</th>
                          <th scope="col" class="w-[18%]">Date &amp; Time</th>
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
                          <?php foreach (array_slice($transactions, 0, 8) as $t): ?>
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
                  <h4 class="font-black text-lg text-text-primary tracking-tight">Staff Security &amp; Activity Log</h4>
                  <p class="text-[10px] font-black text-text-secondary uppercase tracking-widest mt-0.5">Logged events performed by this staff account</p>
              </div>
          </div>
          <div class="ds-table-wrapper border-0 rounded-none shadow-none bg-transparent overflow-x-auto">
              <table class="ds-table w-full text-left min-w-[800px]">
                  <thead scope="col">
                      <tr>
                          <th scope="col" class="w-[20%]">Date &amp; Time</th>
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
                                          <?= htmlspecialchars($a['action_type'] ?? 'ACTIVITY', ENT_QUOTES, 'UTF-8') ?>
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
            <h3 class="text-base font-black font-headline text-text-primary">Official Account Statement &amp; Passbook</h3>
            <p class="text-[10px] font-bold text-text-secondary uppercase tracking-wider">Authorized Banking Record</p>
          </div>
        </div>
        
        <div class="flex items-center gap-2">
          <button id="print-passbook-action" type="button" class="btn btn-primary px-3.5 py-2 rounded-xl text-xs font-bold uppercase tracking-wider inline-flex items-center gap-1.5 shadow-sm cursor-pointer">
            <span class="material-symbols-outlined text-[16px]" aria-hidden="true">print</span>
            <span>Print Passbook</span>
          </button>
          <button id="close-passbook-btn" type="button" class="w-9 h-9 rounded-xl bg-surface border border-border-subtle flex items-center justify-center text-text-secondary hover:text-text-primary transition-colors cursor-pointer">
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

          <button id="pb-apply-filter" type="button" class="btn btn-secondary px-3 py-1 text-xs font-bold rounded-lg inline-flex items-center gap-1 shadow-sm transition-all cursor-pointer">
            <span class="material-symbols-outlined text-[14px]" aria-hidden="true">filter_alt</span>
            <span>Filter</span>
          </button>
        </div>

        <div class="flex items-center gap-1.5 text-[11px]">
          <button id="pb-preset-all" type="button" class="px-2.5 py-1 rounded-md bg-surface border border-border-subtle hover:border-primary text-text-secondary hover:text-text-primary font-bold text-[10px] uppercase transition-all cursor-pointer">All Time</button>
          <button id="pb-preset-month" type="button" class="px-2.5 py-1 rounded-md bg-surface border border-border-subtle hover:border-primary text-text-secondary hover:text-text-primary font-bold text-[10px] uppercase transition-all cursor-pointer">This Month</button>
          <button id="pb-preset-30" type="button" class="px-2.5 py-1 rounded-md bg-surface border border-border-subtle hover:border-primary text-text-secondary hover:text-text-primary font-bold text-[10px] uppercase transition-all cursor-pointer">Last 30 Days</button>
          <button id="pb-preset-90" type="button" class="px-2.5 py-1 rounded-md bg-surface border border-border-subtle hover:border-primary text-text-secondary hover:text-text-primary font-bold text-[10px] uppercase transition-all cursor-pointer">Last 90 Days</button>
        </div>
      </div>

      <!-- Printable Passbook Paper Area -->
      <div class="p-4 sm:p-8 overflow-y-auto bg-slate-100 dark:bg-black/40">
        <div id="passbook-print-area" class="bg-white text-black p-6 sm:p-8 max-w-4xl mx-auto border-2 border-black font-mono shadow-sm">
          
          <!-- Bank Header -->
          <div class="border-b-2 border-black pb-3 mb-4 text-center">
            <h1 class="text-2xl font-black tracking-wider uppercase text-black font-serif">FINZO BANK</h1>
            <div class="text-xs font-bold uppercase tracking-widest text-black mt-0.5">STAFF OPERATION &amp; TRANSACTION PASSBOOK</div>
            <div class="flex justify-between items-center text-[11px] font-bold text-black border-t border-black pt-2 mt-2.5">
              <div>BRANCH: <?= !empty($staff['branch_name']) ? strtoupper($staff['branch_name']) : 'MAIN BRANCH, MUMBAI' ?></div>
              <div>IFSC: FINZ0002026</div>
              <div>MICR: 400026002</div>
            </div>
          </div>

          <!-- Account Particulars Box -->
          <table class="w-full border-collapse border border-black text-xs mb-4">
            <tbody>
              <tr class="border-b border-black">
                <td class="p-2 border-r border-black font-bold w-1/4 bg-slate-100 text-black">STAFF MEMBER</td>
                <td class="p-2 border-r border-black font-black w-1/4 text-black text-sm uppercase" id="pb-full-name"><?= strtoupper($fullName) ?></td>
                <td class="p-2 border-r border-black font-bold w-1/4 bg-slate-100 text-black">STAFF ID</td>
                <td class="p-2 font-black w-1/4 text-black text-sm" id="pb-staff-id"><?= $staffCode ?></td>
              </tr>
              <tr class="border-b border-black">
                <td class="p-2 border-r border-black font-bold bg-slate-100 text-black">DESIGNATION</td>
                <td class="p-2 border-r border-black font-bold text-black" id="pb-designation"><?= $designation ?> (<?= $department ?>)</td>
                <td class="p-2 border-r border-black font-bold bg-slate-100 text-black">BRANCH</td>
                <td class="p-2 font-bold text-black uppercase" id="pb-branch"><?= !empty($staff['branch_name']) ? $staff['branch_name'] : 'Main Branch' ?></td>
              </tr>
              <tr class="border-b border-black">
                <td class="p-2 border-r border-black font-bold bg-slate-100 text-black">MOBILE NUMBER</td>
                <td class="p-2 border-r border-black font-bold text-black" id="pb-phone"><?= htmlspecialchars($staff['phone'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="p-2 border-r border-black font-bold bg-slate-100 text-black">STATEMENT PERIOD</td>
                <td class="p-2 font-bold text-black" id="pb-generated-date"><?= date('d-m-Y') ?></td>
              </tr>
              <tr>
                <td class="p-2 border-r border-black font-bold bg-slate-100 text-black">ADDRESS</td>
                <td class="p-2 text-black font-bold" colspan="3" id="pb-address"><?= htmlspecialchars(($address['street'] ?? '') . ', ' . ($address['city'] ?? '') . ', ' . ($address['state'] ?? '') . ' - ' . ($address['pincode'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
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
                  <th class="p-2 border-r border-black">PARTICULARS / DETAILS</th>
                  <th class="p-2 border-r border-black w-28 text-right">WITHDRAWAL (DR) ₹</th>
                  <th class="p-2 border-r border-black w-28 text-right">DEPOSIT (CR) ₹</th>
                  <th class="p-2 border-r border-black w-28 text-right">BALANCE / VOL ₹</th>
                  <th class="p-2 w-14 text-center">INIT</th>
                </tr>
              </thead>
              <tbody id="pb-transactions-body" class="divide-y divide-black text-[11px] text-black">
                <tr><td colspan="7" class="p-4 text-center text-black">No transaction entries found.</td></tr>
              </tbody>
            </table>
          </div>

          <!-- Bottom Totals & Closing Balance Summary Box -->
          <table class="w-full border-collapse border-2 border-black text-xs mb-4 text-black font-mono">
            <tbody>
              <tr class="font-bold">
                <td class="p-2.5 border-r border-black w-1/3 text-center">
                  <div class="text-[10px] uppercase">TOTAL WITHDRAWALS (DR)</div>
                  <div class="text-sm font-black mt-0.5" id="pb-total-debits">₹ 0.00</div>
                </td>
                <td class="p-2.5 border-r border-black w-1/3 text-center">
                  <div class="text-[10px] uppercase">TOTAL DEPOSITS (CR)</div>
                  <div class="text-sm font-black mt-0.5" id="pb-total-credits">₹ 0.00</div>
                </td>
                <td class="p-2.5 w-1/3 text-center bg-slate-100 font-black">
                  <div class="text-[10px] uppercase">TOTAL PROCESSED VOLUME</div>
                  <div class="text-base font-black mt-0.5" id="pb-net-balance">₹ 0.00</div>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Footer Rules & Notice -->
          <div class="text-[10px] text-black leading-tight border-t border-black pt-3 font-mono">
            <p class="font-bold">* Please examine this passbook immediately. Discrepancies, if any, should be reported within 15 days.</p>
            <div class="flex justify-between items-center mt-2 text-[9px] font-bold">
              <span>* COMPUTER GENERATED PASSBOOK — NO PHYSICAL SIGNATURE REQUIRED</span>
              <span>PAGE 1 OF 1</span>
            </div>
          </div>

        </div>
      </div>

    </div>
  </div>

  <!-- GOVERNANCE ACTION MODAL -->
  <div id="governance-modal" class="fixed inset-0 z-[2600] hidden items-center justify-center p-4 bg-black/80 backdrop-blur-md overflow-y-auto" role="dialog" aria-modal="true">
      <div class="relative w-full max-w-md bg-surface rounded-3xl border border-border-subtle shadow-2xl overflow-hidden flex flex-col my-auto">
          <!-- Modal Header -->
          <div class="p-6 border-b border-border-subtle flex items-start gap-4">
              <div id="modal-icon-container" class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 bg-primary/10 text-primary border border-primary/20">
                  <span id="modal-icon" class="material-symbols-outlined text-2xl" aria-hidden="true">security</span>
              </div>
              <div class="flex-1">
                  <h3 id="modal-title" class="text-base font-black font-headline text-text-primary">Confirm Governance Action</h3>
                  <p id="modal-subtitle" class="text-xs font-bold text-text-secondary mt-0.5">Authorization Required</p>
              </div>
          </div>

          <!-- Modal Body -->
          <div class="p-6 space-y-4">
              <p id="modal-body-text" class="text-xs font-medium text-text-secondary leading-relaxed"></p>

              <!-- Reason Selection (for suspension) -->
              <div id="reason-step" class="space-y-2 hidden">
                  <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary block">Select Reason</label>
                  <div id="reason-options" class="space-y-1.5 max-h-40 overflow-y-auto"></div>
                  <div class="mt-2">
                      <input type="text" id="governance-notes" placeholder="Additional notes / details..." class="w-full px-3.5 py-2.5 bg-background border border-border-subtle rounded-xl text-xs text-text-primary" />
                  </div>
              </div>

              <!-- Admin Password Verification -->
              <div id="auth-step" class="space-y-2">
                  <label class="text-[10px] font-black uppercase tracking-widest text-text-secondary block">Admin Password Confirmation</label>
                  <input type="password" id="admin-password" placeholder="Enter your administrator password" class="w-full px-3.5 py-2.5 bg-background border border-border-subtle rounded-xl text-xs text-text-primary focus:border-primary" />
                  <div id="auth-error" class="text-xs font-bold text-error hidden"></div>
              </div>
          </div>

          <!-- Footer Actions -->
          <div class="p-6 pt-0 flex gap-3">
              <button type="button" id="abort-governance-btn" class="flex-1 py-3 bg-surface hover:bg-surface-hover text-text-primary font-bold rounded-xl text-xs uppercase tracking-wider transition-all border border-border-subtle cursor-pointer">Cancel</button>
              <button type="button" id="confirm-governance-btn" class="flex-1 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-black rounded-xl text-xs uppercase tracking-wider shadow-sm hover:scale-[1.02] active:scale-[0.98] transition-all cursor-pointer">Confirm</button>
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

  <script>
  (function() {
      var staffObj = window.__INITIAL_STAFF__ || {};
      var staffName = <?= json_encode($fullName) ?> || staffObj.full_name || 'Staff Member';

      var govConfigs = {
          reset: {
              title: 'Reset Staff Password',
              subtitle: 'Security protocol override',
              body: 'You are about to issue an emergency password reset for <strong class="text-text-primary">' + staffName + '</strong>. A secure temporary password will be generated.',
              icon: 'lock_reset',
              btnText: 'Confirm Reset',
              btnClass: 'bg-indigo-600 hover:bg-indigo-700 text-white font-black',
              reasons: []
          },
          deactivate: {
              title: 'Suspend Staff Account',
              subtitle: 'Revoke system permissions',
              body: 'You are about to suspend <strong class="text-text-primary">' + staffName + '</strong>. This will revoke branch operations and system access immediately.',
              icon: 'block',
              btnText: 'Confirm Suspension',
              btnClass: 'bg-amber-600 hover:bg-amber-700 text-white font-black',
              reasons: [
                  'Unauthorized Operations',
                  'Cash / Financial Discrepancy',
                  'Policy Violation / Misconduct',
                  'Performance & Compliance Review',
                  'Voluntary Leave / Resignation',
                  'Security Breach Suspected',
                  'Other'
              ]
          },
          reinstate: {
              title: 'Reinstate Staff Account',
              subtitle: 'Restore operational privileges',
              body: 'You are about to reinstate <strong class="text-text-primary">' + staffName + '</strong>. This will restore branch login access and active status.',
              icon: 'check_circle',
              btnText: 'Confirm Reinstatement',
              btnClass: 'bg-emerald-600 hover:bg-emerald-700 text-white font-black',
              reasons: []
          },
          delete: {
              title: 'Delete Staff Account',
              subtitle: 'Permanent soft deletion',
              body: 'You are about to <span class="text-error font-black uppercase underline">PERMANENTLY SOFT-DELETE</span> <strong class="text-text-primary">' + staffName + '</strong>. This action will archive the staff account from active lists.',
              icon: 'person_remove',
              btnText: 'Confirm Permanent Deletion',
              btnClass: 'bg-rose-600 hover:bg-rose-700 text-white font-black',
              reasons: []
          }
      };

      var activeAction = '';

      window.openGovernanceModal = function(action) {
          activeAction = action;
          var config = govConfigs[action];
          if (!config) return;

          var modal = document.getElementById('governance-modal');
          if (!modal) return;

          var titleEl = document.getElementById('modal-title');
          var subEl = document.getElementById('modal-subtitle');
          var iconEl = document.getElementById('modal-icon');
          var bodyEl = document.getElementById('modal-body-text');
          var confirmBtn = document.getElementById('confirm-governance-btn');
          var reasonStep = document.getElementById('reason-step');
          var reasonOptions = document.getElementById('reason-options');
          var pwInput = document.getElementById('admin-password');
          var authErr = document.getElementById('auth-error');

          if (titleEl) titleEl.textContent = config.title;
          if (subEl) subEl.textContent = config.subtitle;
          if (iconEl) iconEl.textContent = config.icon;
          if (bodyEl) bodyEl.innerHTML = config.body;

          if (confirmBtn) {
              confirmBtn.className = 'flex-1 py-3 font-bold rounded-xl text-xs uppercase tracking-wider shadow-sm transition-all ' + config.btnClass;
              confirmBtn.textContent = config.btnText;
              confirmBtn.disabled = false;
          }

          if (config.reasons && config.reasons.length > 0 && reasonStep) {
              var rHtml = '';
              config.reasons.forEach(function(r, idx) {
                  rHtml += '<label class="flex items-center gap-2.5 p-2.5 bg-background rounded-xl border border-border-subtle hover:border-primary cursor-pointer transition-all text-xs">' +
                           '<input type="radio" name="suspension_reason" value="' + r + '" ' + (idx === 0 ? 'checked' : '') + ' class="text-primary focus:ring-primary">' +
                           '<span class="font-bold text-text-primary text-xs">' + r + '</span>' +
                           '</label>';
              });
              if (reasonOptions) reasonOptions.innerHTML = rHtml;
              reasonStep.classList.remove('hidden');
          } else if (reasonStep) {
              reasonStep.classList.add('hidden');
          }

          if (pwInput) pwInput.value = '';
          if (authErr) authErr.classList.add('hidden');

          modal.classList.remove('hidden');
          modal.classList.add('flex');
          modal.style.display = 'flex';
          document.body.classList.add('overflow-hidden');
      };

      async function executeAction() {
          var pwInput = document.getElementById('admin-password');
          var authErr = document.getElementById('auth-error');
          var confirmBtn = document.getElementById('confirm-governance-btn');
          var password = (pwInput ? pwInput.value : '').trim();

          if (!password) {
              if (authErr) {
                  authErr.textContent = 'Admin password is required to authorize this action';
                  authErr.classList.remove('hidden');
              }
              if (pwInput) pwInput.focus();
              return;
          }

          var apiAction = '';
          var reason = null;

          if (activeAction === 'deactivate') {
              apiAction = 'suspend';
              var checkedReason = document.querySelector('input[name="suspension_reason"]:checked');
              var rVal = checkedReason ? checkedReason.value : 'Administrative Review';
              var notes = (document.getElementById('governance-notes') ? document.getElementById('governance-notes').value : '').trim();
              reason = (rVal === 'Other' && notes) ? notes : rVal;
          } else if (activeAction === 'reinstate') {
              apiAction = 'reinstate';
          } else if (activeAction === 'reset') {
              apiAction = 'reset_password';
          } else if (activeAction === 'delete') {
              apiAction = 'delete';
          }

          if (confirmBtn) {
              confirmBtn.disabled = true;
              confirmBtn.textContent = 'Authorizing...';
          }

          try {
              var csrf = window.csrfToken || (document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '') || '';
              var targetStaffId = staffObj.id || <?= json_encode($staffId) ?>;
              
              var res = await fetch('/Finzo/public/api/v1/staff/' + targetStaffId, {
                  method: 'PATCH',
                  headers: {
                      'Content-Type': 'application/json',
                      'X-CSRF-Token': csrf,
                      'Accept': 'application/json'
                  },
                  body: JSON.stringify({
                      staff_id: targetStaffId,
                      action: apiAction,
                      password: password,
                      reason: reason
                  })
              });

              var data = await res.json();

              if (!res.ok || (!data.success && data.status !== 200)) {
                  if (authErr) {
                      authErr.textContent = data.message || 'Authorization failed. Please verify your password.';
                      authErr.classList.remove('hidden');
                  }
                  if (confirmBtn) {
                      confirmBtn.disabled = false;
                      confirmBtn.textContent = 'Retry';
                  }
                  return;
              }

              var tempPassword = data.temp_password || (data.data && data.data.temp_password) || (data.payload && data.payload.temp_password) || '';
              if (apiAction === 'reset_password') {
                  var bodyText = document.getElementById('modal-body-text');
                  var titleEl = document.getElementById('modal-title');
                  var subEl = document.getElementById('modal-subtitle');
                  var iconEl = document.getElementById('modal-icon');
                  var authStep = document.getElementById('auth-step');
                  var reasonStep = document.getElementById('reason-step');
                  var abortBtn = document.getElementById('abort-governance-btn');

                  if (titleEl) titleEl.textContent = 'Password Reset Complete';
                  if (subEl) subEl.textContent = 'Temporary Credential Issued';
                  if (iconEl) iconEl.textContent = 'key';

                  if (bodyText) {
                      bodyText.innerHTML = `
                          <div class="p-4 bg-background border border-border-subtle rounded-2xl text-center space-y-3">
                              <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest">Temporary One-Time Password</div>
                              <div class="text-2xl font-black font-mono text-indigo-500 tracking-widest select-all py-2 bg-surface rounded-xl border border-indigo-500/20 shadow-inner">${tempPassword || 'Generated Successfully'}</div>
                              <div class="text-[10px] font-bold text-success uppercase tracking-wider">Valid for initial login • Copy securely</div>
                          </div>
                      `;
                  }
                  if (authStep) authStep.classList.add('hidden');
                  if (reasonStep) reasonStep.classList.add('hidden');
                  if (abortBtn) abortBtn.classList.add('hidden');
                  if (confirmBtn) {
                      confirmBtn.textContent = 'Done';
                      confirmBtn.disabled = false;
                      confirmBtn.className = 'flex-1 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-black rounded-xl text-xs uppercase tracking-wider shadow-sm transition-all cursor-pointer';
                      confirmBtn.onclick = function() {
                          window.closeGovernanceModal();
                          window.location.reload();
                      };
                  }
              } else if (apiAction === 'delete') {
                  alert('Staff account has been permanently archived.');
                  window.location.href = '/Finzo/public/views/admin/admin-staff.php';
              } else {
                  if (window.showToast) {
                      window.showToast(data.message || 'Action executed successfully', 'success');
                  }
                  window.closeGovernanceModal();
                  window.location.reload();
              }

          } catch (err) {
              if (authErr) {
                  authErr.textContent = err.message || 'Network error executing action';
                  authErr.classList.remove('hidden');
              }
              if (confirmBtn) {
                  confirmBtn.disabled = false;
                  confirmBtn.textContent = 'Retry';
              }
          }
      }

      window.closeGovernanceModal = function() {
          var modal = document.getElementById('governance-modal');
          if (!modal) return;
          modal.classList.add('hidden');
          modal.classList.remove('flex');
          modal.style.display = 'none';
          document.body.classList.remove('overflow-hidden');
      };

      // PASSBOOK MODAL CONTROLLER
      var allStaffTxns = <?= json_encode($transactions ?? []) ?>;
      var passbookFilter = { from: null, to: null, label: 'ALL TIME' };

      function renderPassbook() {
          var tbody = document.getElementById('pb-transactions-body');
          if (!tbody) return;

          var txns = allStaffTxns || [];
          if (passbookFilter.from || passbookFilter.to) {
              txns = txns.filter(function(t) {
                  if (!t.created_at) return true;
                  var tDate = t.created_at.slice(0, 10);
                  if (passbookFilter.from && tDate < passbookFilter.from) return false;
                  if (passbookFilter.to && tDate > passbookFilter.to) return false;
                  return true;
              });
          }

          var periodStr = 'ALL TIME (AS OF ' + new Date().toLocaleDateString('en-GB') + ')';
          if (passbookFilter.from && passbookFilter.to) {
              periodStr = passbookFilter.from + ' TO ' + passbookFilter.to;
          } else if (passbookFilter.from) {
              periodStr = 'SINCE ' + passbookFilter.from;
          } else if (passbookFilter.to) {
              periodStr = 'UP TO ' + passbookFilter.to;
          }
          var pGen = document.getElementById('pb-generated-date');
          if (pGen) pGen.textContent = periodStr;

          var totalCredits = 0;
          var totalDebits = 0;
          var totalVolume = 0;

          if (txns.length === 0) {
              tbody.innerHTML = '<tr><td colspan="7" class="p-4 text-center text-black font-bold">No transaction entries found for the selected period.</td></tr>';
          } else {
              var html = '';
              txns.forEach(function(t, idx) {
                  var isDeposit = (t.type === 'deposit');
                  var amt = parseFloat(t.amount || 0);
                  if (isDeposit) totalCredits += amt;
                  else totalDebits += amt;
                  totalVolume += amt;

                  var lineNum = String(idx + 1).padStart(2, '0');
                  var dateStr = t.created_at ? new Date(t.created_at).toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' }) : 'N/A';
                  var particulars = ((t.customer_name || 'Customer') + ' / ' + (t.txn_id || 'TXN')).toUpperCase();
                  var debitStr = !isDeposit ? amt.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '-';
                  var creditStr = isDeposit ? amt.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '-';
                  var volStr = amt.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                  html += `
                      <tr class="border-b border-black">
                          <td class="p-2 border-r border-black text-center font-bold">${lineNum}</td>
                          <td class="p-2 border-r border-black text-center whitespace-nowrap">${dateStr}</td>
                          <td class="p-2 border-r border-black uppercase font-bold truncate max-w-[200px]">${particulars}</td>
                          <td class="p-2 border-r border-black text-right font-mono">${debitStr}</td>
                          <td class="p-2 border-r border-black text-right font-mono">${creditStr}</td>
                          <td class="p-2 border-r border-black text-right font-black font-mono">${volStr}</td>
                          <td class="p-2 text-center font-bold">SYS</td>
                      </tr>
                  `;
              });
              tbody.innerHTML = html;
          }

          var elCred = document.getElementById('pb-total-credits');
          var elDeb = document.getElementById('pb-total-debits');
          var elBal = document.getElementById('pb-net-balance');

          if (elCred) elCred.textContent = '₹ ' + totalCredits.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
          if (elDeb) elDeb.textContent = '₹ ' + totalDebits.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
          if (elBal) elBal.textContent = '₹ ' + totalVolume.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      }

      window.openPassbookModal = function() {
          passbookFilter = { from: null, to: null, label: 'ALL TIME' };
          var fromInput = document.getElementById('pb-filter-from');
          var toInput = document.getElementById('pb-filter-to');
          if (fromInput) fromInput.value = '';
          if (toInput) toInput.value = '';

          renderPassbook();

          var modal = document.getElementById('passbook-modal');
          if (modal) {
              modal.classList.remove('hidden');
              modal.classList.add('flex');
              modal.style.display = 'flex';
              document.body.classList.add('overflow-hidden');
          }
      };

      window.closePassbookModal = function() {
          var modal = document.getElementById('passbook-modal');
          if (modal) {
              modal.classList.add('hidden');
              modal.classList.remove('flex');
              modal.style.display = 'none';
              document.body.classList.remove('overflow-hidden');
          }
      };

      function printPassbook() {
          var printArea = document.getElementById('passbook-print-area');
          if (!printArea) return;

          var printHtml = `
              <!DOCTYPE html>
              <html lang="en">
              <head>
                  <meta charset="UTF-8">
                  <title>Staff Passbook - <?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?></title>
                  <style>
                      @page { size: A4 portrait; margin: 12mm; }
                      * { box-sizing: border-box; margin: 0; padding: 0; }
                      body { font-family: "Courier New", Courier, monospace; color: #000; background: #fff; font-size: 11px; }
                      table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
                      th, td { border: 1px solid #000; padding: 5px 6px; }
                      th { background-color: #f2f2f2; font-weight: 900; text-transform: uppercase; font-size: 10px; }
                  </style>
              </head>
              <body>
                  <div style="border: 2px solid #000; padding: 14px 16px;">
                      ${printArea.innerHTML}
                  </div>
              </body>
              </html>
          `;

          var printFrame = document.getElementById('finzo-staff-inline-print-iframe');
          if (!printFrame) {
              printFrame = document.createElement('iframe');
              printFrame.id = 'finzo-staff-inline-print-iframe';
              printFrame.style.position = 'fixed';
              printFrame.style.right = '0';
              printFrame.style.bottom = '0';
              printFrame.style.width = '0';
              printFrame.style.height = '0';
              printFrame.style.border = '0';
              document.body.appendChild(printFrame);
          }

          var doc = printFrame.contentWindow || printFrame.contentDocument.document || printFrame.contentDocument;
          doc.document.open();
          doc.document.write(printHtml);
          doc.document.close();

          setTimeout(function() {
              printFrame.contentWindow.focus();
              printFrame.contentWindow.print();
          }, 350);
      }

      function setPassbookPreset(preset) {
          var fromInput = document.getElementById('pb-filter-from');
          var toInput = document.getElementById('pb-filter-to');
          var today = new Date();
          var formatDate = function(d) { return d.toISOString().slice(0, 10); };

          if (preset === 'all') {
              passbookFilter = { from: null, to: null, label: 'ALL TIME' };
              if (fromInput) fromInput.value = '';
              if (toInput) toInput.value = '';
          } else if (preset === 'month') {
              var firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
              passbookFilter = { from: formatDate(firstDay), to: formatDate(today), label: 'THIS MONTH' };
              if (fromInput) fromInput.value = passbookFilter.from;
              if (toInput) toInput.value = passbookFilter.to;
          } else if (preset === '30') {
              var d = new Date();
              d.setDate(d.getDate() - 30);
              passbookFilter = { from: formatDate(d), to: formatDate(today), label: 'LAST 30 DAYS' };
              if (fromInput) fromInput.value = passbookFilter.from;
              if (toInput) toInput.value = passbookFilter.to;
          } else if (preset === '90') {
              var d = new Date();
              d.setDate(d.getDate() - 90);
              passbookFilter = { from: formatDate(d), to: formatDate(today), label: 'LAST 90 DAYS' };
              if (fromInput) fromInput.value = passbookFilter.from;
              if (toInput) toInput.value = passbookFilter.to;
          }

          renderPassbook();
      }

      document.addEventListener('click', function(e) {
          var suspendBtn = e.target.closest('#deactivate-staff-btn');
          if (suspendBtn) {
              e.preventDefault();
              window.openGovernanceModal(suspendBtn.dataset.action || 'deactivate');
              return;
          }
          var resetBtn = e.target.closest('#reset-password-btn');
          if (resetBtn) {
              e.preventDefault();
              window.openGovernanceModal('reset');
              return;
          }
          var deleteBtn = e.target.closest('#delete-staff-btn');
          if (deleteBtn) {
              e.preventDefault();
              window.openGovernanceModal('delete');
              return;
          }
          var abortBtn = e.target.closest('#abort-governance-btn');
          if (abortBtn) {
              e.preventDefault();
              window.closeGovernanceModal();
              return;
          }
          var confirmGovBtn = e.target.closest('#confirm-governance-btn');
          if (confirmGovBtn) {
              e.preventDefault();
              executeAction();
              return;
          }
          var govModal = document.getElementById('governance-modal');
          if (govModal && e.target === govModal) {
              window.closeGovernanceModal();
              return;
          }

          // Passbook Event Delegation
          var pbOpenBtn = e.target.closest('#open-passbook-modal-table-btn') || e.target.closest('#open-passbook-modal-btn');
          if (pbOpenBtn) {
              e.preventDefault();
              window.openPassbookModal();
              return;
          }
          var pbCloseBtn = e.target.closest('#close-passbook-btn');
          if (pbCloseBtn) {
              e.preventDefault();
              window.closePassbookModal();
              return;
          }
          var pbPrintBtn = e.target.closest('#print-passbook-action');
          if (pbPrintBtn) {
              e.preventDefault();
              printPassbook();
              return;
          }
          var pbFilterBtn = e.target.closest('#pb-apply-filter');
          if (pbFilterBtn) {
              e.preventDefault();
              var fVal = document.getElementById('pb-filter-from')?.value || null;
              var tVal = document.getElementById('pb-filter-to')?.value || null;
              passbookFilter = { from: fVal, to: tVal, label: 'CUSTOM' };
              renderPassbook();
              return;
          }
          var pAll = e.target.closest('#pb-preset-all');
          if (pAll) { e.preventDefault(); setPassbookPreset('all'); return; }
          var pMonth = e.target.closest('#pb-preset-month');
          if (pMonth) { e.preventDefault(); setPassbookPreset('month'); return; }
          var p30 = e.target.closest('#pb-preset-30');
          if (p30) { e.preventDefault(); setPassbookPreset('30'); return; }
          var p90 = e.target.closest('#pb-preset-90');
          if (p90) { e.preventDefault(); setPassbookPreset('90'); return; }

          var pbModal = document.getElementById('passbook-modal');
          if (pbModal && e.target === pbModal) {
              window.closePassbookModal();
              return;
          }
      });

      document.addEventListener('keydown', function(e) {
          if (e.key === 'Escape') {
              window.closeGovernanceModal();
              window.closePassbookModal();
          } else if (e.key === 'Enter') {
              var pw = document.getElementById('admin-password');
              var modal = document.getElementById('governance-modal');
              if (modal && !modal.classList.contains('hidden') && document.activeElement === pw) {
                  e.preventDefault();
                  executeAction();
              }
          }
      });
  })();
  </script>
<?php
$extraScripts = '<script src="/Finzo/public/assets/js/admin/staff-detail.js?v=' . time() . '"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>