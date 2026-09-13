(async function() {
  await fetchUserSession();
  buildSidebar('statement'); buildNavbar('Banking','Account Statement');
  handleStatusRedirect('statement');
  
  document.getElementById('stmt-generated').textContent = new Date().toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'}).toUpperCase();
  document.getElementById('stmt-name').textContent = UserState.name;
  document.getElementById('stmt-email').textContent = UserState.email;
  document.getElementById('stmt-acc-no').textContent = UserState.accountNo || 'NOT ASSIGNED';
  document.getElementById('stmt-acc-type').textContent = (UserState.accountType||'savings').charAt(0).toUpperCase()+(UserState.accountType||'savings').slice(1)+' Account';
  document.getElementById('stmt-closing').textContent = formatINR(UserState.balance || 0);

  try {
    if (window.api && typeof window.api.get === 'function') {
      const accRes = await window.api.get('/user/accounts');
      if (accRes && accRes.success && accRes.account) {
        document.getElementById('stmt-name').textContent = accRes.account.holder_name || UserState.name;
        document.getElementById('stmt-email').textContent = accRes.account.email || UserState.email;
        document.getElementById('stmt-acc-no').textContent = accRes.account.account_no || UserState.accountNo;
        document.getElementById('stmt-acc-type').textContent = (accRes.account.account_type || 'savings').toUpperCase() + ' Account';
        document.getElementById('stmt-closing').textContent = formatINR(accRes.account.balance || 0);
      }
    }
  } catch (e) {}

  const params = new URLSearchParams(window.location.search);
  const fromParam = params.get('from');
  const toParam = params.get('to');
  const autoPrint = params.get('print') === 'true';

  if (fromParam) {
    document.getElementById('stmt-from').value = fromParam;
  } else {
    const d = new Date();
    d.setDate(d.getDate() - 30);
    document.getElementById('stmt-from').value = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
  }
  
  if (toParam) {
    document.getElementById('stmt-to').value = toParam;
  } else {
    const d = new Date();
    document.getElementById('stmt-to').value = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
  }

  await generateStatement();
  if (autoPrint) setTimeout(() => window.print(), 500);
})();

async function generateStatement(){
  const f = document.getElementById('stmt-from').value;
  const t = document.getElementById('stmt-to').value;
  if(f && t) { document.getElementById('stmt-period').textContent = `Period: ${f} to ${t}`; }

  try {
    const query = { per_page: 100 };
    if (f) query.from_date = f;
    if (t) query.to_date = t;

    let data;
    if (window.api && typeof window.api.get === 'function') {
      data = await window.api.get('/user/transactions', query);
    } else {
      const p = new URLSearchParams(query);
      const res = await fetch('/Finzo/api/user/transactions.php?' + p.toString(), { credentials: 'same-origin' });
      data = await res.json();
    }
    
    if (data && data.success && data.transactions) {
      const tbody = document.querySelector('#statement-preview tbody');
      let totalCredits = 0, totalDebits = 0;
      
      if (data.transactions.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="p-12 text-center text-sm font-bold text-text-muted print:text-black">No transactions found for the selected period.</td></tr>';
      } else {
        tbody.innerHTML = data.transactions.map(tx => {
          const isCredit = tx.direction === 'credit' || tx.type === 'deposit' || tx.type === 'interest_credit';
          const amt = parseFloat(tx.amount);
          if (isCredit) totalCredits += amt; else totalDebits += amt;
          const date = new Date(tx.created_at).toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'}).toUpperCase();
          
          const typeBadge = isCredit 
            ? '<span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 print:border-none print:p-0 print:text-black">CREDIT</span>' 
            : '<span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-rose-500/10 text-rose-600 border border-rose-500/20 print:border-none print:p-0 print:text-black">DEBIT</span>';
            
          const amtClass = isCredit ? 'text-emerald-600 print:text-black' : 'text-text-main print:text-black';
          
          return `<tr class="hover:bg-surface-subtle transition-colors print:hover:bg-transparent border-b border-border-main last:border-0">
            <td class="p-3 align-middle text-xs font-bold text-text-main print:text-black">${date}</td>
            <td class="p-3 align-middle font-mono text-[10px] font-bold text-text-muted print:text-black tracking-widest break-all">${tx.txn_id || tx.reference_id || '-'}</td>
            <td class="p-3 align-middle text-xs font-bold text-text-main print:text-black">${tx.description || tx.type}</td>
            <td class="p-3 align-middle">${typeBadge}</td>
            <td class="p-3 align-middle text-right text-xs font-black ${amtClass}">${isCredit?'+':'-'}${formatINR(amt)}</td>
            <td class="p-3 align-middle text-right text-xs font-bold print:text-black">${formatINR(tx.balance_after || 0)}</td>
          </tr>`;
        }).join('');
      }
      
      document.getElementById('stmt-credits').textContent = formatINR(totalCredits);
      document.getElementById('stmt-debits').textContent = formatINR(totalDebits);
      showToast('Statement generated successfully','success');
    }
  } catch(e) { showToast('Failed to load transactions','error'); }
}
