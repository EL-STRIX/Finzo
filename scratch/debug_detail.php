<?php
$_GET['id'] = 1;
$_SERVER['REQUEST_METHOD'] = 'GET';

// Mock session and auth-admin if needed, or simply run the inner code
require_once 'C:/xampp/htdocs/Finzo/config/db.php';
require_once 'C:/xampp/htdocs/Finzo/config/functions.php';

$user_id = 1;
try {
    // Fetch user profile
    $stmt = $pdo->prepare("
        SELECT id, customer_id, full_name, email, phone, status, dob, gender, father_name,
               occupation, annual_income, street, city, state, pincode, country,
               id_type, id_number, id_front_path, id_back_path,
               kyc_submitted_at, rejection_reason, is_deleted, deleted_at,
               failed_attempts, locked_until, last_login_at, last_login_ip,
               created_at, updated_at
        FROM users WHERE id = ?
    ");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch account
    $stmt = $pdo->prepare("
        SELECT id, account_no, account_type, balance, interest_rate, daily_limit,
               status, opened_date, created_at
        FROM accounts WHERE user_id = ?
    ");
    $stmt->execute([$user['customer_id']]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "USER:\n";
    print_r($user);
    echo "ACCOUNT:\n";
    print_r($account);
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
