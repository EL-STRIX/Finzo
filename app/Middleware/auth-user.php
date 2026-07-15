<?php
use App\Core\Exceptions\ApiException;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    $e = new ApiException("Unauthorized access", 401);
    json_response(['success' => false, 'message' => $e->getMessage()], 401);
}
