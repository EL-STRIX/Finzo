/**
 * Finzo Admin — Locked Account Detail
 * Handles displaying details, unlocking accounts, and audit logs
 */

document.addEventListener('DOMContentLoaded', () => {
    // Session Verification
    if (!window.AdminPortal || !window.AdminPortal.state.isLoaded) {
        // Handled by portals/admin.js or requires manual init
    }

    const urlParams = new URLSearchParams(window.location.search);
    const userId = urlParams.get('id');

    if (!userId) {
        alert('User ID missing');
        window.location.href = 'admin-locked-accounts.html';
        return;
    }

    async function loadDetail() {
        try {
            const response = await window.api.get(`/admin/locked-account-detail.php?id=${userId}`);
            if (response.status === 'success' || response.success) { // Handle both cases based on API output
                const user = response.data ? response.data.user : response.user;
                const logs = response.data ? response.data.logs : response.logs;
                populateUI(user);
                renderAuditLogs(logs);
            } else {
                window.FinzoUtils.showToast(response.message || 'Failed to load details', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            window.FinzoUtils.showToast('Network error loading details', 'error');
        }
    }

    function populateUI(user) {
        // Breadcrumb removed
        
        const avatarEl = document.getElementById('d-avatar');
        if (avatarEl) {
            avatarEl.textContent = user.initials || '??';
            avatarEl.className = 'profile-avatar premium-gradient-primary';
            avatarEl.style.background = '';
        }
        
        const setEl = (id, val) => { const el = document.getElementById(id); if(el) el.textContent = val; };
        
        setEl('d-name', user.full_name || 'N/A');
        setEl('d-email', user.email || 'N/A');
        setEl('d-acc-no', user.account_no || 'N/A');
        setEl('d-phone', user.phone || 'N/A');
        setEl('d-acc-type', user.account_type ? (user.account_type + ' Account') : 'N/A');
        
        const typeBadge = document.getElementById('d-acc-type-badge');
        if (typeBadge) {
            typeBadge.textContent = user.account_type || 'N/A';
            typeBadge.className = 'badge badge-green';
        }
        
        setEl('d-opened', user.opened_at || 'N/A');
        
        const balance = parseFloat(user.balance || 0).toLocaleString('en-IN', { style: 'currency', currency: 'INR' });
        setEl('d-balance', balance);
        
        const isLocked = user.is_locked;
        const mainBadge = document.getElementById('d-main-badge');
        if (mainBadge) {
            mainBadge.textContent = isLocked ? 'Locked' : 'Unlocked';
            mainBadge.className = `badge ${isLocked ? 'badge-red' : 'badge-green'}`;
        }
        
        // Lock Info
        setEl('d-locked-date', user.locked_on_date || user.locked_at || 'N/A');
        setEl('d-lock-time', user.locked_on_ts || user.lock_time || 'N/A');
        setEl('d-locked-by', user.locked_by || 'System');
        setEl('d-ip', user.ip_address || 'N/A');
        setEl('d-device', user.device || 'N/A');
        
        const hasPass = user.temp_password_active;
        const tempStatus = document.getElementById('d-temp-status');
        if (tempStatus) {
            tempStatus.innerHTML = hasPass ? '<span class="badge badge-green">Generated</span>' : '<span class="badge badge-amber">Not Generated</span>';
        }
        
        const tempPassCard = document.getElementById('temp-pass-card');
        const unlockActionCard = document.getElementById('unlock-action-card');
        
        if (hasPass && user.temp_password) {
            if(tempPassCard) tempPassCard.classList.remove('hidden');
            if(unlockActionCard) unlockActionCard.classList.add('hidden');
            document.getElementById('display-pass').textContent = user.temp_password;
        }

        const modalName = document.getElementById('modal-name');
        if (modalName) modalName.textContent = user.full_name;
        
        const modalAcc = document.getElementById('modal-acc');
        if (modalAcc) modalAcc.textContent = user.account_no || 'N/A';
    }

    function renderAuditLogs(logs) {
        const tbody = document.getElementById('audit-logs-body');
        if (!logs || Object.keys(logs).length === 0 || logs.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3" class="text-center py-8 opacity-40">No failed attempts logged</td></tr>';
            return;
        }
        
        const html = logs.map(log => `
            <tr>
                <td class="ip-cell">${log.ip}</td>
                <td class="time-cell">${log.time}</td>
                <td class="outcome-cell"><span class="badge ${log.outcome === 'Block' ? 'badge-red' : 'badge-amber'}">${log.outcome}</span></td>
            </tr>
        `).join('');
        tbody.innerHTML = html;
    }

    // Modal elements
    const unlockModal = document.getElementById('unlock-modal');
    
    // Bind global buttons
    const copyIconsBtn = document.querySelectorAll('button[onclick="copyDisplayPass()"], button[onclick="copyModalPass()"]');
    if (copyIconsBtn.length > 0) {
        // They were using inline handlers. We'll find them and bind.
        // Actually best is to just remove the inline onclicks in HTML and select by ID.
    }

    // Export functions strictly needed for inline handlers left or bind them here
    window.openUnlockModal = () => unlockModal.classList.remove('hidden');
    window.closeModal = () => unlockModal.classList.add('hidden');

    window.doGenerate = async () => {
        const btn = document.getElementById('gen-btn');
        const originalText = btn.innerHTML;
        window.api.setButtonLoading(btn, true);
        
        try {
            const response = await window.api.post('/admin/unlock-account.php', { id: userId });
            if (response.status === 'success' || response.success) {
                const pass = response.temp_password || response.data?.temp_password;
                
                const iconBox = document.getElementById('modal-icon-box');
                iconBox.style.background = 'var(--green-soft)';
                iconBox.classList.add('success-pop');
                
                const icon = document.getElementById('modal-icon');
                icon.textContent = 'task_alt';
                icon.style.color = 'var(--green)';
                
                const title = document.getElementById('modal-title');
                title.textContent = 'Account Unlocked';
                title.style.color = 'var(--green)';
                
                document.getElementById('modal-desc').textContent = 'Access restored. Temporary password generated.';
                
                document.getElementById('modal-pass-val').textContent = pass;
                document.getElementById('modal-pass-area').classList.remove('hidden');
                document.getElementById('modal-action-1').classList.add('hidden');
                document.getElementById('modal-action-2').classList.remove('hidden');
                
                document.getElementById('display-pass').textContent = pass;
                document.getElementById('unlock-action-card').classList.add('hidden');
                document.getElementById('temp-pass-card').classList.remove('hidden');
                
                window.FinzoUtils.showToast('Account unlocked successfully', 'success');
                loadDetail();
            } else {
                alert(response.message);
                window.api.setButtonLoading(btn, false, '<span class="material-symbols-outlined" style="font-size:15px">key</span> Generate & Unlock');
            }
        } catch (error) {
            alert('Action failed.');
            window.api.setButtonLoading(btn, false, '<span class="material-symbols-outlined" style="font-size:15px">key</span> Generate & Unlock');
        }
    };

    window.copyModalPass = () => {
        const pass = document.getElementById('modal-pass-val').textContent;
        navigator.clipboard.writeText(pass).then(() => {
            const icon = document.getElementById('modal-copy-icon');
            icon.textContent = 'check';
            icon.style.color = 'var(--green)';
            setTimeout(() => { icon.textContent = 'content_copy'; icon.style.color = ''; }, 2000);
        });
    };

    window.copyDisplayPass = () => {
        const pass = document.getElementById('display-pass').textContent;
        navigator.clipboard.writeText(pass).then(() => {
            const icon = document.getElementById('copy-icon2');
            icon.textContent = 'check';
            icon.style.color = 'var(--green)';
            setTimeout(() => { icon.textContent = 'content_copy'; icon.style.color = ''; }, 2000);
        });
    };

    // Load initial data
    loadDetail();
});
