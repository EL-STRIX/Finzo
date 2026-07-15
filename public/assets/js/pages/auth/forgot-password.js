$(function() {
    $('form').on('submit', function(e) {
        e.preventDefault();
        const email = $(this).find('input[type="email"]').val().trim();
        const $btn = $(this).find('button[type="submit"]');

        if (!email) {
            $('#error-box').text('Please enter your email address.').show();
            return;
        }

        const original = $btn.html();
        $btn.prop('disabled', true).html('<span class="material-symbols-outlined animate-spin text-[16px]">progress_activity</span> <span>Sending...</span>');
        $('#error-box').hide();

        fetch('/Finzo/api/auth/forgot-password.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            credentials: 'same-origin',
            body: 'email=' + encodeURIComponent(email)
        })
        .then(res => res.json())
        .then(data => {
            $btn.prop('disabled', false).html(original);
            // Always show success message (security: don't reveal if email exists)
            const btn2 = $btn.html('<span class="material-symbols-outlined">check_circle</span> <span>Sent Successfully</span>').addClass('bg-green-600').removeClass('bg-primary-600');
            setTimeout(() => window.location.href = 'login.html', 3000);
        })
        .catch(err => {
            $btn.prop('disabled', false).html(original);
            $('#error-box').text('Network error. Please try again.').show();
        });
    });
});