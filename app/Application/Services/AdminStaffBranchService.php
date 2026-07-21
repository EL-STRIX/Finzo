<?php

namespace App\Application\Services;

use App\Infrastructure\Repositories\AdminStaffBranchRepository;
use Exception;
use DateTime;

class AdminStaffBranchService
{
    protected AdminStaffBranchRepository $repo;

    public function __construct(AdminStaffBranchRepository $repo)
    {
        $this->repo = $repo;
    }

    public function getRepo(): AdminStaffBranchRepository
    {
        return $this->repo;
    }

    public function handleStaffUpload($file, $prefix, $dir, &$errors, $field, $required = false)
    {
        if ($required && (!$file || $file['error'] !== UPLOAD_ERR_OK)) {
            $errors[] = "$field is required.";
            return '';
        }
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return '';
        }

        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        if (!in_array($file['type'], $allowed_types)) {
            $errors[] = "Invalid file type for $field.";
            return '';
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            $errors[] = "File size for $field must be under 5MB.";
            return '';
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $prefix . uniqid() . '.' . $ext;
        $upload_dir = FINZO_ROOT . '/uploads/' . $dir . '/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $dest = $upload_dir . $filename;
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return $filename;
        }

        $errors[] = "Failed to upload $field.";
        return '';
    }

    public function validateStaffData(array $data)
    {
        $errors = [];
        if (empty($data['full_name']) || strlen($data['full_name']) < 2) {
            $errors['full_name'] = 'Full name is required (2-150 chars)';
        }
        if (empty($data['date_of_birth'])) {
            $errors['date_of_birth'] = 'Date of birth is required';
        } else {
            $dob = new \DateTime($data['date_of_birth']);
            $today = new \DateTime();
            if ($today->diff($dob)->y < 18) {
                $errors['date_of_birth'] = 'Staff must be at least 18 years old';
            }
        }
        if (!in_array($data['gender'], ['Male', 'Female', 'Other', 'Prefer not to say'])) {
            $errors['gender'] = 'Please select a valid gender';
        }
        if (empty($data['personal_email']) || !filter_var($data['personal_email'], FILTER_VALIDATE_EMAIL)) {
            $errors['personal_email'] = 'Valid personal email is required';
        } else {
            if ($this->repo->findStaffByEmail($data['personal_email'])) {
                $errors['personal_email'] = 'This email is already in use by another staff member';
            }
        }
        if (empty($data['phone']) || !validate_phone($data['phone'])) {
            $errors['phone'] = 'Valid 10-digit mobile number is required';
        }
        if (!in_array($data['id_type'], ['Aadhaar', 'PAN', 'Passport'])) {
            $errors['id_type'] = 'Please select a valid ID type';
        }
        if (empty($data['id_number']) || strlen($data['id_number']) < 5) {
            $errors['id_number'] = 'ID number is required (5-20 chars)';
        }
        if (!in_array($data['designation'], ['Teller', 'Supervisor', 'Manager'])) {
            $errors['designation'] = 'Please select a valid designation';
        }

        if ($data['branch_id']) {
            if (!$this->repo->findBranchById($data['branch_id'])) {
                $errors['branch_id'] = 'Invalid branch selected';
            }
        }
        if (empty($data['password']) || strlen($data['password']) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        }

        if (empty($data['city'])) {
            $errors['city'] = 'City is required';
        }
        if (empty($data['state'])) {
            $errors['state'] = 'State is required';
        }
        if (empty($data['pincode']) || !validate_pincode($data['pincode'])) {
            $errors['pincode'] = 'Valid 6-digit pincode is required';
        }
        return $errors;
    }

    public function generateBranchCode($state)
    {
        $state_clean = trim(preg_replace('/[^a-zA-Z\s]/', '', $state));
        $words = preg_split('/\s+/', $state_clean);
        if (count($words) >= 2) {
            $state_code = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        } else {
            $state_code = strtoupper(substr(str_replace(' ', '', $state_clean), 0, 2));
        }

        $count = $this->repo->countBranchByCode("FNZ-{$state_code}-%");
        $branch_code = "FNZ-{$state_code}-" . str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        while ($this->repo->countExactBranchCode($branch_code) > 0) {
            $count++;
            $branch_code = "FNZ-{$state_code}-" . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
        }
        return $branch_code;
    }

    public function generateStaffId(): string
    {
        $last_id = $this->repo->getLastStaffId();
        if (!$last_id) {
            return 'EMP1000';
        }
        $num = intval(substr($last_id, 3)) + 1;
        return 'EMP' . str_pad((string)$num, 4, '0', STR_PAD_LEFT);
    }

    public function createStaff(array $data, array $files, int $admin_id): array
    {
        $pdo = $this->repo->getPDO();
        check_admin_permission($pdo, $admin_id, 'add_staff');

        $data['department'] = 'Teller Operations';
        if (empty($data['date_of_joining'])) {
            $data['date_of_joining'] = date('Y-m-d');
        }

        $errors = $this->validateStaffData($data);

        $id_front_path = $this->handleStaffUpload($files['id_front'] ?? null, 'staff_doc_front_', 'staff-documents', $errors, 'id_front', true);
        $id_back_path = $this->handleStaffUpload($files['id_back'] ?? null, 'staff_doc_back_', 'staff-documents', $errors, 'id_back', true);
        $profile_photo_path = $this->handleStaffUpload($files['profile_photo'] ?? null, 'staff_photo_', 'staff-photos', $errors, 'profile_photo');

        if (!empty($errors)) {
            throw new \App\Domain\Exceptions\ValidationException(json_encode($errors));
        }

        try {
            $this->repo->beginTransaction();
            $data['staff_id'] = $this->generateStaffId();
            $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);

            $admin = $this->repo->findAdminById($admin_id);
            $data['created_by'] = $admin['admin_id'] ?? null;
            $data['profile_photo'] = $profile_photo_path;
            $data['front_image'] = $id_front_path;
            $data['back_image'] = $id_back_path;

            $this->repo->insertStaff($data);
            $this->repo->commit();

            write_audit_log($pdo, 'staff_created', $data['staff_id'], 'staff', $admin_id, 'admin', "Staff created: {$data['full_name']} | ID: {$data['staff_id']} | Branch: " . ($data['branch_id'] ?? 'None'));

            return ['staff_id' => $data['staff_id'], 'staff_name' => $data['full_name']];
        } catch (\Exception $e) {
            if ($this->repo->inTransaction()) {
                $this->repo->rollBack();
            }
            if ($profile_photo_path) {
                @unlink(FINZO_ROOT . '/' . $profile_photo_path);
            }
            if ($id_front_path) {
                @unlink(FINZO_ROOT . '/' . $id_front_path);
            }
            if ($id_back_path) {
                @unlink(FINZO_ROOT . '/' . $id_back_path);
            }
            throw $e;
        }
    }

    public function verifyAdminPassword(int $admin_id, string $password)
    {
        $admin = $this->repo->findAdminById($admin_id);
        return ($admin && password_verify($password, $admin['password_hash']));
    }

    public function generateStrongPassword($length = 12)
    {
        $chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789@#$%!';
        $pass = '';
        $pass .= 'abcdefghjkmnpqrstuvwxyz'[rand(0, 22)];
        $pass .= 'ABCDEFGHJKMNPQRSTUVWXYZ'[rand(0, 22)];
        $pass .= '23456789'[rand(0, 7)];
        $pass .= '@#$%!'[rand(0, 4)];
        for ($i = 4; $i < $length; $i++) {
            $pass .= $chars[rand(0, strlen($chars) - 1)];
        }
        return str_shuffle($pass);
    }

    public function getStaffDetail(...$args)
    {
        return $this->repo->getStaffDetail(...$args);
    }

    public function getStaffPerformance(...$args)
    {
        return $this->repo->getStaffPerformance(...$args);
    }
}
