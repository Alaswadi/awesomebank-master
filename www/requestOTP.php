<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Database credentials
$servername = "db"; // Hostname of the database server
$username = "user"; // Database username
$password = "password"; // Database password
$dbname = "simple_app"; // Database name

// Database connection
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["message" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    $user_id = $_SESSION['user_id'] ?? 1; // Use session user_id or fallback to 1 for testing

    // Generate a random 6-digit OTP
    $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

    // Insert OTP into the database
    $stmt = $conn->prepare("INSERT INTO otps (user_id, otp) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $otp);

    if ($stmt->execute()) {
        echo json_encode([
            "message" => "OTP has been sent to your phone.",
            "otp" => $otp // Vulnerability: Leaking the OTP in the response
        ]);
    } else {
        echo json_encode(["message" => "Failed to generate OTP."]);
    }

    $stmt->close();
} else {
    http_response_code(405);
    echo json_encode(["message" => "Invalid request method."]);
}

$conn->close();
?>
