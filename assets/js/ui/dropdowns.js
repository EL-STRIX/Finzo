/**
 * Finzo UI — Custom Dropdowns Engine
 * Professional Vanilla JS handler for all .finzo-dropdown elements.
 */

(function() {
    /**
     * Initialize Dropdown logic using event delegation
     */
    function initDropdowns() {
        // Toggle dropdown on trigger click
        document.addEventListener('click', function(e) {
            const trigger = e.target.closest('.finzo-dropdown-trigger');
            if (!trigger) return;
            
            e.preventDefault();
            e.stopPropagation();
            
            const dropdown = trigger.closest('.finzo-dropdown');
            const menu = dropdown.querySelector('.finzo-dropdown-menu');
            
            const isOpen = trigger.classList.contains('is-open');
            
            // Close all other open dropdowns first
            closeAllDropdowns();
            
            if (!isOpen) {
                trigger.classList.add('is-open');
                menu.classList.add('is-open');
            }
        });

        // Handle option selection
        document.addEventListener('click', function(e) {
            const option = e.target.closest('.finzo-dropdown-option');
            if (!option) return;
            
            e.preventDefault();
            e.stopPropagation();
            
            const dropdown = option.closest('.finzo-dropdown');
            const trigger = dropdown.querySelector('.finzo-dropdown-trigger');
            const menu = dropdown.querySelector('.finzo-dropdown-menu');
            const input = dropdown.querySelector('input[type="hidden"]');
            const label = trigger.querySelector('.dropdown-label');
            
            const value = option.getAttribute('data-value');
            const text = option.textContent.trim();
            
            // Update selections
            dropdown.querySelectorAll('.finzo-dropdown-option').forEach(opt => opt.classList.remove('is-selected'));
            option.classList.add('is-selected');
            
            // Update value and UI
            if (input) {
                input.value = value;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
            if (label) label.textContent = text;
            
            // Close
            trigger.classList.remove('is-open');
            menu.classList.remove('is-open');
        });

        // Close on outside click
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.finzo-dropdown')) {
                closeAllDropdowns();
            }
        });
    }

    function closeAllDropdowns() {
        document.querySelectorAll('.finzo-dropdown-trigger.is-open').forEach(el => el.classList.remove('is-open'));
        document.querySelectorAll('.finzo-dropdown-menu.is-open').forEach(el => el.classList.remove('is-open'));
    }

    // Expose close helper globally if needed
    window.closeAllDropdowns = closeAllDropdowns;

    // Run initialization
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDropdowns);
    } else {
        initDropdowns();
    }
})();
