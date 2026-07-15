// Initial render with cached data
buildSidebar('dashboard');
buildNavbar('Account Overview', 'Dashboard');

(async function initDashboard() {
  await fetchUserSession();
  
  buildSidebar('dashboard');
  buildNavbar('Account Overview', 'Dashboard');

  document.getElementById('page-greeting').textContent = getGreeting() + ', ' + UserState.name;
  const status = UserState.status;

  const banners = {
    pending: document.getElementById('banner-pending'),
    review: document.getElementById('banner-review'),
    rejected: document.getElementById('banner-rejected'),
    approved: document.getElementById('banner-approved')
  };
  Object.values(banners).forEach(b => b && b.classList.add('hidden'));

  if (status === 'active') {
    if (banners.approved) {
      banners.approved.classList.remove('hidden');
      const msgEl = document.getElementById('approved-msg');
      if (msgEl) msgEl.textContent = '🎉 Your Finzo account ' + UserState.accountNo + ' has been activated!';
      setTimeout(() => banners.approved.classList.add('hidden'), 8000);
    }

    const balEl = document.getElementById('dash-balance');
    if (balEl) {
      const realBalance = formatINR(UserState.balance);
      if (UserState.isBalanceUnlocked) {
        balEl.textContent = realBalance;
      } else {
        balEl.textContent = '₹ ••••';
        balEl.setAttribute('data-real-value', realBalance);
      }
    }

    const accEl = document.getElementById('dash-account');
    if (accEl) accEl.textContent = UserState.accountNo;
    
    const lockEl = document.getElementById('lock-icon');
    if (lockEl) lockEl.style.display = 'none';

    const badgeEl = document.getElementById('account-badge');
    if (badgeEl) {
      badgeEl.innerHTML = `<div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-success-base/20 border border-success-base/30 backdrop-blur-sm">
                <div class="w-2 h-2 rounded-full bg-success-base"></div>
                <span class="text-xs font-bold text-success-base tracking-wider uppercase">${UserState.accountType} Account · Active</span>
              </div>`;
    }

    ['qa-transfer','qa-txns','qa-stmt'].forEach(id => {
      const el = document.getElementById(id);
      if (el) {
         el.classList.remove('opacity-50', 'pointer-events-none');
         el.classList.add('hover:shadow-md');
      }
    });
    ['qa-transfer-icon','qa-txns-icon','qa-stmt-icon'].forEach(id => {
      const el = document.getElementById(id);
      if (el) { 
        el.textContent = 'chevron_right'; 
        el.className = 'material-symbols-outlined text-[var(--color-text-muted)] group-hover:text-[var(--color-text-main)] transition-colors';
      }
    });

    try {
      const stRes = await fetch('/Finzo/api/user/dashboard.php', { credentials: 'same-origin' });
      const stData = await stRes.json();
      
      const txEmpty = document.getElementById('tx-empty');
      const txList = document.getElementById('tx-list');
      const viewAll = document.getElementById('view-all-txns');
      
      if (stData.success && stData.recent_transactions && stData.recent_transactions.length > 0) {
        if (txEmpty) txEmpty.classList.add('hidden');
        if (txList) {
          txList.innerHTML = stData.recent_transactions.map(tx => {
            const isCredit = tx.direction === 'credit';
            const icon = isCredit ? 'arrow_downward' : 'arrow_upward';
            const iconColorClass = isCredit ? 'text-success-base' : 'text-danger-base';
            const iconBgClass = isCredit ? 'bg-success-surface' : 'bg-danger-surface';
            const amountColorClass = isCredit ? 'text-success-base' : 'text-[var(--color-text-main)]';
            const amountPrefix = isCredit ? '+' : '-';
            const date = new Date(tx.created_at).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
            return `<div class="p-4 border-b border-[var(--color-border-main)] last:border-b-0 flex items-center justify-between hover:bg-[var(--color-surface-hover)] transition-colors">
              <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl ${iconBgClass} ${iconColorClass} flex items-center justify-center shrink-0">
                  <span class="material-symbols-outlined text-lg">${icon}</span>
                </div>
                <div>
                  <div class="font-bold text-[var(--color-text-main)] text-sm">${tx.description || tx.type}</div>
                  <div class="text-xs text-[var(--color-text-muted)]">${tx.txn_id || ''} • ${date}</div>
                </div>
              </div>
              <div class="text-right">
                <div class="font-bold ${amountColorClass} mb-1">${amountPrefix}${formatINR(tx.amount)}</div>
                <span class="ds-badge ds-badge-success !text-[10px] py-0.5">${tx.status || 'Completed'}</span>
              </div>
            </div>`;
          }).join('');
          txList.classList.remove('hidden');
        }
        if (viewAll) viewAll.classList.remove('hidden');
      } else {
        if (txEmpty) txEmpty.classList.remove('hidden');
        if (txList) txList.classList.add('hidden');
      }

      if (stData.success && stData.monthly_stats) {
        const ma = stData.monthly_stats;
        const totalEl = document.getElementById('activity-total');
        const incomeEl = document.getElementById('activity-income');
        const expenseEl = document.getElementById('activity-expenses');
        
        if (totalEl) {
           const net = (ma.income_total || 0) - (ma.expense_total || 0);
           totalEl.textContent = formatINR(net > 0 ? net : 0);
           
           const trendEl = document.getElementById('activity-trend');
           if (trendEl) {
             if (ma.income_total > ma.expense_total) {
               trendEl.textContent = 'Net Positive Cashflow';
               trendEl.className = 'text-sm font-semibold text-success-base';
             } else if (ma.expense_total > ma.income_total) {
               trendEl.textContent = 'Net Negative Cashflow';
               trendEl.className = 'text-sm font-semibold text-danger-base';
             } else {
               trendEl.textContent = 'No Net Activity';
               trendEl.className = 'text-sm font-semibold text-[var(--color-text-muted)]';
             }
           }
        }
        if (incomeEl) incomeEl.textContent = formatINR(ma.income_total || 0);
        if (expenseEl) expenseEl.textContent = formatINR(ma.expense_total || 0);
      }
    } catch (err) {
      console.warn('Failed to load dashboard data:', err);
    }

  } else {
    // INACTIVE STATE
    const banner = banners[status === 'under_review' ? 'review' : status];
    if (banner) banner.classList.remove('hidden');

    const balEl = document.getElementById('dash-balance');
    if (balEl) balEl.textContent = '₹ — — —';

    ['qa-transfer','qa-txns','qa-stmt'].forEach(id => {
      const el = document.getElementById(id);
      if (el) el.classList.add('opacity-50', 'pointer-events-none');
    });

    const totalEl = document.getElementById('activity-total');
    const trendEl = document.getElementById('activity-trend');
    const incomeEl = document.getElementById('activity-income');
    const expensesEl = document.getElementById('activity-expenses');
    if (totalEl) totalEl.textContent = '— — —';
    if (trendEl) trendEl.textContent = 'Data unavailable';
    if (incomeEl) incomeEl.textContent = '— — —';
    if (expensesEl) expensesEl.textContent = '— — —';

    const cta = document.getElementById('empty-cta');
    if (cta) {
      cta.textContent = status === 'under_review' ? 'View Status' : status === 'rejected' ? 'Resubmit Profile' : 'Complete Profile';
      if (status === 'under_review') cta.href = 'account-pending.html';
      if (status === 'rejected') cta.href = 'account-rejected.html';
    }
  }
})();