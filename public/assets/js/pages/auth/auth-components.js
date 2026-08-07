/**
 * Finzo Enterprise Authentication Component System JavaScript
 * Handles password visibility toggling, password strength meter,
 * 6-digit OTP auto-advance/paste, real-time error recovery, feedback toasts,
 * form validation, micro-interactions, CSRF integration, and accessibility.
 */

document.addEventListener('DOMContentLoaded', () => {
    initPageAnimations();
    initCsrfToken();
    initPasswordToggle();
    initPasswordStrengthMeter();
    initOTPComponent();
    initFormValidation();
    initRoleTabs();
    initInputInteractions();
    initResendEmailAction();
    initRememberMe();
    initResetPasswordTokenFromUrl();
    initAuthFormSubmission();
});

/**
 * 0. Page Entrance Animation & Smooth Reveal
 */
function initPageAnimations() {
    const cardContainer = document.querySelector('.ds-card-container');
    if (cardContainer) {
        cardContainer.style.opacity = '0';
        cardContainer.style.transform = 'translateY(8px)';
        cardContainer.style.transition = 'opacity 350ms cubic-bezier(0.16, 1, 0.3, 1), transform 350ms cubic-bezier(0.16, 1, 0.3, 1)';
        
        requestAnimationFrame(() => {
            cardContainer.style.opacity = '1';
            cardContainer.style.transform = 'translateY(0)';
        });
    }
}

/**
 * Fetch and inject CSRF token dynamically for API form submissions
 */
function initCsrfToken() {
    const csrfInputs = document.querySelectorAll('input[name="csrf_token"]');
    if (csrfInputs.length === 0) return;

    const endpoint = '/auth/csrf-token';

    window.api.get(endpoint)
        .then(data => {
            if (data && data.csrf_token) {
                csrfInputs.forEach(input => {
                    input.value = data.csrf_token;
                });
            }
        })
        .catch(() => {
            console.warn('[Finzo] CSRF endpoint unreachable.');
        });
}

/**
 * 1. Password Visibility Toggle Component
 */
function initPasswordToggle() {
    const toggleBtns = document.querySelectorAll('.password-toggle-btn, [data-action="toggle-password"]');
    
    toggleBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const wrapper = btn.closest('.fin-input-wrapper') || btn.parentElement;
            const input = wrapper ? wrapper.querySelector('input') : null;
            
            if (!input) return;
            
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            
            const icon = btn.querySelector('.material-symbols-outlined') || btn;
            if (icon && (icon.tagName === 'SPAN' || icon.classList.contains('material-symbols-outlined'))) {
                icon.textContent = isPassword ? 'visibility_off' : 'visibility';
            }
            
            btn.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
            btn.setAttribute('aria-pressed', isPassword ? 'true' : 'false');
            input.focus();
        });
    });
}

/**
 * 2. Real-time Password Strength Meter Component
 */
function initPasswordStrengthMeter() {
    const passwordInputs = document.querySelectorAll('[data-strength-meter="true"], #create-password');
    
    passwordInputs.forEach(input => {
        const container = input.closest('.fin-input-container') || input.parentElement;
        const fillBar = container ? container.querySelector('.strength-bar-fill') : null;
        const labelText = container ? container.querySelector('.strength-label') : null;
        const reqItems = container ? container.querySelectorAll('.req-item') : [];
        
        if (!fillBar) return;
        
        input.addEventListener('input', () => {
            const val = input.value;
            let score = 0;
            
            // Requirements checks
            const hasLength = val.length >= 8;
            const hasUpper = /[A-Z]/.test(val);
            const hasNumber = /[0-9]/.test(val);
            const hasSpecial = /[^A-Za-z0-9]/.test(val);
            
            // Update requirement indicators
            reqItems.forEach(item => {
                const reqType = item.getAttribute('data-req');
                let isMet = false;
                if (reqType === 'length') isMet = hasLength;
                if (reqType === 'upper') isMet = hasUpper;
                if (reqType === 'number') isMet = hasNumber;
                if (reqType === 'special') isMet = hasSpecial;
                
                if (isMet) {
                    item.classList.add('met');
                    const icon = item.querySelector('.material-symbols-outlined');
                    if (icon) icon.textContent = 'check_circle';
                } else {
                    item.classList.remove('met');
                    const icon = item.querySelector('.material-symbols-outlined');
                    if (icon) icon.textContent = 'cancel';
                }
            });
            
            if (val.length === 0) {
                fillBar.className = 'strength-bar-fill';
                fillBar.style.width = '0%';
                if (labelText) {
                    labelText.textContent = 'Password Strength';
                    labelText.className = 'strength-label';
                }
                return;
            }
            
            if (hasLength) score++;
            if (hasUpper) score++;
            if (hasNumber) score++;
            if (hasSpecial) score++;
            if (val.length >= 12) score++;
            
            fillBar.className = 'strength-bar-fill';
            if (score <= 1) {
                fillBar.classList.add('weak');
                if (labelText) { labelText.textContent = 'Weak'; labelText.className = 'strength-label weak'; }
            } else if (score === 2 || score === 3) {
                fillBar.classList.add('fair');
                if (labelText) { labelText.textContent = 'Fair'; labelText.className = 'strength-label fair'; }
            } else if (score === 4) {
                fillBar.classList.add('strong');
                if (labelText) { labelText.textContent = 'Strong'; labelText.className = 'strength-label strong'; }
            } else {
                fillBar.classList.add('enterprise');
                if (labelText) { labelText.textContent = 'Enterprise Grade'; labelText.className = 'strength-label enterprise'; }
            }
        });
    });
}

