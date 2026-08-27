<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0, viewport-fit=cover" name="viewport" />
    <meta name="description" content="Finzo Enterprise Banking - Secure Account Recovery via Security Question" />
    <title>Finzo | Account Recovery</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="/Finzo/public/assets/js/core/tailwind-config.js"></script>
    
    <link rel="stylesheet" href="/Finzo/public/assets/css/base/variables.css" />
    <link rel="stylesheet" href="/Finzo/public/assets/css/components/forms.css" />
    <link rel="stylesheet" href="/Finzo/public/assets/css/components/buttons.css" />
    <link rel="stylesheet" href="/Finzo/public/assets/css/components/cards.css" />
    <link rel="stylesheet" href="/Finzo/public/assets/css/components/badges.css" />
    <link rel="stylesheet" href="/Finzo/public/assets/css/layouts/auth.css" />

    <script src="/Finzo/public/assets/js/core/api.js"></script>
    <script src="/Finzo/public/assets/js/auth/auth-components.js" defer></script>
    <script defer src="/Finzo/public/assets/js/core/ui-components.js"></script>

    <style>
        /* Step Indicator */
        .recovery-steps { display: flex; align-items: center; gap: 0; margin-bottom: 1.75rem; }
        .step-item { display: flex; flex-direction: column; align-items: center; gap: 4px; flex: 1; }
        .step-circle {
            width: 32px; height: 32px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 600;
            background: var(--color-surface-2, #f1f5f9);
            color: var(--color-text-muted, #6b7280);
            border: 2px solid var(--color-border, #e2e8f0);
            transition: all 0.2s;
        }
        .step-circle.active {
            background: var(--color-primary, #0a2540);
            color: #fff; border-color: var(--color-primary, #0a2540);
        }
        .step-circle.done {
            background: var(--color-success, #16a34a);
            color: #fff; border-color: var(--color-success, #16a34a);
        }
        .step-label { font-size: 11px; font-weight: 500; color: var(--color-text-muted, #9ca3af); }
        .step-label.active { color: var(--color-primary, #0a2540); }
        .step-connector { flex: 1; height: 2px; background: var(--color-border, #e2e8f0); margin: 0 4px; margin-bottom: 16px; }
        .step-connector.done { background: var(--color-success, #16a34a); }

        /* Coming Soon pill */
        .coming-soon-row {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 14px; border-radius: 8px;
            background: var(--color-surface-2, #f8fafc);
            border: 1px dashed var(--color-border, #e2e8f0);
            margin-top: 1.25rem;
        }
        .coming-soon-pill {
            font-size: 10px; font-weight: 700; letter-spacing: 0.5px;
            padding: 2px 8px; border-radius: 20px; white-space: nowrap;
            background: #fef3c7; color: #92400e; border: 1px solid #fde68a;
        }
        .coming-soon-text { font-size: 12px; color: var(--color-text-muted, #6b7280); line-height: 1.4; }

        /* Step panels */
        [data-step] { display: none; }
        [data-step].active { display: block; }
    </style>
</head>
<body class="bg-gray-50 font-body text-on-surface">
    <main class="auth-page-wrapper">
        <div class="auth-split-grid">
            <!-- Left Side: Hero Illustration & Security Banner -->
            <div class="auth-left-panel">
                <img loading="lazy" src="/Finzo/public/assets/images/forget-password-page-image.png" alt="Recovery Illustration" class="auth-hero-img-bg" />
                <img loading="lazy" src="/Finzo/public/assets/images/forget-password-page-image.png" alt="Recovery Illustration" class="auth-hero-img-secondary" />
                <div class="auth-hero-overlay"></div>
                
                <div class="auth-hero-content">
                    <div>
                        <a href="/Finzo/public/index.html" class="auth-brand-logo">
                            <div class="auth-brand-icon-box">
                                <span class="material-symbols-outlined text-2xl">account_balance</span>
                            </div>
                            <span class="auth-brand-name">Finzo</span>
                        </a>
                        <h2 class="auth-hero-headline">Recover your account securely.</h2>
                        <p class="auth-hero-subtitle">Verify your identity using your registered security question. No email link required — your recovery is handled directly.</p>
                    </div>
                    
                    <div>
                        <div class="auth-hero-security-card">
                            <span class="material-symbols-outlined auth-hero-security-icon">help_center</span>
                            <div>
                                <h4 class="auth-hero-security-title">Security Question Recovery</h4>
                                <p class="auth-hero-security-desc">Answer your pre-registered security question to prove ownership. If you fail verification, contact your branch administrator to unlock access.</p>
                            </div>
                        </div>
                        <div class="auth-hero-footer">
                            <p class="auth-hero-copyright">&copy; 2026 Finzo Banking Group. All rights reserved.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Auth Component Container -->
            <div class="auth-right-area">
                <div class="auth-card-container">
                    <!-- Mobile Logo -->
                    <a href="/Finzo/public/index.html" class="auth-mobile-logo" aria-label="Finzo Homepage">
                        <span class="material-symbols-outlined text-2xl">account_balance</span>
                    </a>
                    
                    <!-- Page Header -->
                    <div class="auth-page-header">
                        <h1 class="auth-page-title">Account Recovery</h1>
                        <p class="auth-page-subtitle">Verify your identity to reset your password.</p>
                    </div>

                    <!-- Step Indicator -->
                    <div class="recovery-steps" aria-label="Recovery steps">
                        <div class="step-item">
                            <div class="step-circle active" id="step-circle-1">1</div>
                            <span class="step-label active" id="step-label-1">Identify</span>
                        </div>
                        <div class="step-connector" id="step-connector-1"></div>
                        <div class="step-item">
                            <div class="step-circle" id="step-circle-2">2</div>
                            <span class="step-label" id="step-label-2">Verify</span>
                        </div>
                        <div class="step-connector" id="step-connector-2"></div>
                        <div class="step-item">
                            <div class="step-circle" id="step-circle-3">3</div>
                            <span class="step-label" id="step-label-3">Reset</span>
                        </div>
                    </div>

                    <!-- ======== STEP 1: Enter Customer ID ======== -->
                    <div data-step="1" class="active">
                        <form id="recovery-step1-form" novalidate>
                            <input type="hidden" name="csrf_token" value="" />

                            <!-- Customer ID Input -->
                            <div class="fin-input-container">
                                <label for="recovery-customer-id" class="fin-label fin-label-required">Customer ID</label>
                                <div class="fin-input-wrapper">
                                    <span class="material-symbols-outlined input-icon-left">person</span>
                                    <input type="text" id="recovery-customer-id" name="customer_id" class="fin-input has-icon-left" placeholder="e.g. FNZ123456789" required autocomplete="username" />
                                </div>
                                <span class="fin-error-text" role="alert" id="step1-error">
                                    <span class="material-symbols-outlined">error</span> Please enter your Customer ID.
                                </span>
                            </div>

                            <!-- Action Buttons -->
                            <div class="space-y-3 mt-6">
                                <button type="button" id="step1-next-btn" class="btn btn-primary btn-full">
                                    <span>Continue</span>
                                    <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                                </button>
                                <a href="login.php" class="btn btn-secondary btn-full">
                                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                                    <span>Return to Sign In</span>
                                </a>
                            </div>
                        </form>

                        <!-- Email Recovery — Coming Soon -->
                        <div class="coming-soon-row" aria-label="Email recovery coming soon">
                            <span class="material-symbols-outlined text-[18px] text-gray-400">mail</span>
                            <div>
                                <div class="flex items-center gap-2 mb-0.5">
                                    <span class="text-xs font-semibold text-gray-600">Email Recovery</span>
                                    <span class="coming-soon-pill">Coming Soon</span>
                                </div>
                                <p class="coming-soon-text">Password recovery via email link is under development and will be available in a future release.</p>
                            </div>
                        </div>
                    </div>

                    <!-- ======== STEP 2: Answer Security Question ======== -->
                    <div data-step="2">
                        <form id="recovery-step2-form" data-action="forgot-password" method="POST" novalidate>
                            <input type="hidden" name="csrf_token" value="" />
                            <input type="hidden" name="customer_id" id="hidden-customer-id" value="" />

                            <!-- Security Question Display -->
                            <div class="mb-5">
                                <div class="flex items-start gap-3 p-4 rounded-lg bg-blue-50 border border-blue-100">
                                    <span class="material-symbols-outlined text-blue-500 text-[20px] mt-0.5">help_outline</span>
                                    <div>
                                        <p class="text-xs font-semibold text-blue-700 mb-1">Your Security Question</p>
                                        <p class="text-sm font-medium text-blue-900" id="security-question-text">What was the name of your first pet?</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Security Answer Input -->
                            <div class="fin-input-container">
                                <label for="security-answer" class="fin-label fin-label-required">Your Answer</label>
                                <div class="fin-input-wrapper">
                                    <span class="material-symbols-outlined input-icon-left">lock_open</span>
                                    <input type="password" id="security-answer" name="security_answer" class="fin-input has-icon-left has-icon-right" placeholder="Enter your answer" required autocomplete="off" />
                                    <button type="button" class="password-toggle-btn" aria-label="Toggle answer visibility" tabindex="0">
                                        <span class="material-symbols-outlined">visibility</span>
                                    </button>
                                </div>
                                <span class="fin-error-text" role="alert" id="step2-error">
                                    <span class="material-symbols-outlined">error</span> Security answer is required.
                                </span>
                            </div>

                            <!-- Attempt Warning -->
                            <div class="alert alert-warning text-left mt-4" role="alert">
                                <span class="material-symbols-outlined alert-icon">warning</span>
                                <div class="text-xs">
                                    <strong>Note:</strong> You have a limited number of attempts. If verification fails, your account will be locked and you must contact your branch administrator.
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="space-y-3 mt-5">
                                <button type="submit" class="btn btn-primary btn-full">
                                    <span>Verify Identity</span>
                                    <span class="material-symbols-outlined text-[18px]">verified_user</span>
                                </button>
                                <button type="button" id="step2-back-btn" class="btn btn-secondary btn-full">
                                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                                    <span>Go Back</span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Trust Badges -->
                    <div class="trust-badge-group">
                        <span class="trust-badge"><span class="material-symbols-outlined">enhanced_encryption</span> 256-bit Encryption</span>
                        <span class="trust-badge"><span class="material-symbols-outlined">gpp_good</span> Identity Protected</span>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
    (function () {
        const steps    = document.querySelectorAll('[data-step]');
        const circles  = [1, 2, 3].map(n => document.getElementById('step-circle-' + n));
        const labels   = [1, 2, 3].map(n => document.getElementById('step-label-' + n));
        const connectors = [1, 2].map(n => document.getElementById('step-connector-' + n));
        let currentStep = 1;

        function goToStep(n) {
            steps.forEach(s => s.classList.remove('active'));
            document.querySelector('[data-step="' + n + '"]').classList.add('active');
            circles.forEach((c, i) => {
                c.classList.remove('active', 'done');
                labels[i].classList.remove('active');
                if (i + 1 < n) { c.classList.add('done'); c.innerHTML = '<span class="material-symbols-outlined" style="font-size:15px">check</span>'; }
                else if (i + 1 === n) { c.classList.add('active'); labels[i].classList.add('active'); c.textContent = i + 1; }
                else { c.textContent = i + 1; }
            });
            connectors.forEach((cn, i) => { cn.classList.toggle('done', i + 1 < n); });
            currentStep = n;
        }

        // Step 1 → Step 2
        document.getElementById('step1-next-btn').addEventListener('click', function () {
            const id = document.getElementById('recovery-customer-id').value.trim();
            const err = document.getElementById('step1-error');
            if (!id) {
                document.getElementById('recovery-customer-id').classList.add('is-invalid');
                err.style.display = 'flex'; return;
            }
            document.getElementById('recovery-customer-id').classList.remove('is-invalid');
            err.style.display = 'none';
            document.getElementById('hidden-customer-id').value = id;
            goToStep(2);
        });

        // Step 2 → Step 1 (back)
        document.getElementById('step2-back-btn').addEventListener('click', function () {
            goToStep(1);
        });

        // Password toggle for security answer
        document.querySelectorAll('.password-toggle-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const inp = btn.closest('.fin-input-wrapper').querySelector('input');
                const icon = btn.querySelector('.material-symbols-outlined');
                if (inp.type === 'password') { inp.type = 'text'; icon.textContent = 'visibility_off'; }
                else { inp.type = 'password'; icon.textContent = 'visibility'; }
            });
        });
    })();
    </script>
</body>
</html>
