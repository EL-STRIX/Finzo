<?php
/**
 * Finzo Banking System — Admin Review Application API
 * GET: fetch application details | POST: approve/reject/save notes
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-admin.php';
require_once dirname(__DIR__, 2) . '/config/mailer.php';

$admin_id = $_SESSION['admin_id'];

// =====================================================
// GET: FETCH APPLICATION DETAILS
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_id = intval($_GET['user_id'] ?? $_GET['id'] ?? 0);
    if (!$user_id) {
        json_response(['success' => false, 'message' => 'User ID is required'], 422);
    }

    try {
        // Full user profile
        $stmt = $pdo->prepare("
            SELECT id, full_name, email, phone, status, dob, gender, father_name,
                   occupation, annual_income, street, city, state, pincode, country,
                   id_type, id_number, id_front_path, id_back_path,
                   kyc_submitted_at, rejection_reason, created_at
            FROM users WHERE id = ? AND is_deleted = 0
        ");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if (!$user) {
            json_response(['success' => false, 'message' => 'Application not found'], 404);
        }

        // Account preferences
        $stmt = $pdo->prepare("SELECT account_type, balance as initial_deposit, status FROM accounts WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $account = $stmt->fetch();

        // Document URLs
        $id_front_url = $user['id_front_path'] ? APP_URL . '/' . $user['id_front_path'] : null;
        $id_back_url  = $user['id_back_path'] ? APP_URL . '/' . $user['id_back_path'] : null;

        json_response([
            'success' => true,
            'application' => [
                'user'         => $user,
                'account'      => $account,
                'id_documents' => [
                    'front_url' => $id_front_url,
                    'back_url'  => $id_back_url
                ]
            ]
        ]);

    } catch (PDOException $e) {
        error_log("Review application GET error: " . $e->getMessage());
        json_response(['success' => false, 'message' => 'Failed to load application'], 500);
    }
}

// =====================================================
// POST: APPROVE, REJECT, OR SAVE NOTES
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id'] ?? 0);
    $action  = sanitize_input($_POST['action'] ?? '');

    if (!$user_id || empty($action)) {
        json_response(['success' => false, 'message' => 'User ID and action are required'], 422);
    }

    try {
        // Get user info
        $stmt = $pdo->prepare("SELECT id, full_name, email, status FROM users WHERE id = ? AND is_deleted = 0");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if (!$user) {
            json_response(['success' => false, 'message' => 'User not found'], 404);
        }

        switch ($action) {
            // =====================================================
            // APPROVE APPLICATION
            // =====================================================
            case 'approve':
                $pdo->beginTransaction();

                // Generate proper account number
                $account_no = generate_account_number($pdo);

                // Update user status to active
                $stmt = $pdo->prepare("UPDATE users SET status = 'active', updated_at = NOW() WHERE id = ?");
                $stmt->execute([$user_id]);

                // Update account: set account number, status, opened date
                $stmt = $pdo->prepare("
                    UPDATE accounts SET
                        account_no = ?, status = 'active', opened_date = CURDATE(), updated_at = NOW()
                    WHERE user_id = ?
                ");
                $stmt->execute([$account_no, $user_id]);

                // Get initial deposit amount
                $stmt = $pdo->prepare("SELECT balance FROM accounts WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $initial_balance = (float)$stmt->fetchColumn();

                // Credit initial deposit as first transaction
                if ($initial_balance > 0) {
                    $stmt = $pdo->prepare("SELECT id FROM accounts WHERE user_id = ?");
                    $stmt->execute([$user_id]);
                    $acct_id = $stmt->fetchColumn();

                    $txn_id = generate_transaction_id();
                    $stmt = $pdo->prepare("
                        INSERT INTO transactions (txn_id, to_account_id, type, amount, balance_after, description, status, created_at)
                        VALUES (?, ?, 'deposit', ?, ?, 'Initial Deposit — Account Opening', 'completed', NOW())
                    ");
                    $stmt->execute([$txn_id, $acct_id, $initial_balance, $initial_balance]);
                }

                $pdo->commit();

                // Send approved email
                send_approved_email($user['email'], $user['full_name'], $account_no);

                // Notification
                send_notification($pdo, $user_id, 'system', 'Account Approved!',
                    "Congratulations! Your account has been approved. Your account number is {$account_no}.");

                // Audit log
                write_audit_log($pdo, 'kyc_approved', $user_id, 'user', $admin_id, 'admin',
                    "Approved KYC for {$user['full_name']}. Account: {$account_no}");

                json_response([
                    'success'    => true,
                    'message'    => 'Application approved successfully',
                    'account_no' => $account_no
                ]);
                break;

            // =====================================================
            // REJECT APPLICATION
            // =====================================================
            case 'reject':
                $reason = sanitize_input($_POST['reason'] ?? '');
                if (empty($reason)) {
                    json_response(['success' => false, 'message' => 'Rejection reason is required'], 422);
                }

                // Update user status
                $stmt = $pdo->prepare("
                    UPDATE users SET status = 'rejected', rejection_reason = ?, updated_at = NOW() WHERE id = ?
                ");
                $stmt->execute([$reason, $user_id]);

                // Update account status
                $stmt = $pdo->prepare("UPDATE accounts SET status = 'rejected', updated_at = NOW() WHERE user_id = ?");
                $stmt->execute([$user_id]);

                // Send rejection email
                send_rejected_email($user['email'], $user['full_name'], $reason);

                // Notification
                send_notification($pdo, $user_id, 'system', 'Application Update',
                    "Your application has been reviewed. Status: Rejected. Reason: {$reason}. You can update your profile and resubmit.");

                // Audit log
                write_audit_log($pdo, 'kyc_rejected', $user_id, 'user', $admin_id, 'admin',
                    "Rejected KYC for {$user['full_name']}. Reason: {$reason}");

                json_response(['success' => true, 'message' => 'Application rejected']);
                break;

            // =====================================================
            // SAVE ADMIN NOTES
            // =====================================================
            case 'save_notes':
                $notes = sanitize_input($_POST['notes'] ?? '');

                // Store notes in rejection_reason field (reused for admin notes during review)
                // In a production system, you'd have a separate admin_notes column
                write_audit_log($pdo, 'admin_notes_saved', $user_id, 'user', $admin_id, 'admin',
                    "Admin notes: {$notes}");

                json_response(['success' => true, 'message' => 'Notes saved successfully']);
                break;

            default:
                json_response(['success' => false, 'message' => 'Invalid action'], 422);
        }

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log("Review application POST error: " . $e->getMessage());
        json_response(['success' => false, 'message' => 'Action failed. Please try again.'], 500);
    }
}

json_response(['success' => false, 'message' => 'Method not allowed'], 405);