/**
 * 3. Enterprise 6-Digit OTP Experience
 */
function initOTPComponent() {
    const otpContainer = document.querySelector('.fin-otp-container');
    if (!otpContainer) return;
    
    const inputs = otpContainer.querySelectorAll('.fin-otp-input');
    
    inputs.forEach((input, idx) => {
        input.setAttribute('inputmode', 'numeric');
        input.setAttribute('pattern', '[0-9]*');

        // Auto-advance on input
        input.addEventListener('input', (e) => {
            const val = e.target.value;
            if (val.length >= 1) {
                input.value = val.substring(0, 1);
                input.classList.add('is-filled');
                if (idx < inputs.length - 1) {
                    inputs[idx + 1].focus();
                }
            } else {
                input.classList.remove('is-filled');
            }
        });
        
        // Keydown handling for backspace and arrows
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !input.value && idx > 0) {
                inputs[idx - 1].focus();
                inputs[idx - 1].value = '';
                inputs[idx - 1].classList.remove('is-filled');
            } else if (e.key === 'ArrowLeft' && idx > 0) {
                inputs[idx - 1].focus();
            } else if (e.key === 'ArrowRight' && idx < inputs.length - 1) {
                inputs[idx + 1].focus();
            }
        });
        
        // Paste support
        input.addEventListener('paste', (e) => {
            e.preventDefault();
            const pasteData = (e.clipboardData || window.clipboardData).getData('text').trim();
            if (/^\d{6}$/.test(pasteData)) {
                pasteData.split('').forEach((char, i) => {
                    if (inputs[i]) {
                        inputs[i].value = char;
                        inputs[i].classList.add('is-filled');
                    }
                });
                inputs[inputs.length - 1].focus();
            }
        });
    });
    
    // Countdown Resend Timer & Resend Click Handler
    const resendBtn = document.querySelector('[data-action="resend-otp"]');
    if (resendBtn) {
        startResendCountdown(resendBtn, 45);

        resendBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (resendBtn.disabled) return;
            
            showNotificationToast('A new 6-digit security code has been sent to your device.', 'success');
            startResendCountdown(resendBtn, 45);
        });
    }
}

function startResendCountdown(btn, seconds) {
    let timeLeft = seconds;
    btn.disabled = true;
    
    const timerInterval = setInterval(() => {
        timeLeft--;
        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            btn.disabled = false;
            btn.innerHTML = 'Resend Code Now';
        } else {
            const secs = timeLeft < 10 ? `0${timeLeft}` : timeLeft;
            btn.innerHTML = `Resend Code in 00:${secs}`;
        }
    }, 1000);
}

/**
 * Resend Email Verification Handler
 */
function initResendEmailAction() {
    const btn = document.querySelector('[data-action="resend-email-verification"]');
    if (!btn) return;

    btn.addEventListener('click', (e) => {
        e.preventDefault();
        showNotificationToast('A new verification email has been dispatched to your inbox.', 'success');
    });
}

/**
 * 4. Input Real-Time Micro-Interactions
 */
