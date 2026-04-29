<?php
/**
 * Finzo Banking System — Admin Suspended Staff Detail API
 * GET endpoint: full suspended staff profile with history and stats.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

$staff_id = intval($_GET['id'] ?? 0);
if (!$staff_id) {
    json_response(['success' => false, 'message' => 'Staff ID is required'], 422);
}

try {
    // Fetch staff profile with suspension details
    $stmt = $pdo->prepare("
        SELECT s.id, s.staff_id, s.full_name, s.personal_email as email, s.phone, 
               s.designation as role, s.status,
               s.suspension_reason, s.suspended_by, s.updated_at as suspended_at,
               s.created_at as onboarding_date,
               b.name as branch_name
        FROM staff s
        LEFT JOIN branches b ON s.branch_id = b.id
        WHERE s.id = ? AND s.status = 'suspended'
    ");
    $stmt->execute([$staff_id]);
    $staff = $stmt->fetch();

    if (!$staff) {
        json_response(['success' => false, 'message' => 'Suspended staff record not found'], 404);
    }

    // Format initials
    $name_parts = explode(' ', $staff['full_name']);
    $staff['initials'] = strtoupper(substr($name_parts[0], 0, 1) . (isset($name_parts[1]) ? substr($name_parts[1], 0, 1) : ''));
    
    // Formatting dates
    $staff['suspended_at'] = date('d M Y, H:i', strtotime($staff['suspended_at']));
    $staff['onboarding_date'] = date('d M Y', strtotime($staff['onboarding_date']));
    $staff['duration'] = 'Indefinite';

    // Performance stats (Total handled volume)
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_count,
            COALESCE(SUM(amount), 0) as total_volume,
            COUNT(CASE WHEN type = 'deposit' THEN 1 END) as deposit_count,
            COUNT(CASE WHEN type = 'withdrawal' THEN 1 END) as withdrawal_count
        FROM transactions 
        WHERE processed_by_staff_id = ? AND status = 'completed'
    ");
    $stmt->execute([$staff_id]);
    $stats = $stmt->fetch();

    $staff['lifetime_volume'] = '₹' . number_format($stats['total_volume'], 2);
    $staff['deposit_count'] = $stats['deposit_count'];
    $staff['withdrawal_count'] = $stats['withdrawal_count'];
    $staff['avg_daily_volume'] = '₹' . number_format($stats['total_volume'] / 30, 2); // Dummy avg over 30 days

    // Recent history (last 10 transactions)
    $stmt = $pdo->prepare("
        SELECT type, amount, status, created_at as timestamp
        FROM transactions
        WHERE processed_by_staff_id = ?
        ORDER BY created_at DESC
        LIMIT 10
    ");
    $stmt->execute([$staff_id]);
    $history = $stmt->fetchAll();

    foreach ($history as &$h) {
        $h['amount'] = '₹' . number_format($h['amount'], 2);
        $h['timestamp'] = date('d M Y, H:i', strtotime($h['timestamp']));
        $h['type'] = strtoupper($h['type']);
        $h['status'] = strtoupper($h['status']);
    }

    json_response([
        'status' => 'success',
        'data'   => [
            'staff'   => $staff,
            'history' => $history
        ]
    ]);

} catch (PDOException $e) {
    error_log("Suspended staff detail API error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to load details'], 500);
}
