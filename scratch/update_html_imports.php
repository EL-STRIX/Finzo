<?php
$views_dir = __DIR__ . '/../resources/views';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($views_dir));

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'html') {
        $path = $file->getPathname();
        $html = file_get_contents($path);
        
        // Remove all instances of old CSS imports
        $html = preg_replace('/<link[^>]*href="[^"]*(design-system\.css|user\.css|user-panel\.css|admin\.css|custom\.css|dark-mode\.css)"[^>]*>\s*/is', '', $html);
        
        // Ensure main.css is added right after Tailwind config if not already there
        if (strpos($html, 'main.css') === false) {
            // Find tailwind-config.js import
            if (strpos($html, 'tailwind-config.js') !== false) {
                $html = preg_replace('/(<script[^>]*src="[^"]*tailwind-config\.js"[^>]*><\/script>)/is', "$1\n  <link href=\"/Finzo/public/assets/css/main.css\" rel=\"stylesheet\"/>", $html);
            } else {
                $html = preg_replace('/<\/head>/i', "  <link href=\"/Finzo/public/assets/css/main.css\" rel=\"stylesheet\"/>\n</head>", $html);
            }
        }
        
        file_put_contents($path, $html);
    }
}
echo "HTML imports updated.\n";
