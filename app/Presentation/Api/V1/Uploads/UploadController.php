<?php
namespace App\Presentation\Api\V1\Uploads;

use App\Application\Services\UploadService;
use App\Shared\Core\Validation\Validator;

class UploadController
{
    private UploadService $service;

    public function __construct(UploadService $service)
    {
        $this->service = $service;
    }

    public function uploadBranchCert()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['success' => false, 'message' => 'Method not allowed'];
        }

        if (!Validator::isSet($_FILES['certificate']) || $_FILES['certificate']['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'No certificate file uploaded'];
        }

        $branchId = intval($_POST['branch_id'] ?? 0);
        $result = $this->service->handleBranchCertUpload($_FILES['certificate'], $branchId);
        
        http_response_code($result['status']);
        unset($result['status']);
        return $result;
    }

    public function uploadIdDocument()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['success' => false, 'message' => 'Method not allowed'];
        }

        if (!Validator::isSet($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'No document file uploaded'];
        }

        if (session_status() === PHP_SESSION_NONE) {
            \App\Shared\Security\SessionManager::start();
        }
        
        $userId = $_SESSION['user_id'] ?? 0;
        if ($userId === 0) {
            return ['success' => false, 'message' => 'Not authenticated'];
        }

        $result = $this->service->handleIdDocumentUpload($_FILES['document'], $userId);
        
        http_response_code($result['status']);
        unset($result['status']);
        return $result;
    }
}