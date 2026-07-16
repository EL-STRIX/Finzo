<?php
// 1. Remove .hidden !important from admin.css and user.css
$files = [
    'c:/xampp/htdocs/Finzo/public/assets/css/layouts/admin.css',
    'c:/xampp/htdocs/Finzo/public/assets/css/layouts/user.css'
];
foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $content = str_replace('.hidden { display: none !important; }', '/* .hidden { display: none !important; } */', $content);
        $content = str_replace('.modal-overlay.hidden{display:none!important;}', '/* .modal-overlay.hidden{display:none!important;} */', $content);
        $content = str_replace('.modal-overlay.hidden { display: none !important; }', '/* .modal-overlay.hidden { display: none !important; } */', $content);
        $content = str_replace('.btn.btn-outline.hidden { display: none !important; }', '/* .btn.btn-outline.hidden { display: none !important; } */', $content);
        file_put_contents($file, $content);
    }
}

// 2. Add full primary palette to variables.css
$varFile = 'c:/xampp/htdocs/Finzo/public/assets/css/base/variables.css';
if (file_exists($varFile)) {
    $content = file_get_contents($varFile);
    $primaryFull = "  --color-primary-50: #f0f9ff;\n  --color-primary-100: #e0f2fe;\n  --color-primary-200: #bae6fd;\n  --color-primary-300: #7dd3fc;\n  --color-primary-400: #38bdf8;\n  --color-primary-500: #0ea5e9;\n  --color-primary-600: #0284c7;\n  --color-primary-700: #0369a1;\n  --color-primary-800: #075985;\n  --color-primary-900: #0c4a6e;\n";
    if (strpos($content, '--color-primary-900') === false) {
        $content = str_replace('--color-primary-base: #0284c7;', $primaryFull . "  --color-primary-base: #0284c7;", $content);
        file_put_contents($varFile, $content);
    }
}

// 3. Add .fin-input missing components to forms.css
$formsFile = 'c:/xampp/htdocs/Finzo/public/assets/css/components/forms.css';
if (file_exists($formsFile)) {
    $content = file_get_contents($formsFile);
    if (strpos($content, '.fin-input-container') === false) {
        $finInputCSS = "
/* Finzo Enterprise Auth Form Inputs */
.fin-input-container {
  display: flex;
  flex-direction: column;
  gap: var(--space-3xs);
  margin-bottom: var(--space-xs);
  position: relative;
}
.fin-input {
  width: 100%;
  height: 48px;
  background-color: var(--color-surface-main);
  border: 1.5px solid var(--color-border-main);
  border-radius: var(--radius-xl);
  font-family: var(--font-body);
  font-size: 14px;
  color: var(--color-text-main);
  transition: all var(--transition-fast);
  padding: 0 var(--space-md);
}
.fin-input.has-icon-left {
  padding-left: 2.75rem;
}
.fin-input:focus {
  border-color: var(--color-primary-base);
  box-shadow: var(--shadow-focus);
}
.fin-input::placeholder {
  color: var(--color-text-disabled);
}
.input-icon {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--color-text-muted);
  font-size: 20px;
  pointer-events: none;
}
.field-label {
  font-family: var(--font-display);
  font-size: 13px;
  font-weight: 600;
  color: var(--color-text-main);
}
.error-msg {
  display: none;
  font-size: 12px;
  color: var(--color-danger-base);
  margin-top: 4px;
}
";
        file_put_contents($formsFile, $content . "\n" . $finInputCSS);
    }
}

// 4. Update buttons.css so .btn-primary is standalone
$btnsFile = 'c:/xampp/htdocs/Finzo/public/assets/css/components/buttons.css';
if (file_exists($btnsFile)) {
    $content = file_get_contents($btnsFile);
    $content = str_replace('.btn {', '.btn, .btn-primary, .btn-secondary, .btn-ghost, .btn-danger {', $content);
    file_put_contents($btnsFile, $content);
}

echo "CSS files patched successfully.";
