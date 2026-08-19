<?php if (!defined('FINZO_ROOT')) die('Direct access forbidden'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Receipt - <?= htmlspecialchars($txn['txn_id']) ?></title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        @page {
            size: A4 portrait;
            margin: 15mm 15mm 15mm 15mm;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #0f172a;
            color: #0f172a;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .action-bar {
            width: 100%;
            max-width: 580px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
        }
        .action-bar-title {
            color: #94a3b8;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        .action-bar-buttons {
            display: flex;
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
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3);
        }
        .btn-print:hover {
            background-color: #0369a1;
        }
        .btn-close {
            background-color: #334155;
            color: #f8fafc;
        }
        .btn-close:hover {
            background-color: #475569;
        }
        .receipt-card {
            background-color: #ffffff;
            width: 100%;
            max-width: 580px;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            padding: 36px 32px;
            position: relative;
            overflow: hidden;
        }
        .watermark {
            position: absolute;
            top: 52%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 64px;
            font-weight: 900;
            color: rgba(15, 23, 42, 0.03);
            white-space: nowrap;
            pointer-events: none;
            z-index: 0;
            letter-spacing: 0.12em;
            user-select: none;
        }
        .content {
            position: relative;
            z-index: 10;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 20px;
            border-bottom: 2px solid #002645;
            margin-bottom: 22px;
        }
        .logo-title {
            font-size: 28px;
            font-weight: 900;
            color: #002645;
            letter-spacing: -0.5px;
            line-height: 1;
        }
        .logo-subtitle {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-top: 5px;
        }
        .logo-contact {
            font-size: 10px;
            color: #94a3b8;
            margin-top: 2px;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 8px;
            font-weight: 800;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .status-completed {
            background-color: #ecfdf5;
            color: #059669;
            border: 1px solid #a7f3d0;
        }
        .status-reversed {
            background-color: #fff1f2;
            color: #e11d48;
            border: 1px solid #fecdd3;
        }
        .status-pending {
            background-color: #fffbeb;
            color: #d97706;
            border: 1px solid #fde68a;
        }
        .amount-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        .amount-label {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 4px;
        }
        .amount-val {
            font-size: 34px;
            font-weight: 900;
            color: #002645;
            letter-spacing: -0.5px;
            line-height: 1.1;
        }
        .type-pill {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 20px;
            background-color: #e2e8f0;
            color: #334155;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-top: 6px;
        }
        .flow-banner {
            background-color: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 12px;
            padding: 10px 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11px;
            font-weight: 700;
            color: #0369a1;
            margin-bottom: 18px;
        }
        .flow-arrow {
            font-size: 14px;
            color: #0284c7;
            font-weight: 900;
        }
        .table-rows {
            margin-bottom: 20px;
        }
        .row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 10px 0;
            border-bottom: 1px dashed #e2e8f0;
            font-size: 12px;
        }
        .row:last-child {
            border-bottom: none;
        }
        .row-label {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            flex-shrink: 0;
            margin-right: 12px;
        }
        .row-value {
            font-weight: 700;
            color: #0f172a;
            text-align: right;
            word-break: break-word;
        }
        .row-staff {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 8px 12px;
            margin: 6px 0;
        }
        .mono {
            font-family: monospace, Courier, monospace;
            font-size: 12px;
        }
        .sub-val {
            font-size: 10px;
            color: #64748b;
            font-weight: 600;
            margin-top: 2px;
        }
        .footer {
            border-top: 1px solid #e2e8f0;
            padding-top: 16px;
            text-align: center;
        }
        .footer-note {
            font-size: 10px;
            color: #94a3b8;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            line-height: 1.5;
        }
        .footer-date {
            font-size: 9px;
            color: #cbd5e1;
            font-family: monospace;
            margin-top: 4px;
        }

        @media print {
            body {
                background-color: #ffffff !important;
                padding: 0 !important;
            }
            .action-bar {
                display: none !important;
            }
            .receipt-card {
                box-shadow: none !important;
                border: 1px solid #94a3b8 !important;
                max-width: 100% !important;
                border-radius: 12px !important;
                padding: 24px !important;
            }
        }
    </style>
