// dashboard.js — User Portal Dashboard
// Powered by real-time data populated by fetchUserSession() in user-shared.js.
// Raw API response is available via window.__FINZO_DASH__.

buildSidebar('dashboard');
buildNavbar('Account Overview', 'Dashboard');

(async function initDashboard() {
  // 1. Fetch user session and dashboard data from /api/v1/user/dashboard
  await fetchUserSession();

  // Refresh sidebar/navbar with loaded profile details
  buildSidebar('dashboard');
  buildNavbar('Account Overview', 'Dashboard');

  // Greeting
  const greetingEl = document.getElementById('page-greeting');
  if (greetingEl) {
    greetingEl.textContent = getGreeting() + ', ' + (UserState.name || 'User');
  }

  const status = UserState.status;

  // Status banners
  const banners = {
    pending:  document.getElementById('banner-pending'),
    review:   document.getElementById('banner-review'),
    rejected: document.getElementById('banner-rejected'),
    approved: document.getElementById('banner-approved')
  };
  Object.values(banners).forEach(b => b && b.classList.add('hidden'));

  // Elements
  const balEl        = document.getElementById('dash-balance');
  const toggleBalBtn = document.getElementById('toggle-balance-btn');
  const eyeIcon      = document.getElementById('balance-eye-icon');
  const accEl        = document.getElementById('dash-account');
  const copyBtn      = document.getElementById('copy-account-btn');
  const copyIcon     = document.getElementById('copy-account-icon');
  const lockEl       = document.getElementById('lock-icon');
  const badgeEl      = document.getElementById('account-badge');

  if (status === 'active') {
    // ── ACTIVE ACCOUNT STATE ────────────────────────────────────

    // 1. Activation Welcome Banner (dismissable)
    const dismissKey = 'finzo_activated_dismissed_' + (UserState.accountNo || UserState.customerId);
    if (banners.approved && !localStorage.getItem(dismissKey)) {
      banners.approved.classList.remove('hidden');
      const msgEl = document.getElementById('approved-msg');
      if (msgEl) {
        msgEl.textContent = '🎉 Your Finzo account ' + (UserState.accountNo ? UserState.accountNo + ' ' : '') + 'is active!';
      }
      const closeBtn = banners.approved.querySelector('button');
      if (closeBtn) {
        closeBtn.addEventListener('click', () => {
          localStorage.setItem(dismissKey, '1');
          banners.approved.classList.add('hidden');
        });
      }
    }

    // 2. Real-Time Balance & Visibility Toggle
    const rawBalance = parseFloat(UserState.balance || 0);
    const formattedBalance = formatINR(rawBalance);
    let isBalanceMasked = sessionStorage.getItem('finzo_dash_bal_masked') === 'true';

    function renderBalance() {
      if (!balEl) return;
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

    renderBalance();

    if (toggleBalBtn) {
      toggleBalBtn.classList.remove('hidden');
      toggleBalBtn.addEventListener('click', (e) => {
        e.preventDefault();
        isBalanceMasked = !isBalanceMasked;
        sessionStorage.setItem('finzo_dash_bal_masked', isBalanceMasked ? 'true' : 'false');
        renderBalance();
      });
    }

    // 3. Account Number & Copy Helper
    const accountNo = UserState.accountNo || 'Active';
    if (accEl) {
      accEl.textContent = accountNo;
    }
    if (lockEl) {
      lockEl.classList.add('hidden');
    }

    if (copyBtn && UserState.accountNo) {
      copyBtn.classList.remove('hidden');
      copyBtn.addEventListener('click', async (e) => {
        e.preventDefault();
        try {
          await navigator.clipboard.writeText(UserState.accountNo);
          if (copyIcon) copyIcon.textContent = 'done';
          if (window.FinzoUI && typeof window.FinzoUI.showToast === 'function') {
            window.FinzoUI.showToast('Account number copied to clipboard', 'success');
          }
          setTimeout(() => {
            if (copyIcon) copyIcon.textContent = 'content_copy';
          }, 2000);
        } catch (err) {
          const ta = document.createElement('textarea');
          ta.value = UserState.accountNo;
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
    }

    // 4. Account Badge
    if (badgeEl) {
      const typeLabel = (UserState.accountType || 'Savings').charAt(0).toUpperCase() + (UserState.accountType || 'Savings').slice(1);
      badgeEl.innerHTML = `
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/20 border border-emerald-500/40 backdrop-blur-sm">
          <div class="w-2 h-2 rounded-full bg-emerald-400"></div>
          <span class="text-xs font-bold text-emerald-300 tracking-wider uppercase">${typeLabel} Account · Active</span>
        </div>`;
    }

    // 5. Unlock Quick Actions
    ['qa-transfer', 'qa-txns', 'qa-stmt'].forEach(id => {
      const el = document.getElementById(id);
      if (el) {
        el.classList.remove('opacity-50', 'pointer-events-none');
        el.classList.add('hover:shadow-md');
      }
    });
    ['qa-transfer-icon', 'qa-txns-icon', 'qa-stmt-icon'].forEach(id => {
      const el = document.getElementById(id);
      if (el) {
        el.textContent = 'chevron_right';
        el.className = 'material-symbols-outlined text-text-muted group-hover:text-text-primary transition-colors';
      }
    });

    // 6. Data Source from session/API
    const dashData = window.__FINZO_DASH__ || {};

    // 7. Recent Transactions
    const txEmpty = document.getElementById('tx-empty');
    const txList  = document.getElementById('tx-list');
    const viewAll = document.getElementById('view-all-txns');

    const transactions = dashData.recent_transactions || [];

    if (transactions.length > 0) {
      if (txEmpty) txEmpty.classList.add('hidden');
      if (viewAll) viewAll.classList.remove('hidden');

      if (txList) {
        txList.innerHTML = transactions.map(tx => {
          const isCredit       = tx.direction === 'credit';
          const icon           = isCredit ? 'arrow_downward' : 'arrow_upward';
          const iconColorClass = isCredit ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400';
          const iconBgClass    = isCredit ? 'bg-emerald-500/10' : 'bg-rose-500/10';
          const amountColor    = isCredit ? 'text-emerald-600 dark:text-emerald-400' : 'text-text-primary';
          const prefix         = isCredit ? '+' : '-';
          const date           = new Date(tx.created_at).toLocaleDateString('en-IN', {
            day: 'numeric', month: 'short', year: 'numeric'
          });

          // Labeling logic: identify counterparty if available
          let title = tx.description || '';
          if (tx.counterparty_name) {
            title = (isCredit ? 'Received from ' : 'Sent to ') + tx.counterparty_name;
          } else if (!title) {
            title = tx.type ? tx.type.replace(/_/g, ' ') : 'Transaction';
          }
          title = title.charAt(0).toUpperCase() + title.slice(1);

          const statusText = (tx.status || 'completed').charAt(0).toUpperCase() + (tx.status || 'completed').slice(1);
          const badgeClass = tx.status === 'failed' ? 'bg-rose-500/10 text-rose-600 dark:text-rose-400' :
                             (tx.status === 'pending' ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400' :
                             'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400');

          return `
            <div class="p-4 border-b border-border-subtle last:border-b-0 flex items-center justify-between hover:bg-surface-subtle/50 transition-colors">
              <div class="flex items-center gap-4 min-w-0">
                <div class="w-10 h-10 rounded-xl ${iconBgClass} ${iconColorClass} flex items-center justify-center shrink-0">
                  <span class="material-symbols-outlined text-lg">${icon}</span>
                </div>
                <div class="min-w-0">
                  <div class="font-bold text-text-primary text-sm truncate">${title}</div>
                  <div class="text-xs text-text-muted font-mono">${tx.txn_id || ''} • ${date}</div>
                </div>
              </div>
              <div class="text-right shrink-0 pl-3">
                <div class="font-bold ${amountColor} mb-1 font-mono">${prefix}${formatINR(tx.amount)}</div>
                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider ${badgeClass}">${statusText}</span>
              </div>
            </div>`;
        }).join('');
        txList.classList.remove('hidden');
      }

      const cta = document.getElementById('empty-cta');
      if (cta) {
        cta.textContent = 'View Transactions';
        cta.href = 'transactions.php';
      }

    } else {
      if (txEmpty) txEmpty.classList.remove('hidden');
      if (txList)  txList.classList.add('hidden');
      if (viewAll) viewAll.classList.add('hidden');

      const cta = document.getElementById('empty-cta');
      if (cta) {
        cta.textContent = 'Send Money';
        cta.href = 'transfer.php';
      }
    }

    // 8. Monthly Activity Stats (Real-Time Original Data)
    const stats = dashData.monthly_stats || {};
    const income  = parseFloat(stats.income_total  || 0);
    const expense = parseFloat(stats.expense_total || 0);
    const netFlow = income - expense;

    const totalEl   = document.getElementById('activity-total');
    const trendEl   = document.getElementById('activity-trend');
    const incomeEl  = document.getElementById('activity-income');
    const expenseEl = document.getElementById('activity-expenses');

    if (totalEl) {
      const sign = netFlow > 0 ? '+' : (netFlow < 0 ? '-' : '');
      totalEl.textContent = sign + formatINR(Math.abs(netFlow));
      totalEl.className = 'font-display text-3xl font-extrabold mb-1 ' + 
        (netFlow > 0 ? 'text-emerald-600 dark:text-emerald-400' : (netFlow < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-text-primary'));
    }

    if (incomeEl)  incomeEl.textContent  = formatINR(income);
    if (expenseEl) expenseEl.textContent = formatINR(expense);

    if (trendEl) {
      if (netFlow > 0) {
        trendEl.textContent = '↑ Positive Monthly Cashflow';
        trendEl.className   = 'text-sm font-semibold text-emerald-600 dark:text-emerald-400';
      } else if (netFlow < 0) {
        trendEl.textContent = '↓ Negative Monthly Cashflow';
        trendEl.className   = 'text-sm font-semibold text-rose-600 dark:text-rose-400';
      } else {
        trendEl.textContent = (income === 0 && expense === 0) ? 'No activity recorded this month' : 'Balanced Cashflow';
        trendEl.className   = 'text-sm font-semibold text-text-muted';
      }
    }

  } else {
    // ── INACTIVE / PENDING / REJECTED STATES ────────────────────
    const bannerKey = status === 'under_review' ? 'review' : status;
    const banner = banners[bannerKey];
    if (banner) banner.classList.remove('hidden');

    if (balEl) balEl.textContent = '₹ — — —';
    if (toggleBalBtn) toggleBalBtn.classList.add('hidden');
    if (copyBtn) copyBtn.classList.add('hidden');

    // Lock quick action buttons
    ['qa-transfer', 'qa-txns', 'qa-stmt'].forEach(id => {
      const el = document.getElementById(id);
      if (el) el.classList.add('opacity-50', 'pointer-events-none');
    });

    // Zero out activity card
    const totalEl   = document.getElementById('activity-total');
    const trendEl   = document.getElementById('activity-trend');
    const incomeEl  = document.getElementById('activity-income');
    const expenseEl = document.getElementById('activity-expenses');
    if (totalEl)   totalEl.textContent   = '— — —';
    if (trendEl)   trendEl.textContent   = 'Account awaiting activation';
    if (incomeEl)  incomeEl.textContent  = '— — —';
    if (expenseEl) expenseEl.textContent = '— — —';

    // Contextual CTA
    const cta = document.getElementById('empty-cta');
    if (cta) {
      if (status === 'under_review') {
        cta.textContent = 'Check Application Status';
        cta.href = 'account-pending.php';
      } else if (status === 'rejected') {
        cta.textContent = 'Resubmit KYC Application';
        cta.href = 'account-rejected.php';
      } else {
        cta.textContent = 'Complete Profile Verification';
        cta.href = 'complete-profile.php';
      }
    }
  }
})();
