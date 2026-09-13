// account-detail.js — User Portal Account Details & Daily Limit Management
// Real-time original data from /api/v1/user/accounts and /api/v1/user/accounts/settings.

buildSidebar('accounts');
buildNavbar('Banking', 'Account Details');

let currentDailyLimit = 10000;
let currentUsedToday   = 0;

(async function initAccountDetail() {
  try {
    await fetchUserSession();
    buildSidebar('accounts');
    buildNavbar('Banking', 'Account Details');

    // 1. Fetch live account and limit data
    let accountData = null;
    try {
      const res = await window.api.get('/user/accounts');
      if (res && res.success && res.account) {
        accountData = res;
      }
    } catch (e) {
      console.warn('Account detail fetch error:', e);
    }

    const acc = (accountData && accountData.account) ? accountData.account : {
      account_no: UserState.accountNo || '',
      account_type: UserState.accountType || 'savings',
      balance: parseFloat(UserState.balance || 0),
      interest_rate: UserState.interestRate || '4.25',
      daily_limit: parseFloat(UserState.dailyLimit || 10000),
      status: UserState.status || 'pending',
      opened_date: UserState.openedDate || ''
    };

    const isActive = (acc.status === 'active' || UserState.status === 'active');

    // 2. Status Badge
    const statusBadge = document.getElementById('acc-detail-status-badge');
    if (statusBadge) {
      if (isActive) {
        statusBadge.textContent = 'ACTIVE';
        statusBadge.className = 'badge badge-success !bg-white/20 !border-white/30 text-white text-xs tracking-widest py-1';
      } else {
        statusBadge.textContent = (acc.status || UserState.status || 'PENDING').toUpperCase();
        statusBadge.className = 'badge badge-warning !bg-white/20 !border-white/30 text-white text-xs tracking-widest py-1';
      }
    }

    // 3. Balance with Mask/Unmask Toggle
    const rawBalance = parseFloat(acc.balance || 0);
    const formattedBalance = formatINR(rawBalance);
    const balEl = document.getElementById('acc-balance-big');
    const toggleBalBtn = document.getElementById('toggle-detail-balance-btn');
    const eyeIcon = document.getElementById('detail-balance-eye-icon');

    let isBalanceMasked = sessionStorage.getItem('finzo_dash_bal_masked') === 'true';

    function renderDetailBalance() {
      if (!balEl) return;
      if (!isActive) {
        balEl.textContent = '₹ — — —';
        if (toggleBalBtn) toggleBalBtn.classList.add('hidden');
        return;
      }
      if (toggleBalBtn) toggleBalBtn.classList.remove('hidden');
      if (isBalanceMasked) {
        balEl.textContent = '₹ ••••••';
        if (eyeIcon) eyeIcon.textContent = 'visibility_off';
        if (toggleBalBtn) toggleBalBtn.title = 'Show balance';
      } else {
        balEl.textContent = formattedBalance;
        if (eyeIcon) eyeIcon.textContent = 'visibility';
        if (toggleBalBtn) toggleBalBtn.title = 'Hide balance';
      }
    }

    renderDetailBalance();

    if (toggleBalBtn && isActive) {
      toggleBalBtn.addEventListener('click', (e) => {
        e.preventDefault();
        isBalanceMasked = !isBalanceMasked;
        sessionStorage.setItem('finzo_dash_bal_masked', isBalanceMasked ? 'true' : 'false');
        renderDetailBalance();
      });
    }

    // 4. Account Number & Copy Button
    const accNoEl = document.getElementById('acc-no-big');
    const copyBtn = document.getElementById('copy-detail-account-btn');
    const copyIcon = document.getElementById('copy-detail-account-icon');
    const cleanAccNo = acc.account_no || UserState.accountNo || '';

    if (accNoEl) {
      accNoEl.textContent = cleanAccNo 
        ? cleanAccNo.replace(/(.{4})/g, '$1 ').trim() 
        : (isActive ? 'FNZ — — —' : 'NOT YET ASSIGNED');
    }

    if (copyBtn && cleanAccNo) {
      copyBtn.classList.remove('hidden');
      copyBtn.addEventListener('click', async (e) => {
        e.preventDefault();
        try {
          await navigator.clipboard.writeText(cleanAccNo);
          if (copyIcon) copyIcon.textContent = 'done';
          if (window.FinzoUI && typeof window.FinzoUI.showToast === 'function') {
            window.FinzoUI.showToast('Account number copied to clipboard', 'success');
          }
          setTimeout(() => {
            if (copyIcon) copyIcon.textContent = 'content_copy';
          }, 2000);
        } catch (err) {
          const ta = document.createElement('textarea');
          ta.value = cleanAccNo;
          document.body.appendChild(ta);
          ta.select();
          document.execCommand('copy');
          ta.remove();
          if (copyIcon) copyIcon.textContent = 'done';
          setTimeout(() => {
            if (copyIcon) copyIcon.textContent = 'content_copy';
          }, 2000);
        }
      });
    } else if (copyBtn) {
      copyBtn.classList.add('hidden');
    }

    // 5. Account Type & Metadata
    const accType = acc.account_type || UserState.accountType || 'savings';
    const typeLabel = document.getElementById('acc-type-label');
    if (typeLabel) {
      typeLabel.textContent = accType.charAt(0).toUpperCase() + accType.slice(1) + ' Account';
    }

    const openedRaw = acc.opened_date || UserState.openedDate;
    const openedEl = document.getElementById('acc-opened');
    if (openedEl) {
      if (openedRaw) {
        const d = new Date(openedRaw);
        openedEl.textContent = !isNaN(d.getTime())
          ? d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' })
          : '—';
      } else {
        openedEl.textContent = isActive ? 'Active' : '—';
      }
    }

    const isCurrent = accType.toLowerCase() === 'current';
    const interestEl = document.getElementById('acc-interest');
    if (interestEl) {
      interestEl.textContent = isCurrent ? 'N/A' : (acc.interest_rate || '4.25') + '% p.a.';
    }

    // 6. Daily Limit & Used Today
    if (accountData) {
      currentDailyLimit = parseFloat(acc.daily_limit || 10000);
      currentUsedToday   = parseFloat(accountData.today_transfer_total || 0);
    }
    renderLimitUI();

    // 7. Mini Ledger
    const miniTxns = (accountData && accountData.mini_statement) ? accountData.mini_statement : null;
    await renderMiniLedger(miniTxns);

  } catch (err) {
    console.error('Account detail initialization error:', err);
  }
})();

