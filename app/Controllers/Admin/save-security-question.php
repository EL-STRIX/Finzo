<?php
/**
 * Finzo Banking System — Save Security Question
 * POST endpoint: Admin sets or updates their own security question and answer.
 */

require_once dirname(__DIR__, 3) . '/config/db.php';
require_once dirname(__DIR__, 3) . '/config/functions.php';

// Auth middleware
require_once dirname(__DIR__, 3) . '/middleware/auth-admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

// 1. Validate CSRF token
$csrf_token = $_POST['csrf_token'] ?? '';
if (!validate_csrf_token($csrf_token)) {
    json_response(['success' => false, 'message' => 'Invalid CSRF token'], 403);
}

// 2. Validate inputs
$security_question = clean_input($_POST['security_question'] ?? '');
$security_answer = $_POST['security_answer'] ?? '';
$current_password = $_POST['current_password'] ?? '';

if (empty($security_question) || strlen($security_question) < 10) {
    json_response(['success' => false, 'message' => 'Security question must be at least 10 characters'], 422);
}

if (empty($security_answer) || strlen($security_answer) < 3) {
    json_response(['success' => false, 'message' => 'Security answer must be at least 3 characters'], 422);
}

if (empty($current_password)) {
    json_response(['success' => false, 'message' => 'Current password is required to save changes'], 422);
}

// 3. Fetch current password_hash
$stmt = $pdo->prepare("SELECT password_hash FROM admin_users WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch();

if (!$admin) {
    json_response(['success' => false, 'message' => 'Admin account not found'], 404);
}

// 4. Verify current_password
if (!password_verify($current_password, $admin['password_hash'])) {
    json_response(['success' => false, 'message' => 'Current password is incorrect'], 401);
}

// 5. Hash security_answer (lower-cased for case-insensitive verification later)
$security_answer_lower = strtolower(trim($security_answer));
$hashed_answer = password_hash($security_answer_lower, PASSWORD_BCRYPT);

// 6. Update admin_users
$updateStmt = $pdo->prepare("
    UPDATE admin_users 
    SET security_question = ?, 
        security_answer_hash = ?, 
        security_question_set = 1 
    WHERE id = ?
");
$updateStmt->execute([$security_question, $hashed_answer, $_SESSION['admin_id']]);

// 7. Write audit log
write_audit_log($pdo, 'security_question_updated', $_SESSION['admin_id'], 'admin', $_SESSION['admin_id'], 'admin', 'Admin updated security question');

// 8. Return
json_response([
    'success' => true,
    'message' => 'Security question saved successfully'
]);
