/**
 * Finzo Theme Engine
 * Handles dark/light mode toggle with localStorage persistence.
 * Works for both admin and user panels.
 */
(function() {
  const STORAGE_KEY = 'finzo-theme';

  function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    document.documentElement.classList.toggle('dark', theme === 'dark');
    localStorage.setItem(STORAGE_KEY, theme);

    // Sync all toggle buttons on the page
    document.querySelectorAll('[data-theme-icon]').forEach(el => {
      el.textContent = theme === 'dark' ? 'light_mode' : 'dark_mode';
    });
    document.querySelectorAll('[data-theme-label]').forEach(el => {
      el.textContent = theme === 'dark' ? 'Light Mode' : 'Dark Mode';
    });
  }

  function toggleTheme() {
    const current = localStorage.getItem(STORAGE_KEY) || 'light';
    applyTheme(current === 'dark' ? 'light' : 'dark');
  }

  // Apply saved theme immediately (before DOM paint to prevent flash)
  const saved = localStorage.getItem(STORAGE_KEY) || 'light';
  document.documentElement.setAttribute('data-theme', saved);
  document.documentElement.classList.toggle('dark', saved === 'dark');

  // Expose globally
  window.FinzoTheme = { toggle: toggleTheme, apply: applyTheme };

  // Auto-wire buttons after DOM is ready
  document.addEventListener('DOMContentLoaded', function() {
    applyTheme(saved); // Sync icons

    document.querySelectorAll('[data-toggle-theme]').forEach(btn => {
      btn.addEventListener('click', toggleTheme);
    });
  });
})();
