<?php
/**
 * Enterprise Database Health & Readiness Probe
 * Suitable for Kubernetes, CI/CD, and Load Balancers.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = \App\Core\Database::getInstance();
    
    $status = [
        'status' => 'healthy',
        'timestamp' => date('c'),
        'components' => [
            'database' => [
                'status' => 'up',
                'connection' => 'established'
            ]
        ],
        'metrics' => [],
        'alerts' => []
    ];

    // 1. Connection & Uptime Monitoring
    $stmt = $db->query("SHOW GLOBAL STATUS LIKE 'Uptime'");
    $uptime = $stmt->fetch(PDO::FETCH_ASSOC);
    $status['metrics']['uptime_seconds'] = (int)$uptime['Value'];

    // 2. Active Connections Monitoring
    $stmt = $db->query("SHOW GLOBAL STATUS LIKE 'Threads_connected'");
    $threads = $stmt->fetch(PDO::FETCH_ASSOC);
    $status['metrics']['active_connections'] = (int)$threads['Value'];

    // 3. Ledger Health Monitoring (Critical Check)
    try {
        $stmt = $db->query("SELECT COUNT(*) FROM vw_monitoring_ledger_health");
        $unbalanced = (int)$stmt->fetchColumn();
        $status['components']['ledger'] = [
            'status' => $unbalanced === 0 ? 'up' : 'critical',
            'unbalanced_journals' => $unbalanced
        ];
        if ($unbalanced > 0) {
            $status['status'] = 'degraded';
            $status['alerts'][] = "Ledger integrity compromised: {$unbalanced} unbalanced journals detected.";
        }
    } catch (\Exception $e) {
        // View might not exist yet
    }

    // 4. Storage Monitoring
    try {
        $stmt = $db->query("SELECT SUM(size_mb) as total_size, SUM(free_mb) as total_free FROM vw_monitoring_storage");
        $storage = $stmt->fetch(PDO::FETCH_ASSOC);
        $status['metrics']['storage_mb'] = (float)$storage['total_size'];
        $status['metrics']['storage_free_mb'] = (float)$storage['total_free'];
    } catch (\Exception $e) {}

    // Output with proper HTTP status
    if ($status['status'] === 'degraded') {
        http_response_code(503); // Service Unavailable / Degraded
    } else {
        http_response_code(200);
    }
    
    echo json_encode($status, JSON_PRETTY_PRINT);

} catch (\Exception $e) {
    http_response_code(503);
    echo json_encode([
        'status' => 'critical',
        'timestamp' => date('c'),
        'error' => 'Database connection failed or system is unavailable',
        'message' => getenv('APP_DEBUG') === 'true' ? $e->getMessage() : 'Service Unavailable'
    ]);
}
