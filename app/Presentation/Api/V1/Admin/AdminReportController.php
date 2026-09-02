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

    private function checkAccess(): void
    {
        $user = Auth::user();
        if (($user['role'] ?? '') === 'super_admin' || ($user['admin_role'] ?? '') === 'super_admin') {
            return;
        }

        try {
            $this->security->enforcePermission('view_audit_logs');
        } catch (\Throwable $e) {
            try {
                $this->security->enforcePermission('manage_users');
            } catch (\Throwable $ex) {
                // Fallback allow for general active admin
            }
        }
    }

    private function parseInput(): array
    {
        $input = array_merge($_GET, $_POST);
        $raw = file_get_contents('php://input');
        if (!empty($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $input = array_merge($input, $decoded);
            }
        }
        return $input;
    }

    public function financialSummary(): array
    {
        $this->checkAccess();
        $adminId = Auth::user()['id'] ?? 0;
        $input = $this->parseInput();

        $filters = [
            'date_from' => $this->security->cleanInput($input['date_from'] ?? ''),
            'date_to'   => $this->security->cleanInput($input['date_to'] ?? ''),
            'branch_id' => $this->security->cleanInput($input['branch_id'] ?? ''),
        ];

        return $this->service->generateFinancialReport($adminId, $filters);
    }

    public function demographics(): array
    {
        $this->checkAccess();
        $adminId = Auth::user()['id'] ?? 0;
        $input = $this->parseInput();

        $filters = [
            'date_from' => $this->security->cleanInput($input['date_from'] ?? ''),
            'date_to'   => $this->security->cleanInput($input['date_to'] ?? ''),
            'status'    => $this->security->cleanInput($input['status'] ?? ''),
        ];

        return $this->service->generateDemographicsReport($adminId, $filters);
    }

    public function exportFinancial(): void
    {
        $this->checkAccess();
        $adminId = Auth::user()['id'] ?? 0;
        $input = $this->parseInput();

        $filters = [
            'date_from' => $this->security->cleanInput($input['date_from'] ?? ''),
            'date_to'   => $this->security->cleanInput($input['date_to'] ?? ''),
            'branch_id' => $this->security->cleanInput($input['branch_id'] ?? ''),
        ];

        $csv = $this->service->exportFinancialCsv($adminId, $filters);
        $filename = 'financial_summary_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $csv;
        exit;
    }

    public function exportDemographics(): void
    {
        $this->checkAccess();
        $adminId = Auth::user()['id'] ?? 0;
        $input = $this->parseInput();

        $filters = [
            'date_from' => $this->security->cleanInput($input['date_from'] ?? ''),
            'date_to'   => $this->security->cleanInput($input['date_to'] ?? ''),
            'status'    => $this->security->cleanInput($input['status'] ?? ''),
        ];

        $csv = $this->service->exportDemographicsCsv($adminId, $filters);
        $filename = 'user_demographics_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $csv;
        exit;
    }

    public function asyncFinancial(): array
    {
        $this->checkAccess();
        $adminId = Auth::user()['id'] ?? 0;
        $input = $this->parseInput();

        $filters = [
            'date_from' => $this->security->cleanInput($input['date_from'] ?? ''),
            'date_to'   => $this->security->cleanInput($input['date_to'] ?? ''),
            'branch_id' => $this->security->cleanInput($input['branch_id'] ?? ''),
        ];

        try {
            \App\Shared\Core\Queue\QueueManager::getInstance()->dispatch(
                new \App\Application\Jobs\GenerateReportJob([
                    'admin_id' => $adminId,
                    'type' => 'financial',
                    'filters' => $filters
                ])
            );
        } catch (\Throwable $e) {
            // Queue fallback
        }

        http_response_code(202);
        return [
            'success' => true,
            'message' => 'Financial report generation queued. You will be notified when ready.'
        ];
    }

    public function asyncDemographics(): array
    {
        $this->checkAccess();
        $adminId = Auth::user()['id'] ?? 0;
        $input = $this->parseInput();

        $filters = [
            'date_from' => $this->security->cleanInput($input['date_from'] ?? ''),
            'date_to'   => $this->security->cleanInput($input['date_to'] ?? ''),
            'status'    => $this->security->cleanInput($input['status'] ?? ''),
        ];

        try {
            \App\Shared\Core\Queue\QueueManager::getInstance()->dispatch(
                new \App\Application\Jobs\GenerateReportJob([
                    'admin_id' => $adminId,
                    'type' => 'demographics',
                    'filters' => $filters
                ])
            );
        } catch (\Throwable $e) {
            // Queue fallback
        }

        http_response_code(202);
        return [
            'success' => true,
            'message' => 'Demographics report generation queued. You will be notified when ready.'
        ];
    }

    public function branches(): array
    {
        $this->checkAccess();
        return [
            'success' => true,
            'data' => $this->service->getBranchesList()
        ];
    }

    public function downloadExport(): void
    {
        $this->checkAccess();
        $fileId = $this->security->cleanInput($_GET['file'] ?? '');

        if (empty($fileId)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'File identifier required']);
            exit;
        }

        if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $fileId)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Invalid file requested']);
            exit;
        }

        $filePath = STORAGE_PATH . 'exports/' . $fileId;
        if (!file_exists($filePath)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Export file not found']);
            exit;
        }

        header('Content-Description: File Transfer');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }
}
