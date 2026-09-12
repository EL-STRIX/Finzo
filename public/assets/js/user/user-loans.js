/**
 * Finzo User Loans & EMI Calculator (Client-Side Interactivity)
 * 
 * Clean architecture reference:
 * When the backend lending module is implemented in the future,
 * connect this view to GET /api/v1/loans and POST /api/v1/loans/apply.
 */
(function() {
    'use strict';

    function formatINR(val) {
        return Math.round(val).toLocaleString('en-IN');
    }

    window.updateCalculator = function() {
        const amountSlider = document.getElementById('calc-amount-slider');
        const tenureSlider = document.getElementById('calc-tenure-slider');
        const rateSlider = document.getElementById('calc-rate-slider');

        if (!amountSlider || !tenureSlider || !rateSlider) return;

        const principal = parseFloat(amountSlider.value);
        const months = parseInt(tenureSlider.value);
        const annualRate = parseFloat(rateSlider.value);

        // Update Slider Labels
        document.getElementById('calc-amount-label').textContent = '₹ ' + formatINR(principal);
        document.getElementById('calc-tenure-label').textContent = months + ' Months';
        document.getElementById('calc-rate-label').textContent = annualRate.toFixed(1) + ' %';

        // Actuarial EMI Formula: EMI = [P * r * (1 + r)^n] / [(1 + r)^n - 1]
        const monthlyRate = (annualRate / 12) / 100;
        let emi = 0;
        if (monthlyRate === 0) {
            emi = principal / months;
        } else {
            const factor = Math.pow(1 + monthlyRate, months);
            emi = (principal * monthlyRate * factor) / (factor - 1);
        }

        const totalPayable = emi * months;
        const totalInterest = totalPayable - principal;

        document.getElementById('calc-emi-result').textContent = '₹ ' + formatINR(emi);
        document.getElementById('calc-total-payable').textContent = '₹ ' + formatINR(totalPayable);
        document.getElementById('calc-total-interest').textContent = '₹ ' + formatINR(totalInterest);
    };

    function init() {
        window.updateCalculator();

        const notifyBtn = document.getElementById('btn-user-loan-notify');
        if (notifyBtn) {
            notifyBtn.addEventListener('click', function() {
                if (window.showToast) {
                    window.showToast('You are on the priority waitlist! We will notify you once Digital Credit is available.', 'success');
                }
            });
        }

        const calcWaitlistBtn = document.getElementById('btn-calc-waitlist');
        if (calcWaitlistBtn) {
            calcWaitlistBtn.addEventListener('click', function() {
                const amt = document.getElementById('calc-amount-label')?.textContent || '';
                const tenure = document.getElementById('calc-tenure-label')?.textContent || '';
                if (window.showToast) {
                    window.showToast(`Preference registered for ${amt} over ${tenure}. We will reach out on early rollout!`, 'success');
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
