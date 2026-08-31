/**
 * Finzo Admin — Suspended User Detail
 */
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const userId = urlParams.get('id');

    if (!userId) {
        alert('User ID missing');
        window.location.href = 'admin-suspended-users.php';
        return;
    }

    async function loadDetail() {
        try {
            const response = await window.api.get(`/users/suspended/${userId}`);
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
        // Breadcrumb removed
        
        const avatar = document.getElementById('detail-avatar');
        if (avatar) {
            const avatarUrl = user.avatar_url || user.profile_picture_url;
            const initials = user.initials || (user.full_name ? user.full_name.charAt(0).toUpperCase() : '??');
            if (avatarUrl) {
                avatar.innerHTML = `<img src="${avatarUrl}" class="w-full h-full object-cover rounded-2xl" alt="${user.full_name || 'Customer'}" onerror="this.parentElement.innerHTML='${initials}'">`;
                avatar.style.background = 'none';
            } else {
                avatar.textContent = initials;
                avatar.style.background = '';
            }
        }
        
        document.getElementById('detail-name').textContent = user.full_name || 'N/A';
        const topbarTitle = document.querySelector('.admin-topbar-title');
        if (topbarTitle) topbarTitle.textContent = user.full_name || 'N/A';
        document.getElementById('detail-email').textContent = user.email || 'N/A';
        
        document.getElementById('detail-acc-no').textContent = user.account_no || 'N/A';
        document.getElementById('detail-phone').textContent = user.phone || 'N/A';
        
        const badge = document.getElementById('detail-acc-type-badge');
        badge.textContent = user.account_type || 'N/A';
        badge.className = 'badge badge-success';
        
        document.getElementById('detail-type').textContent = user.account_type ? (user.account_type + ' Account') : 'N/A';
        document.getElementById('detail-opened').textContent = user.opened_at || 'N/A';
        
        const branchName = (user.branch_name && user.branch_name !== 'Unassigned') ? user.branch_name : 'Unassigned / Central';
        const branchCode = user.branch_code ? ` (${user.branch_code})` : '';
        const branchBadge = document.getElementById('detail-branch');
        if (branchBadge) branchBadge.textContent = branchName;
        const branchNameEl = document.getElementById('detail-branch-name');
        if (branchNameEl) {
            branchNameEl.innerHTML = `<span class="inline-flex items-center gap-1.5"><span class="material-symbols-outlined text-sm text-primary">store</span><span>${branchName}</span><span class="font-mono text-xs opacity-60 font-semibold">${branchCode}</span></span>`;
        }

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
            tbody.innerHTML = '<tr><td colspan=\"4\" class=\"py-12 text-center\"><div class=\"flex flex-col items-center justify-center\"><span class=\"material-symbols-outlined text-4xl text-text-muted/30 mb-3\">inventory_2</span><div class=\"text-sm font-bold text-text-muted uppercase tracking-widest\">No recent transactions recorded</div></div></td></tr>';
            return;
        }
        const html = txs.map(tx => `
            <tr style="border-bottom:1px solid var(--border)">
                <td style="padding:12px 16px;font-size:12px;color:var(--text-secondary)">${tx.date}</td>
                <td style="padding:12px 16px;font-size:13px;font-weight:600">${tx.description}</td>
                <td style="padding:12px 16px"><span class="badge ${tx.type === 'Debit' ? 'badge-danger' : 'badge-success'}">${tx.type}</span></td>
                <td style="padding:12px 16px;text-align:right;font-size:13px;font-weight:800;color:var(${tx.type === 'Debit' ? '--red' : '--green'})">${tx.amount}</td>
            </tr>
        `).join('');
        tbody.innerHTML = html;
    }

    window.openReinstateModal = () => document.getElementById('reinstate-modal').classList.remove('hidden');
    window.closeModal = () => document.getElementById('reinstate-modal').classList.add('hidden');

    window.openDeleteModal = () => {
        const modal = document.getElementById('delete-modal');
        if (modal) {
            document.getElementById('delete-modal-name').textContent = document.getElementById('detail-name').textContent;
            modal.classList.remove('hidden');
        }
    };
    window.closeDeleteModal = () => {
        const modal = document.getElementById('delete-modal');
        if (modal) {
            modal.classList.add('hidden');
            const pwd = document.getElementById('delete-admin-password');
            if (pwd) pwd.value = '';
        }
    };
    window.processDelete = async () => {
        const btn = document.getElementById('delete-confirm-btn');
        const password = document.getElementById('delete-admin-password')?.value;
        if (!password) {
            window.FinzoUtils.showToast('Administrator password is required', 'error');
            return;
        }
        window.api.setButtonLoading(btn, true, 'Confirming...');
        try {
            const response = await window.api.patch('/users/' + userId, { action: 'delete', password: password });
            if (response.status === 'success' || response.success) {
                window.FinzoUtils.showToast('Account archived successfully', 'success');
                setTimeout(() => window.location.href = 'admin-deleted-users.php', 1500);
            } else {
                alert(response.message || 'Action failed.');
                window.api.setButtonLoading(btn, false);
            }
        } catch (error) {
            alert('Action failed.');
            window.api.setButtonLoading(btn, false);
        }
    };

    window.processReinstate = async () => {
        const btn = document.getElementById('confirm-btn');
        window.api.setButtonLoading(btn, true, 'Confirming...');
        
        try {
            const response = await window.api.patch('/users/' + userId, { action: 'reinstate' });

            if (response.status === 'success' || response.success) {
                window.FinzoUtils.showToast('Account reinstated. Redirecting...', 'success');
                setTimeout(() => window.location.href = 'admin-suspended-users.php', 1500);
            } else {
                alert(response.message);
                window.api.setButtonLoading(btn, false);
            }
        } catch (error) {
            alert('Action failed.');
            window.api.setButtonLoading(btn, false);
        }
    };

    loadDetail();
});
