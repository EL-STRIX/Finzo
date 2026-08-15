/**
 * Finzo Portals — Global UI Components
 * Centralized logic for Dropdowns, Modals, Tabs, and Alerts.
 * Fully WCAG 2.2 AA Compliant.
 */

(function() {
    window.FinzoUI = {
        init: function() {
            this.bindDropdowns();
            this.bindModals();
            this.bindTabs();
            this.initARIA();
        },

        initARIA: function() {
            // Set initial ARIA attributes on all dropdowns
            document.querySelectorAll('.finzo-dropdown-trigger').forEach(trigger => {
                trigger.setAttribute('aria-haspopup', 'listbox');
                trigger.setAttribute('aria-expanded', 'false');
                if (!trigger.hasAttribute('tabindex')) trigger.setAttribute('tabindex', '0');
            });
            document.querySelectorAll('.finzo-dropdown-menu').forEach(menu => {
                menu.setAttribute('role', 'listbox');
            });
            document.querySelectorAll('.finzo-dropdown-option').forEach(opt => {
                opt.setAttribute('role', 'option');
                opt.setAttribute('tabindex', '-1');
            });
            
            // Set initial ARIA attributes on all tabs
            document.querySelectorAll('[data-tab-target]').forEach(tab => {
                tab.setAttribute('role', 'tab');
                if (!tab.hasAttribute('tabindex')) tab.setAttribute('tabindex', '0');
            });
        },

        bindDropdowns: function() {
            const toggleDropdown = (trigger) => {
                const dropdown = trigger.closest('.finzo-dropdown');
                const menu = dropdown.querySelector('.finzo-dropdown-menu');
                if (!menu) return;

                const isOpen = menu.classList.contains('is-open');
                
                // Close all others
                document.querySelectorAll('.finzo-dropdown-menu.is-open').forEach(m => {
                    if (m !== menu) {
                        m.classList.remove('is-open');
                        const otherTrigger = m.closest('.finzo-dropdown').querySelector('.finzo-dropdown-trigger');
                        if (otherTrigger) otherTrigger.setAttribute('aria-expanded', 'false');
                    }
                });

                // Toggle current
                if (isOpen) {
                    menu.classList.remove('is-open');
                    trigger.setAttribute('aria-expanded', 'false');
                    trigger.focus();
                } else {
                    menu.classList.add('is-open');
                    trigger.setAttribute('aria-expanded', 'true');
                    // Focus first option
                    const firstOption = menu.querySelector('.finzo-dropdown-option');
                    if (firstOption) firstOption.focus();
                }
            };

            const selectOption = (option) => {
                const dropdown = option.closest('.finzo-dropdown');
                const triggerText = dropdown.querySelector('.dropdown-selected-text') || dropdown.querySelector('.finzo-dropdown-trigger');
                
                // Visual update
                dropdown.querySelectorAll('.finzo-dropdown-option').forEach(opt => {
                    opt.classList.remove('is-selected');
                    opt.setAttribute('aria-selected', 'false');
                });
                
                option.classList.add('is-selected');
                option.setAttribute('aria-selected', 'true');
                
                // Text update - avoid replacing children if it has icons
                if (dropdown.querySelector('.dropdown-selected-text')) {
                    dropdown.querySelector('.dropdown-selected-text').textContent = option.textContent.trim();
                } else {
                    // Primitive fallback
                    const textNode = Array.from(triggerText.childNodes).find(n => n.nodeType === 3 && n.textContent.trim().length > 0);
                    if (textNode) textNode.textContent = option.textContent.trim() + ' ';
                }
                
                // Close
                const menu = dropdown.querySelector('.finzo-dropdown-menu');
                menu.classList.remove('is-open');
                dropdown.querySelector('.finzo-dropdown-trigger').setAttribute('aria-expanded', 'false');
                dropdown.querySelector('.finzo-dropdown-trigger').focus();

                const hiddenInput = dropdown.querySelector('input[type="hidden"]');
                const newValue = option.dataset.value || option.textContent.trim();
                if (hiddenInput) {
                    hiddenInput.value = newValue;
                    hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                }

                dropdown.dispatchEvent(new CustomEvent('change', { 
                    detail: { value: newValue, text: option.textContent.trim() } 
                }));
            };

            // Click handling
            document.addEventListener('click', (e) => {
                // Click outside
                if (!e.target.closest('.finzo-dropdown')) {
                    document.querySelectorAll('.finzo-dropdown-menu.is-open').forEach(menu => {
                        menu.classList.remove('is-open');
                        const trigger = menu.closest('.finzo-dropdown').querySelector('.finzo-dropdown-trigger');
                        if (trigger) trigger.setAttribute('aria-expanded', 'false');
                    });
                }

                const trigger = e.target.closest('.finzo-dropdown-trigger');
                if (trigger) {
                    e.preventDefault();
                    toggleDropdown(trigger);
                }

                const option = e.target.closest('.finzo-dropdown-option');
                if (option) {
                    selectOption(option);
                }
            });

            // Keyboard handling
            document.addEventListener('keydown', (e) => {
                // Trigger keyboard nav
                const trigger = document.activeElement.closest('.finzo-dropdown-trigger');
                if (trigger && (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown')) {
                    e.preventDefault();
                    toggleDropdown(trigger);
                    return;
                }

                // Option keyboard nav
                const option = document.activeElement.closest('.finzo-dropdown-option');
                if (option) {
                    const menu = option.closest('.finzo-dropdown-menu');
                    const options = Array.from(menu.querySelectorAll('.finzo-dropdown-option'));
                    const currentIndex = options.indexOf(option);

                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        if (currentIndex < options.length - 1) options[currentIndex + 1].focus();
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        if (currentIndex > 0) options[currentIndex - 1].focus();
                    } else if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        selectOption(option);
                    } else if (e.key === 'Escape') {
                        e.preventDefault();
                        const dropdown = option.closest('.finzo-dropdown');
                        menu.classList.remove('is-open');
                        const t = dropdown.querySelector('.finzo-dropdown-trigger');
                        t.setAttribute('aria-expanded', 'false');
                        t.focus();
                    }
                }
            });
        },

        bindModals: function() {
            // Helper to trap focus
            const getFocusableElements = (container) => {
                return container.querySelectorAll('a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])');
            };

            const openModal = (modal) => {
                modal.classList.add('is-active');
                modal.setAttribute('aria-modal', 'true');
                modal.setAttribute('role', 'dialog');
                
                // Store previously focused element
                modal.dataset.returnFocus = document.activeElement.id || '';
                if (!modal.dataset.returnFocus) {
                    // generate temp ID
                    const id = 'f' + Math.random().toString(36).substr(2, 9);
                    document.activeElement.id = id;
                    modal.dataset.returnFocus = id;
                }

                // Focus first focusable element
                const focusable = getFocusableElements(modal);
                if (focusable.length) focusable[0].focus();
                
                // Prevent body scroll
                document.body.style.overflow = 'hidden';
            };

            const closeModal = (modal) => {
                modal.classList.remove('is-active');
                document.body.style.overflow = '';
                
                // Return focus
                if (modal.dataset.returnFocus) {
                    const el = document.getElementById(modal.dataset.returnFocus);
                    if (el) el.focus();
                }
            };
            
            // Expose globally
            window.FinzoUI.closeModal = closeModal;
            
            // Listen for form success
            document.addEventListener('formSuccess', () => {
                const activeModal = document.querySelector('.modal-overlay.is-active');
                if (activeModal) closeModal(activeModal);
            });

            document.addEventListener('click', (e) => {
                const trigger = e.target.closest('[data-modal-target]');
                if (trigger) {
                    e.preventDefault();
                    const modal = document.getElementById(trigger.getAttribute('data-modal-target'));
                    if (modal) openModal(modal);
                }

                const closeTrigger = e.target.closest('[data-modal-close]');
                if (closeTrigger) {
                    e.preventDefault();
                    const modal = closeTrigger.closest('.modal-overlay');
                    if (modal) closeModal(modal);
                }

                // Click overlay
                if (e.target.classList.contains('modal-overlay')) {
                    closeModal(e.target);
                }
            });

            document.addEventListener('keydown', (e) => {
                const activeModal = document.querySelector('.modal-overlay.is-active');
                if (!activeModal) return;

                if (e.key === 'Escape') {
                    closeModal(activeModal);
                    return;
                }

                // Focus trapping
                if (e.key === 'Tab') {
                    const focusable = getFocusableElements(activeModal);
                    if (!focusable.length) {
                        e.preventDefault();
                        return;
                    }
                    
                    const first = focusable[0];
                    const last = focusable[focusable.length - 1];

                    if (e.shiftKey) {
                        if (document.activeElement === first) {
                            e.preventDefault();
                            last.focus();
                        }
                    } else {
                        if (document.activeElement === last) {
                            e.preventDefault();
                            first.focus();
                        }
                    }
                }
            });
        },

        bindTabs: function() {
            document.addEventListener('click', (e) => {
                const tab = e.target.closest('[data-tab-target]');
                if (tab) {
                    e.preventDefault();
                    this.activateTab(tab);
                }
            });

            // Keyboard Nav for Tabs
            document.addEventListener('keydown', (e) => {
                const tab = document.activeElement.closest('[role="tab"]');
                if (tab) {
                    const tabGroup = tab.parentElement.children;
                    const tabs = Array.from(tabGroup).filter(t => t.hasAttribute('role'));
                    const index = tabs.indexOf(tab);

                    if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
                        e.preventDefault();
                        const next = tabs[(index + 1) % tabs.length];
                        next.focus();
                        this.activateTab(next);
                    } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
                        e.preventDefault();
                        const prev = tabs[(index - 1 + tabs.length) % tabs.length];
                        prev.focus();
                        this.activateTab(prev);
                    }
                }
            });
        },

        activateTab: function(tab) {
            const targetId = tab.getAttribute('data-tab-target');
            const content = document.getElementById(targetId);
            
            if (content) {
                const siblings = tab.parentElement.children;
                Array.from(siblings).forEach(s => {
                    s.classList.remove('is-active', 'bg-white', 'text-primary');
                    s.setAttribute('aria-selected', 'false');
                });
                
                const contentGroup = content.parentElement.children;
                Array.from(contentGroup).forEach(c => c.classList.add('hidden'));
                
                tab.classList.add('is-active', 'bg-white', 'text-primary');
                tab.setAttribute('aria-selected', 'true');
                content.classList.remove('hidden');
            }
        }
    };

    document.addEventListener('DOMContentLoaded', () => window.FinzoUI.init());
    
    document.addEventListener('componentsLoaded', () => {
        window.FinzoUI.initARIA(); // re-init ARIA for dynamic HTML
    });
})();
