let _rawIdNumber = '';
let _isIdRevealed = false;

function escapeHtml(str) {
  if (!str) return '';
  return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function copyText(elementId, label) {
  const el = document.getElementById(elementId);
  if (!el) return;
  let text = el.textContent.trim();
  if (text.startsWith('Customer ID:')) {
    text = text.replace('Customer ID:', '').trim();
  }
  if (!text || text === '—') return;

  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(text).then(() => {
      if (typeof showToast === 'function') showToast(label + ' copied to clipboard!', 'success');
    }).catch(() => {
      fallbackCopy(text, label);
    });
  } else {
    fallbackCopy(text, label);
  }
}

function fallbackCopy(text, label) {
  try {
    const input = document.createElement('input');
    input.value = text;
    document.body.appendChild(input);
    input.select();
    document.execCommand('copy');
    document.body.removeChild(input);
    if (typeof showToast === 'function') showToast(label + ' copied to clipboard!', 'success');
  } catch(e) {}
}

document.addEventListener('DOMContentLoaded', initProfile);

async function initProfile() {
  if (typeof buildSidebar === 'function') buildSidebar('profile');
  if (typeof buildNavbar === 'function') buildNavbar('Settings', 'My Profile');

  try {
    let res = null;
    if (window.api && typeof window.api.get === 'function') {
      res = await window.api.get('/user/profile');
    }

    const user = (res && res.success && res.user) ? res.user : {
      id: typeof UserState !== 'undefined' ? UserState.id : '',
      customer_id: typeof UserState !== 'undefined' ? UserState.customerId : '',
      full_name: typeof UserState !== 'undefined' ? UserState.name : '',
      email: typeof UserState !== 'undefined' ? UserState.email : '',
      phone: typeof UserState !== 'undefined' ? UserState.phone : '',
      status: typeof UserState !== 'undefined' ? UserState.status : 'pending',
    };

    const account = (res && res.success && res.account) ? res.account : {
      account_no: typeof UserState !== 'undefined' ? UserState.accountNo : '',
      account_type: typeof UserState !== 'undefined' ? UserState.accountType : 'savings',
      balance: typeof UserState !== 'undefined' ? UserState.balance : 0,
    };

    const resolvedName = user.full_name || user.name || (typeof UserState !== 'undefined' ? UserState.name : '') || 'Customer Profile';
    const resolvedCustId = user.customer_id || (typeof UserState !== 'undefined' ? UserState.customerId : '') || 'FNZ-USR-PENDING';
    const resolvedEmail = user.email || (typeof UserState !== 'undefined' ? UserState.email : '') || '';
    const resolvedPhone = user.phone || (typeof UserState !== 'undefined' ? UserState.phone : '') || '';
    const resolvedStatus = user.status || (typeof UserState !== 'undefined' ? UserState.status : 'pending');

    // 0. KYC Banner Toggle
    const banner = document.getElementById('kyc-notice-banner');
    if (banner) {
      if (resolvedStatus === 'pending') {
        banner.classList.remove('hidden');
      } else if (resolvedStatus === 'rejected') {
        banner.classList.remove('hidden');
        const bTitle = document.getElementById('kyc-banner-title');
        const bDesc = document.getElementById('kyc-banner-desc');
        const bBtn = document.getElementById('kyc-banner-btn');
        if (bTitle) bTitle.textContent = 'Action Required: Re-submit Application';
        if (bDesc) bDesc.textContent = user.rejection_reason ? ('Reason: ' + user.rejection_reason) : 'Your previous application was returned for updates.';
        if (bBtn) bBtn.href = 'account-rejected.php';
      } else {
        banner.classList.add('hidden');
      }
    }

    // 1. Identity Summary Card
    const nameEl = document.getElementById('profile-name');
    if (nameEl) nameEl.textContent = resolvedName;
    
    const custIdEl = document.getElementById('info-customer-id');
    if (custIdEl) custIdEl.textContent = 'Customer ID: ' + resolvedCustId;

    const circle = document.getElementById('avatar-circle');
    if (circle) {
      const avatarSrc = (user.profile_picture && typeof user.profile_picture === 'string' && user.profile_picture.trim())
        ? user.profile_picture
        : (typeof UserState !== 'undefined' && UserState.avatarUrl && typeof UserState.avatarUrl === 'string' && UserState.avatarUrl.trim())
        ? UserState.avatarUrl
        : null;

      if (avatarSrc) {
        let cleanSrc = avatarSrc;
        if (!cleanSrc.startsWith('http') && !cleanSrc.startsWith('data:')) {
          const clean = cleanSrc.replace(/^\/+/, '');
          cleanSrc = clean.startsWith('Finzo/public/') ? '/' + clean : '/Finzo/public/uploads/' + clean;
        }
        circle.innerHTML = `<img src="${cleanSrc}" alt="${escapeHtml(resolvedName)}" class="w-full h-full object-cover rounded-full">`;
      } else {
        const initials = resolvedName.split(' ').filter(Boolean).map(n => n[0]).join('').substring(0, 2).toUpperCase() || 'U';
        circle.innerHTML = `<span class="text-primary-base font-bold text-2xl">${initials}</span>`;
      }
    }

    const sMap = { pending: 'badge-warning', under_review: 'badge-info', active: 'badge-success', rejected: 'badge-danger' };
    const sLabel = { pending: 'Pending KYC', under_review: 'In Review', active: 'Active Account', rejected: 'Action Required' };
    const statusEl = document.getElementById('info-status');
    if (statusEl) {
      statusEl.innerHTML = `<span class="badge ${sMap[resolvedStatus] || 'badge-warning'} uppercase font-bold text-[10px]">${sLabel[resolvedStatus] || resolvedStatus}</span>`;
    }
    
    const sinceEl = document.getElementById('info-member-since');
    if (sinceEl) {
      if (user.created_at) {
        const d = new Date(user.created_at);
        sinceEl.textContent = !isNaN(d.getTime()) ? d.toLocaleDateString('en-IN', { month: 'short', year: 'numeric' }) : 'Recently';
      } else {
        sinceEl.textContent = 'Recently';
      }
    }

    // 2. Account Ledger
    const accNoEl = document.getElementById('info-acc-no');
    if (accNoEl) accNoEl.textContent = account.account_no || 'Pending Allocation';
    
    const accTypeEl = document.getElementById('info-acc-type');
    if (accTypeEl) accTypeEl.textContent = (account.account_type || 'Savings') + ' Account';

    const interestEl = document.getElementById('info-interest-rate');
    if (interestEl) interestEl.textContent = (account.interest_rate ? account.interest_rate : 4.5) + '% p.a.';

    const limitEl = document.getElementById('info-daily-limit');
    if (limitEl) limitEl.textContent = typeof formatINR === 'function' ? formatINR(account.daily_limit || 50000) : '₹50,000';

    const balEl = document.getElementById('info-balance');
    if (balEl) balEl.textContent = typeof formatINR === 'function' ? formatINR(account.balance || 0) : '₹0.00';

    // 3. Personal & Demographics
    const viewNameEl = document.getElementById('view-full-name');
    if (viewNameEl) viewNameEl.textContent = resolvedName;

    const dobEl = document.getElementById('view-dob');
    if (dobEl) {
      if (user.dob && user.dob !== '0000-00-00') {
        const d = new Date(user.dob);
        dobEl.textContent = !isNaN(d.getTime()) ? d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' }) : 'Not Provided';
      } else {
        dobEl.textContent = 'Not Provided';
      }
    }

    const genderEl = document.getElementById('view-gender');
    if (genderEl) genderEl.textContent = user.gender || 'Not Specified';

    const natEl = document.getElementById('view-nationality');
    if (natEl) natEl.textContent = user.nationality || 'Indian';

    const fatherEl = document.getElementById('view-father-name');
    if (fatherEl) fatherEl.textContent = user.father_name || 'Not Provided';

    const motherEl = document.getElementById('view-mother-name');
    if (motherEl) motherEl.textContent = user.mother_name || 'Not Provided';

    const maritalEl = document.getElementById('view-marital-status');
    if (maritalEl) maritalEl.textContent = user.marital_status || 'Single';

    // 4. Contact & Address
    const emailInput = document.getElementById('edit-email');
    if (emailInput) emailInput.value = resolvedEmail;

    const phoneInput = document.getElementById('edit-phone');
    if (phoneInput) phoneInput.value = resolvedPhone;

    const streetEl = document.getElementById('view-street');
    if (streetEl) streetEl.textContent = user.street || 'Not Provided';

    const cityEl = document.getElementById('view-city');
    if (cityEl) cityEl.textContent = user.city || 'Not Provided';

    const stateEl = document.getElementById('view-state');
    if (stateEl) stateEl.textContent = user.state || 'Not Provided';

    const pinEl = document.getElementById('view-pincode');
    if (pinEl) pinEl.textContent = user.pincode || '—';

    const countryEl = document.getElementById('view-country');
    if (countryEl) countryEl.textContent = user.country || 'India';

    // 5. Identity Verification
    const idTypeEl = document.getElementById('view-id-type');
    if (idTypeEl) idTypeEl.textContent = (user.id_type || 'Government ID').toUpperCase();
    _rawIdNumber = user.id_number || '';
    updateIdMask();

    // 6. Employment & Financial
    const occEl = document.getElementById('view-occupation');
    if (occEl) occEl.textContent = user.occupation || 'Not Specified';

    const srcIncEl = document.getElementById('view-source-income');
    if (srcIncEl) srcIncEl.textContent = user.source_of_income || 'Salary';

    const annIncEl = document.getElementById('view-annual-income');
    if (annIncEl) {
      annIncEl.textContent = user.annual_income ? (typeof formatINR === 'function' ? formatINR(user.annual_income) + ' / yr' : '₹' + user.annual_income) : 'Not Specified';
    }

  } catch (err) {
    console.error('Failed to populate profile details:', err);
  }
}

