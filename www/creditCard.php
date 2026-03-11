<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Authorization, Content-Type, User-Agent");

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

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $userId = $_GET['user_id'] ?? null;
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    if (!$userId) {
        http_response_code(400);
        echo json_encode(["message" => "User ID is required"]);
        exit;
    }

    // SQL Injection vulnerability in User-Agent header
    $sql = "SELECT card_number, expiry_date, cvv FROM credit_cards WHERE user_id = $userId AND user_agent = '$userAgent'";
    $result = $conn->query($sql);

    if (!$result) {
        // Return SQL error to allow exploitation
        http_response_code(500);
        echo json_encode(["message" => "SQL Error: " . $conn->error]);
        exit;
    }

    $data = $result->fetch_assoc();

    if ($data) {
        echo json_encode($data);
    } else {
        http_response_code(404);
        echo json_encode(["message" => "Credit card not found"]);
    }
} else {
    http_response_code(405);
    echo json_encode(["message" => "Only GET requests are allowed"]);
}

$conn->close();
?>
