/**
 * Finzo Admin — Interest Management Handler
 * Manages savings interest rates, history, analytics, and policy updates.
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

    function toast(msg, type = 'info') {
        if (window.showToast) window.showToast(msg, type);
        else if (window.portal && window.portal.showToast) window.portal.showToast(msg, type);
        else if (window.FinzoUtils && window.FinzoUtils.showToast) window.FinzoUtils.showToast(msg, type);
        else alert(msg);
    }

    /**
     * Load interest data and analytics from API
     */
    async function loadInterestData() {
        try {
            const response = await window.api.get('/admin/interest');
            if (response.status === 'success' || response.success) {
                const info = response.data || response;
                
                // Update Live Rate Card
                const rateVal = parseFloat(info.current_rate || 0).toFixed(2);
                if (liveRateEl) liveRateEl.textContent = rateVal;
                if (lastChangeDateEl) lastChangeDateEl.textContent = info.last_change_date || '-';
                if (newRateInput && !newRateInput.value) newRateInput.value = rateVal;

                // Update Stats
                if (totalInterestEl) totalInterestEl.textContent = info.stats?.total_interest_credited || '₹0.00';
                if (interestDeltaEl) {
                    const delta = info.stats?.interest_delta || '0%';
                    const isPositive = delta.startsWith('+');
                    const isNegative = delta.startsWith('-');
                    const icon = isNegative ? 'trending_down' : 'trending_up';
                    const colorClass = isNegative ? 'text-error' : 'text-success';

                    interestDeltaEl.className = `text-[10px] font-black flex items-center gap-1.5 uppercase tracking-widest ${colorClass}`;
                    interestDeltaEl.innerHTML = `
                        <span class="material-symbols-outlined text-sm" aria-hidden="true">${icon}</span> 
                        ${delta} from last month
                    `;
                }
                if (earningAccountsEl) earningAccountsEl.textContent = (info.stats?.earning_accounts ?? 0).toLocaleString();

                // Update History Table
                if (historyBody) {
                    if (!info.history || info.history.length === 0) {
                        historyBody.innerHTML = `
                            <tr>
                                <td colspan="4" class="py-12 text-center text-xs font-bold text-text-secondary">
                                    <div class="flex flex-col items-center justify-center">
                                        <span class="material-symbols-outlined text-3xl text-text-secondary/40 mb-2" aria-hidden="true">history_toggle_off</span>
                                        No rate change history recorded yet.
                                    </div>
                                </td>
                            </tr>
                        `;
                    } else {
                        const historyHtml = info.history.map(h => {
                            let badgeClass = 'badge-neutral';
                            let dotHtml = '';
                            if (h.status === 'Active') {
                                badgeClass = 'badge-success';
                                dotHtml = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>';
                            } else if (h.status === 'Upcoming') {
                                badgeClass = 'badge-warn';
                                dotHtml = '<span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>';
                            }

                            return `
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-colors border-b border-border-subtle last:border-b-0">
                                    <td class="font-bold text-xs text-text-primary py-3.5 px-4">${h.effective_date}</td>
                                    <td class="font-black font-headline text-base text-primary py-3.5 px-4">${parseFloat(h.rate).toFixed(2)}%</td>
                                    <td class="text-xs text-text-secondary py-3.5 px-4">${h.updated_by || 'System Administrator'}</td>
                                    <td class="text-right py-3.5 px-4">
                                        <span class="status-badge ${badgeClass} inline-flex items-center gap-1.5 text-[10px] font-black uppercase tracking-wider">
                                            ${dotHtml}
                                            ${h.status}
                                        </span>
                                    </td>
                                </tr>
                            `;
                        }).join('');
                        historyBody.innerHTML = historyHtml;
                    }
                }

                // Render Analytics Chart
                if (chartContainer && Array.isArray(info.chart_data)) {
                    const rawData = info.chart_data;
                    const maxVal = Math.max(...rawData, 0);
                    
                    const chartHtml = rawData.map((val, idx) => {
                        const numVal = parseFloat(val || 0);
                        let heightPercent = 0;
                        if (maxVal > 0 && numVal > 0) {
                            heightPercent = Math.max(12, Math.round((numVal / maxVal) * 100));
                        }
                        const formattedVal = '₹' + numVal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        const monthLabel = getMonthLabel(11 - idx);
                        const hasValue = numVal > 0;

                        return `
                            <div class="flex-1 flex flex-col justify-end relative group cursor-pointer h-full" title="${monthLabel}: ${formattedVal}">
                                <div class="w-full h-full bg-slate-100 dark:bg-white/5 hover:bg-slate-200/50 dark:hover:bg-white/10 rounded-t-sm flex flex-col justify-end p-0.5 transition-colors">
                                    <div class="w-full ${hasValue ? 'bg-emerald-500 dark:bg-emerald-400 group-hover:bg-emerald-600 shadow-sm' : 'bg-slate-300/40 dark:bg-white/10'} rounded-t-sm transition-all duration-500" style="height: ${hasValue ? heightPercent : 4}%;"></div>
                                </div>
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:flex flex-col items-center z-30 pointer-events-none">
                                    <div class="bg-slate-900 text-white text-[10px] font-black py-1 px-2.5 rounded shadow-xl whitespace-nowrap border border-slate-700">
                                        ${monthLabel}: ${formattedVal}
                                    </div>
                                    <div class="w-1.5 h-1.5 bg-slate-900 rotate-45 -mt-0.5 border-r border-b border-slate-700"></div>
                                </div>
                            </div>
                        `;
                    }).join('');
                    chartContainer.innerHTML = chartHtml;
                }

                // Update Chart Labels dynamically
                const cd1 = document.getElementById('chart-date-1');
                const cd2 = document.getElementById('chart-date-2');
                const cd3 = document.getElementById('chart-date-3');
                if (cd1 && cd2 && cd3) {
                    const now = new Date();
                    cd3.textContent = now.toLocaleString('en-US', { month: 'short' }).toUpperCase();
                    
                    const midDate = new Date();
                    midDate.setMonth(now.getMonth() - 6);
                    cd2.textContent = midDate.toLocaleString('en-US', { month: 'short' }).toUpperCase();
                    
                    const startDate = new Date();
                    startDate.setMonth(now.getMonth() - 11);
                    cd1.textContent = startDate.toLocaleString('en-US', { month: 'short' }).toUpperCase();
                }
            } else {
                toast(response.message || 'Failed to load interest rate data', 'error');
            }
        } catch (error) {
            console.error('Failed to load interest data:', error);
            toast('Network error loading interest details', 'error');
        }
    }

    function getMonthLabel(monthsAgo) {
        const d = new Date();
        d.setMonth(d.getMonth() - monthsAgo);
        return d.toLocaleString('en-US', { month: 'short', year: 'numeric' });
    }

    // Exported Object for UI Handlers
    window.InterestManagement = {
        handleApplyFromChange: (value) => {
            if (customDateContainer) {
                if (value === 'Custom Date') {
                    customDateContainer.classList.remove('hidden');
                    if (customApplyDateInput) {
                        const tomorrow = new Date();
                        tomorrow.setDate(tomorrow.getDate() + 1);
                        customApplyDateInput.min = tomorrow.toISOString().split('T')[0];
                        if (!customApplyDateInput.value) {
                            customApplyDateInput.value = tomorrow.toISOString().split('T')[0];
                        }
                    }
                } else {
                    customDateContainer.classList.add('hidden');
                }
            }
        },

        applyRate: () => {
            const newRateStr = newRateInput ? newRateInput.value.trim() : '';
            const newRate = parseFloat(newRateStr);
            const applyType = applyFromDropdownInput ? applyFromDropdownInput.value : 'Immediately';
            let effectiveDateStr = '';

            if (isNaN(newRate) || newRate < 0.01 || newRate > 99.99) { 
                toast('Please enter a valid interest rate between 0.01% and 99.99%.', 'error'); 
                if (newRateInput) newRateInput.focus();
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
                    toast('Please select a valid custom effective date.', 'error'); 
                    if (customApplyDateInput) customApplyDateInput.focus();
                    return; 
                }
                effectiveDateStr = 'on ' + new Date(customDate).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
            }
            
            if (modalMsg) {
                modalMsg.innerHTML = `You are about to adjust the global savings rate to <strong class="text-primary font-black text-base">${newRate.toFixed(2)}%</strong>. This update is scheduled to take effect <strong class="text-text-primary font-black">${effectiveDateStr}</strong>.`;
            }
            
            if (confirmModal) {
                confirmModal.classList.remove('hidden');
                confirmModal.classList.add('flex');
            }
        },

        closeModal: () => {
            if (confirmModal) {
                confirmModal.classList.add('hidden');
                confirmModal.classList.remove('flex');
            }
        },

        confirmRate: async () => {
            const newRate = newRateInput ? parseFloat(newRateInput.value) : 0;
            const applyType = applyFromDropdownInput ? applyFromDropdownInput.value : 'Immediately';
            const customDate = customApplyDateInput ? customApplyDateInput.value : '';
            const confirmBtn = document.getElementById('confirm-rate-btn');
            
            if (!confirmBtn || confirmBtn.disabled) return;
            const originalHTML = confirmBtn.innerHTML;
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<span class="material-symbols-outlined animate-spin text-base mr-1.5" aria-hidden="true">autorenew</span> <span>Applying...</span>';

            try {
                const response = await window.api.post('/admin/interest', {
                    new_rate: newRate,
                    apply_from: applyType,
                    custom_date: customDate
                });

                if (response.status === 'success' || response.success) {
                    window.InterestManagement.closeModal();
                    toast(response.message || `Interest rate successfully updated to ${newRate.toFixed(2)}%`, 'success');
                    loadInterestData();
                } else {
                    toast(response.message || 'Failed to update interest rate', 'error');
                }
            } catch (error) {
                console.error('Update rate error:', error);
                toast('Network error occurred while updating interest rate.', 'error');
            } finally {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = originalHTML;
            }
        }
    };

    // Initialization & Event Binding
    document.addEventListener('DOMContentLoaded', () => {
        loadInterestData();
        
        // Listen for dropdown changes
        document.body.addEventListener('click', (e) => {
            const option = e.target.closest('.finzo-dropdown-option');
            if (option && option.closest('#apply-from-dropdown')) {
                const val = option.getAttribute('data-value');
                window.InterestManagement.handleApplyFromChange(val);
            }
        });

        // Keyboard ESC handler
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                window.InterestManagement.closeModal();
            }
        });
    });

})();
