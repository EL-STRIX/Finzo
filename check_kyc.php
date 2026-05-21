<?php
require 'config/db.php';
$stmt = $pdo->query("SHOW CREATE TABLE kyc_applications");
print_r($stmt->fetch(PDO::FETCH_ASSOC));
