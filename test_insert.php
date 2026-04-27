<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once __DIR__ . '/config/db.php';
    
    // Check current schema
    $stmt = $pdo->query("DESCRIBE admin_users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "COLUMNS IN ACTUAL APP DATABASE:\n";
    foreach($columns as $col) {
        echo $col['Field'] . " | ";
    }
    echo "\n\n";

    // Replay update admin schema if needed
    echo "Running schema update...\n";
    $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
    $sql = file_get_contents(__DIR__ . '/database/update_admin_schema.sql');
    $pdo->exec($sql);
    $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
    echo "Schema executed.\n\n";

    $hash = '$2y$12$wviDozfDaQ8FO7oUR8035O8XRVYYsRhPXTTkgBxswMiomQaYbsviu';
    $stmt = $pdo->prepare("INSERT INTO admin_users (full_name, email, password_hash, phone, date_of_birth, gender, admin_role, access_level, status, two_fa_enabled, failed_attempts) VALUES ('Sujay Paul', 'sujaypaul892@gmail.com', :hash, '9883625390', '2005-03-19', 'Male', 'super_admin', 'full', 'active', 0, 0)");
    $stmt->execute(['hash' => $hash]);
    
    echo "Super Admin inserted successfully!";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
