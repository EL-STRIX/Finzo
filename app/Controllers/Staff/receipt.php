<?php
/**
 * Finzo Banking System — Staff Transaction Receipt API
 * GET endpoint: fetches a single transaction for receipt display/printing.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 3) . '/middleware/auth-staff.php';

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

$txn_id = clean_input($_GET['id'] ?? $_GET['txn_id'] ?? '');

if (empty($txn_id)) {
    json_response(['success' => false, 'message' => 'Transaction ID is required'], 422);
}

$staff_id = $_SESSION['staff_id'];

try {
    // Fetch transaction details
    $stmt = $pdo->prepare("
        SELECT
            t.txn_id,
            t.type,
            t.amount,
            t.balance_after,
            t.description,
            t.reference_note,
            t.status,
            t.created_at,
            t.processed_by_staff_id,
            fa.account_no as from_account_no,
            COALESCE(fu.full_name, '') as from_name,
            ta.account_no as to_account_no,
            COALESCE(tu.full_name, '') as to_name,
            s.staff_id as staff_display_id,
            s.full_name as staff_name,
            b.name as branch_name
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
    $transaction = $stmt->fetch();

    if (!$transaction) {
        json_response(['success' => false, 'message' => 'Transaction not found'], 404);
    }

    // Determine the customer info based on transaction type
    $customer_name = '';
    $account_no    = '';

    if ($transaction['type'] === 'deposit') {
        $customer_name = $transaction['to_name'];
        $account_no    = $transaction['to_account_no'];
    } elseif ($transaction['type'] === 'withdrawal') {
        $customer_name = $transaction['from_name'];
        $account_no    = $transaction['from_account_no'];
    }

    json_response([
        'success'     => true,
        'transaction' => [
            'txn_id'         => $transaction['txn_id'],
            'type'           => $transaction['type'],
            'amount'         => (float)$transaction['amount'],
            'balance_after'  => (float)$transaction['balance_after'],
            'description'    => $transaction['description'],
            'reference_note' => $transaction['reference_note'],
            'status'         => $transaction['status'],
            'date'           => $transaction['created_at'],
            'customer_name'  => $customer_name,
            'account_no'     => $account_no,
            'from_account'   => $transaction['from_account_no'],
            'from_name'      => $transaction['from_name'],
            'to_account'     => $transaction['to_account_no'],
            'to_name'        => $transaction['to_name'],
            'staff_id'       => $transaction['staff_display_id'],
            'staff_name'     => $transaction['staff_name'],
            'branch_name'    => $transaction['branch_name']
        ]
    ]);

} catch (PDOException $e) {
    error_log("Staff receipt error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to load transaction receipt'], 500);
}
