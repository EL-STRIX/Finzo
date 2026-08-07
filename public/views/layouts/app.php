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
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              primary: {
                50: 'var(--color-primary-50)',
                100: 'var(--color-primary-100)',
                200: 'var(--color-primary-200)',
                300: 'var(--color-primary-300)',
                400: 'var(--color-primary-400)',
                500: 'var(--color-primary-500)',
                600: 'var(--color-primary-600)',
                700: 'var(--color-primary-700)',
                800: 'var(--color-primary-800)',
                900: 'var(--color-primary-900)',
                base: 'var(--color-primary-base)',
                hover: 'var(--color-primary-hover)',
                surface: 'var(--color-primary-surface)',
              },
              surface: {
                main: 'var(--color-surface-main)',
                subtle: 'var(--color-surface-subtle)',
                hover: 'var(--color-surface-hover)',
              },
              text: {
                main: 'var(--color-text-main)',
                muted: 'var(--color-text-muted)',
                disabled: 'var(--color-text-disabled)',
                inverse: 'var(--color-text-inverse)',
              },
              border: {
                main: 'var(--color-border-main)',
                subtle: 'var(--color-border-subtle)',
                focus: 'var(--color-border-focus)',
              },
              success: {
                base: 'var(--color-success-base)',
                hover: 'var(--color-success-hover)',
                surface: 'var(--color-success-surface)',
                text: 'var(--color-success-text)',
              },
              warning: {
                base: 'var(--color-warning-base)',
                hover: 'var(--color-warning-hover)',
                surface: 'var(--color-warning-surface)',
                text: 'var(--color-warning-text)',
              },
              danger: {
                base: 'var(--color-danger-base)',
                hover: 'var(--color-danger-hover)',
                surface: 'var(--color-danger-surface)',
                text: 'var(--color-danger-text)',
              },
              info: {
                base: 'var(--color-info-base)',
                hover: 'var(--color-info-hover)',
                surface: 'var(--color-info-surface)',
                text: 'var(--color-info-text)',
              }
            },
            fontFamily: {
              display: 'var(--font-display)',
              body: 'var(--font-body)',
              mono: 'var(--font-mono)',
            },
            boxShadow: {
              'xs': 'var(--shadow-xs)',
              'sm': 'var(--shadow-sm)',
              'md': 'var(--shadow-md)',
              'lg': 'var(--shadow-lg)',
              'dropdown': 'var(--shadow-dropdown)',
              'focus': 'var(--shadow-focus)',
              'focus-danger': 'var(--shadow-focus-danger)',
              'focus-success': 'var(--shadow-focus-success)',
            },
            borderRadius: {
              'sm': 'var(--radius-sm)',
              'md': 'var(--radius-md)',
              'lg': 'var(--radius-lg)',
              'xl': 'var(--radius-xl)',
              '2xl': 'var(--radius-2xl)',
              'full': 'var(--radius-full)',
            }
          }
        }
      }
    </script>

    <!-- Core Scripts -->
    <script defer src="/Finzo/public/assets/js/core/ui-components.js"></script>
    
    <?= $extraHead ?? '' ?>
</head>
<body class="<?= $bodyClass ?? 'bg-surface-subtle font-body text-text-main antialiased' ?>">
    <?= $slot ?? '' ?>
</body>
</html>
