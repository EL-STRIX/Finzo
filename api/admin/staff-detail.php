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
        SELECT s.id, s.staff_id, s.full_name, s.personal_email as email, s.phone, 
               s.designation, s.department, s.status,
               s.gender, s.date_of_birth, s.emergency_contact_name, s.emergency_contact_phone,
               s.date_of_joining, s.created_by,
               s.last_login_at, s.last_login_ip, s.created_at, s.profile_photo,
               b.name as branch_name, b.branch_code, b.state as branch_state,
               COALESCE(creator_admin.full_name, creator_staff.full_name, s.created_by, 'System') as created_by_name
        FROM staff s
        LEFT JOIN branches b ON s.branch_id = b.id
        LEFT JOIN admin_users creator_admin ON s.created_by = creator_admin.admin_id
        LEFT JOIN staff creator_staff ON s.created_by = creator_staff.staff_id
        WHERE s.id = ?
    ");
    $stmt->execute([$staff_id]);
    $staff = $stmt->fetch();

    if (!$staff) {
        json_response(['success' => false, 'message' => 'Staff member not found'], 404);
    }

    // Performance stats (All Time)
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

    // Today's performance
    $stmt = $pdo->prepare("
        SELECT
            COUNT(*) as total_transactions,
            COALESCE(SUM(CASE WHEN type = 'deposit' THEN amount ELSE 0 END), 0) as total_deposits,
            COALESCE(SUM(CASE WHEN type = 'withdrawal' THEN amount ELSE 0 END), 0) as total_withdrawals,
            COUNT(CASE WHEN type = 'deposit' THEN 1 END) as deposit_count,
            COUNT(CASE WHEN type = 'withdrawal' THEN 1 END) as withdrawal_count
        FROM transactions
        WHERE processed_by_staff_id = ? AND status = 'completed' AND DATE(created_at) = CURDATE()
    ");
    $stmt->execute([$staff_id]);
    $today_performance = $stmt->fetch();

    // Total shifts (distinct days worked)
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT DATE(created_at)) 
        FROM transactions 
        WHERE processed_by_staff_id = ?
    ");
    $stmt->execute([$staff_id]);
    $total_shifts = (int)$stmt->fetchColumn();

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

    // Staff documents
    $stmt = $pdo->prepare("
        SELECT id_type, id_number, front_image, back_image
        FROM staff_documents
        WHERE staff_id = ?
    ");
    $stmt->execute([$staff_id]);
    $documents = $stmt->fetchAll();

    // Staff address
    $stmt = $pdo->prepare("
        SELECT street, city, state, pincode, country
        FROM staff_address
        WHERE staff_id = ?
    ");
    $stmt->execute([$staff_id]);
    $address = $stmt->fetch();

    json_response([
        'success'           => true,
        'staff'             => $staff,
        'performance'       => $performance,
        'today_performance' => $today_performance,
        'total_shifts'      => $total_shifts,
        'transactions'      => $transactions,
        'documents'         => $documents,
        'address'           => $address
    ]);

} catch (PDOException $e) {
    error_log("Admin staff detail error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to load staff details'], 500);
}
