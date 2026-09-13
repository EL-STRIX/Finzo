/**
 * Finzo User Portal — Fixed Deposits & Investments Interactive Calculator
 * 
 * Clean Architecture Reference:
 * This view runs 100% client-side while the backend Fixed Deposit engine
 * is finalized per the blueprint in docs/04-API/Future-Financial-Products-Spec.md.
 */
(function() {
    'use strict';

    function formatINR(val) {
        return Math.round(val).toLocaleString('en-IN');
    }

    window.updateFDCalculator = function() {
        const amountSlider = document.getElementById('fd-calc-amount-slider');
        const tenureSelect = document.getElementById('fd-calc-tenure-select');
        const payoutRadio = document.querySelector('input[name="payout-type"]:checked');

        if (!amountSlider || !tenureSelect) return;

        const principal = parseFloat(amountSlider.value) || 100000;
        const tenureMonths = parseInt(tenureSelect.value) || 12;
        const selectedOption = tenureSelect.options[tenureSelect.selectedIndex];
        const annualRate = parseFloat(selectedOption?.getAttribute('data-rate') || '7.25');
        const isCumulative = !payoutRadio || payoutRadio.value === 'cumulative';

        // Update Labels
        const amtLabel = document.getElementById('fd-calc-amount-label');
        if (amtLabel) amtLabel.textContent = '₹ ' + formatINR(principal);

        const rateBadge = document.getElementById('fd-calc-rate-badge');
        if (rateBadge) rateBadge.textContent = annualRate.toFixed(2) + '% p.a.';

        const principalOutput = document.getElementById('fd-calc-principal-output');
        if (principalOutput) principalOutput.textContent = '₹ ' + formatINR(principal);

        let maturity = 0;
        let interest = 0;

        const r = annualRate / 100;
        const t = tenureMonths / 12;

        if (isCumulative) {
            // Quarterly Compounding: A = P * (1 + r/4)^(4*t)
            maturity = principal * Math.pow((1 + r / 4), 4 * t);
            interest = maturity - principal;
        } else {
            // Simple / Monthly Payout: Total Interest = P * r * t
            interest = principal * r * t;
            maturity = principal + interest;
        }

        const effectiveYield = (interest / principal / t) * 100;

        const maturityResult = document.getElementById('fd-calc-maturity-result');
        if (maturityResult) maturityResult.textContent = '₹ ' + formatINR(maturity);

        const interestOutput = document.getElementById('fd-calc-interest-output');
        if (interestOutput) interestOutput.textContent = '₹ ' + formatINR(interest);

        const yieldOutput = document.getElementById('fd-calc-yield-output');
        if (yieldOutput) yieldOutput.textContent = effectiveYield.toFixed(2) + '%';
    };

    function init() {
        window.updateFDCalculator();

        // Join Wealth Early Access Button
        const waitlistBtn = document.getElementById('btn-fd-waitlist');
        if (waitlistBtn) {
            waitlistBtn.addEventListener('click', function() {
                if (window.showToast) {
                    window.showToast('You are on the High-Yield Wealth waitlist! We will notify you once Term Deposits open.', 'success');
                }
            });
        }

        // Pre-register for Yield Terms
        const preRegisterBtn = document.getElementById('btn-fd-pre-register');
        if (preRegisterBtn) {
            preRegisterBtn.addEventListener('click', function() {
                const amt = document.getElementById('fd-calc-amount-label')?.textContent || '';
                const tenure = document.getElementById('fd-calc-tenure-select')?.options[document.getElementById('fd-calc-tenure-select').selectedIndex]?.text || '';
                if (window.showToast) {
                    window.showToast(`Preference registered for ${amt} (${tenure}). Priority allocation reserved!`, 'success');
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
