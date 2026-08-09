tailwind.config = {
  theme: {
    extend: {
      colors: {
        primary: { 50: 'var(--color-primary-50)', 100: 'var(--color-primary-100)', 200: 'var(--color-primary-200)', 300: 'var(--color-primary-300)', 400: 'var(--color-primary-400)', 500: 'var(--color-primary-500)', 600: 'var(--color-primary-600)', 700: 'var(--color-primary-700)', 800: 'var(--color-primary-800)', 900: 'var(--color-primary-900)', base: 'var(--color-primary-base)', hover: 'var(--color-primary-hover)', surface: 'var(--color-primary-surface)' },
        surface: { main: 'var(--color-surface-main)', subtle: 'var(--color-surface-subtle)', hover: 'var(--color-surface-hover)', dark: 'var(--color-surface-dark)', border: 'var(--color-surface-border)' },
        text: { main: 'var(--color-text-main)', muted: 'var(--color-text-muted)', disabled: 'var(--color-text-disabled)', inverse: 'var(--color-text-inverse)' },
        border: { main: 'var(--color-border-main)', subtle: 'var(--color-border-subtle)', focus: 'var(--color-border-focus)' },
        success: { base: 'var(--color-success-base)', hover: 'var(--color-success-hover)', surface: 'var(--color-success-surface)', text: 'var(--color-success-text)' },
        warning: { base: 'var(--color-warning-base)', hover: 'var(--color-warning-hover)', surface: 'var(--color-warning-surface)', text: 'var(--color-warning-text)' },
        danger: { base: 'var(--color-danger-base)', hover: 'var(--color-danger-hover)', surface: 'var(--color-danger-surface)', text: 'var(--color-danger-text)' },
        info: { base: 'var(--color-info-base)', hover: 'var(--color-info-hover)', surface: 'var(--color-info-surface)', text: 'var(--color-info-text)' }
      },
      fontFamily: { display: ['Plus Jakarta Sans', 'sans-serif'], headline: ['Plus Jakarta Sans', 'sans-serif'], body: ['Inter', 'sans-serif'], mono: ['JetBrains Mono', 'monospace'] },
      boxShadow: { 'xs': 'var(--shadow-xs)', 'sm': 'var(--shadow-sm)', 'md': 'var(--shadow-md)', 'lg': 'var(--shadow-lg)', 'dropdown': 'var(--shadow-dropdown)', 'focus': 'var(--shadow-focus)', 'focus-danger': 'var(--shadow-focus-danger)', 'focus-success': 'var(--shadow-focus-success)' },
      borderRadius: { 'sm': 'var(--radius-sm)', 'md': 'var(--radius-md)', 'lg': 'var(--radius-lg)', 'xl': 'var(--radius-xl)', '2xl': 'var(--radius-2xl)', 'full': 'var(--radius-full)' }
    }
  }
};
