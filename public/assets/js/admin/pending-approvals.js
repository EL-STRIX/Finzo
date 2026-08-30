/**
 * Finzo Admin — Pending Account Approvals Handler
 * Manages the KYC & account opening authorization queue with comprehensive filtering
 */

(function () {
    let currentStatus = 'all';
    let currentPage = 1;
    const perPage = 50;
    let totalPages = 1;
    let searchTimeout = null;

    // DOM Elements
    const tbody = document.getElementById('pending-tbody');
    const searchInput = document.getElementById('search-pending');
    const btnClearSearch = document.getElementById('btn-clear-search');
    const accountTypeSelect = document.getElementById('filter-account-type');
    const idTypeSelect = document.getElementById('filter-id-type');
    const sortSelect = document.getElementById('filter-sort');
    const btnResetFilters = document.getElementById('btn-reset-filters');
    const countEl = document.getElementById('pending-count');
    const paginationInfo = document.getElementById('pagination-info');
    const pageNumbers = document.getElementById('page-numbers');
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const emptyStateEl = document.getElementById('pending-empty');
    const approvalsTable = document.getElementById('approvals-table');

    /**
     * Show loading skeleton in the table
     */
    function showSkeleton() {
        if (!tbody) return;
        tbody.innerHTML = Array.from({ length: 5 }, () => `
            <tr class="animate-pulse">
                <td class="py-3.5 px-4 text-center"><div class="h-4 w-6 bg-surface-subtle rounded mx-auto"></div></td>
                <td class="py-3.5 px-5">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-surface-subtle shrink-0"></div>
                        <div class="space-y-1.5 flex-1">
                            <div class="h-3.5 w-32 bg-surface-subtle rounded"></div>
                            <div class="h-2.5 w-24 bg-surface-subtle rounded"></div>
                        </div>
                    </div>
                </td>
                <td class="py-3.5 px-4"><div class="h-3 w-24 bg-surface-subtle rounded"></div></td>
                <td class="py-3.5 px-4"><div class="h-5 w-20 bg-surface-subtle rounded-md"></div></td>
                <td class="py-3.5 px-4"><div class="h-3 w-16 bg-surface-subtle rounded"></div></td>
                <td class="py-3.5 px-4"><div class="h-3 w-20 bg-surface-subtle rounded"></div></td>
                <td class="py-3.5 px-4"><div class="h-5 w-24 bg-surface-subtle rounded-md"></div></td>
                <td class="py-3.5 px-5 text-right"><div class="h-7 w-20 bg-surface-subtle rounded-lg ml-auto"></div></td>
            </tr>`).join('');
    }

    /**
     * Format timestamp to readable date string
     */
    function formatDate(raw) {
        if (!raw) return '—';
        try {
            const d = new Date(raw.replace(/-/g, '/'));
            if (isNaN(d.getTime())) return raw;
            return d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
        } catch (_) {
            return raw;
        }
    }

    /**
     * Escape HTML entities to prevent XSS when re-rendering server data
     */
    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    /**
     * Load applications from API applying all active filters
     */
    async function loadApplications() {
        showSkeleton();

        try {
            const searchVal = searchInput ? searchInput.value.trim() : '';
            const accountTypeVal = accountTypeSelect ? accountTypeSelect.value : 'all';
            const idTypeVal = idTypeSelect ? idTypeSelect.value : 'all';
            const sortVal = sortSelect ? sortSelect.value : 'newest';

            const params = {
                status: currentStatus,
                search: searchVal,
                account_type: accountTypeVal,
                id_type: idTypeVal,
                sort: sortVal,
                page: currentPage,
                per_page: perPage
            };

            const response = await window.api.get('/users/pending', params);

            if (response && (response.success || response.status === 'success' || response.status === 200)) {
                const payload = (response.data && typeof response.data === 'object' && !Array.isArray(response.data)) ? response.data : response;
                const apps = payload.applications || response.applications || (Array.isArray(response.data) ? response.data : []);
                const total = payload.total ?? response.total ?? apps.length;
                totalPages = payload.pages || response.pages || Math.max(1, Math.ceil(total / perPage));

                renderTable(apps, total);
                updateStats(payload.stats || response.stats || {});
                updatePagination(total);
            } else {
                if (tbody) {
                    tbody.innerHTML = `<tr><td colspan="8" class="text-center py-10 text-xs text-danger font-semibold">Failed to load queue: ${response?.message || 'Unknown error'}</td></tr>`;
                }
            }
        } catch (error) {
            console.error('[PendingApprovals] Load error:', error);
            if (tbody) {
                tbody.innerHTML = `<tr><td colspan="8" class="text-center py-10 text-xs text-danger font-semibold">Connection failed. Please check network and refresh.</td></tr>`;
            }
        }
    }

    /**
     * Render rows into the table
     */
    function renderTable(apps, total) {
        if (!tbody) return;

        if (!apps || !apps.length) {
            tbody.innerHTML = '';
            if (emptyStateEl) emptyStateEl.classList.remove('hidden');
            if (approvalsTable) approvalsTable.classList.add('hidden');
            return;
        }

        if (emptyStateEl) emptyStateEl.classList.add('hidden');
        if (approvalsTable) approvalsTable.classList.remove('hidden');

        tbody.innerHTML = apps.map((app, i) => {
            const rowNum = String((currentPage - 1) * perPage + i + 1).padStart(2, '0');
            const initials = (app.initials || (app.full_name ? app.full_name.charAt(0) : 'U')).toUpperCase();
            const dateDisplay = formatDate(app.kyc_submitted_at || app.created_at);
            const accountType = (app.account_type || 'Savings').toLowerCase();
            const isSavings = accountType.includes('saving');
            const idType = app.id_type && app.id_type !== 'N/A' ? app.id_type : 'Not Uploaded';

            // Status Badge
            const isUnderReview = (app.status === 'under_review');
            const isActive = (app.status === 'active');
            const isRejected = (app.status === 'rejected');

            let statusBadge = '';
            if (isActive) {
                statusBadge = `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-[10px] font-bold uppercase tracking-wider">
                     <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Approved
                   </span>`;
            } else if (isRejected) {
                statusBadge = `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20 text-[10px] font-bold uppercase tracking-wider">
                     <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Rejected
                   </span>`;
            } else if (isUnderReview) {
                statusBadge = `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20 text-[10px] font-bold uppercase tracking-wider">
                     <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span> Under Review
                   </span>`;
            } else {
                statusBadge = `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 text-[10px] font-bold uppercase tracking-wider">
                     <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Pending Review
                   </span>`;
            }

            // Avatar Handling (values escaped before interpolation)
            const safeName = escapeHtml(app.full_name || '—');
            const safeInitials = escapeHtml(initials);
            let avatarHtml = `<div class="w-9 h-9 rounded-xl bg-surface-subtle border border-border-subtle flex items-center justify-center font-bold text-xs text-text-primary uppercase shrink-0 font-mono shadow-2xs">${safeInitials}</div>`;
            if (app.profile_picture_url) {
                let url = app.profile_picture_url;
                if (!url.startsWith('http://') && !url.startsWith('https://')) {
                    url = url.startsWith('/') ? url : '/Finzo/' + url.replace(/^\//, '');
                }
                avatarHtml = `<img src="${escapeHtml(url)}" class="w-9 h-9 rounded-xl object-cover border border-border-subtle shrink-0 shadow-2xs" alt="${safeName}" onerror="this.outerHTML='<div class=\\'w-9 h-9 rounded-xl bg-surface-subtle border border-border-subtle flex items-center justify-center font-bold text-xs text-text-primary uppercase shrink-0 font-mono shadow-2xs\\'>${safeInitials}</div>'">`;
            }

            return `
            <tr class="hover:bg-surface-hover/70 transition-colors">
                <td class="py-3 px-4 text-center font-mono text-[11px] font-bold text-text-secondary opacity-60">${rowNum}</td>
                <td class="py-3 px-5">
                    <div class="flex items-center gap-3">
                        ${avatarHtml}
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-headline font-bold text-xs text-text-primary truncate">${safeName}</span>
                                <span class="font-mono text-[10px] px-1.5 py-0.5 rounded bg-surface-subtle border border-border-subtle text-text-secondary font-semibold">${escapeHtml(app.customer_id || 'FNZ-USR-PENDING')}</span>
                            </div>
                            <div class="text-[11px] text-text-secondary truncate mt-0.5">${escapeHtml(app.email || '—')}</div>
                        </div>
                    </div>
                </td>
                <td class="py-3 px-4 text-xs font-mono font-medium text-text-primary whitespace-nowrap">
                    ${app.phone ? `<span class="inline-flex items-center gap-1"><span class="material-symbols-outlined text-[13px] text-text-secondary">call</span>${escapeHtml(app.phone)}</span>` : '—'}
                </td>
                <td class="py-3 px-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider ${isSavings ? 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20' : 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20'}">
                        ${isSavings ? 'Savings' : 'Current'}
                    </span>
                </td>
                <td class="py-3 px-4 whitespace-nowrap">
                    <span class="text-xs font-semibold text-text-primary">${escapeHtml(idType)}</span>
                </td>
                <td class="py-3 px-4 text-xs text-text-secondary whitespace-nowrap font-medium">
                    ${dateDisplay}
                </td>
                <td class="py-3 px-4 whitespace-nowrap">
                    ${statusBadge}
                </td>
                <td class="py-3 px-5 text-right whitespace-nowrap">
                    <a href="admin-review-application.php?id=${app.id}" class="px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider inline-flex items-center gap-1.5 bg-zinc-900 text-white hover:bg-black dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100 shadow-xs transition-all cursor-pointer" title="Review &amp; Authorize Application">
                        <span class="material-symbols-outlined text-[15px]" aria-hidden="true">rate_review</span>
                        <span>Review</span>
                    </a>
                </td>
            </tr>`;
        }).join('');
    }

    /**
     * Update executive stat cards & sidebar counter
     */
    function updateStats(stats) {
        const pReview = stats.pending_review ?? 0;
        const uReview = stats.under_review ?? 0;
        const aToday = stats.approved_today ?? 0;
        const tApproved = stats.total_approved ?? 0;

        // By ID
        const elPending = document.getElementById('stat-pending-review');
        if (elPending) elPending.textContent = String(pReview);

        const elUnder = document.getElementById('stat-under-review');
        if (elUnder) elUnder.textContent = String(uReview);

        const elApproved = document.getElementById('stat-approved-today');
        if (elApproved) elApproved.textContent = String(aToday);

        const elTotal = document.getElementById('stat-total-approved');
        if (elTotal) elTotal.textContent = String(tApproved);

        // Sidebar badge counter
        const totalPending = pReview + uReview;
        const sidebarBadge = document.getElementById('pending-badge-count');
        if (sidebarBadge) {
            sidebarBadge.textContent = String(totalPending);
            sidebarBadge.classList.toggle('hidden', totalPending === 0);
        }
    }

    /**
     * Update pagination controls & counts
     */
    function updatePagination(total) {
        if (countEl) countEl.textContent = String(total);

        if (paginationInfo) {
            if (total === 0) {
                paginationInfo.textContent = 'Showing 0 to 0 of 0';
            } else {
                const start = (currentPage - 1) * perPage + 1;
                const end = Math.min(currentPage * perPage, total);
                paginationInfo.textContent = `Showing ${start} to ${end} of ${total}`;
            }
        }

        if (prevBtn) prevBtn.disabled = (currentPage <= 1);
        if (nextBtn) nextBtn.disabled = (currentPage >= totalPages || totalPages === 0);

        if (pageNumbers) {
            if (totalPages <= 1) {
                pageNumbers.innerHTML = '';
                return;
            }

            let html = '';
            const maxVisible = 5;
            let startPage = Math.max(1, currentPage - 2);
            let endPage = Math.min(totalPages, startPage + maxVisible - 1);
            if (endPage - startPage < maxVisible - 1) {
                startPage = Math.max(1, endPage - maxVisible + 1);
            }

            for (let p = startPage; p <= endPage; p++) {
                const isCurrent = (p === currentPage);
                html += `<button type="button" class="w-8 h-8 rounded-lg text-xs font-bold transition-colors cursor-pointer ${isCurrent ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 shadow-xs' : 'bg-surface text-text-secondary hover:bg-surface-hover border border-border-subtle'}" data-page="${p}">${p}</button>`;
            }
            pageNumbers.innerHTML = html;

            pageNumbers.querySelectorAll('button[data-page]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const p = parseInt(btn.dataset.page, 10);
                    if (p && p !== currentPage) {
                        currentPage = p;
                        loadApplications();
                    }
                });
            });
        }
    }

    /**
     * Activate a specific status tab visually
     */
    function setActiveTab(status) {
        currentStatus = status;
        document.querySelectorAll('.tab-btn').forEach(btn => {
            const btnStatus = btn.getAttribute('data-status');
            if (btnStatus === status) {
                btn.className = 'tab-btn px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider transition-all cursor-pointer bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 shadow-xs';
            } else {
                btn.className = 'tab-btn px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider transition-all cursor-pointer bg-surface text-text-secondary hover:text-text-primary hover:bg-surface-hover border border-border-subtle';
            }
        });
    }

    /**
     * Bind DOM Event Listeners for all filters
     */
    function bindEvents() {
        // 1. Status Filter Tabs
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const status = this.getAttribute('data-status') || 'all';
                setActiveTab(status);
                currentPage = 1;
                loadApplications();
            });
        });

        // 2. Interactive Stat Filter Cards
        document.querySelectorAll('.stat-filter-card').forEach(card => {
            card.addEventListener('click', function () {
                const status = this.getAttribute('data-status');
                if (status) {
                    setActiveTab(status);
                    currentPage = 1;
                    loadApplications();
                }
            });
        });

        // 3. Search Input with 300ms debounce
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                const val = searchInput.value.trim();
                if (btnClearSearch) {
                    btnClearSearch.classList.toggle('hidden', val.length === 0);
                }
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    currentPage = 1;
                    loadApplications();
                }, 300);
            });
        }

        // 4. Clear Search Button
        if (btnClearSearch && searchInput) {
            btnClearSearch.addEventListener('click', () => {
                searchInput.value = '';
                btnClearSearch.classList.add('hidden');
                currentPage = 1;
                loadApplications();
                searchInput.focus();
            });
        }

        // 5. Account Type Dropdown
        if (accountTypeSelect) {
            accountTypeSelect.addEventListener('change', () => {
                currentPage = 1;
                loadApplications();
            });
        }

        // 6. ID Type Dropdown
        if (idTypeSelect) {
            idTypeSelect.addEventListener('change', () => {
                currentPage = 1;
                loadApplications();
            });
        }

        // 7. Sort Order Dropdown
        if (sortSelect) {
            sortSelect.addEventListener('change', () => {
                currentPage = 1;
                loadApplications();
            });
        }

        // 8. Reset All Filters Button
        if (btnResetFilters) {
            btnResetFilters.addEventListener('click', () => {
                if (searchInput) searchInput.value = '';
                if (btnClearSearch) btnClearSearch.classList.add('hidden');
                if (accountTypeSelect) accountTypeSelect.value = 'all';
                if (idTypeSelect) idTypeSelect.value = 'all';
                if (sortSelect) sortSelect.value = 'newest';
                setActiveTab('all');
                currentPage = 1;
                loadApplications();
            });
        }

        // 9. Pagination Buttons
        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    loadApplications();
                }
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    loadApplications();
                }
            });
        }
    }

    /**
     * Initialize Component
     */
    function init() {
        bindEvents();
    }

    // Expose public API
    window.PendingApprovals = {
        refresh: () => {
            currentPage = 1;
            loadApplications();
        },
        setFilter: (status) => {
            setActiveTab(status);
            currentPage = 1;
            loadApplications();
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
