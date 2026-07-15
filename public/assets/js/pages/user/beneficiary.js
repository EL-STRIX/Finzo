(async function() {
  await fetchUserSession();
  buildSidebar('beneficiary'); buildNavbar('Banking','Beneficiaries');
  handleStatusRedirect('beneficiary');
  loadBeneficiaries();
})();

let beneficiaries = [];
let verifiedHolder = '';

async function loadBeneficiaries() {
  try {
    const res = await fetch('/Finzo/api/user/beneficiary.php', { credentials: 'same-origin' });
    const data = await res.json();
    if (data.success && data.beneficiaries) {
      beneficiaries = data.beneficiaries.map(b => ({
        id: b.id,
        nick: b.nickname || b.name,
        account: b.account_no,
        holder: b.name,
        bank: b.bank_name || 'Finzo Banking System',
        added: b.created_at ? new Date(b.created_at).toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'}).toUpperCase() : '—'
      }));
    }
  } catch(e) { console.warn('Failed to load beneficiaries:', e); }
  renderList();
}

function resetLookup() {
  verifiedHolder = '';
  document.getElementById('ben-result').classList.add('hidden');
}

async function lookupBen() {
  const acc = document.getElementById('new-acc').value.trim().toUpperCase();
  const resEl = document.getElementById('ben-result');
  if (!acc) return;
  try {
    const r = await fetch('/Finzo/api/user/account-lookup.php?account_no=' + encodeURIComponent(acc), { credentials: 'same-origin' });
    const data = await r.json();
    if (data.success && data.account) {
      verifiedHolder = data.account.holder_name;
      resEl.innerHTML = '<span class="material-symbols-outlined text-base">check_circle</span> Verified: ' + verifiedHolder;
      resEl.className = 'mt-3 p-3 rounded-lg text-xs font-bold flex items-center gap-2 animate-in fade-in slide-in-from-top-1 duration-200 bg-success-surface border border-success-base/20 text-success-base';
    } else {
      verifiedHolder = '';
      resEl.innerHTML = '<span class="material-symbols-outlined text-base">error</span> Finzo account not found';
      resEl.className = 'mt-3 p-3 rounded-lg text-xs font-bold flex items-center gap-2 animate-in fade-in slide-in-from-top-1 duration-200 bg-danger-surface border border-danger-base/20 text-danger-base';
    }
  } catch(e) {
    verifiedHolder = '';
    resEl.innerHTML = '<span class="material-symbols-outlined text-base">error</span> Lookup failed';
    resEl.className = 'mt-3 p-3 rounded-lg text-xs font-bold flex items-center gap-2 animate-in fade-in slide-in-from-top-1 duration-200 bg-danger-surface border border-danger-base/20 text-danger-base';
  }
}

async function addBeneficiary() {
  const nick = document.getElementById('new-nick').value.trim();
  const acc = document.getElementById('new-acc').value.trim().toUpperCase();
  if (!nick || !acc) { showToast('Please fill in all fields', 'error'); return; }
  if (!verifiedHolder) { showToast('Please verify the Finzo account number first', 'error'); return; }

  try {
    const res = await fetch('/Finzo/api/user/beneficiary.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      credentials: 'same-origin',
      body: 'action=add&nickname=' + encodeURIComponent(nick) + '&account_no=' + encodeURIComponent(acc) + '&holder_name=' + encodeURIComponent(verifiedHolder)
    });
    const data = await res.json();
    if (data.success) {
      document.getElementById('new-nick').value = '';
      document.getElementById('new-acc').value = '';
      resetLookup();
      showToast('Beneficiary saved successfully', 'success');
      loadBeneficiaries();
    } else {
      showToast(data.message || 'Failed to save beneficiary', 'error');
    }
  } catch(e) { showToast('Connection error', 'error'); }
}

function deleteBen(id) {
  openConfirm('Remove Beneficiary', 'Are you sure you want to remove this beneficiary?', async () => {
    try {
      await fetch('/Finzo/api/user/beneficiary.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        credentials: 'same-origin',
        body: 'action=delete&beneficiary_id=' + encodeURIComponent(id)
      });
    } catch(e) {}
    beneficiaries = beneficiaries.filter(b => b.id !== id);
    renderList();
    showToast('Beneficiary removed', 'success');
  }, true);
}

function renderList() {
  const list = document.getElementById('beneficiary-list');
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
    const initials = b.nick.split(' ').map(n=>n[0]).join('').substring(0,2).toUpperCase();
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
        <a href="transfer.html?to=${b.account}" class="btn btn-primary px-5 h-10 text-xs">
          Send Money
        </a>
      </div>
    </div>`;
  }).join('');
}