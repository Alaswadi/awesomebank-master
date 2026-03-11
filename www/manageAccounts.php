<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
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

// Authorization Check (For simplicity, we'll assume admin role is required)
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
if (strpos($authHeader, 'Bearer ') !== 0) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized"]);
    exit;
}

// Extract token payload (For simplicity, skipping full JWT validation)
$token = substr($authHeader, 7);
$payload = json_decode(base64_decode(explode('.', $token)[1]), true);

if ($payload['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["message" => "Forbidden"]);
    exit;
}

// Handle GET and POST requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch all customer accounts
    $sql = "SELECT accounts.account_id, users.username, accounts.account_type, accounts.balance
            FROM accounts
            JOIN users ON accounts.user_id = users.id
            WHERE users.role = 'customer'";
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
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update account balance
    $data = json_decode(file_get_contents('php://input'), true);
    $accountId = $data['account_id'] ?? null;
    $amount = $data['amount'] ?? null;

    if (!$accountId || !is_numeric($amount)) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid input"]);
        exit;
    }

    $sql = "UPDATE accounts SET balance = balance + $amount WHERE account_id = $accountId";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(["message" => "Balance updated successfully"]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Failed to update balance"]);
    }
} else {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed"]);
}

$conn->close();
?>
