<?php
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set("display_errors", 1);

$conn = new mysqli("localhost", "root", "", "ecommerce");

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB connection failed"]);
    exit;
}

// Total Users
$users = $conn->query("SELECT COUNT(*) AS total_users FROM users")->fetch_assoc()['total_users'];

// Total Products
$products = $conn->query("SELECT COUNT(*) AS total_products FROM products")->fetch_assoc()['total_products'];

// Total Solds (sum of Sold column)
$solds = $conn->query("SELECT SUM(Sold) AS total_sold FROM products")->fetch_assoc()['total_sold'];

// Total Sales (sum of price * sold)
$salesQuery = $conn->query("SELECT Price, Sold FROM products");
$totalSales = 0;

while ($row = $salesQuery->fetch_assoc()) {
    $totalSales += $row['Price'] * $row['Sold'];
}

echo json_encode([
    "success" => true,
    "users" => $users,
    "products" => $products,
    "solds" => $solds,
    "sales" => $totalSales
]);

$conn->close();



?>
