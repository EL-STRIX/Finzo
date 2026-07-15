<?php
$css_dir = __DIR__ . '/../public/assets/css';

// Create directories
@mkdir("$css_dir/base", 0777, true);
@mkdir("$css_dir/components", 0777, true);
@mkdir("$css_dir/layouts", 0777, true);

$ds = file_get_contents("$css_dir/design-system.css");

// 1. variables.css (Root + Dark mode)
preg_match('/:root\s*\{.*?\}/is', $ds, $m1);
preg_match('/\.dark\s*\{.*?\}/is', $ds, $m2);
$variables = "/* Variables (Light & Dark) */\n" . $m1[0] . "\n\n" . $m2[0] . "\n";
file_put_contents("$css_dir/base/variables.css", $variables);

// 2. typography.css
// The @import fonts and body, h1, etc.
preg_match('/@import.*?;/i', $ds, $m3);
preg_match('/body\s*\{.*?\}/is', $ds, $m4);
preg_match('/h1.*?\}/is', $ds, $m5);
$typography = "/* Typography */\n" . $m3[0] . "\n\n" . $m4[0] . "\n\n" . $m5[0] . "\n";
file_put_contents("$css_dir/base/typography.css", $typography);

// 3. reset.css
preg_match('/\*\:focus-visible\s*\{.*?\}/is', $ds, $m6);
$reset = "/* Resets */\n" . $m6[0] . "\n";
file_put_contents("$css_dir/base/reset.css", $reset);

// 4. components/buttons.css
preg_match('/\.btn\s*\{.*?\.btn:disabled.*?}/is', $ds, $m7);
$buttons = "/* Buttons */\n" . $m7[0] . "\n";
file_put_contents("$css_dir/components/buttons.css", $buttons);

// 5. components/forms.css
preg_match('/\.form-group\s*\{.*?\.form-control\.is-invalid:focus\s*\{.*?\}/is', $ds, $m8);
$forms = "/* Forms */\n" . $m8[0] . "\n";
file_put_contents("$css_dir/components/forms.css", $forms);

// 6. components/cards.css
preg_match('/\.ds-card\s*\{.*?\.ds-card-footer\s*\{.*?\}/is', $ds, $m9);
$cards = "/* Cards */\n" . $m9[0] . "\n";
file_put_contents("$css_dir/components/cards.css", $cards);

// 7. components/tables.css
preg_match('/\.ds-table-wrapper\s*\{.*?\.ds-table tbody tr:hover\s*\{.*?\}/is', $ds, $m10);
$tables = "/* Tables */\n" . $m10[0] . "\n";
file_put_contents("$css_dir/components/tables.css", $tables);

// 8. components/badges.css
preg_match('/\.ds-badge\s*\{.*?\.ds-badge-info\s*\{.*?\}/is', $ds, $m11);
$badges = "/* Badges */\n" . $m11[0] . "\n";
file_put_contents("$css_dir/components/badges.css", $badges);

// 9. layouts/grid.css
preg_match('/\.ds-container\s*\{.*?\}/is', $ds, $m12);
$grid = "/* Grid */\n" . $m12[0] . "\n";
file_put_contents("$css_dir/layouts/grid.css", $grid);

// 10. base/animations.css
preg_match('/\.ds-animate-fade\s*\{.*?@keyframes dsSlideUp\s*\{.*?\}/is', $ds, $m13);
$animations = "/* Animations */\n" . $m13[0] . "\n";
file_put_contents("$css_dir/base/animations.css", $animations);

// Generate main.css
$mainCss = <<<CSS
/* FINZO ENTERPRISE CSS ARCHITECTURE */
@import 'base/variables.css';
@import 'base/typography.css';
@import 'base/reset.css';
@import 'base/animations.css';

@import 'components/buttons.css';
@import 'components/forms.css';
@import 'components/cards.css';
@import 'components/tables.css';
@import 'components/badges.css';

@import 'layouts/grid.css';
CSS;

file_put_contents("$css_dir/main.css", $mainCss);

echo "CSS Split successful.";
