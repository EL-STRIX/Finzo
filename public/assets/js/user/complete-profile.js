let currentStep = 1;
const uploads = { profile: null, front: null, back: null };
const formValues = {};

(async function() {
  await fetchUserSession();
  const s = UserState.status;
  if (s === 'active') { window.location.href = 'dashboard.php'; return; }
  if (s === 'under_review') { window.location.href = 'account-pending.php'; return; }
  
  if (s === 'rejected') {
    if (UserState.rejectionReason) {
      showToast('Your previous application was rejected: ' + UserState.rejectionReason, 'error');
    }
  }
  
  await loadBranches();
  document.body.classList.add('ready');
})();

async function loadBranches() {
  try {
    let data;
    if (window.api && typeof window.api.get === 'function') {
      data = await window.api.get('/public/branches');
    } else {
      const res = await fetch('/Finzo/public/api/v1/public/branches');
      data = await res.json();
    }
    
    const branchMenu = document.getElementById('branch-options');
    const branchLabel = document.querySelector('#branch-dropdown-container .dropdown-label');
    
    if (branchMenu && data && data.success && Array.isArray(data.branches) && data.branches.length > 0) {
      const options = data.branches.map(b => 
        `<div class="finzo-dropdown-option" data-value="${b.id}">${b.name} (${b.ifsc || b.branch_code || ''})</div>`
      ).join('');
      branchMenu.innerHTML = options;
      if (branchLabel) branchLabel.textContent = 'Select local branch';
    } else if (branchMenu) {
      branchMenu.innerHTML = '<div class="finzo-dropdown-option" data-value="">No branches available</div>';
      if (branchLabel) branchLabel.textContent = 'No branches available';
    }
  } catch(e) {
    console.warn('Failed to load branches', e);
  }
}

function handleUpload(input, boxId, previewId) {
  if (input.files && input.files[0]) {
    const file = input.files[0];
    if (file.size > 5 * 1024 * 1024) { showToast('File too large (Max 5MB)', 'error'); return; }
    
    const key = input.id.split('-')[0];
    uploads[key] = file;
    
    const reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById(previewId + '-wrap').classList.add('hidden');
      const img = document.getElementById(previewId);
      img.src = e.target.result;
      img.classList.remove('hidden');
      document.getElementById(boxId).classList.add('border-success-base', 'border-solid');
      document.getElementById(boxId).classList.remove('border-dashed');
    };
    reader.readAsDataURL(file);
  }
}

function validateStep(step) {
  if (step === 1) {
    if (!uploads.profile) { showToast('Profile photo is required', 'error'); return false; }
    const flds = ['dob','gender','father-name','mother-name','marital-status','nationality','occupation','income','source-of-income'];
    for(const id of flds) {
      const v = document.getElementById(id).value;
      if (!v) { showToast(`Please complete all fields in Step 1`, 'error'); return false; }
      formValues[id] = v;
    }
  } else if (step === 2) {
    const flds = ['street','city','state','pincode'];
    for(const id of flds) {
      const v = document.getElementById(id).value;
      if (!v) { showToast(`Please complete all address fields`, 'error'); return false; }
      if (id === 'pincode' && !/^\d{6}$/.test(v)) { showToast('Invalid PIN Code', 'error'); return false; }
      formValues[id] = v;
    }
  } else if (step === 3) {
    const idType = document.getElementById('id-type').value;
    const idNum = document.getElementById('id-number').value;
    if (!idType || !idNum) { showToast('Identity details are required', 'error'); return false; }
    if (!uploads.front || !uploads.back) { showToast('Both sides of ID document are required', 'error'); return false; }
    formValues['id-type'] = idType;
    formValues['id-number'] = idNum;
  }
  return true;
}

function nextStep(step) {
  if (!validateStep(step)) return;
  document.getElementById('step' + step).classList.remove('active');
  currentStep = step + 1;
  document.getElementById('step' + currentStep).classList.add('active');
  updateProgress();
}

function prevStep(step) {
  document.getElementById('step' + step).classList.remove('active');
  currentStep = step - 1;
  document.getElementById('step' + currentStep).classList.add('active');
  updateProgress();
}

