(async function() {
    await fetchUserSession();
    buildSidebar('accounts'); buildNavbar('Banking','Account Details');
    handleStatusRedirect('accounts');

    const realBalance = formatINR(UserState.balance);
    const balEl = document.getElementById('acc-balance-big');
    
    if (UserState.isBalanceUnlocked) {
      balEl.textContent = realBalance;
    } else {
      balEl.textContent = '₹ ••••';
      balEl.setAttribute('data-real-value', realBalance);
    }
    document.getElementById('acc-no-big').textContent = (UserState.accountNo || 'NOT ASSIGNED').replace(/(.{4})/g, '$1 ').trim();
    const accType = UserState.accountType || 'savings';
    document.getElementById('acc-type-label').textContent = accType.charAt(0).toUpperCase()+accType.slice(1)+' Profile';

    if (UserState.openedDate) {
      document.getElementById('acc-opened').textContent = new Date(UserState.openedDate).toLocaleDateString('en-IN', {month:'short', year:'numeric', day:'numeric'}).toUpperCase();
    }
    document.getElementById('acc-interest').textContent = UserState.interestRate + '% p.a.';
  })();

  let dailyLimit = 10000, usedToday = 0;
  (async function loadLimitSettings() {
    try {
      const res = await fetch('/Finzo/api/user/account-settings.php', { credentials: 'same-origin' });
      const data = await res.json();
      if (data.success) {
        dailyLimit = parseInt(data.daily_limit || 10000);
        usedToday = parseInt(data.used_today || 0);
      }
    } catch(e) {}
    const remaining = dailyLimit - usedToday;
    document.getElementById('limit-amount').textContent = '₹'+dailyLimit.toLocaleString('en-IN');
    document.getElementById('used-amount').textContent = '₹'+usedToday.toLocaleString('en-IN');
    document.getElementById('limit-remaining').textContent = '₹'+Math.max(0, remaining).toLocaleString('en-IN')+' Available';
    
    const pct = Math.min(100, (usedToday/dailyLimit)*100);
    const bar = document.getElementById('limit-bar');
    bar.style.width = pct + '%';
    
    if (pct >= 90) { bar.className = 'h-full bg-danger-base transition-all duration-1000 ease-out'; }
    else if (pct >= 75) { bar.className = 'h-full bg-warning-base transition-all duration-1000 ease-out'; }
    else { bar.className = 'h-full bg-gradient-to-r from-primary-600 to-info-base transition-all duration-1000 ease-out'; }

    document.getElementById('new-limit').value = dailyLimit;
  })();

  (async function loadMiniLedger() {
    try {
      const res = await fetch('/Finzo/api/user/transactions.php?limit=5', { credentials: 'same-origin' });
      const data = await res.json();
      if (data.success && data.transactions && data.transactions.length > 0) {
        document.getElementById('mini-txns').innerHTML = data.transactions.map(tx => {
          const isCredit = tx.direction === 'credit' || tx.type === 'deposit' || tx.type === 'interest_credit';
          const icon = isCredit ? 'arrow_downward' : 'arrow_upward';
          const iconColor = isCredit ? 'text-success-base' : 'text-danger-base';
          const iconBg = isCredit ? 'bg-success-surface' : 'bg-danger-surface';
          const amtColor = isCredit ? 'text-success-base' : 'text-[var(--color-text-main)]';
          const amtPrefix = isCredit ? '+' : '-';
          const date = new Date(tx.created_at).toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'}).toUpperCase();
          
          return `<div class="p-4 border-b border-[var(--color-border-main)] last:border-0 hover:bg-[var(--color-surface-hover)] transition-colors flex justify-between items-center">
            <div class="flex items-center gap-4">
              <div class="w-10 h-10 rounded-xl ${iconBg} flex items-center justify-center shrink-0"><span class="material-symbols-outlined ${iconColor} text-lg">${icon}</span></div>
              <div>
                <div class="text-sm font-bold text-[var(--color-text-main)]">${tx.description || tx.type}</div>
                <div class="text-[10px] font-bold text-[var(--color-text-muted)] tracking-wider">${tx.reference_id || ''}</div>
              </div>
            </div>
            <div class="text-right">
              <div class="text-sm font-black ${amtColor} mb-1">${amtPrefix}${formatINR(tx.amount)}</div>
              <div class="text-[10px] font-bold text-[var(--color-text-muted)] tracking-wider">${date}</div>
            </div>
          </div>`;
        }).join('');
      } else {
        document.getElementById('mini-txns').innerHTML = `<div class="p-8 text-center text-sm font-bold text-[var(--color-text-muted)]">No recent transactions found</div>`;
      }
    } catch(e) {}
  })();

  async function updateLimit(){
    const val = parseInt(document.getElementById('new-limit').value);
    if (!val || val < 1000 || val > 100000){ showToast('Exposure limit must be between ₹1,000 and ₹1,00,000','error'); return; }
    
    try {
      const res = await fetch('/Finzo/api/user/account-settings.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        credentials: 'same-origin',
        body: 'daily_limit=' + val
      });
      const data = await res.json();
      if (data.success) {
        dailyLimit = val;
        document.getElementById('limit-amount').textContent = '₹'+val.toLocaleString('en-IN');
        document.getElementById('limit-remaining').textContent = '₹'+Math.max(0, val-usedToday).toLocaleString('en-IN')+' Available';
        
        const pct = Math.min(100, (usedToday/val)*100);
        const bar = document.getElementById('limit-bar');
        bar.style.width = pct + '%';
        if (pct >= 90) { bar.className = 'h-full bg-danger-base transition-all duration-1000 ease-out'; }
        else if (pct >= 75) { bar.className = 'h-full bg-warning-base transition-all duration-1000 ease-out'; }
        else { bar.className = 'h-full bg-gradient-to-r from-primary-600 to-info-base transition-all duration-1000 ease-out'; }

        showToast('Exposure threshold updated','success');
      } else {
        showToast(data.message || 'Update failed', 'error');
      }
    } catch(e) { showToast('Connection error', 'error'); }
  }