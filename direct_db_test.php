<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=finzo_db', 'root', '');
    $stmt = $pdo->query("DESCRIBE admin_users");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
