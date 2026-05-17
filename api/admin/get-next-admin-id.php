<?php
/**
 * Finzo Banking System — Get Next Admin ID API
 * GET endpoint: returns the next available Admin ID (ADM00x).
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

if ($_SESSION['admin_role'] !== 'super_admin') {
    json_response(['success' => false, 'message' => 'Forbidden'], 403);
}

try {
    $stmt = $pdo->query("SELECT MAX(CAST(SUBSTRING(admin_id, 4) AS UNSIGNED)) as max_id FROM admin_users");
    $max_id = $stmt->fetchColumn() ?: 0;
    $next_id = 'STF' . str_pad($max_id + 1, 3, '0', STR_PAD_LEFT);

    json_response([
        'success' => true,
        'next_id' => $next_id
    ]);

} catch (PDOException $e) {
    error_log("Get Next Admin ID Error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to calculate next ID'], 500);
}
?>
