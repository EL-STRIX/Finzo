/**
 * Finzo User Portal — Security & 2FA Control Center Handler
 */

document.addEventListener('DOMContentLoaded', async () => {
    buildSidebar('security');
    buildNavbar('Account & Privacy', 'Security & 2FA');
    await fetchUserSession();
    await loadSecurityOverview();
});

async function loadSecurityOverview() {
    try {
        const res = await window.api.get('/user/security/overview');
        if (res.success && res.security) {
            renderSecurityOverview(res.security);
        }
    } catch (e) {
        console.error('Failed to load security overview:', e);
    }
}

function renderSecurityOverview(sec) {
    const toggle2FA = document.getElementById('2fa-toggle');
    if (toggle2FA) toggle2FA.checked = !!sec.two_factor_enabled;

    const sessionsContainer = document.getElementById('sessions-container');
    if (!sessionsContainer) return;

    const sessions = sec.sessions || [];
    if (!sessions.length) {
        sessionsContainer.innerHTML = '<div class="py-8 text-center text-text-secondary text-xs font-bold uppercase tracking-wider">No active sessions.</div>';
        return;
    }

    sessionsContainer.innerHTML = sessions.map((s, idx) => {
        const lastActive = s.last_activity ? new Date(s.last_activity).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'Just Now';
        const isCurrent = idx === 0;

        return `
        <div class="p-4 rounded-xl border border-border-subtle bg-surface/50 hover:bg-surface transition-all flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-xl">devices</span>
                </div>
                <div>
                    <div class="font-bold text-sm text-text-primary flex items-center gap-2">
                        <span>${s.device_name || 'Browser Session'}</span>
                        ${isCurrent ? '<span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold uppercase text-[9px]">THIS DEVICE</span>' : ''}
                    </div>
                    <div class="text-xs text-text-secondary font-mono">IP: ${s.ip_address} • Active: ${lastActive}</div>
                </div>
            </div>

            ${!isCurrent ? `
            <button onclick="revokeSession(${s.id})" class="btn btn-secondary btn-xs text-rose-500 hover:bg-rose-500/10">
                <span>Revoke</span>
            </button>
            ` : ''}
        </div>
        `;
    }).join('');
}

async function toggle2FA(checkbox) {
    const enable = checkbox.checked;
    try {
        const formData = new FormData();
        formData.append('enable', enable ? 1 : 0);

        const res = await window.api.post('/user/security/2fa/toggle', formData, true);
        if (res.success) {
            showToast(res.message || `2FA ${enable ? 'enabled' : 'disabled'}.`, 'success');
        } else {
            showToast(res.message || 'Failed to update 2FA.', 'error');
            checkbox.checked = !enable;
        }
    } catch (e) {
        showToast('Connection failed.', 'error');
        checkbox.checked = !enable;
    }
}

async function submitSetPin(e) {
    e.preventDefault();
    const pin = document.getElementById('sec-pin').value;
    if (!pin || pin.length !== 4) {
        showToast('PIN must be 4 digits.', 'error');
        return;
    }

    try {
        const formData = new FormData();
        formData.append('pin', pin);

        const res = await window.api.post('/user/security/pin/set', formData, true);
        if (res.success) {
            showToast(res.message || 'Transaction PIN updated!', 'success');
            document.getElementById('set-pin-form').reset();
        } else {
            showToast(res.message || 'Failed to set PIN.', 'error');
        }
    } catch (e) {
        console.error('PIN set error:', e);
        showToast('Connection failed.', 'error');
    }
}

async function revokeSession(sessionId) {
    if (!confirm('Are you sure you want to revoke this session?')) return;

    try {
        const formData = new FormData();
        formData.append('session_id', sessionId);

        const res = await window.api.post('/user/security/sessions/revoke', formData, true);
        if (res.success) {
            showToast(res.message || 'Session revoked.', 'success');
            await loadSecurityOverview();
        } else {
            showToast(res.message || 'Failed to revoke session.', 'error');
        }
    } catch (e) {
        console.error('Revoke session error:', e);
        showToast('Connection failed.', 'error');
    }
}
