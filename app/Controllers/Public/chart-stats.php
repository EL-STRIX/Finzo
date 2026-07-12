<?php
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

try {
    // Generate the last 6 months list dynamically
    $months = [];
    for ($i = 5; $i >= 0; $i--) {
        $months[] = date('Y-m', strtotime("-$i months"));
    }

    $deposits = array_fill(0, 6, 0);
    $withdrawals = array_fill(0, 6, 0);
    $transfers = array_fill(0, 6, 0);

    // Query to aggregate by month and type
    $stmt = $pdo->query("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month_str,
            type,
            SUM(amount) as total_amount
        FROM transactions
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        AND status = 'completed'
        GROUP BY month_str, type
    ");

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $row) {
        $monthStr = $row['month_str'];
        $type = $row['type'];
        // Convert to thousands for the chart UI (as it uses 'K' suffix)
        // Ensure minimum 0.1 so small amounts show slightly above 0 if they exist
        $valueInK = max(0, round($row['total_amount'] / 1000, 1));
        if ($valueInK == 0 && $row['total_amount'] > 0) $valueInK = 0.1;

        $index = array_search($monthStr, $months);
        if ($index !== false) {
            if ($type === 'deposit') {
                $deposits[$index] = $valueInK;
            } elseif ($type === 'withdrawal') {
                $withdrawals[$index] = $valueInK;
            } elseif ($type === 'transfer') {
                $transfers[$index] = $valueInK;
            }
        }
    }

    // Format labels to just month abbreviations like 'Jan', 'Feb'
    $labels = array_map(function($m) {
        return date('M', strtotime($m . '-01'));
    }, $months);

    // Percentage changes (comparing last month to previous month for the small indicators)
    // If previous month is 0, we can just say +100% or 0%
    $currentMonthIndex = 5;
    $prevMonthIndex = 4;
    
    function calcChange($current, $prev) {
        if ($prev == 0) return $current > 0 ? 100 : 0;
        return round((($current - $prev) / $prev) * 100, 1);
    }

    $depositChange = calcChange($deposits[$currentMonthIndex], $deposits[$prevMonthIndex]);
    $withdrawalChange = calcChange($withdrawals[$currentMonthIndex], $withdrawals[$prevMonthIndex]);
    $transferChange = calcChange($transfers[$currentMonthIndex], $transfers[$prevMonthIndex]);

    echo json_encode([
        'success' => true,
        'labels' => $labels,
        'datasets' => [
            'deposits' => $deposits,
            'withdrawals' => $withdrawals,
            'transfers' => $transfers
        ],
        'changes' => [
            'deposits' => $depositChange,
            'withdrawals' => $withdrawalChange,
            'transfers' => $transferChange
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
