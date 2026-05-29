<?php
$dir = __DIR__;
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$phpFiles = [];
$cssFiles = [];
$jsFiles = [];
$htmlFiles = [];

foreach ($files as $file) {
    if ($file->isDir()) continue;
    $path = $file->getPathname();
    // skip .git, .idea, .vscode, vendor, node_modules
    if (preg_match('/(\.git|\.idea|\.vscode|vendor|node_modules)/i', $path)) continue;
    
    $ext = pathinfo($path, PATHINFO_EXTENSION);
    if ($ext === 'php') $phpFiles[] = $path;
    if ($ext === 'css') $cssFiles[] = $path;
    if ($ext === 'js') $jsFiles[] = $path;
    if ($ext === 'html') $htmlFiles[] = $path;
}

$functions = [];
$duplicates = [];

// Parse PHP files
foreach ($phpFiles as $file) {
    $content = file_get_contents($file);
    // Find function definitions
    if (preg_match_all('/function\s+([a-zA-Z0-9_]+)\s*\(/i', $content, $matches)) {
        foreach ($matches[1] as $func) {
            $func = strtolower($func);
            if (!isset($functions[$func])) {
                $functions[$func] = [];
            }
            $functions[$func][] = str_replace($dir, '', $file);
        }
    }
}

foreach ($functions as $func => $locs) {
    if (count($locs) > 1) {
        $duplicates[$func] = array_unique($locs);
    }
}

echo "Duplicate PHP Functions:\n";
foreach ($duplicates as $func => $locs) {
    if (count($locs) > 1) {
        echo "- $func in: " . implode(", ", $locs) . "\n";
    }
}

// Find unreferenced PHP files
$allContents = '';
foreach (array_merge($phpFiles, $jsFiles, $htmlFiles) as $file) {
    $allContents .= file_get_contents($file);
}

echo "\nPotentially Unused PHP Files:\n";
foreach ($phpFiles as $file) {
    $basename = basename($file);
    if ($basename === 'index.php' || $basename === 'config.php' || $basename === 'db.php') continue;
    if (strpos($allContents, $basename) === false) {
        echo "- " . str_replace($dir, '', $file) . "\n";
    }
}

echo "\nPotentially Unused JS Files:\n";
foreach ($jsFiles as $file) {
    $basename = basename($file);
    if (strpos($allContents, $basename) === false) {
        echo "- " . str_replace($dir, '', $file) . "\n";
    }
}
