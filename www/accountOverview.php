<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
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

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $userId = $_GET['user_id'] ?? null;

    if (!$userId) {
        http_response_code(400);
        echo json_encode(["message" => "User ID is required"]);
        exit;
    }

    // Fetch accounts for the provided user_id
    $sql = "SELECT account_id, account_type, balance FROM accounts WHERE user_id = $userId";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $accounts = [];
        while ($row = $result->fetch_assoc()) {
            $accounts[] = $row;
        }
        echo json_encode($accounts);
    } else {
        echo json_encode([]);
    }
} else {
    http_response_code(405);
    echo json_encode(["message" => "Only GET requests are allowed"]);
}

$conn->close();
?>
