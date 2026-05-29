<?php
/**
 * Finzo Banking System — Admin User Detail API
 * GET endpoint: full customer profile with account and transaction data.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

$user_id = intval($_GET['user_id'] ?? $_GET['id'] ?? 0);

if (!$user_id) {
    json_response(['success' => false, 'message' => 'User ID is required'], 422);
}

try {
    // Fetch user profile
    $stmt = $pdo->prepare("
        SELECT id, customer_id, full_name, email, phone, status, dob, gender, father_name,
               mother_name, marital_status, nationality, profile_picture,
               occupation, annual_income, source_of_income, street, city, state, pincode, country,
               id_type, id_number, id_front_path, id_back_path,
               kyc_submitted_at, rejection_reason, is_deleted, deleted_at,
               failed_attempts, locked_until, last_login_at, last_login_ip,
               created_at, updated_at
        FROM users WHERE id = ?
    ");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        json_response(['success' => false, 'message' => 'User not found'], 404);
    }

    // Fetch accounts
    $stmt = $pdo->prepare("
        SELECT id, account_no, account_type, balance, interest_rate, daily_limit,
               status, opened_date, created_at
        FROM accounts WHERE user_id = ?
    ");
    $stmt->execute([$user['customer_id']]);
    $accounts = $stmt->fetchAll();

    // Fetch recent transactions (last 20) across all accounts
    $transactions = [];
    if (count($accounts) > 0) {
        $account_ids = array_column($accounts, 'id');
        $placeholders = implode(',', array_fill(0, count($account_ids), '?'));
        
        $stmt = $pdo->prepare("
            SELECT
                t.txn_id, t.type, t.amount, t.balance_after, t.description,
                t.status, t.created_at,
                COALESCE(fa.account_no, '') as from_account,
                COALESCE(ta.account_no, '') as to_account
            FROM transactions t
            LEFT JOIN accounts fa ON t.from_account_id = fa.id
            LEFT JOIN accounts ta ON t.to_account_id = ta.id
            WHERE t.from_account_id IN ($placeholders) OR t.to_account_id IN ($placeholders)
            ORDER BY t.created_at DESC
            LIMIT 20
        ");
        $params = array_merge($account_ids, $account_ids);
        $stmt->execute($params);
        $transactions = $stmt->fetchAll();
    }

    // Build ID document URLs
    $id_front_url = $user['id_front_path'] ? APP_URL . '/' . $user['id_front_path'] : null;
    $id_back_url  = $user['id_back_path'] ? APP_URL . '/' . $user['id_back_path'] : null;

    // Avatar URL
    $avatar_url = !empty($user['profile_picture']) ? APP_URL . '/' . $user['profile_picture'] : null;

    json_response([
        'success' => true,
        'user' => $user,
        'accounts' => $accounts,
        'transactions' => $transactions,
        'avatar_url' => $avatar_url,
        'id_documents' => [
            'front_url' => $id_front_url,
            'back_url'  => $id_back_url
        ]
    ]);

} catch (PDOException $e) {
    error_log("Admin user detail error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to load user details'], 500);
}
