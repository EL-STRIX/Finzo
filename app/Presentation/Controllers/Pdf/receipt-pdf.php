<?php

/**
 * Finzo Banking System — Transaction Receipt Generator
 * GET endpoint: generates a beautifully styled HTML receipt ready for print.
 * Version 2.0 — Beautiful HTML Update
 */

require_once dirname(__DIR__, 3) . '/config/db.php';
require_once dirname(__DIR__, 3) . '/config/functions.php';

// Allow both user and staff to generate receipts
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!\App\Shared\Core\Validation\Validator::isSet($_SESSION['role']) || !in_array($_SESSION['role'], ['customer', 'staff', 'admin'])) {
    json_response(['success' => false, 'message' => 'Authentication required'], 401);
}

$txn_id = $this->security->cleanInput($_GET['id'] ?? $_GET['txn_id'] ?? '');
if (\App\Shared\Core\Validation\Validator::isEmpty($txn_id)) {
    die('Transaction ID is required');
}

try {
    // Fetch transaction
    $stmt = $pdo->prepare("
        SELECT
            t.txn_id, t.type, t.amount, t.balance_after, t.description,
            t.reference_note, t.status, t.created_at,
            fa.account_no as from_account_no,
            COALESCE(fu.full_name, 'N/A') as from_name,
            ta.account_no as to_account_no,
            COALESCE(tu.full_name, 'N/A') as to_name,
            COALESCE(s.staff_id, '') as staff_id,
            COALESCE(s.full_name, '') as staff_name,
            COALESCE(b.name, '') as branch_name
        FROM transactions t
        LEFT JOIN accounts fa ON t.from_account_id = fa.id
        LEFT JOIN users fu ON fa.user_id = fu.customer_id
        LEFT JOIN accounts ta ON t.to_account_id = ta.id
        LEFT JOIN users tu ON ta.user_id = tu.customer_id
        LEFT JOIN staff s ON t.processed_by_staff_id = s.id
        LEFT JOIN branches b ON s.branch_id = b.id
        WHERE t.txn_id = ?
    ");
    $stmt->execute([$txn_id]);
    $txn = $stmt->fetch();

    if (!$txn) {
        die('Transaction not found');
    }

    // Verify ownership for customers
    if ($_SESSION['role'] === 'customer') {
        $stmt = $pdo->prepare("SELECT a.id FROM accounts a JOIN users u ON a.user_id = u.customer_id WHERE u.id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user_accounts = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $stmt = $pdo->prepare("SELECT from_account_id, to_account_id FROM transactions WHERE txn_id = ?");
        $stmt->execute([$txn_id]);
        $txn_accts = $stmt->fetch();

        if (
            !in_array($txn_accts['from_account_id'], $user_accounts) &&
            !in_array($txn_accts['to_account_id'], $user_accounts)
        ) {
            die('Access denied');
        }
    }
} catch (PDOException $e) {
    \App\Shared\Core\Logging\Logger::getInstance()->error('Receipt error: ', ['context' => $e->getMessage()]);
    die('Failed to load transaction data.');
}

$statusColor = $txn['status'] === 'completed' ? 'text-emerald-600 bg-emerald-50 border-emerald-200' : 'text-amber-600 bg-amber-50 border-amber-200';
$typeClass = strtolower($txn['type']);
$formattedAmount = CURRENCY_SYMBOL . ' ' . number_format($txn['amount'], 2);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transaction Receipt - <?= htmlspecialchars($txn['txn_id']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; padding: 40px; }
        .receipt-card { background: white; max-width: 600px; margin: 0 auto; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); border-radius: 24px; padding: 48px; position: relative; overflow: hidden; }
        .finzo-logo { font-weight: 800; font-size: 32px; letter-spacing: -1px; color: #002645; }
        .watermark { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); font-size: 80px; font-weight: 900; color: rgba(0, 38, 69, 0.02); white-space: nowrap; pointer-events: none; z-index: 0; }
        .content-layer { position: relative; z-index: 10; }
        .receipt-row { display: flex; justify-content: space-between; padding: 16px 0; border-bottom: 1px dashed #e2e8f0; }
        .receipt-row:last-child { border-bottom: none; }
        .label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.1em; color: #64748b; font-weight: 700; }
        .value { font-size: 14px; color: #0f172a; font-weight: 600; }
        .value.mono { font-family: 'JetBrains Mono', monospace; font-size: 13px; }
        
        @media print {
            body { background: white; padding: 0; }
            .receipt-card { box-shadow: none; max-width: 100%; border-radius: 0; padding: 20px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    
    <div class="text-center mb-6 no-print">
        <button onclick="window.print()" class="bg-[#002645] hover:bg-blue-900 text-white px-6 py-3 rounded-xl font-bold text-sm shadow-lg hover:shadow-xl transition-all flex items-center gap-2 mx-auto">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Print Receipt
        </button>
    </div>

    <div class="receipt-card">
        <div class="watermark">FINZO VERIFIED</div>
        
        <div class="content-layer">
            <div class="flex justify-between items-start mb-10">
                <div>
                    <div class="finzo-logo">Finzo</div>
                    <div class="text-xs text-slate-500 font-medium tracking-wide uppercase mt-1">Official Banking Receipt</div>
                </div>
                <div class="text-right">
                    <div class="text-xs text-slate-400 font-bold uppercase tracking-widest mb-1">Status</div>
                    <div class="inline-block px-3 py-1 rounded-md border font-bold text-xs uppercase tracking-widest <?= $statusColor ?>">
                        <?= htmlspecialchars($txn['status']) ?>
                    </div>
                </div>
            </div>

            <div class="bg-slate-50 rounded-2xl p-6 mb-8 text-center border border-slate-100">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Transaction Amount</div>
                <div class="text-4xl font-black text-[#002645] tracking-tight"><?= $formattedAmount ?></div>
                <div class="text-xs font-bold text-slate-500 uppercase mt-2 bg-slate-200/50 inline-block px-3 py-1 rounded-full">
                    <?= htmlspecialchars($txn['type']) ?>
                </div>
            </div>

            <div class="mb-8">
                <div class="receipt-row">
                    <span class="label">Transaction ID</span>
                    <span class="value mono tracking-tight"><?= htmlspecialchars($txn['txn_id']) ?></span>
                </div>
                <div class="receipt-row">
                    <span class="label">Date & Time</span>
                    <span class="value"><?= date('d M Y, h:i A', strtotime($txn['created_at'])) ?></span>
                </div>
                
                <?php if ($txn['type'] === 'transfer') : ?>
                    <div class="receipt-row">
                        <span class="label">From Account</span>
                        <div class="text-right">
                            <div class="value"><?= htmlspecialchars($txn['from_name'] === 'N/A' ? 'Deleted User' : $txn['from_name']) ?></div>
                            <div class="value mono opacity-50 mt-1"><?= htmlspecialchars($txn['from_account_no'] ?? 'Deleted Account') ?></div>
                        </div>
                    </div>
                    <div class="receipt-row">
                        <span class="label">To Account</span>
                        <div class="text-right">
                            <div class="value"><?= htmlspecialchars($txn['to_name'] === 'N/A' ? 'Deleted User' : $txn['to_name']) ?></div>
                            <div class="value mono opacity-50 mt-1"><?= htmlspecialchars($txn['to_account_no'] ?? 'Deleted Account') ?></div>
                        </div>
                    </div>
                <?php elseif ($txn['type'] === 'deposit' || $txn['type'] === 'interest_credit') : ?>
                    <div class="receipt-row">
                        <span class="label">Credited To</span>
                        <div class="text-right">
                            <div class="value"><?= htmlspecialchars($txn['to_name'] === 'N/A' ? 'Deleted User' : $txn['to_name']) ?></div>
                            <div class="value mono opacity-50 mt-1"><?= htmlspecialchars($txn['to_account_no'] ?? 'Deleted Account') ?></div>
                        </div>
                    </div>
                <?php elseif ($txn['type'] === 'withdrawal') : ?>
                    <div class="receipt-row">
                        <span class="label">Debited From</span>
                        <div class="text-right">
                            <div class="value"><?= htmlspecialchars($txn['from_name'] === 'N/A' ? 'Deleted User' : $txn['from_name']) ?></div>
                            <div class="value mono opacity-50 mt-1"><?= htmlspecialchars($txn['from_account_no'] ?? 'Deleted Account') ?></div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($txn['description']) : ?>
                <div class="receipt-row">
                    <span class="label">Description</span>
                    <span class="value text-right max-w-[250px]"><?= htmlspecialchars($txn['description']) ?></span>
                </div>
                <?php endif; ?>

                <?php if ($txn['reference_note']) : ?>
                <div class="receipt-row">
                    <span class="label">Reference</span>
                    <span class="value mono"><?= htmlspecialchars($txn['reference_note']) ?></span>
                </div>
                <?php endif; ?>
                
                <?php if ($txn['balance_after'] !== null) : ?>
                <div class="receipt-row">
                    <span class="label">Balance After</span>
                    <span class="value"><?= CURRENCY_SYMBOL . ' ' . number_format($txn['balance_after'], 2) ?></span>
                </div>
                <?php endif; ?>
            </div>

            <div class="border-t-2 border-slate-900 pt-6 mt-6">
                <div class="text-center">
                    <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-widest leading-relaxed">
                        This is a computer-generated receipt.<br>No physical signature is required.
                    </p>
                    <p class="text-[10px] text-slate-300 mt-2 font-mono">
                        Generated on <?= date('d/m/Y H:i:s') ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto print when opened in a new tab
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
