<?php
header("Content-Type: application/json");
require_once "db.php";

/**
 * Menu & Category CRUD
 * - GET: ?type=[categories|items]
 * - POST: add/update
 * - DELETE: remove
 */

$type = $_GET['type'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($type === "categories") {
        $rs = $conn->query("SELECT id, name FROM categories ORDER BY id");
        $data = [];
        while ($row = $rs->fetch_assoc()) $data[] = $row;
        echo json_encode($data); exit;
    }
    if ($type === "items") {
        $rs = $conn->query(
            "SELECT m.id, m.name, m.price, m.category_id, c.name as category 
            FROM menu_items m JOIN categories c ON m.category_id = c.id ORDER BY m.id"
        );
        $data = [];
        while ($row = $rs->fetch_assoc()) $data[] = $row;
        echo json_encode($data); exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    if ($type === "categories") {
        if (isset($data['id'])) {
            $stmt = $conn->prepare("UPDATE categories SET name=? WHERE id=?");
            $stmt->bind_param("si", $data['name'], $data['id']);
            $stmt->execute(); $stmt->close();
            echo json_encode(["success" => true]); exit;
        } else {
            $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->bind_param("s", $data['name']);
            $stmt->execute(); $stmt->close();
            echo json_encode(["success" => true, "id" => $conn->insert_id]); exit;
        }
    }
    if ($type === "items") {
        if (isset($data['id'])) {
            $stmt = $conn->prepare("UPDATE menu_items SET name=?, price=?, category_id=? WHERE id=?");
            $stmt->bind_param("sdii", $data['name'], $data['price'], $data['category_id'], $data['id']);
            $stmt->execute(); $stmt->close();
            echo json_encode(["success" => true]); exit;
        } else {
            $stmt = $conn->prepare("INSERT INTO menu_items (name, price, category_id) VALUES (?, ?, ?)");
            $stmt->bind_param("sdi", $data['name'], $data['price'], $data['category_id']);
            $stmt->execute(); $stmt->close();
            echo json_encode(["success" => true, "id" => $conn->insert_id]); exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $data);
    if ($type === "categories" && isset($data['id'])) {
        $stmt = $conn->prepare("DELETE FROM categories WHERE id=?");
        $stmt->bind_param("i", $data['id']);
        $stmt->execute(); $stmt->close();
        echo json_encode(["success" => true]); exit;
    }
    if ($type === "items" && isset($data['id'])) {
        $stmt = $conn->prepare("DELETE FROM menu_items WHERE id=?");
        $stmt->bind_param("i", $data['id']);
        $stmt->execute(); $stmt->close();
        echo json_encode(["success" => true]); exit;
    }
}
echo json_encode(["error" => "Invalid request"]);
?>