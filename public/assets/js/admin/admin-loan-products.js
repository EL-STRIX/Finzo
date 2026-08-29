(function() {
    let products = [];

    async function loadProducts() {
        try {
            // Check if there's a GET endpoint, assuming it might be /admin/loan-products or part of loan-applications
            const response = await window.api.get('/admin/loan-products');
            if (response.success) {
                products = response.data || [];
                renderProducts();
            } else if (response.status === 404) {
                 window.showToast('Backend endpoint for listing products not fully implemented yet.', 'warning');
                 renderProducts();
            }
        } catch (error) {
            console.error('Failed to load loan products:', error);
            // Ignore 404s gracefully for UI dev
            renderProducts();
        }
    }

    function renderProducts() {
        const tbody = document.getElementById('products-tbody');
        if (!tbody) return;

        if (!products.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="py-12 text-center text-text-muted">No loan products found. Create one to get started.</td></tr>';
            return;
        }

        const query = document.getElementById('product-search')?.value.toLowerCase() || '';
        const filtered = products.filter(p => p.name.toLowerCase().includes(query) || (p.description && p.description.toLowerCase().includes(query)));

        let html = '';
        filtered.forEach(p => {
            const statusClass = p.is_active ? 'badge-success' : 'badge-danger';
            const statusText = p.is_active ? 'ACTIVE' : 'INACTIVE';
            
            html += `
            <tr class="hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">
                <td class="font-mono text-xs opacity-60">#${p.id}</td>
                <td class="font-bold">${p.name}</td>
                <td class="text-sm font-bold text-primary">${p.interest_rate}% p.a.</td>
                <td class="text-sm">₹${window.FinzoUtils.formatINR(p.min_amount)} - ₹${window.FinzoUtils.formatINR(p.max_amount)}</td>
                <td class="text-sm">${p.min_tenure_months} - ${p.max_tenure_months} mo</td>
                <td><span class="status-badge ${statusClass}">${statusText}</span></td>
            </tr>
            `;
        });
        tbody.innerHTML = html;
    }

    function init() {
        loadProducts();

        document.getElementById('product-search')?.addEventListener('input', renderProducts);

        document.getElementById('btn-create-product')?.addEventListener('click', () => {
            document.getElementById('product-form').reset();
            document.getElementById('product-modal').classList.remove('hidden');
        });

        document.getElementById('btn-save-product')?.addEventListener('click', async () => {
            const form = document.getElementById('product-form');
            if(!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const formData = new FormData(form);
            const payload = Object.fromEntries(formData.entries());

            const btn = document.getElementById('btn-save-product');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="loader"></span> Saving...';
            btn.disabled = true;

            try {
                // Post to AdminLoanController::createProduct (Assuming route /api/v1/admin/loan-products)
                const res = await window.api.post('/admin/loan-products', payload);
                
                if (res.success) {
                    window.showToast('Loan product created successfully', 'success');
                    document.getElementById('product-modal').classList.add('hidden');
                    loadProducts();
                } else {
                    window.showToast(res.message || 'Validation failed', 'error');
                }
            } catch (err) {
                console.error(err);
                window.showToast('Error saving loan product. Ensure backend route is active.', 'error');
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
