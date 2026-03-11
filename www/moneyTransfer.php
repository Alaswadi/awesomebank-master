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

    $fromAccountId = $input['from_account_id'] ?? null;
    $toAccountId = $input['to_account_id'] ?? null;
    $amount = $input['amount'] ?? null;

    if (!$fromAccountId || !$toAccountId || !$amount || $amount <= 0) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid input"]);
        exit;
    }

    // Fetch sender details
    $senderQuery = "SELECT balance FROM accounts WHERE account_id = $fromAccountId";
    error_log("Executing Sender Query: $senderQuery");

    $senderResult = $conn->query($senderQuery);

    if (!$senderResult) {
        error_log("Sender Query Failed: " . $conn->error);
        http_response_code(500);
        echo json_encode(["message" => "Failed to fetch sender details"]);
        exit;
    }

    $senderData = $senderResult->fetch_assoc();

    if (!$senderData) {
        http_response_code(404);
        echo json_encode(["message" => "Sender account not found", "account_id" => $fromAccountId]);
        exit;
    }

    if ($fromAccountId !== $toAccountId && $senderData['balance'] < $amount) {
        http_response_code(400);
        echo json_encode(["message" => "Insufficient balance"]);
        exit;
    }

    // Fetch recipient details
    $recipientQuery = "SELECT balance FROM accounts WHERE account_id = $toAccountId";
    error_log("Executing Recipient Query: $recipientQuery");

    $recipientResult = $conn->query($recipientQuery);

    if (!$recipientResult) {
        error_log("Recipient Query Failed: " . $conn->error);
        http_response_code(500);
        echo json_encode([
            "message" => "Failed to fetch recipient details",
            "query" => $recipientQuery,
            "error" => $conn->error
        ]);
        exit;
    }

    $recipientData = $recipientResult->fetch_assoc();

    if (!$recipientData) {
        http_response_code(404);
        echo json_encode(["message" => "Recipient account not found", "account_id" => $toAccountId]);
        exit;
    }

    // Vulnerability: Skip subtracting the amount if transferring to the same account
    if ($fromAccountId !== $toAccountId) {
        $subtractQuery = "UPDATE accounts SET balance = balance - $amount WHERE account_id = $fromAccountId";
        error_log("Executing Subtract Query: $subtractQuery");

        if (!$conn->query($subtractQuery)) {
            error_log("Subtract Query Failed: " . $conn->error);
            http_response_code(500);
            echo json_encode(["message" => "Failed to subtract amount from sender's account"]);
            exit;
        }
    } else {
        error_log("Vulnerability triggered: Sending to the same account adds balance from thin air");
    }

    // Add the amount to the recipient's balance
    $addQuery = "UPDATE accounts SET balance = balance + $amount WHERE account_id = $toAccountId";
    error_log("Executing Add Query: $addQuery");

    if (!$conn->query($addQuery)) {
        error_log("Add Query Failed: " . $conn->error);
        http_response_code(500);
        echo json_encode(["message" => "Failed to add amount to recipient's account"]);
        exit;
    }

    // Return response with sender's updated balance and recipient details
    $updatedSenderBalance = $conn->query("SELECT balance FROM accounts WHERE account_id = $fromAccountId")->fetch_assoc()['balance'];

    echo json_encode([
        "message" => "Transfer successful",
        "your_balance" => $updatedSenderBalance,
        "recipient_balance" => $recipientData['balance'] + $amount
    ]);
} else {
    http_response_code(405);
    echo json_encode(["message" => "Only POST requests are allowed"]);
}

$conn->close();
