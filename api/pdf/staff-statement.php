<?php
/**
 * Finzo Banking System — Staff Transaction Statement Generator
 * GET endpoint: generates a beautifully styled HTML statement of transactions processed by a staff.
 */

require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/config/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    die('Authentication required');
}

$staff_id = max(1, intval($_GET['staff_id'] ?? 0));

if (empty($staff_id)) {
    die('Staff ID is required');
}

if ($_SESSION['role'] === 'staff' && $_SESSION['user_id'] != $staff_id) {
    die('Access denied');
}

try {
    // Get staff details
    $stmt = $pdo->prepare("SELECT staff_id as staff_code, full_name, personal_email as email, phone, created_at FROM staff WHERE id = ?");
    $stmt->execute([$staff_id]);
    $staff = $stmt->fetch();

    if (!$staff) {
        die('Staff not found');
    }

    // Get transactions processed by this staff
    $txn_stmt = $pdo->prepare("
        SELECT
            t.txn_id, t.type, t.amount, t.created_at, t.status, t.description, t.reference_note,
            fa.account_no as from_acc, ta.account_no as to_acc,
            COALESCE(fu.full_name, 'N/A') as from_name,
            COALESCE(tu.full_name, 'N/A') as to_name
        FROM transactions t
        LEFT JOIN accounts fa ON t.from_account_id = fa.id
        LEFT JOIN users fu ON fa.user_id = fu.customer_id
        LEFT JOIN accounts ta ON t.to_account_id = ta.id
        LEFT JOIN users tu ON ta.user_id = tu.customer_id
        WHERE t.processed_by_staff_id = ?
        ORDER BY t.created_at DESC
        LIMIT 200
    ");
    $txn_stmt->execute([$staff_id]);
    $transactions = $txn_stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Statement error: " . $e->getMessage());
    die('Failed to load statement data.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Statement - <?= htmlspecialchars($staff['full_name']) ?></title>
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
            Print Staff Statement
        </button>
    </div>

    <div class="statement-card">
        
        <div class="flex justify-between items-start mb-10 border-b border-slate-100 pb-10">
            <div>
                <div class="finzo-logo">Finzo</div>
                <div class="text-xs text-slate-500 font-medium tracking-wide uppercase mt-1">Official Staff Audit Statement</div>
                <div class="text-sm font-semibold text-slate-700 mt-4">Generated on <?= date('d M Y, h:i A') ?></div>
            </div>
            <div class="text-right">
                <div class="text-2xl font-black text-slate-800"><?= htmlspecialchars($staff['full_name']) ?></div>
                <div class="text-sm text-slate-500 mt-1 mono"><?= htmlspecialchars($staff['staff_code']) ?></div>
                <div class="text-sm text-slate-500 mt-1"><?= htmlspecialchars($staff['email']) ?></div>
                <div class="text-sm text-slate-500 mt-1"><?= htmlspecialchars($staff['phone']) ?></div>
            </div>
        </div>

        <div>
            <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Transactions Processed</h4>
            
            <?php if (empty($transactions)): ?>
                <div class="text-center py-10 text-slate-400 text-sm font-semibold bg-slate-50 rounded-xl">No transactions found for this staff.</div>
            <?php else: ?>
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
                        <?php foreach ($transactions as $txn): 
                            $color = 'text-slate-700';
                            $details = '';
                            
                            if ($txn['type'] === 'deposit') {
                                $color = 'text-emerald-600';
                                $details = 'To: ' . htmlspecialchars($txn['to_name'] ?? '') . ' (' . htmlspecialchars($txn['to_acc'] ?? '') . ')';
                            } elseif ($txn['type'] === 'withdrawal') {
                                $color = 'text-red-500';
                                $details = 'From: ' . htmlspecialchars($txn['from_name'] ?? '') . ' (' . htmlspecialchars($txn['from_acc'] ?? '') . ')';
                            } else {
                                $color = 'text-slate-500';
                                $details = 'Internal Transfer: ' . htmlspecialchars($txn['from_acc'] ?? '') . ' &rarr; ' . htmlspecialchars($txn['to_acc'] ?? '');
                            }
                        ?>
                        <tr>
                            <td class="py-4 px-4 text-xs text-slate-500 font-medium whitespace-nowrap align-top"><?= date('d M Y h:i A', strtotime($txn['created_at'])) ?></td>
                            <td class="py-4 px-4 align-top">
                                <div class="text-[10px] mono text-slate-400 mb-1 uppercase"><?= htmlspecialchars($txn['txn_id']) ?></div>
                                <div class="text-sm font-semibold text-slate-700"><?= $details ?></div>
                                <?php if ($txn['reference_note']): ?>
                                    <div class="text-[10px] text-slate-400 mt-0.5 italic">Ref: <?= htmlspecialchars($txn['reference_note']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 px-4 align-top">
                                <span class="text-[10px] font-bold uppercase tracking-widest bg-slate-100 px-2 py-1 rounded text-slate-500"><?= htmlspecialchars($txn['type']) ?></span>
                            </td>
                            <td class="py-4 px-4 text-right align-top">
                                <div class="text-sm font-black <?= $color ?>"><?= CURRENCY_SYMBOL . number_format($txn['amount'], 2) ?></div>
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
