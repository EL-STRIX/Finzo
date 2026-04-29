/**
 * Finzo Admin — Staff Management Page Handler
 * Version 2.0 — April 2026
 */

(function() {
    let currentPage = 1;
    const PER_PAGE = 20;

    /**
     * Load staff members with current filters
     */
    async function loadStaff() {
        // Role check for Add Button
        const addBtn = document.getElementById('add-staff-btn');
        if (addBtn) {
            // Check role from AdminPortal (which initializes globally)
            const checkRole = setInterval(() => {
                if (window.AdminPortal && window.AdminPortal.state.isLoaded) {
                    clearInterval(checkRole);
                    if (window.AdminPortal.state.admin.role !== 'super_admin') {
                        addBtn.remove();
                    }
                }
            }, 100);
        }

        const tbody = document.getElementById('staff-table-body');
        if (tbody) tbody.style.opacity = '0.5';

        try {
            const response = await window.api.get('/admin/staff.php', {
                search: document.getElementById('staff-search-input').value,
                status: document.getElementById('filter-status').value,
                branch_id: document.getElementById('filter-branch').value,
                page: currentPage
            });

            if (response.success) {
                renderTable(response.staff || []);
                updatePagination(response.total || 0, response.pages || 1);
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
            const countStr = String((currentPage - 1) * PER_PAGE + index + 1).padStart(2, '0');
            
            // initials logic
            const initials = s.profile_photo_url ? `<img src="${s.profile_photo_url}" class="admin-avatar sm object-cover">` : `<div class="admin-avatar sm premium-gradient-primary uppercase" style="color:#fff;">${s.initials}</div>`;

            return `
            <tr class="hover:bg-slate-50/50 dark:hover:bg-white/5 transition-colors border-b border-slate-100 dark:border-white/5 last:border-0">
                <td class="font-bold opacity-30 text-[10px] tracking-widest">${countStr}</td>
                <td>
                    <div class="flex items-center gap-3">
                        ${initials}
                        <div>
                            <div class="font-bold text-sm text-slate-800 dark:text-slate-100">${s.full_name}</div>
                            <div class="text-[9px] opacity-60 uppercase font-black tracking-tighter">${s.designation} • ${s.department}</div>
                        </div>
                    </div>
                </td>
                <td class="text-center text-[10px] font-bold font-mono opacity-50">${s.staff_id}</td>
                <td><div class="text-[11px] font-bold">${s.branch_name || 'Unassigned'}</div></td>
                <td class="text-center font-bold text-xs opacity-60">0</td>
                <td class="text-right font-black text-xs text-primary dark:text-blue-400">₹0</td>
                <td class="text-[10px] font-bold opacity-40">${s.last_login_at ? window.FinzoUtils.formatDate(s.last_login_at) : 'Never'}</td>
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
            const start = total === 0 ? 0 : (currentPage - 1) * PER_PAGE + 1;
            const end = Math.min(currentPage * PER_PAGE, total);
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
     * Load branches for dropdowns
     */
    async function loadBranches() {
        try {
            const response = await window.api.get('/admin/branches.php');
            if (response.success) {
                // Populate Modal Dropdown
                const modalMenu = document.getElementById('staff-branch-menu');
                if (modalMenu) {
                    modalMenu.innerHTML = '';
                    response.branches.forEach(b => {
                        const opt = document.createElement('div');
                        opt.className = 'finzo-dropdown-option';
                        opt.setAttribute('data-value', b.id);
                        opt.textContent = b.name;
                        modalMenu.appendChild(opt);
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
     * Global Functions for Modal and Form
     */
    window.openAddStaffModal = function() {
        // Double check permission on open
        if (window.AdminPortal && window.AdminPortal.state.admin.role !== 'super_admin') {
            if (window.showToast) window.showToast('Access Denied: Only Super Admins can add staff.', 'error');
            return;
        }

        const modal = document.getElementById('addStaffModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
            generateStaffPassword(); // Auto-generate on open
        }
    };

    window.closeAddStaffModal = function() {
        const modal = document.getElementById('addStaffModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
            document.getElementById('addStaffForm').reset();
            resetPreviews();
            clearErrors();
        }
    };

    window.generateStaffPassword = async function() {
        const passInput = document.getElementById('staff-password');
        const icon = document.getElementById('gen-pass-icon');
        
        if (icon) icon.classList.add('animate-spin');
        
        try {
            const response = await window.api.post('/admin/generate-staff-password.php', {});
            if (response.success && passInput) {
                passInput.value = response.password;
            }
        } catch (error) {
            console.error('Failed to generate password');
        } finally {
            if (icon) setTimeout(() => icon.classList.remove('animate-spin'), 500);
        }
    };

    window.copyPasswordToClipboard = function() {
        const pass = document.getElementById('staff-password').value;
        if (!pass) return;
        
        navigator.clipboard.writeText(pass).then(() => {
            const icon = document.getElementById('pass-copy-icon');
            if (icon) {
                icon.textContent = 'check';
                setTimeout(() => icon.textContent = 'content_copy', 2000);
            }
            if (window.showToast) window.showToast('Password copied to clipboard', 'success');
        });
    };

    window.previewProfilePhoto = function(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('profile-preview-img');
                const container = document.getElementById('photo-preview-container');
                img.src = e.target.result;
                img.classList.remove('hidden');
                container.querySelectorAll('span').forEach(s => s.classList.add('hidden'));
            };
            reader.readAsDataURL(input.files[0]);
        }
    };

    window.previewDoc = function(input, type) {
        if (input.files && input.files[0]) {
            const fileName = input.files[0].name;
            document.getElementById(`${type}-file-name`).textContent = fileName;
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById(`${type}-preview`);
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                document.getElementById(`${type}-drop-zone`).querySelector('span').classList.add('hidden');
            };
            reader.readAsDataURL(input.files[0]);
        }
    };

    function resetPreviews() {
        document.getElementById('profile-preview-img').classList.add('hidden');
        document.getElementById('photo-preview-container').querySelectorAll('span').forEach(s => s.classList.remove('hidden'));
        document.getElementById('front-preview').classList.add('hidden');
        document.getElementById('back-preview').classList.add('hidden');
        document.getElementById('front-file-name').textContent = 'Click or drag to upload front image';
        document.getElementById('back-file-name').textContent = 'Click or drag to upload back image';
        document.getElementById('front-drop-zone').querySelector('span').classList.remove('hidden');
        document.getElementById('back-drop-zone').querySelector('span').classList.remove('hidden');
    }

    function clearErrors() {
        document.querySelectorAll('[id^="error-"]').forEach(el => {
            el.textContent = '';
            el.classList.add('hidden');
        });
    }

    function displayErrors(errors) {
        clearErrors();
        Object.keys(errors).forEach(key => {
            const errorEl = document.getElementById(`error-${key}`);
            if (errorEl) {
                errorEl.textContent = errors[key];
                errorEl.classList.remove('hidden');
            }
        });
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

        // Add Staff Form Submit
        const addForm = document.getElementById('addStaffForm');
        if (addForm) {
            addForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const submitBtn = document.getElementById('create-staff-submit');
                
                window.api.setButtonLoading(submitBtn, true, 'Processing...');
                clearErrors();
                
                try {
                    const formData = new FormData(addForm);
                    // CSRF token from session (handled by api.js if set up, or add manually)
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
                    formData.append('csrf_token', csrfToken);

                    const response = await window.api.upload('/admin/add-staff.php', formData);

                    if (response.success) {
                        if (window.showToast) window.showToast('Staff member added successfully!', 'success');
                        closeAddStaffModal();
                        loadStaff();
                    } else {
                        if (response.errors) {
                            displayErrors(response.errors);
                            if (window.showToast) window.showToast('Please correct the highlighted errors.', 'error');
                        } else {
                            if (window.showToast) window.showToast(response.message || 'Failed to add staff', 'error');
                        }
                    }
                } catch (error) {
                    console.error('Staff creation error:', error);
                    if (window.showToast) window.showToast('A network error occurred.', 'error');
                } finally {
                    window.api.setButtonLoading(submitBtn, false);
                }
            });
        }
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', () => {
        loadBranches();
        loadStaff();
        bindEvents();
    });
})();
