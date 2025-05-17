<?php
// CosyPOS DB connection
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "cosypos";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(["error" => "Database connection failed."]));
}
$conn->set_charset("utf8mb4");
?>