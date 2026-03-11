<?php
// Database credentials
$servername = "db";
$username = "user";
$password = "password";
$dbname = "simple_app";

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["message" => "Database connection failed"]);
    exit;
}

// JWT creation function (vulnerable with no signing)
function createVulnerableJWT($payload) {
    $header = base64_encode(json_encode(["alg" => "none", "typ" => "JWT"])); // Weak algorithm "none"
    $payload = base64_encode(json_encode($payload));
    $signature = ""; // Empty signature
    return "$header.$payload.$signature";
}

// Handle login request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['username'], $input['password'])) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid input"]);
        exit;
    }

    $username = $input['username'];
    $password = $input['password'];

    // Vulnerable SQL query (SQL Injection possible)
    $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Create a vulnerable JWT
        $payload = [
            "id" => $user['id'],
            "username" => $user['username'],
            "role" => $user['role'],
            "iat" => time(),
            "exp" => time() + 3600 // 1 hour expiry
        ];
        $jwt = createVulnerableJWT($payload);

        echo json_encode([
            "message" => "Login successful!",
            "token" => $jwt
        ]);
    } else {
        http_response_code(401);
        echo json_encode(["message" => "Invalid username or password"]);
    }
} else {
    http_response_code(405); // Method not allowed
    echo json_encode(["message" => "Only POST requests are allowed"]);
}

$conn->close();
?>
