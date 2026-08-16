/**
 * Finzo Admin — User Dossier & Details
 */

(function() {
    let currentMode = '';
    let currentUserId = '';
    let currentUserData = null;
    let currentUserAccounts = [];
    let currentUserTransactions = [];

    const reasonsMap = {
        block: {
            title: 'Suspend User Account',
            icon: 'block',
            color: 'warning',
            btnClass: '!bg-warning hover:!bg-amber-600',
            options: [
                'KYC Documentation Expired/Invalid',
                'Suspicious UPI/Transfer Patterns',
                'Regulatory Compliance Hold (RBI)',
                'Multiple Failed Authentication Attempts',
                'Non-Compliance with Anti-Fraud Policy',
                'Legal or Account Ownership Dispute'
            ]
        },
        delete: {
            title: 'Soft Delete User Account',
            icon: 'delete_forever',
            color: 'error',
            btnClass: '!bg-error hover:!bg-rose-700',
            options: [
                'User Requested Account Closure',
                'Anti-Money Laundering (AML) Flag',
                'Duplicated System Identity',
                'Fraudulent Document Discovery',
                'Irrecoverable Risk Breach',
                'Deceased Account Holder'
            ]
        }
    };

    /**
     * Load all user details from API
     */
    async function loadUserData() {
        const urlParams = new URLSearchParams(window.location.search);
        currentUserId = urlParams.get('id');
        
        if (!currentUserId) {
            console.error('User ID is required in URL query string');
            return;
        }

        try {
            const response = await window.api.get(`/users/${currentUserId}`);
            
            if (response && (response.success || response.status === 'success' || response.status === 200)) {
                const data = response.data || response;
                const user = data.user;
                
                if (!user) {
                    console.error('User object missing in response:', response);
                    return;
                }

                currentUserData = user;
                currentUserAccounts = data.accounts || [];
                currentUserTransactions = data.transactions || [];
                const idDocuments = data.id_documents || {};

                renderUserProfile(user, currentUserAccounts, data.avatar_url);
                renderBankAccounts(currentUserAccounts);
                renderDocuments(user, idDocuments);
                renderTransactions(currentUserTransactions, currentUserAccounts);
                bindActionButtons(user);

            } else {
                if (window.showToast) window.showToast(response.message || 'Error loading user details', 'error');
            }
        } catch (error) {
            console.error('Failed to load user data:', error);
        }
    }

    /**
     * Format dates cleanly
     */
    function formatDateTime(dateStr) {
        if (!dateStr) return 'Never / N/A';
        try {
            const d = new Date(dateStr);
            if (isNaN(d.getTime())) return dateStr;
            return d.toLocaleDateString('en-GB', { 
                day: '2-digit', 
                month: 'short', 
                year: 'numeric', 
                hour: '2-digit', 
                minute: '2-digit', 
                hour12: true 
            }).replace(',', '');
        } catch (e) {
            return dateStr;
        }
    }

    /**
     * Safely update text and remove skeletons
     */
    function setText(id, text) {
        const el = document.getElementById(id);
        if (el) {
            el.textContent = text || 'N/A';
            el.querySelectorAll('.skeleton').forEach(s => s.remove());
        }
    }

    /**
     * Render user profile data
     */
    function renderUserProfile(user, accounts, avatarUrl) {
        const fullName = user.full_name || 'User';
        const initials = fullName.split(' ').filter(Boolean).map(n => n ? n[0] : '').join('').toUpperCase() || 'U';
        const gradient = window.FinzoUtils && window.FinzoUtils.getAvatarGradient ? window.FinzoUtils.getAvatarGradient(initials) : 'linear-gradient(135deg, #10b981, #059669)';

        // Topbar: ONLY the user's full name
        setText('topbar-user-name', fullName);
        setText('user-name-title', fullName);

        // Avatar
        const avatarContainer = document.getElementById('user-avatar-placeholder');
        if (avatarContainer) {
            const photoUrl = avatarUrl || (user.profile_picture ? (user.profile_picture.startsWith('http') ? user.profile_picture : `/Finzo/${user.profile_picture}`) : null);
            if (photoUrl) {
                avatarContainer.innerHTML = `<img src="${photoUrl}" class="w-full h-full object-cover" alt="${fullName}" onerror="this.outerHTML='<div class=\\'w-full h-full flex items-center justify-center text-3xl font-black text-white\\' style=\\'background:${gradient}\\'>${initials}</div>'">`;
            } else {
                avatarContainer.innerHTML = `<div class="w-full h-full flex items-center justify-center text-3xl font-black text-white" style="background:${gradient}">${initials}</div>`;
            }
        }

        // Status Indicators
        const userStatus = (user.status || 'pending').toLowerCase();
        const statusConfig = {
            'active': { text: 'Active Account', color: 'emerald', badgeClass: 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20' },
            'suspended': { text: 'Suspended Account', color: 'amber', badgeClass: 'bg-amber-500/10 text-amber-500 border-amber-500/20' },
            'pending': { text: 'Pending Verification', color: 'blue', badgeClass: 'bg-blue-500/10 text-blue-500 border-blue-500/20' },
            'under_review': { text: 'Under Review', color: 'purple', badgeClass: 'bg-purple-500/10 text-purple-500 border-purple-500/20' },
            'rejected': { text: 'Rejected Account', color: 'rose', badgeClass: 'bg-rose-500/10 text-rose-500 border-rose-500/20' }
        };
        const cfg = statusConfig[userStatus] || { text: userStatus.toUpperCase(), color: 'slate', badgeClass: 'bg-surface text-text-secondary border-border-subtle' };

        const statusBadge = document.getElementById('user-status-badge');
        if (statusBadge) {
            statusBadge.className = `text-[10px] font-black px-2.5 py-1 rounded-md uppercase tracking-widest border ${cfg.badgeClass}`;
            statusBadge.textContent = cfg.text;
        }

        // Account Type Badge: "Savings Account" or "Current Account"
        const primaryAcc = accounts[0] || {};
        const rawType = (primaryAcc.account_type || user.account_type || 'savings').toLowerCase();
        const accountTypeFormatted = rawType.includes('current') ? 'Current Account' : (rawType.includes('fixed') ? 'Fixed Deposit' : 'Savings Account');
        
        const accTypeBadge = document.getElementById('user-account-type-badge');
        if (accTypeBadge) {
            accTypeBadge.textContent = accountTypeFormatted;
            accTypeBadge.className = 'text-[10px] font-black px-2.5 py-1 rounded-md uppercase tracking-widest bg-primary/10 text-primary border border-primary/20';
        }

        // Basic Info
        setText('info-customer-id', user.customer_id || 'FNZ-USR-PENDING');
        setText('info-full-name', fullName);
        setText('info-dob', user.dob || 'N/A');
        setText('info-gender', user.gender ? (user.gender.charAt(0).toUpperCase() + user.gender.slice(1)) : 'N/A');
        setText('info-marital-status', user.marital_status ? (user.marital_status.charAt(0).toUpperCase() + user.marital_status.slice(1)) : 'N/A');
        setText('info-nationality', user.nationality || 'Indian');

        // Family & Employment
        setText('info-father-name', user.father_name || 'N/A');
        setText('info-mother-name', user.mother_name || 'N/A');
        setText('info-occupation', user.occupation || 'N/A');
        setText('info-income', user.annual_income ? (user.annual_income.startsWith('₹') ? user.annual_income : `₹${user.annual_income}`) : 'N/A');
        setText('info-source-income', user.source_of_income || 'N/A');

        // Contact
        setText('info-phone', user.phone || 'N/A');
        setText('info-email', user.email || 'N/A');

        // Identification
        const idType = user.id_type ? user.id_type.toUpperCase() : 'AADHAAR';
        const idNumber = user.id_number || 'N/A';
        setText('info-aadhaar', `${idType}: ${idNumber}`);

        // Registration & Access Meta
        setText('info-submitted', formatDateTime(user.kyc_submitted_at));
        setText('info-registered', formatDateTime(user.created_at));
        setText('info-updated-at', formatDateTime(user.updated_at));
        setText('info-last-login', formatDateTime(user.last_login_at));
        setText('info-login-ip', user.last_login_ip || 'N/A');
        setText('info-failed-attempts', user.failed_attempts !== undefined ? String(user.failed_attempts) : '0');
        setText('info-locked-until', user.locked_until ? formatDateTime(user.locked_until) : 'Not Locked');

        // Rejection Reason
        if (userStatus === 'rejected' && user.rejection_reason) {
            const rejContainer = document.getElementById('rejection-reason-container');
            if (rejContainer) {
                rejContainer.classList.remove('hidden');
                setText('info-rejection-reason', user.rejection_reason);
            }
        }

        // Residential Address
        const addressParts = [user.street, user.city, user.state, user.pincode, user.country].filter(Boolean);
        setText('info-address', addressParts.length > 0 ? addressParts.join(', ') : 'No residential address registered.');

        // Clean up any remaining skeletons
        document.querySelectorAll('.skeleton').forEach(s => {
            if (s.closest('.ds-card') && !s.closest('#accounts-sidebar-list') && !s.closest('#documents-grid') && !s.closest('#user-transactions-body')) {
                s.remove();
            }
        });
    }

    /**
     * Render bank accounts
     */
    function renderBankAccounts(accounts) {
        const countBadge = document.getElementById('accounts-count-badge');
        if (countBadge) countBadge.textContent = `${accounts.length} Account${accounts.length === 1 ? '' : 's'}`;

        const container = document.getElementById('accounts-sidebar-list');
        if (!container) return;

        if (!accounts || accounts.length === 0) {
            container.innerHTML = `
                <div class="p-6 bg-background rounded-2xl border border-border-subtle text-center">
                    <span class="material-symbols-outlined text-3xl text-text-secondary/40 mb-2" aria-hidden="true">account_balance</span>
                    <p class="text-xs font-bold text-text-secondary uppercase tracking-wider">No Bank Accounts Found</p>
                </div>
            `;
            return;
        }

        let html = '';
        accounts.forEach(acc => {
            const balanceNum = parseFloat(acc.balance || 0);
            const balanceFormatted = '₹' + balanceNum.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            const typeFormatted = acc.account_type ? (acc.account_type.charAt(0).toUpperCase() + acc.account_type.slice(1)) : 'Savings';
            const isActive = (acc.status || 'active') === 'active';
            const statusClass = isActive ? 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20' : 'bg-surface text-text-secondary border-border-subtle';

            html += `
                <div class="p-5 bg-background rounded-2xl border border-border-subtle hover:border-primary/40 transition-all shadow-sm group">
                    <div class="flex justify-between items-start mb-4">
                        <div class="h-9 w-9 bg-surface rounded-xl flex items-center justify-center text-primary border border-border-subtle shadow-sm group-hover:bg-primary group-hover:text-white transition-all">
                            <span class="material-symbols-outlined text-lg" aria-hidden="true">account_balance_wallet</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-[9px] font-bold px-2 py-0.5 rounded bg-surface border border-border-subtle text-text-secondary uppercase tracking-wider">${typeFormatted}</span>
                            <span class="text-[9px] font-black px-2 py-0.5 rounded uppercase tracking-wider border ${statusClass}">${acc.status || 'ACTIVE'}</span>
                        </div>
                    </div>
                    <div class="text-xs font-bold text-text-secondary uppercase tracking-widest">Available Balance</div>
                    <h4 class="font-headline text-2xl font-black text-text-primary tracking-tight mt-0.5 font-mono">${balanceFormatted}</h4>
                    
                    <div class="mt-4 pt-3 border-t border-border-subtle flex items-center justify-between text-[10px] font-mono text-text-secondary font-bold">
                        <div class="flex items-center gap-1.5 cursor-pointer hover:text-primary transition-colors" onclick="navigator.clipboard.writeText('${acc.account_no}'); if(window.showToast) window.showToast('Account number copied!', 'success');" title="Click to copy">
                            <span>${acc.account_no || 'Pending'}</span>
                            <span class="material-symbols-outlined text-[13px] opacity-60" aria-hidden="true">content_copy</span>
                        </div>
                        <div>Interest: ${acc.interest_rate || '0'}%</div>
                    </div>
                    <div class="mt-2 flex items-center justify-between text-[10px] font-mono text-text-secondary opacity-75">
                        <div>Daily Limit: ₹${parseFloat(acc.daily_limit || 0).toLocaleString('en-IN')}</div>
                        <div>Opened: ${acc.opened_date ? acc.opened_date.split(' ')[0] : 'N/A'}</div>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    /**
     * Render KYC and Identity Documents
     */
    function renderDocuments(user, idDocuments) {
        const idType = user.id_type ? (user.id_type.charAt(0).toUpperCase() + user.id_type.slice(1)) : 'Aadhaar';
        const idNumber = user.id_number || 'N/A';
        const isVerified = (user.status === 'active');

        // Document Meta Info in Card Header
        const metaContainer = document.getElementById('doc-meta-info');
        if (metaContainer) {
            metaContainer.textContent = `${idType} Card: ${idNumber}`;
        }

        const verifiedBadge = document.getElementById('doc-verified-badge');
        if (verifiedBadge) {
            if (isVerified) {
                verifiedBadge.className = 'px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider rounded-md bg-emerald-500/10 text-emerald-500 border border-emerald-500/20';
                verifiedBadge.textContent = 'Verified ID';
            } else {
                verifiedBadge.className = 'px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider rounded-md bg-amber-500/10 text-amber-500 border border-amber-500/20';
                verifiedBadge.textContent = 'Pending Review';
            }
        }

        // Front Document Card
        const frontUrl = idDocuments.front_url || (user.id_front_path ? (user.id_front_path.startsWith('http') ? user.id_front_path : `/Finzo/${user.id_front_path}`) : null);
        const frontPreview = document.getElementById('front-doc-preview');
        const frontActions = document.getElementById('front-doc-actions');
        const frontFilename = document.getElementById('front-doc-filename');
        const frontStatus = document.getElementById('front-doc-status');

        if (frontUrl) {
            if (frontFilename) frontFilename.textContent = `${idType}_Front_Document.jpg`;
            if (frontStatus) {
                frontStatus.textContent = 'Uploaded';
                frontStatus.className = 'text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-500 border border-emerald-500/20';
            }
            if (frontPreview) {
                frontPreview.innerHTML = `
                    <img src="${frontUrl}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" alt="Front Document" onerror="this.parentElement.innerHTML='<div class=\\'flex flex-col items-center justify-center p-6 text-center\\'><span class=\\'material-symbols-outlined text-3xl text-text-secondary/40 mb-1\\'>broken_image</span><span class=\\'text-[11px] font-bold text-text-secondary\\'>Image preview unavailable</span></div>'">
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
                        <button class="w-10 h-10 rounded-xl bg-white text-black flex items-center justify-center shadow-lg hover:scale-110 transition-transform" onclick="openLightbox('${frontUrl}', '${idType} - Front Side')" title="Zoom in">
                            <span class="material-symbols-outlined text-xl">zoom_in</span>
                        </button>
                    </div>
                `;
            }
            if (frontActions) {
                frontActions.innerHTML = `
                    <button type="button" class="btn btn-secondary px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider inline-flex items-center gap-1.5" onclick="openLightbox('${frontUrl}', '${idType} - Front Side')">
                        <span class="material-symbols-outlined text-[16px]">visibility</span>
                        <span>Preview</span>
                    </button>
                    <a href="${frontUrl}" download="${idType}_Front.jpg" target="_blank" class="btn btn-primary px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider inline-flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">download</span>
                        <span>Download</span>
                    </a>
                `;
            }
        } else {
            if (frontFilename) frontFilename.textContent = 'No front image uploaded';
            if (frontStatus) {
                frontStatus.textContent = 'Not Provided';
                frontStatus.className = 'text-[10px] font-bold px-2 py-0.5 rounded bg-surface border border-border-subtle text-text-secondary';
            }
            if (frontPreview) {
                frontPreview.innerHTML = `
                    <div class="flex flex-col items-center justify-center p-6 text-center text-text-secondary">
                        <span class="material-symbols-outlined text-3xl opacity-30 mb-2">image_not_supported</span>
                        <p class="text-xs font-bold uppercase tracking-wider">No Front Document</p>
                        <p class="text-[10px] opacity-60 mt-0.5">Physical document copy was not attached</p>
                    </div>
                `;
            }
            if (frontActions) frontActions.innerHTML = '';
        }

        // Back Document Card
        const backUrl = idDocuments.back_url || (user.id_back_path ? (user.id_back_path.startsWith('http') ? user.id_back_path : `/Finzo/${user.id_back_path}`) : null);
        const backPreview = document.getElementById('back-doc-preview');
        const backActions = document.getElementById('back-doc-actions');
        const backFilename = document.getElementById('back-doc-filename');
        const backStatus = document.getElementById('back-doc-status');

        if (backUrl) {
            if (backFilename) backFilename.textContent = `${idType}_Back_Document.jpg`;
            if (backStatus) {
                backStatus.textContent = 'Uploaded';
                backStatus.className = 'text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-500 border border-emerald-500/20';
            }
            if (backPreview) {
                backPreview.innerHTML = `
                    <img src="${backUrl}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" alt="Back Document" onerror="this.parentElement.innerHTML='<div class=\\'flex flex-col items-center justify-center p-6 text-center\\'><span class=\\'material-symbols-outlined text-3xl text-text-secondary/40 mb-1\\'>broken_image</span><span class=\\'text-[11px] font-bold text-text-secondary\\'>Image preview unavailable</span></div>'">
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
                        <button class="w-10 h-10 rounded-xl bg-white text-black flex items-center justify-center shadow-lg hover:scale-110 transition-transform" onclick="openLightbox('${backUrl}', '${idType} - Back Side')" title="Zoom in">
                            <span class="material-symbols-outlined text-xl">zoom_in</span>
                        </button>
                    </div>
                `;
            }
            if (backActions) {
                backActions.innerHTML = `
                    <button type="button" class="btn btn-secondary px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider inline-flex items-center gap-1.5" onclick="openLightbox('${backUrl}', '${idType} - Back Side')">
                        <span class="material-symbols-outlined text-[16px]">visibility</span>
                        <span>Preview</span>
                    </button>
                    <a href="${backUrl}" download="${idType}_Back.jpg" target="_blank" class="btn btn-primary px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider inline-flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">download</span>
                        <span>Download</span>
                    </a>
                `;
            }
        } else {
            if (backFilename) backFilename.textContent = 'No back image uploaded';
            if (backStatus) {
                backStatus.textContent = 'Not Provided';
                backStatus.className = 'text-[10px] font-bold px-2 py-0.5 rounded bg-surface border border-border-subtle text-text-secondary';
            }
            if (backPreview) {
                backPreview.innerHTML = `
                    <div class="flex flex-col items-center justify-center p-6 text-center text-text-secondary">
                        <span class="material-symbols-outlined text-3xl opacity-30 mb-2">image_not_supported</span>
                        <p class="text-xs font-bold uppercase tracking-wider">No Back Document</p>
                        <p class="text-[10px] opacity-60 mt-0.5">Physical document copy was not attached</p>
                    </div>
                `;
            }
            if (backActions) backActions.innerHTML = '';
        }
    }

    /**
     * Render transactions
     */
    function renderTransactions(transactions, accounts) {
        const tbody = document.getElementById('user-transactions-body');
        if (!tbody) return;

        if (!transactions || transactions.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="py-12 text-center text-xs font-bold text-text-secondary uppercase tracking-widest">No recent transactions recorded for this user</td></tr>';
            return;
        }

        const accountNos = accounts.map(a => a.account_no);
        let html = '';

        transactions.forEach((t, i) => {
            const type = t.type || 'transfer';
            let isCredit = (type === 'deposit' || type === 'interest_credit');
            if (type === 'transfer') {
                isCredit = accountNos.includes(t.to_account);
            }

            const amountFormatted = (isCredit ? '+ ' : '- ') + '₹' + parseFloat(t.amount || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 });
            const amountColor = isCredit ? 'text-emerald-500' : 'text-rose-500';
            const status = (t.status || 'completed').toLowerCase();
            const statusClass = status === 'completed' ? 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20' : (status === 'failed' ? 'bg-rose-500/10 text-rose-500 border-rose-500/20' : 'bg-amber-500/10 text-amber-500 border-amber-500/20');

            html += `
                <tr class="hover:bg-surface/50 transition-colors">
                    <td class="w-16 text-center text-xs font-bold text-text-secondary">${i + 1}</td>
                    <td class="font-mono text-xs font-bold text-primary">${t.txn_id || 'TXN-' + t.id}</td>
                    <td>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full ${isCredit ? 'bg-emerald-500' : 'bg-rose-500'}"></span>
                            <span class="text-xs font-bold uppercase tracking-wider text-text-primary">${type.replace('_', ' ')}</span>
                        </div>
                    </td>
                    <td class="text-xs text-text-secondary">${formatDateTime(t.created_at)}</td>
                    <td class="text-right font-mono text-xs font-black ${amountColor}">${amountFormatted}</td>
                    <td><span class="text-[10px] font-black px-2 py-0.5 rounded uppercase tracking-wider border ${statusClass}">${status}</span></td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
    }

    /**
     * Populate and open Bank Passbook & Statement Modal
     */
    function openPassbookModal() {
        if (!currentUserData) return;

        const user = currentUserData;
        const accounts = currentUserAccounts;
        const transactions = currentUserTransactions;
        const primaryAcc = accounts[0] || {};

        // Populate Passbook Header Fields
        setText('pb-full-name', user.full_name || 'Account Holder');
        setText('pb-customer-id', user.customer_id || 'FNZ-USR');
        setText('pb-phone', user.phone || 'N/A');
        setText('pb-email', user.email || 'N/A');
        
        const addressParts = [user.street, user.city, user.state, user.pincode, user.country].filter(Boolean);
        setText('pb-address', addressParts.length > 0 ? addressParts.join(', ') : 'Not on file');

        const rawType = (primaryAcc.account_type || user.account_type || 'savings').toLowerCase();
        const accountTypeFormatted = rawType.includes('current') ? 'Current Account' : (rawType.includes('fixed') ? 'Fixed Deposit' : 'Savings Account');
        
        setText('pb-account-no', primaryAcc.account_no || 'FNZ-PENDING');
        setText('pb-account-type', accountTypeFormatted);

        // Calculate Totals for Credits and Debits
        const accountNos = accounts.map(a => a.account_no);
        let totalCredits = 0;
        let totalDebits = 0;

        let tableRows = '';

        if (transactions && transactions.length > 0) {
            transactions.forEach((t, index) => {
                const type = (t.type || 'transfer').toLowerCase();
                let isCredit = (type === 'deposit' || type === 'interest_credit');
                if (type === 'transfer') {
                    isCredit = accountNos.includes(t.to_account);
                }

                const amt = parseFloat(t.amount || 0);
                if (isCredit) {
                    totalCredits += amt;
                } else {
                    totalDebits += amt;
                }

                const dateStr = t.created_at ? new Date(t.created_at).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : 'N/A';
                const debitDisplay = !isCredit ? '₹' + amt.toLocaleString('en-IN', { minimumFractionDigits: 2 }) : '-';
                const creditDisplay = isCredit ? '₹' + amt.toLocaleString('en-IN', { minimumFractionDigits: 2 }) : '-';

                tableRows += `
                    <tr class="hover:bg-slate-50 transition-colors border-b border-slate-200">
                        <td class="py-2.5 px-3 text-center text-slate-400 font-sans text-[10px]">${index + 1}</td>
                        <td class="py-2.5 px-3 whitespace-nowrap">${dateStr}</td>
                        <td class="py-2.5 px-3">
                            <div class="font-bold text-slate-900">${t.description || type.toUpperCase()}</div>
                            <div class="text-[9px] text-slate-400 font-mono">${t.txn_id || ('TXN-' + t.id)}</div>
                        </td>
                        <td class="py-2.5 px-3 text-right text-rose-600 font-bold">${debitDisplay}</td>
                        <td class="py-2.5 px-3 text-right text-emerald-600 font-bold">${creditDisplay}</td>
                        <td class="py-2.5 px-3 text-center"><span class="px-2 py-0.5 rounded text-[9px] font-sans font-bold uppercase ${t.status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600'}">${t.status || 'COMPLETED'}</span></td>
                    </tr>
                `;
            });
        } else {
            tableRows = '<tr><td colspan="6" class="py-8 text-center text-slate-400 font-sans">No transaction entries found in passbook period.</td></tr>';
        }

        const currentBalance = parseFloat(primaryAcc.balance || 0);
        setText('pb-total-credits', '+ ₹' + totalCredits.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
        setText('pb-total-debits', '- ₹' + totalDebits.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
        setText('pb-net-balance', '₹' + currentBalance.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
        setText('pb-txn-count', `${transactions.length} record${transactions.length === 1 ? '' : 's'}`);

        const tbody = document.getElementById('pb-transactions-body');
        if (tbody) tbody.innerHTML = tableRows;

        // Open Modal
        const modal = document.getElementById('passbook-modal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        }
    }

    function closePassbookModal() {
        const modal = document.getElementById('passbook-modal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        }
    }

    /**
     * Dedicated Clean Bank Passbook Printing via Isolated Frame
     */
    function printPassbookDocument() {
        if (!currentUserData) return;

        const user = currentUserData;
        const accounts = currentUserAccounts;
        const transactions = currentUserTransactions;
        const primaryAcc = accounts[0] || {};

        const fullName = user.full_name || 'Account Holder';
        const customerId = user.customer_id || 'FNZ-USR';
        const phone = user.phone || 'N/A';
        const email = user.email || 'N/A';
        const addressParts = [user.street, user.city, user.state, user.pincode, user.country].filter(Boolean);
        const address = addressParts.length > 0 ? addressParts.join(', ') : 'Not on file';

        const rawType = (primaryAcc.account_type || user.account_type || 'savings').toLowerCase();
        const accountTypeFormatted = rawType.includes('current') ? 'Current Account' : (rawType.includes('fixed') ? 'Fixed Deposit' : 'Savings Account');
        const accountNo = primaryAcc.account_no || 'FNZ-PENDING';
        const currentBalance = parseFloat(primaryAcc.balance || 0);

        const accountNos = accounts.map(a => a.account_no);
        let totalCredits = 0;
        let totalDebits = 0;

        let rowsHtml = '';
        if (transactions && transactions.length > 0) {
            transactions.forEach((t, i) => {
                const type = (t.type || 'transfer').toLowerCase();
                let isCredit = (type === 'deposit' || type === 'interest_credit');
                if (type === 'transfer') {
                    isCredit = accountNos.includes(t.to_account);
                }

                const amt = parseFloat(t.amount || 0);
                if (isCredit) {
                    totalCredits += amt;
                } else {
                    totalDebits += amt;
                }

                const dateStr = t.created_at ? new Date(t.created_at).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : 'N/A';
                const debitDisplay = !isCredit ? '₹' + amt.toLocaleString('en-IN', { minimumFractionDigits: 2 }) : '-';
                const creditDisplay = isCredit ? '₹' + amt.toLocaleString('en-IN', { minimumFractionDigits: 2 }) : '-';

                rowsHtml += `
                    <tr>
                        <td style="text-align: center; color: #64748b;">${i + 1}</td>
                        <td style="white-space: nowrap; font-weight: 600;">${dateStr}</td>
                        <td>
                            <div style="font-weight: 700; color: #0f172a;">${t.description || type.toUpperCase()}</div>
                            <div style="font-size: 8.5px; color: #64748b; font-family: monospace;">Ref: ${t.txn_id || ('TXN-' + t.id)}</div>
                        </td>
                        <td style="text-align: right; color: #dc2626; font-weight: 700;">${debitDisplay}</td>
                        <td style="text-align: right; color: #16a34a; font-weight: 700;">${creditDisplay}</td>
                        <td style="text-align: center; font-size: 9px; font-weight: 700; text-transform: uppercase;">${t.status || 'COMPLETED'}</td>
                    </tr>
                `;
            });
        } else {
            rowsHtml = '<tr><td colspan="6" style="text-align:center; padding: 20px; color: #64748b;">No transactions recorded.</td></tr>';
        }

        const nowFormatted = new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true }).replace(',', '');
        const auditRef = 'FINZ-PB-' + Math.random().toString(36).substring(2, 10).toUpperCase();

        const printHtml = `
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <title>Passbook - ${fullName} - ${accountNo}</title>
                <style>
                    @page {
                        size: A4 portrait;
                        margin: 12mm;
                    }
                    * {
                        box-sizing: border-box;
                        margin: 0;
                        padding: 0;
                    }
                    body {
                        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
                        color: #0f172a;
                        background: #ffffff;
                        padding: 0;
                        font-size: 11px;
                        line-height: 1.35;
                    }
                    .passbook-paper {
                        width: 100%;
                        max-width: 100%;
                    }
                    .header-table {
                        width: 100%;
                        border-bottom: 2.5px solid #002645;
                        padding-bottom: 8px;
                        margin-bottom: 12px;
                    }
                    .bank-title {
                        font-size: 20px;
                        font-weight: 900;
                        color: #002645;
                        letter-spacing: -0.5px;
                    }
                    .bank-subtitle {
                        font-size: 9px;
                        font-weight: 700;
                        color: #475569;
                        text-transform: uppercase;
                        letter-spacing: 0.5px;
                    }
                    .branch-info {
                        text-align: right;
                        font-size: 10px;
                        color: #334155;
                        font-family: monospace;
                        line-height: 1.3;
                    }
                    .doc-banner {
                        background: #f1f5f9;
                        border: 1px solid #cbd5e1;
                        padding: 5px 10px;
                        margin-bottom: 12px;
                        font-weight: 800;
                        text-transform: uppercase;
                        letter-spacing: 1px;
                        font-size: 11px;
                        color: #002645;
                        display: flex;
                        justify-content: space-between;
                    }
                    .info-grid {
                        width: 100%;
                        margin-bottom: 12px;
                        border-collapse: separate;
                        border-spacing: 10px 0;
                    }
                    .info-box {
                        width: 50%;
                        border: 1px solid #cbd5e1;
                        border-radius: 6px;
                        padding: 8px 10px;
                        vertical-align: top;
                    }
                    .info-title {
                        font-size: 9px;
                        font-weight: 800;
                        color: #64748b;
                        text-transform: uppercase;
                        border-bottom: 1px dashed #cbd5e1;
                        padding-bottom: 3px;
                        margin-bottom: 5px;
                    }
                    .info-row {
                        display: flex;
                        justify-content: space-between;
                        margin-bottom: 3px;
                        font-size: 10.5px;
                    }
                    .info-label {
                        color: #64748b;
                        font-weight: 600;
                    }
                    .info-val {
                        color: #0f172a;
                        font-weight: 700;
                        text-align: right;
                    }
                    .summary-grid {
                        width: 100%;
                        border-collapse: collapse;
                        margin-bottom: 14px;
                    }
                    .summary-grid th {
                        background: #f8fafc;
                        border: 1px solid #cbd5e1;
                        padding: 5px 8px;
                        font-size: 9px;
                        font-weight: 800;
                        text-transform: uppercase;
                        color: #475569;
                    }
                    .summary-grid td {
                        border: 1px solid #cbd5e1;
                        padding: 6px 8px;
                        text-align: center;
                        font-size: 11.5px;
                        font-weight: 800;
                        font-family: monospace;
                    }
                    .ledger-table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-bottom: 14px;
                    }
                    .ledger-table th {
                        background: #002645;
                        color: #ffffff;
                        border: 1px solid #002645;
                        padding: 6px 8px;
                        font-size: 9.5px;
                        font-weight: 800;
                        text-transform: uppercase;
                        letter-spacing: 0.5px;
                    }
                    .ledger-table td {
                        border: 1px solid #cbd5e1;
                        padding: 5px 8px;
                        font-size: 10px;
                    }
                    .ledger-table tr:nth-child(even) {
                        background: #f8fafc;
                    }
                    .footer-table {
                        width: 100%;
                        border-top: 1.5px solid #0f172a;
                        padding-top: 8px;
                        margin-top: 12px;
                    }
                    .seal-box {
                        border: 1.5px dashed #64748b;
                        border-radius: 6px;
                        padding: 4px 10px;
                        text-align: center;
                        display: inline-block;
                    }
                </style>
            </head>
            <body>
                <div class="passbook-paper">
                    <table class="header-table">
                        <tr>
                            <td style="vertical-align: middle;">
                                <div class="bank-title">FINZO GLOBAL DIGITAL BANK</div>
                                <div class="bank-subtitle">Core Banking Institution &bull; ISO 9001:2015 Certified</div>
                            </td>
                            <td class="branch-info" style="vertical-align: middle;">
                                <div><strong>IFSC:</strong> FINZ0002026 | <strong>MICR:</strong> 400026002</div>
                                <div>Branch: Central HQ, Mumbai 400051</div>
                                <div>CIN: L65110MH2026PLC123456</div>
                            </td>
                        </tr>
                    </table>

                    <div class="doc-banner">
                        <span>OFFICIAL BANK STATEMENT / PASSBOOK</span>
                        <span style="font-family: monospace; font-size: 10px;">GENERATED: ${nowFormatted}</span>
                    </div>

                    <table class="info-grid">
                        <tr>
                            <td class="info-box">
                                <div class="info-title">Account Holder Details</div>
                                <div class="info-row"><span class="info-label">Name:</span><span class="info-val" style="font-size: 12px; color: #002645;">${fullName}</span></div>
                                <div class="info-row"><span class="info-label">Customer ID (CIF):</span><span class="info-val" style="font-family: monospace;">${customerId}</span></div>
                                <div class="info-row"><span class="info-label">Mobile:</span><span class="info-val">${phone}</span></div>
                                <div class="info-row"><span class="info-label">Email:</span><span class="info-val">${email}</span></div>
                                <div class="info-row"><span class="info-label">Address:</span><span class="info-val" style="font-size: 9.5px; max-width: 220px;">${address}</span></div>
                            </td>
                            <td class="info-box">
                                <div class="info-title">Account Particulars</div>
                                <div class="info-row"><span class="info-label">Account No:</span><span class="info-val" style="font-family: monospace; font-size: 12px; color: #002645;">${accountNo}</span></div>
                                <div class="info-row"><span class="info-label">Account Type:</span><span class="info-val" style="text-transform: uppercase;">${accountTypeFormatted}</span></div>
                                <div class="info-row"><span class="info-label">Currency:</span><span class="info-val">INR (₹ Indian Rupee)</span></div>
                                <div class="info-row"><span class="info-label">Interest Rate:</span><span class="info-val">${primaryAcc.interest_rate || '3.50'}% p.a.</span></div>
                                <div class="info-row"><span class="info-label">Status:</span><span class="info-val" style="color: #16a34a; font-weight: 800;">ACTIVE</span></div>
                            </td>
                        </tr>
                    </table>

                    <table class="summary-grid">
                        <tr>
                            <th style="color: #16a34a;">Total Deposits (Credit +)</th>
                            <th style="color: #dc2626;">Total Withdrawals (Debit -)</th>
                            <th style="color: #002645;">Current Available Balance</th>
                        </tr>
                        <tr>
                            <td style="color: #16a34a;">+ ₹${totalCredits.toLocaleString('en-IN', { minimumFractionDigits: 2 })}</td>
                            <td style="color: #dc2626;">- ₹${totalDebits.toLocaleString('en-IN', { minimumFractionDigits: 2 })}</td>
                            <td style="color: #002645; font-size: 13px;">₹${currentBalance.toLocaleString('en-IN', { minimumFractionDigits: 2 })}</td>
                        </tr>
                    </table>

                    <table class="ledger-table">
                        <thead>
                            <tr>
                                <th style="width: 28px; text-align: center;">#</th>
                                <th style="width: 75px; text-align: left;">Date</th>
                                <th style="text-align: left;">Particulars / Transaction Ref</th>
                                <th style="width: 90px; text-align: right;">Debit (Dr) ₹</th>
                                <th style="width: 90px; text-align: right;">Credit (Cr) ₹</th>
                                <th style="width: 75px; text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${rowsHtml}
                        </tbody>
                    </table>

                    <table class="footer-table">
                        <tr>
                            <td style="font-size: 9px; color: #64748b; line-height: 1.35;">
                                <div><strong>IMPORTANT NOTICE:</strong> This is an official computer-generated passbook statement issued by Finzo Bank.</div>
                                <div>Does not require physical signature &bull; Audit Ref: <strong style="font-family: monospace; color: #0f172a;">${auditRef}</strong></div>
                            </td>
                            <td style="text-align: right; vertical-align: middle;">
                                <div class="seal-box">
                                    <div style="font-size: 8px; font-weight: 800; color: #64748b; text-transform: uppercase;">FINZO SYSTEM SEAL</div>
                                    <div style="font-size: 10px; font-weight: 900; color: #16a34a;">DIGITALLY VERIFIED</div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </body>
            </html>
        `;

        // Create isolated invisible print frame
        let printFrame = document.getElementById('finzo-print-iframe');
        if (!printFrame) {
            printFrame = document.createElement('iframe');
            printFrame.id = 'finzo-print-iframe';
            printFrame.style.position = 'fixed';
            printFrame.style.right = '0';
            printFrame.style.bottom = '0';
            printFrame.style.width = '0';
            printFrame.style.height = '0';
            printFrame.style.border = 'none';
            document.body.appendChild(printFrame);
        }

        const doc = printFrame.contentWindow.document;
        doc.open();
        doc.write(printHtml);
        doc.close();

        setTimeout(() => {
            printFrame.contentWindow.focus();
            printFrame.contentWindow.print();
        }, 300);
    }

    /**
     * Bind Governance & Action Buttons
     */
    function bindActionButtons(user) {
        const suspendBtn = document.getElementById('suspend-btn');
        const suspendIcon = document.getElementById('suspend-btn-icon');
        const suspendText = document.getElementById('suspend-btn-text');
        const userStatus = (user.status || 'active').toLowerCase();

        if (suspendBtn && suspendIcon && suspendText) {
            if (userStatus === 'suspended') {
                suspendIcon.textContent = 'check_circle';
                suspendIcon.className = 'material-symbols-outlined text-emerald-500';
                suspendText.textContent = 'Reactivate Account';
                suspendBtn.onclick = function(e) {
                    e.preventDefault();
                    if (confirm('Are you sure you want to reinstate this user account?')) {
                        executeReinstatement();
                    }
                };
            } else {
                suspendIcon.textContent = 'block';
                suspendIcon.className = 'material-symbols-outlined text-warning';
                suspendText.textContent = 'Deactivate Account';
                suspendBtn.onclick = function(e) {
                    e.preventDefault();
                    openGovernanceModal('block');
                };
            }
        }

        const deleteBtn = document.getElementById('delete-btn');
        if (deleteBtn) {
            deleteBtn.onclick = function(e) {
                e.preventDefault();
                openGovernanceModal('delete');
            };
        }

        // Passbook & Statement Button on Transaction History Header
        const openPassbookBtn = document.getElementById('open-passbook-modal-btn');
        if (openPassbookBtn) {
            openPassbookBtn.onclick = function(e) {
                e.preventDefault();
                openPassbookModal();
            };
        }

        const printPbAction = document.getElementById('print-passbook-action');
        if (printPbAction) {
            printPbAction.onclick = function(e) {
                e.preventDefault();
                printPassbookDocument();
            };
        }

        const closePbBtn = document.getElementById('close-passbook-btn');
        if (closePbBtn) {
            closePbBtn.onclick = closePassbookModal;
        }
    }

    /**
     * Lightbox Modal Functions
     */
    window.openLightbox = function(imgSrc, title) {
        const modal = document.getElementById('image-lightbox-modal');
        const img = document.getElementById('lightbox-img');
        const titleEl = document.getElementById('lightbox-title');
        
        if (modal && img) {
            img.src = imgSrc;
            if (titleEl) titleEl.textContent = title || 'Document Preview';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        }
    };

    function closeLightbox() {
        const modal = document.getElementById('image-lightbox-modal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        }
    }

    // Lightbox Events
    const closeBtn = document.getElementById('close-lightbox');
    if (closeBtn) closeBtn.addEventListener('click', closeLightbox);

    const lightboxModal = document.getElementById('image-lightbox-modal');
    if (lightboxModal) {
        lightboxModal.addEventListener('click', (e) => {
            if (e.target === lightboxModal) closeLightbox();
        });
    }

    /**
     * Governance Modal Functions
     */
    function openGovernanceModal(mode) {
        currentMode = mode;
        const config = reasonsMap[mode];
        if (!config) return;

        const modalTitle = document.getElementById('modal-title');
        const modalIcon = document.getElementById('modal-icon');
        const confirmBtn = document.getElementById('confirm-governance-btn');

        if (modalTitle) modalTitle.textContent = config.title;
        if (modalIcon) modalIcon.textContent = config.icon;
        if (confirmBtn) {
            confirmBtn.className = `flex-1 py-3.5 text-white font-black rounded-2xl text-[10px] uppercase tracking-widest shadow-sm transition-all ${config.btnClass}`;
        }

        let html = '';
        config.options.forEach((reason) => {
            html += `
                <label class="flex items-center gap-3 p-3 bg-background rounded-xl border border-border-subtle hover:border-primary/40 cursor-pointer transition-all">
                    <input type="radio" name="reason" value="${reason}" class="text-primary focus:ring-primary/20">
                    <span class="text-xs font-bold text-text-primary leading-tight">${reason}</span>
                </label>
            `;
        });
        html += `
            <label class="flex items-center gap-3 p-3 bg-background rounded-xl border border-border-subtle hover:border-primary/40 cursor-pointer transition-all sm:col-span-2">
                <input type="radio" name="reason" value="Other" class="text-primary focus:ring-primary/20">
                <span class="text-xs font-bold text-text-primary">Other / Custom Investigation</span>
            </label>
        `;

        const reasonGrid = document.getElementById('reason-grid');
        if (reasonGrid) reasonGrid.innerHTML = html;

        document.querySelectorAll('input[name="reason"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const customContainer = document.getElementById('custom-reason-container');
                if (customContainer) {
                    if (this.value === 'Other') {
                        customContainer.classList.remove('hidden');
                    } else {
                        customContainer.classList.add('hidden');
                    }
                }
            });
        });

        const modal = document.getElementById('governance-modal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        }
    }

    function closeGovernanceModal() {
        const modal = document.getElementById('governance-modal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        }
        const pwd = document.getElementById('admin-password');
        if (pwd) pwd.value = '';
        const err = document.getElementById('auth-error');
        if (err) err.classList.add('hidden');
        const custom = document.getElementById('custom-reason-container');
        if (custom) custom.classList.add('hidden');
    }

    const cancelGovBtn = document.getElementById('cancel-governance-btn');
    if (cancelGovBtn) cancelGovBtn.addEventListener('click', closeGovernanceModal);

    const confirmGovBtn = document.getElementById('confirm-governance-btn');
    if (confirmGovBtn) {
        confirmGovBtn.addEventListener('click', async function(e) {
            e.preventDefault();
            const pass = document.getElementById('admin-password') ? document.getElementById('admin-password').value : '';
            const checkedReason = document.querySelector('input[name="reason"]:checked');
            const reason = checkedReason ? checkedReason.value : null;

            if (!reason) {
                alert('Please select a primary reason for this action.');
                return;
            }

            const mappedAction = currentMode === 'block' ? 'suspend' : 'delete';

            try {
                const response = await window.api.patch('/users/' + currentUserId, {
                    action: mappedAction,
                    reason: reason,
                    password: pass
                });

                if (response && (response.success || response.status === 'success')) {
                    this.textContent = 'SUCCESS!';
                    setTimeout(() => {
                        if (mappedAction === 'suspend') {
                            window.location.href = 'admin-suspended-users.php';
                        } else if (mappedAction === 'delete') {
                            window.location.href = 'admin-deleted-users.php';
                        } else {
                            location.reload();
                        }
                    }, 1000);
                } else {
                    const errorEl = document.getElementById('auth-error');
                    if (errorEl) {
                        errorEl.textContent = response.message || 'Authentication failed.';
                        errorEl.classList.remove('hidden');
                    }
                }
            } catch (error) {
                alert('Action failed. Please try again.');
            }
        });
    }

    async function executeReinstatement() {
        try {
            const response = await window.api.patch('/users/' + currentUserId, { action: 'reinstate' });
            if (response && (response.success || response.status === 'success')) {
                if (window.showToast) window.showToast('User account reactivated successfully', 'success');
                setTimeout(() => location.reload(), 1200);
            } else {
                alert(response.message || 'Failed to reinstate user.');
            }
        } catch (error) {
            alert('Action failed. Please try again.');
        }
    }

    // Keyboard navigation (Escape key to close modals)
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeLightbox();
            closeGovernanceModal();
            closePassbookModal();
        }
    });

    // Run loader
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadUserData);
    } else {
        loadUserData();
    }
})();
