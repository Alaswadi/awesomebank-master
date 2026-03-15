<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Vulnerability: Command Injection (A03)
// User-supplied $host is passed directly into shell_exec without any sanitization.
// Attacker can supply: 8.8.8.8; cat /etc/passwd
// or: 8.8.8.8 && whoami
// or: $(id)

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $host = $input['host'] ?? '';

    if (!$host) {
        http_response_code(400);
        echo json_encode(["message" => "Host is required"]);
        exit;
    }

    // VULNERABLE: $host is injected directly into the shell command
    $output = shell_exec("ping -c 4 $host");

    echo json_encode([
        "host"   => $host,
        "output" => $output
    ]);
} else {
    http_response_code(405);
    echo json_encode(["message" => "Only POST requests are allowed"]);
}
?>
