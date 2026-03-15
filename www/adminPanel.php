<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Vulnerability: Broken Function Level Authorization (A01)
// The role check here reads `role` from the POST body, NOT from the JWT token.
// An attacker simply sends {"role":"admin"} in the request body to gain admin access,
// regardless of what role their JWT actually encodes.
//
// Correct implementation would decode the JWT and read role from there.

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    // VULNERABLE: role is read from user-controlled POST body, not from verified JWT
    $role = $input['role'] ?? 'customer';

    if ($role !== 'admin') {
        http_response_code(403);
        echo json_encode(["message" => "Access denied. Admins only."]);
        exit;
    }

    // If the attacker sends role=admin they reach this code
    $action = $input['action'] ?? 'list_users';

    if ($action === 'list_users') {
        $result = $conn->query("SELECT id, username, role FROM users");
        $users  = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        echo json_encode([
            "message" => "Admin panel — user list",
            "users"   => $users
        ]);
    } elseif ($action === 'list_accounts') {
        $result   = $conn->query("SELECT account_id, user_id, account_type, balance FROM accounts");
        $accounts = [];
        while ($row = $result->fetch_assoc()) {
            $accounts[] = $row;
        }
        echo json_encode([
            "message"  => "Admin panel — account list",
            "accounts" => $accounts
        ]);
    } elseif ($action === 'delete_user') {
        $userId = $input['user_id'] ?? null;
        if (!$userId) {
            http_response_code(400);
            echo json_encode(["message" => "user_id required for delete_user action"]);
            exit;
        }
        $conn->query("DELETE FROM users WHERE id = $userId");
        echo json_encode(["message" => "User $userId deleted"]);
    } else {
        echo json_encode(["message" => "Unknown action", "available_actions" => ["list_users", "list_accounts", "delete_user"]]);
    }
} else {
    http_response_code(405);
    echo json_encode(["message" => "Only POST requests are allowed"]);
}

$conn->close();
?>
