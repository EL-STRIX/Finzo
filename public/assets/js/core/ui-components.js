/**
 * Finzo Portals — Global UI Components
 * Centralized logic for Dropdowns, Modals, Tabs, and Alerts.
 */

(function() {
    window.FinzoUI = {
        init: function() {
            this.bindDropdowns();
            this.bindModals();
            this.bindTabs();
        },

        bindDropdowns: function() {
            document.addEventListener('click', (e) => {
                // Close all open dropdowns if clicking outside
                if (!e.target.closest('.finzo-dropdown')) {
                    document.querySelectorAll('.finzo-dropdown-menu.is-open').forEach(menu => {
                        menu.classList.remove('is-open');
                    });
                }

                // Toggle specific dropdown
                const trigger = e.target.closest('.finzo-dropdown-trigger');
                if (trigger) {
                    e.preventDefault();
                    const dropdown = trigger.closest('.finzo-dropdown');
                    const menu = dropdown.querySelector('.finzo-dropdown-menu');
                    if (menu) {
                        const isOpen = menu.classList.contains('is-open');
                        
                        // Close others
                        document.querySelectorAll('.finzo-dropdown-menu.is-open').forEach(m => {
                            if (m !== menu) m.classList.remove('is-open');
                        });
                        
                        // Toggle current
                        menu.classList.toggle('is-open', !isOpen);
                    }
                }

                // Select option
                const option = e.target.closest('.finzo-dropdown-option');
                if (option) {
                    const dropdown = option.closest('.finzo-dropdown');
                    const triggerText = dropdown.querySelector('.dropdown-selected-text');
                    
                    // Update visual state
                    dropdown.querySelectorAll('.finzo-dropdown-option').forEach(opt => opt.classList.remove('is-selected'));
                    option.classList.add('is-selected');
                    
                    if (triggerText) {
                        triggerText.textContent = option.textContent.trim();
                    }
                    
                    // Close menu
                    dropdown.querySelector('.finzo-dropdown-menu').classList.remove('is-open');
                    
                    // Trigger custom event for specific logic bindings
                    dropdown.dispatchEvent(new CustomEvent('change', { 
                        detail: { value: option.dataset.value, text: option.textContent.trim() } 
                    }));
                }
            });
        },

        bindModals: function() {
            document.addEventListener('click', (e) => {
                const trigger = e.target.closest('[data-modal-target]');
                if (trigger) {
                    e.preventDefault();
                    const targetId = trigger.getAttribute('data-modal-target');
                    const modal = document.getElementById(targetId);
                    if (modal) {
                        modal.classList.add('is-active');
                    }
                }

                const closeTrigger = e.target.closest('[data-modal-close]');
                if (closeTrigger) {
                    e.preventDefault();
                    const modal = closeTrigger.closest('.modal-overlay');
                    if (modal) {
                        modal.classList.remove('is-active');
                    }
                }
            });

            // Close on overlay click
            document.addEventListener('click', (e) => {
                if (e.target.classList.contains('modal-overlay')) {
                    e.target.classList.remove('is-active');
                }
            });

            // Close on Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    document.querySelectorAll('.modal-overlay.is-active').forEach(modal => {
                        modal.classList.remove('is-active');
                    });
                }
            });
        },

        bindTabs: function() {
            document.addEventListener('click', (e) => {
                const tab = e.target.closest('[data-tab-target]');
                if (tab) {
                    e.preventDefault();
                    const targetId = tab.getAttribute('data-tab-target');
                    const tabGroup = tab.closest('.tabs-container') || document;
                    const content = document.getElementById(targetId);
                    
                    if (content) {
                        // Reset all tabs in this group
                        const siblings = tab.parentElement.children;
                        Array.from(siblings).forEach(s => s.classList.remove('is-active', 'bg-white', 'text-primary'));
                        
                        // Hide all content panels in this group
                        const contentGroup = content.parentElement.children;
                        Array.from(contentGroup).forEach(c => c.classList.add('hidden'));
                        
                        // Activate target
                        tab.classList.add('is-active', 'bg-white', 'text-primary');
                        content.classList.remove('hidden');
                    }
                }
            });
        }
    };

    // Initialize on load
    document.addEventListener('DOMContentLoaded', () => window.FinzoUI.init());
    
    // Re-initialize when partial components are loaded dynamically
    document.addEventListener('componentsLoaded', () => {
        // UI bindings use event delegation, so no re-bind needed for most,
        // but this hook is available if stateful plugins are added later.
    });
})();
