<?php
/**
 * Frontend Architecture Refactoring Script
 * Automates extraction of inline JS/CSS across 48 HTML files.
 */

$views_dir = __DIR__ . '/../resources/views';
$js_dir = __DIR__ . '/../public/assets/js/pages';
$css_dir = __DIR__ . '/../public/assets/css/pages';

// Ensure output directories exist
if (!is_dir($js_dir)) mkdir($js_dir, 0777, true);
if (!is_dir($css_dir)) mkdir($css_dir, 0777, true);

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($views_dir));

$metrics = [
    'files_processed' => 0,
    'inline_scripts_extracted' => 0,
    'inline_styles_extracted' => 0,
    'onclicks_removed' => 0
];

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'html') {
        $path = $file->getPathname();
        $relativePath = str_replace($views_dir . DIRECTORY_SEPARATOR, '', $path);
        
        // Setup file paths
        $role = dirname($relativePath); // e.g., 'auth', 'user', 'admin'
        if ($role === '.') $role = 'public';
        $basename = $file->getBasename('.html');
        
        $page_js_dir = "$js_dir/$role";
        $page_css_dir = "$css_dir/$role";
        if (!is_dir($page_js_dir)) mkdir($page_js_dir, 0777, true);
        if (!is_dir($page_css_dir)) mkdir($page_css_dir, 0777, true);
        
        $js_file_path = "$page_js_dir/$basename.js";
        $css_file_path = "$page_css_dir/$basename.css";
        
        $html = file_get_contents($path);
        
        $js_content = "";
        $css_content = "";
        
        // 1. Extract <script> without src
        $html = preg_replace_callback('/<script(?![^>]*src=)([^>]*)>(.*?)<\/script>/is', function($matches) use (&$js_content, &$metrics) {
            $js_content .= trim($matches[2]) . "\n\n";
            $metrics['inline_scripts_extracted']++;
            return ''; // Remove from HTML
        }, $html);
        
        // 2. Extract <style>
        $html = preg_replace_callback('/<style[^>]*>(.*?)<\/style>/is', function($matches) use (&$css_content, &$metrics) {
            $css_content .= trim($matches[1]) . "\n\n";
            $metrics['inline_styles_extracted']++;
            return ''; // Remove from HTML
        }, $html);
        
        // 3. Extract onclick
        $html = preg_replace_callback('/onclick=(["\'])(.*?)\1/is', function($matches) use (&$js_content, &$metrics) {
            $code = $matches[2];
            $metrics['onclicks_removed']++;
            if (strpos($code, 'window.location.href=') !== false) {
                // It's a navigation link
                preg_match('/window\.location\.href=[\'"]([^\'"]+)[\'"]/', $code, $navMatch);
                if (!empty($navMatch[1])) {
                    return 'data-href="' . $navMatch[1] . '"';
                }
            }
            // For other onclicks, return data-action="functionName" and we will manually map it or rely on global helpers
            // A quick hack: data-action="code"
            $safeCode = htmlspecialchars($code, ENT_QUOTES);
            return 'data-action="' . $safeCode . '"';
        }, $html);
        
        // 4. Inject CSS and JS links if they were extracted or even if empty (to follow architecture)
        // Inject CSS before </head>
        $css_link = '<link rel="stylesheet" href="/Finzo/public/assets/css/pages/' . str_replace('\\', '/', $role) . '/' . $basename . '.css"/>';
        if (strpos($html, $css_link) === false) {
            $html = preg_replace('/<\/head>/i', "  $css_link\n</head>", $html);
        }
        
        // Inject JS before </body>
        $js_link = '<script src="/Finzo/public/assets/js/pages/' . str_replace('\\', '/', $role) . '/' . $basename . '.js"></script>';
        if (strpos($html, $js_link) === false) {
            $html = preg_replace('/<\/body>/i', "$js_link\n</body>", $html);
        }
        
        // Write files
        file_put_contents($path, $html);
        if (!empty(trim($js_content))) file_put_contents($js_file_path, trim($js_content));
        if (!empty(trim($css_content))) file_put_contents($css_file_path, trim($css_content));
        
        $metrics['files_processed']++;
    }
}

echo json_encode($metrics, JSON_PRETTY_PRINT);