function updateIdMask() {
  const el = document.getElementById('view-id-number');
  const btn = document.getElementById('btn-toggle-id');
  if (!_rawIdNumber) {
    if (el) el.textContent = 'Not Verified';
    if (btn) btn.style.display = 'none';
    return;
  }
  if (_isIdRevealed) {
    if (el) el.textContent = _rawIdNumber;
    if (btn) btn.textContent = 'Hide';
  } else {
    const len = _rawIdNumber.length;
    const visible = len > 4 ? _rawIdNumber.slice(-4) : _rawIdNumber;
    if (el) el.textContent = '•••• •••• ' + visible;
    if (btn) btn.textContent = 'Show';
  }
}

function toggleIdMask() {
  _isIdRevealed = !_isIdRevealed;
  updateIdMask();
}

function togglePassVisibility(inputId, btnIconId) {
  const input = document.getElementById(inputId);
  const icon = document.getElementById(btnIconId);
  if (!input || !icon) return;
  if (input.type === 'password') {
    input.type = 'text';
    icon.textContent = 'visibility_off';
  } else {
    input.type = 'password';
    icon.textContent = 'visibility';
  }
}

async function saveProfile() {
  const viewNameEl = document.getElementById('view-full-name');
  const name = viewNameEl ? viewNameEl.textContent.trim() : (typeof UserState !== 'undefined' ? UserState.name : '');
  const emailInput = document.getElementById('edit-email');
  const phoneInput = document.getElementById('edit-phone');
  const saveBtn = document.getElementById('btn-save-profile');
  
  const email = emailInput ? emailInput.value.trim() : '';
  const phone = phoneInput ? phoneInput.value.trim() : '';
  
  if (!email) { if (typeof showToast === 'function') showToast('Email address is required', 'error'); return; }
  if (!phone) { if (typeof showToast === 'function') showToast('Mobile phone number is required', 'error'); return; }

  if (saveBtn) {
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="material-symbols-outlined text-sm animate-spin">sync</span><span>Saving...</span>';
  }

  try {
    const formData = new FormData();
    formData.append('full_name', name);
    formData.append('phone', phone);
    formData.append('email', email);

    const res = await window.api.post('/user/profile', formData, true);
    if (res && res.success) {
      if (typeof showToast === 'function') showToast('Contact details updated successfully!', 'success');
      if (typeof fetchUserSession === 'function') await fetchUserSession();
    } else {
      if (typeof showToast === 'function') showToast((res && res.message) ? res.message : 'Failed to update profile', 'error');
    }
  } catch (err) {
    if (typeof showToast === 'function') showToast('Connection error while updating profile', 'error');
  } finally {
    if (saveBtn) {
      saveBtn.disabled = false;
      saveBtn.innerHTML = '<span class="material-symbols-outlined text-sm">save</span><span>Save Contact Details</span>';
    }
  }
}

