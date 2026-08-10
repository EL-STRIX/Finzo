/**
 * Finzo Admin — Pending Approvals Handler
 * Loads KYC/account pending approval queue from /api/v1/users/pending
 */
(function () {
    let currentPage = 1;
    const perPage = 20;
    let totalPages = 1;
    let searchTimeout = null;

    const tbody = document.getElementById('pending-tbody');
    const searchInput = document.getElementById('search-pending');
    const countEl = document.getElementById('pending-count');
    const paginationEl = document.getElementById('pending-pagination');
    const emptyStateEl = document.getElementById('pending-empty');

    function getFilters() {
        return {
            search: searchInput ? searchInput.value.trim() : '',
            page: currentPage,
            per_page: perPage,
        };
    }

    function showSkeleton() {
        if (!tbody) return;
        tbody.innerHTML = Array.from({ length: 5 }, () => `
            <tr class="skeleton-row">
                <td><div class="skeleton h-10 w-full rounded-xl"></div></td>
                <td><div class="skeleton h-6 w-32 rounded-lg"></div></td>
                <td><div class="skeleton h-6 w-24 rounded-lg"></div></td>
                <td><div class="skeleton h-6 w-20 rounded-lg"></div></td>
                <td><div class="skeleton h-8 w-24 float-right rounded-lg"></div></td>
            </tr>`).join('');
    }

    function renderRow(u) {
        const initials = (u.full_name || 'U').split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
        const avatarBg = window.FinzoUtils ? window.FinzoUtils.getAvatarGradient(initials) : '#3b82f6';
        const avatar = u.profile_photo
            ? `<img src="/Finzo/public/${u.profile_photo}" class="w-9 h-9 rounded-full object-cover" alt="${u.full_name}">`
            : `<span class="w-9 h-9 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0" style="background:${avatarBg}">${initials}</span>`;
        const date = u.created_at ? new Date(u.created_at).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' }) : '—';
        const kycBadge = u.kyc_status === 'submitted'
            ? `<span class="ds-badge ds-badge-warning">Submitted</span>`
            : `<span class="ds-badge ds-badge-secondary">${u.kyc_status || 'Pending'}</span>`;
        const acType = u.account_type ? `<span class="text-xs text-text-secondary">${u.account_type}</span>` : '—';

        return `<tr>
            <td>
                <div class="flex items-center gap-3">
                    ${avatar}
                    <div>
                        <div class="font-semibold text-text-primary text-sm">${u.full_name || '—'}</div>
                        <div class="text-xs text-text-secondary">${u.email || '—'}</div>
                        <div class="text-xs text-text-muted font-mono">${u.customer_id || '—'}</div>
                    </div>
                </div>
            </td>
            <td>${kycBadge}</td>
            <td>${acType}</td>
            <td class="text-sm text-text-secondary">${date}</td>
            <td class="text-right">
                <a href="admin-review-application.php?id=${u.id}" class="ds-btn ds-btn-sm ds-btn-primary">
                    <span class="material-symbols-outlined text-[16px]">rate_review</span> Review
                </a>
            </td>
        </tr>`;
    }

    async function loadPending() {
        showSkeleton();
        try {
            const params = getFilters();
            const qs = new URLSearchParams(params).toString();
            const res = await window.api.get('/users/pending?' + qs);

            const users = res.data?.users || res.users || [];
            const meta = res.data?.meta || res.meta || {};
            totalPages = meta.total_pages || 1;
            const total = meta.total || users.length;

            if (countEl) countEl.textContent = total;

            if (!users.length) {
                if (tbody) tbody.innerHTML = '';
                if (emptyStateEl) emptyStateEl.classList.remove('hidden');
                if (paginationEl) paginationEl.innerHTML = '';
                return;
            }

            if (emptyStateEl) emptyStateEl.classList.add('hidden');
            if (tbody) tbody.innerHTML = users.map(renderRow).join('');
            renderPagination(total);
        } catch (err) {
            console.error('[Pending Approvals] Load failed:', err);
            if (tbody) tbody.innerHTML = `<tr><td colspan="5" class="text-center py-10 text-error">Failed to load data. Please refresh.</td></tr>`;
        }
    }

    function renderPagination(total) {
        if (!paginationEl) return;
        if (totalPages <= 1) { paginationEl.innerHTML = ''; return; }

        let html = `<div class="flex items-center gap-2 flex-wrap">`;
        html += `<button class="ds-btn ds-btn-sm ds-btn-ghost" data-page="${currentPage - 1}" ${currentPage <= 1 ? 'disabled' : ''}>
            <span class="material-symbols-outlined text-[18px]">chevron_left</span></button>`;
        for (let i = 1; i <= totalPages; i++) {
            html += `<button class="ds-btn ds-btn-sm ${i === currentPage ? 'ds-btn-primary' : 'ds-btn-ghost'}" data-page="${i}">${i}</button>`;
        }
        html += `<button class="ds-btn ds-btn-sm ds-btn-ghost" data-page="${currentPage + 1}" ${currentPage >= totalPages ? 'disabled' : ''}>
            <span class="material-symbols-outlined text-[18px]">chevron_right</span></button>`;
        html += `</div><p class="text-xs text-text-secondary mt-2">${total} pending application(s)</p>`;
        paginationEl.innerHTML = html;

        paginationEl.querySelectorAll('[data-page]').forEach(btn => {
            btn.addEventListener('click', () => {
                const p = parseInt(btn.dataset.page);
                if (p >= 1 && p <= totalPages && p !== currentPage) {
                    currentPage = p;
                    loadPending();
                }
            });
        });
    }

    function init() {
        loadPending();

        if (searchInput) {
            searchInput.addEventListener('input', () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => { currentPage = 1; loadPending(); }, 400);
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();