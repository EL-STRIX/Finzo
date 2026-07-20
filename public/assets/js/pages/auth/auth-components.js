/**
 * Finzo Enterprise Authentication Component System JavaScript
 * Handles password visibility toggling, password strength meter,
 * 6-digit OTP auto-advance/paste, form validation, micro-interactions, and accessibility.
 */

document.addEventListener('DOMContentLoaded', () => {
    initPageAnimations();
    initPasswordToggle();
    initPasswordStrengthMeter();
    initOTPComponent();
    initFormValidation();
    initRoleTabs();
    initInputInteractions();
});

/**
 * 0. Page Entrance Animation & Smooth Reveal
 */
function initPageAnimations() {
    const cardContainer = document.querySelector('.auth-card-container');
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
    
    // Countdown Resend Timer
    const resendBtn = document.querySelector('[data-action="resend-otp"]');
    if (resendBtn) {
        let timeLeft = 45;
        resendBtn.disabled = true;
        
        const timerInterval = setInterval(() => {
            timeLeft--;
            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                resendBtn.disabled = false;
                resendBtn.innerHTML = 'Resend Code Now';
            } else {
                const secs = timeLeft < 10 ? `0${timeLeft}` : timeLeft;
                resendBtn.innerHTML = `Resend Code in 00:${secs}`;
            }
        }, 1000);
    }
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
                if (errorText) errorText.style.display = 'none';
            }
        });
    });
}

/**
 * 5. Form Validation & Button Loading States
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
                    if (errorText) errorText.style.display = 'flex';
                } else {
                    input.classList.remove('is-invalid');
                    input.removeAttribute('aria-invalid');
                    if (errorText) errorText.style.display = 'none';
                }
            });
            
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
