/**
 * Finzo Theme Engine v2.1
 * Handles dark/light mode toggle with localStorage persistence.
 * Uses event delegation to support dynamically loaded components (Sidebars/Navbars).
 */
(function() {
  const STORAGE_KEY = 'finzo-theme';

  function applyTheme(theme) {
    const activeTheme = theme || localStorage.getItem(STORAGE_KEY) || 'light';
    
    // Apply to document root
    document.documentElement.setAttribute('data-theme', activeTheme);
    document.documentElement.classList.toggle('dark', activeTheme === 'dark');
    
    // Persist
    localStorage.setItem(STORAGE_KEY, activeTheme);

    // Sync all icons and labels currently in the DOM
    document.querySelectorAll('[data-theme-icon]').forEach(el => {
      el.textContent = activeTheme === 'dark' ? 'light_mode' : 'dark_mode';
    });
    document.querySelectorAll('[data-theme-label]').forEach(el => {
      el.textContent = activeTheme === 'dark' ? 'Light Mode' : 'Dark Mode';
    });
  }

  function toggleTheme() {
    const current = localStorage.getItem(STORAGE_KEY) || 'light';
    const target = current === 'dark' ? 'light' : 'dark';
    applyTheme(target);
  }

  // Initial immediate apply (prevents FOUC)
  const saved = localStorage.getItem(STORAGE_KEY) || 'light';
  document.documentElement.setAttribute('data-theme', saved);
  document.documentElement.classList.toggle('dark', saved === 'dark');

  // Expose globally for manual sync if needed
  window.FinzoTheme = { toggle: toggleTheme, apply: applyTheme };

  // Global Event Delegation for toggle buttons
  document.addEventListener('click', function(e) {
    const toggleBtn = e.target.closest('[data-toggle-theme]');
    if (toggleBtn) {
      e.preventDefault();
      toggleTheme();
    }
  });

  // Sync icons on DOM Content Loaded
  document.addEventListener('DOMContentLoaded', function() {
    applyTheme();
  });
})();

