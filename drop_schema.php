<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=finzo_test_db', 'root', '');
$pdo->exec('DROP TABLE IF EXISTS _test_schema_version');
echo "Dropped.";
