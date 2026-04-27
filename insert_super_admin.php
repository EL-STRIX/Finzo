<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
try {
    $pdo = new PDO('mysql:host=localhost;dbname=finzo_db;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // The password hash from previous step
    $hash = '$2y$12$wviDozfDaQ8FO7oUR8035O8XRVYYsRhPXTTkgBxswMiomQaYbsviu';
    
    $stmt = $pdo->prepare("INSERT INTO admin_users (full_name, email, password_hash, phone, date_of_birth, gender, admin_role, access_level, status, two_fa_enabled, failed_attempts) VALUES ('Sujay Paul', 'sujaypaul892@gmail.com', :hash, '9883625390', '2005-03-19', 'Male', 'super_admin', 'full', 'active', 0, 0)");
    $stmt->execute(['hash' => $hash]);
    echo "Successfully Inserted Admin!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
