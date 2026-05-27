<?php
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/db.php';

$where_sql = "u.is_deleted = 1";
$params = [];

$stmt = $pdo->prepare("
    SELECT u.id, u.customer_id, u.full_name, u.email, u.profile_picture
    FROM users u
    WHERE {$where_sql}
    LIMIT 5
");
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$base_avatar_dir = dirname(__DIR__, 2) . '/uploads/avatars/';
foreach ($users as &$u) {
    $avatar_path = $base_avatar_dir . 'user_' . $u['id'] . '.jpg';
    $u['avatar_path_tested'] = $avatar_path;
    $u['exists'] = file_exists($avatar_path);
    $u['avatar_url'] = file_exists($avatar_path) ? APP_URL . '/uploads/avatars/user_' . $u['id'] . '.jpg?t=' . filemtime($avatar_path) : null;
}

echo json_encode($users, JSON_PRETTY_PRINT);
