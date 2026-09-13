/**
 * Finzo - All Transactions Controller
 * Enterprise Real-time Transaction Ledger with server-side pagination, direction filtering, CSV export, and live metrics.
 */

let txnState = {
  page: 1,
  perPage: 15,
  pages: 1,
  total: 0,
  activeTab: 'all', // 'all' | 'credit' | 'debit' | 'transfer'
  search: '',
  fromDate: '',
  toDate: '',
  transactions: []
};

let searchDebounceTimer = null;

// Helper: Safe currency formatting
function formatMoney(amount) {
  if (typeof window.formatINR === 'function') {
    const formatted = window.formatINR(amount);
    return formatted.startsWith('₹') ? formatted : '₹' + formatted;
  }
  return '₹' + parseFloat(amount || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// Helper: HTML escaper
function escapeHtml(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

(async function initTransactions() {
  await fetchUserSession();
  buildSidebar('transactions');
  buildNavbar('Banking', 'All Transactions');
  handleStatusRedirect('transactions');

  // Handle URL parameters (e.g. ?type=transfer or ?search=...)
  const urlParams = new URLSearchParams(window.location.search);
  const typeParam = urlParams.get('type');
  if (typeParam && ['all', 'credit', 'debit', 'transfer'].includes(typeParam)) {
    txnState.activeTab = typeParam;
    updateTabActiveUI(typeParam);
  }

  const searchParam = urlParams.get('search');
  if (searchParam) {
    txnState.search = searchParam;
    const sInput = document.getElementById('search');
    if (sInput) sInput.value = searchParam;
    toggleSearchClearBtn(true);
  }

  const fromParam = urlParams.get('from') || urlParams.get('from_date');
  if (fromParam) {
    txnState.fromDate = fromParam;
    const fInput = document.getElementById('from-date');
    if (fInput) fInput.value = fromParam;
  }

  const toParam = urlParams.get('to') || urlParams.get('to_date');
  if (toParam) {
    txnState.toDate = toParam;
    const tInput = document.getElementById('to-date');
    if (tInput) tInput.value = toParam;
  }

  await loadAccountHeader();
  await loadTransactions();
})();

async function loadAccountHeader() {
  try {
    const res = await window.api.get('/user/accounts');
    if (res && res.success && res.account) {
      const acc = res.account;
      const accNoEl = document.getElementById('overview-acc-no');
      const balanceEl = document.getElementById('overview-balance');

      if (accNoEl) {
        accNoEl.textContent = `${acc.account_no} • ${(acc.account_type || 'Savings').toUpperCase()}`;
      }
      if (balanceEl) {
        balanceEl.textContent = formatMoney(acc.balance);
      }
    } else if (typeof UserState !== 'undefined') {
      const accNoEl = document.getElementById('overview-acc-no');
      const balanceEl = document.getElementById('overview-balance');
      if (accNoEl) accNoEl.textContent = UserState.accountNo || 'Not Assigned';
      if (balanceEl) balanceEl.textContent = formatMoney(UserState.balance || 0);
    }
  } catch (e) {
    if (typeof UserState !== 'undefined') {
      const accNoEl = document.getElementById('overview-acc-no');
      const balanceEl = document.getElementById('overview-balance');
      if (accNoEl) accNoEl.textContent = UserState.accountNo || 'Active Account';
      if (balanceEl) balanceEl.textContent = formatMoney(UserState.balance || 0);
    }
  }
}

async function loadTransactions() {
  const tbody = document.getElementById('txn-body');
  const refreshIcon = document.getElementById('refresh-icon');

  if (refreshIcon) refreshIcon.classList.add('animate-spin');

  // Render Skeleton rows if empty or loading
  if (tbody) {
    tbody.innerHTML = `
      <tr>
        <td class="p-4"><div class="skeleton w-24 h-4 mb-1"></div><div class="skeleton w-16 h-3"></div></td>
        <td class="p-4"><div class="skeleton w-36 h-4 mb-1"></div><div class="skeleton w-24 h-3"></div></td>
        <td class="p-4"><div class="skeleton w-16 h-5 rounded-full"></div></td>
        <td class="p-4"><div class="skeleton w-28 h-4 font-mono"></div></td>
        <td class="p-4 text-right"><div class="skeleton w-20 h-4 ml-auto"></div></td>
        <td class="p-4 text-right"><div class="skeleton w-20 h-4 ml-auto"></div></td>
        <td class="p-4 text-center"><div class="skeleton w-16 h-5 rounded-full mx-auto"></div></td>
        <td class="p-4 text-center"><div class="skeleton w-8 h-8 rounded-full mx-auto"></div></td>
      </tr>
      <tr>
        <td class="p-4"><div class="skeleton w-24 h-4 mb-1"></div><div class="skeleton w-16 h-3"></div></td>
        <td class="p-4"><div class="skeleton w-36 h-4 mb-1"></div><div class="skeleton w-24 h-3"></div></td>
        <td class="p-4"><div class="skeleton w-16 h-5 rounded-full"></div></td>
        <td class="p-4"><div class="skeleton w-28 h-4 font-mono"></div></td>
        <td class="p-4 text-right"><div class="skeleton w-20 h-4 ml-auto"></div></td>
        <td class="p-4 text-right"><div class="skeleton w-20 h-4 ml-auto"></div></td>
        <td class="p-4 text-center"><div class="skeleton w-16 h-5 rounded-full mx-auto"></div></td>
        <td class="p-4 text-center"><div class="skeleton w-8 h-8 rounded-full mx-auto"></div></td>
      </tr>
    `;
  }

  const query = {
    page: txnState.page,
    per_page: txnState.perPage
  };

  if (txnState.activeTab === 'credit') {
    query.direction = 'credit';
  } else if (txnState.activeTab === 'debit') {
    query.direction = 'debit';
  } else if (txnState.activeTab === 'transfer') {
    query.type = 'transfer';
  }

  if (txnState.search) query.search = txnState.search;
  if (txnState.fromDate) query.from_date = txnState.fromDate;
  if (txnState.toDate) query.to_date = txnState.toDate;

  try {
    const data = await window.api.get('/user/transactions', query);

    if (data && data.success) {
      txnState.transactions = (data.transactions || []).map(tx => {
        const rawType = tx.type || 'transfer';
        let direction = tx.direction;
        if (!direction) {
          direction = (rawType === 'deposit' || rawType === 'interest_credit') ? 'credit' : 'debit';
        }
        return {
          id: tx.txn_id || '-',
          dateObj: new Date(tx.created_at),
          created_at: tx.created_at,
          type: rawType,
          direction: direction,
          amount: parseFloat(tx.amount || 0),
          balanceAfter: parseFloat(tx.balance_after || 0),
          description: tx.description || tx.type || 'Transaction',
          referenceNote: tx.reference_note || '',
          fromAccNo: tx.from_account_no || '',
          fromName: tx.from_name || '',
          toAccNo: tx.to_account_no || '',
          toName: tx.to_name || '',
          status: (tx.status || 'completed').toLowerCase()
        };
      });

      txnState.total = parseInt(data.total || txnState.transactions.length, 10);
      txnState.pages = Math.max(1, parseInt(data.pages || Math.ceil(txnState.total / txnState.perPage), 10));
      txnState.page = parseInt(data.current_page || txnState.page, 10);
    } else {
      txnState.transactions = [];
      txnState.total = 0;
      txnState.pages = 1;
    }
  } catch (err) {
    console.error('Failed to load transactions:', err);
    txnState.transactions = [];
    txnState.total = 0;
    txnState.pages = 1;
    showToast('Failed to load transactions. Please check your connection.', 'error');
  } finally {
    if (refreshIcon) refreshIcon.classList.remove('animate-spin');
    renderTable();
    updateOverviewMetrics();
  }
}

function renderTable() {
  const tbody = document.getElementById('txn-body');
  if (!tbody) return;

  const countEl = document.getElementById('txn-count');
  const pagInfo = document.getElementById('pag-info');

  if (txnState.transactions.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="8" class="p-8 text-center">
          <div class="py-12 flex flex-col items-center justify-center text-center max-w-sm mx-auto">
            <div class="w-16 h-16 rounded-2xl bg-surface-subtle border border-border-main text-text-muted flex items-center justify-center mb-3 shadow-inner">
              <span class="material-symbols-outlined text-3xl">receipt_long</span>
            </div>
            <div class="font-display text-base font-bold text-text-main mb-1">No Transactions Found</div>
            <div class="text-xs text-text-muted mb-4">No records match the active filter criteria. Try expanding your date range or adjusting search keywords.</div>
            <button class="btn btn-secondary text-xs h-9 px-4 font-bold" onclick="resetFilters()">Clear Filters</button>
          </div>
        </td>
      </tr>
    `;
    if (countEl) countEl.textContent = '0 records found';
    if (pagInfo) pagInfo.textContent = 'Showing 0 of 0';
    renderPagination(0, 1, 1);
    return;
  }

  tbody.innerHTML = txnState.transactions.map(t => {
    const isCredit = t.direction === 'credit';
    const rowClass = isCredit ? 'bg-emerald-500/[0.02]' : '';
    const amtColor = isCredit ? 'text-emerald-600 dark:text-emerald-400' : 'text-text-main';
    const amtPrefix = isCredit ? '+' : '-';

    // Format date & time
    const dateStr = t.dateObj.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }).toUpperCase();
    const timeStr = t.dateObj.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit' }) + ' IST';

    // Format counterparty & details
    let counterpartyHtml = '';
    if (t.type === 'transfer') {
      if (isCredit) {
        const sender = (t.fromName && t.fromName !== 'N/A') ? t.fromName : 'External Account';
        counterpartyHtml = `<div class="text-[11px] text-text-muted font-medium mt-0.5 flex items-center gap-1">
          <span class="material-symbols-outlined text-[13px] text-emerald-500">arrow_downward</span>
          From: <span class="text-text-main font-semibold">${escapeHtml(sender)}</span> <span class="font-mono text-[10px] opacity-75">(${escapeHtml(t.fromAccNo || '-')})</span>
        </div>`;
      } else {
        const recipient = (t.toName && t.toName !== 'N/A') ? t.toName : 'External Beneficiary';
        counterpartyHtml = `<div class="text-[11px] text-text-muted font-medium mt-0.5 flex items-center gap-1">
          <span class="material-symbols-outlined text-[13px] text-rose-500">arrow_upward</span>
          To: <span class="text-text-main font-semibold">${escapeHtml(recipient)}</span> <span class="font-mono text-[10px] opacity-75">(${escapeHtml(t.toAccNo || '-')})</span>
        </div>`;
      }
    } else if (t.type === 'deposit') {
      counterpartyHtml = `<div class="text-[11px] text-text-muted mt-0.5">Cash Deposit • Branch / Digital Channel</div>`;
    } else if (t.type === 'withdrawal') {
      counterpartyHtml = `<div class="text-[11px] text-text-muted mt-0.5">Cash Withdrawal • Branch / ATM</div>`;
    } else if (t.type === 'interest_credit') {
      counterpartyHtml = `<div class="text-[11px] text-text-muted mt-0.5">Finzo Automated Interest Credit Yield</div>`;
    }

    // Type Badge
    let typeBadge = '';
    if (t.type === 'deposit') {
      typeBadge = `<span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">Deposit</span>`;
    } else if (t.type === 'withdrawal') {
      typeBadge = `<span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">Withdrawal</span>`;
    } else if (t.type === 'interest_credit') {
      typeBadge = `<span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20">Interest</span>`;
    } else {
      typeBadge = `<span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">Transfer</span>`;
    }

    // Status Badge
    let statusBadge = '';
    if (t.status === 'completed') {
      statusBadge = `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Success</span>`;
    } else if (t.status === 'pending') {
      statusBadge = `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20"><span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>Pending</span>`;
    } else {
      statusBadge = `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>${escapeHtml(t.status)}</span>`;
    }

    return `
      <tr class="${rowClass} hover:bg-surface-subtle transition-colors">
        <td class="p-4 align-middle whitespace-nowrap">
          <div class="text-xs font-bold text-text-main">${dateStr}</div>
          <div class="text-[10px] font-bold text-text-muted mt-0.5">${timeStr}</div>
        </td>
        <td class="p-4 align-middle">
          <div class="text-xs font-bold text-text-main">${escapeHtml(t.description)}</div>
          ${counterpartyHtml}
        </td>
        <td class="p-4 align-middle whitespace-nowrap">
          ${typeBadge}
        </td>
        <td class="p-4 align-middle whitespace-nowrap">
          <button type="button" class="inline-flex items-center gap-1 font-mono text-[11px] font-bold text-text-muted hover:text-primary transition-colors py-0.5 px-1.5 rounded hover:bg-surface" onclick="copyTxnId('${escapeHtml(t.id)}')" title="Copy Transaction ID">
            <span>${escapeHtml(t.id)}</span>
            <span class="material-symbols-outlined text-xs">content_copy</span>
          </button>
        </td>
        <td class="p-4 align-middle text-right whitespace-nowrap">
          <div class="font-mono text-sm font-black ${amtColor}">${amtPrefix}${formatMoney(t.amount)}</div>
        </td>
        <td class="p-4 align-middle text-right whitespace-nowrap">
          <div class="font-mono text-xs font-bold text-text-main">${formatMoney(t.balanceAfter)}</div>
        </td>
        <td class="p-4 align-middle text-center whitespace-nowrap">
          ${statusBadge}
        </td>
        <td class="p-4 align-middle text-center whitespace-nowrap">
          <button type="button" onclick="openReceipt('${escapeHtml(t.id)}')" class="w-8 h-8 rounded-lg bg-surface-subtle hover:bg-primary/10 hover:text-primary border border-border-main text-text-muted inline-flex items-center justify-center transition-colors shadow-sm cursor-pointer" title="Official Receipt PDF / Print">
            <span class="material-symbols-outlined text-base">receipt_long</span>
          </button>
        </td>
      </tr>
    `;
  }).join('');

  if (countEl) {
    countEl.textContent = `${txnState.total} record${txnState.total !== 1 ? 's' : ''} found`;
  }

  const startIdx = (txnState.page - 1) * txnState.perPage + 1;
  const endIdx = Math.min(txnState.page * txnState.perPage, txnState.total);
  if (pagInfo) {
    pagInfo.textContent = txnState.total > 0 ? `Showing ${startIdx}–${endIdx} of ${txnState.total}` : 'No records';
  }

  renderPagination(txnState.total, txnState.page, txnState.pages);
}

function renderPagination(total, page, pages) {
  const container = document.getElementById('pag-nums');
  if (!container) return;

  if (pages <= 1) {
    container.innerHTML = '';
    return;
  }

  let html = `
    <button class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all border border-border-main ${page <= 1 ? 'text-text-disabled cursor-not-allowed bg-surface-subtle' : 'text-text-main bg-surface hover:bg-surface-subtle'}" onclick="goPage(-1)" ${page <= 1 ? 'disabled' : ''}>
      ← Prev
    </button>
  `;

  // Determine window of pages around current page
  let startPage = Math.max(1, page - 2);
  let endPage = Math.min(pages, page + 2);

  if (startPage > 1) {
    html += `<button class="w-8 h-8 rounded-lg text-xs font-bold text-text-muted hover:bg-surface-subtle border border-border-main" onclick="setPage(1)">1</button>`;
    if (startPage > 2) {
      html += `<span class="px-1 text-xs text-text-muted">...</span>`;
    }
  }

  for (let i = startPage; i <= endPage; i++) {
    const isActive = i === page;
    const btnClass = isActive 
      ? 'bg-primary text-white border-primary shadow-sm font-black' 
      : 'text-text-muted hover:bg-surface-subtle border-border-main font-bold';
    html += `<button class="w-8 h-8 rounded-lg text-xs transition-all border ${btnClass}" onclick="setPage(${i})">${i}</button>`;
  }

  if (endPage < pages) {
    if (endPage < pages - 1) {
      html += `<span class="px-1 text-xs text-text-muted">...</span>`;
    }
    html += `<button class="w-8 h-8 rounded-lg text-xs font-bold text-text-muted hover:bg-surface-subtle border border-border-main" onclick="setPage(${pages})">${pages}</button>`;
  }

  html += `
    <button class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all border border-border-main ${page >= pages ? 'text-text-disabled cursor-not-allowed bg-surface-subtle' : 'text-text-main bg-surface hover:bg-surface-subtle'}" onclick="goPage(1)" ${page >= pages ? 'disabled' : ''}>
      Next →
    </button>
  `;

  container.innerHTML = html;
}

function updateOverviewMetrics() {
  const creditsEl = document.getElementById('overview-credits');
  const debitsEl = document.getElementById('overview-debits');
  const countEl = document.getElementById('overview-count');

  let totalCredits = 0;
  let totalDebits = 0;

  txnState.transactions.forEach(t => {
    if (t.direction === 'credit') {
      totalCredits += t.amount;
    } else {
      totalDebits += t.amount;
    }
  });

  if (creditsEl) creditsEl.textContent = formatMoney(totalCredits);
  if (debitsEl) debitsEl.textContent = formatMoney(totalDebits);
  if (countEl) countEl.textContent = txnState.total.toLocaleString();
}

function setFilterTab(tab, el) {
  txnState.activeTab = tab;
  txnState.page = 1;

  updateTabActiveUI(tab);
  loadTransactions();
}

function updateTabActiveUI(activeTab) {
  document.querySelectorAll('.filter-tab').forEach(btn => {
    const tab = btn.getAttribute('data-tab');
    if (tab === activeTab) {
      btn.className = 'filter-tab active px-5 py-3 text-xs font-bold border-b-2 transition-colors border-primary text-primary flex items-center gap-1.5';
    } else {
      btn.className = 'filter-tab px-5 py-3 text-xs font-bold border-b-2 border-transparent text-text-muted hover:text-text-main transition-colors flex items-center gap-1.5';
    }
  });
}

function onSearchInput() {
  const input = document.getElementById('search');
  if (!input) return;

  const val = input.value.trim();
  toggleSearchClearBtn(Boolean(val));

  clearTimeout(searchDebounceTimer);
  searchDebounceTimer = setTimeout(() => {
    txnState.search = val;
    txnState.page = 1;
    loadTransactions();
  }, 350);
}

function clearSearch() {
  const input = document.getElementById('search');
  if (input) {
    input.value = '';
    toggleSearchClearBtn(false);
  }
  txnState.search = '';
  txnState.page = 1;
  loadTransactions();
}

function toggleSearchClearBtn(show) {
  const btn = document.getElementById('search-clear-btn');
  if (btn) {
    if (show) btn.classList.remove('hidden');
    else btn.classList.add('hidden');
  }
}

function onDateChange() {
  const fInput = document.getElementById('from-date');
  const tInput = document.getElementById('to-date');

  const fVal = fInput ? fInput.value : '';
  const tVal = tInput ? tInput.value : '';

  if (fVal && tVal && fVal > tVal) {
    showToast('From Date cannot be later than To Date', 'warning');
    return;
  }

  txnState.fromDate = fVal;
  txnState.toDate = tVal;
  txnState.page = 1;
  loadTransactions();
}

function onPerPageChange(val) {
  txnState.perPage = parseInt(val, 10) || 15;
  txnState.page = 1;
  loadTransactions();
}

function resetFilters() {
  const sInput = document.getElementById('search');
  const fInput = document.getElementById('from-date');
  const tInput = document.getElementById('to-date');
  const pSelect = document.getElementById('per-page-select');

  if (sInput) sInput.value = '';
  if (fInput) fInput.value = '';
  if (tInput) tInput.value = '';
  if (pSelect) pSelect.value = '15';

  toggleSearchClearBtn(false);

  txnState.activeTab = 'all';
  txnState.search = '';
  txnState.fromDate = '';
  txnState.toDate = '';
  txnState.perPage = 15;
  txnState.page = 1;

  updateTabActiveUI('all');
  loadTransactions();
  showToast('Filters reset to default', 'info');
}

function goPage(delta) {
  const target = txnState.page + delta;
  if (target >= 1 && target <= txnState.pages) {
    setPage(target);
  }
}

function setPage(pageNum) {
  txnState.page = pageNum;
  loadTransactions();
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function copyTxnId(id) {
  if (navigator.clipboard) {
    navigator.clipboard.writeText(id).then(() => {
      showToast('Transaction ID copied: ' + id, 'success');
    }).catch(() => {
      showToast('ID: ' + id, 'info');
    });
  } else {
    showToast('ID: ' + id, 'info');
  }
}

function openReceipt(txnId) {
  if (!txnId) return;
  window.open(`/Finzo/api/v1/documents/receipt?id=${encodeURIComponent(txnId)}`, '_blank');
}

function printStatement() {
  const f = txnState.fromDate;
  const t = txnState.toDate;
  let url = 'statement.php?print=true';
  if (f) url += '&from=' + encodeURIComponent(f);
  if (t) url += '&to=' + encodeURIComponent(t);
  window.location.href = url;
}

function exportTransactionsCsv() {
  if (!txnState.transactions || txnState.transactions.length === 0) {
    showToast('No transactions available to export', 'warning');
    return;
  }

  const headers = [
    'Transaction ID',
    'Date',
    'Time',
    'Type',
    'Description',
    'Direction',
    'Counterparty',
    'Amount (INR)',
    'Balance After (INR)',
    'Status'
  ];

  const rows = txnState.transactions.map(t => {
    const dStr = t.dateObj.toLocaleDateString('en-GB');
    const tStr = t.dateObj.toLocaleTimeString('en-IN');
    let counterparty = '';
    if (t.type === 'transfer') {
      counterparty = t.direction === 'credit'
        ? `From: ${t.fromName || 'External'} (${t.fromAccNo || '-'})`
        : `To: ${t.toName || 'Beneficiary'} (${t.toAccNo || '-'})`;
    } else {
      counterparty = t.type;
    }

    return [
      `"${t.id}"`,
      `"${dStr}"`,
      `"${tStr}"`,
      `"${t.type}"`,
      `"${(t.description || '').replace(/"/g, '""')}"`,
      `"${t.direction}"`,
      `"${counterparty.replace(/"/g, '""')}"`,
      t.amount.toFixed(2),
      t.balanceAfter.toFixed(2),
      `"${t.status}"`
    ].join(',');
  });

  const csvContent = [headers.join(','), ...rows].join('\r\n');
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');

  const todayStr = new Date().toISOString().split('T')[0];
  link.setAttribute('href', url);
  link.setAttribute('download', `Finzo_Transactions_${todayStr}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);

  showToast('Transactions exported successfully!', 'success');
}

// Expose globals for inline attributes and template actions
window.setFilterTab = setFilterTab;
window.filterTxns = (type, el) => setFilterTab(type, el);
window.onSearchInput = onSearchInput;
window.clearSearch = clearSearch;
window.onDateChange = onDateChange;
window.onPerPageChange = onPerPageChange;
window.resetFilters = resetFilters;
window.loadTransactions = loadTransactions;
window.goPage = goPage;
window.setPage = setPage;
window.copyTxnId = copyTxnId;
window.printStatement = printStatement;
window.openReceipt = openReceipt;
window.exportTransactionsCsv = exportTransactionsCsv;
