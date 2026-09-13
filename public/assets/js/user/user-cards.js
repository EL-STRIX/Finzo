/**
 * Finzo Next-Gen Cards — Interactive Demo (Client-Side Only)
 *
 * Clean architecture reference for future backend connection:
 *   GET  /api/v1/cards              → list user's cards
 *   POST /api/v1/cards/request      → apply for a card
 *   PATCH /api/v1/cards/{id}/status → freeze / unfreeze
 */
(function () {
    'use strict';

    var DEMO_NAME    = 'ARJUN K. SHARMA';
    var DEMO_NUMBER  = '4829  9281  7392  8842';
    var DEMO_CVV     = '739';
    var MASKED_NUM   = '\u2022\u2022\u2022\u2022 \u2022\u2022\u2022\u2022 \u2022\u2022\u2022\u2022 8842';
    var MASKED_CVV   = '\u2022\u2022\u2022';

    var cardFlipped = false;
    var cvvRevealed = false;

    /* Safely add a class and remove another */
    function swapClass(el, remove, add) {
        if (!el) return;
        el.classList.remove(remove);
        el.classList.add(add);
    }

    function init() {
        var cardBox     = document.getElementById('virtual-card-box');
        var holderElem  = document.getElementById('card-display-holder');
        var numElem     = document.getElementById('card-display-number');
        var backCvvElem = document.getElementById('back-cvv');
        var backSig     = document.getElementById('back-signature');
        var cvvBtn      = document.getElementById('btn-toggle-cvv');
        var cvvLbl      = document.getElementById('lbl-cvv');
        var cvvIcon     = cvvBtn ? cvvBtn.querySelector('.material-symbols-outlined') : null;

        /* ── Reveal / Hide Card Details ─────────────────────────── */
        if (cvvBtn) {
            cvvBtn.addEventListener('click', function () {
                cvvRevealed = !cvvRevealed;

                if (cvvRevealed) {
                    /* --- FRONT: full number + demo name --- */
                    if (numElem)   numElem.textContent   = DEMO_NUMBER;
                    if (holderElem) holderElem.textContent = DEMO_NAME;

                    /* --- BACK: CVV revealed with high-contrast style --- */
                    if (backCvvElem) {
                        backCvvElem.textContent = DEMO_CVV;
                        backCvvElem.style.background = 'rgba(245,158,11,0.25)'; /* amber tint */
                        backCvvElem.style.borderColor = 'rgba(245,158,11,0.6)';
                        backCvvElem.style.color = '#fcd34d';                     /* amber-300 */
                    }
                    /* Signature strip shows demo name */
                    if (backSig) backSig.textContent = DEMO_NAME;

                    /* --- Button: active state --- */
                    if (cvvLbl)  cvvLbl.textContent  = 'Hide Details';
                    if (cvvIcon) cvvIcon.textContent = 'visibility_off';
                    swapClass(cvvBtn, 'btn-secondary', 'btn-primary');

                    if (window.showToast) {
                        window.showToast('Demo revealed \u2014 flip the card to see CVV ' + DEMO_CVV + ' on the back.', 'info');
                    }
                } else {
                    /* --- FRONT: mask --- */
                    if (numElem)    numElem.textContent    = MASKED_NUM;
                    if (holderElem) holderElem.textContent = 'VALUED FINZO MEMBER';

                    /* --- BACK: mask + reset style --- */
                    if (backCvvElem) {
                        backCvvElem.textContent = MASKED_CVV;
                        backCvvElem.style.background = 'rgba(255,255,255,0.12)';
                        backCvvElem.style.borderColor = 'rgba(255,255,255,0.2)';
                        backCvvElem.style.color = '#fff';
                    }
                    if (backSig) backSig.textContent = 'Authorized Signature';

                    /* --- Button: default state --- */
                    if (cvvLbl)  cvvLbl.textContent  = 'Reveal Demo Card Details';
                    if (cvvIcon) cvvIcon.textContent = 'visibility';
                    swapClass(cvvBtn, 'btn-primary', 'btn-secondary');
                }
            });
        }

        /* ── Flip Card ─────────────────────────────────────────── */
        var flipBtn = document.getElementById('btn-toggle-flip');
        var flipLbl = document.getElementById('lbl-flip');
        var flipIcon = flipBtn ? flipBtn.querySelector('.material-symbols-outlined') : null;

        if (flipBtn && cardBox) {
            flipBtn.addEventListener('click', function () {
                cardFlipped = !cardFlipped;
                cardBox.style.transform = cardFlipped ? 'rotateY(180deg)' : 'rotateY(0deg)';
                if (flipLbl)  flipLbl.textContent  = cardFlipped ? 'Show Front' : 'Flip Card';
                if (flipIcon) flipIcon.textContent = cardFlipped ? 'flip_to_front' : 'cached';
            });
        }

        /* ── Freeze Toggle (demo visual only) ──────────────────── */
        var freezeToggle = document.getElementById('preview-freeze-toggle');
        if (freezeToggle && cardBox) {
            freezeToggle.addEventListener('change', function () {
                if (this.checked) {
                    cardBox.classList.add('opacity-50', 'grayscale');
                    if (window.showToast) window.showToast('Demo: Card frozen. All transactions paused.', 'warning');
                } else {
                    cardBox.classList.remove('opacity-50', 'grayscale');
                    if (window.showToast) window.showToast('Demo: Card unfrozen and active.', 'success');
                }
            });
        }

        /* ── Save Preferences (demo toast only) ────────────────── */
        var saveLimitsBtn = document.getElementById('btn-save-preview-limits');
        if (saveLimitsBtn) {
            saveLimitsBtn.addEventListener('click', function () {
                var online = document.getElementById('limit-purchase-label') ? document.getElementById('limit-purchase-label').textContent : '';
                var atm    = document.getElementById('limit-atm-label')      ? document.getElementById('limit-atm-label').textContent      : '';
                if (window.showToast) {
                    window.showToast('Preferences saved! Online: ' + online + ' \u00b7 ATM: ' + atm, 'success');
                }
            });
        }

        /* ── Metal Card Waitlist CTA ───────────────────────────── */
        var waitlistBtn = document.getElementById('btn-user-card-waitlist');
        if (waitlistBtn) {
            waitlistBtn.addEventListener('click', function () {
                if (window.showToast) {
                    window.showToast('You are on the Obsidian Metal Card priority waitlist! We will notify you on launch.', 'success');
                }
            });
        }
    }

    /* Run after DOM is ready regardless of how the script was loaded */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
