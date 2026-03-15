<?php
// Vulnerability: Path Traversal (A01)
// The user-supplied `file` parameter is appended to a base directory path without
// any sanitization, canonicalization, or path component validation.
// Attacker can supply: ../../etc/passwd  or  ../../../windows/win.ini
// Example request: GET /downloadStatement.php?file=../../etc/passwd

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $file = $_GET['file'] ?? '';

    if (!$file) {
        http_response_code(400);
        echo "Error: 'file' parameter is required.\n";
        echo "Example: ?file=sample_statement.txt\n";
        exit;
    }

    // VULNERABLE: no realpath() check, no basename() enforcement
    $path = __DIR__ . '/statements/' . $file;

    if (!file_exists($path)) {
        http_response_code(404);
        echo "File not found: $path\n";
        exit;
    }

    header('Content-Type: text/plain');
    echo file_get_contents($path);
} else {
    http_response_code(405);
    echo "Only GET requests are allowed.";
}
?>
