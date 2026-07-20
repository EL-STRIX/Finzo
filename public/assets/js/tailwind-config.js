tailwind.config = {
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        // Core Brand Colors
        primary: {
          50: '#f4f7f9',
          100: '#e1e5e9',
          200: '#d1d9e0',
          300: '#b0b8c1',
          400: '#6e7a87',
          500: '#4a5560',
          600: '#0A2540', // Base Primary
          700: '#061A2D', // Primary Hover
          800: '#03101C',
          900: '#01080e',
        },
        // Semantic Colors
        success: { base: '#0E793C', hover: '#0B6331', surface: '#E8F5E9', text: '#084823' },
        warning: { base: '#B97200', hover: '#975D00', surface: '#FFF8E1', text: '#754800' },
        danger: { base: '#C62828', hover: '#A32121', surface: '#FFEBEE', text: '#7E1919' },
        info: { base: '#185ABB', hover: '#134A99', surface: '#E3F2FD', text: '#0E3773' },
        // Surface & Backgrounds
        surface: {
          light: '#ffffff',
          dark: '#1e293b',
          subtle: '#F4F7F9',
          hover: '#E6E9EC',
          border: '#D1D9E0',
        },
        // Typography
        text: {
          main: '#111820',
          muted: '#6E7A87',
          disabled: '#A0AAB4',
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
        'soft-sm': '0 1px 2px 0 rgba(10, 37, 64, 0.04)',
        'soft-md': '0 4px 12px rgba(10, 37, 64, 0.05), 0 1px 3px rgba(10, 37, 64, 0.03)',
        'soft-lg': '0 10px 20px rgba(10, 37, 64, 0.08), 0 4px 6px rgba(10, 37, 64, 0.04)',
        'soft-xl': '0 20px 30px rgba(10, 37, 64, 0.1)',
        'focus-ring': '0 0 0 3px rgba(10, 37, 64, 0.2)',
        'dropdown': '0 10px 25px -5px rgba(10, 37, 64, 0.1), 0 8px 10px -6px rgba(10, 37, 64, 0.1)',
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
