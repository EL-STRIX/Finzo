<?php

namespace App\Presentation\Api\V1\Admin;

use App\Domain\Contracts\Services\LedgerServiceInterface;
use App\Domain\Contracts\Security\SecurityServiceInterface;

class LedgerController
{
    protected LedgerServiceInterface $service;
    protected SecurityServiceInterface $security;

    public function __construct(LedgerServiceInterface $service, SecurityServiceInterface $security)
    {
        $this->service = $service;
        $this->security = $security;
    }

    public function getChartOfAccounts()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        try {
            $accounts = $this->service->getChartOfAccounts();
            json_response([
                'success' => true,
                'accounts' => $accounts
            ]);
        } catch (\Exception $e) {
            \App\Shared\Core\Logging\Logger::getInstance()->error('Ledger COA error: ', ['context' => $e->getMessage()]);
            json_response(['success' => false, 'message' => 'Failed to load Chart of Accounts'], 500);
        }
    }

    public function getJournals()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $page = max(1, intval($_GET['page'] ?? 1));
        $per_page = min(100, max(10, intval($_GET['per_page'] ?? 20)));
        $search = trim($_GET['search'] ?? '');
        $type = trim($_GET['type'] ?? '');

        try {
            $data = $this->service->getJournalEntries($page, $per_page, $search, $type);
            $data['success'] = true;
            json_response($data);
        } catch (\Exception $e) {
            \App\Shared\Core\Logging\Logger::getInstance()->error('Ledger Journals error: ', ['context' => $e->getMessage()]);
            json_response(['success' => false, 'message' => 'Failed to load Journal Entries'], 500);
        }
    }

    public function getActivePeriod()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        try {
            $period = $this->service->getActivePeriod();
            json_response([
                'success' => true,
                'period' => $period,
                'has_active' => $period !== null
            ]);
        } catch (\Exception $e) {
            json_response(['success' => false, 'message' => 'Failed to fetch accounting period'], 500);
        }
    }

    public function closePeriod()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true) ?: [];
        $data = array_merge($_POST, $json);

        $adminId = (int)($_SESSION['admin_id'] ?? 0);
        $periodId = $this->security->cleanInput($data['period_id'] ?? '');

        if (!$periodId || !is_numeric($periodId)) {
            json_response(['success' => false, 'message' => 'Valid Period ID is required'], 422);
        }

        try {
            $this->service->closePeriod((int)$periodId, $adminId);
            json_response([
                'success' => true,
                'message' => 'Accounting period successfully closed.'
            ]);
        } catch (\App\Shared\Core\Exceptions\BusinessRuleException $e) {
            json_response(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            \App\Shared\Core\Logging\Logger::getInstance()->error('Close period error: ', ['context' => $e->getMessage()]);
            json_response(['success' => false, 'message' => 'Failed to close accounting period'], 500);
        }
    }

    public function openPeriod()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true) ?: [];
        $data = array_merge($_POST, $json);

        $adminId = (int)($_SESSION['admin_id'] ?? 0);
        $name = $this->security->cleanInput($data['name'] ?? $data['period_name'] ?? '');
        $startDate = $this->security->cleanInput($data['start_date'] ?? '');
        $endDate = $this->security->cleanInput($data['end_date'] ?? '');

        if (empty($name)) {
            json_response(['success' => false, 'message' => 'Period name is required'], 422);
        }

        if (empty($startDate) || empty($endDate)) {
            json_response(['success' => false, 'message' => 'Start date and end date are required'], 422);
        }

        try {
            $periodId = $this->service->openPeriod($name, $startDate, $endDate, $adminId);
            json_response([
                'success'   => true,
                'message'   => "Accounting period '{$name}' opened successfully.",
                'period_id' => $periodId
            ]);
        } catch (\App\Shared\Core\Exceptions\BusinessRuleException $e) {
            json_response(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            \App\Shared\Core\Logging\Logger::getInstance()->error('Open period error: ', ['context' => $e->getMessage()]);
            json_response(['success' => false, 'message' => 'Failed to open accounting period'], 500);
        }
    }

    public function getSummary()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        try {
            $summary = $this->service->getLedgerSummary();
            json_response([
                'success' => true,
                'summary' => $summary
            ]);
        } catch (\Exception $e) {
            \App\Shared\Core\Logging\Logger::getInstance()->error('Ledger Summary error: ', ['context' => $e->getMessage()]);
            json_response(['success' => false, 'message' => 'Failed to load ledger summary'], 500);
        }
    }

    public function getJournalDetail(?string $id = null)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $journalId = (int)($id ?? $_GET['id'] ?? 0);
        if ($journalId <= 0) {
            json_response(['success' => false, 'message' => 'Valid Journal ID is required'], 422);
        }

        try {
            $journal = $this->service->getJournalDetail($journalId);
            if (!$journal) {
                json_response(['success' => false, 'message' => 'Journal entry not found'], 404);
            }
            json_response([
                'success' => true,
                'journal' => $journal
            ]);
        } catch (\Exception $e) {
            \App\Shared\Core\Logging\Logger::getInstance()->error('Ledger Journal Detail error: ', ['context' => $e->getMessage()]);
            json_response(['success' => false, 'message' => 'Failed to load journal detail'], 500);
        }
    }

    public function exportJournals()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $search = trim($_GET['search'] ?? '');
        $type = trim($_GET['type'] ?? '');

        try {
            $csv = $this->service->exportJournals($search, $type);
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="Finzo_General_Ledger_' . date('Ymd_His') . '.csv"');
            echo $csv;
            exit;
        } catch (\Exception $e) {
            \App\Shared\Core\Logging\Logger::getInstance()->error('Export journals error: ', ['context' => $e->getMessage()]);
            json_response(['success' => false, 'message' => 'Failed to export journals'], 500);
        }
    }
}
