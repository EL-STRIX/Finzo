<?php
/**
 * Finzo Banking System — Check Lock Status
 * GET endpoint: checks if an admin account is locked
 */

require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/config/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

$email = clean_input($_GET['email'] ?? '');

if (empty($email)) {
    json_response(['found' => false]);
}

$stmt = $pdo->prepare("
    SELECT account_locked, auto_unlock_at, security_question_set, answer_locked_until, admin_role
    FROM admin_users
    WHERE email = ?
");
$stmt->execute([$email]);
$admin = $stmt->fetch();

if (!$admin) {
    json_response(['found' => false]);
}

$answer_locked = false;
$answer_locked_until = $admin['answer_locked_until'];
if ($answer_locked_until && strtotime($answer_locked_until) > time()) {
    $answer_locked = true;
}

json_response([
    'found' => true,
    'locked' => (bool)$admin['account_locked'],
    'auto_unlock_at' => $admin['auto_unlock_at'],
    'security_question_set' => (bool)$admin['security_question_set'],
    'answer_locked' => $answer_locked,
    'answer_locked_until' => $answer_locked_until,
    'admin_role' => $admin['admin_role']
]);
