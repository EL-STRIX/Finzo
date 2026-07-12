<?php
/**
 * FINZO Enterprise Architecture Restructuring Script
 */

$rootDir = dirname(__DIR__); // c:\xampp\htdocs\Finzo

$directoriesToCreate = [
    'app/Controllers/Admin',
    'app/Controllers/Auth',
    'app/Controllers/Pdf',
    'app/Controllers/Public',
    'app/Controllers/Staff',
    'app/Controllers/Uploads',
    'app/Controllers/User',
    'app/Models',
    'app/Services',
    'app/Repositories',
    'app/Middleware',
    'app/Helpers',
    'app/Validators',
    'app/Exceptions',
    'app/Traits',
    'app/Libraries',
    'app/Core',
    'bootstrap',
    'database/migrations',
    'database/seeders',
    'database/schema',
    'database/backup',
    'database/factories',
    'public/assets/css',
    'public/assets/js',
    'public/assets/images',
    'public/assets/fonts',
    'public/assets/vendors',
    'public/uploads/profile',
    'public/uploads/kyc',
    'public/uploads/documents',
    'public/uploads/transactions',
    'public/uploads/temp',
    'public/uploads/exports',
    'resources/views/layouts',
    'resources/views/components',
    'resources/views/admin',
    'resources/views/user',
    'resources/views/auth',
    'resources/views/errors',
    'resources/views/emails',
    'resources/views/staff',
    'resources/lang',
    'routes',
    'storage/cache',
    'storage/framework',
    'storage/logs',
    'storage/sessions',
    'storage/temp',
    'storage/exports',
    'storage/backups',
    'tests/Unit',
    'tests/Feature',
    'tests/Integration',
    'tests/Performance',
    'docs',
    'scripts'
];

foreach ($directoriesToCreate as $dir) {
    $path = $rootDir . '/' . $dir;
    if (!is_dir($path)) {
        mkdir($path, 0777, true);
    }
}

// 1. Define mapping rules (Old Prefix -> New Prefix)
$mappings = [
    'api/' => 'app/Controllers/',
    'assets/' => 'public/assets/',
    'database/' => 'database/schema/',
    'middleware/' => 'app/Middleware/',
    'uploads/' => 'public/uploads/',
    'views/' => 'resources/views/',
    'index.html' => 'public/index.html'
];

// Special cases to skip moving via prefix mapping (they stay or move specifically)
$skipPrefixMove = [
    'config/',
    'scripts/',
    'database/schema/',
    'public/',
    'app/',
    'bootstrap/',
    'resources/',
    'routes/',
    'storage/',
    'tests/',
    'docs/',
    'vendor/'
];

// 2. Scan all files and build exact old->new map
$fileMap = [];
$filesToMove = [];

function scanDirRecursive($dir, $baseDir, &$files, &$skipPrefixMove) {
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..' || $item === '.git' || $item === '.idea' || $item === '.vscode' || $item === 'C-GAMES-COLLECTION') continue;
        
        $fullPath = $dir . '/' . $item;
        $relPath = str_replace('\\', '/', str_replace($baseDir . '/', '', $fullPath));
        
        if (is_dir($fullPath)) {
            $skip = false;
            foreach ($skipPrefixMove as $skipPrefix) {
                if (strpos($relPath . '/', $skipPrefix) === 0) {
                    $skip = true;
                    break;
                }
            }
            if (!$skip || $relPath === 'database' || $relPath === 'uploads') { // database and uploads have specific mappings
                scanDirRecursive($fullPath, $baseDir, $files, $skipPrefixMove);
            }
            if ($skip && $relPath !== 'database' && $relPath !== 'uploads') {
                 // Even if skipped for move, we might need to process its contents for string replacement
                 if (in_array($relPath, ['config', 'scripts'])) {
                     scanDirRecursive($fullPath, $baseDir, $files, $skipPrefixMove);
                 }
            }
        } else {
            $files[] = $relPath;
        }
    }
}

$allFiles = [];
scanDirRecursive($rootDir, $rootDir, $allFiles, $skipPrefixMove);

foreach ($allFiles as $relPath) {
    $newRelPath = $relPath;
    
    // Check if it should be moved
    foreach ($mappings as $oldPrefix => $newPrefix) {
        if (strpos($relPath, $oldPrefix) === 0) {
            $newRelPath = $newPrefix . substr($relPath, strlen($oldPrefix));
            break;
        }
    }
    
    // Exception for root index.html
    if ($relPath === 'index.html') {
        $newRelPath = 'public/index.html';
    }
    
    $fileMap[$relPath] = $newRelPath;
}

// Ensure target directories exist for all mapped files
foreach ($fileMap as $old => $new) {
    if ($old !== $new) {
        $targetDir = dirname($rootDir . '/' . $new);
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
    }
}

