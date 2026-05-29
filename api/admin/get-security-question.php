<?php
/**
 * Finzo Banking System — Get Security Question
 * POST endpoint: returns the security question for a locked admin
 */

require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/config/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

// 1. Validate CSRF token
$csrf_token = $_POST['csrf_token'] ?? '';
if (!validate_csrf_token($csrf_token)) {
    json_response(['success' => false, 'message' => 'Invalid CSRF token'], 403);
}

// 2. Sanitize email
$email = clean_input($_POST['email'] ?? '');

if (empty($email)) {
    json_response(['success' => false, 'message' => 'Email is required'], 422);
}

// 3. Query admin_users
$stmt = $pdo->prepare("
    SELECT security_question, account_locked, security_question_set, answer_locked_until
    FROM admin_users
    WHERE email = ?
");
$stmt->execute([$email]);
$admin = $stmt->fetch();

// 4. If not found
if (!$admin) {
    json_response(['success' => false, 'message' => 'No account found with this email'], 404);
}

// 5. If account_locked = 0
if ($admin['account_locked'] == 0) {
    json_response(['success' => false, 'message' => 'Your account is not locked'], 400);
}

// 6. If security_question_set = 0
if ($admin['security_question_set'] == 0) {
    json_response([
        'success' => false, 
        'message' => 'No security question set. Contact Super Admin to unlock.',
        'no_question' => true
    ], 400);
}

// 7. If answer_locked_until > NOW()
$answer_locked_until = $admin['answer_locked_until'];
if ($answer_locked_until && strtotime($answer_locked_until) > time()) {
    $remaining_seconds = strtotime($answer_locked_until) - time();
    $remaining_minutes = ceil($remaining_seconds / 60);
    json_response([
        'success' => false,
        'answer_locked' => true,
        'message' => "Too many wrong answers. Try again in {$remaining_minutes} minutes."
    ], 403);
}

// Mask email logic
$parts = explode('@', $email);
$masked_email = $email;
if (count($parts) === 2) {
    $name = $parts[0];
    $domain = $parts[1];
    if (strlen($name) > 1) {
        $masked_name = substr($name, 0, 1) . str_repeat('*', strlen($name) - 1);
        $masked_email = $masked_name . '@' . $domain;
    }
}

// 8. Return
json_response([
    'success' => true,
    'question' => $admin['security_question'],
    'email' => $masked_email
]);
