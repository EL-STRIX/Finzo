(function() {
    
    async function handleGenerate(e, type, isAsync = false) {
        e.preventDefault();
        const form = document.getElementById(`form-${type}-report`);
        if(!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const formData = new FormData(form);
        const queryParams = new URLSearchParams(formData).toString();
        
        const baseEndpoint = `/admin/reports/${type === 'financial' ? 'financial' : 'demographics'}`;
        let res;

        const submitBtn = form.querySelector('button[type="submit"]');
        const asyncBtn = form.querySelector('.btn-generate-async');
        
        const originalSubmitHtml = submitBtn ? submitBtn.innerHTML : '';
        const originalAsyncHtml = asyncBtn ? asyncBtn.innerHTML : '';

        if (isAsync && asyncBtn && submitBtn) {
            asyncBtn.innerHTML = '<span class="loader border-info border-b-transparent"></span>...';
            asyncBtn.disabled = true;
            submitBtn.disabled = true;
        } else if (!isAsync && submitBtn) {
            submitBtn.innerHTML = '<span class="loader"></span>...';
            submitBtn.disabled = true;
            if (asyncBtn) asyncBtn.disabled = true;
        }

        try {
            if (isAsync) {
                // Async jobs should use POST
                res = await window.api.post(baseEndpoint + '/async', Object.fromEntries(formData.entries()));
            } else {
                res = await window.api.get(baseEndpoint + '?' + queryParams);
            }
            
            if (isAsync) {
                if (res.success || res.status === 202) {
                    window.showToast(res.message || 'Report generation queued. You will be notified when ready.', 'success');
                } else {
                    window.showToast('Failed to queue report', 'error');
                }
            } else {
                if (res.success) {
                    renderResults(type, res.data || res.report || res);
                } else {
                    window.showToast(res.message || 'Failed to generate report', 'error');
                }
            }
        } catch (error) {
            console.error('Report error:', error);
            // Ignore 404s gracefully for UI dev
            if (error.message && error.message.includes('404')) {
                window.showToast('Backend endpoint for this report is not fully implemented yet.', 'warning');
            } else {
                window.showToast('Error generating report', 'error');
            }
        } finally {
            submitBtn.innerHTML = originalSubmitHtml;
            asyncBtn.innerHTML = originalAsyncHtml;
            submitBtn.disabled = false;
            asyncBtn.disabled = false;
        }
    }

    function renderResults(type, data) {
        const container = document.getElementById(`${type}-result`);
        const metricsGrid = document.getElementById(`${type}-metrics`);
        
        container.classList.remove('hidden');
        
        // Render raw data as key value pairs for now since we don't know exact structure
        let html = '';
        if (typeof data === 'object' && data !== null) {
            for (const [key, value] of Object.entries(data)) {
                if (typeof value !== 'object') {
                    const formattedKey = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                    const displayVal = (typeof value === 'number' && key.includes('amount')) 
                        ? `₹${window.FinzoUtils.formatINR(value)}` 
                        : value;
                        
                    html += `
                    <div class="p-3 border border-border-subtle rounded-xl bg-surface">
                        <div class="text-text-muted text-xs mb-1">${formattedKey}</div>
                        <div class="font-bold text-text-primary">${displayVal}</div>
                    </div>
                    `;
                }
            }
            if (html === '') {
                html = '<div class="col-span-full text-center text-text-muted">Report generated successfully, but contained no summable data.</div>';
            }
        } else {
            html = '<div class="col-span-full text-center text-text-muted">No data returned.</div>';
        }
        
        metricsGrid.innerHTML = html;
    }

    function init() {
        // Set default dates to current month
        const today = new Date();
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
        const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0).toISOString().split('T')[0];
        
        document.querySelectorAll('input[type="date"]').forEach(input => {
            if (input.name === 'date_from') input.value = firstDay;
            if (input.name === 'date_to') input.value = lastDay;
        });

        document.getElementById('form-financial-report')?.addEventListener('submit', (e) => handleGenerate(e, 'financial', false));
        document.getElementById('form-demographics-report')?.addEventListener('submit', (e) => handleGenerate(e, 'demographics', false));

        document.querySelectorAll('.btn-generate-async').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const type = e.currentTarget.dataset.type;
                const form = document.getElementById(`form-${type}-report`);
                // Use the submit event handler logic but pass isAsync=true
                const mockEvent = { preventDefault: () => {} };
                handleGenerate(mockEvent, type, true);
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