function initInputInteractions() {
    const inputs = document.querySelectorAll('.fin-input, .form-control');
    
    inputs.forEach(input => {
        // Clear invalid state on typing
        input.addEventListener('input', () => {
            if (input.classList.contains('is-invalid')) {
                input.classList.remove('is-invalid');
                input.removeAttribute('aria-invalid');
                const errorText = input.closest('.fin-input-container')?.querySelector('.fin-error-text');
                if (errorText) {
                    errorText.classList.remove('is-visible');
                    errorText.style.display = '';
                }
            }
        });
    });
}

/**
 * 5. Form Validation & Password Matching Error Recovery
 */
function initFormValidation() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', (e) => {
            let isValid = true;
            const requiredInputs = form.querySelectorAll('input[required], select[required]');
            
            requiredInputs.forEach(input => {
                const errorText = input.closest('.fin-input-container')?.querySelector('.fin-error-text');
                
                if (!input.value.trim()) {
                    isValid = false;
                    input.classList.add('is-invalid');
                    input.setAttribute('aria-invalid', 'true');
                    if (errorText) {
                        errorText.classList.add('is-visible');
                    }
                } else {
                    input.classList.remove('is-invalid');
                    input.removeAttribute('aria-invalid');
                    if (errorText) {
                        errorText.classList.remove('is-visible');
                        errorText.style.display = '';
                    }
                }
            });

            // Password confirmation matching validation
            const newPassInput = form.querySelector('input[name="new_password"], input[name="password"]');
            const confirmPassInput = form.querySelector('input[name="confirm_password"]');
            
            if (newPassInput && confirmPassInput && confirmPassInput.value) {
                const errorText = confirmPassInput.closest('.fin-input-container')?.querySelector('.fin-error-text');
                if (newPassInput.value !== confirmPassInput.value) {
                    isValid = false;
                    confirmPassInput.classList.add('is-invalid');
                    confirmPassInput.setAttribute('aria-invalid', 'true');
                    if (errorText) {
                        errorText.innerHTML = '<span class="material-symbols-outlined">error</span> Passwords do not match.';
                        errorText.classList.add('is-visible');
                    }
                }
            }
            
            if (!isValid) {
                e.preventDefault();
                const firstInvalid = form.querySelector('.is-invalid');
                if (firstInvalid) firstInvalid.focus();
                return;
            }
            
            // Trigger button loading state
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.classList.add('is-loading');
                submitBtn.setAttribute('aria-busy', 'true');
                submitBtn.disabled = true;
            }
        });
    });
}

/**
 * 6. Role Switcher Keyboard Controls
 */
function initRoleTabs() {
    const roleTabs = document.querySelectorAll('.auth-role-tab');
    roleTabs.forEach(tab => {
        tab.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
                const tabsArr = Array.from(roleTabs);
                const currIdx = tabsArr.indexOf(tab);
                let nextIdx = e.key === 'ArrowRight' ? currIdx + 1 : currIdx - 1;
                if (nextIdx < 0) nextIdx = tabsArr.length - 1;
                if (nextIdx >= tabsArr.length) nextIdx = 0;
                tabsArr[nextIdx].focus();
            }
        });
    });
}

/**
 * 7. Enterprise Toast Notification Feedback System
 */
