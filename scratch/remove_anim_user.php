<?php
$files = ['resources/views/user/account-pending.html', 'resources/views/user/account-rejected.html'];
foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $newContent = str_replace(['fade-in-up', 'ds-animate-slide-up'], '', $content);
        if ($content !== $newContent) {
            file_put_contents($file, $newContent);
            echo "Removed animations from $file\n";
        }
    }
}
