<?php

namespace App\Presentation\Api\V1\Admin;

use App\Shared\Security\SecurityService;

use App\Application\Services\AdminStaffBranchService;
use App\Shared\Core\Exceptions\ApiException;
use PDOException;
use Exception;

class AdminStaffController
{
    protected SecurityService $security;

    protected AdminStaffBranchService $service;

    public function __construct(\App\Domain\Contracts\Services\AdminStaffBranchServiceInterface $service, ?\App\Domain\Contracts\Security\SecurityServiceInterface $security = null)
    {
        $this->security = $security;
        $this->service = $service;
    }



    public function addStaff()
    {

        $adminId = \App\Shared\Security\Auth::user()['id'];

        $data = [
            'full_name' => $this->security->cleanInput($_POST['full_name'] ?? ''),
            'date_of_birth' => $this->security->cleanInput($_POST['date_of_birth'] ?? ''),
            'gender' => $this->security->cleanInput($_POST['gender'] ?? ''),
            'personal_email' => $this->security->cleanInput($_POST['personal_email'] ?? ''),
            'phone' => $this->security->cleanInput($_POST['phone'] ?? ''),
            'emergency_contact_name' => $this->security->cleanInput($_POST['emergency_contact_name'] ?? ''),
            'emergency_contact_phone' => $this->security->cleanInput($_POST['emergency_contact_phone'] ?? ''),
            'designation' => $this->security->cleanInput($_POST['designation'] ?? 'Teller'),
            'branch_id' => ($_POST['branch_id'] ?? '' !== '' && $_POST['branch_id'] ?? '' !== 'null') ? intval($_POST['branch_id'] ?? '') : null,
            'date_of_joining' => $this->security->cleanInput($_POST['date_of_joining'] ?? date('Y-m-d')),
            'street' => $this->security->cleanInput($_POST['street'] ?? ''),
            'city' => $this->security->cleanInput($_POST['city'] ?? ''),
            'state' => $this->security->cleanInput($_POST['state'] ?? ''),
            'pincode' => $this->security->cleanInput($_POST['pincode'] ?? ''),
            'id_type' => $this->security->cleanInput($_POST['id_type'] ?? ''),
            'id_number' => $this->security->cleanInput($_POST['id_number'] ?? ''),
            'password' => $_POST['password'] ?? ''
        ];

        try {
            $result = $this->service->createStaff($data, $_FILES, $adminId);
            json_response(['success' => true, 'message' => 'Staff member added successfully', 'staff_id' => $result['staff_id'], 'staff_name' => $result['staff_name']], 201);
        } catch (\App\Domain\Exceptions\ValidationException $e) {
            json_response(['success' => false, 'message' => 'Validation failed', 'errors' => json_decode($e->getMessage(), true)], 422);
        } catch (ApiException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new ApiException('An error occurred while creating staff account: ' . $e->getMessage(), 500);
        }
    }

    public function getStaffDetail(?string $id = null, ?string $extra = null): array
    {
        $staffId = intval($_GET['staff_id'] ?? ($id ?? $_GET['id'] ?? null) ?? 0);
        if (!$staffId) {
            throw new ApiException('Staff ID is required', 422);
        }

        $data = $this->service->getStaffDetailComprehensive($staffId);
        if (!$data || empty($data['staff'])) {
            throw new ApiException('Staff member not found', 404);
        }

        return array_merge(['success' => true], $data);
    }

    public function generateStaffPassword(?string $id = null, ?string $extra = null): array
    {
        return ['success' => true, 'password' => $this->service->generateStrongPassword(12)];
    }

    public function getNextStaffId(?string $id = null, ?string $extra = null): array
    {
        return ['success' => true, 'next_id' => $this->service->generateStaffId()];
    }

    public function resetStaffPassword(?string $id = null, ?string $extra = null): array
    {
        $adminId = \App\Shared\Security\Auth::user()['id'];
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $staffId = intval($input['staff_id'] ?? ($id ?? $_POST['staff_id'] ?? null) ?? 0);
        $newPassword = $input['new_password'] ?? $_POST['new_password'] ?? '';

        if (!$staffId) {
            throw new ApiException('Staff ID is required', 422);
        }
        if (\App\Shared\Core\Validation\Validator::length($newPassword) < 8) {
            throw new ApiException('Password must be at least 8 characters', 422);
        }

        $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);
        $this->service->getRepo()->updateStaffPassword($staffId, $passwordHash);

