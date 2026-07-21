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

    public function generateStaffId()
    {
        $max_id = $this->repo->getMaxStaffId();
        return 'STF' . str_pad($max_id + 1, 3, '0', STR_PAD_LEFT);
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
}