async function updatePassword() {
  const currentPass = document.getElementById('current-pass')?.value || '';
  const newPass = document.getElementById('new-pass')?.value || '';
  const confirmPass = document.getElementById('confirm-pass')?.value || '';
  const btn = document.getElementById('btn-update-password');

  if (!currentPass) { if (typeof showToast === 'function') showToast('Please enter your current password', 'error'); return; }
  if (!newPass) { if (typeof showToast === 'function') showToast('Please enter a new password', 'error'); return; }
  if (newPass.length < 8) { if (typeof showToast === 'function') showToast('New password must be at least 8 characters', 'error'); return; }
  if (newPass !== confirmPass) { if (typeof showToast === 'function') showToast('New passwords do not match', 'error'); return; }

  if (btn) {
    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined text-sm animate-spin">sync</span><span>Updating...</span>';
  }

  try {
    const formData = new FormData();
    formData.append('current_password', currentPass);
    formData.append('new_password', newPass);
    formData.append('confirm_password', confirmPass);

    let res;
    if (window.api && typeof window.api.post === 'function') {
      res = await window.api.post('/user/security/change-password', formData, true);
    } else {
      const csrf = window.csrfToken || '';
      if (csrf) formData.append('csrf_token', csrf);
      const rawRes = await fetch('/Finzo/public/api/v1/user/security/change-password', {
        method: 'POST',
        credentials: 'same-origin',
        headers: csrf ? { 'X-CSRF-Token': csrf } : {},
        body: formData
      });
      res = await rawRes.json();
    }

    if (res && res.success) {
      if (typeof showToast === 'function') showToast('Password updated successfully!', 'success');
      document.getElementById('current-pass').value = '';
      document.getElementById('new-pass').value = '';
      document.getElementById('confirm-pass').value = '';
    } else {
      if (typeof showToast === 'function') showToast((res && res.message) ? res.message : 'Failed to update password', 'error');
    }
  } catch (err) {
    if (typeof showToast === 'function') showToast('Connection error while updating password', 'error');
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.innerHTML = '<span class="material-symbols-outlined text-sm">lock_reset</span><span>Update Password</span>';
    }
  }
}
