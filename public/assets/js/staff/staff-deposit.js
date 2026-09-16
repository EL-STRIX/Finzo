/**
 * Finzo Staff — Transaction Controller (Deposit & Withdrawal)
 * Pure Vanilla JavaScript — Clean Architecture & Real-Time Balance Calculations
 */

(function () {
    'use strict';

    // ── Local State ──
    const urlParams = new URLSearchParams(window.location.search);
    const isWithdraw = urlParams.get('type') === 'withdraw';
    
    let verifiedAccountNo = '';
    let verifiedName = '';
    let verifiedBalance = 0;
    let verifiedLimit = 0;
    let currentStep = 1;
    let isProcessing = false;

    // ── Safe HTML Escape ──
    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        const div = document.createElement('div');
        div.textContent = String(str);
        return div.innerHTML;
    }

    // ── Number to Indian Words ──
    function numberToWords(number) {
        const num = Math.floor(Math.abs(Number(number) || 0));
        if (num === 0) return 'Zero';

        const ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 
                      'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

        function convertThreeDigits(n) {
            let str = '';
            if (n >= 100) {
                str += ones[Math.floor(n / 100)] + ' Hundred ';
                n %= 100;
            }
            if (n >= 20) {
                str += tens[Math.floor(n / 10)] + ' ';
                n %= 10;
            }
            if (n > 0) {
                str += ones[n] + ' ';
            }
            return str.trim();
        }

        let words = '';
        const crore = Math.floor(num / 10000000);
        const remCrore = num % 10000000;
        const lakh = Math.floor(remCrore / 100000);
        const remLakh = remCrore % 100000;
        const thousand = Math.floor(remLakh / 1000);
        const remThousand = remLakh % 1000;

        if (crore > 0) words += convertThreeDigits(crore) + ' Crore ';
        if (lakh > 0) words += convertThreeDigits(lakh) + ' Lakh ';
        if (thousand > 0) words += convertThreeDigits(thousand) + ' Thousand ';
        if (remThousand > 0) words += convertThreeDigits(remThousand);

        return words.trim() + ' Rupees Only';
    }

    // ── Init on DOM Ready ──
    function init() {
        if (typeof fetchStaffSession === 'function') {
            fetchStaffSession();
        }

        bindEvents();
        updateStepsUI(1);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // ── Stepper Navigation ──
    function updateStepsUI(stepNum) {
        currentStep = stepNum;

        const indicators = document.querySelectorAll('.step-indicator');
        indicators.forEach((ind) => {
            const s = parseInt(ind.getAttribute('data-step'), 10);
            ind.className = 'step-indicator w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs transition-all border';

            if (s < stepNum) {
                // Completed step
                ind.classList.add('bg-emerald-600', 'text-white', 'border-emerald-600');
                ind.innerHTML = '<span class="material-symbols-outlined text-sm font-bold">check</span>';
            } else if (s === stepNum) {
                // Active step
                ind.classList.add('bg-warning-base', 'text-white', 'border-warning-base', 'shadow-md', 'shadow-amber-500/20');
                ind.textContent = s.toString();
            } else {
                // Pending step
                ind.classList.add('bg-surface-subtle', 'dark:bg-white/5', 'text-text-muted', 'border-surface-border', 'dark:border-white/10');
                ind.textContent = s.toString();
            }
        });

        // Step text highlight
        for (let i = 1; i <= 3; i++) {
            const lbl = document.getElementById(`step-label-${i}`);
            if (lbl) {
                if (i === stepNum) {
                    lbl.className = 'text-xs font-black uppercase tracking-widest text-warning-base whitespace-nowrap';
                } else if (i < stepNum) {
                    lbl.className = 'text-xs font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-400 whitespace-nowrap';
                } else {
                    lbl.className = 'text-xs font-black uppercase tracking-widest text-text-muted whitespace-nowrap';
                }
            }
        }

        // Section visibility
        const sec1 = document.getElementById('section-1');
        const sec2 = document.getElementById('section-2');
        const sec3 = document.getElementById('section-3');

        if (sec1) sec1.classList.toggle('hidden', stepNum !== 1);
        if (sec2) sec2.classList.toggle('hidden', stepNum !== 2);
        if (sec3) sec3.classList.toggle('hidden', stepNum !== 3);

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // ── Bind DOM Events ──
    function bindEvents() {
        // Step 1: Lookup
        const findBtn = document.getElementById('find-btn');
        const lookupInput = document.getElementById('account-lookup');
        if (findBtn) {
            findBtn.addEventListener('click', handleAccountLookup);
        }
        if (lookupInput) {
            lookupInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    handleAccountLookup();
                }
            });
        }

        const btnSearchAgain = document.getElementById('btn-search-again');
        if (btnSearchAgain) {
            btnSearchAgain.addEventListener('click', resetLookup);
        }

        const btnProceedStep2 = document.getElementById('btn-proceed-step2');
        if (btnProceedStep2) {
            btnProceedStep2.addEventListener('click', goToStep2);
        }

        // Step 2: Details & Live Calc
        const depAmount = document.getElementById('dep-amount');
        const depType = document.getElementById('dep-type');
        if (depAmount) {
            depAmount.addEventListener('input', handleAmountChange);
        }
        if (depType) {
            depType.addEventListener('change', handleAmountChange);
        }

        const btnBackStep1 = document.getElementById('btn-back-step1');
        if (btnBackStep1) {
            btnBackStep1.addEventListener('click', () => updateStepsUI(1));
        }

        const btnProceedStep3 = document.getElementById('btn-proceed-step3');
        if (btnProceedStep3) {
            btnProceedStep3.addEventListener('click', goToStep3);
        }

        // Step 3: Confirmation
        const btnBackStep2 = document.getElementById('btn-back-step2');
        if (btnBackStep2) {
            btnBackStep2.addEventListener('click', () => updateStepsUI(2));
        }

        const finalDepositBtn = document.getElementById('final-deposit-btn');
        if (finalDepositBtn) {
            finalDepositBtn.addEventListener('click', executeTransaction);
        }
    }

    // ── Action: Account Lookup ──
    async function handleAccountLookup() {
        const lookupInput = document.getElementById('account-lookup');
        const acc = lookupInput ? lookupInput.value.trim() : '';

        const errorBanner = document.getElementById('account-error');
        const errorDesc = document.getElementById('error-desc');
        const successBanner = document.getElementById('account-success');
        const findBtn = document.getElementById('find-btn');
        const findBtnText = document.getElementById('find-btn-text');

        if (!acc) {
            if (errorDesc) errorDesc.textContent = 'Please enter an account number to search.';
            if (errorBanner) errorBanner.classList.remove('hidden');
            if (successBanner) successBanner.classList.add('hidden');
            if (lookupInput) lookupInput.focus();
            return;
        }

        if (errorBanner) errorBanner.classList.add('hidden');
        if (findBtn) findBtn.disabled = true;
        if (findBtnText) findBtnText.textContent = 'Searching...';

        try {
            const res = await window.api.get('/staff-portal/account-lookup', { account_no: acc });

            if (res && res.success && res.found && res.account) {
                const account = res.account;

                verifiedAccountNo = account.account_no;
                verifiedName = account.name;
                verifiedBalance = parseFloat(account.balance) || 0;
                verifiedLimit = parseFloat(account.daily_limit_left) || 0;

                // Render Success Info
                const resName = document.getElementById('res-name');
                const resAcc = document.getElementById('res-acc');
                const resInitials = document.getElementById('res-initials');

                if (resName) resName.textContent = account.name;
                if (resAcc) resAcc.textContent = account.account_no;
                if (resInitials) {
                    const initials = (account.name || 'CU')
                        .split(' ')
                        .filter(w => /^[a-zA-Z]/.test(w))
                        .map(n => n[0])
                        .join('')
                        .substring(0, 2)
                        .toUpperCase() || 'CU';
                    resInitials.textContent = initials;
                }

                document.querySelectorAll('.account-type-val').forEach(el => {
                    el.textContent = (account.account_type || 'Savings').toUpperCase();
                });
                document.querySelectorAll('.current-balance-val').forEach(el => {
                    el.textContent = typeof formatINR === 'function' ? formatINR(verifiedBalance) : '₹' + verifiedBalance.toFixed(2);
                });
                document.querySelectorAll('.daily-limit-val').forEach(el => {
                    el.textContent = typeof formatINR === 'function' ? formatINR(verifiedLimit) : '₹' + verifiedLimit.toFixed(2);
                });

                if (successBanner) successBanner.classList.remove('hidden');
                if (errorBanner) errorBanner.classList.add('hidden');
            } else {
                const msg = res && res.message ? res.message : 'No active account found matching this number. Please check for typos.';
                if (errorDesc) errorDesc.textContent = msg;
                if (errorBanner) errorBanner.classList.remove('hidden');
                if (successBanner) successBanner.classList.add('hidden');
            }
        } catch (err) {
            console.error('Account lookup error:', err);
            if (errorDesc) errorDesc.textContent = 'Failed to connect to account lookup service. Please check your network.';
            if (errorBanner) errorBanner.classList.remove('hidden');
            if (successBanner) successBanner.classList.add('hidden');
        } finally {
            if (findBtn) findBtn.disabled = false;
            if (findBtnText) findBtnText.textContent = 'Find Account';
        }
    }

    function resetLookup() {
        const lookupInput = document.getElementById('account-lookup');
        if (lookupInput) {
            lookupInput.value = '';
            lookupInput.focus();
        }
        const successBanner = document.getElementById('account-success');
        const errorBanner = document.getElementById('account-error');
        if (successBanner) successBanner.classList.add('hidden');
        if (errorBanner) errorBanner.classList.add('hidden');
        updateStepsUI(1);
    }

    // ── Step 2: Details ──
    function goToStep2() {
        if (!verifiedAccountNo) {
            updateStepsUI(1);
            return;
        }

        const sumName = document.getElementById('sum-name');
        const sumAcc = document.getElementById('sum-acc');
        const sumBefore = document.getElementById('sum-before');

        if (sumName) sumName.textContent = verifiedName;
        if (sumAcc) sumAcc.textContent = verifiedAccountNo;
        if (sumBefore) sumBefore.textContent = typeof formatINR === 'function' ? formatINR(verifiedBalance) : '₹' + verifiedBalance.toFixed(2);

        handleAmountChange();
        updateStepsUI(2);

        const depAmount = document.getElementById('dep-amount');
        if (depAmount) depAmount.focus();
    }

    function handleAmountChange() {
        const depAmount = document.getElementById('dep-amount');
        const depType = document.getElementById('dep-type');
        const amtWords = document.getElementById('amt-words');
        const sumAmt = document.getElementById('sum-amt');
        const sumAfter = document.getElementById('sum-after');
        const sumType = document.getElementById('sum-type');
        const validMsg = document.getElementById('amt-validation-msg');

        const val = parseFloat(depAmount ? depAmount.value : 0) || 0;
        const typeVal = depType ? depType.value : (isWithdraw ? 'Cash Withdrawal' : 'Cash Deposit');

        if (sumType) {
            sumType.textContent = typeVal.replace(' Deposit', '').replace(' Withdrawal', '');
        }

        const formattedVal = typeof formatINR === 'function' ? formatINR(val) : '₹' + val.toFixed(2);
        if (sumAmt) sumAmt.textContent = formattedVal;

        // Calculate After Balance
        let newBalance = 0;
        if (isWithdraw) {
            newBalance = verifiedBalance - val;
        } else {
            newBalance = verifiedBalance + val;
        }

        if (sumAfter) {
            sumAfter.textContent = typeof formatINR === 'function' ? formatINR(newBalance) : '₹' + newBalance.toFixed(2);
            if (newBalance < 0) {
                sumAfter.className = 'font-black text-sm text-danger-base animate-pulse';
            } else {
                sumAfter.className = 'font-black text-sm text-text-main dark:text-white';
            }
        }

        // Convert to words
        if (amtWords) {
            if (val > 0) {
                amtWords.textContent = numberToWords(val);
            } else {
                amtWords.textContent = 'Waiting for input...';
            }
        }

        // Real-time validations
        let hasError = false;
        let errMsg = '';

        if (isWithdraw) {
            if (val > verifiedBalance) {
                hasError = true;
                errMsg = `Amount exceeds available account balance (${typeof formatINR === 'function' ? formatINR(verifiedBalance) : verifiedBalance}).`;
            } else if (val > verifiedLimit && verifiedLimit > 0) {
                hasError = true;
                errMsg = `Amount exceeds remaining daily withdrawal limit (${typeof formatINR === 'function' ? formatINR(verifiedLimit) : verifiedLimit}).`;
            }
        }

        if (validMsg) {
            if (hasError) {
                validMsg.textContent = errMsg;
                validMsg.classList.remove('hidden');
            } else {
                validMsg.textContent = '';
                validMsg.classList.add('hidden');
            }
        }
    }

    // ── Step 3: Confirmation ──
    function goToStep3() {
        const depAmount = document.getElementById('dep-amount');
        const depType = document.getElementById('dep-type');
        const val = parseFloat(depAmount ? depAmount.value : 0) || 0;

        if (val < 100) {
            if (typeof showToast === 'function') {
                showToast('Minimum transaction amount is ₹100.', 'warning');
            }
            const validMsg = document.getElementById('amt-validation-msg');
            if (validMsg) {
                validMsg.textContent = 'Minimum transaction amount is ₹100.';
                validMsg.classList.remove('hidden');
            }
            if (depAmount) depAmount.focus();
            return;
        }

        if (isWithdraw) {
            if (val > verifiedBalance) {
                if (typeof showToast === 'function') {
                    showToast('Withdrawal amount exceeds available balance.', 'error');
                }
                return;
            }
            if (val > verifiedLimit && verifiedLimit > 0) {
                if (typeof showToast === 'function') {
                    showToast('Withdrawal amount exceeds daily limit.', 'error');
                }
                return;
            }
        }

        // Fill Confirmation Card
        const confirmName = document.getElementById('confirm-name');
        const confirmAcc = document.getElementById('confirm-acc');
        const confirmType = document.getElementById('confirm-type');
        const confirmAmt = document.getElementById('confirm-amt');
        const currentDateDisplay = document.getElementById('current-date-display');
        const errAlert = document.getElementById('deposit-error');

        if (errAlert) {
            errAlert.textContent = '';
            errAlert.classList.add('hidden');
        }

        if (confirmName) confirmName.textContent = verifiedName;
        if (confirmAcc) confirmAcc.textContent = verifiedAccountNo;
        if (confirmType) confirmType.textContent = depType ? depType.value : (isWithdraw ? 'Cash Withdrawal' : 'Cash Deposit');
        if (confirmAmt) confirmAmt.textContent = typeof formatINR === 'function' ? formatINR(val) : '₹' + val.toFixed(2);

        if (currentDateDisplay) {
            const now = new Date();
            currentDateDisplay.textContent = now.toLocaleDateString('en-IN', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
        }

        updateStepsUI(3);
    }

    // ── Execute Transaction ──
    async function executeTransaction() {
        if (isProcessing) return;
        isProcessing = true;

        const depAmount = document.getElementById('dep-amount');
        const depRef = document.getElementById('dep-ref');
        const finalBtn = document.getElementById('final-deposit-btn');
        const finalBtnText = document.getElementById('final-btn-text');
        const btnBackStep2 = document.getElementById('btn-back-step2');
        const errAlert = document.getElementById('deposit-error');

        const val = parseFloat(depAmount ? depAmount.value : 0) || 0;
        const refNote = depRef ? depRef.value.trim() : '';

        if (errAlert) {
            errAlert.textContent = '';
            errAlert.classList.add('hidden');
        }

        if (finalBtn) finalBtn.disabled = true;
        if (btnBackStep2) btnBackStep2.disabled = true;
        if (finalBtnText) finalBtnText.textContent = isWithdraw ? 'Processing Withdrawal...' : 'Executing Deposit...';

        const endpoint = isWithdraw ? '/staff-portal/withdrawal' : '/staff-portal/deposit';
        const payload = isWithdraw
            ? { account_no: verifiedAccountNo, amount: val, identity_notes: refNote || 'Teller In-Branch Withdrawal' }
            : { account_no: verifiedAccountNo, amount: val, reference_note: refNote || 'Cash Deposit' };

        try {
            const res = await window.api.post(endpoint, payload);

            if (res && res.success) {
                if (typeof showToast === 'function') {
                    showToast(res.message || 'Transaction processed successfully!', 'success');
                }

                // Redirect to receipt with automatic print trigger
                const txnId = res.transaction_id || (res.data ? res.data.transaction_id : '');
                let redirectUrl = res.redirect || (`staff-receipt.php?id=${encodeURIComponent(txnId)}`);
                if (!redirectUrl.includes('print=')) {
                    redirectUrl += (redirectUrl.includes('?') ? '&print=1' : '?print=1');
                }
                
                setTimeout(() => {
                    window.location.href = redirectUrl;
                }, 350);
            } else {
                const errMsg = res ? res.message : 'Transaction was rejected by the banking core.';
                if (errAlert) {
                    errAlert.textContent = errMsg;
                    errAlert.classList.remove('hidden');
                }
                if (typeof showToast === 'function') {
                    showToast(errMsg, 'error');
                }
            }
        } catch (error) {
            console.error('Transaction execution error:', error);
            const msg = 'A network error occurred while submitting the transaction.';
            if (errAlert) {
                errAlert.textContent = msg;
                errAlert.classList.remove('hidden');
            }
            if (typeof showToast === 'function') {
                showToast(msg, 'error');
            }
        } finally {
            isProcessing = false;
            if (finalBtn) finalBtn.disabled = false;
            if (btnBackStep2) btnBackStep2.disabled = false;
            if (finalBtnText) finalBtnText.textContent = isWithdraw ? 'Execute Withdrawal' : 'Execute Deposit';
        }
    }

    // Expose for debugging/testing
    window.FinzoStaffTransaction = {
        lookup: handleAccountLookup,
        goToStep: updateStepsUI,
        execute: executeTransaction
    };
})();
