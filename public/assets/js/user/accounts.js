(async function() {
    try {
      await fetchUserSession();
      buildSidebar('accounts'); buildNavbar('Banking','My Accounts');
      handleStatusRedirect('accounts');
      const realBalance = formatINR(UserState.balance);
      const balEls = [document.getElementById('acc-balance')];
      
      balEls.forEach(el => {
        if (!el) return;
        if (UserState.isBalanceUnlocked) {
          el.textContent = realBalance;
        } else {
          el.textContent = '₹ ••••';
          el.setAttribute('data-real-value', realBalance);
        }
      });

      document.getElementById('acc-no').textContent = (UserState.accountNo || 'NOT ASSIGNED').replace(/(.{4})/g, '$1 ').trim();

      const openedDate = new Date(UserState.openedDate);
      const dateStr = !isNaN(openedDate) ? openedDate.toLocaleDateString('en-GB', {day:'2-digit',month:'short',year:'numeric'}).toUpperCase() : '—';
      const accOpenedEl = document.getElementById('acc-opened');
      if (accOpenedEl) accOpenedEl.textContent = dateStr;
      
      const accInterestEl = document.getElementById('acc-interest');
      if (accInterestEl) accInterestEl.textContent = UserState.interestRate + '% p.a.';
      
      const accLimitEl = document.getElementById('acc-limit');
      if (accLimitEl) accLimitEl.textContent = '₹' + parseInt(UserState.dailyLimit).toLocaleString('en-IN');
      
      const accTypeMap = { 'savings': 'Finzo Platinum Savings', 'current': 'Finzo Current Account' };
      const accNameEl = document.getElementById('acc-type-name');
      if (accNameEl) accNameEl.textContent = accTypeMap[UserState.accountType] || 'Finzo Account';
    } catch (err) {
      document.body.innerHTML += `<div style="position:fixed;top:0;left:0;background:red;color:white;z-index:99999;padding:20px;font-size:20px;width:100%;">${err.stack}</div>`;
    }
  })();
