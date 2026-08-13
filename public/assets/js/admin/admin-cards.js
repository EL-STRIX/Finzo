(function() {
    let requests = [];

    async function loadRequests() {
        try {
            const status = document.getElementById('filter-status').value;
            const response = await window.api.get(`/admin/card-requests?status=${status}`);
            if (response.success) {
                requests = response.requests || [];
                updateStats(requests);
                renderRequests();
            } else if (response.status === 404) {
                 window.showToast('Backend endpoint for card requests not fully implemented yet.', 'warning');
                 updateStats([]);
                 renderRequests();
            }
        } catch (error) {
            console.error('Failed to load card requests:', error);
            updateStats([]);
            renderRequests();
        }
    }

    function updateStats(reqs) {
        let pending = 0, active = 0, blocked = 0;
        
        reqs.forEach(req => {
            if (req.status === 'pending') pending++;
            else if (req.status === 'active') active++;
            else if (req.status === 'blocked' || req.status === 'closed') blocked++;
        });

        document.getElementById('stat-pending').textContent = pending;
        document.getElementById('stat-active').textContent = active;
        document.getElementById('stat-blocked').textContent = blocked;

        document.getElementById('stat-pending').classList.remove('skeleton');
        document.getElementById('stat-active').classList.remove('skeleton');
        document.getElementById('stat-blocked').classList.remove('skeleton');
    }

    function renderRequests() {
        const tbody = document.getElementById('cards-tbody');
        if (!tbody) return;

        if (!requests.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="py-12 text-center text-text-muted">No card requests found for the selected filter.</td></tr>';
            return;
        }

        const query = document.getElementById('card-search')?.value.toLowerCase() || '';
        const filtered = requests.filter(r => 
            (r.user_name && r.user_name.toLowerCase().includes(query)) || 
            (r.product_name && r.product_name.toLowerCase().includes(query))
        );

        let html = '';
        filtered.forEach(req => {
            let statusClass = 'badge-neutral';
            if (req.status === 'pending') statusClass = 'badge-warning';
            else if (req.status === 'active') statusClass = 'badge-success';
            else if (req.status === 'rejected' || req.status === 'blocked' || req.status === 'closed') statusClass = 'badge-danger';

            const date = new Date(req.created_at).toLocaleDateString();
            const maskedCard = req.card_number ? `**** **** **** ${req.card_number.slice(-4)}` : 'N/A';
            
            html += `
            <tr class="hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">
                <td class="font-mono text-xs opacity-60">#${req.id}</td>
                <td class="font-bold">${req.user_name || 'User ' + req.user_id}</td>
                <td class="text-sm">${req.product_name || 'Card'}</td>
                <td class="text-sm font-mono opacity-80">${maskedCard}</td>
                <td class="text-sm opacity-80">${date}</td>
                <td><span class="status-badge ${statusClass} uppercase">${req.status}</span></td>
                <td class="text-right">
                    <button class="btn btn-secondary !px-3 !py-1.5 !text-[10px] !rounded-lg inline-flex items-center gap-1 btn-review" data-id="${req.id}">
                        <span class="material-symbols-outlined !text-[14px]">${req.status === 'pending' ? 'gavel' : 'visibility'}</span> 
                        ${req.status === 'pending' ? 'Review' : 'View'}
                    </button>
                    ${req.status === 'active' ? `
                    <button class="btn btn-secondary !px-3 !py-1.5 !text-[10px] !rounded-lg inline-flex items-center gap-1 text-error hover:bg-error-bg hover:border-error btn-block ml-1" data-id="${req.id}">
                        <span class="material-symbols-outlined !text-[14px]">block</span> Block
                    </button>` : ''}
                </td>
            </tr>
            `;
        });
        tbody.innerHTML = html;
        bindRowEvents();
    }

    function bindRowEvents() {
        document.querySelectorAll('.btn-review').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.currentTarget.dataset.id;
                openReviewModal(id);
            });
        });

        document.querySelectorAll('.btn-block').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const id = e.currentTarget.dataset.id;
                const reason = prompt('Enter reason for blocking this card:');
                if (reason === null) return;
                
                try {
                    const res = await window.api.post('/admin/cards/force-close', { card_id: id, reason: reason });
                    if (res.success) {
                        window.showToast('Card blocked successfully', 'success');
                        loadRequests();
                    } else {
                        window.showToast(res.message || 'Failed to block card', 'error');
                    }
                } catch (err) {
                    window.showToast('Error communicating with backend API', 'error');
                }
            });
        });
    }

    function openReviewModal(id) {
        const modal = document.getElementById('review-modal');
        const req = requests.find(r => r.id == id);
        
        if (!req) return;

        document.getElementById('review-request-id').value = id;
        document.getElementById('review-user').textContent = req.user_name || `User ID: ${req.user_id}`;
        document.getElementById('review-product').textContent = req.product_name || 'N/A';
        document.getElementById('review-notes').value = '';

        const btnApprove = document.getElementById('btn-approve-card');
        const btnReject = document.getElementById('btn-reject-card');

        if (req.status === 'pending') {
            btnApprove.classList.remove('hidden');
            btnReject.classList.remove('hidden');
        } else {
            btnApprove.classList.add('hidden');
            btnReject.classList.add('hidden');
        }

        modal.classList.remove('hidden');
    }

    async function handleReviewAction(action) {
        const id = document.getElementById('review-request-id').value;
        const notes = document.getElementById('review-notes').value;

        if (!id) return;

        const endpoint = action === 'approve' ? `/admin/card-requests/issue` : `/admin/card-requests/reject`;
        
        const btn = action === 'approve' ? document.getElementById('btn-approve-card') : document.getElementById('btn-reject-card');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="loader"></span> Processing...';
        btn.disabled = true;

        try {
            const res = await window.api.post(endpoint, { id: id, notes: notes });
            
            if (res.success) {
                window.showToast(`Card request ${action}d successfully`, 'success');
                document.getElementById('review-modal').classList.add('hidden');
                loadRequests();
            } else {
                window.showToast(res.message || `Failed to ${action} request`, 'error');
            }
        } catch (err) {
            console.error(err);
            window.showToast(`Error processing request. Ensure backend route is active.`, 'error');
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }

    function init() {
        loadRequests();

        document.getElementById('filter-status')?.addEventListener('change', loadRequests);
        document.getElementById('card-search')?.addEventListener('input', renderRequests);

        document.getElementById('btn-approve-card')?.addEventListener('click', () => handleReviewAction('approve'));
        document.getElementById('btn-reject-card')?.addEventListener('click', () => handleReviewAction('reject'));
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
