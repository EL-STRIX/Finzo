/**
 * Finzo Core Theme Engine
 * Manages dark/light mode with full DOM synchronization.
 */
(function() {
    const STORAGE_KEY = 'finzo-theme';

    function applyTheme(theme) {
        const activeTheme = theme || localStorage.getItem(STORAGE_KEY) || 'light';
        
        document.documentElement.setAttribute('data-theme', activeTheme);
        document.documentElement.classList.toggle('dark', activeTheme === 'dark');
        
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

    // Immediate execution to prevent FOUC
    const saved = localStorage.getItem(STORAGE_KEY) || 'light';
    document.documentElement.setAttribute('data-theme', saved);
    document.documentElement.classList.toggle('dark', saved === 'dark');

    window.FinzoTheme = { 
        toggle: toggleTheme, 
        apply: applyTheme,
        current: () => localStorage.getItem(STORAGE_KEY) || 'light'
    };

    // Global toggle listener
    document.addEventListener('click', function(e) {
        const toggleBtn = e.target.closest('[data-toggle-theme]');
        if (toggleBtn) {
            e.preventDefault();
            toggleTheme();
        }
    });

    // Final sync on load
    document.addEventListener('DOMContentLoaded', () => applyTheme());
})();
