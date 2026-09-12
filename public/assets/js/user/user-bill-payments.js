/**
 * Finzo User Portal — Bill Payments & Utilities Interactive Explorer
 * 
 * Clean Architecture Reference:
 * This view runs 100% client-side while the backend Bharat BillPay (BBPS) integration
 * is developed per the specification in docs/04-API/Future-Financial-Products-Spec.md.
 */
(function() {
    'use strict';

    window.setCategory = function(categoryName, icon, billers) {
        // Update active card styling
        const cards = document.querySelectorAll('.category-card');
        cards.forEach(c => {
            c.classList.remove('border-primary', 'bg-primary/5', 'border-primary/40');
            c.classList.add('border-border-subtle', 'bg-surface/80');
        });

        if (event && event.currentTarget) {
            event.currentTarget.classList.remove('border-border-subtle', 'bg-surface/80');
            event.currentTarget.classList.add('border-primary/40', 'bg-primary/5');
        }

        // Update active category badge
        const pill = document.getElementById('active-category-pill');
        if (pill) pill.textContent = categoryName + ' Selected';

        // Update sandbox title & icon
        const title = document.getElementById('sandbox-title');
        if (title) title.textContent = categoryName + ' Bill Sandbox';

        const iconEl = document.getElementById('sandbox-category-icon');
        if (iconEl) iconEl.textContent = icon;

        // Populate Sandbox Select
        const select = document.getElementById('sandbox-biller-select');
        if (select && billers && billers.length) {
            select.innerHTML = billers.map(b => `<option value="${b}">${b}</option>`).join('');
        }

        // Hide prior simulation results
        const resCard = document.getElementById('sandbox-result');
        if (resCard) resCard.classList.add('hidden');

        if (window.showToast) {
            window.showToast(`Selected category: ${categoryName}`, 'info');
        }
    };

    window.simulateBillFetch = function(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-fetch-bill');
        const resCard = document.getElementById('sandbox-result');
        const biller = document.getElementById('sandbox-biller-select')?.value || 'Provider';
        const consumerNo = document.getElementById('sandbox-consumer-no')?.value || '1029384756';

        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="material-symbols-outlined animate-spin text-sm">sync</span><span>Contacting BBPS Gateway...</span>';
        }

        setTimeout(() => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<span class="material-symbols-outlined text-[18px]">search</span><span>Fetch Live Bill (Demo Simulation)</span>';
            }

            if (resCard) {
                resCard.classList.remove('hidden');
                const nameEl = document.getElementById('demo-consumer-name');
                if (nameEl) {
                    nameEl.textContent = (window.UserState && window.UserState.name) ? window.UserState.name : 'Sujay Paul';
                }
            }

            if (window.showToast) {
                window.showToast(`Simulated bill discovered for ${biller} (${consumerNo})`, 'success');
            }
        }, 600);
    };

    function init() {
        // Waitlist CTA
        const waitlistBtn = document.getElementById('btn-bill-waitlist');
        if (waitlistBtn) {
            waitlistBtn.addEventListener('click', function() {
                if (window.showToast) {
                    window.showToast('You are on the Bharat BillPay early access list! 0 convenience fees on launch.', 'success');
                }
            });
        }

        // Pre-register Biller CTA
        const preRegisterBtn = document.getElementById('btn-pre-register-biller');
        if (preRegisterBtn) {
            preRegisterBtn.addEventListener('click', function() {
                if (window.showToast) {
                    window.showToast('Pre-registered for zero convenience fees and early auto-debit rollout!', 'success');
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
