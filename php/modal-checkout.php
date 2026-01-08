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

// Expecting quantities as associative array: { "Product Name": quantity }
$quantities = isset($_POST["quantities"]) ? json_decode($_POST["quantities"], true) : [];

if ($user_id && !empty($items)) {

    // Prepare delete statement for cart
    $delCart = $conn->prepare("DELETE FROM carts WHERE user_id = ? AND Cart = ?");

    // Prepare update statement for products
    $updateProd = $conn->prepare("UPDATE products SET Sold = Sold + ? WHERE `Product Name` = ?");

    foreach ($items as $item) {
        $qty = isset($quantities[$item]) ? intval($quantities[$item]) : 1;

        // Update Sold column
        $updateProd->bind_param("is", $qty, $item);
        $updateProd->execute();

        // Remove from cart
        $delCart->bind_param("is", $user_id, $item);
        $delCart->execute();
    }

    $delCart->close();
    $updateProd->close();
}

// Optional: handle shipping/payment info here

echo json_encode(["success" => true]);
$conn->close();
?>
