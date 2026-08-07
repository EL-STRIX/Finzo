<?php
$portal = 'user';
$pageTitle = 'Change Password';
ob_start();
?>
<div class="auth-split-grid">
    
    <!-- Left Information Panel -->
    <div class="auth-left-panel">
      <!-- Top: Branding -->
      <div>
        <div class="flex items-center gap-2 mb-8">
          <div class="flex items-center justify-center w-10 h-10 bg-white/10 rounded-md text-white">
            <span class="material-symbols-outlined text-2xl" aria-hidden="true">account_balance</span>
          </div>
          <span class="font-display font-bold text-xl text-white tracking-tight">Finzo</span>
        </div>
        
        <h2 class="font-display text-4xl font-bold text-white mb-4 leading-tight">
          Password Security
        </h2>
        <p class="text-white/80 text-sm leading-relaxed mb-10 max-w-md">
          Protect your account with a strong password. Regular password updates improve account security.
        </p>

        <!-- Security Features -->
        <div class="space-y-2">
          <div class="auth-security-block">
            <div class="auth-security-icon">
              <span class="material-symbols-outlined text-lg" aria-hidden="true">enhanced_encryption</span>
            </div>
            <div class="auth-security-content">
              <h4>Encrypted Credentials</h4>
              <p>Your credentials are securely protected.</p>
            </div>
          </div>
          
          <div class="auth-security-block">
            <div class="auth-security-icon">
              <span class="material-symbols-outlined text-lg" aria-hidden="true">security</span>
            </div>
            <div class="auth-security-content">
              <h4>Fraud Protection</h4>
              <p>Strong authentication helps prevent fraud.</p>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Bottom: Copyright -->
      <div class="mt-8 pt-6 border-t border-white/10">
        <p class="text-xs text-white/50 font-medium tracking-wide uppercase">
          &copy; 2026 Finzo Banking Group. All rights reserved.
        </p>
      </div>
    </div>
    
    <!-- Right Authentication Area -->
    <div class="auth-right-area">
      <!-- Language Selector -->
      <div class="auth-lang-selector">
        <span class="material-symbols-outlined text-base" aria-hidden="true">language</span>
        <span>EN</span>
        <span class="material-symbols-outlined text-base" aria-hidden="true">expand_more</span>
      </div>
      
      <!-- Auth Card -->
      <div class="ds-card-wrapper w-full max-w-[480px]">
        <!-- Brand Header (Mobile Only) -->
        <div class="auth-brand-header lg:hidden">
          <div class="auth-brand-logo-container cursor-pointer" data-href="/Finzo/public/index.php">
            <span class="material-symbols-outlined text-2xl" aria-hidden="true">account_balance</span>
          </div>
        </div>
        
        <div class="mb-6 text-center lg:text-left">
          <h1 class="auth-hero-title">Change Your Password</h1>
          <p class="auth-subtitle text-sm">Update your password to keep your Finzo Banking account secure.</p>
        </div>

        <form class="auth-form-body" onsubmit="event.preventDefault();">
          
          <div class="fin-input-container !mb-0">
              <label class="form-label" for="current-pw">Current Password</label>
              <div class="relative mt-2">
                  <span class="material-symbols-outlined input-icon" aria-hidden="true">lock</span>
                  <input id="current-pw" class="fin-input has-icon-left has-icon-right" placeholder="Enter current password" type="password" required aria-required="true" />
                  <button type="button" class="absolute right-4 top-1/2 -translate-y-1/2 text-text-muted hover:text-primary-base-600 transition-colors focus:outline-none focus:text-primary-base-600" aria-label="Toggle password visibility" data-action="togglePw('current-pw','eye1')">
                      <span class="material-symbols-outlined text-xl" id="eye1" aria-hidden="true">visibility</span>
                  </button>
              </div>
          </div>
          
          <div class="fin-input-container !mb-0 mt-5">
              <label class="form-label" for="new-pw">New Password</label>
              <div class="relative mt-2">
                  <span class="material-symbols-outlined input-icon" aria-hidden="true">lock_open</span>
                  <input id="new-pw" class="fin-input has-icon-left has-icon-right" placeholder="Min 8 characters" type="password" required aria-required="true" oninput="checkStrength()" />
                  <button type="button" class="absolute right-4 top-1/2 -translate-y-1/2 text-text-muted hover:text-primary-base-600 transition-colors focus:outline-none focus:text-primary-base-600" aria-label="Toggle password visibility" data-action="togglePw('new-pw','eye2')">
                      <span class="material-symbols-outlined text-xl" id="eye2" aria-hidden="true">visibility</span>
                  </button>
              </div>
              
              <!-- Original password strength bar structure preserved with auth sizing -->
              <div class="w-full h-1.5 bg-surface-hover rounded-full overflow-hidden mt-3">
                  <div id="strength-fill" class="h-full bg-danger-base transition-all duration-300" style="width: 0%"></div>
              </div>
              <div class="flex justify-between items-center mt-1 mb-2">
                  <span class="text-xs font-bold text-text-muted uppercase tracking-wider">Password Strength</span>
                  <span class="text-xs font-bold text-text-muted" id="strength-label"></span>
              </div>
          </div>

          <div class="fin-input-container !mb-0 mt-3">
              <label class="form-label" for="confirm-pw">Confirm New Password</label>
              <div class="relative mt-2">
                  <span class="material-symbols-outlined input-icon" aria-hidden="true">lock_reset</span>
                  <input id="confirm-pw" class="fin-input has-icon-left has-icon-right" placeholder="Re-enter new password" type="password" required aria-required="true" oninput="checkMatch()" />
                  <button type="button" class="absolute right-4 top-1/2 -translate-y-1/2 text-text-muted hover:text-primary-base-600 transition-colors focus:outline-none focus:text-primary-base-600" aria-label="Toggle password visibility" data-action="togglePw('confirm-pw','eye3')">
                      <span class="material-symbols-outlined text-xl" id="eye3" aria-hidden="true">visibility</span>
                  </button>
              </div>
              <div id="match-error" class="hidden text-xs font-semibold text-danger-base mt-2 tracking-wide uppercase" aria-live="polite"><span class="material-symbols-outlined text-xs align-text-bottom" aria-hidden="true">error</span> Passwords do not match</div>
          </div>

          <!-- Password requirements based on original text -->
          <div class="p-3 bg-info-surface/40 border border-info-base/20 rounded-md flex items-start gap-2 mt-5">
              <span class="material-symbols-outlined text-info-base text-base shrink-0" aria-hidden="true">info</span>
              <div class="text-xs text-text-muted font-medium leading-relaxed">
                  Password must be at least 8 characters long and contain at least one uppercase letter, one number, and one special character.
              </div>
          </div>

          <div class="pt-5">
              <button class="btn btn-primary w-full" id="update-btn" data-action="updatePassword()">
                  <span>Update Credentials</span>
                  <span class="material-symbols-outlined text-xl" aria-hidden="true">update</span>
              </button>
          </div>

        </form>

        <div class="auth-trust-badge mt-6 border-t border-surface-border pt-6">
          <span class="material-symbols-outlined text-sm" aria-hidden="true">verified_user</span>
          <span>Enterprise Security Level</span>
        </div>

        <div class="auth-footer">
            <a href="profile.php" class="flex items-center justify-center gap-1 font-semibold text-text-muted hover:text-primary-base-800 transition-colors">
                <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_back</span>
                Return to Profile
            </a>
        </div>
      </div>

            <!-- Global Footer -->
      <div class="auth-global-footer">
        <ul>
          <li><span class="flex items-center gap-1"><span class="material-symbols-outlined text-xs" aria-hidden="true">enhanced_encryption</span> 256-bit Encryption</span></li>
          <li><span class="flex items-center gap-1"><span class="material-symbols-outlined text-xs" aria-hidden="true">verified_user</span> Secure Digital Banking Access</span></li>
          <li><span class="flex items-center gap-1"><span class="material-symbols-outlined text-xs" aria-hidden="true">gpp_good</span> Privacy Protected</span></li>
          <li><span class="flex items-center gap-1"><span class="material-symbols-outlined text-xs" aria-hidden="true">credit_card</span> PCI DSS Compliant</span></li>
        </ul>
      </div>
    </div>
  </div>
<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>