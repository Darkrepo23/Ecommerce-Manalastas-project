<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); // hide PHP warnings

try {
    $conn = new mysqli("localhost", "root", "", "ecommerce");
    if ($conn->connect_error) {
        throw new Exception("DB connection failed: " . $conn->connect_error);
    }

    // Removed `ID` from SELECT
    $sql = "SELECT `Product Name`, `Description`, `Price`, `Ratings`, `Stock`, `Sold`, `Product Img`, `Adding Date`
            FROM `products`
            ORDER BY `Adding Date` DESC";

    $result = $conn->query($sql);
    if (!$result) {
        throw new Exception("DB query failed: " . $conn->error);
    }

    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = [
            'productName' => $row['Product Name'],
            'description' => $row['Description'],
            'price' => $row['Price'] ?? 0,
            'ratings' => $row['Ratings'] ?? 0,
            'stock' => $row['Stock'] ?? 0,
            'sold' => $row['Sold'] ?? 0,
            'productImg' => !empty($row['Product Img']) ? 'php/' . $row['Product Img'] : '', // <- added "php/"
            'addingDate' => $row['Adding Date'] ?? ''
        ];
    }

    echo json_encode(['success' => true, 'products' => $products]);
    $conn->close();

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit();
}
