/**
 * Finzo Staff — Official Bank Counter Receipt Controller
 * Pure Vanilla JavaScript — Clean Architecture & Strict Monochrome Banking Slip
 */

(function () {
    'use strict';

    let currentTransaction = null;

    function init() {
        const params = new URLSearchParams(window.location.search);
        const txnId = params.get('id') || params.get('txn_id');
        const autoPrint = params.get('print') === '1' || params.get('autoprint') === '1';

        if (!txnId) {
            if (typeof showToast === 'function') {
                showToast('Transaction ID not found.', 'error');
            }
            setTimeout(() => {
                window.location.href = 'staff-dashboard.php';
            }, 1200);
            return;
        }

        // Bind print button
        const printBtn = document.getElementById('btn-print-receipt');
        if (printBtn) {
            printBtn.addEventListener('click', function (e) {
                e.preventDefault();
                printReceiptDocument();
            });
        }

        // Global keyboard shortcut: Ctrl+P or Cmd+P
        window.addEventListener('keydown', function (e) {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'p') {
                e.preventDefault();
                printReceiptDocument();
            }
        });

        loadReceipt(txnId, autoPrint);
    }

    // Number to Words Converter for Indian Rupee Banking
    function convertNumberToWords(amount) {
        if (typeof window.numberToWords === 'function') {
            return window.numberToWords(amount);
        }
        if (window.FinzoUtils && typeof window.FinzoUtils.numberToWords === 'function') {
            return window.FinzoUtils.numberToWords(amount);
        }

        const num = Math.floor(Math.abs(amount));
        if (num === 0) return 'RUPEES ZERO ONLY';

        const units = ['', 'ONE', 'TWO', 'THREE', 'FOUR', 'FIVE', 'SIX', 'SEVEN', 'EIGHT', 'NINE', 'TEN', 'ELEVEN', 'TWELVE', 'THIRTEEN', 'FOURTEEN', 'FIFTEEN', 'SIXTEEN', 'SEVENTEEN', 'EIGHTEEN', 'NINETEEN'];
        const tens = ['', '', 'TWENTY', 'THIRTY', 'FORTY', 'FIFTY', 'SIXTY', 'SEVENTY', 'EIGHTY', 'NINETY'];

        function convertThreeDigits(n) {
            let str = '';
            if (n >= 100) {
                str += units[Math.floor(n / 100)] + ' HUNDRED ';
                n %= 100;
            }
            if (n >= 20) {
                str += tens[Math.floor(n / 10)] + ' ';
                n %= 10;
            }
            if (n > 0) {
                str += units[n] + ' ';
            }
            return str.trim();
        }

        let words = '';
        const crore = Math.floor(num / 10000000);
        let rem = num % 10000000;
        const lakh = Math.floor(rem / 100000);
        rem = rem % 100000;
        const thousand = Math.floor(rem / 1000);
        rem = rem % 1000;

        if (crore > 0) words += convertThreeDigits(crore) + ' CRORE ';
        if (lakh > 0) words += convertThreeDigits(lakh) + ' LAKH ';
        if (thousand > 0) words += convertThreeDigits(thousand) + ' THOUSAND ';
        if (rem > 0) words += convertThreeDigits(rem);

        return 'RUPEES ' + words.trim() + ' ONLY';
    }

    function formatINR(val) {
        const num = parseFloat(val) || 0;
        return '₹' + num.toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    async function loadReceipt(txnId, autoPrint) {
        try {
            let res = null;
            if (window.api && typeof window.api.get === 'function') {
                res = await window.api.get('/staff-portal/receipt', { id: txnId });
            } else {
                const response = await fetch('/Finzo/api/v1/staff-portal/receipt?id=' + encodeURIComponent(txnId), {
                    credentials: 'include',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                res = await response.json();
            }

            if (res && res.success && res.transaction) {
                currentTransaction = res.transaction;
                renderReceipt(currentTransaction);

                if (autoPrint) {
                    setTimeout(() => {
                        printReceiptDocument();
                    }, 400);
                }
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
                showToast('Failed to load official receipt.', 'error');
            } else {
                alert('Failed to load receipt details.');
            }
        }
    }

    function renderReceipt(t) {
        const setElText = (id, text) => {
            const el = document.getElementById(id);
            if (el) el.textContent = text;
        };

        setElText('receipt-ref-id', t.txn_id || 'TXN---');

        let formattedDate = '—';
        if (t.date) {
            const raw = t.date.replace(' ', 'T');
            const dateObj = new Date(raw);
            if (!isNaN(dateObj.getTime())) {
                formattedDate = dateObj.toLocaleDateString('en-IN', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                }) + ' ' + dateObj.toLocaleTimeString('en-IN', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: true
                });
            } else {
                formattedDate = t.date;
            }
        }
        setElText('r-date', formattedDate);

        const isDeposit = (t.type || '').toLowerCase() === 'deposit';
        setElText('r-txn-type', isDeposit ? 'CASH DEPOSIT' : 'CASH WITHDRAWAL');

        setElText('r-branch', t.branch_name || 'Finzo Main Branch');
        setElText('r-branch-code', t.branch_code || 'FNZ-HQ');

        setElText('r-cust-name', t.customer_name || 'Account Holder');
        setElText('r-cust-account', t.account_no || '---');
        setElText('r-cust-account-type', t.account_type || 'Savings Account');

        const amtVal = parseFloat(t.amount) || 0;
        const balance = parseFloat(t.balance_after) || 0;
        const prevBalance = isDeposit ? (balance - amtVal) : (balance + amtVal);

        setElText('r-amount', formatINR(amtVal));
        setElText('r-amount-words', convertNumberToWords(amtVal));
        setElText('r-status', (t.status || 'COMPLETED').toUpperCase() + ' / POSTED');

        setElText('r-prev-balance', formatINR(prevBalance));
        setElText('r-avail-balance', formatINR(balance));
        setElText('r-narration', t.reference_note || t.description || (isDeposit ? 'Branch Cash Deposit' : 'Branch Cash Withdrawal'));

        setElText('r-staff', t.staff_name || 'Finzo Staff');
        setElText('r-staff-id', t.staff_id || 'STF---');
    }

    /**
     * Dedicated Pure Black & White Vintage Bank Counter Receipt Printing via Isolated Frame
     */
    function printReceiptDocument() {
        if (!currentTransaction) {
            window.print();
            return;
        }

        const t = currentTransaction;
        const isDeposit = (t.type || '').toLowerCase() === 'deposit';
        const amtVal = parseFloat(t.amount) || 0;
        const balance = parseFloat(t.balance_after) || 0;
        const prevBalance = isDeposit ? (balance - amtVal) : (balance + amtVal);

        let formattedDate = '—';
        if (t.date) {
            const raw = t.date.replace(' ', 'T');
            const dateObj = new Date(raw);
            if (!isNaN(dateObj.getTime())) {
                formattedDate = dateObj.toLocaleDateString('en-IN', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                }) + ' ' + dateObj.toLocaleTimeString('en-IN', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: true
                });
            } else {
                formattedDate = t.date;
            }
        }

        const words = convertNumberToWords(amtVal);
        const narration = t.reference_note || t.description || (isDeposit ? 'Branch Cash Deposit' : 'Branch Cash Withdrawal');

        const printHtml = `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt - ${escapeHtml(t.txn_id)}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 15mm;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: "Courier New", Courier, "Lucida Console", Monaco, monospace;
            color: #000000;
            background: #ffffff;
            padding: 0;
            font-size: 11px;
            line-height: 1.35;
        }
        .receipt-container {
            width: 100%;
            max-width: 650px;
            margin: 0 auto;
            border: 2px solid #000000;
            padding: 16px 20px;
        }
        .bank-header {
            text-align: center;
            border-bottom: 2px solid #000000;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .bank-title {
            font-size: 22px;
            font-weight: 900;
            letter-spacing: 2px;
            text-transform: uppercase;
            font-family: Georgia, serif;
        }
        .bank-sub {
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-top: 2px;
        }
        .bank-meta {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            font-weight: bold;
            border-top: 1px solid #000000;
            padding-top: 5px;
            margin-top: 6px;
        }
        .table-grid {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000000;
            margin-bottom: 10px;
            font-size: 10.5px;
        }
        .table-grid td, .table-grid th {
            border: 1px solid #000000;
            padding: 5px 8px;
        }
        .section-header {
            background-color: #f2f2f2;
            font-weight: 900;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.5px;
        }
        .lbl {
            font-weight: bold;
            background-color: #f8f8f8;
            width: 30%;
        }
        .val {
            font-weight: bold;
        }
        .amount-box {
            width: 100%;
            border: 2px solid #000000;
            margin-bottom: 10px;
            padding: 10px 14px;
            background-color: #fcfcfc;
        }
        .amount-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #000000;
            padding-bottom: 6px;
            margin-bottom: 6px;
        }
        .amount-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .amount-val {
            font-size: 20px;
            font-weight: 900;
        }
        .amount-words {
            font-size: 10.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .signatures {
            margin-top: 25px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            text-align: center;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .sig-col {
            width: 40%;
        }
        .sig-line {
            border-bottom: 1px solid #000000;
            margin-bottom: 4px;
            height: 20px;
        }
        .footer-notice {
            border-top: 1px solid #000000;
            padding-top: 6px;
            font-size: 9.5px;
            line-height: 1.35;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="bank-header">
            <div class="bank-title">FINZO BANK</div>
            <div class="bank-sub">OFFICIAL CASH TRANSACTION VOUCHER</div>
            <div class="bank-meta">
                <div>BRANCH: ${escapeHtml(t.branch_name || 'Finzo Main Branch')} (${escapeHtml(t.branch_code || 'FNZ-HQ')})</div>
                <div>IFSC: FINZ0002026</div>
                <div>MICR: 400026002</div>
            </div>
        </div>

        <table class="table-grid">
            <tr>
                <td class="lbl">VOUCHER / TXN ID</td>
                <td class="val" style="font-size: 12px; font-weight: 900;">${escapeHtml(t.txn_id)}</td>
                <td class="lbl">DATE &amp; TIME</td>
                <td class="val">${escapeHtml(formattedDate)}</td>
            </tr>
            <tr>
                <td class="lbl">TRANSACTION NATURE</td>
                <td class="val" style="font-weight: 900;">${isDeposit ? 'CASH DEPOSIT' : 'CASH WITHDRAWAL'}</td>
                <td class="lbl">CHANNEL / DESK</td>
                <td class="val">BRANCH COUNTER / TELLER</td>
            </tr>
        </table>

        <table class="table-grid">
            <tr>
                <td colspan="4" class="section-header">CUSTOMER &amp; ACCOUNT PARTICULARS</td>
            </tr>
            <tr>
                <td class="lbl">ACCOUNT HOLDER</td>
                <td class="val" style="font-size: 12px; text-transform: uppercase;">${escapeHtml(t.customer_name || 'Customer')}</td>
                <td class="lbl">ACCOUNT NUMBER</td>
                <td class="val" style="font-size: 12px; font-family: monospace;">${escapeHtml(t.account_no)}</td>
            </tr>
            <tr>
                <td class="lbl">ACCOUNT TYPE</td>
                <td class="val" style="text-transform: uppercase;">${escapeHtml(t.account_type || 'Savings Account')}</td>
                <td class="lbl">BRANCH</td>
                <td class="val">${escapeHtml(t.branch_name || 'Finzo Main Branch')}</td>
            </tr>
        </table>

        <div class="amount-box">
            <div class="amount-row">
                <div class="amount-title">TRANSACTION AMOUNT</div>
                <div class="amount-val">${formatINR(amtVal)}</div>
            </div>
            <div class="amount-words">
                AMOUNT IN WORDS: ${escapeHtml(words)}
            </div>
        </div>

        <table class="table-grid">
            <tr>
                <td class="lbl">PREVIOUS BALANCE</td>
                <td class="val">${formatINR(prevBalance)}</td>
                <td class="lbl">CLOSING BALANCE</td>
                <td class="val" style="font-weight: 900;">${formatINR(balance)}</td>
            </tr>
            <tr>
                <td class="lbl">PARTICULARS / REF</td>
                <td class="val" colspan="3">${escapeHtml(narration)}</td>
            </tr>
            <tr>
                <td class="lbl">TELLER / CASHIER</td>
                <td class="val">${escapeHtml(t.staff_name || 'Staff')} (${escapeHtml(t.staff_id || 'STF---')})</td>
                <td class="lbl">CORE STATUS</td>
                <td class="val" style="font-weight: 900;">${escapeHtml((t.status || 'COMPLETED').toUpperCase())} / POSTED</td>
            </tr>
        </table>

        <div class="signatures">
            <div class="sig-col">
                <div class="sig-line"></div>
                <div>CUSTOMER SIGNATURE</div>
            </div>
            <div class="sig-col">
                <div class="sig-line"></div>
                <div>CASHIER / TELLER SIGNATURE</div>
            </div>
        </div>

        <div class="footer-notice">
            <div>* Please verify cash and receipt before leaving counter. Discrepancies must be notified immediately.</div>
            <div>* This is a computer-generated bank counter voucher. Official Bank Audit Record.</div>
            <div style="margin-top: 3px; font-weight: bold; letter-spacing: 1px;">FINZO ENTERPRISE CORE BANKING • 24X7 SUPPORT: 1800-FINZO-BANK</div>
        </div>
    </div>
</body>
</html>`;

        let printFrame = document.getElementById('finzo-receipt-print-iframe');
        if (!printFrame) {
            printFrame = document.createElement('iframe');
            printFrame.id = 'finzo-receipt-print-iframe';
            printFrame.style.position = 'fixed';
            printFrame.style.right = '0';
            printFrame.style.bottom = '0';
            printFrame.style.width = '0';
            printFrame.style.height = '0';
            printFrame.style.border = '0';
            document.body.appendChild(printFrame);
        }

        const frameDoc = printFrame.contentWindow || printFrame.contentDocument.document || printFrame.contentDocument;
        const doc = frameDoc.document || frameDoc;
        doc.open();
        doc.write(printHtml);
        doc.close();

        setTimeout(() => {
            printFrame.contentWindow.focus();
            printFrame.contentWindow.print();
        }, 250);
    }

    function escapeHtml(str) {
        if (!str) return '';
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return String(str).replace(/[&<>"']/g, (m) => map[m]);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
