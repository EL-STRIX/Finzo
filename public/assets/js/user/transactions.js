(async function() {
  await fetchUserSession();
  buildSidebar('transactions'); buildNavbar('Banking','All Transactions');
  handleStatusRedirect('transactions');
  loadTransactions();
})();

const ROW_PP = 8;
let page = 1, activeFilter = 'all';
let allTxns = [];

// Fetch transactions from API
async function loadTransactions() {
  try {
    const params = new URLSearchParams();
    if (activeFilter === 'transfer') params.set('type', 'transfer');
    const search = document.getElementById('search').value;
    if (search) params.set('search', search);
    const fromDate = document.getElementById('from-date').value;
    if (fromDate) params.set('from_date', fromDate);
    const toDate = document.getElementById('to-date').value;
    if (toDate) params.set('to_date', toDate);
    params.set('limit', '200');

    const res = await fetch('/Finzo/api/user/transactions.php?' + params.toString(), { credentials: 'same-origin' });
    const data = await res.json();
    if (data.success && data.transactions) {
      allTxns = data.transactions.map(tx => ({
        id: tx.txn_id || tx.reference_id || '-',
        date: new Date(tx.created_at).toLocaleDateString('en-GB', {day:'2-digit',month:'short',year:'numeric'}).toUpperCase(),
        time: new Date(tx.created_at).toLocaleTimeString('en-IN', {hour:'2-digit',minute:'2-digit'}) + ' IST',
        desc: tx.description || tx.type,
        rawType: tx.type,
        direction: tx.direction || (tx.type === 'deposit' || tx.type === 'interest_credit' ? 'credit' : 'debit'),
        amount: parseFloat(tx.amount),
        balanceAfter: parseFloat(tx.balance_after || 0),
        status: tx.status || 'completed'
      }));
    } else {
      allTxns = [];
    }
  } catch (err) {
    console.warn('Failed to load transactions:', err);
    allTxns = [];
  }
  renderTable();
}

let filtered = [];

function filterTxns(type, el) {
  activeFilter = type;
  document.querySelectorAll('.filter-tab').forEach(b => {
    b.className = 'filter-tab px-6 py-4 text-sm font-bold border-b-2 border-transparent text-[var(--color-text-muted)] hover:text-[var(--color-text-main)] transition-colors';
  });
  el.className = 'filter-tab active px-6 py-4 text-sm font-bold border-b-2 transition-colors border-primary-base text-primary-base';
  page = 1;
  loadTransactions();
}

function resetFilters() {
  document.getElementById('search').value = '';
  document.getElementById('from-date').value = '';
  document.getElementById('to-date').value = '';
  activeFilter = 'all';
  document.querySelectorAll('.filter-tab').forEach((b,i) => {
    b.className = i === 0 ? 
      'filter-tab active px-6 py-4 text-sm font-bold border-b-2 transition-colors border-primary-base text-primary-base' : 
      'filter-tab px-6 py-4 text-sm font-bold border-b-2 border-transparent text-[var(--color-text-muted)] hover:text-[var(--color-text-main)] transition-colors';
  });
  page = 1; loadTransactions();
}

