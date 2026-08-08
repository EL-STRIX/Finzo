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

        try {
            $data = $this->service->getJournalEntries($page, $per_page);
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

        $adminId = $_SESSION['admin_id'];
        $periodId = $this->security->cleanInput($_POST['period_id'] ?? '');

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
}
