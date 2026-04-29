<?php
/**
 * api/admin/generate-staff-password.php
 * Generates a strong random password for staff creation/reset.
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../api/auth/auth-admin.php';

header('Content-Type: application/json; charset=utf-8');

// 1. Validate CSRF token
$input = json_decode(file_get_contents('php://input'), true);
$csrf_token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $input['csrf_token'] ?? '';

if (empty($csrf_token) || $csrf_token !== $_SESSION['csrf_token']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

// 2. Generate random password
function generate_strong_password($length = 12) {
    $uppercase = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    $lowercase = 'abcdefghjkmnpqrstuvwxyz';
    $numbers = '23456789';
    $special = '@#$%&*!';
    
    // Ensure at least one of each
    $password = '';
    $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
    $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
    $password .= $numbers[random_int(0, strlen($numbers) - 1)];
    $password .= $special[random_int(0, strlen($special) - 1)];
    
    // Fill the rest
    $all = $uppercase . $lowercase . $numbers . $special;
    for ($i = 0; $i < $length - 4; $i++) {
        $password .= $all[random_int(0, strlen($all) - 1)];
    }
    
    // Shuffle
    $chars = str_split($password);
    shuffle($chars);
    return implode('', $chars);
}

try {
    $generated_password = generate_strong_password(12);
    
    echo json_encode([
        'success' => true,
        'password' => $generated_password
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error generating password']);
}
