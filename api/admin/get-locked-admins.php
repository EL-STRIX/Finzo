<?php
/**
 * Finzo Banking System — Get Locked Admins
 * GET endpoint: Returns list of all locked admin accounts.
 */

require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/config/functions.php';

// Auth middleware
require_once dirname(__DIR__, 2) . '/middleware/auth-admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

// 1. Check super_admin role
if ($_SESSION['admin_role'] !== 'super_admin') {
    json_response(['success' => false, 'message' => 'Unauthorized. Super Admin access required.'], 403);
}

// 2. Query
$stmt = $pdo->prepare("
    SELECT id, full_name, email, admin_role, locked_at, lock_reason, 
           answer_attempts, answer_locked_until, security_question_set
    FROM admin_users
    WHERE account_locked = 1
    ORDER BY locked_at DESC
");
$stmt->execute();
$locked_admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Format records if needed (e.g. standardizing nulls)
foreach ($locked_admins as &$admin) {
    $admin['security_question_set'] = (bool)$admin['security_question_set'];
}

// 3. Return
json_response([
    'success' => true,
    'locked_admins' => $locked_admins,
    'total' => count($locked_admins)
]);
