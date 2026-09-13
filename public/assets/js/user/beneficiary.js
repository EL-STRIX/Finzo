/**
 * Finzo User Portal — Beneficiary Management
 */

function toast(message, type = 'info') {
  if (typeof window.showToast === 'function') {
    window.showToast(message, type);
  } else if (typeof showToast === 'function') {
    showToast(message, type);
  } else if (window.FinzoUtils && typeof window.FinzoUtils.showToast === 'function') {
    window.FinzoUtils.showToast(message, type);
  } else {
    alert(message);
  }
}

let beneficiaries = [];
let verifiedHolder = '';

document.addEventListener('DOMContentLoaded', async function() {
  await fetchUserSession();
  buildSidebar('beneficiary');
  buildNavbar('Banking', 'Beneficiaries');
  handleStatusRedirect('beneficiary');
  loadBeneficiaries();

  // Attach Enter key trigger on nickname input
  const nickInput = document.getElementById('new-nick');
  if (nickInput) {
    nickInput.addEventListener('keydown', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        const accInput = document.getElementById('new-acc');
        if (accInput && !accInput.value.trim()) {
          accInput.focus();
        } else {
          addBeneficiary();
        }
      }
    });
  }

  // Attach Enter key trigger on account number input
  const accInput = document.getElementById('new-acc');
  if (accInput) {
    accInput.addEventListener('keydown', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        addBeneficiary();
      }
    });
  }

  // Explicit click listeners
  const saveBtn = document.getElementById('btn-save-ben') || document.querySelector('[data-action="addBeneficiary()"]');
  if (saveBtn) {
    saveBtn.addEventListener('click', function(e) {
      e.preventDefault();
      addBeneficiary();
    });
  }

  const lookupBtn = document.getElementById('btn-lookup-ben') || document.querySelector('[data-action="lookupBen()"]');
  if (lookupBtn) {
    lookupBtn.addEventListener('click', function(e) {
      e.preventDefault();
      lookupBen();
    });
  }
});

async function loadBeneficiaries() {
  try {
    const res = await (window.api ? window.api.get('/user/beneficiaries') : fetch('/Finzo/api/user/beneficiary.php').then(r => r.json()));
    if (res && res.success && res.beneficiaries) {
      beneficiaries = res.beneficiaries.map(b => ({
        id: b.id,
        nick: b.nickname || b.name,
        account: b.account_no,
        holder: b.holder_name || b.name || b.nickname,
        bank: b.bank_name || 'Finzo Banking System',
        added: b.created_at ? new Date(b.created_at).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' }).toUpperCase() : '—'
      }));
    }
  } catch (e) {
    console.warn('Failed to load beneficiaries:', e);
  }
  renderList();
}

function resetLookup() {
  verifiedHolder = '';
  const resEl = document.getElementById('ben-result');
  if (resEl) {
    resEl.classList.add('hidden');
    resEl.innerHTML = '';
  }
}

