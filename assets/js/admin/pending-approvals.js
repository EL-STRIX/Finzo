/**
 * Finzo Admin — Pending Approvals Handler
 */

(function() {
    let currentStatus = 'all';
    let currentPage = 1;

    /**
     * Load applications with current filters
     */
    async function loadApplications() {
        const tbody = document.getElementById('applications-table-body');
        if (tbody) tbody.style.opacity = '0.5';

        try {
            const searchInput = document.getElementById('filter-search');
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

            return `
            <tr class="hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-colors" data-status="${app.status}">
                <td class="font-bold opacity-30 tracking-widest text-[11px]">${countStr}</td>
                <td>
                    <div class="flex items-center gap-3 applicant-info">
                        <div class="admin-avatar premium-gradient-primary uppercase">${initials}</div>
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
        const info = document.querySelector('.admin-card + div span') || document.querySelector('.text-xs.font-bold.opacity-40');
        if (info) {
            info.textContent = `Showing ${Math.min((currentPage - 1) * 10 + 1, total)}-${Math.min(currentPage * 10, total)} of ${total} applications`;
        }

        const nextBtn = document.getElementById('next-page');
        if (!nextBtn) return;
        const parent = nextBtn.parentElement;
        
        const prevBtn = document.getElementById('prev-page');
        if (prevBtn) prevBtn.disabled = (currentPage === 1);
        nextBtn.disabled = (currentPage === pages || pages === 0);

        // Remove old page buttons
        const oldBtns = parent.querySelectorAll('button:not(#prev-page):not(#next-page)');
        oldBtns.forEach(b => b.remove());
        const oldSpans = parent.querySelectorAll('span.opacity-50');
        oldSpans.forEach(s => s.remove());

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
        
        // Insert after prev button
        if (prevBtn) prevBtn.insertAdjacentHTML('afterend', html);
    }

    /**
     * Update stat cards
     */
    function updateStats(stats) {
        const cardLabels = document.querySelectorAll('.admin-stat-label');
        cardLabels.forEach(label => {
            const nextEl = label.nextElementSibling;
            if (!nextEl) return;
            label.classList.remove('skeleton');
            nextEl.classList.remove('skeleton');

            const txt = label.textContent.trim();
            if (txt === 'PENDING REVIEW' && stats.pending_review !== undefined) {
                nextEl.innerHTML = `${stats.pending_review} <span class="admin-stat-delta negative">Action Required</span>`;
            } else if (txt === 'APPROVED TODAY' && stats.approved_today !== undefined) {
                nextEl.textContent = stats.approved_today;
            } else if (txt === 'REJECTED TODAY' && stats.rejected_today !== undefined) {
                nextEl.textContent = stats.rejected_today;
            } else if (txt === 'TOTAL APPROVED' && stats.total_approved !== undefined) {
                nextEl.textContent = stats.total_approved;
            }
        });
    }

    /**
     * Bind events
     */
    function bindEvents() {
        // Status Tabs
        const tabs = document.querySelectorAll('.tab-btn');
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                tabs.forEach(t => t.classList.remove('bg-primary', 'text-white', 'shadow-md'));
                tabs.forEach(t => t.classList.add('text-slate-500'));
                
                this.classList.remove('text-slate-500');
                this.classList.add('bg-primary', 'text-white', 'shadow-md');
                
                currentStatus = this.getAttribute('data-status');
                currentPage = 1;
                loadApplications();
            });
        });

        // Filter Apply/Reset
        const applyBtn = document.getElementById('apply-filters');
        const resetBtn = document.getElementById('reset-filters');
        const searchInput = document.getElementById('filter-search');

        if (applyBtn) applyBtn.addEventListener('click', () => { currentPage = 1; loadApplications(); });
        if (resetBtn) {
            resetBtn.addEventListener('click', () => {
                if (searchInput) searchInput.value = '';
                currentStatus = 'all';
                currentPage = 1;
                loadApplications();
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

        const prevBtn = document.getElementById('prev-page');
        if (prevBtn) prevBtn.addEventListener('click', () => { if (currentPage > 1) { currentPage--; loadApplications(); } });

        const nextBtn = document.getElementById('next-page');
        if (nextBtn) nextBtn.addEventListener('click', () => { currentPage++; loadApplications(); });
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', () => {
        loadApplications();
        bindEvents();
    });
})();
