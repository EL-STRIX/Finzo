let currentStep = 1;
const uploads = { profile: null, front: null, back: null };
const formValues = {};
// True when a rejected user is resubmitting — existing files on server are accepted
let isRejectedResubmit = false;

(async function() {
  await fetchUserSession();
  const s = UserState.status;
  if (s === 'active') { window.location.href = 'dashboard.php'; return; }
  if (s === 'under_review') { window.location.href = 'account-pending.php'; return; }
  
  if (s === 'rejected') {
    isRejectedResubmit = true;
    if (UserState.rejectionReason) {
      showToast('Your previous application was rejected: ' + UserState.rejectionReason, 'error');
    }
    // Pre-fill all fields with existing KYC data so user doesn't re-enter everything
    await prefillRejectedUserData();
  }
  
  await loadBranches();
  document.body.classList.add('ready');
})();

// Helper: set a finzo custom dropdown value by updating its hidden input and label
function setDropdownValue(hiddenInputId, value) {
  const input = document.getElementById(hiddenInputId);
  if (!input) return;
  input.value = value;
  // Find the matching option to update the visible label
  const container = input.closest('.finzo-dropdown');
  if (!container) return;
  const label = container.querySelector('.dropdown-label');
  const option = container.querySelector(`.finzo-dropdown-option[data-value="${CSS.escape(value)}"]`);
  if (label && option) {
    label.textContent = option.textContent;
    label.classList.remove('text-text-muted');
  }
}

