<?php if (!defined('FINZO_ROOT')) die('Direct access forbidden'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Receipt - <?= htmlspecialchars($txn['txn_id']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 15mm 15mm 15mm;
        }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .receipt-container {
            max-width: 620px;
            margin: 0 auto;
        }
        .receipt-card {
            background: #ffffff;
            border-radius: 24px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
            position: relative;
            overflow: hidden;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 72px;
            font-weight: 900;
            color: rgba(15, 23, 42, 0.025);
            white-space: nowrap;
            pointer-events: none;
            z-index: 0;
            letter-spacing: 0.1em;
            user-select: none;
        }
        .receipt-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 12px 0;
            border-bottom: 1px dashed #e2e8f0;
        }
        .receipt-row:last-child {
            border-bottom: none;
        }
        .mono {
            font-family: 'JetBrains Mono', monospace;
        }
        
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .receipt-container {
                max-width: 100% !important;
                margin: 0 auto !important;
            }
            .receipt-card {
                border: 1px solid #cbd5e1 !important;
                box-shadow: none !important;
                border-radius: 16px !important;
                padding: 28px !important;
            }
        }
    </style>
</head>
<body class="p-4 sm:p-8 min-h-screen flex flex-col items-center justify-center">
    
    <!-- Action Bar (Hidden during Print) -->
    <div class="receipt-container w-full mb-5 no-print flex items-center justify-between gap-3">
        <div class="text-xs font-bold text-slate-500 uppercase tracking-widest">
            Receipt Preview
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="bg-[#002645] hover:bg-slate-800 text-white px-5 py-2 rounded-xl font-bold text-xs shadow-md hover:shadow-lg transition-all flex items-center gap-1.5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Print Receipt</span>
            </button>
            <button onclick="window.close()" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-4 py-2 rounded-xl font-bold text-xs transition-all cursor-pointer">
                Close
            </button>
        </div>
    </div>

    <!-- Official Bank Receipt Card -->
    <div class="receipt-container w-full">
        <div class="receipt-card p-8 sm:p-10 relative">
            <div class="watermark">FINZO OFFICIAL</div>
            
            <div class="relative z-10">
                <!-- Header: Finzo Branding & Receipt Status -->
                <div class="flex justify-between items-start pb-6 border-b border-slate-200 mb-6">
                    <div>
                        <div class="flex items-center gap-2">
                            <div class="text-3xl font-black text-[#002645] tracking-tight">Finzo</div>
                        </div>
                        <div class="text-[11px] font-bold text-slate-500 tracking-wider uppercase mt-1">Official Banking Transaction Receipt</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Finzo Global Banking Ltd. • Support: 1800-FINZO-BANK</div>
                    </div>
                    <div class="text-right">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Status</div>
                        <div class="inline-block px-3 py-1 rounded-lg border font-black text-xs uppercase tracking-widest <?= $statusColor ?>">
                            <?= htmlspecialchars($txn['status']) ?>
                        </div>
                    </div>
                </div>

                <!-- Transaction Amount Box (Centered) -->
                <div class="bg-slate-50/80 rounded-2xl p-6 mb-6 text-center border border-slate-100">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Transaction Amount</div>
                    <div class="text-3xl sm:text-4xl font-black text-[#002645] tracking-tight mb-2"><?= $formattedAmount ?></div>
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-slate-200/70 text-slate-700">
                        <?= htmlspecialchars(str_replace('_', ' ', $txn['type'])) ?>
                    </div>
                </div>

                <!-- Transaction Details Grid -->
                <div class="space-y-1 text-xs mb-6">
                    <div class="receipt-row">
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Transaction ID</span>
                        <span class="font-bold mono text-slate-900"><?= htmlspecialchars($txn['txn_id']) ?></span>
                    </div>

                    <div class="receipt-row">
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Date & Time</span>
                        <span class="font-bold text-slate-900"><?= date('d M Y, h:i:s A', strtotime($txn['created_at'])) ?></span>
                    </div>
                    
                    <?php if ($txn['type'] === 'transfer') : ?>
                        <div class="receipt-row">
                            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">From Account (Sender)</span>
                            <div class="text-right">
                                <div class="font-bold text-slate-900"><?= htmlspecialchars($txn['from_name'] === 'N/A' ? 'Internal Settlement' : $txn['from_name']) ?></div>
                                <div class="font-mono text-[11px] text-slate-500 font-semibold mt-0.5"><?= htmlspecialchars($txn['from_account_no'] ?? 'N/A') ?></div>
                            </div>
                        </div>
                        <div class="receipt-row">
                            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">To Account (Beneficiary)</span>
                            <div class="text-right">
                                <div class="font-bold text-slate-900"><?= htmlspecialchars($txn['to_name'] === 'N/A' ? 'Internal Settlement' : $txn['to_name']) ?></div>
                                <div class="font-mono text-[11px] text-slate-500 font-semibold mt-0.5"><?= htmlspecialchars($txn['to_account_no'] ?? 'N/A') ?></div>
                            </div>
                        </div>
                    <?php elseif ($txn['type'] === 'deposit' || $txn['type'] === 'interest_credit') : ?>
                        <div class="receipt-row">
                            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Source Channel</span>
                            <div class="text-right font-bold text-slate-900">
                                <?php if (!empty($txn['staff_name']) || !empty($txn['staff_id'])) : ?>
                                    OTC Branch Deposit
                                <?php elseif ($txn['type'] === 'interest_credit') : ?>
                                    Finzo Automated Treasury Interest
                                <?php else : ?>
                                    Electronic Direct Inflow
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="receipt-row">
                            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Credited Account</span>
                            <div class="text-right">
                                <div class="font-bold text-slate-900"><?= htmlspecialchars($txn['to_name'] === 'N/A' ? 'Settlement Account' : $txn['to_name']) ?></div>
                                <div class="font-mono text-[11px] text-slate-500 font-semibold mt-0.5"><?= htmlspecialchars($txn['to_account_no'] ?? 'N/A') ?></div>
                            </div>
                        </div>
                    <?php elseif ($txn['type'] === 'withdrawal') : ?>
                        <div class="receipt-row">
                            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Debited Account</span>
                            <div class="text-right">
                                <div class="font-bold text-slate-900"><?= htmlspecialchars($txn['from_name'] === 'N/A' ? 'Settlement Account' : $txn['from_name']) ?></div>
                                <div class="font-mono text-[11px] text-slate-500 font-semibold mt-0.5"><?= htmlspecialchars($txn['from_account_no'] ?? 'N/A') ?></div>
                            </div>
                        </div>
                        <div class="receipt-row">
                            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Payout Method</span>
                            <div class="text-right font-bold text-slate-900">
                                <?= !empty($txn['staff_name']) || !empty($txn['staff_id']) ? 'OTC Branch Cash Payout' : 'ATM / Direct Cash Withdrawal' ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Staff & Branch Processing Information -->
                    <?php if (!empty($txn['staff_name']) || !empty($txn['staff_id'])) : ?>
                        <div class="receipt-row bg-slate-50/50 p-2 rounded-lg my-1">
                            <span class="text-[11px] font-bold text-slate-600 uppercase tracking-wider">Processed By Staff</span>
                            <div class="text-right">
                                <div class="font-bold text-slate-900"><?= htmlspecialchars($txn['staff_name'] ?: 'Branch Staff') ?> (<?= htmlspecialchars($txn['staff_id']) ?>)</div>
                                <?php if (!empty($txn['branch_name'])) : ?>
                                    <div class="text-[11px] text-slate-500 font-semibold mt-0.5"><?= htmlspecialchars($txn['branch_name']) ?> Branch</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($txn['description'])) : ?>
                    <div class="receipt-row">
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Description</span>
                        <span class="font-semibold text-slate-900 text-right max-w-[280px]"><?= htmlspecialchars($txn['description']) ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($txn['reference_note'])) : ?>
                    <div class="receipt-row">
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Audit / Reference Note</span>
                        <span class="font-mono text-slate-700 text-right text-[11px] max-w-[280px]"><?= htmlspecialchars($txn['reference_note']) ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($txn['balance_after'] !== null) : ?>
                    <div class="receipt-row">
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Ledger Balance After</span>
                        <span class="font-black text-slate-900"><?= CURRENCY_SYMBOL . ' ' . number_format($txn['balance_after'], 2) ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Footer Disclaimer -->
                <div class="border-t border-slate-200 pt-5 text-center">
                    <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-widest leading-relaxed">
                        This is an official computer-generated banking record.<br>Finzo Enterprise Core Banking Authentication Verified.
                    </p>
                    <p class="text-[10px] text-slate-400 mt-1.5 font-mono">
                        Generated on <?= date('d M Y, h:i:s A') ?>
                    </p>
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