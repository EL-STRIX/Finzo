/**
 * Finzo Admin — User Detail
 */
document.addEventListener('DOMContentLoaded', () => {
    loadUserData();
});

async function loadUserData() {
    const urlParams = new URLSearchParams(window.location.search);
    const userId = urlParams.get('id');
    
    if (!userId) {
        alert('User ID is required');
        return;
    }

    try {
        const data = await window.api.get(`/admin/user-detail.php?id=${userId}`);
        if (data.status === 'success' || data.success) {
            const user = data.data || data.user;
            
            // Update Header & Avatar
            document.getElementById('user-name-title').textContent = user.name || user.full_name;
            const topbarTitle = document.querySelector('.admin-topbar .force-black.tracking-tight');
            if (topbarTitle) topbarTitle.textContent = user.name || user.full_name;
            
            const adminAvatar = document.getElementById('admin-user-avatar');
            if (adminAvatar) adminAvatar.textContent = user.initials;
            const placeholder = document.getElementById('user-avatar-placeholder');
            if (placeholder) placeholder.textContent = user.initials;

            // Update Info
            document.getElementById('info-full-name').textContent = user.name || user.full_name;
            document.getElementById('info-dob').textContent = user.dob;
            document.getElementById('info-gender').textContent = user.gender;
            document.getElementById('info-father-name').textContent = user.father_name;
            document.getElementById('info-occupation').textContent = user.occupation;
            document.getElementById('info-income').textContent = user.annual_income;
            document.getElementById('info-phone').textContent = user.phone;
            document.getElementById('info-email').textContent = user.email;
            document.getElementById('info-aadhaar').textContent = user.aadhaar;
            document.getElementById('info-account-type').textContent = user.account_type;
            document.getElementById('info-submitted').textContent = user.submitted_at;
            document.getElementById('info-registered').textContent = user.registered_at;
            
            const addressEl = document.querySelector('.info-value.text-sm.leading-tight');
            if(addressEl) addressEl.textContent = user.address;

            // Update Status
            const statusColors = {
                'Active': 'emerald',
                'Suspended': 'orange',
                'Pending': 'blue'
            };
            const color = statusColors[user.status] || 'slate';
            const badge = document.getElementById('user-status-badge');
            badge.className = `text-[10px] font-black text-${color}-50 px-2 py-0.5 bg-${color}-500 rounded-md uppercase tracking-widest border border-${color}-100 dark:border-${color}-500/20`;
            badge.textContent = `Account ${user.status}`;
            
            const tbContainer = document.getElementById('topbar-status-container');
            if (tbContainer) {
                tbContainer.className = `hidden sm:flex items-center gap-2 px-4 py-2 bg-${color}-50 dark:bg-${color}-500/10 border border-${color}-100 dark:border-${color}-500/20 rounded-full`;
                const span = tbContainer.querySelector('span');
                if (span) span.textContent = `${user.status} Account`;
                const dot = tbContainer.querySelector('.w-2');
                if (dot) dot.className = `w-2 h-2 bg-${color}-500 rounded-full animate-pulse`;
            }

            // Update Sidebar Accounts
            if (user.bank_accounts && user.bank_accounts.length > 0) {
                const accountsHtml = user.bank_accounts.map(acc => `
                    <div class="p-5 bg-slate-50 dark:bg-white/5 rounded-2xl border border-transparent hover:border-primary/20 transition-all cursor-pointer group">
                    <div class="flex justify-between items-start mb-6">
                        <div class="h-8 w-8 bg-white dark:bg-slate-800 rounded-lg flex items-center justify-center text-primary dark:text-white shadow-sm group-hover:bg-primary group-hover:text-white transition-all">
                        <span class="material-symbols-outlined text-lg">account_balance_wallet</span>
                        </div>
                        <span class="status-badge !text-[9px] !px-2 !py-0.5">${acc.type}</span>
                    </div>
                    <h4 class="font-headline text-xl font-black text-black dark:text-white mt-0.5">${acc.balance}</h4>
                    <div class="mt-3 flex items-center gap-2 text-[9px] text-slate-400 font-mono font-bold">
                        <span>${acc.account_number}</span>
                        <span class="material-symbols-outlined text-[12px] opacity-40">content_copy</span>
                    </div>
                    </div>
                `).join('');
                document.getElementById('accounts-sidebar-list').innerHTML = accountsHtml;
            } else {
                 document.getElementById('accounts-sidebar-list').innerHTML = '<div class="text-[11px] text-slate-400">No bank accounts</div>';
            }

            // Update Transactions
            renderUserTransactions(user.recent_transactions);
        } else {
             window.FinzoUtils.showToast(data.message || 'Error loading details', 'error');
        }
    } catch (error) {
        console.error('Failed to load user data:', error);
    }
}

