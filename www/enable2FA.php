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
    $input = json_decode(file_get_contents('php://input'), true);
    $otp = $input['otp'] ?? null;

    session_start();
    $user_id = $_SESSION['user_id'] ?? 1; // Use session user_id or fallback to 1 for testing

    if (!$otp) {
        http_response_code(400);
        echo json_encode(["message" => "OTP is required."]);
        exit;
    }

    // Fetch the latest OTP for the user
    $stmt = $conn->prepare("SELECT otp FROM otps WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $validOTP = $row['otp'];

        if ($otp === $validOTP) {
            echo json_encode(["message" => "Two-Factor Authentication enabled successfully!"]);
        } else {
            http_response_code(401);
            echo json_encode(["message" => "Invalid OTP."]);
        }
    } else {
        http_response_code(404);
        echo json_encode(["message" => "No OTP found. Please request a new one."]);
    }

    $stmt->close();
} else {
    http_response_code(405);
    echo json_encode(["message" => "Invalid request method."]);
}

$conn->close();
?>
