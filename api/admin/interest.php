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
            JOIN admin_users au ON ir.changed_by_admin_id = au.id
            ORDER BY ir.created_at DESC
            LIMIT 1
        ");
        $current_rate = $stmt->fetch();

        // Rate change history (last 10)
        $stmt = $pdo->query("
            SELECT ir.id, ir.rate, ir.apply_from_type, ir.apply_from_date, ir.created_at,
                   au.full_name as changed_by_name
            FROM interest_rates ir
            JOIN admin_users au ON ir.changed_by_admin_id = au.id
            ORDER BY ir.created_at DESC
            LIMIT 10
        ");
        $history = $stmt->fetchAll();

        json_response([
            'success'      => true,
            'current_rate' => $current_rate,
            'history'      => $history
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
    $action = sanitize_input($_POST['action'] ?? 'update_rate');

    $new_rate       = floatval($_POST['new_rate'] ?? 0);
    $apply_from     = sanitize_input($_POST['apply_from'] ?? 'Immediately');
    $custom_date    = sanitize_input($_POST['custom_date'] ?? '');

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
        // Insert new rate record
        $stmt = $pdo->prepare("
            INSERT INTO interest_rates (rate, apply_from_type, apply_from_date, changed_by_admin_id, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$new_rate, $apply_from, $apply_date, $admin_id]);

        // If apply immediately, update all active accounts
        if ($apply_from === 'Immediately') {
            $stmt = $pdo->prepare("UPDATE accounts SET interest_rate = ?, updated_at = NOW() WHERE status = 'active'");
            $stmt->execute([$new_rate]);
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