async function prefillRejectedUserData() {
  try {
    let res;
    if (window.api && typeof window.api.get === 'function') {
      res = await window.api.get('/user/profile');
    } else {
      const r = await fetch('/Finzo/public/api/v1/user/profile', { credentials: 'same-origin' });
      res = await r.json();
    }
    if (!res || !res.success || !res.user) return;
    const u = res.user;

    // --- Step 1: Personal ---
    if (u.dob) { const el = document.getElementById('dob'); if (el) el.value = u.dob; }
    if (u.gender)          setDropdownValue('gender', u.gender);
    if (u.father_name)     { const el = document.getElementById('father-name'); if (el) el.value = u.father_name; }
    if (u.mother_name)     { const el = document.getElementById('mother-name'); if (el) el.value = u.mother_name; }
    if (u.marital_status)  setDropdownValue('marital-status', u.marital_status);
    if (u.nationality)     { const el = document.getElementById('nationality'); if (el) el.value = u.nationality; }
    if (u.occupation)      { const el = document.getElementById('occupation'); if (el) el.value = u.occupation; }
    if (u.annual_income)   setDropdownValue('income', u.annual_income);
    if (u.source_of_income) setDropdownValue('source-of-income', u.source_of_income);

    // Show existing profile photo preview
    if (u.profile_picture) {
      let src = u.profile_picture;
      if (!src.startsWith('http')) {
        const clean = src.replace(/^\/+/, '');
        src = clean.startsWith('uploads/') ? '/Finzo/public/' + clean : '/Finzo/public/uploads/' + clean;
      }
      const wrap = document.getElementById('profile-preview-wrap');
      const img  = document.getElementById('profile-preview');
      if (wrap && img) { wrap.classList.add('hidden'); img.src = src; img.classList.remove('hidden'); }
      const box = document.getElementById('profile-box');
      if (box) { box.classList.add('border-success-base', 'border-solid'); box.classList.remove('border-dashed'); }
    }

    // --- Step 2: Address ---
    if (u.street)  { const el = document.getElementById('street');  if (el) el.value = u.street; }
    if (u.city)    { const el = document.getElementById('city');    if (el) el.value = u.city; }
    if (u.state)   { const el = document.getElementById('state');   if (el) el.value = u.state; }
    if (u.pincode) { const el = document.getElementById('pincode'); if (el) el.value = u.pincode; }

    // --- Step 3: Documents ---
    if (u.id_type)   setDropdownValue('id-type', u.id_type);
    if (u.id_number) { const el = document.getElementById('id-number'); if (el) el.value = u.id_number; }

    // Show "existing" badge on document upload boxes so user knows files are on file
    if (u.id_front_path) {
      const wrap = document.getElementById('front-preview-wrap');
      if (wrap) wrap.innerHTML = '<span class="material-symbols-outlined text-2xl text-success-base mb-1">check_circle</span><div class="text-xs font-bold text-success-base">Existing file on server</div><div class="text-[10px] text-text-muted mt-0.5">Upload new to replace</div>';
      const box = document.getElementById('front-box');
      if (box) { box.classList.add('border-success-base', 'border-solid'); box.classList.remove('border-dashed'); }
    }
    if (u.id_back_path) {
      const wrap = document.getElementById('back-preview-wrap');
      if (wrap) wrap.innerHTML = '<span class="material-symbols-outlined text-2xl text-success-base mb-1">check_circle</span><div class="text-xs font-bold text-success-base">Existing file on server</div><div class="text-[10px] text-text-muted mt-0.5">Upload new to replace</div>';
      const box = document.getElementById('back-box');
      if (box) { box.classList.add('border-success-base', 'border-solid'); box.classList.remove('border-dashed'); }
    }
  } catch(e) {
    console.warn('Failed to prefill rejected user data:', e);
  }
}


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
      const options = data.branches.map(b => {
        const code = b.branch_code ? ` (${b.branch_code})` : '';
        const state = b.state ? ` — ${b.state}` : '';
        return `<div class="finzo-dropdown-option" data-value="${b.id}">${b.name}${code}${state}</div>`;
      }).join('');
      branchMenu.innerHTML = options;
      if (branchLabel && (!branchLabel.textContent || branchLabel.textContent.includes('No branches') || branchLabel.textContent.includes('Loading'))) {
        branchLabel.textContent = 'Select local branch';
      }
    } else if (branchMenu && (!branchMenu.children.length || branchMenu.querySelector('[data-value=""]'))) {
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

function goToStep(targetStep) {
  if (targetStep === currentStep || targetStep < 1 || targetStep > 4) return;
  const currentEl = document.getElementById('step' + currentStep);
  const targetEl = document.getElementById('step' + targetStep);
  if (currentEl) currentEl.classList.remove('active');
  if (targetEl) targetEl.classList.add('active');
  currentStep = targetStep;
  updateProgress();
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function validateStep(step) {
  if (step === 1) {
    const profileFile = uploads.profile || document.getElementById('profile-input')?.files?.[0];
    if (!profileFile && !isRejectedResubmit) { showToast('Profile photo is required', 'error'); return false; }
    
    const fieldMap = [
      { id: 'dob', label: 'Date of Birth' },
      { id: 'gender', label: 'Gender' },
      { id: 'father-name', label: "Father's Legal Name" },
      { id: 'mother-name', label: "Mother's Legal Name" },
      { id: 'marital-status', label: 'Marital Status' },
      { id: 'nationality', label: 'Nationality' },
      { id: 'occupation', label: 'Occupation' },
      { id: 'income', label: 'Annual Income Range' },
      { id: 'source-of-income', label: 'Source of Income' }
    ];

    for (const f of fieldMap) {
      const el = document.getElementById(f.id);
      const v = el ? el.value.trim() : '';
      if (!v) { showToast(`${f.label} is required`, 'error'); return false; }
      if (f.id === 'dob') {
        const birthDate = new Date(v);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const m = today.getMonth() - birthDate.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) age--;
        if (isNaN(age) || age < 18) {
          showToast('You must be at least 18 years old', 'error');
          return false;
        }
      }
      formValues[f.id] = v;
    }
  } else if (step === 2) {
    const fieldMap = [
      { id: 'street', label: 'Street address' },
      { id: 'city', label: 'City' },
      { id: 'state', label: 'State' },
      { id: 'pincode', label: 'PIN code' }
    ];
    for (const f of fieldMap) {
      const el = document.getElementById(f.id);
      const v = el ? el.value.trim() : '';
      if (!v) { showToast(`${f.label} is required`, 'error'); return false; }
      if (f.id === 'pincode' && !/^\d{6}$/.test(v)) { showToast('Please enter a valid 6-digit PIN code', 'error'); return false; }
      formValues[f.id] = v;
    }
  } else if (step === 3) {
    const idType = document.getElementById('id-type')?.value?.trim();
    const idNum = document.getElementById('id-number')?.value?.trim();
    if (!idType) { showToast('Please select Document Type', 'error'); return false; }
    if (!idNum) { showToast('Document Number is required', 'error'); return false; }
    
    const frontFile = uploads.front || document.getElementById('front-input')?.files?.[0];
    const backFile = uploads.back || document.getElementById('back-input')?.files?.[0];
    if ((!frontFile || !backFile) && !isRejectedResubmit) { showToast('Both front and back images of ID document are required', 'error'); return false; }
    
    formValues['id-type'] = idType;
    formValues['id-number'] = idNum;
  }
  return true;
}

function nextStep(step) {
  if (!validateStep(step)) return;
  document.getElementById('step' + step)?.classList.remove('active');
  currentStep = step + 1;
  document.getElementById('step' + currentStep)?.classList.add('active');
  updateProgress();
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function prevStep(step) {
  document.getElementById('step' + step)?.classList.remove('active');
  currentStep = step - 1;
  document.getElementById('step' + currentStep)?.classList.add('active');
  updateProgress();
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function updateProgress() {
  const progressBar = document.getElementById('progress-bar');
  if (progressBar) progressBar.style.width = (currentStep * 25) + '%';
  for (let i = 1; i <= 4; i++) {
    const sc = document.getElementById('sc' + i);
    const sl = document.getElementById('sl' + i);
    const line = document.getElementById('line' + (i - 1));
    
    if (!sc || !sl) continue;

    if (i < currentStep) {
      // Done
      sc.className = 'w-8 h-8 rounded-full bg-success-base text-white flex items-center justify-center border-4 border-[var(--color-surface-subtle)] transition-colors duration-300';
      sc.innerHTML = '<span class="material-symbols-outlined text-[16px]">check</span>';
      sl.className = 'text-[10px] font-bold text-success-base uppercase tracking-widest transition-colors duration-300';
      if (line) line.className = 'flex-1 h-0.5 bg-success-base -mt-6 transition-colors duration-300';
    } else if (i === currentStep) {
      // Active
      sc.className = 'w-8 h-8 rounded-full bg-primary-base text-white flex items-center justify-center border-4 border-[var(--color-surface-subtle)] transition-colors duration-300 shadow-md';
      sc.innerHTML = i === 1 ? '<span class="material-symbols-outlined text-[16px]">person</span>' : i;
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
  const profileFile = uploads.profile || document.getElementById('profile-input')?.files?.[0];
  const frontFile = uploads.front || document.getElementById('front-input')?.files?.[0];
  const backFile = uploads.back || document.getElementById('back-input')?.files?.[0];

  if (!profileFile && !isRejectedResubmit) {
    showToast('Profile photo is required (Step 1)', 'error');
    goToStep(1);
    return;
  }

  // Verify all steps are satisfied
  if (!validateStep(1)) { goToStep(1); return; }
  if (!validateStep(2)) { goToStep(2); return; }
  if (!validateStep(3)) { goToStep(3); return; }

  const type = document.querySelector('input[name="account-type"]:checked')?.value || 'savings';
  const branchEl = document.getElementById('branch');
  const branch = branchEl ? branchEl.value.trim() : '';
  const termsEl = document.getElementById('terms');
  const terms = termsEl ? termsEl.checked : false;

  if (!branch) {
    showToast('Please select a servicing branch', 'error');
    const branchContainer = document.getElementById('branch-dropdown-container');
    if (branchContainer) branchContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
    return;
  }

  if (!terms) {
    showToast('You must agree to the Terms of Service & Privacy Policy', 'error');
    if (termsEl) termsEl.focus();
    return;
  }

  const btn = document.getElementById('submit-btn');
  if (btn) {
    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined animate-spin text-lg">sync</span> <span>Processing...</span>';
  }

  try {
    const formData = new FormData();
    // Gather all fields directly from DOM
    formData.append('dob', document.getElementById('dob')?.value?.trim() || '');
    formData.append('gender', document.getElementById('gender')?.value?.trim() || '');
    formData.append('father_name', document.getElementById('father-name')?.value?.trim() || '');
    formData.append('mother_name', document.getElementById('mother-name')?.value?.trim() || '');
    formData.append('marital_status', document.getElementById('marital-status')?.value?.trim() || '');
    formData.append('nationality', document.getElementById('nationality')?.value?.trim() || 'Indian');
    formData.append('occupation', document.getElementById('occupation')?.value?.trim() || '');
    formData.append('annual_income', document.getElementById('income')?.value?.trim() || '');
    formData.append('source_of_income', document.getElementById('source-of-income')?.value?.trim() || '');
    formData.append('street', document.getElementById('street')?.value?.trim() || '');
    formData.append('city', document.getElementById('city')?.value?.trim() || '');
    formData.append('state', document.getElementById('state')?.value?.trim() || '');
    formData.append('pincode', document.getElementById('pincode')?.value?.trim() || '');
    formData.append('id_type', document.getElementById('id-type')?.value?.trim() || '');
    formData.append('id_number', document.getElementById('id-number')?.value?.trim() || '');
    formData.append('account_type', type);
    formData.append('branch', branch);
    formData.append('initial_deposit', '500');
    formData.append('txn_pin', '1234');

    if (profileFile) formData.append('profile_photo', profileFile);
    if (frontFile) formData.append('id_front', frontFile);
    if (backFile) formData.append('id_back', backFile);

    let data;
    if (window.api && typeof window.api.post === 'function') {
      data = await window.api.post('/user/profile/complete', formData, true);
    } else {
      const csrf = window.csrfToken || (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');
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
      showToast('Profile submitted successfully! Redirecting...', 'success');
      setTimeout(() => {
        window.location.href = 'account-pending.php';
      }, 700);
    } else {
      let msg = (data && data.message) ? data.message : 'Submission failed';
      if (data && data.errors && typeof data.errors === 'object') {
        const errorList = Object.values(data.errors);
        if (errorList.length > 0) {
          msg = errorList[0];
        }
      }
      showToast(msg, 'error');
      if (btn) {
        btn.disabled = false;
        btn.innerHTML = '<span class="material-symbols-outlined text-lg">done_all</span> <span>Submit Application</span>';
      }
    }
  } catch (e) {
    console.error('Submission error:', e);
    showToast('Connection error. Please try again.', 'error');
    if (btn) {
      btn.disabled = false;
      btn.innerHTML = '<span class="material-symbols-outlined text-lg">done_all</span> <span>Submit Application</span>';
    }
  }
}
