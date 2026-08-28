/**
 * Finzo User Portal — Fixed Deposits & Investments Handler
 */

let allInvestments = [];

document.addEventListener('DOMContentLoaded', async () => {
    buildSidebar('investments');
    buildNavbar('Financial Products', 'Fixed Deposits & Investments');
    await fetchUserSession();
    calculateFDReturns();
    await loadInvestments();
});

function formatINR(val) {
    const num = parseFloat(val) || 0;
    return '₹ ' + num.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

/**
 * FD Return Calculator
 */
function calculateFDReturns() {
    const amount = parseFloat(document.getElementById('calc-fd-amount').value) || 50000;
    const tenureMonths = parseInt(document.getElementById('calc-fd-tenure').value) || 12;

    let interestRate = 6.5;
    if (tenureMonths >= 12 && tenureMonths < 24) interestRate = 7.25;
    else if (tenureMonths >= 24 && tenureMonths < 36) interestRate = 7.75;
    else if (tenureMonths >= 36) interestRate = 8.10;

    const r = interestRate / 100;
    const t = tenureMonths / 12;
    const maturity = amount * Math.pow((1 + r / 4), 4 * t);
    const interest = maturity - amount;

    document.getElementById('calc-fd-rate').textContent = interestRate.toFixed(2) + '%';
    document.getElementById('calc-fd-interest').textContent = formatINR(interest);
    document.getElementById('calc-fd-maturity').textContent = formatINR(maturity);
}

/**
 * Fetch Investments
 */
async function loadInvestments() {
    const container = document.getElementById('fd-container');
    try {
        const res = await window.api.get('/user/investments');
        if (res.success) {
            allInvestments = res.investments || [];
            updateInvestmentStats(allInvestments);
            renderInvestmentsList(allInvestments);
        } else {
            container.innerHTML = `<div class="py-12 text-center text-text-secondary text-xs font-bold uppercase tracking-wider">${res.message || 'Failed to load investments.'}</div>`;
        }
    } catch (e) {
        console.error('Failed to load investments:', e);
        container.innerHTML = `<div class="py-12 text-center text-text-secondary text-xs font-bold uppercase tracking-wider">Error connecting to server.</div>`;
    }
}

/**
 * Update Portfolio Stats Cards
 */
function updateInvestmentStats(investments) {
    const active = investments.filter(i => i.status === 'active');
    const totalAmount = active.reduce((sum, i) => sum + (parseFloat(i.deposit_amount) || 0), 0);
    const totalMaturity = active.reduce((sum, i) => sum + (parseFloat(i.maturity_amount) || 0), 0);
    const totalInterest = totalMaturity - totalAmount;

    document.getElementById('stat-total-fd-amount').textContent = formatINR(totalAmount);
    document.getElementById('stat-total-fd-interest').textContent = formatINR(totalInterest > 0 ? totalInterest : 0);
    document.getElementById('stat-active-fd-count').textContent = active.length;

    if (active.length > 0) {
        const sortedMaturities = active.map(i => new Date(i.maturity_date)).sort((a, b) => a - b);
        document.getElementById('stat-next-fd-maturity').textContent = sortedMaturities[0].toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
    } else {
        document.getElementById('stat-next-fd-maturity').textContent = '—';
    }
}

/**
 * Render Investments List
 */
function renderInvestmentsList(investments) {
    const container = document.getElementById('fd-container');
    if (!container) return;

    if (!investments || !investments.length) {
        container.innerHTML = '<div class="py-12 text-center text-text-secondary text-xs font-bold uppercase tracking-wider">No active term deposits found. Click "Open New Fixed Deposit" to start investing.</div>';
        return;
    }

    container.innerHTML = investments.map(fd => {
        const isActive = (fd.status === 'active');
        const statusBadge = isActive
            ? '<span class="px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-[10px] font-black uppercase tracking-wider">Active</span>'
            : '<span class="px-2.5 py-1 rounded-full bg-slate-500/10 text-slate-600 dark:text-slate-400 border border-slate-500/20 text-[10px] font-black uppercase tracking-wider">Closed</span>';

        const matDate = fd.maturity_date ? new Date(fd.maturity_date).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' }) : '—';
        const createdDate = fd.created_at ? new Date(fd.created_at).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' }) : '—';

        return `
        <div class="p-5 rounded-xl border border-border-subtle bg-surface/50 hover:bg-surface transition-all flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-2xl">account_balance</span>
                </div>
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <h4 class="font-headline font-bold text-base text-text-primary">${fd.fd_number || 'FD'}</h4>
                        ${statusBadge}
                    </div>
                    <div class="text-xs text-text-secondary">
                        Created on ${createdDate} • Tenure: ${fd.tenure_months} Months @ <span class="font-bold text-primary">${fd.interest_rate}% p.a.</span>
                    </div>
                    <div class="text-xs text-text-secondary mt-1 font-mono">
                        Matures on: <span class="font-bold text-text-primary">${matDate}</span> • Est. Maturity Value: <span class="font-bold text-emerald-500">${formatINR(fd.maturity_amount)}</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4 justify-between md:justify-end border-t md:border-t-0 border-border-subtle pt-3 md:pt-0">
                <div class="text-right">
                    <div class="text-[10px] font-bold text-text-secondary uppercase tracking-wider">Principal Amount</div>
                    <div class="font-display font-extrabold text-lg text-primary">${formatINR(fd.deposit_amount)}</div>
                </div>

                ${isActive ? `
                <div class="flex items-center gap-2">
                    <label class="flex items-center gap-1.5 cursor-pointer text-xs font-bold text-text-secondary">
                        <input type="checkbox" ${fd.auto_renew == 1 ? 'checked' : ''} onchange="toggleFDAutoRenew(${fd.id}, this.checked)" class="fin-checkbox-input" />
                        <span>Auto-Renew</span>
                    </label>
                    <button onclick="closeFD(${fd.id}, '${fd.fd_number}')" class="btn btn-secondary btn-xs text-rose-500 hover:bg-rose-500/10">
                        <span>Close</span>
                    </button>
                </div>
                ` : ''}
            </div>
        </div>
        `;
    }).join('');
}

/**
 * Toggle Auto-Renew
 */
async function toggleFDAutoRenew(fdId, isChecked) {
    try {
        const formData = new FormData();
        formData.append('fd_id', fdId);
        formData.append('auto_renew', isChecked ? 1 : 0);

        const res = await window.api.post(`/user/investments/fd/${fdId}/auto-renew`, formData, true);
        if (res.success) {
            showToast(res.message || 'Auto-renewal preference updated.', 'success');
        } else {
            showToast(res.message || 'Failed to update preference.', 'error');
        }
    } catch (e) {
        showToast('Connection failed.', 'error');
    }
}

/**
 * Premature FD Closure
 */
async function closeFD(fdId, fdNumber) {
    if (!confirm(`Are you sure you want to close Fixed Deposit ${fdNumber} prematurely? Funds will be credited back to your primary account.`)) return;

    try {
        const formData = new FormData();
        formData.append('fd_id', fdId);

        const res = await window.api.post(`/user/investments/fd/${fdId}/close`, formData, true);
        if (res.success) {
            showToast(res.message || 'Fixed Deposit closed.', 'success');
            await loadInvestments();
            await fetchUserSession(); // refresh account balance
        } else {
            showToast(res.message || 'Failed to close Fixed Deposit.', 'error');
        }
    } catch (e) {
        console.error('FD closure error:', e);
        showToast('Connection failed.', 'error');
    }
}

/**
 * Create FD Modal & Logic
 */
function openCreateFDModal() {
    document.getElementById('create-fd-modal')?.classList.remove('hidden');
}

function closeCreateFDModal() {
    document.getElementById('create-fd-modal')?.classList.add('hidden');
}

function openCreateFDWithCalc() {
    const amount = document.getElementById('calc-fd-amount').value;
    const tenure = document.getElementById('calc-fd-tenure').value;

    document.getElementById('create-fd-amount').value = amount;
    document.getElementById('create-fd-tenure').value = tenure;
    openCreateFDModal();
}

async function submitCreateFD(e) {
    e.preventDefault();
    const amount = document.getElementById('create-fd-amount').value;
    const tenure = document.getElementById('create-fd-tenure').value;
    const autoRenew = document.getElementById('create-fd-autorenew').checked ? 1 : 0;

    if (!amount || amount < 5000 || !tenure) {
        showToast('Minimum deposit amount is ₹5,000.', 'error');
        return;
    }

    try {
        const formData = new FormData();
        formData.append('amount', amount);
        formData.append('tenure_months', tenure);
        formData.append('auto_renew', autoRenew);

        const res = await window.api.post('/user/investments/fd/create', formData, true);
        if (res.success) {
            showToast(res.message || 'Fixed Deposit opened successfully!', 'success');
            closeCreateFDModal();
            document.getElementById('create-fd-form').reset();
            await loadInvestments();
            await fetchUserSession(); // refresh balance
        } else {
            showToast(res.message || 'Failed to open Fixed Deposit.', 'error');
        }
    } catch (e) {
        console.error('Create FD error:', e);
        showToast('Connection failed.', 'error');
    }
}
