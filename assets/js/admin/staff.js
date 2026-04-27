/**
 * Finzo Admin — Staff Management Page Handler
 */

(function() {
    let currentPage = 1;

    /**
     * Load staff members with current filters
     */
    async function loadStaff() {
        const tbody = document.getElementById('staff-table-body');
        if (tbody) tbody.style.opacity = '0.5';

        try {
            const response = await window.api.get('/admin/staff.php', {
                search: document.getElementById('staff-search-input').value,
                status: document.getElementById('filter-status').value,
                branch_id: document.getElementById('filter-branch').value,
                page: currentPage,
                per_page: 10
            });

            if (response.success) {
                renderTable(response.staff || []);
                updatePagination(response.total || 0, response.pages || 1);
                updateStats(response.stats || {});
            }
        } catch (error) {
            console.error('Failed to load staff:', error);
        } finally {
            if (tbody) tbody.style.opacity = '1';
        }
    }

    /**
     * Render the staff table rows
     */
    function renderTable(staff) {
        const tbody = document.getElementById('staff-table-body');
        if (!tbody) return;

        if (!staff.length) {
            tbody.innerHTML = '<tr><td colspan="9" class="py-12 text-center font-bold opacity-40">No staff members found.</td></tr>';
            return;
        }

        tbody.innerHTML = staff.map((s, index) => {
            const statusClass = s.status === 'active' ? 'badge-success' : 'badge-error';
            const countStr = String((currentPage - 1) * 10 + index + 1).padStart(2, '0');
            
            return `
            <tr class="hover:bg-slate-50/50 dark:hover:bg-white/5 transition-colors border-b border-slate-100 dark:border-white/5 last:border-0">
                <td class="font-bold opacity-30 text-[10px] tracking-widest">${countStr}</td>
                <td>
                    <div class="flex items-center gap-3">
                        <div class="admin-avatar sm premium-gradient-primary uppercase" style="color:#fff;">${s.initials}</div>
                        <div>
                            <div class="font-bold text-sm text-slate-800 dark:text-slate-100">${s.name}</div>
                            <div class="text-[9px] opacity-60 uppercase font-black tracking-tighter">${s.designation}</div>
                        </div>
                    </div>
                </td>
                <td class="text-center text-[10px] font-bold font-mono opacity-50">${s.staff_id}</td>
                <td><div class="text-[11px] font-bold">${s.branch_name}</div></td>
                <td class="text-center font-bold text-xs opacity-60">${s.transactions_today || 0}</td>
                <td class="text-right font-black text-xs text-primary dark:text-blue-400">${s.volume_formatted || '₹0'}</td>
                <td class="text-[10px] font-bold opacity-40">${s.last_active || 'Never'}</td>
                <td><span class="status-badge ${statusClass}">${s.status.toUpperCase()}</span></td>
                <td class="text-right">
                    <a href="admin-staff-detail.html?id=${s.id}" class="p-2 hover:bg-primary/5 rounded-lg transition-colors text-primary inline-flex items-center gap-1 group">
                        <span class="text-[10px] font-black uppercase tracking-widest">Manage</span>
                        <span class="material-symbols-outlined !text-xs transition-transform group-hover:translate-x-1">arrow_forward</span>
                    </a>
                </td>
            </tr>`;
        }).join('');
    }

    /**
     * Update pagination UI
     */
    function updatePagination(total, pages) {
        const info = document.getElementById('pagination-counter');
        if (info) {
            const start = total === 0 ? 0 : (currentPage - 1) * 10 + 1;
            const end = Math.min(currentPage * 10, total);
            info.textContent = `Showing ${start}-${end} of ${total} staff`;
        }

        const container = document.getElementById('page-numbers');
        if (container) {
            let html = '';
            for (let i = 1; i <= pages; i++) {
                html += `<button class="page-num ${i === currentPage ? 'admin-btn-primary active' : 'admin-btn-secondary'} w-8 h-8 p-0 rounded-lg text-xs" data-page="${i}">${i}</button>`;
            }
            container.innerHTML = html;
        }

        const prevBtn = document.getElementById('prev-page');
        const nextBtn = document.getElementById('next-page');
        if (prevBtn) {
            prevBtn.disabled = (currentPage === 1);
            prevBtn.style.opacity = (currentPage === 1 ? '0.5' : '1');
        }
        if (nextBtn) {
            nextBtn.disabled = (currentPage === pages || pages === 0);
            nextBtn.style.opacity = (currentPage === pages || pages === 0 ? '0.5' : '1');
        }
    }

    /**
     * Update high-level stat cards
     */
    function updateStats(stats) {
        const valEls = document.querySelectorAll('.admin-stat-card .admin-stat-value');
        if (valEls.length >= 4) {
            if (stats.total !== undefined) { valEls[0].textContent = stats.total; valEls[0].classList.remove('skeleton'); }
            if (stats.active !== undefined) { valEls[1].textContent = stats.active; valEls[1].classList.remove('skeleton'); }
            if (stats.suspended !== undefined) { valEls[2].textContent = stats.suspended; valEls[2].classList.remove('skeleton'); }
            if (stats.transactions_today !== undefined) { valEls[3].textContent = stats.transactions_today; valEls[3].classList.remove('skeleton'); }
        }
    }

    /**
     * Load branches for filter and modal dropdowns
     */
    async function loadBranches() {
        try {
            const response = await window.api.get('/admin/branches.php');
            if (response.success) {
                // Populate Modal Select
                const modalSelect = document.getElementById('staff-branch');
                if (modalSelect) {
                    modalSelect.innerHTML = '<option value="">Select Branch</option>';
                    response.branches.forEach(b => {
                        const opt = document.createElement('option');
                        opt.value = b.id;
                        opt.textContent = b.name;
                        modalSelect.appendChild(opt);
                    });
                }

                // Populate Filter Dropdown
                const filterMenu = document.getElementById('filter-branch-menu');
                if (filterMenu) {
                    filterMenu.innerHTML = '<div class="finzo-dropdown-option is-selected" data-value="all">All Branches</div>';
                    response.branches.forEach(b => {
                        const opt = document.createElement('div');
                        opt.className = 'finzo-dropdown-option';
                        opt.setAttribute('data-value', b.id);
                        opt.textContent = b.name;
                        filterMenu.appendChild(opt);
                    });
                }
            }
        } catch (error) {
            console.error('Failed to load branches:', error);
        }
    }

    /**
     * Bind page events
     */
    function bindEvents() {
        // Search with debounce
        const searchInput = document.getElementById('staff-search-input');
        if (searchInput) {
            searchInput.addEventListener('input', window.FinzoUtils.debounce(() => {
                currentPage = 1;
                loadStaff();
            }, 400));
        }

        // Dropdown filters
        const statusVal = document.getElementById('filter-status');
        const branchVal = document.getElementById('filter-branch');
        
        if (statusVal) statusVal.addEventListener('change', () => { currentPage = 1; loadStaff(); });
        if (branchVal) branchVal.addEventListener('change', () => { currentPage = 1; loadStaff(); });

        // Pagination
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.page-num');
            if (btn) {
                currentPage = parseInt(btn.getAttribute('data-page'));
                loadStaff();
            }
        });

        const prevBtn = document.getElementById('prev-page');
        if (prevBtn) prevBtn.addEventListener('click', () => { if (currentPage > 1) { currentPage--; loadStaff(); } });

        const nextBtn = document.getElementById('next-page');
        if (nextBtn) nextBtn.addEventListener('click', () => { currentPage++; loadStaff(); });

        // Add Staff Form
        const addForm = document.getElementById('addStaffForm');
        if (addForm) {
            addForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const submitBtn = document.getElementById('create-staff-submit');
                
                window.api.setButtonLoading(submitBtn, true, 'Creating...');
                
                try {
                    const formData = {
                        full_name: document.getElementById('staff-full-name').value,
                        email: document.getElementById('staff-email').value,
                        phone: document.getElementById('staff-phone').value,
                        branch_id: document.getElementById('staff-branch').value,
                        password: document.getElementById('temp-password-display').textContent,
                        role: 'teller'
                    };

                    const response = await window.api.post('/admin/add-staff.php', formData);

                    if (response.success) {
                        if (window.showToast) window.showToast('Staff account created successfully!', 'success');
                        else alert('Staff account created successfully!');
                        
                        document.getElementById('addStaffModal').classList.add('hidden');
                        document.getElementById('addStaffModal').classList.remove('flex');
                        addForm.reset();
                        loadStaff();
                    } else {
                        if (window.showToast) window.showToast(response.message || 'Failed to create staff', 'error');
                        else alert(response.message || 'Failed to create staff');
                    }
                } catch (error) {
                    console.error('Staff creation error:', error);
                } finally {
                    window.api.setButtonLoading(submitBtn, false);
                }
            });
        }

        // Copy password
        const copyBtn = document.getElementById('copy-password-btn');
        if (copyBtn) {
            copyBtn.addEventListener('click', function() {
                const pass = document.getElementById('temp-password-display').textContent;
                navigator.clipboard.writeText(pass).then(() => {
                    const icon = document.getElementById('copy-icon');
                    if (icon) {
                        icon.textContent = 'check';
                        setTimeout(() => icon.textContent = 'content_copy', 2000);
                    }
                });
            });
        }
    }

    /**
     * Logic for opening the Add Staff Modal
     */
    window.openAddStaffModal = async function() {
        const modal = document.getElementById('addStaffModal');
        const idInput = document.getElementById('new-staff-id');
        const passDisplay = document.getElementById('temp-password-display');

        if (!modal) return;

        // Fetch Next ID
        try {
            const response = await window.api.get('/admin/staff.php', { action: 'next_id' });
            if (response.success && idInput) idInput.value = response.next_id;
        } catch (error) {
            console.error('Failed to fetch next ID');
        }

        // Generate Temp Pass
        if (passDisplay) {
            passDisplay.textContent = "FINZO-" + Math.floor(1000 + Math.random() * 9000);
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };

    // Initialize
    document.addEventListener('DOMContentLoaded', () => {
        loadBranches();
        loadStaff();
        bindEvents();
    });
})();
