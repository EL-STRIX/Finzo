<?php
/**
 * Finzo Banking System — Staff Process Withdrawal API
 * POST endpoint: debits a customer's account for a cash withdrawal.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-staff.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

// Validate CSRF token
if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    json_response(['success' => false, 'message' => 'Invalid security token'], 403);
}

$staff_id = $_SESSION['staff_id'];

// =====================================================
// INPUT VALIDATION
// =====================================================
$account_no     = clean_input($_POST['account_no'] ?? '');
$amount         = floatval($_POST['amount'] ?? 0);
$identity_notes = clean_input($_POST['identity_notes'] ?? '');

$errors = [];

if (empty($account_no)) {
    $errors['account_no'] = 'Account number is required';
}

if ($amount <= 0) {
    $errors['amount'] = 'Amount must be greater than zero';
}

if (!empty($errors)) {
    json_response(['success' => false, 'errors' => $errors], 422);
}

// =====================================================
// PROCESS WITHDRAWAL
// =====================================================
try {
    $pdo->beginTransaction();

    // Find account with row lock
    $stmt = $pdo->prepare("
        SELECT a.id, a.account_no, a.balance, a.status, u.id as owner_user_id, u.customer_id, u.full_name
        FROM accounts a
        JOIN users u ON a.user_id = u.customer_id
        WHERE a.account_no = ?
        FOR UPDATE
    ");
    $stmt->execute([$account_no]);
    $account = $stmt->fetch();

    if (!$account) {
        $pdo->rollBack();
        json_response(['success' => false, 'message' => 'Account not found'], 404);
    }

    if ($account['status'] !== 'active') {
        $pdo->rollBack();
        json_response(['success' => false, 'message' => 'Account is not active. Status: ' . $account['status']], 403);
    }

    // Check sufficient balance
    if ((float)$account['balance'] < $amount) {
        $pdo->rollBack();
        json_response([
            'success' => false,
            'message' => 'Insufficient balance. Available: ' . format_currency($account['balance'])
        ], 422);
    }

    // Check maximum withdrawal amount
    $max_withdrawal = get_setting($pdo, 'max_withdrawal_amount', 50000);
    if ($amount > $max_withdrawal) {
        $pdo->rollBack();
        json_response(['success' => false, 'message' => 'Maximum withdrawal amount is ' . format_currency($max_withdrawal)], 422);
    }

    // Debit the account
    $new_balance = (float)$account['balance'] - $amount;
    $stmt = $pdo->prepare("UPDATE accounts SET balance = balance - ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$amount, $account['id']]);

    // Generate transaction ID
    $txn_id = generate_transaction_id();

    // Insert transaction record
    $stmt = $pdo->prepare("
        INSERT INTO transactions
            (txn_id, from_account_id, to_account_id, type, amount, balance_after, description, reference_note, status, processed_by_staff_id, created_at)
        VALUES (?, ?, NULL, 'withdrawal', ?, ?, 'Cash Withdrawal', ?, 'completed', ?, NOW())
    ");
    $stmt->execute([
        $txn_id,
        $account['id'],
        $amount,
        $new_balance,
        $identity_notes,
        $staff_id
    ]);

    // Create notification for account owner
    send_notification($pdo, $account['customer_id'], 'transaction',
        'Cash Withdrawal Processed',
        'Cash withdrawal of ' . format_currency($amount) . ' debited from your account ' . $account_no . '. Transaction ID: ' . $txn_id
    );

    // Write audit log
    write_audit_log($pdo, 'staff_withdrawal', $txn_id, 'transaction', $staff_id, 'staff',
        "Withdrawal of " . format_currency($amount) . " from account {$account_no}. Identity notes: {$identity_notes}");

    $pdo->commit();

    json_response([
        'success'        => true,
        'message'        => 'Withdrawal processed successfully',
        'transaction_id' => $txn_id,
        'customer_name'  => $account['full_name'],
        'account_no'     => $account_no,
        'amount'         => $amount,
        'new_balance'    => $new_balance,
        'redirect'       => '/views/staff/staff-receipt.html?id=' . $txn_id
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Staff withdrawal error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to process withdrawal. Please try again.'], 500);
}