// 3. Move files
foreach ($fileMap as $old => $new) {
    if ($old !== $new) {
        rename($rootDir . '/' . $old, $rootDir . '/' . $new);
    }
}

echo "Files moved successfully.\n";

// Helper to count directory depth
function getDepth($path) {
    return substr_count($path, '/');
}

// Helper to get relative path between two files
function getRelativePath($fromDir, $toPath) {
    $fromParts = explode('/', $fromDir);
    $toParts = explode('/', $toPath);
    
    while(count($fromParts) > 0 && count($toParts) > 0 && $fromParts[0] === $toParts[0]) {
        array_shift($fromParts);
        array_shift($toParts);
    }
    
    $path = str_repeat('../', count($fromParts));
    $path .= implode('/', $toParts);
    if ($path === '') $path = './';
    return $path;
}

// Helper to resolve a relative path from a base directory
function resolvePath($baseDir, $relPath) {
    $parts = explode('/', $baseDir);
    $relParts = explode('/', $relPath);
    
    foreach ($relParts as $p) {
        if ($p === '.') continue;
        if ($p === '..') {
            array_pop($parts);
        } else {
            array_push($parts, $p);
        }
    }
    return implode('/', $parts);
}

// 4. Update References in all files
foreach ($fileMap as $oldPath => $newPath) {
    $fullNewPath = $rootDir . '/' . $newPath;
    $ext = pathinfo($fullNewPath, PATHINFO_EXTENSION);
    
    if (in_array($ext, ['php', 'html', 'js', 'css'])) {
        $content = file_get_contents($fullNewPath);
        $originalContent = $content;
        
        $oldDir = dirname($oldPath);
        if ($oldDir === '.') $oldDir = '';
        
        $newDir = dirname($newPath);
        if ($newDir === '.') $newDir = '';

        // Replace dirname(__DIR__, N)
        if ($ext === 'php') {
            $content = preg_replace_callback('/dirname\(\s*__DIR__\s*,\s*(\d+)\s*\)/', function($m) use ($oldDir, $newDir) {
                $oldDepth = $oldDir === '' ? 0 : getDepth($oldDir) + 1;
                $newDepth = $newDir === '' ? 0 : getDepth($newDir) + 1;
                $diff = $newDepth - $oldDepth;
                $newN = (int)$m[1] + $diff;
                return "dirname(__DIR__, $newN)";
            }, $content);
        }
        
        // Replace relative paths (../ or ./)
        $content = preg_replace_callback('/([\'"])((\.\.\/|\.\/)[^\'"]+)([\'"])/', function($m) use ($oldDir, $newDir, $fileMap) {
            $quote = $m[1];
            $relPath = $m[2];
            
            $targetOld = resolvePath($oldDir, $relPath);
            
            // Check if target was moved
            if (isset($fileMap[$targetOld])) {
                $targetNew = $fileMap[$targetOld];
                $newRel = getRelativePath($newDir, $targetNew);
                return $quote . $newRel . $quote;
            } else {
                // If it wasn't moved (e.g. config which stayed in config), but the caller moved!
                // We still need to calculate the new relative path to it.
                $newRel = getRelativePath($newDir, $targetOld);
                return $quote . $newRel . $quote;
            }
        }, $content);
        
        // Also fix api.js API_BASE logic
        if ($newPath === 'public/assets/js/core/api.js') {
             $content = str_replace("/assets/js/.*", "/public/assets/js/.*", $content);
             $content = str_replace(" + '/api'", " + '/app/Controllers'", $content);
        }

        if ($content !== $originalContent) {
            file_put_contents($fullNewPath, $content);
        }
    }
}

echo "References updated successfully.\n";

// 5. Create root index.php for redirection
$indexPhp = <<<'PHP'
<?php
/**
 * FINZO - Root Redirect
 * Redirects all root traffic to the public/ directory.
 */
header("Location: public/index.html");
exit;
PHP;

file_put_contents($rootDir . '/index.php', $indexPhp);
echo "Root index.php created.\n";

// 6. Cleanup empty directories
function removeEmptyDirs($dir) {
    if (!is_dir($dir)) return;
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            removeEmptyDirs($path);
        }
    }
    $items = scandir($dir);
    if (count($items) === 2) { // only . and ..
        rmdir($dir);
    }
}

removeEmptyDirs($rootDir . '/api');
removeEmptyDirs($rootDir . '/assets');
removeEmptyDirs($rootDir . '/database');
removeEmptyDirs($rootDir . '/middleware');
removeEmptyDirs($rootDir . '/uploads');
removeEmptyDirs($rootDir . '/views');

echo "Cleanup completed.\n";
