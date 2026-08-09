let profileData = null;
        let csrfToken = '';

        $(document).ready(async function () {
            const session = await fetchStaffSession();
            if (!session) return;
            csrfToken = session.csrf_token || '';
            loadProfile();
        });

        function showToast(msg, type = 'success') {
            const bgClass = type === 'success' ? 'bg-emerald-500' : 'bg-red-500';
            const icon = type === 'success' ? 'check_circle' : 'error';
            const toast = $(`<div class="${bgClass} text-white px-4 py-3 rounded-lg shadow-lg flex items-center gap-2 text-sm font-bold animate-in slide-in-from-right"><span class="material-symbols-outlined text-[20px]">${icon}</span> ${msg}</div>`);
            $('#toast-container').append(toast);
            setTimeout(() => { toast.fadeOut(300, function() { $(this).remove(); }); }, 3000);
        }

        async function loadProfile() {
            try {
                const res = await fetch('/Finzo/api/staff/staff-profile.php', { credentials: 'same-origin' });
                const data = await res.json();
                if (!data.success) throw new Error(data.message);
                profileData = data;
                renderProfile(data);
            } catch (err) {
                console.error('Profile load error:', err);
            }
        }

        function renderProfile(data) {
            const s = data.staff;
            const addr = data.address || {};
            const docs = data.documents || [];

            // Hero
            document.getElementById('profile-name').textContent = s.full_name;
            document.getElementById('profile-designation').textContent = s.designation || 'Staff';
            document.getElementById('profile-department').textContent = s.department || 'Operations';
            document.getElementById('profile-staff-id').textContent = s.staff_id;
            document.getElementById('profile-branch').textContent = s.branch_name || 'Unassigned';
            document.getElementById('profile-joined').textContent = s.date_of_joining ? new Date(s.date_of_joining).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' }) : '—';

            // Avatar
            const avatarEl = document.getElementById('profile-avatar');
            const avatarText = document.getElementById('profile-avatar-text');
            if (s.profile_photo) {
                const src = s.profile_photo.startsWith('http') ? s.profile_photo : '/Finzo/' + s.profile_photo;
                avatarEl.innerHTML = `<img src="${src}?v=${Date.now()}" alt="${s.full_name}">`;
            } else {
                const initials = s.full_name.split(' ').filter(w => /^[a-zA-Z]/.test(w)).map(w => w[0]).join('').toUpperCase();
                avatarEl.innerHTML = `<span id="profile-avatar-text">${initials}</span>`;
            }

            // Personal Info
            document.getElementById('info-name').textContent = s.full_name;
            document.getElementById('info-dob').textContent = s.date_of_birth ? new Date(s.date_of_birth).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' }) : 'Not set';
            document.getElementById('info-gender').textContent = s.gender || 'Not set';
            document.getElementById('info-email').textContent = s.personal_email;
            document.getElementById('info-phone').textContent = s.phone || 'Not set';

            // Emergency
            document.getElementById('info-emergency-name').textContent = s.emergency_contact_name || 'Not set';
            document.getElementById('info-emergency-phone').textContent = s.emergency_contact_phone || 'Not set';

            // Address
            document.getElementById('info-street').textContent = addr.street || 'Not set';
            document.getElementById('info-city').textContent = addr.city || 'Not set';
            document.getElementById('info-state').textContent = addr.state || 'Not set';
            document.getElementById('info-pincode').textContent = addr.pincode || 'Not set';
            document.getElementById('info-country').textContent = addr.country || 'India';

            // Documents
            const docsContainer = document.getElementById('documents-container');
            if (docs.length > 0) {
                docsContainer.innerHTML = docs.map(doc => `
                    <div class="bg-white dark:bg-[#161b22] border border-slate-200 dark:border-white/10 rounded-xl p-4 mb-4">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="material-symbols-outlined text-purple-500">credit_card</span>
                            <div class="flex-1">
                                <div class="font-bold text-sm text-slate-900 dark:text-white">${doc.id_type}</div>
                                <div class="text-[10px] text-slate-400 font-mono tracking-widest">${maskIdNumber(doc.id_number)}</div>
                            </div>
                            <span class="px-2 py-1 rounded bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 text-[9px] font-black uppercase tracking-widest border border-emerald-200 dark:border-emerald-500/20">Verified</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            ${doc.front_image ? `<div class="rounded-lg overflow-hidden border border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-[#0d1117] flex items-center justify-center p-2 h-32">
                                <img src="/Finzo/${doc.front_image}" class="max-w-full max-h-full object-contain" alt="Front">
                            </div>` : ''}
                            ${doc.back_image ? `<div class="rounded-lg overflow-hidden border border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-[#0d1117] flex items-center justify-center p-2 h-32">
                                <img src="/Finzo/${doc.back_image}" class="max-w-full max-h-full object-contain" alt="Back">
                            </div>` : ''}
                        </div>
                    </div>
                `).join('');
            } else {
                docsContainer.innerHTML = '<div class="text-center py-6 text-slate-400 font-bold text-xs uppercase tracking-widest">No documents on file</div>';
            }

            // Session Info
            const dtStr = s.last_login_at ? new Date(s.last_login_at) : null;
            document.getElementById('info-last-login').innerHTML = dtStr ? `${dtStr.toLocaleDateString('en-IN')}<br><span class="text-[10px] text-slate-400">${dtStr.toLocaleTimeString('en-IN')}</span>` : 'First login';
            document.getElementById('info-last-ip').textContent = s.last_login_ip || 'N/A';

            const statusEl = document.getElementById('info-status');
            if (s.status === 'active') {
                statusEl.innerHTML = '<span class="px-2 py-1 rounded bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 text-[10px] font-black uppercase tracking-widest border border-emerald-200 dark:border-emerald-500/20">Active</span>';
            } else {
                statusEl.innerHTML = '<span class="px-2 py-1 rounded bg-red-50 dark:bg-red-500/10 text-red-600 text-[10px] font-black uppercase tracking-widest border border-red-200 dark:border-red-500/20">Suspended</span>';
            }
        }

        function maskIdNumber(num) {
            if (!num || num.length < 4) return num || '—';
            return '•'.repeat(num.length - 4) + num.slice(-4);
        }

        // Photo Upload
        document.getElementById('photo-upload').addEventListener('change', async function () {
            const file = this.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('photo', file);
            formData.append('csrf_token', csrfToken);

            try {
                const res = await fetch('/Finzo/api/staff/upload-photo.php', { method: 'POST', body: formData, credentials: 'same-origin' });
                const data = await res.json();
                if (data.success) {
                    showToast('Profile photo updated successfully');
                    loadProfile();
                } else {
                    showToast(data.message, 'error');
                }
            } catch (err) {
                showToast('Error uploading photo', 'error');
            }
        });

        // Edit Modal Logic
        let currentEditType = '';
        function openEditModal(type) {
            currentEditType = type;
            const fieldsContainer = document.getElementById('edit-fields');
            const title = document.getElementById('edit-modal-title');
            document.getElementById('edit-error').classList.add('hidden');
            fieldsContainer.innerHTML = '';

            const s = profileData.staff;

            if (type === 'phone') {
                title.textContent = 'Edit Phone Number';
                fieldsContainer.innerHTML = `
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Phone Number</label>
                        <input type="text" id="edit-val-phone" class="w-full ds-input" value="${s.phone || ''}" placeholder="e.g. 9876543210" pattern="[0-9]{10}">
                    </div>`;
            } else if (type === 'emergency') {
                title.textContent = 'Edit Emergency Contact';
                fieldsContainer.innerHTML = `
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Contact Name</label>
                        <input type="text" id="edit-val-ename" class="w-full ds-input" value="${s.emergency_contact_name || ''}">
                    </div>
                    <div class="mt-4">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Contact Phone</label>
                        <input type="text" id="edit-val-ephone" class="w-full ds-input" value="${s.emergency_contact_phone || ''}" pattern="[0-9]{10}">
                    </div>`;
            }

            document.getElementById('edit-modal').classList.remove('hidden');
            document.getElementById('edit-modal').classList.add('flex');
        }

        function closeEditModal() {
            document.getElementById('edit-modal').classList.add('hidden');
            document.getElementById('edit-modal').classList.remove('flex');
        }

        document.getElementById('edit-form').addEventListener('submit', async function (e) {
            e.preventDefault();
            const btn = document.getElementById('edit-submit-btn');
            const errDiv = document.getElementById('edit-error');
            btn.disabled = true;
            btn.innerHTML = '<span class="material-symbols-outlined animate-spin text-[16px]">sync</span> Saving...';
            errDiv.classList.add('hidden');

            const payload = { csrf_token: csrfToken };

            if (currentEditType === 'phone') {
                payload.phone = document.getElementById('edit-val-phone').value.trim();
            } else if (currentEditType === 'emergency') {
                payload.emergency_contact_name = document.getElementById('edit-val-ename').value.trim();
                payload.emergency_contact_phone = document.getElementById('edit-val-ephone').value.trim();
            }

            try {
                const res = await fetch('/Finzo/api/staff/update-profile.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                    credentials: 'same-origin'
                });
                const data = await res.json();
                if (data.success) {
                    showToast('Profile updated successfully');
                    closeEditModal();
                    loadProfile();
                } else {
                    errDiv.textContent = data.message;
                    errDiv.classList.remove('hidden');
                }
            } catch (err) {
                errDiv.textContent = 'Network error. Try again.';
                errDiv.classList.remove('hidden');
            }
            btn.disabled = false;
            btn.innerHTML = 'Save';
        });

        // Password Update Logic
        function checkPasswordStrength() {
            const pw = document.getElementById('pw-new').value;
            const bar = document.getElementById('pw-strength-bar');
            const txt = document.getElementById('pw-strength-text');

            let score = 0;
            if (pw.length >= 8) score++;
            if (pw.match(/[A-Z]/)) score++;
            if (pw.match(/[0-9]/)) score++;
            if (pw.match(/[^A-Za-z0-9]/)) score++;

            if (pw.length === 0) {
                bar.style.width = '0%';
                txt.textContent = '';
            } else if (score < 2) {
                bar.style.width = '33%';
                bar.className = 'password-strength-fill bg-red-500';
                txt.textContent = 'Weak';
                txt.className = 'text-[9px] font-bold mt-1 text-red-500 uppercase tracking-widest';
            } else if (score === 2 || score === 3) {
                bar.style.width = '66%';
                bar.className = 'password-strength-fill bg-amber-500';
                txt.textContent = 'Good';
                txt.className = 'text-[9px] font-bold mt-1 text-amber-500 uppercase tracking-widest';
            } else {
                bar.style.width = '100%';
                bar.className = 'password-strength-fill bg-emerald-500';
                txt.textContent = 'Strong';
                txt.className = 'text-[9px] font-bold mt-1 text-emerald-500 uppercase tracking-widest';
            }
        }

        document.getElementById('password-form').addEventListener('submit', async function (e) {
            e.preventDefault();
            const current = document.getElementById('pw-current').value;
            const nw = document.getElementById('pw-new').value;
            const confirm = document.getElementById('pw-confirm').value;
            const errDiv = document.getElementById('pw-error');
            const btn = document.getElementById('pw-submit-btn');

            errDiv.classList.add('hidden');

            if (nw !== confirm) {
                errDiv.textContent = 'New passwords do not match';
                errDiv.classList.remove('hidden');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<span class="material-symbols-outlined animate-spin text-[16px]">sync</span> Updating...';

            try {
                const res = await fetch('/Finzo/api/staff/change-password.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        current_password: current,
                        new_password: nw,
                        csrf_token: csrfToken
                    }),
                    credentials: 'same-origin'
                });
                const data = await res.json();
                if (data.success) {
                    showToast('Password changed successfully');
                    document.getElementById('password-form').reset();
                    document.getElementById('pw-strength-bar').style.width = '0%';
                    document.getElementById('pw-strength-text').textContent = '';
                } else {
                    errDiv.textContent = data.message;
                    errDiv.classList.remove('hidden');
                }
            } catch (err) {
                errDiv.textContent = 'Network error. Try again.';
                errDiv.classList.remove('hidden');
            }
            btn.disabled = false;
            btn.innerHTML = 'Update Password';
        });