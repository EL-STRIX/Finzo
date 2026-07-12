<?php
/**
 * api/user/verify-pin.php
 * Validates a user's transaction PIN.
 */

require_once dirname(__DIR__, 3) . '/middleware/auth-user.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

// Ensure the request is JSON
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$pin = $input['pin'] ?? '';

if (empty($pin) || strlen($pin) !== 4 || !is_numeric($pin)) {
    json_response(['success' => false, 'message' => 'Invalid PIN format'], 400);
}

if (!validate_pin($pdo, $_SESSION['user_id'], $pin)) {
    json_response(['success' => false, 'message' => 'Incorrect PIN'], 401);
}

json_response(['success' => true, 'message' => 'PIN verified successfully']);
