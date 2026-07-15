$(function() {
    const params = new URLSearchParams(window.location.search);
    const token = params.get('token') || '';

    $('form').on('submit', function(e) {
        e.preventDefault();
        const inputs = $(this).find('input[type="password"]');
        const newPassword = $(inputs[0]).val();
        const confirmPassword = $(inputs[1]).val();
        const $btn = $(this).find('button[type="submit"]');
        if (!newPassword || !confirmPassword) {
            $('#error-box').text('Both password fields are required.').show();
            return;
        }

        if (newPassword !== confirmPassword) {
            $('#error-box').text('Passwords do not match.').show();
            return;
        }

        if (newPassword.length < 8) {
            $('#error-box').text('Password must be at least 8 characters.').show();
            return;
        }

        const original = $btn.html();
        $btn.prop('disabled', true).html('<span class="material-symbols-outlined animate-spin text-[16px]">progress_activity</span> <span>Resetting...</span>');
        $('#error-box').hide();

        fetch('/Finzo/api/auth/reset-password.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            credentials: 'same-origin',
            body: 'token=' + encodeURIComponent(token) +
                  '&new_password=' + encodeURIComponent(newPassword) +
                  '&confirm_password=' + encodeURIComponent(confirmPassword)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                $btn.html('<span class="material-symbols-outlined">check_circle</span> <span>Reset Successful</span>').addClass('bg-green-600').removeClass('bg-primary-600');
                setTimeout(() => window.location.href = 'login.html', 2000);
            } else {
                $('#error-box').text(data.message || 'Reset failed. Please try again.').show();
                $btn.prop('disabled', false).html(original);
            }
        })
        .catch(() => {
            $('#error-box').text('Connection error. Please try again.').show();
            $btn.prop('disabled', false).html(original);
        });
    });
});