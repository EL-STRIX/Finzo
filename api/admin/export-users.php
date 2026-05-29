<?php
/**
 * Finzo Banking System — Admin Users CSV Export API
 * GET endpoint: Generates and downloads a CSV of users based on current filters.
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

try {
    $search       = clean_input($_GET['search'] ?? '');
    $status       = clean_input($_GET['status'] ?? 'all');

    // Build query
    $where_clauses = ["u.is_deleted = 0", "u.status != 'suspended'"];
    $params = [];

    // Status filter
    if ($status !== 'all' && in_array($status, ['pending', 'under_review', 'active', 'rejected'])) {
        $where_clauses[] = "u.status = ?";
        $params[] = $status;
    }

    // Search
    if (!empty($search)) {
        $where_clauses[] = "(u.full_name LIKE ? OR u.customer_id LIKE ?)";
        $search_param = "%{$search}%";
        $params[] = $search_param;
        $params[] = $search_param;
    }

    $where_sql = implode(' AND ', $where_clauses);

    // Fetch users for export (NO pagination)
    $sql = "
        SELECT
            u.customer_id, u.full_name, u.email, u.phone, u.status, u.created_at, u.last_login_at
        FROM users u
        WHERE {$where_sql}
        ORDER BY u.created_at DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Finzo_Users_Export_' . date('Y-m-d') . '.csv');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Output column headings
    fputcsv($output, ['Customer ID', 'Full Name', 'Email', 'Phone', 'Status', 'Joined Date', 'Last Active']);
    
    // Output data rows
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['customer_id'] ?: 'Pending',
            $row['full_name'],
            $row['email'],
            $row['phone'],
            ucfirst($row['status']),
            $row['created_at'],
            $row['last_login_at'] ?: 'Never'
        ]);
    }
    
    fclose($output);
    exit();

} catch (PDOException $e) {
    error_log("Admin users export API error: " . $e->getMessage());
    die("Failed to generate export file.");
}
