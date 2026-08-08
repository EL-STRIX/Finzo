/**
 * Finzo Admin — Locked Account Detail
 * Handles displaying details, unlocking accounts, and audit logs
 * Version 1.1 — May 2026
 */

document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const userId = urlParams.get('id');

    if (!userId) {
        alert('User ID missing');
        window.location.href = 'admin-locked-accounts.html';
        return;
    }

    // ── Load Detail ──
    async function loadDetail() {
        try {
            const response = await window.api.get(`/users/locked/${userId}`);
            if (response.success) {
                const user = response.user;
                const logs = response.logs || [];
                populateUI(user);
                renderAuditLogs(logs);
            } else {
                window.FinzoUtils?.showToast(response.message || 'Failed to load details', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            window.FinzoUtils?.showToast('Network error loading details', 'error');
        }
    }

    // ── Populate UI ──
    function populateUI(user) {
        // Safe setter helper
        const setEl = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.textContent = val;
        };

        // Avatar — show profile picture if available, otherwise initials
        const avatarEl = document.getElementById('d-avatar');
        if (avatarEl) {
            if (user.profile_picture) {
                avatarEl.innerHTML = `<img src="/Finzo/public/assets/${user.profile_picture}" class="w-full h-full object-cover rounded-2xl" alt="${user.full_name}">`;
            } else {
                avatarEl.textContent = user.initials || '??';
            }
        }

        // Basic info
        setEl('d-name', user.full_name || 'N/A');
        setEl('d-email', user.email || 'N/A');
        setEl('d-acc-no', user.account_no || 'N/A');
        setEl('d-phone', user.phone || 'N/A');
        setEl('d-acc-type', user.account_type ? (user.account_type.charAt(0).toUpperCase() + user.account_type.slice(1) + ' Account') : 'N/A');

        // Account type badge
        const typeBadge = document.getElementById('d-acc-type-badge');
        if (typeBadge) {
            typeBadge.textContent = user.account_type ? user.account_type.toUpperCase() : 'N/A';
            typeBadge.className = 'px-3 py-1 bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-400 text-[10px] font-black rounded-lg uppercase tracking-widest';
        }

        // Date joined
        setEl('d-opened', user.opened_at || 'N/A');

        // Balance
        const balance = parseFloat(user.balance || 0).toLocaleString('en-IN', { style: 'currency', currency: 'INR' });
        setEl('d-balance', balance);

        // Lock status badge
        const isLocked = user.is_locked;
        const mainBadge = document.getElementById('d-main-badge');
        if (mainBadge) {
            mainBadge.textContent = isLocked ? 'LOCKED' : 'UNLOCKED';
            if (isLocked) {
                mainBadge.className = 'px-3 py-1 bg-red-100 dark:bg-red-500/10 text-red-600 text-[10px] font-black rounded-lg uppercase tracking-widest';
            } else {
                mainBadge.className = 'px-3 py-1 bg-green-100 dark:bg-green-500/10 text-green-600 text-[10px] font-black rounded-lg uppercase tracking-widest';
            }
        }

        // Lock info
        setEl('d-locked-date', user.locked_on_date || user.locked_at || 'N/A');
        setEl('d-lock-time', user.locked_on_ts || user.lock_time || 'N/A');
        setEl('d-locked-by', user.locked_by || 'System Auto-Lock');
        setEl('d-ip', user.ip_address || 'N/A');
        setEl('d-device', user.device || 'N/A');

        // Reason for lock
        setEl('d-reason', `Account locked after ${user.failed_attempts || 5} consecutive failed login attempts`);

        // Temp password status
        const tempStatus = document.getElementById('d-temp-status');
        if (tempStatus) {
            const hasPass = user.temp_password_active;
            tempStatus.innerHTML = hasPass
                ? '<span class="px-2 py-1 bg-green-100 text-green-600 text-[9px] font-black rounded uppercase">Generated</span>'
                : '<span class="px-2 py-1 bg-amber-100 text-amber-600 text-[9px] font-black rounded uppercase">Not Generated</span>';
        }

        // If temp password is active, show temp pass card and hide unlock button
        const tempPassCard = document.getElementById('temp-pass-card');
        const unlockActionCard = document.getElementById('unlock-action-card');

        if (user.temp_password_active && user.temp_password) {
            if (tempPassCard) tempPassCard.classList.remove('hidden');
            if (unlockActionCard) unlockActionCard.classList.add('hidden');
            const displayPass = document.getElementById('display-pass');
            if (displayPass) displayPass.textContent = user.temp_password;
        }

        // If account is unlocked (admin already resolved), update badge but keep controls
        if (!isLocked && mainBadge) {
            // Badge already updated above
        }

        // Modal name
        const modalName = document.getElementById('modal-name');
        if (modalName) modalName.textContent = user.full_name;
    }

    // ── Render Audit Logs (Login History only) ──
    function renderAuditLogs(logs) {
        const tbody = document.getElementById('audit-logs-body');
        if (!tbody) return;

        // Filter to login-related events only
        const loginActions = ['login_failed', 'login_success', 'user_login', 'account_locked', 'account_unlocked', 'password_reset'];
        const filtered = (logs || []).filter(log => {
            const action = (log.action || '').toLowerCase();
            return loginActions.some(a => action.includes(a));
        });

        // Update event count badge
        const countBadge = document.getElementById('log-count-badge');
        if (countBadge) countBadge.textContent = `${filtered.length} Event${filtered.length !== 1 ? 's' : ''}`;

        if (filtered.length === 0) {
            tbody.innerHTML = '<tr><td colspan=\"3\" class=\"py-12 text-center\"><div class=\"flex flex-col items-center justify-center\"><span class=\"material-symbols-outlined text-4xl text-text-muted/30 mb-3\">inventory_2</span><div class=\"text-sm font-bold text-text-muted uppercase tracking-widest\">No login history found</div></div></td></tr>';
            return;
        }

        const statusMap = {
            'user_login': { label: 'Login Success', icon: 'check_circle', color: 'text-green-500', bg: 'bg-green-50 dark:bg-green-500/10' },
            'login_success': { label: 'Login Success', icon: 'check_circle', color: 'text-green-500', bg: 'bg-green-50 dark:bg-green-500/10' },
            'login_failed': { label: 'Login Failed', icon: 'cancel', color: 'text-red-500', bg: 'bg-red-50 dark:bg-red-500/10' },
            'account_locked': { label: 'Account Locked', icon: 'lock', color: 'text-red-500', bg: 'bg-red-50 dark:bg-red-500/10' },
            'account_unlocked': { label: 'Account Unlocked', icon: 'lock_open', color: 'text-green-500', bg: 'bg-green-50 dark:bg-green-500/10' },
            'password_reset': { label: 'Password Reset', icon: 'key', color: 'text-amber-500', bg: 'bg-amber-50 dark:bg-amber-500/10' },
        };

        const getStatus = (action) => {
            const key = (action || '').toLowerCase();
            for (const [k, v] of Object.entries(statusMap)) {
                if (key.includes(k)) return v;
            }
            return { label: action, icon: 'info', color: 'text-slate-400', bg: 'bg-slate-50 dark:bg-white/5' };
        };

        const html = filtered.map(log => {
            const s = getStatus(log.action);
            return `
                <tr class="hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-colors">
                    <td class="pl-6">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg ${s.bg} flex items-center justify-center flex-shrink-0">
                                <span class="material-symbols-outlined text-[16px] ${s.color}" aria-hidden="true">${s.icon}</span>
                            </div>
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">${s.label}</span>
                        </div>
                    </td>
                    <td class="text-[11px] font-mono font-bold text-slate-500">${log.ip || '—'}</td>
                    <td class="text-[11px] text-slate-500">${log.time || '—'}</td>
                </tr>
            `;
        }).join('');
        tbody.innerHTML = html;
    }

    // ── Modal ──
    const unlockModal = document.getElementById('unlock-modal');

    window.openUnlockModal = () => {
        if (unlockModal) unlockModal.classList.remove('hidden');
    };

    window.closeModal = () => {
        if (unlockModal) unlockModal.classList.add('hidden');
    };

    // ── Generate & Unlock ──
    window.doGenerate = async () => {
        const btn = document.getElementById('gen-btn');
        if (!btn) return;

        const originalHTML = btn.innerHTML;
        window.api.setButtonLoading(btn, true);

        try {
            const response = await window.api.post(`/users/locked/${userId}/unlock`, {});
            if (response.success || response.status === 'success') {
                const pass = response.temp_password || response.data?.temp_password || 'N/A';

                // Close modal
                if (unlockModal) unlockModal.classList.add('hidden');

                // Show temp pass card
                const displayPass = document.getElementById('display-pass');
                if (displayPass) displayPass.textContent = pass;

                const unlockActionCard = document.getElementById('unlock-action-card');
                if (unlockActionCard) unlockActionCard.classList.add('hidden');

                const tempPassCard = document.getElementById('temp-pass-card');
                if (tempPassCard) tempPassCard.classList.remove('hidden');

                window.FinzoUtils?.showToast('Account unlocked successfully!', 'success');

                // Reload the detail to reflect new status
                setTimeout(() => loadDetail(), 500);
            } else {
                window.FinzoUtils?.showToast(response.message || 'Unlock failed', 'error');
                window.api.setButtonLoading(btn, false, originalHTML);
            }
        } catch (error) {
            console.error('Unlock error:', error);
            window.FinzoUtils?.showToast('Action failed. Please try again.', 'error');
            window.api.setButtonLoading(btn, false, originalHTML);
        }
    };

    // ── Copy to clipboard ──
    window.copyDisplayPass = () => {
        const displayPass = document.getElementById('display-pass');
        if (!displayPass) return;

        navigator.clipboard.writeText(displayPass.textContent).then(() => {
            window.FinzoUtils?.showToast('PIN copied to clipboard', 'success');
        }).catch(() => {
            // Fallback
            const range = document.createRange();
            range.selectNodeContents(displayPass);
            const sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);
            document.execCommand('copy');
            sel.removeAllRanges();
            window.FinzoUtils?.showToast('PIN copied to clipboard', 'success');
        });
    };

    // ── Init ──
    loadDetail();
});
