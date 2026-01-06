<?php
header('Content-Type: application/json');
error_reporting(E_ALL);

// Connect to DB
$conn = new mysqli("localhost", "root", "", "ecommerce");
if($conn->connect_error){
    echo json_encode(['success'=>false,'message'=>'DB connection failed']);
    exit();
}

// Get JSON body
$data = json_decode(file_get_contents("php://input"), true);
$username = $conn->real_escape_string($data['username'] ?? '');
$email = $conn->real_escape_string($data['email'] ?? '');
$password = $data['password'] ?? '';

if(!$username || !$email || !$password){
    echo json_encode(['success'=>false,'message'=>'All fields are required']);
    exit();
}

// Hash password
$hashed = password_hash($password, PASSWORD_DEFAULT);

// Insert into database
$stmt = $conn->prepare("INSERT INTO users (Username, Email, Password, DateCreated) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("sss", $username, $email, $hashed);

if($stmt->execute()){
    echo json_encode(['success'=>true,'message'=>'User created successfully']);
} else {
    echo json_encode(['success'=>false,'message'=>'DB insert error: '.$stmt->error]);
}

$stmt->close();
$conn->close();
?>
