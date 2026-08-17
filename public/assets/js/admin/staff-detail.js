/**
 * Finzo Admin — Staff Detail (Dossier & Governance Operations)
 * Comprehensive Staff Portfolio, Classic Monochrome Passbook & Statement Printing, and Governance Actions
 */

(function() {
    let staffId = null;
    let currentStaff = window.__INITIAL_STAFF__ || {};
    let currentStaffTransactions = window.__INITIAL_TRANSACTIONS__ || [];
    let currentStaffAddress = window.__INITIAL_ADDRESS__ || {};
    let currentAction = '';
    let passbookFilter = { from: null, to: null, label: 'ALL TIME' };

    function init() {
        const urlParams = new URLSearchParams(window.location.search);
        staffId = urlParams.get('id') || (currentStaff ? currentStaff.id : null);
        
        if (!staffId) {
            const pathParts = window.location.pathname.split('/');
            const lastPart = pathParts[pathParts.length - 1];
            if (/^\d+$/.test(lastPart)) {
                staffId = lastPart;
            }
        }

        bindEvents();

        if (staffId) {
            loadStaffData();
        }
    }

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
        modal.style.display = 'flex';
        document.body.classList.add('overflow-hidden');
    }

    /**
     * Dedicated Pure Black & White Vintage Bank Passbook Printing via Isolated Frame
     */
    function printPassbookDocument() {
        if (!currentStaff) return;

        const staff = currentStaff;
        const addr = currentStaffAddress || {};
        const transactions = getFilteredPassbookTransactions();

        const fullName = (staff.full_name || 'Staff Member').toUpperCase();
        const staffId = staff.staff_id || 'STF---';
        const designation = (staff.designation || 'Staff') + (staff.department ? ' (' + staff.department + ')' : '');
        const branchName = staff.branch_name ? staff.branch_name.toUpperCase() : 'MAIN BRANCH, MUMBAI';
        const phone = staff.phone || 'N/A';
        const addressParts = [addr.street, addr.city, addr.state, addr.pincode, addr.country || 'India'].filter(Boolean);
        const address = addressParts.length > 0 ? addressParts.join(', ') : 'Not on file';

        let totalCredits = 0;
        let totalDebits = 0;
        let totalVolume = 0;

        let rowsHtml = '';
        if (transactions && transactions.length > 0) {
            transactions.forEach((txn, i) => {
                const isDeposit = (txn.type || '').toLowerCase() === 'deposit';
                const amt = parseFloat(txn.amount || 0);

                if (isDeposit) totalCredits += amt;
                else totalDebits += amt;
                totalVolume += amt;

                const dateStr = txn.created_at ? new Date(txn.created_at).toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' }) : 'N/A';
                const debitDisplay = !isDeposit ? amt.toLocaleString('en-IN', { minimumFractionDigits: 2 }) : '-';
                const creditDisplay = isDeposit ? amt.toLocaleString('en-IN', { minimumFractionDigits: 2 }) : '-';
                const volumeDisplay = amt.toLocaleString('en-IN', { minimumFractionDigits: 2 });
                const particulars = (txn.customer_name ? txn.customer_name.toUpperCase() : 'WALK-IN CUSTOMER') + (txn.txn_id ? ' / ' + txn.txn_id : '');

                rowsHtml += `
                    <tr>
                        <td style="text-align: center;">${String(i + 1).padStart(2, '0')}</td>
                        <td style="text-align: center; white-space: nowrap;">${dateStr}</td>
                        <td style="text-transform: uppercase;">${particulars}</td>
                        <td style="text-align: right;">${debitDisplay}</td>
                        <td style="text-align: right;">${creditDisplay}</td>
                        <td style="text-align: right; font-weight: bold;">${volumeDisplay}</td>
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
                <title>Staff Passbook - ${fullName} - ${staffId}</title>
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
                        <div class="bank-sub">STAFF OPERATION &amp; TRANSACTION PASSBOOK</div>
                        <div class="bank-meta">
                            <div>BRANCH: ${branchName}</div>
                            <div>IFSC: FINZ0002026</div>
                            <div>MICR: 400026002</div>
                        </div>
                    </div>

                    <table class="table-grid">
                        <tr>
                            <td class="lbl">STAFF MEMBER</td>
                            <td class="val" style="font-size: 12px; font-weight: 900;">${fullName}</td>
                            <td class="lbl">STAFF ID</td>
                            <td class="val" style="font-size: 12px;">${staffId}</td>
                        </tr>
                        <tr>
                            <td class="lbl">DESIGNATION</td>
                            <td class="val">${designation}</td>
                            <td class="lbl">BRANCH</td>
                            <td class="val">${branchName}</td>
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
                                <th style="width: 100px; text-align: right;">VOLUME / BAL ₹</th>
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
                                <div class="summary-title">TOTAL PROCESSED VOLUME</div>
                                <div class="summary-amt">₹ ${totalVolume.toLocaleString('en-IN', { minimumFractionDigits: 2 })}</div>
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

        let printFrame = document.getElementById('finzo-staff-print-iframe');
        if (!printFrame) {
            printFrame = document.createElement('iframe');
            printFrame.id = 'finzo-staff-print-iframe';
            printFrame.style.position = 'fixed';
            printFrame.style.right = '0';
            printFrame.style.bottom = '0';
            printFrame.style.width = '0';
            printFrame.style.height = '0';
            printFrame.style.border = '0';
            document.body.appendChild(printFrame);
        }

        const frameDoc = printFrame.contentWindow || printFrame.contentDocument.document || printFrame.contentDocument;
        frameDoc.document.open();
        frameDoc.document.write(printHtml);
        frameDoc.document.close();

        setTimeout(() => {
            printFrame.contentWindow.focus();
            printFrame.contentWindow.print();
        }, 350);
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
            btnClass: 'bg-indigo-600 hover:bg-indigo-700 text-white font-black',
            reasons: []
        },
        deactivate: {
            title: 'Suspend Staff Account',
            subtitle: 'Revoke system permissions',
            body: 'You are about to suspend <strong class="text-text-primary" id="deactivate-target-name">...</strong>. This will revoke branch operations and system access immediately.',
            icon: 'block',
            color: 'amber',
            btnText: 'Confirm Suspension',
            btnClass: 'bg-amber-600 hover:bg-amber-700 text-white font-black',
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
            btnClass: 'bg-emerald-600 hover:bg-emerald-700 text-white font-black',
            reasons: []
        },
        delete: {
            title: 'Delete Staff Account',
            subtitle: 'Permanent soft deletion',
            body: 'You are about to <span class="text-error font-black uppercase underline">PERMANENTLY SOFT-DELETE</span> <strong class="text-text-primary" id="delete-target-name">...</strong>. This action will archive the staff account from active lists.',
            icon: 'person_remove',
            color: 'rose',
            btnText: 'Confirm Permanent Deletion',
            btnClass: 'bg-rose-600 hover:bg-rose-700 text-white font-black',
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

    window.openLightbox = openLightbox;
    window.closeLightbox = closeLightbox;
    window.openGovernanceModal = openGovernanceModal;
    window.closeGovernanceModal = closeGovernanceModal;
    window.openPassbookModal = openPassbookModal;
    window.closePassbookModal = closePassbookModal;

    // ==========================================
    //  LOAD STAFF COMPREHENSIVE DOSSIER
    // ==========================================
    async function loadStaffData() {
        try {
            const response = await window.api.get('/staff/' + staffId);

            if (!response || (!response.success && response.status !== 200 && !response.data)) {
                if (window.showToast) window.showToast(response?.message || 'Error loading staff dossier', 'error');
                return;
            }

            const payload = response.data || response;
            const staff = payload.staff || response.staff || {};
            const performance = payload.performance || response.performance || {};
            const today = payload.today_performance || payload.today || response.today_performance || {};
            const transactions = payload.transactions || response.transactions || [];
            const shifts = payload.shifts || response.shifts || [];
            const activities = payload.activities || response.activities || [];
            const documents = payload.documents || response.documents || [];
            const address = payload.address || response.address || {};
            const totalShifts = payload.total_shifts ?? response.total_shifts ?? 0;

            currentStaff = staff;
            currentStaffTransactions = transactions;
            currentStaffAddress = address;
            if (window.setAllStaffTxns) window.setAllStaffTxns(transactions);

            // 1. Header
            setText('header-staff-name', staff.full_name || 'Staff Member');
            const headerStatusBadge = document.getElementById('header-status-badge');
            if (headerStatusBadge) {
                const isActive = (staff.status === 'active');
                headerStatusBadge.className = isActive 
                    ? 'flex items-center gap-2 px-3.5 py-1.5 rounded-full border border-emerald-500/20 bg-emerald-500/10 shadow-sm'
                    : 'flex items-center gap-2 px-3.5 py-1.5 rounded-full border border-rose-500/20 bg-rose-500/10 shadow-sm';
                
                const dot = headerStatusBadge.querySelector('.status-dot');
                if (dot) {
                    dot.className = isActive ? 'status-dot w-2 h-2 rounded-full bg-emerald-500 animate-pulse' : 'status-dot w-2 h-2 rounded-full bg-rose-500';
                }
                const label = headerStatusBadge.querySelector('.status-label');
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
                    let rText = staff.suspension_reason || 'Administrative Review';
                    const m = rText.match(/Reason:\s*(.*)$/i);
                    if (m) rText = m[1].trim();

                    const sDate = staff.suspended_at ? fmtDate(staff.suspended_at) : '';
                    alertBanner.classList.remove('hidden');
                    alertBanner.className = 'rounded-2xl p-4 sm:p-5 flex items-start gap-4 border border-rose-500/30 bg-rose-500/10 text-rose-700 dark:text-rose-300 shadow-sm';
                    alertIcon.textContent = 'gavel';
                    alertTitle.textContent = 'ACCOUNT SUSPENDED';
                    alertMsg.innerHTML = `Reason: <span class="font-black text-rose-600 dark:text-rose-400">${rText}</span><span class="text-xs font-medium text-text-secondary block mt-0.5">Staff terminal privileges and transaction processing rights are currently revoked.</span>`;
                } else if (isLocked) {
                    alertBanner.classList.remove('hidden');
                    alertBanner.className = 'rounded-2xl p-4 sm:p-5 flex items-start gap-4 border border-amber-500/30 bg-amber-500/10 text-amber-700 dark:text-amber-300 shadow-sm';
                    alertIcon.textContent = 'lock';
                    alertTitle.textContent = 'ACCOUNT TEMPORARILY LOCKED';
                    alertMsg.innerHTML = `This account is locked due to ${staff.failed_attempts || 0} failed login attempts. Unlocks automatically at ${staff.locked_until}.`;
                } else {
                    alertBanner.classList.add('hidden');
                }
            }

            // 3. Profile Card Photo & Badges
            const photoContainer = document.getElementById('staff-photo-container');
            const initials = staff.full_name ? staff.full_name.split(' ').map(n => n[0]).join('').slice(0, 2).toUpperCase() : 'ST';
            if (photoContainer) {
                if (staff.profile_photo_url) {
                    const img = new Image();
                    img.src = staff.profile_photo_url;
                    img.className = 'w-full h-full object-cover';
                    img.alt = staff.full_name || 'Staff Profile';
                    img.onload = () => {
                        photoContainer.innerHTML = '';
                        photoContainer.appendChild(img);
                    };
                    img.onerror = () => {
                        photoContainer.innerHTML = `<span class="text-3xl sm:text-4xl font-black text-primary font-headline">${initials}</span>`;
                    };
                } else {
                    photoContainer.innerHTML = `<span class="text-3xl sm:text-4xl font-black text-primary font-headline">${initials}</span>`;
                }
            }

            setText('info-name', staff.full_name);
            setText('info-staff-id-badge', staff.staff_id || 'STF---');

            // Role Badge (Positioned prominently next to Staff ID)
            const roleBadge = document.getElementById('info-role-badge') || document.getElementById('info-designation-badge');
            const roleName = staff.designation || staff.role || 'Teller';
            if (roleBadge) {
                roleBadge.textContent = roleName;
                if (roleName.toLowerCase().includes('manager')) {
                    roleBadge.className = 'px-3.5 py-1 rounded-xl text-xs font-black uppercase tracking-wider bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/30 shadow-sm';
                } else if (roleName.toLowerCase().includes('supervisor')) {
                    roleBadge.className = 'px-3.5 py-1 rounded-xl text-xs font-black uppercase tracking-wider bg-purple-500/15 text-purple-600 dark:text-purple-400 border border-purple-500/30 shadow-sm';
                } else {
                    roleBadge.className = 'px-3.5 py-1 rounded-xl text-xs font-black uppercase tracking-wider bg-blue-500/15 text-blue-600 dark:text-blue-400 border border-blue-500/30 shadow-sm';
                }
            }

            setText('info-dept-badge', staff.department || 'Teller Operations');

            // Status Badge on Profile Card
            const infoStatusBadge = document.getElementById('info-status-badge');
            const statusText = document.getElementById('info-status-text');
            const isActive = (staff.status === 'active');
            if (infoStatusBadge && statusText) {
                infoStatusBadge.className = isActive 
                    ? 'px-3 py-1 rounded-xl text-xs font-black uppercase tracking-wider inline-flex items-center gap-1.5 bg-success-bg text-success border border-success/20 shadow-sm'
                    : 'px-3 py-1 rounded-xl text-xs font-black uppercase tracking-wider inline-flex items-center gap-1.5 bg-error-bg text-error border border-error/20 shadow-sm';
                statusText.textContent = isActive ? 'Active Account' : 'Suspended';
            }

            setText('info-joined-date', fmtDate(staff.date_of_joining || staff.created_at));

            // Tenure Calculation
            const rawJoin = staff.date_of_joining || staff.created_at;
            if (rawJoin) {
                const joinDate = new Date(rawJoin);
                const now = new Date();
                let yrs = now.getFullYear() - joinDate.getFullYear();
                let mos = now.getMonth() - joinDate.getMonth();
                let days = now.getDate() - joinDate.getDate();
                if (days < 0) {
                    mos--;
                    const prevMonthLastDay = new Date(now.getFullYear(), now.getMonth(), 0).getDate();
                    days += prevMonthLastDay;
                }
                if (mos < 0) {
                    yrs--;
                    mos += 12;
                }
                const tenureParts = [];
                if (yrs > 0) tenureParts.push(`${yrs} ${yrs === 1 ? 'yr' : 'yrs'}`);
                if (mos > 0) tenureParts.push(`${mos} ${mos === 1 ? 'mo' : 'mos'}`);
                if (tenureParts.length === 0) {
                    tenureParts.push(days > 0 ? `${days} ${days === 1 ? 'day' : 'days'}` : 'Joined Today');
                }
                setText('info-tenure', tenureParts.join(', '));
            } else {
                setText('info-tenure', '—');
            }

            setText('info-email', staff.email || 'N/A');
            setText('info-phone', staff.phone || 'N/A');

            // Gender & Age
            let genderStr = staff.gender || 'Not specified';
            if (staff.date_of_birth) {
                const birthDate = new Date(staff.date_of_birth);
                const ageDiff = Date.now() - birthDate.getTime();
                const ageDate = new Date(ageDiff);
                const age = Math.abs(ageDate.getUTCFullYear() - 1970);
                if (!isNaN(age) && age > 0) {
                    genderStr += ` (${age} yrs)`;
                }
            }
            setText('info-gender', genderStr);
            setText('info-dob', fmtDate(staff.date_of_birth));
            
            let emergencyStr = 'Not provided';
            if (staff.emergency_contact_name) {
                emergencyStr = staff.emergency_contact_name;
                if (staff.emergency_contact_phone) emergencyStr += ` (${staff.emergency_contact_phone})`;
            }
            setText('info-emergency', emergencyStr);
            setText('info-branch', staff.branch_name ? `${staff.branch_name} (${staff.branch_code || 'BR'})` : 'Unassigned');

            // Employment & Corporate Profile Card
            setText('emp-designation', staff.designation || 'Staff');
            setText('emp-department', staff.department || 'Teller Operations');
            setText('emp-branch-code', staff.branch_code || '—');
            setText('emp-branch-state', staff.branch_state || '—');
            setText('emp-joined-date', fmtDate(staff.date_of_joining || staff.created_at));
            setText('emp-created-by', staff.created_by_name || 'System');
            setText('emp-created-at', fmtDate(staff.created_at));
            setText('emp-updated-at', fmtDate(staff.updated_at));

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
            setText('sys-failed-logins', `${staff.failed_attempts || 0} attempts`);

            const tempPassMap = {
                'pending': 'Pending First Login',
                'expired': 'Expired',
                'none': 'None (Standard Password)'
            };
            setText('sys-temp-pass', tempPassMap[staff.temp_pass_status] || 'None (Standard Password)');
            setText('sys-lifecycle', staff.is_deleted ? 'Soft-Deleted' : 'Active Ledger');
            
            const lockEl = document.getElementById('sys-lock-status');
            if (lockEl) {
                lockEl.textContent = isLocked ? `Locked until ${fmtDate(staff.locked_until)}` : 'Unlocked / Secure';
                lockEl.className = isLocked ? 'text-xs font-black text-error' : 'text-xs font-bold text-success';
            }

            // 6. Documents
            const doc = (documents && documents.length > 0) ? documents[0] : null;
            setText('doc-id-type', staff.id_type || doc?.id_type || 'Identity Proof');
            setText('doc-id-number', staff.id_number || doc?.id_number || '—');

            const frontSrc = staff.front_image_url || (doc?.front_image ? `/Finzo/${doc.front_image.replace(/^\//, '')}` : null);
            const frontImg = document.getElementById('doc-front-img');
            const frontPlaceholder = document.getElementById('doc-front-placeholder');
            const frontWrapper = document.getElementById('doc-front-wrapper');
            if (frontSrc && frontImg && frontWrapper) {
                frontImg.src = frontSrc;
                frontImg.classList.remove('hidden');
                if (frontPlaceholder) frontPlaceholder.classList.add('hidden');
                frontWrapper.onclick = () => openLightbox(frontSrc);
            } else if (frontPlaceholder && frontImg) {
                frontImg.classList.add('hidden');
                frontPlaceholder.classList.remove('hidden');
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
            } else if (backPlaceholder && backImg) {
                backImg.classList.add('hidden');
                backPlaceholder.classList.remove('hidden');
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

            // 10. Shifts History (Top 5)
            const shiftContainer = document.getElementById('staff-shift-history');
            if (shiftContainer) {
                if (!shifts || shifts.length === 0) {
                    shiftContainer.innerHTML = '<div class="text-xs text-text-secondary text-center py-4">No recent shift logs recorded.</div>';
                } else {
                    shiftContainer.innerHTML = shifts.slice(0, 5).map(s => {
                        const dateStr = fmtDate(s.shift_date);
                        const loginTime = s.login_time ? new Date(s.login_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '09:00 AM';
                        const logoutTime = s.logout_time ? new Date(s.logout_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : null;
                        const timeDisplay = (logoutTime && logoutTime !== loginTime) ? `${loginTime} — ${logoutTime}` : `${loginTime} — 05:30 PM`;
                        return `
                            <div class="flex items-center justify-between p-3 bg-surface rounded-xl border border-border-subtle">
                                <div>
                                    <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest">${dateStr}</div>
                                    <div class="text-xs font-bold text-text-primary mt-0.5">${timeDisplay}</div>
                                </div>
                                <div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-base" aria-hidden="true">check</span>
                                </div>
                            </div>
                        `;
                    }).join('');
                }
            }

            // 11. Transactions Table (Top 8)
            const txnBody = document.getElementById('staff-transactions-body');
            if (txnBody) {
                const displayTxns = (transactions || []).slice(0, 8);
                if (displayTxns.length === 0) {
                    txnBody.innerHTML = '<tr><td colspan="6" class="text-center py-12 text-text-secondary text-xs font-bold uppercase tracking-wider">No transactions processed yet</td></tr>';
                } else {
                    txnBody.innerHTML = displayTxns.map(t => {
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
            confirmBtn.onclick = executeGovernanceAction;
        }

        // Reason Grid for Suspension
        const reasonStep = document.getElementById('reason-step');
        const reasonContainer = document.getElementById('reason-options') || document.getElementById('reason-grid');
        const notesInput = document.getElementById('governance-notes');
        if (config.reasons && config.reasons.length > 0 && reasonStep) {
            let html = '';
            config.reasons.forEach((r, idx) => {
                html += `
                    <label class="flex items-center gap-2.5 p-2.5 bg-background rounded-xl border border-border-subtle hover:border-primary cursor-pointer transition-all text-xs">
                        <input type="radio" name="suspension_reason" value="${r}" ${idx === 0 ? 'checked' : ''} class="text-primary focus:ring-primary">
                        <span class="font-bold text-text-primary text-xs">${r}</span>
                    </label>
                `;
            });
            if (reasonContainer) {
                reasonContainer.innerHTML = html;
            }
            reasonStep.classList.remove('hidden');

            document.querySelectorAll('input[name="suspension_reason"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    if (notesInput) {
                        notesInput.placeholder = (this.value === 'Other') ? 'Specify suspension details...' : 'Additional notes / details...';
                    }
                });
            });
        } else if (reasonStep) {
            reasonStep.classList.add('hidden');
        }

        // Reset auth step & password input
        const authStep = document.getElementById('auth-step');
        if (authStep) authStep.classList.remove('hidden');

        const pwInput = document.getElementById('admin-password');
        if (pwInput) pwInput.value = '';
        const authErr = document.getElementById('auth-error');
        if (authErr) authErr.classList.add('hidden');

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        modal.style.display = 'flex';
        document.body.classList.add('overflow-hidden');
    }

    function closeGovernanceModal() {
        const modal = document.getElementById('governance-modal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        modal.style.display = 'none';
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

            const respPayload = resp.data || resp;

            if (resp.success || resp.status === 200 || respPayload.success) {
                if (currentAction === 'reset') {
                    const tempPass = respPayload.temp_password || resp.temp_password || (resp.data && resp.data.temp_password) || (respPayload.data && respPayload.data.temp_password) || '—';
                    const bodyText = document.getElementById('modal-body-text');
                    if (bodyText) {
                        bodyText.innerHTML = `
                            <div class="p-5 bg-background border border-border-subtle rounded-2xl text-center space-y-2">
                                <div class="text-[10px] font-black text-text-secondary uppercase tracking-widest">Temporary One-Time Password</div>
                                <div class="text-2xl font-black font-mono text-indigo-500 tracking-widest select-all py-2 bg-surface rounded-xl border border-indigo-500/20 shadow-inner">${tempPass}</div>
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
                        confirmBtn.className = 'flex-1 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs uppercase tracking-wider cursor-pointer';
                        confirmBtn.onclick = () => {
                            closeGovernanceModal();
                            loadStaffData();
                        };
                    }
                } else if (currentAction === 'delete') {
                    if (window.showToast) window.showToast('Staff member permanently deleted', 'success');
                    setTimeout(() => {
                        window.location.href = '/Finzo/public/views/admin/admin-staff.php';
                    }, 1000);
                } else {
                    if (window.showToast) window.showToast(respPayload.message || resp.message || 'Action executed successfully', 'success');
                    closeGovernanceModal();
                    loadStaffData();
                }
            } else {
                if (authErr) {
                    authErr.textContent = respPayload.message || resp.message || 'Authorization failed. Please verify credentials.';
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
            printAction.addEventListener('click', printPassbookDocument);
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

    // Initialize safely
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
