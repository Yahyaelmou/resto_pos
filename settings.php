<?php
header("Content-Type: application/json");
require_once "db.php";

// GET: all settings, POST: update (key, value)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $rs = $conn->query("SELECT `key`, `value` FROM settings");
    $settings = [];
    while ($row = $rs->fetch_assoc()) $settings[$row['key']] = $row['value'];
    echo json_encode($settings);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!isset($data['key'], $data['value'])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing key/value"]);
        exit;
    }
    $stmt = $conn->prepare("REPLACE INTO settings (`key`, `value`) VALUES (?, ?)");
    $stmt->bind_param("ss", $data['key'], $data['value']);
    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "DB error"]);
    }
    $stmt->close();
    exit;
}
?>