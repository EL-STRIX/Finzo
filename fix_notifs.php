<?php
require 'config/db.php';
$pdo->query("INSERT INTO notifications (user_id, type, title, message)
             SELECT id, 'system', 'Welcome to Finzo!', 'Your account has been registered. Complete your profile to activate.' FROM users
             WHERE id NOT IN (SELECT user_id FROM notifications WHERE title = 'Welcome to Finzo!')");
$pdo->query("INSERT INTO notifications (user_id, type, title, message)
             SELECT id, 'security', 'Profile Incomplete', 'Please complete your identity verification to unlock banking.' FROM users
             WHERE id NOT IN (SELECT user_id FROM notifications WHERE title = 'Profile Incomplete')");
echo "Success";
