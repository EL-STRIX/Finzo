$(function() {

    const $form = $('#loginForm');
    const $btn = $('#login-btn');
    const $error = $('#error-box');
    let CSRF_TOKEN = '';

    // Fetch CSRF Token on load
    fetch('/Finzo/api/auth/get-csrf-token.php')
      .then(res => res.json())
      .then(data => { if(data.csrf_token) CSRF_TOKEN = data.csrf_token; })
      .catch(err => console.error('Token fetch error:', err));

    $form.on('submit', function(e) {
      e.preventDefault();
      const id = $('#identity').val().trim();
      const pw = $('#password').val().trim();

      $form.removeClass('error-state');
      $error.hide();

      if (!id || !pw) {
        $form.addClass('error-state');
        $error.text("Please enter your credentials").show();
        return;
      }

      const original = $btn.html();
      $btn.html('<span class="material-symbols-outlined animate-spin">progress_activity</span><span>Signing in...</span>');
      $btn.prop('disabled', true);

      // Real API authentication
      fetch('/Finzo/api/auth/login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        credentials: 'same-origin',
        body: 'identity=' + encodeURIComponent(id) + '&password=' + encodeURIComponent(pw) + '&csrf_token=' + encodeURIComponent(CSRF_TOKEN)
      })
      .then(res => {
        if (!res.ok && res.status !== 401 && res.status !== 403 && res.status !== 422 && res.status !== 423) throw new Error('Server Error');
        return res.json();
      })
      .then(data => {
        if (data.success) {
          // Sync session data to localStorage for UserState
          if (data.user) {
            localStorage.setItem('finzo_status', data.user.status || 'active');
            localStorage.setItem('finzo_user_name', data.user.full_name || '');
            localStorage.setItem('finzo_user_email', data.user.email || '');
            localStorage.setItem('finzo_account_no', data.user.account_no || '');
            localStorage.setItem('finzo_account_type', data.user.account_type || 'savings');
            localStorage.setItem('finzo_balance', data.user.balance || '0');
          }
          window.location.href = data.redirect || '/Finzo/views/user/dashboard.html';
        } else {
          $form.addClass('error-state');
          $error.text(data.message || "Invalid email or password").show();
          $btn.html(original).prop('disabled', false);
        }
      })
      .catch(err => {
        console.error('Login error:', err);
        $form.addClass('error-state');
        $error.text("Connection error. Please try again.").show();
        $btn.html(original).prop('disabled', false);
      });
    });

    $('#toggle-password').on('click', function() {
      const p = $('#password');
      const isPw = p.attr('type') === 'password';
      p.attr('type', isPw ? 'text' : 'password');
      $(this).text(isPw ? 'visibility_off' : 'visibility');
    });
  });