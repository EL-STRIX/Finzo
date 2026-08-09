$(document).ready(async function() {
            const params = new URLSearchParams(window.location.search);
            const txnId = params.get('id') || params.get('txn_id');

            if (!txnId) {
                alert('Transaction ID not found.');
                window.location.href = 'staff-dashboard.html';
                return;
            }

            try {
                const res = await api.get('/staff/receipt.php?id=' + txnId);
                if (res.success) {
                    const t = res.transaction;
                    
                    $('#receipt-ref-id').text(t.txn_id);
                    
                    const dateObj = new Date(t.date);
                    $('#r-date').text(dateObj.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' }) + ' ' + dateObj.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit', hour12: true }));
                    
                    $('#r-staff').text(t.staff_name);
                    $('#r-staff-id').text(t.staff_id);
                    $('#r-branch').text(t.branch_name || 'Finzo Main Branch');

                    $('#r-cust-name').text(t.customer_name);
                    $('#r-cust-account').text(t.account_no);

                    const isDeposit = t.type === 'deposit';
                    $('#r-txn-type').text(`Cash ${t.type}`);
                    $('#r-amount').text(formatINR(t.amount));
                    
                    if(isDeposit) {
                        $('#r-amount').addClass('text-emerald-600 dark:text-emerald-500').text('+' + formatINR(t.amount));
                    } else {
                        $('#r-amount').addClass('text-red-600 dark:text-red-500').text('-' + formatINR(t.amount));
                    }

                    const balance = parseFloat(t.balance_after);
                    const amount = parseFloat(t.amount);
                    const prevBalance = isDeposit ? (balance - amount) : (balance + amount);
                    
                    $('#r-prev-balance').text(formatINR(prevBalance));
                    $('#r-avail-balance').text(formatINR(balance));

                } else {
                    alert('Error: ' + res.message);
                }
            } catch (err) {
                console.error('Receipt Error:', err);
                alert('Failed to load receipt details.');
            }
        });