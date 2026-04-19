<?php
/**
 * Finzo Banking System — Admin Profile API
 * GET: fetch admin profile | POST: update admin profile
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-admin.php';

$admin_id = $_SESSION['admin_id'];

// =====================================================
// GET: FETCH ADMIN PROFILE
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->prepare("
            SELECT id, admin_id, full_name, email, phone, status, last_login_at, last_login_ip, created_at
            FROM admin_users WHERE id = ?
        ");
        $stmt->execute([$admin_id]);
        $admin = $stmt->fetch();

        if (!$admin) {
            json_response(['success' => false, 'message' => 'Admin not found'], 404);
        }

        // Recent audit log (last 10 actions by this admin)
        $stmt = $pdo->prepare("
            SELECT action_type, entity_id, entity_type, details, ip_address, created_at
            FROM audit_log
            WHERE actor_id = ? AND actor_role = 'admin'
            ORDER BY created_at DESC
            LIMIT 10
        ");
        $stmt->execute([$admin_id]);
        $recent_activity = $stmt->fetchAll();

        // Security overview
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total_actions
            FROM audit_log
            WHERE actor_id = ? AND actor_role = 'admin'
        ");
        $stmt->execute([$admin_id]);
        $total_actions = (int)$stmt->fetchColumn();

        json_response([
            'success' => true,
            'admin'   => $admin,
            'recent_activity' => $recent_activity,
            'security' => [
                'total_actions' => $total_actions,
                'last_login_at' => $admin['last_login_at'],
                'last_login_ip' => $admin['last_login_ip']
            ]
        ]);

    } catch (PDOException $e) {
        error_log("Admin profile GET error: " . $e->getMessage());
        json_response(['success' => false, 'message' => 'Failed to load admin profile'], 500);
    }
}

// =====================================================
// POST: UPDATE ADMIN PROFILE
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize_input($_POST['action'] ?? 'update_profile');

    if ($action === 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password     = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($current_password) || empty($new_password)) {
            json_response(['success' => false, 'message' => 'All password fields are required'], 422);
        }

        if ($new_password !== $confirm_password) {
            json_response(['success' => false, 'message' => 'Passwords do not match'], 422);
        }

        $strength = validate_password_strength($new_password);
        if (!$strength['valid']) {
            json_response(['success' => false, 'message' => $strength['message']], 422);
        }

        try {
            $stmt = $pdo->prepare("SELECT password_hash FROM admin_users WHERE id = ?");
            $stmt->execute([$admin_id]);
            $admin = $stmt->fetch();

            if (!password_verify($current_password, $admin['password_hash'])) {
                json_response(['success' => false, 'message' => 'Current password is incorrect'], 401);
            }

            $new_hash = password_hash($new_password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE admin_users SET password_hash = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$new_hash, $admin_id]);

            write_audit_log($pdo, 'admin_password_changed', $admin_id, 'admin', $admin_id, 'admin',
                'Admin password changed');

            json_response(['success' => true, 'message' => 'Password changed successfully']);

        } catch (PDOException $e) {
            error_log("Admin password change error: " . $e->getMessage());
            json_response(['success' => false, 'message' => 'Failed to change password'], 500);
        }

    } else {
        // Update profile info
        $full_name = sanitize_input($_POST['full_name'] ?? '');
        $email     = sanitize_input($_POST['email'] ?? '');

        if (empty($full_name) || strlen($full_name) < 2) {
            json_response(['success' => false, 'message' => 'Full name is required'], 422);
        }

        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            json_response(['success' => false, 'message' => 'Invalid email address'], 422);
        }

        try {
            $stmt = $pdo->prepare("UPDATE admin_users SET full_name = ?, email = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$full_name, $email, $admin_id]);

            $_SESSION['admin_name']  = $full_name;
            $_SESSION['admin_email'] = $email;

            json_response(['success' => true, 'message' => 'Profile updated successfully']);

        } catch (PDOException $e) {
            error_log("Admin profile update error: " . $e->getMessage());
            json_response(['success' => false, 'message' => 'Failed to update profile'], 500);
        }
    }
}

json_response(['success' => false, 'message' => 'Method not allowed'], 405);
