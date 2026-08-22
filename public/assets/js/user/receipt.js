(async function initReceipt() {
  await fetchUserSession();
  buildSidebar('transactions'); buildNavbar('Banking','Receipt');

  const params = new URLSearchParams(window.location.search);
  const txnId = params.get('id');

  if (txnId) {
    try {
      const res = await fetch('/Finzo/api/user/transactions.php?search=' + encodeURIComponent(txnId), { credentials: 'same-origin' });
      const data = await res.json();
      if (data.success && data.transactions && data.transactions.length > 0) {
        const tx = data.transactions.find(t => t.txn_id === txnId) || data.transactions[0];
        
        const date = new Date(tx.created_at);
        const dateStr = date.toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'}).toUpperCase();
        const timeStr = date.toLocaleTimeString('en-IN',{hour:'2-digit',minute:'2-digit'}) + ' IST';

        document.getElementById('rcpt-no').textContent = 'RCP' + txnId.slice(-8);
        document.getElementById('rcpt-txn-id').textContent = tx.txn_id || txnId;
        document.getElementById('rcpt-datetime').textContent = dateStr + ' — ' + timeStr;
        document.getElementById('rcpt-desc').textContent = tx.description || 'Fund Transfer';

        const isCredit = tx.direction === 'credit' || tx.type === 'deposit' || tx.type === 'interest_credit';
        document.getElementById('rcpt-type').innerHTML = isCredit
          ? '<span class="ds-badge ds-badge-success !py-0.5 !px-1.5 !text-[9px] print:border-none print:p-0 print:text-black">CREDIT / DEPOSIT</span>'
          : '<span class="ds-badge !py-0.5 !px-1.5 !text-[9px] print:border-none print:p-0 print:text-black uppercase">' + tx.type + '</span>';

        let fromName, fromAcc, toName, toAcc;
        
        if (tx.type === 'deposit') {
            fromName = 'Cash Deposit';
            fromAcc = 'Branch / ATM';
            toName = UserState.name;
            toAcc = UserState.accountNo;
        } else if (tx.type === 'withdrawal') {
            fromName = UserState.name;
            fromAcc = UserState.accountNo;
            toName = 'Cash Withdrawal';
            toAcc = 'Branch / ATM';
        } else if (tx.type === 'interest_credit') {
            fromName = 'Finzo Bank';
            fromAcc = 'Interest Settlement';
            toName = UserState.name;
            toAcc = UserState.accountNo;
        } else {
            if (tx.direction === 'credit') {
                fromName = (tx.from_name && tx.from_name !== 'N/A') ? tx.from_name : 'External Account';
                fromAcc = tx.from_account_no || '-';
                toName = UserState.name;
                toAcc = UserState.accountNo;
            } else {
                fromName = UserState.name;
                fromAcc = UserState.accountNo;
                toName = (tx.to_name && tx.to_name !== 'N/A') ? tx.to_name : 'External Beneficiary';
                toAcc = tx.to_account_no || '-';
            }
        }
        
        document.getElementById('rcpt-from-name').textContent = fromName;
        document.getElementById('rcpt-from-acc').textContent = fromAcc;
        document.getElementById('rcpt-to-name').textContent = toName;
        document.getElementById('rcpt-to-acc').textContent = toAcc;

        const amt = parseFloat(tx.amount || 0);
        document.getElementById('rcpt-amount').textContent = formatINR(amt);
        document.getElementById('rcpt-amount2').textContent = formatINR(amt);
        document.getElementById('rcpt-balance-after').textContent = formatINR(tx.balance_after || 0);
        return;
      }
    } catch (e) { console.warn('Failed to load receipt:', e); }
  }

  // Fallback UI
  const now = new Date();
  document.getElementById('rcpt-no').textContent = 'RCP' + Date.now().toString().slice(-8);
  document.getElementById('rcpt-txn-id').textContent = txnId || 'UNKNOWN';
  document.getElementById('rcpt-datetime').textContent = now.toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'}).toUpperCase() + ' — ' + now.toLocaleTimeString('en-IN',{hour:'2-digit',minute:'2-digit'}) + ' IST';
  document.getElementById('rcpt-balance-after').textContent = formatINR(UserState.balance || 0);
  document.getElementById('rcpt-from-name').textContent = UserState.name;
  document.getElementById('rcpt-from-acc').textContent = UserState.accountNo || 'NOT ASSIGNED';
  document.getElementById('rcpt-amount').textContent = '₹0.00';
  document.getElementById('rcpt-amount2').textContent = '₹0.00';
  document.getElementById('rcpt-type').innerHTML = '<span class="ds-badge !py-0.5 !px-1.5 !text-[9px]">UNKNOWN</span>';
  document.getElementById('rcpt-desc').textContent = 'Not Available';
  document.getElementById('rcpt-to-name').textContent = '-';
  document.getElementById('rcpt-to-acc').textContent = '-';
})();