let currentMode = '';
const reasonsMap = {
    block: {
        title: 'Suspend Account',
        icon: 'block',
        color: 'orange',
        btnClass: '!bg-orange-600 shadow-orange-600/20',
        options: [
            'KYC Documentation Expired/Invalid',
            'Suspicious UPI Transaction Patterns',
            'Regulatory Compliance Hold (RBI)',
            'Multiple Failed Auth Attempts',
            'Non-Compliance with IRDAI',
            'Legal/Account Dispute'
        ]
    },
    delete: {
        title: 'Archive Portfolio',
        icon: 'delete_forever',
        color: 'rose',
        btnClass: '!bg-rose-600 shadow-rose-600/20',
        options: [
            'User Requested Account Closure',
            'Anti-Money Laundering (AML) Flag',
            'Duplicated System Identity',
            'Fraudulent Document Discovery',
            'Irrecoverable Risk Breach',
            'Death of Account Holder'
        ]
    }
};

window.openGovernanceModal = (mode) => {
    currentMode = mode;
    const config = reasonsMap[mode];
    
    document.getElementById('modal-title').textContent = config.title;
    document.getElementById('modal-icon').textContent = config.icon;
    document.getElementById('modal-icon-container').className = `w-16 h-16 rounded-full mx-auto flex items-center justify-center mb-4 bg-${config.color}-50 dark:bg-${config.color}-500/10 text-${config.color}-500 shadow-lg`;
    document.getElementById('confirm-governance-btn').className = `flex-1 py-4 text-white font-black rounded-2xl text-[11px] uppercase tracking-widest shadow-lg hover:scale-[1.02] active:scale-[0.98] transition-all ${config.btnClass}`;
    
    let html = '';
    config.options.forEach((reason) => {
        html += `
            <label class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-white/5 rounded-xl border-2 border-transparent hover:border-slate-200 dark:hover:border-white/10 cursor-pointer transition-all group">
                <input type="radio" name="reason" value="${reason}" class="text-primary focus:ring-primary/10">
                <span class="text-[11px] font-black text-slate-500 group-hover:text-black dark:group-hover:text-white leading-tight">${reason}</span>
            </label>
        `;
    });
    html += `
        <label class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-white/5 rounded-xl border-2 border-transparent hover:border-slate-200 dark:hover:border-white/10 cursor-pointer transition-all group lg:col-span-2">
            <input type="radio" name="reason" value="Other" class="text-primary focus:ring-primary/10">
            <span class="text-[11px] font-black text-slate-500 group-hover:text-black dark:group-hover:text-white">Other / Custom Investigation</span>
        </label>
    `;
    
    document.getElementById('reason-grid').innerHTML = html;
    
    document.querySelectorAll('input[name="reason"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const container = document.getElementById('custom-reason-container');
            if (this.value === 'Other') {
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
            }
        });
    });

    const modal = document.getElementById('governance-modal');
    modal.classList.remove('hidden');
    modal.style.display = 'block';
    document.body.classList.add('overflow-hidden');
};

