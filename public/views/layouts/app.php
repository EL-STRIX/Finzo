<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0, viewport-fit=cover" name="viewport" />
    <meta name="description" content="<?= $pageDescription ?? 'Finzo Enterprise Banking' ?>" />
    <title><?= $pageTitle ?? 'Finzo' ?></title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet" />

    <!-- CSS -->
    <link href="/Finzo/public/assets/css/main.css" rel="stylesheet" />
    <link href="/Finzo/public/assets/css/tailwind.css" rel="stylesheet" />

    <!-- Core Scripts -->
    <script defer src="/Finzo/public/assets/js/core/ui-components.js"></script>
    
    <?= $extraHead ?? '' ?>
</head>
<body class="<?= $bodyClass ?? 'bg-surface-subtle font-body text-text-main antialiased' ?>">
    <?= $slot ?? '' ?>
</body>
</html>
