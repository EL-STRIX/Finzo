<?php
include 'config/config.php';
$res = $conn->query("DESCRIBE admin_users");
$columns = [];
while($row = $res->fetch_assoc()) {
    $columns[] = $row;
}
echo json_encode($columns, JSON_PRETTY_PRINT);
?>
