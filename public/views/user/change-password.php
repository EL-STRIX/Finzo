<?php
$portal = 'user';
$pageTitle = 'Change Password';
ob_start();
?>
<script defer src="/Finzo/public/assets/js/user/user-shared.js?v=3"></script>

<div class="mb-8 ds-animate-fade">
    <div class="text-sm font-bold text-text-muted uppercase tracking-wider mb-1">Security</div>
    <h1 class="font-display text-3xl font-extrabold text-text-main tracking-tight">Change Password</h1>
    <p class="text-text-muted mt-1">Update your password to keep your Finzo Banking account secure.</p>
</div>

<div class="ds-card p-6 lg:p-8 max-w-2xl ds-animate-slide-up">
    <form class="" onsubmit="event.preventDefault();">
          
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
</div>

<script defer src="/Finzo/public/assets/js/user/change-password.js"></script>

<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>