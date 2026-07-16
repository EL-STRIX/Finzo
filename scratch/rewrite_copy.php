<?php

// Fix login.html
$file = 'resources/views/auth/login.html';
if (file_exists($file)) {
    $content = file_get_contents($file);
    // Update Supporting Description
    $content = str_replace(
        'Sign in to your account and manage your money.',
        'Sign in securely to access your Finzo banking account.',
        $content
    );
    // Add Security Reassurance below the form
    if (strpos($content, 'Your connection is encrypted') === false) {
        $security_html = '<div class="mt-6 text-center text-xs text-slate-500 flex items-center justify-center gap-1"><span class="material-symbols-outlined text-[14px]">lock</span> Your connection is 256-bit encrypted.</div>';
        $content = str_replace('</form>', "</form>\n\n      " . $security_html, $content);
    }
    file_put_contents($file, $content);
}

// Fix register.html
$file = 'resources/views/auth/register.html';
if (file_exists($file)) {
    $content = file_get_contents($file);
    $content = str_replace(
        'Join Finzo and start banking in minutes.',
        'Create your secure online banking account to begin managing your finances.',
        $content
    );
    if (strpos($content, 'protect your personal information') === false) {
        $security_html = '<div class="mt-6 text-center text-xs text-slate-500 flex items-center justify-center gap-1"><span class="material-symbols-outlined text-[14px]">verified_user</span> We protect your personal information using industry-standard security practices.</div>';
        $content = str_replace('</form>', "</form>\n\n                " . $security_html, $content);
    }
    file_put_contents($file, $content);
}

// Fix admin-login.html
$file = 'resources/views/auth/admin-login.html';
if (file_exists($file)) {
    $content = file_get_contents($file);
    $content = str_replace(
        'Finzo administrative console.',
        'Sign in securely to access the Finzo administrative console.',
        $content
    );
    if (strpos($content, 'Your connection is encrypted') === false) {
        $security_html = '<div class="mt-6 text-center text-xs text-slate-500 flex items-center justify-center gap-1"><span class="material-symbols-outlined text-[14px]">lock</span> Your connection is encrypted.</div>';
        $content = str_replace('</form>', "</form>\n\n        " . $security_html, $content);
    }
    file_put_contents($file, $content);
}

// Fix forgot-password.html
$file = 'resources/views/auth/forgot-password.html';
if (file_exists($file)) {
    $content = file_get_contents($file);
    $content = str_replace(
        'We\'ll send a recovery link to your email.',
        'Enter your registered email address to receive password reset instructions.',
        $content
    );
    file_put_contents($file, $content);
}

// Fix staff-login.html
$file = 'resources/views/auth/staff-login.html';
if (file_exists($file)) {
    $content = file_get_contents($file);
    $content = str_replace(
        'Finzo employee portal.',
        'Sign in securely to access the Finzo staff portal.',
        $content
    );
    if (strpos($content, 'Your connection is encrypted') === false) {
        $security_html = '<div class="mt-6 text-center text-xs text-slate-500 flex items-center justify-center gap-1"><span class="material-symbols-outlined text-[14px]">lock</span> Your connection is encrypted.</div>';
        $content = str_replace('</form>', "</form>\n\n        " . $security_html, $content);
    }
    file_put_contents($file, $content);
}

echo "Copy and UX text updated.";
