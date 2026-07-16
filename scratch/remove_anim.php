<?php
$files = glob('resources/views/auth/*.html');
foreach ($files as $file) {
    $content = file_get_contents($file);
    $newContent = str_replace(['fade-in-up', 'ds-animate-slide-up'], '', $content);
    if ($content !== $newContent) {
        file_put_contents($file, $newContent);
        echo "Removed animations from $file\n";
    }
}