async function lookupBen() {
  const accInput = document.getElementById('new-acc');
  const acc = (accInput ? accInput.value : '').trim().toUpperCase();
  const resEl = document.getElementById('ben-result');
  const btn = document.getElementById('btn-lookup-ben') || document.querySelector('[data-action="lookupBen()"]');
  if (!acc) {
    toast('Please enter an account number to verify', 'warning');
    if (accInput) accInput.focus();
    return false;
  }

  // Prevent looking up own account
  const ownAcc = (window.UserState && window.UserState.accountNo) ? String(window.UserState.accountNo).trim().toUpperCase() : '';
  if (ownAcc && acc === ownAcc) {
    verifiedHolder = '';
    if (resEl) {
      resEl.innerHTML = '<span class="material-symbols-outlined text-base">warning</span> Cannot add your own account as a beneficiary';
      resEl.className = 'mt-3 p-3 rounded-lg text-xs font-bold flex items-center gap-2 animate-in fade-in slide-in-from-top-1 duration-200 bg-danger-surface border border-danger-base/20 text-danger-base';
      resEl.classList.remove('hidden');
    }
    toast('Cannot add your own account as a beneficiary', 'warning');
    return false;
  }

  const originalBtnHtml = btn ? btn.innerHTML : '';
  if (btn) {
    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined animate-spin text-sm">sync</span><span>Verifying...</span>';
  }

  try {
    let data;
    if (window.api) {
      data = await window.api.get('/user/accounts/lookup', { account_no: acc });
    } else {
      const r = await fetch('/Finzo/api/user/account-lookup.php?account_no=' + encodeURIComponent(acc), { credentials: 'same-origin' });
      data = await r.json();
    }

    if (resEl) resEl.classList.remove('hidden');

    if (data && data.success && data.account) {
      if (data.account.is_self) {
        verifiedHolder = '';
        if (resEl) {
          resEl.innerHTML = '<span class="material-symbols-outlined text-base">warning</span> Cannot add your own account as a beneficiary';
          resEl.className = 'mt-3 p-3 rounded-lg text-xs font-bold flex items-center gap-2 animate-in fade-in slide-in-from-top-1 duration-200 bg-danger-surface border border-danger-base/20 text-danger-base';
        }
        toast('Cannot add your own account as a beneficiary', 'warning');
        return false;
      } else {
        verifiedHolder = data.account.holder_name;
        const accType = data.account.account_type ? ` (${data.account.account_type.toUpperCase()})` : '';
        if (resEl) {
          resEl.innerHTML = `<span class="material-symbols-outlined text-base">check_circle</span> Verified: <span class="font-black">${verifiedHolder}</span>${accType}`;
          resEl.className = 'mt-3 p-3 rounded-lg text-xs font-bold flex items-center gap-2 animate-in fade-in slide-in-from-top-1 duration-200 bg-success-surface border border-success-base/20 text-success-base';
        }
        return true;
      }
    } else {
      verifiedHolder = '';
      const errMsg = (data && data.message) ? data.message : 'Finzo account not found';
      if (resEl) {
        resEl.innerHTML = `<span class="material-symbols-outlined text-base">error</span> ${errMsg}`;
        resEl.className = 'mt-3 p-3 rounded-lg text-xs font-bold flex items-center gap-2 animate-in fade-in slide-in-from-top-1 duration-200 bg-danger-surface border border-danger-base/20 text-danger-base';
      }
      toast(errMsg, 'error');
      return false;
    }
  } catch (e) {
    verifiedHolder = '';
    if (resEl) {
      resEl.innerHTML = '<span class="material-symbols-outlined text-base">error</span> Lookup failed. Please try again.';
      resEl.className = 'mt-3 p-3 rounded-lg text-xs font-bold flex items-center gap-2 animate-in fade-in slide-in-from-top-1 duration-200 bg-danger-surface border border-danger-base/20 text-danger-base';
      resEl.classList.remove('hidden');
    }
    toast('Lookup failed. Please try again.', 'error');
    return false;
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.innerHTML = originalBtnHtml;
    }
  }
}

let isSavingBeneficiary = false;

async function addBeneficiary() {
  if (isSavingBeneficiary) return;

  const nickInput = document.getElementById('new-nick');
  const accInput = document.getElementById('new-acc');
  const nick = (nickInput ? nickInput.value : '').trim();
  const acc = (accInput ? accInput.value : '').trim().toUpperCase();

  if (!nick || !acc) {
    toast('Please fill in both Nickname and Account Number', 'warning');
    if (!nick && nickInput) nickInput.focus();
    else if (!acc && accInput) accInput.focus();
    return;
  }

  const saveBtn = document.getElementById('btn-save-ben') || document.querySelector('[data-action="addBeneficiary()"]');
  const originalSaveBtnHtml = saveBtn ? saveBtn.innerHTML : 'Save Beneficiary';

  // If user hasn't explicitly clicked 'Verify Account' yet, auto-verify now
  if (!verifiedHolder) {
    const verifiedOk = await lookupBen();
    if (!verifiedOk || !verifiedHolder) {
      return;
    }
  }

  isSavingBeneficiary = true;
  if (saveBtn) {
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="material-symbols-outlined animate-spin text-sm">sync</span><span>Saving...</span>';
  }

  try {
    let data;
    if (window.api) {
      data = await window.api.post('/user/beneficiaries', {
        nickname: nick,
        account_no: acc,
        holder_name: verifiedHolder,
        bank_name: 'Finzo Banking System'
      });
    } else {
      const res = await fetch('/Finzo/api/user/beneficiary.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        credentials: 'same-origin',
        body: 'action=add&nickname=' + encodeURIComponent(nick) + '&account_no=' + encodeURIComponent(acc) + '&holder_name=' + encodeURIComponent(verifiedHolder)
      });
      data = await res.json();
    }

    if (data && data.success) {
      if (nickInput) nickInput.value = '';
      if (accInput) accInput.value = '';
      resetLookup();
      toast('Beneficiary saved successfully', 'success');
      await loadBeneficiaries();
    } else {
      toast((data && data.message) ? data.message : 'Failed to save beneficiary', 'error');
    }
  } catch (e) {
    console.error('Error saving beneficiary:', e);
    toast('Connection error saving beneficiary', 'error');
  } finally {
    isSavingBeneficiary = false;
    if (saveBtn) {
      saveBtn.disabled = false;
      saveBtn.innerHTML = originalSaveBtnHtml;
    }
  }
}

