tailwind.config = {
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        // Core Brand Colors
        primary: {
          50: '#f0f9ff',
          100: '#e0f2fe',
          200: '#bae6fd',
          300: '#7dd3fc',
          400: '#38bdf8',
          500: '#0ea5e9',
          600: '#0284c7', // Base Primary
          700: '#0369a1', // Primary Hover
          800: '#075985',
          900: '#0c4a6e',
        },
        // Semantic Colors
        success: { base: '#10b981', hover: '#059669', surface: '#d1fae5', text: '#065f46' },
        warning: { base: '#f59e0b', hover: '#d97706', surface: '#fef3c7', text: '#92400e' },
        danger: { base: '#ef4444', hover: '#dc2626', surface: '#fee2e2', text: '#991b1b' },
        info: { base: '#3b82f6', hover: '#2563eb', surface: '#dbeafe', text: '#1e40af' },
        // Surface & Backgrounds
        surface: {
          light: '#ffffff',
          dark: '#1e293b',
          subtle: '#f8fafc',
          hover: '#f1f5f9',
          border: '#e2e8f0',
        },
        // Typography
        text: {
          main: '#0f172a',
          muted: '#64748b',
          disabled: '#94a3b8',
          inverse: '#ffffff'
        }
      },
      fontFamily: {
        display: ['Plus Jakarta Sans', 'sans-serif'],
        body: ['Inter', 'sans-serif'],
        mono: ['JetBrains Mono', 'monospace'],
      },
      spacing: {
        '4xs': '4px',
        '3xs': '8px',
        '2xs': '12px',
        'xs': '16px',
        'sm': '20px',
        'md': '24px',
        'lg': '32px',
        'xl': '40px',
        '2xl': '48px',
        '3xl': '64px',
      },
      boxShadow: {
        'soft-sm': '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
        'soft-md': '0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03)',
        'soft-lg': '0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.03)',
        'soft-xl': '0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02)',
        'focus-ring': '0 0 0 4px rgba(2, 132, 199, 0.15)',
        'dropdown': '0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1)',
      },
      borderRadius: {
        'sm': '4px',
        'md': '8px',
        'lg': '12px',
        'xl': '16px',
        '2xl': '24px',
        'full': '9999px',
      }
    }
  }
};
