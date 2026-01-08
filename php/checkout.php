<?php
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set("display_errors", 1);

$conn = new mysqli("localhost", "root", "", "ecommerce");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "html" => "", "message" => "DB connection failed"]);
    exit;
}

$user_id = $_POST["user_id"] ?? '';
$cart    = $_POST["cart"] ?? null;
$change  = isset($_POST["change"]) ? intval($_POST["change"]) : null;
$remove  = isset($_POST["remove"]) ? intval($_POST["remove"]) : null;

// Remove item
if ($cart !== null && $remove === 1) {
    $del = $conn->prepare("DELETE FROM carts WHERE user_id = ? AND Cart = ?");
    $del->bind_param("is", $user_id, $cart);
    $del->execute();
    $del->close();
}

// Update quantity
if ($cart !== null && $change !== null) {
    $update = $conn->prepare("UPDATE carts SET Quantity = GREATEST(1, Quantity + ?) WHERE user_id = ? AND Cart = ?");
    $update->bind_param("iis", $change, $user_id, $cart);
    $update->execute();
    $update->close();
}

// Fetch cart items
$cartSQL = $conn->prepare("SELECT Cart, Quantity FROM carts WHERE user_id = ?");
$cartSQL->bind_param("i", $user_id);
$cartSQL->execute();
$cartResult = $cartSQL->get_result();

if ($cartResult->num_rows == 0) {
    echo json_encode(["success" => true, "html" => "<p>No items in your cart.</p>"]);
    exit;
}

$html = "";

while ($cartRow = $cartResult->fetch_assoc()) {
    $productName = $cartRow["Cart"];
    $quantity = $cartRow["Quantity"];

    $prodSQL = $conn->prepare("SELECT `Product Name`, `Description`, `Product Img`, `Price` FROM products WHERE `Product Name` = ? LIMIT 1");
    $prodSQL->bind_param("s", $productName);
    $prodSQL->execute();
    $prodResult = $prodSQL->get_result();

    if ($prodResult->num_rows > 0) {
        $prod = $prodResult->fetch_assoc();
        $img = !empty($prod["Product Img"]) ? 'php/' . $prod["Product Img"] : '';
        $desc = $prod["Description"];
        $price = number_format($prod["Price"], 2);

        $html .= '
        <div class="cart-item mb-3">
            <div class="row align-items-center">

                <!-- Checkbox -->
                <div class="col-auto d-flex justify-content-center align-items-center">
                    <input type="checkbox" class="cart-checkbox">
                </div>

                <!-- Product Image -->
                <div class="col-md-2 col-3">
                    <img src="'.$img.'" class="img-fluid" alt="Product">
                </div>

                <!-- Product Info -->
                <div class="col-md-5 col-6">
                    <h5 class="mb-1" id="prodName">'.$productName.'</h5>
                    <p class="text-muted small mb-0">'.$desc.'</p>
                </div>

                <!-- Quantity controls -->
                <div class="col-md-2 col-6 mt-2 mt-md-0">
                    <div class="input-group input-group-sm">
                        <button class="btn btn-outline-secondary" type="button" onclick="updateQty(\''.$productName.'\', -1)">-</button>
                        <input type="number" class="form-control text-center" value="'.$quantity.'" readonly id="quantity">
                        <button class="btn btn-outline-secondary" type="button" onclick="updateQty(\''.$productName.'\', 1)">+</button>
                    </div>
                </div>

                <!-- Price & Remove -->
                <div class="col-md-2 col-6 mt-2 mt-md-0 text-end">
                    <h5 class="text-primary mb-0" id="price">₱'.$price.'</h5>
                    <button class="btn btn-sm btn-outline-danger mt-1" onclick="removeItem(\''.$productName.'\')">Remove</button>
                </div>

            </div>
        </div>';
    }
}

echo json_encode(["success" => true, "html" => $html]);
$conn->close();
?>
