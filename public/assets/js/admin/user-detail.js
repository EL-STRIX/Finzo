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
        
        const backLink = document.getElementById('back-to-users-link');
        if (backLink) {
            const fromParam = urlParams.get('from');
            if (fromParam === 'suspended' || (document.referrer && document.referrer.includes('admin-suspended-users.php'))) {
                backLink.href = 'admin-suspended-users.php';
                backLink.setAttribute('aria-label', 'Back to Suspended Users');
            }
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

        // Contact & Branch
        setText('info-phone', user.phone || 'N/A');
        setText('info-email', user.email || 'N/A');

        const branchEl = document.getElementById('info-branch');
        if (branchEl) {
            if (user.branch_name && user.branch_name !== 'Unassigned') {
                const branchCodeStr = user.branch_code ? `<span class="font-mono text-xs opacity-60 font-semibold ml-1">(${user.branch_code})</span>` : '';
                branchEl.innerHTML = `<span class="inline-flex items-center gap-1.5"><span class="material-symbols-outlined text-sm text-primary">store</span><span>${user.branch_name}</span>${branchCodeStr}</span>`;
            } else {
                branchEl.textContent = 'Unassigned / Central';
            }
            branchEl.querySelectorAll('.skeleton').forEach(s => s.remove());
        }

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
        const frontUrl = idDocuments.front_url || (user.id_front_path ? (user.id_front_path.startsWith('http') ? user.id_front_path : (user.id_front_path.startsWith('storage/') ? `/Finzo/api/v1/users/${user.id}/kyc?path=${encodeURIComponent(user.id_front_path)}` : `/Finzo/${user.id_front_path.replace(/^\//, '')}`)) : null);
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
        const backUrl = idDocuments.back_url || (user.id_back_path ? (user.id_back_path.startsWith('http') ? user.id_back_path : (user.id_back_path.startsWith('storage/') ? `/Finzo/api/v1/users/${user.id}/kyc?path=${encodeURIComponent(user.id_back_path)}` : `/Finzo/${user.id_back_path.replace(/^\//, '')}`)) : null);
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

    let passbookFilter = { from: null, to: null, label: 'ALL TIME' };

    function getFilteredPassbookTransactions() {
        if (!currentUserTransactions || currentUserTransactions.length === 0) return [];
        if (!passbookFilter.from && !passbookFilter.to) return [...currentUserTransactions];

        return currentUserTransactions.filter(t => {
            if (!t.created_at) return true;
            const tDate = new Date(t.created_at);
            if (passbookFilter.from) {
                const fromDate = new Date(passbookFilter.from + 'T00:00:00');
                if (tDate < fromDate) return false;
            }
            if (passbookFilter.to) {
                const toDate = new Date(passbookFilter.to + 'T23:59:59.999');
                if (tDate > toDate) return false;
            }
            return true;
        });
    }

    /**
     * Render Monochrome Bank Passbook Content (Ledger & Summary)
     */
    function renderPassbookContent() {
        if (!currentUserData) return;

        const user = currentUserData;
        const accounts = currentUserAccounts;
        const primaryAcc = accounts[0] || {};
        const transactions = getFilteredPassbookTransactions();

        // Populate Passbook Header Fields
        setText('pb-full-name', (user.full_name || 'Account Holder').toUpperCase());
        setText('pb-customer-id', user.customer_id || 'FNZ-USR');
        setText('pb-phone', user.phone || 'N/A');
        
        const addressParts = [user.street, user.city, user.state, user.pincode, user.country].filter(Boolean);
        setText('pb-address', addressParts.length > 0 ? addressParts.join(', ') : 'Not on file');

        const rawType = (primaryAcc.account_type || user.account_type || 'savings').toLowerCase();
        const accountTypeFormatted = rawType.includes('current') ? 'CURRENT ACCOUNT' : (rawType.includes('fixed') ? 'FIXED DEPOSIT' : 'SAVINGS ACCOUNT');
        
        setText('pb-account-no', primaryAcc.account_no || 'FNZ0000000000');
        setText('pb-account-type', accountTypeFormatted);

        // Statement Period label
        let periodLabel = '';
        if (passbookFilter.from && passbookFilter.to) {
            const f = new Date(passbookFilter.from + 'T00:00:00').toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });
            const t = new Date(passbookFilter.to + 'T00:00:00').toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });
            periodLabel = `${f} TO ${t}`;
        } else if (passbookFilter.from) {
            const f = new Date(passbookFilter.from + 'T00:00:00').toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });
            periodLabel = `FROM ${f}`;
        } else if (passbookFilter.to) {
            const t = new Date(passbookFilter.to + 'T00:00:00').toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });
            periodLabel = `UP TO ${t}`;
        } else {
            periodLabel = `ALL TIME (UP TO ${new Date().toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' })})`;
        }
        setText('pb-generated-date', periodLabel);

        // Calculate running ledger and totals
        const accountNos = accounts.map(a => a.account_no);
        let totalCredits = 0;
        let totalDebits = 0;

        let tableRows = '';

        if (transactions && transactions.length > 0) {
            const chronological = [...transactions].sort((a, b) => new Date(a.created_at || 0) - new Date(b.created_at || 0));

            chronological.forEach(t => {
                const type = (t.type || 'transfer').toLowerCase();
                let isCredit = (type === 'deposit' || type === 'interest_credit');
                if (type === 'transfer') isCredit = accountNos.includes(t.to_account);
                const amt = parseFloat(t.amount || 0);
                if (isCredit) totalCredits += amt;
                else totalDebits += amt;
            });

            const currentBalance = parseFloat(primaryAcc.balance || 0);
            let runningBalance = currentBalance - (totalCredits - totalDebits);

            chronological.forEach((t, index) => {
                const type = (t.type || 'transfer').toLowerCase();
                let isCredit = (type === 'deposit' || type === 'interest_credit');
                if (type === 'transfer') isCredit = accountNos.includes(t.to_account);
                const amt = parseFloat(t.amount || 0);

                if (isCredit) runningBalance += amt;
                else runningBalance -= amt;

                const dateStr = t.created_at ? new Date(t.created_at).toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' }) : 'N/A';
                const debitDisplay = !isCredit ? amt.toLocaleString('en-IN', { minimumFractionDigits: 2 }) : '-';
                const creditDisplay = isCredit ? amt.toLocaleString('en-IN', { minimumFractionDigits: 2 }) : '-';
                const balanceDisplay = runningBalance.toLocaleString('en-IN', { minimumFractionDigits: 2 });
                const particulars = (t.description || type.toUpperCase()) + (t.txn_id ? ' / ' + t.txn_id : '');

                tableRows += `
                    <tr class="border-b border-black text-black">
                        <td class="p-2 border-r border-black text-center">${index + 1}</td>
                        <td class="p-2 border-r border-black text-center whitespace-nowrap">${dateStr}</td>
                        <td class="p-2 border-r border-black uppercase text-[10.5px]">${particulars}</td>
                        <td class="p-2 border-r border-black text-right">${debitDisplay}</td>
                        <td class="p-2 border-r border-black text-right">${creditDisplay}</td>
                        <td class="p-2 border-r border-black text-right font-bold">${balanceDisplay}</td>
                        <td class="p-2 text-center text-[10px]">SYS</td>
                    </tr>
                `;
            });
        } else {
            tableRows = '<tr><td colspan="7" class="p-4 text-center text-black font-bold">No transactions found for the selected date range.</td></tr>';
        }

        const currentBalance = parseFloat(primaryAcc.balance || 0);
        setText('pb-total-credits', '₹ ' + totalCredits.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
        setText('pb-total-debits', '₹ ' + totalDebits.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
        setText('pb-net-balance', '₹ ' + currentBalance.toLocaleString('en-IN', { minimumFractionDigits: 2 }));

        const tbody = document.getElementById('pb-transactions-body');
        if (tbody) tbody.innerHTML = tableRows;
    }

    /**
     * Preset range button selector
     */
    function setPassbookPreset(preset) {
        const today = new Date();
        const formatDate = (d) => {
        const dt = new Date(d);
        return dt.getFullYear() + '-' + String(dt.getMonth() + 1).padStart(2, '0') + '-' + String(dt.getDate()).padStart(2, '0');
    };
        const fromInput = document.getElementById('pb-filter-from');
        const toInput = document.getElementById('pb-filter-to');

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
     * Populate and open Classic Monochrome Bank Passbook & Statement Modal
     */
    function openPassbookModal() {
        if (!currentUserData) return;

        // Reset inputs & filter
        passbookFilter = { from: null, to: null, label: 'ALL TIME' };
        const fromInput = document.getElementById('pb-filter-from');
        const toInput = document.getElementById('pb-filter-to');
        if (fromInput) fromInput.value = '';
        if (toInput) toInput.value = '';

        renderPassbookContent();

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
     * Dedicated Pure Black & White Vintage Bank Passbook Printing via Isolated Frame
     */
    function printPassbookDocument() {
        if (!currentUserData) return;

        const user = currentUserData;
        const accounts = currentUserAccounts;
        const transactions = getFilteredPassbookTransactions();
        const primaryAcc = accounts[0] || {};

        const fullName = (user.full_name || 'Account Holder').toUpperCase();
        const customerId = user.customer_id || 'FNZ-USR';
        const phone = user.phone || 'N/A';
        const addressParts = [user.street, user.city, user.state, user.pincode, user.country].filter(Boolean);
        const address = addressParts.length > 0 ? addressParts.join(', ') : 'Not on file';

        const rawType = (primaryAcc.account_type || user.account_type || 'savings').toLowerCase();
        const accountTypeFormatted = rawType.includes('current') ? 'CURRENT ACCOUNT' : (rawType.includes('fixed') ? 'FIXED DEPOSIT' : 'SAVINGS ACCOUNT');
        const accountNo = primaryAcc.account_no || 'FNZ0000000000';
        const currentBalance = parseFloat(primaryAcc.balance || 0);

        const accountNos = accounts.map(a => a.account_no);
        let totalCredits = 0;
        let totalDebits = 0;

        let rowsHtml = '';
        if (transactions && transactions.length > 0) {
            const chronological = [...transactions].sort((a, b) => new Date(a.created_at || 0) - new Date(b.created_at || 0));

            chronological.forEach(t => {
                const type = (t.type || 'transfer').toLowerCase();
                let isCredit = (type === 'deposit' || type === 'interest_credit');
                if (type === 'transfer') isCredit = accountNos.includes(t.to_account);
                const amt = parseFloat(t.amount || 0);
                if (isCredit) totalCredits += amt;
                else totalDebits += amt;
            });

            let runningBalance = currentBalance - (totalCredits - totalDebits);

            chronological.forEach((t, i) => {
                const type = (t.type || 'transfer').toLowerCase();
                let isCredit = (type === 'deposit' || type === 'interest_credit');
                if (type === 'transfer') isCredit = accountNos.includes(t.to_account);
                const amt = parseFloat(t.amount || 0);

                if (isCredit) runningBalance += amt;
                else runningBalance -= amt;

                const dateStr = t.created_at ? new Date(t.created_at).toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' }) : 'N/A';
                const debitDisplay = !isCredit ? amt.toLocaleString('en-IN', { minimumFractionDigits: 2 }) : '-';
                const creditDisplay = isCredit ? amt.toLocaleString('en-IN', { minimumFractionDigits: 2 }) : '-';
                const balanceDisplay = runningBalance.toLocaleString('en-IN', { minimumFractionDigits: 2 });
                const particulars = (t.description || type.toUpperCase()) + (t.txn_id ? ' / ' + t.txn_id : '');

                rowsHtml += `
                    <tr>
                        <td style="text-align: center;">${i + 1}</td>
                        <td style="text-align: center; white-space: nowrap;">${dateStr}</td>
                        <td style="text-transform: uppercase;">${particulars}</td>
                        <td style="text-align: right;">${debitDisplay}</td>
                        <td style="text-align: right;">${creditDisplay}</td>
                        <td style="text-align: right; font-weight: bold;">${balanceDisplay}</td>
                        <td style="text-align: center;">SYS</td>
                    </tr>
                `;
            });
        } else {
            rowsHtml = '<tr><td colspan="7" style="text-align: center; padding: 15px;">No transactions recorded for this period.</td></tr>';
        }

        const periodFormatted = document.getElementById('pb-generated-date')?.textContent || new Date().toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });

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
                        font-family: "Courier New", Courier, "Lucida Console", Monaco, monospace;
                        color: #000000;
                        background: #ffffff;
                        padding: 0;
                        font-size: 11px;
                        line-height: 1.3;
                    }
                    .passbook-container {
                        width: 100%;
                        border: 2px solid #000000;
                        padding: 14px 16px;
                    }
                    .bank-header {
                        text-align: center;
                        border-bottom: 2px solid #000000;
                        padding-bottom: 8px;
                        margin-bottom: 10px;
                    }
                    .bank-title {
                        font-size: 20px;
                        font-weight: 900;
                        letter-spacing: 2px;
                        text-transform: uppercase;
                        font-family: Georgia, serif;
                    }
                    .bank-sub {
                        font-size: 11px;
                        font-weight: bold;
                        letter-spacing: 1px;
                        margin-top: 2px;
                    }
                    .bank-meta {
                        display: flex;
                        justify-content: space-between;
                        font-size: 10px;
                        font-weight: bold;
                        border-top: 1px solid #000000;
                        padding-top: 6px;
                        margin-top: 6px;
                    }
                    .table-grid {
                        width: 100%;
                        border-collapse: collapse;
                        border: 1px solid #000000;
                        margin-bottom: 10px;
                        font-size: 10.5px;
                    }
                    .table-grid td, .table-grid th {
                        border: 1px solid #000000;
                        padding: 4px 6px;
                    }
                    .table-grid th {
                        background-color: #f2f2f2;
                        font-weight: 900;
                        text-transform: uppercase;
                        font-size: 10px;
                    }
                    .lbl {
                        font-weight: bold;
                        background-color: #f8f8f8;
                        width: 25%;
                    }
                    .val {
                        font-weight: bold;
                        width: 25%;
                    }
                    .summary-box {
                        width: 100%;
                        border-collapse: collapse;
                        border: 2px solid #000000;
                        margin-bottom: 10px;
                        font-size: 10.5px;
                        text-align: center;
                    }
                    .summary-box td {
                        border: 1px solid #000000;
                        padding: 6px 8px;
                    }
                    .summary-title {
                        font-size: 9.5px;
                        font-weight: bold;
                        text-transform: uppercase;
                    }
                    .summary-amt {
                        font-size: 13px;
                        font-weight: 900;
                        margin-top: 2px;
                    }
                    .footer-notice {
                        border-top: 1px solid #000000;
                        padding-top: 6px;
                        font-size: 9.5px;
                        line-height: 1.35;
                    }
                </style>
            </head>
            <body>
                <div class="passbook-container">
                    <div class="bank-header">
                        <div class="bank-title">FINZO BANK</div>
                        <div class="bank-sub">SAVINGS / CURRENT ACCOUNT PASSBOOK</div>
                        <div class="bank-meta">
                            <span>BRANCH: MAIN BRANCH, MUMBAI</span>
                            <span>IFSC: FINZ0002026</span>
                            <span>MICR: 400026002</span>
                        </div>
                    </div>

                    <table class="table-grid">
                        <tr>
                            <td class="lbl">A/C HOLDER NAME</td>
                            <td class="val" style="font-size: 12px;">${fullName}</td>
                            <td class="lbl">ACCOUNT NO.</td>
                            <td class="val" style="font-size: 12px;">${accountNo}</td>
                        </tr>
                        <tr>
                            <td class="lbl">CUSTOMER ID (CIF)</td>
                            <td class="val">${customerId}</td>
                            <td class="lbl">ACCOUNT TYPE</td>
                            <td class="val">${accountTypeFormatted}</td>
                        </tr>
                        <tr>
                            <td class="lbl">MOBILE NUMBER</td>
                            <td class="val">${phone}</td>
                            <td class="lbl">STATEMENT PERIOD</td>
                            <td class="val">${periodFormatted}</td>
                        </tr>
                        <tr>
                            <td class="lbl">ADDRESS</td>
                            <td class="val" colspan="3">${address}</td>
                        </tr>
                    </table>

                    <table class="table-grid">
                        <thead>
                            <tr>
                                <th style="width: 32px; text-align: center;">LINE</th>
                                <th style="width: 75px; text-align: center;">DATE</th>
                                <th style="text-align: left;">PARTICULARS / DETAILS</th>
                                <th style="width: 95px; text-align: right;">WITHDRAWAL (DR) ₹</th>
                                <th style="width: 95px; text-align: right;">DEPOSIT (CR) ₹</th>
                                <th style="width: 100px; text-align: right;">BALANCE ₹</th>
                                <th style="width: 35px; text-align: center;">INIT</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${rowsHtml}
                        </tbody>
                    </table>

                    <table class="summary-box">
                        <tr>
                            <td style="width: 33.33%;">
                                <div class="summary-title">TOTAL WITHDRAWALS (DR)</div>
                                <div class="summary-amt">₹ ${totalDebits.toLocaleString('en-IN', { minimumFractionDigits: 2 })}</div>
                            </td>
                            <td style="width: 33.33%;">
                                <div class="summary-title">TOTAL DEPOSITS (CR)</div>
                                <div class="summary-amt">₹ ${totalCredits.toLocaleString('en-IN', { minimumFractionDigits: 2 })}</div>
                            </td>
                            <td style="width: 33.34%; background-color: #f2f2f2;">
                                <div class="summary-title">CLOSING AVAILABLE BALANCE</div>
                                <div class="summary-amt">₹ ${currentBalance.toLocaleString('en-IN', { minimumFractionDigits: 2 })}</div>
                            </td>
                        </tr>
                    </table>

                    <div class="footer-notice">
                        <div>* Please examine this passbook immediately. Discrepancies, if any, should be reported within 15 days.</div>
                        <div style="display: flex; justify-content: space-between; margin-top: 4px; font-weight: bold;">
                            <span>* COMPUTER GENERATED PASSBOOK — NO PHYSICAL SIGNATURE REQUIRED</span>
                            <span>PAGE 1 OF 1</span>
                        </div>
                    </div>
                </div>
            </body>
            </html>
        `;

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
        }, 250);
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

        // Date range filter buttons
        const applyFilterBtn = document.getElementById('pb-apply-filter');
        if (applyFilterBtn) {
            applyFilterBtn.onclick = function(e) {
                e.preventDefault();
                const fromInput = document.getElementById('pb-filter-from');
                const toInput = document.getElementById('pb-filter-to');
                const fromVal = fromInput ? fromInput.value : '';
                const toVal = toInput ? toInput.value : '';
                passbookFilter = { from: fromVal || null, to: toVal || null, label: 'CUSTOM' };
                renderPassbookContent();
            };
        }

        const presetAll = document.getElementById('pb-preset-all');
        if (presetAll) presetAll.onclick = function(e) { e.preventDefault(); setPassbookPreset('all'); };

        const presetMonth = document.getElementById('pb-preset-month');
        if (presetMonth) presetMonth.onclick = function(e) { e.preventDefault(); setPassbookPreset('month'); };

        const preset30 = document.getElementById('pb-preset-30');
        if (preset30) preset30.onclick = function(e) { e.preventDefault(); setPassbookPreset('30'); };

        const preset90 = document.getElementById('pb-preset-90');
        if (preset90) preset90.onclick = function(e) { e.preventDefault(); setPassbookPreset('90'); };
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
            let reason = checkedReason ? checkedReason.value : null;

            if (!reason) {
                alert('Please select a primary reason for this action.');
                return;
            }

            if (reason === 'Other') {
                const customNotes = document.getElementById('governance-notes') ? document.getElementById('governance-notes').value.trim() : '';
                if (customNotes) {
                    reason = 'Other: ' + customNotes;
                }
            }

            if (!pass) {
                const errorEl = document.getElementById('auth-error');
                if (errorEl) {
                    errorEl.textContent = 'Please enter your admin password.';
                    errorEl.classList.remove('hidden');
                }
                return;
            }

            const mappedAction = currentMode === 'block' ? 'suspend' : 'delete';
            const csrf = window.csrfToken || (document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '');

            try {
                const response = await window.api.patch('/users/' + currentUserId, {
                    action: mappedAction,
                    reason: reason,
                    password: pass,
                    csrf_token: csrf
                });

                if (response && (response.success || response.status === 'success')) {
                    this.textContent = 'SUCCESS!';
                    if (window.showToast) window.showToast(`User account ${mappedAction === 'suspend' ? 'suspended' : 'deleted'} successfully`, 'success');
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
                const errorEl = document.getElementById('auth-error');
                if (errorEl) {
                    errorEl.textContent = (error && error.message) ? error.message : 'Action failed. Please try again.';
                    errorEl.classList.remove('hidden');
                } else {
                    alert('Action failed. Please try again.');
                }
            }
        });
    }

    async function executeReinstatement() {
        const csrf = window.csrfToken || (document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '');
        try {
            const response = await window.api.patch('/users/' + currentUserId, { 
                action: 'reinstate',
                csrf_token: csrf
            });
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
