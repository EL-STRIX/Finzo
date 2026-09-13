// accounts.js — User Portal "My Accounts" Page
// Powered by real-time original data from /api/v1/user/accounts and UserState.

buildSidebar('accounts');
buildNavbar('Banking', 'My Accounts');

(async function initAccounts() {
  try {
    await fetchUserSession();
    buildSidebar('accounts');
    buildNavbar('Banking', 'My Accounts');

    // 1. Fetch live account details from /api/v1/user/accounts
    let accountData = null;
    try {
      const res = await window.api.get('/user/accounts');
      if (res && res.success && res.account) {
        accountData = res.account;
      }
    } catch (e) {
      console.warn('Live account fetch error:', e);
    }

    // Fallback to UserState if API returns empty
    const account = accountData || {
      account_no: UserState.accountNo || '',
      account_type: UserState.accountType || 'savings',
      balance: parseFloat(UserState.balance || 0),
      interest_rate: UserState.interestRate || '4.25',
      daily_limit: parseFloat(UserState.dailyLimit || 10000),
      status: UserState.status || 'pending',
      opened_date: UserState.openedDate || ''
    };

    const isActive = (account.status === 'active' || UserState.status === 'active');

    // 2. Pending / Review Status Banner
    const pendingBanner = document.getElementById('acc-pending-banner');
    const pendingMsg    = document.getElementById('acc-pending-msg');
    const pendingCta    = document.getElementById('acc-pending-cta');
    const statusBadge   = document.getElementById('acc-status-badge');

    if (pendingBanner) {
      if (!isActive) {
        pendingBanner.classList.remove('hidden');
        if (UserState.status === 'under_review') {
          if (pendingMsg) pendingMsg.textContent = 'Your application is currently under review by our operations team.';
          if (pendingCta) { pendingCta.textContent = 'View Status'; pendingCta.href = 'account-pending.php'; }
        } else if (UserState.status === 'rejected') {
          if (pendingMsg) pendingMsg.textContent = 'Your application was rejected. Please review details and resubmit.';
          if (pendingCta) { pendingCta.textContent = 'Resubmit KYC'; pendingCta.href = 'account-rejected.php'; }
        } else {
          if (pendingMsg) pendingMsg.textContent = 'Your account is pending verification. Complete your profile to unlock full banking.';
          if (pendingCta) { pendingCta.textContent = 'Complete Profile'; pendingCta.href = 'complete-profile.php'; }
        }
      } else {
        pendingBanner.classList.add('hidden');
      }
    }

    // 3. Status Badge
    if (statusBadge) {
      if (isActive) {
        statusBadge.textContent = 'ACTIVE';
        statusBadge.className = 'badge badge-success text-xs tracking-widest uppercase';
      } else if (UserState.status === 'under_review') {
        statusBadge.textContent = 'UNDER REVIEW';
        statusBadge.className = 'badge badge-warning text-xs tracking-widest uppercase';
      } else if (UserState.status === 'rejected') {
        statusBadge.textContent = 'REJECTED';
        statusBadge.className = 'badge badge-danger text-xs tracking-widest uppercase';
      } else {
        statusBadge.textContent = 'PENDING';
        statusBadge.className = 'badge badge-warning text-xs tracking-widest uppercase';
      }
    }

    // 4. Real-Time Balance & Visibility Toggle
    const rawBalance = parseFloat(account.balance || 0);
    const formattedBalance = formatINR(rawBalance);
    const balEl = document.getElementById('acc-balance');
    const toggleBalBtn = document.getElementById('toggle-balance-btn');
    const eyeIcon = document.getElementById('balance-eye-icon');

    let isBalanceMasked = sessionStorage.getItem('finzo_dash_bal_masked') === 'true';

    function renderBalance() {
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

    renderBalance();

    if (toggleBalBtn && isActive) {
      toggleBalBtn.addEventListener('click', (e) => {
        e.preventDefault();
        isBalanceMasked = !isBalanceMasked;
        sessionStorage.setItem('finzo_dash_bal_masked', isBalanceMasked ? 'true' : 'false');
        renderBalance();
      });
    }

    // 5. Account Reference & Copy Button
    const accNoEl = document.getElementById('acc-no');
    const copyBtn = document.getElementById('copy-account-btn');
    const copyIcon = document.getElementById('copy-account-icon');

    const cleanAccNo = account.account_no || UserState.accountNo || '';
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
            window.FinzoUI.showToast('Account reference copied to clipboard', 'success');
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

    // 6. Account Type Title
    const accNameEl = document.getElementById('acc-type-name');
    const accTypeKey = (account.account_type || UserState.accountType || 'savings').toLowerCase();
    const accTypeMap = {
      'savings': 'Finzo Platinum Savings',
      'current': 'Finzo Commercial Current'
    };
    if (accNameEl) {
      accNameEl.textContent = accTypeMap[accTypeKey] || (accTypeKey.charAt(0).toUpperCase() + accTypeKey.slice(1) + ' Account');
    }

    // 7. Metadata (Opened Date, Interest Rate, Daily Limit)
    const openedRaw = account.opened_date || UserState.openedDate;
    const accOpenedEl = document.getElementById('acc-opened');
    if (accOpenedEl) {
      if (openedRaw) {
        const d = new Date(openedRaw);
        accOpenedEl.textContent = !isNaN(d.getTime())
          ? d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' })
          : '—';
      } else {
        accOpenedEl.textContent = isActive ? 'Active' : '—';
      }
    }

    const isCurrent = accTypeKey === 'current';
    const accInterestEl = document.getElementById('acc-interest');
    if (accInterestEl) {
      accInterestEl.textContent = isCurrent ? 'N/A (Current)' : (account.interest_rate || '4.25') + '% p.a.';
    }

    const accLimitEl = document.getElementById('acc-limit');
    if (accLimitEl) {
      const limitVal = parseFloat(account.daily_limit || 10000);
      accLimitEl.textContent = isCurrent ? 'Unlimited' : formatINR(limitVal);
    }

    // 8. Action buttons locking for non-active users
    const transferBtn = document.getElementById('btn-acc-transfer');
    if (transferBtn && !isActive) {
      transferBtn.classList.add('opacity-50', 'pointer-events-none');
    }

  } catch (err) {
    console.error('Accounts initialization error:', err);
  }
})();
