tailwind.config = {
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        primary: { 50: 'var(--color-primary-50)', 100: 'var(--color-primary-100)', 200: 'var(--color-primary-200)', 300: 'var(--color-primary-300)', 400: 'var(--color-primary-400)', 500: 'var(--color-primary-500)', 600: 'var(--color-primary-600)', 700: 'var(--color-primary-700)', 800: 'var(--color-primary-800)', 900: 'var(--color-primary-900)', DEFAULT: 'var(--color-primary)', hover: 'var(--color-primary-hover)', active: 'var(--color-primary-active)' },
        secondary: { 50: 'var(--color-secondary-50)', 500: 'var(--color-secondary-500)', 600: 'var(--color-secondary-600)', 700: 'var(--color-secondary-700)', 800: 'var(--color-secondary-800)', 900: 'var(--color-secondary-900)', DEFAULT: 'var(--color-secondary)', hover: 'var(--color-secondary-hover)' },
        accent: { DEFAULT: 'var(--color-accent)', hover: 'var(--color-accent-hover)' },
        background: 'var(--color-background)',
        surface: { DEFAULT: 'var(--color-surface)', elevated: 'var(--color-surface-elevated)' },
        text: { primary: 'var(--color-text-primary)', secondary: 'var(--color-text-secondary)', muted: 'var(--color-text-muted)', inverse: 'var(--color-text-inverse)' },
        border: { DEFAULT: 'var(--color-border)', subtle: 'var(--color-border-subtle)', strong: 'var(--color-border-strong)' },
        success: { DEFAULT: 'var(--color-success)', bg: 'var(--color-success-bg)' },
        warning: { DEFAULT: 'var(--color-warning)', bg: 'var(--color-warning-bg)' },
        error: { DEFAULT: 'var(--color-error)', bg: 'var(--color-error-bg)' },
        danger: { DEFAULT: 'var(--color-error)', bg: 'var(--color-error-bg)' }, /* Alias for backward compatibility */
        info: { DEFAULT: 'var(--color-info)', bg: 'var(--color-info-bg)' },
        focus: 'var(--color-focus)',
        disabled: { DEFAULT: 'var(--color-disabled)', bg: 'var(--color-disabled-bg)' },
        overlay: 'var(--color-overlay)'
      },
      fontFamily: { display: ['Cormorant', 'Playfair Display', 'serif'], headline: ['Cormorant', 'Playfair Display', 'serif'], body: ['Montserrat', 'sans-serif'], mono: ['JetBrains Mono', 'monospace'] },
      boxShadow: { 'xs': 'var(--shadow-xs)', 'sm': 'var(--shadow-sm)', 'md': 'var(--shadow-md)', 'lg': 'var(--shadow-lg)', 'dropdown': 'var(--shadow-dropdown)', 'focus': 'var(--shadow-focus)', 'focus-error': 'var(--shadow-focus-error)', 'focus-success': 'var(--shadow-focus-success)' },
      borderRadius: { 'sm': 'var(--radius-sm)', 'md': 'var(--radius-md)', 'lg': 'var(--radius-lg)', 'xl': 'var(--radius-xl)', '2xl': 'var(--radius-2xl)', 'full': 'var(--radius-full)' }
    }
  }
};
