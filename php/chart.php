<?php
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "ecommerce");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB connection failed"]);
    exit;
}

// Disable HTML errors from breaking JSON
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    echo json_encode(["success" => false, "message" => "PHP error: $errstr"]);
    exit;
});

// ----------------- USERS -----------------
$userRes = $conn->query("SELECT COUNT(*) AS totalUsers FROM users");
if (!$userRes) { echo json_encode(["success"=>false,"message"=>"User query failed"]); exit; }
$totalUsers = ($userRes->num_rows > 0) ? $userRes->fetch_assoc()['totalUsers'] : 0;

// ----------------- PRODUCTS -----------------
$prodRes = $conn->query("SELECT Sold, Price, `Adding Date` FROM products");


if (!$prodRes) { echo json_encode(["success"=>false,"message"=>"Products query failed"]); exit; }

$totalProducts = 0;
$totalSolds = 0;
$totalSales = 0;
$salesByMonth = array_fill(1, 12, 0);
$soldsByMonth = array_fill(1, 12, 0);

while ($row = $prodRes->fetch_assoc()) {
    $totalProducts++;
    $sold = (int)$row['Sold'];
    $price = (float)$row['Price'];
    $totalSolds += $sold;
    $totalSales += ($sold * $price);

 $month = (int)date('n', strtotime($row['Adding Date']));

    $salesByMonth[$month] += ($sold * $price);
    $soldsByMonth[$month] += $sold;
}

$salesByMonth = array_values($salesByMonth);
$soldsByMonth = array_values($soldsByMonth);

echo json_encode([
    "success" => true,
    "totalUsers" => $totalUsers,
    "totalProducts" => $totalProducts,
    "totalSolds" => $totalSolds,
    "totalSales" => $totalSales,
    "salesByMonth" => $salesByMonth,
    "soldsByMonth" => $soldsByMonth
]);

$conn->close();
?>
