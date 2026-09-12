/**
 * Finzo User Portal — Bill Payments & Utility Services Handler
 */

document.addEventListener('DOMContentLoaded', async () => {
    buildSidebar('bill-payments');
    buildNavbar('Services & Payments', 'Bill Payments & Utilities');
    await fetchUserSession();
    await loadPaymentHistory();
});

function formatINR(val) {
    const num = parseFloat(val) || 0;
    return '₹ ' + num.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function selectBillerCategory(category, icon) {
    document.getElementById('bill-category').value = category;
    document.getElementById('form-category-title').textContent = category + ' Payment';
    const iconEl = document.getElementById('form-category-icon');
    if (iconEl) iconEl.textContent = icon;
    showToast(`Category selected: ${category}`, 'info');
}

async function loadPaymentHistory() {
    const container = document.getElementById('bill-history-container');
    try {
        const res = await window.api.get('/user/billers/history');
        if (res.success) {
            renderPaymentHistory(res.history || []);
        } else {
            container.innerHTML = `<div class="py-12 text-center text-text-secondary text-xs font-bold uppercase tracking-wider">${res.message || 'No payment history.'}</div>`;
        }
    } catch (e) {
        console.error('Failed to load bill history:', e);
        container.innerHTML = `<div class="py-12 text-center text-text-secondary text-xs font-bold uppercase tracking-wider">Error connecting to server.</div>`;
    }
}

function renderPaymentHistory(history) {
    const container = document.getElementById('bill-history-container');
    if (!container) return;

    if (!history || !history.length) {
        container.innerHTML = '<div class="py-12 text-center text-text-secondary text-xs font-bold uppercase tracking-wider">No bill payments made yet.</div>';
        return;
    }

    container.innerHTML = history.map(h => {
        const paidDate = h.created_at ? new Date(h.created_at).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—';

        return `
        <div class="p-4 rounded-xl border border-border-subtle bg-surface/50 hover:bg-surface transition-all flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-xl">receipt</span>
                </div>
                <div>
                    <div class="font-bold text-sm text-text-primary">${h.biller_name}</div>
                    <div class="text-xs text-text-secondary font-mono">${h.category} • Ref: ${h.reference_no}</div>
                    <div class="text-[10px] text-text-secondary mt-0.5">${paidDate}</div>
                </div>
            </div>

            <div class="text-right">
                <div class="font-display font-extrabold text-base text-text-primary">${formatINR(h.amount)}</div>
                <span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold uppercase text-[9px]">PAID</span>
            </div>
        </div>
        `;
    }).join('');
}

async function submitBillPayment(e) {
    e.preventDefault();
    const category = document.getElementById('bill-category').value;
    const billerName = document.getElementById('bill-biller-name').value;
    const consumerNumber = document.getElementById('bill-consumer-number').value;
    const amount = document.getElementById('bill-amount').value;

    if (!billerName || !consumerNumber || !amount || amount <= 0) {
        showToast('Please provide valid bill details and amount.', 'error');
        return;
    }

    if (!confirm(`Confirm bill payment of ₹${parseFloat(amount).toLocaleString('en-IN')} for ${billerName}?`)) return;

    try {
        const formData = new FormData();
        formData.append('category', category);
        formData.append('biller_name', billerName);
        formData.append('consumer_number', consumerNumber);
        formData.append('amount', amount);

        const res = await window.api.post('/user/billers/pay', formData, true);
        if (res.success) {
            showToast(res.message || 'Bill payment successful!', 'success');
            document.getElementById('bill-pay-form').reset();
            await loadPaymentHistory();
            await fetchUserSession(); // refresh balance
        } else {
            showToast(res.message || 'Bill payment failed.', 'error');
        }
    } catch (e) {
        console.error('Bill payment error:', e);
        showToast('Connection failed.', 'error');
    }
}
