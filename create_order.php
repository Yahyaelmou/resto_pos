<?php
header("Content-Type: application/json");
require_once "db.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['table_id'], $data['user_id'], $data['items'], $data['payment_method'], $data['total']) || !is_array($data['items'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing or invalid data."]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO orders (table_id, user_id, status, payment_method, total) VALUES (?, ?, 'pending', ?, ?)");
$stmt->bind_param("iisd", $data['table_id'], $data['user_id'], $data['payment_method'], $data['total']);
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["error" => "Could not create order."]);
    exit;
}
$order_id = $stmt->insert_id;
$stmt->close();

$stmt = $conn->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity) VALUES (?, ?, ?)");
foreach ($data['items'] as $item) {
    if (!isset($item['menu_item_id'], $item['quantity'])) continue;
    $stmt->bind_param("iii", $order_id, $item['menu_item_id'], $item['quantity']);
    $stmt->execute();
}
$stmt->close();

// Mark table as occupied
$conn->query("UPDATE tables SET status='occupied' WHERE id=" . intval($data['table_id']));

echo json_encode(["success" => true, "order_id" => $order_id]);
?>