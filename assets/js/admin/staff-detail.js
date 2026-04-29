/**
 * Finzo Admin — Staff Detail
 */
document.addEventListener('DOMContentLoaded', () => {

    const urlParams = new URLSearchParams(window.location.search);
    const staffId = urlParams.get('id');
    
    if (!staffId) {
        alert('Staff ID is required');
        // window.location.href = 'admin-staff.html';
        return;
    }

    let currentAction = '';

    const modalConfigs = {
        reset: {
            title: 'Reset Password',
            subtitle: 'Security protocol override',
            body: 'You are about to issue a mandatory password reset for <b id="reset-target-name">...</b>. A temporary system-generated password will be created, and the staff will be required to change it at next login.',
            icon: 'lock_reset',
            color: 'indigo',
            btnText: 'Confirm Reset',
            btnClass: 'bg-indigo-600 shadow-indigo-600/20',
            reasons: []
        },
        deactivate: {
            title: 'Deactivate Staff',
            subtitle: 'Irreversible suspension of access',
            body: 'You are about to deactivate <b id="deactivate-target-name">...</b>. This will revoke all system access immediately and terminate active sessions.',
            icon: 'block',
            color: 'rose',
            btnText: 'Confirm Deactivation',
            btnClass: 'bg-rose-600 shadow-rose-600/20',
            reasons: [
                'Unauthorized Data Access',
                'Financial Discrepancy Found',
                'Policy Violation / Misconduct',
                'Performance Related Termination',
                'Voluntary Resignation',
                'Security Breach Suspected',
                'Other'
            ]
        }
    };

    function getInitials(name) {
        if (!name) return 'S';
        return name.split(' ').map(n => n[0]).join('').toUpperCase();
    }

    function formatCurrency(amount) {
        return window.FinzoUtils.formatINR(amount);
    }

    async function loadStaffData() {
        try {
            const response = await window.api.get(`/admin/staff-detail.php?id=${staffId}`);
            if (response.status === 'success' || response.success) {
                const staff = response.staff;
                const performance = response.performance || {};
                const today = response.today_performance || {};
                const transactions = response.transactions || [];
                const totalShifts = response.total_shifts || 0;
                
                // Header
                const breadcrumb = document.getElementById('staff-name-breadcrumb');
                if(breadcrumb) {
                    breadcrumb.classList.remove('opacity-60');
                    breadcrumb.textContent = staff.full_name;
                }
                
                const h1 = document.getElementById('staff-name-h1');
                if(h1) h1.textContent = staff.full_name;
                
                const resetTarget = document.getElementById('reset-target-name');
                if(resetTarget) resetTarget.textContent = staff.full_name;
                
                const deactivateTarget = document.getElementById('deactivate-target-name');
                if(deactivateTarget) deactivateTarget.textContent = staff.full_name;

                const avatar = document.getElementById('staff-initials-avatar');
                if(avatar) {
                    const initials = getInitials(staff.full_name);
                    avatar.innerHTML = initials;
                    avatar.style.background = window.FinzoUtils.getAvatarGradient(initials);
                }

                // Title display (Designation & Department)
                const desEl = document.getElementById('info-designation');
                if(desEl) desEl.textContent = staff.designation;
                const depEl = document.getElementById('info-department');
                if(depEl) depEl.textContent = staff.department;
                
                const idDisplay = document.getElementById('staff-id-display');
                if(idDisplay) idDisplay.textContent = `ID: ${staff.staff_id}`;
                
                const branchDisplay = document.getElementById('staff-branch-display');
                if(branchDisplay) branchDisplay.textContent = staff.branch_name || 'N/A';
                
                const state = staff.status.toLowerCase() === 'active' ? 'badge-success' : 'badge-error';
                const statusBadge = document.getElementById('staff-status-badge');
                if(statusBadge) {
                   statusBadge.className = `status-badge ${state}`;
                   statusBadge.textContent = staff.status.toUpperCase();
                }

                // Button visibility based on status
                const deactivateBtn = document.getElementById('deactivate-staff-btn');
                if (staff.status.toLowerCase() !== 'active') {
                   if(deactivateBtn) {
                       deactivateBtn.disabled = true;
                       deactivateBtn.classList.add('opacity-50', 'cursor-not-allowed');
                       deactivateBtn.innerHTML = '<span class="material-symbols-outlined">block</span> Suspended';
                   }
                }

                // Stats (All Time)
                const statDeposits = document.getElementById('stat-total-deposits');
                if(statDeposits) statDeposits.innerHTML = `${formatCurrency(performance.total_deposits)} <span class="admin-stat-delta positive">+0%</span>`;
                
                const statWithdrawals = document.getElementById('stat-total-withdrawals');
                if(statWithdrawals) statWithdrawals.innerHTML = `${formatCurrency(performance.total_withdrawals)} <span class="admin-stat-delta positive">+0%</span>`;
                
                const statTxns = document.getElementById('stat-total-txns');
                if(statTxns) statTxns.innerHTML = `${performance.total_transactions} <span class="admin-stat-delta neutral">All Time</span>`;
                
                const lastActive = staff.last_login_at ? new Date(staff.last_login_at) : null;
                const statLastActive = document.getElementById('stat-last-active');
                if(statLastActive) {
                   statLastActive.innerHTML = lastActive ? `${window.FinzoUtils.formatDate(lastActive)} <span class="admin-stat-delta positive" style="background:#e8f8f2; color:#00b894;">${lastActive.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>` : 'Never';
                }

                // Info
                const mapping = {
                    'info-full-name': staff.full_name,
                    'info-email': staff.email,
                    'info-phone': staff.phone || 'N/A',
                    'info-branch': staff.branch_name || 'N/A',
                    'info-staff-id': staff.staff_id,
                    'info-joined': window.FinzoUtils.formatDate(staff.created_at),
                    'info-shifts': totalShifts
                };
                
                for(const [id, val] of Object.entries(mapping)) {
                    const el = document.getElementById(id);
                    if(el) el.textContent = val;
                }

                // Today's Shift Summary
                const sStart = document.getElementById('summary-shift-start'); if(sStart) sStart.textContent = '09:00 AM';
                const sDep = document.getElementById('summary-deposits'); if(sDep) sDep.textContent = `${today.deposit_count || 0} (${formatCurrency(today.total_deposits)})`;
                const sWit = document.getElementById('summary-withdrawals'); if(sWit) sWit.textContent = `${today.withdrawal_count || 0} (${formatCurrency(today.total_withdrawals)})`;
                const sTx = document.getElementById('summary-total-txns'); if(sTx) sTx.textContent = today.total_transactions || 0;
                const sAmt = document.getElementById('summary-total-amount'); if(sAmt) sAmt.textContent = formatCurrency((parseFloat(today.total_deposits) || 0) - (parseFloat(today.total_withdrawals) || 0));

                // Transactions
                const tbody = document.getElementById('staff-transactions-body');
                if (tbody) {
                    if (transactions && transactions.length > 0) {
                        const txHtml = transactions.map(t => `
                        <tr class="hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">
                            <td class="text-[11px] font-bold opacity-60 font-mono">${t.txn_id}</td>
                            <td class="text-xs font-medium">${window.FinzoUtils.formatDateTime(t.created_at)}</td>
                            <td class="font-bold text-sm text-slate-800 dark:text-slate-100">${t.customer_name}</td>
                            <td class="font-mono text-xs opacity-60">${t.account_no}</td>
                            <td><span class="status-badge ${t.type === 'deposit' ? 'badge-success' : 'badge-error'}">${t.type.toUpperCase()}</span></td>
                            <td class="font-black text-xs ${t.type === 'deposit' ? 'text-primary dark:text-blue-400' : 'text-error'}">${formatCurrency(t.amount)}</td>
                            <td class="text-right">
                            <button class="p-2 hover:bg-slate-100 dark:hover:bg-white/5 rounded-lg transition-all text-slate-400 group">
                                <span class="material-symbols-outlined text-lg group-hover:text-primary transition-colors">receipt_long</span>
                            </button>
                            </td>
                        </tr>
                        `).join('');
                        tbody.innerHTML = txHtml;
                    } else {
                        tbody.innerHTML = '<tr><td colspan="7" class="py-12 text-center opacity-40 font-bold">No transactions recorded yet</td></tr>';
                    }
                }
            } else {
                const tbody = document.getElementById('staff-transactions-body');
                if(tbody) tbody.innerHTML = '<tr><td colspan="7" class="py-12 text-center text-error font-bold">Failed to load staff details: ' + (response.message || 'Unknown error') + '</td></tr>';
                if(window.showToast) window.showToast(response.message || 'Error loading staff details', 'error');
            }
        } catch (error) {
            console.error('Failed to load staff data:', error);
            const tbody = document.getElementById('staff-transactions-body');
            if(tbody) tbody.innerHTML = '<tr><td colspan="7" class="py-12 text-center text-error font-bold">Connection error. Please check your network and try again.</td></tr>';
        }
    }

    function openGovernanceModal(action) {
        currentAction = action;
        const config = modalConfigs[action];

        document.getElementById('auth-step').style.display = 'block';
        
        const bodyText = document.getElementById('modal-body-text');
        bodyText.style.display = 'block';
        bodyText.innerHTML = config.body;
        
        const subTitle = document.getElementById('modal-subtitle');
        subTitle.style.display = 'block';
        subTitle.textContent = config.subtitle;
        
        document.getElementById('modal-title').textContent = config.title;
        document.getElementById('modal-icon').textContent = config.icon;
        
        const iconContainer = document.getElementById('modal-icon-container');
        iconContainer.className = `w-16 h-16 rounded-full mx-auto flex items-center justify-center mb-4 bg-${config.color}-50 dark:bg-${config.color}-500/10 text-${config.color}-500 shadow-lg`;
        
        const confirmBtn = document.getElementById('confirm-governance-btn');
        confirmBtn.className = `flex-1 py-4 text-white font-black rounded-2xl text-[11px] uppercase tracking-widest shadow-lg hover:scale-[1.02] active:scale-[0.98] transition-all ${config.btnClass}`;
        confirmBtn.textContent = config.btnText;
        confirmBtn.style.display = 'block';
        confirmBtn.disabled = false;

        const reasonStep = document.getElementById('reason-step');
        if (config.reasons && config.reasons.length > 0) {
            let reasonsHtml = '';
            config.reasons.forEach(r => {
                reasonsHtml += `
                <label class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-white/5 rounded-xl border-2 border-transparent hover:border-slate-200 dark:hover:border-white/10 cursor-pointer transition-all group">
                    <input type="radio" name="suspension_reason" value="${r}" class="text-primary focus:ring-primary/10">
                    <span class="text-[10px] font-black text-slate-500 group-hover:text-black dark:group-hover:text-white leading-tight">${r}</span>
                </label>
                `;
            });
            document.getElementById('reason-grid').innerHTML = reasonsHtml;
            reasonStep.style.display = 'block';
            
            document.querySelectorAll('input[name="suspension_reason"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    const customContainer = document.getElementById('custom-reason-container');
                    if (this.value === 'Other') {
                        customContainer.style.display = 'block';
                    } else {
                        customContainer.style.display = 'none';
                    }
                });
            });
        } else {
            reasonStep.style.display = 'none';
        }

        const backdrop = document.getElementById('modal-backdrop');
        backdrop.style.opacity = '1';
        
        const container = document.getElementById('modal-container');
        container.classList.remove('scale-95');
        container.style.opacity = '1';
        
        const modal = document.getElementById('governance-modal');
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
        
        document.body.classList.add('overflow-hidden');
    }

    window.closeGovernanceModal = function() {
        const container = document.getElementById('modal-container');
        container.classList.add('scale-95');
        container.style.opacity = '0';
        
        const backdrop = document.getElementById('modal-backdrop');
        backdrop.style.opacity = '0';
        
        setTimeout(() => {
            const modal = document.getElementById('governance-modal');
            modal.style.display = '';
            modal.classList.add('hidden');
            
            document.body.classList.remove('overflow-hidden');
            
            document.getElementById('admin-password').value = '';
            document.getElementById('auth-error').classList.add('hidden');
            document.getElementById('admin-password').classList.remove('ring-2', 'ring-rose-500/20');
            
            document.getElementById('reason-step').style.display = 'none';
            document.getElementById('custom-reason-container').style.display = 'none';
        }, 300);
    };

    const dBtn = document.getElementById('deactivate-staff-btn');
    if(dBtn) {
        dBtn.addEventListener('click', function() {
            if (this.disabled) return;
            openGovernanceModal('deactivate');
        });
    }

    const rBtn = document.getElementById('reset-password-btn');
    if(rBtn) {
        rBtn.addEventListener('click', function() {
            openGovernanceModal('reset');
        });
    }

    const cBtn = document.getElementById('confirm-governance-btn');
    if(cBtn) {
        cBtn.addEventListener('click', async function() {
            if (this.textContent.includes('DONE') || this.textContent.includes('LOGGED')) { 
                closeGovernanceModal(); 
                return; 
            }

            const password = document.getElementById('admin-password').value;
            const checkedReason = document.querySelector('input[name="suspension_reason"]:checked');
            const reason = checkedReason ? checkedReason.value : null;
            const apiAction = currentAction === 'deactivate' ? 'suspend' : 'reset_password';

            if (currentAction === 'deactivate' && !reason) {
                alert('Please select a reason for deactivation.');
                return;
            }

            this.disabled = true;
            this.innerHTML = '<span class="material-symbols-outlined animate-spin text-sm">refresh</span> AUTHORIZING...';

            try {
                const response = await window.api.post('/admin/staff-action.php', {
                    action: apiAction,
                    staff_id: staffId,
                    password: password,
                    reason: reason,
                    notes: document.getElementById('custom-reason-notes').value
                });

                if (response.status === 'success' || response.success) {
                    if (currentAction === 'reset') {
                        document.getElementById('auth-step').style.display = 'none';
                        document.getElementById('modal-subtitle').style.display = 'none';
                        document.getElementById('modal-title').textContent = 'Temporary Password Generated';
                        document.getElementById('modal-body-text').innerHTML = `
                            <div class="p-6 bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 rounded-2xl text-center">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">One-Time Password</p>
                            <div class="text-2xl font-black text-primary dark:text-amber-500 font-mono tracking-wider mb-2">${response.temp_password || response.data?.temp_password}</div>
                            <p class="text-[10px] font-bold text-rose-500 uppercase">Valid for 24 hours only</p>
                            </div>
                        `;
                        this.disabled = false;
                        this.innerHTML = '<span class="material-symbols-outlined">check_circle</span> DONE';
                        this.classList.replace(modalConfigs['reset'].btnClass.split(' ')[0], 'bg-emerald-500');
                        this.classList.add('shadow-emerald-500/20');
                    } else {
                        this.innerHTML = '<span class="material-symbols-outlined">check_circle</span> ACTION LOGGED';
                        this.classList.replace(modalConfigs['deactivate'].btnClass.split(' ')[0], 'bg-emerald-500');
                        this.classList.add('shadow-emerald-500/20');
                        setTimeout(() => {
                            closeGovernanceModal();
                            loadStaffData(); // Refresh page
                        }, 1500);
                    }
                } else {
                    const errEl = document.getElementById('auth-error');
                    errEl.textContent = response.message;
                    errEl.classList.remove('hidden');
                    errEl.style.display = 'block';
                    
                    this.disabled = false;
                    this.textContent = currentAction === 'reset' ? 'Confirm Reset' : 'Confirm Deactivation';
                }
            } catch (error) {
                alert('Connection failed. Please try again.');
                this.disabled = false;
                this.textContent = 'Try Again';
            }
        });
    }

    loadStaffData();
});
