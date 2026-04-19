<?php
/**
 * Finzo Banking System — Staff Process Deposit API
 * POST endpoint: credits a customer's account with a cash deposit.
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
$account_no     = sanitize_input($_POST['account_no'] ?? '');
$amount         = floatval($_POST['amount'] ?? 0);
$reference_note = sanitize_input($_POST['reference_note'] ?? '');

$errors = [];

if (empty($account_no)) {
    $errors['account_no'] = 'Account number is required';
}

if ($amount < 100) {
    $errors['amount'] = 'Minimum deposit amount is ₹100';
}

if (!empty($errors)) {
    json_response(['success' => false, 'errors' => $errors], 422);
}

// =====================================================
// PROCESS DEPOSIT
// =====================================================
try {
    $pdo->beginTransaction();

    // Find account with row lock
    $stmt = $pdo->prepare("
        SELECT a.id, a.account_no, a.balance, a.status, u.id as user_id, u.full_name
        FROM accounts a
        JOIN users u ON a.user_id = u.id
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

    // Credit the account
    $new_balance = (float)$account['balance'] + $amount;
    $stmt = $pdo->prepare("UPDATE accounts SET balance = balance + ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$amount, $account['id']]);

    // Generate transaction ID
    $txn_id = generate_transaction_id();

    // Insert transaction record
    $stmt = $pdo->prepare("
        INSERT INTO transactions
            (txn_id, from_account_id, to_account_id, type, amount, balance_after, description, reference_note, status, processed_by_staff_id, created_at)
        VALUES (?, NULL, ?, 'deposit', ?, ?, 'Cash Deposit', ?, 'completed', ?, NOW())
    ");
    $stmt->execute([
        $txn_id,
        $account['id'],
        $amount,
        $new_balance,
        $reference_note,
        $staff_id
    ]);

    // Create notification for account owner
    send_notification($pdo, $account['user_id'], 'transaction',
        'Cash Deposit Received',
        'Cash deposit of ' . format_currency($amount) . ' credited to your account ' . $account_no . '. Transaction ID: ' . $txn_id
    );

    // Write audit log
    write_audit_log($pdo, 'staff_deposit', $txn_id, 'transaction', $staff_id, 'staff',
        "Deposit of " . format_currency($amount) . " to account {$account_no}");

    $pdo->commit();

    json_response([
        'success'        => true,
        'message'        => 'Deposit processed successfully',
        'transaction_id' => $txn_id,
        'customer_name'  => $account['full_name'],
        'account_no'     => $account_no,
        'amount'         => $amount,
        'new_balance'    => $new_balance,
        'redirect'       => '/views/staff/staff-receipt.html?id=' . $txn_id
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Staff deposit error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to process deposit. Please try again.'], 500);
}