function renderLimitUI() {
  const limitAmountEl    = document.getElementById('limit-amount');
  const usedAmountEl     = document.getElementById('used-amount');
  const limitRemainingEl = document.getElementById('limit-remaining');
  const limitBar         = document.getElementById('limit-bar');
  const newLimitInput    = document.getElementById('new-limit');

  const remaining = Math.max(0, currentDailyLimit - currentUsedToday);
  const pct = currentDailyLimit > 0 ? Math.min(100, (currentUsedToday / currentDailyLimit) * 100) : 0;

  if (limitAmountEl)    limitAmountEl.textContent    = formatINR(currentDailyLimit);
  if (usedAmountEl)     usedAmountEl.textContent     = formatINR(currentUsedToday);
  if (limitRemainingEl) limitRemainingEl.textContent = formatINR(remaining) + ' Remaining';

  if (limitBar) {
    limitBar.style.width = pct + '%';
    if (pct >= 90) {
      limitBar.className = 'h-full bg-rose-500 transition-all duration-700 ease-out';
    } else if (pct >= 75) {
      limitBar.className = 'h-full bg-amber-500 transition-all duration-700 ease-out';
    } else {
      limitBar.className = 'h-full bg-emerald-500 transition-all duration-700 ease-out';
    }
  }

  if (newLimitInput && !newLimitInput.value) {
    newLimitInput.value = currentDailyLimit;
  }
}

