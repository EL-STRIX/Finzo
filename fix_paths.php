<?php
$dirs = [
    'c:/xampp/htdocs/Finzo/resources/views',
    'c:/xampp/htdocs/Finzo/public/assets/js',
    'c:/xampp/htdocs/Finzo/public'
];

function processDir($dir) {
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            processDir($path);
        } else {
            if (pathinfo($path, PATHINFO_EXTENSION) === 'html' || pathinfo($path, PATHINFO_EXTENSION) === 'js') {
                $content = file_get_contents($path);
                $original = $content;
                
                // Replace ../../../public/ with /Finzo/public/
                // Replace ../../../resources/ with /Finzo/resources/
                // Replace ../../../index.html with /Finzo/public/index.html
                // Replace ../../../../public/ with /Finzo/public/
                // Replace ../../../../resources/ with /Finzo/resources/
                
                $content = preg_replace('/(\.\.\/)+public\//', '/Finzo/public/', $content);
                $content = preg_replace('/(\.\.\/)+resources\//', '/Finzo/resources/', $content);
                $content = preg_replace('/(\.\.\/)+index\.html/', '/Finzo/public/index.html', $content);
                
                if ($content !== $original) {
                    file_put_contents($path, $content);
                    echo "Updated: $path\n";
                }
            }
        }
    }
}

foreach ($dirs as $dir) {
    processDir($dir);
}
