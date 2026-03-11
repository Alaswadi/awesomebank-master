<?php
// Database connection settings
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

// Fetch all users
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT id, username, role FROM users";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        echo json_encode($users);
    } else {
        echo json_encode([]);
    }
    exit;
} else {
    http_response_code(405);
    echo json_encode(["message" => "Only GET requests are allowed"]);
}

$conn->close();
?>
