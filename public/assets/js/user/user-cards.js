/**
 * Finzo Next-Gen Cards & Interactive Controls (Client-Side Interactivity)
 * 
 * Clean architecture reference:
 * When the backend card engine is implemented in the future,
 * connect this view to GET /api/v1/cards, POST /api/v1/cards/request, and PATCH /api/v1/cards/{id}/status.
 */
(function() {
    'use strict';

    let cvvRevealed = false;
    let cardFlipped = false;

    function init() {
        // CVV Reveal Button
        const cvvBtn = document.getElementById('btn-toggle-cvv');
        const numElem = document.getElementById('card-display-number');
        const cvvLbl = document.getElementById('lbl-cvv');

        if (cvvBtn && numElem && cvvLbl) {
            cvvBtn.addEventListener('click', function() {
                cvvRevealed = !cvvRevealed;
                if (cvvRevealed) {
                    numElem.textContent = '4829  9281  7392  8842';
                    cvvLbl.textContent = 'Hide CVV (739)';
                    cvvBtn.classList.add('btn-primary');
                    cvvBtn.classList.remove('btn-secondary');
                } else {
                    numElem.textContent = '•••• •••• •••• 8842';
                    cvvLbl.textContent = 'Reveal Demo CVV';
                    cvvBtn.classList.add('btn-secondary');
                    cvvBtn.classList.remove('btn-primary');
                }
            });
        }

        // Flip Card Demo
        const flipBtn = document.getElementById('btn-toggle-flip');
        const cardBox = document.getElementById('virtual-card-box');
        if (flipBtn && cardBox) {
            flipBtn.addEventListener('click', function() {
                cardFlipped = !cardFlipped;
                if (cardFlipped) {
                    cardBox.style.transform = 'rotateY(180deg)';
                } else {
                    cardBox.style.transform = 'rotateY(0deg)';
                }
            });
        }

        // Freeze Card Toggle Demo
        const freezeToggle = document.getElementById('preview-freeze-toggle');
        if (freezeToggle) {
            freezeToggle.addEventListener('change', function() {
                if (this.checked) {
                    if (window.showToast) {
                        window.showToast('Demo: Card temporarily locked. All transactions paused.', 'warning');
                    }
                    if (cardBox) cardBox.classList.add('opacity-60', 'grayscale');
                } else {
                    if (window.showToast) {
                        window.showToast('Demo: Card unlocked and active for transactions.', 'success');
                    }
                    if (cardBox) cardBox.classList.remove('opacity-60', 'grayscale');
                }
            });
        }

        // Save Preferences Button
        const saveLimitsBtn = document.getElementById('btn-save-preview-limits');
        if (saveLimitsBtn) {
            saveLimitsBtn.addEventListener('click', function() {
                const onlineCap = document.getElementById('limit-purchase-label')?.textContent || '';
                const atmCap = document.getElementById('limit-atm-label')?.textContent || '';
                if (window.showToast) {
                    window.showToast(`Preferences saved! Daily Online: ${onlineCap}, Daily ATM: ${atmCap}`, 'success');
                }
            });
        }

        // Waitlist CTA
        const waitlistBtn = document.getElementById('btn-user-card-waitlist');
        if (waitlistBtn) {
            waitlistBtn.addEventListener('click', function() {
                if (window.showToast) {
                    window.showToast('You are on the Obsidian Metal Card priority waitlist! We will notify you upon launch.', 'success');
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
