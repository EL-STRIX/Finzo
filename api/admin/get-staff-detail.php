<?php
/**
 * api/admin/get-staff-detail.php
 * GET endpoint: returns full staff profile including address and documents.
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
    // Fetch details directly from unified staff table
    $stmt = $pdo->prepare("
        SELECT
            s.id, s.staff_id, s.full_name, s.personal_email as email, s.phone,
            s.designation, s.department, s.status, s.profile_photo, s.created_at,
            s.street, s.city, s.state, s.pincode, s.country,
            s.id_type, s.id_number, s.front_image, s.back_image,
            b.name as branch_name
        FROM staff s
        LEFT JOIN branches b ON b.id = s.branch_id
        WHERE s.id = ?
    ");
    $stmt->execute([$staff_id]);
    $staff = $stmt->fetch();

    if (!$staff) {
        json_response(['success' => false, 'message' => 'Staff member not found'], 404);
    }

    // Convert image paths to accessible URLs if they exist
    $base_url = APP_URL . '/';
    if ($staff['profile_photo']) $staff['profile_photo_url'] = $base_url . $staff['profile_photo'];
    if ($staff['front_image']) $staff['front_image_url'] = $base_url . $staff['front_image'];
    if ($staff['back_image']) $staff['back_image_url'] = $base_url . $staff['back_image'];

    // Performance stats (preserving existing functionality)
    $stmt = $pdo->prepare("
        SELECT
            COUNT(*) as total_transactions,
            COALESCE(SUM(CASE WHEN type = 'deposit' THEN amount ELSE 0 END), 0) as total_deposits,
            COALESCE(SUM(CASE WHEN type = 'withdrawal' THEN amount ELSE 0 END), 0) as total_withdrawals
        FROM transactions
        WHERE processed_by_staff_id = ? AND status = 'completed'
    ");
    $stmt->execute([$staff_id]);
    $performance = $stmt->fetch();

    json_response([
        'success' => true,
        'staff' => $staff,
        'performance' => $performance
    ]);

} catch (PDOException $e) {
    error_log("Get staff detail error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to load staff details'], 500);
}
