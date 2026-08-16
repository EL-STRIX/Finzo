/**
 * Finzo Admin — Staff Management Page Handler
 * Enterprise Staff Operations, 3-Tier Filter Architecture, CSV Streaming, Dynamic Pagination & Full Add-Staff Suite
 */

(function() {
    let currentPage = 1;
    const PER_PAGE = 20;

    /**
     * Load staff members with current filters and pagination
     */
    async function loadStaff() {
        const tbody = document.getElementById('ds-table-body');
        if (tbody) tbody.style.opacity = '0.5';

        const searchVal = document.getElementById('staff-search-input')?.value?.trim() || '';
        const statusVal = document.getElementById('filter-status')?.value || 'all';
        const roleVal = document.getElementById('filter-role')?.value || 'all';
        const branchVal = document.getElementById('filter-branch')?.value || 'all';

        try {
            const response = await window.api.get('/staff', {
                search: searchVal,
                status: statusVal,
                role: roleVal,
                branch_id: branchVal === 'all' ? 0 : branchVal,
                page: currentPage,
                per_page: PER_PAGE
            });

            if (response && (response.success || response.status === 200 || response.data)) {
                const payload = response.data || response;
                const staffList = payload.staff || response.staff || [];
                const totalCount = payload.total ?? response.total ?? staffList.length;
                const totalPages = payload.pages ?? response.pages ?? 1;

                renderTable(staffList);
                updatePagination(totalCount, totalPages);

                const stats = payload.stats || response.stats;
                if (stats) {
                    updateStats(stats);
                }
            } else {
                renderTable([]);
                updatePagination(0, 1);
            }
        } catch (error) {
            console.error('Failed to load staff:', error);
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="9" class="py-12 text-center text-error font-bold text-xs uppercase tracking-wider">Failed to load staff records. Please check your connection.</td></tr>';
            }
        } finally {
            if (tbody) tbody.style.opacity = '1';
        }
    }

    /**
     * Update top stat cards
     */
    function updateStats(stats) {
        const elements = {
            'stat-total-staff': stats.total_staff ?? 0,
            'stat-active-staff': stats.active_staff ?? 0,
            'stat-suspend-staff': stats.suspended_staff ?? 0,
            'stat-transactions-today': stats.transactions_today ?? 0
        };

        for (const [id, value] of Object.entries(elements)) {
            const el = document.getElementById(id);
            if (el) {
                el.classList.remove('skeleton', 'min-w-[40px]');
                el.textContent = Number(value).toLocaleString();
            }
        }
    }

    /**
     * Render the staff table rows
     */
    function renderTable(staff) {
        const tbody = document.getElementById('ds-table-body');
        if (!tbody) return;

        if (!staff || !staff.length) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" class="py-16 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-14 h-14 rounded-2xl bg-surface-subtle border border-border-subtle flex items-center justify-center text-text-secondary mb-3">
                                <span class="material-symbols-outlined text-3xl" aria-hidden="true">group_off</span>
                            </div>
                            <div class="text-sm font-bold text-text-primary uppercase tracking-wider">No Staff Found</div>
                            <p class="text-xs text-text-secondary mt-1">Try adjusting your filters or search query.</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = staff.map((s, index) => {
            const countStr = String((currentPage - 1) * PER_PAGE + index + 1).padStart(2, '0');
            const isActive = (s.status === 'active');
            
            const statusBadge = isActive
                ? `<span class="badge-tag bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>Active</span>`
                : `<span class="badge-tag bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20 text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>Suspended</span>`;

            const initials = s.profile_photo_url 
                ? `<img src="${s.profile_photo_url}" alt="${s.full_name}" class="w-9 h-9 rounded-xl object-cover border border-border-subtle shrink-0">` 
                : `<div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary/20 to-primary/5 text-primary border border-primary/20 font-black text-xs flex items-center justify-center uppercase shrink-0">${s.initials || 'ST'}</div>`;

            const branchName = s.branch_name || 'Unassigned';
            const joinedDate = s.date_of_joining ? new Date(s.date_of_joining).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '—';
            const txnCount = Number(s.txn_count || 0);
            const txnAmount = Number(s.txn_amount || 0);

            return `
            <tr class="hover:bg-surface-hover transition-colors border-b border-border-subtle last:border-0 group">
                <td class="font-bold opacity-40 text-[11px] tracking-widest text-center !px-2">${countStr}</td>
                <td class="!px-3 py-2.5">
                    <div class="flex items-center gap-2.5 min-w-0">
                        ${initials}
                        <div class="min-w-0">
                            <div class="font-bold text-sm text-text-primary group-hover:text-primary transition-colors truncate">${s.full_name || 'Unknown'}</div>
                            <div class="text-[10px] text-text-secondary uppercase font-bold tracking-tight truncate">${s.designation || 'Staff'} • ${s.department || 'Operations'}</div>
                        </div>
                    </div>
                </td>
                <td class="text-center !px-2">
                    <span class="px-2 py-0.5 bg-surface-subtle border border-border-subtle rounded-md text-[10px] font-mono font-bold text-text-secondary">${s.staff_id || '—'}</span>
                </td>
                <td class="!px-3">
                    <div class="text-xs font-bold text-text-primary flex items-center gap-1 min-w-0">
                        <span class="material-symbols-outlined text-sm text-text-secondary shrink-0" aria-hidden="true">store</span>
                        <span class="truncate">${branchName}</span>
                    </div>
                </td>
                <td class="text-center !px-2">
                    ${statusBadge}
                </td>
                <td class="text-center font-mono font-bold text-xs text-text-secondary !px-2">${txnCount.toLocaleString()}</td>
                <td class="text-right font-mono font-bold text-xs text-text-primary !px-3">
                    ₹${txnAmount.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                </td>
                <td class="text-center text-[11px] font-bold text-text-secondary !px-2 whitespace-nowrap">${joinedDate}</td>
                <td class="text-right !px-3">
                    <a href="/Finzo/public/views/admin/admin-staff-detail.php?id=${s.id}" class="btn btn-secondary !px-3 !py-1 text-[11px] font-bold uppercase tracking-wider inline-flex items-center gap-1 shadow-sm hover:bg-primary hover:text-white transition-all group/btn">
                        <span>View</span>
                        <span class="material-symbols-outlined text-[13px] transition-transform group-hover/btn:translate-x-0.5" aria-hidden="true">arrow_forward</span>
                    </a>
                </td>
            </tr>`;
        }).join('');
    }

    /**
     * Update pagination controls
     */
    function updatePagination(total, totalPages) {
        const counter = document.getElementById('pagination-counter');
        const prevBtn = document.getElementById('prev-page');
        const nextBtn = document.getElementById('next-page');
        const numbersContainer = document.getElementById('page-numbers');

        if (counter) {
            const start = total === 0 ? 0 : (currentPage - 1) * PER_PAGE + 1;
            const end = Math.min(currentPage * PER_PAGE, total);
            counter.textContent = `Showing ${start}-${end} of ${total} staff`;
        }

        if (prevBtn) prevBtn.disabled = (currentPage <= 1);
        if (nextBtn) nextBtn.disabled = (currentPage >= totalPages || totalPages === 0);

        if (numbersContainer) {
            let html = '';
            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                    const isActive = (i === currentPage);
                    html += `<button class="page-num w-9 h-9 flex items-center justify-center rounded-xl text-xs font-bold transition-all ${isActive ? 'bg-primary text-white shadow-sm' : 'bg-surface border border-border-subtle text-text-secondary hover:bg-surface-hover hover:text-text-primary'}" data-page="${i}">${i}</button>`;
                } else if (i === currentPage - 2 || i === currentPage + 2) {
                    html += `<span class="w-9 h-9 flex items-center justify-center text-text-secondary text-xs">...</span>`;
                }
            }
            numbersContainer.innerHTML = html;
        }
    }

    /**
     * Load branch options dynamically
     */
    async function loadBranches() {
        try {
            const response = await window.api.get('/branches');
            const payload = response?.data || response;
            const branches = payload?.branches || response?.branches || [];

            if (Array.isArray(branches) && branches.length > 0) {
                // Populate Filter Dropdown
                const filterMenu = document.getElementById('filter-branch-menu');
                if (filterMenu) {
                    filterMenu.innerHTML = '<div class="finzo-dropdown-option is-selected" data-value="all">All Branches</div>';
                    branches.forEach(b => {
                        const opt = document.createElement('div');
                        opt.className = 'finzo-dropdown-option';
                        opt.setAttribute('data-value', b.id);
                        opt.textContent = `${b.name} (${b.branch_code || 'BR'})`;
                        filterMenu.appendChild(opt);
                    });
                }

                // Populate Export Modal Dropdown
                const exportMenu = document.getElementById('export-branch-menu');
                if (exportMenu) {
                    exportMenu.innerHTML = '<div class="finzo-dropdown-option is-selected" data-value="all">All Branches</div>';
                    branches.forEach(b => {
                        const opt = document.createElement('div');
                        opt.className = 'finzo-dropdown-option';
                        opt.setAttribute('data-value', b.id);
                        opt.textContent = `${b.name} (${b.branch_code || 'BR'})`;
                        exportMenu.appendChild(opt);
                    });
                }

                // Populate Add Staff Modal Branch Select
                const addStaffBranchSelect = document.getElementById('new-staff-branch');
                if (addStaffBranchSelect) {
                    addStaffBranchSelect.innerHTML = '<option value="">Head Office / Unassigned</option>';
                    branches.forEach(b => {
                        const opt = document.createElement('option');
                        opt.value = b.id;
                        opt.textContent = `${b.name} (${b.branch_code || 'BR'})`;
                        addStaffBranchSelect.appendChild(opt);
                    });
                }
            }
        } catch (error) {
            console.error('Failed to load branches:', error);
        }
    }

    /**
     * Set Modal Dropdown Value and synchronized UI label
     */
    function setModalDropdownValue(dropdownId, inputId, val) {
        const dropdown = document.getElementById(dropdownId);
        const input = document.getElementById(inputId);
        if (input) input.value = val;
        if (dropdown) {
            const opt = dropdown.querySelector(`.finzo-dropdown-option[data-value="${val}"]`) || dropdown.querySelector('.finzo-dropdown-option');
            const label = dropdown.querySelector('.dropdown-label');
            if (opt && label) {
                label.textContent = opt.textContent.trim();
                dropdown.querySelectorAll('.finzo-dropdown-option').forEach(o => o.classList.remove('is-selected'));
                opt.classList.add('is-selected');
            }
        }
    }

    /**
     * Open CSV Export Modal
     */
    function openExportModal() {
        const exportModal = document.getElementById('export-modal');
        if (!exportModal) return;

        const curStatus = document.getElementById('filter-status')?.value || 'all';
        const curRole = document.getElementById('filter-role')?.value || 'all';
        const curBranch = document.getElementById('filter-branch')?.value || 'all';

        setModalDropdownValue('export-status-dropdown', 'export-filter-status', curStatus);
        setModalDropdownValue('export-role-dropdown', 'export-filter-role', curRole);
        setModalDropdownValue('export-branch-dropdown', 'export-filter-branch', curBranch);

        exportModal.classList.remove('hidden');
        exportModal.style.display = 'flex';
        document.body.classList.add('overflow-hidden');
    }

    /**
     * Close CSV Export Modal
     */
    function closeExportModal() {
        const exportModal = document.getElementById('export-modal');
        if (!exportModal) return;
        exportModal.classList.add('hidden');
        exportModal.style.display = 'none';
        document.body.classList.remove('overflow-hidden');
    }

    /**
     * Start Staff CSV Download
     */
    function startStaffExport() {
        const status = document.getElementById('export-filter-status')?.value || 'all';
        const role = document.getElementById('export-filter-role')?.value || 'all';
        const branchId = document.getElementById('export-filter-branch')?.value || 'all';
        const search = document.getElementById('staff-search-input')?.value?.trim() || '';

        const params = new URLSearchParams({
            search: search,
            status: status,
            role: role,
            branch_id: branchId === 'all' ? '0' : branchId
        });

        const exportUrl = `/Finzo/api/v1/staff/export?${params.toString()}`;
        
        const downloadLink = document.createElement('a');
        downloadLink.href = exportUrl;
        downloadLink.setAttribute('download', `finzo_staff_${new Date().toISOString().slice(0, 10)}.csv`);
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);

        if (window.showToast) {
            window.showToast('Staff CSV export download started!', 'success');
        }

        closeExportModal();
    }

    /**
     * Open Add Staff Modal
     */
    function openAddStaffModal() {
        const modal = document.getElementById('add-staff-modal');
        if (!modal) return;

        const alertEl = document.getElementById('add-staff-alert');
        if (alertEl) {
            alertEl.classList.add('hidden');
            alertEl.innerHTML = '';
        }

        // Auto-generate strong default password
        generateStaffPassword();

        modal.classList.remove('hidden');
        modal.style.display = 'flex';
        document.body.classList.add('overflow-hidden');

        // Focus first field
        setTimeout(() => {
            document.getElementById('new-staff-name')?.focus();
        }, 100);
    }

    /**
     * Close Add Staff Modal
     */
    function closeAddStaffModal() {
        const modal = document.getElementById('add-staff-modal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.style.display = 'none';
        document.body.classList.remove('overflow-hidden');
    }

    /**
     * Generate Strong Password for New Staff
     */
    async function generateStaffPassword() {
        const pwdInput = document.getElementById('new-staff-password');
        if (!pwdInput) return;

        try {
            const res = await window.api.post('/staff/password/generate', {});
            if (res && res.password) {
                pwdInput.value = res.password;
                return;
            }
        } catch (e) {
            // fallback client side generation
        }

        const chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789@#$%!';
        let pass = '';
        pass += 'abcdefghjkmnpqrstuvwxyz'[Math.floor(Math.random() * 23)];
        pass += 'ABCDEFGHJKMNPQRSTUVWXYZ'[Math.floor(Math.random() * 23)];
        pass += '23456789'[Math.floor(Math.random() * 8)];
        pass += '@#$%!'[Math.floor(Math.random() * 5)];
        for (let i = 4; i < 12; i++) {
            pass += chars[Math.floor(Math.random() * chars.length)];
        }
        pwdInput.value = pass.split('').sort(() => 0.5 - Math.random()).join('');
    }

    /**
     * Handle Add Staff Form Submit
     */
    async function handleAddStaffSubmit(e) {
        e.preventDefault();
        const form = document.getElementById('add-staff-form');
        const alertEl = document.getElementById('add-staff-alert');
        const submitBtn = document.getElementById('submit-add-staff-btn');
        const submitText = document.getElementById('submit-btn-text');

        if (!form) return;

        // Reset alert
        if (alertEl) {
            alertEl.classList.add('hidden');
            alertEl.className = 'hidden p-4 rounded-xl border text-xs font-bold leading-relaxed';
            alertEl.innerHTML = '';
        }

        const formData = new FormData(form);

        // Client validations
        const fullName = formData.get('full_name')?.trim();
        const email = formData.get('personal_email')?.trim();
        const phone = formData.get('phone')?.trim();
        const dob = formData.get('date_of_birth')?.trim();
        const password = formData.get('password')?.trim();

        if (!fullName || !email || !phone || !dob || !password) {
            if (alertEl) {
                alertEl.className = 'p-4 rounded-xl border bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20 text-xs font-bold leading-relaxed';
                alertEl.innerHTML = '<div class="flex items-center gap-2"><span class="material-symbols-outlined text-base">error</span><span>Please fill in all required fields marked with *</span></div>';
                alertEl.classList.remove('hidden');
            }
            return;
        }

        // Set Loading state
        if (submitBtn) submitBtn.disabled = true;
        if (submitText) submitText.textContent = 'Creating Staff...';

        try {
            const res = await fetch('/Finzo/api/v1/staff', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await res.json();
            const payload = result.data || result;

            if (res.ok && (result.success || result.status === 201 || result.status === 200 || payload.staff_id)) {
                const staffId = payload.staff_id || 'New Staff';
                const staffName = payload.staff_name || fullName;

                if (window.showToast) {
                    window.showToast(`Staff member ${staffName} (${staffId}) created successfully!`, 'success');
                } else {
                    alert(`Staff member ${staffName} (${staffId}) created successfully!`);
                }

                form.reset();
                closeAddStaffModal();
                currentPage = 1;
                loadStaff();
            } else {
                const msg = result.message || payload.error || result.error || 'Failed to create staff member. Please verify the form.';
                if (alertEl) {
                    alertEl.className = 'p-4 rounded-xl border bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20 text-xs font-bold leading-relaxed';
                    alertEl.innerHTML = `<div class="flex items-center gap-2"><span class="material-symbols-outlined text-base">error</span><span>${msg}</span></div>`;
                    alertEl.classList.remove('hidden');
                }
            }
        } catch (err) {
            console.error('Error creating staff:', err);
            if (alertEl) {
                alertEl.className = 'p-4 rounded-xl border bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20 text-xs font-bold leading-relaxed';
                alertEl.innerHTML = `<div class="flex items-center gap-2"><span class="material-symbols-outlined text-base">error</span><span>Server communication error: ${err.message || err}</span></div>`;
                alertEl.classList.remove('hidden');
            }
        } finally {
            if (submitBtn) submitBtn.disabled = false;
            if (submitText) submitText.textContent = 'Create Staff Member';
        }
    }

    /**
     * Bind page events
     */
    function bindEvents() {
        // Search input with debounce
        const searchInput = document.getElementById('staff-search-input');
        if (searchInput) {
            searchInput.addEventListener('input', window.FinzoUtils.debounce(() => {
                currentPage = 1;
                loadStaff();
            }, 350));
        }

        // Filter change listeners
        ['filter-status', 'filter-role', 'filter-branch'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('change', () => {
                    currentPage = 1;
                    loadStaff();
                });
            }
        });

        // Add Staff Form Submission
        const addStaffForm = document.getElementById('add-staff-form');
        if (addStaffForm) {
            addStaffForm.addEventListener('submit', handleAddStaffSubmit);
        }

        // Generate Password Button
        const genPwdBtn = document.getElementById('generate-staff-pwd-btn');
        if (genPwdBtn) {
            genPwdBtn.addEventListener('click', (e) => {
                e.preventDefault();
                generateStaffPassword();
            });
        }

        // Global click delegator
        document.addEventListener('click', (e) => {
            const pageBtn = e.target.closest('.page-num');
            if (pageBtn) {
                currentPage = parseInt(pageBtn.getAttribute('data-page'), 10);
                loadStaff();
                window.scrollTo({ top: 0, behavior: 'smooth' });
                return;
            }

            const prevBtn = e.target.closest('#prev-page');
            if (prevBtn && currentPage > 1) {
                currentPage--;
                loadStaff();
                window.scrollTo({ top: 0, behavior: 'smooth' });
                return;
            }

            const nextBtn = e.target.closest('#next-page');
            if (nextBtn) {
                currentPage++;
                loadStaff();
                window.scrollTo({ top: 0, behavior: 'smooth' });
                return;
            }

            // Export Modal Open/Close
            const exportOpen = e.target.closest('#open-export-modal');
            if (exportOpen) {
                e.preventDefault();
                openExportModal();
                return;
            }

            const exportClose = e.target.closest('.close-export-modal');
            if (exportClose) {
                e.preventDefault();
                closeExportModal();
                return;
            }

            const exportAction = e.target.closest('#start-export');
            if (exportAction) {
                e.preventDefault();
                startStaffExport();
                return;
            }

            // Add Staff Modal Open/Close
            const addStaffOpen = e.target.closest('#open-add-staff-modal');
            if (addStaffOpen) {
                e.preventDefault();
                openAddStaffModal();
                return;
            }

            const addStaffClose = e.target.closest('.close-add-staff-modal');
            if (addStaffClose) {
                e.preventDefault();
                closeAddStaffModal();
                return;
            }
        });

        // Backdrop click to close modals
        const exportModal = document.getElementById('export-modal');
        if (exportModal) {
            exportModal.addEventListener('click', (e) => {
                if (e.target === exportModal) closeExportModal();
            });
        }

        const addStaffModal = document.getElementById('add-staff-modal');
        if (addStaffModal) {
            addStaffModal.addEventListener('click', (e) => {
                if (e.target === addStaffModal) closeAddStaffModal();
            });
        }

        // Escape key to close active modal
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                if (exportModal && !exportModal.classList.contains('hidden')) closeExportModal();
                if (addStaffModal && !addStaffModal.classList.contains('hidden')) closeAddStaffModal();
            }
        });
    }

    // Initialize on DOM ready
    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const searchQuery = urlParams.get('search');
        if (searchQuery) {
            const searchInput = document.getElementById('staff-search-input');
            if (searchInput) searchInput.value = searchQuery;
        }

        loadBranches();
        loadStaff();
        bindEvents();
    });
})();
