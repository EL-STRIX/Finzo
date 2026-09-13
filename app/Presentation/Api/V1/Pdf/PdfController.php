<?php
namespace App\Presentation\Api\V1\Pdf;

use App\Application\Services\PdfService;
use App\Shared\Core\Validation\Validator;

class PdfController
{
    private PdfService $service;

    public function __construct(PdfService $service)
    {
        $this->service = $service;
    }

    public function receipt()
    {
        if (session_status() === PHP_SESSION_NONE) \App\Shared\Security\SessionManager::start();
        $role = $_SESSION['role'] ?? null;
        $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

        $txnId = $_GET['id'] ?? $_GET['txn_id'] ?? '';
        if (Validator::isEmpty($txnId)) throw new \App\Shared\Core\Exceptions\ApiException('Transaction ID is required', 400);

        $txn = $this->service->getReceiptData($txnId, $role, $userId);
        if (!$txn) throw new \App\Shared\Core\Exceptions\ApiException('Transaction not found or access denied', 404);
        
        $statusColor = $txn['status'] === 'completed' ? 'text-emerald-600 bg-emerald-50 border-emerald-200' : 'text-amber-600 bg-amber-50 border-amber-200';
        $formattedAmount = CURRENCY_SYMBOL . ' ' . number_format($txn['amount'], 2);

        header('Content-Type: text/html; charset=utf-8');
        header("Content-Security-Policy: default-src 'self' 'unsafe-inline' https: data:; style-src 'self' 'unsafe-inline' https:; script-src 'self' 'unsafe-inline';");

        require FINZO_ROOT . '/app/Presentation/Views/Pdf/receipt-pdf.php';
        exit;
    }

    public function staffStatement()
    {
        if (session_status() === PHP_SESSION_NONE) \App\Shared\Security\SessionManager::start();
        $role = $_SESSION['role'] ?? null;
        $userId = $_SESSION['user_id'] ?? null;

        if (!in_array($role, ['admin', 'staff'])) throw new \App\Shared\Core\Exceptions\ApiException('Authentication required', 401);

        $staffId = max(1, intval($_GET['staff_id'] ?? 0));
        if (Validator::isEmpty($staffId)) throw new \App\Shared\Core\Exceptions\ApiException('Staff ID is required', 400);
        if ($role === 'staff' && $userId != $staffId) throw new \App\Shared\Core\Exceptions\ApiException('Access denied', 403);

        $data = $this->service->getStaffStatement($staffId);
        if (empty($data)) throw new \App\Shared\Core\Exceptions\ApiException('Staff not found', 404);
        
        $staff = $data['staff'];
        $transactions = $data['transactions'];

        header('Content-Type: text/html; charset=utf-8');
        header("Content-Security-Policy: default-src 'self' 'unsafe-inline' https: data:; style-src 'self' 'unsafe-inline' https:; script-src 'self' 'unsafe-inline';");

        require FINZO_ROOT . '/app/Presentation/Views/Pdf/staff-statement.php';
        exit;
    }

    public function statement()
    {
        if (session_status() === PHP_SESSION_NONE) \App\Shared\Security\SessionManager::start();
        $role = $_SESSION['role'] ?? null;
        $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

        if (!in_array($role, ['customer', 'user', 'staff', 'admin'], true)) {
            throw new \App\Shared\Core\Exceptions\ApiException('Authentication required', 401);
        }

        $targetUserId = max(0, intval($_GET['user_id'] ?? 0));
        if ($targetUserId === 0 && in_array($role, ['customer', 'user'], true)) {
            $targetUserId = (int)$userId;
        }
        if (in_array($role, ['customer', 'user'], true) && (int)$userId !== (int)$targetUserId) {
            throw new \App\Shared\Core\Exceptions\ApiException('Access denied', 403);
        }

        $fromDate = $_GET['from_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $toDate   = $_GET['to_date'] ?? date('Y-m-d');

        $data = $this->service->getStatement($targetUserId, $fromDate, $toDate);
        if (empty($data)) throw new \App\Shared\Core\Exceptions\ApiException('Account not found', 404);
        
        $user = $data['user'] ?? null;
        $account = $data['account'];
        $accounts = $data['accounts'] ?? [$account];
        $transactions = $data['transactions'];
        $selectedAccount = $account;

        header('Content-Type: text/html; charset=utf-8');
        header("Content-Security-Policy: default-src 'self' 'unsafe-inline' https: data:; style-src 'self' 'unsafe-inline' https:; script-src 'self' 'unsafe-inline';");

        require FINZO_ROOT . '/app/Presentation/Views/Pdf/statement.php';
        exit;
    }
}
