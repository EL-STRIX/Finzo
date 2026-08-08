<?php

namespace App\Application\Services;

use App\Shared\Security\SecurityService;

use App\Infrastructure\Repositories\AdminStaffBranchRepository;
use Exception;
use DateTime;

class AdminStaffBranchService implements \App\Domain\Contracts\Services\AdminStaffBranchServiceInterface
{
    protected ?\App\Domain\Contracts\Security\SecurityServiceInterface $security;

    protected \App\Domain\Contracts\Repositories\AdminStaffBranchRepositoryInterface $repo;
    /**
     *   construct.
     *
     * @param AdminStaffBranchRepository $repo
     * @param ?SecurityService $security
     */
    public function __construct(\App\Domain\Contracts\Repositories\AdminStaffBranchRepositoryInterface $repo, ?\App\Domain\Contracts\Security\SecurityServiceInterface $security = null)
    {
        $this->security = $security;
        $this->repo = $repo;
    }
    /**
     * Get Repo.
     *
     * @return AdminStaffBranchRepository
     */
    public function getRepo(): \App\Domain\Contracts\Repositories\AdminStaffBranchRepositoryInterface
    {
        return $this->repo;
    }
    /**
     * Handle Staff Upload.
     *
     * @param mixed $file
     * @param mixed $prefix
     * @param mixed $dir
     * @param mixed $errors
     * @param mixed $field
     * @param mixed $required
     * @return mixed
     */
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
    /**
     * Validate Staff Data.
     *
     * @param array $data
     * @return mixed
     */
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
    /**
     * Generate Branch Code.
     *
     * @param mixed $state
     * @return mixed
     */
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
    /**
     * Generate Staff Id.
     *
     * @return string
     */
    public function generateStaffId(): string
    {
        $last_id = $this->repo->getLastStaffId();
        if (!$last_id) {
            return 'EMP1000';
        }
        $num = intval(substr($last_id, 3)) + 1;
        return 'EMP' . str_pad((string)$num, 4, '0', STR_PAD_LEFT);
    }
    /**
     * Create Staff.
     *
     * @param array $data
     * @param array $files
     * @param int $adminId
     * @return array
     */
    public function createStaff(array $data, array $files, int $adminId): array
    {
        $this->security->checkAdminPermission($adminId, 'add_staff');

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
            
            // Encrypt highly sensitive PII before saving to the database
            if (!empty($data['id_number'])) {
                $crypto = new \App\Shared\Security\EncryptionService();
                $data['id_number'] = $crypto->encryptSearchable($data['id_number']);
            }

            $admin = $this->repo->findAdminById($adminId);
            $data['created_by'] = $admin['admin_id'] ?? null;
            $data['profile_photo'] = $profile_photo_path;
            $data['front_image'] = $id_front_path;
            $data['back_image'] = $id_back_path;

            $this->repo->insertStaff($data);
            $this->repo->commit();

            \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
    new \App\Application\Events\GenericAuditAction('staff_created', $data['staff_id'], \App\Domain\Enums\RoleEnum::STAFF->value, $adminId, \App\Domain\Enums\RoleEnum::ADMIN->value, "Staff created: {$data['full_name']} | ID: {$data['staff_id']} | Branch: " . ($data['branch_id'] ?? 'None'))
);

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
    /**
     * Verify Admin Password.
     *
     * @param int $adminId
     * @param string $password
     * @return mixed
     */
    public function verifyAdminPassword(int $adminId, string $password)
    {
        $admin = $this->repo->findAdminById($adminId);
        return ($admin && password_verify($password, $admin['password_hash']));
    }
    /**
     * Generate Strong Password.
     *
     * @param mixed $length
     * @return mixed
     */
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
    /**
     * Get Staff Detail.
     *
     * @param mixed $args
     * @return mixed
     */
    public function getStaffDetail(...$args)
    {
        return $this->repo->getStaffDetail(...$args);
    }
    /**
     * Get Staff Performance.
     *
     * @param mixed $args
     * @return mixed
     */
    public function getStaffPerformance(...$args)
    {
        return $this->repo->getStaffPerformance(...$args);
    }
    /**
     * Delete Branch.
     *
     * @param int $branchId
     * @param int $adminId
     * @param string $admin_password
     * @param string $confirm_name
     * @param ?array $closure_doc
     * @return array
     */
    public function deleteBranch(int $branchId, int $adminId, string $admin_password, string $confirm_name, ?array $closure_doc): array
    {
        $hash = $this->repo->getAdminPasswordHash($adminId);
        if (!$hash || !password_verify($admin_password, $hash)) {
            throw new \App\Shared\Core\Exceptions\ApiException('Invalid admin password', 401);
        }

        $branch = $this->repo->findBranchById($branchId);
        if (!$branch) {
            throw new \App\Shared\Core\Exceptions\ApiException('Branch not found', 404);
        }
        if ($branch->type === 'system') { // It uses \App\Domain\Models\Staff or stdClass
            throw new \App\Shared\Core\Exceptions\ApiException('System branches cannot be deleted', 403);
        }
        $branchName = is_array($branch) ? $branch['name'] : $branch->name;
        if (strtolower(trim($branchName)) !== strtolower(trim($confirm_name))) {
            throw new \App\Shared\Core\Exceptions\ApiException('Branch name confirmation does not match', 400);
        }

        if ($closure_doc && $closure_doc['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($closure_doc['name'], PATHINFO_EXTENSION);
            $filename = 'closure_doc_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            $dest_dir = FINZO_ROOT . '/uploads/branch-certs/';
            if (!is_dir($dest_dir)) mkdir($dest_dir, 0755, true);
            move_uploaded_file($closure_doc['tmp_name'], $dest_dir . $filename);
        }

        $this->repo->deleteBranchAndUnassignStaff($branchId);

        $security = new \App\Shared\Security\SecurityService();
        $security->writeAuditLog('branch_deleted', $branchName, 'branch', (string)$adminId, 'admin', "Deleted branch: {$branchName}");

        return ['success' => true, 'message' => 'Branch deleted successfully'];
    }
    /**
     * Get Branches List.
     *
     * @param string $search
     * @param string $type
     * @param string $status
     * @return array
     */
    public function getBranchesList(string $search, string $type, string $status): array
    {
        $data = $this->repo->getBranchesWithStats($search, $type, $status);
        $branches = $data['branches'];
        $stats = $data['stats'];

        foreach ($branches as &$b) {
            if (!empty($b['manager_name'])) {
                $parts = explode(' ', $b['manager_name']);
                $b['manager_initials'] = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
            } else {
                $b['manager_initials'] = '??';
            }
        }

        return [
            'success' => true,
            'branches' => $branches,
            'stats' => ['total' => (int)$stats['total'], 'active' => (int)$stats['active'], 'total_staff' => (int)$stats['total_staff']]
        ];
    }

    public function getDepartmentAnalytics(): array
    {
        return [
            'success' => true,
            'data' => $this->repo->getDepartmentAnalytics()
        ];
    }
}