function renderTable() {
  const search = document.getElementById('search').value.toLowerCase();
  filtered = allTxns.filter(t => {
    if (activeFilter === 'credit' && t.direction !== 'credit') return false;
    if (activeFilter === 'debit' && t.direction !== 'debit') return false;
    if (activeFilter === 'transfer' && t.rawType !== 'transfer') return false;
    if (search && !String(t.desc).toLowerCase().includes(search) && !String(t.id).toLowerCase().includes(search)) return false;
    return true;
  });
  const total = filtered.length;
  const totalPages = Math.max(1, Math.ceil(total / ROW_PP));
  if (page > totalPages) page = totalPages;
  const slice = filtered.slice((page-1)*ROW_PP, page*ROW_PP);
  const tbody = document.getElementById('txn-body');
  if (slice.length === 0) {
    tbody.innerHTML = `<tr><td colspan="5">
      <div class="py-12 flex flex-col items-center justify-center text-center">
        <div class="w-16 h-16 rounded-full bg-[var(--color-surface-subtle)] text-[var(--color-text-disabled)] flex items-center justify-center mb-4">
          <span class="material-symbols-outlined text-3xl">receipt_long</span>
        </div>
        <div class="font-display text-lg font-bold text-[var(--color-text-main)] mb-1">No transactions found</div>
        <div class="text-sm text-[var(--color-text-muted)]">Try adjusting your filters</div>
      </div>
    </td></tr>`;
  } else {
    tbody.innerHTML = slice.map((t,i) => {
      const isCredit = t.direction === 'credit';
      const rowClass = isCredit ? 'bg-success-surface/20' : '';
      const amtClass = isCredit ? 'text-success-base' : 'text-[var(--color-text-main)]';
      const amtSign = isCredit ? '+' : '-';
      return `<tr class="${rowClass} hover:bg-[var(--color-surface-hover)] transition-colors border-b border-[var(--color-border-main)] last:border-0">
        <td class="p-4 align-middle"><div class="text-xs font-bold text-[var(--color-text-main)] tracking-wide">${t.date}</div><div class="text-[10px] font-bold text-[var(--color-text-muted)] tracking-wider mt-0.5">${t.time}</div></td>
        <td class="p-4 align-middle"><div class="text-sm font-bold text-[var(--color-text-main)]">${t.desc}</div><div class="font-mono text-[10px] font-bold text-[var(--color-text-muted)] tracking-widest mt-1">${t.id}</div></td>
        <td class="p-4 align-middle text-right"><div class="text-sm font-black ${amtClass}">${amtSign}${formatINR(t.amount)}</div></td>
        <td class="p-4 align-middle text-right text-xs font-bold tracking-wide">${formatINR(t.balanceAfter)}</td>
        <td class="p-4 align-middle text-center"><button class="btn btn-secondary w-8 h-8 !p-0 rounded-full inline-flex items-center justify-center" onclick="window.location.href='receipt.php?id=${t.id}'" title="Download Receipt"><span class="material-symbols-outlined text-[16px]">download</span></button></td>
      </tr>`;
    }).join('');
  }
  document.getElementById('txn-count').textContent = `${total} transaction${total !== 1 ? 's' : ''} found`;
  const s = (page-1)*ROW_PP+1, e = Math.min(page*ROW_PP, total);
  document.getElementById('pag-info').textContent = total ? `Showing ${s}–${e} of ${total}` : 'No results';
  
  const pn = document.getElementById('pag-nums');
  pn.innerHTML = `<button class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors ${page<=1?'text-[var(--color-text-disabled)] cursor-not-allowed':'text-[var(--color-text-main)] hover:bg-[var(--color-border-main)]'}" onclick="goPage(-1)" ${page<=1?'disabled':''}>← Prev</button>`;
  for(let i=1;i<=totalPages&&i<=5;i++) {
    const activeClass = i===page ? 'bg-primary-base text-white shadow-sm' : 'text-[var(--color-text-muted)] hover:bg-[var(--color-border-main)]';
    pn.innerHTML += `<button class="w-8 h-8 rounded-lg text-xs font-bold transition-colors flex items-center justify-center ${activeClass}" onclick="page=${i};renderTable()">${i}</button>`;
  }
  pn.innerHTML += `<button class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors ${page>=totalPages?'text-[var(--color-text-disabled)] cursor-not-allowed':'text-[var(--color-text-main)] hover:bg-[var(--color-border-main)]'}" onclick="goPage(1)" ${page>=totalPages?'disabled':''}>Next →</button>`;
}

function goPage(dir) { page = Math.max(1, Math.min(Math.ceil(filtered.length/ROW_PP), page+dir)); renderTable(); }

function printStatement() {
  const f = document.getElementById('from-date').value;
  const t = document.getElementById('to-date').value;
  let url = 'statement.php?print=true';
  if(f) url += '&from=' + f;
  if(t) url += '&to=' + t;
  window.location.href = url;
}
