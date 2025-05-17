<?php
header("Content-Type: application/json");
require_once "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $rs = $conn->query("SELECT id, name, username, role FROM users");
    $data = [];
    while ($row = $rs->fetch_assoc()) $data[] = $row;
    echo json_encode($data); exit;
}

// Add/edit employee
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $d = json_decode(file_get_contents("php://input"),true);
    if (isset($d['id'])) {
        $stmt = $conn->prepare("UPDATE users SET name=?, username=?, role=? WHERE id=?");
        $stmt->bind_param("sssi", $d['name'], $d['username'], $d['role'], $d['id']);
        $stmt->execute(); $stmt->close();
        echo json_encode(["success"=>true]); exit;
    } else {
        $pwd = password_hash($d['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, username, password, role) VALUES (?,?,?,?)");
        $stmt->bind_param("ssss", $d['name'], $d['username'], $pwd, $d['role']);
        $stmt->execute(); $stmt->close();
        echo json_encode(["success"=>true,"id"=>$conn->insert_id]); exit;
    }
}

// Clock-in/out
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    parse_str(file_get_contents("php://input"),$d);
    if ($d['action']=="clock_in") {
        $conn->query("INSERT INTO employee_attendance (user_id, clock_in) VALUES ({$d['user_id']}, NOW())");
        echo json_encode(["success"=>true]); exit;
    }
    if ($d['action']=="clock_out") {
        $conn->query("UPDATE employee_attendance SET clock_out=NOW() WHERE user_id={$d['user_id']} AND clock_out IS NULL");
        echo json_encode(["success"=>true]); exit;
    }
}
?>