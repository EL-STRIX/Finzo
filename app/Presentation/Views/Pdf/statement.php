<?php if (!defined('FINZO_ROOT')) die('Direct access forbidden'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account Statement - <?= htmlspecialchars($user['full_name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; padding: 40px; }
        .statement-card { background: white; max-width: 900px; margin: 0 auto; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); border-radius: 24px; padding: 48px; position: relative; overflow: hidden; min-height: 1000px;}
        .finzo-logo { font-weight: 800; font-size: 32px; letter-spacing: -1px; color: #002645; }
        .mono { font-family: 'JetBrains Mono', monospace; }
        
        @media print {
            body { background: white; padding: 0; }
            .statement-card { box-shadow: none; max-width: 100%; border-radius: 0; padding: 20px; min-height: auto;}
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    
    <div class="text-center mb-6 no-print">
        <button onclick="window.print()" class="bg-[#002645] hover:bg-blue-900 text-white px-6 py-3 rounded-xl font-bold text-sm shadow-lg hover:shadow-xl transition-all flex items-center gap-2 mx-auto">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Print Statement
        </button>
    </div>

    <div class="statement-card">
        
        <div class="flex justify-between items-start mb-10 border-b border-slate-100 pb-10">
            <div>
                <div class="finzo-logo">Finzo</div>
                <div class="text-xs text-slate-500 font-medium tracking-wide uppercase mt-1">Official Account Statement</div>
                <div class="text-sm font-semibold text-slate-700 mt-4">Generated on <?= date('d M Y, h:i A') ?></div>
            </div>
            <div class="text-right">
                <div class="text-2xl font-black text-slate-800"><?= htmlspecialchars($user['full_name']) ?></div>
                <div class="text-sm text-slate-500 mt-1 mono"><?= htmlspecialchars($user['customer_id']) ?></div>
                <div class="text-sm text-slate-500 mt-1"><?= htmlspecialchars($user['email']) ?></div>
                <div class="text-sm text-slate-500 mt-1"><?= htmlspecialchars($user['phone']) ?></div>
            </div>
        </div>

        <div class="mb-10">
            <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Account Summary</h4>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <?php foreach ($accounts as $acc) : ?>
                <div class="bg-slate-50 rounded-xl p-5 border border-slate-100">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1"><?= htmlspecialchars($acc['account_type']) ?> Account</div>
                    <div class="text-sm font-semibold mono text-slate-600 mb-3"><?= htmlspecialchars($acc['account_no']) ?></div>
                    <div class="text-xl font-black text-[#002645]"><?= CURRENCY_SYMBOL . ' ' . number_format($acc['balance'], 2) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div>
            <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Transaction History</h4>
            
            <?php if (\App\Shared\Core\Validation\Validator::isEmpty($transactions)) : ?>
                <div class="text-center py-10 text-slate-400 text-sm font-semibold bg-slate-50 rounded-xl">No transactions found.</div>
            <?php else : ?>
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr>
                            <th class="py-3 px-4 border-b-2 border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest">Date</th>
                            <th class="py-3 px-4 border-b-2 border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest">Details</th>
                            <th class="py-3 px-4 border-b-2 border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest">Type</th>
                            <th class="py-3 px-4 border-b-2 border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($transactions as $txn) :
                            $is_credit = in_array($txn['to_acc'], array_column($accounts, 'account_no'));
                            $is_debit = in_array($txn['from_acc'], array_column($accounts, 'account_no'));

                            $sign = '';
                            $color = 'text-slate-700';
                            $counterparty = '';

                            if ($is_credit && !$is_debit) {
                                $sign = '+';
                                $color = 'text-emerald-600';
                                $counterparty = 'From: ' . htmlspecialchars($txn['from_name'] ?? '') . ' (' . htmlspecialchars($txn['from_acc'] ?? '') . ')';
                            } elseif ($is_debit && !$is_credit) {
                                $sign = '-';
                                $color = 'text-red-500';
                                $counterparty = 'To: ' . htmlspecialchars($txn['to_name'] ?? '') . ' (' . htmlspecialchars($txn['to_acc'] ?? '') . ')';
                            } elseif ($is_credit && $is_debit) {
                                $sign = '';
                                $color = 'text-slate-500';
                                $counterparty = 'Internal Transfer: ' . htmlspecialchars($txn['from_acc'] ?? '') . ' &rarr; ' . htmlspecialchars($txn['to_acc'] ?? '');
                            }
                            ?>
                        <tr>
                            <td class="py-4 px-4 text-xs text-slate-500 font-medium whitespace-nowrap align-top"><?= date('d M Y', strtotime($txn['created_at'])) ?></td>
                            <td class="py-4 px-4 align-top">
                                <div class="text-sm font-semibold text-slate-700"><?= htmlspecialchars($txn['description'] ?: 'Transaction') ?></div>
                                <?php if ($counterparty) : ?>
                                    <div class="text-[10px] text-slate-500 mt-1"><?= $counterparty ?></div>
                                <?php endif; ?>
                                <?php if ($txn['reference_note']) : ?>
                                    <div class="text-[10px] text-slate-400 mt-0.5 italic">Ref: <?= htmlspecialchars($txn['reference_note']) ?></div>
                                <?php endif; ?>
                                <?php if ($txn['staff_name']) : ?>
                                    <div class="text-[10px] text-slate-400 mt-0.5">Processed By: <?= htmlspecialchars($txn['staff_name']) ?></div>
                                <?php endif; ?>
                                <div class="text-[10px] mono text-slate-400 mt-1 uppercase"><?= htmlspecialchars($txn['txn_id']) ?></div>
                            </td>
                            <td class="py-4 px-4 align-top">
                                <span class="text-[10px] font-bold uppercase tracking-widest bg-slate-100 px-2 py-1 rounded text-slate-500"><?= htmlspecialchars($txn['type']) ?></span>
                            </td>
                            <td class="py-4 px-4 text-right align-top">
                                <div class="text-sm font-black <?= $color ?>"><?= $sign . ' ' . CURRENCY_SYMBOL . number_format($txn['amount'], 2) ?></div>
                                <?php if ($txn['balance_after'] !== null) : ?>
                                    <div class="text-[10px] font-semibold text-slate-400 mt-1">Bal: <?= CURRENCY_SYMBOL . number_format($txn['balance_after'], 2) ?></div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="border-t-2 border-slate-900 pt-6 mt-16 text-center">
            <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-widest leading-relaxed">
                This is a computer-generated statement.<br>No physical signature is required.
            </p>
        </div>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>