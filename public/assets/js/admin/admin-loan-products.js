(function() {
    'use strict';

    function init() {
        const notifyBtn = document.getElementById('btn-notify-release');
        if (notifyBtn) {
            notifyBtn.addEventListener('click', function() {
                if (window.showToast) {
                    window.showToast('You will be notified once the Loan Products engine backend is deployed.', 'info');
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
