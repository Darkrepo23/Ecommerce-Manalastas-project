<?php
header('Content-Type: application/json');
error_reporting(E_ALL);

// Database connection
$conn = new mysqli("localhost", "root", "", "ecommerce");
if($conn->connect_error){
    echo json_encode(['success'=>false, 'message'=>'DB connection failed']);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$productName = $conn->real_escape_string($data['productName'] ?? '');
$price = $conn->real_escape_string($data['price'] ?? '');

if(empty($productName)){
    echo json_encode(['success'=>false, 'message'=>'Product name required']);
    exit();
}

// Fetch matching product
$sql = "SELECT `Product Name`, `Price`, `Description`, `Ratings`, `Stock`, `Sold`, `Product Img` 
        FROM `products`
        WHERE `Product Name` = '$productName'";

$result = $conn->query($sql);

if($result && $row = $result->fetch_assoc()){
    // Prepend PHP path to image
    $row['productImg'] = !empty($row['Product Img']) ? 'php/' . $row['Product Img'] : '';
    echo json_encode(['success'=>true, 'product'=>$row]);
} else {
    echo json_encode(['success'=>false, 'message'=>'Product not found']);
}

$conn->close();
?>
