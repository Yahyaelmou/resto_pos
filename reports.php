<?php
header("Content-Type: application/json");
require_once "db.php";

// GET params: type=[sales|items|payments]&range=[day|week|month]
$type = $_GET['type'] ?? "sales";
$range = $_GET['range'] ?? "day";

// Date range
switch ($range) {
    case "day":
        $from = date("Y-m-d 00:00:00");
        break;
    case "week":
        $from = date("Y-m-d 00:00:00", strtotime("-6 days"));
        break;
    case "month":
        $from = date("Y-m-01 00:00:00");
        break;
    default:
        $from = "1970-01-01 00:00:00";
}

if ($type === "sales") {
    $sql = "SELECT DATE(created_at) as date, SUM(total) as total 
            FROM orders WHERE created_at >= '$from'
            GROUP BY DATE(created_at) ORDER BY date DESC";
    $res = $conn->query($sql);
    $data = [];
    while ($row = $res->fetch_assoc()) $data[] = $row;
    echo json_encode($data);
    exit;
}
if ($type === "items") {
    $sql = "SELECT m.name, SUM(oi.quantity) as count
            FROM order_items oi
            JOIN menu_items m ON oi.menu_item_id = m.id
            JOIN orders o ON oi.order_id = o.id
            WHERE o.created_at >= '$from'
            GROUP BY m.id ORDER BY count DESC LIMIT 10";
    $res = $conn->query($sql);
    $data = [];
    while ($row = $res->fetch_assoc()) $data[] = $row;
    echo json_encode($data);
    exit;
}
if ($type === "payments") {
    $sql = "SELECT payment_method, COUNT(*) as count, SUM(total) as total
            FROM orders WHERE created_at >= '$from'
            GROUP BY payment_method";
    $res = $conn->query($sql);
    $data = [];
    while ($row = $res->fetch_assoc()) $data[] = $row;
    echo json_encode($data);
    exit;
}
echo json_encode([]);
?>