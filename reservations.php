<?php
header("Content-Type: application/json");
require_once "db.php";

// GET: fetch reservations, POST: create

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT r.id, r.customer_name, r.guests, r.datetime, r.status, t.name AS table_name 
            FROM reservations r
            JOIN tables t ON r.table_id = t.id
            ORDER BY r.datetime DESC";
    $res = $conn->query($sql);
    $list = [];
    while ($row = $res->fetch_assoc()) $list[] = $row;
    echo json_encode($list);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!isset($data['customer_name'], $data['table_id'], $data['guests'], $data['datetime'])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing data"]);
        exit;
    }
    $stmt = $conn->prepare("INSERT INTO reservations (customer_name, table_id, guests, datetime) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("siis", $data['customer_name'], $data['table_id'], $data['guests'], $data['datetime']);
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "reservation_id" => $stmt->insert_id]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Could not save reservation"]);
    }
    $stmt->close();
    exit;
}
?>