<?php
header("Content-Type: application/json");
require_once "db.php";

// GET = list tables + status + current order if any
$sql = "SELECT t.id, t.name, t.status, 
        (SELECT id FROM orders WHERE table_id = t.id AND status='pending' ORDER BY id DESC LIMIT 1) AS active_order_id
        FROM tables t ORDER BY t.id";
$res = $conn->query($sql);
$list = [];
while ($row = $res->fetch_assoc()) {
    $list[] = $row;
}
echo json_encode($list);
?>