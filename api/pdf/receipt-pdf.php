<?php
/**
 * Finzo Banking System — Transaction Receipt PDF Generator
 * GET endpoint: generates and streams a single transaction receipt as PDF.
 * Uses TCPDF library.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/config/functions.php';

// Allow both user and staff to generate receipts
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['customer', 'staff', 'admin'])) {
    json_response(['success' => false, 'message' => 'Authentication required'], 401);
}

// Check for TCPDF
$tcpdf_autoload = dirname(__DIR__, 2) . '/vendor/autoload.php';
$tcpdf_path = dirname(__DIR__, 2) . '/vendor/tcpdf/tcpdf.php';

if (file_exists($tcpdf_autoload)) {
    require_once $tcpdf_autoload;
} elseif (file_exists($tcpdf_path)) {
    require_once $tcpdf_path;
} else {
    json_response(['success' => false, 'message' => 'TCPDF library not installed. Use browser print instead.']);
}

$txn_id = sanitize_input($_GET['id'] ?? $_GET['txn_id'] ?? '');
if (empty($txn_id)) {
    json_response(['success' => false, 'message' => 'Transaction ID is required'], 422);
}

try {
    // Fetch transaction
    $stmt = $pdo->prepare("
        SELECT
            t.txn_id, t.type, t.amount, t.balance_after, t.description,
            t.reference_note, t.status, t.created_at,
            fa.account_no as from_account_no,
            COALESCE(fu.full_name, 'N/A') as from_name,
            ta.account_no as to_account_no,
            COALESCE(tu.full_name, 'N/A') as to_name,
            COALESCE(s.staff_id, '') as staff_id,
            COALESCE(s.full_name, '') as staff_name,
            COALESCE(b.name, '') as branch_name
        FROM transactions t
        LEFT JOIN accounts fa ON t.from_account_id = fa.id
        LEFT JOIN users fu ON fa.user_id = fu.id
        LEFT JOIN accounts ta ON t.to_account_id = ta.id
        LEFT JOIN users tu ON ta.user_id = tu.id
        LEFT JOIN staff s ON t.processed_by_staff_id = s.id
        LEFT JOIN branches b ON s.branch_id = b.id
        WHERE t.txn_id = ?
    ");
    $stmt->execute([$txn_id]);
    $txn = $stmt->fetch();

    if (!$txn) {
        json_response(['success' => false, 'message' => 'Transaction not found'], 404);
    }

    // Verify ownership for customers
    if ($_SESSION['role'] === 'customer') {
        $stmt = $pdo->prepare("SELECT id FROM accounts WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user_accounts = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $stmt = $pdo->prepare("SELECT from_account_id, to_account_id FROM transactions WHERE txn_id = ?");
        $stmt->execute([$txn_id]);
        $txn_accts = $stmt->fetch();

        if (!in_array($txn_accts['from_account_id'], $user_accounts) &&
            !in_array($txn_accts['to_account_id'], $user_accounts)) {
            json_response(['success' => false, 'message' => 'Access denied'], 403);
        }
    }

    // Create PDF
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

    $pdf->SetCreator('Finzo Banking System');
    $pdf->SetAuthor('Finzo');
    $pdf->SetTitle('Transaction Receipt');
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetAutoPageBreak(false);
    $pdf->SetMargins(20, 20, 20);
    $pdf->AddPage();

    // Watermark
    $pdf->SetFont('helvetica', 'B', 50);
    $pdf->SetTextColor(240, 240, 240);
    $pdf->StartTransform();
    $pdf->Rotate(45, 105, 148);
    $pdf->Text(40, 130, 'FINZO VERIFIED');
    $pdf->StopTransform();

    // Header
    $pdf->SetTextColor(26, 35, 126);
    $pdf->SetFont('helvetica', 'B', 24);
    $pdf->Cell(0, 12, 'FINZO', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(0, 5, 'Online Banking System', 0, 1, 'C');
    $pdf->Ln(3);

    // Receipt title
    $pdf->SetDrawColor(26, 35, 126);
    $pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());
    $pdf->Ln(3);

    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 10, 'Transaction Receipt', 0, 1, 'C');

    // Status badge
    $status_color = ($txn['status'] === 'completed') ? [34, 139, 34] : [200, 0, 0];
    $pdf->SetTextColor(...$status_color);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 7, strtoupper($txn['status']), 0, 1, 'C');
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln(5);

    // Transaction details
    $pdf->SetFont('helvetica', '', 10);
    $left_margin = 40;
    $label_width = 50;

    $details = [
        'Transaction ID'  => $txn['txn_id'],
        'Date & Time'     => date('d M Y, h:i A', strtotime($txn['created_at'])),
        'Type'            => ucfirst($txn['type']),
        'Amount'          => CURRENCY_SYMBOL . ' ' . number_format($txn['amount'], 2),
    ];

    if ($txn['type'] === 'transfer') {
        $details['From Account'] = $txn['from_account_no'] . ' (' . $txn['from_name'] . ')';
        $details['To Account']   = $txn['to_account_no'] . ' (' . $txn['to_name'] . ')';
    } elseif ($txn['type'] === 'deposit') {
        $details['Credited To'] = $txn['to_account_no'] . ' (' . $txn['to_name'] . ')';
    } elseif ($txn['type'] === 'withdrawal') {
        $details['Debited From'] = $txn['from_account_no'] . ' (' . $txn['from_name'] . ')';
    }

    if (!empty($txn['description'])) {
        $details['Description'] = $txn['description'];
    }
    if (!empty($txn['reference_note'])) {
        $details['Reference'] = $txn['reference_note'];
    }
    if ($txn['balance_after'] !== null) {
        $details['Balance After'] = CURRENCY_SYMBOL . ' ' . number_format($txn['balance_after'], 2);
    }
    if (!empty($txn['staff_id'])) {
        $details['Processed By'] = $txn['staff_name'] . ' (' . $txn['staff_id'] . ')';
    }
    if (!empty($txn['branch_name'])) {
        $details['Branch'] = $txn['branch_name'];
    }

    foreach ($details as $label => $value) {
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell($label_width, 7, $label . ':', 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 7, $value, 0, 1);
    }

    // Separator
    $pdf->Ln(10);
    $pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());
    $pdf->Ln(10);

    // Footer notes
    $pdf->SetFont('helvetica', 'I', 8);
    $pdf->SetTextColor(150, 150, 150);
    $pdf->Cell(0, 5, 'This is a computer-generated receipt and does not require a signature.', 0, 1, 'C');
    $pdf->Cell(0, 5, 'For any queries, please contact Finzo Customer Support.', 0, 1, 'C');
    $pdf->Cell(0, 5, 'Generated on: ' . date('d/m/Y H:i:s'), 0, 1, 'C');

    // Output
    $pdf->Output('Finzo_Receipt_' . $txn['txn_id'] . '.pdf', 'D');

} catch (PDOException $e) {
    error_log("Receipt PDF error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to generate receipt'], 500);
} catch (Exception $e) {
    error_log("TCPDF error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'PDF generation error'], 500);
}
