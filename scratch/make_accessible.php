<?php

// 1. Accessibility Styles (WCAG 2.2 AA Focus Rings & Toggles)
// ALREADY ADDED TO main.css

// 2. Fix HTML forms: add for="id" to labels and aria-labels to toggles
function makeAccessible($dir) {
    $files = glob($dir . '/*.html');
    foreach ($files as $file) {
        $content = file_get_contents($file);
        $original = $content;

        // Pattern 1
        $content = preg_replace_callback('/(<label[^>]*>)(.*?)(<\/label>\s*<div[^>]*>\s*<span[^>]*><\/span>\s*<input[^>]+id=[\'"]([^\'"]+)[\'"])/is', function($matches) {
            $labelStart = $matches[1];
            if (strpos($labelStart, 'for=') === false) {
                $labelStart = str_replace('<label', '<label for="' . $matches[4] . '"', $labelStart);
            }
            return $labelStart . $matches[2] . $matches[3];
        }, $content);

        // Pattern 2
        $content = preg_replace_callback('/(<label[^>]*>)(.*?)(<\/label>.*?<input[^>]+id=[\'"]([^\'"]+)[\'"])/is', function($matches) {
            $labelStart = $matches[1];
            if (strpos($labelStart, 'for=') === false) {
                $labelStart = str_replace('<label', '<label for="' . $matches[4] . '"', $labelStart);
            }
            return $labelStart . $matches[2] . $matches[3];
        }, $content);

        // Fix password toggle icon accessibility
        $content = preg_replace_callback('/(<span[^>]+id=[\'"]toggle-password[\'"][^>]*>)/is', function($matches) {
            $span = $matches[1];
            if (strpos($span, 'role="button"') === false) {
                $span = str_replace('id="toggle-password"', 'id="toggle-password" role="button" tabindex="0" aria-label="Toggle password visibility"', $span);
            }
            return $span;
        }, $content);

        if ($content !== $original) {
            file_put_contents($file, $content);
            echo "Made $file accessible.\n";
        }
    }
}

makeAccessible('resources/views/auth');
makeAccessible('resources/views/user');
