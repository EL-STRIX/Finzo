$(document).ready(async function() {
            await fetchStaffSession();
        });

        // --- Withdrawal Logic ---
        const steps = $('.step-indicator');
        function updateSteps(num) {
            steps.each(function() {
                const s = parseInt($(this).data('step'));
                $(this).removeClass('active completed');
                if (s < num) $(this).addClass('completed');
                else if (s === num) $(this).addClass('active');
            });
        }
        updateSteps(1);

        let verifiedAccountNo = '';
        let verifiedName = '';
        let currentBalance = 0;
        let currentLimit = 0;

        $('#find-btn').on('click', async function() {
            const acc = $('#account-lookup').val().trim();
            if (!acc) return;
            const btn = $(this);
            const originalText = btn.html();
            btn.html('<span class="material-symbols-outlined animate-spin text-[18px]">sync</span> <span>Searching</span>');
            
            try {
                const res = await api.get('/staff/account-lookup.php?account_no=' + encodeURIComponent(acc));
                btn.html(originalText);
                
                if(res.success && res.found && res.account) {
                    $('#account-success').removeClass('hidden');
                    $('#account-error').addClass('hidden');
                    
                    verifiedAccountNo = res.account.account_no;
                    verifiedName = res.account.name;
                    currentBalance = parseFloat(res.account.balance) || 0;
                    currentLimit = parseFloat(res.account.daily_limit_left) || 0;
                    
                    $('#res-name').text(res.account.name);
                    $('#res-acc').text(res.account.account_no);
                    $('#confirm-acc').text(res.account.account_no);
                    const init = res.account.name.split(' ').map(n=>n[0]).join('').substring(0,2).toUpperCase();
                    $('#res-initials').text(init);
                    
                    $('.account-type-val').text(res.account.account_type);
                    $('.current-balance-val').text(formatINR(currentBalance));
                    $('.daily-limit-val').text(formatINR(currentLimit));
                    
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

        window.resetForm = function() {
            $('#account-lookup').val('');
            $('#account-success, #account-error').addClass('hidden');
            updateSteps(1);
        };

        window.goToStep1 = function() { 
            $('#section-1').removeClass('hidden'); 
            $('#section-2, #section-3').addClass('hidden'); 
            updateSteps(1); 
        };
        
        window.goToStep2 = function() { 
            $('#section-1, #section-3').addClass('hidden'); 
            $('#section-2').removeClass('hidden'); 
            updateSteps(2); 
            $('#sum-after').text(formatINR(currentBalance));
            $('.daily-limit-display').text(formatINR(currentLimit));
        };
        
        window.goToStep3 = function() { 
            const amt = parseFloat($('#with-amount').val()) || 0;
            if (amt <= 0) {
                alert('Please enter a valid withdrawal amount.');
                return;
            }
            if (amt > currentBalance) {
                alert('Withdrawal amount exceeds available balance of ' + formatINR(currentBalance));
                return;
            }
            $('#section-2').addClass('hidden'); 
            $('#section-3').removeClass('hidden'); 
            updateSteps(3); 
            $('#confirm-amt').text(formatINR(amt));
            $('#confirm-customer-name').text(verifiedName);
            const now = new Date();
            $('#current-date-display').text(now.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' }));
        };

        $('#with-amount').on('input', function() {
            const val = parseFloat($(this).val()) || 0;
            const $msg = $('#limit-msg');
            const $btn = $('#confirm-step-btn');

            $('#sum-amt').text(formatINR(val));
            $('#sum-after').text(formatINR(currentBalance - val));

            $msg.removeClass('hidden text-emerald-500 text-red-600 text-amber-500');
            $btn.prop('disabled', false).css('opacity', '1');

            if (val <= 0) { 
                $msg.addClass('hidden'); 
            } else if (val > currentBalance) {
                $msg.removeClass('hidden').addClass('text-red-600').text(`❌ Exceeds balance of ${formatINR(currentBalance)}`);
                $btn.prop('disabled', true).css('opacity', '0.5');
            } else if (val > currentLimit) { 
                $msg.removeClass('hidden').addClass('text-amber-500').text(`⚠️ Exceeds limit. Remaining: ${formatINR(currentLimit)}`);
                $btn.prop('disabled', true).css('opacity', '0.5');
            } else {
                $msg.removeClass('hidden').addClass('text-emerald-500').text('✅ Valid amount');
            }
        });

        $('#final-withdraw-btn').on('click', async function() {
            const btn = $(this);
            const originalText = btn.html();
            btn.prop('disabled', true).html('<span class="material-symbols-outlined animate-spin text-[18px]">sync</span> Processing...');
            
            const reqData = {
                account_no: verifiedAccountNo,
                amount: parseFloat($('#with-amount').val()),
                identity_notes: $('#with-notes').val().trim() || 'Verified by staff'
            };
            
            try {
                const res = await api.post('/staff/withdrawal.php', reqData);
                if (res.success) {
                    window.location.href = 'staff-receipt.html?id=' + res.transaction_id;
                } else {
                    alert('Error: ' + res.message);
                    btn.prop('disabled', false).html(originalText);
                }
            } catch (err) {
                alert('Connection error. Please try again.');
                btn.prop('disabled', false).html(originalText);
            }
        });