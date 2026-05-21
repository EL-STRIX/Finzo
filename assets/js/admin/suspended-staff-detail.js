/**
 * Finzo Admin — Suspended Staff Detail
 */
document.addEventListener('DOMContentLoaded', () => {

    const urlParams = new URLSearchParams(window.location.search);
    const staffId = urlParams.get('id');

    if (!staffId) {
        alert('Staff ID missing');
        window.location.href = 'admin-suspended-staff.html';
        return;
    }

    async function loadDetail() {
        try {
            const response = await window.api.get(`/admin/suspended-staff-detail.php?id=${staffId}`);
            if (response.status === 'success' || response.success) {
                const data = response.data || response;
                populateUI(data.staff);
                renderHistory(data.history);
                // The global window.AdminPortal handles the rest
            } else {
                window.FinzoUtils.showToast(response.message || 'Record not found', 'error');
                setTimeout(() => window.location.href = 'admin-suspended-staff.html', 2000);
            }
        } catch (error) {
            console.error('Error loading detail:', error);
            window.FinzoUtils.showToast('Connection error', 'error');
        }
    }

    function populateUI(staff) {
        document.getElementById('bc-name').textContent = staff.full_name || 'N/A';
        
        // Avatar handling
        const av = document.getElementById('detail-avatar');
        if (staff.profile_photo) {
            av.innerHTML = `<img src="../../${staff.profile_photo}" class="w-full h-full object-cover rounded-lg">`;
            av.style.background = 'none';
        } else {
            av.textContent = staff.initials || (staff.full_name ? staff.full_name.charAt(0) : 'S');
            av.className = 'avatar-lg premium-gradient-primary';
            av.style.background = '';
        }

        document.getElementById('detail-name').textContent = staff.full_name || 'N/A';
        document.getElementById('detail-email').textContent = staff.email || 'N/A';
        
        document.getElementById('detail-staff-id').textContent = staff.staff_id || 'N/A';
        const badge = document.getElementById('detail-staff-id-badge');
        if (badge) badge.textContent = staff.staff_id || 'N/A';

        document.getElementById('detail-phone').textContent = staff.phone || 'N/A';
        document.getElementById('detail-branch').textContent = staff.branch_name || 'N/A';
        document.getElementById('detail-onboarding').textContent = staff.onboarding_date || 'N/A';

        
        document.getElementById('detail-reason').textContent = staff.suspension_reason || 'Administrative Review';
        document.getElementById('detail-reason-detail').textContent = staff.suspension_notes || `Staff account suspended for ${staff.suspension_reason || 'administrative reasons'}. Policy compliance review pending.`;
        document.getElementById('detail-by').textContent = staff.suspended_by || 'Senior Administrator';
        document.getElementById('detail-date').textContent = staff.suspended_at || 'N/A';
        document.getElementById('detail-duration').textContent = staff.duration || 'Indefinite';



        const mName = document.getElementById('modal-name');
        if (mName) mName.textContent = staff.full_name || 'N/A';

        const mId = document.getElementById('modal-id');
        if (mId) mId.textContent = staff.staff_id || 'N/A';
    }

    function renderHistory(txs) {
        const tbody = document.getElementById('staff-transactions-body');
        if (!txs || txs.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center py-12 opacity-40 font-bold">No historical transactions found</td></tr>';
            return;
        }
        const html = txs.map(t => `
            <tr style="border-bottom:1px solid var(--border)" class="hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-colors">
                <td style="padding:16px 24px;font-size:13px;font-weight:700;color:var(--text-primary)">
                   <div class="flex items-center gap-2">
                     <span class="material-symbols-outlined text-[16px] ${t.type === 'DEPOSIT' ? 'text-green-500' : 'text-red-500'}">
                        ${t.type === 'DEPOSIT' ? 'vertical_align_bottom' : 'vertical_align_top'}
                     </span>
                     ${t.type}
                   </div>
                </td>
                <td style="padding:16px 24px;font-size:12px;color:var(--text-secondary);font-weight:600">${t.timestamp}</td>
                <td style="padding:16px 24px;text-align:right;font-size:13px;font-weight:800;color:var(--text-primary)">${t.amount}</td>
                <td style="padding:16px 24px;text-align:center">
                    <span class="badge ${t.status === 'COMPLETED' || t.status === 'SUCCESS' ? 'badge-green' : t.status === 'FAILED' ? 'badge-red' : 'badge-amber'}">${t.status}</span>
                </td>
            </tr>
        `).join('');
        tbody.innerHTML = html;
    }

    window.openReinstateModal = () => {
        document.getElementById('reinstate-modal').classList.remove('hidden');
        document.getElementById('reinstate-modal').classList.add('flex');
    };

    window.closeModal = () => {
        document.getElementById('reinstate-modal').classList.add('hidden');
        document.getElementById('reinstate-modal').classList.remove('flex');
        const pwd = document.getElementById('admin-password');
        if(pwd) pwd.value = '';
    };

    window.processReinstate = async () => {
        const btn = document.getElementById('confirm-btn');
        const password = document.getElementById('admin-password')?.value;

        if(!password) {
            window.FinzoUtils.showToast('Administrator password is required', 'error');
            return;
        }

        window.api.setButtonLoading(btn, true);
        
        try {
            const response = await window.api.post('/admin/staff-action.php', {
                staff_id: staffId,
                action: 'reinstate',
                password: password
            });

            if (response.success || response.status === 'success') {
                window.FinzoUtils.showToast('Staff account reinstated. Redirecting...', 'success');
                setTimeout(() => window.location.href = 'admin-suspended-staff.html', 1500);
            } else {
                window.FinzoUtils.showToast(response.message || 'Action failed', 'error');
                window.api.setButtonLoading(btn, false, 'Confirm & Authorize');
            }
        } catch (error) {
            console.error('Reinstate failed:', error);
            window.FinzoUtils.showToast('Server error', 'error');
            window.api.setButtonLoading(btn, false, 'Confirm & Authorize');
        }
    };

    loadDetail();
});
