<?php
/**
 * Finzo Banking System — Account Settings API
 * GET: returns daily_limit and used_today
 * POST: updates daily_limit
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-user.php';

$user_id = $_SESSION['user_id'];

try {
    // Get user's account ID
    $stmt = $pdo->prepare("SELECT a.id, a.daily_limit FROM accounts a JOIN users u ON a.user_id = u.customer_id WHERE u.id = ? AND a.status = 'active' LIMIT 1");
    $stmt->execute([$user_id]);
    $account = $stmt->fetch();

    if (!$account) {
        json_response(['success' => false, 'message' => 'No active account found'], 404);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Calculate used today based on completed/pending transfers
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(amount), 0) 
            FROM transactions 
            WHERE from_account_id = ? 
              AND type = 'transfer'
              AND DATE(created_at) = CURDATE() 
              AND status IN ('completed', 'pending')
        ");
        $stmt->execute([$account['id']]);
        $used_today = $stmt->fetchColumn();

        json_response([
            'success' => true,
            'daily_limit' => (float)$account['daily_limit'],
            'used_today' => (float)$used_today
        ]);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $daily_limit = clean_input($_POST['daily_limit'] ?? '');
        
        if (!is_numeric($daily_limit) || $daily_limit < 1000 || $daily_limit > 100000) {
            json_response(['success' => false, 'message' => 'Invalid limit value. Must be between 1,000 and 1,00,000']);
        }

        $stmt = $pdo->prepare("UPDATE accounts SET daily_limit = ? WHERE id = ?");
        $stmt->execute([$daily_limit, $account['id']]);

        json_response(['success' => true, 'message' => 'Daily limit updated']);
    } else {
        json_response(['success' => false, 'message' => 'Method not allowed'], 405);
    }
} catch (PDOException $e) {
    error_log("Account settings API error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Database error'], 500);
}
