/**
 * Finzo Admin — Suspended User Detail
 */
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const userId = urlParams.get('id');

    if (!userId) {
        alert('User ID missing');
        window.location.href = 'admin-suspended-users.html';
        return;
    }

    async function loadDetail() {
        try {
            const response = await window.api.get(`/admin/suspended-user-detail.php?id=${userId}`);
            if (response.status === 'success' || response.success) {
                const data = response.data || response;
                populateUI(data.user);
                renderTransactions(data.transactions);
            } else {
                window.FinzoUtils.showToast(response.message || 'Failed to load details', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            window.FinzoUtils.showToast('Network error loading details', 'error');
        }
    }

    function populateUI(user) {
        document.getElementById('bc-name').textContent = user.full_name || 'N/A';
        
        const avatar = document.getElementById('detail-avatar');
        if (avatar) {
            if (user.avatar_url) {
                avatar.innerHTML = `<img src="${user.avatar_url}" class="w-full h-full object-cover rounded-2xl">`;
                avatar.style.background = 'none';
            } else {
                avatar.textContent = user.initials || '??';
                avatar.style.background = '';
            }
        }
        
        document.getElementById('detail-name').textContent = user.full_name || 'N/A';
        document.getElementById('detail-email').textContent = user.email || 'N/A';
        
        document.getElementById('detail-acc-no').textContent = user.account_no || 'N/A';
        document.getElementById('detail-phone').textContent = user.phone || 'N/A';
        
        const badge = document.getElementById('detail-acc-type-badge');
        badge.textContent = user.account_type || 'N/A';
        badge.className = 'badge badge-green';
        
        document.getElementById('detail-type').textContent = user.account_type ? (user.account_type + ' Account') : 'N/A';
        document.getElementById('detail-opened').textContent = user.opened_at || 'N/A';
        
        const balance = parseFloat(user.balance || 0).toLocaleString('en-IN', { style: 'currency', currency: 'INR' });
        document.getElementById('detail-balance').textContent = balance;
        
        const snap = document.getElementById('snap-balance');
        if (snap) snap.textContent = balance;
        
        document.getElementById('detail-reason').textContent = user.suspension_reason || 'Administrative Suspensions';
        document.getElementById('detail-reason-detail').textContent = user.suspension_notes || 'This account was suspended for administrative review.';
        document.getElementById('detail-by').textContent = user.suspended_by || 'System / Admin';
        document.getElementById('detail-date').textContent = user.suspended_date || 'N/A';
        document.getElementById('detail-time').textContent = user.suspended_time || 'N/A';
        
        document.getElementById('detail-notes').textContent = user.admin_notes || 'No administrative notes available.';
        document.getElementById('detail-notes-by').textContent = user.notes_footer || '';

        const modalName = document.getElementById('modal-name');
        if (modalName) modalName.textContent = user.full_name;
        
        const modalAcc = document.getElementById('modal-acc');
        if (modalAcc) modalAcc.textContent = user.account_no || 'N/A';
    }

    function renderTransactions(txs) {
        const tbody = document.getElementById('transactions-body');
        if (!txs || txs.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center py-8 opacity-40">No recent transactions recorded</td></tr>';
            return;
        }
        const html = txs.map(tx => `
            <tr style="border-bottom:1px solid var(--border)">
                <td style="padding:12px 16px;font-size:12px;color:var(--text-secondary)">${tx.date}</td>
                <td style="padding:12px 16px;font-size:13px;font-weight:600">${tx.description}</td>
                <td style="padding:12px 16px"><span class="badge ${tx.type === 'Debit' ? 'badge-red' : 'badge-green'}">${tx.type}</span></td>
                <td style="padding:12px 16px;text-align:right;font-size:13px;font-weight:800;color:var(${tx.type === 'Debit' ? '--red' : '--green'})">${tx.amount}</td>
            </tr>
        `).join('');
        tbody.innerHTML = html;
    }

    window.openReinstateModal = () => document.getElementById('reinstate-modal').classList.remove('hidden');
    window.closeModal = () => document.getElementById('reinstate-modal').classList.add('hidden');

    window.processReinstate = async () => {
        const btn = document.getElementById('confirm-btn');
        window.api.setButtonLoading(btn, true);
        
        try {
            const response = await window.api.post('/admin/user-action.php', {
                user_id: userId,
                action: 'reinstate'
            });

            if (response.status === 'success' || response.success) {
                window.FinzoUtils.showToast('Account reinstated. Redirecting...', 'success');
                setTimeout(() => window.location.href = 'admin-suspended-users.html', 1500);
            } else {
                alert(response.message);
                window.api.setButtonLoading(btn, false, '<span class="material-symbols-outlined" style="font-size:16px">check</span> Confirm & Reinstate');
            }
        } catch (error) {
            alert('Action failed.');
            window.api.setButtonLoading(btn, false, '<span class="material-symbols-outlined" style="font-size:16px">check</span> Confirm & Reinstate');
        }
    };

    loadDetail();
});
