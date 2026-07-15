$(document).ready(async function() {
            await fetchStaffSession();
        });

        // --- Deposit Page Logic ---
        const steps = $('.step-indicator');
        function updateSteps(active) {
            steps.each(function() {
                const s = parseInt($(this).data('step'));
                $(this).removeClass('active completed');
                if (s < active) $(this).addClass('completed');
                else if (s === active) $(this).addClass('active');
            });
        }
        updateSteps(1);

        let verifiedAccountNo = '';
        let verifiedName = '';
        let verifiedBalance = 0;
        
        $('#find-btn').on('click', async function() {
            const acc = $('#account-lookup').val().trim();
            if (!acc) return;
            
            const btn = $(this);
            const originalText = btn.html();
            btn.html('<span class="material-symbols-outlined animate-spin text-[18px]">sync</span> <span>Searching</span>');
            
            try {
                const res = await api.get('/staff/account-lookup.php?account_no=' + encodeURIComponent(acc));
                btn.html(originalText);
                
                if (res.success && res.found && res.account) {
                    $('#account-success').removeClass('hidden');
                    $('#account-error').addClass('hidden');
                    
                    verifiedAccountNo = res.account.account_no;
                    verifiedName      = res.account.name;
                    verifiedBalance   = parseFloat(res.account.balance) || 0;
                    
                    $('#res-name').text(res.account.name);
                    $('#res-acc').text(res.account.account_no);
                    
                    const init = res.account.name.split(' ').map(n=>n[0]).join('').substring(0,2).toUpperCase();
                    $('#res-initials').text(init);
                    
                    $('.account-type-val').text(res.account.account_type);
                    $('.current-balance-val').text(formatINR(res.account.balance));
                    $('.daily-limit-val').text(formatINR(res.account.daily_limit_left));
                    
                } else {
                    $('#account-error').removeClass('hidden');
                    $('#account-success').addClass('hidden');
                }
            } catch (err) {
                btn.html(originalText);
                $('#account-error').removeClass('hidden');
                $('#account-success').addClass('hidden');
            }
        });

        function resetForm() {
            $('#account-lookup').val('');
            $('#account-success, #account-error').addClass('hidden');
            updateSteps(1);
        }

        function goToStep1() {
            $('#section-1').removeClass('hidden');
            $('#section-2, #section-3').addClass('hidden');
            updateSteps(1);
        }

        function goToStep2() {
            $('#section-1, #section-3').addClass('hidden');
            $('#section-2').removeClass('hidden');
            
            $('#sum-name').text(verifiedName);
            $('#sum-acc').text(verifiedAccountNo);
            $('#sum-before').text(formatINR(verifiedBalance));
            
            const amt = parseFloat($('#dep-amount').val()) || 0;
            $('#sum-after').text(formatINR(verifiedBalance + amt));
            
            updateSteps(2);
        }

        function goToStep3() {
            const amt = parseFloat($('#dep-amount').val()) || 0;
            if (amt < 100) { alert('Minimum deposit ₹100'); return; }
            
            $('#section-2').addClass('hidden');
            $('#section-3').removeClass('hidden');
            
            $('#confirm-name').text(verifiedName);
            $('#confirm-acc').text(verifiedAccountNo);
            $('#confirm-amt').text(formatINR(amt));
            $('#confirm-type').text($('#dep-type').val());
            
            const now = new Date();
            $('#current-date-display').text(now.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' }));
            updateSteps(3);
        }

        $('#dep-amount, #dep-type').on('input change', function() {
            const val = parseFloat($('#dep-amount').val()) || 0;
            const type = $('#dep-type').val();
            
            $('#sum-amt').text(formatINR(val));
            $('#sum-after').text(formatINR(verifiedBalance + val));
            $('#sum-type').text(type.replace(' Deposit', '')); // short form
            
            if (val > 0) {
                $('#amt-words').text(numberToWords(val) + ' Rupees Only');
            } else {
                $('#amt-words').text('Waiting for input...');
            }
        });

        $('#final-deposit-btn').on('click', async function() {
            const btn = $(this);
            btn.prop('disabled', true).html('<span class="material-symbols-outlined animate-spin text-[18px]">sync</span> Processing');
            
            const reqData = {
                account_no: verifiedAccountNo,
                amount: parseFloat($('#dep-amount').val()),
                reference_note: ($('#dep-ref').val() || '').trim() || 'Cash Deposit'
            };
            
            try {
                const res = await api.post('/staff/deposit.php', reqData);
                
                if (res.success) {
                    window.location.href = 'staff-receipt.html?id=' + res.transaction_id;
                } else {
                    alert('Error: ' + res.message);
                    btn.prop('disabled', false).html('<span class="material-symbols-outlined text-[18px]">done_all</span> Execute Deposit');
                }
            } catch (err) {
                alert('Connection error. Please try again.');
                btn.prop('disabled', false).html('<span class="material-symbols-outlined text-[18px]">done_all</span> Execute Deposit');
            }
        });

        function numberToWords(number) {
            const ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
            const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
            if (number === 0) return 'Zero';
            if (number < 20) return ones[number];
            if (number < 100) return tens[Math.floor(number / 10)] + (number % 10 !== 0 ? ' ' + ones[number % 10] : '');
            if (number < 1000) return ones[Math.floor(number / 100)] + ' Hundred' + (number % 100 !== 0 ? ' ' + numberToWords(number % 100) : '');
            if (number < 100000) return numberToWords(Math.floor(number / 1000)) + ' Thousand' + (number % 1000 !== 0 ? ' ' + numberToWords(number % 1000) : '');
            if (number < 10000000) return numberToWords(Math.floor(number / 100000)) + ' Lakh' + (number % 100000 !== 0 ? ' ' + numberToWords(number % 100000) : '');
            return number.toString(); 
        }