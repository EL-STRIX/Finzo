<?php

$files = [
    'resources/views/auth/admin-login.html',
    'resources/views/auth/forgot-password.html',
    'resources/views/auth/login.html',
    'resources/views/auth/register.html',
    'resources/views/auth/reset-password.html',
    'resources/views/auth/staff-login.html'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;

    $content = file_get_contents($file);

    // 1. Remove background gradients and images from the left panel.
    // Replace <img ...> inside the left panel background with nothing.
    $content = preg_replace('/<img[^>]*customer_bg[^>]*>/', '', $content);
    $content = preg_replace('/<img[^>]*admin_bg[^>]*>/', '', $content);
    $content = preg_replace('/<img[^>]*register-page-image[^>]*>/', '', $content);
    
    // Replace gradient styles
    $content = preg_replace('/style="background:\s*linear-gradient[^"]*"/', 'class="bg-slate-900"', $content);
    $content = preg_replace('/<div class="absolute inset-0 customer-gradient"><\/div>/', '<div class="absolute inset-0 bg-slate-900"></div>', $content);
    
    // 2. Remove "pill" and neon classes
    $content = str_replace('bg-primary-600-600', 'bg-blue-600', $content);
    $content = str_replace('text-primary-600-600', 'text-blue-600', $content);
    $content = str_replace('bg-primary-600', 'bg-blue-600', $content);
    $content = str_replace('text-primary-600', 'text-blue-600', $content);
    $content = str_replace('border-primary-600-600', 'border-blue-600', $content);
    $content = str_replace('text-[#818cf8]', 'text-blue-400', $content);
    $content = str_replace('bg-[var(--color-surface-subtle)]', 'bg-slate-50', $content);

    // 3. Clean up the form card
    $content = str_replace('ds-card p-6 md:p-8 space-y-6 fade-in-up', 'bg-white border border-slate-200 rounded-lg p-8 shadow-sm space-y-6', $content);
    $content = str_replace('ds-card p-6 md:p-8', 'bg-white border border-slate-200 rounded-lg p-8 shadow-sm', $content);
    
    // 4. Clean up input containers
    $content = str_replace('fin-input-container', 'space-y-2', $content);
    $content = str_replace('field-label', 'block text-sm font-semibold text-slate-700', $content);
    $content = str_replace('fin-input', 'w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-md focus:ring-2 focus:ring-blue-600 focus:border-blue-600 block p-3 transition-colors', $content);
    $content = str_replace('has-icon-left', 'pl-10', $content);
    $content = str_replace('input-icon', 'absolute left-3 top-1/2 -translate-y-1/2 text-slate-400', $content);
    
    // 5. Clean up buttons
    $content = str_replace('btn-primary', 'w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-md text-sm px-5 py-3 text-center flex justify-center items-center gap-2 transition-colors shadow-sm', $content);
    $content = str_replace('admin-btn', 'w-full text-white bg-slate-900 hover:bg-slate-800 focus:ring-4 focus:outline-none focus:ring-slate-300 font-medium rounded-md text-sm px-5 py-3 text-center flex justify-center items-center gap-2 transition-colors shadow-sm', $content);

    // 6. Role Switcher Cleanup
    // Role switcher is a group of buttons.
    $content = str_replace('rounded-2xl border border-slate-100', 'rounded-md border border-slate-200 p-1 bg-slate-50', $content);
    $content = preg_replace('/text-\[11px\] font-extrabold uppercase tracking-widest text-white bg-blue-600 rounded-xl shadow-md/', 'text-sm font-semibold text-slate-900 bg-white rounded shadow-sm', $content);
    $content = preg_replace('/text-\[11px\] font-extrabold uppercase tracking-widest text-slate-400 hover:text-slate-600 rounded-xl/', 'text-sm font-medium text-slate-500 hover:text-slate-700 rounded', $content);

    // 7. Typography overrides
    $content = str_replace('font-headline font-extrabold', 'font-bold text-slate-900', $content);
    $content = str_replace('text-[var(--color-text-main)]', 'text-slate-900', $content);
    $content = str_replace('text-[var(--color-text-muted)]', 'text-slate-500', $content);
    $content = str_replace('text-[48px]', 'text-4xl lg:text-5xl', $content);
    $content = preg_replace('/<span class="text-\[10px\] font-black tracking-widest text-blue-600 uppercase">● PERSONAL BANKING<\/span>/', '<span class="text-xs font-bold text-blue-600 uppercase tracking-wider">Personal Banking</span>', $content);
    $content = preg_replace('/<span class="text-\[10px\] font-black tracking-widest text-blue-600 uppercase">● ADMIN CONSOLE<\/span>/', '<span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Admin Console</span>', $content);

    // 8. Fix missing quotes/typos in the original
    $content = str_replace('class="space-y-4 sm:space-y-5 w-full fade-in-up"', '', $content);
    $content = str_replace('class="space-y-5 sm:space-y-6 fade-in-up"', '', $content);

    // 9. Remove all inline styles that might break the pure look
    $content = preg_replace('/\sstyle="[^"]*"/', '', $content);

    file_put_contents($file, $content);
}

echo "Auth UI updated.";