function updateProgress() {
  document.getElementById('progress-bar').style.width = (currentStep * 25) + '%';
  for (let i = 1; i <= 4; i++) {
    const sc = document.getElementById('sc'+i);
    const sl = document.getElementById('sl'+i);
    const line = document.getElementById('line'+(i-1));
    
    if (i < currentStep) {
      // Done
      sc.className = 'w-8 h-8 rounded-full bg-success-base text-white flex items-center justify-center border-4 border-[var(--color-surface-subtle)] transition-colors duration-300';
      sc.innerHTML = '<span class="material-symbols-outlined text-[16px]">check</span>';
      sl.className = 'text-[10px] font-bold text-success-base uppercase tracking-widest transition-colors duration-300';
      if (line) line.className = 'flex-1 h-0.5 bg-success-base -mt-6 transition-colors duration-300';
    } else if (i === currentStep) {
      // Active
      sc.className = 'w-8 h-8 rounded-full bg-primary-base text-white flex items-center justify-center border-4 border-[var(--color-surface-subtle)] transition-colors duration-300 shadow-md';
      sc.innerHTML = i===1?'<span class="material-symbols-outlined text-[16px]">person</span>' : i;
      sl.className = 'text-[10px] font-bold text-primary-base uppercase tracking-widest transition-colors duration-300';
      if (line) line.className = 'flex-1 h-0.5 bg-primary-base -mt-6 transition-colors duration-300';
    } else {
      // Inactive
      sc.className = 'w-8 h-8 rounded-full bg-[var(--color-surface-hover)] text-[var(--color-text-disabled)] font-bold text-xs flex items-center justify-center border-4 border-[var(--color-surface-subtle)] transition-colors duration-300';
      sc.innerHTML = i;
      sl.className = 'text-[10px] font-bold text-[var(--color-text-disabled)] uppercase tracking-widest transition-colors duration-300';
      if (line) line.className = 'flex-1 h-0.5 bg-[var(--color-border-main)] -mt-6 transition-colors duration-300';
    }
  }
}

async function submitProfile() {
  const type = document.querySelector('input[name="account-type"]:checked')?.value || 'savings';
  const branch = document.getElementById('branch').value;
  const terms = document.getElementById('terms').checked;
  
  if (!branch) { showToast('Please select a preferred branch', 'error'); return; }
  if (!terms) { showToast('You must agree to the Terms of Service', 'error'); return; }
  
  formValues['account-type'] = type;
  formValues['branch'] = branch;
  
  const btn = document.getElementById('submit-btn');
  btn.disabled = true;
  btn.innerHTML = '<span class="material-symbols-outlined animate-spin text-lg">sync</span> Processing...';

  try {
    const formData = new FormData();
    Object.keys(formValues).forEach(k => formData.append(k, formValues[k]));
    
    if (uploads.profile) formData.append('profile_photo', uploads.profile);
    if (uploads.front) formData.append('id_front', uploads.front);
    if (uploads.back) formData.append('id_back', uploads.back);

    let data;
    if (window.api && typeof window.api.post === 'function') {
      data = await window.api.post('/user/profile/complete', formData, true);
    } else {
      const csrf = window.csrfToken || '';
      if (csrf) formData.append('csrf_token', csrf);
      const res = await fetch('/Finzo/public/api/v1/user/profile/complete', {
        method: 'POST',
        credentials: 'same-origin',
        headers: csrf ? { 'X-CSRF-Token': csrf } : {},
        body: formData
      });
      data = await res.json();
    }
    
    if (data && data.success) {
      showToast('Profile submitted successfully!', 'success');
      setTimeout(() => {
        window.location.href = 'account-pending.php';
      }, 600);
    } else {
      showToast((data && data.message) ? data.message : 'Submission failed', 'error');
      btn.disabled = false;
      btn.innerHTML = '<span class="material-symbols-outlined text-[18px]">done_all</span> Submit Application';
    }
  } catch (e) {
    showToast('Connection error. Please try again.', 'error');
    btn.disabled = false;
    btn.innerHTML = '<span class="material-symbols-outlined text-[18px]">done_all</span> Submit Application';
  }
}
