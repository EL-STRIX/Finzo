<?php
$role = strtolower(trim($_GET['role'] ?? 'user'));
if (!in_array($role, ['user', 'staff', 'admin'], true)) {
    $role = 'user';
}

$roleConfig = [
    'user' => [
        'page_title' => 'Finzo | Customer Recovery',
        'title' => 'Customer Password Recovery',
        'subtitle' => 'Email and phone OTP verification are currently coming soon.',
        'badge' => 'Email Recovery &mdash; Coming Soon',
        'badge_class' => 'bg-amber-100 text-amber-700 border-amber-200',
        'badge_icon' => 'schedule',
        'icon' => 'mark_email_unread',
        'icon_class' => 'bg-amber-50 border-amber-100 text-amber-500',
        'alert' => 'Automated email and SMS OTP recovery are currently under development. Online password reset via email link is not active.',
        'info_title' => 'Customer Password Assistance',
        'info_desc' => 'Customer accounts do not use security questions. If you forgot your password or your account is locked, please contact your branch administrator.',
        'info_extra' => 'The branch administrator will verify your identity and manually generate a new secure password for your account.',
        'hero_headline' => 'Customer account recovery.',
        'hero_subtitle' => 'Email and phone OTP verification are coming soon. Account password resets are handled directly by your branch administrator.',
        'hero_card_title' => 'Branch-Managed Password Reset',
        'hero_card_desc' => 'To protect customer account security, passwords are generated and reset manually by branch administrators. No automated email links are sent.',
        'hero_card_icon' => 'support_agent',
        'back_link' => 'login.php',
        'back_label' => 'Return to Sign In',
    ],
    'staff' => [
        'page_title' => 'Finzo | Staff Recovery',
        'title' => 'Staff Portal Recovery',
        'subtitle' => 'Internal personnel credentials and password reset workflow.',
        'badge' => 'Security Question Verification',
        'badge_class' => 'bg-blue-100 text-blue-700 border-blue-200',
        'badge_icon' => 'help_center',
        'icon' => 'badge',
        'icon_class' => 'bg-blue-50 border-blue-100 text-blue-500',
        'alert' => 'Staff password recovery uses security question verification. Automated email and SMS OTP recovery are disabled.',
        'info_title' => 'Staff Credential Reset Workflow',
        'info_desc' => 'Staff members recover passwords through pre-registered security questions. If your security question verification fails or your account is locked, an Administrator will manually change and issue a new password.',
        'info_extra' => 'Once the administrator updates your password in the staff management section, you can log in to the staff portal immediately.',
        'hero_headline' => 'Staff access recovery.',
        'hero_subtitle' => 'Internal personnel credentials are protected by security questions. Failed attempts require manual administrator password update.',
        'hero_card_title' => 'Administrator Password Override',
        'hero_card_desc' => 'If a staff member fails the security question verification, the account is locked and an Administrator must manually set a new password in the Staff section.',
        'hero_card_icon' => 'badge',
        'back_link' => 'staff-login.php',
        'back_label' => 'Return to Staff Portal',
    ],
    'admin' => [
        'page_title' => 'Finzo | Admin Recovery',
        'title' => 'Admin Terminal Recovery',
        'subtitle' => 'Administrator security credentials and access restoration.',
        'badge' => 'Super Admin Authorization',
        'badge_class' => 'bg-purple-100 text-purple-700 border-purple-200',
        'badge_icon' => 'shield_person',
        'icon' => 'terminal',
        'icon_class' => 'bg-purple-50 border-purple-100 text-purple-500',
        'alert' => 'Admin password recovery uses security question verification. If verification fails, access restoration requires Super Admin intervention.',
        'info_title' => 'Administrator Recovery Workflow',
        'info_desc' => 'Administrators can recover access via security questions. If your security question verification fails, the Super Admin must manually generate a new password for you.',
        'info_extra' => 'Super Admin passwords cannot be generated or changed by standard administrators or any other user.',
        'hero_headline' => 'Administrator terminal recovery.',
        'hero_subtitle' => 'Strict access control applies. Password resets for administrative personnel require security question verification or Super Admin action.',
        'hero_card_title' => 'Super Admin Exclusive Authority',
        'hero_card_desc' => 'Only the Super Admin has the privilege to unlock or generate passwords for administrators. Standard administrators cannot reset Super Admin credentials.',
        'hero_card_icon' => 'admin_panel_settings',
        'back_link' => 'admin-login.php',
        'back_label' => 'Return to Admin Terminal',
    ],
];

