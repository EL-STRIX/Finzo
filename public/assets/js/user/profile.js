let _rawIdNumber = '';
let _isIdRevealed = false;

(async function initProfile() {
  await fetchUserSession();
  
  buildSidebar('profile'); 
  buildNavbar('Settings', 'My Profile');

  try {
    const res = await window.api.get('/user/profile');
    if (res && res.success && res.user) {
      const user = res.user;
      const account = res.account || {};

      // 1. Identity Card
      document.getElementById('profile-name').textContent = user.full_name || UserState.name || 'User Profile';
      document.getElementById('info-customer-id').textContent = 'Customer ID: ' + (user.customer_id || UserState.customerId || 'FNZ-USR-PENDING');

      const circle = document.getElementById('avatar-circle');
      if (user.profile_picture || UserState.avatarUrl) {
        let photoSrc = user.profile_picture || UserState.avatarUrl;
        if (!photoSrc.startsWith('http') && !photoSrc.startsWith('data:')) {
          const clean = photoSrc.replace(/^\/+/, '');
          photoSrc = clean.startsWith('Finzo/public/') ? '/' + clean : '/Finzo/public/uploads/' + clean;
        }
        circle.innerHTML = `<img src="${photoSrc}" alt="${user.full_name}" class="w-full h-full object-cover rounded-full">`;
      } else {
        const initials = user.full_name ? user.full_name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase() : 'U';
        circle.innerHTML = `<span class="text-primary-base font-bold text-2xl">${initials}</span>`;
      }

      const sMap = { pending: 'badge-warning', under_review: 'badge-info', active: 'badge-success', rejected: 'badge-danger' };
      const sLabel = { pending: 'Pending KYC', under_review: 'In Review', active: 'Active Account', rejected: 'Action Required' };
      const statusText = user.status || UserState.status || 'pending';
      document.getElementById('info-status').innerHTML = `<span class="badge ${sMap[statusText] || 'badge-warning'} uppercase font-bold text-[10px]">${sLabel[statusText] || statusText}</span>`;
      
      if (user.created_at) {
        document.getElementById('info-member-since').textContent = 'Member since: ' + new Date(user.created_at).toLocaleDateString('en-IN', { month: 'short', year: 'numeric' });
      }

      // 2. Account Ledger
      document.getElementById('info-acc-no').textContent = account.account_no || 'Pending Allocation';
      document.getElementById('info-acc-type').textContent = account.account_type || 'Savings Account';
      document.getElementById('info-interest-rate').textContent = (account.interest_rate || 4.5) + '% p.a.';
      document.getElementById('info-daily-limit').textContent = formatINR(account.daily_limit || 50000);
      document.getElementById('info-balance').textContent = formatINR(account.balance || 0);

      // 3. Personal & Demographics
      document.getElementById('view-full-name').textContent = user.full_name || 'Not Specified';
      document.getElementById('view-dob').textContent = user.dob ? new Date(user.dob).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' }) : 'Not Provided';
      document.getElementById('view-gender').textContent = user.gender || 'Not Specified';
      document.getElementById('view-nationality').textContent = user.nationality || 'Indian';
      document.getElementById('view-father-name').textContent = user.father_name || 'Not Provided';
      document.getElementById('view-mother-name').textContent = user.mother_name || 'Not Provided';
      document.getElementById('view-marital-status').textContent = user.marital_status || 'Single';

      // 4. Contact & Address
      document.getElementById('edit-email').value = user.email || UserState.email || '';
      document.getElementById('edit-phone').value = user.phone || UserState.phone || '';
      document.getElementById('view-street').textContent = user.street || 'Not Provided';
      document.getElementById('view-city').textContent = user.city || 'Not Provided';
      document.getElementById('view-state').textContent = user.state || 'Not Provided';
      document.getElementById('view-pincode').textContent = user.pincode || '—';
      document.getElementById('view-country').textContent = user.country || 'India';

      // 5. Identity Verification
      document.getElementById('view-id-type').textContent = (user.id_type || 'Government ID').toUpperCase();
      _rawIdNumber = user.id_number || '';
      updateIdMask();

      // 6. Employment & Financial
      document.getElementById('view-occupation').textContent = user.occupation || 'Not Specified';
      document.getElementById('view-source-income').textContent = user.source_of_income || 'Salary';
      document.getElementById('view-annual-income').textContent = user.annual_income ? formatINR(user.annual_income) + ' / yr' : 'Not Specified';
    }
  } catch (err) {
    console.error('Failed to load profile details:', err);
  }
})();

function updateIdMask() {
  const el = document.getElementById('view-id-number');
  const btn = document.getElementById('btn-toggle-id');
  if (!_rawIdNumber) {
    el.textContent = 'Not Verified';
    if (btn) btn.style.display = 'none';
    return;
  }
  if (_isIdRevealed) {
    el.textContent = _rawIdNumber;
    if (btn) btn.textContent = 'Hide';
  } else {
    const len = _rawIdNumber.length;
    const visible = len > 4 ? _rawIdNumber.slice(-4) : _rawIdNumber;
    el.textContent = '•••• •••• ' + visible;
    if (btn) btn.textContent = 'Show';
  }
}

function toggleIdMask() {
  _isIdRevealed = !_isIdRevealed;
  updateIdMask();
}

async function saveProfile() {
  const name = document.getElementById('view-full-name').textContent.trim();
  const phone = document.getElementById('edit-phone').value.trim();
  const email = document.getElementById('edit-email').value.trim();
  
  if (!email) { showToast('Email address is required', 'error'); return; }
  if (!phone) { showToast('Mobile phone number is required', 'error'); return; }

  try {
    const formData = new FormData();
    formData.append('full_name', name);
    formData.append('phone', phone);
    formData.append('email', email);

    const res = await window.api.post('/user/profile', formData, true);
    if (res && res.success) {
      showToast('Contact details updated successfully!', 'success');
      await fetchUserSession();
    } else {
      showToast(res.message || 'Failed to update profile', 'error');
    }
  } catch (err) {
    showToast('Connection error while updating profile', 'error');
  }
}
