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
            SELECT id, admin_id, full_name, email, phone, profile_photo, status, last_login_at, last_login_ip, created_at
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
            WHERE actor_id = ? AND actor_role IN ('Super Admin', 'Admin', 'Moderator', 'super_admin', 'admin', 'moderator')
            ORDER BY created_at DESC
            LIMIT 10
        ");
        $stmt->execute([$admin['admin_id']]);
        $recent_activity = $stmt->fetchAll();

        // Security overview
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total_actions
            FROM audit_log
            WHERE actor_id = ? AND actor_role IN ('Super Admin', 'Admin', 'Moderator', 'super_admin', 'admin', 'moderator')
        ");
        $stmt->execute([$admin['admin_id']]);
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
    $action = clean_input($_POST['action'] ?? 'update_profile');

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
            $admin_row = $stmt->fetch();

            if (!password_verify($current_password, $admin_row['password_hash'])) {
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

    } elseif ($action === 'upload_avatar') {
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            json_response(['success' => false, 'message' => 'No file uploaded or upload error'], 422);
        }

        $file = $_FILES['avatar'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        
        if (!in_array($mime, $allowed_types)) {
            json_response(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and WEBP are allowed.'], 422);
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'admin_' . $admin_id . '_' . time() . '.' . $ext;
        $upload_dir = dirname(__DIR__, 2) . '/uploads/avatars/';
        $target_path = $upload_dir . $filename;

        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            try {
                // Get old avatar to delete if it exists
                $stmt = $pdo->prepare("SELECT profile_photo FROM admin_users WHERE id = ?");
                $stmt->execute([$admin_id]);
                $old_avatar = $stmt->fetchColumn();

                if ($old_avatar && file_exists(dirname(__DIR__, 2) . '/' . $old_avatar)) {
                    @unlink(dirname(__DIR__, 2) . '/' . $old_avatar);
                }

                $relative_path = 'uploads/avatars/' . $filename;
                $stmt = $pdo->prepare("UPDATE admin_users SET profile_photo = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$relative_path, $admin_id]);

                $_SESSION['admin_profile_photo'] = $relative_path;

                json_response(['success' => true, 'message' => 'Profile photo updated successfully', 'avatar_url' => $relative_path]);

            } catch (PDOException $e) {
                json_response(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
            }
        } else {
            json_response(['success' => false, 'message' => 'Failed to move uploaded file'], 500);
        }

    } else {
        // Update profile info
        $full_name = clean_input($_POST['full_name'] ?? $_POST['name'] ?? '');
        $email     = clean_input($_POST['email'] ?? '');
        $phone     = clean_input($_POST['phone'] ?? '');
        $password  = $_POST['password'] ?? '';

        if (empty($full_name)) {
            json_response(['success' => false, 'message' => 'Full name is required'], 422);
        }

        if (empty($password)) {
            json_response(['success' => false, 'message' => 'Admin password is required for verification'], 422);
        }

        try {
            // VERIFY PASSWORD FIRST
            $stmt = $pdo->prepare("SELECT password_hash FROM admin_users WHERE id = ?");
            $stmt->execute([$admin_id]);
            $admin_row = $stmt->fetch();

            if (!$admin_row || !password_verify($password, $admin_row['password_hash'])) {
                json_response(['success' => false, 'message' => 'Verification failed: Incorrect password'], 401);
            }

            // UPDATE PROFILE
            $stmt = $pdo->prepare("
                UPDATE admin_users 
                SET full_name = ?, email = ?, phone = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$full_name, $email, $phone, $admin_id]);

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
