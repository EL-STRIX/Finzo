$(document).ready(async function () {
            const session = await fetchStaffSession();
            if (!session) return;

            // Update welcome text
            $('#welcome-text').text(`Welcome back, ${StaffState.name}`);

            // Populate dashboard data
            if (session.shift_stats) {
                const s = session.shift_stats;
                $('#stat-deposits').text(formatINR(s.deposits_total));
                $('#stat-withdrawals').text(formatINR(s.withdrawals_total));
                $('#stat-transactions').text(s.total_transactions);
                
                const netFlow = $('#stat-netflow');
                netFlow.text((s.net_cash_flow > 0 ? '+' : '') + formatINR(s.net_cash_flow));
                if (s.net_cash_flow < 0) {
                    netFlow.removeClass('text-blue-600 dark:text-blue-500').addClass('text-red-600 dark:text-red-500');
                }
            }

            // Render Recent Activity
            renderTransactions(session.recent_activity);
        });

        function renderTransactions(txns) {
            const tbody = $('#txn-tbody');
            tbody.empty();

            if (!txns || txns.length === 0) {
                tbody.html(`<tr><td colspan="6" class="text-center py-10 font-bold text-slate-400 text-sm">No transactions processed today.</td></tr>`);
                return;
            }

            txns.forEach((txn) => {
                const isDeposit = txn.type === 'deposit';
                const time = new Date(txn.created_at).toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit' });
                const am = formatINR(txn.amount);
                
                // Styles
                const typeBg = isDeposit ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20' : 'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400 border-red-200 dark:border-red-500/20';
                const typeText = txn.type.toUpperCase();
                const sign = isDeposit ? '+' : '-';
                const amtColor = isDeposit ? 'text-emerald-600 dark:text-emerald-500' : 'text-red-600 dark:text-red-500';
                
                const initials = (txn.customer_name || 'U').substring(0,2).toUpperCase();

                tbody.append(`
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-white/2 transition-colors">
                        <td class="p-3 text-xs font-bold text-slate-900 dark:text-slate-300">${time}</td>
                        <td class="p-3">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded bg-slate-100 dark:bg-white/10 flex items-center justify-center text-[10px] font-bold text-slate-600 dark:text-slate-300">
                                    ${initials}
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-slate-900 dark:text-white leading-tight">${txn.customer_name || 'N/A'}</div>
                                    <div class="text-[10px] text-slate-500 font-mono leading-tight">REF: ${txn.txn_id}</div>
                                </div>
                            </div>
                        </td>
                        <td class="p-3 font-mono text-xs font-medium text-slate-600 dark:text-slate-400">${txn.account_no || 'N/A'}</td>
                        <td class="p-3">
                            <span class="inline-block px-2 py-1 rounded text-[9px] font-black uppercase tracking-widest border ${typeBg}">
                                ${typeText}
                            </span>
                        </td>
                        <td class="p-3 font-mono font-bold text-sm ${amtColor}">${sign}${am}</td>
                        <td class="p-3 text-right">
                            <a href="staff-receipt.php?id=${txn.txn_id}" class="inline-flex p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-white/10 hover:text-amber-500 transition-colors" title="View Receipt">
                                <span class="material-symbols-outlined text-[18px]">print</span>
                            </a>
                        </td>
                    </tr>
                `);
            });
        }
