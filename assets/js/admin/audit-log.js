/**
 * Finzo Admin — Audit Log Handler
 */

(function() {
    let currentPage = 1;
    const rowsPerPage = 25;

    // Load Audit Log
    async function loadAuditLog() {
        const tbody = document.getElementById('audit-log-tbody');
        if (tbody) tbody.style.opacity = '0.5';

        try {
            const searchInput = document.getElementById('filter-search');
            const actionInput = document.getElementById('filter-action-type');
            const roleInput = document.getElementById('filter-actor-role');
            const dateFrom = document.getElementById('date-from');
            const dateTo = document.getElementById('date-to');

            const params = {
                search: searchInput ? searchInput.value : '',
                action_type: actionInput ? actionInput.value : 'all',
                actor_role: roleInput ? roleInput.value : 'all',
                from_date: dateFrom ? dateFrom.value : '',
                to_date: dateTo ? dateTo.value : '',
                page: currentPage,
                per_page: rowsPerPage
            };

            console.log('[AuditLog] Fetching with params:', params);
            const response = await window.api.get('/admin/audit-log.php', params);
            console.log('[AuditLog] Response:', response);

            if (response && response.success) {
                renderTable(response.entries, response.total, response.pages);
                updateStats(response);
            } else {
                console.warn('[AuditLog] API returned failure or empty:', response);
                if (tbody) {
                    tbody.innerHTML = '<tr><td colspan="7" class="py-20 text-center font-bold opacity-40">Failed to load audit log. Please refresh the page.</td></tr>';
                    tbody.style.opacity = '1';
                }
            }
        } catch (error) {
            console.error('[AuditLog] Failed to load audit log:', error);
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="7" class="py-20 text-center font-bold opacity-40">Error loading data. Please refresh.</td></tr>';
            }
        } finally {
            if (tbody) tbody.style.opacity = '1';
        }
    }

    // Render Table
    function renderTable(entries, total, pages) {
        const tbody = document.getElementById('audit-log-tbody');
        if (!tbody) return;

        if (!entries || !entries.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="py-20 text-center font-bold opacity-40">No audit log entries found.</td></tr>';
            updatePagination(total, pages);
            return;
        }

        tbody.innerHTML = '';
        entries.forEach((entry, i) => {
            const countVal = total - ((currentPage - 1) * rowsPerPage + i);
            const countStr = String(countVal).padStart(2, '0');
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-colors cursor-pointer';
            
            // Format details for display
            let detailsText = entry.details || '';
            const truncatedDetails = detailsText.length > 60 ? detailsText.substring(0, 60) + '...' : detailsText;
            
            tr.innerHTML = `
                <td class="font-bold opacity-30 text-[11px] tracking-widest">${String(entry.id).padStart(2, '0')}</td>
                <td>
                    <div class="font-bold text-slate-800 dark:text-slate-100">${entry.actor_display_id || entry.actor_id}</div>
                    <div class="mt-1">${getActorBadge(entry.actor_specific_role || entry.actor_role)}</div>
                </td>
                <td>${getActionBadge(entry.action_type)}</td>
                <td>
                    <div class="text-[10px] uppercase tracking-widest font-bold opacity-50">${entry.entity_type || 'SYSTEM'}</div>
                    <div class="font-mono text-xs font-bold mt-1">${entry.entity_display_id || entry.entity_id || '-'}</div>
                </td>
                <td class="font-mono text-xs opacity-60">${entry.ip_address || 'N/A'}</td>
                <td class="text-xs opacity-60" title="${detailsText.replace(/"/g, '&quot;')}">${truncatedDetails}</td>
                <td class="text-[11px] font-bold opacity-60">${entry.created_at}</td>
            `;
            
            tr.addEventListener('click', () => openDetail(entry));
            tbody.appendChild(tr);
        });

        updatePagination(total, pages);
    }

    // Get Action Badge
    function getActionBadge(action) {
        const type = (action || '').toLowerCase();
        let badgeClass = '';
        
        if (['login', 'logout'].includes(type)) {
            badgeClass = 'badge-transfer'; // blue
        } else if (['create', 'approve'].includes(type)) {
            badgeClass = 'badge-deposit'; // green
        } else if (['delete', 'reject', 'suspend'].includes(type)) {
            badgeClass = 'badge-withdrawal'; // red
        } else if (['update', 'unlock', 'reset_password'].includes(type)) {
            badgeClass = 'badge-pending'; // amber
        }
        
        const label = type.replace(/_/g, ' ').toUpperCase();
        
        if (badgeClass) {
            return `<span class="badge ${badgeClass}">${label}</span>`;
        } else {
            return `<span class="badge" style="background:#e2e8f0;color:#64748b">${label}</span>`;
        }
    }

    // Get Actor Badge
    function getActorBadge(role) {
        const r = (role || '').toLowerCase();
        if (r === 'super_admin') {
            return `<span class="badge" style="background:#faf5ff;color:#6b21a8;border:1px solid #d8b4fe">SUPER ADMIN</span>`;
        } else if (r === 'admin') {
            return `<span class="badge" style="background:#dbeafe;color:#1e40af">ADMIN</span>`;
        } else if (r === 'moderator') {
            return `<span class="badge" style="background:#e0f2fe;color:#0369a1">MODERATOR</span>`;
        } else if (r === 'staff') {
            return `<span class="badge" style="background:#ede9fe;color:#5b21b6">STAFF</span>`;
        } else if (r === 'system') {
            return `<span class="badge" style="background:#f1f5f9;color:#475569">SYSTEM</span>`;
        } else if (r === 'user') {
            return `<span class="badge" style="background:#ccfbf1;color:#0f766e">USER</span>`;
        }
        return `<span class="badge">${r.replace(/_/g, ' ').toUpperCase()}</span>`;
    }

    // Open Detail Modal
    function openDetail(entry) {
        const elId = document.getElementById('detail-id');
        const elAction = document.getElementById('detail-action');
        const elActorRole = document.getElementById('detail-actor-role');
        const elActorId = document.getElementById('detail-actor-id');
        const elEntityType = document.getElementById('detail-entity-type');
        const elEntityId = document.getElementById('detail-entity-id');
        const elIp = document.getElementById('detail-ip');
        const elTimestamp = document.getElementById('detail-timestamp');
        const elDetails = document.getElementById('detail-details');
        
        if (elId) elId.textContent = entry.id;
        if (elAction) elAction.innerHTML = getActionBadge(entry.action_type);
        if (elActorRole) elActorRole.innerHTML = getActorBadge(entry.actor_specific_role || entry.actor_role);
        if (elActorId) elActorId.textContent = entry.actor_display_id || entry.actor_id;
        if (elEntityType) elEntityType.textContent = (entry.entity_type || 'SYSTEM').toUpperCase();
        if (elEntityId) elEntityId.textContent = entry.entity_display_id || entry.entity_id || '-';
        if (elIp) elIp.textContent = entry.ip_address || 'N/A';
        if (elTimestamp) elTimestamp.textContent = entry.created_at;
        if (elDetails) elDetails.textContent = entry.details || 'No details provided.';
        
        const detailModal = document.getElementById('detail-modal');
        if (detailModal) detailModal.style.display = 'flex';
    }

    // Update Stats
    function updateStats(data) {
        const elTotal = document.getElementById('stat-total');
        const elAdmin = document.getElementById('stat-admin');
        const elStaff = document.getElementById('stat-staff');
        const elUser = document.getElementById('stat-user');
        
        if (elTotal) {
            elTotal.textContent = window.utils && window.utils.formatNumber ? 
                window.utils.formatNumber(data.total || 0) : 
                (data.total || 0).toLocaleString();
        }
        
        if (data.stats) {
            if (elAdmin) elAdmin.textContent = String(data.stats.admin || 0);
            if (elStaff) elStaff.textContent = String(data.stats.staff || 0);
            if (elUser) elUser.textContent = String(data.stats.user || 0);
        }
    }

    // Update Pagination
    function updatePagination(total, pages) {
        const info = document.getElementById('pagination-info');
        if (info) {
            info.textContent = `Showing ${Math.min((currentPage - 1) * rowsPerPage + 1, total || 0)}-${Math.min(currentPage * rowsPerPage, total || 0)} of ${total || 0}`;
        }

        const prevBtn = document.getElementById('prev-btn');
        const nextBtn = document.getElementById('next-btn');
        const pagNumbers = document.getElementById('page-numbers');

        if (prevBtn) {
            prevBtn.disabled = (currentPage === 1 || total === 0);
            prevBtn.classList.toggle('opacity-50', currentPage === 1 || total === 0);
            prevBtn.classList.toggle('cursor-not-allowed', currentPage === 1 || total === 0);
        }
        if (nextBtn) {
            nextBtn.disabled = (currentPage === pages || pages === 0);
            nextBtn.classList.toggle('opacity-50', currentPage === pages || pages === 0);
            nextBtn.classList.toggle('cursor-not-allowed', currentPage === pages || pages === 0);
        }

        if (pagNumbers) {
            let html = '';
            for (let i = 1; i <= pages; i++) {
                if (pages > 5 && i > 3 && i < pages) {
                    if (i === 4) html += '<span class="px-2 opacity-50">...</span>';
                    continue;
                }
                const activeClass = i === currentPage ? 'bg-primary text-white' : 'bg-slate-100 dark:bg-white/5 text-slate-400 hover:text-primary cursor-pointer';
                html += `<span data-page="${i}" class="w-8 h-8 flex items-center justify-center ${activeClass} rounded-lg text-xs font-bold transition-all">${i}</span>`;
            }
            pagNumbers.innerHTML = html;
        }
    }

    // Export
    function exportAuditLog() {
        const searchInput = document.getElementById('filter-search');
        const actionInput = document.getElementById('filter-action-type');
        const roleInput = document.getElementById('filter-actor-role');
        const dateFrom = document.getElementById('date-from');
        const dateTo = document.getElementById('date-to');
        
        const filters = {
            search: searchInput ? searchInput.value : '',
            action_type: actionInput ? actionInput.value : 'all',
            actor_role: roleInput ? roleInput.value : 'all',
            from_date: dateFrom ? dateFrom.value : '',
            to_date: dateTo ? dateTo.value : '',
            export: 'xlsx'
        };
        
        window.location.href = window.api.buildUrl('/admin/audit-log.php', filters);
    }

    // Init
    document.addEventListener('DOMContentLoaded', () => {
        // Apply Filters
        const applyBtn = document.getElementById('apply-filters');
        if (applyBtn) {
            applyBtn.addEventListener('click', () => {
                currentPage = 1;
                loadAuditLog();
            });
        }

        // Live Filter Updates
        const searchInput = document.getElementById('filter-search');
        const actionInput = document.getElementById('filter-action-type');
        const roleInput = document.getElementById('filter-actor-role');
        const dateFrom = document.getElementById('date-from');
        const dateTo = document.getElementById('date-to');

        let debounceTimer;
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    currentPage = 1;
                    loadAuditLog();
                }, 300);
            });
            searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    clearTimeout(debounceTimer);
                    currentPage = 1;
                    loadAuditLog();
                }
            });
        }

        const triggerFilterUpdate = () => {
            currentPage = 1;
            loadAuditLog();
        };

        if (actionInput) actionInput.addEventListener('change', triggerFilterUpdate);
        if (roleInput) roleInput.addEventListener('change', triggerFilterUpdate);
        if (dateFrom) dateFrom.addEventListener('change', triggerFilterUpdate);
        if (dateTo) dateTo.addEventListener('change', triggerFilterUpdate);

        // Clear Filters
        const clearBtn = document.getElementById('clear-filters');
        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                const searchInput = document.getElementById('filter-search');
                const actionInput = document.getElementById('filter-action-type');
                const roleInput = document.getElementById('filter-actor-role');
                const dateFrom = document.getElementById('date-from');
                const dateTo = document.getElementById('date-to');

                if (searchInput) searchInput.value = '';
                if (dateFrom) dateFrom.value = '';
                if (dateTo) dateTo.value = '';
                if (actionInput) actionInput.value = 'all';
                if (roleInput) roleInput.value = 'all';
                
                // Update dropdown UI labels
                const actionLabel = document.querySelector('#action-type-dropdown .dropdown-label');
                if (actionLabel) actionLabel.textContent = 'All Actions';
                
                const roleLabel = document.querySelector('#actor-role-dropdown .dropdown-label');
                if (roleLabel) roleLabel.textContent = 'All Roles';
                
                // Also reset dropdown active states if needed
                document.querySelectorAll('.finzo-dropdown-option').forEach(opt => {
                    opt.classList.remove('is-selected');
                });
                document.querySelectorAll('.finzo-dropdown-option[data-value="all"]').forEach(opt => {
                    opt.classList.add('is-selected');
                });

                currentPage = 1;
                loadAuditLog();
            });
        }

        // Pagination Clicks
        document.addEventListener('click', (e) => {
            const span = e.target.closest('span[data-page]');
            if (span) {
                currentPage = parseInt(span.getAttribute('data-page'));
                loadAuditLog();
            }
        });

        const prevBtn = document.getElementById('prev-btn');
        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    loadAuditLog();
                }
            });
        }

        const nextBtn = document.getElementById('next-btn');
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                currentPage++;
                loadAuditLog();
            });
        }

        // Detail Modal Close
        document.querySelectorAll('.detail-close').forEach(btn => {
            btn.addEventListener('click', () => {
                const detailModal = document.getElementById('detail-modal');
                if (detailModal) detailModal.style.display = 'none';
            });
        });

        // Export Modal
        const openExport = document.getElementById('open-export-modal');
        if (openExport) {
            openExport.addEventListener('click', () => {
                const exportModal = document.getElementById('export-modal');
                if (exportModal) exportModal.style.display = 'flex';
            });
        }

        document.querySelectorAll('.close-export-modal').forEach(btn => {
            btn.addEventListener('click', () => {
                const exportModal = document.getElementById('export-modal');
                if (exportModal) exportModal.style.display = 'none';
            });
        });

        const startExport = document.getElementById('start-export');
        if (startExport) {
            startExport.addEventListener('click', () => {
                exportAuditLog();
                const exportModal = document.getElementById('export-modal');
                if (exportModal) exportModal.style.display = 'none';
            });
        }

        // Initial Load
        loadAuditLog();
    });

})();
