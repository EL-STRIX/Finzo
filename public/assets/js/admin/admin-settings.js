/**
 * Finzo Admin — System Settings
 * UI-only tab switcher with interactive preview notifications.
 * 
 * Clean architecture reference:
 * When the backend settings engine is implemented in the future,
 * connect this view to GET /api/v1/admin/settings and PUT /api/v1/admin/settings.
 */
(function () {
    'use strict';

    function init() {
        const tabs = document.querySelectorAll('.settings-tab-ui');
        const panels = document.querySelectorAll('.settings-panel');

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const target = tab.dataset.tab;

                // Update tab active state
                tabs.forEach(t => {
                    t.classList.remove('active', 'bg-primary/10', 'text-primary', 'border', 'border-primary/20');
                    t.classList.add('text-text-secondary', 'hover:bg-background', 'hover:text-text-primary');
                });
                tab.classList.add('active', 'bg-primary/10', 'text-primary', 'border', 'border-primary/20');
                tab.classList.remove('text-text-secondary', 'hover:bg-background', 'hover:text-text-primary');

                // Show the target panel
                panels.forEach(panel => panel.classList.add('hidden'));
                const activePanel = document.getElementById('tab-' + target);
                if (activePanel) activePanel.classList.remove('hidden');
            });
        });

        const notifyBtn = document.getElementById('btn-notify-settings');
        if (notifyBtn) {
            notifyBtn.addEventListener('click', function() {
                if (window.showToast) {
                    window.showToast('You will be notified once the System Settings engine backend is deployed.', 'info');
                }
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
