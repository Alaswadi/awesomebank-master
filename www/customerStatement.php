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
    $accountId = $_GET['account_id'] ?? null;

    if (!$accountId) {
        http_response_code(400);
        echo json_encode(["message" => "Account ID is required"]);
        exit;
    }

    // SQL Injection vulnerability
    $sql = "SELECT transaction_id, amount, transaction_type, date FROM transactions WHERE account_id = '$accountId'";
    $result = $conn->query($sql);

    if (!$result) {
        // Return SQL error to allow exploitation
        http_response_code(500);
        echo json_encode(["message" => "SQL Error: " . $conn->error]);
        exit;
    }

    $transactions = [];
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }

    echo json_encode($transactions);
} else {
    http_response_code(405);
    echo json_encode(["message" => "Only GET requests are allowed"]);
}

$conn->close();
?>
