let _rawIdNumber = '';
let _isIdRevealed = false;

function escapeHtml(str) {
  if (!str) return '';
  return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

document.addEventListener('DOMContentLoaded', initProfile);

async function initProfile() {
  try {
    if (typeof fetchUserSession === 'function') {
      await fetchUserSession();
    }
  } catch (err) {
    console.warn('fetchUserSession error in profile.js:', err);
  }
  
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

    // 1. Identity Summary Card
    const nameEl = document.getElementById('profile-name');
    if (nameEl) nameEl.textContent = user.full_name || (typeof UserState !== 'undefined' ? UserState.name : '') || 'Customer Profile';
    
    const custIdEl = document.getElementById('info-customer-id');
    if (custIdEl) custIdEl.textContent = 'Customer ID: ' + (user.customer_id || (typeof UserState !== 'undefined' ? UserState.customerId : '') || 'FNZ-USR-PENDING');

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
        circle.innerHTML = `<img src="${cleanSrc}" alt="${escapeHtml(user.full_name || 'Avatar')}" class="w-full h-full object-cover rounded-full">`;
      } else {
        const nameStr = user.full_name || (typeof UserState !== 'undefined' ? UserState.name : '') || 'User';
        const initials = nameStr.split(' ').filter(Boolean).map(n => n[0]).join('').substring(0, 2).toUpperCase() || 'U';
        circle.innerHTML = `<span class="text-primary-base font-bold text-2xl">${initials}</span>`;
      }
    }

    const sMap = { pending: 'badge-warning', under_review: 'badge-info', active: 'badge-success', rejected: 'badge-danger' };
    const sLabel = { pending: 'Pending KYC', under_review: 'In Review', active: 'Active Account', rejected: 'Action Required' };
    const statusText = user.status || (typeof UserState !== 'undefined' ? UserState.status : 'pending');
    const statusEl = document.getElementById('info-status');
    if (statusEl) {
      statusEl.innerHTML = `<span class="badge ${sMap[statusText] || 'badge-warning'} uppercase font-bold text-[10px]">${sLabel[statusText] || statusText}</span>`;
    }
    
    const sinceEl = document.getElementById('info-member-since');
    if (sinceEl) {
      if (user.created_at) {
        const d = new Date(user.created_at);
        sinceEl.textContent = 'Member since: ' + (!isNaN(d.getTime()) ? d.toLocaleDateString('en-IN', { month: 'short', year: 'numeric' }) : 'Recently');
      } else {
        sinceEl.textContent = 'Member since: Recently';
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
    if (viewNameEl) viewNameEl.textContent = user.full_name || (typeof UserState !== 'undefined' ? UserState.name : '') || 'Not Specified';

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
    if (emailInput) emailInput.value = user.email || (typeof UserState !== 'undefined' ? UserState.email : '') || '';

    const phoneInput = document.getElementById('edit-phone');
    if (phoneInput) phoneInput.value = user.phone || (typeof UserState !== 'undefined' ? UserState.phone : '') || '';

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

async function saveProfile() {
  const viewNameEl = document.getElementById('view-full-name');
  const name = viewNameEl ? viewNameEl.textContent.trim() : (typeof UserState !== 'undefined' ? UserState.name : '');
  const emailInput = document.getElementById('edit-email');
  const phoneInput = document.getElementById('edit-phone');
  
  const email = emailInput ? emailInput.value.trim() : '';
  const phone = phoneInput ? phoneInput.value.trim() : '';
  
  if (!email) { if (typeof showToast === 'function') showToast('Email address is required', 'error'); return; }
  if (!phone) { if (typeof showToast === 'function') showToast('Mobile phone number is required', 'error'); return; }

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
  }
}
