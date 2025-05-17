<?php
header("Content-Type: application/json");
require_once "db.php";
$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['order_id'], $data['table_id'])) { http_response_code(400); echo json_encode(["error"=>"Missing"]); exit; }
$conn->query("UPDATE orders SET status='completed' WHERE id=".(int)$data['order_id']);
$conn->query("UPDATE tables SET status='available' WHERE id=".(int)$data['table_id']);
echo json_encode(["success"=>true]);
?>