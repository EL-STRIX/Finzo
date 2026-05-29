<?php
/**
 * Finzo Banking System — Admin Interest Rate Management API
 * GET: fetch current rate and history | POST: update rate
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-admin.php';

$admin_id = $_SESSION['admin_id'];

// =====================================================
// GET: CURRENT RATE + HISTORY
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Current rate (most recent)
        $stmt = $pdo->query("
            SELECT ir.rate, ir.apply_from_type, ir.apply_from_date, ir.created_at,
                   au.full_name as changed_by_name
            FROM interest_rates ir
            JOIN admin_users au ON ir.changed_by_admin_id = au.admin_id
            ORDER BY ir.created_at DESC
            LIMIT 1
        ");
        $current_rate_row = $stmt->fetch();
        
        $current_rate = $current_rate_row ? (float)$current_rate_row['rate'] : 4.25;
        $last_change_date = $current_rate_row ? date('d M Y', strtotime($current_rate_row['created_at'])) : '-';

        // Stats
        $stats = [
            'total_interest_credited' => 0,
            'interest_delta' => '+0%',
            'earning_accounts' => 0
        ];
        
        $stmt = $pdo->query("SELECT SUM(amount) FROM transactions WHERE type = 'interest_credit' AND status = 'completed'");
        $total_interest = (float)$stmt->fetchColumn();
        $stats['total_interest_credited'] = '₹' . number_format($total_interest, 2);

        $stmt = $pdo->query("SELECT COUNT(*) FROM accounts WHERE account_type = 'savings' AND status = 'active'");
        $stats['earning_accounts'] = (int)$stmt->fetchColumn();
        
        $stmt = $pdo->query("SELECT SUM(amount) FROM transactions WHERE type = 'interest_credit' AND status = 'completed' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
        $this_month = (float)$stmt->fetchColumn();
        $stmt = $pdo->query("SELECT SUM(amount) FROM transactions WHERE type = 'interest_credit' AND status = 'completed' AND MONTH(created_at) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH) AND YEAR(created_at) = YEAR(CURRENT_DATE() - INTERVAL 1 MONTH)");
        $last_month = (float)$stmt->fetchColumn();
        if ($last_month > 0) {
            $delta = (($this_month - $last_month) / $last_month) * 100;
            $stats['interest_delta'] = ($delta > 0 ? '+' : '') . round($delta, 1) . '%';
        }

        // Chart Data
        $chart_data = [];
        for ($i = 11; $i >= 0; $i--) {
            $stmt = $pdo->query("SELECT SUM(amount) FROM transactions WHERE type = 'interest_credit' AND status = 'completed' AND MONTH(created_at) = MONTH(CURRENT_DATE() - INTERVAL {$i} MONTH) AND YEAR(created_at) = YEAR(CURRENT_DATE() - INTERVAL {$i} MONTH)");
            $chart_data[] = (float)$stmt->fetchColumn();
        }

        // Rate change history
        $stmt = $pdo->query("
            SELECT ir.id, ir.rate, ir.apply_from_type, ir.apply_from_date, ir.created_at,
                   au.full_name as changed_by_name
            FROM interest_rates ir
            JOIN admin_users au ON ir.changed_by_admin_id = au.admin_id
            ORDER BY ir.created_at DESC
            LIMIT 10
        ");
        $history_rows = $stmt->fetchAll();
        $history = [];
        $first = true;
        foreach ($history_rows as $row) {
            $status = 'Past';
            if ($row['apply_from_date'] > date('Y-m-d')) {
                $status = 'Upcoming';
            } elseif ($first) {
                $status = 'Active';
                $first = false;
            }
            $history[] = [
                'effective_date' => date('d M Y', strtotime($row['apply_from_date'] ?? $row['created_at'])),
                'rate' => (float)$row['rate'],
                'updated_by' => $row['changed_by_name'],
                'status' => $status
            ];
        }

        json_response([
            'success'          => true,
            'current_rate'     => $current_rate,
            'last_change_date' => $last_change_date,
            'stats'            => $stats,
            'chart_data'       => $chart_data,
            'history'          => $history
        ]);

    } catch (PDOException $e) {
        error_log("Interest rate GET error: " . $e->getMessage());
        json_response(['success' => false, 'message' => 'Failed to load interest rate data'], 500);
    }
}

// =====================================================
// POST: UPDATE RATE
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_admin_permission($pdo, $admin_id, 'change_interest_rate');

    $action = clean_input($_POST['action'] ?? 'update_rate');

    $new_rate       = floatval($_POST['new_rate'] ?? 0);
    $apply_from     = clean_input($_POST['apply_from'] ?? 'Immediately');
    $custom_date    = clean_input($_POST['custom_date'] ?? '');

    $errors = [];

    // Validate rate
    if ($new_rate < 0.01 || $new_rate > 99.99) {
        $errors['new_rate'] = 'Interest rate must be between 0.01% and 99.99%';
    }

    // Validate apply_from
    $valid_types = ['Immediately', 'Next Month', 'Custom Date'];
    if (!in_array($apply_from, $valid_types)) {
        $errors['apply_from'] = 'Invalid application type';
    }

    // Custom date required if type is Custom Date
    $apply_date = null;
    if ($apply_from === 'Custom Date') {
        if (empty($custom_date)) {
            $errors['custom_date'] = 'Custom date is required';
        } else {
            $apply_date = $custom_date;
        }
    } elseif ($apply_from === 'Next Month') {
        $apply_date = date('Y-m-01', strtotime('first day of next month'));
    } else {
        $apply_date = date('Y-m-d');
    }

    if (!empty($errors)) {
        json_response(['success' => false, 'errors' => $errors], 422);
    }

    try {
        // Fetch display admin_id (e.g. ADM001) from admin_users to prevent foreign key constraint violation
        $stmt = $pdo->prepare("SELECT admin_id FROM admin_users WHERE id = ?");
        $stmt->execute([$admin_id]);
        $admin_display_id = $stmt->fetchColumn();

        if (!$admin_display_id) {
            json_response(['success' => false, 'message' => 'Admin account not found'], 404);
        }

        // Insert new rate record
        $stmt = $pdo->prepare("
            INSERT INTO interest_rates (rate, apply_from_type, apply_from_date, changed_by_admin_id, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$new_rate, $apply_from, $apply_date, $admin_display_id]);

        // If apply immediately, update all active accounts and credit the interest!
        if ($apply_from === 'Immediately') {
            // 1. Update the interest rate for all active savings accounts
            $stmt = $pdo->prepare("UPDATE accounts SET interest_rate = ?, updated_at = NOW() WHERE status = 'active' AND account_type = 'savings'");
            $stmt->execute([$new_rate]);

            // 2. Fetch all active savings accounts with a balance > 0 to credit interest
            $stmt = $pdo->query("SELECT id, user_id, balance FROM accounts WHERE account_type = 'savings' AND status = 'active' AND balance > 0");
            $savings_accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($savings_accounts as $acc) {
                $balance = (float)$acc['balance'];
                // Interest = Balance * (Rate / 100) / 12 months
                $interest_amount = round($balance * ($new_rate / 100) / 12, 2);

                if ($interest_amount >= 0.01) {
                    $new_balance = $balance + $interest_amount;
                    
                    // Generate unique transaction ID
                    $txn_id = 'TXN' . strtoupper(bin2hex(random_bytes(8)));

                    // Insert transaction record
                    $txStmt = $pdo->prepare("
                        INSERT INTO transactions (txn_id, from_account_id, to_account_id, type, amount, balance_after, description, reference_note, status, created_at)
                        VALUES (?, NULL, ?, 'interest_credit', ?, ?, 'Savings Interest Credit', ?, 'completed', NOW())
                    ");
                    $txStmt->execute([
                        $txn_id,
                        $acc['id'],
                        $interest_amount,
                        $new_balance,
                        "Interest credited at " . number_format($new_rate, 2) . "% p.a."
                    ]);

                    // Update the account balance
                    $upStmt = $pdo->prepare("UPDATE accounts SET balance = ?, updated_at = NOW() WHERE id = ?");
                    $upStmt->execute([$new_balance, $acc['id']]);

                    // Send notification to the user
                    $notifStmt = $pdo->prepare("
                        INSERT INTO notifications (user_id, type, title, message, is_read, created_at)
                        VALUES (?, 'transaction', 'Interest Credited', ?, 0, NOW())
                    ");
                    $notifMessage = "₹" . number_format($interest_amount, 2) . " interest has been credited to your savings account. New balance: ₹" . number_format($new_balance, 2);
                    $notifStmt->execute([$acc['user_id'], $notifMessage]);
                }
            }
        }

        // Audit log
        write_audit_log($pdo, 'interest_rate_updated', null, 'system', $admin_id, 'admin',
            "Interest rate changed to {$new_rate}%. Apply from: {$apply_from}" . ($apply_date ? " ({$apply_date})" : ''));

        json_response([
            'success' => true,
            'message' => "Interest rate updated to {$new_rate}%"
        ]);

    } catch (PDOException $e) {
        error_log("Interest rate update error: " . $e->getMessage());
        json_response(['success' => false, 'message' => 'Failed to update interest rate'], 500);
    }
}

json_response(['success' => false, 'message' => 'Method not allowed'], 405);
