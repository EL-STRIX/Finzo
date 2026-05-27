/**
 * Finzo Admin — Pending Approvals Handler
 */

(function() {
    let currentStatus = 'all';
    let currentPage = 1;
    let searchTimeout = null;

    /**
     * Load applications with current filters
     */
    async function loadApplications() {
        const tbody = document.getElementById('applications-table-body');
        if (tbody) tbody.style.opacity = '0.5';

        try {
            const searchInput = document.getElementById('approval-search');
            const response = await window.api.get('/admin/pending-approvals.php', {
                status: currentStatus,
                search: searchInput ? searchInput.value : '',
                page: currentPage,
                per_page: 10
            });

            if (response.success) {
                renderTable(response.data.applications, response.data.total, response.data.pages);
                updateStats(response.data.stats || {});
            }
        } catch (error) {
            console.error('Failed to load applications:', error);
        } finally {
            if (tbody) tbody.style.opacity = '1';
        }
    }

    /**
     * Render applications table
     */
    function renderTable(apps, total, pages) {
        const tbody = document.getElementById('applications-table-body');
        if (!tbody) return;

        if (!apps || !apps.length) {
            tbody.innerHTML = '<tr><td colspan="8" class="py-20 text-center font-bold opacity-40">No applications found.</td></tr>';
            return;
        }

        tbody.innerHTML = apps.map((app, i) => {
            const initials = app.initials || app.full_name.charAt(0);
            const dateStr = app.created_at || 'N/A';
            const statusBadgeClass = app.status === 'pending' ? 'badge-amber' : (app.status === 'under_review' ? 'badge-blue' : 'badge-green');
            const statusText = (app.status_text || app.status || 'Pending').replace('_', ' ');
            const countStr = String((currentPage - 1) * 10 + i + 1).padStart(2, '0');

            let avatarHtml = `<div class="admin-avatar premium-gradient-primary uppercase">${initials}</div>`;
            if (app.profile_picture_url) {
                let url = app.profile_picture_url;
                if (!url.startsWith('http://') && !url.startsWith('https://') && !url.startsWith('/')) {
                    url = '../../' + url;
                }
                avatarHtml = `<img src="${url}" class="admin-avatar object-cover border border-slate-200 dark:border-white/10" alt="${app.full_name}">`;
            }

            return `
            <tr class="hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-colors" data-status="${app.status}">
                <td class="font-bold opacity-30 tracking-widest text-[11px]">${countStr}</td>
                <td>
                    <div class="flex items-center gap-3 applicant-info">
                        ${avatarHtml}
                        <div>
                            <div class="font-bold applicant-name text-slate-800 dark:text-slate-100">${app.full_name} <span class="ml-2 font-mono text-[10px] bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded text-slate-500">${app.customer_id || 'FNZ-USR-PENDING'}</span></div>
                            <div class="text-[11px] opacity-60 applicant-email">${app.email}</div>
                        </div>
                    </div>
                </td>
                <td class="text-xs font-bold text-slate-600 dark:text-slate-400 font-mono">${app.phone || 'N/A'}</td>
                <td><span class="status-badge bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 font-bold border border-indigo-100 dark:border-indigo-500/20">${(app.account_type || 'Savings').charAt(0).toUpperCase() + (app.account_type || 'Savings').slice(1)}</span></td>
                <td><span class="text-xs font-bold opacity-70">${app.id_type || 'N/A'}</span></td>
                <td class="text-xs whitespace-nowrap opacity-60 font-semibold">${dateStr}</td>
                <td>
                    <span class="badge ${statusBadgeClass}">
                        <span class="material-symbols-outlined" style="font-size:10px;">${app.status === 'pending' ? 'hourglass_empty' : 'verified'}</span>
                        ${statusText.toUpperCase()}
                    </span>
                </td>
                <td>
                    <div class="flex justify-end">
                        <a href="admin-review-application.html?id=${app.id}" class="admin-btn-secondary px-3 py-1.5 text-[11px] rounded-lg flex items-center gap-2">
                            <span class="material-symbols-outlined !text-xs">settings_accessibility</span> Manage
                        </a>
                    </div>
                </td>
            </tr>`;
        }).join('');

        updatePagination(total, pages);
    }

    /**
     * Update pagination UI
     */
    function updatePagination(total, pages) {
        const info = document.getElementById('pagination-info');
        if (info) {
            info.textContent = `Showing ${Math.min((currentPage - 1) * 10 + 1, total)}-${Math.min(currentPage * 10, total)} of ${total} Items`;
        }

        const nextBtn = document.getElementById('next-btn');
        if (!nextBtn) return;
        const parent = document.getElementById('page-numbers');
        
        const prevBtn = document.getElementById('prev-btn');
        if (prevBtn) prevBtn.disabled = (currentPage === 1);
        nextBtn.disabled = (currentPage === pages || pages === 0);

        let html = '';
        for (let i = 1; i <= pages; i++) {
            if (pages > 5 && i > 3 && i < pages) {
                if (i === 4) html += '<span class="px-2 opacity-50">...</span>';
                continue;
            }
            const activeClass = i === currentPage ? 'admin-btn-primary active' : 'admin-btn-secondary';
            const style = i === currentPage ? 'background: #1a3c5e !important;' : '';
            html += `<button class="${activeClass} w-9 h-9 p-0 text-xs rounded-lg" style="${style}" data-page="${i}">${i}</button>`;
        }
        
        if (parent) parent.innerHTML = html;
    }

    /**
     * Update stat cards
     */
    function updateStats(stats) {
        // Map each label text → value from stats
        const labelMap = {
            'PENDING REVIEW': String(stats.pending_review ?? 0),
            'UNDER REVIEW':   String(stats.under_review   ?? 0),
            'APPROVED TODAY': String(stats.approved_today ?? 0),
            'TOTAL APPROVED': String(stats.total_approved ?? 0),
        };

        document.querySelectorAll('.admin-stat-label').forEach(label => {
            const key = label.textContent.trim();
            const valueEl = label.nextElementSibling;
            if (valueEl && labelMap[key] !== undefined) {
                valueEl.classList.remove('skeleton');
                valueEl.textContent = labelMap[key];
            }
        });

        // Topbar pending badge
        const badge = document.getElementById('pending-badge');
        const badgeCount = document.getElementById('pending-badge-count');
        const pending = (stats.pending_review ?? 0) + (stats.under_review ?? 0);
        if (badge && badgeCount) {
            if (pending > 0) {
                badge.classList.remove('hidden');
                badge.classList.add('flex');
                badgeCount.textContent = `${pending} Pending`;
            } else {
                badge.classList.add('hidden');
                badge.classList.remove('flex');
            }
        }
    }

    /**
     * Bind events
     */
    function bindEvents() {
        // Status Tabs
        const tabs = document.querySelectorAll('.tab-btn');
        const activeClasses = [
            'bg-primary', 'text-white', 'shadow-lg', 'shadow-primary/20', 'scale-[1.02]',
            'dark:bg-sky-500/20', 'dark:text-sky-300', 'dark:shadow-none', 'dark:ring-1', 'dark:ring-sky-500/30'
        ];
        const inactiveClasses = [
            'bg-slate-100', 'text-slate-600', 'hover:bg-slate-200/80', 'hover:text-slate-800', 'hover:scale-[1.01]',
            'dark:bg-white/5', 'dark:text-slate-400', 'dark:hover:bg-white/10', 'dark:hover:text-slate-200'
        ];

        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Reset all tabs to inactive state
                tabs.forEach(t => {
                    t.classList.remove(...activeClasses);
                    t.classList.add(...inactiveClasses);
                });

                // Set active state on clicked tab
                this.classList.remove(...inactiveClasses);
                this.classList.add(...activeClasses);

                currentStatus = this.getAttribute('data-status');
                currentPage = 1;
                loadApplications();
            });
        });

        // Filter Apply/Reset
        const searchInput = document.getElementById('approval-search');
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    currentPage = 1;
                    loadApplications();
                }, 300);
            });
        }

        // Pagination
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('button[data-page]');
            if (btn) {
                currentPage = parseInt(btn.getAttribute('data-page'));
                loadApplications();
            }
        });

        const prevBtn = document.getElementById('prev-btn');
        if (prevBtn) prevBtn.addEventListener('click', () => { if (currentPage > 1) { currentPage--; loadApplications(); } });

        const nextBtn = document.getElementById('next-btn');
        if (nextBtn) nextBtn.addEventListener('click', () => { currentPage++; loadApplications(); });
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', () => {
        loadApplications();
        bindEvents();
    });
})();
