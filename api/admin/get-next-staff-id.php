<?php
/**
 * Finzo Banking System — Get Next Staff ID API
 * GET endpoint: returns the next available Staff ID (STF00x).
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
    require_once dirname(__DIR__, 2) . '/config/functions.php';
    
    $next_id = generate_staff_id($pdo);

    json_response([
        'success' => true,
        'next_id' => $next_id
    ]);

} catch (PDOException $e) {
    error_log("Get Next Staff ID Error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to calculate next ID'], 500);
}
?>
