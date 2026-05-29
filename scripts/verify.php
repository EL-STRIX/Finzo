<?php
$baseDir = dirname(__DIR__);
$issues = [];

// 1. Check .htaccess files
$protectedDirs = ['config', 'middleware', 'database', 'uploads', 'scripts'];
foreach ($protectedDirs as $dir) {
    $htaccess = $baseDir . '/' . $dir . '/.htaccess';
    if (!file_exists($htaccess)) {
        $issues[] = "Missing .htaccess in /$dir/";
    } else {
        $content = file_get_contents($htaccess);
        if ($dir === 'uploads') {
            if (stripos($content, 'php') === false && stripos($content, 'Require all denied') === false && stripos($content, 'engine off') === false) {
                $issues[] = "/$dir/.htaccess does not seem to disable PHP execution";
            }
        } else {
            if (stripos($content, 'Require all denied') === false && stripos($content, 'Deny from all') === false) {
                $issues[] = "/$dir/.htaccess does not have 'Require all denied'";
            }
        }
    }
}

// Helper to get all files
function getAllFiles($dir, $ext = null) {
    $files = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isDir()) continue;
        $path = $file->getPathname();
        // Skip hidden and vendor folders
        if (preg_match('/(\.git|\.idea|\.vscode|vendor|node_modules)/i', $path)) continue;
        if ($ext && pathinfo($path, PATHINFO_EXTENSION) !== $ext) continue;
        $files[] = $path;
    }
    return $files;
}

$phpFiles = getAllFiles($baseDir, 'php');
$htmlFiles = getAllFiles($baseDir, 'html');

// 2. Check require/include paths in PHP files
foreach ($phpFiles as $file) {
    $content = file_get_contents($file);
    if (preg_match_all('/(?:require|require_once|include|include_once)\s*\(?\s*(["\'])(.*?)\1/', $content, $matches)) {
        foreach ($matches[2] as $inc) {
            // Very basic resolution for __DIR__ . '/...'
            if (strpos($inc, '.php') !== false) {
                // If it's a static string like '../config/db.php'
                // Real path resolution in static analysis is hard, but we can do simple checks
                if (!preg_match('/^\$/', $inc)) { 
                    $dir = dirname($file);
                    $target = realpath($dir . '/' . $inc);
                    if (!$target && strpos($inc, '/') !== false && strpos($content, '__DIR__') === false) {
                        $issues[] = "Broken PHP include in " . basename($file) . ": " . $inc;
                    }
                }
            }
        }
    }
    // Check for __DIR__ . '/path'
    if (preg_match_all('/(?:require|require_once|include|include_once)\s*\(?\s*__DIR__\s*\.\s*(["\'])(.*?)\1/', $content, $matches)) {
        foreach ($matches[2] as $inc) {
            $dir = dirname($file);
            $target = realpath($dir . $inc);
            if (!$target) {
                $issues[] = "Broken __DIR__ include in " . str_replace($baseDir, '', $file) . ": " . $inc;
            }
        }
    }
    
    if (preg_match_all('/(?:require|require_once|include|include_once)\s*\(?\s*dirname\(__DIR__.*?\)?[^"\']*?(["\'])(.*?)\1/', $content, $matches)) {
         foreach ($matches[2] as $inc) {
            // basic check
            $dir = dirname(dirname($file)); // assuming level 1
            if (strpos(file_get_contents($file), 'dirname(__DIR__, 2)') !== false) {
                 $dir = dirname(dirname(dirname($file)));
            }
            $target = realpath($dir . $inc);
            if (!$target && !file_exists($dir . $inc)) {
                 $issues[] = "Broken dirname include in " . str_replace($baseDir, '', $file) . ": " . $inc;
            }
         }
    }
}

// 3. Check src/href in HTML files
foreach ($htmlFiles as $file) {
    $content = file_get_contents($file);
    if (preg_match_all('/(?:src|href)=["\']([^"\']+)["\']/', $content, $matches)) {
        foreach ($matches[1] as $link) {
            if (strpos($link, 'http') === 0 || strpos($link, '//') === 0 || strpos($link, 'mailto:') === 0 || strpos($link, 'tel:') === 0 || $link === '#') continue;
            
            // Remove query params
            $linkParts = explode('?', $link);
            $cleanLink = $linkParts[0];
            
            if (empty($cleanLink)) continue;
            
            $dir = dirname($file);
            $target = realpath($dir . '/' . $cleanLink);
            if (!$target) {
                // Sometimes paths are absolute from root like /assets/...
                // But this project uses relative like ../../assets/...
                $issues[] = "Broken HTML link in " . str_replace($baseDir, '', $file) . ": " . $link;
            }
        }
    }
}

// 4. Check Duplicate PHP functions
$functions = [];
foreach ($phpFiles as $file) {
    $content = file_get_contents($file);
    if (preg_match_all('/function\s+([a-zA-Z0-9_]+)\s*\(/i', $content, $matches)) {
        foreach ($matches[1] as $func) {
            $func = strtolower($func);
            if (!isset($functions[$func])) {
                $functions[$func] = [];
            }
            $functions[$func][] = str_replace($baseDir, '', $file);
        }
    }
}

foreach ($functions as $func => $locs) {
    $locs = array_unique($locs);
    if (count($locs) > 1) {
        $issues[] = "Duplicate function '$func' found in: " . implode(", ", $locs);
    }
}

echo json_encode([
    'total_issues' => count($issues),
    'issues' => $issues
], JSON_PRETTY_PRINT);

