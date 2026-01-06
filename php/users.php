<?php
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$conn = new mysqli("localhost", "root", "", "ecommerce");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit();
}

$action = $_GET["action"] ?? "";
$method = $_SERVER["REQUEST_METHOD"];

// --------------------------
// 1. FETCH ALL USERS
// --------------------------
if ($action == "fetch") {
    $result = $conn->query("SELECT * FROM users ORDER BY ID DESC");
    $users = [];
    while ($row = $result->fetch_assoc()) $users[] = $row;
    echo json_encode($users);
    exit();
}

// --------------------------
// 2. GET SINGLE USER
// --------------------------
if ($action == "get" && isset($_GET["id"])) {
    $id = intval($_GET["id"]);
    $query = $conn->query("SELECT * FROM users WHERE ID = $id");
    echo json_encode($query->fetch_assoc() ?? []);
    exit();
}

// --------------------------
// 3. DELETE USER
// --------------------------
if ($action == "delete" && isset($_GET["id"])) {
    // allow JS POST or DELETE method
    if (!in_array($method, ["POST", "DELETE"])) {
        echo json_encode(["success" => false, "message" => "Invalid request method"]);
        exit();
    }

    $id = intval($_GET["id"]);
    $conn->query("DELETE FROM users WHERE ID = $id");
    echo json_encode(["success" => true, "message" => "User Deleted"]);
    exit();
}

// --------------------------
// 4. INSERT / UPDATE (POST only)
// --------------------------
if ($method !== "POST") {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit();
}

// Common POST values
$username = $_POST["username"] ?? "";
$email    = $_POST["email"] ?? "";
$password = $_POST["password"] ?? "";

// --------------------------
// HANDLE IMAGE UPLOAD
// --------------------------
$profileImg = "";
if (isset($_FILES["userImage"]) && $_FILES["userImage"]["error"] == 0) {
    $targetDir = "../uploads/users/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
    $ext = pathinfo($_FILES["userImage"]["name"], PATHINFO_EXTENSION);
    $newName = uniqid("user_", true) . "." . strtolower($ext);
    $filePath = $targetDir . $newName;
    if (move_uploaded_file($_FILES["userImage"]["tmp_name"], $filePath)) {
        $profileImg = $filePath;
    }
}

// --------------------------
// 5. INSERT USER
// --------------------------
if ($action == "insert") {
    if (!$username || !$email) {
        echo json_encode(["success" => false, "message" => "Missing required fields"]);
        exit();
    }
    if (!$password) {
        echo json_encode(["success" => false, "message" => "Password required"]);
        exit();
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (`Username`, `Email`, `Password`, `ProfileImg`) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $hashed, $profileImg);

    if ($stmt->execute()) {
        $newId = $stmt->insert_id;
        $fetch = $conn->query("SELECT DateCreated FROM users WHERE ID = $newId");
        $row = $fetch->fetch_assoc();

        echo json_encode([
            "success" => true,
            "message" => "User Added!",
            "user" => [
                "ID" => $newId,
                "Username" => $username,
                "Email" => $email,
                "ProfileImg" => $profileImg,
                "DateCreated" => $row["DateCreated"]
            ]
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Insert error: " . $conn->error]);
    }
    exit();
}

// --------------------------
// 6. UPDATE USER
// --------------------------
if ($action == "update") {
    $id = $_POST["ID"] ?? 0;
    if (!$id) {
        echo json_encode(["success" => false, "message" => "Missing user ID"]);
        exit();
    }

    if ($password !== "") {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $conn->query("UPDATE users SET Password='$hashed' WHERE ID=$id");
    }

    $imgQuery = $profileImg ? ", ProfileImg='$profileImg'" : "";
    $conn->query("UPDATE users SET Username='$username', Email='$email' $imgQuery WHERE ID=$id");

    echo json_encode(["success" => true, "message" => "User Updated!"]);
    exit();
}
