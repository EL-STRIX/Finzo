/* ═══════════════════════════════════════════
   FINZO — Staff Profile Management
   Clean Architecture & Vanilla JavaScript
   ═══════════════════════════════════════════ */

let profileData = null;
let csrfToken = '';

document.addEventListener('DOMContentLoaded', async function () {
    if (typeof fetchStaffSession === 'function') {
        const session = await fetchStaffSession();
        if (session && session.csrf_token) {
            csrfToken = session.csrf_token;
        }
    }

    if (!csrfToken) {
        csrfToken = window.csrfToken || (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')) || '';
    }

    await loadProfile();
    initEventListeners();
});

function displayToast(msg, type = 'success') {
    if (typeof window.showToast === 'function') {
        window.showToast(msg, type);
    } else {
        alert(msg);
    }
}

function formatDate(dateStr) {
    if (!dateStr || dateStr === '0000-00-00') return 'Not set';
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return dateStr;
    return d.toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
}

function maskIdNumber(num) {
    if (!num) return '—';
    const str = String(num).trim();
    if (str.length <= 4) return str;
    return '•'.repeat(Math.max(0, str.length - 4)) + str.slice(-4);
}

function resolveAssetUrl(path, subDir = 'staff-photos') {
    if (!path) return null;
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    const clean = path.replace(/^\/+/, '');
    if (clean.startsWith('Finzo/')) return '/' + clean;
    if (clean.startsWith('uploads/')) return '/Finzo/' + clean;
    return '/Finzo/uploads/' + subDir + '/' + clean;
}

async function loadProfile() {
    try {
        let data = null;
        if (window.api && typeof window.api.get === 'function') {
            data = await window.api.get('/staff-portal/profile');
        } else {
            const res = await fetch('/Finzo/api/v1/staff-portal/profile', {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }
            });
            data = await res.json();
        }

        if (!data || !data.success) {
            throw new Error(data?.message || 'Failed to load staff profile data');
        }

        profileData = data;
        renderProfile(data);
    } catch (err) {
        console.error('Profile load error:', err);
        displayToast('Failed to load profile: ' + (err.message || 'Server error'), 'error');
    }
}

