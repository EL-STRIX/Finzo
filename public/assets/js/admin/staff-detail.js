/**
 * Finzo Admin — Staff Detail (Dossier & Governance Operations)
 * Comprehensive Staff Portfolio, Classic Monochrome Passbook & Statement Printing, and Governance Actions
 */

document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const staffId = urlParams.get('id');
    
    if (!staffId) {
        if (window.showToast) window.showToast('Staff ID parameter is required', 'error');
        return;
    }

    let currentStaff = {};
    let currentStaffTransactions = [];
    let currentStaffAddress = {};
    let currentAction = '';

    // ==========================================
    //  PASSBOOK & STATEMENT STATE
    // ==========================================
    let passbookFilter = { from: null, to: null, label: 'ALL TIME' };

    function getFilteredPassbookTransactions() {
        if (!passbookFilter.from && !passbookFilter.to) {
            return [...currentStaffTransactions];
        }

        return currentStaffTransactions.filter(txn => {
            const rawDate = txn.created_at;
            if (!rawDate) return false;
            const txnTime = new Date(rawDate).getTime();

            if (passbookFilter.from) {
                const fromDate = new Date(passbookFilter.from + 'T00:00:00').getTime();
                if (txnTime < fromDate) return false;
            }
            if (passbookFilter.to) {
                const toDate = new Date(passbookFilter.to + 'T23:59:59.999').getTime();
                if (txnTime > toDate) return false;
            }
            return true;
        });
    }

    /**
     * Render Monochrome Bank Passbook Content (Ledger & Summary)
     */
    function renderPassbookContent() {
        const staff = currentStaff || {};
        const addr = currentStaffAddress || {};
        const transactions = getFilteredPassbookTransactions();

        // Populate Passbook Header Fields
        setText('pb-full-name', (staff.full_name || 'Staff Member').toUpperCase());
        setText('pb-staff-id', staff.staff_id || 'STF---');
        setText('pb-designation', (staff.designation || 'Staff') + (staff.department ? ' (' + staff.department + ')' : ''));
        setText('pb-branch', staff.branch_name || 'Main Headquarters');
        setText('pb-phone', staff.phone || 'N/A');
        
        const branchHeader = document.getElementById('pb-branch-header');
        if (branchHeader) {
            branchHeader.textContent = 'BRANCH: ' + (staff.branch_name ? staff.branch_name.toUpperCase() : 'MAIN HEADQUARTERS');
        }

        // Formatted Address Line
        const addrParts = [addr.street, addr.city, addr.state, addr.pincode, addr.country || 'India'].filter(Boolean);
        setText('pb-address', addrParts.length ? addrParts.join(', ') : 'Not provided');

        // Statement Period String
        let periodStr = 'ALL TIME (AS OF ' + new Date().toLocaleDateString('en-GB') + ')';
        if (passbookFilter.from && passbookFilter.to) {
            const f = new Date(passbookFilter.from + 'T00:00:00').toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });
            const t = new Date(passbookFilter.to + 'T00:00:00').toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });
            periodStr = `${f} TO ${t}`;
        } else if (passbookFilter.from) {
            const f = new Date(passbookFilter.from + 'T00:00:00').toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });
            periodStr = `SINCE ${f}`;
        } else if (passbookFilter.to) {
            const t = new Date(passbookFilter.to + 'T00:00:00').toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });
            periodStr = `UP TO ${t}`;
        }
        setText('pb-generated-date', periodStr);

        // Populate Passbook Ledger Entries
        const tbody = document.getElementById('pb-transactions-body');
        if (!tbody) return;

        let totalCredits = 0;
        let totalDebits = 0;
        let totalVolume = 0;

        if (transactions.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="p-4 text-center text-black font-bold">No transaction entries found for the selected period.</td></tr>';
        } else {
            let html = '';
            transactions.forEach((txn, index) => {
                const lineNum = String(index + 1).padStart(2, '0');
                const d = txn.created_at ? new Date(txn.created_at) : new Date();
                const dateStr = d.toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: '2-digit' }) + ' ' + d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                
                const amt = parseFloat(txn.amount) || 0;
                const isDeposit = (txn.type || '').toLowerCase() === 'deposit';

                if (isDeposit) {
                    totalCredits += amt;
                } else {
                    totalDebits += amt;
                }
                totalVolume += amt;

                const custName = txn.customer_name || 'Walk-in Customer';
                const accNo = txn.account_no || 'N/A';
                const typeLabel = isDeposit ? 'CR / DEPOSIT' : 'DR / WITHDRAWAL';

                html += `
                    <tr class="border-b border-black">
                        <td class="p-2 border-r border-black text-center font-bold">${lineNum}</td>
                        <td class="p-2 border-r border-black text-center whitespace-nowrap">${dateStr}</td>
                        <td class="p-2 border-r border-black font-bold font-mono text-[10px]">${txn.txn_id || '—'}</td>
                        <td class="p-2 border-r border-black uppercase font-bold truncate max-w-[200px]" title="${custName}">${custName}</td>
                        <td class="p-2 border-r border-black font-mono text-[10px]">${accNo}</td>
                        <td class="p-2 border-r border-black text-center font-black text-[10px]">${typeLabel}</td>
                        <td class="p-2 text-right font-black text-xs font-mono">₹ ${amt.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        }

        // Summary Totals
        setText('pb-total-credits', '₹ ' + totalCredits.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        setText('pb-total-debits', '₹ ' + totalDebits.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        setText('pb-total-txns-count', String(transactions.length));
        setText('pb-net-balance', '₹ ' + totalVolume.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
    }

    /**
     * Set Passbook Preset Date Filters
     */
    function setPassbookPreset(preset) {
        const fromInput = document.getElementById('pb-filter-from');
        const toInput = document.getElementById('pb-filter-to');
        const today = new Date();
        const formatDate = (d) => d.toISOString().slice(0, 10);

        if (preset === 'all') {
            passbookFilter = { from: null, to: null, label: 'ALL TIME' };
            if (fromInput) fromInput.value = '';
            if (toInput) toInput.value = '';
        } else if (preset === 'month') {
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            passbookFilter = { from: formatDate(firstDay), to: formatDate(today), label: 'THIS MONTH' };
            if (fromInput) fromInput.value = passbookFilter.from;
            if (toInput) toInput.value = passbookFilter.to;
        } else if (preset === '30') {
            const d = new Date();
            d.setDate(d.getDate() - 30);
            passbookFilter = { from: formatDate(d), to: formatDate(today), label: 'LAST 30 DAYS' };
            if (fromInput) fromInput.value = passbookFilter.from;
            if (toInput) toInput.value = passbookFilter.to;
        } else if (preset === '90') {
            const d = new Date();
            d.setDate(d.getDate() - 90);
            passbookFilter = { from: formatDate(d), to: formatDate(today), label: 'LAST 90 DAYS' };
            if (fromInput) fromInput.value = passbookFilter.from;
            if (toInput) toInput.value = passbookFilter.to;
        }

        renderPassbookContent();
    }

    /**
     * Open Passbook Modal
     */
    function openPassbookModal() {
        const modal = document.getElementById('passbook-modal');
        if (!modal) return;

        passbookFilter = { from: null, to: null, label: 'ALL TIME' };
        const fromInput = document.getElementById('pb-filter-from');
        const toInput = document.getElementById('pb-filter-to');
        if (fromInput) fromInput.value = '';
        if (toInput) toInput.value = '';

        renderPassbookContent();

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    }

    /**
     * Close Passbook Modal
     */
    function closePassbookModal() {
        const modal = document.getElementById('passbook-modal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    }

    // Modal Configs for Governance Actions
    const modalConfigs = {
        reset: {
            title: 'Reset Staff Password',
            subtitle: 'Security protocol override',
            body: 'You are about to issue an emergency password reset for <strong class="text-text-primary" id="reset-target-name">...</strong>. A secure temporary password will be generated.',
            icon: 'lock_reset',
            color: 'indigo',
            btnText: 'Confirm Reset',
            btnClass: 'bg-primary',
            reasons: []
        },
        deactivate: {
            title: 'Suspend Staff Account',
            subtitle: 'Revoke system permissions',
            body: 'You are about to suspend <strong class="text-text-primary" id="deactivate-target-name">...</strong>. This will revoke branch operations and system access immediately.',
            icon: 'block',
            color: 'rose',
            btnText: 'Confirm Suspension',
            btnClass: 'bg-warning text-white',
            reasons: [
                'Unauthorized Operations',
                'Cash / Financial Discrepancy',
                'Policy Violation / Misconduct',
                'Performance & Compliance Review',
                'Voluntary Leave / Resignation',
                'Security Breach Suspected',
                'Other'
            ]
        },
        reinstate: {
            title: 'Reinstate Staff Account',
            subtitle: 'Restore operational privileges',
            body: 'You are about to reinstate <strong class="text-text-primary" id="reinstate-target-name">...</strong>. This will restore branch login access and active status.',
            icon: 'check_circle',
            color: 'emerald',
            btnText: 'Confirm Reinstatement',
            btnClass: 'bg-success text-white',
            reasons: []
        },
        delete: {
            title: 'Delete Staff Account',
            subtitle: 'Permanent soft deletion',
            body: 'You are about to <span class="text-error font-black uppercase underline">PERMANENTLY SOFT-DELETE</span> <strong class="text-text-primary" id="delete-target-name">...</strong>. This action will archive the staff account from active lists.',
            icon: 'person_remove',
            color: 'rose',
            btnText: 'Confirm Permanent Deletion',
            btnClass: 'bg-error text-white',
            reasons: []
        }
    };

    function setText(id, val) {
        const el = document.getElementById(id);
        if (el) el.textContent = val !== null && val !== undefined && val !== '' ? val : '—';
    }

    function fmtCurrency(amount) {
        const num = parseFloat(amount) || 0;
        return '₹' + num.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function fmtDate(dateStr) {
        if (!dateStr) return '—';
        try {
            const d = new Date(dateStr);
            if (isNaN(d.getTime())) return '—';
            return d.toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
        } catch(e) { return '—'; }
    }

    function timeAgo(dateStr) {
        if (!dateStr) return 'Never';
        try {
            const d = new Date(dateStr);
            const now = new Date();
            const diff = now - d;
            const mins = Math.floor(diff / 60000);
            const hours = Math.floor(diff / 3600000);
            const days = Math.floor(diff / 86400000);
            if (mins < 1) return 'Just now';
            if (mins < 60) return `${mins}m ago`;
            if (hours < 24) return `${hours}h ago`;
            if (days < 30) return `${days}d ago`;
            return fmtDate(dateStr);
        } catch(e) { return 'Never'; }
    }

    // Lightbox Modal
    function openLightbox(src) {
        const modal = document.getElementById('lightbox-modal');
        const img = document.getElementById('lightbox-img');
        if (modal && img && src) {
            img.src = src;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        }
    }

    function closeLightbox() {
        const modal = document.getElementById('lightbox-modal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        }
    }

    // ==========================================
    //  LOAD STAFF COMPREHENSIVE DOSSIER
    // ==========================================
    async function loadStaffData() {
        try {
            const response = await window.api.get('/staff/' + staffId);

            if (!response || !response.success) {
                if (window.showToast) window.showToast(response?.message || 'Error loading staff dossier', 'error');
                return;
            }

            const staff = response.staff || {};
            const performance = response.performance || {};
            const today = response.today_performance || {};
            const transactions = response.transactions || [];
            const shifts = response.shifts || [];
            const activities = response.activities || [];
            const documents = response.documents || [];
            const address = response.address || {};
            const totalShifts = response.total_shifts || 0;

            currentStaff = staff;
            currentStaffTransactions = transactions;
            currentStaffAddress = address;

            // 1. Header
            setText('header-staff-name', staff.full_name || 'Staff Member');
            const statusBadge = document.getElementById('header-status-badge');
            if (statusBadge) {
                const isActive = (staff.status === 'active');
                statusBadge.className = isActive 
                    ? 'flex items-center gap-2 px-3.5 py-1.5 rounded-full border border-emerald-500/20 bg-emerald-500/10 shadow-sm'
                    : 'flex items-center gap-2 px-3.5 py-1.5 rounded-full border border-rose-500/20 bg-rose-500/10 shadow-sm';
                
                const dot = statusBadge.querySelector('.status-dot');
                if (dot) {
                    dot.className = isActive ? 'status-dot w-2 h-2 rounded-full bg-emerald-500 animate-pulse' : 'status-dot w-2 h-2 rounded-full bg-rose-500';
                }
                const label = statusBadge.querySelector('.status-label');
                if (label) {
                    label.className = isActive ? 'status-label text-[10px] font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-400' : 'status-label text-[10px] font-black uppercase tracking-widest text-rose-600 dark:text-rose-400';
                    label.textContent = isActive ? 'ACTIVE ACCOUNT' : 'SUSPENDED';
                }
            }

            // 2. Alert Banner
            const alertBanner = document.getElementById('security-alert-banner');
            const alertIcon = document.getElementById('security-alert-icon');
            const alertTitle = document.getElementById('security-alert-title');
            const alertMsg = document.getElementById('security-alert-message');

            const isLocked = staff.locked_until && (new Date(staff.locked_until) > new Date());

            if (alertBanner && alertIcon && alertTitle && alertMsg) {
                if (staff.status === 'suspended') {
                    alertBanner.className = 'rounded-2xl p-4 sm:p-5 flex items-start gap-4 border border-rose-500/20 bg-rose-500/10 text-rose-700 dark:text-rose-300';
                    alertIcon.textContent = 'gavel';
                    alertTitle.textContent = 'Account Suspended';
                    alertMsg.innerHTML = `Staff privileges have been revoked. Suspension details: <strong>${staff.suspension_reason || 'Administrative Review'}</strong> (${staff.suspended_by_name || 'Administrator'}).`;
                } else if (isLocked) {
                    alertBanner.className = 'rounded-2xl p-4 sm:p-5 flex items-start gap-4 border border-amber-500/20 bg-amber-500/10 text-amber-700 dark:text-amber-300';
                    alertIcon.textContent = 'lock';
                    alertTitle.textContent = 'Account Temporarily Locked';
                    alertMsg.innerHTML = `This account is locked due to ${staff.failed_attempts || 0} failed login attempts. Unlocks automatically at ${staff.locked_until}.`;
                } else {
                    alertBanner.classList.add('hidden');
                }
            }

            // 3. Profile Card
            const photoContainer = document.getElementById('staff-photo-container');
            const initials = staff.full_name ? staff.full_name.split(' ').map(n => n[0]).join('').slice(0, 2).toUpperCase() : 'ST';
            if (photoContainer) {
                if (staff.profile_photo_url) {
                    photoContainer.innerHTML = `<img src="${staff.profile_photo_url}" class="w-full h-full object-cover" alt="${staff.full_name}">`;
                } else {
                    photoContainer.innerHTML = `<span class="text-4xl font-black text-primary">${initials}</span>`;
                }
            }

            setText('info-name', staff.full_name);
            setText('info-staff-id-badge', staff.staff_id || 'STF---');
            setText('info-designation-badge', staff.designation || 'Staff');
            setText('info-department-badge', staff.department || 'Operations');
            setText('info-joined-date', fmtDate(staff.date_of_joining || staff.created_at));

            setText('info-email', staff.email || 'N/A');
            setText('info-phone', staff.phone || 'N/A');
            setText('info-gender', staff.gender || 'Not specified');
            setText('info-dob', fmtDate(staff.date_of_birth));
            
            let emergencyStr = 'Not provided';
            if (staff.emergency_contact_name) {
                emergencyStr = staff.emergency_contact_name;
                if (staff.emergency_contact_phone) emergencyStr += ` (${staff.emergency_contact_phone})`;
            }
            setText('info-emergency', emergencyStr);
            setText('info-branch', staff.branch_name ? `${staff.branch_name} (${staff.branch_code || 'BR'})` : 'Unassigned');

            // 4. Address
            setText('addr-street', address.street || '—');
            setText('addr-city', address.city || '—');
            setText('addr-state', address.state || '—');
            setText('addr-pincode', address.pincode || '—');
            setText('addr-country', address.country || 'India');

            // 5. System Audit
            setText('sys-last-login', staff.last_login_at ? `${timeAgo(staff.last_login_at)} (${fmtDate(staff.last_login_at)})` : 'Never');
            setText('sys-last-ip', staff.last_login_ip || '—');
            setText('sys-joining-date', fmtDate(staff.date_of_joining));
            setText('sys-created-by', staff.created_by_name || 'System');
            setText('sys-staff-id', staff.staff_id || '—');
            setText('sys-total-shifts', String(totalShifts || 0));
            setText('sys-failed-logins', String(staff.failed_attempts || 0));
            
            const lockEl = document.getElementById('sys-lock-status');
            if (lockEl) {
                lockEl.textContent = isLocked ? 'Locked' : 'Unlocked';
                lockEl.className = isLocked ? 'text-xs font-black text-error' : 'text-xs font-bold text-success';
            }

            // 6. Documents
            const doc = (documents && documents.length > 0) ? documents[0] : null;
            setText('doc-id-type', doc?.id_type || staff.id_type || 'Not uploaded');
            setText('doc-id-number', doc?.id_number || staff.id_number || '—');

            const frontSrc = staff.front_image_url || (doc?.front_image ? `/Finzo/${doc.front_image.replace(/^\//, '')}` : null);
            const frontImg = document.getElementById('doc-front-img');
            const frontPlaceholder = document.getElementById('doc-front-placeholder');
            const frontWrapper = document.getElementById('doc-front-wrapper');
            if (frontSrc && frontImg && frontWrapper) {
                frontImg.src = frontSrc;
                frontImg.classList.remove('hidden');
                if (frontPlaceholder) frontPlaceholder.classList.add('hidden');
                frontWrapper.onclick = () => openLightbox(frontSrc);
            }

            const backSrc = staff.back_image_url || (doc?.back_image ? `/Finzo/${doc.back_image.replace(/^\//, '')}` : null);
            const backImg = document.getElementById('doc-back-img');
            const backPlaceholder = document.getElementById('doc-back-placeholder');
            const backWrapper = document.getElementById('doc-back-wrapper');
            if (backSrc && backImg && backWrapper) {
                backImg.src = backSrc;
                backImg.classList.remove('hidden');
                if (backPlaceholder) backPlaceholder.classList.add('hidden');
                backWrapper.onclick = () => openLightbox(backSrc);
            }

            // 7. Performance Overview
            setText('stat-total-deposits', fmtCurrency(performance.total_deposits));
            setText('stat-total-withdrawals', fmtCurrency(performance.total_withdrawals));
            setText('stat-total-txns', Number(performance.total_transactions || 0).toLocaleString());
            setText('stat-last-active', staff.last_login_at ? timeAgo(staff.last_login_at) : 'Never');

            // 8. Today's Shift Summary
            const depTodayCount = Number(today.deposit_count || 0);
            const depTodayAmount = fmtCurrency(today.total_deposits || 0);
            setText('summary-deposits', `${depTodayCount} (${depTodayAmount})`);

            const withTodayCount = Number(today.withdrawal_count || 0);
            const withTodayAmount = fmtCurrency(today.total_withdrawals || 0);
            setText('summary-withdrawals', `${withTodayCount} (${withTodayAmount})`);

            setText('summary-total-txns', String(today.total_transactions || 0));
            const netVol = (parseFloat(today.total_deposits) || 0) + (parseFloat(today.total_withdrawals) || 0);
            setText('summary-total-amount', fmtCurrency(netVol));

            // 9. Governance Button States
            const deactivateBtn = document.getElementById('deactivate-staff-btn');
            const suspendLabel = document.getElementById('suspend-label');
            const suspendIcon = document.getElementById('suspend-icon');
            if (deactivateBtn && suspendLabel && suspendIcon) {
                if (staff.status === 'suspended') {
                    deactivateBtn.dataset.action = 'reinstate';
                    suspendLabel.textContent = 'Reinstate Staff';
                    suspendIcon.textContent = 'check_circle';
                    suspendIcon.className = 'material-symbols-outlined text-success';
                } else {
                    deactivateBtn.dataset.action = 'deactivate';
                    suspendLabel.textContent = 'Suspend Staff';
                    suspendIcon.textContent = 'block';
                    suspendIcon.className = 'material-symbols-outlined text-warning';
                }
            }

            // 10. Shifts History
            const shiftContainer = document.getElementById('staff-shift-history');
            if (shiftContainer) {
                if (!shifts || shifts.length === 0) {
                    shiftContainer.innerHTML = '<div class="text-xs text-text-secondary text-center py-4">No recent shift logs recorded.</div>';
                } else {
                    shiftContainer.innerHTML = shifts.map(s => {
                        const dateStr = fmtDate(s.shift_date);
                        const loginTime = s.login_time ? new Date(s.login_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '—';
                        const logoutTime = s.logout_time ? new Date(s.logout_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : 'Active';
                        return `
                            <div class="flex items-center justify-between p-3 bg-surface rounded-xl border border-border-subtle">
                                <div>
                                    <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest">${dateStr}</div>
                                    <div class="text-xs font-bold text-text-primary mt-0.5">${loginTime} — ${logoutTime}</div>
                                </div>
                                <div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-base" aria-hidden="true">check</span>
                                </div>
                            </div>
                        `;
                    }).join('');
                }
            }

            // 11. Transactions Table
            const txnBody = document.getElementById('staff-transactions-body');
            if (txnBody) {
                if (!transactions || transactions.length === 0) {
                    txnBody.innerHTML = '<tr><td colspan="6" class="text-center py-12 text-text-secondary text-xs font-bold uppercase tracking-wider">No transactions processed yet</td></tr>';
                } else {
                    txnBody.innerHTML = transactions.map(t => {
                        const isDeposit = (t.type === 'deposit');
                        const typeBadge = isDeposit
                            ? '<span class="badge-tag bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full inline-flex items-center gap-1">DEPOSIT</span>'
                            : '<span class="badge-tag bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20 text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full inline-flex items-center gap-1">WITHDRAWAL</span>';
                        const amtClass = isDeposit ? 'text-success font-black' : 'text-error font-black';
                        const dateStr = t.created_at ? new Date(t.created_at).toLocaleString('en-IN', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—';

                        return `
                            <tr class="hover:bg-surface-hover transition-colors border-b border-border-subtle last:border-0">
                                <td class="font-mono text-xs font-bold text-text-secondary">${t.txn_id || '—'}</td>
                                <td class="text-xs font-bold text-text-primary whitespace-nowrap">${dateStr}</td>
                                <td class="text-xs font-bold text-text-primary truncate max-w-[180px]">${t.customer_name || 'Customer'}</td>
                                <td class="font-mono text-xs text-text-secondary">${t.account_no || 'N/A'}</td>
                                <td class="text-center">${typeBadge}</td>
                                <td class="text-right text-xs font-mono ${amtClass}">${fmtCurrency(t.amount)}</td>
                            </tr>
                        `;
                    }).join('');
                }
            }

            // 12. Recent Activities Table
            const actBody = document.getElementById('staff-activities-body');
            if (actBody) {
                if (!activities || activities.length === 0) {
                    actBody.innerHTML = '<tr><td colspan="4" class="text-center py-12 text-text-secondary text-xs font-bold uppercase tracking-wider">No audit activities logged</td></tr>';
                } else {
                    actBody.innerHTML = activities.map(a => {
                        const dateStr = a.created_at ? new Date(a.created_at).toLocaleString('en-IN', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—';
                        return `
                            <tr class="hover:bg-surface-hover transition-colors border-b border-border-subtle last:border-0">
                                <td class="text-xs font-bold text-text-primary whitespace-nowrap">${dateStr}</td>
                                <td><span class="px-2 py-0.5 bg-surface-subtle border border-border-subtle rounded-md text-[10px] font-black uppercase text-text-secondary">${a.action_type || 'EVENT'}</span></td>
                                <td class="text-xs text-text-secondary truncate max-w-sm" title="${a.details || ''}">${a.details || '—'}</td>
                                <td class="text-right text-xs font-mono text-text-secondary">${a.ip_address || '—'}</td>
                            </tr>
                        `;
                    }).join('');
                }
            }

        } catch (error) {
            console.error('Error loading staff dossier:', error);
            if (window.showToast) window.showToast('Network error loading staff dossier', 'error');
        }
    }

    // ==========================================
    //  GOVERNANCE MODAL SYSTEM
    // ==========================================
    function openGovernanceModal(action) {
        currentAction = action;
        const config = modalConfigs[action];
        if (!config) return;

        const modal = document.getElementById('governance-modal');
        if (!modal) return;

        setText('modal-title', config.title);
        setText('modal-subtitle', config.subtitle);
        setText('modal-icon', config.icon);

        const bodyText = document.getElementById('modal-body-text');
        if (bodyText) {
            bodyText.innerHTML = config.body;
            const targetEl = document.getElementById(action + '-target-name');
            if (targetEl && currentStaff.full_name) {
                targetEl.textContent = currentStaff.full_name;
            }
        }

        const confirmBtn = document.getElementById('confirm-governance-btn');
        if (confirmBtn) {
            confirmBtn.className = 'flex-1 py-3 font-bold rounded-xl text-xs uppercase tracking-wider shadow-sm transition-all ' + config.btnClass;
            confirmBtn.textContent = config.btnText;
            confirmBtn.disabled = false;
        }

        // Reason Grid for Suspension
        const reasonStep = document.getElementById('reason-step');
        const customContainer = document.getElementById('custom-reason-container');
        if (config.reasons && config.reasons.length > 0 && reasonStep) {
            let html = '';
            config.reasons.forEach((r, idx) => {
                html += `
                    <label class="flex items-center gap-2 p-2.5 bg-background rounded-xl border border-border-subtle hover:border-primary cursor-pointer transition-all text-xs">
                        <input type="radio" name="suspension_reason" value="${r}" ${idx === 0 ? 'checked' : ''} class="text-primary focus:ring-primary">
                        <span class="font-bold text-text-primary text-[11px]">${r}</span>
                    </label>
                `;
            });
            document.getElementById('reason-grid').innerHTML = html;
            reasonStep.classList.remove('hidden');

            document.querySelectorAll('input[name="suspension_reason"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'Other') {
                        customContainer.classList.remove('hidden');
                    } else {
                        customContainer.classList.add('hidden');
                    }
                });
            });
        } else if (reasonStep) {
            reasonStep.classList.add('hidden');
            if (customContainer) customContainer.classList.add('hidden');
        }

        // Clear password input & error
        const pwInput = document.getElementById('admin-password');
        if (pwInput) pwInput.value = '';
        const authErr = document.getElementById('auth-error');
        if (authErr) authErr.classList.add('hidden');

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    }

    function closeGovernanceModal() {
        const modal = document.getElementById('governance-modal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    }

    async function executeGovernanceAction() {
        const confirmBtn = document.getElementById('confirm-governance-btn');
        const passwordInput = document.getElementById('admin-password');
        const authErr = document.getElementById('auth-error');
        const password = passwordInput?.value || '';

        if (!password) {
            if (authErr) {
                authErr.textContent = 'Admin password is required to authorize this action';
                authErr.classList.remove('hidden');
            }
            return;
        }

        let apiAction = '';
        let reason = null;

        if (currentAction === 'deactivate') {
            apiAction = 'suspend';
            const checkedReason = document.querySelector('input[name="suspension_reason"]:checked')?.value || 'Administrative Review';
            const notes = document.getElementById('governance-notes')?.value?.trim() || '';
            reason = (checkedReason === 'Other' && notes) ? notes : checkedReason;
        } else if (currentAction === 'reinstate') {
            apiAction = 'reinstate';
        } else if (currentAction === 'reset') {
            apiAction = 'reset_password';
        } else if (currentAction === 'delete') {
            apiAction = 'delete';
        }

        if (confirmBtn) {
            confirmBtn.disabled = true;
            confirmBtn.textContent = 'Authorizing...';
        }

        try {
            const resp = await window.api.patch('/staff/' + staffId, {
                action: apiAction,
                password: password,
                reason: reason
            });

            if (resp.success) {
                if (currentAction === 'reset') {
                    const tempPass = resp.temp_password || '—';
                    const bodyText = document.getElementById('modal-body-text');
                    if (bodyText) {
                        bodyText.innerHTML = `
                            <div class="p-5 bg-background border border-border-subtle rounded-2xl text-center space-y-2">
                                <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest">Temporary One-Time Password</div>
                                <div class="text-2xl font-black font-mono text-primary tracking-widest selection:bg-primary selection:text-white py-1">${tempPass}</div>
                                <div class="text-[10px] font-bold text-success uppercase tracking-wider">Generated Successfully • Valid for 24 Hours</div>
                            </div>
                        `;
                    }
                    const reasonStep = document.getElementById('reason-step');
                    const authStep = document.getElementById('auth-step');
                    if (reasonStep) reasonStep.classList.add('hidden');
                    if (authStep) authStep.classList.add('hidden');

                    if (confirmBtn) {
                        confirmBtn.textContent = 'Close';
                        confirmBtn.disabled = false;
                        confirmBtn.className = 'flex-1 py-3 bg-emerald-600 text-white font-bold rounded-xl text-xs uppercase tracking-wider';
                        confirmBtn.onclick = () => {
                            closeGovernanceModal();
                            loadStaffData();
                        };
                    }
                } else if (currentAction === 'delete') {
                    if (window.showToast) window.showToast('Staff member permanently deleted', 'success');
                    setTimeout(() => {
                        window.location.href = '/Finzo/admin/staff';
                    }, 1000);
                } else {
                    if (window.showToast) window.showToast(resp.message || 'Action executed successfully', 'success');
                    closeGovernanceModal();
                    loadStaffData();
                }
            } else {
                if (authErr) {
                    authErr.textContent = resp.message || 'Authorization failed. Please verify credentials.';
                    authErr.classList.remove('hidden');
                }
                if (confirmBtn) {
                    confirmBtn.disabled = false;
                    confirmBtn.textContent = 'Retry';
                }
            }
        } catch (err) {
            console.error('Governance action error:', err);
            if (authErr) {
                authErr.textContent = err?.message || 'Failed to complete action. Access denied.';
                authErr.classList.remove('hidden');
            }
            if (confirmBtn) {
                confirmBtn.disabled = false;
                confirmBtn.textContent = 'Retry';
            }
        }
    }

    // ==========================================
    //  EVENT BINDINGS
    // ==========================================
    function bindEvents() {
        // Open Passbook & Statement Modal Buttons
        ['open-passbook-modal-btn', 'open-passbook-modal-table-btn'].forEach(id => {
            const btn = document.getElementById(id);
            if (btn) {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    openPassbookModal();
                });
            }
        });

        // Close Passbook Modal
        const closePassbookBtn = document.getElementById('close-passbook-btn');
        if (closePassbookBtn) {
            closePassbookBtn.addEventListener('click', closePassbookModal);
        }

        // Print Passbook Action
        const printAction = document.getElementById('print-passbook-action');
        if (printAction) {
            printAction.addEventListener('click', () => {
                window.print();
            });
        }

        // Passbook Date Filter Presets
        const presetAll = document.getElementById('pb-preset-all');
        const presetMonth = document.getElementById('pb-preset-month');
        const preset30 = document.getElementById('pb-preset-30');
        const preset90 = document.getElementById('pb-preset-90');
        const applyFilterBtn = document.getElementById('pb-apply-filter');

        if (presetAll) presetAll.addEventListener('click', () => setPassbookPreset('all'));
        if (presetMonth) presetMonth.addEventListener('click', () => setPassbookPreset('month'));
        if (preset30) preset30.addEventListener('click', () => setPassbookPreset('30'));
        if (preset90) preset90.addEventListener('click', () => setPassbookPreset('90'));

        if (applyFilterBtn) {
            applyFilterBtn.addEventListener('click', () => {
                const fromVal = document.getElementById('pb-filter-from')?.value || null;
                const toVal = document.getElementById('pb-filter-to')?.value || null;
                passbookFilter = { from: fromVal, to: toVal, label: 'CUSTOM' };
                renderPassbookContent();
            });
        }

        // Governance Action Buttons
        const deactBtn = document.getElementById('deactivate-staff-btn');
        if (deactBtn) {
            deactBtn.addEventListener('click', () => {
                openGovernanceModal(deactBtn.dataset.action || 'deactivate');
            });
        }

        const resetBtn = document.getElementById('reset-password-btn');
        if (resetBtn) {
            resetBtn.addEventListener('click', () => {
                openGovernanceModal('reset');
            });
        }

        const deleteBtn = document.getElementById('delete-staff-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', () => {
                openGovernanceModal('delete');
            });
        }

        const abortGovBtn = document.getElementById('abort-governance-btn');
        if (abortGovBtn) {
            abortGovBtn.addEventListener('click', closeGovernanceModal);
        }

        const confirmGovBtn = document.getElementById('confirm-governance-btn');
        if (confirmGovBtn) {
            confirmGovBtn.addEventListener('click', executeGovernanceAction);
        }

        // Lightbox close button
        const closeLightboxBtn = document.getElementById('close-lightbox-btn');
        if (closeLightboxBtn) {
            closeLightboxBtn.addEventListener('click', closeLightbox);
        }

        const lightboxModal = document.getElementById('lightbox-modal');
        if (lightboxModal) {
            lightboxModal.addEventListener('click', (e) => {
                if (e.target === lightboxModal) closeLightbox();
            });
        }

        // Escape Key Global Handler
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closePassbookModal();
                closeGovernanceModal();
                closeLightbox();
            }
        });
    }

    // Initialize
    loadStaffData();
    bindEvents();
});
