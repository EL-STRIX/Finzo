/* ═════════════════════════════════════════════════════════════════
   FINZO — User Account Statement & Passbook Script
   Matches Executive Admin Passbook & Statement Layout & Print Engine
   ═════════════════════════════════════════════════════════════════ */

let currentStatementTransactions = [];
let currentAccountData = null;

function setText(id, text) {
  const el = document.getElementById(id);
  if (el) el.textContent = text;
}

function formatDisplayDate(dateVal) {
  if (!dateVal) return '';
  const d = new Date(dateVal);
  if (isNaN(d.getTime())) return String(dateVal);
  return d.toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function formatDateInput(d) {
  return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
}

function escapeHtml(str) {
  if (typeof str !== 'string') return '';
  return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

(async function initStatement() {
  await fetchUserSession();
  buildSidebar('statement');
  buildNavbar('Banking', 'Account Statement');
  handleStatusRedirect('statement');

  // Load live account details
  try {
    if (window.api && typeof window.api.get === 'function') {
      const accRes = await window.api.get('/user/accounts');
      if (accRes && accRes.success && accRes.account) {
        currentAccountData = accRes.account;
      }
    }
  } catch (e) {
    console.warn('Failed to load accounts in statement.js:', e);
  }

  // Parse URL query params
  const params = new URLSearchParams(window.location.search);
  const fromParam = params.get('from');
  const toParam = params.get('to');
  const autoPrint = params.get('print') === 'true';

  const today = new Date();
  const d30 = new Date();
  d30.setDate(d30.getDate() - 30);

  const fromInput = document.getElementById('stmt-from');
  const toInput = document.getElementById('stmt-to');

  if (fromInput) fromInput.value = fromParam || formatDateInput(d30);
  if (toInput) toInput.value = toParam || formatDateInput(today);

  await generateStatement();

  if (autoPrint) {
    setTimeout(() => printStatement(), 450);
  }
})();

/**
 * Switch Date Range via Quick Presets (Matching Admin Portal)
 */
function setPeriodPreset(preset, btnEl) {
  const fromInput = document.getElementById('stmt-from');
  const toInput = document.getElementById('stmt-to');
  const today = new Date();

  document.querySelectorAll('.stmt-preset-btn').forEach(b => {
    b.classList.remove('active', 'bg-primary', 'text-white', 'border-primary', 'bg-surface', 'text-text-muted');
  });
  if (btnEl) {
    btnEl.classList.add('active');
  }

  if (preset === 'all') {
    if (fromInput) fromInput.value = '';
    if (toInput) toInput.value = formatDateInput(today);
  } else if (preset === 'month') {
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    if (fromInput) fromInput.value = formatDateInput(firstDay);
    if (toInput) toInput.value = formatDateInput(today);
  } else if (preset === '30') {
    const d30 = new Date();
    d30.setDate(d30.getDate() - 30);
    if (fromInput) fromInput.value = formatDateInput(d30);
    if (toInput) toInput.value = formatDateInput(today);
  } else if (preset === '90') {
    const d90 = new Date();
    d90.setDate(d90.getDate() - 90);
    if (fromInput) fromInput.value = formatDateInput(d90);
    if (toInput) toInput.value = formatDateInput(today);
  }

  generateStatement();
}

/**
 * Fetch and Render Transactions in Monochrome Bank Passbook Format
 */
async function generateStatement() {
  const fromVal = document.getElementById('stmt-from')?.value || '';
  const toVal = document.getElementById('stmt-to')?.value || '';

  const user = {
    full_name: currentAccountData?.holder_name || UserState.name || 'Account Holder',
    account_no: currentAccountData?.account_no || UserState.accountNo || 'NOT ASSIGNED',
    customer_id: currentAccountData?.customer_id || UserState.customerId || 'FNZ-USR-001',
    account_type: (currentAccountData?.account_type || UserState.accountType || 'savings').toUpperCase() + ' ACCOUNT',
    phone: currentAccountData?.phone || UserState.phone || UserState.email || 'N/A',
    balance: parseFloat(currentAccountData?.balance ?? UserState.balance ?? 0)
  };

  // Populate Header Particulars
  setText('pb-full-name', user.full_name.toUpperCase());
  setText('pb-account-no', user.account_no);
  setText('pb-customer-id', user.customer_id);
  setText('pb-account-type', user.account_type);
  setText('pb-phone', user.phone);

  let periodLabel = '';
  if (fromVal && toVal) {
    periodLabel = `${formatDisplayDate(fromVal)} TO ${formatDisplayDate(toVal)}`;
  } else if (fromVal) {
    periodLabel = `FROM ${formatDisplayDate(fromVal)}`;
  } else if (toVal) {
    periodLabel = `UP TO ${formatDisplayDate(toVal)}`;
  } else {
    periodLabel = `ALL TIME (UP TO ${formatDisplayDate(new Date())})`;
  }
  setText('pb-generated-date', periodLabel);

  try {
    const query = { per_page: 500 };
    if (fromVal) query.from_date = fromVal;
    if (toVal) query.to_date = toVal;

    let data;
    if (window.api && typeof window.api.get === 'function') {
      data = await window.api.get('/user/transactions', query);
    } else {
      const p = new URLSearchParams(query);
      const res = await fetch('/Finzo/api/user/transactions.php?' + p.toString(), { credentials: 'same-origin' });
      data = await res.json();
    }

    if (data && data.success && data.transactions) {
      currentStatementTransactions = data.transactions;
      renderPassbookLedger(data.transactions, user);
      if (typeof showToast === 'function') {
        showToast('Statement generated successfully', 'success');
      }
    }
  } catch (err) {
    console.error('Error generating statement:', err);
    if (typeof showToast === 'function') {
      showToast('Failed to load statement records', 'error');
    }
  }
}

/**
 * Render Ledger Rows with Running Balance Computation
 */
function renderPassbookLedger(transactions, user) {
  const tbody = document.getElementById('pb-transactions-body');
  if (!tbody) return;

  if (!transactions || transactions.length === 0) {
    tbody.innerHTML = '<tr><td colspan="7" class="p-8 text-center text-black font-bold">No transaction entries found for the selected period.</td></tr>';
    setText('pb-total-debits', '₹ 0.00');
    setText('pb-total-credits', '₹ 0.00');
    setText('pb-net-balance', '₹ ' + (user.balance || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 }));
    return;
  }

  // Chronological order (oldest first)
  const chronological = [...transactions].sort((a, b) => new Date(a.created_at || 0) - new Date(b.created_at || 0));

  let totalCredits = 0;
  let totalDebits = 0;

  chronological.forEach(t => {
    const isCredit = t.direction === 'credit' || t.type === 'deposit' || t.type === 'interest_credit';
    const amt = parseFloat(t.amount || 0);
    if (isCredit) totalCredits += amt;
    else totalDebits += amt;
  });

  let rowsHtml = '';
  chronological.forEach((t, index) => {
    const isCredit = t.direction === 'credit' || t.type === 'deposit' || t.type === 'interest_credit';
    const amt = parseFloat(t.amount || 0);
    const debitDisplay = !isCredit ? amt.toLocaleString('en-IN', { minimumFractionDigits: 2 }) : '-';
    const creditDisplay = isCredit ? amt.toLocaleString('en-IN', { minimumFractionDigits: 2 }) : '-';
    const balDisplay = parseFloat(t.balance_after || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 });
    const dateStr = t.created_at ? new Date(t.created_at).toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '-';
    const particulars = (t.description || t.type || 'TRANSFER').toUpperCase() + (t.txn_id ? ' / ' + t.txn_id : '');

    rowsHtml += `
      <tr class="border-b border-black text-black">
        <td class="p-2 border-r border-black text-center">${index + 1}</td>
        <td class="p-2 border-r border-black text-center whitespace-nowrap">${dateStr}</td>
        <td class="p-2 border-r border-black uppercase text-[10.5px]">${escapeHtml(particulars)}</td>
        <td class="p-2 border-r border-black text-right">${debitDisplay}</td>
        <td class="p-2 border-r border-black text-right">${creditDisplay}</td>
        <td class="p-2 border-r border-black text-right font-bold">${balDisplay}</td>
        <td class="p-2 text-center text-[10px]">SYS</td>
      </tr>
    `;
  });

  tbody.innerHTML = rowsHtml;
  setText('pb-total-debits', '₹ ' + totalDebits.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
  setText('pb-total-credits', '₹ ' + totalCredits.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
  setText('pb-net-balance', '₹ ' + (user.balance || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 }));
}

/**
 * Print Statement using the Authentic Admin Bank Passbook & Statement Layout
 */
function printStatement() {
  const user = {
    full_name: currentAccountData?.holder_name || UserState.name || 'Account Holder',
    account_no: currentAccountData?.account_no || UserState.accountNo || 'NOT ASSIGNED',
    customer_id: currentAccountData?.customer_id || UserState.customerId || 'FNZ-USR-001',
    account_type: (currentAccountData?.account_type || UserState.accountType || 'savings').toUpperCase() + ' ACCOUNT',
    phone: currentAccountData?.phone || UserState.phone || UserState.email || 'N/A',
    balance: parseFloat(currentAccountData?.balance ?? UserState.balance ?? 0)
  };

  const fromVal = document.getElementById('stmt-from')?.value || '';
  const toVal = document.getElementById('stmt-to')?.value || '';

  let periodLabel = '';
  if (fromVal && toVal) {
    periodLabel = `${formatDisplayDate(fromVal)} TO ${formatDisplayDate(toVal)}`;
  } else if (fromVal) {
    periodLabel = `FROM ${formatDisplayDate(fromVal)}`;
  } else if (toVal) {
    periodLabel = `UP TO ${formatDisplayDate(toVal)}`;
  } else {
    periodLabel = `ALL TIME (UP TO ${formatDisplayDate(new Date())})`;
  }

  const chronological = [...currentStatementTransactions].sort((a, b) => new Date(a.created_at || 0) - new Date(b.created_at || 0));

  let totalCredits = 0;
  let totalDebits = 0;
  chronological.forEach(t => {
    const isCredit = t.direction === 'credit' || t.type === 'deposit' || t.type === 'interest_credit';
    const amt = parseFloat(t.amount || 0);
    if (isCredit) totalCredits += amt;
    else totalDebits += amt;
  });

  let rowsHtml = '';
  if (chronological.length === 0) {
    rowsHtml = '<tr><td colspan="7" style="padding: 20px; text-align: center; font-weight: bold;">No transaction entries found for the selected period.</td></tr>';
  } else {
    chronological.forEach((t, index) => {
      const isCredit = t.direction === 'credit' || t.type === 'deposit' || t.type === 'interest_credit';
      const amt = parseFloat(t.amount || 0);
      const debitDisplay = !isCredit ? amt.toLocaleString('en-IN', { minimumFractionDigits: 2 }) : '-';
      const creditDisplay = isCredit ? amt.toLocaleString('en-IN', { minimumFractionDigits: 2 }) : '-';
      const balDisplay = parseFloat(t.balance_after || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 });
      const dateStr = t.created_at ? new Date(t.created_at).toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '-';
      const particulars = (t.description || t.type || 'TRANSFER').toUpperCase() + (t.txn_id ? ' / ' + t.txn_id : '');

      rowsHtml += `
        <tr>
          <td style="text-align: center;">${index + 1}</td>
          <td style="text-align: center; white-space: nowrap;">${dateStr}</td>
          <td style="text-transform: uppercase; font-size: 10px;">${escapeHtml(particulars)}</td>
          <td style="text-align: right;">${debitDisplay}</td>
          <td style="text-align: right;">${creditDisplay}</td>
          <td style="text-align: right; font-weight: bold;">${balDisplay}</td>
          <td style="text-align: center; font-size: 9px;">SYS</td>
        </tr>
      `;
    });
  }

  const printHtml = `
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <title>Account Statement - ${escapeHtml(user.full_name)}</title>
      <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        @page { size: A4 portrait; margin: 10mm 12mm 12mm 12mm; }
        body {
          font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
          background: #ffffff;
          color: #000000;
          padding: 8px;
          font-size: 11px;
          line-height: 1.3;
          -webkit-print-color-adjust: exact;
          print-color-adjust: exact;
        }
        .passbook-container {
          width: 100%;
          border: 2px solid #000000;
          padding: 14px 16px;
        }
        .bank-header {
          text-align: center;
          border-bottom: 2px solid #000000;
          padding-bottom: 8px;
          margin-bottom: 10px;
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
          padding-top: 6px;
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
          padding: 4px 6px;
        }
        .table-grid th {
          background-color: #f2f2f2;
          font-weight: 900;
          text-transform: uppercase;
          font-size: 10px;
        }
        .lbl {
          font-weight: bold;
          background-color: #f8f8f8;
          width: 25%;
        }
        .val {
          font-weight: bold;
          width: 25%;
        }
        .summary-box {
          width: 100%;
          border-collapse: collapse;
          border: 2px solid #000000;
          margin-bottom: 10px;
          font-size: 10.5px;
          text-align: center;
        }
        .summary-box td {
          border: 1px solid #000000;
          padding: 6px 8px;
        }
        .summary-title {
          font-size: 9.5px;
          font-weight: bold;
          text-transform: uppercase;
        }
        .summary-amt {
          font-size: 13px;
          font-weight: 900;
          margin-top: 2px;
        }
        .footer-notice {
          border-top: 1px solid #000000;
          padding-top: 6px;
          font-size: 9.5px;
          line-height: 1.35;
        }
      </style>
    </head>
    <body>
      <div class="passbook-container">
        <div class="bank-header">
          <div class="bank-title">FINZO BANK</div>
          <div class="bank-sub">SAVINGS / CURRENT ACCOUNT PASSBOOK & STATEMENT</div>
          <div class="bank-meta">
            <span>BRANCH: MAIN BRANCH, MUMBAI</span>
            <span>IFSC: FINZ0002026</span>
            <span>MICR: 400026002</span>
          </div>
        </div>

        <table class="table-grid">
          <tr>
            <td class="lbl">A/C HOLDER NAME</td>
            <td class="val" style="font-size: 12px;">${escapeHtml(user.full_name)}</td>
            <td class="lbl">ACCOUNT NO.</td>
            <td class="val" style="font-size: 12px;">${escapeHtml(user.account_no)}</td>
          </tr>
          <tr>
            <td class="lbl">CUSTOMER ID (CIF)</td>
            <td class="val">${escapeHtml(user.customer_id)}</td>
            <td class="lbl">ACCOUNT TYPE</td>
            <td class="val">${escapeHtml(user.account_type)}</td>
          </tr>
          <tr>
            <td class="lbl">MOBILE / EMAIL</td>
            <td class="val">${escapeHtml(user.phone)}</td>
            <td class="lbl">STATEMENT PERIOD</td>
            <td class="val">${escapeHtml(periodLabel)}</td>
          </tr>
          <tr>
            <td class="lbl">BRANCH / ADDRESS</td>
            <td class="val" colspan="3">MAIN BRANCH, MUMBAI • FINZ0002026</td>
          </tr>
        </table>

        <table class="summary-box">
          <tr>
            <td style="width: 33.33%;">
              <div class="summary-title">TOTAL WITHDRAWALS (DR)</div>
              <div class="summary-amt">₹ ${totalDebits.toLocaleString('en-IN', { minimumFractionDigits: 2 })}</div>
            </td>
            <td style="width: 33.33%;">
              <div class="summary-title">TOTAL DEPOSITS (CR)</div>
              <div class="summary-amt">₹ ${totalCredits.toLocaleString('en-IN', { minimumFractionDigits: 2 })}</div>
            </td>
            <td style="width: 33.34%; background-color: #f2f2f2;">
              <div class="summary-title">CLOSING AVAILABLE BALANCE</div>
              <div class="summary-amt">₹ ${(user.balance || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 })}</div>
            </td>
          </tr>
        </table>

        <table class="table-grid">
          <thead>
            <tr>
              <th style="width: 35px; text-align: center;">LINE</th>
              <th style="width: 78px; text-align: center;">DATE</th>
              <th style="text-align: left;">PARTICULARS / DETAILS</th>
              <th style="width: 95px; text-align: right;">WITHDRAWAL (DR) ₹</th>
              <th style="width: 95px; text-align: right;">DEPOSIT (CR) ₹</th>
              <th style="width: 100px; text-align: right;">BALANCE ₹</th>
              <th style="width: 35px; text-align: center;">INIT</th>
            </tr>
          </thead>
          <tbody>
            ${rowsHtml}
          </tbody>
        </table>

        <div class="footer-notice">
          <div>* Please examine this passbook/statement immediately. Discrepancies, if any, should be reported within 15 days.</div>
          <div style="display: flex; justify-content: space-between; margin-top: 4px; font-weight: bold; font-size: 9px;">
            <span>* OFFICIAL COMPUTER GENERATED BANK STATEMENT — NO PHYSICAL SIGNATURE REQUIRED • FINZO CORE BANKING</span>
            <span>PAGE 1 OF 1</span>
          </div>
        </div>
      </div>
    </body>
    </html>
  `;

  let printFrame = document.getElementById('finzo-print-iframe');
  if (!printFrame) {
    printFrame = document.createElement('iframe');
    printFrame.id = 'finzo-print-iframe';
    printFrame.style.position = 'fixed';
    printFrame.style.right = '0';
    printFrame.style.bottom = '0';
    printFrame.style.width = '0';
    printFrame.style.height = '0';
    printFrame.style.border = 'none';
    document.body.appendChild(printFrame);
  }

  try {
    const doc = printFrame.contentWindow.document;
    doc.open();
    doc.write(printHtml);
    doc.close();

    setTimeout(() => {
      printFrame.contentWindow.focus();
      printFrame.contentWindow.print();
    }, 250);
  } catch (e) {
    console.warn('Iframe print failed, falling back to window.print():', e);
    window.print();
  }
}

/**
 * Open Standalone Official Bank Statement PDF
 */
function openOfficialStatementPdf() {
  const fromVal = document.getElementById('stmt-from')?.value || '';
  const toVal = document.getElementById('stmt-to')?.value || '';
  let url = '/Finzo/api/v1/documents/statement';
  const query = [];
  if (fromVal) query.push('from_date=' + encodeURIComponent(fromVal));
  if (toVal) query.push('to_date=' + encodeURIComponent(toVal));
  if (query.length > 0) url += '?' + query.join('&');
  window.open(url, '_blank');
}

// Expose globals for inline attributes
window.generateStatement = generateStatement;
window.setPeriodPreset = setPeriodPreset;
window.printStatement = printStatement;
window.openOfficialStatementPdf = openOfficialStatementPdf;
