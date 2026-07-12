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
            const response = await window.api.get('/admin/users.php', {
                search: document.getElementById('user-search').value,
                status: document.getElementById('filter-status').value,
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
            tbody.innerHTML = '<tr><td colspan="7" class="py-12 text-center font-bold opacity-40">No users found.</td></tr>';
            return;
        }

        tbody.innerHTML = users.map(u => {
            const name = u.full_name || 'User';
            const initials = name.split(' ').filter(Boolean).map(n => n ? n[0] : '').join('').toUpperCase() || 'U';
            const statusVal = u.status || 'pending';
            const statusClass = statusVal === 'active' ? 'badge-success' : (statusVal === 'suspended' ? 'badge-neutral' : 'badge-warn');
            const statusText = statusVal.toUpperCase();
            const gradient = window.FinzoUtils.getAvatarGradient(initials);
            
            const avatarHtml = u.profile_picture_url 
                ? `<img src="${u.profile_picture_url}" class="admin-avatar object-cover border border-slate-200 dark:border-white/10" alt="${name}">` 
                : (u.avatar_url ? `<img src="${u.avatar_url}" class="admin-avatar object-cover border border-slate-200 dark:border-white/10" alt="${name}">` : `<div class="admin-avatar" style="background:${gradient};color:#fff;">${initials}</div>`);

            const balanceStr = u.balance !== null && u.balance !== undefined ? '₹' + parseFloat(u.balance).toLocaleString('en-IN', {minimumFractionDigits: 2}) : 'N/A';
            const lastActive = u.last_login_at ? window.FinzoUtils.formatDate(u.last_login_at) : 'Never';
            
            return `
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
                <td class="text-[11px] opacity-60 font-semibold">${lastActive}</td>
                <td class="text-right">
                    <a href="admin-user-detail.html?id=${u.id}" class="admin-btn-secondary px-4 py-2 text-[11px] uppercase tracking-wider font-bold rounded-xl inline-flex items-center gap-2">
                        <span class="material-symbols-outlined !text-[14px]">visibility</span> View
                    </a>
                </td>
            </tr>`;
        }).join('');
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
                html += `<button class="page-num ${i === currentPage ? 'admin-btn-primary active' : 'admin-btn-secondary'} w-8 h-8 p-0 rounded-lg text-xs" data-page="${i}">${i}</button>`;
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
            searchInput.addEventListener('keyup', (e) => {
                if (e.key === 'Enter') {
                    clearTimeout(searchTimeout);
                    currentPage = 1;
                    loadUsers();
                }
            });
        }

        const statusInput = document.getElementById('filter-status');
        if (statusInput) {
            statusInput.addEventListener('change', () => {
                currentPage = 1;
                loadUsers();
            });
        }

        const clearBtn = document.getElementById('clear-filters');
        if (clearBtn) {
            clearBtn.addEventListener('click', (e) => {
                e.preventDefault();
                document.getElementById('user-search').value = '';
                document.getElementById('filter-status').value = 'all';
                
                // Reset custom dropdown labels
                document.querySelectorAll('.dropdown-label').forEach(label => {
                    const dropdown = label.closest('.finzo-dropdown');
                    if (dropdown) {
                        const defaultOpt = dropdown.querySelector('.finzo-dropdown-option[data-value="all"]');
                        if (defaultOpt) label.textContent = defaultOpt.textContent.trim();
                        dropdown.querySelectorAll('.finzo-dropdown-option').forEach(opt => opt.classList.remove('is-selected'));
                        if (defaultOpt) defaultOpt.classList.add('is-selected');
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

        // Export Modal Logic
        const exportBtn = document.getElementById('open-export-modal');
        const modal = document.getElementById('export-modal');
        if (exportBtn && modal) {
            exportBtn.addEventListener('click', (e) => {
                e.preventDefault();
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            });
        }

        document.querySelectorAll('.close-export-modal').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
            });
        });

        const startExport = document.getElementById('start-export');
        if (startExport) {
            startExport.addEventListener('click', function(e) {
                e.preventDefault();
                window.api.setButtonLoading(this, true, 'Generating...');
                
                // Build the URL with current filters
                const search = document.getElementById('user-search') ? document.getElementById('user-search').value : '';
                const status = document.getElementById('filter-status') ? document.getElementById('filter-status').value : 'all';
                
                const queryParams = new URLSearchParams({ search, status }).toString();
                const exportUrl = `${window.api.baseUrl}/admin/export-users.php?${queryParams}`;
                
                // Trigger download
                window.location.href = exportUrl;
                
                setTimeout(() => {
                    this.textContent = 'Export Started!';
                    setTimeout(() => {
                        window.api.setButtonLoading(this, false);
                        modal.classList.add('hidden');
                        document.body.classList.remove('overflow-hidden');
                    }, 1000);
                }, 1000);
            });
        }
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', () => {
        bindEvents();
        loadUsers();
    });
})();
