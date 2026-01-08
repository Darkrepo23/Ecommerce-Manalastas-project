<?php
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set("display_errors", 1);

$conn = new mysqli("localhost", "root", "", "ecommerce");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB connection failed"]);
    exit;
}

$user_id = $_POST["user_id"] ?? '';
$items = isset($_POST["items"]) ? json_decode($_POST["items"], true) : [];

if ($user_id && !empty($items)) {
    $del = $conn->prepare("DELETE FROM carts WHERE user_id = ? AND Cart = ?");
    foreach ($items as $item) {
        $del->bind_param("is", $user_id, $item);
        $del->execute();
    }
    $del->close();
}

// You can also save shipping/payment info here if needed

echo json_encode(["success" => true]);
$conn->close();
?>
