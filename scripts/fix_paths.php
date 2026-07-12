<?php
/**
 * FINZO Query-string Path Fixer
 */
$rootDir = dirname(__DIR__); // c:\xampp\htdocs\Finzo

function fixPaths($dir) {
    if (!is_dir($dir)) return;
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            fixPaths($path);
        } else {
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            if (in_array($ext, ['php', 'html', 'js', 'css'])) {
                $content = file_get_contents($path);
                $original = $content;
                
                // Fix missing prefixes caused by query strings in the migration map lookup
                $content = preg_replace_callback('/([\'"])((\.\.\/|\.\/)+)(api|assets)\//', function($m) {
                    $quote = $m[1];
                    $dots = $m[2];
                    $target = $m[4];
                    if ($target === 'api') {
                        return $quote . $dots . 'app/Controllers/';
                    } elseif ($target === 'assets') {
                        return $quote . $dots . 'public/assets/';
                    }
                    return $m[0];
                }, $content);
                
                if ($content !== $original) {
                    file_put_contents($path, $content);
                    echo "Fixed paths in: $path\n";
                }
            }
        }
    }
}

fixPaths($rootDir . '/resources/views');
fixPaths($rootDir . '/public/assets');
fixPaths($rootDir . '/public');
echo "Done fixing query-string paths.\n";