async function renderMiniLedger(preloadedTxns) {
  const container = document.getElementById('mini-txns');
  if (!container) return;

  let txns = preloadedTxns;

  if (!txns) {
    try {
      const res = await window.api.get('/user/transactions', { per_page: 5 });
      if (res && res.success && res.transactions) {
        txns = res.transactions;
      }
    } catch (e) {
      console.warn('Failed to fetch mini ledger:', e);
    }
  }

  if (txns && txns.length > 0) {
    container.innerHTML = txns.slice(0, 5).map(tx => {
      const isCredit       = tx.direction === 'credit' || inCreditTypes(tx.type);
      const icon           = isCredit ? 'arrow_downward' : 'arrow_upward';
      const iconColorClass = isCredit ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400';
      const iconBgClass    = isCredit ? 'bg-emerald-500/10' : 'bg-rose-500/10';
      const amountColor    = isCredit ? 'text-emerald-600 dark:text-emerald-400' : 'text-text-primary';
      const prefix         = isCredit ? '+' : '-';
      const dateStr        = tx.created_at ? new Date(tx.created_at).toLocaleDateString('en-IN', {
        day: '2-digit', month: 'short', year: 'numeric'
      }) : '';

      let description = tx.description || tx.type || 'Transaction';
      description = description.charAt(0).toUpperCase() + description.slice(1);

      return `
        <div class="p-4 flex items-center justify-between hover:bg-surface-subtle/50 transition-colors">
          <div class="flex items-center gap-4 min-w-0">
            <div class="w-10 h-10 rounded-xl ${iconBgClass} ${iconColorClass} flex items-center justify-center shrink-0">
              <span class="material-symbols-outlined text-lg">${icon}</span>
            </div>
            <div class="min-w-0">
              <div class="text-sm font-bold text-text-primary truncate">${description}</div>
              <div class="text-xs text-text-muted font-mono">${tx.txn_id || tx.reference_id || ''}</div>
            </div>
          </div>
          <div class="text-right shrink-0 pl-3">
            <div class="text-sm font-bold font-mono ${amountColor} mb-1">${prefix}${formatINR(tx.amount)}</div>
            <div class="text-[10px] font-semibold text-text-muted">${dateStr}</div>
          </div>
        </div>`;
    }).join('');
  } else {
    container.innerHTML = `<div class="p-8 text-center text-sm font-semibold text-text-muted">No recent transactions recorded</div>`;
  }
}

function inCreditTypes(type) {
  return type === 'deposit' || type === 'interest_credit';
}

// Globally accessible for the button onclick
window.updateLimit = async function() {
  const inputEl = document.getElementById('new-limit');
  const btn = document.getElementById('btn-update-limit');
  const val = parseInt(inputEl.value, 10);

  if (isNaN(val) || val < 1000 || val > 100000) {
    if (window.FinzoUI && typeof window.FinzoUI.showToast === 'function') {
      window.FinzoUI.showToast('Daily limit must be between ₹1,000 and ₹1,00,000', 'error');
    } else {
      alert('Daily limit must be between ₹1,000 and ₹1,00,000');
    }
    return;
  }

  const originalBtnText = btn ? btn.innerHTML : '';
  if (btn) {
    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined animate-spin text-sm mr-1">progress_activity</span> Updating...';
  }

  try {
    const res = await window.api.post('/user/accounts/settings', { daily_limit: val });
    if (res && res.success) {
      currentDailyLimit = val;
      renderLimitUI();
      if (window.FinzoUI && typeof window.FinzoUI.showToast === 'function') {
        window.FinzoUI.showToast('Daily transfer limit updated successfully to ' + formatINR(val), 'success');
      }
    } else {
      const errMsg = (res && res.message) ? res.message : 'Failed to update daily limit';
      if (window.FinzoUI && typeof window.FinzoUI.showToast === 'function') {
        window.FinzoUI.showToast(errMsg, 'error');
      }
    }
  } catch (err) {
    console.error('Update limit error:', err);
    if (window.FinzoUI && typeof window.FinzoUI.showToast === 'function') {
      window.FinzoUI.showToast('Network error while updating limit', 'error');
    }
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.innerHTML = originalBtnText;
    }
  }
};