</head>
<body>
    
    <!-- Action Bar (Hidden during Print) -->
    <div class="action-bar">
        <div class="action-bar-title">Official Receipt Preview</div>
        <div class="action-bar-buttons">
            <button onclick="window.print()" class="btn btn-print">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Print Receipt
            </button>
            <button onclick="window.close()" class="btn btn-close">Close</button>
        </div>
    </div>

    <!-- Official Receipt Card -->
    <div class="receipt-card">
        <div class="watermark">FINZO OFFICIAL</div>
        
        <div class="content">
            <!-- Header -->
            <div class="header">
                <div>
                    <div class="logo-title">Finzo</div>
                    <div class="logo-subtitle">Official Banking Transaction Receipt</div>
                    <div class="logo-contact">Finzo Enterprise Core Banking • 1800-FINZO-BANK</div>
                </div>
                <div>
                    <?php
                        $st = strtolower($txn['status'] ?? 'completed');
                        $badgeClass = 'status-completed';
                        if ($st === 'reversed' || $st === 'failed') $badgeClass = 'status-reversed';
                        elseif ($st === 'pending') $badgeClass = 'status-pending';
                    ?>
                    <span class="status-badge <?= $badgeClass ?>"><?= htmlspecialchars($txn['status']) ?></span>
                </div>
            </div>

            <!-- Amount Box -->
            <div class="amount-box">
                <div class="amount-label">Transaction Amount</div>
                <div class="amount-val"><?= $formattedAmount ?></div>
                <span class="type-pill"><?= htmlspecialchars(str_replace('_', ' ', $txn['type'])) ?></span>
            </div>

            <!-- Transfer Account-to-Account Flow Banner -->
            <?php if ($txn['type'] === 'transfer' && !empty($txn['from_account_no']) && !empty($txn['to_account_no'])) : ?>
                <div class="flow-banner">
                    <div>
                        <span style="font-size:9px;text-transform:uppercase;color:#64748b;display:block;">Sender Account</span>
                        <span class="mono" style="color:#0f172a;"><?= htmlspecialchars($txn['from_account_no']) ?></span>
                    </div>
                    <div class="flow-arrow">➔</div>
                    <div style="text-align:right;">
                        <span style="font-size:9px;text-transform:uppercase;color:#64748b;display:block;">Beneficiary Account</span>
                        <span class="mono" style="color:#0f172a;"><?= htmlspecialchars($txn['to_account_no']) ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Details Rows -->
            <div class="table-rows">
                <div class="row">
                    <span class="row-label">Transaction ID</span>
                    <span class="row-value mono"><?= htmlspecialchars($txn['txn_id']) ?></span>
                </div>

                <div class="row">
                    <span class="row-label">Date & Time</span>
                    <span class="row-value"><?= date('d M Y, h:i:s A', strtotime($txn['created_at'])) ?></span>
                </div>
                
                <?php if ($txn['type'] === 'transfer') : ?>
                    <div class="row">
                        <span class="row-label">From Account</span>
                        <div class="row-value">
                            <div><?= htmlspecialchars($txn['from_name'] === 'N/A' ? 'Settlement Core' : $txn['from_name']) ?></div>
                            <div class="mono sub-val"><?= htmlspecialchars($txn['from_account_no'] ?? 'N/A') ?></div>
                        </div>
                    </div>
                    <div class="row">
                        <span class="row-label">To Account</span>
                        <div class="row-value">
                            <div><?= htmlspecialchars($txn['to_name'] === 'N/A' ? 'Settlement Core' : $txn['to_name']) ?></div>
                            <div class="mono sub-val"><?= htmlspecialchars($txn['to_account_no'] ?? 'N/A') ?></div>
                        </div>
                    </div>
                <?php elseif ($txn['type'] === 'deposit' || $txn['type'] === 'interest_credit') : ?>
                    <div class="row">
                        <span class="row-label">Source Channel</span>
                        <div class="row-value">
                            <?php if (!empty($txn['staff_name']) || !empty($txn['staff_id'])) : ?>
                                OTC Branch Desk
                            <?php elseif ($txn['type'] === 'interest_credit') : ?>
                                Finzo Treasury Pool
                            <?php else : ?>
                                Electronic Inflow
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row">
                        <span class="row-label">Credited To</span>
                        <div class="row-value">
                            <div><?= htmlspecialchars($txn['to_name'] === 'N/A' ? 'Settlement Account' : $txn['to_name']) ?></div>
                            <div class="mono sub-val"><?= htmlspecialchars($txn['to_account_no'] ?? 'N/A') ?></div>
                        </div>
                    </div>
                <?php elseif ($txn['type'] === 'withdrawal') : ?>
                    <div class="row">
                        <span class="row-label">Debited From</span>
                        <div class="row-value">
                            <div><?= htmlspecialchars($txn['from_name'] === 'N/A' ? 'Settlement Account' : $txn['from_name']) ?></div>
                            <div class="mono sub-val"><?= htmlspecialchars($txn['from_account_no'] ?? 'N/A') ?></div>
                        </div>
                    </div>
                    <div class="row">
                        <span class="row-label">Payout Channel</span>
                        <div class="row-value">
                            <?= (!empty($txn['staff_name']) || !empty($txn['staff_id'])) ? 'OTC Branch Cash Payout' : 'ATM / Direct Cash Withdrawal' ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Staff & Branch Processing Information -->
                <?php if (!empty($txn['staff_name']) || !empty($txn['staff_id'])) : ?>
                    <div class="row row-staff">
                        <span class="row-label">Processed By Staff</span>
                        <div class="row-value">
                            <div><?= htmlspecialchars($txn['staff_name'] ?: 'Branch Staff') ?> (<?= htmlspecialchars($txn['staff_id']) ?>)</div>
                            <?php if (!empty($txn['branch_name'])) : ?>
                                <div class="sub-val"><?= htmlspecialchars($txn['branch_name']) ?> Branch</div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($txn['description'])) : ?>
                <div class="row">
                    <span class="row-label">Description</span>
                    <span class="row-value" style="max-width:280px;"><?= htmlspecialchars($txn['description']) ?></span>
                </div>
                <?php endif; ?>

                <?php if (!empty($txn['reference_note'])) : ?>
                <div class="row">
                    <span class="row-label">Reference / Note</span>
                    <span class="row-value mono" style="font-size:11px;max-width:280px;"><?= htmlspecialchars($txn['reference_note']) ?></span>
                </div>
                <?php endif; ?>
                
                <?php if ($txn['balance_after'] !== null) : ?>
                <div class="row">
                    <span class="row-label">Balance After</span>
                    <span class="row-value" style="font-size:14px;font-weight:900;color:#002645;"><?= CURRENCY_SYMBOL . ' ' . number_format($txn['balance_after'], 2) ?></span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Footer -->
            <div class="footer">
                <div class="footer-note">
                    This is an official computer-generated banking receipt.<br>Finzo Enterprise Core Banking Authentication Verified.
                </div>
                <div class="footer-date">
                    Generated on <?= date('d M Y, h:i:s A') ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Automatic print prompt when loaded
        window.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                window.print();
            }, 400);
        });
    </script>
</body>
</html>