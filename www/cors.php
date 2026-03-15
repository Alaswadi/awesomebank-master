<?php
// Vulnerability: CORS Misconfiguration (A05)
// This endpoint reflects the Origin header back as Access-Control-Allow-Origin
// AND sets Access-Control-Allow-Credentials: true.
//
// A properly configured server should only allow specific trusted origins.
// Reflecting the Origin blindly while allowing credentials means ANY site can
// make authenticated cross-origin requests (reading cookies / Authorization headers)
// to this endpoint, enabling cross-origin data theft.

// VULNERABLE: reflect Origin back unconditionally
$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header("Access-Control-Allow-Origin: $origin");
header("Access-Control-Allow-Credentials: true");   // dangerous combination
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Simulate returning sensitive user data that a cross-origin attacker could steal
$token = $_COOKIE['session'] ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? 'no-token');

echo json_encode([
    "message"        => "CORS endpoint — credentials are exposed cross-origin",
    "reflected_origin" => $origin,
    "sensitive_data" => [
        "balance"    => 9999.99,
        "account_no" => "4111111111110002",
        "token_seen" => $token,
    ]
]);
?>
