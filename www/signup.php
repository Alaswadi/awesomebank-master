<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Authorization, Content-Type");

// Database connection settings
$servername = "db";
$username = "user";
$password = "password";
$dbname = "simple_app";

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["message" => "Database connection failed"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $username = $input['username'] ?? null;
    $password = $input['password'] ?? null;
    $role = 'customer'; // Default role for new users

    if (!$username || !$password) {
        http_response_code(400);
        echo json_encode(["message" => "Username and password are required"]);
        exit;
    }

    // Check if the username already exists
    $checkUserQuery = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($checkUserQuery);

    if ($result->num_rows > 0) {
        http_response_code(400);
        echo json_encode(["message" => "Username already exists"]);
        exit;
    }

    // Insert new user into the users table
    $insertUserQuery = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', '$role')";
    if (!$conn->query($insertUserQuery)) {
        http_response_code(500);
        echo json_encode(["message" => "Failed to create user", "error" => $conn->error]);
        exit;
    }

    // Get the user ID of the newly created user
    $userId = $conn->insert_id;

    // Create an account for the new user with an initial balance of 0
    $insertAccountQuery = "INSERT INTO accounts (account_id, user_id, account_type, balance) VALUES ($userId, $userId, 'checking', 0)";
    if (!$conn->query($insertAccountQuery)) {
        error_log("Account Query Failed: " . $conn->error);
        http_response_code(500);
        echo json_encode(["message" => "Failed to create account", "error" => $conn->error]);
        exit;
    }

    // Generate a credit card for the new user
    $cardNumber = '411111111111' . str_pad($userId, 4, '0', STR_PAD_LEFT); // Example card number format
    $expiryDate = date('Y-m-d', strtotime('+3 years')); // 3 years from today
    $cvv = rand(100, 999);

    $insertCreditCardQuery = "INSERT INTO credit_cards (user_id, card_number, expiry_date, cvv) VALUES ($userId, '$cardNumber', '$expiryDate', $cvv)";
    if (!$conn->query($insertCreditCardQuery)) {
        http_response_code(500);
        echo json_encode(["message" => "Failed to create credit card", "error" => $conn->error]);
        exit;
    }

    // Return success response
    echo json_encode(["message" => "Signup successful", "user_id" => $userId]);
} else {
    http_response_code(405);
    echo json_encode(["message" => "Only POST requests are allowed"]);
}

$conn->close();
