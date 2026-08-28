/**
 * Finzo Admin — Audit Trail & Activity Log Handler
 * Enterprise compliance logging, interactive filtering, and record inspection.
 */

(function() {
    'use strict';

    let currentRole = 'all';
    let currentPage = 1;
    let rowsPerPage = 25;
    let actionTypesPopulated = false;

    // Active Filter State
    function getFilterParams() {
        const searchInput = document.getElementById('filter-search');
        const actionInput = document.getElementById('filter-action-type');
        const dateFrom = document.getElementById('date-from');
        const dateTo = document.getElementById('date-to');
        const perPageSelect = document.getElementById('rows-per-page');

        if (perPageSelect) {
            rowsPerPage = parseInt(perPageSelect.value, 10) || 25;
        }

        return {
            search: searchInput ? searchInput.value.trim() : '',
            action_type: actionInput ? actionInput.value : 'all',
            actor_role: currentRole || 'all',
            from_date: dateFrom ? dateFrom.value : '',
            to_date: dateTo ? dateTo.value : '',
            page: currentPage,
            per_page: rowsPerPage
        };
    }

    // Load Audit Log from Backend API
    async function loadAuditLog() {
        const tbody = document.getElementById('audit-log-tbody');
        if (tbody) {
            renderSkeletonTable();
        }

        try {
            const params = getFilterParams();
            const response = await window.api.get('/admin/audit-logs', params);

            if (response && response.success) {
                const data = response.data || response;
                const entries = data.entries || response.entries || [];
                const total = data.total !== undefined ? data.total : (response.total || 0);
                const pages = data.pages !== undefined ? data.pages : (response.pages || 1);
                const stats = data.stats || response.stats || {};
                const actionTypes = data.action_types || response.action_types || [];

                renderTable(entries, total, pages);
                updateStats(stats, total);

                if (!actionTypesPopulated && actionTypes.length > 0) {
                    populateActionTypes(actionTypes);
                    actionTypesPopulated = true;
                }
            } else {
                renderError(response?.message || 'Failed to load audit records.');
            }
        } catch (error) {
            console.error('[AuditLog] Error fetching audit trail:', error);
            renderError('Unable to connect to server. Please check your network connection.');
        }
    }

    // Render Skeleton Loading Table
    function renderSkeletonTable() {
        const tbody = document.getElementById('audit-log-tbody');
        if (!tbody) return;

        let skeletonHtml = '';
        for (let i = 0; i < 5; i++) {
            skeletonHtml += `
            <tr class="animate-pulse">
                <td class="py-4 px-4 text-center"><div class="h-3 w-4 bg-border-subtle rounded mx-auto"></div></td>
                <td class="py-4 px-4"><div class="h-3.5 w-24 bg-border-subtle rounded"></div></td>
                <td class="py-4 px-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-full bg-border-subtle shrink-0"></div>
                        <div class="space-y-1.5 w-full">
                            <div class="h-3 w-24 bg-border-subtle rounded"></div>
                            <div class="h-2 w-14 bg-border-subtle rounded"></div>
                        </div>
                    </div>
                </td>
                <td class="py-4 px-4"><div class="h-5 w-20 bg-border-subtle rounded-md"></div></td>
                <td class="py-4 px-4"><div class="h-3.5 w-24 bg-border-subtle rounded"></div></td>
                <td class="py-4 px-4"><div class="h-3 w-20 bg-border-subtle rounded"></div></td>
                <td class="py-4 px-4 text-center"><div class="h-5 w-16 bg-border-subtle rounded-full mx-auto"></div></td>
                <td class="py-4 px-4"><div class="h-3 w-40 bg-border-subtle rounded"></div></td>
                <td class="py-4 px-4"><div class="h-3 w-20 bg-border-subtle rounded"></div></td>
                <td class="py-4 px-6 text-right"><div class="h-7 w-7 bg-border-subtle rounded-lg ml-auto"></div></td>
            </tr>`;
        }
        tbody.innerHTML = skeletonHtml;
    }

    // Render Table Rows
    function renderTable(entries, total, pages) {
        const tbody = document.getElementById('audit-log-tbody');
        if (!tbody) return;

        if (!entries || !entries.length) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="10" class="py-16 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-14 h-14 rounded-2xl bg-surface-subtle border border-border-subtle flex items-center justify-center mb-3">
                                <span class="material-symbols-outlined text-3xl text-text-secondary">search_off</span>
                            </div>
                            <div class="text-sm font-black text-text-primary uppercase tracking-wider mb-1">No Audit Records Found</div>
                            <div class="text-xs text-text-secondary max-w-sm">No activity records match your current filter parameters. Try clearing or expanding your search criteria.</div>
                            <button onclick="window.resetAuditFilters()" class="btn btn-secondary px-4 py-1.5 text-xs font-bold mt-4">Reset Filters</button>
                        </div>
                    </td>
                </tr>
            `;
            updatePagination(total, pages);
            return;
        }

        tbody.innerHTML = '';
        entries.forEach((entry, i) => {
            const rowIndex = total ? (total - (((currentPage - 1) * rowsPerPage) + i)) : (i + 1);
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50/70 dark:hover:bg-white/[0.03] transition-colors cursor-pointer group';
            
            const eventId = entry.event_id || `EVT-${String(entry.id).padStart(6, '0')}`;
            const detailsText = entry.details || 'No details recorded.';
            const truncatedDetails = detailsText.length > 55 ? detailsText.substring(0, 55) + '...' : detailsText;
            const actorInitial = (entry.actor_name || entry.actor_id || 'U').charAt(0).toUpperCase();

            tr.innerHTML = `
                <td class="py-3 px-4 font-mono font-bold opacity-40 text-[11px] text-center">${rowIndex}</td>
                <td class="py-3 px-4">
                    <div class="flex items-center gap-1.5">
                        <span class="font-mono text-xs font-bold text-primary group-hover:underline" title="${eventId}">${eventId}</span>
                    </div>
                </td>
                <td class="py-3 px-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-full bg-primary/10 text-primary border border-primary/20 flex items-center justify-center font-black text-[11px] shrink-0">
                            ${actorInitial}
                        </div>
                        <div class="overflow-hidden">
                            <div class="font-bold text-xs text-text-primary truncate max-w-[130px]" title="${escapeHtml(entry.actor_name || entry.actor_id)}">
                                ${escapeHtml(entry.actor_name || entry.actor_id)}
                            </div>
                            <div class="flex items-center gap-1 mt-0.5">
                                ${getActorBadge(entry.actor_specific_role || entry.actor_role)}
                                <span class="font-mono text-[10px] text-text-secondary opacity-70">${escapeHtml(entry.actor_display_id || entry.actor_id)}</span>
                            </div>
                        </div>
                    </div>
                </td>
                <td class="py-3 px-4">${getActionBadge(entry.action_type)}</td>
                <td class="py-3 px-4">
                    <div class="text-[10px] uppercase font-black tracking-wider text-text-secondary">${escapeHtml(entry.module || 'SYSTEM')}</div>
                    <div class="font-mono text-xs font-semibold text-text-primary mt-0.5 flex items-center gap-1">
                        <span class="opacity-50 text-[10px]">${escapeHtml((entry.entity_type || '').toUpperCase())}:</span>
                        <span>${escapeHtml(entry.entity_display_id || entry.entity_id || '-')}</span>
                    </div>
                </td>
                <td class="py-3 px-4 font-mono text-xs text-text-secondary">
                    <div class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-[13px] opacity-60">dns</span>
                        <span>${escapeHtml(entry.ip_address || '0.0.0.0')}</span>
                    </div>
                </td>
                <td class="py-3 px-4 text-center">${getStatusBadge(entry.status)}</td>
                <td class="py-3 px-4 text-xs text-text-secondary max-w-[220px]" title="${escapeHtml(detailsText)}">
                    <span class="line-clamp-1">${escapeHtml(truncatedDetails)}</span>
                </td>
                <td class="py-3 px-4 text-xs font-medium text-text-secondary whitespace-nowrap">
                    <div class="text-[11px] font-bold text-text-primary">${formatDate(entry.created_at)}</div>
                    <div class="text-[10px] opacity-70 mt-0.5">${formatTime(entry.created_at)}</div>
                </td>
                <td class="py-3 px-6 text-right">
                    <button class="btn-view-detail p-1.5 rounded-lg text-text-secondary hover:text-primary hover:bg-primary/10 transition-colors" title="Inspect Record">
                        <span class="material-symbols-outlined text-[18px]">visibility</span>
                    </button>
                </td>
            `;

            // Click listener to open record details
            tr.addEventListener('click', () => {
                openDetailModal(entry);
            });

            tbody.appendChild(tr);
        });

        updatePagination(total, pages);
    }

    // Render Error State
    function renderError(message) {
        const tbody = document.getElementById('audit-log-tbody');
        if (!tbody) return;
        tbody.innerHTML = `
            <tr>
                <td colspan="10" class="py-14 text-center">
                    <div class="flex flex-col items-center justify-center">
                        <span class="material-symbols-outlined text-4xl text-danger mb-3">error</span>
                        <div class="text-sm font-black text-text-primary uppercase tracking-widest mb-1">Error Loading Audit Log</div>
                        <div class="text-xs text-text-secondary max-w-sm mb-4">${escapeHtml(message)}</div>
                        <button onclick="window.loadAuditLog()" class="btn btn-secondary px-4 py-1.5 text-xs font-bold flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[14px]">refresh</span>
                            <span>Try Again</span>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }

    // Dynamic Action Badge Formatter
    function getActionBadge(action) {
        const type = (action || 'unknown').toLowerCase();
        let badgeClass = 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-slate-300/40';

        if (type.includes('login_success') || type === 'login' || type === 'session_logout' || type.includes('auth')) {
            badgeClass = 'bg-sky-50 text-sky-700 dark:bg-sky-950/40 dark:text-sky-400 border-sky-300/40';
        } else if (type.includes('login_failed') || type.includes('delete') || type.includes('suspend') || type.includes('reject') || type.includes('lock')) {
            badgeClass = 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 border-rose-300/40';
        } else if (type.includes('create') || type.includes('deposit') || type.includes('approve') || type.includes('unlock') || type.includes('reactivate') || type.includes('clock_in')) {
            badgeClass = 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border-emerald-300/40';
        } else if (type.includes('update') || type.includes('password') || type.includes('reconciliation') || type.includes('withdrawal')) {
            badgeClass = 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border-amber-300/40';
        } else if (type.includes('transfer') || type.includes('interest') || type.includes('admin_action')) {
            badgeClass = 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-400 border-indigo-300/40';
        }

        const formattedText = type.replace(/_/g, ' ').toUpperCase();
        return `<span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black border uppercase tracking-wider ${badgeClass}">${formattedText}</span>`;
    }

    // Actor Role Badge Formatter
    function getActorBadge(role) {
        const r = (role || 'system').toLowerCase();
        let colorClass = 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
        let label = 'SYSTEM';

        if (r.includes('admin') || r.includes('moderator')) {
            colorClass = 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300';
            label = r.includes('super') ? 'SUPER ADMIN' : 'ADMIN';
        } else if (r.includes('staff')) {
            colorClass = 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300';
            label = 'STAFF';
        } else if (r.includes('user')) {
            colorClass = 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300';
            label = 'USER';
        } else if (r.includes('system')) {
            colorClass = 'bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-300';
            label = 'SYSTEM';
        }

        return `<span class="px-1.5 py-0.5 rounded text-[9px] font-black tracking-wider uppercase ${colorClass}">${label}</span>`;
    }

    // Status Badge Formatter
    function getStatusBadge(status) {
        const s = (status || 'success').toLowerCase();
        if (s === 'success') {
            return `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>SUCCESS</span>`;
        } else if (s === 'failure' || s === 'failed') {
            return `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400 border border-rose-200 dark:border-rose-800"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>FAILED</span>`;
        } else if (s === 'warning') {
            return `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-200 dark:border-amber-800"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>WARNING</span>`;
        }
        return `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-50 text-sky-600 dark:bg-sky-950/40 dark:text-sky-400 border border-sky-200 dark:border-sky-800"><span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>${s.toUpperCase()}</span>`;
    }

    // Update Top Metric Stat Cards
    function updateStats(stats, total) {
        stats = stats || {};
        const totalCount = total !== undefined ? total : (stats.total || 0);
        
        const elTotal = document.getElementById('stat-total');
        const elAdmin = document.getElementById('stat-admin');
        const elStaff = document.getElementById('stat-staff');
        const elUser = document.getElementById('stat-user');
        const elSystem = document.getElementById('stat-system');

        const fmt = (num) => Number(num || 0).toLocaleString();

        if (elTotal) elTotal.textContent = fmt(totalCount);
        if (elAdmin) elAdmin.textContent = fmt(stats.admin);
        if (elStaff) elStaff.textContent = fmt(stats.staff);
        if (elUser) elUser.textContent = fmt(stats.user);
        if (elSystem) elSystem.textContent = fmt(stats.system);
    }

    // Populate Dynamic Action Types in Filter Dropdown
    function populateActionTypes(actionTypes) {
        const menu = document.getElementById('action-type-menu');
        if (!menu || !Array.isArray(actionTypes)) return;

        menu.innerHTML = '<div class="finzo-dropdown-option is-selected text-xs" data-value="all">All Actions</div>';
        actionTypes.forEach(type => {
            if (!type) return;
            const opt = document.createElement('div');
            opt.className = 'finzo-dropdown-option text-xs';
            opt.setAttribute('data-value', type);
            opt.textContent = type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            menu.appendChild(opt);
        });
    }

    // Update Pagination UI
    function updatePagination(total, pages) {
        const info = document.getElementById('pagination-info');
        if (info) {
            const start = total === 0 ? 0 : ((currentPage - 1) * rowsPerPage) + 1;
            const end = Math.min(currentPage * rowsPerPage, total);
            info.textContent = `Showing ${start}-${end} of ${total.toLocaleString()} Records`;
        }

        const prevBtn = document.getElementById('prev-btn');
        const nextBtn = document.getElementById('next-btn');
        const pageNumbers = document.getElementById('page-numbers');

        if (prevBtn) {
            prevBtn.disabled = (currentPage <= 1 || total === 0);
            prevBtn.classList.toggle('opacity-50', currentPage <= 1 || total === 0);
            prevBtn.classList.toggle('cursor-not-allowed', currentPage <= 1 || total === 0);
        }

        if (nextBtn) {
            nextBtn.disabled = (currentPage >= pages || pages === 0);
            nextBtn.classList.toggle('opacity-50', currentPage >= pages || pages === 0);
            nextBtn.classList.toggle('cursor-not-allowed', currentPage >= pages || pages === 0);
        }

        if (pageNumbers) {
            let html = '';
            const maxVisiblePages = 5;
            let startPage = Math.max(1, currentPage - 2);
            let endPage = Math.min(pages, startPage + maxVisiblePages - 1);

            if (endPage - startPage < maxVisiblePages - 1) {
                startPage = Math.max(1, endPage - maxVisiblePages + 1);
            }

            if (startPage > 1) {
                html += `<button data-page="1" class="w-8 h-8 rounded-lg text-xs font-bold bg-surface border border-border-subtle hover:bg-surface-hover transition-all">1</button>`;
                if (startPage > 2) html += `<span class="px-1 text-xs opacity-50">...</span>`;
            }

            for (let p = startPage; p <= endPage; p++) {
                if (p === currentPage) {
                    html += `<button data-page="${p}" class="w-8 h-8 rounded-lg text-xs font-black bg-primary text-text-inverse shadow-sm shadow-primary/20">${p}</button>`;
                } else {
                    html += `<button data-page="${p}" class="w-8 h-8 rounded-lg text-xs font-bold bg-surface border border-border-subtle hover:bg-surface-hover transition-all">${p}</button>`;
                }
            }

            if (endPage < pages) {
                if (endPage < pages - 1) html += `<span class="px-1 text-xs opacity-50">...</span>`;
                html += `<button data-page="${pages}" class="w-8 h-8 rounded-lg text-xs font-bold bg-surface border border-border-subtle hover:bg-surface-hover transition-all">${pages}</button>`;
            }

            pageNumbers.innerHTML = html;
        }
    }

    // Open Detail Modal
    function openDetailModal(entry) {
        const modal = document.getElementById('audit-detail-modal');
        if (!modal) return;

        const eventId = entry.event_id || `EVT-${String(entry.id).padStart(6, '0')}`;
        
        // Header & Overview
        document.getElementById('detail-event-id').textContent = eventId;
        document.getElementById('detail-log-id').textContent = `Record #${entry.id}`;
        document.getElementById('detail-timestamp').textContent = entry.created_at;
        document.getElementById('detail-action-badge').innerHTML = getActionBadge(entry.action_type);
        document.getElementById('detail-status-badge').innerHTML = getStatusBadge(entry.status);

        // Actor Card
        document.getElementById('detail-actor-role').innerHTML = getActorBadge(entry.actor_specific_role || entry.actor_role);
        document.getElementById('detail-actor-id').textContent = entry.actor_display_id || entry.actor_id || 'SYSTEM';
        document.getElementById('detail-actor-name').textContent = entry.actor_name || entry.actor_id || 'System Process';
        document.getElementById('detail-ip').textContent = entry.ip_address || '0.0.0.0 (Local / System)';

        // Target & Entity Card
        document.getElementById('detail-module').textContent = (entry.module || 'SYSTEM').toUpperCase();
        document.getElementById('detail-entity-type').textContent = (entry.entity_type || 'N/A').toUpperCase();
        document.getElementById('detail-entity-id').textContent = entry.entity_display_id || entry.entity_id || '-';
        document.getElementById('detail-correlation-id').textContent = entry.correlation_id || 'None';
        document.getElementById('detail-request-id').textContent = entry.request_id || entry.session_id || 'None';

        // Branch info
        const branchRow = document.getElementById('detail-branch-row');
        const branchEl = document.getElementById('detail-branch');
        if (entry.branch_name || entry.branch_id) {
            if (branchRow) branchRow.classList.remove('hidden');
            if (branchEl) branchEl.textContent = entry.branch_name ? `${entry.branch_name} (${entry.branch_id})` : entry.branch_id;
        } else {
            if (branchRow) branchRow.classList.add('hidden');
        }

        // Client & Environment Context
        const uuidEl = document.getElementById('detail-uuid');
        const devEl = document.getElementById('detail-device');
        if (uuidEl) uuidEl.textContent = entry.uuid || 'N/A';
        if (devEl) devEl.textContent = entry.device_info || 'Browser / Standard Web Client';

        // Details Description
        document.getElementById('detail-details').textContent = entry.details || 'No additional narrative recorded for this event.';

        // State Changes / JSON Payload
        const payloadSection = document.getElementById('detail-payload-section');
        const prevEl = document.getElementById('detail-previous-values');
        const newEl = document.getElementById('detail-new-values');

        if (entry.previous_values || entry.new_values) {
            if (payloadSection) payloadSection.classList.remove('hidden');
            if (prevEl) prevEl.textContent = formatJson(entry.previous_values);
            if (newEl) newEl.textContent = formatJson(entry.new_values);
        } else {
            if (payloadSection) payloadSection.classList.add('hidden');
        }

        // Copy button setup
        const copyBtn = document.getElementById('copy-event-id-btn');
        if (copyBtn) {
            copyBtn.onclick = () => {
                navigator.clipboard.writeText(eventId).then(() => {
                    if (window.utils && window.utils.showToast) {
                        window.utils.showToast('Event ID copied to clipboard', 'success');
                    }
                });
            };
        }

        // Show modal with animation
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            const card = modal.querySelector('.ds-card');
            if (card) {
                card.classList.remove('scale-95');
                card.classList.add('scale-100');
            }
        }, 10);
    }

    // Close Detail Modal
    function closeDetailModal() {
        const modal = document.getElementById('audit-detail-modal');
        if (!modal) return;

        modal.classList.add('opacity-0');
        const card = modal.querySelector('.ds-card');
        if (card) {
            card.classList.remove('scale-100');
            card.classList.add('scale-95');
        }

        setTimeout(() => {
            modal.classList.add('hidden');
        }, 200);
    }

    // Set Role Filter (called by role pills or stat cards)
    window.setRoleFilter = function(role) {
        currentRole = role || 'all';

        const roleInput = document.getElementById('filter-actor-role');
        if (roleInput) {
            roleInput.value = currentRole;
        }

        // Update active role pill styles
        document.querySelectorAll('.role-pill').forEach(pill => {
            const pillRole = pill.dataset.role;
            if (pillRole === currentRole) {
                pill.className = 'role-pill px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-200 bg-primary text-text-inverse shadow-md shadow-primary/20';
            } else {
                pill.className = 'role-pill px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-200 bg-surface border border-border-subtle text-text-secondary hover:text-text-primary hover:bg-surface-hover';
            }
        });

        currentPage = 1;
        loadAuditLog();
    };

    // Reset All Filters
    window.resetAuditFilters = function() {
        currentRole = 'all';
        currentPage = 1;

        const searchInput = document.getElementById('filter-search');
        const actionInput = document.getElementById('filter-action-type');
        const roleInput = document.getElementById('filter-actor-role');
        const dateFrom = document.getElementById('date-from');
        const dateTo = document.getElementById('date-to');
        const clearBtn = document.getElementById('clear-search-btn');

        if (searchInput) searchInput.value = '';
        if (clearBtn) clearBtn.classList.add('hidden');
        if (dateFrom) dateFrom.value = '';
        if (dateTo) dateTo.value = '';
        if (roleInput) roleInput.value = 'all';

        if (actionInput) {
            actionInput.value = 'all';
            const d = document.getElementById('action-type-dropdown');
            if (d) {
                const lbl = d.querySelector('.dropdown-label');
                if (lbl) lbl.textContent = 'All Actions';
                d.querySelectorAll('.finzo-dropdown-option').forEach(o => o.classList.toggle('is-selected', o.dataset.value === 'all'));
            }
        }

        const perPageInput = document.getElementById('rows-per-page');
        if (perPageInput) {
            perPageInput.value = '25';
            rowsPerPage = 25;
            const d = document.getElementById('dropdown-rows-per-page');
            if (d) {
                const lbl = d.querySelector('.dropdown-label');
                if (lbl) lbl.textContent = '25';
                d.querySelectorAll('.finzo-dropdown-option').forEach(o => o.classList.toggle('is-selected', o.dataset.value === '25'));
            }
        }


        // Reset role pills
        document.querySelectorAll('.role-pill').forEach(pill => {
            if (pill.dataset.role === 'all') {
                pill.className = 'role-pill px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-200 bg-primary text-text-inverse shadow-md shadow-primary/20';
            } else {
                pill.className = 'role-pill px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-200 bg-surface border border-border-subtle text-text-secondary hover:text-text-primary hover:bg-surface-hover';
            }
        });

        loadAuditLog();


        if (window.utils && window.utils.showToast) {
            window.utils.showToast('All filters have been reset', 'info');
        }
    };

    // Helper: JSON Formatter
    function formatJson(val) {
        if (!val) return 'None';
        try {
            const parsed = typeof val === 'string' ? JSON.parse(val) : val;
            return JSON.stringify(parsed, null, 2);
        } catch (e) {
            return String(val);
        }
    }

    // Helper: HTML Sanitizer
    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Helper: Format Date
    function formatDate(dateStr) {
        if (!dateStr) return '--';
        try {
            const d = new Date(dateStr);
            return d.toLocaleDateString('en-US', { day: '2-digit', month: 'short', year: 'numeric' });
        } catch (e) {
            return dateStr;
        }
    }

    // Helper: Format Time
    function formatTime(dateStr) {
        if (!dateStr) return '';
        try {
            const d = new Date(dateStr);
            return d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        } catch (e) {
            return '';
        }
    }

    // Attach Event Listeners on DOM Ready
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('filter-search');
        const clearSearchBtn = document.getElementById('clear-search-btn');
        const actionInput = document.getElementById('filter-action-type');
        const dateFrom = document.getElementById('date-from');
        const dateTo = document.getElementById('date-to');
        const perPageSelect = document.getElementById('rows-per-page');
        const resetBtn = document.getElementById('reset-filters-btn');

        // Debounced Search
        let debounceTimer;
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                if (clearSearchBtn) {
                    clearSearchBtn.classList.toggle('hidden', !searchInput.value);
                }
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    currentPage = 1;
                    loadAuditLog();
                }, 350);
            });

            searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    clearTimeout(debounceTimer);
                    currentPage = 1;
                    loadAuditLog();
                }
            });
        }

        if (clearSearchBtn) {
            clearSearchBtn.addEventListener('click', () => {
                if (searchInput) searchInput.value = '';
                clearSearchBtn.classList.add('hidden');
                currentPage = 1;
                loadAuditLog();
            });
        }

        // Dropdown & Input Listeners
        const triggerUpdate = () => {
            currentPage = 1;
            loadAuditLog();
        };

        if (actionInput) actionInput.addEventListener('change', triggerUpdate);
        if (dateFrom) dateFrom.addEventListener('change', triggerUpdate);
        if (dateTo) dateTo.addEventListener('change', triggerUpdate);

        if (perPageSelect) {
            perPageSelect.addEventListener('change', () => {
                rowsPerPage = parseInt(perPageSelect.value, 10) || 25;
                currentPage = 1;
                loadAuditLog();
            });
        }

        // Role Pills
        document.querySelectorAll('.role-pill').forEach(pill => {
            pill.addEventListener('click', () => {
                const role = pill.dataset.role || 'all';
                window.setRoleFilter(role);
            });
        });

        if (resetBtn) {
            resetBtn.addEventListener('click', window.resetAuditFilters);
        }

        // Export CSV (respects all currently applied filters)
        const exportBtn = document.getElementById('export-csv-btn');
        if (exportBtn) {
            exportBtn.addEventListener('click', () => {
                const params = getFilterParams();
                params.export = 'csv';

                const url = window.api && typeof window.api.buildUrl === 'function'
                    ? window.api.buildUrl('/admin/audit-logs', params)
                    : null;
                if (!url) return;

                // Direct navigation: the endpoint responds with
                // Content-Disposition: attachment so the browser downloads it
                const link = document.createElement('a');
                link.href = url;
                document.body.appendChild(link);
                link.click();
                link.remove();

                if (window.utils && window.utils.showToast) {
                    window.utils.showToast('Audit trail export started', 'info');
                }
            });
        }

        // Pagination Clicks
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('button[data-page]');
            if (btn) {
                const p = parseInt(btn.getAttribute('data-page'), 10);
                if (p && p !== currentPage) {
                    currentPage = p;
                    loadAuditLog();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            }
        });

        const prevBtn = document.getElementById('prev-btn');
        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    loadAuditLog();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            });
        }

        const nextBtn = document.getElementById('next-btn');
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                currentPage++;
                loadAuditLog();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }

        // Modal Close Listeners
        document.querySelectorAll('.detail-close').forEach(btn => {
            btn.addEventListener('click', closeDetailModal);
        });

        const detailModal = document.getElementById('audit-detail-modal');
        if (detailModal) {
            detailModal.addEventListener('click', (e) => {
                if (e.target === detailModal) {
                    closeDetailModal();
                }
            });
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeDetailModal();
            }
        });

        // Expose loadAuditLog globally
        window.loadAuditLog = loadAuditLog;

        // Initial Data Load
        loadAuditLog();
    });

})();
