(function() {
    let applications = [];

    async function loadApplications() {
        try {
            const status = document.getElementById('filter-status').value;
            // AdminLoanController::applications() is mapped via status parameter
            const response = await window.api.get(`/admin/loan-applications?status=${status}`);
            if (response.success) {
                applications = response.applications || [];
                updateStats(applications);
                renderApplications();
            } else if (response.status === 404) {
                 window.ui.showToast('Backend endpoint for listing applications not fully implemented yet.', 'warning');
                 updateStats([]);
                 renderApplications();
            }
        } catch (error) {
            console.error('Failed to load applications:', error);
            updateStats([]);
            renderApplications();
        }
    }

    function updateStats(apps) {
        let pending = 0, active = 0, defaulted = 0;
        
        apps.forEach(app => {
            if (app.status === 'pending') pending++;
            else if (app.status === 'approved' || app.status === 'active') active++;
            else if (app.status === 'foreclosed' || app.status === 'defaulted') defaulted++;
        });

        document.getElementById('stat-pending').textContent = pending;
        document.getElementById('stat-active').textContent = active;
        document.getElementById('stat-defaulted').textContent = defaulted;

        document.getElementById('stat-pending').classList.remove('skeleton');
        document.getElementById('stat-active').classList.remove('skeleton');
        document.getElementById('stat-defaulted').classList.remove('skeleton');
    }

    function renderApplications() {
        const tbody = document.getElementById('loans-tbody');
        if (!tbody) return;

        if (!applications.length) {
            tbody.innerHTML = '<tr><td colspan="8" class="py-12 text-center text-text-muted">No applications found for the selected filter.</td></tr>';
            return;
        }

        const query = document.getElementById('loan-search')?.value.toLowerCase() || '';
        const filtered = applications.filter(a => 
            (a.user_name && a.user_name.toLowerCase().includes(query)) || 
            (a.product_name && a.product_name.toLowerCase().includes(query))
        );

        let html = '';
        filtered.forEach(app => {
            let statusClass = 'badge-neutral';
            if (app.status === 'pending') statusClass = 'badge-warning';
            else if (app.status === 'approved' || app.status === 'active') statusClass = 'badge-success';
            else if (app.status === 'rejected' || app.status === 'foreclosed') statusClass = 'badge-danger';

            const date = new Date(app.created_at).toLocaleDateString();
            
            html += `
            <tr class="hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">
                <td class="font-mono text-xs opacity-60">#${app.id}</td>
                <td class="font-bold">${app.user_name || 'User ' + app.user_id}</td>
                <td class="text-sm">${app.product_name || 'Loan'}</td>
                <td class="text-sm font-bold text-primary">₹${window.utils.formatCurrency(app.amount)}</td>
                <td class="text-sm">${app.tenure_months} mo</td>
                <td class="text-sm opacity-80">${date}</td>
                <td><span class="status-badge ${statusClass} uppercase">${app.status}</span></td>
                <td class="text-right">
                    <button class="btn btn-secondary !px-3 !py-1.5 !text-[10px] !rounded-lg inline-flex items-center gap-1 btn-review" data-id="${app.id}">
                        <span class="material-symbols-outlined !text-[14px]">${app.status === 'pending' ? 'gavel' : 'visibility'}</span> 
                        ${app.status === 'pending' ? 'Review' : 'View'}
                    </button>
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
    }

    function openReviewModal(id) {
        const modal = document.getElementById('review-modal');
        const app = applications.find(a => a.id == id);
        
        if (!app) return;

        document.getElementById('review-loan-id').value = id;
        document.getElementById('review-user').textContent = app.user_name || `User ID: ${app.user_id}`;
        document.getElementById('review-product').textContent = app.product_name || 'N/A';
        document.getElementById('review-amount').textContent = `₹${window.utils.formatCurrency(app.amount)}`;
        document.getElementById('review-tenure').textContent = `${app.tenure_months} Months`;
        document.getElementById('review-notes').value = '';

        const btnApprove = document.getElementById('btn-approve-loan');
        const btnReject = document.getElementById('btn-reject-loan');

        if (app.status === 'pending') {
            btnApprove.classList.remove('hidden');
            btnReject.classList.remove('hidden');
        } else {
            btnApprove.classList.add('hidden');
            btnReject.classList.add('hidden');
        }

        modal.classList.remove('hidden');
    }

    async function handleReviewAction(action) {
        const id = document.getElementById('review-loan-id').value;
        const notes = document.getElementById('review-notes').value;

        if (!id) return;

        const endpoint = action === 'approve' ? `/admin/loan-applications/approve` : `/admin/loan-applications/reject`;
        
        const btn = action === 'approve' ? document.getElementById('btn-approve-loan') : document.getElementById('btn-reject-loan');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="loader"></span> Processing...';
        btn.disabled = true;

        try {
            const res = await window.api.post(endpoint, { id: id, notes: notes });
            
            if (res.success) {
                window.ui.showToast(`Application ${action}d successfully`, 'success');
                document.getElementById('review-modal').classList.add('hidden');
                loadApplications();
            } else {
                window.ui.showToast(res.message || `Failed to ${action} application`, 'error');
            }
        } catch (err) {
            console.error(err);
            window.ui.showToast(`Error processing application. Ensure backend route is active.`, 'error');
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }

    function init() {
        loadApplications();

        document.getElementById('filter-status')?.addEventListener('change', loadApplications);
        document.getElementById('loan-search')?.addEventListener('input', renderApplications);

        document.getElementById('btn-approve-loan')?.addEventListener('click', () => handleReviewAction('approve'));
        document.getElementById('btn-reject-loan')?.addEventListener('click', () => handleReviewAction('reject'));
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
