/**
 * Finzo Admin — Staff Detail (Dossier View)
 * Matches the admin-detail.html design pattern
 */
document.addEventListener('DOMContentLoaded', () => {

    const urlParams = new URLSearchParams(window.location.search);
    const staffId = urlParams.get('id');
    
    if (!staffId) {
        alert('Staff ID is required');
        return;
    }

    let currentAction = '';

    const modalConfigs = {
        reset: {
            title: 'Reset Password',
            subtitle: 'Security protocol override',
            body: 'You are about to issue a mandatory password reset for <b id="reset-target-name">...</b>. A temporary system-generated password will be created, and the staff will be required to change it at next login.',
            icon: 'lock_reset',
            color: 'indigo',
            btnText: 'Confirm Reset',
            btnClass: 'bg-indigo-600 shadow-indigo-600/20',
            reasons: []
        },
        deactivate: {
            title: 'Deactivate Staff',
            subtitle: 'Irreversible suspension of access',
            body: 'You are about to deactivate <b id="deactivate-target-name">...</b>. This will revoke all system access immediately and terminate active sessions.',
            icon: 'block',
            color: 'rose',
            btnText: 'Confirm Deactivation',
            btnClass: 'bg-rose-600 shadow-rose-600/20',
            reasons: [
                'Unauthorized Data Access',
                'Financial Discrepancy Found',
                'Policy Violation / Misconduct',
                'Performance Related Termination',
                'Voluntary Resignation',
                'Security Breach Suspected',
                'Other'
            ]
        },
        delete: {
            title: 'Delete Staff Account',
            subtitle: 'Critical security procedure',
            body: 'You are about to <span class="text-rose-600 font-black underline">PERMANENTLY DELETE</span> <b id="delete-target-name">...</b> from the system. This action is irreversible and will remove all associated address and identity records.',
            icon: 'delete_forever',
            color: 'rose',
            btnText: 'Confirm Permanent Deletion',
            btnClass: 'bg-rose-700 shadow-rose-700/20',
            reasons: []
        }
    };

    function getInitials(name) {
        if (!name) return 'S';
        return name.split(' ').map(n => n[0]).join('').toUpperCase();
    }

    function formatCurrency(amount) {
        return window.FinzoUtils.formatINR(amount);
    }

    function formatDate(dateStr) {
        if (!dateStr) return 'N/A';
        const d = new Date(dateStr);
        return d.toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
    }

    function timeAgo(dateStr) {
        if (!dateStr) return 'Never';
        const d = new Date(dateStr);
        const now = new Date();
        const diff = now - d;
        const mins = Math.floor(diff / 60000);
        const hours = Math.floor(diff / 3600000);
        const days = Math.floor(diff / 86400000);
        
        if (mins < 1) return 'Just now';
        if (mins < 60) return `${mins} min ago`;
        if (hours < 24) return `${hours} hour${hours > 1 ? 's' : ''} ago`;
        if (days < 30) return `${days} day${days > 1 ? 's' : ''} ago`;
        return formatDate(dateStr);
    }

    // Lightbox
    window.openLightbox = function(imgSrc) {
        const modal = document.getElementById('lightbox-modal');
        const img = document.getElementById('lightbox-img');
        if (modal && img) {
            img.src = imgSrc;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    };

    window.closeLightbox = function() {
        const modal = document.getElementById('lightbox-modal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    };

    async function loadStaffData() {
        try {
            const response = await window.api.get(`/admin/staff-detail.php?id=${staffId}`);
            if (response.status === 'success' || response.success) {
                const staff = response.staff;
                const performance = response.performance || {};
                const today = response.today_performance || {};
                const transactions = response.transactions || [];
                const totalShifts = response.total_shifts || 0;
                const documents = response.documents || [];
                const address = response.address || {};

                // --- Header ---
                const headerName = document.getElementById('header-staff-name');
                if (headerName) headerName.textContent = staff.full_name;

                // Status badge in header
                const statusBadge = document.getElementById('header-status-badge');
                if (statusBadge) {
                    const isActive = staff.status.toLowerCase() === 'active';
                    const dotEl = statusBadge.querySelector('div');
                    const textEl = statusBadge.querySelector('span');
                    
                    if (isActive) {
                        statusBadge.className = 'hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-full border border-emerald-200 dark:border-emerald-500/30 bg-emerald-50 dark:bg-emerald-500/10';
                        if (dotEl) dotEl.className = 'w-1.5 h-1.5 rounded-full animate-pulse bg-emerald-500';
                        if (textEl) { textEl.className = 'text-[10px] font-black uppercase tracking-widest text-emerald-700 dark:text-emerald-400'; textEl.textContent = 'ACTIVE ACCOUNT'; }
                    } else {
                        statusBadge.className = 'hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-full border border-rose-200 dark:border-rose-500/30 bg-rose-50 dark:bg-rose-500/10';
                        if (dotEl) dotEl.className = 'w-1.5 h-1.5 rounded-full animate-pulse bg-rose-500';
                        if (textEl) { textEl.className = 'text-[10px] font-black uppercase tracking-widest text-rose-700 dark:text-rose-400'; textEl.textContent = 'SUSPENDED'; }
                    }
                }

                // --- Profile Card ---
                const photoContainer = document.getElementById('staff-photo-container');
                const initialsEl = document.getElementById('photo-initials');
                if (photoContainer && staff.profile_photo) {
                    const baseUrl = window.location.origin + '/Finzo/';
                    photoContainer.innerHTML = `<img src="${baseUrl}${staff.profile_photo}" class="w-full h-full object-cover" alt="${staff.full_name}" onerror="this.parentNode.innerHTML='<span class=\\'text-4xl font-black text-slate-300\\'>${getInitials(staff.full_name)}</span>'" />`;
                } else if (initialsEl) {
                    initialsEl.textContent = getInitials(staff.full_name);
                    if (photoContainer) photoContainer.style.background = window.FinzoUtils?.getAvatarGradient?.(getInitials(staff.full_name)) || 'linear-gradient(135deg, #667eea, #764ba2)';
                }

                const infoName = document.getElementById('info-name');
                if (infoName) infoName.textContent = staff.full_name;

                const staffIdBadge = document.getElementById('info-staff-id-badge');
                if (staffIdBadge) staffIdBadge.textContent = staff.staff_id;

                const desBadge = document.getElementById('info-designation-badge');
                if (desBadge) desBadge.textContent = staff.designation;

                const depBadge = document.getElementById('info-department-badge');
                if (depBadge) depBadge.textContent = staff.department;

                const joinedDate = document.getElementById('info-joined-date');
                if (joinedDate) joinedDate.textContent = formatDate(staff.date_of_joining || staff.created_at);

                // Personal info
                const infoEmail = document.getElementById('info-email');
                if (infoEmail) infoEmail.innerHTML = `<span class="material-symbols-outlined text-slate-400 text-base">mail</span> ${staff.email}`;

                const infoPhone = document.getElementById('info-phone');
                if (infoPhone) infoPhone.innerHTML = `<span class="material-symbols-outlined text-slate-400 text-base">phone</span> ${staff.phone || 'N/A'}`;

                const infoEmergency = document.getElementById('info-emergency');
                if (infoEmergency) {
                    const emergencyText = staff.emergency_contact_name 
                        ? `${staff.emergency_contact_name} (${staff.emergency_contact_phone || 'N/A'})` 
                        : 'Not provided';
                    infoEmergency.innerHTML = `<span class="material-symbols-outlined text-slate-400 text-base">emergency</span> ${emergencyText}`;
                }

                const infoGender = document.getElementById('info-gender');
                if (infoGender) infoGender.textContent = staff.gender || 'N/A';

                const infoDob = document.getElementById('info-dob');
                if (infoDob) infoDob.textContent = formatDate(staff.date_of_birth);

                const infoBranch = document.getElementById('info-branch');
                if (infoBranch) infoBranch.innerHTML = `<span class="material-symbols-outlined text-slate-400 text-base">store</span> ${staff.branch_name || 'Unassigned'}`;

                // --- Address ---
                const addrStreet = document.getElementById('addr-street');
                if (addrStreet) addrStreet.textContent = address.street || 'N/A';

                const addrCity = document.getElementById('addr-city');
                if (addrCity) addrCity.textContent = address.city || 'N/A';

                const addrState = document.getElementById('addr-state');
                if (addrState) addrState.textContent = address.state || 'N/A';

                const addrPincode = document.getElementById('addr-pincode');
                if (addrPincode) addrPincode.textContent = address.pincode || 'N/A';

                // --- System Audit ---
                const sysLastLogin = document.getElementById('sys-last-login');
                if (sysLastLogin) sysLastLogin.textContent = staff.last_login_at ? timeAgo(staff.last_login_at) : 'Never';

                const sysLastIp = document.getElementById('sys-last-ip');
                if (sysLastIp) sysLastIp.textContent = staff.last_login_ip || '—';

                const sysJoining = document.getElementById('sys-joining-date');
                if (sysJoining) sysJoining.textContent = formatDate(staff.date_of_joining);

                const sysCreatedBy = document.getElementById('sys-created-by');
                if (sysCreatedBy) sysCreatedBy.textContent = staff.created_by || 'System';

                const sysStaffId = document.getElementById('sys-staff-id');
                if (sysStaffId) sysStaffId.textContent = staff.staff_id;

                const sysDbId = document.getElementById('sys-db-id');
                if (sysDbId) sysDbId.textContent = `#${staff.id}`;

                const sysTotalShifts = document.getElementById('sys-total-shifts');
                if (sysTotalShifts) sysTotalShifts.textContent = totalShifts;

                // --- Documents ---
                const doc = documents.length > 0 ? documents[0] : null;
                const docIdType = document.getElementById('doc-id-type');
                if (docIdType) docIdType.textContent = doc?.id_type || 'Not uploaded';

                const docIdNumber = document.getElementById('doc-id-number');
                if (docIdNumber) docIdNumber.textContent = doc?.id_number || '—';

                const baseUrl = window.location.origin + '/Finzo/';

                if (doc?.front_image) {
                    const frontImg = document.getElementById('doc-front-img');
                    const frontPlaceholder = document.getElementById('doc-front-placeholder');
                    const frontWrapper = document.getElementById('doc-front-wrapper');
                    if (frontImg) {
                        frontImg.src = baseUrl + doc.front_image;
                        frontImg.classList.remove('hidden');
                    }
                    if (frontPlaceholder) frontPlaceholder.classList.add('hidden');
                    if (frontWrapper) frontWrapper.onclick = () => openLightbox(baseUrl + doc.front_image);
                }

                if (doc?.back_image) {
                    const backImg = document.getElementById('doc-back-img');
                    const backPlaceholder = document.getElementById('doc-back-placeholder');
                    const backWrapper = document.getElementById('doc-back-wrapper');
                    if (backImg) {
                        backImg.src = baseUrl + doc.back_image;
                        backImg.classList.remove('hidden');
                    }
                    if (backPlaceholder) backPlaceholder.classList.add('hidden');
                    if (backWrapper) backWrapper.onclick = () => openLightbox(baseUrl + doc.back_image);
                }

                // --- Performance Stats ---
                const statDeposits = document.getElementById('stat-total-deposits');
                if (statDeposits) statDeposits.textContent = formatCurrency(performance.total_deposits);

                const statWithdrawals = document.getElementById('stat-total-withdrawals');
                if (statWithdrawals) statWithdrawals.textContent = formatCurrency(performance.total_withdrawals);

                const statTxns = document.getElementById('stat-total-txns');
                if (statTxns) statTxns.textContent = performance.total_transactions || 0;

                const statLastActive = document.getElementById('stat-last-active');
                if (statLastActive) statLastActive.textContent = staff.last_login_at ? timeAgo(staff.last_login_at) : 'Never';

                // --- Today's Shift ---
                const sDep = document.getElementById('summary-deposits');
                if (sDep) sDep.textContent = `${today.deposit_count || 0} (${formatCurrency(today.total_deposits)})`;

                const sWit = document.getElementById('summary-withdrawals');
                if (sWit) sWit.textContent = `${today.withdrawal_count || 0} (${formatCurrency(today.total_withdrawals)})`;

                const sTx = document.getElementById('summary-total-txns');
                if (sTx) sTx.textContent = today.total_transactions || 0;

                const sAmt = document.getElementById('summary-total-amount');
                if (sAmt) sAmt.textContent = formatCurrency((parseFloat(today.total_deposits) || 0) + (parseFloat(today.total_withdrawals) || 0));

                // --- Button state for suspended ---
                const deactivateBtn = document.getElementById('deactivate-staff-btn');
                if (staff.status.toLowerCase() !== 'active' && deactivateBtn) {
                    deactivateBtn.disabled = true;
                    deactivateBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    const label = document.getElementById('suspend-label');
                    if (label) label.textContent = 'Already Suspended';
                    const icon = document.getElementById('suspend-icon');
                    if (icon) icon.textContent = 'check_circle';
                }

                // --- Transactions ---
                const tbody = document.getElementById('staff-transactions-body');
                if (tbody) {
                    if (transactions && transactions.length > 0) {
                        tbody.innerHTML = transactions.map(t => `
                        <tr class="hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">
                            <td class="text-[11px] font-bold opacity-60 font-mono">${t.txn_id}</td>
                            <td class="text-xs font-medium">${window.FinzoUtils.formatDateTime(t.created_at)}</td>
                            <td class="font-bold text-sm text-slate-800 dark:text-slate-100">${t.customer_name}</td>
                            <td class="font-mono text-xs opacity-60">${t.account_no}</td>
                            <td><span class="status-badge ${t.type === 'deposit' ? 'badge-success' : 'badge-error'}">${t.type.toUpperCase()}</span></td>
                            <td class="font-black text-xs ${t.type === 'deposit' ? 'text-primary dark:text-blue-400' : 'text-error'}">${formatCurrency(t.amount)}</td>
                            <td class="text-right">
                                <button class="p-2 hover:bg-slate-100 dark:hover:bg-white/5 rounded-lg transition-all text-slate-400 group">
                                    <span class="material-symbols-outlined text-lg group-hover:text-primary transition-colors">receipt_long</span>
                                </button>
                            </td>
                        </tr>
                        `).join('');
                    } else {
                        tbody.innerHTML = '<tr><td colspan="7" class="py-12 text-center opacity-40 font-bold">No transactions recorded yet</td></tr>';
                    }
                }
            } else {
                if (window.showToast) window.showToast(response.message || 'Error loading staff details', 'error');
            }
        } catch (error) {
            console.error('Failed to load staff data:', error);
            if (window.showToast) window.showToast('Connection error. Please try again.', 'error');
        }
    }

    // --- Modal System (identical to original) ---
    function openGovernanceModal(action) {
        currentAction = action;
        const config = modalConfigs[action];

        const reasonStep = document.getElementById('reason-step');
        const authStep = document.getElementById('auth-step');
        const bodyText = document.getElementById('modal-body-text');
        const customContainer = document.getElementById('custom-reason-container');
        
        reasonStep.classList.add('hidden');
        authStep.classList.remove('hidden');
        customContainer.classList.add('hidden');

        bodyText.innerHTML = config.body;
        document.getElementById('modal-title').textContent = config.title;
        document.getElementById('modal-subtitle').textContent = config.subtitle;
        document.getElementById('modal-icon').textContent = config.icon;
        
        const staffName = document.getElementById('info-name')?.textContent || 'this staff member';
        const targetName = document.getElementById(action + '-target-name');
        if (targetName) targetName.textContent = staffName;

        const iconContainer = document.getElementById('modal-icon-container');
        iconContainer.className = `w-16 h-16 rounded-full mx-auto flex items-center justify-center mb-4 bg-${config.color}-50 dark:bg-${config.color}-500/10 text-${config.color}-500 shadow-lg`;
        
        const confirmBtn = document.getElementById('confirm-governance-btn');
        confirmBtn.className = `flex-1 py-4 text-white font-black rounded-2xl text-[11px] uppercase tracking-widest shadow-lg hover:scale-[1.02] active:scale-[0.98] transition-all ${config.btnClass}`;
        confirmBtn.textContent = config.btnText;
        confirmBtn.disabled = false;

        if (config.reasons && config.reasons.length > 0) {
            let reasonsHtml = '';
            config.reasons.forEach(r => {
                reasonsHtml += `
                <label class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-white/5 rounded-xl border-2 border-transparent hover:border-slate-200 dark:hover:border-white/10 cursor-pointer transition-all group">
                    <input type="radio" name="suspension_reason" value="${r}" class="text-primary focus:ring-primary/10">
                    <span class="text-[10px] font-black text-slate-500 group-hover:text-black dark:group-hover:text-white leading-tight">${r}</span>
                </label>
                `;
            });
            document.getElementById('reason-grid').innerHTML = reasonsHtml;
            reasonStep.classList.remove('hidden');
            
            document.querySelectorAll('input[name="suspension_reason"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'Other') {
                        customContainer.classList.remove('hidden');
                    } else {
                        customContainer.classList.add('hidden');
                    }
                });
            });
        }

        const backdrop = document.getElementById('modal-backdrop');
        backdrop.style.opacity = '1';
        
        const container = document.getElementById('modal-container');
        container.classList.remove('scale-95');
        container.style.opacity = '1';
        
        const modal = document.getElementById('governance-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        document.body.classList.add('overflow-hidden');
    }

    window.closeGovernanceModal = function() {
        const container = document.getElementById('modal-container');
        container.classList.add('scale-95');
        container.style.opacity = '0';
        
        const backdrop = document.getElementById('modal-backdrop');
        backdrop.style.opacity = '0';
        
        setTimeout(() => {
            const modal = document.getElementById('governance-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            
            document.body.classList.remove('overflow-hidden');
            
            document.getElementById('admin-password').value = '';
            document.getElementById('auth-error').classList.add('hidden');
            
            document.getElementById('reason-step').classList.add('hidden');
            document.getElementById('custom-reason-container').classList.add('hidden');
        }, 300);
    };

    const dBtn = document.getElementById('deactivate-staff-btn');
    if (dBtn) {
        dBtn.addEventListener('click', function() {
            if (this.disabled) return;
            openGovernanceModal('deactivate');
        });
    }

    const delBtn = document.getElementById('delete-staff-btn');
    if (delBtn) {
        delBtn.addEventListener('click', function() {
            openGovernanceModal('delete');
        });
    }

    const rBtn = document.getElementById('reset-password-btn');
    if (rBtn) {
        rBtn.addEventListener('click', function() {
            openGovernanceModal('reset');
        });
    }

    const cBtn = document.getElementById('confirm-governance-btn');
    if (cBtn) {
        cBtn.addEventListener('click', async function() {
            if (this.textContent.includes('DONE') || this.textContent.includes('LOGGED')) { 
                closeGovernanceModal(); 
                return; 
            }

            const password = document.getElementById('admin-password').value;
            const checkedReason = document.querySelector('input[name="suspension_reason"]:checked');
            const reason = checkedReason ? checkedReason.value : null;
            
            let apiAction = '';
            if (currentAction === 'deactivate') apiAction = 'suspend';
            else if (currentAction === 'reset') apiAction = 'reset_password';
            else if (currentAction === 'delete') apiAction = 'delete';

            if (currentAction === 'deactivate' && !reason) {
                alert('Please select a reason for deactivation.');
                return;
            }

            this.disabled = true;
            this.innerHTML = '<span class="material-symbols-outlined animate-spin text-sm">refresh</span> AUTHORIZING...';

            try {
                const response = await window.api.post('/admin/staff-action.php', {
                    action: apiAction,
                    staff_id: staffId,
                    password: password,
                    reason: reason,
                    notes: document.getElementById('custom-reason-notes').value
                });

                if (response.status === 'success' || response.success) {
                    if (currentAction === 'reset') {
                        document.getElementById('auth-step').style.display = 'none';
                        document.getElementById('modal-subtitle').style.display = 'none';
                        document.getElementById('modal-title').textContent = 'Temporary Password Generated';
                        document.getElementById('modal-body-text').innerHTML = `
                            <div class="p-6 bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 rounded-2xl text-center">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">One-Time Password</p>
                            <div class="text-2xl font-black text-primary dark:text-amber-500 font-mono tracking-wider mb-2">${response.temp_password || response.data?.temp_password}</div>
                            <p class="text-[10px] font-bold text-rose-500 uppercase">Valid for 24 hours only</p>
                            </div>
                        `;
                        this.disabled = false;
                        this.innerHTML = '<span class="material-symbols-outlined">check_circle</span> DONE';
                        this.classList.replace(modalConfigs['reset'].btnClass.split(' ')[0], 'bg-emerald-500');
                        this.classList.add('shadow-emerald-500/20');
                    } else {
                        this.innerHTML = '<span class="material-symbols-outlined">check_circle</span> ACTION LOGGED';
                        this.classList.replace(modalConfigs['deactivate'].btnClass.split(' ')[0], 'bg-emerald-500');
                        this.classList.add('shadow-emerald-500/20');
                        setTimeout(() => {
                            closeGovernanceModal();
                            loadStaffData();
                        }, 1500);
                    }
                } else {
                    const errEl = document.getElementById('auth-error');
                    errEl.textContent = response.message;
                    errEl.classList.remove('hidden');
                    errEl.style.display = 'block';
                    
                    this.disabled = false;
                    this.textContent = currentAction === 'reset' ? 'Confirm Reset' : 'Confirm Deactivation';
                }
            } catch (error) {
                alert('Connection failed. Please try again.');
                this.disabled = false;
                this.textContent = 'Try Again';
            }
        });
    }

    loadStaffData();
});
