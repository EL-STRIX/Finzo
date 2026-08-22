$(document).ready(async function() {
            await fetchStaffSession();
            loadDashboardStats();
            renderTransactions();
        });

        async function loadDashboardStats() {
            try {
                const res = await api.get('/staff/dashboard.php');
                if (res.success) {
                    const stats = res.shift_stats;
                    $('#stat-deposits').text(formatINR(stats.deposits_total));
                    $('#stat-withdrawals').text(formatINR(stats.withdrawals_total));
                    $('#stat-transactions').text(stats.total_transactions);
                    
                    const now = new Date();
                    $('#stat-month-count').text(stats.total_transactions);
                    $('#stat-month-label').text(now.toLocaleDateString('en-IN', { month: 'short', year: 'numeric' }));
                }
            } catch (err) {
                console.error("Failed to load stats", err);
            }
        }

        let currentPage = 1;
        const itemsPerPage = 10;
        let currentFilters = {
            search: '',
            type: 'all',
            status: 'all',
            range: 'All Time',
            from_date: '',
            to_date: ''
        };

        async function renderTransactions() {
            const tbody = $('#transaction-table-body');
            tbody.html('<tr><td colspan="8" class="text-center py-10 font-bold text-slate-400 text-sm">Loading transactions...</td></tr>');

            try {
                let from_date = currentFilters.from_date;
                let to_date = currentFilters.to_date;
                const today = new Date().toISOString().split('T')[0];
                if (currentFilters.range === 'Today') {
                    from_date = today;
                    to_date = today;
                } else if (currentFilters.range === 'This Week') {
                    const d = new Date();
                    d.setDate(d.getDate() - 7);
                    from_date = d.toISOString().split('T')[0];
                    to_date = today;
                } else if (currentFilters.range === 'This Month') {
                    const d = new Date();
                    d.setDate(1);
                    from_date = d.toISOString().split('T')[0];
                    to_date = today;
                }
                
                const query = new URLSearchParams({
                    page: currentPage,
                    per_page: itemsPerPage,
                    search: currentFilters.search,
                    type: currentFilters.type,
                    status: currentFilters.status,
                    from_date: from_date,
                    to_date: to_date
                });

                const res = await api.get('/staff/history.php?' + query.toString());
                tbody.empty();

                if (!res.success || !res.transactions || res.transactions.length === 0) {
                    tbody.append('<tr><td colspan="8" class="text-center py-10 text-slate-400 font-bold text-sm">No transactions found matching your filters.</td></tr>');
                    renderPagination(1, 1, 0);
                    return;
                }

                res.transactions.forEach((item, idx) => {
                    const isDeposit = item.type === 'deposit';
                    const typeBg = isDeposit ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20' : 'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400 border-red-200 dark:border-red-500/20';
                    const statusBg = item.status === 'completed' ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20' : (item.status === 'pending' ? 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-500/20' : 'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400 border-red-200 dark:border-red-500/20');
                    const amtColor = isDeposit ? 'text-emerald-600 dark:text-emerald-500' : 'text-red-600 dark:text-red-500';
                    const amtPrefix = isDeposit ? '+' : '-';
                    
                    const dateObj = new Date(item.created_at);
                    const dateStr = dateObj.toLocaleDateString('en-IN', { day: '2-digit', month: 'short' });
                    const timeStr = dateObj.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit' });
                    const initials = (item.customer_name || 'U').substring(0,2).toUpperCase();

                    tbody.append(`
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-white/2 transition-colors">
                            <td class="p-3 text-xs font-bold text-slate-400 text-center">${(currentPage-1)*itemsPerPage + idx + 1}</td>
                            <td class="p-3 text-xs font-bold text-slate-900 dark:text-slate-300">
                                ${dateStr} <span class="text-[10px] text-slate-500 ml-1">${timeStr}</span>
                            </td>
                            <td class="p-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded bg-slate-100 dark:bg-white/10 flex items-center justify-center text-[10px] font-bold text-slate-600 dark:text-slate-300">
                                        ${initials}
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-slate-900 dark:text-white leading-tight truncate max-w-[150px]">${item.customer_name}</div>
                                        <div class="text-[10px] text-slate-500 font-mono leading-tight">REF: ${item.txn_id}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-3 font-mono text-xs font-medium text-slate-600 dark:text-slate-400">${item.account_no}</td>
                            <td class="p-3">
                                <span class="inline-block px-2 py-1 rounded text-[9px] font-black uppercase tracking-widest border ${typeBg}">
                                    ${item.type}
                                </span>
                            </td>
                            <td class="p-3 font-mono font-bold text-sm ${amtColor}">${amtPrefix}${formatINR(item.amount)}</td>
                            <td class="p-3">
                                <span class="inline-block px-2 py-1 rounded text-[9px] font-black uppercase tracking-widest border ${statusBg}">
                                    ${item.status}
                                </span>
                            </td>
                            <td class="p-3 text-right">
                                <a href="staff-receipt.php?id=${item.txn_id}" class="inline-flex p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-white/10 hover:text-amber-500 transition-colors" title="Print Receipt">
                                    <span class="material-symbols-outlined text-[18px]">print</span>
                                </a>
                            </td>
                        </tr>
                    `);
                });

                renderPagination(res.current_page, res.pages, res.total);

            } catch (err) {
                tbody.html('<tr><td colspan="8" class="text-center py-10 text-red-500 font-bold text-sm">Error loading data. Please refresh.</td></tr>');
            }
        }

        function renderPagination(current, total, totalItems) {
            const startIdx = (current - 1) * itemsPerPage + 1;
            const endIdx = Math.min(startIdx + itemsPerPage - 1, totalItems);
            
            $('#showing-meta').text(`Showing ${totalItems > 0 ? startIdx : 0}-${endIdx} of ${totalItems}`);

            const pButtons = $('#page-buttons');
            pButtons.empty();
            if (total > 1) {
                pButtons.append(`<button class="px-2 py-1 rounded text-slate-400 hover:bg-slate-100 dark:hover:bg-white/10 ${current === 1 ? 'opacity-50 pointer-events-none' : ''}" id="prev-btn"><span class="material-symbols-outlined text-[18px]">chevron_left</span></button>`);
                
                let start = Math.max(1, current - 2);
                let end = Math.min(total, start + 4);
                if (end - start < 4) start = Math.max(1, end - 4);
                
                for (let i = start; i <= end; i++) {
                    const active = i === current ? 'bg-amber-500 text-white shadow-md' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/10';
                    pButtons.append(`<button class="w-8 h-8 rounded text-[11px] font-bold page-num ${active}" data-page="${i}">${i}</button>`);
                }
                
                pButtons.append(`<button class="px-2 py-1 rounded text-slate-400 hover:bg-slate-100 dark:hover:bg-white/10 ${current === total ? 'opacity-50 pointer-events-none' : ''}" id="next-btn"><span class="material-symbols-outlined text-[18px]">chevron_right</span></button>`);
            }
        }

        $(document).on('change', '#filter-type, #filter-status', function() {
            applyFilters();
        });

        let _searchTimer;
        $('#search-input').on('input', function() {
            clearTimeout(_searchTimer);
            _searchTimer = setTimeout(applyFilters, 350);
        });
        $('#search-input').on('keyup', function(e) {
            if (e.key === 'Enter') {
                clearTimeout(_searchTimer);
                applyFilters();
            }
        });

        function applyFilters() {
            currentFilters.search = $('#search-input').val().trim();
            currentFilters.type = $('#filter-type').val().toLowerCase().replace('all types', 'all').replace('deposits', 'deposit').replace('withdrawals', 'withdrawal');
            currentFilters.status = $('#filter-status').val().toLowerCase().replace('all status', 'all');
            currentFilters.from_date = $('#filter-from-date').val();
            currentFilters.to_date = $('#filter-to-date').val();
            currentPage = 1;
            renderTransactions();
        }

        $('.time-tab').on('click', function() {
            $('.time-tab').removeClass('bg-amber-500 text-white shadow shadow-amber-500/20').addClass('text-slate-500 dark:text-slate-400');
            $(this).addClass('bg-amber-500 text-white shadow shadow-amber-500/20').removeClass('text-slate-500 dark:text-slate-400');
            currentFilters.range = $(this).text().trim();
            
            if (currentFilters.range === 'Custom') {
                $('#custom-date-row').slideDown(200);
            } else {
                $('#custom-date-row').slideUp(200);
                $('#filter-from-date').val('');
                $('#filter-to-date').val('');
                currentFilters.from_date = '';
                currentFilters.to_date = '';
            }
            
            currentPage = 1;
            renderTransactions();
        });

        $(document).on('change', '#filter-from-date, #filter-to-date', function() {
            applyFilters();
        });

        $(document).on('click', '.page-num', function() {
            currentPage = parseInt($(this).data('page'));
            renderTransactions();
        });

        $(document).on('click', '#prev-btn', function() {
            if (currentPage > 1) { currentPage--; renderTransactions(); }
        });

        $(document).on('click', '#next-btn', function() {
            currentPage++; renderTransactions();
        });

        async function printAllTransactions() {
            const btn = $('#print-all-btn');
            const originalHtml = btn.html();
            btn.prop('disabled', true).html('<span class="material-symbols-outlined text-[18px] animate-spin">sync</span><span class="hidden sm:inline">Loading...</span>');

            try {
                let from_date = currentFilters.from_date;
                let to_date = currentFilters.to_date;
                const today = new Date().toISOString().split('T')[0];
                if (currentFilters.range === 'Today') {
                    from_date = today;
                    to_date = today;
                } else if (currentFilters.range === 'This Week') {
                    const d = new Date();
                    d.setDate(d.getDate() - 7);
                    from_date = d.toISOString().split('T')[0];
                    to_date = today;
                } else if (currentFilters.range === 'This Month') {
                    const d = new Date();
                    d.setDate(1);
                    from_date = d.toISOString().split('T')[0];
                    to_date = today;
                }

                const query = new URLSearchParams({
                    page: 1,
                    per_page: 999,
                    search: currentFilters.search,
                    type: currentFilters.type,
                    status: currentFilters.status,
                    from_date: from_date,
                    to_date: to_date
                });

                const res = await api.get('/staff/history.php?' + query.toString());
                btn.prop('disabled', false).html(originalHtml);

                if (!res.success || !res.transactions || res.transactions.length === 0) {
                    alert('No transactions to print with the current filters.');
                    return;
                }

                let totalDeposits = 0, totalWithdrawals = 0;
                res.transactions.forEach(t => {
                    if (t.type === 'deposit') totalDeposits += parseFloat(t.amount);
                    else totalWithdrawals += parseFloat(t.amount);
                });

                const fmt = (n) => '₹' + parseFloat(n).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                const now = new Date();
                const printDate = now.toLocaleString('en-IN', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true });

                let rowsHtml = '';
                res.transactions.forEach((item, idx) => {
                    const dateObj = new Date(item.created_at);
                    const dateStr = dateObj.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
                    const isDeposit = item.type === 'deposit';
                    const amtColor = isDeposit ? '#059669' : '#dc2626';
                    
                    rowsHtml += `
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding:8px; font-size:10px; color:#64748b; text-align:center;">${idx + 1}</td>
                            <td style="padding:8px; font-size:10px;">${dateStr}</td>
                            <td style="padding:8px; font-size:10px; font-weight:bold;">${item.customer_name}<br><span style="font-size:8px; font-family:monospace; color:#64748b;">${item.txn_id}</span></td>
                            <td style="padding:8px; font-size:10px; font-family:monospace;">${item.account_no}</td>
                            <td style="padding:8px; font-size:10px; text-transform:uppercase;">${item.type}</td>
                            <td style="padding:8px; font-size:10px; font-weight:bold; color:${amtColor};">${fmt(item.amount)}</td>
                            <td style="padding:8px; font-size:10px;">${item.status}</td>
                        </tr>`;
                });

                const printHtml = `<!DOCTYPE html>
                <html>
                <head>
                    <title>Report</title>
                    <style>
                        body { font-family: Arial, sans-serif; }
                        table { width: 100%; border-collapse: collapse; }
                        th { background: #f8fafc; padding: 8px; text-align: left; font-size: 10px; text-transform: uppercase; border-bottom: 2px solid #cbd5e1; }
                        h2 { font-size: 18px; margin-bottom: 5px; }
                        p { font-size: 11px; color: #64748b; margin-bottom: 20px; }
                    </style>
                </head>
                <body>
                    <h2>Finzo Staff — Transaction Report</h2>
                    <p>Generated by ${StaffState.name} (${StaffState.staff_id}) on ${printDate}</p>
                    <div style="display:flex; gap:20px; margin-bottom: 20px; font-size:12px; font-weight:bold;">
                        <span>Total Deposits: <span style="color:#059669">${fmt(totalDeposits)}</span></span>
                        <span>Total Withdrawals: <span style="color:#dc2626">${fmt(totalWithdrawals)}</span></span>
                        <span>Count: ${res.transactions.length}</span>
                    </div>
                    <table>
                        <thead>
                            <tr><th>#</th><th>Date</th><th>Customer/Ref</th><th>Account</th><th>Type</th><th>Amount</th><th>Status</th></tr>
                        </thead>
                        <tbody>${rowsHtml}</tbody>
                    </table>
                </body>
                </html>`;

                const printWin = window.open('', '_blank');
                printWin.document.write(printHtml);
                printWin.document.close();
                printWin.focus();
                setTimeout(() => { printWin.print(); printWin.close(); }, 500);

            } catch (err) {
                alert('Error generating report.');
                btn.prop('disabled', false).html(originalHtml);
            }
        }
