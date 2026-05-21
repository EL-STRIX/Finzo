<?php
/**
 * Finzo Banking System — Staff Dashboard API
 * GET endpoint: returns shift overview stats for the current day.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-staff.php';

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

$staff_id = $_SESSION['staff_id'];

try {
    // =====================================================
    // TODAY'S SHIFT STATS
    // =====================================================

    // Total deposits today by this staff
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as total
        FROM transactions
        WHERE processed_by_staff_id = ? AND type = 'deposit'
            AND status = 'completed' AND DATE(created_at) = CURDATE()
    ");
    $stmt->execute([$staff_id]);
    $deposits = $stmt->fetch();

    // Total withdrawals today by this staff
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as total
        FROM transactions
        WHERE processed_by_staff_id = ? AND type = 'withdrawal'
            AND status = 'completed' AND DATE(created_at) = CURDATE()
    ");
    $stmt->execute([$staff_id]);
    $withdrawals = $stmt->fetch();

    // Total transaction count today
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM transactions
        WHERE processed_by_staff_id = ? AND status = 'completed'
            AND DATE(created_at) = CURDATE()
    ");
    $stmt->execute([$staff_id]);
    $total_today = (int)$stmt->fetchColumn();

    // =====================================================
    // RECENT ACTIVITY (last 10 transactions by this staff)
    // =====================================================
    $stmt = $pdo->prepare("
        SELECT
            t.txn_id,
            t.type,
            t.amount,
            t.status,
            t.description,
            t.reference_note,
            t.created_at,
            COALESCE(ta.account_no, fa.account_no) as account_no,
            COALESCE(tu.full_name, fu.full_name) as customer_name
        FROM transactions t
        LEFT JOIN accounts ta ON t.to_account_id = ta.id
        LEFT JOIN users tu ON ta.user_id = tu.id
        LEFT JOIN accounts fa ON t.from_account_id = fa.id
        LEFT JOIN users fu ON fa.user_id = fu.id
        WHERE t.processed_by_staff_id = ?
        ORDER BY t.created_at DESC
        LIMIT 10
    ");
    $stmt->execute([$staff_id]);
    $recent_activity = $stmt->fetchAll();

    // =====================================================
    // STAFF INFO
    // =====================================================
    $stmt = $pdo->prepare("
        SELECT s.staff_id, s.full_name, s.designation, s.profile_photo, b.name as branch_name
        FROM staff s
        LEFT JOIN branches b ON s.branch_id = b.id
        WHERE s.id = ?
    ");
    $stmt->execute([$staff_id]);
    $staff_info = $stmt->fetch();

    // =====================================================
    // RESPONSE
    // =====================================================
    json_response([
        'success' => true,
        'staff' => [
            'staff_id'      => $staff_info['staff_id'] ?? $_SESSION['staff_display_id'],
            'name'          => $staff_info['full_name'] ?? $_SESSION['staff_name'],
            'role'          => $staff_info['designation'] ?? 'Teller',
            'profile_photo' => $staff_info['profile_photo'] ?? null,
            'branch_name'   => $staff_info['branch_name'] ?? 'Unassigned'
        ],
        'shift_stats' => [
            'deposits_count'     => (int)$deposits['count'],
            'deposits_total'     => (float)$deposits['total'],
            'withdrawals_count'  => (int)$withdrawals['count'],
            'withdrawals_total'  => (float)$withdrawals['total'],
            'total_transactions' => $total_today,
            'net_cash_flow'      => (float)$deposits['total'] - (float)$withdrawals['total']
        ],
        'recent_activity' => $recent_activity,
        'greeting'         => get_greeting(),
        'csrf_token'       => $_SESSION['csrf_token'] ?? ''
    ]);

} catch (PDOException $e) {
    error_log("Staff dashboard error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to load dashboard data'], 500);
}
