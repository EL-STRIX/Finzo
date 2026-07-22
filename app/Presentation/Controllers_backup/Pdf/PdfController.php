<?php
namespace App\Presentation\Controllers\Pdf;

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
        if (session_status() === PHP_SESSION_NONE) session_start();
        $role = $_SESSION['role'] ?? null;
        $userId = $_SESSION['user_id'] ?? null;

        $txnId = $_GET['id'] ?? $_GET['txn_id' '';
        if (Validator::isEmpty($txnId)) die('Transaction ID is required');

        $txn = $this->service->getReceiptData($txnId, $role, $userId);
        if (!$txn) die('Transaction not found or access denied');
        
        $statusColor = $txn['status'] === 'completed' ? 'text-emerald-600 bg-emerald-50 border-emerald-200' : 'text-amber-600 bg-amber-50 border-amber-200';
        $formattedAmount = CURRENCY_SYMBOL . ' ' . number_format($txn['amount'], 2);

        require FINZO_ROOT . '/app/Presentation/Views/Pdf/receipt-pdf.php';
    }

    public function staffStatement()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $role = $_SESSION['role'] ?? null;
        $userId = $_SESSION['user_id'] ?? null;

        if (!in_array($role, ['admin', 'staff'])) die('Authentication required');

        $staffId = max(1, intval($_GET['staff_id'] ?? 0));
        if (Validator::isEmpty($staffId)) die('Staff ID is required');
        if ($role === 'staff' && $userId != $staffId) die('Access denied');

        $data = $this->service->getStaffStatement($staffId);
        if (empty($data)) die('Staff not found');
        
        $staff = $data['staff'];
        $transactions = $data['transactions'];

        require FINZO_ROOT . '/app/Presentation/Views/Pdf/staff-statement.php';
    }

    public function statement()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $role = $_SESSION['role'] ?? null;
        $userId = $_SESSION['user_id'] ?? null;

        if (!in_array($role, ['customer', 'staff', 'admin'])) die('Authentication required');

        $targetUserId = max(1, intval($_GET['user_id'] ?? 0));
        if ($targetUserId === 0 && $role === 'customer') $targetUserId = $userId;
        if ($role === 'customer' && $userId != $targetUserId) die('Access denied');

        $fromDate = $_GET['from_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $toDate   = $_GET['to_date'] ?? date('Y-m-d');

        $data = $this->service->getStatement($targetUserId, $fromDate, $toDate);
        if (empty($data)) die('Account not found');
        
        $account = $data['account'];
        $transactions = $data['transactions'];

        require FINZO_ROOT . '/app/Presentation/Views/Pdf/statement.php';
    }
}
