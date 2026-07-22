<?php

/**
 * Finzo Banking System — Account Statement PDF Generator
 * GET endpoint: generates and streams an account statement as PDF.
 * Uses TCPDF library.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 3) . '/Presentation/Middleware/auth-user.php';

// Check for TCPDF library
$tcpdf_path = dirname(__DIR__, 3) . '/vendor/tcpdf/tcpdf.php';
$tcpdf_autoload = dirname(__DIR__, 3) . '/vendor/autoload.php';

if (file_exists($tcpdf_autoload)) {
    require_once $tcpdf_autoload;
} elseif (file_exists($tcpdf_path)) {
    require_once $tcpdf_path;
} else {
    // Fallback: return JSON data for client-side PDF generation
    // This allows the frontend to generate PDF using browser print or jsPDF
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'TCPDF library not installed. Use browser print instead.']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Parse params
$from_date = $_GET['from_date'] ?? date('Y-m-d', strtotime('-30 days'));
$to_date   = $_GET['to_date'] ?? date('Y-m-d');

try {
    // Get account info
    $stmt = $pdo->prepare("
        SELECT a.account_no, a.account_type, a.balance, u.full_name, u.email, u.phone
        FROM accounts a JOIN users u ON a.user_id = u.customer_id
        WHERE u.id = ? AND a.status = 'active' LIMIT 1
    ");
    $stmt->execute([$user_id]);
    $account = $stmt->fetch();

    if (!$account) {
        json_response(['success' => false, 'message' => 'No active account found'], 404);
    }

    // Get account ID
    $stmt = $pdo->prepare("SELECT a.id FROM accounts a JOIN users u ON a.user_id = u.customer_id WHERE u.id = ? AND a.status = 'active' LIMIT 1");
    $stmt->execute([$user_id]);
    $account_id = $stmt->fetchColumn();

    // Get transactions
    $stmt = $pdo->prepare("
        SELECT t.txn_id, t.type, t.amount, t.balance_after, t.description, t.created_at,
               t.from_account_id, t.to_account_id
        FROM transactions t
        WHERE (t.from_account_id = ? OR t.to_account_id = ?)
            AND t.status = 'completed'
            AND DATE(t.created_at) BETWEEN ? AND ?
        ORDER BY t.created_at ASC
    ");
    $stmt->execute([$account_id, $account_id, $from_date, $to_date]);
    $transactions = $stmt->fetchAll();

    // Create PDF
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

    $pdf->SetCreator('Finzo Banking System');
    $pdf->SetAuthor('Finzo');
    $pdf->SetTitle('Account Statement');
    $pdf->SetSubject('Account Statement - ' . $account['account_no']);

    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(true);
    $pdf->SetAutoPageBreak(true, 15);
    $pdf->SetMargins(15, 15, 15);
    $pdf->AddPage();

    // Header
    $pdf->SetFont('helvetica', 'B', 20);
    $pdf->SetTextColor(26, 35, 126); // Finzo navy
    $pdf->Cell(0, 12, 'FINZO', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(0, 5, 'Online Banking System', 0, 1, 'L');
    $pdf->Ln(5);

    // Statement title
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 8, 'Account Statement', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 5, "Period: {$from_date} to {$to_date}", 0, 1, 'C');
    $pdf->Ln(5);

    // Account details
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(40, 6, 'Account Holder:', 0, 0);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 6, $account['full_name'], 0, 1);

    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(40, 6, 'Account No:', 0, 0);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 6, $account['account_no'], 0, 1);

    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(40, 6, 'Account Type:', 0, 0);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 6, ucfirst($account['account_type']), 0, 1);

    $pdf->Ln(5);

    // Transaction table header
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetFillColor(26, 35, 126);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(25, 7, 'Date', 1, 0, 'C', true);
    $pdf->Cell(30, 7, 'Txn ID', 1, 0, 'C', true);
    $pdf->Cell(20, 7, 'Type', 1, 0, 'C', true);
    $pdf->Cell(45, 7, 'Description', 1, 0, 'C', true);
    $pdf->Cell(25, 7, 'Debit', 1, 0, 'C', true);
    $pdf->Cell(25, 7, 'Credit', 1, 0, 'C', true);
    $pdf->Cell(25, 7, 'Balance', 1, 1, 'C', true);

    // Transaction rows
    $pdf->SetFont('helvetica', '', 7);
    $pdf->SetTextColor(0, 0, 0);
    $fill = false;

    foreach ($transactions as $txn) {
        if ($fill) {
            $pdf->SetFillColor(245, 245, 245);
        }

        $is_credit = ($txn['type'] === 'deposit' || $txn['type'] === 'interest_credit' ||
                     ($txn['type'] === 'transfer' && $txn['to_account_id'] == $account_id));

        $debit  = $is_credit ? '' : number_format($txn['amount'], 2);
        $credit = $is_credit ? number_format($txn['amount'], 2) : '';

        $date = date('d/m/Y', strtotime($txn['created_at']));
        $short_txn = substr($txn['txn_id'], 0, 15);

        $pdf->Cell(25, 6, $date, 1, 0, 'C', $fill);
        $pdf->Cell(30, 6, $short_txn, 1, 0, 'C', $fill);
        $pdf->Cell(20, 6, ucfirst($txn['type']), 1, 0, 'C', $fill);
        $pdf->Cell(45, 6, substr($txn['description'] ?? '', 0, 30), 1, 0, 'L', $fill);
        $pdf->Cell(25, 6, $debit, 1, 0, 'R', $fill);
        $pdf->Cell(25, 6, $credit, 1, 0, 'R', $fill);
        $pdf->Cell(25, 6, number_format($txn['balance_after'], 2), 1, 1, 'R', $fill);

        $fill = !$fill;
    }

    // Closing balance
    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(0, 7, 'Closing Balance: ' . CURRENCY_SYMBOL . number_format($account['balance'], 2), 0, 1, 'R');

    // Footer note
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'I', 8);
    $pdf->SetTextColor(150, 150, 150);
    $pdf->Cell(0, 5, 'This is a computer-generated statement and does not require a signature.', 0, 1, 'C');
    $pdf->Cell(0, 5, 'Generated on: ' . date('d/m/Y H:i:s'), 0, 1, 'C');

    // Output PDF
    $pdf->Output('Finzo_Statement_' . $account['account_no'] . '_' . date('Ymd') . '.pdf', 'D');
} catch (PDOException $e) {
    \App\Shared\Core\Logging\Logger::getInstance()->error('Statement PDF error: ', ['context' => $e->getMessage()]);
    json_response(['success' => false, 'message' => 'Failed to generate statement PDF'], 500);
} catch (Exception $e) {
    \App\Shared\Core\Logging\Logger::getInstance()->error('TCPDF error: ', ['context' => $e->getMessage()]);
    json_response(['success' => false, 'message' => 'PDF generation error: ' . $e->getMessage()], 500);
}
