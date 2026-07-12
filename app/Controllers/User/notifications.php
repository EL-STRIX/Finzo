<?php
/**
 * Finzo Banking System — Notifications API
 * GET: fetch notifications | POST: mark as read
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 3) . '/middleware/auth-user.php';

if (!isset($_SESSION['user_customer_id'])) {
    $stmt = $pdo->prepare("SELECT customer_id FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $_SESSION['user_customer_id'] = $stmt->fetchColumn();
}
$user_id = $_SESSION['user_customer_id'];

// =====================================================
// GET: FETCH NOTIFICATIONS
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $page     = max(1, intval($_GET['page'] ?? 1));
        $per_page = min(50, max(1, intval($_GET['per_page'] ?? 20)));
        $offset   = ($page - 1) * $per_page;

        // Count total
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $total = (int)$stmt->fetchColumn();

        // Count unread
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$user_id]);
        $unread_count = (int)$stmt->fetchColumn();

        // Fetch notifications
        $stmt = $pdo->prepare("
            SELECT id, type, title, message, is_read, created_at
            FROM notifications
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT {$per_page} OFFSET {$offset}
        ");
        $stmt->execute([$user_id]);
        $notifications = $stmt->fetchAll();

        json_response([
            'success'       => true,
            'notifications' => $notifications,
            'unread_count'  => $unread_count,
            'total'         => $total,
            'pages'         => max(1, ceil($total / $per_page)),
            'current_page'  => $page
        ]);

    } catch (PDOException $e) {
        error_log("Notifications fetch error: " . $e->getMessage());
        json_response(['success' => false, 'message' => 'Failed to load notifications'], 500);
    }
}

// =====================================================
// POST: MARK NOTIFICATIONS AS READ
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = clean_input($_POST['action'] ?? '');

    try {
        if ($action === 'mark_read') {
            // Mark a single notification as read
            $notification_id = intval($_POST['notification_id'] ?? 0);
            if (!$notification_id) {
                json_response(['success' => false, 'message' => 'Notification ID is required'], 422);
            }

            $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
            $stmt->execute([$notification_id, $user_id]);

            json_response(['success' => true, 'message' => 'Notification marked as read']);

        } elseif ($action === 'mark_all_read') {
            // Mark all notifications as read
            $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
            $stmt->execute([$user_id]);

            json_response([
                'success' => true,
                'message' => 'All notifications marked as read',
                'updated' => $stmt->rowCount()
            ]);

        } elseif ($action === 'delete') {
            // Delete a single notification
            $notification_id = intval($_POST['notification_id'] ?? 0);
            if (!$notification_id) {
                json_response(['success' => false, 'message' => 'Notification ID is required'], 422);
            }

            $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
            $stmt->execute([$notification_id, $user_id]);

            json_response(['success' => true, 'message' => 'Notification deleted']);

        } elseif ($action === 'clear_all') {
            // Delete all notifications
            $stmt = $pdo->prepare("DELETE FROM notifications WHERE user_id = ?");
            $stmt->execute([$user_id]);

            json_response([
                'success' => true,
                'message' => 'All notifications cleared',
                'deleted' => $stmt->rowCount()
            ]);

        } else {
            json_response(['success' => false, 'message' => 'Invalid action'], 422);
        }

    } catch (PDOException $e) {
        error_log("Notifications update error: " . $e->getMessage());
        json_response(['success' => false, 'message' => 'Failed to update notifications'], 500);
    }
}

json_response(['success' => false, 'message' => 'Method not allowed'], 405);
