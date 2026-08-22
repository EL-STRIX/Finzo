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
    const params = new URLSearchParams();
    if (f) params.set('from_date', f);
    if (t) params.set('to_date', t);
    params.set('limit', '500');
    
    const res = await fetch('/Finzo/api/user/transactions.php?' + params.toString(), { credentials: 'same-origin' });
    const data = await res.json();
    
    if (data.success && data.transactions) {
      const tbody = document.querySelector('#statement-preview tbody');
      let totalCredits = 0, totalDebits = 0;
      
      if (data.transactions.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="p-12 text-center text-sm font-bold text-[var(--color-text-muted)] print:text-black">No transactions found for the selected period.</td></tr>';
      } else {
        tbody.innerHTML = data.transactions.map(tx => {
          const isCredit = tx.direction === 'credit' || tx.type === 'deposit' || tx.type === 'interest_credit';
          const amt = parseFloat(tx.amount);
          if (isCredit) totalCredits += amt; else totalDebits += amt;
          const date = new Date(tx.created_at).toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'}).toUpperCase();
          
          const typeBadge = isCredit 
            ? '<span class="ds-badge ds-badge-success !py-0.5 !px-1.5 !text-[9px] print:border-none print:p-0 print:text-black">CREDIT</span>' 
            : '<span class="ds-badge !py-0.5 !px-1.5 !text-[9px] print:border-none print:p-0 print:text-black">DEBIT</span>';
            
          const amtClass = isCredit ? 'text-success-base print:text-black' : 'text-[var(--color-text-main)] print:text-black';
          
          return `<tr class="hover:bg-[var(--color-surface-hover)] transition-colors print:hover:bg-transparent">
            <td class="align-middle text-xs font-bold text-[var(--color-text-main)] print:text-black">${date}</td>
            <td class="align-middle font-mono text-[10px] font-bold text-[var(--color-text-muted)] print:text-black tracking-widest break-all">${tx.txn_id || tx.reference_id || '-'}</td>
            <td class="align-middle text-sm font-bold text-[var(--color-text-main)] print:text-black">${tx.description || tx.type}</td>
            <td class="align-middle">${typeBadge}</td>
            <td class="align-middle text-right text-sm font-black ${amtClass}">${isCredit?'+':'-'}${formatINR(amt)}</td>
            <td class="align-middle text-right text-xs font-bold print:text-black">${formatINR(tx.balance_after || 0)}</td>
          </tr>`;
        }).join('');
      }
      
      document.getElementById('stmt-credits').textContent = formatINR(totalCredits);
      document.getElementById('stmt-debits').textContent = formatINR(totalDebits);
      showToast('Statement generated','success');
    }
  } catch(e) { showToast('Failed to load transactions','error'); }
}
