<?php
/**
 * Finzo Banking System - Admin Delete Branch API
 */

require_once dirname(__DIR__, 3) . '/middleware/auth-admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    json_response(['success' => false, 'message' => 'Invalid security token'], 403);
}

$admin_id = $_SESSION['admin_id'];

// Collect inputs
$branch_id = intval($_POST['branch_id'] ?? 0);
$admin_password = $_POST['admin_password'] ?? '';
$confirm_name = $_POST['confirm_name'] ?? '';

if (empty($branch_id) || empty($admin_password) || empty($confirm_name)) {
    json_response(['success' => false, 'message' => 'Missing required fields'], 422);
}

try {
    // Verify admin password
    $stmt = $pdo->prepare("SELECT password_hash FROM admin_users WHERE id = ?");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($admin_password, $admin['password_hash'])) {
        json_response(['success' => false, 'message' => 'Invalid admin password'], 401);
    }

    // Check branch
    $stmt = $pdo->prepare("SELECT id, name, type FROM branches WHERE id = ?");
    $stmt->execute([$branch_id]);
    $branch = $stmt->fetch();

    if (!$branch) {
        json_response(['success' => false, 'message' => 'Branch not found'], 404);
    }

    if ($branch['type'] === 'system') {
        json_response(['success' => false, 'message' => 'System branches cannot be deleted'], 403);
    }

    if (strtolower(trim($branch['name'])) !== strtolower(trim($confirm_name))) {
        json_response(['success' => false, 'message' => 'Branch name confirmation does not match'], 400);
    }

    $pdo->beginTransaction();

    // Handle closure document upload if provided
    if (isset($_FILES['closure_document']) && $_FILES['closure_document']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['closure_document']['name'], PATHINFO_EXTENSION);
        $filename = 'closure_doc_' . time() . '_' . rand(1000,9999) . '.' . $ext;
        $dest_dir = dirname(__DIR__, 3) . '/uploads/branch-certs/';
        if (!is_dir($dest_dir)) mkdir($dest_dir, 0755, true);
        move_uploaded_file($_FILES['closure_document']['tmp_name'], $dest_dir . $filename);
    }

    // Unassign staff
    $stmt = $pdo->prepare("UPDATE staff SET branch_id = NULL WHERE branch_id = ?");
    $stmt->execute([$branch_id]);

    // Delete branch
    $stmt = $pdo->prepare("DELETE FROM branches WHERE id = ?");
    $stmt->execute([$branch_id]);

    write_audit_log($pdo, 'branch_deleted', $branch['name'], 'branch', $admin_id, 'admin', "Deleted branch: {$branch['name']}");

    $pdo->commit();
    json_response(['success' => true, 'message' => 'Branch deleted successfully']);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log("Delete branch error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Database error occurred'], 500);
}