function renderProfile(data) {
    const s = data.staff || {};
    const addr = data.address || {};
    const docs = data.documents || [];

    // Header Hero
    const nameEl = document.getElementById('profile-name');
    if (nameEl) nameEl.textContent = s.full_name || 'Staff Member';

    const desigEl = document.getElementById('profile-designation');
    if (desigEl) desigEl.textContent = s.designation || 'Staff';

    const deptEl = document.getElementById('profile-department');
    if (deptEl) deptEl.textContent = s.department || 'Teller Operations';

    const staffIdEl = document.getElementById('profile-staff-id');
    if (staffIdEl) staffIdEl.textContent = s.staff_id || '—';

    const branchEl = document.getElementById('profile-branch');
    if (branchEl) {
        let branchText = s.branch_name || 'Unassigned';
        if (s.branch_code) branchText += ` (${s.branch_code})`;
        branchEl.textContent = branchText;
    }

    const joinedEl = document.getElementById('profile-joined');
    if (joinedEl) joinedEl.textContent = formatDate(s.date_of_joining);

    // Avatar
    const avatarEl = document.getElementById('profile-avatar');
    if (avatarEl) {
        const photoUrl = s.profile_photo_url || resolveAssetUrl(s.profile_photo, 'staff-photos');
        if (photoUrl) {
            avatarEl.innerHTML = `<img src="${photoUrl}?v=${Date.now()}" alt="${s.full_name || 'Staff'}" class="w-full h-full object-cover rounded-2xl">`;
        } else {
            const initials = (s.full_name || 'ST')
                .split(' ')
                .filter(w => /^[a-zA-Z]/.test(w))
                .map(w => w[0])
                .join('')
                .substring(0, 2)
                .toUpperCase() || 'ST';
            avatarEl.innerHTML = `<span id="profile-avatar-text" class="text-2xl sm:text-3xl font-black text-amber-600 dark:text-amber-400 tracking-wider font-mono select-none">${initials}</span>`;
        }
    }

    // Personal Info
    const setText = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.textContent = val || 'Not set';
    };

    setText('info-name', s.full_name);
    setText('info-dob', formatDate(s.date_of_birth));
    setText('info-gender', s.gender);
    setText('info-email', s.personal_email);
    setText('info-phone', s.phone);

    // Emergency Contact
    setText('info-emergency-name', s.emergency_contact_name);
    setText('info-emergency-phone', s.emergency_contact_phone);

    // Address Info
    setText('info-street', addr.street || s.street);
    setText('info-city', addr.city || s.city);
    setText('info-state', addr.state || s.state);
    setText('info-pincode', addr.pincode || s.pincode);
    setText('info-country', addr.country || s.country || 'India');

    // ID Documents
    const docsContainer = document.getElementById('documents-container');
    if (docsContainer) {
        if (docs.length > 0) {
            docsContainer.innerHTML = docs.map(doc => {
                const frontUrl = doc.front_image_url || resolveAssetUrl(doc.front_image, 'staff-documents');
                const backUrl = doc.back_image_url || resolveAssetUrl(doc.back_image, 'staff-documents');
                const maskedId = maskIdNumber(doc.id_number);

                return `
                    <div class="bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl p-4 sm:p-5 mb-4 shadow-sm">
                        <div class="flex items-center justify-between gap-3 mb-4 pb-3 border-b border-slate-100 dark:border-white/5">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center border border-purple-500/20 shrink-0">
                                    <span class="material-symbols-outlined text-xl">badge</span>
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-sm text-slate-900 dark:text-white truncate">${doc.id_type || 'Identity Card'}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 font-mono tracking-widest">${maskedId}</div>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-[10px] font-black uppercase tracking-widest border border-emerald-500/20 shrink-0">
                                Verified
                            </span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="rounded-xl overflow-hidden border border-slate-200 dark:border-white/10 bg-slate-100/70 dark:bg-black/30 flex flex-col items-center justify-center p-3 min-h-[140px]">
                                <div class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2 self-start">Front Photo</div>
                                ${frontUrl ? `
                                    <a href="${frontUrl}" target="_blank" rel="noopener noreferrer" class="group block relative w-full h-28 overflow-hidden rounded-lg bg-black/5 dark:bg-white/5">
                                        <img src="${frontUrl}" class="w-full h-full object-contain group-hover:scale-105 transition-transform" alt="Front ID">
                                    </a>
                                ` : `
                                    <div class="text-slate-400 dark:text-slate-500 text-xs flex flex-col items-center gap-1 py-4">
                                        <span class="material-symbols-outlined text-2xl opacity-40">image_not_supported</span>
                                        <span>No front photo</span>
                                    </div>
                                `}
                            </div>
                            <div class="rounded-xl overflow-hidden border border-slate-200 dark:border-white/10 bg-slate-100/70 dark:bg-black/30 flex flex-col items-center justify-center p-3 min-h-[140px]">
                                <div class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2 self-start">Back Photo</div>
                                ${backUrl ? `
                                    <a href="${backUrl}" target="_blank" rel="noopener noreferrer" class="group block relative w-full h-28 overflow-hidden rounded-lg bg-black/5 dark:bg-white/5">
                                        <img src="${backUrl}" class="w-full h-full object-contain group-hover:scale-105 transition-transform" alt="Back ID">
                                    </a>
                                ` : `
                                    <div class="text-slate-400 dark:text-slate-500 text-xs flex flex-col items-center gap-1 py-4">
                                        <span class="material-symbols-outlined text-2xl opacity-40">image_not_supported</span>
                                        <span>No back photo</span>
                                    </div>
                                `}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        } else {
            docsContainer.innerHTML = '<div class="text-center py-8 text-slate-400 dark:text-slate-500 font-bold text-xs uppercase tracking-widest">No identity documents on file</div>';
        }
    }

    // Session Status
    const dtStr = s.last_login_at ? new Date(s.last_login_at.replace(' ', 'T')) : null;
    const loginEl = document.getElementById('info-last-login');
    if (loginEl) {
        loginEl.innerHTML = dtStr && !isNaN(dtStr.getTime())
            ? `${dtStr.toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' })} <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">(${dtStr.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit', hour12: true })})</span>`
            : 'First login';
    }

    const ipEl = document.getElementById('info-last-ip');
    if (ipEl) ipEl.textContent = s.last_login_ip || '127.0.0.1';

    const statusEl = document.getElementById('info-status');
    if (statusEl) {
        if (s.status === 'active') {
            statusEl.innerHTML = '<span class="px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-[10px] font-black uppercase tracking-widest border border-emerald-500/20 flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>Active</span>';
        } else {
            statusEl.innerHTML = '<span class="px-2.5 py-1 rounded-full bg-rose-500/10 text-rose-600 dark:text-rose-400 text-[10px] font-black uppercase tracking-widest border border-rose-500/20">Suspended</span>';
        }
    }
}

// ── EDIT MODAL LOGIC ──
let currentEditType = '';

function openEditModal(type) {
    currentEditType = type;
    const modal = document.getElementById('edit-modal');
    const fieldsContainer = document.getElementById('edit-fields');
    const title = document.getElementById('edit-modal-title');
    const errDiv = document.getElementById('edit-error');

    if (!modal || !fieldsContainer) return;

    if (errDiv) errDiv.classList.add('hidden');
    fieldsContainer.innerHTML = '';

    const s = profileData?.staff || {};
    const addr = profileData?.address || {};

    if (type === 'phone') {
        if (title) title.textContent = 'Edit Phone Number';
        fieldsContainer.innerHTML = `
            <div>
                <label for="edit-val-phone" class="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1.5">Mobile Phone Number *</label>
                <div class="fin-input-wrapper relative">
                    <span class="material-symbols-outlined input-icon-left text-slate-400">call</span>
                    <input type="tel" id="edit-val-phone" required pattern="[0-9]{10}" maxlength="10" class="w-full fin-input has-icon-left text-sm bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20" value="${s.phone || ''}" placeholder="10-digit number">
                </div>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Enter a valid 10-digit Indian mobile number.</p>
            </div>
        `;
    } else if (type === 'emergency') {
        if (title) title.textContent = 'Edit Emergency Contact';
        fieldsContainer.innerHTML = `
            <div class="space-y-4">
                <div>
                    <label for="edit-val-ename" class="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1.5">Contact Full Name *</label>
                    <div class="fin-input-wrapper relative">
                        <span class="material-symbols-outlined input-icon-left text-slate-400">person</span>
                        <input type="text" id="edit-val-ename" required class="w-full fin-input has-icon-left text-sm bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20" value="${s.emergency_contact_name || ''}" placeholder="Contact person name">
                    </div>
                </div>
                <div>
                    <label for="edit-val-ephone" class="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1.5">Contact Phone Number *</label>
                    <div class="fin-input-wrapper relative">
                        <span class="material-symbols-outlined input-icon-left text-slate-400">phone_in_talk</span>
                        <input type="tel" id="edit-val-ephone" required pattern="[0-9]{10}" maxlength="10" class="w-full fin-input has-icon-left text-sm bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20" value="${s.emergency_contact_phone || ''}" placeholder="10-digit emergency contact">
                    </div>
                </div>
            </div>
        `;
    } else if (type === 'address') {
        if (title) title.textContent = 'Edit Address Information';
        fieldsContainer.innerHTML = `
            <div class="space-y-3.5">
                <div>
                    <label for="edit-val-street" class="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1.5">Street Address</label>
                    <input type="text" id="edit-val-street" class="w-full px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20" value="${addr.street || s.street || ''}" placeholder="House / Flat / Street">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="edit-val-city" class="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1.5">City</label>
                        <input type="text" id="edit-val-city" class="w-full px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20" value="${addr.city || s.city || ''}" placeholder="City">
                    </div>
                    <div>
                        <label for="edit-val-state" class="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1.5">State</label>
                        <input type="text" id="edit-val-state" class="w-full px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20" value="${addr.state || s.state || ''}" placeholder="State">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="edit-val-pincode" class="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1.5">Postal Code</label>
                        <input type="text" id="edit-val-pincode" pattern="[0-9]{6}" maxlength="6" class="w-full px-3.5 py-2.5 text-sm font-mono bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20" value="${addr.pincode || s.pincode || ''}" placeholder="6-digit PIN">
                    </div>
                    <div>
                        <label for="edit-val-country" class="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1.5">Country</label>
                        <input type="text" id="edit-val-country" class="w-full px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20" value="${addr.country || s.country || 'India'}" placeholder="Country">
                    </div>
                </div>
            </div>
        `;
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeEditModal() {
    const modal = document.getElementById('edit-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

window.openEditModal = openEditModal;
window.closeEditModal = closeEditModal;

function checkPasswordStrength() {
    const pw = document.getElementById('pw-new')?.value || '';
    const bar = document.getElementById('pw-strength-bar');
    const txt = document.getElementById('pw-strength-text');

    if (!bar || !txt) return;

    let score = 0;
    if (pw.length >= 8) score++;
    if (/[A-Z]/.test(pw)) score++;
    if (/[0-9]/.test(pw)) score++;
    if (/[^A-Za-z0-9]/.test(pw)) score++;

    if (pw.length === 0) {
        bar.style.width = '0%';
        bar.className = 'password-strength-fill';
        txt.textContent = '';
    } else if (score < 2) {
        bar.style.width = '33%';
        bar.className = 'password-strength-fill bg-rose-500';
        txt.textContent = 'Weak (Needs min 8 chars with uppercase, numbers)';
        txt.className = 'text-[10px] font-bold mt-1 text-rose-500 uppercase tracking-wider';
    } else if (score === 2 || score === 3) {
        bar.style.width = '66%';
        bar.className = 'password-strength-fill bg-amber-500';
        txt.textContent = 'Good';
        txt.className = 'text-[10px] font-bold mt-1 text-amber-500 uppercase tracking-wider';
    } else {
        bar.style.width = '100%';
        bar.className = 'password-strength-fill bg-emerald-500';
        txt.textContent = 'Strong Enterprise Grade';
        txt.className = 'text-[10px] font-bold mt-1 text-emerald-500 uppercase tracking-wider';
    }
}

window.checkPasswordStrength = checkPasswordStrength;

function initEventListeners() {
    // 1. Photo Upload
    const photoInput = document.getElementById('photo-upload');
    if (photoInput) {
        photoInput.addEventListener('change', async function () {
            const file = this.files?.[0];
            if (!file) return;

            if (!file.type.match('image.*')) {
                displayToast('Please select a valid image file (JPEG or PNG)', 'error');
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                displayToast('Photo must be smaller than 5 MB', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('profile_photo', file);
            formData.append('photo', file);
            if (csrfToken) formData.append('csrf_token', csrfToken);

            displayToast('Uploading photo...', 'info');

            try {
                let data = null;
                if (window.api && typeof window.api.upload === 'function') {
                    data = await window.api.upload('/staff-portal/profile', formData);
                } else {
                    const res = await fetch('/Finzo/api/v1/staff-portal/profile', {
                        method: 'POST',
                        body: formData,
                        credentials: 'same-origin',
                        headers: { 'X-CSRF-Token': csrfToken }
                    });
                    data = await res.json();
                }

                if (data && data.success) {
                    displayToast('Profile photo updated successfully', 'success');
                    await loadProfile();
                } else {
                    displayToast(data?.message || 'Failed to upload photo', 'error');
                }
            } catch (err) {
                console.error('Photo upload error:', err);
                displayToast('Error uploading photo. Please try again.', 'error');
            }
        });
    }

    // 2. Edit Details Form
    const editForm = document.getElementById('edit-form');
    if (editForm) {
        editForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const btn = document.getElementById('edit-submit-btn');
            const errDiv = document.getElementById('edit-error');

            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="material-symbols-outlined animate-spin text-[16px]">sync</span> Saving...';
            }
            if (errDiv) errDiv.classList.add('hidden');

            const payload = {};

            if (currentEditType === 'phone') {
                payload.phone = document.getElementById('edit-val-phone')?.value.trim();
            } else if (currentEditType === 'emergency') {
                payload.emergency_contact_name = document.getElementById('edit-val-ename')?.value.trim();
                payload.emergency_contact_phone = document.getElementById('edit-val-ephone')?.value.trim();
            } else if (currentEditType === 'address') {
                payload.street = document.getElementById('edit-val-street')?.value.trim();
                payload.city = document.getElementById('edit-val-city')?.value.trim();
                payload.state = document.getElementById('edit-val-state')?.value.trim();
                payload.pincode = document.getElementById('edit-val-pincode')?.value.trim();
                payload.country = document.getElementById('edit-val-country')?.value.trim();
            }

            try {
                let data = null;
                if (window.api && typeof window.api.post === 'function') {
                    data = await window.api.post('/staff-portal/profile', payload);
                } else {
                    const res = await fetch('/Finzo/api/v1/staff-portal/profile', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': csrfToken
                        },
                        body: JSON.stringify(payload),
                        credentials: 'same-origin'
                    });
                    data = await res.json();
                }

                if (data && data.success) {
                    displayToast('Profile updated successfully', 'success');
                    closeEditModal();
                    await loadProfile();
                } else {
                    const errorMsg = data?.message || (data?.errors ? Object.values(data.errors).join(', ') : 'Failed to update profile');
                    if (errDiv) {
                        errDiv.textContent = errorMsg;
                        errDiv.classList.remove('hidden');
                    }
                }
            } catch (err) {
                console.error('Update error:', err);
                if (errDiv) {
                    errDiv.textContent = 'Network or server error. Please try again.';
                    errDiv.classList.remove('hidden');
                }
            }

            if (btn) {
                btn.disabled = false;
                btn.innerHTML = 'Save Changes';
            }
        });
    }

    // 3. Password Form
    const pwForm = document.getElementById('password-form');
    if (pwForm) {
        pwForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const current = document.getElementById('pw-current')?.value || '';
            const nw = document.getElementById('pw-new')?.value || '';
            const confirm = document.getElementById('pw-confirm')?.value || '';
            const errDiv = document.getElementById('pw-error');
            const btn = document.getElementById('pw-submit-btn');

            if (errDiv) errDiv.classList.add('hidden');

            if (nw !== confirm) {
                if (errDiv) {
                    errDiv.textContent = 'New passwords do not match';
                    errDiv.classList.remove('hidden');
                }
                return;
            }

            if (nw.length < 8) {
                if (errDiv) {
                    errDiv.textContent = 'Password must be at least 8 characters long';
                    errDiv.classList.remove('hidden');
                }
                return;
            }

            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="material-symbols-outlined animate-spin text-[16px]">sync</span> Updating...';
            }

            try {
                let data = null;
                const payload = {
                    current_password: current,
                    new_password: nw,
                    confirm_password: confirm
                };

                if (window.api && typeof window.api.post === 'function') {
                    data = await window.api.post('/staff-portal/change-password', payload);
                } else {
                    const res = await fetch('/Finzo/api/v1/staff-portal/change-password', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': csrfToken
                        },
                        body: JSON.stringify(payload),
                        credentials: 'same-origin'
                    });
                    data = await res.json();
                }

                if (data && data.success) {
                    displayToast('Password changed successfully', 'success');
                    pwForm.reset();
                    checkPasswordStrength();
                } else {
                    const errorMsg = data?.message || (data?.errors ? Object.values(data.errors).join(', ') : 'Password update failed');
                    if (errDiv) {
                        errDiv.textContent = errorMsg;
                        errDiv.classList.remove('hidden');
                    }
                }
            } catch (err) {
                console.error('Password change error:', err);
                if (errDiv) {
                    errDiv.textContent = 'Network or server error. Please try again.';
                    errDiv.classList.remove('hidden');
                }
            }

            if (btn) {
                btn.disabled = false;
                btn.innerHTML = 'Update Password';
            }
        });
    }
}
