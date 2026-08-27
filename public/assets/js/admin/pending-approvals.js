/**
 * Finzo Admin — Pending Account Approvals Handler
 * Manages the KYC & account opening authorization queue
 */

(function () {
    let currentStatus = 'all';
    let currentPage = 1;
    const perPage = 10;
    let totalPages = 1;
    let searchTimeout = null;

    const tbody = document.getElementById('pending-tbody');
    const searchInput = document.getElementById('search-pending');
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
        tbody.innerHTML = Array.from({ length: 5 }, (_, i) => `
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
     * Load pending applications from API
     */
    async function loadApplications() {
        showSkeleton();

        try {
            const searchVal = searchInput ? searchInput.value.trim() : '';
            const params = {
                status: currentStatus,
                search: searchVal,
                page: currentPage,
                per_page: perPage
            };

            const response = await window.api.get('/users/pending', params);

            if (response && (response.success || response.status === 'success')) {
                const apps = response.applications || [];
                const total = response.total || apps.length;
                totalPages = response.pages || Math.max(1, Math.ceil(total / perPage));

                renderTable(apps, total);
                updateStats(response.stats || {});
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
            const statusBadge = isUnderReview
                ? `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20 text-[10px] font-bold uppercase tracking-wider">
                     <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span> Under Review
                   </span>`
                : `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 text-[10px] font-bold uppercase tracking-wider">
                     <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Pending Review
                   </span>`;

            // Avatar Handling
            let avatarHtml = `<div class="w-9 h-9 rounded-xl bg-surface-subtle border border-border-subtle flex items-center justify-center font-bold text-xs text-text-primary uppercase shrink-0 font-mono shadow-2xs">${initials}</div>`;
            if (app.profile_picture_url) {
                let url = app.profile_picture_url;
                if (!url.startsWith('http://') && !url.startsWith('https://')) {
                    url = url.startsWith('/') ? url : '/Finzo/' + url.replace(/^\//, '');
                }
                avatarHtml = `<img src="${url}" class="w-9 h-9 rounded-xl object-cover border border-border-subtle shrink-0 shadow-2xs" alt="${app.full_name}" onerror="this.outerHTML='<div class=\\'w-9 h-9 rounded-xl bg-surface-subtle border border-border-subtle flex items-center justify-center font-bold text-xs text-text-primary uppercase shrink-0 font-mono shadow-2xs\\'>${initials}</div>'">`;
            }

            return `
            <tr class="hover:bg-surface-hover/70 transition-colors">
                <td class="py-3 px-4 text-center font-mono text-[11px] font-bold text-text-secondary opacity-60">${rowNum}</td>
                <td class="py-3 px-5">
                    <div class="flex items-center gap-3">
                        ${avatarHtml}
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-headline font-bold text-xs text-text-primary truncate">${app.full_name || '—'}</span>
                                <span class="font-mono text-[10px] px-1.5 py-0.5 rounded bg-surface-subtle border border-border-subtle text-text-secondary font-semibold">${app.customer_id || 'FNZ-USR-PENDING'}</span>
                            </div>
                            <div class="text-[11px] text-text-secondary truncate mt-0.5">${app.email || '—'}</div>
                        </div>
                    </div>
                </td>
                <td class="py-3 px-4 text-xs font-mono font-medium text-text-primary whitespace-nowrap">
                    ${app.phone ? `<span class="inline-flex items-center gap-1"><span class="material-symbols-outlined text-[13px] text-text-secondary">call</span>${app.phone}</span>` : '—'}
                </td>
                <td class="py-3 px-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider ${isSavings ? 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20' : 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20'}">
                        ${isSavings ? 'Savings' : 'Current'}
                    </span>
                </td>
                <td class="py-3 px-4 whitespace-nowrap">
                    <span class="text-xs font-semibold text-text-primary">${idType}</span>
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

        // Backward compatibility: check .admin-stat-label
        const labelMap = {
            'PENDING REVIEW': String(pReview),
            'UNDER REVIEW': String(uReview),
            'APPROVED TODAY': String(aToday),
            'TOTAL APPROVED': String(tApproved)
        };
        document.querySelectorAll('.admin-stat-label').forEach(lbl => {
            const k = lbl.textContent.trim();
            const valEl = lbl.nextElementSibling;
            if (valEl && labelMap[k] !== undefined) {
                valEl.classList.remove('skeleton');
                valEl.textContent = labelMap[k];
            }
        });

        // Sidebar badge counter
        const totalPending = pReview + uReview;
        const sidebarBadge = document.getElementById('pending-badge-count');
        if (sidebarBadge) {
            sidebarBadge.textContent = String(totalPending);
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
     * Export queue as CSV
     */
    async function exportCSV() {
        try {
            const params = {
                status: currentStatus,
                search: searchInput ? searchInput.value.trim() : '',
                page: 1,
                per_page: 500
            };
            const res = await window.api.get('/users/pending', params);
            const apps = res.applications || [];
            if (!apps.length) {
                if (window.showToast) window.showToast('No applications to export', 'info');
                return;
            }

            const headers = ['#', 'Customer ID', 'Full Name', 'Email', 'Phone', 'Account Type', 'ID Type', 'Date Applied', 'Status'];
            const rows = apps.map((a, idx) => [
                idx + 1,
                `"${(a.customer_id || '').replace(/"/g, '""')}"`,
                `"${(a.full_name || '').replace(/"/g, '""')}"`,
                `"${(a.email || '').replace(/"/g, '""')}"`,
                `"${(a.phone || '').replace(/"/g, '""')}"`,
                `"${(a.account_type || 'Savings').replace(/"/g, '""')}"`,
                `"${(a.id_type || 'N/A').replace(/"/g, '""')}"`,
                `"${(a.kyc_submitted_at || a.created_at || '').replace(/"/g, '""')}"`,
                `"${(a.status || 'pending').replace(/"/g, '""')}"`
            ]);

            const csvContent = [headers.join(','), ...rows.map(r => r.join(','))].join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `finzo_pending_approvals_${new Date().toISOString().slice(0, 10)}.csv`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
            if (window.showToast) window.showToast('CSV Export generated successfully', 'success');
        } catch (e) {
            console.error('Export error:', e);
            if (window.showToast) window.showToast('Failed to export CSV', 'error');
        }
    }

    /**
     * Bind DOM Event Listeners
     */
    function bindEvents() {
        // Status Filter Tabs
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.tab-btn').forEach(b => {
                    b.className = 'tab-btn px-3.5 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider transition-all cursor-pointer bg-surface text-text-secondary hover:text-text-primary hover:bg-surface-hover border border-border-subtle';
                });
                this.className = 'tab-btn px-3.5 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider transition-all cursor-pointer bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 shadow-xs';

                currentStatus = this.getAttribute('data-status') || 'all';
                currentPage = 1;
                loadApplications();
            });
        });

        // Search Input with Debounce
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    currentPage = 1;
                    loadApplications();
                }, 350);
            });
        }

        // Pagination Buttons
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
        loadApplications();
        bindEvents();
    }

    // Expose public API
    window.PendingApprovals = {
        refresh: () => {
            currentPage = 1;
            loadApplications();
        },
        exportCSV
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