        return ['success' => true, 'message' => 'Password updated successfully'];
    }

    public function staffAction(?string $id = null, ?string $extra = null): array
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $staffId = intval($input['staff_id'] ?? ($id ?? $_POST['staff_id'] ?? null) ?? 0);
        $action = $this->security->cleanInput($input['action'] ?? $_POST['action'] ?? '');
        $reason = $this->security->cleanInput($input['reason'] ?? $_POST['reason'] ?? '');
        $password = $input['password'] ?? $_POST['password'] ?? '';

        $adminId = \App\Shared\Security\Auth::user()['id'] ?? 0;

        return $this->service->executeStaffAction($staffId, $action, $reason, $adminId, $password);
    }

    public function uploadStaffPhoto(?string $id = null, ?string $extra = null): array
    {
        $staffId = intval(($id ?? $_POST['staff_id'] ?? null) ?? 0);
        if (!$staffId) {
            throw new ApiException('Staff ID is required', 422);
        }

        $staff = $this->service->getRepo()->findStaffRecord($staffId);
        if (!$staff) {
            throw new ApiException('Staff member not found', 404);
        }
        if (\App\Shared\Core\Validation\Validator::isEmpty($_FILES['photo']['name']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            throw new ApiException('Photo file is required', 422);
        }

        $file = $_FILES['photo'];
        $allowed_types = ['image/jpeg', 'image/png'];
        if (!in_array($file['type'], $allowed_types)) {
            throw new ApiException('Only JPG and PNG images are allowed', 422);
        }
        if ($file['size'] > MAX_UPLOAD_SIZE) {
            throw new ApiException('File size exceeds 5MB limit', 422);
        }

        $profilePhoto = is_object($staff) ? $staff->profile_photo : ($staff['profile_photo'] ?? null);
        if ($profilePhoto) {
            $oldPath = FINZO_ROOT . '/' . ltrim($profilePhoto, '/');
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'staff_photo_' . $staffId . '_' . time() . '.' . $ext;
        $targetDir = defined('UPLOAD_STAFF_PHOTOS_PATH') ? UPLOAD_STAFF_PHOTOS_PATH : FINZO_ROOT . '/uploads/staff-photos/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $targetDir . $filename)) {
            throw new ApiException('Failed to save uploaded file', 500);
        }

        $newPhotoPath = 'uploads/staff-photos/' . $filename;
        $this->service->getRepo()->updateStaffPhoto($staffId, $newPhotoPath);

        return [
            'success'   => true,
            'message'   => 'Photo updated successfully',
            'photo_url' => APP_URL . '/' . $newPhotoPath
        ];
    }

    public function getStaffList(?string $id = null, ?string $extra = null): array
    {
        $filters = [
            'search'    => $this->security->cleanInput($_GET['search'] ?? ''),
            'status'    => $this->security->cleanInput($_GET['status'] ?? 'all'),
            'role'      => $this->security->cleanInput($_GET['role'] ?? ($_GET['designation'] ?? 'all')),
            'branch_id' => intval($_GET['branch_id'] ?? 0),
            'filter'    => $this->security->cleanInput($_GET['filter'] ?? '')
        ];

        $page = max(1, intval($_GET['page'] ?? 1));
        $perPage = min(100, max(5, intval($_GET['per_page'] ?? 20)));

        return $this->service->getStaffList($filters, $page, $perPage);
    }

    public function exportStaff(): void
    {
        $filters = [
            'search'    => $this->security->cleanInput($_GET['search'] ?? ''),
            'status'    => $this->security->cleanInput($_GET['status'] ?? 'all'),
            'role'      => $this->security->cleanInput($_GET['role'] ?? ($_GET['designation'] ?? 'all')),
            'branch_id' => intval($_GET['branch_id'] ?? 0),
            'filter'    => $this->security->cleanInput($_GET['filter'] ?? '')
        ];

        $generator = $this->service->exportStaff($filters);

        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="finzo_staff_' . date('Y-m-d') . '.csv"');
        header('X-Accel-Buffering: no');

        $output = fopen('php://output', 'w');
        $headerWritten = false;
        foreach ($generator as $row) {
            if (!$headerWritten) {
                fputcsv($output, array_keys($row), ',', '"', "\\");
                $headerWritten = true;
            }
            fputcsv($output, $row, ',', '"', "\\");
        }
        fclose($output);

        if (defined('FINZO_TESTING') && FINZO_TESTING) {
            throw new \Exception('Exit called after staff export');
        }
        exit;
    }

    public function getSuspendedStaff(?string $id = null, ?string $extra = null): array
    {
        $filters = [
            'search'    => $this->security->cleanInput($_GET['search'] ?? ''),
            'status'    => 'suspended',
            'branch_id' => intval($_GET['branch_id'] ?? 0)
        ];

        $page = max(1, intval($_GET['page'] ?? 1));
        $perPage = min(50, max(10, intval($_GET['per_page'] ?? 20)));

        $result = $this->service->getStaffList($filters, $page, $perPage);
        return [
            'status' => 'success',
            'data'   => [
                'staff'        => $result['staff'],
                'total'        => $result['total'],
                'pages'        => $result['pages'],
                'current_page' => $result['current_page']
            ]
        ];
    }
}


