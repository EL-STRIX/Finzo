<?php
/**
 * Finzo Banking System — Admin Staff Detail API
 * GET endpoint: full staff profile with transaction history and stats.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

$staff_id = intval($_GET['staff_id'] ?? $_GET['id'] ?? 0);
if (!$staff_id) {
    json_response(['success' => false, 'message' => 'Staff ID is required'], 422);
}

try {
    // Fetch staff profile
    $stmt = $pdo->prepare("
        SELECT s.id, s.staff_id, s.full_name, s.email, s.phone, s.role, s.status,
               s.last_login_at, s.last_login_ip, s.created_at,
               b.name as branch_name, b.branch_code, b.state as branch_state
        FROM staff s
        LEFT JOIN branches b ON s.branch_id = b.id
        WHERE s.id = ?
    ");
    $stmt->execute([$staff_id]);
    $staff = $stmt->fetch();

    if (!$staff) {
        json_response(['success' => false, 'message' => 'Staff member not found'], 404);
    }

    // Performance stats
    $stmt = $pdo->prepare("
        SELECT
            COUNT(*) as total_transactions,
            COALESCE(SUM(CASE WHEN type = 'deposit' THEN amount ELSE 0 END), 0) as total_deposits,
            COALESCE(SUM(CASE WHEN type = 'withdrawal' THEN amount ELSE 0 END), 0) as total_withdrawals,
            COUNT(CASE WHEN type = 'deposit' THEN 1 END) as deposit_count,
            COUNT(CASE WHEN type = 'withdrawal' THEN 1 END) as withdrawal_count
        FROM transactions
        WHERE processed_by_staff_id = ? AND status = 'completed'
    ");
    $stmt->execute([$staff_id]);
    $performance = $stmt->fetch();

    // Recent transactions (last 20)
    $stmt = $pdo->prepare("
        SELECT
            t.txn_id, t.type, t.amount, t.status, t.created_at,
            COALESCE(a.account_no, 'N/A') as account_no,
            COALESCE(u.full_name, 'N/A') as customer_name
        FROM transactions t
        LEFT JOIN accounts a ON COALESCE(t.to_account_id, t.from_account_id) = a.id
        LEFT JOIN users u ON a.user_id = u.id
        WHERE t.processed_by_staff_id = ?
        ORDER BY t.created_at DESC
        LIMIT 20
    ");
    $stmt->execute([$staff_id]);
    $transactions = $stmt->fetchAll();

    json_response([
        'success'      => true,
        'staff'        => $staff,
        'performance'  => $performance,
        'transactions' => $transactions
    ]);

} catch (PDOException $e) {
    error_log("Admin staff detail error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to load staff details'], 500);
}
