<?php
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set("display_errors", 1);

// Database connection
$conn = new mysqli("localhost", "root", "", "ecommerce");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}

// POST data
$user_id  = $_POST["user_id"] ?? "";
$cart     = $_POST["cart"] ?? "";
$quantity = $_POST["quantity"] ?? "";

if (!$user_id || !$cart || !$quantity) {
    echo json_encode(["success" => false, "message" => "Missing fields"]);
    exit;
}

/*-------------------------------------------------
    Check if this cart item already exists
    Only same cart name for same user triggers update
-------------------------------------------------*/
$check = $conn->prepare("SELECT Quantity FROM carts WHERE user_id = ? AND Cart = ?");
$check->bind_param("is", $user_id, $cart);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    // Same cart exists → update quantity
    $update = $conn->prepare("UPDATE carts SET Quantity = ? WHERE user_id = ? AND Cart = ?");
    $update->bind_param("iis", $quantity, $user_id, $cart);
    
    if ($update->execute()) {
        echo json_encode(["success" => true, "message" => "Cart updated"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update cart"]);
    }
} else {
    // New cart item → insert row
    $insert = $conn->prepare("INSERT INTO carts (user_id, Cart, Quantity) VALUES (?, ?, ?)");
    $insert->bind_param("isi", $user_id, $cart, $quantity);

    if ($insert->execute()) {
        echo json_encode(["success" => true, "message" => "Added to cart"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to add to cart"]);
    }
}

$conn->close();
?>
