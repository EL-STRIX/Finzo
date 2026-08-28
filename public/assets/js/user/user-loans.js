/**
 * Finzo User Portal — Loans & Credit Services Handler
 */

let allLoans = [];
let loanProducts = [];

document.addEventListener('DOMContentLoaded', async () => {
    buildSidebar('loans');
    buildNavbar('Financial Products', 'Loans & Credit Services');
    await fetchUserSession();
    updateCalculator();
    await loadLoanProducts();
    await loadUserLoans();
});

function formatINR(val) {
    const num = parseFloat(val) || 0;
    return '₹ ' + num.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

/**
 * EMI Calculator Logic
 */
function updateCalculator() {
    const amount = parseFloat(document.getElementById('calc-amount-slider').value) || 100000;
    const tenure = parseInt(document.getElementById('calc-tenure-slider').value) || 12;
    const rateAnnual = parseFloat(document.getElementById('calc-rate-slider').value) || 10.5;

    document.getElementById('calc-amount-label').textContent = formatINR(amount);
    document.getElementById('calc-tenure-label').textContent = tenure + ' Months';
    document.getElementById('calc-rate-label').textContent = rateAnnual.toFixed(1) + ' %';

    const r = rateAnnual / (12 * 100); // monthly interest
    const n = tenure;
    
    let emi = 0;
    if (r > 0) {
        emi = (amount * r * Math.pow(1 + r, n)) / (Math.pow(1 + r, n) - 1);
    } else {
        emi = amount / n;
    }

    const totalPayable = emi * n;
    const totalInterest = totalPayable - amount;

    document.getElementById('calc-emi-result').textContent = formatINR(emi);
    document.getElementById('calc-total-payable').textContent = formatINR(totalPayable);
    document.getElementById('calc-total-interest').textContent = formatINR(totalInterest);
}

/**
 * Fetch loan products for dropdown
 */
async function loadLoanProducts() {
    try {
        const res = await window.api.get('/loan-products');
        if (res.success && res.products) {
            loanProducts = res.products;
            const select = document.getElementById('apply-product');
            if (select) {
                select.innerHTML = '<option value="">Select a loan product...</option>' + 
                    loanProducts.map(p => `<option value="${p.id}">${p.name} (${p.interest_rate}% p.a.)</option>`).join('');
            }
        }
    } catch (e) {
        console.error('Failed to load loan products:', e);
    }
}

/**
 * Fetch user loans
 */
async function loadUserLoans() {
    const container = document.getElementById('loans-container');
    try {
        const res = await window.api.get('/loans');
        if (res.success) {
            allLoans = res.loans || [];
            renderLoans(allLoans);
        } else {
            container.innerHTML = `<div class="py-12 text-center text-text-secondary text-xs font-bold uppercase tracking-wider">${res.message || 'Failed to load loans.'}</div>`;
        }
    } catch (e) {
        console.error('Failed to load user loans:', e);
        container.innerHTML = `<div class="py-12 text-center text-text-secondary text-xs font-bold uppercase tracking-wider">Error connecting to server.</div>`;
    }
}

/**
 * Render loan facility cards
 */
function renderLoans(loans) {
    const container = document.getElementById('loans-container');
    if (!container) return;

    const filter = document.getElementById('loan-filter-status')?.value || 'all';
    let filtered = loans;
    if (filter === 'approved') filtered = loans.filter(l => l.status === 'approved' || l.status === 'active');
    else if (filter === 'pending') filtered = loans.filter(l => l.status === 'pending');
    else if (filter === 'rejected') filtered = loans.filter(l => l.status === 'rejected');

    if (!filtered || !filtered.length) {
        container.innerHTML = '<div class="py-12 text-center text-text-secondary text-xs font-bold uppercase tracking-wider">No loan facilities found.</div>';
        return;
    }

    container.innerHTML = filtered.map(l => {
        const isApproved = (l.status === 'approved' || l.status === 'active');
        const isPending = (l.status === 'pending');
        const isRejected = (l.status === 'rejected');

        const statusBadge = isApproved 
            ? '<span class="px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-[10px] font-black uppercase tracking-wider">Active Facility</span>'
            : (isPending 
                ? '<span class="px-2.5 py-1 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 text-[10px] font-black uppercase tracking-wider">Under Review</span>'
                : '<span class="px-2.5 py-1 rounded-full bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20 text-[10px] font-black uppercase tracking-wider">Rejected</span>');

        const createdDate = l.created_at ? new Date(l.created_at).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' }) : '—';

        return `
        <div class="p-5 rounded-xl border border-border-subtle bg-surface/50 hover:bg-surface transition-all flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-2xl">account_balance_wallet</span>
                </div>
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <h4 class="font-headline font-bold text-base text-text-primary">${l.product_name || 'Personal Credit'}</h4>
                        ${statusBadge}
                    </div>
                    <div class="text-xs text-text-secondary">
                        <span class="font-mono font-bold text-text-primary">ID: ${l.id}</span> • Applied on ${createdDate} • ${l.tenure_months} Months @ ${l.interest_rate || '10.5'}% p.a.
                    </div>
                    ${l.purpose ? `<div class="text-xs text-text-secondary italic mt-1.5">"${l.purpose}"</div>` : ''}
                </div>
            </div>

            <div class="flex items-center gap-4 justify-between md:justify-end border-t md:border-t-0 border-border-subtle pt-3 md:pt-0">
                <div class="text-right">
                    <div class="text-[10px] font-bold text-text-secondary uppercase tracking-wider">Facility Amount</div>
                    <div class="font-display font-extrabold text-lg text-primary">${formatINR(l.amount)}</div>
                </div>

                ${isApproved ? `
                <button onclick="viewSchedule(${l.id}, '${l.product_name}')" class="btn btn-secondary btn-sm shrink-0">
                    <span class="material-symbols-outlined text-sm">calendar_month</span>
                    <span>Schedule</span>
                </button>
                ` : ''}
            </div>
        </div>
        `;
    }).join('');
}

function filterLoans() {
    renderLoans(allLoans);
}

/**
 * View Repayment Schedule Modal
 */
async function viewSchedule(loanId, productName) {
    const modal = document.getElementById('schedule-modal');
    const tbody = document.getElementById('schedule-tbody');
    const title = document.getElementById('schedule-modal-title');

    title.textContent = `${productName || 'Loan'} — Repayment Schedule`;
    tbody.innerHTML = '<tr><td colspan="7" class="py-8 text-center text-text-secondary font-bold uppercase text-xs">Loading schedules...</td></tr>';
    modal.classList.remove('hidden');

    try {
        const res = await window.api.get(`/loans/${loanId}/schedules`);
        if (res.success && res.schedules) {
            if (!res.schedules.length) {
                tbody.innerHTML = '<tr><td colspan="7" class="py-8 text-center text-text-secondary font-bold uppercase text-xs">No repayment schedules generated yet.</td></tr>';
                return;
            }

            tbody.innerHTML = res.schedules.map(s => {
                const isPaid = s.status === 'paid';
                const statusBadge = isPaid
                    ? '<span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold uppercase text-[9px]">PAID</span>'
                    : '<span class="px-2 py-0.5 rounded bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold uppercase text-[9px]">DUE</span>';

                const dueDate = s.due_date ? new Date(s.due_date).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' }) : '—';

                return `
                <tr class="hover:bg-surface-hover transition-colors">
                    <td class="py-3 px-2 font-mono font-bold">${s.installment_no || 1}</td>
                    <td class="py-3 px-2 font-mono">${dueDate}</td>
                    <td class="py-3 px-2 font-bold text-text-primary">${formatINR(s.amount)}</td>
                    <td class="py-3 px-2 text-text-secondary">${formatINR(s.principal_amount || 0)}</td>
                    <td class="py-3 px-2 text-text-secondary">${formatINR(s.interest_amount || 0)}</td>
                    <td class="py-3 px-2">${statusBadge}</td>
                    <td class="py-3 px-2 text-right">
                        ${!isPaid ? `
                        <button onclick="payEMI(${loanId}, ${s.id})" class="btn btn-primary btn-xs">
                            <span>Pay EMI</span>
                        </button>
                        ` : '<span class="text-xs text-text-secondary font-mono">Completed</span>'}
                    </td>
                </tr>
                `;
            }).join('');
        }
    } catch (e) {
        console.error('Failed to load schedules:', e);
        tbody.innerHTML = '<tr><td colspan="7" class="py-8 text-center text-rose-500 font-bold uppercase text-xs">Failed to load schedule.</td></tr>';
    }
}

function closeScheduleModal() {
    document.getElementById('schedule-modal')?.classList.add('hidden');
}

/**
 * Pay EMI Action
 */
async function payEMI(loanId, scheduleId) {
    if (!confirm('Confirm repayment of this EMI from your primary account balance?')) return;

    try {
        const formData = new FormData();
        formData.append('loan_id', loanId);
        formData.append('schedule_id', scheduleId);

        const res = await window.api.post(`/loans/${loanId}/repay`, formData, true);
        if (res.success) {
            showToast(res.message || 'EMI Repayment successful!', 'success');
            await viewSchedule(loanId, 'Loan');
            await loadUserLoans();
            await fetchUserSession(); // refresh balance
        } else {
            showToast(res.message || 'Repayment failed.', 'error');
        }
    } catch (e) {
        console.error('EMI repayment failed:', e);
        showToast('Connection failed.', 'error');
    }
}

/**
 * Apply Loan Modal
 */
function openApplyLoanModal() {
    document.getElementById('apply-modal')?.classList.remove('hidden');
}

function closeApplyModal() {
    document.getElementById('apply-modal')?.classList.add('hidden');
}

function applyWithCalcValues() {
    const amount = document.getElementById('calc-amount-slider').value;
    const tenure = document.getElementById('calc-tenure-slider').value;
    
    document.getElementById('apply-amount').value = amount;
    document.getElementById('apply-tenure').value = tenure;
    openApplyLoanModal();
}

async function submitLoanApplication(e) {
    e.preventDefault();
    const productId = document.getElementById('apply-product').value;
    const amount = document.getElementById('apply-amount').value;
    const tenure = document.getElementById('apply-tenure').value;
    const purpose = document.getElementById('apply-purpose').value;

    if (!productId || !amount || !tenure || !purpose) {
        showToast('Please fill in all required fields.', 'error');
        return;
    }

    try {
        const formData = new FormData();
        formData.append('loan_product_id', productId);
        formData.append('amount', amount);
        formData.append('tenure_months', tenure);
        formData.append('purpose', purpose);

        const res = await window.api.post('/loans/apply', formData, true);
        if (res.success) {
            showToast(res.message || 'Loan application submitted successfully!', 'success');
            closeApplyModal();
            document.getElementById('loan-apply-form').reset();
            await loadUserLoans();
        } else {
            showToast(res.message || 'Failed to submit loan application.', 'error');
        }
    } catch (e) {
        console.error('Loan application error:', e);
        showToast('Connection failed.', 'error');
    }
}
