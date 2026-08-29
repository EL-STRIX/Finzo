// Initial render with cached data
if (typeof buildSidebar === 'function') buildSidebar('dashboard');
if (typeof buildNavbar === 'function') buildNavbar('Account Overview', 'Dashboard');

(async function initDashboard() {
  try {
    if (typeof fetchUserSession === 'function') {
      await fetchUserSession();
    }
  } catch (err) {
    console.warn('fetchUserSession error in dashboard.js:', err);
  }

  if (typeof buildSidebar === 'function') buildSidebar('dashboard');
  if (typeof buildNavbar === 'function') buildNavbar('Account Overview', 'Dashboard');

  const greetingEl = document.getElementById('page-greeting');
  if (greetingEl) {
    const greetingText = typeof getGreeting === 'function' ? getGreeting() : 'Welcome';
    greetingEl.textContent = greetingText + ', ' + (UserState.name || 'User');
  }

  const status = UserState.status || 'pending';

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
      if (msgEl) msgEl.textContent = '🎉 Your Finzo account ' + (UserState.accountNo || '') + ' has been activated!';
      setTimeout(() => banners.approved.classList.add('hidden'), 8000);
    }

    const balEl = document.getElementById('dash-balance');
    if (balEl) {
      const realBalance = typeof formatINR === 'function' ? formatINR(UserState.balance || 0) : '₹' + (UserState.balance || '0.00');
      balEl.textContent = realBalance;
    }

    const accEl = document.getElementById('dash-account');
    if (accEl) accEl.textContent = UserState.accountNo || '—';

    const lockEl = document.getElementById('lock-icon');
    if (lockEl) lockEl.style.display = 'none';

    const badgeEl = document.getElementById('account-badge');
    if (badgeEl) {
      const accType = UserState.accountType ? (UserState.accountType.charAt(0).toUpperCase() + UserState.accountType.slice(1)) : 'Savings';
      badgeEl.innerHTML = `<div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/20 border border-emerald-500/30 backdrop-blur-sm">
                <div class="w-2 h-2 rounded-full bg-emerald-400"></div>
                <span class="text-xs font-bold text-emerald-200 tracking-wider uppercase">${accType} Account · Active</span>
              </div>`;
    }

    ['qa-transfer','qa-txns','qa-stmt'].forEach(id => {
      const el = document.getElementById(id);
      if (el) {
        el.classList.remove('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
        el.classList.add('hover:shadow-md');
        el.removeAttribute('title');
        el.onclick = null;
      }
    });
    ['qa-transfer-icon','qa-txns-icon','qa-stmt-icon'].forEach(id => {
      const el = document.getElementById(id);
      if (el) {
        el.textContent = 'chevron_right';
        el.className = 'material-symbols-outlined text-text-muted group-hover:text-text-main transition-colors';
      }
    });

    try {
      const stData = await window.api.get('/user/dashboard');

      const txEmpty = document.getElementById('tx-empty');
      const txList = document.getElementById('tx-list');
      const viewAll = document.getElementById('view-all-txns');

      if (stData && stData.success && stData.recent_transactions && stData.recent_transactions.length > 0) {
        if (txEmpty) txEmpty.classList.add('hidden');
        if (txList) {
          txList.innerHTML = stData.recent_transactions.map(tx => {
            const isCredit = tx.direction === 'credit';
            const icon = isCredit ? 'arrow_downward' : 'arrow_upward';
            const iconColorClass = isCredit ? 'text-emerald-500' : 'text-rose-500';
            const iconBgClass = isCredit ? 'bg-emerald-500/10' : 'bg-rose-500/10';
            const amountColorClass = isCredit ? 'text-emerald-500 font-bold' : 'text-text-main font-bold';
            const amountPrefix = isCredit ? '+' : '-';
            const date = new Date(tx.created_at).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
            return `<div class="p-4 border-b border-border-main last:border-b-0 flex items-center justify-between hover:bg-surface-subtle transition-colors">
              <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl ${iconBgClass} ${iconColorClass} flex items-center justify-center shrink-0">
                  <span class="material-symbols-outlined text-lg">${icon}</span>
                </div>
                <div>
                  <div class="font-bold text-text-main text-sm">${tx.description || tx.type}</div>
                  <div class="text-xs text-text-muted">${tx.txn_id || ''} • ${date}</div>
                </div>
              </div>
              <div class="text-right">
                <div class="font-bold ${amountColorClass} mb-1">${amountPrefix}${typeof formatINR === 'function' ? formatINR(tx.amount) : '₹' + tx.amount}</div>
                <span class="badge badge-success text-[10px] py-0.5">${tx.status || 'Completed'}</span>
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

      if (stData && stData.success && stData.monthly_stats) {
        const ma = stData.monthly_stats;
        const totalEl = document.getElementById('activity-total');
        const incomeEl = document.getElementById('activity-income');
        const expenseEl = document.getElementById('activity-expenses');

        if (totalEl) {
          const net = (ma.income_total || 0) - (ma.expense_total || 0);
          totalEl.textContent = typeof formatINR === 'function' ? formatINR(net > 0 ? net : 0) : '₹' + (net > 0 ? net : 0);

          const trendEl = document.getElementById('activity-trend');
          if (trendEl) {
            if (ma.income_total > ma.expense_total) {
              trendEl.textContent = '↑ Net Positive Cashflow';
              trendEl.className = 'text-sm font-semibold text-emerald-600 dark:text-emerald-400';
            } else if (ma.expense_total > ma.income_total) {
              trendEl.textContent = '↓ Net Negative Cashflow';
              trendEl.className = 'text-sm font-semibold text-rose-500';
            } else {
              trendEl.textContent = 'No Net Activity';
              trendEl.className = 'text-sm font-semibold text-text-muted';
            }
          }
        }
        if (incomeEl) incomeEl.textContent = typeof formatINR === 'function' ? formatINR(ma.income_total || 0) : '₹' + (ma.income_total || 0);
        if (expenseEl) expenseEl.textContent = typeof formatINR === 'function' ? formatINR(ma.expense_total || 0) : '₹' + (ma.expense_total || 0);
      }
    } catch (err) {
      console.warn('Failed to load dashboard data:', err);
    }

  } else {
    // INACTIVE STATE (pending, under_review, rejected)
    const activeBannerKey = status === 'under_review' ? 'review' : status;
    const banner = banners[activeBannerKey];
    if (banner) banner.classList.remove('hidden');

    if (status === 'rejected' && UserState.rejectionReason) {
      const rejDesc = document.getElementById('rejected-banner-desc');
      if (rejDesc) rejDesc.textContent = 'Reason: ' + UserState.rejectionReason;
    }

    const balEl = document.getElementById('dash-balance');
    if (balEl) balEl.textContent = '₹ — — —';

    const accEl = document.getElementById('dash-account');
    if (accEl) {
      if (status === 'under_review') accEl.textContent = 'VERIFICATION PENDING';
      else if (status === 'rejected') accEl.textContent = 'ACTION REQUIRED';
      else accEl.textContent = 'NOT YET ALLOCATED';
    }

    const badgeEl = document.getElementById('account-badge');
    if (badgeEl) {
      if (status === 'under_review') {
        badgeEl.innerHTML = `<div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-indigo-500/20 border border-indigo-500/30 backdrop-blur-sm">
                  <div class="w-2 h-2 rounded-full bg-indigo-400 animate-pulse"></div>
                  <span class="text-xs font-bold text-indigo-200 tracking-wider uppercase">Under Admin Review</span>
                </div>`;
      } else if (status === 'rejected') {
        badgeEl.innerHTML = `<div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-rose-500/20 border border-rose-500/30 backdrop-blur-sm">
                  <div class="w-2 h-2 rounded-full bg-rose-400"></div>
                  <span class="text-xs font-bold text-rose-200 tracking-wider uppercase">Action Required</span>
                </div>`;
      } else {
        badgeEl.innerHTML = `<div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-500/20 border border-amber-500/30 backdrop-blur-sm">
                  <div class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></div>
                  <span class="text-xs font-bold text-amber-200 tracking-wider uppercase">Pending KYC</span>
                </div>`;
      }
    }

    ['qa-transfer','qa-txns','qa-stmt'].forEach(id => {
      const el = document.getElementById(id);
      if (el) {
        el.classList.add('opacity-50', 'cursor-not-allowed');
        el.setAttribute('title', 'Account activation required');
        el.onclick = function(e) {
          e.preventDefault();
          if (typeof showToast === 'function') {
            showToast('Account activation required to access this feature', 'warning');
          }
        };
      }
    });

    const totalEl = document.getElementById('activity-total');
    const trendEl = document.getElementById('activity-trend');
    const incomeEl = document.getElementById('activity-income');
    const expensesEl = document.getElementById('activity-expenses');
    if (totalEl) totalEl.textContent = '— — —';
    if (trendEl) trendEl.textContent = 'Awaiting Activation';
    if (incomeEl) incomeEl.textContent = '— — —';
    if (expensesEl) expensesEl.textContent = '— — —';

    const emptyTitle = document.getElementById('tx-empty-title');
    const emptyDesc = document.getElementById('tx-empty-desc');
    const cta = document.getElementById('empty-cta');

    if (status === 'under_review') {
      if (emptyTitle) emptyTitle.textContent = 'KYC Under Admin Review';
      if (emptyDesc) emptyDesc.textContent = 'Your identity verification documents are under review by our admin team. Banking features will unlock upon approval.';
      if (cta) {
        cta.textContent = 'View KYC Status';
        cta.href = 'account-pending.php';
      }
    } else if (status === 'rejected') {
      if (emptyTitle) emptyTitle.textContent = 'KYC Verification Returned';
      if (emptyDesc) emptyDesc.textContent = 'Your application requires updates before your account can be activated. Please review feedback and resubmit.';
      if (cta) {
        cta.textContent = 'Resubmit Profile';
        cta.href = 'account-rejected.php';
      }
    } else {
      if (emptyTitle) emptyTitle.textContent = 'Account Activation Required';
      if (emptyDesc) emptyDesc.textContent = 'Complete your identity and address verification to unlock deposits, transfers, and your banking account.';
      if (cta) {
        cta.textContent = 'Complete Profile';
        cta.href = 'complete-profile.php';
      }
    }
  }
})();
