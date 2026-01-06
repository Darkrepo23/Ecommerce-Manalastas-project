<?php
// Enable full error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
define('LOG_FILE', 'notepad.txt');

function logAllErrors($errno, $errstr, $errfile, $errline) {
    $message = "[" . date('Y-m-d H:i:s') . "] Error ($errno) in $errfile at line $errline: $errstr\n";
    file_put_contents(LOG_FILE, $message, FILE_APPEND);
    return true;
}
set_error_handler("logAllErrors");
set_exception_handler(function($e){
    file_put_contents(LOG_FILE, "[".date('Y-m-d H:i:s')."] Exception: ".$e->getMessage()."\n", FILE_APPEND);
    echo json_encode(['success'=>false, 'message'=>'Exception occurred. Check notepad.txt']);
    exit();
});

header('Content-Type: application/json');

// Database connection
$conn = new mysqli("localhost", "root", "", "ecommerce");
if($conn->connect_error){
    logAllErrors(E_USER_ERROR, "DB connection failed: ".$conn->connect_error, __FILE__, __LINE__);
    echo json_encode(['success'=>false, 'message'=>'DB connection failed. Check notepad.txt']);
    exit();
}

// Handle POST request
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $action = $_POST['action'] ?? 'add';
    
    // DELETE action
    if($action === 'delete'){
        $productName = $_POST['productName'] ?? '';
        
        if(empty($productName)){
            echo json_encode(['success'=>false, 'message'=>'Product name is required']);
            $conn->close();
            exit();
        }
        
        // First get the image path to delete the file
        $stmt = $conn->prepare("SELECT `Product Img` FROM products WHERE `Product Name` = ?");
        $stmt->bind_param("s", $productName);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($row = $result->fetch_assoc()){
            $imagePath = $row['Product Img'];
            
            // Delete from database
            $deleteStmt = $conn->prepare("DELETE FROM products WHERE `Product Name` = ?");
            $deleteStmt->bind_param("s", $productName);
            
            if($deleteStmt->execute()){
                // Delete image file if exists
                if(!empty($imagePath) && file_exists($imagePath)){
                    unlink($imagePath);
                }
                echo json_encode(['success'=>true, 'message'=>'Product deleted successfully!']);
            } else {
                logAllErrors(E_USER_ERROR, "DB Delete failed: ".$deleteStmt->error, __FILE__, __LINE__);
                echo json_encode(['success'=>false, 'message'=>'Failed to delete product']);
            }
            $deleteStmt->close();
        } else {
            echo json_encode(['success'=>false, 'message'=>'Product not found']);
        }
        $stmt->close();
        $conn->close();
        exit();
    }
    
    // UPDATE action
    if($action === 'update'){
        $originalName = $_POST['originalName'] ?? '';
        $productName = $_POST['productName'] ?? '';
        $specsDescription = $_POST['specsDescription'] ?? '';
        $rate = intval($_POST['rate'] ?? 0);
        $stock = intval($_POST['stock'] ?? 0);
        
        if(empty($originalName)){
            echo json_encode(['success'=>false, 'message'=>'Original product name is required']);
            $conn->close();
            exit();
        }
        
        // Get current image path
        $stmt = $conn->prepare("SELECT `Product Img` FROM products WHERE `Product Name` = ?");
        $stmt->bind_param("s", $originalName);
        $stmt->execute();
        $result = $stmt->get_result();
        $currentImg = '';
        if($row = $result->fetch_assoc()){
            $currentImg = $row['Product Img'];
        }
        $stmt->close();
        
        $productImg = $currentImg; // Keep existing image by default
        
        // Handle new image upload
        if(isset($_FILES['productImage']) && $_FILES['productImage']['error'] == 0){
            $targetDir = "uploads/";
            if(!is_dir($targetDir)) mkdir($targetDir, 0777, true);
            $fileName = basename($_FILES['productImage']['name']);
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $newFileName = uniqid('prod_', true).'.'.$fileExt;
            $targetFilePath = $targetDir.$newFileName;
            
            if(move_uploaded_file($_FILES['productImage']['tmp_name'], $targetFilePath)){
                // Delete old image
                if(!empty($currentImg) && file_exists($currentImg)){
                    unlink($currentImg);
                }
                $productImg = $targetFilePath;
            } else {
                logAllErrors(E_USER_WARNING, "File upload failed during update: $fileName", __FILE__, __LINE__);
            }
        }
        
        $price = floatval($_POST['price'] ?? 0); // <- NEW
        // Update database
    $updateStmt = $conn->prepare("UPDATE products SET `Product Name`=?, `Description`=?, `Ratings`=?, `Stock`=?, `Price`=?, `Product Img`=? WHERE `Product Name`=?");
    $updateStmt->bind_param("ssiidss", $productName, $specsDescription, $rate, $stock, $price, $productImg, $originalName);

        
        if($updateStmt->execute()){
            echo json_encode(['success'=>true, 'message'=>'Product updated successfully!']);
        } else {
            logAllErrors(E_USER_ERROR, "DB Update failed: ".$updateStmt->error, __FILE__, __LINE__);
            echo json_encode(['success'=>false, 'message'=>'Failed to update product']);
        }
        $updateStmt->close();
        $conn->close();
        exit();
    }
    
    // ADD action (default)
    $productName = $_POST['productName'] ?? '';
    $specsDescription = $_POST['specsDescription'] ?? '';
    $rate = intval($_POST['rate'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $price = floatval($_POST['price'] ?? 0); // <- NEW
    $sold = 0;
    $addingDate = date('Y-m-d H:i:s');

    $productImg = '';
    if(isset($_FILES['productImage']) && $_FILES['productImage']['error'] == 0){
        $targetDir = "uploads/";
        if(!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $fileName = basename($_FILES['productImage']['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $newFileName = uniqid('prod_', true).'.'.$fileExt;
        $targetFilePath = $targetDir.$newFileName;
        if(!move_uploaded_file($_FILES['productImage']['tmp_name'], $targetFilePath)){
            logAllErrors(E_USER_WARNING, "File upload failed: $fileName", __FILE__, __LINE__);
            echo json_encode(['success'=>false,'message'=>"File upload error. Check notepad.txt"]);
            $conn->close();
            exit();
        }
        $productImg = $targetFilePath;
    }

    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO products (`Product Name`, `Description`, `Ratings`, `Stock`, `Sold`, `Price`, `Product Img`, `Adding Date`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiidsss", $productName, $specsDescription, $rate, $stock, $sold, $price, $productImg, $addingDate);


    if($stmt->execute()){
        echo json_encode([
            'success'=>true,
            'message'=>'Product added successfully!',
            'productName'=>$productName,
            'specsDescription'=>$specsDescription,
            'rate'=>$rate,
            'stock'=>$stock,
             'price'=>$price, // <- NEW
            'sold'=>$sold,
            'productImg'=>$productImg,
            'addingDate'=>$addingDate
        ]);
    } else {
        logAllErrors(E_USER_ERROR, "DB Insert failed: ".$stmt->error, __FILE__, __LINE__);
        echo json_encode(['success'=>false,'message'=>'Database insert error. Check notepad.txt']);
    }
    $stmt->close();
    $conn->close();
}

// Handle GET request - Fetch all products
elseif($_SERVER['REQUEST_METHOD'] === 'GET'){
    // Added `Price` to SELECT
    $sql = "SELECT `Product Name`, `Description`, `Price`, `Ratings`, `Stock`, `Sold`, `Product Img`, `Adding Date` 
            FROM `products` 
            ORDER BY `Adding Date` DESC";
    $result = $conn->query($sql);

    $products = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }

    echo json_encode(['success' => true, 'products' => $products]);
    $conn->close();
}

// Handle unsupported methods
else {
    echo json_encode(['success'=>false, 'message'=>'Invalid request method']);
    $conn->close();
}

?>