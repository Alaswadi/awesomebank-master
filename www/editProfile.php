<?php
// Database credentials
$servername = "db";
$username = "user";
$password = "password";
$dbname = "simple_app";

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["message" => "Database connection failed"]);
    exit;
}

// Handle GET request to fetch user details
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_id = $_GET['user_id'] ?? null;

    if (!$user_id) {
        http_response_code(400);
        echo json_encode(["message" => "User ID is required"]);
        exit;
    }

    $sql = "SELECT username, role FROM users WHERE id = $user_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo json_encode($user);
    } else {
        http_response_code(404);
        echo json_encode(["message" => "User not found"]);
    }
    exit;
}

// Handle POST request to update user details
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['user_id'], $input['password'], $input['role'])) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid input"]);
        exit;
    }

    $user_id = $input['user_id'];
    $password = $input['password'];
    $role = $input['role'];  // Vulnerable: Role can be changed by modifying the request

    $sql = "UPDATE users SET password = '$password', role = '$role' WHERE id = $user_id";

    if ($conn->query($sql)) {
        echo json_encode(["message" => "Profile updated successfully!"]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Error: " . $conn->error]);
    }
    exit;
}

// Handle invalid request methods
http_response_code(405);
echo json_encode(["message" => "Invalid request method"]);
$conn->close();
