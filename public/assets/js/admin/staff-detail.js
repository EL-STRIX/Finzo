/**
 * Finzo Admin — Staff Detail (Dossier View)
 * v2.0 — Redesigned to match admin-detail.html
 */
document.addEventListener('DOMContentLoaded', function() {

    var urlParams = new URLSearchParams(window.location.search);
    var staffId = urlParams.get('id');
    
    if (!staffId) {
        alert('Staff ID is required');
        return;
    }

    var currentAction = '';

    var modalConfigs = {
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
        },
        delete: {
            title: 'Delete Staff Account',
            subtitle: 'Critical security procedure',
            body: 'You are about to <span class="text-rose-600 font-black underline">PERMANENTLY DELETE</span> <b id="delete-target-name">...</b> from the system. This action is irreversible and will remove all associated address and identity records.',
            icon: 'delete_forever',
            color: 'rose',
            btnText: 'Confirm Permanent Deletion',
            btnClass: 'bg-rose-700 shadow-rose-700/20',
            reasons: []
        }
    };

    function getInitials(name) {
        if (!name) return 'S';
        return name.split(' ').map(function(n) { return n[0]; }).join('').toUpperCase();
    }

    function fmtCurrency(amount) {
        if (window.FinzoUtils && window.FinzoUtils.formatINR) return window.FinzoUtils.formatINR(amount);
        var num = parseFloat(amount) || 0;
        return '₹' + num.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function fmtDate(dateStr) {
        if (!dateStr) return 'N/A';
        try {
            var d = new Date(dateStr);
            if (isNaN(d.getTime())) return 'N/A';
            return d.toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
        } catch(e) { return 'N/A'; }
    }

    function timeAgo(dateStr) {
        if (!dateStr) return 'Never';
        try {
            var d = new Date(dateStr);
            var now = new Date();
            var diff = now - d;
            var mins = Math.floor(diff / 60000);
            var hours = Math.floor(diff / 3600000);
            var days = Math.floor(diff / 86400000);
            if (mins < 1) return 'Just now';
            if (mins < 60) return mins + ' min ago';
            if (hours < 24) return hours + ' hour' + (hours > 1 ? 's' : '') + ' ago';
            if (days < 30) return days + ' day' + (days > 1 ? 's' : '') + ' ago';
            return fmtDate(dateStr);
        } catch(e) { return 'N/A'; }
    }

    // safe DOM setter
    function setText(id, val) {
        var el = document.getElementById(id);
        if (el) el.textContent = val || '---';
    }
    function setHtml(id, val) {
        var el = document.getElementById(id);
        if (el) el.innerHTML = val || '---';
    }

    // Lightbox
    window.openLightbox = function(imgSrc) {
        var modal = document.getElementById('lightbox-modal');
        var img = document.getElementById('lightbox-img');
        if (modal && img) {
            img.src = imgSrc;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    };
    window.closeLightbox = function() {
        var modal = document.getElementById('lightbox-modal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    };

    // =============================
    //  LOAD STAFF DATA
    // =============================
    async function loadStaffData() {
        try {
            var response = await window.api.get('/admin/staff-detail.php?id=' + staffId);

            if (!response || (!response.success && response.status !== 'success')) {
                if (window.showToast) window.showToast(response?.message || 'Error loading staff details', 'error');
                return;
            }

            var staff = response.staff || {};
            var performance = response.performance || {};
            var today = response.today_performance || {};
            var transactions = response.transactions || [];
            var totalShifts = response.total_shifts || 0;
            var documents = response.documents || [];
            var address = response.address || {};
            var appBase = window.location.origin + '/Finzo/';

            // ─── HEADER ─────────────────────────────
            setText('header-staff-name', staff.full_name);

            // Status badge
            var statusBadge = document.getElementById('header-status-badge');
            if (statusBadge) {
                var isActive = (staff.status || '').toLowerCase() === 'active';
                var dot = statusBadge.querySelector('.status-dot');
                var label = statusBadge.querySelector('.status-label');
                if (isActive) {
                    statusBadge.className = 'flex items-center gap-2 px-3 py-1.5 rounded-full border border-emerald-200 dark:border-emerald-500/30 bg-emerald-50 dark:bg-emerald-500/10';
                    if (dot) dot.className = 'status-dot w-1.5 h-1.5 rounded-full animate-pulse bg-emerald-500';
                    if (label) { label.className = 'status-label text-[10px] font-black uppercase tracking-widest text-emerald-700 dark:text-emerald-400'; label.textContent = 'ACTIVE ACCOUNT'; }
                } else {
                    statusBadge.className = 'flex items-center gap-2 px-3 py-1.5 rounded-full border border-rose-200 dark:border-rose-500/30 bg-rose-50 dark:bg-rose-500/10';
                    if (dot) dot.className = 'status-dot w-1.5 h-1.5 rounded-full animate-pulse bg-rose-500';
                    if (label) { label.className = 'status-label text-[10px] font-black uppercase tracking-widest text-rose-700 dark:text-rose-400'; label.textContent = 'SUSPENDED'; }
                }
            }

            // ─── PROFILE CARD ────────────────────────
            var photoContainer = document.getElementById('staff-photo-container');
            if (photoContainer) {
                var initials = getInitials(staff.full_name);
                if (staff.profile_photo) {
                    photoContainer.innerHTML = '<img src="' + appBase + staff.profile_photo + '" class="w-full h-full object-cover" alt="' + (staff.full_name||'') + '" onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'flex\'" /><span style="display:none" class="text-4xl font-black text-slate-300 w-full h-full flex items-center justify-center">' + initials + '</span>';
                } else {
                    var grad = (window.FinzoUtils && window.FinzoUtils.getAvatarGradient) ? window.FinzoUtils.getAvatarGradient(initials) : 'linear-gradient(135deg, #667eea, #764ba2)';
                    photoContainer.style.background = grad;
                    photoContainer.innerHTML = '<span class="text-4xl font-black text-white">' + initials + '</span>';
                }
            }

            setText('info-name', staff.full_name);
            setText('info-staff-id-badge', staff.staff_id);
            setText('info-designation-badge', staff.designation);
            setText('info-department-badge', staff.department);
            setText('info-joined-date', fmtDate(staff.date_of_joining || staff.created_at));

            // Personal Info
            setText('info-email', staff.email || 'N/A');
            setText('info-phone', staff.phone || 'N/A');
            setText('info-gender', staff.gender || 'N/A');
            setText('info-dob', fmtDate(staff.date_of_birth));
            
            var emergencyText = 'Not provided';
            if (staff.emergency_contact_name) {
                emergencyText = staff.emergency_contact_name;
                if (staff.emergency_contact_phone) emergencyText += ' (' + staff.emergency_contact_phone + ')';
            }
            setText('info-emergency', emergencyText);
            setText('info-branch', staff.branch_name || 'Unassigned');

            // ─── ADDRESS ─────────────────────────────
            setText('addr-street', address.street);
            setText('addr-city', address.city);
            setText('addr-state', address.state);
            setText('addr-pincode', address.pincode);
            setText('addr-country', address.country || 'India');

            // ─── SYSTEM AUDIT ────────────────────────
            setText('sys-last-login', staff.last_login_at ? timeAgo(staff.last_login_at) : 'Never');
            setText('sys-last-ip', staff.last_login_ip || '—');
            setText('sys-joining-date', fmtDate(staff.date_of_joining));
            setText('sys-created-by', staff.created_by_name || staff.created_by || 'System');
            setText('sys-staff-id', staff.staff_id);
            setText('sys-total-shifts', String(totalShifts || 0));
            setText('sys-failed-logins', String(staff.failed_attempts || 0));
            
            var isLocked = staff.locked_until && new Date(staff.locked_until) > new Date();
            var lockEl = document.getElementById('sys-lock-status');
            if (lockEl) {
                if (isLocked) {
                    lockEl.innerText = 'Locked';
                    lockEl.className = 'text-xs font-black text-rose-500';
                } else {
                    lockEl.innerText = 'Unlocked';
                    lockEl.className = 'text-xs font-bold text-slate-800 dark:text-slate-200';
                }
            }
            
            // ─── SECURITY ALERT BANNER ────────────────────────
            var alertBanner = document.getElementById('security-alert-banner');
            var alertIcon = document.getElementById('security-alert-icon');
            var alertTitle = document.getElementById('security-alert-title');
            var alertMessage = document.getElementById('security-alert-message');

            if (alertBanner && alertIcon && alertTitle && alertMessage) {
                // reset classes
                alertBanner.className = 'hidden rounded-2xl p-4 sm:p-5 flex items-start gap-4';
                
                if (staff.status === 'suspended') {
                    alertBanner.classList.remove('hidden');
                    alertBanner.classList.add('bg-rose-50', 'text-rose-900', 'border', 'border-rose-200', 'dark:bg-rose-500/10', 'dark:border-rose-500/20', 'dark:text-rose-200');
                    alertIcon.innerText = 'gavel';
                    alertTitle.innerText = 'Account Suspended';
                    var reason = staff.suspension_reason || 'No reason provided';
                    var suspender = staff.suspended_by_name || 'System Administrator';
                    alertMessage.innerHTML = 'This staff account has been suspended by <strong>' + suspender + '</strong>.<br>Reason: ' + reason;
                } else if (isLocked) {
                    alertBanner.classList.remove('hidden');
                    alertBanner.classList.add('bg-amber-50', 'text-amber-900', 'border', 'border-amber-200', 'dark:bg-amber-500/10', 'dark:border-amber-500/20', 'dark:text-amber-200');
                    alertIcon.innerText = 'lock';
                    alertTitle.innerText = 'Account Temporarily Locked';
                    alertMessage.innerHTML = 'This account is locked due to too many failed login attempts (' + staff.failed_attempts + ').<br>It will automatically unlock at ' + staff.locked_until + '.';
                }
            }
            
            // ─── SHIFT HISTORY ────────────────────────
            var shiftHistoryDiv = document.getElementById('staff-shift-history');
            var shifts = response.shifts;
            if (shiftHistoryDiv && shifts) {
                if (shifts.length === 0) {
                    shiftHistoryDiv.innerHTML = '<div class="text-xs text-slate-400 text-center py-4">No recent shifts found.</div>';
                } else {
                    var html = '';
                    shifts.forEach(function(s) {
                        var dateStr = fmtDate(s.shift_date);
                        var loginTime = new Date(s.login_time).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                        var logoutTime = new Date(s.logout_time).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                        
                        html += '<div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-white/5 rounded-xl border border-slate-100 dark:border-white/5">';
                        html += '    <div>';
                        html += '        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">' + dateStr + '</div>';
                        html += '        <div class="text-xs font-bold text-slate-800 dark:text-slate-200 mt-1">' + loginTime + ' — ' + logoutTime + '</div>';
                        html += '    </div>';
                        html += '    <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400 flex items-center justify-center">';
                        html += '        <span class="material-symbols-outlined text-[16px]">check_circle</span>';
                        html += '    </div>';
                        html += '</div>';
                    });
                    shiftHistoryDiv.innerHTML = html;
                }
            }

            // ─── DOCUMENTS ──────────────────────────
            var doc = (documents && documents.length > 0) ? documents[0] : null;
            setText('doc-id-type', doc ? doc.id_type : 'Not uploaded');
            setText('doc-id-number', doc ? doc.id_number : '—');

            if (doc && doc.front_image) {
                var frontImg = document.getElementById('doc-front-img');
                var frontPlaceholder = document.getElementById('doc-front-placeholder');
                var frontWrapper = document.getElementById('doc-front-wrapper');
                var frontSrc = appBase + doc.front_image;
                if (frontImg) { frontImg.src = frontSrc; frontImg.classList.remove('hidden'); }
                if (frontPlaceholder) frontPlaceholder.classList.add('hidden');
                if (frontWrapper) frontWrapper.onclick = function() { openLightbox(frontSrc); };
            }
            if (doc && doc.back_image) {
                var backImg = document.getElementById('doc-back-img');
                var backPlaceholder = document.getElementById('doc-back-placeholder');
                var backWrapper = document.getElementById('doc-back-wrapper');
                var backSrc = appBase + doc.back_image;
                if (backImg) { backImg.src = backSrc; backImg.classList.remove('hidden'); }
                if (backPlaceholder) backPlaceholder.classList.add('hidden');
                if (backWrapper) backWrapper.onclick = function() { openLightbox(backSrc); };
            }

            // ─── PERFORMANCE STATS ──────────────────
            setText('stat-total-deposits', fmtCurrency(performance.total_deposits));
            setText('stat-total-withdrawals', fmtCurrency(performance.total_withdrawals));
            setText('stat-total-txns', String(performance.total_transactions || 0));
            setText('stat-last-active', staff.last_login_at ? timeAgo(staff.last_login_at) : 'Never');

            // ─── TODAY'S SHIFT ───────────────────────
            setText('summary-deposits', (today.deposit_count || 0) + ' (' + fmtCurrency(today.total_deposits) + ')');
            setText('summary-withdrawals', (today.withdrawal_count || 0) + ' (' + fmtCurrency(today.total_withdrawals) + ')');
            setText('summary-total-txns', String(today.total_transactions || 0));
            setText('summary-total-amount', fmtCurrency((parseFloat(today.total_deposits) || 0) + (parseFloat(today.total_withdrawals) || 0)));

            // ─── BUTTON STATE ───────────────────────
            if ((staff.status || '').toLowerCase() !== 'active') {
                var deactivateBtn = document.getElementById('deactivate-staff-btn');
                if (deactivateBtn) {
                    deactivateBtn.disabled = true;
                    deactivateBtn.classList.add('opacity-50', 'cursor-not-allowed');
                }
                var suspendLabel = document.getElementById('suspend-label');
                if (suspendLabel) suspendLabel.textContent = 'Already Suspended';
                var suspendIcon = document.getElementById('suspend-icon');
                if (suspendIcon) suspendIcon.textContent = 'check_circle';
            }

            // ─── TRANSACTIONS TABLE ─────────────────
            var tbody = document.getElementById('staff-transactions-body');
            if (tbody) {
                if (transactions && transactions.length > 0) {
                    var html = '';
                    for (var i = 0; i < transactions.length; i++) {
                        var t = transactions[i];
                        var typeClass = t.type === 'deposit' ? 'badge-success' : 'badge-error';
                        var amtClass = t.type === 'deposit' ? 'text-primary dark:text-blue-400' : 'text-error';
                        var dateStr = (window.FinzoUtils && window.FinzoUtils.formatDateTime) ? window.FinzoUtils.formatDateTime(t.created_at) : fmtDate(t.created_at);
                        html += '<tr class="hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">';
                        html += '<td class="text-[11px] font-bold opacity-60 font-mono">' + t.txn_id + '</td>';
                        html += '<td class="text-xs font-medium">' + dateStr + '</td>';
                        html += '<td class="font-bold text-sm text-slate-800 dark:text-slate-100">' + t.customer_name + '</td>';
                        html += '<td class="font-mono text-xs opacity-60">' + t.account_no + '</td>';
                        html += '<td><span class="status-badge ' + typeClass + '">' + t.type.toUpperCase() + '</span></td>';
                        html += '<td class="font-black text-xs ' + amtClass + '">' + fmtCurrency(t.amount) + '</td>';
                        html += '<td class="text-right"><button onclick="window.open(\'../../../../public/assets/api/pdf/receipt-pdf.php?id=' + t.txn_id + '\', \'_blank\')" class="p-2 hover:bg-slate-100 dark:hover:bg-white/5 rounded-lg transition-all text-slate-400" title="Print Receipt"><span class="material-symbols-outlined text-lg">receipt_long</span></button></td>';
                        html += '</tr>';
                    }
                    tbody.innerHTML = html;
                } else {
                    tbody.innerHTML = '<tr><td colspan="7" class="py-12 text-center opacity-40 font-bold">No transactions recorded yet</td></tr>';
                }
            }

            // ─── ACTIVITIES TABLE ───────────────────
            var activities = response.activities || [];
            var actBody = document.getElementById('staff-activities-body');
            if (actBody) {
                if (activities && activities.length > 0) {
                    var html = '';
                    for (var i = 0; i < activities.length; i++) {
                        var a = activities[i];
                        var dateStr = (window.FinzoUtils && window.FinzoUtils.formatDateTime) ? window.FinzoUtils.formatDateTime(a.created_at) : fmtDate(a.created_at);
                        html += '<tr class="hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">';
                        html += '<td class="text-xs font-medium">' + dateStr + '</td>';
                        html += '<td class="font-bold text-[11px] uppercase tracking-wider text-slate-800 dark:text-slate-100"><span class="px-2 py-1 bg-slate-100 dark:bg-white/10 rounded-md">' + a.action_type + '</span></td>';
                        html += '<td class="text-xs text-slate-600 dark:text-slate-400 max-w-xs truncate" title="' + (a.details || '') + '">' + (a.details || '—') + '</td>';
                        html += '<td class="text-right text-xs font-mono opacity-60">' + (a.ip_address || '—') + '</td>';
                        html += '</tr>';
                    }
                    actBody.innerHTML = html;
                } else {
                    actBody.innerHTML = '<tr><td colspan="4" class="py-12 text-center opacity-40 font-bold">No recent activity recorded</td></tr>';
                }
            }

        } catch (error) {
            if (window.showToast) window.showToast('Connection error. Please try again.', 'error');
        }
    }

    // =============================
    //  GOVERNANCE MODAL SYSTEM
    // =============================
    function openGovernanceModal(action) {
        currentAction = action;
        var config = modalConfigs[action];

        var reasonStep = document.getElementById('reason-step');
        var authStep = document.getElementById('auth-step');
        var bodyText = document.getElementById('modal-body-text');
        var customContainer = document.getElementById('custom-reason-container');
        
        if (reasonStep) reasonStep.classList.add('hidden');
        if (authStep) authStep.classList.remove('hidden');
        if (customContainer) customContainer.classList.add('hidden');

        if (bodyText) bodyText.innerHTML = config.body;
        setText('modal-title', config.title);
        setText('modal-subtitle', config.subtitle);
        setText('modal-icon', config.icon);
        
        var staffName = document.getElementById('info-name');
        var nameText = staffName ? staffName.textContent : 'this staff member';
        var targetName = document.getElementById(action + '-target-name');
        if (targetName) targetName.textContent = nameText;

        var iconContainer = document.getElementById('modal-icon-container');
        if (iconContainer) iconContainer.className = 'w-16 h-16 rounded-full mx-auto flex items-center justify-center mb-4 bg-' + config.color + '-50 dark:bg-' + config.color + '-500/10 text-' + config.color + '-500 shadow-lg';
        
        var confirmBtn = document.getElementById('confirm-governance-btn');
        if (confirmBtn) {
            confirmBtn.className = 'flex-1 py-4 text-white font-black rounded-2xl text-[11px] uppercase tracking-widest shadow-lg hover:scale-[1.02] active:scale-[0.98] transition-all ' + config.btnClass;
            confirmBtn.textContent = config.btnText;
            confirmBtn.disabled = false;
        }

        if (config.reasons && config.reasons.length > 0 && reasonStep) {
            var reasonsHtml = '';
            config.reasons.forEach(function(r) {
                reasonsHtml += '<label class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-white/5 rounded-xl border-2 border-transparent hover:border-slate-200 dark:hover:border-white/10 cursor-pointer transition-all group">';
                reasonsHtml += '<input type="radio" name="suspension_reason" value="' + r + '" class="text-primary focus:ring-primary/10">';
                reasonsHtml += '<span class="text-[10px] font-black text-slate-500 group-hover:text-black dark:group-hover:text-white leading-tight">' + r + '</span>';
                reasonsHtml += '</label>';
            });
            document.getElementById('reason-grid').innerHTML = reasonsHtml;
            reasonStep.classList.remove('hidden');
            
            document.querySelectorAll('input[name="suspension_reason"]').forEach(function(radio) {
                radio.addEventListener('change', function() {
                    if (this.value === 'Other') {
                        customContainer.classList.remove('hidden');
                    } else {
                        customContainer.classList.add('hidden');
                    }
                });
            });
        }

        var backdrop = document.getElementById('modal-backdrop');
        if (backdrop) backdrop.style.opacity = '1';
        
        var container = document.getElementById('modal-container');
        if (container) { container.classList.remove('scale-95'); container.style.opacity = '1'; }
        
        var modal = document.getElementById('governance-modal');
        if (modal) { modal.classList.remove('hidden'); modal.classList.add('flex'); }
        
        document.body.classList.add('overflow-hidden');
    }

    window.closeGovernanceModal = function() {
        var container = document.getElementById('modal-container');
        if (container) { container.classList.add('scale-95'); container.style.opacity = '0'; }
        
        var backdrop = document.getElementById('modal-backdrop');
        if (backdrop) backdrop.style.opacity = '0';
        
        setTimeout(function() {
            var modal = document.getElementById('governance-modal');
            if (modal) { modal.classList.add('hidden'); modal.classList.remove('flex'); }
            document.body.classList.remove('overflow-hidden');
            var pw = document.getElementById('admin-password');
            if (pw) pw.value = '';
            var err = document.getElementById('auth-error');
            if (err) err.classList.add('hidden');
            var rs = document.getElementById('reason-step');
            if (rs) rs.classList.add('hidden');
            var cc = document.getElementById('custom-reason-container');
            if (cc) cc.classList.add('hidden');
        }, 300);
    };

    // Button bindings
    var dBtn = document.getElementById('deactivate-staff-btn');
    if (dBtn) dBtn.addEventListener('click', function() { if (!this.disabled) openGovernanceModal('deactivate'); });

    var delBtn = document.getElementById('delete-staff-btn');
    if (delBtn) delBtn.addEventListener('click', function() { openGovernanceModal('delete'); });

    var rBtn = document.getElementById('reset-password-btn');
    if (rBtn) rBtn.addEventListener('click', function() { openGovernanceModal('reset'); });

    var cBtn = document.getElementById('confirm-governance-btn');
    if (cBtn) {
        cBtn.addEventListener('click', async function() {
            if (this.textContent.indexOf('DONE') >= 0 || this.textContent.indexOf('LOGGED') >= 0) { 
                closeGovernanceModal(); 
                return; 
            }

            var password = document.getElementById('admin-password').value;
            var checkedReason = document.querySelector('input[name="suspension_reason"]:checked');
            var reason = checkedReason ? checkedReason.value : null;
            
            var apiAction = '';
            if (currentAction === 'deactivate') apiAction = 'suspend';
            else if (currentAction === 'reset') apiAction = 'reset_password';
            else if (currentAction === 'delete') apiAction = 'delete';

            if (currentAction === 'deactivate' && !reason) {
                alert('Please select a reason for deactivation.');
                return;
            }

            this.disabled = true;
            this.innerHTML = '<span class="material-symbols-outlined animate-spin text-sm">refresh</span> AUTHORIZING...';

            try {
                var notesEl = document.getElementById('custom-reason-notes');
                var resp = await window.api.post('/admin/staff-action.php', {
                    action: apiAction,
                    staff_id: staffId,
                    password: password,
                    reason: reason,
                    notes: notesEl ? notesEl.value : ''
                });

                if (resp.status === 'success' || resp.success) {
                    if (currentAction === 'reset') {
                        var authStep = document.getElementById('auth-step');
                        if (authStep) authStep.style.display = 'none';
                        var subtitle = document.getElementById('modal-subtitle');
                        if (subtitle) subtitle.style.display = 'none';
                        setText('modal-title', 'Temporary Password Generated');
                        var bodyText = document.getElementById('modal-body-text');
                        if (bodyText) {
                            var tempPw = resp.temp_password || (resp.data && resp.data.temp_password) || '--------';
                            bodyText.innerHTML = '<div class="p-6 bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 rounded-2xl text-center"><p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">One-Time Password</p><div class="text-2xl font-black text-primary dark:text-amber-500 font-mono tracking-wider mb-2">' + tempPw + '</div><p class="text-[10px] font-bold text-rose-500 uppercase">Valid for 24 hours only</p></div>';
                        }
                        this.disabled = false;
                        this.innerHTML = '<span class="material-symbols-outlined">check_circle</span> DONE';
                        this.className = 'flex-1 py-4 text-white font-black rounded-2xl text-[11px] uppercase tracking-widest shadow-lg transition-all bg-emerald-500 shadow-emerald-500/20';
                    } else {
                        this.innerHTML = '<span class="material-symbols-outlined">check_circle</span> ACTION LOGGED';
                        this.className = 'flex-1 py-4 text-white font-black rounded-2xl text-[11px] uppercase tracking-widest shadow-lg transition-all bg-emerald-500 shadow-emerald-500/20';
                        setTimeout(function() {
                            closeGovernanceModal();
                            loadStaffData();
                        }, 1500);
                    }
                } else {
                    var errEl = document.getElementById('auth-error');
                    if (errEl) { errEl.textContent = resp.message; errEl.classList.remove('hidden'); }
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

    // Start loading
    loadStaffData();
});
