/**
 * Finzo Admin — Interest Management Handler
 * Manages savings interest rates, history, and updates.
 */

(function() {
    // DOM Elements
    const liveRateEl = document.getElementById('live-rate');
    const lastChangeDateEl = document.getElementById('last-change-date');
    const newRateInput = document.getElementById('new-rate');
    const totalInterestEl = document.getElementById('stat-total-interest');
    const interestDeltaEl = document.getElementById('stat-interest-delta');
    const earningAccountsEl = document.getElementById('stat-earning-accounts');
    const historyBody = document.getElementById('interest-history-body');
    const chartContainer = document.getElementById('interest-chart-container');
    const customDateContainer = document.getElementById('custom-date-container');
    const customApplyDateInput = document.getElementById('custom-apply-date');
    const applyFromDropdownInput = document.getElementById('lock_period_input');
    const confirmModal = document.getElementById('confirm-modal');
    const modalMsg = document.getElementById('modal-msg');

    /**
     * Load interest data from API
     */
    async function loadInterestData() {
        try {
            const response = await window.api.get('/admin/interest');
            if (response.status === 'success' || response.success) {
                const info = response.data || response;
                
                // Update Live Rate
                if (liveRateEl) liveRateEl.textContent = info.current_rate;
                if (lastChangeDateEl) lastChangeDateEl.textContent = info.last_change_date;
                if (newRateInput) newRateInput.value = info.current_rate;

                // Update Stats
                if (totalInterestEl) totalInterestEl.textContent = info.stats.total_interest_credited;
                if (interestDeltaEl) {
                    interestDeltaEl.innerHTML = `
                        <span class="material-symbols-outlined" style="font-size:14px; vertical-align:middle;" aria-hidden="true">trending_up</span> 
                        ${info.stats.interest_delta} from last month
                    `;
                }
                if (earningAccountsEl) earningAccountsEl.textContent = info.stats.earning_accounts;

                // Update History Table
                if (historyBody) {
                    const historyHtml = info.history.map(h => `
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="font-semibold text-sm dark:text-slate-200">${h.effective_date}</td>
                            <td class="font-black font-headline text-lg text-primary dark:text-blue-400">${h.rate}%</td>
                            <td class="text-sm opacity-60">${h.updated_by}</td>
                            <td class="text-right">
                                <span class="status-badge ${h.status === 'Active' ? 'badge-success' : 'badge-neutral'} flex items-center gap-1.5 w-fit ml-auto text-[10px]">
                                    ${h.status === 'Active' ? '<span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>' : ''}
                                    ${h.status}
                                </span>
                            </td>
                        </tr>
                    `).join('');
                    historyBody.innerHTML = historyHtml;
                }

                // Render Chart
                if (chartContainer && info.chart_data) {
                    const maxVal = Math.max(...info.chart_data, 1);
                    const chartHtml = info.chart_data.map(val => {
                        const height = Math.max(10, (val / maxVal) * 100); 
                        return `<div class="chart-bar flex-1 bg-emerald-500 rounded-t-sm transition-all duration-500 hover:bg-emerald-600 cursor-help" style="height:${height}%; opacity:${0.5 + (height/200)};" title="₹${val}"></div>`;
                    }).join('');
                    chartContainer.innerHTML = chartHtml;
                }

                // Update Chart Labels dynamically
                const cd1 = document.getElementById('chart-date-1');
                const cd2 = document.getElementById('chart-date-2');
                const cd3 = document.getElementById('chart-date-3');
                if (cd1 && cd2 && cd3) {
                    const d = new Date();
                    cd3.textContent = d.toLocaleString('en-US', {month: 'short', year: 'numeric'}).toUpperCase();
                    const d2 = new Date(); d2.setMonth(d2.getMonth() - 6);
                    cd2.textContent = d2.toLocaleString('en-US', {month: 'short', year: 'numeric'}).toUpperCase();
                    const d3 = new Date(); d3.setMonth(d3.getMonth() - 11);
                    cd1.textContent = d3.toLocaleString('en-US', {month: 'short', year: 'numeric'}).toUpperCase();
                }
            }
        } catch (error) {
            console.error('Failed to load interest data:', error);
        }
    }

    // Exported Object
    window.InterestManagement = {
        handleApplyFromChange: (value) => {
            if (customDateContainer) {
                if (value === 'Custom Date') {
                    customDateContainer.classList.remove('hidden');
                } else {
                    customDateContainer.classList.add('hidden');
                }
            }
        },

        applyRate: () => {
            const newRate = newRateInput ? newRateInput.value.trim() : '';
            const applyType = applyFromDropdownInput ? applyFromDropdownInput.value : 'Immediately';
            let effectiveDateStr = '';

            if (!newRate || isNaN(parseFloat(newRate))) { 
                window.portal.showToast('Please enter a valid rate.', 'error'); 
                return; 
            }

            if (applyType === 'Immediately') {
                effectiveDateStr = 'immediately';
            } else if (applyType === 'Next Month') {
                const d = new Date();
                const nextMonth = new Date(d.getFullYear(), d.getMonth() + 1, 1);
                effectiveDateStr = 'on ' + nextMonth.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
            } else {
                const customDate = customApplyDateInput ? customApplyDateInput.value : '';
                if (!customDate) { 
                    window.portal.showToast('Please select a custom date.', 'error'); 
                    if (customApplyDateInput) customApplyDateInput.focus();
                    return; 
                }
                effectiveDateStr = 'on ' + new Date(customDate).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
            }
            
            if (modalMsg) {
                modalMsg.innerHTML = `You are about to adjust the global savings rate to <strong class="text-primary dark:text-blue-400">${newRate}%</strong>. This update is scheduled to take effect <strong class="text-emerald-600">${effectiveDateStr}</strong>.`;
            }
            
            if (confirmModal) {
                confirmModal.classList.remove('hidden');
                confirmModal.classList.add('flex');
                confirmModal.classList.add('animate-fade-in');
            }
        },

        closeModal: () => {
            if (confirmModal) {
                confirmModal.classList.add('hidden');
                confirmModal.classList.remove('flex');
            }
        },

        confirmRate: async () => {
            const newRate = newRateInput ? newRateInput.value : '';
            const applyType = applyFromDropdownInput ? applyFromDropdownInput.value : '';
            const customDate = customApplyDateInput ? customApplyDateInput.value : '';
            
            try {
                const response = await window.api.post('/admin/interest', {
                    new_rate: newRate,
                    apply_from: applyType,
                    custom_date: customDate
                });

                if (response.status === 'success' || response.success) {
                    window.InterestManagement.closeModal();
                    window.showToast(response.message || 'Interest rate update scheduled successfully', 'success');
                    loadInterestData();
                } else {
                    window.showToast(response.message || 'Error updating rate', 'error');
                }
            } catch (error) {
                console.error('Update rate error:', error);
                window.showToast('Network error updating rate', 'error');
            }
        }
    };

    // Initialization
    document.addEventListener('DOMContentLoaded', () => {
        loadInterestData();
        
        // Listen for internal dropdown changes
        if (applyFromDropdownInput) {
            // We'll use a MutationObserver or better, event delegation in dropdowns.js
            // But since the current dropdown logic in admin.php was tied to a global handleApplyFromChange,
            // we'll hook into that.
            
            // Temporary strategy for custom date toggle
            document.body.addEventListener('click', (e) => {
                const option = e.target.closest('.finzo-dropdown-option');
                if (option && option.closest('#apply-from-dropdown')) {
                    const val = option.getAttribute('data-value');
                    window.InterestManagement.handleApplyFromChange(val);
                }
            });
        }
    });

})();
