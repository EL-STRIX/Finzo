<?php
$mysqli = new mysqli("localhost", "root", "", "finzo_db");
if ($mysqli->connect_error) { die("Connection failed: " . $mysqli->connect_error); }
$result = $mysqli->query("SELECT * FROM audit_log");
$rows = [];
while($row = $result->fetch_assoc()) { $rows[] = $row; }
echo json_encode($rows);
