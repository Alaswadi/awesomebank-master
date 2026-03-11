<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Authorization, Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $phoneNumber = $input['phone_number'] ?? null;

    if (!$phoneNumber || !preg_match('/^1\d{8}$/', $phoneNumber)) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid phone number format."]);
        exit;
    }

    // Simulate successful payment
    echo json_encode(["message" => "Bill paid successfully!"]);
} else {
    http_response_code(405);
    echo json_encode(["message" => "Only POST requests are allowed"]);
}
