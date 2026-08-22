/**
 * Finzo Admin — Review Application
 */
document.addEventListener('DOMContentLoaded', () => {

    const urlParams = new URLSearchParams(window.location.search);
    const userId = urlParams.get('id');

    if (!userId) {
        alert('User ID is missing');
        // window.location.href = 'admin-pending-approvals.php';
        return;
    }

    async function loadDetails() {
        try {
            const response = await window.api.get(`/admin/applications/${userId}`);
            if (response.status === 'success' || response.success) {
                const data = response.data || response;
                populateUI(data);
            } else {
                window.showToast(response.message || 'Failed to load details', 'error');
            }
        } catch (error) {
            console.error('Error loading application:', error);
            window.showToast('Connection error', 'error');
        }
    }

    function populateUI(data) {
        const user = data.user;
        const account = data.account || {};

        // Header
        const bcName = document.getElementById('bc-name');
        if (bcName) bcName.textContent = user.full_name;

        const statusBadge = document.getElementById('top-status-badge');
        if (statusBadge) {
            statusBadge.textContent = (user.status || '').toUpperCase();
            statusBadge.className = `status-badge ${user.status === 'pending' ? 'badge-warning' : (user.status === 'active' ? 'badge-success' : 'badge-danger')} px-4 py-2 text-[11px]`;
        }

        const adminAvatar = document.getElementById('top-admin-avatar');
        if (adminAvatar) adminAvatar.textContent = user.initials || (user.full_name ? user.full_name.charAt(0) : 'A');

        // Header info
        const headerFullName = document.getElementById('header-full-name');
        if (headerFullName) headerFullName.textContent = user.full_name || 'N/A';
        
        const headerCustomerId = document.getElementById('header-customer-id');
        if (headerCustomerId) headerCustomerId.textContent = user.customer_id || 'FNZ-USR-PENDING';

        const profilePhoto = document.getElementById('profile-photo');
        const profilePlaceholder = document.getElementById('profile-photo-placeholder');
        if (profilePhoto && user.profile_picture_url) {
            let url = user.profile_picture_url;
            if (!url.startsWith('http://') && !url.startsWith('https://')) {
                url = url.startsWith('/') ? url : '/Finzo/' + url.replace(/^\//, '');
            }
            profilePhoto.src = url;
            profilePhoto.classList.remove('hidden');
            if (profilePlaceholder) profilePlaceholder.classList.add('hidden');
        }

        // Personal Details
        const valDob = document.getElementById('val-dob');
        const valGender = document.getElementById('val-gender');
        const valMarital = document.getElementById('val-marital-status');
        const valNat = document.getElementById('val-nationality');
        const valFather = document.getElementById('val-father-name');
        const valMother = document.getElementById('val-mother-name');
        const valOcc = document.getElementById('val-occupation');
        const valSrc = document.getElementById('val-source-of-income');
        const valAnn = document.getElementById('val-annual-income');
        const valPhone = document.getElementById('val-phone');
        const valEmail = document.getElementById('val-email');
        const valKyc = document.getElementById('val-kyc-submitted-at');
        const valCreated = document.getElementById('val-created-at');

        if (valDob) valDob.textContent = user.dob || 'N/A';
        if (valGender) valGender.textContent = user.gender || 'N/A';
        if (valMarital) valMarital.textContent = user.marital_status || 'N/A';
        if (valNat) valNat.textContent = user.nationality || 'N/A';
        if (valFather) valFather.textContent = user.father_name || 'N/A';
        if (valMother) valMother.textContent = user.mother_name || 'N/A';
        if (valOcc) valOcc.textContent = user.occupation || 'N/A';
        if (valSrc) valSrc.textContent = user.source_of_income || 'N/A';
        if (valAnn) valAnn.textContent = user.annual_income || 'N/A';
        if (valPhone) valPhone.textContent = user.phone || 'N/A';
        if (valEmail) valEmail.textContent = user.email || 'N/A';
        if (valKyc) valKyc.textContent = user.kyc_submitted_at || 'N/A';
        if (valCreated) valCreated.textContent = user.created_at || 'N/A';

        // Address
        const valStreet = document.getElementById('val-street');
        const valCity = document.getElementById('val-city');
        const valState = document.getElementById('val-state');
        const valPincode = document.getElementById('val-pincode');
        const valCountry = document.getElementById('val-country');
        
        if (valStreet) valStreet.textContent = user.street || 'N/A';
        if (valCity) valCity.textContent = user.city || 'N/A';
        if (valState) valState.textContent = user.state || 'N/A';
        if (valPincode) valPincode.textContent = user.pincode || 'N/A';
        if (valCountry) valCountry.textContent = user.country || 'N/A';

        // Identity
        const valIdType = document.getElementById('val-id-type');
        const valIdNumber = document.getElementById('val-id-number');
        if (valIdType) valIdType.textContent = user.id_type || 'N/A';
        if (valIdNumber) valIdNumber.textContent = user.id_number || 'N/A';

        if (data.id_documents) {
            const docBlocks = document.querySelectorAll('.ds-card .aspect-video');
            if (data.id_documents.front_url && docBlocks.length > 0) {
                let url = data.id_documents.front_url;
                if (!url.startsWith('http://') && !url.startsWith('https://')) {
                    url = url.startsWith('/') ? url : '/Finzo/' + url.replace(/^\//, '');
                }
                docBlocks[0].innerHTML = `<img src="${url}" class="w-full h-full object-cover rounded-xl shadow-lg border border-slate-200" alt="Front Side" onerror="this.parentElement.innerHTML='<div class=\\'flex flex-col items-center justify-center p-6 text-center text-text-secondary\\'><span class=\\'material-symbols-outlined text-3xl opacity-30 mb-1\\'>broken_image</span><span class=\\'text-xs\\'>Preview unavailable</span></div>'">`;
            }
            if (data.id_documents.back_url && docBlocks.length > 1) {
                let url = data.id_documents.back_url;
                if (!url.startsWith('http://') && !url.startsWith('https://')) {
                    url = url.startsWith('/') ? url : '/Finzo/' + url.replace(/^\//, '');
                }
                docBlocks[1].innerHTML = `<img src="${url}" class="w-full h-full object-cover rounded-xl shadow-lg border border-slate-200" alt="Back Side" onerror="this.parentElement.innerHTML='<div class=\\'flex flex-col items-center justify-center p-6 text-center text-text-secondary\\'><span class=\\'material-symbols-outlined text-3xl opacity-30 mb-1\\'>broken_image</span><span class=\\'text-xs\\'>Preview unavailable</span></div>'">`;
            }
        }

        // Account Details
        const valAccType = document.getElementById('val-account-type');
        const valInitialDeposit = document.getElementById('val-initial-deposit');
        const valPin = document.getElementById('val-transaction-pin');
        
        if (valAccType) valAccType.innerHTML = `<span class="status-badge" >${account.account_type || 'Savings'} Account</span>`;
        if (valInitialDeposit) valInitialDeposit.innerHTML = `₹${parseFloat(account.initial_deposit || 0).toLocaleString('en-IN', {minimumFractionDigits: 2})}`;
        if (valPin) valPin.innerHTML = `<div class="flex items-center gap-2.5 font-bold"><span class="font-mono text-base tracking-widest text-slate-600 dark:text-slate-400">••••</span><span class="text-[9px] font-black uppercase tracking-widest px-2 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-900/30">Encrypted</span></div>`;

        // Timeline
        if (data.timeline) {
            renderTimeline(data.timeline);
        }

        // Decision Modals
        const maName = document.getElementById('modal-approve-name');
        if(maName) maName.textContent = user.full_name;
        
        const mrName = document.getElementById('modal-reject-name');
        if(mrName) mrName.textContent = user.full_name;
        
        const mAccType = document.getElementById('modal-acc-type');
        if(mAccType) mAccType.textContent = (account.account_type || 'Savings').charAt(0).toUpperCase() + (account.account_type || 'Savings').slice(1);
        
        if (data.expected_acc) {
           const modalApproveAcc = document.getElementById('modal-approve-acc');
           if(modalApproveAcc) modalApproveAcc.textContent = data.expected_acc;
           
           const accPreview = document.querySelector('.font-code.text-green-700');
           if(accPreview) accPreview.innerHTML = data.expected_acc.replace(/(.{7})(.*)/, '$1<span class="opacity-30">$2</span>');
        }

        // Disable buttons if already processed
        if (user.status === 'active' || user.status === 'rejected') {
            const decisionBtns = document.querySelectorAll('.ds-card:last-child button:not(.btn-ghost)');
            decisionBtns.forEach(btn => {
                btn.disabled = true;
                btn.classList.add('opacity-50', 'cursor-not-allowed');
            });
        }
    }

    function renderTimeline(events) {
        let html = '<div class="absolute left-[11px] top-2 bottom-2 w-0.5 bg-slate-100 dark:bg-white/5"></div>';
        events.forEach((ev, idx) => {
            let dotClass = 'bg-on-tertiary-container text-white';
            let icon = 'check';
            let titleClass = '';
            
            if (ev.type === 'error') {
                dotClass = 'bg-red-500 text-white';
                icon = 'cancel';
                titleClass = 'text-red-500';
            } else if (ev.type === 'active') {
                dotClass = 'bg-blue-500 text-white';
                icon = 'hourglass_empty';
                titleClass = 'text-blue-500';
            } else if (ev.type === 'pending') {
                dotClass = 'bg-slate-200 dark:bg-slate-700 text-slate-500';
                icon = '';
                titleClass = 'opacity-40';
            }

            html += `
              <div class="flex gap-4 relative mb-4">
                <div class="w-6 h-6 rounded-full ${dotClass} flex items-center justify-center z-10 transition-colors shrink-0">
                  ${icon ? `<span class="material-symbols-outlined text-[14px] leading-none" aria-hidden="true">${icon}</span>` : ''}
                </div>
                <div class="flex-1 pb-4">
                  <div class="font-bold text-[13px] ${titleClass}">${ev.status}</div>
                  ${ev.desc ? `<div class="text-[12px] mt-2 p-3 bg-red-50/50 dark:bg-red-900/10 border border-red-100 dark:border-red-900/30 rounded-lg text-slate-700 dark:text-slate-300 leading-relaxed">${ev.desc}</div>` : ''}
                  ${ev.time ? `<div class="text-[11px] font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mt-2">${ev.time}</div>` : ''}
                </div>
              </div>
            `;
        });
        const tlBody = document.getElementById('timeline-body');
        if(tlBody) tlBody.innerHTML = html;
    }

    // Modal Opening
    window.openApproveModal = function() {
        const modal = document.getElementById('approveModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };
    
    window.closeApproveModal = function() {
        const modal = document.getElementById('approveModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    };

    window.openRejectModal = function() {
        const reasonArea = document.getElementById('reject-reason-input');
        const reasonPreview = document.getElementById('rejection-reason-preview');
        if (reasonArea && reasonPreview) {
            const reason = reasonArea.value.trim();
            reasonPreview.textContent = reason ? reason : 'No reason provided';
            if (reason) {
                reasonPreview.classList.remove('italic', 'text-slate-400');
                reasonPreview.classList.add('text-slate-800', 'dark:text-white');
            } else {
                reasonPreview.classList.add('italic', 'text-slate-400');
                reasonPreview.classList.remove('text-slate-800', 'dark:text-white');
            }
        }
        const modal = document.getElementById('rejectModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };
    
    window.closeRejectModal = function() {
        const modal = document.getElementById('rejectModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    };

    // Actions
    const btnApprove = document.getElementById('confirmApprove');
    if(btnApprove) {
        btnApprove.addEventListener('click', async function() {
            window.api.setButtonLoading(this, true, 'Approving...');
            try {
                const response = await window.api.post(`/admin/applications/${userId}/review`, {
                    user_id: userId,
                    action: 'approve'
                });

                if (response.status === 'success' || response.success) {
                    window.showToast('Application Approved! Account Number: ' + (response.account_no || response.data?.account_no), 'success');
                    setTimeout(() => {
                         window.location.href = 'admin-pending-approvals.php';
                    }, 1500);
                } else {
                    window.showToast(response.message || 'Action failed', 'error');
                    window.api.setButtonLoading(this, false, 'Yes, Approve Now');
                }
            } catch (error) {
                console.error(error);
                window.showToast('Connection failed', 'error');
                window.api.setButtonLoading(this, false, 'Yes, Approve Now');
            }
        });
    }

    const btnReject = document.getElementById('confirmReject');
    if(btnReject) {
        btnReject.addEventListener('click', async function() {
            const reasonArea = document.getElementById('reject-reason-input');
            const reason = reasonArea ? reasonArea.value.trim() : '';

            if (!reason) {
                alert('Please enter a rejection reason.');
                closeRejectModal();
                return;
            }

            window.api.setButtonLoading(this, true, 'Rejecting...');

            try {
                const response = await window.api.post(`/admin/applications/${userId}/review`, {
                    user_id: userId,
                    action: 'reject',
                    reason: reason
                });

                if (response.status === 'success' || response.success) {
                    window.showToast('Application Rejected.', 'success');
                    setTimeout(() => {
                        window.location.href = 'admin-pending-approvals.php';
                    }, 1500);
                } else {
                    window.showToast(response.message || 'Action failed', 'error');
                    window.api.setButtonLoading(this, false, 'Yes, Reject Application');
                }
            } catch (error) {
                console.error(error);
                window.showToast('Connection failed', 'error');
                window.api.setButtonLoading(this, false, 'Yes, Reject Application');
            }
        });
    }



    loadDetails();
});
