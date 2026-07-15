<?php
use App\Core\Exceptions\ApiException;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'admin') {
    $e = new ApiException("Unauthorized access", 401);
    json_response(['success' => false, 'message' => $e->getMessage()], 401);
}
