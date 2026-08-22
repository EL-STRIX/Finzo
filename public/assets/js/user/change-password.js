(async function initPage() {
  await fetchUserSession();
  buildSidebar('change-password'); 
  buildNavbar('Security','Change Password');
})();

function togglePw(id, eyeId) {
  const i = document.getElementById(id);
  const e = document.getElementById(eyeId);
  if(i.type === 'password'){
    i.type = 'text';
    e.textContent = 'visibility_off';
  } else {
    i.type = 'password';
    e.textContent = 'visibility';
  }
}

function checkStrength() {
  const pw = document.getElementById('new-pw').value;
  const fill = document.getElementById('strength-fill');
  const label = document.getElementById('strength-label');
  let score = 0;
  if (pw.length >= 8) score++;
  if (/[A-Z]/.test(pw)) score++;
  if (/[0-9]/.test(pw)) score++;
  if (/[^A-Za-z0-9]/.test(pw)) score++;
  
  const configs = {
    0: { w: '0%', c: 'bg-danger-base', l: '', tc: 'text-danger-base' },
    1: { w: '25%', c: 'bg-danger-base', l: 'Weak', tc: 'text-danger-base' },
    2: { w: '50%', c: 'bg-warning-base', l: 'Fair', tc: 'text-warning-base' },
    3: { w: '75%', c: 'bg-warning-base', l: 'Good', tc: 'text-warning-base' },
    4: { w: '100%', c: 'bg-success-base', l: 'Strong', tc: 'text-success-base' }
  };
  
  const cfg = configs[score];
  fill.className = `h-full transition-all duration-300 ${cfg.c}`;
  fill.style.width = cfg.w;
  label.textContent = cfg.l;
  label.className = `text-[10px] font-bold ${cfg.tc}`;
}

function checkMatch() {
  const np = document.getElementById('new-pw').value;
  const cp = document.getElementById('confirm-pw').value;
  const err = document.getElementById('match-error');
  if (cp && np !== cp) {
    err.classList.remove('hidden');
  } else {
    err.classList.add('hidden');
  }
}

async function updatePassword() {
  const cur = document.getElementById('current-pw').value;
  const np = document.getElementById('new-pw').value;
  const cp = document.getElementById('confirm-pw').value;
  if (!cur || !np || !cp) { showToast('Please fill in all fields', 'error'); return; }
  if (np.length < 8) { showToast('Password must be at least 8 characters', 'error'); return; }
  if (np !== cp) { showToast('Passwords do not match', 'error'); return; }

  let csrfToken = '';
  try {
    const tokenRes = await fetch('/Finzo/api/auth/get-csrf-token.php');
    const tokenData = await tokenRes.json();
    csrfToken = tokenData.csrf_token || '';
  } catch (e) { }

  const btn = document.getElementById('update-btn');
  btn.disabled = true;
  btn.innerHTML = '<span class="material-symbols-outlined animate-spin text-lg">sync</span> Updating...';
  
  try {
    const res = await fetch('/Finzo/api/user/change-password.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      credentials: 'same-origin',
      body: 'current_password=' + encodeURIComponent(cur) + '&new_password=' + encodeURIComponent(np) + '&confirm_password=' + encodeURIComponent(cp) + '&csrf_token=' + encodeURIComponent(csrfToken)
    });
    const data = await res.json();
    if (data.success) {
      showToast('Password updated successfully! Redirecting...', 'success');
      setTimeout(() => { window.location.href = '/Finzo/views/auth/login.php'; }, 2000);
    } else {
      showToast(data.message || 'Update failed', 'error');
      if (data.errors) {
        const firstErr = Object.values(data.errors)[0];
        if (firstErr) showToast(firstErr, 'error');
      }
      btn.disabled = false;
      btn.innerHTML = 'Update Credentials';
    }
  } catch(e) {
    showToast('Connection error', 'error');
    btn.disabled = false;
    btn.innerHTML = 'Update Credentials';
  }
}
