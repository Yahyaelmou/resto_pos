<?php
header("Content-Type: application/json");
require_once "db.php";

$sql = "SELECT c.id AS category_id, c.name AS category_name, 
        m.id AS item_id, m.name AS item_name, m.price
        FROM categories c
        LEFT JOIN menu_items m ON m.category_id = c.id
        ORDER BY c.id, m.id";
$result = $conn->query($sql);

$menu = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $catId = $row['category_id'];
        if (!isset($menu[$catId])) {
            $menu[$catId] = [
                "id" => $catId,
                "name" => $row['category_name'],
                "items" => []
            ];
        }
        if ($row['item_id']) {
            $menu[$catId]["items"][] = [
                "id" => $row['item_id'],
                "name" => $row['item_name'],
                "price" => (float)$row['price']
            ];
        }
    }
    $menu = array_values($menu);
}
echo json_encode($menu);
?>