window.closeGovernanceModal = () => {
    const modal = document.getElementById('governance-modal');
    modal.classList.add('hidden');
    modal.style.display = '';
    document.body.classList.remove('overflow-hidden');
    document.getElementById('admin-password').value = '';
    document.getElementById('auth-error').classList.add('hidden');
    document.getElementById('custom-reason-container').classList.add('hidden');
};

window.executeGovernanceAction = async () => {
    const pass = document.getElementById('admin-password').value;
    const checkedReason = document.querySelector('input[name="reason"]:checked');
    const reason = checkedReason ? checkedReason.value : null;
    const userId = new URLSearchParams(window.location.search).get('id');
    
    if (!reason) { alert("Please select a primary reason for this action."); return; }

    try {
        const response = await window.api.post('/admin/user-detail.php', {
            action: currentMode,
            id: userId,
            reason: reason,
            notes: document.getElementById('governance-notes').value,
            password: pass
        });

        if (response.status === 'success' || response.success) {
            document.getElementById('confirm-governance-btn').textContent = 'SUCCESS! AUDIT LOGGED';
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            const errorEl = document.getElementById('auth-error');
            errorEl.textContent = response.message;
            errorEl.classList.remove('hidden');
        }
    } catch (error) {
        alert('Action failed. Please try again.');
    }
};

function renderUserTransactions(transactions) {
    if (!transactions || transactions.length === 0) {
        document.getElementById('user-transactions-body').innerHTML = '<tr><td colspan="7" class="py-10 text-center opacity-40">No recent activities found</td></tr>';
        return;
    }

    const html = transactions.map((t, i) => {
        const isPositive = t.amount && t.amount.startsWith('+');
        const isSpecial = t.rawType === 'special';
        
        return \`
        <tr class="hover:bg-slate-50/50 dark:hover:bg-white/5 transition-all group">
            <td class="py-5 px-8 text-center font-black opacity-20 text-[10px] group-hover:opacity-100 transition-opacity">\${i + 1}</td>
            <td class="py-5 px-6">
            <div class="font-mono text-[11px] font-black tracking-widest text-primary dark:text-blue-400 bg-primary/5 dark:bg-blue-400/5 px-3 py-1.5 rounded-lg w-fit">
                \${t.txId}
            </div>
            </td>
            <td class="py-5 px-6">
            <div class="flex items-center gap-2">
                <div class="w-1.5 h-1.5 rounded-full \${t.rawType === 'deposit' ? 'bg-emerald-500' : t.rawType === 'withdrawal' ? 'bg-rose-500' : t.rawType === 'transfer' ? 'bg-blue-500' : 'bg-slate-400 animate-pulse'}"></div>
                <span class="text-[11px] font-black uppercase tracking-widest text-slate-600 dark:text-slate-300">\${t.type ? t.type.replace('_', ' ') : ''}</span>
            </div>
            </td>
            <td class="py-5 px-6">
            <div class="text-[11px] font-bold text-slate-500">\${t.date}</div>
            </td>
            <td class="py-5 px-6 text-right">
            <div class="text-sm font-black \${isPositive ? 'text-emerald-500' : isSpecial ? 'text-slate-400' : 'text-rose-500'} font-mono">
                \${t.amount}
            </div>
            </td>
            <td class="py-5 px-6">
            <span class="badge \${t.rawType === 'deposit' ? 'badge-green' : t.rawType === 'withdrawal' ? 'badge-red' : t.rawType === 'transfer' ? 'badge-blue' : 'badge-gray'} !text-[9px] !px-3 !py-1 !rounded-full !border-none !font-black !tracking-tighter shadow-sm">
                \${t.status}
            </span>
            </td>
            <td class="py-5 px-8 text-right">
            <button class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100 dark:hover:bg-white/10 hover:text-primary dark:hover:text-blue-400 transition-all">
                <span class="material-symbols-outlined text-[18px]">print</span>
            </button>
            </td>
        </tr>
        \`;
    }).join('');
    document.getElementById('user-transactions-body').innerHTML = html;
}
