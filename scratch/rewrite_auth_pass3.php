<?php

$files = [
    'resources/views/user/account-pending.html',
    'resources/views/user/account-rejected.html',
    'resources/views/admin/admin-locked-accounts.html'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;

    $content = file_get_contents($file);

    // Replace typical un-enterprise classes with clean slate
    $content = str_replace('bg-[var(--color-surface-subtle)]', 'bg-slate-50', $content);
    $content = str_replace('text-[var(--color-text-main)]', 'text-slate-900', $content);
    $content = str_replace('border-[var(--color-border-main)]', 'border-slate-200', $content);
    $content = str_replace('bg-primary-base', 'bg-blue-600', $content);
    $content = str_replace('bg-primary-50', 'bg-blue-50', $content);
    $content = str_replace('text-primary-800', 'text-blue-800', $content);
    $content = str_replace('text-primary-600', 'text-blue-600', $content);
    $content = str_replace('bg-primary-600', 'bg-blue-600', $content);

    // Form cards
    $content = str_replace('ds-card', 'bg-white border border-slate-200 rounded-lg p-8 shadow-sm', $content);

    file_put_contents($file, $content);
}

echo "Pass 3 (User Status Views) Complete.";
