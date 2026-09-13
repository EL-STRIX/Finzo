/**
 * Finzo Staff — Receipt Controller
 * Pure Vanilla JavaScript — Clean Architecture & Real-Time Data Binding
 */

(function () {
    'use strict';

    function init() {
        const params = new URLSearchParams(window.location.search);
        const txnId = params.get('id') || params.get('txn_id');

        if (!txnId) {
            if (typeof showToast === 'function') {
                showToast('Transaction ID not found.', 'error');
            }
            setTimeout(() => {
                window.location.href = 'staff-dashboard.php';
            }, 1000);
            return;
        }

        loadReceipt(txnId);
    }

    async function loadReceipt(txnId) {
        try {
            let res = null;
            if (window.api && typeof window.api.get === 'function') {
                res = await window.api.get('/staff-portal/receipt', { id: txnId });
            } else {
                const response = await fetch('/Finzo/api/v1/staff-portal/receipt?id=' + encodeURIComponent(txnId), {
                    credentials: 'same-origin'
                });
                res = await response.json();
            }

            if (res && res.success && res.transaction) {
                const t = res.transaction;

                const setElText = (id, text) => {
                    const el = document.getElementById(id);
                    if (el) el.textContent = text;
                };

                setElText('receipt-ref-id', t.txn_id || txnId);

                if (t.date) {
                    const dateObj = new Date(t.date);
                    const formattedDate = dateObj.toLocaleDateString('en-IN', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric'
                    }) + ' ' + dateObj.toLocaleTimeString('en-IN', {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    });
                    setElText('r-date', formattedDate);
                }

                setElText('r-staff', t.staff_name || 'Finzo Staff');
                setElText('r-staff-id', t.staff_id || '---');
                setElText('r-branch', t.branch_name || 'Finzo Main Branch');

                setElText('r-cust-name', t.customer_name || 'Account Holder');
                setElText('r-cust-account', t.account_no || '---');

                const isDeposit = (t.type || '').toLowerCase() === 'deposit';
                setElText('r-txn-type', isDeposit ? 'Cash Deposit' : 'Cash Withdrawal');

                const amtVal = parseFloat(t.amount) || 0;
                const balance = parseFloat(t.balance_after) || 0;
                const prevBalance = isDeposit ? (balance - amtVal) : (balance + amtVal);

                const amtEl = document.getElementById('r-amount');
                if (amtEl) {
                    if (isDeposit) {
                        amtEl.className = 'text-3xl font-black text-emerald-600 dark:text-emerald-400 font-mono tracking-tight';
                        amtEl.textContent = '+' + (typeof formatINR === 'function' ? formatINR(amtVal) : '₹' + amtVal.toFixed(2));
                    } else {
                        amtEl.className = 'text-3xl font-black text-rose-600 dark:text-rose-400 font-mono tracking-tight';
                        amtEl.textContent = '-' + (typeof formatINR === 'function' ? formatINR(amtVal) : '₹' + amtVal.toFixed(2));
                    }
                }

                setElText('r-prev-balance', typeof formatINR === 'function' ? formatINR(prevBalance) : '₹' + prevBalance.toFixed(2));
                setElText('r-avail-balance', typeof formatINR === 'function' ? formatINR(balance) : '₹' + balance.toFixed(2));
            } else {
                const msg = res && res.message ? res.message : 'Receipt not found.';
                if (typeof showToast === 'function') {
                    showToast(msg, 'error');
                } else {
                    alert('Receipt Error: ' + msg);
                }
            }
        } catch (err) {
            console.error('Receipt Error:', err);
            if (typeof showToast === 'function') {
                showToast('Failed to load receipt details.', 'error');
            } else {
                alert('Failed to load receipt details.');
            }
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
