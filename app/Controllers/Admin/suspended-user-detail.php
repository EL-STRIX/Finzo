<?php
/**
 * Finzo Banking System — Admin Suspended User Detail API
 * GET endpoint: full customer profile with account, transaction data, and audit logs.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 3) . '/middleware/auth-admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

$user_id = intval($_GET['id'] ?? 0);

if (!$user_id) {
    json_response(['success' => false, 'message' => 'User ID is required'], 422);
}

try {
    // Fetch user profile
    $stmt = $pdo->prepare("
        SELECT id, customer_id, full_name, email, phone, status, dob, gender, father_name,
               occupation, annual_income, street, city, state, pincode, country,
               id_type, id_number, kyc_submitted_at, created_at, updated_at
        FROM users WHERE id = ? AND status = 'suspended' AND is_deleted = 0
    ");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        json_response(['success' => false, 'message' => 'Suspended user not found'], 404);
    }

    // Fetch account
    $stmt = $pdo->prepare("
        SELECT account_no, account_type, balance, status, created_at as opened_at
        FROM accounts WHERE user_id = ?
    ");
    $stmt->execute([$user['customer_id']]);
    $account = $stmt->fetch();

    // Fetch suspension details from audit log
    $stmt = $pdo->prepare("
        SELECT details, created_at, actor_id
        FROM audit_log
        WHERE action_type = 'user_suspended' AND entity_id = ?
        ORDER BY id DESC LIMIT 1
    ");
    $stmt->execute([$user['customer_id']]);
    $audit = $stmt->fetch();

    // Parse suspension reason
    $suspension_reason = 'Administrative Review';
    $suspension_notes = 'This account was suspended for administrative review.';
    $suspended_by = 'Admin';
    $suspended_date = 'N/A';
    $suspended_time = 'N/A';

    if ($audit) {
        if (preg_match('/Reason:\s*(.*)$/i', $audit['details'], $matches)) {
            $suspension_reason = trim($matches[1]);
        }
        $suspension_notes = $audit['details'];
        $suspended_by = $audit['actor_id'];
        
        $d = new DateTime($audit['created_at']);
        $suspended_date = $d->format('d M Y');
        $suspended_time = $d->format('h:i A');
    }

    // Fetch recent transactions (last 10)
    $transactions = [];
    if ($account) {
        $stmt = $pdo->prepare("
            SELECT t.txn_id, t.type, t.amount, t.description, t.status, t.created_at
            FROM transactions t
            LEFT JOIN accounts fa ON t.from_account_id = fa.id
            LEFT JOIN accounts ta ON t.to_account_id = ta.id
            WHERE fa.account_no = ? OR ta.account_no = ?
            ORDER BY t.created_at DESC
            LIMIT 10
        ");
        $stmt->execute([$account['account_no'], $account['account_no']]);
        $txs = $stmt->fetchAll();

        foreach ($txs as $tx) {
            $is_debit = false;
            if ($tx['type'] === 'withdrawal') {
                $is_debit = true;
            } else if ($tx['type'] === 'transfer' && $tx['description'] !== 'Deposit') {
                // If it is a transfer, determine if it is debit
                $is_debit = true;
            }
            
            $d = new DateTime($tx['created_at']);
            $transactions[] = [
                'date' => $d->format('d M Y, h:i A'),
                'description' => $tx['description'] ?: ucfirst($tx['type']),
                'type' => $is_debit ? 'Debit' : 'Credit',
                'amount' => ($is_debit ? '-' : '+') . ' ₹' . number_format($tx['amount'], 2)
            ];
        }
    }

    // Avatar URL
    $base_avatar_dir = dirname(__DIR__, 3) . '/uploads/avatars/';
    $avatar_path = $base_avatar_dir . 'user_' . $user_id . '.jpg';
    $avatar_url = file_exists($avatar_path) ? APP_URL . '/uploads/avatars/user_' . $user_id . '.jpg?t=' . filemtime($avatar_path) : null;

    // Format fields for UI
    $formatted_user = [
        'id' => $user['id'],
        'customer_id' => $user['customer_id'],
        'full_name' => $user['full_name'],
        'email' => $user['email'],
        'phone' => $user['phone'],
        'initials' => implode('', array_map(function($n) { return $n[0]; }, explode(' ', $user['full_name']))),
        'avatar_url' => $avatar_url,
        'account_no' => $account ? $account['account_no'] : 'N/A',
        'account_type' => $account ? ucfirst($account['account_type']) : 'N/A',
        'balance' => $account ? $account['balance'] : 0,
        'opened_at' => $account && $account['opened_at'] ? (new DateTime($account['opened_at']))->format('d M Y') : 'N/A',
        'suspension_reason' => $suspension_reason,
        'suspension_notes' => $suspension_notes,
        'suspended_by' => $suspended_by,
        'suspended_date' => $suspended_date,
        'suspended_time' => $suspended_time,
        'admin_notes' => $suspension_notes,
        'notes_footer' => "Logged by {$suspended_by} on {$suspended_date} {$suspended_time}"
    ];

    json_response([
        'success' => true,
        'user' => $formatted_user,
        'transactions' => $transactions
    ]);

} catch (PDOException $e) {
    error_log("Admin suspended user detail error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to load suspended user details'], 500);
}
