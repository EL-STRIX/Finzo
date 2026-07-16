<?php
$dir = 'c:/xampp/htdocs/Finzo/resources/views/auth';
$files = glob($dir . '/*.html');
$broken = [];
foreach ($files as $file) {
    $content = file_get_contents($file);
    preg_match_all('/(href|src|data-href)="([^"]+)"/', $content, $matches);
    foreach ($matches[2] as $link) {
        if (strpos($link, 'http') === 0 || strpos($link, '#') === 0 || strpos($link, 'mailto:') === 0 || strpos($link, 'javascript:') === 0) continue;
        
        $cleanLink = strtok($link, '?');
        // resolve relative paths
        $target = '';
        if (strpos($cleanLink, '../../../public/') === 0) {
            $target = 'c:/xampp/htdocs/Finzo/public/' . substr($cleanLink, 16);
        } else if (strpos($cleanLink, '/') === 0) {
            if (strpos($cleanLink, '/Finzo/') === 0) {
                $target = 'c:/xampp/htdocs/Finzo/' . substr($cleanLink, 7);
            }
        } else {
            $target = $dir . '/' . $cleanLink;
        }
        
        if ($target && !file_exists($target)) {
            $broken[] = basename($file) . ' -> ' . $link . ' (Checked: ' . $target . ')';
        }
    }
}
if (empty($broken)) {
    echo "No broken links found.\n";
} else {
    echo "Broken links:\n" . implode("\n", $broken);
}
