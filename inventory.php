<?php
header("Content-Type: application/json");
require_once "db.php";

// GET: List all ingredients (inventory)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $q = $conn->query("SELECT * FROM ingredients ORDER BY name");
    $data = [];
    while($row = $q->fetch_assoc()) $data[] = $row;
    echo json_encode($data);
    exit;
}

// POST: Add/adjust stock (purchase/adjustment)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"),true);
    if (!isset($data['ingredient_id'], $data['type'], $data['qty'])) {
        http_response_code(400); echo json_encode(["error"=>"Missing fields"]); exit;
    }
    $type = $data['type'];
    $qty = floatval($data['qty']);
    $ingredient_id = intval($data['ingredient_id']);
    $sign = ($type=="purchase"||$type=="adjustment") ? 1 : -1;
    $conn->query("UPDATE ingredients SET stock = stock + ($sign * $qty) WHERE id=$ingredient_id");
    $stmt = $conn->prepare("INSERT INTO inventory_movements (ingredient_id,type,qty,user_id,notes) VALUES (?,?,?,?,?)");
    $stmt->bind_param("isdis", $ingredient_id, $type, $qty, $data['user_id'], $data['notes']);
    $stmt->execute(); $stmt->close();
    echo json_encode(["success"=>true]);
    exit;
}
?>