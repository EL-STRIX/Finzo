<?php
function replaceContent($file, $search, $replace) {
    if (!file_exists($file)) return;
    $content = file_get_contents($file);
    $newContent = str_replace($search, $replace, $content);
    if ($content !== $newContent) {
        file_put_contents($file, $newContent);
        echo "Updated $file\n";
    }
}

// 1. forgot-password.html
replaceContent('resources/views/auth/forgot-password.html', 'Recovery Access.', 'Forgot Your Password?');
replaceContent('resources/views/auth/forgot-password.html', 'Re-establish security protocols for your institutional ledger through our precision identity layer.', 'Reset your password securely by entering the email address associated with your account.');
replaceContent('resources/views/auth/forgot-password.html', 'Password Recovery', 'Forgot Your Password?');
replaceContent('resources/views/auth/forgot-password.html', 'Verify your identity to re-establish secure access.', 'We\'ll send secure instructions to help you regain access.');
replaceContent('resources/views/auth/forgot-password.html', 'Send Recovery Key', 'Recover Password');
replaceContent('resources/views/auth/forgot-password.html', 'class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-md focus:ring-2 focus:ring-blue-600 focus:border-blue-600 block p-3 transition-colors pl-10"', 'class="fin-input has-icon-left"');
replaceContent('resources/views/auth/forgot-password.html', 'class="w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-md text-sm px-5 py-3 text-center flex justify-center items-center gap-2 transition-colors shadow-sm"', 'class="btn-primary w-full"');

// 2. reset-password.html
replaceContent('resources/views/auth/reset-password.html', 'Reset <br/>Ciphers.', 'Reset <br/>Password.');
replaceContent('resources/views/auth/reset-password.html', 'Establish a new cryptographic key for your institutional wealth management portal.', 'Establish a new secure password for your Finzo banking account.');
replaceContent('resources/views/auth/reset-password.html', 'Define your new architectural access key.', 'Please enter and confirm your new password.');
replaceContent('resources/views/auth/reset-password.html', 'New Security Cipher', 'New Password');
replaceContent('resources/views/auth/reset-password.html', 'Confirm Cipher', 'Confirm Password');
replaceContent('resources/views/auth/reset-password.html', 'Initialize Key Update', 'Reset Password');

// 3. admin-login.html
replaceContent('resources/views/auth/admin-login.html', 'Finzo administrative console.', 'Authorized personnel only. Administrative access requires valid credentials.');
replaceContent('resources/views/auth/admin-login.html', 'Invalid root credentials', 'Invalid Admin ID or password');
replaceContent('resources/views/auth/admin-login.html', 'System access restricted due to security protocols.', 'The account has been temporarily locked. This helps protect your information.');

// 4. staff-login.html
replaceContent('resources/views/auth/staff-login.html', 'Finzo employee portal.', 'Authorized personnel only. Secure staff access.');
replaceContent('resources/views/auth/staff-login.html', 'Invalid staff credentials', 'Invalid Staff ID or password');

// 5. login.html
replaceContent('resources/views/auth/login.html', 'Sign in to your account and manage your money.', 'Sign in securely to access your Finzo banking account.');
replaceContent('resources/views/auth/login.html', 'Email or Account Number', 'Email Address or Account Number');
replaceContent('resources/views/auth/login.html', 'Secure Password', 'Password');
replaceContent('resources/views/auth/login.html', 'Sign In to Account', 'Sign In');
if (strpos(file_get_contents('resources/views/auth/login.html'), 'Your connection is encrypted.') === false) {
    replaceContent('resources/views/auth/login.html', '</form>', "<div class=\"mt-4 text-center text-xs text-slate-500 flex items-center justify-center gap-1\"><span class=\"material-symbols-outlined text-[14px]\">lock</span> Your connection is encrypted.</div>\n      </form>");
}

// 6. register.html
replaceContent('resources/views/auth/register.html', 'Join Finzo and start banking in minutes.', 'Create your secure online banking account to begin managing your finances.');
replaceContent('resources/views/auth/register.html', 'Registration incomplete. Please ensure all architectural fields are precisely configured.', 'Please check the highlighted fields and try again.');
if (strpos(file_get_contents('resources/views/auth/register.html'), 'industry-standard security practices') === false) {
    replaceContent('resources/views/auth/register.html', '</form>', "</form>\n<div class=\"mt-4 text-center text-xs text-slate-500 flex items-center justify-center gap-1\"><span class=\"material-symbols-outlined text-[14px]\">verified_user</span> We protect your personal information using industry-standard security practices.</div>");
}

// 7. account-pending.html
replaceContent('resources/views/user/account-pending.html', 'Our team is reviewing your application.<br>You\'ll be notified within 24 hours.', 'Your application is currently under review.<br>We\'ll notify you once verification has been completed.');

echo "UX Copy standardization complete.\n";
