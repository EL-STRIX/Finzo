tailwind.config = {
  theme: {
    extend: {
      colors: {
        "primary": "#1a3c5e",      // Deep navy
        "primary-dark": "#0f2a44",
        "accent": "#10b981",       // Emerald green
        "accent-dark": "#059669",
        "staff-accent": "#f59e0b", // Amber for Staff
        "admin-accent": "#6366f1", // Indigo for Admin
        "fin-navy": "#0f172a",
        "fin-slate": "#64748b",
        "background": "#f8fafc",   // Light gray bg for pages
        "card": "#ffffff",
        "border": "#e2e8f0"
      },
      fontFamily: { 
        "headline": ["Plus Jakarta Sans", "sans-serif"], 
        "body": ["Inter", "sans-serif"] 
      },
      boxShadow: {
        'soft': '0 4px 20px -2px rgba(0, 0, 0, 0.05)',
        'floating': '0 10px 40px -10px rgba(26, 60, 94, 0.1)',
        'input-focus': '0 0 0 4px rgba(26, 60, 94, 0.1)'
      },
      borderRadius: {
        'xl': '0.75rem',
        '2xl': '1rem',
        '3xl': '1.5rem',
      }
    }
  }
};
