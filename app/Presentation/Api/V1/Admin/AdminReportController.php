<?php

namespace App\Presentation\Api\V1\Admin;

use App\Domain\Contracts\Services\ReportServiceInterface;
use App\Shared\Security\SecurityService;
use App\Shared\Security\Auth;

class AdminReportController
{
    protected ReportServiceInterface $service;
    protected SecurityService $security;

    public function __construct(ReportServiceInterface $service, ?SecurityService $security = null)
    {
        $this->service = $service;
        $this->security = $security ?? new SecurityService();
    }

    public function financialSummary(): array
    {
        $adminId = Auth::user()['id'] ?? 0;
        $filters = [
            'date_from' => $this->security->cleanInput($_GET['date_from'] ?? ''),
            'date_to'   => $this->security->cleanInput($_GET['date_to'] ?? ''),
            'branch_id' => $this->security->cleanInput($_GET['branch_id'] ?? ''),
        ];

        $isAsync = filter_var($_GET['async'] ?? false, FILTER_VALIDATE_BOOLEAN);
        if ($isAsync) {
            \App\Shared\Core\Queue\QueueManager::getInstance()->dispatch(
                new \App\Application\Jobs\GenerateReportJob([
                    'admin_id' => $adminId,
                    'type' => 'financial',
                    'filters' => $filters
                ])
            );
            http_response_code(202);
            echo json_encode(['success' => true, 'message' => 'Financial report generation started asynchronously.']);
            exit;
        }

        return $this->service->generateFinancialReport($adminId, $filters);
    }

    public function demographics(): array
    {
        $adminId = Auth::user()['id'] ?? 0;
        $filters = [
            'date_from' => $this->security->cleanInput($_GET['date_from'] ?? ''),
            'date_to'   => $this->security->cleanInput($_GET['date_to'] ?? ''),
            'status'    => $this->security->cleanInput($_GET['status'] ?? ''),
        ];

        $isAsync = filter_var($_GET['async'] ?? false, FILTER_VALIDATE_BOOLEAN);
        if ($isAsync) {
            \App\Shared\Core\Queue\QueueManager::getInstance()->dispatch(
                new \App\Application\Jobs\GenerateReportJob([
                    'admin_id' => $adminId,
                    'type' => 'demographics',
                    'filters' => $filters
                ])
            );
            http_response_code(202);
            echo json_encode(['success' => true, 'message' => 'Demographics report generation started asynchronously.']);
            exit;
        }

        return $this->service->generateDemographicsReport($adminId, $filters);
    }

    public function downloadExport(): void
    {
        $adminId = Auth::user()['id'] ?? 0;
        $fileId = $this->security->cleanInput($_GET['file'] ?? '');

        if (empty($fileId)) {
            http_response_code(400);
            die(json_encode(['success' => false, 'message' => 'File ID is required']));
        }

        // Validate the file path securely to prevent directory traversal
        if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $fileId)) {
            http_response_code(403);
            die(json_encode(['success' => false, 'message' => 'Invalid file requested']));
        }

        $filePath = STORAGE_PATH . 'exports/' . $fileId;
        if (!file_exists($filePath)) {
            http_response_code(404);
            die(json_encode(['success' => false, 'message' => 'File not found']));
        }

        // Output headers for file download
        header('Content-Description: File Transfer');
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));

        // Read the file and send to the output buffer
        readfile($filePath);
        
        // C-01: Optional, delete after download to save space if needed
        // unlink($filePath);
        exit;
    }
}