function showNotificationToast(message, type = 'info') {
    let toastContainer = document.querySelector('.fin-toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'fin-toast-container';
        toastContainer.setAttribute('role', 'status');
        toastContainer.setAttribute('aria-live', 'polite');
        document.body.appendChild(toastContainer);
    }

    const toast = document.createElement('div');
    toast.className = `fin-toast fin-toast-${type}`;
    const iconMap = {
        success: 'check_circle',
        error:   'error',
        warning: 'warning',
        info:    'info'
    };
    toast.innerHTML = `
        <span class="material-symbols-outlined fin-toast-icon">${iconMap[type] || 'info'}</span>
        <span class="fin-toast-msg">${message}</span>
    `;

    toastContainer.appendChild(toast);

    requestAnimationFrame(() => {
        toast.classList.add('is-visible');
    });

    setTimeout(() => {
        toast.classList.remove('is-visible');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

/**
 * 8. Remember Me — persist identity field using localStorage
 */
function initRememberMe() {
    const rememberCheckbox = document.getElementById('remember-me');
    const identityInput = document.getElementById('customer-id');
    if (!rememberCheckbox || !identityInput) return;

    // Restore saved identity
    const saved = localStorage.getItem('finzo_remembered_identity');
    if (saved) {
        identityInput.value = saved;
        rememberCheckbox.checked = true;
    }

    const form = rememberCheckbox.closest('form');
    if (form) {
        form.addEventListener('submit', () => {
            if (rememberCheckbox.checked && identityInput.value.trim()) {
                localStorage.setItem('finzo_remembered_identity', identityInput.value.trim());
            } else {
                localStorage.removeItem('finzo_remembered_identity');
            }
        }, { capture: true });
    }
}

/**
 * 9. Auto-populate reset token from URL query param
 */
function initResetPasswordTokenFromUrl() {
    const tokenInput = document.querySelector('input[name="token"]');
    if (!tokenInput) return;
    const params = new URLSearchParams(window.location.search);
    const token = params.get('token');
    if (token) {
        tokenInput.value = token;
    }
}

/**
 * 10. AJAX Form Submission Handler for all auth forms
 */
function initAuthFormSubmission() {
    const formMap = [
        { selector: 'form[data-action="customer-login"]',    endpoint: '/Finzo/public/api/auth/login' },
        { selector: 'form[data-action="admin-login"]',       endpoint: '/Finzo/public/api/auth/admin-login' },
        { selector: 'form[data-action="staff-login"]',       endpoint: '/Finzo/public/api/auth/staff-login' },
        { selector: 'form[data-action="register"]',         endpoint: '/Finzo/public/api/auth/register' },
        { selector: 'form[data-action="forgot-password"]',  endpoint: '/auth/forgot-password' },
        { selector: 'form[data-action="reset-password"]',   endpoint: '/auth/reset-password' },
    ];

    formMap.forEach(({ selector, endpoint }) => {
        const form = document.querySelector(selector);
        if (!form) return;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Clear previous server errors
            clearServerErrors(form);

            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.classList.add('is-loading');
                submitBtn.disabled = true;
                submitBtn.setAttribute('aria-busy', 'true');
            }

            try {
                const formData = new FormData(form);
                // If it's a FormData object, window.api handles the fetch automatically
                // However, since it's auth, we should map endpoint
                const apiEndpoint = endpoint.replace('/Finzo/public/api', '');
                
                const data = await window.api.post(apiEndpoint, formData, true);

                if (data.success) {
                    if (data.redirect) {
                        showNotificationToast(data.message || 'Success!', 'success');
                        setTimeout(() => { window.location.href = data.redirect; }, 600);
                    } else {
                        showNotificationToast(data.message || 'Done!', 'success');
                    }
                } else {
                    // Show field-level errors if present
                    if (data.errors) {
                        displayFieldErrors(form, data.errors);
                    } else {
                        showNotificationToast(data.message || 'An error occurred.', 'error');
                    }

                    // Handle locked account redirect
                    if (data.locked && data.redirect) {
                        setTimeout(() => { window.location.href = data.redirect; }, 1500);
                    }
                }
            } catch (err) {
                showNotificationToast('Connection failed. Please check your network.', 'error');
            } finally {
                if (submitBtn) {
                    submitBtn.classList.remove('is-loading');
                    submitBtn.disabled = false;
                    submitBtn.removeAttribute('aria-busy');
                }
                // Refresh CSRF token after each submission
                initCsrfToken();
            }
        });
    });
}

/**
 * Display field-level server validation errors on the form
 */
function displayFieldErrors(form, errors) {
    Object.entries(errors).forEach(([field, message]) => {
        const input = form.querySelector(`[name="${field}"]`);
        if (!input) return;
        input.classList.add('is-invalid');
        input.setAttribute('aria-invalid', 'true');
        const container = input.closest('.fin-input-container');
        if (container) {
            let errorEl = container.querySelector('.fin-error-text');
            if (!errorEl) {
                errorEl = document.createElement('span');
                errorEl.className = 'fin-error-text';
                container.appendChild(errorEl);
            }
            errorEl.innerHTML = `<span class="material-symbols-outlined">error</span> ${message}`;
            errorEl.classList.add('is-visible');
        }
    });
}

/**
 * Clear all server-side error states from a form
 */
function clearServerErrors(form) {
    form.querySelectorAll('.is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
        el.removeAttribute('aria-invalid');
    });
    form.querySelectorAll('.fin-error-text.is-visible').forEach(el => {
        el.classList.remove('is-visible');
    });
}
