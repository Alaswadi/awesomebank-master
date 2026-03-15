<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Vulnerability: Sensitive Data Exposure in URL (A02)
// The password reset flow accepts `token`, `username`, AND `old_password` as GET parameters.
// GET parameters appear in:
//   - Browser history
//   - Server access logs
//   - Referrer headers sent to third-party resources on the page
//   - Proxy / CDN logs
//   - Bookmarks
//
// Example vulnerable URL:
//   GET /resetPassword.php?token=abc123&username=customer1&old_password=123456789&new_password=newpass

// Database credentials
$servername = "db";
$dbUser     = "user";
$dbPassword = "password";
$dbname     = "simple_app";

$conn = new mysqli($servername, $dbUser, $dbPassword, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["message" => "Database connection failed"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // VULNERABLE: old_password and reset token exposed in the URL / GET params
    $token       = $_GET['token']        ?? '';
    $username    = $_GET['username']     ?? '';
    $oldPassword = $_GET['old_password'] ?? '';
    $newPassword = $_GET['new_password'] ?? '';

    if (!$token || !$username || !$oldPassword || !$newPassword) {
        http_response_code(400);
        echo json_encode([
            "message" => "Missing parameters",
            "required" => ["token", "username", "old_password", "new_password"],
            "example"  => "?token=RESET_TOKEN&username=customer1&old_password=123456789&new_password=mynewpass"
        ]);
        exit;
    }

    // Weak token validation — just checks the token equals a predictable value
    $expectedToken = md5($username . 'reset_secret');
    if ($token !== $expectedToken) {
        http_response_code(401);
        echo json_encode(["message" => "Invalid or expired reset token"]);
        exit;
    }

    // Verify old password (MD5 hash comparison — also weak crypto)
    $hashedOld = md5($oldPassword);
    $sql       = "SELECT id FROM users WHERE username = '$username' AND password = '$hashedOld'";
    $result    = $conn->query($sql);

    if (!$result || $result->num_rows === 0) {
        http_response_code(401);
        echo json_encode(["message" => "Old password is incorrect"]);
        exit;
    }

    // Update to new password (MD5 — weak hashing)
    $hashedNew = md5($newPassword);
    $updateSql = "UPDATE users SET password = '$hashedNew' WHERE username = '$username'";
    if ($conn->query($updateSql)) {
        echo json_encode(["message" => "Password reset successful"]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Failed to reset password"]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Generate a reset token for a given username (also returns it — token disclosure)
    $input    = json_decode(file_get_contents('php://input'), true);
    $username = $input['username'] ?? '';

    if (!$username) {
        http_response_code(400);
        echo json_encode(["message" => "Username is required"]);
        exit;
    }

    $token = md5($username . 'reset_secret');

    echo json_encode([
        "message"    => "Reset token generated",
        "username"   => $username,
        "token"      => $token,
        "reset_url"  => "resetPassword.html?token=$token&username=$username"
    ]);
} else {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed"]);
}

$conn->close();
?>
