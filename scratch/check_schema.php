<?php
require_once __DIR__ . '/../config/db.php';
try {
    $stmt = $pdo->query("DESCRIBE admin_users");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
