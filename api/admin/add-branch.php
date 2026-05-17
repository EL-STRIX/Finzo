<?php
/**
 * Finzo Banking System — Admin Add Branch API
 * POST endpoint: creates a new bank branch.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    json_response(['success' => false, 'message' => 'Invalid security token'], 403);
}

$admin_id = $_SESSION['admin_id'];
check_admin_permission($pdo, $admin_id, 'add_branch');

// Collect inputs
$branch_name  = sanitize_input($_POST['branch_name'] ?? '');
$state        = sanitize_input($_POST['state'] ?? '');
$manager_id   = intval($_POST['manager_id'] ?? 0);
$description  = sanitize_input($_POST['description'] ?? '');
$admin_password = $_POST['admin_password'] ?? '';

$errors = [];

// Validation
if (empty($branch_name) || strlen($branch_name) < 3) {
    $errors['branch_name'] = 'Branch name is required (min 3 characters)';
}

if (empty($state)) {
    $errors['state'] = 'State is required';
}

if (empty($admin_password)) {
    $errors['admin_password'] = 'Admin password is required for verification';
}

if (!empty($errors)) {
    json_response(['success' => false, 'errors' => $errors], 422);
}

try {
    // Verify admin password
    $stmt = $pdo->prepare("SELECT password_hash FROM admin_users WHERE id = ?");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($admin_password, $admin['password_hash'])) {
        json_response(['success' => false, 'errors' => ['admin_password' => 'Invalid admin password']], 401);
    }

    // Generate branch code (FNZ-ST-XXX format)
    $state_code = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $state), 0, 2));
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM branches WHERE branch_code LIKE ?");
    $stmt->execute(["FNZ-{$state_code}-%"]);
    $count = (int)$stmt->fetchColumn();
    $branch_code = "FNZ-{$state_code}-" . str_pad($count + 1, 3, '0', STR_PAD_LEFT);

    // Ensure uniqueness
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM branches WHERE branch_code = ?");
    $stmt->execute([$branch_code]);
    while ($stmt->fetchColumn() > 0) {
        $count++;
        $branch_code = "FNZ-{$state_code}-" . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
        $stmt->execute([$branch_code]);
    }

    // Handle certificate upload
    $cert_path = null;
    if (isset($_FILES['certificate']) && $_FILES['certificate']['error'] === UPLOAD_ERR_OK) {
        if (!is_dir(UPLOAD_BRANCH_CERTS_PATH)) {
            mkdir(UPLOAD_BRANCH_CERTS_PATH, 0755, true);
        }
        $ext = pathinfo($_FILES['certificate']['name'], PATHINFO_EXTENSION);
        $filename = 'branch_cert_' . time() . '.' . $ext;
        $dest = UPLOAD_BRANCH_CERTS_PATH . $filename;

        if (move_uploaded_file($_FILES['certificate']['tmp_name'], $dest)) {
            $cert_path = 'uploads/branch-certs/' . $filename;
        }
    }

    // Validate manager
    $mgr_id = null;
    if ($manager_id > 0) {
        $stmt = $pdo->prepare("SELECT id FROM staff WHERE id = ? AND status = 'active'");
        $stmt->execute([$manager_id]);
        if ($stmt->fetch()) {
            $mgr_id = $manager_id;
        }
    }

    // Insert branch
    $stmt = $pdo->prepare("
        INSERT INTO branches (name, branch_code, state, description, manager_id, cert_path, type, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, 'custom', 'active', NOW())
    ");
    $stmt->execute([$branch_name, $branch_code, $state, $description, $mgr_id, $cert_path]);

    $branch_id_new = $pdo->lastInsertId();

    // If manager specified, assign them to this branch
    if ($mgr_id) {
        $stmt = $pdo->prepare("UPDATE staff SET branch_id = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$branch_id_new, $mgr_id]);
    }

    // Audit log
    write_audit_log($pdo, 'branch_created', $branch_code, 'branch', $admin_id, 'admin',
        "Created branch: {$branch_name} ({$branch_code})");

    json_response([
        'success'     => true,
        'message'     => 'Branch created successfully',
        'branch_code' => $branch_code
    ], 201);

} catch (PDOException $e) {
    error_log("Add branch error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to create branch'], 500);
}
