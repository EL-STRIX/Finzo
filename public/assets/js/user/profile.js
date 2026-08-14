(async function initProfile() {
  await fetchUserSession();
  
  buildSidebar('profile'); 
  buildNavbar('Settings','My Profile');
  
  document.getElementById('edit-name').value = UserState.name;
  document.getElementById('edit-phone').value = UserState.phone;
  document.getElementById('edit-email').value = UserState.email;
  document.getElementById('profile-name').textContent = UserState.name;
  document.getElementById('info-customer-id').textContent = UserState.customerId || 'FNZ-USR-PENDING';
  
  const circle = document.getElementById('avatar-circle');
  if (UserState.avatarUrl) {
    circle.style.backgroundImage = `url(${UserState.avatarUrl})`;
    circle.style.backgroundSize = 'cover';
    circle.textContent = '';
  } else {
    circle.innerHTML = `<span class="text-primary-base">${UserState.initials}</span>`;
  }
  
  document.getElementById('info-acc-no').textContent = UserState.accountNo || 'NOT ASSIGNED';
  
  if (UserState.isActive) {
    const realBalance = formatINR(UserState.balance);
    const balEl = document.getElementById('info-balance');
    if (UserState.isBalanceUnlocked) {
      balEl.textContent = realBalance;
    } else {
      balEl.textContent = '₹ ••••';
      balEl.setAttribute('data-real-value', realBalance);
    }
  } else {
    document.getElementById('info-balance').textContent = '₹0.00';
  }
  
  const sMap = {pending:'ds-badge-warning',under_review:'ds-badge-info',active:'ds-badge-success',rejected:'ds-badge-danger'};
  const sLabel = {pending:'PENDING',under_review:'IN REVIEW',active:'ACTIVE',rejected:'REJECTED'};
  document.getElementById('info-status').innerHTML = `<span class="ds-badge ${sMap[UserState.status]||'ds-badge-warning'}">${sLabel[UserState.status]||'OFFLINE'}</span>`;
})();
  async function saveProfile(){
    const name=document.getElementById('edit-name').value.trim();
    const phone=document.getElementById('edit-phone').value.trim();
    const email=document.getElementById('edit-email').value.trim();
    if(!email){showToast('Email is required','error');return;}
    
    let csrfToken = '';
    try {
      const tokenRes = await fetch('/Finzo/api/auth/get-csrf-token.php');
      const tokenData = await tokenRes.json();
      csrfToken = tokenData.csrf_token || '';
    } catch (e) {
      console.warn('Failed to fetch CSRF token', e);
    }
    
    fetch('/Finzo/api/user/profile.php',{
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      credentials:'same-origin',
      body:'full_name='+encodeURIComponent(name)+'&phone='+encodeURIComponent(phone)+'&email='+encodeURIComponent(email)+'&csrf_token='+encodeURIComponent(csrfToken)
    })
    .then(r=>r.json())
    .then(data=>{
      if(data.success){
        showToast('Profile updated successfully','success');
      } else {
        showToast(data.message||'Update failed','error');
      }
    })
    .catch(()=>showToast('Connection error','error'));
  }
