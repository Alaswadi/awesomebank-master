<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Authorization, Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $phoneNumber = $_GET['phone_number'] ?? null;

    if (!$phoneNumber || !preg_match('/^1\d{8}$/', $phoneNumber)) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid phone number format."]);
        exit;
    }

    // Generate random bill amount
    $billAmount = rand(1000, 5000);

    echo json_encode(["bill_amount" => $billAmount]);
} else {
    http_response_code(405);
    echo json_encode(["message" => "Only GET requests are allowed"]);
}
