<?php
/**
 * Finzo Banking System — Fund Transfer API
 * POST endpoint: ACID-safe account-to-account transfer.
 * This is the most critical endpoint in the system.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-user.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

// Validate CSRF token
if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    json_response(['success' => false, 'message' => 'Invalid security token'], 403);
}

// Verify user is active
if ($_SESSION['user_status'] !== 'active') {
    json_response(['success' => false, 'message' => 'Your account is not active. Transfers are not allowed.'], 403);
}

$user_id = $_SESSION['user_id'];

// =====================================================
// INPUT VALIDATION
// =====================================================
$to_account_no = clean_input($_POST['to_account_no'] ?? '');
$amount        = floatval($_POST['amount'] ?? 0);
$description   = clean_input($_POST['description'] ?? '');
$txn_pin       = $_POST['txn_pin'] ?? '';

$errors = [];

if (empty($to_account_no)) {
    $errors['to_account_no'] = 'Recipient account number is required';
}

if ($amount <= 0) {
    $errors['amount'] = 'Amount must be greater than zero';
} elseif ($amount != round($amount, 2)) {
    $errors['amount'] = 'Amount can have at most 2 decimal places';
}

if (strlen($description) > 100) {
    $errors['description'] = 'Description must not exceed 100 characters';
}

if (empty($txn_pin) || !preg_match('/^\d{4}$/', $txn_pin)) {
    $errors['txn_pin'] = 'Please enter your 4-digit transaction PIN';
}

if (!empty($errors)) {
    json_response(['success' => false, 'errors' => $errors], 422);
}

// =====================================================
// VERIFY TRANSACTION PIN
// =====================================================
if (!validate_pin($pdo, $user_id, $txn_pin)) {
    json_response(['success' => false, 'message' => 'Invalid transaction PIN'], 401);
}

// =====================================================
// TRANSFER LOGIC (ACID)
// =====================================================
try {
    $pdo->beginTransaction();

    // Fetch source account ID (no lock yet)
    $stmt = $pdo->prepare("
        SELECT a.id
        FROM accounts a
        JOIN users u ON a.user_id = u.customer_id
        WHERE u.id = ? AND a.status = 'active'
    ");
    $stmt->execute([$user_id]);
    $source_id = $stmt->fetchColumn();

    if (!$source_id) {
        $pdo->rollBack();
        json_response(['success' => false, 'message' => 'Source account not found or inactive'], 404);
    }

    // Fetch destination account ID (no lock yet)
    $stmt = $pdo->prepare("
        SELECT a.id
        FROM accounts a
        WHERE a.account_no = ? AND a.status = 'active'
    ");
    $stmt->execute([$to_account_no]);
    $dest_id = $stmt->fetchColumn();

    if (!$dest_id) {
        $pdo->rollBack();
        json_response(['success' => false, 'message' => 'Destination account not found or inactive'], 404);
    }

    if ($source_id === $dest_id) {
        $pdo->rollBack();
        json_response(['success' => false, 'message' => 'Cannot transfer to your own account'], 422);
    }

    // Lock accounts in ascending ID order to prevent deadlocks
    $first_id  = min($source_id, $dest_id);
    $second_id = max($source_id, $dest_id);

    $stmt = $pdo->prepare("
        SELECT a.id, a.account_no, a.balance, a.daily_limit, u.full_name, u.id as owner_user_id, u.customer_id
        FROM accounts a
        JOIN users u ON a.user_id = u.customer_id
        WHERE a.id = ? AND a.status = 'active'
        FOR UPDATE
    ");
    $stmt->execute([$first_id]);
    $first = $stmt->fetch();

    $stmt->execute([$second_id]);
    $second = $stmt->fetch();

    // Assign source and dest based on which was which
    $source = ($first['id'] == $source_id) ? $first : $second;
    $dest   = ($first['id'] == $dest_id)   ? $first : $second;

    if (!$source || !$dest) {
        $pdo->rollBack();
        json_response(['success' => false, 'message' => 'Account error during locking'], 500);
    }

    // Populate dest_user_id for notifications
    $dest['dest_user_id'] = $dest['customer_id'];

    // Check balance
    if ($source['balance'] < $amount) {
        $pdo->rollBack();
        json_response(['success' => false, 'message' => 'Insufficient balance'], 422);
    }

    // Check minimum transfer amount
    $min_transfer = get_setting($pdo, 'min_transfer_amount', 10);
    if ($amount < $min_transfer) {
        $pdo->rollBack();
        json_response(['success' => false, 'message' => 'Minimum transfer amount is ' . format_currency($min_transfer)], 422);
    }

    // Check daily transfer limit
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(amount), 0) as today_total
        FROM transactions
        WHERE from_account_id = ? AND type = 'transfer'
            AND status = 'completed' AND DATE(created_at) = CURDATE()
    ");
    $stmt->execute([$source['id']]);
    $today_total = (float)$stmt->fetchColumn();

    $daily_limit = $source['daily_limit'];
    if (($today_total + $amount) > $daily_limit) {
        $pdo->rollBack();
        $remaining = max(0, $daily_limit - $today_total);
        json_response([
            'success' => false,
            'message' => "Daily transfer limit exceeded. Remaining limit: " . format_currency($remaining)
        ], 422);
    }

    // =====================================================
    // EXECUTE TRANSFER
    // =====================================================

    // Debit source
    $new_source_balance = $source['balance'] - $amount;
    $stmt = $pdo->prepare("UPDATE accounts SET balance = balance - ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$amount, $source['id']]);

    // Credit destination
    $new_dest_balance = $dest['balance'] + $amount;
    $stmt = $pdo->prepare("UPDATE accounts SET balance = balance + ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$amount, $dest['id']]);

    // Generate transaction ID
    $txn_id = generate_transaction_id();

    // Insert transaction record
    $stmt = $pdo->prepare("
        INSERT INTO transactions
            (txn_id, from_account_id, to_account_id, type, amount, balance_after, dest_balance_after, description, status, created_at)
        VALUES (?, ?, ?, 'transfer', ?, ?, ?, ?, 'completed', NOW())
    ");
    $stmt->execute([
        $txn_id,
        $source['id'],
        $dest['id'],
        $amount,
        $new_source_balance,
        $new_dest_balance,
        $description,
    ]);

    // =====================================================
    // NOTIFICATIONS
    // =====================================================

    // Notify sender
    send_notification($pdo, $source['owner_user_id'], 'transaction',
        'Transfer Successful',
        "You transferred " . format_currency($amount) . " to {$dest['full_name']} ({$to_account_no}). Transaction ID: {$txn_id}"
    );

    // Notify receiver
    send_notification($pdo, $dest['owner_user_id'], 'transaction',
        'Money Received',
        "You received " . format_currency($amount) . " from {$source['full_name']} ({$source['account_no']}). Transaction ID: {$txn_id}"
    );

    // Write audit log
    write_audit_log($pdo, 'fund_transfer', $txn_id, 'transaction', $user_id, 'user',
        "Transfer of " . format_currency($amount) . " from {$source['account_no']} to {$to_account_no}");

    $pdo->commit();

    // Update session balance
    $_SESSION['user_balance'] = $new_source_balance;

    json_response([
        'success'        => true,
        'message'        => 'Transfer successful',
        'transaction_id' => $txn_id,
        'amount'         => $amount,
        'to_name'        => $dest['full_name'],
        'to_account'     => $to_account_no,
        'new_balance'    => $new_source_balance,
        'redirect'       => '/views/user/receipt.html?id=' . $txn_id
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Transfer error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Transfer failed. Please try again.'], 500);
}
