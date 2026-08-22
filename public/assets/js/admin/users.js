/**
 * Finzo Admin — User Management Page Handler
 */

(function() {
    let currentPage = 1;

    /**
     * Load users from API with current filters
     */
    async function loadUsers() {
        // Show skeletons (handled by CSS/existing table state)
        const tbody = document.querySelector('#users-table tbody');
        if (tbody) tbody.style.opacity = '0.5';

        try {
            const response = await window.api.get('/users', {
                search: document.getElementById('user-search')?.value?.trim() || '',
                status: document.getElementById('filter-status')?.value || 'all',
                activity: document.getElementById('filter-activity')?.value || 'all',
                page: currentPage,
                per_page: 10
            });

            if (response.success) {
                const data = response.data || response;
                renderTable(data.users || []);
                updatePagination(data.total ?? 0, data.pages ?? 1);
                if (data.stats) {
                    updateStats(data.stats);
                }
            }
        } catch (error) {
            console.error('Failed to load users:', error);
        } finally {
            if (tbody) tbody.style.opacity = '1';
        }
    }

    /**
     * Render user rows into the table
     */
    function renderTable(users) {
        const tbody = document.querySelector('#users-table tbody');
        if (!tbody) return;

        if (!users.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="py-12 text-center"><div class="flex flex-col items-center justify-center"><span class="material-symbols-outlined text-4xl text-text-muted/30 mb-3">inventory_2</span><div class="text-sm font-bold text-text-muted uppercase tracking-widest">No users found.</div></div></td></tr>';
            return;
        }

        // Clear existing skeletons efficiently
        tbody.innerHTML = '';

        // Build the fragment string and insert efficiently
        let rowsHtml = '';
        users.forEach(u => {
            const name = u.full_name || 'User';
            const initials = name.split(' ').filter(Boolean).map(n => n ? n[0] : '').join('').toUpperCase() || 'U';
            const statusVal = u.status || 'pending';
            const statusClass = statusVal === 'active' ? 'badge-success' : (statusVal === 'suspended' ? 'badge-danger' : 'badge-warning');
            const statusText = statusVal.toUpperCase();
            const gradient = window.FinzoUtils && window.FinzoUtils.getAvatarGradient ? window.FinzoUtils.getAvatarGradient(initials) : 'linear-gradient(135deg, #10b981, #059669)';
            
            const fallbackAvatar = `<div class="admin-avatar !w-10 !h-10 !rounded-xl text-[12px] font-bold text-white flex items-center justify-center shadow-inner" style="background: ${gradient}">${initials}</div>`;
            const avatarUrl = u.profile_picture_url || u.avatar_url;
            // Build HTML entities programmatically so the source cannot be mangled by formatters (XSS prevention)
            const AMP_CH = String.fromCharCode(38);   // &
            const Q_CH = String.fromCharCode(34);     // "
            const LT_CH = String.fromCharCode(60);    // <
            const GT_CH = String.fromCharCode(62);    // >
            const escHtml = function(s) {
                return String(s)
                    .replace(new RegExp(Q_CH, 'g'), AMP_CH + 'quot;')
                    .replace(new RegExp(LT_CH, 'g'), AMP_CH + 'lt;')
                    .replace(new RegExp(GT_CH, 'g'), AMP_CH + 'gt;');
            };
            const escapedFallback = escHtml(fallbackAvatar);
            const escapedName = escHtml(name);
            const avatarHtml = avatarUrl 
                ? `<img src="${avatarUrl}" class="admin-avatar !w-10 !h-10 !rounded-xl object-cover border border-border-subtle" alt="${escapedName}" onerror="this.outerHTML='${escapedFallback}'">` 
                : fallbackAvatar;

            const joinedDate = u.created_at ? (window.FinzoUtils ? window.FinzoUtils.formatDate(u.created_at) : u.created_at) : '—';
            const lastActive = u.last_login_at ? (window.FinzoUtils ? window.FinzoUtils.formatDate(u.last_login_at) : u.last_login_at) : 'Never';
            
            rowsHtml += `
            <tr>
                <td>
                    <div class="flex items-center gap-3">
                        ${avatarHtml}
                        <div>
                            <div class="font-bold text-sm">${name}</div>
                            <div class="text-[11px] opacity-60 font-mono mt-0.5">${u.customer_id || 'FNZ-USR-PENDING'}</div>
                        </div>
                    </div>
                </td>
                <td class="text-xs opacity-70">${u.email || '—'}</td>
                <td class="text-xs">${u.phone || 'N/A'}</td>
                <td><span class="status-badge ${statusClass}"><span class="status-dot"></span>${statusText}</span></td>
                <td class="text-xs font-semibold opacity-70">${joinedDate}</td>
                <td class="text-[11px] opacity-60 font-semibold">${lastActive}</td>
                <td class="text-right">
                    <a href="admin-user-detail.php?id=${u.id}" class="btn btn-secondary px-4 py-2 text-[11px] uppercase tracking-wider font-bold rounded-xl inline-flex items-center gap-2">
                        <span class="material-symbols-outlined !text-[14px]" aria-hidden="true">visibility</span> View
                    </a>
                </td>
            </tr>`;
        });
        
        // Inject single large string at once (avoids thrashing existing DOM logic during map)
        tbody.insertAdjacentHTML('beforeend', rowsHtml);
    }

    /**
     * Update pagination UI
     */
    function updatePagination(total, pages) {
        const info = document.getElementById('pagination-info');
        if (info) {
            const start = (currentPage - 1) * 10 + 1;
            const end = Math.min(currentPage * 10, total);
            info.textContent = `Showing ${total === 0 ? 0 : start}-${end} of ${total} Users`;
        }

        const container = document.getElementById('page-numbers');
        if (container) {
            let html = '';
            for (let i = 1; i <= pages; i++) {
                html += `<button class="page-num ${i === currentPage ? 'btn btn-primary active' : 'btn btn-secondary'} w-8 h-8 p-0 rounded-lg text-xs" data-page="${i}">${i}</button>`;
            }
            container.innerHTML = html;
        }

        const prevBtn = document.getElementById('prev-btn');
        const nextBtn = document.getElementById('next-btn');
        if (prevBtn) prevBtn.disabled = (currentPage === 1);
        if (nextBtn) nextBtn.disabled = (currentPage === pages || pages === 0);
    }

    /**
     * Update stat cards
     */
    function updateStats(stats) {
        const updateEl = (id, value) => {
            const el = document.getElementById(id);
            if (el) {
                el.classList.remove('skeleton');
                el.textContent = value;
            }
        };

        updateEl('stat-total-users', stats.total_users || 0);
        updateEl('stat-active-users', stats.active_users || 0);
        updateEl('stat-suspended-users', stats.suspended_users || 0);
        updateEl('stat-new-month', stats.new_this_month || 0);
        updateEl('stat-soft-deleted', stats.soft_deleted || 0);
    }

    /**
     * Bind page-specific events
     */
    function bindEvents() {
        const applyBtn = document.getElementById('apply-filters');
        if (applyBtn) {
            applyBtn.addEventListener('click', (e) => {
                e.preventDefault();
                currentPage = 1;
                loadUsers();
            });
        }

        const searchInput = document.getElementById('user-search');
        let searchTimeout;
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    currentPage = 1;
                    loadUsers();
                }, 300);
            });
        }

        ['filter-status', 'filter-activity'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('change', () => {
                    currentPage = 1;
                    loadUsers();
                });
            }
        });

        const clearBtn = document.getElementById('clear-filters');
        if (clearBtn) {
            clearBtn.addEventListener('click', (e) => {
                e.preventDefault();
                if(document.getElementById('user-search')) document.getElementById('user-search').value = '';
                if(document.getElementById('filter-status')) document.getElementById('filter-status').value = 'all';
                if(document.getElementById('filter-activity')) document.getElementById('filter-activity').value = 'all';
                
                // Reset custom dropdown labels
                document.querySelectorAll('.dropdown-label').forEach(label => {
                    const dropdown = label.closest('.finzo-dropdown');
                    if (dropdown) {
                        const defaultOpt = dropdown.querySelector('.finzo-dropdown-option[data-value="all"]') || dropdown.querySelector('.finzo-dropdown-option');
                        if (defaultOpt) {
                            label.textContent = defaultOpt.textContent.trim();
                            dropdown.querySelectorAll('.finzo-dropdown-option').forEach(opt => opt.classList.remove('is-selected'));
                            defaultOpt.classList.add('is-selected');
                        }
                    }
                });

                currentPage = 1;
                loadUsers();
            });
        }

        // Delegation for pagination numbers
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.page-num');
            if (btn) {
                currentPage = parseInt(btn.getAttribute('data-page'));
                loadUsers();
            }
        });

        const prevBtn = document.getElementById('prev-btn');
        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    loadUsers();
                }
            });
        }

        const nextBtn = document.getElementById('next-btn');
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                currentPage++;
                loadUsers();
            });
        }

        // Export Modal Open / Close & Dropdown Synchronization
        const exportModal = document.getElementById('export-modal');

        function openExportModal() {
            if (!exportModal) return;

            // Sync modal dropdowns with current page filter values
            const curStatus = document.getElementById('filter-status')?.value || 'all';
            const curActivity = document.getElementById('filter-activity')?.value || 'all';

            setModalDropdownValue('export-status-dropdown', 'export-filter-status', curStatus);
            setModalDropdownValue('export-activity-dropdown', 'export-filter-activity', curActivity);

            exportModal.classList.remove('hidden');
            exportModal.style.display = 'flex';
            document.body.classList.add('overflow-hidden');
        }

        function closeExportModal() {
            if (!exportModal) return;
            exportModal.classList.add('hidden');
            exportModal.style.display = 'none';
            document.body.classList.remove('overflow-hidden');
        }

        function setModalDropdownValue(dropdownId, inputId, val) {
            const dropdown = document.getElementById(dropdownId);
            const input = document.getElementById(inputId);
            if (input) input.value = val;
            if (dropdown) {
                const opt = dropdown.querySelector(`.finzo-dropdown-option[data-value="${val}"]`);
                const label = dropdown.querySelector('.dropdown-label');
                if (opt && label) {
                    label.textContent = opt.textContent.trim();
                    dropdown.querySelectorAll('.finzo-dropdown-option').forEach(o => o.classList.remove('is-selected'));
                    opt.classList.add('is-selected');
                }
            }
        }

        // Global delegation for modal open, close, and outside clicks
        document.addEventListener('click', (e) => {
            const exportTrigger = e.target.closest('#open-export-modal');
            if (exportTrigger) {
                e.preventDefault();
                openExportModal();
                return;
            }

            const closeTrigger = e.target.closest('.close-export-modal');
            if (closeTrigger) {
                e.preventDefault();
                closeExportModal();
                return;
            }

            if (exportModal && e.target === exportModal) {
                closeExportModal();
            }
        });

        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && exportModal && !exportModal.classList.contains('hidden')) {
                closeExportModal();
            }
        });

        // Export Confirmation & Trigger
        const startExportBtn = document.getElementById('start-export');
        if (startExportBtn) {
            startExportBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const originalHtml = this.innerHTML;
                this.disabled = true;
                this.innerHTML = '<span class="material-symbols-outlined animate-spin text-[16px]" aria-hidden="true">autorenew</span> <span>Exporting...</span>';
                
                try {
                    const search = document.getElementById('user-search') ? document.getElementById('user-search').value.trim() : '';
                    const status = document.getElementById('export-filter-status') ? document.getElementById('export-filter-status').value : 'all';
                    const activity = document.getElementById('export-filter-activity') ? document.getElementById('export-filter-activity').value : 'all';
                    const sort = 'newest';
                    
                    const params = { search, status, sort, activity };
                    const basePath = window.location.pathname.includes('/public/') ? '/Finzo/public' : '/Finzo';
                    const exportUrl = window.api && typeof window.api.buildUrl === 'function'
                        ? window.api.buildUrl('/users/export', params)
                        : `${basePath}/api/v1/users/export?${new URLSearchParams(params).toString()}`;
                    
                    // Trigger direct browser download via hidden link
                    const dlLink = document.createElement('a');
                    dlLink.href = exportUrl;
                    dlLink.setAttribute('download', 'finzo_users_' + new Date().toISOString().slice(0, 10) + '.csv');
                    document.body.appendChild(dlLink);
                    dlLink.click();
                    setTimeout(() => {
                        if (document.body.contains(dlLink)) document.body.removeChild(dlLink);
                    }, 200);
                    
                    setTimeout(() => {
                        this.innerHTML = '<span class="material-symbols-outlined text-[16px] text-success" aria-hidden="true">check_circle</span> <span>Downloaded!</span>';
                        setTimeout(() => {
                            this.disabled = false;
                            this.innerHTML = originalHtml;
                            closeExportModal();
                        }, 1200);
                    }, 800);
                } catch (err) {
                    console.error('Export failed:', err);
                    this.disabled = false;
                    this.innerHTML = originalHtml;
                }
            });
        }
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', () => {
        bindEvents();
        loadUsers();
    });
})();