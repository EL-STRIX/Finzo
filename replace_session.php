<?php

$dir = new RecursiveDirectoryIterator(__DIR__);
$iterator = new RecursiveIteratorIterator($dir);

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php' && strpos($file->getPathname(), 'vendor') === false) {
        $content = file_get_contents($file->getPathname());
        
        $modified = false;

        // Skip SessionManager.php itself to avoid modifying the actual implementation
        if (basename($file->getPathname()) === 'SessionManager.php') {
            continue;
        }

        if (strpos($content, 'session_start()') !== false) {
            // Replace standalone \App\Shared\Security\SessionManager::start();
            $content = preg_replace('/session_start\(\);/', '\App\Shared\Security\SessionManager::start();', $content);
            $modified = true;
        }

        if ($modified) {
            file_put_contents($file->getPathname(), $content);
            echo "Updated: " . $file->getPathname() . "\n";
        }
    }
}
echo "Done.";
