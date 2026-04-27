/**
 * Finzo Admin — Review Application
 */
document.addEventListener('DOMContentLoaded', () => {

    const urlParams = new URLSearchParams(window.location.search);
    const userId = urlParams.get('id');

    if (!userId) {
        alert('User ID is missing');
        // window.location.href = 'admin-pending-approvals.html';
        return;
    }

    async function loadDetails() {
        try {
            const response = await window.api.get(`/admin/review-application.php?id=${userId}`);
            if (response.status === 'success' || response.success) {
                const data = response.data || response;
                populateUI(data);
            } else {
                window.FinzoUtils.showToast(response.message || 'Failed to load details', 'error');
            }
        } catch (error) {
            console.error('Error loading application:', error);
            window.FinzoUtils.showToast('Connection error', 'error');
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
            statusBadge.className = `status-badge ${user.status === 'pending' ? 'badge-warn' : (user.status === 'active' ? 'badge-success' : 'badge-error')} px-4 py-2 text-[11px]`;
        }

        const adminAvatar = document.getElementById('top-admin-avatar');
        if (adminAvatar) adminAvatar.textContent = user.initials || (user.full_name ? user.full_name.charAt(0) : 'A');

        // Personal Details
        const personalFields = document.querySelectorAll('#personal-fields .font-bold');
        if (personalFields.length >= 10) {
            personalFields[0].textContent = user.full_name;
            personalFields[1].textContent = user.dob;
            personalFields[2].textContent = user.gender;
            personalFields[3].textContent = user.father_name;
            personalFields[4].textContent = user.occupation;
            personalFields[5].textContent = user.annual_income;
            personalFields[6].textContent = user.phone;
            personalFields[7].textContent = user.email;
            personalFields[8].textContent = user.kyc_submitted_at;
            personalFields[9].textContent = user.created_at;
        }

        // Address
        const addressFields = document.querySelectorAll('#address-fields .font-bold');
        if (addressFields.length >= 5) {
            addressFields[0].textContent = user.street;
            addressFields[1].textContent = user.city;
            addressFields[2].textContent = user.state;
            addressFields[3].textContent = user.pincode;
            addressFields[4].textContent = user.country;
        }

        // Identity
        const identityFields = document.querySelectorAll('#identity-fields .font-bold');
        if (identityFields.length >= 2) {
            identityFields[0].textContent = user.id_type;
            identityFields[1].textContent = user.id_number;
        }

        if (data.id_documents) {
            const docBlocks = document.querySelectorAll('.admin-card .aspect-video');
            if (data.id_documents.front_url && docBlocks.length > 0) {
                docBlocks[0].innerHTML = `<img src="../../${data.id_documents.front_url.replace('../../','')}" class="w-full h-full object-cover rounded-xl shadow-lg border border-slate-200">`;
            }
            if (data.id_documents.back_url && docBlocks.length > 1) {
                docBlocks[1].innerHTML = `<img src="../../${data.id_documents.back_url.replace('../../','')}" class="w-full h-full object-cover rounded-xl shadow-lg border border-slate-200">`;
            }
        }

        // Account Details
        const accountBlock = document.getElementById('account-fields');
        if (accountBlock) {
             const accTypeDiv = accountBlock.querySelector('div:first-child');
             if(accTypeDiv) accTypeDiv.innerHTML = `<label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Account Type</label><span class="status-badge" style="background:#eef2ff; color:#4f46e5;">${account.account_type || 'Savings'} Account</span>`;
             
             const initDepDiv = accountBlock.querySelector('div:nth-child(2) .font-bold');
             if(initDepDiv) initDepDiv.textContent = `₹${parseFloat(account.initial_deposit || 0).toLocaleString('en-IN', {minimumFractionDigits: 2})}`;
             
             const pinDiv = accountBlock.querySelector('div:last-child');
             if (pinDiv) pinDiv.innerHTML = `<label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Transaction PIN</label><div class="flex items-center gap-2 text-on-tertiary-container font-black"><span class="material-symbols-outlined text-lg">check_circle</span><span>Set ✅</span></div>`;
        }

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
            const decisionBtns = document.querySelectorAll('.admin-card:last-child button:not(.btn-ghost)');
            decisionBtns.forEach(btn => {
                btn.disabled = true;
                btn.classList.add('opacity-50', 'cursor-not-allowed');
            });
        }
    }

    function renderTimeline(events) {
        let html = '<div class="absolute left-[11px] top-2 bottom-2 w-0.5 bg-slate-100 dark:bg-white/5"></div>';
        events.forEach((ev, idx) => {
            const isLast = idx === events.length - 1;
            const isDecisionPending = ev.status === 'Decision Pending';
            
            html += `
              <div class="flex gap-4 relative mb-4">
                <div class="w-6 h-6 rounded-full ${ev.completed ? 'bg-on-tertiary-container' : (isDecisionPending ? 'bg-slate-200 dark:bg-slate-700' : 'bg-blue-500')} text-white flex items-center justify-center z-10 transition-colors">
                  <span class="material-symbols-outlined text-[14px] leading-none">${ev.completed ? 'check' : (isDecisionPending ? '' : 'hourglass_empty')}</span>
                </div>
                <div>
                  <div class="font-bold text-[13px] ${!ev.completed && !isDecisionPending ? 'text-blue-500' : (isDecisionPending ? 'opacity-40' : '')}">${ev.status}</div>
                  ${ev.time ? `<div class="text-[11px] opacity-60 italic mt-0.5">${ev.time}</div>` : ''}
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
                const response = await window.api.post('/admin/review-application.php', {
                    user_id: userId,
                    action: 'approve'
                });

                if (response.status === 'success' || response.success) {
                    window.FinzoUtils.showToast('Application Approved! Account Number: ' + (response.account_no || response.data?.account_no), 'success');
                    setTimeout(() => {
                         window.location.href = 'admin-pending-approvals.html';
                    }, 1500);
                } else {
                    window.FinzoUtils.showToast(response.message || 'Action failed', 'error');
                    window.api.setButtonLoading(this, false, 'Yes, Approve Now');
                }
            } catch (error) {
                console.error(error);
                window.FinzoUtils.showToast('Connection failed', 'error');
                window.api.setButtonLoading(this, false, 'Yes, Approve Now');
            }
        });
    }

    const btnReject = document.getElementById('confirmReject');
    if(btnReject) {
        btnReject.addEventListener('click', async function() {
            const reasonArea = document.querySelector('textarea[placeholder*="rejection"]');
            const reason = reasonArea ? reasonArea.value.trim() : '';

            if (!reason) {
                alert('Please enter a rejection reason.');
                closeRejectModal();
                return;
            }

            window.api.setButtonLoading(this, true, 'Rejecting...');

            try {
                const response = await window.api.post('/admin/review-application.php', {
                    user_id: userId,
                    action: 'reject',
                    reason: reason
                });

                if (response.status === 'success' || response.success) {
                    window.FinzoUtils.showToast('Application Rejected.', 'success');
                    setTimeout(() => {
                        window.location.href = 'admin-pending-approvals.html';
                    }, 1500);
                } else {
                    window.FinzoUtils.showToast(response.message || 'Action failed', 'error');
                    window.api.setButtonLoading(this, false, 'Yes, Reject Application');
                }
            } catch (error) {
                console.error(error);
                window.FinzoUtils.showToast('Connection failed', 'error');
                window.api.setButtonLoading(this, false, 'Yes, Reject Application');
            }
        });
    }

    const btnNotes = document.querySelector('.admin-btn-secondary:nth-child(4)'); // Need a specific selector, let's use the save notes text
    const saveNotesBtns = document.querySelectorAll('.admin-btn-secondary');
    saveNotesBtns.forEach(btn => {
        if(btn.textContent.includes('Save Notes')) {
            btn.addEventListener('click', async function() {
                const notesArea = this.previousElementSibling;
                const notes = notesArea ? notesArea.value.trim() : '';
                
                window.api.setButtonLoading(this, true);
                
                try {
                    const response = await window.api.post('/admin/review-application.php', {
                        user_id: userId,
                        action: 'save_notes',
                        notes: notes
                    });
                    if (response.status === 'success' || response.success) {
                        window.FinzoUtils.showToast('Notes saved successfully.', 'success');
                    } else {
                        window.FinzoUtils.showToast(response.message || 'Error saving notes', 'error');
                    }
                } catch (error) {
                    console.error(error);
                    window.FinzoUtils.showToast('Connection failed', 'error');
                } finally {
                    window.api.setButtonLoading(this, false, 'Save Notes');
                }
            });
        }
    });

    loadDetails();
});