$config = $roleConfig[$role];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0, viewport-fit=cover" name="viewport" />
    <meta name="description" content="Finzo Enterprise Banking - Password Recovery" />
    <title><?= htmlspecialchars($config['page_title']) ?></title>
    
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
                        <h2 class="auth-hero-headline"><?= htmlspecialchars($config['hero_headline']) ?></h2>
                        <p class="auth-hero-subtitle"><?= htmlspecialchars($config['hero_subtitle']) ?></p>
                    </div>
                    
                    <div>
                        <div class="auth-hero-security-card">
                            <span class="material-symbols-outlined auth-hero-security-icon"><?= htmlspecialchars($config['hero_card_icon']) ?></span>
                            <div>
                                <h4 class="auth-hero-security-title"><?= htmlspecialchars($config['hero_card_title']) ?></h4>
                                <p class="auth-hero-security-desc"><?= htmlspecialchars($config['hero_card_desc']) ?></p>
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
                <div class="auth-card-container text-center">
                    <!-- Mobile Logo -->
                    <a href="/Finzo/public/index.html" class="auth-mobile-logo" aria-label="Finzo Homepage">
                        <span class="material-symbols-outlined text-2xl">account_balance</span>
                    </a>

                    <!-- Role Switcher -->
                    <div class="auth-role-switcher mb-6" role="tablist" aria-label="Select Role">
                        <a href="forgot-password.php?role=user" class="auth-role-tab <?= $role === 'user' ? 'active' : '' ?>" role="tab" aria-selected="<?= $role === 'user' ? 'true' : 'false' ?>">Customer</a>
                        <a href="forgot-password.php?role=staff" class="auth-role-tab <?= $role === 'staff' ? 'active' : '' ?>" role="tab" aria-selected="<?= $role === 'staff' ? 'true' : 'false' ?>">Staff</a>
                        <a href="forgot-password.php?role=admin" class="auth-role-tab <?= $role === 'admin' ? 'active' : '' ?>" role="tab" aria-selected="<?= $role === 'admin' ? 'true' : 'false' ?>">Admin</a>
                    </div>

                    <!-- Icon -->
                    <div class="w-16 h-16 <?= $config['icon_class'] ?> rounded-full flex items-center justify-center mx-auto mb-5 border">
                        <span class="material-symbols-outlined text-3xl"><?= htmlspecialchars($config['icon']) ?></span>
                    </div>

                    <!-- Page Header -->
                    <div class="auth-page-header">
                        <h1 class="auth-page-title"><?= htmlspecialchars($config['title']) ?></h1>
                        <div class="flex items-center justify-center gap-2 mt-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border <?= $config['badge_class'] ?>">
                                <span class="material-symbols-outlined" style="font-size:14px"><?= htmlspecialchars($config['badge_icon']) ?></span>
                                <?= $config['badge'] ?>
                            </span>
                        </div>
                    </div>

                    <!-- Alert Notice -->
                    <div class="alert alert-info text-left mt-6" role="status">
                        <span class="material-symbols-outlined alert-icon">info</span>
                        <div>
                            <?= htmlspecialchars($config['alert']) ?>
                        </div>
                    </div>

                    <!-- Information Box -->
                    <div class="text-left mt-5 p-4 rounded-lg border border-gray-100 bg-gray-50">
                        <p class="text-xs font-semibold text-gray-700 mb-2 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px] text-primary">verified_user</span>
                            <?= htmlspecialchars($config['info_title']) ?>
                        </p>
                        <p class="text-sm text-gray-600 leading-relaxed mb-2">
                            <?= htmlspecialchars($config['info_desc']) ?>
                        </p>
                        <p class="text-xs text-gray-500 leading-relaxed">
                            <?= htmlspecialchars($config['info_extra']) ?>
                        </p>
                    </div>

                    <!-- Buttons Group -->
                    <div class="space-y-3 mt-6">
                        <a href="<?= htmlspecialchars($config['back_link']) ?>" class="btn btn-primary btn-full">
                            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                            <span><?= htmlspecialchars($config['back_label']) ?></span>
                        </a>
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
</body>
</html>
