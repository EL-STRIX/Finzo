<?php
/**
 * Finzo Banking System — Staff Profile API
 * GET  : Fetch full staff profile with address and documents.
 * POST : Update allowed fields (phone, emergency contacts, profile photo).
 * Version 1.0 — May 2026
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-staff.php';

$staff_id = $_SESSION['staff_id'];
$method   = $_SERVER['REQUEST_METHOD'];

// =====================================================
// GET — Fetch Full Staff Profile
// =====================================================
if ($method === 'GET') {
    try {
        // Staff record with branch info (exclude password_hash)
        $stmt = $pdo->prepare("
            SELECT
                s.id, s.full_name, s.date_of_birth, s.gender, s.profile_photo,
                s.personal_email, s.phone, s.emergency_contact_name,
                s.emergency_contact_phone, s.staff_id, s.designation,
                s.department, s.branch_id, s.date_of_joining, s.status,
                s.created_by, s.created_at, s.updated_at,
                s.last_login_at, s.last_login_ip,
                s.failed_attempts, s.locked_until,
                b.name   AS branch_name,
                b.branch_code AS branch_code
            FROM staff s
            LEFT JOIN branches b ON s.branch_id = b.id
            WHERE s.id = ?
        ");
        $stmt->execute([$staff_id]);
        $staff = $stmt->fetch();

        if (!$staff) {
            json_response(['success' => false, 'message' => 'Staff profile not found'], 404);
        }

        // Staff address
        $stmt = $pdo->prepare("
            SELECT id, street, city, state, pincode, country
            FROM staff_address
            WHERE staff_id = ?
        ");
        $stmt->execute([$staff_id]);
        $address = $stmt->fetch();

        // Staff documents
        $stmt = $pdo->prepare("
            SELECT id, id_type, id_number, front_image, back_image
            FROM staff_documents
            WHERE staff_id = ?
        ");
        $stmt->execute([$staff_id]);
        $documents = $stmt->fetchAll();

        json_response([
            'success'   => true,
            'staff'     => $staff,
            'address'   => $address ?: null,
            'documents' => $documents ?: []
        ]);

    } catch (PDOException $e) {
        error_log("Staff profile fetch error: " . $e->getMessage());
        json_response(['success' => false, 'message' => 'Failed to load profile data'], 500);
    }
}

// =====================================================
// POST — Update Allowed Fields
// =====================================================
if ($method === 'POST') {

    // Validate CSRF token
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        json_response(['success' => false, 'message' => 'Invalid security token'], 403);
    }

    // ---- Collect & validate inputs ----
    $phone                   = sanitize_input($_POST['phone'] ?? '');
    $emergency_contact_name  = sanitize_input($_POST['emergency_contact_name'] ?? '');
    $emergency_contact_phone = sanitize_input($_POST['emergency_contact_phone'] ?? '');

    $errors = [];

    if ($phone !== '' && !validate_phone($phone)) {
        $errors['phone'] = 'Enter a valid 10-digit mobile number';
    }

    if ($emergency_contact_phone !== '' && !validate_phone($emergency_contact_phone)) {
        $errors['emergency_contact_phone'] = 'Enter a valid 10-digit emergency contact number';
    }

    // ---- Profile photo handling ----
    $new_photo_path = null;

    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_photo'];

        // Validate MIME type
        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
            $errors['profile_photo'] = 'Only JPEG and PNG images are allowed';
        }

        // Validate size
        if ($file['size'] > MAX_UPLOAD_SIZE) {
            $errors['profile_photo'] = 'Profile photo must be under 5 MB';
        }
    }

    if (!empty($errors)) {
        json_response(['success' => false, 'errors' => $errors], 422);
    }

    try {
        // ---- Handle photo upload if present ----
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['profile_photo'];
            $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            $filename  = 'staff_photo_' . uniqid() . '.' . $ext;
            $dest_path = UPLOAD_STAFF_PHOTOS_PATH . $filename;

            if (!move_uploaded_file($file['tmp_name'], $dest_path)) {
                json_response(['success' => false, 'message' => 'Failed to save profile photo'], 500);
            }

            $new_photo_path = 'uploads/staff-photos/' . $filename;

            // Delete old photo file if it exists
            $stmt = $pdo->prepare("SELECT profile_photo FROM staff WHERE id = ?");
            $stmt->execute([$staff_id]);
            $old_photo = $stmt->fetchColumn();

            if ($old_photo) {
                $old_full = dirname(__DIR__, 2) . '/' . $old_photo;
                if (file_exists($old_full)) {
                    @unlink($old_full);
                }
            }
        }

        // ---- Build dynamic UPDATE query ----
        $fields = [];
        $params = [];

        if ($phone !== '') {
            $fields[] = 'phone = ?';
            $params[] = $phone;
        }

        if ($emergency_contact_name !== '') {
            $fields[] = 'emergency_contact_name = ?';
            $params[] = $emergency_contact_name;
        }

        if ($emergency_contact_phone !== '') {
            $fields[] = 'emergency_contact_phone = ?';
            $params[] = $emergency_contact_phone;
        }

        if ($new_photo_path !== null) {
            $fields[] = 'profile_photo = ?';
            $params[] = $new_photo_path;
        }

        if (empty($fields)) {
            json_response(['success' => false, 'message' => 'No fields provided to update'], 422);
        }

        $fields[] = 'updated_at = NOW()';
        $params[] = $staff_id;

        $sql = "UPDATE staff SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // ---- Fetch updated profile to return ----
        $stmt = $pdo->prepare("
            SELECT
                id, full_name, date_of_birth, gender, profile_photo,
                personal_email, phone, emergency_contact_name,
                emergency_contact_phone, staff_id, designation,
                department, branch_id, date_of_joining, status,
                updated_at
            FROM staff
            WHERE id = ?
        ");
        $stmt->execute([$staff_id]);
        $updated = $stmt->fetch();

        // Audit log
        write_audit_log($pdo, 'staff_profile_update', $staff_id, 'staff', $staff_id, 'staff',
            'Staff updated their profile');

        json_response([
            'success' => true,
            'message' => 'Profile updated successfully',
            'staff'   => $updated
        ]);

    } catch (PDOException $e) {
        error_log("Staff profile update error: " . $e->getMessage());
        json_response(['success' => false, 'message' => 'Failed to update profile'], 500);
    }
}

// =====================================================
// Unsupported method
// =====================================================
json_response(['success' => false, 'message' => 'Method not allowed'], 405);
