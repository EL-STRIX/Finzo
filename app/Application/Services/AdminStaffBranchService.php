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
        $filename = $prefix . time() . '_' . rand(1000, 9999) . '.' . $ext;
        $upload_dir = FINZO_ROOT . '/public/uploads/' . $dir . '/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $dest = $upload_dir . $filename;
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            $root_dir = FINZO_ROOT . '/uploads/' . $dir . '/';
            if (!is_dir($root_dir)) {
                @mkdir($root_dir, 0777, true);
            }
            @copy($dest, $root_dir . $filename);

            return 'uploads/' . $dir . '/' . $filename;
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
     * Map state/UT name to standard 2-letter Indian state code.
     */
    public static function getStateCode(string $state): string
    {
        $map = [
            'Andhra Pradesh' => 'AP',
            'Arunachal Pradesh' => 'AR',
            'Assam' => 'AS',
            'Bihar' => 'BR',
            'Chhattisgarh' => 'CG',
            'Goa' => 'GA',
            'Gujarat' => 'GJ',
            'Haryana' => 'HR',
            'Himachal Pradesh' => 'HP',
            'Jharkhand' => 'JH',
            'Karnataka' => 'KA',
            'Kerala' => 'KL',
            'Madhya Pradesh' => 'MP',
            'Maharashtra' => 'MH',
            'Manipur' => 'MN',
            'Meghalaya' => 'ML',
            'Mizoram' => 'MZ',
            'Nagaland' => 'NL',
            'Odisha' => 'OD',
            'Punjab' => 'PB',
            'Rajasthan' => 'RJ',
            'Sikkim' => 'SK',
            'Tamil Nadu' => 'TN',
            'Telangana' => 'TG',
            'Tripura' => 'TR',
            'Uttar Pradesh' => 'UP',
            'Uttarakhand' => 'UK',
            'West Bengal' => 'WB',
            'Andaman and Nicobar Islands' => 'AN',
            'Chandigarh' => 'CH',
            'Dadra and Nagar Haveli and Daman and Diu' => 'DH',
            'Delhi' => 'DL',
            'Jammu and Kashmir' => 'JK',
            'Ladakh' => 'LA',
            'Lakshadweep' => 'LD',
            'Puducherry' => 'PY',
        ];

        $normalized = trim($state);
        foreach ($map as $name => $code) {
            if (strcasecmp($name, $normalized) === 0) {
                return $code;
            }
        }

        $clean = trim(preg_replace('/[^a-zA-Z\s]/', '', $normalized));
        $words = preg_split('/\s+/', $clean);
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }
        return strtoupper(substr(str_replace(' ', '', $clean), 0, 2)) ?: 'XX';
    }

    /**
     * Generate Branch Code based on state.
     *
     * @param mixed $state
     * @return string
     */
    public function generateBranchCode($state): string
    {
        $state_code = self::getStateCode((string)$state);

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
        $max_id = (int)$this->repo->getMaxStaffId();
        $num = max(1, $max_id + 1);
        return 'STF' . str_pad((string)$num, 3, '0', STR_PAD_LEFT);
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
        if ($this->security) {
            $this->security->checkAdminPermission($adminId, 'add_staff');
        }

        $data['department'] = 'Teller Operations';
        if (empty($data['date_of_joining'])) {
            $data['date_of_joining'] = date('Y-m-d');
        }

        $errors = $this->validateStaffData($data);

        $id_front_path = $this->handleStaffUpload($files['id_front'] ?? null, 'staff_doc_front_', 'staff-documents', $errors, 'id_front', false);
        $id_back_path = $this->handleStaffUpload($files['id_back'] ?? null, 'staff_doc_back_', 'staff-documents', $errors, 'id_back', false);
        $profile_photo_path = $this->handleStaffUpload($files['profile_photo'] ?? null, 'staff_photo_', 'staff-photos', $errors, 'profile_photo', false);

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
        $hash = $this->repo->getAdminPasswordHash($adminId);
        if ($hash) {
            return password_verify($password, $hash);
        }
        $admin = $this->repo->findAdminById($adminId);
        if (!$admin) return false;
        $adminHash = is_array($admin) ? ($admin['password_hash'] ?? null) : ($admin->password_hash ?? null);
        return ($adminHash && password_verify($password, $adminHash));
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
     * Check if the current admin has the given permission, falling back to
     * alternate permissions. If none are available, throws a 403.
     */
    private function assertBranchPermission(int $adminId, string $primary, array $fallbacks = []): void
    {
        if (!$this->security) {
            return;
        }

        foreach (array_merge([$primary], $fallbacks) as $perm) {
            try {
                $this->security->checkAdminPermission($adminId, $perm);
                return;
            } catch (\Throwable $e) {
                // Try next fallback permission
            }
        }

        throw new \App\Shared\Core\Exceptions\ApiException('You do not have permission to perform this action', 403);
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
        $this->assertBranchPermission($adminId, 'delete_branch', ['manage_branches', 'add_branch']);

        $hash = $this->repo->getAdminPasswordHash($adminId);
        if (!$hash || !password_verify($admin_password, $hash)) {
            throw new \App\Shared\Core\Exceptions\ApiException('Invalid administrator password.', 401);
        }

        $branch = $this->repo->findBranchById($branchId);
        if (!$branch) {
            throw new \App\Shared\Core\Exceptions\ApiException('Branch not found', 404);
        }
        $type = is_array($branch) ? ($branch['type'] ?? 'custom') : ($branch->type ?? 'custom');
        if ($type === 'system') {
            throw new \App\Shared\Core\Exceptions\ApiException('System branches cannot be deleted', 403);
        }
        $branchName = is_array($branch) ? $branch['name'] : $branch->name;
        $branchCode = is_array($branch) ? ($branch['branch_code'] ?? '') : ($branch->branch_code ?? '');

        $confirmClean = strtolower(trim($confirm_name));
        $nameClean = strtolower(trim($branchName));
        $codeClean = strtolower(trim($branchCode));

        if ($confirmClean !== $nameClean && $confirmClean !== $codeClean && $confirmClean !== 'delete' && $confirmClean !== 'confirm') {
            throw new \App\Shared\Core\Exceptions\ApiException("Confirmation name must match '{$branchName}'", 400);
        }

        $closureNote = '';
        if ($closure_doc && ($closure_doc['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $ext = pathinfo($closure_doc['name'], PATHINFO_EXTENSION);
            $filename = 'closure_doc_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            $dest_dir = FINZO_ROOT . '/uploads/branch-certs/';
            $public_dir = FINZO_ROOT . '/public/uploads/branch-certs/';
            if (!is_dir($dest_dir)) @mkdir($dest_dir, 0777, true);
            if (!is_dir($public_dir)) @mkdir($public_dir, 0777, true);
            if (move_uploaded_file($closure_doc['tmp_name'], $dest_dir . $filename)) {
                @copy($dest_dir . $filename, $public_dir . $filename);
                $closureNote = " | Closure document archived: uploads/branch-certs/{$filename}";
            }
        }

        $this->repo->deleteBranchAndUnassignStaff($branchId);

        $security = new \App\Shared\Security\SecurityService();
        $security->writeAuditLog('branch_deleted', $branchName, 'branch', (string)$adminId, 'admin', "Deleted branch: {$branchName}{$closureNote}");

        return ['success' => true, 'message' => 'Branch deleted successfully'];
    }

    /**
     * Create Branch.
     *
     * @param array $data
     * @param ?array $certFile
     * @param int $adminId
     * @param string $adminPassword
     * @return array
     */
    public function createBranch(array $data, ?array $certFile, int $adminId, string $adminPassword): array
    {
        $this->assertBranchPermission($adminId, 'add_branch', ['manage_branches', 'edit_branch']);

        if (empty($adminPassword)) {
            throw new \App\Shared\Core\Exceptions\ApiException('Admin password is required for branch creation', 422);
        }

        if (!$this->verifyAdminPassword($adminId, $adminPassword)) {
            throw new \App\Shared\Core\Exceptions\ApiException('Invalid administrator password. Authorization failed.', 403);
        }

        $branchName = trim($data['branch_name'] ?? ($data['name'] ?? ''));
        if (empty($branchName) || strlen($branchName) < 2 || strlen($branchName) > 150) {
            throw new \App\Shared\Core\Exceptions\ApiException('Branch name is required (2-150 characters)', 422);
        }

        $state = trim($data['state'] ?? '');
        if (empty($state)) {
            throw new \App\Shared\Core\Exceptions\ApiException('State is required', 422);
        }

        $city = trim($data['city'] ?? '');
        $address = trim($data['address'] ?? '');
        $description = trim($data['description'] ?? '');

        // Compose location metadata into description for rich preservation
        $locationMeta = [];
        if (!empty($city)) $locationMeta[] = "City: {$city}";
        if (!empty($address)) $locationMeta[] = "Address: {$address}";
        
        $combinedDesc = $description;
        if (!empty($locationMeta)) {
            $metaStr = implode(' | ', $locationMeta);
            $combinedDesc = !empty($combinedDesc) ? "{$combinedDesc}\n\n[Location: {$metaStr}]" : "[Location: {$metaStr}]";
        }

        // Branch code generation
        $branchCode = trim($data['branch_code'] ?? '');
        if (empty($branchCode)) {
            $branchCode = $this->generateBranchCode($state);
        } else {
            if ($this->repo->countExactBranchCode($branchCode) > 0) {
                $branchCode = $this->generateBranchCode($state);
            }
        }

        $managerId = !empty($data['manager_id']) ? intval($data['manager_id']) : null;
        if ($managerId) {
            $staff = $this->repo->findActiveStaffById($managerId);
            if (!$staff) {
                throw new \App\Shared\Core\Exceptions\ApiException('Selected manager not found or not active', 422);
            }
            $existingBranch = $this->repo->findBranchByManagerId($managerId);
            if ($existingBranch) {
                $existingName = $existingBranch['name'] ?? 'another branch';
                throw new \App\Shared\Core\Exceptions\ApiException("Selected staff member is already assigned as manager of '{$existingName}'", 422);
            }
        }

        // Handle certificate upload
        $certPath = null;
        if ($certFile && ($certFile['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
            if (!in_array($certFile['type'], $allowedTypes)) {
                throw new \App\Shared\Core\Exceptions\ApiException('Invalid certificate file format. Only PDF, JPG, and PNG are allowed.', 422);
            }
            if ($certFile['size'] > 10 * 1024 * 1024) {
                throw new \App\Shared\Core\Exceptions\ApiException('Certificate file exceeds maximum limit of 10MB', 422);
            }

            $ext = pathinfo($certFile['name'], PATHINFO_EXTENSION);
            $filename = 'branch_cert_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            
            $publicDir = FINZO_ROOT . '/public/uploads/branch-certs/';
            $rootUploadDir = FINZO_ROOT . '/uploads/branch-certs/';
            if (!is_dir($publicDir)) @mkdir($publicDir, 0777, true);
            if (!is_dir($rootUploadDir)) @mkdir($rootUploadDir, 0777, true);

            $dest = $publicDir . $filename;
            if (move_uploaded_file($certFile['tmp_name'], $dest)) {
                @copy($dest, $rootUploadDir . $filename);
                $certPath = 'uploads/branch-certs/' . $filename;
            }
        }

        $branchData = [
            'name'        => $branchName,
            'branch_code' => $branchCode,
            'state'       => $state,
            'description' => $combinedDesc,
            'manager_id'  => $managerId,
            'cert_path'   => $certPath
        ];

        try {
            $branchId = $this->repo->insertBranch($branchData);

            if ($managerId && $branchId) {
                $this->repo->updateStaffBranch($managerId, $branchId);
            }

            $security = new \App\Shared\Security\SecurityService();
            $security->writeAuditLog('branch_created', $branchName, 'branch', (string)$adminId, 'admin', "Created branch: {$branchName} ({$branchCode}) in {$state}");

            return [
                'success'     => true,
                'message'     => 'Branch created successfully',
                'branch_id'   => $branchId,
                'branch_code' => $branchCode
            ];
        } catch (\Throwable $e) {
            if ($certPath) {
                @unlink(FINZO_ROOT . '/public/' . $certPath);
                @unlink(FINZO_ROOT . '/' . $certPath);
            }
            throw $e;
        }
    }

    /**
     * Update Branch.
     *
     * @param int $branchId
     * @param array $data
     * @param ?array $certFile
     * @param int $adminId
     * @param ?string $adminPassword
     * @return array
     */
    public function updateBranch(int $branchId, array $data, ?array $certFile, int $adminId, ?string $adminPassword): array
    {
        $this->assertBranchPermission($adminId, 'edit_branch', ['manage_branches', 'add_branch']);

        if (!empty($adminPassword)) {
            if (!$this->verifyAdminPassword($adminId, $adminPassword)) {
                throw new \App\Shared\Core\Exceptions\ApiException('Invalid administrator password.', 403);
            }
        }

        $branch = $this->repo->findBranchById($branchId);
        if (!$branch) {
            throw new \App\Shared\Core\Exceptions\ApiException('Branch not found', 404);
        }

        $updateData = [];

        if (isset($data['branch_name']) || isset($data['name'])) {
            $name = trim($data['branch_name'] ?? $data['name']);
            if (!empty($name)) {
                $updateData['name'] = $name;
            }
        }

        if (isset($data['branch_code']) && !empty(trim($data['branch_code']))) {
            $newCode = strtoupper(trim($data['branch_code']));
            $dupCount = $this->repo->countExactBranchCode($newCode);
            if ($dupCount > 0) {
                $currentCode = is_array($branch) ? ($branch['branch_code'] ?? '') : ($branch->branch_code ?? '');
                if ($currentCode !== $newCode) {
                    throw new \App\Shared\Core\Exceptions\ApiException('Branch code already exists. Please choose a different code.', 422);
                }
            }
            $updateData['branch_code'] = $newCode;
        }

        if (isset($data['state']) && !empty(trim($data['state']))) {
            $updateData['state'] = trim($data['state']);
        }

        $city = trim($data['city'] ?? '');
        $address = trim($data['address'] ?? '');
        $description = trim($data['description'] ?? '');

        // Preserve any existing [EditDoc: ...] tag unless updated
        $currentDesc = $branch->description ?? '';
        $existingEditDocTag = '';
        if (preg_match('/\[EditDoc:\s*.*?\]/s', $currentDesc, $existingEditMatch)) {
            $existingEditDocTag = $existingEditMatch[0];
        }

        // Explicitly submitted (and possibly emptied) city/address allow clearing stored [Location: ...] meta
        $locationProvided = array_key_exists('city', $data) || array_key_exists('address', $data);
        $hasStoredLocation = strpos($currentDesc, '[Location:') !== false;

        if (!empty($city) || !empty($address) || !empty($description) || !empty($existingEditDocTag) || ($locationProvided && $hasStoredLocation)) {
            $locationMeta = [];
            if (!empty($city)) $locationMeta[] = "City: {$city}";
            if (!empty($address)) $locationMeta[] = "Address: {$address}";
            
            $combinedDesc = $description;
            if (!empty($locationMeta)) {
                $metaStr = implode(' | ', $locationMeta);
                $combinedDesc = !empty($combinedDesc) ? "{$combinedDesc}\n\n[Location: {$metaStr}]" : "[Location: {$metaStr}]";
            }
            if (!empty($existingEditDocTag)) {
                $combinedDesc = !empty($combinedDesc) ? "{$combinedDesc}\n\n{$existingEditDocTag}" : $existingEditDocTag;
            }
            $updateData['description'] = $combinedDesc;
        }

        if (array_key_exists('manager_id', $data)) {
            $newManagerId = !empty($data['manager_id']) ? intval($data['manager_id']) : null;
            if ($newManagerId) {
                $existingBranch = $this->repo->findBranchByManagerId($newManagerId);
                if ($existingBranch && (int)$existingBranch['id'] !== $branchId) {
                    $existingName = $existingBranch['name'] ?? 'another branch';
                    throw new \App\Shared\Core\Exceptions\ApiException("Selected staff member is already assigned as manager of '{$existingName}'", 422);
                }
            }
            $updateData['manager_id'] = $newManagerId;
            if ($newManagerId) {
                $this->repo->updateStaffBranch($newManagerId, $branchId);
            }
        }

        // Handle new certificate if uploaded during edit (persisted as Latest Edit Document)
        if ($certFile && ($certFile['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
            if (in_array($certFile['type'], $allowedTypes) && $certFile['size'] <= 10 * 1024 * 1024) {
                $ext = pathinfo($certFile['name'], PATHINFO_EXTENSION);
                $filename = 'branch_edit_cert_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                $publicDir = FINZO_ROOT . '/public/uploads/branch-certs/';
                $rootUploadDir = FINZO_ROOT . '/uploads/branch-certs/';
                if (!is_dir($publicDir)) @mkdir($publicDir, 0777, true);
                if (!is_dir($rootUploadDir)) @mkdir($rootUploadDir, 0777, true);

                $dest = $publicDir . $filename;
                if (move_uploaded_file($certFile['tmp_name'], $dest)) {
                    @copy($dest, $rootUploadDir . $filename);
                    $newEditDocPath = 'uploads/branch-certs/' . $filename;
                    
                    // If branch had no initial creation document, establish it
                    if (empty($branch->cert_path)) {
                        $updateData['cert_path'] = $newEditDocPath;
                    }

                    // Save as [EditDoc: ...] in description
                    $activeDesc = $updateData['description'] ?? ($branch->description ?? '');
                    $activeDesc = preg_replace('/\[EditDoc:\s*.*?\]/s', '', $activeDesc);
                    $activeDesc = trim($activeDesc);
                    $updateData['description'] = $activeDesc ? "{$activeDesc}\n\n[EditDoc: {$newEditDocPath}]" : "[EditDoc: {$newEditDocPath}]";
                }
            }
        }

        if (!empty($updateData)) {
            $this->repo->updateBranchRecord($branchId, $updateData);
        }

        $branchName = $updateData['name'] ?? ($branch->name ?? 'Branch');
        $security = new \App\Shared\Security\SecurityService();
        $security->writeAuditLog('branch_updated', $branchName, 'branch', (string)$adminId, 'admin', "Updated branch: {$branchName}");

        return [
            'success' => true,
            'message' => 'Branch updated successfully'
        ];
    }

    /**
     * Get Branch By Id.
     *
     * @param int $branchId
     * @return ?array
     */
    public function getBranchById(int $branchId): ?array
    {
        $branch = $this->repo->getBranchDetailWithStats($branchId);
        if (!$branch) return null;

        $resolveUrl = function(?string $path, string $subDir) {
            if (empty($path)) return null;
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path;
            }
            $cleanPath = ltrim($path, '/');
            if (str_starts_with($cleanPath, 'Finzo/')) {
                return '/' . $cleanPath;
            }
            if (str_starts_with($cleanPath, 'uploads/')) {
                return '/Finzo/public/' . $cleanPath;
            }
            return '/Finzo/public/uploads/' . $subDir . '/' . $cleanPath;
        };

        if (!empty($branch['manager_name'])) {
            $parts = explode(' ', trim($branch['manager_name']));
            $branch['manager_initials'] = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
        } else {
            $branch['manager_initials'] = '??';
        }
        $branch['manager_avatar_url'] = $resolveUrl($branch['manager_avatar'] ?? null, 'staff-photos');

        $latestEditDoc = null;
        $desc = $branch['description'] ?? '';
        if (preg_match('/\[EditDoc:\s*(.*?)\]/s', $desc, $editMatch)) {
            $latestEditDoc = trim($editMatch[1]);
        }

        $branch['city'] = '';
        $branch['address'] = '';
        $branch['clean_description'] = $desc;

        if (preg_match('/\[Location:\s*(.*?)\]/s', $desc, $matches)) {
            $locStr = $matches[1];
            if (preg_match('/City:\s*(.*?)(?:\s*\|\s*Address:|$)/i', $locStr, $cityMatch)) {
                $branch['city'] = trim($cityMatch[1]);
            }
            if (preg_match('/Address:\s*(.*?)$/i', $locStr, $addrMatch)) {
                $branch['address'] = trim($addrMatch[1]);
            }
            $desc = str_replace($matches[0], '', $desc);
        }

        if (!empty($latestEditDoc)) {
            $desc = preg_replace('/\[EditDoc:\s*.*?\]/s', '', $desc);
        }
        $branch['clean_description'] = trim($desc);

        $branch['creation_doc'] = $branch['cert_path'] ?? null;
        $branch['latest_edit_doc'] = $latestEditDoc;
        $branch['creation_doc_url'] = $resolveUrl($branch['creation_doc'], 'branch-certs');
        $branch['latest_edit_doc_url'] = $resolveUrl($branch['latest_edit_doc'], 'branch-certs');
        $branch['cert_url'] = $branch['creation_doc_url'];

        return [
            'success' => true,
            'branch'  => $branch
        ];
    }


    /**
     * Toggle Branch Status (Suspend or Reactivate).
     *
     * @param int $branchId
     * @param string $status 'active' or 'inactive'
     * @param int $adminId
     * @param string $adminPassword
     * @param ?string $reason
     * @return array
     */
    public function toggleBranchStatus(int $branchId, string $status, int $adminId, string $adminPassword, ?string $reason = null): array
    {
        if (!in_array($status, ['active', 'inactive'])) {
            throw new \App\Shared\Core\Exceptions\ApiException('Invalid branch status. Must be active or inactive.', 422);
        }

        if (empty($adminPassword)) {
            throw new \App\Shared\Core\Exceptions\ApiException('Administrator security password is required.', 422);
        }

        if (!$this->verifyAdminPassword($adminId, $adminPassword)) {
            throw new \App\Shared\Core\Exceptions\ApiException('Invalid administrator password.', 401);
        }

        $branch = $this->repo->findBranchById($branchId);
        if (!$branch) {
            throw new \App\Shared\Core\Exceptions\ApiException('Branch not found.', 404);
        }

        $type = is_array($branch) ? ($branch['type'] ?? 'custom') : ($branch->type ?? 'custom');
        if ($type === 'system' && $status === 'inactive') {
            throw new \App\Shared\Core\Exceptions\ApiException('Root system branches cannot be suspended.', 403);
        }

        $branchName = is_array($branch) ? ($branch['name'] ?? 'Branch') : ($branch->name ?? 'Branch');

        $this->repo->updateBranchStatus($branchId, $status);

        $actionType = ($status === 'inactive') ? 'branch_suspended' : 'branch_reactivated';
        $auditMsg = ($status === 'inactive') 
            ? "Suspended branch: {$branchName}" . ($reason ? " | Reason: {$reason}" : "")
            : "Reactivated branch: {$branchName}" . ($reason ? " | Note: {$reason}" : "");

        $security = new \App\Shared\Security\SecurityService();
        $security->writeAuditLog($actionType, $branchName, 'branch', (string)$adminId, 'admin', $auditMsg);

        $msg = ($status === 'inactive') ? 'Branch suspended successfully.' : 'Branch reactivated successfully.';

        return [
            'success' => true,
            'message' => $msg,
            'status'  => $status
        ];
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

        $resolveUrl = function(?string $path, string $subDir) {
            if (empty($path)) return null;
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path;
            }
            $cleanPath = ltrim($path, '/');
            if (str_starts_with($cleanPath, 'Finzo/')) {
                return '/' . $cleanPath;
            }
            if (str_starts_with($cleanPath, 'uploads/')) {
                return '/Finzo/public/' . $cleanPath;
            }
            return '/Finzo/public/uploads/' . $subDir . '/' . $cleanPath;
        };

        foreach ($branches as &$b) {
            if (!empty($b['manager_name'])) {
                $parts = explode(' ', trim($b['manager_name']));
                $b['manager_initials'] = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
            } else {
                $b['manager_initials'] = '??';
            }
            $b['manager_avatar_url'] = $resolveUrl($b['manager_avatar'] ?? null, 'staff-photos');
            $latestEditDoc = null;
            $desc = $b['description'] ?? '';
            if (preg_match('/\[EditDoc:\s*(.*?)\]/s', $desc, $editMatch)) {
                $latestEditDoc = trim($editMatch[1]);
            }

            // Extract City and Address if present in description meta
            $b['city'] = '';
            $b['address'] = '';
            $b['clean_description'] = $desc;

            if (preg_match('/\[Location:\s*(.*?)\]/s', $desc, $matches)) {
                $locStr = $matches[1];
                if (preg_match('/City:\s*(.*?)(?:\s*\|\s*Address:|$)/i', $locStr, $cityMatch)) {
                    $b['city'] = trim($cityMatch[1]);
                }
                if (preg_match('/Address:\s*(.*?)$/i', $locStr, $addrMatch)) {
                    $b['address'] = trim($addrMatch[1]);
                }
                $desc = str_replace($matches[0], '', $desc);
            }

            if (!empty($latestEditDoc)) {
                $desc = preg_replace('/\[EditDoc:\s*.*?\]/s', '', $desc);
            }
            $b['clean_description'] = trim($desc);

            $b['creation_doc'] = $b['cert_path'] ?? null;
            $b['latest_edit_doc'] = $latestEditDoc;
            $b['creation_doc_url'] = $resolveUrl($b['creation_doc'], 'branch-certs');
            $b['latest_edit_doc_url'] = $resolveUrl($b['latest_edit_doc'], 'branch-certs');
            $b['cert_url'] = $b['creation_doc_url'];
        }

        return [
            'success'  => true,
            'branches' => $branches,
            'stats'    => [
                'total'       => (int)($stats['total'] ?? 0),
                'active'      => (int)($stats['active'] ?? 0),
                'inactive'    => (int)($stats['inactive'] ?? 0),
                'total_staff' => (int)($stats['total_staff'] ?? 0),
                'total_txns'  => (int)($stats['total_txns'] ?? 0)
            ]
        ];
    }

    public function getDepartmentAnalytics(): array
    {
        return [
            'success' => true,
            'data' => $this->repo->getDepartmentAnalytics()
        ];
    }

    public function getStaffList(array $filters, int $page = 1, int $perPage = 20): array
    {
        $result = $this->repo->getStaffList($filters, $page, $perPage);
        return [
            'success'      => true,
            'staff'        => $result['staff'],
            'total'        => $result['total'],
            'pages'        => $result['pages'],
            'current_page' => $result['current_page'],
            'stats'        => $result['stats']
        ];
    }

    public function exportStaff(array $filters): \Generator
    {
        return $this->repo->exportStaffGenerator($filters);
    }

    public function getStaffDetailComprehensive(int $staffId): ?array
    {
        return $this->repo->getStaffDetailComprehensive($staffId);
    }

    public function executeStaffAction(int $staffId, string $action, ?string $reason, int $adminId, string $adminPassword): array
    {
        if (!$staffId || empty($action)) {
            throw new \App\Shared\Core\Exceptions\ApiException('Staff ID and action are required', 422);
        }

        if (!in_array($action, ['suspend', 'reinstate', 'reset_password', 'delete'])) {
            throw new \App\Shared\Core\Exceptions\ApiException('Invalid governance action', 422);
        }

        if (empty($adminPassword)) {
            throw new \App\Shared\Core\Exceptions\ApiException('Admin password is required for this action', 422);
        }

        if (!$this->verifyAdminPassword($adminId, $adminPassword)) {
            throw new \App\Shared\Core\Exceptions\ApiException('Invalid administrative password. Access denied.', 403);
        }

        if ($this->security) {
            $perm = match($action) {
                'suspend', 'reinstate' => 'suspend_staff',
                'delete' => 'delete_staff',
                default => 'add_staff'
            };
            try {
                $this->security->checkAdminPermission($adminId, $perm);
            } catch (\Throwable $e) {
                $this->security->checkAdminPermission($adminId, 'add_staff');
            }
        }

        $staff = $this->repo->findStaffRecord($staffId);
        if (!$staff) {
            throw new \App\Shared\Core\Exceptions\ApiException('Staff member not found', 404);
        }

        $staffIdFormatted = is_object($staff) ? $staff->staff_id : ($staff['staff_id'] ?? "STF-{$staffId}");
        $staffName = is_object($staff) ? $staff->full_name : ($staff['full_name'] ?? 'Staff Member');
        $staffEmail = is_object($staff) ? $staff->email : ($staff['email'] ?? '');
        $currentStatus = is_object($staff) ? $staff->status : ($staff['status'] ?? 'active');

        if ($action === 'suspend') {
            if ($currentStatus === 'suspended') {
                throw new \App\Shared\Core\Exceptions\ApiException('Staff is already suspended', 409);
            }
            $suspendedBy = $_SESSION['admin_name'] ?? 'Administrator';
            $suspendReason = !empty($reason) ? $reason : 'Administrative Review';
            $this->repo->suspendStaff($staffId, $suspendReason, $suspendedBy);

            \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
                new \App\Application\Events\GenericAuditAction(
                    'staff_suspended',
                    $staffIdFormatted,
                    \App\Domain\Enums\RoleEnum::STAFF->value,
                    $adminId,
                    \App\Domain\Enums\RoleEnum::ADMIN->value,
                    "Suspended staff: {$staffName} ({$staffIdFormatted}). Reason: {$suspendReason}"
                )
            );

            return ['success' => true, 'message' => 'Staff member suspended successfully'];
        } elseif ($action === 'reinstate') {
            if ($currentStatus !== 'suspended') {
                throw new \App\Shared\Core\Exceptions\ApiException('Staff is not suspended', 409);
            }
            $this->repo->reinstateStaff($staffId);

            \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
                new \App\Application\Events\GenericAuditAction(
                    'staff_reinstated',
                    $staffIdFormatted,
                    \App\Domain\Enums\RoleEnum::STAFF->value,
                    $adminId,
                    \App\Domain\Enums\RoleEnum::ADMIN->value,
                    "Reinstated staff: {$staffName} ({$staffIdFormatted})"
                )
            );

            return ['success' => true, 'message' => 'Staff member reinstated successfully'];
        } elseif ($action === 'reset_password') {
            $tempPass = $this->generateStrongPassword(12);
            $passwordHash = password_hash($tempPass, PASSWORD_BCRYPT);
            $this->repo->updateStaffPassword($staffId, $passwordHash);

            \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
                new \App\Application\Events\GenericAuditAction(
                    'staff_password_reset',
                    $staffIdFormatted,
                    \App\Domain\Enums\RoleEnum::STAFF->value,
                    $adminId,
                    \App\Domain\Enums\RoleEnum::ADMIN->value,
                    "Reset password for staff: {$staffName} ({$staffEmail})"
                )
            );

            return [
                'success'       => true,
                'message'       => 'Temporary password generated successfully',
                'temp_password' => $tempPass
            ];
        } elseif ($action === 'delete') {
            $this->repo->deleteStaff($staffId);

            \App\Shared\Core\Events\EventDispatcher::getInstance()->dispatch(
                new \App\Application\Events\GenericAuditAction(
                    'staff_deleted',
                    $staffIdFormatted,
                    \App\Domain\Enums\RoleEnum::STAFF->value,
                    $adminId,
                    \App\Domain\Enums\RoleEnum::ADMIN->value,
                    "Permanently soft-deleted staff: {$staffName} ({$staffEmail})"
                )
            );

            return ['success' => true, 'message' => 'Staff member deleted permanently'];
        }

        return ['success' => false, 'message' => 'Invalid action'];
    }
}
