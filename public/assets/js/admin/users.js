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
                search: document.getElementById('user-search').value,
                sort: document.getElementById('filter-sort').value,
                activity: document.getElementById('filter-activity').value,
                balance: document.getElementById('filter-balance').value,
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
            tbody.innerHTML = '<tr><td colspan=\"7\" class=\"py-12 text-center\"><div class=\"flex flex-col items-center justify-center\"><span class=\"material-symbols-outlined text-4xl text-text-muted/30 mb-3\">inventory_2</span><div class=\"text-sm font-bold text-text-muted uppercase tracking-widest\">No users found.</div></div></td></tr>';
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
            const avatarHtml = avatarUrl 
                ? `<img src="${avatarUrl}" class="admin-avatar !w-10 !h-10 !rounded-xl object-cover border border-border-subtle" alt="${name}" onerror="this.outerHTML='${fallbackAvatar.replace(/"/g, '&quot;')}'">` 
                : fallbackAvatar;

            const balanceStr = u.balance !== null && u.balance !== undefined ? '₹' + parseFloat(u.balance).toLocaleString('en-IN', {minimumFractionDigits: 2}) : '₹0.00';
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
                <td class="text-xs font-mono font-bold">${balanceStr}</td>
                <td><span class="status-badge ${statusClass}"><span class="status-dot"></span>${statusText}</span></td>
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

        const statusInput = document.getElementById('filter-status');
        if (statusInput) {
            statusInput.addEventListener('change', () => {
                currentPage = 1;
                loadUsers();
            });
        }

        ['filter-sort', 'filter-activity', 'filter-balance'].forEach(id => {
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
                document.getElementById('user-search').value = '';
                if(document.getElementById('filter-status')) document.getElementById('filter-status').value = 'all';
                if(document.getElementById('filter-sort')) document.getElementById('filter-sort').value = 'newest';
                if(document.getElementById('filter-activity')) document.getElementById('filter-activity').value = 'all';
                if(document.getElementById('filter-balance')) document.getElementById('filter-balance').value = 'all';
                
                // Reset custom dropdown labels
                document.querySelectorAll('.dropdown-label').forEach(label => {
                    const dropdown = label.closest('.finzo-dropdown');
                    if (dropdown) {
                        const defaultOpt = dropdown.querySelector('.finzo-dropdown-option[data-value="all"], .finzo-dropdown-option[data-value="newest"]') || dropdown.querySelector('.finzo-dropdown-option');
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

        // Export CSV Action
        const exportBtn = document.getElementById('open-export-modal');
        if (exportBtn) {
            exportBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const originalHtml = this.innerHTML;
                this.disabled = true;
                this.innerHTML = '<span class="material-symbols-outlined animate-spin text-[18px]" aria-hidden="true">autorenew</span> <span>Exporting...</span>';
                
                try {
                    const search = document.getElementById('user-search') ? document.getElementById('user-search').value : '';
                    const status = document.getElementById('filter-status') ? document.getElementById('filter-status').value : 'all';
                    const sort = document.getElementById('filter-sort') ? document.getElementById('filter-sort').value : 'newest';
                    const activity = document.getElementById('filter-activity') ? document.getElementById('filter-activity').value : 'all';
                    const balance = document.getElementById('filter-balance') ? document.getElementById('filter-balance').value : 'all';
                    
                    const params = { search, status, sort, activity, balance };
                    const exportUrl = window.api && typeof window.api.buildUrl === 'function'
                        ? window.api.buildUrl('/users/export', params)
                        : `/Finzo/api/v1/users/export?${new URLSearchParams(params).toString()}`;
                    
                    // Trigger direct browser download
                    window.location.href = exportUrl;
                    
                    setTimeout(() => {
                        this.innerHTML = '<span class="material-symbols-outlined text-[18px] text-success" aria-hidden="true">check_circle</span> <span>Downloaded!</span>';
                        setTimeout(() => {
                            this.disabled = false;
                            this.innerHTML = originalHtml;
                        }, 1500);
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
