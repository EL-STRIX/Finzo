let CSRF_TOKEN = '';
  let CURRENT_EMAIL = '';
  let RESET_TOKEN = '';
  let countdownTimer = null;
  let autoUnlockInterval = null;

  function showToast(msg) {
    const $t = $('<div class="toast">' + msg + '</div>');
    $('#toast-container').append($t);
    setTimeout(() => { $t.remove(); }, 3500);
  }

  function goToState(stateNum) {
    $('.app-state').removeClass('active');
    setTimeout(() => {
      $('#state-' + stateNum).addClass('active');
    }, 50);
  }

  function togglePw(inputId, iconElem) {
    const p = $('#' + inputId);
    const isPw = p.attr('type') === 'password';
    p.attr('type', isPw ? 'text' : 'password');
    $(iconElem).text(isPw ? 'visibility_off' : 'visibility');
  }

  // Countdown logic
  function startCountdown(minutes, $msgElem, $btnElem) {
    if (countdownTimer) clearInterval(countdownTimer);
    let seconds = minutes * 60;
    
    $btnElem.prop('disabled', true).addClass('disabled');
    $msgElem.show();

    function update() {
      if (seconds <= 0) {
        clearInterval(countdownTimer);
        $msgElem.hide();
        $btnElem.prop('disabled', false).removeClass('disabled');
        return;
      }
      const m = Math.floor(seconds / 60);
      const s = seconds % 60;
      $msgElem.text('Try again in ' + m + ':' + (s < 10 ? '0'+s : s));
      seconds--;
    }
    update();
    countdownTimer = setInterval(update, 1000);
  }

  $(function() {
    // Initial fetch of CSRF token
    const $loginBtn = $('#login-btn');
    $loginBtn.prop('disabled', true).addClass('opacity-50 cursor-not-allowed');

    function refreshCsrfToken() {
      return fetch('/Finzo/api/auth/get-csrf-token.php?v=' + Date.now())
        .then(res => res.json())
        .then(data => { 
          if(data.csrf_token) {
            CSRF_TOKEN = data.csrf_token; 
            $loginBtn.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
            console.log("CSRF Token initialized.");
          } else {
            console.error("Token missing in response");
          }
        })
        .catch(err => {
          console.error('Token fetch error:', err);
          showToast("Failed to initialize security session. Please refresh the page.", "error");
        });
    }

    refreshCsrfToken();

    // Handle URL params
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('suspended') === '1') {
      $('#suspended-box').removeClass('hidden');
    }
    if (urlParams.get('unlocked') === '1') {
      showToast("Your account has been unlocked. You can now login normally.");
    }
    if (urlParams.get('locked') === '1') {
      const em = urlParams.get('email');
      if (em) {
        $('#identity').val(em);
        CURRENT_EMAIL = em;
        $('#locked-email-display').text(em);
        goToState(2);
        checkLockStatus(em);
      }
    }

    // Input animations
    $('.fin-input').on('input focus blur', function() {
      const $icon = $(this).siblings('.input-icon');
      if ($(this).val().trim().length > 0) {
        $icon.addClass('icon-up');
      } else {
        $icon.removeClass('icon-up');
      }
    });

    // ==========================================
    // STATE 1: LOGIN FORM
    // ==========================================
    const $loginForm = $('#adminLoginForm');
    const $loginError = $('#error-box');

    // Password visibility toggle
    $('#toggle-password').on('click', function() {
      const $input = $('#password');
      const type = $input.attr('type') === 'password' ? 'text' : 'password';
      $input.attr('type', type);
      $(this).text(type === 'password' ? 'visibility' : 'visibility_off');
    });

    $('#admin-forgot-pw').on('click', function() {
      const email = $('#identity').val().trim();
      const $msg = $('#forgot-pw-msg');
      $msg.hide();

      if (!email) {
        $msg.text("Enter your email address first").show();
        return;
      }

      // Check lock status
      fetch('/Finzo/api/admin/check-lock-status.php?email=' + encodeURIComponent(email))
        .then(res => res.json())
        .then(data => {
          if (data.found && data.locked) {
            CURRENT_EMAIL = email;
            $('#locked-email-display').text(email);
            goToState(2);
            setupLockedCard(data);
          } else {
            $msg.html("To reset your password, your account must be locked first.<br>Contact your Super Admin for assistance.").show();
          }
        })
        .catch(() => {
          $msg.text("Error checking account status").show();
        });
    });

    $loginForm.on('submit', function(e) {
      e.preventDefault();
      const id = $('#identity').val().trim();
      const pw = $('#password').val().trim();

      $loginForm.removeClass('error-state');
      $loginError.hide();

      if (!id || !pw) {
        $loginForm.addClass('error-state');
        $loginError.text("Credentials required").show();
        return;
      }

      CURRENT_EMAIL = id;
      const original = $loginBtn.html();
      $loginBtn.html('<span class="material-symbols-outlined animate-spin">progress_activity</span><span>Authenticating...</span>').prop('disabled', true);

      fetch('/Finzo/api/auth/admin-login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'email=' + encodeURIComponent(id) + '&password=' + encodeURIComponent(pw) + '&csrf_token=' + encodeURIComponent(CSRF_TOKEN)
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          window.location.href = data.redirect || '/Finzo/views/admin/admin-dashboard.html';
        } else if (data.locked) {
          $('#locked-email-display').text(id);
          setupLockedCard(data);
          goToState(2);
          $loginBtn.html(original).prop('disabled', false);
        } else {
          $loginForm.addClass('error-state');
          $loginError.text(data.message || "Invalid credentials").show();
          $loginBtn.html(original).prop('disabled', false);

          // If CSRF error, automatically refresh token for next try
          if (data.message && data.message.includes('CSRF')) {
            refreshCsrfToken();
          }
        }
      })
      .catch(err => {
        console.error('Admin login error:', err);
        $loginForm.addClass('error-state');
        $loginError.text("Connection error. Please try again.").show();
        $loginBtn.html(original).prop('disabled', false);
      });
    });

    function checkLockStatus(email) {
      fetch('/Finzo/api/admin/check-lock-status.php?email=' + encodeURIComponent(email))
        .then(res => res.json())
        .then(data => {
          if (data.found && data.locked) {
            $('#locked-email-display').text(email);
            setupLockedCard(data);
          }
        });
    }

    function setupLockedCard(data) {
      const $timerCard = $('#auto-unlock-card');
      const $countdownDisplay = $('#countdown-display');
      const $countdownFooter = $('#countdown-footer');
      const $recoveryOptions = $('#step-options');
      const $sqMsg = $('#sq-message');

      // 1. Reset Dashboard to Step 1
      resetRecoveryDashboard();

      // 2. Handle Auto-Unlock Timer
      if (autoUnlockInterval) clearInterval(autoUnlockInterval);
      
      if (data.auto_unlock_at) {
        // Cross-browser date parsing (replace - with / and space with T or keep as space)
        const until = new Date(data.auto_unlock_at.replace(/-/g, '/')).getTime();
        const now = new Date().getTime();
        let remaining = Math.max(0, Math.floor((until - now) / 1000));

        if (remaining <= 0) {
          $timerCard.removeClass('border-amber-100 bg-amber-50/30').addClass('border-teal-100 bg-teal-50/30');
          $countdownDisplay.text("00:00").removeClass('text-primary-600-600').addClass('text-teal-600');
          $countdownFooter.html('<span class="text-teal-600">✓ Account Restored Automatically</span>').addClass('text-teal-600');
          $recoveryOptions.hide();
        } else {
          $timerCard.removeClass('border-teal-100 bg-teal-50/30').addClass('border-amber-100 bg-amber-50/30');
          $countdownDisplay.addClass('text-primary-600-600').removeClass('text-teal-600');
          $countdownFooter.text("Account restores automatically after 30 minutes").removeClass('text-teal-600');
          $recoveryOptions.show();
          startAutoUnlockCountdown(remaining);
        }
      }

      // 3. Handle SQ Card State
      const $optCard = $recoveryOptions.find('.option-card');
      if (data.security_question_set === false) {
        $optCard.addClass('opacity-50 pointer-events-none');
        const superMsg = data.admin_role === 'super_admin' 
          ? "Recovery Disabled: No security question set."
          : "Recovery Disabled: Contact Super Admin.";
        $sqMsg.text(superMsg).removeClass('hidden').show();
      } else if (data.answer_locked === true) {
        $optCard.addClass('opacity-50 pointer-events-none');
        let mins = 10;
        if (data.answer_locked_until) {
          const until = new Date(data.answer_locked_until.replace(' ', 'T')).getTime();
          const now = new Date().getTime();
          mins = Math.max(1, Math.ceil((until - now) / 60000));
        }
        $sqMsg.text(`Verification locked. Retry in ${mins}m`).removeClass('hidden').show();
      } else {
        $optCard.removeClass('opacity-50 pointer-events-none');
        $sqMsg.hide();
      }
    }

    function startAutoUnlockCountdown(seconds) {
      const $display = $('#countdown-display');
      const $footer = $('#countdown-footer');
      const $card = $('#auto-unlock-card');
      const $options = $('#step-options');

      function update() {
        if (seconds <= 0) {
          clearInterval(autoUnlockInterval);
          $card.removeClass('border-amber-100 bg-amber-50/30').addClass('border-teal-100 bg-teal-50/30');
          $display.text("00:00").removeClass('text-primary-600-600').addClass('text-teal-600');
          $footer.html('<span class="text-teal-600 font-black uppercase tracking-widest">✓ Account Unlocked</span>').addClass('text-teal-600');
          $options.fadeOut();
          return;
        }

        const m = Math.floor(seconds / 60);
        const s = seconds % 60;
        $display.text(`${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`);
        seconds--;
      }

      update();
      autoUnlockInterval = setInterval(update, 1000);
    }

    // DASHBOARD STEP CONTROLLER
    window.startRecoveryStep = function(step) {
      if (step === 'question') {
        const $btn = $('.option-card');
        const original = $btn.find('h3').text();
        $btn.find('h3').text('Checking security status...');

        fetch('/Finzo/api/admin/get-security-question.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'email=' + encodeURIComponent(CURRENT_EMAIL) + '&csrf_token=' + encodeURIComponent(CSRF_TOKEN)
        })
        .then(res => res.json())
        .then(data => {
          $btn.find('h3').text(original);
          if (data.success) {
            $('#sq-question-text').text(`"${data.question}"`);
            showSubStep('step-question');
            $('#recovery-icon').text('verified_user');
            $('#recovery-title').text('Identity Verification');
            $('#recovery-subtitle').text('Please confirm your security answer');
          } else {
            showToast(data.message || "Error fetching question", "error");
          }
        });
      }
    };

    window.resetRecoveryDashboard = function() {
      showSubStep('step-options');
      $('#recovery-icon').text('lock');
      $('#recovery-title').text('Account Locked');
      $('#recovery-subtitle').text('System access restricted due to security protocols.');
      $('#sq_answer').val('');
      $('#sq-error-box').hide();
    };

    function showSubStep(stepId) {
      $('.recovery-step').addClass('hidden').removeClass('active');
      $(`#${stepId}`).removeClass('hidden').addClass('active');
    }

    // ==========================================
    // RECOVERY FORM HANDLERS
    // ==========================================
    $('#sqForm').on('submit', function(e) {
      e.preventDefault();
      const ans = $('#sq_answer').val().trim();
      const $btn = $('#btn-verify-answer');
      const $error = $('#sq-error-box');
      
      if (!ans) return;

      const original = $btn.html();
      $btn.html('<span class="material-symbols-outlined animate-spin text-[16px]">progress_activity</span>').prop('disabled', true);

      fetch('/Finzo/api/admin/verify-security-answer.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'email=' + encodeURIComponent(CURRENT_EMAIL) + '&security_answer=' + encodeURIComponent(ans) + '&csrf_token=' + encodeURIComponent(CSRF_TOKEN)
      })
      .then(res => res.json())
      .then(data => {
        $btn.html(original).prop('disabled', false);
        if (data.success) {
          RESET_TOKEN = data.reset_token;
          showSubStep('step-password');
          $('#recovery-icon').text('key_visualizer');
          $('#recovery-title').text('Reset Password');
          $('#recovery-subtitle').text('Create a secure new password for your account');
        } else {
          $error.text(data.message).show();
          if (data.message.match(/(\d+)\sattempt/)) {
            const match = data.message.match(/(\d+)\sattempt/);
            $('#sq-attempts-count').text(5 - parseInt(match[1]));
            $('#sq-attempts-warning').removeClass('hidden');
          }
          if (data.answer_locked) {
            setupLockedCard({answer_locked: true}); 
          }
        }
      });
    });

    // PASSWORD STRENGTH LOGIC
    function updateStrength() {
      const pw = $('#new_password').val();
      const conf = $('#confirm_password').val();
      const $btn = $('#btn-update-pw');
      
      let reqs = { len: pw.length >= 8, up: /[A-Z]/.test(pw), num: /[0-9]/.test(pw), sp: /[!@#$%^&*]/.test(pw) };
      let strength = 0;
      if (reqs.len) strength++;
      if (reqs.len && reqs.up && reqs.num) strength++;
      if (reqs.len && reqs.up && reqs.num && reqs.sp) strength++;

      const colors = ['#ef4444', '#f59e0b', '#10b981', '#059669'];
      const labels = ['Insecure', 'Basic', 'Secure', 'Very Strong'];
      
      $('.strength-segment').css('background', 'rgba(226, 232, 240, 0.5)');
      if (pw.length > 0) {
        let count = strength + 1;
        for (let i=1; i<=count; i++) $(`#seg-${i}`).css('background', colors[strength]);
        $('#strength-text').text(`Security: ${count * 25}%`).css('color', colors[strength]);
        $('#strength-label').text(labels[strength]).css('color', colors[strength]);
      } else {
        $('#strength-text').text('Security: 0%').css('color', '');
        $('#strength-label').text('Insecure').css('color', '');
      }

      const match = pw === conf && pw !== '';
      $('#match-indicator').toggleClass('hidden', conf === '');
      $('#match-indicator span').text(match ? 'check_circle' : 'cancel')
                                .toggleClass('text-primary-600-600', match)
                                .toggleClass('text-rose-500', !match);

      $btn.prop('disabled', !(strength === 3 && match));
    }

    $('#new_password, #confirm_password').on('input', updateStrength);

    $('#newPwForm').on('submit', function(e) {
      e.preventDefault();
      const pw = $('#new_password').val();
      const conf = $('#confirm_password').val();
      const $btn = $('#btn-update-pw');
      const $error = $('#npw-error-box');

      const original = $btn.html();
      $btn.html('<span class="material-symbols-outlined animate-spin text-[16px]">progress_activity</span>').prop('disabled', true);

      fetch('/Finzo/api/admin/reset-password-unlock.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'reset_token=' + encodeURIComponent(RESET_TOKEN) + '&new_password=' + encodeURIComponent(pw) + '&confirm_password=' + encodeURIComponent(conf) + '&csrf_token=' + encodeURIComponent(CSRF_TOKEN)
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          showToast("Account Unlocked Successfully!", "success");
          setTimeout(() => window.location.href = 'admin-login.html?unlocked=1', 1500);
        } else {
          $btn.html(original).prop('disabled', false);
          $error.text(data.message).show();
        }
      });
    });

  });