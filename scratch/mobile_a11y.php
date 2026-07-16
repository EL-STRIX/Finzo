<?php

$cssPath = 'public/assets/css/main.css';
if (file_exists($cssPath)) {
    $css = file_get_contents($cssPath);
    if (strpos($css, 'iOS Zoom Prevention') === false) {
        $a11yCss = <<<CSS

/* WCAG 2.2 AA & iOS Zoom Prevention / Mobile Optimization */
.fin-input, .form-control, select, textarea {
    font-size: 16px !important; /* Prevents iOS auto-zoom on focus */
}

/* Ensure Minimum Touch Target (WCAG 2.2 AA) */
.btn-primary, .btn-secondary, button {
    min-height: 44px !important;
}

CSS;
        file_put_contents($cssPath, $css . $a11yCss);
        echo "Added iOS zoom prevention and minimum touch target fixes to main.css\n";
    }
}
