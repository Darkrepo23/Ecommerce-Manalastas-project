<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// DB connection
$conn = new mysqli("localhost", "root", "", "ecommerce");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}

// POST values
$email = isset($_POST['email']) ? $conn->real_escape_string($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if (!$email || !$password) {
    echo json_encode(["success" => false, "message" => "Email and password are required"]);
    exit;
}

// Query user
$sql = "SELECT ID, Username, Email, Password FROM users WHERE Email='$email' LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {

    $user = $result->fetch_assoc();
    $hashedPassword = $user['Password'];

    // ⭐ VERY IMPORTANT: verify hashed password
    if (password_verify($password, $hashedPassword)) {

        echo json_encode([
            "success" => true,
            "data" => [
                "ID" => $user['ID'],
                "Username" => $user['Username']
            ]
        ]);

    } else {

        echo json_encode(["success" => false, "message" => "Incorrect password"]);
    }

} else {
    echo json_encode(["success" => false, "message" => "Account not found"]);
}

$conn->close();
?>
