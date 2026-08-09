$(document).ready(function () {
            const registerForm = $('#register-form');
            const nameInput = $('#full-name');
            const emailInput = $('#email');
            const phoneInput = $('#phone');
            const passwordInput = $('#password');
            const confirmInput = $('#confirm-password');
            const errorBanner = $('#auth-error-banner');
            const errorText = errorBanner.find('span.text-xs');

            // Fetch CSRF Token on load
            fetch('/Finzo/api/auth/get-csrf-token.php')
                .then(res => res.json())
                .then(data => { if(data.csrf_token) $('input[name="csrf_token"]').val(data.csrf_token); })
                .catch(err => console.error('Token fetch error:', err));

            registerForm.on('submit', function (e) {
                e.preventDefault();

                let isValid = true;
                let errorMessage = "Registration incomplete. Please ensure all architectural fields are precisely configured.";

                // Reset states
                $('.input-error').removeClass('input-error');
                $('.shake-animation').removeClass('shake-animation');
                errorBanner.removeClass('active');

                // Validate Empty
                const inputs = [nameInput, emailInput, phoneInput, passwordInput, confirmInput];
                inputs.forEach(input => {
                    if (input.val().trim() === '') {
                        input.addClass('input-error shake-animation');
                        if (input.attr('id') === 'phone') {
                            input.closest('.flex').addClass('input-error shake-animation');
                        }
                        isValid = false;
                    }
                });

                // Password Strength Check
                const password = passwordInput.val();
                const strengthResult = checkPasswordStrength(password);
                if (strengthResult.score < 3) {
                    passwordInput.addClass('input-error shake-animation');
                    isValid = false;
                    errorMessage = "Security notice: Choose a strong password. Use 8+ characters with mixed case, numbers, and symbols.";
                }

                // Password Matching
                if (passwordInput.val() !== confirmInput.val()) {
                    passwordInput.addClass('input-error');
                    confirmInput.addClass('input-error');
                    isValid = false;
                    errorMessage = "Security notice: Password discrepancy detected. Authentication strings must match.";
                }

                // Phone Check
                const phoneVal = phoneInput.val().trim().replace(/[^0-9]/g, '');
                if (phoneVal !== '' && phoneVal.length !== 10) {
                    phoneInput.closest('.flex').addClass('input-error shake-animation');
                    isValid = false;
                    errorMessage = "Phone identity must be exactly 10 digits for verification.";
                }

                if (!isValid) {
                    $('#error-box').text('Please correct the highlighted fields').show();
                    return;
                }

                // Show loading state
                // Real API registration
                const btn = registerForm.find('button[type="submit"]');
                const originalContent = btn.html();
                btn.prop('disabled', true).html('<span class="material-symbols-outlined animate-spin">refresh</span> Establishing Ledger...');
                
                fetch('/Finzo/api/auth/register.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    credentials: 'same-origin',
                    body: 'full_name=' + encodeURIComponent(nameInput.val().trim()) +
                          '&email=' + encodeURIComponent(emailInput.val().trim()) +
                          '&phone=' + encodeURIComponent(phoneInput.val().trim().replace(/[^0-9]/g, '')) +
                          '&password=' + encodeURIComponent(passwordInput.val()) +
                          '&confirm_password=' + encodeURIComponent(confirmInput.val()) +
                          '&csrf_token=' + encodeURIComponent($('input[name="csrf_token"]').val())
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Store session info
                        localStorage.setItem('finzo_status', 'pending');
                        localStorage.setItem('finzo_user_name', nameInput.val().trim());
                        localStorage.setItem('finzo_user_email', emailInput.val().trim());
                        window.location.href = data.redirect || '/Finzo/views/user/dashboard.html';
                    } else {
                        if (data.errors) {
                            const firstError = Object.values(data.errors)[0];
                            errorText.text(firstError);
                        } else {
                            errorText.text(data.message || 'Registration failed. Please try again.');
                        }
                        errorBanner.addClass('active');
                        btn.prop('disabled', false).html(originalContent);
                    }
                })
                .catch(err => {
                    console.error('Registration error:', err);
                    errorText.text('Connection error. Please try again.');
                    errorBanner.addClass('active');
                    btn.prop('disabled', false).html(originalContent);
                });
            });

            $('input').on('input', function () {
                $(this).removeClass('input-error shake-animation');
                $(this).closest('.flex').removeClass('input-error shake-animation');
                if ($('.input-error').length === 0) {
                    errorBanner.removeClass('active');
                }
                
                // Real-time password strength update
                if ($(this).attr('id') === 'password') {
                    updateStrengthUI($(this).val());
                }
            });

            function checkPasswordStrength(p) {
                let s = 0;
                if (!p) return { score: 0, label: "" };
                if (p.length >= 8) s++;
                if (/[A-Z]/.test(p) && /[a-z]/.test(p)) s++;
                if (/[0-9]/.test(p)) s++;
                if (/[^A-Za-z0-9]/.test(p)) s++;
                
                let labels = ["Weak", "Weak", "Fair", "Good", "Strong"];
                let colors = ["bg-error", "bg-error", "bg-amber-400", "bg-blue-400", "bg-on-tertiary-container"];
                return { score: s, label: labels[s], color: colors[s] };
            }

            function updateStrengthUI(p) {
                const res = checkPasswordStrength(p);
                const bar = $('#password-strength-bar');
                const fill = $('#strength-fill');
                const text = $('#password-strength-text');

                if (p.length === 0) {
                    bar.addClass('opacity-0');
                    text.addClass('opacity-0');
                    return;
                }

                bar.removeClass('opacity-0');
                text.removeClass('opacity-0');
                
                fill.removeClass('bg-error bg-amber-400 bg-blue-400 bg-on-tertiary-container');
                fill.addClass(res.color);
                fill.css('width', (res.score / 4 * 100) + '%');
                
                text.text(res.label + ' Password');
                text.removeClass('text-error text-amber-400 text-blue-400 text-on-tertiary-container');
                // Maps bg class to text class for simplicity
                const textClass = res.color.replace('bg-', 'text-');
                text.addClass(textClass);
            }

            $('#toggle-password').on('click', function () {
                const type = passwordInput.attr('type') === 'password' ? 'text' : 'password';
                passwordInput.attr('type', type);
                $(this).find('span').text(type === 'password' ? 'visibility' : 'visibility_off');
            });
        });