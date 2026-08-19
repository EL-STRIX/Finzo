<?php if (!defined('FINZO_ROOT')) die('Direct access forbidden'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account Statement - <?= htmlspecialchars($user['full_name']) ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        @page { size: A4 portrait; margin: 15mm; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; background-color: #0f172a; padding: 30px 16px; color: #0f172a; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .statement-card { background: white; max-width: 900px; margin: 0 auto; box-shadow: 0 20px 40px rgba(0,0,0,0.3); border-radius: 20px; padding: 40px; border: 1px solid #e2e8f0; }
        .finzo-logo { font-weight: 900; font-size: 30px; letter-spacing: -1px; color: #002645; }
        .mono { font-family: monospace, Courier, monospace; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; border-bottom: 2px solid #002645; padding-bottom: 20px; }
        .btn-print { background: #0284c7; color: white; border: none; padding: 10px 20px; border-radius: 10px; font-weight: 800; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; }
        .accounts-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-bottom: 24px; }
        .account-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        .table th { padding: 10px 12px; border-bottom: 2px solid #e2e8f0; text-align: left; font-size: 10px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.08em; }
        .table td { padding: 12px; border-bottom: 1px dashed #e2e8f0; font-size: 12px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 6px; font-size: 10px; font-weight: 800; text-transform: uppercase; background: #e2e8f0; color: #475569; }
        .footer { border-top: 1px solid #e2e8f0; padding-top: 20px; margin-top: 40px; text-align: center; font-size: 10px; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; }
        
        @media print {
            body { background: white !important; padding: 0 !important; }
            .statement-card { box-shadow: none !important; max-width: 100% !important; border: 1px solid #94a3b8 !important; padding: 20px !important; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    
    <div style="text-align: center; margin-bottom: 20px;" class="no-print">
        <button onclick="window.print()" class="btn-print">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Print Statement
        </button>
    </div>

    <div class="statement-card">
        <div class="header">
            <div>
                <div class="finzo-logo">Finzo</div>
                <div style="font-size:11px;color:#64748b;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;margin-top:4px;">Official Account Statement</div>
                <div style="font-size:12px;font-weight:600;color:#334155;margin-top:8px;">Generated on <?= date('d M Y, h:i A') ?></div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:20px;font-weight:900;color:#0f172a;"><?= htmlspecialchars($user['full_name']) ?></div>
                <div class="mono" style="font-size:12px;color:#64748b;font-weight:700;margin-top:2px;"><?= htmlspecialchars($user['customer_id']) ?></div>
                <div style="font-size:12px;color:#64748b;margin-top:2px;"><?= htmlspecialchars($user['email']) ?></div>
                <div style="font-size:12px;color:#64748b;margin-top:2px;"><?= htmlspecialchars($user['phone']) ?></div>
            </div>
        </div>

        <div style="margin-bottom: 24px;">
            <div style="font-size:11px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:10px;">Account Summary</div>
            <div class="accounts-grid">
                <?php foreach ($accounts as $acc) : ?>
                <div class="account-box">
                    <div style="font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:2px;"><?= htmlspecialchars($acc['account_type']) ?> Account</div>
                    <div class="mono" style="font-size:12px;font-weight:700;color:#334155;margin-bottom:6px;"><?= htmlspecialchars($acc['account_no']) ?></div>
                    <div style="font-size:18px;font-weight:900;color:#002645;"><?= CURRENCY_SYMBOL . ' ' . number_format($acc['balance'], 2) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div>
            <div style="font-size:11px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:10px;">Transaction History</div>
            
            <?php if (\App\Shared\Core\Validation\Validator::isEmpty($transactions)) : ?>
                <div style="text-align:center;padding:30px;color:#94a3b8;font-size:13px;font-weight:700;background:#f8fafc;border-radius:12px;">No transactions recorded for this period.</div>
            <?php else : ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Details</th>
                            <th>Type</th>
                            <th style="text-align:right;">Amount</th>
                            <th style="text-align:right;">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $txn) :
                            $isCredit = in_array($txn['type'], ['deposit', 'interest_credit']) || ($txn['type'] === 'transfer' && $txn['to_account_id'] == ($selectedAccount['id'] ?? 0));
                            $color = $isCredit ? '#059669' : '#e11d48';
                            $prefix = $isCredit ? '+' : '-';
                            ?>
                        <tr>
                            <td style="color:#64748b;white-space:nowrap;"><?= date('d M Y h:i A', strtotime($txn['created_at'])) ?></td>
                            <td>
                                <div class="mono" style="font-size:10px;color:#94a3b8;font-weight:700;"><?= htmlspecialchars($txn['txn_id']) ?></div>
                                <div style="font-weight:700;color:#0f172a;margin-top:2px;"><?= htmlspecialchars($txn['description'] ?: 'Transaction') ?></div>
                                <?php if ($txn['reference_note']) : ?>
                                    <div style="font-size:10px;color:#64748b;font-style:italic;margin-top:2px;">Ref: <?= htmlspecialchars($txn['reference_note']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge"><?= htmlspecialchars($txn['type']) ?></span>
                            </td>
                            <td style="text-align:right;">
                                <div style="font-weight:900;font-size:13px;color:<?= $color ?>;"><?= $prefix . CURRENCY_SYMBOL . number_format($txn['amount'], 2) ?></div>
                            </td>
                            <td style="text-align:right;">
                                <div style="font-weight:700;font-size:12px;color:#0f172a;"><?= CURRENCY_SYMBOL . number_format($txn['balance_after'] ?? 0, 2) ?></div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="footer">
            This is a computer-generated statement. No physical signature is required.
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