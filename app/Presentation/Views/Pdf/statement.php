<?php if (!defined('FINZO_ROOT')) die('Direct access forbidden'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Statement - <?= htmlspecialchars($user['full_name'] ?? 'Account Holder') ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        @page { size: A4 portrait; margin: 10mm 12mm 12mm 12mm; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #0f172a;
            color: #0f172a;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 24px 16px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .action-bar {
            width: 100%;
            max-width: 900px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(30, 41, 59, 0.95);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 16px;
            padding: 10px 18px;
            margin-bottom: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.35);
        }
        .action-bar-title {
            color: #e2e8f0;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .action-bar-buttons {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn {
            border: none;
            outline: none;
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 800;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
        }
        .btn-print {
            background-color: #0284c7;
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(2, 132, 199, 0.4);
        }
        .btn-print:hover { background-color: #0369a1; }
        .btn-close {
            background-color: #334155;
            color: #f1f5f9;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .btn-close:hover { background-color: #475569; color: #ffffff; }

        .statement-card {
            background: #ffffff;
            width: 100%;
            max-width: 900px;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
            padding: 36px 32px;
            border: 1px solid #e2e8f0;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            color: #000000;
        }

        .bank-header {
            border-bottom: 2px solid #000000;
            padding-bottom: 12px;
            margin-bottom: 16px;
            text-align: center;
        }
        .bank-title {
            font-size: 26px;
            font-weight: 900;
            letter-spacing: 2px;
            text-transform: uppercase;
            font-family: Georgia, serif;
            color: #000000;
        }
        .bank-sub {
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-top: 3px;
            color: #000000;
        }
        .bank-meta {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            font-weight: 800;
            border-top: 1px solid #000000;
            padding-top: 8px;
            margin-top: 8px;
            color: #000000;
        }

        .table-grid {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000000;
            margin-bottom: 16px;
            font-size: 11px;
        }
        .table-grid td, .table-grid th {
            border: 1px solid #000000;
            padding: 5px 8px;
            vertical-align: middle;
        }
        .table-grid th {
            background-color: #f1f5f9;
            font-weight: 900;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.05em;
        }
        .lbl {
            font-weight: 800;
            background-color: #f8fafc;
            width: 25%;
            color: #000000;
        }
        .val {
            font-weight: 800;
            width: 25%;
            color: #000000;
        }

        .summary-box {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000000;
            margin-bottom: 16px;
            font-size: 11px;
            text-align: center;
        }
        .summary-box td {
            border: 1px solid #000000;
            padding: 8px 10px;
        }
        .summary-title {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .summary-amt {
            font-size: 15px;
            font-weight: 900;
            margin-top: 2px;
        }

        .footer-notice {
            border-top: 1px solid #000000;
            padding-top: 8px;
            font-size: 9.5px;
            line-height: 1.4;
            color: #000000;
        }

        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                color: #000000 !important;
            }
            .action-bar, .no-print {
                display: none !important;
            }
            .statement-card {
                box-shadow: none !important;
                border: 2px solid #000000 !important;
                max-width: 100% !important;
                padding: 16px !important;
                margin: 0 !important;
            }
            table, .table-grid, .summary-box {
                page-break-inside: auto !important;
            }
            tr {
                page-break-inside: avoid !important;
                page-break-after: auto !important;
            }
            thead {
                display: table-header-group !important;
            }
        }
    </style>
</head>
<body>

    <div class="action-bar no-print">
        <div class="action-bar-title">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Official Account Statement
        </div>
        <div class="action-bar-buttons">
            <button id="btn-print-statement" class="btn btn-print">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Print Statement
            </button>
            <button id="btn-close-statement" class="btn btn-close">
                Close
            </button>
        </div>
    </div>

    <div class="statement-card">
        <!-- Bank Header -->
        <div class="bank-header">
            <div class="bank-title">FINZO BANK</div>
            <div class="bank-sub">SAVINGS / CURRENT ACCOUNT PASSBOOK & STATEMENT</div>
            <div class="bank-meta">
                <span>BRANCH: MAIN BRANCH, MUMBAI</span>
                <span>IFSC: FINZ0002026</span>
                <span>MICR: 400026002</span>
            </div>
        </div>

        <?php
        $primaryAcc = $account ?? ($accounts[0] ?? []);
        $accNo = $primaryAcc['account_no'] ?? 'N/A';
        $accType = strtoupper($primaryAcc['account_type'] ?? 'SAVINGS') . ' ACCOUNT';
        $periodStr = date('d-m-Y', strtotime($fromDate)) . ' TO ' . date('d-m-Y', strtotime($toDate));

        $totalCredits = 0;
        $totalDebits = 0;
        $accountIds = array_column($accounts ?? [$primaryAcc], 'id');

        foreach ($transactions as $t) {
            $isCredit = in_array($t['type'], ['deposit', 'interest_credit']) || ($t['type'] === 'transfer' && in_array($t['to_account_id'], $accountIds));
            $amt = (float)$t['amount'];
            if ($isCredit) $totalCredits += $amt;
            else $totalDebits += $amt;
        }

        $currentBalance = (float)($primaryAcc['balance'] ?? 0);
        ?>

        <!-- Account Particulars Box -->
        <table class="table-grid">
            <tr>
                <td class="lbl">A/C HOLDER NAME</td>
                <td class="val" style="font-size: 12px;"><?= htmlspecialchars(strtoupper($user['full_name'] ?? 'ACCOUNT HOLDER')) ?></td>
                <td class="lbl">ACCOUNT NO.</td>
                <td class="val" style="font-size: 12px;"><?= htmlspecialchars($accNo) ?></td>
            </tr>
            <tr>
                <td class="lbl">CUSTOMER ID (CIF)</td>
                <td class="val"><?= htmlspecialchars($user['customer_id'] ?? 'FNZ-USR') ?></td>
                <td class="lbl">ACCOUNT TYPE</td>
                <td class="val"><?= htmlspecialchars($accType) ?></td>
            </tr>
            <tr>
                <td class="lbl">MOBILE / EMAIL</td>
                <td class="val"><?= htmlspecialchars($user['phone'] ?? $user['email'] ?? 'N/A') ?></td>
                <td class="lbl">STATEMENT PERIOD</td>
                <td class="val"><?= htmlspecialchars($periodStr) ?></td>
            </tr>
            <tr>
                <td class="lbl">BRANCH / ADDRESS</td>
                <td class="val" colspan="3">MAIN BRANCH, MUMBAI • FINZ0002026</td>
            </tr>
        </table>

        <!-- Summary Metrics Box -->
        <table class="summary-box">
            <tr>
                <td style="width: 33.33%;">
                    <div class="summary-title">TOTAL WITHDRAWALS (DR)</div>
                    <div class="summary-amt">₹ <?= number_format($totalDebits, 2) ?></div>
                </td>
                <td style="width: 33.33%;">
                    <div class="summary-title">TOTAL DEPOSITS (CR)</div>
                    <div class="summary-amt">₹ <?= number_format($totalCredits, 2) ?></div>
                </td>
                <td style="width: 33.34%; background-color: #f1f5f9;">
                    <div class="summary-title">CLOSING AVAILABLE BALANCE</div>
                    <div class="summary-amt">₹ <?= number_format($currentBalance, 2) ?></div>
                </td>
            </tr>
        </table>

        <!-- Passbook Ledger Table -->
        <table class="table-grid">
            <thead>
                <tr>
                    <th style="width: 38px; text-align: center;">LINE</th>
                    <th style="width: 85px; text-align: center;">DATE</th>
                    <th style="text-align: left;">PARTICULARS / TRANSACTION ID</th>
                    <th style="width: 105px; text-align: right;">WITHDRAWAL (DR) ₹</th>
                    <th style="width: 105px; text-align: right;">DEPOSIT (CR) ₹</th>
                    <th style="width: 110px; text-align: right;">BALANCE ₹</th>
                    <th style="width: 40px; text-align: center;">INIT</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transactions)) : ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 24px; font-weight: bold;">No transactions found for the selected period.</td>
                </tr>
                <?php else : ?>
                    <?php 
                    $lineNo = 1;
                    foreach ($transactions as $t) : 
                        $isCredit = in_array($t['type'], ['deposit', 'interest_credit']) || ($t['type'] === 'transfer' && in_array($t['to_account_id'], $accountIds));
                        $amt = (float)$t['amount'];
                        $dateFormatted = date('d-m-Y', strtotime($t['created_at']));
                        $particulars = strtoupper($t['description'] ?: $t['type']);
                        if (!empty($t['txn_id'])) $particulars .= ' / ' . $t['txn_id'];
                    ?>
                    <tr>
                        <td style="text-align: center;"><?= $lineNo++ ?></td>
                        <td style="text-align: center; white-space: nowrap;"><?= $dateFormatted ?></td>
                        <td style="text-transform: uppercase; font-size: 10.5px;"><?= htmlspecialchars($particulars) ?></td>
                        <td style="text-align: right;"><?= !$isCredit ? number_format($amt, 2) : '-' ?></td>
                        <td style="text-align: right;"><?= $isCredit ? number_format($amt, 2) : '-' ?></td>
                        <td style="text-align: right; font-weight: bold;"><?= number_format((float)($t['balance_after'] ?? 0), 2) ?></td>
                        <td style="text-align: center; font-size: 10px;">SYS</td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Footer Notice -->
        <div class="footer-notice">
            <div>* Please examine this passbook/statement immediately. Discrepancies, if any, should be reported within 15 days.</div>
            <div style="display: flex; justify-content: space-between; margin-top: 4px; font-weight: bold; font-size: 9px;">
                <span>* OFFICIAL COMPUTER GENERATED BANK STATEMENT — NO PHYSICAL SIGNATURE REQUIRED • FINZO CORE BANKING</span>
                <span>PAGE 1 OF 1</span>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('btn-print-statement')?.addEventListener('click', function() {
            window.print();
        });
        document.getElementById('btn-close-statement')?.addEventListener('click', function() {
            window.close();
        });

        // Automatic print prompt when loaded
        window.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                window.print();
            }, 400);
        });
    </script>
</body>
</html>