function deleteBen(id) {
  openConfirm('Remove Beneficiary', 'Are you sure you want to remove this beneficiary?', async () => {
    try {
      if (window.api) {
        await window.api.delete('/user/beneficiaries', { beneficiary_id: id });
      } else {
        await fetch('/Finzo/api/user/beneficiary.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          credentials: 'same-origin',
          body: 'action=delete&beneficiary_id=' + encodeURIComponent(id)
        });
      }
    } catch (e) {}
    beneficiaries = beneficiaries.filter(b => b.id !== id);
    renderList();
    toast('Beneficiary removed', 'success');
  }, true);
}

function renderList() {
  const list = document.getElementById('beneficiary-list');
  if (!list) return;

  if (!beneficiaries.length) { 
    list.innerHTML = `
    <div class="py-16 flex flex-col items-center justify-center text-center">
      <div class="w-16 h-16 rounded-full bg-[var(--color-surface-hover)] text-[var(--color-text-disabled)] flex items-center justify-center mb-4">
        <span class="material-symbols-outlined text-3xl">group</span>
      </div>
      <div class="font-display text-lg font-bold text-[var(--color-text-main)] mb-1">No saved beneficiaries</div>
      <div class="text-sm text-[var(--color-text-muted)]">Add your first beneficiary using the form.</div>
    </div>`; 
    return; 
  }
  
  list.innerHTML = beneficiaries.map(b => {
    const initials = b.nick.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
    return `
    <div class="p-5 flex flex-col sm:flex-row sm:items-center gap-4 border-b border-[var(--color-border-main)] last:border-0 hover:bg-[var(--color-surface-hover)] transition-colors group">
      <div class="w-12 h-12 rounded-full bg-primary-surface text-primary-base flex items-center justify-center font-display font-bold text-lg shrink-0">
        ${initials}
      </div>
      <div class="flex-1">
        <div class="text-sm font-bold text-[var(--color-text-main)] flex items-center gap-2">
          ${b.nick} 
          <span class="ds-badge ds-badge-success !py-0.5 !px-1.5 !text-[9px]">VERIFIED</span>
        </div>
        <div class="font-mono text-xs font-bold text-[var(--color-text-muted)] mt-1 tracking-wider">${b.account}</div>
        <div class="text-[10px] font-bold text-[var(--color-text-disabled)] mt-1 uppercase tracking-widest">Added ${b.added}</div>
      </div>
      <div class="flex items-center gap-2 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity">
        <button class="btn btn-secondary !p-0 w-10 h-10 rounded-full flex items-center justify-center text-danger-base hover:bg-danger-surface hover:text-danger-base hover:border-danger-base/20 transition-all" onclick="deleteBen(${b.id})" title="Remove Beneficiary">
          <span class="material-symbols-outlined text-[18px]">delete</span>
        </button>
        <a href="transfer.php?to=${encodeURIComponent(b.account)}" class="btn btn-primary px-5 h-10 text-xs">
          Send Money
        </a>
      </div>
    </div>`;
  }).join('');
}

// Explicitly expose handlers to window
window.lookupBen = lookupBen;
window.addBeneficiary = addBeneficiary;
window.deleteBen = deleteBen;
window.resetLookup = resetLookup;
