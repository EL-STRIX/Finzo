<?php
namespace App\Controllers\Admin;

use App\Services\AdminStaffBranchService;
use Exception;
use PDOException;
use DateTime;
use PDO;

class AdminStaffBranchController {
    use AdminStaffBranchControllerTrait1;
    use AdminStaffBranchControllerTrait2;
    use AdminStaffBranchControllerTrait3;

    protected AdminStaffBranchService $service;

    public function __construct(AdminStaffBranchService $service) {
        $this->service = $service;
    }

    protected function checkMethod($method) {
        if ($_SERVER['REQUEST_METHOD'] !== $method) json_response(['success' => false, 'message' => 'Method not allowed'], 405);
    }

    protected function checkCsrf() {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $input['csrf_token'] ?? $_POST['csrf_token'] ?? '';
        if (!validate_csrf_token($csrf)) json_response(['success' => false, 'message' => 'Invalid CSRF token'], 403);
    }

    public function addStaff() {
        $this->checkMethod('POST');
        $this->checkCsrf();
        $admin_id = $_SESSION['admin_id'];
        $repo = $this->service->getRepo();
        $pdo = $repo->getPDO();
        check_admin_permission($pdo, $admin_id, 'add_staff');

        $data = [
            'full_name' => clean_input($_POST['full_name'] ?? ''),
            'date_of_birth' => clean_input($_POST['date_of_birth'] ?? ''),
            'gender' => clean_input($_POST['gender'] ?? ''),
            'personal_email' => clean_input($_POST['personal_email'] ?? ''),
            'phone' => clean_input($_POST['phone'] ?? ''),
            'emergency_contact_name' => clean_input($_POST['emergency_contact_name'] ?? ''),
            'emergency_contact_phone' => clean_input($_POST['emergency_contact_phone'] ?? ''),
            'designation' => clean_input($_POST['designation'] ?? 'Teller'),
            'branch_id' => ($_POST['branch_id'] ?? '' !== '' && $_POST['branch_id'] ?? '' !== 'null') ? intval($_POST['branch_id'] ?? '') : null,
            'date_of_joining' => clean_input($_POST['date_of_joining'] ?? date('Y-m-d')),
            'street' => clean_input($_POST['street'] ?? ''),
            'city' => clean_input($_POST['city'] ?? ''),
            'state' => clean_input($_POST['state'] ?? ''),
            'pincode' => clean_input($_POST['pincode'] ?? ''),
            'id_type' => clean_input($_POST['id_type'] ?? ''),
            'id_number' => clean_input($_POST['id_number'] ?? ''),
            'password' => $_POST['password'] ?? ''
        ];
        $data['department'] = 'Teller Operations';
        if (empty($data['date_of_joining'])) $data['date_of_joining'] = date('Y-m-d');

        $errors = $this->service->validateStaffData($data);

        $id_front_path = $this->service->handleStaffUpload($_FILES['id_front'] ?? null, 'staff_doc_front_', 'staff-documents', $errors, 'id_front', true);
        $id_back_path = $this->service->handleStaffUpload($_FILES['id_back'] ?? null, 'staff_doc_back_', 'staff-documents', $errors, 'id_back', true);
        $profile_photo_path = $this->service->handleStaffUpload($_FILES['profile_photo'] ?? null, 'staff_photo_', 'staff-photos', $errors, 'profile_photo');

        if (!empty($errors)) json_response(['success' => false, 'message' => 'Validation failed', 'errors' => $errors], 422);

        try {
            $repo->beginTransaction();
            $data['staff_id'] = $this->service->generateStaffId();
            $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
            
            $admin = $repo->findAdminById($admin_id);
            $data['created_by'] = $admin['admin_id'] ?? null;
            $data['profile_photo'] = $profile_photo_path;
            $data['front_image'] = $id_front_path;
            $data['back_image'] = $id_back_path;

            $repo->insertStaff($data);
            $repo->commit();

            write_audit_log($pdo, 'staff_created', $data['staff_id'], 'staff', $admin_id, 'admin', "Staff created: {$data['full_name']} | ID: {$data['staff_id']} | Branch: " . ($data['branch_id'] ?? 'None'));

            json_response(['success' => true, 'message' => 'Staff member added successfully', 'staff_id' => $data['staff_id'], 'staff_name' => $data['full_name']], 201);
        } catch (Exception $e) {
            if ($repo->inTransaction()) $repo->rollBack();
            if ($profile_photo_path) @unlink(FINZO_ROOT . '/' . $profile_photo_path);
            if ($id_front_path) @unlink(FINZO_ROOT . '/' . $id_front_path);
            if ($id_back_path) @unlink(FINZO_ROOT . '/' . $id_back_path);
            json_response(['success' => false, 'message' => 'An error occurred while creating staff account: ' . $e->getMessage()], 500);
        }
    }

    public function getStaffDetail() {
        $this->checkMethod('GET');
        $staff_id = intval($_GET['staff_id'] ?? $_GET['id'] ?? 0);
        if (!$staff_id) json_response(['success' => false, 'message' => 'Staff ID is required'], 422);

        try {
            $repo = $this->service->getRepo();
            $staff = $repo->getStaffDetail($staff_id);
            if (!$staff) json_response(['success' => false, 'message' => 'Staff member not found'], 404);

            $base_url = APP_URL . '/';
            if ($staff['profile_photo']) $staff['profile_photo_url'] = $base_url . $staff['profile_photo'];
            if ($staff['front_image']) $staff['front_image_url'] = $base_url . $staff['front_image'];
            if ($staff['back_image']) $staff['back_image_url'] = $base_url . $staff['back_image'];

            $performance = $repo->getStaffPerformance($staff_id);
            json_response(['success' => true, 'staff' => $staff, 'performance' => $performance]);
        } catch (PDOException $e) {
            json_response(['success' => false, 'message' => 'Failed to load staff details'], 500);
        }
    }
}
