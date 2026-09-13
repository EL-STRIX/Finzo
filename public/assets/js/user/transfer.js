/**
 * Finzo - Fund Transfer Controller
 * Real-time balance and limit checks, beneficiary auto-discovery, ISO 20022 compliant ACID execution.
 */

let transferState = {
  accountNo: '',
  accountType: 'savings',
  balance: 0,
  dailyLimit: 10000,
  todayTransferTotal: 0,
  remainingLimit: 10000,
  status: 'active'
};

let recipientVerified = false;
let recipientName = '';
let recipientAccNo = '';

// Helper: Format currency safely
function formatMoney(amount) {
  if (typeof window.formatINR === 'function') {
    const formatted = window.formatINR(amount);
    return formatted.startsWith('₹') ? formatted : '₹' + formatted;
  }
  return '₹' + parseFloat(amount || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// Helper: Indian numbering system words converter
function convertToWords(number) {
  if (typeof window.numberToWords === 'function') {
    return window.numberToWords(number);
  }
  let num = Math.floor(Math.abs(Number(number) || 0));
  if (num === 0) return 'Zero';
  const ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
  const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

  function convertLessThanOneThousand(n) {
    if (n === 0) return '';
    if (n < 20) return ones[n];
    if (n < 100) return tens[Math.floor(n / 10)] + (n % 10 !== 0 ? ' ' + ones[n % 10] : '');
    return ones[Math.floor(n / 100)] + ' Hundred' + (n % 100 !== 0 ? ' ' + convertLessThanOneThousand(n % 100) : '');
  }

  let parts = [];
  const crore = Math.floor(num / 10000000);
  num %= 10000000;
  const lakh = Math.floor(num / 100000);
  num %= 100000;
  const thousand = Math.floor(num / 1000);
  num %= 1000;
  const remainder = num;

  if (crore > 0) parts.push(convertLessThanOneThousand(crore) + ' Crore');
  if (lakh > 0) parts.push(convertLessThanOneThousand(lakh) + ' Lakh');
  if (thousand > 0) parts.push(convertLessThanOneThousand(thousand) + ' Thousand');
  if (remainder > 0) parts.push(convertLessThanOneThousand(remainder));

  return parts.join(' ').trim();
}

function escapeHtml(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

// Expose globals immediately for inline and data-action handlers
window.useBeneficiary = useBeneficiary;
window.resetLookup = resetLookup;
window.lookupAccount = lookupAccount;
window.quickAmount = quickAmount;
window.updateSummary = updateSummary;
window.togglePinVisibility = togglePinVisibility;
window.confirmTransfer = confirmTransfer;
window.closeModal = closeModal;
window.processTransfer = processTransfer;

(async function initFundTransfer() {
  if (typeof fetchUserSession === 'function') {
    await fetchUserSession();
  }
  if (typeof buildSidebar === 'function') {
    buildSidebar('transfer');
  }
  if (typeof buildNavbar === 'function') {
    buildNavbar('Banking', 'Fund Transfer');
  }
  if (typeof handleStatusRedirect === 'function') {
    handleStatusRedirect('transfer');
  }

  await loadSourceAccount();
  await loadBeneficiaries();
  setupEventListeners();
})();

async function loadSourceAccount() {
  try {
    let res = null;
    if (window.api && typeof window.api.get === 'function') {
      res = await window.api.get('/user/accounts');
    } else {
      const r = await fetch('/Finzo/api/v1/user/accounts', { credentials: 'same-origin' });
      res = await r.json();
    }

    if (res && res.success && res.account) {
      const acc = res.account;
      transferState.accountNo = acc.account_no || (typeof UserState !== 'undefined' ? UserState.accountNo : '');
      transferState.accountType = acc.account_type || (typeof UserState !== 'undefined' ? UserState.accountType : 'savings');
      transferState.balance = parseFloat(acc.balance ?? (typeof UserState !== 'undefined' ? UserState.balance : 0));
      transferState.dailyLimit = parseFloat(acc.daily_limit ?? (typeof UserState !== 'undefined' ? UserState.dailyLimit : 10000));
      transferState.todayTransferTotal = parseFloat(res.today_transfer_total ?? 0);
      transferState.remainingLimit = parseFloat(res.remaining_limit ?? Math.max(0, transferState.dailyLimit - transferState.todayTransferTotal));
      transferState.status = acc.status || 'active';

      renderSourceAccount();
    } else {
      // Fallback to UserState cache
      if (typeof UserState !== 'undefined') {
        transferState.accountNo = UserState.accountNo || '';
        transferState.accountType = UserState.accountType || 'savings';
        transferState.balance = parseFloat(UserState.balance || 0);
        transferState.dailyLimit = parseFloat(UserState.dailyLimit || 10000);
        transferState.remainingLimit = transferState.dailyLimit;
        renderSourceAccount();
      }
    }
  } catch (e) {
    console.warn('Failed to load accounts real-time info:', e);
    if (typeof UserState !== 'undefined') {
      transferState.accountNo = UserState.accountNo || '';
      transferState.balance = parseFloat(UserState.balance || 0);
      renderSourceAccount();
    }
  }
}

function renderSourceAccount() {
  const accNoEl = document.getElementById('src-acc-no');
  const accTypeEl = document.getElementById('src-acc-type');
  const balanceEl = document.getElementById('src-balance');
  const remainingEl = document.getElementById('src-remaining-limit');
  const usedEl = document.getElementById('src-limit-used');
  const maxEl = document.getElementById('src-limit-max');
  const progressEl = document.getElementById('src-limit-progress');
  const cmFromEl = document.getElementById('cm-from');
  const statusBadge = document.getElementById('src-status-badge');

  if (accNoEl) accNoEl.textContent = transferState.accountNo || 'NOT ASSIGNED';
  if (accTypeEl) accTypeEl.textContent = (transferState.accountType || 'SAVINGS').toUpperCase();
  if (balanceEl) balanceEl.textContent = formatMoney(transferState.balance);
  if (remainingEl) remainingEl.textContent = formatMoney(transferState.remainingLimit);
  if (usedEl) usedEl.textContent = formatMoney(transferState.todayTransferTotal);
  if (maxEl) maxEl.textContent = formatMoney(transferState.dailyLimit);
  if (cmFromEl) cmFromEl.textContent = transferState.accountNo || '-';

  if (statusBadge) {
    if (transferState.status === 'active') {
      statusBadge.innerHTML = `
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-xs font-bold border border-emerald-500/20">
          <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
          Active
        </span>
      `;
    } else {
      statusBadge.innerHTML = `
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-rose-500/10 text-rose-600 dark:text-rose-400 text-xs font-bold border border-rose-500/20">
          <span class="w-2 h-2 rounded-full bg-rose-500"></span>
          ${transferState.status.toUpperCase()}
        </span>
      `;
    }
  }

  if (progressEl) {
    const total = transferState.dailyLimit > 0 ? transferState.dailyLimit : 1;
    const pct = Math.min(100, Math.round((transferState.todayTransferTotal / total) * 100));
    progressEl.style.width = pct + '%';
    if (pct >= 90) {
      progressEl.className = 'bg-rose-500 h-full rounded-full transition-all duration-500';
    } else if (pct >= 70) {
      progressEl.className = 'bg-amber-500 h-full rounded-full transition-all duration-500';
    } else {
      progressEl.className = 'bg-primary h-full rounded-full transition-all duration-500';
    }
  }
}

async function loadBeneficiaries() {
  const container = document.getElementById('beneficiary-chips');
  if (!container) return;

  try {
    let data = null;
    if (window.api && typeof window.api.get === 'function') {
      data = await window.api.get('/user/beneficiaries');
    } else {
      const r = await fetch('/Finzo/api/v1/user/beneficiaries', { credentials: 'same-origin' });
      data = await r.json();
    }

    if (data && data.success && data.beneficiaries && data.beneficiaries.length > 0) {
      container.innerHTML = data.beneficiaries.map(b => {
        const safeAcc = escapeHtml(b.account_no);
        const safeNick = escapeHtml(b.nickname || b.name || 'Beneficiary');
        return `
          <button type="button" class="px-3.5 py-1.5 rounded-full border border-border-main bg-surface-subtle text-xs font-bold text-text-muted hover:border-primary hover:text-primary transition-all flex items-center gap-1.5 shadow-sm hover:shadow" onclick="useBeneficiary('${safeAcc}', '${safeNick}')">
            <span class="material-symbols-outlined text-[15px] text-amber-500">star</span>
            <span>${safeNick}</span>
            <span class="font-mono text-[10px] opacity-70">(${safeAcc.slice(-4)})</span>
          </button>
        `;
      }).join('');
    } else {
      container.innerHTML = '<div class="text-xs text-text-muted italic">No saved beneficiaries found. Enter an account number below.</div>';
    }
  } catch (e) {
    container.innerHTML = '<div class="text-xs text-rose-500 font-bold">Unable to load beneficiaries.</div>';
  }

  // Support deep link ?to=FNZ...
  const params = new URLSearchParams(window.location.search);
  const toAcc = params.get('to');
  if (toAcc) {
    const toInput = document.getElementById('to-account');
    if (toInput) {
      toInput.value = toAcc.toUpperCase();
      lookupAccount();
    }
  }
}

function useBeneficiary(acc, name) {
  const toInput = document.getElementById('to-account');
  if (toInput) {
    toInput.value = acc;
    lookupAccount();
  }
}

function resetLookup() {
  recipientVerified = false;
  recipientName = '';
  recipientAccNo = '';

  const resultContainer = document.getElementById('lookup-result');
  const foundEl = document.getElementById('lookup-found');
  const notFoundEl = document.getElementById('lookup-not-found');

  if (resultContainer) resultContainer.classList.add('hidden');
  if (foundEl) foundEl.classList.add('hidden');
  if (notFoundEl) notFoundEl.classList.add('hidden');

  updateSummary();
}

async function lookupAccount() {
  const inputEl = document.getElementById('to-account');
  if (!inputEl) return false;
  const accNo = inputEl.value.replace(/[\s\-]/g, '').toUpperCase();
  inputEl.value = accNo;

  if (!accNo) {
    if (typeof showToast === 'function') showToast('Please enter an account number', 'warning');
    return false;
  }

  const myAccount = transferState.accountNo || (typeof UserState !== 'undefined' ? UserState.accountNo : '');
  if (myAccount && accNo === myAccount.replace(/[\s\-]/g, '').toUpperCase()) {
    if (typeof showToast === 'function') showToast('Cannot transfer to your own account', 'error');
    recipientVerified = false;
    showLookupError('Cannot transfer to your own account');
    return false;
  }

  const btn = document.getElementById('btn-verify-identity');
  if (btn) {
    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined text-lg animate-spin">sync</span><span>Verifying...</span>';
  }

  try {
    let data = null;
    if (window.api && typeof window.api.get === 'function') {
      data = await window.api.get('/user/accounts/lookup', { account_no: accNo });
    } else {
      const r = await fetch('/Finzo/api/v1/user/accounts/lookup?account_no=' + encodeURIComponent(accNo), { credentials: 'same-origin' });
      data = await r.json();
    }

    const resultContainer = document.getElementById('lookup-result');
    const foundEl = document.getElementById('lookup-found');
    const notFoundEl = document.getElementById('lookup-not-found');

    if (resultContainer) resultContainer.classList.remove('hidden');

    if (data && data.success && data.account) {
      if (data.account.is_self) {
        recipientVerified = false;
        showLookupError('Cannot transfer to your own account');
        if (typeof showToast === 'function') showToast('Cannot transfer to your own account', 'warning');
        return false;
      }

      recipientVerified = true;
      recipientName = data.account.holder_name || 'Verified Beneficiary';
      recipientAccNo = accNo;

      if (notFoundEl) notFoundEl.classList.add('hidden');
      if (foundEl) foundEl.classList.remove('hidden');

      const nameEl = document.getElementById('recipient-name');
      const typeEl = document.getElementById('recipient-type');
      if (nameEl) nameEl.textContent = recipientName;
      if (typeEl) typeEl.textContent = (data.account.account_type || 'Savings') + ' Account';

      if (typeof showToast === 'function') showToast('Recipient verified: ' + recipientName, 'success');
      updateSummary();
      return true;
    } else {
      recipientVerified = false;
      const msg = (data && data.message) ? data.message : 'Finzo account not found';
      showLookupError(msg);
      if (typeof showToast === 'function') showToast(msg, 'error');
      return false;
    }
  } catch (err) {
    recipientVerified = false;
    showLookupError('Account lookup failed. Please try again.');
    if (typeof showToast === 'function') showToast('Lookup failed. Please check connection.', 'error');
    return false;
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.innerHTML = '<span class="material-symbols-outlined text-lg" aria-hidden="true">verified</span><span>Verify Identity</span>';
    }
  }
}

function showLookupError(message) {
  const resultContainer = document.getElementById('lookup-result');
  const foundEl = document.getElementById('lookup-found');
  const notFoundEl = document.getElementById('lookup-not-found');
  const msgEl = document.getElementById('lookup-error-msg');

  if (resultContainer) resultContainer.classList.remove('hidden');
  if (foundEl) foundEl.classList.add('hidden');
  if (notFoundEl) notFoundEl.classList.remove('hidden');
  if (msgEl) msgEl.textContent = message;
}

function quickAmount(val) {
  const input = document.getElementById('transfer-amount');
  if (!input) return;

  if (val === 'max') {
    const maxTransferable = Math.max(0, Math.min(transferState.balance, transferState.remainingLimit));
    input.value = maxTransferable.toFixed(2);
  } else {
    const current = parseFloat(input.value || 0);
    input.value = (current + parseFloat(val)).toFixed(2);
  }
  updateSummary();
}

function updateSummary() {
  const amountEl = document.getElementById('transfer-amount');
  const wordsDiv = document.getElementById('amount-words');
  const errorDiv = document.getElementById('amount-error');
  if (!amountEl) return;

  const amount = parseFloat(amountEl.value || 0);
  const balance = transferState.balance;
  const remainingLimit = transferState.remainingLimit;

  if (amount > 0) {
    if (wordsDiv) {
      wordsDiv.textContent = convertToWords(amount) + ' Rupees Only';
    }

    if (errorDiv) {
      if (amount < 10) {
        errorDiv.textContent = 'Minimum transfer amount is ₹10.00';
        errorDiv.classList.remove('hidden');
      } else if (amount > balance) {
        errorDiv.textContent = 'Insufficient balance (Available: ' + formatMoney(balance) + ')';
        errorDiv.classList.remove('hidden');
      } else if (amount > remainingLimit) {
        errorDiv.textContent = 'Exceeds today\'s remaining limit (' + formatMoney(remainingLimit) + ')';
        errorDiv.classList.remove('hidden');
      } else {
        errorDiv.textContent = '';
        errorDiv.classList.add('hidden');
      }
    }
  } else {
    if (wordsDiv) wordsDiv.textContent = '';
    if (errorDiv) {
      errorDiv.textContent = '';
      errorDiv.classList.add('hidden');
    }
  }
}

function togglePinVisibility() {
  const pinInput = document.getElementById('transfer-pin');
  const icon = document.getElementById('pin-toggle-icon');
  if (!pinInput) return;

  if (pinInput.type === 'password') {
    pinInput.type = 'text';
    if (icon) icon.textContent = 'visibility_off';
  } else {
    pinInput.type = 'password';
    if (icon) icon.textContent = 'visibility';
  }
}

async function confirmTransfer() {
  const toInput = document.getElementById('to-account');
  const amountEl = document.getElementById('transfer-amount');
  const pinEl = document.getElementById('transfer-pin');
  const descEl = document.getElementById('transfer-desc');

  // If user hasn't clicked verify yet but entered an account, automatically attempt verification
  if (!recipientVerified && toInput && toInput.value.trim().length > 0) {
    const ok = await lookupAccount();
    if (!ok) return;
  }

  if (!recipientVerified) {
    if (typeof showToast === 'function') showToast('Please enter and verify the recipient account first', 'warning');
    if (toInput) toInput.focus();
    return;
  }

  const amount = parseFloat(amountEl ? amountEl.value : 0);
  const pin = pinEl ? pinEl.value.trim() : '';
  const desc = descEl ? descEl.value.trim() : '';

  if (isNaN(amount) || amount <= 0) {
    if (typeof showToast === 'function') showToast('Please enter a valid transfer amount', 'warning');
    if (amountEl) amountEl.focus();
    return;
  }

  if (amount < 10) {
    if (typeof showToast === 'function') showToast('Minimum transfer amount is ₹10.00', 'warning');
    if (amountEl) amountEl.focus();
    return;
  }

  if (amount > transferState.balance) {
    if (typeof showToast === 'function') showToast('Insufficient funds in source account', 'error');
    if (amountEl) amountEl.focus();
    return;
  }

  if (amount > transferState.remainingLimit) {
    if (typeof showToast === 'function') showToast('Transfer exceeds today\'s remaining daily limit of ' + formatMoney(transferState.remainingLimit), 'error');
    return;
  }

  if (!pin || !/^\d{4}$/.test(pin)) {
    if (typeof showToast === 'function') showToast('Please enter your 4-digit numeric security PIN', 'warning');
    if (pinEl) pinEl.focus();
    return;
  }

  // Populate authorization modal
  const cmFrom = document.getElementById('cm-from');
  const cmTo = document.getElementById('cm-to');
  const cmRecipient = document.getElementById('cm-recipient');
  const cmAmount = document.getElementById('cm-amount');
  const cmDesc = document.getElementById('cm-desc');

  if (cmFrom) cmFrom.textContent = transferState.accountNo || 'FNZ-SOURCE';
  if (cmTo) cmTo.textContent = recipientAccNo;
  if (cmRecipient) cmRecipient.textContent = recipientName;
  if (cmAmount) cmAmount.textContent = formatMoney(amount);
  if (cmDesc) cmDesc.textContent = desc || 'None';

  const modal = document.getElementById('confirm-modal');
  if (modal) modal.classList.remove('hidden');
}

function closeModal(id) {
  const modal = document.getElementById(id);
  if (modal) modal.classList.add('hidden');
}

async function processTransfer() {
  const amountEl = document.getElementById('transfer-amount');
  const pinEl = document.getElementById('transfer-pin');
  const descEl = document.getElementById('transfer-desc');
  const btn = document.getElementById('final-transfer-btn');

  const amount = parseFloat(amountEl ? amountEl.value : 0);
  const pin = pinEl ? pinEl.value.trim() : '';
  const desc = descEl ? descEl.value.trim() : '';

  if (btn) {
    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined text-lg animate-spin">sync</span><span>Authorizing & Sending...</span>';
  }

  try {
    const payload = {
      to_account: recipientAccNo,
      to_account_no: recipientAccNo,
      amount: amount,
      pin: pin,
      txn_pin: pin,
      description: desc
    };

    let data = null;
    if (window.api && typeof window.api.post === 'function') {
      data = await window.api.post('/user/transactions/transfer', payload);
    } else {
      const r = await fetch('/Finzo/api/v1/user/transactions/transfer', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-Token': window.csrfToken || ''
        },
        body: JSON.stringify(payload)
      });
      data = await r.json();
    }

    if (data && data.success) {
      if (typeof showToast === 'function') showToast('Transfer authorized successfully!', 'success');
      const txnId = data.transaction_id || data.txn_id || '';
      
      // Update local cache balance if returned
      if (data.new_balance !== undefined && typeof UserState !== 'undefined') {
        try {
          UserState.balance = data.new_balance;
        } catch (e) {
          console.warn('Could not update UserState.balance:', e);
        }
      }

      setTimeout(() => {
        if (data.redirect) {
          // Clean possible HTML entity encoding in query strings (&amp; -> &)
          window.location.href = data.redirect.replace(/&amp;/g, '&');
        } else {
          window.location.href = `receipt.php?id=${encodeURIComponent(txnId)}&success=1`;
        }
      }, 500);
    } else {
      const msg = (data && data.message) ? data.message : 'Transfer failed';
      if (typeof showToast === 'function') showToast(msg, 'error');
      if (btn) {
        btn.disabled = false;
        btn.innerHTML = '<span class="material-symbols-outlined text-lg" aria-hidden="true">done_all</span><span>Authorize & Send</span>';
      }
    }
  } catch (err) {
    if (typeof showToast === 'function') showToast('Network error during transfer. Please check your transaction history before retrying.', 'error');
    if (btn) {
      btn.disabled = false;
      btn.innerHTML = '<span class="material-symbols-outlined text-lg" aria-hidden="true">done_all</span><span>Authorize & Send</span>';
    }
  }
}

function setupEventListeners() {
  const toInput = document.getElementById('to-account');
  if (toInput) {
    toInput.addEventListener('keydown', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        lookupAccount();
      }
    });
  }

  const pinInput = document.getElementById('transfer-pin');
  if (pinInput) {
    pinInput.addEventListener('keydown', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        confirmTransfer();
      }
    });
  }

  // Direct click bindings for bulletproof execution
  const btnVerify = document.getElementById('btn-verify-identity');
  if (btnVerify) {
    btnVerify.addEventListener('click', function(e) {
      e.preventDefault();
      lookupAccount();
    });
  }

  const btnTransfer = document.getElementById('transfer-btn');
  if (btnTransfer) {
    btnTransfer.addEventListener('click', function(e) {
      e.preventDefault();
      confirmTransfer();
    });
  }

  const btnFinal = document.getElementById('final-transfer-btn');
  if (btnFinal) {
    btnFinal.addEventListener('click', function(e) {
      e.preventDefault();
      processTransfer();
    });
  }

  const btnModalCancel = document.getElementById('modal-cancel-btn');
  if (btnModalCancel) {
    btnModalCancel.addEventListener('click', function(e) {
      e.preventDefault();
      closeModal('confirm-modal');
    });
  }
}
