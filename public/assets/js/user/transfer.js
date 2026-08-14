(async function() {
    await fetchUserSession();
    buildSidebar('transfer'); buildNavbar('Banking', 'Fund Transfer');
    handleStatusRedirect('transfer');
  })();

  let recipientVerified = false;
  let recipientName = '';
  let recipientAccNo = '';

  (async function loadBeneficiaries() {
    try {
      const res = await fetch('/Finzo/api/user/beneficiary.php', { credentials: 'same-origin' });
      const data = await res.json();
      const container = document.getElementById('beneficiary-chips');
      if (data.success && data.beneficiaries && data.beneficiaries.length > 0) {
        container.innerHTML = data.beneficiaries.map(b => 
          `<button class="px-4 py-2 rounded-full border-2 border-[var(--color-border-main)] bg-[var(--color-surface-subtle)] text-xs font-bold text-[var(--color-text-muted)] hover:border-primary-base hover:text-primary-base transition-colors flex items-center gap-2" onclick="useBeneficiary('${b.account_no}','${b.nickname}')">
            <span class="material-symbols-outlined text-[14px]">star</span> ${b.nickname}
          </button>`
        ).join('');
      } else {
        container.innerHTML = '<div class="text-xs text-[var(--color-text-muted)] italic">No saved beneficiaries found.</div>';
      }
    } catch (e) { 
      document.getElementById('beneficiary-chips').innerHTML = '<div class="text-xs text-danger-base font-bold">Failed to load beneficiaries.</div>';
    }
    
    const params = new URLSearchParams(window.location.search);
    const toAcc = params.get('to');
    if (toAcc) {
      document.getElementById('to-account').value = toAcc;
      lookupAccount();
    }
  })();

  function useBeneficiary(acc, name) {
    document.getElementById('to-account').value = acc;
    lookupAccount();
  }

  function resetLookup() {
    recipientVerified = false;
    document.getElementById('lookup-result').classList.add('hidden');
    updateSummary();
  }

  async function lookupAccount() {
    const accNo = document.getElementById('to-account').value.trim().toUpperCase();
    if (!accNo) { showToast('Please enter an account number', 'warning'); return; }
    if (accNo === UserState.accountNo) { showToast('Cannot transfer to your own account', 'error'); return; }
    
    try {
      const res = await fetch('/Finzo/api/user/account-lookup.php?account_no=' + encodeURIComponent(accNo), { credentials: 'same-origin' });
      const data = await res.json();
      document.getElementById('lookup-result').classList.remove('hidden');
      
      if (data.success && data.account) {
        recipientVerified = true;
        recipientName = data.account.holder_name;
        recipientAccNo = accNo;
        document.getElementById('lookup-found').classList.remove('hidden');
        document.getElementById('lookup-not-found').classList.add('hidden');
        document.getElementById('recipient-name').textContent = data.account.holder_name;
        document.getElementById('recipient-type').textContent = data.account.account_type || 'Savings';
        showToast('Recipient identity verified', 'success');
        updateSummary();
      } else {
        recipientVerified = false;
        document.getElementById('lookup-found').classList.add('hidden');
        document.getElementById('lookup-not-found').classList.remove('hidden');
      }
    } catch (err) {
      showToast('Lookup failed. Please try again.', 'error');
    }
  }

  function updateSummary() {
    const amount = parseFloat(document.getElementById('transfer-amount').value || 0);
    const balance = parseFloat(UserState.balance || 0);

    const wordsDiv = document.getElementById('amount-words');
    const errorDiv = document.getElementById('amount-error');
    
    if (amount > 0) {
      wordsDiv.textContent = numberToWords(amount) + ' Rupees Only';
      if (amount > balance) {
        errorDiv.textContent = 'Insufficient balance (Current: ₹' + formatINR(balance) + ')';
        errorDiv.classList.remove('hidden');
      } else {
        errorDiv.classList.add('hidden');
      }
    } else {
      wordsDiv.textContent = '';
      errorDiv.classList.add('hidden');
    }
  }

  function confirmTransfer() {
    const amount = parseFloat(document.getElementById('transfer-amount').value || 0);
    const balance = parseFloat(UserState.balance || 0);
    const pin = document.getElementById('transfer-pin').value;

    if (!recipientVerified) { showToast('Please verify a recipient account first', 'warning'); return; }
    if (amount <= 0) { showToast('Please enter a valid amount', 'warning'); return; }
    if (amount > balance) { showToast('Insufficient funds in source account', 'error'); return; }
    if (!pin || pin.length < 4) { showToast('Please enter your 4-digit security PIN', 'warning'); return; }

    document.getElementById('cm-from').textContent = (UserState.accountNo || 'FNZ20260001');
    document.getElementById('cm-to').textContent = recipientAccNo;
    document.getElementById('cm-recipient').textContent = recipientName;
    document.getElementById('cm-amount').textContent = '₹' + formatINR(amount);

    document.getElementById('confirm-modal').classList.remove('hidden');
  }
  
  function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
  }

  async function processTransfer() {
    const amount = document.getElementById('transfer-amount').value;
    const pin = document.getElementById('transfer-pin').value;
    const desc = document.getElementById('transfer-desc').value;
    const btn = document.getElementById('final-transfer-btn');

    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined animate-spin">sync</span> Processing...';

    try {
      let csrfToken = '';
      try {
        const tokenRes = await fetch('/Finzo/api/auth/get-csrf-token.php');
        const tokenData = await tokenRes.json();
        csrfToken = tokenData.csrf_token || '';
      } catch (e) { }

      const params = new URLSearchParams();
      params.append('to_account', recipientAccNo);
      params.append('amount', amount);
      params.append('pin', pin);
      params.append('description', desc);
      params.append('csrf_token', csrfToken);

      const res = await fetch('/Finzo/api/user/transfer.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        credentials: 'same-origin',
        body: params.toString()
      });
      const data = await res.json();
      
      if (data.success) {
        window.location.href = `receipt.php?id=${data.txn_id}&success=1`;
      } else {
        showToast(data.message || 'Transfer failed', 'error');
        btn.disabled = false;
        btn.innerHTML = '<span class="material-symbols-outlined">done_all</span> Authorize & Send';
      }
    } catch (e) {
      showToast('Connection error', 'error');
      btn.disabled = false;
      btn.innerHTML = '<span class="material-symbols-outlined">done_all</span> Authorize & Send';
    }
  }
